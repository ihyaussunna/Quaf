<?php

namespace App\Http\Controllers\GreenRoom;

use App\Http\Controllers\Controller;
use App\Models\GreenRoomCall;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Stage;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class GreenRoomController extends Controller
{
    public function index(Request $request): View
    {
        $stages = Stage::all();
        $selectedStageId = $request->query('stage_id', $stages->first()?->id);
        $stage = Stage::with(['currentProgram.category', 'nextProgram.category'])->find($selectedStageId);

        $currentProgram = $stage?->currentProgram;
        $nextProgram = $stage?->nextProgram;

        // Upcoming programs for this stage
        $upcomingPrograms = [];
        if ($stage) {
            $upcomingPrograms = Program::where('stage_id', $stage->id)
                ->where('status', 'upcoming')
                ->where('id', '!=', $currentProgram?->id)
                ->where('id', '!=', $nextProgram?->id)
                ->orderBy('scheduled_time')
                ->take(3)
                ->get();
        }

        // Participants in green room queue for current program (or next program if current finished)
        $activeProgram = $currentProgram ?? $nextProgram;
        $calls = collect();

        if ($activeProgram) {
            // Ensure all verified entries have a green room call record
            $verifiedEntries = $activeProgram->entries()->where('status', 'verified')->get();
            foreach ($verifiedEntries as $index => $entry) {
                GreenRoomCall::firstOrCreate(
                    [
                        'program_id' => $activeProgram->id,
                        'entry_id' => $entry->id,
                    ],
                    [
                        'order_num' => $index + 1,
                        'status' => 'waiting',
                    ]
                );
            }

            $calls = GreenRoomCall::where('program_id', $activeProgram->id)
                ->with(['entry.student.group', 'entry.group'])
                ->orderBy('order_num')
                ->get();
        }

        return view('greenroom.index', compact('stages', 'stage', 'selectedStageId', 'currentProgram', 'nextProgram', 'upcomingPrograms', 'activeProgram', 'calls'));
    }

    public function updateStatus(Request $request, GreenRoomCall $call): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:waiting,checked_in,ready,called,on_stage,completed,absent'],
        ]);

        $status = $validated['status'];
        $updates = ['status' => $status];

        if ($status === 'called') {
            $updates['called_at'] = Carbon::now();
        } elseif ($status === 'on_stage') {
            $updates['stage_entered_at'] = Carbon::now();
        }

        $call->update($updates);

        AuditLogger::log('green_room_status_change', $call, null, [
            'call_id' => $call->id,
            'entry_id' => $call->entry_id,
            'status' => $status,
        ]);

        return back()->with('success', "Participant Chest #{$call->entry->chest_number} marked as ".strtoupper($status));
    }

    public function callNext(Program $program): RedirectResponse
    {
        // Find next participant in 'ready' or 'checked_in' state
        $next = GreenRoomCall::where('program_id', $program->id)
            ->whereIn('status', ['ready', 'checked_in', 'waiting'])
            ->orderBy('order_num')
            ->first();

        if (! $next) {
            return back()->with('info', 'No more waiting participants in this program.');
        }

        $next->update([
            'status' => 'called',
            'called_at' => Carbon::now(),
        ]);

        AuditLogger::log('green_room_call_next', $next, null, ['status' => 'called']);

        return back()->with('success', "CALLED NEXT: Chest #{$next->entry->chest_number} ({$next->entry->student?->name})");
    }

    public function markAttendance(Request $request, ProgramEntry $entry): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $isAdmin = in_array($user?->role, ['admin', 'super_admin']);

        $program = $entry->program;
        $window = $program?->getCallListWindowState();

        if (! $isAdmin && ! ($window['is_open'] ?? false)) {
            $msg = $window['message'] ?? 'ഈ പ്രോഗ്രാമിന്റെ കോൾ ലിസ്റ്റ് ഇപ്പോൾ എഡിറ്റ് ചെയ്യാൻ അനുവാദമില്ല.';
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:present,absent,waiting'],
        ]);

        $oldStatus = $entry->attendance_status;
        $newStatus = $validated['status'];

        $entry->attendance_status = $newStatus;

        // Auto-assign next code letter if marked present and code letter is missing
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
            // If marked absent, clear code letter automatically
            $entry->code_letter = null;
        }

        $entry->save();

        // Sync with GreenRoomCall if exists
        $call = GreenRoomCall::where('entry_id', $entry->id)->first();
        if ($call) {
            $callStatus = match ($newStatus) {
                'present' => 'ready',
                'absent' => 'absent',
                default => 'waiting',
            };
            $call->update(['status' => $callStatus]);
        }

        AuditLogger::log('green_room_attendance', $entry, ['attendance_status' => $oldStatus], [
            'entry_id' => $entry->id,
            'chest_number' => $entry->chest_number,
            'previous_status' => $oldStatus,
            'new_status' => $newStatus,
            'code_letter' => $entry->code_letter,
        ]);

        $statusText = strtoupper($newStatus);
        $message = "ചെസ്റ്റ് #{$entry->chest_number} ഹാജർ നില: {$statusText}";

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            $programEntries = ProgramEntry::where('program_id', $entry->program_id)->with('scoreSheets')->get();

            return response()->json([
                'success' => true,
                'message' => $message,
                'entry_id' => $entry->id,
                'attendance_status' => $entry->attendance_status,
                'code_letter' => $entry->code_letter,
                'display_code' => $entry->display_code,
                'evaluation_status' => $entry->evaluation_status,
                'stats' => [
                    'total' => $programEntries->count(),
                    'present' => $programEntries->where('attendance_status', 'present')->count(),
                    'absent' => $programEntries->where('attendance_status', 'absent')->count(),
                    'waiting' => $programEntries->where('attendance_status', '!=', 'present')->where('attendance_status', '!=', 'absent')->count(),
                    'evaluated' => $programEntries->filter(fn ($e) => $e->evaluation_status === 'EVALUATED')->count(),
                    'pending_evaluation' => $programEntries->filter(fn ($e) => $e->evaluation_status === 'EVALUATION_PENDING')->count(),
                ],
            ]);
        }

        return back()->with('success', $message);
    }

    public function generateCodeLetters(Program $program): RedirectResponse
    {
        $user = auth()->user();
        $isAdmin = in_array($user?->role, ['admin', 'super_admin']);

        $window = $program->getCallListWindowState();
        if (! $isAdmin && ! ($window['is_open'] ?? false)) {
            return back()->with('error', $window['message'] ?? 'ഈ പ്രോഗ്രാമിന്റെ കോൾ ലിസ്റ്റ് ഇപ്പോൾ എഡിറ്റ് ചെയ്യാൻ അനുവാദമില്ല.');
        }

        // Get all verified entries marked as 'present'
        $entries = $program->entries()
            ->where('status', 'verified')
            ->where('attendance_status', 'present')
            ->get();

        if ($entries->isEmpty()) {
            return back()->with('error', 'ഹാജരായ മത്സരാർത്ഥികളില്ല (No present participants). ദയവായി ആദ്യം മത്സരാർത്ഥികളെ "PRESENT" എന്ന് അടയാളപ്പെടുത്തുക.');
        }

        // Random shuffle
        $shuffled = $entries->shuffle();

        foreach ($shuffled as $index => $entry) {
            $letter = ProgramEntry::formatCodeLetter($index);
            $entry->update(['code_letter' => $letter]);

            // Update call order
            GreenRoomCall::where('program_id', $program->id)
                ->where('entry_id', $entry->id)
                ->update(['order_num' => $index + 1]);
        }

        // For absent ones, clear code letter
        $program->entries()
            ->where('attendance_status', 'absent')
            ->update(['code_letter' => null]);

        AuditLogger::log('green_room_shuffle_codes', $program, null, [
            'program_id' => $program->id,
            'assigned_count' => $shuffled->count(),
        ]);

        return back()->with('success', "നറുക്കെടുപ്പ് വിജയകരം! {$shuffled->count()} പേർക്ക് റാൻഡം കോഡ് ലെറ്ററുകൾ (A, B, C...) നൽകി.");
    }

    public function toggleLockCallList(Program $program): RedirectResponse
    {
        if (! in_array(auth()->user()?->role, ['admin', 'super_admin'])) {
            return back()->with('error', 'അഡ്മിന് മാത്രമേ കോൾ ലിസ്റ്റ് ലോക്ക് / അൺലോക്ക് ചെയ്യാൻ അനുവാദമുള്ളൂ.');
        }

        Program::ensureSchema();

        $currentStatus = (bool) ($program->is_call_list_locked ?? false);
        $newStatus = ! $currentStatus;

        try {
            if (! Schema::hasColumn('programs', 'is_call_list_locked')) {
                Schema::table('programs', function (Blueprint $table) {
                    $table->boolean('is_call_list_locked')->default(false)->after('status');
                });
            }
            $program->is_call_list_locked = $newStatus;
            $program->save();
        } catch (\Throwable) {
            return back()->with('error', 'ഡാറ്റാബേസിൽ പുതിയ കോളം അപ്‌ഡേറ്റ് ചെയ്യാൻ https://quaf.ihyaussunna.in/migrate-db?token=quaf2026setup സന്ദർശിക്കുക.');
        }

        $statusText = $program->is_call_list_locked ? 'ലോക്ക് ചെയ്തു' : 'അൺലോക്ക് ചെയ്തു';

        AuditLogger::log('toggle_lock_call_list', $program, null, [
            'program_id' => $program->id,
            'is_call_list_locked' => $program->is_call_list_locked,
        ]);

        return back()->with('success', "പ്രോഗ്രാം '{$program->name}' കോൾ ലിസ്റ്റ് വിജയകരമായി {$statusText}.");
    }

    public function callList(Request $request): View
    {
        Program::ensureSchema();

        $zones = Program::ZONES;
        $stages = Stage::orderBy('name')->get();

        $selectedZone = $request->query('zone', $request->query('category'));
        $selectedStageId = $request->query('stage_id');
        $selectedProgramId = $request->query('program') ?: $request->query('program_id');
        $search = $request->query('search');
        $attendanceFilter = $request->query('attendance');
        $evalFilter = $request->query('eval_status');

        $programsQuery = Program::query();
        if ($selectedZone) {
            $programsQuery->where('eligibility', $selectedZone);
        }
        if ($selectedStageId) {
            $programsQuery->where('stage_id', $selectedStageId);
        }
        $programs = $programsQuery->orderBy('name')->get();

        $selectedProgram = null;
        $entries = collect();
        $stats = [
            'total' => 0,
            'present' => 0,
            'absent' => 0,
            'waiting' => 0,
            'evaluated' => 0,
            'pending_evaluation' => 0,
        ];

        if ($selectedProgramId) {
            $selectedProgram = Program::with(['category', 'stage', 'schedule'])->find($selectedProgramId);
            if ($selectedProgram) {
                $query = ProgramEntry::where('program_id', $selectedProgram->id)
                    ->with(['student.group', 'group', 'scoreSheets']);

                // Calculate summary counters across all verified entries of the selected program
                $allEntries = (clone $query)->get();
                $stats['total'] = $allEntries->count();
                $stats['present'] = $allEntries->where('attendance_status', 'present')->count();
                $stats['absent'] = $allEntries->where('attendance_status', 'absent')->count();
                $stats['waiting'] = $allEntries->where('attendance_status', '!=', 'present')->where('attendance_status', '!=', 'absent')->count();
                $stats['evaluated'] = $allEntries->filter(fn ($e) => $e->evaluation_status === 'EVALUATED')->count();
                $stats['pending_evaluation'] = $allEntries->filter(fn ($e) => $e->evaluation_status === 'EVALUATION_PENDING')->count();

                // Apply Search & Filters
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('chest_number', 'like', "%{$search}%")
                            ->orWhere('code_letter', 'like', "%{$search}%")
                            ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('student_id', 'like', "%{$search}%"))
                            ->orWhereHas('group', fn ($gq) => $gq->where('name', 'like', "%{$search}%"));
                    });
                }

                if ($attendanceFilter === 'present') {
                    $query->where('attendance_status', 'present');
                } elseif ($attendanceFilter === 'absent') {
                    $query->where('attendance_status', 'absent');
                } elseif ($attendanceFilter === 'waiting') {
                    $query->where(function ($q) {
                        $q->whereNull('attendance_status')->orWhere('attendance_status', 'waiting');
                    });
                }

                if ($evalFilter === 'evaluated') {
                    $query->whereHas('scoreSheets', fn ($q) => $q->where('is_submitted', true));
                } elseif ($evalFilter === 'pending') {
                    $query->where('attendance_status', 'present')
                        ->whereDoesntHave('scoreSheets', fn ($q) => $q->where('is_submitted', true));
                }

                $entries = $query->orderByRaw('CASE WHEN code_letter IS NULL THEN 1 ELSE 0 END, code_letter ASC, chest_number ASC')
                    ->get();
            }
        }

        $windowState = $selectedProgram ? $selectedProgram->getCallListWindowState() : null;

        return view('greenroom.call-list', compact(
            'zones',
            'stages',
            'programs',
            'selectedZone',
            'selectedStageId',
            'selectedProgramId',
            'selectedProgram',
            'entries',
            'stats',
            'search',
            'attendanceFilter',
            'evalFilter',
            'windowState'
        ));
    }

    public function codeLetters(Request $request): View
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
            $selectedProgram = Program::with('category')->find($selectedProgramId);
            if ($selectedProgram) {
                $entries = ProgramEntry::where('program_id', $selectedProgram->id)
                    ->with(['student.group', 'group'])
                    ->orderBy('code_letter')
                    ->orderBy('chest_number')
                    ->get();
            }
        }

        $categories = collect();

        return view('greenroom.code-letters', compact(
            'zones',
            'categories',
            'programs',
            'selectedZone',
            'selectedProgramId',
            'selectedProgram',
            'entries'
        ));
    }
}
