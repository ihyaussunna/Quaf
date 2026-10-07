<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Judge;
use App\Models\PointsTransaction;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\ScoreSheet;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResetEvaluationsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_evaluations_clears_marks_and_points_while_preserving_students_and_entries(): void
    {
        $zone = Zone::firstOrCreate(['slug' => 'a-zone'], ['name' => 'A Zone', 'code' => 'A_ZONE']);
        $category = ProgramCategory::firstOrCreate(['slug' => 'gen'], ['name' => 'General', 'code' => 'GEN', 'display_order' => 1]);
        $group = Group::firstOrCreate(['slug' => 'safva'], ['name' => 'SAFVA', 'code' => 'SAF', 'points_cache' => 50, 'rank_cache' => 1]);
        $group->update(['points_cache' => 50]);

        $student = Student::create([
            'student_id' => 'STU-001',
            'name' => 'Zayd',
            'chest_number' => '101',
            'group_id' => $group->id,
            'category_id' => $category->id,
            'qr_token' => Str::random(32),
            'points_cache' => 15,
        ]);

        $program = Program::create([
            'name' => 'Elocution',
            'code' => 'ELO01',
            'category_id' => $category->id,
            'zone_id' => $zone->id,
            'type' => 'single',
            'status' => 'completed',
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'chest_number' => '101',
            'code_letter' => 'A',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        $judgeUser = User::factory()->create(['role' => 'judge']);
        $judge = Judge::create([
            'name' => 'Judge 1',
            'user_id' => $judgeUser->id,
            'access_code' => '1122',
        ]);

        ScoreSheet::create([
            'judge_id' => $judge->id,
            'program_id' => $program->id,
            'entry_id' => $entry->id,
            'total_score' => 95.0,
            'is_submitted' => true,
        ]);

        Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entry->id,
            'status' => 'published',
        ]);

        PointsTransaction::create([
            'group_id' => $group->id,
            'program_id' => $program->id,
            'entry_id' => $entry->id,
            'points' => 5,
            'type' => 'position',
            'description' => '1st place',
        ]);

        $this->assertEquals(1, ScoreSheet::count());
        $this->assertEquals(1, Result::count());
        $this->assertEquals(1, PointsTransaction::count());

        // Execute reset-evaluations endpoint
        $response = $this->get('/reset-evaluations?token=quaf2026setup');
        $response->assertStatus(200);
        $response->assertSee('All Evaluation Marks Reset to Zero Successfully');

        // Score sheets and results cleared
        $this->assertEquals(0, ScoreSheet::count());
        $this->assertEquals(0, Result::count());
        $this->assertEquals(0, PointsTransaction::count());

        // Points reset to zero
        $this->assertEquals(0, $group->fresh()->points_cache);
        $this->assertEquals(0, $student->fresh()->points_cache);

        // Students and program entries remain 100% intact
        $this->assertEquals(1, Student::count());
        $this->assertEquals(1, ProgramEntry::count());
        $this->assertEquals('Zayd', Student::first()->name);
        $this->assertEquals('101', ProgramEntry::first()->chest_number);
    }
}
