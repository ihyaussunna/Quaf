<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'first_entry_id',
        'second_entry_id',
        'third_entry_id',
        'status',
        'verified_by',
        'published_at',
        'remarks',
        'poster_image',
        'template_id',
        'custom_poster_settings',
        'is_media_published',
        'media_published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'media_published_at' => 'datetime',
            'is_media_published' => 'boolean',
            'custom_poster_settings' => 'array',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function firstEntry(): BelongsTo
    {
        return $this->belongsTo(ProgramEntry::class, 'first_entry_id');
    }

    public function secondEntry(): BelongsTo
    {
        return $this->belongsTo(ProgramEntry::class, 'second_entry_id');
    }

    public function thirdEntry(): BelongsTo
    {
        return $this->belongsTo(ProgramEntry::class, 'third_entry_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ResultTemplate::class, 'template_id');
    }

    /**
     * Get ONLY 1st, 2nd, and 3rd place winners for poster generation.
     */
    public function getPosterWinners(): array
    {
        $winners = [
            'first' => [],
            'second' => [],
            'third' => [],
        ];

        if ($this->firstEntry) {
            $name = $this->firstEntry->student->name ?? ($this->firstEntry->group->name ?? '1st Place Winner');
            $unit = $this->firstEntry->student->group->name ?? ($this->firstEntry->group->name ?? 'House');
            $chest = $this->firstEntry->chest_number ?? ($this->firstEntry->student->student_id ?? '');

            $winners['first'][] = [
                'name' => $name,
                'unit' => $unit,
                'chest' => $chest,
                'medal' => '/images/medals/first.png',
            ];
        }

        if ($this->secondEntry) {
            $name = $this->secondEntry->student->name ?? ($this->secondEntry->group->name ?? '2nd Place Winner');
            $unit = $this->secondEntry->student->group->name ?? ($this->secondEntry->group->name ?? 'House');
            $chest = $this->secondEntry->chest_number ?? ($this->secondEntry->student->student_id ?? '');

            $winners['second'][] = [
                'name' => $name,
                'unit' => $unit,
                'chest' => $chest,
                'medal' => '/images/medals/second.png',
            ];
        }

        if ($this->thirdEntry) {
            $name = $this->thirdEntry->student->name ?? ($this->thirdEntry->group->name ?? '3rd Place Winner');
            $unit = $this->thirdEntry->student->group->name ?? ($this->thirdEntry->group->name ?? 'House');
            $chest = $this->thirdEntry->chest_number ?? ($this->thirdEntry->student->student_id ?? '');

            $winners['third'][] = [
                'name' => $name,
                'unit' => $unit,
                'chest' => $chest,
                'medal' => '/images/medals/third.png',
            ];
        }

        return $winners;
    }
}
