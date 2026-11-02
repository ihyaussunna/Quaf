<?php

namespace App\Console\Commands;

use App\Models\Result;
use App\Services\PointCalculationService;
use Illuminate\Console\Command;

class SyncAllPodiumsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'results:sync-all-podiums {--force : Force coin redistribution even if entries match}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all results with latest judge scores, reverse old coins, and award new coins';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting podium synchronization for all declared results...');

        $results = Result::whereIn('status', PointCalculationService::DECLARED_STATUSES)
            ->with(['program.entries.scores', 'program.entries.scoreSheets', 'program.entries.student', 'program.entries.group'])
            ->get();

        $updatedCount = 0;
        $force = (bool) $this->option('force');

        foreach ($results as $result) {
            if ($result->syncWithLatestJudgeScores($force)) {
                $updatedCount++;
                $this->info("✓ Synced result #{$result->result_number} for '{$result->program?->name}'");
            }
        }

        app(PointCalculationService::class)->recalculateAllPoints();

        $this->info("Successfully synced {$updatedCount} results.");

        return self::SUCCESS;
    }
}
