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

class GroupProgramFullWorkflowCoinScannerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        CoinTransaction::ensureSchema();
    }

    public function test_group_program_registered_participants_and_leader_all_receive_exact_coins(): void
    {
        $group = Group::create([
            'name' => 'LUMO FIKRIC',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#7b1113',
        ]);

        $category = ProgramCategory::create([
            'name' => 'General Stage',
            'slug' => 'general-stage',
        ]);

        $program = Program::create([
            'category_id' => $category->id,
            'name' => 'Skit Presentation',
            'code' => 'Q9-999',
            'type' => 'group',
            'participant_count' => 4,
            'status' => 'scheduled',
        ]);

        // Leader student
        $leader = Student::create([
            'student_id' => 'STU-LEAD-1',
            'name' => 'Team Captain Leader',
            'group_id' => $group->id,
            'qr_token' => Str::random(32),
        ]);

        // 3 other registered members
        $members = [];
        for ($i = 1; $i <= 3; $i++) {
            $members[] = Student::create([
                'student_id' => "STU-MEM-{$i}",
                'name' => "Member {$i}",
                'group_id' => $group->id,
                'qr_token' => Str::random(32),
            ]);
        }

        // Group entry with leader as student_id and members in pivot
        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'group_id' => $group->id,
            'student_id' => $leader->id,
            'chest_number' => 'G999',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        foreach ($members as $m) {
            $entry->participants()->attach($m->id, ['role' => 'participant']);
        }

        // Program entry participants should include leader + 3 members = 4 students
        $eligible = CoinRewardService::getEligibleParticipantsForEntry($entry);
        $this->assertCount(4, $eligible);
        $this->assertTrue($eligible->contains('id', $leader->id));

        // Result: 1st Place with Grade A+
        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entry->id,
            'first_grade' => 'A+',
            'status' => 'published',
        ]);

        app(CoinRewardService::class)->distributeCoinsForResult($result);

        // Group 1st prize with Grade A+ = 200 coins per participant
        $leader->refresh();
        $this->assertEquals(200, $leader->coins_cache);

        foreach ($members as $m) {
            $m->refresh();
            $this->assertEquals(200, $m->coins_cache);
        }

        $totalCoins = CoinTransaction::where('result_id', $result->id)->where('transaction_type', 'credit')->sum('coins');
        $this->assertEquals(800, $totalCoins); // 200 * 4 = 800
    }

    public function test_individual_graded_and_non_graded_rates_match_user_specification(): void
    {
        // 1st Place Graded: A+=300, A=280, B=270, C=260
        $this->assertEquals(300, CoinRewardService::calculateReward(1, 'A+', false));
        $this->assertEquals(280, CoinRewardService::calculateReward(1, 'A', false));
        $this->assertEquals(270, CoinRewardService::calculateReward(1, 'B', false));
        $this->assertEquals(260, CoinRewardService::calculateReward(1, 'C', false));

        // 2nd Place Graded: A+=270, A=250, B=240, C=230
        $this->assertEquals(270, CoinRewardService::calculateReward(2, 'A+', false));
        $this->assertEquals(250, CoinRewardService::calculateReward(2, 'A', false));
        $this->assertEquals(240, CoinRewardService::calculateReward(2, 'B', false));
        $this->assertEquals(230, CoinRewardService::calculateReward(2, 'C', false));

        // 3rd Place Graded: A+=240, A=220, B=210, C=200
        $this->assertEquals(240, CoinRewardService::calculateReward(3, 'A+', false));
        $this->assertEquals(220, CoinRewardService::calculateReward(3, 'A', false));
        $this->assertEquals(210, CoinRewardService::calculateReward(3, 'B', false));
        $this->assertEquals(200, CoinRewardService::calculateReward(3, 'C', false));

        // Non-Graded Individual: 1st=200, 2nd=170, 3rd=140
        $this->assertEquals(200, CoinRewardService::calculateReward(1, null, false));
        $this->assertEquals(170, CoinRewardService::calculateReward(2, null, false));
        $this->assertEquals(140, CoinRewardService::calculateReward(3, null, false));

        // Group Graded:
        // 1st: A+=200, A=180, B=170, C=160
        $this->assertEquals(200, CoinRewardService::calculateReward(1, 'A+', true));
        $this->assertEquals(180, CoinRewardService::calculateReward(1, 'A', true));
        $this->assertEquals(170, CoinRewardService::calculateReward(1, 'B', true));
        $this->assertEquals(160, CoinRewardService::calculateReward(1, 'C', true));

        // 2nd: A+=180, A=160, B=150, C=140
        $this->assertEquals(180, CoinRewardService::calculateReward(2, 'A+', true));
        $this->assertEquals(160, CoinRewardService::calculateReward(2, 'A', true));
        $this->assertEquals(150, CoinRewardService::calculateReward(2, 'B', true));
        $this->assertEquals(140, CoinRewardService::calculateReward(2, 'C', true));

        // 3rd: A+=160, A=140, B=130, C=120
        $this->assertEquals(160, CoinRewardService::calculateReward(3, 'A+', true));
        $this->assertEquals(140, CoinRewardService::calculateReward(3, 'A', true));
        $this->assertEquals(130, CoinRewardService::calculateReward(3, 'B', true));
        $this->assertEquals(120, CoinRewardService::calculateReward(3, 'C', true));

        // Non-Graded Group: 1st=100, 2nd=80, 3rd=60
        $this->assertEquals(100, CoinRewardService::calculateReward(1, null, true));
        $this->assertEquals(80, CoinRewardService::calculateReward(2, null, true));
        $this->assertEquals(60, CoinRewardService::calculateReward(3, null, true));
    }

    public function test_scanner_presentation_desk_resolves_and_credits_group_participant_coins(): void
    {
        $presentationUser = User::factory()->create(['role' => 'presentation_team']);

        $group = Group::create([
            'name' => 'PACTO HIKMIC',
            'code' => 'PACTO',
            'slug' => 'pacto',
            'color_hex' => '#112233',
        ]);

        $program = Program::create([
            'name' => 'Duff Muttu',
            'code' => 'Q9-555',
            'type' => 'group',
            'participant_count' => 2,
            'status' => 'scheduled',
        ]);

        $student = Student::create([
            'student_id' => 'QF7777',
            'name' => 'Group Participant Student',
            'group_id' => $group->id,
            'qr_token' => 'qr_token_7777',
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'group_id' => $group->id,
            'chest_number' => 'G555',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);
        $entry->participants()->attach($student->id, ['role' => 'participant']);

        // Result: 2nd Place with Grade A (Group 2nd with Grade A = 160 coins)
        $result = Result::create([
            'program_id' => $program->id,
            'second_entry_id' => $entry->id,
            'second_grade' => 'A',
            'status' => 'published',
        ]);

        // Presentation Desk scans the student via qr_token query
        $response = $this->actingAs($presentationUser)
            ->get(route('presentation.dashboard', ['student' => $student->qr_token]));

        $response->assertOk();
        $response->assertSee('160'); // 160 coins balance
        $response->assertSee('Group Participant Student');
        $response->assertSee('Duff Muttu');

        $student->refresh();
        $this->assertEquals(160, $student->coins_cache);
    }
}
