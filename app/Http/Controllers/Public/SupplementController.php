<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SupplementController extends Controller
{
    public function index(): View
    {
        $pages = [];
        for ($i = 1; $i <= 12; $i++) {
            $pages[] = asset("images/supplement/page-{$i}.jpg");
        }

        $supplement = [
            'title' => 'QUAF Official Festival Supplement',
            'subtitle' => 'Ādabīc Inheritance — Special Supplement Edition',
            'edition' => '2026',
            'pdf_url' => asset('documents/quaf-supplement.pdf'),
            'total_pages' => 12,
            'pages' => $pages,
        ];

        return view('public.supplement', compact('supplement'));
    }
}
