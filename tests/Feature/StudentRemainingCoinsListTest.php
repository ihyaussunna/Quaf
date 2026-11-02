<?php

namespace Tests\Feature;

use App\Models\CoinTransaction;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentRemainingCoinsListTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Group $groupA;

    protected Group $groupB;

    protected Student $studentWithCoins1;

    protected Student $studentWithCoins2;

    protected Student $studentZeroCoins;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'name' => 'Finance Auditor',
            'email' => 'auditor@quaf.test',
        ]);

        $this->groupA = Group::firstOrCreate(
            ['code' => 'UH'],
            ['name' => 'Unio Hilmic', 'slug' => 'unio-hilmic', 'color_hex' => '#10b981', 'display_order' => 1]
        );

        $this->groupB = Group::firstOrCreate(
            ['code' => 'YR'],
            ['name' => 'Yugo Rushdic', 'slug' => 'yugo-rushdic', 'color_hex' => '#f59e0b', 'display_order' => 2]
        );

        $this->studentWithCoins1 = Student::create([
            'name' => 'Abu Hamid. K',
            'student_id' => 'QF4012',
            'chest_number' => '4012',
            'group_id' => $this->groupA->id,
            'category' => 'Mix Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_hamid_rem',
            'coins_cache' => 180,
        ]);

        $this->studentWithCoins2 = Student::create([
            'name' => 'Ahammed Farhan Kp',
            'student_id' => 'QF5012',
            'chest_number' => '5012',
            'group_id' => $this->groupB->id,
            'category' => 'Mix Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_farhan_rem',
            'coins_cache' => 95,
        ]);

        $this->studentZeroCoins = Student::create([
            'name' => 'Alif Kollam',
            'student_id' => 'QF1229',
            'chest_number' => '1229',
            'group_id' => $this->groupA->id,
            'category' => 'Mix Zone',
            'gender' => 'Male',
            'qr_token' => 'qr_alif_rem',
            'coins_cache' => 0,
        ]);

        // Transactions for student 1 (Total earned: 250, Total redeemed: 70 -> Balance: 180)
        CoinTransaction::create([
            'student_id' => $this->studentWithCoins1->id,
            'group_id' => $this->groupA->id,
            'transaction_type' => 'credit',
            'coins' => 250,
            'prize_position' => 1,
            'status' => 'completed',
            'description' => 'First Prize Award',
            'transaction_reference' => 'REF-CR-01',
            'created_at' => now()->subDay(),
        ]);
        CoinTransaction::create([
            'student_id' => $this->studentWithCoins1->id,
            'group_id' => $this->groupA->id,
            'transaction_type' => 'debit',
            'coins' => -70,
            'prize_position' => 0,
            'status' => 'completed',
            'description' => 'Gift Redemption',
            'transaction_reference' => 'REF-DR-01',
            'created_at' => now(),
        ]);
    }

    public function test_admin_can_view_remaining_coins_list(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.coins.remaining'));

        $response->assertStatus(200);
        $response->assertSee('QF4012');
        $response->assertSee('Abu Hamid. K');
        $response->assertSee('180');
        $response->assertSee('QF5012');
        $response->assertSee('Ahammed Farhan Kp');
        $response->assertSee('95');

        // Zero balance student must not be listed
        $response->assertDontSee('QF1229');
    }

    public function test_search_by_qf_number_filters_correctly(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.coins.remaining', ['search' => 'QF4012']));

        $response->assertStatus(200);
        $response->assertSee('QF4012');
        $response->assertSee('Abu Hamid. K');
        $response->assertDontSee('QF5012');
        $response->assertDontSee('Ahammed Farhan Kp');
    }

    public function test_filter_by_group_works(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.coins.remaining', ['group' => $this->groupB->id]));

        $response->assertStatus(200);
        $response->assertSee('QF5012');
        $response->assertSee('Ahammed Farhan Kp');
        $response->assertDontSee('Abu Hamid. K');
    }

    public function test_csv_export_returns_stream_with_correct_data(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.coins.remaining', ['export' => 'csv']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('QF Number / Chest No', $content);
        $this->assertStringContainsString('Remaining Coin Balance', $content);
        $this->assertStringContainsString('QF4012', $content);
        $this->assertStringContainsString('Abu Hamid. K', $content);
        $this->assertStringContainsString('180', $content);
        $this->assertStringNotContainsString('QF1229', $content);
    }

    public function test_coins_index_has_links_to_remaining_coins_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.coins.index'));

        $response->assertStatus(200);
        $response->assertSee(route('admin.coins.remaining'));
    }
}
