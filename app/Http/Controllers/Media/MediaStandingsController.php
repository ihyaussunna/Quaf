<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Result;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class MediaStandingsController extends Controller
{
    /**
     * Display Team Standings management panel for Media Team.
     */
    public function index(): View
    {
        Group::ensureSchema();

        $groups = Group::orderByDesc('public_points_cache')
            ->orderBy('public_rank_cache', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $resultsCount = (int) FestivalSetting::get('public_standings_results_count', 0);
        $formattedCount = sprintf('%02d', $resultsCount);
        $standingsBadge = "After {$formattedCount} Results";

        $totalPublishedResults = Result::where('status', 'published')
            ->where('is_media_published', true)
            ->count();

        return view('media.standings.index', compact(
            'groups',
            'resultsCount',
            'formattedCount',
            'standingsBadge',
            'totalPublishedResults'
        ));
    }

    /**
     * Update and publish official team standings from Media Desk.
     */
    public function update(Request $request): RedirectResponse
    {
        Group::ensureSchema();

        $validated = $request->validate([
            'results_count' => ['required', 'integer', 'min:0', 'max:9999'],
            'points' => ['required', 'array'],
            'points.*' => ['required', 'numeric', 'min:0', 'max:100000'],
        ]);

        $resCount = (int) $validated['results_count'];
        $formatted = sprintf('%02d', $resCount);
        $label = "After {$formatted} Results";

        // 1. Update each group's public points from media input
        foreach ($validated['points'] as $groupId => $pts) {
            Group::where('id', $groupId)->update([
                'public_points_cache' => max(0, (int) $pts),
            ]);
        }

        // 2. Rank strictly by public_points_cache DESC so highest points team is on top (Rank 1)
        $rankedGroups = Group::orderByDesc('public_points_cache')
            ->orderBy('name', 'asc')
            ->get();

        $rank = 1;
        foreach ($rankedGroups as $grp) {
            $grp->update([
                'public_rank_cache' => $rank++,
            ]);
        }

        // 3. Store active milestone settings
        FestivalSetting::set('public_standings_results_count', (string) $resCount);
        FestivalSetting::set('public_standings_label', $label);
        FestivalSetting::set('public_standings_updated_at', now()->toIso8601String());

        // 4. Invalidate all public standings caches immediately
        Cache::forget('public_api_standings');
        Cache::forget('public_home_data');
        Cache::forget('public_groups_list');
        Cache::forget('public_results_groups');

        AuditLogger::log('media_update_team_standings', null, null, [
            'results_count' => $resCount,
            'label' => $label,
            'points' => $validated['points'],
        ]);

        return back()->with('success', "ടീം പോയിന്റ് നില വിജയകരമായി പബ്ലിക് വെബ്സൈറ്റിൽ അപ്‌ഡേറ്റ് ചെയ്തു ({$label}).");
    }
}
