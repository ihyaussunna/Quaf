<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ProgrammeListSeeder extends Seeder
{
    /**
     * Run the database seeds for the 144 official programs.
     */
    public function run(): void
    {
        Artisan::call('app:sync-official-programs');
    }
}
