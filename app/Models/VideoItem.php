<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'youtube_id',
        'thumbnail_path',
        'category',
        'is_live',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_live' => 'boolean',
            'display_order' => 'integer',
        ];
    }
}
