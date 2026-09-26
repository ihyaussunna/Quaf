<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',
        'category',
        'group_id',
        'stage_id',
        'is_featured',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }
}
