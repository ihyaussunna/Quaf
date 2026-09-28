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
        Schema::table('programs', function (Blueprint $table) {
            if (! Schema::hasColumn('programs', 'has_time_limit')) {
                $table->boolean('has_time_limit')->default(true)->after('rules');
            }
            if (! Schema::hasColumn('programs', 'has_criteria')) {
                $table->boolean('has_criteria')->default(true)->after('has_time_limit');
            }
            $table->unsignedInteger('duration_minutes')->nullable()->change();
        });

        // Sync initial state for programs that already have 0 criteria
        DB::table('programs')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('scoring_criteria')
                    ->whereColumn('scoring_criteria.program_id', 'programs.id');
            })
            ->update(['has_criteria' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            if (Schema::hasColumn('programs', 'has_criteria')) {
                $table->dropColumn('has_criteria');
            }
            if (Schema::hasColumn('programs', 'has_time_limit')) {
                $table->dropColumn('has_time_limit');
            }
        });
    }
};
