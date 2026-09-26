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
        Schema::table('programs', function (Blueprint $table) {
            if (! Schema::hasColumn('programs', 'malayalam_name')) {
                $table->string('malayalam_name')->nullable()->after('name');
            }
        });

        Schema::table('groups', function (Blueprint $table) {
            if (! Schema::hasColumn('groups', 'name_in_results')) {
                $table->string('name_in_results')->nullable()->after('manager_contact');
            }
            if (! Schema::hasColumn('groups', 'name_in_certificates')) {
                $table->string('name_in_certificates')->nullable()->after('name_in_results');
            }
            if (! Schema::hasColumn('groups', 'admin_username')) {
                $table->string('admin_username')->nullable()->after('name_in_certificates');
            }
            if (! Schema::hasColumn('groups', 'admin_password')) {
                $table->string('admin_password')->nullable()->after('admin_username');
            }
            if (! Schema::hasColumn('groups', 'assistant_managers')) {
                $table->json('assistant_managers')->nullable()->after('admin_password');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'dob')) {
                $table->date('dob')->nullable()->after('gender');
            }
        });

        Schema::table('judges', function (Blueprint $table) {
            if (! Schema::hasColumn('judges', 'notes')) {
                $table->text('notes')->nullable()->after('specialization');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['malayalam_name']);
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['name_in_results', 'name_in_certificates', 'admin_username', 'admin_password', 'assistant_managers']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['dob']);
        });

        Schema::table('judges', function (Blueprint $table) {
            $table->dropColumn(['notes']);
        });
    }
};
