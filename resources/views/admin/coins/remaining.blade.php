@extends('layouts.admin', ['title' => 'Students with Remaining Coins | Quaf Fest Management'])

@section('content')
<style>
    @media print {
        .no-print, nav, aside, header, .admin-sidebar, #sidebar {
            display: none !important;
        }
        body {
            background-color: #ffffff !important;
            color: #000000 !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .print-container {
            box-shadow: none !important;
            border: none !important;
            max-width: 100% !important;
            width: 100% !important;
            padding: 5mm !important;
            margin: 0 !important;
        }
        tr {
            page-break-inside: avoid !important;
        }
        thead {
            display: table-header-group !important;
        }
    }
    @page {
        size: A4 portrait;
        margin: 8mm;
    }
</style>

<div class="space-y-6">

    <!-- Header Section (Screen Only) -->
    <div class="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.coins.index') }}" class="text-xs font-mono text-slate-500 hover:text-slate-800 transition">
                    ← Coin Ledger
                </a>
                <span class="text-slate-300">•</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-900 border border-amber-200 uppercase">
                    Active Balances
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-sora tracking-tight mt-1">
                Students with Remaining Coins (ബാക്കി കോയിൻ ഉള്ളവർ)
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-mono">
                വാലറ്റിൽ കോയിൻ ബാക്കിയുള്ള വിദ്യാർത്ഥികളുടെ ക്യുഎഫ് നമ്പറും വിശദവിവരങ്ങളും
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.coins.remaining', array_merge(request()->query(), ['export' => 'csv'])) }}" 
               class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 font-bold text-xs hover:bg-slate-50 transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer"
               title="Download CSV spreadsheet for Excel">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Export CSV (Excel)</span>
            </a>

            <button type="button" 
                    onclick="window.print()" 
                    class="px-4 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs font-mono uppercase tracking-wider transition-all flex items-center gap-1.5 shadow-md cursor-pointer">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print / Save PDF</span>
            </button>
        </div>
    </div>

    <!-- 4 Stats Cards (Screen Only) -->
    <div class="no-print grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Students with Coins -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs">
            <div class="text-xs font-mono text-slate-500 font-bold uppercase">Students with Balance</div>
            <div class="mt-2 text-2xl font-black font-sora text-slate-900">
                {{ number_format($totalStudentsWithBalance) }}
            </div>
            <div class="text-[11px] font-mono text-slate-400 mt-0.5">ആകെ കോയിൻ ബാക്കിയുള്ളവർ</div>
        </div>

        <!-- Total Remaining Coins -->
        <div class="p-4 rounded-2xl bg-gradient-to-br from-amber-500/10 via-white to-amber-50/50 border border-amber-300/80 shadow-2xs">
            <div class="text-xs font-mono text-amber-900 font-bold uppercase">Total Active Coins</div>
            <div class="mt-2 text-2xl font-black font-sora text-[#be1e2d]">
                {{ number_format($totalRemainingCoins) }}
            </div>
            <div class="text-[11px] font-mono text-amber-800 mt-0.5">വാലറ്റുകളിൽ ശേഷിക്കുന്ന ആകെ കോയിൻ</div>
        </div>

        <!-- Average Coins per Student -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs">
            <div class="text-xs font-mono text-slate-500 font-bold uppercase">Average Balance</div>
            <div class="mt-2 text-2xl font-black font-sora text-slate-800">
                {{ number_format($averageRemaining) }}
            </div>
            <div class="text-[11px] font-mono text-slate-400 mt-0.5">ശരാശരി ബാക്കി കോയിൻ</div>
        </div>

        <!-- Top Wallet Balance -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs">
            <div class="text-xs font-mono text-slate-500 font-bold uppercase">Top Balance Holder</div>
            <div class="mt-2 text-lg font-black font-sora text-slate-900 truncate" title="{{ $topStudent?->name }}">
                {{ $topStudent?->name ?? '—' }}
            </div>
            <div class="text-[11px] font-mono text-[#be1e2d] font-bold mt-0.5">
                #{{ $topStudent?->chest_number ?? ($topStudent?->student_id ?? '—') }} • {{ number_format($topStudent?->coins_cache ?? 0) }} Coins
            </div>
        </div>

    </div>

    <!-- Filter Control Bar (Screen Only) -->
    <div class="no-print bg-white border border-slate-300 rounded-2xl p-4 shadow-sm space-y-3">
        <form method="GET" action="{{ route('admin.coins.remaining') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3">
            
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Search QF # or Name</label>
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="e.g. Chest No, QF No, Name..." 
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
            </div>

            <!-- Group Filter -->
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Team / Group</label>
                <select name="group" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="all">All Teams (എല്ലാ ടീമും)</option>
                    @foreach($allGroups as $grp)
                        <option value="{{ $grp->id }}" {{ (string) $selectedGroup === (string) $grp->id ? 'selected' : '' }}>
                            {{ $grp->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Zone Filter -->
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Zone / Category</label>
                <select name="zone" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="all">All Zones</option>
                    @foreach($zones as $val => $label)
                        <option value="{{ $val }}" {{ $selectedZone == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Minimum Coins Filter -->
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Min Balance</label>
                <select name="min_coins" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Remaining (> 0)</option>
                    <option value="50" {{ $minCoins == '50' ? 'selected' : '' }}>50+ Coins</option>
                    <option value="100" {{ $minCoins == '100' ? 'selected' : '' }}>100+ Coins</option>
                    <option value="150" {{ $minCoins == '150' ? 'selected' : '' }}>150+ Coins</option>
                    <option value="200" {{ $minCoins == '200' ? 'selected' : '' }}>200+ Coins</option>
                </select>
            </div>

            <!-- Sort By -->
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Sort By</label>
                <select name="sort" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="coins_desc" {{ $sort === 'coins_desc' ? 'selected' : '' }}>Highest Coins First</option>
                    <option value="coins_asc" {{ $sort === 'coins_asc' ? 'selected' : '' }}>Lowest Coins First</option>
                    <option value="qf" {{ $sort === 'qf' ? 'selected' : '' }}>QF Number Order</option>
                    <option value="name" {{ $sort === 'name' ? 'selected' : '' }}>Student Name (A-Z)</option>
                </select>
            </div>

            <div class="col-span-full flex items-center justify-between pt-1 border-t border-slate-100 text-xs">
                <div class="font-mono text-slate-500">
                    Showing <strong class="text-slate-900">{{ number_format($students->total()) }}</strong> students (Total <strong class="text-[#be1e2d]">{{ number_format($filteredTotalCoins) }}</strong> coins)
                </div>
                <div class="flex items-center gap-2">
                    @if($search || $selectedGroup !== 'all' || $selectedZone !== 'all' || $minCoins)
                        <a href="{{ route('admin.coins.remaining') }}" class="px-3 py-1.5 rounded-lg text-xs font-mono font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                            Reset Filters
                        </a>
                    @endif
                    <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-mono font-bold text-white bg-slate-900 hover:bg-slate-800 transition">
                        Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Printable Report Container -->
    <div class="print-container bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden p-4 sm:p-6">

        <!-- Official Header (Visible on Print & Screen) -->
        <div class="border-b-2 border-slate-900 pb-4 mb-4 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
                <div class="text-[10px] font-mono font-bold tracking-widest text-slate-500 uppercase">
                    QUAF 09 • MARKAZ CULTURAL FESTIVAL 2026
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-0.5 uppercase">
                    Active Student Coin Balances (ബാക്കി കോയിൻ റിപ്പോർട്ട്)
                </h2>
                <p class="text-xs text-slate-600 mt-1 font-mono">
                    Official audit list of contestants with unspent festival coins in their active wallet
                </p>
            </div>

            <div class="text-left sm:text-right shrink-0 font-mono text-xs space-y-0.5">
                <div class="font-bold text-slate-900">DATE: {{ now()->format('d M Y, h:i A') }}</div>
                <div class="text-slate-500">TOTAL STUDENTS: <strong class="text-slate-900">{{ number_format($students->total()) }}</strong></div>
                <div class="text-[#be1e2d] font-bold">TOTAL REMAINING: {{ number_format($filteredTotalCoins) }} COINS</div>
                @if($selectedGroup !== 'all')
                    @php $grpObj = $allGroups->firstWhere('id', $selectedGroup); @endphp
                    @if($grpObj)
                        <div class="text-blue-700 font-bold">TEAM: {{ $grpObj->name }}</div>
                    @endif
                @endif
            </div>
        </div>

        @if($students->isEmpty())
            <div class="py-16 text-center text-slate-400 font-mono text-xs">
                No students found with remaining coin balance for the selected filter.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-900 text-white font-mono uppercase text-[11px] border-b border-slate-800">
                            <th class="py-3 px-3 w-12 text-center">#</th>
                            <th class="py-3 px-3 w-28 bg-slate-800 text-amber-300 font-black">QF Number</th>
                            <th class="py-3 px-4 min-w-[200px]">Contestant Name</th>
                            <th class="py-3 px-3 min-w-[140px]">Group / Team</th>
                            <th class="py-3 px-3 w-28">Zone</th>
                            <th class="py-3 px-3 text-right w-24">Earned</th>
                            <th class="py-3 px-3 text-right w-24">Redeemed</th>
                            <th class="py-3 px-3 text-right w-32 bg-amber-500/10 text-[#be1e2d] font-black">Balance Coins</th>
                            <th class="no-print py-3 px-3 text-center w-36">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-sora">
                        @foreach($students as $idx => $s)
                            @php
                                $chest = $s->chest_number ?: $s->student_id;
                                $earned = (int) ($s->total_earned ?? 0);
                                $redeemed = abs((int) ($s->total_redeemed ?? 0));
                                $teamColor = $s->group?->color_hex ?? '#be1e2d';
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors avoid-break {{ $idx % 2 === 1 ? 'bg-slate-50/40' : 'bg-white' }}">
                                
                                <!-- Serial No -->
                                <td class="py-2.5 px-3 text-center font-mono text-slate-500 text-[11px]">
                                    {{ $students->firstItem() + $idx }}
                                </td>

                                <!-- QF Number (Chest Number) -->
                                <td class="py-2.5 px-3 font-mono font-black text-sm text-slate-950 bg-slate-50/80 border-r border-slate-200">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-900 text-amber-300 shadow-2xs">
                                        #{{ $chest }}
                                    </span>
                                </td>

                                <!-- Student Name -->
                                <td class="py-2.5 px-4 font-bold text-slate-900">
                                    <a href="{{ route('verify.student', $s->qr_token ?: $chest) }}" target="_blank" class="hover:text-[#be1e2d] transition flex items-center gap-1.5" title="View Digital Delegate Card">
                                        <span>{{ $s->name }}</span>
                                        <svg class="no-print w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                    @if(!empty($s->contact))
                                        <div class="text-[10px] font-mono text-slate-400 font-normal">Ph: {{ $s->contact }}</div>
                                    @endif
                                </td>

                                <!-- Team / Group -->
                                <td class="py-2.5 px-3 font-mono text-slate-700">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $teamColor }}"></span>
                                        <span class="font-semibold">{{ $s->group?->name ?? '—' }}</span>
                                    </span>
                                </td>

                                <!-- Zone -->
                                <td class="py-2.5 px-3 font-mono text-slate-600 text-[11px]">
                                    {{ $s->zone?->name ?? ($s->category ?? 'General') }}
                                </td>

                                <!-- Total Earned -->
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-700">
                                    +{{ number_format($earned) }}
                                </td>

                                <!-- Total Redeemed -->
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-600">
                                    @if($redeemed > 0)
                                        -{{ number_format($redeemed) }}
                                    @else
                                        <span class="text-slate-400 font-normal">0</span>
                                    @endif
                                </td>

                                <!-- Remaining Coin Balance -->
                                <td class="py-2.5 px-3 text-right font-mono font-black text-sm text-[#be1e2d] bg-amber-500/10 border-l border-slate-200">
                                    <span class="px-2 py-0.5 rounded-lg bg-amber-100 text-[#be1e2d] border border-amber-300 shadow-2xs font-black">
                                        {{ number_format($s->coins_cache) }}
                                    </span>
                                </td>

                                <!-- Action Buttons (Screen Only) -->
                                <td class="no-print py-2.5 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('verify.student', $s->qr_token ?: $chest) }}" 
                                           target="_blank" 
                                           class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-[10px] font-bold transition" 
                                           title="Open Digital ID Card">
                                            Card ↗
                                        </a>
                                        <a href="{{ route('admin.coins.index', ['student_id' => $s->id]) }}" 
                                           class="px-2 py-1 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-900 font-mono text-[10px] font-bold transition" 
                                           title="View Audit Ledger for this student">
                                            Ledger
                                        </a>
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-100 font-mono font-bold text-slate-800 border-t-2 border-slate-900 text-xs">
                        <tr>
                            <td colspan="5" class="py-3 px-4 text-right uppercase tracking-wider">Total Active Coin Balance:</td>
                            <td class="py-3 px-3 text-right font-black text-emerald-700">
                                +{{ number_format($students->sum('total_earned')) }}
                            </td>
                            <td class="py-3 px-3 text-right font-black text-rose-600">
                                -{{ number_format(abs($students->sum('total_redeemed'))) }}
                            </td>
                            <td class="py-3 px-3 text-right font-black text-sm text-[#be1e2d] bg-amber-100/60 border-l border-slate-300">
                                {{ number_format($filteredTotalCoins) }} Coins
                            </td>
                            <td class="no-print py-3 px-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Pagination (Screen Only) -->
            <div class="no-print mt-4 pt-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs font-mono text-slate-500">
                <div>
                    Showing {{ $students->firstItem() }} to {{ $students->lastItem() }} of {{ $students->total() }} students
                </div>
                <div>
                    {{ $students->links() }}
                </div>
            </div>

            <!-- Official Print Signature Footer -->
            <div class="mt-8 pt-6 border-t border-slate-300 hidden print:block">
                <div class="grid grid-cols-3 gap-6 text-center text-xs font-mono">
                    <div>
                        <div class="border-b border-slate-400 mb-2 h-6"></div>
                        <div class="font-bold text-slate-900">Presentation Cashier</div>
                        <div class="text-[10px] text-slate-500">Rewards Counter Desk</div>
                    </div>
                    <div>
                        <div class="border-b border-slate-400 mb-2 h-6"></div>
                        <div class="font-bold text-slate-900">Festival Accounts Officer</div>
                        <div class="text-[10px] text-slate-500">QUAF Central Finance</div>
                    </div>
                    <div>
                        <div class="border-b border-slate-400 mb-2 h-6"></div>
                        <div class="font-bold text-slate-900">General Convener</div>
                        <div class="text-[10px] text-slate-500">Ashabul Quaf Committee</div>
                    </div>
                </div>
            </div>
        @endif

    </div>

</div>
@endsection
