@extends('layouts.public', ['title' => 'QUAF | Ihyaussunna Students Union'])

@section('content')

<!-- Live Fest Banner & Result Ticker (When in Live Fest Mode) -->
@if($liveFestMode)
    <div class="bg-white border-b border-amber-200 py-2 sm:py-2.5 overflow-hidden relative shadow-xs">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 flex items-center">
            <div class="flex items-center gap-1.5 sm:gap-2 pr-3 sm:pr-6 border-r border-slate-200 z-10 bg-white">
                <span class="relative flex h-2 w-2 sm:h-2.5 sm:w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 sm:h-2.5 sm:w-2.5 bg-red-500"></span>
                </span>
                <span class="text-[10px] sm:text-xs font-mono font-bold tracking-wider text-[#f3bd2e] uppercase whitespace-nowrap">LIVE FEST</span>
            </div>

            <!-- Ticker Content -->
            <div class="overflow-hidden flex-1 pl-3 sm:pl-6">
                <div class="animate-marquee flex items-center gap-8 sm:gap-12 text-[11px] sm:text-xs font-mono">
                    @forelse($latestResults as $res)
                        <div class="flex items-center gap-1.5 sm:gap-2 whitespace-nowrap">
                            <span class="text-slate-500 font-medium">[{{ $res->program->code }} {{ $res->program->name }}]:</span>
                            <span class="text-[#f3bd2e] font-bold">1st {{ $res->firstEntry?->student?->name ?? 'Team ' . $res->firstEntry?->group?->name }} ({{ $res->firstEntry?->group?->code }})</span>
                            <span class="text-slate-300">•</span>
                            <span class="text-slate-700">2nd {{ $res->secondEntry?->student?->name ?? 'Team ' . $res->secondEntry?->group?->name }}</span>
                        </div>
                    @empty
                        <span class="text-slate-600">Welcome to QUAF — Live program scores and stage calls are active.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Hero Section (Light Theme & Mobile Optimized) -->
