<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TopScorerController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');
        $gender = $request->query('gender');
        $search = $request->query('search');

        $query = Student::with(['group', 'entries.program', 'entries.program.result'])
            ->where('points_cache', '>', 0);

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($gender && in_array($gender, ['male', 'female'])) {
            $query->where('gender', $gender);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $topScorers = $query->orderByDesc('points_cache')
            ->paginate(20)
            ->withQueryString();

        $topScorers->getCollection()->transform(function ($student) {
            $stagePoints = 0;
            $offstagePoints = 0;
            $specialBadges = [];

            foreach ($student->entries as $entry) {
                $prog = $entry->program;
                if (! $prog) {
                    continue;
                }
                $points = 0;
                if ($prog->result && $prog->result->status === 'published') {
                    if ($prog->result->first_entry_id == $entry->id) {
                        $points = 5;
                    } elseif ($prog->result->second_entry_id == $entry->id) {
                        $points = 3;
                    } elseif ($prog->result->third_entry_id == $entry->id) {
                        $points = 1;
                    }
                }
                if ($prog->stage_id) {
                    $stagePoints += $points;
                } else {
                    $offstagePoints += $points;
                }

                $pName = strtoupper($prog->name);
                if (str_contains($pName, 'SONG') || str_contains($pName, 'PATTU') || str_contains($pName, 'RECITATION') || str_contains($pName, 'QURAN')) {
                    if (! in_array('Vocal of the Fest', $specialBadges) && $points > 0) {
                        $specialBadges[] = 'Vocal of the Fest';
                    }
                }
                if (str_contains($pName, 'ESSAY') || str_contains($pName, 'POETRY') || str_contains($pName, 'STORY') || str_contains($pName, 'WRITING')) {
                    if (! in_array('Pen of the Fest', $specialBadges) && $points > 0) {
                        $specialBadges[] = 'Pen of the Fest';
                    }
                }
                if (str_contains($pName, 'DRAWING') || str_contains($pName, 'PAINTING') || str_contains($pName, 'CALLIGRAPHY')) {
                    if (! in_array('Artist of the Fest', $specialBadges) && $points > 0) {
                        $specialBadges[] = 'Artist of the Fest';
                    }
                }
            }

            if ($stagePoints + $offstagePoints === 0 && $student->points_cache > 0) {
                $stagePoints = (int) round($student->points_cache * 0.6);
                $offstagePoints = $student->points_cache - $stagePoints;
                $specialBadges[] = 'Star Performer';
            }

            $student->stage_points = $stagePoints;
            $student->offstage_points = $offstagePoints;
            $student->special_badges = $specialBadges;

            return $student;
        });

        // Kalaprathibha: Top Male student overall
        $kalaprathibha = Student::with('group')
            ->where('gender', 'male')
            ->where('points_cache', '>', 0)
            ->orderByDesc('points_cache')
            ->first();

        // Kalathilakam: Top Female student overall
        $kalathilakam = Student::with('group')
            ->where('gender', 'female')
            ->where('points_cache', '>', 0)
            ->orderByDesc('points_cache')
            ->first();

        // Zone champions
        $zones = Student::ZONES;
        $categoryToppers = [];
        foreach (array_keys($zones) as $zoneKey) {
            $topper = Student::with('group')
                ->where('category', $zoneKey)
                ->where('points_cache', '>', 0)
                ->orderByDesc('points_cache')
                ->first();
            if ($topper) {
                $categoryToppers[$zoneKey] = $topper;
            }
        }

        return view('admin.top-scorers.index', compact(
            'topScorers',
            'kalaprathibha',
            'kalathilakam',
            'categoryToppers',
            'zones',
            'category',
            'gender',
            'search'
        ));
    }

    public function teamScore(Request $request): View
    {
        $teams = Group::withCount(['students', 'entries'])
            ->orderByDesc('points_cache')
            ->get();

        return view('admin.achievements.team-score', compact('teams'));
    }

    public function zoneScore(Request $request): View
    {
        $zones = Student::ZONES;
        $selectedZone = $request->query('zone', 'A Zone');

        $students = Student::with('group')
            ->where(function ($q) use ($selectedZone) {
                $q->where('category', $selectedZone)
                    ->orWhere('class_level', 'like', "%{$selectedZone}%");
            })
            ->orderByDesc('points_cache')
            ->take(50)
            ->get();

        return view('admin.achievements.zone-score', compact('zones', 'selectedZone', 'students'));
    }

    public function stageScore(Request $request): View
    {
        $stageFilter = $request->query('stage', 'Stage'); // 'Stage' or 'Non stage'
        $isStage = ($stageFilter === 'Stage');

        $students = Student::with(['group', 'entries.program'])
            ->whereHas('entries.program', fn ($q) => $q->where('is_stage', $isStage))
            ->orderByDesc('points_cache')
            ->take(50)
            ->get();

        return view('admin.achievements.stage-score', compact('stageFilter', 'students'));
    }

    public function allStudentScore(Request $request): View
    {
        $search = $request->query('search');

        $query = Student::with('group');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $students = $query->orderByDesc('points_cache')->paginate(10)->withQueryString();

        return view('admin.achievements.all-students', compact('students', 'search'));
    }
}
