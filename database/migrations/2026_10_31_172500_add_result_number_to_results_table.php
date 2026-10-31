<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('results')) {
            Schema::table('results', function (Blueprint $table) {
                if (! Schema::hasColumn('results', 'result_number')) {
                    $table->unsignedInteger('result_number')->nullable()->after('program_id')->index();
                }
            });

            // Backfill existing declared / transferred / published results sequentially
            $existing = DB::table('results')
                ->whereIn('status', ['send', 'delivered', 'announced', 'published'])
                ->orderBy('published_at', 'asc')
                ->orderBy('id', 'asc')
                ->get(['id', 'result_number']);

            $counter = 1;
            foreach ($existing as $row) {
                if (! $row->result_number) {
                    DB::table('results')->where('id', $row->id)->update([
                        'result_number' => $counter,
                    ]);
                }
                $counter++;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('results')) {
            Schema::table('results', function (Blueprint $table) {
                if (Schema::hasColumn('results', 'result_number')) {
                    $table->dropColumn('result_number');
                }
            });
        }
    }
};
