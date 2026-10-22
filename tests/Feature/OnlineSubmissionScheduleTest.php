<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlineSubmissionScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_correctly_identifies_explicit_submission_competitions(): void
    {
        $category = ProgramCategory::firstOrCreate(
            ['slug' => 'offstage-media'],
            ['name' => 'Offstage Media']
        );

        $zone = Zone::firstOrCreate(
            ['slug' => 'mix-zone'],
            ['name' => 'Mix Zone', 'code' => 'MIX']
        );

        $onlineProg = Program::create([
            'name' => 'Vlog Malayalam',
            'code' => 'Q9-218',
            'category_id' => $category->id,
            'zone_id' => $zone->id,
            'is_stage' => false,
            'submission_type' => 'online',
            'type' => 'individual',
        ]);

        $officeProg = Program::create([
            'name' => 'Working Model',
            'code' => 'Q9-233',
            'category_id' => $category->id,
            'zone_id' => $zone->id,
            'is_stage' => false,
            'submission_type' => 'office',
            'type' => 'group',
        ]);

        $stageEvent = Program::create([
            'name' => 'Elocution Malayalam',
            'code' => 'Q9-101',
            'category_id' => $category->id,
            'zone_id' => $zone->id,
            'is_stage' => true,
            'type' => 'individual',
        ]);

        $regularOffstage = Program::create([
            'name' => 'Essay Writing',
            'code' => 'Q9-102',
            'category_id' => $category->id,
            'zone_id' => $zone->id,
            'is_stage' => false,
            'type' => 'individual',
        ]);

        $this->assertTrue($onlineProg->isSubmissionProgram());
        $this->assertTrue($onlineProg->isOnlineSubmission());
        $this->assertFalse($onlineProg->isOfficeSubmission());

        $this->assertTrue($officeProg->isSubmissionProgram());
        $this->assertTrue($officeProg->isOfficeSubmission());
        $this->assertFalse($officeProg->isOnlineSubmission());

        $this->assertFalse($stageEvent->isSubmissionProgram());
        $this->assertFalse($regularOffstage->isSubmissionProgram());
    }

    public function test_admin_can_bulk_set_submission_mode(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = ProgramCategory::firstOrCreate(
            ['slug' => 'media'],
            ['name' => 'Media']
        );

        $prog1 = Program::create([
            'name' => 'Short Film',
            'code' => 'Q9-501',
            'category_id' => $category->id,
            'is_stage' => false,
            'type' => 'group',
        ]);

        $prog2 = Program::create([
            'name' => 'Handmade Craft',
            'code' => 'Q9-502',
            'category_id' => $category->id,
            'is_stage' => false,
            'type' => 'individual',
        ]);

        // Bulk set prog1 to online
        $response = $this->actingAs($admin)->postJson(route('admin.programs.bulk-set-submission-mode'), [
            'mode' => 'online',
            'program_ids' => [$prog1->id],
            'deadline' => '2026-10-31 18:00:00',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $prog1->refresh();
        $this->assertEquals('online', $prog1->submission_type);
        $this->assertFalse((bool) $prog1->is_stage);
        $this->assertNotNull($prog1->scheduled_time);

        // Bulk set prog2 to office
        $response2 = $this->actingAs($admin)->postJson(route('admin.programs.bulk-set-submission-mode'), [
            'mode' => 'office',
            'program_ids' => [$prog2->id],
        ]);

        $response2->assertOk();
        $prog2->refresh();
        $this->assertEquals('office', $prog2->submission_type);
        $this->assertFalse((bool) $prog2->is_stage);
    }

    public function test_admin_can_create_and_update_program_submission_mode(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = ProgramCategory::firstOrCreate(
            ['slug' => 'digital'],
            ['name' => 'Digital Arts']
        );

        $zone = Zone::firstOrCreate(
            ['slug' => 'general-zone'],
            ['name' => 'General Zone', 'code' => 'GEN']
        );

        // 1. Create with online submission mode
        $createResponse = $this->actingAs($admin)->post(route('admin.programs.store'), [
            'name' => 'Digital Poster Making',
            'code' => 'Q9-DPM',
            'category_id' => $category->id,
            'zone_id' => $zone->id,
            'type' => 'individual',
            'submission_mode' => 'online',
            'scheduled_time' => '2026-10-31 16:30:00',
            'points_weight' => 1.0,
            'status' => 'upcoming',
        ]);

        $createResponse->assertRedirect(route('admin.programs.index'));
        $program = Program::where('code', 'Q9-DPM')->firstOrFail();
        $this->assertEquals('online', $program->submission_type);
        $this->assertFalse((bool) $program->is_stage);
        $this->assertTrue($program->isOnlineSubmission());

        // 2. Update to office submission mode
        $updateResponse = $this->actingAs($admin)->put(route('admin.programs.update', $program), [
            'name' => 'Digital Poster Making',
            'code' => 'Q9-DPM',
            'category_id' => $category->id,
            'zone_id' => $zone->id,
            'type' => 'individual',
            'submission_mode' => 'office',
            'scheduled_time' => '2026-10-31 19:00:00',
            'points_weight' => 1.0,
            'status' => 'upcoming',
        ]);

        $updateResponse->assertRedirect();
        $program->refresh();
        $this->assertEquals('office', $program->submission_type);
        $this->assertFalse((bool) $program->is_stage);
        $this->assertTrue($program->isOfficeSubmission());

        // 3. Update to stage competition
        $updateStageResponse = $this->actingAs($admin)->put(route('admin.programs.update', $program), [
            'name' => 'Digital Poster Making',
            'code' => 'Q9-DPM',
            'category_id' => $category->id,
            'zone_id' => $zone->id,
            'type' => 'individual',
            'submission_mode' => 'stage',
            'points_weight' => 1.0,
            'status' => 'upcoming',
        ]);

        $updateStageResponse->assertRedirect();
        $program->refresh();
        $this->assertNull($program->submission_type);
        $this->assertTrue((bool) $program->is_stage);
        $this->assertFalse($program->isSubmissionProgram());
    }

    public function test_public_schedule_displays_submission_desks_and_filters(): void
    {
        $category = ProgramCategory::firstOrCreate(
            ['slug' => 'offstage-media'],
            ['name' => 'Offstage Media']
        );

        $vlog = Program::create([
            'name' => 'Vlog Malayalam',
            'code' => 'Q9-218',
            'category_id' => $category->id,
            'is_stage' => false,
            'submission_type' => 'online',
            'type' => 'individual',
        ]);

        $project = Program::create([
            'name' => 'Project',
            'code' => 'Q9-238',
            'category_id' => $category->id,
            'is_stage' => false,
            'submission_type' => 'office',
            'type' => 'group',
        ]);

        $this->assertTrue($vlog->isOnlineSubmission());
        $this->assertTrue($project->isOfficeSubmission());

        // Test filtering by online
        $responseOnline = $this->get(route('schedule.index', ['stage' => 'online']));
        $responseOnline->assertOk();
        $responseOnline->assertSee('Vlog Malayalam');
        $responseOnline->assertSee('ഓൺലൈൻ സബ്മിഷൻ');

        // Test filtering by office
        $responseOffice = $this->get(route('schedule.index', ['stage' => 'office']));
        $responseOffice->assertOk();
        $responseOffice->assertSee('Project');
        $responseOffice->assertSee('ഫെസ്റ്റിവൽ ഓഫീസ്');
    }

    public function test_show_by_program_auto_initializes_submission_form(): void
    {
        $category = ProgramCategory::create([
            'name' => 'Offstage Media',
            'slug' => 'offstage-media',
        ]);

        $project = Program::create([
            'name' => 'Project',
            'code' => 'Q9-238',
            'category_id' => $category->id,
            'is_stage' => false,
            'submission_type' => 'online',
            'type' => 'group',
        ]);

        $this->assertNull($project->onlineSubmissionForm);

        $response = $this->get(route('online-submission.by-program', $project));
        $response->assertRedirect();

        $project->refresh();
        $this->assertNotNull($project->onlineSubmissionForm);
        $this->assertEquals('Project', $project->onlineSubmissionForm->title);
    }
}
