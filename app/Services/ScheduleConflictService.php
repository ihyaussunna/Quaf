<?php

namespace App\Services;

use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Schedule;
use App\Models\Stage;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ScheduleConflictService
{
    /**
     * Check if a student is registered for another program scheduled at overlapping time.
     *
     * @return Collection<int, array{program: Program, schedule: Schedule, conflict_reason: string}>
     */
    public function checkStudentConflict(int $studentId, Carbon $startTime, Carbon $endTime, ?int $excludeProgramId = null): Collection
    {
        $conflicts = collect();

        $entries = ProgramEntry::where('student_id', $studentId)
            ->when($excludeProgramId, fn ($q) => $q->where('program_id', '!=', $excludeProgramId))
            ->where('status', '!=', 'rejected')
            ->with(['program.schedule.stage'])
            ->get();

        foreach ($entries as $entry) {
            $otherSchedule = $entry->program?->schedule;
            if (! $otherSchedule) {
                continue;
            }

            if ($this->isOverlapping($startTime, $endTime, $otherSchedule->start_time, $otherSchedule->end_time)) {
                $conflicts->push([
                    'program' => $entry->program,
                    'schedule' => $otherSchedule,
                    'conflict_reason' => "Student #{$entry->chest_number} is in '{$entry->program->name}' on Stage '{$otherSchedule->stage->name}' during this time.",
                ]);
            }
        }

        return $conflicts;
    }

    /**
     * Check if a stage is already occupied at overlapping time.
     */
    public function checkStageConflict(int $stageId, Carbon $startTime, Carbon $endTime, ?int $excludeScheduleId = null): ?Schedule
    {
        return Schedule::where('stage_id', $stageId)
            ->when($excludeScheduleId, fn ($q) => $q->where('id', '!=', $excludeScheduleId))
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            })
            ->with(['program', 'stage'])
            ->first();
    }

    /**
     * Check if a judge is already assigned to an overlapping program.
     */
    public function checkJudgeConflict(int $judgeId, Carbon $startTime, Carbon $endTime, ?int $excludeProgramId = null): ?Program
    {
        $programs = Program::whereHas('judges', fn ($q) => $q->where('judges.id', $judgeId))
            ->when($excludeProgramId, fn ($q) => $q->where('id', '!=', $excludeProgramId))
            ->with('schedule')
            ->get();

        foreach ($programs as $program) {
            if ($program->schedule && $this->isOverlapping($startTime, $endTime, $program->schedule->start_time, $program->schedule->end_time)) {
                return $program;
            }
        }

        return null;
    }

    /**
     * Comprehensive conflict checking for a proposed program schedule slot.
     *
     * @return array{has_conflicts: bool, stage_conflict: ?array, student_conflicts: array, count: int}
     */
    public function checkProgramSlotConflicts(int $programId, Carbon $startTime, Carbon $endTime, ?int $stageId = null, ?int $excludeScheduleId = null): array
    {
        $stageConflictData = null;
        $studentConflicts = [];

        // 1. Stage Conflict Check
        if ($stageId) {
            $stageConflict = $this->checkStageConflict($stageId, $startTime, $endTime, $excludeScheduleId);
            if ($stageConflict && $stageConflict->program) {
                $stageConflictData = [
                    'stage_id' => $stageId,
                    'stage_name' => $stageConflict->stage?->name ?? 'Stage',
                    'venue' => $stageConflict->stage?->venue ?? '',
                    'conflicting_program' => $stageConflict->program->name,
                    'conflicting_code' => $stageConflict->program->code,
                    'start_time' => $stageConflict->start_time->format('h:i A'),
                    'end_time' => $stageConflict->end_time->format('h:i A'),
                    'message' => "Stage '{$stageConflict->stage?->name}' ({$stageConflict->stage?->venue}) is already booked for '{$stageConflict->program->name}' from {$stageConflict->start_time->format('h:i A')} to {$stageConflict->end_time->format('h:i A')}.",
                ];
            }
        }

        // 2. Student Conflict Check for all enrolled students in this program
        // Get individual student IDs
        $individualStudentIds = ProgramEntry::where('program_id', $programId)
            ->where('status', '!=', 'rejected')
            ->whereNotNull('student_id')
            ->pluck('student_id')
            ->toArray();

        // Get group participants student IDs
        $groupParticipantStudentIds = DB::table('program_entry_participants')
            ->join('program_entries', 'program_entries.id', '=', 'program_entry_participants.entry_id')
            ->where('program_entries.program_id', $programId)
            ->where('program_entries.status', '!=', 'rejected')
            ->pluck('program_entry_participants.student_id')
            ->toArray();

        $allStudentIds = array_unique(array_merge($individualStudentIds, $groupParticipantStudentIds));

        if (! empty($allStudentIds)) {
            // Find overlapping schedules
            $overlappingSchedules = Schedule::where('program_id', '!=', $programId)
                ->when($excludeScheduleId, fn ($q) => $q->where('id', '!=', $excludeScheduleId))
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                })
                ->with(['program.stage', 'stage'])
                ->get();

            foreach ($overlappingSchedules as $otherSchedule) {
                $otherProgId = $otherSchedule->program_id;
                if (! $otherProgId) {
                    continue;
                }

                // Check which of our students are in the other program
                $otherIndividual = ProgramEntry::where('program_id', $otherProgId)
                    ->where('status', '!=', 'rejected')
                    ->whereIn('student_id', $allStudentIds)
                    ->with(['student.group'])
                    ->get();

                foreach ($otherIndividual as $oe) {
                    if ($oe->student) {
                        $studentConflicts[] = [
                            'student_id' => $oe->student->id,
                            'student_name' => $oe->student->name,
                            'chest_number' => $oe->student->student_id ?? $oe->chest_number,
                            'group_name' => $oe->student->group?->name ?? 'Team',
                            'group_color' => $oe->student->group?->color ?? '#be1e2d',
                            'conflicting_program' => $otherSchedule->program?->name ?? 'Another Event',
                            'conflicting_code' => $otherSchedule->program?->code ?? '',
                            'conflicting_stage' => $otherSchedule->stage?->name ?? 'Stage',
                            'conflicting_venue' => $otherSchedule->stage?->venue ?? '',
                            'time_range' => $otherSchedule->start_time->format('h:i A').' - '.$otherSchedule->end_time->format('h:i A'),
                            'message' => "Participant {$oe->student->name} (Chest #{$oe->chest_number}, {$oe->student->group?->name}) is also in '{$otherSchedule->program?->name}' on {$otherSchedule->stage?->name} ({$otherSchedule->start_time->format('h:i A')}).",
                        ];
                    }
                }
            }
        }

        $hasConflicts = ($stageConflictData !== null) || (! empty($studentConflicts));

        return [
            'has_conflicts' => $hasConflicts,
            'stage_conflict' => $stageConflictData,
            'student_conflicts' => $studentConflicts,
            'count' => count($studentConflicts) + ($stageConflictData ? 1 : 0),
        ];
    }

    /**
     * Detect all schedule clashes across the entire festival or for a specific date.
     *
     * @return array{
     *     total_conflicts: int,
     *     stage_conflicts: array,
     *     student_conflicts: array,
     *     program_conflict_counts: array<int, int>
     * }
     */
    public function detectAllScheduleConflicts(?string $date = null): array
    {
        $query = Schedule::with(['program.stage', 'stage', 'program.entries.student.group'])
            ->orderBy('start_time');

        if ($date) {
            $query->whereDate('start_time', $date);
        }

        $schedules = $query->get();

        $stageConflicts = [];
        $studentConflicts = [];
        $programConflictCounts = [];

        // Check pairwise overlaps
        $count = $schedules->count();
        for ($i = 0; $i < $count; $i++) {
            $schA = $schedules[$i];
            for ($j = $i + 1; $j < $count; $j++) {
                $schB = $schedules[$j];

                if (! $this->isOverlapping($schA->start_time, $schA->end_time, $schB->start_time, $schB->end_time)) {
                    continue;
                }

                // 1. Stage Clash
                if ($schA->stage_id === $schB->stage_id) {
                    $conflictKey = "stage_{$schA->stage_id}_{$schA->id}_{$schB->id}";
                    $stageConflicts[$conflictKey] = [
                        'stage_id' => $schA->stage_id,
                        'stage_name' => $schA->stage?->name,
                        'venue' => $schA->stage?->venue,
                        'program_a' => [
                            'id' => $schA->program_id,
                            'name' => $schA->program?->name,
                            'code' => $schA->program?->code,
                            'start_time' => $schA->start_time->format('H:i'),
                            'end_time' => $schA->end_time->format('H:i'),
                            'time' => $schA->start_time->format('h:i A').' - '.$schA->end_time->format('h:i A'),
                        ],
                        'program_b' => [
                            'id' => $schB->program_id,
                            'name' => $schB->program?->name,
                            'code' => $schB->program?->code,
                            'start_time' => $schB->start_time->format('H:i'),
                            'end_time' => $schB->end_time->format('H:i'),
                            'time' => $schB->start_time->format('h:i A').' - '.$schB->end_time->format('h:i A'),
                        ],
                        'message' => "Double booking on {$schA->stage?->name} ({$schA->stage?->venue}): '{$schA->program?->name}' and '{$schB->program?->name}' overlap.",
                    ];

                    $programConflictCounts[$schA->program_id] = ($programConflictCounts[$schA->program_id] ?? 0) + 1;
                    $programConflictCounts[$schB->program_id] = ($programConflictCounts[$schB->program_id] ?? 0) + 1;
                }

                // 2. Student Clashes between Program A and Program B
                $progAEntries = $schA->program?->entries ?? collect();
                $progBEntries = $schB->program?->entries ?? collect();

                $studentsA = $progAEntries->pluck('student_id')->filter()->unique()->toArray();
                $studentsB = $progBEntries->pluck('student_id')->filter()->unique()->toArray();

                $commonStudents = array_intersect($studentsA, $studentsB);

                foreach ($commonStudents as $studentId) {
                    $student = Student::with('group')->find($studentId);
                    if (! $student) {
                        continue;
                    }

                    $entryA = $progAEntries->firstWhere('student_id', $studentId);
                    $entryB = $progBEntries->firstWhere('student_id', $studentId);

                    $studentConflicts[] = [
                        'student_id' => $student->id,
                        'student_name' => $student->name,
                        'chest_number' => $entryA?->chest_number ?? $student->student_id,
                        'group_name' => $student->group?->name ?? 'Team',
                        'group_color' => $student->group?->color ?? '#be1e2d',
                        'program_a' => [
                            'id' => $schA->program_id,
                            'name' => $schA->program?->name,
                            'code' => $schA->program?->code,
                            'stage' => $schA->stage?->name,
                            'venue' => $schA->stage?->venue,
                            'start_time' => $schA->start_time->format('H:i'),
                            'end_time' => $schA->end_time->format('H:i'),
                            'time' => $schA->start_time->format('h:i A').' - '.$schA->end_time->format('h:i A'),
                        ],
                        'program_b' => [
                            'id' => $schB->program_id,
                            'name' => $schB->program?->name,
                            'code' => $schB->program?->code,
                            'stage' => $schB->stage?->name,
                            'venue' => $schB->stage?->venue,
                            'start_time' => $schB->start_time->format('H:i'),
                            'end_time' => $schB->end_time->format('H:i'),
                            'time' => $schB->start_time->format('h:i A').' - '.$schB->end_time->format('h:i A'),
                        ],
                        'message' => "Student {$student->name} (#{$entryA?->chest_number}) is in both '{$schA->program?->name}' ({$schA->stage?->venue}) and '{$schB->program?->name}' ({$schB->stage?->venue}) at overlapping times.",
                    ];

                    $programConflictCounts[$schA->program_id] = ($programConflictCounts[$schA->program_id] ?? 0) + 1;
                    $programConflictCounts[$schB->program_id] = ($programConflictCounts[$schB->program_id] ?? 0) + 1;
                }
            }
        }

        return [
            'total_conflicts' => count($stageConflicts) + count($studentConflicts),
            'stage_conflicts' => array_values($stageConflicts),
            'student_conflicts' => $studentConflicts,
            'program_conflict_counts' => $programConflictCounts,
        ];
    }

    /**
     * Test whether two time intervals overlap: [startA, endA) and [startB, endB).
     */
    public function isOverlapping(Carbon $startA, Carbon $endTimeA, Carbon $startB, Carbon $endTimeB): bool
    {
        return $startA->lt($endTimeB) && $endTimeA->gt($startB);
    }

    /**
     * Smartly resolve all schedule conflicts (both stage double-bookings and multi-competition student overlaps)
     * using iterative constraint satisfaction, stage-internal swapping, and gap placement.
     *
     * @return array{
     *     resolved_count: int,
     *     remaining_conflicts: int,
     *     actions: array<string>
     * }
     */
    public function smartResolveAllConflicts(?string $date = null): array
    {
        $dates = [];
        if ($date && $date !== 'all') {
            $dates = [$date];
        } else {
            $dates = Schedule::selectRaw('date(start_time) as dt')
                ->distinct()
                ->pluck('dt')
                ->filter()
                ->toArray();
        }

        $totalResolved = 0;
        $allActions = [];

        foreach ($dates as $targetDate) {
            $result = $this->resolveConflictsForDate($targetDate);
            $totalResolved += $result['resolved_count'];
            $allActions = array_merge($allActions, $result['actions']);
        }

        $finalCheck = $this->detectAllScheduleConflicts($date && $date !== 'all' ? $date : null);

        return [
            'resolved_count' => $totalResolved,
            'remaining_conflicts' => $finalCheck['total_conflicts'],
            'actions' => $allActions,
        ];
    }

    /**
     * Resolve stage clashes and student clashes for a specific festival date.
     *
     * @return array{resolved_count: int, actions: array<string>}
     */
    protected function resolveConflictsForDate(string $targetDate): array
    {
        $resolvedCount = 0;
        $actions = [];

        // Phase 1: Harmonize Stage Clashes (consecutive overlaps on the exact same stage)
        $schedules = Schedule::whereDate('start_time', $targetDate)
            ->with(['program', 'stage'])
            ->orderBy('stage_id')
            ->orderBy('start_time')
            ->get();

        $byStage = $schedules->groupBy('stage_id');

        foreach ($byStage as $stageId => $group) {
            $sorted = $group->sortBy('start_time')->values();
            for ($i = 0; $i < $sorted->count() - 1; $i++) {
                $curr = $sorted[$i];
                $next = $sorted[$i + 1];

                if ($curr->end_time > $next->start_time) {
                    $curr->end_time = $next->start_time;
                    $diffMin = $curr->start_time->diffInMinutes($curr->end_time);
                    $curr->program?->update(['duration_minutes' => max(15, (int) $diffMin)]);
                    $curr->conflict_notes = null;
                    $curr->save();
                    $resolvedCount++;
                    $actions[] = "Harmonized duration for '{$curr->program?->name}' on Stage {$curr->stage?->name}";
                }
            }
        }

        // Phase 2: Iterative Student Overlap Resolver
        $maxRounds = 30;
        for ($round = 0; $round < $maxRounds; $round++) {
            $conflicts = $this->detectAllScheduleConflicts($targetDate);
            if (empty($conflicts['student_conflicts'])) {
                break; // Zero student conflicts remaining
            }

            $studentConflict = $conflicts['student_conflicts'][0];
            $progAId = $studentConflict['program_a']['id'];
            $progBId = $studentConflict['program_b']['id'];

            $schA = Schedule::where('program_id', $progAId)->with(['program.zone', 'stage'])->first();
            $schB = Schedule::where('program_id', $progBId)->with(['program.zone', 'stage'])->first();

            if (! $schA || ! $schB) {
                continue;
            }

            // Decide which schedule to move: prioritize the one on S3 or Mix Zone or whichever is schB
            $targetSch = $schB;
            $otherSch = $schA;
            if ($schA->stage?->location === 'S3' || $schA->program?->zone?->name === 'Mix Zone') {
                $targetSch = $schA;
                $otherSch = $schB;
            }

            $duration = $targetSch->program?->duration_minutes ?: 30;

            // Strategy A: Intra-stage Pairwise Swap with other programs on the same stage
            $sameStageSchedules = Schedule::whereDate('start_time', $targetDate)
                ->where('stage_id', $targetSch->stage_id)
                ->where('id', '!=', $targetSch->id)
                ->with('program')
                ->get();

            $swapFound = false;
            foreach ($sameStageSchedules as $otherOnStage) {
                $origStartT = $targetSch->start_time->copy();
                $origEndT = $targetSch->end_time->copy();
                $origStartO = $otherOnStage->start_time->copy();
                $origEndO = $otherOnStage->end_time->copy();

                $durT = $targetSch->program?->duration_minutes ?: 30;
                $durO = $otherOnStage->program?->duration_minutes ?: 30;

                // Tentative swap
                $targetSch->update([
                    'start_time' => $origStartO,
                    'end_time' => $origStartO->copy()->addMinutes($durT),
                ]);
                $otherOnStage->update([
                    'start_time' => $origStartT,
                    'end_time' => $origStartT->copy()->addMinutes($durO),
                ]);

                $newConflicts = $this->detectAllScheduleConflicts($targetDate);
                if ($newConflicts['total_conflicts'] < $conflicts['total_conflicts']) {
                    $swapFound = true;
                    $resolvedCount++;
                    $actions[] = "Swapped times between '{$targetSch->program?->name}' and '{$otherOnStage->program?->name}' on {$targetSch->stage?->location}";
                    $targetSch->program?->update(['scheduled_time' => $targetSch->start_time]);
                    $otherOnStage->program?->update(['scheduled_time' => $otherOnStage->start_time]);
                    break;
                } else {
                    // Revert tentative swap
                    $targetSch->update(['start_time' => $origStartT, 'end_time' => $origEndT]);
                    $otherOnStage->update(['start_time' => $origStartO, 'end_time' => $origEndO]);
                }
            }

            if ($swapFound) {
                continue;
            }

            // Strategy B: Intra-stage Free Slot Placement
            $occupied = Schedule::whereDate('start_time', $targetDate)
                ->where('stage_id', $targetSch->stage_id)
                ->where('id', '!=', $targetSch->id)
                ->orderBy('start_time')
                ->get();

            $candidateTimes = [
                Carbon::parse("{$targetDate} 17:40:00"),
                Carbon::parse("{$targetDate} 18:15:00"),
                Carbon::parse("{$targetDate} 18:45:00"),
                Carbon::parse("{$targetDate} 20:30:00"),
                Carbon::parse("{$targetDate} 21:45:00"),
                Carbon::parse("{$targetDate} 22:15:00"),
                Carbon::parse("{$targetDate} 22:45:00"),
                Carbon::parse("{$targetDate} 16:00:00"),
            ];

            // 15-minute increments across active hours (16:00 to 23:00)
            $t = Carbon::parse("{$targetDate} 16:00:00");
            $tEnd = Carbon::parse("{$targetDate} 23:00:00");
            while ($t->lte($tEnd)) {
                $candidateTimes[] = $t->copy();
                $t->addMinutes(15);
            }

            $slotFound = false;
            foreach ($candidateTimes as $candStart) {
                $candEnd = $candStart->copy()->addMinutes($duration);

                // Check stage overlap
                $stageBlocked = false;
                foreach ($occupied as $occ) {
                    if ($this->isOverlapping($candStart, $candEnd, $occ->start_time, $occ->end_time)) {
                        $stageBlocked = true;
                        break;
                    }
                }
                if ($stageBlocked) {
                    continue;
                }

                // Check student conflicts
                $slotCheck = $this->checkProgramSlotConflicts(
                    $targetSch->program_id,
                    $candStart,
                    $candEnd,
                    $targetSch->stage_id,
                    $targetSch->id
                );

                if (! $slotCheck['has_conflicts']) {
                    $targetSch->update([
                        'start_time' => $candStart,
                        'end_time' => $candEnd,
                        'conflict_notes' => null,
                    ]);
                    $targetSch->program?->update([
                        'scheduled_time' => $candStart,
                    ]);
                    $slotFound = true;
                    $resolvedCount++;
                    $actions[] = "Shifted '{$targetSch->program?->name}' to {$candStart->format('h:i A')} - {$candEnd->format('h:i A')} on {$targetSch->stage?->location} (0 conflicts)";
                    break;
                }
            }

            if (! $slotFound) {
                // Strategy C: Cross-Stage Reassignment to Compatible Offstage Venue
                $offstageStages = Stage::whereIn('location', ['NF3', 'ID3', 'U2', 'S3'])
                    ->where('id', '!=', $targetSch->stage_id)
                    ->get();

                foreach ($offstageStages as $altStage) {
                    foreach ($candidateTimes as $candStart) {
                        $candEnd = $candStart->copy()->addMinutes($duration);
                        $slotCheck = $this->checkProgramSlotConflicts(
                            $targetSch->program_id,
                            $candStart,
                            $candEnd,
                            $altStage->id,
                            $targetSch->id
                        );

                        if (! $slotCheck['has_conflicts']) {
                            $targetSch->update([
                                'stage_id' => $altStage->id,
                                'start_time' => $candStart,
                                'end_time' => $candEnd,
                                'conflict_notes' => null,
                            ]);
                            $targetSch->program?->update([
                                'stage_id' => $altStage->id,
                                'scheduled_time' => $candStart,
                            ]);
                            $slotFound = true;
                            $resolvedCount++;
                            $actions[] = "Reassigned '{$targetSch->program?->name}' to {$altStage->location} at {$candStart->format('h:i A')} (0 conflicts)";
                            break 2;
                        }
                    }
                }
            }
        }

        $finalConflicts = $this->detectAllScheduleConflicts($targetDate);
        if ($finalConflicts['total_conflicts'] === 0) {
            Schedule::whereDate('start_time', $targetDate)->update(['conflict_notes' => null]);
        }

        return [
            'resolved_count' => $resolvedCount,
            'actions' => $actions,
        ];
    }
}
