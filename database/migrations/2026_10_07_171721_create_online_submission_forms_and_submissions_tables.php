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
        if (! Schema::hasTable('online_submission_forms')) {
            Schema::create('online_submission_forms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
                $table->string('slug', 100)->unique();
                $table->string('title', 255);
                $table->text('instructions')->nullable();
                $table->boolean('allow_text')->default(true);
                $table->string('text_label', 255)->default('Content / Text Submission');
                $table->text('text_placeholder')->nullable();
                $table->boolean('is_text_required')->default(false);
                $table->boolean('allow_image')->default(false);
                $table->string('image_label', 255)->default('Upload Photo / Document');
                $table->boolean('is_image_required')->default(false);
                $table->boolean('allow_video')->default(false);
                $table->string('video_label', 255)->default('Video File or Link');
                $table->boolean('is_video_required')->default(false);
                $table->boolean('is_open')->default(true);
                $table->timestamp('closes_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('online_submissions')) {
            Schema::create('online_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('online_submission_form_id')->constrained('online_submission_forms')->cascadeOnDelete();
                $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
                $table->foreignId('program_entry_id')->nullable()->constrained('program_entries')->nullOnDelete();
                $table->string('code_letter', 20);
                $table->string('chest_number', 50)->nullable();
                $table->string('student_name', 255)->nullable();
                $table->string('student_id', 50)->nullable();
                $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
                $table->longText('text_content')->nullable();
                $table->string('file_path', 500)->nullable();
                $table->string('file_name', 255)->nullable();
                $table->string('file_type', 50)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('video_url', 1000)->nullable();
                $table->string('ip_address', 50)->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->text('notes')->nullable();
                $table->string('status', 30)->default('submitted');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_submissions');
        Schema::dropIfExists('online_submission_forms');
    }
};
