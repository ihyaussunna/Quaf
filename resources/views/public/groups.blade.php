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

<!-- Leaderboard Vertical List -->
<section class="py-10 sm:py-16 bg-slate-50 min-h-[60vh]">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-4 sm:space-y-5">
            @foreach($groups as $group)
                @php
                    $rank = $group->rank_cache ?: $loop->iteration;
                    $isFirst = ($rank === 1 || $loop->iteration === 1);
                    $isSecond = ($rank === 2 || $loop->iteration === 2);
                    $isThird = ($rank === 3 || $loop->iteration === 3);
                @endphp

                @if($isFirst)
                    <!-- #1 GOLD RANK ROW -->
                    <div class="relative rounded-2xl sm:rounded-3xl p-5 sm:p-6 bg-gradient-to-r from-amber-50 via-yellow-50/60 to-white border-2 border-amber-300/90 shadow-lg shadow-amber-500/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4 overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5">
                        <div class="absolute left-0 top-0 bottom-0 w-2.5 bg-gradient-to-b from-amber-400 via-yellow-400 to-amber-500"></div>
                        <div class="absolute -right-12 -top-12 w-32 h-32 rounded-full bg-amber-300/20 blur-2xl pointer-events-none"></div>

                        <div class="flex items-center gap-4 sm:gap-6 pl-1 sm:pl-2">
                            <!-- Gold Rank Badge -->
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-amber-300 via-yellow-400 to-amber-500 text-slate-950 flex flex-col items-center justify-center font-black shadow-md shadow-amber-400/40 shrink-0">
                                <span class="text-[9px] font-mono tracking-widest uppercase opacity-80 leading-none">RANK</span>
                                <span class="text-xl sm:text-2xl font-sora leading-tight font-black">1</span>
                            </div>

                            <!-- Group Info -->
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-3 h-3 rounded-full shadow-2xs" style="background-color: {{ $group->color_hex }}"></span>
                                    <span class="font-mono text-xs text-amber-800 font-bold uppercase tracking-wider">{{ $group->code }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-200/80 text-amber-900 border border-amber-300 uppercase">
                                        GOLD LEADER
                                    </span>
                                </div>
                                <h2 class="text-xl sm:text-2xl lg:text-3xl font-sora font-black text-slate-900 tracking-tight">
                                    {{ $group->name }}
                                </h2>
                            </div>
                        </div>

                        <!-- Points Counter -->
                        <div class="text-left sm:text-right pl-16 sm:pl-0 shrink-0">
                            <div class="text-3xl sm:text-4xl lg:text-5xl font-rockwell font-black tracking-tight text-amber-600">
                                {{ number_format($group->points_cache) }}
                                <span class="text-xs sm:text-sm font-mono text-amber-700 font-bold uppercase ml-0.5">PTS</span>
                            </div>
                        </div>
                    </div>

                @elseif($isSecond)
                    <!-- #2 SILVER RANK ROW -->
                    <div class="relative rounded-2xl sm:rounded-3xl p-5 sm:p-6 bg-gradient-to-r from-slate-100 via-slate-50 to-white border-2 border-slate-300 shadow-md shadow-slate-400/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4 overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
                        <div class="absolute left-0 top-0 bottom-0 w-2.5 bg-gradient-to-b from-slate-300 via-slate-400 to-slate-500"></div>

                        <div class="flex items-center gap-4 sm:gap-6 pl-1 sm:pl-2">
                            <!-- Silver Rank Badge -->
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-slate-200 via-slate-300 to-slate-400 text-slate-900 flex flex-col items-center justify-center font-black shadow-md shadow-slate-400/30 shrink-0">
                                <span class="text-[9px] font-mono tracking-widest uppercase opacity-75 leading-none">RANK</span>
                                <span class="text-xl sm:text-2xl font-sora leading-tight font-black">2</span>
                            </div>

                            <!-- Group Info -->
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-3 h-3 rounded-full shadow-2xs" style="background-color: {{ $group->color_hex }}"></span>
                                    <span class="font-mono text-xs text-slate-600 font-bold uppercase tracking-wider">{{ $group->code }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-200 text-slate-700 border border-slate-300 uppercase">
                                        SILVER RUNNER-UP
                                    </span>
                                </div>
                                <h2 class="text-xl sm:text-2xl lg:text-3xl font-sora font-black text-slate-900 tracking-tight">
                                    {{ $group->name }}
                                </h2>
                            </div>
                        </div>

                        <!-- Points Counter -->
                        <div class="text-left sm:text-right pl-16 sm:pl-0 shrink-0">
                            <div class="text-3xl sm:text-4xl lg:text-5xl font-rockwell font-black tracking-tight text-slate-700">
                                {{ number_format($group->points_cache) }}
                                <span class="text-xs sm:text-sm font-mono text-slate-500 font-bold uppercase ml-0.5">PTS</span>
                            </div>
                        </div>
                    </div>

                @elseif($isThird)
                    <!-- #3 BRONZE RANK ROW -->
                    <div class="relative rounded-2xl sm:rounded-3xl p-5 sm:p-6 bg-gradient-to-r from-orange-50/70 via-amber-50/40 to-white border-2 border-orange-200/90 shadow-md shadow-orange-900/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
                        <div class="absolute left-0 top-0 bottom-0 w-2.5 bg-gradient-to-b from-amber-600 via-orange-600 to-amber-700"></div>

                        <div class="flex items-center gap-4 sm:gap-6 pl-1 sm:pl-2">
                            <!-- Bronze Rank Badge -->
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-amber-600 via-orange-600 to-amber-700 text-white flex flex-col items-center justify-center font-black shadow-md shadow-orange-700/30 shrink-0">
                                <span class="text-[9px] font-mono tracking-widest uppercase opacity-85 leading-none">RANK</span>
                                <span class="text-xl sm:text-2xl font-sora leading-tight font-black">3</span>
                            </div>

                            <!-- Group Info -->
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-3 h-3 rounded-full shadow-2xs" style="background-color: {{ $group->color_hex }}"></span>
                                    <span class="font-mono text-xs text-orange-900 font-bold uppercase tracking-wider">{{ $group->code }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-orange-100 text-orange-800 border border-orange-200 uppercase">
                                        BRONZE
                                    </span>
                                </div>
                                <h2 class="text-xl sm:text-2xl lg:text-3xl font-sora font-black text-slate-900 tracking-tight">
                                    {{ $group->name }}
                                </h2>
                            </div>
                        </div>

                        <!-- Points Counter -->
                        <div class="text-left sm:text-right pl-16 sm:pl-0 shrink-0">
                            <div class="text-3xl sm:text-4xl lg:text-5xl font-rockwell font-black tracking-tight text-orange-800">
                                {{ number_format($group->points_cache) }}
                                <span class="text-xs sm:text-sm font-mono text-orange-700 font-bold uppercase ml-0.5">PTS</span>
                            </div>
                        </div>
                    </div>

                @else
                    <!-- #4+ STANDARD RANK ROW -->
                    <div class="relative rounded-2xl sm:rounded-3xl p-5 sm:p-6 bg-white border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4 overflow-hidden transition-all duration-300 hover:shadow-md hover:border-slate-300 hover:-translate-y-0.5">
                        <div class="absolute left-0 top-0 bottom-0 w-2" style="background-color: {{ $group->color_hex }}"></div>

                        <div class="flex items-center gap-4 sm:gap-6 pl-1 sm:pl-2">
                            <!-- Rank Number -->
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-slate-100 text-slate-700 flex flex-col items-center justify-center font-black shrink-0 border border-slate-200">
                                <span class="text-[9px] font-mono tracking-widest uppercase text-slate-400 leading-none">RANK</span>
                                <span class="text-xl sm:text-2xl font-sora leading-tight font-black">{{ $rank }}</span>
                            </div>

                            <!-- Group Info -->
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-3 h-3 rounded-full shadow-2xs" style="background-color: {{ $group->color_hex }}"></span>
                                    <span class="font-mono text-xs text-slate-500 font-bold uppercase tracking-wider">{{ $group->code }}</span>
                                </div>
                                <h2 class="text-xl sm:text-2xl font-sora font-black text-slate-900 tracking-tight">
                                    {{ $group->name }}
                                </h2>
                            </div>
                        </div>

                        <!-- Points Counter -->
                        <div class="text-left sm:text-right pl-16 sm:pl-0 shrink-0">
                            <div class="text-3xl sm:text-4xl font-rockwell font-black tracking-tight text-slate-800">
                                {{ number_format($group->points_cache) }}
                                <span class="text-xs sm:text-sm font-mono text-slate-400 font-bold uppercase ml-0.5">PTS</span>
                            </div>
                        </div>
                    </div>
                @endif

            @endforeach
        </div>
    </div>
</section>

@endsection
