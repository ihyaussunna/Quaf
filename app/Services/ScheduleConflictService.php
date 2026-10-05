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
                            'time' => $schA->start_time->format('h:i A').' - '.$schA->end_time->format('h:i A'),
                        ],
                        'program_b' => [
                            'id' => $schB->program_id,
                            'name' => $schB->program?->name,
                            'code' => $schB->program?->code,
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
                            'time' => $schA->start_time->format('h:i A').' - '.$schA->end_time->format('h:i A'),
                        ],
                        'program_b' => [
                            'id' => $schB->program_id,
                            'name' => $schB->program?->name,
                            'code' => $schB->program?->code,
                            'stage' => $schB->stage?->name,
                            'venue' => $schB->stage?->venue,
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
}
