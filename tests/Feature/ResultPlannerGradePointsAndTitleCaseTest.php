<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\ScoreSheet;
use App\Models\Student;
use App\Models\User;
use App\Services\PointCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResultPlannerGradePointsAndTitleCaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Judge $judge;

    protected Group $groupA;

    protected Group $groupB;

    protected ProgramCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@quaf.test',
        ]);

        $judgeUser = User::factory()->create([
            'role' => 'judge',
            'name' => 'Judge User',
            'email' => 'judge@quaf.test',
        ]);

        $this->judge = Judge::create([
            'user_id' => $judgeUser->id,
            'name' => 'Judge Master',
            'access_code' => 'JDG-001',
        ]);

        $this->groupA = Group::create([
            'name' => 'PACTO HIKMIC',
            'code' => 'PACTO',
            'slug' => 'pacto-hikmic',
            'color_hex' => '#2E3192',
            'points_cache' => 0,
            'rank_cache' => 1,
            'public_points_cache' => 0,
            'public_rank_cache' => 1,
        ]);

        $this->groupB = Group::create([
            'name' => 'YUGO RUSHDIC',
            'code' => 'YUGO',
            'slug' => 'yugo-rushdic',
            'color_hex' => '#AD1E56',
            'points_cache' => 0,
            'rank_cache' => 2,
            'public_points_cache' => 0,
            'public_rank_cache' => 2,
        ]);

        $this->category = ProgramCategory::create([
            'name' => 'SENIOR ARTS',
            'slug' => 'senior-arts',
        ]);
    }

    public function test_to_title_case_converts_strings_properly(): void
    {
        $this->assertEquals('Muhammed Suhail P.', PointCalculationService::toTitleCase('MUHAMMED SUHAIL P.'));
        $this->assertEquals('Malayalam Elocution', PointCalculationService::toTitleCase('MALAYALAM ELOCUTION'));
        $this->assertEquals('Mix Zone', PointCalculationService::toTitleCase('MIX ZONE'));
        $this->assertEquals('Pacto Hikmic', PointCalculationService::toTitleCase('PACTO HIKMIC'));
        $this->assertEquals('Al-Hassan', PointCalculationService::toTitleCase('AL-HASSAN'));
        $this->assertEquals('Speech & Recitation', PointCalculationService::toTitleCase('SPEECH AND RECITATION'));
    }

    public function test_planner_points_breakdown_includes_both_position_and_grade_points(): void
    {
        $program = Program::create([
            'name' => 'MALAYALAM ELOCUTION',
            'code' => 'Q9-101',
            'type' => 'individual',
            'category_id' => $this->category->id,
            'eligibility' => 'MIX ZONE',
            'status' => 'completed',
        ]);

        $studentA = Student::create([
            'name' => 'MUHAMMED SUHAIL P.',
            'group_id' => $this->groupA->id,
            'student_id' => 'STU-'.Str::random(6),
            'qr_token' => Str::random(16),
        ]);

        $entryA = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $studentA->id,
            'group_id' => $this->groupA->id,
            'chest_number' => 101,
        ]);

        ScoreSheet::create([
            'program_id' => $program->id,
            'entry_id' => $entryA->id,
            'total_score' => 95.00,
            'judge_id' => $this->judge->id,
            'is_submitted' => true,
        ]);

        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entryA->id,
            'first_points' => 5,
            'status' => 'draft',
        ]);

        $breakdown = PointCalculationService::calculatePointsBreakdownForResult($result);

        $this->assertArrayHasKey($this->groupA->id, $breakdown['points_by_group']);
        // 1st place (5 pts) + A+ Grade (6 pts for >= 90) = 11 pts
        $this->assertEquals(11, $breakdown['points_by_group'][$this->groupA->id]);
        $this->assertEquals(5, $breakdown['position_points_by_group'][$this->groupA->id]);
        $this->assertEquals(6, $breakdown['grade_points_by_group'][$this->groupA->id]);

        $firstWinner = $breakdown['winner_details'][1][0];
        $this->assertEquals('Muhammed Suhail P.', $firstWinner['name']);
        $this->assertEquals('Pacto Hikmic', $firstWinner['team']);
        $this->assertEquals('A+', $firstWinner['grade']);
        $this->assertEquals(6, $firstWinner['grade_points']);
        $this->assertEquals(5, $firstWinner['position_points']);
        $this->assertEquals(11, $firstWinner['total_points']);
    }

    public function test_planner_view_renders_successfully_with_grade_points(): void
    {
        $program = Program::create([
            'name' => 'ENGLISH ESSAY',
            'code' => 'Q9-102',
            'type' => 'individual',
            'category_id' => $this->category->id,
            'eligibility' => 'MIX ZONE',
            'status' => 'completed',
        ]);

        $studentB = Student::create([
            'name' => 'AHMED RAYYAN',
            'group_id' => $this->groupB->id,
            'student_id' => 'STU-'.Str::random(6),
            'qr_token' => Str::random(16),
        ]);

        $entryB = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $studentB->id,
            'group_id' => $this->groupB->id,
            'chest_number' => 102,
        ]);

        ScoreSheet::create([
            'program_id' => $program->id,
            'entry_id' => $entryB->id,
            'total_score' => 85.00,
            'judge_id' => $this->judge->id,
            'is_submitted' => true,
        ]);

        Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entryB->id,
            'first_points' => 5,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.results.planner'));

        $response->assertOk();
        $response->assertSee('Live Standings Simulator');
        $response->assertSee('Points split (incl. Grades):');
        $response->assertSee('Pacto Hikmic');
        $response->assertSee('Yugo Rushdic');
    }
}
