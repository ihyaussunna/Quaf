<?php

namespace Tests\Feature;

use App\Models\CoinTransaction;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Student;
use App\Services\CoinRewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentDuplicateProgramCoinDeduplicationTest extends TestCase
{
    use RefreshDatabase;

    protected Group $group;

    protected Student $student;

    protected ProgramCategory $category;

    protected Program $program;

    protected function setUp(): void
    {
        parent::setUp();

        $this->group = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'display_order' => 1,
            'color' => '#10b981',
        ]);

        $this->student = Student::create([
            'student_id' => 'QF1001',
            'name' => 'JAMALUDHEEN ABDUL HAMEED',
            'group_id' => $this->group->id,
            'category' => 'A Zone',
            'qr_token' => Str::random(32),
            'coins_cache' => 0,
        ]);

        $this->category = ProgramCategory::create([
            'name' => 'Mix Zone',
            'slug' => 'mix-zone',
        ]);

        $this->program = Program::create([
            'name' => 'Urdu Essay Writing',
            'code' => 'Q9-206',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'status' => 'completed',
        ]);
    }

    public function test_distribute_coins_for_result_does_not_create_duplicate_rewards_for_same_student_in_same_program(): void
    {
        // Simulate two entries for the same student in this program (e.g. due to duplicate entry or mix zone limit 2)
        $entry1 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'chest_number' => 'QF1001',
        ]);

        $entry2 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'chest_number' => 'QF1001A',
        ]);

        // Result where entry1 is 3rd place, and entry2 is shared 3rd place
        $result = Result::create([
            'program_id' => $this->program->id,
            'status' => 'published',
            'is_media_published' => true,
            'third_entry_id' => $entry1->id,
            'third_grade' => 'C',
            'custom_poster_settings' => [
                'shared_entry_ids' => [
                    3 => [$entry2->id],
                ],
            ],
        ]);

        $service = app(CoinRewardService::class);
        $service->distributeCoinsForResult($result);

        // Only ONE credit transaction should exist for this student and program
        $credits = CoinTransaction::where('student_id', $this->student->id)
            ->where('program_id', $this->program->id)
            ->where('transaction_type', 'credit')
            ->get();

        $this->assertCount(1, $credits);
        $this->assertEquals(200, $credits->first()->coins); // 3rd prize with Grade C for individual = 200 coins
    }

    public function test_cleanup_duplicate_transactions_reverses_extra_coins_and_syncs_wallet_balance(): void
    {
        // Manually seed duplicate transactions (simulating the bug in production)
        CoinTransaction::create([
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'program_id' => $this->program->id,
            'prize_position' => 3,
            'grade' => 'C',
            'coins' => 200,
            'transaction_type' => 'credit',
            'transaction_reference' => 'TX1',
            'status' => 'completed',
            'description' => 'First credit',
        ]);

        CoinTransaction::create([
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'program_id' => $this->program->id,
            'prize_position' => 3,
            'grade' => 'C',
            'coins' => 200,
            'transaction_type' => 'credit',
            'transaction_reference' => 'TX2',
            'status' => 'completed',
            'description' => 'Duplicate credit',
        ]);

        $this->student->update(['coins_cache' => 400]);

        // Run cleanup
        $cleaned = CoinRewardService::cleanupDuplicateTransactionsForStudent($this->student);
        $this->assertTrue($cleaned);

        // Duplicate must be removed
        $remaining = CoinTransaction::where('student_id', $this->student->id)
            ->where('program_id', $this->program->id)
            ->where('transaction_type', 'credit')
            ->get();

        $this->assertCount(1, $remaining);

        // Student's cache must be 200, not 400
        $this->student->refresh();
        $this->assertEquals(200, $this->student->coins_cache);
    }

    public function test_get_student_achievements_never_lists_same_program_twice(): void
    {
        // Create duplicate transactions in DB
        CoinTransaction::create([
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'program_id' => $this->program->id,
            'prize_position' => 3,
            'grade' => 'C',
            'coins' => 200,
            'transaction_type' => 'credit',
            'transaction_reference' => 'TX_A',
            'status' => 'completed',
            'description' => 'Credit A',
        ]);

        CoinTransaction::create([
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'program_id' => $this->program->id,
            'prize_position' => 3,
            'grade' => 'C',
            'coins' => 200,
            'transaction_type' => 'credit',
            'transaction_reference' => 'TX_B',
            'status' => 'completed',
            'description' => 'Credit B',
        ]);

        $service = app(CoinRewardService::class);
        $achievements = $service->getStudentAchievements($this->student);

        // Third prize programs must contain Urdu Essay Writing exactly once
        $p3Programs = $achievements['prizes'][3]['programs'];
        $this->assertCount(1, $p3Programs);
        $this->assertEquals('Urdu Essay Writing', $p3Programs[0]['name']);
        $this->assertEquals(1, $achievements['prizes'][3]['count']);
        $this->assertEquals(1, $achievements['grade_counts']['C']);
    }
}
