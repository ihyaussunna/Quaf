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
        'A Zone' => 'A Zone (Class 4: NF4, UH4, S4, ID4, UT4, L4, TQS)',
        'B Zone' => 'B Zone (Class 3: NF3, ID3, UH3, UT3, S3, L3)',
        'C Zone' => 'C Zone (Class 1 & 2: U1, U2, L2, S1, S2)',
        'Mix Zone' => 'Mix Zone (Open Category)',
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
        'purchase_pin',
        'points_cache',
        'coins_cache',
        'recognition_gift_delivered',
        'recognition_gift_delivered_at',
        'recognition_gift_delivered_by',
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
            'coins_cache' => 'integer',
            'recognition_gift_delivered' => 'boolean',
            'recognition_gift_delivered_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (Student $student) {
            $chest = (string) ($student->chest_number ?: $student->student_id);
            $originalChest = (string) ($student->getOriginal('student_id') ?: $chest);

            // 1. Sync User table if user exists
            if ($student->wasChanged('name') && $student->user_id) {
                try {
                    User::where('id', $student->user_id)->update([
                        'name' => $student->name,
                    ]);
                } catch (\Throwable $e) {
                    // Silently ignore user sync issue
                }
            }

            // 2. Sync OnlineSubmission table (identified by entry IDs or chest number string)
            if ($student->wasChanged('name') || $student->wasChanged('student_id')) {
                try {
                    $entryIds = ProgramEntry::where('student_id', $student->id)->pluck('id')->all();

                    $submissionQuery = OnlineSubmission::where(function ($q) use ($chest, $originalChest, $entryIds) {
                        $hasCond = false;
                        if (! empty($entryIds)) {
                            $q->whereIn('program_entry_id', $entryIds);
                            $hasCond = true;
                        }
                        if ($chest !== '') {
                            if ($hasCond) {
                                $q->orWhere('chest_number', $chest)
                                    ->orWhere('student_id', $chest);
                            } else {
                                $q->where('chest_number', $chest)
                                    ->orWhere('student_id', $chest);
                                $hasCond = true;
                            }
                        }
                        if ($originalChest !== '' && $originalChest !== $chest) {
                            $q->orWhere('chest_number', $originalChest)
                                ->orWhere('student_id', $originalChest);
                        }
                    });

                    $submissionUpdates = [];
                    if ($student->wasChanged('name')) {
                        $submissionUpdates['student_name'] = $student->name;
                    }
                    if ($student->wasChanged('student_id') && $chest !== '') {
                        $submissionUpdates['chest_number'] = $chest;
                        $submissionUpdates['student_id'] = $chest;
                    }

                    if (! empty($submissionUpdates)) {
                        $submissionQuery->update($submissionUpdates);
                    }
                } catch (\Throwable $e) {
                    // Silently ignore online submission sync issue
                }
            }

            // 3. Sync FundReceipt collector_name if linked to student
            if ($student->wasChanged('name') || $student->wasChanged('student_id')) {
                try {
                    FundReceipt::where('student_id', $student->id)->update([
                        'collector_name' => $student->name.' ('.$chest.')',
                    ]);
                } catch (\Throwable $e) {
                    // Silently ignore fund receipt sync issue
                }
            }

            // 4. Sync static qf-directory.json if present
            if ($student->wasChanged('name') || $student->wasChanged('student_id')) {
                self::syncDirectoryJson($student);
            }
        });
    }

    public static function syncDirectoryJson(Student $student): void
    {
        try {
            $jsonPath = public_path('data/qf-directory.json');
            if (! file_exists($jsonPath) || ! is_writable($jsonPath)) {
                return;
            }

            $raw = json_decode(file_get_contents($jsonPath), true);
            if (! is_array($raw)) {
                return;
            }

            $chest = (string) ($student->chest_number ?: $student->student_id);
            $newName = (string) $student->name;
            $newClass = (string) ($student->class_level ?: ($student->category ?: ''));
            $newZone = (string) ($student->zone?->name ?: ($student->category ?: ''));

            $updated = false;
            foreach ($raw as $teamName => &$teamData) {
                if (! isset($teamData['members']) || ! is_array($teamData['members'])) {
                    continue;
                }
                foreach ($teamData['members'] as &$member) {
                    if (strcasecmp((string) ($member['qf'] ?? ''), $chest) === 0) {
                        $member['name'] = $newName;
                        if ($newClass) {
                            $member['class'] = $newClass;
                        }
                        if ($newZone) {
                            $member['zone'] = $newZone;
                        }
                        $updated = true;
                        break 2;
                    }
                }
            }
            unset($member, $teamData);

            if ($updated) {
                file_put_contents($jsonPath, json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        } catch (\Throwable $e) {
            // Silently ignore static JSON sync failures
        }
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

    public function coinTransactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class);
    }

    public function fundReceipts(): HasMany
    {
        return $this->hasMany(FundReceipt::class, 'student_id');
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

    public function getPurchasePinAttribute(?string $value): string
    {
        if (! empty($value)) {
            return (string) $value;
        }

        $token = $this->qr_token ?: $this->student_id ?: (string) $this->id;

        return str_pad((string) (abs(crc32($token)) % 9000 + 1000), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope query to only students who participate in at least one competition (group or individual).
     */
    public function scopeParticipants($query)
    {
        return $query->where(function ($q) {
            $q->has('entries')
                ->orWhereHas('participations')
                ->orWhereHas('certificates')
                ->orWhere('points_cache', '>', 0);
        });
    }

    /**
     * Check if this student is an active participant in any competition (group or individual).
     */
    public function isCompetitionParticipant(): bool
    {
        if (array_key_exists('entries_count', $this->attributes) && array_key_exists('participations_count', $this->attributes)) {
            if (($this->entries_count + $this->participations_count) > 0) {
                return true;
            }
        }

        return $this->entries()->exists()
            || $this->participations()->exists()
            || $this->certificates()->exists()
            || ($this->points_cache ?? 0) > 0;
    }

    /**
     * Get the festival gift name applicable to this student:
     * - Competition participants get 'QUAF Recognition Gift'
     * - Non-participating students get 'QUAF Qalam' (ക്വാഫ് ഖലം)
     */
    public function getGiftNameAttribute(): string
    {
        return $this->isCompetitionParticipant()
            ? 'QUAF Recognition Gift'
            : 'QUAF Qalam (ക്വാഫ് ഖലം)';
    }

    /**
     * Get Malayalam descriptive title for the festival gift.
     */
    public function getGiftMalayalamTitleAttribute(): string
    {
        return $this->isCompetitionParticipant()
            ? 'ക്വാഫ് റെക്കഗ്നിഷൻ ഗിഫ്റ്റ് (QUAF Recognition Gift)'
            : 'ക്വാഫ് ഖലം സമ്മാനം (QUAF Qalam Gift)';
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
                $q->where('type', 'individual')
                    ->where(function ($sub) {
                        $sub->whereNull('individual_limit_counted')
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
