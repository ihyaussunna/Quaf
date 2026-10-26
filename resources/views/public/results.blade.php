@extends('layouts.public', [
    'title' => 'Live Festival Results & Point Table — QUAF 2026 | Markaz Art Fest',
    'metaDescription' => 'Live official competition results, points table, winner announcements, and academic group standings for QUAF 2026 Markaz Cultural Festival.',
])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12 lg:py-16">
    <!-- Header -->
    <div class="mb-5 sm:mb-8 text-center sm:text-left">
        <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase animate-subheading">OFFICIAL CONCLAVE VERDICTS</span>
        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1 sm:mt-2 animate-heading">Festival Results</h1>
    </div>

    <!-- Quick Zone Chips (App-Style One-Tap Mobile Scroller) -->
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-3 mb-4 -mx-4 px-4 sm:mx-0 sm:px-0 select-none">
        <a href="{{ route('results.index', array_merge(request()->except('zone', 'page'), ['zone' => ''])) }}" 
           class="app-tap px-3.5 py-1.5 rounded-full text-xs font-mono font-bold whitespace-nowrap transition-all shadow-2xs {{ empty($zone) ? 'bg-[#be1e2d] text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}">
            All Zones
        </a>
        @foreach($zones as $zKey => $zVal)
            @php
                $zName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                $zValStr = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
            @endphp
            <a href="{{ route('results.index', array_merge(request()->except('zone', 'page'), ['zone' => $zValStr])) }}" 
               class="app-tap px-3.5 py-1.5 rounded-full text-xs font-mono font-bold whitespace-nowrap transition-all shadow-2xs {{ ($zone ?? '') == $zValStr ? 'bg-[#be1e2d] text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}">
                {{ $zName }}
            </a>
        @endforeach
    </div>

    <!-- Filters Bar (Light Theme) -->
    <form method="GET" action="{{ route('results.index') }}" 
          x-data="{
              selectedZone: '{{ $zone ?? '' }}',
              selectedProgram: '{{ $programId ?? '' }}',
              allPrograms: {{ Js::from($programs->map(fn($p) => ['id' => (string)$p->id, 'code' => $p->code, 'name' => $p->name, 'eligibility' => $p->eligibility])) }},
              get filteredPrograms() {
                  if (!this.selectedZone) return this.allPrograms;
                  return this.allPrograms.filter(p => p.eligibility === this.selectedZone);
              },
              onZoneChange() {
                  if (this.selectedProgram) {
                      const exists = this.filteredPrograms.some(p => p.id === String(this.selectedProgram));
                      if (!exists) {
                          this.selectedProgram = '';
                      }
                  }
              }
          }"
          class="bg-white border border-slate-200/90 rounded-2xl p-4 sm:p-6 mb-6 sm:mb-10 shadow-xs font-sora">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-mono uppercase text-slate-600 mb-1 font-bold">Search Program</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Program name, code (e.g. Q9-101)..."
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white font-sora">
            </div>

            <!-- Zone Filter -->
            <div>
                <label class="block text-[11px] font-mono uppercase text-slate-600 mb-1 font-bold">Zone</label>
                <select name="zone" 
                        x-model="selectedZone"
                        @change="onZoneChange()"
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white font-sora">
                    <option value="">All Zones</option>
                    @foreach($zones as $zKey => $zVal)
                        @php
                            $zoneName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                            $zoneValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                        @endphp
                        <option value="{{ $zoneValue }}">{{ $zoneName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Program Filter (Dynamically filtered by selected Zone) -->
            <div>
                <label class="block text-[11px] font-mono uppercase text-slate-600 mb-1 font-bold">Program</label>
                <select name="program" 
                        x-model="selectedProgram"
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white font-sora">
                    <option value="">All Programs</option>
                    <template x-for="prog in filteredPrograms" :key="prog.id">
                        <option :value="prog.id" 
                                :selected="prog.id === String(selectedProgram)" 
                                x-text="prog.code + ' - ' + prog.name"></option>
                    </template>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2 pt-1 sm:pt-0">
                <button type="submit" class="app-tap flex-1 bg-[#be1e2d] text-white font-bold text-xs uppercase tracking-wider py-3 rounded-xl hover:bg-[#a01624] transition-all shadow-xs font-sora">
                    Filter
                </button>
                <a href="{{ route('results.index') }}" class="app-tap px-3 py-3 bg-slate-100 text-slate-600 hover:text-slate-900 rounded-xl text-xs font-mono">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Results Grid (Light Theme) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @forelse($results as $result)
            @php
                $resultNo = $allPublishedOrder[$result->id] ?? $loop->iteration;
            @endphp
            <div class="rounded-2xl bg-white border border-slate-200 hover:border-[#be1e2d]/40 p-5 sm:p-6 transition-all duration-300 flex flex-col justify-between group shadow-xs hover:shadow-md">
                <div>
                    <!-- Header: Round Red-Yellow Gradient Badge (01, 02...) & Zone / Type -->
                    <div class="flex items-center justify-between gap-3 mb-3.5">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gradient-to-tr from-[#be1e2d] via-[#f58220] to-[#f3bd2e] text-white flex items-center justify-center font-sora font-black text-sm sm:text-base shadow-md ring-2 ring-amber-200/60 shrink-0">
                            {{ sprintf('%02d', $resultNo) }}
                        </div>

                        <div class="flex items-center gap-1.5 text-xs font-mono font-bold text-slate-600 bg-slate-100/90 px-3 py-1.5 rounded-xl border border-slate-200/80">
                            <span class="text-slate-800">{{ $result->program->eligibility ?? 'All Zones' }}</span>
                            <span class="text-slate-300">•</span>
                            <span class="text-[#be1e2d] uppercase tracking-wider text-[11px]">{{ ucfirst($result->program->type ?? 'Individual') }}</span>
                        </div>
                    </div>

                    <!-- Program Name -->
                    <h3 class="text-xl sm:text-2xl font-sora font-black text-slate-900 group-hover:text-[#be1e2d] transition-colors mb-4 leading-tight break-words">
                        {{ $result->program->name }}
                    </h3>

                    <!-- Placements Cards (Matching Winner & Team layout) -->
                    <div class="space-y-2 sm:space-y-2.5">
                        <!-- 1st Place -->
                        @if($result->firstEntry)
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-amber-50/70 border border-amber-300/80">
                                <span class="w-7 h-7 rounded-lg bg-[#f3bd2e] text-white font-black text-xs flex items-center justify-center font-sora shadow-xs shrink-0">1</span>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-sm text-slate-900 font-sora truncate">
                                        {{ $result->firstEntry->student?->name ?? 'Team ' . $result->firstEntry->group?->name }}
                                    </div>
                                    <div class="text-[11px] font-mono font-bold tracking-wider uppercase text-slate-600 truncate mt-0.5">
                                        {{ $result->firstEntry->group?->name }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- 2nd Place -->
                        @if($result->secondEntry)
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="w-7 h-7 rounded-lg bg-slate-400 text-white font-black text-xs flex items-center justify-center font-sora shrink-0">2</span>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-sm text-slate-900 font-sora truncate">
                                        {{ $result->secondEntry->student?->name ?? 'Team ' . $result->secondEntry->group?->name }}
                                    </div>
                                    <div class="text-[11px] font-mono font-bold tracking-wider uppercase text-slate-600 truncate mt-0.5">
                                        {{ $result->secondEntry->group?->name }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- 3rd Place -->
                        @if($result->thirdEntry)
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-amber-50/40 border border-amber-200/60">
                                <span class="w-7 h-7 rounded-lg bg-amber-700 text-white font-black text-xs flex items-center justify-center font-sora shrink-0">3</span>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-sm text-slate-900 font-sora truncate">
                                        {{ $result->thirdEntry->student?->name ?? 'Team ' . $result->thirdEntry->group?->name }}
                                    </div>
                                    <div class="text-[11px] font-mono font-bold tracking-wider uppercase text-slate-600 truncate mt-0.5">
                                        {{ $result->thirdEntry->group?->name }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer: Download Icon with Poster Button -->
                <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center justify-end">
                    <a href="{{ route('media.results.download-poster', $result->id) }}" 
                       download
                       class="app-tap inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-red-50 hover:bg-[#be1e2d] text-[#be1e2d] hover:text-white border border-red-200/80 hover:border-[#be1e2d] font-sora font-bold text-xs shadow-2xs hover:shadow-xs active:scale-95 transition-all duration-200 group/btn"
                       title="Download Poster">
                        <svg class="w-4 h-4 text-[#be1e2d] group-hover/btn:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>Poster</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 sm:py-16 bg-white rounded-2xl border border-slate-200 shadow-xs px-4">
                <p class="text-slate-500 font-mono text-sm">No results match the selected filters.</p>
                <a href="{{ route('results.index') }}" class="inline-block mt-3 text-xs font-bold text-[#be1e2d] uppercase tracking-wider hover:underline">Clear all filters</a>
            </div>
        @endforelse
    </div>

    <div class="mt-8 sm:mt-12">
        {{ $results->links() }}
    </div>
</div>
@endsection
