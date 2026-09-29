<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\ScoreSheet;
use App\Models\ScoringCriteria;
use App\Models\Stage;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallListAndEvaluationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $greenRoomUser;

    protected User $judgeUser1;

    protected User $judgeUser2;

    protected Judge $judge1;

    protected Judge $judge2;

    protected Group $group;

    protected ProgramCategory $category;

    protected Stage $stage;

    protected Program $program;

    protected Student $student1;

    protected Student $student2;

    protected Student $student3;

    protected ProgramEntry $entry1;

    protected ProgramEntry $entry2;

    protected ProgramEntry $entry3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->greenRoomUser = User::factory()->create([
            'role' => 'green_room_coordinator',
        ]);

        $this->judgeUser1 = User::factory()->create([
            'role' => 'judge',
        ]);

        $this->judgeUser2 = User::factory()->create([
            'role' => 'judge',
        ]);

        $this->judge1 = Judge::create([
            'user_id' => $this->judgeUser1->id,
            'name' => 'Dr. Rahman Judge',
            'phone' => '9876543211',
            'pin' => '1111',
            'status' => 'active',
        ]);

        $this->judge2 = Judge::create([
            'user_id' => $this->judgeUser2->id,
            'name' => 'Prof. Hameed Judge',
            'phone' => '9876543212',
            'pin' => '2222',
            'status' => 'active',
        ]);

        $this->group = Group::create([
            'name' => 'Team Alpha',
            'slug' => 'team-alpha',
            'code' => 'ALP',
            'color_hex' => '#be1e2d',
        ]);

        $this->category = ProgramCategory::create([
            'name' => 'Arts and Elocution',
            'slug' => 'arts-elocution',
        ]);

        $this->stage = Stage::create([
            'name' => 'Stage 1 - Main Auditorium',
            'code' => 'STG01',
            'status' => 'active',
        ]);

        $this->program = Program::create([
            'name' => 'Malayalam Speech',
            'code' => 'MS01',
            'category_id' => $this->category->id,
            'stage_id' => $this->stage->id,
            'eligibility' => 'General',
            'type' => 'individual',
            'status' => 'in_progress',
        ]);

        $this->program->judges()->attach([$this->judge1->id, $this->judge2->id]);

        ScoringCriteria::create([
            'program_id' => $this->program->id,
            'criterion_name' => 'Content & Fluency',
            'max_marks' => 50,
        ]);

        ScoringCriteria::create([
            'program_id' => $this->program->id,
            'criterion_name' => 'Presentation & Timing',
            'max_marks' => 50,
        ]);

        $zone = Zone::firstOrCreate(
            ['slug' => 'general-zone'],
            ['name' => 'General Zone', 'code' => 'GEN-Z']
        );

        $this->student1 = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $zone->id,
            'student_id' => 'STU-001',
            'name' => 'Zayd Abdullah',
            'chest_number' => '101',
            'category' => 'General',
            'qr_token' => 'qr-stu-001',
            'status' => 'active',
        ]);

        $this->student2 = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $zone->id,
            'student_id' => 'STU-002',
            'name' => 'Bilal Ahmed',
            'chest_number' => '102',
            'category' => 'General',
            'qr_token' => 'qr-stu-002',
            'status' => 'active',
        ]);

        $this->student3 = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $zone->id,
            'student_id' => 'STU-003',
            'name' => 'Tariq Anwar',
            'chest_number' => '103',
            'category' => 'General',
            'qr_token' => 'qr-stu-003',
            'status' => 'active',
        ]);

        $this->entry1 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $this->student1->id,
            'group_id' => $this->group->id,
            'chest_number' => '101',
            'status' => 'verified',
            'attendance_status' => 'waiting',
        ]);

        $this->entry2 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $this->student2->id,
            'group_id' => $this->group->id,
            'chest_number' => '102',
            'status' => 'verified',
            'attendance_status' => 'waiting',
        ]);

        $this->entry3 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $this->student3->id,
            'group_id' => $this->group->id,
            'chest_number' => '103',
            'status' => 'verified',
            'attendance_status' => 'waiting',
        ]);
    }

    public function test_registered_students_appear_automatically_in_call_lists(): void
    {
        // 1. Green Room Call List
        $greenRoomResponse = $this->actingAs($this->greenRoomUser)
            ->get(route('greenroom.call-list', ['program_id' => $this->program->id]));

        $greenRoomResponse->assertOk();
        $greenRoomResponse->assertSee('Zayd Abdullah');
        $greenRoomResponse->assertSee('101');
        $greenRoomResponse->assertSee('102');
        $greenRoomResponse->assertSee('103');

        // 2. Admin Call List & Attendance Center
        $adminResponse = $this->actingAs($this->admin)
            ->get(route('admin.call-list.index', ['program_id' => $this->program->id]));

        $adminResponse->assertOk();
        $adminResponse->assertSee('Call List &amp; Attendance Center', false);
        $adminResponse->assertSee('101');
        $adminResponse->assertSee('102');
        $adminResponse->assertSee('103');
    }

    public function test_green_room_and_admin_can_mark_attendance_with_automatic_code_assignment(): void
    {
        // Mark entry 1 as PRESENT via Green Room AJAX
        $response1 = $this->actingAs($this->greenRoomUser)
            ->postJson(route('greenroom.mark-attendance', $this->entry1), [
                'status' => 'present',
            ]);

        $response1->assertOk();
        $response1->assertJson([
            'success' => true,
            'attendance_status' => 'present',
            'code_letter' => 'A',
            'display_code' => 'Code A',
        ]);

        $this->entry1->refresh();
        $this->assertEquals('present', $this->entry1->attendance_status);
        $this->assertEquals('A', $this->entry1->code_letter);

        // Mark entry 2 as PRESENT via Admin AJAX - should receive next sequential code letter 'B'
        $response2 = $this->actingAs($this->admin)
            ->postJson(route('admin.call-list.attendance', $this->entry2), [
                'status' => 'present',
            ]);

        $response2->assertOk();
        $response2->assertJson([
            'success' => true,
            'attendance_status' => 'present',
            'code_letter' => 'B',
            'display_code' => 'Code B',
        ]);

        $this->entry2->refresh();
        $this->assertEquals('present', $this->entry2->attendance_status);
        $this->assertEquals('B', $this->entry2->code_letter);

        // Mark entry 3 as ABSENT - code letter should remain null
        $response3 = $this->actingAs($this->admin)
            ->postJson(route('admin.call-list.attendance', $this->entry3), [
                'status' => 'absent',
            ]);

        $response3->assertOk();
        $this->entry3->refresh();
        $this->assertEquals('absent', $this->entry3->attendance_status);
        $this->assertNull($this->entry3->code_letter);
    }

    public function test_judge_panel_strictly_filters_present_participants_and_preserves_anonymity(): void
    {
        // Setup: Entry 1 present (Code A), Entry 2 present (Code B), Entry 3 absent
        $this->entry1->update(['attendance_status' => 'present', 'code_letter' => 'A']);
        $this->entry2->update(['attendance_status' => 'present', 'code_letter' => 'B']);
        $this->entry3->update(['attendance_status' => 'absent', 'code_letter' => null]);

        $response = $this->actingAs($this->judgeUser1)
            ->get(route('judge.evaluate', $this->program));

        $response->assertOk();

        // Must see Anonymous Code Letters
        $response->assertSee('Code A');
        $response->assertSee('Code B');

        // Must NEVER reveal student real identity to judges
        $response->assertDontSee('Zayd Abdullah');
        $response->assertDontSee('Bilal Ahmed');
        $response->assertDontSee('Tariq Anwar');
        $response->assertDontSee('Team Alpha');

        // Must NOT list absent candidate
        $response->assertDontSee('Code C');
        $response->assertDontSee('103');
    }

    public function test_judge_cannot_submit_scores_for_absent_or_waiting_participant(): void
    {
        // Entry 3 is absent
        $this->entry3->update(['attendance_status' => 'absent', 'code_letter' => null]);

        $criteria = $this->program->scoringCriteria->pluck('id')->all();

        $response = $this->actingAs($this->judgeUser1)
            ->postJson(route('judge.evaluate.save', [$this->program, $this->entry3]), [
                'scores' => [
                    $criteria[0] => 45,
                    $criteria[1] => 40,
                ],
                'remarks' => 'Illegal attempt on absent student',
            ]);

        // Expect HTTP 422 Unprocessable Entity
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        $this->assertDatabaseMissing('score_sheets', [
            'judge_id' => $this->judge1->id,
            'entry_id' => $this->entry3->id,
        ]);
    }

    public function test_multiple_judges_can_evaluate_and_scores_are_averaged_independently(): void
    {
        $this->entry1->update(['attendance_status' => 'present', 'code_letter' => 'A']);
        $this->entry2->update(['attendance_status' => 'present', 'code_letter' => 'B']);

        $criteria = $this->program->scoringCriteria->pluck('id')->all();

        // Judge 1 evaluates Entry 1: 45 + 45 = 90
        $this->actingAs($this->judgeUser1)
            ->postJson(route('judge.evaluate.save', [$this->program, $this->entry1]), [
                'scores' => [
                    $criteria[0] => 45,
                    $criteria[1] => 45,
                ],
            ])->assertOk();

        // Judge 2 evaluates Entry 1: 35 + 35 = 70
        $this->actingAs($this->judgeUser2)
            ->postJson(route('judge.evaluate.save', [$this->program, $this->entry1]), [
                'scores' => [
                    $criteria[0] => 35,
                    $criteria[1] => 35,
                ],
            ])->assertOk();

        // Judge 1 evaluates Entry 2: 40 + 40 = 80
        $this->actingAs($this->judgeUser1)
            ->postJson(route('judge.evaluate.save', [$this->program, $this->entry2]), [
                'scores' => [
                    $criteria[0] => 40,
                    $criteria[1] => 40,
                ],
            ])->assertOk();

        // Two score sheets exist for Entry 1
        $this->assertEquals(2, ScoreSheet::where('entry_id', $this->entry1->id)->count());
        $this->assertEquals(90, ScoreSheet::where('entry_id', $this->entry1->id)->where('judge_id', $this->judge1->id)->value('total_score'));
        $this->assertEquals(70, ScoreSheet::where('entry_id', $this->entry1->id)->where('judge_id', $this->judge2->id)->value('total_score'));

        // Admin Evaluation Details: Average for Entry 1 is (90 + 70) / 2 = 80.0
        $evalDetails = $this->actingAs($this->admin)
            ->get(route('admin.evaluation-monitor.show', $this->program));

        $evalDetails->assertOk();
        $evalDetails->assertSee('80.00'); // Average for Entry 1
        $evalDetails->assertSee('Code A');
        $evalDetails->assertSee('Code B');

        // Admin Judge Marks Dashboard lists both sheets
        $judgeMarksResponse = $this->actingAs($this->admin)
            ->get(route('admin.judge-marks.index', ['program_id' => $this->program->id]));

        $judgeMarksResponse->assertOk();
        $judgeMarksResponse->assertSee('Dr. Rahman Judge');
        $judgeMarksResponse->assertSee('Prof. Hameed Judge');
        $judgeMarksResponse->assertSee('90.00');
        $judgeMarksResponse->assertSee('70.00');
    }

    public function test_admin_call_list_index_shows_program_call_lists_and_dashboard_is_clean(): void
    {
        $this->entry1->update(['attendance_status' => 'present', 'code_letter' => 'A']);
        $this->entry2->update(['attendance_status' => 'absent', 'code_letter' => null]);
        $this->entry3->update(['attendance_status' => 'waiting', 'code_letter' => null]);

        // 1. Dashboard should be clean without the Call List workflow table
        $dashboardResponse = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $dashboardResponse->assertOk();
        $dashboardResponse->assertDontSee('Recent Participant Call Status Updates');
        $dashboardResponse->assertDontSee('Call List, Attendance &amp; Evaluation Workflow', false);

        // 2. Call List center displays each program as its own Call List (One Program = One Call List)
        $callListResponse = $this->actingAs($this->admin)
            ->get(route('admin.call-list.index'));

        $callListResponse->assertOk();
        $callListResponse->assertSee('Call List &amp; Attendance Center', false);
        $callListResponse->assertSee($this->program->name);
        $callListResponse->assertSee('Open Call List');
        $callListResponse->assertSee('1 Present');
        $callListResponse->assertSee('1 Absent');
        $callListResponse->assertSee('1 Waiting');
    }

    public function test_admin_can_reset_individual_program_and_call_list_lock(): void
    {
        $this->program->update([
            'status' => 'completed',
            'is_call_list_locked' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.call-list.reset-program', $this->program));

        $response->assertRedirect();
        $this->program->refresh();
        $this->assertEquals('upcoming', $this->program->status);
        $this->assertFalse((bool) $this->program->is_call_list_locked);
    }

    public function test_admin_can_reset_all_programs_and_locks(): void
    {
        $this->program->update([
            'status' => 'completed',
            'is_call_list_locked' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.call-list.reset-all'));

        $response->assertRedirect();
        $this->program->refresh();
        $this->assertEquals('upcoming', $this->program->status);
        $this->assertFalse((bool) $this->program->is_call_list_locked);
    }
}
