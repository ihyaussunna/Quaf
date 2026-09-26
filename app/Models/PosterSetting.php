<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosterSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'layout_mode',
        'result_x',
        'result_y',
        'result_size',
        'result_weight',
        'result_color',
        'category_x',
        'category_y',
        'category_size',
        'category_weight',
        'category_color',
        'category_align',
        'competition_x',
        'competition_y',
        'competition_size',
        'competition_weight',
        'competition_max_width',
        'competition_line_height',
        'competition_color',
        'competition_align',
        'block_left',
        'first_top',
        'row_gap',
        'item_gap',
        'medal_size',
        'winner_name_size',
        'winner_name_weight',
        'winner_name_color',
        'winner_unit_size',
        'winner_unit_weight',
        'winner_unit_color',
        'sponsor_image',
        'sponsor_enabled',
    ];

    protected function casts(): array
    {
        return [
            'sponsor_enabled' => 'boolean',
            'result_x' => 'float',
            'result_y' => 'float',
            'result_size' => 'float',
            'category_x' => 'float',
            'category_y' => 'float',
            'category_size' => 'float',
            'competition_x' => 'float',
            'competition_y' => 'float',
            'competition_size' => 'float',
            'competition_max_width' => 'float',
            'competition_line_height' => 'float',
            'block_left' => 'float',
            'first_top' => 'float',
            'row_gap' => 'float',
            'item_gap' => 'float',
            'medal_size' => 'float',
            'winner_name_size' => 'float',
            'winner_unit_size' => 'float',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ResultTemplate::class, 'template_id');
    }

    /**
     * Get default settings array.
     */
    public static function defaultSettings(): array
    {
        return [
            'layout_mode' => 'default',
            'result_x' => 733,
            'result_y' => 238,
            'result_size' => 78,
            'result_weight' => '700',
            'result_color' => 'theme',
            'category_x' => 540,
            'category_y' => 286,
            'category_size' => 31,
            'category_weight' => '400',
            'category_color' => 'white',
            'category_align' => 'center',
            'competition_x' => 540,
            'competition_y' => 338,
            'competition_size' => 46,
            'competition_weight' => '700',
            'competition_max_width' => 650,
            'competition_line_height' => 46,
            'competition_color' => 'white',
            'competition_align' => 'center',
            'block_left' => 380,
            'first_top' => 460,
            'row_gap' => 98,
            'item_gap' => 14,
            'medal_size' => 60,
            'winner_name_size' => 32,
            'winner_name_weight' => '700',
            'winner_name_color' => 'white',
            'winner_unit_size' => 22,
            'winner_unit_weight' => '400',
            'winner_unit_color' => 'white',
            'sponsor_enabled' => false,
        ];
    }
}
