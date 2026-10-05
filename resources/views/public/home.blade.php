@extends('layouts.public', ['title' => 'QUAF 9.0 — Markaz Cultural Festival 2026'])

@section('content')

<!-- Live Fest Marquee Banner (Section 10) -->
@if($liveFestMode)
    <div class="bg-white border-b border-amber-200 py-2 sm:py-2.5 overflow-hidden relative shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center">
            <div class="flex items-center gap-2 pr-3 sm:pr-6 border-r border-slate-200 z-10 bg-white shrink-0">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-600"></span>
                </span>
                <span class="text-[11px] sm:text-xs font-mono font-bold tracking-wider text-[#be1e2d] uppercase whitespace-nowrap">LIVE FEST</span>
            </div>

            <!-- Ticker Content -->
            <div class="overflow-hidden flex-1 pl-3 sm:pl-6">
                <div class="animate-marquee flex items-center gap-8 sm:gap-12 text-[11px] sm:text-xs font-mono">
                    @forelse($latestResults as $res)
                        <div class="flex items-center gap-2 whitespace-nowrap">
                            <span class="text-slate-500 font-semibold">[{{ $res->program->code }} {{ $res->program->name }}]:</span>
                            <span class="text-amber-600 font-bold">1st {{ $res->firstEntry?->student?->name ?? 'Team ' . $res->firstEntry?->group?->name }} ({{ $res->firstEntry?->group?->code }})</span>
                            <span class="text-slate-300">•</span>
                            <span class="text-slate-700">2nd {{ $res->secondEntry?->student?->name ?? 'Team ' . $res->secondEntry?->group?->name }}</span>
                            @if($res->thirdEntry)
                                <span class="text-slate-300">•</span>
                                <span class="text-slate-600">3rd {{ $res->thirdEntry?->student?->name ?? 'Team ' . $res->thirdEntry?->group?->name }}</span>
                            @endif
                        </div>
                    @empty
                        <span class="text-slate-600">Welcome to QUAF 9.0 — Live competition verdicts, stage calls, and verified scores are officially active.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Hero Section (Apple-inspired 2-Column Festival Presentation - Section 11 & 12) -->
