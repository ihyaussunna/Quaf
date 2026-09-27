<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Stage;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StageController extends Controller
{
    public function index(): View
    {
        $stages = Stage::with(['currentProgram', 'nextProgram'])
            ->withCount('programs')
            ->get();

        $programs = Program::where('is_stage', true)->orderBy('name')->get();

        return view('admin.stages.index', compact('stages', 'programs'));
    }

    public function create(): View
    {
        return view('admin.stages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:stages,code'],
            'location' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:active,break,closed'],
        ]);

        $stage = Stage::create($validated);

        AuditLogger::log('create_stage', $stage, null, $stage->toArray());

        return redirect()->route('admin.stages.index')->with('success', "Stage '{$stage->name}' created.");
    }

    public function edit(Stage $stage): View
    {
        $programs = Program::where('is_stage', true)->orderBy('name')->get();

        return view('admin.stages.edit', compact('stage', 'programs'));
    }

    public function update(Request $request, Stage $stage): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', "unique:stages,code,{$stage->id}"],
            'location' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:1'],
            'current_program_id' => ['nullable', 'exists:programs,id'],
            'next_program_id' => ['nullable', 'exists:programs,id'],
            'status' => ['required', 'in:active,break,closed'],
        ]);

        $old = $stage->toArray();
        $stage->update($validated);

        AuditLogger::log('update_stage', $stage, $old, $stage->toArray());

        return redirect()->route('admin.stages.index')->with('success', "Stage '{$stage->name}' updated.");
    }

    public function updateLiveStatus(Request $request, Stage $stage): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,break,closed'],
            'current_program_id' => ['nullable', 'exists:programs,id'],
            'next_program_id' => ['nullable', 'exists:programs,id'],
        ]);

        $stage->update($validated);

        // If current program changed, optionally mark that program in_progress
        if ($stage->current_program_id) {
            Program::where('id', $stage->current_program_id)->update(['status' => 'in_progress']);
        }

        return back()->with('success', "Stage '{$stage->name}' live status updated.");
    }

    public function destroy(Stage $stage): RedirectResponse
    {
        $old = $stage->toArray();
        $name = $stage->name;
        $stage->delete();

        AuditLogger::log('delete_stage', null, $old, null);

        return redirect()->route('admin.stages.index')->with('success', "Stage '{$name}' deleted.");
    }

    public function projector(Stage $stage): View
    {
        $stage->load([
            'currentProgram.entries.student',
            'currentProgram.entries.group',
            'currentProgram.greenRoomCalls.entry.student',
            'currentProgram.greenRoomCalls.entry.group',
            'nextProgram',
        ]);

        $currentCall = null;
        $upcomingCalls = collect();

        if ($stage->currentProgram) {
            $calls = $stage->currentProgram->greenRoomCalls()
                ->with(['entry.student.group', 'entry.group'])
                ->orderBy('created_at', 'asc')
                ->get();

            $currentCall = $calls->firstWhere('status', 'on_stage') ?? $calls->firstWhere('status', 'called');
            $upcomingCalls = $calls->whereIn('status', ['called', 'ready', 'pending'])
                ->where('id', '!=', $currentCall?->id)
                ->take(6);
        }

        return view('admin.stages.projector', compact('stage', 'currentCall', 'upcomingCalls'));
    }
}
