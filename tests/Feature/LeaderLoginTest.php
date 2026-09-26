<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LeaderLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed leader user and group
        $leader = User::create([
            'name' => 'WARIS ADANY (Leader - LUMO FIKRIC)',
            'email' => 'leader.lumo@quaf.fest',
            'password' => Hash::make('Lumo#9482@FikricFest!26'),
            'role' => 'group_leader',
            'is_active' => true,
        ]);

        Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#56286b',
            'leader_id' => $leader->id,
            'manager_name' => 'WARIS ADANY',
            'admin_password' => 'Lumo#9482@FikricFest!26',
        ]);
    }

    public function test_leader_can_login_with_email_and_redirects_to_leader_panel(): void
    {
        $response = $this->post(route('login'), [
            'username' => 'leader.lumo@quaf.fest',
            'password' => 'Lumo#9482@FikricFest!26',
        ]);

        $response->assertRedirect(route('leader.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_leader_can_login_with_short_code_or_team_name_and_redirects_to_leader_panel(): void
    {
        $response = $this->post(route('login'), [
            'username' => 'LUMO',
            'password' => 'Lumo#9482@FikricFest!26',
        ]);

        $response->assertRedirect(route('leader.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_leader_can_login_with_leader_name_and_redirects_to_leader_panel(): void
    {
        $response = $this->post(route('login'), [
            'username' => 'WARIS ADANY',
            'password' => 'Lumo#9482@FikricFest!26',
        ]);

        $response->assertRedirect(route('leader.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_leader_login_clears_stray_admin_intended_url_and_goes_to_leader_panel(): void
    {
        // Simulate previous guest attempt to access /admin
        session(['url.intended' => 'http://127.0.0.1:8000/admin']);

        $response = $this->post(route('login'), [
            'username' => 'leader.lumo@quaf.fest',
            'password' => 'Lumo#9482@FikricFest!26',
        ]);

        $response->assertRedirect(route('leader.dashboard'));
        $this->assertAuthenticated();
    }
}
