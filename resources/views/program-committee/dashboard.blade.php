@extends('layouts.program-committee', ['title' => 'Program Committee Dashboard'])

@section('content')
<div class="space-y-8">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif font-black text-slate-900 tracking-tight">Program Samithi Portal</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">
                Program Committee Portal: Manage festival programs, categories, schedules, and official guidelines.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('program-committee.programs.create') }}" 
               class="px-4 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white text-xs font-mono font-bold flex items-center gap-2 shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add New Program</span>
            </a>
            <a href="{{ route('program-committee.niyamavali.index') }}" 
               class="px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-500 text-slate-950 text-xs font-mono font-bold flex items-center gap-2 shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Niyamavali Hub</span>
            </a>
        </div>
    </div>

    <!-- Summary Statistics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Programs -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-mono uppercase text-slate-400 font-bold block">Total Programs</span>
                <span class="text-3xl font-serif font-black text-slate-900 mt-1 block">{{ $totalPrograms }}</span>
                <span class="text-[11px] font-mono text-slate-500">All registered events</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center font-mono font-bold text-lg">
                #
            </div>
        </div>

        <!-- Card 2: Niyamavali Completed -->
        <a href="{{ route('program-committee.niyamavali.index', ['status' => 'with_rules']) }}" 
           class="p-6 rounded-3xl bg-white border-2 border-emerald-500/40 hover:border-emerald-500 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <span class="text-[11px] font-mono uppercase text-emerald-600 font-bold block">Niyamavali Added</span>
                <span class="text-3xl font-serif font-black text-emerald-700 mt-1 block">{{ $withRulesCount }}</span>
                <span class="text-[11px] font-mono text-emerald-600">Guidelines registered</span>
            </div>
            <span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-mono font-bold">
                Done ✓
            </span>
        </a>

        <!-- Card 3: Niyamavali Pending -->
        <a href="{{ route('program-committee.niyamavali.index', ['status' => 'missing_rules']) }}" 
           class="p-6 rounded-3xl bg-white border-2 border-amber-500/40 hover:border-amber-500 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <span class="text-[11px] font-mono uppercase text-amber-600 font-bold block">Pending Niyamavali</span>
                <span class="text-3xl font-serif font-black text-amber-700 mt-1 block">{{ $missingRulesCount }}</span>
                <span class="text-[11px] font-mono text-amber-600">Awaiting guidelines</span>
            </div>
            <span class="px-2.5 py-1 rounded-xl bg-amber-100 text-amber-800 text-xs font-mono font-bold">
                Action !
            </span>
        </a>

        <!-- Card 4: Type Breakdown -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm">
            <span class="text-[11px] font-mono uppercase text-slate-400 font-bold block mb-2">Zone & Format</span>
            <div class="space-y-1.5 text-xs font-mono">
                <div class="flex justify-between items-center text-slate-700">
                    <span>Stage Programs:</span>
                    <strong class="font-bold text-slate-900">{{ $stageProgramsCount }}</strong>
                </div>
                <div class="flex justify-between items-center text-slate-700">
                    <span>Non-Stage:</span>
                    <strong class="font-bold text-slate-900">{{ $nonStageProgramsCount }}</strong>
                </div>
                <div class="flex justify-between items-center text-slate-700 pt-1 border-t border-slate-100">
                    <span>Individual / Group:</span>
                    <strong class="font-bold text-slate-900">{{ $individualCount }} / {{ $groupCount }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Zones Overview -->
    <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 space-y-4 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-serif font-bold text-slate-900">Programs by Festival Zone</h3>
                <p class="text-[11px] font-mono text-slate-500">Number of competitions by zone</p>
            </div>
            <a href="{{ route('program-committee.programs.index') }}" class="text-xs font-mono font-bold text-brand-burgundy hover:underline">
                View All Programs →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($zones as $z)
                <a href="{{ route('program-committee.programs.index', ['zone_id' => $z->id]) }}" 
                   class="p-4 rounded-2xl bg-slate-50 hover:bg-amber-50/50 border border-slate-200 hover:border-amber-300 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-sm text-slate-900">{{ $z->name }}</span>
                        <span class="px-2 py-0.5 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-slate-700">
                            {{ $z->programs_count }}
                        </span>
                    </div>
                    @if($z->description)
                        <p class="text-[11px] font-malayalam text-slate-500 mt-1 line-clamp-1">{{ $z->description }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Quick Action: Programs Missing Niyamavali -->
        <div class="rounded-3xl bg-white border border-amber-200 p-6 sm:p-8 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-serif font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        Programs Pending Niyamavali
                    </h3>
                    <p class="text-[11px] font-mono text-slate-500">Competitions with pending rules</p>
                </div>
                <a href="{{ route('program-committee.niyamavali.index', ['status' => 'missing_rules']) }}" class="text-xs font-mono text-amber-700 font-bold hover:underline">
                    View All ({{ $missingRulesCount }}) →
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($pendingRulesPrograms as $prp)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="truncate">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">
                                    {{ $prp->code }}
                                </span>
                                <span class="font-bold text-sm text-slate-900 truncate">{{ $prp->name }}</span>
                            </div>
                            <div class="text-[11px] font-mono text-slate-400 mt-0.5">
                                {{ $prp->zone?->name ?? $prp->eligibility }} • {{ ucfirst($prp->type) }}
                            </div>
                        </div>

                        <a href="{{ route('program-committee.programs.rules', $prp) }}" 
                           class="px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-400 hover:text-slate-950 text-amber-800 text-xs font-mono font-bold flex-shrink-0 transition-colors">
                            + Add Rules
                        </a>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs font-mono text-emerald-600 font-bold">
                        All programs have Niyamavali rules configured!
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Programs -->
        <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-serif font-bold text-slate-900">Recently Updated Programs</h3>
                    <p class="text-[11px] font-mono text-slate-500">Recently updated competitions</p>
                </div>
                <a href="{{ route('program-committee.programs.index') }}" class="text-xs font-mono font-bold text-brand-burgundy hover:underline">
                    All Programs →
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentPrograms as $rp)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="truncate">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">
                                    {{ $rp->code }}
                                </span>
                                <a href="{{ route('program-committee.programs.show', $rp) }}" class="font-bold text-sm text-slate-900 hover:text-brand-burgundy truncate">
                                    {{ $rp->name }}
                                </a>
                                @if(!empty($rp->rules))
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-emerald-100 text-emerald-800">Rules ✓</span>
                                @endif
                            </div>
                            <div class="text-[11px] font-mono text-slate-400 mt-0.5">
                                {{ $rp->zone?->name ?? $rp->eligibility }} • Updated {{ $rp->updated_at?->diffForHumans() }}
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <a href="{{ route('program-committee.programs.rules', $rp) }}" 
                               class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-slate-100" title="Edit Niyamavali">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </a>
                            <a href="{{ route('program-committee.programs.edit', $rp) }}" 
                               class="p-1.5 rounded-lg text-slate-400 hover:text-brand-burgundy hover:bg-slate-100" title="Edit Program">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs font-mono text-slate-400">
                        No programs found.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
