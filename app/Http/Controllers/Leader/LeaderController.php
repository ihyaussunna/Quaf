<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Stage;
use App\Models\Student;
use App\Models\Zone;
use App\Services\AuditLogger;
use App\Services\EligibilityService;
use App\Services\PointCalculationService;
use App\Services\ScheduleConflictService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaderController extends Controller
{
    public function __construct(
        protected ScheduleConflictService $conflictService,
        protected EligibilityService $eligibilityService,
        protected PointCalculationService $pointCalculationService
    ) {}

    public function isRegistrationOpen(): bool
    {
        $open = FestivalSetting::get('registration_open', '1');
        if ($open === '0' || $open === false || $open === 'false') {
            return false;
        }

        $start = FestivalSetting::get('registration_start');
        $end = FestivalSetting::get('registration_end');

        $now = Carbon::now();

        if ($start && $now->lt(Carbon::parse($start))) {
            return false;
        }

        if ($end && $now->gt(Carbon::parse($end))) {
            return false;
        }

        return true;
    }

    protected function getGroup(): Group
    {
        $group = Group::where('leader_id', Auth::id())->first();
        if (! $group) {
            // Fallback: leader may be attached by id
            $group = Group::first();
        }

        return $group;
    }

    public function dashboard(): View
    {
        $group = $this->getGroup();

        // Load only recent students for dashboard preview instead of full group roster
        $students = $group->students()->with('zone')->take(10)->get();

        $entries = ProgramEntry::where('group_id', $group->id)
            ->with(['program.category', 'program.stage', 'program.schedule', 'program.zone', 'student', 'participants'])
            ->latest()
            ->take(10)
            ->get();

        $upcomingPrograms = Program::whereHas('entries', fn ($q) => $q->where('group_id', $group->id))
            ->whereIn('status', ['upcoming', 'in_progress'])
            ->with(['category', 'stage', 'schedule', 'zone'])
            ->take(5)
            ->get();

        $results = Result::where('status', 'published')
            ->where(function ($q) use ($group) {
                $q->whereHas('firstEntry', fn ($sq) => $sq->where('group_id', $group->id))
                    ->orWhereHas('secondEntry', fn ($sq) => $sq->where('group_id', $group->id))
                    ->orWhereHas('thirdEntry', fn ($sq) => $sq->where('group_id', $group->id));
            })
            ->with(['program', 'firstEntry.student', 'secondEntry.student', 'thirdEntry.student'])
            ->latest('published_at')
            ->take(5)
            ->get();

        $announcements = Announcement::where('is_active', true)
            ->where(function ($q) use ($group) {
                $q->whereNull('target_group_id')
                    ->orWhere('target_group_id', $group->id);
            })
            ->where(function ($q) {
                $q->whereNull('target_role')
                    ->orWhereIn('target_role', ['all', 'leader']);
            })
            ->latest()
            ->take(5)
            ->get();

        $isRegistrationOpen = $this->isRegistrationOpen();

        // Registration quota and completion statistics for the team
        $allGroupEntries = ProgramEntry::where('group_id', $group->id)
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->with(['participants'])
            ->get();
        $entriesByProgram = $allGroupEntries->groupBy('program_id');

        $eligiblePrograms = Program::where('status', 'upcoming')->get();
        $totalProgramsCount = $eligiblePrograms->count();

        $fullyRegisteredCount = 0;
        $partiallyRegisteredCount = 0;
        $unregisteredCount = 0;
        $partialSlotsNeeded = 0;
        $totalSlotsNeeded = 0;

        foreach ($eligiblePrograms as $p) {
            $limit = $p->limit;
            $pEntries = $entriesByProgram->get($p->id, collect());
            if ($p->isGroup()) {
                $firstEntry = $pEntries->first();
                $enrolled = $firstEntry ? max(1, $firstEntry->participants->count()) : 0;
            } else {
                $enrolled = $pEntries->count();
            }

            if ($enrolled >= $limit) {
                $fullyRegisteredCount++;
            } elseif ($enrolled > 0) {
                $partiallyRegisteredCount++;
                $diff = max(0, $limit - $enrolled);
                $partialSlotsNeeded += $diff;
                $totalSlotsNeeded += $diff;
            } else {
                $unregisteredCount++;
                $totalSlotsNeeded += $limit;
            }
        }

        $regProgressPercent = $totalProgramsCount > 0
            ? round(($fullyRegisteredCount / $totalProgramsCount) * 100, 1)
            : 0;

        $registrationStats = [
            'total' => $totalProgramsCount,
            'completed' => $fullyRegisteredCount,
            'partial' => $partiallyRegisteredCount,
            'pending' => $unregisteredCount,
            'partial_slots_needed' => $partialSlotsNeeded,
            'total_slots_needed' => $totalSlotsNeeded,
            'percent' => $regProgressPercent,
        ];

        $stats = [
            'students' => $group->students()->count(),
            'programs' => Cache::remember('programs_count_cached', 60, fn () => Program::count()),
            'groups' => Cache::remember('groups_count_cached', 300, fn () => Group::count()),
            'venues' => Cache::remember('stages_count_cached', 300, fn () => Stage::count()),
            'declared_results' => Cache::remember('declared_results_count_cached', 20, fn () => Result::where('status', 'published')->count()),
            'total_results' => Cache::remember('programs_count_cached', 60, fn () => Program::count()),
        ];

        $stats['progress_percent'] = $stats['total_results'] > 0
            ? round(($stats['declared_results'] / $stats['total_results']) * 100, 2)
            : 0;

        $leaderboard = Group::orderByDesc('points_cache')->get();
        $chartData = $this->pointCalculationService->getPerformanceChartData();

        return view('leader.dashboard', compact(
            'group',
            'students',
            'entries',
            'upcomingPrograms',
            'results',
            'announcements',
            'isRegistrationOpen',
            'stats',
            'registrationStats',
            'leaderboard',
            'chartData'
        ));

    }

    public function programs(Request $request): View
    {
        $group = $this->getGroup();
        $search = $request->query('search');
        $selectedZoneId = $request->query('zone_id');
        $selectedZone = $request->query('zone', $request->query('category'));

        $query = Program::with(['category', 'stage', 'zone']);

        if ($selectedZoneId) {
            $query->where('zone_id', $selectedZoneId);
        } elseif ($selectedZone) {
            $query->where(function ($q) use ($selectedZone) {
                $q->where('eligibility', $selectedZone)
                    ->orWhereHas('zone', fn ($zq) => $zq->where('name', $selectedZone)->orWhere('code', $selectedZone));
            });
        }

        $selectedType = $request->query('type');
        if ($selectedType && in_array($selectedType, ['individual', 'group'])) {
            $query->where('type', $selectedType);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy('name')->paginate(15)->withQueryString();
        $zones = Zone::orderBy('display_order')->get();

        return view('leader.programs', compact('group', 'programs', 'zones', 'search', 'selectedZone', 'selectedZoneId', 'selectedType'));
    }

    public function programWise(Request $request): View
    {
        $group = $this->getGroup();
        $zones = Zone::orderBy('display_order')->get();
        $selectedZoneId = $request->query('zone_id');
        $selectedZone = $request->query('zone', $request->query('category'));
        $selectedProgramId = $request->query('program');
        $statusFilter = $request->query('status', 'all');

        // Programs list for dropdown selector
        $programsQuery = Program::query();
        if ($selectedZoneId) {
            $programsQuery->where('zone_id', $selectedZoneId);
        } elseif ($selectedZone) {
            $programsQuery->where(function ($q) use ($selectedZone) {
                $q->where('eligibility', $selectedZone)
                    ->orWhereHas('zone', fn ($zq) => $zq->where('name', $selectedZone));
            });
        }
        $programs = $programsQuery->withCount(['entries as my_entries_count' => fn ($q) => $q->where('group_id', $group->id)])
            ->orderBy('code')
            ->orderBy('name')
            ->get();

        $selectedProgram = null;
        if ($selectedProgramId) {
            if (($selectedZone || $selectedZoneId) && ! $programs->contains('id', (int) $selectedProgramId)) {
                $selectedProgramId = null;
            } else {
                $selectedProgram = Program::with([
                    'category',
                    'stage',
                    'zone',
                    'entries' => fn ($q) => $q->where('group_id', $group->id)->with(['student', 'participants']),
                ])->find($selectedProgramId);
            }
        }

        // Query programs to display in the main content area
        $displayQuery = Program::with([
            'category',
            'stage',
            'zone',
            'entries' => fn ($q) => $q->where('group_id', $group->id)->with(['student', 'participants']),
        ])->withCount(['entries as my_entries_count' => fn ($q) => $q->where('group_id', $group->id)]);

        if ($selectedZoneId) {
            $displayQuery->where('zone_id', $selectedZoneId);
        } elseif ($selectedZone) {
            $displayQuery->where(function ($q) use ($selectedZone) {
                $q->where('eligibility', $selectedZone)
                    ->orWhereHas('zone', fn ($zq) => $zq->where('name', $selectedZone));
            });
        }

        if ($selectedProgramId) {
            $displayQuery->where('id', $selectedProgramId);
        } else {
            if ($statusFilter === 'registered') {
                $displayQuery->whereHas('entries', fn ($q) => $q->where('group_id', $group->id));
            } elseif (! $selectedZone && ! $selectedZoneId && ! $request->has('status')) {
                // If neither zone nor explicit status filter is chosen, show programs with entries by default
                $displayQuery->whereHas('entries', fn ($q) => $q->where('group_id', $group->id));
            }
        }

        $allDisplayedPrograms = $displayQuery->orderBy('code')->orderBy('name')->get();

        if (! $selectedProgramId && in_array($statusFilter, ['pending', 'completed'], true)) {
            $displayedPrograms = $allDisplayedPrograms->filter(function ($prog) use ($statusFilter) {
                $regCount = $prog->my_entries_count ?? $prog->entries->count();
                $limit = $prog->limit ?: 1;
                if ($statusFilter === 'pending') {
                    return $regCount < $limit;
                }
                if ($statusFilter === 'completed') {
                    return $regCount >= $limit;
                }

                return true;
            })->values();
        } else {
            $displayedPrograms = $allDisplayedPrograms;
        }

        $totalDisplayedEntries = $displayedPrograms->sum(fn ($p) => $p->entries->count());

        return view('leader.program-wise', compact(
            'group',
            'zones',
            'programs',
            'displayedPrograms',
            'selectedZone',
            'selectedZoneId',
            'selectedProgramId',
            'selectedProgram',
            'statusFilter',
            'totalDisplayedEntries'
        ));
    }

    public function studentWise(Request $request): View
    {
        $group = $this->getGroup();
        $isEditingOpen = (FestivalSetting::get('student_editing_open', '1') == '1');
        $selectedStudentId = $request->query('student');
        $search = $request->query('search');

        $studentsQuery = Student::where('group_id', $group->id)->with([
            'zone',
            'entries.program.category',
            'entries.program.stage',
            'entries.program.zone',
            'participations.program.category',
            'participations.program.stage',
            'participations.program.zone',
        ]);
        if ($search) {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }
        $students = $studentsQuery->orderBy('name')->get();

        $selectedStudent = null;
        if ($selectedStudentId) {
            $selectedStudent = Student::with([
                'zone',
                'entries.program.category',
                'entries.program.stage',
                'entries.program.zone',
                'participations.program.category',
                'participations.program.stage',
                'participations.program.zone',
            ])->where('group_id', $group->id)->find($selectedStudentId);
        }

        $isRegistrationOpen = $this->isRegistrationOpen();
        $allGroupEntries = ProgramEntry::where('group_id', $group->id)
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->get();
        $groupEntriesCount = $allGroupEntries->groupBy('program_id')->map->count();

        $individualPrograms = Program::with('zone')
            ->where('status', 'upcoming')
            ->where('type', 'individual')
            ->orderBy('name')
            ->get()
            ->map(function ($p) use ($groupEntriesCount) {
                $enrolled = $groupEntriesCount->get($p->id, 0);

                return [
                    'id' => $p->id,
                    'code' => $p->code ?: (string) $p->id,
                    'name' => $p->name,
                    'zone_id' => $p->zone_id,
                    'zone_name' => $p->zone?->name ?? ($p->eligibility ?? 'Mix Zone'),
                    'is_mix_zone' => (bool) $p->isMixZone(),
                    'mix_zone_open_to_all' => (bool) $p->mix_zone_open_to_all,
                    'allowed_zones' => $p->eligibility_rules['allowed_zones'] ?? [],
                    'limit' => $p->limit,
                    'enrolled' => $enrolled,
                    'remaining' => max(0, $p->limit - $enrolled),
                    'is_full' => ($enrolled >= $p->limit),
                    'is_stage' => (bool) $p->is_stage,
                ];
            });

        return view('leader.student-wise', compact(
            'group',
            'students',
            'selectedStudentId',
            'selectedStudent',
            'search',
            'isEditingOpen',
            'isRegistrationOpen',
            'individualPrograms'
        ));
    }

    public function students(Request $request): View
    {
        $group = $this->getGroup();
        $isEditingOpen = (FestivalSetting::get('student_editing_open', '1') == '1');
        $search = $request->query('search');

        $query = $group->students()->with(['zone', 'entries.program.category']);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }
        $students = $query->orderBy('name')->paginate(15)->withQueryString();
        $allStudentsForPrint = (clone $query)->orderBy('name')->get();

        return view('leader.students', compact('group', 'students', 'allStudentsForPrint', 'isEditingOpen', 'search'));
    }

    public function updateStudent(Request $request, Student $student): JsonResponse|RedirectResponse
    {
        $group = $this->getGroup();
        if ($student->group_id !== $group->id) {
            abort(403, 'Unauthorized student access.');
        }

        $isEditingOpen = (FestivalSetting::get('student_editing_open', '1') == '1');
        if (! $isEditingOpen) {
            $msg = 'സ്റ്റുഡന്റ് വിവരങ്ങൾ എഡിറ്റ് ചെയ്യുന്നത് അഡ്മിൻ ബ്ലോക്ക് ചെയ്തിരിക്കുന്നു (Student editing is currently blocked by Admin).';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }

            return back()->withErrors(['student_editing' => $msg]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $oldName = $student->name;
        $newName = trim($validated['name']);

        $student->update([
            'name' => $newName,
        ]);

        if ($student->user) {
            $student->user->update([
                'name' => $newName,
            ]);
        }

        AuditLogger::log('leader_edit_student_name', $student, ['name' => $oldName], ['name' => $newName]);

        $successMsg = "വിദ്യാർത്ഥിയുടെ പേര് '{$oldName}' എന്നതിൽ നിന്ന് '{$newName}' എന്ന് വിജയകരമായി തിരുത്തി.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                ],
            ]);
        }

        return back()->with('success', $successMsg);
    }

    public function registrations(): View
    {
        $group = $this->getGroup();

        $tab = request()->query('tab', 'all'); // 'all', 'pending', 'verified', 'unregistered'

        $entriesQuery = ProgramEntry::where('group_id', $group->id)
            ->with(['program.category', 'program.stage', 'program.zone', 'student', 'participants']);

        if ($tab === 'pending') {
            $entriesQuery->where('status', 'pending');
        } elseif ($tab === 'verified') {
            $entriesQuery->whereIn('status', ['verified', 'confirmed']);
        }

        $entries = $entriesQuery->latest()->paginate(15)->withQueryString();

        $allGroupEntries = ProgramEntry::where('group_id', $group->id)
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->with(['student.zone', 'participants.zone'])
            ->get();
        $entriesByProgram = $allGroupEntries->groupBy('program_id');
        $registeredProgramIds = $allGroupEntries->pluck('program_id')->unique()->toArray();

        $eligiblePrograms = Program::with('zone')->where('status', 'upcoming')->orderBy('code')->get();

        $totalProgramsCount = $eligiblePrograms->count();
        $registeredProgramsCount = count($registeredProgramIds);

        $fullyRegisteredCount = 0;
        $partiallyRegisteredCount = 0;
        $unregisteredCount = 0;
        $partialSlotsNeeded = 0;
        $totalSlotsNeeded = 0;
        $unregisteredPrograms = collect();

        $programsStatusList = $eligiblePrograms->map(function ($p) use (
            $entriesByProgram,
            &$fullyRegisteredCount,
            &$partiallyRegisteredCount,
            &$unregisteredCount,
            &$partialSlotsNeeded,
            &$totalSlotsNeeded,
            &$unregisteredPrograms
        ) {
            $limit = $p->limit;
            $pEntries = $entriesByProgram->get($p->id, collect());

            $enrolledStudents = [];
            if ($p->type === 'group') {
                $firstEntry = $pEntries->first();
                if ($firstEntry) {
                    $leaderStudent = $firstEntry->leaderStudent() ?? $firstEntry->student;
                    if ($firstEntry->participants && $firstEntry->participants->isNotEmpty()) {
                        $enrolledStudents = $firstEntry->participants->map(function ($st) use ($firstEntry, $leaderStudent) {
                            return [
                                'id' => $st->id,
                                'chest' => ltrim((string) ($st->chest_number ?: ($st->student_id ?: $st->id)), '#'),
                                'name' => $st->name,
                                'class' => $st->class_level ?? '',
                                'zone_id' => $st->zone_id,
                                'zone_name' => $st->zone?->name ?? 'N/A',
                                'is_leader' => ($leaderStudent && $leaderStudent->id === $st->id),
                                'entry_id' => $firstEntry->id,
                            ];
                        })->values()->all();
                    } elseif ($firstEntry->student) {
                        $st = $firstEntry->student;
                        $enrolledStudents = [[
                            'id' => $st->id,
                            'chest' => ltrim((string) ($st->chest_number ?: ($st->student_id ?: $st->id)), '#'),
                            'name' => $st->name,
                            'class' => $st->class_level ?? '',
                            'zone_id' => $st->zone_id,
                            'zone_name' => $st->zone?->name ?? 'N/A',
                            'is_leader' => true,
                            'entry_id' => $firstEntry->id,
                        ]];
                    }
                }
            } else {
                $enrolledStudents = $pEntries->map(function ($entry) {
                    $st = $entry->student;
                    if (! $st) {
                        return null;
                    }

                    return [
                        'id' => $st->id,
                        'chest' => ltrim((string) ($st->chest_number ?: ($st->student_id ?: $st->id)), '#'),
                        'name' => $st->name,
                        'class' => $st->class_level ?? '',
                        'zone_id' => $st->zone_id,
                        'zone_name' => $st->zone?->name ?? 'N/A',
                        'is_leader' => false,
                        'entry_id' => $entry->id,
                    ];
                })->filter()->values()->all();
            }

            $enrolled = count($enrolledStudents);
            $remaining = max(0, $limit - $enrolled);
            $percent = $limit > 0 ? min(100, round(($enrolled / $limit) * 100)) : 0;
            $zoneName = $p->zone?->name ?? ($p->eligibility ?? 'Mix Zone');

            if ($enrolled >= $limit) {
                $statusKey = 'completed';
                $statusLabel = 'Complete';
                $fullyRegisteredCount++;
            } elseif ($enrolled > 0) {
                $statusKey = 'partial';
                $statusLabel = "Partial ({$remaining} More Needed)";
                $partiallyRegisteredCount++;
                $partialSlotsNeeded += $remaining;
                $totalSlotsNeeded += $remaining;
                $unregisteredPrograms->push($p);
            } else {
                $statusKey = 'pending';
                $statusLabel = "Pending ({$limit} To Fill)";
                $unregisteredCount++;
                $totalSlotsNeeded += $limit;
                $unregisteredPrograms->push($p);
            }

            return [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'malayalam_name' => $p->malayalam_name,
                'type' => $p->type,
                'zone_id' => $p->zone_id,
                'zone_name' => $zoneName,
                'is_mix_zone' => (bool) $p->isMixZone(),
                'limit' => $limit,
                'enrolled' => $enrolled,
                'remaining' => $remaining,
                'needed' => $remaining,
                'percent' => $percent,
                'status_key' => $statusKey,
                'status_label' => $statusLabel,
                'enrolled_students' => $enrolledStudents,
            ];
        });

        $unregisteredProgramsCount = $unregisteredCount;

        $statusSummary = [
            'total' => $totalProgramsCount,
            'completed' => $fullyRegisteredCount,
            'partial' => $partiallyRegisteredCount,
            'pending' => $unregisteredCount,
            'action_required' => $partiallyRegisteredCount + $unregisteredCount,
            'partial_slots_needed' => $partialSlotsNeeded,
            'total_slots_needed' => $totalSlotsNeeded,
            'percent' => $totalProgramsCount > 0 ? round(($fullyRegisteredCount / $totalProgramsCount) * 100, 1) : 0,
        ];

        $students = $group->students()->with('zone')->orderBy('name')->get();
        $zones = Zone::orderBy('display_order')->get();
        if ($zones->isEmpty()) {
            $defaultZones = [
                ['code' => 'A_ZONE', 'name' => 'A Zone', 'slug' => 'a-zone', 'color_hex' => '#be1e2d', 'display_order' => 1],
                ['code' => 'B_ZONE', 'name' => 'B Zone', 'slug' => 'b-zone', 'color_hex' => '#f3bd2e', 'display_order' => 2],
                ['code' => 'C_ZONE', 'name' => 'C Zone', 'slug' => 'c-zone', 'color_hex' => '#005c94', 'display_order' => 3],
                ['code' => 'MIX_ZONE', 'name' => 'Mix Zone', 'slug' => 'mix-zone', 'color_hex' => '#009444', 'display_order' => 4],
            ];
            foreach ($defaultZones as $dz) {
                Zone::firstOrCreate(['code' => $dz['code']], $dz);
            }
            $zones = Zone::orderBy('display_order')->get();
        }
        $isRegistrationOpen = $this->isRegistrationOpen();

        return view('leader.registrations', compact(
            'group',
            'entries',
            'eligiblePrograms',
            'unregisteredPrograms',
            'registeredProgramIds',
            'entriesByProgram',
            'totalProgramsCount',
            'registeredProgramsCount',
            'fullyRegisteredCount',
            'partiallyRegisteredCount',
            'unregisteredProgramsCount',
            'programsStatusList',
            'statusSummary',
            'tab',
            'students',
            'zones',
            'isRegistrationOpen'
        ));
    }

    public function storeRegistration(Request $request): JsonResponse|RedirectResponse
    {
        $isAjax = $request->expectsJson() || $request->ajax();

        if (! $this->isRegistrationOpen()) {
            $msg = 'Registration window is currently closed. New registrations are not permitted.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => [$msg]], 422);
            }

            return back()->withInput()->withErrors([
                'registration' => $msg,
            ]);
        }

        $program = Program::with(['schedule', 'zone'])->findOrFail($request->input('program_id'));

        // If program is a group program, route to storeGroupRegistration
        if ($program->isGroup()) {
            return $this->storeGroupRegistration($request);
        }

        $group = $this->getGroup();

        $maxPerGroup = $program->limit;

        // Fetch existing active entries for this program and group
        $existingEntries = ProgramEntry::where('program_id', $program->id)
            ->where('group_id', $group->id)
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->get();
        $existingStudentIds = $existingEntries->pluck('student_id')->toArray();

        // Support both single student_id or array student_ids for individual programs
        $isRosterSync = $request->has('student_ids');
        $studentIds = $request->input('student_ids');
        if (empty($studentIds) && $request->filled('student_id')) {
            $submittedId = (int) $request->input('student_id');
            if (in_array($submittedId, $existingStudentIds)) {
                $msg = 'Student is already registered for this competition.';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => [$msg]], 422);
                }

                return back()->withInput()->withErrors(['student_id' => $msg]);
            }
            $studentIds = array_merge($existingStudentIds, [$submittedId]);
        }
        $studentIds = array_values(array_unique(array_filter((array) $studentIds)));

        // If no student_ids provided:
        if (empty($studentIds)) {
            if ($existingEntries->isNotEmpty()) {
                foreach ($existingEntries as $ee) {
                    $ee->participants()->detach();
                    $ee->delete();
                }
                $msg = "All participants for '{$program->name}' have been removed.";
                if ($isAjax) {
                    return response()->json([
                        'success' => true,
                        'message' => $msg,
                        'program_id' => $program->id,
                        'program_name' => $program->name,
                        'is_group' => false,
                        'enrolled_students' => [],
                        'enrolled_count' => 0,
                        'updated_students' => $this->getGroupStudentsQuotaData($group, $existingStudentIds),
                    ]);
                }

                return back()->with('success', $msg);
            }

            $msg = 'Please select at least one student for this competition.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => [$msg]], 422);
            }

            return back()->withInput()->withErrors(['student_id' => $msg]);
        }

        // Limit check on new desired list
        if (count($studentIds) > $maxPerGroup) {
            $msg = "This program allows at most {$maxPerGroup} participants per group. ".count($studentIds).' selected.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => [$msg]], 422);
            }

            return back()->withInput()->withErrors(['student_id' => $msg]);
        }

        $studentsToRemove = array_diff($existingStudentIds, $studentIds);
        $studentsToAdd = array_diff($studentIds, $existingStudentIds);

        // Validate newly added students
        $excludedEntryIds = $existingEntries->whereIn('student_id', $studentsToRemove)->pluck('id')->all();
        $newStudentsToRegister = [];
        foreach ($studentsToAdd as $stId) {
            $student = Student::with(['group', 'zone'])->where('group_id', $group->id)->find($stId);
            if (! $student) {
                $msg = "Selected student (ID: {$stId}) does not belong to {$group->name}.";
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => [$msg]], 422);
                }

                return back()->withInput()->withErrors(['student_id' => $msg]);
            }

            $eligibility = $this->eligibilityService->validateIndividualRegistration($student, $program, $excludedEntryIds);
            if (! $eligibility['valid']) {
                $errorField = $eligibility['field'] ?? 'student_id';
                $errorMsg = $eligibility['error'];
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $errorMsg, 'errors' => [$errorMsg]], 422);
                }

                return back()->withInput()->withErrors([
                    $errorField => $errorMsg,
                ]);
            }

            $newStudentsToRegister[] = $student;
        }

        $conflictsFound = [];

        DB::beginTransaction();
        try {
            // 1. Remove deleted entries
            if (! empty($studentsToRemove)) {
                $entriesToDelete = ProgramEntry::where('program_id', $program->id)
                    ->where('group_id', $group->id)
                    ->whereIn('student_id', $studentsToRemove)
                    ->get();
                foreach ($entriesToDelete as $ed) {
                    $ed->participants()->detach();
                    $ed->delete();
                }
            }

            // 2. Add new students with status 'verified' (directly verified)
            foreach ($newStudentsToRegister as $student) {
                $conflictFlag = false;
                $notes = null;

                if ($program->schedule) {
                    $conflicts = $this->conflictService->checkStudentConflict(
                        $student->id,
                        $program->schedule->start_time,
                        $program->schedule->end_time,
                        $program->id
                    );

                    if ($conflicts->isNotEmpty()) {
                        $conflictFlag = true;
                        $notes = $conflicts->first()['conflict_reason'];
                        $conflictsFound[] = $student->name;
                    }
                }

                $entry = $this->eligibilityService->registerIndividual($student, $program, [
                    'chest_number' => $student->student_id,
                    'status' => 'verified',
                    'conflict_flag' => $conflictFlag,
                    'notes' => $notes,
                ]);

                // Create GreenRoomCall
                $order = $program->greenRoomCalls()->count() + 1;
                $program->greenRoomCalls()->firstOrCreate(
                    ['entry_id' => $entry->id],
                    ['order_num' => $order, 'status' => 'waiting']
                );
            }

            // 3. Ensure any existing retained entries are marked 'verified'
            ProgramEntry::where('program_id', $program->id)
                ->where('group_id', $group->id)
                ->whereIn('status', ['pending'])
                ->update(['status' => 'verified']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $msg = 'Registration failed: '.$e->getMessage();
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => [$msg]], 422);
            }

            return back()->withInput()->withErrors([
                'registration' => $msg,
            ]);
        }

        // Fetch fresh enrolled students list for JSON response
        $freshEntries = ProgramEntry::where('program_id', $program->id)
            ->where('group_id', $group->id)
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->with(['student.zone'])
            ->get();

        $freshEnrolledStudents = [];
        foreach ($freshEntries as $fe) {
            if ($fe->student) {
                $st = $fe->student;
                $freshEnrolledStudents[] = [
                    'id' => $st->id,
                    'chest' => (string) ($st->student_id ?: $st->id),
                    'name' => $st->name,
                    'class' => $st->class_level ?? '',
                    'zone_name' => $st->zone?->name ?? ($st->category ?? 'Mix Zone'),
                    'is_leader' => false,
                    'entry_id' => $fe->id,
                ];
            }
        }

        $count = count($freshEnrolledStudents);
        $msg = "Successfully updated participants for '{$program->name}' ({$count} enrolled).";
        if (! empty($conflictsFound)) {
            $msg .= ' NOTICE: Schedule conflict detected for: '.implode(', ', $conflictsFound);
        }

        $allAffectedStudentIds = array_values(array_unique(array_filter(array_merge($existingStudentIds, $studentIds))));

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'program_id' => $program->id,
                'program_name' => $program->name,
                'is_group' => false,
                'enrolled_students' => $freshEnrolledStudents,
                'enrolled_count' => $count,
                'registered_count' => $count,
                'updated_students' => $this->getGroupStudentsQuotaData($group, $allAffectedStudentIds),
            ]);
        }

        return back()->with('success', $msg);
    }

    public function storeGroupRegistration(Request $request): JsonResponse|RedirectResponse
    {
        $isAjax = $request->expectsJson() || $request->ajax();

        if (! $this->isRegistrationOpen()) {
            $msg = 'Registration window is currently closed. New registrations are not permitted.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => [$msg]], 422);
            }

            return back()->withInput()->withErrors([
                'registration' => $msg,
            ]);
        }

        $group = $this->getGroup();

        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['exists:students,id'],
            'leader_id' => ['nullable', 'exists:students,id'],
            'chest_number' => ['nullable', 'string', 'max:20'],
        ]);

        $program = Program::with(['schedule', 'zone'])->findOrFail($validated['program_id']);
        $studentIds = array_values(array_unique(array_filter((array) $validated['student_ids'])));
        $leaderId = $validated['leader_id'] ?? ($studentIds[0] ?? null);

        // Check if an existing group entry already exists for this group
        $existingEntry = ProgramEntry::where('program_id', $program->id)
            ->where('group_id', $group->id)
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->first();

        // Validate via Eligibility Engine (pass existing entry ID to exclude if updating)
        $eligibility = $this->eligibilityService->validateGroupRegistration(
            $group,
            $program,
            $studentIds,
            $existingEntry?->id
        );

        if (! $eligibility['valid']) {
            $msg = $eligibility['error'];
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => [$msg]], 422);
            }

            return back()->withInput()->withErrors([
                $eligibility['field'] ?? 'student_ids' => $msg,
            ]);
        }

        try {
            if ($existingEntry) {
                // Update existing entry participants
                $existingEntry->participants()->detach();
                $leaderStudent = Student::find($leaderId) ?? Student::find($studentIds[0] ?? null);
                $allParticipantIds = array_values(array_unique(array_filter(array_merge([$leaderStudent?->id], $studentIds))));

                foreach ($allParticipantIds as $stId) {
                    $role = ($stId == $leaderStudent?->id) ? 'captain' : 'participant';
                    $existingEntry->participants()->attach($stId, ['role' => $role]);
                }

                $existingEntry->student_id = $leaderStudent?->id;
                $existingEntry->status = 'verified';
                if (! empty($validated['chest_number'])) {
                    $existingEntry->chest_number = $validated['chest_number'];
                }
                $existingEntry->save();
                $entry = $existingEntry;
            } else {
                $entry = $this->eligibilityService->registerGroup($group, $program, $studentIds, [
                    'status' => 'verified',
                    'leader_id' => $leaderId,
                    'chest_number' => $validated['chest_number'] ?? null,
                ]);
            }

            // Create GreenRoomCall
            $order = $program->greenRoomCalls()->count() + 1;
            $program->greenRoomCalls()->firstOrCreate(
                ['entry_id' => $entry->id],
                ['order_num' => $order, 'status' => 'waiting']
            );
        } catch (\Throwable $e) {
            $msg = 'Group registration failed: '.$e->getMessage();
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => [$msg]], 422);
            }

            return back()->withInput()->withErrors([
                'registration' => $msg,
            ]);
        }

        $freshEntry = ProgramEntry::where('id', $entry->id)->with(['participants.zone', 'student.zone'])->first();
        $freshEnrolledStudents = [];
        $leaderStudent = $freshEntry ? ($freshEntry->leaderStudent() ?? $freshEntry->student) : null;
        if ($freshEntry && $freshEntry->participants) {
            foreach ($freshEntry->participants as $st) {
                $freshEnrolledStudents[] = [
                    'id' => $st->id,
                    'chest' => (string) ($st->student_id ?: $st->id),
                    'name' => $st->name,
                    'class' => $st->class_level ?? '',
                    'zone_name' => $st->zone?->name ?? ($st->category ?? 'Mix Zone'),
                    'is_leader' => ($leaderStudent && $leaderStudent->id === $st->id),
                    'entry_id' => $freshEntry->id,
                ];
            }
        }

        $participantCount = count($freshEnrolledStudents);
        $msg = "Successfully registered {$group->name} team with {$participantCount} member(s) for '{$program->name}'.";

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'program_id' => $program->id,
                'program_name' => $program->name,
                'is_group' => true,
                'enrolled_students' => $freshEnrolledStudents,
                'enrolled_count' => $participantCount,
                'registered_count' => $participantCount,
                'updated_students' => $this->getGroupStudentsQuotaData($group, $studentIds),
            ]);
        }

        return back()->with('success', $msg);
    }

    public function editRegistration(ProgramEntry $entry): View|RedirectResponse
    {
        $group = $this->getGroup();
        if ($entry->group_id !== $group->id) {
            abort(403, 'Unauthorized access to group entry.');
        }

        if (! $this->isRegistrationOpen()) {
            return redirect()->route('leader.registrations')->withErrors([
                'registration' => 'Registration window is currently closed. Edits are not permitted.',
            ]);
        }

        $entry->load(['program.zone', 'student', 'participants']);
        $program = $entry->program;
        $students = $group->students()->with('zone')->orderBy('name')->get();

        return view('leader.registrations-edit', compact('group', 'entry', 'program', 'students'));
    }

    public function updateRegistration(Request $request, ProgramEntry $entry): RedirectResponse
    {
        $group = $this->getGroup();
        if ($entry->group_id !== $group->id) {
            abort(403, 'Unauthorized access to group entry.');
        }

        if (! $this->isRegistrationOpen()) {
            return back()->withInput()->withErrors([
                'registration' => 'Registration window is currently closed. Edits are not permitted.',
            ]);
        }

        $program = $entry->program;

        if ($program->isGroup()) {
            $validated = $request->validate([
                'student_ids' => ['required', 'array'],
                'student_ids.*' => ['exists:students,id'],
                'leader_id' => ['nullable', 'exists:students,id'],
            ]);

            $studentIds = $validated['student_ids'];
            $leaderId = $validated['leader_id'] ?? ($studentIds[0] ?? null);

            $eligibility = $this->eligibilityService->validateGroupRegistration($group, $program, $studentIds, $entry->id);
            if (! $eligibility['valid']) {
                return back()->withInput()->withErrors([
                    $eligibility['field'] ?? 'student_ids' => $eligibility['error'],
                ]);
            }

            // Sync participants
            $entry->participants()->detach();
            $leaderStudent = Student::find($leaderId) ?? Student::find($studentIds[0] ?? null);
            $allParticipantIds = array_values(array_unique(array_filter(array_merge([$leaderStudent?->id], $studentIds))));

            foreach ($allParticipantIds as $stId) {
                $role = ($stId == $leaderStudent?->id) ? 'captain' : 'participant';
                $entry->participants()->attach($stId, ['role' => $role]);
            }

            $entry->student_id = $leaderStudent?->id;
            $entry->status = 'verified';
            $entry->save();

            return redirect()->route('leader.registrations')->with('success', "Group entry for '{$program->name}' updated successfully.");
        } else {
            $validated = $request->validate([
                'student_id' => ['required', 'exists:students,id'],
            ]);

            $student = Student::with(['group', 'zone'])->where('group_id', $group->id)->findOrFail($validated['student_id']);
            $eligibility = $this->eligibilityService->validateIndividualRegistration($student, $program, $entry->id);
            if (! $eligibility['valid']) {
                return back()->withInput()->withErrors([
                    $eligibility['field'] ?? 'student_id' => $eligibility['error'],
                ]);
            }

            $entry->student_id = $student->id;
            $entry->chest_number = $student->student_id;
            $entry->status = 'verified';
            $entry->save();

            return redirect()->route('leader.registrations')->with('success', "Entry for '{$program->name}' updated with participant {$student->name}.");
        }
    }

    public function destroyRegistration(ProgramEntry $entry): JsonResponse|RedirectResponse
    {
        $group = $this->getGroup();
        if ($entry->group_id !== $group->id) {
            abort(403, 'Unauthorized access to group entry.');
        }

        if (! $this->isRegistrationOpen()) {
            $msg = 'Registration window is currently closed. Cancellations are not permitted.';
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->withErrors([
                'registration' => $msg,
            ]);
        }

        $program = $entry->program;
        $programName = $program?->name ?? 'Program';
        $studentId = $entry->student_id;
        $participantIds = $entry->participants()->pluck('students.id')->all();
        $affectedStudentIds = array_values(array_unique(array_filter(array_merge([$studentId], $participantIds))));

        $entry->participants()->detach();
        $entry->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Registration for '{$programName}' has been removed successfully.",
                'program_id' => $program?->id,
                'program_name' => $programName,
                'entry_id' => $entry->id,
                'updated_students' => $this->getGroupStudentsQuotaData($group, $affectedStudentIds),
            ]);
        }

        return redirect()->route('leader.registrations')->with('success', "Registration for '{$programName}' has been removed successfully.");
    }

    public function destroyByProgram(Program $program): JsonResponse|RedirectResponse
    {
        $group = $this->getGroup();
        if (! $this->isRegistrationOpen()) {
            $msg = 'Registration window is currently closed. Cancellations are not permitted.';
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->withErrors(['registration' => $msg]);
        }

        $entries = ProgramEntry::where('program_id', $program->id)
            ->where('group_id', $group->id)
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->get();

        $affectedStudentIds = [];
        foreach ($entries as $entry) {
            if ($entry->student_id) {
                $affectedStudentIds[] = $entry->student_id;
            }
            $pIds = $entry->participants()->pluck('students.id')->all();
            $affectedStudentIds = array_merge($affectedStudentIds, $pIds);
            $entry->participants()->detach();
            $entry->delete();
        }
        $affectedStudentIds = array_values(array_unique(array_filter($affectedStudentIds)));

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "All participants for '{$program->name}' have been removed successfully.",
                'program_id' => $program->id,
                'program_name' => $program->name,
                'updated_students' => $this->getGroupStudentsQuotaData($group, $affectedStudentIds),
            ]);
        }

        return redirect()->route('leader.registrations')->with('success', "All participants for '{$program->name}' have been removed.");
    }

    /**
     * Atomically swap a student from one program to another.
     */
    public function swapStudentProgram(Request $request): JsonResponse|RedirectResponse
    {
        $isAjax = $request->expectsJson() || $request->ajax();

        if (! $this->isRegistrationOpen()) {
            $msg = 'Registration window is currently closed. Program swaps are not permitted.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => [$msg]], 422);
            }

            return back()->withInput()->withErrors(['registration' => $msg]);
        }

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'from_entry_id' => ['required', 'exists:program_entries,id'],
            'to_program_id' => ['required', 'exists:programs,id'],
        ]);

        $group = $this->getGroup();
        $student = Student::with(['group', 'zone'])->where('group_id', $group->id)->findOrFail($validated['student_id']);

        $fromEntry = ProgramEntry::with('program')->where('group_id', $group->id)->findOrFail($validated['from_entry_id']);
        $fromProgram = $fromEntry->program;

        $toProgram = Program::with(['zone', 'schedule'])->findOrFail($validated['to_program_id']);

        if ($fromProgram->id === $toProgram->id) {
            $msg = 'Source and destination programs cannot be the same.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => [$msg]], 422);
            }

            return back()->withInput()->withErrors(['to_program_id' => $msg]);
        }

        if ($toProgram->isGroup()) {
            $msg = 'Group programs cannot be swapped via individual swap.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => [$msg]], 422);
            }

            return back()->withInput()->withErrors(['to_program_id' => $msg]);
        }

        // Validate eligibility for the destination program excluding the from_entry
        $eligibility = $this->eligibilityService->validateIndividualRegistration($student, $toProgram, $fromEntry->id);
        if (! $eligibility['valid']) {
            $errorMsg = $eligibility['error'];
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $errorMsg, 'errors' => [$errorMsg]], 422);
            }

            return back()->withInput()->withErrors(['to_program_id' => $errorMsg]);
        }

        $conflictFlag = false;
        $notes = null;
        if ($toProgram->schedule) {
            $conflicts = $this->conflictService->checkStudentConflict(
                $student->id,
                $toProgram->schedule->start_time,
                $toProgram->schedule->end_time,
                $toProgram->id
            );

            if ($conflicts->isNotEmpty()) {
                $conflictFlag = true;
                $notes = $conflicts->first()['conflict_reason'];
            }
        }

        DB::beginTransaction();
        try {
            // Delete old entry
            $fromEntry->participants()->detach();
            $fromEntry->delete();

            // Create new entry
            $newEntry = $this->eligibilityService->registerIndividual($student, $toProgram, [
                'chest_number' => $student->student_id,
                'status' => 'verified',
                'conflict_flag' => $conflictFlag,
                'notes' => $notes,
            ]);

            // Create GreenRoomCall
            $order = $toProgram->greenRoomCalls()->count() + 1;
            $toProgram->greenRoomCalls()->firstOrCreate(
                ['entry_id' => $newEntry->id],
                ['order_num' => $order, 'status' => 'waiting']
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $msg = 'Program swap failed: '.$e->getMessage();
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => [$msg]], 422);
            }

            return back()->withInput()->withErrors(['registration' => $msg]);
        }

        $successMsg = "വിദ്യാർത്ഥി '{$student->name}' ൻ്റെ മത്സരം '{$fromProgram->name}' ൽ നിന്ന് '{$toProgram->name}' ലേക്ക് വിജയകരമായി മാറ്റി.";

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'old_entry_id' => $fromEntry->id,
                'new_entry' => [
                    'id' => $newEntry->id,
                    'program_id' => $toProgram->id,
                    'program_code' => $toProgram->code ?: (string) $toProgram->id,
                    'program_name' => $toProgram->name,
                    'zone_name' => $toProgram->zone?->name ?? ($toProgram->eligibility ?? 'Mix Zone'),
                    'type' => $toProgram->type,
                    'is_stage' => (bool) $toProgram->is_stage,
                ],
                'updated_students' => $this->getGroupStudentsQuotaData($group, [$student->id]),
            ]);
        }

        return redirect()->route('leader.students-wise', ['student' => $student->id])->with('success', $successMsg);
    }

    /**
     * Get list of eligible, available programs for a given student (for fast inline add/swap).
     */
    public function eligibleProgramsForStudent(Request $request, Student $student): JsonResponse
    {
        $group = $this->getGroup();
        if ($student->group_id !== $group->id) {
            abort(403, 'Unauthorized student access.');
        }

        $excludeEntryId = $request->query('exclude_entry_id') ? (int) $request->query('exclude_entry_id') : null;

        // Fetch registered program ids for this student (excluding excludeEntryId)
        $studentRegisteredProgIds = ProgramEntry::where('student_id', $student->id)
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->when($excludeEntryId, fn ($q) => $q->where('id', '!=', $excludeEntryId))
            ->pluck('program_id')
            ->toArray();

        // Student's zone
        $studentZoneId = $student->zone_id;
        $studentZoneCode = $student->zone?->code ?? $student->category;
        $studentZoneName = $student->zone?->name ?? $student->category;

        // All active entries of the group to calculate group limit
        $allGroupEntries = ProgramEntry::where('group_id', $group->id)
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->get();
        $groupEntriesCount = $allGroupEntries->groupBy('program_id')->map->count();

        // Eligible programs: individual programs where zone matches or mix zone
        $programs = Program::with('zone')
            ->where('status', 'upcoming')
            ->where('type', 'individual')
            ->orderBy('name')
            ->get()
            ->filter(function ($prog) use ($studentZoneId, $studentZoneCode, $studentZoneName, $studentRegisteredProgIds, $groupEntriesCount) {
                // If student is already registered, skip
                if (in_array($prog->id, $studentRegisteredProgIds)) {
                    return false;
                }

                // Check zone
                if (! $prog->isMixZone()) {
                    if ($prog->zone_id && $studentZoneId && $prog->zone_id !== $studentZoneId) {
                        return false;
                    }
                } else {
                    if (! $prog->mix_zone_open_to_all) {
                        $rules = $prog->eligibility_rules ?? [];
                        if (! empty($rules['allowed_zones'])) {
                            $allowed = (array) $rules['allowed_zones'];
                            if (! in_array($studentZoneCode, $allowed) && ! in_array($studentZoneName, $allowed)) {
                                return false;
                            }
                        }
                    }
                }

                // Check group quota
                $enrolledForGroup = $groupEntriesCount->get($prog->id, 0);
                if ($enrolledForGroup >= $prog->limit) {
                    return false;
                }

                return true;
            })
            ->map(function ($prog) use ($groupEntriesCount) {
                $enrolled = $groupEntriesCount->get($prog->id, 0);
                $remaining = max(0, $prog->limit - $enrolled);

                return [
                    'id' => $prog->id,
                    'code' => $prog->code ?: (string) $prog->id,
                    'name' => $prog->name,
                    'zone_name' => $prog->zone?->name ?? ($prog->eligibility ?? 'Mix Zone'),
                    'limit' => $prog->limit,
                    'enrolled' => $enrolled,
                    'remaining' => $remaining,
                    'is_stage' => (bool) $prog->is_stage,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'programs' => $programs,
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'chest' => ltrim((string) ($student->chest_number ?: ($student->student_id ?: $student->id)), '#'),
                'individual_count' => $student->getIndividualParticipationCount(),
                'remaining_slots' => $student->getRemainingIndividualSlots(),
                'has_reached_individual_limit' => $student->hasReachedIndividualLimit(),
            ],
        ]);
    }

    /**
     * Get updated quota counts for given students or all group students.
     *
     * @param  array<int>|null  $studentIds
     * @return array<array{id: int, individual_count: int, has_reached_individual_limit: bool, remaining_slots: int}>
     */
    protected function getGroupStudentsQuotaData(Group $group, ?array $studentIds = null): array
    {
        $query = Student::where('group_id', $group->id);
        if (! empty($studentIds)) {
            $query->whereIn('id', $studentIds);
        }

        return $query->get()->map(function ($s) {
            $indCount = $s->getIndividualParticipationCount();

            return [
                'id' => $s->id,
                'individual_count' => $indCount,
                'has_reached_individual_limit' => ($indCount >= Student::MAX_INDIVIDUAL_PROGRAMS),
                'remaining_slots' => max(0, Student::MAX_INDIVIDUAL_PROGRAMS - $indCount),
            ];
        })->values()->all();
    }
}
