<?php

namespace Tests\Feature;

use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalStandingsPublishAndCelebrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $mediaUser;

    protected Group $groupA;

    protected Group $groupB;

    protected Group $groupC;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mediaUser = User::factory()->create([
            'role' => 'media_team',
            'name' => 'Media Desk Lead',
            'email' => 'media@quaf.test',
        ]);

        $this->groupA = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LF',
            'slug' => 'lumo-fikric',
            'color_hex' => '#be1e2d',
            'points_cache' => 350,
            'rank_cache' => 1,
            'public_points_cache' => 350,
            'public_rank_cache' => 1,
        ]);

        $this->groupB = Group::create([
            'name' => 'Pacto Hikmic',
            'code' => 'PH',
            'slug' => 'pacto-hikmic',
            'color_hex' => '#10b981',
            'points_cache' => 310,
            'rank_cache' => 2,
            'public_points_cache' => 310,
            'public_rank_cache' => 2,
        ]);

        $this->groupC = Group::create([
            'name' => 'Conco Majdic',
            'code' => 'CM',
            'slug' => 'conco-majdic',
            'color_hex' => '#6366f1',
            'points_cache' => 280,
            'rank_cache' => 3,
            'public_points_cache' => 280,
            'public_rank_cache' => 3,
        ]);

        FestivalSetting::set('is_final_standings_reset_v2', '1');
        FestivalSetting::set('is_final_standings_published', '0');
        FestivalSetting::set('public_standings_results_count', '15');
        FestivalSetting::set('public_standings_label', 'After 15 Results');
    }

    public function test_media_team_can_publish_final_championship_standings(): void
    {
        $response = $this->actingAs($this->mediaUser)
            ->post(route('media.standings.publish-final'), [
                'results_count' => 50,
                'points' => [
                    $this->groupA->id => 450,
                    $this->groupB->id => 410,
                    $this->groupC->id => 380,
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('1', FestivalSetting::get('is_final_standings_published'));
        $this->assertEquals('Final Result • Official Champions', FestivalSetting::get('public_standings_label'));

        $this->assertEquals(450, $this->groupA->fresh()->public_points_cache);
        $this->assertEquals(1, $this->groupA->fresh()->public_rank_cache);
        $this->assertEquals(410, $this->groupB->fresh()->public_points_cache);
        $this->assertEquals(2, $this->groupB->fresh()->public_rank_cache);
        $this->assertEquals(380, $this->groupC->fresh()->public_points_cache);
        $this->assertEquals(3, $this->groupC->fresh()->public_rank_cache);
    }

    public function test_public_groups_page_triggers_celebration_when_final_standings_are_published(): void
    {
        FestivalSetting::set('is_final_standings_published', '1');
        FestivalSetting::set('public_standings_label', 'Final Result • Official Champions');

        $this->groupA->update(['public_points_cache' => 500, 'public_rank_cache' => 1]);
        $this->groupB->update(['public_points_cache' => 450, 'public_rank_cache' => 2]);
        $this->groupC->update(['public_points_cache' => 400, 'public_rank_cache' => 3]);

        $response = $this->get(route('groups.index'));

        $response->assertStatus(200);

        // Header celebration badge
        $response->assertSee('FINAL OFFICIAL RESULT', false);

        // Canvas confetti fireworks
        $response->assertSee('id="celebration-canvas"', false);
        $response->assertSee('launchConfettiCannon', false);
        $response->assertSee('CELEBRATE', false);

        // Grand Championship Victory Podium
        $response->assertSee('OVERALL CHAMPION');
        $response->assertSee('Lumo Fikric');
        $response->assertSee('500');
        $response->assertSee('SECOND PLACE');
        $response->assertSee('Pacto Hikmic');
        $response->assertSee('THIRD PLACE');
        $response->assertSee('Conco Majdic');

        // Leaderboard rows honors
        $response->assertSee('WINNER');
    }

    public function test_public_home_page_shows_final_champions_badge_when_published(): void
    {
        FestivalSetting::set('is_final_standings_published', '1');
        FestivalSetting::set('public_standings_label', 'Final Result • Official Champions');

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('FINAL CHAMPIONS');
        $response->assertSee('WINNER');
    }

    public function test_media_team_can_publish_final_via_standings_update_checkbox(): void
    {
        $response = $this->actingAs($this->mediaUser)
            ->post(route('media.standings.update'), [
                'results_count' => 30,
                'is_final' => '1',
                'points' => [
                    $this->groupA->id => 390,
                    $this->groupB->id => 350,
                    $this->groupC->id => 320,
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('1', FestivalSetting::get('is_final_standings_published'));
        $this->assertEquals('Final Result • Official Champions', FestivalSetting::get('public_standings_label'));
    }

    public function test_media_team_can_revert_final_status_back_to_regular_standings(): void
    {
        FestivalSetting::set('is_final_standings_published', '1');
        FestivalSetting::set('public_standings_results_count', '25');
        FestivalSetting::set('public_standings_label', 'Final Result • Official Champions');

        $response = $this->actingAs($this->mediaUser)
            ->post(route('media.standings.revert-final'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('0', FestivalSetting::get('is_final_standings_published'));
        $this->assertEquals('After 25 Results', FestivalSetting::get('public_standings_label'));

        // Public groups page should now be in regular mode without celebration banner
        $publicResponse = $this->get(route('groups.index'));
        $publicResponse->assertStatus(200);
        $publicResponse->assertDontSee('id="celebration-canvas"', false);
        $publicResponse->assertDontSee('QUAF 2026 OVERALL CHAMPIONS');
        $publicResponse->assertSee('After 25 Results');
    }

    public function test_media_results_index_displays_final_championship_callout(): void
    {
        FestivalSetting::set('is_final_standings_published', '1');

        $response = $this->actingAs($this->mediaUser)
            ->get(route('media.results.index'));

        $response->assertStatus(200);
        $response->assertSee('FINAL CHAMPIONSHIP PUBLISHED');
        $response->assertSee('Celebration Active on Public Website');
    }
}
