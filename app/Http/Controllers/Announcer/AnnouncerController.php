<?php

namespace App\Http\Controllers\Announcer;

use App\Http\Controllers\Controller;
use App\Models\GreenRoomCall;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Models\Stage;
use App\Models\StandingAnnouncement;
use App\Services\AuditLogger;
use App\Services\PointCalculationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AnnouncerController extends Controller
{
    public function index(Request $request): View
    {
        Result::ensureSchema();

        $tab = $request->query('tab', 'ready');
        $search = trim((string) $request->query('search'));

        $query = Result::with([
            'program.category',
            'program.stage',
            'program.zone',
            'firstEntry.student.group',
            'firstEntry.group',
            'firstEntry.scoreSheets',
            'secondEntry.student.group',
            'secondEntry.group',
            'secondEntry.scoreSheets',
            'thirdEntry.student.group',
            'thirdEntry.group',
            'thirdEntry.scoreSheets',
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

        $pendingStandings = collect();
        $announcedStandings = collect();
        try {
            StandingAnnouncement::ensureSchema();
            if (Schema::hasTable('standing_announcements')) {
                $pendingStandings = StandingAnnouncement::where('status', 'ready')->latest()->get();
                $announcedStandings = StandingAnnouncement::whereIn('status', ['announced', 'published'])->latest()->take(6)->get();
            }
        } catch (\Throwable) {
            $pendingStandings = collect();
            $announcedStandings = collect();
        }

        return view('announcer.index', compact('results', 'stats', 'tab', 'search', 'pendingStandings', 'announcedStandings'));
    }

    public function markStandingAnnounced(StandingAnnouncement $standingAnnouncement): RedirectResponse
    {
        $standingAnnouncement->markAsAnnounced(auth()->id());

        AuditLogger::log('announcer_announced_standing', $standingAnnouncement, ['status' => 'ready'], ['status' => 'announced']);

        return back()->with('success', "Team standings ({$standingAnnouncement->title}) announced on stage and transferred to Media Desk.");
    }

    public function markAnnounced(Result $result): RedirectResponse
    {
        $old = $result->status;

        // Auto-assign top 3 winner entries from recorded scores if not already set
        if (! $result->first_entry_id && $result->program_id) {
            $topEntries = ProgramEntry::where('program_id', $result->program_id)
                ->with('scores')
                ->get()
                ->sortByDesc(fn ($e) => $e->scores->sum('total_score'))
                ->values();

            if ($topEntries->isNotEmpty() && $topEntries[0]->scores->sum('total_score') > 0) {
                $result->first_entry_id = $topEntries[0]->id;
                $result->second_entry_id = $topEntries->get(1)?->id;
                $result->third_entry_id = $topEntries->get(2)?->id;
            }
        }

        $rawNumber = $result->getRawOriginal('result_number');
        if (empty($rawNumber) || (int) $rawNumber <= 0) {
            $max = Result::getSafeMaxResultNumber();
            $result->result_number = $max + 1;
        }

        $result->status = 'announced';
        $result->is_media_published = false;
        $result->save();

        app(PointCalculationService::class)->recalculateForProgram($result->program_id);

        AuditLogger::log('announcer_announced_result', $result, ['status' => $old], ['status' => 'announced']);

        return back()->with('success', "Program '{$result->program->name}' marked as announced and transferred to Media Desk.");
    }

    public function stageCalling(Request $request): View
    {
        $stages = Stage::all();
        $selectedStageId = $request->query('stage_id', $stages->first()?->id);
        $stage = Stage::with(['currentProgram.category', 'nextProgram.category'])->find($selectedStageId);

        $currentProgram = $stage?->currentProgram;
        $nextProgram = $stage?->nextProgram;
        $activeProgram = $currentProgram ?? $nextProgram;

        $calls = collect();
        $currentPerformer = null;
        $nextPerformer = null;

        if ($activeProgram) {
            // Get verified entries with their green room call record
            $calls = GreenRoomCall::where('green_room_calls.program_id', $activeProgram->id)
                ->with(['entry.student.group', 'entry.group', 'entry.program'])
                ->join('program_entries', 'green_room_calls.entry_id', '=', 'program_entries.id')
                ->orderByRaw('CASE WHEN program_entries.code_letter IS NULL THEN 1 ELSE 0 END, program_entries.code_letter ASC, green_room_calls.order_num ASC')
                ->select('green_room_calls.*')
                ->get();

            $currentPerformer = $calls->firstWhere('status', 'on_stage');
            $nextPerformer = $calls->whereIn('status', ['called', 'ready', 'checked_in', 'waiting'])->first();
        }

        return view('announcer.stage', compact(
            'stages',
            'stage',
            'selectedStageId',
            'currentProgram',
            'nextProgram',
            'activeProgram',
            'calls',
            'currentPerformer',
            'nextPerformer'
        ));
    }

    public function callToStage(GreenRoomCall $call): RedirectResponse
    {
        $call->update([
            'status' => 'called',
            'called_at' => Carbon::now(),
        ]);

        AuditLogger::log('announcer_call_stage', $call, null, ['status' => 'called']);

        return back()->with('success', "Code {$call->entry?->code_letter} (Chest #{$call->entry?->chest_number}) called to stage.");
    }

    public function enterStage(GreenRoomCall $call): RedirectResponse
    {
        // Mark any currently on_stage as completed
        GreenRoomCall::where('program_id', $call->program_id)
            ->where('status', 'on_stage')
            ->where('id', '!=', $call->id)
            ->update(['status' => 'completed']);

        $call->update([
            'status' => 'on_stage',
            'stage_entered_at' => Carbon::now(),
        ]);

        AuditLogger::log('announcer_enter_stage', $call, null, ['status' => 'on_stage']);

        return back()->with('success', "Code {$call->entry?->code_letter} (Chest #{$call->entry?->chest_number}) entered the stage.");
    }

    public function completeStage(GreenRoomCall $call): RedirectResponse
    {
        $call->update([
            'status' => 'completed',
        ]);

        AuditLogger::log('announcer_complete_stage', $call, null, ['status' => 'completed']);

        return back()->with('success', "Code {$call->entry?->code_letter} (Chest #{$call->entry?->chest_number}) marked performance complete.");
    }

    /**
     * Quick update for declared grades from the Announcer Desk.
     */
    public function updateGrades(Request $request, Result $result): RedirectResponse
    {
        $validated = $request->validate([
            'first_grade' => ['nullable', 'string', 'max:10'],
            'second_grade' => ['nullable', 'string', 'max:10'],
            'third_grade' => ['nullable', 'string', 'max:10'],
        ]);

        $firstGrade = $validated['first_grade'] === 'NO_GRADE' ? null : ($validated['first_grade'] ?? $result->first_grade);
        $secondGrade = $validated['second_grade'] === 'NO_GRADE' ? null : ($validated['second_grade'] ?? $result->second_grade);
        $thirdGrade = $validated['third_grade'] === 'NO_GRADE' ? null : ($validated['third_grade'] ?? $result->third_grade);

        $result->update([
            'first_grade' => $firstGrade,
            'second_grade' => $secondGrade,
            'third_grade' => $thirdGrade,
        ]);

        // Recalculate points for this program to ensure synchronization
        app(PointCalculationService::class)->recalculateForProgram($result->program_id);

        AuditLogger::log('announcer_update_grades', $result, [], [
            'first_grade' => $firstGrade,
            'second_grade' => $secondGrade,
            'third_grade' => $thirdGrade,
        ]);

        return back()->with('success', "Grades for program '{$result->program?->name}' updated successfully.");
    }
}
