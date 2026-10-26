<?php

namespace Tests\Feature;

use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\OnlineSubmission;
use App\Models\OnlineSubmissionForm;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSettingAndStudentEditingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $leaderUser;

    protected Group $group;

    protected Student $student;

    protected Zone $zone;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin Controller',
            'email' => 'admin@quaf.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->leaderUser = User::create([
            'name' => 'Leader User',
            'email' => 'leader@quaf.test',
            'password' => Hash::make('password'),
            'role' => 'group_leader',
            'is_active' => true,
        ]);

        $this->group = Group::create([
            'name' => 'Yugo Rushdic',
            'code' => 'YUGO',
            'slug' => 'yugo-rushdic',
            'color_hex' => '#c026d3',
            'leader_id' => $this->leaderUser->id,
            'manager_name' => 'Team Manager',
        ]);

        $this->zone = Zone::firstOrCreate(
            ['slug' => 'a-zone'],
            ['name' => 'A Zone', 'code' => 'ZONE-A']
        );

        $this->student = Student::create([
            'student_id' => 'YUG-101',
            'group_id' => $this->group->id,
            'zone_id' => $this->zone->id,
            'name' => 'Muhamed Ali',
            'category' => 'A Zone',
            'class_level' => 'Class 4',
            'qr_token' => 'qr-test-token-123',
        ]);
    }

    public function test_admin_can_toggle_program_registration(): void
    {
        FestivalSetting::set('registration_open', '1');

        $res = $this->actingAs($this->adminUser)->post(route('admin.settings.toggle-registration'));
        $res->assertRedirect();
        $this->assertEquals('0', FestivalSetting::get('registration_open'));

        // Toggle back
        $res2 = $this->actingAs($this->adminUser)->post(route('admin.settings.toggle-registration'));
        $res2->assertRedirect();
        $this->assertEquals('1', FestivalSetting::get('registration_open'));
    }

    public function test_admin_can_toggle_student_editing(): void
    {
        FestivalSetting::set('student_editing_open', '1');

        $res = $this->actingAs($this->adminUser)->post(route('admin.settings.toggle-student-editing'));
        $res->assertRedirect();
        $this->assertEquals('0', FestivalSetting::get('student_editing_open'));

        // Toggle back
        $res2 = $this->actingAs($this->adminUser)->post(route('admin.settings.toggle-student-editing'));
        $res2->assertRedirect();
        $this->assertEquals('1', FestivalSetting::get('student_editing_open'));
    }

    public function test_admin_settings_page_renders_registration_and_student_editing_controls(): void
    {
        FestivalSetting::set('registration_open', '1');
        FestivalSetting::set('student_editing_open', '1');

        $response = $this->actingAs($this->adminUser)->get(route('admin.settings.index'));
        $response->assertStatus(200);
        $response->assertSee('Leader Program Registration Portal');
        $response->assertSee('Leader Student Name / Spelling Editing');
        $response->assertSee('Block Registration Now');
        $response->assertSee('Block Student Editing');
    }

    public function test_leader_can_edit_student_name_when_open(): void
    {
        FestivalSetting::set('student_editing_open', '1');

        $response = $this->actingAs($this->leaderUser)->putJson(route('leader.students.update', $this->student), [
            'name' => 'Muhammed Ali Corrected',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('students', [
            'id' => $this->student->id,
            'name' => 'Muhammed Ali Corrected',
        ]);
    }

    public function test_leader_cannot_edit_student_name_when_blocked_by_admin(): void
    {
        FestivalSetting::set('student_editing_open', '0');

        $response = $this->actingAs($this->leaderUser)->putJson(route('leader.students.update', $this->student), [
            'name' => 'Should Not Update',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
        $this->assertDatabaseMissing('students', [
            'id' => $this->student->id,
            'name' => 'Should Not Update',
        ]);
    }

    public function test_leader_cannot_edit_student_belonging_to_another_group(): void
    {
        FestivalSetting::set('student_editing_open', '1');

        $otherGroup = Group::create([
            'name' => 'Other Group',
            'code' => 'OTHR',
            'slug' => 'other-group',
            'color_hex' => '#000000',
        ]);

        $otherStudent = Student::create([
            'student_id' => 'OTH-101',
            'group_id' => $otherGroup->id,
            'zone_id' => $this->zone->id,
            'name' => 'Other Student',
            'category' => 'A Zone',
            'qr_token' => 'qr-oth-token',
        ]);

        $response = $this->actingAs($this->leaderUser)->putJson(route('leader.students.update', $otherStudent), [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('students', [
            'id' => $otherStudent->id,
            'name' => 'Hacked Name',
        ]);
    }

    public function test_leader_cannot_register_when_registration_blocked(): void
    {
        FestivalSetting::set('registration_open', '0');

        $program = Program::create([
            'name' => 'Essay Writing',
            'code' => 'ESS01',
            'type' => 'individual',
            'eligibility' => 'A Zone',
            'zone_id' => $this->zone->id,
            'max_participants_per_group' => 1,
            'status' => 'upcoming',
        ]);

        $response = $this->actingAs($this->leaderUser)->postJson(route('leader.registrations.store'), [
            'program_id' => $program->id,
            'student_ids' => [$this->student->id],
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertDatabaseMissing('program_entries', [
            'program_id' => $program->id,
            'student_id' => $this->student->id,
        ]);
    }

    public function test_admin_can_delete_registration_entry(): void
    {
        $program = Program::create([
            'name' => 'Speech',
            'code' => 'SPH01',
            'type' => 'individual',
            'eligibility' => 'A Zone',
            'zone_id' => $this->zone->id,
            'max_participants_per_group' => 1,
            'status' => 'upcoming',
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'chest_number' => '101',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.registrations.destroy', $entry));
        $response->assertRedirect();
        $this->assertDatabaseMissing('program_entries', [
            'id' => $entry->id,
        ]);
    }

    public function test_admin_student_update_syncs_with_online_submission_without_type_cast_errors(): void
    {
        $program = Program::create([
            'name' => 'Painting Competition',
            'code' => 'PNT01',
            'type' => 'individual',
            'eligibility' => 'A Zone',
            'zone_id' => $this->zone->id,
            'status' => 'upcoming',
        ]);

        $form = OnlineSubmissionForm::create([
            'program_id' => $program->id,
            'title' => 'Painting Form',
            'is_open' => true,
        ]);

        // Create an online submission for this student with chest number QF2004
        $submission = OnlineSubmission::create([
            'online_submission_form_id' => $form->id,
            'program_id' => $program->id,
            'code_letter' => 'A',
            'chest_number' => $this->student->student_id,
            'student_name' => $this->student->name,
            'student_id' => $this->student->student_id,
            'group_id' => $this->group->id,
        ]);

        // Create another online submission for another chest string QF3064
        OnlineSubmission::create([
            'online_submission_form_id' => $form->id,
            'program_id' => $program->id,
            'code_letter' => 'B',
            'chest_number' => 'QF3064',
            'student_name' => 'Other Participant',
            'student_id' => 'QF3064',
            'group_id' => $this->group->id,
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.students.update', $this->student), [
            'name' => 'MUHAMMED SINAN VAZHAKKAD',
            'student_id' => $this->student->student_id,
            'group_id' => $this->group->id,
            'zone_id' => $this->zone->id,
            'category' => 'A Zone',
            'class_level' => 'TQS',
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $this->assertDatabaseHas('students', [
            'id' => $this->student->id,
            'name' => 'MUHAMMED SINAN VAZHAKKAD',
        ]);

        $this->assertDatabaseHas('online_submissions', [
            'id' => $submission->id,
            'student_name' => 'MUHAMMED SINAN VAZHAKKAD',
        ]);
    }
}
