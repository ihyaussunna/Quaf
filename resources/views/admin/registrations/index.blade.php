@extends('layouts.admin', ['title' => 'Program Entries & Registrations'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-sora font-black text-slate-900 tracking-tight">Program Entries & Registrations</h1>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800 font-bold text-xs font-mono">
                    {{ $totalEntriesCount }} Total Entries
                </span>
            </div>
            <p class="text-xs font-mono text-slate-500 mt-1">Review participant entries and teams submitted by Group Leaders across all zones.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.registrations.create') }}" class="px-4 py-2.5 rounded-xl bg-[#005c94] hover:bg-[#004875] text-white font-medium text-xs font-mono flex items-center gap-1.5 shadow-sm transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Entry</span>
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs">
            <ul class="list-disc list-inside space-y-1 font-medium">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-mono font-medium shadow-xs">
            {{ session('success') }}
        </div>
    @endif

    <!-- Group-Wise Entries & Quota Statistics -->
    <div id="group-stats" class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-6 shadow-xs space-y-5" x-data="{ activeGroupModal: null }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#be1e2d] animate-pulse"></span>
                    <h2 class="text-base sm:text-lg font-sora font-black text-slate-900 tracking-tight">Group Entry Statistics</h2>
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-mono font-bold">
                        {{ $statsData['total_programs'] }} Competitions
                    </span>
                </div>
                <p class="text-xs font-mono text-slate-500 mt-1">Live tracking of Registered (Full), Partial (Slots remaining), and Pending competition entries per group.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.registrations.stats') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-mono font-bold flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5 text-[#005c94]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Full Statistics Matrix &rarr;</span>
                </a>
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
                <div class="bg-slate-50/80 rounded-2xl border transition-all p-3.5 flex flex-col justify-between space-y-3 {{ $isSelected ? 'border-slate-900 bg-white ring-2 ring-slate-900/10 shadow-sm' : 'border-slate-200 hover:border-slate-300 hover:bg-white' }}">
                    <!-- Card Top: Group Name + Color Dot + Code -->
                    <div>
                        <div class="flex items-center justify-between gap-1.5 mb-1.5">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                                <h3 class="font-sora font-bold text-xs text-slate-900 truncate" title="{{ $grp->name }}">{{ $grp->name }}</h3>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold shrink-0 text-white" style="background-color: {{ $color }}">
                                {{ $grp->code ?? 'GRP' }}
                            </span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-2 space-y-1">
                            <div class="flex items-center justify-between text-[10px] font-mono text-slate-500">
                                <span>Progress</span>
                                <span class="font-bold text-slate-800">{{ $stat['progress_percent'] }}%</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded-full transition-all" style="width: {{ $stat['progress_percent'] }}%; background-color: {{ $color }}"></div>
                            </div>
                        </div>

                        <!-- Status Counts Grid (Registered, Partial, Pending) -->
                        <div class="grid grid-cols-3 gap-1.5 mt-3 text-center">
                            <!-- Registered / Full -->
                            <div class="p-1.5 rounded-xl bg-emerald-50 border border-emerald-100 flex flex-col items-center">
                                <span class="text-xs font-mono font-bold text-emerald-800">{{ $stat['full_count'] }}</span>
                                <span class="text-[9px] font-mono font-bold text-emerald-600 uppercase">Registered</span>
                            </div>
                            <!-- Partial -->
                            <div class="p-1.5 rounded-xl bg-amber-50 border border-amber-100 flex flex-col items-center">
                                <span class="text-xs font-mono font-bold text-amber-800">{{ $stat['partial_count'] }}</span>
                                <span class="text-[9px] font-mono font-bold text-amber-600 uppercase">Partial</span>
                            </div>
                            <!-- Pending -->
                            <div class="p-1.5 rounded-xl bg-rose-50 border border-rose-100 flex flex-col items-center">
                                <span class="text-xs font-mono font-bold text-rose-800">{{ $stat['pending_count'] }}</span>
                                <span class="text-[9px] font-mono font-bold text-rose-600 uppercase">Pending</span>
                            </div>
                        </div>

                        <!-- Entries breakdown (Total, Verified, Pending Verification) -->
                        <div class="mt-2.5 pt-2 border-t border-slate-200/60 flex items-center justify-between text-[10px] font-mono text-slate-500">
                            <span>Entries: <strong class="text-slate-800">{{ $stat['total_entries'] }}</strong></span>
                            <span>Verif: <strong class="text-emerald-700">{{ $stat['verified_entries'] }}</strong> • Pend: <strong class="text-amber-700">{{ $stat['pending_verif_entries'] }}</strong></span>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="pt-2 flex items-center gap-1.5">
                        <button type="button" @click="activeGroupModal = '{{ $grp->id }}'" 
                                class="flex-1 py-1.5 px-2 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 font-mono text-[10px] font-bold text-center transition-colors">
                            Breakdown
                        </button>
                        @if($isSelected)
                            <a href="{{ route('admin.registrations.index', array_filter(['zone_id' => $selectedZoneId, 'search' => $search])) }}" 
                               class="py-1.5 px-2.5 rounded-xl bg-slate-900 text-white font-mono text-[10px] font-bold text-center transition-colors">
                                Selected ✓
                            </a>
                        @else
                            <a href="{{ route('admin.registrations.index', array_filter(['group' => $grp->id, 'zone_id' => $selectedZoneId, 'search' => $search])) }}" 
                               class="py-1.5 px-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-[10px] font-bold text-center transition-colors">
                                Filter
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Breakdown Modals for each group -->
        @foreach($statsData['groups'] as $stat)
            @php $grp = $stat['group']; @endphp
            <div x-show="activeGroupModal === '{{ $grp->id }}'" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
                 @keydown.escape.window="activeGroupModal = null">
                <div class="bg-white w-full max-w-3xl rounded-3xl border border-slate-200 shadow-2xl p-6 space-y-4 max-h-[85vh] flex flex-col"
                     @click.away="activeGroupModal = null"
                     x-data="{ tab: 'partial' }">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded-full" style="background-color: {{ $grp->color_hex ?? '#005c94' }}"></span>
                            <h3 class="text-lg font-sora font-black text-slate-900">{{ $grp->name }}</h3>
                            <span class="px-2 py-0.5 rounded text-xs font-mono font-bold text-white" style="background-color: {{ $grp->color_hex ?? '#005c94' }}">
                                {{ $grp->code }}
                            </span>
                        </div>
                        <button type="button" @click="activeGroupModal = null" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Tabs: Partial, Pending, Full -->
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-2 text-xs font-mono">
                        <button type="button" @click="tab = 'partial'" :class="tab === 'partial' ? 'bg-amber-100 text-amber-900 font-bold' : 'text-slate-500 hover:bg-slate-100'" class="px-3 py-1.5 rounded-xl transition">
                            Partial ({{ $stat['partial_count'] }})
                        </button>
                        <button type="button" @click="tab = 'pending'" :class="tab === 'pending' ? 'bg-rose-100 text-rose-900 font-bold' : 'text-slate-500 hover:bg-slate-100'" class="px-3 py-1.5 rounded-xl transition">
                            Pending ({{ $stat['pending_count'] }})
                        </button>
                        <button type="button" @click="tab = 'full'" :class="tab === 'full' ? 'bg-emerald-100 text-emerald-900 font-bold' : 'text-slate-500 hover:bg-slate-100'" class="px-3 py-1.5 rounded-xl transition">
                            Registered ({{ $stat['full_count'] }})
                        </button>
                    </div>

                    <!-- Modal Content list (Scrollable) -->
                    <div class="overflow-y-auto flex-1 divide-y divide-slate-100 text-xs">
                        <!-- Partial Tab -->
                        <div x-show="tab === 'partial'" class="space-y-2 py-2">
                            @forelse($stat['partial_programs'] as $pp)
                                <div class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50/50 border border-amber-100 hover:bg-amber-50 transition">
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $pp['name'] }}</div>
                                        <div class="text-[10px] font-mono text-slate-500">{{ $pp['code'] }} • {{ $pp['zone'] }} • {{ ucfirst($pp['type']) }}</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-mono font-bold text-[11px]">
                                            {{ $pp['enrolled'] }} / {{ $pp['limit'] }} Filled
                                        </span>
                                        <span class="block text-[10px] font-mono text-amber-600 mt-0.5">({{ $pp['remaining'] }} slot left)</span>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-slate-400 font-mono">No partially registered programs for this group.</div>
                            @endforelse
                        </div>

                        <!-- Pending Tab -->
                        <div x-show="tab === 'pending'" class="space-y-2 py-2">
                            @forelse($stat['pending_programs'] as $pp)
                                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 hover:bg-slate-100/70 transition">
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $pp['name'] }}</div>
                                        <div class="text-[10px] font-mono text-slate-400">{{ $pp['code'] }} • {{ $pp['zone'] }} • {{ ucfirst($pp['type']) }}</div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 font-mono font-bold text-[10px]">
                                        0 / {{ $pp['limit'] }} (Unregistered)
                                    </span>
                                </div>
                            @empty
                                <div class="py-8 text-center text-slate-400 font-mono">All programs have at least one registration.</div>
                            @endforelse
                        </div>

                        <!-- Full Tab -->
                        <div x-show="tab === 'full'" class="space-y-2 py-2">
                            @forelse($stat['full_programs'] as $pp)
                                <div class="flex items-center justify-between p-2.5 rounded-xl bg-emerald-50/40 border border-emerald-100 hover:bg-emerald-50 transition">
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $pp['name'] }}</div>
                                        <div class="text-[10px] font-mono text-slate-500">{{ $pp['code'] }} • {{ $pp['zone'] }} • {{ ucfirst($pp['type']) }}</div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-mono font-bold text-[11px]">
                                        ✓ {{ $pp['enrolled'] }} / {{ $pp['limit'] }} Full
                                    </span>
                                </div>
                            @empty
                                <div class="py-8 text-center text-slate-400 font-mono">No fully completed programs yet.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ route('admin.registrations.index', ['group' => $grp->id]) }}" class="text-xs font-mono font-bold text-[#005c94] hover:underline">
                            Filter main table to {{ $grp->name }} &rarr;
                        </a>
                        <button type="button" @click="activeGroupModal = null" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-xs font-bold transition">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filters Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.registrations.index') }}" class="flex flex-wrap items-center justify-between gap-3">

            <div class="flex flex-wrap items-center gap-3">
                <!-- Zone Dropdown -->
                <select name="zone_id" onchange="this.form.submit()" class="px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($zones as $z)
                        <option value="{{ $z->id }}" {{ (string)$selectedZoneId === (string)$z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                    @endforeach
                </select>

                <!-- Group Dropdown -->
                <select name="group" onchange="this.form.submit()" class="px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Groups</option>
                    @foreach($groups as $grp)
                        <option value="{{ $grp->id }}" {{ (string)$selectedGroupId === (string)$grp->id ? 'selected' : '' }}>{{ $grp->name }}</option>
                    @endforeach
                </select>

                @if($selectedZoneId || $selectedGroupId || $search)
                    <a href="{{ route('admin.registrations.index', ['status' => $status ?? 'pending']) }}" class="text-xs font-mono text-slate-400 hover:text-slate-700">Clear Filters</a>
                @endif
            </div>

            <!-- Search box -->
            <div class="relative w-full sm:w-64">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search chest #, student, program..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-[#be1e2d] transition-colors pr-8">
                <button type="submit" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-700">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Verification Table -->
    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sora">
                <thead class="bg-slate-50/75 text-slate-500 font-semibold border-b border-slate-200 uppercase text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5 w-12 text-center">#</th>
                        <th class="px-5 py-3.5">Chest #</th>
                        <th class="px-5 py-3.5">Program</th>
                        <th class="px-5 py-3.5">Group / Team</th>
                        <th class="px-5 py-3.5">Participant / Team Leader</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($entries as $idx => $entry)
                        @php
                            $isGroup = $entry->isGroupEntry();
                            $leaderStudent = $isGroup
                                ? ($entry->participants()->wherePivot('role', 'captain')->first() ?? $entry->student ?? $entry->participants()->first())
                                : $entry->student;
                            $displayStudentId = $leaderStudent?->student_id ?? ($entry->chest_number ? '#' . $entry->chest_number : '—');
                            $displayName = $leaderStudent?->name ?? ($isGroup ? ($entry->group?->name . ' Team') : '—');
                            $displayZone = $leaderStudent?->category ?? ($entry->program?->zone?->name ?? ($entry->program?->eligibility ?? 'Mix Zone'));
                            $displayClass = $leaderStudent?->class_level ?? '—';
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors" x-data="{ showMembers: false }">
                            <td class="px-5 py-3.5 text-center text-slate-400 font-mono text-[11px]">{{ $entries->firstItem() + $idx }}</td>
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-900">
                                <span>{{ $entry->chest_number }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900 text-sm">{{ $entry->program?->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    {{ $entry->program?->code }} • {{ $entry->program?->zone?->name ?? $entry->program?->eligibility }} • {{ ucfirst($entry->program?->type) }}
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $entry->group?->color_hex ?? '#be1e2d' }}"></span>
                                    <span class="font-bold text-slate-900">{{ $entry->group?->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div>
                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                        <span>{{ $displayName }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">({{ $displayStudentId }})</span>
                                    </div>
                                    @if($isGroup)
                                        <div class="text-[11px] text-slate-500 font-mono mt-0.5 flex items-center gap-1">
                                            <span class="text-amber-700 font-semibold">(Group Leader)</span>
                                            <span>• {{ $entry->participants->count() }} members</span>
                                            <button type="button" @click="showMembers = !showMembers" class="text-[#005c94] hover:underline ml-1">
                                                <span x-text="showMembers ? 'hide' : 'view members'"></span>
                                            </button>
                                        </div>

                                        <!-- Expandable members list -->
                                        <div x-show="showMembers" class="mt-2 p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-[11px] space-y-1" style="display: none;">
                                            @foreach($entry->participants as $pIdx => $part)
                                                <div class="flex items-center justify-between text-slate-700">
                                                    <span>{{ $pIdx + 1 }}. {{ $part->name }} (Chest #{{ $part->student_id }})</span>
                                                    @if($part->id === $leaderStudent?->id)
                                                        <span class="px-1.5 py-0.2 rounded bg-amber-200 text-amber-900 font-bold text-[9px]">Leader</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                            Zone: {{ $displayZone }} • Class: {{ $displayClass }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <form method="POST" action="{{ route('admin.registrations.destroy', $entry) }}" class="inline" onsubmit="return confirm('Delete this registration entry for Chest #{{ $entry->chest_number }} ({{ addslashes($entry->program?->name ?? 'Program') }}) permanently?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition-colors" title="Delete Entry">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                No registration entries found matching the filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($entries->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
