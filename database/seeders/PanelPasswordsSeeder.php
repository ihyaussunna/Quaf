<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PanelPasswordsSeeder extends Seeder
{
    public function run(): void
    {
        $passwords = [
            'admin@quaf.fest' => 'CentralAdmin#2026@Quaf!',
            'samithi@quaf.fest' => 'Samithi#2026@QuafFest!',
            'announcer@quaf.fest' => 'Announcer#2026@QuafLive!',
            'media@quaf.fest' => 'Media#2026@QuafLive!',
            'greenroom@quaf.fest' => 'GreenRoom#2026@Quaf!',
            'judge1@quaf.fest' => 'Judge1#2026@Quaf!',
            'judge2@quaf.fest' => 'Judge2#2026@Quaf!',
            'student@quaf.fest' => 'Student#2026@Quaf!',
        ];

        User::firstOrCreate(
            ['email' => 'announcer@quaf.fest'],
            [
                'name' => 'QUAF Announcer Desk (അനൗൺസർ ഡെസ്ക്)',
                'role' => 'announcer',
                'phone' => '+91 98470 00015',
                'is_active' => true,
                'plain_password' => 'Announcer#2026@QuafLive!',
                'password' => Hash::make('Announcer#2026@QuafLive!'),
            ]
        );

        foreach ($passwords as $email => $pass) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update([
                    'plain_password' => $pass,
                    'password' => Hash::make($pass),
                ]);
            }
        }

        // Leader passwords from groups
        $groups = Group::all();
        foreach ($groups as $group) {
            if ($group->leader_id && $group->admin_password) {
                $leader = User::find($group->leader_id);
                if ($leader) {
                    $leader->update([
                        'plain_password' => $group->admin_password,
                        'password' => Hash::make($group->admin_password),
                    ]);
                }
            }
        }
    }
}
