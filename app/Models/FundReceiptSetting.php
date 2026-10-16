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
        'rcp_x',
        'rcp_y',
        'rcp_font_size',
        'rcp_color',
        'rcp_align',
        'date_x',
        'date_y',
        'date_font_size',
        'date_color',
        'date_align',
        'donor_x',
        'donor_y',
        'donor_font_size',
        'donor_color',
        'donor_align',
        'amount_x',
        'amount_y',
        'amount_font_size',
        'amount_color',
        'amount_align',
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
            'rcp_x' => 'integer',
            'rcp_y' => 'integer',
            'rcp_font_size' => 'integer',
            'date_x' => 'integer',
            'date_y' => 'integer',
            'date_font_size' => 'integer',
            'donor_x' => 'integer',
            'donor_y' => 'integer',
            'donor_font_size' => 'integer',
            'amount_x' => 'integer',
            'amount_y' => 'integer',
            'amount_font_size' => 'integer',
        ];
    }

    public function getRcpXAttribute($value): int
    {
        return $value !== null ? (int) $value : 890;
    }

    public function getRcpYAttribute($value): int
    {
        return $value !== null ? (int) $value : 175;
    }

    public function getRcpFontSizeAttribute($value): int
    {
        return $value !== null ? (int) $value : 20;
    }

    public function getRcpColorAttribute($value): string
    {
        return $value ?: '#5c5a5b';
    }

    public function getRcpAlignAttribute($value): string
    {
        return $value ?: 'left';
    }

    public function getDateXAttribute($value): int
    {
        return $value !== null ? (int) $value : 830;
    }

    public function getDateYAttribute($value): int
    {
        return $value !== null ? (int) $value : 230;
    }

    public function getDateFontSizeAttribute($value): int
    {
        return $value !== null ? (int) $value : 20;
    }

    public function getDateColorAttribute($value): string
    {
        return $value ?: '#5c5a5b';
    }

    public function getDateAlignAttribute($value): string
    {
        return $value ?: 'left';
    }

    public function getDonorXAttribute($value): int
    {
        return $value !== null ? (int) $value : 563;
    }

    public function getDonorYAttribute($value): int
    {
        return $value !== null ? (int) $value : 610;
    }

    public function getDonorFontSizeAttribute($value): int
    {
        return $value !== null ? (int) $value : 40;
    }

    public function getDonorColorAttribute($value): string
    {
        return $value ?: '#ffffff';
    }

    public function getDonorAlignAttribute($value): string
    {
        return $value ?: 'center';
    }

    public function getAmountXAttribute($value): int
    {
        return $value !== null ? (int) $value : 563;
    }

    public function getAmountYAttribute($value): int
    {
        return $value !== null ? (int) $value : 755;
    }

    public function getAmountFontSizeAttribute($value): int
    {
        return $value !== null ? (int) $value : 40;
    }

    public function getAmountColorAttribute($value): string
    {
        return $value ?: '#000000';
    }

    public function getAmountAlignAttribute($value): string
    {
        return $value ?: 'center';
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
            'rcp_x' => 890,
            'rcp_y' => 175,
            'rcp_font_size' => 20,
            'rcp_color' => '#5c5a5b',
            'rcp_align' => 'left',
            'date_x' => 830,
            'date_y' => 230,
            'date_font_size' => 20,
            'date_color' => '#5c5a5b',
            'date_align' => 'left',
            'donor_x' => 563,
            'donor_y' => 610,
            'donor_font_size' => 40,
            'donor_color' => '#ffffff',
            'donor_align' => 'center',
            'amount_x' => 563,
            'amount_y' => 755,
            'amount_font_size' => 40,
            'amount_color' => '#000000',
            'amount_align' => 'center',
        ]);
    }
}
