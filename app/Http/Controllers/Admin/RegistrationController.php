<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Student;
use App\Models\Zone;
use App\Services\AuditLogger;
use App\Services\EligibilityService;
use App\Services\ScheduleConflictService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(
        protected ScheduleConflictService $conflictService,
        protected EligibilityService $eligibilityService
    ) {}

    public function index(Request $request): View
    {
        $zones = Zone::orderBy('display_order')->get();
        $groups = Group::orderBy('name')->get();

        $selectedGroupId = $request->query('group');
        $selectedZoneId = $request->query('zone_id');
        $selectedZone = $request->query('zone', $request->query('category'));
        $search = $request->query('search');
        $status = $request->query('status', 'all');

        // Query direct program entries for verification
        $entriesQuery = ProgramEntry::with(['program.zone', 'program.category', 'group', 'student', 'participants']);

        if ($status && $status !== 'all') {
            $entriesQuery->where('status', $status);
        }

        if ($selectedGroupId) {
            $entriesQuery->where('group_id', $selectedGroupId);
        }

        if ($selectedZoneId) {
            $entriesQuery->whereHas('program', fn ($q) => $q->where('zone_id', $selectedZoneId));
        } elseif ($selectedZone) {
            $entriesQuery->whereHas('program', function ($q) use ($selectedZone) {
                $q->where('eligibility', $selectedZone)
                    ->orWhereHas('zone', fn ($zq) => $zq->where('name', $selectedZone)->orWhere('code', $selectedZone));
            });
        }

        if ($search) {
            $entriesQuery->where(function ($q) use ($search) {
                $q->where('chest_number', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('student_id', 'like', "%{$search}%"))
                    ->orWhereHas('program', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            });
        }

        $entries = $entriesQuery->latest()->paginate(25)->withQueryString();

        $pendingCount = ProgramEntry::where('status', 'pending')->count();
        $verifiedCount = ProgramEntry::where('status', 'verified')->count();
        $rejectedCount = ProgramEntry::where('status', 'rejected')->count();
        $totalEntriesCount = ProgramEntry::count();

        $selectedGroup = $selectedGroupId ? $groups->firstWhere('id', $selectedGroupId) : null;
        $categories = collect();

        // Compute group-wise entry statistics (Registered, Partial, Pending)
        $statsData = $this->buildGroupStats($selectedZoneId ? (int) $selectedZoneId : null);

        return view('admin.registrations.index', compact(
            'zones',
            'categories',
            'groups',
            'entries',
            'status',
            'pendingCount',
            'verifiedCount',
            'rejectedCount',
            'totalEntriesCount',
            'selectedGroupId',
            'selectedZone',
            'selectedZoneId',
            'selectedGroup',
            'search',
            'statsData'
        ));
    }

    public function stats(Request $request): View
    {
        $zones = Zone::orderBy('display_order')->get();
        $groups = Group::orderBy('name')->get();
        $selectedZoneId = $request->query('zone_id');
        $selectedGroupId = $request->query('group');
        $filterStatus = $request->query('filter_status', 'all'); // all, full, partial, pending
        $search = $request->query('search');

        $statsData = $this->buildGroupStats($selectedZoneId ? (int) $selectedZoneId : null);

        // Precompute matrix of programs with each group's status for the matrix view
        $allEntries = ProgramEntry::with('participants:id')
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->get()
            ->groupBy(['program_id', 'group_id']);

        $matrix = $statsData['programs']->map(function ($p) use ($groups, $allEntries) {
            $groupCells = [];
            foreach ($groups as $grp) {
                $pEntries = $allEntries->get($p->id)?->get($grp->id) ?? collect();
                $limit = $p->limit;
                if ($p->type === 'group') {
                    $first = $pEntries->first();
                    $enrolled = $first ? max(1, $first->participants->count()) : 0;
                } else {
                    $enrolled = $pEntries->count();
                }

                if ($enrolled >= $limit) {
                    $status = 'full';
                } elseif ($enrolled > 0) {
                    $status = 'partial';
                } else {
                    $status = 'pending';
                }

                $groupCells[$grp->id] = [
                    'status' => $status,
                    'enrolled' => $enrolled,
                    'limit' => $limit,
                    'remaining' => max(0, $limit - $enrolled),
                ];
            }

            return [
                'program' => $p,
                'groups' => $groupCells,
            ];
        });

        if ($search) {
            $matrix = $matrix->filter(function ($item) use ($search) {
                $p = $item['program'];

                return str_contains(strtolower($p->name), strtolower($search))
                    || str_contains(strtolower($p->code), strtolower($search));
            });
        }

        if ($filterStatus !== 'all') {
            $matrix = $matrix->filter(function ($item) use ($filterStatus, $selectedGroupId) {
                if ($selectedGroupId) {
                    return ($item['groups'][$selectedGroupId]['status'] ?? '') === $filterStatus;
                }

                // Any group matches filter status
                foreach ($item['groups'] as $cell) {
                    if ($cell['status'] === $filterStatus) {
                        return true;
                    }
                }

                return false;
            });
        }

        return view('admin.registrations.stats', compact(
            'zones',
            'groups',
            'selectedZoneId',
            'selectedGroupId',
            'filterStatus',
            'search',
            'statsData',
            'matrix'
        ));
    }

    /**
     * Compute comprehensive registration and quota fulfillment statistics for each group.
     *
     * @return array{groups: Collection, programs: Collection, total_programs: int, global_full_count: int, global_partial_count: int, global_pending_count: int, global_total_entries: int, global_verified_count: int, global_pending_verif_count: int}
     */
    protected function buildGroupStats(?int $zoneId = null): array
    {
        $groups = Group::orderBy('name')->get();
        $programsQuery = Program::with('zone')->where('status', 'upcoming')->orderBy('name');
        if ($zoneId) {
            $programsQuery->where('zone_id', $zoneId);
        }
        $programs = $programsQuery->get();
        if ($programs->isEmpty() && ! $zoneId) {
            $programs = Program::with('zone')->orderBy('name')->get();
        }

        $allEntries = ProgramEntry::with('participants:id')
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->get();

        $groupStats = $groups->map(function ($grp) use ($programs, $allEntries) {
            $grpEntries = $allEntries->where('group_id', $grp->id);
            $entriesByProgram = $grpEntries->groupBy('program_id');

            $fullPrograms = collect();
            $partialPrograms = collect();
            $pendingPrograms = collect();

            foreach ($programs as $p) {
                $limit = $p->limit;
                $pEntries = $entriesByProgram->get($p->id, collect());

                if ($p->type === 'group') {
                    $firstEntry = $pEntries->first();
                    $enrolled = $firstEntry ? max(1, $firstEntry->participants->count()) : 0;
                } else {
                    $enrolled = $pEntries->count();
                }

                $remaining = max(0, $limit - $enrolled);

                $programInfo = [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                    'zone' => $p->zone?->name ?? ($p->eligibility ?? 'Mix Zone'),
                    'type' => $p->type,
                    'limit' => $limit,
                    'enrolled' => $enrolled,
                    'remaining' => $remaining,
                ];

                if ($enrolled >= $limit) {
                    $fullPrograms->push($programInfo);
                } elseif ($enrolled > 0) {
                    $partialPrograms->push($programInfo);
                } else {
                    $pendingPrograms->push($programInfo);
                }
            }

            $totalEntries = $grpEntries->count();
            $verifiedEntries = $grpEntries->where('status', 'verified')->count();
            $pendingVerifEntries = $grpEntries->where('status', 'pending')->count();
            $totalProgramsCount = $programs->count();
            $progressPercent = $totalProgramsCount > 0
                ? round(($fullPrograms->count() / $totalProgramsCount) * 100)
                : 0;

            return [
                'group' => $grp,
                'total_entries' => $totalEntries,
                'verified_entries' => $verifiedEntries,
                'pending_verif_entries' => $pendingVerifEntries,
                'full_count' => $fullPrograms->count(),
                'partial_count' => $partialPrograms->count(),
                'pending_count' => $pendingPrograms->count(),
                'progress_percent' => $progressPercent,
                'full_programs' => $fullPrograms,
                'partial_programs' => $partialPrograms,
                'pending_programs' => $pendingPrograms,
            ];
        });

        $globalFullCount = $groupStats->sum('full_count');
        $globalPartialCount = $groupStats->sum('partial_count');
        $globalPendingCount = $groupStats->sum('pending_count');
        $globalTotalEntries = $allEntries->count();
        $globalVerifiedCount = $allEntries->where('status', 'verified')->count();
        $globalPendingVerifCount = $allEntries->where('status', 'pending')->count();

        return [
            'groups' => $groupStats,
            'programs' => $programs,
            'total_programs' => $programs->count(),
            'global_full_count' => $globalFullCount,
            'global_partial_count' => $globalPartialCount,
            'global_pending_count' => $globalPendingCount,
            'global_total_entries' => $globalTotalEntries,
            'global_verified_count' => $globalVerifiedCount,
            'global_pending_verif_count' => $globalPendingVerifCount,
        ];
    }

    public function create(): View
    {
        $programs = Program::with('zone')->orderBy('name')->get();
        $groups = Group::orderBy('name')->get();
        $students = Student::with(['group', 'zone'])->orderBy('student_id')->get();
        $zones = Zone::orderBy('display_order')->get();

        return view('admin.registrations.create', compact('programs', 'groups', 'students', 'zones'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'student_id' => ['nullable', 'exists:students,id'],
            'leader_id' => ['nullable', 'exists:students,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:students,id'],
            'chest_number' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:pending,verified,confirmed,rejected,cancelled,withdrawn'],
            'notes' => ['nullable', 'string'],
        ]);

        $program = Program::with(['schedule', 'zone'])->findOrFail($validated['program_id']);

        $requestedChestNumber = $validated['chest_number'] ?? null;
        if (! empty($requestedChestNumber)) {
            $duplicateChest = ProgramEntry::where('program_id', $program->id)
                ->where('chest_number', $requestedChestNumber)
                ->exists();
            if ($duplicateChest) {
                return back()->withInput()->withErrors([
                    'chest_number' => "Registration failed: Chest number (#{$requestedChestNumber}) already exists for this program.",
                ]);
            }
        }

        // A. Group Programme Registration (checked first if program is group or student_ids provided)
        if ($program->isGroup() || ! empty($validated['student_ids'])) {
            $studentIds = $validated['student_ids'] ?? [];

            // If group_id is empty, try to derive from leader or first student
            $groupId = $validated['group_id'] ?? null;
            if (empty($groupId) && ! empty($studentIds)) {
                $firstSt = Student::find($studentIds[0]);
                $groupId = $firstSt?->group_id;
            }

            if (empty($groupId)) {
                return back()->withInput()->withErrors(['group_id' => 'Please select a participating Group.']);
            }

            $group = Group::findOrFail($groupId);
            $leaderId = $validated['leader_id'] ?? ($validated['student_id'] ?? ($studentIds[0] ?? null));

            if (! empty($studentIds)) {
                $eligibility = $this->eligibilityService->validateGroupRegistration($group, $program, $studentIds);
                if (! $eligibility['valid']) {
                    return back()->withInput()->withErrors([
                        $eligibility['field'] ?? 'group_id' => $eligibility['error'],
                    ]);
                }

                try {
                    $entry = $this->eligibilityService->registerGroup($group, $program, $studentIds, [
                        'chest_number' => $requestedChestNumber,
                        'leader_id' => $leaderId,
                        'status' => $validated['status'],
                        'notes' => $validated['notes'] ?? null,
                    ]);
                } catch (\Throwable $e) {
                    return back()->withInput()->withErrors([
                        'registration' => 'Group registration failed: '.$e->getMessage(),
                    ]);
                }
            } else {
                // General group entry without student IDs (e.g. admin manual entry)
                $chestNum = $requestedChestNumber ?: ('G-'.$group->code.'-'.rand(100, 999));
                if (ProgramEntry::where('program_id', $program->id)->where('chest_number', $chestNum)->exists()) {
                    return back()->withInput()->withErrors([
                        'chest_number' => "Registration failed: Chest number (#{$chestNum}) already exists for this program.",
                    ]);
                }

                $entry = ProgramEntry::create([
                    'program_id' => $program->id,
                    'group_id' => $group->id,
                    'chest_number' => $chestNum,
                    'status' => $validated['status'],
                    'notes' => $validated['notes'] ?? null,
                ]);
            }

            return redirect()->route('admin.registrations.index')->with('success', "Group entry created for {$group->name} ({$entry->chest_number}).");
        }

        // B. Individual Student Registration
        if (! empty($validated['student_id'])) {
            $student = Student::with(['group', 'zone'])->findOrFail($validated['student_id']);
            $chestNum = $requestedChestNumber ?: $student->student_id;
            $validated['group_id'] = $student->group_id;

            // Check eligibility engine
            $eligibility = $this->eligibilityService->validateIndividualRegistration($student, $program);
            if (! $eligibility['valid']) {
                return back()->withInput()->withErrors([
                    $eligibility['field'] ?? 'student_id' => $eligibility['error'],
                ]);
            }

            if (ProgramEntry::where('program_id', $program->id)->where('chest_number', $chestNum)->exists()) {
                return back()->withInput()->withErrors([
                    'chest_number' => "Registration failed: Chest number (#{$chestNum}) already exists for this program.",
                ]);
            }

            // Conflict check if program has schedule
            if ($program->schedule) {
                $conflicts = $this->conflictService->checkStudentConflict(
                    $student->id,
                    $program->schedule->start_time,
                    $program->schedule->end_time,
                    $program->id
                );

                if ($conflicts->isNotEmpty()) {
                    $validated['conflict_flag'] = true;
                    $validated['notes'] = ($validated['notes'] ? $validated['notes'].' | ' : '').$conflicts->first()['conflict_reason'];
                }
            }

            try {
                $entry = DB::transaction(function () use ($validated, $student, $program, $chestNum) {
                    $entry = ProgramEntry::create([
                        'program_id' => $program->id,
                        'student_id' => $student->id,
                        'group_id' => $student->group_id,
                        'chest_number' => $chestNum,
                        'status' => $validated['status'],
                        'conflict_flag' => $validated['conflict_flag'] ?? false,
                        'notes' => $validated['notes'] ?? null,
                    ]);

                    return $entry;
                });
            } catch (\Throwable $e) {
                return back()->withInput()->withErrors([
                    'chest_number' => "Registration failed: Chest number (#{$chestNum}) already exists for this program.",
                ]);
            }

            AuditLogger::log('create_entry', $entry, null, $entry->toArray());

            return redirect()->route('admin.registrations.index')->with('success', "Entry created for Chest #{$entry->chest_number}.");
        }

        return back()->withInput()->withErrors(['student_id' => 'Please select a participant student or group.']);
    }

    public function verifyAllPending(): RedirectResponse
    {
        $pending = ProgramEntry::where('status', 'pending')->get();
        $count = $pending->count();

        foreach ($pending as $entry) {
            $entry->update(['status' => 'verified']);
            $program = $entry->program;
            if ($program) {
                $order = $program->greenRoomCalls()->count() + 1;
                $program->greenRoomCalls()->firstOrCreate(
                    ['entry_id' => $entry->id],
                    ['order_num' => $order, 'status' => 'waiting']
                );
            }
        }

        AuditLogger::log('verify_all_pending_entries', null, null, ['count' => $count]);

        return back()->with('success', "All {$count} pending registrations have been verified successfully.");
    }

    public function verify(ProgramEntry $entry): RedirectResponse
    {
        $entry->update(['status' => 'verified']);

        // Ensure GreenRoomCall entry exists
        $program = $entry->program;
        if ($program) {
            $order = $program->greenRoomCalls()->count() + 1;
            $program->greenRoomCalls()->firstOrCreate(
                ['entry_id' => $entry->id],
                ['order_num' => $order, 'status' => 'waiting']
            );
        }

        AuditLogger::log('verify_entry', $entry, ['status' => 'pending'], ['status' => 'verified']);

        return back()->with('success', "Entry for Chest #{$entry->chest_number} verified.");
    }

    public function reject(ProgramEntry $entry): RedirectResponse
    {
        $entry->update(['status' => 'rejected']);
        AuditLogger::log('reject_entry', $entry, null, ['status' => 'rejected']);

        return back()->with('success', "Entry for Chest #{$entry->chest_number} rejected. Slot has been released.");
    }

    public function cancel(ProgramEntry $entry): RedirectResponse
    {
        $entry->update(['status' => 'cancelled']);
        AuditLogger::log('cancel_entry', $entry, null, ['status' => 'cancelled']);

        return back()->with('success', "Entry for Chest #{$entry->chest_number} cancelled. Slot has been released.");
    }

    public function destroy(Request $request, $entry): RedirectResponse
    {
        $programEntry = $entry instanceof ProgramEntry ? $entry : ProgramEntry::findOrFail($entry);
        $chest = $programEntry->chest_number;
        $programName = $programEntry->program?->name ?? 'Program';
        $old = $programEntry->toArray();

        DB::transaction(function () use ($programEntry) {
            $programEntry->participants()->detach();
            $programEntry->greenRoomCall()?->delete();
            $programEntry->scoreSheets()->delete();
            $programEntry->pointsTransactions()->delete();
            $programEntry->certificate()?->delete();
            $programEntry->delete();
        });

        AuditLogger::log('delete_entry', null, $old, null);

        return back()->with('success', "Entry for Chest #{$chest} ({$programName}) deleted successfully.");
    }
}
