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
        Schema::create('points_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->foreignId('result_id')->nullable()->constrained('results')->nullOnDelete();
            $table->foreignId('entry_id')->nullable()->constrained('program_entries')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('source_type', 30)->default('position'); // position, grade, participation, penalty, manual
            $table->integer('points'); // signed integer to allow penalties/corrections
            $table->string('description');
            $table->timestamps();

            $table->index(['group_id', 'created_at']);
            $table->index(['program_id']);
            $table->index(['result_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_transactions');
    }
};
