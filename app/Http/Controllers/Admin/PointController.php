<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\PointSetting;
use App\Models\PointsTransaction;
use App\Services\AuditLogger;
use App\Services\PointCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PointController extends Controller
{
    public function __construct(
        protected PointCalculationService $pointService
    ) {}

    public function index(Request $request): View
    {
        $selectedGroupId = $request->query('group_id');
        $sourceType = $request->query('source_type');

        $settings = PointSetting::first() ?? PointSetting::create([
            'first_place_points' => 10,
            'second_place_points' => 7,
            'third_place_points' => 5,
            'participation_points' => 1,
            'group_multiplier' => 2.00,
        ]);

        $groups = Group::withCount(['students', 'entries'])
            ->orderBy('rank_cache')
            ->orderByDesc('points_cache')
            ->get();

        $txQuery = PointsTransaction::with(['group', 'program', 'result', 'student']);

        if ($selectedGroupId) {
            $txQuery->where('group_id', $selectedGroupId);
        }

        if ($sourceType) {
            $txQuery->where('source_type', $sourceType);
        }

        $transactions = $txQuery->latest()->paginate(25)->withQueryString();

        return view('admin.points.index', compact('settings', 'groups', 'transactions', 'selectedGroupId', 'sourceType'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_place_points' => ['required', 'integer', 'min:1'],
            'second_place_points' => ['required', 'integer', 'min:1'],
            'third_place_points' => ['required', 'integer', 'min:1'],
            'participation_points' => ['required', 'integer', 'min:0'],
            'group_multiplier' => ['required', 'numeric', 'min:1'],
        ]);

        $settings = PointSetting::first();
        $old = $settings ? $settings->toArray() : [];

        $settings->update($validated);

        AuditLogger::log('update_point_settings', $settings, $old, $settings->toArray());

        $this->pointService->recalculateAllPoints();

        return back()->with('success', 'Point settings updated and all scores recalculated.');
    }

    public function recalculate(): RedirectResponse
    {
        $this->pointService->recalculateAllPoints();

        return back()->with('success', 'All group rankings, student points, and points transaction ledgers successfully recalculated.');
    }
}
