<?php

namespace Tests\Feature;

use App\Models\FestivalSetting;
use App\Models\PosterSetting;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Result;
use App\Models\ResultTemplate;
use App\Models\User;
use App\Services\ResultPosterGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultNumberFontAndMediaSlideTest extends TestCase
{
    use RefreshDatabase;

    protected User $mediaUser;

    protected function setUp(): void
    {
        parent::setUp();
        Result::ensureSchema();

        $this->mediaUser = User::factory()->create([
            'role' => 'media_manager',
        ]);
    }

    public function test_media_results_index_does_not_display_stage_announcer_standings_slides(): void
    {
        FestivalSetting::set('stage_announcer_standings_banner', json_encode([
            'title' => 'Standings after 15 Results',
            'standings' => [
                ['rank' => 1, 'name' => 'Team A', 'code' => 'TA', 'points' => 120],
            ],
        ]));

        $response = $this->actingAs($this->mediaUser)->get(route('media.results.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Announced by Stage');
        $response->assertDontSee('അടിയന്തിര പബ്ലിഷിംഗ്: ടീം പോയിന്റ് നില');
    }

    public function test_result_poster_generator_resolves_all_required_fonts(): void
    {
        $rockwellLight = ResultPosterGenerator::resolveResultNumberFont('Rockwell Std', '300');
        $this->assertFileExists($rockwellLight);
        $this->assertStringContainsString('Rockwell', $rockwellLight);

        $rockwellRegular = ResultPosterGenerator::resolveResultNumberFont('Rockwell Std', '400');
        $this->assertFileExists($rockwellRegular);

        $rockwellBold = ResultPosterGenerator::resolveResultNumberFont('Rockwell Std', '700');
        $this->assertFileExists($rockwellBold);

        $rockwellExtra = ResultPosterGenerator::resolveResultNumberFont('Rockwell Std', '800');
        $this->assertFileExists($rockwellExtra);

        $montserrat = ResultPosterGenerator::resolveResultNumberFont('Montserrat', '400');
        $this->assertFileExists($montserrat);
        $this->assertStringContainsString('Montserrat', $montserrat);

        $sora = ResultPosterGenerator::resolveResultNumberFont('Sora', '400');
        $this->assertFileExists($sora);
        $this->assertStringContainsString('Sora', $sora);

        $monbaiti = ResultPosterGenerator::resolveResultNumberFont('Mongolian Baiti', '400');
        $this->assertFileExists($monbaiti);
        $this->assertStringContainsString('monbaiti', $monbaiti);

        $georgia = ResultPosterGenerator::resolveResultNumberFont('Georgia', '400');
        $this->assertFileExists($georgia);

        $arial = ResultPosterGenerator::resolveResultNumberFont('Arial', '700');
        $this->assertFileExists($arial);
    }

    public function test_template_save_customization_persists_result_font_and_weight(): void
    {
        $template = ResultTemplate::create([
            'name' => 'Test Yellow Template',
            'slug' => 'test-yellow-template',
            'image_path' => 'images/result-templates/quaf_template_yellow.png',
            'is_active' => true,
        ]);

        $settings = array_merge(PosterSetting::defaultSettings(), [
            'result_font' => 'Montserrat',
            'result_weight' => '800',
            'result_size' => 450,
            'result_color' => '#1e1b4b',
        ]);

        $response = $this->actingAs($this->mediaUser)->postJson(
            route('media.results.templates.save-customization', $template),
            ['settings' => $settings]
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $template->refresh();
        $this->assertEquals('Montserrat', $template->default_settings['result_font'] ?? null);
        $this->assertEquals('800', $template->default_settings['result_weight'] ?? null);

        $posterSetting = PosterSetting::where('template_id', $template->id)->first();
        $this->assertNotNull($posterSetting);
        $this->assertEquals('Montserrat', $posterSetting->result_font);
        $this->assertEquals('800', $posterSetting->result_weight);
    }

    public function test_saving_result_poster_saves_custom_result_font_settings(): void
    {
        $category = ProgramCategory::create(['name' => 'General', 'code' => 'GEN', 'slug' => 'general']);

        $program = Program::create([
            'name' => 'Elocution English',
            'code' => 'ELOC-ENG',
            'category_id' => $category->id,
            'eligibility' => 'Mix Zone',
            'type' => 'individual',
            'points_weight' => 1.0,
            'status' => 'completed',
        ]);

        $result = Result::create([
            'program_id' => $program->id,
            'status' => 'draft',
        ]);

        $customSettings = [
            'result_font' => 'Sora',
            'result_weight' => '700',
            'result_size' => 500,
            'result_color' => '#D02627',
        ];

        $response = $this->actingAs($this->mediaUser)->postJson(
            route('media.results.save-poster', $result),
            [
                'settings' => $customSettings,
                'publish_now' => false,
            ]
        );

        $response->assertStatus(200);

        $result->refresh();
        $this->assertEquals('Sora', $result->custom_poster_settings['result_font'] ?? null);
        $this->assertEquals('700', $result->custom_poster_settings['result_weight'] ?? null);
    }
}
