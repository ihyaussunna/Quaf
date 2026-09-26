<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoItem;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(): View
    {
        $videos = VideoItem::orderBy('display_order')->latest()->paginate(15);

        return view('admin.videos.index', compact('videos'));
    }

    public function create(): View
    {
        return view('admin.videos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'youtube_id' => ['required', 'string', 'max:50'],
            'thumbnail_path' => ['nullable', 'string'],
            'category' => ['required', 'string'],
            'is_live' => ['boolean'],
            'display_order' => ['integer'],
        ]);

        $video = VideoItem::create($validated);

        AuditLogger::log('create_video', $video, null, $video->toArray());

        return redirect()->route('admin.videos.index')->with('success', "Video '{$video->title}' added.");
    }

    public function destroy(VideoItem $video): RedirectResponse
    {
        $old = $video->toArray();
        $video->delete();

        AuditLogger::log('delete_video', null, $old, null);

        return redirect()->route('admin.videos.index')->with('success', 'Video removed.');
    }
}
