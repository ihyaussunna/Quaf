<?php

namespace App\Http\Controllers\ProgramCommittee;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Stage;
use App\Models\Zone;
use App\Services\AuditLogger;
use App\Services\GroupEntryStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProgramCommitteeController extends Controller
{
    public function __construct(
        protected GroupEntryStatsService $groupStatsService
    ) {}

    /**
     * Display Program Committee Dashboard with summary metrics.
     */
    public function dashboard(): View
    {
        $totalPrograms = Program::count();
        $withRulesCount = Program::whereNotNull('rules')->where('rules', '!=', '')->count();
        $missingRulesCount = $totalPrograms - $withRulesCount;

        $stageProgramsCount = Program::where('is_stage', true)->count();
        $nonStageProgramsCount = $totalPrograms - $stageProgramsCount;

        $individualCount = Program::where('type', 'individual')->count();
        $groupCount = Program::where('type', 'group')->count();

        $zones = Zone::withCount('programs')->orderBy('display_order')->get();

        $recentPrograms = Program::with(['zone', 'category', 'stage'])
            ->latest('updated_at')
            ->take(8)
            ->get();

        $pendingRulesPrograms = Program::with(['zone', 'category'])
            ->where(function ($q) {
                $q->whereNull('rules')->orWhere('rules', '');
            })
            ->orderBy('name')
            ->take(10)
            ->get();

        $totalEntriesCount = ProgramEntry::count();
        $statsData = $this->groupStatsService->buildGroupStats();

        return view('program-committee.dashboard', compact(
            'totalPrograms',
            'withRulesCount',
            'missingRulesCount',
            'stageProgramsCount',
            'nonStageProgramsCount',
            'individualCount',
            'groupCount',
            'zones',
            'recentPrograms',
            'pendingRulesPrograms',
            'totalEntriesCount',
            'statsData'
        ));
    }

    /**
     * Display program list with filters and Niyamavali status.
     */
    public function index(Request $request): View
    {
        $zoneId = $request->query('zone_id');
        $zone = $request->query('zone');
        $categoryId = $request->query('category_id');
        $type = $request->query('type');
        $isStage = $request->query('is_stage');
        $rulesStatus = $request->query('rules_status');
        $search = $request->query('search');

        $query = Program::with(['category', 'stage', 'zone', 'scoringCriteria'])->withCount('entries');

        if ($zoneId) {
            $query->where('zone_id', $zoneId);
        } elseif ($zone) {
            $query->where(function ($q) use ($zone) {
                $q->where('eligibility', $zone)
                    ->orWhereHas('zone', fn ($zq) => $zq->where('name', $zone));
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($type && in_array($type, ['individual', 'group'])) {
            $query->where('type', $type);
        }

        if ($isStage !== null && $isStage !== '') {
            $query->where('is_stage', (bool) $isStage);
        }

        if ($rulesStatus === 'with_rules') {
            $query->whereNotNull('rules')->where('rules', '!=', '');
        } elseif ($rulesStatus === 'missing_rules') {
            $query->where(function ($q) {
                $q->whereNull('rules')->orWhere('rules', '');
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('malayalam_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy('code')->paginate(20)->withQueryString();
        $categories = ProgramCategory::all();
        $stages = Stage::all();
        $zones = Zone::orderBy('display_order')->get();

        $totalCount = Program::count();
        $withRulesTotal = Program::whereNotNull('rules')->where('rules', '!=', '')->count();
        $missingRulesTotal = $totalCount - $withRulesTotal;
        $totalEntriesTotal = ProgramEntry::count();

        return view('program-committee.programs.index', compact(
            'programs',
            'categories',
            'stages',
            'zones',
            'zoneId',
            'zone',
            'categoryId',
            'type',
            'isStage',
            'rulesStatus',
            'search',
            'totalCount',
            'withRulesTotal',
            'missingRulesTotal',
            'totalEntriesTotal'
        ));
    }

    /**
     * Show form to add a new program.
     */
    public function create(): View
    {
        $categories = ProgramCategory::all();
        if ($categories->isEmpty()) {
            ProgramCategory::firstOrCreate(['slug' => 'general'], ['name' => 'General']);
            $categories = ProgramCategory::all();
        }
        $stages = Stage::all();
        $zones = Zone::orderBy('display_order')->get();

        // Calculate a suggested next code e.g. Q9-145
        $lastProgram = Program::orderBy('id', 'desc')->first();
        $suggestedNumber = ($lastProgram ? $lastProgram->id + 1 : 1);
        $suggestedCode = 'Q9-'.str_pad($suggestedNumber, 3, '0', STR_PAD_LEFT);

        return view('program-committee.programs.create', compact('categories', 'stages', 'zones', 'suggestedCode'));
    }

    /**
     * Store a newly created program.
     */
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
            'points_weight' => ['required', 'numeric', 'min:0.5', 'max:20'],
            'status' => ['required', 'in:upcoming,in_progress,completed,cancelled'],
            'is_stage' => ['nullable', 'boolean'],
            'gender_restriction' => ['nullable', 'in:all,male,female'],
            'criteria' => ['nullable', 'array'],
            'criteria.*.name' => ['nullable', 'string', 'max:255'],
            'criteria.*.max_marks' => ['nullable', 'integer', 'min:1', 'max:100'],
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

        // Scoring criteria handling: only create criteria if hasCriteria is true
        if ($hasCriteria && ! empty($validated['criteria'])) {
            foreach ($validated['criteria'] as $c) {
                if (! empty($c['name']) && ! empty($c['max_marks'])) {
                    $program->scoringCriteria()->create([
                        'criterion_name' => $c['name'],
                        'max_marks' => $c['max_marks'],
                    ]);
                }
            }
        }

        // If hasCriteria is true and no criteria entered, supply sensible defaults
        if ($hasCriteria && $program->scoringCriteria()->count() === 0) {
            $defaults = [
                ['criterion_name' => 'അവതരണ മികവ് / Performance', 'max_marks' => 30],
                ['criterion_name' => 'ഉള്ളടക്കവും അറിവും / Content', 'max_marks' => 30],
                ['criterion_name' => 'ശൈലിയും ഭാവവും / Style & Expression', 'max_marks' => 25],
                ['criterion_name' => 'സമയനിഷ്ഠ / Time Adherence', 'max_marks' => 15],
            ];
            foreach ($defaults as $d) {
                $program->scoringCriteria()->create($d);
            }
        }

        AuditLogger::log('create_program_by_committee', $program, null, $program->toArray());

        Cache::flush();
        try {
            Artisan::call('view:clear');
        } catch (\Throwable) {
        }

        return redirect()->route('program-committee.programs.show', $program)
            ->with('success', "Program '{$program->name}' has been created successfully with rules.");
    }

    /**
     * Show program details and Niyamavali.
     */
    public function show(Program $program): View
    {
        $program->load([
            'category',
            'stage',
            'zone',
            'schedule',
            'scoringCriteria',
            'entries.group',
            'entries.student',
            'judges',
        ]);

        return view('program-committee.programs.show', compact('program'));
    }

    /**
     * Show form to edit an existing program.
     */
    public function edit(Program $program): View
    {
        $categories = ProgramCategory::all();
        $stages = Stage::all();
        $zones = Zone::orderBy('display_order')->get();
        $program->load('scoringCriteria');

        return view('program-committee.programs.edit', compact('program', 'categories', 'stages', 'zones'));
    }

    /**
     * Update an existing program.
     */
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
            'points_weight' => ['required', 'numeric', 'min:0.5', 'max:20'],
            'status' => ['required', 'in:upcoming,in_progress,completed,cancelled'],
            'is_stage' => ['nullable', 'boolean'],
            'gender_restriction' => ['nullable', 'in:all,male,female'],
            'criteria' => ['nullable', 'array'],
            'criteria.*.name' => ['nullable', 'string', 'max:255'],
            'criteria.*.max_marks' => ['nullable', 'integer', 'min:1', 'max:100'],
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
        } elseif ($request->has('criteria')) {
            $program->scoringCriteria()->delete();
            foreach ($request->input('criteria', []) as $c) {
                if (! empty($c['name']) && ! empty($c['max_marks'])) {
                    $program->scoringCriteria()->create([
                        'criterion_name' => $c['name'],
                        'max_marks' => $c['max_marks'],
                    ]);
                }
            }
        }

        AuditLogger::log('update_program_by_committee', $program, $old, $program->toArray());

        Cache::flush();
        try {
            Artisan::call('view:clear');
        } catch (\Throwable) {
        }

        return redirect()->route('program-committee.programs.show', $program)
            ->with('success', "Program '{$program->name}' updated successfully.");
    }

    /**
     * Delete a program.
     */
    public function destroy(Program $program): RedirectResponse
    {
        if ($program->entries()->count() > 0) {
            return back()->withErrors([
                'delete' => "Cannot delete program '{$program->name}' because participants are already registered. Remove entries first.",
            ]);
        }

        $name = $program->name;
        $old = $program->toArray();
        $program->scoringCriteria()->delete();
        $program->delete();

        AuditLogger::log('delete_program_by_committee', null, $old, null);

        Cache::flush();
        try {
            Artisan::call('view:clear');
        } catch (\Throwable) {
        }

        return redirect()->route('program-committee.programs.index')
            ->with('success', "Program '{$name}' has been deleted.");
    }

    /**
     * Dedicated Niyamavali Hub listing all programs and their rules status.
     */
    public function niyamavaliIndex(Request $request): View
    {
        $zoneId = $request->query('zone_id');
        $status = $request->query('status'); // 'all', 'with_rules', 'missing_rules'
        $search = $request->query('search');

        $query = Program::with(['zone', 'category', 'scoringCriteria'])->withCount('entries');

        if ($zoneId) {
            $query->where('zone_id', $zoneId);
        }

        if ($status === 'with_rules') {
            $query->whereNotNull('rules')->where('rules', '!=', '');
        } elseif ($status === 'missing_rules') {
            $query->where(function ($q) {
                $q->whereNull('rules')->orWhere('rules', '');
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('malayalam_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy('code')->paginate(25)->withQueryString();
        $zones = Zone::orderBy('display_order')->get();

        $totalCount = Program::count();
        $withRulesCount = Program::whereNotNull('rules')->where('rules', '!=', '')->count();
        $missingRulesCount = $totalCount - $withRulesCount;

        return view('program-committee.niyamavali.index', compact(
            'programs',
            'zones',
            'zoneId',
            'status',
            'search',
            'totalCount',
            'withRulesCount',
            'missingRulesCount'
        ));
    }

    /**
     * Show dedicated Niyamavali editor for a specific program.
     */
    public function editRules(Program $program): View
    {
        $program->load(['zone', 'category', 'stage', 'scoringCriteria']);

        return view('program-committee.niyamavali.edit', compact('program'));
    }

    /**
     * Save/update Niyamavali & Criteria for a specific program.
     */
    public function updateRules(Request $request, Program $program): RedirectResponse
    {
        $validated = $request->validate([
            'rules' => ['nullable', 'string'],
            'has_time_limit' => ['nullable', 'boolean'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'has_criteria' => ['nullable', 'boolean'],
            'criteria' => ['nullable', 'array'],
            'criteria.*.name' => ['required_with:criteria.*.max_marks', 'string', 'max:255'],
            'criteria.*.max_marks' => ['required_with:criteria.*.name', 'integer', 'min:1', 'max:100'],
        ]);

        Program::ensureSchema();

        $hasTimeLimit = $request->boolean('has_time_limit', true);
        $hasCriteria = $request->boolean('has_criteria', true);

        $programData = [
            'rules' => $validated['rules'] ?? null,
        ];

        if (Schema::hasColumn('programs', 'has_time_limit')) {
            $programData['has_time_limit'] = $hasTimeLimit;
        }

        if (Schema::hasColumn('programs', 'has_criteria')) {
            $programData['has_criteria'] = $hasCriteria;
        }

        if ($hasTimeLimit) {
            $programData['duration_minutes'] = ! empty($validated['duration_minutes']) ? (int) $validated['duration_minutes'] : ($program->duration_minutes ?: 15);
        } else {
            $programData['duration_minutes'] = null;
        }

        $program->update($programData);

        // Update Scoring Criteria: if hasCriteria is false, remove all criteria rows
        if (! $hasCriteria) {
            $program->scoringCriteria()->delete();
        } elseif ($request->has('criteria')) {
            $program->scoringCriteria()->delete();
            foreach ($request->input('criteria', []) as $c) {
                if (! empty($c['name']) && ! empty($c['max_marks'])) {
                    $program->scoringCriteria()->create([
                        'criterion_name' => $c['name'],
                        'max_marks' => $c['max_marks'],
                    ]);
                }
            }
        }

        AuditLogger::log('update_niyamavali', $program, null, [
            'rules' => $program->rules,
            'has_time_limit' => $hasTimeLimit,
            'has_criteria' => $hasCriteria,
        ]);

        return redirect()->route('program-committee.programs.show', $program)
            ->with('success', "Niyamavali (നിയമാവലി) for '{$program->name}' updated successfully.");
    }

    /**
     * Print official Niyamavali sheet for a single program.
     */
    public function printRules(Program $program): View
    {
        $program->load(['zone', 'category', 'stage', 'scoringCriteria']);

        return view('program-committee.niyamavali.print', compact('program'));
    }

    /**
     * Print official booklet of all Niyamavali.
     */
    public function printAllRules(Request $request): View
    {
        $zoneId = $request->query('zone_id');
        $query = Program::with(['zone', 'category', 'stage', 'scoringCriteria'])
            ->whereNotNull('rules')
            ->where('rules', '!=', '');

        if ($zoneId) {
            $query->where('zone_id', $zoneId);
        }

        $programs = $query->orderBy('code')->get();
        $zone = $zoneId ? Zone::find($zoneId) : null;

        return view('program-committee.niyamavali.print-all', compact('programs', 'zone'));
    }

    /**
     * Display Team Entries Data, quota fulfillment, and competition matrix.
     */
    public function teamEntries(Request $request): View
    {
        $zones = Zone::orderBy('display_order')->get();
        $groups = Group::orderBy('name')->get();

        $selectedZoneId = $request->query('zone_id');
        $selectedGroupId = $request->query('group');
        $filterStatus = $request->query('filter_status', 'all'); // all, full, partial, pending
        $search = $request->query('search');
        $activeTab = $request->query('tab', 'matrix'); // matrix or entries
        $entryStatus = $request->query('entry_status', 'all');

        $statsData = $this->groupStatsService->buildGroupStats($selectedZoneId ? (int) $selectedZoneId : null);

        $matrix = $this->groupStatsService->buildMatrix(
            $statsData['programs'],
            $groups,
            $search,
            $filterStatus,
            $selectedGroupId ? (int) $selectedGroupId : null
        );

        // Query direct program entries for itemized view
        $entriesQuery = ProgramEntry::with(['program.zone', 'program.category', 'group', 'student', 'participants'])
            ->latest();

        if ($selectedGroupId) {
            $entriesQuery->where('group_id', $selectedGroupId);
        }

        if ($selectedZoneId) {
            $entriesQuery->whereHas('program', fn ($q) => $q->where('zone_id', $selectedZoneId));
        }

        if ($entryStatus && $entryStatus !== 'all') {
            $entriesQuery->where('status', $entryStatus);
        }

        if ($search) {
            $entriesQuery->where(function ($q) use ($search) {
                $q->where('chest_number', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('student_id', 'like', "%{$search}%"))
                    ->orWhereHas('program', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")->orWhere('malayalam_name', 'like', "%{$search}%"));
            });
        }

        $entries = $entriesQuery->paginate(25)->withQueryString();

        $selectedGroup = $selectedGroupId ? $groups->firstWhere('id', $selectedGroupId) : null;

        return view('program-committee.entries.index', compact(
            'zones',
            'groups',
            'selectedZoneId',
            'selectedGroupId',
            'selectedGroup',
            'filterStatus',
            'search',
            'activeTab',
            'entryStatus',
            'statsData',
            'matrix',
            'entries'
        ));
    }

    /**
     * Export team entries as a CSV file.
     */
    public function exportTeamEntries(Request $request): StreamedResponse
    {
        $selectedZoneId = $request->query('zone_id');
        $selectedGroupId = $request->query('group');
        $entryStatus = $request->query('entry_status', 'all');

        $entriesQuery = ProgramEntry::with(['program.zone', 'group', 'student', 'participants'])
            ->latest();

        if ($selectedGroupId) {
            $entriesQuery->where('group_id', $selectedGroupId);
        }

        if ($selectedZoneId) {
            $entriesQuery->whereHas('program', fn ($q) => $q->where('zone_id', $selectedZoneId));
        }

        if ($entryStatus && $entryStatus !== 'all') {
            $entriesQuery->where('status', $entryStatus);
        }

        $entries = $entriesQuery->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="QUAF-Team-Entries-'.now()->format('Y-m-d-His').'.csv"',
        ];

        return response()->stream(function () use ($entries) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Program Code',
                'Program Name',
                'Zone',
                'Format',
                'Limit',
                'Group',
                'Candidate Chest No',
                'Candidate Name',
                'Class',
                'Participants Count',
                'Entry Status',
                'Created At',
            ]);

            foreach ($entries as $e) {
                $candidates = $e->student?->name ?? 'Group Entry';
                if ($e->participants->isNotEmpty()) {
                    $candidates = $e->participants->pluck('name')->implode(', ');
                }

                $chestNos = $e->chest_number ?? ($e->student?->chest_number ?? '');
                if ($e->participants->isNotEmpty()) {
                    $chestNos = $e->participants->pluck('chest_number')->filter()->implode(', ');
                }

                fputcsv($file, [
                    $e->program?->code ?? '-',
                    $e->program?->name ?? '-',
                    $e->program?->zone?->name ?? ($e->program?->eligibility ?? 'Mix Zone'),
                    ucfirst($e->program?->type ?? 'individual'),
                    $e->program?->limit ?? 1,
                    $e->group?->name ?? '-',
                    $chestNos,
                    $candidates,
                    $e->student?->class ?? '-',
                    $e->participants->count() ?: 1,
                    ucfirst($e->status ?? 'pending'),
                    $e->created_at?->format('Y-m-d H:i') ?? '-',
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }
}
