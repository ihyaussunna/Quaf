<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Student;
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

    public function verifyStudent(string $qrToken): View
    {
        $student = Student::where('qr_token', trim($qrToken))
            ->orWhere('student_id', trim($qrToken))
            ->with([
                'group',
                'entries.program.category',
                'entries.program.stage',
                'entries.program.schedule',
                'certificates.program',
            ])
            ->first();

        $isValid = (bool) $student;

        return view('public.verify-student', compact('student', 'isValid', 'qrToken'));
    }
}
