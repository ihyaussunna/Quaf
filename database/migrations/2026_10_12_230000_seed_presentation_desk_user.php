<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        User::firstOrCreate(
            ['email' => 'presentation@quaf.fest'],
            [
                'name' => 'QUAF Presentation Desk (പ്രസന്റേഷൻ ഡെസ്ക്)',
                'role' => 'presentation_team',
                'phone' => '+91 98470 00020',
                'is_active' => true,
                'plain_password' => 'Presentation#2026@Quaf!',
                'password' => Hash::make('Presentation#2026@Quaf!'),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        User::where('email', 'presentation@quaf.fest')->delete();
    }
};
