@extends('layouts.public', ['title' => 'Festival Schedule — QUAF 9.0'])

@section('content')

<!-- Header Section -->
<section class="py-10 sm:py-14 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">FESTIVAL LINEUP & TIMINGS</span>
                <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 mt-1">Official Schedule</h1>
                <p class="text-sm text-slate-600 mt-2 max-w-xl">
                    Offstage programs start October 06 • Main Stage competitions October 31 – November 01, 2026 • Central Festival Arena & Offstage Venues (NF3, ID3, U2, S3).
                </p>
            </div>
            
            <!-- Quick Actions & Stats -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('schedule.offstage-pdf', ['date' => $selectedDay ?: '2026-10-07']) }}" target="_blank"
                   class="px-4 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Download Offstage PDF</span>
                </a>
                <span class="px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 font-mono font-bold text-xs text-slate-800">8 Stages / Venues</span>
                <span class="px-3 py-2 rounded-xl bg-red-50 border border-red-200 text-[#be1e2d] font-mono font-bold text-xs">145 Events</span>
            </div>
        </div>
    </div>
</section>

<!-- Filter Toolbar (Sticky Date Tabs & Stage/Zone Filters) -->
<section class="sticky top-16 sm:top-20 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200 py-3 shadow-2xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Day Tabs (24 OCT to 28 OCT) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 sm:pb-0 scrollbar-none mb-3">
            <a href="{{ route('schedule.index', array_merge(request()->except('day', 'page'))) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all {{ empty($selectedDay) ? 'bg-[#be1e2d] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All Days (24–28 Oct)
            </a>
            @foreach($festivalDays as $dateKey => $label)
                <a href="{{ route('schedule.index', array_merge(request()->except('page'), ['day' => $dateKey])) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all {{ $selectedDay === $dateKey ? 'bg-[#be1e2d] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Secondary Filters: Stage, Zone & Search -->
        <form method="GET" action="{{ route('schedule.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 pt-2 border-t border-slate-100">
            @if($selectedDay)
                <input type="hidden" name="day" value="{{ $selectedDay }}">
            @endif

            <!-- Search -->
            <div class="sm:col-span-5 relative">
                <input type="text" 
                       name="search" 
                       value="{{ $search }}"
                       placeholder="Search program name, code (e.g. Q9-101)..." 
                       class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/20 focus:border-[#be1e2d]">
                @if($search)
                    <a href="{{ route('schedule.index', request()->except('search')) }}" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs">✕</a>
                @endif
            </div>

            <!-- Stage Filter -->
            <div class="sm:col-span-3">
                <select name="stage" onchange="this.form.submit()" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/20 focus:border-[#be1e2d]">
                    <option value="">All Stages</option>
                    @foreach($stages as $stg)
                        <option value="{{ $stg->id }}" {{ $selectedStage == $stg->id ? 'selected' : '' }}>
                            {{ $stg->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Zone Filter -->
            <div class="sm:col-span-3">
                <select name="zone" onchange="this.form.submit()" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/20 focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($zones as $z)
                        <option value="{{ $z->name }}" {{ $selectedZone == $z->name ? 'selected' : '' }}>
                            {{ $z->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Clear Button -->
            <div class="sm:col-span-1 flex items-center">
                @if($selectedDay || $selectedStage || $selectedZone || $search)
                    <a href="{{ route('schedule.index') }}" class="w-full py-2.5 text-center text-xs font-semibold text-slate-500 hover:text-[#be1e2d] bg-slate-100 rounded-xl">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</section>

<!-- Schedule Content Feed -->
<section class="py-10 bg-slate-50 min-h-[60vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Case 1: Specific Timed Schedules Exist -->
        @if($schedules->isNotEmpty())
            <div class="space-y-4">
                @foreach($schedules as $item)
                    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-2xs hover:shadow-md transition-shadow flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <!-- Time Badge -->
                            <div class="p-3 rounded-xl bg-slate-100 border border-slate-200 text-center font-mono shrink-0 w-24">
                                <div class="text-xs font-bold text-slate-900">{{ $item->start_time?->format('h:i A') ?? 'TBD' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $item->start_time?->format('M d') ?? 'Oct 2026' }}</div>
                            </div>

                            <!-- Program Info -->
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-mono text-xs font-bold text-[#be1e2d]">{{ $item->program?->code }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span class="text-xs font-mono text-slate-500">{{ $item->program?->eligibility ?? 'All Zones' }}</span>
                                </div>
                                <h3 class="text-base sm:text-lg font-sora font-bold text-slate-900">
                                    {{ $item->program?->name }}
                                </h3>
                                @if($item->program?->malayalam_name)
                                    <p class="text-xs text-slate-500 font-malayalam mt-0.5">{{ $item->program->malayalam_name }}</p>
                                @endif
                                <div class="flex items-center gap-2 mt-2 text-xs text-slate-500">
                                    <span>Venue: <strong class="text-slate-700">{{ $item->stage?->name ?? 'Main Arena' }}</strong></span>
                                    @if($item->stage?->location)
                                        <span class="text-slate-300">•</span>
                                        <span>{{ $item->stage->location }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge & Action -->
                        <div class="flex items-center justify-between md:flex-col md:items-end gap-3 pt-3 md:pt-0 border-t md:border-t-0 border-slate-100">
                            @if($item->status === 'live' || $item->status === 'in_progress')
                                <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5 animate-pulse">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                    <span>LIVE NOW</span>
                                </span>
                            @elseif($item->status === 'completed')
                                <span class="px-3 py-1 rounded-full text-xs font-mono font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                    COMPLETED
                                </span>
                            @elseif($item->status === 'cancelled')
                                <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-red-100 text-red-800 border border-red-200">
                                    CANCELLED
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-mono font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    UPCOMING
                                </span>
                            @endif

                            @if($item->program)
                                <a href="{{ route('results.show', $item->program->id) }}" class="text-xs font-bold text-[#be1e2d] hover:underline">
                                    Program Details →
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        <!-- Case 2: Display Stage Program Lineup with Statuses -->
        @elseif($stagePrograms->isNotEmpty())
            <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs sm:text-sm flex items-center justify-between gap-4">
                <div>
                    <strong>Festival Program Lineup:</strong> Showing official stage competitions categorized under designated stages and academic zones.
                </div>
                <span class="font-mono text-xs font-bold shrink-0">{{ $stagePrograms->count() }} Competitions</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($stagePrograms as $prog)
                    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-2xs hover:shadow-md transition-shadow flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between text-xs font-mono mb-2">
                                <span class="font-bold text-[#be1e2d]">{{ $prog->code }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $prog->status === 'completed' ? 'bg-slate-100 text-slate-600' : ($prog->status === 'in_progress' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-50 text-blue-700') }}">
                                    {{ $prog->status === 'in_progress' ? 'LIVE NOW' : ($prog->status === 'completed' ? 'COMPLETED' : 'UPCOMING') }}
                                </span>
                            </div>

                            <h3 class="text-base font-sora font-bold text-slate-900 mb-1 leading-snug">
                                {{ $prog->name }}
                            </h3>
                            @if($prog->malayalam_name)
                                <p class="text-xs text-slate-500 font-malayalam mb-2">{{ $prog->malayalam_name }}</p>
                            @endif

                            <div class="space-y-1 text-xs text-slate-600 mt-3 pt-3 border-t border-slate-100 font-mono">
                                <div>Zone: <strong class="text-slate-800">{{ $prog->eligibility ?? 'All Zones' }}</strong></div>
                                <div>Category: <span class="text-slate-700">{{ $prog->category?->name ?? 'Arts & Literature' }}</span></div>
                                <div>Venue: <span class="text-slate-700">{{ $prog->stage?->name ?? 'Designated Stage' }}</span></div>
                            </div>
                        </div>

                        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-slate-400 font-mono">{{ $prog->type === 'group' ? 'Group Event' : 'Individual' }}</span>
                            <a href="{{ route('results.show', $prog->id) }}" class="font-bold text-[#be1e2d] hover:underline">
                                View Event →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

        <!-- Case 3: Empty State -->
        @else
            <div class="text-center py-20 bg-white rounded-2xl border border-slate-200 max-w-lg mx-auto p-8 shadow-2xs">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-lg font-sora font-bold text-slate-900 mb-1">No Schedule Entries Found</h3>
                <p class="text-xs text-slate-500 mb-5 leading-relaxed">
                    No competitions match the selected day or filter criteria. Check another date or clear your filters to view the complete schedule.
                </p>
                <a href="{{ route('schedule.index') }}" class="inline-block px-5 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs uppercase tracking-wider">
                    View Complete Schedule
                </a>
            </div>
        @endif

    </div>
</section>

@endsection
