<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');
        $search = $request->query('search');

        $query = News::where('status', 'published');

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $featured = News::where('status', 'published')->where('is_featured', true)->latest('published_at')->first();

        $news = $query->latest('published_at')->paginate(9)->withQueryString();

        $categories = News::where('status', 'published')->select('category')->distinct()->pluck('category');

        return view('public.news', compact('news', 'featured', 'categories', 'category', 'search'));
    }

    public function show(string $slug): View
    {
        $article = News::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $related = News::where('status', 'published')
            ->where('id', '!=', $article->id)
            ->where('category', $article->category)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('public.news-detail', compact('article', 'related'));
    }
}
