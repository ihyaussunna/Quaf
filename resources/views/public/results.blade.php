@extends('layouts.public', ['title' => 'Official Results | QUAF Season 09'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16">
    <!-- Header -->
    <div class="mb-6 sm:mb-10 text-center sm:text-left">
        <span class="text-xs font-mono font-bold tracking-widest text-[#f3bd2e] uppercase">OFFICIAL CONCLAVE VERDICTS</span>
        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-serif font-black text-slate-900 mt-1 sm:mt-2">Festival Results</h1>
        <p class="text-xs sm:text-sm text-slate-600 mt-2 sm:mt-3 max-w-2xl">
            Explore verified verdicts across all programs. Filter by category, house, or stage to discover champions and point tallies.
        </p>
    </div>

    <!-- Filters Bar (Light Theme) -->
    <form method="GET" action="{{ route('results.index') }}" class="bg-white border border-slate-200/90 rounded-2xl p-4 sm:p-6 mb-8 sm:mb-12 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
            <!-- Search -->
            <div>
                <label class="block text-[11px] font-mono uppercase text-slate-600 mb-1 font-bold">Search Program</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="e.g. Arabic Speech..."
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white">
            </div>

            <!-- Zone Filter -->
            <div>
                <label class="block text-[11px] font-mono uppercase text-slate-600 mb-1 font-bold">Zone</label>
                <select name="zone" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white">
                    <option value="">All Zones</option>
                    @foreach($zones as $zKey => $zVal)
                        @php
                            $zoneName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                            $zoneValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                        @endphp
                        <option value="{{ $zoneValue }}" {{ ($zone ?? '') == $zoneValue ? 'selected' : '' }}>{{ $zoneName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Group Filter -->
            <div>
                <label class="block text-[11px] font-mono uppercase text-slate-600 mb-1 font-bold">House / Group</label>
                <select name="group" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white">
                    <option value="">All Houses</option>
                    @foreach($groups as $grp)
                        <option value="{{ $grp->id }}" {{ $groupId == $grp->id ? 'selected' : '' }}>{{ $grp->name }} ({{ $grp->code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Stage Filter -->
            <div>
                <label class="block text-[11px] font-mono uppercase text-slate-600 mb-1 font-bold">Stage</label>
                <select name="stage" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white">
                    <option value="">All Stages</option>
                    @foreach($stages as $stg)
                        <option value="{{ $stg->id }}" {{ $stageId == $stg->id ? 'selected' : '' }}>{{ $stg->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-[#f3bd2e] text-white font-bold text-xs uppercase tracking-wider py-3 rounded-xl hover:brightness-105 transition-all shadow-xs">
                    Filter
                </button>
                <a href="{{ route('results.index') }}" class="px-4 py-3 bg-slate-100 text-slate-600 hover:text-slate-900 rounded-xl text-xs font-mono">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Results Grid (Light Theme) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @forelse($results as $result)
            <div class="rounded-2xl bg-white border border-slate-200 hover:border-[#f3bd2e]/40 p-5 sm:p-6 transition-all duration-300 flex flex-col justify-between group shadow-xs hover:shadow-md">
                <div>
                    <div class="flex items-center justify-between text-xs font-mono text-slate-500 mb-2">
                        <span class="text-[#f3bd2e] font-bold">{{ $result->program->code }}</span>
                        <span class="truncate max-w-[160px]">Stage: {{ $result->program->stage?->name ?? 'Main Arena' }}</span>
                    </div>

                    <h3 class="text-xl sm:text-2xl font-serif font-bold text-slate-900 group-hover:text-[#f3bd2e] transition-colors mb-1 leading-snug">
                        {{ $result->program->name }}
                    </h3>
                    <span class="text-xs text-slate-500 block mb-4 sm:mb-6 font-medium">{{ $result->program->eligibility ?? 'A Zone' }} • {{ ucfirst($result->program->type) }}</span>

                    <!-- Placements Cards -->
                    <div class="space-y-2.5 sm:space-y-3">
                        <!-- 1st -->
                        <div class="flex items-center justify-between p-2.5 sm:p-3 rounded-xl bg-amber-50/80 border border-amber-300/80 gap-2">
                            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-[#f3bd2e] text-white font-black text-xs flex items-center justify-center font-serif shadow-xs shrink-0">1</span>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-xs sm:text-sm text-slate-900 truncate">
                                        {{ $result->firstEntry?->student?->name ?? 'Team ' . $result->firstEntry?->group?->name }}
                                    </div>
                                    <span class="text-[10px] sm:text-[11px] text-slate-500 font-mono">Chest #{{ $result->firstEntry?->chest_number }}</span>
                                </div>
                            </div>
                            <span class="text-[11px] sm:text-xs font-mono font-bold px-2 py-0.5 rounded-lg bg-white text-[#f3bd2e] border border-amber-200 shrink-0">
                                {{ $result->firstEntry?->group?->name }}
                            </span>
                        </div>

                        <!-- 2nd -->
                        @if($result->secondEntry)
                            <div class="flex items-center justify-between p-2.5 sm:p-3 rounded-xl bg-slate-50 border border-slate-200 gap-2">
                                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                    <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-slate-400 text-white font-black text-xs flex items-center justify-center font-serif shrink-0">2</span>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-medium text-xs sm:text-sm text-slate-800 truncate">
                                            {{ $result->secondEntry?->student?->name ?? 'Team ' . $result->secondEntry?->group?->name }}
                                        </div>
                                        <span class="text-[10px] sm:text-[11px] text-slate-500 font-mono">Chest #{{ $result->secondEntry?->chest_number }}</span>
                                    </div>
                                </div>
                                <span class="text-[11px] sm:text-xs font-mono font-bold px-2 py-0.5 rounded-lg bg-white text-slate-700 border border-slate-200 shrink-0">
                                    {{ $result->secondEntry?->group?->name }}
                                </span>
                            </div>
                        @endif

                        <!-- 3rd -->
                        @if($result->thirdEntry)
                            <div class="flex items-center justify-between p-2.5 sm:p-3 rounded-xl bg-amber-50/40 border border-amber-200/60 gap-2">
                                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                    <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-amber-700 text-white font-black text-xs flex items-center justify-center font-serif shrink-0">3</span>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-medium text-xs sm:text-sm text-slate-800 truncate">
                                            {{ $result->thirdEntry?->student?->name ?? 'Team ' . $result->thirdEntry?->group?->name }}
                                        </div>
                                        <span class="text-[10px] sm:text-[11px] text-slate-500 font-mono">Chest #{{ $result->thirdEntry?->chest_number }}</span>
                                    </div>
                                </div>
                                <span class="text-[11px] sm:text-xs font-mono font-bold px-2 py-0.5 rounded-lg bg-white text-slate-700 border border-slate-200 shrink-0">
                                    {{ $result->thirdEntry?->group?->name }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-5 sm:mt-6 pt-3 sm:pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-mono">
                    <span class="text-slate-400 text-[11px] sm:text-xs">{{ $result->published_at?->format('h:i A, M d') }}</span>
                    <div class="flex items-center gap-3">
                        @if($result->poster_image)
                            <a href="{{ route('media.results.public-poster', $result) }}" target="_blank" class="text-slate-700 hover:text-[#be1e2d] font-bold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>Poster</span>
                            </a>
                        @endif
                        <a href="{{ route('results.show', $result->program->id) }}" class="text-[#f3bd2e] font-bold hover:underline">
                            Full Breakdown →
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-16 bg-white rounded-2xl border border-slate-200 shadow-xs">
                <p class="text-slate-500 font-mono text-sm">No results match the selected filters.</p>
                <a href="{{ route('results.index') }}" class="inline-block mt-4 text-xs font-bold text-[#f3bd2e] uppercase tracking-wider hover:underline">Clear all filters</a>
            </div>
        @endforelse
    </div>

    <div class="mt-8 sm:mt-12">
        {{ $results->links() }}
    </div>
</div>
@endsection
