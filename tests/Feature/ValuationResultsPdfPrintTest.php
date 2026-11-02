<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\ScoreSheet;
use App\Models\Stage;
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

    public function test_stage_programs_results_can_be_filtered_and_printed_with_code_letter_and_qf_number(): void
    {
        $stage = Stage::firstOrCreate(
            ['name' => 'Stage 01 — Grand Amphitheatre'],
            ['code' => 'S01', 'venue' => 'Main Ground']
        );

        // 1. Stage program with Code Letters
        $stageProgram = Program::create([
            'name' => 'Arabic Mappila Song',
            'code' => 'Q9-STAGE-1',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'status' => 'completed',
            'is_stage' => true,
            'stage_id' => $stage->id,
            'eligibility' => 'Mix Zone',
        ]);

        $student1 = Student::create([
            'student_id' => 'QF4099',
            'name' => 'ZAYAN K',
            'group_id' => $this->groupA->id,
            'category' => 'Mix Zone',
            'qr_token' => Str::random(32),
        ]);

        $student2 = Student::create([
            'student_id' => 'QF5088',
            'name' => 'HASHIM T',
            'group_id' => $this->groupB->id,
            'category' => 'Mix Zone',
            'qr_token' => Str::random(32),
        ]);

        $entry1 = ProgramEntry::create([
            'program_id' => $stageProgram->id,
            'student_id' => $student1->id,
            'group_id' => $this->groupA->id,
            'chest_number' => 'QF4099',
            'code_letter' => 'C',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        $entry2 = ProgramEntry::create([
            'program_id' => $stageProgram->id,
            'student_id' => $student2->id,
            'group_id' => $this->groupB->id,
            'chest_number' => 'QF5088',
            'code_letter' => 'A',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        Result::create([
            'program_id' => $stageProgram->id,
            'result_number' => 88,
            'status' => 'published',
            'is_media_published' => true,
            'first_entry_id' => $entry1->id,
            'first_grade' => 'A',
            'second_entry_id' => $entry2->id,
            'second_grade' => 'A',
        ]);

        // 2. Off-stage program
        $offstageProgram = Program::create([
            'name' => 'Pencil Drawing Master',
            'code' => 'Q9-OFF-1',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'status' => 'completed',
            'is_stage' => false,
            'eligibility' => 'A Zone',
        ]);

        $student3 = Student::create([
            'student_id' => 'QF1001',
            'name' => 'ANAS MALAPPURAM',
            'group_id' => $this->groupA->id,
            'category' => 'A Zone',
            'qr_token' => Str::random(32),
        ]);

        $entry3 = ProgramEntry::create([
            'program_id' => $offstageProgram->id,
            'student_id' => $student3->id,
            'group_id' => $this->groupA->id,
            'chest_number' => 'QF1001',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        Result::create([
            'program_id' => $offstageProgram->id,
            'result_number' => 89,
            'status' => 'published',
            'is_media_published' => true,
            'first_entry_id' => $entry3->id,
            'first_grade' => 'B',
        ]);

        // Filter is_stage = 1 (Stage programs only)
        $stageRes = $this->actingAs($this->admin)->get(route('admin.print.results', ['is_stage' => '1']));
        $stageRes->assertOk();
        $stageRes->assertSee('Arabic Mappila Song');
        $stageRes->assertSee('Mix Zone');
        $stageRes->assertSee('ZAYAN K');
        $stageRes->assertSee('QF4099');
        $stageRes->assertSee('Code C');
        $stageRes->assertSee('HASHIM T');
        $stageRes->assertSee('QF5088');
        $stageRes->assertSee('Code A');
        $stageRes->assertSee('SCOPE: STAGE PROGRAMS ONLY');
        $stageRes->assertDontSee('Pencil Drawing Master');

        // Filter is_stage = 1 with Table View
        $tableRes = $this->actingAs($this->admin)->get(route('admin.print.results', ['is_stage' => '1', 'view' => 'table']));
        $tableRes->assertOk();
        $tableRes->assertSee('Arabic Mappila Song');
        $tableRes->assertSee('Winners Table');
        $tableRes->assertSee('ZAYAN K');
        $tableRes->assertSee('QF4099');
        $tableRes->assertSee('HASHIM T');
        $tableRes->assertSee('QF5088');
        $tableRes->assertDontSee('Pencil Drawing Master');

        // Filter is_stage = 0 (Off-stage only)
        $offRes = $this->actingAs($this->admin)->get(route('admin.print.results', ['is_stage' => '0']));
        $offRes->assertOk();
        $offRes->assertSee('Pencil Drawing Master');
        $offRes->assertSee('ANAS MALAPPURAM');
        $offRes->assertDontSee('Arabic Mappila Song');
    }
}
