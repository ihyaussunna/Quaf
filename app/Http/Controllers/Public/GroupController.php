<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Result;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(): View
    {
        Cache::forget('public_groups_list');

        $groups = Group::orderBy('rank_cache', 'asc')
            ->orderByDesc('points_cache')
            ->withCount(['students', 'entries'])
            ->get();

        return view('public.groups', compact('groups'));
    }

    public function show(Group $group): View
    {
        $group->loadCount(['students', 'entries']);

        // Fetch winning results where this group got 1st, 2nd or 3rd place
        $groupWins = Result::where('status', 'published')
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
