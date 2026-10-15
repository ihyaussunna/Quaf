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

        $rockwellRegular = public_path('fonts/Rockwell-Regular.otf');
        if (! file_exists($rockwellRegular)) {
            $rockwellRegular = $rockwellBold;
        }

        // Colors
        // Exact sampled color of "Receipt No :" and "Date :" from template (#5c5a5b -> R:92, G:90, B:91)
        $labelColor = imagecolorallocate($img, 92, 90, 91);
        $whiteColor = imagecolorallocate($img, 255, 255, 255);
        $blackColor = imagecolorallocate($img, 0, 0, 0);

        // 1. Receipt Number (Right next to "Receipt No :")
        $rcpNumber = $receipt->receipt_number;
        @imagettftext($img, 20, 0, 890, 175, $labelColor, $rockwellBold, $rcpNumber);

        // 2. Date (Right next to "Date :")
        $dateText = $receipt->created_at ? $receipt->created_at->format('d/m/Y') : date('d/m/Y');
        @imagettftext($img, 20, 0, 830, 230, $labelColor, $rockwellBold, $dateText);

        // 3. Donor Name (Centered below "Thank you" and above "for your kind contribution of")
        $donorName = strtoupper($receipt->donor_name);
        $fontSize = 40;
        $maxNameWidth = $w - 220; // safe margin

        while ($fontSize > 18) {
            $bbox = @imagettfbbox($fontSize, 0, $rockwellBold, $donorName);
            if (! $bbox) {
                break;
            }
            $textW = abs($bbox[4] - $bbox[0]);
            if ($textW <= $maxNameWidth) {
                break;
            }
            $fontSize -= 2;
        }

        $bbox = @imagettfbbox($fontSize, 0, $rockwellBold, $donorName);
        if ($bbox) {
            $textW = abs($bbox[4] - $bbox[0]);
            $nameX = (int) (($w - $textW) / 2);
            @imagettftext($img, $fontSize, 0, $nameX, 610, $whiteColor, $rockwellBold, $donorName);
        }

        // 4. Amount (Centered inside yellow pill box, X: 357..763, Y: 694..843)
        $amountFormatted = number_format($receipt->amount, 0, '.', ',');
        $amountText = "Rs.{$amountFormatted}/-";

        $amountFontSize = 40;
        $maxAmountWidth = 360;

        while ($amountFontSize > 20) {
            $bbox = @imagettfbbox($amountFontSize, 0, $rockwellBold, $amountText);
            if (! $bbox) {
                break;
            }
            $textW = abs($bbox[4] - $bbox[0]);
            if ($textW <= $maxAmountWidth) {
                break;
            }
            $amountFontSize -= 2;
        }

        $bbox = @imagettfbbox($amountFontSize, 0, $rockwellBold, $amountText);
        if ($bbox) {
            $textW = abs($bbox[4] - $bbox[0]);
            $amountX = (int) (($w - $textW) / 2);
            @imagettftext($img, $amountFontSize, 0, $amountX, 755, $blackColor, $rockwellBold, $amountText);
        }

        return $img;
    }

    /**
     * Return a StreamedResponse for downloading or previewing the generated receipt image.
     */
    public static function streamResponse(FundReceipt $receipt, bool $download = false): StreamedResponse
    {
        $filename = "QUAF-Receipt-{$receipt->receipt_number}.jpg";

        return response()->stream(function () use ($receipt) {
            $img = self::generate($receipt);
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
