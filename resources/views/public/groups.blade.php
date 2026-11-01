@extends('layouts.public', [
    'title' => 'Collegiate Teams & Academic Houses — QUAF 2026 | Markaz Art Fest',
    'metaDescription' => 'The 5 official collegiate teams of QUAF 2026: Lumo Fikric, Pacto Hikmic, Conco Majdic, Yugo Rushdic, and Unio Hilmic at Jamia Markaz.',
])

@section('content')

<!-- Header Section -->
<section class="py-10 sm:py-14 bg-white border-b border-slate-200">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase animate-subheading">FESTIVAL TEAMS</span>
                <div class="flex flex-wrap items-center gap-3 mt-1">
                    <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 animate-heading">Group Standings</h1>
                    @php
                        $standingsCount = (int) \App\Models\FestivalSetting::get('public_standings_results_count', 0);
                        $standingsLabel = $standingsLabel ?? ('After ' . sprintf('%02d', $standingsCount) . ' Results');
                        $standingsPoster = $standingsPosterUrl ?? \App\Models\FestivalSetting::get('public_standings_poster_url');
                    @endphp
                    @if(!empty($isFinalPublished))
                        <span class="px-3.5 py-1 rounded-full text-xs font-mono font-black bg-gradient-to-r from-amber-400 to-yellow-400 text-slate-950 border border-amber-300 shadow-xs flex items-center gap-1.5 animate-pulse">
                            <svg class="w-3.5 h-3.5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.004 0A5.25 5.25 0 0012 3.75a5.25 5.25 0 00-2.25 10.5m4.5 0h-4.5" /></svg>
                            <span>FINAL STANDINGS &amp; CHAMPIONS</span>
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-emerald-50 text-emerald-800 border border-emerald-300 shadow-2xs">
                            {{ $standingsLabel }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-slate-600 mt-2 max-w-xl">
                    Official leaderboard and cumulative point standings of competing festival groups in QUAF.
                </p>
            </div>

            @if(!empty($standingsPoster))
                <div class="shrink-0 pt-2 sm:pt-0">
                    <a href="{{ route('groups.download-poster') }}" class="px-4 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>Download Poster</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Leaderboard Vertical List matching User Template (Light Theme) -->
<section class="py-10 sm:py-16 bg-slate-50 min-h-[60vh] relative">
    @if(!empty($isFinalPublished))
        <!-- Celebration Canvas for Confetti Fireworks -->
        <canvas id="celebration-canvas" class="fixed inset-0 pointer-events-none z-50 w-full h-full"></canvas>
    @endif

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(!empty($isFinalPublished) && $groups->isNotEmpty())
            <!-- Grand Championship Victory Banner for Rank 1 Team -->
            @php
                $championGroup = $groups->first();
                $runnerUpGroup = $groups->skip(1)->first();
                $secondRunnerUpGroup = $groups->skip(2)->first();
            @endphp
            <div class="mb-8 rounded-3xl bg-gradient-to-b from-amber-400 via-yellow-400 to-amber-500 p-1 sm:p-1.5 shadow-2xl shadow-amber-500/20">
                <div class="rounded-[22px] bg-gradient-to-b from-slate-950 via-slate-900 to-indigo-950 p-6 sm:p-10 text-white relative overflow-hidden text-center">
                    
                    <!-- Ambient Glow -->
                    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-amber-400/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-24 left-1/2 -translate-x-1/2 w-80 h-80 bg-yellow-500/15 rounded-full blur-3xl pointer-events-none"></div>

                    <!-- Content -->
                    <div class="relative z-10 max-w-2xl mx-auto space-y-4">
                        
                        <!-- Grand Trophy Badge -->
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-400/15 border border-amber-400/40 text-amber-300 font-mono text-xs uppercase tracking-widest font-black shadow-inner">
                            <svg class="w-4 h-4 text-amber-400 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.004 0A5.25 5.25 0 0012 3.75a5.25 5.25 0 00-2.25 10.5m4.5 0h-4.5" /></svg>
                            <span>QUAF 2026 OVERALL CHAMPIONS</span>
                        </div>

                        <!-- Trophy Emblem -->
                        <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto rounded-3xl bg-gradient-to-br from-amber-300 via-amber-400 to-yellow-600 text-slate-950 flex items-center justify-center shadow-xl shadow-amber-500/40 ring-4 ring-amber-300/30">
                            <svg class="w-11 h-11 sm:w-14 sm:h-14 text-slate-950" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l2.4 4.8 5.3.8-3.8 3.7.9 5.3L12 16.1l-4.8 2.5.9-5.3-3.8-3.7 5.3-.8L12 2z"/>
                            </svg>
                        </div>

                        <!-- Title -->
                        <div>
                            <div class="text-xs sm:text-sm font-mono tracking-widest uppercase font-bold text-amber-300">
                                Official Festival Championship
                            </div>
                            <h2 class="text-3xl sm:text-5xl lg:text-6xl font-sora font-black tracking-tight text-white mt-1">
                                {{ $championGroup->name ?? 'CHAMPION' }}
                            </h2>
                        </div>

                        <!-- Points Banner -->
                        <div class="inline-flex items-baseline gap-2 px-6 py-2 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 shadow-lg">
                            <span class="text-xs font-mono font-bold text-slate-300 uppercase">Total Score:</span>
                            <span class="text-3xl sm:text-4xl font-sora font-black text-amber-300">{{ number_format($championGroup->public_points_cache ?? 0) }}</span>
                            <span class="text-xs font-mono font-bold text-slate-400 uppercase">PTS</span>
                        </div>

                        <!-- Runners-Up Sub-Banner -->
                        <div class="pt-2 flex flex-wrap items-center justify-center gap-3 sm:gap-4 text-xs font-mono">
                            @if($runnerUpGroup)
                                <div class="px-3.5 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700 flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-md bg-slate-300 text-slate-950 font-black text-[10px] flex items-center justify-center">2</span>
                                    <span class="text-slate-300 font-bold uppercase">1st Runner-Up:</span>
                                    <span class="text-white font-black">{{ $runnerUpGroup->name }}</span>
                                    <span class="text-slate-400">({{ number_format($runnerUpGroup->public_points_cache ?? 0) }} pts)</span>
                                </div>
                            @endif

                            @if($secondRunnerUpGroup)
                                <div class="px-3.5 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700 flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-md bg-amber-700 text-white font-black text-[10px] flex items-center justify-center">3</span>
                                    <span class="text-slate-300 font-bold uppercase">2nd Runner-Up:</span>
                                    <span class="text-white font-black">{{ $secondRunnerUpGroup->name }}</span>
                                    <span class="text-slate-400">({{ number_format($secondRunnerUpGroup->public_points_cache ?? 0) }} pts)</span>
                                </div>
                            @endif
                        </div>

                        <!-- Celebrate Again Button -->
                        <div class="pt-2">
                            <button type="button" onclick="launchConfettiCannon()" class="px-5 py-2.5 rounded-full bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-300 hover:to-yellow-300 text-slate-950 font-sora font-black text-xs uppercase tracking-wider transition shadow-lg shadow-amber-400/30 flex items-center gap-2 mx-auto cursor-pointer">
                                <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                <span>Celebrate Again</span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        @endif

        <!-- Outer Light Card matching template -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-5 sm:p-7 shadow-sm">
            <!-- Table Header: RANK & TEAM | TOTAL POINTS -->
            <div class="flex items-center justify-between px-2 mb-4 sm:mb-5">
                <span class="text-xs font-mono uppercase tracking-widest text-slate-400 font-bold">
                    RANK &amp; TEAM
                </span>
                <span class="text-xs font-mono uppercase tracking-widest text-slate-400 font-bold">
                    TOTAL POINTS
                </span>
            </div>

            <!-- Rows: Only Rank Number, Team Name, Points -->
            <div class="space-y-3 sm:space-y-4">
                @foreach($groups as $group)
                    @php
                        $rank = $loop->iteration;
                        $isWinner = $rank === 1;
                        $showWinnerBadge = $isWinner && (!empty($isFullResultsPublished) && $isFullResultsPublished === true);
                    @endphp

                    @if($rank === 1)
                        <!-- Rank 1 Row (Gold Theme) -->
                        <div class="rounded-2xl p-4 sm:p-5 bg-gradient-to-r from-amber-50/90 via-yellow-50/40 to-white border border-amber-300/90 flex items-center justify-between gap-4 shadow-xs transition-all ring-1 ring-amber-400/20">
                            <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                                @if($showWinnerBadge)
                                    <!-- Trophy Box when Full Results Published -->
                                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-amber-400 text-slate-950 font-black shadow-xs flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.004 0A5.25 5.25 0 0012 3.75a5.25 5.25 0 00-2.25 10.5m4.5 0h-4.5" />
                                        </svg>
                                    </div>
                                @else
                                    <!-- Rank 1 Number Box during Live Festival -->
                                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-amber-400 text-slate-950 font-sora font-black text-lg flex items-center justify-center shadow-xs shrink-0">
                                        1
                                    </div>
                                @endif

                                <div class="flex items-center gap-2.5 min-w-0 flex-wrap">
                                    <h2 class="font-sora font-black text-slate-900 text-base sm:text-xl lg:text-2xl tracking-tight truncate">
                                        {{ $group->name }}
                                    </h2>
                                    @if($showWinnerBadge)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-200/90 text-amber-950 border border-amber-400/60 uppercase tracking-wider shrink-0">
                                            <span>QUAF CHAMPION</span>
                                            <svg class="w-3.5 h-3.5 text-amber-800" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Points -->
                            <div class="flex items-baseline gap-1.5 shrink-0 text-right">
                                <span class="font-sora font-black text-2xl sm:text-3xl lg:text-4xl text-amber-600 leading-none">
                                    {{ number_format($group->public_points_cache ?? 0) }}
                                </span>
                                <span class="text-xs sm:text-sm font-mono text-slate-500 uppercase tracking-widest font-semibold">
                                    PTS
                                </span>
                            </div>
                        </div>
                    @elseif($rank === 2)
                        <!-- Rank 2 Row (Silver Theme) -->
                        <div class="rounded-2xl p-4 sm:p-5 bg-gradient-to-r from-slate-100/90 via-slate-50/60 to-white border border-slate-300 flex items-center justify-between gap-4 shadow-2xs transition-all ring-1 ring-slate-300/40">
                            <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                                <!-- Rank 2 Silver Number Box -->
                                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-slate-200 via-slate-300 to-slate-400 text-slate-900 font-sora font-black text-base sm:text-lg flex items-center justify-center shadow-xs shrink-0 border border-slate-300">
                                    2
                                </div>

                                <div class="flex items-center gap-2.5 min-w-0 flex-wrap">
                                    <h2 class="font-sora font-black text-slate-900 text-base sm:text-xl lg:text-2xl tracking-tight truncate">
                                        {{ $group->name }}
                                    </h2>
                                    @if(!empty($isFinalPublished))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-200 text-slate-800 border border-slate-300 uppercase tracking-wider shrink-0">
                                            <span>1st RUNNER-UP</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Points -->
                            <div class="flex items-baseline gap-1.5 shrink-0 text-right">
                                <span class="font-sora font-black text-2xl sm:text-3xl lg:text-4xl text-slate-700 leading-none">
                                    {{ number_format($group->public_points_cache ?? 0) }}
                                </span>
                                <span class="text-xs sm:text-sm font-mono text-slate-400 uppercase tracking-widest font-semibold">
                                    PTS
                                </span>
                            </div>
                        </div>
                    @elseif($rank === 3)
                        <!-- Rank 3 Row (Bronze Theme - Clean stroke, rich visibility) -->
                        <div class="rounded-2xl p-4 sm:p-5 bg-gradient-to-r from-amber-50/60 via-orange-50/25 to-white border border-amber-300/80 flex items-center justify-between gap-4 shadow-xs transition-all">
                            <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                                <!-- Rank 3 Bronze Number Box -->
                                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl text-white font-sora font-black text-base sm:text-lg flex items-center justify-center shadow-xs shrink-0"
                                     style="background: linear-gradient(135deg, #c26928 0%, #92400e 100%);">
                                    3
                                </div>

                                <div class="flex items-center gap-2.5 min-w-0 flex-wrap">
                                    <h2 class="font-sora font-black text-slate-900 text-base sm:text-xl lg:text-2xl tracking-tight truncate">
                                        {{ $group->name }}
                                    </h2>
                                    @if(!empty($isFinalPublished))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-100 text-amber-900 border border-amber-300 uppercase tracking-wider shrink-0">
                                            <span>2nd RUNNER-UP</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Points -->
                            <div class="flex items-baseline gap-1.5 shrink-0 text-right">
                                <span class="font-sora font-black text-2xl sm:text-3xl lg:text-4xl text-amber-700 leading-none">
                                    {{ number_format($group->public_points_cache ?? 0) }}
                                </span>
                                <span class="text-xs sm:text-sm font-mono text-amber-800/60 uppercase tracking-widest font-semibold">
                                    PTS
                                </span>
                            </div>
                        </div>
                    @else
                        <!-- Subsequent Rows (Rank 4, 5...) in Neutral Theme -->
                        <div class="rounded-2xl p-4 sm:p-5 bg-slate-50/80 border border-slate-200/80 hover:bg-slate-100/70 flex items-center justify-between gap-4 transition-colors">
                            <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                                <!-- Rank Number Box -->
                                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-slate-200 text-slate-700 font-sora font-black text-base sm:text-lg flex items-center justify-center shrink-0">
                                    {{ $rank }}
                                </div>

                                <h2 class="font-sora font-bold text-slate-800 text-base sm:text-xl lg:text-2xl tracking-tight truncate">
                                    {{ $group->name }}
                                </h2>
                            </div>

                            <!-- Points -->
                            <div class="flex items-baseline gap-1.5 shrink-0 text-right">
                                <span class="font-sora font-black text-2xl sm:text-3xl lg:text-4xl text-slate-900 leading-none">
                                    {{ number_format($group->public_points_cache ?? 0) }}
                                </span>
                                <span class="text-xs sm:text-sm font-mono text-slate-400 uppercase tracking-widest font-semibold">
                                    PTS
                                </span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        @if(!empty($standingsPoster))
            <!-- Official Standings Poster Section (View Below on Page) -->
            <div class="mt-10 rounded-3xl bg-white border border-slate-200/90 p-5 sm:p-8 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#be1e2d] text-white uppercase">OFFICIAL POSTER</span>
                            <span class="text-xs font-mono font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">{{ $standingsLabel }}</span>
                        </div>
                        <h2 class="text-xl font-sora font-extrabold text-slate-900 mt-1">Official Standings Poster</h2>
                        <p class="text-xs text-slate-500 font-mono mt-0.5">Official cumulative leaderboard graphic poster</p>
                    </div>

                    <a href="{{ route('groups.download-poster') }}" class="px-5 py-2.5 rounded-2xl bg-[#be1e2d] hover:bg-[#a01824] text-white font-sora font-bold text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2 shadow-md shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>Download Poster</span>
                    </a>
                </div>

                <div class="bg-slate-950/5 rounded-2xl p-2 sm:p-4 border border-slate-200/80 flex justify-center">
                    <a href="{{ $standingsPoster }}" target="_blank" class="block group relative overflow-hidden rounded-xl">
                        <img src="{{ $standingsPoster }}" alt="Official Group Standings Poster - {{ $standingsLabel }}" class="max-w-full h-auto max-h-[850px] rounded-xl shadow-lg group-hover:scale-[1.01] transition-transform duration-300">
                        <div class="absolute inset-0 bg-slate-950/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <span class="px-4 py-2 rounded-xl bg-slate-900/90 text-white font-mono text-xs font-bold shadow-lg">Click to View Full Size</span>
                        </div>
                    </a>
                </div>
            </div>
        @endif
    </div>
</section>

@if(!empty($isFinalPublished))
<script>
function launchConfettiCannon() {
    const canvas = document.getElementById('celebration-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    let width = canvas.width = window.innerWidth;
    let height = canvas.height = window.innerHeight;

    window.addEventListener('resize', () => {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    }, { once: true });

    const colors = [
        '#f59e0b', '#fbbf24', '#fef08a', // Gold
        '#be1e2d', '#ef4444',             // Red
        '#10b981', '#34d399',             // Emerald
        '#6366f1', '#8b5cf6',             // Purple / Indigo
        '#38bdf8', '#0284c7'              // Sky / Blue
    ];

    const particles = [];
    const particleCount = 200;

    for (let i = 0; i < particleCount; i++) {
        const origin = i % 3;
        let x, y, vx, vy;
        if (origin === 0) {
            // Left bottom corner cannon
            x = width * 0.1;
            y = height * 0.95;
            vx = (Math.random() * 9 + 4) * (width > 600 ? 1.6 : 1);
            vy = -(Math.random() * 16 + 11);
        } else if (origin === 1) {
            // Right bottom corner cannon
            x = width * 0.9;
            y = height * 0.95;
            vx = -(Math.random() * 9 + 4) * (width > 600 ? 1.6 : 1);
            vy = -(Math.random() * 16 + 11);
        } else {
            // Top center shower
            x = width * 0.5 + (Math.random() - 0.5) * (width * 0.6);
            y = height * 0.05;
            vx = (Math.random() - 0.5) * 8;
            vy = -(Math.random() * 5 + 1);
        }

        particles.push({
            x: x,
            y: y,
            vx: vx,
            vy: vy,
            size: Math.random() * 9 + 6,
            color: colors[Math.floor(Math.random() * colors.length)],
            rotation: Math.random() * 360,
            rotationSpeed: (Math.random() - 0.5) * 14,
            gravity: 0.28,
            friction: 0.985,
            opacity: 1,
            shape: Math.random() > 0.35 ? 'rect' : 'circle'
        });
    }

    let animationFrame;
    let startTime = Date.now();

    function render() {
        ctx.clearRect(0, 0, width, height);

        let activeCount = 0;
        const elapsed = (Date.now() - startTime) / 1000;

        for (let i = 0; i < particles.length; i++) {
            const p = particles[i];

            p.vx *= p.friction;
            p.vy += p.gravity;
            p.x += p.vx;
            p.y += p.vy;
            p.rotation += p.rotationSpeed;

            if (elapsed > 4.5) {
                p.opacity -= 0.015;
            }

            if (p.y < height + 60 && p.opacity > 0) {
                activeCount++;
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate((p.rotation * Math.PI) / 180);
                ctx.globalAlpha = Math.max(0, p.opacity);
                ctx.fillStyle = p.color;

                if (p.shape === 'rect') {
                    ctx.fillRect(-p.size / 2, -p.size / 4, p.size, p.size / 2);
                } else {
                    ctx.beginPath();
                    ctx.arc(0, 0, p.size / 2, 0, Math.PI * 2);
                    ctx.fill();
                }

                ctx.restore();
            }
        }

        if (activeCount > 0 && elapsed < 9) {
            animationFrame = requestAnimationFrame(render);
        } else {
            ctx.clearRect(0, 0, width, height);
            cancelAnimationFrame(animationFrame);
        }
    }

    render();
}

document.addEventListener('DOMContentLoaded', function () {
    setTimeout(() => {
        launchConfettiCannon();
    }, 350);
});
</script>
@endif

@endsection
