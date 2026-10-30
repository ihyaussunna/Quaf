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

    public function test_guest_can_access_7012450418_without_login_and_page_is_isolated(): void
    {
        // No login required
        $response = $this->get('/7012450418');

        $response->assertOk();
        $response->assertSee('7012450418#');
        $response->assertSee('Paste Text & Notes Board', false);
        $response->assertSee('Paste Clipboard');
        $response->assertSee('Save Note to 7012450418#');

        // Cannot navigate to other pages: no external links
        $response->assertDontSee('href="/admin"', false);
        $response->assertDontSee('href="/"', false);
        $response->assertDontSee('href="/login"', false);
        $response->assertDontSee('Live Site', false);
        $response->assertDontSee('Signout', false);
    }

    public function test_guest_can_store_and_view_pasted_text_on_7012450418(): void
    {
        $response = $this->post('/7012450418', [
            'title' => 'Stage 1 Announcement Draft',
            'content' => 'Malayalam Elocution starts at 10:30 AM sharp at Stage 01.',
            'category' => 'announcement',
            'color' => 'amber',
            'is_pinned' => '1',
        ]);

        $response->assertRedirect('/7012450418');
        $this->assertDatabaseHas('admin_notes', [
            'title' => 'Stage 1 Announcement Draft',
            'category' => 'announcement',
            'color' => 'amber',
            'is_pinned' => true,
        ]);

        $pageResponse = $this->get('/7012450418');
        $pageResponse->assertSee('Stage 1 Announcement Draft');
        $pageResponse->assertSee('Malayalam Elocution starts at 10:30 AM');
        $pageResponse->assertSee('PINNED');
    }

    public function test_guest_can_toggle_pin_status_on_7012450418(): void
    {
        $note = AdminNote::create([
            'title' => 'Judge Briefing Notes',
            'content' => 'Review criteria before round 2.',
            'is_pinned' => false,
        ]);

        $response = $this->post("/7012450418/{$note->id}/pin");
        $response->assertRedirect('/7012450418');

        $note->refresh();
        $this->assertTrue($note->is_pinned);
    }

    public function test_guest_can_update_note_on_7012450418(): void
    {
        $note = AdminNote::create([
            'title' => 'Old Title',
            'content' => 'Old content snippet.',
        ]);

        $response = $this->put("/7012450418/{$note->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated content snippet.',
            'category' => 'urgent',
            'color' => 'red',
        ]);

        $response->assertRedirect('/7012450418');
        $note->refresh();
        $this->assertSame('Updated Title', $note->title);
        $this->assertSame('Updated content snippet.', $note->content);
        $this->assertSame('urgent', $note->category);
    }

    public function test_guest_can_delete_note_on_7012450418(): void
    {
        $note = AdminNote::create([
            'title' => 'Temporary snippet',
            'content' => 'Will be deleted.',
        ]);

        $response = $this->delete("/7012450418/{$note->id}");
        $response->assertRedirect('/7012450418');

        $this->assertDatabaseMissing('admin_notes', [
            'id' => $note->id,
        ]);
    }

    public function test_authenticated_admin_can_access_admin_notes_panel(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.notes.index'));

        $response->assertOk();
        $response->assertSee('7012450418#');
    }
}
