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

class IndividualProgramCoinDistributionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        CoinTransaction::ensureSchema();
    }

    public function test_individual_program_results_award_coins_based_on_position_and_grade(): void
    {
        $groupA = Group::create([
            'name' => 'PACTO HIKMIC',
            'code' => 'PACTO',
            'slug' => 'pacto-hikmic',
            'color_hex' => '#2e3192',
        ]);
        $groupB = Group::create([
            'name' => 'CONCO MAJDIC',
            'code' => 'CONCO',
            'slug' => 'conco-majdic',
            'color_hex' => '#f8e709',
        ]);

        $category = ProgramCategory::create([
            'name' => 'Senior Arts',
            'slug' => 'senior-arts',
        ]);

        $program = Program::create([
            'category_id' => $category->id,
            'name' => 'Elocution English',
            'code' => 'Q9-101',
            'type' => 'individual',
            'participant_count' => 1,
            'status' => 'scheduled',
        ]);

        $student1 = Student::create([
            'student_id' => 'STU-101',
            'name' => 'First Place Winner',
            'group_id' => $groupA->id,
            'category' => 'A Zone',
            'qr_token' => Str::random(32),
        ]);

        $student2 = Student::create([
            'student_id' => 'STU-102',
            'name' => 'Second Place Winner',
            'group_id' => $groupB->id,
            'category' => 'A Zone',
            'qr_token' => Str::random(32),
        ]);

        $entry1 = ProgramEntry::create([
            'program_id' => $program->id,
            'group_id' => $groupA->id,
            'student_id' => $student1->id,
            'chest_number' => '101',
            'attendance_status' => 'present',
            'status' => 'verified',
        ]);

        $entry2 = ProgramEntry::create([
            'program_id' => $program->id,
            'group_id' => $groupB->id,
            'student_id' => $student2->id,
            'chest_number' => '102',
            'attendance_status' => 'present',
            'status' => 'verified',
        ]);

        $coinService = app(CoinRewardService::class);

        // Preview checks
        $result = new Result([
            'program_id' => $program->id,
            'first_entry_id' => $entry1->id,
            'second_entry_id' => $entry2->id,
            'first_grade' => 'A+',
            'second_grade' => 'A',
            'status' => 'draft',
        ]);
        $result->setRelation('program', $program);
        $result->setRelation('firstEntry', $entry1);
        $result->setRelation('secondEntry', $entry2);

        $preview = $coinService->previewGroupCoinDistribution($result);
        $this->assertEquals(2, $preview['total_eligible_participants']);
        // 1st with A+ = 200, 2nd with A = 160 => 360 total coins
        $this->assertEquals(360, $preview['total_coins_to_credit']);

        // Save & publish result
        $result->save();
        $dist = $coinService->distributeCoinsForResult($result);

        $this->assertFalse($dist['skipped']);
        $this->assertEquals(2, $dist['credited_count']);
        $this->assertEquals(360, $dist['total_coins']);

        $student1->refresh();
        $student2->refresh();

        $this->assertEquals(200, (int) $student1->coins_cache);
        $this->assertEquals(160, (int) $student2->coins_cache);

        // Verify transaction descriptions and types
        $tx1 = CoinTransaction::where('student_id', $student1->id)->first();
        $this->assertNotNull($tx1);
        $this->assertEquals(200, $tx1->coins);
        $this->assertStringContainsString('Individual Reward', $tx1->description);
        $this->assertEquals('credit', $tx1->transaction_type);

        $tx2 = CoinTransaction::where('student_id', $student2->id)->first();
        $this->assertNotNull($tx2);
        $this->assertEquals(160, $tx2->coins);
        $this->assertStringContainsString('Individual Reward', $tx2->description);
    }
}
