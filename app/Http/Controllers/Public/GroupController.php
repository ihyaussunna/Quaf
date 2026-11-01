<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Program;
use App\Models\Result;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(): View
    {
        Cache::forget('public_groups_list');

        $groups = Group::getPublicStandings();

        $totalPrograms = Program::count();
        $publishedCount = Result::where('status', 'published')->where('is_media_published', true)->count();
        $isFinalPublished = (bool) FestivalSetting::get('is_final_standings_published', false) || ($totalPrograms > 0 && $publishedCount >= $totalPrograms);
        $isFullResultsPublished = $isFinalPublished;
        $standingsPosterUrl = FestivalSetting::get('public_standings_poster_url');
        $standingsLabel = FestivalSetting::get('public_standings_label');

        return view('public.groups', compact('groups', 'isFullResultsPublished', 'isFinalPublished', 'standingsPosterUrl', 'standingsLabel'));
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
