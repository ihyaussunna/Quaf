<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminFormsPrintTest extends TestCase
{
    use RefreshDatabase;

    protected ProgramCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = ProgramCategory::create([
            'name' => 'General Stage Arts',
            'slug' => 'general-stage-arts',
        ]);
    }

    public function test_call_list_renders_with_header_logo_and_quaf_format(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $program = Program::create([
            'category_id' => $this->category->id,
            'name' => 'Malayalam Speech',
            'code' => 'Q9 - 103',
            'type' => 'individual',
            'eligibility' => 'A Zone',
            'participant_count' => 2,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.forms.call-list', ['program' => $program->id]));

        $response->assertStatus(200);
        $response->assertSee('images/forms-header-logo.png');
        $response->assertSee('CALL LIST');
        $response->assertSee('Malayalam Speech');
        $response->assertSee('Q9 - 103');
        $response->assertSee('Student Id');
        $response->assertSee('Code Letter');
    }

    public function test_call_list_standalone_print_view(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $program = Program::create([
            'category_id' => $this->category->id,
            'name' => 'Qira\'th',
            'code' => 'Q9 - 101',
            'type' => 'individual',
            'eligibility' => 'A Zone',
            'participant_count' => 2,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.forms.call-list', [
            'program' => $program->id,
            'print' => 1,
        ]));

        $response->assertStatus(200);
        $response->assertSee('images/forms-header-logo.png');
        $response->assertSee('CALL LIST');
        $response->assertSee('Qira\'th');
        $response->assertSee('Student Id');
        $response->assertSee('Code Letter');
        $response->assertSee('Sign');
    }

    public function test_evaluation_sheet_renders_with_header_logo_and_code_letters(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $program = Program::create([
            'category_id' => $this->category->id,
            'name' => 'Urdu Speech',
            'code' => 'Q9 - 105',
            'type' => 'individual',
            'eligibility' => 'A Zone',
            'participant_count' => 2,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.forms.evaluation', ['program' => $program->id]));

        $response->assertStatus(200);
        $response->assertSee('images/forms-header-logo.png');
        $response->assertSee('Evaluation Sheet');
        $response->assertSee('Urdu Speech');
        $response->assertSee('Out of 100');
    }

    public function test_evaluation_sheet_standalone_print_view(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $program = Program::create([
            'category_id' => $this->category->id,
            'name' => 'English Poem Writing',
            'code' => 'Q9 - 116',
            'type' => 'individual',
            'eligibility' => 'A Zone',
            'participant_count' => 2,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.forms.evaluation', [
            'program' => $program->id,
            'print' => 1,
        ]));

        $response->assertStatus(200);
        $response->assertSee('images/forms-header-logo.png');
        $response->assertSee('Evaluation Sheet');
        $response->assertSee('English Poem Writing');
        $response->assertSee('Out of 100');
    }

    public function test_chest_slips_renders_successfully(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $group = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric',
            'color_hex' => '#56286b',
        ]);

        $student = Student::create([
            'student_id' => 'QF1001',
            'name' => 'Faris Test',
            'group_id' => $group->id,
            'admission_number' => 'ADM001',
            'class_level' => 'D4',
            'category' => 'A Zone',
            'qr_token' => Str::random(32),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.idcards.chest-slips'));

        $response->assertStatus(200);
        $response->assertSee('Contestant Chest Number Slips');
        $response->assertSee('QF1001');
        $response->assertSee('Faris Test');
    }
}
