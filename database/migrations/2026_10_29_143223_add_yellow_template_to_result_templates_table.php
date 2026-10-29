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
                'layout_mode' => 'custom',
                'result_x' => 645,
                'result_y' => 365,
                'result_size' => 135,
                'result_weight' => '800',
                'result_color' => '#D02627',
                'category_x' => 725,
                'category_y' => 134,
                'category_size' => 22,
                'category_weight' => '700',
                'category_color' => '#FFFFFF',
                'category_align' => 'center',
                'competition_x' => 725,
                'competition_y' => 285,
                'competition_size' => 44,
                'competition_weight' => '700',
                'competition_max_width' => 400,
                'competition_line_height' => 46,
                'competition_color' => '#000000',
                'competition_align' => 'center',
                'block_left' => 220,
                'first_top' => 130,
                'row_gap' => 175,
                'item_gap' => 0,
                'medal_size' => 0,
                'winner_name_size' => 30,
                'winner_name_weight' => '700',
                'winner_name_color' => '#2D2D2D',
                'winner_unit_size' => 20,
                'winner_unit_weight' => '400',
                'winner_unit_color' => '#555555',
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
