<?php

namespace App\Console\Commands;

use App\Models\CoinTransaction;
use App\Models\Result;
use App\Models\Student;
use App\Services\CoinRewardService;
use Illuminate\Console\Command;

class SyncCoinRewardsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quaf:sync-coins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit and distribute missing coins for all declared festival competition results and synchronize student wallets.';

    /**
     * Execute the console command.
     */
    public function handle(CoinRewardService $coinService): int
    {
        $this->info('Starting QUAF Season 09 Coin Synchronization & Audit...');

        $declaredCount = Result::whereIn('status', CoinRewardService::DECLARED_STATUSES)->count();
        $this->line("Found {$declaredCount} declared results to inspect.");

        $creditedCount = $coinService->distributeMissingCoinsForAllDeclaredResults();

        $studentsWithCoins = Student::where('coins_cache', '>', 0)->count();
        $totalCoinsInSystem = (int) CoinTransaction::where('transaction_type', 'credit')->sum('coins');

        $this->table(
            ['Metric', 'Value'],
            [
                ['Declared Results Processed', $declaredCount],
                ['New / Updated Coin Credits', $creditedCount],
                ['Students with Active Coin Balance', $studentsWithCoins],
                ['Total Competition Coins Distributed', number_format($totalCoinsInSystem)],
            ]
        );

        $this->info('Coin synchronization and student wallet integrity check completed successfully.');

        return self::SUCCESS;
    }
}
