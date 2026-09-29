<?php

namespace App\Console\Commands;

use App\Models\GreenRoomCall;
use App\Models\Group;
use App\Models\PointsTransaction;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\ScoreSheet;
use App\Models\Student;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Signature('app:reset-festival-state {--keep-entries : Keep program entries and only reset locks and statuses}')]
#[Description('Reset all completed programs, unlock locked call lists, and clear evaluation results back to starting stage.')]
class ResetFestivalStateCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Resetting festival competition state to starting stage...');

        DB::transaction(function () {
            // 1. Reset all programs to upcoming and unlocked
            if (Schema::hasTable('programs')) {
                $updates = ['status' => 'upcoming'];
                if (Schema::hasColumn('programs', 'is_call_list_locked')) {
                    $updates['is_call_list_locked'] = false;
                }
                Program::query()->update($updates);
            }

            // 2. Clear results
            Result::query()->delete();

            // 3. Clear score sheets
            ScoreSheet::query()->delete();

            // 4. Clear green room calls
            GreenRoomCall::query()->delete();

            // 5. Clear entries unless specified
            if (! $this->option('keep-entries')) {
                if (Schema::hasTable('program_entry_participants')) {
                    DB::table('program_entry_participants')->delete();
                }
                ProgramEntry::query()->delete();
            }

            // 6. Reset points
            PointsTransaction::query()->delete();
            Group::query()->update(['points_cache' => 0, 'rank_cache' => 1]);
            Student::query()->update(['points_cache' => 0]);
        });

        $this->info('Successfully reset all completed programs and unlocked all call lists.');

        return Command::SUCCESS;
    }
}
