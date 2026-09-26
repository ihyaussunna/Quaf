<?php

namespace Tests\Feature;

use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegistrationChestNumberTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $leader;

    protected Group $group;

    protected ProgramCategory $category;

    protected Program $program;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Festival Admin',
            'email' => 'admin@quaf.fest',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->leader = User::create([
            'name' => 'House Leader',
            'email' => 'leader@quaf.fest',
            'password' => Hash::make('password'),
            'role' => 'group_leader',
            'is_active' => true,
        ]);

        $this->group = Group::create([
            'name' => 'PACTO HIKMIC',
            'code' => 'PACTO',
            'slug' => 'pacto-hikmic',
            'color_hex' => '#be1e2d',
            'leader_id' => $this->leader->id,
        ]);

        $this->category = ProgramCategory::create([
            'name' => 'Elocution Arts',
            'slug' => 'elocution-arts',
        ]);

        $this->program = Program::create([
            'name' => 'Arabic Speech',
            'code' => 'Q9-ARB-01',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'eligibility' => 'Senior',
            'status' => 'upcoming',
        ]);

        $this->student = Student::create([
            'student_id' => '101',
            'group_id' => $this->group->id,
            'name' => 'Ahmed Fayiz',
            'category' => 'Senior',
            'gender' => 'Male',
            'qr_token' => Str::random(32),
        ]);
    }

    public function test_admin_registration_auto_assigns_student_chest_number(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/registrations', [
            'program_id' => $this->program->id,
            'student_id' => $this->student->id,
            'status' => 'verified',
        ]);

        $response->assertRedirect(route('admin.registrations.index'));
        $this->assertDatabaseHas('program_entries', [
            'program_id' => $this->program->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'chest_number' => '101',
            'status' => 'verified',
        ]);
    }

    public function test_admin_registration_rejects_duplicate_student_with_friendly_error(): void
    {
        // First registration
        ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'chest_number' => '101',
            'status' => 'verified',
        ]);

        // Attempt second registration for same student
        $response = $this->actingAs($this->admin)->post('/admin/registrations', [
            'program_id' => $this->program->id,
            'student_id' => $this->student->id,
            'status' => 'verified',
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertEquals(1, ProgramEntry::where('program_id', $this->program->id)->count());
    }

    public function test_admin_registration_rejects_duplicate_chest_number_with_friendly_error(): void
    {
        // First registration with chest number 101
        ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'chest_number' => '101',
            'status' => 'verified',
        ]);

        // Create second student
        $student2 = Student::create([
            'student_id' => '102',
            'group_id' => $this->group->id,
            'name' => 'Tariq Jameel',
            'category' => 'Senior',
            'gender' => 'Male',
            'qr_token' => Str::random(32),
        ]);

        // Attempt manual entry with chest number 101 for group item or another entry
        $response = $this->actingAs($this->admin)->post('/admin/registrations', [
            'program_id' => $this->program->id,
            'group_id' => $this->group->id,
            'chest_number' => '101',
            'status' => 'verified',
        ]);

        $response->assertSessionHasErrors('chest_number');
        $this->assertEquals(1, ProgramEntry::where('program_id', $this->program->id)->count());
    }

    public function test_leader_registration_auto_assigns_student_chest_number(): void
    {
        // Ensure registration window is open
        FestivalSetting::set('registration_start', Carbon::now()->subDays(2)->toDateTimeString());
        FestivalSetting::set('registration_end', Carbon::now()->addDays(2)->toDateTimeString());

        $response = $this->actingAs($this->leader)->post('/leader/registrations', [
            'program_id' => $this->program->id,
            'student_id' => $this->student->id,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('program_entries', [
            'program_id' => $this->program->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'chest_number' => '101',
            'status' => 'pending',
        ]);
    }

    public function test_leader_registration_rejects_duplicate_student_gracefully(): void
    {
        FestivalSetting::set('registration_start', Carbon::now()->subDays(2)->toDateTimeString());
        FestivalSetting::set('registration_end', Carbon::now()->addDays(2)->toDateTimeString());

        // First registration
        ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'chest_number' => '101',
            'status' => 'pending',
        ]);

        // Attempt second registration
        $response = $this->actingAs($this->leader)->post('/leader/registrations', [
            'program_id' => $this->program->id,
            'student_id' => $this->student->id,
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertEquals(1, ProgramEntry::where('program_id', $this->program->id)->count());
    }
}
