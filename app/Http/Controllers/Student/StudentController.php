<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Student;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function showLogin(Request $request): Response
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()
            ->view('student.login')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:50'],
        ]);

        $identifier = trim($validated['identifier']);

        // Search by student_id, or by chest_number in program_entries
        $student = Student::where('student_id', $identifier)
            ->orWhereHas('entries', fn ($q) => $q->where('chest_number', $identifier))
            ->first();

        if (! $student) {
            return back()->withErrors([
                'identifier' => 'ചെസ്റ്റ് നമ്പർ അല്ലെങ്കിൽ സ്റ്റുഡന്റ് ഐഡി കണ്ടെത്താനായില്ല (Student not found). ദയവായി പരിശോധിക്കുക.',
            ]);
        }

        // Ensure user account exists
        if (! $student->user_id || ! $student->user) {
            $user = User::create([
                'name' => $student->name,
                'email' => Str::slug($student->student_id).'@student.quaf.org',
                'password' => Hash::make(Str::random(16)),
                'role' => 'student',
                'phone' => $student->contact,
                'is_active' => true,
            ]);
            $student->update(['user_id' => $user->id]);
            $student->setRelation('user', $user);
        }

        Auth::login($student->user);
        $request->session()->regenerate();

        return redirect()->route('student.dashboard')->with('success', "സ്വാഗതം, {$student->name}! സ്റ്റുഡന്റ് പോർട്ടലിലേക്ക് ലോഗിൻ ചെയ്തു.");
    }

    protected function getStudent(): Student
    {
        $student = Student::where('user_id', Auth::id())->first();
        if (! $student) {
            // If logged in as student without record, fallback or create
            $student = Student::with('group')->firstOrFail();
        }

        return $student;
    }

    public function dashboard(): View
    {
        $student = $this->getStudent();
        $student->load([
            'group',
            'zone',
            'entries.program.category',
            'entries.program.stage',
            'entries.program.schedule',
            'entries.program.zone',
            'certificates.program',
        ]);

        $individualCount = $student->getIndividualParticipationCount();
        $remainingSlots = $student->getRemainingIndividualSlots();
        $maxSlots = Student::MAX_INDIVIDUAL_PROGRAMS;

        // Find Next Program prominently
        $nextEntry = $student->entries
            ->filter(fn ($e) => $e->program?->scheduled_time && $e->program->scheduled_time->isFuture())
            ->sortBy('program.scheduled_time')
            ->first();

        $myPrograms = $student->entries;

        $myResults = $student->certificates()->with('program.category')->latest('issued_at')->get();

        $announcements = Announcement::where('is_active', true)
            ->where(function ($q) use ($student) {
                $q->whereNull('target_group_id')
                    ->orWhere('target_group_id', $student->group_id);
            })
            ->where(function ($q) {
                $q->whereNull('target_role')
                    ->orWhereIn('target_role', ['all', 'student']);
            })
            ->latest()
            ->take(4)
            ->get();

        $qrCodeSvg = QrCodeService::svg(route('verify.student', $student->qr_token), 200);

        return view('student.dashboard', compact(
            'student',
            'nextEntry',
            'myPrograms',
            'myResults',
            'announcements',
            'qrCodeSvg',
            'individualCount',
            'remainingSlots',
            'maxSlots'
        ));
    }

    public function idCard(): View
    {
        $student = $this->getStudent();
        $student->load('group');
        $qrCodeSvg = QrCodeService::svg(route('verify.student', $student->qr_token), 220);

        return view('student.idcard', compact('student', 'qrCodeSvg'));
    }

    public function certificates(): View
    {
        $student = $this->getStudent();
        $certificates = $student->certificates()->with(['program.category', 'entry'])->latest('issued_at')->get();

        return view('student.certificates', compact('student', 'certificates'));
    }
}
