<?php

namespace Tests\Feature;

use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\User;
use App\Services\StandingPosterGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualFundPointStandingPosterTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $collectorUser;

    protected Group $group1;

    protected Group $group2;

    protected Group $group3;

    protected Group $group4;

    protected Group $group5;

    protected function setUp(): void
    {
        parent::setUp();

        FestivalSetting::ensureSchema();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->collectorUser = User::factory()->create([
            'role' => 'fund_collector',
        ]);

        $this->group1 = Group::create([
            'name' => 'Conco Majdic',
            'code' => 'CONCO',
            'slug' => 'conco-majdic',
            'color_hex' => '#eab308',
        ]);

        $this->group2 = Group::create([
            'name' => 'Unio Hilmic',
            'code' => 'UNIO',
            'slug' => 'unio-hilmic',
            'color_hex' => '#94a3b8',
        ]);

        $this->group3 = Group::create([
            'name' => 'Pacto Hikmic',
            'code' => 'PACTO',
            'slug' => 'pacto-hikmic',
            'color_hex' => '#d97706',
        ]);

        $this->group4 = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#6366f1',
        ]);

        $this->group5 = Group::create([
            'name' => 'Yugo Rushdic',
            'code' => 'YUGO',
            'slug' => 'yugo-rushdic',
            'color_hex' => '#ec4899',
        ]);
    }

    public function test_fund_ledger_displays_manual_point_standing_section(): void
    {
        // Set manual amounts: Conco = 71,825 (35 pts), Unio = 69,865 (32 pts), Pacto = 46,244 (22 pts), Lumo = 32,514 (16 pts), Yugo = 31,925 (16 pts)
        FestivalSetting::set('manual_fund_standings', json_encode([
            $this->group1->id => 71825,
            $this->group2->id => 69865,
            $this->group3->id => 46244,
            $this->group4->id => 32514,
            $this->group5->id => 31925,
        ]));

        $response = $this->actingAs($this->collectorUser)->get(route('fund.ledger'));

        $response->assertOk();
        $response->assertSee('Manual Point Standings');
        $response->assertSee('Team Point Standing Poster Generator');
        $response->assertSee('Enter Manual Amount For Each Team');
        $response->assertSee('Save Amounts');
        $response->assertSee('Preview Poster');
        $response->assertSee('Download Standing Poster');

        // Check points calculation in the view
        $response->assertSee('35 PTS');
        $response->assertSee('32 PTS');
        $response->assertSee('22 PTS');
        $response->assertSee('16 PTS');
    }

    public function test_manual_standing_calculation_helper_ranks_teams_accurately(): void
    {
        $customAmounts = [
            $this->group1->id => 71825, // 35 pts
            $this->group2->id => 69865, // 32 pts
            $this->group3->id => 46244, // 22 pts
            $this->group4->id => 32514, // 16 pts
            $this->group5->id => 31925, // 16 pts
        ];

        $standings = StandingPosterGenerator::getManualStandings($customAmounts);

        $this->assertCount(5, $standings);
        $this->assertEquals($this->group1->id, $standings[0]->id);
        $this->assertEquals(35, $standings[0]->points);

        $this->assertEquals($this->group2->id, $standings[1]->id);
        $this->assertEquals(32, $standings[1]->points);

        $this->assertEquals($this->group3->id, $standings[2]->id);
        $this->assertEquals(22, $standings[2]->points);

        $this->assertEquals($this->group4->id, $standings[3]->id);
        $this->assertEquals(16, $standings[3]->points);

        $this->assertEquals($this->group5->id, $standings[4]->id);
        $this->assertEquals(16, $standings[4]->points);
    }

    public function test_manual_standing_poster_streams_inline_image(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('fund.standings.manual.poster', [
            'amounts' => [
                $this->group1->id => 75000,
                $this->group2->id => 65000,
                $this->group3->id => 45000,
                $this->group4->id => 35000,
                $this->group5->id => 25000,
            ],
            'show_amount' => 0,
        ]));

        $response->assertOk();
        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('QUAF-09-Team-Point-Standings.jpg', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_manual_standing_poster_downloads_attachment_image(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('fund.standings.manual.download', [
            'amounts' => [
                $this->group1->id => 75000,
                $this->group2->id => 65000,
            ],
            'show_amount' => 0,
        ]));

        $response->assertOk();
        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_save_manual_standing_amounts_persists_settings(): void
    {
        $amounts = [
            $this->group1->id => 85000,
            $this->group2->id => 62000,
            $this->group3->id => 51000,
            $this->group4->id => 34000,
            $this->group5->id => 12000,
        ];

        $response = $this->actingAs($this->adminUser)->postJson(route('fund.standings.manual.save'), [
            'amounts' => $amounts,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $saved = FestivalSetting::get('manual_fund_standings');
        $this->assertNotNull($saved);
        $decoded = json_decode($saved, true);
        $this->assertEquals(85000, $decoded[$this->group1->id]);
        $this->assertEquals(62000, $decoded[$this->group2->id]);
    }
}
