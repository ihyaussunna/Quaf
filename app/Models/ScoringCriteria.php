<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoringCriteria extends Model
{
    use HasFactory;

    protected $table = 'scoring_criteria';

    protected $fillable = [
        'program_id',
        'criterion_name',
        'max_marks',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
