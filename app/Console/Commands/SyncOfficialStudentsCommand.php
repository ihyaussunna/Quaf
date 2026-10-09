<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use App\Models\Zone;
use App\Services\PointCalculationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

#[Signature('app:sync-official-students')]
#[Description('Wipe old students, sync official 5 Groups with exact names, and import all students from official CSV')]
class SyncOfficialStudentsCommand extends Command
{
    public function handle(): int
    {
        $this->info('Starting sync of official 5 Groups and 2026 student rosters...');

        $csvPath = database_path('data/students_official_2026.csv');
        if (! file_exists($csvPath)) {
            $csvPath = 'C:\\Users\\CELLFI\\Downloads\\quaf 26 students list gropue wise.csv';
        }

        if (! file_exists($csvPath)) {
            $this->error("CSV file not found at: {$csvPath}");

            return Command::FAILURE;
        }

        DB::transaction(function () use ($csvPath) {
            // 1. Ensure 4 Master Zones exist
            $aZone = Zone::updateOrCreate(['code' => Zone::A_ZONE], [
                'name' => 'A Zone',
                'slug' => 'a-zone',
                'sub_text' => 'റാബിഅ, തഖസ്സുസ് (ക്ലാസ് 4)',
                'classes' => 'All Class 4 (NF4, UH4, S4, ID4, UT4, L4, TQS)',
                'color_hex' => '#be1e2d',
                'display_order' => 1,
            ]);
            $bZone = Zone::updateOrCreate(['code' => Zone::B_ZONE], [
                'name' => 'B Zone',
                'slug' => 'b-zone',
                'sub_text' => 'സാലിസ് (ക്ലാസ് 3)',
                'classes' => 'All Class 3 (NF3, ID3, UH3, UT3, S3, L3)',
                'color_hex' => '#f3bd2e',
                'display_order' => 2,
            ]);
            $cZone = Zone::updateOrCreate(['code' => Zone::C_ZONE], [
                'name' => 'C Zone',
                'slug' => 'c-zone',
                'sub_text' => 'ഊല, സാനി (ക്ലാസ് 1 & 2)',
                'classes' => 'All Class 1 & 2 (U1, U2, L2, S1, S2)',
                'color_hex' => '#005c94',
                'display_order' => 3,
            ]);
            $mixZone = Zone::updateOrCreate(['code' => Zone::MIX_ZONE], [
                'name' => 'Mix Zone',
                'slug' => 'mix-zone',
                'sub_text' => 'എല്ലാ സോണുകൾക്കും (ജനറൽ കാറ്റഗറി)',
                'classes' => 'All Classes Included (General Category)',
                'color_hex' => '#009444',
                'display_order' => 4,
            ]);

            // 2. Ensure / Update the 5 Official Groups with user-specified mapping:
            // Team A: Lumo Fikric
            // Team B: Pacto Hikmic
            // Team C: Conco Majdic
            // Team D: Unio Hilmic
            // Team E: Yugo Rushdic

            $lumo = Group::where('slug', 'lumo-fikric')->orWhere('code', 'LUMO')->first() ?? new Group;
            $lumo->fill([
                'name' => 'Lumo Fikric',
                'code' => 'LUMO',
                'slug' => 'lumo-fikric',
                'color_hex' => '#56286b',
                'manager_name' => 'WARIS ADANY',
                'assistant_managers' => ['SUFIYAN KOOTTAMPARA', 'JAFAR GUDALUR'],
                'name_in_results' => 'Lumo Fikric',
                'name_in_certificates' => 'Lumo Fikric',
                'logo_url' => null,
            ]);
            $lumo->save();

            $pacto = Group::where('slug', 'pacto-hikmic')->orWhere('code', 'PACTO')->first() ?? new Group;
            $pacto->fill([
                'name' => 'Pacto Hikmic',
                'code' => 'PACTO',
                'slug' => 'pacto-hikmic',
                'color_hex' => '#2e3192',
                'manager_name' => 'BASIL ADANY',
                'assistant_managers' => ['ASHIQ CHENGANASSERI', 'UVAIS PALLAM'],
                'name_in_results' => 'Pacto Hikmic',
                'name_in_certificates' => 'Pacto Hikmic',
                'logo_url' => null,
            ]);
            $pacto->save();

            $conco = Group::where('slug', 'conco-majdic')->orWhere('code', 'CONCO')->first() ?? new Group;
            $conco->fill([
                'name' => 'Conco Majdic',
                'code' => 'CONCO',
                'slug' => 'conco-majdic',
                'color_hex' => '#f8e709',
                'manager_name' => 'SINAN SAQAFI VELLIMUTTAM',
                'assistant_managers' => ['MUSHARAF PONNANI', 'ZAINUL ABID VAVAD'],
                'name_in_results' => 'Conco Majdic',
                'name_in_certificates' => 'Conco Majdic',
                'logo_url' => null,
            ]);
            $conco->save();

            $unio = Group::where('slug', 'unio-hilmic')->orWhere('code', 'UNIO')->first() ?? new Group;
            $unio->fill([
                'name' => 'Unio Hilmic',
                'code' => 'UNIO',
                'slug' => 'unio-hilmic',
                'color_hex' => '#7f1518',
                'manager_name' => 'ANAS ADANY',
                'assistant_managers' => ['HASEEB VENNIYUR', 'THWAYYIB IRITTY'],
                'name_in_results' => 'Unio Hilmic',
                'name_in_certificates' => 'Unio Hilmic',
                'logo_url' => null,
            ]);
            $unio->save();

            $yugo = Group::where('slug', 'yugo-rushdic')->orWhere('code', 'YUGO')->first() ?? new Group;
            $yugo->fill([
                'name' => 'Yugo Rushdic',
                'code' => 'YUGO',
                'slug' => 'yugo-rushdic',
                'color_hex' => '#ad1e56',
                'manager_name' => 'JABIR SAQAFI',
                'assistant_managers' => ['SALMAN PAKKANA', 'JAISAL KUFA'],
                'name_in_results' => 'Yugo Rushdic',
                'name_in_certificates' => 'Yugo Rushdic',
                'logo_url' => null,
            ]);
            $yugo->save();

            // 3. Ensure Group Leader users match
            $leadersData = [
                ['group' => $lumo, 'name' => 'WARIS ADANY (Leader - LUMO FIKRIC)', 'email' => 'leader.lumo@quaf.fest'],
                ['group' => $pacto, 'name' => 'BASIL ADANY (Leader - PACTO HIKMIC)', 'email' => 'leader.pacto@quaf.fest'],
                ['group' => $conco, 'name' => 'SINAN SAQAFI VELLIMUTTAM (Leader - CONCO MAJDIC)', 'email' => 'leader.conco@quaf.fest'],
                ['group' => $unio, 'name' => 'ANAS ADANY (Leader - UNIO HILMIC)', 'email' => 'leader.unio@quaf.fest'],
                ['group' => $yugo, 'name' => 'JABIR SAQAFI (Leader - YUGO RUSHDIC)', 'email' => 'leader.yugo@quaf.fest'],
            ];

            foreach ($leadersData as $item) {
                $user = User::firstWhere('email', $item['email']);
                if (! $user && $item['group']->leader_id) {
                    $user = User::find($item['group']->leader_id);
                }
                if (! $user) {
                    $user = new User;
                }
                $user->name = $item['name'];
                $user->email = $item['email'];
                $user->role = 'group_leader';
                $user->is_active = true;
                if (! $user->password) {
                    $user->password = Hash::make('password');
                }
                $user->save();

                $item['group']->update(['leader_id' => $user->id]);
            }

            $this->info('Configured all 5 official Groups and Leaders.');

            // 4. Wipe all previous students
            Student::query()->delete();
            $this->info('Wiped all previous student records.');

            // 5. Read CSV and insert students
            $groupsByLetter = [
                'A' => $lumo,
                'B' => $pacto,
                'C' => $conco,
                'D' => $unio,
                'E' => $yugo,
            ];

            $zonesByName = [
                'A ZONE' => $aZone,
                'B ZONE' => $bZone,
                'C ZONE' => $cZone,
                'MIX ZONE' => $mixZone,
            ];

            $fp = fopen($csvPath, 'r');
            if (! $fp) {
                throw new \RuntimeException("Could not open CSV file: {$csvPath}");
            }

            $currentGroup = null;
            $studentsBatch = [];
            $totalCount = 0;
            $groupCounts = [
                'LUMO' => 0,
                'PACTO' => 0,
                'CONCO' => 0,
                'UNIO' => 0,
                'YUGO' => 0,
            ];

            while (($row = fgetcsv($fp)) !== false) {
                if (empty($row)) {
                    continue;
                }

                $firstCol = trim($row[0] ?? '');

                // Check for GROUP header
                if (preg_match('/^GROUP\s+([A-E])/i', $firstCol, $match)) {
                    $letter = strtoupper($match[1]);
                    $currentGroup = $groupsByLetter[$letter] ?? null;

                    continue;
                }

                // Check for student row with QF chest number
                if (preg_match('/^(QF\d{4})$/i', $firstCol, $match)) {
                    $chestNo = strtoupper($match[1]);
                    $targetGroup = $currentGroup;
                    if (! $targetGroup) {
                        $cNum = (int) substr($chestNo, 2);
                        if ($cNum >= 1000 && $cNum < 2000) {
                            $targetGroup = $lumo;
                        } elseif ($cNum >= 2000 && $cNum < 3000) {
                            $targetGroup = $pacto;
                        } elseif ($cNum >= 3000 && $cNum < 4000) {
                            $targetGroup = $conco;
                        } elseif ($cNum >= 4000 && $cNum < 5000) {
                            $targetGroup = $unio;
                        } elseif ($cNum >= 5000 && $cNum < 6000) {
                            $targetGroup = $yugo;
                        }
                    }

                    if (! $targetGroup) {
                        continue;
                    }

                    $rawName = $row[1] ?? '';
                    $rawClass = $row[2] ?? '';
                    $rawZone = $row[3] ?? '';

                    // Ensure valid UTF-8 encoding (handling Windows-1252 non-breaking spaces)
                    if (! mb_check_encoding($rawName, 'UTF-8')) {
                        $rawName = mb_convert_encoding($rawName, 'UTF-8', 'Windows-1252');
                    }
                    if (! mb_check_encoding($rawClass, 'UTF-8')) {
                        $rawClass = mb_convert_encoding($rawClass, 'UTF-8', 'Windows-1252');
                    }
                    if (! mb_check_encoding($rawZone, 'UTF-8')) {
                        $rawZone = mb_convert_encoding($rawZone, 'UTF-8', 'Windows-1252');
                    }

                    // Clean unicode whitespace and NBSP
                    $name = trim(preg_replace('/[\s\x{00a0}\x{200b}]+/u', ' ', $rawName));
                    $class = trim(preg_replace('/[\s\x{00a0}\x{200b}]+/u', ' ', $rawClass));
                    $zoneStr = strtoupper(trim(preg_replace('/[\s\x{00a0}\x{200b}]+/u', ' ', $rawZone)));

                    $zoneModel = $zonesByName[$zoneStr] ?? $aZone;

                    $studentsBatch[] = [
                        'student_id' => $chestNo,
                        'name' => $name,
                        'group_id' => $targetGroup->id,
                        'zone_id' => $zoneModel->id,
                        'category' => $zoneModel->name,
                        'class_level' => $class,
                        'gender' => 'Male',
                        'contact' => null,
                        'photo_url' => null,
                        'qr_token' => Str::random(40),
                        'points_cache' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $groupCounts[$targetGroup->code]++;
                    $totalCount++;

                    // Insert in chunks of 100
                    if (count($studentsBatch) >= 100) {
                        Student::insert($studentsBatch);
                        $studentsBatch = [];
                    }
                }
            }
            fclose($fp);

            if (! empty($studentsBatch)) {
                Student::insert($studentsBatch);
            }

            $this->info("Imported total {$totalCount} official students:");
            foreach ($groupCounts as $code => $count) {
                $grpName = match ($code) {
                    'LUMO' => 'Team A (Lumo Fikric)',
                    'PACTO' => 'Team B (Pacto Hikmic)',
                    'CONCO' => 'Team C (Conco Majdic)',
                    'UNIO' => 'Team D (Unio Hilmic)',
                    'YUGO' => 'Team E (Yugo Rushdic)',
                    default => $code,
                };
                $this->line(" - {$grpName}: {$count} students");
            }

            // 6. Sync programs zone_id
            Program::where('eligibility', 'A Zone')->update(['zone_id' => $aZone->id]);
            Program::where('eligibility', 'B Zone')->update(['zone_id' => $bZone->id]);
            Program::where('eligibility', 'C Zone')->update(['zone_id' => $cZone->id]);
            Program::where('eligibility', 'Mix Zone')->update(['zone_id' => $mixZone->id]);

            // 7. Recalculate Points Engine
            app(PointCalculationService::class)->recalculateAllPoints();
            $this->info('Points calculated and clean state initialized.');

            // 8. Pre-generate student user accounts
            $this->call('app:sync-student-accounts');
        });

        return Command::SUCCESS;
    }
}
