<?php

use App\Models\FestivalSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        FestivalSetting::set('is_final_standings_published', '0');
        FestivalSetting::set('public_standings_is_final', '0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
