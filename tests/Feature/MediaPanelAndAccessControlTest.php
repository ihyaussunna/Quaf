<?php

namespace Tests\Feature;

use App\Models\GalleryItem;
use App\Models\Group;
use App\Models\News;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\ResultTemplate;
use App\Models\Student;
use App\Models\User;
use App\Models\VideoItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class MediaPanelAndAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected User $mediaUser;

    protected User $adminUser;

    protected User $leaderUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mediaUser = User::firstOrCreate(
            ['email' => 'media@quaf.fest'],
            [
                'name' => 'QUAF Media Team (മീഡിയ വിംഗ്)',
                'password' => Hash::make('Media#2026@QuafLive!'),
                'plain_password' => 'Media#2026@QuafLive!',
                'role' => 'media_team',
                'is_active' => true,
            ]
        );

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin@quaf.fest'],
            [
                'name' => 'QUAF Central Admin',
                'password' => Hash::make('CentralAdmin#2026@Quaf!'),
                'plain_password' => 'CentralAdmin#2026@Quaf!',
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        $this->leaderUser = User::firstOrCreate(
            ['email' => 'leader.pacto@quaf.fest'],
            [
                'name' => 'BASIL ADANY (Leader - PACTO)',
                'password' => Hash::make('Pacto$Hikmic*8319#Q9'),
                'plain_password' => 'Pacto$Hikmic*8319#Q9',
                'role' => 'group_leader',
                'is_active' => true,
            ]
        );
    }

    public function test_media_user_can_login_with_alias_and_redirects_to_media_dashboard(): void
    {
        $response = $this->post(route('login'), [
            'username' => 'media',
            'password' => 'Media#2026@QuafLive!',
        ]);

        $response->assertRedirect(route('media.dashboard'));
        $this->assertAuthenticatedAs($this->mediaUser);
    }

    public function test_media_user_can_view_media_dashboard(): void
    {
        $response = $this->actingAs($this->mediaUser)->get(route('media.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Media Wing Dashboard');
        $response->assertSee('News Articles');
        $response->assertSee('Gallery Photos');
        $response->assertSee('Videos Uploaded');
    }

    public function test_media_user_can_create_and_manage_news_article(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->mediaUser)->post(route('media.news.store'), [
            'title' => 'QUAF 09 Grand Stage Inauguration Set For Tomorrow',
            'category' => 'Festival Updates',
            'excerpt' => 'Preparations completed for the mega festival kickoff.',
            'content' => 'Markaz cultural festival season 09 begins with historic participation.',
            'status' => 'published',
            'is_featured' => 1,
            'cover_file' => UploadedFile::fake()->image('news_cover.jpg'),
        ]);

        $response->assertRedirect(route('media.news.index'));
        $this->assertDatabaseHas('news', [
            'title' => 'QUAF 09 Grand Stage Inauguration Set For Tomorrow',
            'category' => 'Festival Updates',
            'status' => 'published',
            'is_featured' => true,
        ]);

        $article = News::first();
        $this->assertNotNull($article->cover_image);

        // Edit
        $editResponse = $this->actingAs($this->mediaUser)->put(route('media.news.update', $article), [
            'title' => 'QUAF 09 Grand Stage Inauguration Postponed by 1 Hour',
            'category' => 'Festival Updates',
            'excerpt' => 'Updated kickoff time.',
            'content' => 'New scheduled time announced.',
            'status' => 'published',
            'is_featured' => 0,
        ]);

        $editResponse->assertRedirect(route('media.news.index'));
        $this->assertDatabaseHas('news', [
            'id' => $article->id,
            'title' => 'QUAF 09 Grand Stage Inauguration Postponed by 1 Hour',
            'is_featured' => false,
        ]);

        // Delete
        $deleteResponse = $this->actingAs($this->mediaUser)->delete(route('media.news.destroy', $article));
        $deleteResponse->assertRedirect(route('media.news.index'));
        $this->assertDatabaseMissing('news', ['id' => $article->id]);
    }

    public function test_media_user_can_toggle_news_featured(): void
    {
        $article = News::create([
            'title' => 'Spotlight Performance',
            'slug' => 'spotlight-performance-12345',
            'content' => 'Great performance.',
            'category' => 'Stage News',
            'status' => 'published',
            'is_featured' => false,
        ]);

        $response = $this->actingAs($this->mediaUser)->post(route('media.news.toggle-featured', $article));
        $response->assertRedirect();

        $this->assertTrue($article->fresh()->is_featured);
    }

    public function test_media_user_can_create_and_manage_gallery_photo(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->mediaUser)->post(route('media.gallery.store'), [
            'title' => 'Stage 1 Lighting Moments',
            'category' => 'Stage Event',
            'is_featured' => 1,
            'display_order' => 1,
            'image_file' => UploadedFile::fake()->image('photo.jpg'),
        ]);

        $response->assertRedirect(route('media.gallery.index'));
        $this->assertDatabaseHas('gallery_items', [
            'title' => 'Stage 1 Lighting Moments',
            'category' => 'Stage Event',
            'is_featured' => true,
        ]);

        $item = GalleryItem::first();
        $this->assertNotNull($item->image_path);

        // Update
        $this->actingAs($this->mediaUser)->put(route('media.gallery.update', $item), [
            'title' => 'Stage 1 Grand Night View',
            'category' => 'Festival Highlights',
            'display_order' => 2,
        ]);

        $this->assertDatabaseHas('gallery_items', [
            'id' => $item->id,
            'title' => 'Stage 1 Grand Night View',
            'category' => 'Festival Highlights',
        ]);

        // Destroy
        $this->actingAs($this->mediaUser)->delete(route('media.gallery.destroy', $item));
        $this->assertDatabaseMissing('gallery_items', ['id' => $item->id]);
    }

    public function test_media_user_can_create_and_manage_videos_with_youtube_parser(): void
    {
        // YouTube URL format: https://www.youtube.com/watch?v=dQw4w9WgXcQ
        $response = $this->actingAs($this->mediaUser)->post(route('media.videos.store'), [
            'title' => 'QUAF 09 Theme Song Official Video',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'category' => 'Theme Song',
            'is_live' => 0,
            'display_order' => 1,
        ]);

        $response->assertRedirect(route('media.videos.index'));
        $this->assertDatabaseHas('video_items', [
            'title' => 'QUAF 09 Theme Song Official Video',
            'youtube_id' => 'dQw4w9WgXcQ',
            'category' => 'Theme Song',
            'is_live' => false,
        ]);

        $video = VideoItem::first();

        // Toggle Live
        $this->actingAs($this->mediaUser)->post(route('media.videos.toggle-live', $video));
        $this->assertTrue($video->fresh()->is_live);

        // Delete
        $this->actingAs($this->mediaUser)->delete(route('media.videos.destroy', $video));
        $this->assertDatabaseMissing('video_items', ['id' => $video->id]);
    }

    public function test_admin_can_view_panel_access_and_credentials(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.panel-access.index'));

        $response->assertStatus(200);
        $response->assertSee('Panel Access & Credentials Hub', false);
        $response->assertSee($this->adminUser->email);
        $response->assertSee($this->mediaUser->email);
        $response->assertSee($this->leaderUser->email);
        $response->assertSee('Media#2026@QuafLive!');
        $response->assertSee('Pacto$Hikmic*8319#Q9');
    }

    public function test_admin_can_toggle_account_lock_status(): void
    {
        $this->assertTrue($this->leaderUser->is_active);

        // Lock account
        $response = $this->actingAs($this->adminUser)->post(route('admin.panel-access.toggle-status', $this->leaderUser));
        $response->assertRedirect();
        $this->assertFalse($this->leaderUser->fresh()->is_active);

        // Unlock account
        $response2 = $this->actingAs($this->adminUser)->post(route('admin.panel-access.toggle-status', $this->leaderUser));
        $response2->assertRedirect();
        $this->assertTrue($this->leaderUser->fresh()->is_active);
    }

    public function test_admin_cannot_lock_their_own_account(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.panel-access.toggle-status', $this->adminUser));
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertTrue($this->adminUser->fresh()->is_active);
    }

    public function test_locked_account_cannot_login_and_sees_locked_message(): void
    {
        // Deactivate / Lock leader
        $this->leaderUser->update(['is_active' => false]);

        $response = $this->post(route('login'), [
            'username' => 'leader.pacto@quaf.fest',
            'password' => 'Pacto$Hikmic*8319#Q9',
        ]);

        $response->assertSessionHasErrors('username');
        $errors = session('errors')->get('username');
        $this->assertStringContainsString('Locked', $errors[0]);
        $this->assertGuest();
    }

    public function test_active_session_of_locked_account_is_terminated_by_role_middleware(): void
    {
        // User starts active
        $this->assertTrue($this->leaderUser->is_active);

        // Now admin locks them behind the scenes
        $this->leaderUser->update(['is_active' => false]);

        // Leader tries to access leader dashboard with their existing session
        $response = $this->actingAs($this->leaderUser)->get(route('leader.dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('username');
        $errors = session('errors')->get('username');
        $this->assertStringContainsString('Locked', $errors[0]);
        $this->assertGuest();
    }

    public function test_admin_can_update_panel_password(): void
    {
        $newPass = 'NewSecurePass#9876!';

        $response = $this->actingAs($this->adminUser)->post(route('admin.panel-access.update-password', $this->leaderUser), [
            'password' => $newPass,
        ]);

        $response->assertRedirect();
        $this->leaderUser->refresh();
        $this->assertEquals($newPass, $this->leaderUser->plain_password);
        $this->assertTrue(Hash::check($newPass, $this->leaderUser->password));
    }

    public function test_announced_result_appears_in_media_results_awaiting_poster(): void
    {
        $group = Group::create(['name' => 'Hikmic Group', 'code' => 'HIK', 'slug' => 'hikmic', 'color_hex' => '#be1e2d']);
        $cat = ProgramCategory::create(['name' => 'General Stage', 'slug' => 'general-stage']);
        $prog = Program::create([
            'category_id' => $cat->id,
            'name' => 'Arabic Elocution Final',
            'code' => 'Q9-501',
            'type' => 'individual',
            'eligibility' => 'General',
            'status' => 'completed',
        ]);

        $student1 = Student::create([
            'student_id' => 'STU501',
            'chest_number' => '501',
            'name' => 'Zayd Rahman',
            'group_id' => $group->id,
            'admission_number' => 'ADM501',
            'class' => 'D3',
            'category' => 'General',
            'qr_token' => Str::random(32),
        ]);

        $entry1 = ProgramEntry::create([
            'program_id' => $prog->id,
            'student_id' => $student1->id,
            'group_id' => $group->id,
            'chest_number' => '501',
            'status' => 'verified',
        ]);

        $result = Result::create([
            'program_id' => $prog->id,
            'first_entry_id' => $entry1->id,
            'status' => 'announced', // Announcer announced on stage!
            'is_media_published' => false,
        ]);

        $response = $this->actingAs($this->mediaUser)->get(route('media.results.index', ['tab' => 'announced']));

        $response->assertStatus(200);
        $response->assertSee('Arabic Elocution Final');
        $response->assertSee('Q9-501');
        $response->assertSee('Ready for Poster');
        $response->assertSee('Create & Design Poster');
    }

    public function test_media_user_can_view_studio_and_sees_only_podium_winners(): void
    {
        $group = Group::create(['name' => 'Pacto Group', 'code' => 'PAC', 'slug' => 'pacto', 'color_hex' => '#1e3a8a']);
        $cat = ProgramCategory::create(['name' => 'Junior Wing', 'slug' => 'junior-wing']);
        $prog = Program::create([
            'category_id' => $cat->id,
            'name' => 'Madh Song Competition',
            'code' => 'Q9-502',
            'type' => 'individual',
            'status' => 'completed',
        ]);

        $createStudentAndEntry = function ($id, $name, $chest) use ($group, $prog) {
            $student = Student::create([
                'student_id' => 'STU'.$id,
                'chest_number' => $chest,
                'name' => $name,
                'group_id' => $group->id,
                'admission_number' => 'ADM'.$id,
                'class' => 'D2',
                'category' => 'Junior',
                'qr_token' => Str::random(32),
            ]);

            return ProgramEntry::create([
                'program_id' => $prog->id,
                'student_id' => $student->id,
                'group_id' => $group->id,
                'chest_number' => $chest,
                'status' => 'verified',
            ]);
        };

        $first = $createStudentAndEntry('10', 'First Winner Person', '101');
        $second = $createStudentAndEntry('20', 'Second Winner Person', '102');
        $third = $createStudentAndEntry('30', 'Third Winner Person', '103');
        $fourthNonPodium = $createStudentAndEntry('40', 'Fourth Non-Podium Person', '104');

        $result = Result::create([
            'program_id' => $prog->id,
            'first_entry_id' => $first->id,
            'second_entry_id' => $second->id,
            'third_entry_id' => $third->id,
            'status' => 'announced',
            'is_media_published' => false,
        ]);

        // Studio view
        $response = $this->actingAs($this->mediaUser)->get(route('media.results.studio', $result));

        $response->assertStatus(200);
        $response->assertSee('Madh Song Competition');
        $response->assertSee('First Winner Person');
        $response->assertSee('Second Winner Person');
        $response->assertSee('Third Winner Person');
        // Non-podium participant must NOT be rendered in podium winners
        $response->assertDontSee('Fourth Non-Podium Person');
    }

    public function test_media_user_can_save_poster_and_publishes_result(): void
    {
        Storage::fake('public');

        $group = Group::create(['name' => 'Adany Group', 'code' => 'ADA', 'slug' => 'adany', 'color_hex' => '#047857']);
        $cat = ProgramCategory::create(['name' => 'Senior Wing', 'slug' => 'senior-wing']);
        $prog = Program::create([
            'category_id' => $cat->id,
            'name' => 'Calligraphy Contest',
            'code' => 'Q9-503',
            'type' => 'individual',
            'status' => 'completed',
        ]);

        $student = Student::create([
            'student_id' => 'STU503',
            'chest_number' => '503',
            'name' => 'Hassan Ali',
            'group_id' => $group->id,
            'admission_number' => 'ADM503',
            'class' => 'D4',
            'category' => 'Senior',
            'qr_token' => Str::random(32),
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $prog->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'chest_number' => '503',
            'status' => 'verified',
        ]);

        $result = Result::create([
            'program_id' => $prog->id,
            'first_entry_id' => $entry->id,
            'status' => 'announced',
            'is_media_published' => false,
        ]);

        // 1x1 transparent png in base64
        $fakePngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($this->mediaUser)->postJson(route('media.results.save-poster', $result), [
            'poster_data' => $fakePngBase64,
            'settings' => [
                'row_gap' => 105,
                'item_gap' => 16,
                'result_x' => 740,
                'result_y' => 240,
            ],
            'publish_now' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $result->refresh();
        $this->assertTrue($result->is_media_published);
        $this->assertEquals('published', $result->status);
        $this->assertNotNull($result->poster_image);
        $this->assertEquals(105, $result->custom_poster_settings['row_gap']);
        $this->assertEquals(16, $result->custom_poster_settings['item_gap']);
    }

    public function test_media_user_can_save_default_settings_for_template(): void
    {
        $template = ResultTemplate::create([
            'name' => 'Test Frame Template',
            'image_path' => '/images/result-templates/test.png',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->mediaUser)->postJson(route('media.results.save-default-settings'), [
            'template_id' => $template->id,
            'settings' => [
                'row_gap' => 120,
                'item_gap' => 18,
                'medal_size' => 65,
                'competition_max_width' => 700,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('poster_settings', [
            'template_id' => $template->id,
            'row_gap' => 120,
            'item_gap' => 18,
            'medal_size' => 65,
        ]);
    }

    public function test_public_user_can_view_result_poster(): void
    {
        $group = Group::create(['name' => 'Echo Group', 'code' => 'ECH', 'slug' => 'echo', 'color_hex' => '#10b981']);
        $cat = ProgramCategory::create(['name' => 'Arts Wing', 'slug' => 'arts-wing']);
        $prog = Program::create([
            'category_id' => $cat->id,
            'name' => 'Digital Illustration',
            'code' => 'Q9-504',
            'type' => 'individual',
            'status' => 'completed',
        ]);

        $student = Student::create([
            'student_id' => 'STU504',
            'chest_number' => '504',
            'name' => 'Bilal Farooq',
            'group_id' => $group->id,
            'admission_number' => 'ADM504',
            'class' => 'D1',
            'category' => 'Arts',
            'qr_token' => Str::random(32),
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $prog->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'chest_number' => '504',
            'status' => 'verified',
        ]);

        $result = Result::create([
            'program_id' => $prog->id,
            'first_entry_id' => $entry->id,
            'status' => 'published',
            'is_media_published' => true,
            'poster_image' => '/storage/media/posters/test_poster.png',
        ]);

        $response = $this->get(route('media.results.public-poster', $result));

        $response->assertStatus(200);
        $response->assertSee('Digital Illustration');
        $response->assertSee('Bilal Farooq');
        $response->assertSee('Download Poster');
        $response->assertSee('WhatsApp Share');
    }

    public function test_non_admin_accessing_admin_panel_receives_403_with_helpful_options(): void
    {
        $response = $this->actingAs($this->mediaUser)->get(route('admin.dashboard'));

        $response->assertStatus(403);
        $response->assertSee('Access Restricted');
        $response->assertSee('Media team', false);
        $response->assertSee('Log Out / Switch Account');
        $response->assertSee('Go to Media Dashboard');
    }
}
