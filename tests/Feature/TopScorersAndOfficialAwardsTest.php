<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TopScorersAndOfficialAwardsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Group $groupA;

    protected Group $groupB;

    protected Zone $zoneA;

    protected Zone $zoneB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'name' => 'System Admin',
            'email' => 'admin@quaf.test',
        ]);

        $this->groupA = Group::firstOrCreate(
            ['code' => 'LF'],
            [
                'name' => 'Lumo Fikric',
                'slug' => 'lumo-fikric',
                'color_hex' => '#be1e2d',
                'display_order' => 1,
            ]
        );

        $this->groupB = Group::firstOrCreate(
            ['code' => 'PH'],
            [
                'name' => 'Pacto Hikmic',
                'slug' => 'pacto-hikmic',
                'color_hex' => '#10b981',
                'display_order' => 2,
            ]
        );

        $this->zoneA = Zone::firstOrCreate(
            ['code' => 'A_ZONE'],
            [
                'name' => 'A Zone',
                'slug' => 'a-zone-test',
                'display_order' => 1,
            ]
        );

        $this->zoneB = Zone::firstOrCreate(
            ['code' => 'B_ZONE'],
            [
                'name' => 'B Zone',
                'slug' => 'b-zone-test',
                'display_order' => 2,
            ]
        );
    }

    public function test_admin_sidebar_has_single_top_scorers_link_and_no_achievements_dropdown(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.top-scorers.index'));

        $response->assertOk();
        $response->assertSee('Top Scorers');
        $response->assertSee(route('admin.top-scorers.index'));
        $response->assertDontSee('Achievements');
        $response->assertDontSee('All Student Score');
        $response->assertDontSee('Group Score');
    }

    public function test_official_awards_calculated_and_displayed_per_rule_10(): void
    {
        $cat = ProgramCategory::create([
            'name' => 'General',
            'slug' => 'general',
            'code' => 'GEN',
        ]);

        // Student 1: High Stage points (Vocal Winner) in Zone A
        $student1 = Student::create([
            'name' => 'Ahmad Shafi',
            'student_id' => 'QF101',
            'group_id' => $this->groupA->id,
            'zone_id' => $this->zoneA->id,
            'category' => 'A Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_shafi_101',
            'points_cache' => 30,
        ]);

        // Student 2: High Offstage points (Pen Winner) in Zone B
        $student2 = Student::create([
            'name' => 'Zayd Rahman',
            'student_id' => 'QF102',
            'group_id' => $this->groupB->id,
            'zone_id' => $this->zoneB->id,
            'category' => 'B Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_zayd_102',
            'points_cache' => 25,
        ]);

        // Stage Program (Individual)
        $stageProgram = Program::create([
            'name' => 'Elocution Arabic',
            'code' => 'PRG-STG-01',
            'type' => 'individual',
            'category_id' => $cat->id,
            'is_stage' => true,
        ]);

        // Offstage Program (Individual)
        $offstageProgram = Program::create([
            'name' => 'Essay Writing',
            'code' => 'PRG-OFF-01',
            'type' => 'individual',
            'category_id' => $cat->id,
            'is_stage' => false,
        ]);

        // Group Program (Group) - Must be excluded from individual calculations per Rule 10
        $groupProgram = Program::create([
            'name' => 'Qawwali Group',
            'code' => 'PRG-GRP-01',
            'type' => 'group',
            'category_id' => $cat->id,
            'is_stage' => true,
        ]);

        // Point transactions
        // Ahmad Shafi: 20 pts stage + 5 pts offstage = 25 pts individual
        DB::table('points_transactions')->insert([
            'student_id' => $student1->id,
            'program_id' => $stageProgram->id,
            'group_id' => $this->groupA->id,
            'points' => 20,
            'description' => 'Stage 1st Place',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('points_transactions')->insert([
            'student_id' => $student1->id,
            'program_id' => $offstageProgram->id,
            'group_id' => $this->groupA->id,
            'points' => 5,
            'description' => 'Offstage 2nd Place',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Zayd Rahman: 5 pts stage + 18 pts offstage = 23 pts individual
        DB::table('points_transactions')->insert([
            'student_id' => $student2->id,
            'program_id' => $stageProgram->id,
            'group_id' => $this->groupB->id,
            'points' => 5,
            'description' => 'Stage 3rd Place',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('points_transactions')->insert([
            'student_id' => $student2->id,
            'program_id' => $offstageProgram->id,
            'group_id' => $this->groupB->id,
            'points' => 18,
            'description' => 'Offstage 1st Place',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Extra points for Zayd in Group Program - MUST NOT BE COUNTED in individual top scorers
        DB::table('points_transactions')->insert([
            'student_id' => $student2->id,
            'program_id' => $groupProgram->id,
            'group_id' => $this->groupB->id,
            'points' => 50,
            'description' => 'Group 1st Place',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.top-scorers.index'));

        $response->assertOk();

        // 1. Check Vocal of the Fest (ചെറിയ എ പി ഉസ്താദ് കലാപ്രതിഭാ പുരസ്കാരം)
        $response->assertSee('ചെറിയ എ പി ഉസ്താദ് കലാപ്രതിഭാ പുരസ്കാരം');
        $response->assertSee('VOCAL OF THE FEST');
        $response->assertSee('Ahmad Shafi');
        $response->assertSee('20'); // Stage points for Ahmad

        // 2. Check Pen of the Fest (നെല്ലിക്കുത്ത് ഉസ്താദ് സർഗ്ഗപ്രതിഭാ പുരസ്കാരം)
        $response->assertSee('നെല്ലിക്കുത്ത് ഉസ്താദ് സർഗ്ഗപ്രതിഭാ പുരസ്കാരം');
        $response->assertSee('PEN OF THE FEST');
        $response->assertSee('Zayd Rahman');
        $response->assertSee('18'); // Offstage points for Zayd

        // 3. Check Zone Top Scorers (കട്ടിപ്പാറ ഉസ്താദ് അവാർഡ്)
        $response->assertSee('കട്ടിപ്പാറ ഉസ്താദ് അവാർഡ്');
        $response->assertSee('A Zone Top Scorer');
        $response->assertSee('B Zone Top Scorer');

        // Check that group program points (50 pts) were NOT added to individual scores per Rule 10
        $topScorers = $response->viewData('topScorers');
        $zaydScorer = collect($topScorers->items())->firstWhere('id', $student2->id);
        $this->assertNotNull($zaydScorer);
        $this->assertEquals(23, $zaydScorer->calc_total_points);
        $this->assertEquals(5, $zaydScorer->calc_stage_points);
        $this->assertEquals(18, $zaydScorer->calc_offstage_points);
    }

    public function test_top_scorers_filters_by_category_team_and_awards(): void
    {
        $cat = ProgramCategory::create([
            'name' => 'General',
            'slug' => 'general',
            'code' => 'GEN',
        ]);

        $student1 = Student::create([
            'name' => 'Ali Akbar',
            'student_id' => 'QF201',
            'group_id' => $this->groupA->id,
            'zone_id' => $this->zoneA->id,
            'category' => 'A Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_ali_201',
            'points_cache' => 30,
        ]);

        $student2 = Student::create([
            'name' => 'Bilal Hassan',
            'student_id' => 'QF202',
            'group_id' => $this->groupB->id,
            'zone_id' => $this->zoneB->id,
            'category' => 'B Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_bilal_202',
            'points_cache' => 20,
        ]);

        $stageProgram = Program::create([
            'name' => 'Recitation',
            'code' => 'PRG-REC-01',
            'type' => 'individual',
            'category_id' => $cat->id,
            'is_stage' => true,
        ]);

        DB::table('points_transactions')->insert([
            'student_id' => $student1->id,
            'program_id' => $stageProgram->id,
            'group_id' => $this->groupA->id,
            'points' => 30,
            'description' => 'Recitation 1st Place',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('points_transactions')->insert([
            'student_id' => $student2->id,
            'program_id' => $stageProgram->id,
            'group_id' => $this->groupB->id,
            'points' => 20,
            'description' => 'Recitation 2nd Place',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Filter by Category
        $responseCat = $this->actingAs($this->adminUser)->get(route('admin.top-scorers.index', ['category' => 'A Zone']));
        $responseCat->assertOk();
        $catItems = collect($responseCat->viewData('topScorers')->items());
        $this->assertTrue($catItems->contains('name', 'Ali Akbar'));
        $this->assertFalse($catItems->contains('name', 'Bilal Hassan'));

        // Filter by Team
        $responseTeam = $this->actingAs($this->adminUser)->get(route('admin.top-scorers.index', ['team' => $this->groupB->id]));
        $responseTeam->assertOk();
        $teamItems = collect($responseTeam->viewData('topScorers')->items());
        $this->assertTrue($teamItems->contains('name', 'Bilal Hassan'));
        $this->assertFalse($teamItems->contains('name', 'Ali Akbar'));

        // Filter by Search
        $responseSearch = $this->actingAs($this->adminUser)->get(route('admin.top-scorers.index', ['search' => 'Bilal']));
        $responseSearch->assertOk();
        $searchItems = collect($responseSearch->viewData('topScorers')->items());
        $this->assertTrue($searchItems->contains('name', 'Bilal Hassan'));
        $this->assertFalse($searchItems->contains('name', 'Ali Akbar'));
    }

    public function test_legacy_achievements_routes_redirect_to_top_scorers(): void
    {
        $resTeam = $this->actingAs($this->adminUser)->get(route('admin.achievements.team-score'));
        $resTeam->assertRedirect(route('admin.top-scorers.index'));

        $resZone = $this->actingAs($this->adminUser)->get(route('admin.achievements.zone-score', ['zone' => 'A Zone']));
        $resZone->assertRedirect(route('admin.top-scorers.index', ['category' => 'A Zone']));

        $resStage = $this->actingAs($this->adminUser)->get(route('admin.achievements.stage-score', ['stage' => 'Stage']));
        $resStage->assertRedirect(route('admin.top-scorers.index', ['award' => 'vocal']));

        $resSearch = $this->actingAs($this->adminUser)->get(route('admin.achievements.all-students', ['search' => 'Ahmad']));
        $resSearch->assertRedirect(route('admin.top-scorers.index', ['search' => 'Ahmad']));
    }
}
