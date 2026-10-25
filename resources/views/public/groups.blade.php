@extends('layouts.public', ['title' => 'Academic Groups & Standings — QUAF'])

@section('content')

<!-- Header Section -->
<section class="py-10 sm:py-14 bg-white border-b border-slate-200">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div>
            <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase animate-subheading">FESTIVAL HOUSES & TEAMS</span>
            <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 mt-1 animate-heading">Group Standings</h1>
            <p class="text-sm text-slate-600 mt-2 max-w-xl">
                Official leaderboard and cumulative point standings of competing festival groups in QUAF.
            </p>
        </div>
    </div>
</section>

<!-- Leaderboard Vertical List matching User Template (Light Theme) -->
<section class="py-10 sm:py-16 bg-slate-50 min-h-[60vh]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
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

                    @if($isWinner)
                        <!-- Rank 1 Row (Light Theme) -->
                        <div class="rounded-2xl p-4 sm:p-5 bg-gradient-to-r from-amber-50/80 via-yellow-50/40 to-white border border-amber-300/80 flex items-center justify-between gap-4 shadow-xs transition-all">
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
                                            <span>WINNER</span>
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
                                    {{ number_format($group->points_cache) }}
                                </span>
                                <span class="text-xs sm:text-sm font-mono text-slate-500 uppercase tracking-widest font-semibold">
                                    PTS
                                </span>
                            </div>
                        </div>
                    @else
                        <!-- Subsequent Rows (Rank 2, 3, 4, 5...) in Light Theme -->
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
                                    {{ number_format($group->points_cache) }}
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
    </div>
</section>

@endsection
