<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\Certificate;
use App\Models\GalleryItem;
use App\Models\GreenRoomCall;
use App\Models\Group;
use App\Models\News;
use App\Models\PointsTransaction;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Schedule;
use App\Models\ScoreSheet;
use App\Models\Stage;
use App\Models\Student;
use App\Models\VideoItem;
use App\Services\PointCalculationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:purge-mock-data')]
#[Description('Purge all mock and demo data while preserving authentic festival structure, groups, zones, and official students.')]
class PurgeMockDataCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Purging all demo and mockup data from database...');

        DB::transaction(function () {
            // 1. Delete all mock activity and media data
            News::query()->delete();
            GalleryItem::query()->delete();
            VideoItem::query()->delete();
            Announcement::query()->delete();
            Certificate::query()->delete();
            Result::query()->delete();
            ScoreSheet::query()->delete();
            GreenRoomCall::query()->delete();
            PointsTransaction::query()->delete();
            Schedule::query()->delete();

            // Program entries and participants
            DB::table('program_entry_participants')->delete();
            ProgramEntry::query()->delete();

            // 2. Remove mock students (any student not part of official Pacto QF1001-QF1030)
            $deletedStudents = Student::where('student_id', 'not like', 'QF10%')->delete();
            $this->info("Removed {$deletedStudents} mock students.");

            // 3. Clear mock photo URLs and reset points for official students
            Student::query()->update([
                'photo_url' => null,
                'points_cache' => 0,
            ]);
            $this->info('Reset photo URLs and points for official students.');

            // 4. Clear mock logo URLs and reset points/ranks for official groups
            Group::query()->update([
                'logo_url' => null,
                'points_cache' => 0,
                'rank_cache' => 0,
            ]);
            $this->info('Reset logo URLs and points for official groups.');

            // 5. Reset all programs status to upcoming
            Program::query()->update([
                'status' => 'upcoming',
            ]);
            $this->info('Reset all programs status to upcoming.');

            // 6. Reset stage active program pointers
            Stage::query()->update([
                'current_program_id' => null,
                'next_program_id' => null,
            ]);
            $this->info('Cleared current and next program pointers from stages.');

            // 7. Recalculate points engine
            app(PointCalculationService::class)->recalculateAllPoints();
        });

        $this->info('All demo and mockup data successfully purged!');

        return Command::SUCCESS;
    }
}
