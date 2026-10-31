<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Services\AuditLogger;
use App\Services\CoinRewardService;
use App\Services\PointCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function __construct(
        protected PointCalculationService $pointService,
        protected CoinRewardService $coinService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Result::with([
            'program.category',
            'firstEntry.student.group',
            'secondEntry.student.group',
            'thirdEntry.student.group',
            'verifier',
        ]);

        if ($status === 'published') {
            $query->where('status', 'published');
        } elseif ($status === 'submitted') {
            $query->where('status', 'submitted');
        } elseif ($status === 'in_progress') {
            $query->whereIn('status', ['draft', 'under_review']);
        } elseif ($status === 'pending') {
            $query->where('status', 'draft');
        }

        if ($search) {
            $query->whereHas('program', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
        }

        $results = $query->latest()->paginate(15)->withQueryString();
        $pendingPrograms = Program::doesntHave('result')->orderBy('name')->get();
        $teams = Group::orderByDesc('points_cache')->get();

        $counts = [
            'all' => Result::count(),
            'pending' => Program::doesntHave('result')->count() + Result::where('status', 'draft')->count(),
            'in_progress' => Result::whereIn('status', ['draft', 'under_review'])->count(),
            'submitted' => Result::where('status', 'submitted')->count(),
            'published' => Result::where('status', 'published')->count(),
        ];

        return view('admin.results.index', compact('results', 'pendingPrograms', 'status', 'search', 'teams', 'counts'));
    }

    public function create(Request $request): View
    {
        $programId = $request->query('program_id');
        $program = Program::with(['entries.student.group', 'entries.participants', 'entries.scoreSheets', 'scoringCriteria'])
            ->findOrFail($programId);

        $podium = PointCalculationService::determinePodiumForProgram($program);

        $coinPreview = null;
        if ($podium['first'] || $podium['second'] || $podium['third']) {
            $tempResult = new Result([
                'program_id' => $program->id,
                'first_entry_id' => $podium['first']?->id,
                'second_entry_id' => $podium['second']?->id,
                'third_entry_id' => $podium['third']?->id,
            ]);
            $tempResult->setRelation('program', $program);
            if ($podium['first']) {
                $tempResult->setRelation('firstEntry', $podium['first']);
            }
            if ($podium['second']) {
                $tempResult->setRelation('secondEntry', $podium['second']);
            }
            if ($podium['third']) {
                $tempResult->setRelation('thirdEntry', $podium['third']);
            }
            $coinPreview = $this->coinService->previewGroupCoinDistribution($tempResult);
        }

        return view('admin.results.create', compact('program', 'podium', 'coinPreview'));
    }

    public function store(Request $request): RedirectResponse
    {
        Result::ensureSchema();

        $programId = $request->input('program_id');
        $program = Program::findOrFail($programId);

        $podium = PointCalculationService::determinePodiumForProgram($program);

        $firstEntryId = $request->input('first_entry_id') ?: $podium['first']?->id;
        $secondEntryId = $request->input('second_entry_id') ?: $podium['second']?->id;
        $thirdEntryId = $request->input('third_entry_id') ?: $podium['third']?->id;

        $request->merge([
            'first_entry_id' => $firstEntryId,
            'second_entry_id' => $secondEntryId,
            'third_entry_id' => $thirdEntryId,
        ]);

        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id', 'unique:results,program_id'],
            'first_entry_id' => ['required', 'exists:program_entries,id'],
            'first_grade' => ['nullable', 'string', 'max:10'],
            'second_entry_id' => ['nullable', 'exists:program_entries,id'],
            'second_grade' => ['nullable', 'string', 'max:10'],
            'third_entry_id' => ['nullable', 'exists:program_entries,id'],
            'third_grade' => ['nullable', 'string', 'max:10'],
            'status' => ['required', 'in:draft,submitted,under_review,verified,published'],
            'remarks' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'published') {
            $validated['published_at'] = Carbon::now();
            $validated['verified_by'] = Auth::id();
        }

        $result = Result::create($validated);

        AuditLogger::log('create_result', $result, null, $result->toArray());

        if ($result->status === 'published') {
            $this->issueCertificates($result);
            $this->pointService->recalculateAllPoints();
            $this->coinService->distributeCoinsForResult($result);
            Group::checkAndApplyMilestone();
        }

        return redirect()->route('admin.results.index')->with('success', "Result for '{$result->program->name}' saved ({$result->status}).");
    }

    public function edit(Result $result): View
    {
        $result->load([
            'program.entries.student.group',
            'program.entries.participants',
            'program.entries.scoreSheets',
            'firstEntry.group',
            'firstEntry.student',
            'firstEntry.participants',
            'secondEntry.group',
            'secondEntry.student',
            'secondEntry.participants',
            'thirdEntry.group',
            'thirdEntry.student',
            'thirdEntry.participants',
        ]);

        $podium = PointCalculationService::determinePodiumForProgram($result->program);

        $coinPreview = $this->coinService->previewGroupCoinDistribution($result);

        return view('admin.results.edit', compact('result', 'podium', 'coinPreview'));
    }

    public function autoDetermine(Program $program): RedirectResponse
    {
        $result = PointCalculationService::autoAssignResultPodium($program);

        if (! $result) {
            return back()->with('error', "No submitted judge marks found for '{$program->name}'.");
        }

        $this->pointService->recalculateForProgram($program);

        return back()->with('success', "1st, 2nd, and 3rd place winners for '{$program->name}' automatically determined from judge marks!");
    }

    public function update(Request $request, Result $result): RedirectResponse
    {
        Result::ensureSchema();

        $validated = $request->validate([
            'first_entry_id' => ['required', 'exists:program_entries,id'],
            'first_grade' => ['nullable', 'string', 'max:10'],
            'second_entry_id' => ['nullable', 'exists:program_entries,id'],
            'second_grade' => ['nullable', 'string', 'max:10'],
            'third_entry_id' => ['nullable', 'exists:program_entries,id'],
            'third_grade' => ['nullable', 'string', 'max:10'],
            'status' => ['required', 'in:draft,submitted,under_review,verified,published'],
            'remarks' => ['nullable', 'string'],
        ]);

        $old = $result->toArray();
        $wasPublished = $result->status === 'published';

        if ($validated['status'] === 'published' && ! $wasPublished) {
            $validated['published_at'] = Carbon::now();
            $validated['verified_by'] = Auth::id();
        }

        $result->update($validated);

        AuditLogger::log('update_result', $result, $old, $result->toArray());

        if ($wasPublished && $result->wasChanged(['first_entry_id', 'second_entry_id', 'third_entry_id', 'first_grade', 'second_grade', 'third_grade', 'status'])) {
            $this->coinService->reverseCoinDistributionForResult($result, 'Podium or status updated');
        }

        if ($result->status === 'published') {
            $this->issueCertificates($result);
            $this->pointService->recalculateAllPoints();
            $this->coinService->distributeCoinsForResult($result);
            Group::checkAndApplyMilestone();
        }

        return redirect()->route('admin.results.index')->with('success', "Result for '{$result->program->name}' updated ({$result->status}).");
    }

    public function publish(Result $result): RedirectResponse
    {
        Result::ensureSchema();

        $old = $result->toArray();
        $result->update([
            'status' => 'published',
            'published_at' => Carbon::now(),
            'verified_by' => Auth::id(),
        ]);

        $result->program->update(['status' => 'completed']);

        $this->issueCertificates($result);
        $this->pointService->recalculateAllPoints();
        $this->coinService->distributeCoinsForResult($result);
        Group::checkAndApplyMilestone();

        AuditLogger::log('publish_result', $result, $old, $result->toArray());

        return back()->with('success', "Result for '{$result->program->name}' is now PUBLISHED and points and coins have been calculated.");
    }

    public function unpublish(Result $result): RedirectResponse
    {
        $old = $result->toArray();
        $programName = $result->program?->name ?? 'Program';

        $result->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        // Reverse coin distribution and recalculate team points
        $this->coinService->reverseCoinDistributionForResult($result, 'Result unpublished / reverted to draft');
        $this->pointService->recalculateAllPoints();

        AuditLogger::log('unpublish_result', $result, $old, $result->toArray());

        return back()->with('success', "Result for '{$programName}' has been reverted to DRAFT and hidden from public view. Points have been recalculated.");
    }

    public function sendToAnnouncer(Result $result): RedirectResponse
    {
        Result::ensureSchema();
        $old = $result->status;

        if (! $result->result_number) {
            $max = Result::getSafeMaxResultNumber();
            $result->result_number = $max + 1;
        }

        $result->update([
            'status' => 'send',
            'result_number' => $result->result_number,
            'published_at' => $result->published_at ?? Carbon::now(),
        ]);

        // Immediately update team standings in Admin Panel
        $this->pointService->recalculateAllPoints();

        AuditLogger::log('send_result_to_announcer', $result, ['status' => $old], ['status' => 'send', 'result_number' => $result->result_number]);

        $formattedNo = sprintf('%02d', $result->result_number);

        return back()->with('success', "Result #{$formattedNo} for '{$result->program->name}' has been sent to the Announcer Desk. Admin team standings updated.");
    }

    protected function issueCertificates(Result $result): void
    {
        $program = $result->program;

        $placements = [
            '1st Place' => $result->firstEntry,
            '2nd Place' => $result->secondEntry,
            '3rd Place' => $result->thirdEntry,
        ];

        foreach ($placements as $pos => $entry) {
            if ($entry && $entry->student_id) {
                $certNum = 'QUAF09-'.strtoupper(Str::slug($program->code)).'-'.$entry->chest_number;
                Certificate::firstOrCreate(
                    ['certificate_number' => $certNum],
                    [
                        'entry_id' => $entry->id,
                        'student_id' => $entry->student_id,
                        'program_id' => $program->id,
                        'position' => $pos,
                        'issued_at' => Carbon::now(),
                        'qr_verification_url' => route('verify.certificate', $certNum),
                    ]
                );
            }
        }
    }

    public function declareIndex(Request $request): View
    {
        $search = $request->query('search');

        $query = Program::with(['category', 'stage', 'result'])
            ->whereHas('result', fn ($q) => $q->whereIn('status', ['verified', 'send', 'announced']));

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.results.declare', compact('programs', 'search'));
    }

    public function declaredResults(Request $request): View
    {
        Result::ensureSchema();

        $search = $request->query('search');

        $query = Result::with(['program.category', 'program.stage', 'firstEntry.student', 'secondEntry.student', 'thirdEntry.student'])
            ->whereIn('status', ['send', 'delivered', 'announced', 'published']);

        if ($search) {
            $query->whereHas('program', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $results = $query->latest('published_at')->paginate(20)->withQueryString();

        return view('admin.results.declared', compact('results', 'search'));
    }

    public function undeclare(Result $result): RedirectResponse
    {
        $programName = $result->program?->name ?? 'Program';
        $this->coinService->reverseCoinDistributionForResult($result, 'Verdict undeclared');
        $result->delete();

        $this->pointService->recalculateAllPoints();

        AuditLogger::log('undeclare_result', null, ['program' => $programName], null);

        return back()->with('success', "Result for '{$programName}' has been undeclared.");
    }

    public function destroy(Result $result): RedirectResponse
    {
        $program = $result->program;
        $programName = $program?->name ?? 'Program';

        if ($program && $program->status === 'completed') {
            $program->update(['status' => 'upcoming']);
        }

        $this->coinService->reverseCoinDistributionForResult($result, 'Verdict deleted');
        $result->delete();

        $this->pointService->recalculateAllPoints();

        AuditLogger::log('delete_result', null, ['program' => $programName], null);

        return redirect()->route('admin.results.index')->with('success', "Verdict for '{$programName}' deleted successfully and points recalculated.");
    }

    public function coinPreview(Result $result): JsonResponse
    {
        $result->load([
            'program',
            'firstEntry.group',
            'firstEntry.student',
            'firstEntry.participants',
            'secondEntry.group',
            'secondEntry.student',
            'secondEntry.participants',
            'thirdEntry.group',
            'thirdEntry.student',
            'thirdEntry.participants',
        ]);

        $preview = $this->coinService->previewGroupCoinDistribution($result);

        return response()->json($preview);
    }

    public function show(Result $result): View
    {
        $result->load([
            'program.category',
            'program.stage',
            'firstEntry.group',
            'firstEntry.student',
            'firstEntry.participants',
            'secondEntry.group',
            'secondEntry.student',
            'secondEntry.participants',
            'thirdEntry.group',
            'thirdEntry.student',
            'thirdEntry.participants',
            'verifier',
        ]);

        $coinPreview = $this->coinService->previewGroupCoinDistribution($result);

        return view('admin.results.show', compact('result', 'coinPreview'));
    }

    public function specified(Request $request): View
    {
        $programQuery = $request->query('program_id');
        $selectedProgram = null;
        $rankedEntries = collect();

        if ($programQuery) {
            $trimmed = trim((string) $programQuery);
            $numeric = preg_replace('/[^0-9]/', '', $trimmed);

            $selectedProgram = Program::with(['category', 'stage', 'result'])
                ->where(function ($q) use ($trimmed, $numeric) {
                    $q->where('code', $trimmed)
                        ->orWhere('code', 'Q9-'.$trimmed)
                        ->orWhere('code', 'Q-'.$trimmed)
                        ->orWhere('code', 'like', '%'.$trimmed.'%');

                    if ($numeric !== '') {
                        $q->orWhere('code', 'like', '%-'.$numeric)
                            ->orWhere('code', 'like', '%-'.$numeric.' %');
                    }

                    $q->orWhere('name', 'like', "%{$trimmed}%")
                        ->orWhere('malayalam_name', 'like', "%{$trimmed}%");

                    if (is_numeric($trimmed)) {
                        $q->orWhere('id', (int) $trimmed);
                    }
                })
                ->orderByRaw('CASE 
                    WHEN code = ? THEN 1 
                    WHEN code = ? THEN 2 
                    WHEN code LIKE ? THEN 3 
                    WHEN code LIKE ? THEN 4 
                    WHEN name LIKE ? THEN 5 
                    ELSE 6 END', [
                    $trimmed,
                    'Q9-'.$trimmed,
                    '%-'.$numeric,
                    '%'.$trimmed.'%',
                    '%'.$trimmed.'%',
                ])
                ->first();

            if ($selectedProgram) {
                $entries = ProgramEntry::where('program_id', $selectedProgram->id)
                    ->where('attendance_status', 'present')
                    ->with(['student.group', 'group', 'scores'])
                    ->get()
                    ->map(function ($entry) {
                        $score = (float) $entry->scores->avg('total_score');
                        $entry->computed_score = $score;
                        $gradeInfo = PointCalculationService::getGradeFromScore($score);
                        $entry->computed_grade = $gradeInfo['grade'] ?? '-';

                        return $entry;
                    })
                    ->sortByDesc('computed_score')
                    ->values();

                $currentRank = 0;
                $prevScore = null;
                $rankMap = [1 => 'first', 2 => 'second', 3 => 'third'];

                foreach ($entries as $item) {
                    if ($item->computed_score <= 0) {
                        $item->computed_rank = '-';

                        continue;
                    }

                    if ($prevScore === null || abs($item->computed_score - $prevScore) > 0.001) {
                        $currentRank++;
                        $prevScore = $item->computed_score;
                    }

                    $item->computed_rank = $rankMap[$currentRank] ?? '-';
                }
                $rankedEntries = $entries;
            }
        }

        return view('admin.results.specified', compact('selectedProgram', 'rankedEntries', 'programQuery'));
    }

    public function allResults(Request $request): View
    {
        $search = $request->query('search');

        $query = Program::with(['category', 'stage', 'result'])
            ->whereHas('result');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.results.all', compact('programs', 'search'));
    }

    /**
     * Strategic Announcement & Standings Simulation Planner for Central Festival Committee.
     */
    public function planner(Request $request): View
    {
        $groups = Group::orderByDesc('points_cache')->get();

        $results = Result::with([
            'program.category',
            'program.stage',
            'firstEntry.student.group',
            'secondEntry.student.group',
            'thirdEntry.student.group',
        ])->orderByRaw("CASE WHEN status IN ('draft', 'verified') THEN 0 WHEN status = 'send' THEN 1 WHEN status = 'announced' THEN 2 ELSE 3 END")
            ->latest('updated_at')
            ->get();

        // Attach points breakdown to each result
        $resultsData = $results->map(function ($res) {
            $breakdown = $this->getResultPointsBreakdown($res);
            $res->points_by_group = $breakdown['points_by_group'];
            $res->first_group = $breakdown['first_group'];
            $res->second_group = $breakdown['second_group'];
            $res->third_group = $breakdown['third_group'];
            $res->top_group = $breakdown['top_group'];
            $res->max_points = $breakdown['max_points'];

            return $res;
        });

        // Calculate group strategy potential in queue
        $groupStrategyStats = [];
        foreach ($groups as $grp) {
            $readyWins = $resultsData->where('status', '!=', 'published')
                ->filter(fn ($r) => $r->top_group && $r->top_group->id === $grp->id)
                ->count();

            $pendingPoints = $resultsData->where('status', '!=', 'published')
                ->sum(fn ($r) => $r->points_by_group[$grp->id] ?? 0);

            $groupStrategyStats[$grp->id] = [
                'group' => $grp,
                'ready_wins' => $readyWins,
                'pending_points' => $pendingPoints,
            ];
        }

        $stats = [
            'total_programs' => Program::count(),
            'published_count' => Result::where('status', 'published')->count(),
            'send_count' => Result::where('status', 'send')->count(),
            'announced_count' => Result::where('status', 'announced')->count(),
            'ready_count' => Result::where('status', '!=', 'published')->count(),
            'remaining_count' => Program::whereDoesntHave('result')->count(),
        ];

        $publicStandingsInfo = [
            'count' => (int) FestivalSetting::get('public_standings_results_count', 0),
            'label' => FestivalSetting::get('public_standings_label', 'Official Standings'),
            'updated_at' => FestivalSetting::get('public_standings_updated_at'),
        ];

        return view('admin.results.planner', compact(
            'groups',
            'resultsData',
            'groupStrategyStats',
            'stats',
            'publicStandingsInfo'
        ));
    }

    /**
     * Batch send selected results to the Stage Announcer desk.
     */
    public function batchSendToAnnouncer(Request $request): RedirectResponse
    {
        Result::ensureSchema();
        $resultIds = $request->input('result_ids', []);
        if (empty($resultIds)) {
            return back()->with('error', 'അനൗൺസർ ഡെസ്കിലേക്ക് അയക്കാൻ റിസൾട്ടുകൾ തിരഞ്ഞെടുത്തിട്ടില്ല.');
        }

        $results = Result::whereIn('id', $resultIds)->get();
        $count = 0;
        foreach ($results as $res) {
            $old = $res->status;
            if (! $res->result_number) {
                $max = Result::getSafeMaxResultNumber();
                $res->result_number = $max + 1;
            }
            $res->update([
                'status' => 'send',
                'result_number' => $res->result_number,
                'published_at' => $res->published_at ?? Carbon::now(),
            ]);
            AuditLogger::log('send_result_to_announcer', $res, ['status' => $old], ['status' => 'send', 'result_number' => $res->result_number]);
            $count++;
        }

        if ($count > 0) {
            $this->pointService->recalculateAllPoints();
        }

        return back()->with('success', "{$count} മത്സര ഫലങ്ങൾ സ്റ്റേജ് അനൗൺസർ ഡെസ്കിലേക്ക് കൈമാറി. അഡ്മിൻ ടീം സ്റ്റാൻഡിങ്സ് അപ്‌ഡേറ്റ് ചെയ്തു.");
    }

    /**
     * Batch publish selected results directly and optionally update standings.
     */
    public function batchPublish(Request $request): RedirectResponse
    {
        Result::ensureSchema();

        $resultIds = $request->input('result_ids', []);
        if (empty($resultIds)) {
            return back()->with('error', 'പ്രസിദ്ധീകരിക്കാൻ റിസൾട്ടുകൾ തിരഞ്ഞെടുത്തിട്ടില്ല.');
        }

        $results = Result::whereIn('id', $resultIds)->get();
        $count = 0;
        foreach ($results as $res) {
            $res->update([
                'status' => 'published',
                'is_media_published' => true,
                'media_published_at' => Carbon::now(),
                'published_at' => $res->published_at ?? Carbon::now(),
            ]);
            $res->program->update(['status' => 'completed']);
            $this->issueCertificates($res);
            $count++;
        }

        $this->pointService->recalculateAllPoints();

        if ($request->boolean('update_standings')) {
            $customLabel = $request->input('standings_label');
            Group::publishStandings($customLabel);
        }

        return back()->with('success', "{$count} ഫലങ്ങൾ വിജയകരമായി പബ്ലിഷ് ചെയ്യുകയും പോയിന്റ് നില അപ്‌ഡേറ്റ് ചെയ്യുകയും ചെയ്തു.");
    }

    /**
     * Publish team standings milestone announcement.
     */
    public function publishStandings(Request $request): RedirectResponse
    {
        $customLabel = $request->input('custom_label', $request->input('label'));
        $count = Group::publishStandings($customLabel);

        return back()->with('success', "ടീം സ്റ്റാൻഡിങ് വിജയകരമായി അപ്‌ഡേറ്റ് ചെയ്തു (After {$count} Results).");
    }

    /**
     * Calculate points breakdown per group for a given Result.
     *
     * @return array{
     *     points_by_group: array<int, int>,
     *     first_group: ?Group,
     *     second_group: ?Group,
     *     third_group: ?Group,
     *     top_group: ?Group,
     *     max_points: int
     * }
     */
    public function getResultPointsBreakdown(Result $result): array
    {
        $program = $result->program;
        $posPointsMap = PointCalculationService::getPositionPointsForProgram($program);
        $weight = (float) ($program->points_weight ?? 1.0);

        $pointsByGroup = [];
        $groupsMap = [];

        $placements = [
            1 => $result->firstEntry,
            2 => $result->secondEntry,
            3 => $result->thirdEntry,
        ];

        foreach ($placements as $pos => $entry) {
            if (! $entry || ! $entry->group_id) {
                continue;
            }

            $grpId = $entry->group_id;
            $groupsMap[$grpId] = $entry->group;

            $posPts = (int) round(($posPointsMap[$pos] ?? 0) * $weight);

            $grade = match ($pos) {
                1 => $result->first_grade,
                2 => $result->second_grade,
                3 => $result->third_grade,
                default => null,
            };

            $gradePts = 0;
            if ($grade && isset(PointCalculationService::GRADE_POINTS[strtoupper(trim($grade))])) {
                $gradePts = PointCalculationService::GRADE_POINTS[strtoupper(trim($grade))];
            }

            $totalForEntry = $posPts + $gradePts;
            $pointsByGroup[$grpId] = ($pointsByGroup[$grpId] ?? 0) + $totalForEntry;
        }

        $topGroupId = null;
        $maxPoints = 0;
        foreach ($pointsByGroup as $gId => $pts) {
            if ($pts > $maxPoints) {
                $maxPoints = $pts;
                $topGroupId = $gId;
            }
        }

        return [
            'points_by_group' => $pointsByGroup,
            'first_group' => $result->firstEntry?->group,
            'second_group' => $result->secondEntry?->group,
            'third_group' => $result->thirdEntry?->group,
            'top_group' => $topGroupId ? ($groupsMap[$topGroupId] ?? null) : null,
            'max_points' => $maxPoints,
        ];
    }
}
