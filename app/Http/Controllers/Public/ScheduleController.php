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
    public const FESTIVAL_TITLES = [
        '2026-10-31' => '31 Oct — Festival Day 01',
        '2026-11-01' => '01 Nov — Festival Day 02',
    ];

    public function index(Request $request): View
    {
        $selectedDay = $request->query('day');
        $selectedStage = $request->query('stage');
        $selectedZone = $request->query('zone');
        $search = $request->query('search');

        Cache::forget('public_schedule_stages');
        Cache::forget('public_schedule_zones');

        $stages = Stage::orderBy('code')->get();
        $zones = Zone::orderBy('display_order')->get();

        $todayStr = Carbon::today()->format('Y-m-d');

        // Only include dates that actually have scheduled competitions
        $distinctDates = Schedule::whereNotNull('start_time')
            ->selectRaw('DATE(start_time) as schedule_date')
            ->distinct()
            ->pluck('schedule_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->filter()
            ->unique();

        if (! $distinctDates->contains($todayStr)) {
            $distinctDates->push($todayStr);
        }

        // Put Today first immediately after All Days, followed by the other dates in chronological order
        $orderedDates = $distinctDates->sort()->values()->sortBy(function ($dt) use ($todayStr) {
            return $dt === $todayStr ? 0 : 1;
        })->values();

        $festivalDays = [];
        foreach ($orderedDates as $dt) {
            $carbon = Carbon::parse($dt);
            $isToday = ($dt === $todayStr);

            if ($isToday) {
                $festivalDays[$dt] = $carbon->format('d M').' — Today';
            } elseif (isset(self::FESTIVAL_TITLES[$dt])) {
                $festivalDays[$dt] = self::FESTIVAL_TITLES[$dt];
            } else {
                if ($carbon->greaterThanOrEqualTo(Carbon::parse('2026-10-31'))) {
                    $festivalDays[$dt] = $carbon->format('d M').' — Festival Day';
                } else {
                    $festivalDays[$dt] = $carbon->format('d M').' — '.$carbon->format('l');
                }
            }
        }

        if ($selectedDay && ! isset($festivalDays[$selectedDay])) {
            $selectedDay = array_key_first($festivalDays);
        }

        // Fetch explicitly scheduled events if any exist
        $scheduleQuery = Schedule::with(['program.category', 'program.zone', 'program.stage', 'program.result', 'program.onlineSubmissionForm', 'stage']);

        if ($selectedDay) {
            $scheduleQuery->whereDate('start_time', $selectedDay);
        }

        $onlineKeywords = ['vlog', 'reel', 'podcast', 'documentary', 'short video', 'digital designing', 'poster designing', 'e-poster', 'portfolio design', 'visual story', 'photo feature', 'photography', 'typography'];
        $officeKeywords = ['project', 'wall magazine', 'spot magazine', 'master plan', 'catalogue', 'book writing', 'craft', 'model', 'album'];

        $applyOnlineScope = function ($q) use ($onlineKeywords) {
            $q->where('submission_type', 'online')
                ->orWhere(function ($sq) use ($onlineKeywords) {
                    $sq->where('is_stage', false)
                        ->where(function ($nq) use ($onlineKeywords) {
                            $nq->has('onlineSubmissionForm');
                            foreach ($onlineKeywords as $kw) {
                                $nq->orWhere('name', 'like', "%{$kw}%");
                            }
                        });
                });
        };

        $applyOfficeScope = function ($q) use ($officeKeywords) {
            $q->where('submission_type', 'office')
                ->orWhere(function ($sq) use ($officeKeywords) {
                    $sq->where('is_stage', false)
                        ->where(function ($nq) use ($officeKeywords) {
                            $isFirst = true;
                            foreach ($officeKeywords as $kw) {
                                if ($isFirst) {
                                    $nq->where('name', 'like', "%{$kw}%");
                                    $isFirst = false;
                                } else {
                                    $nq->orWhere('name', 'like', "%{$kw}%");
                                }
                            }
                        });
                });
        };

        if ($selectedStage === 'online') {
            $scheduleQuery->whereHas('program', $applyOnlineScope);
        } elseif ($selectedStage === 'office') {
            $scheduleQuery->whereHas('program', $applyOfficeScope);
        } elseif ($selectedStage === 'submissions') {
            $scheduleQuery->whereHas('program', function ($q) use ($applyOnlineScope, $applyOfficeScope) {
                $q->where($applyOnlineScope)->orWhere($applyOfficeScope);
            });
        } elseif ($selectedStage) {
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

        // If specific Schedule records are empty, provide stage programs and submission programs as festival lineup
        $stageProgramsQuery = Program::with(['category', 'stage', 'result', 'onlineSubmissionForm']);

        if ($selectedStage === 'online') {
            $stageProgramsQuery->where($applyOnlineScope);
        } elseif ($selectedStage === 'office') {
            $stageProgramsQuery->where($applyOfficeScope);
        } elseif ($selectedStage === 'submissions') {
            $stageProgramsQuery->where(function ($q) use ($applyOnlineScope, $applyOfficeScope) {
                $q->where($applyOnlineScope)->orWhere($applyOfficeScope);
            });
        } elseif ($selectedStage) {
            $stageProgramsQuery->where('stage_id', $selectedStage)->where('is_stage', true);
        } else {
            $stageProgramsQuery->where(function ($q) use ($applyOnlineScope, $applyOfficeScope) {
                $q->where('is_stage', true)
                    ->orWhere($applyOnlineScope)
                    ->orWhere($applyOfficeScope);
            });
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
            $isSub = $prog->isSubmissionProgram();
            $isOpen = $isSub ? $prog->isSubmissionOpen() : true;
            $status = ($prog->status === 'completed' || $prog->result || ($isSub && ! $isOpen))
                ? 'completed'
                : ($prog->status === 'in_progress' ? 'in_progress' : 'upcoming');

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
