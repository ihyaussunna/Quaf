@extends('layouts.presentation', ['title' => 'Presentation Counter'])

@section('content')
<div class="space-y-8" x-data="presentationDesk()">

    <!-- Header & Metric Cards -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span>Presentation Desk</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-amber-100 text-amber-800 border border-amber-300">
                    QUAF 09
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-anek">
                വിദ്യാർത്ഥികളുടെ Fest ID / Chest Card സ്കാൻ ചെയ്ത് കോയിൻ ബാലൻസ് പരിശോധിക്കുകയും പുസ്തകങ്ങളും സമ്മാനങ്ങളും നൽകുകയും ചെയ്യുക.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('presentation.report') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 hover:border-slate-400 bg-white text-slate-700 text-xs font-bold font-mono uppercase transition-all shadow-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Report</span>
            </a>
            <a href="{{ route('presentation.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold font-mono uppercase transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                <span>New Scan</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400">Total Redeemed</div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">{{ number_format($stats['total_redeemed']) }} <span class="text-xs font-mono font-normal text-slate-400">Coins</span></div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <div>
                <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400">Items Purchased</div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">{{ number_format($stats['total_purchases']) }} <span class="text-xs font-mono font-normal text-slate-400">Tx</span></div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-teal-500/10 text-teal-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
            </div>
            <div>
                <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400">Recognition Gifts</div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">{{ number_format($stats['gifts_delivered'] ?? 0) }} <span class="text-xs font-mono font-normal text-slate-400">/ {{ number_format($stats['total_eligible_students'] ?? $stats['total_students']) }}</span></div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400">Students Served</div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">{{ number_format($stats['unique_students']) }}</div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4 col-span-2 lg:col-span-1">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <div>
                <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400">Total Fest Coins</div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">{{ number_format($stats['total_credited']) }}</div>
            </div>
        </div>
    </div>

    <!-- SCANNER & SEARCH SECTION -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    <span>Scan Fest ID Card / Chest Number</span>
                </h2>
                <p class="text-xs text-slate-500 font-anek">
                    ക്യാമറ വഴി QR കോഡ് സ്കാൻ ചെയ്യുകയോ, ബാർകോഡ് സ്കാനർ ഉപയോഗിക്കുകയോ, ചെസ്റ്റ് നമ്പർ ടൈപ്പ് ചെയ്യുകയോ ചെയ്യാം.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" @click="toggleCameraScanner()" 
                        class="px-4 py-2.5 rounded-xl font-bold font-mono text-xs uppercase flex items-center gap-2 transition-all cursor-pointer shadow-xs"
                        :class="scannerActive ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-amber-500 hover:bg-amber-600 text-white'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span x-text="scannerActive ? 'Close Camera Scanner' : 'Open Camera Scanner'"></span>
                </button>
            </div>
        </div>

        <!-- Camera Scanner Live View (Hidden by default) -->
        <div x-show="scannerActive" x-cloak class="p-4 bg-slate-900 rounded-2xl border-2 border-amber-400/60 max-w-lg mx-auto space-y-3">
            <div class="flex items-center justify-between text-xs text-amber-300 font-mono">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Point camera at Student Fest ID QR
                </span>
                <button type="button" @click="toggleCameraScanner()" class="text-slate-400 hover:text-white">&times; Close</button>
            </div>
            <div id="qr-reader" class="rounded-xl overflow-hidden bg-black aspect-square max-h-80 mx-auto"></div>
            <div id="qr-reader-results" class="text-center text-xs text-slate-300 font-mono"></div>
        </div>

        <!-- Direct Search / USB Scanner Input Form -->
        <form method="GET" action="{{ route('presentation.dashboard') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" 
                       id="studentScanInput"
                       name="student" 
                       value="{{ $queryStr }}" 
                       placeholder="Scan QR / Enter Chest No (e.g. LM101, QF1001) / Student Name..." 
                       autofocus
                       class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border-2 border-slate-200 focus:border-amber-500 focus:bg-white rounded-2xl text-sm font-semibold text-slate-900 placeholder-slate-400 focus:outline-none transition-all shadow-inner">
            </div>

            <button type="submit" class="px-6 py-3.5 bg-[#be1e2d] hover:bg-[#a01824] text-white font-bold text-xs uppercase tracking-wider rounded-2xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span>Find Student</span>
            </button>
        </form>
    </div>

    @if($selectedStudent && $studentSummary)
        <!-- SELECTED STUDENT DETAILS & PURCHASE CONSOLE -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left: Student Profile Card & Coin Balance (5 cols) -->
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

                <!-- Big Golden Coin Balance Display -->
                <div class="px-6 space-y-4">
                    <div class="p-5 rounded-2xl bg-gradient-to-br from-amber-500 via-amber-400 to-yellow-500 text-slate-950 shadow-lg shadow-amber-500/20 relative overflow-hidden border border-amber-300">
                        <div class="absolute -right-4 -bottom-4 opacity-20 pointer-events-none text-7xl">
                            <svg class="w-28 h-28" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.03 6.9C12.68 8.17 11.94 7.66 11.07 7.66H8.28V6.42h7.44v1.24h-2.02c.3.36.52.78.65 1.24H15.72v1.24h-1.25c-.16 1.74-1.62 3.1-3.4 3.1h-.45l4.17 4.34h-1.72L8.9 13.24V12h2.17c1.09 0 2-.81 2.15-1.86H8.28V8.9h4.75z"/></svg>
                        </div>

                        <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-800">Available Wallet Balance</div>
                        <div class="text-4xl sm:text-5xl font-black mt-1 tracking-tight flex items-baseline gap-2">
                            <span>{{ number_format($studentSummary['balance']) }}</span>
                            <span class="text-sm font-bold font-mono tracking-widest uppercase opacity-80">Coins</span>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-950/15 flex items-center justify-between text-xs font-mono font-bold">
                            <div>
                                <span class="opacity-70">Total Credited:</span>
                                <span class="text-emerald-950 font-black">+{{ $studentSummary['total_earned'] }}</span>
                            </div>
                            <div>
                                <span class="opacity-70">Total Spent:</span>
                                <span class="text-rose-950 font-black">-{{ $studentSummary['total_spent'] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Grade Achievements Row (A+ Excellence, A Proficient, B Adequate, C Satisfactory) -->
                    @if(isset($studentAchievements))
                        <div class="space-y-1.5">
                            <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">Grade Achievements</div>
                            <div class="grid grid-cols-4 gap-2">
                                <!-- A+ Excellence -->
                                <div class="rounded-xl p-2.5 bg-white border-2 border-amber-300 text-center shadow-2xs">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span class="text-base sm:text-lg font-black font-sora text-amber-600">A+</span>
                                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-100 text-amber-900 border border-amber-200">
                                            {{ $studentAchievements['grade_counts']['A+'] ?? 0 }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-slate-700 font-bold block mt-1 truncate">Excellence</span>
                                </div>

                                <!-- A Proficient -->
                                <div class="rounded-xl p-2.5 bg-white border-2 border-emerald-300 text-center shadow-2xs">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span class="text-base sm:text-lg font-black font-sora text-emerald-600">A</span>
                                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-100 text-emerald-900 border border-emerald-200">
                                            {{ $studentAchievements['grade_counts']['A'] ?? 0 }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-slate-700 font-bold block mt-1 truncate">Proficient</span>
                                </div>

                                <!-- B Adequate -->
                                <div class="rounded-xl p-2.5 bg-white border-2 border-blue-300 text-center shadow-2xs">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span class="text-base sm:text-lg font-black font-sora text-blue-600">B</span>
                                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-blue-100 text-blue-900 border border-blue-200">
                                            {{ $studentAchievements['grade_counts']['B'] ?? 0 }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-slate-700 font-bold block mt-1 truncate">Adequate</span>
                                </div>

                                <!-- C Satisfactory -->
                                <div class="rounded-xl p-2.5 bg-white border-2 border-slate-300 text-center shadow-2xs">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span class="text-base sm:text-lg font-black font-sora text-slate-700">C</span>
                                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                            {{ $studentAchievements['grade_counts']['C'] ?? 0 }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-slate-700 font-bold block mt-1 truncate">Satisfactory</span>
                                </div>
                            </div>
                        </div>

                        <!-- Competition Prize Cards (First Prize Pinnacle, Second Prize Eminent, Third Prize Competent) -->
                        <div class="space-y-2">
                            <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">Competition Prizes</div>

                            <!-- First Prize -->
                            @php $p1 = $studentAchievements['prizes'][1] ?? ['count' => 0, 'programs' => []]; @endphp
                            <div class="rounded-2xl p-3.5 border transition-all {{ $p1['count'] > 0 ? 'bg-[#0f1d36] text-white border-amber-400/60 shadow-md' : 'bg-slate-50 text-slate-700 border-slate-200' }}" x-data="{ open: false }">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold {{ $p1['count'] > 0 ? 'bg-amber-400 text-slate-950' : 'bg-slate-200 text-slate-600' }}">
                                        <svg class="w-3 h-3 text-current shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <span>{{ $p1['count'] }} Pinnacle</span>
                                    </span>
                                    @if(!empty($p1['programs']))
                                        <button type="button" @click="open = !open" class="text-[10px] font-mono font-bold text-amber-300 hover:text-amber-200 underline cursor-pointer">
                                            <span x-text="open ? 'Hide Details' : 'View Details'"></span>
                                        </button>
                                    @endif
                                </div>
                                <h4 class="text-sm font-black font-sora mt-1.5 {{ $p1['count'] > 0 ? 'text-white' : 'text-slate-800' }}">First Prize</h4>
                                <p class="text-[10px] mt-0.5 {{ $p1['count'] > 0 ? 'text-slate-300' : 'text-slate-500' }}">You've secured the First Prize with excellence!</p>

                                @if(!empty($p1['programs']))
                                    <div x-show="open" x-cloak class="mt-2.5 pt-2.5 border-t border-white/10 space-y-1">
                                        @foreach($p1['programs'] as $prog)
                                            <div class="flex items-center justify-between text-[11px] bg-white/5 rounded-lg px-2.5 py-1.5">
                                                <span class="font-medium truncate">{{ $prog['name'] }}</span>
                                                <span class="text-[10px] font-mono font-bold text-amber-300 shrink-0 ml-2">{{ $prog['grade'] ? 'Grade '.$prog['grade'] : '' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Second Prize -->
                            @php $p2 = $studentAchievements['prizes'][2] ?? ['count' => 0, 'programs' => []]; @endphp
                            <div class="rounded-2xl p-3.5 border transition-all {{ $p2['count'] > 0 ? 'bg-[#0f1d36] text-white border-blue-400/60 shadow-md' : 'bg-slate-50 text-slate-700 border-slate-200' }}" x-data="{ open: false }">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold {{ $p2['count'] > 0 ? 'bg-blue-400 text-slate-950' : 'bg-slate-200 text-slate-600' }}">
                                        <svg class="w-3 h-3 text-current shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <span>{{ $p2['count'] }} Eminent</span>
                                    </span>
                                    @if(!empty($p2['programs']))
                                        <button type="button" @click="open = !open" class="text-[10px] font-mono font-bold text-blue-300 hover:text-blue-200 underline cursor-pointer">
                                            <span x-text="open ? 'Hide Details' : 'View Details'"></span>
                                        </button>
                                    @endif
                                </div>
                                <h4 class="text-sm font-black font-sora mt-1.5 {{ $p2['count'] > 0 ? 'text-white' : 'text-slate-800' }}">Second Prize</h4>
                                <p class="text-[10px] mt-0.5 {{ $p2['count'] > 0 ? 'text-slate-300' : 'text-slate-500' }}">Great achievement securing Second Prize!</p>

                                @if(!empty($p2['programs']))
                                    <div x-show="open" x-cloak class="mt-2.5 pt-2.5 border-t border-white/10 space-y-1">
                                        @foreach($p2['programs'] as $prog)
                                            <div class="flex items-center justify-between text-[11px] bg-white/5 rounded-lg px-2.5 py-1.5">
                                                <span class="font-medium truncate">{{ $prog['name'] }}</span>
                                                <span class="text-[10px] font-mono font-bold text-blue-300 shrink-0 ml-2">{{ $prog['grade'] ? 'Grade '.$prog['grade'] : '' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Third Prize -->
                            @php $p3 = $studentAchievements['prizes'][3] ?? ['count' => 0, 'programs' => []]; @endphp
                            <div class="rounded-2xl p-3.5 border transition-all {{ $p3['count'] > 0 ? 'bg-[#0f1d36] text-white border-amber-600/60 shadow-md' : 'bg-slate-50 text-slate-700 border-slate-200' }}" x-data="{ open: false }">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold {{ $p3['count'] > 0 ? 'bg-amber-600 text-white' : 'bg-slate-200 text-slate-600' }}">
                                        <svg class="w-3 h-3 text-current shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <span>{{ $p3['count'] }} Competent</span>
                                    </span>
                                    @if(!empty($p3['programs']))
                                        <button type="button" @click="open = !open" class="text-[10px] font-mono font-bold text-amber-300 hover:text-amber-200 underline cursor-pointer">
                                            <span x-text="open ? 'Hide Details' : 'View Details'"></span>
                                        </button>
                                    @endif
                                </div>
                                <h4 class="text-sm font-black font-sora mt-1.5 {{ $p3['count'] > 0 ? 'text-white' : 'text-slate-800' }}">Third Prize</h4>
                                <p class="text-[10px] mt-0.5 {{ $p3['count'] > 0 ? 'text-slate-300' : 'text-slate-500' }}">Proud moment! You earned the Third Prize.</p>

                                @if(!empty($p3['programs']))
                                    <div x-show="open" x-cloak class="mt-2.5 pt-2.5 border-t border-white/10 space-y-1">
                                        @foreach($p3['programs'] as $prog)
                                            <div class="flex items-center justify-between text-[11px] bg-white/5 rounded-lg px-2.5 py-1.5">
                                                <span class="font-medium truncate">{{ $prog['name'] }}</span>
                                                <span class="text-[10px] font-mono font-bold text-amber-300 shrink-0 ml-2">{{ $prog['grade'] ? 'Grade '.$prog['grade'] : '' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- QUAF Recognition Gift Status & Delivery Action -->
                    @if(!$selectedStudent->isCompetitionParticipant())
                        <div class="p-4 sm:p-5 rounded-2xl border bg-slate-100 border-slate-200 text-slate-700">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-500">QUAF Recognition Gift</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-200 text-slate-700">
                                    Not Eligible
                                </span>
                            </div>
                            <div class="mt-2">
                                <div class="text-sm font-bold font-sora text-slate-800">അർഹതയില്ല (Not Eligible)</div>
                                <div class="text-[11px] text-slate-500 font-anek mt-0.5">ഈ വിദ്യാർത്ഥി മത്സരങ്ങളിൽ (Individual / Group) പങ്കെടുത്തിട്ടില്ല. ക്വാഫ് റെക്കഗ്നിഷൻ ഗിഫ്റ്റ് മത്സരങ്ങളിൽ പങ്കെടുത്തവർക്ക് മാത്രമുള്ളതാണ്.</div>
                            </div>
                        </div>
                    @else
                        <div class="p-4 sm:p-5 rounded-2xl border transition-all {{ $selectedStudent->recognition_gift_delivered ? 'bg-emerald-50 border-emerald-300 text-emerald-950' : 'bg-amber-50 border-amber-300 text-amber-950' }}">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] font-mono font-bold uppercase tracking-wider">QUAF Recognition Gift</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold {{ $selectedStudent->recognition_gift_delivered ? 'bg-emerald-200 text-emerald-900' : 'bg-amber-200 text-amber-900' }}">
                                    {{ $selectedStudent->recognition_gift_delivered ? 'Delivered' : 'Pending' }}
                                </span>
                            </div>

                            <div class="mt-2">
                                @if($selectedStudent->recognition_gift_delivered)
                                    <div class="text-sm font-bold font-sora text-emerald-900">സമ്മാനം നൽകി (Gift Delivered)</div>
                                    <div class="text-[10px] font-mono text-emerald-700 mt-0.5">
                                        {{ $selectedStudent->recognition_gift_delivered_at?->format('d M, h:i A') }} • {{ $selectedStudent->recognition_gift_delivered_by }}
                                    </div>
                                @else
                                    <div class="text-sm font-bold font-sora text-amber-900">സമ്മാനം നൽകിയിട്ടില്ല (Gift Pending)</div>
                                    <div class="text-[10px] text-amber-800 font-anek mt-0.5">ഈ വിദ്യാർത്ഥിക്ക് ക്വാഫ് റെക്കഗ്നിഷൻ ഗിഫ്റ്റ് നൽകുക.</div>
                                @endif
                            </div>

                            <div class="mt-3 pt-3 border-t {{ $selectedStudent->recognition_gift_delivered ? 'border-emerald-200' : 'border-amber-200' }}">
                                <form method="POST" action="{{ route('presentation.toggle-gift', $selectedStudent->id) }}">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full py-2.5 rounded-xl font-mono font-bold text-xs uppercase tracking-wider transition-all cursor-pointer shadow-xs {{ $selectedStudent->recognition_gift_delivered ? 'bg-slate-200 hover:bg-slate-300 text-slate-800' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-600/20' }}">
                                        {{ $selectedStudent->recognition_gift_delivered ? 'Cancel / Revert Status' : 'Mark Gift Delivered (സമ്മാനം നൽകി)' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Student Transaction History -->
                <div class="px-6 pb-6 space-y-3" x-data="{ showAll: false }">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-slate-900 text-amber-400 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono">Transaction History</span>
                        </div>
                        @if($studentTransactions->count() > 5)
                            <button type="button" @click="showAll = !showAll" class="text-[10px] font-mono font-bold text-amber-700 hover:text-amber-900 cursor-pointer">
                                <span x-text="showAll ? 'Show Recent' : 'View All &rarr;'"></span>
                            </button>
                        @endif
                    </div>
                    <div class="space-y-1.5 max-h-64 overflow-y-auto pr-1">
                        @forelse($studentTransactions as $index => $stx)
                            <div x-show="showAll || {{ $index }} < 5" class="p-3 rounded-xl bg-slate-50 text-slate-900 flex items-center justify-between gap-3 text-xs border border-slate-200/80">
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 truncate text-[11px]">{{ $stx->description }}</div>
                                    <div class="text-[9px] font-mono text-slate-400">{{ $stx->created_at?->format('d M, h:i A') }}</div>
                                </div>
                                <div class="font-mono font-black text-xs flex-shrink-0 {{ $stx->coins > 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $stx->coins > 0 ? '+' : '-' }} {{ abs($stx->coins) }} Coins
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-xs text-slate-400 font-mono bg-slate-50 rounded-xl border border-slate-100">
                                No previous transactions recorded for this student.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right: Redemption / Purchase Action Form (7 cols) -->
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
                <div>
                    <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        <span>Record Purchase / Gift Redemption</span>
                    </h3>
                    <p class="text-xs text-slate-500 font-anek">
                        വിദ്യാർത്ഥിക്ക് നൽകുന്ന പുസ്തകമോ സമ്മാനമോ തിരഞ്ഞെടുക്കുക. കോയിനുകൾ സ്വമേധയാ അക്കൗണ്ടിൽ നിന്ന് കുറയുന്നതാണ്.
                    </p>
                </div>

                <!-- Fast Preset Catalog Buttons -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">Quick Preset Items</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach($presets as $p)
                            <button type="button" 
                                    @click="selectPreset('{{ addslashes($p['name']) }}', {{ $p['coins'] }}, '{{ $p['category'] }}')"
                                    class="p-2.5 rounded-xl border border-slate-200 hover:border-amber-400 hover:bg-amber-50/50 text-left transition-all cursor-pointer group shadow-2xs">
                                <span class="block text-[10px] font-mono font-bold text-amber-700 uppercase">{{ $p['badge'] }}</span>
                                <span class="block text-xs font-bold text-slate-800 group-hover:text-amber-950 truncate">{{ $p['name'] }}</span>
                                <span class="block text-xs font-mono font-black text-[#be1e2d] mt-1">{{ $p['coins'] }} Coins</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Purchase Form -->
                <form method="POST" action="{{ route('presentation.redeem') }}" class="space-y-5" @submit="confirmSubmit($event)">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Item Name -->
                        <div class="sm:col-span-2 space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                                Item / Book Name (പുസ്തകം / ഇനം) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="item_name" 
                                   x-model="itemName" 
                                   required 
                                   placeholder="e.g. Fiqhussunna / Islamic History Book / Trophy"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:bg-white focus:border-amber-500 focus:outline-none transition-all">
                        </div>

                        <!-- Coins per unit -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                                Coins Required (കോയിൻ) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" 
                                   name="coins" 
                                   x-model.number="unitCoins" 
                                   min="1" 
                                   max="5000" 
                                   required 
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold font-mono text-slate-900 focus:bg-white focus:border-amber-500 focus:outline-none transition-all">
                        </div>

                        <!-- Quantity -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                                Quantity (എണ്ണം)
                            </label>
                            <div class="flex items-center">
                                <button type="button" @click="if (quantity > 1) quantity--" class="px-3 py-3 bg-slate-100 hover:bg-slate-200 rounded-l-xl text-slate-700 font-bold border border-slate-200 border-r-0 cursor-pointer">-</button>
                                <input type="number" 
                                       name="quantity" 
                                       x-model.number="quantity" 
                                       min="1" 
                                       max="100" 
                                       class="w-full px-3 py-3 text-center bg-slate-50 border border-slate-200 text-sm font-bold font-mono text-slate-900 focus:outline-none">
                                <button type="button" @click="quantity++" class="px-3 py-3 bg-slate-100 hover:bg-slate-200 rounded-r-xl text-slate-700 font-bold border border-slate-200 border-l-0 cursor-pointer">+</button>
                            </div>
                        </div>

                        <!-- Item Category -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                                Category
                            </label>
                            <select name="item_category" x-model="itemCategory" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:bg-white focus:border-amber-500 focus:outline-none">
                                <option value="book">Book (പുസ്തകം)</option>
                                <option value="souvenir">Fest Souvenir / Memento</option>
                                <option value="stationery">Stationery (ഡയറി / പേന)</option>
                                <option value="kit">Kit / Badge</option>
                                <option value="other">Other / ഇതര ഇനങ്ങൾ</option>
                            </select>
                        </div>

                        <!-- Notes / Reference -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                                Notes / Bill No (ഓപ്ഷണൽ)
                            </label>
                            <input type="text" 
                                   name="notes" 
                                   x-model="notes" 
                                   placeholder="Bill or voucher number..."
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:bg-white focus:border-amber-500 focus:outline-none transition-all">
                        </div>
                    </div>

                    <!-- Dynamic Balance Calculation Breakdown -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between text-xs font-mono">
                            <span class="text-slate-500">Student Current Balance:</span>
                            <span class="font-bold text-slate-900">{{ $studentSummary['balance'] }} Coins</span>
                        </div>
                        <div class="flex items-center justify-between text-xs font-mono">
                            <span class="text-slate-500">Total Purchase Deduction:</span>
                            <span class="font-bold text-rose-600" x-text="'-' + (unitCoins * quantity) + ' Coins'"></span>
                        </div>
                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-sm font-mono font-bold">
                            <span class="text-slate-800">Remaining Balance After Purchase:</span>
                            <span :class="( {{ $studentSummary['balance'] }} - (unitCoins * quantity) ) >= 0 ? 'text-emerald-700 font-black' : 'text-rose-600 font-black'"
                                  x-text="( {{ $studentSummary['balance'] }} - (unitCoins * quantity) ) + ' Coins'"></span>
                        </div>
                    </div>

                    <!-- Insufficient Balance Warning -->
                    <div x-show="(unitCoins * quantity) > {{ $studentSummary['balance'] }}" x-cloak
                         class="p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-800 text-xs font-bold flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>ശ്രദ്ധിക്കുക: വിദ്യാർത്ഥിയുടെ അക്കൗണ്ടിൽ ആവശ്യത്തിന് കോയിനുകൾ ലഭ്യമല്ല (Insufficient Coins). ഈ പർച്ചേസ് സേവ് ചെയ്യാൻ സാധ്യമല്ല.</span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            :disabled="(unitCoins * quantity) > {{ $studentSummary['balance'] }} || (unitCoins * quantity) <= 0 || !itemName"
                            class="w-full py-4 rounded-2xl font-bold font-sora text-sm uppercase tracking-wider text-white shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="(unitCoins * quantity) > {{ $studentSummary['balance'] }} ? 'bg-slate-400' : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/30'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Confirm Purchase & Deduct Coins (സേവ് ചെയ്യുക & കോയിൻ കുറയ്ക്കുക)</span>
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

    <!-- ALL RECENT PURCHASES / TRANSACTIONS TABLE -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6" id="history">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <span>Recent Presentation Redemptions & Purchases</span>
                </h3>
                <p class="text-xs text-slate-500 font-anek">പ്രസന്റേഷൻ കൗണ്ടറിൽ നിന്നും നൽകിയ സമ്മാനങ്ങളുടെയും കുറച്ച കോയിനുകളുടെയും വിശദവിവരങ്ങൾ.</p>
            </div>

            <!-- Filter Search -->
            <form method="GET" action="{{ route('presentation.dashboard') }}" class="flex items-center gap-2">
                <input type="text" 
                       name="search_history" 
                       value="{{ $searchFilter }}" 
                       placeholder="Search by student, item, ref..." 
                       class="px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none font-semibold text-slate-700">
                <button type="submit" class="px-3 py-2 bg-slate-900 text-white rounded-xl text-xs font-mono font-bold hover:bg-slate-800 cursor-pointer">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto -mx-6 sm:mx-0">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 font-mono uppercase text-[11px] bg-slate-50/50">
                        <th class="py-3 px-4">Receipt Ref</th>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Student</th>
                        <th class="py-3 px-4">Group</th>
                        <th class="py-3 px-4">Item & Details</th>
                        <th class="py-3 px-4 text-center">Coins Deducted</th>
                        <th class="py-3 px-4 text-center">Receipt</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentTransactions as $tx)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800 text-[11px]">
                                {{ $tx->transaction_reference }}
                            </td>
                            <td class="py-3 px-4 text-slate-500 font-mono text-[11px]">
                                {{ $tx->created_at?->format('d M Y, h:i A') }}
                            </td>
                            <td class="py-3 px-4">
                                <a href="{{ route('presentation.dashboard', ['student' => $tx->student?->student_id]) }}" class="font-bold text-slate-900 hover:text-[#be1e2d] transition-colors">
                                    {{ $tx->student?->name ?? 'Student' }}
                                </a>
                                <div class="text-[10px] font-mono text-slate-400">#{{ $tx->student?->student_id }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold" style="background-color: {{ $tx->student?->group?->color_hex ?? '#be1e2d' }}20; color: {{ $tx->student?->group?->color_hex ?? '#be1e2d' }}">
                                    {{ $tx->student?->group?->name ?? '---' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-800">
                                <div>{{ $tx->description }}</div>
                                @if(!empty($tx->metadata['notes']))
                                    <div class="text-[10px] text-slate-400 italic">Notes: {{ $tx->metadata['notes'] }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-mono font-black text-xs {{ $tx->coins < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $tx->coins }} Coins
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('presentation.receipt', $tx->id) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-bold text-[10px] uppercase inline-flex items-center gap-1 transition-all">
                                    <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    <span>Slip</span>
                                </a>
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if($tx->transaction_type === 'debit')
                                    <form method="POST" action="{{ route('presentation.reverse', $tx->id) }}" onsubmit="return confirm('ഈ പർച്ചേസ് ക്യാൻസൽ ചെയ്യാനും വിദ്യാർത്ഥിക്ക് കോയിനുകൾ തിരികെ നൽകാനും നിങ്ങൾ ഉറപ്പാണോ?')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-mono font-bold text-[10px] uppercase transition-all cursor-pointer">
                                            Cancel
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[10px] font-mono text-slate-400 italic">Reversed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 font-mono text-xs">
                                No purchases recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-200">
            {{ $recentTransactions->links() }}
        </div>
    </div>

    <!-- LAST TRANSACTION RECEIPT POPUP MODAL (If present in session) -->
    @if($lastTransaction)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs" x-data="{ modalOpen: true }" x-show="modalOpen">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-slate-200 text-center relative">
                <button type="button" @click="modalOpen = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
                
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>

                <div class="space-y-1">
                    <h3 class="text-xl font-black text-slate-900">Purchase Completed!</h3>
                    <p class="text-xs text-slate-500 font-anek">കോയിൻ കുറയ്ക്കുകയും സമ്മാനം രേഖപ്പെടുത്തുകയും ചെയ്തു.</p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-left text-xs space-y-2">
                    <div class="flex justify-between"><span class="text-slate-400 font-mono">Receipt:</span> <span class="font-mono font-bold text-slate-900">{{ $lastTransaction->transaction_reference }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400 font-mono">Student:</span> <span class="font-bold text-slate-900">{{ $lastTransaction->student?->name }} ({{ $lastTransaction->student?->student_id }})</span></div>
                    <div class="flex justify-between"><span class="text-slate-400 font-mono">Item:</span> <span class="font-bold text-slate-900">{{ $lastTransaction->description }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400 font-mono">Coins Deducted:</span> <span class="font-mono font-black text-rose-600">{{ $lastTransaction->coins }} Coins</span></div>
                    <div class="pt-2 border-t border-slate-200 flex justify-between font-mono font-black text-sm"><span class="text-slate-800">Remaining Coins:</span> <span class="text-emerald-700">{{ $lastTransaction->student?->coins_cache }}</span></div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('presentation.receipt', $lastTransaction->id) }}" target="_blank" class="flex-1 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-mono font-bold uppercase tracking-wider transition-all shadow-md">
                        Print Slip
                    </a>
                    <button type="button" @click="modalOpen = false" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-mono font-bold uppercase tracking-wider transition-all cursor-pointer">
                        Next Student
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function presentationDesk() {
    return {
        scannerActive: false,
        html5QrCode: null,
        itemName: '{{ addslashes($presets[0]['name'] ?? 'Book') }}',
        unitCoins: {{ $presets[0]['coins'] ?? 50 }},
        quantity: 1,
        itemCategory: '{{ $presets[0]['category'] ?? 'book' }}',
        notes: '',

        selectPreset(name, coins, category) {
            this.itemName = name;
            this.unitCoins = coins;
            this.itemCategory = category;
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

        confirmSubmit(event) {
            let total = this.unitCoins * this.quantity;
            if (!confirm(this.itemName + ' നൽകി ' + total + ' കോയിനുകൾ കുറയ്ക്കട്ടെ?')) {
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
                        this.onScanSuccess(decodedText);
                    },
                    (errorMessage) => {
                        // background frame errors
                    }
                ).catch(err => {
                    alert('ക്യാമറ തുറക്കാൻ സാധിച്ചില്ല: ' + err + '\nദയവായി ക്യാമറ പെർമിഷൻ നൽകിയിട്ടുണ്ടോ എന്ന് പരിശോധിക്കുക.');
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
        },

        onScanSuccess(decodedText) {
            this.playBeep();
            this.stopCameraScanner();

            // Automatically put scanned text into search input and submit
            const input = document.getElementById('studentScanInput');
            if (input) {
                input.value = decodedText;
                input.form.submit();
            } else {
                window.location.href = "{{ route('presentation.dashboard') }}?student=" + encodeURIComponent(decodedText);
            }
        }
    }
}
</script>
@endpush
