<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\VideoItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');

        $query = VideoItem::query();

        if ($category) {
            $query->where('category', $category);
        }

        $featured = VideoItem::where('is_live', true)->first() ?? VideoItem::latest()->first();

        $videos = $query->orderBy('display_order')->latest()->paginate(12)->withQueryString();

        $categories = VideoItem::select('category')->distinct()->pluck('category');

        return view('public.videos', compact('videos', 'featured', 'categories', 'category'));
    }
}
