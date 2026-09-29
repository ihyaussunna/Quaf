<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('programs')) {
            $updates = [
                'status' => 'upcoming',
            ];

            if (Schema::hasColumn('programs', 'is_call_list_locked')) {
                $updates['is_call_list_locked'] = false;
            }

            DB::table('programs')->update($updates);
        }

        if (Schema::hasTable('results')) {
            DB::table('results')->delete();
        }

        if (Schema::hasTable('score_sheets')) {
            DB::table('score_sheets')->delete();
        }

        if (Schema::hasTable('green_room_calls')) {
            DB::table('green_room_calls')->delete();
        }

        if (Schema::hasTable('program_entry_participants')) {
            DB::table('program_entry_participants')->delete();
        }

        if (Schema::hasTable('program_entries')) {
            DB::table('program_entries')->delete();
        }

        if (Schema::hasTable('points_transactions')) {
            DB::table('points_transactions')->delete();
        }

        if (Schema::hasTable('groups')) {
            DB::table('groups')->update([
                'points_cache' => 0,
                'rank_cache' => 1,
            ]);
        }

        if (Schema::hasTable('students')) {
            DB::table('students')->update([
                'points_cache' => 0,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive rollback
    }
};
