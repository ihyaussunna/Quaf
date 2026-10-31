<?php

namespace Tests\Feature;

use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\ScoreSheet;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomJudgePanelTest extends TestCase
{
    use RefreshDatabase;

    protected Group $group;

    protected Program $programA;

    protected Program $programB;

    protected ProgramEntry $entry1;

    protected ProgramEntry $entry2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->group = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#be1e2d',
            'points_cache' => 0,
            'rank_cache' => 1,
            'public_points_cache' => 0,
            'public_rank_cache' => 1,
        ]);

        $this->programA = Program::create([
            'name' => 'Elocution Malayalam',
            'code' => 'EM01',
            'type' => 'individual',
            'status' => 'in_progress',
        ]);

        $this->programB = Program::create([
            'name' => 'Mapilappattu',
            'code' => 'MP02',
            'type' => 'individual',
            'status' => 'upcoming',
        ]);

        $student1 = Student::create([
            'student_id' => 'STU-'.Str::upper(Str::random(6)),
            'group_id' => $this->group->id,
            'name' => 'Ahmad Shaheed',
            'chest_number' => '101',
            'qr_token' => Str::random(32),
        ]);

        $student2 = Student::create([
            'student_id' => 'STU-'.Str::upper(Str::random(6)),
            'group_id' => $this->group->id,
            'name' => 'Bilal Hassan',
            'chest_number' => '102',
            'qr_token' => Str::random(32),
        ]);

        $this->entry1 = ProgramEntry::create([
            'program_id' => $this->programA->id,
            'student_id' => $student1->id,
            'group_id' => $this->group->id,
            'chest_number' => '101',
            'code_letter' => 'A',
            'status' => 'verified',
            'attendance_status' => 'waiting',
        ]);

        $this->entry2 = ProgramEntry::create([
            'program_id' => $this->programA->id,
            'student_id' => $student2->id,
            'group_id' => $this->group->id,
            'chest_number' => '102',
            'code_letter' => 'B',
            'status' => 'verified',
            'attendance_status' => 'waiting',
        ]);

        FestivalSetting::set('custom_judge_pin', '9999');
    }

    public function test_unauthenticated_user_visiting_custom_judge_is_redirected_to_login(): void
    {
        $response = $this->get(route('custom-judge.index'));
        $response->assertRedirect(route('custom-judge.login'));
    }

    public function test_invalid_4_digit_pin_shows_error(): void
    {
        $response = $this->post(route('custom-judge.authenticate'), [
            'pin' => '0000',
        ]);

        $response->assertSessionHasErrors('pin');
        $this->assertFalse(session('custom_judge_authenticated', false));
    }

    public function test_valid_4_digit_pin_9999_authenticates_and_opens_panel(): void
    {
        $response = $this->post(route('custom-judge.authenticate'), [
            'pin' => '9999',
        ]);

        $response->assertRedirect(route('custom-judge.index'));
        $this->assertTrue(session('custom_judge_authenticated'));

        // Visit panel
        $panelResponse = $this->get(route('custom-judge.index'));
        $panelResponse->assertOk();
        $panelResponse->assertSee('Custom Judge Panel');
        $panelResponse->assertSee('Judge 1');
        $panelResponse->assertSee('Judge 2');
        $panelResponse->assertSee('Judge 3');
        $panelResponse->assertSee('Elocution Malayalam');
    }

    public function test_regular_judge_login_accepts_9999_and_redirects_to_custom_judge(): void
    {
        $response = $this->post(route('judge.login.submit'), [
            'pin' => '9999',
        ]);

        $response->assertRedirect(route('custom-judge.index'));
        $this->assertTrue(session('custom_judge_authenticated'));
    }

    public function test_switching_program_and_judge_number_updates_view(): void
    {
        $this->withSession(['custom_judge_authenticated' => true]);

        // Select Program B and Judge 2
        $response = $this->get(route('custom-judge.index', [
            'program_id' => $this->programB->id,
            'judge_num' => 2,
        ]));

        $response->assertOk();
        $response->assertSee('Mapilappattu');
        $response->assertSee('Scoring as: Judge 2');
    }

    public function test_three_judges_can_evaluate_same_entry_without_overwriting_and_averages_are_computed(): void
    {
        $this->withSession(['custom_judge_authenticated' => true]);

        // 1. Judge 1 enters 80.00 marks for Entry 1
        $res1 = $this->postJson(route('custom-judge.save'), [
            'program_id' => $this->programA->id,
            'entry_id' => $this->entry1->id,
            'judge_num' => 1,
            'total_score' => 80.0,
            'grade' => 'A',
            'remarks' => 'Good performance by J1',
        ]);

        $res1->assertOk();
        $res1->assertJsonPath('success', true);
        $res1->assertJsonPath('judge_num', 1);
        $this->assertEquals(80.0, (float) $res1->json('score'));
        $this->assertEquals(80.0, (float) $res1->json('average_score'));

        // Attendance auto-updates to present
        $this->entry1->refresh();
        $this->assertEquals('present', $this->entry1->attendance_status);

        // 2. Judge 2 enters 90.00 marks for the same Entry 1
        $res2 = $this->postJson(route('custom-judge.save'), [
            'program_id' => $this->programA->id,
            'entry_id' => $this->entry1->id,
            'judge_num' => 2,
            'total_score' => 90.0,
            'grade' => 'A+',
            'remarks' => 'Splendid delivery by J2',
        ]);

        $res2->assertOk();
        $res2->assertJsonPath('success', true);
        $res2->assertJsonPath('judge_num', 2);
        $this->assertEquals(90.0, (float) $res2->json('score'));
        // Average of J1 (80) and J2 (90) = 85.0
        $this->assertEquals(85.0, (float) $res2->json('average_score'));

        // 3. Judge 3 enters 85.00 marks for the same Entry 1
        $res3 = $this->postJson(route('custom-judge.save'), [
            'program_id' => $this->programA->id,
            'entry_id' => $this->entry1->id,
            'judge_num' => 3,
            'total_score' => 85.0,
            'grade' => 'A',
            'remarks' => 'Balanced score by J3',
        ]);

        $res3->assertOk();
        $res3->assertJsonPath('success', true);
        $res3->assertJsonPath('judge_num', 3);
        $this->assertEquals(85.0, (float) $res3->json('score'));
        // Average of (80 + 90 + 85) / 3 = 85.0
        $this->assertEquals(85.0, (float) $res3->json('average_score'));
        $res3->assertJsonPath('combined_grade', 'A');

        // Verify that 3 distinct ScoreSheet rows exist for Entry 1
        $sheets = ScoreSheet::where('program_id', $this->programA->id)
            ->where('entry_id', $this->entry1->id)
            ->get();

        $this->assertCount(3, $sheets);
        $this->assertEquals(80.0, $sheets->firstWhere('criteria_scores.direct_score', 80.0)->total_score);
        $this->assertEquals(90.0, $sheets->firstWhere('criteria_scores.direct_score', 90.0)->total_score);
        $this->assertEquals(85.0, $sheets->firstWhere('criteria_scores.direct_score', 85.0)->total_score);

        // 4. Judge 1 scores Entry 2 with 70.00
        $this->postJson(route('custom-judge.save'), [
            'program_id' => $this->programA->id,
            'entry_id' => $this->entry2->id,
            'judge_num' => 1,
            'total_score' => 70.0,
            'grade' => 'B',
        ]);

        // Verify podium: Entry 1 (Avg 85.00) is 1st place!
        $this->programA->refresh();
        $this->assertNotNull($this->programA->result);
        $this->assertEquals($this->entry1->id, $this->programA->result->first_entry_id);
    }

    public function test_custom_judge_logout_clears_session(): void
    {
        $this->withSession(['custom_judge_authenticated' => true]);

        $response = $this->get(route('custom-judge.logout'));
        $response->assertRedirect(route('custom-judge.login'));
        $this->assertFalse(session('custom_judge_authenticated', false));
    }
}
