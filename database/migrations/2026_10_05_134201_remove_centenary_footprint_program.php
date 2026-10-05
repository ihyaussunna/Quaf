<?php

use App\Models\GreenRoomCall;
use App\Models\JudgeAssignment;
use App\Models\PointsTransaction;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Schedule;
use App\Models\ScoreSheet;
use App\Models\ScoringCriteria;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $programs = Program::where('code', 'Q9-241')
            ->orWhere('name', 'like', '%Centenary Footprint%')
            ->get();

        foreach ($programs as $program) {
            ProgramEntry::where('program_id', $program->id)->delete();
            Schedule::where('program_id', $program->id)->delete();
            Result::where('program_id', $program->id)->delete();
            ScoreSheet::where('program_id', $program->id)->delete();
            ScoringCriteria::where('program_id', $program->id)->delete();
            GreenRoomCall::where('program_id', $program->id)->delete();
            JudgeAssignment::where('program_id', $program->id)->delete();
            PointsTransaction::where('program_id', $program->id)->delete();
            $program->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Program removal is permanent per request
    }
};
