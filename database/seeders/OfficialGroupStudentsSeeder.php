<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Student;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfficialGroupStudentsSeeder extends Seeder
{
    /**
     * Seed all official student rosters for all 5 groups:
     * - LUMO FIKRIC (236 students: QF1001 to QF1236)
     * - PACTO HIKMIC (229 students: QF2001 to QF2229)
     * - CONCO MAJDIC (236 students: QF3001 to QF3236)
     * - UNIO HILMIC (232 students: QF4001 to QF4232)
     * - YUGO RUSHDIC (235 students: QF5001 to QF5235)
     */
    public function run(): void
    {
        $allZones = Zone::all();
        $zoneMap = [
            'A ZONE' => $allZones->firstWhere('name', 'A Zone') ?? $allZones->firstWhere('code', 'A_ZONE'),
            'B ZONE' => $allZones->firstWhere('name', 'B Zone') ?? $allZones->firstWhere('code', 'B_ZONE'),
            'C ZONE' => $allZones->firstWhere('name', 'C Zone') ?? $allZones->firstWhere('code', 'C_ZONE'),
            'MIX ZONE' => $allZones->firstWhere('name', 'Mix Zone') ?? $allZones->firstWhere('code', 'MIX_ZONE'),
        ];
        $defaultZone = $allZones->firstWhere('name', 'A Zone');

        $groupsConfig = [
            'LUMO' => [
                'code' => 'LUMO',
                'name' => 'Lumo Fikric',
                'slug' => 'lumo-fikric',
                'color_hex' => '#56286B',
                'manager_name' => 'WARIS ADANY',
                'assistant_managers' => ['SUFIYAN KOOTTAMPARA', 'JAFAR GUDALUR'],
                'file' => base_path('database/data/LUMO_FIKRIC.csv'),
                'delimiter' => ',',
            ],
            'PACTO' => [
                'code' => 'PACTO',
                'name' => 'Pacto Hikmic',
                'slug' => 'pacto-hikmic',
                'color_hex' => '#2E3192',
                'manager_name' => 'BASIL ADANY',
                'assistant_managers' => ['ASHIQ CHENGANASSERI', 'UVAIS PALLAM'],
                'file' => base_path('database/data/PACTO_HIKMIC.csv'),
                'delimiter' => ',',
            ],
            'CONCO' => [
                'code' => 'CONCO',
                'name' => 'Conco Majdic',
                'slug' => 'conco-majdic',
                'color_hex' => '#F8E709',
                'manager_name' => 'SINAN SAQAFI VELLIMUTTAM',
                'assistant_managers' => ['MUSHARAF PONNANI', 'ZAINUL ABID VAVAD'],
                'file' => base_path('database/data/conco_majdic_students.tsv'),
                'delimiter' => "\t",
            ],
            'UNIO' => [
                'code' => 'UNIO',
                'name' => 'Unio Hilmic',
                'slug' => 'unio-hilmic',
                'color_hex' => '#7F1518',
                'manager_name' => 'ANAS ADANY',
                'assistant_managers' => ['HASEEB VENNIYUR', 'THWAYYIB IRITTY'],
                'file' => base_path('database/data/UNIO_HILMIC.csv'),
                'delimiter' => ',',
            ],
            'YUGO' => [
                'code' => 'YUGO',
                'name' => 'Yugo Rushdic',
                'slug' => 'yugo-rushdic',
                'color_hex' => '#AD1E56',
                'manager_name' => 'JABIR SAQAFI',
                'assistant_managers' => ['SALMAN PAKKANA', 'JAISAL KUFA'],
                'file' => base_path('database/data/YUGO_RUSHDIC.csv'),
                'delimiter' => ',',
            ],
        ];

        $totalSeeded = 0;

        $cleanString = function (?string $val): string {
            if ($val === null || $val === '') {
                return '';
            }
            $str = str_replace(["\xC2\xA0", "\xA0"], ' ', $val);
            $str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
            $str = preg_replace('/[^\x20-\x7E]/', '', $str);
            $str = preg_replace('/\s+/', ' ', $str);

            return trim($str);
        };

        DB::transaction(function () use ($groupsConfig, $zoneMap, $defaultZone, &$totalSeeded, $cleanString) {
            foreach ($groupsConfig as $code => $config) {
                $group = Group::where('code', $code)
                    ->orWhere('slug', $config['slug'])
                    ->orWhere('name', 'like', "%{$config['name']}%")
                    ->first();

                if (! $group) {
                    $group = Group::create([
                        'code' => $code,
                        'name' => $config['name'],
                        'slug' => $config['slug'],
                        'color_hex' => $config['color_hex'],
                        'manager_name' => $config['manager_name'],
                        'assistant_managers' => $config['assistant_managers'],
                        'name_in_results' => $config['name'],
                        'name_in_certificates' => $config['name'],
                    ]);
                } else {
                    $group->update([
                        'code' => $code,
                        'name' => $config['name'],
                        'manager_name' => $config['manager_name'],
                        'assistant_managers' => $config['assistant_managers'],
                    ]);
                }

                if (! file_exists($config['file'])) {
                    continue;
                }

                $handle = fopen($config['file'], 'r');
                if (! $handle) {
                    continue;
                }

                $groupCount = 0;
                while (($row = fgetcsv($handle, 1000, $config['delimiter'])) !== false) {
                    if (empty($row) || empty($row[0])) {
                        continue;
                    }

                    $chest = strtoupper(trim((string) $row[0]));
                    if (! preg_match('/^QF\d{4}$/i', $chest)) {
                        continue;
                    }

                    $name = $cleanString($row[1] ?? '');
                    $class = $cleanString($row[2] ?? '');
                    $zoneRaw = strtoupper($cleanString($row[3] ?? ''));

                    $zone = $zoneMap[$zoneRaw] ?? $defaultZone;
                    $category = $zone ? $zone->name : 'A Zone';
                    $zoneId = $zone ? $zone->id : 1;

                    Student::updateOrCreate(
                        ['student_id' => $chest],
                        [
                            'name' => $name,
                            'group_id' => $group->id,
                            'zone_id' => $zoneId,
                            'category' => $category,
                            'class_level' => ! empty($class) ? $class : null,
                            'gender' => 'Male',
                            'qr_token' => Str::random(40),
                        ]
                    );

                    $groupCount++;
                    $totalSeeded++;
                }

                fclose($handle);
            }
        });
    }
}
