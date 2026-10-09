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
     * Get position points (1st, 2nd, 3rd) based on Program type, member count, and General Shifting rules.
     *
     * Official Rulebook (Rule 11):
     * - Individual: 1st = 5, 2nd = 3, 3rd = 1
     * - Group (2 members): 1st = 7, 2nd = 5, 3rd = 3
     * - Group (3 members): 1st = 10, 2nd = 7, 3rd = 4
     * - Group (4, 5 members): 1st = 15, 2nd = 10, 3rd = 5
     * - General Shifting (>5 members or General Shifting): 1st = 20, 2nd = 15, 3rd = 10
     *
     * @return array<int, int> [1 => 1st_pts, 2 => 2nd_pts, 3 => 3rd_pts]
     */
    public static function getPositionPointsForProgram(Program $program): array
    {
        if ($program->isIndividual()) {
            return [
                1 => 5,
                2 => 3,
                3 => 1,
            ];
        }

        // Check for General Shifting (ജനറൽ ഷിഫ്റ്റിങ്)
        $nameLower = strtolower($program->name.' '.($program->malayalam_name ?? '').' '.($program->rules ?? ''));
        $isGeneralShifting = str_contains($nameLower, 'shift')
            || str_contains($nameLower, 'general')
            || str_contains($nameLower, 'ഷിഫ്റ്റ്')
            || str_contains($nameLower, 'ജനറൽ');

        $members = max(1, (int) ($program->participant_count ?: $program->max_participants_per_group ?: 2));

        if ($isGeneralShifting || $members > 5) {
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

        // 2 members (or 1 member group)
        return [
            1 => 7,
            2 => 5,
            3 => 3,
        ];
    }

    /**
     * Automatically determine 1st, 2nd, and 3rd place entries from judge marks.
     * Only 'present' entries with submitted judge scores are considered.
     *
     * @return array{
     *     first: ?ProgramEntry,
     *     second: ?ProgramEntry,
     *     third: ?ProgramEntry,
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

        return [
            'first' => $entries->get(0),
            'second' => $entries->get(1),
            'third' => $entries->get(2),
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

            // 3. Fetch all published results with their programs and entries
            $results = Result::where('status', 'published')
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
                ->where('status', 'published')
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

        $placements = [
            1 => ['entry' => $result->firstEntry, 'label' => '1st Place', 'pts' => (int) round($posPointsMap[1] * $weight)],
            2 => ['entry' => $result->secondEntry, 'label' => '2nd Place', 'pts' => (int) round($posPointsMap[2] * $weight)],
            3 => ['entry' => $result->thirdEntry, 'label' => '3rd Place', 'pts' => (int) round($posPointsMap[3] * $weight)],
        ];

        foreach ($placements as $pos => $data) {
            $entry = $data['entry'];
            if (! $entry || ! $entry->group_id) {
                continue;
            }

            $group = $entry->group;
            $student = $entry->student;
            $posPts = $data['pts'];
            $posLabel = $data['label'];

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
                    'description' => "{$group->name} — {$program->name} — {$posLabel} (+{$posPts} pts)",
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // B. Grade Points from Result declaration or ScoreSheets
            $declaredGrade = match ($pos) {
                1 => $result->first_grade,
                2 => $result->second_grade,
                3 => $result->third_grade,
                default => null,
            };

            $gradeName = null;
            $gradePointsRaw = 0;

            if (! empty($declaredGrade) && isset(self::GRADE_POINTS[strtoupper(trim($declaredGrade))])) {
                $gradeName = strtoupper(trim($declaredGrade));
                $gradePointsRaw = self::GRADE_POINTS[$gradeName];
            } else {
                $judgeGrade = $entry->scoreSheets->where('is_submitted', true)->pluck('criteria_scores.grade')->filter()->first();
                if ($judgeGrade && isset(self::GRADE_POINTS[strtoupper(trim($judgeGrade))])) {
                    $gradeName = strtoupper(trim($judgeGrade));
                    $gradePointsRaw = self::GRADE_POINTS[$gradeName];
                } else {
                    $avgScore = $entry->scoreSheets->where('is_submitted', true)->avg('total_score');
                    if ($avgScore !== null && $avgScore > 0) {
                        $gradeInfo = self::getGradeFromScore((float) $avgScore);
                        $gradeName = $gradeInfo['grade'];
                        $gradePointsRaw = $gradeInfo['points'];
                    }
                }
            }

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

        // C. Participation Points for non-placed verified entries
        $placedEntryIds = array_filter([
            $result->first_entry_id,
            $result->second_entry_id,
            $result->third_entry_id,
        ]);

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

        $results = Result::where('status', 'published')
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
}
