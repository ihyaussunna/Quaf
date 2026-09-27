@extends('layouts.admin', ['title' => 'Dashboard | FestFloww Operations Center'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight font-sora">Dashboard</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-sora">Central festival operations, real-time analytics & group progress.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.mark-entry.view-marks') }}" class="px-3.5 py-2 rounded-xl bg-[#be1e2d] text-white font-bold text-xs uppercase tracking-wider hover:bg-[#a01624] transition-colors shadow-sm flex items-center gap-1.5 font-sora">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>View Marks</span>
            </a>
            <a href="{{ route('admin.results.all') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 font-bold text-xs uppercase hover:bg-slate-50 transition-colors shadow-2xs flex items-center gap-1.5 font-sora">
                <svg class="w-3.5 h-3.5 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span>All Results</span>
            </a>
            <a href="{{ route('admin.exports.index') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 font-bold text-xs uppercase hover:bg-slate-50 transition-colors shadow-2xs flex items-center gap-1.5 font-sora">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Export Data</span>
            </a>
            <form method="POST" action="{{ route('admin.system.sync-festival-data') }}" class="inline">
                @csrf
                <button type="submit" onclick="return confirm('Do you want to sync all 144 official programs, groups, stages, and Conco Majdic students into the database?')" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs uppercase hover:bg-slate-800 transition-colors shadow-2xs flex items-center gap-1.5 font-sora" title="Sync 144 Programs, Groups, Stages, and Conco Majdic Students">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Sync Official Data</span>
                </button>
            </form>
        </div>
    </div>

    @if($stats['competitions'] == 0 || $stats['teams'] == 0)
        <div class="rounded-2xl bg-amber-500/10 border border-amber-500/30 p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-sm font-bold text-amber-950 flex items-center gap-2 font-sora">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>ഡാറ്റാബേസിൽ പ്രോഗ്രാമുകളും ഗ്രൂപ്പുകളും ചേർത്തിട്ടില്ല (Database Needs Initial Sync)</span>
                </h3>
                <p class="text-xs text-amber-800 leading-relaxed font-sora">
                    ഔദ്യോഗിക 144 പ്രോഗ്രാമുകളും, 5 ഗ്രൂപ്പുകളും, സ്റ്റേജുകളും, കോൺകോ മജ്ദിക് വിദ്യാർത്ഥികളും ഒറ്റ ക്ലിക്കിൽ ആഡ് ചെയ്യാൻ താഴെയുള്ള ബട്ടൺ ക്ലിക്ക് ചെയ്യുക.
                </p>
            </div>
            <form method="POST" action="{{ route('admin.system.sync-festival-data') }}" class="flex-shrink-0">
                @csrf
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white text-xs font-sora font-bold uppercase tracking-wider shadow-md transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Sync 144 Programs & Data Now</span>
                </button>
            </form>
        </div>
    @endif

    <!-- 4 Primary Stat Cards (Matching Last Year's Screenshot) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Students -->
        <a href="{{ route('admin.students.index') }}" class="rounded-2xl bg-white border border-slate-200 p-5 shadow-2xs hover:border-[#be1e2d] transition-all group flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-red-50 text-[#be1e2d] flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900 group-hover:text-[#be1e2d] transition-colors font-mono">{{ number_format($stats['participants']) }}+</div>
                <div class="text-xs text-slate-500 font-bold uppercase tracking-wider font-sora">Students</div>
            </div>
        </a>

        <!-- 2. Programs -->
        <a href="{{ route('admin.programs.index') }}" class="rounded-2xl bg-white border border-slate-200 p-5 shadow-2xs hover:border-[#be1e2d] transition-all group flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-yellow-50 text-[#f3bd2e] flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900 group-hover:text-[#be1e2d] transition-colors font-mono">{{ number_format($stats['competitions']) }}+</div>
                <div class="text-xs text-slate-500 font-bold uppercase tracking-wider font-sora">Programs</div>
            </div>
        </a>

        <!-- 3. Teams -->
        <a href="{{ route('admin.groups.index') }}" class="rounded-2xl bg-white border border-slate-200 p-5 shadow-2xs hover:border-[#be1e2d] transition-all group flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#005c94] flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900 group-hover:text-[#be1e2d] transition-colors font-mono">{{ number_format($stats['teams']) }}+</div>
                <div class="text-xs text-slate-500 font-bold uppercase tracking-wider font-sora">Teams</div>
            </div>
        </a>

        <!-- 4. Venues / Stages -->
        <a href="{{ route('admin.stages.index') }}" class="rounded-2xl bg-white border border-slate-200 p-5 shadow-2xs hover:border-[#be1e2d] transition-all group flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-green-50 text-[#009444] flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900 group-hover:text-[#be1e2d] transition-colors font-mono">{{ number_format($stats['stages']) }}+</div>
                <div class="text-xs text-slate-500 font-bold uppercase tracking-wider font-sora">Venues</div>
            </div>
        </a>
    </div>

    <!-- Main Grid: Performance Over Time Chart + Progress Result & Score Board -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Performance Over Time Multi-line Chart (2 cols) -->
        <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-200 p-6 shadow-2xs">
            @php
                $chartData = $chartData ?? app(\App\Services\PointCalculationService::class)->getPerformanceChartData();
            @endphp
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 tracking-tight font-sora">Performance Over Time</h2>
                    <p class="text-xs text-slate-400 font-medium font-sora mt-0.5">Real-time team points progress</p>
                </div>
                <!-- Legend -->
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    @foreach(($chartData['series'] ?? []) as $ser)
                        <div class="flex items-center gap-1.5">
                            <span class="w-3.5 h-3.5 rounded-xs border border-black/10 shadow-2xs shrink-0" style="background-color: {{ $ser['color'] }}"></span>
                            <span class="text-slate-700 font-semibold font-sora">{{ $ser['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- SVG Multi-Line Chart -->
            <div class="mt-6 w-full overflow-x-auto">
                <div class="min-w-[500px]">
                    <svg viewBox="0 0 750 340" class="w-full h-72 text-xs font-sora select-none">
                        <!-- Horizontal Grid Lines -->
                        <g stroke="#f1f5f9" stroke-width="1.5">
                            @foreach(($chartData['ySteps'] ?? []) as $ys)
                                <line x1="60" y1="{{ $ys['y'] }}" x2="720" y2="{{ $ys['y'] }}" />
                            @endforeach
                        </g>

                        <!-- Y-Axis Labels -->
                        <g fill="#94a3b8" font-size="11" font-family="'Rockwell', 'Sora', sans-serif" font-weight="600" text-anchor="end">
                            @foreach(($chartData['ySteps'] ?? []) as $ys)
                                <text x="50" y="{{ $ys['y'] + 4 }}">{{ number_format($ys['val']) }}</text>
                            @endforeach
                        </g>

                        <!-- Y-axis Title -->
                        <text x="15" y="165" fill="#64748b" font-size="10" font-family="'Sora', sans-serif" font-weight="bold" transform="rotate(-90 15,165)">Scores</text>

                        <!-- X-axis Labels -->
                        <g fill="#64748b" font-size="11" font-family="'Sora', 'Rockwell', sans-serif" font-weight="600" text-anchor="middle">
                            @foreach(($chartData['xSteps'] ?? []) as $xs)
                                <text x="{{ $xs['x'] }}" y="308">{{ $xs['label'] }}</text>
                                @if(!empty($xs['sub']))
                                    <text x="{{ $xs['x'] }}" y="321" font-size="9" fill="#94a3b8">{{ $xs['sub'] }}</text>
                                @endif
                            @endforeach
                        </g>

                        <!-- Trend Lines for each Team -->
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
        </div>

        <!-- Right: Progress Result Card & Score Board (1 col) -->
        <div class="space-y-6">
            <!-- Progress Result Card -->
            <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-2xs">
                <h3 class="text-sm font-bold text-slate-900 tracking-tight mb-4 font-sora">Progress Result</h3>
                
                @php
                    $declaredCount = $stats['published_results'] ?? 0;
                    $totalCompetitions = max(1, $stats['competitions'] ?? 1);
                    $progressPct = round(($declaredCount / $totalCompetitions) * 100, 2);
                @endphp

                <!-- Progress Bar -->
                <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden mb-3">
                    <div class="bg-[#be1e2d] h-3 rounded-full transition-all duration-500" style="width: {{ $progressPct }}%;"></div>
                </div>

                <div class="text-center">
                    <div class="text-xs font-semibold text-slate-700 font-sora">
                        Declared <span class="font-mono font-bold">{{ $declaredCount }}</span> of <span class="font-mono font-bold">{{ $totalCompetitions }}</span> Results
                    </div>
                    <div class="text-[11px] font-bold text-[#be1e2d] mt-0.5 font-mono">
                        Progress {{ $progressPct }}%
                    </div>
                </div>
            </div>

            <!-- Score Board Card -->
            <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-2xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 tracking-tight font-sora">Score Board</h3>
                        <span class="text-[10px] font-sora text-slate-400">Live Status</span>
                    </div>
                    <a href="{{ route('admin.achievements.team-score') }}" class="text-xs font-bold text-[#be1e2d] hover:underline font-sora">View All →</a>
                </div>

                <div class="space-y-2.5">
                    @forelse($leaderboard->take(5) as $rank => $group)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 hover:border-slate-200 transition-colors">
                            <div class="flex items-center gap-2.5">
                                <span class="w-6 h-6 rounded-lg font-bold text-xs flex items-center justify-center font-mono
                                    {{ $rank === 0 ? 'bg-[#f3bd2e]/20 text-[#be1e2d]' : ($rank === 1 ? 'bg-slate-200 text-slate-700' : ($rank === 2 ? 'bg-[#005c94]/10 text-[#005c94]' : 'bg-slate-100 text-slate-500')) }}">
                                    {{ $rank + 1 }}
                                </span>
                                <span class="w-3 h-3 rounded-full shrink-0 border border-black/10 shadow-2xs" style="background-color: {{ $group->color_hex }};"></span>
                                <span class="font-bold text-xs text-slate-800 font-sora">{{ $group->name }}</span>
                            </div>
                            <span class="font-bold text-sm font-mono px-2.5 py-0.5 rounded-lg border shadow-2xs" style="background-color: {{ $group->color_hex }}15; color: {{ $group->color_hex }}; border-color: {{ $group->color_hex }}35;">
                                {{ number_format($group->points_cache) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4 font-sora">No team scores recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Active Stages & Festival Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Stages Overview -->
        <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-200 p-6 shadow-2xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-900 tracking-tight font-sora">Live Stage Monitoring</h3>
                <a href="{{ route('admin.stages.index') }}" class="text-xs font-bold text-[#be1e2d] hover:underline font-sora">Manage Stages →</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($stages as $stage)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono text-xs font-bold text-slate-700">{{ $stage->code }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-sora font-bold {{ $stage->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-200 text-slate-600' }}">
                                {{ strtoupper($stage->status) }}
                            </span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900 truncate font-sora">{{ $stage->name }}</h4>
                        <div class="mt-2 text-xs text-slate-500">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block font-sora">Now Playing:</span>
                            <span class="font-medium text-slate-800 truncate block font-sora">{{ $stage->currentProgram?->name ?? 'Intermission / Sound Check' }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 font-sora">No stages active.</p>
                @endforelse
            </div>
        </div>

        <!-- Central Jury / Quick Links -->
        <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 tracking-tight mb-3 font-sora">Quick Navigation</h3>
                <div class="space-y-2 text-xs">
                    <a href="{{ route('admin.mark-entry.handler') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors">
                        <span class="font-semibold text-slate-700">Programs to Verify</span>
                        <span class="text-[#be1e2d] font-bold">→</span>
                    </a>
                    <a href="{{ route('admin.results.declare') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors">
                        <span class="font-semibold text-slate-700">Declare Results</span>
                        <span class="text-[#be1e2d] font-bold">→</span>
                    </a>
                    <a href="{{ route('admin.forms.call-list') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors">
                        <span class="font-semibold text-slate-700">Stage Call Sheets</span>
                        <span class="text-[#be1e2d] font-bold">→</span>
                    </a>
                    <a href="{{ route('admin.code-letters.index') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors">
                        <span class="font-semibold text-slate-700">Code Letters Handler</span>
                        <span class="text-[#be1e2d] font-bold">→</span>
                    </a>
                </div>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-100">
                <a href="{{ route('admin.settings.index') }}" class="w-full block py-2 text-center rounded-xl text-xs font-bold bg-[#be1e2d]/10 text-[#be1e2d] hover:bg-[#be1e2d]/20 transition-colors">
                    Festival Settings
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
