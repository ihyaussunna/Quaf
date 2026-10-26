<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class QfDirectoryController extends Controller
{
    public function index(): View
    {
        $jsonPath = public_path('data/qf-directory.json');
        $raw = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];

        $teams = [
            'Lumo Fikric' => [
                'name' => 'Lumo Fikric',
                'color' => '#4d2469',
                'leader' => $raw['Lumo Fikric']['leader'] ?? 'WARIS ADANY',
                'assistants' => $raw['Lumo Fikric']['assistants'] ?? ['SUFIYAN KOOTTAMPARA', 'JAFAR GUDALUR'],
                'count' => isset($raw['Lumo Fikric']['members']) ? count($raw['Lumo Fikric']['members']) : 236,
                'flag' => asset('images/teams/flag-lumo-fikric.jpg'),
            ],
            'Pacto Hikmic' => [
                'name' => 'Pacto Hikmic',
                'color' => '#2e3192',
                'leader' => $raw['Pacto Hikmic']['leader'] ?? 'BASIL ADANY',
                'assistants' => $raw['Pacto Hikmic']['assistants'] ?? ['ASHIQ CHENGANASSERI', 'UVAIS PALLAM'],
                'count' => isset($raw['Pacto Hikmic']['members']) ? count($raw['Pacto Hikmic']['members']) : 229,
                'flag' => asset('images/teams/flag-pacto-hikmic.jpg'),
            ],
            'Conco Majdic' => [
                'name' => 'Conco Majdic',
                'color' => '#b45309',
                'badge_color' => '#f59e0b',
                'leader' => $raw['Conco Majdic']['leader'] ?? 'SINAN SAQAFI VELLIMUTTAM',
                'assistants' => $raw['Conco Majdic']['assistants'] ?? ['MUSHARAF PONNANI', 'ZAINUL ABID VAVAD'],
                'count' => isset($raw['Conco Majdic']['members']) ? count($raw['Conco Majdic']['members']) : 236,
                'flag' => asset('images/teams/flag-conco-majdic.jpg'),
            ],
            'Yugo Rushdic' => [
                'name' => 'Yugo Rushdic',
                'color' => '#ac1553',
                'leader' => $raw['Yugo Rushdic']['leader'] ?? 'JABIR SAQAFI',
                'assistants' => $raw['Yugo Rushdic']['assistants'] ?? ['SALMAN PAKKANA', 'JAISAL KUFA'],
                'count' => isset($raw['Yugo Rushdic']['members']) ? count($raw['Yugo Rushdic']['members']) : 235,
                'flag' => asset('images/teams/flag-yugo-rushdic.jpg'),
            ],
            'Unio Hilmic' => [
                'name' => 'Unio Hilmic',
                'color' => '#451a03',
                'leader' => $raw['Unio Hilmic']['leader'] ?? 'ANAS ADANY',
                'assistants' => $raw['Unio Hilmic']['assistants'] ?? ['HASEEB VENNIYUR', 'THWAYYIB IRITTY'],
                'count' => isset($raw['Unio Hilmic']['members']) ? count($raw['Unio Hilmic']['members']) : 232,
                'flag' => asset('images/teams/flag-unio-hilmic.jpg'),
            ],
        ];

        $allMembers = [];
        if (! empty($raw)) {
            foreach ($raw as $teamName => $teamData) {
                $leader = $teamData['leader'] ?? '';
                $assistants = $teamData['assistants'] ?? [];
                foreach ($teamData['members'] as $m) {
                    $allMembers[] = [
                        'qf' => $m['qf'],
                        'name' => $m['name'],
                        'class' => $m['class'],
                        'zone' => $m['zone'],
                        'team' => $teamName,
                        'leader' => $leader,
                        'assistants' => $assistants,
                    ];
                }
            }
        }

        return view('public.qf-directory', [
            'title' => 'QF Chest Directory — QUAF 2026',
            'ogTitle' => 'QF Chest Directory — QUAF 2026 Markaz Cultural Festival',
            'ogDescription' => 'Search and identify participant QF numbers, academic groups, categories, zones, and leadership across all five collegiate teams.',
            'teams' => $teams,
            'members' => $allMembers,
            'totalCount' => count($allMembers),
        ]);
    }
}
