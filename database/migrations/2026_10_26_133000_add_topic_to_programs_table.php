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
        if (! Schema::hasColumn('programs', 'topic')) {
            Schema::table('programs', function (Blueprint $table) {
                $table->text('topic')->nullable()->default(null)->after('rules');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('programs', 'topic')) {
            Schema::table('programs', function (Blueprint $table) {
                $table->dropColumn('topic');
            });
        }
    }
};
