<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Program;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function index(Request $request): View
    {
        $programId = $request->query('program');
        $search = $request->query('search');

        $query = Certificate::with(['student.group', 'program']);

        if ($programId) {
            $query->where('program_id', $programId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('certificate_number', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $certificates = $query->latest('issued_at')->paginate(15)->withQueryString();
        $programs = Program::has('result')->orderBy('name')->get();

        return view('admin.certificates.index', compact('certificates', 'programs', 'programId', 'search'));
    }

    public function show(Certificate $certificate): View
    {
        $certificate->load(['student.group', 'program.category', 'entry']);
        $qrCodeSvg = QrCodeService::svg($certificate->qr_verification_url ?? route('verify.certificate', $certificate->certificate_number), 180);

        return view('admin.certificates.show', compact('certificate', 'qrCodeSvg'));
    }
}
