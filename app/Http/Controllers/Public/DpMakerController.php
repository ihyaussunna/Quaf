<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DpMakerController extends Controller
{
    public function index(): View
    {
        $version = file_exists(public_path('images/frames/quaf-adabic-dp-frame.png'))
            ? filemtime(public_path('images/frames/quaf-adabic-dp-frame.png'))
            : time();

        $frameConfig = [
            'frame_image' => asset('images/frames/quaf-adabic-dp-frame.png').'?v='.$version,
            'width' => 1024,
            'height' => 1024,
            'circle_center_x' => 512,
            'circle_center_y' => 488,
            'circle_radius' => 412,
        ];

        return view('public.dp-maker', [
            'title' => 'Be QUAF, Your DP — Official Profile Frame | QUAF 2026 Markaz Art Fest',
            'ogTitle' => 'Be QUAF, Your DP — Official QUAF 2026 Profile Frame Maker',
            'metaDescription' => 'Create and customize your official QUAF Markaz Cultural Festival 2026 DP with the Ādabic Inheritance frame, and share directly to WhatsApp.',
            'ogDescription' => 'Create and customize your official QUAF Markaz Cultural Festival 2026 DP with the Ādabic Inheritance frame, and share directly to WhatsApp.',
            'ogImage' => asset('images/frames/quaf-adabic-dp-frame.jpg').'?v='.$version,
            'frameConfig' => $frameConfig,
        ]);
    }
}
