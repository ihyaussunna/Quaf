<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Student;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TopScorerController extends Controller
{
    /**
     * Display the official Top Scorers and Special Awards (Vocal, Pen, Zone Champions).
     * Follows QUAF Rule 10:
     * - Stage Top Scorer: ചെറിയ എ പി ഉസ്താദ് കലാപ്രതിഭാ പുരസ്കാരം (Vocal of the Fest)
     * - Offstage Top Scorer: നെല്ലിക്കുത്ത് ഉസ്താദ് സർഗ്ഗപ്രതിഭാ പുരസ്കാരം (Pen of the Fest)
     * - Zone-wise Top Scorers: കട്ടിപ്പാറ ഉസ്താദ് അവാർഡ് (Zone Top Scorer)
     * - Group programs are strictly excluded from individual top scorers table.
     */
    public function index(Request $request): View
    {
        $search = trim((string) ($request->query('search') ?? ''));
        $selectedCategory = $request->query('category', 'all');
        $selectedTeam = $request->query('team', 'all');
        $awardFilter = $request->query('award', 'all');

        // 1. Calculate individual points (Stage, Offstage, Total) per student from points_transactions
        // Excluding group programs per Rule 10
        $pointsQuery = DB::table('points_transactions')
            ->join('programs', 'points_transactions.program_id', '=', 'programs.id')
            ->whereNotNull('points_transactions.student_id')
            ->where('programs.type', 'individual')
            ->select(
                'points_transactions.student_id',
                DB::raw('SUM(CASE WHEN programs.is_stage = 1 OR programs.stage_id IS NOT NULL THEN points_transactions.points ELSE 0 END) as stage_points'),
                DB::raw('SUM(CASE WHEN (programs.is_stage = 0 OR programs.is_stage IS NULL) AND programs.stage_id IS NULL THEN points_transactions.points ELSE 0 END) as offstage_points'),
                DB::raw('SUM(points_transactions.points) as total_points')
            )
            ->groupBy('points_transactions.student_id');

        $studentPointsMap = $pointsQuery->get()->keyBy('student_id');
        $studentIdsWithPoints = $studentPointsMap->keys()->all();

        // 2. Fetch all candidates across festival to compute global awards
        $allScorers = Student::with(['group', 'zone'])
            ->where(function ($q) use ($studentIdsWithPoints) {
                $q->whereIn('id', $studentIdsWithPoints)
                    ->orWhere('points_cache', '>', 0);
            })
            ->get()
            ->map(function ($student) use ($studentPointsMap) {
                $pts = $studentPointsMap->get($student->id);
                $stage = (int) ($pts->stage_points ?? 0);
                $offstage = (int) ($pts->offstage_points ?? 0);
                $total = (int) ($pts->total_points ?? $student->points_cache ?? 0);

                if ($total > 0 && $stage === 0 && $offstage === 0) {
                    $total = $student->points_cache;
                }

                $student->calc_stage_points = $stage;
                $student->calc_offstage_points = $offstage;
                $student->calc_total_points = $total;

                return $student;
            })
            ->filter(fn ($s) => $s->calc_total_points > 0);

        // 3. Identify Global Award Winners (Official Rule 10)
        // A. Vocal of the Fest: ചെറിയ എ പി ഉസ്താദ് കലാപ്രതിഭാ പുരസ്കാരം (Stage Top Scorer)
        $maxStage = $allScorers->max('calc_stage_points') ?? 0;
        $vocalWinners = $maxStage > 0
            ? $allScorers->filter(fn ($s) => $s->calc_stage_points === $maxStage)->values()
            : collect();
        $vocalWinnerIds = $vocalWinners->pluck('id')->all();

        // B. Pen of the Fest: നെല്ലിക്കുത്ത് ഉസ്താദ് സർഗ്ഗപ്രതിഭാ പുരസ്കാരം (Offstage Top Scorer)
        $maxOffstage = $allScorers->max('calc_offstage_points') ?? 0;
        $penWinners = $maxOffstage > 0
            ? $allScorers->filter(fn ($s) => $s->calc_offstage_points === $maxOffstage)->values()
            : collect();
        $penWinnerIds = $penWinners->pluck('id')->all();

        // C. Zone-wise Top Scorers: കട്ടിപ്പാറ ഉസ്താദ് അവാർഡ്
        $zones = Zone::orderBy('display_order')->get();
        if ($zones->isEmpty()) {
            $zones = collect([
                (object) ['id' => 1, 'name' => 'A Zone', 'code' => 'A_ZONE'],
                (object) ['id' => 2, 'name' => 'B Zone', 'code' => 'B_ZONE'],
                (object) ['id' => 3, 'name' => 'C Zone', 'code' => 'C_ZONE'],
            ]);
        }

        $zoneToppers = [];
        $zoneTopperIds = [];
        foreach ($zones as $z) {
            $zStudents = $allScorers->filter(function ($s) use ($z) {
                return ($s->zone_id && $s->zone_id == $z->id)
                    || (stripos((string) $s->category, $z->name) !== false)
                    || (stripos((string) $s->category, str_replace(' Zone', '', $z->name)) !== false);
            });

            $zMax = $zStudents->max('calc_total_points') ?? 0;
            if ($zMax > 0) {
                $toppers = $zStudents->filter(fn ($s) => $s->calc_total_points === $zMax)->values();
                $zoneToppers[$z->name] = [
                    'zone' => $z,
                    'max_points' => $zMax,
                    'winners' => $toppers,
                ];
                foreach ($toppers as $w) {
                    $zoneTopperIds[$w->id] = $z->name;
                }
            }
        }

        // Attach award badges to each student
        $allScorers->each(function ($student) use ($vocalWinnerIds, $penWinnerIds, $zoneTopperIds) {
            $badges = [];
            if (in_array($student->id, $vocalWinnerIds, true)) {
                $badges[] = [
                    'type' => 'vocal',
                    'label' => 'Vocal of the Fest',
                    'award_name' => 'ചെറിയ എ പി ഉസ്താദ് കലാപ്രതിഭാ പുരസ്കാരം',
                    'bg_class' => 'bg-[#be1e2d] text-white',
                ];
            }
            if (in_array($student->id, $penWinnerIds, true)) {
                $badges[] = [
                    'type' => 'pen',
                    'label' => 'Pen of the Fest',
                    'award_name' => 'നെല്ലിക്കുത്ത് ഉസ്താദ് സർഗ്ഗപ്രതിഭാ പുരസ്കാരം',
                    'bg_class' => 'bg-slate-900 text-white',
                ];
            }
            if (isset($zoneTopperIds[$student->id])) {
                $zName = $zoneTopperIds[$student->id];
                $badges[] = [
                    'type' => 'zone',
                    'label' => "{$zName} Top Scorer",
                    'award_name' => "കട്ടിപ്പാറ ഉസ്താദ് അവാർഡ് ({$zName})",
                    'bg_class' => 'bg-amber-500 text-slate-950 font-bold',
                ];
            }
            $student->award_badges = $badges;
        });

        // 4. Apply Filters for View Table
        $filteredScorers = $allScorers;

        if ($selectedCategory && $selectedCategory !== 'all') {
            $filteredScorers = $filteredScorers->filter(function ($s) use ($selectedCategory) {
                return $s->category === $selectedCategory
                    || ($s->zone && ($s->zone->name === $selectedCategory || $s->zone->code === $selectedCategory))
                    || stripos((string) $s->category, $selectedCategory) !== false;
            });
        }

        if ($selectedTeam && $selectedTeam !== 'all') {
            $filteredScorers = $filteredScorers->filter(fn ($s) => (string) $s->group_id === (string) $selectedTeam);
        }

        if ($awardFilter && $awardFilter !== 'all') {
            if ($awardFilter === 'vocal') {
                $filteredScorers = $filteredScorers->filter(fn ($s) => in_array($s->id, $vocalWinnerIds, true));
            } elseif ($awardFilter === 'pen') {
                $filteredScorers = $filteredScorers->filter(fn ($s) => in_array($s->id, $penWinnerIds, true));
            } elseif ($awardFilter === 'zone') {
                $filteredScorers = $filteredScorers->filter(fn ($s) => isset($zoneTopperIds[$s->id]));
            } elseif ($awardFilter === 'any_award') {
                $filteredScorers = $filteredScorers->filter(fn ($s) => ! empty($s->award_badges));
            }
        }

        if ($search !== '') {
            $searchLower = strtolower($search);
            $filteredScorers = $filteredScorers->filter(function ($s) use ($searchLower) {
                return str_contains(strtolower($s->name), $searchLower)
                    || str_contains(strtolower((string) $s->student_id), $searchLower);
            });
        }

        // Sort descending: total points, stage points, name
        $sortedScorers = $filteredScorers->sort(function ($a, $b) {
            if ($b->calc_total_points !== $a->calc_total_points) {
                return $b->calc_total_points <=> $a->calc_total_points;
            }
            if ($b->calc_stage_points !== $a->calc_stage_points) {
                return $b->calc_stage_points <=> $a->calc_stage_points;
            }

            return strcmp($a->name, $b->name);
        })->values();

        // 5. Paginate for table
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('limit', 20);
        $topScorers = new LengthAwarePaginator(
            $sortedScorers->forPage($page, $perPage),
            $sortedScorers->count(),
            $perPage,
            $page,
            ['path' => route('admin.top-scorers.index'), 'query' => $request->query()]
        );

        $allTeams = Group::orderBy('name')->get();
        $allCategories = Zone::orderBy('display_order')->get();

        return view('admin.top-scorers.index', compact(
            'topScorers',
            'vocalWinners',
            'maxStage',
            'penWinners',
            'maxOffstage',
            'zoneToppers',
            'allTeams',
            'allCategories',
            'search',
            'selectedCategory',
            'selectedTeam',
            'awardFilter'
        ));
    }

    public function teamScore(Request $request): RedirectResponse
    {
        return redirect()->route('admin.top-scorers.index');
    }

    public function zoneScore(Request $request): RedirectResponse
    {
        $zone = $request->query('zone');

        return redirect()->route('admin.top-scorers.index', array_filter(['category' => $zone]));
    }

    public function stageScore(Request $request): RedirectResponse
    {
        $stage = $request->query('stage');
        $award = ($stage === 'Non stage') ? 'pen' : 'vocal';

        return redirect()->route('admin.top-scorers.index', ['award' => $award]);
    }

    public function allStudentScore(Request $request): RedirectResponse
    {
        $search = $request->query('search');

        return redirect()->route('admin.top-scorers.index', array_filter(['search' => $search]));
    }
}
