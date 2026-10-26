@extends('layouts.judge', ['title' => 'Evaluation: ' . $program->name])

@php
    $circlePalettes = [
        ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-100'],
        ['bg' => 'bg-rose-50', 'text' => 'text-[#be1e2d]', 'border' => 'border-rose-100'],
        ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-100'],
        ['bg' => 'bg-sky-50', 'text' => 'text-[#005c94]', 'border' => 'border-sky-100'],
        ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-100'],
        ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-100'],
        ['bg' => 'bg-teal-50', 'text' => 'text-teal-700', 'border' => 'border-teal-100'],
        ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'border' => 'border-orange-100'],
    ];
@endphp

@section('content')
<div class="space-y-6" x-data="{
    topicModalOpen: false,
    rulesModalOpen: false,
    textReaderOpen: false,
    textReaderCode: '',
    textReaderContent: '',
    lightboxOpen: false,
    lightboxSrc: '',
    lightboxCode: '',
    evaluationMode: 'simple',
    openTextReader(code, text) {
        this.textReaderCode = code;
        this.textReaderContent = text;
        this.textReaderOpen = true;
    },
    openLightbox(code, src) {
        this.lightboxCode = code;
        this.lightboxSrc = src;
        this.lightboxOpen = true;
    },
    clearAllLocalDrafts() {
        if (confirm('ബ്രൗസറിൽ താൽക്കാലികമായി സേവ് ചെയ്തിട്ടുള്ള ഡ്രാഫ്റ്റ് മാർക്കുകൾ ക്ലിയർ ചെയ്യണോ? (Clear all local draft scores?)')) {
            const prefix = 'quaf_draft_{{ $program->id }}_';
            Object.keys(localStorage).forEach(key => {
                if (key.startsWith(prefix)) {
                    localStorage.removeItem(key);
                }
            });
            window.location.reload();
        }
    }
}">
    <!-- Top Bar (Clean & Harmonious with Festival Theme) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-slate-200 p-4 sm:p-5 rounded-2xl shadow-xs">
        <div class="flex items-center gap-3">
            <a href="{{ route('judge.dashboard') }}" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-mono font-bold transition-colors">
                &larr; Back to Schedule
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-base sm:text-lg font-sora font-bold text-slate-900">{{ $program->name }}</h1>
                    @if($program->malayalam_name)
                        <span class="text-xs font-ml text-slate-500">({{ $program->malayalam_name }})</span>
                    @endif
                    <span class="px-2.5 py-0.5 rounded-lg bg-rose-50 text-[#be1e2d] border border-rose-200 text-[10px] font-mono font-bold uppercase tracking-wider">
                        {{ $program->zone?->name ?: $program->zone_name }}
                    </span>
                </div>
                <p class="text-[11px] font-mono text-slate-500 flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-0.5">
                    <span>Code: <strong class="text-slate-800">{{ $program->code }}</strong></span>
                    <span>•</span>
                    <span>Zone: <strong class="text-[#be1e2d]">{{ $program->zone?->name ?: $program->zone_name }}</strong></span>
                    <span>•</span>
                    <span>Stage: <strong class="text-slate-800">{{ $program->stage->name ?? 'TBA' }}</strong></span>
                    <span>•</span>
                    <span>Duration: <strong class="text-slate-800">{{ $program->duration_minutes }} Mins</strong></span>
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

            <!-- Topic Button -->
            <button type="button" @click="topicModalOpen = true"
                    class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 text-xs font-mono font-bold flex items-center gap-1.5 transition-all shadow-2xs cursor-pointer">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                <span>Topic / വിഷയം</span>
            </button>

            <!-- Rules Button -->
            <button type="button" @click="rulesModalOpen = true"
                    class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 text-xs font-mono font-bold flex items-center gap-1.5 transition-all shadow-2xs cursor-pointer">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Rules & Guidelines</span>
            </button>

            <!-- Reset Drafts Button -->
            <button type="button" @click="clearAllLocalDrafts()"
                    class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-700 text-slate-600 border border-slate-200 text-xs font-mono font-semibold flex items-center gap-1.5 transition-all cursor-pointer"
                    title="Clear local draft scores stored in this browser">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>Reset Drafts</span>
            </button>

            <span class="px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-800 text-xs font-mono font-bold">
                {{ $entries->count() }} Candidates
            </span>
        </div>
    </div>

    <!-- Scoring Mode Switcher (If Program has Criteria) -->
    @if($program->has_criteria && $program->scoringCriteria->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-2xl p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-2">
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-700">Scoring Mode:</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wide"
                      :class="evaluationMode === 'simple' ? 'bg-blue-100 text-[#005c94] border border-blue-200' : 'bg-rose-100 text-[#be1e2d] border border-rose-200'"
                      x-text="evaluationMode === 'simple' ? 'Simple Mode' : 'Criteria Mode'"></span>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0">
                <div class="relative min-w-[200px] sm:min-w-[240px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                         :class="evaluationMode === 'simple' ? 'text-[#005c94]' : 'text-[#be1e2d]'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <select x-model="evaluationMode"
                            class="w-full pl-9 pr-9 py-2 rounded-xl border-2 font-mono text-xs font-bold appearance-none transition-all cursor-pointer shadow-2xs focus:outline-none"
                            :class="evaluationMode === 'simple'
                                ? 'bg-blue-50/80 border-[#005c94]/40 text-[#005c94] focus:border-[#005c94] focus:ring-2 focus:ring-[#005c94]/20'
                                : 'bg-rose-50/80 border-[#be1e2d]/40 text-[#be1e2d] focus:border-[#be1e2d] focus:ring-2 focus:ring-[#be1e2d]/20'">
                        <option value="simple">Simple Mode (100 Mark)</option>
                        <option value="criteria">Criteria Mode</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <div class="hidden sm:flex p-1 bg-slate-100 rounded-xl border border-slate-200 gap-1 shrink-0">
                    <button type="button"
                            @click="evaluationMode = 'simple'"
                            :class="evaluationMode === 'simple' ? 'bg-[#005c94] text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                            class="px-3 py-1 rounded-lg text-xs font-mono transition-all flex items-center gap-1 cursor-pointer">
                        <span>Simple</span>
                    </button>
                    <button type="button"
                            @click="evaluationMode = 'criteria'"
                            :class="evaluationMode === 'criteria' ? 'bg-[#be1e2d] text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                            class="px-3 py-1 rounded-lg text-xs font-mono transition-all flex items-center gap-1 cursor-pointer">
                        <span>Criteria</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Network Connectivity Status Indicator -->
    <div x-data="{ online: navigator.onLine }"
         x-init="window.addEventListener('online', () => online = true); window.addEventListener('offline', () => online = false)"
         x-show="!online" 
         class="p-3.5 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 text-xs font-mono flex items-center justify-between shadow-xs"
         style="display: none;">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
            <span class="font-bold">Offline Mode: Internet connection is unavailable. Scores are saved locally and will sync when reconnected.</span>
        </div>
        <span class="text-[10px] uppercase font-bold px-2.5 py-0.5 rounded-lg bg-rose-200 text-rose-900">Local Draft Active</span>
    </div>

    <!-- Program Topic Bar (Immediately above evaluation mark entry section) -->
    <div class="bg-gradient-to-r from-amber-50 via-amber-50/70 to-orange-50/50 border border-amber-200/90 rounded-2xl p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
        <div class="flex items-center gap-3 min-w-0">
            <span class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            </span>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-amber-800">
                        Competition Topic / മത്സര വിഷയം
                    </span>
                    @if(!empty($program->topic))
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    @endif
                </div>
                @if(!empty($program->topic))
                    <p class="text-xs sm:text-sm font-revathi-bold font-bold text-slate-900 truncate mt-0.5">
                        {{ Str::limit(strip_tags($program->topic), 95) }}
                    </p>
                @else
                    <p class="text-xs font-mono text-slate-500 italic mt-0.5">
                        ഈ മത്സരത്തിന് വിഷയം ചേർത്തിട്ടില്ല (Click to view)
                    </p>
                @endif
            </div>
        </div>
        <button type="button" @click="topicModalOpen = true"
                class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-mono text-xs font-bold transition-all shadow-xs flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
            <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <span>View Topic / വിഷയം കാണുക</span>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- MODE 1: SIMPLE MODE EVALUATION TABLE (Minimalist, Compact & Elegant)        -->
    <!-- Matches User Design: CODE (left), GRADE (center auto), MARK (right)       -->
    <!-- ========================================================================= -->
    <div x-show="evaluationMode === 'simple'" class="max-w-xl mx-auto w-full space-y-4">
        <div class="bg-white border border-slate-200/90 rounded-2xl sm:rounded-3xl shadow-xs overflow-hidden">
            <!-- Table Header (Strict Single Horizontal Line) -->
            <div class="flex items-center justify-between px-4 sm:px-6 py-3.5 bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-mono font-bold tracking-wider text-slate-500 uppercase"
                 style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                <div style="flex: 1; text-align: left; display: flex; align-items: center;">
                    <span>CODE</span>
                </div>
                <div style="flex: 1; text-align: center; display: flex; align-items: center; justify-content: center;">
                    <span>GRADE</span>
                </div>
                <div style="flex: 1; text-align: right; display: flex; align-items: center; justify-content: flex-end; padding-right: 32px;">
                    <span>MARK</span>
                </div>
            </div>

            <!-- Participant Rows -->
            <div class="divide-y divide-slate-100">
                @forelse($entries as $index => $entry)
                    @php
                        $sheet = $scoreSheets->get($entry->id);
                        $isSubmitted = $sheet && $sheet->is_submitted;
                        $savedScores = $sheet ? ($sheet->criteria_scores ?? []) : [];
                        $savedTotal = $sheet ? ($sheet->total_score ?? null) : null;
                        $entrySubmission = ($submissionsByEntryId ?? collect())->get($entry->id) 
                            ?? ($submissionsByCodeLetter ?? collect())->get(strtoupper(trim($entry->code_letter ?? '')));
                        $palette = $circlePalettes[$index % count($circlePalettes)];
                        $codeLetter = $entry->code_letter ?? chr(65 + $index);
                    @endphp
                    <div x-data="{
                             id: {{ $entry->id }},
                             code: '{{ $codeLetter }}',
                             directScore: '{{ $savedTotal !== null ? $savedTotal : '' }}',
                             remarks: '{{ addslashes($sheet->remarks ?? '') }}',
                             isSubmitted: {{ $isSubmitted ? 'true' : 'false' }},
                             isSaving: false,
                             saveSuccess: false,
                             isFocused: false,
                             expanded: false,
                             storageKey: 'quaf_draft_{{ $program->id }}_{{ $entry->id }}',
                             saveUrl: '{{ route('judge.evaluate.save', [$program, $entry]) }}',
                             init() {
                                 try {
                                     const draft = localStorage.getItem(this.storageKey);
                                     if (draft && !this.isSubmitted) {
                                         const parsed = JSON.parse(draft);
                                         if (parsed.directScore !== undefined && parsed.directScore !== '') this.directScore = parsed.directScore;
                                         if (parsed.remarks) this.remarks = parsed.remarks;
                                     }
                                 } catch (e) {}
                             },
                             get computedGrade() {
                                 const t = parseFloat(this.directScore);
                                 if (isNaN(t) || t <= 0) return '-';
                                 if (t >= 90) return 'A+';
                                 if (t >= 70) return 'A';
                                 if (t >= 60) return 'B';
                                 if (t >= 50) return 'C';
                                 return '-';
                             },
                             handleInput() {
                                 let val = parseFloat(this.directScore);
                                 if (val > 100) this.directScore = 100;
                                 if (val < 0) this.directScore = 0;
                                 try {
                                     localStorage.setItem(this.storageKey, JSON.stringify({
                                         directScore: this.directScore,
                                         remarks: this.remarks
                                     }));
                                 } catch (e) {}
                             },
                             saveScore() {
                                 if (this.isSaving) return;
                                 const scoreVal = parseFloat(this.directScore);
                                 if (this.directScore === '' || this.directScore === null || isNaN(scoreVal)) {
                                     return;
                                 }
                                 this.isSaving = true;
                                 this.saveSuccess = false;
                                 const formData = new FormData();
                                 formData.append('_token', '{{ csrf_token() }}');
                                 formData.append('scoring_mode', 'simple');
                                 formData.append('total_score', this.directScore);
                                 const gr = this.computedGrade;
                                 formData.append('grade', gr !== '-' ? gr : '');
                                 if (this.remarks) formData.append('remarks', this.remarks);

                                 fetch(this.saveUrl, {
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
                                         throw new Error(err.message || 'Error saving score.');
                                     }
                                     return res.json();
                                 })
                                 .then(data => {
                                     this.isSubmitted = true;
                                     this.saveSuccess = true;
                                     try { localStorage.removeItem(this.storageKey); } catch (e) {}
                                     setTimeout(() => { this.saveSuccess = false; }, 3000);
                                 })
                                 .catch(err => {
                                     // Score remains safely preserved in localStorage draft
                                 })
                                 .finally(() => {
                                     this.isSaving = false;
                                 });
                             }
                         }"
                         :class="isFocused ? 'bg-indigo-50/40' : (isSubmitted ? 'bg-white' : 'hover:bg-slate-50/50')"
                         class="transition-colors"
                    >
                        <!-- Main Entry Row (Strict Single Horizontal Line) -->
                        <div class="flex items-center justify-between px-4 sm:px-6 py-3"
                             style="display: flex; align-items: center; justify-content: space-between; width: 100%; min-height: 58px;">
                            <!-- 1. CODE (Left) -->
                            <div style="flex: 1; display: flex; align-items: center; justify-content: flex-start; gap: 8px;">
                                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full {{ $palette['bg'] }} {{ $palette['text'] }} {{ $palette['border'] }} border-2 flex items-center justify-center font-mono font-bold text-base sm:text-lg shadow-2xs shrink-0"
                                     style="display: flex; align-items: center; justify-content: center; width: 42px; height: 42px;">
                                    {{ $codeLetter }}
                                </div>

                                <!-- Collapsible Work / Remarks Toggle Button -->
                                @if($entrySubmission)
                                    <button type="button" @click="expanded = !expanded" 
                                            class="p-1 px-1.5 rounded-lg text-indigo-700 hover:bg-indigo-50 border border-indigo-200/80 text-[10px] font-mono font-bold flex items-center gap-1 transition shadow-2xs shrink-0"
                                            title="View Candidate Submission">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                        <span>Work</span>
                                    </button>
                                @else
                                    <button type="button" @click="expanded = !expanded"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition shrink-0"
                                            title="Add remarks / note">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                @endif
                            </div>

                            <!-- 2. GRADE (Center - Perfectly on same line) -->
                            <div style="flex: 1; display: flex; align-items: center; justify-content: center; text-align: center;">
                                <span class="inline-flex items-center justify-center min-w-[42px] px-2.5 py-1 rounded-full text-xs font-mono font-black transition-all shadow-2xs"
                                      style="display: inline-flex; align-items: center; justify-content: center;"
                                      :class="{
                                          'bg-emerald-100 text-emerald-800 border border-emerald-300': computedGrade === 'A+',
                                          'bg-emerald-50 text-emerald-700 border border-emerald-200': computedGrade === 'A',
                                          'bg-blue-50 text-[#005c94] border border-blue-200': computedGrade === 'B',
                                          'bg-amber-50 text-amber-800 border border-amber-200': computedGrade === 'C',
                                          'text-slate-300 font-bold': computedGrade === '-'
                                      }"
                                      x-text="computedGrade">-</span>
                            </div>

                            <!-- 3. MARK (Right - Perfectly on same line) -->
                            <div style="flex: 1; display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                                <input type="number"
                                       step="0.5"
                                       min="0"
                                       max="100"
                                       inputmode="decimal"
                                       x-model="directScore"
                                       placeholder="0"
                                       id="simple_mark_{{ $index }}"
                                       @focus="isFocused = true"
                                       @blur="isFocused = false; saveScore()"
                                       @input="handleInput()"
                                       @keydown.enter.prevent="saveScore(); const next = document.getElementById('simple_mark_{{ $index + 1 }}'); if (next) { next.focus(); next.select(); }"
                                       class="w-20 sm:w-24 h-10 sm:h-11 bg-slate-50/80 hover:bg-white focus:bg-white border-2 border-slate-200 focus:border-[#be1e2d] rounded-xl text-center font-mono font-black text-base sm:text-lg text-slate-800 transition-all focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/15 shadow-2xs">

                                <!-- Row Status Indicator (Spinning loader / Saved checkmark) -->
                                <div class="w-6 h-6 flex items-center justify-center shrink-0" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                    <template x-if="isSaving">
                                        <svg class="w-4 h-4 text-[#be1e2d] animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    </template>
                                    <template x-if="!isSaving && saveSuccess">
                                        <span class="text-emerald-600 animate-bounce" title="Saved">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    </template>
                                    <template x-if="!isSaving && !saveSuccess && isSubmitted">
                                        <span class="text-emerald-500 opacity-80" title="Submitted">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Collapsible Drawer: Remarks and Online Submission (if any) -->
                        <div x-show="expanded" x-collapse class="px-4 sm:px-6 py-3.5 bg-slate-50/90 border-t border-slate-100 space-y-3 text-xs" style="display: none;">
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                <span class="font-mono text-slate-500 font-bold shrink-0">Remarks:</span>
                                <input type="text" x-model="remarks" @blur="saveScore()" placeholder="Confidential note for Code {{ $codeLetter }}..."
                                       class="flex-1 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-800 text-xs focus:outline-none focus:border-[#be1e2d]">
                                <button type="button" @click="saveScore()" class="px-3 py-1.5 rounded-lg bg-slate-800 text-white font-mono font-bold text-xs hover:bg-slate-900 transition">Save Note</button>
                            </div>

                            @if($entrySubmission)
                                @php
                                    $isImageFile = $entrySubmission->isImage();
                                    $subWordCount = $entrySubmission->text_content ? str_word_count(strip_tags($entrySubmission->text_content)) : 0;
                                @endphp
                                <div class="rounded-xl border border-indigo-200 bg-white p-3 space-y-3">
                                    <div class="flex items-center justify-between font-mono text-[11px] text-slate-600">
                                        <span class="font-bold text-indigo-700">Code {{ $codeLetter }} Submission Content:</span>
                                        <div class="flex items-center gap-2">
                                            @if($entrySubmission->file_path)
                                                <a href="{{ $entrySubmission->file_url }}" target="_blank" class="text-indigo-600 hover:underline font-bold">Open File</a>
                                            @endif
                                            @if($entrySubmission->text_content)
                                                <button type="button" 
                                                        @click="openTextReader('Code {{ $codeLetter }}', {{ json_encode($entrySubmission->text_content) }})"
                                                        class="text-indigo-600 hover:underline font-bold">
                                                    Read Fullscreen
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    @if($entrySubmission->text_content)
                                        <div class="max-h-40 overflow-y-auto text-xs text-slate-800 leading-relaxed font-serif bg-slate-50 p-2.5 rounded-lg border border-slate-100 whitespace-pre-wrap">
                                            {{ $entrySubmission->text_content }}
                                        </div>
                                    @endif

                                    @if($entrySubmission->file_path && $isImageFile)
                                        <div class="flex items-center justify-center p-2 bg-slate-50 rounded-lg border border-slate-100">
                                            <img src="{{ $entrySubmission->file_url }}" alt="Work" class="max-h-48 object-contain rounded cursor-pointer"
                                                 @click="openLightbox('Code {{ $codeLetter }}', '{{ $entrySubmission->file_url }}')">
                                        </div>
                                    @endif

                                    @if($entrySubmission->video_url)
                                        <div class="text-xs font-mono">
                                            <a href="{{ $entrySubmission->video_url }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-bold inline-flex items-center gap-1.5">
                                                <span>Watch Video Submission</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-500 font-mono text-xs space-y-2">
                        <svg class="w-8 h-8 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div class="font-bold text-slate-800">No Candidates Marked PRESENT Yet</div>
                        <p class="text-slate-400 max-w-sm mx-auto">Only contestants with attendance marked "PRESENT" appear here for evaluation.</p>
                    </div>
                @endforelse
            </div>

            <!-- Table Footer: Fast Navigation Help & Save All Action -->
            @if($entries->isNotEmpty())
                <div class="px-4 sm:px-6 py-3.5 bg-slate-50/80 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs font-mono">
                    <div class="flex items-center gap-2 text-slate-500">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Auto-saves on blur • Enter or Tab jumps to next mark</span>
                    </div>
                    <button type="button" @click="document.querySelectorAll('[id^=simple_mark_]').forEach(el => el.dispatchEvent(new Event('blur')))"
                            class="w-full sm:w-auto px-4 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-mono font-bold text-xs uppercase shadow-sm shadow-[#be1e2d]/20 transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save All Scores</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODE 2: CRITERIA BREAKDOWN MODE (When Detailed Criteria Evaluation Needed)-->
    <!-- ========================================================================= -->
    @if($program->has_criteria && $program->scoringCriteria->isNotEmpty())
        <div x-show="evaluationMode === 'criteria'" class="space-y-6" style="display: none;">
            <!-- Criteria Rubric Banner -->
            <div class="bg-white border border-slate-200 rounded-2xl p-4 flex flex-wrap items-center gap-3 text-xs font-mono shadow-2xs">
                <span class="text-slate-500 font-bold uppercase tracking-wider">Evaluation Rubric:</span>
                @foreach($program->scoringCriteria as $criterion)
                    <div class="px-3 py-1 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-2">
                        <span class="text-slate-800 font-medium">{{ $criterion->criterion_name }}</span>
                        <span class="text-[#be1e2d] font-bold">({{ $criterion->max_marks }} pts)</span>
                    </div>
                @endforeach
            </div>

            <!-- Criteria Mode Cards -->
            <div class="space-y-6">
                @foreach($entries as $index => $entry)
                    @php
                        $sheet = $scoreSheets->get($entry->id);
                        $isSubmitted = $sheet && $sheet->is_submitted;
                        $savedScores = $sheet ? ($sheet->criteria_scores ?? []) : [];
                        $palette = $circlePalettes[$index % count($circlePalettes)];
                        $codeLetter = $entry->code_letter ?? chr(65 + $index);
                    @endphp
                    <div class="bg-white border {{ $isSubmitted ? 'border-emerald-300' : 'border-slate-200' }} rounded-3xl p-6 transition-all relative overflow-hidden shadow-sm"
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
                             remarks: '{{ addslashes($sheet->remarks ?? '') }}',
                             isSubmitted: {{ $isSubmitted ? 'true' : 'false' }},
                             submittedScore: {{ $sheet ? ($sheet->total_score ?? 0) : 0 }},
                             isSaving: false,
                             saveSuccess: false,
                             errorMessage: '',
                             get total() {
                                 let sum = 0;
                                 for (let key in this.scores) {
                                     sum += parseFloat(this.scores[key]) || 0;
                                 }
                                 return Math.round(sum * 10) / 10;
                             },
                             get computedGrade() {
                                 const t = this.total;
                                 if (t >= 90) return 'A+';
                                 if (t >= 70) return 'A';
                                 if (t >= 60) return 'B';
                                 if (t >= 50) return 'C';
                                 return '-';
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
                                         throw new Error(err.message || 'Server error.');
                                     }
                                     return res.json();
                                 })
                                 .then(data => {
                                     this.isSubmitted = true;
                                     this.submittedScore = data.total_score || this.total;
                                     this.saveSuccess = true;
                                     setTimeout(() => { this.saveSuccess = false; }, 3000);
                                 })
                                 .catch(err => {
                                     this.errorMessage = err.message || 'Error saving score.';
                                 })
                                 .finally(() => {
                                     this.isSaving = false;
                                 });
                             }
                         }">
                        <form method="POST" action="{{ route('judge.evaluate.save', [$program, $entry]) }}" @submit.prevent="submitEvaluation($event)" class="space-y-6">
                            @csrf
                            <input type="hidden" name="scoring_mode" value="criteria">

                            <!-- Header -->
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-full {{ $palette['bg'] }} {{ $palette['text'] }} {{ $palette['border'] }} border-2 flex items-center justify-center font-mono font-bold text-lg shadow-2xs">
                                        {{ $codeLetter }}
                                    </div>
                                    <div>
                                        <h3 class="text-base font-sora font-bold text-slate-900">Code {{ $codeLetter }}</h3>
                                        <p class="text-[11px] font-mono text-slate-400">Strictly Anonymous Evaluation</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="px-3.5 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-center font-mono">
                                        <span class="text-[9px] text-slate-400 block uppercase font-bold">Total</span>
                                        <span class="text-lg font-black text-slate-900" x-text="total">0</span>
                                    </div>
                                    <div class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-center font-mono">
                                        <span class="text-[9px] text-slate-400 block uppercase font-bold">Grade</span>
                                        <span class="text-xs font-black inline-block px-2 py-0.5 rounded-full"
                                              :class="{
                                                  'bg-emerald-100 text-emerald-800 border border-emerald-300': computedGrade === 'A+',
                                                  'bg-emerald-50 text-emerald-700 border border-emerald-200': computedGrade === 'A',
                                                  'bg-blue-50 text-[#005c94] border border-blue-200': computedGrade === 'B',
                                                  'bg-amber-50 text-amber-800 border border-amber-200': computedGrade === 'C',
                                                  'text-slate-300': computedGrade === '-'
                                              }"
                                              x-text="computedGrade">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Criteria Breakdown Inputs -->
                            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden divide-y divide-slate-100">
                                @foreach($program->scoringCriteria as $critIndex => $criterion)
                                    <div class="p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center font-mono font-bold text-xs bg-slate-100 text-slate-700 shrink-0">
                                                {{ $critIndex + 1 }}
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-sora font-semibold text-slate-900">{{ $criterion->criterion_name }}</h4>
                                                <span class="text-[11px] font-mono text-[#be1e2d] font-bold">Max: {{ $criterion->max_marks }} pts</span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 self-end sm:self-center">
                                            <input type="number"
                                                   step="0.5"
                                                   min="0"
                                                   max="{{ $criterion->max_marks }}"
                                                   inputmode="decimal"
                                                   name="scores[{{ $criterion->id }}]"
                                                   x-model="scores['{{ $criterion->id }}']"
                                                   @input="if (scores['{{ $criterion->id }}'] > maxMarks['{{ $criterion->id }}']) scores['{{ $criterion->id }}'] = maxMarks['{{ $criterion->id }}']; if (scores['{{ $criterion->id }}'] < 0) scores['{{ $criterion->id }}'] = 0;"
                                                   class="w-20 sm:w-24 h-10 bg-slate-50 border-2 border-slate-200 focus:border-[#be1e2d] rounded-xl text-center font-mono font-black text-base text-slate-900 focus:outline-none focus:bg-white transition-all">
                                            <span class="text-xs font-mono text-slate-400 font-bold">/ {{ $criterion->max_marks }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Remarks & Submit -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                                <input type="text" name="remarks" x-model="remarks" placeholder="Confidential remarks for Code {{ $codeLetter }}..."
                                       class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 h-11 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white font-sora transition-all">
                                <button type="submit"
                                        :disabled="isSaving"
                                        class="h-11 px-6 bg-[#be1e2d] hover:bg-[#a01624] text-white font-mono font-bold text-xs uppercase rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <template x-if="isSaving">
                                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    </template>
                                    <template x-if="!isSaving">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </template>
                                    <span x-text="isSaving ? 'Saving...' : (isSubmitted ? 'Update Score' : 'Save Score')"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Program Topic Modal -->
    <div x-show="topicModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;"
         @keydown.escape.window="topicModalOpen = false">
        <div class="bg-white border border-slate-200 rounded-3xl max-w-2xl w-full p-6 sm:p-8 space-y-6 shadow-2xl relative"
             @click.away="topicModalOpen = false">
            <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-mono uppercase tracking-widest text-amber-700 font-bold">Competition Topic / മത്സര വിഷയം</span>
                        <h2 class="text-xl font-sora font-black text-slate-900 mt-0.5">{{ $program->name }}</h2>
                        @if($program->malayalam_name)
                            <p class="text-xs font-ml text-slate-500">{{ $program->malayalam_name }}</p>
                        @endif
                    </div>
                </div>
                <button type="button" @click="topicModalOpen = false" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Program Meta Badges -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs font-mono">
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <span class="text-[10px] text-slate-400 block uppercase">Code</span>
                    <span class="text-slate-900 font-bold">{{ $program->code }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <span class="text-[10px] text-slate-400 block uppercase">Zone</span>
                    <span class="text-[#be1e2d] font-bold">{{ $program->zone?->name ?? $program->eligibility ?? 'All' }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <span class="text-[10px] text-slate-400 block uppercase">Stage</span>
                    <span class="text-slate-800 font-bold">{{ $program->stage->name ?? 'TBA' }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <span class="text-[10px] text-slate-400 block uppercase">Duration</span>
                    <span class="text-slate-800 font-bold">{{ $program->duration_minutes }} Mins</span>
                </div>
            </div>

            <!-- Topic Content Box -->
            <div class="space-y-2">
                <span class="text-xs font-mono uppercase tracking-wider text-slate-600 font-bold">Official Topic / വിഷയം:</span>
                <div class="p-6 rounded-2xl bg-amber-50/70 border-2 border-amber-200/90 text-slate-900 leading-relaxed max-h-80 overflow-y-auto shadow-inner">
                    @if(!empty($program->topic))
                        <div class="text-base sm:text-xl font-revathi-bold font-bold text-slate-900 whitespace-pre-line leading-relaxed">
                            {!! nl2br(e($program->topic)) !!}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-10 h-10 mx-auto text-slate-300 mb-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="font-mono text-sm font-semibold text-slate-600">ഈ മത്സരത്തിന് വിഷയം ചേർത്തിട്ടില്ല.</p>
                            <p class="font-mono text-xs text-slate-400 mt-1">(No specific topic has been configured for this competition yet.)</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                <span class="text-[11px] font-mono text-slate-400">QUAF Adjudication Console</span>
                <button type="button" @click="topicModalOpen = false"
                        class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-mono font-bold hover:bg-slate-800 transition-all cursor-pointer shadow-sm">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Rules & Guidelines Modal -->
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
                    <span class="text-[10px] font-mono uppercase tracking-widest text-[#be1e2d] font-bold">Program Guidelines</span>
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
                    <span class="text-[10px] text-slate-400 block uppercase">Zone</span>
                    <span class="text-[#be1e2d] font-bold">{{ $program->zone?->name ?? $program->eligibility ?? 'All' }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <span class="text-[10px] text-slate-400 block uppercase">Type</span>
                    <span class="text-slate-800 font-bold capitalize">{{ $program->type }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <span class="text-[10px] text-slate-400 block uppercase">Duration</span>
                    <span class="text-slate-800 font-bold">{{ $program->duration_minutes }} Mins</span>
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
            @if($program->scoringCriteria->isNotEmpty())
                <div class="space-y-3">
                    <h3 class="text-sm font-mono font-bold text-slate-800 uppercase tracking-wider">Scoring Rubric</h3>
                    <div class="space-y-2">
                        @foreach($program->scoringCriteria as $criterion)
                            <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                                <span class="text-slate-800 font-semibold">{{ $criterion->criterion_name }}</span>
                                <span class="px-2.5 py-0.5 rounded-full bg-rose-50 text-[#be1e2d] border border-rose-200 font-bold">Max: {{ $criterion->max_marks }} Marks</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="pt-2 text-right">
                <button type="button" @click="rulesModalOpen = false"
                        class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-mono font-bold hover:bg-slate-800 transition-all cursor-pointer">
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
