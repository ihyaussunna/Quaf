<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Program;
use App\Models\Result;
use App\Services\AuditLogger;
use App\Services\PointCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

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
        $standingsPosterUrl = FestivalSetting::get('public_standings_poster_url');

        $totalPublishedResults = Result::where('status', 'published')
            ->where('is_media_published', true)
            ->count();

        $totalPrograms = Program::count();
        $totalAnnouncedResults = Result::whereIn('status', PointCalculationService::DECLARED_STATUSES)->count();
        $isFinalPublished = (bool) FestivalSetting::get('is_final_standings_published', false);
        $publicStandingsLabel = FestivalSetting::get('public_standings_label', $standingsBadge);

        return view('media.standings.index', compact(
            'groups',
            'resultsCount',
            'formattedCount',
            'standingsBadge',
            'standingsPosterUrl',
            'totalPublishedResults',
            'totalPrograms',
            'totalAnnouncedResults',
            'isFinalPublished',
            'publicStandingsLabel'
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
            'standings_poster' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:15360'],
            'remove_poster' => ['nullable', 'boolean'],
            'is_final' => ['nullable', 'boolean'],
        ]);

        $resCount = (int) $validated['results_count'];
        $formatted = sprintf('%02d', $resCount);
        $isFinal = $request->boolean('is_final');
        $label = $isFinal ? 'Final Result • Official Champions' : "After {$formatted} Results";

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
        FestivalSetting::set('is_final_standings_published', $isFinal ? '1' : '0');
        FestivalSetting::set('public_standings_is_final', $isFinal ? '1' : '0');
        FestivalSetting::set('public_standings_updated_at', now()->toIso8601String());

        // 4. Handle Standings Poster upload or removal
        if ($request->hasFile('standings_poster')) {
            $path = $request->file('standings_poster')->store('media/standings', 'public');
            $posterUrl = Storage::url($path);
            FestivalSetting::set('public_standings_poster_path', $path);
            FestivalSetting::set('public_standings_poster_url', $posterUrl);
            FestivalSetting::set('public_standings_poster_updated_at', now()->toIso8601String());
        } elseif ($request->boolean('remove_poster')) {
            FestivalSetting::set('public_standings_poster_path', '');
            FestivalSetting::set('public_standings_poster_url', '');
            FestivalSetting::set('public_standings_poster_updated_at', now()->toIso8601String());
        }

        // 5. Invalidate all public standings caches immediately
        Cache::forget('public_api_standings');
        Cache::forget('public_home_data');
        Cache::forget('public_groups_list');
        Cache::forget('public_results_groups');

        AuditLogger::log('media_update_team_standings', null, null, [
            'results_count' => $resCount,
            'label' => $label,
            'is_final' => $isFinal,
            'points' => $validated['points'],
        ]);

        $msg = $isFinal
            ? 'ഫൈനൽ റിസൾട്ട് ടീം സ്റ്റാൻഡിങ്സ് പബ്ലിക് വെബ്സൈറ്റിൽ ഔദ്യോഗികമായി പ്രഖ്യാപിച്ചു. പബ്ലിക് വെബ്സൈറ്റിൽ സെലിബ്രേഷൻ സജീവമായി!'
            : "ടീം പോയിന്റ് നില വിജയകരമായി പബ്ലിക് വെബ്സൈറ്റിൽ അപ്‌ഡേറ്റ് ചെയ്തു ({$label}).";

        return back()->with('success', $msg);
    }

    /**
     * Instantly publish final championship standings and trigger website celebration.
     */
    public function publishFinal(Request $request): RedirectResponse
    {
        Group::ensureSchema();

        if ($request->has('points') && is_array($request->input('points'))) {
            foreach ($request->input('points') as $groupId => $pts) {
                Group::where('id', $groupId)->update([
                    'public_points_cache' => max(0, (int) $pts),
                ]);
            }
        } else {
            app(PointCalculationService::class)->recalculateAllPoints();
            $sortedGroups = Group::orderByDesc('points_cache')->orderBy('name')->get();
            foreach ($sortedGroups as $grp) {
                $grp->update([
                    'public_points_cache' => (int) $grp->points_cache,
                ]);
            }
        }

        $rankedGroups = Group::orderByDesc('public_points_cache')
            ->orderBy('name', 'asc')
            ->get();

        $rank = 1;
        foreach ($rankedGroups as $grp) {
            $grp->update([
                'public_rank_cache' => $rank++,
            ]);
        }

        $totalPrograms = Program::count();
        $resCount = $request->input('results_count', $totalPrograms > 0 ? $totalPrograms : Result::where('status', 'published')->count());
        $label = 'Final Result • Official Champions';

        FestivalSetting::set('public_standings_results_count', (string) $resCount);
        FestivalSetting::set('public_standings_label', $label);
        FestivalSetting::set('is_final_standings_published', '1');
        FestivalSetting::set('public_standings_is_final', '1');
        FestivalSetting::set('public_standings_updated_at', now()->toIso8601String());

        if ($request->hasFile('standings_poster')) {
            $path = $request->file('standings_poster')->store('media/standings', 'public');
            $posterUrl = Storage::url($path);
            FestivalSetting::set('public_standings_poster_path', $path);
            FestivalSetting::set('public_standings_poster_url', $posterUrl);
            FestivalSetting::set('public_standings_poster_updated_at', now()->toIso8601String());
        }

        Cache::forget('public_api_standings');
        Cache::forget('public_home_data');
        Cache::forget('public_groups_list');
        Cache::forget('public_results_groups');

        AuditLogger::log('media_publish_final_standings', null, null, [
            'results_count' => $resCount,
            'label' => $label,
            'is_final' => true,
        ]);

        return back()->with('success', 'ഫൈനൽ റിസൾട്ട് ടീം സ്റ്റാൻഡിങ്സ് പബ്ലിക് വെബ്സൈറ്റിൽ ഔദ്യോഗികമായി പ്രഖ്യാപിച്ചു. പബ്ലിക് വെബ്സൈറ്റിൽ സെലിബ്രേഷൻ സജീവമായി!');
    }

    /**
     * Revert final championship status back to regular milestone standings.
     */
    public function revertFinal(Request $request): RedirectResponse
    {
        FestivalSetting::set('is_final_standings_published', '0');
        FestivalSetting::set('public_standings_is_final', '0');

        $resCount = (int) FestivalSetting::get('public_standings_results_count', 0);
        $formatted = sprintf('%02d', $resCount);
        $label = "After {$formatted} Results";
        FestivalSetting::set('public_standings_label', $label);
        FestivalSetting::set('public_standings_updated_at', now()->toIso8601String());

        Cache::forget('public_api_standings');
        Cache::forget('public_home_data');
        Cache::forget('public_groups_list');
        Cache::forget('public_results_groups');

        AuditLogger::log('media_revert_final_standings', null, null, [
            'is_final' => false,
            'label' => $label,
        ]);

        return back()->with('success', 'ഫൈനൽ സ്റ്റാൻഡിങ്സ് സ്റ്റാറ്റസ് പിൻവലിച്ചു. റെഗുലർ സ്റ്റാൻഡിങ്സിലേക്ക് തിരികെ മാറ്റി.');
    }

    /**
     * Download the currently active Team Standings poster in full original high quality without compression.
     */
    public function downloadPoster(): Response
    {
        $label = FestivalSetting::get('public_standings_label', 'Official_Standings');
        $filenameBase = 'QUAF_Standings_'.Str::slug($label);

        $path = ltrim((string) FestivalSetting::get('public_standings_poster_path'), '/');
        if ($path !== '') {
            $fullPath = null;
            if (Storage::disk('public')->exists($path)) {
                $fullPath = Storage::disk('public')->path($path);
            } elseif (file_exists(public_path('storage/'.$path))) {
                $fullPath = public_path('storage/'.$path);
            } elseif (file_exists(public_path($path))) {
                $fullPath = public_path($path);
            }

            if ($fullPath && is_file($fullPath)) {
                $ext = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'jpg';
                $filename = $filenameBase.'.'.$ext;

                return response()->download($fullPath, $filename, [
                    'Content-Type' => (function_exists('mime_content_type') ? @mime_content_type($fullPath) : null) ?: 'image/jpeg',
                    'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                    'Cache-Control' => 'no-cache, must-revalidate',
                    'Pragma' => 'public',
                ]);
            }
        }

        $url = FestivalSetting::get('public_standings_poster_url');
        if ($url) {
            $parsedPath = parse_url($url, PHP_URL_PATH) ?? '';
            $cleanPath = ltrim(preg_replace('~^/?storage/~', '', $parsedPath), '/');

            $fullPath = null;
            if ($cleanPath !== '' && Storage::disk('public')->exists($cleanPath)) {
                $fullPath = Storage::disk('public')->path($cleanPath);
            } elseif ($cleanPath !== '' && file_exists(public_path('storage/'.$cleanPath))) {
                $fullPath = public_path('storage/'.$cleanPath);
            } elseif ($cleanPath !== '' && file_exists(public_path($cleanPath))) {
                $fullPath = public_path($cleanPath);
            } elseif ($parsedPath !== '' && file_exists(public_path(ltrim($parsedPath, '/')))) {
                $fullPath = public_path(ltrim($parsedPath, '/'));
            }

            if ($fullPath && is_file($fullPath)) {
                $ext = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'jpg';
                $filename = $filenameBase.'.'.$ext;

                return response()->download($fullPath, $filename, [
                    'Content-Type' => (function_exists('mime_content_type') ? @mime_content_type($fullPath) : null) ?: 'image/jpeg',
                    'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                    'Cache-Control' => 'no-cache, must-revalidate',
                    'Pragma' => 'public',
                ]);
            }

            return redirect($url);
        }

        return redirect()->route('groups.index')->with('error', 'സ്റ്റാൻഡിങ്സ് പോസ്റ്റർ ലഭ്യമല്ല.');
    }
}
