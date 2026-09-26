<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ResultTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_path',
        'is_active',
        'default_settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'default_settings' => 'array',
        ];
    }

    public function posterSetting(): HasOne
    {
        return $this->hasOne(PosterSetting::class, 'template_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class, 'template_id');
    }
}
