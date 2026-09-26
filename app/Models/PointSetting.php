<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_place_points',
        'second_place_points',
        'third_place_points',
        'participation_points',
        'group_multiplier',
    ];

    protected function casts(): array
    {
        return [
            'group_multiplier' => 'decimal:2',
        ];
    }
}
