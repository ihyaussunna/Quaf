@extends('layouts.judge', ['title' => 'Evaluation: ' . $program->name])

@section('content')
<div class="space-y-6" x-data="{ rulesModalOpen: false }">
    <!-- Top Bar (Light Theme) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-slate-200 p-4 rounded-2xl shadow-xs">
        <div class="flex items-center gap-3">
            <a href="{{ route('judge.dashboard') }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-mono font-semibold">
                &larr; Back to Schedule
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base font-sora font-bold text-slate-900">{{ $program->name }}</h1>
                    @if($program->malayalam_name)
                        <span class="text-xs font-ml text-slate-500">({{ $program->malayalam_name }})</span>
                    @endif
                </div>
                <p class="text-[11px] font-mono text-slate-500">
                    Code: {{ $program->code }} • Category: {{ $program->category->name ?? 'General' }} • Stage: {{ $program->stage->name ?? 'TBA' }} • Duration: {{ $program->duration_minutes }} Mins
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Rules Button -->
            <button type="button" @click="rulesModalOpen = true"
                    class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-[#f3bd2e] border border-amber-200 text-xs font-mono font-bold flex items-center gap-1.5 transition-all shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>View Rules & Guidelines</span>
            </button>

            <span class="px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 text-xs font-mono font-bold">
                {{ $entries->count() }} Candidates
            </span>
        </div>
    </div>

    <!-- Scoring Rules & Criteria Overview Banner (Light Theme) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 flex flex-wrap items-center gap-3 text-xs font-mono shadow-2xs">
        <span class="text-slate-500 font-bold uppercase tracking-wider">Evaluation Rubric:</span>
        @if(!$program->has_criteria || $program->scoringCriteria->isEmpty())
            <span class="px-3 py-1 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 font-bold">Total Mark Only (100-Point Scale)</span>
        @else
            @foreach($program->scoringCriteria as $criterion)
                <div class="px-3 py-1 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-2">
                    <span class="text-slate-800 font-medium">{{ $criterion->criterion_name }}</span>
                    <span class="text-[#f3bd2e] font-bold">({{ $criterion->max_marks }} pts)</span>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Network Connectivity Status Indicator -->
    <div x-data="{ online: navigator.onLine }"
         x-init="window.addEventListener('online', () => online = true); window.addEventListener('offline', () => online = false)"
         x-show="!online" 
         class="p-3.5 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs font-mono flex items-center justify-between shadow-xs"
         style="display: none;">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
            <span class="font-bold">Offline Mode: Internet connection is unavailable. Your scores are saved locally in this browser and will sync when reconnected.</span>
        </div>
        <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-amber-200 text-amber-800">Local Draft Active</span>
    </div>

    <!-- Anonymous Participants Evaluation Cards (Strict Confidentiality: Code Letters Only) -->
    <div class="space-y-6">
        @forelse($entries as $index => $entry)
            @php
                $sheet = $scoreSheets->get($entry->id);
                $isSubmitted = $sheet && $sheet->is_submitted;
                $savedScores = $sheet ? ($sheet->criteria_scores ?? []) : [];
                $displayCode = $entry->code_letter ? 'Code ' . $entry->code_letter : 'Participant ' . ($index + 1);
            @endphp

            <div class="bg-white border {{ $isSubmitted ? 'border-emerald-300' : 'border-slate-200' }} rounded-3xl p-6 transition-all relative overflow-hidden shadow-sm hover:shadow-md"
                 x-data="{
                     scores: {
                         @foreach($program->scoringCriteria as $criterion)
                             '{{ $criterion->id }}': {{ $savedScores[$criterion->criterion_name] ?? 0 }},
                         @endforeach
                     },
                     maxMarks: {
                         @foreach($program->scoringCriteria as $criterion)
                             '{{ $criterion->id }}': {{ $criterion->max_marks }},
                         @endforeach
                     },
                     hasCriteria: {{ ($program->has_criteria && $program->scoringCriteria->isNotEmpty()) ? 'true' : 'false' }},
                     directScore: {{ $sheet ? ($sheet->total_score ?? 0) : 0 }},
                     selectedGrade: '{{ $savedScores['grade'] ?? '' }}',
                     remarks: '{{ addslashes($sheet->remarks ?? '') }}',
                     isSubmitted: {{ $isSubmitted ? 'true' : 'false' }},
                     submittedScore: {{ $sheet ? ($sheet->total_score ?? 0) : 0 }},
                     isSaving: false,
                     saveSuccess: false,
                     errorMessage: '',
                     storageKey: 'quaf_draft_{{ $program->id }}_{{ $entry->id }}',
                     init() {
                         try {
                             const draft = localStorage.getItem(this.storageKey);
                             if (draft && !this.isSubmitted) {
                                 const parsed = JSON.parse(draft);
                                 if (parsed.scores) this.scores = Object.assign(this.scores, parsed.scores);
                                 if (parsed.directScore) this.directScore = parsed.directScore;
                                 if (parsed.selectedGrade) this.selectedGrade = parsed.selectedGrade;
                                 if (parsed.remarks) this.remarks = parsed.remarks;
                             }
                         } catch (e) {}
                     },
                     saveDraftLocal() {
                         try {
                             localStorage.setItem(this.storageKey, JSON.stringify({
                                 scores: this.scores,
                                 directScore: this.directScore,
                                 selectedGrade: this.selectedGrade,
                                 remarks: this.remarks
                             }));
                         } catch (e) {}
                     },
                     clearDraftLocal() {
                         try {
                             localStorage.removeItem(this.storageKey);
                         } catch (e) {}
                     },
                     get total() {
                         if (this.hasCriteria) {
                             let sum = 0;
                             for (let key in this.scores) {
                                 sum += parseFloat(this.scores[key]) || 0;
                             }
                             return Math.round(sum * 10) / 10;
                         }
                         return Math.round((parseFloat(this.directScore) || 0) * 10) / 10;
                     },
                     get computedGrade() {
                         if (this.selectedGrade) return this.selectedGrade;
                         const t = this.total;
                         if (t >= 80) return 'A+';
                         if (t >= 70) return 'A';
                         if (t >= 60) return 'B+';
                         if (t >= 50) return 'B';
                         if (t >= 40) return 'C';
                         return '-';
                     },
                     setGrade(g) {
                         this.selectedGrade = g;
                         if (!this.hasCriteria && (!this.directScore || this.directScore == 0)) {
                             if (g === 'A+') this.directScore = 85;
                             else if (g === 'A') this.directScore = 75;
                             else if (g === 'B+') this.directScore = 65;
                             else if (g === 'B') this.directScore = 55;
                             else if (g === 'C') this.directScore = 45;
                         }
                         this.saveDraftLocal();
                     },
                     submitEvaluation(e) {
                         if (this.isSaving) return;
                         this.isSaving = true;
                         this.saveSuccess = false;
                         this.errorMessage = '';

                         const form = e.target;
                         const formData = new FormData(form);

                         fetch(form.action, {
                             method: 'POST',
                             body: formData,
                             headers: {
                                 'X-Requested-With': 'XMLHttpRequest',
                                 'Accept': 'application/json'
                             }
                         })
                         .then(async res => {
                             if (!res.ok) {
                                 const err = await res.json().catch(() => ({}));
                                 throw new Error(err.message || 'Server returned an error.');
                             }
                             return res.json();
                         })
                         .then(data => {
                             this.isSubmitted = true;
                             this.submittedScore = data.total_score || this.total;
                             this.saveSuccess = true;
                             this.clearDraftLocal();
                             setTimeout(() => { this.saveSuccess = false; }, 4000);
                         })
                         .catch(err => {
                             this.saveDraftLocal();
                             this.errorMessage = 'Connection error: Scores saved locally in browser. Please try again.';
                         })
                         .finally(() => {
                             this.isSaving = false;
                         });
                     }
                 }">

                <form method="POST" action="{{ route('judge.evaluate.save', [$program, $entry]) }}" @submit.prevent="submitEvaluation($event)" class="space-y-6">
                    @csrf

                    <!-- Anonymous Header (No Name, No Group, No Photo) -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-amber-50 border-2 border-amber-300 flex items-center justify-center font-mono font-black text-xl text-[#f3bd2e] shadow-xs">
                                {{ $entry->code_letter ?? chr(65 + $index) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-sora font-black text-slate-900 tracking-wide">{{ $displayCode }}</h3>
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-mono font-semibold">
                                        Anonymous Entry #{{ $index + 1 }}
                                    </span>
                                </div>
                                <p class="text-[11px] font-mono text-slate-400 mt-0.5">
                                    Confidential evaluation • Adjudicate by code letter only
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <template x-if="isSubmitted">
                                <span class="px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-mono text-xs font-bold flex items-center gap-1.5 shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>Submitted (Score: <span x-text="submittedScore"></span>)</span>
                                </span>
                            </template>
                            <template x-if="!isSubmitted">
                                <span class="px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700 font-mono text-xs font-bold">
                                    Pending Evaluation
                                </span>
                            </template>

                            <div class="px-4 py-1.5 rounded-2xl bg-slate-50 border border-slate-200 font-mono text-center min-w-[80px]">
                                <span class="text-[9px] text-slate-400 block uppercase font-semibold">Total Score</span>
                                <span class="text-xl font-bold text-[#f3bd2e]" x-text="total">0</span>
                            </div>

                            <div class="px-3.5 py-1.5 rounded-2xl bg-slate-50 border border-slate-200 font-mono text-center min-w-[65px]">
                                <span class="text-[9px] text-slate-400 block uppercase font-semibold">Grade</span>
                                <span class="text-sm font-black px-2 py-0.5 rounded-lg inline-block transition-colors"
                                      :class="{
                                          'bg-emerald-100 text-emerald-800': computedGrade === 'A+' || computedGrade === 'A',
                                          'bg-blue-100 text-blue-800': computedGrade === 'B+' || computedGrade === 'B',
                                          'bg-amber-100 text-amber-800': computedGrade === 'C',
                                          'bg-slate-100 text-slate-400': computedGrade === '-'
                                      }"
                                      x-text="computedGrade">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Criteria Score Inputs -->
                    @if($program->has_criteria && $program->scoringCriteria->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach($program->scoringCriteria as $criterion)
                                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-2">
                                    <div class="flex items-center justify-between text-xs font-mono">
                                        <span class="text-slate-700 font-semibold truncate">{{ $criterion->criterion_name }}</span>
                                        <span class="text-[#f3bd2e] font-bold">/ {{ $criterion->max_marks }}</span>
                                    </div>
                                    <div>
                                        <input type="number" step="0.5" min="0" max="{{ $criterion->max_marks }}"
                                               name="scores[{{ $criterion->id }}]"
                                               x-model="scores['{{ $criterion->id }}']"
                                               @input="if (scores['{{ $criterion->id }}'] > maxMarks['{{ $criterion->id }}']) scores['{{ $criterion->id }}'] = maxMarks['{{ $criterion->id }}']; saveDraftLocal();"
                                               required
                                               class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-sm font-mono text-slate-900 text-center font-bold focus:outline-none focus:border-[#f3bd2e]">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 max-w-sm space-y-2">
                            <div class="flex items-center justify-between text-xs font-mono">
                                <span class="text-slate-700 font-semibold">Total Score</span>
                                <span class="text-[#f3bd2e] font-bold">/ 100 max</span>
                            </div>
                            <input type="number" step="0.5" min="0" max="100"
                                   name="total_score"
                                   x-model="directScore"
                                   @input="saveDraftLocal()"
                                   required
                                   class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-sm font-mono text-slate-900 text-center font-bold focus:outline-none focus:border-[#f3bd2e]">
                        </div>
                    @endif

                    <!-- Grade Selector Buttons (A+, A, B+, B, C) -->
                    <div class="bg-slate-50/75 border border-slate-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="text-xs font-mono">
                            <span class="font-bold text-slate-700 uppercase tracking-wider">Grade Option:</span>
                            <span class="text-slate-400 ml-1.5">(Select or confirm grade for this candidate)</span>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <input type="hidden" name="grade" :value="selectedGrade || (computedGrade !== '-' ? computedGrade : '')">
                            <template x-for="g in ['A+', 'A', 'B+', 'B', 'C']" :key="g">
                                <button type="button" 
                                        @click="setGrade(g)"
                                        :class="(selectedGrade === g || (!selectedGrade && computedGrade === g))
                                            ? (g === 'A+' || g === 'A' ? 'bg-emerald-600 text-white shadow-xs border-emerald-600' : (g === 'B+' || g === 'B' ? 'bg-blue-600 text-white shadow-xs border-blue-600' : 'bg-amber-600 text-white shadow-xs border-amber-600'))
                                            : 'bg-white hover:bg-slate-100 text-slate-700 border-slate-200'"
                                        class="px-3.5 py-1.5 rounded-xl border text-xs font-mono font-bold transition-all shadow-2xs">
                                    <span x-text="g"></span>
                                </button>
                            </template>
                            <button type="button" x-show="selectedGrade" @click="selectedGrade = ''; saveDraftLocal();" 
                                    class="px-2 py-1 text-[11px] font-mono text-slate-400 hover:text-slate-600 underline">
                                Reset
                            </button>
                        </div>
                    </div>

                    <!-- Remarks & Submit Action -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                        <div class="flex-1">
                            <input type="text" name="remarks" x-model="remarks" @input="saveDraftLocal()" placeholder="Confidential remarks for this performance..."
                                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white font-sora">
                            <div x-show="errorMessage" class="text-xs font-mono font-bold text-red-600 mt-1" x-text="errorMessage"></div>
                            <div x-show="saveSuccess" class="text-xs font-mono font-bold text-emerald-600 mt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Scores saved successfully (Total: <span x-text="submittedScore"></span>)</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="submit" 
                                    :disabled="isSaving"
                                    :class="isSaving ? 'opacity-60 cursor-not-allowed' : 'hover:brightness-105 active:scale-95'"
                                    class="px-6 py-2.5 bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase rounded-xl shadow-md shadow-[#f3bd2e]/20 flex items-center gap-2 transition-all">
                                <template x-if="isSaving">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                </template>
                                <template x-if="!isSaving">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </template>
                                <span x-text="isSaving ? 'Saving...' : (isSubmitted ? 'Update Evaluation' : 'Lock & Submit Score')"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        @empty
            <div class="py-16 text-center text-slate-500 font-mono text-xs bg-white rounded-3xl border border-slate-200 shadow-sm space-y-2">
                <svg class="w-8 h-8 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="font-bold text-slate-800">No Candidates Marked PRESENT Yet</div>
                <p class="text-slate-400 max-w-md mx-auto">Only candidates marked "PRESENT" by the Green Room desk become eligible and appear here for jury evaluation. Absent candidates are strictly excluded.</p>
            </div>
        @endforelse
    </div>

    <!-- Rules & Niyamavali Modal (Light Theme) -->
    <div x-show="rulesModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">

        <div class="bg-white border border-slate-200 rounded-3xl max-w-2xl w-full p-6 sm:p-8 space-y-6 shadow-2xl relative"
             @click.away="rulesModalOpen = false">

            <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                <div>
                    <span class="text-[10px] font-mono uppercase tracking-widest text-[#f3bd2e] font-bold">Program Guidelines</span>
                    <h2 class="text-xl font-sora font-black text-slate-900 mt-1">{{ $program->name }}</h2>
                    @if($program->malayalam_name)
                        <p class="text-xs font-ml text-slate-500 mt-0.5">{{ $program->malayalam_name }}</p>
                    @endif
                </div>
                <button type="button" @click="rulesModalOpen = false" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Program Meta Badges -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs font-mono">
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <span class="text-[10px] text-slate-400 block uppercase">Category</span>
                    <span class="text-slate-800 font-bold">{{ $program->category->name ?? 'General' }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <span class="text-[10px] text-slate-400 block uppercase">Type</span>
                    <span class="text-slate-800 font-bold capitalize">{{ $program->type }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <span class="text-[10px] text-slate-400 block uppercase">Duration</span>
                    <span class="text-[#f3bd2e] font-bold">{{ $program->duration_minutes }} Mins</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <span class="text-[10px] text-slate-400 block uppercase">Eligibility</span>
                    <span class="text-slate-800 font-bold">{{ $program->eligibility ?? 'All' }}</span>
                </div>
            </div>

            <!-- Rules Content -->
            <div class="space-y-3">
                <h3 class="text-sm font-mono font-bold text-slate-800 uppercase tracking-wider">Rules & Guidelines</h3>
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-sora text-slate-700 leading-relaxed max-h-60 overflow-y-auto">
                    @if($program->rules)
                        {!! nl2br(e($program->rules)) !!}
                    @else
                        <p class="italic text-slate-400 font-mono">No specific rules provided. General fest regulations apply.</p>
                    @endif
                </div>
            </div>

            <!-- Scoring Criteria List -->
            <div class="space-y-3">
                <h3 class="text-sm font-mono font-bold text-slate-800 uppercase tracking-wider">Scoring Rubric</h3>
                <div class="space-y-2">
                    @foreach($program->scoringCriteria as $criterion)
                        <div class="flex items-center justify-between p-3 bg-amber-50/50 border border-amber-200 rounded-xl text-xs font-mono">
                            <span class="text-slate-800 font-semibold">{{ $criterion->criterion_name }}</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-[#be1e2d] font-bold">Max: {{ $criterion->max_marks }} Marks</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-2 text-right">
                <button type="button" @click="rulesModalOpen = false"
                        class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-mono font-bold hover:bg-slate-800 transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
