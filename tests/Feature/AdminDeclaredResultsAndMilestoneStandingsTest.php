<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use App\Services\PointCalculationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminDeclaredResultsAndMilestoneStandingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Group $groupA;

    protected Group $groupB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@quaf.test',
        ]);

        $this->groupA = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#be1e2d',
            'points_cache' => 0,
            'rank_cache' => 1,
            'public_points_cache' => 0,
            'public_rank_cache' => 1,
        ]);

        $this->groupB = Group::create([
            'name' => 'Aura Zeic',
            'code' => 'AURA',
            'slug' => 'aura-zeic',
            'color_hex' => '#009444',
            'points_cache' => 0,
            'rank_cache' => 2,
            'public_points_cache' => 0,
            'public_rank_cache' => 2,
        ]);
    }

    protected function createProgramWithResult(string $code, string $name, Group $winnerGroup, Carbon $publishedAt, string $status = 'send'): Result
    {
        $program = Program::create([
            'name' => $name,
            'code' => $code,
            'type' => 'individual',
            'status' => 'completed',
        ]);

        $student = Student::create([
            'student_id' => 'STU-'.Str::upper(Str::random(6)),
            'group_id' => $winnerGroup->id,
            'name' => 'Winner '.$code,
            'qr_token' => Str::random(32),
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'group_id' => $winnerGroup->id,
            'chest_number' => 'C-'.rand(100, 999),
            'status' => 'verified',
        ]);

        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entry->id,
            'first_grade' => 'A',
            'status' => $status,
            'published_at' => $publishedAt,
        ]);

        return $result;
    }

    public function test_send_to_announcer_immediately_recalculates_and_updates_admin_team_standings(): void
    {
        // Setup a submitted result that has not yet been sent to the announcer
        $result = $this->createProgramWithResult('PRG01', 'Test Elocution', $this->groupA, now(), 'submitted');

        $this->assertEquals(0, $this->groupA->fresh()->points_cache);

        // Admin sends result to announcer
        $response = $this->actingAs($this->admin)->post(route('admin.results.send-to-announcer', $result));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $result->refresh();
        $this->assertEquals('send', $result->status);
        $this->assertNotNull($result->published_at);

        // Group A's points_cache in admin panel must immediately be updated (10 points: 5 pos + 5 grade)
        $this->groupA->refresh();
        $this->assertEquals(10, $this->groupA->points_cache);
    }

    public function test_batch_send_to_announcer_recalculates_admin_team_standings(): void
    {
        $res1 = $this->createProgramWithResult('PRG02', 'Test Essay', $this->groupA, now(), 'submitted');
        $res2 = $this->createProgramWithResult('PRG03', 'Test Recitation', $this->groupB, now(), 'submitted');

        $this->assertEquals(0, $this->groupA->fresh()->points_cache);
        $this->assertEquals(0, $this->groupB->fresh()->points_cache);

        $response = $this->actingAs($this->admin)->post(route('admin.results.batch-send-announcer'), [
            'result_ids' => [$res1->id, $res2->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('send', $res1->fresh()->status);
        $this->assertEquals('send', $res2->fresh()->status);

        // Both teams must have updated points immediately
        $this->assertEquals(10, $this->groupA->fresh()->points_cache);
        $this->assertEquals(10, $this->groupB->fresh()->points_cache);
    }

    public function test_get_standings_after_results_calculates_exact_milestone_points(): void
    {
        $service = app(PointCalculationService::class);

        // Create 6 declared results at 5-minute intervals
        // Results 1 to 3 won by Group A (3 * 10 = 30 pts)
        // Results 4 to 5 won by Group B (2 * 10 = 20 pts)
        // Result 6 won by Group B (+10 pts = 30 pts for B)
        $baseTime = Carbon::now()->subHours(2);

        $this->createProgramWithResult('R1', 'Prog 1', $this->groupA, $baseTime->copy()->addMinutes(5), 'send');
        $this->createProgramWithResult('R2', 'Prog 2', $this->groupA, $baseTime->copy()->addMinutes(10), 'send');
        $this->createProgramWithResult('R3', 'Prog 3', $this->groupA, $baseTime->copy()->addMinutes(15), 'announced');
        $this->createProgramWithResult('R4', 'Prog 4', $this->groupB, $baseTime->copy()->addMinutes(20), 'published');
        $this->createProgramWithResult('R5', 'Prog 5', $this->groupB, $baseTime->copy()->addMinutes(25), 'send');
        $this->createProgramWithResult('R6', 'Prog 6', $this->groupB, $baseTime->copy()->addMinutes(30), 'send');

        // Test Milestone after 5 results:
        $milestone5 = $service->getStandingsAfterResults(5);

        $this->assertEquals(5, $milestone5['limit']);
        $this->assertEquals(5, $milestone5['total_results']);
        $this->assertCount(2, $milestone5['standings']);

        // Group A must be Rank 1 with 30 points
        $rank1 = $milestone5['standings'][0];
        $this->assertEquals($this->groupA->id, $rank1['group']->id);
        $this->assertEquals(30, $rank1['points']);
        $this->assertEquals(1, $rank1['rank']);

        // Group B must be Rank 2 with 20 points
        $rank2 = $milestone5['standings'][1];
        $this->assertEquals($this->groupB->id, $rank2['group']->id);
        $this->assertEquals(20, $rank2['points']);
        $this->assertEquals(2, $rank2['rank']);

        // Test All declared results (limit 0 or 6):
        $allDeclared = $service->getStandingsAfterResults(0);
        $this->assertEquals(6, $allDeclared['total_results']);

        $allTeamsById = collect($allDeclared['standings'])->keyBy('group.id');
        $this->assertEquals(30, $allTeamsById[$this->groupA->id]['points']);
        $this->assertEquals(30, $allTeamsById[$this->groupB->id]['points']);
    }

    public function test_admin_print_declared_results_pdf_view_renders_successfully(): void
    {
        $this->createProgramWithResult('P1', 'Elocution', $this->groupA, now(), 'send');
        $this->createProgramWithResult('P2', 'Essay', $this->groupB, now(), 'published');

        $response = $this->actingAs($this->admin)->get(route('admin.print.results'));

        $response->assertOk();
        $response->assertSee('Official Festival Results');
        $response->assertSee('Elocution');
        $response->assertSee('Essay');

        // Test status filtered view
        $filteredResponse = $this->actingAs($this->admin)->get(route('admin.print.results', ['status' => 'published']));
        $filteredResponse->assertOk();
        $filteredResponse->assertSee('Essay');
    }

    public function test_admin_milestone_standings_print_view_renders_successfully(): void
    {
        $this->createProgramWithResult('M1', 'Competition One', $this->groupA, now(), 'send');
        $this->createProgramWithResult('M2', 'Competition Two', $this->groupB, now(), 'announced');

        // Test with count=5 (with 2 declared, effectively After 02 Results)
        $response = $this->actingAs($this->admin)->get(route('admin.print.milestone-standings', ['count' => 5]));

        $response->assertOk();
        $response->assertSee('OFFICIAL TEAM STANDINGS');
        $response->assertSee('After 02 Results');
        $response->assertSee('Lumo Fikric');
        $response->assertSee('Aura Zeic');
        $response->assertSee('Competition One');

        // Test alias route admin.standings.milestone
        $aliasResponse = $this->actingAs($this->admin)->get(route('admin.standings.milestone', ['count' => 10]));
        $aliasResponse->assertOk();
    }
}
