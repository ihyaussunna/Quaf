<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminBulkStudentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Group $groupLumo;

    protected Group $groupPacto;

    protected Zone $zoneA;

    protected Zone $zoneB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@quaf.fest',
        ]);

        $this->groupLumo = Group::firstOrCreate(
            ['code' => 'LUMO'],
            [
                'name' => 'LUMO Group',
                'slug' => 'lumo-group',
                'color_hex' => '#009444',
            ]
        );

        $this->groupPacto = Group::firstOrCreate(
            ['code' => 'PACTO'],
            [
                'name' => 'PACTO Group',
                'slug' => 'pacto-group',
                'color_hex' => '#be1e2d',
            ]
        );

        $this->zoneA = Zone::where('name', 'A Zone')->first() ?? Zone::firstOrCreate(
            ['code' => 'A_ZONE'],
            [
                'name' => 'A Zone',
                'slug' => 'a-zone',
                'color_hex' => '#be1e2d',
                'display_order' => 1,
            ]
        );

        $this->zoneB = Zone::where('name', 'B Zone')->first() ?? Zone::firstOrCreate(
            ['code' => 'B_ZONE'],
            [
                'name' => 'B Zone',
                'slug' => 'b-zone',
                'color_hex' => '#f3bd2e',
                'display_order' => 2,
            ]
        );
    }

    public function test_admin_can_view_bulk_create_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.bulk'));

        $response->assertOk();
        $response->assertViewIs('admin.students.bulk');
        $response->assertSee('Bulk Register Participants');
        $response->assertSee('LUMO');
        $response->assertSee('PACTO');
    }

    public function test_admin_can_download_bulk_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.bulk-template'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_bulk_register_students_via_paste_text(): void
    {
        $pasteText = "Muhammed Fayiz, LUMO, Class 4, 9847000001\n".
                     "Ahmad Bilal, PACTO, Class 3, 9847000002\n".
                     'Zaid Ameen, LUMO, Class 4, 9847000003';

        $response = $this->actingAs($this->admin)->post(route('admin.students.bulk-store'), [
            'paste_text' => $pasteText,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'name' => 'Muhammed Fayiz',
            'group_id' => $this->groupLumo->id,
            'category' => 'A Zone',
            'class_level' => 'Class 4',
            'contact' => '9847000001',
        ]);

        $this->assertDatabaseHas('students', [
            'name' => 'Ahmad Bilal',
            'group_id' => $this->groupPacto->id,
            'category' => 'B Zone',
            'class_level' => 'Class 3',
            'contact' => '9847000002',
        ]);

        $this->assertDatabaseHas('students', [
            'name' => 'Zaid Ameen',
            'group_id' => $this->groupLumo->id,
            'category' => 'A Zone',
        ]);

        $this->assertEquals(3, Student::count());
    }

    public function test_admin_can_bulk_register_using_default_group_and_zone(): void
    {
        $pasteText = "Student One\nStudent Two\nStudent Three";

        $response = $this->actingAs($this->admin)->post(route('admin.students.bulk-store'), [
            'default_group_id' => $this->groupPacto->id,
            'default_category' => 'A Zone',
            'paste_text' => $pasteText,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertEquals(3, Student::where('group_id', $this->groupPacto->id)->count());
        $this->assertEquals(3, Student::where('category', 'A Zone')->count());
    }

    public function test_admin_can_bulk_register_via_csv_upload(): void
    {
        $csvContent = "Name,Group,Class / Zone,Contact,Chest Number\n".
                      "Nasirudeen, LUMO, Class 4, 9999999999,\n".
                      "Fathih, PACTO, Class 3, 8888888888,\n";

        $file = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.students.bulk-store'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'name' => 'Nasirudeen',
            'group_id' => $this->groupLumo->id,
            'category' => 'A Zone',
        ]);

        $this->assertDatabaseHas('students', [
            'name' => 'Fathih',
            'group_id' => $this->groupPacto->id,
            'category' => 'B Zone',
        ]);
    }

    public function test_admin_can_bulk_register_four_column_chest_name_class_zone_format_with_group_header(): void
    {
        $concoGroup = Group::firstOrCreate(
            ['code' => 'CONCO'],
            [
                'name' => 'Conco Majdic',
                'slug' => 'conco-majdic',
                'color_hex' => '#f8e709',
            ]
        );

        $pasteText = "CONCO MAJDIC\n".
                     "\"LEADER : SINAN SAQAFI VELLIMUTTAM\nASSI.LEADERS : MUSHARAF PONNANI\"\n".
                     "Chest No\tName\tClass\tZone\n".
                     "QF3001\tIMRAN MAVINAKATT\tTQS\tA ZONE\n".
                     "QF3002\tSYD SWABAH\tTQS\tA ZONE\n".
                     "QF3099\tYASIR RILWAN\tS3\tB ZONE\n".
                     "QF3208\tABDULLAH REZA\tL2\tC ZONE\n";

        $response = $this->actingAs($this->admin)->post(route('admin.students.bulk-store'), [
            'paste_text' => $pasteText,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'student_id' => 'QF3001',
            'name' => 'IMRAN MAVINAKATT',
            'class_level' => 'TQS',
            'category' => 'A Zone',
            'group_id' => $concoGroup->id,
        ]);

        $this->assertDatabaseHas('students', [
            'student_id' => 'QF3002',
            'name' => 'SYD SWABAH',
            'class_level' => 'TQS',
            'category' => 'A Zone',
            'group_id' => $concoGroup->id,
        ]);

        $this->assertDatabaseHas('students', [
            'student_id' => 'QF3099',
            'name' => 'YASIR RILWAN',
            'class_level' => 'S3',
            'category' => 'B Zone',
            'group_id' => $concoGroup->id,
        ]);

        $this->assertDatabaseHas('students', [
            'student_id' => 'QF3208',
            'name' => 'ABDULLAH REZA',
            'class_level' => 'L2',
            'category' => 'C Zone',
            'group_id' => $concoGroup->id,
        ]);
    }

    public function test_admin_can_bulk_register_four_column_comma_separated_with_default_group(): void
    {
        $pasteText = "QF2001, Ahmad Rayan, S4, A ZONE\n".
                     "QF2002, Zahir Ali, S3, B ZONE\n";

        $response = $this->actingAs($this->admin)->post(route('admin.students.bulk-store'), [
            'default_group_id' => $this->groupPacto->id,
            'paste_text' => $pasteText,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'student_id' => 'QF2001',
            'name' => 'Ahmad Rayan',
            'class_level' => 'S4',
            'category' => 'A Zone',
            'group_id' => $this->groupPacto->id,
        ]);

        $this->assertDatabaseHas('students', [
            'student_id' => 'QF2002',
            'name' => 'Zahir Ali',
            'class_level' => 'S3',
            'category' => 'B Zone',
            'group_id' => $this->groupPacto->id,
        ]);
    }
}
