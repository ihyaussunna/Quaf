<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\ScoreSheet;
use App\Models\Student;
use App\Models\User;
use App\Services\CoinRewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultJudgmentScoreUpdateSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Group $groupA;

    protected Group $groupB;

    protected Group $groupC;

    protected ProgramCategory $category;

    protected Judge $judge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'name' => 'System Admin',
            'email' => 'admin@quaf.test',
        ]);

        $this->groupA = Group::firstOrCreate(
            ['code' => 'UH'],
            ['name' => 'Unio Hilmic', 'slug' => 'unio-hilmic', 'color_hex' => '#10b981', 'display_order' => 1]
        );

        $this->groupB = Group::firstOrCreate(
            ['code' => 'YR'],
            ['name' => 'Yugo Rushdic', 'slug' => 'yugo-rushdic', 'color_hex' => '#3b82f6', 'display_order' => 2]
        );

        $this->groupC = Group::firstOrCreate(
            ['code' => 'CM'],
            ['name' => 'Conco Majdic', 'slug' => 'conco-majdic', 'color_hex' => '#f59e0b', 'display_order' => 3]
        );

        $this->category = ProgramCategory::firstOrCreate(
            ['code' => 'MIX'],
            ['name' => 'Mix Zone', 'slug' => 'mix-zone']
        );

        $this->judge = Judge::firstOrCreate(
            ['code' => 'J01'],
            ['name' => 'Chief Judge', 'status' => 'active']
        );
    }

    public function test_when_marks_are_updated_only_latest_result_shows_and_old_coins_are_clawed_back(): void
    {
        $program = Program::create([
            'category_id' => $this->category->id,
            'name' => 'Photo Feature',
            'code' => 'Q9-243',
            'type' => 'individual',
            'eligibility' => 'Mix Zone',
            'points_weight' => 1.0,
            'status' => 'published',
        ]);

        // Contestants
        // Old 1st: Ahamed Farhan (QF5012)
        $studentFarhan = Student::create([
            'name' => 'Ahammed Farhan KP',
            'student_id' => 'QF5012',
            'group_id' => $this->groupB->id,
            'category' => 'Mix Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_farhan',
            'coins_cache' => 0,
        ]);
        $entryFarhan = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $studentFarhan->id,
            'group_id' => $this->groupB->id,
            'chest_number' => 'QF5012',
            'attendance_status' => 'present',
            'status' => 'verified',
        ]);

        // Old 2nd: Abdul Bari P (QF3127)
        $studentBari = Student::create([
            'name' => 'Abdul Bari. P',
            'student_id' => 'QF3127',
            'group_id' => $this->groupC->id,
            'category' => 'Mix Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_bari',
            'coins_cache' => 0,
        ]);
        $entryBari = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $studentBari->id,
            'group_id' => $this->groupC->id,
            'chest_number' => 'QF3127',
            'attendance_status' => 'present',
            'status' => 'verified',
        ]);

        // Old 3rd: Alif Kollam (QF1229)
        $studentAlif = Student::create([
            'name' => 'Alif Kollam',
            'student_id' => 'QF1229',
            'group_id' => $this->groupA->id,
            'category' => 'Mix Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_alif',
            'coins_cache' => 0,
        ]);
        $entryAlif = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $studentAlif->id,
            'group_id' => $this->groupA->id,
            'chest_number' => 'QF1229',
            'attendance_status' => 'present',
            'status' => 'verified',
        ]);

        // New Winner: Abu Hamid K (QF4012)
        $studentHamid = Student::create([
            'name' => 'Abu Hamid. K',
            'student_id' => 'QF4012',
            'group_id' => $this->groupA->id,
            'category' => 'Mix Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_hamid',
            'coins_cache' => 0,
        ]);
        $entryHamid = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $studentHamid->id,
            'group_id' => $this->groupA->id,
            'chest_number' => 'QF4012',
            'attendance_status' => 'present',
            'status' => 'verified',
        ]);

        // New 3rd: Muhammed Razeen (QF4033)
        $studentRazeen = Student::create([
            'name' => 'Muhammed Razeen',
            'student_id' => 'QF4033',
            'group_id' => $this->groupA->id,
            'category' => 'Mix Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_razeen',
            'coins_cache' => 0,
        ]);
        $entryRazeen = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $studentRazeen->id,
            'group_id' => $this->groupA->id,
            'chest_number' => 'QF4033',
            'attendance_status' => 'present',
            'status' => 'verified',
        ]);

        // Step 1: Create initial result where Farhan=1st (300 coins), Bari=2nd (250 coins), Alif=3rd (220 coins)
        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entryFarhan->id,
            'first_grade' => 'A',
            'second_entry_id' => $entryBari->id,
            'second_grade' => 'A',
            'third_entry_id' => $entryAlif->id,
            'third_grade' => 'A',
            'status' => 'published',
            'published_at' => now(),
            'result_number' => 140,
            'is_media_published' => true,
        ]);

        app(CoinRewardService::class)->distributeCoinsForResult($result);

        $studentFarhan->refresh();
        $studentBari->refresh();
        $studentAlif->refresh();
        $this->assertEquals(280, $studentFarhan->coins_cache); // 1st Grade A = 280
        $this->assertEquals(250, $studentBari->coins_cache);   // 2nd Grade A = 250
        $this->assertEquals(220, $studentAlif->coins_cache);   // 3rd Grade A = 220

        // Step 2: Now marks are updated by judges!
        // Hamid: Score 85 (Grade A) -> 1st Place!
        ScoreSheet::create([
            'program_id' => $program->id,
            'entry_id' => $entryHamid->id,
            'judge_id' => $this->judge->id,
            'total_score' => 85,
            'is_submitted' => true,
        ]);
        // Farhan: Score 80 (Grade A) -> 2nd Place!
        ScoreSheet::create([
            'program_id' => $program->id,
            'entry_id' => $entryFarhan->id,
            'judge_id' => $this->judge->id,
            'total_score' => 80,
            'is_submitted' => true,
        ]);
        // Razeen: Score 74 (Grade A) -> 3rd Place!
        ScoreSheet::create([
            'program_id' => $program->id,
            'entry_id' => $entryRazeen->id,
            'judge_id' => $this->judge->id,
            'total_score' => 74,
            'is_submitted' => true,
        ]);
        // Bari: Score 50 -> Unplaced
        ScoreSheet::create([
            'program_id' => $program->id,
            'entry_id' => $entryBari->id,
            'judge_id' => $this->judge->id,
            'total_score' => 50,
            'is_submitted' => true,
        ]);
        // Alif: Score 50 -> Unplaced
        ScoreSheet::create([
            'program_id' => $program->id,
            'entry_id' => $entryAlif->id,
            'judge_id' => $this->judge->id,
            'total_score' => 50,
            'is_submitted' => true,
        ]);

        // Step 3: Fetch podium with ties
        $podium = $result->getPodiumWinnersWithTies();

        // Verify that ONLY the latest winners are returned (NO duplicates, NO fake ties!)
        $this->assertCount(1, $podium[1], '1st place must have exactly 1 winner');
        $this->assertEquals('Abu Hamid. K', $podium[1][0]['name']);

        $this->assertCount(1, $podium[2], '2nd place must have exactly 1 winner');
        $this->assertEquals('Ahammed Farhan KP', $podium[2][0]['name']);

        $this->assertCount(1, $podium[3], '3rd place must have exactly 1 winner');
        $this->assertEquals('Muhammed Razeen', $podium[3][0]['name']);

        $this->assertFalse($podium['has_ties'], 'There should be NO ties in this result');

        // Step 4: Verify coins were automatically clawed back and redistributed!
        $studentHamid->refresh();
        $studentFarhan->refresh();
        $studentRazeen->refresh();
        $studentBari->refresh();
        $studentAlif->refresh();

        // Hamid gets 1st place coins (280)
        $this->assertEquals(280, $studentHamid->coins_cache);

        // Farhan was demoted from 1st (280) to 2nd (250) -> wallet updated to 250
        $this->assertEquals(250, $studentFarhan->coins_cache);

        // Razeen gets 3rd place coins (220)
        $this->assertEquals(220, $studentRazeen->coins_cache);

        // Bari is UNPLACED -> ALL coins clawed back to 0!
        $this->assertEquals(0, $studentBari->coins_cache);

        // Alif is UNPLACED -> ALL coins clawed back to 0!
        $this->assertEquals(0, $studentAlif->coins_cache);

        // Verify reversal transactions exist for Bari and Alif
        $this->assertDatabaseHas('coin_transactions', [
            'student_id' => $studentBari->id,
            'program_id' => $program->id,
            'transaction_type' => 'reversal',
            'coins' => 0,
        ]);
        $this->assertDatabaseHas('coin_transactions', [
            'student_id' => $studentAlif->id,
            'program_id' => $program->id,
            'transaction_type' => 'reversal',
            'coins' => 0,
        ]);

        // Step 5: Test public results view
        $publicResultsResponse = $this->get(route('results.index'));
        $publicResultsResponse->assertOk();
        $publicResultsResponse->assertSee('Abu Hamid. K');
        $publicResultsResponse->assertSee('Ahammed Farhan Kp');
        $publicResultsResponse->assertSee('Muhammed Razeen');
        // Old unplaced contestants should NOT appear in the payload as winners
        $this->assertStringNotContainsString('Abdul Bari. P', $publicResultsResponse->getContent());
        $this->assertStringNotContainsString('Alif Kollam', $publicResultsResponse->getContent());
    }

    public function test_admin_mark_check_sync_button_updates_result_and_adjusts_coins(): void
    {
        $program = Program::create([
            'category_id' => $this->category->id,
            'name' => 'Painting Competition',
            'code' => 'Q9-100',
            'type' => 'individual',
            'eligibility' => 'Mix Zone',
            'points_weight' => 1.0,
            'status' => 'published',
        ]);

        $student1 = Student::create([
            'name' => 'Initial First',
            'student_id' => 'QF101',
            'group_id' => $this->groupA->id,
            'category' => 'Mix Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_101',
            'coins_cache' => 0,
        ]);
        $entry1 = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student1->id,
            'group_id' => $this->groupA->id,
            'chest_number' => 'QF101',
            'attendance_status' => 'present',
            'status' => 'verified',
        ]);

        $student2 = Student::create([
            'name' => 'New First',
            'student_id' => 'QF102',
            'group_id' => $this->groupB->id,
            'category' => 'Mix Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_102',
            'coins_cache' => 0,
        ]);
        $entry2 = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student2->id,
            'group_id' => $this->groupB->id,
            'chest_number' => 'QF102',
            'attendance_status' => 'present',
            'status' => 'verified',
        ]);

        // Old result with student1 as 1st
        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entry1->id,
            'first_grade' => 'A',
            'status' => 'published',
            'published_at' => now(),
            'result_number' => 101,
        ]);
        app(CoinRewardService::class)->distributeCoinsForResult($result);
        $student1->refresh();
        $this->assertEquals(280, $student1->coins_cache);

        // Score update: student2 gets 95, student1 gets 50
        ScoreSheet::create([
            'program_id' => $program->id,
            'entry_id' => $entry2->id,
            'judge_id' => $this->judge->id,
            'total_score' => 95,
            'is_submitted' => true,
        ]);
        ScoreSheet::create([
            'program_id' => $program->id,
            'entry_id' => $entry1->id,
            'judge_id' => $this->judge->id,
            'total_score' => 50,
            'is_submitted' => true,
        ]);

        // Admin clicks sync button
        $response = $this->actingAs($this->adminUser)->post(route('admin.mark-entry.sync-result', $program->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student1->refresh();
        $student2->refresh();
        // student1 was demoted from 1st (280) to 2nd (230) -> 50 coins clawed back, wallet is 230!
        $this->assertEquals(230, $student1->coins_cache);
        // student2 now 1st (Grade A+: 95 score) -> 300 coins!
        $this->assertEquals(300, $student2->coins_cache);
    }
}
