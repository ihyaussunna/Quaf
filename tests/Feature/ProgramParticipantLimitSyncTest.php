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

class ProgramParticipantLimitSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $leaderUser;

    protected Group $group;

    protected Zone $zoneA;

    protected ProgramCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        FestivalSetting::set('registration_open', '1');
        FestivalSetting::set('student_editing_open', '1');

        $this->adminUser = User::create([
            'name' => 'Admin User',
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
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#56286b',
            'leader_id' => $this->leaderUser->id,
            'manager_name' => 'LUMO MANAGER',
        ]);

        $this->zoneA = Zone::firstOrCreate(
            ['code' => 'A_ZONE'],
            ['name' => 'A Zone', 'sort_order' => 1]
        );

        $this->category = ProgramCategory::create([
            'name' => 'General',
            'slug' => 'general',
        ]);
    }

    public function test_admin_updating_program_participant_limit_syncs_to_leader_and_eligibility(): void
    {
        // 1. Create an individual program with limit = 1
        $program = Program::create([
            'name' => 'Theology Test',
            'code' => 'TH-01',
            'type' => 'individual',
            'zone_id' => $this->zoneA->id,
            'category_id' => $this->category->id,
            'participant_count' => 1,
            'status' => 'upcoming',
            'points_weight' => 1.0,
        ]);

        $this->assertEquals(1, $program->limit);
        $this->assertEquals(1, $program->participant_count);
        $this->assertEquals(1, $program->max_participants_per_group);

        // 2. Register first student
        $student1 = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => 'QF1001',
            'name' => 'Student One',
            'chest_number' => 'QF1001',
            'qr_token' => 'qr-test-1',
            'is_active' => true,
        ]);

        $student2 = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => 'QF1002',
            'name' => 'Student Two',
            'chest_number' => 'QF1002',
            'qr_token' => 'qr-test-2',
            'is_active' => true,
        ]);

        $this->actingAs($this->leaderUser)
            ->post(route('leader.registrations.store'), [
                'program_id' => $program->id,
                'student_id' => $student1->id,
            ])
            ->assertRedirect();

        // 3. Second student registration should fail when limit is 1
        $this->actingAs($this->leaderUser)
            ->post(route('leader.registrations.store'), [
                'program_id' => $program->id,
                'student_id' => $student2->id,
            ])
            ->assertSessionHasErrors();

        // 4. Admin updates program limit from 1 to 2
        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.programs.update', $program), [
                'name' => 'Theology Test',
                'code' => 'TH-01',
                'type' => 'individual',
                'zone_id' => $this->zoneA->id,
                'category_id' => $this->category->id,
                'participant_count' => 2,
                'points_weight' => 1.0,
                'status' => 'upcoming',
            ]);

        $response->assertRedirect(route('admin.programs.index'));

        // Verify program model fields are synced
        $program->refresh();
        $this->assertEquals(2, $program->participant_count);
        $this->assertEquals(2, $program->max_participants_per_group);
        $this->assertEquals(10, $program->max_participants);
        $this->assertEquals(2, $program->limit);

        // 5. Leader registration page shows [1/2 • 1 left] instead of [Full]
        $leaderPage = $this->actingAs($this->leaderUser)
            ->get(route('leader.registrations'));
        $leaderPage->assertOk();
        $leaderPage->assertSee('[1/2 • 1 left]');

        $response = $this->actingAs($this->leaderUser)
            ->post(route('leader.registrations.store'), [
                'program_id' => $program->id,
                'student_id' => $student2->id,
            ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertEquals(2, ProgramEntry::where('program_id', $program->id)->count());

        // 7. Third student is now blocked
        $student3 = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => 'QF1003',
            'name' => 'Student Three',
            'chest_number' => 'QF1003',
            'qr_token' => 'qr-test-3',
            'is_active' => true,
        ]);

        $this->actingAs($this->leaderUser)
            ->post(route('leader.registrations.store'), [
                'program_id' => $program->id,
                'student_id' => $student3->id,
            ])
            ->assertSessionHasErrors();
    }

    public function test_group_program_limit_sync(): void
    {
        $program = Program::create([
            'name' => 'Qawwali',
            'code' => 'QW-01',
            'type' => 'group',
            'zone_id' => $this->zoneA->id,
            'category_id' => $this->category->id,
            'participant_count' => 5,
            'status' => 'upcoming',
            'points_weight' => 2.0,
        ]);

        $this->assertEquals(5, $program->limit);
        $this->assertEquals(5, $program->participant_count);
        $this->assertEquals(5, $program->max_participants);
        $this->assertEquals(1, $program->max_participants_per_group);

        // Admin updates group limit to 7
        $this->actingAs($this->adminUser)
            ->put(route('admin.programs.update', $program), [
                'name' => 'Qawwali',
                'code' => 'QW-01',
                'type' => 'group',
                'zone_id' => $this->zoneA->id,
                'category_id' => $this->category->id,
                'participant_count' => 7,
                'points_weight' => 2.0,
                'status' => 'upcoming',
            ])
            ->assertRedirect();

        $program->refresh();
        $this->assertEquals(7, $program->participant_count);
        $this->assertEquals(7, $program->max_participants);
        $this->assertEquals(1, $program->max_participants_per_group);
        $this->assertEquals(7, $program->limit);
    }

    public function test_program_model_saving_hook_auto_syncs_limits(): void
    {
        $program = Program::create([
            'name' => 'Speech English',
            'code' => 'SP-01',
            'type' => 'individual',
            'zone_id' => $this->zoneA->id,
            'category_id' => $this->category->id,
            'participant_count' => 1,
            'status' => 'upcoming',
            'points_weight' => 1.0,
        ]);

        $this->assertEquals(1, $program->limit);
        $this->assertEquals(1, $program->max_participants_per_group);
        $this->assertEquals(5, $program->max_participants);

        // Update participant count directly
        $program->update(['participant_count' => 3]);
        $program->refresh();

        $this->assertEquals(3, $program->participant_count);
        $this->assertEquals(3, $program->max_participants_per_group);
        $this->assertEquals(15, $program->max_participants);
        $this->assertEquals(3, $program->limit);
    }
}
