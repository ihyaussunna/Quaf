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
        $students = DB::table('students')
            ->where('name', 'like', '?%')
            ->get(['id', 'name']);

        foreach ($students as $student) {
            $cleaned = ltrim((string) $student->name, "? \t\n\r\0\x0B");
            DB::table('students')
                ->where('id', $student->id)
                ->update(['name' => $cleaned]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible data cleanup
    }
};
