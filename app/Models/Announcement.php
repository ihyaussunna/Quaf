<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'priority',
        'target_role',
        'target_group_id',
        'target_stage_id',
        'target_program_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function targetGroup(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'target_group_id');
    }

    public function targetStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'target_stage_id');
    }

    public function targetProgram(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'target_program_id');
    }
}
