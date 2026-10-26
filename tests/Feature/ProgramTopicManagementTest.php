<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProgramTopicManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $judgeUser;

    protected Judge $judge;

    protected Zone $zone;

    protected ProgramCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->judgeUser = User::factory()->create([
            'role' => 'judge',
        ]);

        $this->judge = Judge::create([
            'user_id' => $this->judgeUser->id,
            'name' => 'Juror Muhammad',
            'access_code' => '5566',
        ]);

        $this->zone = Zone::first() ?? Zone::create([
            'name' => 'Mix Zone',
            'code' => 'MZ',
            'slug' => 'mix-zone',
            'display_order' => 1,
        ]);

        $this->category = ProgramCategory::first() ?? ProgramCategory::create([
            'name' => 'General',
            'slug' => 'general',
            'code' => 'GEN',
            'min_class' => 1,
            'max_class' => 12,
        ]);
    }

    public function test_admin_can_create_program_with_topic(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.programs.store'), [
            'name' => 'E-Poster Competition',
            'malayalam_name' => 'ഇ-പോസ്റ്റർ',
            'code' => 'Q9-228',
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'participant_count' => 2,
            'points_weight' => 1.0,
            'status' => 'upcoming',
            'topic' => 'AI and Cultural Preservation in 2026',
            'rules' => 'Submit only original creative designs.',
        ]);

        $response->assertRedirect(route('admin.programs.index'));

        $this->assertDatabaseHas('programs', [
            'code' => 'Q9-228',
            'topic' => 'AI and Cultural Preservation in 2026',
        ]);
    }

    public function test_admin_can_update_program_topic(): void
    {
        $program = Program::create([
            'name' => 'Essay Writing',
            'code' => 'Q9-ESS-01',
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'points_weight' => 1.0,
            'status' => 'upcoming',
            'topic' => 'Initial Topic',
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.programs.update', $program), [
            'name' => 'Essay Writing',
            'code' => 'Q9-ESS-01',
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'participant_count' => 2,
            'points_weight' => 1.0,
            'status' => 'upcoming',
            'topic' => 'പ്രളയാനന്തര കേരളവും നവനിർമ്മിതിയും',
        ]);

        $response->assertRedirect(route('admin.programs.index'));

        $program->refresh();
        $this->assertEquals('പ്രളയാനന്തര കേരളവും നവനിർമ്മിതിയും', $program->topic);
    }

    public function test_judge_evaluation_page_displays_topic_option_and_popup_modal(): void
    {
        $program = Program::create([
            'name' => 'E-Poster',
            'malayalam_name' => 'ഇ-പോസ്റ്റർ',
            'code' => 'Q9-228',
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'points_weight' => 1.0,
            'status' => 'in_progress',
            'duration_minutes' => 60,
            'topic' => 'Adabic Heritage and Ethical Art Forms',
        ]);

        $program->judges()->attach($this->judge->id);

        $group = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#56286b',
        ]);

        $student = Student::create([
            'student_id' => 'STU991',
            'name' => 'Candidate One',
            'group_id' => $group->id,
            'admission_number' => 'ADM991',
            'class_level' => 'D4',
            'category' => 'Mix Zone',
            'qr_token' => Str::random(32),
        ]);

        ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'chest_number' => '901',
            'code_letter' => 'A',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        $response = $this->actingAs($this->judgeUser)->get(route('judge.evaluate', $program));

        $response->assertOk();
        // Check topic view buttons and content
        $response->assertSee('Topic / വിഷയം');
        $response->assertSee('View Topic / വിഷയം കാണുക');
        $response->assertSee('Adabic Heritage and Ethical Art Forms');
        $response->assertSee('topicModalOpen');
    }
}
