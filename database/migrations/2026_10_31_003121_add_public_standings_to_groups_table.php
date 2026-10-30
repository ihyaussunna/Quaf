<?php

use App\Models\FestivalSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            if (! Schema::hasColumn('groups', 'public_points_cache')) {
                $table->integer('public_points_cache')->default(0)->after('points_cache');
            }
            if (! Schema::hasColumn('groups', 'public_rank_cache')) {
                $table->integer('public_rank_cache')->default(1)->after('rank_cache');
            }
        });

        // Default milestone settings: 10, 15, 20, 27, 35, 40 as requested by committee
        if (! FestivalSetting::get('public_standings_milestones')) {
            FestivalSetting::set('public_standings_milestones', '10,15,20,27,35,40');
        }
        if (! FestivalSetting::get('public_standings_mode')) {
            FestivalSetting::set('public_standings_mode', 'milestone');
        }
        if (! FestivalSetting::get('public_standings_results_count')) {
            FestivalSetting::set('public_standings_results_count', '0');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            if (Schema::hasColumn('groups', 'public_points_cache')) {
                $table->dropColumn('public_points_cache');
            }
            if (Schema::hasColumn('groups', 'public_rank_cache')) {
                $table->dropColumn('public_rank_cache');
            }
        });
    }
};
