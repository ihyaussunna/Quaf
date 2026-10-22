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
        if (! Schema::hasColumn('programs', 'submission_type')) {
            Schema::table('programs', function (Blueprint $table) {
                $table->string('submission_type', 20)->nullable()->default(null)->after('is_stage');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('programs', 'submission_type')) {
            Schema::table('programs', function (Blueprint $table) {
                $table->dropColumn('submission_type');
            });
        }
    }
};
