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
        Schema::table('students', function (Blueprint $table) {
            $table->index('group_id');
            $table->index('zone_id');
            $table->index('category');
            $table->index('class_level');
        });

        Schema::table('program_entries', function (Blueprint $table) {
            $table->index('group_id');
            $table->index('student_id');
            $table->index('status');
            $table->index(['program_id', 'status']);
            $table->index(['group_id', 'status']);
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->index('zone_id');
            $table->index('category_id');
            $table->index('stage_id');
            $table->index('status');
            $table->index('type');
            $table->index('scheduled_time');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->index('status');
            $table->index('published_at');
            $table->index('is_media_published');
        });

        Schema::table('score_sheets', function (Blueprint $table) {
            $table->index('entry_id');
            $table->index(['program_id', 'is_submitted']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->index('program_id');
            $table->index('stage_id');
            $table->index('status');
            $table->index('start_time');
        });

        Schema::table('judge_assignments', function (Blueprint $table) {
            $table->index('program_id');
        });

        Schema::table('program_entry_participants', function (Blueprint $table) {
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['group_id']);
            $table->dropIndex(['zone_id']);
            $table->dropIndex(['category']);
            $table->dropIndex(['class_level']);
        });

        Schema::table('program_entries', function (Blueprint $table) {
            $table->dropIndex(['group_id']);
            $table->dropIndex(['student_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['program_id', 'status']);
            $table->dropIndex(['group_id', 'status']);
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropIndex(['zone_id']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['stage_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['type']);
            $table->dropIndex(['scheduled_time']);
        });

        Schema::table('results', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['published_at']);
            $table->dropIndex(['is_media_published']);
        });

        Schema::table('score_sheets', function (Blueprint $table) {
            $table->dropIndex(['entry_id']);
            $table->dropIndex(['program_id', 'is_submitted']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex(['program_id']);
            $table->dropIndex(['stage_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['start_time']);
        });

        Schema::table('judge_assignments', function (Blueprint $table) {
            $table->dropIndex(['program_id']);
        });

        Schema::table('program_entry_participants', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
        });
    }
};
