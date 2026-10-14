<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('fund_receipt_settings')) {
            Schema::create('fund_receipt_settings', function (Blueprint $table) {
                $table->id();
                $table->string('header_title')->default('QUAF SEASON 09');
                $table->string('organization_name')->default('JAMIA IHYA-US-SUNNA');
                $table->string('subtitle')->default('Grand Festival Fund Collection Voucher');
                $table->string('text_alignment', 20)->default('center'); // left, center, right
                $table->string('font_size', 20)->default('normal'); // small, normal, large
                $table->string('padding_size', 20)->default('normal'); // compact, normal, relaxed
                $table->string('primary_color', 20)->default('#be1e2d');
                $table->string('accent_color', 20)->default('#d97706');
                $table->string('text_color', 20)->default('#0f172a');
                $table->string('bg_color', 20)->default('#ffffff');
                $table->boolean('show_logo')->default(true);
                $table->string('logo_path')->nullable();
                $table->text('footer_text')->nullable();
                $table->string('authorized_by')->default('QUAF Finance Samithi');
                $table->timestamps();
            });

            DB::table('fund_receipt_settings')->insert([
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
                'show_logo' => true,
                'footer_text' => 'Thank you for your generous contribution towards QUAF 9.0!',
                'authorized_by' => 'QUAF Finance Samithi',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! Schema::hasTable('fund_receipts')) {
            Schema::create('fund_receipts', function (Blueprint $table) {
                $table->id();
                $table->string('receipt_number', 50)->unique();
                $table->string('donor_name');
                $table->string('donor_phone', 30)->nullable();
                $table->string('donor_place', 100)->nullable();
                $table->decimal('amount', 12, 2);
                $table->string('amount_in_words')->nullable();
                $table->string('payment_method', 30)->default('cash'); // cash, upi, gpay, bank_transfer, other
                $table->string('transaction_reference', 100)->nullable();
                $table->string('category', 100)->default('Festival Fund');
                $table->text('notes')->nullable();
                $table->foreignId('collector_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('collector_name')->nullable();
                $table->boolean('is_cancelled')->default(false);
                $table->string('cancelled_reason')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->softDeletes();
                $table->timestamps();

                $table->index(['created_at', 'is_cancelled']);
                $table->index(['donor_phone', 'donor_name']);
                $table->index('collector_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_receipts');
        Schema::dropIfExists('fund_receipt_settings');
    }
};
