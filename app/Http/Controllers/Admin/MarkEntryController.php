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
        ]);

        // STRICT REQUIREMENT: Only candidates marked PRESENT in Call List appear in evaluation
        $presentEntries = $program->entries()
            ->where('status', 'verified')
            ->where('attendance_status', 'present')
            ->with(['student.group', 'group', 'scores.judge'])
            ->orderByRaw('CASE WHEN code_letter IS NULL THEN 1 ELSE 0 END, code_letter ASC, chest_number ASC')
            ->get();

        $absentCount = $program->entries()
            ->where('status', 'verified')
            ->where('attendance_status', 'absent')
            ->count();

        $judges = Judge::orderBy('name')->get();
        $podium = PointCalculationService::determinePodiumForProgram($program);

        return view('admin.mark-entry.show', compact('program', 'presentEntries', 'absentCount', 'judges', 'podium'));
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
                ->where('status', 'verified')
                ->where('attendance_status', 'present')
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

        // Automatically determine and assign podium winners from the latest saved marks
        PointCalculationService::autoAssignResultPodium($program);

        AuditLogger::log('save_marks', $program, null, [
            'program_id' => $program->id,
            'entries_count' => count($validated['scores']),
        ]);

        return back()->with('success', "Marks saved and podium winners updated for '{$program->name}'.");
    }

    public function publish(Request $request, Program $program): RedirectResponse
    {
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
        Program::ensureSchema();

        $zones = Program::ZONES;
        $selectedZone = $request->query('zone', $request->query('category'));
        $selectedProgramId = $request->query('program');
        $search = $request->query('search');

        $programsQuery = Program::query()
            ->with([
                'category',
                'zone',
                'stage',
                'result.firstEntry.student.group',
                'result.firstEntry.group',
                'result.firstEntry.scoreSheets',
                'result.secondEntry.student.group',
                'result.secondEntry.group',
                'result.secondEntry.scoreSheets',
                'result.thirdEntry.student.group',
                'result.thirdEntry.group',
                'result.thirdEntry.scoreSheets',
            ])
            ->withCount('entries');

        if ($selectedZone) {
            $programsQuery->where('eligibility', $selectedZone);
        }

        if ($search) {
            $programsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('malayalam_name', 'like', "%{$search}%");
            });
        }

        // Alphabetical A to Z ordering by name
        $allPrograms = $programsQuery->orderBy('name', 'asc')->get();
        $programs = $allPrograms;

        // Fetch scores for programs that don't have a published result yet
        $programsWithoutResultIds = $allPrograms->filter(fn ($p) => ! $p->result)->pluck('id');
        $evaluatedEntriesByProg = collect();

        if ($programsWithoutResultIds->isNotEmpty()) {
            $evaluatedEntries = ProgramEntry::whereIn('program_id', $programsWithoutResultIds)
                ->where('status', 'verified')
                ->where('attendance_status', 'present')
                ->whereHas('scoreSheets', fn ($sq) => $sq->where('is_submitted', true))
                ->with(['student.group', 'group', 'scoreSheets'])
                ->get()
                ->map(function ($entry) {
                    $submitted = $entry->scoreSheets->where('is_submitted', true);
                    $entry->computed_avg_score = $submitted->isNotEmpty() ? (float) $submitted->avg('total_score') : 0.0;
                    $entry->total_score = $entry->computed_avg_score;
                    $grade = PointCalculationService::getGradeFromScore($entry->computed_avg_score);
                    $entry->computed_grade = $grade['grade'] ?? '-';

                    return $entry;
                })
                ->filter(fn ($entry) => $entry->computed_avg_score > 0);

            $evaluatedEntriesByProg = $evaluatedEntries->groupBy('program_id');
        }

        // Map podium winners (1st, 2nd, 3rd) for each program
        $programsList = $allPrograms->map(function ($prog) use ($evaluatedEntriesByProg) {
            $first = null;
            $second = null;
            $third = null;

            if ($prog->result) {
                $first = $prog->result->firstEntry;
                $second = $prog->result->secondEntry;
                $third = $prog->result->thirdEntry;

                if ($first) {
                    $first->computed_avg_score = (float) ($first->scoreSheets->where('is_submitted', true)->avg('total_score') ?? 0);
                    $first->total_score = $first->computed_avg_score;
                    $grade = PointCalculationService::getGradeFromScore($first->computed_avg_score);
                    $first->computed_grade = $grade['grade'] ?? '-';
                }
                if ($second) {
                    $second->computed_avg_score = (float) ($second->scoreSheets->where('is_submitted', true)->avg('total_score') ?? 0);
                    $second->total_score = $second->computed_avg_score;
                    $grade = PointCalculationService::getGradeFromScore($second->computed_avg_score);
                    $second->computed_grade = $grade['grade'] ?? '-';
                }
                if ($third) {
                    $third->computed_avg_score = (float) ($third->scoreSheets->where('is_submitted', true)->avg('total_score') ?? 0);
                    $third->total_score = $third->computed_avg_score;
                    $grade = PointCalculationService::getGradeFromScore($third->computed_avg_score);
                    $third->computed_grade = $grade['grade'] ?? '-';
                }
            }

            // Fallback to evaluated entries if result not set
            if (! $first && ! $second && ! $third && $evaluatedEntriesByProg->has($prog->id)) {
                $sorted = $evaluatedEntriesByProg[$prog->id]->sortByDesc('computed_avg_score')->values();
                $first = $sorted->get(0);
                $second = $sorted->get(1);
                $third = $sorted->get(2);
            }

            $prog->podium = [
                'first' => $first,
                'second' => $second,
                'third' => $third,
            ];
            $prog->has_marks = (bool) (($first && $first->total_score > 0) || ($second && $second->total_score > 0) || ($third && $third->total_score > 0));

            return $prog;
        });

        $selectedProgram = null;
        $entries = collect();

        if ($selectedProgramId) {
            $selectedProgram = Program::with(['category', 'stage', 'zone', 'result'])->find($selectedProgramId);
            if ($selectedProgram) {
                $entries = ProgramEntry::where('program_id', $selectedProgram->id)
                    ->where('status', 'verified')
                    ->with(['student.group', 'group', 'scoreSheets.judge'])
                    ->orderByRaw('CASE WHEN code_letter IS NULL THEN 1 ELSE 0 END, code_letter ASC, chest_number ASC')
                    ->get()
                    ->map(function ($entry) use ($selectedProgram) {
                        $submitted = $entry->scoreSheets->where('is_submitted', true);
                        $avgScore = $submitted->isNotEmpty() ? (float) $submitted->avg('total_score') : 0.0;
                        $entry->total_score = $avgScore;
                        $grade = PointCalculationService::getGradeFromScore($avgScore);
                        $entry->computed_grade = $grade['grade'] ?? '-';

                        // Check podium rank from result or scores
                        $entry->rank = null;
                        if ($selectedProgram->result) {
                            if ($selectedProgram->result->first_entry_id === $entry->id) {
                                $entry->rank = 1;
                            } elseif ($selectedProgram->result->second_entry_id === $entry->id) {
                                $entry->rank = 2;
                            } elseif ($selectedProgram->result->third_entry_id === $entry->id) {
                                $entry->rank = 3;
                            }
                        }

                        return $entry;
                    });

                // Fallback rank calculation if no published result
                if (! $selectedProgram->result && $entries->where('total_score', '>', 0)->isNotEmpty()) {
                    $ranked = $entries->where('total_score', '>', 0)->sortByDesc('total_score')->values();
                    if ($first = $ranked->get(0)) {
                        $match = $entries->firstWhere('id', $first->id);
                        if ($match) {
                            $match->rank = 1;
                        }
                    }
                    if ($second = $ranked->get(1)) {
                        $match = $entries->firstWhere('id', $second->id);
                        if ($match) {
                            $match->rank = 2;
                        }
                    }
                    if ($third = $ranked->get(2)) {
                        $match = $entries->firstWhere('id', $third->id);
                        if ($match) {
                            $match->rank = 3;
                        }
                    }
                }
            }
        }

        $categories = collect();

        return view('admin.mark-entry.view-marks', compact(
            'zones',
            'categories',
            'programs',
            'programsList',
            'selectedZone',
            'selectedProgramId',
            'selectedProgram',
            'entries',
            'search'
        ));
    }

    public function marksHandler(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');

        // Strictly only show programs where judge evaluation is completed / marks submitted
        $query = Program::with(['category', 'result'])
            ->where(function ($q) {
                $q->whereHas('result', fn ($rq) => $rq->whereNotNull('first_entry_id'))
                    ->orWhere(function ($sub) {
                        $sub->whereHas('entries', function ($eq) {
                            $eq->where('attendance_status', 'present')
                                ->whereHas('scoreSheets', fn ($sq) => $sq->where('is_submitted', true));
                        });
                    });
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($status) {
            if ($status === 'passed') {
                $query->where(function ($q) {
                    $q->whereHas('result', fn ($rq) => $rq->where('status', 'passed'))
                        ->orWhere(function ($sub) {
                            $sub->doesntHave('result')
                                ->orWhereHas('result', fn ($rq) => $rq->whereNull('status')->orWhere('status', 'draft'));
                        });
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
            PointCalculationService::autoAssignResultPodium($program);
            $result = $program->fresh()->result ?? $result;
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
