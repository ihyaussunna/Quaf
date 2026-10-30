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
}
