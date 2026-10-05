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
use App\Services\ScheduleConflictService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ScheduleConflictAutoResolveTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Stage $stageNF3;

    protected Stage $stageS3;

    protected ProgramCategory $category;

    protected Zone $zone;

    protected Group $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@sahityotsav.com',
        ]);

        $this->stageNF3 = Stage::firstOrCreate(
            ['location' => 'NF3'],
            [
                'name' => 'Stage 05 — NF3',
                'code' => 'TEST-STG-05',
                'capacity' => 150,
                'status' => 'active',
            ]
        );

        $this->stageS3 = Stage::firstOrCreate(
            ['location' => 'S3'],
            [
                'name' => 'Stage 08 — S3',
                'code' => 'TEST-STG-08',
                'capacity' => 150,
                'status' => 'active',
            ]
        );

        $this->category = ProgramCategory::firstOrCreate(
            ['slug' => 'literature-verses'],
            [
                'name' => 'Literature & Verses',
            ]
        );

        $this->zone = Zone::firstOrCreate(
            ['name' => 'Mix Zone'],
            [
                'code' => 'MIX',
                'color' => '#10b981',
                'display_order' => 1,
            ]
        );

        $this->group = Group::firstOrCreate(
            ['code' => 'GRP-A'],
            [
                'name' => 'Team Alpha',
                'slug' => 'team-alpha',
                'manager_name' => 'Manager Alpha',
                'contact_number' => '9876543210',
                'color' => '#be1e2d',
                'points' => 0,
            ]
        );
    }

    public function test_it_smartly_resolves_student_and_stage_conflicts(): void
    {
        $date = '2026-10-06';

        $progA = Program::create([
            'name' => 'Malayalam Poem Writing',
            'code' => 'Q9-117',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'duration_minutes' => 30,
            'stage_id' => $this->stageNF3->id,
            'status' => 'upcoming',
        ]);

        $progB = Program::create([
            'name' => 'E-Poster',
            'code' => 'Q9-220',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'duration_minutes' => 30,
            'stage_id' => $this->stageS3->id,
            'status' => 'upcoming',
        ]);

        $progC = Program::create([
            'name' => 'Content Writing',
            'code' => 'Q9-229',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'duration_minutes' => 30,
            'stage_id' => $this->stageS3->id,
            'status' => 'upcoming',
        ]);

        // Schedules: Prog A on NF3 (16:40-17:10), Prog B on S3 (16:40-17:10) -> Clashing in time!
        // Prog C on S3 (17:10-17:40) -> Subsequent slot
        Schedule::create([
            'program_id' => $progA->id,
            'stage_id' => $this->stageNF3->id,
            'start_time' => Carbon::parse("{$date} 16:40:00"),
            'end_time' => Carbon::parse("{$date} 17:10:00"),
            'status' => 'scheduled',
        ]);

        Schedule::create([
            'program_id' => $progB->id,
            'stage_id' => $this->stageS3->id,
            'start_time' => Carbon::parse("{$date} 16:40:00"),
            'end_time' => Carbon::parse("{$date} 17:10:00"),
            'status' => 'scheduled',
        ]);

        Schedule::create([
            'program_id' => $progC->id,
            'stage_id' => $this->stageS3->id,
            'start_time' => Carbon::parse("{$date} 17:10:00"),
            'end_time' => Carbon::parse("{$date} 17:40:00"),
            'status' => 'scheduled',
        ]);

        $student = Student::create([
            'name' => 'Zayd Ahmad',
            'student_id' => '1001',
            'group_id' => $this->group->id,
            'qr_token' => Str::random(32),
        ]);

        // Student is in both Prog A and Prog B (both at 16:40)
        ProgramEntry::create([
            'program_id' => $progA->id,
            'student_id' => $student->id,
            'group_id' => $this->group->id,
            'chest_number' => '1001',
            'status' => 'approved',
        ]);

        ProgramEntry::create([
            'program_id' => $progB->id,
            'student_id' => $student->id,
            'group_id' => $this->group->id,
            'chest_number' => '1001',
            'status' => 'approved',
        ]);

        $service = app(ScheduleConflictService::class);
        $conflictsBefore = $service->detectAllScheduleConflicts($date);
        $this->assertEquals(1, $conflictsBefore['total_conflicts']);
        $this->assertCount(1, $conflictsBefore['student_conflicts']);

        // Call auto-resolve endpoint
        $response = $this->actingAs($this->admin)->post(route('admin.schedules.auto-resolve'), [
            'date' => $date,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $conflictsAfter = $service->detectAllScheduleConflicts($date);
        $this->assertEquals(0, $conflictsAfter['total_conflicts'], 'All clashes must be eliminated');
    }

    public function test_it_returns_json_response_and_resolves_via_api(): void
    {
        $date = '2026-10-07';

        $prog1 = Program::create([
            'name' => 'Arabic Story Writing',
            'code' => 'Q9-112',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'duration_minutes' => 30,
            'stage_id' => $this->stageNF3->id,
            'status' => 'upcoming',
        ]);

        $prog2 = Program::create([
            'name' => 'AI Poem',
            'code' => 'Q9-212',
            'category_id' => $this->category->id,
            'zone_id' => $this->zone->id,
            'type' => 'individual',
            'duration_minutes' => 30,
            'stage_id' => $this->stageS3->id,
            'status' => 'upcoming',
        ]);

        Schedule::create([
            'program_id' => $prog1->id,
            'stage_id' => $this->stageNF3->id,
            'start_time' => Carbon::parse("{$date} 21:45:00"),
            'end_time' => Carbon::parse("{$date} 22:15:00"),
            'status' => 'scheduled',
        ]);

        Schedule::create([
            'program_id' => $prog2->id,
            'stage_id' => $this->stageS3->id,
            'start_time' => Carbon::parse("{$date} 21:45:00"),
            'end_time' => Carbon::parse("{$date} 22:15:00"),
            'status' => 'scheduled',
        ]);

        $student = Student::create([
            'name' => 'Bilal Faris',
            'student_id' => '1002',
            'group_id' => $this->group->id,
            'qr_token' => Str::random(32),
        ]);

        ProgramEntry::create([
            'program_id' => $prog1->id,
            'student_id' => $student->id,
            'group_id' => $this->group->id,
            'chest_number' => '1002',
            'status' => 'approved',
        ]);

        ProgramEntry::create([
            'program_id' => $prog2->id,
            'student_id' => $student->id,
            'group_id' => $this->group->id,
            'chest_number' => '1002',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.schedules.auto-resolve'), [
            'date' => $date,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'remaining_conflicts' => 0,
        ]);

        $service = app(ScheduleConflictService::class);
        $conflicts = $service->detectAllScheduleConflicts($date);
        $this->assertEquals(0, $conflicts['total_conflicts']);
    }
}
