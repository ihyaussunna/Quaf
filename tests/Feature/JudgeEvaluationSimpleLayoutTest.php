<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Schedule;
use App\Models\ScoreSheet;
use App\Models\Stage;
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
        $response->assertSee('Score Saved in Database');
        $response->assertDontSee('ഡാറ്റാബേസിൽ ഡാറ്റ സേവ് ആയിട്ടുണ്ട്');
        $response->assertSee('Database Synchronized');
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
            'database_saved' => true,
            'result_sheet_synced' => true,
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

    public function test_judge_dashboard_displays_live_and_next_program_cards(): void
    {
        // Program 1 is live on stage
        $this->program->update(['status' => 'in_progress']);

        // Program 2 is upcoming next
        $program2 = Program::create([
            'category_id' => $this->program->category_id,
            'name' => 'Arabic Calligraphy Next',
            'code' => 'Q9 - 202',
            'type' => 'individual',
            'eligibility' => 'A Zone',
            'participant_count' => 1,
            'duration_minutes' => 45,
            'status' => 'upcoming',
        ]);
        $program2->judges()->attach($this->judge->id);

        $response = $this->actingAs($this->judgeUser)
            ->get(route('judge.dashboard'));

        $response->assertOk();
        $response->assertSee('Live Program On Stage');
        $response->assertSee('Elocution English');
        $response->assertSee('Evaluate Live Program Now');

        $response->assertSee('Next Program Schedule');
        $response->assertSee('Arabic Calligraphy Next');
        $response->assertSee('Open Next Evaluation Sheet');
    }

    public function test_finished_program_is_never_shown_as_live_program(): void
    {
        $stage = Stage::create([
            'name' => 'Stage 01',
            'code' => 'STG-01',
        ]);

        $this->program->update(['status' => 'completed']);
        Schedule::create([
            'program_id' => $this->program->id,
            'stage_id' => $stage->id,
            'start_time' => now()->subHours(2),
            'end_time' => now()->subHour(),
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->judgeUser)
            ->get(route('judge.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Live Program On Stage');
        $response->assertDontSee('Evaluate Live Program Now');
    }
}
