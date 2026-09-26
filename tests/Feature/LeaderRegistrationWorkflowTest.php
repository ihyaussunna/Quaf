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

class LeaderRegistrationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $leaderUser;

    protected User $adminUser;

    protected Group $group;

    protected Zone $zone;

    protected Student $student1;

    protected Student $student2;

    protected Program $indProgram;

    protected Program $groupProgram;

    protected function setUp(): void
    {
        parent::setUp();

        FestivalSetting::set('registration_open', '1');

        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@quaf.fest',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->leaderUser = User::create([
            'name' => 'Leader User',
            'email' => 'leader@quaf.fest',
            'password' => Hash::make('password'),
            'role' => 'group_leader',
            'is_active' => true,
        ]);

        $this->group = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#56286b',
            'leader_id' => $this->leaderUser->id,
            'manager_name' => 'WARIS ADANY',
        ]);

        $this->zone = Zone::firstOrCreate(
            ['slug' => 'a-zone'],
            ['name' => 'A Zone', 'code' => 'ZONE-A']
        );

        $this->student1 = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $this->zone->id,
            'student_id' => 'QF1001',
            'name' => 'STUDENT ONE',
            'class_level' => '10',
            'qr_token' => 'qr-token-qf1001',
            'is_active' => true,
        ]);

        $this->student2 = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $this->zone->id,
            'student_id' => 'QF1002',
            'name' => 'STUDENT TWO',
            'class_level' => '10',
            'qr_token' => 'qr-token-qf1002',
            'is_active' => true,
        ]);

        $category = ProgramCategory::firstOrCreate(
            ['slug' => 'stage'],
            ['name' => 'Stage', 'code' => 'STG']
        );

        $this->indProgram = Program::create([
            'category_id' => $category->id,
            'code' => 'IND01',
            'name' => 'Elocution',
            'slug' => 'elocution',
            'type' => 'individual',
            'zone_id' => $this->zone->id,
            'participant_count' => 1,
            'is_active' => true,
        ]);

        $this->groupProgram = Program::create([
            'category_id' => $category->id,
            'code' => 'GRP01',
            'name' => 'Group Song',
            'slug' => 'group-song',
            'type' => 'group',
            'zone_id' => $this->zone->id,
            'participant_count' => 5,
            'is_active' => true,
        ]);
    }

    public function test_leader_can_view_registration_portal_with_counts(): void
    {
        $response = $this->actingAs($this->leaderUser)->get(route('leader.registrations'));
        $response->assertStatus(200);
        $response->assertSee('Registered Program Entries');
        $response->assertSee('Unregistered Programs');
        $response->assertSee('Elocution');
        $response->assertSee('Group Song');
    }

    public function test_leader_can_register_individual_and_group_programs_with_leader_designation(): void
    {
        // 1. Register individual program
        $resInd = $this->actingAs($this->leaderUser)->post(route('leader.registrations.store'), [
            'program_id' => $this->indProgram->id,
            'zone' => $this->zone->name,
            'student_id' => $this->student1->id,
            'chest_number' => $this->student1->student_id,
        ]);
        $resInd->assertRedirect();
        $this->assertDatabaseHas('program_entries', [
            'program_id' => $this->indProgram->id,
            'group_id' => $this->group->id,
            'student_id' => $this->student1->id,
            'chest_number' => 'QF1001',
        ]);

        // 2. Register group program with student2 as leader
        $resGrp = $this->actingAs($this->leaderUser)->post(route('leader.registrations.store'), [
            'program_id' => $this->groupProgram->id,
            'zone' => $this->zone->name,
            'leader_id' => $this->student2->id,
            'student_id' => $this->student2->id,
            'student_ids' => [$this->student1->id, $this->student2->id],
        ]);
        $resGrp->assertRedirect();

        $groupEntry = ProgramEntry::where('program_id', $this->groupProgram->id)->first();
        $this->assertNotNull($groupEntry);
        $this->assertEquals($this->student2->id, $groupEntry->student_id);
        $this->assertEquals('QF1002', $groupEntry->chest_number);
        $this->assertEquals(2, $groupEntry->participants()->count());

        // Check leaderStudent() method
        $this->assertEquals($this->student2->id, $groupEntry->leaderStudent()?->id);
    }

    public function test_leader_can_edit_and_update_registration(): void
    {
        $entry = ProgramEntry::create([
            'program_id' => $this->indProgram->id,
            'group_id' => $this->group->id,
            'student_id' => $this->student1->id,
            'chest_number' => $this->student1->student_id,
            'status' => 'pending',
        ]);

        $editRes = $this->actingAs($this->leaderUser)->get(route('leader.registrations.edit', $entry));
        $editRes->assertStatus(200);
        $editRes->assertSee('Save Changes');

        $updateRes = $this->actingAs($this->leaderUser)->put(route('leader.registrations.update', $entry), [
            'student_id' => $this->student2->id,
            'chest_number' => $this->student2->student_id,
        ]);
        $updateRes->assertRedirect(route('leader.registrations'));

        $this->assertDatabaseHas('program_entries', [
            'id' => $entry->id,
            'student_id' => $this->student2->id,
            'chest_number' => 'QF1002',
        ]);
    }

    public function test_leader_can_delete_registration(): void
    {
        $entry = ProgramEntry::create([
            'program_id' => $this->indProgram->id,
            'group_id' => $this->group->id,
            'student_id' => $this->student1->id,
            'chest_number' => $this->student1->student_id,
            'status' => 'pending',
        ]);

        $delRes = $this->actingAs($this->leaderUser)->delete(route('leader.registrations.destroy', $entry));
        $delRes->assertRedirect(route('leader.registrations'));

        $this->assertDatabaseMissing('program_entries', ['id' => $entry->id]);
    }

    public function test_admin_can_verify_and_batch_verify_entries(): void
    {
        $entry1 = ProgramEntry::create([
            'program_id' => $this->indProgram->id,
            'group_id' => $this->group->id,
            'student_id' => $this->student1->id,
            'chest_number' => $this->student1->student_id,
            'status' => 'pending',
        ]);

        $entry2 = ProgramEntry::create([
            'program_id' => $this->groupProgram->id,
            'group_id' => $this->group->id,
            'student_id' => $this->student2->id,
            'chest_number' => $this->student2->student_id,
            'status' => 'pending',
        ]);

        // Admin verification page view
        $viewRes = $this->actingAs($this->adminUser)->get(route('admin.registrations.index'));
        $viewRes->assertStatus(200);
        $viewRes->assertSee('Verify Registrations');

        // Admin batch verify
        $batchRes = $this->actingAs($this->adminUser)->post(route('admin.registrations.verify-all'), [
            'entry_ids' => [$entry1->id, $entry2->id],
        ]);
        $batchRes->assertRedirect();

        $this->assertEquals('verified', $entry1->fresh()->status);
        $this->assertEquals('verified', $entry2->fresh()->status);
    }

    public function test_admin_can_toggle_registration_portal_open_close(): void
    {
        $this->assertEquals('1', FestivalSetting::get('registration_open'));

        $toggleRes = $this->actingAs($this->adminUser)->post(route('admin.settings.toggle-registration'));
        $toggleRes->assertRedirect();

        $this->assertEquals('0', FestivalSetting::get('registration_open'));

        // When closed, leader registration attempt fails
        $failRes = $this->actingAs($this->leaderUser)->post(route('leader.registrations.store'), [
            'program_id' => $this->indProgram->id,
            'zone' => $this->zone->name,
            'student_id' => $this->student1->id,
            'chest_number' => $this->student1->student_id,
        ]);
        $failRes->assertSessionHasErrors();
    }
}
