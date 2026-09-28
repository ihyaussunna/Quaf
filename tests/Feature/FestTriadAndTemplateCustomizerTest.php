<?php

namespace Tests\Feature;

use App\Models\GreenRoomCall;
use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\ResultTemplate;
use App\Models\Stage;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FestTriadAndTemplateCustomizerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $judgeUser;

    protected Judge $judge;

    protected Program $program;

    protected Stage $stage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->judgeUser = User::factory()->create([
            'role' => 'judge',
        ]);

        $this->judge = Judge::create([
            'user_id' => $this->judgeUser->id,
            'name' => 'Test Judge',
            'phone' => '9876543210',
            'pin' => '1234',
            'status' => 'active',
        ]);

        $category = ProgramCategory::create([
            'name' => 'General Category',
            'slug' => 'general-category',
        ]);

        $this->stage = Stage::create([
            'name' => 'Stage 1 - Main Auditorium',
            'code' => 'STG01',
            'status' => 'live',
        ]);

        $this->program = Program::create([
            'name' => 'Mappilappattu Solo',
            'malayalam_name' => 'മാപ്പിളപ്പാട്ട്',
            'code' => 'MPP01',
            'category_id' => $category->id,
            'stage_id' => $this->stage->id,
            'type' => 'individual',
            'eligibility' => 'Zone A',
            'duration_minutes' => 10,
            'status' => 'in_progress',
            'is_call_list_locked' => false,
        ]);

        // Assign judge to program
        $this->judge->programs()->attach($this->program->id);

        $this->stage->update(['current_program_id' => $this->program->id]);
    }

    public function test_template_customizer_renders_and_saves_settings(): void
    {
        $template = ResultTemplate::create([
            'name' => 'QUAF Golden Glory Template',
            'image_path' => '/images/test-template.png',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('media.results.templates.customize', $template));
        $response->assertStatus(200);
        $response->assertSee('QUAF Golden Glory Template');

        $customSettings = [
            'result_x' => 800,
            'result_y' => 250,
            'result_size' => 80,
            'result_weight' => '700',
            'result_color' => '#be1e2d',
            'category_x' => 540,
            'category_y' => 300,
            'category_size' => 35,
            'category_weight' => '600',
            'category_color' => '#ffffff',
            'category_align' => 'center',
            'competition_x' => 540,
            'competition_y' => 360,
            'competition_size' => 50,
            'competition_weight' => '700',
            'competition_color' => '#ffffff',
            'competition_align' => 'center',
            'block_left' => 400,
            'first_top' => 500,
            'row_gap' => 100,
            'item_gap' => 15,
            'medal_size' => 65,
            'winner_name_size' => 36,
            'winner_name_weight' => '700',
            'winner_name_color' => '#ffffff',
            'winner_unit_size' => 24,
            'winner_unit_weight' => '400',
            'winner_unit_color' => '#e2e8f0',
        ];

        $saveResponse = $this->actingAs($this->admin)->postJson(route('media.results.templates.save-customization', $template), [
            'settings' => $customSettings,
        ]);

        $saveResponse->assertStatus(200);
        $saveResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('poster_settings', [
            'template_id' => $template->id,
            'result_x' => 800,
            'winner_name_size' => 36,
        ]);
    }

    public function test_call_list_locking_and_attendance_protection(): void
    {
        $group = Group::create(['name' => 'Red Fox', 'code' => 'RF', 'slug' => 'red-fox']);
        $student1 = Student::create([
            'student_id' => 'STU101',
            'name' => 'Participant One',
            'chest_number' => '101',
            'group_id' => $group->id,
            'gender' => 'male',
            'qr_token' => Str::random(32),
        ]);
        $student2 = Student::create([
            'student_id' => 'STU102',
            'name' => 'Participant Two',
            'chest_number' => '102',
            'group_id' => $group->id,
            'gender' => 'male',
            'qr_token' => Str::random(32),
        ]);

        $entry1 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $student1->id,
            'group_id' => $group->id,
            'chest_number' => '101',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        $entry2 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $student2->id,
            'group_id' => $group->id,
            'chest_number' => '102',
            'status' => 'verified',
            'attendance_status' => 'absent',
        ]);

        // Auto shuffle code letters
        $shuffleResponse = $this->actingAs($this->admin)->post(route('greenroom.generate-codes', $this->program->id));
        $shuffleResponse->assertSessionHas('success');

        $entry1->refresh();
        $entry2->refresh();

        $this->assertNotNull($entry1->code_letter);
        $this->assertNull($entry2->code_letter); // Absent must have no code letter

        // Lock call list
        $lockResponse = $this->actingAs($this->admin)->post(route('greenroom.toggle-lock', $this->program->id));
        $lockResponse->assertSessionHas('success');

        $this->program->refresh();
        $this->assertTrue((bool) $this->program->is_call_list_locked);

        // Attempting to change attendance when locked must be blocked
        $blockedAttendance = $this->actingAs($this->admin)->post(route('greenroom.mark-attendance', $entry1->id), [
            'status' => 'absent',
        ]);
        $blockedAttendance->assertSessionHas('error');

        // Attempting to re-shuffle code letters when locked must be blocked
        $blockedShuffle = $this->actingAs($this->admin)->post(route('greenroom.generate-codes', $this->program->id));
        $blockedShuffle->assertSessionHas('error');
    }

    public function test_judge_evaluation_sheet_excludes_absent_participants(): void
    {
        $group = Group::create(['name' => 'Green Warriors', 'code' => 'GW', 'slug' => 'green-warriors']);
        $student1 = Student::create([
            'student_id' => 'STU201',
            'name' => 'Present Student',
            'chest_number' => '201',
            'group_id' => $group->id,
            'gender' => 'male',
            'qr_token' => Str::random(32),
        ]);
        $student2 = Student::create([
            'student_id' => 'STU202',
            'name' => 'Absent Student',
            'chest_number' => '202',
            'group_id' => $group->id,
            'gender' => 'male',
            'qr_token' => Str::random(32),
        ]);

        $entry1 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $student1->id,
            'group_id' => $group->id,
            'chest_number' => '201',
            'status' => 'verified',
            'attendance_status' => 'present',
            'code_letter' => 'A',
        ]);

        $entry2 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $student2->id,
            'group_id' => $group->id,
            'chest_number' => '202',
            'status' => 'verified',
            'attendance_status' => 'absent',
            'code_letter' => null,
        ]);

        $this->program->update(['is_call_list_locked' => true]);

        $response = $this->actingAs($this->judgeUser)->get(route('judge.evaluate', $this->program->id));
        $response->assertStatus(200);

        // Evaluation sheet must include Present Student's Code A
        $response->assertSee('Code A');

        // Evaluation sheet must only have 1 candidate
        $response->assertViewHas('entries', function ($entries) use ($entry1, $entry2) {
            return $entries->contains('id', $entry1->id) && ! $entries->contains('id', $entry2->id);
        });
    }

    public function test_announcer_stage_calling_console_flow(): void
    {
        $group = Group::create(['name' => 'Blue Riders', 'code' => 'BR', 'slug' => 'blue-riders']);
        $student = Student::create([
            'student_id' => 'STU301',
            'name' => 'Stage Star',
            'chest_number' => '301',
            'group_id' => $group->id,
            'gender' => 'male',
            'qr_token' => Str::random(32),
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'chest_number' => '301',
            'status' => 'verified',
            'attendance_status' => 'present',
            'code_letter' => 'A',
        ]);

        $call = GreenRoomCall::create([
            'program_id' => $this->program->id,
            'entry_id' => $entry->id,
            'order_num' => 1,
            'status' => 'ready',
        ]);

        $response = $this->actingAs($this->admin)->get(route('announcer.stage', ['stage_id' => $this->stage->id]));
        $response->assertStatus(200);
        $response->assertSee('Mappilappattu Solo');
        $response->assertSee('Stage Star');

        // Call to stage
        $callResponse = $this->actingAs($this->admin)->post(route('announcer.call-stage', $call->id));
        $callResponse->assertSessionHas('success');
        $this->assertEquals('called', $call->fresh()->status);

        // Enter stage
        $enterResponse = $this->actingAs($this->admin)->post(route('announcer.enter-stage', $call->id));
        $enterResponse->assertSessionHas('success');
        $this->assertEquals('on_stage', $call->fresh()->status);

        // Complete stage
        $doneResponse = $this->actingAs($this->admin)->post(route('announcer.complete-stage', $call->id));
        $doneResponse->assertSessionHas('success');
        $this->assertEquals('completed', $call->fresh()->status);
    }
}
