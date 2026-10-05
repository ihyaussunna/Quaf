@extends('layouts.admin', ['title' => 'Festival Schedule — All Stages | Quaf'])

@section('content')
<div class="space-y-6" x-data="{
    viewMode: 'table',
    showScheduleModal: false,
    showAddStageModal: false,
    showClashDrawer: false,
    
    // Quick Slot Modal State
    modalProgramId: '{{ $programsWithoutSchedule->first()?->id ?? ($allPrograms->first()?->id ?? '') }}',
    modalStageId: '{{ $stageId && is_numeric($stageId) ? $stageId : ($stages->first()?->id ?? '') }}',
    modalDate: '{{ $date && $date !== 'all' ? $date : '2026-10-31' }}',
    modalTime: '09:00',
    modalDuration: {{ ($programsWithoutSchedule->first() ?? $allPrograms->first())?->duration_minutes ?: 30 }},
    isCheckingClash: false,
    clashWarning: null,

    // Zone & Search Filters for Easy Scheduling
    selectedZoneFilter: 'all',
    programSearch: '',
    programsList: {{ Js::from($allPrograms->map(fn($p) => [
        'id' => (string) $p->id,
        'code' => $p->code,
        'name' => $p->name,
        'zone_id' => (string) ($p->zone_id ?? ''),
        'zone_name' => $p->zone?->name ?? 'Mix',
        'is_stage' => (bool) $p->is_stage,
        'is_scheduled' => (bool) $p->schedule,
        'duration' => (int) ($p->duration_minutes ?: 30),
    ])) }},

    get filteredPrograms() {
        return this.programsList.filter(p => {
            const matchZone = this.selectedZoneFilter === 'all' || 
                (this.selectedZoneFilter === 'mix' && (!p.zone_id || p.zone_name.toLowerCase().includes('mix'))) ||
                p.zone_id === this.selectedZoneFilter;
            
            const q = this.programSearch.trim().toLowerCase();
            const matchSearch = !q || p.name.toLowerCase().includes(q) || p.code.toLowerCase().includes(q) || p.zone_name.toLowerCase().includes(q);

            return matchZone && matchSearch;
        });
    },

    onProgramChange(id) {
        this.modalProgramId = id;
        const prog = this.programsList.find(p => String(p.id) === String(id));
        if (prog && prog.duration) {
            this.modalDuration = prog.duration;
        }
        this.checkClash();
    },

    selectProgram(prog) {
        this.onProgramChange(prog.id);
    },

    setStage(id) {
        this.modalStageId = id;
        this.checkClash();
    },

    setTime(val) {
        this.modalTime = val;
        this.checkClash();
    },

    setDuration(val) {
        this.modalDuration = val;
        this.checkClash();
    },

    checkClash() {
        if (!this.modalProgramId || !this.modalStageId || !this.modalDate || !this.modalTime) {
            return;
        }
        this.isCheckingClash = true;
        this.clashWarning = null;

        fetch(`{{ route('admin.schedules.check-conflict') }}?program_id=${this.modalProgramId}&stage_id=${this.modalStageId}&date=${this.modalDate}&time=${this.modalTime}&duration=${this.modalDuration}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            this.isCheckingClash = false;
            if (data.status === 'success' && data.conflicts && data.conflicts.has_conflicts) {
                this.clashWarning = data.conflicts;
            } else {
                this.clashWarning = null;
            }
        })
        .catch(() => {
            this.isCheckingClash = false;
        });
    }
}">
    <!-- Top Header & Primary Action Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/90 shadow-2xs">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#be1e2d]/10 text-[#be1e2d] uppercase tracking-wider">
                    MASTER FESTIVAL TIMELINE
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs font-mono text-slate-500 font-medium">All Main & Offstage Venues</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-sora tracking-tight">
                All Stages Schedule
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-sora max-w-2xl">
                Comprehensive scheduling across Main Stages (01–04) and Offstage Venues (NF3, ID3, U2, S3) with live conflict detection.
            </p>

            <!-- Quick Festival Stats Strip -->
            <div class="flex flex-wrap items-center gap-3 mt-4 text-xs font-mono">
                <span class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">
                    Scheduled: <strong class="text-slate-900">{{ $scheduledCount }}</strong> / {{ $totalProgramsCount }} Events
                </span>
                <span class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">
                    Stages: <strong class="text-slate-900">{{ $stages->count() }}</strong> Venues
                </span>
                @if($conflictsSummary['total_conflicts'] > 0)
                    <button @click="showClashDrawer = !showClashDrawer" 
                            class="px-3 py-1.5 rounded-xl bg-red-50 border border-red-200 text-[#be1e2d] font-bold flex items-center gap-1.5 hover:bg-red-100 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>{{ $conflictsSummary['total_conflicts'] }} Clashes Detected</span>
                    </button>
                @else
                    <span class="px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 font-semibold flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Zero Clashes</span>
                    </span>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Offstage Scheduler Direct Shortcut -->
            <a href="{{ route('admin.schedules.offstage') }}" 
               class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition-all flex items-center gap-2 border border-slate-300">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span>Offstage & Clashes</span>
            </a>

            <!-- Print Rockwell PDF -->
            <a href="{{ route('admin.schedules.offstage.pdf', ['date' => $date && $date !== 'all' ? $date : 'all']) }}" target="_blank"
               class="px-3.5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-all flex items-center gap-2 shadow-2xs">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print PDF (Rockwell)</span>
            </a>

            <!-- Dynamic Venue Addition -->
            <button @click="showAddStageModal = true"
                    class="px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-xs transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                <span>+ Venue / Stage</span>
            </button>

            <!-- Quick Schedule Trigger (Main CTA) -->
            <button @click="showScheduleModal = true; checkClash()"
                    class="px-4 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs transition-all flex items-center gap-2 shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Schedule Any Stage</span>
            </button>
        </div>
    </div>

    <!-- Clash Alert Banner (If Conflicts Exist) -->
    @if($conflictsSummary['total_conflicts'] > 0)
        <div class="rounded-2xl border-2 border-red-300 bg-red-50/90 p-4 transition-all shadow-xs">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-red-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        !
                    </span>
                    <div>
                        <h4 class="font-bold text-sm text-red-950 font-sora">
                            {{ $conflictsSummary['total_conflicts'] }} Schedule Clashes Identified
                        </h4>
                        <p class="text-xs text-red-800">
                            {{ count($conflictsSummary['stage_conflicts']) }} stage double-bookings and {{ count($conflictsSummary['student_conflicts']) }} multi-competition student overlaps.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <form method="POST" action="{{ route('admin.schedules.auto-resolve') }}">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date && $date !== 'all' ? $date : 'all' }}">
                        <button type="submit" 
                                onclick="return confirm('Automatically optimize schedule to eliminate all stage double-bookings and participant clashes?');"
                                class="px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs uppercase tracking-wider transition shadow-2xs flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            <span>Auto-Resolve Clashes</span>
                        </button>
                    </form>

                    <button @click="showClashDrawer = !showClashDrawer" 
                            class="px-3.5 py-1.5 rounded-xl bg-white border border-red-300 text-red-900 font-bold text-xs hover:bg-red-50 transition cursor-pointer">
                        <span x-text="showClashDrawer ? 'Hide Clash Breakdown' : 'View Clash Breakdown'"></span>
                    </button>
                    <a href="{{ route('admin.schedules.offstage') }}" class="px-3 py-1.5 rounded-xl bg-red-700 text-white font-bold text-xs hover:bg-red-800 transition">
                        Resolve in Offstage Scheduler →
                    </a>
                </div>
            </div>

            <!-- Expandable Clash Details Drawer -->
            <div x-show="showClashDrawer" class="mt-4 pt-4 border-t border-red-200/80 space-y-3" style="display: none;">
                @if(!empty($conflictsSummary['stage_conflicts']))
                    <div class="space-y-2">
                        <h5 class="text-xs font-mono font-bold uppercase text-red-900 tracking-wider">Stage Double Bookings:</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($conflictsSummary['stage_conflicts'] as $sc)
                                <div class="p-3 rounded-xl bg-white border border-red-200 text-xs">
                                    <div class="font-bold text-slate-900 mb-1 flex items-center justify-between">
                                        <span>{{ $sc['stage_name'] }} ({{ $sc['venue'] ?? '' }})</span>
                                        <span class="text-[10px] font-mono text-red-600 bg-red-50 px-2 py-0.5 rounded">Overlap</span>
                                    </div>
                                    <p class="text-[11px] text-slate-600 mb-1">{{ $sc['message'] }}</p>
                                    <div class="text-[10px] font-mono text-slate-500">
                                        {{ $sc['program_a']['code'] }} ({{ $sc['program_a']['time'] }}) vs {{ $sc['program_b']['code'] }} ({{ $sc['program_b']['time'] }})
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($conflictsSummary['student_conflicts']))
                    <div class="space-y-2 pt-2">
                        <h5 class="text-xs font-mono font-bold uppercase text-red-900 tracking-wider">Participant Multi-Program Clashes:</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach($conflictsSummary['student_conflicts'] as $stc)
                                <div class="p-3 rounded-xl bg-white border border-red-200 text-xs">
                                    <div class="flex items-center justify-between mb-1">
                                        <strong class="text-slate-900">{{ $stc['student_name'] }}</strong>
                                        <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700">
                                            #{{ $stc['chest_number'] }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-600 mb-1.5">{{ $stc['message'] }}</div>
                                    <div class="text-[10px] font-mono text-slate-500 space-y-0.5">
                                        <div>• {{ $stc['program_a']['name'] }} ({{ $stc['program_a']['stage'] }} @ {{ $stc['program_a']['time'] }})</div>
                                        <div>• {{ $stc['program_b']['name'] }} ({{ $stc['program_b']['stage'] }} @ {{ $stc['program_b']['time'] }})</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Controls Row: Date Chips, Stage Groups, Search & View Switcher -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/90 shadow-2xs space-y-4">
        <!-- Date Chips Row -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1 select-none">
            <span class="text-[11px] font-mono uppercase text-slate-400 font-bold shrink-0 mr-1">Festival Date:</span>
            @foreach($festivalDates as $dKey => $dLabel)
                <a href="{{ route('admin.schedules.index', array_merge(request()->query(), ['date' => $dKey])) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-mono font-bold whitespace-nowrap transition-all shadow-2xs {{ ($date ?? 'all') === $dKey ? 'bg-[#be1e2d] text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    {{ $dLabel }}
                </a>
            @endforeach
        </div>

        <!-- Stage Filter Category Pills -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-[11px] font-mono uppercase text-slate-400 font-bold shrink-0 mr-1">Stage Scope:</span>
                
                <a href="{{ route('admin.schedules.index', array_merge(request()->query(), ['stage' => 'all', 'group' => null])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-medium transition {{ empty($stageId) && empty($stageGroup) ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    All Stages ({{ $stages->count() }})
                </a>

                <a href="{{ route('admin.schedules.index', array_merge(request()->query(), ['group' => 'main', 'stage' => null])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-medium transition {{ $stageGroup === 'main' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-red-50 hover:bg-red-100 text-[#be1e2d]' }}">
                    Main Stages (01–04)
                </a>

                <a href="{{ route('admin.schedules.index', array_merge(request()->query(), ['group' => 'offstage', 'stage' => null])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-medium transition {{ $stageGroup === 'offstage' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-amber-50 hover:bg-amber-100 text-amber-900' }}">
                    Offstage Venues (NF3, ID3, U2, S3)
                </a>

                <!-- Specific Stage Selector Dropdown -->
                <div class="relative inline-block ml-1">
                    <select onchange="window.location.href = this.value" class="bg-slate-100 border border-slate-300 rounded-xl px-3 py-1.5 text-xs text-slate-800 font-sora font-medium focus:outline-none focus:border-[#be1e2d]">
                        <option value="{{ route('admin.schedules.index', array_merge(request()->query(), ['stage' => 'all', 'group' => null])) }}">Select Specific Stage...</option>
                        @foreach($stages as $stg)
                            <option value="{{ route('admin.schedules.index', array_merge(request()->query(), ['stage' => $stg->id, 'group' => null])) }}" {{ $stageId == $stg->id ? 'selected' : '' }}>
                                {{ $stg->name }} ({{ $stg->location ?? $stg->code }}) — {{ $stg->schedules_count }} events
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- View Switcher (Table / Stage Columns / Timeline) -->
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-2xl">
                <button @click="viewMode = 'table'"
                        :class="viewMode === 'table' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-900 font-medium'"
                        class="px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Table View</span>
                </button>
                <button @click="viewMode = 'columns'"
                        :class="viewMode === 'columns' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-900 font-medium'"
                        class="px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                    <span>Stage Columns</span>
                </button>
                <button @click="viewMode = 'timeline'"
                        :class="viewMode === 'timeline' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-900 font-medium'"
                        class="px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Time Blocks</span>
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="{{ route('admin.schedules.index') }}" class="pt-2">
            <input type="hidden" name="date" value="{{ $date ?? 'all' }}">
            <input type="hidden" name="stage" value="{{ $stageId ?? '' }}">
            <input type="hidden" name="group" value="{{ $stageGroup ?? '' }}">
            <div class="relative">
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                       placeholder="Filter scheduled items by program code, name, Malayalam name..."
                       class="w-full bg-slate-50 border border-slate-300 rounded-2xl pl-10 pr-24 py-2.5 text-xs text-slate-900 font-sora focus:outline-none focus:border-[#be1e2d] focus:bg-white">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <button type="submit" class="absolute right-2 top-1.5 px-3 py-1 rounded-xl bg-slate-900 text-white font-bold text-[11px] hover:bg-slate-800 transition">
                    Search
                </button>
            </div>
        </form>
    </div>

    <!-- VIEW 1: DATA TABLE VIEW -->
    <div x-show="viewMode === 'table'" class="bg-white border border-slate-200/90 rounded-3xl overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sora">
                <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-200 text-[11px] font-bold font-mono">
                    <tr>
                        <th class="px-5 py-4">Time Slot</th>
                        <th class="px-5 py-4">Program Details</th>
                        <th class="px-5 py-4">Stage & Venue</th>
                        <th class="px-5 py-4 text-center">Zone / Type</th>
                        <th class="px-5 py-4 text-center">Duration</th>
                        <th class="px-5 py-4 text-center">Status</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($schedules as $item)
                        @php
                            $hasConflict = !empty($item->conflict_notes);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $hasConflict ? 'bg-red-50/30' : '' }}">
                            <!-- Time Slot -->
                            <td class="px-5 py-4 font-mono">
                                <span class="font-bold text-slate-900 block text-xs">{{ $item->start_time?->format('h:i A') }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $item->start_time?->format('d M (D)') }}</span>
                                <span class="text-[10px] text-slate-500">to {{ $item->end_time?->format('h:i A') }}</span>
                            </td>

                            <!-- Program Details -->
                            <td class="px-5 py-4">
                                <div class="flex items-start gap-2.5">
                                    <span class="font-mono font-bold text-[10px] text-[#be1e2d] bg-red-50 px-2 py-0.5 rounded border border-red-200 shrink-0">
                                        {{ $item->program->code }}
                                    </span>
                                    <div>
                                        <span class="font-bold text-slate-900 block text-xs leading-snug">{{ $item->program->name }}</span>
                                        @if($item->program->malayalam_name)
                                            <span class="text-[11px] text-slate-500 block font-normal">{{ $item->program->malayalam_name }}</span>
                                        @endif
                                        @if($hasConflict)
                                            <span class="mt-1 inline-flex items-center gap-1 text-[10px] font-mono text-red-700 bg-red-100/80 px-2 py-0.5 rounded border border-red-300">
                                                Clash Flagged
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Stage & Venue -->
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-800 text-xs">{{ $item->stage->name }}</div>
                                <div class="text-[10px] font-mono text-slate-500">Venue: <strong class="text-slate-700">{{ $item->stage->venue }}</strong></div>
                            </td>

                            <!-- Zone / Type -->
                            <td class="px-5 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-mono text-[10px] text-slate-700 font-semibold block mb-0.5">
                                    {{ $item->program->eligibility ?? 'A Zone' }}
                                </span>
                                <span class="text-[10px] text-slate-400">
                                    {{ ucfirst($item->program->type) }}
                                </span>
                            </td>

                            <!-- Duration -->
                            <td class="px-5 py-4 text-center font-mono">
                                <span class="px-2 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 font-bold text-[11px]">
                                    {{ $item->program->duration_minutes }}m
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4 text-center font-mono">
                                @if($item->status === 'completed')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Completed
                                    </span>
                                @elseif($item->status === 'in_progress')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Live
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 uppercase">
                                        {{ $item->status }}
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4 text-right space-x-1.5 whitespace-nowrap">
                                <button @click="modalProgramId = '{{ $item->program_id }}'; modalStageId = '{{ $item->stage_id }}'; modalDate = '{{ $item->start_time->format('Y-m-d') }}'; modalTime = '{{ $item->start_time->format('H:i') }}'; modalDuration = {{ $item->program->duration_minutes ?? 30 }}; showScheduleModal = true; checkClash()"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-[11px] hover:bg-slate-200 transition cursor-pointer">
                                    Reschedule
                                </button>
                                <form method="POST" action="{{ route('admin.schedules.destroy', $item) }}" onsubmit="return confirm('Remove program {{ $item->program->code }} from festival schedule?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 text-slate-400 hover:text-red-600 transition cursor-pointer" title="Delete Schedule">
                                        <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-slate-400">
                                <p class="text-sm font-medium text-slate-500">No scheduled events found for this filter criteria.</p>
                                <button @click="showScheduleModal = true; checkClash()" class="mt-3 inline-block text-xs font-bold text-[#be1e2d] hover:underline cursor-pointer">
                                    + Schedule an Event Now
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- VIEW 2: STAGE COLUMNS (KANBAN BOARD VIEW) -->
    <div x-show="viewMode === 'columns'" class="space-y-4" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($stages as $stg)
                @php
                    $stageItems = $schedulesByStage->get($stg->id, collect());
                    $isMainStage = in_array($stg->code, ['STG-01', 'STG-02', 'STG-03', 'STG-04']);
                @endphp
                <div class="bg-white rounded-3xl border-2 {{ $isMainStage ? 'border-red-200/80 bg-red-50/10' : 'border-slate-200' }} p-4 shadow-2xs flex flex-col min-h-[400px]">
                    <!-- Stage Column Header -->
                    <div class="pb-3 border-b border-slate-100 mb-3 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ $isMainStage ? 'text-[#be1e2d]' : 'text-amber-800' }}">
                                {{ $isMainStage ? 'MAIN STAGE' : 'OFFSTAGE VENUE' }}
                            </span>
                            <h3 class="font-bold text-slate-900 text-sm font-sora">{{ $stg->name }}</h3>
                            <span class="text-[10px] font-mono text-slate-400 block">Venue: {{ $stg->venue }}</span>
                        </div>
                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 font-mono font-bold text-xs flex items-center justify-center">
                            {{ $stageItems->count() }}
                        </span>
                    </div>

                    <!-- Program Cards in Stage Column -->
                    <div class="space-y-2.5 flex-1 overflow-y-auto">
                        @forelse($stageItems as $sItem)
                            <div class="p-3 rounded-2xl border border-slate-200 bg-white hover:border-[#be1e2d] transition shadow-2xs relative">
                                <div class="flex items-center justify-between gap-1 mb-1 font-mono text-[10px]">
                                    <span class="font-bold text-[#be1e2d] bg-red-50 px-1.5 py-0.2 rounded border border-red-200">
                                        {{ $sItem->start_time?->format('h:i A') }}
                                    </span>
                                    <span class="text-slate-400">{{ $sItem->program->duration_minutes }}m</span>
                                </div>
                                <h4 class="font-bold text-xs text-slate-900 leading-snug line-clamp-2">
                                    {{ $sItem->program->name }}
                                </h4>
                                <div class="mt-2 flex items-center justify-between text-[10px] font-mono text-slate-500 pt-1 border-t border-slate-100">
                                    <span>{{ $sItem->program->code }}</span>
                                    <button @click="modalProgramId = '{{ $sItem->program_id }}'; modalStageId = '{{ $sItem->stage_id }}'; modalDate = '{{ $sItem->start_time->format('Y-m-d') }}'; modalTime = '{{ $sItem->start_time->format('H:i') }}'; modalDuration = {{ $sItem->program->duration_minutes ?? 30 }}; showScheduleModal = true; checkClash()"
                                            class="text-[#be1e2d] font-bold hover:underline cursor-pointer">
                                        Edit
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="h-32 flex flex-col items-center justify-center text-center p-4 border border-dashed border-slate-200 rounded-2xl text-slate-400 text-xs">
                                <span>No events scheduled</span>
                                <button @click="modalStageId = '{{ $stg->id }}'; showScheduleModal = true; checkClash()" class="mt-1 text-[11px] font-bold text-[#be1e2d] hover:underline cursor-pointer">
                                    + Assign Slot
                                </button>
                            </div>
                        @endforelse
                    </div>

                    <!-- Column Footer Quick Button -->
                    <button @click="modalStageId = '{{ $stg->id }}'; showScheduleModal = true; checkClash()" 
                            class="w-full mt-3 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 font-bold text-[11px] transition text-center cursor-pointer">
                        + Add Program to {{ $stg->venue }}
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    <!-- VIEW 3: TIME BLOCKS VIEW -->
    <div x-show="viewMode === 'timeline'" class="bg-white border border-slate-200/90 rounded-3xl p-6 shadow-2xs space-y-6" style="display: none;">
        @forelse($schedulesByTime as $timeSlot => $slotItems)
            <div class="border-b border-slate-100 pb-5 last:border-b-0">
                <div class="flex items-center gap-3 mb-3">
                    <span class="px-3.5 py-1.5 rounded-xl bg-slate-900 text-white font-mono font-bold text-xs">
                        Time Slot | {{ $timeSlot }}
                    </span>
                    <span class="text-xs font-mono text-slate-400">{{ $slotItems->count() }} Simultaneous Competitions</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach($slotItems as $sItem)
                        <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60 hover:bg-white transition shadow-2xs">
                            <div class="flex items-center justify-between text-[10px] font-mono mb-1">
                                <span class="px-2 py-0.5 rounded font-bold bg-[#be1e2d] text-white">{{ $sItem->stage->venue }}</span>
                                <span class="text-slate-500">{{ $sItem->stage->name }}</span>
                            </div>
                            <div class="font-bold text-xs text-slate-900 mt-1 mb-1">{{ $sItem->program->name }}</div>
                            <div class="text-[10px] font-mono text-slate-500 flex items-center justify-between">
                                <span>Code: {{ $sItem->program->code }}</span>
                                <span>{{ $sItem->program->duration_minutes }}m</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="py-12 text-center text-slate-400 text-xs">
                No scheduled time blocks found.
            </div>
        @endforelse
    </div>

    <!-- QUICK SCHEDULE MODAL FOR ANY STAGE -->
    <div x-show="showScheduleModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs" 
         style="display: none;">
        <div @click.away="showScheduleModal = false" 
             class="w-full max-w-xl bg-white rounded-3xl p-5 sm:p-6 shadow-2xl border border-slate-200 max-h-[92vh] overflow-y-auto space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-[#be1e2d] text-white flex items-center justify-center font-bold text-sm">
                        +
                    </span>
                    <div>
                        <h3 class="font-bold text-base text-slate-900 font-sora">Schedule Any Stage Slot</h3>
                        <p class="text-[11px] text-slate-500 font-sora">Fast slotting with live participant & stage clash inspection.</p>
                    </div>
                </div>
                <button @click="showScheduleModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.schedules.quick-slot') }}" class="space-y-4 font-sora">
                @csrf
                <input type="hidden" name="redirect_to" value="index">

                <!-- 1. Select Stage / Venue -->
                <div>
                    <label class="block text-[11px] font-mono uppercase text-slate-600 font-bold mb-1.5 flex items-center justify-between">
                        <span>Select Stage / Venue *</span>
                        <span class="text-[10px] font-normal text-slate-400">Tap to select or choose from dropdown</span>
                    </label>

                    <!-- Quick Stage Chips -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 mb-2">
                        @foreach($stages->take(8) as $stg)
                            <button type="button" 
                                    @click="setStage('{{ $stg->id }}')"
                                    :class="modalStageId == '{{ $stg->id }}' ? 'bg-[#be1e2d] text-white font-bold border-[#be1e2d] shadow-2xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                    class="px-2 py-1.5 rounded-xl border text-center text-xs transition-all cursor-pointer">
                                <div class="font-bold leading-tight">{{ $stg->name }}</div>
                                <div class="text-[10px] font-mono opacity-80">{{ $stg->location ?? $stg->venue ?? $stg->code }}</div>
                            </button>
                        @endforeach
                    </div>

                    <select name="stage_id" x-model="modalStageId" @change="checkClash()" required
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d] font-sora">
                        <optgroup label="Main Stages (Festival Arena)">
                            @foreach($mainStages as $stg)
                                <option value="{{ $stg->id }}">{{ $stg->name }} ({{ $stg->location ?? $stg->code }})</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Offstage Venues">
                            @foreach($offstageStages as $stg)
                                <option value="{{ $stg->id }}">{{ $stg->name }} — Venue: {{ $stg->venue }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <!-- 2. Select Program with Zone Filter & Search -->
                <div class="space-y-2">
                    <label class="block text-[11px] font-mono uppercase text-slate-600 font-bold flex items-center justify-between">
                        <span>Select Competition / Program *</span>
                        <span class="text-[10px] text-slate-500 font-mono" x-text="`${filteredPrograms.length} programs available`"></span>
                    </label>

                    <!-- Zone Filter Chips -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <button type="button" @click="selectedZoneFilter = 'all'; if (filteredPrograms.length) modalProgramId = filteredPrograms[0].id; checkClash()"
                                :class="selectedZoneFilter === 'all' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer">
                            All Zones
                        </button>
                        <button type="button" @click="selectedZoneFilter = '1'; if (filteredPrograms.length) onProgramChange(filteredPrograms[0].id); else checkClash()"
                                :class="selectedZoneFilter === '1' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer">
                            A Zone
                        </button>
                        <button type="button" @click="selectedZoneFilter = '2'; if (filteredPrograms.length) onProgramChange(filteredPrograms[0].id); else checkClash()"
                                :class="selectedZoneFilter === '2' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer">
                            B Zone
                        </button>
                        <button type="button" @click="selectedZoneFilter = '3'; if (filteredPrograms.length) onProgramChange(filteredPrograms[0].id); else checkClash()"
                                :class="selectedZoneFilter === '3' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer">
                            C Zone
                        </button>
                        <button type="button" @click="selectedZoneFilter = '4'; if (filteredPrograms.length) onProgramChange(filteredPrograms[0].id); else checkClash()"
                                :class="selectedZoneFilter === '4' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer">
                            Mix Zone
                        </button>
                    </div>

                    <!-- Instant Program Search Box -->
                    <div class="relative">
                        <input type="text" x-model="programSearch" @input="if (filteredPrograms.length) { onProgramChange(filteredPrograms[0].id); } else { checkClash(); }"
                               placeholder="Quick search program name or code (e.g. 112, Story, Poem, Speech)..."
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d] font-sora">
                        <button type="button" x-show="programSearch" @click="programSearch = ''; checkClash()" 
                                class="absolute right-3 top-2.5 text-xs text-slate-400 hover:text-slate-700">✕</button>
                    </div>

                    <!-- Filtered Programs Dropdown -->
                    <select name="program_id" x-model="modalProgramId" @change="onProgramChange($event.target.value)" required
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d] font-sora">
                        <template x-for="prog in filteredPrograms" :key="prog.id">
                            <option :value="prog.id" 
                                    x-text="`[${prog.code}] ${prog.name} (${prog.duration}m, ${prog.zone_name}) ${prog.is_scheduled ? '— Scheduled' : ''}`">
                            </option>
                        </template>
                    </select>
                </div>

                <!-- 3. Date Selection & Presets -->
                <div>
                    <label class="block text-[11px] font-mono uppercase text-slate-600 font-bold mb-1.5">
                        Date *
                    </label>
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <button type="button" @click="modalDate = '2026-10-06'; modalTime = '16:40'; checkClash()" 
                                :class="modalDate === '2026-10-06' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-[10px] font-mono transition cursor-pointer">Oct 06 (Offstage)</button>
                        <button type="button" @click="modalDate = '2026-10-07'; modalTime = '16:40'; checkClash()" 
                                :class="modalDate === '2026-10-07' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-[10px] font-mono transition cursor-pointer">Oct 07 (Offstage)</button>
                        <button type="button" @click="modalDate = '2026-10-31'; modalTime = '09:00'; checkClash()" 
                                :class="modalDate === '2026-10-31' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-red-50 text-red-900 hover:bg-red-100'"
                                class="px-2.5 py-1 rounded-lg text-[10px] font-mono transition cursor-pointer">Oct 31 (Main Stage 1)</button>
                        <button type="button" @click="modalDate = '2026-11-01'; modalTime = '09:00'; checkClash()" 
                                :class="modalDate === '2026-11-01' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-red-50 text-red-900 hover:bg-red-100'"
                                class="px-2.5 py-1 rounded-lg text-[10px] font-mono transition cursor-pointer">Nov 01 (Main Stage 2)</button>
                    </div>
                    <input type="date" name="date" x-model="modalDate" @change="checkClash()" required
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 font-mono">
                </div>

                <!-- 4. Time Slot & Duration Presets -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-mono uppercase text-slate-600 font-bold mb-1.5">
                            Start Time *
                        </label>

                        <!-- Context-Aware Time Presets -->
                        <div class="flex items-center gap-1 mb-1.5 flex-wrap">
                            <template x-if="modalDate === '2026-10-06' || modalDate === '2026-10-07'">
                                <div class="flex items-center gap-1 flex-wrap">
                                    <button type="button" @click="setTime('16:40')" :class="modalTime === '16:40' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="px-1.5 py-0.5 rounded text-[10px] font-mono">04:40 PM</button>
                                    <button type="button" @click="setTime('17:10')" :class="modalTime === '17:10' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="px-1.5 py-0.5 rounded text-[10px] font-mono">05:10 PM</button>
                                    <button type="button" @click="setTime('21:15')" :class="modalTime === '21:15' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="px-1.5 py-0.5 rounded text-[10px] font-mono">09:15 PM</button>
                                    <button type="button" @click="setTime('21:45')" :class="modalTime === '21:45' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="px-1.5 py-0.5 rounded text-[10px] font-mono">09:45 PM</button>
                                    <button type="button" @click="setTime('22:25')" :class="modalTime === '22:25' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="px-1.5 py-0.5 rounded text-[10px] font-mono">10:25 PM</button>
                                </div>
                            </template>
                            <template x-if="modalDate !== '2026-10-06' && modalDate !== '2026-10-07'">
                                <div class="flex items-center gap-1 flex-wrap">
                                    <button type="button" @click="setTime('09:00')" :class="modalTime === '09:00' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="px-1.5 py-0.5 rounded text-[10px] font-mono">09:00 AM</button>
                                    <button type="button" @click="setTime('10:30')" :class="modalTime === '10:30' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="px-1.5 py-0.5 rounded text-[10px] font-mono">10:30 AM</button>
                                    <button type="button" @click="setTime('14:00')" :class="modalTime === '14:00' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="px-1.5 py-0.5 rounded text-[10px] font-mono">02:00 PM</button>
                                    <button type="button" @click="setTime('16:00')" :class="modalTime === '16:00' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="px-1.5 py-0.5 rounded text-[10px] font-mono">04:00 PM</button>
                                    <button type="button" @click="setTime('19:30')" :class="modalTime === '19:30' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="px-1.5 py-0.5 rounded text-[10px] font-mono">07:30 PM</button>
                                    <button type="button" @click="setTime('21:00')" :class="modalTime === '21:00' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="px-1.5 py-0.5 rounded text-[10px] font-mono">09:00 PM</button>
                                </div>
                            </template>
                        </div>

                        <input type="time" name="time" x-model="modalTime" @change="checkClash()" required
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 font-mono">
                    </div>

                    <div>
                        <label class="block text-[11px] font-mono uppercase text-slate-600 font-bold mb-1.5 flex items-center justify-between">
                            <span>Duration *</span>
                            <span class="text-[10px] text-slate-700 font-mono font-bold" x-text="`${modalDuration} min`"></span>
                        </label>
                        <div class="flex items-center gap-1 mb-1.5 flex-wrap">
                            <button type="button" @click="setDuration(15)" :class="modalDuration == 15 ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-100 text-slate-700'" class="px-2 py-0.5 rounded text-[10px] font-mono cursor-pointer">15m</button>
                            <button type="button" @click="setDuration(20)" :class="modalDuration == 20 ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-100 text-slate-700'" class="px-2 py-0.5 rounded text-[10px] font-mono cursor-pointer">20m</button>
                            <button type="button" @click="setDuration(30)" :class="modalDuration == 30 ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-100 text-slate-700'" class="px-2 py-0.5 rounded text-[10px] font-mono cursor-pointer">30m (Std)</button>
                            <button type="button" @click="setDuration(45)" :class="modalDuration == 45 ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-100 text-slate-700'" class="px-2 py-0.5 rounded text-[10px] font-mono cursor-pointer">45m</button>
                            <button type="button" @click="setDuration(60)" :class="modalDuration == 60 ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-100 text-slate-700'" class="px-2 py-0.5 rounded text-[10px] font-mono cursor-pointer">60m</button>
                        </div>
                        <input type="number" name="duration" x-model="modalDuration" @change="checkClash()" min="5" max="360" required
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 font-mono">
                    </div>
                </div>

                <!-- 5. Real-Time Live Conflict Inspection Box -->
                <div class="p-3.5 rounded-2xl border text-xs transition-all"
                     :class="clashWarning ? 'bg-red-50 border-red-300 text-red-900' : 'bg-slate-50 border-slate-200 text-slate-600'">
                    <div class="flex items-center justify-between mb-1">
                        <strong class="font-bold flex items-center gap-1.5">
                            <span x-show="isCheckingClash" class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                            <span>Live Clash Inspection</span>
                        </strong>
                        <span x-text="clashWarning ? clashWarning.count + ' Clash Found' : 'Clean Slot'" 
                              class="font-mono text-[10px] font-bold px-2 py-0.5 rounded"
                              :class="clashWarning ? 'bg-red-200 text-red-900' : 'bg-emerald-100 text-emerald-800'"></span>
                    </div>

                    <template x-if="clashWarning">
                        <div class="space-y-1.5 mt-2 pt-2 border-t border-red-200 text-[11px]">
                            <template x-if="clashWarning.stage_conflict">
                                <div class="font-bold text-red-700" x-text="clashWarning.stage_conflict.message"></div>
                            </template>
                            <template x-for="sc in clashWarning.student_conflicts" :key="sc.student_id">
                                <div class="text-red-800" x-text="sc.message"></div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!clashWarning">
                        <p class="text-[11px] text-slate-500 mt-1">No overlapping competitions or participant conflicts found for this slot.</p>
                    </template>
                </div>

                <!-- Submit Button -->
                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="showScheduleModal = false" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs uppercase tracking-wider transition shadow-sm cursor-pointer">
                        Confirm & Save Slot
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DYNAMIC ADD STAGE / VENUE MODAL -->
    <div x-show="showAddStageModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         style="display: none;">
        <div @click.away="showAddStageModal = false" 
             class="w-full max-w-md bg-white rounded-3xl p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="font-bold text-base text-slate-900 font-sora">Add New Stage or Venue</h3>
                <button @click="showAddStageModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.schedules.stages.store') }}" class="space-y-4 font-sora">
                @csrf
                <div>
                    <label class="block text-[11px] font-mono uppercase text-slate-600 font-bold mb-1">Stage Name *</label>
                    <input type="text" name="name" placeholder="e.g. Stage 09, Library Hall, Seminar Hall" required
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                </div>
                <div>
                    <label class="block text-[11px] font-mono uppercase text-slate-600 font-bold mb-1">Venue Code / Location *</label>
                    <input type="text" name="location" placeholder="e.g. NF4, ID4, Central Library, Mini Audi" required
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                </div>
                <div>
                    <label class="block text-[11px] font-mono uppercase text-slate-600 font-bold mb-1">Capacity (Audience Seats)</label>
                    <input type="number" name="capacity" placeholder="150" min="10" max="5000"
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="showAddStageModal = false" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#be1e2d] text-white font-bold text-xs uppercase tracking-wider hover:bg-[#a01624] transition shadow-sm">
                        Create Stage
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
