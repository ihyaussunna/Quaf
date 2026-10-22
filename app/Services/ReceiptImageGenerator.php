<?php

namespace App\Services;

use App\Models\FundReceipt;
use App\Models\FundReceiptSetting;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptImageGenerator
{
    /**
     * Generate the receipt image using the official festival template.
     */
    public static function generate(FundReceipt $receipt, ?FundReceiptSetting $settings = null): ?\GdImage
    {
        $templatePath = public_path('images/receipt-template.jpg');
        if (! file_exists($templatePath)) {
            return null;
        }

        $img = @imagecreatefromjpeg($templatePath);
        if (! $img) {
            return null;
        }

        $w = imagesx($img);
        $h = imagesy($img);

        // Fonts
        $rockwellBold = public_path('fonts/Rockwell-Bold.otf');
        if (! file_exists($rockwellBold)) {
            $rockwellBold = public_path('fonts/arialbd.ttf');
        }

        $soraBold = public_path('fonts/Sora-Bold.ttf');
        if (! file_exists($soraBold)) {
            $soraBold = public_path('fonts/Sora-ExtraBold.ttf');
        }
        if (! file_exists($soraBold)) {
            $soraBold = $rockwellBold;
        }

        $rockwellRegular = public_path('fonts/Rockwell-Regular.otf');
        if (! file_exists($rockwellRegular)) {
            $rockwellRegular = $rockwellBold;
        }

        $settings ??= FundReceiptSetting::current();

        // 1. Receipt Number
        $rcpNumber = (string) $receipt->receipt_number;
        $rcpSize = (int) ($settings->rcp_font_size ?? 20);
        $rcpColor = self::allocateHexColor($img, $settings->rcp_color ?? '#5c5a5b', [92, 90, 91]);
        $rcpTargetX = (int) ($settings->rcp_x ?? 890);
        $rcpTargetY = (int) ($settings->rcp_y ?? 175);
        $rcpAlign = (string) ($settings->rcp_align ?? 'left');

        $rcpX = $rcpTargetX;
        if ($rcpAlign === 'center' || $rcpAlign === 'right') {
            $bbox = @imagettfbbox($rcpSize, 0, $rockwellBold, $rcpNumber);
            if ($bbox) {
                $textW = abs($bbox[4] - $bbox[0]);
                $rcpX = $rcpAlign === 'center' ? (int) ($rcpTargetX - ($textW / 2)) : (int) ($rcpTargetX - $textW);
            }
        }
        @imagettftext($img, $rcpSize, 0, $rcpX, $rcpTargetY, $rcpColor, $rockwellBold, $rcpNumber);

        // 2. Date
        $dateText = $receipt->created_at ? $receipt->created_at->format('d/m/Y') : date('d/m/Y');
        $dateSize = (int) ($settings->date_font_size ?? 20);
        $dateColor = self::allocateHexColor($img, $settings->date_color ?? '#5c5a5b', [92, 90, 91]);
        $dateTargetX = (int) ($settings->date_x ?? 830);
        $dateTargetY = (int) ($settings->date_y ?? 230);
        $dateAlign = (string) ($settings->date_align ?? 'left');

        $dateX = $dateTargetX;
        if ($dateAlign === 'center' || $dateAlign === 'right') {
            $bbox = @imagettfbbox($dateSize, 0, $rockwellBold, $dateText);
            if ($bbox) {
                $textW = abs($bbox[4] - $bbox[0]);
                $dateX = $dateAlign === 'center' ? (int) ($dateTargetX - ($textW / 2)) : (int) ($dateTargetX - $textW);
            }
        }
        @imagettftext($img, $dateSize, 0, $dateX, $dateTargetY, $dateColor, $rockwellBold, $dateText);

        // 3. Donor Name
        $donorName = strtoupper((string) $receipt->donor_name);
        $donorBaseSize = (int) ($settings->donor_font_size ?? 40);
        $donorColor = self::allocateHexColor($img, $settings->donor_color ?? '#ffffff', [255, 255, 255]);
        $donorTargetX = (int) ($settings->donor_x ?? 563);
        $donorTargetY = (int) ($settings->donor_y ?? 610);
        $donorAlign = (string) ($settings->donor_align ?? 'center');

        $donorSize = $donorBaseSize;
        $maxNameWidth = $w - 220; // safe margin

        while ($donorSize > 14) {
            $bbox = @imagettfbbox($donorSize, 0, $soraBold, $donorName);
            if (! $bbox) {
                break;
            }
            $textW = abs($bbox[4] - $bbox[0]);
            if ($textW <= $maxNameWidth) {
                break;
            }
            $donorSize -= 2;
        }

        $bbox = @imagettfbbox($donorSize, 0, $soraBold, $donorName);
        $donorX = $donorTargetX;
        if ($bbox) {
            $textW = abs($bbox[4] - $bbox[0]);
            if ($donorAlign === 'center') {
                $donorX = (int) ($donorTargetX - ($textW / 2));
            } elseif ($donorAlign === 'right') {
                $donorX = (int) ($donorTargetX - $textW);
            }
        }
        @imagettftext($img, $donorSize, 0, $donorX, $donorTargetY, $donorColor, $soraBold, $donorName);

        // 4. Amount
        $amountFormatted = number_format($receipt->amount, 0, '.', ',');
        $amountText = "Rs.{$amountFormatted}/-";
        $amountBaseSize = (int) ($settings->amount_font_size ?? 40);
        $amountColor = self::allocateHexColor($img, $settings->amount_color ?? '#000000', [0, 0, 0]);
        $amountTargetX = (int) ($settings->amount_x ?? 563);
        $amountTargetY = (int) ($settings->amount_y ?? 755);
        $amountAlign = (string) ($settings->amount_align ?? 'center');

        $amountSize = $amountBaseSize;
        $maxAmountWidth = 420;

        while ($amountSize > 16) {
            $bbox = @imagettfbbox($amountSize, 0, $rockwellBold, $amountText);
            if (! $bbox) {
                break;
            }
            $textW = abs($bbox[4] - $bbox[0]);
            if ($textW <= $maxAmountWidth) {
                break;
            }
            $amountSize -= 2;
        }

        $bbox = @imagettfbbox($amountSize, 0, $rockwellBold, $amountText);
        $amountX = $amountTargetX;
        if ($bbox) {
            $textW = abs($bbox[4] - $bbox[0]);
            if ($amountAlign === 'center') {
                $amountX = (int) ($amountTargetX - ($textW / 2));
            } elseif ($amountAlign === 'right') {
                $amountX = (int) ($amountTargetX - $textW);
            }
        }
        @imagettftext($img, $amountSize, 0, $amountX, $amountTargetY, $amountColor, $rockwellBold, $amountText);

        return $img;
    }

    /**
     * Safely allocate a GD color from a HEX string (e.g. #5c5a5b).
     */
    public static function allocateHexColor($img, ?string $hex, array $fallbackRgb = [0, 0, 0]): int
    {
        if (! $hex) {
            return imagecolorallocate($img, $fallbackRgb[0], $fallbackRgb[1], $fallbackRgb[2]);
        }

        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            return imagecolorallocate($img, $fallbackRgb[0], $fallbackRgb[1], $fallbackRgb[2]);
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return imagecolorallocate($img, $r, $g, $b);
    }

    /**
     * Return a StreamedResponse for downloading or previewing the generated receipt image.
     */
    public static function streamResponse(FundReceipt $receipt, bool $download = false, ?FundReceiptSetting $settings = null): StreamedResponse
    {
        $settings ??= FundReceiptSetting::current();
        $filename = "QUAF-Receipt-{$receipt->receipt_number}.jpg";

        return response()->stream(function () use ($receipt, $settings) {
            $img = self::generate($receipt, $settings);
            if ($img) {
                imagejpeg($img, null, 95);
                imagedestroy($img);
            }
        }, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => ($download ? 'attachment' : 'inline')."; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, private',
        ]);
    }
}
