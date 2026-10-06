<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Schedule;
use App\Models\ScoringCriteria;
use App\Models\Stage;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GreenRoomShuffleAndJudgeTwoModesTest extends TestCase
{
    use RefreshDatabase;

    protected User $greenRoomUser;

    protected User $judgeUser;

    protected Judge $judge;

    protected Program $program;

    protected ProgramEntry $entry1;

    protected ProgramEntry $entry2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->greenRoomUser = User::factory()->create([
            'role' => 'green_room_coordinator',
        ]);

        $this->judgeUser = User::factory()->create([
            'role' => 'judge',
        ]);

        $this->judge = Judge::create([
            'user_id' => $this->judgeUser->id,
            'name' => 'Juror Test',
            'contact' => '1234567890',
            'access_code' => '9999',
            'status' => 'active',
        ]);

        $group = Group::create([
            'name' => 'Ruby Team',
            'slug' => 'ruby-team',
            'code' => 'RUB',
            'color_hex' => '#dc2626',
        ]);

        $zone = Zone::create([
            'name' => 'Senior',
            'code' => 'SNR',
            'slug' => 'senior',
        ]);

        $category = ProgramCategory::create([
            'name' => 'On Stage Arts',
            'slug' => 'on-stage-arts',
        ]);

        $stage = Stage::create([
            'name' => 'Stage 01',
            'code' => 'STG01',
            'status' => 'active',
        ]);

        $this->program = Program::create([
            'name' => 'Speech English',
            'code' => 'SP01',
            'category_id' => $category->id,
            'stage_id' => $stage->id,
            'duration_minutes' => 20,
            'type' => 'individual',
            'status' => 'upcoming',
            'shuffle_count' => 0,
        ]);

        Schedule::create([
            'program_id' => $this->program->id,
            'stage_id' => $stage->id,
            'start_time' => Carbon::parse('2026-10-15 10:00:00'),
            'end_time' => Carbon::parse('2026-10-15 10:30:00'),
        ]);

        $this->judge->programs()->attach($this->program->id);

        $student1 = Student::create([
            'group_id' => $group->id,
            'zone_id' => $zone->id,
            'student_id' => 'STU-001',
            'name' => 'Tariq Ziyad',
            'chest_number' => '101',
            'qr_token' => 'qr-stu-001',
            'category' => 'Senior',
            'status' => 'active',
        ]);

        $student2 = Student::create([
            'group_id' => $group->id,
            'zone_id' => $zone->id,
            'student_id' => 'STU-002',
            'name' => 'Hamza Ali',
            'chest_number' => '102',
            'qr_token' => 'qr-stu-002',
            'category' => 'Senior',
            'status' => 'active',
        ]);

        $this->entry1 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'group_id' => $group->id,
            'student_id' => $student1->id,
            'chest_number' => '101',
            'status' => 'verified',
            'attendance_status' => 'present',
            'code_letter' => null,
        ]);

        $this->entry2 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'group_id' => $group->id,
            'student_id' => $student2->id,
            'chest_number' => '102',
            'status' => 'verified',
            'attendance_status' => 'present',
            'code_letter' => null,
        ]);
    }

    public function test_green_room_shuffle_is_limited_to_two_chances(): void
    {
        $this->assertEquals(0, $this->program->shuffle_count);

        // Chance 1: Should succeed
        $response1 = $this->actingAs($this->greenRoomUser)
            ->post(route('greenroom.generate-codes', $this->program));

        $response1->assertRedirect();
        $this->program->refresh();
        $this->assertEquals(1, $this->program->shuffle_count);

        // Chance 2: Should succeed
        $response2 = $this->actingAs($this->greenRoomUser)
            ->post(route('greenroom.generate-codes', $this->program));

        $response2->assertRedirect();
        $this->program->refresh();
        $this->assertEquals(2, $this->program->shuffle_count);

        // Chance 3: Should fail (limit reached)
        $response3 = $this->actingAs($this->greenRoomUser)
            ->post(route('greenroom.generate-codes', $this->program));

        $response3->assertRedirect();
        $response3->assertSessionHas('error');
        $this->program->refresh();
        $this->assertEquals(2, $this->program->shuffle_count);
    }

    public function test_green_room_can_manually_update_code_letter(): void
    {
        $response = $this->actingAs($this->greenRoomUser)
            ->postJson(route('greenroom.update-code-letter', $this->entry1), [
                'code_letter' => 'k',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'code_letter' => 'K',
        ]);

        $this->entry1->refresh();
        $this->assertEquals('K', $this->entry1->code_letter);
    }

    public function test_green_room_can_batch_update_code_letters(): void
    {
        $response = $this->actingAs($this->greenRoomUser)
            ->postJson(route('greenroom.batch-update-code-letters', $this->program), [
                'codes' => [
                    $this->entry1->id => 'X',
                    $this->entry2->id => 'Y',
                ],
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'updated_count' => 2,
        ]);

        $this->entry1->refresh();
        $this->entry2->refresh();
        $this->assertEquals('X', $this->entry1->code_letter);
        $this->assertEquals('Y', $this->entry2->code_letter);
    }

    public function test_green_room_can_attend_and_shuffle_next_program_while_previous_program_is_active(): void
    {
        // Program 1 is active on stage
        $this->program->update(['status' => 'in_progress']);

        // Create Program 2 (Up Next)
        $nextProgram = Program::create([
            'name' => 'Song Malayalam',
            'code' => 'SM01',
            'category_id' => $this->program->category_id,
            'stage_id' => $this->program->stage_id,
            'duration_minutes' => 15,
            'type' => 'individual',
            'status' => 'upcoming',
            'shuffle_count' => 0,
        ]);

        Schedule::create([
            'program_id' => $nextProgram->id,
            'stage_id' => $nextProgram->stage_id,
            'start_time' => Carbon::parse('2026-10-15 10:30:00'),
            'end_time' => Carbon::parse('2026-10-15 10:45:00'),
        ]);

        $nextEntry = ProgramEntry::create([
            'program_id' => $nextProgram->id,
            'group_id' => $this->entry1->group_id,
            'student_id' => $this->entry1->student_id,
            'chest_number' => '201',
            'status' => 'verified',
            'attendance_status' => 'waiting',
            'code_letter' => null,
        ]);

        // Green room desk index loads next program with program_id param
        $indexResponse = $this->actingAs($this->greenRoomUser)
            ->get(route('greenroom.index', [
                'stage_id' => $this->program->stage_id,
                'program_id' => $nextProgram->id,
            ]));

        $indexResponse->assertOk();
        $indexResponse->assertSee('Song Malayalam');

        // Mark attendance for next program's participant
        $attResponse = $this->actingAs($this->greenRoomUser)
            ->postJson(route('greenroom.mark-attendance', $nextEntry), [
                'status' => 'present',
            ]);

        $attResponse->assertOk();
        $nextEntry->refresh();
        $this->assertEquals('present', $nextEntry->attendance_status);

        // Update code letter for next program
        $codeResponse = $this->actingAs($this->greenRoomUser)
            ->postJson(route('greenroom.update-code-letter', $nextEntry), [
                'code_letter' => 'M',
            ]);

        $codeResponse->assertOk();
        $nextEntry->refresh();
        $this->assertEquals('M', $nextEntry->code_letter);
    }

    public function test_judge_can_submit_score_in_simple_100_mode(): void
    {
        $response = $this->actingAs($this->judgeUser)
            ->postJson(route('judge.evaluate.save', [$this->program, $this->entry1]), [
                'scoring_mode' => 'simple',
                'total_score' => 88.5,
                'grade' => 'A',
                'remarks' => 'Good performance in simple mode',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'total_score' => 88.5,
            'grade' => 'A',
        ]);

        $this->assertDatabaseHas('score_sheets', [
            'program_id' => $this->program->id,
            'entry_id' => $this->entry1->id,
            'judge_id' => $this->judge->id,
            'total_score' => 88.5,
        ]);
    }

    public function test_judge_can_submit_score_in_criteria_mode(): void
    {
        $c1 = ScoringCriteria::create([
            'program_id' => $this->program->id,
            'criterion_name' => 'Fluency',
            'max_marks' => 30,
        ]);

        $c2 = ScoringCriteria::create([
            'program_id' => $this->program->id,
            'criterion_name' => 'Content',
            'max_marks' => 40,
        ]);

        $response = $this->actingAs($this->judgeUser)
            ->postJson(route('judge.evaluate.save', [$this->program, $this->entry2]), [
                'scoring_mode' => 'criteria',
                'scores' => [
                    $c1->id => 28,
                    $c2->id => 35,
                ],
                'remarks' => 'Detailed criteria evaluation',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'total_score' => 63.0,
        ]);

        $this->assertDatabaseHas('score_sheets', [
            'program_id' => $this->program->id,
            'entry_id' => $this->entry2->id,
            'judge_id' => $this->judge->id,
            'total_score' => 63.0,
        ]);
    }
}
