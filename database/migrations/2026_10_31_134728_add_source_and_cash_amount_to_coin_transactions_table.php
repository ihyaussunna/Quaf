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
        Schema::table('coin_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('coin_transactions', 'source')) {
                $table->string('source', 50)->default('competition')->after('transaction_type')->index();
            }
            if (! Schema::hasColumn('coin_transactions', 'cash_amount')) {
                $table->decimal('cash_amount', 10, 2)->default(0.00)->after('coins');
            }
            if (! Schema::hasColumn('coin_transactions', 'payment_mode')) {
                $table->string('payment_mode', 30)->nullable()->default('cash')->after('cash_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coin_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('coin_transactions', 'payment_mode')) {
                $table->dropColumn('payment_mode');
            }
            if (Schema::hasColumn('coin_transactions', 'cash_amount')) {
                $table->dropColumn('cash_amount');
            }
            if (Schema::hasColumn('coin_transactions', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
