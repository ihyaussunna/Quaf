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
        if (Schema::hasTable('fund_receipt_settings')) {
            Schema::table('fund_receipt_settings', function (Blueprint $table) {
                // Receipt Number customization
                if (! Schema::hasColumn('fund_receipt_settings', 'rcp_x')) {
                    $table->integer('rcp_x')->default(890);
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'rcp_y')) {
                    $table->integer('rcp_y')->default(175);
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'rcp_font_size')) {
                    $table->integer('rcp_font_size')->default(20);
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'rcp_color')) {
                    $table->string('rcp_color', 25)->default('#5c5a5b');
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'rcp_align')) {
                    $table->string('rcp_align', 10)->default('left');
                }

                // Date customization
                if (! Schema::hasColumn('fund_receipt_settings', 'date_x')) {
                    $table->integer('date_x')->default(830);
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'date_y')) {
                    $table->integer('date_y')->default(230);
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'date_font_size')) {
                    $table->integer('date_font_size')->default(20);
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'date_color')) {
                    $table->string('date_color', 25)->default('#5c5a5b');
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'date_align')) {
                    $table->string('date_align', 10)->default('left');
                }

                // Donor Name customization
                if (! Schema::hasColumn('fund_receipt_settings', 'donor_x')) {
                    $table->integer('donor_x')->default(563);
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'donor_y')) {
                    $table->integer('donor_y')->default(610);
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'donor_font_size')) {
                    $table->integer('donor_font_size')->default(40);
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'donor_color')) {
                    $table->string('donor_color', 25)->default('#ffffff');
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'donor_align')) {
                    $table->string('donor_align', 10)->default('center');
                }

                // Amount customization
                if (! Schema::hasColumn('fund_receipt_settings', 'amount_x')) {
                    $table->integer('amount_x')->default(563);
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'amount_y')) {
                    $table->integer('amount_y')->default(755);
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'amount_font_size')) {
                    $table->integer('amount_font_size')->default(40);
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'amount_color')) {
                    $table->string('amount_color', 25)->default('#000000');
                }
                if (! Schema::hasColumn('fund_receipt_settings', 'amount_align')) {
                    $table->string('amount_align', 10)->default('center');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('fund_receipt_settings')) {
            Schema::table('fund_receipt_settings', function (Blueprint $table) {
                $columns = [
                    'rcp_x', 'rcp_y', 'rcp_font_size', 'rcp_color', 'rcp_align',
                    'date_x', 'date_y', 'date_font_size', 'date_color', 'date_align',
                    'donor_x', 'donor_y', 'donor_font_size', 'donor_color', 'donor_align',
                    'amount_x', 'amount_y', 'amount_font_size', 'amount_color', 'amount_align',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('fund_receipt_settings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
