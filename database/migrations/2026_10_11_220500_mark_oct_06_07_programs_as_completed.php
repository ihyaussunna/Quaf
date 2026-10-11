<?php

use App\Models\Program;
use App\Models\Schedule;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $completedProgramCodes = [
            // October 06
            'Q9-117', 'Q9-144', 'Q9-165', 'Q9-116', 'Q9-143', 'Q9-166', 'Q9-229',
            'Q9-207', 'Q9-119', 'Q9-146', 'Q9-168', 'Q9-211', 'Q9-220', 'Q9-136',
            'Q9-142', 'Q9-164', 'Q9-115',
            // October 07
            'Q9-113', 'Q9-148', 'Q9-163', 'Q9-205', 'Q9-125', 'Q9-151', 'Q9-173',
            'Q9-206', 'Q9-114', 'Q9-140', 'Q9-162', 'Q9-209', 'Q9-112', 'Q9-139',
            'Q9-161', 'Q9-212', 'Q9-141', 'Q9-170', 'Q9-208',
        ];

        $programs = Program::whereIn('code', $completedProgramCodes)->get();

        foreach ($programs as $prog) {
            $prog->updateQuietly(['status' => 'completed']);
            Schedule::where('program_id', $prog->id)->update(['status' => 'completed']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $completedProgramCodes = [
            'Q9-117', 'Q9-144', 'Q9-165', 'Q9-116', 'Q9-143', 'Q9-166', 'Q9-229',
            'Q9-207', 'Q9-119', 'Q9-146', 'Q9-168', 'Q9-211', 'Q9-220', 'Q9-136',
            'Q9-142', 'Q9-164', 'Q9-115',
            'Q9-113', 'Q9-148', 'Q9-163', 'Q9-205', 'Q9-125', 'Q9-151', 'Q9-173',
            'Q9-206', 'Q9-114', 'Q9-140', 'Q9-162', 'Q9-209', 'Q9-112', 'Q9-139',
            'Q9-161', 'Q9-212', 'Q9-141', 'Q9-170', 'Q9-208',
        ];

        Program::whereIn('code', $completedProgramCodes)->update(['status' => 'upcoming']);
        $progIds = Program::whereIn('code', $completedProgramCodes)->pluck('id');
        Schedule::whereIn('program_id', $progIds)->update(['status' => 'scheduled']);
    }
};
