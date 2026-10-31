<?php

namespace App\Services;

use App\Models\CoinTransaction;
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
     * Official QUAF Season 09 Group Program Reward Chart:
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
     * Calculate individual coins reward for a participant based on position and grade.
     */
    public static function calculateGroupRewardPerParticipant(int $position, ?string $grade): int
    {
        if (! isset(self::GROUP_REWARD_CHART[$position])) {
            return 0;
        }

        $chart = self::GROUP_REWARD_CHART[$position];
        $normalizedGrade = self::normalizeGrade($grade);

        if ($normalizedGrade !== null && isset($chart[$normalizedGrade])) {
            return $chart[$normalizedGrade];
        }

        return $chart['NO_GRADE'];
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
     * Enforces Green Room attendance rules and group verification.
     *
     * @return Collection<int, Student>
     */
    public static function getEligibleParticipantsForEntry(ProgramEntry $entry, bool $strictAttendance = true): Collection
    {
        if ($strictAttendance && $entry->attendance_status === 'absent') {
            return collect();
        }

        $participants = $entry->relationLoaded('participants')
            ? $entry->participants
            : $entry->participants()->get();

        if ($participants->isEmpty() && $entry->student) {
            $participants = collect([$entry->student]);
        }

        return $participants->unique('id')->values();
    }

    /**
     * Generate preview of coin distribution for a group program result prior to publishing.
     *
     * @return array<string, mixed>
     */
    public function previewGroupCoinDistribution(Result $result): array
    {
        CoinTransaction::ensureSchema();

        $program = $result->program ?? Program::find($result->program_id);
        $isGroup = $program ? $program->isGroup() : false;

        $placements = [];
        $totalParticipants = 0;
        $totalCoins = 0;
        $flags = [];

        $placementConfigs = [
            1 => ['entry' => $result->firstEntry ?? ProgramEntry::with(['group', 'student', 'participants'])->find($result->first_entry_id), 'label' => '1st Prize'],
            2 => ['entry' => $result->secondEntry ?? ProgramEntry::with(['group', 'student', 'participants'])->find($result->second_entry_id), 'label' => '2nd Prize'],
            3 => ['entry' => $result->thirdEntry ?? ProgramEntry::with(['group', 'student', 'participants'])->find($result->third_entry_id), 'label' => '3rd Prize'],
        ];

        foreach ($placementConfigs as $pos => $cfg) {
            /** @var ProgramEntry|null $entry */
            $entry = $cfg['entry'];
            if (! $entry) {
                continue;
            }

            $group = $entry->group;
            $grade = self::resolveGradeForEntry($entry, $result, $pos);
            $coinsPerParticipant = self::calculateGroupRewardPerParticipant($pos, $grade);
            $participants = self::getEligibleParticipantsForEntry($entry, false);
            $isAbsent = $entry->attendance_status === 'absent';

            $participantItems = [];
            foreach ($participants as $student) {
                $participantItems[] = [
                    'student_id' => $student->id,
                    'student_number' => $student->student_id ?? 'STU-'.$student->id,
                    'name' => $student->name,
                    'role' => $student->pivot?->role ?? ($isGroup ? 'participant' : 'individual'),
                    'coins' => $isAbsent ? 0 : $coinsPerParticipant,
                ];
            }

            $count = $isAbsent ? 0 : $participants->count();
            $subtotal = $count * $coinsPerParticipant;

            if ($participants->isEmpty()) {
                $identifier = $isGroup ? ($group?->name ?? 'Team') : ($entry->student?->name ?? 'Participant');
                $flags[] = "{$cfg['label']} ({$identifier}): No registered participant found.";
            }

            if ($isAbsent) {
                $identifier = $isGroup ? ($group?->name ?? 'Team') : ($entry->student?->name ?? 'Participant');
                $flags[] = "{$cfg['label']} ({$identifier}): Marked absent in Green Room attendance. Zero coins credited.";
            }

            $totalParticipants += $count;
            $totalCoins += $subtotal;

            $placements[] = [
                'position' => $pos,
                'position_label' => $cfg['label'],
                'entry_id' => $entry->id,
                'chest_number' => $entry->chest_number,
                'group_id' => $entry->group_id,
                'group_name' => $isGroup ? ($group?->name ?? 'Team') : ($entry->student?->name ?? $group?->name ?? 'Participant'),
                'group_code' => $group?->code ?? '',
                'attendance_status' => $entry->attendance_status,
                'is_absent' => $isAbsent,
                'grade' => $grade ?: 'No Grade',
                'coins_per_participant' => $coinsPerParticipant,
                'participants_count' => $count,
                'total_coins' => $subtotal,
                'participants' => $participantItems,
            ];
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
        if (! $program) {
            return [
                'credited_count' => 0,
                'total_coins' => 0,
                'skipped' => true,
            ];
        }

        return DB::transaction(function () use ($result, $program) {
            $placements = [
                1 => $result->firstEntry ?? ProgramEntry::with(['group', 'student', 'participants'])->find($result->first_entry_id),
                2 => $result->secondEntry ?? ProgramEntry::with(['group', 'student', 'participants'])->find($result->second_entry_id),
                3 => $result->thirdEntry ?? ProgramEntry::with(['group', 'student', 'participants'])->find($result->third_entry_id),
            ];

            $creditedCount = 0;
            $totalCoins = 0;
            $affectedStudentIds = [];
            $isGroup = $program->isGroup();

            foreach ($placements as $pos => $entry) {
                if (! $entry) {
                    continue;
                }

                if ($entry->attendance_status === 'absent') {
                    continue;
                }

                $grade = self::resolveGradeForEntry($entry, $result, $pos);
                $coins = self::calculateGroupRewardPerParticipant($pos, $grade);
                $participants = self::getEligibleParticipantsForEntry($entry, true);

                if ($participants->isEmpty() || $coins <= 0) {
                    continue;
                }

                $posLabel = match ($pos) {
                    1 => '1st Prize',
                    2 => '2nd Prize',
                    3 => '3rd Prize',
                    default => "Position {$pos}",
                };

                foreach ($participants as $student) {
                    $existing = CoinTransaction::where('result_id', $result->id)
                        ->where('student_id', $student->id)
                        ->where('transaction_type', 'credit')
                        ->first();

                    if ($existing) {
                        continue;
                    }

                    $ref = "COIN-RES{$result->id}-ENT{$entry->id}-STU{$student->id}-P{$pos}-CR";
                    $progType = $isGroup ? 'Group' : 'Individual';

                    CoinTransaction::create([
                        'student_id' => $student->id,
                        'group_id' => $entry->group_id ?: $student->group_id,
                        'program_id' => $program->id,
                        'entry_id' => $entry->id,
                        'result_id' => $result->id,
                        'prize_position' => $pos,
                        'grade' => $grade ?: 'NO_GRADE',
                        'reward_rule_id' => self::REWARD_RULE_ID,
                        'coins' => $coins,
                        'transaction_type' => 'credit',
                        'transaction_reference' => $ref,
                        'status' => 'completed',
                        'description' => "QUAF 09 {$progType} Reward: {$program->name} - {$posLabel} - Grade ".($grade ?: 'No Grade')." (+{$coins} coins)",
                        'metadata' => [
                            'program_name' => $program->name,
                            'program_type' => $program->type,
                            'group_name' => $entry->group?->name,
                            'student_number' => $student->student_id,
                            'student_name' => $student->name,
                            'prize_position' => $pos,
                            'grade' => $grade ?: 'NO_GRADE',
                            'participant_role' => $student->pivot?->role ?? ($isGroup ? 'participant' : 'individual'),
                        ],
                    ]);

                    $affectedStudentIds[] = $student->id;
                    $creditedCount++;
                    $totalCoins += $coins;
                }
            }

            if (! empty($affectedStudentIds)) {
                $this->syncStudentCoinsCache($affectedStudentIds);
            }

            return [
                'credited_count' => $creditedCount,
                'total_coins' => $totalCoins,
                'skipped' => false,
            ];
        });
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
        });
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
     * Get real-time coin ledger summary for a student.
     *
     * @param  bool  $onlyMediaPublished  When true, only includes coins from results published by media desk.
     * @return array{balance: int, total_earned: int, competition_earned: int, added_coins: int, total_spent: int}
     */
    public function getStudentCoinSummary(Student $student, bool $onlyMediaPublished = false): array
    {
        CoinTransaction::ensureSchema();

        // 1. Added coins purchased via Cashier cash deposit
        $addedQuery = CoinTransaction::where('student_id', $student->id)
            ->where('coins', '>', 0)
            ->where(function ($q) {
                $q->where('source', 'cash_deposit')
                    ->orWhere('reward_rule_id', 'QUAF09_CASHIER_COIN_DEPOSIT_V1');
            });
        $addedCoins = (int) $addedQuery->sum('coins');

        // 2. Competition earned coins from grades/prizes
        $competitionQuery = CoinTransaction::where('student_id', $student->id)
            ->where('coins', '>', 0)
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('source')
                        ->orWhere('source', '!=', 'cash_deposit');
                })->where('reward_rule_id', '!=', 'QUAF09_CASHIER_COIN_DEPOSIT_V1');
            });

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

        $balance = max(0, $earned - $spent);

        return [
            'balance' => $balance,
            'total_earned' => $earned,
            'competition_earned' => $competitionEarned,
            'added_coins' => $addedCoins,
            'total_spent' => $spent,
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
            if ($onlyMediaPublished && $res && (! $res->is_media_published || $res->status !== 'published')) {
                continue;
            }

            $pos = (int) $credit->prize_position;
            $grade = self::normalizeGrade($credit->grade);
            $progName = $credit->program?->name ?? ($credit->metadata['program_name'] ?? 'Competition');

            if ($progId) {
                $processedPrograms[$progId] = true;
            }

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
            $entriesQuery->whereHas('program.result', fn ($q) => $q->whereIn('status', ['verified', 'published']));
            $groupEntriesQuery->whereHas('program.result', fn ($q) => $q->whereIn('status', ['verified', 'published']));
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

            $ref = 'COIN-CASH-STU'.$student->id.'-'.now()->format('YmdHis').'-'.strtoupper(Str::random(4));

            $transaction = CoinTransaction::create([
                'student_id' => $student->id,
                'group_id' => $student->group_id,
                'program_id' => null,
                'entry_id' => null,
                'result_id' => null,
                'prize_position' => 0,
                'grade' => null,
                'reward_rule_id' => 'QUAF09_CASHIER_COIN_DEPOSIT_V1',
                'coins' => $coins,
                'cash_amount' => $cashAmount,
                'payment_mode' => $paymentMode,
                'source' => 'cash_deposit',
                'transaction_type' => 'credit',
                'transaction_reference' => $ref,
                'status' => 'completed',
                'description' => 'Added Coin (പണം നൽകി ചേർത്തത്): ₹'.number_format($cashAmount, 2)." (+{$coins} Coins)",
                'metadata' => [
                    'source_type' => 'cash_deposit',
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
