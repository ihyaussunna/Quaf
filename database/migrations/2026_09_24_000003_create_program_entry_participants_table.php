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
        Schema::create('program_entry_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->constrained('program_entries')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('role', 30)->default('participant'); // participant, captain, lead
            $table->timestamps();

            $table->unique(['entry_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_entry_participants');
    }
};
