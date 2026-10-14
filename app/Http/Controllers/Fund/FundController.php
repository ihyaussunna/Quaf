<?php

namespace App\Http\Controllers\Fund;

use App\Http\Controllers\Controller;
use App\Models\FundReceipt;
use App\Models\FundReceiptSetting;
use App\Services\AuditLogger;
use App\Services\ReceiptImageGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FundController extends Controller
{
    /**
     * Display the Fund Collection Panel Dashboard.
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
        $query = FundReceipt::query()->with('collector')->latest();

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
     * Store a newly issued fund receipt.
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
            'transaction_reference' => $validated['transaction_reference'] ? trim($validated['transaction_reference']) : null,
            'category' => $validated['category'] ? trim($validated['category']) : 'Festival Fund',
            'notes' => $validated['notes'] ? trim($validated['notes']) : null,
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
