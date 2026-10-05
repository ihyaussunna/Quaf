<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Result;
use App\Models\Stage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PublicApiController extends Controller
{
    public function ticker(): JsonResponse
    {
        $liveFestMode = FestivalSetting::get('live_fest_mode', '1') === '1';

        $tickerData = Cache::remember('public_api_live_ticker', 15, function () {
            return Result::where('status', 'published')
                ->with(['program', 'firstEntry.student.group', 'secondEntry.student.group', 'thirdEntry.student.group'])
                ->latest('published_at')
                ->take(10)
                ->get()
                ->map(function ($res) {
                    return [
                        'program_code' => $res->program?->code,
                        'program_name' => $res->program?->name,
                        'first_place' => [
                            'name' => $res->firstEntry?->student?->name ?? 'Team '.$res->firstEntry?->group?->name,
                            'chest_number' => $res->firstEntry?->chest_number,
                            'group' => $res->firstEntry?->group?->name,
                            'group_code' => $res->firstEntry?->group?->code,
                            'color' => $res->firstEntry?->group?->color_hex,
                        ],
                        'second_place' => $res->secondEntry ? [
                            'name' => $res->secondEntry?->student?->name ?? 'Team '.$res->secondEntry?->group?->name,
                            'chest_number' => $res->secondEntry?->chest_number,
                            'group' => $res->secondEntry?->group?->name,
                            'group_code' => $res->secondEntry?->group?->code,
                            'color' => $res->secondEntry?->group?->color_hex,
                        ] : null,
                        'third_place' => $res->thirdEntry ? [
                            'name' => $res->thirdEntry?->student?->name ?? 'Team '.$res->thirdEntry?->group?->name,
                            'chest_number' => $res->thirdEntry?->chest_number,
                            'group' => $res->thirdEntry?->group?->name,
                            'group_code' => $res->thirdEntry?->group?->code,
                            'color' => $res->thirdEntry?->group?->color_hex,
                        ] : null,
                        'published_at' => $res->published_at?->toIso8601String(),
                    ];
                });
        });

        return response()->json([
            'live_fest_mode' => $liveFestMode,
            'items' => $tickerData,
        ]);
    }

    public function standings(): JsonResponse
    {
        $standings = Cache::remember('public_api_standings', 20, function () {
            return Group::orderBy('rank_cache', 'asc')
                ->orderByDesc('points_cache')
                ->select(['id', 'name', 'code', 'slug', 'color_hex', 'points_cache', 'rank_cache', 'manager_name'])
                ->get();
        });

        return response()->json(['standings' => $standings]);
    }

    public function stages(): JsonResponse
    {
        $stages = Cache::remember('public_api_stages', 30, function () {
            return Stage::with(['currentProgram.category', 'nextProgram.category'])
                ->select(['id', 'name', 'code', 'status', 'location', 'capacity', 'current_program_id', 'next_program_id'])
                ->get()
                ->map(function ($stage) {
                    return [
                        'id' => $stage->id,
                        'name' => $stage->name,
                        'code' => $stage->code,
                        'status' => $stage->status,
                        'location' => $stage->location,
                        'capacity' => $stage->capacity,
                        'current_program' => $stage->currentProgram ? [
                            'id' => $stage->currentProgram->id,
                            'code' => $stage->currentProgram->code,
                            'name' => $stage->currentProgram->name,
                            'category' => $stage->currentProgram->category?->name,
                            'eligibility' => $stage->currentProgram->eligibility,
                        ] : null,
                        'next_program' => $stage->nextProgram ? [
                            'id' => $stage->nextProgram->id,
                            'code' => $stage->nextProgram->code,
                            'name' => $stage->nextProgram->name,
                            'category' => $stage->nextProgram->category?->name,
                        ] : null,
                    ];
                });
        });

        return response()->json(['stages' => $stages]);
    }

    public function latestResults(): JsonResponse
    {
        $results = Cache::remember('public_api_latest_results', 30, function () {
            return Result::where('status', 'published')
                ->with(['program.category', 'firstEntry.student.group', 'secondEntry.student.group', 'thirdEntry.student.group'])
                ->latest('published_at')
                ->take(12)
                ->get()
                ->map(function ($res) {
                    return [
                        'id' => $res->id,
                        'program_id' => $res->program_id,
                        'program_code' => $res->program?->code,
                        'program_name' => $res->program?->name,
                        'category' => $res->program?->category?->name,
                        'eligibility' => $res->program?->eligibility,
                        'published_at' => $res->published_at?->diffForHumans(),
                        'first_place' => [
                            'name' => $res->firstEntry?->student?->name ?? 'Team '.$res->firstEntry?->group?->name,
                            'chest_number' => $res->firstEntry?->chest_number,
                            'group' => $res->firstEntry?->group?->name,
                            'group_code' => $res->firstEntry?->group?->code,
                        ],
                    ];
                });
        });

        return response()->json(['results' => $results]);
    }
}
