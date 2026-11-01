<?php

namespace Tests\Feature;

use App\Models\CoinTransaction;
use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Student;
use App\Services\CoinRewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SupplementAndStandingFixesAndInsightCoinExclusionTest extends TestCase
{
    use RefreshDatabase;

    protected Group $groupA;

    protected Group $groupB;

    protected Group $groupC;

    protected Student $student1;

    protected Student $student2;

    protected ProgramCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupA = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LF',
            'slug' => 'lumo-fikric',
            'color_hex' => '#be1e2d',
            'public_points_cache' => 350,
            'public_rank_cache' => 1,
        ]);

        $this->groupB = Group::create([
            'name' => 'Unio Hilmic',
            'code' => 'UH',
            'slug' => 'unio-hilmic',
            'color_hex' => '#10b981',
            'public_points_cache' => 340,
            'public_rank_cache' => 2,
        ]);

        $this->groupC = Group::create([
            'name' => 'Pacto Hikmic',
            'code' => 'PH',
            'slug' => 'pacto-hikmic',
            'color_hex' => '#f59e0b',
            'public_points_cache' => 330,
            'public_rank_cache' => 3,
        ]);

        $this->student1 = Student::create([
            'student_id' => 'STU-1001',
            'group_id' => $this->groupA->id,
            'name' => 'Muhammed Nihal',
            'qr_token' => Str::random(32),
        ]);

        $this->student2 = Student::create([
            'student_id' => 'STU-1002',
            'group_id' => $this->groupB->id,
            'name' => 'Ahmed Fayis',
            'qr_token' => Str::random(32),
        ]);

        $this->category = ProgramCategory::create([
            'name' => 'Fine Arts',
            'code' => 'FA',
            'slug' => 'fine-arts',
        ]);
    }

    public function test_q9_236_insight_is_strictly_excluded_from_coin_rewards(): void
    {
        $program = Program::create([
            'category_id' => $this->category->id,
            'code' => 'Q9-236',
            'name' => 'Insight',
            'type' => 'group',
            'status' => 'completed',
        ]);

        $entryA = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $this->student1->id,
            'group_id' => $this->groupA->id,
            'chest_number' => '101',
            'status' => 'verified',
        ]);

        $entryB = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $this->student2->id,
            'group_id' => $this->groupB->id,
            'chest_number' => '102',
            'status' => 'verified',
        ]);

        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entryA->id,
            'first_grade' => 'A+',
            'second_entry_id' => $entryB->id,
            'second_grade' => 'A',
            'status' => 'published',
            'is_media_published' => true,
            'published_at' => now(),
        ]);

        $service = app(CoinRewardService::class);

        $this->assertTrue(CoinRewardService::isProgramExcludedFromCoins($program));

        $preview = $service->previewGroupCoinDistribution($result);
        $this->assertEquals(0, $preview['total_coins_to_credit']);
        $this->assertEquals(0, $preview['total_eligible_participants']);
        $this->assertNotEmpty($preview['flags']);

        $distResult = $service->distributeCoinsForResult($result);
        $this->assertTrue($distResult['skipped']);
        $this->assertEquals(0, $distResult['credited_count']);

        // Verify zero transactions created
        $this->assertEquals(0, CoinTransaction::where('program_id', $program->id)->count());
        $this->assertEquals(0, (int) $this->student1->fresh()->coin_balance);
        $this->assertEquals(0, (int) $this->student2->fresh()->coin_balance);
    }

    public function test_supplement_page_renders_with_digital_flipbook_and_12_pages(): void
    {
        $response = $this->get(route('supplement.index'));

        $response->assertStatus(200);
        $response->assertSee('QUAF Official Festival Supplement');
        $response->assertSee('quaf-supplement.pdf');
        $response->assertSee('page-flip.browser.js');
        $response->assertSee('supplementFlipViewer');
        $response->assertSee('12 Pages');
        $response->assertSee('page-1.jpg');
        $response->assertSee('page-12.jpg');
    }

    public function test_groups_standings_page_has_no_malayalam_in_poster_section_and_uses_proper_download_route(): void
    {
        FestivalSetting::set('public_standings_poster_url', '/storage/media/standings/poster.jpg');
        FestivalSetting::set('public_standings_poster_path', 'media/standings/poster.jpg');

        $response = $this->get(route('groups.index'));

        $response->assertStatus(200);
        $response->assertSee('Official Standings Poster');
        $response->assertDontSee('ടീം സ്റ്റാൻഡിങ്സ് ഒഫീഷ്യൽ പോസ്റ്റർ');
        $response->assertDontSee('DOWNLOAD POSTER (ഡൗൺലോഡ്)');
        $response->assertSee(route('groups.download-poster'));

        // Rank 3 bronze box must have inline rich bronze gradient and no thick stroke
        $response->assertSee('linear-gradient(135deg, #c26928 0%, #92400e 100%)');
    }

    public function test_home_page_rank_3_bronze_visibility_and_clean_stroke(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('linear-gradient(135deg, #c26928 0%, #92400e 100%)');
        $response->assertDontSee('ring-1 ring-amber-600/20');
    }

    public function test_public_navbar_and_results_page_contain_standings_and_supplement_links(): void
    {
        // Results page header link
        $resultsResponse = $this->get(route('results.index'));
        $resultsResponse->assertStatus(200);
        $resultsResponse->assertSee(route('groups.index'));
        $resultsResponse->assertSee('Team Standings &rarr;', false);

        // Public navigation
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee(route('groups.index'));
        $homeResponse->assertSee('Standings');
        $homeResponse->assertSee(route('supplement.index'));
        $homeResponse->assertSee('Supplement');
    }
}
