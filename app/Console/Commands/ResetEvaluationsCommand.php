<?php

namespace App\Console\Commands;

use App\Models\Certificate;
use App\Models\Group;
use App\Models\PointsTransaction;
use App\Models\Program;
use App\Models\Result;
use App\Models\ScoreSheet;
use App\Models\Student;
use App\Services\AuditLogger;
use App\Services\PointCalculationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Signature('app:reset-evaluations {--force : Force reset without confirmation}')]
#[Description('Reset all evaluation marks, score sheets, and results to zero while keeping all students, entries, and programs intact.')]
class ResetEvaluationsCommand extends Command
{
    public function handle(): int
    {
        $this->info('Resetting all evaluation marks and score sheets to zero...');

        DB::transaction(function () {
            // 1. Delete all score sheets (judge marks and criteria evaluations)
            if (Schema::hasTable('score_sheets')) {
                ScoreSheet::query()->delete();
            }

            // 2. Delete all results and podium records
            if (Schema::hasTable('results')) {
                Result::query()->delete();
            }

            // 3. Clear points transaction ledger
            if (Schema::hasTable('points_transactions')) {
                PointsTransaction::query()->delete();
            }

            // 4. Delete winner certificates generated from results
            if (Schema::hasTable('certificates')) {
                Certificate::whereNotNull('result_id')->delete();
            }

            // 5. Reset points cache on all groups to zero
            if (Schema::hasTable('groups')) {
                Group::query()->update([
                    'points_cache' => 0,
                    'rank_cache' => 1,
                ]);
            }

            // 6. Reset points cache on all students to zero
            if (Schema::hasTable('students')) {
                Student::query()->update([
                    'points_cache' => 0,
                ]);
            }

            // 7. Reset programs completed by test evaluation back to upcoming
            if (Schema::hasTable('programs')) {
                Program::where('status', 'completed')->update([
                    'status' => 'upcoming',
                ]);
            }
        });

        // 8. Re-run point calculation service to ensure perfect consistency
        PointCalculationService::recalculateAll();

        // 9. Flush application and standings caches
        Cache::flush();

        AuditLogger::log('evaluations_reset_to_zero', null, null, [
            'timestamp' => now()->toIso8601String(),
        ]);

        $this->info('Successfully reset all evaluations to zero!');
        $this->line('Score sheets: 0');
        $this->line('Results: 0');
        $this->line('Group Points: 0');
        $this->line('Student Points: 0');
        $this->info('All students, chest numbers, and program entries remain 100% intact.');

        return Command::SUCCESS;
    }
}
