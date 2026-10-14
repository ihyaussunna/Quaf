<?php

namespace Tests\Feature;

use App\Models\FundReceipt;
use App\Models\FundReceiptSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $fundCollector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@quaf.fest'],
            [
                'name' => 'QUAF Central Admin',
                'password' => bcrypt('CentralAdmin#2026@Quaf!'),
                'plain_password' => 'CentralAdmin#2026@Quaf!',
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->fundCollector = User::firstOrCreate(
            ['email' => 'fund@quaf.fest'],
            [
                'name' => 'QUAF Fund Collection Desk',
                'password' => bcrypt('Fund#2026@Quaf!'),
                'plain_password' => 'Fund#2026@Quaf!',
                'role' => 'fund_collector',
                'is_active' => true,
            ]
        );

        FundReceiptSetting::current();
    }

    public function test_fund_collector_can_login_with_alias_and_password(): void
    {
        $response = $this->post('/login', [
            'username' => 'fund',
            'password' => 'Fund#2026@Quaf!',
        ]);

        $response->assertRedirect(route('fund.dashboard'));
        $this->assertAuthenticatedAs($this->fundCollector);
    }

    public function test_fund_collector_can_access_fund_dashboard(): void
    {
        FundReceipt::create([
            'receipt_number' => 'Q9-RCP-'.date('Y').'-0001',
            'donor_name' => 'Ahmed Kabeer',
            'donor_phone' => '9847000001',
            'donor_place' => 'Calicut',
            'amount' => 5000.00,
            'amount_in_words' => 'Five Thousand Rupees Only',
            'payment_method' => 'cash',
            'collector_id' => $this->fundCollector->id,
            'collector_name' => $this->fundCollector->name,
        ]);

        $response = $this->actingAs($this->fundCollector)->get(route('fund.dashboard'));

        $response->assertOk();
        $response->assertSee('Fund Collection Desk');
        $response->assertSee('Ahmed Kabeer');
        $response->assertSee('5,000.00');
        $response->assertSee('Q9-RCP-');
    }

    public function test_fund_collector_can_issue_receipt_with_auto_sequence_and_words(): void
    {
        $response = $this->actingAs($this->fundCollector)->post(route('fund.receipts.store'), [
            'donor_name' => 'Sayyid Munavvar Ali',
            'donor_phone' => '9847123456',
            'donor_place' => 'Malappuram',
            'amount' => 2500,
            'payment_method' => 'upi',
            'transaction_reference' => 'UPI-994829104',
            'category' => 'Mahabba Token',
            'notes' => 'Token of Halawa receipt',
        ]);

        $receipt = FundReceipt::where('donor_name', 'Sayyid Munavvar Ali')->first();
        $this->assertNotNull($receipt);
        $this->assertEquals(2500, (float) $receipt->amount);
        $this->assertStringContainsString('Two Thousand Five Hundred Rupees Only', $receipt->amount_in_words);
        $this->assertStringStartsWith('Q9-RCP-', $receipt->receipt_number);

        $response->assertRedirect(route('fund.receipts.show', $receipt));

        // Test view of receipt voucher
        $viewResponse = $this->actingAs($this->fundCollector)->get(route('fund.receipts.show', $receipt));
        $viewResponse->assertOk();
        $viewResponse->assertSee($receipt->receipt_number);
        $viewResponse->assertSee('Sayyid Munavvar Ali');
        $viewResponse->assertSee('2,500.00');
        $viewResponse->assertSee('Two Thousand Five Hundred Rupees Only');
    }

    public function test_fund_collector_cannot_access_admin_panel(): void
    {
        // Must be prevented from accessing admin panel
        $response = $this->actingAs($this->fundCollector)->get(route('admin.dashboard'));

        // RoleMiddleware logs out non-admins trying to access admin and redirects to login with alert
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_fund_collector_cannot_delete_receipts(): void
    {
        $receipt = FundReceipt::create([
            'receipt_number' => 'Q9-RCP-'.date('Y').'-0002',
            'donor_name' => 'Ibrahim Kunhi',
            'amount' => 1000.00,
            'amount_in_words' => 'One Thousand Rupees Only',
            'payment_method' => 'cash',
            'collector_id' => $this->fundCollector->id,
        ]);

        // Attempting to delete via admin route as fund collector
        $response = $this->actingAs($this->fundCollector)->delete(route('admin.fund.receipts.destroy', $receipt));

        // Should be blocked and redirected to login
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('fund_receipts', [
            'id' => $receipt->id,
            'is_cancelled' => false,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_can_view_fund_ledger_and_stats(): void
    {
        FundReceipt::create([
            'receipt_number' => 'Q9-RCP-'.date('Y').'-0003',
            'donor_name' => 'Musthafa Moulavi',
            'amount' => 15000.00,
            'amount_in_words' => 'Fifteen Thousand Rupees Only',
            'payment_method' => 'bank_transfer',
            'collector_id' => $this->fundCollector->id,
            'collector_name' => $this->fundCollector->name,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.fund.index'));

        $response->assertOk();
        $response->assertSee('Receipts Ledger');
        $response->assertSee('Musthafa Moulavi');
        $response->assertSee('15,000.00');
    }

    public function test_admin_can_delete_and_cancel_receipt(): void
    {
        $receipt = FundReceipt::create([
            'receipt_number' => 'Q9-RCP-'.date('Y').'-0004',
            'donor_name' => 'Test Donor Cancel',
            'amount' => 3000.00,
            'amount_in_words' => 'Three Thousand Rupees Only',
            'payment_method' => 'cash',
            'collector_id' => $this->fundCollector->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.fund.receipts.destroy', $receipt), [
            'reason' => 'Donor requested cancellation',
        ]);

        $response->assertRedirect();
        $this->assertSoftDeleted('fund_receipts', ['id' => $receipt->id]);

        $receipt->refresh();
        $this->assertTrue($receipt->is_cancelled);
        $this->assertEquals('Donor requested cancellation', $receipt->cancelled_reason);
        $this->assertEquals($this->admin->id, $receipt->cancelled_by);
    }

    public function test_admin_can_update_receipt_customizer_settings(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.fund.settings.update'), [
            'header_title' => 'QUAF 09 FESTIVAL DONATIONS',
            'organization_name' => 'IHYA-US-SUNNA FOUNDATION',
            'subtitle' => 'Official Contribution Receipt Voucher',
            'text_alignment' => 'left',
            'font_size' => 'large',
            'padding_size' => 'relaxed',
            'primary_color' => '#059669',
            'accent_color' => '#0284c7',
            'text_color' => '#111827',
            'bg_color' => '#ffffff',
            'show_logo' => '1',
            'footer_text' => 'May Allah bless your noble contributions.',
            'authorized_by' => 'Chief Accountant & Finance Desk',
        ]);

        $response->assertRedirect(route('admin.fund.settings'));

        $settings = FundReceiptSetting::current();
        $this->assertEquals('QUAF 09 FESTIVAL DONATIONS', $settings->header_title);
        $this->assertEquals('left', $settings->text_alignment);
        $this->assertEquals('large', $settings->font_size);
        $this->assertEquals('#059669', $settings->primary_color);
        $this->assertTrue($settings->show_logo);
    }

    public function test_panel_access_lists_fund_collector_account(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.panel-access.index', ['panel' => 'fund']));

        $response->assertOk();
        $response->assertSee('QUAF Fund Collection Desk');
        $response->assertSee('fund@quaf.fest');
        $response->assertSee('Fund Collection Desk');
    }

    public function test_fund_collector_can_stream_and_download_receipt_image(): void
    {
        $receipt = FundReceipt::create([
            'receipt_number' => 'Q9-RCP-'.date('Y').'-0005',
            'donor_name' => 'Vaheed Zaman',
            'amount' => 2000.00,
            'amount_in_words' => 'Two Thousand Rupees Only',
            'payment_method' => 'upi',
            'collector_id' => $this->fundCollector->id,
            'collector_name' => $this->fundCollector->name,
        ]);

        // Inline image stream
        $imageResponse = $this->actingAs($this->fundCollector)->get(route('fund.receipts.image', $receipt));
        $imageResponse->assertOk();
        $this->assertEquals('image/jpeg', $imageResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', $imageResponse->headers->get('Content-Disposition'));

        // Download attachment
        $downloadResponse = $this->actingAs($this->fundCollector)->get(route('fund.receipts.download', $receipt));
        $downloadResponse->assertOk();
        $this->assertEquals('image/jpeg', $downloadResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $downloadResponse->headers->get('Content-Disposition'));
    }

    public function test_admin_can_stream_and_download_receipt_image(): void
    {
        $receipt = FundReceipt::create([
            'receipt_number' => 'Q9-RCP-'.date('Y').'-0006',
            'donor_name' => 'Vaheed Zaman Admin',
            'amount' => 5000.00,
            'amount_in_words' => 'Five Thousand Rupees Only',
            'payment_method' => 'cash',
            'collector_id' => $this->admin->id,
            'collector_name' => $this->admin->name,
        ]);

        $imageResponse = $this->actingAs($this->admin)->get(route('admin.fund.receipts.image', $receipt));
        $imageResponse->assertOk();
        $this->assertEquals('image/jpeg', $imageResponse->headers->get('Content-Type'));

        $downloadResponse = $this->actingAs($this->admin)->get(route('admin.fund.receipts.download', $receipt));
        $downloadResponse->assertOk();
        $this->assertEquals('image/jpeg', $downloadResponse->headers->get('Content-Type'));
    }
}
