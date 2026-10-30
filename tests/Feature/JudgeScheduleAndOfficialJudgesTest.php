<?php

namespace Tests\Feature;

use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class JudgeScheduleAndOfficialJudgesTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_official_judges_command_creates_all_judges_without_assigning_programs(): void
    {
        $exitCode = Artisan::call('app:sync-official-judges');
        $this->assertSame(0, $exitCode);

        // Verify key judges exist
        $this->assertDatabaseHas('judges', ['name' => 'Rafi Ahsani']);
        $this->assertDatabaseHas('judges', ['name' => 'Shahabas Master']);
        $this->assertDatabaseHas('judges', ['name' => 'TP Cheruvadi']);
        $this->assertDatabaseHas('judges', ['name' => 'Noufal Master']);
        $this->assertDatabaseHas('judges', ['name' => 'Abdurahman Hikami']);
        $this->assertDatabaseHas('judges', ['name' => 'Qadar Saqafi']);
        $this->assertDatabaseHas('judges', ['name' => 'Shanid Saqafi']);
        $this->assertDatabaseHas('judges', ['name' => 'Anwar Latheefi']);
        $this->assertDatabaseHas('judges', ['name' => 'Yoonus Saqafi']);
        $this->assertDatabaseHas('judges', ['name' => 'Shaheed Nurani']);
        $this->assertDatabaseHas('judges', ['name' => 'Muzammil Nurani']);
        $this->assertDatabaseHas('judges', ['name' => 'Sinan Nurani']);
        $this->assertDatabaseHas('judges', ['name' => 'Irfan Suraiji Koyilandi']);
        $this->assertDatabaseHas('judges', ['name' => 'Shareef Saqafi']);
        $this->assertDatabaseHas('judges', ['name' => 'Adv. Zain Saqafi Mukkam']);
        $this->assertDatabaseHas('judges', ['name' => 'Sanahul Musthafa Saqafi']);
        $this->assertDatabaseHas('judges', ['name' => 'Adv. Saifudheen Saqafi']);
        $this->assertDatabaseHas('judges', ['name' => 'CK Muhammed Iringannur']);
        $this->assertDatabaseHas('judges', ['name' => 'Shakeer Master']);
        $this->assertDatabaseHas('judges', ['name' => 'MT Shihabudheen Saqafi']);
        $this->assertDatabaseHas('judges', ['name' => 'Hafiz Abdulla Suraiji']);
        $this->assertDatabaseHas('judges', ['name' => 'Hafiz Nasrudheen Adany']);
        $this->assertDatabaseHas('judges', ['name' => 'Mubashir Nurani']);
        $this->assertDatabaseHas('judges', ['name' => 'Majid Saqafi Thaachanna']);
        $this->assertDatabaseHas('judges', ['name' => 'Ustd. Umarali Saqafi']);
        $this->assertDatabaseHas('judges', ['name' => 'Ustd. Saleem Saqafi']);
        $this->assertDatabaseHas('judges', ['name' => 'Umair Bukhari Cherumuttam']);

        // Verify judge has user account and 4-digit PIN
        $judge = Judge::where('name', 'Rafi Ahsani')->first();
        $this->assertNotNull($judge);
        $this->assertNotNull($judge->user);
        $this->assertSame('judge', $judge->user->role);
        $this->assertMatchesRegularExpression('/^[0-9]{4}$/', $judge->access_code);

        // Verify programs were NOT assigned automatically
        $this->assertSame(0, $judge->programs()->count());

        // Idempotency: re-running does not duplicate or alter PINs
        $originalPin = $judge->access_code;
        Artisan::call('app:sync-official-judges');
        $judge->refresh();
        $this->assertSame($originalPin, $judge->access_code);
    }

    public function test_judge_dashboard_displays_schedule_buttons(): void
    {
        $user = User::factory()->create([
            'role' => 'judge',
            'name' => 'Official Judge Test',
        ]);

        $judge = Judge::create([
            'user_id' => $user->id,
            'name' => 'Official Judge Test',
            'access_code' => '4821',
        ]);

        $response = $this->actingAs($user)->get(route('judge.dashboard'));

        $response->assertOk();
        $response->assertSee('My Schedule');
        $response->assertSee('View My Schedule');
        $response->assertSee('Quick Timetable');
        $response->assertSee(route('judge.schedule'));
    }

    public function test_judge_schedule_view_renders_assigned_programs_with_times(): void
    {
        $user = User::factory()->create([
            'role' => 'judge',
            'name' => 'Juror Test',
        ]);

        $judge = Judge::create([
            'user_id' => $user->id,
            'name' => 'Juror Test',
            'access_code' => '5920',
        ]);

        $category = ProgramCategory::create([
            'name' => 'Speech',
            'slug' => 'speech',
            'code' => 'SP',
        ]);

        $stage = Stage::create([
            'name' => 'Stage 01 — Grand Amphitheatre',
            'code' => 'STG-01',
            'status' => 'active',
        ]);

        $program = Program::create([
            'category_id' => $category->id,
            'stage_id' => $stage->id,
            'name' => 'Malayalam Speech',
            'code' => 'Q9-103',
            'type' => 'individual',
            'scheduled_time' => now()->addHours(2),
            'duration_minutes' => 45,
        ]);

        $judge->programs()->attach($program->id);

        $response = $this->actingAs($user)->get(route('judge.schedule'));

        $response->assertOk();
        $response->assertSee('Malayalam Speech');
        $response->assertSee('Q9-103');
        $response->assertSee('Stage 01 — Grand Amphitheatre');
        $response->assertSee('45 Mins');
        $response->assertSee('Juror Test — Program Schedule');
        $response->assertSee(route('judge.evaluate', $program));
    }
}
