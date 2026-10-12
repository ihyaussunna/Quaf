<?php

namespace Tests\Feature;

use Tests\TestCase;

class LaunchPageWhiteDesignTest extends TestCase
{
    public function test_launching_page_renders_with_white_theme_and_new_logo(): void
    {
        $response = $this->get('/launching');

        $response->assertStatus(200);

        // Assert staff portal button is removed
        $response->assertDontSee('Staff Portal');

        // Assert old logos are removed
        $response->assertDontSee('adabic-inheritance-web.svg');
        $response->assertDontSee('quaf-logo-hero.svg');

        // Assert new logo is present
        $response->assertSee('quaf-launch-logo.svg');

        // Assert white theme and elements
        $response->assertSee('bg-white');
        $response->assertSee('IHYAUSSUNNA STUDENTS UNION');
        $response->assertSee('QUAF 2026');
        $response->assertSee('WEBSITE LAUNCHING');
        $response->assertSee('LAUNCH WEBSITE');
    }

    public function test_launch_route_also_renders_clean_white_design(): void
    {
        $response = $this->get('/launch');

        $response->assertStatus(200);
        $response->assertDontSee('Staff Portal');
        $response->assertSee('quaf-launch-logo.svg');
    }
}
