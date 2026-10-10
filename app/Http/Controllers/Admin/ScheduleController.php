<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Stage;
use App\Models\Zone;
use App\Services\AuditLogger;
use App\Services\ScheduleConflictService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
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
        $isProgramCommittee = $request->routeIs('program-committee.*')
            || $request->is('program-committee/*')
            || (auth()->check() && in_array(auth()->user()->role, ['program_committee', 'program_coordinator']) && ! auth()->user()->isAdmin());
        $layout = $isProgramCommittee ? 'layouts.program-committee' : 'layouts.admin';
        $routePrefix = $isProgramCommittee ? 'program-committee.schedules.' : 'admin.schedules.';

        $stageId = $request->query('stage');
        $date = $request->query('date');
        $stageGroup = $request->query('group');
        $search = $request->query('search');
        $status = $request->query('status');

        $stages = Stage::withCount('schedules')->orderBy('code')->get();

        // Categorize stages: Main Stages (STG-01 to STG-04) vs Offstage Venues (NF3, ID3, U2, S3, etc.)
        $mainStages = $stages->filter(function ($s) {
            return in_array($s->code, ['STG-01', 'STG-02', 'STG-03', 'STG-04']) || str_contains(strtolower($s->name), 'main');
        });
        $offstageStages = $stages->reject(function ($s) {
            return in_array($s->code, ['STG-01', 'STG-02', 'STG-03', 'STG-04']) || str_contains(strtolower($s->name), 'main');
        });

        $query = Schedule::with(['program.zone', 'program.category', 'program.entries.student.group', 'stage'])
            ->orderBy('start_time')
            ->orderBy('stage_id');

        if ($stageId && $stageId !== 'all') {
            $query->where('stage_id', $stageId);
        } elseif ($stageGroup === 'main') {
            $query->whereIn('stage_id', $mainStages->pluck('id'));
        } elseif ($stageGroup === 'offstage') {
            $query->whereIn('stage_id', $offstageStages->pluck('id'));
        }

        if ($date && $date !== 'all') {
            $query->whereDate('start_time', $date);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('program', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('malayalam_name', 'like', "%{$search}%");
            });
        }

        $schedules = $query->get();

        // Dynamically queried distinct scheduled dates from DB merged with milestone dates
        $distinctDates = Schedule::whereNotNull('start_time')
            ->selectRaw('DATE(start_time) as schedule_date')
            ->distinct()
            ->orderBy('schedule_date')
            ->pluck('schedule_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->filter()
            ->values();

        $milestoneLabels = [
            '2026-10-06' => 'Oct 06 (Offstage)',
            '2026-10-07' => 'Oct 07 (Offstage)',
            '2026-10-08' => 'Oct 08 (Offstage)',
            '2026-10-09' => 'Oct 09 (Offstage)',
            '2026-10-10' => 'Oct 10 (Offstage)',
            '2026-10-31' => 'Oct 31 (Main Stage Day 1)',
            '2026-11-01' => 'Nov 01 (Main Stage Day 2)',
        ];

        $allUniqueDates = $distinctDates->merge(array_keys($milestoneLabels))->unique()->sort()->values();

        $festivalDates = ['all' => 'All Days'];
        foreach ($allUniqueDates as $dt) {
            if (isset($milestoneLabels[$dt])) {
                $festivalDates[$dt] = $milestoneLabels[$dt];
            } else {
                $carbon = Carbon::parse($dt);
                $festivalDates[$dt] = $carbon->format('M d').' ('.$carbon->format('D').')';
            }
        }

        if ($date && $date !== 'all' && ! isset($festivalDates[$date])) {
            $carbon = Carbon::parse($date);
            $festivalDates[$date] = $carbon->format('M d').' ('.$carbon->format('D').')';
        }

        // Grouping for Board / Timeline
        $schedulesByStage = $schedules->groupBy('stage_id');
        $schedulesByTime = $schedules->groupBy(function ($sch) {
            return $sch->start_time ? $sch->start_time->format('h:i A') : 'Unscheduled';
        });

        // Unscheduled and All programs for Quick-Slot
        $programsWithoutSchedule = Program::doesntHave('schedule')->with('zone')->orderBy('is_stage')->orderBy('name')->get();
        $allPrograms = Program::with(['zone', 'schedule.stage'])->orderBy('is_stage')->orderBy('name')->get();
        $zones = Zone::orderBy('display_order')->get();

        // Conflict Detection Scan
        $conflictsSummary = $this->conflictService->detectAllScheduleConflicts($date && $date !== 'all' ? $date : null);

        // Stats summary
        $totalProgramsCount = Program::count();
        $scheduledCount = Schedule::count();
        $unscheduledCount = $totalProgramsCount - $scheduledCount;

        return view('admin.schedules.index', compact(
            'layout',
            'routePrefix',
            'isProgramCommittee',
            'schedules',
            'stages',
            'mainStages',
            'offstageStages',
            'stageId',
            'stageGroup',
            'date',
            'search',
            'status',
            'festivalDates',
            'schedulesByStage',
            'schedulesByTime',
            'programsWithoutSchedule',
            'allPrograms',
            'zones',
            'conflictsSummary',
            'totalProgramsCount',
            'scheduledCount',
            'unscheduledCount'
        ));
    }

    /**
     * Dedicated Offstage Schedule Manager & Real-Time Conflict Detector.
     * Mobile-friendly quick scheduling interface.
     */
    public function offstage(Request $request): View
    {
        $isProgramCommittee = $request->routeIs('program-committee.*')
            || $request->is('program-committee/*')
            || (auth()->check() && in_array(auth()->user()->role, ['program_committee', 'program_coordinator']) && ! auth()->user()->isAdmin());
        $layout = $isProgramCommittee ? 'layouts.program-committee' : 'layouts.admin';
        $routePrefix = $isProgramCommittee ? 'program-committee.schedules.' : 'admin.schedules.';

        $selectedDate = $request->query('date', '2026-10-06');

        // All distinct festival dates scheduled or available
        $defaultDates = [
            '2026-10-06' => 'Oct 06 (Tue)',
            '2026-10-07' => 'Oct 07 (Wed)',
            '2026-10-08' => 'Oct 08 (Thu)',
            '2026-10-09' => 'Oct 09 (Fri)',
            '2026-10-10' => 'Oct 10 (Sat)',
            '2026-10-31' => 'Oct 31 (Sat - Main Stage)',
            '2026-11-01' => 'Nov 01 (Sun - Main Stage)',
        ];

        // Fetch all stages, ensuring offstage stages are listed first or highlighted
        $stages = Stage::orderBy('code')->get();

        // Fetch schedules for the selected date
        $schedules = Schedule::with(['program.zone', 'program.category', 'program.entries.student.group', 'stage'])
            ->whereDate('start_time', $selectedDate)
            ->orderBy('start_time')
            ->orderBy('stage_id')
            ->get();

        // Group by Time Slot (e.g. "04:40 PM")
        $schedulesByTime = $schedules->groupBy(function ($sch) {
            return $sch->start_time->format('h:i A');
        });

        // Run full conflict detection for this date
        $conflicts = $this->conflictService->detectAllScheduleConflicts($selectedDate);

        // Fetch programs list for scheduler selection (offstage programs first, then others)
        $programs = Program::with(['zone', 'schedule.stage'])
            ->orderBy('is_stage')
            ->orderBy('name')
            ->get();

        $zones = Zone::orderBy('display_order')->get();

        return view('admin.schedules.offstage', compact(
            'layout',
            'routePrefix',
            'isProgramCommittee',
            'selectedDate',
            'defaultDates',
            'stages',
            'schedules',
            'schedulesByTime',
            'conflicts',
            'programs',
            'zones'
        ));
    }

    /**
     * Official Rockwell PDF Export & Print View for Offstage Schedule.
     */
    public function offstagePdf(Request $request): View
    {
        $date = $request->query('date', '2026-10-07');

        if ($date === 'all') {
            $schedules = Schedule::with(['program.zone', 'stage'])
                ->orderBy('start_time')
                ->orderBy('stage_id')
                ->get();

            // Group by Date, then by Time
            $groupedByDate = $schedules->groupBy(function ($sch) {
                return $sch->start_time->format('Y-m-d');
            })->map(function ($daySchedules) {
                return $daySchedules->groupBy(function ($sch) {
                    return $sch->start_time->format('h:i A');
                });
            });

            return view('admin.schedules.offstage-pdf', [
                'mode' => 'all',
                'groupedByDate' => $groupedByDate,
            ]);
        }

        $parsedDate = Carbon::parse($date);
        $formattedDate = $parsedDate->format('Y F d l'); // e.g. "2026 October 07 Wednesday"

        $schedules = Schedule::with(['program.zone', 'stage'])
            ->whereDate('start_time', $date)
            ->orderBy('start_time')
            ->orderBy('stage_id')
            ->get();

        // Group by Time Slot (e.g. "04:40 PM")
        $schedulesByTime = $schedules->groupBy(function ($sch) {
            return $sch->start_time->format('h:i A');
        });

        return view('admin.schedules.offstage-pdf', [
            'mode' => 'single',
            'date' => $date,
            'formattedDate' => $formattedDate,
            'schedulesByTime' => $schedulesByTime,
        ]);
    }

    /**
     * Real-time AJAX Conflict Checking API for slot builder.
     */
    public function checkConflictApi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'string'],
            'duration' => ['required', 'integer', 'min:5', 'max:360'],
            'stage_id' => ['nullable', 'exists:stages,id'],
            'exclude_schedule_id' => ['nullable', 'integer'],
        ]);

        try {
            $start = Carbon::parse("{$validated['date']} {$validated['time']}");
            $end = (clone $start)->addMinutes((int) $validated['duration']);

            $results = $this->conflictService->checkProgramSlotConflicts(
                $validated['program_id'],
                $start,
                $end,
                $validated['stage_id'] ?? null,
                $validated['exclude_schedule_id'] ?? null
            );

            return response()->json([
                'status' => 'success',
                'start_time' => $start->format('Y-m-d H:i:s'),
                'end_time' => $end->format('Y-m-d H:i:s'),
                'conflicts' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Quick Slot Add / Edit (Mobile & Desktop).
     */
    public function quickSlot(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'stage_id' => ['required', 'exists:stages,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'string'],
            'duration' => ['required', 'integer', 'min:5', 'max:360'],
            'schedule_id' => ['nullable', 'exists:schedules,id'],
        ]);

        $start = Carbon::parse("{$validated['date']} {$validated['time']}");
        $end = (clone $start)->addMinutes((int) $validated['duration']);

        $conflicts = $this->conflictService->checkProgramSlotConflicts(
            $validated['program_id'],
            $start,
            $end,
            $validated['stage_id'],
            $validated['schedule_id'] ?? null
        );

        $conflictNotes = [];
        if ($conflicts['stage_conflict']) {
            $conflictNotes[] = $conflicts['stage_conflict']['message'];
        }
        foreach ($conflicts['student_conflicts'] as $sc) {
            $conflictNotes[] = $sc['message'];
        }

        $schedule = Schedule::updateOrCreate(
            ['program_id' => $validated['program_id']],
            [
                'stage_id' => $validated['stage_id'],
                'start_time' => $start,
                'end_time' => $end,
                'status' => 'scheduled',
                'conflict_notes' => ! empty($conflictNotes) ? implode("\n", $conflictNotes) : null,
            ]
        );

        $schedule->program->update([
            'stage_id' => $validated['stage_id'],
            'scheduled_time' => $start,
            'duration_minutes' => $validated['duration'],
        ]);

        AuditLogger::log('quick_schedule_slot', $schedule, null, $schedule->toArray());

        $hasConflicts = ! empty($conflictNotes);
        $msg = "Schedule slot for '{$schedule->program->name}' saved successfully.";
        if ($hasConflicts) {
            $msg .= ' Clashes were detected: '.implode(' | ', array_slice($conflictNotes, 0, 2));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'has_conflicts' => $hasConflicts,
                'conflicts' => $conflicts,
                'schedule' => $schedule->load(['program.zone', 'stage']),
            ]);
        }

        $isProgramCommittee = $request->routeIs('program-committee.*') || str_contains(url()->previous(), 'program-committee');
        $indexRoute = $isProgramCommittee ? 'program-committee.schedules.index' : 'admin.schedules.index';
        $offstageRoute = $isProgramCommittee ? 'program-committee.schedules.offstage' : 'admin.schedules.offstage';

        $redirectTo = $request->input('redirect_to');
        if ($redirectTo === 'index') {
            return redirect()->route($indexRoute, [
                'stage' => $validated['stage_id'],
                'date' => $validated['date'],
            ])->with($hasConflicts ? 'warning' : 'success', $msg);
        }

        return redirect()->route($offstageRoute, ['date' => $validated['date']])
            ->with($hasConflicts ? 'warning' : 'success', $msg);
    }

    /**
     * Store new Stage/Venue dynamically (e.g. Stage 09, Venue NF4).
     */
    public function storeStage(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'location' => ['required', 'string', 'max:50'], // Venue code e.g. NF3, ID3, U2, S3, Hall 1
            'capacity' => ['nullable', 'integer', 'min:10', 'max:5000'],
        ]);

        // Auto-generate unique code
        $count = Stage::count() + 1;
        $code = 'STG-'.str_pad((string) $count, 2, '0', STR_PAD_LEFT);
        while (Stage::where('code', $code)->exists()) {
            $count++;
            $code = 'STG-'.str_pad((string) $count, 2, '0', STR_PAD_LEFT);
        }

        $stage = Stage::create([
            'name' => $validated['name'],
            'code' => $code,
            'location' => $validated['location'],
            'capacity' => $validated['capacity'] ?? 150,
            'status' => 'active',
        ]);

        AuditLogger::log('create_stage', $stage, null, $stage->toArray());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'stage' => $stage,
                'message' => "Venue '{$stage->location}' ({$stage->name}) added.",
            ]);
        }

        return back()->with('success', "Stage '{$stage->name}' with Venue '{$stage->location}' added successfully.");
    }

    public function create(): View
    {
        $isProgramCommittee = request()->routeIs('program-committee.*')
            || request()->is('program-committee/*')
            || (auth()->check() && in_array(auth()->user()->role, ['program_committee', 'program_coordinator']) && ! auth()->user()->isAdmin());
        $layout = $isProgramCommittee ? 'layouts.program-committee' : 'layouts.admin';
        $routePrefix = $isProgramCommittee ? 'program-committee.schedules.' : 'admin.schedules.';

        $programs = Program::doesntHave('schedule')->orderBy('name')->get();
        $stages = Stage::all();

        return view('admin.schedules.create', compact('layout', 'routePrefix', 'isProgramCommittee', 'programs', 'stages'));
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

        $slotClashes = $this->conflictService->checkProgramSlotConflicts(
            $validated['program_id'],
            $start,
            $end,
            $validated['stage_id']
        );

        $conflictNotes = [];
        if ($slotClashes['stage_conflict']) {
            $conflictNotes[] = $slotClashes['stage_conflict']['message'];
        }
        foreach ($slotClashes['student_conflicts'] as $sc) {
            $conflictNotes[] = $sc['message'];
        }

        if (! empty($conflictNotes)) {
            $validated['conflict_notes'] = implode("\n", $conflictNotes);
        }

        $schedule = Schedule::create($validated);

        $duration = max(5, (int) round($end->diffInMinutes($start)));

        $schedule->program->update([
            'stage_id' => $validated['stage_id'],
            'scheduled_time' => $validated['start_time'],
            'duration_minutes' => $duration,
        ]);

        AuditLogger::log('create_schedule', $schedule, null, $schedule->toArray());

        $msg = "Schedule created for '{$schedule->program->name}'.";
        if (! empty($conflictNotes)) {
            $msg .= ' WARNING: Clashes were detected and recorded in schedule notes!';
        }

        $isProgramCommittee = $request->routeIs('program-committee.*')
            || $request->is('program-committee/*')
            || str_contains(url()->previous(), 'program-committee')
            || (auth()->check() && in_array(auth()->user()->role, ['program_committee', 'program_coordinator']) && ! auth()->user()->isAdmin());
        $indexRoute = $isProgramCommittee ? 'program-committee.schedules.index' : 'admin.schedules.index';

        return redirect()->route($indexRoute)->with('success', $msg);
    }

    public function edit(Schedule $schedule): View
    {
        $isProgramCommittee = request()->routeIs('program-committee.*')
            || request()->is('program-committee/*')
            || (auth()->check() && in_array(auth()->user()->role, ['program_committee', 'program_coordinator']) && ! auth()->user()->isAdmin());
        $layout = $isProgramCommittee ? 'layouts.program-committee' : 'layouts.admin';
        $routePrefix = $isProgramCommittee ? 'program-committee.schedules.' : 'admin.schedules.';

        $stages = Stage::all();
        $programs = Program::orderBy('name')->get();

        return view('admin.schedules.edit', compact('layout', 'routePrefix', 'isProgramCommittee', 'schedule', 'stages', 'programs'));
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

        $slotClashes = $this->conflictService->checkProgramSlotConflicts(
            $schedule->program_id,
            $start,
            $end,
            $validated['stage_id'],
            $schedule->id
        );

        $conflictNotes = [];
        if ($slotClashes['stage_conflict']) {
            $conflictNotes[] = $slotClashes['stage_conflict']['message'];
        }
        foreach ($slotClashes['student_conflicts'] as $sc) {
            $conflictNotes[] = $sc['message'];
        }

        $validated['conflict_notes'] = ! empty($conflictNotes) ? implode("\n", $conflictNotes) : null;

        $old = $schedule->toArray();
        $schedule->update($validated);

        $duration = max(5, (int) round($end->diffInMinutes($start)));

        $schedule->program->update([
            'stage_id' => $validated['stage_id'],
            'scheduled_time' => $validated['start_time'],
            'duration_minutes' => $duration,
        ]);

        AuditLogger::log('update_schedule', $schedule, $old, $schedule->toArray());

        $isProgramCommittee = $request->routeIs('program-committee.*')
            || $request->is('program-committee/*')
            || str_contains(url()->previous(), 'program-committee')
            || (auth()->check() && in_array(auth()->user()->role, ['program_committee', 'program_coordinator']) && ! auth()->user()->isAdmin());
        $indexRoute = $isProgramCommittee ? 'program-committee.schedules.index' : 'admin.schedules.index';

        return redirect()->route($indexRoute)->with('success', "Schedule for '{$schedule->program->name}' updated.");
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $old = $schedule->toArray();

        // Clear program's scheduled time
        $schedule->program?->update([
            'stage_id' => null,
            'scheduled_time' => null,
        ]);

        $schedule->delete();

        AuditLogger::log('delete_schedule', null, $old, null);

        return back()->with('success', 'Schedule slot removed.');
    }

    /**
     * Automated Clash Resolver: Smartly resolve all stage double-bookings and student overlaps.
     */
    public function autoResolveClashes(Request $request): RedirectResponse|JsonResponse
    {
        $date = $request->input('date');
        $targetDate = ($date && $date !== 'all') ? $date : null;

        $result = $this->conflictService->smartResolveAllConflicts($targetDate);

        AuditLogger::log('auto_resolve_schedule_clashes', null, null, [
            'date' => $targetDate ?: 'all',
            'resolved' => $result['resolved_count'],
            'remaining_conflicts' => $result['remaining_conflicts'],
            'actions' => $result['actions'],
        ]);

        $message = $result['remaining_conflicts'] === 0
            ? "Schedule fully optimized! All clashes resolved ({$result['resolved_count']} adjustments made, 0 clashes remaining)."
            : "Schedule partially optimized: {$result['resolved_count']} adjustments made ({$result['remaining_conflicts']} clashes remaining).";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'resolved_count' => $result['resolved_count'],
                'remaining_conflicts' => $result['remaining_conflicts'],
                'actions' => $result['actions'],
            ]);
        }

        return back()->with($result['remaining_conflicts'] === 0 ? 'success' : 'warning', $message);
    }
}
