<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'online_submission_form_id',
        'program_id',
        'program_entry_id',
        'code_letter',
        'chest_number',
        'student_name',
        'student_id',
        'group_id',
        'text_content',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'video_url',
        'ip_address',
        'submitted_at',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'file_size' => 'integer',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(OnlineSubmissionForm::class, 'online_submission_form_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function programEntry(): BelongsTo
    {
        return $this->belongsTo(ProgramEntry::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return route('online-submission.file', $this->id);
    }

    public function isImage(): bool
    {
        $ext = strtolower($this->file_type ?: pathinfo($this->file_name ?? (string) $this->file_path, PATHINFO_EXTENSION));

        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'bmp'], true);
    }

    public function isPdf(): bool
    {
        $ext = strtolower($this->file_type ?: pathinfo($this->file_name ?? (string) $this->file_path, PATHINFO_EXTENSION));

        return $ext === 'pdf';
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (! $this->file_size) {
            return '';
        }

        if ($this->file_size >= 1048576) {
            return number_format($this->file_size / 1048576, 2).' MB';
        }

        if ($this->file_size >= 1024) {
            return number_format($this->file_size / 1024, 2).' KB';
        }

        return $this->file_size.' bytes';
    }
}
