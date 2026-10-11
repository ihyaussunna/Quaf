<?php

use App\Models\Schedule;
use App\Services\ScheduleConflictService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $service = app(ScheduleConflictService::class);
        $conflictsSummary = $service->detectAllScheduleConflicts(null);
        $programConflictCounts = $conflictsSummary['program_conflict_counts'] ?? [];

        Schedule::whereNotNull('conflict_notes')->get()->each(function (Schedule $schedule) use ($programConflictCounts) {
            $conflictCount = $programConflictCounts[$schedule->program_id] ?? 0;
            if ($conflictCount === 0) {
                $schedule->updateQuietly(['conflict_notes' => null]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed
    }
};
