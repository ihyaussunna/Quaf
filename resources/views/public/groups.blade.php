@extends('layouts.public', [
    'title' => 'Collegiate Teams & Academic Houses — QUAF 2026 | Markaz Art Fest',
    'metaDescription' => 'The 5 official collegiate teams of QUAF 2026: Lumo Fikric, Pacto Hikmic, Conco Majdic, Yugo Rushdic, and Unio Hilmic at Jamia Markaz.',
])

@section('content')

@if(!empty($isDemo))
    <!-- Demo Mode Ribbon -->
    <div class="bg-gradient-to-r from-purple-950 via-indigo-950 to-slate-950 text-white px-4 sm:px-6 py-3 shadow-lg border-b border-indigo-500/40 sticky top-16 z-40">
        <div class="max-w-5xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs font-sora">
            <div class="flex items-center gap-2.5">
                <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 font-mono font-black text-[10px] uppercase tracking-wider animate-pulse shrink-0">
                    DEMO PREVIEW
                </span>
                <span class="font-bold text-amber-300">
                    ഫൈനൽ ചാമ്പ്യൻഷിപ്പ് &amp; വിക്ടറി സെലിബ്രേഷൻ ഡെമോ (White Theme)
                </span>
                <span class="text-slate-300 text-[11px] hidden md:inline">
                    (മീഡിയ പാനലിൽ ഫൈനൽ പബ്ലിഷ് ചെയ്യുമ്പോൾ വെബ്സൈറ്റിൽ ഇപ്രകാരമാണ് കാണപ്പെടുക)
                </span>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="launchCelebrationSequence();" class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-300 hover:to-yellow-300 text-slate-950 font-mono font-bold text-[11px] uppercase tracking-wider transition shadow cursor-pointer flex items-center gap-1.5 active:scale-95">
                    <svg class="w-3.5 h-3.5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    <span>Replay Celebration</span>
                </button>
                <a href="{{ route('media.standings.index') }}" class="px-3 py-1.5 rounded-xl bg-slate-800/90 hover:bg-slate-700 text-slate-200 border border-slate-600 font-mono font-medium text-[11px] uppercase tracking-wider transition">
                    &larr; Media Panel
                </a>
                <a href="{{ route('groups.index') }}" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-mono font-medium text-[11px] uppercase tracking-wider transition">
                    Live View
                </a>
            </div>
        </div>
    </div>
@endif

<!-- Header Section (White Theme) -->
<section class="py-10 sm:py-14 bg-white border-b border-slate-200">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(!empty($isFinalPublished))
            <!-- Final Championship Header matching Model in White Theme -->
            <div class="text-center max-w-3xl mx-auto space-y-3">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gradient-to-r from-amber-500/15 via-yellow-500/20 to-amber-500/15 border border-amber-400/60 shadow-xs text-amber-700 font-mono text-xs font-black uppercase tracking-widest"
                     style="animation: slideDownFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: 0.1s;">
                    <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span>FINAL OFFICIAL RESULT</span>
                    <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-sora font-black text-slate-900 tracking-tight"
                    style="animation: slideDownFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: 0.3s;">
                    Final Team Standings
                </h1>

                <div>
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-300/80 font-mono font-bold text-xs shadow-2xs"
                         style="animation: slideDownFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: 0.5s;">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>All {{ $totalPrograms > 0 ? $totalPrograms : 143 }} Results Declared</span>
                    </div>
                </div>
            </div>
        @else
            <!-- Regular Festival Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase animate-subheading">FESTIVAL TEAMS</span>
                    <div class="flex flex-wrap items-center gap-3 mt-1">
                        <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 animate-heading">Team Standings</h1>
                        @php
                            $standingsCount = (int) \App\Models\FestivalSetting::get('public_standings_results_count', 0);
                            $standingsLabel = $standingsLabel ?? ('After ' . sprintf('%02d', $standingsCount) . ' Results');
                            $standingsPoster = $standingsPosterUrl ?? \App\Models\FestivalSetting::get('public_standings_poster_url');
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-emerald-50 text-emerald-800 border border-emerald-300 shadow-2xs">
                            {{ $standingsLabel }}
                        </span>
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
        @endif
    </div>
