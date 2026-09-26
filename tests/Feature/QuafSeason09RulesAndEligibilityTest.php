<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Judge;
use App\Models\PointSetting;
use App\Models\PointsTransaction;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\ScoreSheet;
use App\Models\Student;
use App\Models\Zone;
use App\Services\EligibilityService;
use App\Services\PointCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuafSeason09RulesAndEligibilityTest extends TestCase
{
    use RefreshDatabase;

    protected Zone $zoneA;

    protected Zone $zoneB;

    protected Zone $zoneC;

    protected Zone $zoneMix;

    protected Group $groupPacto;

    protected Group $groupYugo;

    protected ProgramCategory $category;

    protected EligibilityService $eligibilityService;

    protected PointCalculationService $pointService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eligibilityService = app(EligibilityService::class);
        $this->pointService = app(PointCalculationService::class);

        // Ensure default point settings
        PointSetting::firstOrCreate([], [
            'first_place_points' => 5,
            'second_place_points' => 3,
            'third_place_points' => 1,
            'participation_points' => 0,
            'group_multiplier' => 1.0,
        ]);

        // Setup 4 Master Zones
        $this->zoneA = Zone::firstOrCreate(['code' => Zone::A_ZONE], [
            'name' => 'A Zone',
            'display_order' => 1,
            'classes' => 'Class 1-4',
            'color_hex' => '#2e3192',
        ]);

        $this->zoneB = Zone::firstOrCreate(['code' => Zone::B_ZONE], [
            'name' => 'B Zone',
            'display_order' => 2,
            'classes' => 'Class 5-7',
            'color_hex' => '#ad1e56',
        ]);

        $this->zoneC = Zone::firstOrCreate(['code' => Zone::C_ZONE], [
            'name' => 'C Zone',
            'display_order' => 3,
            'classes' => 'Class 8-10',
            'color_hex' => '#f8e709',
        ]);

        $this->zoneMix = Zone::firstOrCreate(['code' => Zone::MIX_ZONE], [
            'name' => 'Mix Zone',
            'display_order' => 4,
            'classes' => 'General / Combined',
            'color_hex' => '#56286b',
        ]);

        // Setup Groups
        $this->groupPacto = Group::firstOrCreate(['code' => Group::PACTO], [
            'name' => 'Pacto Hikmic',
            'color_hex' => '#2e3192',
            'slug' => 'pacto-hikmic',
        ]);

        $this->groupYugo = Group::firstOrCreate(['code' => Group::YUGO], [
            'name' => 'Yugo Rushdic',
            'color_hex' => '#ad1e56',
            'slug' => 'yugo-rushdic',
        ]);

        $this->category = ProgramCategory::firstOrCreate(['slug' => 'quran'], [
            'name' => 'Quran & Hadith',
        ]);
    }

    protected function createStudent(Group $group, Zone $zone, string $name, string $chest): Student
    {
        return Student::create([
            'student_id' => $chest,
            'name' => $name,
            'group_id' => $group->id,
            'zone_id' => $zone->id,
            'category' => $zone->name,
            'gender' => 'Male',
            'class_level' => $zone->classes,
            'qr_token' => Str::random(32),
        ]);
    }

    protected function createProgram(string $name, Zone $zone, string $type = 'individual', array $extra = []): Program
    {
        return Program::create(array_merge([
            'name' => $name,
            'code' => 'PRG-'.Str::upper(Str::random(5)),
            'zone_id' => $zone->id,
            'category_id' => $this->category->id,
            'eligibility' => $zone->name,
            'type' => $type,
            'status' => 'upcoming',
            'individual_limit_counted' => ($type === 'individual'),
            'mix_zone_open_to_all' => ($zone->code === Zone::MIX_ZONE),
            'max_participants_per_group' => 2,
        ], $extra));
    }

    /**
     * Test 1: Individual student can register for maximum 5 programs; 6th is blocked.
     */
    public function test_01_individual_student_cannot_exceed_five_programs(): void
    {
        $student = $this->createStudent($this->groupPacto, $this->zoneA, 'Zayd Fayiz', 'QF1001');

        // Register 5 individual programmes in A Zone
        for ($i = 1; $i <= 5; $i++) {
            $program = $this->createProgram("A Zone Competition {$i}", $this->zoneA, 'individual');
            $regResult = $this->eligibilityService->registerIndividual($student, $program, [
                'chest_number' => "QF1001-{$i}",
                'status' => 'confirmed',
            ]);
            $this->assertInstanceOf(ProgramEntry::class, $regResult);
        }

        $this->assertEquals(5, $student->getIndividualParticipationCount());
        $this->assertTrue($student->hasReachedIndividualLimit());
        $this->assertEquals(0, $student->getRemainingIndividualSlots());

        // Attempt 6th individual programme
        $sixthProgram = $this->createProgram('A Zone Competition 6', $this->zoneA, 'individual');
        $check = $this->eligibilityService->validateIndividualRegistration($student, $sixthProgram);

        $this->assertFalse($check['valid']);
        $this->assertEquals('individual_limit', $check['field']);
    }

    /**
     * Test 2: Student with 3 own-zone + 2 Mix-zone = 5 cannot register 6th.
     */
    public function test_02_student_with_three_own_zone_and_two_mix_zone_cannot_register_sixth(): void
    {
        $student = $this->createStudent($this->groupPacto, $this->zoneA, 'Umar Farooq', 'QF1002');

        // 3 programmes in A Zone
        for ($i = 1; $i <= 3; $i++) {
            $program = $this->createProgram("A Zone Event {$i}", $this->zoneA, 'individual');
            $this->eligibilityService->registerIndividual($student, $program, [
                'chest_number' => "QF1002-A{$i}",
                'status' => 'confirmed',
            ]);
        }

        // 2 programmes in Mix Zone
        for ($i = 1; $i <= 2; $i++) {
            $mixProgram = $this->createProgram("Mix Zone Event {$i}", $this->zoneMix, 'individual', [
                'mix_zone_open_to_all' => true,
            ]);
            $this->eligibilityService->registerIndividual($student, $mixProgram, [
                'chest_number' => "QF1002-M{$i}",
                'status' => 'confirmed',
            ]);
        }

        $this->assertEquals(5, $student->getIndividualParticipationCount());

        // Attempt 6th in own zone
        $sixthProgram = $this->createProgram('A Zone Event 4', $this->zoneA, 'individual');
        $checkA = $this->eligibilityService->validateIndividualRegistration($student, $sixthProgram);
        $this->assertFalse($checkA['valid']);
        $this->assertEquals('individual_limit', $checkA['field']);

        // Attempt 6th in Mix zone
        $sixthMixProgram = $this->createProgram('Mix Zone Event 3', $this->zoneMix, 'individual', ['mix_zone_open_to_all' => true]);
        $checkMix = $this->eligibilityService->validateIndividualRegistration($student, $sixthMixProgram);
        $this->assertFalse($checkMix['valid']);
        $this->assertEquals('individual_limit', $checkMix['field']);
    }

    /**
     * Test 3: Student with 5 Mix Zone individual programs cannot register 6th.
     */
    public function test_03_student_with_five_mix_zone_individual_programs_cannot_register_sixth(): void
    {
        $student = $this->createStudent($this->groupPacto, $this->zoneB, 'Bilal Ibn Rabah', 'QF1003');

        // 5 programmes in Mix Zone
        for ($i = 1; $i <= 5; $i++) {
            $mixProg = $this->createProgram("Mix Special {$i}", $this->zoneMix, 'individual', [
                'mix_zone_open_to_all' => true,
            ]);
            $this->eligibilityService->registerIndividual($student, $mixProg, [
                'chest_number' => "QF1003-MX{$i}",
                'status' => 'confirmed',
            ]);
        }

        $this->assertEquals(5, $student->getIndividualParticipationCount());

        // Attempt 6th programme
        $sixthProg = $this->createProgram('B Zone Native Event', $this->zoneB, 'individual');
        $check = $this->eligibilityService->validateIndividualRegistration($student, $sixthProg);

        $this->assertFalse($check['valid']);
        $this->assertEquals('individual_limit', $check['field']);
    }

    /**
     * Test 4: Student from A Zone can register for open Mix Zone program.
     */
    public function test_04_student_from_a_zone_can_register_for_open_mix_zone_program(): void
    {
        $student = $this->createStudent($this->groupPacto, $this->zoneA, 'Ali Murtaza', 'QF1004');
        $mixProg = $this->createProgram('Open Calligraphy Mix', $this->zoneMix, 'individual', [
            'mix_zone_open_to_all' => true,
        ]);

        $check = $this->eligibilityService->validateIndividualRegistration($student, $mixProg);
        $this->assertTrue($check['valid']);
    }

    /**
     * Test 5: Student from B Zone cannot register for restricted Mix Zone program.
     */
    public function test_05_student_from_b_zone_cannot_register_for_restricted_mix_zone_program(): void
    {
        $student = $this->createStudent($this->groupPacto, $this->zoneB, 'Hamza Lion', 'QF1005');
        $mixProg = $this->createProgram('A-Zone Exclusive Mix Workshop', $this->zoneMix, 'individual', [
            'mix_zone_open_to_all' => false,
            'eligibility_rules' => [
                'allowed_zones' => ['a-zone', 'A Zone'],
            ],
        ]);

        $check = $this->eligibilityService->validateIndividualRegistration($student, $mixProg);
        $this->assertFalse($check['valid']);
        $this->assertEquals('zone', $check['field']);
    }

    /**
     * Test 6: Group quota exceeded blocks 5th student from same group.
     */
    public function test_06_group_quota_exceeded_blocks_further_registrations_for_same_group(): void
    {
        $prog = $this->createProgram('Quran Hifz A', $this->zoneA, 'individual', [
            'max_participants_per_group' => 4,
        ]);

        // Register 4 students from Pacto
        for ($i = 1; $i <= 4; $i++) {
            $student = $this->createStudent($this->groupPacto, $this->zoneA, "Pacto Candidate {$i}", "PAC-{$i}");
            $reg = $this->eligibilityService->registerIndividual($student, $prog, [
                'chest_number' => "PAC-CH-{$i}",
                'status' => 'confirmed',
            ]);
            $this->assertInstanceOf(ProgramEntry::class, $reg);
        }

        // 5th student from Pacto tries to register
        $student5 = $this->createStudent($this->groupPacto, $this->zoneA, 'Pacto Candidate 5', 'PAC-5');
        $check = $this->eligibilityService->validateIndividualRegistration($student5, $prog);

        $this->assertFalse($check['valid']);
        $this->assertEquals('max_participants_per_group', $check['field']);
    }

    /**
     * Test 7: Yugo attempts same programme after Pacto quota is full -> Allowed.
     */
    public function test_07_different_group_can_still_register_when_another_group_quota_is_full(): void
    {
        $prog = $this->createProgram('Tajweed A', $this->zoneA, 'individual', [
            'max_participants_per_group' => 4,
        ]);

        // Fill Pacto quota with 4 students
        for ($i = 1; $i <= 4; $i++) {
            $student = $this->createStudent($this->groupPacto, $this->zoneA, "Pacto Candidate {$i}", "PAC-TJ-{$i}");
            $this->eligibilityService->registerIndividual($student, $prog, [
                'chest_number' => "PAC-TJ-CH-{$i}",
                'status' => 'confirmed',
            ]);
        }

        // Student from Yugo attempts registration
        $yugoStudent = $this->createStudent($this->groupYugo, $this->zoneA, 'Yugo Candidate 1', 'YUG-01');
        $check = $this->eligibilityService->validateIndividualRegistration($yugoStudent, $prog);

        $this->assertTrue($check['valid']);
    }

    /**
     * Test 8: Duplicate individual registration is blocked.
     */
    public function test_08_duplicate_individual_registration_is_blocked(): void
    {
        $student = $this->createStudent($this->groupPacto, $this->zoneA, 'Hassan Ali', 'QF1008');
        $prog = $this->createProgram('Malayalam Elocution A', $this->zoneA, 'individual');

        // First registration
        $this->eligibilityService->registerIndividual($student, $prog, [
            'chest_number' => 'QF1008',
            'status' => 'confirmed',
        ]);

        // Second registration attempt
        $check = $this->eligibilityService->validateIndividualRegistration($student, $prog);
        $this->assertFalse($check['valid']);
        $this->assertEquals('student_id', $check['field']);
    }

    /**
     * Test 9: Cancelled registration releases quota slot.
     */
    public function test_09_cancelled_registration_releases_quota_slot(): void
    {
        $student = $this->createStudent($this->groupPacto, $this->zoneA, 'Salman Farsi', 'QF1009');

        $entries = [];
        for ($i = 1; $i <= 5; $i++) {
            $prog = $this->createProgram("Contest {$i}", $this->zoneA, 'individual');
            $entries[] = $this->eligibilityService->registerIndividual($student, $prog, [
                'chest_number' => "QF1009-{$i}",
                'status' => 'confirmed',
            ]);
        }

        $sixthProg = $this->createProgram('Contest 6', $this->zoneA, 'individual');
        $checkBlocked = $this->eligibilityService->validateIndividualRegistration($student, $sixthProg);
        $this->assertFalse($checkBlocked['valid']);

        // Cancel one of the active entries
        $entries[0]->update(['status' => 'cancelled']);

        // Check if 6th programme is now allowed
        $checkAllowed = $this->eligibilityService->validateIndividualRegistration($student, $sixthProg);
        $this->assertTrue($checkAllowed['valid']);
    }

    /**
     * Test 10: Group programme verifies participant count and does not consume individual limit.
     */
    public function test_10_group_program_validates_participant_count_and_does_not_consume_individual_limit(): void
    {
        $groupProg = $this->createProgram('Nasheed Group', $this->zoneA, 'group', [
            'max_participants' => 4,
            'individual_limit_counted' => false,
        ]);

        // Create 5 students
        $students = [];
        for ($i = 1; $i <= 5; $i++) {
            $students[] = $this->createStudent($this->groupPacto, $this->zoneA, "Choir Member {$i}", "CH-{$i}");
        }

        // Test over limit (5 students provided for max 4)
        $checkOver = $this->eligibilityService->validateGroupRegistration($this->groupPacto, $groupProg, collect($students)->pluck('id')->all());
        $this->assertFalse($checkOver['valid']);
        $this->assertEquals('participant_count', $checkOver['field']);

        // Test valid count (4 students provided)
        $validStudentIds = collect($students)->take(4)->pluck('id')->all();
        $checkValid = $this->eligibilityService->validateGroupRegistration($this->groupPacto, $groupProg, $validStudentIds);
        $this->assertTrue($checkValid['valid']);

        // Perform registration
        $groupEntry = $this->eligibilityService->registerGroup($this->groupPacto, $groupProg, $validStudentIds, [
            'status' => 'confirmed',
        ]);
        $this->assertInstanceOf(ProgramEntry::class, $groupEntry);
        $this->assertEquals(4, $groupEntry->participants()->count());

        // Verify participating students still have 0 individual programs consumed
        $firstMember = $students[0]->fresh();
        $this->assertEquals(0, $firstMember->getIndividualParticipationCount());
        $this->assertEquals(5, $firstMember->getRemainingIndividualSlots());
    }

    /**
     * Test 11: Transactional safety and concurrency locking.
     */
    public function test_11_transactional_safety_and_locking_prevents_individual_limit_overflow(): void
    {
        $student = $this->createStudent($this->groupPacto, $this->zoneA, 'Khalid Waleed', 'QF1011');

        DB::transaction(function () use ($student) {
            // Register 5 programmes inside transaction
            for ($i = 1; $i <= 5; $i++) {
                $prog = $this->createProgram("Tx Prog {$i}", $this->zoneA, 'individual');
                $this->eligibilityService->registerIndividual($student, $prog, [
                    'chest_number' => "QF1011-{$i}",
                    'status' => 'confirmed',
                ]);
            }
        });

        $this->assertEquals(5, $student->fresh()->getIndividualParticipationCount());

        $extraProg = $this->createProgram('Tx Prog 6', $this->zoneA, 'individual');
        $check = $this->eligibilityService->validateIndividualRegistration($student, $extraProg);
        $this->assertFalse($check['valid']);
    }

    /**
     * Test 12: Published result generates auditable points transactions and updates group totals.
     */
    public function test_12_published_result_generates_points_transactions_and_updates_group_totals(): void
    {
        $student1 = $this->createStudent($this->groupPacto, $this->zoneA, 'Tariq Ziyad', 'QF1012');
        $student2 = $this->createStudent($this->groupYugo, $this->zoneA, 'Saad Waqqas', 'QF1013');

        $prog = $this->createProgram('Calligraphy Champion', $this->zoneA, 'individual');

        $entry1 = $this->eligibilityService->registerIndividual($student1, $prog, [
            'chest_number' => '101',
            'status' => 'confirmed',
        ]);

        $entry2 = $this->eligibilityService->registerIndividual($student2, $prog, [
            'chest_number' => '102',
            'status' => 'confirmed',
        ]);

        $judge = Judge::create([
            'name' => 'Grand Judge',
            'code' => 'JDG-99',
            'is_active' => true,
        ]);

        ScoreSheet::create([
            'judge_id' => $judge->id,
            'program_id' => $prog->id,
            'entry_id' => $entry1->id,
            'total_score' => 85.00, // Grade A (5 pts)
            'is_submitted' => true,
        ]);

        ScoreSheet::create([
            'judge_id' => $judge->id,
            'program_id' => $prog->id,
            'entry_id' => $entry2->id,
            'total_score' => 65.00, // Grade B (3 pts)
            'is_submitted' => true,
        ]);

        // Create Result: Entry 1 = 1st place (5 pts) with Grade A (5 pts) -> 10 pts for Pacto
        // Entry 2 = 2nd place (3 pts) with Grade B (3 pts) -> 6 pts for Yugo
        $result = Result::create([
            'program_id' => $prog->id,
            'status' => 'published',
            'published_at' => now(),
            'first_entry_id' => $entry1->id,
            'second_entry_id' => $entry2->id,
        ]);

        // Run Point Calculation Service
        $this->pointService->recalculateAllPoints();

        // Verify Points Transactions for Entry 1 (Pacto)
        $pactoPositionTx = PointsTransaction::where('group_id', $this->groupPacto->id)
            ->where('source_type', 'POSITION')
            ->where('points', 5)
            ->first();
        $this->assertNotNull($pactoPositionTx);
        $this->assertEquals($student1->id, $pactoPositionTx->student_id);

        $pactoGradeTx = PointsTransaction::where('group_id', $this->groupPacto->id)
            ->where('source_type', 'GRADE')
            ->where('points', 5)
            ->first();
        $this->assertNotNull($pactoGradeTx);

        // Verify Points Transactions for Entry 2 (Yugo)
        $yugoPositionTx = PointsTransaction::where('group_id', $this->groupYugo->id)
            ->where('source_type', 'POSITION')
            ->where('points', 3)
            ->first();
        $this->assertNotNull($yugoPositionTx);

        $yugoGradeTx = PointsTransaction::where('group_id', $this->groupYugo->id)
            ->where('source_type', 'GRADE')
            ->where('points', 3)
            ->first();
        $this->assertNotNull($yugoGradeTx);

        // Verify Group Points Cache
        $this->assertEquals(10, $this->groupPacto->fresh()->points_cache);
        $this->assertEquals(6, $this->groupYugo->fresh()->points_cache);

        // Verify Group Ranking Cache
        $this->assertEquals(1, $this->groupPacto->fresh()->rank_cache);
        $this->assertEquals(2, $this->groupYugo->fresh()->rank_cache);
    }
}
