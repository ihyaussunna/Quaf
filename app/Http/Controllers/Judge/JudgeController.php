<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Models\Judge;
use App\Models\Program;
use App\Models\ProgramEntry;
use App\Models\ScoreSheet;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\PointCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JudgeController extends Controller
{
    public function showPinLogin(Request $request): Response
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()
            ->view('judge.login')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
    }

    public function pinLogin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'string', 'max:10'],
        ]);

        $judge = Judge::where('access_code', $validated['pin'])->first();

        if (! $judge) {
            return back()->withErrors([
                'pin' => 'അസാധുവായ 4-അക്ക പിൻ (Invalid 4-digit PIN). ശരിയായ പിൻ നൽകുക അല്ലെങ്കിൽ ഫെസ്റ്റിവൽ ഡെസ്കുമായി ബന്ധപ്പെടുക.',
            ]);
        }

        // Ensure Judge has a User account to log into
        if (! $judge->user_id || ! $judge->user) {
            $user = User::create([
                'name' => $judge->name,
                'email' => Str::slug($judge->name).'_'.$judge->id.'@judge.quaf.org',
                'password' => Hash::make(Str::random(16)),
                'role' => 'judge',
                'phone' => $judge->contact,
                'is_active' => true,
            ]);
            $judge->update(['user_id' => $user->id]);
            $judge->setRelation('user', $user);
        }

        Auth::login($judge->user);
        $request->session()->regenerate();

        return redirect()->route('judge.dashboard')->with('success', "സ്വാഗതം, {$judge->name}! വിധികർത്താവ് പാനലിലേക്ക് വിജയകരമായി ലോഗിൻ ചെയ്തു.");
    }

    protected function getJudge(): Judge
    {
        $judge = Judge::where('user_id', Auth::id())->first();
        if (! $judge && in_array(Auth::user()?->role, ['admin', 'super_admin'])) {
            $judge = Judge::first();
        }

        if (! $judge) {
            abort(403, 'No judge profile found for this user account. Please log in with a Judge account.');
        }

        return $judge;
    }

    public function dashboard(): View
    {
        $judge = $this->getJudge();

        $assignedPrograms = $judge->programs()
            ->with(['category', 'stage', 'schedule', 'scoringCriteria', 'result'])
            ->withCount('entries')
            ->get();

        $upcoming = $assignedPrograms->where('status', 'upcoming');
        $inProgress = $assignedPrograms->where('status', 'in_progress');
        $completed = $assignedPrograms->where('status', 'completed');

        Program::ensureSchema();

        $progIds = $assignedPrograms->pluck('id');

        // Present verified participants count per program
        $presentCounts = ProgramEntry::whereIn('program_id', $progIds)
            ->where('status', 'verified')
            ->where('attendance_status', 'present')
            ->groupBy('program_id')
            ->selectRaw('program_id, count(*) as total')
            ->pluck('total', 'program_id');

        // Submitted scores count by this judge for present participants
        $submittedCounts = ScoreSheet::where('judge_id', $judge->id)
            ->whereIn('program_id', $progIds)
            ->where('is_submitted', true)
            ->whereHas('entry', function ($eq) {
                $eq->where('attendance_status', 'present');
            })
            ->groupBy('program_id')
            ->selectRaw('program_id, count(*) as total')
            ->pluck('total', 'program_id');

        $evaluationStatus = [];
        foreach ($assignedPrograms as $prog) {
            $present = (int) ($presentCounts[$prog->id] ?? 0);
            $submittedScores = (int) ($submittedCounts[$prog->id] ?? 0);
            $pending = max(0, $present - $submittedScores);

            $evaluationStatus[$prog->id] = [
                'total' => $present,
                'present' => $present,
                'submitted' => $submittedScores,
                'pending' => $pending,
                'is_complete' => ($present > 0 && $submittedScores >= $present),
            ];
        }

        return view('judge.dashboard', compact('judge', 'assignedPrograms', 'upcoming', 'inProgress', 'completed', 'evaluationStatus'));
    }

    public function showProgram(Program $program): View
    {
        $judge = $this->getJudge();

        // Security check: Judge cannot access unassigned programs
        if (! $judge->programs()->where('programs.id', $program->id)->exists() && ! in_array(Auth::user()?->role, ['admin', 'super_admin'])) {
            abort(403, 'Unauthorized. You are not assigned to evaluate this program.');
        }

        $program->load([
            'category',
            'stage',
            'schedule',
            'scoringCriteria',
        ]);

        // STRICT ANONYMITY & ATTENDANCE FILTER:
        // Do NOT load student names, photos, or groups to preserve total anonymity.
        // ONLY present participants with attendance_status = 'present' are eligible for evaluation.
        // Absent participants are strictly excluded from evaluation sheet.
        $entries = $program->entries()
            ->where('status', 'verified')
            ->where('attendance_status', 'present')
            ->orderByRaw('CASE WHEN code_letter IS NULL THEN 1 ELSE 0 END, code_letter ASC, chest_number ASC')
            ->get();

        // Load judge's existing score sheets for these entries
        $scoreSheets = ScoreSheet::where('judge_id', $judge->id)
            ->where('program_id', $program->id)
            ->get()
            ->keyBy('entry_id');

        return view('judge.evaluate', compact('judge', 'program', 'entries', 'scoreSheets'));
    }

    public function saveScore(Request $request, Program $program, ProgramEntry $entry): JsonResponse|RedirectResponse
    {
        $judge = $this->getJudge();

        if (! $judge->programs()->where('programs.id', $program->id)->exists() && ! in_array(Auth::user()?->role, ['admin', 'super_admin'])) {
            abort(403, 'Unauthorized.');
        }

        if ((int) $entry->program_id !== (int) $program->id) {
            abort(404, 'Participant entry does not belong to this program.');
        }

        // STRICT REQUIREMENT 8: Prevent Invalid Evaluation
        // Backend validation: IF participant attendance != PRESENT THEN evaluation submission must be rejected
        if ($entry->attendance_status !== 'present') {
            $errMsg = 'ഹാജരില്ലാത്ത (ABSENT/WAITING) മത്സരാർത്ഥിക്ക് മാർക്ക് നൽകാൻ സാധ്യമല്ല. (Evaluation rejected: participant attendance is not PRESENT).';
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errMsg,
                ], 422);
            }

            return back()->with('error', $errMsg);
        }

        $criteria = $program->scoringCriteria;
        $criteriaScores = [];
        $total = 0;
        $scoringMode = $request->input('scoring_mode', 'criteria'); // 'simple' or 'criteria'

        if ($scoringMode === 'simple' || $criteria->isEmpty() || (! $request->has('scores') && $request->has('total_score'))) {
            $total = (float) $request->input('total_score', $request->input('score', 0));
            if ($total > 100) {
                $total = 100;
            }
            if ($total < 0) {
                $total = 0;
            }
            $criteriaScores['scoring_mode'] = 'simple_100';
            $criteriaScores['direct_score'] = $total;
        } else {
            foreach ($criteria as $criterion) {
                $score = (float) $request->input("scores.{$criterion->id}", 0);
                if ($score > $criterion->max_marks) {
                    $score = $criterion->max_marks;
                }
                if ($score < 0) {
                    $score = 0;
                }
                $criteriaScores[$criterion->criterion_name] = $score;
                $total += $score;
            }
            $criteriaScores['scoring_mode'] = 'criteria';
        }

        // Judge Grade option (A+, A, B, C) - strictly no B+
        $grade = null;
        $gradeInput = $request->input('grade');
        if (! empty($gradeInput)) {
            $normalizedGrade = strtoupper(trim($gradeInput));
            if ($normalizedGrade === 'B+') {
                $normalizedGrade = 'B';
            }
            if (in_array($normalizedGrade, ['A+', 'A', 'B', 'C'], true)) {
                $grade = $normalizedGrade;
            }
        }
        if (empty($grade) && $total > 0) {
            $grade = PointCalculationService::getGradeFromScore($total)['grade'] ?? null;
        }
        if ($grade) {
            $criteriaScores['grade'] = $grade;
        }

        $remarks = $request->input('remarks');

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

        // Automatically determine and synchronize podium winners (1st, 2nd, 3rd) from judge scores
        $existingResult = $program->result;
        if (! $existingResult || ! in_array($existingResult->status, ['published', 'announced'], true)) {
            PointCalculationService::autoAssignResultPodium($program);
        }

        AuditLogger::log('judge_submit_score', $sheet, null, [
            'judge_id' => $judge->id,
            'program_id' => $program->id,
            'entry_id' => $entry->id,
            'total_score' => $total,
            'grade' => $grade,
        ]);

        $codeName = $entry->code_letter ? "Code {$entry->code_letter}" : "Participant #{$entry->id}";
        $gradeDisplay = $grade ? " • Grade {$grade}" : '';
        $message = "Evaluation saved for {$codeName} (Score: {$total}{$gradeDisplay}).";

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'entry_id' => $entry->id,
                'total_score' => $total,
                'grade' => $grade,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
