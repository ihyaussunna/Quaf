<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    public const MAX_INDIVIDUAL_PROGRAMS = 5;

    public const ZONES = [
        'A Zone' => 'A Zone (റാബിഅ, തഖസ്സുസ് — Class 4: NF4, UH4, S4, ID4, UT4, L4, TQS)',
        'B Zone' => 'B Zone (സാലിസ് — Class 3: NF3, ID3, UH3, UT3, S3, L3)',
        'C Zone' => 'C Zone (ഊല, സാനി — Class 1 & 2: U1, U2, L2, S1, S2)',
        'Mix Zone' => 'Mix Zone (ജനറൽ — എല്ലാ ക്ലാസുകൾക്കും Open Category)',
    ];

    protected $fillable = [
        'student_id',
        'user_id',
        'group_id',
        'zone_id',
        'name',
        'category',
        'class_level',
        'gender',
        'dob',
        'contact',
        'photo_url',
        'qr_token',
        'points_cache',
    ];

    protected $appends = [
        'zone_name',
        'chest_number',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'points_cache' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ProgramEntry::class);
    }

    public function participations(): BelongsToMany
    {
        return $this->belongsToMany(ProgramEntry::class, 'program_entry_participants', 'student_id', 'entry_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function pointsTransactions(): HasMany
    {
        return $this->hasMany(PointsTransaction::class);
    }

    public function getNameAttribute(?string $value): string
    {
        return ltrim((string) $value, "? \t\n\r\0\x0B");
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['name'] = ltrim((string) $value, "? \t\n\r\0\x0B");
    }

    public function getZoneNameAttribute(): string
    {
        return $this->zone?->name ?? $this->category ?? 'A Zone';
    }

    public function getChestNumberAttribute(): string
    {
        return (string) ($this->attributes['chest_number'] ?? $this->student_id ?? '');
    }

    /**
     * Calculate active individual programme participation count
     * considering student's own zone + Mix zone programmes.
     * Excludes cancelled, rejected, and withdrawn registrations.
     */
    public function getIndividualParticipationCount(): int
    {
        return $this->entries()
            ->whereIn('status', ['pending', 'verified', 'confirmed'])
            ->whereHas('program', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('type', 'individual')
                        ->orWhere('individual_limit_counted', true);
                });
            })
            ->count();
    }

    /**
     * Remaining individual slots available out of 5.
     */
    public function getRemainingIndividualSlots(): int
    {
        return max(0, self::MAX_INDIVIDUAL_PROGRAMS - $this->getIndividualParticipationCount());
    }

    /**
     * Check if 5-programme limit has been reached.
     */
    public function hasReachedIndividualLimit(): bool
    {
        return $this->getIndividualParticipationCount() >= self::MAX_INDIVIDUAL_PROGRAMS;
    }

    /**
     * Generate next sequential chest number for a given group.
     * Follows the highest chest number in that group.
     */
    public static function generateNextChestNumber(Group|int|null $group = null): string
    {
        if (is_numeric($group)) {
            $group = Group::find($group);
        }

        $baseStart = match (strtoupper($group?->code ?? '')) {
            'LUMO' => 1001,
            'PACTO' => 2001,
            'CONCO' => 3001,
            'UNIO' => 4001,
            'YUGO' => 5001,
            default => match ($group?->id) {
                4 => 1001,
                1 => 2001,
                6 => 3001,
                5 => 4001,
                2 => 5001,
                default => 1001,
            },
        };

        if (! $group) {
            return 'QF'.$baseStart;
        }

        $studentIds = self::where('group_id', $group->id)->pluck('student_id');
        $maxVal = 0;
        foreach ($studentIds as $sid) {
            if (preg_match('/(?:QF)?(\d+)/i', (string) $sid, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxVal) {
                    $maxVal = $num;
                }
            }
        }

        if ($maxVal < $baseStart) {
            return 'QF'.$baseStart;
        }

        return 'QF'.($maxVal + 1);
    }
}
