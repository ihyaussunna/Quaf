<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (! Schema::hasColumn('students', 'purchase_pin')) {
                    $table->string('purchase_pin', 10)->nullable()->after('qr_token');
                }
                if (! Schema::hasColumn('students', 'recognition_gift_delivered')) {
                    $table->boolean('recognition_gift_delivered')->default(false)->after('coins_cache');
                }
                if (! Schema::hasColumn('students', 'recognition_gift_delivered_at')) {
                    $table->timestamp('recognition_gift_delivered_at')->nullable()->after('recognition_gift_delivered');
                }
                if (! Schema::hasColumn('students', 'recognition_gift_delivered_by')) {
                    $table->string('recognition_gift_delivered_by', 100)->nullable()->after('recognition_gift_delivered_at');
                }
            });

            // Populate purchase_pin for all existing students
            DB::transaction(function () {
                $students = DB::table('students')->select('id', 'student_id', 'qr_token', 'purchase_pin')->get();
                foreach ($students as $student) {
                    if (empty($student->purchase_pin)) {
                        $token = $student->qr_token ?: $student->student_id ?: (string) $student->id;
                        $pin = str_pad((string) (abs(crc32($token)) % 9000 + 1000), 4, '0', STR_PAD_LEFT);
                        DB::table('students')->where('id', $student->id)->update(['purchase_pin' => $pin]);
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                $cols = array_filter(
                    ['purchase_pin', 'recognition_gift_delivered', 'recognition_gift_delivered_at', 'recognition_gift_delivered_by'],
                    fn ($col) => Schema::hasColumn('students', $col)
                );
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
