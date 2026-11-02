<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\Group;
use App\Models\Program;
use App\Models\Student;
use App\Services\CoinRewardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoinAuditController extends Controller
{
    /**
     * Display the official Festival Coin Ledger & Audit Hub.
     * Allows admin to track every coin credited, clawed back after judgment updates, or redeemed.
     */
    public function index(Request $request): View
    {
        CoinTransaction::ensureSchema();

        $search = trim((string) ($request->query('search') ?? ''));
        $selectedType = $request->query('type', 'all');
        $selectedProgram = $request->query('program', 'all');
        $selectedGroup = $request->query('group', 'all');
        $selectedStudentId = $request->query('student_id');
        $perPage = $request->query('per_page', 25);

        // 1. Overall Festival Coin Metrics
        $metrics = $this->getCoinMetrics();

        // 2. Query Coin Transactions Ledger
        $query = $this->buildTransactionsQuery($request);

        if ($perPage === 'all') {
            $transactions = $query->paginate(10000)->withQueryString();
        } else {
            $transactions = $query->paginate(max(10, min(500, (int) $perPage)))->withQueryString();
        }

        // 3. Highlighted Student Breakdown (if filtered for a specific student)
        $highlightedStudent = null;
        $studentProgramBreakdown = [];

        if ($selectedStudentId) {
            $highlightedStudent = Student::with('group')->find($selectedStudentId);
        } elseif ($search !== '' && $transactions->total() > 0) {
            // If search matches a single student name or chest number
            $matchedStudent = Student::where('name', $search)
                ->orWhere('student_id', $search)
                ->first();
            if ($matchedStudent) {
                $highlightedStudent = $matchedStudent;
            }
        }

        if ($highlightedStudent) {
            $studentTxs = CoinTransaction::where('student_id', $highlightedStudent->id)
                ->with(['program'])
                ->orderByDesc('id')
                ->get();

            // Group transactions by program to show the per-program journey
            $byProgram = $studentTxs->groupBy('program_id');
            foreach ($byProgram as $progId => $txs) {
                $firstTx = $txs->first();
                $prog = $firstTx->program;

                $earnedInProg = (int) $txs->where('transaction_type', 'credit')->sum('coins');
                $clawedBackInProg = (int) $txs->sum(function ($t) {
                    if ($t->transaction_type === 'reversal') {
                        return (int) ($t->metadata['clawback_amount'] ?? $t->metadata['previous_coins'] ?? abs($t->coins));
                    }

                    return (int) ($t->metadata['clawback_amount'] ?? 0);
                });

                $studentProgramBreakdown[] = [
                    'program_name' => $prog?->name ?? 'General / Other',
                    'program_code' => $prog?->code ?? '-',
                    'prize_position' => $firstTx->prize_position,
                    'grade' => $firstTx->grade,
                    'current_coins' => $earnedInProg,
                    'clawback' => $clawedBackInProg,
                    'history' => $txs,
                ];
            }
        }

        $allPrograms = Program::orderBy('name')->get();
        $allGroups = Group::orderBy('name')->get();

        return view('admin.coins.index', array_merge($metrics, [
            'transactions' => $transactions,
            'highlightedStudent' => $highlightedStudent,
            'studentProgramBreakdown' => $studentProgramBreakdown,
            'allPrograms' => $allPrograms,
            'allGroups' => $allGroups,
            'search' => $search,
            'selectedType' => $selectedType,
            'selectedProgram' => $selectedProgram,
            'selectedGroup' => $selectedGroup,
            'selectedStudentId' => $selectedStudentId,
            'perPage' => $perPage,
        ]));
    }

    /**
     * Dedicated printable report showing all coin transaction audit details together.
     */
    public function print(Request $request): View
    {
        CoinTransaction::ensureSchema();

        $metrics = $this->getCoinMetrics();
        $query = $this->buildTransactionsQuery($request);
        $transactions = $query->get();

        $search = trim((string) ($request->query('search') ?? ''));
        $selectedType = $request->query('type', 'all');

        $selectedProgramModel = $request->query('program') && $request->query('program') !== 'all'
            ? Program::find($request->query('program'))
            : null;

        $selectedGroupModel = $request->query('group') && $request->query('group') !== 'all'
            ? Group::find($request->query('group'))
            : null;

        $selectedStudent = $request->query('student_id')
            ? Student::with('group')->find($request->query('student_id'))
            : null;

        $totalFilteredCredit = (int) $transactions->where('transaction_type', 'credit')->where('coins', '>', 0)->sum('coins');
        $totalFilteredDebit = (int) abs($transactions->where('transaction_type', 'debit')->sum('coins'));
        $totalFilteredClawback = (int) $transactions->sum(function ($tx) {
            if ($tx->transaction_type === 'reversal') {
                return (int) ($tx->metadata['clawback_amount'] ?? $tx->metadata['previous_coins'] ?? abs($tx->coins));
            }

            return (int) ($tx->metadata['clawback_amount'] ?? 0);
        });
        $netFilteredCoins = (int) $transactions->sum('coins');

        return view('admin.coins.print', array_merge($metrics, [
            'transactions' => $transactions,
            'search' => $search,
            'selectedType' => $selectedType,
            'selectedProgramModel' => $selectedProgramModel,
            'selectedGroupModel' => $selectedGroupModel,
            'selectedStudent' => $selectedStudent,
            'totalFilteredCredit' => $totalFilteredCredit,
            'totalFilteredDebit' => $totalFilteredDebit,
            'totalFilteredClawback' => $totalFilteredClawback,
            'netFilteredCoins' => $netFilteredCoins,
        ]));
    }

    /**
     * Overall Festival Coin Metrics.
     */
    protected function getCoinMetrics(): array
    {
        $totalEarned = (int) CoinTransaction::where('transaction_type', 'credit')
            ->where('coins', '>', 0)
            ->sum('coins');

        $totalRedeemed = (int) abs(CoinTransaction::where('transaction_type', 'debit')->sum('coins'));

        $totalActiveBalance = (int) Student::sum('coins_cache');

        $studentsWithCoinsCount = Student::where('coins_cache', '>', 0)->count();

        $clawbackTxs = CoinTransaction::where(function ($q) {
            $q->where('transaction_type', 'reversal')
                ->orWhere('metadata->judgment_updated', true);
        })->get();

        $totalClawedBack = (int) $clawbackTxs->sum(function ($tx) {
            if ($tx->transaction_type === 'reversal') {
                return (int) ($tx->metadata['clawback_amount'] ?? $tx->metadata['previous_coins'] ?? abs($tx->coins));
            }

            return (int) ($tx->metadata['clawback_amount'] ?? 0);
        });

        return [
            'totalEarned' => $totalEarned,
            'totalRedeemed' => $totalRedeemed,
            'totalActiveBalance' => $totalActiveBalance,
            'studentsWithCoinsCount' => $studentsWithCoinsCount,
            'totalClawedBack' => $totalClawedBack,
        ];
    }

    /**
     * Build filtered transactions query based on request parameters.
     */
    protected function buildTransactionsQuery(Request $request)
    {
        $search = trim((string) ($request->query('search') ?? ''));
        $selectedType = $request->query('type', 'all');
        $selectedProgram = $request->query('program', 'all');
        $selectedGroup = $request->query('group', 'all');
        $selectedStudentId = $request->query('student_id');

        $query = CoinTransaction::with(['student.group', 'program', 'group', 'result'])
            ->orderByDesc('id');

        if ($selectedType && $selectedType !== 'all') {
            if ($selectedType === 'credit') {
                $query->where('transaction_type', 'credit')->where('coins', '>', 0);
            } elseif ($selectedType === 'clawback') {
                $query->where(function ($q) {
                    $q->where('transaction_type', 'reversal')
                        ->orWhere('metadata->judgment_updated', true);
                });
            } elseif ($selectedType === 'debit') {
                $query->where('transaction_type', 'debit');
            } elseif ($selectedType === 'reversal') {
                $query->where('transaction_type', 'reversal');
            }
        }

        if ($selectedProgram && $selectedProgram !== 'all') {
            $query->where('program_id', $selectedProgram);
        }

        if ($selectedGroup && $selectedGroup !== 'all') {
            $query->where(function ($q) use ($selectedGroup) {
                $q->where('group_id', $selectedGroup)
                    ->orWhereHas('student', fn ($sq) => $sq->where('group_id', $selectedGroup));
            });
        }

        if ($selectedStudentId) {
            $query->where('student_id', $selectedStudentId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('transaction_reference', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('student_id', 'like', "%{$search}%");
                    })
                    ->orWhereHas('program', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    /**
     * Resynchronize all student wallet balances from the complete ledger.
     */
    public function syncAll(CoinRewardService $coinService): RedirectResponse
    {
        $coinService->syncStudentCoinsCache();

        return back()->with('success', 'All student coin balances successfully recalculated and verified from the ledger.');
    }

    /**
     * Provide JSON endpoint for student coin journey and audit details.
     */
    public function studentBreakdown(Student $student): JsonResponse
    {
        $transactions = CoinTransaction::where('student_id', $student->id)
            ->with(['program'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'student_id' => $student->student_id,
                'group' => $student->group?->name,
                'coins_cache' => $student->coins_cache,
            ],
            'transactions' => $transactions,
        ]);
    }
}
