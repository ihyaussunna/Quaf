<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\FestivalSetting;
use App\Models\Program;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'live_fest_mode' => FestivalSetting::get('live_fest_mode', '1'),
            'festival_name' => FestivalSetting::get('festival_name', 'QUAF — Season 09'),
            'festival_dates' => FestivalSetting::get('festival_dates', 'October 24 - 28, 2026'),
            'tagline' => FestivalSetting::get('tagline', 'The Grand Cultural Conclave of Talents'),
            'registration_open' => FestivalSetting::get('registration_open', '1'),
            'student_editing_open' => FestivalSetting::get('student_editing_open', '1'),
            'registration_start' => FestivalSetting::get('registration_start', ''),
            'registration_end' => FestivalSetting::get('registration_end', ''),
            'organizer' => FestivalSetting::get('organizer', 'Ihyaussunna Students Union, Markazu Saquafathi Sunniyya'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $keys = [
            'live_fest_mode',
            'festival_name',
            'festival_dates',
            'tagline',
            'registration_open',
            'student_editing_open',
            'registration_start',
            'registration_end',
            'organizer',
        ];

        foreach ($keys as $key) {
            $value = $request->input($key, '0');
            FestivalSetting::set($key, (string) $value);
        }

        AuditLogger::log('update_settings', null, null, $request->only($keys));

        return back()->with('success', 'Festival settings updated successfully.');
    }

    public function toggleRegistration(Request $request): RedirectResponse
    {
        $current = FestivalSetting::get('registration_open', '1');
        $new = ($current === '1' || $current === true) ? '0' : '1';
        FestivalSetting::set('registration_open', $new);

        $statusText = $new === '1' ? 'തുറന്നു (Opened / Allowed)' : 'ബ്ലോക്ക് ചെയ്തു (Blocked / Closed)';
        AuditLogger::log('toggle_registration', null, ['registration_open' => $current], ['registration_open' => $new]);

        return back()->with('success', "പ്രോഗ്രാം രജിസ്ട്രേഷൻ പോർട്ടൽ വിജയകരമായി {$statusText}.");
    }

    public function toggleStudentEditing(Request $request): RedirectResponse
    {
        $current = FestivalSetting::get('student_editing_open', '1');
        $new = ($current === '1' || $current === true) ? '0' : '1';
        FestivalSetting::set('student_editing_open', $new);

        $statusText = $new === '1' ? 'തുറന്നു (Allowed / Open)' : 'ബ്ലോക്ക് ചെയ്തു (Blocked / Closed)';
        AuditLogger::log('toggle_student_editing', null, ['student_editing_open' => $current], ['student_editing_open' => $new]);

        return back()->with('success', "വിദ്യാർത്ഥികളുടെ പേര് / വിവരങ്ങൾ തിരുത്താനുള്ള സൗകര്യം വിജയകരമായി {$statusText}.");
    }

    public function markSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'points_weight' => ['required', 'numeric', 'min:0'],
        ]);

        $program = Program::findOrFail($validated['program_id']);
        $program->update(['points_weight' => $validated['points_weight']]);

        AuditLogger::log('update_program_mark_setting', $program, null, ['points_weight' => $validated['points_weight']]);

        return back()->with('success', "Mark for '{$program->name}' updated to {$validated['points_weight']}.");
    }

    public function limitSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'group_limit' => ['required', 'integer', 'min:1'],
        ]);

        FestivalSetting::set('group_limit_'.$validated['program_id'], (string) $validated['group_limit']);

        AuditLogger::log('update_group_limit', null, null, $validated);

        return back()->with('success', "Group program limit updated to {$validated['group_limit']}.");
    }

    public function broadcastMessage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        Announcement::create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'priority' => 'important',
            'target_role' => 'all',
            'is_active' => true,
        ]);

        AuditLogger::log('broadcast_message', null, null, $validated);

        return back()->with('success', 'Message broadcasted to teams and students.');
    }

    public function deadlineSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'deadline' => ['required', 'date'],
        ]);

        FestivalSetting::set('registration_end', (string) $validated['deadline']);

        Announcement::create([
            'title' => $validated['title'],
            'message' => ($validated['message'] ?? '')." | Deadline: {$validated['deadline']}",
            'priority' => 'urgent',
            'target_role' => 'all',
            'is_active' => true,
        ]);

        AuditLogger::log('update_deadline', null, null, $validated);

        return back()->with('success', "Deadline set to {$validated['deadline']} and notification published.");
    }

    public function scoreDisplaySettings(Request $request): RedirectResponse
    {
        $count = $request->input('declare_count', 'Off');
        FestivalSetting::set('score_display_count', (string) $count);

        AuditLogger::log('update_score_display', null, null, ['declare_count' => $count]);

        return back()->with('success', "Score display setting set to {$count}.");
    }
}
