<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CoinTransaction;
use App\Models\Student;
use App\Services\CoinRewardService;
use App\Services\PointCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        $student = Student::where('qr_token', $clean)
            ->orWhere('student_id', $clean)
            ->orWhereRaw('UPPER(student_id) = ?', [strtoupper($clean)])
            ->orWhere('student_id', 'QF'.$clean)
            ->with([
                'group',
                'zone',
                'entries.program.category',
                'entries.program.stage',
                'entries.program.schedule',
                'certificates.program',
            ])
            ->first();

        $isValid = (bool) $student;
        $coinSummary = null;
        $studentAchievements = null;
        $purchaseHistory = collect();
        $coinTransactions = collect();
        $awardedPrograms = collect();
        $declaredStatuses = PointCalculationService::DECLARED_STATUSES;

        if ($student) {
            // Live coins and achievements immediately available upon result announcement
            $coinSummary = $coinRewardService->getStudentCoinSummary($student, false);
            $studentAchievements = $coinRewardService->getStudentAchievements($student, false);
            $purchaseHistory = CoinTransaction::where('student_id', $student->id)
                ->where('transaction_type', 'debit')
                ->latest()
                ->get();
            $coinTransactions = CoinTransaction::where('student_id', $student->id)
                ->where(function ($q) use ($declaredStatuses) {
                    $q->whereNull('result_id')
                        ->orWhereHas('result', fn ($rq) => $rq->whereIn('status', $declaredStatuses));
                })
                ->where(function ($q) use ($declaredStatuses) {
                    $q->whereNull('program_id')
                        ->orDoesntHave('program.result')
                        ->orWhereHas('program.result', fn ($rq) => $rq->whereIn('status', $declaredStatuses));
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
            'awardedPrograms'
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
        return app(CoinRewardService::class)->resolveStudentAwardedPrograms($student, false);
    }
}
