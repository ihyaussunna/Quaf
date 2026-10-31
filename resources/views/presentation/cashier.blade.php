@extends('layouts.presentation', ['title' => 'Presentation Cashier Desk'])

@section('content')
<div class="space-y-8" x-data="cashierDesk()">

    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span>Presentation Cashier Desk</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                    CASHIER
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-anek">
                കുട്ടികളുടെ കൈയിൽ നിന്നും പണം സ്വീകരിച്ച് അക്കൗണ്ടിലേക്ക് കോയിൻ നൽകുക (Cash to Added Coins).
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('presentation.dashboard') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 hover:border-slate-400 bg-white text-slate-700 text-xs font-bold font-mono uppercase transition-all shadow-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Gift Counter</span>
            </a>
            <a href="{{ route('presentation.cashier') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold font-mono uppercase transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                <span>New Cash Deposit</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Today's Cash Collected -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400">Today Cash Collected</div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">₹ {{ number_format($stats['today_cash_total'], 2) }}</div>
            </div>
        </div>

        <!-- Today's Added Coins Issued -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
            </div>
            <div>
                <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400">Today Added Coins</div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">+{{ number_format($stats['today_coins_added']) }} <span class="text-xs font-mono font-normal text-slate-400">Coins</span></div>
            </div>
        </div>

        <!-- Today Deposits Count -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <div>
                <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400">Today Receipts</div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">{{ number_format($stats['today_deposits_count']) }} <span class="text-xs font-mono font-normal text-slate-400">Slips</span></div>
            </div>
        </div>

        <!-- All-time Cash Total -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400">Total Cash (Festival)</div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">₹ {{ number_format($stats['all_time_cash_total'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- SCANNER & SEARCH SECTION -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    <span>Find Student for Cash Deposit</span>
                </h2>
                <p class="text-xs text-slate-500 font-anek">
                    Fest ID QR കോഡ് സ്കാൻ ചെയ്യുകയോ അല്ലെങ്കിൽ ചെസ്റ്റ് നമ്പർ (e.g. LM101, QF1001) ടൈപ്പ് ചെയ്യുകയോ ചെയ്യുക.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" @click="toggleCameraScanner()" 
                        class="px-4 py-2.5 rounded-xl font-bold font-mono text-xs uppercase flex items-center gap-2 transition-all cursor-pointer shadow-xs"
                        :class="scannerActive ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span x-text="scannerActive ? 'Close Camera Scanner' : 'Open Camera Scanner'"></span>
                </button>
            </div>
        </div>

        <!-- Camera Scanner Live View -->
        <div x-show="scannerActive" x-cloak class="p-4 bg-slate-900 rounded-2xl border-2 border-emerald-400/60 max-w-lg mx-auto space-y-3">
            <div class="flex items-center justify-between text-xs text-emerald-300 font-mono">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Point camera at Student Fest ID QR
                </span>
                <button type="button" @click="toggleCameraScanner()" class="text-slate-400 hover:text-white">&times; Close</button>
            </div>
            <div id="qr-reader" class="rounded-xl overflow-hidden bg-black aspect-square max-h-80 mx-auto"></div>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('presentation.cashier') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" 
                       id="cashierStudentScanInput"
                       name="student" 
                       value="{{ $queryStr }}" 
                       placeholder="Scan QR / Enter Chest No (e.g. LM101, QF1001) / Student Name..." 
                       autofocus
                       class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:bg-white rounded-2xl text-sm font-semibold text-slate-900 placeholder-slate-400 focus:outline-none transition-all shadow-inner">
            </div>

            <button type="submit" class="px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-2xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span>Find Student</span>
            </button>
        </form>
    </div>

    @if($selectedStudent && $studentSummary)
        <!-- SELECTED STUDENT & CASH DEPOSIT CONSOLE -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left: Student Details & Coin Breakdown (5 cols) -->
            <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm space-y-6">
                <!-- Group Banner Header -->
                <div class="p-6 text-white relative overflow-hidden" style="background-color: {{ $selectedStudent->group?->color_hex ?? '#be1e2d' }}">
                    <div class="absolute -right-6 -bottom-6 opacity-15 pointer-events-none text-8xl font-black">
                        {{ $selectedStudent->group?->code ?? 'QUAF' }}
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-black/25 backdrop-blur-xs">
                            {{ $selectedStudent->group?->name ?? 'Group' }}
                        </span>
                        <span class="text-xs font-mono font-bold opacity-80">{{ $selectedStudent->zone_name }}</span>
                    </div>

                    <div class="mt-4 flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-white/20 border-2 border-white/40 flex items-center justify-center font-black text-2xl text-white overflow-hidden shadow-md flex-shrink-0">
                            @if($selectedStudent->photo_path)
                                <img src="{{ asset('storage/' . $selectedStudent->photo_path) }}" alt="{{ $selectedStudent->name }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($selectedStudent->name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-xl sm:text-2xl font-black tracking-tight leading-tight truncate">{{ $selectedStudent->name }}</h2>
                            <div class="text-xs font-mono font-semibold opacity-90 mt-0.5">
                                Chest #: <strong class="underline decoration-white/50">{{ $selectedStudent->chest_number ?? $selectedStudent->student_id }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wallet Breakdown Box -->
                <div class="px-6 space-y-4">
                    <!-- Total Available Balance -->
                    <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/20 relative overflow-hidden border border-emerald-400">
                        <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-emerald-100">Total Wallet Balance</div>
                        <div class="text-4xl sm:text-5xl font-black mt-1 tracking-tight flex items-baseline gap-2">
                            <span>{{ number_format($studentSummary['balance']) }}</span>
                            <span class="text-sm font-bold font-mono tracking-widest uppercase opacity-80">Coins</span>
                        </div>
                    </div>

                    <!-- Breakdown Grid: Competition Earned vs Added Coins -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Competition Earned Coins -->
                        <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200">
                            <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-amber-700">മത്സരത്തിലൂടെ നേടിയത്</div>
                            <div class="text-xl font-black text-amber-900 mt-1">
                                {{ number_format($studentSummary['competition_earned']) }}
                                <span class="text-xs font-mono font-normal">Coins</span>
                            </div>
                            <span class="text-[10px] text-amber-600 font-anek block mt-0.5">Competition Earned</span>
                        </div>

                        <!-- Added Coins (Cash Topup) -->
                        <div class="p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-300">
                            <div class="flex items-center justify-between">
                                <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-emerald-800">പണം നൽകി ചേർത്തത്</div>
                                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-mono font-bold bg-emerald-200 text-emerald-900">ADDED COIN</span>
                            </div>
                            <div class="text-xl font-black text-emerald-900 mt-1">
                                +{{ number_format($studentSummary['added_coins']) }}
                                <span class="text-xs font-mono font-normal">Coins</span>
                            </div>
                            <span class="text-[10px] text-emerald-700 font-anek block mt-0.5">Cashier Added Coins</span>
                        </div>
                    </div>

                    <!-- Total Spent -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs font-mono">
                        <span class="text-slate-500 font-bold">Total Spent on Gifts:</span>
                        <span class="font-black text-rose-600">-{{ number_format($studentSummary['total_spent']) }} Coins</span>
                    </div>
                </div>

                <!-- Previous Deposits by this student -->
                <div class="px-6 pb-6 space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-emerald-900 text-emerald-300 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono">Previous Cash Deposits</span>
                    </div>

                    <div class="space-y-1.5 max-h-52 overflow-y-auto pr-1">
                        @forelse($studentTransactions->where('source', 'cash_deposit') as $ctx)
                            <div class="p-3 rounded-xl bg-slate-50 text-slate-900 flex items-center justify-between gap-3 text-xs border border-slate-200">
                                <div>
                                    <div class="font-bold text-slate-900 text-[11px]">₹{{ number_format($ctx->cash_amount, 2) }} Cash Received</div>
                                    <div class="text-[9px] font-mono text-slate-400">{{ $ctx->created_at?->format('d M, h:i A') }} • {{ $ctx->metadata['operator_name'] ?? 'Cashier' }}</div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="px-2 py-0.5 rounded-md font-mono font-bold text-xs bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        +{{ $ctx->coins }} Coins
                                    </span>
                                    <div class="mt-1">
                                        <a href="{{ route('presentation.cashier.receipt', $ctx->id) }}" target="_blank" class="text-[9px] font-mono text-emerald-700 hover:underline">
                                            Slip &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-3 text-center text-xs text-slate-400 font-mono bg-slate-50 rounded-xl border border-slate-100">
                                No previous cash deposits for this student.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right: Add Coins Form (7 cols) -->
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
                <div>
                    <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Add Coins for Cash in Hand (പണം സ്വീകരിച്ച് കോയിൻ നൽകുക)</span>
                    </h3>
                    <p class="text-xs text-slate-500 font-anek">
                        വിദ്യാർത്ഥിയുടെ കൈയിൽ നിന്നും പണം നേരിട്ട് സ്വീകരിച്ച് അക്കൗണ്ടിലേക്ക് <strong>Added Coins</strong> ആയി ക്രെഡിറ്റ് ചെയ്യുക.
                    </p>
                </div>

                <!-- Quick Cash Presets -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">Quick Cash Amount</label>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                        @foreach([20, 50, 100, 150, 200, 500] as $amount)
                            <button type="button" 
                                    @click="setCashAmount({{ $amount }})"
                                    class="py-3 px-2 rounded-xl border text-center transition-all cursor-pointer font-mono font-black"
                                    :class="cashAmount == {{ $amount }} ? 'border-emerald-600 bg-emerald-50 text-emerald-900 shadow-xs' : 'border-slate-200 hover:border-emerald-400 text-slate-700 bg-white'">
                                <span class="block text-xs text-slate-400 font-normal">₹</span>
                                <span class="text-sm font-black">{{ $amount }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Add Coins Form -->
                <form method="POST" action="{{ route('presentation.cashier.add-coins') }}" class="space-y-5" @submit="confirmDeposit($event)">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Cash Amount in Rupees -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                                Cash Received in ₹ (ലഭിച്ച പണം) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-base">₹</span>
                                <input type="number" 
                                       name="cash_amount" 
                                       x-model.number="cashAmount" 
                                       @input="onCashInput()"
                                       min="1" 
                                       max="20000" 
                                       step="1"
                                       required 
                                       class="w-full pl-9 pr-4 py-3.5 bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:bg-white rounded-xl text-base font-bold font-mono text-slate-900 focus:outline-none transition-all">
                            </div>
                        </div>

                        <!-- Coins to Add -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                                Coins to Add (നൽകുന്ന കോയിൻ) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" 
                                   name="coins" 
                                   x-model.number="coinsToAdd" 
                                   min="1" 
                                   max="20000" 
                                   required 
                                   class="w-full px-4 py-3.5 bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:bg-white rounded-xl text-base font-bold font-mono text-emerald-800 focus:outline-none transition-all">
                            <span class="text-[10px] text-slate-400 font-mono">Default: 1 ₹ = 1 Coin</span>
                        </div>

                        <!-- Payment Mode -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                                Payment Method
                            </label>
                            <select name="payment_mode" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold font-mono text-slate-800 focus:bg-white focus:outline-none">
                                <option value="cash">Cash in Hand (കൈയിൽ പണം)</option>
                                <option value="upi">UPI / GPay / Scanner</option>
                            </select>
                        </div>

                        <!-- Remarks / Notes -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                                Cashier Notes (ഓപ്ഷണൽ)
                            </label>
                            <input type="text" 
                                   name="notes" 
                                   placeholder="e.g. Paid by parent / student"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:bg-white focus:outline-none">
                        </div>
                    </div>

                    <!-- Live Calculation Preview -->
                    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 space-y-2">
                        <div class="flex items-center justify-between text-xs font-mono">
                            <span class="text-slate-600">Current Balance:</span>
                            <span class="font-bold text-slate-900">{{ $studentSummary['balance'] }} Coins</span>
                        </div>
                        <div class="flex items-center justify-between text-xs font-mono">
                            <span class="text-emerald-700 font-bold">Added Coins Now:</span>
                            <span class="font-black text-emerald-700" x-text="'+' + coinsToAdd + ' Coins'"></span>
                        </div>
                        <div class="pt-2 border-t border-emerald-200 flex items-center justify-between text-sm font-mono font-bold">
                            <span class="text-slate-800">New Total Balance:</span>
                            <span class="text-emerald-800 font-black text-base" x-text="( {{ $studentSummary['balance'] }} + coinsToAdd ) + ' Coins'"></span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            :disabled="coinsToAdd <= 0 || cashAmount <= 0"
                            class="w-full py-4 rounded-2xl font-bold font-sora text-sm uppercase tracking-wider text-white bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span>Confirm Cash & Add Coins (പണം സ്വീകരിച്ച് കോയിൻ നൽകുക)</span>
                    </button>
                </form>
            </div>
        </div>
    @elseif($queryStr !== '')
        <!-- Student Not Found Notice -->
        <div class="p-8 text-center bg-white rounded-3xl border border-slate-200 text-slate-500 space-y-3">
            <svg class="w-12 h-12 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div class="text-base font-bold text-slate-800 font-sora">വിദ്യാർത്ഥിയെ കണ്ടെത്താനായില്ല (Student Not Found)</div>
            <p class="text-xs font-mono text-slate-400 max-w-md mx-auto">
                "{{ $queryStr }}" എന്ന ചെസ്റ്റ് നമ്പറിലോ QR ടോക്കണിലോ ഉള്ള വിദ്യാർത്ഥി ലിസ്റ്റിൽ ലഭ്യമല്ല. ശരിയായ Fest ID കാർഡ് സ്കാൻ ചെയ്യുക.
            </p>
        </div>
    @endif

    <!-- ALL RECENT CASH DEPOSITS TABLE -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <span>Recent Cash Deposits (പണം സ്വീകരിച്ച് ചേർത്ത കോയിനുകൾ)</span>
                </h3>
                <p class="text-xs text-slate-500 font-anek">ക്യാഷ്യർ കൗണ്ടർ വഴി പണം നൽകി ചേർത്ത കോയിനുകളുടെ ഹിസ്റ്ററിയും രസീതും.</p>
            </div>
        </div>

        <div class="overflow-x-auto -mx-6 sm:mx-0">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 font-mono uppercase text-[11px] bg-slate-50/50">
                        <th class="py-3 px-4">Receipt Ref</th>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Student</th>
                        <th class="py-3 px-4">Group</th>
                        <th class="py-3 px-4 text-center">Cash Received</th>
                        <th class="py-3 px-4 text-center">Coins Added</th>
                        <th class="py-3 px-4 text-center">Operator</th>
                        <th class="py-3 px-4 text-right">Receipt Slip</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentDeposits as $tx)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800 text-[11px]">
                                {{ $tx->transaction_reference }}
                            </td>
                            <td class="py-3 px-4 text-slate-500 font-mono text-[11px]">
                                {{ $tx->created_at?->format('d M Y, h:i A') }}
                            </td>
                            <td class="py-3 px-4">
                                <a href="{{ route('presentation.cashier', ['student' => $tx->student?->student_id]) }}" class="font-bold text-slate-900 hover:text-emerald-700 transition-colors">
                                    {{ $tx->student?->name ?? 'Student' }}
                                </a>
                                <div class="text-[10px] font-mono text-slate-400">#{{ $tx->student?->student_id }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold" style="background-color: {{ $tx->student?->group?->color_hex ?? '#be1e2d' }}20; color: {{ $tx->student?->group?->color_hex ?? '#be1e2d' }}">
                                    {{ $tx->student?->group?->name ?? '---' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-900">
                                ₹ {{ number_format($tx->cash_amount, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full font-mono font-bold text-xs bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    +{{ $tx->coins }} Coins
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center text-slate-500 text-[11px] font-mono">
                                {{ $tx->metadata['operator_name'] ?? 'Cashier' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('presentation.cashier.receipt', $tx->id) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-[10px] uppercase inline-flex items-center gap-1 transition-all">
                                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    <span>Print Slip</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 font-mono text-xs">
                                ഇന്നത്തെ പണം സ്വീകരിച്ച് ചേർത്ത കോയിനുകൾ ഒന്നും രേഖപ്പെടുത്തിയിട്ടില്ല (No cash deposits recorded yet).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recentDeposits->hasPages())
            <div class="pt-4 border-t border-slate-100">
                {{ $recentDeposits->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
function cashierDesk() {
    return {
        scannerActive: false,
        html5QrCode: null,
        cashAmount: 100,
        coinsToAdd: 100,

        setCashAmount(amt) {
            this.cashAmount = amt;
            this.coinsToAdd = amt;
        },

        onCashInput() {
            this.coinsToAdd = this.cashAmount > 0 ? this.cashAmount : 0;
        },

        playBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';
                osc.frequency.value = 880;
                gain.gain.setValueAtTime(0.25, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.15);
            } catch (e) {}
        },

        confirmDeposit(event) {
            if (!confirm('₹' + this.cashAmount + ' പണം സ്വീകരിച്ച് ' + this.coinsToAdd + ' കോയിൻ ചേർക്കട്ടെ?')) {
                event.preventDefault();
            }
        },

        toggleCameraScanner() {
            if (this.scannerActive) {
                this.stopCameraScanner();
            } else {
                this.startCameraScanner();
            }
        },

        startCameraScanner() {
            this.scannerActive = true;
            this.$nextTick(() => {
                if (typeof Html5Qrcode === 'undefined') {
                    alert('Camera scanner library is loading. Please try again in a moment.');
                    this.scannerActive = false;
                    return;
                }

                if (!this.html5QrCode) {
                    this.html5QrCode = new Html5Qrcode('qr-reader');
                }

                const config = { fps: 10, qrbox: { width: 250, height: 250 } };
                this.html5QrCode.start(
                    { facingMode: 'environment' },
                    config,
                    (decodedText) => {
                        this.playBeep();
                        this.stopCameraScanner();
                        const input = document.getElementById('cashierStudentScanInput');
                        if (input) {
                            input.value = decodedText;
                            input.form.submit();
                        } else {
                            window.location.href = "{{ route('presentation.cashier') }}?student=" + encodeURIComponent(decodedText);
                        }
                    },
                    (errorMessage) => {}
                ).catch(err => {
                    alert('ക്യാമറ തുറക്കാൻ സാധിച്ചില്ല: ' + err);
                    this.scannerActive = false;
                });
            });
        },

        stopCameraScanner() {
            if (this.html5QrCode && this.html5QrCode.isScanning) {
                this.html5QrCode.stop().then(() => {
                    this.scannerActive = false;
                }).catch(err => {
                    this.scannerActive = false;
                });
            } else {
                this.scannerActive = false;
            }
        }
    }
}
</script>
@endpush
