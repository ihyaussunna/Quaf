<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Stage;
use App\Models\Student;
use App\Services\PointCalculationService;
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
        $selectedStatus = $request->query('status');

        $groups = Group::orderByDesc('points_cache')->get();
        $categories = ProgramCategory::orderBy('name')->get();
        $allPrograms = Program::orderBy('name')->get();

        $query = Result::with([
            'program.category',
            'program.stage',
            'program.zone',
            'firstEntry.student.group',
            'firstEntry.group',
            'firstEntry.scoreSheets',
            'secondEntry.student.group',
            'secondEntry.group',
            'secondEntry.scoreSheets',
            'thirdEntry.student.group',
            'thirdEntry.group',
            'thirdEntry.scoreSheets',
        ]);

        if ($selectedStatus === 'evaluated') {
            // Valuation completed results: auto-assign podium for any evaluated programs without results
            $evaluatedWithoutResult = Program::whereDoesntHave('result')
                ->whereHas('entries', fn ($q) => $q->where('attendance_status', 'present')->whereHas('scoreSheets', fn ($sq) => $sq->where('is_submitted', true)))
                ->get();
            foreach ($evaluatedWithoutResult as $prog) {
                PointCalculationService::autoAssignResultPodium($prog, 'submitted');
            }

            $query->where(function ($q) {
                $q->whereNotNull('first_entry_id')
                    ->orWhereIn('status', ['submitted', 'verified', 'send', 'announced', 'published']);
            });
        } elseif ($selectedStatus === 'submitted') {
            $query->whereIn('status', ['submitted', 'verified']);
        } elseif ($selectedStatus && in_array($selectedStatus, ['published', 'announced', 'send'], true)) {
            $query->where('status', $selectedStatus);
        } else {
            // Default: All declared results (sent to announcer, announced, or published)
            $query->whereIn('status', PointCalculationService::DECLARED_STATUSES);
        }

        if ($selectedZone) {
            $query->whereHas('program', function ($q) use ($selectedZone) {
                $q->where('eligibility', $selectedZone);
            });
        }

        if ($selectedProgramId) {
            $query->where('program_id', $selectedProgramId);
        }

        $results = $query->orderByRaw('COALESCE(published_at, created_at) ASC, id ASC')->get();
        $zones = Program::ZONES;

        return view('admin.print.results', compact(
            'results',
            'groups',
            'categories',
            'zones',
            'allPrograms',
            'selectedZone',
            'selectedProgramId',
            'selectedStatus'
        ));
    }

    /**
     * Display printable & exportable Team Standings after X results (e.g. after 5, or custom number).
     */
    public function milestoneStandings(Request $request): View
    {
        $requestedCount = $request->query('count');
        $count = $requestedCount !== null && $requestedCount !== '' ? (int) $requestedCount : 5;
        if ($count < 0) {
            $count = 0;
        }

        $milestoneData = PointCalculationService::getStandingsAfterResults($count);

        return view('admin.print.milestone_standings', [
            'count' => $count,
            'limit' => $milestoneData['limit'],
            'totalDeclared' => $milestoneData['total_declared'],
            'effectiveCount' => $milestoneData['effective_count'],
            'milestoneLabel' => $milestoneData['milestone_label'],
            'standings' => $milestoneData['standings'],
            'results' => $milestoneData['results'],
        ]);
    }

    /**
     * Display printable A4 Points Progression Matrix Sheet matching official ledger format.
     * Shows result-by-result team points and cumulative progression (1, 1+2, 1+2+3...).
     */
    public function progressionMatrix(Request $request): View
    {
        $zone = $request->query('zone');
        $categoryId = $request->query('category') ? (int) $request->query('category') : null;

        $matrix = PointCalculationService::getProgressionMatrix($zone, $categoryId);
        $categories = ProgramCategory::orderBy('name')->get();

        return view('admin.print.progression_matrix', [
            'groups' => $matrix['groups'],
            'rows' => $matrix['rows'],
            'totals' => $matrix['totals'],
            'totalResults' => $matrix['total_results'],
            'selectedZone' => $zone,
            'selectedCategory' => $categoryId,
            'categories' => $categories,
        ]);
    }

    /**
     * Display printable multi-judge evaluation sheet & official scorecard for a program.
     */
    public function evaluationSheet(Program $program): View
    {
        $program->load([
            'category',
            'stage',
            'zone',
            'scoringCriteria',
            'judges',
            'result.firstEntry.student.group',
            'result.secondEntry.student.group',
            'result.thirdEntry.student.group',
        ]);

        $judges = $program->judges()->orderBy('name')->get();
        if ($judges->isEmpty()) {
            $judges = Judge::whereHas('scoreSheets', fn ($q) => $q->where('program_id', $program->id))->get();
        }

        $podium = PointCalculationService::determinePodiumForProgram($program);
        $posPoints = PointCalculationService::getPositionPointsForProgram($program);

        // Fetch all active entries for this program
        $entries = $program->entries()
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->with(['student.group', 'group', 'scores.judge', 'participants.group'])
            ->get();

        $entries = $entries->map(function ($entry) use ($podium, $posPoints) {
            $submitted = $entry->scores->where('is_submitted', true);
            $avg = $submitted->isNotEmpty() ? (float) $submitted->avg('total_score') : 0.0;
            $entry->avg_score = round($avg, 2);
            $gradeInfo = PointCalculationService::getGradeFromScore($entry->avg_score);
            $entry->computed_grade = $gradeInfo['grade'];
            $entry->grade_points = $gradeInfo['points'];

            $entry->podium_rank = null;
            if ($podium['first_all']->contains('id', $entry->id)) {
                $entry->podium_rank = 1;
                $entry->position_points = $posPoints[1] ?? 0;
            } elseif ($podium['second_all']->contains('id', $entry->id)) {
                $entry->podium_rank = 2;
                $entry->position_points = $posPoints[2] ?? 0;
            } elseif ($podium['third_all']->contains('id', $entry->id)) {
                $entry->podium_rank = 3;
                $entry->position_points = $posPoints[3] ?? 0;
            } else {
                $entry->position_points = 0;
            }

            $entry->total_team_points = $entry->position_points + $entry->grade_points;

            return $entry;
        })->sort(function ($a, $b) {
            $aPres = $a->attendance_status === 'present' ? 1 : 0;
            $bPres = $b->attendance_status === 'present' ? 1 : 0;
            if ($aPres !== $bPres) {
                return $bPres <=> $aPres;
            }
            if ($b->avg_score != $a->avg_score) {
                return $b->avg_score <=> $a->avg_score;
            }

            return strcmp((string) $a->code_letter, (string) $b->code_letter);
        })->values();

        return view('admin.print.evaluation_sheet', compact(
            'program',
            'judges',
            'entries',
            'podium',
            'posPoints'
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
        $selectedStatus = $request->query('status');
        $selectedIsStage = $request->query('is_stage');
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

        if ($selectedStatus) {
            if ($selectedStatus === 'in_progress' || $selectedStatus === 'ongoing') {
                $query->whereIn('status', ['in_progress', 'ongoing']);
            } else {
                $query->where('status', $selectedStatus);
            }
        }

        if ($selectedIsStage !== null && $selectedIsStage !== '') {
            $query->where('is_stage', (bool) $selectedIsStage);
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
            'selectedStatus',
            'selectedIsStage',
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
