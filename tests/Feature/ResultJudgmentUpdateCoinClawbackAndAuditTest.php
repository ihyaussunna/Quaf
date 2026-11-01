<?php

namespace Tests\Feature;

use App\Models\CoinTransaction;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use App\Services\CoinRewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultJudgmentUpdateCoinClawbackAndAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Group $groupA;

    protected Group $groupB;

    protected Group $groupC;

    protected ProgramCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'name' => 'System Admin',
            'email' => 'admin@quaf.test',
        ]);

        $this->groupA = Group::firstOrCreate(
            ['code' => 'LF'],
            [
                'name' => 'Lumo Fikric',
                'slug' => 'lumo-fikric',
                'color_hex' => '#be1e2d',
                'display_order' => 1,
            ]
        );

        $this->groupB = Group::firstOrCreate(
            ['code' => 'PH'],
            [
                'name' => 'Pacto Hikmic',
                'slug' => 'pacto-hikmic',
                'color_hex' => '#10b981',
                'display_order' => 2,
            ]
        );

        $this->groupC = Group::firstOrCreate(
            ['code' => 'CM'],
            [
                'name' => 'Conco Majdic',
                'slug' => 'conco-majdic',
                'color_hex' => '#6366f1',
                'display_order' => 3,
            ]
        );

        $this->category = ProgramCategory::firstOrCreate(
            ['code' => 'GEN'],
            [
                'name' => 'General',
                'slug' => 'general-test',
            ]
        );
    }

    public function test_when_result_judgment_updated_coins_are_upgraded_clawed_back_and_reversed(): void
    {
        // 3 individual students
        $student1 = Student::create([
            'name' => 'Zayd Participant',
            'student_id' => 'QF301',
            'group_id' => $this->groupA->id,
            'category' => 'A Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_301',
            'coins_cache' => 0,
        ]);

        $student2 = Student::create([
            'name' => 'Bilal Participant',
            'student_id' => 'QF302',
            'group_id' => $this->groupB->id,
            'category' => 'A Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_302',
            'coins_cache' => 0,
        ]);

        $student3 = Student::create([
            'name' => 'Hamza Participant',
            'student_id' => 'QF303',
            'group_id' => $this->groupC->id,
            'category' => 'A Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_303',
            'coins_cache' => 0,
        ]);

        $student4 = Student::create([
            'name' => 'Tariq Participant',
            'student_id' => 'QF304',
            'group_id' => $this->groupA->id,
            'category' => 'A Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_304',
            'coins_cache' => 0,
        ]);

        $program = Program::create([
            'name' => 'Elocution English',
            'code' => 'PRG-ENG-01',
            'type' => 'individual',
            'category_id' => $this->category->id,
            'is_stage' => true,
        ]);

        $entry1 = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student1->id,
            'group_id' => $this->groupA->id,
            'chest_number' => 'QF301',
            'attendance_status' => 'present',
        ]);

        $entry2 = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student2->id,
            'group_id' => $this->groupB->id,
            'chest_number' => 'QF302',
            'attendance_status' => 'present',
        ]);

        $entry3 = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student3->id,
            'group_id' => $this->groupC->id,
            'chest_number' => 'QF303',
            'attendance_status' => 'present',
        ]);

        $entry4 = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student4->id,
            'group_id' => $this->groupA->id,
            'chest_number' => 'QF304',
            'attendance_status' => 'present',
        ]);

        // Phase 1: Initial published result
        // 1st Prize: Student 1 (A+: 300 coins)
        // 2nd Prize: Student 2 (A: 250 coins)
        // 3rd Prize: Student 3 (A: 220 coins)
        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entry1->id,
            'first_grade' => 'A+',
            'second_entry_id' => $entry2->id,
            'second_grade' => 'A',
            'third_entry_id' => $entry3->id,
            'third_grade' => 'A',
            'status' => 'published',
            'published_at' => now(),
            'result_number' => 1,
        ]);

        $coinService = app(CoinRewardService::class);
        $coinService->distributeCoinsForResult($result);

        $student1->refresh();
        $student2->refresh();
        $student3->refresh();

        $this->assertEquals(300, $student1->coins_cache);
        $this->assertEquals(250, $student2->coins_cache); // 2nd Prize initially
        $this->assertEquals(220, $student3->coins_cache); // 3rd Prize initially

        // Phase 2: JUDGMENT UPDATE!
        // Admin or judge reviews scores and corrects the result:
        // - Student 3 (previously 3rd) is PROMOTED to 2nd Prize (Grade A: 250 coins) -> should gain +30 coins!
        // - Student 2 (previously 2nd) is DEMOTED to 3rd Prize (Grade A: 220 coins) -> 30 coins CLAWED BACK!
        $updateResponse = $this->actingAs($this->adminUser)->put(route('admin.results.update', $result), [
            'first_entry_id' => $entry1->id,
            'first_grade' => 'A+',
            'second_entry_id' => $entry3->id, // Student 3 now 2nd!
            'second_grade' => 'A',
            'third_entry_id' => $entry2->id,  // Student 2 now 3rd!
            'third_grade' => 'A',
            'status' => 'published',
        ]);

        $updateResponse->assertRedirect(route('admin.results.index'));

        $student1->refresh();
        $student2->refresh();
        $student3->refresh();

        // Check: Student 3 promoted from 3rd (220) to 2nd (250) -> wallet is now 250!
        $this->assertEquals(250, $student3->coins_cache);

        // Check: Student 2 demoted from 2nd (250) to 3rd (220) -> 30 coins CLAWED BACK, wallet is now 220!
        $this->assertEquals(220, $student2->coins_cache);

        // Check: Student 1 unchanged at 300
        $this->assertEquals(300, $student1->coins_cache);

        // Verify transaction description and metadata
        $txStudent2 = CoinTransaction::where('student_id', $student2->id)
            ->where('program_id', $program->id)
            ->where('transaction_type', 'credit')
            ->first();

        $this->assertNotNull($txStudent2);
        $this->assertEquals(220, $txStudent2->coins);
        $this->assertEquals(3, $txStudent2->prize_position);
        $this->assertTrue($txStudent2->metadata['judgment_updated']);
        $this->assertEquals('demoted', $txStudent2->metadata['action']);
        $this->assertEquals(30, $txStudent2->metadata['clawback_amount']);
        $this->assertStringContainsString('clawed back', $txStudent2->description);

        $txStudent3 = CoinTransaction::where('student_id', $student3->id)
            ->where('program_id', $program->id)
            ->where('transaction_type', 'credit')
            ->first();

        $this->assertNotNull($txStudent3);
        $this->assertEquals(250, $txStudent3->coins);
        $this->assertEquals(2, $txStudent3->prize_position);
        $this->assertTrue($txStudent3->metadata['judgment_updated']);
        $this->assertEquals('promoted', $txStudent3->metadata['action']);
        $this->assertEquals(30, $txStudent3->metadata['coins_added']);

        // Phase 3: UNPLACED PARTICIPANT!
        // Suppose another update occurs where Student 4 takes 3rd place, and Student 2 is completely unplaced!
        $this->actingAs($this->adminUser)->put(route('admin.results.update', $result), [
            'first_entry_id' => $entry1->id,
            'first_grade' => 'A+',
            'second_entry_id' => $entry3->id,
            'second_grade' => 'A',
            'third_entry_id' => $entry4->id, // Student 4 now 3rd! Student 2 is UNPLACED!
            'third_grade' => 'A',
            'status' => 'published',
        ]);

        $student2->refresh();
        $student4->refresh();

        // Student 2 is completely unplaced now -> ALL coins for this program must be clawed back to 0!
        $this->assertEquals(0, $student2->coins_cache);
        // Student 4 gets 3rd place coins (220)
        $this->assertEquals(220, $student4->coins_cache);

        // Verify Student 2's transaction was converted to an auditable reversal
        $reversalTx = CoinTransaction::where('student_id', $student2->id)
            ->where('program_id', $program->id)
            ->where('transaction_type', 'reversal')
            ->first();

        $this->assertNotNull($reversalTx);
        $this->assertEquals('unplaced_clawback', $reversalTx->metadata['action']);
        $this->assertStringContainsString('clawed back', $reversalTx->description);
    }

    public function test_admin_coin_ledger_and_audit_hub_displays_metrics_and_student_breakdown(): void
    {
        $student = Student::create([
            'name' => 'Rashid Scholar',
            'student_id' => 'QF401',
            'group_id' => $this->groupA->id,
            'category' => 'A Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_401',
            'coins_cache' => 250,
        ]);

        $program = Program::create([
            'name' => 'Essay Malayalam',
            'code' => 'PRG-ES-01',
            'type' => 'individual',
            'category_id' => $this->category->id,
            'is_stage' => false,
        ]);

        CoinTransaction::create([
            'student_id' => $student->id,
            'group_id' => $this->groupA->id,
            'program_id' => $program->id,
            'prize_position' => 2,
            'grade' => 'A',
            'coins' => 250,
            'transaction_type' => 'credit',
            'transaction_reference' => 'COIN-AUDIT-TEST-01',
            'status' => 'completed',
            'description' => 'QUAF 09 Individual Reward: Essay Malayalam - 2nd Prize (+250 coins)',
            'metadata' => [
                'judgment_updated' => true,
                'action' => 'demoted',
                'clawback_amount' => 30,
            ],
        ]);

        // 1. Check index page loads
        $response = $this->actingAs($this->adminUser)->get(route('admin.coins.index'));
        $response->assertOk();
        $response->assertSee('Coin Ledger & Audit Hub');
        $response->assertSee('Total Awarded');
        $response->assertSee('Clawed Back / Returned');
        $response->assertSee('Active Wallet Balance');
        $response->assertSee('Rashid Scholar');
        $response->assertSee('Essay Malayalam');
        $response->assertSee('+250');
        $response->assertSee('Judgment Demoted (Clawback)');

        // 2. Check filter by student
        $filterResponse = $this->actingAs($this->adminUser)->get(route('admin.coins.index', ['student_id' => $student->id]));
        $filterResponse->assertOk();
        $filterResponse->assertSee('Rashid Scholar');
        $filterResponse->assertSee('Per-Competition Breakdown');
        $filterResponse->assertSee('Net Retained:');
        $filterResponse->assertSee('Clawed Back:');

        // 3. Check sync-all recalculation endpoint
        $syncResponse = $this->actingAs($this->adminUser)->post(route('admin.coins.sync-all'));
        $syncResponse->assertRedirect();
        $syncResponse->assertSessionHas('success');

        // 4. Check JSON breakdown endpoint
        $jsonResponse = $this->actingAs($this->adminUser)->getJson(route('admin.coins.student', $student));
        $jsonResponse->assertOk();
        $jsonResponse->assertJsonPath('student.name', 'Rashid Scholar');
        $jsonResponse->assertJsonPath('student.coins_cache', 250);
    }

    public function test_student_who_purchased_items_on_initial_result_shows_clawback_and_over_redeemed_in_presentation_and_portals(): void
    {
        $studentUser = User::factory()->create([
            'role' => 'student',
            'name' => 'Faris Winner',
            'email' => 'faris@student.quaf.org',
        ]);

        $student = Student::create([
            'name' => 'Faris Winner',
            'user_id' => $studentUser->id,
            'student_id' => 'QF777',
            'chest_number' => '777',
            'group_id' => $this->groupA->id,
            'category' => 'A Zone',
            'qr_token' => 'qr_faris_777',
            'coins_cache' => 0,
        ]);

        $program = Program::create([
            'name' => 'Classical Verses',
            'code' => 'PRG-CLS-01',
            'type' => 'individual',
            'category_id' => $this->category->id,
            'is_stage' => true,
        ]);

        $entry = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'group_id' => $this->groupA->id,
            'chest_number' => '777',
            'attendance_status' => 'present',
        ]);

        // 1. Initial Result: 1st Prize A+ (300 coins), published with poster
        $result = Result::create([
            'program_id' => $program->id,
            'first_entry_id' => $entry->id,
            'first_grade' => 'A+',
            'status' => 'published',
            'is_media_published' => true,
            'media_published_at' => now(),
            'published_at' => now(),
            'result_number' => 10,
        ]);

        app(CoinRewardService::class)->distributeCoinsForResult($result);

        $student->refresh();
        $this->assertEquals(300, $student->coins_cache);

        // 2. Student goes to Presentation Desk and redeems a premium book for 300 coins
        $presentationOperator = User::factory()->create([
            'role' => 'presentation_team',
            'name' => 'Desk Officer',
            'email' => 'officer@quaf.test',
        ]);

        $redeemRes = $this->actingAs($presentationOperator)->post(route('presentation.redeem'), [
            'student_id' => $student->id,
            'item_name' => 'Grand Encyclopedia',
            'coins' => 300,
            'quantity' => 1,
            'item_category' => 'book',
        ]);
        $redeemRes->assertRedirect();

        $student->refresh();
        $this->assertEquals(0, $student->coins_cache);

        // 3. Score update! Student is demoted to 2nd Prize with A+ (270 coins) -> 30 coins clawed back!
        // Now student earned 270, but has already spent 300 coins. Excess spent = 30 coins!
        $secondEntry = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => null,
            'group_id' => $this->groupB->id,
            'chest_number' => '888',
            'attendance_status' => 'present',
        ]);

        $this->actingAs($this->adminUser)->put(route('admin.results.update', $result), [
            'first_entry_id' => $secondEntry->id,
            'first_grade' => 'A+',
            'second_entry_id' => $entry->id, // Faris is now 2nd!
            'second_grade' => 'A+',
            'status' => 'published',
        ]);

        $student->refresh();

        // 4. Check Presentation Team Dashboard
        $deskRes = $this->actingAs($presentationOperator)->get(route('presentation.dashboard', ['student' => $student->student_id]));
        $deskRes->assertOk();
        $deskRes->assertSee('ഫലം തിരുത്തൽ / കോയിൻ ക്രമീകരണം');
        $deskRes->assertSee('ആദ്യ ഫലപ്രകാരം പർച്ചേസ് നടത്തിയിട്ടുണ്ട്');
        $deskRes->assertSee('അധികമായി കൈപ്പറ്റിയത് (Over-redeemed / Excess Spent):');
        $deskRes->assertSee('-30 Coins');

        // 5. Check Student Dashboard Portal
        $portalRes = $this->actingAs($studentUser)->get(route('student.dashboard'));
        $portalRes->assertOk();
        $portalRes->assertSee('ഫലം തിരുത്തൽ / കോയിൻ ക്രമീകരണം');
        $portalRes->assertSee('ആദ്യ ഫലപ്രകാരം സാധനങ്ങൾ കൈപ്പറ്റിയിട്ടുണ്ട്');
        $portalRes->assertSee('ആദ്യ ഫലപ്രകാരം അധികമായി കൈപ്പറ്റിയത് (Over-redeemed / Excess Spent):');
        $portalRes->assertSee('-30 Coins');

        // 6. Check Public QR Scan Verification Card
        $scanRes = $this->get(route('verify.student', $student->qr_token));
        $scanRes->assertOk();
        $scanRes->assertSee('ഫലം തിരുത്തൽ / കോയിൻ ക്രമീകരണം');
        $scanRes->assertSee('ആദ്യ ഫലപ്രകാരം സാധനങ്ങൾ കൈപ്പറ്റിയിട്ടുണ്ട്');
        $scanRes->assertSee('ആദ്യ ഫലപ്രകാരം അധികമായി കൈപ്പറ്റിയത് (Over-redeemed / Excess Spent):');
        $scanRes->assertSee('-30 Coins');

        // 7. Phase 4: Student is completely unplaced!
        $thirdEntry = ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => null,
            'group_id' => $this->groupC->id,
            'chest_number' => '999',
            'attendance_status' => 'present',
        ]);

        $this->actingAs($this->adminUser)->put(route('admin.results.update', $result), [
            'first_entry_id' => $secondEntry->id,
            'first_grade' => 'A+',
            'second_entry_id' => $thirdEntry->id,
            'second_grade' => 'A',
            'third_entry_id' => null, // Faris is now completely UNPLACED!
            'status' => 'published',
        ]);

        // Faris earned 0 coins, but spent 300 coins. Excess spent = 300 coins!
        $deskRes2 = $this->actingAs($presentationOperator)->get(route('presentation.dashboard', ['student' => $student->student_id]));
        $deskRes2->assertOk();
        $deskRes2->assertSee('ഫലം തിരുത്തൽ / കോയിൻ ക്രമീകരണം');
        $deskRes2->assertSee('ആദ്യ ഫലപ്രകാരം പർച്ചേസ് നടത്തിയിട്ടുണ്ട്');
        $deskRes2->assertSee('-300 Coins');
        $deskRes2->assertSee('സ്ഥാനം ഇല്ലാതാവുകയും');
    }
}
