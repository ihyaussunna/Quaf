<?php

namespace App\Http\Controllers\Fund;

use App\Http\Controllers\Controller;
use App\Models\FundReceipt;
use App\Models\FundReceiptSetting;
use App\Models\Group;
use App\Models\Student;
use App\Services\AuditLogger;
use App\Services\ReceiptImageGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // 1. Top Collected Students (Order by total collected desc)
        $topStudents = Student::query()
            ->with('group')
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

        // 2. Top Collected Groups
        $topGroups = Group::query()
            ->withSum(['fundReceipts as total_collected' => function ($q) {
                $q->where('is_cancelled', false);
            }], 'amount')
            ->withCount(['fundReceipts as receipts_count' => function ($q) {
                $q->where('is_cancelled', false);
            }])
            ->orderByDesc('total_collected')
            ->get();

        // Overall Fund Stats
        $overallQuery = FundReceipt::where('is_cancelled', false);
        $totalCollected = (clone $overallQuery)->sum('amount');
        $totalReceiptsCount = (clone $overallQuery)->count();

        // Distinct Classes for Student Selection dropdown
        $classes = Student::whereNotNull('class_level')
            ->where('class_level', '!=', '')
            ->distinct()
            ->orderBy('class_level')
            ->pluck('class_level');

        // All Groups for Student Selection dropdown (optional selection)
        $groups = Group::orderBy('name')->get();

        // Check if student is already in session
        $currentStudent = null;
        if ($studentId = session('fund_student_id')) {
            $currentStudent = Student::with('group')->find($studentId);
        }

        return view('fund.home', compact(
            'topStudents',
            'topGroups',
            'totalCollected',
            'totalReceiptsCount',
            'classes',
            'groups',
            'currentStudent'
        ));
    }

    /**
     * API to search and filter students dynamically.
     */
    public function searchStudents(Request $request): JsonResponse
    {
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

        $students = $query->orderBy('name')->take(40)->get();

        return response()->json($students->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'chest_number' => $s->chest_number ?: $s->student_id,
                'class_level' => $s->class_level ?? '',
                'group_name' => $s->group?->name ?? 'No Group',
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
}
