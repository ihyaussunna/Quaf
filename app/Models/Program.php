<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class Program extends Model
{
    use HasFactory;

    public const ZONES = [
        'A Zone' => 'A Zone (റാബിഅ, തഖസ്സുസ്)',
        'B Zone' => 'B Zone (സാലിസ)',
        'C Zone' => 'C Zone (ഊല, സാനി)',
        'Mix Zone' => 'Mix Zone (എല്ലാ സോണുകൾക്കും)',
    ];

    protected $fillable = [
        'name',
        'malayalam_name',
        'code',
        'zone_id',
        'category_id',
        'type',
        'participant_count',
        'max_participants',
        'max_participants_per_group',
        'individual_limit_counted',
        'mix_zone_open_to_all',
        'eligibility_rules',
        'is_stage',
        'gender_restriction',
        'eligibility',
        'rules',
        'has_time_limit',
        'duration_minutes',
        'has_criteria',
        'stage_id',
        'scheduled_time',
        'points_weight',
        'status',
        'is_call_list_locked',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_time' => 'datetime',
            'points_weight' => 'decimal:2',
            'max_participants' => 'integer',
            'max_participants_per_group' => 'integer',
            'individual_limit_counted' => 'boolean',
            'mix_zone_open_to_all' => 'boolean',
            'is_call_list_locked' => 'boolean',
            'eligibility_rules' => 'array',
            'is_stage' => 'boolean',
            'has_time_limit' => 'boolean',
            'has_criteria' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Program $program) {
            self::ensureSchema();

            // Guard against legacy database tables missing new columns
            if (! Schema::hasColumn('programs', 'has_time_limit')) {
                unset($program->attributes['has_time_limit']);
            }
            if (! Schema::hasColumn('programs', 'has_criteria')) {
                unset($program->attributes['has_criteria']);
            }
            if (! Schema::hasColumn('programs', 'is_call_list_locked')) {
                unset($program->attributes['is_call_list_locked']);
            }

            // Keep participant limits synchronized across all columns
            if ($program->isGroup()) {
                if ($program->isDirty('participant_count') && $program->participant_count !== null) {
                    $limit = (int) $program->participant_count;
                } elseif ($program->isDirty('max_participants') && $program->max_participants !== null) {
                    $limit = (int) $program->max_participants;
                } else {
                    $limit = (int) ($program->participant_count ?: ($program->max_participants ?: 2));
                }

                $limit = max(1, $limit);
                $program->participant_count = $limit;
                $program->max_participants = $limit;
                $program->max_participants_per_group = 1;
            } else {
                if ($program->isDirty('participant_count') && $program->participant_count !== null) {
                    $limit = (int) $program->participant_count;
                } elseif ($program->isDirty('max_participants_per_group') && $program->max_participants_per_group !== null) {
                    $limit = (int) $program->max_participants_per_group;
                } elseif ($program->isDirty('max_participants') && $program->max_participants !== null) {
                    $limit = max(1, (int) round($program->max_participants / 5));
                } else {
                    $limit = (int) ($program->participant_count ?: ($program->max_participants_per_group ?: 1));
                }

                $limit = max(1, $limit);
                $program->participant_count = $limit;
                $program->max_participants_per_group = $limit;
                $program->max_participants = $limit * 5;
            }
        });
    }

    public static function ensureSchema(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        try {
            if (! Schema::hasTable('programs')) {
                return;
            }

            if (! Schema::hasColumn('programs', 'is_call_list_locked')) {
                Schema::table('programs', function (Blueprint $table) {
                    $table->boolean('is_call_list_locked')->default(false)->after('status');
                });
            }
            if (! Schema::hasColumn('programs', 'has_time_limit')) {
                Schema::table('programs', function (Blueprint $table) {
                    $table->boolean('has_time_limit')->default(true)->after('rules');
                });
            }
            if (! Schema::hasColumn('programs', 'has_criteria')) {
                Schema::table('programs', function (Blueprint $table) {
                    $table->boolean('has_criteria')->default(true)->after('has_time_limit');
                });
            }
            $checked = true;
        } catch (\Throwable) {
            try {
                Artisan::call('migrate', ['--force' => true]);
                $checked = true;
            } catch (\Throwable) {
            }
        }
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProgramCategory::class, 'category_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ProgramEntry::class);
    }

    public function schedule(): HasOne
    {
        return $this->hasOne(Schedule::class);
    }

    public function scoringCriteria(): HasMany
    {
        return $this->hasMany(ScoringCriteria::class);
    }

    public function scoreSheets(): HasMany
    {
        return $this->hasMany(ScoreSheet::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(Result::class);
    }

    public function greenRoomCalls(): HasMany
    {
        return $this->hasMany(GreenRoomCall::class);
    }

    public function judgeAssignments(): HasMany
    {
        return $this->hasMany(JudgeAssignment::class);
    }

    public function judges(): BelongsToMany
    {
        return $this->belongsToMany(Judge::class, 'judge_assignments');
    }

    public function pointsTransactions(): HasMany
    {
        return $this->hasMany(PointsTransaction::class);
    }

    public function isIndividual(): bool
    {
        return strtolower($this->type) === 'individual';
    }

    public function isGroup(): bool
    {
        return strtolower($this->type) === 'group';
    }

    public function isMixZone(): bool
    {
        if ($this->zone) {
            return $this->zone->isMixZone();
        }

        return strtolower((string) $this->eligibility) === 'mix zone';
    }

    public function countsTowardIndividualLimit(): bool
    {
        if (! $this->isIndividual()) {
            return false;
        }

        return (bool) ($this->individual_limit_counted ?? true);
    }

    public function getZoneNameAttribute(): string
    {
        return $this->zone?->name ?? $this->eligibility ?? 'A Zone';
    }

    public function getLimitAttribute(): int
    {
        if ($this->isGroup()) {
            return max(1, (int) ($this->participant_count ?: ($this->max_participants ?: 2)));
        }

        return max(1, (int) ($this->participant_count ?: ($this->max_participants_per_group ?: 1)));
    }

    /**
     * Get the real-time attendance window state for Green Room call list.
     *
     * Rules:
     * 1. If admin manually locked ($this->is_call_list_locked == true): LOCKED.
     * 2. If program is not scheduled (no schedule and no scheduled_time): NOT SCHEDULED.
     * 3. Opens 10 minutes prior to scheduled start time: now >= (start_time - 10 minutes).
     * 4. Auto-locks once scheduled end time has passed: now > end_time (or status == completed).
     * 5. Active attendance window: (start_time - 10 minutes) <= now <= end_time.
     */
    public function getCallListWindowState(): array
    {
        if ((bool) ($this->is_call_list_locked ?? false)) {
            return [
                'is_open' => false,
                'state' => 'locked_by_admin',
                'badge' => 'Locked by Admin',
                'badge_ml' => 'അഡ്മിൻ ലോക്ക് ചെയ്തു',
                'badge_color' => 'red',
                'message' => 'കോൾ ലിസ്റ്റ് അഡ്മിൻ ലോക്ക് ചെയ്തിരിക്കുന്നു (Locked by Admin). ഗ്രീൻ റൂം എഡിറ്റിംഗ് അനുമതി ഒഴിവാക്കി.',
                'opens_at' => null,
                'closes_at' => null,
                'scheduled_start' => null,
                'minutes_until_open' => null,
            ];
        }

        $tz = config('app.timezone', 'Asia/Kolkata') ?: 'Asia/Kolkata';
        $now = Carbon::now($tz);

        $schedule = $this->schedule;
        $rawStart = $schedule?->getRawOriginal('start_time') ?? $schedule?->start_time ?? $this->scheduled_time;
        $startTime = $rawStart ? Carbon::parse($rawStart, $tz)->timezone($tz) : null;
        $duration = (int) ($this->duration_minutes ?: 30);
        $rawEnd = $schedule?->getRawOriginal('end_time') ?? $schedule?->end_time;
        $endTime = $rawEnd ? Carbon::parse($rawEnd, $tz)->timezone($tz) : ($startTime ? (clone $startTime)->addMinutes($duration) : null);

        if ($this->status === 'completed' || $this->status === 'published') {
            return [
                'is_open' => false,
                'state' => 'auto_locked_ended',
                'badge' => 'Completed (Locked)',
                'badge_ml' => 'പ്രോഗ്രാം പൂർത്തിയായി (ലോക്ക് ചെയ്തു)',
                'badge_color' => 'red',
                'message' => 'പ്രോഗ്രാം പൂർത്തിയായതിനാൽ കോൾ ലിസ്റ്റ് ഓട്ടോമാറ്റിക്കായി ലോക്ക് ചെയ്യപ്പെട്ടു.',
                'opens_at' => null,
                'closes_at' => null,
                'scheduled_start' => null,
                'minutes_until_open' => null,
            ];
        }

        if (! $startTime) {
            return [
                'is_open' => true,
                'state' => 'open_unscheduled',
                'badge' => 'Unscheduled (Open)',
                'badge_ml' => 'ഷെഡ്യൂൾ ചെയ്തിട്ടില്ല (തുറന്നത്)',
                'badge_color' => 'slate',
                'message' => 'പ്രോഗ്രാം നിർദ്ദിഷ്ട സമയത്തിൽ ഷെഡ്യൂൾ ചെയ്തിട്ടില്ല. ഏതുസമയത്തും ഹാജർ രേഖപ്പെടുത്താം.',
                'opens_at' => null,
                'closes_at' => null,
                'scheduled_start' => null,
                'minutes_until_open' => null,
            ];
        }

        $opensAt = (clone $startTime)->subMinutes(10);

        // 1. Has the scheduled time passed or status is completed? Auto-lock!
        if ($this->status === 'completed' || ($endTime && $now->greaterThan($endTime))) {
            return [
                'is_open' => false,
                'state' => 'auto_locked_ended',
                'badge' => 'Auto-Locked (Ended)',
                'badge_ml' => 'ഷെഡ്യൂൾ സമയം കഴിഞ്ഞു (Auto-Locked)',
                'badge_color' => 'red',
                'message' => 'ഷെഡ്യൂൾ ചെയ്ത സമയം ('.($endTime ? $endTime->format('h:i A') : '').') പൂർത്തിയായതിനാൽ കോൾ ലിസ്റ്റ് ഓട്ടോമാറ്റിക്കായി ലോക്ക് ചെയ്യപ്പെട്ടു.',
                'opens_at' => $opensAt,
                'closes_at' => $endTime,
                'scheduled_start' => $startTime,
                'minutes_until_open' => null,
            ];
        }

        // 2. Is it too early? (More than 10 mins before start)
        if ($now->lessThan($opensAt)) {
            $minutesLeft = max(1, (int) round($now->diffInMinutes($opensAt, false)));

            return [
                'is_open' => false,
                'state' => 'upcoming_window',
                'badge' => 'Opens 10m Before',
                'badge_ml' => '10 മിനിറ്റ് മുമ്പ് തുറക്കും',
                'badge_color' => 'amber',
                'message' => 'ഹാജർ രേഖപ്പെടുത്തൽ ഷെഡ്യൂൾ ചെയ്ത സമയത്തിന് 10 മിനിറ്റ് മുമ്പ് ('.$opensAt->format('h:i A').') മാത്രമേ ആരംഭിക്കൂ.',
                'opens_at' => $opensAt,
                'closes_at' => $endTime,
                'scheduled_start' => $startTime,
                'minutes_until_open' => $minutesLeft,
            ];
        }

        // 3. Active live window!
        return [
            'is_open' => true,
            'state' => 'open',
            'badge' => 'Open (Live)',
            'badge_ml' => 'തുറന്നിരിക്കുന്നു (Live)',
            'badge_color' => 'emerald',
            'message' => 'ഹാജർ പട്ടിക തുറന്നിരിക്കുന്നു (Attendance Window Active). മത്സരാർത്ഥികളുടെ സാന്നിധ്യം രേഖപ്പെടുത്താം.',
            'opens_at' => $opensAt,
            'closes_at' => $endTime,
            'scheduled_start' => $startTime,
            'minutes_until_open' => 0,
        ];
    }

    public function isCallListOpen(): bool
    {
        return (bool) ($this->getCallListWindowState()['is_open'] ?? false);
    }
}
