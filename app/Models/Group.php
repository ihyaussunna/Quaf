<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    public const PACTO = 'PACTO';

    public const YUGO = 'YUGO';

    public const CONCO = 'CONCO';

    public const LUMO = 'LUMO';

    public const UNIO = 'UNIO';

    public const GROUPS = [
        'PACTO_HIKMIC' => [
            'name' => 'Pacto Hikmic',
            'code' => 'PACTO',
            'color_hex' => '#2E3192',
        ],
        'YUGO_RUSHDIC' => [
            'name' => 'Yugo Rushdic',
            'code' => 'YUGO',
            'color_hex' => '#AD1E56',
        ],
        'CONCO_MAJDIC' => [
            'name' => 'Conco Majdic',
            'code' => 'CONCO',
            'color_hex' => '#F8E709',
        ],
        'LUMO_FIKRIC' => [
            'name' => 'Lumo Fikric',
            'code' => 'LUMO',
            'color_hex' => '#56286B',
        ],
        'UNIO_HILMIC' => [
            'name' => 'Unio Hilmic',
            'code' => 'UNIO',
            'color_hex' => '#7F1518',
        ],
    ];

    public static function getColorForCode(?string $code): string
    {
        $colors = [
            'PACTO' => '#2E3192',
            'YUGO' => '#AD1E56',
            'CONCO' => '#F8E709',
            'LUMO' => '#56286B',
            'UNIO' => '#7F1518',
        ];

        return $colors[strtoupper(trim((string) $code))] ?? '#2E3192';
    }

    protected $fillable = [
        'name',
        'code',
        'slug',
        'logo_url',
        'color_hex',
        'leader_id',
        'manager_name',
        'manager_contact',
        'name_in_results',
        'name_in_certificates',
        'admin_username',
        'admin_password',
        'assistant_managers',
        'points_cache',
        'rank_cache',
    ];

    protected function casts(): array
    {
        return [
            'assistant_managers' => 'array',
            'points_cache' => 'integer',
            'rank_cache' => 'integer',
        ];
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ProgramEntry::class);
    }

    public function pointsTransactions(): HasMany
    {
        return $this->hasMany(PointsTransaction::class);
    }

    public function galleryItems(): HasMany
    {
        return $this->hasMany(GalleryItem::class);
    }
}
