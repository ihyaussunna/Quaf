<?php

namespace Tests\Feature;

use App\Models\CoinTransaction;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Stage;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use App\Services\CoinRewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresentationDeskCoinRedemptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $presentationUser;

    protected Group $group;

    protected Zone $zone;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        CoinTransaction::ensureSchema();

        $this->presentationUser = User::where('email', 'presentation@quaf.fest')->first() ?? User::create([
            'name' => 'Presentation Desk Officer',
            'email' => 'presentation@quaf.fest',
            'password' => bcrypt('Presentation#2026@Quaf!'),
            'role' => 'presentation_team',
            'is_active' => true,
        ]);

        $this->group = Group::firstOrCreate(
            ['code' => 'LUMO'],
            [
                'name' => 'Lumo Fikric',
                'slug' => 'lumo-fikric',
                'color_hex' => '#56286B',
            ]
        );

        $this->zone = Zone::first() ?? Zone::create([
            'name' => 'A Zone',
            'code' => 'A_ZONE',
            'slug' => 'a-zone-test',
        ]);

        $this->student = Student::create([
            'name' => 'Muhammad Bilal',
            'student_id' => 'LM101',
            'group_id' => $this->group->id,
            'zone_id' => $this->zone->id,
            'category' => 'A Zone',
            'qr_token' => 'qr-token-bilal-test-999',
            'coins_cache' => 0,
        ]);

        $stage = Stage::first() ?? Stage::create([
            'name' => 'Stage 01',
            'code' => 'ST01',
            'order' => 1,
        ]);

        $program = Program::first() ?? Program::create([
            'name' => 'Elocution English',
            'code' => 'ENG-ELO',
            'type' => 'individual',
            'stage_id' => $stage->id,
        ]);

        ProgramEntry::create([
            'program_id' => $program->id,
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'chest_number' => 'LM101',
            'status' => 'verified',
        ]);
    }

    public function test_presentation_user_can_login_via_web_login(): void
    {
        $response = $this->post(route('login'), [
            'username' => 'presentation@quaf.fest',
            'password' => 'Presentation#2026@Quaf!',
        ]);

        $response->assertRedirect(route('presentation.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('presentation_team', auth()->user()->role);
    }

    public function test_unauthenticated_user_cannot_access_presentation_dashboard(): void
    {
        $response = $this->get(route('presentation.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_presentation_team_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->presentationUser)->get(route('presentation.dashboard'));

        $response->assertOk();
        $response->assertSee('Presentation Desk');
        $response->assertSee('Scan Fest ID Card');
    }

    public function test_presentation_desk_can_search_student_by_chest_number_or_qr_url(): void
    {
        // 1. Direct Chest Number
        $response = $this->actingAs($this->presentationUser)->get(route('presentation.dashboard', [
            'student' => 'LM101',
        ]));
        $response->assertOk();
        $response->assertSee('Muhammad Bilal');
        $response->assertSee('Lumo Fikric');

        // 2. Scanned verification URL
        $scannedUrl = "https://quaf.ihyaussunna.in/verify/student/{$this->student->qr_token}";
        $urlResponse = $this->actingAs($this->presentationUser)->get(route('presentation.dashboard', [
            'student' => $scannedUrl,
        ]));
        $urlResponse->assertOk();
        $urlResponse->assertSee('Muhammad Bilal');
    }

    public function test_presentation_desk_search_student_ajax_api(): void
    {
        $response = $this->actingAs($this->presentationUser)->getJson(route('presentation.search-student', [
            'query' => 'LM101',
        ]));

        $response->assertOk();
        $response->assertJson([
            'found' => true,
            'student' => [
                'name' => 'Muhammad Bilal',
                'student_id' => 'LM101',
                'group_name' => 'Lumo Fikric',
            ],
        ]);
    }

    public function test_student_cannot_redeem_if_coins_are_insufficient(): void
    {
        // Student currently has 0 coins
        $response = $this->actingAs($this->presentationUser)->post(route('presentation.redeem'), [
            'student_id' => $this->student->id,
            'secret_code' => $this->student->purchase_pin,
            'item_name' => 'Islamic History Book',
            'coins' => 100,
            'quantity' => 1,
            'item_category' => 'book',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, $this->student->fresh()->coins_cache);
    }

    public function test_student_cannot_redeem_with_invalid_coins(): void
    {
        // Credit coins first
        CoinTransaction::create([
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'prize_position' => 1,
            'coins' => 100,
            'transaction_type' => 'credit',
            'transaction_reference' => 'COIN-TEST-CREDIT-WRONG-COINS',
            'status' => 'completed',
        ]);
        app(CoinRewardService::class)->syncStudentCoinsCache($this->student->id);

        $response = $this->actingAs($this->presentationUser)->post(route('presentation.redeem'), [
            'student_id' => $this->student->id,
            'item_name' => 'Book',
            'coins' => 0, // invalid zero coins
            'quantity' => 1,
            'item_category' => 'book',
        ]);

        $response->assertSessionHasErrors(['coins']);
        $this->assertEquals(100, $this->student->fresh()->coins_cache);
    }

    public function test_student_can_purchase_book_and_coins_are_deducted_accurately(): void
    {
        // 1. Credit 200 coins to student
        CoinTransaction::create([
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'prize_position' => 1,
            'grade' => 'A+',
            'reward_rule_id' => 'QUAF09_GROUP_PROGRAM_REWARD_RULE_V1',
            'coins' => 200,
            'transaction_type' => 'credit',
            'transaction_reference' => 'COIN-TEST-CREDIT-1',
            'status' => 'completed',
            'description' => '1st Prize Qawwali',
        ]);

        app(CoinRewardService::class)->syncStudentCoinsCache($this->student->id);
        $this->assertEquals(200, $this->student->fresh()->coins_cache);

        // 2. Student purchases a book for 70 coins with valid secret PIN (qty 1)
        $response = $this->actingAs($this->presentationUser)->post(route('presentation.redeem'), [
            'student_id' => $this->student->id,
            'secret_code' => $this->student->purchase_pin,
            'item_name' => 'Tuhfatul Mujahideen (Book)',
            'coins' => 70,
            'quantity' => 1,
            'item_category' => 'book',
            'notes' => 'Bill #402',
        ]);

        $response->assertRedirect(route('presentation.dashboard', ['student' => 'LM101']));
        $response->assertSessionHas('success');

        // 3. Verify balance is now 130
        $this->assertEquals(130, $this->student->fresh()->coins_cache);

        // 4. Verify ledger has debit entry
        $this->assertDatabaseHas('coin_transactions', [
            'student_id' => $this->student->id,
            'transaction_type' => 'debit',
            'coins' => -70,
        ]);
    }

    public function test_reversing_redemption_restores_student_coins(): void
    {
        // 1. Credit 200 coins
        CoinTransaction::create([
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'prize_position' => 1,
            'coins' => 200,
            'transaction_type' => 'credit',
            'transaction_reference' => 'COIN-TEST-CREDIT-2',
            'status' => 'completed',
        ]);
        app(CoinRewardService::class)->syncStudentCoinsCache($this->student->id);

        // 2. Redeem 50 coins
        $tx = app(CoinRewardService::class)->redeemCoins($this->student, [
            'item_name' => 'Diary',
            'coins' => 50,
            'quantity' => 1,
        ]);
        $this->assertEquals(150, $this->student->fresh()->coins_cache);

        // 3. Reverse the purchase
        $response = $this->actingAs($this->presentationUser)->post(route('presentation.reverse', $tx->id), [
            'reason' => 'Customer returned item',
        ]);

        $response->assertSessionHas('success');

        // 4. Verify balance is restored back to 200
        $this->assertEquals(200, $this->student->fresh()->coins_cache);
    }

    public function test_printable_receipt_and_report_views(): void
    {
        // Credit and purchase
        CoinTransaction::create([
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'prize_position' => 1,
            'coins' => 100,
            'transaction_type' => 'credit',
            'transaction_reference' => 'COIN-TEST-CREDIT-3',
            'status' => 'completed',
        ]);
        app(CoinRewardService::class)->syncStudentCoinsCache($this->student->id);

        $tx = app(CoinRewardService::class)->redeemCoins($this->student, [
            'item_name' => 'Fest Souvenir',
            'coins' => 100,
            'quantity' => 1,
        ]);

        // Printable receipt
        $receiptResponse = $this->actingAs($this->presentationUser)->get(route('presentation.receipt', $tx->id));
        $receiptResponse->assertOk();
        $receiptResponse->assertSee('COIN REDEMPTION SLIP');
        $receiptResponse->assertSee('Fest Souvenir');
        $receiptResponse->assertSee('Muhammad Bilal');

        // Report
        $reportResponse = $this->actingAs($this->presentationUser)->get(route('presentation.report'));
        $reportResponse->assertOk();
        $reportResponse->assertSee('Coin Redemption');
        $reportResponse->assertSee('Gift Distribution Report');
        $reportResponse->assertSee('Fest Souvenir');
    }

    public function test_presentation_desk_can_toggle_recognition_gift(): void
    {
        $this->assertFalse((bool) $this->student->fresh()->recognition_gift_delivered);

        // 1. Mark as delivered
        $response = $this->actingAs($this->presentationUser)->post(route('presentation.toggle-gift', $this->student->id));
        $response->assertSessionHas('success');
        $this->assertTrue((bool) $this->student->fresh()->recognition_gift_delivered);
        $this->assertNotNull($this->student->fresh()->recognition_gift_delivered_at);

        // 2. Toggle back to pending
        $response2 = $this->actingAs($this->presentationUser)->post(route('presentation.toggle-gift', $this->student->id));
        $response2->assertSessionHas('success');
        $this->assertFalse((bool) $this->student->fresh()->recognition_gift_delivered);
    }

    public function test_non_participating_student_cannot_receive_recognition_gift(): void
    {
        $nonParticipant = Student::create([
            'name' => 'Campus Visitor',
            'student_id' => 'LM999',
            'group_id' => $this->group->id,
            'zone_id' => $this->zone->id,
            'category' => 'A Zone',
            'qr_token' => 'qr-token-visitor-999',
            'coins_cache' => 0,
        ]);

        $this->assertFalse($nonParticipant->isCompetitionParticipant());

        $response = $this->actingAs($this->presentationUser)->post(route('presentation.toggle-gift', $nonParticipant->id));
        $response->assertSessionHas('error');
        $this->assertFalse((bool) $nonParticipant->fresh()->recognition_gift_delivered);
    }

    public function test_scanned_student_verification_page_reveals_secret_code_and_wallet_data(): void
    {
        // Credit 150 coins
        CoinTransaction::create([
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'prize_position' => 1,
            'coins' => 150,
            'transaction_type' => 'credit',
            'transaction_reference' => 'COIN-TEST-CREDIT-VERIFY',
            'status' => 'completed',
            'description' => '1st Prize Qawwali',
        ]);
        app(CoinRewardService::class)->syncStudentCoinsCache($this->student->id);

        $response = $this->get(route('verify.student', $this->student->qr_token));
        $response->assertOk();
        $response->assertSee('Muhammad Bilal');
        $response->assertSee('1st Prize Qawwali');
        $response->assertSee('1st Prize');
        $response->assertSee('150');
        $response->assertSee('QUAF Recognition Gift');
        $response->assertDontSee('Grade Achievements');
        $response->assertDontSee('VERIFIED ENTRY');
    }

    public function test_student_portal_via_chest_number_login_masks_secret_code(): void
    {
        $response = $this->post(route('student.login.submit'), [
            'identifier' => 'LM101',
        ]);
        $response->assertRedirect(route('student.dashboard'));

        $dashResponse = $this->get(route('student.dashboard'));
        $dashResponse->assertOk();
        $dashResponse->assertSee('Muhammad Bilal');
        $dashResponse->assertDontSee('Secret Purchase PIN');
        $dashResponse->assertDontSee($this->student->purchase_pin);
    }

    public function test_presentation_desk_shows_student_grade_counts_and_prize_achievements(): void
    {
        CoinTransaction::create([
            'student_id' => $this->student->id,
            'group_id' => $this->student->group_id,
            'prize_position' => 1,
            'grade' => 'A+',
            'coins' => 200,
            'transaction_type' => 'credit',
            'transaction_reference' => 'COIN-TEST-ACHIEVE-01',
            'status' => 'completed',
            'description' => 'First Prize Qawwali - Grade A+',
        ]);

        $response = $this->actingAs($this->presentationUser)->get(route('presentation.dashboard', [
            'student' => $this->student->student_id,
        ]));

        $response->assertOk();
        $response->assertSee('Grade Achievements');
        $response->assertSee('Excellence');
        $response->assertSee('Proficient');
        $response->assertSee('Pinnacle');
        $response->assertSee('First Prize');
        $response->assertSee('Transaction History');

        $ajaxResponse = $this->actingAs($this->presentationUser)->getJson(route('presentation.search-student', [
            'query' => $this->student->student_id,
        ]));

        $ajaxResponse->assertOk();
        $ajaxResponse->assertJsonPath('found', true);
        $ajaxResponse->assertJsonPath('student.grade_counts.A+', 1);
        $ajaxResponse->assertJsonPath('student.prizes.1.count', 1);
        $ajaxResponse->assertJsonPath('student.prizes.1.rank', 'Pinnacle');
    }

    public function test_cashier_user_can_login_with_username_and_password_and_redirects_to_cashier_console(): void
    {
        $response = $this->post(route('login'), [
            'username' => 'cashier',
            'password' => 'Cashier#2026@Quaf!',
        ]);

        $response->assertRedirect(route('presentation.cashier'));
        $this->assertAuthenticated();
        $this->assertEquals('presentation_cashier', auth()->user()->role);
    }

    public function test_cashier_can_add_coins_for_cash_in_hand_and_it_shows_as_added_coins(): void
    {
        $cashierUser = User::where('role', 'presentation_cashier')->first() ?? User::create([
            'name' => 'Cashier Operator',
            'email' => 'cashier@quaf.fest',
            'password' => bcrypt('Cashier#2026@Quaf!'),
            'role' => 'presentation_cashier',
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashierUser)->post(route('presentation.cashier.add-coins'), [
            'student_id' => $this->student->id,
            'cash_amount' => 150,
            'coins' => 150,
            'payment_mode' => 'cash',
            'notes' => 'Received cash at presentation counter',
        ]);

        $response->assertRedirect(route('presentation.cashier', ['student' => $this->student->student_id]));
        $response->assertSessionHas('success');

        // Verify transaction in database
        $this->assertDatabaseHas('coin_transactions', [
            'student_id' => $this->student->id,
            'coins' => 150,
            'cash_amount' => 150.00,
            'source' => 'cash_deposit',
            'transaction_type' => 'credit',
            'reward_rule_id' => 'QUAF09_CASHIER_COIN_DEPOSIT_V1',
        ]);

        // Check student coin summary
        $service = app(CoinRewardService::class);
        $summary = $service->getStudentCoinSummary($this->student);

        $this->assertEquals(150, $summary['balance']);
        $this->assertEquals(150, $summary['added_coins']);
        $this->assertEquals(0, $summary['competition_earned']);
        $this->assertEquals(150, $summary['total_earned']);

        // Check cashier page displays "Added Coins"
        $cashierView = $this->actingAs($cashierUser)->get(route('presentation.cashier', ['student' => $this->student->student_id]));
        $cashierView->assertOk();
        $cashierView->assertSee('പണം നൽകി ചേർത്തത്');
        $cashierView->assertSee('ADDED COIN');
        $cashierView->assertSee('+150');
    }

    public function test_cart_redemption_deducts_total_coins_for_multiple_items(): void
    {
        // First topup student wallet with 200 coins
        CoinTransaction::create([
            'student_id' => $this->student->id,
            'group_id' => $this->student->group_id,
            'prize_position' => 0,
            'coins' => 200,
            'transaction_type' => 'credit',
            'source' => 'cash_deposit',
            'reward_rule_id' => 'QUAF09_CASHIER_COIN_DEPOSIT_V1',
            'transaction_reference' => 'COIN-TEST-TOPUP-01',
            'status' => 'completed',
            'description' => 'Topup: ₹200',
        ]);

        $cart = [
            ['item_name' => 'Book Premium', 'coins' => 100, 'quantity' => 1, 'category' => 'book'],
            ['item_name' => 'Centenary Pen', 'coins' => 40, 'quantity' => 2, 'category' => 'stationery'],
        ]; // Total: 100*1 + 40*2 = 180 coins

        $response = $this->actingAs($this->presentationUser)->post(route('presentation.redeem-cart'), [
            'student_id' => $this->student->id,
            'cart_data' => json_encode($cart),
            'notes' => 'Cart gift delivery',
        ]);

        $response->assertRedirect(route('presentation.dashboard', ['student' => $this->student->student_id]));
        $response->assertSessionHas('success');

        // Check debit transaction recorded
        $this->assertDatabaseHas('coin_transactions', [
            'student_id' => $this->student->id,
            'coins' => -180,
            'transaction_type' => 'debit',
            'reward_rule_id' => 'QUAF09_PRESENTATION_CART_PURCHASE_V1',
        ]);

        $service = app(CoinRewardService::class);
        $summary = $service->getStudentCoinSummary($this->student);
        $this->assertEquals(20, $summary['balance']); // 200 - 180 = 20
        $this->assertEquals(180, $summary['total_spent']);
    }

    public function test_cart_redemption_fails_when_coins_are_insufficient(): void
    {
        // Student has only 50 coins
        CoinTransaction::create([
            'student_id' => $this->student->id,
            'group_id' => $this->student->group_id,
            'prize_position' => 0,
            'coins' => 50,
            'transaction_type' => 'credit',
            'reward_rule_id' => 'QUAF09_GROUP_PROGRAM_REWARD_RULE_V1',
            'transaction_reference' => 'COIN-TEST-COMP-50',
            'status' => 'completed',
            'description' => 'Competition 50 coins',
        ]);

        $cart = [
            ['item_name' => 'Book Premium', 'coins' => 100, 'quantity' => 1, 'category' => 'book'],
        ];

        $response = $this->actingAs($this->presentationUser)->post(route('presentation.redeem-cart'), [
            'student_id' => $this->student->id,
            'cart_data' => json_encode($cart),
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('coin_transactions', [
            'student_id' => $this->student->id,
            'coins' => -100,
        ]);
    }

    public function test_cashier_receipt_can_be_viewed(): void
    {
        $deposit = CoinTransaction::create([
            'student_id' => $this->student->id,
            'group_id' => $this->student->group_id,
            'prize_position' => 0,
            'coins' => 100,
            'cash_amount' => 100.00,
            'payment_mode' => 'cash',
            'source' => 'cash_deposit',
            'transaction_type' => 'credit',
            'reward_rule_id' => 'QUAF09_CASHIER_COIN_DEPOSIT_V1',
            'transaction_reference' => 'COIN-CASH-TEST-999',
            'status' => 'completed',
            'description' => 'Cash Deposit ₹100 (+100 Coins)',
            'metadata' => [
                'cash_amount' => 100.00,
                'coins_added' => 100,
                'balance_before' => 0,
                'balance_after' => 100,
            ],
        ]);

        $response = $this->actingAs($this->presentationUser)->get(route('presentation.cashier.receipt', $deposit->id));
        $response->assertOk();
        $response->assertSee('QUAF FESTIVAL 09');
        $response->assertSee('ADDED COINS RECEIPT');
        $response->assertSee('COIN-CASH-TEST-999');
        $response->assertSee('100.00');
    }
}
