<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\Schedule;
use App\Models\Stage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OffstageScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stages = [
            'STG-05' => Stage::where('code', 'STG-05')->first() ?? Stage::create(['name' => 'Stage 05', 'code' => 'STG-05', 'location' => 'NF3', 'status' => 'active']),
            'STG-06' => Stage::where('code', 'STG-06')->first() ?? Stage::create(['name' => 'Stage 06', 'code' => 'STG-06', 'location' => 'ID3', 'status' => 'active']),
            'STG-07' => Stage::where('code', 'STG-07')->first() ?? Stage::create(['name' => 'Stage 07', 'code' => 'STG-07', 'location' => 'U2', 'status' => 'active']),
            'STG-08' => Stage::where('code', 'STG-08')->first() ?? Stage::create(['name' => 'Stage 08', 'code' => 'STG-08', 'location' => 'S3', 'status' => 'active']),
        ];

        $scheduleData = [
            // ==========================================
            // October 06, 2026 (Tuesday)
            // ==========================================
            // 04:40 PM
            [
                'program_code' => 'Q9-117', // Malayalam Poem Writing (A Zone)
                'stage_code' => 'STG-05',
                'start' => '2026-10-06 16:40:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-144', // Malayalam Poem Writing (B Zone)
                'stage_code' => 'STG-06',
                'start' => '2026-10-06 16:40:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-165', // Malayalam Poem Writing (C Zone)
                'stage_code' => 'STG-07',
                'start' => '2026-10-06 16:40:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-220', // E-Poster (Mix Zone)
                'stage_code' => 'STG-08',
                'start' => '2026-10-06 16:40:00',
                'duration' => 30,
            ],

            // 05:10 PM
            [
                'program_code' => 'Q9-116', // English Poem Writing (A Zone)
                'stage_code' => 'STG-05',
                'start' => '2026-10-06 17:10:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-143', // English Poem Writing (B Zone)
                'stage_code' => 'STG-06',
                'start' => '2026-10-06 17:10:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-166', // English Poem Writing (C Zone)
                'stage_code' => 'STG-07',
                'start' => '2026-10-06 17:10:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-229', // Content Writing (Mix Zone)
                'stage_code' => 'STG-08',
                'start' => '2026-10-06 17:10:00',
                'duration' => 30,
            ],

            // 09:15 PM
            [
                'program_code' => 'Q9-119', // Malayalam Essay Writing (A Zone)
                'stage_code' => 'STG-05',
                'start' => '2026-10-06 21:15:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-146', // Malayalam Essay Writing (B Zone)
                'stage_code' => 'STG-06',
                'start' => '2026-10-06 21:15:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-168', // Malayalam Essay Writing (C Zone)
                'stage_code' => 'STG-07',
                'start' => '2026-10-06 21:15:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-207', // Madh Song Writing (Mix Zone)
                'stage_code' => 'STG-08',
                'start' => '2026-10-06 21:15:00',
                'duration' => 30,
            ],

            // 09:45 PM
            [
                'program_code' => 'Q9-136', // Tashreeh (A/B Zone)
                'stage_code' => 'STG-05',
                'start' => '2026-10-06 21:45:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-142', // Arabic Poem Writing (B Zone)
                'stage_code' => 'STG-06',
                'start' => '2026-10-06 21:45:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-164', // Arabic Poem Writing (C Zone)
                'stage_code' => 'STG-07',
                'start' => '2026-10-06 21:45:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-211', // Feature Writing (Mix Zone)
                'stage_code' => 'STG-08',
                'start' => '2026-10-06 21:45:00',
                'duration' => 30,
            ],

            // 10:25 PM
            [
                'program_code' => 'Q9-115', // Arabic Haiku (A Zone)
                'stage_code' => 'STG-05',
                'start' => '2026-10-06 22:25:00',
                'duration' => 20,
            ],

            // ==========================================
            // October 07, 2026 (Wednesday)
            // ==========================================
            // 04:40 PM
            [
                'program_code' => 'Q9-113', // Malayalam Story Writing (A Zone)
                'stage_code' => 'STG-05',
                'start' => '2026-10-07 16:40:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-148', // Revolutionary Song Writing (B Zone)
                'stage_code' => 'STG-06',
                'start' => '2026-10-07 16:40:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-163', // Malayalam Story Writing (C Zone)
                'stage_code' => 'STG-07',
                'start' => '2026-10-07 16:40:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-205', // Urdu Story Writing (Mix Zone)
                'stage_code' => 'STG-08',
                'start' => '2026-10-07 16:40:00',
                'duration' => 30,
            ],

            // 05:10 PM
            [
                'program_code' => 'Q9-125', // Social Text Malayalam (A Zone)
                'stage_code' => 'STG-05',
                'start' => '2026-10-07 17:10:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-151', // Social Text Malayalam (B Zone)
                'stage_code' => 'STG-06',
                'start' => '2026-10-07 17:10:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-173', // Social Text Malayalam (C Zone)
                'stage_code' => 'STG-07',
                'start' => '2026-10-07 17:10:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-206', // Urdu Essay Writing (Mix Zone)
                'stage_code' => 'STG-08',
                'start' => '2026-10-07 17:10:00',
                'duration' => 30,
            ],

            // 09:15 PM
            [
                'program_code' => 'Q9-114', // English Story Writing (A Zone)
                'stage_code' => 'STG-05',
                'start' => '2026-10-07 21:15:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-140', // English Story Writing (B Zone)
                'stage_code' => 'STG-06',
                'start' => '2026-10-07 21:15:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-162', // English Story Writing (C Zone)
                'stage_code' => 'STG-07',
                'start' => '2026-10-07 21:15:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-209', // Theme Song Writing (Mix Zone)
                'stage_code' => 'STG-08',
                'start' => '2026-10-07 21:15:00',
                'duration' => 30,
            ],

            // 09:45 PM
            [
                'program_code' => 'Q9-112', // Arabic Story Writing (A Zone)
                'stage_code' => 'STG-05',
                'start' => '2026-10-07 21:45:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-139', // Arabic Story Writing (B Zone)
                'stage_code' => 'STG-06',
                'start' => '2026-10-07 21:45:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-161', // Arabic Story Writing (C Zone)
                'stage_code' => 'STG-07',
                'start' => '2026-10-07 21:45:00',
                'duration' => 30,
            ],
            [
                'program_code' => 'Q9-212', // AI Poem (Mix Zone)
                'stage_code' => 'STG-08',
                'start' => '2026-10-07 21:45:00',
                'duration' => 30,
            ],

            // 10:25 PM
            [
                'program_code' => 'Q9-141', // Micro Fiction Malayalam (B Zone)
                'stage_code' => 'STG-06',
                'start' => '2026-10-07 22:25:00',
                'duration' => 20,
            ],
            [
                'program_code' => 'Q9-170', // Slogan Writing (C Zone)
                'stage_code' => 'STG-07',
                'start' => '2026-10-07 22:25:00',
                'duration' => 20,
            ],
            [
                'program_code' => 'Q9-208', // Social Text English (Mix Zone)
                'stage_code' => 'STG-08',
                'start' => '2026-10-07 22:25:00',
                'duration' => 30,
            ],
        ];

        foreach ($scheduleData as $item) {
            $program = Program::where('code', $item['program_code'])->first();
            $stage = $stages[$item['stage_code']] ?? null;

            if ($program && $stage) {
                $start = Carbon::parse($item['start']);
                $end = (clone $start)->addMinutes($item['duration']);

                Schedule::updateOrCreate(
                    [
                        'program_id' => $program->id,
                    ],
                    [
                        'stage_id' => $stage->id,
                        'start_time' => $start,
                        'end_time' => $end,
                        'status' => 'scheduled',
                        'conflict_notes' => null,
                    ]
                );

                $program->update([
                    'stage_id' => $stage->id,
                    'scheduled_time' => $start,
                    'duration_minutes' => $item['duration'],
                ]);
            }
        }
    }
}
