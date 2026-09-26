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
use App\Services\EligibilityService;
use App\Services\ScheduleConflictService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LeaderController extends Controller
{
    public function __construct(
        protected ScheduleConflictService $conflictService,
        protected EligibilityService $eligibilityService
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

        return view('leader.dashboard', compact(
            'group',
            'students',
            'entries',
            'upcomingPrograms',
            'results',
            'announcements',
            'isRegistrationOpen',
            'stats',
            'leaderboard'
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

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy('name')->paginate(15)->withQueryString();
        $zones = Zone::orderBy('display_order')->get();

        return view('leader.programs', compact('group', 'programs', 'zones', 'search', 'selectedZone', 'selectedZoneId'));
    }

    public function programWise(Request $request): View
    {
        $group = $this->getGroup();
        $zones = Zone::orderBy('display_order')->get();
        $selectedZoneId = $request->query('zone_id');
        $selectedZone = $request->query('zone', $request->query('category'));
        $selectedProgramId = $request->query('program');

        $programsQuery = Program::whereHas('entries', fn ($q) => $q->where('group_id', $group->id));
        if ($selectedZoneId) {
            $programsQuery->where('zone_id', $selectedZoneId);
        } elseif ($selectedZone) {
            $programsQuery->where(function ($q) use ($selectedZone) {
                $q->where('eligibility', $selectedZone)
                    ->orWhereHas('zone', fn ($zq) => $zq->where('name', $selectedZone));
            });
        }
        $programs = $programsQuery->orderBy('name')->get();

        $selectedProgram = null;
        if ($selectedProgramId) {
            $selectedProgram = Program::with([
                'category',
                'stage',
                'zone',
                'entries' => fn ($q) => $q->where('group_id', $group->id)->with(['student', 'participants']),
            ])->find($selectedProgramId);
        }

        return view('leader.program-wise', compact(
            'group',
            'zones',
            'programs',
            'selectedZone',
            'selectedZoneId',
            'selectedProgramId',
            'selectedProgram'
        ));
    }

    public function studentWise(Request $request): View
    {
        $group = $this->getGroup();
        $selectedStudentId = $request->query('student');
        $search = $request->query('search');

        $studentsQuery = Student::where('group_id', $group->id)->with('zone');
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
            ])->where('group_id', $group->id)->find($selectedStudentId);
        }

        return view('leader.student-wise', compact(
            'group',
            'students',
            'selectedStudentId',
            'selectedStudent',
            'search'
        ));
    }

    public function students(): View
    {
        $group = $this->getGroup();
        $students = $group->students()->with(['zone', 'entries.program.category'])->paginate(15);

        return view('leader.students', compact('group', 'students'));
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

        $allGroupEntries = ProgramEntry::where('group_id', $group->id)->get();
        $registeredProgramIds = $allGroupEntries->pluck('program_id')->unique()->toArray();

        $eligiblePrograms = Program::with('zone')->where('status', 'upcoming')->orderBy('name')->get();

        $totalProgramsCount = $eligiblePrograms->count();
        $registeredProgramsCount = count($registeredProgramIds);
        $unregisteredProgramsCount = max(0, $totalProgramsCount - $registeredProgramsCount);

        // List of unregistered programs for the Unregistered tab
        $unregisteredPrograms = $eligiblePrograms->whereNotIn('id', $registeredProgramIds);

        $students = $group->students()->with('zone')->orderBy('name')->get();
        $zones = Zone::orderBy('display_order')->get();
        $isRegistrationOpen = $this->isRegistrationOpen();

        return view('leader.registrations', compact(
            'group',
            'entries',
            'eligiblePrograms',
            'unregisteredPrograms',
            'registeredProgramIds',
            'totalProgramsCount',
            'registeredProgramsCount',
            'unregisteredProgramsCount',
            'tab',
            'students',
            'zones',
            'isRegistrationOpen'
        ));
    }

    public function storeRegistration(Request $request): RedirectResponse
    {
        if (! $this->isRegistrationOpen()) {
            return back()->withInput()->withErrors([
                'registration' => 'Registration window is currently closed. New registrations are not permitted.',
            ]);
        }

        $program = Program::with(['schedule', 'zone'])->findOrFail($request->input('program_id'));

        // If program is a group program or student_ids array is submitted, route to storeGroupRegistration
        if ($program->isGroup() || $request->has('student_ids')) {
            return $this->storeGroupRegistration($request);
        }

        $group = $this->getGroup();

        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'student_id' => ['nullable', 'exists:students,id'],
            'chest_number' => ['nullable', 'string', 'max:20'],
        ]);

        if (empty($validated['student_id'])) {
            return back()->withInput()->withErrors([
                'student_id' => 'Please select a student for this competition.',
            ]);
        }

        // Ensure student belongs to this Leader's Group
        $student = Student::with(['group', 'zone'])->where('group_id', $group->id)->findOrFail($validated['student_id']);

        // Check 10-point Eligibility Engine
        $eligibility = $this->eligibilityService->validateIndividualRegistration($student, $program);
        if (! $eligibility['valid']) {
            return back()->withInput()->withErrors([
                $eligibility['field'] ?? 'student_id' => $eligibility['error'],
            ]);
        }

        $conflictFlag = false;
        $notes = null;

        // Conflict check
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
            }
        }

        try {
            $chestNum = ($validated['chest_number'] ?? null) ?: $student->student_id;
            $entry = $this->eligibilityService->registerIndividual($student, $program, [
                'chest_number' => $chestNum,
                'status' => 'pending', // Requires admin verification
                'conflict_flag' => $conflictFlag,
                'notes' => $notes,
            ]);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'registration' => 'Registration failed: '.$e->getMessage(),
            ]);
        }

        $msg = "Entry submitted for Chest #{$entry->chest_number} (Pending Admin Verification).";
        if ($conflictFlag) {
            $msg .= ' NOTICE: Schedule conflict detected and notified to festival desk.';
        }

        return back()->with('success', $msg);
    }

    public function storeGroupRegistration(Request $request): RedirectResponse
    {
        if (! $this->isRegistrationOpen()) {
            return back()->withInput()->withErrors([
                'registration' => 'Registration window is currently closed. New registrations are not permitted.',
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
        $studentIds = $validated['student_ids'];
        $leaderId = $validated['leader_id'] ?? ($studentIds[0] ?? null);

        // Validate via Eligibility Engine
        $eligibility = $this->eligibilityService->validateGroupRegistration($group, $program, $studentIds);
        if (! $eligibility['valid']) {
            return back()->withInput()->withErrors([
                $eligibility['field'] ?? 'student_ids' => $eligibility['error'],
            ]);
        }

        try {
            $entry = $this->eligibilityService->registerGroup($group, $program, $studentIds, [
                'status' => 'pending',
                'leader_id' => $leaderId,
                'chest_number' => $validated['chest_number'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'registration' => 'Group registration failed: '.$e->getMessage(),
            ]);
        }

        return back()->with('success', "Group entry submitted for {$program->name} with ".count($studentIds).' participants (Pending Admin Verification).');
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
            $entry->status = 'pending'; // Require re-verification
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
            $entry->status = 'pending';
            $entry->save();

            return redirect()->route('leader.registrations')->with('success', "Entry for '{$program->name}' updated with participant {$student->name}.");
        }
    }

    public function destroyRegistration(ProgramEntry $entry): RedirectResponse
    {
        $group = $this->getGroup();
        if ($entry->group_id !== $group->id) {
            abort(403, 'Unauthorized access to group entry.');
        }

        if (! $this->isRegistrationOpen()) {
            return back()->withErrors([
                'registration' => 'Registration window is currently closed. Cancellations are not permitted.',
            ]);
        }

        $programName = $entry->program?->name ?? 'Program';
        $entry->participants()->detach();
        $entry->delete();

        return redirect()->route('leader.registrations')->with('success', "Registration for '{$programName}' has been removed successfully.");
    }
}
