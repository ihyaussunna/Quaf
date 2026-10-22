<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('students')->where('student_id', 'QF5195')->update([
            'name' => 'MO DANISH',
            'class_level' => 'L3',
            'category' => 'B Zone',
        ]);

        DB::table('students')->where('student_id', 'QF2106')->update([
            'name' => 'MU THASIM BILLA',
            'class_level' => 'S3',
            'category' => 'B Zone',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('students')->where('student_id', 'QF5195')->update([
            'name' => 'MO',
            'class_level' => 'DANISH',
        ]);

        DB::table('students')->where('student_id', 'QF2106')->update([
            'name' => 'MU',
            'class_level' => 'THASIM BILLA',
        ]);
    }
};
