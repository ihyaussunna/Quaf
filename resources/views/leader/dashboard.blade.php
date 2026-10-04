@extends('layouts.leader')

@section('title', 'Leader Dashboard - QUAF Fest')

@section('content')
<div class="space-y-6">
    <!-- Team Identity Header -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-gray-100 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4 min-w-0">
            <span class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-xl text-white shadow-sm shrink-0 font-sora" 
                  style="background-color: {{ $group->color_hex ?? '#be1e2d' }};">
                {{ substr($group->name ?? 'T', 0, 1) }}
            </span>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black font-sora text-gray-900 truncate">{{ $group->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold"
                          style="background-color: {{ $group->color_hex ?? '#be1e2d' }}15; color: {{ $group->color_hex ?? '#be1e2d' }};">
                        Team Dashboard
                    </span>
                </div>
                <p class="text-xs font-sora text-gray-500 mt-0.5">QUAF Fest 2026 • Official Registration & Performance Portal</p>
            </div>
        </div>
        <div class="flex items-center gap-2 self-stretch sm:self-auto justify-end">
            <div class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono font-bold text-slate-700 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" style="background-color: {{ $group->color_hex ?? '#be1e2d' }};"></span>
                <span>Team: {{ $group->name }}</span>
            </div>
        </div>
    </div>

    <!-- Top Row: Stat Cards & Progress Result matching screenshot -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
        <!-- 4 Quick Stat Cards -->
        <div class="xl:col-span-8 grid grid-cols-2 sm:grid-cols-4 gap-4">
            <!-- Students (Fest Red #be1e2d) -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-xs flex flex-col items-center justify-center text-center">
                <div class="w-12 h-12 rounded-full bg-red-50 text-[#be1e2d] flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div class="text-2xl font-extrabold text-gray-900">{{ $stats['students'] ?? '982+' }}</div>
                <div class="text-xs text-gray-500 font-medium mt-0.5">Students</div>
            </div>

            <!-- Programs (Fest Gold #f3bd2e) -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-xs flex flex-col items-center justify-center text-center">
                <div class="w-12 h-12 rounded-full bg-yellow-50 text-[#f3bd2e] flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="text-2xl font-extrabold text-gray-900">{{ $stats['programs'] ?? '146+' }}</div>
                <div class="text-xs text-gray-500 font-medium mt-0.5">Programs</div>
            </div>

            <!-- Teams (Fest Blue #005c94) -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-xs flex flex-col items-center justify-center text-center">
                <div class="w-12 h-12 rounded-full bg-blue-50 text-[#005c94] flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div class="text-2xl font-extrabold text-gray-900">{{ $stats['teams'] ?? '5+' }}</div>
                <div class="text-xs text-gray-500 font-medium mt-0.5">Teams</div>
            </div>

            <!-- Venues (Fest Green #009444) -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-xs flex flex-col items-center justify-center text-center">
                <div class="w-12 h-12 rounded-full bg-green-50 text-[#009444] flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div class="text-2xl font-extrabold text-gray-900">{{ $stats['venues'] ?? '5+' }}</div>
                <div class="text-xs text-gray-500 font-medium mt-0.5">Venues</div>
            </div>
        </div>

        <!-- Progress Result Card matching screenshot -->
        <div class="xl:col-span-4 bg-white rounded-3xl p-6 border-2 border-red-200/80 shadow-xs flex flex-col justify-center">
            <h3 class="text-center font-bold text-gray-900 text-base mb-3">Progress Result</h3>
            <div class="w-full bg-gray-200 rounded-full h-3 mb-4 overflow-hidden">
                <div class="bg-brand-orange h-3 rounded-full transition-all duration-500" style="width: {{ $stats['progress_percent'] ?? 99.32 }}%"></div>
            </div>
            <div class="text-center">
                <p class="text-sm font-extrabold text-gray-900">
                    Declared {{ $stats['declared_results'] ?? '145' }} of {{ $stats['total_results'] ?? '146' }} Results
                </p>
                <p class="text-xs font-bold text-brand-orange mt-0.5">
                    Progress {{ $stats['progress_percent'] ?? '99.32' }}%
                </p>
            </div>
        </div>
    </div>

    <!-- Registration Quota & Entry Completion Status Card -->
    @if(isset($registrationStats))
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $group->color_hex }};"></span>
                        <h3 class="font-sora font-black text-slate-900 text-lg">Team Entry & Quota Completion Status</h3>
                    </div>
                    <p class="text-xs font-sora text-slate-500 mt-1">Live tracking of complete and pending program registrations for {{ $group->name }}.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-xl text-xs font-mono font-bold {{ $registrationStats['percent'] >= 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900' }}">
                        {{ $registrationStats['percent'] }}% Quota Completed
                    </span>
                    <a href="{{ route('leader.registrations') }}?tab=incomplete" class="px-4 py-2 rounded-xl bg-brand-orange hover:bg-orange-600 text-white font-sora text-xs font-bold transition shadow-xs">
                        Fill Open Slots &rarr;
                    </a>
                </div>
            </div>

            <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                <div class="bg-emerald-500 h-3 rounded-full transition-all duration-500" style="width: {{ $registrationStats['percent'] }}%"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Completed Card -->
                <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200 flex flex-col justify-between">
                    <div>
                        <span class="text-[11px] font-sora uppercase font-bold text-emerald-700 block">Completed Entries (Full Quota)</span>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-2xl font-sora font-black text-emerald-800">{{ $registrationStats['completed'] }}</span>
                            <span class="text-xs font-sora text-emerald-600">/ {{ $registrationStats['total'] }} Programs</span>
                        </div>
                    </div>
                    <span class="text-[11px] font-sora text-emerald-700 mt-2 block font-medium">All candidate slots fully registered</span>
                </div>

                <!-- Partial Card -->
                <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-300 flex flex-col justify-between">
                    <div>
                        <span class="text-[11px] font-sora uppercase font-bold text-amber-800 block">Partially Filled (Slots Open)</span>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-2xl font-sora font-black text-amber-900">{{ $registrationStats['partial'] }}</span>
                            <span class="text-xs font-sora text-amber-700">Programs Incomplete</span>
                        </div>
                    </div>
                    <div class="mt-2 text-xs font-sora font-bold text-amber-900 flex items-center justify-between">
                        <span>{{ $registrationStats['partial_slots_needed'] }} More Candidates Needed</span>
                        <a href="{{ route('leader.registrations') }}?tab=partial" class="underline text-amber-800 hover:text-amber-950">Add Now &rarr;</a>
                    </div>
                </div>

                <!-- Pending Card -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col justify-between">
                    <div>
                        <span class="text-[11px] font-sora uppercase font-bold text-slate-500 block">Pending Registration</span>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-2xl font-sora font-black text-slate-800">{{ $registrationStats['pending'] }}</span>
                            <span class="text-xs font-sora text-slate-500">Programs (0 Enrolled)</span>
                        </div>
                    </div>
                    <div class="mt-2 text-xs font-sora text-slate-600 flex items-center justify-between">
                        <span>{{ $registrationStats['total_slots_needed'] }} total seats across fest</span>
                        <a href="{{ route('leader.registrations') }}?tab=pending" class="underline text-slate-700 hover:text-slate-900 font-bold">Enroll &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Section: Performance Over Time & Score Board -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
        <!-- Performance Over Time SVG Chart matching screenshot -->
        <div class="xl:col-span-8 bg-white rounded-3xl p-6 border border-gray-100 shadow-xs">
            @php
                $chartData = $chartData ?? app(\App\Services\PointCalculationService::class)->getPerformanceChartData();
            @endphp
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-6">
                <div>
                    <h3 class="font-bold text-gray-900 text-lg font-sora">Performance Over Time</h3>
                    <p class="text-xs text-gray-400 font-medium mt-0.5">Real-time team points progress</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 font-sora">
                    Declared {{ $chartData['declaredCount'] ?? 0 }} of {{ $chartData['totalPrograms'] ?? 144 }} Results
                </span>
            </div>

            <!-- Team Legends matching screenshot -->
            <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6 mb-6 text-xs font-semibold">
                @foreach(($chartData['series'] ?? []) as $ser)
                    <div class="flex items-center gap-1.5">
                        <span class="w-4 h-3.5 rounded-xs border border-black/10 shadow-2xs shrink-0" style="background-color: {{ $ser['color'] }};"></span>
                        <span class="text-gray-700 uppercase font-sora">{{ $ser['name'] }}</span>
                    </div>
                @endforeach
            </div>

            <!-- SVG Multi-Line Chart -->
            <div class="w-full overflow-x-auto">
                <svg viewBox="0 0 750 340" class="w-full h-72 text-xs font-sora select-none" style="min-width: 550px;">
                    <!-- Horizontal Grid lines -->
                    <g stroke="#e2e8f0" stroke-width="1" stroke-dasharray="2,2">
                        @foreach(($chartData['ySteps'] ?? []) as $ys)
                            <line x1="60" y1="{{ $ys['y'] }}" x2="720" y2="{{ $ys['y'] }}" @if($ys['val'] === 0) stroke-dasharray="0" stroke="#94a3b8" @endif />
                        @endforeach
                    </g>
                    <!-- Y Axis Labels -->
                    <g fill="#64748b" font-size="10" text-anchor="end">
                        @foreach(($chartData['ySteps'] ?? []) as $ys)
                            <text x="50" y="{{ $ys['y'] + 4 }}">{{ number_format($ys['val']) }}</text>
                        @endforeach
                        <!-- Y axis title rotated -->
                        <text x="-160" y="15" transform="rotate(-90)" font-weight="bold" fill="#334155">Scores</text>
                    </g>

                    <!-- X Axis Labels -->
                    <g fill="#64748b" font-size="10" text-anchor="middle">
                        @foreach(($chartData['xSteps'] ?? []) as $xs)
                            <text x="{{ $xs['x'] }}" y="308">{{ $xs['label'] }}</text>
                            @if(!empty($xs['sub']))
                                <text x="{{ $xs['x'] }}" y="321" font-size="8.5" fill="#94a3b8">{{ $xs['sub'] }}</text>
                            @endif
                        @endforeach
                        <!-- X axis title -->
                        <text x="385" y="335" font-weight="bold" fill="#334155">Declared Results</text>
                    </g>

                    <!-- Dynamic Lines & Markers for each team -->
                    @foreach(($chartData['series'] ?? []) as $ser)
                        <polyline fill="none" stroke="{{ $ser['color'] }}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                            points="{{ $ser['polyline_points'] }}">
                            <title>{{ $ser['name'] }}: {{ $ser['final_points'] }} pts</title>
                        </polyline>

                        @foreach($ser['coords'] as $c)
                            <circle cx="{{ $c['x'] }}" cy="{{ $c['y'] }}" r="3.5" fill="#ffffff" stroke="{{ $ser['color'] }}" stroke-width="2">
                                <title>{{ $ser['name'] }}: {{ $c['pts'] }} pts</title>
                            </circle>
                        @endforeach
                    @endforeach
                </svg>
            </div>
        </div>

        <!-- Score Board Card matching screenshot -->
        <div class="xl:col-span-4 bg-white rounded-3xl p-6 border border-gray-100 shadow-xs flex flex-col justify-between">
            <div>
                <h3 class="text-xl font-extrabold text-gray-900 mb-0.5">Score Board</h3>
                <p class="text-xs text-gray-400 font-medium mb-5">Final Status</p>

                <div class="space-y-3">
                    @php
                        $teamsList = $leaderboard ?? \App\Models\Group::orderByDesc('points_cache')->get();
                    @endphp
                    @forelse($teamsList as $index => $team)
                        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-gray-50/75 hover:bg-gray-100/80 transition">
                            <div class="flex items-center gap-3">
                                <span class="text-base font-extrabold text-brand-orange w-4 font-rockwell">
                                    {{ $index + 1 }}
                                </span>
                                <span class="w-3 h-3 rounded-full shrink-0 border border-black/10 shadow-2xs" style="background-color: {{ $team->color_hex }};"></span>
                                <span class="text-sm font-bold text-gray-900 font-sora">
                                    {{ $team->name }}
                                </span>
                            </div>
                            <div>
                                <span class="px-3.5 py-1 rounded-xl text-sm font-bold font-rockwell border shadow-2xs" style="background-color: {{ $team->color_hex }}15; color: {{ $team->color_hex }}; border-color: {{ $team->color_hex }}35;">
                                    {{ number_format($team->points_cache ?? $team->total_points ?? 0) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-center py-6 text-xs text-gray-400">No score details available.</p>
                    @endforelse
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 mt-6">
                <a href="{{ route('leader.programs') }}" class="w-full py-3 bg-brand-orange text-white rounded-2xl text-xs font-bold text-center block hover:bg-orange-600 transition shadow-xs">
                    View Group Programs &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
