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
        if (Schema::hasTable('program_entries')) {
            DB::table('program_entries')->update([
                'attendance_status' => 'waiting',
                'code_letter' => null,
            ]);
        }

        if (Schema::hasTable('green_room_calls')) {
            DB::table('green_room_calls')->delete();
        }

        if (Schema::hasTable('programs')) {
            DB::table('programs')->update([
                'status' => 'upcoming',
                'is_call_list_locked' => false,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration required
    }
};
