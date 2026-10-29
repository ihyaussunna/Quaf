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
                'result_x' => 720,
                'result_y' => 940,
                'result_size' => 480,
                'result_weight' => '400',
                'result_color' => '#D02627',
                'category_x' => 687,
                'category_y' => 168,
                'category_size' => 45,
                'category_weight' => '400',
                'category_color' => '#FFFFFF',
                'category_align' => 'center',
                'competition_x' => 585,
                'competition_y' => 245,
                'competition_size' => 85,
                'competition_weight' => '700',
                'competition_max_width' => 420,
                'competition_line_height' => 88,
                'competition_color' => '#000000',
                'competition_align' => 'left',
                'block_left' => 201,
                'first_top' => 120,
                'row_gap' => 175,
                'item_gap' => 0,
                'medal_size' => 0,
                'winner_name_size' => 34,
                'winner_name_weight' => '400',
                'winner_name_color' => '#D02627',
                'winner_unit_size' => 26,
                'winner_unit_weight' => '300',
                'winner_unit_color' => '#000000',
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