<section class="relative min-h-[75vh] sm:min-h-[85vh] flex items-center justify-center overflow-hidden bg-slate-50 pt-8 sm:pt-12 pb-14 sm:pb-20">
    <!-- Atmospheric Ambient Glows -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[320px] sm:w-[600px] h-[250px] sm:h-[350px] bg-amber-400/10 rounded-full blur-[100px] sm:blur-[140px] pointer-events-none"></div>
    <div class="absolute bottom-10 left-10 w-64 sm:w-96 h-64 sm:h-96 bg-emerald-500/5 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute top-10 right-10 w-64 sm:w-96 h-64 sm:h-96 bg-blue-500/5 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
        <!-- Flex Container: Logo on one side, Description on the other side -->
        <div class="flex flex-col lg:flex-row items-center justify-between gap-8 lg:gap-14">
            
            <!-- Side 1: Title Logo (Prominent, High Quality, Fully Responsive) -->
            <div class="w-full lg:w-1/2 flex items-center justify-center lg:justify-start">
                <div class="relative group">
                    <img src="{{ asset('images/quaf-title-logo.png') }}" 
                         alt="QUAF" 
                         class="w-full max-w-[280px] xs:max-w-[340px] sm:max-w-md md:max-w-lg lg:max-w-xl h-auto object-contain drop-shadow-sm transition-transform duration-300 group-hover:scale-[1.02]">
                </div>
            </div>

            <!-- Side 2: Description, Dates & Action Buttons (Side of Logo) -->
            <div class="w-full lg:w-1/2 text-center lg:text-left space-y-5 sm:space-y-6">
                <!-- Institution Tag -->
                <div class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-1 sm:py-1.5 rounded-full bg-white border border-slate-200/80 text-slate-700 text-[10px] sm:text-xs tracking-wider uppercase shadow-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#f3bd2e]"></span>
                    <span class="font-semibold truncate max-w-[280px] sm:max-w-none">Ihyaussunna Students Union • Markaz</span>
                </div>

                <!-- Description (Placed to the side of the logo) -->
                <p class="text-sm sm:text-base md:text-lg text-slate-600 font-normal leading-relaxed max-w-xl">
                    The grand confluence of eloquence, arts, and intellectual heritage. Uniting premier academic groups across 120+ cultural and literary disciplines.
                </p>

                <!-- Date & Location Badge -->
                <div class="inline-flex flex-col sm:flex-row items-center lg:items-start justify-center lg:justify-start gap-2 sm:gap-4 text-[11px] sm:text-xs font-mono text-slate-700 px-4 py-2.5 sm:px-5 sm:py-3 rounded-2xl bg-white border border-slate-200 shadow-xs">
                    <div class="flex items-center gap-2 font-semibold">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#f3bd2e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>OCTOBER 24 – 28, 2026</span>
                    </div>
                    <span class="text-slate-300 hidden sm:inline">|</span>
                    <div class="flex items-center gap-2 font-semibold">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#f3bd2e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>CENTRAL FESTIVAL ARENA, MARKAZ</span>
                    </div>
                </div>

                <!-- Action CTAs -->
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-2">
                    <a href="{{ route('results.index') }}" class="w-full sm:w-auto px-6 sm:px-8 py-3.5 sm:py-4 rounded-xl font-bold text-xs sm:text-sm uppercase tracking-wider bg-gradient-to-r from-[#f3bd2e] to-[#be1e2d] text-white hover:brightness-105 shadow-md shadow-[#f3bd2e]/20 transition-all transform hover:-translate-y-0.5 text-center">
                        View Live Results
                    </a>
                    <a href="#groups" class="w-full sm:w-auto px-6 sm:px-8 py-3.5 sm:py-4 rounded-xl font-semibold text-xs sm:text-sm tracking-wider bg-white border border-slate-300 hover:border-[#f3bd2e] text-slate-800 transition-all transform hover:-translate-y-0.5 shadow-xs text-center">
                        Explore Groups & Points
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Interactive Groups Standings Section (Light Theme) -->
<section id="groups" class="py-12 sm:py-16 lg:py-24 border-t border-slate-200 bg-white relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 sm:mb-12 lg:mb-16 gap-4 sm:gap-6">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#f3bd2e] uppercase">ACADEMIC GROUPS</span>
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1 sm:mt-2">Festival Standings</h2>
            </div>
            <p class="text-xs sm:text-sm text-slate-600 max-w-md">
                Points are calculated dynamically upon official result verification. Hover or tap each group to inspect leadership and points.
            </p>
        </div>

        <!-- Visual Interactive Group Blocks (No Profile Pictures) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-6">
            @foreach($groups as $group)
                <div class="group relative rounded-2xl bg-white border-2 border-slate-200/80 hover:border-slate-300 p-5 sm:p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg overflow-hidden flex flex-col justify-between"
                     style="border-top-color: {{ $group->color_hex }}; border-top-width: 4px;">
                    <!-- Ambient Group Highlight on Hover -->
                    <div class="absolute -right-16 -top-16 w-36 h-36 rounded-full opacity-10 group-hover:opacity-20 blur-2xl transition-opacity duration-500"
                         style="background-color: {{ $group->color_hex }}"></div>

                    <div>
                        <!-- Header with Rank Badge & Code -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full shadow-xs" style="background-color: {{ $group->color_hex }}"></span>
                                <span class="font-mono text-xs text-slate-700 font-bold uppercase tracking-wider">{{ $group->code }}</span>
                            </div>
                            <div class="px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-slate-100 border border-slate-200 text-slate-800">
                                #{{ $group->rank_cache ?: $loop->iteration }}
                            </div>
                        </div>

                        <!-- Name & Leadership (No Profile Picture) -->
                        <h3 class="text-xl font-sora font-black text-slate-900 mb-1.5 group-hover:text-[#be1e2d] transition-colors">
                            {{ $group->name }}
                        </h3>
                        <p class="text-xs text-slate-600 mb-4 flex items-center gap-1.5 truncate">
                            <svg class="w-3.5 h-3.5 opacity-60 shrink-0 text-[#005c94]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="truncate">Leader: {{ $group->manager_name ?: ($group->leader?->name ?? 'Group Captain') }}</span>
                        </p>
                    </div>

                    <!-- Points Metric -->
                    <div class="pt-4 border-t border-slate-100 flex items-baseline justify-between mt-auto">
                        <span class="text-xs font-mono text-slate-500 uppercase font-semibold">Points</span>
                        <div class="text-3xl font-sora font-black tracking-tight" style="color: {{ $group->color_hex }}">
                            {{ number_format($group->points_cache) }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Official Festival Zones Section -->
<section id="zones" class="py-12 sm:py-16 lg:py-24 border-t border-slate-200 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 sm:mb-12 lg:mb-16 gap-4 sm:gap-6">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#f3bd2e] uppercase">ACADEMIC DIVISIONS</span>
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1 sm:mt-2">Festival Zones</h2>
            </div>
            <p class="text-xs sm:text-sm text-slate-600 max-w-md">
                Competitions and student enrolments categorized under 4 designated zones according to academic year and class levels.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($zones as $key => $zoneItem)
                <div class="rounded-2xl bg-white border-2 border-slate-200/90 p-5 sm:p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-md flex flex-col justify-between"
                     style="border-top-color: {{ $zoneItem['color'] }}; border-top-width: 4px;">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-mono text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800">
                                {{ $zoneItem['name'] }}
                            </span>
                            <span class="w-3 h-3 rounded-full" style="background-color: {{ $zoneItem['color'] }}"></span>
                        </div>
                        <h3 class="text-xl font-sora font-bold text-slate-900 mb-1">{{ $zoneItem['name'] }}</h3>
                        <p class="text-sm font-semibold text-slate-700 font-sora mb-1">{{ $zoneItem['sub'] }}</p>
                        <p class="text-xs text-slate-500 mb-4 sm:mb-6 font-mono">Classes: {{ $zoneItem['classes'] }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-[10px] font-mono uppercase text-slate-400 font-semibold block">Programs</span>
                            <span class="font-black text-slate-900 text-base">{{ $zoneItem['programs_count'] }}</span>
                        </div>
                        <div class="text-right">
                            <a href="{{ route('results.index', ['zone' => $key]) }}" class="text-xs font-semibold text-[#f3bd2e] hover:underline">
                                View Results →
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Festival Stages & Live Status Grid (Light Theme) -->
<section class="py-12 sm:py-16 lg:py-24 border-t border-slate-200 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 sm:mb-12 lg:mb-16 gap-4 sm:gap-6">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#f3bd2e] uppercase">FESTIVAL VENUES</span>
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1 sm:mt-2">Active Stages</h2>
            </div>
            <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs font-mono text-slate-600">
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Intermission</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-400"></span> Closed</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($stages as $stage)
                <div class="rounded-2xl bg-white border border-slate-200/90 p-5 sm:p-6 relative overflow-hidden flex flex-col justify-between shadow-xs hover:shadow-md transition-shadow">
                    <div>
                        <!-- Stage Header -->
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <span class="font-mono text-xs font-bold text-[#f3bd2e] uppercase">{{ $stage->code }}</span>
                            @if($stage->status === 'active')
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold">LIVE NOW</span>
                            @elseif($stage->status === 'break')
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200 font-bold">BREAK</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase tracking-wider bg-slate-100 text-slate-600 font-bold">CLOSED</span>
                            @endif
                        </div>

                        <h3 class="text-base sm:text-lg font-sora font-bold text-slate-900 mb-1">{{ $stage->name }}</h3>
                        <p class="text-xs text-slate-500 mb-4 sm:mb-6 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 opacity-60 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            <span class="truncate">{{ $stage->location ?? 'Festival Grounds' }}</span>
                        </p>

                        <!-- Current Program -->
                        <div class="bg-slate-50 rounded-xl p-3 sm:p-3.5 border border-slate-200 mb-2.5 sm:mb-3">
                            <span class="text-[10px] font-mono text-slate-500 uppercase tracking-wider block mb-0.5 font-semibold">CURRENT PROGRAM</span>
                            <div class="font-bold text-xs sm:text-sm text-slate-900 truncate">
                                {{ $stage->currentProgram?->name ?? 'Stage Intermission' }}
                            </div>
                            @if($stage->currentProgram)
                                <span class="text-[11px] text-[#f3bd2e] block mt-0.5 font-semibold truncate">{{ $stage->currentProgram->eligibility ?? 'A Zone' }}</span>
                            @endif
                        </div>

                        <!-- Next Program -->
                        <div class="bg-white rounded-xl p-2.5 sm:p-3 border border-slate-200">
                            <span class="text-[10px] font-mono text-slate-500 uppercase tracking-wider block mb-0.5 font-semibold">NEXT UP</span>
                            <div class="text-xs text-slate-700 truncate font-medium">
                                {{ $stage->nextProgram?->name ?? 'To be scheduled' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 pt-3 sm:pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-mono">
                        <span class="text-[11px] sm:text-xs">Capacity: {{ $stage->capacity }} seats</span>
                        <a href="{{ route('results.index', ['stage' => $stage->id]) }}" class="text-[#f3bd2e] font-semibold hover:underline">Results →</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Latest Official Results (Podium Cards in Light Theme) -->
<section class="py-12 sm:py-16 lg:py-24 border-t border-slate-200 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 sm:mb-12 lg:mb-16 gap-4 sm:gap-6">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#f3bd2e] uppercase">VERIFIED VERDICTS</span>
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1 sm:mt-2">Latest Results</h2>
            </div>
            <a href="{{ route('results.index') }}" class="text-xs sm:text-sm font-semibold text-[#f3bd2e] hover:underline flex items-center gap-1">
                <span>View Complete Results Archive</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @forelse($latestResults as $result)
                <div class="rounded-2xl bg-white border border-slate-200 hover:border-[#f3bd2e]/40 p-5 sm:p-6 transition-all duration-300 flex flex-col justify-between shadow-xs hover:shadow-md">
                    <div>
                        <!-- Header -->
                        <div class="flex items-center justify-between text-xs font-mono text-slate-500 mb-2 sm:mb-3">
                            <span class="text-[#f3bd2e] font-bold">{{ $result->program->code }}</span>
                            <span>{{ $result->published_at?->diffForHumans() ?? 'Just now' }}</span>
                        </div>
                        <h3 class="text-lg sm:text-xl font-sora font-bold text-slate-900 mb-1 leading-snug">
                            {{ $result->program->name }}
                        </h3>
                        <span class="text-xs text-slate-500 block mb-4 sm:mb-6 font-medium">{{ $result->program->eligibility ?? 'A Zone' }}</span>

                        <!-- Placements -->
                        <div class="space-y-2.5 sm:space-y-3">
                            <!-- 1st Place -->
                            <div class="flex items-center justify-between p-2.5 sm:p-3 rounded-xl bg-amber-50/70 border border-amber-300/80 gap-2">
                                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                    <span class="w-6 h-6 rounded-full bg-[#f3bd2e] text-white font-black text-xs flex items-center justify-center font-sora shadow-xs shrink-0">1</span>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-xs sm:text-sm text-slate-900 truncate">
                                            {{ $result->firstEntry?->student?->name ?? 'Team ' . $result->firstEntry?->group?->name }}
                                        </div>
                                        <span class="text-[10px] sm:text-[11px] text-slate-500 font-mono">Chest #{{ $result->firstEntry?->chest_number }}</span>
                                    </div>
                                </div>
                                <span class="text-[11px] sm:text-xs font-mono font-bold px-2 py-0.5 rounded bg-white text-[#f3bd2e] border border-amber-200 shrink-0">
                                    {{ $result->firstEntry?->group?->code }}
                                </span>
                            </div>

                            <!-- 2nd Place -->
                            @if($result->secondEntry)
                                <div class="flex items-center justify-between p-2 sm:p-2.5 rounded-xl bg-slate-50 border border-slate-200 gap-2">
                                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                        <span class="w-6 h-6 rounded-full bg-slate-400 text-white font-black text-xs flex items-center justify-center font-sora shrink-0">2</span>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-xs sm:text-sm text-slate-800 truncate">
                                                {{ $result->secondEntry?->student?->name ?? 'Team ' . $result->secondEntry?->group?->name }}
                                            </div>
                                            <span class="text-[10px] sm:text-[11px] text-slate-500 font-mono">Chest #{{ $result->secondEntry?->chest_number }}</span>
                                        </div>
                                    </div>
                                    <span class="text-[11px] sm:text-xs font-mono font-bold px-2 py-0.5 rounded bg-white text-slate-700 border border-slate-200 shrink-0">
                                        {{ $result->secondEntry?->group?->code }}
                                    </span>
                                </div>
                            @endif

                            <!-- 3rd Place -->
                            @if($result->thirdEntry)
                                <div class="flex items-center justify-between p-2 sm:p-2.5 rounded-xl bg-amber-50/40 border border-amber-200/60 gap-2">
                                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                        <span class="w-6 h-6 rounded-full bg-amber-700 text-white font-black text-xs flex items-center justify-center font-sora shrink-0">3</span>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-xs sm:text-sm text-slate-800 truncate">
                                                {{ $result->thirdEntry?->student?->name ?? 'Team ' . $result->thirdEntry?->group?->name }}
                                            </div>
                                            <span class="text-[10px] sm:text-[11px] text-slate-500 font-mono">Chest #{{ $result->thirdEntry?->chest_number }}</span>
                                        </div>
                                    </div>
                                    <span class="text-[11px] sm:text-xs font-mono font-bold px-2 py-0.5 rounded bg-white text-slate-700 border border-slate-200 shrink-0">
                                        {{ $result->thirdEntry?->group?->code }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 pt-3 sm:pt-4 border-t border-slate-100 text-right">
                        <a href="{{ route('results.show', $result->program->id) }}" class="text-xs font-semibold text-[#f3bd2e] hover:underline">
                            View Full Score Sheet →
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12 sm:py-16 text-slate-500 font-mono text-sm">
                    No results have been officially published yet. Stay tuned as jury deliberations conclude.
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Editorial News & Journal Section (Light Theme) -->
@if($latestNews->isNotEmpty())
<section class="py-12 sm:py-16 lg:py-24 border-t border-slate-200 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 sm:mb-12 lg:mb-16 gap-4 sm:gap-6">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#f3bd2e] uppercase">FESTIVAL JOURNAL</span>
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1 sm:mt-2">Latest News & Dispatches</h2>
            </div>
            <a href="{{ route('news.index') }}" class="text-xs sm:text-sm font-semibold text-[#f3bd2e] hover:underline flex items-center gap-1">
                <span>View All Articles</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 sm:gap-8">
            @foreach($latestNews as $article)
                <a href="{{ route('news.show', $article->slug) }}" class="group flex flex-col rounded-2xl bg-white border border-slate-200 hover:border-[#f3bd2e]/40 overflow-hidden transition-all duration-300 shadow-xs hover:shadow-md">
                    <div class="aspect-video w-full overflow-hidden bg-slate-100 relative">
                        @if($article->cover_image)
                            <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        @endif
                        <div class="absolute top-3 left-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-mono uppercase tracking-wider bg-white/90 backdrop-blur-md text-[#f3bd2e] border border-amber-200 font-bold shadow-xs">
                                {{ $article->category }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <span class="text-xs font-mono text-slate-400 block mb-2">{{ $article->published_at?->format('M d, Y') }}</span>
                            <h3 class="text-lg sm:text-xl font-sora font-bold text-slate-900 group-hover:text-[#f3bd2e] transition-colors leading-snug mb-2 sm:mb-3">
                                {{ $article->title }}
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 font-normal line-clamp-2 leading-relaxed">
                                {{ $article->excerpt }}
                            </p>
                        </div>
                        <span class="text-xs font-semibold text-[#f3bd2e] mt-4 sm:mt-6 flex items-center gap-1">
                            Read Dispatch →
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Visual Gallery Preview (Light Theme) -->
@if($galleryPreview->isNotEmpty())
<section class="py-12 sm:py-16 lg:py-24 border-t border-slate-200 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 sm:mb-12 lg:mb-16 gap-4 sm:gap-6">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#f3bd2e] uppercase">MOMENTS OF SPLENDOR</span>
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1 sm:mt-2">Festival Gallery</h2>
            </div>
            <a href="{{ route('gallery.index') }}" class="text-xs sm:text-sm font-semibold text-[#f3bd2e] hover:underline flex items-center gap-1">
                <span>View Full Photo Archive</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
            @foreach($galleryPreview as $photo)
                <div class="group relative rounded-xl sm:rounded-2xl overflow-hidden aspect-[4/3] bg-slate-100 shadow-xs">
                    <img src="{{ $photo->image_path }}" alt="{{ $photo->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3 sm:p-4">
                        <span class="text-xs sm:text-sm font-medium text-white truncate">{{ $photo->title }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
