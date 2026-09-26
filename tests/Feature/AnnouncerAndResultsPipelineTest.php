<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnnouncerAndResultsPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $announcer;

    protected User $mediaUser;

    protected Program $program;

    protected ProgramEntry $entry1;

    protected ProgramEntry $entry2;

    protected ProgramEntry $entry3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Officer',
            'email' => 'admin@quaf.test',
        ]);

        $this->announcer = User::factory()->create([
            'role' => 'announcer',
            'name' => 'QUAF Announcer',
            'email' => 'announcer@quaf.test',
        ]);

        $this->mediaUser = User::factory()->create([
            'role' => 'media_team',
            'name' => 'Media Designer',
            'email' => 'media@quaf.test',
        ]);

        $groupA = Group::create([
            'name' => 'Cordova House',
            'code' => 'CRD',
            'slug' => 'cordova-house',
            'color_hex' => '#be1e2d',
        ]);

        $groupB = Group::create([
            'name' => 'Baghdad House',
            'code' => 'BGD',
            'slug' => 'baghdad-house',
            'color_hex' => '#1e3a8a',
        ]);

        $category = ProgramCategory::create([
            'name' => 'General Wing',
            'slug' => 'general-wing',
        ]);

        $this->program = Program::create([
            'category_id' => $category->id,
            'name' => 'Malayalam Speech',
            'malayalam_name' => 'മലയാള പ്രസംഗം',
            'code' => 'Q9-105',
            'type' => 'individual',
            'eligibility' => 'General',
            'participant_count' => 3,
            'status' => 'ongoing',
        ]);

        $s1 = Student::create([
            'student_id' => 'STU101',
            'chest_number' => '101',
            'name' => 'Ahmed Fayiz',
            'group_id' => $groupA->id,
            'admission_number' => 'ADM101',
            'class' => 'D4',
            'category' => 'General',
            'qr_token' => Str::random(32),
        ]);

        $s2 = Student::create([
            'student_id' => 'STU102',
            'chest_number' => '102',
            'name' => 'Bilal Hassan',
            'group_id' => $groupB->id,
            'admission_number' => 'ADM102',
            'class' => 'D4',
            'category' => 'General',
            'qr_token' => Str::random(32),
        ]);

        $s3 = Student::create([
            'student_id' => 'STU103',
            'chest_number' => '103',
            'name' => 'Hamza K',
            'group_id' => $groupA->id,
            'admission_number' => 'ADM103',
            'class' => 'D4',
            'category' => 'General',
            'qr_token' => Str::random(32),
        ]);

        $this->entry1 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $s1->id,
            'group_id' => $groupA->id,
            'chest_number' => '101',
            'status' => 'verified',
        ]);

        $this->entry2 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $s2->id,
            'group_id' => $groupB->id,
            'chest_number' => '102',
            'status' => 'verified',
        ]);

        $this->entry3 = ProgramEntry::create([
            'program_id' => $this->program->id,
            'student_id' => $s3->id,
            'group_id' => $groupA->id,
            'chest_number' => '103',
            'status' => 'verified',
        ]);
    }

    public function test_announcer_route_access_control(): void
    {
        // Guests cannot access
        $this->get(route('announcer.index'))->assertRedirect(route('login'));

        // Announcer user can access
        $this->actingAs($this->announcer)
            ->get(route('announcer.index'))
            ->assertStatus(200)
            ->assertSee('Stage Announcement Console');

        // Admin can also access
        $this->actingAs($this->admin)
            ->get(route('announcer.index'))
            ->assertStatus(200);
    }

    public function test_complete_result_pipeline_admin_to_announcer_to_media_to_public(): void
    {
        // 1. Initial State: Result created by Admin evaluation with status 'verified'
        $result = Result::create([
            'program_id' => $this->program->id,
            'first_entry_id' => $this->entry1->id,
            'second_entry_id' => $this->entry2->id,
            'third_entry_id' => $this->entry3->id,
            'status' => 'verified',
            'is_media_published' => false,
        ]);

        // 2. Admin sends result to Announcer Desk
        $adminResponse = $this->actingAs($this->admin)
            ->post(route('admin.results.send-to-announcer', $result));

        $adminResponse->assertRedirect();
        $this->assertDatabaseHas('results', [
            'id' => $result->id,
            'status' => 'send',
        ]);

        // 3. Announcer opens Announcer Console and sees the program ready for announcement
        $announcerResponse = $this->actingAs($this->announcer)
            ->get(route('announcer.index', ['tab' => 'ready']));

        $announcerResponse->assertStatus(200);
        $announcerResponse->assertSee('Malayalam Speech');
        $announcerResponse->assertSee('Ahmed Fayiz');
        $announcerResponse->assertSee('READY FOR MIC ANNOUNCEMENT');

        // 4. Announcer announces on microphone and clicks "Mark as Announced"
        $announceActionResponse = $this->actingAs($this->announcer)
            ->post(route('announcer.announced', $result));

        $announceActionResponse->assertRedirect();
        $this->assertDatabaseHas('results', [
            'id' => $result->id,
            'status' => 'announced',
        ]);

        // 5. Result immediately arrives at Media Desk under 'announced' tab
        $mediaResponse = $this->actingAs($this->mediaUser)
            ->get(route('media.results.index', ['tab' => 'announced']));

        $mediaResponse->assertStatus(200);
        $mediaResponse->assertSee('Malayalam Speech');
        $mediaResponse->assertSee('Ready for Poster');

        // 6. Media Desk publishes public result
        $mediaPublishResponse = $this->actingAs($this->mediaUser)
            ->post(route('media.results.publish', $result));

        $mediaPublishResponse->assertRedirect();
        $this->assertDatabaseHas('results', [
            'id' => $result->id,
            'status' => 'published',
            'is_media_published' => true,
        ]);

        // 7. Verify public result page shows published program result
        $publicResponse = $this->get(route('results.show', $this->program));
        $publicResponse->assertStatus(200);
        $publicResponse->assertSee('Ahmed Fayiz');
    }
}
