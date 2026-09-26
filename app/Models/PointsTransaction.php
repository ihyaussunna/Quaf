<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'program_id',
        'result_id',
        'entry_id',
        'student_id',
        'source_type',
        'points',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(Result::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(ProgramEntry::class, 'entry_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
