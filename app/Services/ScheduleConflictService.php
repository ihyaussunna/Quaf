<?php

namespace App\Services;

use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
     * Test whether two time intervals overlap: [startA, endA) and [startB, endB).
     */
    public function isOverlapping(Carbon $startA, Carbon $endTimeA, Carbon $startB, Carbon $endTimeB): bool
    {
        return $startA->lt($endTimeB) && $endTimeA->gt($startB);
    }
}
