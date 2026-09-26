<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create Zones Table
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // A_ZONE, B_ZONE, C_ZONE, MIX_ZONE
            $table->string('name', 50)->unique(); // A Zone, B Zone, C Zone, Mix Zone
            $table->string('slug', 50)->unique();
            $table->string('sub_text')->nullable();
            $table->string('classes')->nullable();
            $table->string('color_hex', 10)->default('#be1e2d');
            $table->unsignedInteger('display_order')->default(1);
            $table->timestamps();
        });

        // 2. Populate the 4 Official Master Zones
        $zones = [
            [
                'code' => 'A_ZONE',
                'name' => 'A Zone',
                'slug' => 'a-zone',
                'sub_text' => 'റാബിഅ, തഖസ്സുസ്',
                'classes' => 'TQS, S4',
                'color_hex' => '#be1e2d',
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'B_ZONE',
                'name' => 'B Zone',
                'slug' => 'b-zone',
                'sub_text' => 'സാലിസ',
                'classes' => 'S3',
                'color_hex' => '#f3bd2e',
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'C_ZONE',
                'name' => 'C Zone',
                'slug' => 'c-zone',
                'sub_text' => 'ഊല, സാനി',
                'classes' => 'S1, S2',
                'color_hex' => '#005c94',
                'display_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'MIX_ZONE',
                'name' => 'Mix Zone',
                'slug' => 'mix-zone',
                'sub_text' => 'എല്ലാ സോണുകൾക്കും',
                'classes' => 'Open Category',
                'color_hex' => '#009444',
                'display_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('zones')->insert($zones);

        // 3. Add zone_id to programs
        Schema::table('programs', function (Blueprint $table) {
            if (! Schema::hasColumn('programs', 'zone_id')) {
                $table->foreignId('zone_id')->nullable()->after('category_id')->constrained('zones')->nullOnDelete();
            }
        });

        // 4. Add zone_id to students
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'zone_id')) {
                $table->foreignId('zone_id')->nullable()->after('group_id')->constrained('zones')->nullOnDelete();
            }
        });

        // 5. Backfill existing records
        $zoneMap = DB::table('zones')->pluck('id', 'name')->toArray();
        $zoneCodeMap = [
            'A Zone' => $zoneMap['A Zone'] ?? 1,
            'B Zone' => $zoneMap['B Zone'] ?? 2,
            'C Zone' => $zoneMap['C Zone'] ?? 3,
            'Mix Zone' => $zoneMap['Mix Zone'] ?? 4,
            'A ZONE' => $zoneMap['A Zone'] ?? 1,
            'B ZONE' => $zoneMap['B Zone'] ?? 2,
            'C ZONE' => $zoneMap['C Zone'] ?? 3,
            'MIX ZONE' => $zoneMap['Mix Zone'] ?? 4,
        ];

        foreach ($zoneCodeMap as $name => $id) {
            DB::table('programs')->where('eligibility', $name)->update(['zone_id' => $id]);
            DB::table('students')->where('category', $name)->update(['zone_id' => $id]);
        }

        // Default any remaining nulls to A Zone (1)
        DB::table('programs')->whereNull('zone_id')->update(['zone_id' => $zoneMap['A Zone'] ?? 1]);
        DB::table('students')->whereNull('zone_id')->update(['zone_id' => $zoneMap['A Zone'] ?? 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropColumn('zone_id');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropColumn('zone_id');
        });

        Schema::dropIfExists('zones');
    }
};
