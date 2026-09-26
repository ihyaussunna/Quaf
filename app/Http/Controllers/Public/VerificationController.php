<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Student;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function verifyCertificate(string $certificateNumber): View
    {
        $certificate = Certificate::where('certificate_number', $certificateNumber)
            ->with(['student.group', 'program.category', 'entry'])
            ->firstOrFail();

        return view('public.verify-certificate', compact('certificate'));
    }

    public function verifyStudent(string $qrToken): View
    {
        $student = Student::where('qr_token', $qrToken)
            ->with([
                'group',
                'entries.program.category',
                'entries.program.stage',
                'entries.program.schedule',
                'certificates.program',
            ])
            ->firstOrFail();

        return view('public.verify-student', compact('student'));
    }
}
