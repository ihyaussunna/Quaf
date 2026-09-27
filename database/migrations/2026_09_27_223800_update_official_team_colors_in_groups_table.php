<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $teamColors = [
            'PACTO' => '#2E3192',
            'YUGO' => '#AD1E56',
            'CONCO' => '#F8E709',
            'LUMO' => '#56286B',
            'UNIO' => '#7F1518',
        ];

        foreach ($teamColors as $code => $hex) {
            DB::table('groups')
                ->where('code', $code)
                ->orWhere('name', 'like', "%{$code}%")
                ->update(['color_hex' => $hex]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversible if needed
    }
};
