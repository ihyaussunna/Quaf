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
        // Programs delivered/sent by committee for stage announcement
        $results = Result::whereIn('status', ['send', 'delivered', 'published', 'announced'])
            ->with([
                'program.category',
                'program.stage',
                'firstEntry.student.group',
                'secondEntry.student.group',
                'thirdEntry.student.group',
            ])
            ->orderByRaw("CASE WHEN status = 'send' OR status = 'delivered' THEN 1 ELSE 2 END")
            ->latest('updated_at')
            ->get();

        return view('announcer.index', compact('results'));
    }

    public function markAnnounced(Result $result): RedirectResponse
    {
        $old = $result->status;
        $result->update(['status' => 'announced']);

        AuditLogger::log('announcer_announced_result', $result, ['status' => $old], ['status' => 'announced']);

        return back()->with('success', "Program '{$result->program->name}' marked as announced.");
    }
}
