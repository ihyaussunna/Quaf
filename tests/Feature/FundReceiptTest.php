<?php

namespace Tests\Feature;

use App\Http\Controllers\Fund\FundController;
use App\Models\FundReceipt;
use App\Models\FundReceiptSetting;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use App\Services\ReceiptImageGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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
        $this->assertStringStartsWith('QFR-', $receipt->receipt_number);

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

    public function test_admin_can_update_receipt_poster_customizer_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.fund.settings.update'), [
            'header_title' => 'QUAF SEASON 09',
            'organization_name' => 'JAMIA IHYA-US-SUNNA',
            'subtitle' => 'Grand Festival Fund Collection Voucher',
            'text_alignment' => 'center',
            'font_size' => 'normal',
            'padding_size' => 'normal',
            'primary_color' => '#be1e2d',
            'accent_color' => '#d97706',
            'text_color' => '#0f172a',
            'bg_color' => '#ffffff',
            'authorized_by' => 'QUAF Finance Samithi',
            'rcp_x' => 905,
            'rcp_y' => 180,
            'rcp_font_size' => 24,
            'rcp_color' => '#444444',
            'rcp_align' => 'right',
            'date_x' => 840,
            'date_y' => 235,
            'date_font_size' => 22,
            'date_color' => '#333333',
            'date_align' => 'right',
            'donor_x' => 570,
            'donor_y' => 615,
            'donor_font_size' => 44,
            'donor_color' => '#ffff00',
            'donor_align' => 'center',
            'amount_x' => 565,
            'amount_y' => 760,
            'amount_font_size' => 42,
            'amount_color' => '#111111',
            'amount_align' => 'center',
        ]);

        $response->assertRedirect(route('admin.fund.settings'));

        $settings = FundReceiptSetting::current();
        $this->assertEquals(905, $settings->rcp_x);
        $this->assertEquals(180, $settings->rcp_y);
        $this->assertEquals(24, $settings->rcp_font_size);
        $this->assertEquals('#444444', $settings->rcp_color);
        $this->assertEquals('right', $settings->rcp_align);

        $this->assertEquals(840, $settings->date_x);
        $this->assertEquals(235, $settings->date_y);
        $this->assertEquals(22, $settings->date_font_size);
        $this->assertEquals('#333333', $settings->date_color);
        $this->assertEquals('right', $settings->date_align);

        $this->assertEquals(570, $settings->donor_x);
        $this->assertEquals(615, $settings->donor_y);
        $this->assertEquals(44, $settings->donor_font_size);
        $this->assertEquals('#ffff00', $settings->donor_color);
        $this->assertEquals('center', $settings->donor_align);

        $this->assertEquals(565, $settings->amount_x);
        $this->assertEquals(760, $settings->amount_y);
        $this->assertEquals(42, $settings->amount_font_size);
        $this->assertEquals('#111111', $settings->amount_color);
        $this->assertEquals('center', $settings->amount_align);
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

    public function test_public_fund_home_displays_top_students_and_groups(): void
    {
        $group = Group::create([
            'name' => 'Subulul Huda',
            'slug' => 'subulul-huda',
            'code' => 'SH',
            'color_hex' => '#009444',
        ]);

        $student = Student::create([
            'name' => 'Muhammad Rashid',
            'student_id' => '101',
            'qr_token' => 'token_101',
            'class_level' => 'Thanawiyya',
            'group_id' => $group->id,
        ]);

        FundReceipt::create([
            'receipt_number' => 'QFR-26-01',
            'donor_name' => 'Haji Usman',
            'amount' => 10000.00,
            'amount_in_words' => 'Ten Thousand Rupees Only',
            'payment_method' => 'cash',
            'student_id' => $student->id,
            'group_id' => $group->id,
            'collector_name' => $student->name,
        ]);

        $response = $this->get(route('fund.home'));

        $response->assertOk();
        $response->assertSee('Festival Fund Portal');
        $response->assertSee('Muhammad Rashid');
        $response->assertSee('Subulul Huda');
        $response->assertSee('10,000');
    }

    public function test_api_search_students_returns_filtered_results(): void
    {
        $group = Group::create([
            'name' => 'Al Hilal',
            'slug' => 'al-hilal',
            'code' => 'AH',
            'color_hex' => '#005c94',
        ]);

        $student = Student::create([
            'name' => 'Zubair Ahmad',
            'student_id' => '202',
            'qr_token' => 'token_202',
            'class_level' => 'Aliya',
            'group_id' => $group->id,
        ]);

        $response = $this->getJson(route('fund.api.search-students', [
            'class_level' => 'Aliya',
            'query' => 'Zubair',
        ]));

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => 'Zubair Ahmad',
            'chest_number' => '202',
            'class_level' => 'Aliya',
            'group_code' => 'AH',
        ]);
    }

    public function test_student_can_login_and_issue_receipt_from_desk(): void
    {
        $group = Group::create([
            'name' => 'Al Furqan',
            'slug' => 'al-furqan',
            'code' => 'AF',
            'color_hex' => '#be1e2d',
        ]);

        $student = Student::create([
            'name' => 'Abdullah Nizar',
            'student_id' => '303',
            'qr_token' => 'token_303',
            'class_level' => 'Thanawiyya',
            'group_id' => $group->id,
        ]);

        // 1. Login
        $loginResponse = $this->post(route('fund.student.login'), [
            'student_id' => $student->id,
        ]);

        $loginResponse->assertRedirect(route('fund.student.desk'));
        $this->assertEquals($student->id, session('fund_student_id'));

        // 2. View Desk with active student session
        $deskResponse = $this->withSession(['fund_student_id' => $student->id])->get(route('fund.student.desk'));
        $deskResponse->assertOk();
        $deskResponse->assertSee('Abdullah Nizar');
        $deskResponse->assertSee('303');
        $deskResponse->assertSee('Al Furqan');

        // 3. Issue Receipt with active student session
        $storeResponse = $this->withSession(['fund_student_id' => $student->id])->post(route('fund.student.receipts.store'), [
            'donor_name' => 'Hamza Musliyar',
            'donor_phone' => '9847112233',
            'donor_place' => 'Perinthalmanna',
            'amount' => 7500,
            'payment_method' => 'cash',
            'category' => 'Festival Fund',
            'notes' => 'Donation via student desk',
        ]);

        $storeResponse->assertSessionHasNoErrors();
        $storeResponse->assertRedirect();

        $receipt = FundReceipt::where('donor_name', 'Hamza Musliyar')->first();
        $this->assertNotNull($receipt);
        $this->assertEquals($student->id, $receipt->student_id);
        $this->assertEquals($group->id, $receipt->group_id);
        $this->assertEquals(7500, (float) $receipt->amount);
        $this->assertStringStartsWith('QFR-26-', $receipt->receipt_number);

        $storeResponse->assertRedirect(route('fund.receipts.show', $receipt));

        // 4. View Receipt Voucher - verify no admin links
        $receiptView = $this->withSession(['fund_student_id' => $student->id])->get(route('fund.receipts.show', $receipt));
        $receiptView->assertOk();
        $receiptView->assertDontSee('← Ledger');
        $receiptView->assertDontSee('Customizer');

        // 5. Logout
        $logoutResponse = $this->withSession(['fund_student_id' => $student->id])->get(route('fund.student.logout'));
        $logoutResponse->assertRedirect(route('fund.home'));
        $this->assertNull(session('fund_student_id'));
    }

    public function test_office_student_account_can_be_selected_and_has_no_group(): void
    {
        $response = $this->getJson(route('fund.api.search-students', ['class_level' => 'OFFICE']));

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => 'OFFICE',
            'chest_number' => 'OFFICE',
            'class_level' => 'OFFICE',
            'group_name' => '',
            'group_code' => '',
        ]);

        $officeStudent = Student::where('student_id', 'OFFICE')->first();
        $this->assertNotNull($officeStudent);
        $this->assertNull($officeStudent->group_id);

        // Desk login as office
        $loginResponse = $this->post(route('fund.student.login'), [
            'student_id' => $officeStudent->id,
        ]);
        $loginResponse->assertRedirect(route('fund.student.desk'));

        // View desk
        $deskResponse = $this->withSession(['fund_student_id' => $officeStudent->id])->get(route('fund.student.desk'));
        $deskResponse->assertOk();
        $deskResponse->assertSee('OFFICE');

        // Home page contains OFFICE in classes dropdown
        $homeResponse = $this->get(route('fund.home'));
        $homeResponse->assertOk();
        $homeResponse->assertSee('<option value="OFFICE">OFFICE</option>', false);
    }

    public function test_dual_font_receipt_rendering_for_malayalam_and_english(): void
    {
        $malayalamReceipt = FundReceipt::create([
            'receipt_number' => 'QFR-26-991',
            'donor_name' => 'മുഹമ്മദ് ഷാഫി',
            'amount' => 1000,
            'payment_method' => 'cash',
        ]);

        $englishReceipt = FundReceipt::create([
            'receipt_number' => 'QFR-26-992',
            'donor_name' => 'Mohammed Shafi',
            'amount' => 2000,
            'payment_method' => 'upi',
        ]);

        $malayalamImage = ReceiptImageGenerator::generate($malayalamReceipt);
        $this->assertInstanceOf(\GdImage::class, $malayalamImage);

        $englishImage = ReceiptImageGenerator::generate($englishReceipt);
        $this->assertInstanceOf(\GdImage::class, $englishImage);

        $malayalamStream = $this->get(route('fund.receipts.image', $malayalamReceipt));
        $malayalamStream->assertOk();
        $malayalamStream->assertHeader('Content-Type', 'image/jpeg');

        $englishStream = $this->get(route('fund.receipts.image', $englishReceipt));
        $englishStream->assertOk();
        $englishStream->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_student_can_update_their_own_receipt_donor_name_and_payment_method(): void
    {
        $group = Group::create([
            'name' => 'Al Furqan',
            'slug' => 'al-furqan-2',
            'code' => 'AF2',
            'color_hex' => '#be1e2d',
        ]);

        $student = Student::create([
            'name' => 'Faris Ameen',
            'student_id' => '404',
            'qr_token' => 'token_404',
            'class_level' => 'Thanawiyya',
            'group_id' => $group->id,
        ]);

        $receipt = FundReceipt::create([
            'receipt_number' => 'QFR-26-404',
            'donor_name' => 'Original Name',
            'amount' => 1500,
            'payment_method' => 'cash',
            'student_id' => $student->id,
            'group_id' => $group->id,
        ]);

        // Student updates the receipt to Malayalam name and UPI payment method
        $response = $this->withSession(['fund_student_id' => $student->id])
            ->put(route('fund.student.receipts.update', $receipt), [
                'donor_name' => 'മുഹമ്മദ് സാലിഹ്',
                'payment_method' => 'upi',
                'transaction_reference' => 'UPI-REF-998877',
                'donor_phone' => '9847000111',
                'donor_place' => 'Calicut',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $receipt->refresh();
        $this->assertEquals('മുഹമ്മദ് സാലിഹ്', $receipt->donor_name);
        $this->assertEquals('upi', $receipt->payment_method);
        $this->assertEquals('UPI-REF-998877', $receipt->transaction_reference);
        $this->assertEquals('9847000111', $receipt->donor_phone);
        $this->assertEquals('Calicut', $receipt->donor_place);

        // Also check receipt view shows the Edit button and updated values
        $viewResponse = $this->withSession(['fund_student_id' => $student->id])
            ->get(route('fund.receipts.show', $receipt));
        $viewResponse->assertOk();
        $viewResponse->assertSee('Edit Details');
        $viewResponse->assertSee('മുഹമ്മദ് സാലിഹ്');
        $viewResponse->assertSee('UPI');
    }

    public function test_student_cannot_update_another_students_receipt(): void
    {
        $group = Group::create([
            'name' => 'Al Bayan',
            'slug' => 'al-bayan-2',
            'code' => 'AB2',
            'color_hex' => '#10b981',
        ]);

        $studentA = Student::create([
            'name' => 'Student A',
            'student_id' => '501',
            'qr_token' => 'token_501',
            'class_level' => 'Aliya',
            'group_id' => $group->id,
        ]);

        $studentB = Student::create([
            'name' => 'Student B',
            'student_id' => '502',
            'qr_token' => 'token_502',
            'class_level' => 'Aliya',
            'group_id' => $group->id,
        ]);

        $receiptA = FundReceipt::create([
            'receipt_number' => 'QFR-26-501',
            'donor_name' => 'Owner Name',
            'amount' => 500,
            'payment_method' => 'cash',
            'student_id' => $studentA->id,
            'group_id' => $group->id,
        ]);

        // Student B attempts to edit Student A's receipt
        $response = $this->withSession(['fund_student_id' => $studentB->id])
            ->put(route('fund.student.receipts.update', $receiptA), [
                'donor_name' => 'Hacked Name',
                'payment_method' => 'upi',
            ]);

        $response->assertSessionHas('error');

        $receiptA->refresh();
        $this->assertEquals('Owner Name', $receiptA->donor_name);
        $this->assertEquals('cash', $receiptA->payment_method);
    }

    public function test_receipt_view_contains_customized_malayalam_whatsapp_share_message(): void
    {
        $receipt = FundReceipt::create([
            'receipt_number' => 'QFR-26-777',
            'donor_name' => 'ഉസ്മാൻ ഹാജി',
            'donor_place' => 'കോഴിക്കോട്',
            'amount' => 5000,
            'payment_method' => 'cash',
        ]);

        $response = $this->get(route('fund.receipts.show', $receipt));
        $response->assertOk();
        $response->assertSee('ജസാഖല്ലാഹു ഖൈർ...');
        $response->assertSee('നിങ്ങളുടെ ഈ ഹദിയ അല്ലാഹു സ്വീകരിക്കട്ടെ...');
        $response->assertSee('ഖാഫ് അദബിക് എഡിഷനിൽ ഞങ്ങളോടൊപ്പം ചേരാൻ മർകസിലേക്ക് നിങ്ങൾക്കും ഹൃദ്യമായ സ്വാഗതം');
        $response->assertDontSee('QUAF 09 Official Receipt - QFR-26-777');
    }

    public function test_student_can_delete_own_receipt_with_cancellation_reason(): void
    {
        $group = Group::create([
            'name' => 'Al Bayan',
            'slug' => 'al-bayan-del-1',
            'code' => 'ABD1',
            'color_hex' => '#10b981',
        ]);

        $student = Student::create([
            'name' => 'Test Student Del',
            'student_id' => '601',
            'qr_token' => 'token_601',
            'class_level' => 'Aliya',
            'group_id' => $group->id,
        ]);

        $receipt = FundReceipt::create([
            'receipt_number' => 'QFR-26-601',
            'donor_name' => 'Musthafa',
            'amount' => 1000,
            'payment_method' => 'upi',
            'student_id' => $student->id,
            'group_id' => $group->id,
        ]);

        $response = $this->withSession(['fund_student_id' => $student->id])
            ->delete(route('fund.student.receipts.destroy', $receipt), [
                'reason' => 'തുക തെറ്റായി രേഖപ്പെടുത്തി',
            ]);

        $response->assertSessionHas('success');

        $this->assertSoftDeleted('fund_receipts', [
            'id' => $receipt->id,
            'is_cancelled' => true,
            'cancelled_reason' => 'തുക തെറ്റായി രേഖപ്പെടുത്തി',
        ]);
    }

    public function test_student_cannot_delete_receipt_without_reason(): void
    {
        $group = Group::create([
            'name' => 'Al Bayan',
            'slug' => 'al-bayan-del-2',
            'code' => 'ABD2',
            'color_hex' => '#10b981',
        ]);

        $student = Student::create([
            'name' => 'Test Student Del 2',
            'student_id' => '602',
            'qr_token' => 'token_602',
            'class_level' => 'Aliya',
            'group_id' => $group->id,
        ]);

        $receipt = FundReceipt::create([
            'receipt_number' => 'QFR-26-602',
            'donor_name' => 'Musthafa 2',
            'amount' => 500,
            'payment_method' => 'cash',
            'student_id' => $student->id,
            'group_id' => $group->id,
        ]);

        $response = $this->withSession(['fund_student_id' => $student->id])
            ->delete(route('fund.student.receipts.destroy', $receipt), [
                'reason' => '',
            ]);

        $response->assertSessionHasErrors(['reason']);

        $this->assertDatabaseHas('fund_receipts', [
            'id' => $receipt->id,
            'deleted_at' => null,
            'is_cancelled' => false,
        ]);
    }

    public function test_student_cannot_delete_another_students_receipt(): void
    {
        $group = Group::create([
            'name' => 'Al Bayan',
            'slug' => 'al-bayan-del-3',
            'code' => 'ABD3',
            'color_hex' => '#10b981',
        ]);

        $studentA = Student::create([
            'name' => 'Student A Del',
            'student_id' => '603',
            'qr_token' => 'token_603',
            'class_level' => 'Aliya',
            'group_id' => $group->id,
        ]);

        $studentB = Student::create([
            'name' => 'Student B Del',
            'student_id' => '604',
            'qr_token' => 'token_604',
            'class_level' => 'Aliya',
            'group_id' => $group->id,
        ]);

        $receiptA = FundReceipt::create([
            'receipt_number' => 'QFR-26-603',
            'donor_name' => 'Original Donor',
            'amount' => 1500,
            'payment_method' => 'cash',
            'student_id' => $studentA->id,
            'group_id' => $group->id,
        ]);

        // Student B tries to delete Student A's receipt
        $response = $this->withSession(['fund_student_id' => $studentB->id])
            ->delete(route('fund.student.receipts.destroy', $receiptA), [
                'reason' => 'Unauthorized attempt',
            ]);

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('fund_receipts', [
            'id' => $receiptA->id,
            'deleted_at' => null,
            'is_cancelled' => false,
        ]);
    }

    public function test_student_can_delete_from_receipt_view_page_and_redirects_to_desk(): void
    {
        $group = Group::create([
            'name' => 'Al Bayan',
            'slug' => 'al-bayan-del-4',
            'code' => 'ABD4',
            'color_hex' => '#10b981',
        ]);

        $student = Student::create([
            'name' => 'Student Desk Del',
            'student_id' => '605',
            'qr_token' => 'token_605',
            'class_level' => 'Aliya',
            'group_id' => $group->id,
        ]);

        $receipt = FundReceipt::create([
            'receipt_number' => 'QFR-26-605',
            'donor_name' => 'Donor from Page',
            'amount' => 2000,
            'payment_method' => 'cash',
            'student_id' => $student->id,
            'group_id' => $group->id,
        ]);

        $response = $this->withSession(['fund_student_id' => $student->id])
            ->from(route('fund.receipts.show', $receipt))
            ->delete(route('fund.receipts.destroy', $receipt), [
                'reason' => 'ദാതാവ് പേയ്മെന്റ് റദ്ദാക്കി',
            ]);

        $response->assertRedirect(route('fund.student.desk'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('fund_receipts', [
            'id' => $receipt->id,
            'is_cancelled' => true,
        ]);
    }

    public function test_cancelled_or_soft_deleted_receipt_voucher_can_be_viewed_by_admin_without_404(): void
    {
        $receipt = FundReceipt::create([
            'receipt_number' => 'QFR-26-075',
            'donor_name' => 'Abubakker Jouhari',
            'amount' => 500,
            'payment_method' => 'upi',
            'is_cancelled' => true,
            'cancelled_reason' => 'റദ്ദാക്കപ്പെട്ടു',
            'cancelled_at' => now(),
        ]);
        $receipt->delete(); // Soft delete

        $this->assertTrue($receipt->trashed());

        // 1. Admin can view the voucher without 404
        $adminViewResponse = $this->actingAs($this->admin)
            ->get(route('admin.fund.receipts.show', $receipt->id));

        $adminViewResponse->assertOk();
        $adminViewResponse->assertSee('QFR-26-075');
        $adminViewResponse->assertSee('Abubakker Jouhari');
        $adminViewResponse->assertSee('VOID / CANCELLED');

        // 2. Admin can stream image without 404
        $adminImageResponse = $this->actingAs($this->admin)
            ->get(route('admin.fund.receipts.image', $receipt->id));

        $adminImageResponse->assertOk();

        // 3. Admin can download voucher without 404
        $adminDownloadResponse = $this->actingAs($this->admin)
            ->get(route('admin.fund.receipts.download', $receipt->id));

        $adminDownloadResponse->assertOk();

        // 4. Public route also resolves soft-deleted receipt
        $publicViewResponse = $this->get(route('fund.receipts.show', $receipt->id));
        $publicViewResponse->assertOk();
        $publicViewResponse->assertSee('QFR-26-075');
    }

    public function test_office_collected_data_is_never_shown_in_top_students_list(): void
    {
        $group = Group::create([
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'slug' => 'lumo-fikric-top',
            'color_hex' => '#56286b',
        ]);

        $realStudent = Student::create([
            'student_id' => 'STU991',
            'name' => 'Real Top Student',
            'group_id' => $group->id,
            'admission_number' => 'ADM991',
            'class_level' => 'D4',
            'category' => 'A Zone',
            'qr_token' => Str::random(32),
        ]);

        FundReceipt::create([
            'receipt_number' => 'QFR-26-991',
            'donor_name' => 'Donor For Real Student',
            'amount' => 5000,
            'payment_method' => 'cash',
            'student_id' => $realStudent->id,
            'is_cancelled' => false,
        ]);

        // Ensure OFFICE account exists
        FundController::ensureOfficeAccount();
        $officeStudent = Student::where('student_id', 'OFFICE')->first();
        $this->assertNotNull($officeStudent);

        // Office collects a huge amount
        FundReceipt::create([
            'receipt_number' => 'QFR-26-999',
            'donor_name' => 'Donor For Office',
            'amount' => 50000,
            'payment_method' => 'upi',
            'student_id' => $officeStudent->id,
            'is_cancelled' => false,
        ]);

        $response = $this->get(route('fund.home'));

        $response->assertOk();
        $response->assertSee('Real Top Student');
        $response->assertSee('5,000');

        // Verify topStudents collection passed to the view does not contain OFFICE
        $topStudents = $response->viewData('topStudents');
        $this->assertTrue($topStudents->pluck('name')->contains('Real Top Student'));
        $this->assertFalse($topStudents->pluck('student_id')->contains('OFFICE'));
        $this->assertFalse($topStudents->pluck('name')->contains('OFFICE'));
    }
}
