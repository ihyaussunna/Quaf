<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProgramEntry extends Model
{
    use HasFactory;

    public const ACTIVE_STATUSES = ['pending', 'verified', 'confirmed'];

    public const INACTIVE_STATUSES = ['cancelled', 'rejected', 'withdrawn'];

    protected $fillable = [
        'program_id',
        'student_id',
        'group_id',
        'chest_number',
        'code_letter',
        'attendance_status',
        'status',
        'conflict_flag',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'conflict_flag' => 'boolean',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'program_entry_participants', 'entry_id', 'student_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function scoreSheets(): HasMany
    {
        return $this->hasMany(ScoreSheet::class, 'entry_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(ScoreSheet::class, 'entry_id');
    }

    public function greenRoomCall(): HasOne
    {
        return $this->hasOne(GreenRoomCall::class, 'entry_id');
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class, 'entry_id');
    }

    public function pointsTransactions(): HasMany
    {
        return $this->hasMany(PointsTransaction::class, 'entry_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES);
    }

    public function isGroupEntry(): bool
    {
        return $this->program?->isGroup() || $this->participants()->exists();
    }

    public function leaderStudent(): ?Student
    {
        if ($this->student) {
            return $this->student;
        }

        $participants = $this->relationLoaded('participants')
            ? $this->participants
            : $this->participants()->get();

        $captain = $participants->first(fn ($p) => in_array($p->pivot?->role, ['captain', 'leader'], true));

        return $captain ?? $participants->first();
    }
}
