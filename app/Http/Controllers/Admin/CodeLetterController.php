<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CodeLetterController extends Controller
{
    public function index(Request $request): View
    {
        $zones = Program::ZONES;
        $selectedZone = $request->query('zone', $request->query('category'));
        $selectedProgramId = $request->query('program');

        $programsQuery = Program::query();
        if ($selectedZone) {
            $programsQuery->where('eligibility', $selectedZone);
        }
        $programs = $programsQuery->orderBy('name')->get();

        $selectedProgram = null;
        $entries = collect();

        if ($selectedProgramId) {
            $selectedProgram = Program::with(['category'])->find($selectedProgramId);
            if ($selectedProgram) {
                $entries = ProgramEntry::where('program_id', $selectedProgram->id)
                    ->with(['student.group', 'group'])
                    ->orderBy('code_letter')
                    ->orderBy('chest_number')
                    ->get();
            }
        }

        $categories = collect();

        return view('admin.code-letters.index', compact(
            'zones',
            'categories',
            'programs',
            'selectedZone',
            'selectedProgramId',
            'selectedProgram',
            'entries'
        ));
    }

    public function save(Request $request, Program $program): RedirectResponse
    {
        $letters = $request->input('letters', []);

        foreach ($letters as $entryId => $letter) {
            ProgramEntry::where('id', $entryId)
                ->where('program_id', $program->id)
                ->update(['code_letter' => strtoupper(trim($letter)) ?: null]);
        }

        AuditLogger::log('update_code_letters', $program, null, ['count' => count($letters)]);

        return back()->with('success', 'Code letters updated successfully.');
    }

    public function autoAssign(Request $request, Program $program): RedirectResponse
    {
        $entries = ProgramEntry::where('program_id', $program->id)->get()->shuffle();
        $alphabet = range('A', 'Z');

        foreach ($entries as $index => $entry) {
            $letter = $alphabet[$index] ?? ('C'.($index + 1));
            $entry->update(['code_letter' => $letter]);
        }

        AuditLogger::log('auto_assign_code_letters', $program, null, ['count' => $entries->count()]);

        return back()->with('success', "Code letters randomly assigned for {$entries->count()} participants.");
    }
}
