<?php

use App\Models\CoinTransaction;
use App\Services\CoinRewardService;
use App\Services\PointCalculationService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updates coin transaction ledger with official individual and group reward charts,
     * and credits all eligible participants for declared results.
     */
    public function up(): void
    {
        CoinTransaction::ensureSchema();

        if (class_exists(CoinRewardService::class)) {
            app(CoinRewardService::class)->distributeMissingCoinsForAllDeclaredResults();
        }

        if (class_exists(PointCalculationService::class)) {
            app(PointCalculationService::class)->recalculateAllPoints();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op
    }
};
