<?php

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
        if (Schema::hasTable('poster_settings') && ! Schema::hasColumn('poster_settings', 'show_winner_divider')) {
            Schema::table('poster_settings', function (Blueprint $table) {
                $table->boolean('show_winner_divider')->default(false)->after('winner_unit_color');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('poster_settings') && Schema::hasColumn('poster_settings', 'show_winner_divider')) {
            Schema::table('poster_settings', function (Blueprint $table) {
                $table->dropColumn('show_winner_divider');
            });
        }
    }
};
