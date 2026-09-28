<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProgramCommitteePortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $committeeUser;

    protected User $adminUser;

    protected User $studentUser;

    protected Zone $zone;

    protected ProgramCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->committeeUser = User::firstOrCreate(
            ['email' => 'samithi@quaf.fest'],
            [
                'name' => 'Program Samithi (പ്രോഗ്രാം സമിതി)',
                'password' => Hash::make('Samithi#2026@QuafFest!'),
                'role' => 'program_committee',
                'is_active' => true,
            ]
        );

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin@quaf.fest'],
            [
                'name' => 'Central Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->studentUser = User::firstOrCreate(
            ['email' => 'student@quaf.fest'],
            [
                'name' => 'Student User',
                'password' => Hash::make('password'),
                'role' => 'student',
                'is_active' => true,
            ]
        );

        $this->zone = Zone::firstOrCreate(
            ['slug' => 'a-zone'],
            ['name' => 'A Zone', 'code' => 'ZONE-A']
        );

        $this->category = ProgramCategory::firstOrCreate(
            ['slug' => 'stage'],
            ['name' => 'Stage Competitions', 'code' => 'STG']
        );
    }

    public function test_program_committee_user_can_login_with_samithi_alias_and_redirects_to_portal(): void
    {
        $response = $this->post(route('login'), [
            'username' => 'samithi',
            'password' => 'Samithi#2026@QuafFest!',
        ]);

        $response->assertRedirect(route('program-committee.dashboard'));
        $this->assertAuthenticatedAs($this->committeeUser);
    }

    public function test_program_committee_can_access_dashboard_and_see_niyamavali_metrics(): void
    {
        // Create 1 program with rules and 1 without rules
        Program::create([
            'code' => 'PRG01',
            'name' => 'Speech Competition',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'duration_minutes' => 5,
            'points_weight' => 5,
            'status' => 'upcoming',
            'rules' => 'Time limit: 5 minutes. No reading notes.',
        ]);

        Program::create([
            'code' => 'PRG02',
            'name' => 'Quran Recitation',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'duration_minutes' => 7,
            'points_weight' => 10,
            'status' => 'upcoming',
            'rules' => null,
        ]);

        $response = $this->actingAs($this->committeeUser)->get(route('program-committee.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Program Samithi Portal');
        $response->assertSee('Speech Competition');
        $response->assertSee('Quran Recitation');
    }

    public function test_program_committee_can_create_new_program_with_niyamavali_and_criteria(): void
    {
        $response = $this->actingAs($this->committeeUser)->post(route('program-committee.programs.store'), [
            'code' => 'Q9-999',
            'name' => 'Malayalam Essay Writing',
            'malayalam_name' => 'മലയാള ഉപന്യാസ രചന',
            'zone_id' => $this->zone->id,
            'category_id' => $this->category->id,
            'type' => 'individual',
            'duration_minutes' => 60,
            'points_weight' => 10,
            'status' => 'upcoming',
            'rules' => "1. വിഷയം ഹാളിൽ വെച്ച് നൽകും.\n2. സമയപരിധി: 60 മിനിറ്റ്.",
            'criteria' => [
                ['name' => 'ഉള്ളടക്കവും അറിവും', 'max_marks' => 40],
                ['name' => 'ഭാഷാശുദ്ധി', 'max_marks' => 30],
                ['name' => 'ആശയവ്യക്തത', 'max_marks' => 30],
            ],
        ]);

        $program = Program::where('code', 'Q9-999')->first();
        $this->assertNotNull($program);
        $this->assertEquals('Malayalam Essay Writing', $program->name);
        $this->assertEquals('മലയാള ഉപന്യാസ രചന', $program->malayalam_name);
        $this->assertStringContainsString('വിഷയം ഹാളിൽ വെച്ച് നൽകും', $program->rules);
        $this->assertEquals(3, $program->scoringCriteria()->count());

        $response->assertRedirect(route('program-committee.programs.show', $program));
    }

    public function test_program_committee_can_edit_and_update_program(): void
    {
        $program = Program::create([
            'code' => 'PRG-EDIT',
            'name' => 'Original Name',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'duration_minutes' => 5,
            'points_weight' => 5,
            'status' => 'upcoming',
        ]);

        $editView = $this->actingAs($this->committeeUser)->get(route('program-committee.programs.edit', $program));
        $editView->assertStatus(200);

        $updateRes = $this->actingAs($this->committeeUser)->put(route('program-committee.programs.update', $program), [
            'code' => 'PRG-EDIT',
            'name' => 'Updated Program Name',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'group',
            'participant_count' => 5,
            'duration_minutes' => 10,
            'points_weight' => 10,
            'status' => 'upcoming',
            'rules' => 'Updated rules text',
        ]);

        $updateRes->assertRedirect(route('program-committee.programs.show', $program));
        $this->assertEquals('Updated Program Name', $program->fresh()->name);
        $this->assertEquals('group', $program->fresh()->type);
        $this->assertEquals(5, $program->fresh()->participant_count);
    }

    public function test_program_committee_can_update_niyamavali_and_criteria_directly(): void
    {
        $program = Program::create([
            'code' => 'PRG-RULES',
            'name' => 'Debate Competition',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'duration_minutes' => 5,
            'points_weight' => 5,
            'status' => 'upcoming',
        ]);

        $rulesView = $this->actingAs($this->committeeUser)->get(route('program-committee.programs.rules', $program));
        $rulesView->assertStatus(200);
        $rulesView->assertSee('Competition Rules');

        $updateRulesRes = $this->actingAs($this->committeeUser)->put(route('program-committee.programs.rules.update', $program), [
            'rules' => "1. ഒന്നാം ബെൽ: 4-ാം മിനിറ്റ്.\n2. രണ്ടാം ബെൽ: 5-ാം മിനിറ്റ്.",
            'duration_minutes' => 5,
            'criteria' => [
                ['name' => 'Argument Strength', 'max_marks' => 50],
                ['name' => 'Rebuttal Skill', 'max_marks' => 50],
            ],
        ]);

        $updateRulesRes->assertRedirect(route('program-committee.programs.show', $program));
        $this->assertStringContainsString('ഒന്നാം ബെൽ', $program->fresh()->rules);
        $this->assertEquals(2, $program->fresh()->scoringCriteria()->count());
    }

    public function test_program_committee_can_view_niyamavali_print_views(): void
    {
        $program = Program::create([
            'code' => 'PRG-PRINT',
            'name' => 'Printable Program',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'duration_minutes' => 5,
            'points_weight' => 5,
            'status' => 'upcoming',
            'rules' => 'Sample test rules for printing.',
        ]);

        // Single program sheet print
        $sheetRes = $this->actingAs($this->committeeUser)->get(route('program-committee.programs.rules.print', $program));
        $sheetRes->assertStatus(200);
        $sheetRes->assertSee('Sample test rules for printing.');
        $sheetRes->assertSee('PRG-PRINT');

        // Booklet print
        $bookletRes = $this->actingAs($this->committeeUser)->get(route('program-committee.niyamavali.print-book'));
        $bookletRes->assertStatus(200);
        $bookletRes->assertSee('COMPETITION RULES');
        $bookletRes->assertSee('Printable Program');
    }

    public function test_unauthorized_users_cannot_access_program_committee_portal(): void
    {
        $studentRes = $this->actingAs($this->studentUser)->get(route('program-committee.dashboard'));
        $studentRes->assertRedirect(route('login'));
    }

    public function test_program_can_be_created_without_category_selection_using_zone(): void
    {
        $createPage = $this->actingAs($this->committeeUser)->get(route('program-committee.programs.create'));
        $createPage->assertStatus(200);
        $createPage->assertDontSee('Category *');

        $response = $this->actingAs($this->committeeUser)->post(route('program-committee.programs.store'), [
            'code' => 'Q9-888',
            'name' => 'Zone Based Elocution',
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'duration_minutes' => 7,
            'points_weight' => 5,
            'status' => 'upcoming',
        ]);

        $program = Program::where('code', 'Q9-888')->first();
        $this->assertNotNull($program);
        $this->assertEquals($this->zone->id, $program->zone_id);
        $this->assertEquals('A Zone', $program->eligibility);
        $this->assertNotNull($program->category_id);
        $response->assertRedirect(route('program-committee.programs.show', $program));
    }

    public function test_program_creation_with_non_existent_category_id_succeeds_with_fallback(): void
    {
        $response = $this->actingAs($this->committeeUser)->post(route('program-committee.programs.store'), [
            'code' => 'Q9-999',
            'name' => 'Auto Fallback Category Competition',
            'category_id' => 999999, // non-existent category id
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'duration_minutes' => 5,
            'points_weight' => 5,
            'status' => 'upcoming',
        ]);

        $response->assertSessionHasNoErrors();
        $program = Program::where('code', 'Q9-999')->first();
        $this->assertNotNull($program);
        $this->assertDatabaseHas('programs', [
            'code' => 'Q9-999',
            'name' => 'Auto Fallback Category Competition',
        ]);
    }

    public function test_program_can_be_created_without_time_limit_and_with_total_mark_only(): void
    {
        $response = $this->actingAs($this->committeeUser)->post(route('program-committee.programs.store'), [
            'code' => 'Q9-NOTIME',
            'name' => 'Painting Competition',
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'has_time_limit' => 0,
            'has_criteria' => 0,
            'points_weight' => 5,
            'status' => 'upcoming',
        ]);

        $response->assertSessionHasNoErrors();
        $program = Program::where('code', 'Q9-NOTIME')->first();
        $this->assertNotNull($program);
        $this->assertFalse($program->has_time_limit);
        $this->assertNull($program->duration_minutes);
        $this->assertFalse($program->has_criteria);
        $this->assertCount(0, $program->scoringCriteria);

        $showView = $this->actingAs($this->committeeUser)->get(route('program-committee.programs.show', $program));
        $showView->assertOk();
        $showView->assertSee('No Time Limit');
        $showView->assertSee('Total Mark Only');
    }

    public function test_rules_update_can_toggle_off_time_limit_and_criteria(): void
    {
        $program = Program::create([
            'code' => 'Q9-TESTCRIT',
            'name' => 'Speech with Criteria',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'has_time_limit' => true,
            'duration_minutes' => 10,
            'has_criteria' => true,
            'points_weight' => 5,
            'status' => 'upcoming',
        ]);

        $program->scoringCriteria()->createMany([
            ['criterion_name' => 'Presentation', 'max_marks' => 50],
            ['criterion_name' => 'Content', 'max_marks' => 50],
        ]);

        $this->assertCount(2, $program->scoringCriteria);

        // Now update rules to disable criteria and time limit
        $response = $this->actingAs($this->committeeUser)->put(route('program-committee.programs.rules.update', $program), [
            'rules' => 'General guidelines only.',
            'has_time_limit' => 0,
            'has_criteria' => 0,
        ]);

        $response->assertSessionHasNoErrors();
        $program->refresh();

        $this->assertFalse($program->has_time_limit);
        $this->assertNull($program->duration_minutes);
        $this->assertFalse($program->has_criteria);
        $this->assertCount(0, $program->scoringCriteria);
    }
}
