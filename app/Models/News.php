<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category',
        'cover_image',
        'is_featured',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Resolve cover image to complete browser-loadable URL.
     */
    public function getCoverImageAttribute(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            $parsed = parse_url($value);
            if (isset($parsed['path']) && str_starts_with($parsed['path'], '/storage/')) {
                return asset(ltrim($parsed['path'], '/'));
            }

            return $value;
        }

        $clean = ltrim($value, '/');
        if (str_starts_with($clean, 'storage/')) {
            return asset($clean);
        }

        return asset('storage/'.$clean);
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image;
    }
}
