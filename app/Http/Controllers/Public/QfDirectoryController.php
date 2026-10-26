<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Student;
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
                'count' => 0,
                'flag' => asset('images/teams/flag-lumo-fikric.jpg'),
            ],
            'Pacto Hikmic' => [
                'name' => 'Pacto Hikmic',
                'color' => '#2e3192',
                'leader' => $raw['Pacto Hikmic']['leader'] ?? 'BASIL ADANY',
                'assistants' => $raw['Pacto Hikmic']['assistants'] ?? ['ASHIQ CHENGANASSERI', 'UVAIS PALLAM'],
                'count' => 0,
                'flag' => asset('images/teams/flag-pacto-hikmic.jpg'),
            ],
            'Conco Majdic' => [
                'name' => 'Conco Majdic',
                'color' => '#b45309',
                'badge_color' => '#f59e0b',
                'leader' => $raw['Conco Majdic']['leader'] ?? 'SINAN SAQAFI VELLIMUTTAM',
                'assistants' => $raw['Conco Majdic']['assistants'] ?? ['MUSHARAF PONNANI', 'ZAINUL ABID VAVAD'],
                'count' => 0,
                'flag' => asset('images/teams/flag-conco-majdic.jpg'),
            ],
            'Yugo Rushdic' => [
                'name' => 'Yugo Rushdic',
                'color' => '#ac1553',
                'leader' => $raw['Yugo Rushdic']['leader'] ?? 'JABIR SAQAFI',
                'assistants' => $raw['Yugo Rushdic']['assistants'] ?? ['SALMAN PAKKANA', 'JAISAL KUFA'],
                'count' => 0,
                'flag' => asset('images/teams/flag-yugo-rushdic.jpg'),
            ],
            'Unio Hilmic' => [
                'name' => 'Unio Hilmic',
                'color' => '#7F1518',
                'leader' => $raw['Unio Hilmic']['leader'] ?? 'ANAS ADANY',
                'assistants' => $raw['Unio Hilmic']['assistants'] ?? ['HASEEB VENNIYUR', 'THWAYYIB IRITTY'],
                'count' => 0,
                'flag' => asset('images/teams/flag-unio-hilmic.png'),
            ],
        ];

        // Fetch live students from database with their assigned group and zone (excluding system/office accounts)
        $dbStudents = Student::with(['group', 'zone'])
            ->whereNotNull('group_id')
            ->orderBy('student_id')
            ->get();

        $allMembers = [];

        if ($dbStudents->isNotEmpty()) {
            foreach ($dbStudents as $s) {
                $teamName = $s->group?->name ?? 'General';
                $leader = $teams[$teamName]['leader'] ?? ($raw[$teamName]['leader'] ?? '');
                $assistants = $teams[$teamName]['assistants'] ?? ($raw[$teamName]['assistants'] ?? []);

                $allMembers[] = [
                    'qf' => (string) ($s->chest_number ?: $s->student_id),
                    'name' => (string) $s->name,
                    'class' => (string) ($s->class_level ?: ($s->category ?: 'General')),
                    'zone' => (string) ($s->zone?->name ?: ($s->category ?: 'A ZONE')),
                    'team' => $teamName,
                    'leader' => $leader,
                    'assistants' => $assistants,
                ];

                if (isset($teams[$teamName])) {
                    $teams[$teamName]['count']++;
                }
            }
        } elseif (! empty($raw)) {
            // Fallback to static JSON dataset when database has no student records
            foreach ($raw as $teamName => $teamData) {
                $leader = $teamData['leader'] ?? '';
                $assistants = $teamData['assistants'] ?? [];
                $members = $teamData['members'] ?? [];

                if (isset($teams[$teamName])) {
                    $teams[$teamName]['count'] = count($members);
                }

                foreach ($members as $m) {
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
            'title' => 'QF Chest Directory & Participants — QUAF 2026 | Markaz Art Fest',
            'ogTitle' => 'QF Chest Directory & Participants — QUAF 2026 Markaz Cultural Festival',
            'metaDescription' => 'Search participant names, QF chest numbers, academic groups, categories, zones, and leadership for QUAF 2026 Markaz Art Festival.',
            'ogDescription' => 'Search and identify participant QF numbers, academic groups, categories, zones, and leadership across all five collegiate teams.',
            'teams' => $teams,
            'members' => $allMembers,
            'totalCount' => count($allMembers),
        ]);
    }
}
