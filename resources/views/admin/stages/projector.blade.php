<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $stage->name }} ({{ $stage->code }}) — Live Stage Display | QUAF 9.0</title>
    <meta http-equiv="refresh" content="15">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.85; transform: scale(1.02); }
        }
        .animate-pulse-slow {
            animation: pulse-slow 3s infinite ease-in-out;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen flex flex-col font-sora selection:bg-amber-100 selection:text-amber-900 antialiased overflow-x-hidden" x-data="{
    time: '',
    date: '',
    isFullscreen: false,
    updateClock() {
        const now = new Date();
        this.time = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        this.date = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
    },
    toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => alert(err.message));
            this.isFullscreen = true;
        } else {
            document.exitFullscreen();
            this.isFullscreen = false;
        }
    }
}" x-init="updateClock(); setInterval(() => updateClock(), 1000)">

    <!-- Top Header Bar -->
    <header class="bg-white border-b border-slate-200 px-6 lg:px-10 py-4 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white shadow-md shadow-amber-500/20 font-sora font-black text-xl tracking-wider">
                Q9
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200">QUAF 9.0</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        LIVE STAGE SCREEN
                    </span>
                </div>
                <h1 class="text-xl lg:text-2xl font-black tracking-tight text-slate-900 font-sora mt-0.5">
                    {{ $stage->name }} <span class="text-amber-700 font-mono text-base font-bold">({{ $stage->code }})</span>
                </h1>
            </div>
        </div>

        <!-- Clock & Controls -->
        <div class="flex items-center gap-4">
            <div class="text-right hidden sm:block">
                <div class="font-mono text-2xl lg:text-3xl font-black text-slate-900 tracking-tight" x-text="time">--:--:--</div>
                <div class="text-xs font-mono font-semibold text-slate-500 uppercase tracking-wider" x-text="date">---</div>
            </div>

            <button @click="toggleFullscreen()" type="button" class="p-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition border border-slate-200 shadow-sm" title="Toggle Fullscreen">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0 0l-5-5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
            </button>
        </div>
    </header>

    <!-- Main Live Screen Area -->
    <main class="flex-1 p-6 lg:p-10 max-w-[1600px] w-full mx-auto flex flex-col gap-6">

        @if($stage->currentProgram)
            <!-- Current Program Banner -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 lg:p-8 shadow-sm relative overflow-hidden">
                <div class="absolute -right-12 -top-12 w-64 h-64 bg-amber-100/50 rounded-full blur-3xl pointer-events-none"></div>
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200">
                                CURRENT PROGRAM
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $stage->currentProgram->program_code }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold uppercase tracking-wider bg-blue-50 text-[#005c94] border border-blue-200">
                                {{ strtoupper($stage->currentProgram->eligibility ?? ($stage->currentProgram->zone?->name ?? 'Zone')) }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                {{ strtoupper(str_replace('_', ' ', $stage->currentProgram->type)) }}
                            </span>
                        </div>
                        <h2 class="text-3xl lg:text-5xl font-black text-slate-900 tracking-tight font-sora">
                            {{ $stage->currentProgram->name }}
                        </h2>
                        @if($stage->currentProgram->malayalam_name)
                            <p class="text-xl lg:text-2xl font-semibold text-slate-600">
                                {{ $stage->currentProgram->malayalam_name }}
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-col items-start md:items-end gap-1">
                        <span class="text-xs font-mono uppercase tracking-wider text-slate-500 font-bold">Stage Status</span>
                        @if($stage->status === 'active')
                            <span class="px-4 py-2 rounded-xl text-sm font-bold uppercase tracking-wide bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-ping"></span>
                                Active in Session
                            </span>
                        @elseif($stage->status === 'break')
                            <span class="px-4 py-2 rounded-xl text-sm font-bold uppercase tracking-wide bg-amber-100 text-amber-800 border border-amber-200">
                                Interval / Break
                            </span>
                        @else
                            <span class="px-4 py-2 rounded-xl text-sm font-bold uppercase tracking-wide bg-rose-100 text-rose-800 border border-rose-200">
                                Closed
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Spotlight Grid: Current Performer + Upcoming Queue -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-1">
                <!-- Massive Spotlight Performer Card (8 cols) -->
                <div class="lg:col-span-8 flex flex-col">
                    @if($currentCall)
                        @php
                            $entry = $currentCall->entry;
                            $student = $entry?->student;
                            $group = $entry?->group ?? $student?->group;
                            $chestNo = $student?->chest_number ?? $entry?->chest_number ?? '---';
                        @endphp
                        <div class="bg-gradient-to-br from-white to-amber-50/40 rounded-3xl border-2 border-amber-300/80 p-8 lg:p-12 shadow-lg flex-1 flex flex-col justify-between relative overflow-hidden">
                            <!-- Background Watermark -->
                            <div class="absolute right-4 bottom-4 font-mono font-black text-slate-100 text-[180px] lg:text-[240px] leading-none select-none pointer-events-none -z-0 opacity-40">
                                {{ $chestNo }}
                            </div>

                            <div class="relative z-10 space-y-6">
                                <div class="flex items-center justify-between">
                                    <span class="px-4 py-1.5 rounded-full text-xs font-mono font-extrabold uppercase tracking-widest bg-amber-500 text-white shadow-md shadow-amber-500/30 flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-white animate-pulse"></span>
                                        NOW ON STAGE
                                    </span>
                                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-500">
                                        Call Status: <strong class="text-amber-700">{{ strtoupper(str_replace('_', ' ', $currentCall->status)) }}</strong>
                                    </span>
                                </div>

                                <!-- Massive Chest Number Display -->
                                <div class="space-y-1">
                                    <div class="text-xs lg:text-sm font-mono font-bold uppercase tracking-widest text-slate-500">
                                        CONTESTANT CHEST NUMBER
                                    </div>
                                    <div class="text-7xl lg:text-9xl font-mono font-black text-slate-900 tracking-tight leading-none drop-shadow-sm">
                                        #{{ $chestNo }}
                                    </div>
                                </div>

                                <!-- Contestant / Team Name -->
                                <div class="space-y-2 pt-2 border-t border-slate-200/80">
                                    <div class="text-2xl lg:text-4xl font-extrabold text-slate-900 tracking-tight font-sora">
                                        {{ $student ? $student->name : ($group ? $group->name . ' Team' : 'Contestant') }}
                                    </div>
                                    @if($student && $student->admission_number)
                                        <div class="text-xs font-mono font-bold text-slate-500">
                                            ID: {{ $student->admission_number }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Team / Group Banner at Bottom -->
                            @if($group)
                                <div class="relative z-10 pt-6 mt-6 border-t border-slate-200 flex items-center justify-between bg-white/80 backdrop-blur rounded-2xl p-4 border border-slate-200">
                                    <div class="flex items-center gap-3">
                                        <span class="w-5 h-5 rounded-full border border-slate-300 shadow-sm" style="background-color: {{ $group->color_code ?? '#f3bd2e' }}"></span>
                                        <div>
                                            <div class="text-[11px] font-mono uppercase font-bold text-slate-500 tracking-wider">Group / Team</div>
                                            <div class="text-base font-bold text-slate-900 font-sora">{{ $group->name }}</div>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-lg text-xs font-mono font-bold uppercase tracking-wider bg-slate-100 text-slate-700">
                                        {{ $group->code }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Standby State -->
                        <div class="bg-white rounded-3xl border border-slate-200 p-12 shadow-sm flex-1 flex flex-col items-center justify-center text-center space-y-4">
                            <div class="w-20 h-20 rounded-3xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200 text-3xl font-sora font-black animate-pulse-slow">
                                Q9
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-2xl lg:text-3xl font-bold text-slate-900 font-sora">Awaiting Next Performer</h3>
                                <p class="text-slate-500 text-sm max-w-md mx-auto">
                                    Green Room coordinator is preparing the next contestant for stage presentation.
                                </p>
                            </div>
                            <span class="px-4 py-2 rounded-full text-xs font-mono font-bold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200">
                                Standby • Stage Ready
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Right Column: Next Up Queue & Next Event (4 cols) -->
                <div class="lg:col-span-4 flex flex-col gap-6">
                    <!-- Next Up Queue -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex-1 flex flex-col">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <h3 class="font-mono font-extrabold text-xs uppercase tracking-widest text-slate-700">NEXT UP / ON DECK</h3>
                            </div>
                            <span class="text-xs font-mono font-bold text-slate-500">{{ $upcomingCalls->count() }} In Queue</span>
                        </div>

                        <div class="space-y-3 flex-1 overflow-y-auto">
                            @forelse($upcomingCalls as $idx => $call)
                                @php
                                    $uEntry = $call->entry;
                                    $uStudent = $uEntry?->student;
                                    $uGroup = $uEntry?->group ?? $uStudent?->group;
                                    $uChest = $uStudent?->chest_number ?? $uEntry?->chest_number ?? '---';
                                @endphp
                                <div class="p-3.5 rounded-2xl bg-slate-50 hover:bg-amber-50/50 border border-slate-200 transition flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center font-mono font-black text-slate-900 text-sm shadow-sm">
                                            #{{ $uChest }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-sm text-slate-900 truncate max-w-[150px]">
                                                {{ $uStudent ? $uStudent->name : ($uGroup ? $uGroup->name . ' Team' : 'Contestant') }}
                                            </div>
                                            <div class="text-[11px] font-mono text-slate-500">
                                                {{ $uGroup ? $uGroup->name : 'General' }}
                                            </div>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-mono font-bold uppercase tracking-wider {{ $call->status === 'ready' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $call->status }}
                                    </span>
                                </div>
                            @empty
                                <div class="p-8 text-center text-slate-400 text-xs font-mono flex-1 flex items-center justify-center">
                                    No contestants queued in Green Room
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Next Scheduled Program -->
                    @if($stage->nextProgram)
                        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                            <div class="text-[10px] font-mono font-extrabold uppercase tracking-widest text-slate-500 mb-1">
                                UPCOMING EVENT
                            </div>
                            <div class="font-sora font-bold text-base text-slate-900 line-clamp-1">
                                {{ $stage->nextProgram->name }}
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-slate-100 text-slate-700">
                                    {{ $stage->nextProgram->program_code }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-amber-50 text-amber-800 border border-amber-200">
                                    {{ $stage->nextProgram->eligibility ?? ($stage->nextProgram->zone?->name ?? 'Zone') }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- Stage Idle -->
            <div class="bg-white rounded-3xl border border-slate-200 p-16 shadow-sm flex-1 flex flex-col items-center justify-center text-center space-y-6">
                <div class="w-24 h-24 rounded-3xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200 text-4xl font-sora font-black shadow-md">
                    Q9
                </div>
                <div class="space-y-2 max-w-lg">
                    <h2 class="text-3xl lg:text-4xl font-black text-slate-900 font-sora tracking-tight">Stage Currently Idle</h2>
                    <p class="text-slate-500 text-sm">
                        There is no active program scheduled right now on {{ $stage->name }}. The stage screen will automatically update when the festival session resumes.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-4 py-2 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $stage->name }} • {{ $stage->code }}
                    </span>
                    <span class="px-4 py-2 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200">
                        LIVE STAGE DISPLAY
                    </span>
                </div>
            </div>
        @endif

    </main>

    <!-- Bottom Marquee Ticker -->
    <footer class="bg-white border-t border-slate-200 px-6 py-3 shadow-inner flex items-center gap-4 text-xs font-mono">
        <span class="px-2.5 py-1 rounded bg-amber-500 text-white font-extrabold tracking-wider uppercase whitespace-nowrap shadow-sm">
            QUAF BULLETIN
        </span>
        <div class="flex-1 overflow-hidden whitespace-nowrap">
            <div class="inline-block animate-marquee font-medium text-slate-700">
                Organized by Ihyaussunna Students Union, Markazu Saquafathi Sunniyya • Official Stage Display • Auto-refreshes every 15 seconds • Results and schedules available at the public portal.
            </div>
        </div>
        <div class="text-slate-400 hidden sm:block whitespace-nowrap">
            Auto-Sync: <strong class="text-emerald-600">Active</strong>
        </div>
    </footer>

</body>
</html>
