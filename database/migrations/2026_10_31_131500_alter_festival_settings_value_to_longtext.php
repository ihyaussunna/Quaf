<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Upgrade festival_settings.value to LONGTEXT
        if (Schema::hasTable('festival_settings')) {
            $driver = Schema::getConnection()->getDriverName();
            if (in_array($driver, ['mysql', 'mariadb'])) {
                DB::statement('ALTER TABLE festival_settings MODIFY value LONGTEXT NULL');
            } else {
                try {
                    Schema::table('festival_settings', function (Blueprint $table) {
                        $table->longText('value')->nullable()->change();
                    });
                } catch (Throwable $e) {
                    // Ignore for drivers that do not support change() without doctrine/dbal
                }
            }
        }

        // 2. Upgrade result_templates.default_settings to LONGTEXT
        if (Schema::hasTable('result_templates')) {
            $driver = Schema::getConnection()->getDriverName();
            if (in_array($driver, ['mysql', 'mariadb'])) {
                try {
                    DB::statement('ALTER TABLE result_templates MODIFY default_settings LONGTEXT NULL');
                } catch (Throwable $e) {
                }
            }
        }

        // 3. Upgrade results.custom_poster_settings to LONGTEXT
        if (Schema::hasTable('results')) {
            $driver = Schema::getConnection()->getDriverName();
            if (in_array($driver, ['mysql', 'mariadb'])) {
                try {
                    DB::statement('ALTER TABLE results MODIFY custom_poster_settings LONGTEXT NULL');
                } catch (Throwable $e) {
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('festival_settings')) {
            $driver = Schema::getConnection()->getDriverName();
            if (in_array($driver, ['mysql', 'mariadb'])) {
                DB::statement('ALTER TABLE festival_settings MODIFY value TEXT NULL');
            }
        }
    }
};
