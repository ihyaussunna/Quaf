<?php

namespace Tests\Feature;

use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalStandingsDemoPreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        FestivalSetting::set('is_final_standings_published', '0');
        FestivalSetting::set('launch_status', 'launched');
    }

    public function test_standings_demo_route_renders_final_celebration_mode(): void
    {
        Group::create([
            'name' => 'Demo Champion Team',
            'code' => 'DEMO1',
            'slug' => 'demo-champion-team',
            'display_order' => 1,
            'color' => '#10b981',
        ]);
        Group::create([
            'name' => 'Demo Runner Up Team',
            'code' => 'DEMO2',
            'slug' => 'demo-runner-up-team',
            'display_order' => 2,
            'color' => '#3b82f6',
        ]);

        $response = $this->get(route('standings.demo'));

        $response->assertStatus(200);
        $response->assertSee('DEMO PREVIEW', false);
        $response->assertSee('celebration-canvas', false);
        $response->assertSee('Demo Champion Team', false);
        $response->assertSee('QUAF 2026 OVERALL CHAMPIONS', false);
    }

    public function test_groups_query_param_demo_renders_celebration(): void
    {
        Group::create([
            'name' => 'Sample Group',
            'code' => 'SMP1',
            'slug' => 'sample-group',
            'display_order' => 1,
            'color' => '#e11d48',
        ]);

        $response = $this->get('/groups?demo=1');

        $response->assertStatus(200);
        $response->assertSee('DEMO PREVIEW', false);
        $response->assertSee('celebration-canvas', false);
    }

    public function test_final_preview_route_alias_works(): void
    {
        Group::create([
            'name' => 'Sample House',
            'code' => 'SMP2',
            'slug' => 'sample-house',
            'display_order' => 1,
            'color' => '#f59e0b',
        ]);

        $response = $this->get(route('final.preview'));

        $response->assertStatus(200);
        $response->assertSee('DEMO PREVIEW', false);
    }

    public function test_media_panel_has_demo_preview_celebration_button(): void
    {
        $user = User::factory()->create([
            'role' => 'media_team',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('media.standings.index'));

        $response->assertStatus(200);
        $response->assertSee(route('groups.demo'), false);
        $response->assertSee('Demo / Preview Celebration', false);
    }
}
