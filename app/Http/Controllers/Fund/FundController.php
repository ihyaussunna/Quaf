<?php

namespace App\Http\Controllers\Fund;

use App\Http\Controllers\Controller;
use App\Models\FundReceipt;
use App\Models\FundReceiptSetting;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ReceiptImageGenerator;
use App\Services\StandingPosterGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FundController extends Controller
{
    /**
     * Public Fund Landing / Home Page
     */
    public function index(Request $request): View
    {
        FundReceipt::ensureSchema();
        self::ensureOfficeAccount();

        // 1. Top Collected Students (Order by total collected desc - Excludes special OFFICE account)
        $topStudents = Student::query()
            ->with('group')
            ->where('student_id', '!=', 'OFFICE')
            ->where(function ($q) {
                $q->whereNull('class_level')
                    ->orWhereRaw('UPPER(class_level) != ?', ['OFFICE']);
            })
            ->whereRaw('UPPER(name) != ?', ['OFFICE'])
            ->whereHas('fundReceipts', function ($q) {
                $q->where('is_cancelled', false);
            })
            ->withSum(['fundReceipts as total_collected' => function ($q) {
                $q->where('is_cancelled', false);
            }], 'amount')
            ->withCount(['fundReceipts as receipts_count' => function ($q) {
                $q->where('is_cancelled', false);
            }])
            ->orderByDesc('total_collected')
            ->take(10)
            ->get();

        // Overall Fund Stats
        $overallQuery = FundReceipt::where('is_cancelled', false);
        $totalCollected = (clone $overallQuery)->sum('amount');
        $totalReceiptsCount = (clone $overallQuery)->count();

        // Distinct Classes for Student Selection dropdown (includes OFFICE)
        $classes = Student::whereNotNull('class_level')
            ->where('class_level', '!=', '')
            ->distinct()
            ->orderBy('class_level')
            ->pluck('class_level');

        if (! $classes->contains('OFFICE')) {
            $classes->push('OFFICE');
        }

        // All Groups for Student Selection dropdown (optional selection)
        $groups = Group::orderBy('name')->get();

        // Check if student is already in session
        $currentStudent = null;
        if ($studentId = session('fund_student_id')) {
            $currentStudent = Student::with('group')->find($studentId);
        }

        return view('fund.home', compact(
            'topStudents',
            'totalCollected',
            'totalReceiptsCount',
            'classes',
            'groups',
            'currentStudent'
        ));
    }

    /**
     * Ensure the special OFFICE student account exists.
     */
    public static function ensureOfficeAccount(): void
    {
        try {
            if (Schema::hasTable('students')) {
                if (! DB::table('students')->where('student_id', 'OFFICE')->exists()) {
                    try {
                        Artisan::call('migrate', ['--force' => true]);
                    } catch (\Throwable) {
                    }

                    DB::table('students')->updateOrInsert(
                        ['student_id' => 'OFFICE'],
                        [
                            'name' => 'OFFICE',
                            'class_level' => 'OFFICE',
                            'group_id' => null,
                            'category' => 'Office',
                            'gender' => 'Other',
                            'qr_token' => Str::random(40),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        } catch (\Throwable) {
            // Fail gracefully if schema constraints prevent null before migration
        }
    }

    /**
     * API to search and filter students dynamically.
     */
    public function searchStudents(Request $request): JsonResponse
    {
        self::ensureOfficeAccount();

        $classLevel = trim((string) $request->input('class_level'));
        $groupId = $request->input('group_id');
        $search = trim((string) $request->input('query', $request->input('search')));

        $query = Student::with('group');

        if ($classLevel !== '') {
            $query->where('class_level', $classLevel);
        }

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('chest_number', 'like', "%{$search}%");
            });
        }

        $limit = $search !== '' ? 60 : 40;
        $students = $query->orderBy('name')->take($limit)->get();

        return response()->json($students->map(function ($s) {
            $groupName = $s->group?->name ?? '';
            $groupCode = $s->group ? ($s->group->code ?: (strtok($groupName, ' ') ?: $groupName)) : '';

            return [
                'id' => $s->id,
                'name' => $s->name,
                'chest_number' => $s->chest_number ?: $s->student_id,
                'class_level' => $s->class_level ?? '',
                'group_name' => $groupName,
                'group_code' => $groupCode,
                'group_color' => $s->group?->color_hex ?? '#be1e2d',
            ];
        }));
    }

    /**
     * Student login to personal receipt desk.
     */
    public function studentLogin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ]);

        $student = Student::with('group')->findOrFail($validated['student_id']);
        session(['fund_student_id' => $student->id]);

        return redirect()->route('fund.student.desk')
            ->with('success', "സ്വാഗതം {$student->name}! നിങ്ങളുടെ പേഴ്സണൽ ഫണ്ട് ഡെസ്ക് തയ്യാറാണ്.");
    }

    /**
     * Student logout.
     */
    public function studentLogout(): RedirectResponse
    {
        session()->forget('fund_student_id');

        return redirect()->route('fund.home')
            ->with('success', 'സ്റ്റുഡന്റ് ഫണ്ട് പോർട്ടലിൽ നിന്നും ലോഗൗട്ട് ചെയ്തു.');
    }

    /**
     * Student Personal Fund Desk.
     */
    public function studentDesk(): View|RedirectResponse
    {
        FundReceipt::ensureSchema();

        $studentId = session('fund_student_id');
        if (! $studentId) {
            return redirect()->route('fund.home', ['login' => 1])
                ->with('error', 'ദയവായി നിങ്ങളുടെ പേര് സെലക്ട് ചെയ്ത് ലോഗിൻ ചെയ്യുക.');
        }

        $student = Student::with('group')->findOrFail($studentId);

        $myReceiptsQuery = FundReceipt::where('student_id', $student->id)->where('is_cancelled', false);
        $myTotalCollected = (clone $myReceiptsQuery)->sum('amount');
        $myReceiptsCount = (clone $myReceiptsQuery)->count();

        $myRecentReceipts = FundReceipt::where('student_id', $student->id)
            ->latest()
            ->paginate(15);

        $nextReceiptNumber = FundReceipt::nextReceiptNumber();
        $settings = FundReceiptSetting::current();

        return view('fund.student-desk', compact(
            'student',
            'myTotalCollected',
            'myReceiptsCount',
            'myRecentReceipts',
            'nextReceiptNumber',
            'settings'
        ));
    }

    /**
     * Store a receipt issued by a student.
     */
    public function studentStoreReceipt(Request $request): RedirectResponse
    {
        FundReceipt::ensureSchema();

        $studentId = session('fund_student_id');
        if (! $studentId) {
            return redirect()->route('fund.home', ['login' => 1]);
        }

        $student = Student::with('group')->findOrFail($studentId);

        $validated = $request->validate([
            'donor_name' => ['required', 'string', 'max:150'],
            'donor_phone' => ['nullable', 'string', 'max:25'],
            'donor_place' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'payment_method' => ['required', 'string', 'in:cash,upi,gpay,bank_transfer,card,other'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $receiptNumber = FundReceipt::nextReceiptNumber();
        $amountInWords = FundReceipt::amountInWords($validated['amount']);

        $receipt = FundReceipt::create([
            'receipt_number' => $receiptNumber,
            'donor_name' => trim($validated['donor_name']),
            'donor_phone' => trim($validated['donor_phone'] ?? ''),
            'donor_place' => trim($validated['donor_place'] ?? ''),
            'amount' => $validated['amount'],
            'amount_in_words' => $amountInWords,
            'payment_method' => $validated['payment_method'],
            'transaction_reference' => ! empty($validated['transaction_reference']) ? trim($validated['transaction_reference']) : null,
            'category' => ! empty($validated['category']) ? trim($validated['category']) : 'Festival Fund',
            'notes' => ! empty($validated['notes']) ? trim($validated['notes']) : null,
            'student_id' => $student->id,
            'group_id' => $student->group_id,
            'collector_name' => $student->name.' ('.($student->chest_number ?: $student->student_id).')',
            'collector_id' => Auth::id() ?: null,
            'is_cancelled' => false,
        ]);

        AuditLogger::log('issue_student_fund_receipt', $receipt, null, $receipt->toArray());

        return redirect()->route('fund.receipts.show', $receipt)
            ->with('success', "രസീത് {$receipt->receipt_number} വിജയകരമായി ജനറേറ്റ് ചെയ്തു!");
    }

    /**
     * Update an existing receipt (donor name, payment mode, etc.) by student or admin.
     */
    public function studentUpdateReceipt(Request $request, FundReceipt $receipt): RedirectResponse|JsonResponse
    {
        FundReceipt::ensureSchema();

        $studentId = session('fund_student_id');
        $isAdmin = Auth::check() && (
            (method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin()) ||
            in_array(Auth::user()->role, ['admin', 'super_admin', 'fund_manager', 'fund_collector'])
        );

        if (! $isAdmin && (! $studentId || (int) $receipt->student_id !== (int) $studentId)) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'ഈ രസീത് എഡിറ്റ് ചെയ്യാനുള്ള അനുമതിയില്ല.'], 403);
            }

            return redirect()->back()->with('error', 'ഈ രസീത് എഡിറ്റ് ചെയ്യാനുള്ള അനുമതിയില്ല.');
        }

        if ($receipt->is_cancelled) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'റദ്ദാക്കിയ രസീത് എഡിറ്റ് ചെയ്യാൻ സാധ്യമല്ല.'], 422);
            }

            return redirect()->back()->with('error', 'റദ്ദാക്കിയ രസീത് എഡിറ്റ് ചെയ്യാൻ സാധ്യമല്ല.');
        }

        $validated = $request->validate([
            'donor_name' => ['required', 'string', 'max:150'],
            'payment_method' => ['required', 'string', 'in:cash,upi,gpay,bank_transfer,card,other'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'donor_phone' => ['nullable', 'string', 'max:25'],
            'donor_place' => ['nullable', 'string', 'max:100'],
        ]);

        $before = $receipt->only(['donor_name', 'payment_method', 'transaction_reference', 'donor_phone', 'donor_place']);

        $receipt->donor_name = trim($validated['donor_name']);
        $receipt->payment_method = $validated['payment_method'];
        $receipt->transaction_reference = ! empty($validated['transaction_reference']) ? trim($validated['transaction_reference']) : null;

        if (array_key_exists('donor_phone', $validated)) {
            $receipt->donor_phone = trim((string) $validated['donor_phone']);
        }
        if (array_key_exists('donor_place', $validated)) {
            $receipt->donor_place = trim((string) $validated['donor_place']);
        }

        $receipt->save();

        AuditLogger::log('update_fund_receipt_by_student', $receipt, $before, $receipt->toArray());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "രസീത് #{$receipt->receipt_number} വിജയകരമായി അപ്ഡേറ്റ് ചെയ്തു!",
                'receipt' => $receipt,
            ]);
        }

        return redirect()->back()->with('success', "രസീത് #{$receipt->receipt_number} വിവരങ്ങൾ വിജയകരമായി അപ്ഡേറ്റ് ചെയ്തു!");
    }

    /**
     * Cancel / delete a receipt issued by a student (with mandatory reason).
     */
    public function studentDestroyReceipt(Request $request, FundReceipt $receipt): RedirectResponse|JsonResponse
    {
        FundReceipt::ensureSchema();

        $studentId = session('fund_student_id');
        $isAdmin = Auth::check() && (
            (method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin()) ||
            in_array(Auth::user()->role, ['admin', 'super_admin', 'fund_manager', 'fund_collector'])
        );

        if (! $isAdmin && (! $studentId || (int) $receipt->student_id !== (int) $studentId)) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'ഈ രസീത് ഡിലീറ്റ് ചെയ്യാനുള്ള അനുമതിയില്ല.'], 403);
            }

            return redirect()->back()->with('error', 'ഈ രസീത് ഡിലീറ്റ് ചെയ്യാനുള്ള അനുമതിയില്ല.');
        }

        if ($receipt->is_cancelled || $receipt->trashed()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'ഈ രസീത് ഇതിനകം റദ്ദാക്കപ്പെട്ടതാണ്.'], 422);
            }

            return redirect()->back()->with('error', 'ഈ രസീത് ഇതിനകം റദ്ദാക്കപ്പെട്ടതാണ്.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $reason = trim($validated['reason']);

        $receipt->update([
            'is_cancelled' => true,
            'cancelled_reason' => $reason,
            'cancelled_at' => now(),
            'cancelled_by' => Auth::id() ?: null,
        ]);

        $receipt->delete(); // Soft delete

        AuditLogger::log('delete_fund_receipt_by_student', $receipt, ['reason' => $reason], ['is_cancelled' => true]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "രസീത് #{$receipt->receipt_number} വിജയകരമായി റദ്ദാക്കി / ഡിലീറ്റ് ചെയ്തു.",
            ]);
        }

        // If deleted from receipt view page, redirect to student desk or home
        if ($request->header('referer') && str_contains($request->header('referer'), '/fund/receipts/')) {
            if ($studentId) {
                return redirect()->route('fund.student.desk')
                    ->with('success', "രസീത് #{$receipt->receipt_number} വിജയകരമായി റദ്ദാക്കി / ഡിലീറ്റ് ചെയ്തു.");
            }

            return redirect()->route('fund.home')
                ->with('success', "രസീത് #{$receipt->receipt_number} വിജയകരമായി റദ്ദാക്കി / ഡിലീറ്റ് ചെയ്തു.");
        }

        return redirect()->back()
            ->with('success', "രസീത് #{$receipt->receipt_number} വിജയകരമായി റദ്ദാക്കി / ഡിലീറ്റ് ചെയ്തു.");
    }

    /**
     * Display the Central Fund Collection Panel Dashboard (Staff / Admin).
     */
    public function dashboard(Request $request): View
    {
        FundReceipt::ensureSchema();

        $today = now()->startOfDay();

        // Active receipts query (excluding soft-deleted / cancelled)
        $baseQuery = FundReceipt::query()->where('is_cancelled', false);

        $totalCount = (clone $baseQuery)->count();
        $totalAmount = (clone $baseQuery)->sum('amount');

        $todayCount = (clone $baseQuery)->where('created_at', '>=', $today)->count();
        $todayAmount = (clone $baseQuery)->where('created_at', '>=', $today)->sum('amount');

        $cashAmount = (clone $baseQuery)->where('payment_method', 'cash')->sum('amount');
        $upiAmount = (clone $baseQuery)->whereIn('payment_method', ['upi', 'gpay', 'bank_transfer'])->sum('amount');

        // Recent receipts
        $query = FundReceipt::query()->with(['collector', 'student.group'])->latest();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                    ->orWhere('donor_phone', 'like', "%{$search}%")
                    ->orWhere('receipt_number', 'like', "%{$search}%")
                    ->orWhere('transaction_reference', 'like', "%{$search}%");
            });
        }

        if ($method = $request->input('payment_method')) {
            $query->where('payment_method', $method);
        }

        $receipts = $query->paginate(20)->withQueryString();
        $nextReceiptNumber = FundReceipt::nextReceiptNumber();
        $settings = FundReceiptSetting::current();

        return view('fund.dashboard', compact(
            'totalCount',
            'totalAmount',
            'todayCount',
            'todayAmount',
            'cashAmount',
            'upiAmount',
            'receipts',
            'nextReceiptNumber',
            'settings'
        ));
    }

    /**
     * Display the Fund Collection & Receipts Ledger Desk.
     * Dedicated desk interface for viewing central ledger records and group standings without admin access.
     */
    public function ledger(Request $request): View
    {
        FundReceipt::ensureSchema();

        $today = now()->startOfDay();
        $baseQuery = FundReceipt::query();

        // Overall stats
        $totalCount = (clone $baseQuery)->where('is_cancelled', false)->count();
        $totalAmount = (clone $baseQuery)->where('is_cancelled', false)->sum('amount');
        $todayAmount = (clone $baseQuery)->where('is_cancelled', false)->where('created_at', '>=', $today)->sum('amount');
        $cancelledCount = (clone $baseQuery)->where('is_cancelled', true)->count();
        $cancelledAmount = (clone $baseQuery)->where('is_cancelled', true)->sum('amount');

        // Filtered listing
        $query = FundReceipt::withTrashed()->with(['collector', 'cancelledBy', 'student.group', 'group'])->latest();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                    ->orWhere('donor_phone', 'like', "%{$search}%")
                    ->orWhere('receipt_number', 'like', "%{$search}%")
                    ->orWhere('transaction_reference', 'like', "%{$search}%");
            });
        }

        if ($method = $request->input('payment_method')) {
            $query->where('payment_method', $method);
        }

        if ($collectorId = $request->input('collector_id')) {
            $query->where('collector_id', $collectorId);
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('is_cancelled', false)->whereNull('deleted_at');
            } elseif ($status === 'cancelled') {
                $query->where(function ($q) {
                    $q->where('is_cancelled', true)->orWhereNotNull('deleted_at');
                });
            }
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $receipts = $query->paginate(25)->withQueryString();
        $collectors = User::whereIn('role', ['fund_collector', 'fund_manager', 'admin', 'super_admin'])->orderBy('name')->get();

        // Top Collected Groups leaderboard
        $topGroups = Group::query()
            ->withSum(['fundReceipts as total_collected' => function ($q) {
                $q->where('is_cancelled', false);
            }], 'amount')
            ->withCount(['fundReceipts as receipts_count' => function ($q) {
                $q->where('is_cancelled', false);
            }])
            ->orderByDesc('total_collected')
            ->get();

        return view('fund.ledger', compact(
            'receipts',
            'collectors',
            'totalCount',
            'totalAmount',
            'todayAmount',
            'cancelledCount',
            'cancelledAmount',
            'topGroups'
        ));
    }

    /**
     * Store a newly issued fund receipt (Staff / Central Desk).
     */
    public function store(Request $request): RedirectResponse
    {
        FundReceipt::ensureSchema();

        $validated = $request->validate([
            'donor_name' => ['required', 'string', 'max:150'],
            'donor_phone' => ['nullable', 'string', 'max:25'],
            'donor_place' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'payment_method' => ['required', 'string', 'in:cash,upi,gpay,bank_transfer,card,other'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $receiptNumber = FundReceipt::nextReceiptNumber();
        $amountInWords = FundReceipt::amountInWords($validated['amount']);

        $receipt = FundReceipt::create([
            'receipt_number' => $receiptNumber,
            'donor_name' => trim($validated['donor_name']),
            'donor_phone' => trim($validated['donor_phone'] ?? ''),
            'donor_place' => trim($validated['donor_place'] ?? ''),
            'amount' => $validated['amount'],
            'amount_in_words' => $amountInWords,
            'payment_method' => $validated['payment_method'],
            'transaction_reference' => ! empty($validated['transaction_reference']) ? trim($validated['transaction_reference']) : null,
            'category' => ! empty($validated['category']) ? trim($validated['category']) : 'Festival Fund',
            'notes' => ! empty($validated['notes']) ? trim($validated['notes']) : null,
            'collector_id' => Auth::id(),
            'collector_name' => Auth::user()?->name ?? 'Fund Collection Desk',
            'is_cancelled' => false,
        ]);

        AuditLogger::log('issue_fund_receipt', $receipt, null, $receipt->toArray());

        return redirect()->route('fund.receipts.show', $receipt)
            ->with('success', "രസീത് {$receipt->receipt_number} വിജയകരമായി ജനറേറ്റ് ചെയ്തു!");
    }

    /**
     * View and print a specific receipt voucher.
     */
    public function show(FundReceipt $receipt): View
    {
        $settings = FundReceiptSetting::current();

        return view('fund.receipt', compact('receipt', 'settings'));
    }

    /**
     * Stream the generated receipt image inline.
     */
    public function image(FundReceipt $receipt): StreamedResponse
    {
        return ReceiptImageGenerator::streamResponse($receipt, false);
    }

    /**
     * Download the generated receipt image as a JPEG file.
     */
    public function download(FundReceipt $receipt): StreamedResponse
    {
        return ReceiptImageGenerator::streamResponse($receipt, true);
    }

    /**
     * Stream the generated Group Standings Poster inline.
     */
    public function standingPoster(): StreamedResponse
    {
        $img = StandingPosterGenerator::generate();

        return StandingPosterGenerator::streamResponse($img, 'QUAF-09-Top-Collected-Groups.jpg', false);
    }

    /**
     * Download the generated Group Standings Poster as a JPEG file.
     */
    public function downloadStandingPoster(): StreamedResponse
    {
        $img = StandingPosterGenerator::generate();

        return StandingPosterGenerator::streamResponse($img, 'QUAF-09-Top-Collected-Groups.jpg', true);
    }
}
