<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Result;
use App\Models\Stage;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrintReportController extends Controller
{
    /**
     * Display printable & exportable Results Report with interactive section customizer.
     */
    public function results(Request $request): View
    {
        $selectedZone = $request->query('zone');
        $selectedProgramId = $request->query('program');

        $groups = Group::orderByDesc('points_cache')->get();
        $categories = ProgramCategory::orderBy('name')->get();
        $allPrograms = Program::orderBy('name')->get();

        $query = Result::with([
            'program.category',
            'program.stage',
            'firstEntry.student',
            'firstEntry.group',
            'secondEntry.student',
            'secondEntry.group',
            'thirdEntry.student',
            'thirdEntry.group',
        ])->where('status', 'published');

        if ($selectedZone) {
            $query->whereHas('program', function ($q) use ($selectedZone) {
                $q->where('eligibility', $selectedZone);
            });
        }

        if ($selectedProgramId) {
            $query->where('program_id', $selectedProgramId);
        }

        $results = $query->latest('published_at')->get();
        $zones = Program::ZONES;

        return view('admin.print.results', compact(
            'results',
            'groups',
            'categories',
            'zones',
            'allPrograms',
            'selectedZone',
            'selectedProgramId'
        ));
    }

    /**
     * Display printable & exportable Participants Report with interactive section customizer.
     */
    public function students(Request $request): View
    {
        $selectedGroupId = $request->query('group');
        $selectedCategory = $request->query('category');
        $search = $request->query('search');

        $groups = Group::orderBy('name')->get();
        $categories = Student::ZONES;

        $query = Student::with(['group', 'entries.program.category']);

        if ($selectedGroupId) {
            $query->where('group_id', $selectedGroupId);
        }

        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')->get();

        return view('admin.print.students', compact(
            'students',
            'groups',
            'categories',
            'selectedGroupId',
            'selectedCategory',
            'search'
        ));
    }

    /**
     * Display printable & exportable Programs Report with interactive section customizer.
     */
    public function programs(Request $request): View
    {
        $selectedCategoryId = $request->query('category');
        $selectedStageId = $request->query('stage');
        $selectedType = $request->query('type');
        $selectedZone = $request->query('zone');
        $search = $request->query('search');

        $categories = ProgramCategory::orderBy('name')->get();
        $stages = Stage::orderBy('name')->get();
        $zones = Program::ZONES;

        $query = Program::with(['category', 'stage', 'schedule'])->withCount('entries');

        if ($selectedCategoryId) {
            $query->where('category_id', $selectedCategoryId);
        }

        if ($selectedStageId) {
            $query->where('stage_id', $selectedStageId);
        }

        if ($selectedType) {
            $query->where('type', $selectedType);
        }

        if ($selectedZone) {
            $query->where('eligibility', $selectedZone);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('malayalam_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy('code')->get();

        return view('admin.print.programs', compact(
            'programs',
            'categories',
            'stages',
            'zones',
            'selectedCategoryId',
            'selectedStageId',
            'selectedType',
            'selectedZone',
            'search'
        ));
    }
}
