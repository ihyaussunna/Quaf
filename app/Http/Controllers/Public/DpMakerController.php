<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DpMakerController extends Controller
{
    public function index(): View
    {
        $frameConfig = [
            'frame_image' => asset('images/frames/quaf-adabic-dp-frame.png'),
            'width' => 1024,
            'height' => 1024,
            'circle_center_x' => 512,
            'circle_center_y' => 450,
            'circle_radius' => 310,
        ];

        return view('public.dp-maker', [
            'title' => 'Be QUAF, Your DP — Official Festival DP Maker',
            'ogTitle' => 'Be QUAF, Your DP — Official QUAF 2026 Profile Frame Maker',
            'ogDescription' => 'Create and customize your official QUAF Markaz Cultural Festival 2026 DP with the Ādabic Inheritance frame, and share directly to WhatsApp.',
            'ogImage' => asset('images/frames/quaf-adabic-dp-frame.jpg'),
            'frameConfig' => $frameConfig,
        ]);
    }
}
