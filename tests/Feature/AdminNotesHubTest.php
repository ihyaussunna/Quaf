<?php

namespace Tests\Feature;

use App\Models\AdminNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotesHubTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Main Admin',
            'email' => 'admin@quaf.fest',
        ]);
    }

    public function test_guest_visiting_7012450418_redirects_to_login(): void
    {
        $response = $this->get('/7012450418');

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_7012450418_directly(): void
    {
        $response = $this->actingAs($this->admin)->get('/7012450418');

        $response->assertOk();
        $response->assertSee('7012450418#');
        $response->assertSee('Admin Paste Text & Notes', false);
        $response->assertSee('Paste from Clipboard');
        $response->assertSee('Save Note to 7012450418#');
    }

    public function test_admin_can_access_admin_notes_route(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.notes.index'));

        $response->assertOk();
        $response->assertSee('7012450418#');
    }

    public function test_admin_can_store_and_view_pasted_text_note(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.notes.store'), [
            'title' => 'Stage 1 Announcement Draft',
            'content' => 'Malayalam Elocution starts at 10:30 AM sharp at Stage 01.',
            'category' => 'announcement',
            'color' => 'amber',
            'is_pinned' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('admin_notes', [
            'title' => 'Stage 1 Announcement Draft',
            'category' => 'announcement',
            'color' => 'amber',
            'is_pinned' => true,
        ]);

        $pageResponse = $this->actingAs($this->admin)->get('/7012450418');
        $pageResponse->assertSee('Stage 1 Announcement Draft');
        $pageResponse->assertSee('Malayalam Elocution starts at 10:30 AM');
        $pageResponse->assertSee('PINNED');
    }

    public function test_admin_can_toggle_pin_status(): void
    {
        $note = AdminNote::create([
            'title' => 'Judge Briefing Notes',
            'content' => 'Review criteria before round 2.',
            'is_pinned' => false,
            'created_by_user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.notes.pin', $note));
        $response->assertRedirect();

        $note->refresh();
        $this->assertTrue($note->is_pinned);
    }

    public function test_admin_can_update_note(): void
    {
        $note = AdminNote::create([
            'title' => 'Old Title',
            'content' => 'Old content snippet.',
            'created_by_user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.notes.update', $note), [
            'title' => 'Updated Title',
            'content' => 'Updated content snippet.',
            'category' => 'urgent',
            'color' => 'red',
        ]);

        $response->assertRedirect();
        $note->refresh();
        $this->assertSame('Updated Title', $note->title);
        $this->assertSame('Updated content snippet.', $note->content);
        $this->assertSame('urgent', $note->category);
    }

    public function test_admin_can_delete_note(): void
    {
        $note = AdminNote::create([
            'title' => 'Temporary snippet',
            'content' => 'Will be deleted.',
            'created_by_user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.notes.destroy', $note));
        $response->assertRedirect();

        $this->assertDatabaseMissing('admin_notes', [
            'id' => $note->id,
        ]);
    }
}
