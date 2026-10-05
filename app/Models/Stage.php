<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'map_url',
        'capacity',
        'current_program_id',
        'next_program_id',
        'status',
    ];

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function currentProgram(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'current_program_id');
    }

    public function nextProgram(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'next_program_id');
    }

    public function getVenueAttribute(): string
    {
        return $this->location ?? $this->code;
    }
}
