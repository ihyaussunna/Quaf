@extends('layouts.public', [
    'title' => 'Live Festival Results — QUAF 2026 | Markaz Art Fest',
    'metaDescription' => 'Live official competition results, winner announcements, and academic group rankings for QUAF 2026 Markaz Cultural Festival.',
])

@section('content')
<div x-data="{
        isModalOpen: false,
        activeResult: null,
        openResult(data) {
            this.activeResult = data;
            this.isModalOpen = true;
        },
        closeResult() {
            this.isModalOpen = false;
            this.activeResult = null;
        }
    }" 
    @keydown.escape.window="closeResult()"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12 lg:py-16">
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

    <!-- Results Grid: Compact Button Cards matching user design -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4.5">
        @forelse($results as $result)
            @php
                $resultNo = $result->result_number ?: ($allPublishedOrder[$result->id] ?? $loop->iteration);
                $podiumData = $result->getPodiumWinnersWithTies();

                $modalWinners = [];
                $posLabels = [1 => '1st Place', 2 => '2nd Place', 3 => '3rd Place'];

                foreach ([1, 2, 3] as $pos) {
                    $posItems = [];
                    foreach ($podiumData[$pos] as $w) {
                        $posItems[] = [
                            'name' => \App\Services\PointCalculationService::toTitleCase($w['name']),
                            'team' => \App\Services\PointCalculationService::toTitleCase($w['team']),
                            'team_color' => $w['team_color'],
                            'chest' => $w['chest'],
                            'grade' => $w['grade'],
                            'grade_points' => $w['grade_points'],
                            'position_points' => $w['position_points'],
                            'total_points' => $w['total_points'],
                            'is_tied' => !empty($w['is_tied']),
                        ];
                    }
                    $modalWinners[] = [
                        'pos' => $pos,
                        'label' => $posLabels[$pos],
                        'items' => $posItems,
                    ];
                }

                $resultPayload = [
                    'id' => $result->id,
                    'result_no' => sprintf('%02d', $resultNo),
                    'program_name' => \App\Services\PointCalculationService::toTitleCase($result->program->name),
                    'program_code' => $result->program->code ?? '',
                    'malayalam_name' => $result->program->malayalam_name ?? '',
                    'zone' => \App\Services\PointCalculationService::toTitleCase($result->program->eligibility ?? 'All Zones'),
                    'type' => ucfirst($result->program->type ?? 'Individual'),
                    'winners' => $modalWinners,
                    'poster_url' => route('media.results.download-poster', $result->id),
                    'poster_image' => $result->poster_image ? asset($result->poster_image) : null,
                ];
            @endphp

            <!-- Compact Button Card (Matching Image 1) -->
            <button type="button"
                    @click="openResult({{ Js::from($resultPayload) }})"
                    class="app-tap w-full text-left rounded-2xl sm:rounded-3xl bg-white border border-slate-200/90 hover:border-[#be1e2d] hover:shadow-md p-3.5 sm:p-4.5 transition-all duration-200 flex items-center justify-between gap-3 sm:gap-4 shadow-2xs group active:scale-[0.99] cursor-pointer">
                
                <div class="flex items-center gap-3.5 min-w-0">
                    <!-- Circular Gradient Badge with Result Number -->
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-gradient-to-tr from-[#be1e2d] via-[#f58220] to-[#f3bd2e] text-white flex items-center justify-center font-sora font-black text-lg sm:text-xl shadow-sm ring-2 ring-amber-200/70 shrink-0">
                        {{ sprintf('%02d', $resultNo) }}
                    </div>

                    <!-- Program Name & Zone • Type Subtitle Pill -->
                    <div class="min-w-0">
                        <h3 class="text-lg sm:text-xl font-sora font-black text-[#be1e2d] group-hover:text-[#a01624] transition-colors truncate leading-tight">
                            {{ \App\Services\PointCalculationService::toTitleCase($result->program->name) }}
                        </h3>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] sm:text-xs font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200/80">
                                <span>{{ \App\Services\PointCalculationService::toTitleCase($result->program->eligibility ?? 'Mix Zone') }}</span>
                                <span class="text-slate-300">•</span>
                                <span class="uppercase tracking-wider text-[10px] text-slate-600">{{ $result->program->type ?? 'INDIVIDUAL' }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right Action Icon Indicator -->
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-slate-50 group-hover:bg-red-50 border border-slate-200/80 group-hover:border-red-200 text-slate-400 group-hover:text-[#be1e2d] flex items-center justify-center shrink-0 transition-all">
                    <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </button>
        @empty
            <div class="col-span-full text-center py-12 sm:py-16 bg-white rounded-2xl border border-slate-200 shadow-xs px-4">
                <p class="text-slate-500 font-mono text-sm">No results match the selected filters.</p>
                <a href="{{ route('results.index') }}" class="inline-block mt-3 text-xs font-bold text-[#be1e2d] uppercase tracking-wider hover:underline">Clear all filters</a>
            </div>
        @endforelse
    </div>

    <!-- Alpine.js Interactive Modal Popup with Full Winners & Poster Download -->
    <div x-show="isModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop with Blur -->
        <div x-show="isModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeResult()" 
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

        <div class="flex min-h-full items-end sm:items-center justify-center p-3 sm:p-6 text-center">
            <!-- Modal Card Container -->
            <div x-show="isModalOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-2xl border border-slate-200/90 font-sora">
                
                <template x-if="activeResult">
                    <div>
                        <!-- Modal Header -->
                        <div class="p-5 sm:p-6 border-b border-slate-100 bg-gradient-to-b from-slate-50 to-white flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <!-- Circular Gradient Badge with Result Number -->
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-gradient-to-tr from-[#be1e2d] via-[#f58220] to-[#f3bd2e] text-white flex items-center justify-center font-sora font-black text-lg sm:text-xl shadow-md ring-2 ring-amber-200/70 shrink-0"
                                     x-text="activeResult.result_no">
                                </div>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-[#be1e2d] text-white"
                                              x-text="'Result #' + activeResult.result_no"></span>
                                        <template x-if="activeResult.program_code">
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-slate-900 text-white"
                                                  x-text="activeResult.program_code"></span>
                                        </template>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200"
                                              x-text="activeResult.zone + ' • ' + activeResult.type"></span>
                                    </div>
                                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 leading-tight truncate"
                                        x-text="activeResult.program_name"></h2>
                                    <template x-if="activeResult.malayalam_name">
                                        <p class="text-xs text-slate-500 font-ml mt-0.5" x-text="activeResult.malayalam_name"></p>
                                    </template>
                                </div>
                            </div>

                            <!-- Close Button (X) -->
                            <button type="button" 
                                    @click="closeResult()" 
                                    class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-900 flex items-center justify-center transition-colors shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Modal Body: Winners List (Supports Multiple/Tied Winners) -->
                        <div class="p-5 sm:p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                            <div class="text-xs font-mono font-bold tracking-widest text-slate-400 uppercase">OFFICIAL PODIUM VERDICTS</div>

                            <template x-for="tier in activeResult.winners" :key="tier.pos">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs font-bold font-sora">
                                        <span class="uppercase tracking-wider" 
                                              :class="tier.pos === 1 ? 'text-amber-800' : (tier.pos === 2 ? 'text-slate-700' : 'text-[#be1e2d]')"
                                              x-text="tier.label + (tier.items.length > 1 ? ' (' + tier.items.length + ' Shared Winners)' : '')">
                                        </span>
                                    </div>

                                    <template x-if="tier.items.length === 0">
                                        <div class="p-3 rounded-2xl border border-dashed border-slate-200 text-xs text-slate-400 italic text-center font-mono">
                                            No placement declared
                                        </div>
                                    </template>

                                    <template x-for="(item, idx) in tier.items" :key="idx">
                                        <div class="p-3.5 sm:p-4 rounded-2xl border transition-all flex items-center justify-between gap-3 shadow-2xs"
                                             :class="tier.pos === 1 ? 'bg-amber-50/70 border-amber-300' : (tier.pos === 2 ? 'bg-slate-50 border-slate-200' : 'bg-red-50/50 border-red-200')">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <span class="w-8 h-8 rounded-xl text-white font-black text-sm flex items-center justify-center font-sora shadow-xs shrink-0"
                                                      :class="tier.pos === 1 ? 'bg-[#f3bd2e]' : (tier.pos === 2 ? 'bg-slate-400' : 'bg-[#be1e2d]')"
                                                      x-text="tier.pos">
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="font-black text-sm sm:text-base text-slate-900 font-sora truncate" x-text="item.name"></div>
                                                    <div class="text-[11px] sm:text-xs text-slate-600 mt-0.5 flex flex-wrap items-center gap-1.5 font-mono">
                                                        <span class="font-bold text-slate-800" x-text="item.team"></span>
                                                        <span class="text-slate-300">•</span>
                                                        <span>Chest: <strong class="text-slate-900" x-text="'#' + item.chest"></strong></span>
                                                        <template x-if="item.is_tied">
                                                            <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-900 text-[10px] font-bold">Shared / Tied</span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="text-right shrink-0 flex flex-col items-end gap-1">
                                                <template x-if="item.grade">
                                                    <span class="px-2.5 py-1 rounded-lg font-mono font-bold text-xs bg-emerald-600 text-white shadow-2xs"
                                                          x-text="'Grade ' + item.grade"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Official Poster Preview (if available) -->
                            <template x-if="activeResult.poster_image">
                                <div class="mt-4 pt-4 border-t border-slate-100">
                                    <div class="text-xs font-mono font-bold tracking-widest text-slate-400 uppercase mb-2">OFFICIAL FESTIVAL POSTER</div>
                                    <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-900 aspect-video relative group">
                                        <img :src="activeResult.poster_image" alt="Poster Preview" class="w-full h-full object-cover">
                                        <a :href="activeResult.poster_image" target="_blank" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>View Poster Fullscreen</span>
                                        </a>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Modal Footer: Prominent Download Poster Option -->
                        <div class="p-5 sm:p-6 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="text-xs text-slate-500 font-mono text-center sm:text-left">
                                Official QUAF 2026 Verdict • Verified & Published
                            </div>
                            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                                <button type="button" @click="closeResult()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold font-sora transition">
                                    Close
                                </button>
                                <a :href="activeResult.poster_url" 
                                   download
                                   class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-sora font-bold text-xs uppercase tracking-wider shadow-sm hover:shadow transition-all active:scale-95">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    <span>Download Official Poster</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div class="mt-8 sm:mt-12">
        {{ $results->links() }}
    </div>
</div>
@endsection
