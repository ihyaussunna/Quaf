<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use App\Services\CoinRewardService;
use App\Services\PointCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentPanelCoinsAndDeclaredResultsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $announcer;

    protected Group $group;

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

        $this->group = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#be1e2d',
        ]);

        $this->category = ProgramCategory::create([
            'name' => 'Senior',
            'slug' => 'senior',
        ]);
    }

    protected function makeStudent(string $name, string $chest): Student
    {
        return Student::create([
            'student_id' => 'STU-'.Str::upper(Str::random(6)),
            'name' => $name,
            'chest_number' => $chest,
            'group_id' => $this->group->id,
            'category_id' => $this->category->id,
            'qr_token' => Str::random(32),
        ]);
    }

    public function test_student_receives_coins_and_achievements_in_panel_when_result_sent_to_announcer(): void
    {
        $program = Program::create([
            'name' => 'Elocution Arabic',
            'code' => 'PROG-501',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'status' => 'completed',
        ]);

        $student = $this->makeStudent('Ahmad Student', 'CH-501');

        $user = User::factory()->create([
            'role' => 'student',
            'name' => $student->name,
            'email' => 'ahmad@student.quaf.org',
        ]);
        $student->update(['user_id' => $user->id]);

        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'group_id' => $this->group->id,
            'chest_number' => 'CH-501',
            'attendance_status' => 'present',
            'status' => 'verified',
        ]);

        // 1st prize with A+ grade in individual program = 200 coins
        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entry->id,
            'first_grade' => 'A+',
            'status' => 'verified',
            'is_media_published' => false,
        ]);

        // Transfer result to Announcer Desk
        $response = $this->actingAs($this->admin)->post(route('admin.results.send-to-announcer', $result));
        $response->assertRedirect();

        $freshResult = $result->fresh();
        $this->assertEquals('send', $freshResult->status);

        // Student coins must immediately be credited (1st with A+ = 200 coins)
        $student->refresh();
        $this->assertEquals(200, (int) $student->coins_cache);

        // Student logs into their dashboard
        $dashRes = $this->actingAs($user)->get(route('student.dashboard'));
        $dashRes->assertOk();

        // Must see Wallet Balance and Competition Results
        $dashRes->assertSee('QUAF Coin Wallet');
        $dashRes->assertSee('200');
        $dashRes->assertSee('Official Competition Results');
        $dashRes->assertSee('Elocution Arabic');
        $dashRes->assertSee('1st Prize');
        $dashRes->assertSee('Grade A+');
    }

    public function test_distribute_missing_coins_catches_up_all_declared_results(): void
    {
        $program = Program::create([
            'name' => 'Calligraphy Painting',
            'code' => 'PROG-601',
            'category_id' => $this->category->id,
            'type' => 'individual',
            'status' => 'completed',
        ]);

        $student = $this->makeStudent('Yusuf Artist', 'CH-601');

        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'group_id' => $this->group->id,
            'chest_number' => 'CH-601',
            'attendance_status' => 'present',
            'status' => 'verified',
        ]);

        // 2nd prize with A grade = 160 coins, marked as announced
        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => null,
            'second_entry_id' => $entry->id,
            'second_grade' => 'A',
            'status' => 'announced',
            'result_number' => 5,
            'is_media_published' => false,
        ]);

        $this->assertEquals(0, (int) $student->fresh()->coins_cache);

        // Recalculate Points Engine
        app(PointCalculationService::class)->recalculateAllPoints();

        $student->refresh();
        $this->assertEquals(160, (int) $student->coins_cache);

        $summary = app(CoinRewardService::class)->getStudentCoinSummary($student, false);
        $this->assertEquals(160, $summary['balance']);
        $this->assertEquals(160, $summary['competition_earned']);
    }
}
