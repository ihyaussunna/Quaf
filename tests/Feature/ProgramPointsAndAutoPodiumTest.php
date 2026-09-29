<?php

namespace Tests\Feature;

use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Judge;
use App\Models\PointSetting;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\ScoreSheet;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use App\Services\PointCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProgramPointsAndAutoPodiumTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Zone $zone;

    protected Group $groupA;

    protected Group $groupB;

    protected Group $groupC;

    protected ProgramCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        FestivalSetting::set('registration_open', '1');

        PointSetting::firstOrCreate([], [
            'first_place_points' => 5,
            'second_place_points' => 3,
            'third_place_points' => 1,
            'participation_points' => 0,
            'group_multiplier' => 1.0,
        ]);

        $this->admin = User::create([
            'name' => 'Admin Controller',
            'email' => 'admin@quaf.test',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->zone = Zone::firstOrCreate(
            ['slug' => 'a-zone'],
            ['name' => 'A Zone', 'code' => 'ZONE-A']
        );

        $this->category = ProgramCategory::firstOrCreate(
            ['slug' => 'general'],
            ['name' => 'General', 'code' => 'GEN']
        );

        $this->groupA = Group::create([
            'name' => 'Pacto Hikmic',
            'code' => 'PACTO',
            'slug' => 'pacto-hikmic',
            'color_hex' => '#2e3192',
        ]);

        $this->groupB = Group::create([
            'name' => 'Yugo Rushdic',
            'code' => 'YUGO',
            'slug' => 'yugo-rushdic',
            'color_hex' => '#ad1e56',
        ]);

        $this->groupC = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#56286b',
        ]);
    }

    public function test_grade_scale_and_points_match_rulebook(): void
    {
        // A+ (90-100%): 6 pts
        $aPlus = PointCalculationService::getGradeFromScore(95.0);
        $this->assertEquals('A+', $aPlus['grade']);
        $this->assertEquals(6, $aPlus['points']);

        $aPlusBoundary = PointCalculationService::getGradeFromScore(90.0);
        $this->assertEquals('A+', $aPlusBoundary['grade']);
        $this->assertEquals(6, $aPlusBoundary['points']);

        // A (70-89.99%): 5 pts
        $a = PointCalculationService::getGradeFromScore(89.5);
        $this->assertEquals('A', $a['grade']);
        $this->assertEquals(5, $a['points']);

        $aBoundary = PointCalculationService::getGradeFromScore(70.0);
        $this->assertEquals('A', $aBoundary['grade']);
        $this->assertEquals(5, $aBoundary['points']);

        // B (60-69.99%): 3 pts (No B+)
        $b = PointCalculationService::getGradeFromScore(68.0);
        $this->assertEquals('B', $b['grade']);
        $this->assertEquals(3, $b['points']);

        $bBoundary = PointCalculationService::getGradeFromScore(60.0);
        $this->assertEquals('B', $bBoundary['grade']);
        $this->assertEquals(3, $bBoundary['points']);

        // C (50-59.99%): 1 pt
        $c = PointCalculationService::getGradeFromScore(55.0);
        $this->assertEquals('C', $c['grade']);
        $this->assertEquals(1, $c['points']);

        $cBoundary = PointCalculationService::getGradeFromScore(50.0);
        $this->assertEquals('C', $cBoundary['grade']);
        $this->assertEquals(1, $cBoundary['points']);

        // Below 50%: No grade, 0 pts
        $fail = PointCalculationService::getGradeFromScore(49.9);
        $this->assertNull($fail['grade']);
        $this->assertEquals(0, $fail['points']);

        // Assert B+ is not in GRADE_POINTS
        $this->assertArrayNotHasKey('B+', PointCalculationService::GRADE_POINTS);
    }

    public function test_position_points_matrix_for_individual_and_groups(): void
    {
        // 1. Individual: 1st=5, 2nd=3, 3rd=1
        $indProg = Program::create([
            'category_id' => $this->category->id,
            'code' => 'Q9-IND',
            'name' => 'Elocution',
            'type' => 'individual',
            'zone_id' => $this->zone->id,
        ]);
        $this->assertEquals([1 => 5, 2 => 3, 3 => 1], PointCalculationService::getPositionPointsForProgram($indProg));

        // 2. Group (2 members): 1st=7, 2nd=5, 3rd=3
        $grp2 = Program::create([
            'category_id' => $this->category->id,
            'code' => 'Q9-G2',
            'name' => 'Duo Debate',
            'type' => 'group',
            'participant_count' => 2,
            'zone_id' => $this->zone->id,
        ]);
        $this->assertEquals([1 => 7, 2 => 5, 3 => 3], PointCalculationService::getPositionPointsForProgram($grp2));

        // 3. Group (3 members): 1st=10, 2nd=7, 3rd=4
        $grp3 = Program::create([
            'category_id' => $this->category->id,
            'code' => 'Q9-G3',
            'name' => 'Trio Discussion',
            'type' => 'group',
            'participant_count' => 3,
            'zone_id' => $this->zone->id,
        ]);
        $this->assertEquals([1 => 10, 2 => 7, 3 => 4], PointCalculationService::getPositionPointsForProgram($grp3));

        // 4. Group (4 or 5 members): 1st=15, 2nd=10, 3rd=5
        $grp5 = Program::create([
            'category_id' => $this->category->id,
            'code' => 'Q9-G5',
            'name' => 'Qawwali',
            'type' => 'group',
            'participant_count' => 5,
            'zone_id' => $this->zone->id,
        ]);
        $this->assertEquals([1 => 15, 2 => 10, 3 => 5], PointCalculationService::getPositionPointsForProgram($grp5));

        // 5. General Shifting (>5 or contains shift/general): 1st=20, 2nd=15, 3rd=10
        $grpShift = Program::create([
            'category_id' => $this->category->id,
            'code' => 'Q9-SHIFT',
            'name' => 'General Shifting Choir',
            'type' => 'group',
            'participant_count' => 8,
            'zone_id' => $this->zone->id,
        ]);
        $this->assertEquals([1 => 20, 2 => 15, 3 => 10], PointCalculationService::getPositionPointsForProgram($grpShift));
    }

    public function test_automatic_podium_ranking_and_points_calculation(): void
    {
        $prog = Program::create([
            'category_id' => $this->category->id,
            'code' => 'Q9-AUTO',
            'name' => 'Choral Verse',
            'type' => 'individual',
            'zone_id' => $this->zone->id,
            'status' => 'in_progress',
        ]);

        $judgeUser = User::create([
            'name' => 'Jury Chief',
            'email' => 'jury_chief@quaf.test',
            'password' => Hash::make('secret123'),
            'role' => 'judge',
            'is_active' => true,
        ]);

        $judge = Judge::create([
            'name' => 'Jury Chief',
            'access_code' => '7777',
            'user_id' => $judgeUser->id,
        ]);

        $prog->judges()->attach($judge->id);

        // Create 3 students representing 3 groups
        $studentA = Student::create(['group_id' => $this->groupA->id, 'zone_id' => $this->zone->id, 'student_id' => 'ST-A', 'name' => 'Alice', 'qr_token' => 'qr-a', 'is_active' => true]);
        $studentB = Student::create(['group_id' => $this->groupB->id, 'zone_id' => $this->zone->id, 'student_id' => 'ST-B', 'name' => 'Bob', 'qr_token' => 'qr-b', 'is_active' => true]);
        $studentC = Student::create(['group_id' => $this->groupC->id, 'zone_id' => $this->zone->id, 'student_id' => 'ST-C', 'name' => 'Charlie', 'qr_token' => 'qr-c', 'is_active' => true]);

        $entryA = ProgramEntry::create(['program_id' => $prog->id, 'student_id' => $studentA->id, 'group_id' => $this->groupA->id, 'chest_number' => '101', 'status' => 'verified', 'attendance_status' => 'present']);
        $entryB = ProgramEntry::create(['program_id' => $prog->id, 'student_id' => $studentB->id, 'group_id' => $this->groupB->id, 'chest_number' => '102', 'status' => 'verified', 'attendance_status' => 'present']);
        $entryC = ProgramEntry::create(['program_id' => $prog->id, 'student_id' => $studentC->id, 'group_id' => $this->groupC->id, 'chest_number' => '103', 'status' => 'verified', 'attendance_status' => 'present']);

        // Judge evaluates:
        // Entry A: 94.0 -> Grade A+
        // Entry B: 76.0 -> Grade A
        // Entry C: 62.0 -> Grade B
        $this->actingAs($judgeUser)->postJson(route('judge.evaluate.save', [$prog, $entryA]), ['total_score' => 94.0]);
        $this->actingAs($judgeUser)->postJson(route('judge.evaluate.save', [$prog, $entryB]), ['total_score' => 76.0]);
        $this->actingAs($judgeUser)->postJson(route('judge.evaluate.save', [$prog, $entryC]), ['total_score' => 62.0]);

        // Auto podium resolution
        $podium = PointCalculationService::determinePodiumForProgram($prog);

        $this->assertEquals($entryA->id, $podium['first']->id);
        $this->assertEquals('A+', $podium['first']->computed_grade);
        $this->assertEquals(94.0, $podium['first']->computed_avg_score);

        $this->assertEquals($entryB->id, $podium['second']->id);
        $this->assertEquals('A', $podium['second']->computed_grade);
        $this->assertEquals(76.0, $podium['second']->computed_avg_score);

        $this->assertEquals($entryC->id, $podium['third']->id);
        $this->assertEquals('B', $podium['third']->computed_grade);
        $this->assertEquals(62.0, $podium['third']->computed_avg_score);

        // Verify Result model has automatically assigned podium
        $result = Result::where('program_id', $prog->id)->first();
        $this->assertNotNull($result);
        $this->assertEquals($entryA->id, $result->first_entry_id);
        $this->assertEquals($entryB->id, $result->second_entry_id);
        $this->assertEquals($entryC->id, $result->third_entry_id);

        // Publish result
        $result->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        PointCalculationService::recalculateAll();

        // 1st place: 5 pos pts + 6 A+ pts = 11 pts for Group A
        $this->assertEquals(11, $this->groupA->fresh()->points_cache);

        // 2nd place: 3 pos pts + 5 A pts = 8 pts for Group B
        $this->assertEquals(8, $this->groupB->fresh()->points_cache);

        // 3rd place: 1 pos pt + 3 B pts = 4 pts for Group C
        $this->assertEquals(4, $this->groupC->fresh()->points_cache);

        // Rankings: 1 = Group A, 2 = Group B, 3 = Group C
        $this->assertEquals(1, $this->groupA->fresh()->rank_cache);
        $this->assertEquals(2, $this->groupB->fresh()->rank_cache);
        $this->assertEquals(3, $this->groupC->fresh()->rank_cache);
    }

    public function test_admin_can_destroy_result_and_recalculate_points(): void
    {
        $prog = Program::create([
            'category_id' => $this->category->id,
            'code' => 'Q9-DEL',
            'name' => 'To Delete Program',
            'type' => 'individual',
            'zone_id' => $this->zone->id,
            'status' => 'completed',
        ]);

        $student = Student::create(['group_id' => $this->groupA->id, 'zone_id' => $this->zone->id, 'student_id' => 'ST-DEL', 'name' => 'Del Student', 'qr_token' => 'qr-del', 'is_active' => true]);
        $entry = ProgramEntry::create(['program_id' => $prog->id, 'student_id' => $student->id, 'group_id' => $this->groupA->id, 'chest_number' => '999', 'status' => 'verified', 'attendance_status' => 'present']);

        $result = Result::create([
            'program_id' => $prog->id,
            'first_entry_id' => $entry->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        PointCalculationService::recalculateAll();
        $this->assertGreaterThan(0, $this->groupA->fresh()->points_cache);

        // Send DELETE to destroy result
        $res = $this->actingAs($this->admin)->delete(route('admin.results.destroy', $result));
        $res->assertRedirect(route('admin.results.index'));
        $res->assertSessionHas('success');

        $this->assertDatabaseMissing('results', ['id' => $result->id]);
        $this->assertEquals(0, $this->groupA->fresh()->points_cache);
        $this->assertEquals('upcoming', $prog->fresh()->status);
    }

    public function test_marks_handler_only_shows_evaluated_programs(): void
    {
        // 1. Program without any evaluation / result
        $unjudgedProg = Program::create([
            'category_id' => $this->category->id,
            'code' => 'Q9-UNJUDGED',
            'name' => 'Unjudged Program Off Stage',
            'type' => 'individual',
            'zone_id' => $this->zone->id,
            'status' => 'upcoming',
        ]);

        // 2. Program with submitted judge evaluation / result
        $judgedProg = Program::create([
            'category_id' => $this->category->id,
            'code' => 'Q9-JUDGED',
            'name' => 'Judged Completed Program',
            'type' => 'individual',
            'zone_id' => $this->zone->id,
            'status' => 'in_progress',
        ]);

        $student = Student::create(['group_id' => $this->groupA->id, 'zone_id' => $this->zone->id, 'student_id' => 'ST-JUD', 'name' => 'Judged Student', 'qr_token' => 'qr-jud', 'is_active' => true]);
        $entry = ProgramEntry::create(['program_id' => $judgedProg->id, 'student_id' => $student->id, 'group_id' => $this->groupA->id, 'chest_number' => '888', 'status' => 'verified', 'attendance_status' => 'present']);

        $judgeUser = User::create([
            'name' => 'Handler Judge',
            'email' => 'handler_judge@quaf.test',
            'password' => Hash::make('secret123'),
            'role' => 'judge',
            'is_active' => true,
        ]);

        $judge = Judge::create([
            'name' => 'Handler Judge',
            'access_code' => '5555',
            'user_id' => $judgeUser->id,
        ]);

        $judgedProg->judges()->attach($judge->id);

        ScoreSheet::create([
            'judge_id' => $judge->id,
            'program_id' => $judgedProg->id,
            'entry_id' => $entry->id,
            'total_score' => 85.0,
            'is_submitted' => true,
        ]);

        PointCalculationService::autoAssignResultPodium($judgedProg);

        // Fetch mark handler page
        $res = $this->actingAs($this->admin)->get(route('admin.mark-entry.handler'));
        $res->assertStatus(200);

        // Evaluated program MUST be present in handler list
        $viewPrograms = $res->viewData('programs');
        $this->assertTrue($viewPrograms->pluck('name')->contains('Judged Completed Program'));
        // Unjudged program MUST NOT be in handler list
        $this->assertFalse($viewPrograms->pluck('name')->contains('Unjudged Program Off Stage'));
    }
}
