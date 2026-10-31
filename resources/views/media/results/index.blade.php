@extends('layouts.media')

@section('title', 'Results & Poster Studio | QUAF Media')

@section('content')
<div x-data="{
    uploadModalOpen: false,
    selectedResultId: '',
    programName: '',
    programCode: '',
    zoneName: '',
    resultNo: '',
    formAction: '',
    isUploading: false,
    previewUrl: null,
    fileName: '',
    fileSize: '',
    isDragging: false,
    availableResults: [
        @foreach($results as $r)
        {
            id: {{ $r->id }},
            code: '{{ $r->program->code ?? 'PROG' }}',
            name: '{{ addslashes($r->program->name) }}',
            zone: '{{ addslashes($r->program->zone?->name ?? $r->program->eligibility ?? 'All Zones') }}',
            result_no: '{{ sprintf('%02d', $r->result_number) }}',
            action: '{{ route('media.results.save-poster', $r) }}'
        },
        @endforeach
    ],
    init() {
        this.$watch('uploadModalOpen', (val) => {
            window.__isModalActive = !!val;
        });
    },
    openGeneralUploadModal() {
        if (!this.availableResults.length) {
            alert('No announced results available for upload.');
            return;
        }
        const first = this.availableResults[0];
        this.openUploadModal(first.id, first.name, first.code, first.zone, first.result_no, first.action);
    },
    openUploadModal(id, name, code, zone, resNo, action) {
        this.selectedResultId = id;
        this.programName = name;
        this.programCode = code;
        this.zoneName = zone;
        this.resultNo = resNo;
        this.formAction = action;
        this.previewUrl = null;
        this.fileName = '';
        this.fileSize = '';
        this.isUploading = false;
        this.isDragging = false;
        const input = document.getElementById('posterFileInput');
        if (input) input.value = '';
        this.uploadModalOpen = true;
    },
    onResultSelected(id) {
        const item = this.availableResults.find(r => r.id == id);
        if (item) {
            this.selectedResultId = item.id;
            this.programName = item.name;
            this.programCode = item.code;
            this.zoneName = item.zone;
            this.resultNo = item.result_no;
            this.formAction = item.action;
        }
    },
    handleFile(file) {
        if (!file) return;
        if (!file.type.match('image.*')) {
            alert('Please select a valid image file (PNG, JPG, WEBP).');
            return;
        }
        this.fileName = file.name;
        this.fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        const reader = new FileReader();
        reader.onload = (e) => {
            this.previewUrl = e.target.result;
        };
        reader.readAsDataURL(file);
    },
    onFileChange(e) {
        const file = e.target.files && e.target.files[0];
        this.handleFile(file);
    },
    onDrop(e) {
        this.isDragging = false;
        const file = e.dataTransfer.files && e.dataTransfer.files[0];
        if (file) {
            const input = document.getElementById('posterFileInput');
            if (input) {
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
            }
            this.handleFile(file);
        }
    }
}" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-900 text-white">
                    Result Studio
                </span>
                <span class="text-xs text-slate-500 font-mono">Stage Announcer & Media Sync</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-2 font-sora">
                Result Publishing & Poster Studio
            </h1>
            <p class="text-xs text-slate-600 mt-1 max-w-2xl">
                Once announced by stage coordinators, results appear here. Create and publish official podium posters (1st, 2nd, and 3rd winners only) to the public festival portal.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <a href="{{ route('media.print.progression-matrix') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-purple-50 border border-purple-200 text-purple-700 hover:bg-purple-100 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs" title="Print Sequential Points Progression Matrix">
                <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Points Matrix PDF</span>
            </a>
            <button type="button" @click="openGeneralUploadModal()" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Upload Poster</span>
            </button>
            <button type="button" onclick="window.location.reload()" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                <span>Refresh</span>
            </button>
            <div class="flex items-center gap-2 bg-slate-50 px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span id="autoReloadLabel">Auto-refresh: 25s</span>
            </div>
            <a href="{{ route('media.results.templates') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors">
                Templates
            </a>
        </div>
    </div>


    <!-- Dedicated Section: Total Announced Competitions Live Team Standings -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 text-white rounded-3xl p-6 sm:p-7 border-2 border-indigo-500/40 shadow-xl ring-4 ring-indigo-500/10 font-sora" x-data="{ showFullTable: false }">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 border-b border-white/10 pb-5">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-3 py-1 rounded-full text-xs font-mono font-bold uppercase tracking-wider bg-indigo-600 text-white flex items-center gap-1.5 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <span>Announcer Sync Leaderboard</span>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-400 text-slate-950">
                        {{ $totalAnnouncedStandings['milestone_label'] }}
                    </span>
                    <span class="text-xs font-mono text-slate-300">
                        ആകെ അനൗൺസ് ചെയ്ത മത്സരങ്ങൾ: <strong class="text-white font-bold">{{ $totalAnnouncedCount }}</strong>
                    </span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                    അനൗൺസർക്ക് അയച്ച മുഴുവൻ മത്സരങ്ങളുടെയും ടീം പോയിന്റ് നില
                </h2>
                <p class="text-xs text-slate-300 mt-1 max-w-2xl font-ml">
                    അനൗൺസർ ഡെസ്കിലേക്ക് കൈമാറിയ ആകെ {{ $totalAnnouncedCount }} മത്സരങ്ങളുടെ യഥാർത്ഥ പോയിന്റുകൾ അടിസ്ഥാനമാക്കിയുള്ള ലൈവ് ടീം നിലയാണിത്.
                </p>
            </div>

            <!-- Action Buttons for Announced Standings -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('media.print.milestone-standings', ['count' => $totalAnnouncedCount]) }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition flex items-center gap-1.5 border border-white/15 shadow-2xs font-mono">
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print Standings PDF ↗</span>
                </a>

                <form method="POST" action="{{ route('media.results.publish-standings') }}" class="inline">
                    @csrf
                    <input type="hidden" name="results_count" value="{{ $totalAnnouncedCount }}">
                    <input type="hidden" name="custom_label" value="{{ $totalAnnouncedStandings['milestone_label'] }}">
                    <button type="submit" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black rounded-xl text-xs uppercase tracking-wider transition shadow-lg flex items-center gap-2 font-mono cursor-pointer">
                        <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Publish {{ $totalAnnouncedStandings['milestone_label'] }}</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Team Standings Live Podium Strip -->
        <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @foreach($totalAnnouncedStandings['standings'] as $tItem)
                <div class="bg-white/10 backdrop-blur-xs p-3.5 rounded-2xl border border-white/10 flex flex-col justify-between hover:bg-white/15 transition">
                    <div class="flex items-center justify-between text-xs font-mono mb-2">
                        <span class="px-2 py-0.5 rounded font-black {{ $tItem['rank'] === 1 ? 'bg-amber-400 text-slate-950' : ($tItem['rank'] === 2 ? 'bg-slate-200 text-slate-900' : ($tItem['rank'] === 3 ? 'bg-amber-600 text-white' : 'bg-white/20 text-white')) }}">
                            #{{ $tItem['rank'] }}
                        </span>
                        <span class="text-[10px] text-slate-300 font-bold uppercase">{{ $tItem['code'] }}</span>
                    </div>
                    <div>
                        <div class="font-bold text-white text-sm truncate">{{ $tItem['name'] }}</div>
                        <div class="mt-1 flex items-baseline gap-1.5">
                            <span class="text-2xl font-black text-amber-300 font-mono">{{ number_format($tItem['points']) }}</span>
                            <span class="text-[10px] text-slate-300 uppercase font-mono font-bold">pts</span>
                        </div>
                        <div class="mt-1.5 text-[10px] font-mono text-slate-400 flex items-center gap-2 border-t border-white/10 pt-1">
                            <span>1st: <strong class="text-slate-200">{{ $tItem['first_count'] }}</strong></span>
                            <span>2nd: <strong class="text-slate-200">{{ $tItem['second_count'] }}</strong></span>
                            <span>3rd: <strong class="text-slate-200">{{ $tItem['third_count'] }}</strong></span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Toggle Full Breakdown Table -->
        <div class="mt-4 pt-3 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <button type="button" @click="showFullTable = !showFullTable" class="text-xs font-bold text-indigo-300 hover:text-white flex items-center gap-1.5 transition cursor-pointer">
                <svg class="w-4 h-4 transition-transform" :class="showFullTable ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                <span x-text="showFullTable ? 'Hide Complete Team Score Breakdown' : 'View Complete Team Score Breakdown (Position & Grade Pts)'"></span>
            </button>
            <div class="flex items-center gap-2 text-slate-400 font-mono text-[11px]">
                <span>Active Public Site Milestone:</span>
                <strong class="text-emerald-400">{{ $standingsInfo['public_standings_label'] }}</strong>
                <a href="{{ route('media.standings.index') }}" class="ml-2 text-indigo-300 hover:text-white underline">
                    Edit Standings &rarr;
                </a>
            </div>
        </div>

        <!-- Detailed Table -->
        <div x-show="showFullTable" x-cloak class="mt-3 overflow-x-auto rounded-2xl border border-white/15 bg-black/30">
            <table class="w-full text-left text-xs font-sora">
                <thead class="bg-white/10 text-slate-300 uppercase font-mono text-[10px] border-b border-white/10">
                    <tr>
                        <th class="py-2.5 px-3">Rank</th>
                        <th class="py-2.5 px-3">Team Name</th>
                        <th class="py-2.5 px-3 text-center">1st</th>
                        <th class="py-2.5 px-3 text-center">2nd</th>
                        <th class="py-2.5 px-3 text-center">3rd</th>
                        <th class="py-2.5 px-3 text-center">Position Pts</th>
                        <th class="py-2.5 px-3 text-center">Grade Pts</th>
                        <th class="py-2.5 px-3 text-right">Total Points</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 font-mono">
                    @foreach($totalAnnouncedStandings['standings'] as $tItem)
                        <tr class="hover:bg-white/5 transition">
                            <td class="py-2.5 px-3 font-bold">
                                <span class="px-2 py-0.5 rounded font-black {{ $tItem['rank'] === 1 ? 'bg-amber-400 text-slate-950' : 'bg-white/20 text-white' }}">
                                    #{{ $tItem['rank'] }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 font-sora font-bold text-white">
                                {{ $tItem['name'] }} <span class="text-slate-400 text-xs">({{ $tItem['code'] }})</span>
                            </td>
                            <td class="py-2.5 px-3 text-center text-amber-300 font-bold">{{ $tItem['first_count'] }}</td>
                            <td class="py-2.5 px-3 text-center text-slate-300 font-bold">{{ $tItem['second_count'] }}</td>
                            <td class="py-2.5 px-3 text-center text-amber-500 font-bold">{{ $tItem['third_count'] }}</td>
                            <td class="py-2.5 px-3 text-center text-slate-300">{{ number_format($tItem['position_points']) }}</td>
                            <td class="py-2.5 px-3 text-center text-emerald-300">{{ number_format($tItem['grade_points']) }}</td>
                            <td class="py-2.5 px-3 text-right text-base font-black text-amber-300">
                                {{ number_format($tItem['points']) }} pts
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stat Badges -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Announced Awaiting Poster -->
        <a href="{{ route('media.results.index', ['tab' => 'announced']) }}" 
           class="p-5 rounded-2xl border transition-all {{ $tab === 'announced' ? 'bg-amber-50 border-amber-300 ring-2 ring-amber-400' : 'bg-white border-slate-200 hover:border-slate-300' }}">
            <div class="flex items-center justify-between text-amber-700 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider">Awaiting Poster</span>
                @if($stats['announced_count'] > 0)
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                @endif
            </div>
            <div class="text-3xl font-black text-slate-900">{{ $stats['announced_count'] }}</div>
            <div class="text-[11px] text-amber-800 font-medium mt-1">Announced by stage, awaiting poster design</div>
        </a>

        <!-- Published Posters -->
        <a href="{{ route('media.results.index', ['tab' => 'published']) }}" 
           class="p-5 rounded-2xl border transition-all {{ $tab === 'published' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-400' : 'bg-white border-slate-200 hover:border-slate-300' }}">
            <div class="flex items-center justify-between text-emerald-700 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider">Published Posters</span>
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div class="text-3xl font-black text-slate-900">{{ $stats['published_count'] }}</div>
            <div class="text-[11px] text-emerald-800 font-medium mt-1">Official results published with poster</div>
        </a>

        <!-- Total Results -->
        <a href="{{ route('media.results.index', ['tab' => 'all']) }}" 
           class="p-5 rounded-2xl border transition-all {{ $tab === 'all' ? 'bg-slate-100 border-slate-300 ring-2 ring-slate-400' : 'bg-white border-slate-200 hover:border-slate-300' }}">
            <div class="flex items-center justify-between text-slate-500 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider">All Festival Results</span>
                <span class="text-xs font-mono font-bold">{{ $stats['total_count'] }}</span>
            </div>
            <div class="text-3xl font-black text-slate-900">{{ $stats['total_count'] }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-1">All officially declared competition results</div>
        </a>
    </div>

    <!-- Search & Tabs -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('media.results.index', ['tab' => 'announced']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === 'announced' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                New from Announcer
                @if($stats['announced_count'] > 0)
                    <span class="ml-1.5 px-1.5 py-0.5 rounded-full bg-white text-amber-800 text-[10px]">{{ $stats['announced_count'] }}</span>
                @endif
            </a>
            <a href="{{ route('media.results.index', ['tab' => 'published']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === 'published' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Published Posters
            </a>
            <a href="{{ route('media.results.index', ['tab' => 'all']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                All Results
            </a>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Numerical Order Selector -->
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-mono font-bold">
                <span class="text-[10px] text-slate-500 uppercase px-1.5 font-bold">Order:</span>
                <a href="{{ route('media.results.index', array_merge(request()->query(), ['order' => 'desc'])) }}" 
                   class="px-2.5 py-1 rounded-lg transition {{ $sortOrder !== 'asc' ? 'bg-[#be1e2d] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    #15 &rarr; #01 (Latest First)
                </a>
                <a href="{{ route('media.results.index', array_merge(request()->query(), ['order' => 'asc'])) }}" 
                   class="px-2.5 py-1 rounded-lg transition {{ $sortOrder === 'asc' ? 'bg-[#be1e2d] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    #01 &rarr; #15 (Sequential)
                </a>
            </div>

            <form method="GET" action="{{ route('media.results.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="hidden" name="order" value="{{ $sortOrder }}">
                <div class="relative w-full md:w-56">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search program, code..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-3.5 py-2 bg-slate-900 text-white font-bold text-xs rounded-xl hover:bg-slate-800 transition-colors">
                    Search
                </button>
                @if($search)
                    <a href="{{ route('media.results.index', ['tab' => $tab, 'order' => $sortOrder]) }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 text-xs font-medium rounded-xl hover:bg-slate-100 transition-colors">
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Results Cards Grid -->
    @if($results->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($results as $res)
                @php
                    $prog = $res->program;
                    $winners = $res->getPosterWinners();
                    $resNo = sprintf('%02d', $res->result_number);
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div>
                        <!-- Header Bar of Card -->
                        <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-xl font-mono text-xs font-black bg-[#be1e2d] text-white shadow-xs tracking-wider">
                                    Result #{{ $resNo }}
                                </span>
                                <span class="px-2 py-0.5 rounded font-mono text-[11px] font-bold bg-slate-900 text-white">
                                    {{ $prog->code ?? 'PROG' }}
                                </span>
                                <span class="text-[11px] font-bold text-slate-600 uppercase">
                                    {{ $prog->eligibility ?? 'General' }}
                                </span>
                            </div>

                            @if($res->is_media_published)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    Poster Published
                                </span>
                            @elseif($res->status === 'announced')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 animate-pulse">
                                    Ready for Poster
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                                    {{ ucfirst($res->status) }}
                                </span>
                            @endif
                        </div>

                        <!-- Content Info -->
                        <div class="p-5 space-y-4">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-mono font-black text-[#be1e2d]">#{{ $resNo }}</span>
                                    <h3 class="text-base font-bold text-slate-900 leading-snug">{{ $prog->name }}</h3>
                                </div>
                                @if($prog->malayalam_name)
                                    <div class="text-xs text-slate-500 font-ml mt-0.5">{{ $prog->malayalam_name }}</div>
                                @endif
                            </div>

                            <!-- Podium Winners (Supports Ties / Shared Winners) -->
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Podium Winners</div>
                                
                                <!-- 1st Place -->
                                @if(!empty($winners['first']))
                                    @foreach($winners['first'] as $wFirst)
                                        <div class="flex items-center gap-2.5">
                                            <img src="{{ asset('images/medals/first.png') }}" alt="1st" class="w-5 h-5 object-contain shrink-0">
                                            <div class="truncate flex items-center gap-1.5 min-w-0">
                                                <span class="font-bold text-slate-900 truncate">{{ $wFirst['name'] }}</span>
                                                <span class="text-slate-500 text-[11px] shrink-0">({{ $wFirst['unit'] }})</span>
                                                @if(count($winners['first']) > 1)
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-mono font-bold bg-amber-200 text-amber-950 shrink-0">Shared</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex items-center gap-2.5">
                                        <img src="{{ asset('images/medals/first.png') }}" alt="1st" class="w-5 h-5 object-contain shrink-0">
                                        <span class="text-slate-400 italic text-[11px]">Not declared</span>
                                    </div>
                                @endif

                                <!-- 2nd Place -->
                                @if(!empty($winners['second']))
                                    @foreach($winners['second'] as $wSecond)
                                        <div class="flex items-center gap-2.5">
                                            <img src="{{ asset('images/medals/second.png') }}" alt="2nd" class="w-5 h-5 object-contain shrink-0">
                                            <div class="truncate flex items-center gap-1.5 min-w-0">
                                                <span class="font-bold text-slate-900 truncate">{{ $wSecond['name'] }}</span>
                                                <span class="text-slate-500 text-[11px] shrink-0">({{ $wSecond['unit'] }})</span>
                                                @if(count($winners['second']) > 1)
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-mono font-bold bg-slate-200 text-slate-800 shrink-0">Shared</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex items-center gap-2.5">
                                        <img src="{{ asset('images/medals/second.png') }}" alt="2nd" class="w-5 h-5 object-contain shrink-0">
                                        <span class="text-slate-400 italic text-[11px]">Not declared</span>
                                    </div>
                                @endif

                                <!-- 3rd Place -->
                                @if(!empty($winners['third']))
                                    @foreach($winners['third'] as $wThird)
                                        <div class="flex items-center gap-2.5">
                                            <img src="{{ asset('images/medals/third.png') }}" alt="3rd" class="w-5 h-5 object-contain shrink-0">
                                            <div class="truncate flex items-center gap-1.5 min-w-0">
                                                <span class="font-bold text-slate-900 truncate">{{ $wThird['name'] }}</span>
                                                <span class="text-slate-500 text-[11px] shrink-0">({{ $wThird['unit'] }})</span>
                                                @if(count($winners['third']) > 1)
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-mono font-bold bg-red-100 text-[#be1e2d] shrink-0">Shared</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex items-center gap-2.5">
                                        <img src="{{ asset('images/medals/third.png') }}" alt="3rd" class="w-5 h-5 object-contain shrink-0">
                                        <span class="text-slate-400 italic text-[11px]">Not declared</span>
                                    </div>
                                @endif
                            </div>

                            @if($res->poster_image)
                                <div class="relative rounded-xl overflow-hidden aspect-video bg-slate-900 border border-slate-200 group">
                                    <img src="{{ $res->poster_image }}" alt="Generated Poster" class="w-full h-full object-cover">
                                    <a href="{{ route('media.results.public-poster', $res) }}" target="_blank" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold">
                                        View Poster Fullscreen
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="p-4 bg-slate-50/70 border-t border-slate-100 space-y-2">
                        <!-- Primary Action Row -->
                        <div class="flex items-center gap-2">
                            <a href="{{ route('media.results.studio', $res) }}" class="flex-1 text-center py-2 px-3 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs transition-colors shadow-xs">
                                {{ $res->poster_image ? 'Edit in Studio' : 'Create & Design Poster' }}
                            </a>
                            <button type="button" 
                                    @click="openUploadModal({{ $res->id }}, '{{ addslashes($prog->name) }}', '{{ $prog->code }}', '{{ addslashes($prog->zone?->name ?? $prog->eligibility ?? 'All Zones') }}', '{{ sprintf('%02d', $res->result_number) }}', '{{ route('media.results.save-poster', $res) }}')"
                                    class="py-2 px-3 rounded-xl border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition-colors flex items-center gap-1.5 shadow-2xs shrink-0 cursor-pointer"
                                    title="Upload ready-made poster file directly">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                <span>Upload Poster</span>
                            </button>
                        </div>

                        <!-- Secondary Row: Status / Publish / Download / View -->
                        <div class="flex items-center justify-between gap-2 pt-1.5 border-t border-slate-200/60">
                            @if(!$res->is_media_published)
                                <form method="POST" action="{{ route('media.results.publish', $res) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="py-1.5 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors shadow-2xs flex items-center gap-1" title="Publish Public Result">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Publish Result</span>
                                    </button>
                                </form>
                            @else
                                <span class="text-[11px] font-bold text-emerald-700 flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Live on Website</span>
                                </span>
                            @endif

                            @if($res->poster_image)
                                <div class="flex items-center gap-1.5 ml-auto">
                                    <a href="{{ route('media.results.download-poster', $res) }}" download="{{ $res->getDownloadFilename('png') }}" class="p-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 transition-colors" title="Download Poster ({{ $res->getDownloadFilename('png') }})">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    </a>
                                    <a href="{{ route('media.results.public-poster', $res) }}" target="_blank" class="p-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 transition-colors" title="View Public Poster">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($results->hasPages())
            <div class="p-4 bg-white rounded-xl border border-slate-200">
                {{ $results->links() }}
            </div>
        @endif
    @else
        <div class="bg-white p-12 rounded-2xl border border-slate-200 text-center space-y-3">
            <svg class="w-12 h-12 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <div class="font-bold text-slate-700 text-sm">No results found</div>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                When stage announcers declare results and mark them announced, they will appear here in real-time.
            </p>
        </div>
    @endif

    <!-- Upload Poster Modal (Direct Image Upload & Instant Publish) -->
    <div x-show="uploadModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;"
         @keydown.escape.window="uploadModalOpen = false">

        <!-- Modal Dialog -->
        <div @click.away="uploadModalOpen = false" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-3xl max-w-xl w-full border border-slate-200 shadow-2xl overflow-hidden font-sora my-8">

            <form :action="formAction" method="POST" enctype="multipart/form-data" @submit="isUploading = true">
                @csrf

                <!-- Modal Header -->
                <div class="p-6 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-white">Upload Finished Poster</h3>
                            <p class="text-xs text-slate-300">ഡിസൈൻ ചെയ്ത പോസ്റ്റർ നേരിട്ട് അപ്‌ലോഡ് ചെയ്തു പബ്ലിഷ് ചെയ്യുക</p>
                        </div>
                    </div>
                    <button type="button" @click="uploadModalOpen = false" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-white/10 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-5">
                    <!-- Selected Program Info & Switcher -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Target Competition Result</span>
                            <div class="flex items-center gap-1.5 font-mono text-[11px] font-bold">
                                <span class="px-2 py-0.5 rounded bg-slate-900 text-amber-300" x-text="'Result #' + resultNo"></span>
                                <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700" x-text="zoneName"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-lg font-mono text-xs font-black bg-indigo-600 text-white shrink-0" x-text="programCode"></span>
                            <div class="text-sm font-bold text-slate-900 truncate" x-text="programName"></div>
                        </div>

                        <!-- Dropdown to select another program if needed -->
                        <div class="pt-2 border-t border-slate-200/80" x-show="availableResults.length > 1">
                            <label class="text-[11px] font-semibold text-slate-600 mb-1 block">Change Program (മറ്റൊരു റിസൾട്ട് തിരഞ്ഞെടുക്കുക):</label>
                            <select class="w-full text-xs font-medium p-2 rounded-xl border border-slate-300 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    x-model="selectedResultId" 
                                    @change="onResultSelected($event.target.value)">
                                <template x-for="item in availableResults" :key="item.id">
                                    <option :value="item.id" x-text="'#' + item.result_no + ' • ' + item.code + ' - ' + item.name + ' (' + item.zone + ')'" :selected="item.id == selectedResultId"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- File Drag & Drop Box -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Poster Image File (പോസ്റ്റർ ഇമേജ് ഫയൽ)
                        </label>
                        <div @dragover.prevent="isDragging = true"
                             @dragleave.prevent="isDragging = false"
                             @drop.prevent="onDrop($event)"
                             :class="isDragging ? 'border-indigo-500 bg-indigo-50/50' : 'border-slate-300 hover:border-slate-400 bg-slate-50/50'"
                             class="relative border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all">

                            <input type="file" 
                                   id="posterFileInput"
                                   name="poster_file" 
                                   accept="image/png,image/jpeg,image/webp" 
                                   required
                                   @change="onFileChange($event)"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                            <!-- Case 1: No file selected yet -->
                            <div x-show="!previewUrl" class="space-y-2">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 mx-auto flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                </div>
                                <div class="text-xs font-bold text-slate-800">
                                    Drag & drop your finished poster image here
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    or <span class="text-indigo-600 font-bold underline">browse file from device</span>
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">
                                    Supported: PNG, JPG, WEBP • Recommended aspect ratio: 1:1 or 4:5 • Max 10MB
                                </div>
                            </div>

                            <!-- Case 2: File Selected (Live Preview) -->
                            <div x-show="previewUrl" class="space-y-3" style="display: none;">
                                <div class="relative max-w-xs mx-auto rounded-xl overflow-hidden shadow-md border border-slate-200 aspect-square bg-slate-900">
                                    <img :src="previewUrl" alt="Poster Preview" class="w-full h-full object-contain">
                                </div>
                                <div class="flex items-center justify-center gap-2 text-xs">
                                    <span class="font-bold text-slate-800 truncate max-w-xs" x-text="fileName"></span>
                                    <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-600 font-mono text-[10px]" x-text="fileSize"></span>
                                </div>
                                <div class="text-[11px] text-indigo-600 font-bold hover:underline">
                                    ക്ലിക്ക് ചെയ്ത് വേറെ ഇമേജ് മാറ്റാം (Change File)
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instant Publish Checkbox -->
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3">
                        <input type="hidden" name="publish_now" value="0">
                        <input type="checkbox" 
                               name="publish_now" 
                               id="publishNowCheck" 
                               value="1" 
                               checked 
                               class="w-4 h-4 mt-0.5 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                        <label for="publishNowCheck" class="text-xs text-emerald-900 cursor-pointer">
                            <span class="font-bold">Publish immediately to public website (തത്സമയം പബ്ലിഷ് ചെയ്യുക)</span>
                            <span class="block text-[11px] text-emerald-700 mt-0.5">
                                അപ്‌ലോഡ് പൂർത്തിയാകുന്ന ഉടൻ ഫെസ്റ്റിവൽ ലൈവ് പോർട്ടലിൽ റിസൾട്ടും പോസ്റ്ററും പ്രസിദ്ധീകരിക്കപ്പെടും.
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="uploadModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="isUploading || !previewUrl"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs transition flex items-center gap-2 shadow-md shadow-indigo-600/20 disabled:opacity-50 disabled:pointer-events-none cursor-pointer">
                        <svg x-show="!isUploading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <svg x-show="isUploading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isUploading ? 'Uploading & Publishing...' : 'Upload & Publish Poster'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let countdown = 25;
    const label = document.getElementById('autoReloadLabel');
    if (label) {
        setInterval(() => {
            if (window.__isModalActive) {
                label.innerText = 'Paused (Modal Open)';
                return;
            }
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
