<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Student;
use App\Models\User;
use App\Services\PointCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResultPublishTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_publish_result_and_recalculate_points(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $groupA = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#56286b',
        ]);

        $category = ProgramCategory::create([
            'name' => 'Arts Category',
            'slug' => 'arts-category',
        ]);

        $program = Program::create([
            'category_id' => $category->id,
            'name' => 'E-Poster',
            'code' => 'Q9-220',
            'type' => 'individual',
            'eligibility' => 'Mix Zone',
            'participant_count' => 2,
            'status' => 'scheduled',
        ]);

        $student1 = Student::create([
            'student_id' => 'QF1001',
            'chest_number' => 'QF1001',
            'name' => 'Student One',
            'group_id' => $groupA->id,
            'admission_number' => 'ADM001',
            'class' => 'D4',
            'category' => 'A Zone',
            'qr_token' => Str::random(32),
        ]);

        $entry1 = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student1->id,
            'group_id' => $groupA->id,
            'chest_number' => '1001',
            'status' => 'verified',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.mark-entry.show', $program))
            ->post(route('admin.mark-entry.publish', $program), [
                'first_entry_id' => $entry1->id,
                'remarks' => 'Outstanding presentation',
            ]);

        $response->assertRedirect(route('admin.mark-entry.show', $program));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('results', [
            'program_id' => $program->id,
            'first_entry_id' => $entry1->id,
            'status' => 'published',
        ]);

        // Verify chart data reflects the published result
        $chartData = app(PointCalculationService::class)->getPerformanceChartData();
        $this->assertEquals(1, $chartData['declaredCount']);
        $this->assertNotEmpty($chartData['series']);

        $lumoSeries = collect($chartData['series'])->firstWhere('group_id', $groupA->id);
        $this->assertNotNull($lumoSeries);
        $this->assertGreaterThan(0, $lumoSeries['final_points']);
        $this->assertStringContainsString((string) $lumoSeries['final_points'], $lumoSeries['polyline_points'].json_encode($lumoSeries['coords']));

        // Verify admin dashboard renders the dynamic chart
        $dashResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashResponse->assertOk();
        $dashResponse->assertSee('Performance Over Time');
        $dashResponse->assertSee($groupA->name);
        $dashResponse->assertSee('Result 1 (Now)');

        // Verify leader dashboard renders the dynamic chart
        $leaderUser = User::factory()->create(['role' => 'group_leader', 'is_active' => true]);
        $groupA->update(['leader_id' => $leaderUser->id]);
        $leaderResponse = $this->actingAs($leaderUser)->get(route('leader.dashboard'));
        $leaderResponse->assertOk();
        $leaderResponse->assertSee('Performance Over Time');
        $leaderResponse->assertSee($groupA->name);
        $leaderResponse->assertSee('Result 1 (Now)');
    }
}
