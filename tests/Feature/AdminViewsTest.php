<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\ScoreSheet;
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
        $editView->assertSee('Edit Group: '.$group->name);

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
            route('admin.print.entries', ['mode' => 'group_wise']),
            route('admin.print.entries', ['mode' => 'program_wise']),
        ];

        foreach ($printRoutes as $pRoute) {
            $response = $this->actingAs($this->admin)->get($pRoute);
            $response->assertOk();
            $response->assertDontSee('dashboard-logo.png');
            $response->assertSee('print-pdf-header.svg');
            $response->assertSee('PRINT & PDF EXPORT', false);
        }

        // Test entries CSV export
        $csvResponse = $this->actingAs($this->admin)->get(route('admin.exports.download', 'entries'));
        $csvResponse->assertOk();
        $this->assertTrue(str_contains($csvResponse->headers->get('Content-Disposition') ?? '', 'festfloww-entries'));
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

    public function test_admin_can_view_group_show_details_page(): void
    {
        $group = Group::first();

        $response = $this->actingAs($this->admin)->get(route('admin.groups.show', $group));

        $response->assertStatus(200);
        $response->assertSee($group->name);
        $response->assertSee($group->code);
        $response->assertSee('Leadership & Management Details', false);
        $response->assertSee('Students Roster');
        $response->assertSee('Program Registrations');
    }

    public function test_admin_print_students_supports_participation_filtering(): void
    {
        $group = Group::first();
        $studentWithEntry = Student::create([
            'name' => 'Active Competitor Student',
            'student_id' => 'QF9901',
            'group_id' => $group->id,
            'category' => 'A Zone',
            'qr_token' => Str::random(32),
        ]);
        $studentWithoutEntry = Student::create([
            'name' => 'Inactive Unregistered Student',
            'student_id' => 'QF9902',
            'group_id' => $group->id,
            'category' => 'A Zone',
            'qr_token' => Str::random(32),
        ]);

        $program = Program::first();
        ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $studentWithEntry->id,
            'group_id' => $group->id,
            'chest_number' => 'QF9901',
            'status' => 'confirmed',
        ]);

        // All students
        $resAll = $this->actingAs($this->admin)->get(route('admin.print.students'));
        $resAll->assertOk();
        $resAll->assertSee('Active Competitor Student');
        $resAll->assertSee('Inactive Unregistered Student');
        $resAll->assertSee('Omit Unregistered (0 Programs)', false);
        $resAll->assertSee('Hide Students with 0 Competitions', false);

        // Only participating
        $resPart = $this->actingAs($this->admin)->get(route('admin.print.students', ['participation' => 'participating']));
        $resPart->assertOk();
        $resPart->assertSee('Active Competitor Student');
        $resPart->assertDontSee('Inactive Unregistered Student');

        // Only unregistered
        $resUnreg = $this->actingAs($this->admin)->get(route('admin.print.students', ['participation' => 'not_participating']));
        $resUnreg->assertOk();
        $resUnreg->assertDontSee('Active Competitor Student');
        $resUnreg->assertSee('Inactive Unregistered Student');
    }

    public function test_view_marks_page_displays_programs_alphabetically_with_podiums_without_dropdown_selection(): void
    {
        $judge = Judge::create([
            'name' => 'Expert Evaluator',
            'phone' => '9898989898',
            'specialization' => 'Arts',
        ]);

        $cat = ProgramCategory::first();
        $group = Group::first();

        // Create programs with alphabetical names: Beta Contest, Alpha Contest, Gamma Contest
        $progAlpha = Program::create([
            'name' => 'Alpha Elocution',
            'code' => 'ALP01',
            'type' => 'individual',
            'category_id' => $cat->id,
            'status' => 'upcoming',
            'is_stage' => 1,
        ]);

        $progBeta = Program::create([
            'name' => 'Beta Debate',
            'code' => 'BET01',
            'type' => 'individual',
            'category_id' => $cat->id,
            'status' => 'upcoming',
            'is_stage' => 1,
        ]);

        $student1 = Student::create([
            'name' => 'Winner One',
            'student_id' => 'STU-WIN-01',
            'group_id' => $group->id,
            'category' => 'A Zone',
            'qr_token' => Str::random(32),
        ]);

        $entry1 = ProgramEntry::create([
            'program_id' => $progAlpha->id,
            'student_id' => $student1->id,
            'group_id' => $group->id,
            'chest_number' => '101',
            'code_letter' => 'A',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        ScoreSheet::create([
            'judge_id' => $judge->id,
            'program_id' => $progAlpha->id,
            'entry_id' => $entry1->id,
            'total_score' => 95.0,
            'is_submitted' => true,
        ]);

        // Access without selecting any program in dropdown
        $response = $this->actingAs($this->admin)->get(route('admin.mark-entry.view-marks'));
        $response->assertOk();
        $response->assertSee('View Program Marks');
        $response->assertSee('A to Z Directory');
        $response->assertSee('Alpha Elocution');
        $response->assertSee('Beta Debate');
        $response->assertSee('1st Place (Gold)');
        $response->assertSee('Winner One');
        $response->assertSee('95.0 pts');
        $response->assertSee('Evaluation Pending');

        // Check search filter works
        $searchRes = $this->actingAs($this->admin)->get(route('admin.mark-entry.view-marks', ['search' => 'Alpha']));
        $searchRes->assertOk();
        $this->assertTrue($searchRes->viewData('programsList')->contains('name', 'Alpha Elocution'));
        $this->assertFalse($searchRes->viewData('programsList')->contains('name', 'Beta Debate'));
    }

    public function test_view_marks_page_displays_selected_program_with_code_letter_sorted_entries_and_ranks(): void
    {
        $judge = Judge::first() ?? Judge::create([
            'name' => 'Judge Master',
            'phone' => '9999888877',
        ]);
        $cat = ProgramCategory::first();
        $group = Group::first();

        $prog = Program::create([
            'name' => 'Calligraphy Master',
            'code' => 'CAL01',
            'type' => 'individual',
            'category_id' => $cat->id,
            'status' => 'upcoming',
            'is_stage' => 0,
        ]);

        $stuB = Student::create([
            'name' => 'Candidate B',
            'student_id' => 'STU-B',
            'group_id' => $group->id,
            'category' => 'A Zone',
            'qr_token' => Str::random(32),
        ]);
        $entryB = ProgramEntry::create([
            'program_id' => $prog->id,
            'student_id' => $stuB->id,
            'group_id' => $group->id,
            'chest_number' => '102',
            'code_letter' => 'B',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        $stuA = Student::create([
            'name' => 'Candidate A',
            'student_id' => 'STU-A',
            'group_id' => $group->id,
            'category' => 'A Zone',
            'qr_token' => Str::random(32),
        ]);
        $entryA = ProgramEntry::create([
            'program_id' => $prog->id,
            'student_id' => $stuA->id,
            'group_id' => $group->id,
            'chest_number' => '101',
            'code_letter' => 'A',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        ScoreSheet::create([
            'judge_id' => $judge->id,
            'program_id' => $prog->id,
            'entry_id' => $entryA->id,
            'total_score' => 92.0,
            'is_submitted' => true,
        ]);

        ScoreSheet::create([
            'judge_id' => $judge->id,
            'program_id' => $prog->id,
            'entry_id' => $entryB->id,
            'total_score' => 84.0,
            'is_submitted' => true,
        ]);

        // Select program
        $response = $this->actingAs($this->admin)->get(route('admin.mark-entry.view-marks', ['program' => $prog->id]));
        $response->assertOk();
        $response->assertSee('Calligraphy Master');
        $response->assertSee('Participants by Code Letter (A to Z)', false);
        $response->assertSee('Candidate A');
        $response->assertSee('Candidate B');
        $response->assertSee('1st Place (Gold)');
        $response->assertSee('2nd Place (Silver)');
        $response->assertSee('92.0');
        $response->assertSee('84.0');
    }
}
