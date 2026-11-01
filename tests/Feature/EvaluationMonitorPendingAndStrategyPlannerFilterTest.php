<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\ScoreSheet;
use App\Models\Student;
use App\Models\User;
use App\Services\CoinRewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluationMonitorPendingAndStrategyPlannerFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Group $group1;

    protected Group $group2;

    protected ProgramCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->group1 = Group::create([
            'name' => 'Subair',
            'code' => 'SUB',
            'slug' => 'subair',
            'color_hex' => '#be1e2d',
            'points_cache' => 100,
        ]);

        $this->group2 = Group::create([
            'name' => 'Miqdad',
            'code' => 'MIQ',
            'slug' => 'miqdad',
            'color_hex' => '#2e3192',
            'points_cache' => 80,
        ]);

        $this->category = ProgramCategory::create([
            'name' => 'Senior',
            'code' => 'SNR',
            'slug' => 'senior',
            'description' => 'Senior Category',
        ]);
    }

    public function test_student_portal_and_qr_scan_show_participated_programs_and_results_and_coins(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'name' => 'Ahmad Delegate',
            'student_id' => 'QF101',
            'chest_number' => '101',
            'group_id' => $this->group1->id,
            'category' => 'Senior',
            'qr_token' => 'QR-AHMAD-101',
        ]);

        // 1. Individual program
        $progInd = Program::create([
            'name' => 'Elocution Arabic',
            'code' => 'ELOC01',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'eligibility' => 'A Zone',
            'points_first' => 10,
            'points_second' => 7,
            'points_third' => 5,
        ]);

        $entryInd = ProgramEntry::create([
            'program_id' => $progInd->id,
            'student_id' => $student->id,
            'group_id' => $this->group1->id,
            'chest_number' => '101',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        // 2. Group program where student is added in program_entry_participants
        $progGrp = Program::create([
            'name' => 'Group Song',
            'code' => 'GRP01',
            'category_id' => $this->category->id,
            'type' => 'group',
            'eligibility' => 'Mix Zone',
            'points_first' => 15,
            'points_second' => 10,
            'points_third' => 7,
        ]);

        $entryGrp = ProgramEntry::create([
            'program_id' => $progGrp->id,
            'student_id' => null,
            'group_id' => $this->group1->id,
            'chest_number' => 'G1',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        $student->participations()->attach($entryGrp->id, ['role' => 'member']);

        // Declare results for both
        $resInd = Result::create([
            'program_id' => $progInd->id,
            'result_number' => 1,
            'first_entry_id' => $entryInd->id,
            'first_grade' => 'A+',
            'status' => 'announced',
        ]);

        $resGrp = Result::create([
            'program_id' => $progGrp->id,
            'result_number' => 2,
            'second_entry_id' => $entryGrp->id,
            'second_grade' => 'A',
            'status' => 'announced',
        ]);

        // Distribute coins
        app(CoinRewardService::class)->distributeCoinsForResult($resInd);
        app(CoinRewardService::class)->distributeCoinsForResult($resGrp);

        // A. Student Portal Dashboard
        $response = $this->actingAs($studentUser)->get(route('student.dashboard'));
        $response->assertOk();
        $response->assertSee('Elocution Arabic');
        $response->assertSee('Group Song');
        $response->assertSee('1st Prize');
        $response->assertSee('2nd Prize');
        $response->assertSee('Grade A+');
        $response->assertSee('Grade A');

        // B. Public QR Scan Verification
        $scanResponse = $this->get(route('verify.student', $student->qr_token));
        $scanResponse->assertOk();
        $scanResponse->assertSee('Elocution Arabic');
        $scanResponse->assertSee('Group Song');
        $scanResponse->assertSee('1st Prize');
        $scanResponse->assertSee('2nd Prize');
        $scanResponse->assertSee('Wallet Balance');
        $scanResponse->assertSee('460'); // 300 for Ind 1st A+, 160 for Grp 2nd A
    }

    public function test_strategy_planner_filters_out_programs_with_incomplete_evaluation(): void
    {
        // Program 1: Evaluation Complete (present entries all evaluated)
        $progComplete = Program::create([
            'name' => 'Complete Quran Recitation',
            'code' => 'QUR01',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'eligibility' => 'A Zone',
            'points_first' => 10,
            'points_second' => 7,
            'points_third' => 5,
        ]);

        $entry1 = ProgramEntry::create([
            'program_id' => $progComplete->id,
            'group_id' => $this->group1->id,
            'chest_number' => '201',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        $judgeUser = User::factory()->create(['role' => 'judge']);
        $judge = Judge::create([
            'user_id' => $judgeUser->id,
            'name' => 'Judge One',
            'judge_code' => 'J01',
        ]);

        // Judge evaluated entry1
        ScoreSheet::create([
            'program_id' => $progComplete->id,
            'entry_id' => $entry1->id,
            'judge_id' => $judge->id,
            'is_submitted' => true,
            'total_score' => 95,
        ]);

        $resultComplete = Result::create([
            'program_id' => $progComplete->id,
            'result_number' => 1,
            'first_entry_id' => $entry1->id,
            'first_grade' => 'A+',
            'status' => 'draft',
        ]);

        // Program 2: Evaluation INCOMPLETE (2 present entries, but only 1 evaluated)
        $progIncomplete = Program::create([
            'name' => 'Incomplete Essay Writing',
            'code' => 'ESS01',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'eligibility' => 'B Zone',
            'points_first' => 10,
            'points_second' => 7,
            'points_third' => 5,
        ]);

        $entry2 = ProgramEntry::create([
            'program_id' => $progIncomplete->id,
            'group_id' => $this->group1->id,
            'chest_number' => '301',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        $entry3 = ProgramEntry::create([
            'program_id' => $progIncomplete->id,
            'group_id' => $this->group2->id,
            'chest_number' => '302',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        // Only entry2 was scored, entry3 was NOT scored!
        ScoreSheet::create([
            'program_id' => $progIncomplete->id,
            'entry_id' => $entry2->id,
            'judge_id' => $judge->id,
            'is_submitted' => true,
            'total_score' => 88,
        ]);

        $resultIncomplete = Result::create([
            'program_id' => $progIncomplete->id,
            'result_number' => 2,
            'first_entry_id' => $entry2->id,
            'first_grade' => 'A',
            'status' => 'draft',
        ]);

        // Strategy Planner response
        $response = $this->actingAs($this->admin)->get(route('admin.results.planner'));
        $response->assertOk();

        // 1. Verify in view data that incomplete evaluation result is filtered out
        $resultsData = $response->viewData('resultsData');
        $this->assertTrue($resultsData->contains('id', $resultComplete->id));
        $this->assertFalse($resultsData->contains('id', $resultIncomplete->id));
        $this->assertTrue($resultsData->pluck('program.name')->contains('Complete Quran Recitation'));
        $this->assertFalse($resultsData->pluck('program.name')->contains('Incomplete Essay Writing'));
    }

    public function test_evaluation_monitor_can_filter_and_print_pending_evaluations(): void
    {
        $judgeUser = User::factory()->create(['role' => 'judge']);
        $judge = Judge::create([
            'user_id' => $judgeUser->id,
            'name' => 'Judge Two',
            'judge_code' => 'J02',
        ]);

        // Program A: Evaluation Complete
        $progDone = Program::create([
            'name' => 'Done Painting',
            'code' => 'PNT01',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'eligibility' => 'A Zone',
        ]);

        $eDone = ProgramEntry::create([
            'program_id' => $progDone->id,
            'group_id' => $this->group1->id,
            'chest_number' => '401',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        ScoreSheet::create([
            'program_id' => $progDone->id,
            'entry_id' => $eDone->id,
            'judge_id' => $judge->id,
            'is_submitted' => true,
            'total_score' => 90,
        ]);

        // Program B: Evaluation Incomplete (present but not evaluated)
        $progPending = Program::create([
            'name' => 'Pending Debate',
            'code' => 'DEB01',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'eligibility' => 'B Zone',
        ]);

        ProgramEntry::create([
            'program_id' => $progPending->id,
            'group_id' => $this->group1->id,
            'chest_number' => '501',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        // 1. Filter Evaluation Monitor with status=pending_evaluation
        $monitorResponse = $this->actingAs($this->admin)->get(route('admin.evaluation-monitor.index', ['status' => 'pending_evaluation']));
        $monitorResponse->assertOk();

        $viewPrograms = $monitorResponse->viewData('programs');
        $this->assertTrue($viewPrograms->pluck('name')->contains('Pending Debate'));
        $this->assertFalse($viewPrograms->pluck('name')->contains('Done Painting'));

        // 2. Print Pending Evaluations List PDF (clean standalone view)
        $printResponse = $this->actingAs($this->admin)->get(route('admin.evaluation-monitor.print-pending'));
        $printResponse->assertOk();
        $printResponse->assertSee('EVALUATION PENDING COMPETITIONS LIST');
        $printResponse->assertSee('Pending Debate');
        $printResponse->assertDontSee('Done Painting');
    }
}
