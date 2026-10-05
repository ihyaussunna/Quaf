<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GreenRoomCall;
use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\ScoreSheet;
use App\Models\Stage;
use App\Services\AuditLogger;
use App\Services\PointCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CallListController extends Controller
{
    /**
     * Call List & Attendance Center.
     */
    public function index(Request $request): View
    {
        Program::ensureSchema();

        $search = $request->query('search');
        $zone = $request->query('zone');
        $stageId = $request->query('stage_id');
        $programId = $request->query('program_id') ?: $request->query('program');
        $lockStatus = $request->query('lock_status');
        $programStatus = $request->query('program_status');
        $attendance = $request->query('attendance');
        $evalStatus = $request->query('eval_status');

        $zones = Program::ZONES;
        $stages = Stage::orderBy('name')->get();
        $allPrograms = Program::orderBy('name')->get();
        $groups = Group::orderBy('name')->get();

        // Macro counters across the whole festival system
        $stats = [
            'total_programs' => Program::count(),
            'locked_programs' => Program::where('is_call_list_locked', true)->count(),
            'pending_programs' => Program::where('status', '!=', 'completed')->count(),
            'completed_programs' => Program::where('status', 'completed')->count(),
            'open_programs' => Program::where(function ($q) {
                $q->whereNull('is_call_list_locked')->orWhere('is_call_list_locked', false);
            })->count(),
            'total_entries' => ProgramEntry::where('status', 'verified')->count(),
            'present' => ProgramEntry::where('status', 'verified')->where('attendance_status', 'present')->count(),
            'absent' => ProgramEntry::where('status', 'verified')->where('attendance_status', 'absent')->count(),
            'waiting' => ProgramEntry::where('status', 'verified')->where(function ($q) {
                $q->whereNull('attendance_status')->orWhere('attendance_status', 'waiting');
            })->count(),
            'evaluated' => ProgramEntry::where('status', 'verified')->whereHas('scoreSheets', fn ($q) => $q->where('is_submitted', true))->count(),
            'pending_evaluation' => ProgramEntry::where('status', 'verified')->where('attendance_status', 'present')->whereDoesntHave('scoreSheets', fn ($q) => $q->where('is_submitted', true))->count(),
        ];

        $selectedProgram = null;
        $entries = collect();
        $programStats = [];

        if ($programId) {
            $selectedProgram = Program::with(['category', 'stage', 'schedule'])->find($programId);

            if ($selectedProgram) {
                $entriesQuery = ProgramEntry::where('program_id', $selectedProgram->id)
                    ->where('status', 'verified')
                    ->with(['student.group', 'group', 'scoreSheets.judge']);

                if ($search) {
                    $entriesQuery->where(function ($q) use ($search) {
                        $q->where('chest_number', 'like', "%{$search}%")
                            ->orWhere('code_letter', 'like', "%{$search}%")
                            ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('student_id', 'like', "%{$search}%"))
                            ->orWhereHas('group', fn ($gq) => $gq->where('name', 'like', "%{$search}%"));
                    });
                }

                if ($attendance === 'present') {
                    $entriesQuery->where('attendance_status', 'present');
                } elseif ($attendance === 'absent') {
                    $entriesQuery->where('attendance_status', 'absent');
                } elseif ($attendance === 'waiting') {
                    $entriesQuery->where(function ($q) {
                        $q->whereNull('attendance_status')->orWhere('attendance_status', 'waiting');
                    });
                }

                if ($evalStatus === 'evaluated') {
                    $entriesQuery->whereHas('scoreSheets', fn ($q) => $q->where('is_submitted', true));
                } elseif ($evalStatus === 'pending') {
                    $entriesQuery->where('attendance_status', 'present')
                        ->whereDoesntHave('scoreSheets', fn ($q) => $q->where('is_submitted', true));
                }

                $entries = $entriesQuery->orderByRaw('CASE WHEN code_letter IS NULL THEN 1 ELSE 0 END, code_letter ASC, chest_number ASC')->get();

                // Compute stats for selected program
                $allProgEntries = ProgramEntry::where('program_id', $selectedProgram->id)->where('status', 'verified')->get();
                $programStats = [
                    'total' => $allProgEntries->count(),
                    'present' => $allProgEntries->where('attendance_status', 'present')->count(),
                    'absent' => $allProgEntries->where('attendance_status', 'absent')->count(),
                    'waiting' => $allProgEntries->where('attendance_status', '!=', 'present')->where('attendance_status', '!=', 'absent')->count(),
                    'evaluated' => $allProgEntries->filter(fn ($e) => $e->evaluation_status === 'EVALUATED')->count(),
                    'pending_evaluation' => $allProgEntries->filter(fn ($e) => $e->evaluation_status === 'EVALUATION_PENDING')->count(),
                ];
            }
        }

        // Program-wise Call Lists (One Program = One Call List)
        $programsQuery = Program::with(['category', 'stage', 'schedule'])
            ->withCount([
                'entries as total_count' => fn ($q) => $q->where('status', 'verified'),
                'entries as present_count' => fn ($q) => $q->where('status', 'verified')->where('attendance_status', 'present'),
                'entries as absent_count' => fn ($q) => $q->where('status', 'verified')->where('attendance_status', 'absent'),
                'entries as waiting_count' => fn ($q) => $q->where('status', 'verified')->where(function ($sq) {
                    $sq->whereNull('attendance_status')->orWhere('attendance_status', 'waiting');
                }),
                'entries as evaluated_count' => fn ($q) => $q->where('status', 'verified')->whereHas('scoreSheets', fn ($sq) => $sq->where('is_submitted', true)),
            ]);

        if ($search && ! $selectedProgram) {
            $programsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('malayalam_name', 'like', "%{$search}%");
            });
        }

        if ($zone) {
            $programsQuery->where('eligibility', $zone);
        }

        if ($stageId) {
            $programsQuery->where('stage_id', $stageId);
        }

        if ($lockStatus === 'locked') {
            $programsQuery->where('is_call_list_locked', true);
        } elseif ($lockStatus === 'open') {
            $programsQuery->where(function ($q) {
                $q->whereNull('is_call_list_locked')->orWhere('is_call_list_locked', false);
            });
        }

        if ($programStatus) {
            $programsQuery->where('status', $programStatus);
        }

        $programCallLists = $programsQuery->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.call-list.index', compact(
            'programCallLists',
            'selectedProgram',
            'entries',
            'stats',
            'programStats',
            'zones',
            'stages',
            'allPrograms',
            'groups',
            'search',
            'zone',
            'stageId',
            'programId',
            'lockStatus',
            'programStatus',
            'attendance',
            'evalStatus'
        ));
    }

    /**
     * Instant attendance toggle for admin.
     */
    public function markAttendance(Request $request, ProgramEntry $entry): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:present,absent,waiting'],
        ]);

        $oldStatus = $entry->attendance_status;
        $newStatus = $validated['status'];

        $entry->attendance_status = $newStatus;

        if ($newStatus === 'present' && empty($entry->code_letter)) {
            $assignedCodes = ProgramEntry::where('program_id', $entry->program_id)
                ->whereNotNull('code_letter')
                ->pluck('code_letter')
                ->all();

            for ($i = 0; $i < 500; $i++) {
                $candidate = ProgramEntry::formatCodeLetter($i);
                if (! in_array($candidate, $assignedCodes, true)) {
                    $entry->code_letter = $candidate;
                    break;
                }
            }
        } elseif ($newStatus === 'absent') {
            $entry->code_letter = null;
        }

        $entry->save();

        $call = GreenRoomCall::where('entry_id', $entry->id)->first();
        if ($call) {
            $callStatus = match ($newStatus) {
                'present' => 'ready',
                'absent' => 'absent',
                default => 'waiting',
            };
            $call->update(['status' => $callStatus]);
        }

        AuditLogger::log('admin_attendance_change', $entry, ['attendance_status' => $oldStatus], [
            'entry_id' => $entry->id,
            'previous_status' => $oldStatus,
            'new_status' => $newStatus,
            'code_letter' => $entry->code_letter,
        ]);

        $msg = 'Attendance updated to '.strtoupper($newStatus)." for Chest #{$entry->chest_number}.";

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'entry_id' => $entry->id,
                'attendance_status' => $entry->attendance_status,
                'code_letter' => $entry->code_letter,
                'display_code' => $entry->display_code,
                'evaluation_status' => $entry->evaluation_status,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Toggle lock status on program call list.
     */
    public function toggleLock(Program $program): RedirectResponse
    {
        Program::ensureSchema();

        $newStatus = ! ((bool) ($program->is_call_list_locked ?? false));
        $program->update(['is_call_list_locked' => $newStatus]);

        $statusText = $newStatus ? 'LOCKED' : 'UNLOCKED';
        AuditLogger::log('admin_toggle_call_list_lock', $program, null, ['is_call_list_locked' => $newStatus]);

        return back()->with('success', "Call list for '{$program->name}' has been {$statusText}.");
    }

    /**
     * Reset all programs to 'upcoming', unlock call lists, and reset attendance/calls back to waiting.
     * Preserves student registrations and group entries.
     */
    public function resetAll(): RedirectResponse
    {
        Program::ensureSchema();

        DB::transaction(function () {
            Program::query()->update([
                'status' => 'upcoming',
                'is_call_list_locked' => false,
            ]);

            DB::table('results')->delete();
            DB::table('score_sheets')->delete();
            DB::table('green_room_calls')->delete();
            DB::table('points_transactions')->delete();
            DB::table('groups')->update(['points_cache' => 0, 'rank_cache' => 1]);
            DB::table('students')->update(['points_cache' => 0]);

            // Reset attendance status and code letter on all existing entries WITHOUT deleting student registrations
            ProgramEntry::query()->update([
                'attendance_status' => 'waiting',
                'code_letter' => null,
            ]);
        });

        AuditLogger::log('admin_reset_festival_call_lists_and_status', null, null, [
            'action' => 'Reset attendance statuses, call list locks, evaluations, and results while preserving candidate registrations',
        ]);

        return back()->with('success', 'All call lists and programs have been reset to starting state. Student registrations were preserved.');
    }

    /**
     * Reset a single program to upcoming and reset its attendance/calls without deleting entries.
     */
    public function resetProgram(Program $program): RedirectResponse
    {
        Program::ensureSchema();

        DB::transaction(function () use ($program) {
            $program->update([
                'status' => 'upcoming',
                'is_call_list_locked' => false,
            ]);

            DB::table('results')->where('program_id', $program->id)->delete();
            DB::table('score_sheets')->where('program_id', $program->id)->delete();
            DB::table('green_room_calls')->where('program_id', $program->id)->delete();

            // Reset attendance and code letters on entries of this program WITHOUT deleting them
            ProgramEntry::where('program_id', $program->id)->update([
                'attendance_status' => 'waiting',
                'code_letter' => null,
            ]);
        });

        AuditLogger::log('admin_reset_program_call_list', $program, null, [
            'program_id' => $program->id,
            'name' => $program->name,
        ]);

        return back()->with('success', "Program '{$program->name}' attendance and call status have been reset. Student registrations were preserved.");
    }

    /**
     * Shuffle and assign random anonymous code letters to present participants.
     */
    public function shuffle(Program $program): RedirectResponse
    {
        if ($program->is_call_list_locked) {
            return back()->with('error', 'Call list is locked. Cannot shuffle codes.');
        }

        $presentEntries = ProgramEntry::where('program_id', $program->id)
            ->where('status', 'verified')
            ->where('attendance_status', 'present')
            ->get();

        if ($presentEntries->isEmpty()) {
            return back()->with('error', 'No present participants found to assign codes.');
        }

        $shuffled = $presentEntries->shuffle()->values();

        foreach ($shuffled as $index => $entry) {
            $codeLetter = ProgramEntry::formatCodeLetter($index);
            $entry->update(['code_letter' => $codeLetter]);
        }

        AuditLogger::log('admin_shuffle_codes', $program, null, [
            'program_id' => $program->id,
            'assigned_count' => $shuffled->count(),
        ]);

        return back()->with('success', "Assigned random code letters to {$shuffled->count()} present participants.");
    }

    /**
     * Evaluation Monitor Dashboard.
     */
    public function evaluationMonitor(Request $request): View
    {
        Program::ensureSchema();

        $search = $request->query('search');
        $zone = $request->query('zone');
        $stageId = $request->query('stage_id');
        $status = $request->query('status');

        $query = Program::with(['category', 'stage', 'schedule', 'result', 'judges'])
            ->withCount([
                'entries as total_entries_count' => fn ($q) => $q->where('status', 'verified'),
                'entries as present_entries_count' => fn ($q) => $q->where('status', 'verified')->where('attendance_status', 'present'),
                'entries as absent_entries_count' => fn ($q) => $q->where('status', 'verified')->where('attendance_status', 'absent'),
                'entries as evaluated_entries_count' => fn ($q) => $q->where('status', 'verified')->whereHas('scoreSheets', fn ($sq) => $sq->where('is_submitted', true)),
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($zone) {
            $query->where('eligibility', $zone);
        }

        if ($stageId) {
            $query->where('stage_id', $stageId);
        }

        if ($status === 'completed') {
            $query->where('status', 'completed');
        } elseif ($status === 'in_progress') {
            $query->where('status', 'in_progress');
        } elseif ($status === 'upcoming') {
            $query->where('status', 'upcoming');
        }

        $programs = $query->orderBy('name')->paginate(20)->withQueryString();

        // Calculate macro statistics: program-level evaluation metrics
        $evaluatedProgramsCount = Program::where(function ($q) {
            $q->whereHas('result', fn ($rq) => $rq->whereNotNull('first_entry_id'))
                ->orWhere(function ($sub) {
                    $sub->whereHas('entries', function ($eq) {
                        $eq->where('attendance_status', 'present')
                            ->whereHas('scoreSheets', fn ($sq) => $sq->where('is_submitted', true));
                    });
                });
        })->count();

        $stats = [
            'total_programs' => Program::count(),
            'evaluated_programs' => $evaluatedProgramsCount,
            'pending_programs' => max(0, Program::count() - $evaluatedProgramsCount),
            'completed_programs' => Program::where('status', 'completed')->count(),
            'in_progress_programs' => Program::where('status', 'in_progress')->count(),
            'upcoming_programs' => Program::where('status', 'upcoming')->count(),
        ];

        $zones = Program::ZONES;
        $stages = Stage::orderBy('name')->get();

        return view('admin.evaluation-monitor.index', compact(
            'programs',
            'stats',
            'zones',
            'stages',
            'search',
            'zone',
            'stageId',
            'status'
        ));
    }

    /**
     * Detailed evaluation records for a single program.
     */
    public function evaluationDetails(Program $program): View
    {
        $program->load([
            'category',
            'stage',
            'schedule',
            'scoringCriteria',
            'judges',
            'result',
            'entries.student.group',
            'entries.group',
            'entries.scoreSheets.judge',
        ]);

        $entries = $program->entries()
            ->where('status', 'verified')
            ->with(['student.group', 'group', 'scoreSheets.judge'])
            ->get()
            ->map(function ($entry) {
                $submittedSheets = $entry->scoreSheets->where('is_submitted', true);
                $entry->submitted_score_count = $submittedSheets->count();
                $entry->average_score = $submittedSheets->isNotEmpty() ? (float) $submittedSheets->avg('total_score') : 0.0;
                $entry->grade = PointCalculationService::getGradeFromScore($entry->average_score)['grade'] ?? '-';

                return $entry;
            })
            ->sort(function ($a, $b) {
                // Present first, then by average score descending, then by code letter
                if ($a->attendance_status === 'present' && $b->attendance_status !== 'present') {
                    return -1;
                }
                if ($a->attendance_status !== 'present' && $b->attendance_status === 'present') {
                    return 1;
                }
                if ($a->average_score != $b->average_score) {
                    return $b->average_score <=> $a->average_score;
                }

                return strcmp($a->code_letter ?? '', $b->code_letter ?? '');
            })
            ->values();

        $judges = $program->judges;

        return view('admin.evaluation-monitor.show', compact('program', 'entries', 'judges'));
    }

    /**
     * Dedicated Judge Marks Dashboard.
     */
    public function judgeMarks(Request $request): View
    {
        $search = $request->query('search');
        $programId = $request->query('program_id');
        $judgeId = $request->query('judge_id');
        $zone = $request->query('zone');
        $stageId = $request->query('stage_id');

        $query = ScoreSheet::with([
            'judge',
            'program.category',
            'program.stage',
            'program.scoringCriteria',
            'entry.student.group',
            'entry.group',
        ])->where('is_submitted', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('entry', function ($eq) use ($search) {
                    $eq->where('chest_number', 'like', "%{$search}%")
                        ->orWhere('code_letter', 'like', "%{$search}%")
                        ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
                })->orWhereHas('program', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                    ->orWhereHas('judge', fn ($jq) => $jq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($programId) {
            $query->where('program_id', $programId);
        }

        if ($judgeId) {
            $query->where('judge_id', $judgeId);
        }

        if ($zone) {
            $query->whereHas('program', fn ($pq) => $pq->where('eligibility', $zone));
        }

        if ($stageId) {
            $query->whereHas('program', fn ($pq) => $pq->where('stage_id', $stageId));
        }

        $scoreSheets = $query->latest('submitted_at')->paginate(30)->withQueryString();

        $programs = Program::orderBy('name')->get();
        $judges = Judge::orderBy('name')->get();
        $stages = Stage::orderBy('name')->get();
        $zones = Program::ZONES;

        return view('admin.judge-marks.index', compact(
            'scoreSheets',
            'programs',
            'judges',
            'stages',
            'zones',
            'search',
            'programId',
            'judgeId',
            'zone',
            'stageId'
        ));
    }
}
