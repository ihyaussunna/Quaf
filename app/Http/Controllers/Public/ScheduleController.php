<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Stage;
use App\Models\Zone;
use Illuminate\Http\Request;
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

        $stages = Stage::orderBy('code')->get();
        $zones = Zone::orderBy('display_order')->get();
        $festivalDays = self::FESTIVAL_DAYS;

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

        $schedules = $scheduleQuery->orderBy('start_time')->get();

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

        $stagePrograms = $stageProgramsQuery->orderBy('stage_id')->orderBy('code')->get();

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
