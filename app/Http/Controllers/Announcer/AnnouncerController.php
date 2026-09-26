<?php

namespace App\Http\Controllers\Announcer;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncerController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'ready');
        $search = trim((string) $request->query('search'));

        $query = Result::with([
            'program.category',
            'program.stage',
            'firstEntry.student.group',
            'secondEntry.student.group',
            'thirdEntry.student.group',
        ]);

        if ($search) {
            $query->whereHas('program', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('malayalam_name', 'like', "%{$search}%");
            });
        }

        if ($tab === 'ready') {
            $query->whereIn('status', ['send', 'delivered']);
        } elseif ($tab === 'announced') {
            $query->whereIn('status', ['announced', 'published']);
        } else {
            $query->whereIn('status', ['send', 'delivered', 'announced', 'published']);
        }

        $results = $query->orderByRaw("CASE WHEN status IN ('send', 'delivered') THEN 1 ELSE 2 END")
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'ready_count' => Result::whereIn('status', ['send', 'delivered'])->count(),
            'announced_count' => Result::whereIn('status', ['announced', 'published'])->count(),
            'total_count' => Result::whereIn('status', ['send', 'delivered', 'announced', 'published'])->count(),
        ];

        return view('announcer.index', compact('results', 'stats', 'tab', 'search'));
    }

    public function markAnnounced(Result $result): RedirectResponse
    {
        $old = $result->status;
        $result->update(['status' => 'announced']);

        AuditLogger::log('announcer_announced_result', $result, ['status' => $old], ['status' => 'announced']);

        return back()->with('success', "പ്രോഗ്രാം '{$result->program->name}' അനൗൺസ് ചെയ്തു. റിസൾട്ട് മീഡിയ ഡെസ്കിലേക്ക് കൈമാറിയിരിക്കുന്നു.");
    }
}
