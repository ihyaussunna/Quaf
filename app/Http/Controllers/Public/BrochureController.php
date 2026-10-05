<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class BrochureController extends Controller
{
    public function index(): View
    {
        $brochure = [
            'title' => 'QUAF 9.0 Official Brochure',
            'edition' => 'Season 09 — 2026',
            'pdf_url' => asset('documents/quaf-brochure.pdf'),
            'total_pages' => 14,
            'handbook_url' => asset('documents/quaf-handbook.pdf'),
            'handbook_title' => 'Official Festival Handbook 2026',
        ];

        return view('public.brochure', compact('brochure'));
    }
}
