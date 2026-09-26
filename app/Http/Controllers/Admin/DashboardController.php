<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Group;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Stage;
use App\Models\Student;
use App\Services\PointCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // 6 Primary Operational Statistics
        $stats = [
            'participants' => Student::count(),
            'competitions' => Program::count(),
            'teams' => Group::count(),
            'categories' => ProgramCategory::count(),
            'stages' => Stage::count(),
            'judges' => Judge::count(),
            // Additional operational metrics
            'active_stages' => Stage::where('status', 'active')->count(),
            'pending_registrations' => ProgramEntry::where('status', 'pending')->count(),
            'pending_results' => Result::whereIn('status', ['submitted', 'under_review'])->count(),
            'published_results' => Result::where('status', 'published')->count(),
            'programs_in_progress' => Program::where('status', 'in_progress')->count(),
        ];

        // Real-time Leaderboard of all groups
        $leaderboard = Group::withCount(['students', 'entries'])
            ->orderBy('rank_cache')
            ->orderByDesc('points_cache')
            ->get();

        $activePrograms = Program::where('status', 'in_progress')
            ->with(['category', 'stage', 'schedule'])
            ->get();

        $upcomingPrograms = Program::where('status', 'upcoming')
            ->with(['category', 'stage', 'schedule'])
            ->orderBy('scheduled_time')
            ->take(5)
            ->get();

        $stages = Stage::with(['currentProgram', 'nextProgram'])->get();

        $recentAnnouncements = Announcement::latest()->take(4)->get();

        $recentAuditLogs = AuditLog::with('user')->latest()->take(6)->get();

        return view('admin.dashboard', compact(
            'stats',
            'leaderboard',
            'activePrograms',
            'upcomingPrograms',
            'stages',
            'recentAnnouncements',
            'recentAuditLogs'
        ));
    }

    public function syncFestivalData(): RedirectResponse
    {
        try {
            @set_time_limit(300);

            // 1. Run migrations safely
            Artisan::call('migrate', ['--force' => true]);

            // 2. Run DatabaseSeeder (which is now 100% idempotent)
            Artisan::call('db:seed', ['--force' => true]);

            // 3. Sync 144 official programs
            Artisan::call('app:sync-official-programs');

            // 4. Seed Conco Majdic 236 students
            Artisan::call('db:seed', ['--class' => 'ConcoMajdicStudentsSeeder', '--force' => true]);

            // 5. Seed Passwords
            Artisan::call('db:seed', ['--class' => 'PanelPasswordsSeeder', '--force' => true]);

            // 6. Recalculate Points
            app(PointCalculationService::class)->recalculateAllPoints();

            // 7. Clear view & optimize caches
            Artisan::call('optimize:clear');
            Cache::flush();

            $progCount = Program::count();
            $studCount = Student::count();
            $grpCount = Group::count();

            return redirect()->route('admin.dashboard')->with('success', "Festival data synced successfully! {$progCount} Programs, {$grpCount} Groups, and {$studCount} Students are active.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.dashboard')->with('error', 'Sync failed: '.$e->getMessage());
        }
    }
}
