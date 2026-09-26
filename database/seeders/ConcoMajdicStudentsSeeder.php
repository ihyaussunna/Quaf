<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Student;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ConcoMajdicStudentsSeeder extends Seeder
{
    /**
     * Seed all 236 participants of Conco Majdic.
     */
    public function run(): void
    {
        $concoGroup = Group::where('code', 'CONCO')
            ->orWhere('name', 'like', '%Conco%')
            ->first();

        if (! $concoGroup) {
            $concoGroup = Group::firstOrCreate(
                ['code' => 'CONCO'],
                [
                    'name' => 'Conco Majdic',
                    'slug' => 'conco-majdic',
                    'color_hex' => '#f8e709',
                    'manager_name' => 'SINAN SAQAFI VELLIMUTTAM',
                    'assistant_managers' => ['MUSHARAF PONNANI', 'ZAINUL ABID VAVAD'],
                    'name_in_results' => 'Conco Majdic',
                    'name_in_certificates' => 'Conco Majdic',
                ]
            );
        }

        $allZones = Zone::all();
        $zoneMap = [
            'A ZONE' => $allZones->firstWhere('name', 'A Zone') ?? $allZones->firstWhere('code', 'A_ZONE'),
            'B ZONE' => $allZones->firstWhere('name', 'B Zone') ?? $allZones->firstWhere('code', 'B_ZONE'),
            'C ZONE' => $allZones->firstWhere('name', 'C Zone') ?? $allZones->firstWhere('code', 'C_ZONE'),
            'MIX ZONE' => $allZones->firstWhere('name', 'Mix Zone') ?? $allZones->firstWhere('code', 'MIX_ZONE'),
        ];

        $tsvPath = base_path('database/data/conco_majdic_students.tsv');
        if (! file_exists($tsvPath)) {
            return;
        }

        $lines = file($tsvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $cols = array_map('trim', explode("\t", $line));
            if (count($cols) < 4) {
                continue;
            }

            $chest = $cols[0];
            $name = $cols[1];
            $class = $cols[2];
            $zoneRaw = strtoupper($cols[3]);

            $zone = $zoneMap[$zoneRaw] ?? $allZones->firstWhere('name', 'A Zone');
            $category = $zone ? $zone->name : 'A Zone';
            $zoneId = $zone ? $zone->id : 1;

            Student::updateOrCreate(
                ['student_id' => $chest],
                [
                    'name' => $name,
                    'group_id' => $concoGroup->id,
                    'zone_id' => $zoneId,
                    'category' => $category,
                    'class_level' => $class,
                    'gender' => 'Male',
                    'qr_token' => Str::random(40),
                ]
            );
        }
    }
}
