<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'plain_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('plain_password')->nullable()->after('password');
            });
        }

        // 1. Create or update Media Team user
        $mediaUser = User::firstOrCreate(
            ['email' => 'media@quaf.fest'],
            [
                'name' => 'QUAF Media Team (മീഡിയ വിംഗ്)',
                'password' => Hash::make('Media#2026@QuafLive!'),
                'plain_password' => 'Media#2026@QuafLive!',
                'role' => 'media_team',
                'phone' => '+91 98470 00012',
                'is_active' => true,
            ]
        );
        $mediaUser->update([
            'plain_password' => 'Media#2026@QuafLive!',
            'role' => 'media_team',
        ]);

        // 2. Populate plain_password for existing panel accounts
        $passwords = [
            'admin@quaf.fest' => 'CentralAdmin#2026@Quaf!',
            'samithi@quaf.fest' => 'Samithi#2026@QuafFest!',
            'greenroom@quaf.fest' => 'GreenRoom#2026@Quaf!',
            'judge1@quaf.fest' => 'Judge1#2026@Quaf!',
            'judge2@quaf.fest' => 'Judge2#2026@Quaf!',
            'student@quaf.fest' => 'Student#2026@Quaf!',
        ];

        foreach ($passwords as $email => $plainPass) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update([
                    'password' => Hash::make($plainPass),
                    'plain_password' => $plainPass,
                ]);
            }
        }

        // 3. Populate plain_password for group leaders from groups table
        $groups = Group::all();
        foreach ($groups as $group) {
            if ($group->leader_id && $group->admin_password) {
                $leader = User::find($group->leader_id);
                if ($leader) {
                    $leader->update([
                        'password' => Hash::make($group->admin_password),
                        'plain_password' => $group->admin_password,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'plain_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('plain_password');
            });
        }
    }
};
