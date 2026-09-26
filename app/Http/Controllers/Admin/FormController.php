<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormController extends Controller
{
    public function callList(Request $request): View
    {
        $zones = Program::ZONES;
        $selectedZone = $request->query('zone', $request->query('category'));
        $selectedProgramId = $request->query('program', $request->query('program_id'));

        $programsQuery = Program::with(['category', 'stage', 'schedule']);
        if ($selectedZone) {
            $programsQuery->where('eligibility', $selectedZone);
        }
        $programs = $programsQuery->orderBy('name')->get();

        $selectedProgram = null;
        if ($selectedProgramId) {
            $selectedProgram = Program::with([
                'category',
                'stage',
                'schedule',
                'entries.student.group',
                'entries.group',
                'entries.participants.group',
            ])->find($selectedProgramId);
        }

        if ($request->boolean('print') && $selectedProgram) {
            return view('admin.forms.call-list-print', compact('selectedProgram'));
        }

        return view('admin.forms.call-list', compact(
            'zones',
            'programs',
            'selectedZone',
            'selectedProgramId',
            'selectedProgram'
        ));
    }

    public function evaluation(Request $request): View
    {
        $zones = Program::ZONES;
        $selectedZone = $request->query('zone', $request->query('category'));
        $selectedProgramId = $request->query('program', $request->query('program_id'));

        $programsQuery = Program::with(['category', 'stage']);
        if ($selectedZone) {
            $programsQuery->where('eligibility', $selectedZone);
        }
        $programs = $programsQuery->orderBy('name')->get();

        $selectedProgram = null;
        if ($selectedProgramId) {
            $selectedProgram = Program::with([
                'category',
                'stage',
                'scoringCriteria',
                'entries.student.group',
                'entries.group',
                'judges',
            ])->find($selectedProgramId);
        }

        if ($request->boolean('print') && $selectedProgram) {
            return view('admin.forms.evaluation-print', compact('selectedProgram'));
        }

        return view('admin.forms.evaluation', compact(
            'zones',
            'programs',
            'selectedZone',
            'selectedProgramId',
            'selectedProgram'
        ));
    }
}