<section class="relative min-h-[75vh] lg:min-h-[85vh] flex items-center justify-center overflow-hidden bg-slate-50 pt-10 sm:pt-16 pb-14 sm:pb-20 border-b border-slate-200/80">
    <!-- Atmospheric Ambient Glows -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[350px] sm:w-[650px] h-[300px] sm:h-[400px] bg-red-500/10 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute bottom-10 left-10 w-72 sm:w-96 h-72 sm:h-96 bg-amber-400/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute top-10 right-10 w-72 sm:w-96 h-72 sm:h-96 bg-blue-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-10 lg:gap-16">
            
            <!-- Left Column: Eyebrow, Heading, Theme & Actions -->
            <div class="w-full lg:w-1/2 text-center lg:text-left space-y-6">
                <!-- Eyebrow Institution Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 text-slate-700 text-[11px] sm:text-xs tracking-wider uppercase shadow-2xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-[#be1e2d]"></span>
                    <span>IHYAUSSUNNA STUDENTS UNION • MARKAZ</span>
                </div>

                <!-- Main Titles -->
                <div class="space-y-1.5">
                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-sora font-black text-slate-900 tracking-tight leading-none">
                        QUAF 9.0
                    </h1>
                    <div class="text-lg sm:text-2xl font-sora font-bold text-slate-700 tracking-tight">
                        MARKAZ CULTURAL FESTIVAL 2026
                    </div>
                </div>

                <!-- Festival Theme -->
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-amber-50 border border-amber-200/90 text-amber-950 shadow-2xs">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-amber-700">Theme:</span>
                    <span class="text-sm sm:text-base font-bold tracking-tight">Ādabīc Inheritance</span>
                    <span class="text-xs text-amber-700 hidden sm:inline">• Confluence of Eloquence & Fine Arts</span>
                </div>

                <!-- Narrative Description -->
                <p class="text-sm sm:text-base md:text-lg text-slate-600 font-normal leading-relaxed max-w-xl mx-auto lg:mx-0">
                    The grand collegiate arts and literary confluence of Jamia Markaz. Uniting premier academic groups across 120+ intellectual, oratory, and fine arts disciplines.
                </p>

                <!-- Dates & Venue Badge -->
                <div class="inline-flex flex-col sm:flex-row items-center lg:items-start justify-center lg:justify-start gap-2 sm:gap-4 text-xs font-mono text-slate-700 px-4 py-3 rounded-2xl bg-white border border-slate-200 shadow-2xs">
                    <div class="flex items-center gap-2 font-semibold">
                        <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>06 OCT — 01 NOV 2026</span>
                    </div>
                    <span class="text-slate-300 hidden sm:inline">|</span>
                    <div class="flex items-center gap-2 font-semibold">
                        <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>CENTRAL ARENA & OFFSTAGE VENUES, MARKAZ</span>
                    </div>
                </div>

                <!-- Primary CTAs -->
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-2">
                    <a href="{{ route('results.index') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-xl font-bold text-xs sm:text-sm uppercase tracking-wider bg-gradient-to-r from-[#be1e2d] to-[#991522] text-white hover:brightness-110 shadow-md shadow-red-600/20 transition-all transform hover:-translate-y-0.5 text-center">
                        View Live Results
                    </a>
                    <a href="{{ route('schedule.index') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-xl font-semibold text-xs sm:text-sm tracking-wider bg-white border border-slate-300 hover:border-[#be1e2d] text-slate-800 transition-all transform hover:-translate-y-0.5 shadow-2xs text-center">
                        Explore Schedule
                    </a>
                    <a href="{{ route('brochure.index') }}" class="w-full sm:w-auto px-5 py-3.5 rounded-xl font-semibold text-xs sm:text-sm tracking-wider bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 transition-all text-center">
                        Brochure
                    </a>
                </div>
            </div>

            <!-- Right Column: Premium Festival Artwork Panel (Section 12) -->
            <div class="w-full lg:w-1/2 flex items-center justify-center">
                <div class="relative group max-w-lg w-full">
                    <!-- Glass Container with Title Logo -->
                    <div class="glass-panel rounded-3xl p-6 sm:p-10 border border-slate-200/90 shadow-xl relative overflow-hidden flex flex-col items-center text-center">
                        <div class="absolute -top-20 -right-20 w-44 h-44 bg-red-500/10 rounded-full blur-2xl"></div>
                        <div class="absolute -bottom-20 -left-20 w-44 h-44 bg-amber-500/15 rounded-full blur-2xl"></div>

                        <img src="{{ asset('images/quaf-title-logo.png') }}" 
                             alt="QUAF 9.0" 
                             style="max-height: 260px; object-fit: contain;"
                             class="w-full max-w-[260px] xs:max-w-[300px] sm:max-w-md h-auto object-contain drop-shadow-sm transition-transform duration-500 group-hover:scale-105">

                        <!-- Badge Pills inside Hero Card -->
                        <div class="grid grid-cols-3 gap-2.5 w-full mt-6 pt-6 border-t border-slate-200/80">
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-center">
                                <span class="font-sora font-black text-slate-900 text-base sm:text-lg block">{{ $stats['programs'] ?? 144 }}</span>
                                <span class="text-[10px] font-mono text-slate-500 uppercase tracking-tight">Programs</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-center">
                                <span class="font-sora font-black text-[#be1e2d] text-base sm:text-lg block">{{ $stats['groups'] ?? 5 }}</span>
                                <span class="text-[10px] font-mono text-slate-500 uppercase tracking-tight">Groups</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-center">
                                <span class="font-sora font-black text-[#009444] text-base sm:text-lg block">{{ $stats['stages'] ?? 8 }}</span>
                                <span class="text-[10px] font-mono text-slate-500 uppercase tracking-tight">Venues & Stages</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Festival Countdown & Status Banner (Section 13) -->
<section class="py-6 sm:py-8 bg-white border-b border-slate-200 relative overflow-hidden"
         x-data="{
             eventDate: new Date('2026-10-06T09:00:00+05:30').getTime(),
             endDate: new Date('2026-11-01T23:59:59+05:30').getTime(),
             now: new Date().getTime(),
             days: 0,
             hours: 0,
             minutes: 0,
             seconds: 0,
             status: 'upcoming',
             updateTimer() {
                 this.now = new Date().getTime();
                 const diff = this.eventDate - this.now;
                 if (diff > 0) {
                     this.status = 'upcoming';
                     this.days = Math.floor(diff / (1000 * 60 * 60 * 24));
                     this.hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                     this.minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                     this.seconds = Math.floor((diff % (1000 * 60)) / 1000);
                 } else if (this.now < this.endDate) {
                     this.status = 'live';
                 } else {
                     this.status = 'archive';
                 }
             }
         }"
         x-init="updateTimer(); setInterval(() => updateTimer(), 1000)">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            
            <div class="flex items-center gap-3 text-center md:text-left">
                <template x-if="status === 'upcoming'">
                    <div>
                        <span class="text-[11px] font-mono uppercase tracking-widest text-[#be1e2d] font-bold block">OFFICIAL FESTIVAL COUNTDOWN</span>
                        <h3 class="text-xl sm:text-2xl font-sora font-black text-slate-900">Offstage: 06 Oct &bull; Main Stage: 31 Oct — 01 Nov</h3>
                    </div>
                </template>
                <template x-if="status === 'live'">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-600"></span>
                        </span>
                        <div>
                            <span class="text-[11px] font-mono uppercase tracking-widest text-emerald-600 font-bold block">FESTIVAL IN PROGRESS</span>
                            <h3 class="text-xl sm:text-2xl font-sora font-black text-slate-900">QUAF 9.0 IS CURRENTLY LIVE</h3>
                        </div>
                    </div>
                </template>
                <template x-if="status === 'archive'">
                    <div>
                        <span class="text-[11px] font-mono uppercase tracking-widest text-slate-500 font-bold block">OFFICIAL ARCHIVE</span>
                        <h3 class="text-xl sm:text-2xl font-sora font-black text-slate-900">QUAF 9.0 Festival Concluded</h3>
                    </div>
                </template>
            </div>

            <!-- Countdown Timer Blocks -->
            <template x-if="status === 'upcoming'">
                <div class="grid grid-cols-4 gap-2 sm:gap-3 text-center font-mono">
                    <div class="px-3 sm:px-4 py-2 rounded-xl bg-slate-100 border border-slate-200">
                        <div class="text-xl sm:text-2xl font-sora font-black text-slate-900" x-text="days">0</div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-tight">Days</div>
                    </div>
                    <div class="px-3 sm:px-4 py-2 rounded-xl bg-slate-100 border border-slate-200">
                        <div class="text-xl sm:text-2xl font-sora font-black text-slate-900" x-text="hours">0</div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-tight">Hours</div>
                    </div>
                    <div class="px-3 sm:px-4 py-2 rounded-xl bg-slate-100 border border-slate-200">
                        <div class="text-xl sm:text-2xl font-sora font-black text-slate-900" x-text="minutes">0</div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-tight">Mins</div>
                    </div>
                    <div class="px-3 sm:px-4 py-2 rounded-xl bg-slate-100 border border-slate-200">
                        <div class="text-xl sm:text-2xl font-sora font-black text-[#be1e2d]" x-text="seconds">0</div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-tight">Secs</div>
                    </div>
                </div>
            </template>

            <template x-if="status === 'live'">
                <div class="flex items-center gap-3">
                    <a href="{{ route('results.index') }}" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider shadow-xs">
                        View Live Scores
                    </a>
                </div>
            </template>
        </div>
    </div>
