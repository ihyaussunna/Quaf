<?php

use App\Models\Schedule;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schedule::with('program')
            ->whereNotNull('conflict_notes')
            ->get()
            ->each(function (Schedule $schedule) {
                if (! $schedule->program || empty($schedule->conflict_notes)) {
                    return;
                }

                $lines = explode("\n", $schedule->conflict_notes);
                $progName = $schedule->program->name;

                $cleaned = array_filter($lines, function ($line) use ($progName) {
                    $trimmed = trim($line);
                    if (empty($trimmed)) {
                        return false;
                    }
                    // Remove self-referencing "booked for '[this program]'"
                    if (str_contains($trimmed, "booked for '{$progName}'")) {
                        return false;
                    }

                    return true;
                });

                $newNotes = ! empty($cleaned) ? implode("\n", $cleaned) : null;
                if ($newNotes !== $schedule->conflict_notes) {
                    $schedule->updateQuietly(['conflict_notes' => $newNotes]);
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
