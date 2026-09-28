<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminGroupCreateTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin_group_test@quaf.fest',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_group_without_specifying_code_or_color(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.groups.store'), [
                'name' => 'Al-Hikmah Team',
                'manager_name' => 'Ahmad Manager',
                'manager_contact' => '9876543210',
            ]);

        $response->assertRedirect(route('admin.groups.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('groups', [
            'name' => 'Al-Hikmah Team',
            'manager_name' => 'Ahmad Manager',
            'manager_contact' => '9876543210',
        ]);

        $group = Group::where('name', 'Al-Hikmah Team')->first();
        $this->assertNotNull($group);
        $this->assertNotEmpty($group->code);
        $this->assertNotEmpty($group->color_hex);
        $this->assertNotEmpty($group->slug);
    }

    public function test_admin_can_create_group_with_custom_code_and_color(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.groups.store'), [
                'name' => 'Custom Team',
                'code' => 'CUST01',
                'color_hex' => '#FF5733',
            ]);

        $response->assertRedirect(route('admin.groups.index'));

        $this->assertDatabaseHas('groups', [
            'name' => 'Custom Team',
            'code' => 'CUST01',
            'color_hex' => '#FF5733',
        ]);
    }

    public function test_duplicate_name_auto_generates_unique_code_and_slug(): void
    {
        $group1 = Group::create([
            'name' => 'Alpha Team',
            'code' => 'ALPHA',
            'slug' => 'alpha-team',
            'color_hex' => '#2E3192',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.groups.store'), [
                'name' => 'Alpha Team',
            ]);

        $response->assertRedirect(route('admin.groups.index'));

        $this->assertEquals(2, Group::where('name', 'Alpha Team')->count());
        $newGroup = Group::where('id', '!=', $group1->id)->where('name', 'Alpha Team')->first();
        $this->assertNotNull($newGroup);
        $this->assertNotEquals($group1->code, $newGroup->code);
        $this->assertNotEquals($group1->slug, $newGroup->slug);
    }
}
