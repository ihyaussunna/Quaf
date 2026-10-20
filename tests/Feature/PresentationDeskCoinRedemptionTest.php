<?php

namespace Tests\Feature;

use App\Models\CoinTransaction;
use App\Models\Group;
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

    public function test_student_cannot_redeem_with_invalid_secret_code(): void
    {
        // Credit coins first
        CoinTransaction::create([
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'prize_position' => 1,
            'coins' => 100,
            'transaction_type' => 'credit',
            'transaction_reference' => 'COIN-TEST-CREDIT-WRONG-PIN',
            'status' => 'completed',
        ]);
        app(CoinRewardService::class)->syncStudentCoinsCache($this->student->id);

        $response = $this->actingAs($this->presentationUser)->post(route('presentation.redeem'), [
            'student_id' => $this->student->id,
            'secret_code' => '0000', // incorrect PIN
            'item_name' => 'Book',
            'coins' => 50,
            'quantity' => 1,
            'item_category' => 'book',
        ]);

        $response->assertSessionHas('error');
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
        $response->assertSee('Secret Purchase Code');
        $response->assertSee((string) $this->student->purchase_pin);
        $response->assertSee('150');
        $response->assertSee('QUAF Recognition Gift');
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
        $dashResponse->assertSee('Scan QR to View');
        $dashResponse->assertDontSee($this->student->purchase_pin);
    }
}
