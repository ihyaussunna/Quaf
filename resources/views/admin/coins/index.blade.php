@extends('layouts.admin', ['title' => 'Festival Coin Ledger & Audit Hub | Quaf Fest Management'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-sora tracking-tight">Coin Ledger & Audit Hub</h1>
            <p class="text-xs text-slate-500 mt-1 font-mono">മത്സര ഫലങ്ങൾ, ജഡ്ജ്മെന്റ് അപ്ഡേറ്റ് ക്ലോബാക്കുകൾ, കാഷ്യർ റിഡംപ്ഷൻ എന്നിവയുടെ സമഗ്ര ഓഡിറ്റ് ലോഗ്</p>
        </div>
        <div class="flex items-center gap-2.5 print:hidden">
            <a href="{{ route('admin.coins.print', request()->query()) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 font-bold text-xs hover:bg-slate-50 transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Print entire audit ledger with all details without page limits">
                <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Audit (All Details)</span>
            </a>
            <form method="POST" action="{{ route('admin.coins.sync-all') }}" onsubmit="return confirm('Recalculate and synchronize all student coin balances from the ledger?')">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white font-semibold text-xs hover:bg-slate-800 transition-all flex items-center gap-1.5 shadow-sm cursor-pointer">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Recalculate All Balances</span>
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- 4 Key Coin Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 print:grid-cols-4">
        
        <!-- Total Coins Awarded -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between text-xs font-mono text-slate-500">
                <span class="font-bold">Total Awarded</span>
                <span class="p-1 rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black font-sora text-slate-900 leading-none">
                    +{{ number_format($totalEarned) }}
                </div>
                <div class="text-[11px] font-mono text-slate-400 mt-1">മത്സരങ്ങളിൽ നൽകിയ ആകെ കോയിൻ</div>
            </div>
        </div>

        <!-- Total Clawed Back / Reversals from Judgment Updates -->
        <div class="p-4 rounded-2xl bg-gradient-to-br from-rose-50/50 to-white border border-rose-200/80 shadow-2xs flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between text-xs font-mono text-rose-700">
                <span class="font-bold">Clawed Back / Returned</span>
                <span class="p-1 rounded-lg bg-rose-100 text-rose-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black font-sora text-rose-600 leading-none">
                    -{{ number_format($totalClawedBack) }}
                </div>
                <div class="text-[11px] font-mono text-rose-500 mt-1">ജഡ്ജ്മെന്റ് തിരുത്തലിൽ തിരിച്ചുപിടിച്ചവ</div>
            </div>
        </div>

        <!-- Total Redeemed at Presentation Desk -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between text-xs font-mono text-slate-500">
                <span class="font-bold">Total Redeemed</span>
                <span class="p-1 rounded-lg bg-blue-50 text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black font-sora text-slate-800 leading-none">
                    -{{ number_format($totalRedeemed) }}
                </div>
                <div class="text-[11px] font-mono text-slate-400 mt-1">ഗിഫ്റ്റുകൾക്കായി ചെലവഴിച്ചവ</div>
            </div>
        </div>

        <!-- Active Wallet Balance Across All Students -->
        <div class="p-4 rounded-2xl bg-gradient-to-br from-amber-500/10 via-white to-amber-50/50 border border-amber-300/80 shadow-2xs flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between text-xs font-mono text-amber-900">
                <span class="font-bold">Active Wallet Balance</span>
                <span class="p-1 rounded-lg bg-amber-400/20 text-amber-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black font-sora text-[#be1e2d] leading-none">
                    {{ number_format($totalActiveBalance) }}
                </div>
                <div class="text-[11px] font-mono text-amber-800 mt-1">{{ $studentsWithCoinsCount }} വിദ്യാർത്ഥികളുടെ വാലറ്റിൽ ലഭ്യമാണ്</div>
            </div>
        </div>

    </div>

    <!-- Student Spotlight Breakdown (When a student is filtered) -->
    @if($highlightedStudent)
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white shadow-md relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-700/80">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-400 text-slate-950 font-black font-sora flex items-center justify-center text-lg shadow-sm">
                        {{ substr($highlightedStudent->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold font-sora">{{ $highlightedStudent->name }}</h2>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-white/10 text-amber-300 border border-white/10">
                                #{{ $highlightedStudent->student_id }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-400 font-mono mt-0.5 flex items-center gap-2">
                            <span>Team: <strong class="text-white">{{ $highlightedStudent->group?->name }}</strong></span>
                            <span>•</span>
                            <span>Category: {{ $highlightedStudent->category }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <div class="text-xs font-mono uppercase tracking-wider text-slate-400">Current Wallet Balance</div>
                        <div class="text-2xl font-black font-sora text-amber-400 leading-none mt-1">
                            {{ number_format($highlightedStudent->coins_cache) }} <span class="text-xs font-mono text-white/80">Coins</span>
                        </div>
                    </div>
                    <a href="{{ route('admin.coins.index') }}" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-medium transition print:hidden">
                        Clear Filter ✕
                    </a>
                </div>
            </div>

            <!-- Per-Program Coin Journey for this Student -->
            <div class="mt-4">
                <h3 class="text-xs font-mono font-bold text-amber-300 uppercase tracking-wider mb-2.5">
                    ഒരു മത്സരത്തിൽ നിന്നും ലഭിച്ചതും തിരിച്ചുപിടിച്ചതുമായ കോയിനുകൾ (Per-Competition Breakdown):
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($studentProgramBreakdown as $b)
                        <div class="p-3 rounded-xl bg-white/5 border border-white/10 hover:border-amber-400/40 transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="font-sora font-bold text-xs truncate text-white">{{ $b['program_name'] }}</div>
                                        <div class="text-[10px] font-mono text-slate-400">{{ $b['program_code'] }}</div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold shrink-0 {{ $b['prize_position'] === 1 ? 'bg-amber-400 text-slate-950' : ($b['prize_position'] === 2 ? 'bg-slate-300 text-slate-900' : ($b['prize_position'] === 3 ? 'bg-amber-700 text-white' : 'bg-white/10 text-slate-300')) }}">
                                        {{ $b['prize_position'] ? "Pos {$b['prize_position']}" : 'Unplaced' }}
                                    </span>
                                </div>

                                <div class="mt-2.5 flex items-center justify-between text-xs font-mono">
                                    <span class="text-slate-400">Net Retained:</span>
                                    <span class="font-bold text-emerald-400 text-sm">+{{ number_format($b['current_coins']) }}</span>
                                </div>

                                @if($b['clawback'] > 0)
                                    <div class="mt-1 flex items-center justify-between text-xs font-mono text-rose-400">
                                        <span>Clawed Back:</span>
                                        <span class="font-bold">-{{ number_format($b['clawback']) }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-3 pt-2 border-t border-white/10 text-[10px] font-mono text-slate-400">
                                @php
                                    $latestTx = $b['history']->first();
                                @endphp
                                @if(!empty($latestTx->metadata['judgment_updated']))
                                    <span class="text-amber-300 font-bold">Judged Updated:</span> {{ $latestTx->metadata['action'] ?? 'Adjusted' }}
                                @else
                                    <span>Awarded: {{ $latestTx->created_at?->format('d M, h:i A') }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full p-3 rounded-xl bg-white/5 text-center text-xs text-slate-400 font-mono">
                            No competition coin records for this student.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-2xs print:hidden">
        <form method="GET" action="{{ route('admin.coins.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            <!-- Search participants / programs / references -->
            <div class="sm:col-span-4 relative">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search participant name, chest #, program..."
                       class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#be1e2d] focus:bg-white text-slate-900 font-medium transition-all">
            </div>

            <!-- Transaction Event Type Filter -->
            <div class="sm:col-span-3">
                <select name="type" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#be1e2d] focus:bg-white text-slate-900 font-medium transition-all">
                    <option value="all" {{ $selectedType === 'all' ? 'selected' : '' }}>All Events (എല്ലാ ഇവന്റുകളും)</option>
                    <option value="credit" {{ $selectedType === 'credit' ? 'selected' : '' }}>Competition Awards (+Coins)</option>
                    <option value="clawback" {{ $selectedType === 'clawback' ? 'selected' : '' }}>Judgment Clawbacks & Updates</option>
                    <option value="debit" {{ $selectedType === 'debit' ? 'selected' : '' }}>Cashier Gift Redemptions (-Coins)</option>
                    <option value="reversal" {{ $selectedType === 'reversal' ? 'selected' : '' }}>Reversals & Unplaced</option>
                </select>
            </div>

            <!-- Competition Program Filter -->
            <div class="sm:col-span-3">
                <select name="program" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#be1e2d] focus:bg-white text-slate-900 font-medium transition-all">
                    <option value="all" {{ $selectedProgram === 'all' ? 'selected' : '' }}>All Competitions (എല്ലാ മത്സരങ്ങളും)</option>
                    @foreach($allPrograms as $p)
                        <option value="{{ $p->id }}" {{ (string)$selectedProgram === (string)$p->id ? 'selected' : '' }}>{{ $p->code }} - {{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Team Filter & Clear -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <select name="group" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#be1e2d] focus:bg-white text-slate-900 font-medium transition-all">
                    <option value="all" {{ $selectedGroup === 'all' ? 'selected' : '' }}>All Teams</option>
                    @foreach($allGroups as $g)
                        <option value="{{ $g->id }}" {{ (string)$selectedGroup === (string)$g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
                @if($search !== '' || $selectedType !== 'all' || $selectedProgram !== 'all' || $selectedGroup !== 'all' || $selectedStudentId)
                    <a href="{{ route('admin.coins.index') }}" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Clear Filters">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Master Transactions Table -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-mono uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4 font-bold">#</th>
                        <th class="py-3.5 px-4 font-bold">Date & Time</th>
                        <th class="py-3.5 px-4 font-bold">Participant</th>
                        <th class="py-3.5 px-4 font-bold">Competition</th>
                        <th class="py-3.5 px-4 font-bold">Event & Prize</th>
                        <th class="py-3.5 px-4 font-bold text-right">Coins</th>
                        <th class="py-3.5 px-4 font-bold">Audit Description & Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($transactions as $tx)
                        @php
                            $rank = ($transactions->currentPage() - 1) * $transactions->perPage() + $loop->iteration;
                            $isCredit = $tx->transaction_type === 'credit' && $tx->coins > 0;
                            $isDebit = $tx->transaction_type === 'debit';
                            $isReversal = $tx->transaction_type === 'reversal';
                            $isJudgmentUpdated = !empty($tx->metadata['judgment_updated']);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $isJudgmentUpdated ? 'bg-amber-50/30' : ($isReversal ? 'bg-rose-50/20' : '') }}">
                            
                            <!-- Index -->
                            <td class="py-3 px-4 font-mono font-bold text-slate-400">
                                {{ sprintf('%02d', $rank) }}
                            </td>

                            <!-- Date & Time -->
                            <td class="py-3 px-4 font-mono text-slate-500 whitespace-nowrap">
                                <div>{{ $tx->created_at?->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $tx->created_at?->format('h:i A') }}</div>
                            </td>

                            <!-- Participant Details -->
                            <td class="py-3 px-4">
                                @if($tx->student)
                                    <div>
                                        <a href="{{ route('admin.coins.index', ['student_id' => $tx->student->id]) }}" class="font-sora font-extrabold text-slate-900 hover:text-[#be1e2d] transition-colors" title="View all coins for this student">
                                            {{ $tx->student->name }}
                                        </a>
                                        <div class="flex items-center gap-1.5 text-[10px] text-slate-500 font-mono mt-0.5">
                                            <span class="font-bold text-slate-700">#{{ $tx->student->student_id }}</span>
                                            <span>•</span>
                                            <span class="font-bold" style="color: {{ $tx->student->group?->color_hex ?? '#be1e2d' }}">{{ $tx->student->group?->name }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 font-mono">General Participant</span>
                                @endif
                            </td>

                            <!-- Competition / Program -->
                            <td class="py-3 px-4">
                                @if($tx->program)
                                    <div class="min-w-0">
                                        <div class="font-bold font-sora text-slate-900 truncate max-w-xs">{{ $tx->program->name }}</div>
                                        <div class="flex items-center gap-1.5 text-[10px] font-mono text-slate-500 mt-0.5">
                                            <span>{{ $tx->program->code }}</span>
                                            <span>•</span>
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold {{ $tx->program->isGroup() ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $tx->program->isGroup() ? 'Group' : 'Individual' }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 font-mono">Store Redemption / Cashier</span>
                                @endif
                            </td>

                            <!-- Event & Prize -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1 items-start">
                                    @if($isJudgmentUpdated)
                                        @if(($tx->metadata['action'] ?? '') === 'demoted')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                                Judgment Demoted (Clawback)
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                Judgment Promoted (Upgraded)
                                            </span>
                                        @endif
                                    @elseif($isCredit)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Competition Award
                                        </span>
                                    @elseif($isDebit)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            Gift Redemption
                                        </span>
                                    @elseif($isReversal)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            Reversal / Cancelled
                                        </span>
                                    @endif

                                    <div class="text-[11px] font-mono text-slate-600">
                                        @if($tx->prize_position)
                                            <span class="font-bold text-slate-800">
                                                {{ match($tx->prize_position) { 1 => '1st Prize', 2 => '2nd Prize', 3 => '3rd Prize', default => "Pos {$tx->prize_position}" } }}
                                            </span>
                                            @if($tx->grade && $tx->grade !== 'NO_GRADE')
                                                <span class="px-1 py-0.2 rounded bg-slate-100 text-slate-700 font-bold ml-1">[{{ $tx->grade }}]</span>
                                            @endif
                                        @elseif($isDebit)
                                            <span class="text-slate-500">Presentation Counter</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Coins Change -->
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                @if($isCredit)
                                    <div class="font-sora font-black text-sm text-emerald-600 leading-none">
                                        +{{ number_format($tx->coins) }}
                                    </div>
                                    @if($isJudgmentUpdated && !empty($tx->metadata['clawback_amount']))
                                        <div class="text-[10px] font-mono text-rose-500 font-bold mt-0.5">
                                            -{{ $tx->metadata['clawback_amount'] }} clawed back
                                        </div>
                                    @elseif($isJudgmentUpdated && !empty($tx->metadata['coins_added']))
                                        <div class="text-[10px] font-mono text-emerald-700 font-bold mt-0.5">
                                            +{{ $tx->metadata['coins_added'] }} upgraded
                                        </div>
                                    @endif
                                @elseif($isDebit)
                                    <div class="font-sora font-black text-sm text-blue-600 leading-none">
                                        -{{ number_format(abs($tx->coins)) }}
                                    </div>
                                @elseif($isReversal)
                                    <div class="font-sora font-black text-sm text-rose-600 leading-none">
                                        -{{ number_format((int)($tx->metadata['clawback_amount'] ?? abs($tx->coins))) }}
                                    </div>
                                    <div class="text-[9px] font-mono text-rose-500 font-bold">Unplaced</div>
                                @endif
                            </td>

                            <!-- Audit Description & Notes -->
                            <td class="py-3 px-4">
                                <div class="text-xs text-slate-700 max-w-md">
                                    {{ $tx->description }}
                                </div>
                                <div class="text-[10px] font-mono text-slate-400 mt-0.5">
                                    Ref: {{ $tx->transaction_reference }}
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <h4 class="font-sora font-bold text-slate-700 text-sm">No Coin Transactions Found</h4>
                                    <p class="text-xs text-slate-400 max-w-sm">No transactions match your current search and filter criteria.</p>
                                    <a href="{{ route('admin.coins.index') }}" class="mt-2 text-xs font-bold text-[#be1e2d] hover:underline">Reset All Filters</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 font-mono print:hidden">
            <div class="flex flex-wrap items-center gap-2.5">
                <span>
                    Showing <span class="font-bold text-slate-700">{{ $transactions->firstItem() ?? 0 }}</span> to <span class="font-bold text-slate-700">{{ $transactions->lastItem() ?? 0 }}</span> of <span class="font-bold text-slate-700">{{ $transactions->total() }}</span> transactions
                </span>
                <span class="text-slate-300">•</span>
                <div class="flex items-center gap-1 text-[11px]">
                    <span class="text-slate-400">Per page:</span>
                    <a href="{{ request()->fullUrlWithQuery(['per_page' => 25]) }}" class="px-2 py-0.5 rounded {{ ($perPage ?? 25) == 25 ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:text-slate-900' }}">25</a>
                    <a href="{{ request()->fullUrlWithQuery(['per_page' => 50]) }}" class="px-2 py-0.5 rounded {{ ($perPage ?? '') == 50 ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:text-slate-900' }}">50</a>
                    <a href="{{ request()->fullUrlWithQuery(['per_page' => 100]) }}" class="px-2 py-0.5 rounded {{ ($perPage ?? '') == 100 ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:text-slate-900' }}">100</a>
                    <a href="{{ request()->fullUrlWithQuery(['per_page' => 'all']) }}" class="px-2 py-0.5 rounded {{ ($perPage ?? '') === 'all' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:text-slate-900 font-bold' }}" title="Show all transactions on one page">All (എല്ലാം)</a>
                </div>
            </div>
            @if($transactions->hasPages())
                <div>
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>

</div>

<style>
@media print {
    body {
        background: white !important;
        color: black !important;
    }
    aside, nav, header, .print\:hidden {
        display: none !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
    }
}
</style>
@endsection
