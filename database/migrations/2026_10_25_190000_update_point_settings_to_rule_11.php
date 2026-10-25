<?php

use App\Models\PointSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        PointSetting::query()->update([
            'first_place_points' => 5,
            'second_place_points' => 3,
            'third_place_points' => 1,
            'participation_points' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        PointSetting::query()->update([
            'first_place_points' => 10,
            'second_place_points' => 7,
            'third_place_points' => 5,
            'participation_points' => 1,
        ]);
    }
};
