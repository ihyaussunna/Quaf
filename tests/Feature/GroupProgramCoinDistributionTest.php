<?php

namespace Tests\Feature;

use App\Models\CoinTransaction;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use App\Services\CoinRewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GroupProgramCoinDistributionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        CoinTransaction::ensureSchema();
    }

    public function test_acceptance_criteria_five_participants_win_first_prize_with_grade_a_plus(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $groupA = Group::create([
            'name' => 'PACTO HIKMIC',
            'code' => 'PACTO',
            'slug' => 'pacto-hikmic',
            'color_hex' => '#2e3192',
        ]);

        $category = ProgramCategory::create([
            'name' => 'General Category',
            'slug' => 'general-category',
        ]);

        $program = Program::create([
            'category_id' => $category->id,
            'name' => 'Group Song Arabic',
            'code' => 'Q9-501',
            'type' => 'group',
            'participant_count' => 5,
            'status' => 'scheduled',
        ]);

        // Create 5 participants for the group program entry
        $participants = [];
        for ($i = 1; $i <= 5; $i++) {
            $participants[] = Student::create([
                'student_id' => 'STU-100'.$i,
                'name' => "Participant {$i}",
                'group_id' => $groupA->id,
                'category' => 'A Zone',
                'qr_token' => Str::random(32),
            ]);
        }

        // Create a 6th student in the same group who is NOT participating in this program
        $nonParticipant = Student::create([
            'student_id' => 'STU-9999',
            'name' => 'Non Participating Member',
            'group_id' => $groupA->id,
            'category' => 'A Zone',
            'qr_token' => Str::random(32),
        ]);

        // Create the team entry
        $entry1 = ProgramEntry::create([
            'program_id' => $program->id,
            'group_id' => $groupA->id,
            'chest_number' => 'G501',
            'attendance_status' => 'present',
            'status' => 'verified',
        ]);

        // Officially attach the 5 participants to the entry
        foreach ($participants as $idx => $student) {
            $entry1->participants()->attach($student->id, [
                'role' => $idx === 0 ? 'captain' : 'participant',
            ]);
        }

        // Create draft result with 1st place and Grade A+
        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entry1->id,
            'first_grade' => 'A+',
            'status' => 'verified',
        ]);

        // Verify Preview before publishing
        $previewResponse = $this->actingAs($admin)->getJson(route('admin.results.coin-preview', $result));
        $previewResponse->assertOk();
        $previewData = $previewResponse->json();

        $this->assertEquals($program->id, $previewData['program_id']);
        $this->assertEquals(5, $previewData['total_eligible_participants']);
        $this->assertEquals(1000, $previewData['total_coins_to_credit']);
        $this->assertCount(1, $previewData['placements']);
        $this->assertEquals(200, $previewData['placements'][0]['coins_per_participant']);
        $this->assertCount(5, $previewData['placements'][0]['participants']);

        // Publish the result
        $publishResponse = $this->actingAs($admin)->post(route('admin.results.publish', $result));
        $publishResponse->assertRedirect();
        $result->refresh();
        $this->assertEquals('published', $result->status);

        // Verify Acceptance Criteria:
        // 1. Five participating students receive 200 coins each
        foreach ($participants as $student) {
            $student->refresh();
            $this->assertEquals(200, $student->coins_cache, "Student {$student->name} should have 200 coins.");

            $this->assertDatabaseHas('coin_transactions', [
                'result_id' => $result->id,
                'program_id' => $program->id,
                'entry_id' => $entry1->id,
                'group_id' => $groupA->id,
                'student_id' => $student->id,
                'prize_position' => 1,
                'grade' => 'A+',
                'reward_rule_id' => CoinRewardService::REWARD_RULE_ID,
                'coins' => 200,
                'transaction_type' => 'credit',
            ]);
        }

        // 2. Total credit: 1,000 coins across all wallets
        $totalCoinsCredited = CoinTransaction::where('result_id', $result->id)
            ->where('transaction_type', 'credit')
            ->sum('coins');
        $this->assertEquals(1000, $totalCoinsCredited);

        // 3. Other students in the same group receive zero coins
        $nonParticipant->refresh();
        $this->assertEquals(0, (int) $nonParticipant->coins_cache);
        $this->assertDatabaseMissing('coin_transactions', [
            'result_id' => $result->id,
            'student_id' => $nonParticipant->id,
        ]);

        // 4. Idempotency Protection: Re-publishing the same result does NOT create duplicate credits
        $rePublishService = app(CoinRewardService::class);
        $secondRun = $rePublishService->distributeCoinsForResult($result);

        $this->assertEquals(0, $secondRun['credited_count']);
        $this->assertEquals(0, $secondRun['total_coins']);

        // Total transactions count must strictly remain 5
        $this->assertEquals(5, CoinTransaction::where('result_id', $result->id)->count());

        foreach ($participants as $student) {
            $student->refresh();
            $this->assertEquals(200, $student->coins_cache);
        }
    }

    public function test_group_reward_chart_matrix_for_all_positions_and_grades(): void
    {
        // 1st Prize
        $this->assertEquals(200, CoinRewardService::calculateGroupRewardPerParticipant(1, 'A+'));
        $this->assertEquals(180, CoinRewardService::calculateGroupRewardPerParticipant(1, 'A'));
        $this->assertEquals(170, CoinRewardService::calculateGroupRewardPerParticipant(1, 'B'));
        $this->assertEquals(160, CoinRewardService::calculateGroupRewardPerParticipant(1, 'C'));
        $this->assertEquals(100, CoinRewardService::calculateGroupRewardPerParticipant(1, null));
        $this->assertEquals(100, CoinRewardService::calculateGroupRewardPerParticipant(1, 'NO_GRADE'));

        // 2nd Prize
        $this->assertEquals(180, CoinRewardService::calculateGroupRewardPerParticipant(2, 'A+'));
        $this->assertEquals(160, CoinRewardService::calculateGroupRewardPerParticipant(2, 'A'));
        $this->assertEquals(150, CoinRewardService::calculateGroupRewardPerParticipant(2, 'B'));
        $this->assertEquals(140, CoinRewardService::calculateGroupRewardPerParticipant(2, 'C'));
        $this->assertEquals(80, CoinRewardService::calculateGroupRewardPerParticipant(2, null));
        $this->assertEquals(80, CoinRewardService::calculateGroupRewardPerParticipant(2, 'NO_GRADE'));

        // 3rd Prize
        $this->assertEquals(160, CoinRewardService::calculateGroupRewardPerParticipant(3, 'A+'));
        $this->assertEquals(140, CoinRewardService::calculateGroupRewardPerParticipant(3, 'A'));
        $this->assertEquals(130, CoinRewardService::calculateGroupRewardPerParticipant(3, 'B'));
        $this->assertEquals(120, CoinRewardService::calculateGroupRewardPerParticipant(3, 'C'));
        $this->assertEquals(60, CoinRewardService::calculateGroupRewardPerParticipant(3, null));
        $this->assertEquals(60, CoinRewardService::calculateGroupRewardPerParticipant(3, 'NO_GRADE'));

        // Invalid position
        $this->assertEquals(0, CoinRewardService::calculateGroupRewardPerParticipant(4, 'A+'));
    }

    public function test_green_room_absent_participants_receive_zero_coins(): void
    {
        $group = Group::create([
            'name' => 'LUMO FIKRIC',
            'code' => 'LUMO',
            'slug' => 'lumo',
            'color_hex' => '#56286b',
        ]);

        $program = Program::create([
            'name' => 'Skit English',
            'code' => 'Q9-601',
            'type' => 'group',
            'participant_count' => 3,
            'status' => 'scheduled',
        ]);

        $students = [];
        for ($i = 1; $i <= 3; $i++) {
            $students[] = Student::create([
                'student_id' => 'STU-60'.$i,
                'name' => "Skit Actor {$i}",
                'group_id' => $group->id,
                'qr_token' => Str::random(32),
            ]);
        }

        // Absent entry
        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'group_id' => $group->id,
            'chest_number' => 'G601',
            'attendance_status' => 'absent',
            'status' => 'verified',
        ]);

        foreach ($students as $stu) {
            $entry->participants()->attach($stu->id, ['role' => 'participant']);
        }

        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entry->id,
            'first_grade' => 'A+',
            'status' => 'published',
        ]);

        app(CoinRewardService::class)->distributeCoinsForResult($result);

        // Verification: 0 coins for absent entry
        $this->assertEquals(0, CoinTransaction::where('result_id', $result->id)->count());
        foreach ($students as $stu) {
            $stu->refresh();
            $this->assertEquals(0, (int) $stu->coins_cache);
        }
    }

    public function test_reversal_and_cache_sync_when_result_is_deleted_or_undeclared(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $group = Group::create([
            'name' => 'CONCO MAJDIC',
            'code' => 'CONCO',
            'slug' => 'conco',
            'color_hex' => '#f8e709',
        ]);

        $program = Program::create([
            'name' => 'Mime',
            'code' => 'Q9-701',
            'type' => 'group',
            'participant_count' => 2,
            'status' => 'scheduled',
        ]);

        $studentA = Student::create([
            'student_id' => 'STU-701',
            'name' => 'Mime 1',
            'group_id' => $group->id,
            'qr_token' => Str::random(32),
        ]);
        $studentB = Student::create([
            'student_id' => 'STU-702',
            'name' => 'Mime 2',
            'group_id' => $group->id,
            'qr_token' => Str::random(32),
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'group_id' => $group->id,
            'chest_number' => 'G701',
            'attendance_status' => 'present',
            'status' => 'verified',
        ]);
        $entry->participants()->attach([$studentA->id, $studentB->id], ['role' => 'participant']);

        // 2nd prize with No Grade = 80 coins each
        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => null,
            'second_entry_id' => $entry->id,
            'second_grade' => null,
            'status' => 'published',
        ]);

        app(CoinRewardService::class)->distributeCoinsForResult($result);

        $studentA->refresh();
        $studentB->refresh();
        $this->assertEquals(80, $studentA->coins_cache);
        $this->assertEquals(80, $studentB->coins_cache);

        // Now delete the result verdict
        $this->actingAs($admin)->delete(route('admin.results.destroy', $result));

        // Reversal transactions should exist and coins_cache back to 0
        $this->assertDatabaseHas('coin_transactions', [
            'student_id' => $studentA->id,
            'transaction_type' => 'reversal',
            'coins' => -80,
        ]);

        $studentA->refresh();
        $studentB->refresh();
        $this->assertEquals(0, $studentA->coins_cache);
        $this->assertEquals(0, $studentB->coins_cache);
    }

    public function test_individual_programs_now_award_coins_based_on_position_and_grade(): void
    {
        $group = Group::create([
            'name' => 'YUGO RUSHDIC',
            'code' => 'YUGO',
            'slug' => 'yugo',
            'color_hex' => '#ad1e56',
        ]);

        $program = Program::create([
            'name' => 'Solo Recitation',
            'code' => 'Q9-101',
            'type' => 'individual',
            'status' => 'scheduled',
        ]);

        $student = Student::create([
            'student_id' => 'STU-101',
            'name' => 'Solo Participant',
            'group_id' => $group->id,
            'qr_token' => Str::random(32),
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'chest_number' => '101',
            'attendance_status' => 'present',
            'status' => 'verified',
        ]);

        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entry->id,
            'first_grade' => 'A+',
            'status' => 'published',
        ]);

        $res = app(CoinRewardService::class)->distributeCoinsForResult($result);

        $this->assertFalse($res['skipped']);
        $this->assertEquals(1, $res['credited_count']);
        $this->assertEquals(200, $res['total_coins']);
        $this->assertEquals(1, CoinTransaction::count());
    }

    public function test_multi_podium_places_award_correct_individual_rewards(): void
    {
        $groupA = Group::create(['name' => 'Group A', 'code' => 'GA', 'slug' => 'ga', 'color_hex' => '#111111']);
        $groupB = Group::create(['name' => 'Group B', 'code' => 'GB', 'slug' => 'gb', 'color_hex' => '#222222']);
        $groupC = Group::create(['name' => 'Group C', 'code' => 'GC', 'slug' => 'gc', 'color_hex' => '#333333']);

        $program = Program::create([
            'name' => 'Qawwali',
            'code' => 'Q9-801',
            'type' => 'group',
            'participant_count' => 3,
            'status' => 'scheduled',
        ]);

        // 1st place: 2 participants, Grade A -> 180 coins each
        $s1 = Student::create(['student_id' => 'S1', 'name' => 'A1', 'group_id' => $groupA->id, 'qr_token' => Str::random(32)]);
        $s2 = Student::create(['student_id' => 'S2', 'name' => 'A2', 'group_id' => $groupA->id, 'qr_token' => Str::random(32)]);
        $e1 = ProgramEntry::create(['program_id' => $program->id, 'group_id' => $groupA->id, 'chest_number' => 'E1', 'attendance_status' => 'present', 'status' => 'verified']);
        $e1->participants()->attach([$s1->id, $s2->id]);

        // 2nd place: 2 participants, Grade B -> 150 coins each
        $s3 = Student::create(['student_id' => 'S3', 'name' => 'B1', 'group_id' => $groupB->id, 'qr_token' => Str::random(32)]);
        $s4 = Student::create(['student_id' => 'S4', 'name' => 'B2', 'group_id' => $groupB->id, 'qr_token' => Str::random(32)]);
        $e2 = ProgramEntry::create(['program_id' => $program->id, 'group_id' => $groupB->id, 'chest_number' => 'E2', 'attendance_status' => 'present', 'status' => 'verified']);
        $e2->participants()->attach([$s3->id, $s4->id]);

        // 3rd place: 2 participants, No Grade -> 60 coins each
        $s5 = Student::create(['student_id' => 'S5', 'name' => 'C1', 'group_id' => $groupC->id, 'qr_token' => Str::random(32)]);
        $s6 = Student::create(['student_id' => 'S6', 'name' => 'C2', 'group_id' => $groupC->id, 'qr_token' => Str::random(32)]);
        $e3 = ProgramEntry::create(['program_id' => $program->id, 'group_id' => $groupC->id, 'chest_number' => 'E3', 'attendance_status' => 'present', 'status' => 'verified']);
        $e3->participants()->attach([$s5->id, $s6->id]);

        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $e1->id,
            'first_grade' => 'A',
            'second_entry_id' => $e2->id,
            'second_grade' => 'B',
            'third_entry_id' => $e3->id,
            'third_grade' => null,
            'status' => 'published',
        ]);

        app(CoinRewardService::class)->distributeCoinsForResult($result);

        $s1->refresh();
        $s2->refresh();
        $this->assertEquals(180, $s1->coins_cache);
        $this->assertEquals(180, $s2->coins_cache);

        $s3->refresh();
        $s4->refresh();
        $this->assertEquals(150, $s3->coins_cache);
        $this->assertEquals(150, $s4->coins_cache);

        $s5->refresh();
        $s6->refresh();
        $this->assertEquals(60, $s5->coins_cache);
        $this->assertEquals(60, $s6->coins_cache);

        $totalCoins = CoinTransaction::where('result_id', $result->id)->where('transaction_type', 'credit')->sum('coins');
        // (180*2) + (150*2) + (60*2) = 360 + 300 + 120 = 780
        $this->assertEquals(780, $totalCoins);
    }
}
