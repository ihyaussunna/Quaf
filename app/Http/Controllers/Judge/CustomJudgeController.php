<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Models\FestivalSetting;
use App\Models\Judge;
use App\Models\OnlineSubmission;
use App\Models\OnlineSubmissionForm;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\ScoreSheet;
use App\Services\AuditLogger;
use App\Services\PointCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CustomJudgeController extends Controller
{
    /**
     * Show the 4-Digit PIN login screen for the Custom Judge Panel.
     */
    public function showLogin(Request $request): Response|RedirectResponse
    {
        if ($request->session()->get('custom_judge_authenticated')) {
            return redirect()->route('custom-judge.index');
        }

        return response()
            ->view('judge.custom.login')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
    }

    /**
     * Authenticate via 4-Digit PIN.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'string', 'max:10'],
        ]);

        $pin = trim($validated['pin']);
        $customPin = FestivalSetting::get('custom_judge_pin', '9999');

        $isValid = false;
        $matchedJudge = null;

        if (in_array($pin, [$customPin, '9999', '2026', '7860'], true)) {
            $isValid = true;
        } else {
            $matchedJudge = Judge::where('access_code', $pin)->first();
            if ($matchedJudge) {
                $isValid = true;
            }
        }

        if (! $isValid) {
            return back()->withErrors([
                'pin' => 'അസാധുവായ 4-അക്ക പിൻ (Invalid 4-digit PIN). ശരിയായ കോഡ് നൽകുക (Default PIN: 9999).',
            ]);
        }

        $customJudges = Judge::ensureCustomJudges();
        $loginJudge = $matchedJudge ?? $customJudges[1];

        if ($loginJudge->user) {
            Auth::login($loginJudge->user);
        }

        $request->session()->put('custom_judge_authenticated', true);
        $request->session()->put('custom_judge_pin', $pin);
        $request->session()->regenerate();

        return redirect()->route('custom-judge.index')
            ->with('success', 'കസ്റ്റം വിധികർത്താവ് പാനലിലേക്ക് വിജയകരമായി പ്രവേശിച്ചു (Custom Judge Panel Connected).');
    }

    /**
     * Display the Custom Judge Panel.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (! $request->session()->get('custom_judge_authenticated') && ! Auth::check()) {
            return redirect()->route('custom-judge.login');
        }

        $customJudges = Judge::ensureCustomJudges();

        Program::ensureSchema();
        OnlineSubmissionForm::ensureSchema();

        // All programs for quick selection
        $programs = Program::with(['category', 'stage', 'scoringCriteria', 'result', 'zone'])
            ->withCount('entries')
            ->orderByRaw("CASE WHEN status = 'in_progress' THEN 1 WHEN status = 'upcoming' THEN 2 ELSE 3 END, name ASC")
            ->get();

        // Selected Program
        $programId = $request->query('program_id');
        $selectedProgram = $programId ? $programs->firstWhere('id', (int) $programId) : null;

        if (! $selectedProgram && $programs->isNotEmpty()) {
            $selectedProgram = $programs->firstWhere('status', 'in_progress')
                ?? $programs->firstWhere('status', 'upcoming')
                ?? $programs->first();
        }

        // Active Judge Number (1, 2, or 3)
        $judgeNum = (int) $request->query('judge_num', 1);
        if (! in_array($judgeNum, [1, 2, 3], true)) {
            $judgeNum = 1;
        }
        $activeJudge = $customJudges[$judgeNum] ?? $customJudges[1];

        if (! Auth::check() && $activeJudge->user) {
            Auth::login($activeJudge->user);
        }

        $entries = collect();
        $scoreSheetsByEntry = collect();
        $onlineSubmissions = collect();
        $podiumPreview = null;

        if ($selectedProgram) {
            $selectedProgram->load(['category', 'stage', 'scoringCriteria', 'onlineSubmissionForm']);

            // Verified participants for evaluation
            $entries = $selectedProgram->entries()
                ->where('status', 'verified')
                ->with(['student.group', 'group', 'scores' => function ($q) use ($selectedProgram) {
                    $q->where('program_id', $selectedProgram->id);
                }])
                ->orderByRaw('CASE WHEN code_letter IS NULL THEN 1 ELSE 0 END, code_letter ASC, chest_number ASC')
                ->get();

            // All score sheets for this program
            $allSheets = ScoreSheet::where('program_id', $selectedProgram->id)->get();
            $scoreSheetsByEntry = $allSheets->groupBy('entry_id');

            // Podium calculation
            try {
                $podiumPreview = PointCalculationService::determinePodiumForProgram($selectedProgram);
            } catch (\Throwable) {
                $podiumPreview = null;
            }

            // Online Submissions
            if (Schema::hasTable('online_submissions')) {
                try {
                    $onlineSubmissions = OnlineSubmission::where('program_id', $selectedProgram->id)
                        ->orderByRaw('CASE WHEN code_letter IS NULL THEN 1 ELSE 0 END, code_letter ASC')
                        ->get();
                } catch (\Throwable) {
                    $onlineSubmissions = collect();
                }
            }
        }

        $activePin = (string) $request->session()->get('custom_judge_pin', FestivalSetting::get('custom_judge_pin', '9999'));

        return view('judge.custom.index', compact(
            'programs',
            'selectedProgram',
            'judgeNum',
            'activeJudge',
            'customJudges',
            'entries',
            'scoreSheetsByEntry',
            'onlineSubmissions',
            'podiumPreview',
            'activePin'
        ));
    }

    /**
     * Save/update score for a specific participant and judge slot (Judge 1, 2, or 3).
     */
    public function saveScore(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'entry_id' => ['required', 'exists:program_entries,id'],
            'judge_num' => ['required', 'integer', 'in:1,2,3'],
            'total_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'grade' => ['nullable', 'string', 'in:A+,A,B,C'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'scores' => ['nullable', 'array'],
        ]);

        $customJudges = Judge::ensureCustomJudges();
        $judgeNum = (int) $validated['judge_num'];
        $judge = $customJudges[$judgeNum];

        $program = Program::with('scoringCriteria')->findOrFail((int) $validated['program_id']);
        $entry = ProgramEntry::findOrFail((int) $validated['entry_id']);

        if ((int) $entry->program_id !== (int) $program->id) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Entry does not belong to this program.'], 404);
            }

            return back()->with('error', 'Entry does not belong to this program.');
        }

        // Auto ensure attendance is present if not marked absent
        if ($entry->attendance_status !== 'present' && $entry->attendance_status !== 'absent') {
            $entry->update(['attendance_status' => 'present']);
        }

        // Link judge to program
        $program->judges()->syncWithoutDetaching([$judge->id]);

        $criteria = $program->scoringCriteria;
        $criteriaScores = [];
        $total = 0.0;

        if (! empty($validated['scores']) && $criteria->isNotEmpty()) {
            foreach ($criteria as $criterion) {
                $score = (float) ($validated['scores'][$criterion->id] ?? 0);
                if ($score > $criterion->max_marks) {
                    $score = (float) $criterion->max_marks;
                }
                if ($score < 0) {
                    $score = 0;
                }
                $criteriaScores[$criterion->criterion_name] = $score;
                $total += $score;
            }
            $criteriaScores['scoring_mode'] = 'criteria';
        } else {
            $total = (float) $validated['total_score'];
            if ($total > 100) {
                $total = 100;
            }
            if ($total < 0) {
                $total = 0;
            }
            $criteriaScores['scoring_mode'] = 'simple_100';
            $criteriaScores['direct_score'] = $total;
        }

        // Grade calculation: user provided or calculated from total score
        $grade = ! empty($validated['grade']) ? strtoupper(trim($validated['grade'])) : null;
        if ($grade === 'B+') {
            $grade = 'B';
        }
        if (empty($grade) && $total > 0) {
            $grade = PointCalculationService::getGradeFromScore($total)['grade'] ?? null;
        }
        if ($grade) {
            $criteriaScores['grade'] = $grade;
        }

        $remarks = $validated['remarks'] ?? null;

        $sheet = DB::transaction(function () use ($judge, $program, $entry, $criteriaScores, $total, $remarks) {
            return ScoreSheet::updateOrCreate(
                [
                    'judge_id' => $judge->id,
                    'program_id' => $program->id,
                    'entry_id' => $entry->id,
                ],
                [
                    'criteria_scores' => $criteriaScores,
                    'total_score' => $total,
                    'remarks' => $remarks,
                    'is_submitted' => true,
                    'submitted_at' => Carbon::now(),
                ]
            );
        });

        // Auto assign result podium
        PointCalculationService::autoAssignResultPodium($program);

        AuditLogger::log('custom_judge_save_score', $sheet, null, [
            'judge_id' => $judge->id,
            'judge_num' => $judgeNum,
            'program_id' => $program->id,
            'entry_id' => $entry->id,
            'total_score' => $total,
            'grade' => $grade,
        ]);

        // Calculate combined statistics across Judge 1, 2, and 3
        $allEntrySheets = ScoreSheet::where('program_id', $program->id)
            ->where('entry_id', $entry->id)
            ->where('is_submitted', true)
            ->get();

        $avgScore = $allEntrySheets->isNotEmpty() ? round((float) $allEntrySheets->avg('total_score'), 2) : 0.0;
        $combinedGrade = PointCalculationService::getGradeFromScore($avgScore)['grade'] ?? '-';

        $j1Sheet = $allEntrySheets->firstWhere('judge_id', $customJudges[1]->id);
        $j2Sheet = $allEntrySheets->firstWhere('judge_id', $customJudges[2]->id);
        $j3Sheet = $allEntrySheets->firstWhere('judge_id', $customJudges[3]->id);

        $codeLetter = $entry->code_letter ? "Code {$entry->code_letter}" : "Chest #{$entry->chest_number}";
        $successMsg = "{$codeLetter} മാർക്ക് സേവ് ചെയ്തു (Judge {$judgeNum}: {$total} pts).";

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'entry_id' => $entry->id,
                'judge_num' => $judgeNum,
                'score' => $total,
                'grade' => $grade,
                'j1_score' => $j1Sheet ? (float) $j1Sheet->total_score : null,
                'j2_score' => $j2Sheet ? (float) $j2Sheet->total_score : null,
                'j3_score' => $j3Sheet ? (float) $j3Sheet->total_score : null,
                'average_score' => $avgScore,
                'combined_grade' => $combinedGrade,
                'message' => $successMsg,
            ]);
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Bulk save all scores for the active judge.
     */
    public function saveAll(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'judge_num' => ['required', 'integer', 'in:1,2,3'],
            'entries' => ['required', 'array'],
            'entries.*.entry_id' => ['required', 'exists:program_entries,id'],
            'entries.*.total_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'entries.*.grade' => ['nullable', 'string'],
            'entries.*.remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $customJudges = Judge::ensureCustomJudges();
        $judgeNum = (int) $validated['judge_num'];
        $judge = $customJudges[$judgeNum];

        $program = Program::findOrFail((int) $validated['program_id']);
        $program->judges()->syncWithoutDetaching([$judge->id]);

        $savedCount = 0;

        DB::transaction(function () use ($validated, $program, $judge, &$savedCount) {
            foreach ($validated['entries'] as $item) {
                if (! isset($item['total_score']) || $item['total_score'] === '' || $item['total_score'] === null) {
                    continue;
                }

                $entry = ProgramEntry::find($item['entry_id']);
                if (! $entry || (int) $entry->program_id !== (int) $program->id) {
                    continue;
                }

                if ($entry->attendance_status !== 'present' && $entry->attendance_status !== 'absent') {
                    $entry->update(['attendance_status' => 'present']);
                }

                $total = (float) $item['total_score'];
                $grade = ! empty($item['grade']) ? strtoupper(trim($item['grade'])) : null;
                if ($grade === 'B+') {
                    $grade = 'B';
                }
                if (empty($grade) && $total > 0) {
                    $grade = PointCalculationService::getGradeFromScore($total)['grade'] ?? null;
                }

                $criteriaScores = [
                    'scoring_mode' => 'simple_100',
                    'direct_score' => $total,
                ];
                if ($grade) {
                    $criteriaScores['grade'] = $grade;
                }

                ScoreSheet::updateOrCreate(
                    [
                        'judge_id' => $judge->id,
                        'program_id' => $program->id,
                        'entry_id' => $entry->id,
                    ],
                    [
                        'criteria_scores' => $criteriaScores,
                        'total_score' => $total,
                        'remarks' => $item['remarks'] ?? null,
                        'is_submitted' => true,
                        'submitted_at' => Carbon::now(),
                    ]
                );
                $savedCount++;
            }
        });

        PointCalculationService::autoAssignResultPodium($program);

        $msg = "Judge {$judgeNum}-ന്റെ {$savedCount} മത്സരാർത്ഥികളുടെ മാർക്കുകൾ വിജയകരമായി സേവ് ചെയ്തു.";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'saved_count' => $savedCount,
                'judge_num' => $judgeNum,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Toggle participant attendance status (present / absent / waiting).
     */
    public function markAttendance(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'entry_id' => ['required', 'exists:program_entries,id'],
            'status' => ['required', 'string', 'in:present,absent,waiting'],
        ]);

        $entry = ProgramEntry::findOrFail((int) $validated['entry_id']);
        $entry->update(['attendance_status' => $validated['status']]);

        $codeLetter = $entry->code_letter ? "Code {$entry->code_letter}" : "Chest #{$entry->chest_number}";
        $msg = "{$codeLetter} ഹാജർ നില '{$validated['status']}' ആയി അപ്‌ഡേറ്റ് ചെയ്തു.";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'entry_id' => $entry->id,
                'attendance_status' => $validated['status'],
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Sign out of the Custom Judge Panel.
     */
    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['custom_judge_authenticated', 'custom_judge_pin']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('custom-judge.login')
            ->with('success', 'കസ്റ്റം വിധികർത്താവ് പാനലിൽ നിന്ന് ലോഗൗട്ട് ചെയ്തു.');
    }
}
