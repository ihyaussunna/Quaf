<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'stage_id',
        'start_time',
        'end_time',
        'status',
        'conflict_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    /**
     * Compute actual festival state based on time, explicit status, and results.
     */
    public function getComputedStatusAttribute(): string
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        // 1. Explicitly marked completed or program result published/completed
        if ($this->status === 'completed' || $this->program?->status === 'completed' || $this->program?->result) {
            return 'completed';
        }

        // 2. Check scheduled times against current time
        if ($this->start_time) {
            $now = now();
            $durationMinutes = (int) ($this->program?->duration_minutes ?: 60);
            $endTime = $this->end_time ?: $this->start_time->copy()->addMinutes($durationMinutes);

            if ($endTime->isPast()) {
                return 'completed';
            }

            if ($this->start_time->lte($now) && $endTime->gte($now)) {
                return 'live';
            }

            if ($this->start_time->isFuture()) {
                return 'upcoming';
            }
        }

        return $this->status ?: 'upcoming';
    }
}
