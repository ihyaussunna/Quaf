@extends('layouts.admin', ['title' => 'Competitions'])

@section('content')
<div class="space-y-4" x-data="{
    selectedIds: [],
    allIds: @json($programs->pluck('id')->values()),
    toggleAll() {
        if (this.selectedIds.length === this.allIds.length) {
            this.selectedIds = [];
        } else {
            this.selectedIds = [...this.allIds];
        }
    },
    isSelected(id) {
        return this.selectedIds.includes(Number(id));
    },
    toggleId(id) {
        const num = Number(id);
        if (this.selectedIds.includes(num)) {
            this.selectedIds = this.selectedIds.filter(x => x !== num);
        } else {
            this.selectedIds.push(num);
        }
    },
    clearSelection() {
        this.selectedIds = [];
    },
    regOverrides: {},
    regLoading: {},
    isProgRegOpen(id, defaultVal) {
        if (this.regOverrides[id] !== undefined) {
            return this.regOverrides[id];
        }
        return defaultVal;
    },
    async toggleProgramRegistration(progId, url) {
        if (this.regLoading[progId]) return;
        this.regLoading[progId] = true;
        try {
            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '{{ csrf_token() }}';
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json();
            if (data.success) {
                this.regOverrides[progId] = data.is_registration_open;
            } else {
                alert(data.message || 'Error updating registration status.');
            }
        } catch (e) {
            console.error(e);
            alert('Error updating registration status.');
        } finally {
            this.regLoading[progId] = false;
        }
    },
    bulkLoading: false,
    async bulkToggle(target, action) {
        if (this.bulkLoading) return;
        if (target === 'selected' && this.selectedIds.length === 0) {
            alert('Please select competitions first.');
            return;
        }
        this.bulkLoading = true;
        try {
            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '{{ csrf_token() }}';
            const payload = {
                target: target,
                action: action,
                program_ids: target === 'selected' ? this.selectedIds : []
            };
            const res = await fetch('{{ route('admin.programs.bulk-toggle-registration') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                if (target === 'selected') {
                    this.selectedIds.forEach(id => {
                        this.regOverrides[id] = (action === 'open');
                    });
                }
            } else {
                alert(data.message || 'Error updating registration status.');
            }
        } catch (e) {
            console.error(e);
            alert('Error updating registration status.');
        } finally {
            this.bulkLoading = false;
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 font-sora">Competitions</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage events, schedule categories, and registration status</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.print.programs') }}" target="_blank" class="px-3.5 py-2 rounded-lg bg-white border border-slate-200 text-slate-800 font-semibold text-xs hover:bg-slate-50 hover:border-[#be1e2d] transition-all flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print / PDF Report</span>
            </a>
            <a href="{{ route('admin.programs.create') }}" class="px-4 py-2 rounded-lg bg-[#be1e2d] hover:bg-[#a01624] text-white font-medium text-xs flex items-center gap-1.5 shadow-xs transition-colors">
                <span>+</span> <span>New Competition</span>
            </a>
        </div>
    </div>

    <!-- Program Summary Counts & Quick Filter Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Total Competitions -->
        <a href="{{ route('admin.programs.index') }}"
           class="p-3 rounded-xl border transition-all shadow-xs flex flex-col justify-between {{ empty($status) && ($isStage === null || $isStage === '') ? 'bg-slate-900 border-slate-900 text-white ring-2 ring-slate-900/10' : 'bg-white border-slate-200 text-slate-800 hover:border-slate-300 hover:bg-slate-50/50' }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ empty($status) && ($isStage === null || $isStage === '') ? 'text-slate-300' : 'text-slate-500' }}">Total</span>
                <span class="text-[10px] font-mono font-semibold {{ empty($status) && ($isStage === null || $isStage === '') ? 'text-slate-300' : 'text-slate-400' }}">All</span>
            </div>
            <div class="mt-2">
                <span class="text-2xl font-black font-sora {{ empty($status) && ($isStage === null || $isStage === '') ? 'text-white' : 'text-slate-900' }}">{{ $counts['total'] }}</span>
                <span class="block text-[10px] {{ empty($status) && ($isStage === null || $isStage === '') ? 'text-slate-300' : 'text-slate-400' }} font-sora mt-0.5">Competitions</span>
            </div>
        </a>

        <!-- Upcoming -->
        <a href="{{ route('admin.programs.index', array_merge(request()->except(['page']), ['status' => 'upcoming'])) }}"
           class="p-3 rounded-xl border transition-all shadow-xs flex flex-col justify-between {{ $status === 'upcoming' ? 'bg-amber-500 border-amber-600 text-white ring-2 ring-amber-500/20' : 'bg-white border-slate-200 text-slate-800 hover:border-amber-300 hover:bg-amber-50/20' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full {{ $status === 'upcoming' ? 'bg-white' : 'bg-amber-500' }}"></span>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ $status === 'upcoming' ? 'text-amber-100' : 'text-amber-800' }}">Upcoming</span>
                </div>
                <span class="text-[10px] font-mono font-semibold {{ $status === 'upcoming' ? 'text-amber-100' : 'text-amber-600' }}">Pending</span>
            </div>
            <div class="mt-2">
                <span class="text-2xl font-black font-sora {{ $status === 'upcoming' ? 'text-white' : 'text-amber-700' }}">{{ $counts['upcoming'] }}</span>
                <span class="block text-[10px] {{ $status === 'upcoming' ? 'text-amber-100' : 'text-slate-400' }} font-sora mt-0.5">To be held</span>
            </div>
        </a>

        <!-- In Progress -->
        <a href="{{ route('admin.programs.index', array_merge(request()->except(['page']), ['status' => 'in_progress'])) }}"
           class="p-3 rounded-xl border transition-all shadow-xs flex flex-col justify-between {{ $status === 'in_progress' ? 'bg-blue-600 border-blue-700 text-white ring-2 ring-blue-600/20' : 'bg-white border-slate-200 text-slate-800 hover:border-blue-300 hover:bg-blue-50/20' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full {{ $status === 'in_progress' ? 'bg-white animate-pulse' : 'bg-blue-500 animate-pulse' }}"></span>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ $status === 'in_progress' ? 'text-blue-100' : 'text-blue-800' }}">Ongoing</span>
                </div>
                <span class="text-[10px] font-mono font-semibold {{ $status === 'in_progress' ? 'text-blue-100' : 'text-blue-600' }}">Live</span>
            </div>
            <div class="mt-2">
                <span class="text-2xl font-black font-sora {{ $status === 'in_progress' ? 'text-white' : 'text-blue-700' }}">{{ $counts['in_progress'] }}</span>
                <span class="block text-[10px] {{ $status === 'in_progress' ? 'text-blue-100' : 'text-slate-400' }} font-sora mt-0.5">In Progress</span>
            </div>
        </a>

        <!-- Completed -->
        <a href="{{ route('admin.programs.index', array_merge(request()->except(['page']), ['status' => 'completed'])) }}"
           class="p-3 rounded-xl border transition-all shadow-xs flex flex-col justify-between {{ $status === 'completed' ? 'bg-emerald-600 border-emerald-700 text-white ring-2 ring-emerald-600/20' : 'bg-white border-slate-200 text-slate-800 hover:border-emerald-300 hover:bg-emerald-50/20' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full {{ $status === 'completed' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ $status === 'completed' ? 'text-emerald-100' : 'text-emerald-800' }}">Completed</span>
                </div>
                <span class="text-[10px] font-mono font-semibold {{ $status === 'completed' ? 'text-emerald-100' : 'text-emerald-600' }}">Done</span>
            </div>
            <div class="mt-2">
                <span class="text-2xl font-black font-sora {{ $status === 'completed' ? 'text-white' : 'text-emerald-700' }}">{{ $counts['completed'] }}</span>
                <span class="block text-[10px] {{ $status === 'completed' ? 'text-emerald-100' : 'text-slate-400' }} font-sora mt-0.5">Evaluated</span>
            </div>
        </a>

        <!-- Stage Events -->
        <a href="{{ route('admin.programs.index', array_merge(request()->except(['page']), ['is_stage' => '1'])) }}"
           class="p-3 rounded-xl border transition-all shadow-xs flex flex-col justify-between {{ ($isStage ?? '') === '1' ? 'bg-[#be1e2d] border-[#be1e2d] text-white ring-2 ring-[#be1e2d]/20' : 'bg-white border-slate-200 text-slate-800 hover:border-rose-300 hover:bg-rose-50/20' }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ ($isStage ?? '') === '1' ? 'text-rose-100' : 'text-slate-500' }}">Stage</span>
                <span class="text-[10px] font-mono font-semibold {{ ($isStage ?? '') === '1' ? 'text-rose-100' : 'text-slate-400' }}">Venues</span>
            </div>
            <div class="mt-2">
                <span class="text-2xl font-black font-sora {{ ($isStage ?? '') === '1' ? 'text-white' : 'text-slate-900' }}">{{ $counts['stage'] }}</span>
                <span class="block text-[10px] {{ ($isStage ?? '') === '1' ? 'text-rose-100' : 'text-slate-400' }} font-sora mt-0.5">Stage Programs</span>
            </div>
        </a>

        <!-- Off-Stage Events -->
        <a href="{{ route('admin.programs.index', array_merge(request()->except(['page']), ['is_stage' => '0'])) }}"
           class="p-3 rounded-xl border transition-all shadow-xs flex flex-col justify-between {{ ($isStage ?? '') === '0' ? 'bg-indigo-600 border-indigo-700 text-white ring-2 ring-indigo-600/20' : 'bg-white border-slate-200 text-slate-800 hover:border-indigo-300 hover:bg-indigo-50/20' }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ ($isStage ?? '') === '0' ? 'text-indigo-100' : 'text-slate-500' }}">Off-Stage</span>
                <span class="text-[10px] font-mono font-semibold {{ ($isStage ?? '') === '0' ? 'text-indigo-100' : 'text-slate-400' }}">Non-Stage</span>
            </div>
            <div class="mt-2">
                <span class="text-2xl font-black font-sora {{ ($isStage ?? '') === '0' ? 'text-white' : 'text-slate-900' }}">{{ $counts['off_stage'] }}</span>
                <span class="block text-[10px] {{ ($isStage ?? '') === '0' ? 'text-indigo-100' : 'text-slate-400' }} font-sora mt-0.5">Written / Studio</span>
            </div>
        </a>
    </div>

    <!-- Category Registration Control Widgets (Stage & Off-Stage) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
        <!-- Stage Programs Registration Toggle Widget -->
        <div class="bg-white border-2 {{ ($isStageRegOpen ?? true) ? 'border-emerald-500/30' : 'border-rose-500/30' }} rounded-xl p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ ($isStageRegOpen ?? true) ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                    <h3 class="text-xs font-bold font-sora text-slate-900 uppercase tracking-wide">Stage Programs Registration</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase {{ ($isStageRegOpen ?? true) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ ($isStageRegOpen ?? true) ? 'OPEN' : 'CLOSED' }}
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 font-sora">
                    {{ ($isStageRegOpen ?? true) ? 'Group leaders can submit entries for stage competitions.' : 'Registration for all stage competitions is currently locked.' }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <form method="POST" action="{{ route('admin.programs.bulk-toggle-registration') }}">
                    @csrf
                    <input type="hidden" name="target" value="stage">
                    <input type="hidden" name="action" value="{{ ($isStageRegOpen ?? true) ? 'close' : 'open' }}">
                    <button type="submit" class="w-full sm:w-auto px-3.5 py-1.5 rounded-lg text-xs font-bold font-mono uppercase tracking-wider transition-all shadow-2xs {{ ($isStageRegOpen ?? true) ? 'bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                        {{ ($isStageRegOpen ?? true) ? 'Close Stage Registration' : 'Open Stage Registration' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Off-Stage Programs Registration Toggle Widget -->
        <div class="bg-white border-2 {{ ($isOffStageRegOpen ?? true) ? 'border-emerald-500/30' : 'border-rose-500/30' }} rounded-xl p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ ($isOffStageRegOpen ?? true) ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                    <h3 class="text-xs font-bold font-sora text-slate-900 uppercase tracking-wide">Off-Stage Programs Registration</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase {{ ($isOffStageRegOpen ?? true) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ ($isOffStageRegOpen ?? true) ? 'OPEN' : 'CLOSED' }}
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 font-sora">
                    {{ ($isOffStageRegOpen ?? true) ? 'Group leaders can submit entries for off-stage competitions.' : 'Registration for all off-stage competitions is currently locked.' }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <form method="POST" action="{{ route('admin.programs.bulk-toggle-registration') }}">
                    @csrf
                    <input type="hidden" name="target" value="off_stage">
                    <input type="hidden" name="action" value="{{ ($isOffStageRegOpen ?? true) ? 'close' : 'open' }}">
                    <button type="submit" class="w-full sm:w-auto px-3.5 py-1.5 rounded-lg text-xs font-bold font-mono uppercase tracking-wider transition-all shadow-2xs {{ ($isOffStageRegOpen ?? true) ? 'bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                        {{ ($isOffStageRegOpen ?? true) ? 'Close Off-Stage Registration' : 'Open Off-Stage Registration' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-xs">
        <form method="GET" action="{{ route('admin.programs.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <!-- Search -->
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search competitions..."
                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-900 focus:outline-none focus:border-[#be1e2d] transition-all">
            </div>

            <!-- Dropdowns -->
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Statuses</option>
                    <option value="upcoming" {{ $status === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                <select name="reg_status" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Registrations</option>
                    <option value="open" {{ ($regStatus ?? '') === 'open' ? 'selected' : '' }}>Reg: Open</option>
                    <option value="closed" {{ ($regStatus ?? '') === 'closed' ? 'selected' : '' }}>Reg: Closed</option>
                </select>

                <select name="is_stage" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Stages</option>
                    <option value="1" {{ ($isStage ?? '') === '1' ? 'selected' : '' }}>Stage Programs</option>
                    <option value="0" {{ ($isStage ?? '') === '0' ? 'selected' : '' }}>Non-Stage (Off-Stage)</option>
                </select>

                <select name="type" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Types</option>
                    <option value="individual" {{ ($type ?? '') === 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="group" {{ ($type ?? '') === 'group' ? 'selected' : '' }}>Group</option>
                </select>

                <select name="zone" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($zones as $zKey => $zVal)
                        @php
                            $zoneName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                            $zoneValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                        @endphp
                        <option value="{{ $zoneValue }}" {{ ($zone ?? '') === $zoneValue ? 'selected' : '' }}>{{ $zoneName }}</option>
                    @endforeach
                </select>

                @if($search || $status || $zone || ($type ?? '') || ($regStatus ?? '') || ($isStage !== null && $isStage !== ''))
                    <a href="{{ route('admin.programs.index') }}" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 bg-slate-100 rounded-lg">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Floating Bulk Selection Action Bar for Selected Programs -->
    <div x-show="selectedIds.length > 0" 
         x-transition 
         class="bg-slate-900 text-white rounded-xl p-3 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-3 border border-slate-800"
         style="display: none;">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
            <span class="font-sora text-xs font-bold">
                <span class="font-mono text-amber-300" x-text="selectedIds.length"></span> Competitions Selected
            </span>
            <button type="button" @click="clearSelection()" class="text-xs text-slate-400 hover:text-white underline ml-2 font-mono">
                Clear
            </button>
        </div>

        <div class="flex items-center gap-2">
            <!-- Open Registration Button -->
            <button type="button"
                    @click.prevent="bulkToggle('selected', 'open')"
                    :disabled="bulkLoading"
                    class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold font-mono uppercase tracking-wider transition-all flex items-center gap-1 shadow-sm cursor-pointer">
                <span>Open Registration for Selected</span>
            </button>

            <!-- Close Registration Button -->
            <button type="button"
                    @click.prevent="bulkToggle('selected', 'close')"
                    :disabled="bulkLoading"
                    class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold font-mono uppercase tracking-wider transition-all flex items-center gap-1 shadow-sm cursor-pointer">
                <span>Close Registration for Selected</span>
            </button>
        </div>
    </div>

    <!-- Competitions Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sora">
                <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 uppercase text-[11px] font-mono">
                    <tr>
                        <th class="px-4 py-3.5 w-10 text-center">
                            <input type="checkbox" 
                                   @change="toggleAll()" 
                                   :checked="selectedIds.length === allIds.length && allIds.length > 0" 
                                   class="rounded border-slate-300 text-[#be1e2d] focus:ring-[#be1e2d] cursor-pointer">
                        </th>
                        <th class="px-4 py-3.5">Prog ID</th>
                        <th class="px-5 py-3.5">Programme Name</th>
                        <th class="px-4 py-3.5">Type</th>
                        <th class="px-4 py-3.5">Stage / Off Stage</th>
                        <th class="px-4 py-3.5">Zone</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5">Limit</th>
                        <th class="px-4 py-3.5">Entries</th>
                        <th class="px-4 py-3.5 text-center">Registration</th>
                        <th class="px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($programs as $p)
                        @php
                            $isDirectOpen = (bool) ($p->is_registration_open ?? true);
                            $isEffectiveOpen = $p->isRegistrationOpen();
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors" :class="isSelected({{ $p->id }}) ? 'bg-amber-50/40' : ''">
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <input type="checkbox" 
                                       :value="{{ $p->id }}" 
                                       :checked="isSelected({{ $p->id }})" 
                                       @change="toggleId({{ $p->id }})" 
                                       class="rounded border-slate-300 text-[#be1e2d] focus:ring-[#be1e2d] cursor-pointer">
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-1 rounded font-mono font-bold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $p->code }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-slate-900 block text-sm">{{ $p->name }}</span>
                                @if($p->malayalam_name)
                                    <span class="text-[11px] text-[#005c94] font-medium block mt-0.5 font-ml">{{ $p->malayalam_name }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if(($p->type ?? 'individual') === 'group')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        Group
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        Individual
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($p->is_stage)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Stage
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Non Stage
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-mono text-xs font-semibold px-2 py-0.5 bg-blue-50 text-blue-800 rounded border border-blue-200">
                                    {{ $p->eligibility ?? 'A Zone' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($p->status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Completed
                                    </span>
                                @elseif(in_array($p->status, ['in_progress', 'ongoing']))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase bg-blue-100 text-blue-800 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                        In Progress
                                    </span>
                                @elseif($p->status === 'cancelled')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase bg-rose-100 text-rose-800 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        Cancelled
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase bg-amber-100 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                        Upcoming
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-mono font-bold text-xs px-2 py-0.5 rounded bg-amber-50 text-amber-900 border border-amber-200">
                                    {{ $p->participant_count ?? 2 }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-mono font-medium text-slate-700">
                                {{ $p->entries_count }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <button type="button" 
                                        @click.prevent="toggleProgramRegistration('{{ $p->id }}', '{{ route('admin.programs.toggle-registration', $p) }}')"
                                        :disabled="regLoading['{{ $p->id }}']"
                                        :title="isProgRegOpen('{{ $p->id }}', {{ $isDirectOpen ? 'true' : 'false' }}) ? 'Click to Close registration for this competition' : 'Click to Open registration for this competition'"
                                        class="px-2.5 py-1 rounded-full text-[10px] font-bold font-mono inline-flex items-center gap-1 transition-all shadow-2xs cursor-pointer"
                                        :class="isProgRegOpen('{{ $p->id }}', {{ $isDirectOpen ? 'true' : 'false' }}) ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200 border border-emerald-300' : 'bg-rose-100 text-rose-800 hover:bg-rose-200 border border-rose-300'">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                          :class="isProgRegOpen('{{ $p->id }}', {{ $isDirectOpen ? 'true' : 'false' }}) ? 'bg-emerald-600' : 'bg-rose-600'"></span>
                                    <span x-text="isProgRegOpen('{{ $p->id }}', {{ $isDirectOpen ? 'true' : 'false' }}) ? 'OPEN' : 'CLOSED'">{{ $isDirectOpen ? 'OPEN' : 'CLOSED' }}</span>
                                </button>
                                @if($isDirectOpen && ! $isEffectiveOpen)
                                    <span class="block text-[9px] text-amber-700 font-sans font-medium mt-0.5" title="{{ $p->getRegistrationClosureReason() }}">
                                        (Parent Locked)
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right relative" x-data="{ menuOpen: false }">
                                <button @click="menuOpen = !menuOpen" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                                    <span class="font-bold text-sm leading-none">···</span>
                                </button>
                                <div x-show="menuOpen" @click.away="menuOpen = false" class="absolute right-5 mt-1 w-44 bg-white border border-slate-200 rounded-lg shadow-lg py-1 z-20 text-left text-xs" style="display: none;">
                                    <a href="{{ route('admin.programs.show', $p) }}" class="block px-3 py-1.5 text-slate-700 hover:bg-slate-50">View Details</a>
                                    <a href="{{ route('admin.programs.edit', $p) }}" class="block px-3 py-1.5 text-slate-700 hover:bg-slate-50">Edit</a>
                                    <button type="button" 
                                            @click.prevent="toggleProgramRegistration('{{ $p->id }}', '{{ route('admin.programs.toggle-registration', $p) }}'); menuOpen = false"
                                            class="w-full text-left px-3 py-1.5 font-medium cursor-pointer"
                                            :class="isProgRegOpen('{{ $p->id }}', {{ $isDirectOpen ? 'true' : 'false' }}) ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50'">
                                        <span x-text="isProgRegOpen('{{ $p->id }}', {{ $isDirectOpen ? 'true' : 'false' }}) ? 'Close Registration' : 'Open Registration'">{{ $isDirectOpen ? 'Close Registration' : 'Open Registration' }}</span>
                                    </button>
                                    <form method="POST" action="{{ route('admin.programs.destroy', $p) }}" onsubmit="return confirm('Delete this competition?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-left px-3 py-1.5 text-red-600 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-5 py-12 text-center text-slate-400">
                                No competitions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($programs->hasPages())
            <div class="p-3 border-t border-slate-100">
                {{ $programs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
