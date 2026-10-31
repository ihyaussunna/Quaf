<?php

namespace Tests\Feature;

use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\StandingAnnouncement;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TeamStandingsMilestoneReleaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $announcer;

    protected User $mediaUser;

    protected Group $groupA;

    protected Group $groupB;

    protected function createStudent(Group $group, string $name, string $chest): Student
    {
        return Student::create([
            'student_id' => 'STU-'.Str::upper(Str::random(6)),
            'group_id' => $group->id,
            'name' => $name,
            'qr_token' => Str::random(32),
        ]);
    }

    protected function createEntry(Program $program, Student $student, string $chest): ProgramEntry
    {
        return ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'group_id' => $student->group_id,
            'chest_number' => $chest,
            'status' => 'verified',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Festival Administrator',
            'email' => 'admin@quaf.test',
        ]);

        $this->announcer = User::factory()->create([
            'role' => 'announcer',
            'name' => 'Festival Announcer',
            'email' => 'announcer@quaf.test',
        ]);

        $this->mediaUser = User::factory()->create([
            'role' => 'media_team',
            'name' => 'Media Coordinator',
            'email' => 'media@quaf.test',
        ]);

        $this->groupA = Group::create([
            'name' => 'House Alpha',
            'code' => 'ALPHA',
            'slug' => 'house-alpha',
            'color_hex' => '#be1e2d',
            'points_cache' => 0,
            'rank_cache' => 1,
            'public_points_cache' => 0,
            'public_rank_cache' => 1,
        ]);

        $this->groupB = Group::create([
            'name' => 'House Beta',
            'code' => 'BETA',
            'slug' => 'house-beta',
            'color_hex' => '#10b981',
            'points_cache' => 0,
            'rank_cache' => 2,
            'public_points_cache' => 0,
            'public_rank_cache' => 2,
        ]);

        FestivalSetting::set('public_standings_milestones', '10,15,20,27,35,40');
        FestivalSetting::set('public_standings_mode', 'milestone');
        FestivalSetting::set('public_standings_results_count', '0');
        FestivalSetting::set('public_standings_label', 'Initial Standings');
    }

    public function test_individual_result_publish_does_not_update_public_standings_before_milestone(): void
    {
        $category = ProgramCategory::create(['name' => 'General Stage', 'code' => 'GEN', 'slug' => 'gen-stage']);
        $program = Program::create([
            'category_id' => $category->id,
            'name' => 'Elocution Malayalam',
            'code' => 'P-101',
            'type' => 'individual',
            'eligibility' => 'all',
            'status' => 'in_progress',
        ]);

        $studentA = $this->createStudent($this->groupA, 'Student One', 'CH-101');
        $studentB = $this->createStudent($this->groupB, 'Student Two', 'CH-102');

        $entryA = $this->createEntry($program, $studentA, 'CH-101');
        $entryB = $this->createEntry($program, $studentB, 'CH-102');

        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entryA->id,
            'second_entry_id' => $entryB->id,
            'status' => 'announced',
        ]);

        // Publish via media desk
        $response = $this->actingAs($this->mediaUser)
            ->post(route('media.results.publish', $result));

        $response->assertSessionHasNoErrors();

        // 1. Live points should be updated in groups
        $this->groupA->refresh();
        $this->groupB->refresh();
        $this->assertGreaterThan(0, $this->groupA->points_cache);

        // 2. BUT public_points_cache must remain 0 because 1 result is below milestone 10
        $this->assertEquals(0, $this->groupA->public_points_cache);
        $this->assertEquals(0, $this->groupB->public_points_cache);

        // 3. Public API must reflect public_points_cache (0), not live points_cache
        $apiResponse = $this->getJson(route('api.public.standings'));
        $apiResponse->assertOk();
        $apiResponse->assertJsonFragment([
            'name' => 'House Alpha',
            'points_cache' => 0,
        ]);

        // 4. Public Home page and Groups page must render public_points_cache (0 PTS), NOT live points!
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertOk();
        $homeResponse->assertSee('House Alpha');
        $homeResponse->assertDontSee($this->groupA->points_cache.' PTS');

        $groupsResponse = $this->get(route('groups.index'));
        $groupsResponse->assertOk();
        $groupsResponse->assertSee('House Alpha');
        $groupsResponse->assertDontSee($this->groupA->points_cache.' PTS');
    }

    public function test_standings_auto_update_when_milestone_is_reached(): void
    {
        // Set milestone to 2 for testing
        FestivalSetting::set('public_standings_milestones', '2,5,10');

        $category = ProgramCategory::create(['name' => 'Arts', 'code' => 'ART', 'slug' => 'arts']);
        $studentA = $this->createStudent($this->groupA, 'Winner Student', 'CH-WIN');

        // Create 2 published results with entries so recalculate retains points
        for ($i = 1; $i <= 2; $i++) {
            $prog = Program::create([
                'category_id' => $category->id,
                'name' => "Competition {$i}",
                'code' => "P-20{$i}",
                'type' => 'individual',
                'eligibility' => 'all',
                'status' => 'completed',
            ]);

            $entry = $this->createEntry($prog, $studentA, "CH-20{$i}");

            Result::create([
                'program_id' => $prog->id,
                'first_entry_id' => $entry->id,
                'status' => 'published',
                'published_at' => now(),
            ]);
        }

        // Trigger milestone check
        $milestoneApplied = Group::checkAndApplyMilestone();

        $this->assertTrue($milestoneApplied);

        $announcement = StandingAnnouncement::where('results_count', 2)->first();
        $this->assertNotNull($announcement);
        $this->assertEquals('ready', $announcement->status);

        // Announcer announces it on stage
        $announcement->markAsAnnounced();
        $this->assertEquals('announced', $announcement->status);

        // Media panel publishes it publicly
        $announcement->publishPublicly();
        $this->assertEquals('published', $announcement->status);

        $this->groupA->refresh();
        $this->assertGreaterThan(0, $this->groupA->public_points_cache);
        $this->assertEquals('Standings after 2 Results', FestivalSetting::get('public_standings_label'));
    }

    public function test_admin_and_media_can_manually_publish_team_standings(): void
    {
        $category = ProgramCategory::create(['name' => 'Manual Stage', 'code' => 'MAN', 'slug' => 'man-stage']);
        $studentA = $this->createStudent($this->groupA, 'Student Manual', 'CH-MAN');

        $prog = Program::create([
            'category_id' => $category->id,
            'name' => 'Essay Writing',
            'code' => 'P-301',
            'type' => 'individual',
            'eligibility' => 'all',
            'status' => 'completed',
        ]);

        $entry = $this->createEntry($prog, $studentA, 'CH-MAN');

        Result::create([
            'program_id' => $prog->id,
            'first_entry_id' => $entry->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.points.publish-standings'), [
                'custom_label' => 'Special Committee Standings Release',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->groupA->refresh();
        $this->assertGreaterThan(0, $this->groupA->public_points_cache);
        $this->assertEquals('Special Committee Standings Release', FestivalSetting::get('public_standings_label'));

        // Also test Media portal manual release
        $mediaResponse = $this->actingAs($this->mediaUser)
            ->post(route('media.results.publish-standings'), [
                'custom_label' => 'Media Flash Standings',
            ]);

        $mediaResponse->assertRedirect();
        $this->assertEquals('Media Flash Standings', FestivalSetting::get('public_standings_label'));
    }

    public function test_admin_can_update_milestones_and_mode(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.points.update-milestones'), [
                'milestones' => '5, 10, 15, 25, 30',
                'mode' => 'manual',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('5,10,15,25,30', FestivalSetting::get('public_standings_milestones'));
        $this->assertEquals('manual', FestivalSetting::get('public_standings_mode'));
    }

    public function test_admin_sends_standings_to_announcer_then_announcer_announces_then_media_publishes(): void
    {
        $category = ProgramCategory::create(['name' => 'General Stage', 'code' => 'GEN', 'slug' => 'gen-stage']);
        $studentA = $this->createStudent($this->groupA, 'Top Champion', 'CH-CHAMP');

        $prog = Program::create([
            'category_id' => $category->id,
            'name' => 'Grand Speech',
            'code' => 'P-999',
            'type' => 'individual',
            'eligibility' => 'all',
            'status' => 'completed',
        ]);

        $entry = $this->createEntry($prog, $studentA, 'CH-CHAMP');

        Result::create([
            'program_id' => $prog->id,
            'first_entry_id' => $entry->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        // 1. Admin sends standings to Announcer Desk
        $adminResponse = $this->actingAs($this->admin)
            ->post(route('admin.points.send-to-announcer'), [
                'custom_label' => 'Standings after 10 Results',
            ]);

        $adminResponse->assertRedirect();
        $adminResponse->assertSessionHas('success');

        $announcement = StandingAnnouncement::latest()->first();
        $this->assertNotNull($announcement);
        $this->assertEquals('ready', $announcement->status);
        $this->assertEquals('Standings after 10 Results', $announcement->title);
        $this->assertNotEmpty($announcement->script_text);

        // 2. Announcer views and marks it as announced on stage
        $announcerConsole = $this->actingAs($this->announcer)->get(route('announcer.index'));
        $announcerConsole->assertOk();
        $announcerConsole->assertSee('Standings after 10 Results');

        $announceResponse = $this->actingAs($this->announcer)
            ->post(route('announcer.standings.announced', $announcement));

        $announceResponse->assertRedirect();
        $announceResponse->assertSessionHas('success');

        $announcement->refresh();
        $this->assertEquals('announced', $announcement->status);
        $this->assertNotNull($announcement->announced_at);

        // 3. Media desk receives announced standings and publishes to website
        $mediaIndex = $this->actingAs($this->mediaUser)->get(route('media.results.index'));
        $mediaIndex->assertOk();
        $mediaIndex->assertSee('Standings after 10 Results');

        $mediaPublishResponse = $this->actingAs($this->mediaUser)
            ->post(route('media.standings.publish', $announcement));

        $mediaPublishResponse->assertRedirect();
        $mediaPublishResponse->assertSessionHas('success');

        $announcement->refresh();
        $this->assertEquals('published', $announcement->status);
        $this->assertNotNull($announcement->published_at);

        // 4. Verify public website and API now reflect the published standings
        $this->assertEquals('Standings after 10 Results', FestivalSetting::get('public_standings_label'));

        $this->groupA->refresh();
        $this->assertGreaterThan(0, $this->groupA->public_points_cache);

        $apiResponse = $this->getJson(route('api.public.standings'));
        $apiResponse->assertOk();
        $apiResponse->assertJsonFragment([
            'milestone_label' => 'Standings after 10 Results',
        ]);
    }

    public function test_media_and_announcer_portals_load_safely_even_if_no_standings_exist(): void
    {
        $mediaResponse = $this->actingAs($this->mediaUser)->get(route('media.results.index'));
        $mediaResponse->assertOk();

        $announcerResponse = $this->actingAs($this->announcer)->get(route('announcer.index'));
        $announcerResponse->assertOk();

        $adminResponse = $this->actingAs($this->admin)->get(route('admin.points.index'));
        $adminResponse->assertOk();
    }

    public function test_announcer_console_displays_grade_points_and_breakdown_and_can_update_grades(): void
    {
        $category = ProgramCategory::create(['name' => 'Speech', 'code' => 'SP', 'slug' => 'speech']);
        $prog = Program::create([
            'category_id' => $category->id,
            'name' => 'English Elocution',
            'code' => 'P-401',
            'type' => 'individual',
            'eligibility' => 'all',
            'status' => 'completed',
        ]);

        $student1 = $this->createStudent($this->groupA, 'Yaseen First', 'CH-401');
        $student2 = $this->createStudent($this->groupB, 'Rayyan Second', 'CH-402');
        $student3 = $this->createStudent($this->groupA, 'Bilal Third', 'CH-403');

        $entry1 = $this->createEntry($prog, $student1, 'CH-401');
        $entry2 = $this->createEntry($prog, $student2, 'CH-402');
        $entry3 = $this->createEntry($prog, $student3, 'CH-403');

        $result = Result::create([
            'program_id' => $prog->id,
            'first_entry_id' => $entry1->id,
            'first_grade' => 'A+',
            'second_entry_id' => $entry2->id,
            'second_grade' => 'A',
            'third_entry_id' => $entry3->id,
            'third_grade' => 'B',
            'status' => 'delivered',
        ]);

        // 1. Announcer desk displays grades, grade points, and total points
        $response = $this->actingAs($this->announcer)->get(route('announcer.index'));
        $response->assertOk();
        $response->assertSee('English Elocution');
        $response->assertSee('Yaseen First');
        $response->assertSee('Grade A+');
        $response->assertSee('+6 Grade Pts');
        $response->assertSee('+5 Pos Pts');
        $response->assertSee('11 PTS');

        $response->assertSee('Rayyan Second');
        $response->assertSee('Grade A');
        $response->assertSee('+5 Grade Pts');
        $response->assertSee('+3 Pos Pts');
        $response->assertSee('8 PTS');

        $response->assertSee('Bilal Third');
        $response->assertSee('Grade B');
        $response->assertSee('+3 Grade Pts');
        $response->assertSee('+1 Pos Pts');
        $response->assertSee('4 PTS');

        // 2. Announcer can update declared grades directly
        $updateResponse = $this->actingAs($this->announcer)
            ->post(route('announcer.update-grades', $result), [
                'first_grade' => 'A+',
                'second_grade' => 'A+',
                'third_grade' => 'A',
            ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');

        $result->refresh();
        $this->assertEquals('A+', $result->second_grade);
        $this->assertEquals('A', $result->third_grade);

        $w2 = $result->getWinnerBreakdown(2);
        $this->assertEquals('A+', $w2['grade']);
        $this->assertEquals(6, $w2['grade_points']);
        $this->assertEquals(3, $w2['position_points']);
        $this->assertEquals(9, $w2['total_points']);
    }

    public function test_media_panel_team_standings_management_and_public_ordering(): void
    {
        // 1. Media team can access Team Standings management panel
        $response = $this->actingAs($this->mediaUser)->get(route('media.standings.index'));
        $response->assertOk();
        $response->assertSee('ടീം പോയിന്റ് നില');
        $response->assertSee('House Alpha');
        $response->assertSee('House Beta');
        $response->assertDontSee('സിസ്റ്റം കാൽക്കുലേഷൻ');
        $response->assertDontSee('System Calculation');
        $response->assertDontSee('നിലവിലെ പബ്ലിക് പോയിന്റ്');

        // 2. Media team enters custom points (Beta = 25 pts, Alpha = 10 pts) and sets 5 results
        $updateResponse = $this->actingAs($this->mediaUser)
            ->post(route('media.standings.update'), [
                'results_count' => 5,
                'points' => [
                    $this->groupA->id => 10,
                    $this->groupB->id => 25,
                ],
            ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');

        // 3. Verify FestivalSetting label and count
        $this->assertEquals('5', FestivalSetting::get('public_standings_results_count'));
        $this->assertEquals('After 05 Results', FestivalSetting::get('public_standings_label'));

        // 4. Verify highest points team (House Beta, 25 pts) is assigned Rank 1
        $this->groupA->refresh();
        $this->groupB->refresh();
        $this->assertEquals(25, $this->groupB->public_points_cache);
        $this->assertEquals(1, $this->groupB->public_rank_cache);
        $this->assertEquals(10, $this->groupA->public_points_cache);
        $this->assertEquals(2, $this->groupA->public_rank_cache);

        // 5. Verify Public Home page renders 'After 05 Results', does NOT show 'Initial Standings', and places House Beta on top
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertOk();
        $homeResponse->assertDontSee('Initial Standings');
        $homeResponse->assertSee('After 05 Results');
        $homeResponse->assertSee('25');
        $homeResponse->assertSee('10');

        $content = $homeResponse->getContent();
        $posBeta = strpos($content, 'House Beta');
        $posAlpha = strpos($content, 'House Alpha');
        $this->assertNotFalse($posBeta);
        $this->assertNotFalse($posAlpha);
        $this->assertLessThan($posAlpha, $posBeta, 'The team with more points (House Beta) must appear before House Alpha in the standings!');

        // 6. Verify Public Groups page renders 'After 05 Results' and does NOT show 'Initial Standings'
        $groupsResponse = $this->get(route('groups.index'));
        $groupsResponse->assertOk();
        $groupsResponse->assertDontSee('Initial Standings');
        $groupsResponse->assertSee('After 05 Results');
    }
}
