<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Group;
use App\Models\Program;
use App\Models\Stage;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::with(['targetGroup', 'targetStage', 'targetProgram'])
            ->latest()
            ->paginate(15);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create(): View
    {
        $groups = Group::all();
        $stages = Stage::all();
        $programs = Program::orderBy('name')->get();

        return view('admin.announcements.create', compact('groups', 'stages', 'programs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'priority' => ['required', 'in:normal,important,urgent'],
            'target_role' => ['nullable', 'string'],
            'target_group_id' => ['nullable', 'exists:groups,id'],
            'target_stage_id' => ['nullable', 'exists:stages,id'],
            'target_program_id' => ['nullable', 'exists:programs,id'],
            'is_active' => ['boolean'],
        ]);

        $announcement = Announcement::create($validated);

        AuditLogger::log('create_announcement', $announcement, null, $announcement->toArray());

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement published.');
    }

    public function edit(Announcement $announcement): View
    {
        $groups = Group::all();
        $stages = Stage::all();
        $programs = Program::orderBy('name')->get();

        return view('admin.announcements.edit', compact('announcement', 'groups', 'stages', 'programs'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'priority' => ['required', 'in:normal,important,urgent'],
            'target_role' => ['nullable', 'string'],
            'target_group_id' => ['nullable', 'exists:groups,id'],
            'target_stage_id' => ['nullable', 'exists:stages,id'],
            'target_program_id' => ['nullable', 'exists:programs,id'],
            'is_active' => ['boolean'],
        ]);

        $old = $announcement->toArray();
        $announcement->update($validated);

        AuditLogger::log('update_announcement', $announcement, $old, $announcement->toArray());

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $old = $announcement->toArray();
        $announcement->delete();

        AuditLogger::log('delete_announcement', null, $old, null);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement deleted.');
    }
}
