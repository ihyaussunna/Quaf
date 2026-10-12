<?php

namespace Tests\Feature;

use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminRegistrationEnhancedWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Group $group1;

    protected Group $group2;

    protected Zone $zoneA;

    protected Student $student1;

    protected Student $student2;

    protected Student $student3;

    protected Program $indProgram;

    protected Program $groupProgram;

    protected function setUp(): void
    {
        parent::setUp();

        FestivalSetting::set('registration_open', '1');

        $this->adminUser = User::create([
            'name' => 'Festival Administrator',
            'email' => 'admin@quaf.fest',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->group1 = Group::create([
            'name' => 'LUMO FIKRIC',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#56286b',
        ]);

        $this->group2 = Group::create([
            'name' => 'PACTO HIKMIC',
            'code' => 'PACTO',
            'slug' => 'pacto-hikmic',
            'color_hex' => '#be1e2d',
        ]);

        $this->zoneA = Zone::firstOrCreate(
            ['slug' => 'a-zone'],
            ['name' => 'A Zone', 'code' => 'ZONE-A']
        );

        $this->student1 = Student::create([
            'group_id' => $this->group1->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => '101',
            'name' => 'Ahmed Fayiz',
            'class_level' => '10',
            'qr_token' => 'qr-token-101',
            'is_active' => true,
        ]);

        $this->student2 = Student::create([
            'group_id' => $this->group1->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => '102',
            'name' => 'Bilal Hashim',
            'class_level' => '10',
            'qr_token' => 'qr-token-102',
            'is_active' => true,
        ]);

        $this->student3 = Student::create([
            'group_id' => $this->group2->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => '201',
            'name' => 'Zayd Khalid',
            'class_level' => '10',
            'qr_token' => 'qr-token-201',
            'is_active' => true,
        ]);

        $category = ProgramCategory::firstOrCreate(
            ['slug' => 'arts'],
            ['name' => 'Arts', 'code' => 'ART']
        );

        $this->indProgram = Program::create([
            'category_id' => $category->id,
            'code' => 'Q9-IND-01',
            'name' => 'Elocution Arts',
            'slug' => 'elocution-arts',
            'type' => 'individual',
            'zone_id' => $this->zoneA->id,
            'participant_count' => 2,
            'is_active' => true,
            'status' => 'upcoming',
        ]);

        $this->groupProgram = Program::create([
            'category_id' => $category->id,
            'code' => 'Q9-GRP-01',
            'name' => 'Group Song',
            'slug' => 'group-song',
            'type' => 'group',
            'zone_id' => $this->zoneA->id,
            'participant_count' => 5,
            'is_active' => true,
            'status' => 'upcoming',
        ]);
    }

    public function test_admin_create_registration_view_renders_with_group_and_quota_data(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.registrations.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Program Entry & Participant Roster');
        $response->assertSee('LUMO FIKRIC');
        $response->assertSee('PACTO HIKMIC');
        $response->assertSee('window.quafRegistrationData', false);
    }

    public function test_admin_can_register_individual_program_with_multiple_participants_via_ajax(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson(route('admin.registrations.store'), [
            'group_id' => $this->group1->id,
            'program_id' => $this->indProgram->id,
            'student_ids' => [$this->student1->id, $this->student2->id],
            'status' => 'verified',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'enrolled_count' => 2,
        ]);

        $this->assertDatabaseHas('program_entries', [
            'program_id' => $this->indProgram->id,
            'group_id' => $this->group1->id,
            'student_id' => $this->student1->id,
            'status' => 'verified',
        ]);

        $this->assertDatabaseHas('program_entries', [
            'program_id' => $this->indProgram->id,
            'group_id' => $this->group1->id,
            'student_id' => $this->student2->id,
            'status' => 'verified',
        ]);
    }

    public function test_admin_can_register_group_program_with_leader_via_ajax(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson(route('admin.registrations.store'), [
            'group_id' => $this->group1->id,
            'program_id' => $this->groupProgram->id,
            'leader_id' => $this->student2->id,
            'student_ids' => [$this->student1->id, $this->student2->id],
            'status' => 'verified',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_group' => true,
            'enrolled_count' => 2,
        ]);

        $entry = ProgramEntry::where('program_id', $this->groupProgram->id)->where('group_id', $this->group1->id)->first();
        $this->assertNotNull($entry);
        $this->assertEquals($this->student2->id, $entry->student_id);
        $this->assertEquals(2, $entry->participants()->count());
    }

    public function test_admin_can_delete_all_registrations_for_program_and_group(): void
    {
        ProgramEntry::create([
            'program_id' => $this->indProgram->id,
            'group_id' => $this->group1->id,
            'student_id' => $this->student1->id,
            'chest_number' => '101',
            'status' => 'verified',
        ]);

        $response = $this->actingAs($this->adminUser)->deleteJson(
            route('admin.registrations.destroy-by-program', $this->indProgram->id).'?group_id='.$this->group1->id
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('program_entries', [
            'program_id' => $this->indProgram->id,
            'group_id' => $this->group1->id,
        ]);
    }
}
