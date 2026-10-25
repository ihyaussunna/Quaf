<?php

use App\Services\PointCalculationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('points:recalculate', function (PointCalculationService $pointService) {
    $this->info('Recalculating all group and candidate points based on Rule 11...');
    $pointService->recalculateAllPoints();
    $this->info('Points successfully recalculated.');
})->purpose('Recalculate all festival points based on official rules');
