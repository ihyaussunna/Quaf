<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\PointSetting;
use App\Models\PointsTransaction;
use App\Models\Result;
use App\Models\StandingAnnouncement;
use App\Services\AuditLogger;
use App\Services\PointCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
            'first_place_points' => 5,
            'second_place_points' => 3,
            'third_place_points' => 1,
            'participation_points' => 0,
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

        $publishedResultsCount = Result::where('status', 'published')->count();
        $publicStandingsCount = (int) FestivalSetting::get('public_standings_results_count', '0');
        $publicStandingsLabel = FestivalSetting::get('public_standings_label') ?: 'Not Released';
        $publicStandingsMode = FestivalSetting::get('public_standings_mode', 'milestone');
        $publicStandingsMilestones = FestivalSetting::get('public_standings_milestones', '10,15,20,27,35,40');
        $publicStandingsUpdatedAt = FestivalSetting::get('public_standings_updated_at');

        $recentStandingsAnnouncements = collect();
        try {
            StandingAnnouncement::ensureSchema();
            if (Schema::hasTable('standing_announcements')) {
                $recentStandingsAnnouncements = StandingAnnouncement::with(['sentBy', 'announcedBy', 'publishedBy'])
                    ->latest()
                    ->take(8)
                    ->get();
            }
        } catch (\Throwable) {
            $recentStandingsAnnouncements = collect();
        }

        return view('admin.points.index', compact(
            'settings',
            'groups',
            'transactions',
            'selectedGroupId',
            'sourceType',
            'publishedResultsCount',
            'publicStandingsCount',
            'publicStandingsLabel',
            'publicStandingsMode',
            'publicStandingsMilestones',
            'publicStandingsUpdatedAt',
            'recentStandingsAnnouncements'
        ));
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

    /**
     * Update Finance and Grace marks for groups and recalculate standings.
     */
    public function updateGroupMarks(Request $request): RedirectResponse
    {
        Group::ensureSchema();

        if ($request->has('groups') && is_array($request->input('groups'))) {
            $validated = $request->validate([
                'groups' => ['required', 'array'],
                'groups.*.id' => ['required', 'exists:groups,id'],
                'groups.*.finance_points' => ['required', 'integer'],
                'groups.*.finance_remarks' => ['nullable', 'string', 'max:255'],
                'groups.*.grace_points' => ['required', 'integer'],
                'groups.*.grace_remarks' => ['nullable', 'string', 'max:255'],
            ]);

            foreach ($validated['groups'] as $item) {
                $group = Group::find($item['id']);
                if ($group) {
                    $old = [
                        'finance_points' => $group->finance_points,
                        'finance_remarks' => $group->finance_remarks,
                        'grace_points' => $group->grace_points,
                        'grace_remarks' => $group->grace_remarks,
                    ];
                    $group->update([
                        'finance_points' => (int) $item['finance_points'],
                        'finance_remarks' => $item['finance_remarks'] ?? null,
                        'grace_points' => (int) $item['grace_points'],
                        'grace_remarks' => $item['grace_remarks'] ?? null,
                    ]);
                    AuditLogger::log('update_group_marks_batch', $group, $old, $item);
                }
            }

            $this->pointService->recalculateAllPoints();
            Group::checkAndApplyMilestone();

            return back()->with('success', 'എല്ലാ ഗ്രൂപ്പുകളുടെയും ഫിനാൻസ് & ഗ്രേസ് മാർക്കുകൾ വിജയകരമായി അപ്ഡേറ്റ് ചെയ്യുകയും ആകെ പോയിന്റ് നില പുനർനിർണ്ണയിക്കുകയും ചെയ്തു.');
        }

        $validated = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'finance_points' => ['required', 'integer'],
            'finance_remarks' => ['nullable', 'string', 'max:255'],
            'grace_points' => ['required', 'integer'],
            'grace_remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $group = Group::findOrFail($validated['group_id']);
        $old = [
            'finance_points' => $group->finance_points,
            'finance_remarks' => $group->finance_remarks,
            'grace_points' => $group->grace_points,
            'grace_remarks' => $group->grace_remarks,
        ];

        $group->update([
            'finance_points' => (int) $validated['finance_points'],
            'finance_remarks' => $validated['finance_remarks'] ?? null,
            'grace_points' => (int) $validated['grace_points'],
            'grace_remarks' => $validated['grace_remarks'] ?? null,
        ]);

        AuditLogger::log('update_group_marks', $group, $old, [
            'finance_points' => $group->finance_points,
            'finance_remarks' => $group->finance_remarks,
            'grace_points' => $group->grace_points,
            'grace_remarks' => $group->grace_remarks,
        ]);

        $this->pointService->recalculateAllPoints();
        Group::checkAndApplyMilestone();

        return back()->with('success', "{$group->name} ഗ്രൂപ്പിന്റെ ഫിനാൻസ് & ഗ്രേസ് മാർക്കുകൾ വിജയകരമായി അപ്ഡേറ്റ് ചെയ്യുകയും പോയിന്റ് നില പുനർനിർണ്ണയിക്കുകയും ചെയ്തു.");
    }

    public function recalculate(): RedirectResponse
    {
        $this->pointService->recalculateAllPoints();

        return back()->with('success', 'All group rankings, student points, and points transaction ledgers successfully recalculated.');
    }

    /**
     * Send current team standings snapshot to the Announcer desk.
     */
    public function sendStandingsToAnnouncer(Request $request): RedirectResponse
    {
        $customLabel = $request->input('custom_label', $request->input('label'));
        $forceCount = $request->has('results_count') ? (int) $request->input('results_count') : null;
        $announcement = StandingAnnouncement::createFromCurrentStandings($customLabel, auth()->id(), $forceCount);

        AuditLogger::log('admin_send_standings_to_announcer', $announcement, [], $announcement->toArray());

        return back()->with('success', "ടീം പോയിന്റ് നില അനൗൺസർ കൺസോളിലേക്ക് വിജയകരമായി അയച്ചു ({$announcement->title}). അനൗൺസർ സ്റ്റേജിൽ പ്രഖ്യാപിച്ച ശേഷം ഇത് മീഡിയ പാനലിൽ ലഭ്യമാകും.");
    }

    /**
     * Publish / release the official team standings to the public website.
     */
    public function publishPublicStandings(Request $request): RedirectResponse
    {
        $customLabel = $request->input('custom_label', $request->input('label'));
        $count = Group::publishStandings($customLabel);

        return back()->with('success', "Official public team standings successfully published (After {$count} Results).");
    }

    /**
     * Update milestone release configuration.
     */
    public function updateMilestones(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:manual,milestone'],
            'milestones' => ['nullable', 'string'],
        ]);

        FestivalSetting::set('public_standings_mode', $validated['mode']);
        if (isset($validated['milestones'])) {
            $parsed = array_values(array_filter(
                array_map('intval', array_map('trim', explode(',', (string) $validated['milestones']))),
                fn (int $n) => $n > 0
            ));
            FestivalSetting::set('public_standings_milestones', ! empty($parsed) ? implode(',', $parsed) : '10,15,20,27,35,40');
        }

        return back()->with('success', 'Public team standings milestone release settings successfully updated.');
    }
}
