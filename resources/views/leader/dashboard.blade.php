@extends('layouts.leader')

@section('title', 'Leader Dashboard - QUAF Fest')

@section('content')
<div class="space-y-6">
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

    <!-- Main Section: Performance Over Time & Score Board -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
        <!-- Performance Over Time SVG Chart matching screenshot -->
        <div class="xl:col-span-8 bg-white rounded-3xl p-6 border border-gray-100 shadow-xs">
            <h3 class="text-center font-bold text-gray-900 text-lg mb-6">Performance Over Time</h3>

            <!-- Team Legends matching screenshot -->
            <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6 mb-6 text-xs font-semibold">
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-3.5 rounded-xs" style="background-color: #be1e2d;"></span>
                    <span class="text-gray-700">PACTO HIKMIC</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-3.5 rounded-xs" style="background-color: #f3bd2e;"></span>
                    <span class="text-gray-700">YUGO RUSHDIC</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-3.5 rounded-xs" style="background-color: #005c94;"></span>
                    <span class="text-gray-700">CONCO MAJDIC</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-3.5 rounded-xs" style="background-color: #009444;"></span>
                    <span class="text-gray-700">LUMO FIKRIC</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-3.5 rounded-xs" style="background-color: #0f172a;"></span>
                    <span class="text-gray-700">UNIO HILMIC</span>
                </div>
            </div>

            <!-- SVG Multi-Line Chart -->
            <div class="w-full overflow-x-auto">
                <svg viewBox="0 0 750 340" class="w-full h-72 text-xs font-sans select-none" style="min-width: 550px;">
                    <!-- Horizontal Grid lines & Y Axis labels -->
                    <g stroke="#e2e8f0" stroke-width="1" stroke-dasharray="2,2">
                        <line x1="60" y1="40" x2="720" y2="40"/>
                        <line x1="60" y1="90" x2="720" y2="90"/>
                        <line x1="60" y1="140" x2="720" y2="140"/>
                        <line x1="60" y1="190" x2="720" y2="190"/>
                        <line x1="60" y1="240" x2="720" y2="240"/>
                        <line x1="60" y1="290" x2="720" y2="290" stroke-dasharray="0" stroke="#94a3b8"/>
                    </g>
                    <!-- Y Axis Labels -->
                    <g fill="#64748b" font-size="10" text-anchor="end">
                        <text x="50" y="44">1,000</text>
                        <text x="50" y="94">800</text>
                        <text x="50" y="144">600</text>
                        <text x="50" y="194">400</text>
                        <text x="50" y="244">200</text>
                        <text x="50" y="294">0</text>
                        <!-- Y axis title rotated -->
                        <text x="-160" y="15" transform="rotate(-90)" font-weight="bold" fill="#334155">Scores</text>
                    </g>

                    <!-- X Axis Labels -->
                    <g fill="#64748b" font-size="10" text-anchor="middle">
                        <text x="60" y="308">0</text>
                        <text x="160" y="308">After 10 %</text>
                        <text x="260" y="308">After 30 %</text>
                        <text x="360" y="308">After 50 %</text>
                        <text x="460" y="308">After 60 %</text>
                        <text x="560" y="308">After 75 %</text>
                        <text x="645" y="308">After 90 %</text>
                        <text x="710" y="308">Final</text>
                        <!-- X axis title -->
                        <text x="385" y="330" font-weight="bold" fill="#334155">Results</text>
                    </g>

                    <!-- LUMO FIKRIC (Green #009444) -->
                    <polyline fill="none" stroke="#009444" stroke-width="2.5"
                        points="60,290 160,270 260,225 360,195 460,175 560,145 645,105 710,65"/>
                    <circle cx="710" cy="65" r="4" fill="#ffffff" stroke="#009444" stroke-width="2"/>
                    <circle cx="645" cy="105" r="3.5" fill="#ffffff" stroke="#009444" stroke-width="1.5"/>
                    <circle cx="560" cy="145" r="3.5" fill="#ffffff" stroke="#009444" stroke-width="1.5"/>
                    <circle cx="460" cy="175" r="3.5" fill="#ffffff" stroke="#009444" stroke-width="1.5"/>
                    <circle cx="360" cy="195" r="3.5" fill="#ffffff" stroke="#009444" stroke-width="1.5"/>
                    <circle cx="260" cy="225" r="3.5" fill="#ffffff" stroke="#009444" stroke-width="1.5"/>
                    <circle cx="160" cy="270" r="3.5" fill="#ffffff" stroke="#009444" stroke-width="1.5"/>

                    <!-- PACTO HIKMIC (Red #be1e2d) -->
                    <polyline fill="none" stroke="#be1e2d" stroke-width="2.5"
                        points="60,290 160,268 260,222 360,198 460,180 560,155 645,110 710,68"/>
                    <circle cx="710" cy="68" r="4" fill="#ffffff" stroke="#be1e2d" stroke-width="2"/>
                    <circle cx="645" cy="110" r="3.5" fill="#ffffff" stroke="#be1e2d" stroke-width="1.5"/>
                    <circle cx="560" cy="155" r="3.5" fill="#ffffff" stroke="#be1e2d" stroke-width="1.5"/>
                    <circle cx="460" cy="180" r="3.5" fill="#ffffff" stroke="#be1e2d" stroke-width="1.5"/>
                    <circle cx="360" cy="198" r="3.5" fill="#ffffff" stroke="#be1e2d" stroke-width="1.5"/>
                    <circle cx="260" cy="222" r="3.5" fill="#ffffff" stroke="#be1e2d" stroke-width="1.5"/>
                    <circle cx="160" cy="268" r="3.5" fill="#ffffff" stroke="#be1e2d" stroke-width="1.5"/>

                    <!-- CONCO MAJDIC (Blue #005c94) -->
                    <polyline fill="none" stroke="#005c94" stroke-width="2.5"
                        points="60,290 160,272 260,228 360,192 460,172 560,150 645,115 710,78"/>
                    <circle cx="710" cy="78" r="4" fill="#ffffff" stroke="#005c94" stroke-width="2"/>
                    <circle cx="645" cy="115" r="3.5" fill="#ffffff" stroke="#005c94" stroke-width="1.5"/>
                    <circle cx="560" cy="150" r="3.5" fill="#ffffff" stroke="#005c94" stroke-width="1.5"/>
                    <circle cx="460" cy="172" r="3.5" fill="#ffffff" stroke="#005c94" stroke-width="1.5"/>
                    <circle cx="360" cy="192" r="3.5" fill="#ffffff" stroke="#005c94" stroke-width="1.5"/>
                    <circle cx="260" cy="228" r="3.5" fill="#ffffff" stroke="#005c94" stroke-width="1.5"/>
                    <circle cx="160" cy="272" r="3.5" fill="#ffffff" stroke="#005c94" stroke-width="1.5"/>

                    <!-- UNIO HILMIC (Black #0f172a) -->
                    <polyline fill="none" stroke="#0f172a" stroke-width="2"
                        points="60,290 160,274 260,230 360,194 460,174 560,152 645,118 710,80"/>
                    <circle cx="710" cy="80" r="3.5" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>

                    <!-- YUGO RUSHDIC (Gold #f3bd2e) -->
                    <polyline fill="none" stroke="#f3bd2e" stroke-width="2.5"
                        points="60,290 160,278 260,238 360,205 460,190 560,170 645,150 710,120"/>
                    <circle cx="710" cy="120" r="4" fill="#ffffff" stroke="#f3bd2e" stroke-width="2"/>
                    <circle cx="645" cy="150" r="3.5" fill="#ffffff" stroke="#f3bd2e" stroke-width="1.5"/>
                    <circle cx="560" cy="170" r="3.5" fill="#ffffff" stroke="#f3bd2e" stroke-width="1.5"/>
                    <circle cx="460" cy="190" r="3.5" fill="#ffffff" stroke="#f3bd2e" stroke-width="1.5"/>
                    <circle cx="360" cy="205" r="3.5" fill="#ffffff" stroke="#f3bd2e" stroke-width="1.5"/>
                    <circle cx="260" cy="238" r="3.5" fill="#ffffff" stroke="#f3bd2e" stroke-width="1.5"/>
                    <circle cx="160" cy="278" r="3.5" fill="#ffffff" stroke="#f3bd2e" stroke-width="1.5"/>
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
                            <div class="flex items-center gap-4">
                                <span class="text-base font-extrabold text-brand-orange w-4">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-sm font-bold text-gray-900">
                                    {{ $team->name }}
                                </span>
                            </div>
                            <div>
                                <span class="px-3.5 py-1 rounded-xl text-sm font-extrabold {{ $index === 0 ? 'bg-amber-100/70 text-amber-900' : ($index === 1 ? 'bg-gray-200/80 text-gray-800' : ($index === 2 ? 'bg-orange-100/80 text-orange-900' : 'bg-blue-50 text-blue-900')) }}">
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
                    View House Programs &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