</section>

<!-- Leaderboard & Podium Section (White Theme) -->
<section class="py-10 sm:py-16 bg-slate-50 min-h-[60vh] relative">
    @if(!empty($isFinalPublished))
        <!-- Celebration Canvas for Confetti Fireworks -->
        <canvas id="celebration-canvas" class="fixed inset-0 pointer-events-none z-50 w-full h-full"></canvas>

        <!-- Floating Action Button for Celebration matching user screenshot model -->
        <div class="fixed bottom-6 right-6 z-40">
            <button type="button" onclick="launchCelebrationSequence();" class="px-5 py-3 rounded-full bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 hover:from-amber-300 hover:to-yellow-300 text-slate-950 font-sora font-black text-xs uppercase tracking-wider transition-all shadow-xl hover:shadow-2xl hover:scale-105 active:scale-95 flex items-center gap-2 border-2 border-white cursor-pointer">
                <svg class="w-4 h-4 text-slate-950 animate-bounce" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                <span>CELEBRATE</span>
            </button>
        </div>
    @endif

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(!empty($isFinalPublished) && $groups->isNotEmpty())
            <!-- 3-Podium Cards Section matching user screenshot model in White Theme -->
            @php
                $championGroup = $groups->first();
                $runnerUpGroup = $groups->skip(1)->first();
                $secondRunnerUpGroup = $groups->skip(2)->first();
            @endphp
            <div class="mb-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6 items-end">
                    
                    <!-- Second Place / Runner Up (Left Card on md, order-2 on mobile) -->
                    @if($runnerUpGroup)
                    <div class="order-2 md:order-1 rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-7 shadow-sm text-center flex flex-col items-center justify-between transition-all hover:shadow-md"
                         style="animation: cascadeReveal 0.7s cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: 0.8s;">
                        <div class="flex flex-col items-center w-full">
                            <!-- Silver Medal Box -->
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-slate-100 via-slate-200 to-slate-300 border border-slate-300/80 text-slate-800 flex items-center justify-center shadow-xs">
                                <svg class="w-8 h-8 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="14" r="5" stroke-width="2"/>
                                    <path stroke-width="2" stroke-linecap="round" d="M8.5 12L7 3h10l-1.5 9"/>
                                    <circle cx="12" cy="14" r="2" fill="currentColor"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-mono font-bold tracking-wider text-slate-500 uppercase mt-3.5">
                                SECOND PLACE &bull; RUNNER UP
                            </span>
                            <h3 class="font-sora font-black text-xl sm:text-2xl text-slate-900 mt-1 truncate max-w-full">
                                {{ $runnerUpGroup->name }}
                            </h3>
                        </div>
                        <div class="w-full px-5 py-2.5 rounded-2xl bg-slate-100 border border-slate-200/90 mt-5 flex items-baseline justify-center gap-1.5">
                            <span class="font-sora font-black text-2xl sm:text-3xl text-slate-800 count-up-target" data-target="{{ $runnerUpGroup->public_points_cache ?? 0 }}">{{ number_format($runnerUpGroup->public_points_cache ?? 0) }}</span>
                            <span class="font-mono text-xs font-bold text-slate-500 uppercase tracking-widest">PTS</span>
                        </div>
                    </div>
                    @endif

                    <!-- Rank 1 Overall Champion (Center Card - Taller, Elevated, Golden on md, order-1 on mobile) -->
                    @if($championGroup)
                    <div class="order-1 md:order-2 rounded-3xl bg-gradient-to-b from-amber-50/90 via-white to-amber-50/50 border-2 border-amber-400 p-7 sm:p-9 shadow-xl shadow-amber-500/15 text-center flex flex-col items-center justify-between sm:-mt-6 relative z-10 transition-all hover:shadow-2xl"
                         style="animation: popReveal 0.85s cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: 1.1s;">
                        <!-- Ambient Golden Glow -->
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 w-48 h-48 bg-amber-400/20 rounded-full blur-2xl pointer-events-none"></div>

                        <div class="flex flex-col items-center w-full relative z-10">
                            <!-- Gold Crown Box matching user screenshot model -->
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl bg-gradient-to-br from-amber-400 via-yellow-400 to-amber-500 text-slate-950 flex items-center justify-center shadow-lg shadow-amber-400/40 ring-4 ring-amber-200">
                                <svg class="w-9 h-9 sm:w-11 sm:h-11 text-slate-950" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-mono font-black tracking-widest text-amber-700 uppercase mt-4 flex items-center gap-1.5 justify-center">
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <span>OVERALL CHAMPION</span>
                            </span>
                            <h2 class="font-sora font-black text-2xl sm:text-4xl text-slate-950 mt-1.5 tracking-tight truncate max-w-full">
                                {{ $championGroup->name }}
                            </h2>
                        </div>
                        <div class="w-full px-6 py-3 rounded-2xl bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 text-slate-950 mt-6 flex items-baseline justify-center gap-2 shadow-md shadow-amber-500/20 relative z-10">
                            <span class="font-sora font-black text-3xl sm:text-4xl leading-none count-up-target" data-target="{{ $championGroup->public_points_cache ?? 0 }}">{{ number_format($championGroup->public_points_cache ?? 0) }}</span>
                            <span class="font-mono text-xs sm:text-sm font-black uppercase tracking-wider text-slate-950">POINTS</span>
                        </div>
                    </div>
                    @endif

                    <!-- Third Place (Right Card on md, order-3 on mobile) -->
                    @if($secondRunnerUpGroup)
                    <div class="order-3 md:order-3 rounded-3xl bg-white border border-amber-200/90 p-6 sm:p-7 shadow-sm text-center flex flex-col items-center justify-between transition-all hover:shadow-md"
                         style="animation: cascadeReveal 0.7s cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: 1.0s;">
                        <div class="flex flex-col items-center w-full">
                            <!-- Bronze Medal Box -->
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl text-white flex items-center justify-center shadow-xs"
                                 style="background: linear-gradient(135deg, #c26928 0%, #92400e 100%);">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="14" r="5" stroke-width="2"/>
                                    <path stroke-width="2" stroke-linecap="round" d="M8.5 12L7 3h10l-1.5 9"/>
                                    <circle cx="12" cy="14" r="2" fill="currentColor"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-mono font-bold tracking-wider text-amber-800 uppercase mt-3.5">
                                THIRD PLACE
                            </span>
                            <h3 class="font-sora font-black text-xl sm:text-2xl text-slate-900 mt-1 truncate max-w-full">
                                {{ $secondRunnerUpGroup->name }}
                            </h3>
                        </div>
                        <div class="w-full px-5 py-2.5 rounded-2xl bg-amber-50/80 border border-amber-200 mt-5 flex items-baseline justify-center gap-1.5">
                            <span class="font-sora font-black text-2xl sm:text-3xl text-amber-800 count-up-target" data-target="{{ $secondRunnerUpGroup->public_points_cache ?? 0 }}">{{ number_format($secondRunnerUpGroup->public_points_cache ?? 0) }}</span>
                            <span class="font-mono text-xs font-bold text-amber-700/80 uppercase tracking-widest">PTS</span>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        @endif

        <!-- Outer Light Card matching template -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-5 sm:p-7 shadow-sm">
            <!-- Table Header: RANK & TEAM | TOTAL POINTS -->
            <div class="flex items-center justify-between px-2 mb-4 sm:mb-5"
                 @if(!empty($isFinalPublished)) style="animation: slideDownFade 0.5s cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: 1.4s;" @endif>
                <span class="text-xs font-mono uppercase tracking-widest text-slate-400 font-bold">
                    RANK &amp; TEAM
                </span>
                <span class="text-xs font-mono uppercase tracking-widest text-slate-400 font-bold">
                    TOTAL POINTS
                </span>
            </div>

            <!-- Rows: Sequential Text-by-Text Staggered Animation -->
            <div class="space-y-3 sm:space-y-4">
                @foreach($groups as $group)
                    @php
                        $rank = $loop->iteration;
                        $isWinner = $rank === 1;
                        $showWinnerBadge = $isWinner && (!empty($isFullResultsPublished) && $isFullResultsPublished === true);
                        $rowDelay = 1.4 + ($rank * 0.22);
                        $animStyle = !empty($isFinalPublished) ? "animation: cascadeReveal 0.55s cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: {$rowDelay}s;" : '';
                    @endphp

                    @if($rank === 1)
                        <!-- Rank 1 Row (Gold Theme - Clean stroke like Rank 3 with WINNER badge) -->
                        <div class="rounded-2xl p-4 sm:p-5 bg-gradient-to-r from-amber-50/90 via-yellow-50/40 to-white border border-amber-300/80 flex items-center justify-between gap-4 shadow-xs transition-all"
                             style="{{ $animStyle }} border: 1px solid rgba(251, 191, 36, 0.8);">
                            <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                                @if(!empty($isFinalPublished))
                                    <!-- Trophy Box when Final Published matching Image 2 -->
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
                                    @if(!empty($isFinalPublished))
                                        <!-- WINNER Badge exactly matching user screenshot Image 2 -->
                                        <span class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full text-xs font-mono font-black bg-gradient-to-r from-amber-400 to-yellow-400 text-slate-950 border border-amber-300 shadow-2xs uppercase tracking-wider shrink-0">
                                            <span>WINNER</span>
                                            <svg class="w-3.5 h-3.5 text-slate-950" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Points -->
                            <div class="flex items-baseline gap-1.5 shrink-0 text-right">
                                <span class="font-sora font-black text-2xl sm:text-3xl lg:text-4xl text-amber-600 leading-none count-up-target" data-target="{{ $group->public_points_cache ?? 0 }}">
                                    {{ number_format($group->public_points_cache ?? 0) }}
                                </span>
                                <span class="text-xs sm:text-sm font-mono text-slate-500 uppercase tracking-widest font-semibold">
                                    PTS
                                </span>
                            </div>
                        </div>
                    @elseif($rank === 2)
                        <!-- Rank 2 Row (Silver Theme - Clean stroke like Rank 3) -->
                        <div class="rounded-2xl p-4 sm:p-5 bg-gradient-to-r from-slate-100/90 via-slate-50/60 to-white border border-amber-300/80 flex items-center justify-between gap-4 shadow-xs transition-all"
                             style="{{ $animStyle }} border: 1px solid rgba(251, 191, 36, 0.8);">
                            <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                                <!-- Rank 2 Silver Number Box -->
                                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-slate-200 via-slate-300 to-slate-400 text-slate-900 font-sora font-black text-base sm:text-lg flex items-center justify-center shadow-xs shrink-0">
                                    2
                                </div>

                                <div class="flex items-center gap-2.5 min-w-0 flex-wrap">
                                    <h2 class="font-sora font-black text-slate-900 text-base sm:text-xl lg:text-2xl tracking-tight truncate">
                                        {{ $group->name }}
                                    </h2>
                                </div>
                            </div>

                            <!-- Points -->
                            <div class="flex items-baseline gap-1.5 shrink-0 text-right">
                                <span class="font-sora font-black text-2xl sm:text-3xl lg:text-4xl text-slate-700 leading-none count-up-target" data-target="{{ $group->public_points_cache ?? 0 }}">
                                    {{ number_format($group->public_points_cache ?? 0) }}
                                </span>
                                <span class="text-xs sm:text-sm font-mono text-slate-400 uppercase tracking-widest font-semibold">
                                    PTS
                                </span>
                            </div>
                        </div>
                    @elseif($rank === 3)
                        <!-- Rank 3 Row (Bronze Theme - Clean stroke, rich visibility) -->
                        <div class="rounded-2xl p-4 sm:p-5 bg-gradient-to-r from-amber-50/60 via-orange-50/25 to-white border border-amber-300/80 flex items-center justify-between gap-4 shadow-xs transition-all"
                             style="{{ $animStyle }} border: 1px solid rgba(251, 191, 36, 0.8);">
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
                                </div>
                            </div>

                            <!-- Points -->
                            <div class="flex items-baseline gap-1.5 shrink-0 text-right">
                                <span class="font-sora font-black text-2xl sm:text-3xl lg:text-4xl text-amber-700 leading-none count-up-target" data-target="{{ $group->public_points_cache ?? 0 }}">
                                    {{ number_format($group->public_points_cache ?? 0) }}
                                </span>
                                <span class="text-xs sm:text-sm font-mono text-amber-800/60 uppercase tracking-widest font-semibold">
                                    PTS
                                </span>
                            </div>
                        </div>
                    @else
                        <!-- Subsequent Rows (Rank 4, 5, 6...) in Clean Neutral Theme -->
                        <div class="rounded-2xl p-4 sm:p-5 bg-slate-50/80 border border-slate-200/80 hover:bg-slate-100/70 flex items-center justify-between gap-4 transition-colors"
                             style="{{ $animStyle }}">
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
                                <span class="font-sora font-black text-2xl sm:text-3xl lg:text-4xl text-slate-900 leading-none count-up-target" data-target="{{ $group->public_points_cache ?? 0 }}">
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
<style>
@keyframes slideDownFade {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}
@keyframes cascadeReveal {
    0% {
        opacity: 0;
        transform: translateY(24px) scale(0.97);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
@keyframes popReveal {
    0% {
        opacity: 0;
        transform: scale(0.75);
    }
    70% {
        transform: scale(1.04);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}
</style>

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
    const particleCount = 220;

    for (let i = 0; i < particleCount; i++) {
        const origin = i % 3;
        let x, y, vx, vy;
        if (origin === 0) {
            // Left bottom corner cannon
            x = width * 0.1;
            y = height * 0.95;
            vx = (Math.random() * 10 + 4) * (width > 600 ? 1.6 : 1);
            vy = -(Math.random() * 17 + 12);
        } else if (origin === 1) {
            // Right bottom corner cannon
            x = width * 0.9;
            y = height * 0.95;
            vx = -(Math.random() * 10 + 4) * (width > 600 ? 1.6 : 1);
            vy = -(Math.random() * 17 + 12);
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

function playVictoryFanfare() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        if (ctx.state === 'suspended') {
            ctx.resume();
        }
        const now = ctx.currentTime;
        const notes = [
            { freq: 392.00, start: 0.0, dur: 0.16 }, // G4
            { freq: 523.25, start: 0.16, dur: 0.16 }, // C5
            { freq: 659.25, start: 0.32, dur: 0.20 }, // E5
            { freq: 783.99, start: 0.52, dur: 0.26 }, // G5
            { freq: 1046.50, start: 0.80, dur: 1.10 }, // C6
            { freq: 523.25, start: 0.80, dur: 1.10 },  // C5
            { freq: 659.25, start: 0.80, dur: 1.10 },  // E5
        ];

        notes.forEach(n => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(n.freq, now + n.start);

            gain.gain.setValueAtTime(0.001, now + n.start);
            gain.gain.exponentialRampToValueAtTime(0.22, now + n.start + 0.04);
            gain.gain.exponentialRampToValueAtTime(0.001, now + n.start + n.dur);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start(now + n.start);
            osc.stop(now + n.start + n.dur + 0.05);
        });
    } catch(e) {
        // Ignored if sound gesture policy blocks before interaction
    }
}
window.playVictoryFanfare = playVictoryFanfare;

function animatePointsCounters() {
    const targets = document.querySelectorAll('.count-up-target');
    targets.forEach(el => {
        const finalVal = parseInt(el.getAttribute('data-target')) || 0;
        if (finalVal === 0) return;
        
        let startVal = 0;
        const duration = 1500;
        const startTime = performance.now();

        function step(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease out quart
            const ease = 1 - Math.pow(1 - progress, 4);
            const current = Math.floor(ease * finalVal);
            el.innerText = current.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.innerText = finalVal.toLocaleString();
            }
        }
        requestAnimationFrame(step);
    });
}

function launchCelebrationSequence() {
    launchConfettiCannon();
    if (window.playVictoryFanfare) {
        playVictoryFanfare();
    }
    setTimeout(() => {
        animatePointsCounters();
    }, 400);
}
window.launchCelebrationSequence = launchCelebrationSequence;

document.addEventListener('DOMContentLoaded', function () {
    // Stage-timed confetti burst matching the Champion podium card reveal
    setTimeout(() => {
        launchCelebrationSequence();
    }, 1100);
});
</script>
@endif

@endsection
