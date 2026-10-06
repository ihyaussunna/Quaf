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

class ProgramRegistrationControlTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $leaderUser;

    protected Group $group;

    protected Zone $zone;

    protected ProgramCategory $category;

    protected Program $stageProgram;

    protected Program $offStageProgram;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        FestivalSetting::set('registration_open', '1');
        FestivalSetting::set('stage_registration_open', '1');
        FestivalSetting::set('off_stage_registration_open', '1');

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
            'name' => 'Ruby Team',
            'code' => 'RUBY',
            'slug' => 'ruby-team',
            'color_hex' => '#dc2626',
            'leader_id' => $this->leaderUser->id,
        ]);

        $this->zone = Zone::firstOrCreate(
            ['slug' => 'senior-section'],
            ['name' => 'Senior Section', 'code' => 'SENIOR']
        );

        $this->category = ProgramCategory::firstOrCreate(
            ['slug' => 'general'],
            ['name' => 'General', 'code' => 'GEN']
        );

        $this->stageProgram = Program::create([
            'category_id' => $this->category->id,
            'name' => 'Elocution Malayalam',
            'slug' => 'elocution-malayalam',
            'code' => 'ELOC-MAL',
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'is_stage' => true,
            'is_registration_open' => true,
            'participant_count' => 1,
            'participant_limit' => 1,
            'is_active' => true,
        ]);

        $this->offStageProgram = Program::create([
            'category_id' => $this->category->id,
            'name' => 'Essay Writing Malayalam',
            'slug' => 'essay-writing-malayalam',
            'code' => 'ESSAY-MAL',
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'is_stage' => false,
            'is_registration_open' => true,
            'participant_count' => 1,
            'participant_limit' => 1,
            'is_active' => true,
        ]);

        $this->student = Student::create([
            'student_id' => 'STU101',
            'name' => 'Farhan P',
            'chest_number' => '101',
            'class_level' => '10',
            'qr_token' => 'qr-token-stu101',
            'group_id' => $this->group->id,
            'zone_id' => $this->zone->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_toggle_individual_program_registration(): void
    {
        $this->actingAs($this->adminUser);

        $this->assertTrue($this->stageProgram->fresh()->isRegistrationOpen());

        $response = $this->post(route('admin.programs.toggle-registration', $this->stageProgram));
        $response->assertRedirect();

        $this->assertFalse((bool) $this->stageProgram->fresh()->is_registration_open);
        $this->assertFalse($this->stageProgram->fresh()->isRegistrationOpen());

        $response = $this->post(route('admin.programs.toggle-registration', $this->stageProgram));
        $response->assertRedirect();

        $this->assertTrue((bool) $this->stageProgram->fresh()->is_registration_open);
        $this->assertTrue($this->stageProgram->fresh()->isRegistrationOpen());
    }

    public function test_admin_can_bulk_toggle_selected_programs(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('admin.programs.bulk-toggle-registration'), [
            'target' => 'selected',
            'action' => 'close',
            'program_ids' => [$this->stageProgram->id, $this->offStageProgram->id],
        ]);
        $response->assertRedirect();

        $this->assertFalse((bool) $this->stageProgram->fresh()->is_registration_open);
        $this->assertFalse((bool) $this->offStageProgram->fresh()->is_registration_open);

        $response = $this->post(route('admin.programs.bulk-toggle-registration'), [
            'target' => 'selected',
            'action' => 'open',
            'program_ids' => [$this->stageProgram->id],
        ]);
        $response->assertRedirect();

        $this->assertTrue((bool) $this->stageProgram->fresh()->is_registration_open);
        $this->assertFalse((bool) $this->offStageProgram->fresh()->is_registration_open);
    }

    public function test_admin_can_toggle_stage_and_off_stage_registration(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('admin.settings.toggle-stage-registration'), [
            'action' => 'close',
        ]);
        $response->assertRedirect();

        $this->assertEquals('0', FestivalSetting::get('stage_registration_open'));
        $this->assertFalse($this->stageProgram->fresh()->isRegistrationOpen());
        $this->assertTrue($this->offStageProgram->fresh()->isRegistrationOpen());

        $response = $this->post(route('admin.settings.toggle-off-stage-registration'), [
            'action' => 'close',
        ]);
        $response->assertRedirect();

        $this->assertEquals('0', FestivalSetting::get('off_stage_registration_open'));
        $this->assertFalse($this->offStageProgram->fresh()->isRegistrationOpen());

        $response = $this->post(route('admin.programs.bulk-toggle-registration'), [
            'target' => 'stage',
            'action' => 'open',
        ]);
        $response->assertRedirect();

        $this->assertEquals('1', FestivalSetting::get('stage_registration_open'));
        $this->assertTrue($this->stageProgram->fresh()->isRegistrationOpen());
    }

    public function test_leader_cannot_register_when_program_registration_is_closed(): void
    {
        $this->stageProgram->update(['is_registration_open' => false]);

        $this->actingAs($this->leaderUser);

        $response = $this->post(route('leader.registrations.store'), [
            'group_id' => $this->group->id,
            'program_id' => $this->stageProgram->id,
            'student_id' => $this->student->id,
        ]);

        $response->assertSessionHasErrors(['registration']);
        $this->assertDatabaseMissing('program_entries', [
            'program_id' => $this->stageProgram->id,
            'student_id' => $this->student->id,
        ]);
    }

    public function test_leader_can_register_for_open_program(): void
    {
        $this->actingAs($this->leaderUser);

        $response = $this->post(route('leader.registrations.store'), [
            'group_id' => $this->group->id,
            'program_id' => $this->offStageProgram->id,
            'student_id' => $this->student->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('program_entries', [
            'program_id' => $this->offStageProgram->id,
            'student_id' => $this->student->id,
        ]);
    }

    public function test_leader_cannot_delete_or_swap_entry_when_program_registration_is_closed(): void
    {
        $entry = ProgramEntry::create([
            'program_id' => $this->stageProgram->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'chest_number' => '101',
        ]);

        // Close stage registration
        $this->stageProgram->update(['is_registration_open' => false]);

        $this->actingAs($this->leaderUser);

        // Try deleting
        $response = $this->delete(route('leader.registrations.destroy', $entry));
        $response->assertSessionHasErrors(['registration']);
        $this->assertDatabaseHas('program_entries', ['id' => $entry->id]);

        // Try swapping to another program when from_program is closed
        $response = $this->post(route('leader.registrations.swap'), [
            'student_id' => $this->student->id,
            'from_entry_id' => $entry->id,
            'to_program_id' => $this->offStageProgram->id,
        ]);

        $response->assertSessionHasErrors(['registration']);
    }
}
