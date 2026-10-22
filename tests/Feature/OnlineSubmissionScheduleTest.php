<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlineSubmissionScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_correctly_identifies_submission_competitions(): void
    {
        $category = ProgramCategory::firstOrCreate(
            ['slug' => 'offstage-media'],
            ['name' => 'Offstage Media']
        );

        $zone = Zone::firstOrCreate(
            ['slug' => 'mix-zone'],
            ['name' => 'Mix Zone', 'code' => 'MIX']
        );

        $vlog = Program::create([
            'name' => 'Vlog Malayalam',
            'code' => 'Q9-218',
            'category_id' => $category->id,
            'zone_id' => $zone->id,
            'is_stage' => false,
            'type' => 'individual',
        ]);

        $podcast = Program::create([
            'name' => 'Podcast',
            'code' => 'Q9-233',
            'category_id' => $category->id,
            'zone_id' => $zone->id,
            'is_stage' => false,
            'type' => 'individual',
        ]);

        $stageEvent = Program::create([
            'name' => 'Elocution Malayalam',
            'code' => 'Q9-101',
            'category_id' => $category->id,
            'zone_id' => $zone->id,
            'is_stage' => true,
            'type' => 'individual',
        ]);

        $this->assertTrue($vlog->isSubmissionProgram());
        $this->assertTrue($podcast->isSubmissionProgram());
        $this->assertFalse($stageEvent->isSubmissionProgram());
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
            'type' => 'individual',
        ]);

        $project = Program::create([
            'name' => 'Project',
            'code' => 'Q9-238',
            'category_id' => $category->id,
            'is_stage' => false,
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
