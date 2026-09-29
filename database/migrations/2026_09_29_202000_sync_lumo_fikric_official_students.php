<?php

use App\Models\Group;
use App\Models\Student;
use App\Models\Zone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('students') || ! Schema::hasTable('groups') || ! Schema::hasTable('zones')) {
            return;
        }

        if (Group::count() === 0 || Zone::count() === 0) {
            return;
        }

        // Find or reference LUMO group
        $lumo = Group::where('code', 'LUMO')->orWhere('slug', 'lumo-fikric')->first();
        $groupId = $lumo ? $lumo->id : 4;

        // Wipe students table to match the user's fresh one-by-one import
        Student::query()->delete();

        // Clear any orphaned transactional records
        if (Schema::hasTable('results')) {
            DB::table('results')->delete();
        }
        if (Schema::hasTable('score_sheets')) {
            DB::table('score_sheets')->delete();
        }
        if (Schema::hasTable('green_room_calls')) {
            DB::table('green_room_calls')->delete();
        }
        if (Schema::hasTable('program_entry_participants')) {
            DB::table('program_entry_participants')->delete();
        }
        if (Schema::hasTable('program_entries')) {
            DB::table('program_entries')->delete();
        }
        if (Schema::hasTable('points_transactions')) {
            DB::table('points_transactions')->delete();
        }

        $csvPath = base_path('database/data/LUMO_FIKRIC.csv');
        if (! file_exists($csvPath)) {
            return;
        }

        $allZones = Zone::all();
        $zoneMap = [
            'A ZONE' => $allZones->firstWhere('code', 'A_ZONE')?->id ?? 1,
            'B ZONE' => $allZones->firstWhere('code', 'B_ZONE')?->id ?? 2,
            'C ZONE' => $allZones->firstWhere('code', 'C_ZONE')?->id ?? 3,
            'MIX ZONE' => $allZones->firstWhere('code', 'MIX_ZONE')?->id ?? 4,
        ];
        $categoryMap = [
            1 => 'A Zone',
            2 => 'B Zone',
            3 => 'C Zone',
            4 => 'Mix Zone',
        ];

        $handle = fopen($csvPath, 'r');
        if (! $handle) {
            return;
        }

        $batch = [];
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if (empty($row) || empty($row[0])) {
                continue;
            }

            $chest = strtoupper(trim((string) $row[0]));
            if (! preg_match('/^QF\d+$/i', $chest)) {
                continue;
            }

            $name = trim(preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{00A0}]+/u', ' ', (string) ($row[1] ?? '')));
            $name = trim(preg_replace('/\s+/', ' ', $name));

            $class = trim(preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{00A0}]+/u', ' ', (string) ($row[2] ?? '')));
            $class = trim(preg_replace('/\s+/', ' ', $class));

            $zoneStr = strtoupper(trim((string) ($row[3] ?? '')));
            $zoneId = $zoneMap[$zoneStr] ?? 1;
            $category = $categoryMap[$zoneId] ?? 'A Zone';

            $batch[] = [
                'student_id' => $chest,
                'name' => $name,
                'class_level' => $class,
                'category' => $category,
                'zone_id' => $zoneId,
                'group_id' => $groupId,
                'gender' => 'Male',
                'qr_token' => Str::random(40),
                'points_cache' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 50) {
                Student::insert($batch);
                $batch = [];
            }
        }
        fclose($handle);

        if (! empty($batch)) {
            Student::insert($batch);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive rollback
    }
};
