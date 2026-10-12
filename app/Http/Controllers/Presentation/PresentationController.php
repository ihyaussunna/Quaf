<?php

namespace App\Http\Controllers\Presentation;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\Student;
use App\Services\CoinRewardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PresentationController extends Controller
{
    /**
     * Standard Catalog Presets for quick redemption.
     */
    public const PRESET_ITEMS = [
        ['id' => 'book_50', 'name' => 'Book (പുസ്തകം - Basic)', 'category' => 'book', 'coins' => 50, 'badge' => 'Book'],
        ['id' => 'book_100', 'name' => 'Book (പുസ്തകം - Standard)', 'category' => 'book', 'coins' => 100, 'badge' => 'Book'],
        ['id' => 'book_150', 'name' => 'Book (പുസ്തകം - Special)', 'category' => 'book', 'coins' => 150, 'badge' => 'Book'],
        ['id' => 'book_200', 'name' => 'Book (പുസ്തകം - Premium)', 'category' => 'book', 'coins' => 200, 'badge' => 'Book'],
        ['id' => 'memento_100', 'name' => 'Fest Souvenir / Memento', 'category' => 'souvenir', 'coins' => 100, 'badge' => 'Souvenir'],
        ['id' => 'notebook_60', 'name' => 'QUAF Centenary Diary', 'category' => 'stationery', 'coins' => 60, 'badge' => 'Stationery'],
        ['id' => 'pen_40', 'name' => 'Executive Pen Set', 'category' => 'stationery', 'coins' => 40, 'badge' => 'Stationery'],
        ['id' => 'badge_30', 'name' => 'Festival Badge & Kit', 'category' => 'kit', 'coins' => 30, 'badge' => 'Kit'],
    ];

    public function __construct(
        protected CoinRewardService $coinRewardService
    ) {}

    /**
     * Display the Presentation Team Dashboard.
     */
    public function index(Request $request): View
    {
        CoinTransaction::ensureSchema();

        $stats = [
            'total_credited' => (int) CoinTransaction::where('coins', '>', 0)->sum('coins'),
            'total_redeemed' => (int) abs(CoinTransaction::where('transaction_type', 'debit')->sum('coins')),
            'total_purchases' => CoinTransaction::where('transaction_type', 'debit')->count(),
            'unique_students' => CoinTransaction::where('transaction_type', 'debit')->distinct('student_id')->count('student_id'),
        ];

        $selectedStudent = null;
        $studentSummary = null;
        $studentTransactions = collect();

        $queryStr = trim((string) ($request->query('student') ?? $request->query('query') ?? ''));
        if ($queryStr !== '') {
            $selectedStudent = $this->resolveStudent($queryStr);
            if ($selectedStudent) {
                $studentSummary = $this->coinRewardService->getStudentCoinSummary($selectedStudent);
                $studentTransactions = CoinTransaction::where('student_id', $selectedStudent->id)
                    ->latest()
                    ->take(10)
                    ->get();
            }
        }

        $searchFilter = trim((string) $request->query('search_history', ''));
        $historyQuery = CoinTransaction::whereIn('transaction_type', ['debit', 'reversal'])
            ->with(['student.group', 'student.zone'])
            ->latest();

        if ($searchFilter !== '') {
            $historyQuery->where(function ($q) use ($searchFilter) {
                $q->where('transaction_reference', 'like', "%{$searchFilter}%")
                    ->orWhere('description', 'like', "%{$searchFilter}%")
                    ->orWhereHas('student', function ($sq) use ($searchFilter) {
                        $sq->where('name', 'like', "%{$searchFilter}%")
                            ->orWhere('student_id', 'like', "%{$searchFilter}%");
                    });
            });
        }

        $recentTransactions = $historyQuery->paginate(15)->withQueryString();
        $presets = self::PRESET_ITEMS;

        $lastTransaction = null;
        if (session('last_transaction_id')) {
            $lastTransaction = CoinTransaction::with(['student.group'])->find(session('last_transaction_id'));
        }

        return view('presentation.dashboard', compact(
            'stats',
            'presets',
            'selectedStudent',
            'studentSummary',
            'studentTransactions',
            'recentTransactions',
            'queryStr',
            'searchFilter',
            'lastTransaction'
        ));
    }

    /**
     * Search student via AJAX / Scan.
     */
    public function searchStudent(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('query', ''));
        if ($query === '') {
            return response()->json([
                'found' => false,
                'message' => 'Query string is required.',
            ], 422);
        }

        $student = $this->resolveStudent($query);

        if (! $student) {
            return response()->json([
                'found' => false,
                'message' => 'No student found for: '.$query,
            ], 404);
        }

        $summary = $this->coinRewardService->getStudentCoinSummary($student);
        $recent = CoinTransaction::where('student_id', $student->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'reference' => $tx->transaction_reference,
                    'type' => $tx->transaction_type,
                    'coins' => $tx->coins,
                    'description' => $tx->description,
                    'created_at' => $tx->created_at?->format('M d, h:i A') ?? '',
                ];
            });

        return response()->json([
            'found' => true,
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'student_id' => $student->student_id,
                'chest_number' => $student->chest_number ?? $student->student_id,
                'group_name' => $student->group?->name ?? 'N/A',
                'group_color' => $student->group?->color_hex ?? '#be1e2d',
                'zone_name' => $student->zone_name ?? 'N/A',
                'photo_url' => $student->photo_path ? asset('storage/'.$student->photo_path) : null,
                'balance' => $summary['balance'],
                'total_earned' => $summary['total_earned'],
                'total_spent' => $summary['total_spent'],
                'recent_transactions' => $recent,
            ],
        ]);
    }

    /**
     * Record a purchase / coin deduction for the student.
     */
    public function redeem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'item_name' => 'required|string|max:255',
            'coins' => 'required|integer|min:1',
            'quantity' => 'nullable|integer|min:1|max:100',
            'item_category' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $student = Student::with(['group'])->findOrFail($validated['student_id']);

        try {
            $transaction = $this->coinRewardService->redeemCoins($student, [
                'item_name' => $validated['item_name'],
                'coins' => (int) $validated['coins'],
                'quantity' => (int) ($validated['quantity'] ?? 1),
                'item_category' => $validated['item_category'] ?? 'book',
                'notes' => $validated['notes'] ?? null,
                'operator_id' => Auth::id(),
                'operator_name' => Auth::user()?->name ?? 'Presentation Desk',
            ]);

            return redirect()->route('presentation.dashboard', ['student' => $student->student_id])
                ->with('success', "വിജയകരമായി രേഖപ്പെടുത്തി! {$student->name}-ൽ നിന്ന് ".abs($transaction->coins).' കോയിനുകൾ കുറച്ചു.')
                ->with('last_transaction_id', $transaction->id);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error processing purchase: '.$e->getMessage());
        }
    }

    /**
     * Reverse a mistaken redemption/purchase.
     */
    public function reverseRedemption(CoinTransaction $transaction, Request $request): RedirectResponse
    {
        $reason = trim((string) $request->input('reason', 'Cancelled by Presentation Team'));

        try {
            $this->coinRewardService->reverseRedemption(
                $transaction,
                $reason,
                Auth::id(),
                Auth::user()?->name ?? 'Presentation Desk'
            );

            return redirect()->back()
                ->with('success', 'പർച്ചേസ് വിജയകരമായി ക്യാൻസൽ ചെയ്തു. കോയിൻ വിദ്യാർത്ഥിക്ക് തിരികെ നൽകി.');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Error reversing purchase: '.$e->getMessage());
        }
    }

    /**
     * View or print receipt for a transaction.
     */
    public function receipt(CoinTransaction $transaction): View
    {
        $transaction->load(['student.group', 'student.zone']);

        return view('presentation.receipt', compact('transaction'));
    }

    /**
     * View overall presentation report / summary.
     */
    public function report(Request $request): View
    {
        CoinTransaction::ensureSchema();

        $query = CoinTransaction::whereIn('transaction_type', ['debit', 'reversal'])
            ->with(['student.group', 'student.zone'])
            ->latest();

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transactions = $query->get();

        $totalCoinsRedeemed = (int) abs($transactions->where('transaction_type', 'debit')->sum('coins'));
        $totalItemsCount = $transactions->where('transaction_type', 'debit')->sum(function ($tx) {
            return $tx->metadata['quantity'] ?? 1;
        });

        return view('presentation.report', compact(
            'transactions',
            'totalCoinsRedeemed',
            'totalItemsCount',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Resolve student from QR token, Chest number, or Student ID.
     */
    protected function resolveStudent(string $queryStr): ?Student
    {
        $clean = trim($queryStr);

        // If a full QR verification URL was scanned
        if (preg_match('~/verify/student/([^/?#]+)~', $clean, $matches)) {
            $clean = trim($matches[1]);
        }

        // Direct token or chest match
        $student = Student::with(['group', 'zone'])
            ->where('qr_token', $clean)
            ->orWhere('student_id', $clean)
            ->orWhereRaw('LOWER(student_id) = ?', [strtolower($clean)])
            ->first();

        // Numeric primary key match
        if (! $student && is_numeric($clean)) {
            $student = Student::with(['group', 'zone'])->find((int) $clean);
        }

        // Fallback name search
        if (! $student && strlen($clean) >= 2) {
            $student = Student::with(['group', 'zone'])
                ->where('name', 'like', "%{$clean}%")
                ->orWhere('student_id', 'like', "%{$clean}%")
                ->first();
        }

        return $student;
    }
}
