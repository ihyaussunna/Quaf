<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\ScoreSheet;
use App\Services\AuditLogger;
use App\Services\PointCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MarkEntryController extends Controller
{
    public function index(Request $request): View
    {
        $zone = $request->query('zone');
        $categoryId = $request->query('category');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Program::with(['category', 'stage', 'result'])
            ->withCount([
                'entries',
                'entries as evaluated_entries_count' => function ($q) {
                    $q->whereHas('scores');
                },
            ]);

        if ($zone) {
            $query->where('eligibility', $zone);
        } elseif ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($status === 'published') {
            $query->whereHas('result', fn ($q) => $q->where('is_published', true));
        } elseif ($status === 'pending') {
            $query->whereDoesntHave('result', fn ($q) => $q->where('is_published', true));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = ProgramCategory::orderBy('name')->get();
        $zones = Program::ZONES;

        return view('admin.mark-entry.index', compact('programs', 'categories', 'zones', 'zone', 'categoryId', 'status', 'search'));
    }

    public function show(Program $program): View
    {
        $program->load([
            'category',
            'stage',
            'scoringCriteria',
            'result.firstEntry.student',
            'result.secondEntry.student',
            'result.thirdEntry.student',
            'entries.student.group',
            'entries.group',
            'entries.scores.judge',
        ]);

        $judges = Judge::orderBy('name')->get();

        return view('admin.mark-entry.show', compact('program', 'judges'));
    }

    public function saveMarks(Request $request, Program $program): RedirectResponse
    {
        $validated = $request->validate([
            'judge_id' => ['nullable', 'exists:judges,id'],
            'scores' => ['required', 'array'],
            'scores.*.total_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'scores.*.remarks' => ['nullable', 'string'],
        ]);

        // Default to first judge or primary judge if not provided
        $judgeId = $validated['judge_id'] ?? Judge::first()?->id;

        if (! $judgeId) {
            return back()->with('error', 'Please register at least one judge before entering marks.');
        }

        DB::transaction(function () use ($validated, $judgeId, $program) {
            $validEntries = ProgramEntry::whereIn('id', array_keys($validated['scores']))
                ->where('program_id', $program->id)
                ->pluck('id')
                ->flip();

            foreach ($validated['scores'] as $entryId => $scoreData) {
                if (! isset($validEntries[$entryId])) {
                    continue;
                }

                ScoreSheet::updateOrCreate(
                    [
                        'judge_id' => $judgeId,
                        'program_id' => $program->id,
                        'entry_id' => $entryId,
                    ],
                    [
                        'total_score' => $scoreData['total_score'],
                        'remarks' => $scoreData['remarks'] ?? null,
                        'is_submitted' => true,
                    ]
                );
            }
        });

        AuditLogger::log('save_marks', $program, null, [
            'program_id' => $program->id,
            'entries_count' => count($validated['scores']),
        ]);

        return back()->with('success', "Marks saved for '{$program->name}'.");
    }

    public function publish(Request $request, Program $program): RedirectResponse
    {
        $validated = $request->validate([
            'first_entry_id' => ['required', 'exists:program_entries,id'],
            'second_entry_id' => ['nullable', 'exists:program_entries,id', 'different:first_entry_id'],
            'third_entry_id' => ['nullable', 'exists:program_entries,id', 'different:first_entry_id', 'different:second_entry_id'],
            'remarks' => ['nullable', 'string'],
        ]);

        $result = Result::updateOrCreate(
            ['program_id' => $program->id],
            [
                'first_entry_id' => $validated['first_entry_id'],
                'second_entry_id' => $validated['second_entry_id'] ?? null,
                'third_entry_id' => $validated['third_entry_id'] ?? null,
                'status' => 'published',
                'published_at' => now(),
                'verified_by' => auth()->id(),
                'remarks' => $validated['remarks'] ?? null,
            ]
        );

        $program->update(['status' => 'completed']);

        // High-speed incremental points calculation for the published program & leaderboard
        app(PointCalculationService::class)->recalculateForProgram($program);

        AuditLogger::log('publish_result', $result, null, $result->toArray());

        return back()->with('success', "Result for '{$program->name}' published successfully! Team leaderboard points updated in real-time.");
    }

    public function viewMarks(Request $request): View
    {
        $zones = Program::ZONES;
        $selectedZone = $request->query('zone', $request->query('category'));
        $selectedProgramId = $request->query('program');

        $programsQuery = Program::query();
        if ($selectedZone) {
            $programsQuery->where('eligibility', $selectedZone);
        }
        $programs = $programsQuery->orderBy('name')->get();

        $selectedProgram = null;
        $entries = collect();

        if ($selectedProgramId) {
            $selectedProgram = Program::with(['category'])->find($selectedProgramId);
            if ($selectedProgram) {
                $entries = ProgramEntry::where('program_id', $selectedProgram->id)
                    ->with(['student.group', 'group', 'scores'])
                    ->get()
                    ->map(function ($entry) {
                        $entry->total_score = $entry->scores->sum('total_score');

                        return $entry;
                    });
            }
        }

        $categories = collect();

        return view('admin.mark-entry.view-marks', compact(
            'zones',
            'categories',
            'programs',
            'selectedZone',
            'selectedProgramId',
            'selectedProgram',
            'entries'
        ));
    }

    public function marksHandler(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = Program::with(['category', 'result']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($status) {
            if ($status === 'passed') {
                $query->where(function ($q) {
                    $q->doesntHave('result')
                        ->orWhereHas('result', fn ($rq) => $rq->where('status', 'passed'));
                });
            } else {
                $query->whereHas('result', fn ($rq) => $rq->where('status', $status));
            }
        }

        $programs = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.mark-entry.handler', compact('programs', 'search', 'status'));
    }

    public function updateHandlerStatus(Request $request, Program $program): RedirectResponse
    {
        $action = $request->input('action'); // verify, send, announced, reset

        $result = $program->result ?? Result::firstOrNew(['program_id' => $program->id]);

        // Auto-assign top 3 winner entries from recorded scores if not already set
        if (! $result->first_entry_id) {
            $topEntries = ProgramEntry::where('program_id', $program->id)
                ->where('attendance_status', 'present')
                ->with('scores')
                ->get()
                ->filter(fn ($e) => $e->scores->isNotEmpty() && (float) $e->scores->avg('total_score') > 0)
                ->sortByDesc(fn ($e) => (float) $e->scores->avg('total_score'))
                ->values();

            if ($topEntries->isNotEmpty()) {
                $result->first_entry_id = $topEntries[0]->id;
                $result->second_entry_id = $topEntries->get(1)?->id;
                $result->third_entry_id = $topEntries->get(2)?->id;
            }
        }

        if ($action === 'verify') {
            $result->status = 'verified';
        } elseif ($action === 'send') {
            $result->status = 'send';
        } elseif ($action === 'announced') {
            $result->status = 'announced';
        } elseif ($action === 'reset') {
            $result->status = 'passed';
        }
        $result->save();

        AuditLogger::log('update_handler_status', $program, null, ['action' => $action, 'status' => $result->status]);

        return back()->with('success', "Status updated to '{$result->status}' for '{$program->name}'.");
    }

    public function markCheck(Request $request): View
    {
        $zones = Program::ZONES;
        $selectedZone = $request->query('zone', $request->query('category'));
        $selectedProgramId = $request->query('program');

        $programsQuery = Program::query();
        if ($selectedZone) {
            $programsQuery->where('eligibility', $selectedZone);
        }
        $programs = $programsQuery->orderBy('name')->get();

        $selectedProgram = null;
        $rankedEntries = collect();

        if ($selectedProgramId) {
            $selectedProgram = Program::with(['category', 'stage', 'result'])->find($selectedProgramId);
            if ($selectedProgram) {
                $entries = ProgramEntry::where('program_id', $selectedProgram->id)
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
                        $item->computed_rank = '';

                        continue;
                    }

                    if ($prevScore === null || abs($item->computed_score - $prevScore) > 0.001) {
                        $currentRank++;
                        $prevScore = $item->computed_score;
                    }

                    $item->computed_rank = $rankMap[$currentRank] ?? '';
                }
                $rankedEntries = $entries;
            }
        }

        $categories = collect();

        return view('admin.mark-entry.check', compact(
            'zones',
            'categories',
            'programs',
            'selectedZone',
            'selectedProgramId',
            'selectedProgram',
            'rankedEntries'
        ));
    }
}
