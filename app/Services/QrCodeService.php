<?php

namespace App\Services;

class QrCodeService
{
    /**
     * Generate an SVG QR code or URL for verification.
     */
    public static function svg(string $data, int $size = 200, string $color = '10b981', string $bgColor = '0a0b0e'): string
    {
        $encoded = urlencode($data);

        // High quality SVG QR code via standard fast vector generator URL with fallback
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encoded}&color=d4af37&bgcolor=0a0b0e&format=svg";
    }

    /**
     * Generate an HTML image element for a QR code.
     */
    public static function image(string $data, int $size = 180, string $alt = 'QR Code'): string
    {
        $url = self::svg($data, $size);

        return "<img src=\"{$url}\" alt=\"{$alt}\" width=\"{$size}\" height=\"{$size}\" class=\"rounded-lg border border-gold-500/20 shadow-xl bg-midnight-950 p-2\" loading=\"lazy\" />";
    }
}
