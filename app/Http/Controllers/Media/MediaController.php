<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use App\Models\Group;
use App\Models\News;
use App\Models\Stage;
use App\Models\VideoItem;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    /**
     * Display the Media Team dashboard.
     */
    public function dashboard(): View
    {
        $stats = [
            'news_total' => News::count(),
            'news_published' => News::where('status', 'published')->count(),
            'gallery_total' => GalleryItem::count(),
            'videos_total' => VideoItem::count(),
            'videos_live' => VideoItem::where('is_live', true)->count(),
        ];

        $recentNews = News::latest()->take(5)->get();
        $recentGallery = GalleryItem::with(['group', 'stage'])->latest()->take(6)->get();
        $recentVideos = VideoItem::latest()->take(4)->get();

        return view('media.dashboard', compact('stats', 'recentNews', 'recentGallery', 'recentVideos'));
    }

    // ==========================================
    // NEWS MANAGEMENT
    // ==========================================

    public function newsIndex(Request $request): View
    {
        $query = News::query()->latest();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $news = $query->paginate(15);
        $categories = News::distinct()->pluck('category')->filter()->values();

        return view('media.news.index', compact('news', 'categories'));
    }

    public function newsCreate(): View
    {
        return view('media.news.create');
    }

    public function newsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'cover_image' => ['nullable', 'string'],
            'cover_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg,gif', 'max:12288'],
            'is_featured' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draft,published,scheduled'],
            'published_at' => ['nullable', 'date'],
        ]);

        $coverImagePath = $validated['cover_image'] ?? null;
        if ($request->hasFile('cover_file')) {
            $path = $request->file('cover_file')->store('media/news', 'public');
            $coverImagePath = '/storage/'.$path;
        }

        $slug = Str::slug($validated['title']).'-'.Str::random(5);
        $publishedAt = $validated['published_at'] ?? null;
        if ($validated['status'] === 'published' && empty($publishedAt)) {
            $publishedAt = Carbon::now();
        }

        $article = News::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'cover_image' => $coverImagePath,
            'is_featured' => $request->boolean('is_featured'),
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        AuditLogger::log('create_news', $article, null, $article->toArray());

        return redirect()->route('media.news.index')->with('success', "വാർത്ത '{$article->title}' വിജയകരമായി പ്രസിദ്ധീകരിച്ചു.");
    }

    public function newsEdit(News $news): View
    {
        return view('media.news.edit', compact('news'));
    }

    public function newsUpdate(Request $request, News $news): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'cover_image' => ['nullable', 'string'],
            'cover_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg,gif', 'max:12288'],
            'is_featured' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draft,published,scheduled'],
            'published_at' => ['nullable', 'date'],
        ]);

        $coverImagePath = $validated['cover_image'] ?? $news->getRawOriginal('cover_image');
        if ($request->hasFile('cover_file')) {
            $path = $request->file('cover_file')->store('media/news', 'public');
            $coverImagePath = '/storage/'.$path;
        }

        $publishedAt = $validated['published_at'] ?? $news->published_at;
        if ($validated['status'] === 'published' && empty($publishedAt)) {
            $publishedAt = Carbon::now();
        }

        $old = $news->toArray();
        $news->update([
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'cover_image' => $coverImagePath,
            'is_featured' => $request->boolean('is_featured'),
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        AuditLogger::log('update_news', $news, $old, $news->toArray());

        return redirect()->route('media.news.index')->with('success', "വാർത്ത '{$news->title}' അപ്‌ഡേറ്റ് ചെയ്തു.");
    }

    public function newsDestroy(News $news): RedirectResponse
    {
        $old = $news->toArray();
        $news->delete();

        AuditLogger::log('delete_news', null, $old, null);

        return redirect()->route('media.news.index')->with('success', 'വാർത്ത നീക്കം ചെയ്തു.');
    }

    public function newsToggleFeatured(News $news): RedirectResponse
    {
        $news->is_featured = ! $news->is_featured;
        $news->save();

        return back()->with('success', 'ഫീച്ചേർഡ് സ്റ്റാറ്റസ് മാറ്റി: '.($news->is_featured ? 'ഫീച്ചർ ചെയ്തു' : 'ഫീച്ചർ ഒഴിവാക്കി'));
    }

    // ==========================================
    // GALLERY PHOTOS MANAGEMENT
    // ==========================================

    public function galleryIndex(Request $request): View
    {
        $query = GalleryItem::with(['group', 'stage'])->orderBy('display_order')->latest();

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($groupId = $request->input('group_id')) {
            $query->where('group_id', $groupId);
        }

        if ($stageId = $request->input('stage_id')) {
            $query->where('stage_id', $stageId);
        }

        $items = $query->paginate(24);
        $groups = Group::all();
        $stages = Stage::all();
        $categories = GalleryItem::distinct()->pluck('category')->filter()->values();

        return view('media.gallery.index', compact('items', 'groups', 'stages', 'categories'));
    }

    public function galleryCreate(): View
    {
        $groups = Group::all();
        $stages = Stage::all();

        return view('media.gallery.create', compact('groups', 'stages'));
    }

    public function galleryStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'image_path' => ['nullable', 'string'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg,gif', 'max:12288'],
            'category' => ['required', 'string', 'max:100'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'stage_id' => ['nullable', 'exists:stages,id'],
            'is_featured' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer'],
        ]);

        $imagePath = $validated['image_path'] ?? null;
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('media/gallery', 'public');
            $imagePath = '/storage/'.$path;
        }

        if (empty($imagePath)) {
            return back()->withErrors(['image_file' => 'ദയവായി ഒരു ഫോട്ടോ അപ്‌ലോഡ് ചെയ്യുകയോ ലിങ്ക് നൽകുകയോ ചെയ്യുക.'])->withInput();
        }

        $item = GalleryItem::create([
            'title' => $validated['title'],
            'image_path' => $imagePath,
            'category' => $validated['category'],
            'group_id' => $validated['group_id'] ?? null,
            'stage_id' => $validated['stage_id'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'display_order' => $validated['display_order'] ?? 0,
        ]);

        AuditLogger::log('create_gallery_item', $item, null, $item->toArray());

        return redirect()->route('media.gallery.index')->with('success', 'ഫോട്ടോ ഗാലറിയിലേക്ക് ചേർത്തു.');
    }

    public function galleryEdit(GalleryItem $gallery): View
    {
        $groups = Group::all();
        $stages = Stage::all();

        return view('media.gallery.edit', compact('gallery', 'groups', 'stages'));
    }

    public function galleryUpdate(Request $request, GalleryItem $gallery): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'image_path' => ['nullable', 'string'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg,gif', 'max:12288'],
            'category' => ['required', 'string', 'max:100'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'stage_id' => ['nullable', 'exists:stages,id'],
            'is_featured' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer'],
        ]);

        $imagePath = $validated['image_path'] ?? $gallery->getRawOriginal('image_path');
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('media/gallery', 'public');
            $imagePath = '/storage/'.$path;
        }

        $old = $gallery->toArray();
        $gallery->update([
            'title' => $validated['title'],
            'image_path' => $imagePath,
            'category' => $validated['category'],
            'group_id' => $validated['group_id'] ?? null,
            'stage_id' => $validated['stage_id'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'display_order' => $validated['display_order'] ?? 0,
        ]);

        AuditLogger::log('update_gallery_item', $gallery, $old, $gallery->toArray());

        return redirect()->route('media.gallery.index')->with('success', 'ഗാലറി ഫോട്ടോ അപ്‌ഡേറ്റ് ചെയ്തു.');
    }

    public function galleryDestroy(GalleryItem $gallery): RedirectResponse
    {
        $old = $gallery->toArray();
        $gallery->delete();

        AuditLogger::log('delete_gallery_item', null, $old, null);

        return redirect()->route('media.gallery.index')->with('success', 'ഫോട്ടോ നീക്കം ചെയ്തു.');
    }

    // ==========================================
    // VIDEOS MANAGEMENT
    // ==========================================

    public function videosIndex(Request $request): View
    {
        $query = VideoItem::orderBy('display_order')->latest();

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($request->has('is_live')) {
            $query->where('is_live', $request->boolean('is_live'));
        }

        $videos = $query->paginate(15);
        $categories = VideoItem::distinct()->pluck('category')->filter()->values();

        return view('media.videos.index', compact('videos', 'categories'));
    }

    public function videosCreate(): View
    {
        return view('media.videos.create');
    }

    public function videosStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'youtube_url' => ['required', 'string'],
            'thumbnail_path' => ['nullable', 'string'],
            'thumbnail_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'category' => ['required', 'string', 'max:100'],
            'is_live' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer'],
        ]);

        $youtubeId = $this->extractYoutubeId($validated['youtube_url']);

        $thumbnailPath = $validated['thumbnail_path'] ?? null;
        if ($request->hasFile('thumbnail_file')) {
            $path = $request->file('thumbnail_file')->store('media/videos', 'public');
            $thumbnailPath = Storage::url($path);
        } elseif (empty($thumbnailPath)) {
            $thumbnailPath = "https://img.youtube.com/vi/{$youtubeId}/hqdefault.jpg";
        }

        $video = VideoItem::create([
            'title' => $validated['title'],
            'youtube_id' => $youtubeId,
            'thumbnail_path' => $thumbnailPath,
            'category' => $validated['category'],
            'is_live' => $request->boolean('is_live'),
            'display_order' => $validated['display_order'] ?? 0,
        ]);

        AuditLogger::log('create_video', $video, null, $video->toArray());

        return redirect()->route('media.videos.index')->with('success', "വീഡിയോ '{$video->title}' ചേർത്തു.");
    }

    public function videosEdit(VideoItem $video): View
    {
        return view('media.videos.edit', compact('video'));
    }

    public function videosUpdate(Request $request, VideoItem $video): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'youtube_url' => ['required', 'string'],
            'thumbnail_path' => ['nullable', 'string'],
            'thumbnail_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'category' => ['required', 'string', 'max:100'],
            'is_live' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer'],
        ]);

        $youtubeId = $this->extractYoutubeId($validated['youtube_url']);

        $thumbnailPath = $validated['thumbnail_path'] ?? $video->thumbnail_path;
        if ($request->hasFile('thumbnail_file')) {
            $path = $request->file('thumbnail_file')->store('media/videos', 'public');
            $thumbnailPath = Storage::url($path);
        }

        $old = $video->toArray();
        $video->update([
            'title' => $validated['title'],
            'youtube_id' => $youtubeId,
            'thumbnail_path' => $thumbnailPath,
            'category' => $validated['category'],
            'is_live' => $request->boolean('is_live'),
            'display_order' => $validated['display_order'] ?? 0,
        ]);

        AuditLogger::log('update_video', $video, $old, $video->toArray());

        return redirect()->route('media.videos.index')->with('success', "വീഡിയോ '{$video->title}' അപ്‌ഡേറ്റ് ചെയ്തു.");
    }

    public function videosDestroy(VideoItem $video): RedirectResponse
    {
        $old = $video->toArray();
        $video->delete();

        AuditLogger::log('delete_video', null, $old, null);

        return redirect()->route('media.videos.index')->with('success', 'വീഡിയോ നീക്കം ചെയ്തു.');
    }

    public function videosToggleLive(VideoItem $video): RedirectResponse
    {
        $video->is_live = ! $video->is_live;
        $video->save();

        return back()->with('success', 'ലൈവ് സ്റ്റാറ്റസ് മാറ്റി: '.($video->is_live ? 'ലൈവ് ഓൺ' : 'ലൈവ് ഓഫ്'));
    }

    /**
     * Extract standard 11-char YouTube ID from URL or return raw string.
     */
    protected function extractYoutubeId(string $urlOrId): string
    {
        $urlOrId = trim($urlOrId);
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|live\/)|youtu\.be\/)([^"&?\/\s]{11})/i', $urlOrId, $matches)) {
            return $matches[1];
        }

        return $urlOrId;
    }
}
