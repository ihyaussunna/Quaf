<?php

use App\Models\Program;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('programs')) {
            return;
        }

        if (Program::count() === 0) {
            return;
        }

        $programs = Program::all();
        foreach ($programs as $program) {
            $isGroup = $program->isGroup();
            if ($isGroup) {
                $limit = max(1, (int) ($program->participant_count ?: ($program->max_participants ?: 2)));
                $program->participant_count = $limit;
                $program->max_participants = $limit;
                $program->max_participants_per_group = 1;
            } else {
                $limit = max(1, (int) ($program->participant_count ?: ($program->max_participants_per_group ?: 1)));
                $program->participant_count = $limit;
                $program->max_participants_per_group = $limit;
                $program->max_participants = $limit * 5;
            }
            $program->saveQuietly();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed for data synchronization
    }
};
