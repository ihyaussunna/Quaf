<?php

namespace App\Services;

use App\Models\Group;
use App\Models\PointsTransaction;
use App\Models\Program;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PointCalculationService
{
    /**
     * Handbook official position points:
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
     * Handbook official grade points:
     * A+ = 6 points
     * A  = 5 points
     * B  = 3 points
     * C  = 1 point
     */
    public const GRADE_POINTS = [
        'A+' => 6,
        'A' => 5,
        'B' => 3,
        'C' => 1,
    ];

    /**
     * Calculate grade name and points from mark (0-100 scale).
     * Handbook grade points:
     * A+ = 90–100 -> 6 points
     * A  = 70–89  -> 5 points
     * B  = 60–69  -> 3 points
     * C  = 50–59  -> 1 point
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

        $placements = [
            1 => ['entry' => $result->firstEntry, 'label' => '1st Place', 'pts' => (int) round(self::POSITION_POINTS[1] * $weight)],
            2 => ['entry' => $result->secondEntry, 'label' => '2nd Place', 'pts' => (int) round(self::POSITION_POINTS[2] * $weight)],
            3 => ['entry' => $result->thirdEntry, 'label' => '3rd Place', 'pts' => (int) round(self::POSITION_POINTS[3] * $weight)],
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
                $avgScore = $entry->scoreSheets->where('is_submitted', true)->avg('total_score');
                if ($avgScore !== null && $avgScore > 0) {
                    $gradeInfo = self::getGradeFromScore((float) $avgScore);
                    $gradeName = $gradeInfo['grade'];
                    $gradePointsRaw = $gradeInfo['points'];
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

        $partPts = (int) round(1 * $weight);

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
    }
}
