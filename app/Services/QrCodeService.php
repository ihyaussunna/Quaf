<?php

namespace App\Services;

class QrCodeService
{
    /**
     * Generate a QR Code URL (black on white standard high-contrast matrix).
     */
    public static function url(string $data, int $size = 200, string $color = '000000', string $bgColor = 'ffffff'): string
    {
        $encoded = urlencode($data);

        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encoded}&color={$color}&bgcolor={$bgColor}&margin=0";
    }

    /**
     * Generate an HTML/SVG image element for rendering a QR code in views and badges.
     */
    public static function svg(string $data, int $size = 200, string $color = '000000', string $bgColor = 'ffffff'): string
    {
        return self::render($data, $size, $color, $bgColor);
    }

    /**
     * Render an image tag for a QR code.
     */
    public static function render(string $data, int $size = 200, string $color = '000000', string $bgColor = 'ffffff'): string
    {
        $url = self::url($data, $size, $color, $bgColor);

        return "<img src=\"{$url}\" alt=\"QR Code\" width=\"{$size}\" height=\"{$size}\" class=\"w-full h-full object-contain\" loading=\"eager\" />";
    }

    /**
     * Generate an HTML image element for a QR code with custom styling.
     */
    public static function image(string $data, int $size = 180, string $alt = 'QR Code'): string
    {
        $url = self::url($data, $size);

        return "<img src=\"{$url}\" alt=\"{$alt}\" width=\"{$size}\" height=\"{$size}\" class=\"rounded-lg border border-slate-200 shadow-sm bg-white p-2\" loading=\"lazy\" />";
    }
}
