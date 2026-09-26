<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Student;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EligibilityService
{
    /**
     * Validate all 10 eligibility rules for an individual student registration.
     * Returns an array: ['valid' => bool, 'error' => ?string, 'field' => ?string]
     *
     * @return array{valid: bool, error: ?string, field: ?string}
     */
    public function validateIndividualRegistration(
        Student|int $student,
        Program|int $program,
        ?int $excludeEntryId = null
    ): array {
        // Check 1: Student exists
        if (! ($student instanceof Student)) {
            $student = Student::with(['group', 'zone'])->find($student);
        }
        if (! $student) {
            return [
                'valid' => false,
                'error' => 'Student record not found.',
                'field' => 'student_id',
            ];
        }

        // Check 2: Student has a Group
        if (empty($student->group_id)) {
            return [
                'valid' => false,
                'error' => "Student '{$student->name}' does not belong to any Group.",
                'field' => 'group_id',
            ];
        }

        // Check 3: Programme exists
        if (! ($program instanceof Program)) {
            $program = Program::with('zone')->find($program);
        }
        if (! $program) {
            return [
                'valid' => false,
                'error' => 'Programme not found.',
                'field' => 'program_id',
            ];
        }

        // Check 4: Zone Eligibility
        $isMixZone = $program->isMixZone();
        if ($isMixZone) {
            // Check Mix Zone exceptions
            if (! $program->mix_zone_open_to_all) {
                $rules = $program->eligibility_rules ?? [];
                if (! empty($rules['allowed_zones'])) {
                    $studentZoneCode = $student->zone?->code ?? $student->category;
                    $studentZoneName = $student->zone?->name ?? $student->category;
                    $allowed = (array) $rules['allowed_zones'];

                    if (! in_array($studentZoneCode, $allowed) && ! in_array($studentZoneName, $allowed)) {
                        return [
                            'valid' => false,
                            'error' => "This Mix Zone programme is restricted to specific zones. Student's zone ({$studentZoneName}) is not eligible.",
                            'field' => 'zone',
                        ];
                    }
                }
            }
        } else {
            // Standard zone programme: student's zone must match programme's zone
            $progZoneId = $program->zone_id;
            $studentZoneId = $student->zone_id;

            $progZoneName = $program->zone?->name ?? $program->eligibility;
            $studentZoneName = $student->zone?->name ?? $student->category ?? Zone::determineZoneNameFromClass($student->class_level);

            $matches = false;
            if ($progZoneId && $studentZoneId && $progZoneId === $studentZoneId) {
                $matches = true;
            } elseif (! empty($progZoneName) && ! empty($studentZoneName) && strtolower(trim($progZoneName)) === strtolower(trim($studentZoneName))) {
                $matches = true;
            } elseif (! empty($progZoneName) && $student->class_level) {
                $classZone = Zone::determineZoneNameFromClass($student->class_level);
                if ($classZone && strtolower(trim($progZoneName)) === strtolower(trim($classZone))) {
                    $matches = true;
                }
            }

            if (! $matches) {
                return [
                    'valid' => false,
                    'error' => "Student's zone ({$studentZoneName}) does not match programme zone ({$progZoneName}).",
                    'field' => 'zone',
                ];
            }
        }

        // Check 5: Individual Programme Limit (Max 5)
        if ($program->countsTowardIndividualLimit()) {
            $activeIndividualCount = ProgramEntry::where('student_id', $student->id)
                ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
                ->when($excludeEntryId, fn ($q) => $q->where('id', '!=', $excludeEntryId))
                ->whereHas('program', function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('type', 'individual')
                            ->orWhere('individual_limit_counted', true);
                    });
                })
                ->count();

            if ($activeIndividualCount >= Student::MAX_INDIVIDUAL_PROGRAMS) {
                return [
                    'valid' => false,
                    'error' => 'Maximum individual programme limit reached. A student can participate in a maximum of 5 individual programmes.',
                    'field' => 'individual_limit',
                ];
            }
        }

        // Check 6: Programme-Specific Overall Participant Limit
        if (! empty($program->max_participants)) {
            $activeTotalEntries = ProgramEntry::where('program_id', $program->id)
                ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
                ->when($excludeEntryId, fn ($q) => $q->where('id', '!=', $excludeEntryId))
                ->count();

            if ($activeTotalEntries >= $program->max_participants) {
                return [
                    'valid' => false,
                    'error' => "Maximum participant limit ({$program->max_participants}) reached for this programme.",
                    'field' => 'max_participants',
                ];
            }
        }

        // Check 7: Group-Wise Participant Limit
        $maxPerGroup = $program->max_participants_per_group ?? 2;
        if ($maxPerGroup > 0) {
            $activeGroupEntries = ProgramEntry::where('program_id', $program->id)
                ->where('group_id', $student->group_id)
                ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
                ->when($excludeEntryId, fn ($q) => $q->where('id', '!=', $excludeEntryId))
                ->count();

            if ($activeGroupEntries >= $maxPerGroup) {
                $groupName = $student->group?->name ?? 'Group';

                return [
                    'valid' => false,
                    'error' => "Maximum participants ({$maxPerGroup}) from {$groupName} already reached for this programme.",
                    'field' => 'max_participants_per_group',
                ];
            }
        }

        // Check 8: Duplicate Registration
        $alreadyRegistered = ProgramEntry::where('program_id', $program->id)
            ->where('student_id', $student->id)
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->when($excludeEntryId, fn ($q) => $q->where('id', '!=', $excludeEntryId))
            ->exists();

        if ($alreadyRegistered) {
            return [
                'valid' => false,
                'error' => "Student '{$student->name}' is already registered for this programme.",
                'field' => 'student_id',
            ];
        }

        // Check 10: Specific Eligibility Rules (e.g. gender or class)
        if (! empty($program->gender_restriction) && $program->gender_restriction !== 'all') {
            if (strtolower($student->gender) !== strtolower($program->gender_restriction)) {
                return [
                    'valid' => false,
                    'error' => "This programme is restricted to {$program->gender_restriction} participants.",
                    'field' => 'gender_restriction',
                ];
            }
        }

        $rules = $program->eligibility_rules ?? [];
        if (! empty($rules['allowed_classes'])) {
            $allowedClasses = (array) $rules['allowed_classes'];
            if (! in_array($student->class_level, $allowedClasses)) {
                return [
                    'valid' => false,
                    'error' => "Student's class ({$student->class_level}) is not eligible for this programme.",
                    'field' => 'class_level',
                ];
            }
        }

        return ['valid' => true, 'error' => null, 'field' => null];
    }

    /**
     * Validate eligibility for a Group Programme Registration.
     *
     * @param  array<int>  $studentIds
     * @return array{valid: bool, error: ?string, field: ?string}
     */
    public function validateGroupRegistration(
        Group|int $group,
        Program|int $program,
        array $studentIds,
        ?int $excludeEntryId = null
    ): array {
        if (! ($group instanceof Group)) {
            $group = Group::find($group);
        }
        if (! $group) {
            return ['valid' => false, 'error' => 'Group not found.', 'field' => 'group_id'];
        }

        if (! ($program instanceof Program)) {
            $program = Program::with('zone')->find($program);
        }
        if (! $program) {
            return ['valid' => false, 'error' => 'Programme not found.', 'field' => 'program_id'];
        }

        if (! $program->isGroup()) {
            return ['valid' => false, 'error' => 'This programme is not a group programme.', 'field' => 'type'];
        }

        // Check 9: Participant Count Limit
        $maxAllowed = (int) ($program->max_participants ?? $program->participant_count ?? 10);
        if (count($studentIds) < 1) {
            return [
                'valid' => false,
                'error' => 'Please provide participating students for this group programme.',
                'field' => 'participant_count',
            ];
        }

        if (count($studentIds) > $maxAllowed) {
            return [
                'valid' => false,
                'error' => "This group programme allows at most {$maxAllowed} participants. ".count($studentIds).' provided.',
                'field' => 'participant_count',
            ];
        }

        // Check 7: Group-wise Limit for entries from this group
        $maxPerGroup = $program->max_participants_per_group ?? 1;
        $activeGroupEntries = ProgramEntry::where('program_id', $program->id)
            ->where('group_id', $group->id)
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->when($excludeEntryId, fn ($q) => $q->where('id', '!=', $excludeEntryId))
            ->count();

        if ($activeGroupEntries >= $maxPerGroup) {
            return [
                'valid' => false,
                'error' => "Maximum group entries ({$maxPerGroup}) from {$group->name} already reached for this programme.",
                'field' => 'max_participants_per_group',
            ];
        }

        // Verify all students belong to this Group
        $students = Student::whereIn('id', $studentIds)->get();
        if ($students->count() !== count($studentIds)) {
            return ['valid' => false, 'error' => 'One or more selected students do not exist.', 'field' => 'students'];
        }

        foreach ($students as $st) {
            if ($st->group_id !== $group->id) {
                return [
                    'valid' => false,
                    'error' => "Student '{$st->name}' does not belong to {$group->name}.",
                    'field' => 'group_id',
                ];
            }

            // Check Zone if not Mix Zone
            if (! $program->isMixZone()) {
                $progZoneName = $program->zone?->name ?? $program->eligibility;
                $studentZoneName = $st->zone?->name ?? $st->category;
                if ($progZoneName && $studentZoneName && strtolower(trim($progZoneName)) !== strtolower(trim($studentZoneName))) {
                    return [
                        'valid' => false,
                        'error' => "Student '{$st->name}' is from zone {$studentZoneName}, but this programme is for {$progZoneName}.",
                        'field' => 'zone',
                    ];
                }
            }
        }

        return ['valid' => true, 'error' => null, 'field' => null];
    }

    /**
     * Concurrency-safe, transactional individual registration.
     */
    public function registerIndividual(
        Student $student,
        Program $program,
        array $extraAttributes = []
    ): ProgramEntry {
        return DB::transaction(function () use ($student, $program, $extraAttributes) {
            // Lock records to prevent concurrent race condition
            $lockedStudent = Student::where('id', $student->id)->lockForUpdate()->first();
            $lockedProgram = Program::where('id', $program->id)->lockForUpdate()->first();

            $validation = $this->validateIndividualRegistration($lockedStudent, $lockedProgram);
            if (! $validation['valid']) {
                throw ValidationException::withMessages([
                    $validation['field'] ?? 'registration' => [$validation['error']],
                ]);
            }

            $chestNumber = $extraAttributes['chest_number'] ?? $lockedStudent->student_id;
            $status = $extraAttributes['status'] ?? 'pending';

            $entry = ProgramEntry::create([
                'program_id' => $lockedProgram->id,
                'student_id' => $lockedStudent->id,
                'group_id' => $lockedStudent->group_id,
                'chest_number' => $chestNumber,
                'status' => $status,
                'conflict_flag' => $extraAttributes['conflict_flag'] ?? false,
                'notes' => $extraAttributes['notes'] ?? null,
            ]);

            AuditLogger::log('register_individual_entry', $entry, null, $entry->toArray());

            return $entry;
        });
    }

    /**
     * Concurrency-safe, transactional group registration with multiple students.
     *
     * @param  array<int>  $studentIds
     */
    public function registerGroup(
        Group $group,
        Program $program,
        array $studentIds,
        array $extraAttributes = []
    ): ProgramEntry {
        return DB::transaction(function () use ($group, $program, $studentIds, $extraAttributes) {
            $lockedGroup = Group::where('id', $group->id)->lockForUpdate()->first();
            $lockedProgram = Program::where('id', $program->id)->lockForUpdate()->first();

            $validation = $this->validateGroupRegistration($lockedGroup, $lockedProgram, $studentIds);
            if (! $validation['valid']) {
                throw ValidationException::withMessages([
                    $validation['field'] ?? 'registration' => [$validation['error']],
                ]);
            }

            $leaderId = $extraAttributes['leader_id'] ?? ($extraAttributes['student_id'] ?? ($studentIds[0] ?? null));
            $leaderStudent = Student::find($leaderId) ?? Student::find($studentIds[0] ?? null);
            $chestNumber = $extraAttributes['chest_number']
                ?? ($leaderStudent?->student_id ?: ($lockedGroup->code.'-'.$lockedProgram->code.'-'.(ProgramEntry::where('program_id', $lockedProgram->id)->where('group_id', $lockedGroup->id)->count() + 1)));

            $entry = ProgramEntry::create([
                'program_id' => $lockedProgram->id,
                'student_id' => $leaderStudent?->id, // Team Leader whose name appears on result posters
                'group_id' => $lockedGroup->id,
                'chest_number' => $chestNumber,
                'status' => $extraAttributes['status'] ?? 'pending',
                'conflict_flag' => $extraAttributes['conflict_flag'] ?? false,
                'notes' => $extraAttributes['notes'] ?? null,
            ]);

            // Ensure leader is included in participant IDs
            $allParticipantIds = array_values(array_unique(array_filter(array_merge([$leaderStudent?->id], $studentIds))));

            // Attach participants in pivot table
            foreach ($allParticipantIds as $stId) {
                $role = ($stId == $leaderStudent?->id) ? 'captain' : 'participant';
                $entry->participants()->attach($stId, ['role' => $role]);
            }

            AuditLogger::log('register_group_entry', $entry, null, $entry->toArray());

            return $entry;
        });
    }
}
