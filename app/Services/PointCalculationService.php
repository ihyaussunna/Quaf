<?php

namespace App\Services;

use App\Models\Group;
use App\Models\PointSetting;
use App\Models\PointsTransaction;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PointCalculationService
{
    /**
     * Handbook official position points for individual programs:
     * 1st Place = 5 points
     * 2nd Place = 3 points
     * 3rd Place = 1 point
     */
    public const POSITION_POINTS = [
        1 => 5,
        2 => 3,
        3 => 1,
    ];

    /**
     * Declared result statuses that count towards official team points and merit awards.
     */
    public const DECLARED_STATUSES = ['send', 'delivered', 'announced', 'published'];

    /**
     * Handbook official grade points (Rule 11):
     * A+ = 90% - 100% -> 6 points
     * A  = 70% - 89%  -> 5 points
     * B  = 60% - 69%  -> 3 points
     * C  = 50% - 59%  -> 1 point
     * (Below 50% = 0 points / No Grade)
     */
    public const GRADE_POINTS = [
        'A+' => 6,
        'A' => 5,
        'B' => 3,
        'C' => 1,
    ];

    /**
     * Calculate grade name and points from mark (0-100 scale).
     * Official Rulebook:
     * A+ : 90% - 100% -> 6 points
     * A  : 70% - 89%  -> 5 points
     * B  : 60% - 69%  -> 3 points
     * C  : 50% - 59%  -> 1 point
     * Below 50%       -> No grade (0 points)
     *
     * @return array{grade: ?string, points: int}
     */
    public static function getGradeFromScore(float $score): array
    {
        if ($score >= 90.0) {
            return ['grade' => 'A+', 'points' => 6];
        }
        if ($score >= 70.0) {
            return ['grade' => 'A', 'points' => 5];
        }
        if ($score >= 60.0) {
            return ['grade' => 'B', 'points' => 3];
        }
        if ($score >= 50.0) {
            return ['grade' => 'C', 'points' => 1];
        }

        return ['grade' => null, 'points' => 0];
    }

    /**
     * Get position points (1st, 2nd, 3rd) strictly based on Rule 11 (പോയിന്റ്സ് & ഗ്രേഡ്സ്):
     *
     * 1. Individual Competitions:
     *    1st = 5, 2nd = 3, 3rd = 1
     *
     * 2. Group Competitions:
     *    - 2 members: 1st = 7, 2nd = 5, 3rd = 3
     *    - 3 members: 1st = 10, 2nd = 7, 3rd = 4
     *    - 4, 5 members: 1st = 15, 2nd = 10, 3rd = 5
     *    - ഇൻസെറ്റ് / ഇൻസൈറ്റ് (Inset/Insight), സെന്റിനറി ഫുട്പ്രിന്റ് (Centenary Footprint), General Shifting (>5 members):
     *      1st = 20, 2nd = 15, 3rd = 10
     *
     * @return array<int, int> [1 => 1st_pts, 2 => 2nd_pts, 3 => 3rd_pts]
     */
    public static function getPositionPointsForProgram(Program $program): array
    {
        // 1. Check for Special High-Value Group Competitions:
        // Rule 11: ഇൻസെറ്റ് / ഇൻസൈറ്റ് (Insight/Inset), സെന്റിനറി ഫുട്പ്രിന്റ് (Centenary Footprint), General Shifting
        $nameLower = strtolower($program->name.' '.($program->malayalam_name ?? '').' '.($program->rules ?? ''));
        $isSpecialHighGroup = str_contains($nameLower, 'inset')
            || str_contains($nameLower, 'insight')
            || str_contains($nameLower, 'ഇൻസെറ്റ്')
            || str_contains($nameLower, 'ഇൻസൈറ്റ്')
            || str_contains($nameLower, 'centenary')
            || str_contains($nameLower, 'footprint')
            || str_contains($nameLower, 'സെന്റിനറി')
            || str_contains($nameLower, 'ഫുട്പ്രിന്റ്')
            || str_contains($nameLower, 'shift')
            || str_contains($nameLower, 'general')
            || str_contains($nameLower, 'ഷിഫ്റ്റ്')
            || str_contains($nameLower, 'ജനറൽ');

        if ($isSpecialHighGroup) {
            return [
                1 => 20,
                2 => 15,
                3 => 10,
            ];
        }

        // 2. Individual Competitions (Rule 11: 1st = 5, 2nd = 3, 3rd = 1)
        if ($program->isIndividual()) {
            return [
                1 => 5,
                2 => 3,
                3 => 1,
            ];
        }

        // 3. Group Competitions based on member count
        $members = max(1, (int) ($program->participant_count ?: $program->max_participants_per_group ?: 2));

        if ($members > 5) {
            return [
                1 => 20,
                2 => 15,
                3 => 10,
            ];
        }

        if ($members >= 4) { // 4 or 5 members
            return [
                1 => 15,
                2 => 10,
                3 => 5,
            ];
        }

        if ($members === 3) {
            return [
                1 => 10,
                2 => 7,
                3 => 4,
            ];
        }

        // 2 members (or 1-2 members group)
        return [
            1 => 7,
            2 => 5,
            3 => 3,
        ];
    }

    /**
     * Automatically determine 1st, 2nd, and 3rd place entries from judge marks.
     * Supports dense ranking for ties (same scores receive the same rank).
     * Only 'present' entries with submitted judge scores are considered.
     *
     * @return array{
     *     first: ?ProgramEntry,
     *     second: ?ProgramEntry,
     *     third: ?ProgramEntry,
     *     first_all: Collection<int, ProgramEntry>,
     *     second_all: Collection<int, ProgramEntry>,
     *     third_all: Collection<int, ProgramEntry>,
     *     has_ties: bool,
     *     ranked: Collection<int, ProgramEntry>
     * }
     */
    public static function determinePodiumForProgram(Program $program): array
    {
        $entries = $program->entries()
            ->where('attendance_status', 'present')
            ->whereIn('status', ['verified', 'confirmed'])
            ->with(['scores', 'student.group', 'group'])
            ->get()
            ->map(function ($entry) {
                $submittedSheets = $entry->scores->where('is_submitted', true);
                $avgScore = $submittedSheets->isNotEmpty()
                    ? (float) $submittedSheets->avg('total_score')
                    : 0.0;

                $entry->computed_avg_score = round($avgScore, 2);
                $gradeInfo = self::getGradeFromScore($entry->computed_avg_score);
                $entry->computed_grade = $gradeInfo['grade'];
                $entry->computed_grade_points = $gradeInfo['points'];

                return $entry;
            })
            ->filter(fn ($entry) => $entry->computed_avg_score > 0)
            ->sortByDesc('computed_avg_score')
            ->values();

        $uniqueScores = $entries->pluck('computed_avg_score')
            ->map(fn ($s) => round((float) $s, 2))
            ->unique()
            ->sortDesc()
            ->values();

        $s1 = $uniqueScores->get(0);
        $s2 = $uniqueScores->get(1);
        $s3 = $uniqueScores->get(2);

        $firstAll = $s1 !== null ? $entries->filter(fn ($e) => abs(round((float) $e->computed_avg_score, 2) - $s1) < 0.001)->values() : collect();
        $secondAll = $s2 !== null ? $entries->filter(fn ($e) => abs(round((float) $e->computed_avg_score, 2) - $s2) < 0.001)->values() : collect();
        $thirdAll = $s3 !== null ? $entries->filter(fn ($e) => abs(round((float) $e->computed_avg_score, 2) - $s3) < 0.001)->values() : collect();

        foreach ($entries as $entry) {
            $score = round((float) $entry->computed_avg_score, 2);
            if ($s1 !== null && abs($score - $s1) < 0.001) {
                $entry->rank = 1;
                $entry->computed_rank = 'first';
                $entry->is_tied = $firstAll->count() > 1;
            } elseif ($s2 !== null && abs($score - $s2) < 0.001) {
                $entry->rank = 2;
                $entry->computed_rank = 'second';
                $entry->is_tied = $secondAll->count() > 1;
            } elseif ($s3 !== null && abs($score - $s3) < 0.001) {
                $entry->rank = 3;
                $entry->computed_rank = 'third';
                $entry->is_tied = $thirdAll->count() > 1;
            } else {
                $entry->rank = null;
                $entry->computed_rank = null;
                $entry->is_tied = false;
            }
        }

        $hasTies = ($firstAll->count() > 1) || ($secondAll->count() > 1) || ($thirdAll->count() > 1);

        return [
            'first' => $firstAll->first(),
            'second' => $secondAll->first(),
            'third' => $thirdAll->first(),
            'first_all' => $firstAll,
            'second_all' => $secondAll,
            'third_all' => $thirdAll,
            'has_ties' => $hasTies,
            'ranked' => $entries,
        ];
    }

    /**
     * Auto-assign and save podium winners from judge scores to the Program's Result.
     */
    public static function autoAssignResultPodium(Program $program, ?string $status = null): ?Result
    {
        $podium = self::determinePodiumForProgram($program);
        if (! $podium['first']) {
            return null;
        }

        $result = $program->result ?? new Result(['program_id' => $program->id]);

        $result->first_entry_id = $podium['first']->id;
        $result->second_entry_id = $podium['second']?->id;
        $result->third_entry_id = $podium['third']?->id;

        if ($status) {
            $result->status = $status;
        } elseif (! $result->status) {
            $result->status = 'draft';
        }

        $result->save();

        if (in_array($result->status, self::DECLARED_STATUSES)) {
            app(self::class)->recalculateAllPoints();
            if (class_exists(CoinRewardService::class)) {
                $coinService = app(CoinRewardService::class);
                $coinService->reverseCoinDistributionForResult($result, 'Podium or marks updated');
                $coinService->distributeCoinsForResult($result);
            }
            Group::checkAndApplyMilestone();
        }

        return $result;
    }

    /**
     * Static shortcut to recalculate all group and student points.
     */
    public static function recalculateAll(): void
    {
        app(self::class)->recalculateAllPoints();
    }

    /**
     * Recalculate all group and student points, log auditable transactions, and update rankings.
     * High-speed optimized with batch inserts and targeted cache invalidation.
     */
    public function recalculateAllPoints(): void
    {
        DB::transaction(function () {
            // 1. Wipe existing transaction log for full idempotency (use delete() to prevent MySQL implicit commit)
            PointsTransaction::query()->delete();

            // 2. Reset points cache
            Student::query()->update(['points_cache' => 0]);
            Group::query()->update(['points_cache' => 0, 'rank_cache' => 0]);

            // 3. Fetch all declared results with their programs and entries
            $results = Result::whereIn('status', self::DECLARED_STATUSES)
                ->with([
                    'program.zone',
                    'firstEntry.group',
                    'firstEntry.student',
                    'firstEntry.scoreSheets',
                    'secondEntry.group',
                    'secondEntry.student',
                    'secondEntry.scoreSheets',
                    'thirdEntry.group',
                    'thirdEntry.student',
                    'thirdEntry.scoreSheets',
                ])
                ->get();

            $allTransactions = [];

            foreach ($results as $result) {
                $program = $result->program;
                if (! $program) {
                    continue;
                }

                $txs = $this->buildTransactionsForResult($result, $program);
                foreach ($txs as $t) {
                    $allTransactions[] = $t;
                }
            }

            // Batch insert in chunks of 100 for maximum database efficiency
            if (! empty($allTransactions)) {
                foreach (array_chunk($allTransactions, 100) as $chunk) {
                    PointsTransaction::insert($chunk);
                }
            }

            // 4. Update Students & Groups points_cache & rankings
            $this->syncPointsCache();
        });

        // 5. Synchronize and distribute coins for all declared results
        if (class_exists(CoinRewardService::class)) {
            app(CoinRewardService::class)->distributeMissingCoinsForAllDeclaredResults();
        }
    }

    /**
     * Incremental calculation for a single program without recalculating the entire fest database.
     */
    public function recalculateForProgram(Program|int $program): void
    {
        $programId = $program instanceof Program ? $program->id : $program;

        DB::transaction(function () use ($programId) {
            $program = Program::find($programId);
            if (! $program) {
                return;
            }

            // Delete old transactions for this program
            PointsTransaction::where('program_id', $programId)->delete();

            $result = Result::where('program_id', $programId)
                ->whereIn('status', self::DECLARED_STATUSES)
                ->with([
                    'program.zone',
                    'firstEntry.group',
                    'firstEntry.student',
                    'firstEntry.scoreSheets',
                    'secondEntry.group',
                    'secondEntry.student',
                    'secondEntry.scoreSheets',
                    'thirdEntry.group',
                    'thirdEntry.student',
                    'thirdEntry.scoreSheets',
                ])
                ->first();

            if ($result) {
                $txs = $this->buildTransactionsForResult($result, $program);
                if (! empty($txs)) {
                    foreach (array_chunk($txs, 100) as $chunk) {
                        PointsTransaction::insert($chunk);
                    }
                }

                if (class_exists(CoinRewardService::class)) {
                    app(CoinRewardService::class)->distributeCoinsForResult($result);
                }
            }

            $this->syncPointsCache();
        });
    }

    /**
     * Build transactional point items for a single published result.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildTransactionsForResult(Result $result, Program $program): array
    {
        $transactions = [];
        $weight = (float) ($program->points_weight ?? 1.0);
        $isGroupProg = $program->isGroup();
        $now = now();

        $posPointsMap = self::getPositionPointsForProgram($program);
        $podiumData = $result->getPodiumWinnersWithTies();
        $placedEntryIds = [];

        foreach ([1, 2, 3] as $pos) {
            $posLabel = match ($pos) {
                1 => '1st Place',
                2 => '2nd Place',
                3 => '3rd Place',
            };
            $posPts = (int) round(($posPointsMap[$pos] ?? 0) * $weight);

            foreach ($podiumData[$pos] as $winner) {
                $entry = $winner['entry'];
                if (! $entry || ! $entry->group_id) {
                    continue;
                }

                $placedEntryIds[] = $entry->id;
                $group = $entry->group;
                $student = $entry->student;
                $isTiedLabel = ! empty($winner['is_tied']) ? ' (Shared)' : '';

                // A. Position Points (Awarded to Group)
                if ($posPts > 0) {
                    $transactions[] = [
                        'group_id' => $group->id,
                        'program_id' => $program->id,
                        'result_id' => $result->id,
                        'entry_id' => $entry->id,
                        'student_id' => (! $isGroupProg && $student) ? $student->id : null,
                        'source_type' => 'POSITION',
                        'points' => $posPts,
                        'description' => "{$group->name} — {$program->name} — {$posLabel}{$isTiedLabel} (+{$posPts} pts)",
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // B. Grade Points from Result declaration or ScoreSheets
                $gradeName = $winner['grade'];
                $gradePointsRaw = ($gradeName && isset(self::GRADE_POINTS[$gradeName]))
                    ? self::GRADE_POINTS[$gradeName]
                    : 0;

                if ($gradeName && $gradePointsRaw > 0) {
                    $gradePts = (int) round($gradePointsRaw * $weight);
                    if ($gradePts > 0) {
                        $transactions[] = [
                            'group_id' => $group->id,
                            'program_id' => $program->id,
                            'result_id' => $result->id,
                            'entry_id' => $entry->id,
                            'student_id' => (! $isGroupProg && $student) ? $student->id : null,
                            'source_type' => 'GRADE',
                            'points' => $gradePts,
                            'description' => "{$group->name} — {$program->name} — Grade {$gradeName} (+{$gradePts} pts)",
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }
        }

        // C. Participation Points for non-placed verified entries
        $placedEntryIds = array_unique(array_filter(array_merge($placedEntryIds, [
            $result->first_entry_id,
            $result->second_entry_id,
            $result->third_entry_id,
        ])));

        $otherEntries = $program->entries()
            ->whereIn('status', ['verified', 'confirmed'])
            ->whereNotIn('id', $placedEntryIds)
            ->with(['group', 'student', 'scoreSheets'])
            ->get();

        $baseParticipation = (int) (PointSetting::value('participation_points') ?? 0);
        $partPts = (int) round($baseParticipation * $weight);

        foreach ($otherEntries as $entry) {
            if (! $entry->group_id) {
                continue;
            }

            $group = $entry->group;
            $student = $entry->student;

            // Non-placed entries can still earn Grade Points if their mark qualifies!
            $avgScore = $entry->scoreSheets->where('is_submitted', true)->avg('total_score');
            if ($avgScore !== null && $avgScore > 0) {
                $gradeInfo = self::getGradeFromScore((float) $avgScore);
                $gradePts = (int) round($gradeInfo['points'] * $weight);

                if ($gradeInfo['grade'] && $gradePts > 0) {
                    $transactions[] = [
                        'group_id' => $group->id,
                        'program_id' => $program->id,
                        'result_id' => $result->id,
                        'entry_id' => $entry->id,
                        'student_id' => (! $isGroupProg && $student) ? $student->id : null,
                        'source_type' => 'GRADE',
                        'points' => $gradePts,
                        'description' => "{$group->name} — {$program->name} — Grade {$gradeInfo['grade']} ({$avgScore} marks) (+{$gradePts} pts)",
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            } elseif ($partPts > 0) {
                // Base participation point
                $transactions[] = [
                    'group_id' => $group->id,
                    'program_id' => $program->id,
                    'result_id' => $result->id,
                    'entry_id' => $entry->id,
                    'student_id' => (! $isGroupProg && $student) ? $student->id : null,
                    'source_type' => 'PARTICIPATION',
                    'points' => $partPts,
                    'description' => "{$group->name} — {$program->name} — Participation (+{$partPts} pt)",
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        return $transactions;
    }

    /**
     * Synchronize points_cache on students and groups, update ranks, and bust cache.
     */
    public function syncPointsCache(): void
    {
        // 1. Reset and recalculate student points_cache
        Student::query()->update(['points_cache' => 0]);

        $studentTotals = PointsTransaction::whereNotNull('student_id')
            ->select('student_id', DB::raw('SUM(points) as total_points'))
            ->groupBy('student_id')
            ->pluck('total_points', 'student_id');

        foreach ($studentTotals as $studentId => $pts) {
            Student::where('id', $studentId)->update(['points_cache' => (int) $pts]);
        }

        // 2. Update Groups points_cache
        $groupTotals = PointsTransaction::select('group_id', DB::raw('SUM(points) as total_points'))
            ->groupBy('group_id')
            ->pluck('total_points', 'group_id');

        foreach (Group::all() as $grp) {
            $grp->update(['points_cache' => (int) ($groupTotals[$grp->id] ?? 0)]);
        }

        // 3. Update Group Rankings based on points_cache
        $rankedGroups = Group::orderByDesc('points_cache')->get();
        $rank = 1;
        foreach ($rankedGroups as $g) {
            $g->update(['rank_cache' => $rank]);
            $rank++;
        }

        // 4. Invalidate related caches
        Cache::forget('admin_dashboard_stats');
        Cache::forget('admin_leaderboard');
        Cache::forget('public_leaderboard');
        Cache::forget('public_results_summary');
        Cache::forget('chart_performance_data_cached');
        Cache::forget('public_home_data');
        Cache::forget('public_groups_list');
        Cache::forget('public_results_categories');
        Cache::forget('public_results_groups');
        Cache::forget('public_results_stages');
    }

    /**
     * Generate dynamic, real-time data for the "Performance Over Time" chart.
     * Matches the actual declared results and points_cache identically.
     *
     * @return array{
     *     ySteps: array<int, array{val: int, y: int}>,
     *     xSteps: array<int, array{label: string, sub: string, x: float, result_index: int}>,
     *     series: array<int, array{
     *         group_id: int,
     *         name: string,
     *         code: string,
     *         color: string,
     *         final_points: int,
     *         polyline_points: string,
     *         coords: array<int, array{x: float, y: float, pts: int}>
     *     }>,
     *     declaredCount: int,
     *     totalPrograms: int,
     *     maxScore: int,
     *     yMax: int
     * }
     */
    public function getPerformanceChartData(): array
    {
        $groups = Group::orderBy('rank_cache')->orderByDesc('points_cache')->get();
        if ($groups->isEmpty()) {
            $groups = Group::all();
        }

        $totalPrograms = Cache::remember('chart_total_programs_cached', 60, fn () => Program::count());
        $totalPrograms = max(1, (int) $totalPrograms);

        $results = Result::whereIn('status', self::DECLARED_STATUSES)
            ->orderBy('published_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $declaredCount = $results->count();

        // 1. Fetch transactions by result and group
        $transactions = PointsTransaction::whereNotNull('result_id')
            ->whereIn('result_id', $results->pluck('id'))
            ->select('result_id', 'group_id', DB::raw('SUM(points) as pts'))
            ->groupBy('result_id', 'group_id')
            ->get();

        // If results exist but transaction log is empty, recalculate
        if ($declaredCount > 0 && $transactions->isEmpty()) {
            $this->recalculateAllPoints();
            $transactions = PointsTransaction::whereNotNull('result_id')
                ->whereIn('result_id', $results->pluck('id'))
                ->select('result_id', 'group_id', DB::raw('SUM(points) as pts'))
                ->groupBy('result_id', 'group_id')
                ->get();
        }

        $ptsByResultAndGroup = [];
        foreach ($transactions as $t) {
            $ptsByResultAndGroup[$t->result_id][$t->group_id] = (int) $t->pts;
        }

        // 2. Determine Y-Axis Scale
        $highestPoint = (int) ($groups->max('points_cache') ?? 0);
        if ($highestPoint <= 25) {
            $yMax = 50;
            $yStep = 10;
        } elseif ($highestPoint <= 50) {
            $yMax = 50;
            $yStep = 10;
        } elseif ($highestPoint <= 100) {
            $yMax = 100;
            $yStep = 20;
        } elseif ($highestPoint <= 200) {
            $yMax = 200;
            $yStep = 40;
        } elseif ($highestPoint <= 300) {
            $yMax = 300;
            $yStep = 60;
        } elseif ($highestPoint <= 500) {
            $yMax = 500;
            $yStep = 100;
        } elseif ($highestPoint <= 750) {
            $yMax = 750;
            $yStep = 150;
        } elseif ($highestPoint <= 1000) {
            $yMax = 1000;
            $yStep = 200;
        } else {
            $step = (int) ceil($highestPoint / 5 / 50) * 50;
            $yMax = $step * 5;
            $yStep = $step;
        }

        $ySteps = [];
        for ($i = 0; $i <= 5; $i++) {
            $val = $yMax - ($i * $yStep);
            $yPos = 40 + ($i * 50); // 40, 90, 140, 190, 240, 290
            $ySteps[] = [
                'val' => $val,
                'y' => $yPos,
            ];
        }

        // 3. Determine Checkpoints for X-Axis
        $checkpoints = [];
        if ($declaredCount === 0) {
            $checkpoints = [
                ['label' => 'Start', 'sub' => '0%', 'result_index' => 0],
                ['label' => 'Progress', 'sub' => '0%', 'result_index' => 0],
            ];
        } elseif ($declaredCount <= 7) {
            $checkpoints[] = ['label' => 'Start', 'sub' => '0%', 'result_index' => 0];
            for ($k = 1; $k <= $declaredCount; $k++) {
                $isLast = ($k === $declaredCount);
                $pct = round(($k / $totalPrograms) * 100, 1);
                $checkpoints[] = [
                    'label' => $isLast ? "Result {$k} (Now)" : "Result {$k}",
                    'sub' => "{$pct}%",
                    'result_index' => $k,
                ];
            }
        } else {
            $checkpoints[] = ['label' => 'Start', 'sub' => '0%', 'result_index' => 0];
            $sampleCount = 5;
            for ($s = 1; $s <= $sampleCount; $s++) {
                $rIdx = (int) round(($s / ($sampleCount + 1)) * $declaredCount);
                $rIdx = max(1, min($declaredCount - 1, $rIdx));
                $pct = round(($rIdx / $totalPrograms) * 100, 1);
                $checkpoints[] = [
                    'label' => "R {$rIdx}",
                    'sub' => "{$pct}%",
                    'result_index' => $rIdx,
                ];
            }
            $finalPct = round(($declaredCount / $totalPrograms) * 100, 1);
            $checkpoints[] = [
                'label' => "R {$declaredCount} (Now)",
                'sub' => "{$finalPct}%",
                'result_index' => $declaredCount,
            ];
        }

        $numCheckpoints = count($checkpoints);
        $xStart = 60.0;
        $xEnd = 710.0;
        $xSteps = [];

        foreach ($checkpoints as $cIdx => $cp) {
            $xPos = $numCheckpoints > 1
                ? $xStart + (($cIdx / ($numCheckpoints - 1)) * ($xEnd - $xStart))
                : $xStart;

            $xSteps[] = [
                'label' => $cp['label'],
                'sub' => $cp['sub'],
                'x' => round($xPos, 1),
                'result_index' => $cp['result_index'] ?? 0,
            ];
        }

        // 4. Compute Cumulative Points across Results
        $cumulativePerResult = [];
        $running = [];
        foreach ($groups as $g) {
            $running[$g->id] = 0;
        }

        $resArray = $results->values()->all();
        for ($r = 0; $r < $declaredCount; $r++) {
            $rId = $resArray[$r]->id;
            foreach ($groups as $g) {
                $ptsThis = $ptsByResultAndGroup[$rId][$g->id] ?? 0;
                $running[$g->id] += $ptsThis;
                $cumulativePerResult[$r + 1][$g->id] = $running[$g->id];
            }
        }

        // 5. Build SVG Series for each group
        $brandColors = [
            'PACTO' => '#2E3192',
            'YUGO' => '#AD1E56',
            'CONCO' => '#F8E709',
            'LUMO' => '#56286B',
            'UNIO' => '#7F1518',
        ];

        $series = [];
        $chartAreaHeight = 250.0; // from y=40 to y=290
        $yBase = 290.0;

        foreach ($groups as $g) {
            $codeUpper = strtoupper(trim((string) $g->code));
            $color = $brandColors[$codeUpper] ?? $g->color_hex ?? '#2E3192';
            $finalCache = (int) $g->points_cache;

            $coords = [];
            $ptsStrings = [];

            foreach ($xSteps as $stepIdx => $xStep) {
                $rIdx = $xStep['result_index'] ?? 0;
                $x = $xStep['x'];

                if ($rIdx === 0) {
                    $scoreAtStep = 0;
                } elseif ($rIdx === $declaredCount || $stepIdx === $numCheckpoints - 1) {
                    // Always anchor the final checkpoint to points_cache for 100% scoreboard consistency
                    $scoreAtStep = $finalCache;
                } else {
                    $scoreAtStep = $cumulativePerResult[$rIdx][$g->id] ?? 0;
                }

                $fraction = $yMax > 0 ? min(1.0, max(0.0, $scoreAtStep / $yMax)) : 0.0;
                $y = round($yBase - ($fraction * $chartAreaHeight), 1);

                $coords[] = [
                    'x' => $x,
                    'y' => $y,
                    'pts' => $scoreAtStep,
                ];
                $ptsStrings[] = "{$x},{$y}";
            }

            $series[] = [
                'group_id' => $g->id,
                'name' => $g->name,
                'code' => $g->code,
                'color' => $color,
                'final_points' => $finalCache,
                'polyline_points' => implode(' ', $ptsStrings),
                'coords' => $coords,
            ];
        }

        return [
            'ySteps' => $ySteps,
            'xSteps' => $xSteps,
            'series' => $series,
            'declaredCount' => $declaredCount,
            'totalPrograms' => $totalPrograms,
            'maxScore' => $highestPoint,
            'yMax' => $yMax,
        ];
    }

    /**
     * Convert any string (names, zones, programs, groups) to clean, professional Title Case.
     */
    public static function toTitleCase(?string $string): string
    {
        if (empty($string)) {
            return '';
        }

        $string = trim($string);
        $words = preg_split('/\s+/', strtolower($string));
        $capitalized = array_map(function ($w) {
            if ($w === '') {
                return '';
            }
            if (str_contains($w, '-')) {
                return implode('-', array_map('ucfirst', explode('-', $w)));
            }
            if (str_contains($w, '/')) {
                return implode('/', array_map('ucfirst', explode('/', $w)));
            }
            if (str_contains($w, '.')) {
                return implode('.', array_map('ucfirst', explode('.', $w)));
            }
            if (in_array(strtolower($w), ['and', '&'])) {
                return '&';
            }

            return ucfirst($w);
        }, $words);

        return implode(' ', array_filter($capitalized, fn ($w) => $w !== ''));
    }

    /**
     * Calculate comprehensive points breakdown for a result, including position points,
     * grade points (from declared grade, judge scores, or criteria grades), and non-placed entries.
     *
     * @return array{
     *     points_by_group: array<int, int>,
     *     position_points_by_group: array<int, int>,
     *     grade_points_by_group: array<int, int>,
     *     winner_details: array<int, array<int, array<string, mixed>>>,
     *     first_group: ?Group,
     *     second_group: ?Group,
     *     third_group: ?Group,
     *     top_group: ?Group,
     *     max_points: int
     * }
     */
    public static function calculatePointsBreakdownForResult(Result $result): array
    {
        $program = $result->relationLoaded('program')
            ? $result->program
            : ($result->program_id ? Program::find($result->program_id) : null);

        if (! $program) {
            return [
                'points_by_group' => [],
                'position_points_by_group' => [],
                'grade_points_by_group' => [],
                'winner_details' => [1 => [], 2 => [], 3 => []],
                'first_group' => $result->firstEntry?->group,
                'second_group' => $result->secondEntry?->group,
                'third_group' => $result->thirdEntry?->group,
                'top_group' => null,
                'max_points' => 0,
            ];
        }

        $calcService = app(self::class);
        $txs = $calcService->buildTransactionsForResult($result, $program);

        $pointsByGroup = [];
        $posPointsByGroup = [];
        $gradePointsByGroup = [];
        $groupsMap = [];

        foreach ($txs as $t) {
            $gid = $t['group_id'] ?? null;
            if (! $gid) {
                continue;
            }

            $pts = (int) ($t['points'] ?? 0);
            $pointsByGroup[$gid] = ($pointsByGroup[$gid] ?? 0) + $pts;

            if (($t['source_type'] ?? '') === 'POSITION') {
                $posPointsByGroup[$gid] = ($posPointsByGroup[$gid] ?? 0) + $pts;
            } elseif (($t['source_type'] ?? '') === 'GRADE') {
                $gradePointsByGroup[$gid] = ($gradePointsByGroup[$gid] ?? 0) + $pts;
            }
        }

        $podiumData = $result->getPodiumWinnersWithTies();
        $winnerDetails = [1 => [], 2 => [], 3 => []];

        foreach ([1, 2, 3] as $pos) {
            foreach ($podiumData[$pos] as $w) {
                if (! empty($w['entry']) && $w['entry']->group) {
                    $groupsMap[$w['entry']->group_id] = $w['entry']->group;
                }
                $winnerDetails[$pos][] = [
                    'name' => self::toTitleCase($w['name'] ?? ''),
                    'team' => self::toTitleCase($w['team'] ?? ''),
                    'group_id' => $w['entry']?->group_id ?? null,
                    'grade' => $w['grade'] ?? null,
                    'grade_points' => $w['grade_points'] ?? 0,
                    'position_points' => $w['position_points'] ?? 0,
                    'total_points' => $w['total_points'] ?? 0,
                ];
            }
        }

        $topGroupId = null;
        $maxPoints = 0;
        foreach ($pointsByGroup as $gId => $pts) {
            if ($pts > $maxPoints) {
                $maxPoints = $pts;
                $topGroupId = $gId;
            }
        }

        $topGroup = null;
        if ($topGroupId) {
            $topGroup = $groupsMap[$topGroupId] ?? Group::find($topGroupId);
        }

        return [
            'points_by_group' => $pointsByGroup,
            'position_points_by_group' => $posPointsByGroup,
            'grade_points_by_group' => $gradePointsByGroup,
            'winner_details' => $winnerDetails,
            'first_group' => $result->firstEntry?->group,
            'second_group' => $result->secondEntry?->group,
            'third_group' => $result->thirdEntry?->group,
            'top_group' => $topGroup,
            'max_points' => $maxPoints,
        ];
    }

    /**
     * Calculate team standings after a specific milestone count of declared results.
     *
     * @param  int  $limit  Number of declared results to consider (0 for all declared)
     * @return array{
     *     limit: int,
     *     total_declared: int,
     *     effective_count: int,
     *     milestone_label: string,
     *     standings: array<int, array<string, mixed>>,
     *     results: Collection<int, Result>
     * }
     */
    public static function getStandingsAfterResults(int $limit = 0): array
    {
        $allDeclared = Result::whereIn('status', self::DECLARED_STATUSES)
            ->with([
                'program.zone',
                'program.category',
                'firstEntry.group',
                'firstEntry.student',
                'firstEntry.scoreSheets',
                'secondEntry.group',
                'secondEntry.student',
                'secondEntry.scoreSheets',
                'thirdEntry.group',
                'thirdEntry.student',
                'thirdEntry.scoreSheets',
            ])
            ->orderByRaw('CASE WHEN result_number IS NOT NULL AND result_number > 0 THEN result_number ELSE 999999 END ASC, COALESCE(published_at, created_at) ASC, id ASC')
            ->get();

        $totalDeclared = $allDeclared->count();
        $effectiveCount = $limit > 0 ? min($limit, $totalDeclared) : $totalDeclared;
        $milestoneResults = $limit > 0 ? $allDeclared->take($limit) : $allDeclared;

        $formatted = sprintf('%02d', $effectiveCount);
        $milestoneLabel = $effectiveCount > 0 ? "After {$formatted} Results" : 'Initial Standings';

        $groups = Group::all();
        $calcService = app(self::class);

        $teamData = [];
        foreach ($groups as $g) {
            $teamData[$g->id] = [
                'group' => $g,
                'points' => 0,
                'first_count' => 0,
                'second_count' => 0,
                'third_count' => 0,
                'grade_points' => 0,
                'position_points' => 0,
                'rank' => 0,
            ];
        }

        foreach ($milestoneResults as $result) {
            $program = $result->program;
            if (! $program) {
                continue;
            }

            if ($result->firstEntry?->group_id && isset($teamData[$result->firstEntry->group_id])) {
                $teamData[$result->firstEntry->group_id]['first_count']++;
            }
            if ($result->secondEntry?->group_id && isset($teamData[$result->secondEntry->group_id])) {
                $teamData[$result->secondEntry->group_id]['second_count']++;
            }
            if ($result->thirdEntry?->group_id && isset($teamData[$result->thirdEntry->group_id])) {
                $teamData[$result->thirdEntry->group_id]['third_count']++;
            }

            $txs = $calcService->buildTransactionsForResult($result, $program);
            foreach ($txs as $tx) {
                $gid = $tx['group_id'] ?? null;
                if ($gid && isset($teamData[$gid])) {
                    $pts = (int) ($tx['points'] ?? 0);
                    $teamData[$gid]['points'] += $pts;
                    if (($tx['source_type'] ?? '') === 'POSITION') {
                        $teamData[$gid]['position_points'] += $pts;
                    } elseif (($tx['source_type'] ?? '') === 'GRADE') {
                        $teamData[$gid]['grade_points'] += $pts;
                    }
                }
            }
        }

        uasort($teamData, function ($a, $b) {
            if ($b['points'] !== $a['points']) {
                return $b['points'] <=> $a['points'];
            }
            if ($b['first_count'] !== $a['first_count']) {
                return $b['first_count'] <=> $a['first_count'];
            }

            return strcmp($a['group']->name, $b['group']->name);
        });

        $rank = 1;
        $prevPts = null;
        $actualPos = 1;
        $standingsList = [];
        $topPoints = null;

        foreach ($teamData as $gid => $item) {
            if ($prevPts !== null && $item['points'] < $prevPts) {
                $rank = $actualPos;
            }
            $item['rank'] = $rank;
            $prevPts = $item['points'];
            $actualPos++;

            if ($topPoints === null) {
                $topPoints = $item['points'];
                $item['diff_from_leader'] = 0;
            } else {
                $item['diff_from_leader'] = $topPoints - $item['points'];
            }

            $item['name'] = $item['group']?->name ?? '';
            $item['code'] = $item['group']?->code ?? '';

            $standingsList[] = $item;
        }

        return [
            'limit' => $limit,
            'total_declared' => $totalDeclared,
            'effective_count' => $effectiveCount,
            'total_results' => $effectiveCount,
            'milestone_label' => $milestoneLabel,
            'standings' => $standingsList,
            'results' => $milestoneResults,
        ];
    }

    /**
     * Build the sequential result-by-result points progression matrix.
     * Shows points earned per result and progressive running total (1, 1+2, 1+2+3...).
     *
     * @return array<string, mixed>
     */
    public static function getProgressionMatrix(?string $zone = null, ?int $categoryId = null): array
    {
        Group::ensureSchema();
        Result::ensureSchema();
        Result::resyncResultNumbers();

        $groups = Group::orderBy('name')->get();

        $query = Result::whereIn('status', self::DECLARED_STATUSES)
            ->with([
                'program.zone',
                'program.category',
                'firstEntry.group',
                'firstEntry.student',
                'firstEntry.scoreSheets',
                'secondEntry.group',
                'secondEntry.student',
                'secondEntry.scoreSheets',
                'thirdEntry.group',
                'thirdEntry.student',
                'thirdEntry.scoreSheets',
            ]);

        if ($zone) {
            $query->whereHas('program', fn ($q) => $q->where('zone', $zone));
        }
        if ($categoryId) {
            $query->whereHas('program', fn ($q) => $q->where('category_id', $categoryId));
        }

        $results = $query
            ->orderByRaw('CASE WHEN result_number IS NOT NULL AND result_number > 0 THEN result_number ELSE 999999 END ASC, COALESCE(published_at, created_at) ASC, id ASC')
            ->get();

        $calcService = app(self::class);

        // Cumulative totals and group aggregate statistics
        $cumulativeTotals = [];
        $groupStats = [];
        foreach ($groups as $g) {
            $cumulativeTotals[$g->id] = 0;
            $groupStats[$g->id] = [
                'group' => $g,
                'name' => $g->name,
                'code' => $g->code,
                'color_hex' => $g->color_hex ?? '#be1e2d',
                'total_points' => 0,
                'position_points' => 0,
                'grade_points' => 0,
                'participation_points' => 0,
                'first_count' => 0,
                'second_count' => 0,
                'third_count' => 0,
                'rank' => 1,
            ];
        }

        $rows = [];
        $seq = 1;

        foreach ($results as $result) {
            $program = $result->program;
            if (! $program) {
                continue;
            }

            $resNum = (int) ($result->result_number > 0 ? $result->result_number : $seq);
            $seq++;

            $txs = $calcService->buildTransactionsForResult($result, $program);

            $resultGroupPoints = [];
            foreach ($groups as $g) {
                $resultGroupPoints[$g->id] = [
                    'pos_points' => 0,
                    'grade_points' => 0,
                    'part_points' => 0,
                    'this_points' => 0,
                    'placements' => [],
                    'grades' => [],
                    'students' => [],
                ];
            }

            foreach ($txs as $tx) {
                $gid = $tx['group_id'] ?? null;
                if ($gid && isset($resultGroupPoints[$gid])) {
                    $pts = (int) ($tx['points'] ?? 0);
                    $st = $tx['source_type'] ?? '';
                    if ($st === 'POSITION') {
                        $resultGroupPoints[$gid]['pos_points'] += $pts;
                        $groupStats[$gid]['position_points'] += $pts;
                    } elseif ($st === 'GRADE') {
                        $resultGroupPoints[$gid]['grade_points'] += $pts;
                        $groupStats[$gid]['grade_points'] += $pts;
                    } elseif ($st === 'PARTICIPATION') {
                        $resultGroupPoints[$gid]['part_points'] += $pts;
                        $groupStats[$gid]['participation_points'] += $pts;
                    }
                    $resultGroupPoints[$gid]['this_points'] += $pts;
                }
            }

            // Track podium placements
            if ($result->firstEntry?->group_id && isset($resultGroupPoints[$result->firstEntry->group_id])) {
                $gid = $result->firstEntry->group_id;
                $resultGroupPoints[$gid]['placements'][] = '1st';
                $groupStats[$gid]['first_count']++;
                if ($result->firstEntry->student?->name) {
                    $resultGroupPoints[$gid]['students'][] = $result->firstEntry->student->name;
                }
                if ($result->first_grade) {
                    $resultGroupPoints[$gid]['grades'][] = $result->first_grade;
                }
            }
            if ($result->secondEntry?->group_id && isset($resultGroupPoints[$result->secondEntry->group_id])) {
                $gid = $result->secondEntry->group_id;
                $resultGroupPoints[$gid]['placements'][] = '2nd';
                $groupStats[$gid]['second_count']++;
                if ($result->secondEntry->student?->name) {
                    $resultGroupPoints[$gid]['students'][] = $result->secondEntry->student->name;
                }
                if ($result->second_grade) {
                    $resultGroupPoints[$gid]['grades'][] = $result->second_grade;
                }
            }
            if ($result->thirdEntry?->group_id && isset($resultGroupPoints[$result->thirdEntry->group_id])) {
                $gid = $result->thirdEntry->group_id;
                $resultGroupPoints[$gid]['placements'][] = '3rd';
                $groupStats[$gid]['third_count']++;
                if ($result->thirdEntry->student?->name) {
                    $resultGroupPoints[$gid]['students'][] = $result->thirdEntry->student->name;
                }
                if ($result->third_grade) {
                    $resultGroupPoints[$gid]['grades'][] = $result->third_grade;
                }
            }

            // Calculate running cumulative progressive total for each team after this result
            $teamsData = [];
            foreach ($groups as $g) {
                $gid = $g->id;
                $thisPts = $resultGroupPoints[$gid]['this_points'];
                $cumulativeTotals[$gid] += $thisPts;
                $groupStats[$gid]['total_points'] = $cumulativeTotals[$gid];

                $teamsData[$gid] = [
                    'group_id' => $gid,
                    'group_name' => self::toTitleCase($g->name),
                    'group_code' => $g->code,
                    'color_hex' => $g->color_hex ?? '#be1e2d',
                    'this_points' => $thisPts,
                    'pos_points' => $resultGroupPoints[$gid]['pos_points'],
                    'grade_points' => $resultGroupPoints[$gid]['grade_points'],
                    'part_points' => $resultGroupPoints[$gid]['part_points'],
                    'cumulative_total' => $cumulativeTotals[$gid],
                    'placements' => $resultGroupPoints[$gid]['placements'],
                    'grades' => $resultGroupPoints[$gid]['grades'],
                    'students' => array_map(fn ($s) => self::toTitleCase($s), $resultGroupPoints[$gid]['students']),
                ];
            }

            $rows[] = [
                'result_id' => $result->id,
                'result_number' => $resNum,
                'formatted_result_number' => sprintf('%02d', $resNum),
                'program_id' => $program->id,
                'program_name' => self::toTitleCase($program->name),
                'program_code' => $program->code,
                'category_name' => self::toTitleCase($program->category?->name ?? 'General'),
                'zone' => self::toTitleCase($program->zone ?? ''),
                'status' => $result->status,
                'teams' => $teamsData,
            ];
        }

        // Rank the final group stats
        uasort($groupStats, function ($a, $b) {
            if ($b['total_points'] !== $a['total_points']) {
                return $b['total_points'] <=> $a['total_points'];
            }
            if ($b['first_count'] !== $a['first_count']) {
                return $b['first_count'] <=> $a['first_count'];
            }

            return strcmp($a['name'], $b['name']);
        });

        $currRank = 1;
        $prevPts = null;
        $idx = 1;
        foreach ($groupStats as $gid => &$st) {
            if ($prevPts !== null && $st['total_points'] < $prevPts) {
                $currRank = $idx;
            }
            $st['rank'] = $currRank;
            $prevPts = $st['total_points'];
            $idx++;
        }
        unset($st);

        return [
            'groups' => $groups,
            'rows' => $rows,
            'totals' => $groupStats,
            'total_results' => count($rows),
        ];
    }
}
