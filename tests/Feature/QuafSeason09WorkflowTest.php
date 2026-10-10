<?php

namespace Tests\Feature;

use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Stage;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuafSeason09WorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Stage $stage;

    protected ProgramCategory $category;

    protected Group $group;

    protected Program $program;

    protected Student $student1;

    protected Student $student2;

    protected ProgramEntry $entry1;

    protected ProgramEntry $entry2;

    protected Judge $judge;

    protected User $greenRoomUser;

    protected User $leaderUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stage = Stage::create([
            'name' => 'Main Stage',
            'code' => 'STG-01',
            'capacity' => 300,
        ]);

        $this->category = ProgramCategory::create([
            'name' => 'Islamic Science',
            'slug' => 'islamic-science',
        ]);

        $this->group = Group::create([
            'name' => 'Al Quds House',
            'slug' => 'al-quds',
            'code' => 'QUDS',
            'color_hex' => '#b4831f',
        ]);

        $this->program = Program::create([
            'name' => 'QURAN RECITATION',
            'malayalam_name' => 'ഖുർആൻ പാരായണം',
            'code' => 'P-101',
            'type' => 'individual',
            'category_id' => $this->category->id,
            'stage_id' => $this->stage->id,
            'duration_minutes' => 15,
            'status' => 'upcoming',
        ]);

        $this->program->scoringCriteria()->create([
            'criterion_name' => 'Tajweed & Tartheel',
            'max_marks' => 50,
        ]);

        $this->student1 = Student::create([
            'student_id' => 'QUAF-ST-101',
            'name' => 'Zayd Haris',
            'group_id' => $this->group->id,
            'category' => 'Senior',
            'gender' => 'Male',
            'qr_token' => Str::random(32),
        ]);

        $this->student2 = Student::create([
            'student_id' => 'QUAF-ST-102',
            'name' => 'Bilal Mahmoud',
            'group_id' => $this->group->id,
            'category' => 'Senior',
            'gender' => 'Male',
            'qr_token' => Str::random(32),
        ]);

        $this->entry1 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $this->student1->id,
            'group_id' => $this->group->id,
            'chest_number' => 'CH-101',
            'status' => 'verified',
            'attendance_status' => 'waiting',
        ]);

        $this->entry2 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $this->student2->id,
            'group_id' => $this->group->id,
            'chest_number' => 'CH-102',
            'status' => 'verified',
            'attendance_status' => 'waiting',
        ]);

        $this->judge = Judge::create([
            'name' => 'Usthad Ibrahim',
            'access_code' => '4567',
            'designation' => 'Senior Qari',
        ]);

        $this->judge->programs()->attach($this->program->id);

        $this->greenRoomUser = User::factory()->create([
            'role' => 'green_room_coordinator',
        ]);

        $this->leaderUser = User::factory()->create([
            'role' => 'group_leader',
        ]);
        $this->group->update(['leader_id' => $this->leaderUser->id]);
    }

    public function test_judge_can_login_with_4_digit_pin_and_access_evaluation(): void
    {
        // 1. Visit PIN login screen
        $response = $this->get('/judge/login');
        $response->assertStatus(200);
        $response->assertSee('Judge Portal Login');

        // 2. Submit valid 4-digit PIN
        $loginResponse = $this->post('/judge/login', [
            'pin' => '4567',
        ]);

        $loginResponse->assertRedirect(route('judge.dashboard'));
        $this->assertAuthenticated();

        // 3. Judge dashboard displays assigned program
        $dashResponse = $this->get('/judge');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('QURAN RECITATION');
    }

    public function test_judge_evaluation_is_strictly_anonymous(): void
    {
        // Assign code letters and mark present
        $this->entry1->update(['code_letter' => 'A', 'attendance_status' => 'present']);
        $this->entry2->update(['code_letter' => 'B', 'attendance_status' => 'present']);

        // Log in judge
        $this->post('/judge/login', ['pin' => '4567']);

        // View evaluate screen
        $evalResponse = $this->get("/judge/evaluate/{$this->program->id}");
        $evalResponse->assertStatus(200);

        // Asserts code letters are visible
        $evalResponse->assertSee('Code A');
        $evalResponse->assertSee('Code B');

        // Asserts strictly NO student names, NO house names, and NO chest numbers in judge view
        $evalResponse->assertDontSee($this->student1->name);
        $evalResponse->assertDontSee($this->student2->name);
        $evalResponse->assertDontSee($this->group->name);
        $evalResponse->assertDontSee('CH-101');
        $evalResponse->assertDontSee('CH-102');
    }

    public function test_green_room_attendance_and_random_code_generation(): void
    {
        // 1. Mark attendance as present
        $attendanceResponse = $this->actingAs($this->greenRoomUser)->post("/greenroom/attendance/{$this->entry1->id}", [
            'status' => 'present',
        ]);
        $attendanceResponse->assertRedirect();
        $this->assertEquals('present', $this->entry1->fresh()->attendance_status);

        $this->actingAs($this->greenRoomUser)->post("/greenroom/attendance/{$this->entry2->id}", [
            'status' => 'present',
        ]);
        $this->assertEquals('present', $this->entry2->fresh()->attendance_status);

        // 2. Trigger random code letter shuffle
        $shuffleResponse = $this->actingAs($this->greenRoomUser)->post("/greenroom/generate-codes/{$this->program->id}");
        $shuffleResponse->assertRedirect();

        $entry1Code = $this->entry1->fresh()->code_letter;
        $entry2Code = $this->entry2->fresh()->code_letter;

        $this->assertNotNull($entry1Code);
        $this->assertNotNull($entry2Code);
        $this->assertNotEquals($entry1Code, $entry2Code);
        $this->assertTrue(in_array($entry1Code, ['A', 'B']));
        $this->assertTrue(in_array($entry2Code, ['A', 'B']));
    }

    public function test_student_can_login_with_chest_number_and_view_scratch_card(): void
    {
        $this->entry1->update(['code_letter' => 'A']);

        // 1. Visit student login page
        $loginPage = $this->get('/student/login');
        $loginPage->assertStatus(200);
        $loginPage->assertSee('Student Portal Login');

        // 2. Login by Chest Number
        $loginResponse = $this->post('/student/login', [
            'identifier' => 'CH-101',
        ]);
        $loginResponse->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticated();

        // 3. Student dashboard displays candidate programs (anonymous code letters are hidden from students)
        $dashboardResponse = $this->get('/student');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertDontSee('Anonymous Code Scratch Card');
    }

    public function test_leader_registration_window_enforcement(): void
    {
        // Set registration window to the past (closed)
        FestivalSetting::set('registration_start', Carbon::now()->subDays(10)->toDateTimeString());
        FestivalSetting::set('registration_end', Carbon::now()->subDays(2)->toDateTimeString());

        $newStudent = Student::create([
            'student_id' => 'QUAF-ST-200',
            'name' => 'Tariq Ali',
            'group_id' => $this->group->id,
            'category' => 'Junior',
            'gender' => 'Male',
            'qr_token' => Str::random(32),
        ]);

        $response = $this->actingAs($this->leaderUser)->post('/leader/registrations', [
            'program_id' => $this->program->id,
            'student_id' => $newStudent->id,
            'chest_number' => 'CH-999',
        ]);

        $response->assertSessionHasErrors('registration');
        $this->assertDatabaseMissing('program_entries', ['chest_number' => 'CH-999']);
    }

    public function test_user_can_login_with_username_or_email_and_password(): void
    {
        $admin = User::create([
            'name' => 'Admin Controller',
            'email' => 'controller@quaf.fest',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 1. Visit login view
        $viewResponse = $this->get('/login');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Login to Festival');
        $viewResponse->assertSee('name="username"', false);
        $viewResponse->assertSee('name="password"', false);

        // 2. Login using email
        $loginEmailResponse = $this->post('/login', [
            'username' => 'controller@quaf.fest',
            'password' => 'secret123',
        ]);
        $loginEmailResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        Auth::logout();

        // 3. Login using name/username prefix
        $loginNameResponse = $this->post('/login', [
            'username' => 'Admin Controller',
            'password' => 'secret123',
        ]);
        $loginNameResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }
}
