<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Services\AuditLogger;
use App\Services\PointCalculationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function __construct(
        protected PointCalculationService $pointService
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
        $program = Program::with(['entries.student.group', 'entries.scoreSheets', 'scoringCriteria'])
            ->findOrFail($programId);

        $podium = PointCalculationService::determinePodiumForProgram($program);

        return view('admin.results.create', compact('program', 'podium'));
    }

    public function store(Request $request): RedirectResponse
    {
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
            'second_entry_id' => ['nullable', 'exists:program_entries,id'],
            'third_entry_id' => ['nullable', 'exists:program_entries,id'],
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
        }

        return redirect()->route('admin.results.index')->with('success', "Result for '{$result->program->name}' saved ({$result->status}).");
    }

    public function edit(Result $result): View
    {
        $result->load([
            'program.entries.student.group',
            'program.entries.scoreSheets',
            'firstEntry',
            'secondEntry',
            'thirdEntry',
        ]);

        $podium = PointCalculationService::determinePodiumForProgram($result->program);

        return view('admin.results.edit', compact('result', 'podium'));
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
        $validated = $request->validate([
            'first_entry_id' => ['required', 'exists:program_entries,id'],
            'second_entry_id' => ['nullable', 'exists:program_entries,id'],
            'third_entry_id' => ['nullable', 'exists:program_entries,id'],
            'status' => ['required', 'in:draft,submitted,under_review,verified,published'],
            'remarks' => ['nullable', 'string'],
        ]);

        $old = $result->toArray();

        if ($validated['status'] === 'published' && $result->status !== 'published') {
            $validated['published_at'] = Carbon::now();
            $validated['verified_by'] = Auth::id();
        }

        $result->update($validated);

        AuditLogger::log('update_result', $result, $old, $result->toArray());

        if ($result->status === 'published') {
            $this->issueCertificates($result);
            $this->pointService->recalculateAllPoints();
        }

        return redirect()->route('admin.results.index')->with('success', "Result for '{$result->program->name}' updated ({$result->status}).");
    }

    public function publish(Result $result): RedirectResponse
    {
        $old = $result->toArray();
        $result->update([
            'status' => 'published',
            'published_at' => Carbon::now(),
            'verified_by' => Auth::id(),
        ]);

        $result->program->update(['status' => 'completed']);

        $this->issueCertificates($result);
        $this->pointService->recalculateAllPoints();

        AuditLogger::log('publish_result', $result, $old, $result->toArray());

        return back()->with('success', "Result for '{$result->program->name}' is now PUBLISHED and points have been calculated.");
    }

    public function sendToAnnouncer(Result $result): RedirectResponse
    {
        $old = $result->status;
        $result->update(['status' => 'send']);

        AuditLogger::log('send_result_to_announcer', $result, ['status' => $old], ['status' => 'send']);

        return back()->with('success', "Result for '{$result->program->name}' has been sent to the Announcer Desk.");
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
        $search = $request->query('search');

        $query = Result::with(['program.category', 'program.stage', 'firstEntry.student', 'secondEntry.student', 'thirdEntry.student'])
            ->whereIn('status', ['published', 'announced']);

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
        $result->delete();

        $this->pointService->recalculateAllPoints();

        AuditLogger::log('undeclare_result', null, ['program' => $programName], null);

        return back()->with('success', "Result for '{$programName}' has been undeclared.");
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
}
