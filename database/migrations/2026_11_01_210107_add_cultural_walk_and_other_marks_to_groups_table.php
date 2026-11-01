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
            if (! Schema::hasColumn('groups', 'cultural_walk_points')) {
                $table->integer('cultural_walk_points')->default(0)->after('grace_remarks');
            }
            if (! Schema::hasColumn('groups', 'cultural_walk_remarks')) {
                $table->string('cultural_walk_remarks')->nullable()->after('cultural_walk_points');
            }
            if (! Schema::hasColumn('groups', 'other_points')) {
                $table->integer('other_points')->default(0)->after('cultural_walk_remarks');
            }
            if (! Schema::hasColumn('groups', 'other_remarks')) {
                $table->string('other_remarks')->nullable()->after('other_points');
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
            foreach (['cultural_walk_points', 'cultural_walk_remarks', 'other_points', 'other_remarks'] as $col) {
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
