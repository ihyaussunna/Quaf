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
        // 1. Groups (Festival Houses / Teams)
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->string('slug')->unique();
            $table->string('logo_url')->nullable();
            $table->string('color_hex', 10)->default('#d4af37');
            $table->foreignId('leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('points_cache')->default(0);
            $table->unsignedInteger('rank_cache')->default(0);
            $table->timestamps();
        });

        // 2. Program Categories
        Schema::create('program_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        // 3. Stages (Festival Venues)
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->string('location')->nullable();
            $table->unsignedInteger('capacity')->default(100);
            $table->unsignedBigInteger('current_program_id')->nullable();
            $table->unsignedBigInteger('next_program_id')->nullable();
            $table->string('status', 20)->default('active'); // active, break, closed
            $table->timestamps();
        });

        // 4. Programs (Festival Events)
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->foreignId('category_id')->constrained('program_categories')->cascadeOnDelete();
            $table->string('type', 20)->default('individual'); // individual, group
            $table->string('eligibility')->nullable(); // e.g. Sub-Junior, Junior, Senior, General
            $table->text('rules')->nullable();
            $table->unsignedInteger('duration_minutes')->default(15);
            $table->foreignId('stage_id')->nullable()->constrained('stages')->nullOnDelete();
            $table->dateTime('scheduled_time')->nullable();
            $table->decimal('points_weight', 4, 2)->default(1.00)->unsigned();
            $table->string('status', 20)->default('upcoming'); // upcoming, in_progress, completed, cancelled
            $table->timestamps();
        });

        // 5. Students (Participant Registry)
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 30)->unique(); // e.g. QUAF-ST-1001
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('name');
            $table->string('category', 30)->default('General'); // Sub-Junior, Junior, Senior, General
            $table->string('class_level', 50)->nullable();
            $table->string('gender', 10)->default('Male');
            $table->string('contact', 30)->nullable();
            $table->string('photo_url')->nullable();
            $table->string('qr_token', 64)->unique();
            $table->unsignedInteger('points_cache')->default(0);
            $table->timestamps();
        });

        // 6. Program Entries (Registrations)
        Schema::create('program_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('chest_number', 20);
            $table->string('status', 20)->default('pending'); // pending, verified, rejected
            $table->boolean('conflict_flag')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['program_id', 'chest_number']);
        });

        // 7. Schedules
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('stages')->cascadeOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('status', 20)->default('scheduled'); // scheduled, ongoing, completed, delayed
            $table->text('conflict_notes')->nullable();
            $table->timestamps();
        });

        // 8. Judges & Assignments
        Schema::create('judges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->text('bio')->nullable();
            $table->string('specialization')->nullable();
            $table->string('contact')->nullable();
            $table->timestamps();
        });

        Schema::create('judge_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('judge_id')->constrained('judges')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->string('status', 20)->default('assigned'); // assigned, completed
            $table->timestamps();

            $table->unique(['judge_id', 'program_id']);
        });

        // 9. Scoring Criteria & Score Sheets
        Schema::create('scoring_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->string('criterion_name');
            $table->unsignedInteger('max_marks')->default(25);
            $table->timestamps();
        });

        Schema::create('score_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('judge_id')->constrained('judges')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('entry_id')->constrained('program_entries')->cascadeOnDelete();
            $table->json('criteria_scores')->nullable();
            $table->decimal('total_score', 5, 2)->default(0.00);
            $table->text('remarks')->nullable();
            $table->boolean('is_submitted')->default(false);
            $table->dateTime('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['judge_id', 'program_id', 'entry_id']);
        });

        // 10. Results
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->unique()->constrained('programs')->cascadeOnDelete();
            $table->foreignId('first_entry_id')->nullable()->constrained('program_entries')->nullOnDelete();
            $table->foreignId('second_entry_id')->nullable()->constrained('program_entries')->nullOnDelete();
            $table->foreignId('third_entry_id')->nullable()->constrained('program_entries')->nullOnDelete();
            $table->string('status', 20)->default('draft'); // draft, submitted, under_review, verified, published
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('published_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // 11. Point Settings
        Schema::create('point_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('first_place_points')->default(10);
            $table->unsignedInteger('second_place_points')->default(7);
            $table->unsignedInteger('third_place_points')->default(5);
            $table->unsignedInteger('participation_points')->default(1);
            $table->decimal('group_multiplier', 4, 2)->default(2.00);
            $table->timestamps();
        });

        // 12. Green Room Calls
        Schema::create('green_room_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('entry_id')->constrained('program_entries')->cascadeOnDelete();
            $table->unsignedInteger('order_num')->default(1);
            $table->string('status', 20)->default('waiting'); // waiting, checked_in, ready, called, on_stage, completed, absent
            $table->dateTime('called_at')->nullable();
            $table->dateTime('stage_entered_at')->nullable();
            $table->timestamps();
        });

        // 13. News
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('category', 50)->default('General');
            $table->string('cover_image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status', 20)->default('published'); // draft, published, scheduled
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
        });

        // 14. Gallery Items
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image_path');
            $table->string('category', 50)->default('Festival');
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('stages')->nullOnDelete();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        // 15. Video Items
        Schema::create('video_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('youtube_id');
            $table->string('thumbnail_path')->nullable();
            $table->string('category', 50)->default('Highlights');
            $table->boolean('is_live')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        // 16. Announcements
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('priority', 20)->default('normal'); // normal, important, urgent
            $table->string('target_role', 50)->nullable(); // e.g. student, leader, judge, green_room, all
            $table->foreignId('target_group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->foreignId('target_stage_id')->nullable()->constrained('stages')->nullOnDelete();
            $table->foreignId('target_program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 17. Certificates
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number', 50)->unique();
            $table->foreignId('entry_id')->constrained('program_entries')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->string('position', 30); // 1st Place, 2nd Place, 3rd Place, Participation
            $table->dateTime('issued_at');
            $table->string('qr_verification_url')->nullable();
            $table->timestamps();
        });

        // 18. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // e.g. create_result, publish_result, update_score, register_entry
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // 19. Festival Settings
        Schema::create('festival_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('festival_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('video_items');
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('news');
        Schema::dropIfExists('green_room_calls');
        Schema::dropIfExists('point_settings');
        Schema::dropIfExists('results');
        Schema::dropIfExists('score_sheets');
        Schema::dropIfExists('scoring_criteria');
        Schema::dropIfExists('judge_assignments');
        Schema::dropIfExists('judges');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('program_entries');
        Schema::dropIfExists('students');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('stages');
        Schema::dropIfExists('program_categories');
        Schema::dropIfExists('groups');
    }
};
