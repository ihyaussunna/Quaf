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

class AdminGroupEntryStatsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Group $groupA;

    protected Group $groupB;

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

        $this->groupA = Group::create([
            'name' => 'Pacto Hikmic',
            'code' => 'PACTO',
            'slug' => 'pacto-hikmic',
            'color_hex' => '#005c94',
            'manager_name' => 'PACTO MANAGER',
        ]);

        $this->groupB = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#56286b',
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

    public function test_admin_registrations_page_shows_group_entry_stats_section(): void
    {
        // Create 3 programs in Zone A
        // 1. Program 1: Limit = 2. Pacto will register 2 (Full), Lumo will register 1 (Partial)
        $progFull = Program::create([
            'name' => 'English Poem Making',
            'code' => 'Q9-166',
            'type' => 'individual',
            'zone_id' => $this->zoneA->id,
            'category_id' => $this->category->id,
            'participant_count' => 2,
            'status' => 'upcoming',
            'points_weight' => 1.0,
        ]);

        // 2. Program 2: Limit = 2. Pacto will register 1 (Partial), Lumo will register 0 (Pending)
        $progPartial = Program::create([
            'name' => 'Book Test',
            'code' => 'Q9-172',
            'type' => 'individual',
            'zone_id' => $this->zoneA->id,
            'category_id' => $this->category->id,
            'participant_count' => 2,
            'status' => 'upcoming',
            'points_weight' => 1.0,
        ]);

        // 3. Program 3: Limit = 1. Pacto and Lumo have 0 (Pending for both)
        $progPending = Program::create([
            'name' => 'Arabic Speech',
            'code' => 'Q9-160',
            'type' => 'individual',
            'zone_id' => $this->zoneA->id,
            'category_id' => $this->category->id,
            'participant_count' => 1,
            'status' => 'upcoming',
            'points_weight' => 1.0,
        ]);

        // Students for Pacto
        $pactoStudent1 = Student::create([
            'group_id' => $this->groupA->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => 'QF2223',
            'name' => 'Pacto Student 1',
            'chest_number' => 'QF2223',
            'qr_token' => 'qr-pacto-1',
            'is_active' => true,
        ]);

        $pactoStudent2 = Student::create([
            'group_id' => $this->groupA->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => 'QF2220',
            'name' => 'Pacto Student 2',
            'chest_number' => 'QF2220',
            'qr_token' => 'qr-pacto-2',
            'is_active' => true,
        ]);

        $pactoStudent3 = Student::create([
            'group_id' => $this->groupA->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => 'QF2209',
            'name' => 'Pacto Student 3',
            'chest_number' => 'QF2209',
            'qr_token' => 'qr-pacto-3',
            'is_active' => true,
        ]);

        // Students for Lumo
        $lumoStudent1 = Student::create([
            'group_id' => $this->groupB->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => 'QF1001',
            'name' => 'Lumo Student 1',
            'chest_number' => 'QF1001',
            'qr_token' => 'qr-lumo-1',
            'is_active' => true,
        ]);

        // Entries:
        // Pacto in progFull (2 entries -> Full)
        ProgramEntry::create([
            'program_id' => $progFull->id,
            'group_id' => $this->groupA->id,
            'student_id' => $pactoStudent1->id,
            'chest_number' => $pactoStudent1->chest_number,
            'status' => 'verified',
        ]);
        ProgramEntry::create([
            'program_id' => $progFull->id,
            'group_id' => $this->groupA->id,
            'student_id' => $pactoStudent2->id,
            'chest_number' => $pactoStudent2->chest_number,
            'status' => 'verified',
        ]);

        // Pacto in progPartial (1 entry -> Partial)
        ProgramEntry::create([
            'program_id' => $progPartial->id,
            'group_id' => $this->groupA->id,
            'student_id' => $pactoStudent3->id,
            'chest_number' => $pactoStudent3->chest_number,
            'status' => 'pending',
        ]);

        // Lumo in progFull (1 entry -> Partial)
        ProgramEntry::create([
            'program_id' => $progFull->id,
            'group_id' => $this->groupB->id,
            'student_id' => $lumoStudent1->id,
            'chest_number' => $lumoStudent1->chest_number,
            'status' => 'verified',
        ]);

        // Visit admin registrations index
        $response = $this->actingAs($this->adminUser)->get(route('admin.registrations.index'));
        $response->assertOk();
        $response->assertSee('Group Entry Statistics');
        $response->assertSee('Pacto Hikmic');
        $response->assertSee('Lumo Fikric');
        $response->assertSee('Registered');
        $response->assertSee('Partial');
        $response->assertSee('Pending');

        // Check stats data passed to view
        $statsData = $response->viewData('statsData');
        $this->assertNotNull($statsData);
        $this->assertEquals(3, $statsData['total_programs']);

        // Check Pacto stats
        $pactoStat = collect($statsData['groups'])->firstWhere('group.id', $this->groupA->id);
        $this->assertEquals(1, $pactoStat['full_count']); // English Poem Making
        $this->assertEquals(1, $pactoStat['partial_count']); // Book Test
        $this->assertEquals(1, $pactoStat['pending_count']); // Arabic Speech
        $this->assertEquals(3, $pactoStat['total_entries']);
        $this->assertEquals(2, $pactoStat['verified_entries']);
        $this->assertEquals(1, $pactoStat['pending_verif_entries']);

        // Check Lumo stats
        $lumoStat = collect($statsData['groups'])->firstWhere('group.id', $this->groupB->id);
        $this->assertEquals(0, $lumoStat['full_count']);
        $this->assertEquals(1, $lumoStat['partial_count']); // English Poem Making
        $this->assertEquals(2, $lumoStat['pending_count']); // Book Test & Arabic Speech
        $this->assertEquals(1, $lumoStat['total_entries']);
    }

    public function test_admin_registrations_stats_matrix_page(): void
    {
        $program = Program::create([
            'name' => 'Debate Arabic',
            'code' => 'Q9-150',
            'type' => 'individual',
            'zone_id' => $this->zoneA->id,
            'category_id' => $this->category->id,
            'participant_count' => 2,
            'status' => 'upcoming',
            'points_weight' => 1.0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.registrations.stats'));
        $response->assertOk();
        $response->assertSee('Group Entry Statistics');
        $response->assertSee('Debate Arabic');
        $response->assertSee('Q9-150');
        $response->assertSee('Full Quotas Met');
        $response->assertSee('Partial Registrations');
        $response->assertSee('Pending / Unregistered');
    }
}
