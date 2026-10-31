<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Student;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentVerificationProfileTest extends TestCase
{
    use RefreshDatabase;

    protected Student $student;

    protected Group $group;

    protected Zone $zone;

    protected ProgramCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->zone = Zone::create(['name' => 'South Zone', 'code' => 'SZ', 'slug' => 'south-zone']);
        $this->group = Group::create([
            'name' => 'Unio Hilmic',
            'code' => 'UNIO',
            'slug' => 'unio-hilmic',
            'color_hex' => '#be1e2d',
        ]);
        $this->category = ProgramCategory::create([
            'name' => 'Under High',
            'code' => 'UH',
            'slug' => 'under-high',
            'min_class' => 8,
            'max_class' => 10,
        ]);

        $this->student = Student::create([
            'student_id' => 'QF4129',
            'name' => 'SALIKH CT',
            'chest_number' => '4129',
            'group_id' => $this->group->id,
            'zone_id' => $this->zone->id,
            'category' => 'UH3',
            'qr_token' => 'qr-salikh-test-token',
        ]);
    }

    public function test_verification_profile_hides_zero_count_cards_and_verified_entry_badge_when_no_results(): void
    {
        $prog = Program::create([
            'category_id' => $this->category->id,
            'name' => 'Malayalam Micro Fiction',
            'code' => 'Q9-141',
            'type' => 'individual',
            'status' => 'completed',
        ]);

        ProgramEntry::create([
            'program_id' => $prog->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'chest_number' => '4129',
            'status' => 'verified',
        ]);

        $response = $this->get(route('verify.student', $this->student->qr_token));

        $response->assertOk();
        $response->assertSee('SALIKH CT');
        $response->assertSee('Official Delegate Profile');
        $response->assertSee('Malayalam Micro Fiction');

        // Old static 0-count badges and boxes must never be shown
        $response->assertDontSee('Grade Achievements');
        $response->assertDontSee('Official Evaluation');
        $response->assertDontSee('Excellence');
        $response->assertDontSee('Proficient');
        $response->assertDontSee('Adequate');
        $response->assertDontSee('Satisfactory');
        $response->assertDontSee('0 Pinnacle');
        $response->assertDontSee('0 Eminent');
        $response->assertDontSee('0 Competent');
        $response->assertDontSee('VERIFIED ENTRY');
        $response->assertDontSee('CONFIRMED');

        // Portal navigation buttons must be removed
        $response->assertDontSee('Open Student Portal');
        $response->assertDontSee('Open in Presentation Desk');
    }

    public function test_verification_profile_shows_only_achieved_programs_with_prizes_and_grades(): void
    {
        // 1. Program 1: Student wins 1st prize with Grade A+
        $prog1 = Program::create([
            'category_id' => $this->category->id,
            'name' => 'Elocution English',
            'code' => 'Q9-101',
            'type' => 'individual',
            'status' => 'completed',
        ]);
        $entry1 = ProgramEntry::create([
            'program_id' => $prog1->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'chest_number' => '4129',
            'status' => 'verified',
        ]);
        Result::create([
            'program_id' => $prog1->id,
            'first_entry_id' => $entry1->id,
            'first_grade' => 'A+',
            'status' => 'published',
            'is_media_published' => true,
        ]);

        // 2. Program 2: Student wins 2nd prize without grade
        $prog2 = Program::create([
            'category_id' => $this->category->id,
            'name' => 'Calligraphy Arabic',
            'code' => 'Q9-102',
            'type' => 'individual',
            'status' => 'completed',
        ]);
        $entry2 = ProgramEntry::create([
            'program_id' => $prog2->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'chest_number' => '4129',
            'status' => 'verified',
        ]);
        Result::create([
            'program_id' => $prog2->id,
            'second_entry_id' => $entry2->id,
            'second_grade' => null,
            'status' => 'published',
            'is_media_published' => true,
        ]);

        // 3. Program 3: Student has no prize, no grade
        $prog3 = Program::create([
            'category_id' => $this->category->id,
            'name' => 'Water Color Painting',
            'code' => 'Q9-103',
            'type' => 'individual',
            'status' => 'completed',
        ]);
        ProgramEntry::create([
            'program_id' => $prog3->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'chest_number' => '4129',
            'status' => 'verified',
        ]);

        $response = $this->get(route('verify.student', $this->student->qr_token));

        $response->assertOk();
        $response->assertSee('Competition Results');
        $response->assertSee('Elocution English');
        $response->assertSee('1st Prize');
        $response->assertSee('Grade A+');

        $response->assertSee('Calligraphy Arabic');
        $response->assertSee('2nd Prize');

        // Program 3 without prize or grade must NOT appear in the Competition Results section
        $response->assertDontSee('3rd Prize');
        $response->assertDontSee('VERIFIED ENTRY');
    }

    public function test_verification_profile_does_not_reveal_results_or_coins_until_media_published(): void
    {
        $prog = Program::create([
            'category_id' => $this->category->id,
            'name' => 'Secret Ghazal Competition',
            'code' => 'Q9-999',
            'type' => 'individual',
            'status' => 'completed',
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $prog->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'chest_number' => '4129',
            'status' => 'verified',
        ]);

        $result = Result::create([
            'program_id' => $prog->id,
            'first_entry_id' => $entry->id,
            'first_grade' => 'A+',
            'status' => 'published',
            'is_media_published' => false, // Not yet published by Media Desk!
            'published_at' => now(),
        ]);

        // When scanned, the result must be completely hidden
        $response = $this->get(route('verify.student', $this->student->qr_token));
        $response->assertOk();
        $response->assertDontSee('Competition Results');
        $response->assertDontSee('1st Prize');
        $response->assertDontSee('Grade A+');

        // Also public results page must not show it in published results
        $pubResponse = $this->get(route('results.index'));
        $pubResponse->assertOk();
        $this->assertCount(0, $pubResponse->viewData('results'));
        $pubResponse->assertSee('No results match the selected filters');

        // Now Media Desk publishes the poster & result
        $result->update([
            'is_media_published' => true,
            'media_published_at' => now(),
        ]);

        // Now when scanned, it reveals the achievements
        $response2 = $this->get(route('verify.student', $this->student->qr_token));
        $response2->assertOk();
        $response2->assertSee('Competition Results');
        $response2->assertSee('1st Prize');
        $response2->assertSee('Grade A+');

        // And public results page now displays it in published results
        $pubResponse2 = $this->get(route('results.index'));
        $pubResponse2->assertOk();
        $this->assertCount(1, $pubResponse2->viewData('results'));
        $pubResponse2->assertSee('Download Poster');
    }
}
