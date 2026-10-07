<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Services\AuditLogger;
use App\Services\FileStorageService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        $articles = News::latest()->paginate(15);

        return view('admin.news.index', compact('articles'));
    }

    public function create(): View
    {
        return view('admin.news.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'category' => ['required', 'string'],
            'cover_image' => ['nullable', 'string'],
            'cover_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,svg,gif,bmp,avif,jfif,heic', 'max:20480'],
            'is_featured' => ['boolean'],
            'status' => ['required', 'in:draft,published,scheduled'],
            'published_at' => ['nullable', 'date'],
        ]);

        if ($request->hasFile('cover_file')) {
            $validated['cover_image'] = FileStorageService::storePublicFile($request->file('cover_file'), 'media/news');
        }

        $validated['slug'] = Str::slug($validated['title']).'-'.Str::random(5);
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = Carbon::now();
        }

        $news = News::create($validated);

        AuditLogger::log('create_news', $news, null, $news->toArray());

        return redirect()->route('admin.news.index')->with('success', "News article '{$news->title}' created.");
    }

    public function edit(News $news): View
    {
        return view('admin.news.edit', compact('news'));
    }

    public function update(Request $request, News $news): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'category' => ['required', 'string'],
            'cover_image' => ['nullable', 'string'],
            'cover_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,svg,gif,bmp,avif,jfif,heic', 'max:20480'],
            'is_featured' => ['boolean'],
            'status' => ['required', 'in:draft,published,scheduled'],
            'published_at' => ['nullable', 'date'],
        ]);

        if ($request->hasFile('cover_file')) {
            $validated['cover_image'] = FileStorageService::storePublicFile($request->file('cover_file'), 'media/news');
        } elseif (empty($validated['cover_image'])) {
            $validated['cover_image'] = $news->getRawOriginal('cover_image');
        }

        $old = $news->toArray();
        $news->update($validated);

        AuditLogger::log('update_news', $news, $old, $news->toArray());

        return redirect()->route('admin.news.index')->with('success', "Article '{$news->title}' updated.");
    }

    public function destroy(News $news): RedirectResponse
    {
        $old = $news->toArray();
        $news->delete();

        AuditLogger::log('delete_news', null, $old, null);

        return redirect()->route('admin.news.index')->with('success', 'Article deleted.');
    }
}
