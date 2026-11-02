<?php

namespace App\Services;

use App\Models\CoinTransaction;
use App\Models\FestivalSetting;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CoinRewardService
{
    /**
     * Official QUAF Season 09 Group Program Reward Rule Identifier.
     */
    public const REWARD_RULE_ID = 'QUAF09_GROUP_PROGRAM_REWARD_RULE_V1';

    /**
     * Official competition declared result statuses that warrant coin distribution.
     */
    public const DECLARED_STATUSES = ['send', 'delivered', 'announced', 'published'];

    /**
     * Official QUAF Season 09 Graded Individual Program Reward Chart:
     * Coins per winner.
     */
    public const INDIVIDUAL_REWARD_CHART = [
        1 => [
            'A+' => 300,
            'A' => 280,
            'B' => 270,
            'C' => 260,
            'NO_GRADE' => 200,
        ],
        2 => [
            'A+' => 270,
            'A' => 250,
            'B' => 240,
            'C' => 230,
            'NO_GRADE' => 170,
        ],
        3 => [
            'A+' => 240,
            'A' => 220,
            'B' => 210,
            'C' => 200,
            'NO_GRADE' => 140,
        ],
    ];

    /**
     * Official QUAF Season 09 Graded Group Program Reward Chart:
     * Coins per individual eligible participant.
     */
    public const GROUP_REWARD_CHART = [
        1 => [
            'A+' => 200,
            'A' => 180,
            'B' => 170,
            'C' => 160,
            'NO_GRADE' => 100,
        ],
        2 => [
            'A+' => 180,
            'A' => 160,
            'B' => 150,
            'C' => 140,
            'NO_GRADE' => 80,
        ],
        3 => [
            'A+' => 160,
            'A' => 140,
            'B' => 130,
            'C' => 120,
            'NO_GRADE' => 60,
        ],
    ];

    /**
     * Calculate individual coins reward for a participant based on position, grade, and program type.
     */
    public static function calculateReward(int $position, ?string $grade, bool $isGroup = false): int
    {
        $chart = $isGroup ? self::GROUP_REWARD_CHART : self::INDIVIDUAL_REWARD_CHART;

        if (! isset($chart[$position])) {
            return 0;
        }

        $posChart = $chart[$position];
        $normalizedGrade = self::normalizeGrade($grade);

        if ($normalizedGrade !== null && isset($posChart[$normalizedGrade])) {
            return $posChart[$normalizedGrade];
        }

        return $posChart['NO_GRADE'] ?? 0;
    }

    /**
     * Calculate individual coins reward for a participant based on position and grade (Group programs).
     */
    public static function calculateGroupRewardPerParticipant(int $position, ?string $grade): int
    {
        return self::calculateReward($position, $grade, true);
    }

    /**
     * Normalize grade string to A+, A, B, C, or null (No Grade).
     */
    public static function normalizeGrade(?string $grade): ?string
    {
        if ($grade === null) {
            return null;
        }

        $upper = strtoupper(trim($grade));

        if (in_array($upper, ['A+', 'A', 'B', 'C'], true)) {
            return $upper;
        }

        return null;
    }

    /**
     * Resolve grade for a placed program entry.
     */
    public static function resolveGradeForEntry(ProgramEntry $entry, ?Result $result = null, ?int $position = null): ?string
    {
        if ($result && $position) {
            $declared = match ($position) {
                1 => $result->first_grade ?? null,
                2 => $result->second_grade ?? null,
                3 => $result->third_grade ?? null,
                default => null,
            };

            $normalized = self::normalizeGrade($declared);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        if (! empty($entry->computed_grade)) {
            $normalized = self::normalizeGrade($entry->computed_grade);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        $submittedSheets = $entry->relationLoaded('scoreSheets')
            ? $entry->scoreSheets->where('is_submitted', true)
            : $entry->scoreSheets()->where('is_submitted', true)->get();

        $judgeGrade = $submittedSheets->pluck('criteria_scores.grade')->filter()->first();
        if ($judgeGrade) {
            $normalized = self::normalizeGrade($judgeGrade);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        $avgScore = $submittedSheets->isNotEmpty() ? $submittedSheets->avg('total_score') : null;
        if ($avgScore !== null && (float) $avgScore > 0) {
            $gradeInfo = PointCalculationService::getGradeFromScore((float) $avgScore);

            return self::normalizeGrade($gradeInfo['grade'] ?? null);
        }

        return null;
    }

    /**
     * Retrieve official eligible participating students for a program entry.
     * Includes all students registered for the entry (both entry student and pivot participants).
     *
     * @return Collection<int, Student>
     */
    public static function getEligibleParticipantsForEntry(ProgramEntry $entry, bool $strictAttendance = true): Collection
    {
        if ($strictAttendance && $entry->attendance_status === 'absent') {
            return collect();
        }

        $all = collect();

        // 1. Primary registered student (individual participant or team captain/leader)
        if ($entry->student) {
            $all->push($entry->student);
        } elseif ($entry->student_id) {
            $stu = Student::find($entry->student_id);
            if ($stu) {
                $all->push($stu);
            }
        }

        // 2. All registered team participants from pivot table
        $participants = $entry->relationLoaded('participants')
            ? $entry->participants
            : $entry->participants()->get();

        foreach ($participants as $p) {
            $all->push($p);
        }

        // 3. If still empty and it's a group program, check sibling entries for the same group in this program
        if ($all->isEmpty() && $entry->group_id && $entry->program_id) {
            $siblingEntries = ProgramEntry::where('program_id', $entry->program_id)
                ->where('group_id', $entry->group_id)
                ->with(['participants', 'student'])
                ->get();

            foreach ($siblingEntries as $sibling) {
                if ($sibling->student) {
                    $all->push($sibling->student);
                }
                foreach ($sibling->participants as $sp) {
                    $all->push($sp);
                }
            }
        }

        // 4. If still empty, check if chest_number directly matches a student identifier
        if ($all->isEmpty() && ! empty($entry->chest_number)) {
            $chestClean = trim($entry->chest_number);
            $matchedStudent = Student::where('student_id', $chestClean)
                ->orWhere('student_id', 'QF'.$chestClean)
                ->when(Schema::hasColumn('students', 'chest_number'), fn ($q) => $q->orWhere('chest_number', $chestClean))
                ->first();

            if ($matchedStudent) {
                $all->push($matchedStudent);
            }
        }

        return $all->unique('id')->values();
    }

    /**
     * Determine if a program is specifically excluded from coin rewards.
     * E.g., Q9-236 Insight (coins should not be distributed to registered participants).
     */
    public static function isProgramExcludedFromCoins(?Program $program): bool
    {
        if (! $program) {
            return true;
        }

        $code = strtoupper(trim((string) $program->code));
        $name = strtolower(trim((string) $program->name));

        if ($code === 'Q9-236' || $name === 'insight') {
            return true;
        }

        if (FestivalSetting::get("program_coins_disabled_{$program->id}", false) === '1') {
            return true;
        }

        return false;
    }

    /**
     * Generate preview of coin distribution for a group or individual program result prior to publishing.
     *
     * @return array<string, mixed>
     */
    public function previewGroupCoinDistribution(Result $result): array
    {
        CoinTransaction::ensureSchema();

        $program = $result->program ?? Program::find($result->program_id);
        $isGroup = $program ? $program->isGroup() : false;

        if (self::isProgramExcludedFromCoins($program)) {
            return [
                'program_id' => $program?->id,
                'program_name' => $program?->name ?? 'Unknown Program',
                'program_code' => $program?->code ?? '',
                'is_group' => $isGroup,
                'reward_rule_id' => self::REWARD_RULE_ID,
                'placements' => [],
                'total_eligible_participants' => 0,
                'total_coins_to_credit' => 0,
                'can_publish' => true,
                'flags' => ["ഈ പ്രോഗ്രാമിന് ({$program?->code} {$program?->name}) വിദ്യാർത്ഥികൾക്ക് കോയിൻ നൽകേണ്ടതില്ല എന്ന് ഒഴിവാക്കിയിരിക്കുന്നു."],
            ];
        }

        $placements = [];
        $totalParticipants = 0;
        $totalCoins = 0;
        $flags = [];

        $podiumWinners = $result->getPodiumWinnersWithTies();

        foreach ([1, 2, 3] as $pos) {
            $baseLabel = match ($pos) {
                1 => '1st Prize',
                2 => '2nd Prize',
                3 => '3rd Prize',
                default => "Position {$pos}",
            };

            foreach ($podiumWinners[$pos] as $wData) {
                /** @var ProgramEntry|null $entry */
                $entry = $wData['entry'];
                if (! $entry) {
                    continue;
                }

                $group = $entry->group;
                $grade = $wData['grade'] ?? self::resolveGradeForEntry($entry, $result, $pos);
                $coinsPerParticipant = self::calculateReward($pos, $grade, $isGroup);
                $participants = self::getEligibleParticipantsForEntry($entry, false);
                $isAbsent = false;

                $participantItems = [];
                foreach ($participants as $student) {
                    $participantItems[] = [
                        'student_id' => $student->id,
                        'student_number' => $student->student_id ?? 'STU-'.$student->id,
                        'name' => $student->name,
                        'role' => $student->pivot?->role ?? ($isGroup ? 'participant' : 'individual'),
                        'coins' => $coinsPerParticipant,
                    ];
                }

                $count = $participants->count();
                $subtotal = $count * $coinsPerParticipant;
                $posLabel = $baseLabel.(! empty($wData['is_tied']) ? ' (Shared)' : '');

                if ($participants->isEmpty()) {
                    $identifier = $isGroup ? ($group?->name ?? 'Team') : ($entry->student?->name ?? 'Participant');
                    $flags[] = "{$posLabel} ({$identifier}): No registered participant found.";
                }

                if ($isAbsent) {
                    $identifier = $isGroup ? ($group?->name ?? 'Team') : ($entry->student?->name ?? 'Participant');
                    $flags[] = "{$posLabel} ({$identifier}): Marked absent in Green Room attendance. Zero coins credited.";
                }

                $totalParticipants += $count;
                $totalCoins += $subtotal;

                $placements[] = [
                    'position' => $pos,
                    'position_label' => $posLabel,
                    'entry_id' => $entry->id,
                    'chest_number' => $entry->chest_number,
                    'group_id' => $entry->group_id,
                    'group_name' => $isGroup ? ($group?->name ?? 'Team') : ($entry->student?->name ?? $group?->name ?? 'Participant'),
                    'group_code' => $group?->code ?? '',
                    'attendance_status' => $entry->attendance_status,
                    'is_absent' => $isAbsent,
                    'is_tied' => ! empty($wData['is_tied']),
                    'grade' => $grade ?: 'No Grade',
                    'coins_per_participant' => $coinsPerParticipant,
                    'participants_count' => $count,
                    'total_coins' => $subtotal,
                    'participants' => $participantItems,
                ];
            }
        }

        return [
            'program_id' => $program?->id,
            'program_name' => $program?->name ?? 'Unknown Program',
            'program_code' => $program?->code ?? '',
            'is_group' => $isGroup,
            'reward_rule_id' => self::REWARD_RULE_ID,
            'placements' => $placements,
            'total_eligible_participants' => $totalParticipants,
            'total_coins_to_credit' => $totalCoins,
            'can_publish' => true,
            'flags' => $flags,
        ];
    }

    /**
     * Distribute individual wallet coins for a published program result (group or individual).
     * Enforces strict idempotency and auditable transaction logs.
     *
     * @return array{credited_count: int, total_coins: int, skipped: bool}
     */
    public function distributeCoinsForResult(Result $result): array
    {
        CoinTransaction::ensureSchema();

        $program = $result->program ?? Program::find($result->program_id);
        if (! $program || self::isProgramExcludedFromCoins($program)) {
            return [
                'credited_count' => 0,
                'total_coins' => 0,
                'skipped' => true,
            ];
        }

        return DB::transaction(function () use ($result, $program) {
            $podiumWinners = $result->getPodiumWinnersWithTies();

            $creditedCount = 0;
            $totalCoins = 0;
            $affectedStudentIds = [];
            $isGroup = $program->isGroup();
            $awardedStudentsInProgram = [];

            // 1. Gather all existing positive credit transactions for this result or program
            $existingCredits = CoinTransaction::where('transaction_type', 'credit')
                ->where('coins', '>', 0)
                ->where(function ($q) use ($result, $program) {
                    $q->where('result_id', $result->id)
                        ->orWhere('program_id', $program->id);
                })
                ->get()
                ->keyBy('student_id');

            $allNewWinnerStudentIds = [];

            // 2. Iterate through new podium winners
            foreach ([1, 2, 3] as $pos) {
                $posLabel = match ($pos) {
                    1 => '1st Prize',
                    2 => '2nd Prize',
                    3 => '3rd Prize',
                    default => "Position {$pos}",
                };

                foreach ($podiumWinners[$pos] as $wData) {
                    /** @var ProgramEntry|null $entry */
                    $entry = $wData['entry'];
                    if (! $entry || $entry->attendance_status === 'absent') {
                        continue;
                    }

                    $grade = $wData['grade'] ?? self::resolveGradeForEntry($entry, $result, $pos);
                    $coins = self::calculateReward($pos, $grade, $isGroup);
                    $participants = self::getEligibleParticipantsForEntry($entry, true);

                    if ($participants->isEmpty() || $coins <= 0) {
                        continue;
                    }

                    foreach ($participants as $student) {
                        $allNewWinnerStudentIds[] = $student->id;

                        // Strict Deduplication: If student was already awarded in this program, keep the higher reward and skip lower/duplicate
                        if (isset($awardedStudentsInProgram[$student->id])) {
                            $prev = $awardedStudentsInProgram[$student->id];
                            if ($coins > $prev['coins']) {
                                // Delete previous lower transaction by ID
                                CoinTransaction::where('id', $prev['tx_id'])->delete();
                            } else {
                                // Already has equal or higher reward for this program; skip duplicate
                                continue;
                            }
                        }

                        $existing = $existingCredits->get($student->id);

                        // If student already has exact matching active credit, avoid any database delete or write locks entirely!
                        if ($existing && (int) $existing->coins === $coins && (int) $existing->prize_position === $pos && $existing->grade === ($grade ?: 'NO_GRADE')) {
                            $awardedStudentsInProgram[$student->id] = [
                                'tx_id' => $existing->id,
                                'coins' => $coins,
                            ];
                            $affectedStudentIds[] = $student->id;

                            continue;
                        }

                        // Remove any stale reversal for this result & student so net matches exact new coins (delete by primary key only)
                        $staleReversalIds = CoinTransaction::where('result_id', $result->id)
                            ->where('student_id', $student->id)
                            ->where('transaction_type', 'reversal')
                            ->pluck('id');

                        if ($staleReversalIds->isNotEmpty()) {
                            CoinTransaction::whereIn('id', $staleReversalIds)->delete();
                        }

                        if ($existing) {
                            // Clean up any extra duplicate credit transactions (delete by primary key only)
                            $dupCreditIds = CoinTransaction::where('student_id', $student->id)
                                ->where('transaction_type', 'credit')
                                ->where('id', '!=', $existing->id)
                                ->where(function ($q) use ($result, $program) {
                                    $q->where('result_id', $result->id)
                                        ->orWhere('program_id', $program->id);
                                })
                                ->pluck('id');

                            if ($dupCreditIds->isNotEmpty()) {
                                CoinTransaction::whereIn('id', $dupCreditIds)->delete();
                            }
                        }

                        $ref = "COIN-RES{$result->id}-STU{$student->id}-P{$pos}-CR";
                        $progType = $isGroup ? 'Group' : 'Individual';

                        $metadata = [
                            'program_name' => $program->name,
                            'program_type' => $program->type,
                            'group_name' => $entry->group?->name,
                            'student_number' => $student->student_id,
                            'student_name' => $student->name,
                            'prize_position' => $pos,
                            'grade' => $grade ?: 'NO_GRADE',
                            'participant_role' => $student->pivot?->role ?? ($isGroup ? 'participant' : 'individual'),
                        ];

                        $description = "QUAF 09 {$progType} Reward: {$program->name} - {$posLabel} - Grade ".($grade ?: 'No Grade')." (+{$coins} coins)";

                        if ($existing) {
                            $oldCoins = (int) $existing->coins;
                            $oldPos = (int) $existing->prize_position;

                            if ($coins > $oldCoins) {
                                $diff = $coins - $oldCoins;
                                $description .= " (Judgment updated: promoted from Pos {$oldPos} to Pos {$pos}; +{$diff} coins added; now +{$coins} coins)";
                                $metadata['judgment_updated'] = true;
                                $metadata['action'] = 'promoted';
                                $metadata['previous_position'] = $oldPos;
                                $metadata['previous_coins'] = $oldCoins;
                                $metadata['coins_added'] = $diff;
                            } elseif ($coins < $oldCoins) {
                                $clawback = $oldCoins - $coins;
                                $description .= " (Judgment updated: demoted from Pos {$oldPos} to Pos {$pos}; {$clawback} coins clawed back; now +{$coins} coins)";
                                $metadata['judgment_updated'] = true;
                                $metadata['action'] = 'demoted';
                                $metadata['previous_position'] = $oldPos;
                                $metadata['previous_coins'] = $oldCoins;
                                $metadata['clawback_amount'] = $clawback;
                            }
                        }

                        $tx = CoinTransaction::updateOrCreate(
                            [
                                'id' => $existing?->id,
                            ],
                            [
                                'result_id' => $result->id,
                                'entry_id' => $entry->id,
                                'student_id' => $student->id,
                                'transaction_type' => 'credit',
                                'group_id' => $entry->group_id ?: $student->group_id,
                                'program_id' => $program->id,
                                'prize_position' => $pos,
                                'grade' => $grade ?: 'NO_GRADE',
                                'reward_rule_id' => self::REWARD_RULE_ID,
                                'coins' => $coins,
                                'transaction_reference' => $existing?->transaction_reference ?? $ref,
                                'status' => 'completed',
                                'description' => $description,
                                'metadata' => $metadata,
                            ]
                        );

                        // Ensure no extra duplicate was left for this student and program (delete by primary key only)
                        $extraDupIds = CoinTransaction::where('student_id', $student->id)
                            ->where('transaction_type', 'credit')
                            ->where('id', '!=', $tx->id)
                            ->where(function ($q) use ($result, $program) {
                                $q->where('result_id', $result->id)
                                    ->orWhere('program_id', $program->id);
                            })
                            ->pluck('id');

                        if ($extraDupIds->isNotEmpty()) {
                            CoinTransaction::whereIn('id', $extraDupIds)->delete();
                        }

                        $awardedStudentsInProgram[$student->id] = [
                            'tx_id' => $tx->id,
                            'coins' => $coins,
                        ];

                        $affectedStudentIds[] = $student->id;
                        $creditedCount++;
                        $totalCoins += $coins;
                    }
                }
            }

            // 3. Handle previously awarded participants who are NO LONGER placed in the updated result (Unplaced / Removed)
            $allNewWinnerStudentIds = array_unique($allNewWinnerStudentIds);
            foreach ($existingCredits as $staleStudentId => $staleCredit) {
                if (! in_array($staleStudentId, $allNewWinnerStudentIds, true)) {
                    $staleCoins = (int) $staleCredit->coins;
                    $stalePos = (int) $staleCredit->prize_position;

                    $staleCredit->update([
                        'coins' => 0,
                        'transaction_type' => 'reversal',
                        'status' => 'reversed',
                        'description' => "QUAF 09 Reversal: {$program->name} - Position {$stalePos} (Unplaced after judgment update; {$staleCoins} coins clawed back)",
                        'metadata' => [
                            'action' => 'unplaced_clawback',
                            'previous_position' => $stalePos,
                            'previous_coins' => $staleCoins,
                            'clawback_amount' => $staleCoins,
                            'reason' => 'Podium changed after score correction',
                            'unplaced_at' => now()->toIso8601String(),
                        ],
                    ]);

                    $affectedStudentIds[] = $staleStudentId;
                }
            }

            // 4. Synchronize wallet balances immediately for ALL affected students
            if (! empty($affectedStudentIds)) {
                $this->syncStudentCoinsCache(array_unique($affectedStudentIds));
            }

            return [
                'credited_count' => $creditedCount,
                'total_coins' => $totalCoins,
                'skipped' => false,
            ];
        }, 5);
    }

    /**
     * Auditable reversal of all distributed coins for a result.
     * Used when results are modified, undeclared, or deleted.
     */
    public function reverseCoinDistributionForResult(Result $result, string $reason = 'Result modified or undeclared'): int
    {
        CoinTransaction::ensureSchema();

        return DB::transaction(function () use ($result, $reason) {
            $credits = CoinTransaction::where('result_id', $result->id)
                ->where('transaction_type', 'credit')
                ->get();

            if ($credits->isEmpty()) {
                return 0;
            }

            $reversedCount = 0;
            $affectedStudentIds = [];

            foreach ($credits as $credit) {
                $revRef = "COIN-REV-RES{$result->id}-STU{$credit->student_id}-{$credit->id}";

                $alreadyReversed = CoinTransaction::where('transaction_reference', $revRef)->exists();
                if ($alreadyReversed) {
                    continue;
                }

                CoinTransaction::create([
                    'student_id' => $credit->student_id,
                    'group_id' => $credit->group_id,
                    'program_id' => $credit->program_id,
                    'entry_id' => $credit->entry_id,
                    'result_id' => $credit->result_id,
                    'prize_position' => $credit->prize_position,
                    'grade' => $credit->grade,
                    'reward_rule_id' => $credit->reward_rule_id,
                    'coins' => -abs($credit->coins),
                    'transaction_type' => 'reversal',
                    'transaction_reference' => $revRef,
                    'status' => 'completed',
                    'description' => "Reversal: {$reason} (Ref: {$credit->transaction_reference})",
                    'metadata' => [
                        'original_transaction_id' => $credit->id,
                        'original_reference' => $credit->transaction_reference,
                        'reversal_reason' => $reason,
                    ],
                ]);

                $affectedStudentIds[] = $credit->student_id;
                $reversedCount++;
            }

            if (! empty($affectedStudentIds)) {
                $this->syncStudentCoinsCache($affectedStudentIds);
            }

            return $reversedCount;
        }, 5);
    }

    /**
     * Synchronize the cached coin balance for students from their transaction ledger.
     *
     * @param  int|array<int>|null  $studentIds
     */
    public function syncStudentCoinsCache(int|array|null $studentIds = null): void
    {
        CoinTransaction::ensureSchema();

        if ($studentIds !== null) {
            $ids = is_array($studentIds) ? array_unique($studentIds) : [$studentIds];
            if (empty($ids)) {
                return;
            }

            $totals = CoinTransaction::select('student_id', DB::raw('SUM(coins) as total_coins'))
                ->whereIn('student_id', $ids)
                ->groupBy('student_id')
                ->pluck('total_coins', 'student_id');

            foreach ($ids as $id) {
                $sum = max(0, (int) ($totals[$id] ?? 0));
                if (Schema::hasColumn('students', 'coins_cache')) {
                    Student::where('id', $id)->update(['coins_cache' => $sum]);
                }
            }
        } else {
            $totals = CoinTransaction::select('student_id', DB::raw('SUM(coins) as total_coins'))
                ->groupBy('student_id')
                ->pluck('total_coins', 'student_id');

            if (Schema::hasColumn('students', 'coins_cache')) {
                Student::query()->update(['coins_cache' => 0]);
                foreach ($totals as $sId => $sum) {
                    Student::where('id', $sId)->update(['coins_cache' => max(0, (int) $sum)]);
                }
            }
        }
    }

    /**
     * Scan and distribute missing coins for all declared competition results.
     * Ensures all students with 1st/2nd/3rd places and grades receive their coins immediately.
     */
    public function distributeMissingCoinsForAllDeclaredResults(): int
    {
        CoinTransaction::ensureSchema();

        $declaredResults = Result::whereIn('status', self::DECLARED_STATUSES)
            ->with([
                'program',
                'firstEntry.student',
                'firstEntry.participants',
                'secondEntry.student',
                'secondEntry.participants',
                'thirdEntry.student',
                'thirdEntry.participants',
            ])
            ->get();

        $totalCredited = 0;
        foreach ($declaredResults as $res) {
            $dist = $this->distributeCoinsForResult($res);
            $totalCredited += $dist['credited_count'];
        }

        $this->syncStudentCoinsCache();

        return $totalCredited;
    }

    /**
     * Ensure coins are accurately distributed for all awarded programs of a student.
     * Checks both individual entries and group program participations.
     */
    public function ensureCoinsDistributedForStudent(Student $student, bool $onlyMediaPublished = false): void
    {
        CoinTransaction::ensureSchema();

        $flag = $onlyMediaPublished ? '_coins_ensured_media_published' : '_coins_ensured_all';
        if (! empty($student->{$flag})) {
            return;
        }
        $student->{$flag} = true;

        // 1. Direct entries (individual and leader entries)
        $directEntries = ProgramEntry::where('student_id', $student->id)->get(['id', 'program_id', 'group_id']);
        $directEntryIds = $directEntries->pluck('id')->all();
        $directProgIds = $directEntries->pluck('program_id')->all();

        // 2. Group participations from pivot table
        $participationEntryIds = DB::table('program_entry_participants')
            ->where('student_id', $student->id)
            ->pluck('entry_id')
            ->all();

        if (! empty($participationEntryIds)) {
            $partProgIds = ProgramEntry::whereIn('id', $participationEntryIds)->pluck('program_id')->all();
            $directProgIds = array_merge($directProgIds, $partProgIds);
        }

        // 3. Group entries for this student's group if enrolled
        $groupEntryIds = [];
        if ($student->group_id) {
            $groupEntryIds = ProgramEntry::where('group_id', $student->group_id)
                ->whereHas('program', fn ($q) => $q->where('type', 'group'))
                ->pluck('id')
                ->all();
        }

        $allRelevantEntryIds = array_values(array_unique(array_filter(array_merge($directEntryIds, $participationEntryIds, $groupEntryIds))));
        $allDirectProgIds = array_values(array_unique(array_filter($directProgIds)));

        // 4. Any result where student has an existing transaction in coin_transactions (covers unplaced clawbacks)
        $existingResultIds = CoinTransaction::where('student_id', $student->id)
            ->whereNotNull('result_id')
            ->pluck('result_id')
            ->all();

        if (empty($allRelevantEntryIds) && empty($allDirectProgIds) && empty($existingResultIds)) {
            return;
        }

        // Find declared results where this student's entry or their group's entry is placed on podium, OR where student has existing transaction
        $resultsQuery = Result::query();
        if ($onlyMediaPublished) {
            $resultsQuery->where('status', 'published')->where('is_media_published', true);
        } else {
            $resultsQuery->whereIn('status', self::DECLARED_STATUSES);
        }

        $resultsQuery->where(function ($q) use ($allRelevantEntryIds, $allDirectProgIds, $existingResultIds) {
            if (! empty($allRelevantEntryIds)) {
                $q->whereIn('first_entry_id', $allRelevantEntryIds)
                    ->orWhereIn('second_entry_id', $allRelevantEntryIds)
                    ->orWhereIn('third_entry_id', $allRelevantEntryIds);
            }
            if (! empty($allDirectProgIds)) {
                $q->orWhereIn('program_id', $allDirectProgIds);
            }
            if (! empty($existingResultIds)) {
                $q->orWhereIn('id', $existingResultIds);
            }
        });

        $results = $resultsQuery->with(['program', 'firstEntry', 'secondEntry', 'thirdEntry'])->get();

        $updated = false;
        foreach ($results as $res) {
            $dist = $this->distributeCoinsForResult($res);
            if ($dist['credited_count'] > 0) {
                $updated = true;
            }
        }

        if ($updated) {
            self::cleanupDuplicateTransactionsForStudent($student);
            $this->syncStudentCoinsCache([$student->id]);
        }
    }

    /**
     * Clean up any duplicate credit transactions for a student so that a student
     * has at most ONE credit transaction per program/result.
     * Re-calculates and synchronizes the student's coins_cache immediately.
     */
    public static function cleanupDuplicateTransactionsForStudent(Student $student): bool
    {
        CoinTransaction::ensureSchema();

        $duplicates = CoinTransaction::where('student_id', $student->id)
            ->where('transaction_type', 'credit')
            ->where('coins', '>', 0)
            ->get()
            ->groupBy(function ($tx) {
                return $tx->program_id ? 'prog_'.$tx->program_id : ($tx->result_id ? 'res_'.$tx->result_id : 'tx_'.$tx->id);
            });

        $hasDuplicates = false;
        foreach ($duplicates as $groupKey => $txs) {
            if ($txs->count() > 1 && ! str_starts_with($groupKey, 'tx_')) {
                $hasDuplicates = true;
                // Keep the best transaction (highest coins, earliest created)
                $sorted = $txs->sortByDesc('coins');
                $keep = $sorted->first();
                $toDeleteIds = $sorted->slice(1)->pluck('id');

                if ($toDeleteIds->isNotEmpty()) {
                    CoinTransaction::whereIn('id', $toDeleteIds)->delete();
                }
            }
        }

        if ($hasDuplicates) {
            app(self::class)->syncStudentCoinsCache([$student->id]);

            return true;
        }

        return false;
    }

    /**
     * Scan and clean up duplicate credit transactions for all students festival-wide.
     *
     * @return array{duplicates_removed: int, students_rebalanced: int}
     */
    public static function cleanupAllDuplicateTransactions(): array
    {
        CoinTransaction::ensureSchema();

        $duplicates = DB::table('coin_transactions')
            ->select('student_id', 'program_id', DB::raw('COUNT(*) as total_count'))
            ->where('transaction_type', 'credit')
            ->whereNotNull('program_id')
            ->where('coins', '>', 0)
            ->groupBy('student_id', 'program_id')
            ->having('total_count', '>', 1)
            ->get();

        $affectedStudentIds = [];
        $duplicatesRemoved = 0;

        foreach ($duplicates as $dup) {
            $txs = CoinTransaction::where('student_id', $dup->student_id)
                ->where('program_id', $dup->program_id)
                ->where('transaction_type', 'credit')
                ->orderByDesc('coins')
                ->orderBy('id')
                ->get();

            $keep = $txs->first();
            $toDelete = $txs->slice(1);

            foreach ($toDelete as $delTx) {
                $delTx->delete();
                $duplicatesRemoved++;
            }

            $affectedStudentIds[] = $dup->student_id;
        }

        // Also check by (student_id, result_id)
        $resultDuplicates = DB::table('coin_transactions')
            ->select('student_id', 'result_id', DB::raw('COUNT(*) as total_count'))
            ->where('transaction_type', 'credit')
            ->whereNotNull('result_id')
            ->where('coins', '>', 0)
            ->groupBy('student_id', 'result_id')
            ->having('total_count', '>', 1)
            ->get();

        foreach ($resultDuplicates as $dup) {
            $txs = CoinTransaction::where('student_id', $dup->student_id)
                ->where('result_id', $dup->result_id)
                ->where('transaction_type', 'credit')
                ->orderByDesc('coins')
                ->orderBy('id')
                ->get();

            $keep = $txs->first();
            $toDelete = $txs->slice(1);

            foreach ($toDelete as $delTx) {
                $delTx->delete();
                $duplicatesRemoved++;
            }

            $affectedStudentIds[] = $dup->student_id;
        }

        $affectedStudentIds = array_values(array_unique($affectedStudentIds));
        if (! empty($affectedStudentIds)) {
            app(self::class)->syncStudentCoinsCache($affectedStudentIds);
        }

        return [
            'duplicates_removed' => $duplicatesRemoved,
            'students_rebalanced' => count($affectedStudentIds),
        ];
    }

    /**
     * Resolve all competition programs where the student achieved 1st/2nd/3rd prize or a grade.
     *
     * @return Collection<int, array{
     *     program_id: int|null,
     *     name: string,
     *     code: string|null,
     *     category: string|null,
     *     position: int|null,
     *     position_title: string|null,
     *     grade: string|null
     * }>
     */
    public function resolveStudentAwardedPrograms(Student $student, bool $onlyMediaPublished = false): Collection
    {
        $awarded = collect();
        $declaredStatuses = self::DECLARED_STATUSES;

        // 1. From CoinTransactions (credits with coins > 0 for declared results)
        $creditsQuery = CoinTransaction::where('student_id', $student->id)
            ->where('transaction_type', 'credit')
            ->where('coins', '>', 0)
            ->with(['program.category', 'program.result', 'result']);

        if ($onlyMediaPublished) {
            $creditsQuery->where(function ($q) {
                $q->whereNull('result_id')
                    ->orWhereHas('result', fn ($rq) => $rq->where('status', 'published')->where('is_media_published', true));
            })->where(function ($q) {
                $q->whereNull('program_id')
                    ->orDoesntHave('program.result')
                    ->orWhereHas('program.result', fn ($rq) => $rq->where('status', 'published')->where('is_media_published', true));
            });
        } else {
            $creditsQuery->where(function ($q) use ($declaredStatuses) {
                $q->whereNull('result_id')
                    ->orWhereHas('result', fn ($rq) => $rq->whereIn('status', $declaredStatuses));
            })->where(function ($q) use ($declaredStatuses) {
                $q->whereNull('program_id')
                    ->orDoesntHave('program.result')
                    ->orWhereHas('program.result', fn ($rq) => $rq->whereIn('status', $declaredStatuses));
            });
        }

        $credits = $creditsQuery->get();

        foreach ($credits as $credit) {
            $prog = $credit->program;
            $res = $credit->result ?? $prog?->result;
            $isPublicDeclared = ! $res || ($res->status === 'published' && (bool) $res->is_media_published);
            if ($onlyMediaPublished && ! $isPublicDeclared) {
                continue;
            }
            if (! $onlyMediaPublished && $res && ! in_array($res->status, $declaredStatuses, true)) {
                continue;
            }

            $pos = in_array((int) $credit->prize_position, [1, 2, 3], true) ? (int) $credit->prize_position : null;
            $grade = ! empty($credit->grade) && $credit->grade !== '-' ? trim($credit->grade) : null;
            if ($pos || $grade) {
                $key = $credit->program_id ?: ('tx_'.$credit->id);
                $awarded->put($key, [
                    'program_id' => $credit->program_id,
                    'name' => $credit->program?->name ?? ($credit->metadata['program_name'] ?? $credit->description),
                    'code' => $credit->program?->code,
                    'category' => $credit->program?->category?->name,
                    'position' => $pos,
                    'position_title' => $pos ? match ($pos) {
                        1 => '1st Prize', 2 => '2nd Prize', 3 => '3rd Prize', default => null
                    } : null,
                    'grade' => $grade,
                ]);
            }
        }

        // 2. From Entries & Group Participations
        $student->loadMissing([
            'entries.program.result',
            'entries.program.category',
            'participations.program.result',
            'participations.program.category',
            'certificates.program.category',
            'certificates.program.result',
        ]);

        $allEntries = $student->entries->merge($student->participations)->unique('id');

        foreach ($allEntries as $entry) {
            $prog = $entry->program;
            $res = $prog?->result ?? ($prog ? Result::where('program_id', $prog->id)->first() : null);
            if (! $prog || ! $res) {
                continue;
            }

            $isPublicDeclared = $res->status === 'published' && (bool) $res->is_media_published;
            if ($onlyMediaPublished && ! $isPublicDeclared) {
                continue;
            }
            if (! $onlyMediaPublished && ! in_array($res->status, $declaredStatuses, true)) {
                continue;
            }

            $pos = null;
            $grade = null;

            // A. Precise check via getPodiumWinnersWithTies() (supports ties and team participants)
            $podiumWinners = $res->getPodiumWinnersWithTies();
            foreach ([1, 2, 3] as $p) {
                foreach ($podiumWinners[$p] as $winnerItem) {
                    $wEntry = $winnerItem['entry'] ?? null;
                    $wEntryId = $winnerItem['id'] ?? $wEntry?->id;
                    $isEntryMatch = ($wEntryId && $wEntryId == $entry->id);

                    $isStudentMatch = false;
                    if ($wEntry) {
                        $participants = self::getEligibleParticipantsForEntry($wEntry, false);
                        $isStudentMatch = $participants->contains('id', $student->id);
                    }

                    $isGroup = $prog->isGroup();
                    $matched = $isGroup
                        ? ($isStudentMatch || ($entry->student_id && $entry->student_id == $student->id))
                        : ($isEntryMatch || $isStudentMatch || ($entry->student_id && $entry->student_id == $student->id));

                    if ($matched) {
                        $pos = $p;
                        $grade = $winnerItem['grade'] ?? self::resolveGradeForEntry($entry, $res, $p);
                        break 2;
                    }
                }
            }

            // B. Direct column fallback
            if ($pos === null) {
                $isGroup = $prog->isGroup();
                $isDirectParticipant = ! $isGroup || ($entry->student_id && $entry->student_id == $student->id) || $student->participations->contains('id', $entry->id);

                if ($isDirectParticipant) {
                    if ($res->first_entry_id === $entry->id) {
                        $pos = 1;
                        $grade = $res->first_grade;
                    } elseif ($res->second_entry_id === $entry->id) {
                        $pos = 2;
                        $grade = $res->second_grade;
                    } elseif ($res->third_entry_id === $entry->id) {
                        $pos = 3;
                        $grade = $res->third_grade;
                    }
                }
            }

            // C. Certificate fallback for position
            if ($pos === null && $student->relationLoaded('certificates')) {
                $cert = $student->certificates->firstWhere('program_id', $prog->id);
                if ($cert && in_array((int) $cert->position, [1, 2, 3], true)) {
                    $pos = (int) $cert->position;
                }
            }

            // D. Grade resolution for placed or non-placed participant
            if (! $grade) {
                $grade = self::resolveGradeForEntry($entry, $res, $pos);
            }

            $grade = (! empty($grade) && $grade !== '-' && $grade !== 'NO_GRADE') ? trim($grade) : null;

            if ($pos || $grade) {
                $key = $prog->id;
                if (! $awarded->has($key)) {
                    $awarded->put($key, [
                        'program_id' => $prog->id,
                        'name' => $prog->name,
                        'code' => $prog->code,
                        'category' => $prog->category?->name,
                        'position' => $pos,
                        'position_title' => $pos ? match ($pos) {
                            1 => '1st Prize', 2 => '2nd Prize', 3 => '3rd Prize', default => null
                        } : null,
                        'grade' => $grade,
                    ]);
                }
            }
        }

        return $awarded->values();
    }

    /**
     * Get real-time coin ledger summary for a student.
     *
     * @param  bool  $onlyMediaPublished  When true, only includes coins from results published by media desk.
     * @return array{balance: int, total_earned: int, competition_earned: int, added_coins: int, total_spent: int}
     */
    public function getStudentCoinSummary(Student $student, bool $onlyMediaPublished = false): array
    {
        CoinTransaction::ensureSchema();

        // Ensure all awarded competition results (individual and group) are distributed for this student
        $this->ensureCoinsDistributedForStudent($student, $onlyMediaPublished);

        $hasSourceColumn = Schema::hasColumn('coin_transactions', 'source');

        // 1. Added coins purchased via Cashier cash deposit
        $addedQuery = CoinTransaction::where('student_id', $student->id)
            ->where('coins', '>', 0);

        if ($hasSourceColumn) {
            $addedQuery->where(function ($q) {
                $q->whereIn('source', ['cash_deposit', 'amount_received'])
                    ->orWhereIn('reward_rule_id', ['QUAF09_CASHIER_COIN_DEPOSIT_V1', 'QUAF09_CASHIER_AMOUNT_RECEIVED_V1']);
            });
        } else {
            $addedQuery->whereIn('reward_rule_id', ['QUAF09_CASHIER_COIN_DEPOSIT_V1', 'QUAF09_CASHIER_AMOUNT_RECEIVED_V1']);
        }
        $addedCoins = (int) $addedQuery->sum('coins');

        // 2. Competition earned coins from grades/prizes
        $competitionQuery = CoinTransaction::where('student_id', $student->id)
            ->where('coins', '>', 0)
            ->whereNotIn('reward_rule_id', ['QUAF09_CASHIER_COIN_DEPOSIT_V1', 'QUAF09_CASHIER_AMOUNT_RECEIVED_V1']);

        if ($hasSourceColumn) {
            $competitionQuery->where(function ($sub) {
                $sub->whereNull('source')
                    ->orWhereNotIn('source', ['cash_deposit', 'amount_received']);
            });
        }

        if ($onlyMediaPublished) {
            $competitionQuery->where(function ($q) {
                $q->whereNull('result_id')
                    ->orWhereHas('result', fn ($rq) => $rq->where('status', 'published')->where('is_media_published', true));
            })->where(function ($q) {
                $q->whereNull('program_id')
                    ->orDoesntHave('program.result')
                    ->orWhereHas('program.result', fn ($rq) => $rq->where('status', 'published')->where('is_media_published', true));
            });
        }

        $competitionEarned = (int) $competitionQuery->sum('coins');
        $earned = $competitionEarned + $addedCoins;

        $spent = (int) abs(CoinTransaction::where('student_id', $student->id)
            ->where('transaction_type', 'debit')
            ->sum('coins'));

        $rawBalance = $earned - $spent;
        $balance = max(0, $rawBalance);
        $excessSpent = max(0, $spent - $earned);
        $clawbackSummary = $this->getStudentClawbackSummary($student, $onlyMediaPublished);

        return [
            'balance' => $balance,
            'raw_balance' => $rawBalance,
            'excess_spent' => $excessSpent,
            'has_excess_spent' => $excessSpent > 0,
            'total_earned' => $earned,
            'competition_earned' => $competitionEarned,
            'added_coins' => $addedCoins,
            'total_spent' => $spent,
            'has_purchase_with_clawback' => $clawbackSummary['has_purchase_with_clawback'] || ($excessSpent > 0),
            'total_clawback_coins' => $clawbackSummary['total_clawback_coins'],
            'clawback_adjustments' => $clawbackSummary['adjustments'],
            'clawback_summary' => $clawbackSummary,
        ];
    }

    /**
     * Get comprehensive summary of result updates, coin clawbacks, and purchases made prior to adjustments.
     *
     * @return array{
     *     has_clawback: bool,
     *     has_purchase_with_clawback: bool,
     *     total_clawback_coins: int,
     *     total_spent: int,
     *     adjustments: array<int, array{
     *         program_id: int|null,
     *         program_name: string,
     *         action: string,
     *         previous_position: int|null,
     *         new_position: int|null,
     *         previous_coins: int,
     *         current_coins: int,
     *         clawback_amount: int,
     *         reason: string,
     *         description: string,
     *         date: string|null
     *     }>
     * }
     */
    public function getStudentClawbackSummary(Student $student, bool $onlyMediaPublished = false): array
    {
        CoinTransaction::ensureSchema();

        $adjustments = [];

        // 1. Reversals (unplaced participants whose entire credit was revoked)
        $reversalsQuery = CoinTransaction::where('student_id', $student->id)
            ->where('transaction_type', 'reversal')
            ->with(['program', 'result']);

        if ($onlyMediaPublished) {
            $reversalsQuery->where(function ($q) {
                $q->whereNull('result_id')
                    ->orWhereHas('result', fn ($rq) => $rq->where('status', 'published')->where('is_media_published', true));
            });
        }

        $reversals = $reversalsQuery->latest()->get();

        foreach ($reversals as $rev) {
            $meta = $rev->metadata ?? [];
            $prevPos = $meta['previous_position'] ?? $rev->prize_position;
            $prevCoins = (int) ($meta['previous_coins'] ?? ($meta['clawback_amount'] ?? 0));
            $clawback = (int) ($meta['clawback_amount'] ?? $prevCoins);

            if ($clawback <= 0 && $prevCoins <= 0) {
                continue;
            }

            $progName = $rev->program?->name ?? ($meta['program_name'] ?? 'Competition');
            $posLabel = match ((int) $prevPos) {
                1 => '1st Prize',
                2 => '2nd Prize',
                3 => '3rd Prize',
                default => ($prevPos ? "Position {$prevPos}" : 'Prize'),
            };

            $adjustments[] = [
                'program_id' => $rev->program_id,
                'program_name' => $progName,
                'action' => 'unplaced',
                'previous_position' => $prevPos ? (int) $prevPos : null,
                'new_position' => null,
                'previous_coins' => $prevCoins,
                'current_coins' => 0,
                'clawback_amount' => $clawback ?: $prevCoins,
                'reason' => 'ഫലം തിരുത്തിയതിലൂടെ സ്ഥാനമില്ലാതായി (Unplaced after judgment update)',
                'description' => "ആദ്യം ലഭിച്ച {$posLabel} തിരുത്തിയപ്പോൾ സ്ഥാനം ഇല്ലാതാവുകയും {$clawback} കോയിനുകൾ പിൻവലിക്കുകയും ചെയ്തു",
                'date' => $rev->updated_at?->format('d M, h:i A'),
            ];
        }

        // 2. Demotions (participants demoted from higher to lower position e.g. 1st -> 2nd, 2nd -> 3rd)
        $demotionsQuery = CoinTransaction::where('student_id', $student->id)
            ->where('transaction_type', 'credit')
            ->with(['program', 'result']);

        if ($onlyMediaPublished) {
            $demotionsQuery->where(function ($q) {
                $q->whereNull('result_id')
                    ->orWhereHas('result', fn ($rq) => $rq->where('status', 'published')->where('is_media_published', true));
            });
        }

        $demotions = $demotionsQuery->latest()->get();

        foreach ($demotions as $dem) {
            $meta = $dem->metadata ?? [];
            $isDemoted = ($meta['action'] ?? null) === 'demoted'
                || ! empty($meta['clawback_amount'])
                || str_contains($dem->description ?? '', 'clawed back')
                || str_contains($dem->description ?? '', 'demoted');

            if ($isDemoted) {
                $oldPos = (int) ($meta['previous_position'] ?? 0);
                $newPos = (int) ($dem->prize_position ?? 0);
                $oldCoins = (int) ($meta['previous_coins'] ?? 0);
                $currentCoins = (int) $dem->coins;
                $clawback = (int) ($meta['clawback_amount'] ?? max(0, $oldCoins - $currentCoins));

                if ($clawback <= 0) {
                    continue;
                }

                $progName = $dem->program?->name ?? ($meta['program_name'] ?? 'Competition');
                $oldPosLabel = match ($oldPos) {
                    1 => '1st Prize',
                    2 => '2nd Prize',
                    3 => '3rd Prize',
                    default => ($oldPos ? "Position {$oldPos}" : 'Previous Prize'),
                };
                $newPosLabel = match ($newPos) {
                    1 => '1st Prize',
                    2 => '2nd Prize',
                    3 => '3rd Prize',
                    default => ($newPos ? "Position {$newPos}" : 'New Prize'),
                };

                $adjustments[] = [
                    'program_id' => $dem->program_id,
                    'program_name' => $progName,
                    'action' => 'demoted',
                    'previous_position' => $oldPos ?: null,
                    'new_position' => $newPos ?: null,
                    'previous_coins' => $oldCoins,
                    'current_coins' => $currentCoins,
                    'clawback_amount' => $clawback,
                    'reason' => 'ഫലം തിരുത്തിയതിലൂടെ സ്ഥാനം താഴ്ന്നു (Demoted after judgment update)',
                    'description' => "ആദ്യം ലഭിച്ച {$oldPosLabel}-ൽ നിന്ന് {$newPosLabel}-ലേക്ക് മാറിയപ്പോൾ {$clawback} കോയിനുകൾ പിൻവലിച്ചു",
                    'date' => $dem->updated_at?->format('d M, h:i A'),
                ];
            }
        }

        $totalClawback = array_sum(array_column($adjustments, 'clawback_amount'));

        $spent = (int) abs(CoinTransaction::where('student_id', $student->id)
            ->where('transaction_type', 'debit')
            ->sum('coins'));

        // Has purchases and has clawbacks
        $hasClawback = ! empty($adjustments);
        $hasPurchaseWithClawback = ($spent > 0 && $hasClawback);

        return [
            'has_clawback' => $hasClawback,
            'has_purchase_with_clawback' => $hasPurchaseWithClawback,
            'total_clawback_coins' => $totalClawback,
            'total_spent' => $spent,
            'adjustments' => $adjustments,
        ];
    }

    /**
     * Get student achievements including grade breakdown and prize positions.
     *
     * @param  bool  $onlyMediaPublished  When true, only includes prizes/grades from results published by media desk.
     * @return array{
     *     grade_counts: array<string, int>,
     *     prizes: array<int, array{count: int, title: string, rank: string, message: string, programs: array}>
     * }
     */
    public function getStudentAchievements(Student $student, bool $onlyMediaPublished = false): array
    {
        CoinTransaction::ensureSchema();

        // Ensure all awarded competition results (individual and group) are distributed for this student
        $this->ensureCoinsDistributedForStudent($student, $onlyMediaPublished);

        $achievements = [
            'grade_counts' => [
                'A+' => 0,
                'A' => 0,
                'B' => 0,
                'C' => 0,
            ],
            'prizes' => [
                1 => [
                    'count' => 0,
                    'title' => 'First Prize',
                    'rank' => 'Pinnacle',
                    'message' => "You've secured the First Prize with excellence!",
                    'programs' => [],
                ],
                2 => [
                    'count' => 0,
                    'title' => 'Second Prize',
                    'rank' => 'Eminent',
                    'message' => 'Great achievement securing Second Prize!',
                    'programs' => [],
                ],
                3 => [
                    'count' => 0,
                    'title' => 'Third Prize',
                    'rank' => 'Competent',
                    'message' => 'Proud moment! You earned the Third Prize.',
                    'programs' => [],
                ],
            ],
        ];

        $processedPrograms = [];

        // 1. Scan completed positive coin transactions
        $creditsQuery = CoinTransaction::where('student_id', $student->id)
            ->where('transaction_type', 'credit')
            ->where('coins', '>', 0)
            ->with(['program.category', 'program.result', 'result']);

        if ($onlyMediaPublished) {
            $creditsQuery->where(function ($q) {
                $q->whereNull('result_id')
                    ->orWhereHas('result', fn ($rq) => $rq->where('status', 'published')->where('is_media_published', true));
            })->where(function ($q) {
                $q->whereNull('program_id')
                    ->orDoesntHave('program.result')
                    ->orWhereHas('program.result', fn ($rq) => $rq->where('status', 'published')->where('is_media_published', true));
            });
        }

        $credits = $creditsQuery->get();

        foreach ($credits as $credit) {
            $progId = $credit->program_id;
            $res = $credit->result ?? $credit->program?->result;
            $isPublicDeclared = ! $res || ($res->status === 'published' && (bool) $res->is_media_published);
            if ($onlyMediaPublished && ! $isPublicDeclared) {
                continue;
            }

            // Deduplication: prevent duplicate listing of the same program
            if ($progId) {
                if (isset($processedPrograms[$progId])) {
                    continue;
                }
                $processedPrograms[$progId] = true;
            }

            $pos = (int) $credit->prize_position;
            $grade = self::normalizeGrade($credit->grade);
            $progName = $credit->program?->name ?? ($credit->metadata['program_name'] ?? 'Competition');

            if (isset($achievements['prizes'][$pos])) {
                $achievements['prizes'][$pos]['count']++;
                $achievements['prizes'][$pos]['programs'][] = [
                    'name' => $progName,
                    'grade' => $grade,
                    'coins' => $credit->coins,
                ];
            }

            if ($grade && isset($achievements['grade_counts'][$grade])) {
                $achievements['grade_counts'][$grade]++;
            }
        }

        // 2. Scan verified program entries (individual and group) that may not yet have coin transactions
        $entriesQuery = ProgramEntry::where('student_id', $student->id);
        $groupEntriesQuery = $student->participations();

        if ($onlyMediaPublished) {
            $entriesQuery->whereHas('program.result', fn ($q) => $q->where('status', 'published')->where('is_media_published', true));
            $groupEntriesQuery->whereHas('program.result', fn ($q) => $q->where('status', 'published')->where('is_media_published', true));
        } else {
            $entriesQuery->whereHas('program.result', fn ($q) => $q->whereIn('status', self::DECLARED_STATUSES));
            $groupEntriesQuery->whereHas('program.result', fn ($q) => $q->whereIn('status', self::DECLARED_STATUSES));
        }

        $entries = $entriesQuery->with(['program.result', 'scoreSheets'])->get();
        $groupEntries = $groupEntriesQuery->with(['program.result', 'scoreSheets'])->get();

        $allEntries = $entries->merge($groupEntries)->unique('id');

        foreach ($allEntries as $entry) {
            $program = $entry->program;
            if (! $program || isset($processedPrograms[$program->id])) {
                continue;
            }
            $result = $program->result;
            if (! $result) {
                continue;
            }

            if ($onlyMediaPublished && (! $result->is_media_published || $result->status !== 'published')) {
                continue;
            }

            $pos = 0;
            $grade = null;

            if ($result->first_entry_id === $entry->id) {
                $pos = 1;
                $grade = self::normalizeGrade($result->first_grade);
            } elseif ($result->second_entry_id === $entry->id) {
                $pos = 2;
                $grade = self::normalizeGrade($result->second_grade);
            } elseif ($result->third_entry_id === $entry->id) {
                $pos = 3;
                $grade = self::normalizeGrade($result->third_grade);
            }

            if (! $grade && ! empty($entry->computed_grade)) {
                $grade = self::normalizeGrade($entry->computed_grade);
            }

            if (! $onlyMediaPublished && ! $grade) {
                $judgeGrade = $entry->scoreSheets->where('is_submitted', true)->pluck('criteria_scores.grade')->filter()->first();
                if ($judgeGrade) {
                    $grade = self::normalizeGrade($judgeGrade);
                }
            }

            $processedPrograms[$program->id] = true;

            if (isset($achievements['prizes'][$pos])) {
                $achievements['prizes'][$pos]['count']++;
                $achievements['prizes'][$pos]['programs'][] = [
                    'name' => $program->name,
                    'grade' => $grade,
                    'coins' => 0,
                ];
            }

            if ($grade && isset($achievements['grade_counts'][$grade])) {
                $achievements['grade_counts'][$grade]++;
            }
        }

        return $achievements;
    }

    /**
     * Record a coin redemption / purchase for a student at the Presentation Desk.
     *
     * @param array{
     *     item_name: string,
     *     coins: int,
     *     quantity?: int,
     *     item_category?: string,
     *     notes?: string|null,
     *     operator_id?: int|null,
     *     operator_name?: string|null
     * } $details
     *
     * @throws \InvalidArgumentException
     */
    public function redeemCoins(Student $student, array $details): CoinTransaction
    {
        CoinTransaction::ensureSchema();

        return DB::transaction(function () use ($student, $details) {
            $summary = $this->getStudentCoinSummary($student);
            $available = $summary['balance'];

            $quantity = max(1, (int) ($details['quantity'] ?? 1));
            $unitCoins = (int) $details['coins'];
            $totalCoins = $unitCoins * $quantity;

            if ($totalCoins <= 0) {
                throw new \InvalidArgumentException('Coin amount must be greater than zero.');
            }

            if ($available < $totalCoins) {
                throw new \InvalidArgumentException("Insufficient coins! Student has {$available} coins available, but purchase requires {$totalCoins} coins.");
            }

            $itemName = trim($details['item_name']);
            $category = $details['item_category'] ?? 'book';
            $notes = $details['notes'] ?? null;
            $opId = $details['operator_id'] ?? null;
            $opName = $details['operator_name'] ?? null;

            $ref = 'COIN-PURCH-STU'.$student->id.'-'.now()->format('YmdHis').'-'.strtoupper(Str::random(5));

            $transaction = CoinTransaction::create([
                'student_id' => $student->id,
                'group_id' => $student->group_id,
                'program_id' => null,
                'entry_id' => null,
                'result_id' => null,
                'prize_position' => 0,
                'grade' => null,
                'reward_rule_id' => 'QUAF09_PRESENTATION_PURCHASE_V1',
                'coins' => -$totalCoins,
                'transaction_type' => 'debit',
                'transaction_reference' => $ref,
                'status' => 'completed',
                'description' => "Purchase: {$itemName}".($quantity > 1 ? " (Qty: {$quantity})" : '')." [{$totalCoins} coins]",
                'metadata' => [
                    'item_name' => $itemName,
                    'item_category' => $category,
                    'quantity' => $quantity,
                    'coins_per_unit' => $unitCoins,
                    'total_coins' => $totalCoins,
                    'balance_before' => $available,
                    'balance_after' => $available - $totalCoins,
                    'notes' => $notes,
                    'operator_id' => $opId,
                    'operator_name' => $opName,
                ],
            ]);

            $this->syncStudentCoinsCache($student->id);

            return $transaction;
        });
    }

    /**
     * Record a multi-item cart redemption / purchase for a student at the Presentation Desk.
     *
     * @param  array<int, array{item_name: string, coins: int, quantity?: int, item_category?: string}>  $cartItems
     * @param  array{notes?: string|null, operator_id?: int|null, operator_name?: string|null}  $details
     *
     * @throws \InvalidArgumentException
     */
    public function redeemCart(Student $student, array $cartItems, array $details = []): CoinTransaction
    {
        CoinTransaction::ensureSchema();

        if (empty($cartItems)) {
            throw new \InvalidArgumentException('കാർട്ടിൽ ഇനങ്ങൾ ഒന്നും തിരഞ്ഞെടുത്തിട്ടില്ല (Cart is empty).');
        }

        return DB::transaction(function () use ($student, $cartItems, $details) {
            $summary = $this->getStudentCoinSummary($student);
            $available = $summary['balance'];

            $normalizedItems = [];
            $totalCoins = 0;
            $totalQuantity = 0;

            foreach ($cartItems as $item) {
                $name = trim($item['item_name'] ?? '');
                $coins = (int) ($item['coins'] ?? 0);
                $qty = max(1, (int) ($item['quantity'] ?? 1));
                $category = $item['item_category'] ?? ($item['category'] ?? 'book');

                if ($name === '' || $coins <= 0) {
                    continue;
                }

                $subtotal = $coins * $qty;
                $totalCoins += $subtotal;
                $totalQuantity += $qty;

                $normalizedItems[] = [
                    'item_name' => $name,
                    'coins' => $coins,
                    'quantity' => $qty,
                    'item_category' => $category,
                    'subtotal_coins' => $subtotal,
                ];
            }

            if (empty($normalizedItems) || $totalCoins <= 0) {
                throw new \InvalidArgumentException('സാധുവായ ഇനങ്ങളൊന്നും കാർട്ടിൽ കാണുന്നില്ല (No valid items found in cart).');
            }

            if ($available < $totalCoins) {
                throw new \InvalidArgumentException("കോയിൻ തികയുന്നില്ല! വിദ്യാർത്ഥിയുടെ കൈവശം {$available} കോയിനുകൾ ലഭ്യമാണ്. എന്നാൽ ഈ പർച്ചേസിന് {$totalCoins} കോയിനുകൾ ആവശ്യമാണ്.");
            }

            $opId = $details['operator_id'] ?? null;
            $opName = $details['operator_name'] ?? null;
            $notes = $details['notes'] ?? null;

            $itemsSummaryText = count($normalizedItems) === 1
                ? $normalizedItems[0]['item_name'].($normalizedItems[0]['quantity'] > 1 ? " (Qty: {$normalizedItems[0]['quantity']})" : '')
                : count($normalizedItems).' Items ('.implode(', ', array_slice(array_column($normalizedItems, 'item_name'), 0, 3)).(count($normalizedItems) > 3 ? '...' : '').')';

            $ref = 'COIN-CART-STU'.$student->id.'-'.now()->format('YmdHis').'-'.strtoupper(Str::random(5));

            $transaction = CoinTransaction::create([
                'student_id' => $student->id,
                'group_id' => $student->group_id,
                'program_id' => null,
                'entry_id' => null,
                'result_id' => null,
                'prize_position' => 0,
                'grade' => null,
                'reward_rule_id' => 'QUAF09_PRESENTATION_CART_PURCHASE_V1',
                'coins' => -$totalCoins,
                'cash_amount' => 0.00,
                'payment_mode' => 'coins',
                'source' => 'presentation_redemption',
                'transaction_type' => 'debit',
                'transaction_reference' => $ref,
                'status' => 'completed',
                'description' => "Cart Redemption: {$itemsSummaryText} [{$totalCoins} coins]",
                'metadata' => [
                    'is_cart' => true,
                    'cart_items' => $normalizedItems,
                    'items_count' => count($normalizedItems),
                    'total_quantity' => $totalQuantity,
                    'total_coins' => $totalCoins,
                    'balance_before' => $available,
                    'balance_after' => $available - $totalCoins,
                    'notes' => $notes,
                    'operator_id' => $opId,
                    'operator_name' => $opName,
                ],
            ]);

            $this->syncStudentCoinsCache($student->id);

            return $transaction;
        });
    }

    /**
     * Add coins to a student's balance when paying cash in hand at the Presentation Cashier desk.
     *
     * @param  array{payment_mode?: string, notes?: string|null, operator_id?: int|null, operator_name?: string|null}  $details
     *
     * @throws \InvalidArgumentException
     */
    public function addCashierCoins(Student $student, int $coins, float $cashAmount, array $details = []): CoinTransaction
    {
        CoinTransaction::ensureSchema();

        if ($coins <= 0) {
            throw new \InvalidArgumentException('ചേർക്കുന്ന കോയിൻ പൂജ്യത്തേക്കാൾ വലുതായിരിക്കണം (Coins must be greater than zero).');
        }

        if ($cashAmount < 0) {
            throw new \InvalidArgumentException('പണത്തിന്റെ തുക തെറ്റാണ് (Invalid cash amount).');
        }

        return DB::transaction(function () use ($student, $coins, $cashAmount, $details) {
            $summaryBefore = $this->getStudentCoinSummary($student);
            $balanceBefore = $summaryBefore['balance'];
            $addedBefore = $summaryBefore['added_coins'];

            $opId = $details['operator_id'] ?? null;
            $opName = $details['operator_name'] ?? 'Presentation Cashier';
            $notes = $details['notes'] ?? null;
            $paymentMode = $details['payment_mode'] ?? 'cash';
            $source = $details['source'] ?? 'cash_deposit';
            $rewardRuleId = $details['reward_rule_id'] ?? ($source === 'amount_received' ? 'QUAF09_CASHIER_AMOUNT_RECEIVED_V1' : 'QUAF09_CASHIER_COIN_DEPOSIT_V1');
            $desc = $details['description'] ?? ($source === 'amount_received' ? 'Amount Received (പണം ലഭിച്ചു): ₹'.number_format($cashAmount, 2)." (+{$coins} Coins)" : 'Added Coin (പണം നൽകി ചേർത്തത്): ₹'.number_format($cashAmount, 2)." (+{$coins} Coins)");

            $ref = 'COIN-CASH-STU'.$student->id.'-'.now()->format('YmdHis').'-'.strtoupper(Str::random(4));

            $transaction = CoinTransaction::create([
                'student_id' => $student->id,
                'group_id' => $student->group_id,
                'program_id' => null,
                'entry_id' => null,
                'result_id' => null,
                'prize_position' => 0,
                'grade' => null,
                'reward_rule_id' => $rewardRuleId,
                'coins' => $coins,
                'cash_amount' => $cashAmount,
                'payment_mode' => $paymentMode,
                'source' => $source,
                'transaction_type' => 'credit',
                'transaction_reference' => $ref,
                'status' => 'completed',
                'description' => $desc,
                'metadata' => [
                    'source_type' => $source,
                    'cash_amount' => $cashAmount,
                    'coins_added' => $coins,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceBefore + $coins,
                    'added_coins_before' => $addedBefore,
                    'added_coins_after' => $addedBefore + $coins,
                    'notes' => $notes,
                    'operator_id' => $opId,
                    'operator_name' => $opName,
                    'payment_mode' => $paymentMode,
                ],
            ]);

            $this->syncStudentCoinsCache($student->id);

            return $transaction;
        });
    }

    /**
     * Reverse an accidental coin purchase/redemption.
     */
    public function reverseRedemption(CoinTransaction $transaction, string $reason = 'Purchase cancelled', ?int $operatorId = null, ?string $operatorName = null): CoinTransaction
    {
        CoinTransaction::ensureSchema();

        if ($transaction->transaction_type !== 'debit') {
            throw new \InvalidArgumentException('Only debit/purchase transactions can be reversed through this method.');
        }

        return DB::transaction(function () use ($transaction, $reason, $operatorId, $operatorName) {
            $revRef = 'COIN-REV-PURCH-'.$transaction->id.'-'.now()->format('YmdHis');

            if (CoinTransaction::where('transaction_reference', $revRef)->exists()) {
                throw new \InvalidArgumentException('This purchase has already been reversed.');
            }

            $reversal = CoinTransaction::create([
                'student_id' => $transaction->student_id,
                'group_id' => $transaction->group_id,
                'program_id' => null,
                'entry_id' => null,
                'result_id' => null,
                'prize_position' => 0,
                'grade' => null,
                'reward_rule_id' => 'QUAF09_PRESENTATION_PURCHASE_REVERSAL_V1',
                'coins' => abs($transaction->coins),
                'transaction_type' => 'reversal',
                'transaction_reference' => $revRef,
                'status' => 'completed',
                'description' => "Cancelled Purchase: {$reason} (Ref: {$transaction->transaction_reference})",
                'metadata' => [
                    'original_transaction_id' => $transaction->id,
                    'original_reference' => $transaction->transaction_reference,
                    'reversal_reason' => $reason,
                    'operator_id' => $operatorId,
                    'operator_name' => $operatorName,
                ],
            ]);

            $this->syncStudentCoinsCache($transaction->student_id);

            return $reversal;
        });
    }
}
