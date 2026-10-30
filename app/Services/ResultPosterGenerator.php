<?php

namespace App\Services;

use App\Models\Result;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResultPosterGenerator
{
    /**
     * Generate the high-resolution Result Poster as a GD Image.
     * Dimensions: 1080 x 1440 (HD Social & Print Quality)
     */
    public static function generate(Result $result): \GdImage
    {
        $result->loadMissing([
            'program.category',
            'program.stage',
            'firstEntry.student.group',
            'secondEntry.student.group',
            'thirdEntry.student.group',
        ]);

        $program = $result->program;

        // Calculate Result Sequence Number in published chronological order
        $resultNo = Result::where('status', 'published')
            ->where(function ($q) use ($result) {
                $q->where('published_at', '<', $result->published_at)
                    ->orWhere(function ($sub) use ($result) {
                        $sub->where('published_at', '=', $result->published_at)
                            ->where('id', '<=', $result->id);
                    });
            })
            ->count() ?: ($result->id ?? 1);

        $isYellow = ($result->custom_poster_settings['layout_mode'] ?? '') === 'yellow_modern'
            || str_contains(strtolower($result->template?->image_path ?? ''), 'yellow')
            || str_contains(strtolower($result->template?->name ?? ''), 'yellow');

        if ($isYellow && file_exists(public_path('images/result-templates/quaf_template_yellow.png'))) {
            return self::generateYellow($result, $resultNo);
        }

        $width = 1080;
        $height = 1440;
        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        // Fonts resolution
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

        // 1. Festive Crimson Gradient Background
        $topR = 158;
        $topG = 19;
        $topB = 32;   // #9e1320
        $midR = 190;
        $midG = 30;
        $midB = 45;   // #be1e2d
        $botR = 110;
        $botG = 10;
        $botB = 18;   // #6e0a12

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

        // Background subtle glows / concentric rings
        $subtleRing = imagecolorallocatealpha($img, 255, 255, 255, 122);
        imageellipse($img, 120, 140, 520, 520, $subtleRing);
        imageellipse($img, $width - 100, $height - 140, 640, 640, $subtleRing);

        // 2. Poster Header Graphic (from poster-header.png)
        $headerPngPath = public_path('images/poster-header.png');
        if (file_exists($headerPngPath)) {
            $headerImg = @imagecreatefrompng($headerPngPath);
            if ($headerImg) {
                $origW = imagesx($headerImg);
                $origH = imagesy($headerImg);
                $targetW = 440;
                $targetH = (int) round(($origH / $origW) * $targetW);
                $targetX = (int) (($width - $targetW) / 2);
                $targetY = 40;

                imagecopyresampled($img, $headerImg, $targetX, $targetY, 0, 0, $targetW, $targetH, $origW, $origH);
                imagedestroy($headerImg);
            }
        }

        // 3. Category & Result Badge
        // Circular Result No badge + Zone/Type Pill
        $badgeY = 240;
        $resultNoStr = sprintf('%02d', $resultNo);
        $zoneStr = strtoupper($program->eligibility ?? 'ALL ZONES');
        $typeStr = strtoupper($program->type ?? 'INDIVIDUAL');
        $pillText = "{$zoneStr}  •  {$typeStr}";

        // Amber Capsule Pill for Zone & Type
        $pillFontSize = 13;
        $pillW = 320;
        $pillH = 44;
        $pillX = (int) (($width - $pillW) / 2);
        self::drawRoundedRectangle($img, $pillX, $badgeY, $pillX + $pillW, $badgeY + $pillH, 22, imagecolorallocate($img, 247, 177, 51));
        self::drawCenteredText($img, $pillFontSize, $badgeY + 28, $soraBold, $pillText, imagecolorallocate($img, 20, 20, 20), $width);

        // Circular Result No Badge on left of pill
        $circleRadius = 26;
        $circleX = $pillX - 35;
        $circleY = $badgeY + (int) ($pillH / 2);
        imagefilledellipse($img, $circleX, $circleY, $circleRadius * 2, $circleRadius * 2, imagecolorallocate($img, 247, 177, 51));
        imageellipse($img, $circleX, $circleY, ($circleRadius * 2) - 4, ($circleRadius * 2) - 4, imagecolorallocate($img, 255, 255, 255));

        $numBbox = @imagettfbbox(14, 0, $soraBold, $resultNoStr);
        if ($numBbox) {
            $numW = abs($numBbox[4] - $numBbox[0]);
            $numH = abs($numBbox[5] - $numBbox[1]);
            imagettftext($img, 14, 0, (int) ($circleX - ($numW / 2)), (int) ($circleY + ($numH / 2) - 1), imagecolorallocate($img, 20, 20, 20), $soraBold, $resultNoStr);
        }

        // 4. Program Name Title
        $progName = (string) $program->name;
        $titleFontSize = strlen($progName) > 28 ? 34 : (strlen($progName) > 20 ? 40 : 46);
        $titleY = 360;
        $whiteColor = imagecolorallocate($img, 255, 255, 255);
        self::drawCenteredText($img, $titleFontSize, $titleY, $soraBold, $progName, $whiteColor, $width);

        // 5. Three Podium Winner Capsules
        $winners = [
            1 => [
                'entry' => $result->firstEntry,
                'rankTitle' => 'FIRST PLACE',
                'bg' => imagecolorallocate($img, 255, 255, 255),
                'border' => imagecolorallocate($img, 245, 197, 24),
                'circle' => imagecolorallocate($img, 245, 197, 24), // Gold
                'textColor' => imagecolorallocate($img, 120, 53, 15),
            ],
            2 => [
                'entry' => $result->secondEntry,
                'rankTitle' => 'SECOND PLACE',
                'bg' => imagecolorallocate($img, 255, 255, 255),
                'border' => imagecolorallocate($img, 203, 213, 225),
                'circle' => imagecolorallocate($img, 203, 213, 225), // Silver
                'textColor' => imagecolorallocate($img, 30, 41, 59),
            ],
            3 => [
                'entry' => $result->thirdEntry,
                'rankTitle' => 'THIRD PLACE',
                'bg' => imagecolorallocate($img, 255, 255, 255),
                'border' => imagecolorallocate($img, 217, 119, 6),
                'circle' => imagecolorallocate($img, 217, 119, 6), // Bronze
                'textColor' => imagecolorallocate($img, 255, 255, 255),
            ],
        ];

        $capsuleW = 960;
        $capsuleH = 175;
        $capsuleX1 = (int) (($width - $capsuleW) / 2);
        $capsuleX2 = $capsuleX1 + $capsuleW;
        $startY = 440;
        $gap = 32;
        $radius = 45;

        for ($rank = 1; $rank <= 3; $rank++) {
            $winnerData = $winners[$rank];
            $entry = $winnerData['entry'];

            $currentY1 = $startY + (($rank - 1) * ($capsuleH + $gap));
            $currentY2 = $currentY1 + $capsuleH;
            $centerY = (int) ($currentY1 + ($capsuleH / 2));

            // Shadow
            $shadowColor = imagecolorallocatealpha($img, 60, 5, 10, 85);
            self::drawRoundedRectangle($img, $capsuleX1 + 4, $currentY1 + 6, $capsuleX2 + 4, $currentY2 + 6, $radius, $shadowColor);

            // Capsule White Body
            self::drawRoundedRectangle($img, $capsuleX1, $currentY1, $capsuleX2, $currentY2, $radius, $winnerData['bg']);

            // Rank Badge Circle on Left
            $badgeRadius = 52;
            $circleCenterX = $capsuleX1 + 80;

            // Outer ring shadow
            $ringShadow = imagecolorallocatealpha($img, 0, 0, 0, 105);
            imagefilledellipse($img, $circleCenterX, $centerY + 2, $badgeRadius * 2, $badgeRadius * 2, $ringShadow);

            // Main Badge Circle
            imagefilledellipse($img, $circleCenterX, $centerY, $badgeRadius * 2, $badgeRadius * 2, $winnerData['circle']);
            imageellipse($img, $circleCenterX, $centerY, ($badgeRadius * 2) - 6, ($badgeRadius * 2) - 6, imagecolorallocatealpha($img, 255, 255, 255, 80));

            // Rank Number "1", "2", "3"
            $rankNumFontSize = 32;
            $rankNumStr = (string) $rank;
            $bbox = @imagettfbbox($rankNumFontSize, 0, $soraBold, $rankNumStr);
            if ($bbox) {
                $numW = abs($bbox[4] - $bbox[0]);
                $numH = abs($bbox[5] - $bbox[1]);
                imagettftext($img, $rankNumFontSize, 0, (int) ($circleCenterX - ($numW / 2)), (int) ($centerY + ($numH / 2) - 2), $winnerData['textColor'], $soraBold, $rankNumStr);
            }

            // Winner Name & Group Info
            $winnerName = $entry ? ($entry->student?->name ?? 'Team '.$entry->group?->name) : 'Pending / Not Declared';
            $groupName = $entry?->group?->name ? strtoupper($entry->group->name) : '';
            $groupColorHex = $entry?->group?->color_hex ?? '#be1e2d';

            $textStartX = $capsuleX1 + 165;

            // Winner Rank Label (e.g. FIRST PLACE)
            $rankLabelColor = imagecolorallocate($img, 148, 163, 184); // Slate-400
            imagettftext($img, 11, 0, $textStartX, $centerY - 32, $rankLabelColor, $soraBold, $winnerData['rankTitle']);

            // Winner Name
            $nameFontSize = strlen($winnerName) > 28 ? 22 : 27;
            $nameColor = imagecolorallocate($img, 15, 23, 42); // Slate-900
            imagettftext($img, $nameFontSize, 0, $textStartX, $centerY + 6, $nameColor, $soraBold, $winnerName);

            // Group Name with Team Color Dot
            if ($groupName) {
                $dotColor = self::allocateHexColor($img, $groupColorHex);
                imagefilledellipse($img, $textStartX + 8, $centerY + 38, 14, 14, $dotColor);

                $grpColor = imagecolorallocate($img, 100, 116, 139); // Slate-500
                imagettftext($img, 13, 0, $textStartX + 24, $centerY + 43, $grpColor, $soraBold, $groupName);
            }
        }

        // 6. Poster Footer Branding
        $footerY = 1350;
        $footerLineColor = imagecolorallocatealpha($img, 255, 255, 255, 100);
        imageline($img, 100, $footerY - 30, $width - 100, $footerY - 30, $footerLineColor);

        $footerText = 'QUAF 09  •  MARKAZ CULTURAL FESTIVAL 2026  •  OFFICIAL RESULT';
        $footerFontSize = 13;
        $footerTextColor = imagecolorallocate($img, 255, 255, 255);
        self::drawCenteredText($img, $footerFontSize, $footerY - 5, $soraBold, $footerText, $footerTextColor, $width);

        $timestampText = 'Published on '.($result->published_at ? $result->published_at->format('d M Y, h:i A') : now()->format('d M Y')).'  |  quaf.ihyaussunna.in';
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

        imagefilledrectangle($img, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $radius, $x1 + $radius, $y2 - $radius, $color);
        imagefilledrectangle($img, $x2 - $radius, $y1 + $radius, $x2, $y2 - $radius, $color);

        imagefilledellipse($img, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    }

    /**
     * Generate dedicated yellow modern poster layout matching Adobe Illustrator design.
     */
    public static function generateYellow(Result $result, int $resultNo): \GdImage
    {
        $templatePath = public_path('images/result-templates/quaf_template_yellow.png');
        $img = imagecreatefrompng($templatePath);
        imagealphablending($img, true);

        $red = imagecolorallocate($img, 208, 38, 39); // #D02627
        $black = imagecolorallocate($img, 0, 0, 0);
        $white = imagecolorallocate($img, 255, 255, 255);
        $divider = imagecolorallocatealpha($img, 0, 0, 0, 100);

        $rockwellRegular = public_path('fonts/ROCK.TTF');
        $rockwellLight = public_path('fonts/Rockwell-Light.otf');
        if (! file_exists($rockwellLight)) {
            $rockwellLight = $rockwellRegular;
        }
        $serifFont = public_path('fonts/georgiab.ttf');
        if (! file_exists($serifFont)) {
            $serifFont = $rockwellRegular;
        }

        $program = $result->program;
        $zone = $program->zone?->name ?? ($program->eligibility ?? 'All Zones');

        // 1. Zone in Red Capsule
        $zoneBbox = @imagettfbbox(33, 0, $rockwellRegular, $zone);
        if ($zoneBbox) {
            $zw = abs($zoneBbox[4] - $zoneBbox[0]);
            $zh = abs($zoneBbox[5] - $zoneBbox[1]);
            $zx = 686 - ($zw / 2);
            $zy = 165 + ($zh / 2) - 2;
            imagettftext($img, 33, 0, (int) $zx, (int) $zy, $white, $rockwellRegular, $zone);
        }

        // 2. Program Name
        $progName = $program->name ?? 'Competition';
        $words = explode(' ', $progName);
        $lines = [];
        $line = '';
        foreach ($words as $w) {
            if (strlen($line.' '.$w) > 12 && ! empty($line)) {
                $lines[] = trim($line);
                $line = $w;
            } else {
                $line .= ' '.$w;
            }
        }
        if (! empty($line)) {
            $lines[] = trim($line);
        }

        $py = count($lines) > 2 ? 260 : 290;
        $fontSize = count($lines) > 2 ? 55 : 75;
        $lineH = (int) ($fontSize * 1.15);
        foreach ($lines as $l) {
            imagettftext($img, $fontSize, 0, 584, $py, $black, $rockwellRegular, $l);
            $py += $lineH;
        }

        // 3. Result Sequence Number
        $resultNoStr = sprintf('%02d', $resultNo);
        $bboxNo = @imagettfbbox(260, 0, $serifFont, $resultNoStr);
        if ($bboxNo) {
            $nw = abs($bboxNo[4] - $bboxNo[0]);
            imagettftext($img, 260, 0, (int) (740 - ($nw / 2)), 760, $red, $serifFont, $resultNoStr);
        }

        // 4. Multiple Winners with dynamic auto-scaling
        $winnersData = ! empty($result->custom_poster_settings['custom_winners'])
            ? $result->custom_poster_settings['custom_winners']
            : $result->getPosterWinners();

        $flatWinners = [];
        foreach (['first', 'second', 'third'] as $rankKey) {
            $rankNum = $rankKey === 'first' ? '01' : ($rankKey === 'second' ? '02' : '03');
            if (! empty($winnersData[$rankKey])) {
                foreach ($winnersData[$rankKey] as $item) {
                    if (! empty($item['name'])) {
                        $flatWinners[] = [
                            'rank' => $rankNum,
                            'name' => $item['name'],
                            'unit' => $item['unit'] ?? '',
                        ];
                    }
                }
            }
        }

        $count = count($flatWinners);
        if ($count > 0) {
            $baseScale = $count <= 3 ? 1.0 : min(1.0, max(0.48, 3.15 / $count));
            $availHeight = 520;
            $rowGap = $count <= 3 ? 175 : (int) ($availHeight / $count);
            $startY = $count <= 3 ? 120 : max(90, (int) (130 - ($count - 3) * 10));

            $rankSize = (int) max(14, round(23 * $baseScale));
            $nameSize = (int) max(15, round(25 * $baseScale));
            $unitSize = (int) max(12, round(19 * $baseScale));

            $blockLeft = 205;
            $dividerWidth = (int) (280 * min(1.0, $baseScale * 1.1));

            $currY = $startY;
            foreach ($flatWinners as $winner) {
                // Rank
                imagettftext($img, $rankSize, 0, $blockLeft, $currY, $red, $rockwellRegular, $winner['rank']);

                // Name
                $nameY = $currY + (int) ($rankSize * 1.45);
                $nameParts = explode(' ', trim($winner['name']));
                if (count($nameParts) > 2 && strlen($winner['name']) > 16) {
                    imagettftext($img, $nameSize, 0, $blockLeft, $nameY, $red, $rockwellRegular, $nameParts[0]);
                    $nameY += (int) ($nameSize * 1.25);
                    imagettftext($img, $nameSize, 0, $blockLeft, $nameY, $red, $rockwellRegular, implode(' ', array_slice($nameParts, 1)));
                } else {
                    imagettftext($img, $nameSize, 0, $blockLeft, $nameY, $red, $rockwellRegular, $winner['name']);
                }

                // Unit
                $unitY = $nameY + (int) ($nameSize * 1.4);
                imagettftext($img, $unitSize, 0, $blockLeft, $unitY, $black, $rockwellLight, $winner['unit']);

                // Divider line
                $lineY = $unitY + (int) ($unitSize * 0.95);
                imageline($img, $blockLeft, $lineY, $blockLeft + $dividerWidth, $lineY, $divider);

                $currY += $rowGap;
            }
        }

        return $img;
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
    public static function streamResponse(\GdImage $img, string $filename = 'QUAF-Result-Poster.jpg', bool $download = true): StreamedResponse
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
