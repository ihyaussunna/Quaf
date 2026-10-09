<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Stage;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public const FESTIVAL_DAYS = [
        '2026-10-06' => '06 Oct — Offstage Day 01',
        '2026-10-07' => '07 Oct — Offstage Day 02',
        '2026-10-08' => '08 Oct — Offstage Day 03',
        '2026-10-31' => '31 Oct — Main Stage Day 01',
        '2026-11-01' => '01 Nov — Main Stage Day 02',
    ];

    public function index(Request $request): View
    {
        $selectedDay = $request->query('day');
        $selectedStage = $request->query('stage');
        $selectedZone = $request->query('zone');
        $search = $request->query('search');

        $stages = Cache::remember('public_schedule_stages', 60, fn () => Stage::orderBy('code')->get());
        $zones = Cache::remember('public_schedule_zones', 60, fn () => Zone::orderBy('display_order')->get());
        $distinctDates = Schedule::whereNotNull('start_time')
            ->selectRaw('DATE(start_time) as schedule_date')
            ->distinct()
            ->orderBy('schedule_date')
            ->pluck('schedule_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->filter()
            ->values();

        $allUniqueDays = $distinctDates->merge(array_keys(self::FESTIVAL_DAYS))->unique()->sort()->values();

        $festivalDays = [];
        foreach ($allUniqueDays as $dt) {
            if (isset(self::FESTIVAL_DAYS[$dt])) {
                $festivalDays[$dt] = self::FESTIVAL_DAYS[$dt];
            } else {
                $carbon = Carbon::parse($dt);
                $festivalDays[$dt] = $carbon->format('d M').' — Festival Day ('.$carbon->format('D').')';
            }
        }

        // Fetch explicitly scheduled events if any exist
        $scheduleQuery = Schedule::with(['program.category', 'program.zone', 'program.stage', 'program.result', 'stage']);

        if ($selectedDay) {
            $scheduleQuery->whereDate('start_time', $selectedDay);
        }

        if ($selectedStage) {
            $scheduleQuery->where('stage_id', $selectedStage);
        }

        if ($search) {
            $scheduleQuery->whereHas('program', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('malayalam_name', 'like', "%{$search}%");
            });
        }

        $schedules = $scheduleQuery->get()->sortBy(function ($item) {
            $status = $item->computed_status;
            $priority = match ($status) {
                'live', 'in_progress' => 0,
                'upcoming' => 1,
                'completed' => 2,
                default => 3,
            };
            $timestamp = $item->start_time ? $item->start_time->timestamp : PHP_INT_MAX;

            return sprintf('%d_%012d', $priority, $timestamp);
        })->values();

        // If specific Schedule records are empty, provide stage programs as festival schedule lineup
        $stageProgramsQuery = Program::with(['category', 'stage', 'result'])
            ->where('is_stage', true);

        if ($selectedStage) {
            $stageProgramsQuery->where('stage_id', $selectedStage);
        }

        if ($selectedZone) {
            $stageProgramsQuery->where('eligibility', $selectedZone);
        }

        if ($search) {
            $stageProgramsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('malayalam_name', 'like', "%{$search}%");
            });
        }

        $stagePrograms = $stageProgramsQuery->get()->sortBy(function ($prog) {
            $status = ($prog->status === 'completed' || $prog->result) ? 'completed' : ($prog->status === 'in_progress' ? 'in_progress' : 'upcoming');
            $priority = match ($status) {
                'in_progress' => 0,
                'upcoming' => 1,
                default => 2,
            };

            return sprintf('%d_%s', $priority, $prog->code);
        })->values();

        return view('public.schedule', compact(
            'schedules',
            'stagePrograms',
            'stages',
            'zones',
            'festivalDays',
            'selectedDay',
            'selectedStage',
            'selectedZone',
            'search'
        ));
    }
}
