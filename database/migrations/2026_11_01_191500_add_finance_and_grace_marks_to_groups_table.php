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
        Schema::table('groups', function (Blueprint $table) {
            if (! Schema::hasColumn('groups', 'finance_points')) {
                $table->integer('finance_points')->default(0)->after('points_cache');
            }
            if (! Schema::hasColumn('groups', 'finance_remarks')) {
                $table->string('finance_remarks')->nullable()->after('finance_points');
            }
            if (! Schema::hasColumn('groups', 'grace_points')) {
                $table->integer('grace_points')->default(0)->after('finance_remarks');
            }
            if (! Schema::hasColumn('groups', 'grace_remarks')) {
                $table->string('grace_remarks')->nullable()->after('grace_points');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $dropCols = [];
            foreach (['finance_points', 'finance_remarks', 'grace_points', 'grace_remarks'] as $col) {
                if (Schema::hasColumn('groups', $col)) {
                    $dropCols[] = $col;
                }
            }
            if (! empty($dropCols)) {
                $table->dropColumn($dropCols);
            }
        });
    }
};
