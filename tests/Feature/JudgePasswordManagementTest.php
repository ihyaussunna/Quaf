<?php

namespace Tests\Feature;

use App\Models\Judge;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JudgePasswordManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Central Admin',
            'email' => 'admin@quaf.fest',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_judge_with_custom_password(): void
    {
        $program = Program::create([
            'name' => 'Elocution Arabic',
            'code' => 'ELO-AR-01',
            'category' => 'stage',
            'type' => 'individual',
            'eligibility' => 'Senior',
            'points_first' => 10,
            'points_second' => 7,
            'points_third' => 5,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.judges.store'), [
            'name' => 'Dr. Khalid Al-Amoudi',
            'email' => 'khalid@quaf.fest',
            'password' => 'JudgePass#2026',
            'access_code' => '7841',
            'designation' => 'Senior Professor',
            'specialization' => 'Arabic Literature',
            'contact' => '9876543210',
            'program_ids' => [$program->id],
        ]);

        $response->assertRedirect(route('admin.judges.index'));
        $response->assertSessionHas('success');

        $judge = Judge::where('name', 'Dr. Khalid Al-Amoudi')->first();
        $this->assertNotNull($judge);
        $this->assertEquals('7841', $judge->access_code);
        $this->assertNotNull($judge->user);
        $this->assertEquals('khalid@quaf.fest', $judge->user->email);
        $this->assertEquals('JudgePass#2026', $judge->user->plain_password);
        $this->assertTrue(Hash::check('JudgePass#2026', $judge->user->password));
        $this->assertTrue($judge->programs->contains($program->id));
    }

    public function test_admin_can_create_judge_with_auto_generated_password_and_pin(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.judges.store'), [
            'name' => 'Usthad Ibrahim',
            'designation' => 'Vocal Juror',
        ]);

        $response->assertRedirect(route('admin.judges.index'));

        $judge = Judge::where('name', 'Usthad Ibrahim')->first();
        $this->assertNotNull($judge);
        $this->assertNotEmpty($judge->access_code);
        $this->assertEquals(4, strlen($judge->access_code));
        $this->assertNotNull($judge->user);
        $this->assertNotEmpty($judge->user->plain_password);
        $this->assertStringStartsWith('Judge@', $judge->user->plain_password);
        $this->assertTrue(Hash::check($judge->user->plain_password, $judge->user->password));
    }

    public function test_admin_can_update_judge_password_and_email(): void
    {
        $user = User::create([
            'name' => 'Original Judge',
            'email' => 'original.judge@quaf.fest',
            'password' => Hash::make('OldPass#123'),
            'plain_password' => 'OldPass#123',
            'role' => 'judge',
            'is_active' => true,
        ]);

        $judge = Judge::create([
            'user_id' => $user->id,
            'name' => 'Original Judge',
            'access_code' => '4412',
            'designation' => 'Judge',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.judges.update', $judge), [
            'name' => 'Updated Judge Name',
            'email' => 'updated.judge@quaf.fest',
            'password' => 'NewSecurePassword#999',
            'access_code' => '8821',
        ]);

        $response->assertRedirect(route('admin.judges.index'));

        $user->refresh();
        $judge->refresh();

        $this->assertEquals('Updated Judge Name', $judge->name);
        $this->assertEquals('8821', $judge->access_code);
        $this->assertEquals('updated.judge@quaf.fest', $user->email);
        $this->assertEquals('NewSecurePassword#999', $user->plain_password);
        $this->assertTrue(Hash::check('NewSecurePassword#999', $user->password));
    }

    public function test_admin_can_update_judge_password_via_dedicated_endpoint(): void
    {
        $user = User::create([
            'name' => 'Quick Reset Judge',
            'email' => 'quick.judge@quaf.fest',
            'password' => Hash::make('BeforeReset#1'),
            'plain_password' => 'BeforeReset#1',
            'role' => 'judge',
            'is_active' => true,
        ]);

        $judge = Judge::create([
            'user_id' => $user->id,
            'name' => 'Quick Reset Judge',
            'access_code' => '6523',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.judges.update-password', $judge), [
            'password' => 'QuickNewPass#777',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('QuickNewPass#777', $user->plain_password);
        $this->assertTrue(Hash::check('QuickNewPass#777', $user->password));
    }

    public function test_judge_can_login_with_email_and_password_on_main_login(): void
    {
        $user = User::create([
            'name' => 'Login Test Judge',
            'email' => 'testjudge@quaf.fest',
            'password' => Hash::make('JudgePassword@2026'),
            'plain_password' => 'JudgePassword@2026',
            'role' => 'judge',
            'is_active' => true,
        ]);

        Judge::create([
            'user_id' => $user->id,
            'name' => 'Login Test Judge',
            'access_code' => '3391',
        ]);

        $response = $this->post(route('login'), [
            'username' => 'testjudge@quaf.fest',
            'password' => 'JudgePassword@2026',
        ]);

        $response->assertRedirect(route('judge.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_judge_can_login_with_email_and_access_pin_on_main_login(): void
    {
        $user = User::create([
            'name' => 'Pin Login Test Judge',
            'email' => 'pintestjudge@quaf.fest',
            'password' => Hash::make('SomeComplexPass#4321'),
            'plain_password' => 'SomeComplexPass#4321',
            'role' => 'judge',
            'is_active' => true,
        ]);

        Judge::create([
            'user_id' => $user->id,
            'name' => 'Pin Login Test Judge',
            'access_code' => '9412',
        ]);

        $response = $this->post(route('login'), [
            'username' => 'pintestjudge@quaf.fest',
            'password' => '9412',
        ]);

        $response->assertRedirect(route('judge.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_judge_can_login_with_access_pin_on_judge_pin_login_page(): void
    {
        $user = User::create([
            'name' => 'Direct Pin Judge',
            'email' => 'directpin@quaf.fest',
            'password' => Hash::make('SecretPass'),
            'plain_password' => 'SecretPass',
            'role' => 'judge',
            'is_active' => true,
        ]);

        Judge::create([
            'user_id' => $user->id,
            'name' => 'Direct Pin Judge',
            'access_code' => '7283',
        ]);

        $response = $this->post(route('judge.login.submit'), [
            'pin' => '7283',
        ]);

        $response->assertRedirect(route('judge.dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
