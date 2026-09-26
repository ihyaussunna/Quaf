<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Stage;
use App\Services\AuditLogger;
use App\Services\ScheduleConflictService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __construct(
        protected ScheduleConflictService $conflictService
    ) {}

    public function index(Request $request): View
    {
        $stageId = $request->query('stage');

        $stages = Stage::with(['schedules.program.category', 'schedules.program.entries.student'])->get();

        $query = Schedule::with(['program.category', 'program.entries.student.group', 'stage'])
            ->orderBy('start_time');

        if ($stageId) {
            $query->where('stage_id', $stageId);
        }

        $schedules = $query->get();
        $programsWithoutSchedule = Program::doesntHave('schedule')->orderBy('name')->get();

        return view('admin.schedules.index', compact('schedules', 'stages', 'stageId', 'programsWithoutSchedule'));
    }

    public function create(): View
    {
        $programs = Program::doesntHave('schedule')->orderBy('name')->get();
        $stages = Stage::all();

        return view('admin.schedules.create', compact('programs', 'stages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id', 'unique:schedules,program_id'],
            'stage_id' => ['required', 'exists:stages,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'status' => ['required', 'in:scheduled,ongoing,completed,delayed'],
        ]);

        $start = Carbon::parse($validated['start_time']);
        $end = Carbon::parse($validated['end_time']);

        // Check Stage Conflict
        $stageConflict = $this->conflictService->checkStageConflict($validated['stage_id'], $start, $end);
        $conflictNotes = [];

        if ($stageConflict) {
            $conflictNotes[] = "Stage Conflict: Stage is already booked for '{$stageConflict->program->name}' ({$stageConflict->start_time->format('h:i A')} - {$stageConflict->end_time->format('h:i A')}).";
        }

        // Check Student Conflicts for all registered participants in this program
        $program = Program::with('entries.student')->findOrFail($validated['program_id']);
        foreach ($program->entries as $entry) {
            if ($entry->student_id) {
                $studentConflicts = $this->conflictService->checkStudentConflict($entry->student_id, $start, $end, $program->id);
                foreach ($studentConflicts as $sc) {
                    $conflictNotes[] = $sc['conflict_reason'];
                    $entry->update(['conflict_flag' => true]);
                }
            }
        }

        // Check Judge Conflicts
        foreach ($program->judges as $judge) {
            $judgeConflict = $this->conflictService->checkJudgeConflict($judge->id, $start, $end, $program->id);
            if ($judgeConflict) {
                $conflictNotes[] = "Judge Conflict: Judge {$judge->name} is already assigned to '{$judgeConflict->name}' at this time.";
            }
        }

        if (! empty($conflictNotes)) {
            $validated['conflict_notes'] = implode("\n", $conflictNotes);
        }

        $schedule = Schedule::create($validated);

        // Update program stage and scheduled_time
        $program->update([
            'stage_id' => $validated['stage_id'],
            'scheduled_time' => $validated['start_time'],
        ]);

        AuditLogger::log('create_schedule', $schedule, null, $schedule->toArray());

        $msg = "Schedule created for '{$program->name}'.";
        if (! empty($conflictNotes)) {
            $msg .= ' WARNING: Conflicts were detected and recorded in schedule notes!';
        }

        return redirect()->route('admin.schedules.index')->with('success', $msg);
    }

    public function edit(Schedule $schedule): View
    {
        $stages = Stage::all();
        $programs = Program::orderBy('name')->get();

        return view('admin.schedules.edit', compact('schedule', 'stages', 'programs'));
    }

    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'stage_id' => ['required', 'exists:stages,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'status' => ['required', 'in:scheduled,ongoing,completed,delayed'],
        ]);

        $start = Carbon::parse($validated['start_time']);
        $end = Carbon::parse($validated['end_time']);

        $stageConflict = $this->conflictService->checkStageConflict($validated['stage_id'], $start, $end, $schedule->id);
        $conflictNotes = [];

        if ($stageConflict) {
            $conflictNotes[] = "Stage Conflict: Stage booked for '{$stageConflict->program->name}'.";
        }

        $validated['conflict_notes'] = ! empty($conflictNotes) ? implode("\n", $conflictNotes) : null;

        $old = $schedule->toArray();
        $schedule->update($validated);

        // Update program
        $schedule->program->update([
            'stage_id' => $validated['stage_id'],
            'scheduled_time' => $validated['start_time'],
        ]);

        AuditLogger::log('update_schedule', $schedule, $old, $schedule->toArray());

        return redirect()->route('admin.schedules.index')->with('success', "Schedule for '{$schedule->program->name}' updated.");
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $old = $schedule->toArray();
        $schedule->delete();

        AuditLogger::log('delete_schedule', null, $old, null);

        return redirect()->route('admin.schedules.index')->with('success', 'Schedule removed.');
    }
}
