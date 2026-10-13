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
        FestivalSetting::set('launch_status', 'launched');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        FestivalSetting::set('launch_status', 'coming_soon');
    }
};
