<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\OnlineSubmissionForm;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Stage;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OnlineSubmissionFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Group $group;

    protected Zone $zone;

    protected ProgramCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->zone = Zone::firstOrCreate(
            ['slug' => 'a-zone'],
            [
                'name' => 'A Zone',
                'code' => 'A_ZONE',
                'color_hex' => '#BE1E2D',
            ]
        );

        $this->group = Group::firstOrCreate(
            ['code' => 'SAF'],
            [
                'name' => 'SAFVA',
                'slug' => 'safva',
                'color' => '#BE1E2D',
            ]
        );

        $this->category = ProgramCategory::firstOrCreate(
            ['slug' => 'gen-cat'],
            ['name' => 'General Category', 'code' => 'GEN', 'display_order' => 1]
        );
    }

    public function test_admin_can_create_online_submission_form_for_program(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $program = Program::create([
            'name' => 'Malayalam Essay Writing',
            'code' => 'P101',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'single',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.online-forms.store'), [
            'program_id' => $program->id,
            'title' => 'Malayalam Essay Writing - Online Submission',
            'instructions' => 'Write an essay on modern ethical challenges in 500 words.',
            'allow_text' => 1,
            'text_label' => 'Essay Content',
            'is_text_required' => 1,
            'allow_image' => 1,
            'allow_video' => 0,
            'is_open' => 1,
        ]);

        $response->assertRedirect(route('admin.online-forms.index'));

        $this->assertDatabaseHas('online_submission_forms', [
            'program_id' => $program->id,
            'title' => 'Malayalam Essay Writing - Online Submission',
            'allow_text' => true,
            'is_text_required' => true,
            'allow_image' => true,
        ]);
    }

    public function test_public_user_can_view_online_submission_form(): void
    {
        $program = Program::create([
            'name' => 'Digital Drawing Contest',
            'code' => 'DDC01',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'single',
        ]);

        $form = OnlineSubmissionForm::create([
            'program_id' => $program->id,
            'slug' => 'ddc01-abc123',
            'title' => 'Digital Drawing Submission',
            'instructions' => 'Upload drawing scan or high-res photo.',
            'allow_text' => true,
            'allow_image' => true,
            'is_open' => true,
        ]);

        $response = $this->get(route('online-submission.show', $form->slug));

        $response->assertStatus(200);
        $response->assertSee('Digital Drawing Contest');
        $response->assertSee('Your Assigned Code Letter');
    }

    public function test_student_can_submit_entry_with_code_letter(): void
    {
        $student = Student::create([
            'name' => 'Muhammed Nihal',
            'student_id' => 'STU-9901',
            'group_id' => $this->group->id,
            'zone_id' => $this->zone->id,
            'admission_number' => 'ADM-9901',
            'qr_token' => Str::random(32),
        ]);

        $program = Program::create([
            'name' => 'Arabic Poetry Writing',
            'code' => 'APW01',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'single',
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'group_id' => $this->group->id,
            'chest_number' => '405',
            'code_letter' => 'B',
            'status' => 'confirmed',
        ]);

        $form = OnlineSubmissionForm::create([
            'program_id' => $program->id,
            'slug' => 'apw01-testform',
            'title' => 'Arabic Poetry Form',
            'allow_text' => true,
            'is_text_required' => true,
            'allow_image' => false,
            'allow_video' => false,
            'is_open' => true,
        ]);

        $response = $this->post(route('online-submission.submit', $form->slug), [
            'code_letter' => 'B',
            'text_content' => 'Here is my complete poetry submission for QUAF 9.0.',
        ]);

        $response->assertStatus(200);
        $response->assertSee('SUBMISSION RECORDED');
        $response->assertSee('Arabic Poetry Writing');

        $this->assertDatabaseHas('online_submissions', [
            'online_submission_form_id' => $form->id,
            'program_id' => $program->id,
            'program_entry_id' => $entry->id,
            'code_letter' => 'B',
            'chest_number' => '405',
            'student_name' => 'Muhammed Nihal',
        ]);
    }

    public function test_greenroom_displays_qr_code_when_online_form_configured(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $stage = Stage::firstOrCreate(
            ['code' => 'STAGE-01'],
            ['name' => 'Stage 01', 'venue' => 'Main Arena']
        );

        $program = Program::create([
            'name' => 'Pencil Drawing',
            'code' => 'PD01',
            'stage_id' => $stage->id,
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'single',
            'status' => 'in_progress',
        ]);

        $form = OnlineSubmissionForm::create([
            'program_id' => $program->id,
            'slug' => 'pd01-qr-test',
            'title' => 'Pencil Drawing Online Form',
            'allow_image' => true,
            'is_open' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('greenroom.index', [
            'stage_id' => $stage->id,
            'program_id' => $program->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('ONLINE SUBMISSION ACTIVE');
        $response->assertSee('Project QR');
    }
}
