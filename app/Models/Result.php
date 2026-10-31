<?php

namespace App\Models;

use App\Services\PointCalculationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'result_number',
        'first_entry_id',
        'first_grade',
        'second_entry_id',
        'second_grade',
        'third_entry_id',
        'third_grade',
        'status',
        'verified_by',
        'published_at',
        'remarks',
        'poster_image',
        'template_id',
        'custom_poster_settings',
        'is_media_published',
        'media_published_at',
    ];

    protected function casts(): array
    {
        return [
            'result_number' => 'integer',
            'published_at' => 'datetime',
            'media_published_at' => 'datetime',
            'is_media_published' => 'boolean',
            'custom_poster_settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::ensureSchema();

        static::saving(function (Result $result) {
            static::ensureSchema();

            // Automatically assign next sequential result number when transferring to announcer or declaring
            if (in_array($result->status, ['send', 'delivered', 'announced', 'published'])) {
                $rawNo = $result->attributes['result_number'] ?? null;
                if (empty($rawNo) || (int) $rawNo <= 0) {
                    $max = static::getSafeMaxResultNumber();
                    $result->result_number = $max + 1;
                }
                if (empty($result->published_at)) {
                    $result->published_at = Carbon::now();
                }
            }
        });
    }

    public static function getSafeMaxResultNumber(): int
    {
        static::ensureSchema();

        try {
            if (Schema::hasTable('results') && Schema::hasColumn('results', 'result_number')) {
                return (int) (DB::table('results')->whereNotNull('result_number')->max('result_number') ?? 0);
            }
        } catch (\Throwable) {
            // Column does not exist in live DB
        }

        return 0;
    }

    /**
     * Resync all declared results sequentially: 1, 2, 3, 4, 5...
     * Resolves any duplicates, gaps, or resets permanently.
     */
    public static function resyncResultNumbers(): void
    {
        try {
            if (! Schema::hasTable('results') || ! Schema::hasColumn('results', 'result_number')) {
                return;
            }

            $declared = DB::table('results')
                ->whereIn('status', ['send', 'delivered', 'announced', 'published'])
                ->get(['id', 'result_number', 'status', 'published_at', 'created_at']);

            if ($declared->isEmpty()) {
                return;
            }

            $total = $declared->count();
            $assigned = $declared->filter(fn ($r) => ! empty($r->result_number) && (int) $r->result_number > 0);
            $uniqueNumbers = $assigned->pluck('result_number')->unique();

            $hasNullOrZero = $assigned->count() < $total;
            $hasDuplicates = $uniqueNumbers->count() < $assigned->count();
            $maxNum = (int) ($assigned->max('result_number') ?? 0);
            $hasGapsOrMismatch = ($maxNum !== $total) || ($uniqueNumbers->min() !== 1 && $total > 0);

            // If already perfectly 1..N sequential with no duplicates or gaps, do nothing
            if (! $hasNullOrZero && ! $hasDuplicates && ! $hasGapsOrMismatch) {
                return;
            }

            // Sort chronologically: published_at ASC, created_at ASC, id ASC
            $sorted = $declared->sort(function ($a, $b) {
                $pubA = $a->published_at ? strtotime($a->published_at) : ($a->created_at ? strtotime($a->created_at) : 0);
                $pubB = $b->published_at ? strtotime($b->published_at) : ($b->created_at ? strtotime($b->created_at) : 0);

                if ($pubA !== $pubB) {
                    return $pubA <=> $pubB;
                }

                $crtA = $a->created_at ? strtotime($a->created_at) : 0;
                $crtB = $b->created_at ? strtotime($b->created_at) : 0;

                if ($crtA !== $crtB) {
                    return $crtA <=> $crtB;
                }

                return $a->id <=> $b->id;
            })->values();

            $counter = 1;
            foreach ($sorted as $row) {
                DB::table('results')->where('id', $row->id)->update([
                    'result_number' => $counter,
                ]);
                $counter++;
            }
        } catch (\Throwable) {
            // Silently ignore if DB is unavailable
        }
    }

    public static function ensureSchema(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        try {
            if (Schema::hasTable('results')) {
                if (! Schema::hasColumn('results', 'result_number')) {
                    try {
                        Schema::table('results', function (Blueprint $table) {
                            $table->unsignedInteger('result_number')->nullable()->after('program_id');
                        });
                    } catch (\Throwable) {
                        try {
                            DB::statement('ALTER TABLE results ADD COLUMN result_number INT UNSIGNED NULL');
                        } catch (\Throwable) {
                        }
                    }

                    try {
                        Schema::table('results', function (Blueprint $table) {
                            $table->index('result_number');
                        });
                    } catch (\Throwable) {
                    }
                }

                // Automatically heal and re-sequence any existing duplicates or missing numbers
                static::resyncResultNumbers();
            }
            $checked = true;
        } catch (\Throwable) {
            // Ignore schema errors in constrained environments
        }
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function firstEntry(): BelongsTo
    {
        return $this->belongsTo(ProgramEntry::class, 'first_entry_id');
    }

    public function secondEntry(): BelongsTo
    {
        return $this->belongsTo(ProgramEntry::class, 'second_entry_id');
    }

    public function thirdEntry(): BelongsTo
    {
        return $this->belongsTo(ProgramEntry::class, 'third_entry_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ResultTemplate::class, 'template_id');
    }

    public function coinTransactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class);
    }

    /**
     * Get 1st, 2nd, and 3rd place winners (including ties) for poster generation.
     */
    public function getPosterWinners(): array
    {
        if (! empty($this->custom_poster_settings['custom_winners']) && is_array($this->custom_poster_settings['custom_winners'])) {
            return $this->custom_poster_settings['custom_winners'];
        }

        $podium = $this->getPodiumWinnersWithTies();
        $winners = [
            'first' => [],
            'second' => [],
            'third' => [],
        ];

        $medalMap = [
            1 => '/images/medals/first.png',
            2 => '/images/medals/second.png',
            3 => '/images/medals/third.png',
        ];

        foreach ([1 => 'first', 2 => 'second', 3 => 'third'] as $pos => $key) {
            foreach ($podium[$pos] as $w) {
                $winners[$key][] = [
                    'id' => $w['id'],
                    'name' => $w['name'],
                    'unit' => $w['team'],
                    'chest' => $w['chest'],
                    'medal' => $medalMap[$pos],
                    'rank' => sprintf('%02d', $pos),
                ];
            }
        }

        // Fallbacks so that Studio poster NEVER renders empty podium
        if (empty($winners['first'])) {
            $winners['first'][] = [
                'id' => null,
                'name' => 'MUHAMMED YASEEN P.',
                'unit' => 'RED FOX TEAM',
                'chest' => 'QF0001',
                'medal' => '/images/medals/first.png',
                'rank' => '01',
            ];
        }

        if (empty($winners['second'])) {
            $winners['second'][] = [
                'id' => null,
                'name' => 'AHMAD RAYYAN K.',
                'unit' => 'GREEN WARRIORS',
                'chest' => 'QF0002',
                'medal' => '/images/medals/second.png',
                'rank' => '02',
            ];
        }

        if (empty($winners['third'])) {
            $winners['third'][] = [
                'id' => null,
                'name' => 'BILAL MUHAMMED',
                'unit' => 'BLUE RIDERS',
                'chest' => 'QF0003',
                'medal' => '/images/medals/third.png',
                'rank' => '03',
            ];
        }

        return $winners;
    }

    /**
     * Assign next sequential result number immediately if not already assigned.
     */
    public function assignNextResultNumber(): int
    {
        $raw = $this->attributes['result_number'] ?? null;
        if (! empty($raw) && (int) $raw > 0) {
            return (int) $raw;
        }

        $max = static::getSafeMaxResultNumber();
        $next = $max + 1;
        $this->result_number = $next;
        $this->saveQuietly();

        return (int) $this->result_number;
    }

    /**
     * Get or auto-assign sequential result number (e.g. 1, 2, 3...)
     */
    public function getResultNumberAttribute(): ?int
    {
        $raw = $this->attributes['result_number'] ?? null;
        if ($raw !== null && (int) $raw > 0) {
            return (int) $raw;
        }

        if (in_array($this->status, ['send', 'delivered', 'announced', 'published'])) {
            $max = static::getSafeMaxResultNumber();
            $next = $max + 1;
            $this->attributes['result_number'] = $next;
            if ($this->exists && $this->id) {
                try {
                    DB::table('results')->where('id', $this->id)->update(['result_number' => $next]);
                } catch (\Throwable) {
                }
            }

            return $next;
        }

        return null;
    }

    /**
     * Formatted two-digit result number (e.g. "01", "02", "10").
     */
    public function getFormattedResultNumberAttribute(): ?string
    {
        return $this->result_number ? sprintf('%02d', $this->result_number) : null;
    }

    /**
     * Get clean download filename: {result_number}_{zone}_{program_name}.{ext}
     */
    public function getDownloadFilename(string $ext = 'png'): string
    {
        $resNo = sprintf('%02d', $this->result_number);
        $zone = $this->program?->zone?->name ?? $this->program?->eligibility ?? 'Zone';
        $prog = $this->program?->name ?? 'Program';

        $cleanZone = preg_replace('/[^A-Za-z0-9_-]/', '_', trim($zone));
        $cleanProg = preg_replace('/[^A-Za-z0-9_-]/', '_', trim($prog));
        $cleanZone = trim(preg_replace('/_+/', '_', (string) $cleanZone), '_');
        $cleanProg = trim(preg_replace('/_+/', '_', (string) $cleanProg), '_');

        return "{$resNo}_{$cleanZone}_{$cleanProg}.{$ext}";
    }

    /**
     * Build standard breakdown attributes for a specific entry and position.
     *
     * @return array<string, mixed>
     */
    public function buildWinnerItemForEntry(?ProgramEntry $entry, int $pos, ?string $overrideGrade = null): array
    {
        $declaredGrade = $overrideGrade ?? match ($pos) {
            1 => $this->first_grade,
            2 => $this->second_grade,
            3 => $this->third_grade,
            default => null,
        };

        $grade = (! empty($declaredGrade) && strtoupper(trim($declaredGrade)) !== 'NO_GRADE')
            ? strtoupper(trim($declaredGrade))
            : null;

        $totalMarks = 0.0;
        $avgMarks = 0.0;
        $judgeCount = 0;

        if ($entry) {
            $scores = $entry->relationLoaded('scores')
                ? $entry->scores
                : ($entry->relationLoaded('scoreSheets') ? $entry->scoreSheets : $entry->scores);

            $submitted = $scores ? $scores->where('is_submitted', true) : collect();
            if ($submitted->isEmpty() && $scores && $scores->where('total_score', '>', 0)->isNotEmpty()) {
                $submitted = $scores->where('total_score', '>', 0);
            }

            if ($submitted->isNotEmpty()) {
                $judgeCount = $submitted->count();
                $totalMarks = round((float) $submitted->sum('total_score'), 2);
                $avgMarks = round((float) $submitted->avg('total_score'), 2);
            } elseif (isset($entry->computed_avg_score) && $entry->computed_avg_score > 0) {
                $avgMarks = round((float) $entry->computed_avg_score, 2);
                $totalMarks = round((float) ($entry->computed_total_marks ?? $entry->computed_avg_score), 2);
                $judgeCount = (int) ($entry->computed_judge_count ?? 1);
            }
        }

        if (! $grade && $entry) {
            if ($avgMarks > 0) {
                $gradeInfo = PointCalculationService::getGradeFromScore($avgMarks);
                $grade = $gradeInfo['grade'];
            } elseif ($entry->relationLoaded('scoreSheets')) {
                $judgeGrade = $entry->scoreSheets->where('is_submitted', true)->pluck('criteria_scores.grade')->filter()->first();
                if ($judgeGrade && isset(PointCalculationService::GRADE_POINTS[strtoupper(trim($judgeGrade))])) {
                    $grade = strtoupper(trim($judgeGrade));
                }
            }
        }

        $program = $this->relationLoaded('program') ? $this->program : ($this->program_id ? Program::find($this->program_id) : null);
        $weight = (float) ($program?->points_weight ?? 1.0);
        $gradePointsRaw = ($grade && isset(PointCalculationService::GRADE_POINTS[$grade]))
            ? PointCalculationService::GRADE_POINTS[$grade]
            : 0;
        $gradePoints = (int) round($gradePointsRaw * $weight);

        $posPointsMap = $program
            ? PointCalculationService::getPositionPointsForProgram($program)
            : [1 => 5, 2 => 3, 3 => 1];
        $posPoints = (int) round(($posPointsMap[$pos] ?? 0) * $weight);

        $name = $entry?->student?->name ?: ($entry ? ($entry->group?->name ?? 'Chest #'.$entry->chest_number) : '—');
        $chest = $entry?->chest_number ?? ($entry?->student?->student_id ?? '—');
        $team = $entry?->group?->name ?? $entry?->student?->group?->name ?? '—';
        $teamColor = $entry?->group?->color_hex ?? $entry?->student?->group?->color_hex ?? '#be1e2d';

        return [
            'id' => $entry?->id,
            'entry' => $entry,
            'pos' => $pos,
            'rank' => sprintf('%02d', $pos),
            'name' => $name,
            'chest' => $chest,
            'team' => $team,
            'team_color' => $teamColor,
            'grade' => $grade,
            'grade_points' => $gradePoints,
            'position_points' => $posPoints,
            'total_points' => ($posPoints + $gradePoints),
            'total_marks' => $totalMarks,
            'avg_marks' => $avgMarks,
            'judge_count' => $judgeCount,
            'is_tied' => false,
        ];
    }

    /**
     * Get all podium winners (1st, 2nd, 3rd) including any ties/shared winners.
     *
     * @return array{
     *     1: array<int, array<string, mixed>>,
     *     2: array<int, array<string, mixed>>,
     *     3: array<int, array<string, mixed>>,
     *     has_ties: bool,
     *     total_winners: int
     * }
     */
    public function getPodiumWinnersWithTies(): array
    {
        $program = $this->relationLoaded('program') ? $this->program : ($this->program_id ? Program::with(['entries.scores', 'entries.scoreSheets', 'entries.student.group', 'entries.group'])->find($this->program_id) : null);

        $customWinners = (! empty($this->custom_poster_settings['custom_winners']) && is_array($this->custom_poster_settings['custom_winners']))
            ? $this->custom_poster_settings['custom_winners']
            : null;

        $podium = null;
        if ($program) {
            try {
                $podium = PointCalculationService::determinePodiumForProgram($program);
            } catch (\Throwable) {
                $podium = null;
            }
        }

        $result = [
            1 => [],
            2 => [],
            3 => [],
            'has_ties' => false,
            'total_winners' => 0,
        ];

        foreach ([1 => 'first', 2 => 'second', 3 => 'third'] as $pos => $key) {
            $entriesForPos = collect();

            // A. Primary direct relationship
            $primaryEntry = match ($pos) {
                1 => $this->firstEntry,
                2 => $this->secondEntry,
                3 => $this->thirdEntry,
                default => null,
            };
            if (! $primaryEntry) {
                $pId = match ($pos) {
                    1 => $this->first_entry_id,
                    2 => $this->second_entry_id,
                    3 => $this->third_entry_id,
                    default => null,
                };
                if ($pId) {
                    $primaryEntry = ProgramEntry::with(['group', 'student.group', 'scores', 'scoreSheets'])->find($pId);
                }
            }
            if ($primaryEntry) {
                $entriesForPos->push($primaryEntry);
            }

            // B. Add all tied entries from judge podium calculation
            $podiumKey = "{$key}_all";
            if ($podium && ! empty($podium[$podiumKey]) && $podium[$podiumKey]->isNotEmpty()) {
                foreach ($podium[$podiumKey] as $tiedEntry) {
                    if (! $entriesForPos->contains('id', $tiedEntry->id)) {
                        $entriesForPos->push($tiedEntry);
                    }
                }
            }

            // C. Add manual shared entries from custom_poster_settings['shared_entry_ids']
            if (! empty($this->custom_poster_settings['shared_entry_ids'][$pos]) && is_array($this->custom_poster_settings['shared_entry_ids'][$pos])) {
                foreach ($this->custom_poster_settings['shared_entry_ids'][$pos] as $sharedId) {
                    if (! $entriesForPos->contains('id', $sharedId)) {
                        $sEntry = ProgramEntry::with(['group', 'student.group', 'scores', 'scoreSheets'])->find($sharedId);
                        if ($sEntry) {
                            $entriesForPos->push($sEntry);
                        }
                    }
                }
            }

            // D. Add custom winners from studio
            if (! empty($customWinners[$key]) && is_array($customWinners[$key])) {
                foreach ($customWinners[$key] as $cw) {
                    if (! empty($cw['id']) && ! $entriesForPos->contains('id', $cw['id'])) {
                        $cEntry = ProgramEntry::with(['group', 'student.group', 'scores', 'scoreSheets'])->find($cw['id']);
                        if ($cEntry) {
                            $entriesForPos->push($cEntry);
                        }
                    }
                }
            }

            // E. Format each winner item for this position
            $winnerItems = [];
            foreach ($entriesForPos as $entry) {
                $winnerItems[] = $this->buildWinnerItemForEntry($entry, $pos);
            }

            // Fallback for custom studio names without entry ID
            if (empty($winnerItems) && ! empty($customWinners[$key]) && is_array($customWinners[$key])) {
                foreach ($customWinners[$key] as $cw) {
                    if (! empty($cw['name'])) {
                        $winnerItems[] = [
                            'id' => $cw['id'] ?? null,
                            'entry' => null,
                            'pos' => $pos,
                            'rank' => sprintf('%02d', $pos),
                            'name' => $cw['name'],
                            'chest' => $cw['chest'] ?? '—',
                            'team' => $cw['unit'] ?? 'Group',
                            'team_color' => '#be1e2d',
                            'grade' => null,
                            'grade_points' => 0,
                            'position_points' => (int) round(($pos === 1 ? 5 : ($pos === 2 ? 3 : 1)) * (float) ($program?->points_weight ?? 1.0)),
                            'total_points' => (int) round(($pos === 1 ? 5 : ($pos === 2 ? 3 : 1)) * (float) ($program?->points_weight ?? 1.0)),
                            'total_marks' => 0.0,
                            'avg_marks' => 0.0,
                            'judge_count' => 0,
                            'is_tied' => count($customWinners[$key]) > 1,
                        ];
                    }
                }
            }

            if (count($winnerItems) > 1) {
                foreach ($winnerItems as &$wi) {
                    $wi['is_tied'] = true;
                }
                unset($wi);
                $result['has_ties'] = true;
            }

            $result[$pos] = $winnerItems;
            $result['total_winners'] += count($winnerItems);
        }

        return $result;
    }

    /**
     * Get all winners breakdown for a specific podium position (1, 2, or 3).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getWinnersBreakdownForPosition(int $pos): array
    {
        $all = $this->getPodiumWinnersWithTies();

        return $all[$pos] ?? [];
    }

    /**
     * Get single winner breakdown for backward compatibility.
     *
     * @return array<string, mixed>
     */
    public function getWinnerBreakdown(int $pos): array
    {
        $winners = $this->getWinnersBreakdownForPosition($pos);

        if (! empty($winners)) {
            return $winners[0];
        }

        $entry = match ($pos) {
            1 => $this->firstEntry,
            2 => $this->secondEntry,
            3 => $this->thirdEntry,
            default => null,
        };

        return $this->buildWinnerItemForEntry($entry, $pos);
    }

    /**
     * Get non-placed entries in this program that achieved grades (A+, A, B, C).
     *
     * @return Collection<int, ProgramEntry>
     */
    public function getOtherGradeHolders(): Collection
    {
        if (! $this->program) {
            return collect();
        }

        $placedEntryIds = array_filter([
            $this->first_entry_id,
            $this->second_entry_id,
            $this->third_entry_id,
        ]);

        $weight = (float) ($this->program->points_weight ?? 1.0);

        return $this->program->entries()
            ->whereIn('status', ['verified', 'confirmed'])
            ->whereNotIn('id', $placedEntryIds)
            ->with(['student.group', 'group', 'scoreSheets', 'scores'])
            ->get()
            ->map(function ($e) use ($weight) {
                $scores = $e->relationLoaded('scores') ? $e->scores : ($e->relationLoaded('scoreSheets') ? $e->scoreSheets : $e->scores);
                $submitted = $scores ? $scores->where('is_submitted', true) : collect();
                if ($submitted->isEmpty() && $scores && $scores->where('total_score', '>', 0)->isNotEmpty()) {
                    $submitted = $scores->where('total_score', '>', 0);
                }

                $avgScore = $submitted->isNotEmpty() ? (float) $submitted->avg('total_score') : 0.0;
                $totalScore = $submitted->isNotEmpty() ? (float) $submitted->sum('total_score') : 0.0;
                $judgeCount = $submitted->count();

                $gradeInfo = $avgScore ? PointCalculationService::getGradeFromScore((float) $avgScore) : ['grade' => null, 'points' => 0];
                $e->computed_grade = $gradeInfo['grade'];
                $e->computed_grade_points = (int) round($gradeInfo['points'] * $weight);
                $e->computed_avg_score = round($avgScore, 2);
                $e->computed_total_marks = round($totalScore, 2);
                $e->computed_judge_count = $judgeCount;

                return $e;
            })
            ->filter(fn ($e) => ! empty($e->computed_grade))
            ->values();
    }

    /**
     * Get all participant entries for this result's program with their evaluation marks and rank.
     *
     * @return Collection<int, ProgramEntry>
     */
    public function getAllEntriesWithMarks(): Collection
    {
        if (! $this->program) {
            return collect();
        }

        return $this->program->entries()
            ->whereIn('status', ProgramEntry::ACTIVE_STATUSES)
            ->with(['student.group', 'group', 'scores.judge', 'scoreSheets.judge'])
            ->get()
            ->map(function ($entry) {
                $scores = $entry->relationLoaded('scores') ? $entry->scores : ($entry->relationLoaded('scoreSheets') ? $entry->scoreSheets : $entry->scores);
                $submitted = $scores ? $scores->where('is_submitted', true) : collect();
                if ($submitted->isEmpty() && $scores && $scores->where('total_score', '>', 0)->isNotEmpty()) {
                    $submitted = $scores->where('total_score', '>', 0);
                }

                $entry->computed_judge_count = $submitted->count();
                $entry->computed_total_marks = round((float) $submitted->sum('total_score'), 2);
                $entry->computed_avg_score = $submitted->isNotEmpty() ? round((float) $submitted->avg('total_score'), 2) : 0.0;
                $gradeInfo = PointCalculationService::getGradeFromScore($entry->computed_avg_score);
                $entry->computed_grade = $gradeInfo['grade'];
                $entry->computed_grade_points = $gradeInfo['points'];

                return $entry;
            })
            ->sortByDesc('computed_avg_score')
            ->values();
    }
}
