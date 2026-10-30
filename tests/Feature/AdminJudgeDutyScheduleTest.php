<?php

namespace Tests\Feature;

use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminJudgeDutyScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Judge $judge1;

    private Judge $judge2;

    private Stage $stage1;

    private ProgramCategory $category;

    private Program $prog1;

    private Program $prog2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Super Administrator',
            'email' => 'admin@quaf.test',
        ]);

        $this->stage1 = Stage::create([
            'name' => 'Stage 01 — Grand Auditorium',
            'code' => 'STG-01',
            'status' => 'active',
        ]);

        $this->category = ProgramCategory::create([
            'name' => 'Elocution & Speech',
            'slug' => 'speech',
            'code' => 'SP',
        ]);

        $this->prog1 = Program::create([
            'name' => 'Elocution Malayalam',
            'malayalam_name' => 'പ്രസംഗം മലയാളം',
            'code' => 'PRG-101',
            'category_id' => $this->category->id,
            'stage_id' => $this->stage1->id,
            'scheduled_time' => now()->addHours(3),
            'duration_minutes' => 45,
            'status' => 'scheduled',
        ]);

        $this->prog2 = Program::create([
            'name' => 'Qawwali Performance',
            'malayalam_name' => 'ഖവ്വാലി',
            'code' => 'PRG-102',
            'category_id' => $this->category->id,
            'stage_id' => $this->stage1->id,
            'scheduled_time' => now()->addHours(6),
            'duration_minutes' => 60,
            'status' => 'scheduled',
        ]);

        $u1 = User::factory()->create(['role' => 'judge', 'name' => 'Rafi Ahsani']);
        $this->judge1 = Judge::create([
            'user_id' => $u1->id,
            'name' => 'Rafi Ahsani',
            'access_code' => '7482',
            'contact' => '9847000001',
            'designation' => 'Senior Juror',
            'specialization' => 'Speech & Fiqh',
        ]);
        $this->judge1->programs()->attach([$this->prog1->id, $this->prog2->id]);

        $u2 = User::factory()->create(['role' => 'judge', 'name' => 'Shahabas Master']);
        $this->judge2 = Judge::create([
            'user_id' => $u2->id,
            'name' => 'Shahabas Master',
            'access_code' => '8591',
            'contact' => '9847000002',
            'designation' => 'Faculty Evaluator',
            'specialization' => 'Wa’az & Speech',
        ]);
        $this->judge2->programs()->attach([$this->prog1->id]);
    }

    public function test_admin_duty_schedule_dashboard_loads_with_stats_and_programs(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.judges.duty'));

        $response->assertOk();
        $response->assertSee('Judges Program Schedule', false);
        $response->assertSee('Duty Roster');
        $response->assertSee('Rafi Ahsani');
        $response->assertSee('Shahabas Master');
        $response->assertSee('Elocution Malayalam');
        $response->assertSee('Qawwali Performance');
        $response->assertSee('PRG-101');
        $response->assertSee('Stage 01 — Grand Auditorium');
        $response->assertSee('7482');
        $response->assertSee('Print Duty Slip (PDF)');

        $judges = $response->viewData('judges');
        $this->assertCount(2, $judges);

        $stats = $response->viewData('stats');
        $this->assertSame(2, $stats['total_judges']);
        $this->assertSame(2, $stats['assigned_judges']);
        $this->assertSame(0, $stats['unassigned_judges']);
        $this->assertSame(3, $stats['total_duties']);
    }

    public function test_admin_duty_schedule_filters_by_judge(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.judges.duty', [
            'judge_id' => $this->judge2->id,
        ]));

        $response->assertOk();
        $judges = $response->viewData('judges');
        $this->assertCount(1, $judges);
        $this->assertSame($this->judge2->id, $judges->first()->id);
        $this->assertSame('Shahabas Master', $judges->first()->name);

        // Judge 2 has 1 program: Elocution Malayalam
        $this->assertCount(1, $judges->first()->sorted_programs);
        $this->assertSame('Elocution Malayalam', $judges->first()->sorted_programs->first()->name);
    }

    public function test_admin_duty_print_per_page_mode_includes_header_and_page_break(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.judges.duty.print', [
            'mode' => 'per_page',
        ]));

        $response->assertOk();
        // Official QUAF PDF Header must be present
        $response->assertSee('OFFICIAL JUDGE DUTY ALLOCATION');
        $response->assertSee('QUAF 2026 Markaz Arts Festival');
        $response->assertSee('print-pdf-header.svg');

        // Page break class for 1 Judge per page on the container div
        $response->assertSee('judge-sheet judge-sheet-break');

        // Verify judge cards and sign off signatures
        $response->assertSee('Rafi Ahsani');
        $response->assertSee('Shahabas Master');
        $response->assertSee('Program Committee Convener');
        $response->assertSee('Festival General Secretary');
        $response->assertSee('Judge Acceptance Signature');

        $judges = $response->viewData('judges');
        $this->assertCount(2, $judges);
    }

    public function test_admin_duty_print_continuous_mode_omits_forced_page_break(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.judges.duty.print', [
            'mode' => 'continuous',
        ]));

        $response->assertOk();
        $response->assertSee('OFFICIAL JUDGE DUTY ALLOCATION');
        // The container div should not have the page break class applied
        $response->assertDontSee('judge-sheet judge-sheet-break');

        $judges = $response->viewData('judges');
        $this->assertCount(2, $judges);
    }

    public function test_admin_duty_print_single_judge_duty_slip(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.judges.duty.print', [
            'judge_id' => $this->judge1->id,
        ]));

        $response->assertOk();
        $response->assertSee('Judge Duty Slip — Rafi Ahsani');
        $response->assertSee('Rafi Ahsani');
        $response->assertSee('7482');
        $response->assertSee('Elocution Malayalam');
        $response->assertSee('Qawwali Performance');

        // Only Judge 1 is in the printed sheets collection
        $judges = $response->viewData('judges');
        $this->assertCount(1, $judges);
        $this->assertSame($this->judge1->id, $judges->first()->id);
        $this->assertSame($this->judge1->id, $response->viewData('singleJudge')->id);
    }
}
