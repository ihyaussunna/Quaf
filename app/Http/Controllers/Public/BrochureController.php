<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class BrochureController extends Controller
{
    public function index(): View
    {
        $pages = [];
        for ($i = 1; $i <= 20; $i++) {
            $pages[] = asset("images/brochure/page-{$i}.jpg");
        }

        $brochure = [
            'title' => 'QUAF 9.0 Official Theme Note & Brochure',
            'subtitle' => 'Ādabīc Inheritance — Samastha Centenary Edition',
            'edition' => 'Season 09 — 2026',
            'pdf_url' => asset('documents/quaf-brochure.pdf'),
            'total_pages' => 20,
            'pages' => $pages,
        ];

        return view('public.brochure', compact('brochure'));
    }
}
