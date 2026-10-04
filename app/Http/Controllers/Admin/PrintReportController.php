<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
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
        $selectedParticipation = $request->query('participation');
        $search = $request->query('search');

        $groups = Group::orderBy('name')->get();
        $categories = Student::ZONES;

        $query = Student::with([
            'group',
            'entries' => fn ($q) => $q->whereIn('status', ProgramEntry::ACTIVE_STATUSES)->with('program.category'),
            'participations' => fn ($q) => $q->whereIn('status', ProgramEntry::ACTIVE_STATUSES)->with('program.category'),
        ]);

        if ($selectedGroupId) {
            $query->where('group_id', $selectedGroupId);
        }

        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }

        if ($selectedParticipation === 'participating') {
            $query->where(function ($q) {
                $q->whereHas('entries', function ($sq) {
                    $sq->whereIn('status', ProgramEntry::ACTIVE_STATUSES);
                })->orWhereHas('participations', function ($sq) {
                    $sq->whereIn('status', ProgramEntry::ACTIVE_STATUSES);
                });
            });
        } elseif ($selectedParticipation === 'not_participating') {
            $query->whereDoesntHave('entries', function ($sq) {
                $sq->whereIn('status', ProgramEntry::ACTIVE_STATUSES);
            })->whereDoesntHave('participations', function ($sq) {
                $sq->whereIn('status', ProgramEntry::ACTIVE_STATUSES);
            });
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
            'selectedParticipation',
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

    /**
     * Display printable & exportable Entries Report (Group-wise & Program-wise) with interactive customizer.
     */
    public function entries(Request $request): View
    {
        $mode = $request->query('mode', 'group_wise');
        if (! in_array($mode, ['group_wise', 'program_wise'])) {
            $mode = 'group_wise';
        }

        $selectedGroupId = $request->query('group');
        $selectedProgramId = $request->query('program');
        $selectedZone = $request->query('zone');
        $selectedType = $request->query('type');
        $selectedStatus = $request->query('status');
        $search = $request->query('search');

        $groups = Group::orderBy('name')->get();
        $allPrograms = Program::orderBy('code')->get();
        $zones = Program::ZONES;

        $query = ProgramEntry::with([
            'group',
            'student',
            'program.zone',
            'program.category',
            'program.stage',
            'participants',
        ]);

        if ($selectedGroupId) {
            $query->where('group_id', $selectedGroupId);
        }

        if ($selectedProgramId) {
            $query->where('program_id', $selectedProgramId);
        }

        if ($selectedZone) {
            $query->whereHas('program', function ($q) use ($selectedZone) {
                $q->where('eligibility', $selectedZone);
            });
        }

        if ($selectedType) {
            $query->whereHas('program', function ($q) use ($selectedType) {
                $q->where('type', $selectedType);
            });
        }

        if ($selectedStatus) {
            $query->where('status', $selectedStatus);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('chest_number', 'like', "%{$search}%")
                    ->orWhere('code_letter', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('student_id', 'like', "%{$search}%"))
                    ->orWhereHas('program', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")->orWhere('malayalam_name', 'like', "%{$search}%"))
                    ->orWhereHas('group', fn ($gq) => $gq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                    ->orWhereHas('participants', fn ($ptq) => $ptq->where('name', 'like', "%{$search}%")->orWhere('student_id', 'like', "%{$search}%"));
            });
        }

        $entries = $query->orderBy('id')->get();

        if ($mode === 'program_wise') {
            $groupedData = $entries->groupBy('program_id');
        } else {
            $groupedData = $entries->groupBy('group_id');
        }

        $totalEntries = $entries->count();
        $verifiedCount = $entries->whereIn('status', ['verified', 'confirmed'])->count();
        $pendingCount = $entries->where('status', 'pending')->count();
        $uniqueStudentsCount = $entries->pluck('student_id')->filter()->unique()->count();

        return view('admin.print.entries', compact(
            'mode',
            'entries',
            'groupedData',
            'groups',
            'allPrograms',
            'zones',
            'selectedGroupId',
            'selectedProgramId',
            'selectedZone',
            'selectedType',
            'selectedStatus',
            'search',
            'totalEntries',
            'verifiedCount',
            'pendingCount',
            'uniqueStudentsCount'
        ));
    }
}
