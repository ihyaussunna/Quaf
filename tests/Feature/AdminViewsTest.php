<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Stage;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminViewsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@sahityotsav.com',
        ]);

        $stage = Stage::create([
            'name' => 'Main Stage',
            'code' => 'STG01',
            'location' => 'Auditorium A',
            'capacity' => 200,
        ]);

        $category = ProgramCategory::create([
            'name' => 'General',
            'slug' => 'general',
        ]);

        $group = Group::create([
            'name' => 'Team A',
            'slug' => 'team-a',
            'code' => 'T01',
            'manager_name' => 'Manager 1',
            'contact_number' => '9876543210',
        ]);

        $program = Program::create([
            'name' => 'ARABANA (10 MEM)',
            'malayalam_name' => 'അറബന (10 പേർ)',
            'code' => 'P101',
            'type' => 'group',
            'category_id' => $category->id,
            'stage_id' => $stage->id,
            'status' => 'upcoming',
        ]);

        $student = Student::create([
            'student_id' => 'ST-1001',
            'name' => 'Muhammed Nihal',
            'group_id' => $group->id,
            'category' => 'General',
            'gender' => 'Male',
            'qr_token' => Str::random(32),
        ]);

        $judge = Judge::create([
            'name' => 'Shamsudheen',
            'phone' => '9876543211',
            'notes' => 'Experienced judge',
        ]);
    }

    public function test_admin_pages_render_successfully(): void
    {
        $routes = [
            'admin',
            'admin/groups',
            'admin/zones',
            'admin/programs',
            'admin/students',
            'admin/judges',
            'admin/stages',
            'admin/schedules',
            'admin/mark-entry',
            'admin/results',
            'admin/templates',
            'admin/exports',
            'admin/top-scorers',
            'admin/website-builder',
            'admin/settings',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->admin)->get($route);
            $this->assertEquals(200, $response->getStatusCode(), "Route [{$route}] failed to return 200 OK, got ".$response->getStatusCode());
        }
    }

    public function test_admin_can_edit_and_update_student_details(): void
    {
        $group = Group::first();
        $student = Student::create([
            'student_id' => 'QUAF-TEST-01',
            'name' => 'Original Name',
            'group_id' => $group->id,
            'category' => 'A Zone',
            'class_level' => 'Year 4',
            'gender' => 'Male',
            'contact' => '+91 99999 11111',
            'qr_token' => Str::random(32),
        ]);

        $editView = $this->actingAs($this->admin)->get(route('admin.students.edit', $student));
        $editView->assertStatus(200);
        $editView->assertSee('Edit Participant: Original Name');

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.students.update', $student), [
            'name' => 'Updated Participant Name',
            'student_id' => 'QUAF-TEST-01-MOD',
            'group_id' => $group->id,
            'category' => 'B Zone',
            'class_level' => 'Year 3',
            'gender' => 'Male',
            'contact' => '+91 99999 22222',
        ]);

        $updateResponse->assertRedirect(route('admin.students.index'));
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Updated Participant Name',
            'student_id' => 'QUAF-TEST-01-MOD',
            'category' => 'B Zone',
        ]);
    }

    public function test_admin_can_edit_and_update_program_details(): void
    {
        $program = Program::first();

        $editView = $this->actingAs($this->admin)->get(route('admin.programs.edit', $program));
        $editView->assertStatus(200);
        $editView->assertSee('Edit Program: '.$program->name);

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.programs.update', $program), [
            'name' => 'Updated Program Title',
            'malayalam_name' => 'പുതുക്കിയ മലയാളം പേര്',
            'code' => $program->code,
            'category_id' => $program->category_id,
            'type' => 'individual',
            'eligibility' => 'Mix Zone',
            'duration_minutes' => 45,
            'points_weight' => 2.0,
            'status' => 'upcoming',
            'is_stage' => 1,
            'rules' => 'Updated evaluation guidelines and criteria.',
        ]);

        $updateResponse->assertRedirect(route('admin.programs.index'));
        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'name' => 'Updated Program Title',
            'malayalam_name' => 'പുതുക്കിയ മലയാളം പേര്',
            'eligibility' => 'Mix Zone',
            'duration_minutes' => 45,
        ]);
    }

    public function test_admin_can_edit_and_update_group_details(): void
    {
        $group = Group::first();

        $editView = $this->actingAs($this->admin)->get(route('admin.groups.edit', $group));
        $editView->assertStatus(200);
        $editView->assertSee('Edit House: '.$group->name);

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.groups.update', $group), [
            'name' => 'PACTO HIKMIC UPDATED',
            'code' => 'PACTO-MOD',
            'color_hex' => '#be1e2d',
            'manager_name' => 'Lead Coordinator',
            'manager_contact' => '+91 98470 99999',
            'name_in_results' => 'PACTO HIKMIC (MAIN)',
        ]);

        $updateResponse->assertRedirect(route('admin.groups.index'));
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'PACTO HIKMIC UPDATED',
            'code' => 'PACTO-MOD',
            'manager_name' => 'Lead Coordinator',
            'name_in_results' => 'PACTO HIKMIC (MAIN)',
        ]);
    }

    public function test_admin_print_and_pdf_routes_render_successfully(): void
    {
        $printRoutes = [
            route('admin.print.results'),
            route('admin.print.students'),
            route('admin.print.programs'),
        ];

        foreach ($printRoutes as $pRoute) {
            $response = $this->actingAs($this->admin)->get($pRoute);
            $response->assertSee('QUAF — Season 09');
            $response->assertSee('PRINT & PDF EXPORT', false);
        }
    }

    public function test_admin_can_regenerate_tough_pin_for_judge(): void
    {
        $judge = Judge::first();
        $oldPin = $judge->access_code;

        $response = $this->actingAs($this->admin)->post(route('admin.judges.regenerate-pin', $judge));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $judge->refresh();
        $this->assertNotNull($judge->access_code);
        $this->assertEquals(4, strlen($judge->access_code));
        $this->assertNotEquals('0000', $judge->access_code);
        $this->assertNotEquals('1234', $judge->access_code);
    }

    public function test_zone_and_class_mapping_rules(): void
    {
        // 4 in class -> A Zone
        $this->assertEquals('A Zone', Zone::determineZoneNameFromClass('NF4'));
        $this->assertEquals('A Zone', Zone::determineZoneNameFromClass('UH4'));
        $this->assertEquals('A Zone', Zone::determineZoneNameFromClass('S4'));
        $this->assertEquals('A Zone', Zone::determineZoneNameFromClass('TQS'));

        // 3 in class -> B Zone
        $this->assertEquals('B Zone', Zone::determineZoneNameFromClass('NF3'));
        $this->assertEquals('B Zone', Zone::determineZoneNameFromClass('ID3'));
        $this->assertEquals('B Zone', Zone::determineZoneNameFromClass('S3'));

        // 1 & 2 in class -> C Zone
        $this->assertEquals('C Zone', Zone::determineZoneNameFromClass('U1'));
        $this->assertEquals('C Zone', Zone::determineZoneNameFromClass('U2'));
        $this->assertEquals('C Zone', Zone::determineZoneNameFromClass('L2'));

        // Zones page renders with class mapping guide
        $response = $this->actingAs($this->admin)->get(route('admin.zones.index'));
        $response->assertStatus(200);
        $response->assertSee('Official Zone & Class Mapping', false);
        $response->assertSee('All Class 4 Levels');
        $response->assertSee('All Class 3 Levels');
        $response->assertSee('All Class 1 & 2 Levels', false);

        // Creating student with class NF4 auto-maps to A Zone
        $group = Group::first();
        $postResponse = $this->actingAs($this->admin)->post(route('admin.students.store'), [
            'name' => 'Auto Zone Test Student',
            'group_id' => $group->id,
            'class_level' => 'NF4',
        ]);
        $postResponse->assertRedirect(route('admin.students.index'));
        $this->assertDatabaseHas('students', [
            'name' => 'Auto Zone Test Student',
            'class_level' => 'NF4',
            'category' => 'A Zone',
        ]);
    }
}
