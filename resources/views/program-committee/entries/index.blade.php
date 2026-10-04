@extends('layouts.program-committee', ['title' => 'Team Entries Data & Matrix'])

@section('content')
<div class="space-y-6" x-data="{
    modalOpen: false,
    modalTitle: '',
    modalGroup: '',
    modalType: '',
    modalPrograms: [],
    openModal(groupName, type, programs) {
        this.modalGroup = groupName;
        this.modalType = type;
        this.modalTitle = groupName + ' - ' + (type === 'full' ? 'Registered Programs' : (type === 'partial' ? 'Partial Programs (Open Slots)' : 'Pending Programs (0 Enrolled)'));
        this.modalPrograms = programs || [];
        this.modalOpen = true;
    }
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-sora font-black text-slate-900 tracking-tight">Team Entries Data</h1>
                <span class="px-3 py-1 rounded-full bg-slate-900 text-amber-400 font-bold text-xs font-mono">
                    {{ $statsData['total_programs'] }} Competitions
                </span>
            </div>
            <p class="text-xs font-mono text-slate-500 mt-1">
                Live group quota status, competition fulfillment matrix, and candidate entry registry.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('program-committee.programs.index') }}" 
               class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-medium text-xs font-mono flex items-center gap-1.5 shadow-xs transition-colors">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Program List</span>
            </a>
            <a href="{{ route('program-committee.team-entries.export', request()->query()) }}" 
               class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs font-mono flex items-center gap-1.5 shadow-sm transition-colors">
                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('program-committee.print.entries', array_merge(request()->query(), ['mode' => 'group_wise'])) }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-medium text-xs font-mono flex items-center gap-1.5 shadow-sm transition-colors"
               title="Print or Save Group-Wise Entries as PDF">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Group Wise PDF</span>
            </a>
            <a href="{{ route('program-committee.print.entries', array_merge(request()->query(), ['mode' => 'program_wise'])) }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-amber-400 border border-slate-700 font-medium text-xs font-mono flex items-center gap-1.5 shadow-sm transition-colors"
               title="Print or Save Program-Wise Entries as PDF">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Program Wise PDF</span>
            </a>
        </div>
    </div>

    <!-- Overall KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <span class="text-[11px] font-mono uppercase text-slate-400 font-bold block">Total Entries</span>
            <span class="text-2xl font-sora font-black text-slate-900 mt-1 block">{{ $statsData['global_total_entries'] }}</span>
            <span class="text-[10px] font-mono text-slate-500 mt-0.5 block">
                {{ $statsData['global_verified_count'] }} verified • {{ $statsData['global_pending_verif_count'] }} pending
            </span>
        </div>
        <div class="bg-white rounded-2xl border border-emerald-200/80 p-4 shadow-xs">
            <span class="text-[11px] font-mono uppercase text-emerald-600 font-bold block">Full Quotas Met</span>
            <span class="text-2xl font-sora font-black text-emerald-700 mt-1 block">{{ $statsData['global_full_count'] }}</span>
            <span class="text-[10px] font-mono text-slate-500 mt-0.5 block">Programs fully registered</span>
        </div>
        <div class="bg-white rounded-2xl border border-amber-200/80 p-4 shadow-xs">
            <span class="text-[11px] font-mono uppercase text-amber-600 font-bold block">Partial Registrations</span>
            <span class="text-2xl font-sora font-black text-amber-700 mt-1 block">{{ $statsData['global_partial_count'] }}</span>
            <span class="text-[10px] font-mono text-slate-500 mt-0.5 block">Has open quota slots</span>
        </div>
        <div class="bg-white rounded-2xl border border-rose-200/80 p-4 shadow-xs">
            <span class="text-[11px] font-mono uppercase text-rose-600 font-bold block">Pending / Unregistered</span>
            <span class="text-2xl font-sora font-black text-rose-700 mt-1 block">{{ $statsData['global_pending_count'] }}</span>
            <span class="text-[10px] font-mono text-slate-500 mt-0.5 block">0 candidates enrolled</span>
        </div>
    </div>

    <!-- 5 Group Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3.5">
        @foreach($statsData['groups'] as $stat)
            @php
                $grp = $stat['group'];
                $color = $grp->color_hex ?? '#be1e2d';
                $isSelected = (string)$selectedGroupId === (string)$grp->id;
            @endphp
            <div class="bg-white rounded-2xl border transition-all p-4 shadow-xs flex flex-col justify-between space-y-3 {{ $isSelected ? 'border-slate-900 ring-2 ring-slate-900/10' : 'border-slate-200 hover:border-slate-300' }}">
                <div>
                    <div class="flex items-center justify-between gap-1.5 mb-1.5">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                            <h3 class="font-sora font-bold text-xs text-slate-900 truncate" title="{{ $grp->name }}">{{ $grp->name }}</h3>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold shrink-0 text-white" style="background-color: {{ $color }}">
                            {{ $grp->code ?? 'GRP' }}
                        </span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-2 space-y-1">
                        <div class="flex items-center justify-between text-[10px] font-mono text-slate-500">
                            <span>Quota Met</span>
                            <span class="font-bold text-slate-800">{{ $stat['progress_percent'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="h-1.5 rounded-full transition-all" style="width: {{ $stat['progress_percent'] }}%; background-color: {{ $color }}"></div>
                        </div>
                    </div>

                    <!-- Status Breakdown Badges with Click to Inspect -->
                    <div class="grid grid-cols-3 gap-1.5 mt-3 text-center">
                        <button type="button" 
                                @click="openModal('{{ addslashes($grp->name) }}', 'full', {{ json_encode($stat['full_programs']) }})"
                                class="p-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-100 flex flex-col items-center transition cursor-pointer"
                                title="Click to view full programs">
                            <span class="text-xs font-mono font-bold text-emerald-800">{{ $stat['full_count'] }}</span>
                            <span class="text-[9px] font-mono font-bold text-emerald-600 uppercase">Registered</span>
                        </button>
                        <button type="button" 
                                @click="openModal('{{ addslashes($grp->name) }}', 'partial', {{ json_encode($stat['partial_programs']) }})"
                                class="p-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-100 flex flex-col items-center transition cursor-pointer"
                                title="Click to view partial programs">
                            <span class="text-xs font-mono font-bold text-amber-800">{{ $stat['partial_count'] }}</span>
                            <span class="text-[9px] font-mono font-bold text-amber-600 uppercase">Partial</span>
                        </button>
                        <button type="button" 
                                @click="openModal('{{ addslashes($grp->name) }}', 'pending', {{ json_encode($stat['pending_programs']) }})"
                                class="p-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-100 flex flex-col items-center transition cursor-pointer"
                                title="Click to view pending programs">
                            <span class="text-xs font-mono font-bold text-rose-800">{{ $stat['pending_count'] }}</span>
                            <span class="text-[9px] font-mono font-bold text-rose-600 uppercase">Pending</span>
                        </button>
                    </div>

                    <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] font-mono text-slate-500">
                        <span>Entries: <strong class="text-slate-800">{{ $stat['total_entries'] }}</strong></span>
                        <span>Verif: <strong class="text-emerald-700">{{ $stat['verified_entries'] }}</strong></span>
                    </div>
                </div>

                <div class="pt-2 flex items-center gap-1.5">
                    <a href="{{ route('program-committee.team-entries.index', array_filter(['tab' => $activeTab, 'group' => $isSelected ? null : $grp->id, 'zone_id' => $selectedZoneId, 'filter_status' => $filterStatus, 'search' => $search])) }}" 
                       class="flex-1 py-1.5 px-2 rounded-xl text-center font-mono text-[10px] font-bold transition-colors {{ $isSelected ? 'bg-slate-900 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                        {{ $isSelected ? 'Clear Filter' : 'Filter Group' }}
                    </a>
                    <a href="{{ route('program-committee.team-entries.index', ['tab' => 'entries', 'group' => $grp->id]) }}" 
                       class="py-1.5 px-2.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-mono text-[10px] font-bold text-center transition-colors" 
                       title="View itemized entries for this group">
                        List &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Active Navigation Tabs (Matrix vs Itemized List) -->
    <div class="flex items-center justify-between border-b border-slate-200">
        <div class="flex items-center gap-2">
            <a href="{{ route('program-committee.team-entries.index', array_merge(request()->query(), ['tab' => 'matrix'])) }}" 
               class="px-4 py-2.5 text-xs font-mono font-bold border-b-2 transition flex items-center gap-2 {{ $activeTab !== 'entries' ? 'border-[#be1e2d] text-[#be1e2d]' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span>Competition Quota Matrix</span>
            </a>
            <a href="{{ route('program-committee.team-entries.index', array_merge(request()->query(), ['tab' => 'entries'])) }}" 
               class="px-4 py-2.5 text-xs font-mono font-bold border-b-2 transition flex items-center gap-2 {{ $activeTab === 'entries' ? 'border-[#be1e2d] text-[#be1e2d]' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span>Itemized Candidate Entries ({{ $entries->total() }})</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
        <form method="GET" action="{{ route('program-committee.team-entries.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="hidden" name="tab" value="{{ $activeTab }}">

            <!-- Search -->
            <div class="lg:col-span-2 relative">
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="{{ $activeTab === 'entries' ? 'Search chest no, candidate name, program...' : 'Search program by name, code or Malayalam...' }}" 
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-10 pr-4 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#be1e2d] transition">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <!-- Zone Filter -->
            <div>
                <select name="zone_id" onchange="this.form.submit()" 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-xs text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($zones as $z)
                        <option value="{{ $z->id }}" {{ (string)$selectedZoneId === (string)$z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($activeTab !== 'entries')
                <!-- Quota Status Filter -->
                <div>
                    <select name="filter_status" onchange="this.form.submit()" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-xs text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                        <option value="all" {{ $filterStatus === 'all' ? 'selected' : '' }}>All Quota Statuses</option>
                        <option value="full" {{ $filterStatus === 'full' ? 'selected' : '' }}>Full / Registered</option>
                        <option value="partial" {{ $filterStatus === 'partial' ? 'selected' : '' }}>Partial (Has Remaining Slots)</option>
                        <option value="pending" {{ $filterStatus === 'pending' ? 'selected' : '' }}>Pending (0 Candidates)</option>
                    </select>
                </div>
            @else
                <!-- Entry Verification Status -->
                <div>
                    <select name="entry_status" onchange="this.form.submit()" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-xs text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                        <option value="all" {{ $entryStatus === 'all' ? 'selected' : '' }}>All Entry Statuses</option>
                        <option value="verified" {{ $entryStatus === 'verified' ? 'selected' : '' }}>Verified Only</option>
                        <option value="pending" {{ $entryStatus === 'pending' ? 'selected' : '' }}>Pending Verification</option>
                        <option value="rejected" {{ $entryStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-mono font-bold transition">
                    Filter
                </button>
                @if($search || $selectedZoneId || $selectedGroupId || ($filterStatus && $filterStatus !== 'all') || ($entryStatus && $entryStatus !== 'all'))
                    <a href="{{ route('program-committee.team-entries.index', ['tab' => $activeTab]) }}" 
                       class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-mono font-bold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if($activeTab !== 'entries')
        <!-- Tab 1: Competition Matrix View -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div>
                    <h3 class="font-sora font-bold text-sm text-slate-900">Program Quota Fulfillment Matrix</h3>
                    <p class="text-[11px] font-mono text-slate-500">
                        Shows quota fulfillment for each competition across all 5 groups.
                        <span class="font-bold text-emerald-700">Full</span> = Complete quota met,
                        <span class="font-bold text-amber-700">Partial</span> = Registered with open slots,
                        <span class="font-bold text-rose-700">Pending</span> = No candidates registered yet.
                    </p>
                </div>
                <div class="flex items-center gap-2 text-[10px] font-mono">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">Full</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold">Partial</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold">Pending</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono border-collapse">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-500 uppercase tracking-wider">
                            <th class="py-3 px-3.5 sticky left-0 bg-slate-50 z-10 w-28">Code</th>
                            <th class="py-3 px-4 min-w-[220px]">Program Name</th>
                            <th class="py-3 px-3 min-w-[100px]">Zone</th>
                            <th class="py-3 px-3 min-w-[120px]">Format & Limit</th>
                            @foreach($groups as $grp)
                                @php $color = $grp->color_hex ?? '#be1e2d'; @endphp
                                <th class="py-3 px-3 text-center min-w-[140px]" style="border-top: 3px solid {{ $color }}">
                                    <div class="font-bold text-slate-900 font-sora text-[11px]">{{ $grp->name }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">({{ $grp->code }})</div>
                                </th>
                            @endforeach
                            <th class="py-3 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($matrix as $item)
                            @php
                                $p = $item['program'];
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-3.5 font-bold text-slate-900 sticky left-0 bg-white">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-[11px]">
                                        {{ $p->code }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-sora">
                                    <a href="{{ route('program-committee.programs.show', $p) }}" class="font-bold text-slate-900 hover:text-[#be1e2d] transition block">
                                        {{ $p->name }}
                                    </a>
                                    @if($p->malayalam_name)
                                        <span class="text-[11px] font-malayalam text-slate-500 block">{{ $p->malayalam_name }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-slate-50 border border-slate-200 text-slate-700">
                                        {{ $p->zone?->name ?? ($p->eligibility ?? 'Mix Zone') }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="capitalize font-bold text-slate-800">{{ $p->type }}</span>
                                    <span class="text-[10px] text-slate-400 block font-mono">Limit: <strong>{{ $p->limit }}</strong> / grp</span>
                                </td>
                                @foreach($groups as $grp)
                                    @php
                                        $cell = $item['groups'][$grp->id] ?? null;
                                        $status = $cell['status'] ?? 'pending';
                                        $enrolled = $cell['enrolled'] ?? 0;
                                        $limit = $cell['limit'] ?? 1;
                                        $remaining = $cell['remaining'] ?? 0;
                                    @endphp
                                    <td class="py-3 px-3 text-center">
                                        @if($status === 'full')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-[10px] font-bold" title="Full quota met ({{ $enrolled }}/{{ $limit }})">
                                                <span>Full ({{ $enrolled }}/{{ $limit }})</span>
                                            </span>
                                        @elseif($status === 'partial')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-[10px] font-bold" title="Partial: {{ $enrolled }}/{{ $limit }} enrolled, {{ $remaining }} slot(s) remaining">
                                                <span>{{ $enrolled }}/{{ $limit }} ({{ $remaining }} left)</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-400 text-[10px] font-bold" title="Pending: 0 enrolled out of {{ $limit }} slots">
                                                <span>0/{{ $limit }} Pending</span>
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="py-3 px-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('program-committee.programs.rules', $p) }}" 
                                           class="px-2 py-1 rounded-lg bg-amber-50 hover:bg-amber-400 hover:text-slate-950 text-amber-900 font-mono text-[10px] font-bold transition-colors"
                                           title="Rules & Criteria">
                                            Rules
                                        </a>
                                        <a href="{{ route('program-committee.team-entries.index', ['tab' => 'entries', 'search' => $p->code]) }}" 
                                           class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-[10px] font-bold transition-colors"
                                           title="View Entries List for this Program">
                                            Entries
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + $groups->count() }}" class="py-12 text-center text-slate-400">
                                    No competitions found matching your filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- Tab 2: Itemized Candidate Entries View -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="font-sora font-bold text-sm text-slate-900">Registered Candidate Entries</h3>
                    <p class="text-[11px] font-mono text-slate-500">
                        Total {{ $entries->total() }} individual and group entries submitted by teams.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Chest No</th>
                            <th class="py-3 px-4 font-sora">Candidate Name</th>
                            <th class="py-3 px-3">Class</th>
                            <th class="py-3 px-4 font-sora">Program</th>
                            <th class="py-3 px-3">Zone</th>
                            <th class="py-3 px-3">Group</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($entries as $entry)
                            @php
                                $grpColor = $entry->group?->color_hex ?? '#be1e2d';
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    @if($entry->chest_number)
                                        <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200">
                                            {{ $entry->chest_number }}
                                        </span>
                                    @elseif($entry->student?->chest_number)
                                        <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200">
                                            {{ $entry->student->chest_number }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 font-mono">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-sora">
                                    @if($entry->participants->isNotEmpty())
                                        <div class="font-bold text-slate-900">
                                            Group Entry ({{ $entry->participants->count() }} members)
                                        </div>
                                        <div class="text-[11px] text-slate-500 truncate max-w-xs font-mono">
                                            {{ $entry->participants->pluck('name')->implode(', ') }}
                                        </div>
                                    @else
                                        <div class="font-bold text-slate-900">
                                            {{ $entry->student?->name ?? 'Candidate' }}
                                        </div>
                                        @if($entry->student?->student_id)
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $entry->student->student_id }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-mono">
                                    {{ $entry->student?->class ?? '-' }}
                                </td>
                                <td class="py-3 px-4 font-sora">
                                    <div class="font-bold text-slate-900">
                                        {{ $entry->program?->name ?? '-' }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-mono">
                                        {{ $entry->program?->code ?? '-' }} • {{ ucfirst($entry->program?->type ?? 'individual') }}
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-slate-50 border border-slate-200 text-slate-700">
                                        {{ $entry->program?->zone?->name ?? ($entry->program?->eligibility ?? 'Mix Zone') }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold text-white font-mono" style="background-color: {{ $grpColor }}">
                                        {{ $entry->group?->name ?? 'Group' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($entry->status === 'verified')
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Verified</span>
                                    @elseif($entry->status === 'rejected')
                                        <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-bold">Rejected</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">Pending</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right text-slate-400 font-mono text-[10px]">
                                    {{ $entry->created_at?->format('M d, H:i') ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    No candidate entries found matching your filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $entries->links() }}
            </div>
        </div>
    @endif

    <!-- Group Quota Inspector Modal (Alpine.js) -->
    <div x-show="modalOpen" 
         x-cloak
         @keydown.escape.window="modalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalOpen" 
                 @click="modalOpen = false"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="modalOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200">
                
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-sora font-bold text-slate-900" x-text="modalTitle"></h3>
                        <p class="text-xs font-mono text-slate-500 mt-0.5">
                            Competitions list with quota details and enrolled count.
                        </p>
                    </div>
                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 max-h-[60vh] overflow-y-auto">
                    <template x-if="modalPrograms.length === 0">
                        <div class="text-center py-8 text-slate-400 font-mono text-xs">
                            No competitions in this category.
                        </div>
                    </template>

                    <template x-if="modalPrograms.length > 0">
                        <div class="space-y-2 font-mono text-xs">
                            <div class="text-[11px] text-slate-500 mb-3 flex items-center justify-between font-bold">
                                <span>Total Competitions: <span x-text="modalPrograms.length"></span></span>
                            </div>
                            <div class="divide-y divide-slate-100 border border-slate-200 rounded-2xl overflow-hidden">
                                <template x-for="item in modalPrograms" :key="item.id">
                                    <div class="p-3 bg-white hover:bg-slate-50 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-[10px] font-bold text-slate-800" x-text="item.code"></span>
                                                <span class="font-sora font-bold text-slate-900 text-xs truncate" x-text="item.name"></span>
                                            </div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">
                                                <span x-text="item.zone"></span> • <span class="capitalize" x-text="item.type"></span>
                                            </div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <template x-if="modalType === 'full'">
                                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                                    Full (<span x-text="item.enrolled"></span>/<span x-text="item.limit"></span>)
                                                </span>
                                            </template>
                                            <template x-if="modalType === 'partial'">
                                                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">
                                                    <span x-text="item.enrolled"></span>/<span x-text="item.limit"></span> (<span x-text="item.remaining"></span> slot left)
                                                </span>
                                            </template>
                                            <template x-if="modalType === 'pending'">
                                                <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 text-[10px] font-bold">
                                                    0/<span x-text="item.limit"></span> Pending
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-100 text-right">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-mono font-bold transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
