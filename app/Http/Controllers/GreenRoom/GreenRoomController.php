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

    public function markAttendance(Request $request, ProgramEntry $entry): RedirectResponse
    {
        if ($entry->program?->is_call_list_locked) {
            return back()->with('error', 'ഈ പ്രോഗ്രാമിന്റെ കോൾ ലിസ്റ്റ് ലോക്ക് ചെയ്തിരിക്കുന്നു. ഹാജർ നില മാറ്റാൻ സാധ്യമല്ല.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:present,absent,waiting'],
        ]);

        $entry->update([
            'attendance_status' => $validated['status'],
        ]);

        // If marked absent, clear code letter automatically
        if ($validated['status'] === 'absent') {
            $entry->update(['code_letter' => null]);
        }

        // Sync with GreenRoomCall if exists
        $call = GreenRoomCall::where('entry_id', $entry->id)->first();
        if ($call) {
            $callStatus = match ($validated['status']) {
                'present' => 'ready',
                'absent' => 'absent',
                default => 'waiting',
            };
            $call->update(['status' => $callStatus]);
        }

        AuditLogger::log('green_room_attendance', $entry, null, [
            'entry_id' => $entry->id,
            'chest_number' => $entry->chest_number,
            'attendance_status' => $validated['status'],
        ]);

        return back()->with('success', "ചെസ്റ്റ് #{$entry->chest_number} ഹാജർ നില: ".strtoupper($validated['status']));
    }

    public function generateCodeLetters(Program $program): RedirectResponse
    {
        if ($program->is_call_list_locked) {
            return back()->with('error', 'ഈ പ്രോഗ്രാമിന്റെ കോൾ ലിസ്റ്റ് ലോക്ക് ചെയ്തിരിക്കുന്നു. കോഡ് ലെറ്ററുകൾ ഇനി മാറ്റാൻ കഴിയില്ല.');
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
        $alphabet = range('A', 'Z');

        foreach ($shuffled as $index => $entry) {
            $letter = $alphabet[$index] ?? ('C'.($index + 1));
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
            $selectedProgram = Program::with(['category', 'stage', 'schedule'])->find($selectedProgramId);
            if ($selectedProgram) {
                $entries = ProgramEntry::where('program_id', $selectedProgram->id)
                    ->with(['student.group', 'group'])
                    ->orderByRaw('CASE WHEN code_letter IS NULL THEN 1 ELSE 0 END, code_letter ASC, chest_number ASC')
                    ->get();
            }
        }

        return view('greenroom.call-list', compact(
            'zones',
            'programs',
            'selectedZone',
            'selectedProgramId',
            'selectedProgram',
            'entries'
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
