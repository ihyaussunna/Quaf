<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Stage;
use App\Models\Zone;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(Request $request): View
    {
        $zoneId = $request->query('zone_id');
        $zone = $request->query('zone');
        $categoryId = $request->query('category');
        $stageId = $request->query('stage');
        $status = $request->query('status');
        $isStage = $request->query('is_stage');
        $gender = $request->query('gender');
        $search = $request->query('search');

        $query = Program::with(['category', 'stage', 'schedule', 'zone'])->withCount('entries');

        if ($zoneId) {
            $query->where('zone_id', $zoneId);
        } elseif ($zone) {
            $query->where(function ($q) use ($zone) {
                $q->where('eligibility', $zone)
                    ->orWhereHas('zone', fn ($zq) => $zq->where('name', $zone)->orWhere('code', $zone));
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($stageId) {
            $query->where('stage_id', $stageId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($isStage !== null && $isStage !== '') {
            $query->where('is_stage', (bool) $isStage);
        }

        if ($gender && in_array($gender, ['all', 'male', 'female'])) {
            $query->where('gender_restriction', $gender);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('malayalam_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = ProgramCategory::all();
        $stages = Stage::all();
        $zones = Zone::orderBy('display_order')->get();

        return view('admin.programs.index', compact('programs', 'categories', 'stages', 'zones', 'zone', 'zoneId', 'categoryId', 'stageId', 'status', 'isStage', 'gender', 'search'));
    }

    public function programWise(Request $request): View
    {
        $zones = Zone::orderBy('display_order')->get();
        $categories = ProgramCategory::orderBy('name')->get();
        $selectedZoneId = $request->query('zone_id');
        $selectedZone = $request->query('zone');
        $selectedCategoryId = $request->query('category');
        $selectedProgramId = $request->query('program');

        $programsQuery = Program::with(['category', 'stage', 'zone']);
        if ($selectedZoneId) {
            $programsQuery->where('zone_id', $selectedZoneId);
        } elseif ($selectedZone) {
            $programsQuery->where(function ($q) use ($selectedZone) {
                $q->where('eligibility', $selectedZone)
                    ->orWhereHas('zone', fn ($zq) => $zq->where('name', $selectedZone));
            });
        } elseif ($selectedCategoryId) {
            $programsQuery->where('category_id', $selectedCategoryId);
        }
        $programs = $programsQuery->orderBy('name')->get();

        $selectedProgram = null;
        if ($selectedProgramId) {
            $selectedProgram = Program::with([
                'category',
                'stage',
                'zone',
                'entries.student.group',
                'entries.participants.group',
            ])->find($selectedProgramId);
        }

        return view('admin.programs.program-wise', compact(
            'zones',
            'categories',
            'programs',
            'selectedZone',
            'selectedZoneId',
            'selectedCategoryId',
            'selectedProgramId',
            'selectedProgram'
        ));
    }

    public function create(): View
    {
        $categories = ProgramCategory::all();
        if ($categories->isEmpty()) {
            ProgramCategory::firstOrCreate(['slug' => 'general'], ['name' => 'General']);
            $categories = ProgramCategory::all();
        }
        $stages = Stage::all();
        $zones = Zone::orderBy('display_order')->get();

        return view('admin.programs.create', compact('categories', 'stages', 'zones'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'malayalam_name' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:programs,code'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'category_id' => ['nullable'],
            'type' => ['required', 'in:individual,group'],
            'participant_count' => ['nullable', 'integer', 'min:1', 'max:50'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'max_participants_per_group' => ['nullable', 'integer', 'min:1', 'max:50'],
            'individual_limit_counted' => ['nullable', 'boolean'],
            'mix_zone_open_to_all' => ['nullable', 'boolean'],
            'eligibility' => ['nullable', 'string'],
            'rules' => ['nullable', 'string'],
            'has_time_limit' => ['nullable', 'boolean'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'has_criteria' => ['nullable', 'boolean'],
            'stage_id' => ['nullable', 'exists:stages,id'],
            'scheduled_time' => ['nullable', 'date'],
            'points_weight' => ['required', 'numeric', 'min:0.5', 'max:10'],
            'status' => ['required', 'in:upcoming,in_progress,completed,cancelled'],
            'is_stage' => ['nullable', 'boolean'],
            'gender_restriction' => ['nullable', 'in:all,male,female'],
        ]);

        $validated['is_stage'] = $request->boolean('is_stage');
        $validated['gender_restriction'] = $request->input('gender_restriction', 'all');
        $validated['individual_limit_counted'] = $request->boolean('individual_limit_counted', $validated['type'] === 'individual');
        $validated['mix_zone_open_to_all'] = $request->boolean('mix_zone_open_to_all', true);

        Program::ensureSchema();

        $hasTimeLimit = $request->boolean('has_time_limit', true);
        if (Schema::hasColumn('programs', 'has_time_limit')) {
            $validated['has_time_limit'] = $hasTimeLimit;
        } else {
            unset($validated['has_time_limit']);
        }

        if (! $hasTimeLimit) {
            $validated['duration_minutes'] = null;
        } elseif (empty($validated['duration_minutes'])) {
            $validated['duration_minutes'] = 15;
        }

        $hasCriteria = $request->boolean('has_criteria', true);
        if (Schema::hasColumn('programs', 'has_criteria')) {
            $validated['has_criteria'] = $hasCriteria;
        } else {
            unset($validated['has_criteria']);
        }

        if (! empty($validated['zone_id'])) {
            $zone = Zone::find($validated['zone_id']);
            $validated['eligibility'] = $zone?->name;
        }

        $categoryId = $validated['category_id'] ?? null;
        if ($categoryId && ProgramCategory::where('id', $categoryId)->exists()) {
            $validated['category_id'] = (int) $categoryId;
        } else {
            $defaultCategory = ProgramCategory::first() ?? ProgramCategory::firstOrCreate(
                ['slug' => 'general'],
                ['name' => 'General']
            );
            $validated['category_id'] = $defaultCategory?->id;
        }

        $program = Program::create($validated);

        // Default scoring criteria only if has_criteria is true
        if ($hasCriteria) {
            $criteria = [
                ['criterion_name' => 'Performance & Skill', 'max_marks' => 30],
                ['criterion_name' => 'Content & Depth', 'max_marks' => 30],
                ['criterion_name' => 'Presentation & Stage Presence', 'max_marks' => 25],
                ['criterion_name' => 'Timing & Adherence', 'max_marks' => 15],
            ];

            foreach ($criteria as $c) {
                $program->scoringCriteria()->create($c);
            }
        }

        AuditLogger::log('create_program', $program, null, $program->toArray());

        return redirect()->route('admin.programs.index')->with('success', "Program '{$program->name}' created successfully.");
    }

    public function show(Program $program): View
    {
        $program->load([
            'category',
            'stage',
            'zone',
            'schedule',
            'entries.student.group',
            'entries.participants.group',
            'scoringCriteria',
            'judges',
            'result.firstEntry.student',
            'result.secondEntry.student',
            'result.thirdEntry.student',
        ]);

        return view('admin.programs.show', compact('program'));
    }

    public function edit(Program $program): View
    {
        $categories = ProgramCategory::all();
        $stages = Stage::all();
        $zones = Zone::orderBy('display_order')->get();

        return view('admin.programs.edit', compact('program', 'categories', 'stages', 'zones'));
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'malayalam_name' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', "unique:programs,code,{$program->id}"],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'category_id' => ['nullable'],
            'type' => ['required', 'in:individual,group'],
            'participant_count' => ['nullable', 'integer', 'min:1', 'max:50'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'max_participants_per_group' => ['nullable', 'integer', 'min:1', 'max:50'],
            'individual_limit_counted' => ['nullable', 'boolean'],
            'mix_zone_open_to_all' => ['nullable', 'boolean'],
            'eligibility' => ['nullable', 'string'],
            'rules' => ['nullable', 'string'],
            'has_time_limit' => ['nullable', 'boolean'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'has_criteria' => ['nullable', 'boolean'],
            'stage_id' => ['nullable', 'exists:stages,id'],
            'scheduled_time' => ['nullable', 'date'],
            'points_weight' => ['required', 'numeric', 'min:0.5', 'max:10'],
            'status' => ['required', 'in:upcoming,in_progress,completed,cancelled'],
            'is_stage' => ['nullable', 'boolean'],
            'gender_restriction' => ['nullable', 'in:all,male,female'],
        ]);

        $validated['is_stage'] = $request->boolean('is_stage');
        $validated['gender_restriction'] = $request->input('gender_restriction', 'all');
        $validated['individual_limit_counted'] = $request->boolean('individual_limit_counted', $validated['type'] === 'individual');
        $validated['mix_zone_open_to_all'] = $request->boolean('mix_zone_open_to_all', true);

        Program::ensureSchema();

        $hasTimeLimit = $request->boolean('has_time_limit', true);
        if (Schema::hasColumn('programs', 'has_time_limit')) {
            $validated['has_time_limit'] = $hasTimeLimit;
        } else {
            unset($validated['has_time_limit']);
        }

        if (! $hasTimeLimit) {
            $validated['duration_minutes'] = null;
        } elseif (empty($validated['duration_minutes'])) {
            $validated['duration_minutes'] = $program->duration_minutes ?: 15;
        }

        $hasCriteria = $request->boolean('has_criteria', true);
        if (Schema::hasColumn('programs', 'has_criteria')) {
            $validated['has_criteria'] = $hasCriteria;
        } else {
            unset($validated['has_criteria']);
        }

        if (! empty($validated['zone_id'])) {
            $zone = Zone::find($validated['zone_id']);
            $validated['eligibility'] = $zone?->name;
        }

        $categoryId = $validated['category_id'] ?? $program->category_id;
        if ($categoryId && ProgramCategory::where('id', $categoryId)->exists()) {
            $validated['category_id'] = (int) $categoryId;
        } else {
            $defaultCategory = ProgramCategory::first() ?? ProgramCategory::firstOrCreate(
                ['slug' => 'general'],
                ['name' => 'General']
            );
            $validated['category_id'] = $defaultCategory?->id;
        }

        $old = $program->toArray();
        $program->update($validated);

        if (! $hasCriteria) {
            $program->scoringCriteria()->delete();
        }

        AuditLogger::log('update_program', $program, $old, $program->toArray());

        return redirect()->route('admin.programs.index')->with('success', "Program '{$program->name}' updated.");
    }

    public function destroy(Program $program): RedirectResponse
    {
        $old = $program->toArray();
        $name = $program->name;
        $program->delete();

        AuditLogger::log('delete_program', null, $old, null);

        return redirect()->route('admin.programs.index')->with('success', "Program '{$name}' deleted.");
    }

    public function updateCriteria(Request $request, Program $program): RedirectResponse
    {
        $request->validate([
            'criteria' => ['required', 'array'],
            'criteria.*.name' => ['required', 'string'],
            'criteria.*.max_marks' => ['required', 'integer', 'min:1'],
        ]);

        $program->scoringCriteria()->delete();

        foreach ($request->input('criteria') as $c) {
            $program->scoringCriteria()->create([
                'criterion_name' => $c['name'],
                'max_marks' => $c['max_marks'],
            ]);
        }

        return back()->with('success', 'Scoring criteria updated successfully.');
    }
}
