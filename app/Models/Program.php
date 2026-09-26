<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'duration_minutes',
        'stage_id',
        'scheduled_time',
        'points_weight',
        'status',
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
            'eligibility_rules' => 'array',
            'is_stage' => 'boolean',
        ];
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
}
