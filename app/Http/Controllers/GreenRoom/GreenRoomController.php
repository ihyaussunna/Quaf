<?php

namespace App\Http\Controllers\GreenRoom;

use App\Http\Controllers\Controller;
use App\Models\GreenRoomCall;
use App\Models\OnlineSubmissionForm;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Schedule;
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
        Program::ensureSchema();
        OnlineSubmissionForm::ensureSchema();

        $tz = config('app.timezone', 'Asia/Kolkata') ?: 'Asia/Kolkata';
        $now = Carbon::now($tz);

        // Stages ordered by ID (Stages 01 to 08)
        $stages = Stage::orderBy('id')->get();
        $selectedStageId = (int) ($request->query('stage_id') ?: ($stages->first()?->id ?? 1));
        $stage = Stage::with(['currentProgram.category', 'nextProgram.category'])->find($selectedStageId) ?? $stages->first();

        // All schedules for this selected stage
        $stageSchedules = Schedule::where('stage_id', $stage->id)
            ->with(['program.category', 'program.zone', 'program.scoringCriteria'])
            ->orderBy('start_time')
            ->get();

        // 1. Now On Stage Program:
        // ONLY a program that has an active window right now:
        // (start_time - 10 minutes <= now <= end_time) OR program status is 'in_progress'
        $activeSchedule = $stageSchedules->first(function ($sch) use ($now, $tz) {
            if ($sch->program?->status === 'in_progress') {
                return true;
            }
            $rawStart = $sch->getRawOriginal('start_time') ?? $sch->start_time;
            $rawEnd = $sch->getRawOriginal('end_time') ?? $sch->end_time;
            if (! $rawStart || ! $rawEnd) {
                return false;
            }
            $start = Carbon::parse($rawStart, $tz)->timezone($tz);
            $end = Carbon::parse($rawEnd, $tz)->timezone($tz);
            $opensAt = $start->copy()->subMinutes(10);

            return $now->between($opensAt, $end);
        });

        $currentProgram = $activeSchedule?->program;
        if (! $currentProgram && $stage->current_program_id) {
            $currentProgram = $stage->currentProgram;
        }

        // 2. Up Next Program:
        // The first schedule on this stage whose start_time is in the future (after now)
        $nextSchedule = $stageSchedules->first(function ($sch) use ($now, $tz, $currentProgram) {
            if ($currentProgram && $sch->program_id === $currentProgram->id) {
                return false;
            }
            $rawStart = $sch->getRawOriginal('start_time') ?? $sch->start_time;
            if (! $rawStart) {
                return false;
            }
            $start = Carbon::parse($rawStart, $tz)->timezone($tz);

            return $start->isAfter($now);
        });

        $nextProgram = $nextSchedule?->program ?? ($stage->next_program_id ? $stage->nextProgram : null);

        // 3. Which program's call list should be displayed?
        // - If coordinator explicitly clicked a scheduled program from stage timeline: load that program
        // - Else if there is a program currently active right now: load current program
        // - Else if there is an upcoming program on this stage: load next program (in preview mode, locked)
        // - Else null (no scheduled program)
        $selectedProgId = $request->query('program') ?: $request->query('program_id');
        $activeProgram = ($selectedProgId ? Program::with(['category', 'zone', 'scoringCriteria', 'schedule'])->find($selectedProgId) : null)
            ?? $currentProgram
            ?? $nextProgram;

        // Eager load scoring criteria
        if ($currentProgram && ! $currentProgram->relationLoaded('scoringCriteria')) {
            $currentProgram->load('scoringCriteria');
        }
        if ($nextProgram && ! $nextProgram->relationLoaded('scoringCriteria')) {
            $nextProgram->load('scoringCriteria');
        }
        if ($activeProgram && ! $activeProgram->relationLoaded('scoringCriteria')) {
            $activeProgram->load('scoringCriteria');
        }

        // Eager load online submission form safely if table exists
        $onlineForm = null;
        $onlineSubmissionsCount = 0;
        try {
            OnlineSubmissionForm::ensureSchema();
            if (Schema::hasTable('online_submission_forms') && $activeProgram) {
                if (! $activeProgram->relationLoaded('onlineSubmissionForm')) {
                    $activeProgram->load('onlineSubmissionForm');
                }
                $onlineForm = $activeProgram->onlineSubmissionForm;
                $onlineSubmissionsCount = ($onlineForm && Schema::hasTable('online_submissions'))
                    ? $onlineForm->submissions()->count()
                    : 0;
            }
        } catch (\Throwable) {
            $onlineForm = null;
            $onlineSubmissionsCount = 0;
        }

        $windowState = $activeProgram ? $activeProgram->getCallListWindowState() : null;
        $isAdmin = in_array(auth()->user()?->role, ['admin', 'super_admin']);
        $isEditable = ($windowState['is_open'] ?? false) || $isAdmin;

        $search = $request->query('search');
        $attendanceFilter = $request->query('attendance');
        $entries = collect();
        $calls = collect();

        $stats = [
            'total' => 0,
            'present' => 0,
            'absent' => 0,
            'waiting' => 0,
            'evaluated' => 0,
            'pending_evaluation' => 0,
            'on_stage' => 0,
            'called' => 0,
        ];

        if ($activeProgram) {
            $query = ProgramEntry::where('program_id', $activeProgram->id)
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'rejected');
                })
                ->with(['student.group', 'group', 'scoreSheets']);

            $allEntries = (clone $query)->get();
            $stats['total'] = $allEntries->count();
            $stats['present'] = $allEntries->where('attendance_status', 'present')->count();
            $stats['absent'] = $allEntries->where('attendance_status', 'absent')->count();
            $stats['waiting'] = $allEntries->where('attendance_status', '!=', 'present')->where('attendance_status', '!=', 'absent')->count();
            $stats['evaluated'] = $allEntries->filter(fn ($e) => $e->evaluation_status === 'EVALUATED')->count();
            $stats['pending_evaluation'] = $allEntries->filter(fn ($e) => $e->evaluation_status === 'EVALUATION_PENDING')->count();

            // Ensure GreenRoomCall records exist
            foreach ($allEntries as $index => $entry) {
                GreenRoomCall::firstOrCreate(
                    [
                        'program_id' => $activeProgram->id,
                        'entry_id' => $entry->id,
                    ],
                    [
                        'order_num' => $index + 1,
                        'status' => match ($entry->attendance_status) {
                            'present' => 'ready',
                            'absent' => 'absent',
                            default => 'waiting',
                        },
                    ]
                );
            }

            // Apply search & filter
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

            $entries = $query->orderByRaw('CASE WHEN code_letter IS NULL THEN 1 ELSE 0 END, code_letter ASC, chest_number ASC')
                ->get();

            $calls = GreenRoomCall::where('program_id', $activeProgram->id)
                ->with(['entry.student.group', 'entry.group', 'entry.scoreSheets'])
                ->orderBy('order_num')
                ->get();

            $stats['on_stage'] = $calls->where('status', 'on_stage')->count();
            $stats['called'] = $calls->where('status', 'called')->count();
        }

        return view('greenroom.index', compact(
            'stages',
            'stage',
            'selectedStageId',
            'stageSchedules',
            'activeSchedule',
            'nextSchedule',
            'currentProgram',
            'nextProgram',
            'activeProgram',
            'windowState',
            'isEditable',
            'isAdmin',
            'entries',
            'calls',
            'stats',
            'search',
            'attendanceFilter',
            'onlineForm',
            'onlineSubmissionsCount'
        ));
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

        if ((bool) ($program?->is_call_list_locked ?? false) || (! $isAdmin && ! ($window['is_open'] ?? false))) {
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

        // Sync with GreenRoomCall (ensure record exists and status is synced)
        $callStatus = match ($newStatus) {
            'present' => 'ready',
            'absent' => 'absent',
            default => 'waiting',
        };
        $call = GreenRoomCall::firstOrCreate(
            [
                'program_id' => $entry->program_id,
                'entry_id' => $entry->id,
            ],
            [
                'order_num' => GreenRoomCall::where('program_id', $entry->program_id)->count() + 1,
                'status' => $callStatus,
            ]
        );
        $call->update(['status' => $callStatus]);

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
        if ((bool) ($program->is_call_list_locked ?? false) || (! $isAdmin && ! ($window['is_open'] ?? false))) {
            return back()->with('error', $window['message'] ?? 'ഈ പ്രോഗ്രാമിന്റെ കോൾ ലിസ്റ്റ് ഇപ്പോൾ എഡിറ്റ് ചെയ്യാൻ അനുവാദമില്ല.');
        }

        $currentShuffleCount = (int) ($program->shuffle_count ?? 0);
        if ($currentShuffleCount >= 2 && ! $isAdmin) {
            return back()->with('error', 'ഈ പ്രോഗ്രാമിന്റെ കോഡ് ലെറ്റർ നറുക്കെടുപ്പ് പരിധി (2 തവണ) പൂർത്തിയായി. ആവശ്യമെങ്കിൽ താഴെ മാനുവലായി കോഡ് നൽകാവുന്നതാണ് (Maximum 2 shuffle chances reached).');
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

        $program->increment('shuffle_count');
        $remainingChances = max(0, 2 - ($currentShuffleCount + 1));

        AuditLogger::log('green_room_shuffle_codes', $program, null, [
            'program_id' => $program->id,
            'assigned_count' => $shuffled->count(),
            'shuffle_count' => $program->shuffle_count,
        ]);

        return back()->with('success', "നറുക്കെടുപ്പ് വിജയകരം! {$shuffled->count()} പേർക്ക് റാൻഡം കോഡ് ലെറ്ററുകൾ (A, B, C...) നൽകി. (ബാക്കി അവസരം: {$remainingChances})");
    }

    public function updateCodeLetter(Request $request, ProgramEntry $entry): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $isAdmin = in_array($user?->role, ['admin', 'super_admin']);

        $program = $entry->program;
        $window = $program?->getCallListWindowState();

        if ((bool) ($program?->is_call_list_locked ?? false) || (! $isAdmin && ! ($window['is_open'] ?? false))) {
            $msg = $window['message'] ?? 'ഈ പ്രോഗ്രാമിന്റെ കോൾ ലിസ്റ്റ് ഇപ്പോൾ എഡിറ്റ് ചെയ്യാൻ അനുവാദമില്ല.';
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'code_letter' => ['nullable', 'string', 'max:10'],
        ]);

        $raw = $validated['code_letter'] ?? null;
        $codeLetter = ! empty($raw) ? strtoupper(trim($raw)) : null;

        $oldCode = $entry->code_letter;
        $entry->code_letter = $codeLetter;
        $entry->save();

        AuditLogger::log('green_room_manual_code_letter', $entry, ['code_letter' => $oldCode], [
            'entry_id' => $entry->id,
            'chest_number' => $entry->chest_number,
            'code_letter' => $codeLetter,
        ]);

        $message = "ചെസ്റ്റ് #{$entry->chest_number} കോഡ് ലെറ്റർ '{$codeLetter}' ആയി രേഖപ്പെടുത്തി.";

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'entry_id' => $entry->id,
                'code_letter' => $entry->code_letter,
            ]);
        }

        return back()->with('success', $message);
    }

    public function batchUpdateCodeLetters(Request $request, Program $program): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $isAdmin = in_array($user?->role, ['admin', 'super_admin']);

        $window = $program->getCallListWindowState();
        if ((bool) ($program->is_call_list_locked ?? false) || (! $isAdmin && ! ($window['is_open'] ?? false))) {
            $msg = $window['message'] ?? 'ഈ പ്രോഗ്രാമിന്റെ കോൾ ലിസ്റ്റ് ഇപ്പോൾ എഡിറ്റ് ചെയ്യാൻ അനുവാദമില്ല.';
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'codes' => ['required', 'array'],
            'codes.*' => ['nullable', 'string', 'max:10'],
        ]);

        $updatedCount = 0;
        foreach ($validated['codes'] as $entryId => $rawCode) {
            $entry = ProgramEntry::where('program_id', $program->id)->find($entryId);
            if (! $entry) {
                continue;
            }

            $codeLetter = ! empty($rawCode) ? strtoupper(trim($rawCode)) : null;
            if ($entry->code_letter !== $codeLetter) {
                $oldCode = $entry->code_letter;
                $entry->code_letter = $codeLetter;
                $entry->save();
                $updatedCount++;

                AuditLogger::log('green_room_manual_code_letter', $entry, ['code_letter' => $oldCode], [
                    'entry_id' => $entry->id,
                    'chest_number' => $entry->chest_number,
                    'code_letter' => $codeLetter,
                ]);
            }
        }

        $message = "കോഡ് ലെറ്ററുകൾ വിജയകരമായി സേവ് ചെയ്തു ({$updatedCount} updated).";

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount,
            ]);
        }

        return back()->with('success', $message);
    }

    public function submitAndLock(Request $request, Program $program): RedirectResponse
    {
        $user = auth()->user();
        if (! in_array($user?->role, ['green_room_coordinator', 'admin', 'super_admin'])) {
            return back()->with('error', 'കോൾ ലിസ്റ്റ് സമർപ്പിക്കാൻ ഗ്രീൻ റൂം കോർഡിനേറ്റർക്കോ അഡ്മിനോ മാത്രമേ അനുവാദമുള്ളൂ.');
        }

        Program::ensureSchema();

        // 1. Optionally save any batch codes submitted alongside
        if ($request->has('codes') && is_array($request->input('codes'))) {
            foreach ($request->input('codes') as $entryId => $rawCode) {
                $entry = ProgramEntry::where('program_id', $program->id)->find($entryId);
                if ($entry) {
                    $codeLetter = ! empty($rawCode) ? strtoupper(trim($rawCode)) : null;
                    if ($entry->code_letter !== $codeLetter) {
                        $oldCode = $entry->code_letter;
                        $entry->code_letter = $codeLetter;
                        $entry->save();

                        AuditLogger::log('green_room_manual_code_letter', $entry, ['code_letter' => $oldCode], [
                            'entry_id' => $entry->id,
                            'chest_number' => $entry->chest_number,
                            'code_letter' => $codeLetter,
                        ]);
                    }
                }
            }
        }

        try {
            if (! Schema::hasColumn('programs', 'is_call_list_locked')) {
                Schema::table('programs', function (Blueprint $table) {
                    $table->boolean('is_call_list_locked')->default(false)->after('status');
                });
            }
            $program->is_call_list_locked = true;
            $program->save();
        } catch (\Throwable) {
            return back()->with('error', 'ഡാറ്റാബേസിൽ ലോക്ക് സ്റ്റാറ്റസ് രേഖപ്പെടുത്താൻ സാധിച്ചില്ല.');
        }

        AuditLogger::log('green_room_submit_and_lock', $program, null, [
            'program_id' => $program->id,
            'user_id' => $user?->id,
            'role' => $user?->role,
        ]);

        return back()->with('success', "പ്രോഗ്രാം '{$program->name}' കോഡ് ലെറ്ററുകൾ സമർപ്പിച്ച് കോൾ ലിസ്റ്റ് വിജയകരമായി ലോക്ക് ചെയ്തു (Call list finalized & locked).");
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
        return $this->index($request);
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
