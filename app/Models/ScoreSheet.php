<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'judge_id',
        'program_id',
        'entry_id',
        'criteria_scores',
        'total_score',
        'remarks',
        'is_submitted',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'criteria_scores' => 'array',
            'total_score' => 'decimal:2',
            'is_submitted' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    public function judge(): BelongsTo
    {
        return $this->belongsTo(Judge::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(ProgramEntry::class);
    }
}
