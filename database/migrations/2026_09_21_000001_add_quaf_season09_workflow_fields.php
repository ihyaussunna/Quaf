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
        // 1. Judges: Add access_code for 4-digit PIN login
        Schema::table('judges', function (Blueprint $table) {
            if (! Schema::hasColumn('judges', 'access_code')) {
                $table->string('access_code', 10)->nullable()->unique()->after('user_id');
            }
        });

        // 2. Program Entries: Add code_letter for anonymous evaluation & attendance_status
        Schema::table('program_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('program_entries', 'code_letter')) {
                $table->string('code_letter', 10)->nullable()->after('chest_number');
            }
            if (! Schema::hasColumn('program_entries', 'attendance_status')) {
                $table->string('attendance_status', 20)->default('waiting')->after('code_letter'); // waiting, present, absent
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('judges', function (Blueprint $table) {
            $table->dropColumn(['access_code']);
        });

        Schema::table('program_entries', function (Blueprint $table) {
            $table->dropColumn(['code_letter', 'attendance_status']);
        });
    }
};
