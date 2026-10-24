@extends('layouts.admin')

@section('title', 'Festival Fund Collection Ledger | QUAF 09')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" 
     x-data="{ 
        deleteModalOpen: false, 
        currentReceipt: { id: null, number: '', donor: '', amount: 0 },
        deleteReason: '',
        openDeleteModal(id, number, donor, amount) {
            this.currentReceipt = { id, number, donor, amount };
            this.deleteReason = '';
            this.deleteModalOpen = true;
        }
     }">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-600 text-white">
                    Finance & Collections
                </span>
                <span class="text-xs text-slate-500 font-mono">Central Accounting Ledger</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-2 font-sora">
                Fund Collection & Receipts Ledger
            </h1>
            <p class="text-sm text-slate-600 mt-1 max-w-2xl font-sora">
                View all festival contribution receipts, track total income, manage donors, and authorize deletions.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.fund.settings') }}" 
               class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                <span>Receipt Customizer</span>
            </a>
            <a href="{{ route('fund.dashboard') }}" target="_blank"
               class="px-4 py-2.5 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 font-bold text-xs transition-colors flex items-center gap-1.5">
                <span>Open Fund Desk ↗</span>
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Metric Badges Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Grand Total Amount -->
        <div class="bg-white p-5 rounded-2xl border border-emerald-200 bg-emerald-50/20 shadow-2xs">
            <div class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">Total Amount Collected</div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-700 font-mono mt-1">₹{{ number_format($totalAmount, 2) }}</div>
            <div class="text-xs text-emerald-600 mt-1 font-mono font-medium">{{ number_format($totalCount) }} active receipts</div>
        </div>

        <!-- Today's Collection -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Today's Collection</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 font-mono mt-1">₹{{ number_format($todayAmount, 2) }}</div>
            <div class="text-xs text-slate-400 mt-1 font-mono">Recorded today</div>
        </div>

        <!-- Active Receipts Count -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Donors / Receipts</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 font-mono mt-1">{{ number_format($totalCount) }}</div>
            <div class="text-xs text-slate-400 mt-1">Issued across all desks</div>
        </div>

        <!-- Cancelled / Deleted Receipts -->
        <div class="bg-white p-5 rounded-2xl border border-rose-200 bg-rose-50/20 shadow-2xs">
            <div class="text-xs font-semibold text-rose-700 uppercase tracking-wider">Cancelled / Void</div>
            <div class="text-2xl sm:text-3xl font-black text-rose-600 font-mono mt-1">{{ number_format($cancelledCount) }}</div>
            <div class="text-xs text-rose-500 mt-1 font-mono">₹{{ number_format($cancelledAmount, 2) }} voided</div>
        </div>
    </div>

    <!-- Top Collected Groups Standings -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                        Group Standings
                    </span>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight font-sora">
                        Top Collected Groups
                    </h2>
                </div>
                <p class="text-xs text-slate-500 font-anek mt-0.5">
                    ഗ്രൂപ്പുകളുടെ അടിസ്ഥാനത്തിലുള്ള ആകെ ഫണ്ട് കളക്ഷൻ റാങ്കിംഗ് ലിസ്റ്റ്.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('fund.standings.poster') }}" target="_blank"
                   class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <span>Preview Poster</span>
                </a>
                <a href="{{ route('fund.standings.download') }}"
                   class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black text-xs transition-all shadow-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Download Standing Poster</span>
                </a>
                <div class="text-xs font-mono text-slate-500 bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-200">
                    {{ $topGroups->count() }} Groups
                </div>
            </div>
        </div>

        <div class="space-y-3">
            @forelse($topGroups as $index => $grp)
                @php
                    $rank = $index + 1;
                    $collected = (float) ($grp->total_collected ?? 0);
                    $count = (int) ($grp->receipts_count ?? 0);

                    $isGold = ($rank === 1);
                    $isSilver = ($rank === 2);
                    $isBronze = ($rank === 3);

                    $cardBg = match($rank) {
                        1 => 'bg-gradient-to-r from-amber-500/10 via-amber-400/5 to-white border-2 border-amber-400/90 shadow-md shadow-amber-400/10',
                        2 => 'bg-gradient-to-r from-slate-200/30 via-slate-100/15 to-white border-2 border-slate-300 shadow-xs',
                        3 => 'bg-gradient-to-r from-amber-700/10 via-amber-600/5 to-white border-2 border-amber-700/35 shadow-2xs',
                        default => 'bg-slate-50 border border-slate-200/80 shadow-2xs'
                    };

                    $rankBadge = match($rank) {
                        1 => 'bg-gradient-to-br from-amber-400 via-amber-500 to-amber-600 text-slate-950 font-black shadow-md shadow-amber-500/30',
                        2 => 'bg-gradient-to-br from-slate-200 via-slate-300 to-slate-400 text-slate-900 font-black shadow-xs',
                        3 => 'bg-gradient-to-br from-amber-700 via-amber-600 to-amber-800 text-white font-black shadow-xs',
                        default => 'bg-slate-200/80 text-slate-700 border border-slate-300 font-bold'
                    };

                    $medalTag = match($rank) {
                        1 => 'GOLD',
                        2 => 'SILVER',
                        3 => 'BRONZE',
                        default => null
                    };
                @endphp

                <div class="p-4 sm:p-5 rounded-2xl transition-all hover:shadow-md flex items-center justify-between gap-3 sm:gap-4 relative overflow-hidden {{ $cardBg }}">
                    <!-- Left Vertical Color Accent Bar -->
                    <div class="absolute top-0 bottom-0 left-0 w-2" style="background-color: {{ $grp->color_hex ?? '#be1e2d' }};"></div>

                    <!-- Left: Rank & Group Info -->
                    <div class="flex items-center gap-3 sm:gap-4 min-w-0 pl-1.5">
                        
                        <!-- Rank Badge -->
                        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl flex flex-col items-center justify-center shrink-0 font-mono {{ $rankBadge }}">
                            <span class="text-xs sm:text-sm leading-none">
                                @if($isGold) 1st @elseif($isSilver) 2nd @elseif($isBronze) 3rd @else #{{ $rank }} @endif
                            </span>
                            @if($medalTag)
                                <span class="text-[8px] font-black uppercase tracking-tighter opacity-90 leading-none mt-0.5">{{ $medalTag }}</span>
                            @endif
                        </div>

                        <!-- Group Details -->
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] sm:text-xs font-mono font-black"
                                      style="background-color: {{ $grp->color_hex ?? '#be1e2d' }}20; color: {{ $grp->color_hex ?? '#be1e2d' }};">
                                    {{ $grp->code }}
                                </span>
                                <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight break-words">
                                    {{ $grp->name }}
                                </h3>
                            </div>
                            <div class="text-[10px] sm:text-xs font-mono text-slate-500 mt-0.5">
                                <span><strong class="text-slate-700">{{ $count }}</strong> {{ \Illuminate\Support\Str::plural('receipt', $count) }}</span>
                            </div>
                        </div>

                    </div>

                    <!-- Right: Total Collected Metric -->
                    <div class="text-right shrink-0 font-mono">
                        <div class="text-base sm:text-2xl font-black {{ $isGold ? 'text-amber-800' : ($isSilver ? 'text-slate-900' : ($isBronze ? 'text-amber-900' : 'text-slate-900')) }}">
                            ₹{{ number_format($collected, 0) }}
                        </div>
                    </div>

                </div>
            @empty
                <div class="py-8 text-center text-slate-400 text-xs font-mono">
                    No group standings available.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
        <form method="GET" action="{{ route('admin.fund.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <!-- Search Text -->
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold uppercase text-slate-400 font-mono mb-1">Search</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search donor name, phone, receipt #..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 font-mono mb-1">Mode</label>
                <select name="payment_method" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Modes</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="upi" {{ request('payment_method') === 'upi' ? 'selected' : '' }}>UPI / GPay</option>
                    <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 font-mono mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Receipts</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled / Void</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 font-mono mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
            </div>

            <!-- Date To & Submit -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 font-mono mb-1">To Date</label>
                <div class="flex items-center gap-2">
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
                    <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-colors shrink-0">
                        Filter
                    </button>
                </div>
            </div>
        </form>

        @if(request('search') || request('payment_method') || request('status') || request('date_from') || request('date_to'))
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-mono">Filters active</span>
                <a href="{{ route('admin.fund.index') }}" class="text-[#be1e2d] font-bold hover:underline">
                    Reset All Filters
                </a>
            </div>
        @endif
    </div>

    <!-- Receipts Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4">Receipt #</th>
                        <th class="py-3.5 px-4">Donor Name & Place</th>
                        <th class="py-3.5 px-4">Phone</th>
                        <th class="py-3.5 px-4">Amount</th>
                        <th class="py-3.5 px-4">Payment Mode</th>
                        <th class="py-3.5 px-4">Collector</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($receipts as $rcp)
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $rcp->is_cancelled ? 'bg-rose-50/40 text-rose-900' : '' }}">
                            <!-- Receipt Number -->
                            <td class="py-3 px-4 font-mono font-bold">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-[11px] {{ $rcp->is_cancelled ? 'line-through text-slate-400' : 'text-slate-900' }}">
                                    {{ $rcp->receipt_number }}
                                </span>
                            </td>

                            <!-- Donor Name & Place -->
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $rcp->donor_name }}</div>
                                @if($rcp->donor_place)
                                    <div class="text-[11px] text-slate-500">{{ $rcp->donor_place }}</div>
                                @endif
                                @if($rcp->category)
                                    <span class="inline-block px-1.5 py-0.2 rounded text-[9px] bg-slate-100 text-slate-600 font-mono mt-0.5">
                                        {{ $rcp->category }}
                                    </span>
                                @endif
                            </td>

                            <!-- Phone -->
                            <td class="py-3 px-4 font-mono text-slate-600 text-[11px]">
                                <div>{{ $rcp->donor_phone ?: '—' }}</div>
                                @if($rcp->whatsapp_number)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $rcp->whatsapp_number) }}" target="_blank" class="inline-flex items-center gap-0.5 text-[10px] text-emerald-700 font-bold hover:underline mt-0.5">
                                        WA: {{ $rcp->whatsapp_number }} ↗
                                    </a>
                                @endif
                            </td>

                            <!-- Amount -->
                            <td class="py-3 px-4">
                                <span class="text-sm font-black font-mono {{ $rcp->is_cancelled ? 'text-rose-500 line-through' : 'text-emerald-700' }}">
                                    ₹{{ number_format($rcp->amount, 2) }}
                                </span>
                            </td>

                            <!-- Payment Mode -->
                            <td class="py-3 px-4 font-mono">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ strtoupper($rcp->payment_method) }}
                                </span>
                                @if($rcp->transaction_reference)
                                    <div class="text-[9px] text-slate-600 font-bold mt-0.5 truncate max-w-[130px]" title="{{ $rcp->transaction_reference }}">
                                        Ref: {{ $rcp->transaction_reference }}
                                    </div>
                                @endif
                                @if($rcp->screenshot_path)
                                    <div class="mt-1">
                                        <a href="{{ asset('storage/' . $rcp->screenshot_path) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded border border-sky-200 hover:bg-sky-100 transition-colors">
                                            Proof Screenshot ↗
                                        </a>
                                    </div>
                                @endif
                            </td>

                            <!-- Collector -->
                            <td class="py-3 px-4 text-slate-700">
                                <div class="text-[11px] font-semibold">{{ $rcp->collector_name ?? 'Fund Desk' }}</div>
                            </td>

                            <!-- Date -->
                            <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                                <div>{{ $rcp->created_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $rcp->created_at->format('h:i A') }}</div>
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4 font-mono">
                                @if($rcp->is_cancelled)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        VOID
                                    </span>
                                    @if($rcp->cancelled_reason)
                                        <div class="text-[9px] text-rose-600 mt-0.5 truncate max-w-[100px]" title="{{ $rcp->cancelled_reason }}">
                                            {{ $rcp->cancelled_reason }}
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        Active
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.fund.receipts.show', $rcp) }}" 
                                       target="_blank"
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors"
                                       title="View Voucher">
                                        Voucher ↗
                                    </a>

                                    <a href="{{ route('admin.fund.receipts.download', $rcp) }}" 
                                       class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold text-xs transition-colors"
                                       title="Download JPG Voucher">
                                        JPG ↓
                                    </a>

                                    @if(!$rcp->is_cancelled && !$rcp->trashed())
                                        <button type="button" 
                                                @click="openDeleteModal({{ $rcp->id }}, '{{ $rcp->receipt_number }}', '{{ addslashes($rcp->donor_name) }}', {{ $rcp->amount }})"
                                                class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition-colors"
                                                title="Delete / Cancel Receipt (Admin Only)">
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <p class="text-sm font-semibold text-slate-600">No receipt records found</p>
                                <p class="text-xs text-slate-400 mt-1">Receipts generated from the Fund Desk will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipts->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $receipts->links() }}
            </div>
        @endif
    </div>

    <!-- Admin Exclusive Delete Confirmation Modal -->
    <div x-show="deleteModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4"
             @click.away="deleteModalOpen = false">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-900">Authorize Receipt Deletion</h3>
                    <p class="text-xs text-slate-500 font-anek">ഈ രസീത് അഡ്മിൻ പാനലിൽ നിന്ന് റദ്ദാക്കുന്നു / ഡിലീറ്റ് ചെയ്യുന്നു.</p>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-1.5 font-mono">
                <div>Receipt: <strong class="text-slate-900" x-text="currentReceipt.number"></strong></div>
                <div>Donor: <strong class="text-slate-900" x-text="currentReceipt.donor"></strong></div>
                <div>Amount: <strong class="text-emerald-700">₹<span x-text="Number(currentReceipt.amount).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span></strong></div>
            </div>

            <form method="POST" :action="'{{ url('admin/fund/receipts') }}/' + currentReceipt.id" class="space-y-4">
                @csrf
                @method('DELETE')

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Reason for Deletion / Cancellation <span class="text-rose-500">*</span></label>
                    <input type="text" name="reason" x-model="deleteReason" required
                           placeholder="e.g. Donor cancelled, duplicate entry, incorrect amount"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-rose-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="deleteModalOpen = false" 
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition-colors shadow-md shadow-rose-600/20">
                        Confirm & Delete Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
