@extends('layouts.presentation', ['title' => 'Presentation Report'])

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>Coin Redemption & Gift Distribution Report</span>
            </h1>
            <p class="text-xs text-slate-500 font-anek">പ്രസന്റേഷൻ ഡെസ്കിൽ നിന്നും വിതരണം ചെയ്ത മുഴുവൻ ഇനങ്ങളുടെയും സംഗ്രഹം.</p>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold font-mono uppercase transition-all shadow-md flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Report</span>
            </button>
            <a href="{{ route('presentation.dashboard') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 hover:border-slate-400 bg-white text-slate-700 text-xs font-bold font-mono uppercase transition-all shadow-xs">
                Back to Desk
            </a>
        </div>
    </div>

    <!-- Printable Official Header -->
    <div class="hidden print:block text-center border-b pb-4 mb-6">
        <h2 class="text-xl font-black">QUAF SEASON 09 — PRESENTATION DESK</h2>
        <div class="text-xs font-mono text-slate-500">Official Coins & Gifts Distribution Summary Report</div>
        <div class="text-xs text-slate-400 mt-1">Generated: {{ now()->format('d/m/Y h:i A') }}</div>
    </div>

    <!-- Date Filter Form -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs print:hidden">
        <form method="GET" action="{{ route('presentation.report') }}" class="flex flex-wrap items-end gap-4 text-xs font-mono">
            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800">
            </div>
            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800">
            </div>
            <div>
                <button type="submit" class="px-5 py-2.5 bg-[#be1e2d] hover:bg-[#a01824] text-white rounded-xl font-bold uppercase transition-all cursor-pointer">
                    Apply Filter
                </button>
            </div>
            @if($startDate || $endDate)
                <div>
                    <a href="{{ route('presentation.report') }}" class="px-4 py-2.5 text-slate-500 hover:text-slate-800 font-bold uppercase underline">Clear</a>
                </div>
            @endif
        </form>
    </div>

    <!-- Metric Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-mono font-bold uppercase text-slate-400">Total Coins Redeemed</div>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ number_format($totalCoinsRedeemed) }} Coins</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-mono font-bold uppercase text-slate-400">Total Items Distributed</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalItemsCount) }} Units</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-mono font-bold uppercase text-slate-400">Recognition Gifts Delivered</div>
            <div class="text-2xl font-black text-teal-600 mt-1">{{ number_format($totalGiftsDelivered ?? 0) }} <span class="text-xs font-mono font-normal text-slate-400">/ {{ number_format($totalStudents ?? 550) }}</span></div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-mono font-bold uppercase text-slate-400">Total Transactions</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($transactions->count()) }}</div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-200 text-slate-500 font-mono uppercase text-[10px] bg-slate-50">
                    <th class="py-3 px-4">#</th>
                    <th class="py-3 px-4">Receipt</th>
                    <th class="py-3 px-4">Date</th>
                    <th class="py-3 px-4">Student</th>
                    <th class="py-3 px-4">Group</th>
                    <th class="py-3 px-4">Item</th>
                    <th class="py-3 px-4 text-center">Qty</th>
                    <th class="py-3 px-4 text-right">Coins</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $idx => $t)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-2.5 px-4 font-mono text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-2.5 px-4 font-mono font-bold text-slate-700">{{ $t->transaction_reference }}</td>
                        <td class="py-2.5 px-4 font-mono text-slate-500">{{ $t->created_at?->format('d/m/y h:i A') }}</td>
                        <td class="py-2.5 px-4 font-bold text-slate-900">
                            {{ $t->student?->name }} <span class="font-mono text-[10px] text-slate-400 font-normal">({{ $t->student?->student_id }})</span>
                        </td>
                        <td class="py-2.5 px-4">
                            <span class="font-mono font-bold text-[11px]" style="color: {{ $t->student?->group?->color_hex ?? '#be1e2d' }}">
                                {{ $t->student?->group?->name ?? '---' }}
                            </span>
                        </td>
                        <td class="py-2.5 px-4 font-medium text-slate-800">{{ $t->description }}</td>
                        <td class="py-2.5 px-4 text-center font-mono font-bold">{{ $t->metadata['quantity'] ?? 1 }}</td>
                        <td class="py-2.5 px-4 text-right font-mono font-black {{ $t->coins < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $t->coins }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400 font-mono">No transactions in this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
