<?php

namespace App\Services;

class PointsCalculator extends PointCalculationService
{
    /**
     * Recalculate all points across groups and students.
     */
    public static function recalculateAll(): void
    {
        app(PointCalculationService::class)->recalculateAllPoints();
    }
}
