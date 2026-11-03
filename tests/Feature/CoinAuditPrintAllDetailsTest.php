<?php

namespace Tests\Feature;

use App\Models\CoinTransaction;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoinAuditPrintAllDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Group $group;

    protected ProgramCategory $category;

    protected Program $program;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'name' => 'Finance Auditor',
            'email' => 'auditor@quaf.test',
        ]);

        $this->group = Group::firstOrCreate(
            ['code' => 'UH'],
            ['name' => 'Unio Hilmic', 'slug' => 'unio-hilmic', 'color_hex' => '#10b981', 'display_order' => 1]
        );

        $this->category = ProgramCategory::firstOrCreate(
            ['code' => 'MIX'],
            ['name' => 'Mix Zone', 'slug' => 'mix-zone']
        );

        $this->program = Program::create([
            'category_id' => $this->category->id,
            'name' => 'Photo Feature',
            'code' => 'Q9-243',
            'type' => 'individual',
            'eligibility' => 'Mix Zone',
            'points_weight' => 1.0,
            'status' => 'published',
        ]);

        $this->student = Student::create([
            'name' => 'Abu Hamid. K',
            'student_id' => 'QF4012',
            'group_id' => $this->group->id,
            'category' => 'Mix Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_hamid',
            'coins_cache' => 280,
        ]);
    }

    public function test_print_audit_view_returns_all_details_together(): void
    {
        // Create 35 transactions across award, debit, and clawback
        for ($i = 1; $i <= 35; $i++) {
            CoinTransaction::create([
                'student_id' => $this->student->id,
                'program_id' => $this->program->id,
                'group_id' => $this->group->id,
                'transaction_type' => $i % 3 === 0 ? 'debit' : ($i % 5 === 0 ? 'reversal' : 'credit'),
                'coins' => $i % 3 === 0 ? -150 : ($i % 5 === 0 ? 0 : 280),
                'status' => 'completed',
                'description' => "Audit transaction test entry #{$i}",
                'transaction_reference' => "REF-AUDIT-{$i}",
                'prize_position' => 1,
                'grade' => 'A',
            ]);
        }

        // 1. In regular paginated index, it defaults to 25 rows
        $indexResponse = $this->actingAs($this->adminUser)->get(route('admin.coins.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Print Audit (All Details)');
        $indexResponse->assertSee('Per page:');

        // 2. In dedicated print view, ALL 35 transactions are rendered together without cutoff
        $printResponse = $this->actingAs($this->adminUser)->get(route('admin.coins.print'));
        $printResponse->assertOk();
        $printResponse->assertSee('FESTIVAL COIN LEDGER');
        $printResponse->assertSee('Audit transaction test entry #1');
        $printResponse->assertSee('Audit transaction test entry #25');
        $printResponse->assertSee('Audit transaction test entry #35');
        $printResponse->assertSee('REF-AUDIT-35');
        $printResponse->assertSee('Abu Hamid. K');
        $printResponse->assertSee('#QF4012');
        $printResponse->assertSee('Unio Hilmic');
        $printResponse->assertSee('Prepared by');
        $printResponse->assertSee('Festival Convener');

        // 3. Filtered print works accurately
        $filteredPrintResponse = $this->actingAs($this->adminUser)->get(route('admin.coins.print', [
            'type' => 'debit',
        ]));
        $filteredPrintResponse->assertOk();
        $filteredPrintResponse->assertSee('Cashier Gift Redemptions (-Coins)');
        $filteredPrintResponse->assertSee('Gift Redemption');
    }

    public function test_index_supports_show_all_per_page(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            CoinTransaction::create([
                'student_id' => $this->student->id,
                'program_id' => $this->program->id,
                'group_id' => $this->group->id,
                'transaction_type' => 'credit',
                'coins' => 280,
                'status' => 'completed',
                'prize_position' => 1,
                'grade' => 'A',
                'description' => "Transaction sample row {$i}",
                'transaction_reference' => "REF-ROW-{$i}",
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get(route('admin.coins.index', ['per_page' => 'all']));
        $response->assertOk();
        $response->assertSee('Transaction sample row 1');
        $response->assertSee('Transaction sample row 30');
    }

    public function test_cash_received_metrics_and_filter_work_accurately(): void
    {
        CoinTransaction::create([
            'student_id' => $this->student->id,
            'group_id' => $this->group->id,
            'program_id' => null,
            'transaction_type' => 'credit',
            'coins' => 110,
            'cash_amount' => 110.00,
            'payment_mode' => 'cash',
            'source' => 'amount_received',
            'reward_rule_id' => 'QUAF09_CASHIER_AMOUNT_RECEIVED_V1',
            'transaction_reference' => 'COIN-CASH-TEST-999',
            'status' => 'completed',
            'prize_position' => 0,
            'description' => 'Amount Received (പണം): ₹110.00 (+110 Coins for Order #589)',
        ]);

        // 1. Index shows Cash Received metric card
        $indexResponse = $this->actingAs($this->adminUser)->get(route('admin.coins.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Cash Received (പണം)');
        $indexResponse->assertSee('110.00');

        // 2. Filter by type=cash returns the transaction and proper label
        $filterResponse = $this->actingAs($this->adminUser)->get(route('admin.coins.index', ['type' => 'cash']));
        $filterResponse->assertOk();
        $filterResponse->assertSee('COIN-CASH-TEST-999');
        $filterResponse->assertSee('Cash Received (പണം സ്വീകരിച്ചത്)');
        $filterResponse->assertSee('Cashier Desk (പണം നൽകി ചേർത്തത്)');

        // 3. Print view with type=cash shows proper scope and table
        $printResponse = $this->actingAs($this->adminUser)->get(route('admin.coins.print', ['type' => 'cash']));
        $printResponse->assertOk();
        $printResponse->assertSee('Cash Received / Added Coins (പണം നൽകി ചേർത്തവ)');
        $printResponse->assertSee('COIN-CASH-TEST-999');
        $printResponse->assertSee('110.00');
    }
}
