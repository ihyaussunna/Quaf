<?php

namespace Tests\Feature;

use App\Models\FestivalSetting;
use App\Models\GalleryItem;
use App\Models\Group;
use App\Models\News;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Stage;
use App\Models\User;
use App\Models\VideoItem;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Group $group;

    protected Zone $zone;

    protected Stage $stage;

    protected ProgramCategory $category;

    protected Program $program;

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
            ['slug' => 'pacto-hikmic'],
            [
                'name' => 'Pacto Hikmic',
                'code' => 'PACTO',
                'color' => '#2E3192',
                'manager_name' => 'Manager One',
                'contact_number' => '9876543210',
            ]
        );

        $this->stage = Stage::firstOrCreate(
            ['code' => 'STAGE-01'],
            [
                'name' => 'Main Stage 01',
                'location' => 'Central Festival Arena',
                'capacity' => 500,
            ]
        );

        $this->category = ProgramCategory::firstOrCreate(
            ['slug' => 'general'],
            [
                'name' => 'General',
            ]
        );

        $this->program = Program::firstOrCreate(
            ['code' => 'QAW01'],
            [
                'name' => 'Qawwali',
                'malayalam_name' => 'ഖവ്വാലി',
                'type' => 'group',
                'category_id' => $this->category->id,
                'zone_id' => $this->zone->id,
                'stage_id' => $this->stage->id,
                'status' => 'upcoming',
            ]
        );
    }

    public function test_landing_page_shows_coming_soon_when_not_launched(): void
    {
        FestivalSetting::updateOrCreate(
            ['key' => 'launch_status'],
            ['value' => 'coming_soon', 'group' => 'general']
        );

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('public.coming-soon');
    }

    public function test_landing_page_shows_home_when_preview_query_is_present(): void
    {
        FestivalSetting::updateOrCreate(
            ['key' => 'launch_status'],
            ['value' => 'coming_soon', 'group' => 'general']
        );

        $response = $this->get('/?preview=1');

        $response->assertStatus(200);
        $response->assertViewIs('public.home');
        $response->assertSee('QUAF 9.0');
    }

    public function test_home_page_renders_with_official_quaf_data(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertViewIs('public.home');
        $response->assertSee('QUAF 9.0');
        $response->assertSee('Ādabīc Inheritance');
        $response->assertSee('Pacto Hikmic');
    }

    public function test_results_page_and_detail_render_successfully(): void
    {
        $response = $this->get('/results');
        $response->assertStatus(200);
        $response->assertViewIs('public.results');
        $response->assertSee('Festival Results');

        $detailResponse = $this->get('/results/'.$this->program->code);
        $detailResponse->assertStatus(200);
        $detailResponse->assertViewIs('public.result-detail');
        $detailResponse->assertSee($this->program->name);
    }

    public function test_schedule_page_renders_with_stages_and_days(): void
    {
        $response = $this->get('/schedule');

        $response->assertStatus(200);
        $response->assertViewIs('public.schedule');
        $response->assertSee('Festival Schedule');
        $response->assertSee('Main Stage 01');
    }

    public function test_groups_page_and_group_detail_render(): void
    {
        $response = $this->get('/groups');
        $response->assertStatus(200);
        $response->assertViewIs('public.groups');
        $response->assertSee('Academic Groups');
        $response->assertSee('Pacto Hikmic');

        $groupDetail = $this->get('/groups/'.$this->group->id);
        $groupDetail->assertStatus(200);
        $groupDetail->assertViewIs('public.group-detail');
        $groupDetail->assertSee('Pacto Hikmic');
    }

    public function test_gallery_page_renders(): void
    {
        GalleryItem::create([
            'title' => 'Inauguration Ceremony',
            'image_path' => 'gallery/sample.jpg',
            'category' => 'Inauguration',
            'is_featured' => true,
            'display_order' => 1,
        ]);

        $response = $this->get('/gallery');

        $response->assertStatus(200);
        $response->assertViewIs('public.gallery');
        $response->assertSee('Festival Photo Gallery');
        $response->assertSee('Inauguration Ceremony');
    }

    public function test_news_page_and_news_detail_render(): void
    {
        $news = News::create([
            'title' => 'QUAF 9.0 Commences with Grand Inauguration',
            'slug' => 'quaf-9-commences-grand-inauguration',
            'excerpt' => 'The premier cultural festival begins at Markaz.',
            'content' => 'Full festival details and intellectual heritage discussion.',
            'category' => 'Announcement',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get('/news');
        $response->assertStatus(200);
        $response->assertViewIs('public.news');
        $response->assertSee('All Dispatches');

        $detailResponse = $this->get('/news/'.$news->slug);
        $detailResponse->assertStatus(200);
        $detailResponse->assertViewIs('public.news-detail');
        $detailResponse->assertSee($news->title);
    }

    public function test_media_page_renders(): void
    {
        VideoItem::create([
            'title' => 'QUAF 9.0 Official Theme Song',
            'youtube_id' => 'dQw4w9WgXcQ',
            'category' => 'Theme',
            'is_live' => false,
            'display_order' => 1,
        ]);

        $response = $this->get('/media');

        $response->assertStatus(200);
        $response->assertViewIs('public.media');
        $response->assertSee('SPOTLIGHT BROADCAST');
        $response->assertSee('QUAF 9.0 Official Theme Song');
    }

    public function test_brochure_page_renders(): void
    {
        $response = $this->get('/brochure');

        $response->assertStatus(200);
        $response->assertViewIs('public.brochure');
        $response->assertSee('OFFICIAL PUBLICATION');
        $response->assertSee('Digital Brochure Experience');
    }

    public function test_verification_hub_and_detail_routes_render(): void
    {
        $response = $this->get('/verify');
        $response->assertStatus(200);
        $response->assertViewIs('public.verify');

        $certResponse = $this->get('/verify/certificate/NONEXISTENT-CODE');
        $certResponse->assertStatus(200);
        $certResponse->assertViewIs('public.verify-certificate');

        $studentResponse = $this->get('/verify/student/NONEXISTENT-TOKEN');
        $studentResponse->assertStatus(200);
        $studentResponse->assertViewIs('public.verify-student');
    }

    public function test_about_and_contact_pages_render(): void
    {
        $aboutResponse = $this->get('/about');
        $aboutResponse->assertStatus(200);
        $aboutResponse->assertViewIs('public.about');
        $aboutResponse->assertSee('Ādabīc Inheritance');

        $contactResponse = $this->get('/contact');
        $contactResponse->assertStatus(200);
        $contactResponse->assertViewIs('public.contact');
        $contactResponse->assertSee('Official Helpdesk');
    }

    public function test_public_apis_return_valid_json(): void
    {
        $tickerResponse = $this->getJson('/api/public/live-ticker');
        $tickerResponse->assertStatus(200);
        $tickerResponse->assertJsonStructure(['live_fest_mode', 'items']);

        $standingsResponse = $this->getJson('/api/public/standings');
        $standingsResponse->assertStatus(200);
        $standingsResponse->assertJsonStructure(['standings']);

        $stagesResponse = $this->getJson('/api/public/stages');
        $stagesResponse->assertStatus(200);
        $stagesResponse->assertJsonStructure(['stages']);

        $latestResponse = $this->getJson('/api/public/latest-results');
        $latestResponse->assertStatus(200);
        $latestResponse->assertJsonStructure(['results']);
    }

    public function test_offstage_scheduler_and_rockwell_pdf(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        // 1. Offstage scheduler view (test both Oct 06 and Oct 07 with conflicts)
        $response = $this->actingAs($user)->get(route('admin.schedules.offstage', ['date' => '2026-10-06']));
        $response->assertStatus(200);
        $response->assertSee('Offstage Schedule Manager');

        $responseOct7 = $this->actingAs($user)->get(route('admin.schedules.offstage', ['date' => '2026-10-07']));
        $responseOct7->assertStatus(200);
        $responseOct7->assertSee('Offstage Schedule Manager');

        // 2. Offstage Rockwell PDF print view (Admin)
        $pdfResponse = $this->actingAs($user)->get(route('admin.schedules.offstage.pdf', ['date' => '2026-10-07']));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertSee('Offstage Program Schedule');
        $pdfResponse->assertSee('2026 October 07 Wednesday');
        $pdfResponse->assertSee('Rockwell');

        // 3. Public Offstage PDF view
        $publicPdf = $this->get(route('schedule.offstage-pdf', ['date' => '2026-10-07']));
        $publicPdf->assertStatus(200);
        $publicPdf->assertSee('Offstage Program Schedule');

        // 4. Conflict check API
        $checkResponse = $this->actingAs($user)->getJson(route('admin.schedules.check-conflict', [
            'program_id' => $this->program->id,
            'date' => '2026-10-06',
            'time' => '16:40',
            'duration' => 40,
            'stage_id' => $this->stage->id,
        ]));
        $checkResponse->assertStatus(200);
        $checkResponse->assertJsonStructure(['status', 'start_time', 'end_time', 'conflicts']);

        // 5. Quick slot addition
        $slotResponse = $this->actingAs($user)->post(route('admin.schedules.quick-slot'), [
            'program_id' => $this->program->id,
            'stage_id' => $this->stage->id,
            'date' => '2026-10-06',
            'time' => '18:00',
            'duration' => 30,
        ]);
        $slotResponse->assertRedirect();
        $this->assertDatabaseHas('schedules', [
            'program_id' => $this->program->id,
            'stage_id' => $this->stage->id,
        ]);

        // 6. Test both /admin/schedule (singular) and /admin/schedules (plural)
        $singularResponse = $this->actingAs($user)->get('/admin/schedule');
        $singularResponse->assertStatus(200);
        $singularResponse->assertSee('All Stages Schedule');

        $pluralResponse = $this->actingAs($user)->get('/admin/schedules');
        $pluralResponse->assertStatus(200);
        $pluralResponse->assertSee('All Stages Schedule');
    }
}
