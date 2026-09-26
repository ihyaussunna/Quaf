<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use App\Models\Group;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
}
