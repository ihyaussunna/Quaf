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
            ['email' => 'samithi@quaf.fest'],
            [
                'name' => 'Program Samithi (പ്രോഗ്രാം സമിതി)',
                'password' => Hash::make('Samithi#2026@QuafFest!'),
                'role' => 'program_committee',
                'phone' => '+91 98470 00011',
                'is_active' => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        User::where('email', 'samithi@quaf.fest')->delete();
    }
};
