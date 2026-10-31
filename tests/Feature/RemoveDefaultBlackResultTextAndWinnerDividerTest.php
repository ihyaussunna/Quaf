<?php

namespace Tests\Feature;

use App\Models\PosterSetting;
use App\Models\Result;
use App\Models\ResultTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveDefaultBlackResultTextAndWinnerDividerTest extends TestCase
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

    public function test_poster_setting_defaults_have_show_winner_divider_disabled(): void
    {
        $defaults = PosterSetting::defaultSettings();

        $this->assertArrayHasKey('show_winner_divider', $defaults);
        $this->assertFalse($defaults['show_winner_divider']);
    }

    public function test_template_customizer_view_does_not_contain_default_black_result_text_push(): void
    {
        $studioViewPath = resource_path('views/media/results/studio.blade.php');
        $this->assertFileExists($studioViewPath);

        $viewContent = file_get_contents($studioViewPath);

        // Ensure text_result_default is never pushed as default
        $this->assertStringNotContainsString("id: 'text_result_default'", $viewContent);
        $this->assertStringNotContainsString("'Result Text Art'", $viewContent);

        // Ensure filtering out legacy text_result_default exists
        $this->assertStringContainsString("el.id !== 'text_result_default'", $viewContent);

        // Ensure show_winner_divider toggle exists
        $this->assertStringContainsString('Divider Line Below Group', $viewContent);
        $this->assertStringContainsString('show_winner_divider', $viewContent);
    }

    public function test_media_user_can_access_template_customizer_without_forced_black_text(): void
    {
        $template = ResultTemplate::create([
            'name' => 'Custom Template 12',
            'image_path' => 'images/result-templates/template_12.png',
            'is_active' => true,
            'default_settings' => [
                'layout_mode' => 'default',
            ],
        ]);

        $response = $this->actingAs($this->mediaUser)
            ->get(route('media.results.templates.customize', $template));

        $response->assertStatus(200);
        $response->assertSee('Custom Template 12');
        $response->assertSee('Divider Line Below Group');
        $response->assertDontSee("id: 'text_result_default'");
    }

    public function test_saving_template_customization_persists_show_winner_divider_flag(): void
    {
        $template = ResultTemplate::create([
            'name' => 'Custom Template Test',
            'image_path' => 'images/result-templates/template_test.png',
            'is_active' => true,
        ]);

        $settings = array_merge(PosterSetting::defaultSettings(), [
            'show_winner_divider' => false,
            'winner_unit_color' => '#000000',
        ]);

        $response = $this->actingAs($this->mediaUser)
            ->postJson(route('media.results.templates.save-customization', $template), [
                'settings' => $settings,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $posterSetting = PosterSetting::where('template_id', $template->id)->first();
        $this->assertNotNull($posterSetting);
        $this->assertFalse($posterSetting->show_winner_divider);
    }
}
