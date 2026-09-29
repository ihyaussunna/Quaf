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
        $programId = $request->query('program_id');
        $groupId = $request->query('group_id');
        $attendance = $request->query('attendance');
        $evalStatus = $request->query('eval_status');

        $query = ProgramEntry::with([
            'program.category',
            'program.stage',
            'student.group',
            'group',
            'scoreSheets.judge',
        ])->where('status', 'verified');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('chest_number', 'like', "%{$search}%")
                    ->orWhere('code_letter', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('student_id', 'like', "%{$search}%"))
                    ->orWhereHas('program', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                    ->orWhereHas('group', fn ($gq) => $gq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($zone) {
            $query->whereHas('program', fn ($pq) => $pq->where('eligibility', $zone));
        }

        if ($stageId) {
            $query->whereHas('program', fn ($pq) => $pq->where('stage_id', $stageId));
        }

        if ($programId) {
            $query->where('program_id', $programId);
        }

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        if ($attendance === 'present') {
            $query->where('attendance_status', 'present');
        } elseif ($attendance === 'absent') {
            $query->where('attendance_status', 'absent');
        } elseif ($attendance === 'waiting') {
            $query->where(function ($q) {
                $q->whereNull('attendance_status')->orWhere('attendance_status', 'waiting');
            });
        }

        if ($evalStatus === 'evaluated') {
            $query->whereHas('scoreSheets', fn ($q) => $q->where('is_submitted', true));
        } elseif ($evalStatus === 'pending') {
            $query->where('attendance_status', 'present')
                ->whereDoesntHave('scoreSheets', fn ($q) => $q->where('is_submitted', true));
        }

        // Live Operational Counters across the whole festival system
        $stats = [
            'total' => ProgramEntry::where('status', 'verified')->count(),
            'present' => ProgramEntry::where('status', 'verified')->where('attendance_status', 'present')->count(),
            'absent' => ProgramEntry::where('status', 'verified')->where('attendance_status', 'absent')->count(),
            'waiting' => ProgramEntry::where('status', 'verified')->where(function ($q) {
                $q->whereNull('attendance_status')->orWhere('attendance_status', 'waiting');
            })->count(),
            'evaluated' => ProgramEntry::where('status', 'verified')->whereHas('scoreSheets', fn ($q) => $q->where('is_submitted', true))->count(),
            'pending_evaluation' => ProgramEntry::where('status', 'verified')->where('attendance_status', 'present')->whereDoesntHave('scoreSheets', fn ($q) => $q->where('is_submitted', true))->count(),
        ];

        $entries = $query->orderBy('program_id')
            ->orderByRaw('CASE WHEN code_letter IS NULL THEN 1 ELSE 0 END, code_letter ASC, chest_number ASC')
            ->paginate(25)
            ->withQueryString();

        $zones = Program::ZONES;
        $stages = Stage::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $groups = Group::orderBy('name')->get();

        return view('admin.call-list.index', compact(
            'entries',
            'stats',
            'zones',
            'stages',
            'programs',
            'groups',
            'search',
            'zone',
            'stageId',
            'programId',
            'groupId',
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

        // Calculate macro statistics
        $stats = [
            'total_programs' => Program::count(),
            'completed_programs' => Program::where('status', 'completed')->count(),
            'total_participants' => ProgramEntry::where('status', 'verified')->count(),
            'present_participants' => ProgramEntry::where('status', 'verified')->where('attendance_status', 'present')->count(),
            'evaluated_participants' => ProgramEntry::where('status', 'verified')->whereHas('scoreSheets', fn ($q) => $q->where('is_submitted', true))->count(),
            'pending_evaluations' => ProgramEntry::where('status', 'verified')->where('attendance_status', 'present')->whereDoesntHave('scoreSheets', fn ($q) => $q->where('is_submitted', true))->count(),
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
