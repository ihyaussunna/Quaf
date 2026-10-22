<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TopicPickerController;
use App\Models\Group;
use App\Models\MediaCriticTopic;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MediaCriticTopicController extends Controller
{
    /**
     * Display all media critic topics and allocations for admins.
     */
    public function index(): View
    {
        TopicPickerController::ensureTableAndData();

        $topics = MediaCriticTopic::with(['group', 'pickedBy'])
            ->orderBy('topic_number')
            ->get();

        $groups = Group::orderBy('name')->get();

        $claimedCount = $topics->where('is_locked', true)->count();
        $remainingCount = $topics->where('is_locked', false)->count();

        return view('admin.media-critic.index', compact('topics', 'groups', 'claimedCount', 'remainingCount'));
    }

    /**
     * Reset a single topic assignment.
     */
    public function reset(MediaCriticTopic $topic): RedirectResponse
    {
        $oldGroup = $topic->group?->name;

        $topic->update([
            'group_id' => null,
            'picked_by_user_id' => null,
            'picked_at' => null,
            'selection_method' => 'card',
            'is_locked' => false,
        ]);

        AuditLogger::log('reset_media_critic_topic', $topic, null, [
            'topic_id' => $topic->id,
            'topic_number' => $topic->topic_number,
            'cleared_group' => $oldGroup,
            'reset_by' => Auth::id(),
        ]);

        return back()->with('success', "വിഷയം #{$topic->topic_number} ({$topic->code_letter}) വിജയകരമായി റീസെറ്റ് ചെയ്തു (Topic reset successfully).");
    }

    /**
     * Reset all media critic topic assignments.
     */
    public function resetAll(): RedirectResponse
    {
        MediaCriticTopic::query()->update([
            'group_id' => null,
            'picked_by_user_id' => null,
            'picked_at' => null,
            'selection_method' => 'card',
            'is_locked' => false,
        ]);

        AuditLogger::log('reset_all_media_critic_topics', null, null, [
            'reset_by' => Auth::id(),
            'timestamp' => now()->toIso8601String(),
        ]);

        return back()->with('success', 'എല്ലാ മീഡിയ ക്രിറ്റിക് വിഷയങ്ങളും റീസെറ്റ് ചെയ്തു. ടീമുകൾക്ക് വീണ്ടും തിരഞ്ഞെടുക്കാം (All topics reset).');
    }

    /**
     * Manually assign a topic to a group.
     */
    public function assign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'topic_id' => ['required', 'exists:media_critic_topics,id'],
            'group_id' => ['required', 'exists:groups,id'],
        ]);

        $topic = MediaCriticTopic::findOrFail($validated['topic_id']);
        $group = Group::findOrFail($validated['group_id']);

        // Check if group already has another topic
        $existing = MediaCriticTopic::where('group_id', $group->id)
            ->where('id', '!=', $topic->id)
            ->first();

        if ($existing) {
            $existing->update([
                'group_id' => null,
                'picked_by_user_id' => null,
                'picked_at' => null,
                'is_locked' => false,
            ]);
        }

        $topic->update([
            'group_id' => $group->id,
            'picked_by_user_id' => Auth::id(),
            'picked_at' => now(),
            'selection_method' => 'admin',
            'is_locked' => true,
        ]);

        AuditLogger::log('manual_assign_media_critic_topic', $topic, null, [
            'topic_id' => $topic->id,
            'group_id' => $group->id,
            'assigned_by' => Auth::id(),
        ]);

        return back()->with('success', "'{$group->name}' ടീമിന് വിഷയം #{$topic->topic_number} മാനുവലായി നൽകി (Topic assigned manually).");
    }

    /**
     * Update topic title text.
     */
    public function update(Request $request, MediaCriticTopic $topic): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:500'],
        ]);

        $topic->update($validated);

        return back()->with('success', "വിഷയം #{$topic->topic_number} അപ്‌ഡേറ്റ് ചെയ്തു (Topic title updated).");
    }
}
