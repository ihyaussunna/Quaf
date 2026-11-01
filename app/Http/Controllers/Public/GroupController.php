<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Program;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(Request $request): View
    {
        Cache::forget('public_groups_list');

        // One-time self-healing reset for production: ensure premature final celebration is turned off
        if (FestivalSetting::get('is_final_standings_reset_v2') !== '1') {
            FestivalSetting::set('is_final_standings_published', '0');
            FestivalSetting::set('public_standings_is_final', '0');
            FestivalSetting::set('is_final_standings_reset_v2', '1');
            Cache::forget('public_groups_list');
            Cache::forget('public_home_data');
        }

        $groups = Group::getPublicStandings();

        $totalPrograms = Program::count();
        $publishedCount = Result::where('status', 'published')->where('is_media_published', true)->count();

        $isDemo = $request->boolean('demo') || $request->is('*demo*') || $request->is('*preview*');
        $isFinalPublished = $isDemo || (bool) FestivalSetting::get('is_final_standings_published', false) || ($totalPrograms > 0 && $publishedCount >= $totalPrograms);
        $isFullResultsPublished = $isFinalPublished;
        $standingsPosterUrl = FestivalSetting::get('public_standings_poster_url');
        $standingsLabel = $isDemo
            ? 'FINAL CHAMPIONSHIP STANDINGS (DEMO PREVIEW)'
            : (FestivalSetting::get('public_standings_label') ?: ('After '.sprintf('%02d', (int) FestivalSetting::get('public_standings_results_count', 0)).' Results'));

        return view('public.groups', compact('groups', 'isFullResultsPublished', 'isFinalPublished', 'standingsPosterUrl', 'standingsLabel', 'isDemo', 'totalPrograms'));
    }

    /**
     * Dedicated demo preview route to inspect the full Final Standings & Victory Celebration.
     */
    public function demo(Request $request): View
    {
        $request->merge(['demo' => 1]);

        return $this->index($request);
    }

    public function show(Group $group): View
    {
        $group->loadCount(['students', 'entries']);

        // Fetch winning results where this group got 1st, 2nd or 3rd place
        $groupWins = Result::where('status', 'published')
            ->where('is_media_published', true)
            ->where(function ($q) use ($group) {
                $q->whereHas('firstEntry', fn ($sq) => $sq->where('group_id', $group->id))
                    ->orWhereHas('secondEntry', fn ($sq) => $sq->where('group_id', $group->id))
                    ->orWhereHas('thirdEntry', fn ($sq) => $sq->where('group_id', $group->id));
            })
            ->with(['program.category', 'firstEntry.student', 'secondEntry.student', 'thirdEntry.student'])
            ->latest('published_at')
            ->get();

        $students = $group->students()
            ->orderByDesc('points_cache')
            ->take(20)
            ->get();

        return view('public.group-detail', compact('group', 'groupWins', 'students'));
    }
}
