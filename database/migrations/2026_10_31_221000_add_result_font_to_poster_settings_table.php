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
        if (Schema::hasTable('poster_settings') && ! Schema::hasColumn('poster_settings', 'result_font')) {
            Schema::table('poster_settings', function (Blueprint $table) {
                $table->string('result_font')->default('Rockwell Std')->after('result_weight');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('poster_settings') && Schema::hasColumn('poster_settings', 'result_font')) {
            Schema::table('poster_settings', function (Blueprint $table) {
                $table->dropColumn('result_font');
            });
        }
    }
};
