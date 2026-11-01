<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\PointsTransaction;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use App\Services\PointCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupFinanceAndGraceMarksTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Group $groupA;

    protected Group $groupB;

    protected ProgramCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Controller',
            'email' => 'admin@quaf.test',
        ]);

        $this->groupA = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LF',
            'slug' => 'lumo-fikric',
            'color_hex' => '#56286B',
        ]);

        $this->groupB = Group::create([
            'name' => 'Pacto Hikmic',
            'code' => 'PH',
            'slug' => 'pacto-hikmic',
            'color_hex' => '#2E3192',
        ]);

        $this->category = ProgramCategory::create([
            'name' => 'General Category',
            'code' => 'GEN',
            'slug' => 'gen-category',
            'display_order' => 1,
        ]);
    }

    public function test_admin_can_update_single_group_finance_and_grace_marks(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.points.group-marks'), [
            'group_id' => $this->groupA->id,
            'finance_points' => 25,
            'finance_remarks' => 'Camp fund target reached',
            'grace_points' => 10,
            'grace_remarks' => 'Discipline merit bonus',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->groupA->refresh();
        $this->assertSame(25, $this->groupA->finance_points);
        $this->assertSame('Camp fund target reached', $this->groupA->finance_remarks);
        $this->assertSame(10, $this->groupA->grace_points);
        $this->assertSame('Discipline merit bonus', $this->groupA->grace_remarks);
        $this->assertSame(35, $this->groupA->points_cache);

        // Verify PointsTransaction records
        $this->assertDatabaseHas('points_transactions', [
            'group_id' => $this->groupA->id,
            'source_type' => 'FINANCE',
            'points' => 25,
        ]);

        $this->assertDatabaseHas('points_transactions', [
            'group_id' => $this->groupA->id,
            'source_type' => 'GRACE',
            'points' => 10,
        ]);
    }

    public function test_admin_can_batch_update_finance_and_grace_marks_for_multiple_groups(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.points.group-marks'), [
            'groups' => [
                [
                    'id' => $this->groupA->id,
                    'finance_points' => 30,
                    'finance_remarks' => 'LF Fund Bonus',
                    'grace_points' => 5,
                    'grace_remarks' => 'LF Discipline',
                ],
                [
                    'id' => $this->groupB->id,
                    'finance_points' => 40,
                    'finance_remarks' => 'PH Top Collector',
                    'grace_points' => 15,
                    'grace_remarks' => 'PH Camp Winner',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->groupA->refresh();
        $this->groupB->refresh();

        $this->assertSame(30, $this->groupA->finance_points);
        $this->assertSame(5, $this->groupA->grace_points);
        $this->assertSame(35, $this->groupA->points_cache);

        $this->assertSame(40, $this->groupB->finance_points);
        $this->assertSame(15, $this->groupB->grace_points);
        $this->assertSame(55, $this->groupB->points_cache);

        // Group B should be rank 1, Group A rank 2
        $this->assertSame(1, $this->groupB->rank_cache);
        $this->assertSame(2, $this->groupA->rank_cache);
    }

    public function test_recalculate_all_points_preserves_and_correctly_aggregates_competition_and_special_marks(): void
    {
        // 1. Create a competition program and result
        $program = Program::create([
            'name' => 'Elocution English',
            'code' => 'ELO101',
            'category_id' => $this->category->id,
            'program_type' => 'individual',
            'participant_count' => 1,
            'stage_type' => 'stage',
            'status' => 'completed',
        ]);

        $studentA = Student::create([
            'name' => 'Zayd',
            'student_id' => 'QF1001',
            'group_id' => $this->groupA->id,
            'category' => 'General',
            'gender' => 'Male',
            'qr_token' => 'qr_token_1001',
        ]);

        $studentB = Student::create([
            'name' => 'Bilal',
            'student_id' => 'QF1002',
            'group_id' => $this->groupB->id,
            'category' => 'General',
            'gender' => 'Male',
            'qr_token' => 'qr_token_1002',
        ]);

        $entry1 = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $studentA->id,
            'group_id' => $this->groupA->id,
            'chest_number' => '101',
            'status' => 'confirmed',
            'attendance_status' => 'present',
        ]);

        $entry2 = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $studentB->id,
            'group_id' => $this->groupB->id,
            'chest_number' => '102',
            'status' => 'confirmed',
            'attendance_status' => 'present',
        ]);

        // Publish result: Entry 1 = 1st Place (5 pts) with Grade A (5 pts) = 10 pts for Group A
        // Entry 2 = 2nd Place (3 pts) with Grade B (3 pts) = 6 pts for Group B
        Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entry1->id,
            'first_grade' => 'A',
            'second_entry_id' => $entry2->id,
            'second_grade' => 'B',
            'status' => 'published',
        ]);

        // 2. Set Finance & Grace marks on groups
        $this->groupA->update([
            'finance_points' => 20,
            'grace_points' => 5,
        ]);

        $this->groupB->update([
            'finance_points' => 10,
            'grace_points' => 2,
        ]);

        // 3. Trigger recalculation
        app(PointCalculationService::class)->recalculateAllPoints();

        $this->groupA->refresh();
        $this->groupB->refresh();

        // Group A: Competition = 10 (5 pos + 5 grade), Finance = 20, Grace = 5 => Total = 35
        $this->assertSame(10, $this->groupA->competition_points);
        $this->assertSame(35, $this->groupA->points_cache);

        // Group B: Competition = 6 (3 pos + 3 grade), Finance = 10, Grace = 2 => Total = 18
        $this->assertSame(6, $this->groupB->competition_points);
        $this->assertSame(18, $this->groupB->points_cache);

        // Group A Rank 1, Group B Rank 2
        $this->assertSame(1, $this->groupA->rank_cache);
        $this->assertSame(2, $this->groupB->rank_cache);

        // Verify PointsTransaction has position, grade, finance, grace
        $this->assertDatabaseHas('points_transactions', ['group_id' => $this->groupA->id, 'source_type' => 'POSITION', 'points' => 5]);
        $this->assertDatabaseHas('points_transactions', ['group_id' => $this->groupA->id, 'source_type' => 'GRADE', 'points' => 5]);
        $this->assertDatabaseHas('points_transactions', ['group_id' => $this->groupA->id, 'source_type' => 'FINANCE', 'points' => 20]);
        $this->assertDatabaseHas('points_transactions', ['group_id' => $this->groupA->id, 'source_type' => 'GRACE', 'points' => 5]);
    }

    public function test_group_edit_controller_updates_finance_and_grace_marks(): void
    {
        $response = $this->actingAs($this->adminUser)->put(route('admin.groups.update', $this->groupA), [
            'name' => 'Lumo Fikric Renamed',
            'code' => 'LF',
            'color_hex' => '#56286B',
            'finance_points' => 15,
            'finance_remarks' => 'Updated in group settings',
            'grace_points' => 8,
            'grace_remarks' => 'Conduct award',
        ]);

        $response->assertRedirect(route('admin.groups.index'));
        $this->groupA->refresh();

        $this->assertSame(15, $this->groupA->finance_points);
        $this->assertSame('Updated in group settings', $this->groupA->finance_remarks);
        $this->assertSame(8, $this->groupA->grace_points);
        $this->assertSame('Conduct award', $this->groupA->grace_remarks);
        $this->assertSame(23, $this->groupA->points_cache);
    }
}
