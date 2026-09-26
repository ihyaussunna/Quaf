@extends('layouts.program-committee', ['title' => 'All Programs & Niyamavali'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-serif font-black text-slate-900 tracking-tight">Competitions & Programs</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">
                Manage all festival competitions, schedules, and official rules.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('program-committee.programs.create') }}" 
               class="px-4 py-2 bg-brand-burgundy hover:bg-[#850d18] text-white rounded-xl text-xs font-mono font-bold flex items-center gap-2 shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Add Program</span>
            </a>
            <a href="{{ route('program-committee.niyamavali.print-book', ['zone_id' => $zoneId]) }}" target="_blank"
               class="px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-mono font-semibold flex items-center gap-1.5 transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Rules</span>
            </a>
        </div>
    </div>

    <!-- Quick Status Tabs -->
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3 text-xs font-mono">
        <a href="{{ route('program-committee.programs.index') }}" 
           class="px-3.5 py-1.5 rounded-xl transition {{ empty($rulesStatus) && empty($isStage) ? 'bg-slate-900 text-white font-bold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            All Programs ({{ $totalCount }})
        </a>
        <a href="{{ route('program-committee.programs.index', array_merge(request()->query(), ['rules_status' => 'with_rules'])) }}" 
           class="px-3.5 py-1.5 rounded-xl transition {{ $rulesStatus === 'with_rules' ? 'bg-emerald-600 text-white font-bold' : 'bg-white border border-emerald-200 text-emerald-800 hover:bg-emerald-50' }}">
            Niyamavali Added ({{ $withRulesTotal }}) ✓
        </a>
        <a href="{{ route('program-committee.programs.index', array_merge(request()->query(), ['rules_status' => 'missing_rules'])) }}" 
           class="px-3.5 py-1.5 rounded-xl transition {{ $rulesStatus === 'missing_rules' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-white border border-amber-200 text-amber-800 hover:bg-amber-50' }}">
            Niyamavali Missing ({{ $missingRulesTotal }}) !
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
        <form method="GET" action="{{ route('program-committee.programs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2 relative">
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="Search program by name, Malayalam or code..." 
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-10 pr-4 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-burgundy transition">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <!-- Zone Filter -->
            <div>
                <select name="zone_id" onchange="this.form.submit()" 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-xs text-slate-700 focus:outline-none focus:border-brand-burgundy">
                    <option value="">All Zones</option>
                    @foreach($zones as $z)
                        <option value="{{ $z->id }}" {{ $zoneId == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Type Filter -->
            <div>
                <select name="type" onchange="this.form.submit()" 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-xs text-slate-700 focus:outline-none focus:border-brand-burgundy">
                    <option value="">All Types</option>
                    <option value="individual" {{ $type === 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="group" {{ $type === 'group' ? 'selected' : '' }}>Group</option>
                </select>
            </div>

            <!-- Action Button -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-mono font-bold transition">
                    Filter
                </button>
                @if($search || $zoneId || $type || $rulesStatus)
                    <a href="{{ route('program-committee.programs.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-mono font-bold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Programs Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Code</th>
                        <th class="py-3.5 px-4 font-sans">Program Name</th>
                        <th class="py-3.5 px-4">Zone</th>
                        <th class="py-3.5 px-4">Type</th>
                        <th class="py-3.5 px-4">Duration</th>
                        <th class="py-3.5 px-4 text-center">Rules (Niyamavali)</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($programs as $p)
                        @php
                            $hasRules = !empty($p->rules);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200">
                                    {{ $p->code }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-sans">
                                <a href="{{ route('program-committee.programs.show', $p) }}" class="font-bold text-slate-900 hover:text-brand-burgundy transition block">
                                    {{ $p->name }}
                                </a>
                                @if($p->malayalam_name)
                                    <span class="text-[11px] font-malayalam text-slate-500 block">{{ $p->malayalam_name }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-sans">
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-slate-50 border border-slate-200 text-slate-700">
                                    {{ $p->zone?->name ?? $p->eligibility }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 capitalize">
                                <span>{{ $p->type }}</span>
                                @if($p->type === 'group')
                                    <span class="text-[10px] text-slate-400 block">(Max: {{ $p->participant_count ?? $p->max_participants }})</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                {{ $p->duration_minutes }} Min
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($hasRules)
                                    <a href="{{ route('program-committee.programs.rules', $p) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold hover:bg-emerald-200 transition"
                                       title="Click to view/edit rules">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Added</span>
                                    </a>
                                @else
                                    <a href="{{ route('program-committee.programs.rules', $p) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold hover:bg-amber-200 transition"
                                       title="Click to add rules">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>Add Rules</span>
                                    </a>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('program-committee.programs.rules', $p) }}" 
                                       class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-400 hover:text-slate-950 text-amber-900 font-mono text-[11px] font-bold transition-colors"
                                       title="Niyamavali & Criteria">
                                        Rules
                                    </a>
                                    <a href="{{ route('program-committee.programs.edit', $p) }}" 
                                       class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-[11px] font-bold transition-colors"
                                       title="Edit Program Details">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('program-committee.programs.destroy', $p) }}" 
                                          onsubmit="return confirm('Are you sure you want to delete this program?');" 
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 rounded transition" title="Delete Program">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                No programs found matching your filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $programs->links() }}
        </div>
    </div>

</div>
@endsection
