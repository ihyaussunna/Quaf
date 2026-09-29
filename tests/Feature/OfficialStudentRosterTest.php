<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class OfficialStudentRosterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('app:sync-official-students');
    }

    public function test_all_five_groups_have_official_names_and_codes(): void
    {
        $expectedGroups = [
            'LUMO' => 'Lumo Fikric',
            'PACTO' => 'Pacto Hikmic',
            'CONCO' => 'Conco Majdic',
            'UNIO' => 'Unio Hilmic',
            'YUGO' => 'Yugo Rushdic',
        ];

        foreach ($expectedGroups as $code => $name) {
            $group = Group::where('code', $code)->first();
            $this->assertNotNull($group, "Group with code {$code} should exist.");
            $this->assertEquals($name, $group->name);
        }
    }

    public function test_total_official_student_count_and_group_breakdown(): void
    {
        $totalStudents = Student::count();
        $this->assertEquals(1168, $totalStudents, 'Total student count should be exactly 1,168.');

        $lumo = Group::where('code', 'LUMO')->first();
        $pacto = Group::where('code', 'PACTO')->first();
        $conco = Group::where('code', 'CONCO')->first();
        $unio = Group::where('code', 'UNIO')->first();
        $yugo = Group::where('code', 'YUGO')->first();

        $this->assertEquals(236, Student::where('group_id', $lumo->id)->count(), 'Lumo should have 236 students.');
        $this->assertEquals(229, Student::where('group_id', $pacto->id)->count(), 'Pacto should have 229 students.');
        $this->assertEquals(236, Student::where('group_id', $conco->id)->count(), 'Conco should have 236 students.');
        $this->assertEquals(232, Student::where('group_id', $unio->id)->count(), 'Unio should have 232 students.');
        $this->assertEquals(235, Student::where('group_id', $yugo->id)->count(), 'Yugo should have 235 students.');
    }

    public function test_next_sequential_chest_number_generation(): void
    {
        $lumo = Group::where('code', 'LUMO')->first();
        $pacto = Group::where('code', 'PACTO')->first();
        $conco = Group::where('code', 'CONCO')->first();
        $unio = Group::where('code', 'UNIO')->first();
        $yugo = Group::where('code', 'YUGO')->first();

        $this->assertEquals('QF1237', Student::generateNextChestNumber($lumo));
        $this->assertEquals('QF2230', Student::generateNextChestNumber($pacto));
        $this->assertEquals('QF3237', Student::generateNextChestNumber($conco));
        $this->assertEquals('QF4233', Student::generateNextChestNumber($unio));
        $this->assertEquals('QF5236', Student::generateNextChestNumber($yugo));
    }

    public function test_adding_new_student_auto_assigns_next_chest_number(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);
        $lumo = Group::where('code', 'LUMO')->first();
        $zoneA = Zone::where('code', 'A_ZONE')->first();

        $this->actingAs($admin)
            ->post(route('admin.students.store'), [
                'name' => 'New Test Student Lumo',
                'group_id' => $lumo->id,
                'zone_id' => $zoneA->id,
                'class_level' => 'TQS',
            ])
            ->assertRedirect(route('admin.students.index'));

        $created = Student::where('name', 'New Test Student Lumo')->first();
        $this->assertNotNull($created);
        $this->assertEquals('QF1237', $created->student_id);

        // Next one after that should be QF1238
        $this->assertEquals('QF1238', Student::generateNextChestNumber($lumo));
    }
}
