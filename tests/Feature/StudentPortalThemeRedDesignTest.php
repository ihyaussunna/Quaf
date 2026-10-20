<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Stage;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortalThemeRedDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_login_page_renders_with_theme_red_design_and_no_yellow(): void
    {
        $response = $this->get('/student/login');

        $response->assertStatus(200);

        // Assert theme red classes and branding are present
        $response->assertSee('selection:bg-[#be1e2d]');
        $response->assertSee('#be1e2d');
        $response->assertSee('from-[#be1e2d]');
        $response->assertSee('Student Portal');

        // Assert old yellow color codes and amber classes are gone
        $response->assertDontSee('#f3bd2e');
        $response->assertDontSee('bg-amber-50');
    }

    public function test_student_dashboard_renders_with_red_and_yellow_gradient_wallet_and_global_header(): void
    {
        $group = Group::firstOrCreate(
            ['code' => 'LUMO'],
            [
                'name' => 'Lumo Fikric',
                'slug' => 'lumo-fikric',
                'color_hex' => '#56286B',
            ]
        );

        $zone = Zone::firstOrCreate(
            ['code' => 'B_ZONE'],
            [
                'name' => 'B Zone',
                'slug' => 'b-zone-unique',
            ]
        );

        $user = User::create([
            'name' => 'Bazil Neithala',
            'email' => 'bazil@student.quaf.org',
            'password' => bcrypt('secret123'),
            'role' => 'student',
        ]);

        $student = Student::create([
            'student_id' => 'QF2100',
            'name' => 'Bazil Neithala',
            'group_id' => $group->id,
            'zone_id' => $zone->id,
            'user_id' => $user->id,
            'category' => 'B Zone',
            'qr_token' => 'fixed-token-bazil',
            'coins_cache' => 15,
        ]);

        $stage = Stage::create([
            'name' => 'Stage 06',
            'code' => 'ST06',
            'order' => 1,
        ]);

        $program = Program::create([
            'code' => 'P101',
            'name' => 'Tuhfa Talent Test',
            'type' => 'individual',
            'stage_id' => $stage->id,
            'scheduled_time' => Carbon::now()->addDays(2),
        ]);

        ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'chest_number' => 'QF2100',
            'status' => 'verified',
        ]);

        $response = $this->actingAs($user)->get('/student');

        $response->assertStatus(200);

        // 1. Verify student profile hero has theme red elements
        $response->assertSee('#be1e2d');
        $response->assertSee('View QR Badge');
        $response->assertSee('from-[#be1e2d]');

        // 2. Verify Coin Wallet Banner has red and yellow gradient as requested
        $response->assertSee('QUAF FEST COIN WALLET');
        $response->assertSee('Available Balance');
        $response->assertSee('from-[#be1e2d]');
        $response->assertSee('to-[#f59e0b]');

        // 3. Verify Global Header on desktop (hidden lg:block) matching home page
        $response->assertSee('hidden lg:block');
        $response->assertSee('Student Portal');

        // 4. Verify Mobile Bottom Navigation bar on mobile
        $response->assertSee('lg:hidden fixed bottom-0');

        // 5. Verify Next Program stage slot and countdown has theme red styling
        $response->assertSee('NEXT PROGRAM STAGE SLOT');
        $response->assertSee('Tuhfa Talent Test');
        $response->assertSee('Stage 06');
    }

    public function test_student_digital_id_card_renders_with_theme_red_design(): void
    {
        $group = Group::firstOrCreate(
            ['code' => 'PACTO'],
            [
                'name' => 'Pacto Hikmic',
                'slug' => 'pacto-hikmic',
                'color_hex' => '#be1e2d',
            ]
        );

        $user = User::create([
            'name' => 'Student Passholder',
            'email' => 'pass@student.quaf.org',
            'password' => bcrypt('secret123'),
            'role' => 'student',
        ]);

        $student = Student::create([
            'student_id' => 'QF9999',
            'name' => 'Student Passholder',
            'group_id' => $group->id,
            'user_id' => $user->id,
            'category' => 'General',
            'qr_token' => 'fixed-token-passholder',
        ]);

        $response = $this->actingAs($user)->get(route('student.idcard'));

        $response->assertStatus(200);

        // Verify Theme Red accents
        $response->assertSee('#be1e2d');
        $response->assertSee('Dashboard');
        $response->assertDontSee('Print / Save Badge');
    }
}
