<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FundReceipt;
use App\Models\FundReceiptSetting;
use App\Models\Group;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ReceiptImageGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FundManagementController extends Controller
{
    /**
     * Display the Admin Fund Collection & Receipts Ledger.
     */
    public function index(Request $request): View
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
        $query = FundReceipt::withTrashed()->with(['collector', 'cancelledBy'])->latest();

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

        return view('admin.fund.index', compact(
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
     * View and print receipt from admin panel.
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
     * Admin-exclusive receipt cancellation/deletion.
     */
    public function destroy(Request $request, FundReceipt $receipt): RedirectResponse
    {
        $reason = trim((string) $request->input('reason', 'Cancelled by Admin'));

        $receipt->update([
            'is_cancelled' => true,
            'cancelled_reason' => $reason,
            'cancelled_at' => now(),
            'cancelled_by' => Auth::id(),
        ]);

        $receipt->delete(); // Soft delete

        AuditLogger::log('delete_fund_receipt', $receipt, ['reason' => $reason], ['is_cancelled' => true]);

        return back()->with('success', "രസീത് {$receipt->receipt_number} വിജയകരമായി റദ്ദാക്കി / ഡിലീറ്റ് ചെയ്തു.");
    }

    /**
     * Receipt template and layout customizer.
     */
    public function settings(): View
    {
        $settings = FundReceiptSetting::current();

        $latestReceipt = FundReceipt::latest()->first();
        $sampleReceipt = $latestReceipt ?? new FundReceipt([
            'receipt_number' => 'QFR-26-042',
            'donor_name' => 'MUHAMMED SHAFI K',
            'donor_phone' => '+91 98470 12345',
            'donor_place' => 'KOZHIKODE',
            'amount' => 5000.00,
            'amount_in_words' => FundReceipt::amountInWords(5000),
            'payment_method' => 'upi',
            'transaction_reference' => 'UPI/4298129048/OKAXIS',
            'category' => 'Festival Fund',
            'notes' => 'Festival contribution',
            'collector_name' => Auth::user()?->name ?? 'QUAF Fund Desk',
        ]);

        if (! $sampleReceipt->created_at) {
            $sampleReceipt->created_at = now();
        }

        return view('admin.fund.settings', compact('settings', 'sampleReceipt'));
    }

    /**
     * Update receipt template and layout settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'header_title' => ['required', 'string', 'max:200'],
            'organization_name' => ['required', 'string', 'max:200'],
            'subtitle' => ['required', 'string', 'max:250'],
            'text_alignment' => ['required', 'string', 'in:left,center,right'],
            'font_size' => ['required', 'string', 'in:small,normal,large'],
            'padding_size' => ['required', 'string', 'in:compact,normal,relaxed'],
            'primary_color' => ['required', 'string', 'max:25'],
            'accent_color' => ['required', 'string', 'max:25'],
            'text_color' => ['required', 'string', 'max:25'],
            'bg_color' => ['required', 'string', 'max:25'],
            'show_logo' => ['nullable', 'boolean'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'authorized_by' => ['required', 'string', 'max:200'],

            // Poster Customizer Rules (Placement, Size, Color, Alignment)
            'rcp_x' => ['nullable', 'integer', 'min:0', 'max:1126'],
            'rcp_y' => ['nullable', 'integer', 'min:0', 'max:1407'],
            'rcp_font_size' => ['nullable', 'integer', 'min:8', 'max:100'],
            'rcp_color' => ['nullable', 'string', 'max:25'],
            'rcp_align' => ['nullable', 'string', 'in:left,center,right'],

            'date_x' => ['nullable', 'integer', 'min:0', 'max:1126'],
            'date_y' => ['nullable', 'integer', 'min:0', 'max:1407'],
            'date_font_size' => ['nullable', 'integer', 'min:8', 'max:100'],
            'date_color' => ['nullable', 'string', 'max:25'],
            'date_align' => ['nullable', 'string', 'in:left,center,right'],

            'donor_x' => ['nullable', 'integer', 'min:0', 'max:1126'],
            'donor_y' => ['nullable', 'integer', 'min:0', 'max:1407'],
            'donor_font_size' => ['nullable', 'integer', 'min:8', 'max:100'],
            'donor_color' => ['nullable', 'string', 'max:25'],
            'donor_align' => ['nullable', 'string', 'in:left,center,right'],

            'amount_x' => ['nullable', 'integer', 'min:0', 'max:1126'],
            'amount_y' => ['nullable', 'integer', 'min:0', 'max:1407'],
            'amount_font_size' => ['nullable', 'integer', 'min:8', 'max:100'],
            'amount_color' => ['nullable', 'string', 'max:25'],
            'amount_align' => ['nullable', 'string', 'in:left,center,right'],
        ]);

        $settings = FundReceiptSetting::current();

        $validated['show_logo'] = $request->boolean('show_logo');

        $settings->update($validated);

        AuditLogger::log('update_fund_receipt_settings', $settings, null, $validated);

        return redirect()->route('admin.fund.settings')
            ->with('success', 'രസീത് ഡിസൈൻ സെറ്റിംഗ്സ് വിജയകരമായി അപ്ഡേറ്റ് ചെയ്തു!');
    }
}
