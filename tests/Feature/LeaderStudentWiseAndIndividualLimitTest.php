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
use App\Services\EligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LeaderStudentWiseAndIndividualLimitTest extends TestCase
{
    use RefreshDatabase;

    protected User $leaderUser;

    protected Group $group;

    protected Zone $zoneA;

    protected Zone $mixZone;

    protected ProgramCategory $category;

    protected EligibilityService $eligibilityService;

    protected function setUp(): void
    {
        parent::setUp();

        FestivalSetting::set('registration_open', '1');
        FestivalSetting::set('student_editing_open', '1');

        $this->eligibilityService = app(EligibilityService::class);

        $this->leaderUser = User::create([
            'name' => 'Leader User',
            'email' => 'leader_test@quaf.fest',
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
            'manager_name' => 'MANAGER ONE',
        ]);

        $this->zoneA = Zone::firstOrCreate(
            ['slug' => 'a-zone'],
            ['name' => 'A Zone', 'code' => 'ZONE-A', 'display_order' => 1]
        );

        $this->mixZone = Zone::firstOrCreate(
            ['slug' => 'mix-zone'],
            ['name' => 'Mix Zone', 'code' => 'MIX_ZONE', 'display_order' => 4]
        );

        $this->category = ProgramCategory::firstOrCreate(
            ['slug' => 'stage'],
            ['name' => 'Stage', 'code' => 'STG']
        );
    }

    public function test_student_wise_page_renders_successfully_for_leader(): void
    {
        $student = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => 'QF1001',
            'name' => 'MUHAMMED ALI',
            'class_level' => '10',
            'category' => 'A Zone',
            'qr_token' => 'qr-token-test-1',
        ]);

        $response = $this->actingAs($this->leaderUser)
            ->get(route('leader.students-wise'));

        $response->assertOk();
        $response->assertViewIs('leader.student-wise');
        $response->assertViewHas('isEditingOpen', true);
        $response->assertSee('MUHAMMED ALI');
    }

    public function test_student_can_participate_in_5_individual_programs_across_own_zone_and_mix_zone(): void
    {
        $student = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => 'QF1002',
            'name' => 'AHMED BILAL',
            'class_level' => '10',
            'category' => 'A Zone',
            'qr_token' => 'qr-token-test-2',
        ]);

        // 3 individual competitions in Zone A
        for ($i = 1; $i <= 3; $i++) {
            $prog = Program::create([
                'category_id' => $this->category->id,
                'code' => "IND-A-{$i}",
                'name' => "Zone A Event {$i}",
                'type' => 'individual',
                'zone_id' => $this->zoneA->id,
                'eligibility' => 'A Zone',
                'max_participants_per_group' => 2,
                'individual_limit_counted' => true,
            ]);

            $entry = $this->eligibilityService->registerIndividual($student, $prog, [
                'status' => 'verified',
            ]);
            $this->assertInstanceOf(ProgramEntry::class, $entry);
        }

        // 2 individual competitions in Mix Zone
        for ($i = 1; $i <= 2; $i++) {
            $mixProg = Program::create([
                'category_id' => $this->category->id,
                'code' => "IND-MIX-{$i}",
                'name' => "Mix Zone Event {$i}",
                'type' => 'individual',
                'zone_id' => $this->mixZone->id,
                'eligibility' => 'Mix Zone',
                'mix_zone_open_to_all' => true,
                'max_participants_per_group' => 2,
                'individual_limit_counted' => true,
            ]);

            $entry = $this->eligibilityService->registerIndividual($student, $mixProg, [
                'status' => 'verified',
            ]);
            $this->assertInstanceOf(ProgramEntry::class, $entry);
        }

        $this->assertEquals(5, $student->fresh()->getIndividualParticipationCount());
        $this->assertTrue($student->fresh()->hasReachedIndividualLimit());
        $this->assertEquals(0, $student->fresh()->getRemainingIndividualSlots());
    }

    public function test_student_cannot_register_for_6th_individual_program(): void
    {
        $student = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => 'QF1003',
            'name' => 'HAMZA USMAN',
            'class_level' => '10',
            'category' => 'A Zone',
            'qr_token' => 'qr-token-test-3',
        ]);

        // Fill 5 slots
        for ($i = 1; $i <= 5; $i++) {
            $prog = Program::create([
                'category_id' => $this->category->id,
                'code' => "FILL-{$i}",
                'name' => "Program {$i}",
                'type' => 'individual',
                'zone_id' => $this->zoneA->id,
                'eligibility' => 'A Zone',
                'max_participants_per_group' => 2,
                'individual_limit_counted' => true,
            ]);

            $this->eligibilityService->registerIndividual($student, $prog, ['status' => 'verified']);
        }

        // 6th program
        $sixthProg = Program::create([
            'category_id' => $this->category->id,
            'code' => 'SIXTH-IND',
            'name' => 'Sixth Program',
            'type' => 'individual',
            'zone_id' => $this->zoneA->id,
            'eligibility' => 'A Zone',
            'max_participants_per_group' => 2,
            'individual_limit_counted' => true,
        ]);

        // Attempting via controller
        $response = $this->actingAs($this->leaderUser)
            ->postJson(route('leader.registrations.store'), [
                'program_id' => $sixthProg->id,
                'student_ids' => [$student->id],
            ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_student_with_5_individual_programs_can_still_participate_in_group_programs(): void
    {
        $student = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => 'QF1004',
            'name' => 'SALMAN FARISI',
            'class_level' => '10',
            'category' => 'A Zone',
            'qr_token' => 'qr-token-test-4',
        ]);

        $otherStudent = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => 'QF1005',
            'name' => 'ANAS MALIK',
            'class_level' => '10',
            'category' => 'A Zone',
            'qr_token' => 'qr-token-test-5',
        ]);

        // Register 5 individual programs
        for ($i = 1; $i <= 5; $i++) {
            $prog = Program::create([
                'category_id' => $this->category->id,
                'code' => "IND-5-{$i}",
                'name' => "Ind Program {$i}",
                'type' => 'individual',
                'zone_id' => $this->zoneA->id,
                'eligibility' => 'A Zone',
                'max_participants_per_group' => 2,
                'individual_limit_counted' => true,
            ]);

            $this->eligibilityService->registerIndividual($student, $prog, ['status' => 'verified']);
        }

        $this->assertEquals(5, $student->fresh()->getIndividualParticipationCount());

        // Now register group program with student
        $groupProg = Program::create([
            'category_id' => $this->category->id,
            'code' => 'GRP-01',
            'name' => 'Qawwali Group',
            'type' => 'group',
            'zone_id' => $this->zoneA->id,
            'eligibility' => 'A Zone',
            'participant_count' => 2,
            'max_participants' => 2,
            'max_participants_per_group' => 1,
            'individual_limit_counted' => true, // Even if set to true, group type must not count
        ]);

        $response = $this->actingAs($this->leaderUser)
            ->postJson(route('leader.registrations.group.store'), [
                'program_id' => $groupProg->id,
                'student_ids' => [$student->id, $otherStudent->id],
                'leader_id' => $student->id,
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // Student's individual count must remain 5, not 6
        $this->assertEquals(5, $student->fresh()->getIndividualParticipationCount());
    }

    public function test_group_programs_do_not_consume_individual_participation_quota(): void
    {
        $student = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $this->zoneA->id,
            'student_id' => 'QF1006',
            'name' => 'ZUBAIR AWWAM',
            'class_level' => '10',
            'category' => 'A Zone',
            'qr_token' => 'qr-token-test-6',
        ]);

        // Register in 3 group programs
        for ($i = 1; $i <= 3; $i++) {
            $groupProg = Program::create([
                'category_id' => $this->category->id,
                'code' => "GRP-X-{$i}",
                'name' => "Group Event {$i}",
                'type' => 'group',
                'zone_id' => $this->zoneA->id,
                'eligibility' => 'A Zone',
                'participant_count' => 1,
                'max_participants' => 1,
                'max_participants_per_group' => 1,
            ]);

            $this->eligibilityService->registerGroup($this->group, $groupProg, [$student->id], [
                'status' => 'verified',
            ]);
        }

        // Student still has 0 individual programs consumed and 5 remaining individual slots
        $this->assertEquals(0, $student->fresh()->getIndividualParticipationCount());
        $this->assertEquals(5, $student->fresh()->getRemainingIndividualSlots());
        $this->assertFalse($student->fresh()->hasReachedIndividualLimit());
    }
}
