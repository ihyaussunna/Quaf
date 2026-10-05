@extends('layouts.admin', ['title' => 'Offstage Schedule & Conflict Detector | Quaf 9.0'])

@section('content')
<div class="space-y-6" x-data="{
    slotModal: false,
    stageModal: false,
    showConflictsDrawer: {{ $conflicts['total_conflicts'] > 0 ? 'true' : 'false' }},
    isEditing: false,
    formScheduleId: null,
    formProgramId: '',
    formStageId: '{{ $stages->firstWhere('code', 'STG-05')?->id ?? $stages->first()?->id }}',
    formDate: '{{ $selectedDate }}',
    formTime: '16:40',
    formDuration: 30,
    conflictCheckLoading: false,
    slotConflictResult: null,

    // Zone & Search Filters
    selectedZoneFilter: 'all',
    programSearch: '',
    programsList: {{ Js::from($programs->map(fn($p) => [
        'id' => (string) $p->id,
        'code' => $p->code,
        'name' => $p->name,
        'zone_id' => (string) ($p->zone_id ?? ''),
        'zone_name' => $p->zone?->name ?? 'Mix',
        'is_stage' => (bool) $p->is_stage,
        'is_scheduled' => (bool) $p->schedule,
        'duration' => $p->duration_minutes ?: 30,
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

    onProgramSelect(id) {
        this.formProgramId = id;
        const prog = this.programsList.find(p => String(p.id) === String(id));
        if (prog && prog.duration) {
            this.formDuration = prog.duration;
        }
        this.checkLiveConflict();
    },

    openCreateSlot(presetTime = '16:40', presetStageId = null, presetProgramId = null) {
        this.isEditing = false;
        this.formScheduleId = null;
        this.formProgramId = presetProgramId || '';
        this.formStageId = presetStageId || '{{ $stages->firstWhere('code', 'STG-05')?->id ?? $stages->first()?->id }}';
        this.formDate = '{{ $selectedDate }}';
        this.formTime = presetTime;
        const initProg = presetProgramId ? this.programsList.find(x => String(x.id) === String(presetProgramId)) : null;
        this.formDuration = initProg && initProg.duration ? initProg.duration : 30;
        this.slotConflictResult = null;
        this.slotModal = true;
        if (presetProgramId) {
            this.checkLiveConflict();
        }
    },

    openEditSlot(sch) {
        this.isEditing = true;
        this.formScheduleId = sch.id;
        this.formProgramId = sch.program_id;
        this.formStageId = sch.stage_id;
        this.formDate = sch.start_time.split('T')[0] || sch.start_time.split(' ')[0];
        
        // Extract HH:mm from ISO or string
        const d = new Date(sch.start_time);
        if (!isNaN(d.getTime())) {
            const hours = String(d.getHours()).padStart(2, '0');
            const minutes = String(d.getMinutes()).padStart(2, '0');
            this.formTime = `${hours}:${minutes}`;
        } else {
            this.formTime = '16:40';
        }

        // Calculate duration in minutes
        if (sch.end_time) {
            const startD = new Date(sch.start_time);
            const endD = new Date(sch.end_time);
            const diffMin = Math.round((endD - startD) / 60000);
            this.formDuration = diffMin > 0 ? diffMin : (sch.program?.duration_minutes || 30);
        } else {
            this.formDuration = sch.program?.duration_minutes || 30;
        }

        this.slotConflictResult = null;
        this.slotModal = true;
        this.checkLiveConflict();
    },

    async checkLiveConflict() {
        if (!this.formProgramId || !this.formDate || !this.formTime || !this.formDuration) {
            this.slotConflictResult = null;
            return;
        }

        this.conflictCheckLoading = true;
        try {
            const params = new URLSearchParams({
                program_id: this.formProgramId,
                date: this.formDate,
                time: this.formTime,
                duration: this.formDuration,
                stage_id: this.formStageId || '',
                exclude_schedule_id: this.formScheduleId || ''
            });

            const res = await fetch(`{{ route('admin.schedules.check-conflict') }}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.status === 'success') {
                this.slotConflictResult = data.conflicts;
            }
        } catch (e) {
            console.error('Conflict check failed', e);
        } finally {
            this.conflictCheckLoading = false;
        }
    }
}">

    <!-- Top Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 font-mono mb-1">
                <a href="{{ route('admin.schedules.index') }}" class="hover:text-[#be1e2d] transition">Schedules</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Offstage & Clash Detector</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight font-sora flex items-center gap-3">
                <span>Offstage Schedule Manager</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Live Conflict Detection
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-sora">
                Manage offstage slots (NF3, ID3, U2, S3), detect participant & stage clashes in real-time, and download the official Rockwell PDF.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Add Stage / Venue -->
            <button type="button" @click="stageModal = true"
                    class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-semibold text-xs transition shadow-2xs flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span>+ Add Venue / Stage</span>
            </button>

            <!-- PDF Print / Export Button -->
            <a href="{{ route('admin.schedules.offstage.pdf', ['date' => $selectedDate]) }}" target="_blank"
               class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Rockwell PDF</span>
            </a>

            <!-- Quick Add Slot Button -->
            <button type="button" @click="openCreateSlot()"
                    class="px-4 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Schedule Slot</span>
            </button>
        </div>
    </div>

    <!-- Date Navigation Chips (Horizontal Scrollable for Mobile) -->
    <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-2xs">
        <div class="flex items-center justify-between gap-3 mb-2">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider font-mono">Festival Dates</span>
            <span class="text-xs text-slate-400">Offstage begins Oct 06 • Mainstage Oct 31 – Nov 01</span>
        </div>
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
            @foreach($defaultDates as $dateVal => $dateLabel)
                <a href="{{ route('admin.schedules.offstage', ['date' => $dateVal]) }}"
                   class="whitespace-nowrap px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $selectedDate === $dateVal ? 'bg-[#be1e2d] text-white shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-slate-200' }}">
                    <span>{{ $dateLabel }}</span>
                    @php
                        $dayCount = \App\Models\Schedule::whereDate('start_time', $dateVal)->count();
                    @endphp
                    @if($dayCount > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono {{ $selectedDate === $dateVal ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                            {{ $dayCount }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <!-- Conflict Warning Banner (if conflicts exist) -->
    @if($conflicts['total_conflicts'] > 0)
        <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-4 sm:p-5 shadow-xs transition-all">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm sm:text-base text-amber-950 font-sora">
                            {{ $conflicts['total_conflicts'] }} Schedule Conflict{{ $conflicts['total_conflicts'] > 1 ? 's' : '' }} Detected on {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}
                        </h3>
                        <p class="text-xs text-amber-800 mt-0.5">
                            Overlapping timings detected across stages or participants enrolled in simultaneous events.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <form method="POST" action="{{ route('admin.schedules.auto-resolve') }}">
                        @csrf
                        <input type="hidden" name="date" value="{{ $selectedDate }}">
                        <button type="submit"
                                onclick="return confirm('Automatically optimize schedule to eliminate all stage double-bookings and participant clashes on this date?');"
                                class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs uppercase tracking-wider transition shadow-2xs flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            <span>Auto-Resolve Clashes</span>
                        </button>
                    </form>

                    <button @click="showConflictsDrawer = !showConflictsDrawer"
                            class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs uppercase tracking-wider transition shadow-2xs flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                        <span x-text="showConflictsDrawer ? 'Hide Clashes' : 'Inspect Clashes'"></span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="showConflictsDrawer ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Expanded Conflict Details List -->
            <div x-show="showConflictsDrawer" x-transition class="mt-4 pt-4 border-t border-amber-200/80 space-y-3">
                <!-- Stage Clashes -->
                @if(!empty($conflicts['stage_conflicts']))
                    <div class="space-y-2">
                        <div class="text-[11px] font-mono uppercase tracking-wider font-bold text-amber-900 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-red-600"></span>
                            <span>Stage Double Bookings ({{ count($conflicts['stage_conflicts']) }})</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                            @foreach($conflicts['stage_conflicts'] as $sc)
                                <div class="p-3 rounded-xl bg-white border border-amber-200 shadow-2xs flex items-start justify-between gap-3">
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-900 flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded bg-slate-100 font-mono text-[10px]">{{ $sc['stage_name'] }} ({{ $sc['venue'] }})</span>
                                        </div>
                                        <p class="text-slate-600 mt-1">
                                            <strong>{{ $sc['program_a']['name'] }}</strong> ({{ $sc['program_a']['time'] }}) overlaps with <strong>{{ $sc['program_b']['name'] }}</strong> ({{ $sc['program_b']['time'] }}).
                                        </p>
                                    </div>
                                    <button @click="openCreateSlot('{{ $sc['program_b']['start_time'] ?? '16:40' }}', {{ $sc['stage_id'] }})"
                                            class="text-[11px] font-bold text-[#be1e2d] hover:underline whitespace-nowrap cursor-pointer">
                                        Fix
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Student Clashes -->
                @if(!empty($conflicts['student_conflicts']))
                    <div class="space-y-2 mt-4">
                        <div class="text-[11px] font-mono uppercase tracking-wider font-bold text-amber-900 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-orange-600"></span>
                            <span>Participant Clashes ({{ count($conflicts['student_conflicts']) }})</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                            @foreach($conflicts['student_conflicts'] as $stc)
                                <div class="p-3 rounded-xl bg-white border border-amber-200 shadow-2xs">
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="font-bold text-slate-900">{{ $stc['student_name'] }}</span>
                                        <span class="font-mono text-[10px] px-1.5 py-0.2 rounded bg-slate-100">#{{ $stc['chest_number'] }}</span>
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded text-white" style="background-color: {{ $stc['group_color'] }}">
                                            {{ $stc['group_name'] }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-1">
                                        Enrolled in both <strong>{{ $stc['program_a']['name'] }}</strong> ({{ $stc['program_a']['venue'] }}) and <strong>{{ $stc['program_b']['name'] }}</strong> ({{ $stc['program_b']['venue'] }}) at overlapping times!
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-3.5 sm:p-4 flex items-center justify-between text-xs text-emerald-800">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium">No participant or stage clashes detected for {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}. All slots are harmonious.</span>
            </div>
            <span class="font-mono text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-lg">All Clean</span>
        </div>
    @endif

    <!-- Offstage Schedule Timeline View (Grouped by Time Slot) -->
    <div class="space-y-6">
        @forelse($schedulesByTime as $timeSlot => $slotItems)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                <!-- Time Slot Header -->
                <div class="bg-slate-900 text-white px-4 sm:px-6 py-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                        <h2 class="text-base sm:text-lg font-extrabold tracking-tight font-rockwell">
                            Time | {{ $timeSlot }}
                        </h2>
                        <span class="text-xs text-slate-400 font-mono font-medium">({{ $slotItems->count() }} Stages running)</span>
                    </div>

                    <!-- Add program to this exact time slot -->
                    <button type="button" @click="openCreateSlot('{{ $slotItems->first()?->start_time?->format('H:i') ?? '16:40' }}')"
                            class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white font-semibold text-xs transition flex items-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>+ Add to {{ $timeSlot }}</span>
                    </button>
                </div>

                <!-- Desktop Table / Mobile Cards -->
                <div class="divide-y divide-slate-100">
                    @foreach($slotItems as $sch)
                        @php
                            $isClashing = isset($conflicts['program_conflict_counts'][$sch->program_id]);
                        @endphp
                        <div class="p-4 sm:px-6 sm:py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/80 transition-colors {{ $isClashing ? 'bg-amber-50/50' : '' }}">
                            <!-- Left: Stage & Program Info -->
                            <div class="flex items-start sm:items-center gap-3 sm:gap-5">
                                <!-- Stage Badge -->
                                <div class="shrink-0 w-24">
                                    <div class="font-extrabold text-sm text-slate-900 font-rockwell">{{ $sch->stage?->name }}</div>
                                    <div class="inline-flex items-center gap-1 font-mono text-[11px] font-bold text-slate-500">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                        <span>{{ $sch->stage?->venue }}</span>
                                    </div>
                                </div>

                                <!-- Program Details -->
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono text-xs font-bold text-[#be1e2d]">{{ $sch->program?->code }}</span>
                                        <h3 class="font-bold text-sm sm:text-base text-slate-900 font-rockwell">
                                            {{ $sch->program?->name }}
                                        </h3>
                                        @if($sch->program?->zone)
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full text-white" style="background-color: {{ $sch->program->zone->color_hex }}">
                                                {{ $sch->program->zone->name }}
                                            </span>
                                        @endif
                                        @if($isClashing)
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700 border border-red-200 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                <span>Clash Detected</span>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-slate-500 mt-1">
                                        <span class="font-mono">{{ $sch->start_time->format('h:i A') }} – {{ $sch->end_time->format('h:i A') }}</span>
                                        <span>•</span>
                                        <span>Duration: <strong>{{ $sch->program?->duration_minutes ?: 40 }} min</strong></span>
                                        <span>•</span>
                                        <span>{{ $sch->program?->entries?->count() ?? 0 }} Registered Participants</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Quick Slot Action Buttons -->
                            <div class="flex items-center gap-2 self-end sm:self-center">
                                <button type="button" @click="openEditSlot({{ $sch->toJson() }})"
                                        class="p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition cursor-pointer" title="Edit Slot">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <form method="POST" action="{{ route('admin.schedules.destroy', $sch->id) }}" onsubmit="return confirm('Remove this program from schedule?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-xl text-red-500 hover:text-red-700 hover:bg-red-50 transition cursor-pointer" title="Delete Slot">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="font-bold text-base text-slate-900 font-sora">No Schedule Set for {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Click "+ Schedule Slot" below to assign items to NF3, ID3, U2, S3 or any festival stage.</p>
                <button type="button" @click="openCreateSlot()"
                        class="mt-4 px-4 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs uppercase tracking-wider transition shadow-sm inline-flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ Schedule First Slot</span>
                </button>
            </div>
        @endforelse
    </div>

    <!-- TOUCH-FRIENDLY QUICK SLOT MODAL -->
    <div x-show="slotModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="slotModal = false"></div>

        <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-center">
            <div class="relative transform overflow-hidden rounded-t-3xl sm:rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-lg p-5 sm:p-6"
                 @click.away="slotModal = false">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 font-sora" x-text="isEditing ? 'Edit Schedule Slot' : 'Add Offstage Schedule Slot'"></h3>
                        <p class="text-xs text-slate-500">Live conflict detection runs automatically as you configure.</p>
                    </div>
                    <button type="button" @click="slotModal = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.schedules.quick-slot') }}" class="space-y-4 mt-4">
                    @csrf
                    <input type="hidden" name="schedule_id" :value="formScheduleId">

                    <!-- Program Selector with Zone Filter & Search -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700 font-sora">Competition / Item <span class="text-red-500">*</span></label>
                            <span class="text-[10px] text-slate-500 font-mono" x-text="`${filteredPrograms.length} items`"></span>
                        </div>

                        <!-- Zone Filter Chips -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" @click="selectedZoneFilter = 'all'; if (filteredPrograms.length) formProgramId = filteredPrograms[0].id; checkLiveConflict()"
                                    :class="selectedZoneFilter === 'all' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition cursor-pointer">
                                All
                            </button>
                            <button type="button" @click="selectedZoneFilter = '1'; if (filteredPrograms.length) onProgramSelect(filteredPrograms[0].id); else checkLiveConflict()"
                                    :class="selectedZoneFilter === '1' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition cursor-pointer">
                                A Zone
                            </button>
                            <button type="button" @click="selectedZoneFilter = '2'; if (filteredPrograms.length) onProgramSelect(filteredPrograms[0].id); else checkLiveConflict()"
                                    :class="selectedZoneFilter === '2' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition cursor-pointer">
                                B Zone
                            </button>
                            <button type="button" @click="selectedZoneFilter = '3'; if (filteredPrograms.length) onProgramSelect(filteredPrograms[0].id); else checkLiveConflict()"
                                    :class="selectedZoneFilter === '3' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition cursor-pointer">
                                C Zone
                            </button>
                            <button type="button" @click="selectedZoneFilter = '4'; if (filteredPrograms.length) onProgramSelect(filteredPrograms[0].id); else checkLiveConflict()"
                                    :class="selectedZoneFilter === '4' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition cursor-pointer">
                                Mix Zone
                            </button>
                        </div>

                        <!-- Instant Search Input -->
                        <div class="relative">
                            <input type="text" x-model="programSearch" @input="if (filteredPrograms.length) { onProgramSelect(filteredPrograms[0].id); } else { checkLiveConflict(); }"
                                   placeholder="Type to filter programs (e.g. Story, 112, Essay, Malayalam)..."
                                   class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-[#be1e2d] bg-white">
                            <button type="button" x-show="programSearch" @click="programSearch = ''; checkLiveConflict()" 
                                    class="absolute right-2.5 top-1.5 text-xs text-slate-400 hover:text-slate-700">✕</button>
                        </div>

                        <!-- Filtered Select Dropdown -->
                        <select name="program_id" x-model="formProgramId" @change="onProgramSelect($event.target.value)" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-[#be1e2d] focus:border-[#be1e2d] bg-white">
                            <option value="">-- Choose Program / Item --</option>
                            <template x-for="p in filteredPrograms" :key="p.id">
                                <option :value="p.id" 
                                        x-text="`[${p.code}] ${p.name} (${p.duration}m, ${p.zone_name}) ${p.is_scheduled ? '— Scheduled' : ''}`">
                                </option>
                            </template>
                        </select>
                    </div>

                    <!-- Stage / Venue Selection Chips -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 font-sora">Stage & Venue <span class="text-red-500">*</span></label>
                            <button type="button" @click="stageModal = true" class="text-[11px] font-bold text-[#be1e2d] hover:underline cursor-pointer">+ New Venue</button>
                        </div>
                        <input type="hidden" name="stage_id" :value="formStageId">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            @foreach($stages as $stg)
                                <button type="button"
                                        @click="formStageId = '{{ $stg->id }}'; checkLiveConflict()"
                                        :class="formStageId == '{{ $stg->id }}' ? 'bg-[#be1e2d] text-white border-[#be1e2d] shadow-2xs font-bold' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="px-2.5 py-2 rounded-xl border text-center transition-all cursor-pointer">
                                    <div class="text-xs">{{ $stg->name }}</div>
                                    <div class="text-[10px] font-mono opacity-80">{{ $stg->venue }}</div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Date & Time Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 font-sora mb-1">Date</label>
                            <input type="date" name="date" x-model="formDate" @change="checkLiveConflict()" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-[#be1e2d] bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 font-sora mb-1">Time (24h or Preset)</label>
                            <input type="time" name="time" x-model="formTime" @change="checkLiveConflict()" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-[#be1e2d] bg-white">
                        </div>
                    </div>

                    <!-- Quick Time Presets (Mobile Friendly Chips) -->
                    <div>
                        <div class="text-[10px] font-mono font-bold text-slate-400 mb-1">QUICK TIME PRESETS</div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @foreach(['16:40' => '04:40 PM', '17:10' => '05:10 PM', '21:15' => '09:15 PM', '21:45' => '09:45 PM', '22:25' => '10:25 PM'] as $tVal => $tLbl)
                                <button type="button" @click="formTime = '{{ $tVal }}'; checkLiveConflict()"
                                        :class="formTime === '{{ $tVal }}' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-mono transition cursor-pointer">
                                    {{ $tLbl }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Duration Presets -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-bold text-slate-700 font-sora">Duration (Minutes)</label>
                            <span class="text-xs font-mono font-bold text-slate-800" x-text="`${formDuration} min`"></span>
                        </div>
                        <input type="hidden" name="duration" :value="formDuration">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @foreach([15, 20, 30, 45, 60] as $dur)
                                <button type="button" @click="formDuration = {{ $dur }}; checkLiveConflict()"
                                        :class="formDuration == {{ $dur }} ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="px-3 py-1.5 rounded-lg text-xs font-mono transition cursor-pointer">
                                    {{ $dur == 30 ? '30m (Std)' : ($dur >= 60 ? '1 hr' : "{$dur}m") }}
                                </button>
                            @endforeach
                            <input type="number" x-model="formDuration" @input="checkLiveConflict()" placeholder="Custom" min="5" max="360"
                                   class="w-20 px-2 py-1 rounded-lg border border-slate-200 text-xs font-mono text-center">
                        </div>
                    </div>

                    <!-- Live Conflict Indicator in Modal -->
                    <div class="pt-2">
                        <div x-show="conflictCheckLoading" class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-500 flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Checking for schedule clashes...</span>
                        </div>

                        <template x-if="slotConflictResult && slotConflictResult.has_conflicts">
                            <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-900 space-y-1">
                                <div class="font-bold flex items-center gap-1.5 text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <span x-text="`Warning: ${slotConflictResult.count} Clash(es) Detected!`"></span>
                                </div>
                                <template x-if="slotConflictResult.stage_conflict">
                                    <p class="text-[11px]" x-text="slotConflictResult.stage_conflict.message"></p>
                                </template>
                                <template x-for="sc in slotConflictResult.student_conflicts" :key="sc.student_id">
                                    <p class="text-[11px]" x-text="sc.message"></p>
                                </template>
                            </div>
                        </template>

                        <template x-if="slotConflictResult && !slotConflictResult.has_conflicts">
                            <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>No clashes detected. Timing is completely clear!</span>
                            </div>
                        </template>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="slotModal = false"
                                class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold text-xs cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs uppercase tracking-wider transition shadow-sm cursor-pointer">
                            <span x-text="isEditing ? 'Save Changes' : 'Schedule Slot'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- QUICK ADD STAGE / VENUE MODAL -->
    <div x-show="stageModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="stageModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-sm p-6"
                 @click.away="stageModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 font-sora">Add New Stage / Venue</h3>
                    <button type="button" @click="stageModal = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.schedules.stages.store') }}" class="space-y-4 mt-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 font-sora mb-1">Stage Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. Stage 09 or Auditorium 2"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-[#be1e2d] bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 font-sora mb-1">Venue Code / Room <span class="text-red-500">*</span></label>
                        <input type="text" name="location" required placeholder="e.g. NF4, Library, Hall 1"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-[#be1e2d] bg-white">
                        <p class="text-[11px] text-slate-400 mt-1">This will display in the 'Venue' column on the official PDF.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 font-sora mb-1">Seating Capacity</label>
                        <input type="number" name="capacity" value="150" min="10" max="5000"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-[#be1e2d] bg-white">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="stageModal = false" class="px-3.5 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold text-xs cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs uppercase tracking-wider transition shadow-sm cursor-pointer">
                            Save Venue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
