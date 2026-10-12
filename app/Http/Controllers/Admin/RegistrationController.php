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
use App\Services\GroupEntryStatsService;
use App\Services\ScheduleConflictService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(
        protected ScheduleConflictService $conflictService,
        protected EligibilityService $eligibilityService,
        protected GroupEntryStatsService $groupStatsService
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

        $matrix = $this->groupStatsService->buildMatrix(
            $statsData['programs'],
            $groups,
            $search,
            $filterStatus,
            $selectedGroupId ? (int) $selectedGroupId : null
        );

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
    public function buildGroupStats(?int $zoneId = null): array
    {
        return $this->groupStatsService->buildGroupStats($zoneId);
    }

    public function create(Request $request): View
    {
        $programs = Program::with('zone')->orderBy('code')->orderBy('name')->get();
        $groups = Group::orderBy('name')->get();
        $students = Student::with(['group', 'zone'])->orderBy('student_id')->get();
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

        $allEntries = ProgramEntry::whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->with(['student.zone', 'participants.zone'])
            ->get();

        $selectedGroupId = $request->query('group') ?: $request->query('group_id');

        $entriesByGroupAndProgram = [];
        foreach ($allEntries as $entry) {
            $gId = $entry->group_id;
            $pId = $entry->program_id;
            if (! isset($entriesByGroupAndProgram[$gId])) {
                $entriesByGroupAndProgram[$gId] = [];
            }
            if (! isset($entriesByGroupAndProgram[$gId][$pId])) {
                $entriesByGroupAndProgram[$gId][$pId] = [];
            }

            if ($entry->program && $entry->program->isGroup()) {
                $leaderStudent = $entry->leaderStudent() ?? $entry->student;
                if ($entry->participants && $entry->participants->isNotEmpty()) {
                    foreach ($entry->participants as $part) {
                        $entriesByGroupAndProgram[$gId][$pId][] = [
                            'id' => $part->id,
                            'chest' => ltrim((string) ($part->chest_number ?: ($part->student_id ?: $part->id)), '#'),
                            'name' => $part->name,
                            'class' => $part->class_level ?? '',
                            'zone_id' => $part->zone_id,
                            'zone_name' => $part->zone?->name ?? 'N/A',
                            'is_leader' => ($leaderStudent && $leaderStudent->id === $part->id),
                            'entry_id' => $entry->id,
                        ];
                    }
                } elseif ($entry->student) {
                    $part = $entry->student;
                    $entriesByGroupAndProgram[$gId][$pId][] = [
                        'id' => $part->id,
                        'chest' => ltrim((string) ($part->chest_number ?: ($part->student_id ?: $part->id)), '#'),
                        'name' => $part->name,
                        'class' => $part->class_level ?? '',
                        'zone_id' => $part->zone_id,
                        'zone_name' => $part->zone?->name ?? 'N/A',
                        'is_leader' => true,
                        'entry_id' => $entry->id,
                    ];
                }
            } else {
                if ($entry->student) {
                    $part = $entry->student;
                    $entriesByGroupAndProgram[$gId][$pId][] = [
                        'id' => $part->id,
                        'chest' => ltrim((string) ($part->chest_number ?: ($part->student_id ?: $part->id)), '#'),
                        'name' => $part->name,
                        'class' => $part->class_level ?? '',
                        'zone_id' => $part->zone_id,
                        'zone_name' => $part->zone?->name ?? 'N/A',
                        'is_leader' => false,
                        'entry_id' => $entry->id,
                    ];
                }
            }
        }

        $programsData = $programs->map(function ($p) {
            return [
                'id' => $p->id,
                'code' => $p->code ?: (string) $p->id,
                'name' => $p->name,
                'malayalam_name' => $p->malayalam_name ?? '',
                'type' => $p->type,
                'zone_id' => $p->zone_id,
                'zone_name' => $p->zone?->name ?? ($p->eligibility ?? 'Mix Zone'),
                'is_mix_zone' => (bool) $p->isMixZone(),
                'limit' => $p->limit,
                'is_stage' => (bool) $p->is_stage,
                'is_registration_open' => (bool) $p->isRegistrationOpen(),
                'closure_reason' => $p->getRegistrationClosureReason(),
                'closure_reason_ml' => $p->getRegistrationClosureReasonMl(),
            ];
        });

        $studentsData = $students->map(function ($s) {
            $indCount = $s->getIndividualParticipationCount();

            return [
                'id' => $s->id,
                'chest' => ltrim((string) ($s->chest_number ?: ($s->student_id ?: $s->id)), '#'),
                'name' => $s->name,
                'class' => $s->class_level ?? '',
                'group_id' => $s->group_id,
                'group_name' => $s->group?->name ?? 'Unassigned',
                'group_code' => $s->group?->code ?? '',
                'group_color' => $s->group?->color_hex ?? '#005c94',
                'zone_id' => $s->zone_id,
                'zone_name' => $s->zone?->name ?? ($s->category ?? 'A Zone'),
                'individual_count' => $indCount,
                'has_reached_individual_limit' => ($indCount >= Student::MAX_INDIVIDUAL_PROGRAMS),
            ];
        });

        $groupsData = $groups->map(function ($g) {
            return [
                'id' => $g->id,
                'name' => $g->name,
                'code' => $g->code,
                'color_hex' => $g->color_hex ?: '#005c94',
            ];
        });

        $zonesData = $zones->map(function ($z) {
            return [
                'id' => $z->id,
                'name' => $z->name,
            ];
        });

        return view('admin.registrations.create', compact(
            'programs',
            'groups',
            'students',
            'zones',
            'programsData',
            'studentsData',
            'groupsData',
            'zonesData',
            'entriesByGroupAndProgram',
            'selectedGroupId'
        ));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $isAjax = $request->expectsJson() || $request->ajax();

        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'student_id' => ['nullable', 'exists:students,id'],
            'leader_id' => ['nullable', 'exists:students,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:students,id'],
            'chest_number' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'in:pending,verified,confirmed,rejected,cancelled,withdrawn'],
            'notes' => ['nullable', 'string'],
        ]);

        $status = $validated['status'] ?? 'verified';
        $program = Program::with(['schedule', 'zone'])->findOrFail($validated['program_id']);

        $requestedChestNumber = $validated['chest_number'] ?? null;
        if (! empty($requestedChestNumber)) {
            $duplicateChest = ProgramEntry::where('program_id', $program->id)
                ->where('chest_number', $requestedChestNumber)
                ->when(! empty($validated['student_id']), function ($q) use ($validated) {
                    $q->where('student_id', '!=', $validated['student_id']);
                })
                ->exists();
            if ($duplicateChest) {
                $msg = "Registration failed: Chest number (#{$requestedChestNumber}) already exists for this program.";
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => ['chest_number' => [$msg]]], 422);
                }

                return back()->withInput()->withErrors(['chest_number' => $msg]);
            }
        }

        // A. Group Program Registration
        if ($program->isGroup()) {
            $studentIds = array_values(array_unique(array_filter((array) ($validated['student_ids'] ?? []))));
            $groupId = $validated['group_id'] ?? null;

            if (empty($groupId) && ! empty($studentIds)) {
                $firstSt = Student::find($studentIds[0]);
                $groupId = $firstSt?->group_id;
            }

            if (empty($groupId)) {
                $msg = 'Please select a participating Group.';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => ['group_id' => [$msg]]], 422);
                }

                return back()->withInput()->withErrors(['group_id' => $msg]);
            }

            $group = Group::findOrFail($groupId);
            $leaderId = $validated['leader_id'] ?? ($validated['student_id'] ?? ($studentIds[0] ?? null));

            if (! empty($studentIds)) {
                if (count($studentIds) > $program->limit) {
                    $msg = "This program allows at most {$program->limit} participants per group. ".count($studentIds).' selected.';
                    if ($isAjax) {
                        return response()->json(['success' => false, 'message' => $msg, 'errors' => ['student_ids' => [$msg]]], 422);
                    }

                    return back()->withInput()->withErrors(['student_ids' => $msg]);
                }

                $existingEntry = ProgramEntry::where('program_id', $program->id)
                    ->where('group_id', $group->id)
                    ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
                    ->first();

                $eligibility = $this->eligibilityService->validateGroupRegistration(
                    $group,
                    $program,
                    $studentIds,
                    $existingEntry?->id
                );

                if (! $eligibility['valid']) {
                    $msg = $eligibility['error'];
                    $field = $eligibility['field'] ?? 'group_id';
                    if ($isAjax) {
                        return response()->json(['success' => false, 'message' => $msg, 'errors' => [$field => [$msg]]], 422);
                    }

                    return back()->withInput()->withErrors([$field => $msg]);
                }

                try {
                    if ($existingEntry) {
                        $existingEntry->participants()->detach();
                        $leaderStudent = Student::find($leaderId) ?? Student::find($studentIds[0] ?? null);
                        $allParticipantIds = array_values(array_unique(array_filter(array_merge([$leaderStudent?->id], $studentIds))));

                        foreach ($allParticipantIds as $stId) {
                            $role = ($stId == $leaderStudent?->id) ? 'captain' : 'participant';
                            $existingEntry->participants()->attach($stId, ['role' => $role]);
                        }

                        $existingEntry->student_id = $leaderStudent?->id;
                        $existingEntry->status = $status;
                        if (! empty($requestedChestNumber)) {
                            $existingEntry->chest_number = $requestedChestNumber;
                        } elseif ($leaderStudent) {
                            $existingEntry->chest_number = $leaderStudent->student_id;
                        }
                        if (isset($validated['notes'])) {
                            $existingEntry->notes = $validated['notes'];
                        }
                        $existingEntry->save();
                        $entry = $existingEntry;
                    } else {
                        $entry = $this->eligibilityService->registerGroup($group, $program, $studentIds, [
                            'chest_number' => $requestedChestNumber,
                            'leader_id' => $leaderId,
                            'status' => $status,
                            'notes' => $validated['notes'] ?? null,
                        ]);
                    }

                    // GreenRoomCall
                    $order = $program->greenRoomCalls()->count() + 1;
                    $program->greenRoomCalls()->firstOrCreate(
                        ['entry_id' => $entry->id],
                        ['order_num' => $order, 'status' => 'waiting']
                    );
                } catch (\Throwable $e) {
                    $msg = 'Group registration failed: '.$e->getMessage();
                    if ($isAjax) {
                        return response()->json(['success' => false, 'message' => $msg, 'errors' => ['registration' => [$msg]]], 422);
                    }

                    return back()->withInput()->withErrors(['registration' => $msg]);
                }
            } else {
                // General group entry without student IDs
                $chestNum = $requestedChestNumber ?: ('G-'.$group->code.'-'.rand(100, 999));
                if (ProgramEntry::where('program_id', $program->id)->where('chest_number', $chestNum)->exists()) {
                    $msg = "Registration failed: Chest number (#{$chestNum}) already exists for this program.";
                    if ($isAjax) {
                        return response()->json(['success' => false, 'message' => $msg, 'errors' => ['chest_number' => [$msg]]], 422);
                    }

                    return back()->withInput()->withErrors(['chest_number' => $msg]);
                }

                $entry = ProgramEntry::create([
                    'program_id' => $program->id,
                    'group_id' => $group->id,
                    'chest_number' => $chestNum,
                    'status' => $status,
                    'notes' => $validated['notes'] ?? null,
                ]);
            }

            AuditLogger::log('create_entry', $entry, null, $entry->toArray());

            $freshEntry = ProgramEntry::with(['participants.zone', 'student.zone'])->find($entry->id);
            $freshEnrolledStudents = [];
            if ($freshEntry && $freshEntry->participants->isNotEmpty()) {
                $leaderStudent = $freshEntry->leaderStudent() ?? $freshEntry->student;
                foreach ($freshEntry->participants as $st) {
                    $freshEnrolledStudents[] = [
                        'id' => $st->id,
                        'chest' => ltrim((string) ($st->chest_number ?: ($st->student_id ?: $st->id)), '#'),
                        'name' => $st->name,
                        'class' => $st->class_level ?? '',
                        'zone_name' => $st->zone?->name ?? ($st->category ?? 'Mix Zone'),
                        'is_leader' => ($leaderStudent && $leaderStudent->id === $st->id),
                        'entry_id' => $freshEntry->id,
                    ];
                }
            }

            $count = count($freshEnrolledStudents);
            $msg = "Group entry created for {$group->name} ({$entry->chest_number}) with {$count} participants.";

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'program_id' => $program->id,
                    'program_name' => $program->name,
                    'is_group' => true,
                    'enrolled_students' => $freshEnrolledStudents,
                    'enrolled_count' => $count,
                    'registered_count' => $count,
                    'updated_students' => $this->getGroupStudentsQuotaData($group, $studentIds),
                ]);
            }

            return redirect()->route('admin.registrations.index')->with('success', $msg);
        }

        // B. Individual Program Registration
        $groupId = $validated['group_id'] ?? null;
        $isRosterSync = $request->has('student_ids') && is_array($request->input('student_ids'));

        if ($isRosterSync) {
            $studentIds = array_values(array_unique(array_filter((array) $validated['student_ids'])));
            if (empty($groupId) && ! empty($studentIds)) {
                $firstSt = Student::find($studentIds[0]);
                $groupId = $firstSt?->group_id;
            }

            if (empty($groupId)) {
                $msg = 'Please select a participating Group.';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => ['group_id' => [$msg]]], 422);
                }

                return back()->withInput()->withErrors(['group_id' => $msg]);
            }

            $group = Group::findOrFail($groupId);

            // Fetch existing active entries for this program and group
            $existingEntries = ProgramEntry::where('program_id', $program->id)
                ->where('group_id', $group->id)
                ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
                ->get();
            $existingStudentIds = $existingEntries->pluck('student_id')->toArray();

            // Limit check
            if (count($studentIds) > $program->limit) {
                $msg = "This program allows at most {$program->limit} participant(s) per group. ".count($studentIds).' selected.';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => ['student_ids' => [$msg]]], 422);
                }

                return back()->withInput()->withErrors(['student_ids' => $msg]);
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
                        return response()->json(['success' => false, 'message' => $msg, 'errors' => ['student_ids' => [$msg]]], 422);
                    }

                    return back()->withInput()->withErrors(['student_ids' => $msg]);
                }

                $eligibility = $this->eligibilityService->validateIndividualRegistration($student, $program, $excludedEntryIds);
                if (! $eligibility['valid']) {
                    $errorField = $eligibility['field'] ?? 'student_id';
                    $errorMsg = $eligibility['error'];
                    if ($isAjax) {
                        return response()->json(['success' => false, 'message' => $errorMsg, 'errors' => [$errorField => [$errorMsg]]], 422);
                    }

                    return back()->withInput()->withErrors([$errorField => $errorMsg]);
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

                // 2. Add new students
                foreach ($newStudentsToRegister as $student) {
                    $conflictFlag = false;
                    $entryNotes = $validated['notes'] ?? null;

                    if ($program->schedule) {
                        $conflicts = $this->conflictService->checkStudentConflict(
                            $student->id,
                            $program->schedule->start_time,
                            $program->schedule->end_time,
                            $program->id
                        );

                        if ($conflicts->isNotEmpty()) {
                            $conflictFlag = true;
                            $entryNotes = ($entryNotes ? $entryNotes.' | ' : '').$conflicts->first()['conflict_reason'];
                            $conflictsFound[] = $student->name;
                        }
                    }

                    $entry = $this->eligibilityService->registerIndividual($student, $program, [
                        'chest_number' => $student->student_id,
                        'status' => $status,
                        'conflict_flag' => $conflictFlag,
                        'notes' => $entryNotes,
                    ]);

                    // GreenRoomCall
                    $order = $program->greenRoomCalls()->count() + 1;
                    $program->greenRoomCalls()->firstOrCreate(
                        ['entry_id' => $entry->id],
                        ['order_num' => $order, 'status' => 'waiting']
                    );

                    AuditLogger::log('create_entry', $entry, null, $entry->toArray());
                }

                // 3. Update existing retained entries status if requested
                ProgramEntry::where('program_id', $program->id)
                    ->where('group_id', $group->id)
                    ->update(['status' => $status]);

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $msg = 'Registration failed: '.$e->getMessage();
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => ['registration' => [$msg]]], 422);
                }

                return back()->withInput()->withErrors(['registration' => $msg]);
            }

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
                        'chest' => ltrim((string) ($st->chest_number ?: ($st->student_id ?: $st->id)), '#'),
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

            return redirect()->route('admin.registrations.index')->with('success', $msg);
        }

        // Single student fallback
        if (! empty($validated['student_id'])) {
            $student = Student::with(['group', 'zone'])->findOrFail($validated['student_id']);
            $chestNum = $requestedChestNumber ?: $student->student_id;
            $groupId = $validated['group_id'] ?? $student->group_id;

            $eligibility = $this->eligibilityService->validateIndividualRegistration($student, $program);
            if (! $eligibility['valid']) {
                $msg = $eligibility['error'];
                $field = $eligibility['field'] ?? 'student_id';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => [$field => [$msg]]], 422);
                }

                return back()->withInput()->withErrors([$field => $msg]);
            }

            if (ProgramEntry::where('program_id', $program->id)->where('chest_number', $chestNum)->exists()) {
                $msg = "Registration failed: Chest number (#{$chestNum}) already exists for this program.";
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => ['chest_number' => [$msg]]], 422);
                }

                return back()->withInput()->withErrors(['chest_number' => $msg]);
            }

            $conflictFlag = false;
            $entryNotes = $validated['notes'] ?? null;
            if ($program->schedule) {
                $conflicts = $this->conflictService->checkStudentConflict(
                    $student->id,
                    $program->schedule->start_time,
                    $program->schedule->end_time,
                    $program->id
                );

                if ($conflicts->isNotEmpty()) {
                    $conflictFlag = true;
                    $entryNotes = ($entryNotes ? $entryNotes.' | ' : '').$conflicts->first()['conflict_reason'];
                }
            }

            try {
                $entry = DB::transaction(function () use ($program, $student, $groupId, $chestNum, $status, $conflictFlag, $entryNotes) {
                    $entry = ProgramEntry::create([
                        'program_id' => $program->id,
                        'student_id' => $student->id,
                        'group_id' => $groupId,
                        'chest_number' => $chestNum,
                        'status' => $status,
                        'conflict_flag' => $conflictFlag,
                        'notes' => $entryNotes,
                    ]);

                    $order = $program->greenRoomCalls()->count() + 1;
                    $program->greenRoomCalls()->firstOrCreate(
                        ['entry_id' => $entry->id],
                        ['order_num' => $order, 'status' => 'waiting']
                    );

                    return $entry;
                });
            } catch (\Throwable $e) {
                $msg = "Registration failed: Chest number (#{$chestNum}) already exists for this program.";
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => ['chest_number' => [$msg]]], 422);
                }

                return back()->withInput()->withErrors(['chest_number' => $msg]);
            }

            AuditLogger::log('create_entry', $entry, null, $entry->toArray());
            $msg = "Entry created for Chest #{$entry->chest_number}.";

            if ($isAjax) {
                $group = Group::find($groupId);
                $enrolledStudents = [[
                    'id' => $student->id,
                    'chest' => ltrim((string) ($student->chest_number ?: ($student->student_id ?: $student->id)), '#'),
                    'name' => $student->name,
                    'class' => $student->class_level ?? '',
                    'zone_name' => $student->zone?->name ?? ($student->category ?? 'Mix Zone'),
                    'is_leader' => false,
                    'entry_id' => $entry->id,
                ]];

                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'program_id' => $program->id,
                    'program_name' => $program->name,
                    'is_group' => false,
                    'enrolled_students' => $enrolledStudents,
                    'enrolled_count' => 1,
                    'registered_count' => 1,
                    'updated_students' => $group ? $this->getGroupStudentsQuotaData($group, [$student->id]) : [],
                ]);
            }

            return redirect()->route('admin.registrations.index')->with('success', $msg);
        }

        $msg = 'Please select a participant student or group.';
        if ($isAjax) {
            return response()->json(['success' => false, 'message' => $msg, 'errors' => ['student_id' => [$msg]]], 422);
        }

        return back()->withInput()->withErrors(['student_id' => $msg]);
    }

    public function destroyByProgram(Request $request, Program $program): JsonResponse|RedirectResponse
    {
        $isAjax = $request->expectsJson() || $request->ajax();
        $groupId = $request->input('group_id') ?: $request->query('group_id');

        if (! $groupId) {
            $msg = 'Please specify a group to remove program registrations.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->withErrors(['group_id' => $msg]);
        }

        $group = Group::findOrFail($groupId);

        $entries = ProgramEntry::where('program_id', $program->id)
            ->where('group_id', $group->id)
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->get();

        $affectedStudentIds = [];
        foreach ($entries as $entry) {
            if ($entry->student_id) {
                $affectedStudentIds[] = $entry->student_id;
            }
            foreach ($entry->participants as $p) {
                $affectedStudentIds[] = $p->id;
            }
            $entry->participants()->detach();
            $entry->delete();
        }

        $affectedStudentIds = array_values(array_unique(array_filter($affectedStudentIds)));
        $msg = "All registrations for '{$program->name}' ({$group->name}) removed successfully.";

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'program_id' => $program->id,
                'program_name' => $program->name,
                'updated_students' => $this->getGroupStudentsQuotaData($group, $affectedStudentIds),
            ]);
        }

        return redirect()->route('admin.registrations.index')->with('success', $msg);
    }

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
