<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Group;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\Result;
use App\Services\AuditLogger;
use App\Services\PointCalculationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function __construct(
        protected PointCalculationService $pointService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Result::with([
            'program.category',
            'firstEntry.student.group',
            'secondEntry.student.group',
            'thirdEntry.student.group',
            'verifier',
        ]);

        if ($status === 'published') {
            $query->where('status', 'published');
        } elseif ($status === 'submitted') {
            $query->where('status', 'submitted');
        } elseif ($status === 'in_progress') {
            $query->whereIn('status', ['draft', 'under_review']);
        } elseif ($status === 'pending') {
            $query->where('status', 'draft');
        }

        if ($search) {
            $query->whereHas('program', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
        }

        $results = $query->latest()->paginate(15)->withQueryString();
        $pendingPrograms = Program::doesntHave('result')->orderBy('name')->get();
        $teams = Group::orderByDesc('points_cache')->get();

        $counts = [
            'all' => Result::count(),
            'pending' => Program::doesntHave('result')->count() + Result::where('status', 'draft')->count(),
            'in_progress' => Result::whereIn('status', ['draft', 'under_review'])->count(),
            'submitted' => Result::where('status', 'submitted')->count(),
            'published' => Result::where('status', 'published')->count(),
        ];

        return view('admin.results.index', compact('results', 'pendingPrograms', 'status', 'search', 'teams', 'counts'));
    }

    public function create(Request $request): View
    {
        $programId = $request->query('program_id');
        $program = Program::with(['entries.student.group', 'entries.scoreSheets', 'scoringCriteria'])
            ->findOrFail($programId);

        return view('admin.results.create', compact('program'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id', 'unique:results,program_id'],
            'first_entry_id' => ['required', 'exists:program_entries,id'],
            'second_entry_id' => ['nullable', 'exists:program_entries,id'],
            'third_entry_id' => ['nullable', 'exists:program_entries,id'],
            'status' => ['required', 'in:draft,submitted,under_review,verified,published'],
            'remarks' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'published') {
            $validated['published_at'] = Carbon::now();
            $validated['verified_by'] = Auth::id();
        }

        $result = Result::create($validated);

        AuditLogger::log('create_result', $result, null, $result->toArray());

        if ($result->status === 'published') {
            $this->issueCertificates($result);
            $this->pointService->recalculateAllPoints();
        }

        return redirect()->route('admin.results.index')->with('success', "Result for '{$result->program->name}' saved ({$result->status}).");
    }

    public function edit(Result $result): View
    {
        $result->load([
            'program.entries.student.group',
            'program.entries.scoreSheets',
            'firstEntry',
            'secondEntry',
            'thirdEntry',
        ]);

        return view('admin.results.edit', compact('result'));
    }

    public function update(Request $request, Result $result): RedirectResponse
    {
        $validated = $request->validate([
            'first_entry_id' => ['required', 'exists:program_entries,id'],
            'second_entry_id' => ['nullable', 'exists:program_entries,id'],
            'third_entry_id' => ['nullable', 'exists:program_entries,id'],
            'status' => ['required', 'in:draft,submitted,under_review,verified,published'],
            'remarks' => ['nullable', 'string'],
        ]);

        $old = $result->toArray();

        if ($validated['status'] === 'published' && $result->status !== 'published') {
            $validated['published_at'] = Carbon::now();
            $validated['verified_by'] = Auth::id();
        }

        $result->update($validated);

        AuditLogger::log('update_result', $result, $old, $result->toArray());

        if ($result->status === 'published') {
            $this->issueCertificates($result);
            $this->pointService->recalculateAllPoints();
        }

        return redirect()->route('admin.results.index')->with('success', "Result for '{$result->program->name}' updated ({$result->status}).");
    }

    public function publish(Result $result): RedirectResponse
    {
        $old = $result->toArray();
        $result->update([
            'status' => 'published',
            'published_at' => Carbon::now(),
            'verified_by' => Auth::id(),
        ]);

        $result->program->update(['status' => 'completed']);

        $this->issueCertificates($result);
        $this->pointService->recalculateAllPoints();

        AuditLogger::log('publish_result', $result, $old, $result->toArray());

        return back()->with('success', "Result for '{$result->program->name}' is now PUBLISHED and points have been calculated.");
    }

    protected function issueCertificates(Result $result): void
    {
        $program = $result->program;

        $placements = [
            '1st Place' => $result->firstEntry,
            '2nd Place' => $result->secondEntry,
            '3rd Place' => $result->thirdEntry,
        ];

        foreach ($placements as $pos => $entry) {
            if ($entry && $entry->student_id) {
                $certNum = 'QUAF09-'.strtoupper(Str::slug($program->code)).'-'.$entry->chest_number;
                Certificate::firstOrCreate(
                    ['certificate_number' => $certNum],
                    [
                        'entry_id' => $entry->id,
                        'student_id' => $entry->student_id,
                        'program_id' => $program->id,
                        'position' => $pos,
                        'issued_at' => Carbon::now(),
                        'qr_verification_url' => route('verify.certificate', $certNum),
                    ]
                );
            }
        }
    }

    public function declareIndex(Request $request): View
    {
        $search = $request->query('search');

        $query = Program::with(['category', 'stage', 'result'])
            ->whereHas('result', fn ($q) => $q->whereIn('status', ['verified', 'send', 'announced']));

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.results.declare', compact('programs', 'search'));
    }

    public function declaredResults(Request $request): View
    {
        $search = $request->query('search');

        $query = Result::with(['program.category', 'program.stage', 'firstEntry.student', 'secondEntry.student', 'thirdEntry.student'])
            ->whereIn('status', ['published', 'announced']);

        if ($search) {
            $query->whereHas('program', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $results = $query->latest('published_at')->paginate(20)->withQueryString();

        return view('admin.results.declared', compact('results', 'search'));
    }

    public function undeclare(Result $result): RedirectResponse
    {
        $programName = $result->program?->name ?? 'Program';
        $result->delete();

        $this->pointService->recalculateAllPoints();

        AuditLogger::log('undeclare_result', null, ['program' => $programName], null);

        return back()->with('success', "Result for '{$programName}' has been undeclared.");
    }

    public function specified(Request $request): View
    {
        $programQuery = $request->query('program_id');
        $selectedProgram = null;
        $rankedEntries = collect();

        if ($programQuery) {
            $selectedProgram = Program::with(['category', 'stage', 'result'])
                ->where('id', $programQuery)
                ->orWhere('code', $programQuery)
                ->orWhere('name', 'like', "%{$programQuery}%")
                ->first();

            if ($selectedProgram) {
                $entries = ProgramEntry::where('program_id', $selectedProgram->id)
                    ->with(['student.group', 'group', 'scores'])
                    ->get()
                    ->map(function ($entry) {
                        $score = (float) $entry->scores->avg('total_score');
                        $entry->computed_score = $score;
                        if ($score >= 80) {
                            $entry->computed_grade = 'A+';
                        } elseif ($score >= 70) {
                            $entry->computed_grade = 'A';
                        } elseif ($score >= 55) {
                            $entry->computed_grade = 'B';
                        } elseif ($score >= 40) {
                            $entry->computed_grade = 'C';
                        } else {
                            $entry->computed_grade = '-';
                        }

                        return $entry;
                    })
                    ->sortByDesc('computed_score')
                    ->values();

                foreach ($entries as $index => $item) {
                    if ($index === 0 && $item->computed_score > 0) {
                        $item->computed_rank = 'first';
                    } elseif ($index === 1 && $item->computed_score > 0) {
                        $item->computed_rank = 'second';
                    } elseif ($index === 2 && $item->computed_score > 0) {
                        $item->computed_rank = 'third';
                    } else {
                        $item->computed_rank = '-';
                    }
                }
                $rankedEntries = $entries;
            }
        }

        return view('admin.results.specified', compact('selectedProgram', 'rankedEntries', 'programQuery'));
    }

    public function allResults(Request $request): View
    {
        $search = $request->query('search');

        $query = Program::with(['category', 'stage', 'result'])
            ->whereHas('result');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.results.all', compact('programs', 'search'));
    }
}
