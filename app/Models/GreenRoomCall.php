<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GreenRoomCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'entry_id',
        'order_num',
        'status',
        'called_at',
        'stage_entered_at',
    ];

    protected function casts(): array
    {
        return [
            'called_at' => 'datetime',
            'stage_entered_at' => 'datetime',
        ];
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
