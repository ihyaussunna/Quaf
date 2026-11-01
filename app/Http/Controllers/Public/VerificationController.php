<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CoinTransaction;
use App\Models\ProgramEntry;
use App\Models\Student;
use App\Services\CoinRewardService;
use App\Services\PointCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('public.verify');
    }

    public function verifyCertificate(string $certificateNumber): View
    {
        $certificate = Certificate::where('certificate_number', trim($certificateNumber))
            ->with(['student.group', 'program.category', 'entry', 'program.result'])
            ->first();

        $isMediaPublished = $certificate && $certificate->program && $certificate->program->result
            && $certificate->program->result->status === 'published'
            && (bool) $certificate->program->result->is_media_published;

        $isValid = (bool) $certificate && $isMediaPublished;

        return view('public.verify-certificate', compact('certificate', 'isValid', 'certificateNumber'));
    }

    public function verifyStudent(string $qrToken, CoinRewardService $coinRewardService): View
    {
        $clean = trim($qrToken);
        $stripped = ltrim($clean, '#');
        $numeric = preg_replace('/[^0-9]/', '', $clean);

        $studentQuery = Student::where('qr_token', $clean)
            ->orWhere('student_id', $clean)
            ->orWhereRaw('UPPER(student_id) = ?', [strtoupper($clean)])
            ->orWhere('student_id', 'QF'.$stripped)
            ->orWhere('student_id', 'QF-ST-'.$stripped)
            ->orWhere('student_id', 'QUAF-ST-'.$stripped);

        if (Schema::hasColumn('students', 'chest_number')) {
            $studentQuery->orWhere('chest_number', $clean)
                ->orWhere('chest_number', $stripped);
            if (! empty($numeric)) {
                $studentQuery->orWhere('chest_number', $numeric);
            }
        }

        if (! empty($numeric)) {
            $studentQuery->orWhere('student_id', 'QF'.$numeric)
                ->orWhere('student_id', 'QF-ST-'.$numeric)
                ->orWhere('student_id', $numeric);
        }

        // Also check if $clean matches chest_number on any program_entry
        $studentQuery->orWhereHas('entries', function ($eq) use ($clean, $stripped, $numeric) {
            $eq->where('chest_number', $clean)
                ->orWhere('chest_number', $stripped);
            if (! empty($numeric)) {
                $eq->orWhere('chest_number', $numeric);
            }
        });

        $student = $studentQuery->with([
            'group',
            'zone',
            'entries.program.category',
            'entries.program.stage',
            'entries.program.schedule',
            'entries.program.result',
            'participations.program.category',
            'participations.program.stage',
            'participations.program.schedule',
            'participations.program.result',
            'certificates.program',
        ])->first();

        $isValid = (bool) $student;
        $coinSummary = null;
        $studentAchievements = null;
        $purchaseHistory = collect();
        $coinTransactions = collect();
        $awardedPrograms = collect();
        $allPrograms = collect();
        $declaredStatuses = PointCalculationService::DECLARED_STATUSES;

        if ($student) {
            // Live coins auto-distribution and synchronization immediately upon student verification
            $coinRewardService->ensureCoinsDistributedForStudent($student);
            $student->refresh();

            // Scanner page is public: only show media-published coins and achievements
            $coinSummary = $coinRewardService->getStudentCoinSummary($student, true);
            $studentAchievements = $coinRewardService->getStudentAchievements($student, true);

            $allPrograms = $student->entries->merge($student->participations);
            if ($student->group_id) {
                $grpEntries = ProgramEntry::where('group_id', $student->group_id)
                    ->whereHas('program', fn ($q) => $q->where('type', 'group'))
                    ->with(['program.category', 'program.stage', 'program.schedule', 'program.result'])
                    ->get();
                $allPrograms = $allPrograms->merge($grpEntries);
            }
            $allPrograms = $allPrograms->unique('id');

            $purchaseHistory = CoinTransaction::where('student_id', $student->id)
                ->where('transaction_type', 'debit')
                ->latest()
                ->get();
            $coinTransactions = CoinTransaction::where('student_id', $student->id)
                ->where(function ($q) {
                    $q->whereNull('result_id')
                        ->orWhereHas('result', fn ($rq) => $rq->where('status', 'announced')->orWhere(fn ($sq) => $sq->where('status', 'published')->where('is_media_published', true)));
                })
                ->where(function ($q) {
                    $q->whereNull('program_id')
                        ->orDoesntHave('program.result')
                        ->orWhereHas('program.result', fn ($rq) => $rq->where('status', 'announced')->orWhere(fn ($sq) => $sq->where('status', 'published')->where('is_media_published', true)));
                })
                ->latest()
                ->take(15)
                ->get();
            $awardedPrograms = $this->resolveStudentAwardedPrograms($student);
        }

        return view('public.verify-student', compact(
            'student',
            'isValid',
            'qrToken',
            'coinSummary',
            'studentAchievements',
            'purchaseHistory',
            'coinTransactions',
            'awardedPrograms',
            'allPrograms'
        ));
    }

    /**
     * Resolve all competition programs where the student achieved 1st/2nd/3rd prize or a grade.
     *
     * @return Collection<int, array{
     *     program_id: int|null,
     *     name: string,
     *     code: string|null,
     *     category: string|null,
     *     position: int|null,
     *     position_title: string|null,
     *     grade: string|null
     * }>
     */
    protected function resolveStudentAwardedPrograms(Student $student): Collection
    {
        return app(CoinRewardService::class)->resolveStudentAwardedPrograms($student, true);
    }
}
