<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use App\Models\Group;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');
        $groupId = $request->query('group');
        $stageId = $request->query('stage');

        $query = GalleryItem::with(['group', 'stage']);

        if ($category) {
            $query->where('category', $category);
        }

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        if ($stageId) {
            $query->where('stage_id', $stageId);
        }

        $items = $query->orderBy('display_order')->latest()->paginate(18)->withQueryString();

        $categories = GalleryItem::select('category')->distinct()->pluck('category');
        $groups = Group::all();
        $stages = Stage::all();

        return view('public.gallery', compact('items', 'categories', 'groups', 'stages', 'category', 'groupId', 'stageId'));
    }

    public function download(GalleryItem $item): Response
    {
        $rawPath = $item->getRawOriginal('image_path') ?? $item->image_path;
        $parsed = parse_url($rawPath, PHP_URL_PATH) ?? $rawPath;
        $cleanPath = ltrim($parsed, '/');

        $diskPath = $cleanPath;
        if (str_starts_with($diskPath, 'storage/')) {
            $diskPath = substr($diskPath, strlen('storage/'));
        }

        if (Storage::disk('public')->exists($diskPath)) {
            $fullPath = Storage::disk('public')->path($diskPath);
            $ext = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'jpg';
            $downloadName = Str::slug($item->title ?: 'quaf-festival-photo').'-'.$item->id.'.'.$ext;

            return response()->download($fullPath, $downloadName);
        }

        $publicPath = public_path($cleanPath);
        if (file_exists($publicPath)) {
            $ext = pathinfo($publicPath, PATHINFO_EXTENSION) ?: 'jpg';
            $downloadName = Str::slug($item->title ?: 'quaf-festival-photo').'-'.$item->id.'.'.$ext;

            return response()->download($publicPath, $downloadName);
        }

        return redirect($item->image_path);
    }
}
