<?php

namespace App\Services;

use App\Models\FundReceipt;
use App\Models\Group;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StandingPosterGenerator
{
    /**
     * Generate the high-resolution Standing Poster as a GD Image.
     * Dimensions: 1080 x 1440 (HD Social & Print Quality)
     */
    public static function generate(?Collection $topGroups = null): \GdImage
    {
        if (! $topGroups) {
            $topGroups = Group::query()
                ->withSum(['fundReceipts as total_collected' => function ($q) {
                    $q->where('is_cancelled', false);
                }], 'amount')
                ->withCount(['fundReceipts as receipts_count' => function ($q) {
                    $q->where('is_cancelled', false);
                }])
                ->orderByDesc('total_collected')
                ->get();
        }

        $totalAmount = (float) FundReceipt::where('is_cancelled', false)->sum('amount');

        $width = 1080;
        $height = 1440;
        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        // Fonts
        $soraBold = public_path('fonts/Sora-ExtraBold.ttf');
        if (! file_exists($soraBold)) {
            $soraBold = public_path('fonts/Sora-Bold.ttf');
        }
        if (! file_exists($soraBold)) {
            $soraBold = public_path('fonts/Rockwell-Bold.otf');
        }

        $soraRegular = public_path('fonts/Sora-Bold.ttf');
        if (! file_exists($soraRegular)) {
            $soraRegular = $soraBold;
        }

        // Amount Font with native Indian Rupee sign (U+20B9) support
        $amountFont = public_path('fonts/arialbd.ttf');
        if (! file_exists($amountFont)) {
            $amountFont = public_path('fonts/AnekMalayalam-Bold.ttf');
        }
        if (! file_exists($amountFont)) {
            $amountFont = $soraBold;
        }

        // 1. Festive Crimson Background Gradient
        $topR = 158;
        $topG = 19;
        $topB = 32;   // #9e1320
        $midR = 190;
        $midG = 30;
        $midB = 45;   // #be1e2d
        $botR = 130;
        $botG = 14;
        $botB = 24;   // #820e18

        for ($y = 0; $y < $height; $y++) {
            if ($y < $height * 0.5) {
                $factor = $y / ($height * 0.5);
                $r = (int) ($topR + ($midR - $topR) * $factor);
                $g = (int) ($topG + ($midG - $topG) * $factor);
                $b = (int) ($topB + ($midB - $topB) * $factor);
            } else {
                $factor = ($y - $height * 0.5) / ($height * 0.5);
                $r = (int) ($midR + ($botR - $midR) * $factor);
                $g = (int) ($midG + ($botG - $midG) * $factor);
                $b = (int) ($midB + ($botB - $midB) * $factor);
            }
            $color = imagecolorallocate($img, $r, $g, $b);
            imageline($img, 0, $y, $width, $y, $color);
        }

        // Decorative background geometric curves / circles
        $subtleRing1 = imagecolorallocatealpha($img, 255, 255, 255, 122);
        imageellipse($img, 100, 120, 500, 500, $subtleRing1);
        imageellipse($img, $width - 80, $height - 120, 600, 600, $subtleRing1);

        // 2. Poster Header Graphic (from poster header.svg)
        $headerPngPath = public_path('images/poster-header.png');
        if (file_exists($headerPngPath)) {
            $headerImg = @imagecreatefrompng($headerPngPath);
            if ($headerImg) {
                $origW = imagesx($headerImg);
                $origH = imagesy($headerImg);
                $targetW = 460;
                $targetH = (int) round(($origH / $origW) * $targetW);
                $targetX = (int) (($width - $targetW) / 2);
                $targetY = 40;

                imagecopyresampled($img, $headerImg, $targetX, $targetY, 0, 0, $targetW, $targetH, $origW, $origH);
                imagedestroy($headerImg);
            }
        }

        // Amber Capsule Badge: "Group Standings"
        $badgeText = 'Group Standings';
        $badgeFontSize = 15;
        $badgeW = 250;
        $badgeH = 46;
        $badgeX = (int) (($width - $badgeW) / 2);
        $badgeY = 245;

        self::drawRoundedRectangle($img, $badgeX, $badgeY, $badgeX + $badgeW, $badgeY + $badgeH, 23, imagecolorallocate($img, 247, 177, 51));

        // Badge Text (Dark Brown / Black)
        $badgeTextColor = imagecolorallocate($img, 20, 20, 20);
        self::drawCenteredText($img, $badgeFontSize, $badgeY + 31, $soraBold, $badgeText, $badgeTextColor, $width);

        // Main Title: "Top Collected Groups"
        $titleText = 'Top Collected Groups';
        $titleFontSize = 42;
        $titleY = 345;
        $whiteColor = imagecolorallocate($img, 255, 255, 255);
        self::drawCenteredText($img, $titleFontSize, $titleY, $soraBold, $titleText, $whiteColor, $width);

        // Note: Total collection is removed from poster as requested

        // 3. Five Standing Capsule Bars (Rows)
        $capsuleW = 960;
        $capsuleH = 142;
        $capsuleX1 = (int) (($width - $capsuleW) / 2); // 60
        $capsuleX2 = $capsuleX1 + $capsuleW;           // 1020
        $startY = 390;
        $gap = 26;
        $radius = 71; // Pill radius (half height)

        $goldCircle = imagecolorallocate($img, 245, 197, 24);    // Rank 1 Gold
        $silverCircle = imagecolorallocate($img, 203, 213, 225); // Rank 2 Silver
        $bronzeCircle = imagecolorallocate($img, 217, 119, 6);   // Rank 3 Bronze
        $greenCircle = imagecolorallocate($img, 16, 185, 129);   // Rank 4 Green
        $redCircle = imagecolorallocate($img, 225, 29, 72);      // Rank 5 Rose

        $rankColors = [
            1 => ['circle' => $goldCircle, 'text' => imagecolorallocate($img, 120, 53, 15)],
            2 => ['circle' => $silverCircle, 'text' => imagecolorallocate($img, 15, 23, 42)],
            3 => ['circle' => $bronzeCircle, 'text' => imagecolorallocate($img, 255, 255, 255)],
            4 => ['circle' => $greenCircle, 'text' => imagecolorallocate($img, 255, 255, 255)],
            5 => ['circle' => $redCircle, 'text' => imagecolorallocate($img, 255, 255, 255)],
        ];

        $rankIndex = 0;
        foreach ($topGroups->take(5) as $grp) {
            $rank = $rankIndex + 1;
            $currentY1 = $startY + ($rankIndex * ($capsuleH + $gap));
            $currentY2 = $currentY1 + $capsuleH;
            $centerY = (int) ($currentY1 + ($capsuleH / 2));

            $collected = (float) ($grp->total_collected ?? 0);
            $receiptsCount = (int) ($grp->receipts_count ?? 0);
            $percentage = $totalAmount > 0 ? round(($collected / $totalAmount) * 100, 1) : 0;

            // Capsule Drop Shadow
            $shadowColor = imagecolorallocatealpha($img, 60, 5, 10, 85);
            self::drawRoundedRectangle($img, $capsuleX1 + 4, $currentY1 + 6, $capsuleX2 + 4, $currentY2 + 6, $radius, $shadowColor);

            // Capsule White Body
            $capsuleBg = imagecolorallocate($img, 255, 255, 255);
            self::drawRoundedRectangle($img, $capsuleX1, $currentY1, $capsuleX2, $currentY2, $radius, $capsuleBg);

            // Left Circular Rank Badge
            $badgeRadius = 55;
            $circleCenterX = $capsuleX1 + 75;
            $colors = $rankColors[$rank] ?? $rankColors[5];

            // Outer ring shadow
            $ringShadow = imagecolorallocatealpha($img, 0, 0, 0, 105);
            imagefilledellipse($img, $circleCenterX, $centerY + 2, $badgeRadius * 2, $badgeRadius * 2, $ringShadow);

            // Main Badge Circle
            imagefilledellipse($img, $circleCenterX, $centerY, $badgeRadius * 2, $badgeRadius * 2, $colors['circle']);

            // White Inner Stroke
            $innerStroke = imagecolorallocatealpha($img, 255, 255, 255, 75);
            imageellipse($img, $circleCenterX, $centerY, ($badgeRadius * 2) - 6, ($badgeRadius * 2) - 6, $innerStroke);

            // Rank Number text "01", "02", etc.
            $rankStr = sprintf('%02d', $rank);
            $rankNumFontSize = 26;
            $bbox = @imagettfbbox($rankNumFontSize, 0, $soraBold, $rankStr);
            if ($bbox) {
                $numW = abs($bbox[4] - $bbox[0]);
                $numH = abs($bbox[5] - $bbox[1]);
                $numX = (int) ($circleCenterX - ($numW / 2));
                $numY = (int) ($centerY + ($numH / 2) - 2);
                imagettftext($img, $rankNumFontSize, 0, $numX, $numY, $colors['text'], $soraBold, $rankStr);
            }

            // Left Color Accent Dot
            $grpColorHex = $grp->color_hex ?? '#be1e2d';
            $grpDotColor = self::allocateHexColor($img, $grpColorHex);
            $dotX = $capsuleX1 + 160;
            imagefilledellipse($img, $dotX, $centerY - 16, 16, 16, $grpDotColor);

            // Group Name
            $grpName = strtoupper((string) $grp->name);
            $nameFontSize = 23;
            $nameColor = imagecolorallocate($img, 15, 23, 42); // Slate-900
            $nameX = $capsuleX1 + 180;
            $nameY = $centerY - 6;
            imagettftext($img, $nameFontSize, 0, $nameX, $nameY, $nameColor, $soraBold, $grpName);

            // Subtitle Details: Receipts Count (Team code excluded as requested)
            $detailText = number_format($receiptsCount).' '.($receiptsCount === 1 ? 'RECEIPT' : 'RECEIPTS').' ISSUED';
            $detailFontSize = 11;
            $detailColor = imagecolorallocate($img, 100, 116, 139); // Slate-500
            $detailY = $centerY + 24;
            imagettftext($img, $detailFontSize, 0, $nameX, $detailY, $detailColor, $soraRegular, $detailText);

            // Right side: Total Amount Collected & Finance Support Points
            $points = self::calculateFinancePoints($collected);
            $amountText = '₹'.number_format($collected, 0);
            $amountFontSize = 28;
            $amountColor = imagecolorallocate($img, 15, 23, 42); // Bold dark
            $bboxAmount = @imagettfbbox($amountFontSize, 0, $amountFont, $amountText);
            $amountW = $bboxAmount ? abs($bboxAmount[4] - $bboxAmount[0]) : 170;
            $amountX = $capsuleX2 - $amountW - 40;
            $amountY = $centerY - 4;
            imagettftext($img, $amountFontSize, 0, $amountX, $amountY, $amountColor, $amountFont, $amountText);

            // Points Pill Badge (aligned to right, directly under the amount)
            $ptsText = $points.' PTS';
            $ptsFontSize = 12;
            $bboxPts = @imagettfbbox($ptsFontSize, 0, $soraBold, $ptsText);
            $ptsW = $bboxPts ? abs($bboxPts[4] - $bboxPts[0]) : 55;
            $badgePadX = 14;
            $badgeH = 26;
            $badgeW = $ptsW + ($badgePadX * 2);
            $badgeX2 = $capsuleX2 - 40;
            $badgeX1 = $badgeX2 - $badgeW;
            $badgeY1 = $centerY + 10;
            $badgeY2 = $badgeY1 + $badgeH;

            // Pill styling
            $badgeBg = ($rank === 1)
                ? imagecolorallocate($img, 254, 243, 199) // Amber-100 for 1st
                : imagecolorallocate($img, 241, 245, 249); // Slate-100 for others
            $badgeTextColor = ($rank === 1)
                ? imagecolorallocate($img, 146, 64, 14) // Amber-800
                : imagecolorallocate($img, 30, 41, 59); // Slate-800

            self::drawRoundedRectangle($img, $badgeX1, $badgeY1, $badgeX2, $badgeY2, 13, $badgeBg);
            $ptsX = (int) ($badgeX1 + $badgePadX);
            $ptsY = (int) ($badgeY1 + 18);
            imagettftext($img, $ptsFontSize, 0, $ptsX, $ptsY, $badgeTextColor, $soraBold, $ptsText);

            $rankIndex++;
        }

        // 4. Poster Footer
        $footerY = 1350;
        $footerLineColor = imagecolorallocatealpha($img, 255, 255, 255, 100);
        imageline($img, 100, $footerY - 30, $width - 100, $footerY - 30, $footerLineColor);

        $footerText = 'QUAF 09  •  MARKAZ CULTURAL FESTIVAL 2026  •  OFFICIAL LIVE STANDINGS';
        $footerFontSize = 12;
        $footerTextColor = imagecolorallocate($img, 255, 255, 255);
        self::drawCenteredText($img, $footerFontSize, $footerY - 5, $soraBold, $footerText, $footerTextColor, $width);

        $timestampText = 'Generated on '.now()->format('d M Y, h:i A').'  |  quaf.ihyaussunna.in';
        $timestampFontSize = 11;
        $timestampColor = imagecolorallocate($img, 254, 215, 170);
        self::drawCenteredText($img, $timestampFontSize, $footerY + 22, $soraRegular, $timestampText, $timestampColor, $width);

        return $img;
    }

    /**
     * Helper to draw a filled smooth rounded rectangle with anti-aliasing.
     */
    protected static function drawRoundedRectangle(\GdImage $img, int $x1, int $y1, int $x2, int $y2, int $radius, int $color): void
    {
        $radius = min($radius, (int) (($x2 - $x1) / 2), (int) (($y2 - $y1) / 2));

        // Center rectangle
        imagefilledrectangle($img, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        // Left & right fill
        imagefilledrectangle($img, $x1, $y1 + $radius, $x1 + $radius, $y2 - $radius, $color);
        imagefilledrectangle($img, $x2 - $radius, $y1 + $radius, $x2, $y2 - $radius, $color);

        // 4 corner circles
        imagefilledellipse($img, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    }

    /**
     * Helper to draw horizontally centered TTF text.
     */
    protected static function drawCenteredText(\GdImage $img, int $size, int $y, string $font, string $text, int $color, int $canvasWidth): void
    {
        $bbox = @imagettfbbox($size, 0, $font, $text);
        if ($bbox) {
            $w = abs($bbox[4] - $bbox[0]);
            $x = (int) (($canvasWidth - $w) / 2);
            imagettftext($img, $size, 0, $x, $y, $color, $font, $text);
        }
    }

    /**
     * Helper to allocate RGB from HEX.
     */
    protected static function allocateHexColor(\GdImage $img, string $hex, array $default = [190, 30, 45]): int
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));

            return imagecolorallocate($img, $r, $g, $b);
        }

        return imagecolorallocate($img, $default[0], $default[1], $default[2]);
    }

    /**
     * Return a StreamedResponse for image download.
     */
    public static function streamResponse(\GdImage $img, string $filename = 'QUAF-09-Top-Collected-Groups.jpg', bool $download = true): StreamedResponse
    {
        $disposition = $download ? 'attachment' : 'inline';

        return response()->stream(function () use ($img) {
            imagejpeg($img, null, 95);
            imagedestroy($img);
        }, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Calculate Team Excellence Award - Finance Support Points (out of 40)
     * based on the official collection target slab table:
     *   ₹5,000   – 2 points
     *   ₹10,000  – 5 points
     *   ₹20,000  – 10 points
     *   ₹30,000  – 16 points
     *   ₹40,000  – 22 points
     *   ₹50,000  – 28 points
     *   ₹60,000  – 32 points
     *   ₹70,000  – 35 points
     *   ₹80,000  – 37 points
     *   ₹90,000  – 39 points
     *   ₹1,00,000 – 40 points (Max: 40)
     */
    public static function calculateFinancePoints(float|int $amount): int
    {
        if ($amount >= 100000) {
            return 40;
        }
        if ($amount >= 90000) {
            return 39;
        }
        if ($amount >= 80000) {
            return 37;
        }
        if ($amount >= 70000) {
            return 35;
        }
        if ($amount >= 60000) {
            return 32;
        }
        if ($amount >= 50000) {
            return 28;
        }
        if ($amount >= 40000) {
            return 22;
        }
        if ($amount >= 30000) {
            return 16;
        }
        if ($amount >= 20000) {
            return 10;
        }
        if ($amount >= 10000) {
            return 5;
        }
        if ($amount >= 5000) {
            return 2;
        }

        return 0;
    }
}
