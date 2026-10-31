<?php

namespace App\Models;

use App\Services\PointCalculationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
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
            'published_at' => 'datetime',
            'media_published_at' => 'datetime',
            'is_media_published' => 'boolean',
            'custom_poster_settings' => 'array',
        ];
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
     * Compute sequence result number (e.g. 1, 2, 3...)
     */
    public function getResultNumberAttribute(): int
    {
        return self::where(function ($q) {
            if ($this->published_at) {
                $q->where('published_at', '<', $this->published_at)
                    ->orWhere(function ($sub) {
                        $sub->where('published_at', '=', $this->published_at)
                            ->where('id', '<=', $this->id);
                    });
            } else {
                $q->where('id', '<=', $this->id);
            }
        })->count() ?: ($this->id ?? 1);
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
}
