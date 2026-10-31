<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FestivalSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Self-heal schema so column 'value' can hold large JSON/base64 without SQLSTATE[22001]
     */
    public static function ensureSchema(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        try {
            if (Schema::hasTable('festival_settings')) {
                $driver = Schema::getConnection()->getDriverName();
                if (in_array($driver, ['mysql', 'mariadb'])) {
                    DB::statement('ALTER TABLE festival_settings MODIFY value LONGTEXT NULL');
                }
            }
            $checked = true;
        } catch (\Throwable $e) {
            // Ignore if permissions or sqlite
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, ?string $value): void
    {
        self::ensureSchema();

        try {
            static::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), '1406') || str_contains($e->getMessage(), 'Data too long')) {
                try {
                    $driver = Schema::getConnection()->getDriverName();
                    if (in_array($driver, ['mysql', 'mariadb'])) {
                        DB::statement('ALTER TABLE festival_settings MODIFY value LONGTEXT NULL');
                    }
                    static::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value]
                    );

                    return;
                } catch (\Throwable $inner) {
                    throw $e;
                }
            }
            throw $e;
        }
    }
}
