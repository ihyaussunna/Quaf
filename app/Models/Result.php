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
        $winners = [
            'first' => [],
            'second' => [],
            'third' => [],
        ];

        // 1. Try to fetch all podium entries including ties from PointCalculationService
        if ($this->program) {
            try {
                $podium = PointCalculationService::determinePodiumForProgram($this->program);

                if (! empty($podium['first_all']) && $podium['first_all']->isNotEmpty()) {
                    foreach ($podium['first_all'] as $entry) {
                        $winners['first'][] = [
                            'id' => $entry->id,
                            'name' => $entry->student->name ?? ($entry->group->name ?? '1st Place Winner'),
                            'unit' => $entry->student->group->name ?? ($entry->group->name ?? 'Group'),
                            'chest' => $entry->chest_number ?? ($entry->student->student_id ?? ''),
                            'medal' => '/images/medals/first.png',
                            'rank' => '01',
                        ];
                    }
                }

                if (! empty($podium['second_all']) && $podium['second_all']->isNotEmpty()) {
                    foreach ($podium['second_all'] as $entry) {
                        $winners['second'][] = [
                            'id' => $entry->id,
                            'name' => $entry->student->name ?? ($entry->group->name ?? '2nd Place Winner'),
                            'unit' => $entry->student->group->name ?? ($entry->group->name ?? 'Group'),
                            'chest' => $entry->chest_number ?? ($entry->student->student_id ?? ''),
                            'medal' => '/images/medals/second.png',
                            'rank' => '02',
                        ];
                    }
                }

                if (! empty($podium['third_all']) && $podium['third_all']->isNotEmpty()) {
                    foreach ($podium['third_all'] as $entry) {
                        $winners['third'][] = [
                            'id' => $entry->id,
                            'name' => $entry->student->name ?? ($entry->group->name ?? '3rd Place Winner'),
                            'unit' => $entry->student->group->name ?? ($entry->group->name ?? 'Group'),
                            'chest' => $entry->chest_number ?? ($entry->student->student_id ?? ''),
                            'medal' => '/images/medals/third.png',
                            'rank' => '03',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // Ignore and fall back to direct relationships
            }
        }

        // 2. Fallbacks for direct single entry relations if not populated from podium calculation
        if (empty($winners['first']) && $this->firstEntry) {
            $name = $this->firstEntry->student->name ?? ($this->firstEntry->group->name ?? '1st Place Winner');
            $unit = $this->firstEntry->student->group->name ?? ($this->firstEntry->group->name ?? 'Group');
            $chest = $this->firstEntry->chest_number ?? ($this->firstEntry->student->student_id ?? '');

            $winners['first'][] = [
                'id' => $this->firstEntry->id,
                'name' => $name,
                'unit' => $unit,
                'chest' => $chest,
                'medal' => '/images/medals/first.png',
                'rank' => '01',
            ];
        }

        if (empty($winners['second']) && $this->secondEntry) {
            $name = $this->secondEntry->student->name ?? ($this->secondEntry->group->name ?? '2nd Place Winner');
            $unit = $this->secondEntry->student->group->name ?? ($this->secondEntry->group->name ?? 'Group');
            $chest = $this->secondEntry->chest_number ?? ($this->secondEntry->student->student_id ?? '');

            $winners['second'][] = [
                'id' => $this->secondEntry->id,
                'name' => $name,
                'unit' => $unit,
                'chest' => $chest,
                'medal' => '/images/medals/second.png',
                'rank' => '02',
            ];
        }

        if (empty($winners['third']) && $this->thirdEntry) {
            $name = $this->thirdEntry->student->name ?? ($this->thirdEntry->group->name ?? '3rd Place Winner');
            $unit = $this->thirdEntry->student->group->name ?? ($this->thirdEntry->group->name ?? 'Group');
            $chest = $this->thirdEntry->chest_number ?? ($this->thirdEntry->student->student_id ?? '');

            $winners['third'][] = [
                'id' => $this->thirdEntry->id,
                'name' => $name,
                'unit' => $unit,
                'chest' => $chest,
                'medal' => '/images/medals/third.png',
                'rank' => '03',
            ];
        }

        // 3. Fallbacks so that Studio poster NEVER renders empty podium
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
     * Get detailed points breakdown (position pts, grade, grade pts, total pts) for a podium placement.
     *
     * @return array{
     *     entry: ?ProgramEntry,
     *     name: string,
     *     chest: string,
     *     team: string,
     *     team_color: string,
     *     grade: ?string,
     *     grade_points: int,
     *     position_points: int,
     *     total_points: int
     * }
     */
    public function getWinnerBreakdown(int $pos): array
    {
        $entry = match ($pos) {
            1 => $this->firstEntry,
            2 => $this->secondEntry,
            3 => $this->thirdEntry,
            default => null,
        };

        $declaredGrade = match ($pos) {
            1 => $this->first_grade,
            2 => $this->second_grade,
            3 => $this->third_grade,
            default => null,
        };

        $grade = (! empty($declaredGrade) && strtoupper(trim($declaredGrade)) !== 'NO_GRADE')
            ? strtoupper(trim($declaredGrade))
            : null;

        if (! $grade && $entry) {
            if ($entry->relationLoaded('scoreSheets')) {
                $judgeGrade = $entry->scoreSheets->where('is_submitted', true)->pluck('criteria_scores.grade')->filter()->first();
                if ($judgeGrade && isset(PointCalculationService::GRADE_POINTS[strtoupper(trim($judgeGrade))])) {
                    $grade = strtoupper(trim($judgeGrade));
                } else {
                    $avgScore = $entry->scoreSheets->where('is_submitted', true)->avg('total_score');
                    if ($avgScore !== null && $avgScore > 0) {
                        $gradeInfo = PointCalculationService::getGradeFromScore((float) $avgScore);
                        $grade = $gradeInfo['grade'];
                    }
                }
            }
        }

        $weight = (float) ($this->program?->points_weight ?? 1.0);
        $gradePointsRaw = ($grade && isset(PointCalculationService::GRADE_POINTS[$grade]))
            ? PointCalculationService::GRADE_POINTS[$grade]
            : 0;
        $gradePoints = (int) round($gradePointsRaw * $weight);

        $posPointsMap = $this->program
            ? PointCalculationService::getPositionPointsForProgram($this->program)
            : [1 => 5, 2 => 3, 3 => 1];
        $posPoints = (int) round(($posPointsMap[$pos] ?? 0) * $weight);

        $name = $entry?->student?->name ?: ($entry ? 'Chest #'.$entry->chest_number : '—');
        $chest = $entry?->chest_number ?? '—';
        $team = $entry?->group?->name ?? $entry?->student?->group?->name ?? '—';
        $teamColor = $entry?->group?->color_hex ?? $entry?->student?->group?->color_hex ?? '#be1e2d';

        return [
            'entry' => $entry,
            'name' => $name,
            'chest' => $chest,
            'team' => $team,
            'team_color' => $teamColor,
            'grade' => $grade,
            'grade_points' => $gradePoints,
            'position_points' => $posPoints,
            'total_points' => ($posPoints + $gradePoints),
        ];
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
            ->with(['student.group', 'group', 'scoreSheets'])
            ->get()
            ->map(function ($e) use ($weight) {
                $avgScore = $e->scoreSheets->where('is_submitted', true)->avg('total_score');
                $gradeInfo = $avgScore ? PointCalculationService::getGradeFromScore((float) $avgScore) : ['grade' => null, 'points' => 0];
                $e->computed_grade = $gradeInfo['grade'];
                $e->computed_grade_points = (int) round($gradeInfo['points'] * $weight);

                return $e;
            })
            ->filter(fn ($e) => ! empty($e->computed_grade))
            ->values();
    }
}
