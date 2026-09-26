<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use HasFactory;

    public const A_ZONE = 'A_ZONE';

    public const B_ZONE = 'B_ZONE';

    public const C_ZONE = 'C_ZONE';

    public const MIX_ZONE = 'MIX_ZONE';

    protected $fillable = [
        'code',
        'name',
        'slug',
        'sub_text',
        'classes',
        'color_hex',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
        ];
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function isMixZone(): bool
    {
        return $this->code === self::MIX_ZONE || strtolower($this->name) === 'mix zone';
    }

    /**
     * Determine Zone name from class name based on official rules:
     * - 4 in class name (NF4, UH4, S4, ID4, UT4, L4, TQS) -> A Zone
     * - 3 in class name (NF3, ID3, UH3, UT3, S3, L3) -> B Zone
     * - 2 or 1 in class name (U1, U2, L2, S1, S2) -> C Zone
     * - All classes included -> Mix Zone
     */
    public static function determineZoneNameFromClass(?string $classLevel): ?string
    {
        if (empty($classLevel)) {
            return null;
        }

        $c = strtoupper(trim($classLevel));

        if (str_contains($c, '4') || str_contains($c, 'TQS') || str_contains($c, 'RABIA') || str_contains($c, 'THAQASSUS')) {
            return 'A Zone';
        }

        if (str_contains($c, '3') || str_contains($c, 'SALIS')) {
            return 'B Zone';
        }

        if (str_contains($c, '2') || str_contains($c, '1') || str_contains($c, 'ULA') || str_contains($c, 'SANI')) {
            return 'C Zone';
        }

        return null;
    }

    /**
     * Get Zone model instance for a given class level.
     */
    public static function getZoneForClass(?string $classLevel): ?Zone
    {
        $zoneName = self::determineZoneNameFromClass($classLevel);
        if (! $zoneName) {
            return null;
        }

        return self::where('name', $zoneName)->first()
            ?? self::where('code', match ($zoneName) {
                'A Zone' => self::A_ZONE,
                'B Zone' => self::B_ZONE,
                'C Zone' => self::C_ZONE,
                default => self::MIX_ZONE,
            })->first();
    }

    /**
     * Check if a specific class is eligible for this zone.
     */
    public function isClassEligible(?string $classLevel): bool
    {
        if ($this->isMixZone()) {
            return true; // Mix Zone includes all classes
        }

        if (empty($classLevel)) {
            return false;
        }

        $expectedZone = self::determineZoneNameFromClass($classLevel);

        return $expectedZone === $this->name;
    }
}
