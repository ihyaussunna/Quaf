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
        if (Schema::hasTable('fund_receipts')) {
            Schema::table('fund_receipts', function (Blueprint $table) {
                if (! Schema::hasColumn('fund_receipts', 'whatsapp_number')) {
                    $table->string('whatsapp_number', 30)->nullable()->after('donor_phone');
                }
                if (! Schema::hasColumn('fund_receipts', 'screenshot_path')) {
                    $table->string('screenshot_path')->nullable()->after('transaction_reference');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('fund_receipts')) {
            Schema::table('fund_receipts', function (Blueprint $table) {
                if (Schema::hasColumn('fund_receipts', 'whatsapp_number')) {
                    $table->dropColumn('whatsapp_number');
                }
                if (Schema::hasColumn('fund_receipts', 'screenshot_path')) {
                    $table->dropColumn('screenshot_path');
                }
            });
        }
    }
};
