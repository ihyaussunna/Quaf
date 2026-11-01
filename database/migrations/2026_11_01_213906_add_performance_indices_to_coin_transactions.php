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
        if (Schema::hasTable('coin_transactions')) {
            try {
                Schema::table('coin_transactions', function (Blueprint $table) {
                    $table->index(['result_id', 'student_id', 'transaction_type'], 'idx_coin_tx_res_stu_type');
                });
            } catch (Throwable) {
                // Index already exists
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('coin_transactions')) {
            Schema::table('coin_transactions', function (Blueprint $table) {
                $table->dropIndex('idx_coin_tx_res_stu_type');
            });
        }
    }
};
