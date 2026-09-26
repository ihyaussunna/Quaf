<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Program;
use App\Models\Result;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(Request $request): View
    {
        $zone = $request->query('zone', $request->query('category'));
        $groupId = $request->query('group');
        $stageId = $request->query('stage');
        $search = $request->query('search');

        $query = Result::where('status', 'published')
            ->with([
                'program.category',
                'program.stage',
                'firstEntry.student.group',
                'secondEntry.student.group',
                'thirdEntry.student.group',
            ]);

        if ($zone) {
            $query->whereHas('program', fn ($q) => $q->where('eligibility', $zone));
        }

        if ($stageId) {
            $query->whereHas('program', fn ($q) => $q->where('stage_id', $stageId));
        }

        if ($groupId) {
            $query->where(function ($q) use ($groupId) {
                $q->whereHas('firstEntry', fn ($sq) => $sq->where('group_id', $groupId))
                    ->orWhereHas('secondEntry', fn ($sq) => $sq->where('group_id', $groupId))
                    ->orWhereHas('thirdEntry', fn ($sq) => $sq->where('group_id', $groupId));
            });
        }

        if ($search) {
            $query->whereHas('program', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $results = $query->latest('published_at')->paginate(12)->withQueryString();

        $zones = Program::ZONES;
        $categories = collect();
        $groups = Group::orderBy('rank_cache')->get();
        $stages = Stage::all();

        return view('public.results', compact(
            'results',
            'zones',
            'categories',
            'groups',
            'stages',
            'zone',
            'groupId',
            'stageId',
            'search'
        ));
    }

    public function show(Program $program): View
    {
        $result = Result::where('program_id', $program->id)
            ->where('status', 'published')
            ->with([
                'program.category',
                'program.stage',
                'firstEntry.student.group',
                'secondEntry.student.group',
                'thirdEntry.student.group',
                'program.entries.student.group',
                'program.entries.scoreSheets',
            ])
            ->firstOrFail();

        return view('public.result-detail', compact('program', 'result'));
    }
}
