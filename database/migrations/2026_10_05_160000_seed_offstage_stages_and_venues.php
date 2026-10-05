<?php

use App\Models\Stage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $offstageStages = [
            [
                'name' => 'Stage 05',
                'code' => 'STG-05',
                'location' => 'NF3',
                'capacity' => 150,
                'status' => 'active',
            ],
            [
                'name' => 'Stage 06',
                'code' => 'STG-06',
                'location' => 'ID3',
                'capacity' => 150,
                'status' => 'active',
            ],
            [
                'name' => 'Stage 07',
                'code' => 'STG-07',
                'location' => 'U2',
                'capacity' => 150,
                'status' => 'active',
            ],
            [
                'name' => 'Stage 08',
                'code' => 'STG-08',
                'location' => 'S3',
                'capacity' => 150,
                'status' => 'active',
            ],
        ];

        foreach ($offstageStages as $stg) {
            Stage::firstOrCreate(
                ['code' => $stg['code']],
                $stg
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Stage::whereIn('code', ['STG-05', 'STG-06', 'STG-07', 'STG-08'])->delete();
    }
};
