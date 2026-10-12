<?php

namespace Tests\Feature;

use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Database\Seeders\OfficialGroupStudentsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentNamePreservationAndGroupDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        FestivalSetting::set('student_editing_open', '1');
    }

    public function test_student_name_correction_is_never_overwritten_by_official_group_students_seeder(): void
    {
        $group = Group::where('code', 'LUMO')->first() ?? Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#56286B',
        ]);

        $zone = Zone::where('code', 'A_ZONE')->first() ?? Zone::create([
            'name' => 'A Zone',
            'code' => 'A_ZONE',
            'slug' => 'a-zone-test',
        ]);

        $student = Student::create([
            'student_id' => 'QF1001',
            'name' => 'CORRECTED CUSTOM NAME',
            'group_id' => $group->id,
            'zone_id' => $zone->id,
            'category' => 'A Zone',
            'qr_token' => 'fixed-token-12345',
        ]);

        // Run the OfficialGroupStudentsSeeder
        $seeder = new OfficialGroupStudentsSeeder;
        $seeder->run();

        // Refresh from database
        $student->refresh();

        // Verify that the manually corrected name and QR token were PRESERVED 100%
        $this->assertEquals('CORRECTED CUSTOM NAME', $student->name);
        $this->assertEquals('fixed-token-12345', $student->qr_token);
    }

    public function test_call_list_displays_group_participants_and_searches_by_participant_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $group = Group::where('code', 'PACTO')->first() ?? Group::create([
            'name' => 'Pacto Hikmic',
            'code' => 'PACTO',
            'slug' => 'pacto-hikmic',
            'color_hex' => '#2E3192',
        ]);

        $zone = Zone::where('code', 'A_ZONE')->first() ?? Zone::first();

        $program = Program::create([
            'name' => 'Qawwali Choral',
            'code' => 'Q9-200',
            'type' => 'group',
            'is_stage' => true,
            'zone_id' => $zone->id,
            'limit' => 5,
        ]);

        $student1 = Student::create([
            'student_id' => 'QF2001',
            'name' => 'CAPTAIN AHMED',
            'group_id' => $group->id,
            'zone_id' => $zone->id,
            'category' => 'A Zone',
            'qr_token' => 'qr-ahmed-123',
        ]);

        $student2 = Student::create([
            'student_id' => 'QF2002',
            'name' => 'SINGER BILAL',
            'group_id' => $group->id,
            'zone_id' => $zone->id,
            'category' => 'A Zone',
            'qr_token' => 'qr-bilal-456',
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student1->id,
            'group_id' => $group->id,
            'chest_number' => 'PACTO-Q9-200',
            'status' => 'verified',
        ]);

        $entry->participants()->attach($student1->id, ['role' => 'captain']);
        $entry->participants()->attach($student2->id, ['role' => 'participant']);

        // 1. Visit Call List - check that group and both members are shown
        $response = $this->actingAs($admin)->get(route('admin.call-list.index', ['program_id' => $program->id]));
        $response->assertOk();
        $response->assertSee('PACTO-Q9-200');
        $response->assertSee('Pacto Hikmic');
        $response->assertSee('CAPTAIN AHMED');
        $response->assertSee('SINGER BILAL');

        // 2. Search by participant name (SINGER BILAL) who is not the captain
        $searchResponse = $this->actingAs($admin)->get(route('admin.call-list.index', [
            'program_id' => $program->id,
            'search' => 'BILAL',
        ]));
        $searchResponse->assertOk();
        $searchResponse->assertSee('PACTO-Q9-200');
        $searchResponse->assertSee('SINGER BILAL');

        // 3. Forms call list preview includes participant names
        $formResponse = $this->actingAs($admin)->get(route('admin.forms.call-list', ['program_id' => $program->id]));
        $formResponse->assertOk();
        $formResponse->assertSee('CAPTAIN AHMED');
        $formResponse->assertSee('SINGER BILAL');

        // 4. Printable call list includes participant names
        $printResponse = $this->actingAs($admin)->get(route('admin.forms.call-list', ['program_id' => $program->id, 'print' => 1]));
        $printResponse->assertOk();
        $printResponse->assertSee('CAPTAIN AHMED');
        $printResponse->assertSee('SINGER BILAL');
    }
}
