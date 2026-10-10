<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Judge;
use App\Models\OnlineSubmission;
use App\Models\OnlineSubmissionForm;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Stage;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_admin_and_judge_can_view_submissions_pdf_dossier(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $program = Program::create([
            'name' => 'Calligraphy Painting',
            'code' => 'CP01',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'single',
        ]);

        $form = OnlineSubmissionForm::create([
            'program_id' => $program->id,
            'slug' => 'cp01-pdf-test',
            'title' => 'Calligraphy Painting Submissions',
            'allow_text' => true,
            'allow_image' => true,
            'is_open' => true,
        ]);

        $submission = OnlineSubmission::create([
            'online_submission_form_id' => $form->id,
            'program_id' => $program->id,
            'code_letter' => 'A',
            'text_content' => 'Calligraphy concept explanation by candidate.',
            'status' => 'submitted',
        ]);

        // Admin PDF route
        $adminPdfResponse = $this->actingAs($admin)->get(route('admin.online-forms.pdf', $form->id));
        $adminPdfResponse->assertStatus(200);
        $adminPdfResponse->assertSee('Calligraphy Painting');
        $adminPdfResponse->assertSee('Code A');
        $adminPdfResponse->assertSee('Calligraphy concept explanation by candidate.');

        // Single submission PDF route
        $singlePdfResponse = $this->actingAs($admin)->get(route('admin.online-forms.submissions.pdf', $submission->id));
        $singlePdfResponse->assertStatus(200);
        $singlePdfResponse->assertSee('Code A');

        // Judge PDF route
        $judgeUser = User::factory()->create(['role' => 'judge']);
        $judge = Judge::create([
            'name' => 'Dr. Kareem',
            'user_id' => $judgeUser->id,
            'access_code' => '9988',
        ]);
        $judge->programs()->attach($program->id);

        $judgePdfResponse = $this->actingAs($judgeUser)->get(route('judge.evaluate.submissions-pdf', $program->id));
        $judgePdfResponse->assertStatus(200);
        $judgePdfResponse->assertSee('Calligraphy Painting');
        $judgePdfResponse->assertSee('Code A');
    }

    public function test_judge_evaluation_screen_displays_candidate_submissions_and_answers(): void
    {
        $judgeUser = User::factory()->create(['role' => 'judge']);
        $judge = Judge::create([
            'name' => 'Prof. Usman',
            'user_id' => $judgeUser->id,
            'access_code' => '4455',
        ]);

        $program = Program::create([
            'name' => 'English Creative Writing',
            'code' => 'ECW01',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'single',
        ]);
        $judge->programs()->attach($program->id);

        $student = Student::create([
            'student_id' => 'STU-9901',
            'name' => 'Fathima Zahra',
            'chest_number' => '888',
            'group_id' => $this->group->id,
            'category_id' => $this->category->id,
            'qr_token' => Str::random(32),
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'group_id' => $this->group->id,
            'chest_number' => '888',
            'code_letter' => 'K',
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        $form = OnlineSubmissionForm::create([
            'program_id' => $program->id,
            'slug' => 'ecw01-eval-test',
            'title' => 'English Creative Writing Form',
            'allow_text' => true,
            'is_open' => true,
        ]);

        OnlineSubmission::create([
            'online_submission_form_id' => $form->id,
            'program_id' => $program->id,
            'program_entry_id' => $entry->id,
            'code_letter' => 'K',
            'chest_number' => '888',
            'text_content' => 'Once upon a time in Cordoba, the libraries sparkled with wisdom.',
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($judgeUser)->get(route('judge.evaluate', $program->id));
        $response->assertStatus(200);
        $response->assertSee('Candidate Submission');
        $response->assertSee('Code K');
        $response->assertSee('Once upon a time in Cordoba, the libraries sparkled with wisdom.');
        $response->assertSee('Print / Download Answers (PDF)');
    }

    public function test_student_file_upload_and_viewing_via_dedicated_and_fallback_routes(): void
    {
        $program = Program::create([
            'name' => 'Water Color Painting',
            'code' => 'WCP01',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'single',
        ]);

        $form = OnlineSubmissionForm::create([
            'program_id' => $program->id,
            'slug' => 'wcp01-art',
            'title' => 'Water Color Painting Form',
            'allow_text' => false,
            'allow_image' => true,
            'is_image_required' => true,
            'is_open' => true,
        ]);

        $uploadedPhoto = UploadedFile::fake()->image('nature_art.jpg', 600, 400);

        $submitResponse = $this->post(route('online-submission.submit', $form->slug), [
            'code_letter' => 'M',
            'submission_file' => $uploadedPhoto,
        ]);

        $submitResponse->assertStatus(200);

        $submission = OnlineSubmission::where('online_submission_form_id', $form->id)
            ->where('code_letter', 'M')
            ->first();

        $this->assertNotNull($submission);
        $this->assertNotEmpty($submission->file_path);
        $this->assertTrue($submission->isImage());
        $this->assertStringContainsString('/submissions/file/'.$submission->id, $submission->file_url);

        // Test dedicated file viewing route
        $fileResponse = $this->get(route('online-submission.file', $submission->id));
        $fileResponse->assertStatus(200);
        $fileResponse->assertHeader('Content-Disposition', 'inline; filename="nature_art.jpg"');

        // Test fallback storage route as well
        $fallbackResponse = $this->get('/storage/'.$submission->file_path);
        $fallbackResponse->assertStatus(200);
        $fallbackResponse->assertHeader('Content-Disposition', 'inline; filename="'.basename($submission->file_path).'"');
    }
}
