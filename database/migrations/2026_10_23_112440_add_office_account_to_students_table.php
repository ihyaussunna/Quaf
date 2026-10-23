<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Make group_id nullable on students table so special accounts (like Office) don't require a festival group
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->change();
        });

        // 2. Insert or update the official Office student account
        DB::table('students')->updateOrInsert(
            ['student_id' => 'OFFICE'],
            [
                'name' => 'OFFICE',
                'class_level' => 'OFFICE',
                'group_id' => null,
                'category' => 'Office',
                'gender' => 'Other',
                'qr_token' => Str::random(40),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('students')->where('student_id', 'OFFICE')->delete();
    }
};
