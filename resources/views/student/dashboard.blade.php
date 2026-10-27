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
                    <span>Chest #: <strong class="text-[#be1e2d] font-bold text-sm">#{{ $student->chest_number ?? $student->student_id ?? '---' }}</strong></span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 relative z-10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="app-tap px-4 py-2.5 bg-slate-100 hover:bg-red-50 hover:text-red-700 text-slate-700 font-mono font-bold text-xs uppercase rounded-xl border border-slate-200 transition-all cursor-pointer">
                    Sign Out
                </button>
            </form>
        </div>
    </div>

    <!-- Official Coin Wallet & Rewards Hub (Vibrant Red & Yellow Gradient Fintech Design) -->
    <div class="rounded-3xl bg-gradient-to-br from-[#be1e2d] via-[#d9381e] to-[#f59e0b] p-5 sm:p-7 text-white shadow-2xl shadow-red-950/25 relative overflow-hidden border-2 border-amber-300/40"
         style="background: linear-gradient(135deg, #be1e2d 0%, #c41e3a 35%, #d9531e 70%, #f59e0b 100%);">
        <!-- Ambient Decorative Glows -->
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-amber-400/25 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute left-1/4 -top-12 w-56 h-56 bg-red-950/35 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 space-y-4">
            <!-- Compact Top Bar -->
            <div class="flex items-center justify-between gap-3">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-950/40 backdrop-blur-md text-white text-[11px] font-mono font-bold border border-white/20 shadow-xs">
                    <svg class="w-3.5 h-3.5 text-amber-300 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.03 6.9C12.68 8.17 11.94 7.66 11.07 7.66H8.28V6.42h7.44v1.24h-2.02c.3.36.52.78.65 1.24H15.72v1.24h-1.25c-.16 1.74-1.62 3.1-3.4 3.1h-.45l4.17 4.34h-1.72L8.9 13.24V12h2.17c1.09 0 2-.81 2.15-1.86H8.28V8.9h4.75z"/></svg>
                    <span>QUAF FEST COIN WALLET</span>
                </div>

                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-950/40 backdrop-blur-md text-[10px] font-mono text-emerald-300 border border-white/10">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Live Wallet</span>
                </div>
            </div>

            <!-- Sleek Horizontal Balance & Quick Stats Showcase -->
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 pt-1">
                <div>
                    <span class="text-[10px] font-mono uppercase tracking-wider text-amber-100 font-semibold drop-shadow-xs">Available Balance</span>
                    <div class="text-3xl sm:text-4xl font-black font-sora text-white tracking-tight flex items-baseline gap-2 mt-0.5 drop-shadow-sm">
                        <span>{{ number_format($coinSummary['balance'] ?? ($student->coins_cache ?? 0)) }}</span>
                        <span class="text-xs font-mono font-bold text-amber-200 uppercase tracking-widest">Coins</span>
                    </div>
                </div>

                <!-- 2 Compact Pill Tiles: Earned & Spent -->
                <div class="flex items-center gap-2">
                    <div class="flex-1 sm:flex-none px-3.5 py-2 rounded-xl bg-slate-950/40 backdrop-blur-md border border-white/20">
                        <span class="text-[9px] font-mono uppercase tracking-wider text-emerald-300 font-bold block">Earned</span>
                        <span class="text-sm sm:text-base font-black font-mono text-white block mt-0.5">+{{ number_format($coinSummary['total_earned'] ?? $coinSummary['earned'] ?? 0) }}</span>
                    </div>
                    <div class="flex-1 sm:flex-none px-3.5 py-2 rounded-xl bg-slate-950/40 backdrop-blur-md border border-white/20">
                        <span class="text-[9px] font-mono uppercase tracking-wider text-amber-200 font-bold block">Spent</span>
                        <span class="text-sm sm:text-base font-black font-mono text-white block mt-0.5">-{{ number_format($coinSummary['total_spent'] ?? $coinSummary['spent'] ?? 0) }}</span>
                    </div>
                </div>
            </div>

            <!-- Recognition Gift Status -->
            @php
                $isParticipant = $student->isCompetitionParticipant();
            @endphp
            <div class="pt-2 border-t border-white/15">
                <div class="rounded-xl px-4 py-2.5 border flex items-center justify-between gap-3 {{ $student->recognition_gift_delivered ? 'bg-emerald-500/20 border-emerald-400/40 text-emerald-100' : ($isParticipant ? 'bg-slate-950/40 border-white/20 text-white' : 'bg-slate-950/20 border-white/10 text-white/60') }}">
                    <div class="min-w-0">
                        <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-white/80">
                            Recognition Gift
                        </div>
                        <div class="text-[11px] truncate {{ $student->recognition_gift_delivered ? 'text-emerald-200 font-bold' : ($isParticipant ? 'text-amber-200' : 'text-slate-400') }}">
                            @if($student->recognition_gift_delivered)
                                കൈപ്പറ്റി (Delivered)
                            @elseif($isParticipant)
                                കൗണ്ടറിൽ നിന്ന് കൈപ്പറ്റുക
                            @else
                                മത്സരങ്ങളിൽ പങ്കെടുത്തവർക്ക് മാത്രം
                            @endif
                        </div>
                    </div>
                    <span class="shrink-0 text-[10px] font-mono font-bold px-3 py-1 rounded-lg {{ $student->recognition_gift_delivered ? 'bg-emerald-400 text-slate-950' : ($isParticipant ? 'bg-amber-400 text-slate-950' : 'bg-slate-700 text-slate-300') }}">
                        {{ $student->recognition_gift_delivered ? 'Delivered' : ($isParticipant ? 'Pending' : 'N/A') }}
                    </span>
                </div>
            </div>

            <!-- Transaction Ledger Activity Feed (Recent Transactions) -->
            @if(isset($coinTransactions) && $coinTransactions->isNotEmpty())
                <div class="pt-2 border-t border-white/15" x-data="{ expanded: false }">
                    <button type="button" @click="expanded = !expanded" class="w-full flex items-center justify-between text-[11px] font-mono text-red-100/90 hover:text-white transition cursor-pointer py-0.5">
                        <span class="font-bold uppercase tracking-wider flex items-center gap-1.5">
                            <span>Recent Coin Transactions</span>
                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-white/15 text-white font-mono">({{ $coinTransactions->count() }})</span>
                        </span>
                        <span class="text-[10px] underline" x-text="expanded ? 'Hide Ledger' : 'View Ledger'">View Ledger</span>
                    </button>

                    <div x-show="expanded" x-cloak class="space-y-1.5 pt-2">
                        @foreach($coinTransactions as $tx)
                            <div class="bg-black/30 backdrop-blur-sm rounded-xl px-3 py-2 border border-white/10 flex items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $tx->coins > 0 ? 'bg-emerald-400' : 'bg-amber-300' }} flex-shrink-0"></span>
                                    <div class="min-w-0">
                                        <p class="font-bold text-white truncate text-[11px]">{{ $tx->description ?: 'Coin Transaction' }}</p>
                                        <p class="text-[9px] font-mono text-amber-200/80 truncate">{{ $tx->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0 font-mono font-black text-xs">
                                    <span class="{{ $tx->coins > 0 ? 'text-emerald-300' : 'text-amber-200' }}">
                                        {{ $tx->coins > 0 ? '+' : '' }}{{ number_format($tx->coins) }}
                                    </span>
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
