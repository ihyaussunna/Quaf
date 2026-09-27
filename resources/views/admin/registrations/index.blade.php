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

    <!-- Status Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3 overflow-x-auto text-xs font-mono font-semibold">
        <a href="{{ route('admin.registrations.index', array_merge(request()->query(), ['status' => 'all'])) }}" 
           class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 {{ ($status ?? 'all') === 'all' ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <span>All Entries</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($status ?? 'all') === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">
                {{ $totalEntriesCount }}
            </span>
        </a>
        <a href="{{ route('admin.registrations.index', array_merge(request()->query(), ['status' => 'verified'])) }}" 
           class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 {{ ($status ?? '') === 'verified' ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <span>Verified</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($status ?? '') === 'verified' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                {{ $verifiedCount }}
            </span>
        </a>
        <a href="{{ route('admin.registrations.index', array_merge(request()->query(), ['status' => 'rejected'])) }}" 
           class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 {{ ($status ?? '') === 'rejected' ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <span>Rejected</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($status ?? '') === 'rejected' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800' }}">
                {{ $rejectedCount }}
            </span>
        </a>
        @if($pendingCount > 0)
            <a href="{{ route('admin.registrations.index', array_merge(request()->query(), ['status' => 'pending'])) }}" 
               class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 {{ ($status ?? '') === 'pending' ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <span>Pending</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($status ?? '') === 'pending' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">
                    {{ $pendingCount }}
                </span>
            </a>
        @endif
    </div>

    <!-- Filters Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.registrations.index') }}" class="flex flex-wrap items-center justify-between gap-3">
            <input type="hidden" name="status" value="{{ $status ?? 'all' }}">

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
                        <th class="px-5 py-3.5">Status</th>
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
                            <td class="px-5 py-3.5">
                                @if($entry->status === 'verified')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Verified
                                    </span>
                                @elseif($entry->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800 animate-pulse">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Pending
                                    </span>
                                @elseif($entry->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-rose-100 text-rose-800">
                                        Rejected
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                        {{ $entry->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">

                                    @if($entry->status !== 'rejected')
                                        <form method="POST" action="{{ route('admin.registrations.reject', $entry) }}" class="inline" onsubmit="return confirm('Reject this entry? The slot will be released.');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-medium text-xs transition-colors">
                                                Reject
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.registrations.destroy', $entry) }}" class="inline" onsubmit="return confirm('Delete this entry permanently?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
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