</section>

<!-- Quick Festival Metrics Strip (Section 14) -->
<section class="py-8 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="glass-panel p-4 rounded-2xl text-center shadow-2xs">
                <span class="text-[10px] font-mono uppercase text-slate-500 tracking-wider font-semibold block mb-0.5">Competitions</span>
                <span class="text-2xl sm:text-3xl font-rockwell font-bold text-slate-900">{{ number_format($stats['programs'] ?? 144) }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5">120+ Official Events</span>
            </div>
            <div class="glass-panel p-4 rounded-2xl text-center shadow-2xs">
                <span class="text-[10px] font-mono uppercase text-slate-500 tracking-wider font-semibold block mb-0.5">Registered Students</span>
                <span class="text-2xl sm:text-3xl font-rockwell font-bold text-[#be1e2d]">{{ number_format($stats['students'] ?? 640) }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5">Verified Delegates</span>
            </div>
            <div class="glass-panel p-4 rounded-2xl text-center shadow-2xs">
                <span class="text-[10px] font-mono uppercase text-slate-500 tracking-wider font-semibold block mb-0.5">Academic Groups</span>
                <span class="text-2xl sm:text-3xl font-rockwell font-bold text-[#2e3192]">{{ $stats['groups'] ?? 5 }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5">Official Houses</span>
            </div>
            <div class="glass-panel p-4 rounded-2xl text-center shadow-2xs">
                <span class="text-[10px] font-mono uppercase text-slate-500 tracking-wider font-semibold block mb-0.5">Active Stages</span>
                <span class="text-2xl sm:text-3xl font-rockwell font-bold text-[#009444]">{{ $stats['stages'] ?? 4 }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5">Grand Arenas</span>
            </div>
            <div class="glass-panel p-4 rounded-2xl text-center shadow-2xs col-span-2 md:col-span-1">
                <span class="text-[10px] font-mono uppercase text-slate-500 tracking-wider font-semibold block mb-0.5">Festival Divisions</span>
                <span class="text-2xl sm:text-3xl font-rockwell font-bold text-amber-600">{{ $stats['zones'] ?? 4 }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5">A, B, C & Mix Zones</span>
            </div>
        </div>
    </div>
</section>

<!-- Live Standings / Festival Standings (Section 15) -->
<section id="groups" class="py-14 sm:py-20 bg-white border-b border-slate-200 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">ACADEMIC GROUPS</span>
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1">Festival Standings</h2>
            </div>
            <div class="flex items-center gap-3">
                <p class="text-xs sm:text-sm text-slate-600 max-w-md">
                    Points are aggregated dynamically in real-time as official verdicts are published and verified.
                </p>
                <a href="{{ route('groups.index') }}" class="text-xs font-bold text-[#be1e2d] hover:underline whitespace-nowrap shrink-0">
                    All Groups →
                </a>
            </div>
        </div>

        <!-- 5 Official Groups Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-6">
            @foreach($groups as $group)
                @php
                    $isFirst = $loop->iteration === 1;
                    $isSecond = $loop->iteration === 2;
                    $isThird = $loop->iteration === 3;
                @endphp
                <a href="{{ route('groups.show', $group->id) }}" class="group relative rounded-2xl bg-white border-2 border-slate-200/90 hover:border-slate-300 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg flex flex-col justify-between"
                   style="border-top-color: {{ $group->color_hex }}; border-top-width: 4px;">
                    
                    <!-- Ambient Tint on Hover -->
                    <div class="absolute -right-10 -top-10 w-28 h-28 rounded-full opacity-10 group-hover:opacity-20 blur-xl transition-opacity duration-500"
                         style="background-color: {{ $group->color_hex }}"></div>

                    <div>
                        <!-- Header with Rank Badge & Code -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full shadow-2xs" style="background-color: {{ $group->color_hex }}"></span>
                                <span class="font-mono text-xs text-slate-700 font-bold uppercase tracking-wider">{{ $group->code }}</span>
                            </div>
                            
                            <!-- Rank Badge with Podium Styling -->
                            @if($isFirst)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                                    #1 LEADER
                                </span>
                            @elseif($isSecond)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                    #2
                                </span>
                            @elseif($isThird)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    #3
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-mono font-semibold bg-slate-100 text-slate-600">
                                    #{{ $group->rank_cache ?: $loop->iteration }}
                                </span>
                            @endif
                        </div>

                        <!-- Group Name -->
                        <h3 class="text-xl font-sora font-black text-slate-900 mb-1 group-hover:text-[#be1e2d] transition-colors">
                            {{ $group->name }}
                        </h3>
                        <p class="text-xs text-slate-500 mb-5 flex items-center gap-1.5 truncate">
                            <span class="truncate">Captain: {{ $group->manager_name ?: ($group->leader?->name ?? 'House Leadership') }}</span>
                        </p>
                    </div>

                    <!-- Points Metric in Rockwell Font -->
                    <div class="pt-4 border-t border-slate-100 flex items-baseline justify-between mt-auto">
                        <span class="text-xs font-mono text-slate-500 uppercase font-semibold">Tally</span>
                        <div class="text-3xl font-rockwell font-bold tracking-tight" style="color: {{ $group->color_hex }}">
                            {{ number_format($group->points_cache) }} <span class="text-xs font-mono text-slate-500 uppercase font-normal">PTS</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Official Festival Zones Section (Section 20) -->
<section id="zones" class="py-14 sm:py-20 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">ACADEMIC DIVISIONS</span>
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1">Festival Zones</h2>
            </div>
            <p class="text-xs sm:text-sm text-slate-600 max-w-md">
                120+ cultural and literary competitions strictly categorized across 4 official academic divisions.
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
                        <p class="text-xs text-slate-500 mb-6 font-mono leading-relaxed">Classes: {{ $zoneItem['classes'] }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-[10px] font-mono uppercase text-slate-400 font-semibold block">Total Events</span>
                            <span class="font-black text-slate-900 text-base">{{ $zoneItem['programs_count'] }}</span>
                        </div>
                        <div class="text-right">
                            <a href="{{ route('results.index', ['zone' => $key]) }}" class="text-xs font-bold text-[#be1e2d] hover:underline">
                                View Results →
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Active Stages / Live Stage Monitor (Section 22) -->
<section class="py-14 sm:py-20 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">FESTIVAL VENUES</span>
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1">Active Stages</h2>
            </div>
            <div class="flex flex-wrap items-center gap-4 text-xs font-mono text-slate-600">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Live Now</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Break / Intermission</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span> Concluded</span>
            </div>
        </div>

        <!-- Venue Filter Tabs -->
        <div x-data="{ stageFilter: 'all' }" class="space-y-6">
            <div class="flex items-center gap-2 overflow-x-auto pb-2 sm:pb-0 scrollbar-none">
                <button type="button" @click="stageFilter = 'all'"
                        :class="stageFilter === 'all' ? 'bg-[#be1e2d] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap">
                    All Stages ({{ count($stages) }})
                </button>
                <button type="button" @click="stageFilter = 'main'"
                        :class="stageFilter === 'main' ? 'bg-[#be1e2d] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap">
                    Main Arenas (01–04)
                </button>
                <button type="button" @click="stageFilter = 'offstage'"
                        :class="stageFilter === 'offstage' ? 'bg-[#be1e2d] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap">
                    Offstage Venues (NF3, ID3, U2, S3)
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($stages as $stage)
                    @php
                        $isOffstage = in_array($stage->code, ['STG-05', 'STG-06', 'STG-07', 'STG-08']);
                    @endphp
                    <div x-show="stageFilter === 'all' || (stageFilter === 'main' && !{{ $isOffstage ? 'true' : 'false' }}) || (stageFilter === 'offstage' && {{ $isOffstage ? 'true' : 'false' }})"
                         class="rounded-2xl bg-white border border-slate-200/90 p-5 sm:p-6 relative overflow-hidden flex flex-col justify-between shadow-2xs hover:shadow-md transition-shadow">
                    <div>
                        <!-- Stage Header -->
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-mono text-xs font-bold text-[#be1e2d] uppercase">{{ $stage->code }}</span>
                            @if($stage->status === 'active')
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>LIVE NOW</span>
                                </span>
                            @elseif($stage->status === 'break')
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200 font-bold">BREAK</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase tracking-wider bg-slate-100 text-slate-600 font-bold">CLOSED</span>
                            @endif
                        </div>

                        <h3 class="text-base sm:text-lg font-sora font-bold text-slate-900 mb-1">{{ $stage->name }}</h3>
                        <p class="text-xs text-slate-500 mb-4 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 opacity-60 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            <span class="truncate">{{ $stage->location ?? 'Main Campus Plaza' }}</span>
                        </p>

                        <!-- Current Program -->
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-200/80 mb-2.5">
                            <span class="text-[10px] font-mono text-slate-500 uppercase tracking-wider block mb-0.5 font-semibold">CURRENT EVENT</span>
                            <div class="font-bold text-xs sm:text-sm text-slate-900 line-clamp-1">
                                {{ $stage->currentProgram?->name ?? 'Stage Intermission' }}
                            </div>
                            @if($stage->currentProgram)
                                <span class="text-[11px] text-[#be1e2d] block mt-0.5 font-semibold truncate">{{ $stage->currentProgram->eligibility ?? 'A Zone' }}</span>
                            @endif
                        </div>

                        <!-- Next Program -->
                        <div class="bg-white rounded-xl p-2.5 border border-slate-200/80">
                            <span class="text-[10px] font-mono text-slate-500 uppercase tracking-wider block mb-0.5 font-semibold">NEXT UP</span>
                            <div class="text-xs text-slate-700 line-clamp-1 font-medium">
                                {{ $stage->nextProgram?->name ?? 'Program Lineup Pending' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-mono">
                        <span>Capacity: {{ $stage->capacity }} seats</span>
                        <a href="{{ route('schedule.index', ['stage' => $stage->id]) }}" class="text-[#be1e2d] font-semibold hover:underline">Schedule →</a>
                    </div>
                </div>
            @endforeach
            </div>
        </div>
    </div>
</section>

<!-- Latest Official Results (Podium Cards - Section 16 & 17) -->
<section class="py-14 sm:py-20 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">VERIFIED VERDICTS</span>
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1">Latest Results</h2>
            </div>
            <a href="{{ route('results.index') }}" class="text-xs sm:text-sm font-bold text-[#be1e2d] hover:underline flex items-center gap-1">
                <span>View Complete Results Archive</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($latestResults as $result)
                <div class="rounded-2xl bg-white border border-slate-200 hover:border-slate-300 p-5 sm:p-6 transition-all duration-300 flex flex-col justify-between shadow-2xs hover:shadow-md">
                    <div>
                        <!-- Header -->
                        <div class="flex items-center justify-between text-xs font-mono text-slate-500 mb-2">
                            <span class="text-[#be1e2d] font-bold">{{ $result->program->code }}</span>
                            <span>{{ $result->published_at?->diffForHumans() ?? 'Declared' }}</span>
                        </div>
                        <h3 class="text-base sm:text-xl font-sora font-bold text-slate-900 mb-1 leading-snug">
                            {{ $result->program->name }}
                        </h3>
                        <span class="text-xs text-slate-500 block mb-4 font-medium">{{ $result->program->eligibility ?? 'A Zone' }}</span>

                        <!-- Placements -->
                        <div class="space-y-2">
                            <!-- 1st Place -->
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50 border border-amber-200 gap-2">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <span class="w-6 h-6 rounded-full bg-amber-500 text-white font-black text-xs flex items-center justify-center font-sora shrink-0">1</span>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-xs sm:text-sm text-slate-900 truncate">
                                            {{ $result->firstEntry?->student?->name ?? 'Team ' . $result->firstEntry?->group?->name }}
                                        </div>
                                        <span class="text-[10px] text-slate-500 font-mono">Chest #{{ $result->firstEntry?->chest_number }}</span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-white text-[#be1e2d] border border-amber-200 shrink-0 uppercase">
                                    {{ $result->firstEntry?->group?->code ?? $result->firstEntry?->group?->name }}
                                </span>
                            </div>

                            <!-- 2nd Place -->
                            @if($result->secondEntry)
                                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200 gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <span class="w-6 h-6 rounded-full bg-slate-400 text-white font-black text-xs flex items-center justify-center font-sora shrink-0">2</span>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-xs sm:text-sm text-slate-800 truncate">
                                                {{ $result->secondEntry?->student?->name ?? 'Team ' . $result->secondEntry?->group?->name }}
                                            </div>
                                            <span class="text-[10px] text-slate-500 font-mono">Chest #{{ $result->secondEntry?->chest_number }}</span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-white text-slate-700 border border-slate-200 shrink-0 uppercase">
                                        {{ $result->secondEntry?->group?->code ?? $result->secondEntry?->group?->name }}
                                    </span>
                                </div>
                            @endif

                            <!-- 3rd Place -->
                            @if($result->thirdEntry)
                                <div class="flex items-center justify-between p-2 rounded-xl bg-amber-50/50 border border-amber-200/70 gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <span class="w-6 h-6 rounded-full bg-amber-700 text-white font-black text-xs flex items-center justify-center font-sora shrink-0">3</span>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-xs sm:text-sm text-slate-800 truncate">
                                                {{ $result->thirdEntry?->student?->name ?? 'Team ' . $result->thirdEntry?->group?->name }}
                                            </div>
                                            <span class="text-[10px] text-slate-500 font-mono">Chest #{{ $result->thirdEntry?->chest_number }}</span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-white text-slate-700 border border-slate-200 shrink-0 uppercase">
                                        {{ $result->thirdEntry?->group?->code ?? $result->thirdEntry?->group?->name }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ route('media.results.public-poster', $result->id) }}" class="text-[11px] font-mono text-slate-500 hover:text-slate-800 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>Poster</span>
                        </a>
                        <a href="{{ route('results.show', $result->program->id) }}" class="text-xs font-bold text-[#be1e2d] hover:underline">
                            Full Score Sheet →
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-16 bg-white rounded-2xl border border-slate-200 text-slate-500 font-mono text-sm">
                    No results have been officially published yet. Stay tuned as jury deliberations conclude.
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Official Digital Brochure Callout (Section 27 & 28) -->
<section class="py-14 sm:py-20 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-slate-900 text-white p-8 sm:p-12 relative overflow-hidden flex flex-col lg:flex-row items-center justify-between gap-8 shadow-xl">
            <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-red-600/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-20 -top-20 w-80 h-80 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="space-y-4 max-w-xl text-center lg:text-left relative z-10">
                <span class="px-3 py-1 rounded-full text-[11px] font-mono uppercase tracking-widest bg-amber-500/20 text-amber-400 border border-amber-500/30 font-bold inline-block">
                    OFFICIAL FESTIVAL PUBLICATION
                </span>
                <h2 class="text-2xl sm:text-4xl font-sora font-black tracking-tight leading-tight">
                    Read the 14-Page QUAF 9.0 Digital Brochure
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Explore the complete rules, theme essays, festival philosophy, schedule guidelines, and historical legacy in an interactive 3D two-page book viewer.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3">
                    <a href="{{ route('brochure.index') }}" class="w-full sm:w-auto px-7 py-3 rounded-xl font-bold text-xs uppercase tracking-wider bg-white text-slate-900 hover:bg-slate-100 transition-all text-center">
                        Open Digital Book
                    </a>
                    <a href="{{ asset('documents/quaf-brochure.pdf') }}" download class="w-full sm:w-auto px-6 py-3 rounded-xl font-semibold text-xs tracking-wider bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all text-center">
                        Download PDF (Original)
                    </a>
                </div>
            </div>

            <!-- Brochure Visual Mockup -->
            <div class="relative z-10 w-full lg:w-auto flex justify-center">
                <a href="{{ route('brochure.index') }}" class="group block relative transform hover:scale-105 transition-transform duration-300">
                    <div class="w-64 sm:w-72 aspect-[3/4] rounded-xl bg-gradient-to-tr from-[#991522] via-[#be1e2d] to-[#f3bd2e] p-1 shadow-2xl">
                        <div class="w-full h-full rounded-lg bg-slate-950 p-6 flex flex-col justify-between border border-white/10">
                            <div>
                                <span class="font-mono text-[10px] text-amber-400 uppercase tracking-widest block font-bold">EDITION 2026</span>
                                <div class="text-xl font-sora font-black text-white mt-1">QUAF 9.0</div>
                                <div class="text-xs text-slate-300 font-medium mt-0.5">Brochure</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-[11px] font-mono text-slate-400">14 Pages • Two-Page Spread</div>
                                <div class="text-xs text-amber-400 font-bold group-hover:translate-x-1 transition-transform">Click to Read →</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Festival Journal & News (Section 24) -->
@if($latestNews->isNotEmpty())
<section class="py-14 sm:py-20 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">FESTIVAL JOURNAL</span>
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1">Latest News & Dispatches</h2>
            </div>
            <a href="{{ route('news.index') }}" class="text-xs sm:text-sm font-bold text-[#be1e2d] hover:underline flex items-center gap-1">
                <span>View All Articles</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach($latestNews as $article)
                <a href="{{ route('news.show', $article->slug) }}" class="group flex flex-col rounded-2xl bg-white border border-slate-200 hover:border-slate-300 overflow-hidden transition-all duration-300 shadow-2xs hover:shadow-md">
                    <div class="aspect-video w-full overflow-hidden bg-slate-100 relative">
                        @if($article->cover_image)
                            <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-400 font-mono text-xs">
                                QUAF Press Desk
                            </div>
                        @endif
                        <div class="absolute top-3 left-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-mono uppercase tracking-wider bg-white/90 backdrop-blur-md text-[#be1e2d] border border-red-200 font-bold shadow-2xs">
                                {{ $article->category }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <span class="text-xs font-mono text-slate-400 block mb-2">{{ $article->published_at?->format('M d, Y') ?? 'Recent' }}</span>
                            <h3 class="text-lg font-sora font-bold text-slate-900 group-hover:text-[#be1e2d] transition-colors leading-snug mb-2">
                                {{ $article->title }}
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 line-clamp-2 leading-relaxed">
                                {{ $article->excerpt }}
                            </p>
                        </div>
                        <span class="text-xs font-bold text-[#be1e2d] mt-4 flex items-center gap-1">
                            Read Dispatch →
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Visual Gallery Preview (Section 23) -->
@if($galleryPreview->isNotEmpty())
<section class="py-14 sm:py-20 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">MOMENTS OF SPLENDOR</span>
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1">Festival Gallery</h2>
            </div>
            <a href="{{ route('gallery.index') }}" class="text-xs sm:text-sm font-bold text-[#be1e2d] hover:underline flex items-center gap-1">
                <span>View Full Photo Archive</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($galleryPreview as $photo)
                <div class="group relative rounded-2xl overflow-hidden aspect-[4/3] bg-slate-100 shadow-2xs">
                    <img src="{{ $photo->image_path }}" alt="{{ $photo->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                        <span class="text-xs sm:text-sm font-medium text-white truncate">{{ $photo->title }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Official QR Verification Hub CTA (Section 29) -->
<section class="py-14 sm:py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glass-panel rounded-3xl p-8 sm:p-12 border border-slate-200/90 shadow-sm flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="space-y-2 max-w-xl text-center md:text-left">
                <span class="text-xs font-mono uppercase tracking-widest text-[#be1e2d] font-bold block">AUTHENTICITY ASSURED</span>
                <h3 class="text-2xl sm:text-3xl font-sora font-black text-slate-900">
                    Official QR Verification Hub
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Verify participant certificates, winner awards, and student delegate credentials instantly via our secure cryptographic QR engine.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                <a href="{{ route('verify.index') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl font-bold text-xs uppercase tracking-wider bg-slate-900 text-white hover:bg-slate-800 transition-all text-center">
                    Open Verification Hub
                </a>
                <a href="{{ route('verify.certificate', 'SAMPLE') }}" class="w-full sm:w-auto px-5 py-3.5 rounded-xl font-semibold text-xs tracking-wider bg-white border border-slate-300 text-slate-700 hover:text-slate-900 transition-all text-center">
                    Sample Certificate
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
