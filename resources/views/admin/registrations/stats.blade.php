@extends('layouts.admin', ['title' => 'Group Entry Statistics'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-sora font-black text-slate-900 tracking-tight">Group Entry Statistics</h1>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800 font-bold text-xs font-mono">
                    {{ $statsData['total_programs'] }} Competitions
                </span>
            </div>
            <p class="text-xs font-mono text-slate-500 mt-1">Live matrix tracking Registered (Full), Partial, and Pending competition entries across all 5 groups.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.registrations.index') }}" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-medium text-xs font-mono flex items-center gap-1.5 shadow-xs transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span>View Program Entries</span>
            </a>
            <a href="{{ route('admin.registrations.create') }}" class="px-4 py-2.5 rounded-xl bg-[#005c94] hover:bg-[#004875] text-white font-medium text-xs font-mono flex items-center gap-1.5 shadow-sm transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Entry</span>
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
            <span class="text-[10px] font-mono text-slate-500 mt-0.5 block">0 students enrolled</span>
        </div>
    </div>

    <!-- 5 Group Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3.5">
        @foreach($statsData['groups'] as $stat)
            @php
                $grp = $stat['group'];
                $color = $grp->color_hex ?? '#005c94';
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
                            <span>Quota Fulfilled</span>
                            <span class="font-bold text-slate-800">{{ $stat['progress_percent'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="h-1.5 rounded-full transition-all" style="width: {{ $stat['progress_percent'] }}%; background-color: {{ $color }}"></div>
                        </div>
                    </div>

                    <!-- Status Breakdown Badges -->
                    <div class="grid grid-cols-3 gap-1.5 mt-3 text-center">
                        <div class="p-1.5 rounded-xl bg-emerald-50 border border-emerald-100 flex flex-col items-center">
                            <span class="text-xs font-mono font-bold text-emerald-800">{{ $stat['full_count'] }}</span>
                            <span class="text-[9px] font-mono font-bold text-emerald-600 uppercase">Registered</span>
                        </div>
                        <div class="p-1.5 rounded-xl bg-amber-50 border border-amber-100 flex flex-col items-center">
                            <span class="text-xs font-mono font-bold text-amber-800">{{ $stat['partial_count'] }}</span>
                            <span class="text-[9px] font-mono font-bold text-amber-600 uppercase">Partial</span>
                        </div>
                        <div class="p-1.5 rounded-xl bg-rose-50 border border-rose-100 flex flex-col items-center">
                            <span class="text-xs font-mono font-bold text-rose-800">{{ $stat['pending_count'] }}</span>
                            <span class="text-[9px] font-mono font-bold text-rose-600 uppercase">Pending</span>
                        </div>
                    </div>

                    <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] font-mono text-slate-500">
                        <span>Entries: <strong class="text-slate-800">{{ $stat['total_entries'] }}</strong></span>
                        <span>Verif: <strong class="text-emerald-700">{{ $stat['verified_entries'] }}</strong></span>
                    </div>
                </div>

                <div class="pt-2 flex items-center gap-1.5">
                    <a href="{{ route('admin.registrations.stats', array_filter(['group' => $grp->id, 'zone_id' => $selectedZoneId, 'filter_status' => $filterStatus, 'search' => $search])) }}" 
                       class="flex-1 py-1.5 px-2 rounded-xl text-center font-mono text-[10px] font-bold transition-colors {{ $isSelected ? 'bg-slate-900 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                        {{ $isSelected ? 'Selected' : 'Focus Group' }}
                    </a>
                    <a href="{{ route('admin.registrations.index', ['group' => $grp->id]) }}" 
                       class="py-1.5 px-2.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-mono text-[10px] font-bold text-center transition-colors" title="View entries table for this group">
                        List &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Master Competition Registration Matrix Table -->
    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-xs space-y-4 p-5">
        <!-- Matrix Filter Header -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div class="flex flex-wrap items-center gap-2">
                <!-- Zone Filter Tabs -->
                <a href="{{ route('admin.registrations.stats', array_filter(['filter_status' => $filterStatus, 'group' => $selectedGroupId, 'search' => $search])) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-mono font-bold transition-colors {{ !$selectedZoneId ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    All Zones
                </a>
                @foreach($zones as $z)
                    <a href="{{ route('admin.registrations.stats', array_filter(['zone_id' => $z->id, 'filter_status' => $filterStatus, 'group' => $selectedGroupId, 'search' => $search])) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-mono font-bold transition-colors {{ (string)$selectedZoneId === (string)$z->id ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $z->name }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('admin.registrations.stats') }}" class="flex flex-wrap items-center gap-3">
                @if($selectedZoneId)
                    <input type="hidden" name="zone_id" value="{{ $selectedZoneId }}">
                @endif
                @if($selectedGroupId)
                    <input type="hidden" name="group" value="{{ $selectedGroupId }}">
                @endif

                <!-- Status Filter -->
                <select name="filter_status" onchange="this.form.submit()" class="px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-slate-700 focus:outline-none font-mono">
                    <option value="all" {{ $filterStatus === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="partial" {{ $filterStatus === 'partial' ? 'selected' : '' }}>Partial Only (Slots Remaining)</option>
                    <option value="pending" {{ $filterStatus === 'pending' ? 'selected' : '' }}>Pending Only (0 Enrolled)</option>
                    <option value="full" {{ $filterStatus === 'full' ? 'selected' : '' }}>Registered Only (Full Quota)</option>
                </select>

                <!-- Search box -->
                <div class="relative w-full sm:w-56">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search program name or code..." 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-[#be1e2d] transition-colors pr-8 font-mono">
                    <button type="submit" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-700">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </div>

                @if($selectedZoneId || $selectedGroupId || $filterStatus !== 'all' || $search)
                    <a href="{{ route('admin.registrations.stats') }}" class="text-xs font-mono text-slate-400 hover:text-slate-700">Reset</a>
                @endif
            </form>
        </div>

        <!-- Matrix Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sora">
                <thead class="bg-slate-50/75 text-slate-500 font-semibold border-b border-slate-200 uppercase text-[11px]">
                    <tr>
                        <th class="px-3.5 py-3 w-10 text-center">#</th>
                        <th class="px-3.5 py-3">Code</th>
                        <th class="px-3.5 py-3">Competition</th>
                        <th class="px-3.5 py-3">Zone</th>
                        <th class="px-3.5 py-3 text-center">Limit</th>
                        @foreach($groups as $grp)
                            <th class="px-3 py-3 text-center min-w-[120px]">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $grp->color_hex ?? '#005c94' }}"></span>
                                    <span>{{ $grp->code ?? $grp->name }}</span>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($matrix as $idx => $row)
                        @php $p = $row['program']; @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-3.5 py-3 text-center text-slate-400 font-mono text-[11px]">{{ $loop->iteration }}</td>
                            <td class="px-3.5 py-3 font-mono font-bold text-slate-600 text-xs">{{ $p->code }}</td>
                            <td class="px-3.5 py-3 font-bold text-slate-900">
                                <div>{{ $p->name }}</div>
                                <span class="text-[10px] font-mono text-slate-400 capitalize">{{ $p->type }}</span>
                            </td>
                            <td class="px-3.5 py-3 text-slate-600 font-mono text-[11px]">
                                {{ $p->zone?->name ?? ($p->eligibility ?? 'Mix Zone') }}
                            </td>
                            <td class="px-3.5 py-3 text-center font-mono font-bold text-slate-800 text-xs">
                                {{ $p->limit }}
                            </td>
                            @foreach($groups as $grp)
                                @php
                                    $cell = $row['groups'][$grp->id] ?? ['status' => 'pending', 'enrolled' => 0, 'limit' => $p->limit, 'remaining' => $p->limit];
                                @endphp
                                <td class="px-3 py-3 text-center">
                                    @if($cell['status'] === 'full')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-mono font-bold" title="Full Quota Registered ({{ $cell['enrolled'] }}/{{ $cell['limit'] }})">
                                            <span>✓</span>
                                            <span>{{ $cell['enrolled'] }}/{{ $cell['limit'] }}</span>
                                        </span>
                                    @elseif($cell['status'] === 'partial')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-mono font-bold" title="Partial Registration: {{ $cell['enrolled'] }}/{{ $cell['limit'] }} ({{ $cell['remaining'] }} slot left)">
                                            <span>{{ $cell['enrolled'] }}/{{ $cell['limit'] }}</span>
                                            <span class="text-[9px] text-amber-600 font-normal">({{ $cell['remaining'] }} left)</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-50 text-slate-400 border border-slate-200 text-[10px] font-mono" title="Pending: 0 enrolled out of {{ $cell['limit'] }}">
                                            0/{{ $cell['limit'] }}
                                        </span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 5 + $groups->count() }}" class="px-5 py-12 text-center text-slate-400 font-mono">
                                No competitions found matching your filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Legend -->
        <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-[11px] font-mono text-slate-500">
            <div class="flex flex-wrap items-center gap-4">
                <span class="font-bold text-slate-700">Legend:</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Registered (Full Quota)</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Partial (Open Slots)</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span> Pending (0 Enrolled)</span>
            </div>
            <span>Showing {{ $matrix->count() }} competitions</span>
        </div>
    </div>
</div>
@endsection
