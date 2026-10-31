<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnnouncerAutomaticResultNumberingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $announcer;

    protected Group $groupA;

    protected Group $groupB;

    protected ProgramCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        Result::ensureSchema();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Controller',
            'email' => 'admin@quaf.test',
        ]);

        $this->announcer = User::factory()->create([
            'role' => 'announcer',
            'name' => 'Stage Announcer',
            'email' => 'announcer@quaf.test',
        ]);

        $this->groupA = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#be1e2d',
        ]);

        $this->groupB = Group::create([
            'name' => 'Aura Zeic',
            'code' => 'AURA',
            'slug' => 'aura-zeic',
            'color_hex' => '#009444',
        ]);

        $this->category = ProgramCategory::create([
            'name' => 'Junior',
            'code' => 'JUN',
            'slug' => 'junior',
            'min_class' => 5,
            'max_class' => 7,
        ]);
    }

    protected function createProgramAndResult(string $code, string $name, string $zone = 'A Zone'): array
    {
        $program = Program::create([
            'name' => $name,
            'code' => $code,
            'category_id' => $this->category->id,
            'eligibility' => $zone,
            'type' => 'individual',
            'status' => 'completed',
        ]);

        $student = Student::create([
            'student_id' => 'STU-'.Str::upper(Str::random(6)),
            'name' => 'Student '.$code,
            'group_id' => $this->groupA->id,
            'category' => 'junior',
            'qr_token' => Str::random(32),
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'group_id' => $this->groupA->id,
            'chest_number' => 'C-'.rand(100, 999),
            'status' => 'verified',
            'attendance_status' => 'present',
        ]);

        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entry->id,
            'status' => 'verified',
            'is_media_published' => false,
        ]);

        return [$program, $result, $entry];
    }

    public function test_transferring_result_to_announcer_automatically_assigns_sequential_result_number(): void
    {
        // 1. Create 2 separate program results in verified/draft state (no result_number yet)
        [$prog1, $res1] = $this->createProgramAndResult('P101', 'Malayalam Speech', 'A Zone');
        [$prog2, $res2] = $this->createProgramAndResult('P102', 'English Elocution', 'B Zone');

        $this->assertNull($res1->fresh()->result_number);
        $this->assertNull($res2->fresh()->result_number);

        // 2. Transfer 1st result to Announcer Desk
        $response1 = $this->actingAs($this->admin)->post(route('admin.results.send-to-announcer', $res1));
        $response1->assertRedirect();

        // Must automatically assign number 1 (01) without waiting for media publishing
        $fresh1 = $res1->fresh();
        $this->assertEquals('send', $fresh1->status);
        $this->assertEquals(1, $fresh1->result_number);
        $this->assertEquals('01', $fresh1->formatted_result_number);
        $this->assertFalse($fresh1->is_media_published);

        // 3. Transfer 2nd result to Announcer Desk
        $response2 = $this->actingAs($this->admin)->post(route('admin.results.send-to-announcer', $res2));
        $response2->assertRedirect();

        // Must automatically assign number 2 (02)
        $fresh2 = $res2->fresh();
        $this->assertEquals('send', $fresh2->status);
        $this->assertEquals(2, $fresh2->result_number);
        $this->assertEquals('02', $fresh2->formatted_result_number);

        // 4. Announcer Desk View verification
        $announcerView = $this->actingAs($this->announcer)->get(route('announcer.index', ['tab' => 'ready']));
        $announcerView->assertOk();

        // Verify Result Number, Zone, and Program Name appear in announcer card
        $announcerView->assertSee('Result #01');
        $announcerView->assertSee('Result #02');
        $announcerView->assertSee('A Zone');
        $announcerView->assertSee('B Zone');
        $announcerView->assertSee('Malayalam Speech');
        $announcerView->assertSee('English Elocution');
        $announcerView->assertSee('Mark as Announced');
    }

    public function test_tenth_result_gets_number_10_and_shows_in_announcer_panel(): void
    {
        // Create and transfer 10 results
        $results = [];
        for ($i = 1; $i <= 10; $i++) {
            $code = 'P'.(200 + $i);
            [$prog, $res] = $this->createProgramAndResult($code, 'Competition '.$i, 'Zone '.$i);
            $this->actingAs($this->admin)->post(route('admin.results.send-to-announcer', $res));
            $results[$i] = $res->fresh();
        }

        // Verify the 1st has 1 and 10th has 10
        $this->assertEquals(1, $results[1]->result_number);
        $this->assertEquals('01', $results[1]->formatted_result_number);

        $this->assertEquals(10, $results[10]->result_number);
        $this->assertEquals('10', $results[10]->formatted_result_number);

        // Check Announcer panel for the 10th result
        $announcerResponse = $this->actingAs($this->announcer)->get(route('announcer.index', ['tab' => 'ready']));
        $announcerResponse->assertOk();
        $announcerResponse->assertSee('Result #10');
        $announcerResponse->assertSee('Competition 10');
        $announcerResponse->assertSee('Zone 10');
        $announcerResponse->assertSee('Mark as Announced');

        // Check Admin Declared Results table
        $declaredResponse = $this->actingAs($this->admin)->get(route('admin.results.declared'));
        $declaredResponse->assertOk();
        $declaredResponse->assertSee('#10');
        $declaredResponse->assertSee('Competition 10');
    }

    public function test_batch_send_to_announcer_assigns_sequential_numbers(): void
    {
        [$p1, $r1] = $this->createProgramAndResult('P301', 'Program One', 'Zone X');
        [$p2, $r2] = $this->createProgramAndResult('P302', 'Program Two', 'Zone Y');

        $this->actingAs($this->admin)->post(route('admin.results.batch-send-announcer'), [
            'result_ids' => [$r1->id, $r2->id],
        ]);

        $this->assertEquals(1, $r1->fresh()->result_number);
        $this->assertEquals(2, $r2->fresh()->result_number);
    }

    public function test_media_studio_and_save_poster_never_crashes_with_unknown_column_error(): void
    {
        $mediaUser = User::factory()->create([
            'role' => 'media_team',
            'name' => 'Media Studio Designer',
            'email' => 'media-studio@quaf.test',
        ]);

        [$program, $result] = $this->createProgramAndResult('P401', 'Poster Competition', 'Zone Alpha');

        // Studio view renders 200 OK
        $studioResponse = $this->actingAs($mediaUser)->get(route('media.results.studio', $result));
        $studioResponse->assertOk();
        $studioResponse->assertSee('Poster Competition');

        // Saving poster does not throw 1054 Unknown column error
        $saveResponse = $this->actingAs($mediaUser)->post(route('media.results.save-poster', $result), [
            'publish_now' => true,
        ]);
        $saveResponse->assertRedirect();
        $this->assertDatabaseHas('results', [
            'id' => $result->id,
            'status' => 'published',
        ]);
    }

    public function test_resync_repairs_duplicate_and_gap_numbers_across_fifteen_declared_results(): void
    {
        // Simulate the scenario reported by the user with 15 results where numbers were duplicated or broken
        $programs = [];
        $results = [];

        for ($i = 1; $i <= 15; $i++) {
            [$p, $r] = $this->createProgramAndResult('P5'.sprintf('%02d', $i), 'Program '.$i, 'Zone '.$i);
            $programs[$i] = $p;
            $results[$i] = $r;

            // Intentionally create duplicate result numbers to simulate production issue:
            // e.g. results 1..5 having numbers 1..5, and results 12..15 also having 1,2,4,5
            $bogusNum = ($i > 11) ? ($i - 11) : $i;
            DB::table('results')->where('id', $r->id)->update([
                'status' => 'send',
                'result_number' => $bogusNum,
                'published_at' => Carbon::now()->subMinutes(20 - $i),
            ]);
        }

        // Verify duplicates exist prior to resync
        $preUnique = DB::table('results')->whereIn('id', collect($results)->pluck('id'))->pluck('result_number')->unique()->count();
        $this->assertLessThan(15, $preUnique);

        // Run self-healing resync
        Result::resyncResultNumbers();

        // Verify that all 15 results now have strictly 1..15 with 0 duplicates
        $postNumbers = DB::table('results')
            ->whereIn('id', collect($results)->pluck('id'))
            ->orderBy('result_number', 'asc')
            ->pluck('result_number')
            ->all();

        $this->assertEquals(range(1, 15), $postNumbers);

        // Verify declaring a 16th result gets 16
        [$p16, $r16] = $this->createProgramAndResult('P516', 'Program 16', 'Zone 16');
        $this->actingAs($this->admin)->post(route('admin.results.send-to-announcer', $r16));
        $this->assertEquals(16, $r16->fresh()->result_number);
        $this->assertEquals('16', $r16->fresh()->formatted_result_number);

        // Declared Results view shows #16 down to #01
        $declaredView = $this->actingAs($this->admin)->get(route('admin.results.declared'));
        $declaredView->assertOk();
        $declaredView->assertSee('#16');
        $declaredView->assertSee('#15');
        $declaredView->assertSee('#01');
    }
}
