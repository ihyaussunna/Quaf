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

        // Explicit 5 Group Leader accounts and credentials
        $leaderData = [
            'LUMO' => [
                'email' => 'leader.lumo@quaf.fest',
                'name' => 'WARIS ADANY (Leader - LUMO FIKRIC)',
                'password' => 'Lumo#9482@FikricFest!26',
                'phone' => '+91 98470 00009',
            ],
            'PACTO' => [
                'email' => 'leader.pacto@quaf.fest',
                'name' => 'BASIL ADANY (Leader - PACTO HIKMIC)',
                'password' => 'Pacto$Hikmic*8319#Q9',
                'phone' => '+91 98470 00006',
            ],
            'CONCO' => [
                'email' => 'leader.conco@quaf.fest',
                'name' => 'SINAN SAQAFI VELLIMUTTAM (Leader - CONCO MAJDIC)',
                'password' => 'Majdic&Conco%6724!Apex',
                'phone' => '+91 98470 00008',
            ],
            'UNIO' => [
                'email' => 'leader.unio@quaf.fest',
                'name' => 'ANAS ADANY (Leader - UNIO HILMIC)',
                'password' => 'Unio_5193-Hilmic@9Fest',
                'phone' => '+91 98470 00010',
            ],
            'YUGO' => [
                'email' => 'leader.yugo@quaf.fest',
                'name' => 'JABIR SAQAFI (Leader - YUGO RUSHDIC)',
                'password' => 'Yugo!Rushdic?3825#Shield',
                'phone' => '+91 98470 00007',
            ],
        ];

        foreach ($leaderData as $groupCode => $info) {
            $leader = User::updateOrCreate(
                ['email' => $info['email']],
                [
                    'name' => $info['name'],
                    'role' => 'group_leader',
                    'phone' => $info['phone'],
                    'is_active' => true,
                    'plain_password' => $info['password'],
                    'password' => Hash::make($info['password']),
                ]
            );

            $group = Group::where('code', $groupCode)
                ->orWhere('slug', strtolower($groupCode))
                ->orWhere('name', 'like', "%{$groupCode}%")
                ->first();

            if ($group) {
                $group->update([
                    'leader_id' => $leader->id,
                    'admin_username' => $info['email'],
                    'admin_password' => $info['password'],
                ]);
            }
        }
    }
}
