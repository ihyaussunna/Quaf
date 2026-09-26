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
        // 1. Programs: add is_stage and gender_restriction
        Schema::table('programs', function (Blueprint $table) {
            if (! Schema::hasColumn('programs', 'is_stage')) {
                $table->boolean('is_stage')->default(true)->after('type');
            }
            if (! Schema::hasColumn('programs', 'gender_restriction')) {
                $table->string('gender_restriction', 10)->default('all')->after('is_stage'); // all, male, female
            }
        });

        // 2. Groups (Teams): add manager details
        Schema::table('groups', function (Blueprint $table) {
            if (! Schema::hasColumn('groups', 'manager_name')) {
                $table->string('manager_name')->nullable()->after('leader_id');
            }
            if (! Schema::hasColumn('groups', 'manager_contact')) {
                $table->string('manager_contact', 30)->nullable()->after('manager_name');
            }
        });

        // 3. Stages: add map_url
        Schema::table('stages', function (Blueprint $table) {
            if (! Schema::hasColumn('stages', 'map_url')) {
                $table->string('map_url')->nullable()->after('location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['is_stage', 'gender_restriction']);
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['manager_name', 'manager_contact']);
        });

        Schema::table('stages', function (Blueprint $table) {
            $table->dropColumn(['map_url']);
        });
    }
};
