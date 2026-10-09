<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Result;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(Request $request): View
    {
        $zone = $request->query('zone');
        $groupId = $request->query('group');
        $programId = $request->query('program');
        $stageId = $request->query('stage');
        $categoryId = $request->query('category');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Result::where('status', 'published')
            ->with([
                'program.category',
                'program.stage',
                'firstEntry.student.group',
                'secondEntry.student.group',
                'thirdEntry.student.group',
            ]);

        if ($programId) {
            $query->where('program_id', $programId);
        }

        if ($zone) {
            $query->whereHas('program', fn ($q) => $q->where('eligibility', $zone));
        }

        if ($stageId) {
            $query->whereHas('program', fn ($q) => $q->where('stage_id', $stageId));
        }

        if ($categoryId) {
            $query->whereHas('program', fn ($q) => $q->where('category_id', $categoryId));
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
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('malayalam_name', 'like', "%{$search}%");
            });
        }

        $results = $query->latest('published_at')->paginate(12)->withQueryString();

        Cache::forget('public_results_categories');
        Cache::forget('public_results_groups');
        Cache::forget('public_results_stages');

        $zones = Program::ZONES;
        $categories = ProgramCategory::orderBy('name')->get();
        $groups = Group::orderBy('rank_cache')->get();
        $programs = Program::orderBy('code')->get(['id', 'code', 'name', 'eligibility']);
        $stages = Stage::all();

        return view('public.results', compact(
            'results',
            'zones',
            'categories',
            'groups',
            'programs',
            'stages',
            'zone',
            'groupId',
            'programId',
            'stageId',
            'categoryId',
            'status',
            'search'
        ));
    }

    public function show(string $programIdentifier): View
    {
        $program = Program::where('id', $programIdentifier)
            ->orWhere('code', $programIdentifier)
            ->firstOrFail();

        $program->load(['category', 'stage']);

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
            ->first();

        // If no published result yet, also load entries for lineup inspection
        if (! $result) {
            $program->load(['entries.student.group']);
        }

        return view('public.result-detail', compact('program', 'result'));
    }
}
