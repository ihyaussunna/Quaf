@extends('layouts.student', ['title' => 'Student Dashboard'])

@section('content')
<div class="space-y-8">
    <!-- Student Profile Hero (Standard Executive Card) -->
    <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden shadow-sm hover:shadow-md transition-shadow">
        <!-- Subtle Ambient Theme Red Aura in the background -->
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-gradient-to-br from-[#be1e2d]/8 to-transparent rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-48 h-48 bg-gradient-to-tr from-slate-100 to-transparent rounded-full blur-xl pointer-events-none"></div>

        <div class="flex items-center gap-6 relative z-10">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-red-50 to-slate-100 border-2 border-red-200 ring-4 ring-red-50 flex items-center justify-center font-sora text-3xl font-bold text-[#be1e2d] overflow-hidden flex-shrink-0 shadow-sm">
                @if($student->photo_path)
                    <img src="{{ asset('storage/' . $student->photo_path) }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                @else
                    {{ substr($student->name, 0, 1) }}
                @endif
            </div>

            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-3 py-0.5 rounded-full text-[10px] font-mono font-bold tracking-wide" style="background-color: {{ $student->group->color_hex }}18; color: {{ $student->group->color_hex }}; border: 1px solid {{ $student->group->color_hex }}30;">
                        {{ $student->group->name }}
                    </span>
                    <span class="text-[11px] font-mono font-medium text-slate-500">• {{ $student->zone_name }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-sora font-black tracking-tight text-slate-900">{{ $student->name }}</h1>
                <p class="text-xs font-mono text-slate-600 flex items-center gap-2 flex-wrap">
                    <span>Chest #: <strong class="text-[#be1e2d] font-bold text-sm">#{{ $student->chest_number ?? '---' }}</strong></span>
                    <span class="text-slate-300">•</span>
                    <span>Student ID: <span class="font-semibold text-slate-700">{{ $student->student_id }}</span></span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 relative z-10">
            <a href="{{ route('student.idcard') }}" class="app-tap px-5 py-2.5 bg-gradient-to-r from-[#be1e2d] via-red-600 to-[#991522] hover:from-red-700 hover:to-red-900 text-white font-mono font-bold text-xs uppercase tracking-wider rounded-xl flex items-center gap-2.5 shadow-md shadow-red-900/20 hover:shadow-lg hover:shadow-red-900/30 active:scale-95 transition-all">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                <span>View QR Badge</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="app-tap px-4 py-2.5 bg-slate-100 hover:bg-red-50 hover:text-red-700 text-slate-700 font-mono font-bold text-xs uppercase rounded-xl border border-slate-200 transition-all cursor-pointer">
                    Sign Out
                </button>
            </form>
        </div>
    </div>

    <!-- Official Coin Wallet & Rewards Hub (Attractive 3-Metric Executive Design) -->
    <div class="rounded-3xl bg-gradient-to-br from-[#be1e2d] via-[#ad1826] to-[#800f1c] p-6 sm:p-8 text-white shadow-xl shadow-red-950/20 relative overflow-hidden border border-red-500/30">
        <!-- Ambient Decorative Glows -->
        <div class="absolute -right-12 -bottom-12 w-72 h-72 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute left-1/4 -top-16 w-60 h-60 bg-black/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.18),transparent_65%)] pointer-events-none"></div>

        <div class="relative z-10 space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/15 pb-5">
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-white text-[11px] font-mono font-bold border border-white/20 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-amber-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.12 1.87 1.48 0 2.22-.88 2.22-1.74 0-1.22-.87-1.84-2.58-2.31-2.14-.59-3.51-1.48-3.51-3.32 0-1.76 1.34-3.05 3.06-3.39V4h2.67v1.89c1.64.38 2.8 1.54 2.91 3.16h-1.96c-.14-.88-.73-1.63-1.85-1.63-1.23 0-1.99.73-1.99 1.62 0 1.05.78 1.62 2.45 2.1 2.29.65 3.65 1.51 3.65 3.52 0 1.87-1.39 3.08-3.21 3.43z"/></svg>
                        <span>QUAF 09 FEST COIN WALLET</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black font-sora tracking-tight text-white">You Have Earned Coins!</h2>
                    <p class="text-xs font-anek text-red-100/90 max-w-xl leading-relaxed">
                        ഗ്രൂപ്പ് പ്രോഗ്രാമുകളിലൂടെ ലഭിച്ച കോയിനുകൾ ഉപയോഗിച്ച് Presentation Desk-ൽ നിന്ന് പുസ്തകങ്ങളും മറ്റ് സമ്മാനങ്ങളും വാങ്ങാവുന്നതാണ്.
                    </p>
                </div>

                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-xs font-mono self-start sm:self-auto text-red-100">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Live Wallet Ledger</span>
                </div>
            </div>

            <!-- 3 Attractive Metric Cards: Earned, Spent, Balance -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- 1. Total Earned (ലഭിച്ച കോയിനുകൾ) -->
                <div class="bg-white/10 hover:bg-white/15 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/20 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-red-100/85">
                            ലഭിച്ച കോയിനുകൾ
                        </span>
                        <div class="w-7 h-7 rounded-lg bg-emerald-400/20 text-emerald-300 flex items-center justify-center font-bold text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl sm:text-3xl font-black font-mono text-white tracking-tight">
                            +{{ number_format($coinSummary['earned'] ?? 0) }}
                        </div>
                        <p class="text-[10px] font-mono text-emerald-200 mt-0.5">Total Credited Rewards</p>
                    </div>
                </div>

                <!-- 2. Total Spent / Redeemed (ഉപയോഗിച്ച കോയിനുകൾ) -->
                <div class="bg-white/10 hover:bg-white/15 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/20 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-red-100/85">
                            ഉപയോഗിച്ച കോയിനുകൾ
                        </span>
                        <div class="w-7 h-7 rounded-lg bg-amber-400/20 text-amber-300 flex items-center justify-center font-bold text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl sm:text-3xl font-black font-mono text-white tracking-tight">
                            -{{ number_format($coinSummary['spent'] ?? 0) }}
                        </div>
                        <p class="text-[10px] font-mono text-amber-200 mt-0.5">Presentation Purchases</p>
                    </div>
                </div>

                <!-- 3. Available Balance (ബാക്കിയുള്ള കോയിനുകൾ) -->
                <div class="bg-white/20 hover:bg-white/25 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/30 transition-all flex flex-col justify-between space-y-3 shadow-inner">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-white">
                            ബാക്കിയുള്ള കോയിനുകൾ
                        </span>
                        <div class="w-7 h-7 rounded-lg bg-amber-400 text-slate-950 flex items-center justify-center font-black text-xs shadow-xs">
                            ★
                        </div>
                    </div>
                    <div>
                        <div class="text-3xl sm:text-4xl font-black font-mono text-white tracking-tight drop-shadow-sm">
                            {{ number_format($coinSummary['balance'] ?? ($student->coins_cache ?? 0)) }}
                        </div>
                        <p class="text-[10px] font-mono text-white/90 font-bold mt-0.5">Available Coins</p>
                    </div>
                </div>
            </div>

            <!-- Transaction Ledger Activity Feed (Recent Transactions) -->
            @if(isset($coinTransactions) && $coinTransactions->isNotEmpty())
                <div class="pt-4 border-t border-white/15">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-red-100 flex items-center gap-2">
                            <span>സമീപകാല കോയിൻ ഇടപാടുകൾ</span>
                            <span class="text-[10px] opacity-75 font-normal">({{ $coinTransactions->count() }})</span>
                        </h4>
                        <span class="text-[10px] font-mono text-red-200">Recent Activity</span>
                    </div>

                    <div class="space-y-2">
                        @foreach($coinTransactions as $tx)
                            <div class="bg-white/10 backdrop-blur-sm rounded-xl px-3.5 py-2.5 border border-white/15 flex items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-2 h-2 rounded-full {{ $tx->coins > 0 ? 'bg-emerald-400' : 'bg-amber-300' }} flex-shrink-0"></span>
                                    <div class="min-w-0">
                                        <p class="font-bold text-white truncate text-xs">{{ $tx->description ?: 'Coin Transaction' }}</p>
                                        <p class="text-[10px] font-mono text-red-200 truncate">{{ $tx->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="font-mono font-bold {{ $tx->coins > 0 ? 'text-emerald-300' : 'text-amber-200' }}">
                                        {{ $tx->coins > 0 ? '+' : '' }}{{ number_format($tx->coins) }}
                                    </span>
                                    <span class="text-[10px] font-mono text-red-200 block">coins</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Official Individual Programme Quota Meter (5 Max) -->
    <div class="rounded-3xl bg-white border border-slate-200/90 p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6 hover:shadow-md transition-shadow">
        <div class="space-y-2 w-full sm:w-auto">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ ($individualCount ?? 0) >= 5 ? 'bg-[#be1e2d]' : 'bg-emerald-500 animate-pulse' }}"></span>
                <h3 class="text-sm font-mono font-bold uppercase tracking-wider text-slate-900">Individual Programme Quota</h3>
            </div>
            <p class="text-xs text-slate-500 font-sora">
                Each student can participate in a maximum of 5 individual programmes (Own Zone + Mix Zone).
            </p>
            <!-- 5 Visual Pill Indicators -->
            <div class="flex items-center gap-1.5 pt-1 max-w-xs">
                @for($i = 1; $i <= ($maxSlots ?? 5); $i++)
                    <div class="h-1.5 flex-1 rounded-full {{ $i <= ($individualCount ?? 0) ? 'bg-[#be1e2d]' : 'bg-slate-200' }}"></div>
                @endfor
            </div>
        </div>
        <div class="flex items-center gap-5 flex-shrink-0 bg-slate-50 border border-slate-200/80 px-6 py-3.5 rounded-2xl">
            <div class="text-right">
                <div class="text-2xl font-sora font-black">
                    <span class="{{ ($individualCount ?? 0) >= 5 ? 'text-[#be1e2d]' : 'text-emerald-700' }}">{{ $individualCount ?? 0 }}</span>
                    <span class="text-slate-400 text-lg">/ {{ $maxSlots ?? 5 }}</span>
                </div>
                <span class="text-[10px] font-mono font-bold uppercase text-slate-500 tracking-wider">Used Slots</span>
            </div>
            <div class="h-8 w-[1px] bg-slate-300"></div>
            <div class="text-left">
                <div class="text-2xl font-sora font-black {{ ($remainingSlots ?? 0) > 0 ? 'text-[#be1e2d]' : 'text-slate-400' }}">
                    {{ $remainingSlots ?? 5 }}
                </div>
                <span class="text-[10px] font-mono font-bold uppercase text-slate-500 tracking-wider">Remaining</span>
            </div>
        </div>
    </div>

    <!-- PROMINENT NEXT PROGRAM COUNTDOWN WIDGET (Clean Standard Design) -->
    <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 relative overflow-hidden shadow-sm hover:shadow-md transition-shadow">
        <!-- Subtle Ambient Background Glow -->
        <div class="absolute -top-16 -right-16 w-52 h-52 bg-gradient-to-br from-[#be1e2d]/8 to-transparent rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <div class="space-y-2.5">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 border border-red-200 text-[#be1e2d] text-xs font-mono font-bold shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-[#be1e2d] shadow-[0_0_6px_#be1e2d] animate-ping"></span>
                    <span>NEXT PROGRAM STAGE SLOT</span>
                </div>

                @if($nextEntry)
                    <h2 class="text-2xl sm:text-3xl font-sora font-black text-slate-900 tracking-tight">
                        {{ $nextEntry->program->name }}
                    </h2>
                    <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs font-mono text-slate-600">
                        <span>Venue: <strong class="text-slate-900 font-bold">{{ $nextEntry->program->stage->name ?? 'TBA' }}</strong></span>
                        <span class="text-slate-300">•</span>
                        <span>Scheduled: <strong class="text-[#be1e2d] font-bold">{{ $nextEntry->program->scheduled_time?->format('M d, h:i A') }}</strong></span>
                        <span class="text-slate-300">•</span>
                        <span>Chest: <strong class="text-slate-900 font-bold">#{{ $nextEntry->chest_number }}</strong></span>
                    </div>
                @else
                    <h2 class="text-xl font-sora font-bold text-slate-700">
                        No upcoming programs scheduled at this time.
                    </h2>
                    <p class="text-xs font-mono text-slate-500">
                        Check back once the next stage schedules are announced by the festival desk.
                    </p>
                @endif
            </div>

            @if($nextEntry && $nextEntry->program->scheduled_time)
                <!-- Live Countdown Timer (Theme Red Digit Cards) -->
                <div x-data="{
                    target: new Date('{{ $nextEntry->program->scheduled_time->toIso8601String() }}').getTime(),
                    now: new Date().getTime(),
                    days: 0, hours: 0, minutes: 0, seconds: 0,
                    init() {
                        this.update();
                        setInterval(() => { this.now = new Date().getTime(); this.update(); }, 1000);
                    },
                    update() {
                        let diff = this.target - this.now;
                        if (diff <= 0) {
                            this.days = 0; this.hours = 0; this.minutes = 0; this.seconds = 0;
                            return;
                        }
                        this.days = Math.floor(diff / (1000 * 60 * 60 * 24));
                        this.hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        this.minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                        this.seconds = Math.floor((diff % (1000 * 60)) / 1000);
                    }
                }" class="flex items-center gap-2 sm:gap-3 font-mono">
                    <div class="bg-white border border-slate-200/80 hover:border-red-200 rounded-2xl p-3 sm:p-3.5 text-center min-w-[68px] sm:min-w-[76px] shadow-sm transition-all">
                        <span class="text-2xl sm:text-3xl font-black text-[#be1e2d] tracking-tight block" x-text="days">0</span>
                        <span class="text-[9px] text-slate-500 block uppercase mt-0.5 font-bold tracking-wider">Days</span>
                    </div>
                    <div class="bg-white border border-slate-200/80 hover:border-red-200 rounded-2xl p-3 sm:p-3.5 text-center min-w-[68px] sm:min-w-[76px] shadow-sm transition-all">
                        <span class="text-2xl sm:text-3xl font-black text-[#be1e2d] tracking-tight block" x-text="hours">0</span>
                        <span class="text-[9px] text-slate-500 block uppercase mt-0.5 font-bold tracking-wider">Hours</span>
                    </div>
                    <div class="bg-white border border-slate-200/80 hover:border-red-200 rounded-2xl p-3 sm:p-3.5 text-center min-w-[68px] sm:min-w-[76px] shadow-sm transition-all">
                        <span class="text-2xl sm:text-3xl font-black text-[#be1e2d] tracking-tight block" x-text="minutes">0</span>
                        <span class="text-[9px] text-slate-500 block uppercase mt-0.5 font-bold tracking-wider">Mins</span>
                    </div>
                    <div class="bg-white border border-slate-200/80 hover:border-red-200 rounded-2xl p-3 sm:p-3.5 text-center min-w-[68px] sm:min-w-[76px] shadow-sm transition-all">
                        <span class="text-2xl sm:text-3xl font-black text-[#be1e2d] tracking-tight block" x-text="seconds">0</span>
                        <span class="text-[9px] text-slate-500 block uppercase mt-0.5 font-bold tracking-wider">Secs</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Enrolled Programs Grid (Standard Card Design) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-sora font-bold text-slate-900">My Enrolled Programs</h2>
            <span class="text-xs font-mono text-slate-500 font-semibold px-3 py-1 bg-slate-100 rounded-full border border-slate-200">Total: {{ $myPrograms->count() }} Events</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($myPrograms as $entry)
                <div class="bg-white border border-slate-200/90 rounded-3xl p-6 space-y-4 hover:border-red-300 transition-all shadow-sm hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase">
                            {{ $entry->program->zone?->name ?? $entry->program->eligibility ?? 'All Zones' }} • {{ ucfirst($entry->program->type) }}
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-mono uppercase font-bold
                            {{ $entry->status === 'verified' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                            {{ $entry->status }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-xl font-sora font-bold text-slate-900">{{ $entry->program->name }}</h3>
                        <p class="text-xs font-mono text-slate-500 mt-1">Program Code: <span class="font-semibold text-slate-700">{{ $entry->program->code }}</span> • Chest <span class="text-[#be1e2d] font-bold">#{{ $entry->chest_number }}</span></p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 grid grid-cols-2 gap-3 text-xs font-mono">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Stage Venue</span>
                            <span class="text-slate-900 font-bold">{{ $entry->program->stage->name ?? 'TBA' }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Schedule Slot</span>
                            <span class="text-[#be1e2d] font-bold">{{ $entry->program->scheduled_time?->format('M d, h:i A') ?? 'TBA' }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-slate-500 font-mono text-xs bg-white rounded-3xl border border-slate-200 shadow-sm">
                    You have not been enrolled in any programs yet. Contact your Group Leader.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Announcements -->
    @if($announcements->isNotEmpty())
        <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 space-y-4 shadow-sm">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#be1e2d]"></span>
                <h3 class="text-lg font-sora font-bold text-slate-900">Festival Announcements</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($announcements as $ann)
                    <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-1 hover:border-slate-300 transition">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-sora font-bold text-slate-900">{{ $ann->title }}</h4>
                            <span class="text-[10px] font-mono text-slate-400">{{ $ann->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs font-sora text-slate-700 leading-relaxed">{{ $ann->content }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
