<?php

namespace Tests\Feature;

use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Judge;
use App\Models\PointsTransaction;
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

class DenseRankingAndContinuousRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $leaderUser;

    protected Group $group;

    protected Zone $zone;

    protected function setUp(): void
    {
        parent::setUp();

        FestivalSetting::set('registration_open', '1');

        $this->admin = User::create([
            'name' => 'Admin Controller',
            'email' => 'admin@quaf.test',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->leaderUser = User::create([
            'name' => 'Group Leader One',
            'email' => 'leader1@quaf.test',
            'password' => Hash::make('secret123'),
            'role' => 'group_leader',
            'is_active' => true,
        ]);

        $this->group = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#56286b',
            'leader_id' => $this->leaderUser->id,
        ]);

        $this->zone = Zone::firstOrCreate(
            ['slug' => 'a-zone'],
            ['name' => 'A Zone', 'code' => 'ZONE-A']
        );
    }

    public function test_specified_results_searches_by_program_code_or_number(): void
    {
        $cat = ProgramCategory::firstOrCreate(['slug' => 'general'], ['name' => 'General', 'code' => 'GEN']);

        // Program with code Q9-204 and another program
        $prog204 = Program::create([
            'category_id' => $cat->id,
            'code' => 'Q9-204',
            'name' => 'Abstract Painting',
            'slug' => 'abstract-painting',
            'type' => 'individual',
            'zone_id' => $this->zone->id,
            'participant_count' => 1,
            'status' => 'upcoming',
        ]);

        // Search by numeric part "204"
        $res = $this->actingAs($this->admin)->get(route('admin.results.specified', ['program_id' => '204']));
        $res->assertStatus(200);
        $res->assertSee('Result Q9-204');
        $res->assertSee('Abstract Painting');

        // Search by full code "Q9-204"
        $resFull = $this->actingAs($this->admin)->get(route('admin.results.specified', ['program_id' => 'Q9-204']));
        $resFull->assertStatus(200);
        $resFull->assertSee('Result Q9-204');
    }

    public function test_dense_ranking_assigns_same_rank_to_tied_scores(): void
    {
        $cat = ProgramCategory::firstOrCreate(['slug' => 'general'], ['name' => 'General', 'code' => 'GEN']);

        $prog = Program::create([
            'category_id' => $cat->id,
            'code' => 'Q9-301',
            'name' => 'Essay Writing',
            'slug' => 'essay-writing',
            'type' => 'individual',
            'zone_id' => $this->zone->id,
            'participant_count' => 1,
            'status' => 'upcoming',
        ]);

        $judgeUser = User::create([
            'name' => 'Judge Evaluation',
            'email' => 'judge_eval@quaf.test',
            'password' => Hash::make('secret123'),
            'role' => 'judge',
            'is_active' => true,
        ]);

        $judge = Judge::create([
            'name' => 'Judge Evaluation',
            'access_code' => '8888',
            'user_id' => $judgeUser->id,
        ]);

        // Create 4 students and entries
        $scores = [88.0, 88.0, 87.0, 65.0];
        $entries = [];

        foreach ($scores as $i => $sc) {
            $student = Student::create([
                'group_id' => $this->group->id,
                'zone_id' => $this->zone->id,
                'student_id' => 'QF'.(5000 + $i),
                'name' => 'Candidate '.($i + 1),
                'qr_token' => 'qr-test-'.$i,
                'is_active' => true,
            ]);

            $entry = ProgramEntry::create([
                'program_id' => $prog->id,
                'student_id' => $student->id,
                'group_id' => $this->group->id,
                'chest_number' => $student->student_id,
                'status' => 'verified',
                'attendance_status' => 'present',
            ]);

            ScoreSheet::create([
                'judge_id' => $judge->id,
                'program_id' => $prog->id,
                'entry_id' => $entry->id,
                'total_score' => $sc,
                'is_submitted' => true,
            ]);

            $entries[] = $entry;
        }

        // Test in specified results
        $res = $this->actingAs($this->admin)->get(route('admin.results.specified', ['program_id' => 'Q9-301']));
        $res->assertStatus(200);

        // Verify ranked entries passed to the view
        $ranked = $res->viewData('rankedEntries');
        $this->assertCount(4, $ranked);

        // Dense ranking: 88, 88 -> first, first; 87 -> second; 65 -> third
        $this->assertEquals(88.0, $ranked[0]->computed_score);
        $this->assertEquals('first', $ranked[0]->computed_rank);

        $this->assertEquals(88.0, $ranked[1]->computed_score);
        $this->assertEquals('first', $ranked[1]->computed_rank);

        $this->assertEquals(87.0, $ranked[2]->computed_score);
        $this->assertEquals('second', $ranked[2]->computed_rank);

        $this->assertEquals(65.0, $ranked[3]->computed_score);
        $this->assertEquals('third', $ranked[3]->computed_rank);
    }

    public function test_judge_can_save_evaluation_with_official_grade_scale(): void
    {
        $cat = ProgramCategory::firstOrCreate(['slug' => 'general'], ['name' => 'General', 'code' => 'GEN']);

        $prog = Program::create([
            'category_id' => $cat->id,
            'code' => 'Q9-401',
            'name' => 'Monoact',
            'slug' => 'monoact',
            'type' => 'individual',
            'zone_id' => $this->zone->id,
            'participant_count' => 1,
            'status' => 'in_progress',
        ]);

        $judgeUser = User::create([
            'name' => 'Judge Monoact',
            'email' => 'judge_monoact@quaf.test',
            'password' => Hash::make('secret123'),
            'role' => 'judge',
            'is_active' => true,
        ]);

        $judge = Judge::create([
            'name' => 'Judge Monoact',
            'access_code' => '9999',
            'user_id' => $judgeUser->id,
        ]);

        $prog->judges()->attach($judge->id);

        $student = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $this->zone->id,
            'student_id' => 'QF6001',
            'name' => 'Monoact Actor',
            'qr_token' => 'qr-monoact-1',
            'is_active' => true,
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $prog->id,
            'student_id' => $student->id,
            'group_id' => $this->group->id,
            'chest_number' => 'QF6001',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        // Submit evaluation with score 68 (Grade B on 60-69 scale)
        $res = $this->actingAs($judgeUser)->postJson(route('judge.evaluate.save', [$prog, $entry]), [
            'total_score' => 68.0,
            'remarks' => 'Good performance',
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success' => true,
            'total_score' => 68.0,
            'grade' => 'B',
        ]);

        $sheet = ScoreSheet::where('entry_id', $entry->id)->first();
        $this->assertNotNull($sheet);
        $this->assertEquals('B', $sheet->criteria_scores['grade'] ?? null);

        // Verify that podium was auto-assigned from judge score
        $progResult = Result::where('program_id', $prog->id)->first();
        $this->assertNotNull($progResult);
        $this->assertEquals($entry->id, $progResult->first_entry_id);

        // Publish and verify Grade B awards 3 points (Rule 11)
        $progResult->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        PointCalculationService::recalculateAll();

        $gradeTx = PointsTransaction::where('entry_id', $entry->id)
            ->where('source_type', 'GRADE')
            ->first();

        $this->assertNotNull($gradeTx);
        $this->assertEquals(3, $gradeTx->points);
    }

    public function test_leader_continuous_registration_returns_json_and_supports_multi_students(): void
    {
        $cat = ProgramCategory::firstOrCreate(['slug' => 'general'], ['name' => 'General', 'code' => 'GEN']);

        // Individual program with max_participants_per_group = 2
        $prog = Program::create([
            'category_id' => $cat->id,
            'code' => 'Q9-501',
            'name' => 'Pencil Drawing',
            'slug' => 'pencil-drawing',
            'type' => 'individual',
            'zone_id' => $this->zone->id,
            'participant_count' => 2,
            'max_participants_per_group' => 2,
            'status' => 'upcoming',
        ]);

        $st1 = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $this->zone->id,
            'student_id' => 'QF7001',
            'name' => 'Artist One',
            'qr_token' => 'qr-art-1',
            'is_active' => true,
        ]);

        $st2 = Student::create([
            'group_id' => $this->group->id,
            'zone_id' => $this->zone->id,
            'student_id' => 'QF7002',
            'name' => 'Artist Two',
            'qr_token' => 'qr-art-2',
            'is_active' => true,
        ]);

        // Submit registration for both students together via AJAX
        $res = $this->actingAs($this->leaderUser)->postJson(route('leader.registrations.store'), [
            'program_id' => $prog->id,
            'zone' => $this->zone->name,
            'student_ids' => [$st1->id, $st2->id],
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success' => true,
            'registered_count' => 2,
            'program_id' => $prog->id,
        ]);

        $this->assertEquals(2, ProgramEntry::where('program_id', $prog->id)->count());
        $this->assertDatabaseHas('program_entries', [
            'program_id' => $prog->id,
            'student_id' => $st1->id,
            'chest_number' => 'QF7001',
        ]);
        $this->assertDatabaseHas('program_entries', [
            'program_id' => $prog->id,
            'student_id' => $st2->id,
            'chest_number' => 'QF7002',
        ]);
    }

    public function test_view_marks_assigns_dense_ranking_and_tied_status_to_matching_scores(): void
    {
        $cat = ProgramCategory::firstOrCreate(['slug' => 'general'], ['name' => 'General', 'code' => 'GEN']);

        $prog = Program::create([
            'category_id' => $cat->id,
            'code' => 'Q9-189',
            'name' => 'Tahfeezul Burda',
            'slug' => 'tahfeezul-burda',
            'type' => 'individual',
            'zone_id' => $this->zone->id,
            'participant_count' => 1,
            'status' => 'upcoming',
        ]);

        $judgeUser = User::create([
            'name' => 'Judge Burda',
            'email' => 'judge_burda@quaf.test',
            'password' => Hash::make('secret123'),
            'role' => 'judge',
            'is_active' => true,
        ]);

        $judge = Judge::create([
            'name' => 'Judge Burda',
            'access_code' => '7777',
            'user_id' => $judgeUser->id,
        ]);

        // Candidates: E=90, B=80, D=65, A=65
        $candidateData = [
            ['code' => 'E', 'score' => 90.0, 'chest' => 'QF8001'],
            ['code' => 'B', 'score' => 80.0, 'chest' => 'QF8002'],
            ['code' => 'D', 'score' => 65.0, 'chest' => 'QF8003'],
            ['code' => 'A', 'score' => 65.0, 'chest' => 'QF8004'],
        ];

        foreach ($candidateData as $c) {
            $student = Student::create([
                'group_id' => $this->group->id,
                'zone_id' => $this->zone->id,
                'student_id' => $c['chest'],
                'name' => 'Candidate '.$c['code'],
                'qr_token' => 'qr-'.$c['code'],
                'is_active' => true,
            ]);

            $entry = ProgramEntry::create([
                'program_id' => $prog->id,
                'student_id' => $student->id,
                'group_id' => $this->group->id,
                'chest_number' => $student->student_id,
                'code_letter' => $c['code'],
                'status' => 'verified',
                'attendance_status' => 'present',
            ]);

            ScoreSheet::create([
                'judge_id' => $judge->id,
                'program_id' => $prog->id,
                'entry_id' => $entry->id,
                'total_score' => $c['score'],
                'is_submitted' => true,
            ]);
        }

        // Test PointCalculationService::determinePodiumForProgram
        $podium = PointCalculationService::determinePodiumForProgram($prog);
        $this->assertTrue($podium['has_ties']);
        $this->assertCount(1, $podium['first_all']);
        $this->assertCount(1, $podium['second_all']);
        $this->assertCount(2, $podium['third_all']);
        $this->assertEquals(90.0, $podium['first']->computed_avg_score);
        $this->assertEquals(80.0, $podium['second']->computed_avg_score);
        $this->assertEquals(65.0, $podium['third']->computed_avg_score);

        // Test MarkEntryController::viewMarks
        $res = $this->actingAs($this->admin)->get(route('admin.mark-entry.view-marks', ['program' => $prog->id]));
        $res->assertStatus(200);

        $entries = $res->viewData('entries');
        $this->assertCount(4, $entries);

        $entryMap = $entries->keyBy('code_letter');

        // Candidate E (90) -> Rank 1, not tied
        $this->assertEquals(1, $entryMap['E']->rank);
        $this->assertFalse($entryMap['E']->is_tied);

        // Candidate B (80) -> Rank 2, not tied
        $this->assertEquals(2, $entryMap['B']->rank);
        $this->assertFalse($entryMap['B']->is_tied);

        // Candidate D (65) -> Rank 3, tied!
        $this->assertEquals(3, $entryMap['D']->rank);
        $this->assertTrue($entryMap['D']->is_tied);

        // Candidate A (65) -> Rank 3, tied! (Previously unranked / missing badge)
        $this->assertEquals(3, $entryMap['A']->rank);
        $this->assertTrue($entryMap['A']->is_tied);
    }
}
