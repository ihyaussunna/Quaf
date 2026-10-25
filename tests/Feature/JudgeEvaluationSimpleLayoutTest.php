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

class JudgeEvaluationSimpleLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $judgeUser;

    protected Judge $judge;

    protected Program $program;

    protected ProgramEntry $entry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->judgeUser = User::factory()->create([
            'role' => 'judge',
        ]);

        $this->judge = Judge::create([
            'user_id' => $this->judgeUser->id,
            'name' => 'Dr. Test Juror',
            'access_code' => '7788',
        ]);

        $category = ProgramCategory::create([
            'name' => 'High School',
            'slug' => 'high-school',
            'code' => 'HS',
            'min_class' => 8,
            'max_class' => 10,
        ]);

        $group = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#56286b',
        ]);

        $this->program = Program::create([
            'category_id' => $category->id,
            'name' => 'Elocution English',
            'code' => 'Q9 - 201',
            'type' => 'individual',
            'eligibility' => 'A Zone',
            'participant_count' => 1,
            'duration_minutes' => 7,
        ]);

        $this->program->judges()->attach($this->judge->id);

        $student = Student::create([
            'student_id' => 'STU901',
            'name' => 'Secret Student',
            'group_id' => $group->id,
            'admission_number' => 'ADM901',
            'class_level' => 'D4',
            'category' => 'A Zone',
            'qr_token' => Str::random(32),
        ]);

        $this->entry = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'chest_number' => '501',
            'code_letter' => 'A',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);
    }

    public function test_judge_evaluation_renders_simple_mode_table_matching_design(): void
    {
        $response = $this->actingAs($this->judgeUser)
            ->get(route('judge.evaluate', $this->program));

        $response->assertOk();
        $response->assertSee('CODE');
        $response->assertSee('GRADE');
        $response->assertSee('MARK');
        $response->assertSee('Save All Scores');
        $response->assertSee('Elocution English');

        // Anonymity: real name and chest number must never be shown
        $response->assertDontSee('Secret Student');
        $response->assertDontSee('STU901');
    }

    public function test_judge_can_save_simple_score_with_auto_grade(): void
    {
        $response = $this->actingAs($this->judgeUser)
            ->postJson(route('judge.evaluate.save', [$this->program, $this->entry]), [
                'scoring_mode' => 'simple',
                'total_score' => 88.5,
                'grade' => 'A',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'total_score' => 88.5,
            'grade' => 'A',
        ]);

        $this->assertDatabaseHas('score_sheets', [
            'judge_id' => $this->judge->id,
            'program_id' => $this->program->id,
            'entry_id' => $this->entry->id,
            'total_score' => 88.5,
            'is_submitted' => true,
        ]);
    }

    public function test_judge_dashboard_shows_pending_evaluation_when_candidates_are_not_evaluated(): void
    {
        // Program has status completed in DB, but judge has NOT submitted any scores for present candidate
        $this->program->update(['status' => 'completed']);

        $response = $this->actingAs($this->judgeUser)
            ->get(route('judge.dashboard'));

        $response->assertOk();
        // Completed count should be 0, and Pending Evaluation count should be 1
        $response->assertSee('Evaluation Pending');
        $response->assertSee('Evaluate Participants Now');
        $response->assertDontSee('Review / Edit Scores');
    }

    public function test_judge_dashboard_shows_completed_only_when_judge_submits_all_scores(): void
    {
        // Judge submits scores for the candidate
        ScoreSheet::create([
            'judge_id' => $this->judge->id,
            'program_id' => $this->program->id,
            'entry_id' => $this->entry->id,
            'total_score' => 90,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->judgeUser)
            ->get(route('judge.dashboard'));

        $response->assertOk();
        $response->assertSee('Review / Edit Scores');
        $response->assertDontSee('Evaluate Participants Now');
    }
}
