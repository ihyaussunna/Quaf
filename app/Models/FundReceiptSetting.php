<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundReceiptSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'header_title',
        'organization_name',
        'subtitle',
        'text_alignment',
        'font_size',
        'padding_size',
        'primary_color',
        'accent_color',
        'text_color',
        'bg_color',
        'show_logo',
        'logo_path',
        'footer_text',
        'authorized_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'show_logo' => 'boolean',
        ];
    }

    /**
     * Retrieve the active receipt settings instance.
     */
    public static function current(): self
    {
        FundReceipt::ensureSchema();

        return static::firstOrCreate([], [
            'header_title' => 'QUAF SEASON 09',
            'organization_name' => 'JAMIA IHYA-US-SUNNA',
            'subtitle' => 'Grand Festival Fund Collection Voucher',
            'text_alignment' => 'center',
            'font_size' => 'normal',
            'padding_size' => 'normal',
            'primary_color' => '#be1e2d',
            'accent_color' => '#d97706',
            'text_color' => '#0f172a',
            'bg_color' => '#ffffff',
            'show_logo' => true,
            'footer_text' => 'Thank you for your generous contribution towards QUAF 9.0!',
            'authorized_by' => 'QUAF Finance Samithi',
        ]);
    }
}
