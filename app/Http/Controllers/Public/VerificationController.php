<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CoinTransaction;
use App\Models\Student;
use App\Services\CoinRewardService;
use Illuminate\Http\Request;
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
            ->with(['student.group', 'program.category', 'entry'])
            ->first();

        $isValid = (bool) $certificate;

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

        if ($student) {
            $coinSummary = $coinRewardService->getStudentCoinSummary($student);
            $studentAchievements = $coinRewardService->getStudentAchievements($student);
            $purchaseHistory = CoinTransaction::where('student_id', $student->id)
                ->where('transaction_type', 'debit')
                ->latest()
                ->get();
            $coinTransactions = CoinTransaction::where('student_id', $student->id)
                ->latest()
                ->take(15)
                ->get();
        }

        return view('public.verify-student', compact(
            'student',
            'isValid',
            'qrToken',
            'coinSummary',
            'studentAchievements',
            'purchaseHistory',
            'coinTransactions'
        ));
    }
}
