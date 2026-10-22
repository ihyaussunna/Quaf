<?php

namespace Tests\Feature;

use App\Http\Controllers\TopicPickerController;
use App\Models\Group;
use App\Models\MediaCriticTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MediaCriticTopicPickerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $leaderLumo;

    protected User $leaderPacto;

    protected Group $groupLumo;

    protected Group $groupPacto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Central Admin',
            'email' => 'admin@quaf.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->leaderLumo = User::create([
            'name' => 'Leader Lumo',
            'email' => 'leader.lumo@quaf.test',
            'password' => Hash::make('lumo123'),
            'plain_password' => 'lumo123',
            'role' => 'group_leader',
            'is_active' => true,
        ]);

        $this->groupLumo = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#56286b',
            'leader_id' => $this->leaderLumo->id,
            'admin_password' => 'lumo123',
        ]);

        $this->leaderPacto = User::create([
            'name' => 'Leader Pacto',
            'email' => 'leader.pacto@quaf.test',
            'password' => Hash::make('pacto123'),
            'plain_password' => 'pacto123',
            'role' => 'group_leader',
            'is_active' => true,
        ]);

        $this->groupPacto = Group::create([
            'name' => 'Pacto Hikmic',
            'code' => 'PACTO',
            'slug' => 'pacto-hikmic',
            'color_hex' => '#10b981',
            'leader_id' => $this->leaderPacto->id,
            'admin_password' => 'pacto123',
        ]);

        // Ensure all seeded topics from migration are unlocked and unassigned
        MediaCriticTopic::query()->update([
            'group_id' => null,
            'picked_by_user_id' => null,
            'picked_at' => null,
            'is_locked' => false,
        ]);
    }

    public function test_unauthenticated_user_sees_login_form_on_topic_picker_page(): void
    {
        $response = $this->get(route('topic-picker.index'));
        $response->assertStatus(200);
        $response->assertSee('Media Critic');
        $response->assertSee('ഗ്രൂപ്പ് ലീഡർ ലോഗിൻ');
        $response->assertSee('Username / Group Code');
    }

    public function test_leader_can_login_via_topic_picker_login_route_using_group_code(): void
    {
        $response = $this->post(route('topic-picker.login'), [
            'username' => 'LUMO',
            'password' => 'lumo123',
        ]);

        $response->assertRedirect(route('topic-picker.index'));
        $this->assertAuthenticatedAs($this->leaderLumo);
    }

    public function test_authenticated_leader_sees_available_cards(): void
    {
        $response = $this->actingAs($this->leaderLumo)->get(route('topic-picker.index'));
        $response->assertStatus(200);
        $response->assertSee('Lumo Fikric');
        $response->assertSee('Card A');
        $response->assertSee('Card B');
        $response->assertSee('Card C');
        $response->assertSee('Card D');
        $response->assertSee('Card E');
    }

    public function test_leader_can_pick_a_topic_and_it_locks_to_their_group(): void
    {
        $topicC = MediaCriticTopic::where('code_letter', 'C')->first();

        $response = $this->actingAs($this->leaderLumo)->postJson(route('topic-picker.select'), [
            'topic_id' => $topicC->id,
            'method' => 'card',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $topicC->refresh();
        $this->assertTrue($topicC->is_locked);
        $this->assertEquals($this->groupLumo->id, $topicC->group_id);
        $this->assertEquals($this->leaderLumo->id, $topicC->picked_by_user_id);
    }

    public function test_locked_topic_cannot_be_picked_by_another_team(): void
    {
        $topicC = MediaCriticTopic::where('code_letter', 'C')->first();

        // Lumo locks Card C
        $this->actingAs($this->leaderLumo)->postJson(route('topic-picker.select'), [
            'topic_id' => $topicC->id,
            'method' => 'card',
        ]);

        // Pacto attempts to pick Card C
        $response = $this->actingAs($this->leaderPacto)->postJson(route('topic-picker.select'), [
            'topic_id' => $topicC->id,
            'method' => 'card',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'claimed' => true,
        ]);
    }

    public function test_group_cannot_pick_more_than_one_topic(): void
    {
        $topicA = MediaCriticTopic::where('code_letter', 'A')->first();
        $topicB = MediaCriticTopic::where('code_letter', 'B')->first();

        // Lumo locks Card A
        $this->actingAs($this->leaderLumo)->postJson(route('topic-picker.select'), [
            'topic_id' => $topicA->id,
            'method' => 'card',
        ]);

        // Lumo attempts to lock Card B as well
        $response = $this->actingAs($this->leaderLumo)->postJson(route('topic-picker.select'), [
            'topic_id' => $topicB->id,
            'method' => 'card',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'already_picked' => true,
        ]);
    }

    public function test_leader_visiting_topic_picker_after_selection_sees_locked_topic_slip(): void
    {
        $topicC = MediaCriticTopic::where('code_letter', 'C')->first();

        $this->actingAs($this->leaderLumo)->postJson(route('topic-picker.select'), [
            'topic_id' => $topicC->id,
            'method' => 'card',
        ]);

        $response = $this->actingAs($this->leaderLumo)->get(route('topic-picker.index'));
        $response->assertStatus(200);
        $response->assertSee('TOPIC CONFIRMED');
        $response->assertSee('സമസ്ത സർക്കുലറും സ്ത്രീ വിവാദവും');
        $response->assertSee('Lumo Fikric');
        $response->assertSee('Card C');
    }

    public function test_admin_can_view_topics_and_reset_allocation(): void
    {
        $topicD = MediaCriticTopic::where('code_letter', 'D')->first();
        $topicD->update([
            'group_id' => $this->groupPacto->id,
            'picked_by_user_id' => $this->leaderPacto->id,
            'picked_at' => now(),
            'is_locked' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.media-critic-topics.index'));
        $response->assertStatus(200);
        $response->assertSee('Pacto Hikmic');
        $response->assertSee('ഫലസ്തീൻ ജനതയോടുള്ള ഇസ്രായേൽ വംശഹത്യ');

        // Reset Card D
        $resetResponse = $this->actingAs($this->admin)->post(route('admin.media-critic-topics.reset', $topicD));
        $resetResponse->assertRedirect();

        $topicD->refresh();
        $this->assertFalse($topicD->is_locked);
        $this->assertNull($topicD->group_id);
    }

    public function test_admin_can_reset_all_topics(): void
    {
        $topicA = MediaCriticTopic::where('code_letter', 'A')->first();
        $topicA->update(['group_id' => $this->groupLumo->id, 'is_locked' => true]);

        $resetAllResponse = $this->actingAs($this->admin)->post(route('admin.media-critic-topics.reset-all'));
        $resetAllResponse->assertRedirect();

        $this->assertEquals(0, MediaCriticTopic::where('is_locked', true)->count());
    }

    public function test_status_endpoint_returns_live_counts(): void
    {
        $topicB = MediaCriticTopic::where('code_letter', 'B')->first();
        $topicB->update(['group_id' => $this->groupPacto->id, 'is_locked' => true]);

        $response = $this->get(route('topic-picker.status'));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'claimed_count' => 1,
            'remaining_count' => 4,
        ]);
    }

    public function test_ensure_table_and_data_auto_creates_table_if_missing(): void
    {
        Schema::dropIfExists('media_critic_topics');
        $this->assertFalse(Schema::hasTable('media_critic_topics'));

        TopicPickerController::ensureTableAndData();

        $this->assertTrue(Schema::hasTable('media_critic_topics'));
        $this->assertEquals(5, MediaCriticTopic::count());
    }
}
