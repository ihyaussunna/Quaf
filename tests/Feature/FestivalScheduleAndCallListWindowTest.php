<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Schedule;
use App\Models\Stage;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FestivalScheduleAndCallListWindowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $greenRoomUser;

    protected User $programCommitteeUser;

    protected Stage $stage;

    protected ProgramCategory $category;

    protected Program $program;

    protected Student $student;

    protected ProgramEntry $entry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->greenRoomUser = User::factory()->create([
            'role' => 'green_room_coordinator',
        ]);

        $this->programCommitteeUser = User::factory()->create([
            'role' => 'program_committee',
        ]);

        $group = Group::create([
            'name' => 'Team Emerald',
            'slug' => 'team-emerald',
            'code' => 'EME',
            'color_hex' => '#059669',
        ]);

        $zone = Zone::create([
            'name' => 'Sub-Junior',
            'code' => 'SUB-JR',
            'slug' => 'sub-junior',
        ]);

        $this->category = ProgramCategory::create([
            'name' => 'Stage Events',
            'slug' => 'stage-events',
        ]);

        $this->stage = Stage::create([
            'name' => 'Main Auditorium',
            'code' => 'STG01',
            'status' => 'active',
        ]);

        $this->program = Program::create([
            'name' => 'Qur\'an Recitation',
            'code' => 'QR01',
            'category_id' => $this->category->id,
            'stage_id' => $this->stage->id,
            'duration_minutes' => 30,
            'type' => 'individual',
            'status' => 'upcoming',
        ]);

        $this->student = Student::create([
            'group_id' => $group->id,
            'zone_id' => $zone->id,
            'student_id' => 'STU-101',
            'name' => 'Ahmad Bilal',
            'chest_number' => '501',
            'qr_token' => 'qr-stu-101',
            'category' => 'Sub-Junior',
            'status' => 'active',
        ]);

        $this->entry = ProgramEntry::create([
            'program_id' => $this->program->id,
            'group_id' => $group->id,
            'student_id' => $this->student->id,
            'chest_number' => '501',
            'attendance_status' => 'waiting',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_green_room_is_blocked_until_10_minutes_before_scheduled_time(): void
    {
        $startTime = Carbon::parse('2026-10-15 10:00:00');
        $endTime = Carbon::parse('2026-10-15 10:30:00');

        Schedule::create([
            'program_id' => $this->program->id,
            'stage_id' => $this->stage->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        // 30 minutes before schedule: 09:30 AM
        Carbon::setTestNow(Carbon::parse('2026-10-15 09:30:00'));

        $response = $this->actingAs($this->greenRoomUser)
            ->postJson(route('greenroom.mark-attendance', $this->entry), [
                'status' => 'present',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('10 മിനിറ്റ്', $response->json('message'));

        // Admin override is allowed even outside window
        $adminResponse = $this->actingAs($this->admin)
            ->postJson(route('greenroom.mark-attendance', $this->entry), [
                'status' => 'present',
            ]);

        $adminResponse->assertOk();
        $this->entry->refresh();
        $this->assertEquals('present', $this->entry->attendance_status);
    }

    public function test_green_room_can_mark_attendance_within_10_minutes_window(): void
    {
        $startTime = Carbon::parse('2026-10-15 10:00:00');
        $endTime = Carbon::parse('2026-10-15 10:30:00');

        Schedule::create([
            'program_id' => $this->program->id,
            'stage_id' => $this->stage->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        // 5 minutes before scheduled start time: 09:55 AM
        Carbon::setTestNow(Carbon::parse('2026-10-15 09:55:00'));

        $response = $this->actingAs($this->greenRoomUser)
            ->postJson(route('greenroom.mark-attendance', $this->entry), [
                'status' => 'present',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'attendance_status' => 'present',
            'code_letter' => 'A',
        ]);

        $this->entry->refresh();
        $this->assertEquals('present', $this->entry->attendance_status);
        $this->assertEquals('A', $this->entry->code_letter);
    }

    public function test_call_list_automatically_locks_after_scheduled_end_time(): void
    {
        $startTime = Carbon::parse('2026-10-15 10:00:00');
        $endTime = Carbon::parse('2026-10-15 10:30:00');

        Schedule::create([
            'program_id' => $this->program->id,
            'stage_id' => $this->stage->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        // 5 minutes after scheduled end time: 10:35 AM
        Carbon::setTestNow(Carbon::parse('2026-10-15 10:35:00'));

        $response = $this->actingAs($this->greenRoomUser)
            ->postJson(route('greenroom.mark-attendance', $this->entry), [
                'status' => 'present',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('ഓട്ടോമാറ്റിക്കായി ലോക്ക് ചെയ്യപ്പെട്ടു', $response->json('message'));
    }

    public function test_admin_manual_lock_blocks_green_room_even_inside_window(): void
    {
        $startTime = Carbon::parse('2026-10-15 10:00:00');
        $endTime = Carbon::parse('2026-10-15 10:30:00');

        Schedule::create([
            'program_id' => $this->program->id,
            'stage_id' => $this->stage->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        // Manually lock call list as admin
        $this->program->update(['is_call_list_locked' => true]);

        // Current time is 10:05 AM (inside scheduled window)
        Carbon::setTestNow(Carbon::parse('2026-10-15 10:05:00'));

        $response = $this->actingAs($this->greenRoomUser)
            ->postJson(route('greenroom.mark-attendance', $this->entry), [
                'status' => 'present',
            ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Locked by Admin', $response->json('message'));
    }

    public function test_program_committee_can_access_schedules_and_offstage(): void
    {
        $responseIndex = $this->actingAs($this->programCommitteeUser)
            ->get(route('program-committee.schedules.index'));

        $responseIndex->assertOk();
        $responseIndex->assertSee('Festival Schedule');

        $responseOffstage = $this->actingAs($this->programCommitteeUser)
            ->get(route('program-committee.schedules.offstage'));

        $responseOffstage->assertOk();
        $responseOffstage->assertSee('Offstage');
    }

    public function test_green_room_desk_shows_scheduled_program_and_participants_dynamically(): void
    {
        $startTime = Carbon::parse('2026-10-15 10:00:00');
        $endTime = Carbon::parse('2026-10-15 10:30:00');

        Schedule::create([
            'program_id' => $this->program->id,
            'stage_id' => $this->stage->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-10-15 09:55:00'));

        $response = $this->actingAs($this->greenRoomUser)
            ->get(route('greenroom.index', ['stage_id' => $this->stage->id]));

        $response->assertOk();
        $response->assertSee($this->program->name);
        $response->assertSee($this->student->name);
        $response->assertDontSee('Announcer Tab');
    }

    public function test_announcer_tab_button_is_removed_from_call_list(): void
    {
        $response = $this->actingAs($this->greenRoomUser)
            ->get(route('greenroom.call-list', ['program_id' => $this->program->id]));

        $response->assertOk();
        $response->assertDontSee('Announcer Tab');
        $response->assertDontSee('അനൗൺസർ ടാബ്');
    }
}
