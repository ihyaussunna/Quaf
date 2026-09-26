<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Student;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZoneController extends Controller
{
    public function index(Request $request): View
    {
        $dbZones = Zone::orderBy('display_order')->get();

        $selectedZoneName = $request->query('zone', 'A Zone');
        $selectedZone = $dbZones->firstWhere('name', $selectedZoneName)
            ?? $dbZones->firstWhere('code', $selectedZoneName)
            ?? $dbZones->first();

        $selectedZoneKey = $selectedZone ? $selectedZone->name : 'A Zone';
        $selectedZoneId = $selectedZone ? $selectedZone->id : 1;

        $activeTab = $request->query('tab', 'programs'); // 'programs' or 'students'
        $search = $request->query('search');

        // Build Metrics for all 4 Zones dynamically from DB
        $zoneCards = [];
        foreach ($dbZones as $z) {
            $programsCount = Program::where('zone_id', $z->id)
                ->orWhere('eligibility', $z->name)
                ->count();

            $stageCount = Program::where(function ($q) use ($z) {
                $q->where('zone_id', $z->id)->orWhere('eligibility', $z->name);
            })->where('is_stage', true)->count();

            $offStageCount = Program::where(function ($q) use ($z) {
                $q->where('zone_id', $z->id)->orWhere('eligibility', $z->name);
            })->where('is_stage', false)->count();

            $studentsCount = Student::where('zone_id', $z->id)
                ->orWhere('category', $z->name)
                ->count();

            $pointsTotal = Student::where(function ($q) use ($z) {
                $q->where('zone_id', $z->id)->orWhere('category', $z->name);
            })->sum('points_cache');

            $colorHex = $z->color_hex ?: '#be1e2d';

            $zoneCards[$z->name] = [
                'id' => $z->id,
                'key' => $z->name,
                'code' => $z->code,
                'title' => $z->name,
                'sub' => $z->sub_text,
                'classes' => $z->classes,
                'color' => $colorHex,
                'border_class' => "border-t-[{$colorHex}]",
                'bg_light' => 'bg-slate-50',
                'programs_count' => $programsCount,
                'stage_programs_count' => $stageCount,
                'offstage_programs_count' => $offStageCount,
                'students_count' => $studentsCount,
                'points_total' => (int) $pointsTotal,
            ];
        }

        // Active Listing (Programs or Students)
        $programs = collect();
        $students = collect();

        if ($activeTab === 'programs') {
            $progQuery = Program::where(function ($q) use ($selectedZoneId, $selectedZoneKey) {
                $q->where('zone_id', $selectedZoneId)
                    ->orWhere('eligibility', $selectedZoneKey);
            })->with(['category', 'stage', 'schedule'])->withCount('entries');

            if ($search) {
                $progQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('malayalam_name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            }

            $programs = $progQuery->orderBy('name')->paginate(15)->withQueryString();
        } else {
            $studQuery = Student::where(function ($q) use ($selectedZoneId, $selectedZoneKey) {
                $q->where('zone_id', $selectedZoneId)
                    ->orWhere('category', $selectedZoneKey);
            })->with(['group', 'zone'])->withCount('entries');

            if ($search) {
                $studQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%")
                        ->orWhere('contact', 'like', "%{$search}%");
                });
            }

            $students = $studQuery->orderBy('name')->paginate(15)->withQueryString();
        }

        $currentZone = $zoneCards[$selectedZoneKey] ?? ($dbZones->first() ? $zoneCards[$dbZones->first()->name] : null);
        $selectedZone = $currentZone;

        return view('admin.zones.index', compact(
            'dbZones',
            'zoneCards',
            'selectedZone',
            'selectedZoneKey',
            'currentZone',
            'activeTab',
            'programs',
            'students',
            'search'
        ));
    }
}
