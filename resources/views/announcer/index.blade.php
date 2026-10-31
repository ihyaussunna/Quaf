@extends('layouts.app')

@section('title', 'QUAF - Announcer Console')

@section('content')
<div class="min-h-screen bg-[#edf3f8] py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto space-y-6">
        
        <!-- Header Console Banner -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-900 text-white">
                        Live Announcer Desk
                    </span>
                    <span class="text-xs text-slate-500 font-mono">Stage Microphone Sync</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight font-sora">
                    Stage Announcement Console
                </h1>
                <p class="text-xs text-slate-600 mt-1 max-w-2xl font-sora">
                    Official competition results verified and transferred from the Admin Panel. After announcing on the stage microphone, click "Mark as Announced" to transfer results directly to the Media Desk.
                </p>
            </div>

            <!-- Live Status & Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('announcer.progression-matrix') }}" class="px-4 py-2.5 rounded-xl bg-indigo-700 hover:bg-indigo-800 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-2xs font-sora">
                    <svg class="w-4 h-4 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Points Matrix (1, 1+2, 1+2+3...)</span>
                </a>
                <a href="{{ route('announcer.print.results') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-2xs font-sora">
                    <svg class="w-4 h-4 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print Declared Results PDF ↗</span>
                </a>
                <a href="{{ route('announcer.stage') }}" class="px-4 py-2.5 rounded-xl bg-[#005c94] hover:bg-[#004b78] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-2xs font-sora">
                    <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg>
                    <span>Stage Calling Console &rarr;</span>
                </a>
                <button type="button" onclick="window.location.reload()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Refresh Now</span>
                </button>
                <div class="flex items-center gap-2 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="autoReloadLabel">Auto-refresh: 20s</span>
                </div>
            </div>
        </div>

        <!-- Flash messages -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm font-semibold flex items-center justify-between shadow-2xs">
                <span class="font-ml">{{ session('success') }}</span>
                <span class="text-xs font-mono text-emerald-700">SUCCESS</span>
            </div>
        @endif

        <!-- Priority: Team Standings Announcements from Admin -->
        @if(isset($pendingStandings) && $pendingStandings->isNotEmpty())
            <div class="space-y-4">
                @foreach($pendingStandings as $standAnn)
                    <div class="bg-gradient-to-r from-red-950 via-slate-900 to-slate-900 text-white rounded-3xl p-6 sm:p-8 border-2 border-red-500 shadow-xl ring-4 ring-red-500/20 font-sora">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-white/10 pb-5">
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-mono font-bold uppercase tracking-wider bg-red-600 text-white animate-pulse flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"></path></svg>
                                        <span>MIC ANNOUNCEMENT READY</span>
                                    </span>
                                    <span class="text-xs font-mono text-amber-300 font-bold">
                                        {{ $standAnn->title }}
                                    </span>
                                    <span class="text-xs font-mono text-slate-400">
                                        Sent by {{ $standAnn->sentBy?->name ?? 'Admin' }} at {{ $standAnn->sent_at ? $standAnn->sent_at->format('h:i A') : '—' }}
                                    </span>
                                </div>
                                <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                                    Team Standings Announcement
                                </h2>
                                <p class="text-xs text-slate-300 mt-1 max-w-2xl font-sora">
                                    Current official house points standings prepared by the Admin Directorate. Review and click "Mark as Announced & Transfer to Media".
                                </p>
                            </div>

                            <form method="POST" action="{{ route('announcer.standings.announced', $standAnn->id) }}" class="shrink-0">
                                @csrf
                                <button type="submit" class="w-full sm:w-auto px-6 py-4 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black rounded-2xl text-sm transition shadow-lg flex items-center justify-center gap-2 uppercase tracking-wider">
                                    <svg class="w-5 h-5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    <span>Mark as Announced & Transfer to Media</span>
                                </button>
                            </form>
                        </div>

                        <!-- Team Standings Ranks Grid -->
                        <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                            @foreach($standAnn->standings_data as $grpItem)
                                <div class="bg-white/10 backdrop-blur-xs p-3.5 rounded-2xl border border-white/10 flex flex-col justify-between">
                                    <div class="flex items-center justify-between text-xs font-mono mb-2">
                                        <span class="px-2 py-0.5 rounded font-black {{ $grpItem['rank'] === 1 ? 'bg-amber-400 text-slate-950' : 'bg-white/20 text-white' }}">
                                            #{{ $grpItem['rank'] }}
                                        </span>
                                        <span class="text-[10px] text-slate-300 uppercase font-bold">{{ $grpItem['code'] }}</span>
                                    </div>
                                    <div>
                                        <div class="font-bold text-white text-sm truncate">{{ $grpItem['name'] }}</div>
                                        <div class="mt-1 flex items-baseline gap-1">
                                            <span class="text-2xl font-black text-amber-300 font-mono">{{ number_format($grpItem['points']) }}</span>
                                            <span class="text-[10px] text-slate-400 uppercase font-mono font-bold">pts</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Filter Stat Tabs -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Ready for announcement -->
            <a href="{{ route('announcer.index', ['tab' => 'ready']) }}" 
               class="p-5 rounded-2xl border transition-all {{ $tab === 'ready' ? 'bg-amber-50 border-amber-300 ring-2 ring-amber-400' : 'bg-white border-slate-200 hover:border-slate-300' }}">
                <div class="flex items-center justify-between text-amber-700 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider font-sora">Awaiting Announcement</span>
                    @if($stats['ready_count'] > 0)
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                    @endif
                </div>
                <div class="text-3xl font-black text-slate-900 font-sora">{{ $stats['ready_count'] }}</div>
                <div class="text-[11px] text-amber-800 font-medium mt-1">Pending Stage Announcement</div>
            </a>

            <!-- Announced -->
            <a href="{{ route('announcer.index', ['tab' => 'announced']) }}" 
               class="p-5 rounded-2xl border transition-all {{ $tab === 'announced' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-400' : 'bg-white border-slate-200 hover:border-slate-300' }}">
                <div class="flex items-center justify-between text-emerald-700 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider font-sora">Already Announced</span>
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div class="text-3xl font-black text-slate-900 font-sora">{{ $stats['announced_count'] }}</div>
                <div class="text-[11px] text-emerald-800 font-medium mt-1">Already Announced on Stage</div>
            </a>

            <!-- Total Results -->
            <a href="{{ route('announcer.index', ['tab' => 'all']) }}" 
               class="p-5 rounded-2xl border transition-all {{ $tab === 'all' ? 'bg-slate-100 border-slate-300 ring-2 ring-slate-400' : 'bg-white border-slate-200 hover:border-slate-300' }}">
                <div class="flex items-center justify-between text-slate-500 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider font-sora">Total Transferred</span>
                    <span class="text-xs font-mono font-bold">{{ $stats['total_count'] }}</span>
                </div>
                <div class="text-3xl font-black text-slate-900 font-sora">{{ $stats['total_count'] }}</div>
                <div class="text-[11px] text-slate-500 font-medium mt-1">Total Available Results</div>
            </a>
        </div>

        <!-- Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('announcer.index', ['tab' => 'ready']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === 'ready' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Awaiting Announcement ({{ $stats['ready_count'] }})
                </a>
                <a href="{{ route('announcer.index', ['tab' => 'announced']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === 'announced' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Announced ({{ $stats['announced_count'] }})
                </a>
                <a href="{{ route('announcer.index', ['tab' => 'all']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    All Results
                </a>
            </div>

            <form method="GET" action="{{ route('announcer.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="relative w-full md:w-64">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search program, code..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-3.5 py-2 bg-slate-900 text-white font-bold text-xs rounded-xl hover:bg-slate-800 transition">
                    Search
                </button>
                @if($search)
                    <a href="{{ route('announcer.index', ['tab' => $tab]) }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 text-xs font-medium rounded-xl hover:bg-slate-100 transition">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        @if($results->isEmpty())
            <!-- Empty state -->
            <div class="bg-white rounded-3xl p-16 text-center border border-slate-200 shadow-xs max-w-3xl mx-auto space-y-3">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"></path></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800 font-sora">
                    @if($tab === 'ready')
                        No programs currently waiting for announcement
                    @else
                        No results found for this view
                    @endif
                </h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    Competition results evaluated and transferred from the Admin Panel will appear here in real time.
                </p>
            </div>
        @else
            <!-- Results Cards List -->
            <div class="space-y-6">
                @foreach($results as $res)
                    @php
                        $isDelivered = in_array($res->status, ['send', 'delivered']);
                        $prog = $res->program;
                        $resNo = sprintf('%02d', $res->result_number);
                        $zoneName = \App\Services\PointCalculationService::toTitleCase($prog?->zone?->name ?? $prog?->eligibility ?? 'All Zones');
                        
                        $podiumWithTies = $res->getPodiumWinnersWithTies();
                        $w1List = $podiumWithTies[1];
                        $w2List = $podiumWithTies[2];
                        $w3List = $podiumWithTies[3];
                        $otherGraded = $res->getOtherGradeHolders();
                    @endphp

                    <div class="bg-white rounded-3xl p-6 sm:p-8 border {{ $isDelivered ? 'border-amber-300 ring-4 ring-amber-100/70 shadow-md' : 'border-slate-200 shadow-xs' }} transition">
                        
                        <!-- Top Header Section of Card -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <!-- Result Number Badge -->
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl font-mono text-xs font-black bg-[#be1e2d] text-white shadow-xs tracking-wider">
                                        <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                                        <span>Result #{{ $resNo }}</span>
                                    </span>

                                    <span class="px-2.5 py-1 rounded-xl font-mono text-xs font-bold bg-slate-900 text-white">
                                        {{ $prog?->code ?: 'PROG' }}
                                    </span>
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold {{ $isDelivered ? 'bg-amber-100 text-amber-900 border border-amber-300 animate-pulse' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' }}">
                                        {{ $isDelivered ? 'READY FOR MIC ANNOUNCEMENT' : 'ANNOUNCED ON STAGE' }}
                                    </span>
                                    <!-- Zone Badge -->
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200 font-mono">
                                        <span class="text-blue-500 font-normal">Zone:</span> {{ $zoneName }}
                                    </span>
                                    <span class="text-xs text-slate-500 font-semibold">
                                        {{ $prog?->is_stage ? 'Stage Event' : 'Non-stage Event' }} • {{ $prog?->stage?->name ?? 'Main Stage' }}
                                    </span>
                                </div>
                                <h3 class="text-2xl font-black text-slate-900 font-sora flex items-center gap-2">
                                    <span class="text-[#be1e2d] font-mono">#{{ $resNo }}</span>
                                    <span>{{ \App\Services\PointCalculationService::toTitleCase($prog?->name) }}</span>
                                </h3>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('announcer.print.evaluation', $prog->id) }}" target="_blank" 
                                   class="px-3.5 py-2.5 rounded-xl border border-purple-300 bg-purple-50 hover:bg-purple-100 text-purple-900 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs font-sora">
                                    <svg class="w-4 h-4 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>Scorecard PDF ↗</span>
                                </a>

                                @if($isDelivered)
                                    <form method="POST" action="{{ route('announcer.announced', $res->id) }}">
                                        @csrf
                                        <button type="submit" class="w-full sm:w-auto px-6 py-3.5 bg-[#be1e2d] hover:bg-[#a01624] text-white rounded-2xl text-sm font-bold transition shadow-sm flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                            <span>Mark as Announced</span>
                                        </button>
                                    </form>
                                @else
                                    <div class="flex flex-col sm:items-end gap-1">
                                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Announced on Stage
                                        </span>
                                        <span class="text-[11px] font-semibold text-slate-500">
                                            {{ $res->is_media_published ? 'Media Poster Published' : 'Transferred to Media Desk' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Winners Podium Details with Total Evaluation Marks & Grade Points (Supports Shared Winners/Ties) -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-5">
                            @php
                                $podiumCols = [
                                    1 => [
                                        'label' => '1st Place',
                                        'list' => $w1List,
                                        'bg' => 'bg-amber-50/70',
                                        'border' => 'border-amber-200',
                                        'header_text' => 'text-amber-800',
                                        'pts_badge' => 'bg-amber-200 text-amber-950',
                                        'mark_border' => 'border-amber-300/80',
                                        'mark_bg' => 'bg-amber-100 text-slate-950',
                                        'pos_bg' => 'bg-amber-100 text-amber-900 border border-amber-300',
                                    ],
                                    2 => [
                                        'label' => '2nd Place',
                                        'list' => $w2List,
                                        'bg' => 'bg-slate-50',
                                        'border' => 'border-slate-200',
                                        'header_text' => 'text-slate-700',
                                        'pts_badge' => 'bg-slate-200 text-slate-800',
                                        'mark_border' => 'border-slate-300/80',
                                        'mark_bg' => 'bg-slate-100 text-slate-950',
                                        'pos_bg' => 'bg-slate-200 text-slate-800 border border-slate-300',
                                    ],
                                    3 => [
                                        'label' => '3rd Place',
                                        'list' => $w3List,
                                        'bg' => 'bg-red-50/60',
                                        'border' => 'border-red-200',
                                        'header_text' => 'text-[#be1e2d]',
                                        'pts_badge' => 'bg-red-100 text-[#be1e2d]',
                                        'mark_border' => 'border-red-200',
                                        'mark_bg' => 'bg-red-50 text-[#be1e2d]',
                                        'pos_bg' => 'bg-red-100 text-red-900 border border-red-200',
                                    ],
                                ];
                            @endphp

                            @foreach($podiumCols as $posNum => $col)
                                <div class="space-y-3">
                                    @forelse($col['list'] as $wIdx => $w)
                                        <div class="p-4 rounded-2xl {{ $col['bg'] }} border {{ $col['border'] }} flex flex-col justify-between shadow-2xs">
                                            <div>
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-xs font-extrabold uppercase tracking-wider {{ $col['header_text'] }} font-sora">{{ $col['label'] }}</span>
                                                        @if(count($col['list']) > 1)
                                                            <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-amber-200 text-amber-950">Shared ({{ $wIdx + 1 }}/{{ count($col['list']) }})</span>
                                                        @endif
                                                    </div>
                                                    <span class="text-xs font-black font-mono px-2 py-0.5 rounded {{ $col['pts_badge'] }}">
                                                        {{ $w['total_points'] }} PTS
                                                    </span>
                                                </div>
                                                <div class="font-bold text-slate-900 text-base font-sora truncate">
                                                    {{ \App\Services\PointCalculationService::toTitleCase($w['name']) }}
                                                </div>
                                                <div class="text-xs text-slate-600 mt-0.5">
                                                    Chest: <strong class="font-mono text-slate-900">#{{ $w['chest'] }}</strong> • Team: <strong class="text-slate-800">{{ \App\Services\PointCalculationService::toTitleCase($w['team']) }}</strong>
                                                </div>

                                                <!-- Total Evaluation Mark -->
                                                <div class="mt-2.5 px-3 py-1.5 rounded-xl bg-white border {{ $col['mark_border'] }} flex items-center justify-between shadow-2xs">
                                                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-600">Total Mark:</span>
                                                    <div class="text-right">
                                                        <span class="text-xs font-mono font-black {{ $col['mark_bg'] }} px-2 py-0.5 rounded border border-slate-200">
                                                            {{ ($w['avg_marks'] > 0 || $w['total_marks'] > 0) ? number_format($w['avg_marks'], 2) : '—' }}
                                                        </span>
                                                        @if($w['judge_count'] > 1)
                                                            <span class="block text-[10px] font-mono text-slate-500 mt-0.5">Sum: {{ number_format($w['total_marks'], 1) }} ({{ $w['judge_count'] }} Judges)</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Points Breakdown -->
                                            <div class="mt-3 pt-2.5 border-t {{ $col['border'] }} flex flex-wrap items-center gap-1.5 text-xs">
                                                @if($w['grade'])
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded font-mono font-bold bg-emerald-600 text-white shadow-2xs">
                                                        Grade {{ $w['grade'] }}
                                                    </span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded font-mono font-bold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                                        +{{ $w['grade_points'] }} Grade Pts
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded font-mono text-slate-500 bg-white/70 border border-slate-200">
                                                        No Grade
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center px-2 py-0.5 rounded font-mono font-semibold {{ $col['pos_bg'] }}">
                                                    +{{ $w['position_points'] }} Pos Pts
                                                </span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-4 rounded-2xl {{ $col['bg'] }} border {{ $col['border'] }} text-center text-xs text-slate-400 italic font-mono py-8">
                                            {{ $col['label'] }} — Not declared
                                        </div>
                                    @endforelse
                                </div>
                            @endforeach
                        </div>

                        <!-- Other Grade Holders (if any) -->
                        @if($otherGraded->isNotEmpty())
                            <div class="mt-4 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-wrap items-center gap-2">
                                <span class="text-xs font-bold text-slate-700">Other Grade Holders:</span>
                                @foreach($otherGraded as $og)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white border border-slate-200 text-xs font-mono shadow-2xs">
                                        <strong class="text-slate-900">{{ \App\Services\PointCalculationService::toTitleCase($og->student?->name ?: 'Chest #'.$og->chest_number) }}</strong>
                                        <span class="text-slate-500">({{ \App\Services\PointCalculationService::toTitleCase($og->group?->name ?? $og->student?->group?->name) }})</span>
                                        @if(($og->computed_avg_score ?? 0) > 0)
                                            <span class="px-1.5 py-0.5 rounded font-bold bg-purple-100 text-purple-900 border border-purple-200">Mark: {{ number_format($og->computed_avg_score, 1) }}</span>
                                        @endif
                                        <span class="px-1.5 py-0.5 rounded font-bold bg-emerald-100 text-emerald-800">Grade {{ $og->computed_grade }} (+{{ $og->computed_grade_points }} pts)</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- View All Candidate Marks & Jury Valuation Accordion -->
                        @php
                            $allEntriesMarks = $res->getAllEntriesWithMarks();
                        @endphp
                        @if($allEntriesMarks->isNotEmpty())
                            <div class="mt-4 pt-3 border-t border-slate-100" x-data="{ showScorecard: false }">
                                <div class="flex items-center justify-between">
                                    <button type="button" @click="showScorecard = !showScorecard" 
                                            class="text-xs font-bold text-purple-700 hover:text-purple-900 flex items-center gap-1.5 transition">
                                        <svg class="w-4 h-4 transition-transform" :class="showScorecard ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <span>Candidate Valuation Marks ({{ $allEntriesMarks->count() }} Participants)</span>
                                    </button>
                                    <a href="{{ route('announcer.print.evaluation', $prog->id) }}" target="_blank" class="text-xs font-mono font-bold text-purple-700 hover:text-purple-900 flex items-center gap-1">
                                        <span>Print Scorecard PDF &rarr;</span>
                                    </a>
                                </div>

                                <div x-show="showScorecard" x-cloak class="mt-3 overflow-x-auto border border-slate-200 rounded-2xl bg-white shadow-2xs">
                                    <table class="w-full text-left text-xs font-sora">
                                        <thead class="bg-slate-50 text-slate-700 uppercase font-mono text-[10px] border-b border-slate-200">
                                            <tr>
                                                <th class="py-2.5 px-3">Chest / Candidate</th>
                                                <th class="py-2.5 px-3">Team</th>
                                                <th class="py-2.5 px-3 text-center">Judges</th>
                                                <th class="py-2.5 px-3 text-center">Total Mark</th>
                                                <th class="py-2.5 px-3 text-center">Average Mark</th>
                                                <th class="py-2.5 px-3 text-center">Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($allEntriesMarks as $cEntry)
                                                <tr class="hover:bg-slate-50/80 transition">
                                                    <td class="py-2.5 px-3 font-semibold text-slate-900">
                                                        <span class="font-mono font-black text-slate-900">#{{ $cEntry->chest_number }}</span>
                                                        <span class="ml-1 text-slate-700">{{ \App\Services\PointCalculationService::toTitleCase($cEntry->student?->name) }}</span>
                                                    </td>
                                                    <td class="py-2.5 px-3 text-slate-600 font-medium">
                                                        {{ \App\Services\PointCalculationService::toTitleCase($cEntry->group?->name ?? $cEntry->student?->group?->name ?? '—') }}
                                                    </td>
                                                    <td class="py-2.5 px-3 text-center font-mono text-slate-500">
                                                        {{ $cEntry->computed_judge_count }}
                                                    </td>
                                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-800">
                                                        {{ number_format($cEntry->computed_total_marks, 1) }}
                                                    </td>
                                                    <td class="py-2.5 px-3 text-center">
                                                        <span class="px-2 py-0.5 rounded font-mono font-black {{ $cEntry->computed_avg_score >= 70 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                                                            {{ number_format($cEntry->computed_avg_score, 2) }}
                                                        </span>
                                                    </td>
                                                    <td class="py-2.5 px-3 text-center font-mono font-bold">
                                                        @if($cEntry->computed_grade)
                                                            <span class="px-2 py-0.5 rounded bg-emerald-600 text-white text-[11px]">
                                                                Grade {{ $cEntry->computed_grade }}
                                                            </span>
                                                        @else
                                                            <span class="text-slate-400">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- Quick Grade Management Accordion / Form -->
                        <div class="mt-4 pt-3 border-t border-slate-100" x-data="{ open: false }">
                            <div class="flex items-center justify-between">
                                <button type="button" @click="open = !open" 
                                        class="text-xs font-bold text-slate-600 hover:text-slate-900 flex items-center gap-1.5 transition">
                                    <svg class="w-4 h-4 text-slate-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    <span>Edit / Adjust Grades</span>
                                </button>
                                <span class="text-[11px] font-mono text-slate-400">Official Grade Points: A+=6, A=5, B=3, C=1 pts</span>
                            </div>

                            <form method="POST" action="{{ route('announcer.update-grades', $res->id) }}" x-show="open" x-cloak class="mt-3 p-4 rounded-2xl bg-slate-50 border border-slate-200 grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                                @csrf
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">1st Place Grade</label>
                                    <select name="first_grade" class="w-full bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs font-mono font-bold text-slate-800 focus:outline-none focus:border-[#be1e2d]">
                                        <option value="NO_GRADE" {{ empty($res->first_grade) ? 'selected' : '' }}>No Grade (0 pts)</option>
                                        <option value="A+" {{ $res->first_grade === 'A+' ? 'selected' : '' }}>Grade A+ (+6 pts)</option>
                                        <option value="A" {{ $res->first_grade === 'A' ? 'selected' : '' }}>Grade A (+5 pts)</option>
                                        <option value="B" {{ $res->first_grade === 'B' ? 'selected' : '' }}>Grade B (+3 pts)</option>
                                        <option value="C" {{ $res->first_grade === 'C' ? 'selected' : '' }}>Grade C (+1 pt)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">2nd Place Grade</label>
                                    <select name="second_grade" class="w-full bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs font-mono font-bold text-slate-800 focus:outline-none focus:border-[#be1e2d]">
                                        <option value="NO_GRADE" {{ empty($res->second_grade) ? 'selected' : '' }}>No Grade (0 pts)</option>
                                        <option value="A+" {{ $res->second_grade === 'A+' ? 'selected' : '' }}>Grade A+ (+6 pts)</option>
                                        <option value="A" {{ $res->second_grade === 'A' ? 'selected' : '' }}>Grade A (+5 pts)</option>
                                        <option value="B" {{ $res->second_grade === 'B' ? 'selected' : '' }}>Grade B (+3 pts)</option>
                                        <option value="C" {{ $res->second_grade === 'C' ? 'selected' : '' }}>Grade C (+1 pt)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">3rd Place Grade</label>
                                    <select name="third_grade" class="w-full bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs font-mono font-bold text-slate-800 focus:outline-none focus:border-[#be1e2d]">
                                        <option value="NO_GRADE" {{ empty($res->third_grade) ? 'selected' : '' }}>No Grade (0 pts)</option>
                                        <option value="A+" {{ $res->third_grade === 'A+' ? 'selected' : '' }}>Grade A+ (+6 pts)</option>
                                        <option value="A" {{ $res->third_grade === 'A' ? 'selected' : '' }}>Grade A (+5 pts)</option>
                                        <option value="B" {{ $res->third_grade === 'B' ? 'selected' : '' }}>Grade B (+3 pts)</option>
                                        <option value="C" {{ $res->third_grade === 'C' ? 'selected' : '' }}>Grade C (+1 pt)</option>
                                    </select>
                                </div>
                                <div>
                                    <button type="submit" class="w-full py-1.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-xs uppercase tracking-wider transition">
                                        Save Grades
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Note regarding Media Sync -->
                        @if($isDelivered)
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                                <span>After announcing on the microphone, click the button above to transfer this result directly to the Media Desk.</span>
                                <span class="font-mono text-slate-400">Step 2 of 3: Stage Announcement</span>
                            </div>
                        @else
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                                <span>Transferred to Media Desk for official poster publishing and social media dispatch.</span>
                                <span class="font-mono text-emerald-600 font-semibold">Announced</span>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>

            @if($results->hasPages())
                <div class="p-4 bg-white rounded-2xl border border-slate-200">
                    {{ $results->links() }}
                </div>
            @endif
        @endif

    </div>
</div>

<script>
    // Auto-reload feature for stage announcer
    let countdown = 20;
    const label = document.getElementById('autoReloadLabel');
    if (label) {
        setInterval(() => {
            countdown--;
            if (countdown <= 0) {
                window.location.reload();
            } else {
                label.innerText = 'Auto-refresh: ' + countdown + 's';
            }
        }, 1000);
    }
</script>
@endsection
