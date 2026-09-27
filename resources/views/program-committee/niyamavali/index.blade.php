@extends('layouts.program-committee', ['title' => 'Niyamavali Hub - Competition Rules'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-sora font-black text-slate-900 tracking-tight">Rules Hub (Niyamavali)</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">
                Manage official competition rules, timing instructions, and scoring criteria.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('program-committee.niyamavali.print-book', ['zone_id' => $zoneId]) }}" target="_blank"
               class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-mono font-bold flex items-center gap-2 shadow-sm transition">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Rules Booklet</span>
            </a>
        </div>
    </div>

    <!-- Quick Status Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('program-committee.niyamavali.index') }}" 
           class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between hover:border-slate-400 transition">
            <div>
                <span class="text-[10px] font-mono uppercase text-slate-400 font-bold block">Total Programs</span>
                <span class="text-2xl font-sora font-black text-slate-900">{{ $totalCount }}</span>
            </div>
            <span class="px-2 py-1 rounded bg-slate-100 text-slate-600 font-mono font-bold text-xs">All</span>
        </a>

        <a href="{{ route('program-committee.niyamavali.index', ['status' => 'with_rules']) }}" 
           class="p-4 rounded-2xl bg-white border-2 border-emerald-500/40 shadow-sm flex items-center justify-between hover:border-emerald-500 transition">
            <div>
                <span class="text-[10px] font-mono uppercase text-emerald-600 font-bold block">Rules Configured</span>
                <span class="text-2xl font-sora font-black text-emerald-700">{{ $withRulesCount }}</span>
            </div>
            <span class="px-2 py-1 rounded bg-emerald-100 text-emerald-800 font-mono font-bold text-xs">Added ✓</span>
        </a>

        <a href="{{ route('program-committee.niyamavali.index', ['status' => 'missing_rules']) }}" 
           class="p-4 rounded-2xl bg-white border-2 border-amber-500/40 shadow-sm flex items-center justify-between hover:border-amber-500 transition">
            <div>
                <span class="text-[10px] font-mono uppercase text-amber-600 font-bold block">Pending Rules</span>
                <span class="text-2xl font-sora font-black text-amber-700">{{ $missingRulesCount }}</span>
            </div>
            <span class="px-2 py-1 rounded bg-amber-100 text-amber-800 font-mono font-bold text-xs">Action !</span>
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
        <form method="GET" action="{{ route('program-committee.niyamavali.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
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

            <!-- Status Filter -->
            <div>
                <select name="status" onchange="this.form.submit()" 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-xs text-slate-700 focus:outline-none focus:border-brand-burgundy">
                    <option value="">All Rules Status</option>
                    <option value="with_rules" {{ $status === 'with_rules' ? 'selected' : '' }}>Rules Added</option>
                    <option value="missing_rules" {{ $status === 'missing_rules' ? 'selected' : '' }}>Pending Rules</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Niyamavali List Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-28">Code</th>
                        <th class="py-3.5 px-4 font-sora">Program</th>
                        <th class="py-3.5 px-4">Zone</th>
                        <th class="py-3.5 px-4 font-sora">Niyamavali Preview</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($programs as $p)
                        @php $hasRules = !empty($p->rules); @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200">
                                    {{ $p->code }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-sora">
                                <a href="{{ route('program-committee.programs.show', $p) }}" class="font-bold text-slate-900 hover:text-brand-burgundy transition block">
                                    {{ $p->name }}
                                </a>
                                @if($p->malayalam_name)
                                    <span class="text-[11px] font-malayalam text-slate-500 block">{{ $p->malayalam_name }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                {{ $p->zone?->name ?? $p->eligibility }}
                            </td>
                            <td class="py-3.5 px-4 font-sora max-w-md">
                                @if($hasRules)
                                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                        {{ $p->rules }}
                                    </p>
                                    <span class="text-[10px] text-slate-400 font-mono mt-0.5 block">
                                        {{ $p->scoringCriteria->count() }} Criteria • {{ $p->duration_minutes }} Min
                                    </span>
                                @else
                                    <span class="text-xs text-amber-700 italic">Rules not added</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($hasRules)
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                        Added ✓
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">
                                        Pending !
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('program-committee.programs.rules', $p) }}" 
                                       class="px-3 py-1.5 rounded-xl font-mono text-[11px] font-bold transition {{ $hasRules ? 'bg-amber-50 hover:bg-amber-400 hover:text-slate-950 text-amber-900' : 'bg-brand-burgundy hover:bg-[#850d18] text-white shadow-xs' }}">
                                        {{ $hasRules ? 'Edit Rules' : '+ Add Rules' }}
                                    </a>
                                    @if($hasRules)
                                        <a href="{{ route('program-committee.programs.rules.print', $p) }}" target="_blank"
                                           class="p-1.5 text-slate-400 hover:text-slate-900 rounded-lg hover:bg-slate-100" title="Print Rules Sheet">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
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
