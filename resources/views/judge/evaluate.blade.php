@extends('layouts.judge', ['title' => 'Evaluation: ' . $program->name])
@section('content')
<div class="space-y-6" x-data="{
    rulesModalOpen: false,
    textReaderOpen: false,
    textReaderCode: '',
    textReaderContent: '',
    lightboxOpen: false,
    lightboxSrc: '',
    lightboxCode: '',
    evaluationMode: localStorage.getItem('quaf_judge_mode_{{ $program->id }}') || '{{ $program->has_criteria && $program->scoringCriteria->isNotEmpty() ? 'criteria' : 'simple' }}',
    openTextReader(code, text) {
        this.textReaderCode = code;
        this.textReaderContent = text;
        this.textReaderOpen = true;
    },
    openLightbox(code, src) {
        this.lightboxCode = code;
        this.lightboxSrc = src;
        this.lightboxOpen = true;
    }
}">
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
        <div class="flex items-center gap-2 flex-wrap">
            @if(!empty($hasOnlineSubmissions))
                <a href="{{ route('judge.evaluate.submissions-pdf', $program) }}" 
                   target="_blank" 
                   class="px-3.5 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white border border-[#be1e2d] text-xs font-mono font-bold flex items-center gap-1.5 transition-all shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print / Download Answers (PDF)</span>
                </a>
            @endif

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

    <!-- 2 Evaluation Modes Switcher (Simple 100-Mark Scale vs Criteria Breakdown) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-2xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-800">Scoring Mode / മാർക്ക് നൽകുന്ന രീതി:</span>
                <span class="text-[11px] font-mono text-slate-400 font-semibold">(ഇഷ്ടമുള്ള രീതി തിരഞ്ഞെടുക്കാം)</span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                വിധികർത്താവിന് നേരിട്ട് 100-ൽ മാർക്ക് നൽകുകയോ (Simple Mode), അല്ലെങ്കിൽ മാനദണ്ഡങ്ങൾ തിരിച്ച് നൽകുകയോ (Criteria Mode) ചെയ്യാം.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row p-1.5 bg-slate-100 rounded-2xl border border-slate-200 gap-1.5 shrink-0">
            <button type="button"
                    @click="evaluationMode = 'simple'; localStorage.setItem('quaf_judge_mode_{{ $program->id }}', 'simple')"
                    :class="evaluationMode === 'simple' ? 'bg-[#005c94] text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-mono transition-all flex items-center justify-center gap-2 cursor-pointer min-h-[44px]">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>1. Simple Mode (100-ൽ മാർക്ക്)</span>
            </button>

            <button type="button"
                    @click="evaluationMode = 'criteria'; localStorage.setItem('quaf_judge_mode_{{ $program->id }}', 'criteria')"
                    :class="evaluationMode === 'criteria' ? 'bg-[#be1e2d] text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-mono transition-all flex items-center justify-center gap-2 cursor-pointer min-h-[44px]">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>2. Criteria Mode (മാനദണ്ഡങ്ങൾ)</span>
            </button>
        </div>
    </div>

    <!-- Scoring Rules & Criteria Overview Banner (Light Theme) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 flex flex-wrap items-center gap-3 text-xs font-mono shadow-2xs"
         x-show="evaluationMode === 'criteria'">
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
                         if (evaluationMode === 'criteria' && this.hasCriteria) {
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
                         if (t >= 90) return 'A+';
                         if (t >= 70) return 'A';
                         if (t >= 60) return 'B';
                         if (t >= 50) return 'C';
                         return '-';
                     },
                     setGrade(g) {
                         this.selectedGrade = g;
                         if (evaluationMode === 'simple' && (!this.directScore || this.directScore == 0)) {
                             if (g === 'A+') this.directScore = 95;
                             else if (g === 'A') this.directScore = 80;
                             else if (g === 'B') this.directScore = 65;
                             else if (g === 'C') this.directScore = 55;
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
                                          'bg-blue-100 text-blue-800': computedGrade === 'B',
                                          'bg-amber-100 text-amber-800': computedGrade === 'C',
                                          'bg-slate-100 text-slate-400': computedGrade === '-'
                                      }"
                                      x-text="computedGrade">-</span>
                            </div>
                        </div>
                    </div>

                    @php
                        $entrySubmission = ($submissionsByEntryId ?? collect())->get($entry->id) 
                            ?? ($submissionsByCodeLetter ?? collect())->get(strtoupper(trim($entry->code_letter ?? '')));
                    @endphp

                    @if($entrySubmission)
                        @php
                            $subFileType = strtolower($entrySubmission->file_type ?? pathinfo($entrySubmission->file_path ?? '', PATHINFO_EXTENSION));
                            $isImageFile = in_array($subFileType, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                            $subWordCount = $entrySubmission->text_content ? str_word_count(strip_tags($entrySubmission->text_content)) : 0;
                        @endphp
                        <div class="rounded-2xl border-2 border-indigo-200/90 bg-gradient-to-br from-indigo-50/70 via-white to-sky-50/50 p-4 sm:p-5 shadow-xs space-y-4"
                             x-data="{ submissionExpanded: true }">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-indigo-100">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-600 text-white font-mono font-bold text-sm shadow-2xs">
                                        {{ $entry->code_letter ?? chr(65 + $index) }}
                                    </span>
                                    <div>
                                        <h4 class="font-sora font-bold text-sm text-slate-900 flex items-center gap-2">
                                            <span>Candidate Submission (ഉത്തരങ്ങൾ)</span>
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-mono font-semibold">
                                                Submitted
                                            </span>
                                        </h4>
                                        <p class="text-[11px] font-mono text-slate-500">
                                            Received: {{ $entrySubmission->submitted_at?->format('d M, h:i A') ?? $entrySubmission->created_at->format('d M, h:i A') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('admin.online-forms.submissions.pdf', $entrySubmission->id) }}" 
                                       target="_blank"
                                       class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-mono font-bold flex items-center gap-1.5 transition shadow-2xs"
                                       title="Print this candidate's sheet">
                                        <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        <span>PDF</span>
                                    </a>

                                    @if($entrySubmission->file_path)
                                        <a href="{{ $entrySubmission->file_url }}" target="_blank"
                                           class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-indigo-700 border border-indigo-200 text-xs font-mono font-bold flex items-center gap-1.5 transition shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            <span>Open File</span>
                                        </a>
                                    @endif

                                    <button type="button" 
                                            @click="submissionExpanded = !submissionExpanded"
                                            class="px-3 py-1.5 rounded-xl bg-indigo-100 hover:bg-indigo-200 text-indigo-900 text-xs font-mono font-bold flex items-center gap-1.5 transition cursor-pointer">
                                        <span x-text="submissionExpanded ? 'Minimize' : 'View Content'"></span>
                                        <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': !submissionExpanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div x-show="submissionExpanded" class="space-y-4">
                                <!-- 1. Text Submission Content -->
                                @if($entrySubmission->text_content)
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between text-xs font-mono">
                                            <span class="font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <span>Written Content / രചന</span>
                                            </span>
                                            <div class="flex items-center gap-2 text-[11px] text-slate-500">
                                                <span>{{ $subWordCount }} words</span>
                                                <span>•</span>
                                                <button type="button" 
                                                        @click="openTextReader('Code {{ $entry->code_letter }}', {{ json_encode($entrySubmission->text_content) }})"
                                                        class="text-indigo-600 hover:text-indigo-800 font-bold hover:underline cursor-pointer">
                                                    Read Fullscreen
                                                </button>
                                            </div>
                                        </div>

                                        <div class="bg-white border border-slate-200 rounded-xl p-4 max-h-72 overflow-y-auto text-sm leading-relaxed text-slate-800 whitespace-pre-wrap font-serif shadow-2xs">
                                            {{ $entrySubmission->text_content }}
                                        </div>
                                    </div>
                                @endif

                                <!-- 2. Image / Artwork / Document Upload -->
                                @if($entrySubmission->file_path && $isImageFile)
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between text-xs font-mono">
                                            <span class="font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span>Artwork / Drawing (ചിത്രം)</span>
                                            </span>
                                            <span class="text-[11px] text-slate-500 font-mono">
                                                {{ $entrySubmission->file_name ?? 'Image' }} ({{ $entrySubmission->formatted_file_size }})
                                            </span>
                                        </div>

                                        <div class="relative group rounded-2xl border border-slate-200 overflow-hidden bg-slate-900/5 flex items-center justify-center p-2">
                                            <img src="{{ $entrySubmission->file_url }}" 
                                                 alt="Submission Code {{ $entry->code_letter }}" 
                                                 class="max-h-80 w-auto rounded-xl object-contain shadow-xs">
                                            <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3 backdrop-blur-[2px]">
                                                <button type="button" 
                                                        @click="openLightbox('Code {{ $entry->code_letter }}', '{{ $entrySubmission->file_url }}')"
                                                        class="px-4 py-2 rounded-xl bg-white text-slate-900 font-mono font-bold text-xs shadow-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                                    <span>Zoom / വലുതായി കാണുക</span>
                                                </button>
                                                <a href="{{ $entrySubmission->file_url }}" target="_blank" 
                                                   class="px-4 py-2 rounded-xl bg-slate-900 text-white font-mono font-bold text-xs shadow-lg hover:bg-slate-800 flex items-center gap-2">
                                                    <span>Download</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($entrySubmission->file_path)
                                    <!-- PDF / Document file -->
                                    <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-700 flex items-center justify-center font-mono font-black text-xs">
                                                PDF
                                            </div>
                                            <div>
                                                <div class="font-bold text-sm text-slate-900">{{ $entrySubmission->file_name ?? 'Document' }}</div>
                                                <div class="text-[11px] font-mono text-slate-500">{{ $entrySubmission->formatted_file_size }} • Attached Document</div>
                                            </div>
                                        </div>
                                        <a href="{{ $entrySubmission->file_url }}" target="_blank"
                                           class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-mono font-bold text-xs flex items-center gap-1.5 shadow-2xs transition">
                                            <span>Open & Read PDF</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </div>
                                @endif

                                <!-- 3. Video Submission -->
                                @if($entrySubmission->video_url)
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between text-xs font-mono">
                                            <span class="font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                <span>Video Submission (വീഡിയോ)</span>
                                            </span>
                                        </div>

                                        @php
                                            $isVideoFile = Str::contains($entrySubmission->video_url, ['.mp4', '.webm', '.ogg', '/storage/submissions']);
                                        @endphp

                                        @if($isVideoFile)
                                            <div class="rounded-2xl overflow-hidden bg-black aspect-video max-h-96">
                                                <video controls class="w-full h-full object-contain">
                                                    <source src="{{ $entrySubmission->video_url }}">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </div>
                                        @else
                                            <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                                <div class="space-y-1">
                                                    <div class="font-bold text-sm text-slate-900">External Video Performance Link</div>
                                                    <div class="text-xs font-mono text-indigo-600 truncate max-w-md">{{ $entrySubmission->video_url }}</div>
                                                </div>
                                                <a href="{{ $entrySubmission->video_url }}" target="_blank"
                                                   class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-mono font-bold text-xs inline-flex items-center gap-1.5 shadow-2xs transition self-start sm:self-auto">
                                                    <span>Watch Video Submission</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @elseif(!empty($hasOnlineSubmissions))
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-mono text-slate-500 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                <span>ഈ കോഡ് ലെറ്ററിൽ ഓൺലൈൻ സബ്മിഷൻ ഇതുവരെ ലഭിച്ചിട്ടില്ല (No online submission received yet for Code {{ $entry->code_letter }}).</span>
                            </div>
                        </div>
                    @endif

                    <!-- Scoring Mode Hidden Input -->
                    <input type="hidden" name="scoring_mode" :value="evaluationMode">

                    <!-- Mode 1: Simple 100-Point Score Input (Tablet & Mobile Optimized) -->
                    <div x-show="evaluationMode === 'simple'" class="space-y-3">
                        <div class="bg-blue-50/50 border-2 border-blue-200 rounded-2xl p-5 sm:p-6 max-w-md space-y-3 shadow-2xs">
                            <div class="flex items-center justify-between text-xs font-mono">
                                <span class="text-blue-950 font-bold uppercase tracking-wider">Total Score (നേരിട്ടുള്ള മാർക്ക്)</span>
                                <span class="text-[#005c94] font-black bg-blue-100 border border-blue-200 px-3 py-1 rounded-xl">/ 100 max</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="number" step="0.5" min="0" max="100"
                                       inputmode="decimal"
                                       name="total_score"
                                       x-model="directScore"
                                       :required="evaluationMode === 'simple'"
                                       placeholder="0 - 100"
                                       @input="if (directScore > 100) directScore = 100; if (directScore < 0) directScore = 0; saveDraftLocal();"
                                       class="w-36 sm:w-44 h-14 sm:h-16 bg-white border-2 border-blue-300 rounded-xl px-4 text-3xl sm:text-4xl font-mono text-slate-900 text-center font-black focus:outline-none focus:border-[#005c94] focus:ring-2 focus:ring-[#005c94]/20 shadow-xs transition-all">
                                <div class="text-xs font-mono text-slate-500 space-y-1">
                                    <p class="font-bold text-slate-800">100-ൽ എത്ര മാർക്ക് എന്ന് നൽകുക</p>
                                    <p class="text-[11px] text-slate-400">ഉദാഹരണത്തിന്: 85, 92.5, 78</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mode 2: Criteria Breakdown Inputs (Tablet & Mobile Friendly Vertical List) -->
                    <div x-show="evaluationMode === 'criteria'" class="space-y-3">
                        @if($program->has_criteria && $program->scoringCriteria->isNotEmpty())
                            <div class="space-y-2">
                                <div class="flex items-center justify-between px-1 text-xs font-mono">
                                    <span class="font-bold uppercase tracking-wider text-slate-700">
                                        Criteria Breakdown / മാനദണ്ഡങ്ങളുടെ പട്ടിക ({{ $program->scoringCriteria->count() }} ഇനങ്ങൾ):
                                    </span>
                                    <span class="text-slate-500 font-semibold">
                                        ആകെ പരമാവധി: <strong class="text-slate-900">{{ $program->scoringCriteria->sum('max_marks') }}</strong> മാർക്ക്
                                    </span>
                                </div>

                                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden divide-y divide-slate-100 shadow-2xs">
                                    @foreach($program->scoringCriteria as $critIndex => $criterion)
                                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors"
                                             :class="(scores['{{ $criterion->id }}'] > 0) ? 'bg-amber-50/20' : 'hover:bg-slate-50/70'">
                                            <!-- Criterion Title, Index & Max Marks -->
                                            <div class="flex items-start sm:items-center gap-3.5 min-w-0 flex-1">
                                                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center font-mono font-black text-sm shrink-0 shadow-2xs transition-colors"
                                                     :class="(scores['{{ $criterion->id }}'] > 0) ? 'bg-[#be1e2d] text-white' : 'bg-slate-100 text-slate-600 border border-slate-200'">
                                                    {{ $critIndex + 1 }}
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <h4 class="text-base font-sora font-bold text-slate-900 leading-snug">
                                                            {{ $criterion->criterion_name }}
                                                        </h4>
                                                        <span class="px-2.5 py-0.5 rounded-lg bg-amber-50 border border-amber-200 text-[#be1e2d] font-mono text-xs font-bold shrink-0">
                                                            Max: {{ $criterion->max_marks }} Pts
                                                        </span>
                                                    </div>
                                                    <p class="text-xs font-mono text-slate-500 mt-1">
                                                        മാർക്ക് പരിധി: 0 മുതൽ {{ $criterion->max_marks }} വരെ
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Touch-Friendly Score Input (Tablet & Mobile Optimized) -->
                                            <div class="flex items-center justify-end gap-2 shrink-0 self-end sm:self-center">
                                                <div class="flex items-center gap-2 bg-slate-50 p-1.5 rounded-2xl border border-slate-200 shadow-2xs">
                                                    <input type="number"
                                                           step="0.5"
                                                           min="0"
                                                           max="{{ $criterion->max_marks }}"
                                                           inputmode="decimal"
                                                           name="scores[{{ $criterion->id }}]"
                                                           x-model="scores['{{ $criterion->id }}']"
                                                           :required="evaluationMode === 'criteria'"
                                                           placeholder="0"
                                                           @input="if (scores['{{ $criterion->id }}'] > maxMarks['{{ $criterion->id }}']) scores['{{ $criterion->id }}'] = maxMarks['{{ $criterion->id }}']; if (scores['{{ $criterion->id }}'] < 0) scores['{{ $criterion->id }}'] = 0; saveDraftLocal();"
                                                           class="w-24 sm:w-28 h-12 sm:h-13 bg-white border-2 border-slate-300 rounded-xl px-2 text-xl sm:text-2xl font-mono text-slate-900 text-center font-black focus:outline-none focus:border-[#be1e2d] focus:ring-2 focus:ring-[#be1e2d]/20 shadow-xs transition-all">

                                                    <div class="px-2.5 py-1 text-center font-mono">
                                                        <span class="text-[10px] text-slate-400 block uppercase font-bold leading-none">Max</span>
                                                        <span class="text-sm font-bold text-slate-700 font-mono leading-tight">/ {{ $criterion->max_marks }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs font-mono text-amber-900">
                                ഈ പ്രോഗ്രാമിന് മാനദണ്ഡങ്ങൾ ലഭ്യമല്ല. ദയവായി മുകളിൽ 'Simple Mode (100-ൽ മാർക്ക്)' ഉപയോഗിക്കുക.
                            </div>
                        @endif
                    </div>
                    <!-- Grade Selector Buttons (A+, A, B, C) - Touch Friendly for Tablets -->
                    <div class="bg-slate-50/80 border border-slate-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="text-xs font-mono">
                            <span class="font-bold text-slate-800 uppercase tracking-wider">Grade Option / ഗ്രേഡ്:</span>
                            <span class="text-slate-400 block sm:inline mt-0.5 sm:mt-0 sm:ml-1.5">(Score അനുസരിച്ച് ഓട്ടോമാറ്റിക് ആയി കണക്കാക്കും)</span>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <input type="hidden" name="grade" :value="selectedGrade || (computedGrade !== '-' ? computedGrade : '')">
                            <template x-for="g in ['A+', 'A', 'B', 'C']" :key="g">
                                <button type="button" 
                                        @click="setGrade(g)"
                                        :class="(selectedGrade === g || (!selectedGrade && computedGrade === g))
                                            ? (g === 'A+' || g === 'A' ? 'bg-emerald-600 text-white shadow-xs border-emerald-600 scale-102 font-black' : (g === 'B' ? 'bg-blue-600 text-white shadow-xs border-blue-600 scale-102 font-black' : 'bg-amber-600 text-white shadow-xs border-amber-600 scale-102 font-black'))
                                            : 'bg-white hover:bg-slate-100 text-slate-700 border-slate-200 font-bold'"
                                        class="h-11 sm:h-12 min-w-12 sm:min-w-14 px-3 sm:px-4 rounded-xl border text-sm sm:text-base font-mono transition-all shadow-2xs flex items-center justify-center cursor-pointer active:scale-95">
                                    <span x-text="g"></span>
                                </button>
                            </template>
                            <button type="button" x-show="selectedGrade" @click="selectedGrade = ''; saveDraftLocal();" 
                                    class="px-3 py-2 text-xs font-mono text-slate-400 hover:text-slate-700 underline cursor-pointer">
                                Reset Auto
                            </button>
                        </div>
                    </div>
                    <!-- Remarks & Submit Action -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                        <div class="flex-1">
                            <input type="text" name="remarks" x-model="remarks" @input="saveDraftLocal()" placeholder="Confidential remarks for this performance..."
                                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 h-12 text-xs sm:text-sm text-slate-900 focus:outline-none focus:border-[#005c94] focus:bg-white font-sora shadow-2xs transition-all">
                            <div x-show="errorMessage" class="text-xs font-mono font-bold text-red-600 mt-1" x-text="errorMessage"></div>
                            <div x-show="saveSuccess" class="text-xs font-mono font-bold text-emerald-600 mt-1 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Scores saved successfully (Total: <span x-text="submittedScore"></span>)</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="submit" 
                                    :disabled="isSaving"
                                    :class="isSaving ? 'opacity-60 cursor-not-allowed' : 'hover:brightness-105 active:scale-95'"
                                    class="w-full sm:w-auto h-12 sm:h-13 px-6 sm:px-8 bg-[#005c94] hover:bg-[#004875] text-white font-mono font-bold text-xs sm:text-sm uppercase rounded-xl shadow-md shadow-[#005c94]/20 flex items-center justify-center gap-2 transition-all cursor-pointer">
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

    <!-- Fullscreen Text Reader Modal -->
    <div x-show="textReaderOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-md flex items-center justify-center p-4 sm:p-6"
         style="display: none;"
         @keydown.escape.window="textReaderOpen = false">
        <div class="bg-white rounded-3xl max-w-3xl w-full max-h-[90vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden"
             @click.away="textReaderOpen = false">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-mono font-bold text-xs flex items-center justify-center" x-text="textReaderCode"></span>
                    <h3 class="font-sora font-bold text-sm text-slate-900">Written Submission Reader</h3>
                </div>
                <button type="button" @click="textReaderOpen = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 sm:p-8 overflow-y-auto font-serif text-base leading-relaxed text-slate-800 whitespace-pre-wrap selection:bg-indigo-100 max-h-[70vh]">
                <div x-text="textReaderContent"></div>
            </div>
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button type="button" @click="textReaderOpen = false" class="px-5 py-2 rounded-xl bg-slate-900 text-white text-xs font-mono font-bold hover:bg-slate-800 transition">
                    Close Reader
                </button>
            </div>
        </div>
    </div>

    <!-- Image Lightbox Modal -->
    <div x-show="lightboxOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4"
         style="display: none;"
         @keydown.escape.window="lightboxOpen = false">
        <div class="relative max-w-5xl max-h-[92vh] flex flex-col items-center justify-center"
             @click.away="lightboxOpen = false">
            <div class="absolute -top-12 right-0 flex items-center gap-2">
                <a :href="lightboxSrc" target="_blank" class="px-3 py-1.5 rounded-xl bg-white/20 hover:bg-white/30 text-white font-mono text-xs font-bold transition flex items-center gap-1.5">
                    <span>Open Original</span>
                </a>
                <button type="button" @click="lightboxOpen = false" class="p-2 rounded-xl bg-white/20 hover:bg-white/30 text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <img :src="lightboxSrc" alt="Enlarged Submission" class="max-h-[85vh] max-w-full rounded-2xl object-contain shadow-2xl border border-white/10">
            <div class="mt-3 text-center text-xs font-mono text-white/70">
                <span x-text="lightboxCode"></span> • Full High-Resolution View
            </div>
        </div>
    </div>
</div>
@endsection