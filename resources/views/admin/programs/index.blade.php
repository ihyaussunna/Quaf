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
            <!-- Open Registration Form -->
            <form method="POST" action="{{ route('admin.programs.bulk-toggle-registration') }}" class="inline">
                @csrf
                <input type="hidden" name="target" value="selected">
                <input type="hidden" name="action" value="open">
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="program_ids[]" :value="id">
                </template>
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold font-mono uppercase tracking-wider transition-all flex items-center gap-1 shadow-sm">
                    <span>Open Registration for Selected</span>
                </button>
            </form>

            <!-- Close Registration Form -->
            <form method="POST" action="{{ route('admin.programs.bulk-toggle-registration') }}" class="inline">
                @csrf
                <input type="hidden" name="target" value="selected">
                <input type="hidden" name="action" value="close">
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="program_ids[]" :value="id">
                </template>
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold font-mono uppercase tracking-wider transition-all flex items-center gap-1 shadow-sm">
                    <span>Close Registration for Selected</span>
                </button>
            </form>
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
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-mono font-bold text-xs px-2 py-0.5 rounded bg-amber-50 text-amber-900 border border-amber-200">
                                    {{ $p->participant_count ?? 2 }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-mono font-medium text-slate-700">
                                {{ $p->entries_count }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.programs.toggle-registration', $p) }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            title="Click to {{ $isDirectOpen ? 'Close' : 'Open' }} registration for this competition"
                                            class="px-2.5 py-1 rounded-full text-[10px] font-bold font-mono inline-flex items-center gap-1 transition-all shadow-2xs {{ $isDirectOpen ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200 border border-emerald-300' : 'bg-rose-100 text-rose-800 hover:bg-rose-200 border border-rose-300' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $isDirectOpen ? 'bg-emerald-600' : 'bg-rose-600' }}"></span>
                                        <span>{{ $isDirectOpen ? 'OPEN' : 'CLOSED' }}</span>
                                    </button>
                                </form>
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
                                    <form method="POST" action="{{ route('admin.programs.toggle-registration', $p) }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-3 py-1.5 {{ $isDirectOpen ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50' }}">
                                            {{ $isDirectOpen ? 'Close Registration' : 'Open Registration' }}
                                        </button>
                                    </form>
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
                            <td colspan="10" class="px-5 py-12 text-center text-slate-400">
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
