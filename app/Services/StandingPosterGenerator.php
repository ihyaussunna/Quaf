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

        $rockwellBold = public_path('fonts/Rockwell-Bold.otf');
        if (! file_exists($rockwellBold)) {
            $rockwellBold = $soraBold;
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
        imageellipse($img, 100, 100, 500, 500, $subtleRing1);
        imageellipse($img, $width - 50, $height - 100, 600, 600, $subtleRing1);

        // 2. Header Area
        // Amber Capsule Badge: "Group Standings"
        $badgeText = 'Group Standings';
        $badgeFontSize = 18;
        $badgeW = 280;
        $badgeH = 54;
        $badgeX = (int) (($width - $badgeW) / 2);
        $badgeY = 90;

        self::drawRoundedRectangle($img, $badgeX, $badgeY, $badgeX + $badgeW, $badgeY + $badgeH, 27, imagecolorallocate($img, 247, 177, 51));

        // Badge Text (Dark Brown / Black)
        $badgeTextColor = imagecolorallocate($img, 20, 20, 20);
        self::drawCenteredText($img, $badgeFontSize, $badgeY + 36, $soraBold, $badgeText, $badgeTextColor, $width);

        // Main Title: "Top Collected Groups"
        $titleText = 'Top Collected Groups';
        $titleFontSize = 46;
        $titleY = 220;
        $whiteColor = imagecolorallocate($img, 255, 255, 255);
        self::drawCenteredText($img, $titleFontSize, $titleY, $soraBold, $titleText, $whiteColor, $width);

        // Festival Subtitle / Total Collection summary
        $subText = 'QUAF FESTIVAL FUND 2026 • TOTAL COLLECTION: ₹'.number_format($totalAmount, 0);
        $subFontSize = 15;
        $subY = 275;
        $subColor = imagecolorallocate($img, 254, 215, 170); // Soft amber-cream
        self::drawCenteredText($img, $subFontSize, $subY, $soraRegular, $subText, $subColor, $width);

        // Thin decorative gold line under header
        $lineColor = imagecolorallocatealpha($img, 255, 255, 255, 100);
        imageline($img, 140, 315, $width - 140, 315, $lineColor);

        // 3. Five Standing Capsule Bars (Rows)
        $capsuleW = 960;
        $capsuleH = 150;
        $capsuleX1 = (int) (($width - $capsuleW) / 2); // 60
        $capsuleX2 = $capsuleX1 + $capsuleW;           // 1020
        $startY = 350;
        $gap = 36;
        $radius = 75; // Pill radius (half height)

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

            // Subtitle Details: Code & Receipts Count
            $detailText = "CODE: {$grp->code}  •  ".number_format($receiptsCount)." RECEIPTS ISSUED  •  {$percentage}% OF TOTAL";
            $detailFontSize = 11;
            $detailColor = imagecolorallocate($img, 100, 116, 139); // Slate-500
            $detailY = $centerY + 24;
            imagettftext($img, $detailFontSize, 0, $nameX - 20, $detailY, $detailColor, $soraRegular, $detailText);

            // Right side: Total Amount Collected
            $amountText = '₹'.number_format($collected, 0);
            $amountFontSize = 28;
            $amountColor = imagecolorallocate($img, 15, 23, 42); // Bold dark
            $bboxAmount = @imagettfbbox($amountFontSize, 0, $rockwellBold, $amountText);
            $amountW = $bboxAmount ? abs($bboxAmount[4] - $bboxAmount[0]) : 180;
            $amountX = $capsuleX2 - $amountW - 40;
            $amountY = $centerY - 4;
            imagettftext($img, $amountFontSize, 0, $amountX, $amountY, $amountColor, $rockwellBold, $amountText);

            // Right side sublabel: "Total Collection"
            $colLabel = 'COLLECTED';
            $colLabelFontSize = 10;
            $colLabelColor = imagecolorallocate($img, 148, 163, 184); // Slate-400
            $bboxCol = @imagettfbbox($colLabelFontSize, 0, $soraRegular, $colLabel);
            $colW = $bboxCol ? abs($bboxCol[4] - $bboxCol[0]) : 60;
            $colX = $capsuleX2 - $colW - 40;
            $colY = $centerY + 22;
            imagettftext($img, $colLabelFontSize, 0, $colX, $colY, $colLabelColor, $soraRegular, $colLabel);

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
}
