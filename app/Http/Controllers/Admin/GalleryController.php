<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use App\Models\Group;
use App\Models\Stage;
use App\Services\AuditLogger;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $items = GalleryItem::with(['group', 'stage'])->orderBy('display_order')->latest()->paginate(24);

        return view('admin.gallery.index', compact('items'));
    }

    public function create(): View
    {
        $groups = Group::all();
        $stages = Stage::all();

        return view('admin.gallery.create', compact('groups', 'stages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'image_path' => ['nullable', 'string'],
            'image_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,svg,gif,bmp,avif,jfif,heic', 'max:20480'],
            'category' => ['required', 'string'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'stage_id' => ['nullable', 'exists:stages,id'],
            'is_featured' => ['boolean'],
            'display_order' => ['nullable', 'integer'],
        ]);

        if ($request->hasFile('image_file')) {
            $validated['image_path'] = FileStorageService::storePublicFile($request->file('image_file'), 'media/gallery');
        }

        if (empty($validated['image_path'])) {
            return back()->withErrors(['image_file' => 'Please upload a photo or provide an image URL.'])->withInput();
        }

        $item = GalleryItem::create($validated);

        AuditLogger::log('create_gallery_item', $item, null, $item->toArray());

        return redirect()->route('admin.gallery.index')->with('success', 'Photo added to festival gallery.');
    }

    public function destroy(GalleryItem $gallery): RedirectResponse
    {
        $old = $gallery->toArray();
        $gallery->delete();

        AuditLogger::log('delete_gallery_item', null, $old, null);

        return redirect()->route('admin.gallery.index')->with('success', 'Photo deleted.');
    }
}
