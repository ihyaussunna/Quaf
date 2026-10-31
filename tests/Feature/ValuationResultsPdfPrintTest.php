<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\ScoreSheet;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ValuationResultsPdfPrintTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Group $groupA;

    protected Group $groupB;

    protected ProgramCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Controller',
            'email' => 'admin@quaf.test',
        ]);

        $this->groupA = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#be1e2d',
        ]);

        $this->groupB = Group::create([
            'name' => 'Aura Zeic',
            'code' => 'AURA',
            'slug' => 'aura-zeic',
            'color_hex' => '#009444',
        ]);

        $this->category = ProgramCategory::create([
            'name' => 'Junior',
            'code' => 'JUN',
            'slug' => 'junior',
            'min_class' => 5,
            'max_class' => 7,
        ]);
    }

    public function test_admin_can_view_bulk_evaluated_results_pdf_report(): void
    {
        $program = Program::create([
            'name' => 'Malayalam Speech',
            'code' => 'P101',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'status' => 'completed',
        ]);

        $studentA = Student::create([
            'student_id' => 'STU-'.Str::upper(Str::random(6)),
            'name' => 'Zayd',
            'group_id' => $this->groupA->id,
            'category' => 'junior',
            'class' => 6,
            'qr_token' => Str::random(32),
        ]);

        $studentB = Student::create([
            'student_id' => 'STU-'.Str::upper(Str::random(6)),
            'name' => 'Bilal',
            'group_id' => $this->groupB->id,
            'category' => 'junior',
            'class' => 6,
            'qr_token' => Str::random(32),
        ]);

        $entryA = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $studentA->id,
            'group_id' => $this->groupA->id,
            'chest_number' => '101',
            'code_letter' => 'A',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        $entryB = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $studentB->id,
            'group_id' => $this->groupB->id,
            'chest_number' => '102',
            'code_letter' => 'B',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        $judgeUser = User::factory()->create(['role' => 'judge']);
        $judge = Judge::create([
            'user_id' => $judgeUser->id,
            'name' => 'Usthad Jury 1',
            'access_code' => '1234',
        ]);

        $program->judges()->attach($judge->id);

        ScoreSheet::create([
            'judge_id' => $judge->id,
            'program_id' => $program->id,
            'entry_id' => $entryA->id,
            'criteria_scores' => ['content' => 50, 'delivery' => 45],
            'total_score' => 95.00,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        ScoreSheet::create([
            'judge_id' => $judge->id,
            'program_id' => $program->id,
            'entry_id' => $entryB->id,
            'criteria_scores' => ['content' => 40, 'delivery' => 40],
            'total_score' => 80.00,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        // Access results print report with status=evaluated
        $response = $this->actingAs($this->admin)->get(route('admin.print.results', ['status' => 'evaluated']));
        $response->assertOk();
        $response->assertSee('Malayalam Speech');
        $response->assertSee('P101');
        $response->assertSee('Valuation Completed');

        // Access via shortcut route
        $shortcutResponse = $this->actingAs($this->admin)->get(route('admin.results.evaluated-pdf'));
        $shortcutResponse->assertOk();
        $shortcutResponse->assertSee('Malayalam Speech');
    }

    public function test_admin_can_view_program_valuation_sheet(): void
    {
        $program = Program::create([
            'name' => 'Elocution English',
            'code' => 'P102',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'status' => 'completed',
        ]);

        $student = Student::create([
            'student_id' => 'STU-'.Str::upper(Str::random(6)),
            'name' => 'Faris',
            'group_id' => $this->groupA->id,
            'category' => 'junior',
            'class' => 6,
            'qr_token' => Str::random(32),
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'group_id' => $this->groupA->id,
            'chest_number' => '105',
            'code_letter' => 'A',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        $judgeUser = User::factory()->create(['role' => 'judge']);
        $judge = Judge::create([
            'user_id' => $judgeUser->id,
            'name' => 'Judge Master',
            'access_code' => '5678',
        ]);

        $program->judges()->attach($judge->id);

        ScoreSheet::create([
            'judge_id' => $judge->id,
            'program_id' => $program->id,
            'entry_id' => $entry->id,
            'criteria_scores' => ['presentation' => 90],
            'total_score' => 90.00,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.print.evaluation-sheet', $program));
        $response->assertOk();
        $response->assertSee('OFFICIAL VALUATION REPORT');
        $response->assertSee('Elocution English');
        $response->assertSee('P102');
        $response->assertSee('Judge Master');
        $response->assertSee('Faris');
        $response->assertSee('Chief Tabulator');
        $response->assertSee('Controller of Examinations');
    }

    public function test_ui_contains_evaluated_pdf_buttons(): void
    {
        $program = Program::create([
            'name' => 'Qiraat',
            'code' => 'P103',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'status' => 'completed',
        ]);

        $student = Student::create([
            'student_id' => 'STU-'.Str::upper(Str::random(6)),
            'name' => 'Ahmad',
            'group_id' => $this->groupA->id,
            'category' => 'junior',
            'class' => 7,
            'qr_token' => Str::random(32),
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'group_id' => $this->groupA->id,
            'chest_number' => '109',
            'code_letter' => 'C',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        $judgeUser = User::factory()->create(['role' => 'judge']);
        $judge = Judge::create([
            'user_id' => $judgeUser->id,
            'name' => 'Qari Jury',
            'access_code' => '9999',
        ]);

        $program->judges()->attach($judge->id);

        ScoreSheet::create([
            'judge_id' => $judge->id,
            'program_id' => $program->id,
            'entry_id' => $entry->id,
            'criteria_scores' => ['tajweed' => 92],
            'total_score' => 92.00,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        // Evaluation Monitor Index
        $monIndex = $this->actingAs($this->admin)->get(route('admin.evaluation-monitor.index'));
        $monIndex->assertOk();
        $monIndex->assertSee('Print Evaluated Results PDF');
        $monIndex->assertSee('Sheet PDF');

        // Evaluation Monitor Show
        $monShow = $this->actingAs($this->admin)->get(route('admin.evaluation-monitor.show', $program));
        $monShow->assertOk();
        $monShow->assertSee('Print Valuation Scorecard PDF');

        // Results Index
        $resIndex = $this->actingAs($this->admin)->get(route('admin.results.index'));
        $resIndex->assertOk();
        $resIndex->assertSee('Evaluated Results PDF');
    }
}
