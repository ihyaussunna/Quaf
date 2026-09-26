<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JudgeAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'judge_id',
        'program_id',
        'status',
    ];

    public function judge(): BelongsTo
    {
        return $this->belongsTo(Judge::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
