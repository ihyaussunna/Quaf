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
        Schema::table('program_entries', function (Blueprint $table) {
            $table->index('chest_number');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_entries', function (Blueprint $table) {
            $table->dropIndex(['chest_number']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};
