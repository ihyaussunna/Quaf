<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\FestivalSetting;
use App\Models\GalleryItem;
use App\Models\Group;
use App\Models\News;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Result;
use App\Models\Stage;
use App\Models\Student;
use App\Models\VideoItem;
use App\Models\Zone;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $defaultLaunchStatus = app()->environment('testing') ? 'launched' : 'coming_soon';
        $launchStatus = FestivalSetting::get('launch_status', $defaultLaunchStatus);
        if ($launchStatus === 'coming_soon' && ! request()->has('portal')) {
            return view('public.coming-soon');
        }

        $liveFestMode = FestivalSetting::get('live_fest_mode', '1') === '1';

        $groups = Group::orderBy('rank_cache', 'asc')
            ->orderByDesc('points_cache')
            ->withCount(['students', 'entries'])
            ->get();

        $stages = Stage::with(['currentProgram.category', 'nextProgram.category'])->get();

        $latestResults = Result::where('status', 'published')
            ->with(['program.category', 'firstEntry.student.group', 'secondEntry.student.group', 'thirdEntry.student.group'])
            ->latest('published_at')
            ->take(6)
            ->get();

        $featuredPrograms = Program::with(['category', 'stage', 'schedule'])
            ->whereIn('status', ['upcoming', 'in_progress'])
            ->take(8)
            ->get();

        $announcements = Announcement::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('target_role')->orWhere('target_role', 'all');
            })
            ->latest()
            ->take(5)
            ->get();

        $latestNews = News::where('status', 'published')
            ->latest('published_at')
            ->take(3)
            ->get();

        $galleryPreview = GalleryItem::latest()
            ->take(6)
            ->get();

        $featuredVideo = VideoItem::where('is_live', true)
            ->first() ?? VideoItem::latest()->first();

        $categories = ProgramCategory::withCount('programs')->get();

        $dbZones = Zone::orderBy('display_order')->get();
        $zones = [];
        foreach ($dbZones as $z) {
            $zones[$z->name] = [
                'name' => $z->name,
                'sub' => $z->sub_text,
                'classes' => $z->classes,
                'color' => $z->color_hex ?: '#be1e2d',
                'programs_count' => Program::where('zone_id', $z->id)->orWhere('eligibility', $z->name)->count(),
                'students_count' => Student::where('zone_id', $z->id)->orWhere('category', $z->name)->count(),
            ];
        }

        return view('public.home', compact(
            'liveFestMode',
            'groups',
            'stages',
            'latestResults',
            'featuredPrograms',
            'announcements',
            'latestNews',
            'galleryPreview',
            'featuredVideo',
            'categories',
            'zones'
        ));
    }
}
