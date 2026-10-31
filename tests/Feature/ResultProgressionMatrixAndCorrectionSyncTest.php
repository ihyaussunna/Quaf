<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use App\Services\PointCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResultProgressionMatrixAndCorrectionSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $announcer;

    protected Group $groupA;

    protected Group $groupB;

    protected ProgramCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        Result::ensureSchema();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Controller',
            'email' => 'admin@quaf.test',
        ]);

        $this->announcer = User::factory()->create([
            'role' => 'announcer',
            'name' => 'Stage Announcer',
            'email' => 'announcer@quaf.test',
        ]);

        $this->groupA = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#be1e2d',
        ]);

        $this->groupB = Group::create([
            'name' => 'Pacto',
            'code' => 'PACTO',
            'slug' => 'pacto',
            'color_hex' => '#2563eb',
        ]);

        $this->category = ProgramCategory::create([
            'name' => 'Senior',
            'slug' => 'senior',
        ]);
    }

    protected function makeStudent(string $name, Group $group): Student
    {
        return Student::create([
            'student_id' => 'STU-'.Str::upper(Str::random(6)),
            'name' => $name,
            'group_id' => $group->id,
            'category_id' => $this->category->id,
            'qr_token' => Str::random(32),
        ]);
    }

    protected function makeEntry(Program $program, Student $student, Group $group): ProgramEntry
    {
        return ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'chest_number' => 'CH-'.rand(100, 999),
            'status' => 'participated',
        ]);
    }

    public function test_progression_matrix_calculates_sequential_and_cumulative_totals(): void
    {
        // Program 1
        $program1 = Program::create([
            'name' => 'Elocution English',
            'code' => 'PROG-101',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'status' => 'completed',
        ]);

        $studentA1 = $this->makeStudent('Zayd A', $this->groupA);
        $entryA1 = $this->makeEntry($program1, $studentA1, $this->groupA);

        $studentB1 = $this->makeStudent('Bilal B', $this->groupB);
        $entryB1 = $this->makeEntry($program1, $studentB1, $this->groupB);

        // Result 1: A1 gets 1st (pos=5, A+=6 => 11), B1 gets 2nd (pos=3, A=5 => 8)
        $result1 = Result::create([
            'program_id' => $program1->id,
            'first_entry_id' => $entryA1->id,
            'first_grade' => 'A+',
            'second_entry_id' => $entryB1->id,
            'second_grade' => 'A',
            'status' => 'announced',
            'result_number' => 1,
        ]);

        // Program 2
        $program2 = Program::create([
            'name' => 'Essay Writing',
            'code' => 'PROG-102',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'status' => 'completed',
        ]);

        $studentA2 = $this->makeStudent('Hamza A', $this->groupA);
        $entryA2 = $this->makeEntry($program2, $studentA2, $this->groupA);

        $studentB2 = $this->makeStudent('Tariq B', $this->groupB);
        $entryB2 = $this->makeEntry($program2, $studentB2, $this->groupB);

        // Result 2: B2 gets 1st (pos=5, A+=6 => 11), A2 gets 2nd (pos=3, A=5 => 8)
        $result2 = Result::create([
            'program_id' => $program2->id,
            'first_entry_id' => $entryB2->id,
            'first_grade' => 'A+',
            'second_entry_id' => $entryA2->id,
            'second_grade' => 'A',
            'status' => 'announced',
            'result_number' => 2,
        ]);

        // Recalculate points engine
        app(PointCalculationService::class)->recalculateAllPoints();

        $matrix = PointCalculationService::getProgressionMatrix();

        $this->assertEquals(2, $matrix['total_results']);
        $this->assertCount(2, $matrix['rows']);

        // Row 1 (Result 1)
        $row1 = $matrix['rows'][0];
        $this->assertEquals('01', $row1['formatted_result_number']);
        $this->assertEquals(11, $row1['teams'][$this->groupA->id]['this_points']);
        $this->assertEquals(11, $row1['teams'][$this->groupA->id]['cumulative_total']);
        $this->assertEquals(8, $row1['teams'][$this->groupB->id]['this_points']);
        $this->assertEquals(8, $row1['teams'][$this->groupB->id]['cumulative_total']);

        // Row 2 (Result 2): progressive accumulation (1+2 total)
        $row2 = $matrix['rows'][1];
        $this->assertEquals('02', $row2['formatted_result_number']);
        $this->assertEquals(8, $row2['teams'][$this->groupA->id]['this_points']);
        $this->assertEquals(19, $row2['teams'][$this->groupA->id]['cumulative_total']); // 11 + 8 = 19
        $this->assertEquals(11, $row2['teams'][$this->groupB->id]['this_points']);
        $this->assertEquals(19, $row2['teams'][$this->groupB->id]['cumulative_total']); // 8 + 11 = 19
    }

    public function test_admin_and_announcer_can_access_progression_matrix_views(): void
    {
        $program = Program::create([
            'name' => 'Qiraath Competition',
            'code' => 'PROG-201',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'status' => 'completed',
        ]);

        Result::create([
            'program_id' => $program->id,
            'status' => 'send',
            'result_number' => 1,
        ]);

        // Admin Access
        $adminRes = $this->actingAs($this->admin)->get(route('admin.results.progression-matrix'));
        $adminRes->assertOk();
        $adminRes->assertViewHas('rows');
        $adminRes->assertSee('Sequential Points Progression Matrix');

        $adminPrint = $this->actingAs($this->admin)->get(route('admin.print.progression-matrix'));
        $adminPrint->assertOk();
        $adminPrint->assertSee('Points Progression Matrix');

        // Announcer Access
        $announcerRes = $this->actingAs($this->announcer)->get(route('announcer.progression-matrix'));
        $announcerRes->assertOk();
        $announcerRes->assertViewHas('rows');
        $announcerRes->assertSee('Sequential Points Progression Matrix');

        $announcerPrint = $this->actingAs($this->announcer)->get(route('announcer.print.progression-matrix'));
        $announcerPrint->assertOk();
        $announcerPrint->assertSee('Points Progression Matrix');
    }

    public function test_grade_correction_in_announcer_desk_updates_points_and_syncs(): void
    {
        $program = Program::create([
            'name' => 'Malayalam Recitation',
            'code' => 'PROG-301',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'status' => 'completed',
        ]);

        $studentA = $this->makeStudent('Faris A', $this->groupA);
        $entryA = $this->makeEntry($program, $studentA, $this->groupA);

        // Initial result: A gets 1st with B grade (pos=5, B=3 => 8 pts)
        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entryA->id,
            'first_grade' => 'B',
            'status' => 'send',
            'result_number' => 1,
        ]);

        app(PointCalculationService::class)->recalculateAllPoints();

        $groupInitial = $this->groupA->fresh();
        $this->assertEquals(8, $groupInitial->points_cache);

        // Announcer corrects grade from B to A+ (pos=5, A+=6 => 11 pts)
        $response = $this->actingAs($this->announcer)->post(route('announcer.update-grades', $result->id), [
            'first_grade' => 'A+',
            'second_grade' => null,
            'third_grade' => null,
        ]);

        $response->assertSessionHas('success');

        $resultFresh = $result->fresh();
        $this->assertEquals('A+', $resultFresh->first_grade);

        $groupAfter = $this->groupA->fresh();
        $this->assertEquals(11, $groupAfter->points_cache);
    }
}
