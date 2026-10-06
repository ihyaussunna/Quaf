<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminZoneTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@quaf.fest',
        ]);
    }

    public function test_admin_can_view_zones_index(): void
    {
        $category = ProgramCategory::create([
            'name' => 'General',
            'slug' => 'general',
        ]);

        $group = Group::create([
            'name' => 'Team Alpha',
            'slug' => 'team-alpha',
            'code' => 'T01',
        ]);

        Program::create([
            'name' => 'Qiraath Competition',
            'code' => 'P101',
            'type' => 'individual',
            'category_id' => $category->id,
            'eligibility' => 'A Zone',
            'status' => 'upcoming',
        ]);

        Student::create([
            'student_id' => 'ST101',
            'name' => 'Test Student A',
            'category' => 'A Zone',
            'class_level' => 'TQS',
            'group_id' => $group->id,
            'points_cache' => 15,
            'qr_token' => Str::random(32),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.zones.index'));

        $response->assertOk();
        $response->assertSee('Zones Overview');
        $response->assertSee('A Zone');
        $response->assertSee('B Zone');
        $response->assertSee('C Zone');
        $response->assertSee('Mix Zone');
        $response->assertSee('Qiraath Competition');
    }

    public function test_admin_can_filter_zones_and_tabs(): void
    {
        $responseStudents = $this->actingAs($this->admin)->get(route('admin.zones.index', [
            'zone' => 'B Zone',
            'tab' => 'students',
        ]));

        $responseStudents->assertOk();
        $responseStudents->assertSee('B Zone');

        $responsePrograms = $this->actingAs($this->admin)->get(route('admin.zones.index', [
            'zone' => 'C Zone',
            'tab' => 'programs',
        ]));

        $responsePrograms->assertOk();
        $responsePrograms->assertSee('C Zone');
    }

    public function test_public_home_renders_successfully(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }
}
