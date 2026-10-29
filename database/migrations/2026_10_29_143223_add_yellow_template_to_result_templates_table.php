<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $existing = DB::table('result_templates')
            ->where('name', 'QUAF Yellow Modern Poster (Result Final 1)')
            ->first();

        if ($existing) {
            $templateId = $existing->id;
            DB::table('result_templates')->where('id', $templateId)->update([
                'image_path' => '/images/result-templates/quaf_template_yellow.png',
                'is_active' => true,
                'updated_at' => now(),
            ]);
        } else {
            $templateId = DB::table('result_templates')->insertGetId([
                'name' => 'QUAF Yellow Modern Poster (Result Final 1)',
                'image_path' => '/images/result-templates/quaf_template_yellow.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('poster_settings')->updateOrInsert(
            ['template_id' => $templateId],
            [
                'layout_mode' => 'yellow_modern',
                'result_x' => 520,
                'result_y' => 850,
                'result_size' => 240,
                'result_weight' => '800',
                'result_color' => '#D02627',
                'category_x' => 710,
                'category_y' => 165,
                'category_size' => 24,
                'category_weight' => '700',
                'category_color' => '#FFFFFF',
                'category_align' => 'center',
                'competition_x' => 580,
                'competition_y' => 225,
                'competition_size' => 54,
                'competition_weight' => '800',
                'competition_max_width' => 420,
                'competition_line_height' => 54,
                'competition_color' => '#000000',
                'competition_align' => 'left',
                'block_left' => 200,
                'first_top' => 135,
                'row_gap' => 175,
                'item_gap' => 0,
                'medal_size' => 0,
                'winner_name_size' => 32,
                'winner_name_weight' => '700',
                'winner_name_color' => '#D02627',
                'winner_unit_size' => 22,
                'winner_unit_weight' => '500',
                'winner_unit_color' => '#222222',
                'sponsor_enabled' => false,
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $existing = DB::table('result_templates')
            ->where('name', 'QUAF Yellow Modern Poster (Result Final 1)')
            ->first();

        if ($existing) {
            DB::table('poster_settings')->where('template_id', $existing->id)->delete();
            DB::table('result_templates')->where('id', $existing->id)->delete();
        }
    }
};
