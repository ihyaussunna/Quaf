<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Judge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'access_code',
        'name',
        'designation',
        'bio',
        'specialization',
        'notes',
        'contact',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(JudgeAssignment::class);
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'judge_assignments');
    }

    public function scoreSheets(): HasMany
    {
        return $this->hasMany(ScoreSheet::class);
    }
}
