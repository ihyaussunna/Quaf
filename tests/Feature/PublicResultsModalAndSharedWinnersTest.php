<?php

namespace Tests\Feature;

use App\Models\CoinTransaction;
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
use App\Services\PointCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicResultsModalAndSharedWinnersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Result::ensureSchema();
        CoinTransaction::ensureSchema();
    }

    public function test_public_results_page_renders_compact_button_cards_and_modal(): void
    {
        $group1 = Group::create(['name' => 'Red Fox', 'code' => 'RF', 'slug' => 'red-fox', 'color_hex' => '#be1e2d']);
        $group2 = Group::create(['name' => 'Green Warriors', 'code' => 'GW', 'slug' => 'green-warriors', 'color_hex' => '#10b981']);

        $category = ProgramCategory::create(['name' => 'General', 'code' => 'GEN', 'slug' => 'general']);

        $program = Program::create([
            'name' => 'Typography',
            'code' => 'Q9-101',
            'category_id' => $category->id,
            'eligibility' => 'Mix Zone',
            'type' => 'individual',
            'points_weight' => 1.0,
            'status' => 'completed',
        ]);

        $student1 = Student::create([
            'name' => 'Muhammed Yaseen',
            'student_id' => 'STU-101',
            'group_id' => $group1->id,
            'category' => 'General',
            'qr_token' => Str::random(32),
        ]);

        $student2 = Student::create([
            'name' => 'Ahmad Rayyan',
            'student_id' => 'STU-102',
            'group_id' => $group2->id,
            'category' => 'General',
            'qr_token' => Str::random(32),
        ]);

        $entry1 = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student1->id,
            'group_id' => $group1->id,
            'chest_number' => '101',
            'status' => 'confirmed',
            'attendance_status' => 'present',
        ]);

        $entry2 = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student2->id,
            'group_id' => $group2->id,
            'chest_number' => '102',
            'status' => 'confirmed',
            'attendance_status' => 'present',
        ]);

        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entry1->id,
            'second_entry_id' => $entry2->id,
            'first_grade' => 'A+',
            'second_grade' => 'A',
            'result_number' => 22,
            'status' => 'published',
            'is_media_published' => true,
            'published_at' => now(),
            'poster_image' => '/posters/typography.png',
        ]);

        $response = $this->get(route('results.index'));

        $response->assertOk();
        // Result number badge 22
        $response->assertSee('22');
        // Program title in red
        $response->assertSee('Typography');
        // Zone & Type subtitle
        $response->assertSee('Mix Zone');
        $response->assertSee('individual', false);
        // Modal popup elements
        $response->assertSee('Download Official Poster');
        $response->assertSee('OFFICIAL PODIUM VERDICTS');
        // Verify winner names are in the payload/data
        $response->assertSee('Muhammed Yaseen');
        $response->assertSee('Ahmad Rayyan');
    }

    public function test_shared_winners_in_first_place_are_properly_reflected(): void
    {
        $group1 = Group::create(['name' => 'Team Alpha', 'code' => 'TA', 'slug' => 'team-alpha', 'color_hex' => '#be1e2d']);
        $group2 = Group::create(['name' => 'Team Beta', 'code' => 'TB', 'slug' => 'team-beta', 'color_hex' => '#3b82f6']);

        $category = ProgramCategory::create(['name' => 'Arts', 'code' => 'ART', 'slug' => 'arts']);

        $program = Program::create([
            'name' => 'Calligraphy',
            'code' => 'Q9-202',
            'category_id' => $category->id,
            'eligibility' => 'Sub Junior',
            'type' => 'individual',
            'points_weight' => 1.0,
            'status' => 'completed',
        ]);

        $student1 = Student::create(['name' => 'Participant One', 'student_id' => 'STU-201', 'group_id' => $group1->id, 'category' => 'General', 'qr_token' => Str::random(32)]);
        $student2 = Student::create(['name' => 'Participant Two', 'student_id' => 'STU-202', 'group_id' => $group2->id, 'category' => 'General', 'qr_token' => Str::random(32)]);

        $entry1 = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student1->id,
            'group_id' => $group1->id,
            'chest_number' => '201',
            'status' => 'confirmed',
            'attendance_status' => 'present',
        ]);

        $entry2 = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student2->id,
            'group_id' => $group2->id,
            'chest_number' => '202',
            'status' => 'confirmed',
            'attendance_status' => 'present',
        ]);

        // Submit equal scores for both entries (e.g. 95 marks each) => Tie for 1st place
        $judgeUser = User::factory()->create(['role' => 'judge']);
        $judge = Judge::create([
            'user_id' => $judgeUser->id,
            'name' => 'Juror One',
            'access_code' => '9988',
        ]);

        ScoreSheet::create([
            'program_id' => $program->id,
            'entry_id' => $entry1->id,
            'judge_id' => $judge->id,
            'total_score' => 95.0,
            'is_submitted' => true,
        ]);

        ScoreSheet::create([
            'program_id' => $program->id,
            'entry_id' => $entry2->id,
            'judge_id' => $judge->id,
            'total_score' => 95.0,
            'is_submitted' => true,
        ]);

        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entry1->id,
            'first_grade' => 'A+',
            'result_number' => 5,
            'status' => 'published',
            'published_at' => now(),
            'is_media_published' => true,
        ]);

        // Verify podium calculation has ties
        $podiumWinners = $result->getPodiumWinnersWithTies();
        $this->assertTrue($podiumWinners['has_ties']);
        $this->assertCount(2, $podiumWinners[1]);
        $this->assertEquals('Participant One', $podiumWinners[1][0]['name']);
        $this->assertEquals('Participant Two', $podiumWinners[1][1]['name']);
        $this->assertTrue($podiumWinners[1][0]['is_tied']);
        $this->assertTrue($podiumWinners[1][1]['is_tied']);

        // Verify poster winners contain both tied winners with rank 01
        $posterWinners = $result->getPosterWinners();
        $this->assertCount(2, $posterWinners['first']);
        $this->assertEquals('01', $posterWinners['first'][0]['rank']);
        $this->assertEquals('01', $posterWinners['first'][1]['rank']);

        // Verify PointCalculationService awards position points to BOTH tied 1st place entries
        app(PointCalculationService::class)->recalculateAllPoints();

        $group1Points = Group::find($group1->id)->points_cache;
        $group2Points = Group::find($group2->id)->points_cache;

        // Both groups should receive 1st place position points (5 pts) + Grade A+ (6 pts) = 11 pts
        $this->assertEquals(11, $group1Points);
        $this->assertEquals(11, $group2Points);

        // Verify CoinRewardService awards prize coins to BOTH tied 1st place students
        $coinService = app(CoinRewardService::class);
        $coinService->distributeCoinsForResult($result);

        $student1Coins = Student::find($student1->id)->coins_cache;
        $student2Coins = Student::find($student2->id)->coins_cache;

        $this->assertGreaterThan(0, $student1Coins);
        $this->assertEquals($student1Coins, $student2Coins);

        // Verify Announcer index renders both shared winners
        $announcerUser = User::factory()->create(['role' => 'announcer']);
        $response = $this->actingAs($announcerUser)->get(route('announcer.index', ['tab' => 'all']));
        $response->assertOk();
        $response->assertSee('Participant One');
        $response->assertSee('Participant Two');
        $response->assertSee('Shared');
    }

    public function test_custom_studio_winners_with_ties_are_rendered_in_poster_and_public_modal(): void
    {
        $groupA = Group::create(['name' => 'Team Emerald', 'code' => 'TE', 'slug' => 'team-emerald', 'color_hex' => '#10b981']);
        $groupB = Group::create(['name' => 'Team Ruby', 'code' => 'TR', 'slug' => 'team-ruby', 'color_hex' => '#be1e2d']);
        $groupC = Group::create(['name' => 'Team Sapphire', 'code' => 'TS', 'slug' => 'team-sapphire', 'color_hex' => '#3b82f6']);

        $category = ProgramCategory::create(['name' => 'Literary', 'code' => 'LIT', 'slug' => 'literary']);

        $program = Program::create([
            'name' => 'Essay Writing',
            'code' => 'Q9-303',
            'category_id' => $category->id,
            'eligibility' => 'Senior',
            'type' => 'individual',
            'points_weight' => 1.0,
            'status' => 'completed',
        ]);

        $stuA = Student::create(['name' => 'Zayd Ali', 'student_id' => 'STU-301', 'group_id' => $groupA->id, 'category' => 'General', 'qr_token' => Str::random(32)]);
        $stuB = Student::create(['name' => 'Omar Farooq', 'student_id' => 'STU-302', 'group_id' => $groupB->id, 'category' => 'General', 'qr_token' => Str::random(32)]);
        $stuC = Student::create(['name' => 'Hamza Tariq', 'student_id' => 'STU-303', 'group_id' => $groupC->id, 'category' => 'General', 'qr_token' => Str::random(32)]);

        $entryA = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $stuA->id,
            'group_id' => $groupA->id,
            'chest_number' => '301',
            'status' => 'confirmed',
            'attendance_status' => 'present',
        ]);

        $entryB = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $stuB->id,
            'group_id' => $groupB->id,
            'chest_number' => '302',
            'status' => 'confirmed',
            'attendance_status' => 'present',
        ]);

        $entryC = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $stuC->id,
            'group_id' => $groupC->id,
            'chest_number' => '303',
            'status' => 'confirmed',
            'attendance_status' => 'present',
        ]);

        // Manually configured shared 2nd place winners in custom_poster_settings
        $customWinners = [
            'first' => [
                ['id' => $entryA->id, 'name' => 'Zayd Ali', 'unit' => 'Team Emerald', 'chest' => '301'],
            ],
            'second' => [
                ['id' => $entryB->id, 'name' => 'Omar Farooq', 'unit' => 'Team Ruby', 'chest' => '302'],
                ['id' => $entryC->id, 'name' => 'Hamza Tariq', 'unit' => 'Team Sapphire', 'chest' => '303'],
            ],
            'third' => [],
        ];

        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entryA->id,
            'second_entry_id' => $entryB->id,
            'first_grade' => 'A+',
            'second_grade' => 'A',
            'result_number' => 8,
            'status' => 'published',
            'is_media_published' => true,
            'published_at' => now(),
            'custom_poster_settings' => [
                'custom_winners' => $customWinners,
            ],
        ]);

        $podium = $result->getPodiumWinnersWithTies();
        $this->assertTrue($podium['has_ties']);
        $this->assertCount(1, $podium[1]);
        $this->assertCount(2, $podium[2]);
        $this->assertTrue($podium[2][0]['is_tied']);
        $this->assertTrue($podium[2][1]['is_tied']);

        // Check public results page
        $response = $this->get(route('results.index'));
        $response->assertOk();
        $response->assertSee('Omar Farooq');
        $response->assertSee('Hamza Tariq');
        $response->assertSee('Shared / Tied');
    }
}
