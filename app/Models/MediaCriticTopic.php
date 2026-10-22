<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaCriticTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_number',
        'code_letter',
        'title',
        'group_id',
        'picked_by_user_id',
        'picked_at',
        'selection_method',
        'is_locked',
    ];

    protected $casts = [
        'topic_number' => 'integer',
        'is_locked' => 'boolean',
        'picked_at' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function pickedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'picked_by_user_id');
    }

    public function isClaimed(): bool
    {
        return $this->group_id !== null && $this->is_locked;
    }
}
