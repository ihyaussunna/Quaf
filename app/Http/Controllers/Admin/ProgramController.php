<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FestivalSetting;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Stage;
use App\Models\Zone;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
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

        $type = $request->query('type');
        if ($type && in_array($type, ['individual', 'group'])) {
            $query->where('type', $type);
        }

        $regStatus = $request->query('reg_status');
        if ($regStatus === 'open') {
            $query->where('is_registration_open', true);
        } elseif ($regStatus === 'closed') {
            $query->where('is_registration_open', false);
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

        $isStageRegOpen = (FestivalSetting::get('stage_registration_open', '1') == '1');
        $isOffStageRegOpen = (FestivalSetting::get('off_stage_registration_open', '1') == '1');
        $isGlobalRegOpen = (FestivalSetting::get('registration_open', '1') == '1');

        return view('admin.programs.index', compact('programs', 'categories', 'stages', 'zones', 'zone', 'zoneId', 'categoryId', 'stageId', 'status', 'isStage', 'gender', 'type', 'search', 'regStatus', 'isStageRegOpen', 'isOffStageRegOpen', 'isGlobalRegOpen'));
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

        // Query programs to display in main view
        $displayQuery = Program::with([
            'category',
            'stage',
            'zone',
            'entries.student.group',
            'entries.participants.group',
        ])->withCount('entries');

        if ($selectedZoneId) {
            $displayQuery->where('zone_id', $selectedZoneId);
        } elseif ($selectedZone) {
            $displayQuery->where(function ($q) use ($selectedZone) {
                $q->where('eligibility', $selectedZone)
                    ->orWhereHas('zone', fn ($zq) => $zq->where('name', $selectedZone));
            });
        } elseif ($selectedCategoryId) {
            $displayQuery->where('category_id', $selectedCategoryId);
        }

        if ($selectedProgramId) {
            $displayQuery->where('id', $selectedProgramId);
        } else {
            $displayQuery->whereHas('entries');
        }

        $displayedPrograms = $displayQuery->orderBy('code')->orderBy('name')->get();

        return view('admin.programs.program-wise', compact(
            'zones',
            'categories',
            'programs',
            'displayedPrograms',
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

        $limitCount = (int) ($validated['participant_count'] ?? 1);
        if (($validated['type'] ?? 'individual') === 'group') {
            $validated['participant_count'] = max(1, $limitCount);
            $validated['max_participants'] = max(1, $limitCount);
            $validated['max_participants_per_group'] = 1;
        } else {
            $validated['participant_count'] = max(1, $limitCount);
            $validated['max_participants_per_group'] = max(1, $limitCount);
            $validated['max_participants'] = max(1, $limitCount) * 5;
        }

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

        Cache::flush();
        try {
            Artisan::call('view:clear');
        } catch (\Throwable) {
        }

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

        $limitCount = (int) ($validated['participant_count'] ?? $program->participant_count ?? 1);
        $type = $validated['type'] ?? $program->type ?? 'individual';
        if ($type === 'group') {
            $validated['participant_count'] = max(1, $limitCount);
            $validated['max_participants'] = max(1, $limitCount);
            $validated['max_participants_per_group'] = 1;
        } else {
            $validated['participant_count'] = max(1, $limitCount);
            $validated['max_participants_per_group'] = max(1, $limitCount);
            $validated['max_participants'] = max(1, $limitCount) * 5;
        }

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

        Cache::flush();
        try {
            Artisan::call('view:clear');
        } catch (\Throwable) {
        }

        return redirect()->route('admin.programs.index')->with('success', "Program '{$program->name}' updated.");
    }

    public function destroy(Program $program): RedirectResponse
    {
        $old = $program->toArray();
        $name = $program->name;
        $program->delete();

        AuditLogger::log('delete_program', null, $old, null);

        Cache::flush();
        try {
            Artisan::call('view:clear');
        } catch (\Throwable) {
        }

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

    public function toggleRegistration(Program $program): JsonResponse|RedirectResponse
    {
        Program::ensureSchema();

        $current = (bool) ($program->is_registration_open ?? true);
        $new = ! $current;

        $old = ['is_registration_open' => $current];
        $program->update(['is_registration_open' => $new]);

        AuditLogger::log('toggle_program_registration', $program, $old, ['is_registration_open' => $new]);

        Cache::flush();
        try {
            Artisan::call('view:clear');
        } catch (\Throwable) {
        }

        $statusText = $new ? 'opened (തുറന്നു)' : 'closed (ക്ലോസ് ചെയ്തു)';
        $message = "Registration for '{$program->name}' has been {$statusText}.";

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'program_id' => $program->id,
                'is_registration_open' => $new,
                'is_effective_open' => $program->isRegistrationOpen(),
                'closure_reason' => $program->getRegistrationClosureReason(),
                'closure_reason_ml' => $program->getRegistrationClosureReasonMl(),
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function bulkToggleRegistration(Request $request): JsonResponse|RedirectResponse
    {
        Program::ensureSchema();

        $validated = $request->validate([
            'action' => ['required', 'in:open,close'],
            'target' => ['nullable', 'in:selected,stage,off_stage,all'],
            'program_ids' => ['nullable', 'array'],
            'program_ids.*' => ['integer', 'exists:programs,id'],
        ]);

        $action = $validated['action'];
        $isOpen = ($action === 'open');
        $target = $validated['target'] ?? (empty($validated['program_ids']) ? 'all' : 'selected');

        $query = Program::query();
        $targetDesc = 'Competitions';

        if ($target === 'stage') {
            $query->where('is_stage', true);
            $targetDesc = 'Stage competitions (സ്റ്റേജ് മത്സരങ്ങൾ)';
            FestivalSetting::set('stage_registration_open', $isOpen ? '1' : '0');
        } elseif ($target === 'off_stage') {
            $query->where('is_stage', false);
            $targetDesc = 'Off-stage competitions (ഓഫ്-സ്റ്റേജ് മത്സരങ്ങൾ)';
            FestivalSetting::set('off_stage_registration_open', $isOpen ? '1' : '0');
        } elseif ($target === 'selected' || ! empty($validated['program_ids'])) {
            $programIds = array_filter(array_map('intval', (array) ($validated['program_ids'] ?? [])));
            if (empty($programIds)) {
                $msg = 'Please select at least one competition to update registration.';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }

                return back()->with('error', $msg);
            }
            $query->whereIn('id', $programIds);
            $targetDesc = count($programIds).' selected competitions';
        }

        $affectedCount = $query->update(['is_registration_open' => $isOpen]);

        AuditLogger::log('bulk_toggle_program_registration', null, null, [
            'action' => $action,
            'target' => $target,
            'affected_count' => $affectedCount,
        ]);

        Cache::flush();
        try {
            Artisan::call('view:clear');
        } catch (\Throwable) {
        }

        $actionWord = $isOpen ? 'opened (തുറന്നു)' : 'closed (ക്ലോസ് ചെയ്തു)';
        $message = "Registration {$actionWord} for {$affectedCount} {$targetDesc}.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'affected_count' => $affectedCount,
                'is_registration_open' => $isOpen,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
