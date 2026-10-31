@extends('layouts.judge', ['title' => 'Custom Judge Panel — ' . ($selectedProgram->name ?? 'Stage Evaluation')])

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-16 font-sora"
     x-data="{
        activeJudgeNum: {{ (int) $judgeNum }},
        selectedProgId: {{ $selectedProgram ? $selectedProgram->id : 'null' }},
        searchFilter: '',
        savingEntryId: null,
        successToast: '',
        toastTimeout: null,
        showToast(msg) {
            this.successToast = msg;
            if (this.toastTimeout) clearTimeout(this.toastTimeout);
            this.toastTimeout = setTimeout(() => { this.successToast = ''; }, 4000);
        },
        switchJudge(num) {
            this.activeJudgeNum = num;
            let url = new URL(window.location.href);
            url.searchParams.set('judge_num', num);
            if (this.selectedProgId) url.searchParams.set('program_id', this.selectedProgId);
            window.location.href = url.toString();
        },
        switchProgram(progId) {
            this.selectedProgId = progId;
            let url = new URL(window.location.href);
            url.searchParams.set('program_id', progId);
            url.searchParams.set('judge_num', this.activeJudgeNum);
            window.location.href = url.toString();
        },
        calculateGrade(score) {
            let s = parseFloat(score);
            if (isNaN(s) || s <= 0) return '-';
            if (s >= 90) return 'A+';
            if (s >= 80) return 'A';
            if (s >= 70) return 'B';
            if (s >= 60) return 'C';
            return '-';
        },
        saveSingleScore(entryId) {
            let scoreInput = document.getElementById('score_input_' + entryId);
            let gradeInput = document.getElementById('grade_input_' + entryId);
            let remarksInput = document.getElementById('remarks_input_' + entryId);
            
            let val = scoreInput ? scoreInput.value : '';
            if (val === '') {
                alert('ദയവായി മാർക്ക് നൽകുക (Please enter a score).');
                return;
            }

            let numVal = parseFloat(val);
            if (isNaN(numVal) || numVal < 0 || numVal > 100) {
                alert('മാർക്ക് 0 നും 100 നും ഇടയിൽ ആയിരിക്കണം (Score must be between 0 and 100).');
                return;
            }

            this.savingEntryId = entryId;
            let self = this;

            fetch('{{ route('custom-judge.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    program_id: this.selectedProgId,
                    entry_id: entryId,
                    judge_num: this.activeJudgeNum,
                    total_score: numVal,
                    grade: gradeInput ? gradeInput.value : '',
                    remarks: remarksInput ? remarksInput.value : ''
                })
            })
            .then(res => res.json())
            .then(data => {
                self.savingEntryId = null;
                if (data.success) {
                    self.showToast(data.message || 'Saved successfully!');
                    
                    // Update matrix badges
                    let j1El = document.getElementById('matrix_j1_' + entryId);
                    let j2El = document.getElementById('matrix_j2_' + entryId);
                    let j3El = document.getElementById('matrix_j3_' + entryId);
                    let avgEl = document.getElementById('matrix_avg_' + entryId);
                    let grEl = document.getElementById('matrix_grade_' + entryId);
                    let statusBtn = document.getElementById('save_btn_' + entryId);

                    if (data.j1_score !== null && j1El) {
                        j1El.textContent = data.j1_score;
                        j1El.className = 'px-2 py-0.5 rounded font-mono font-bold text-xs bg-red-100 text-[#be1e2d] border border-red-200';
                    }
                    if (data.j2_score !== null && j2El) {
                        j2El.textContent = data.j2_score;
                        j2El.className = 'px-2 py-0.5 rounded font-mono font-bold text-xs bg-indigo-100 text-indigo-700 border border-indigo-200';
                    }
                    if (data.j3_score !== null && j3El) {
                        j3El.textContent = data.j3_score;
                        j3El.className = 'px-2 py-0.5 rounded font-mono font-bold text-xs bg-emerald-100 text-emerald-800 border border-emerald-200';
                    }
                    if (data.average_score !== null && avgEl) {
                        avgEl.textContent = data.average_score;
                    }
                    if (data.combined_grade !== null && grEl) {
                        grEl.textContent = data.combined_grade;
                    }

                    if (statusBtn) {
                        statusBtn.innerHTML = '<svg class=\'w-4 h-4 text-emerald-600\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'3\' d=\'M5 13l4 4L19 7\'></path></svg><span class=\'text-emerald-700 font-bold\'>Saved</span>';
                        statusBtn.className = 'px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-300 text-xs font-mono transition flex items-center gap-1.5 shadow-2xs';
                        setTimeout(() => {
                            statusBtn.innerHTML = '<span>Save</span>';
                            statusBtn.className = 'px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-mono font-bold transition flex items-center gap-1 shadow-2xs cursor-pointer';
                        }, 2500);
                    }
                } else {
                    alert(data.message || 'Error saving score.');
                }
            })
            .catch(err => {
                self.savingEntryId = null;
                alert('Network error saving score.');
            });
        }
     }">

    <!-- Success Toast Notification -->
    <div x-show="successToast"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
         class="fixed top-5 left-1/2 -translate-x-1/2 z-50 max-w-md w-[92%] sm:w-full pointer-events-auto shadow-2xl rounded-2xl bg-slate-900 text-white p-4 border-2 border-emerald-500 flex items-center justify-between gap-3"
         style="display: none;">
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-xl bg-emerald-500 text-slate-950 flex items-center justify-center shrink-0 font-bold">
                ✓
            </span>
            <span class="text-xs font-mono text-emerald-200 font-bold" x-text="successToast"></span>
        </div>
        <button type="button" @click="successToast = ''" class="text-slate-400 hover:text-white p-1">
            ✕
        </button>
    </div>

    <!-- Top Universal Adjudication Header -->
    <div class="bg-white rounded-3xl p-5 sm:p-7 border border-slate-200 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-slate-900 text-white text-[10px] font-mono font-bold uppercase tracking-wider">
                    Universal Stage Jury Desk
                </span>
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-mono font-bold">
                    Passcode: {{ $activePin }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                കസ്റ്റം വിധികർത്താവ് പാനൽ (Custom Judge Panel)
            </h1>
            <p class="text-xs text-slate-500 max-w-2xl font-ml leading-relaxed">
                ഈ ഒരൊറ്റ പാനലിൽ നിന്ന് ഏത് മത്സരവും തിരഞ്ഞെടുത്ത് ജഡ്ജ് 1, ജഡ്ജ് 2, ജഡ്ജ് 3 എന്നിങ്ങനെ മാർക്കുകൾ നൽകാം. 3 വിധികർത്താക്കൾ ഒരേസമയം മാർക്ക് നൽകിയാലും അവ കൃത്യമായി സംയോജിപ്പിച്ച് ശരാശരി കണക്കാക്കും.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('custom-judge.logout') }}"
               class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-red-50 hover:text-red-700 text-slate-700 text-xs font-mono font-bold border border-slate-200 transition">
                Sign Out Panel &rarr;
            </a>
        </div>
    </div>

    <!-- Dual Selection Bar: 1. SELECT PROGRAM & 2. SELECT JUDGE 1 / 2 / 3 -->
    <div class="bg-slate-900 text-white rounded-3xl p-5 sm:p-7 border border-slate-800 shadow-xl space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            
            <!-- PROGRAM SELECTOR (7 Cols) -->
            <div class="lg:col-span-7 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-mono font-bold uppercase tracking-wider text-[#f3bd2e] flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#f3bd2e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <span>1. മത്സരം തിരഞ്ഞെടുക്കുക (Select Program):</span>
                    </label>
                    <span class="text-[11px] font-mono text-slate-400 font-semibold">
                        Total: {{ $programs->count() }} Programs
                    </span>
                </div>

                <!-- Custom Rich Select Dropdown -->
                <div class="relative">
                    <select @change="switchProgram($event.target.value)"
                            class="w-full bg-slate-800/90 hover:bg-slate-800 text-white text-sm sm:text-base font-bold py-3.5 pl-4 pr-10 rounded-2xl border-2 border-white/20 focus:border-[#be1e2d] outline-none transition cursor-pointer appearance-none">
                        @foreach($programs as $p)
                            <option value="{{ $p->id }}" {{ $selectedProgram && $selectedProgram->id === $p->id ? 'selected' : '' }} class="bg-slate-900 text-white py-2">
                                [{{ $p->zone?->name ?? 'All Zones' }}] {{ $p->name }} (#{{ $p->id }}) — {{ $p->category?->name ?? 'General' }} {{ $p->status === 'in_progress' ? '● LIVE' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                @if($selectedProgram)
                    <div class="flex flex-wrap items-center gap-2 pt-1 text-[11px] font-mono text-slate-300">
                        <span class="px-2 py-0.5 rounded bg-white/10 text-white font-bold">{{ $selectedProgram->zone?->name ?? 'All Zones' }}</span>
                        <span class="text-slate-500">•</span>
                        <span class="px-2 py-0.5 rounded bg-white/10 text-white font-bold">{{ $selectedProgram->category?->name ?? 'General' }}</span>
                        <span class="text-slate-500">•</span>
                        <span>Stage: <strong class="text-amber-300">{{ $selectedProgram->stage->name ?? 'TBA' }}</strong></span>
                        <span class="text-slate-500">•</span>
                        <span class="px-2 py-0.5 rounded font-bold uppercase
                            {{ $selectedProgram->status === 'in_progress' ? 'bg-red-500/20 text-red-300 border border-red-500/30' : ($selectedProgram->status === 'completed' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-300') }}">
                            {{ $selectedProgram->status }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- JUDGE 1, 2, 3 SELECTOR (5 Cols) -->
            <div class="lg:col-span-5 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-mono font-bold uppercase tracking-wider text-[#f3bd2e] flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#f3bd2e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span>2. വിധികർത്താവ് (Select Judge):</span>
                    </label>
                    <span class="text-[11px] font-mono text-emerald-400 font-bold uppercase" x-text="'Active: Judge ' + activeJudgeNum"></span>
                </div>

                <!-- 3 Tactile Toggle Buttons for Judge 1, Judge 2, Judge 3 -->
                <div class="grid grid-cols-3 gap-2 p-1.5 bg-slate-800/80 rounded-2xl border border-white/10">
                    <!-- Judge 1 Button -->
                    <button type="button" @click="switchJudge(1)"
                            :class="activeJudgeNum === 1 ? 'bg-[#be1e2d] text-white shadow-lg ring-2 ring-red-400/40' : 'bg-slate-900/50 text-slate-300 hover:bg-slate-800 hover:text-white'"
                            class="py-3 px-2 rounded-xl font-mono font-black text-sm uppercase transition flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                        <span>Judge 1</span>
                        <span class="text-[9px] font-normal opacity-80">Seat 01</span>
                    </button>

                    <!-- Judge 2 Button -->
                    <button type="button" @click="switchJudge(2)"
                            :class="activeJudgeNum === 2 ? 'bg-indigo-600 text-white shadow-lg ring-2 ring-indigo-400/40' : 'bg-slate-900/50 text-slate-300 hover:bg-slate-800 hover:text-white'"
                            class="py-3 px-2 rounded-xl font-mono font-black text-sm uppercase transition flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                        <span>Judge 2</span>
                        <span class="text-[9px] font-normal opacity-80">Seat 02</span>
                    </button>

                    <!-- Judge 3 Button -->
                    <button type="button" @click="switchJudge(3)"
                            :class="activeJudgeNum === 3 ? 'bg-emerald-600 text-white shadow-lg ring-2 ring-emerald-400/40' : 'bg-slate-900/50 text-slate-300 hover:bg-slate-800 hover:text-white'"
                            class="py-3 px-2 rounded-xl font-mono font-black text-sm uppercase transition flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                        <span>Judge 3</span>
                        <span class="text-[9px] font-normal opacity-80">Seat 03</span>
                    </button>
                </div>

                <div class="text-[11px] font-mono text-slate-400 pt-0.5">
                    നിങ്ങൾ ഇപ്പോൾ മാർക്ക് നൽകുന്നത്: <strong class="text-white font-bold" x-text="'Judge ' + activeJudgeNum"></strong>
                </div>
            </div>

        </div>
    </div>

    @if(!$selectedProgram)
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200">
            <h3 class="text-lg font-bold text-slate-800">മത്സരങ്ങൾ ലഭ്യമല്ല</h3>
            <p class="text-xs text-slate-500 font-mono mt-1">ഡാറ്റാബേസിൽ മത്സരങ്ങൾ ലഭ്യമായിട്ടില്ല.</p>
        </div>
    @else

        <!-- Active Evaluation Console Card -->
        <div class="bg-white rounded-3xl p-5 sm:p-7 border border-slate-200/90 shadow-xs space-y-6">
            
            <!-- Program Header Banner & Mode Indicator -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold uppercase
                            {{ $judgeNum === 1 ? 'bg-red-50 text-[#be1e2d] border border-red-200' : ($judgeNum === 2 ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200') }}">
                            Scoring as: Judge {{ $judgeNum }}
                        </span>
                        <span class="text-xs font-mono text-slate-400">
                            • Max Score: 100 PTS
                        </span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        {{ $selectedProgram->name }}
                    </h2>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs font-mono text-slate-500 font-bold">
                        മത്സരാർത്ഥികൾ: <strong class="text-slate-900">{{ $entries->count() }}</strong>
                    </span>
                    <span class="text-slate-300">|</span>
                    <span class="text-xs font-mono text-emerald-700 font-bold">
                        ഹാജരുള്ളവർ: <strong class="text-emerald-900">{{ $entries->where('attendance_status', 'present')->count() }}</strong>
                    </span>
                </div>
            </div>

            <!-- Participant List / Evaluation Rows -->
            <div class="space-y-4">
                @forelse($entries as $index => $entry)
                    @php
                        $entrySheets = $scoreSheetsByEntry->get($entry->id, collect());
                        $activeSheet = $entrySheets->firstWhere('judge_id', $activeJudge->id);
                        $j1Sheet = $entrySheets->firstWhere('judge_id', $customJudges[1]->id);
                        $j2Sheet = $entrySheets->firstWhere('judge_id', $customJudges[2]->id);
                        $j3Sheet = $entrySheets->firstWhere('judge_id', $customJudges[3]->id);
                        $submittedSheets = $entrySheets->where('is_submitted', true);
                        $avgScore = $submittedSheets->isNotEmpty() ? round((float) $submittedSheets->avg('total_score'), 2) : 0.0;
                        $combGrade = \App\Services\PointCalculationService::getGradeFromScore($avgScore)['grade'] ?? '-';
                        $initialScore = $activeSheet ? $activeSheet->total_score : '';
                        $initialGrade = $activeSheet && isset($activeSheet->criteria_scores['grade']) ? $activeSheet->criteria_scores['grade'] : '';
                        $initialRemarks = $activeSheet ? $activeSheet->remarks : '';
                    @endphp

                    <div class="p-4 sm:p-5 rounded-2xl border transition-all duration-200
                        {{ $entry->attendance_status === 'absent' ? 'bg-slate-100/70 border-slate-200 opacity-60' : 'bg-slate-50/70 hover:bg-white hover:border-slate-300 border-slate-200' }}"
                         id="entry_card_{{ $entry->id }}">
                        
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            
                            <!-- Left: Candidate Identity & Chest Code -->
                            <div class="flex items-start sm:items-center gap-3.5 min-w-0 lg:w-1/3">
                                <!-- Big Code Badge -->
                                <div class="w-13 h-13 rounded-2xl bg-slate-900 text-white font-mono font-black text-lg flex flex-col items-center justify-center shrink-0 shadow-2xs">
                                    <span class="text-[9px] uppercase tracking-wider text-[#f3bd2e]">CODE</span>
                                    <span>{{ $entry->code_letter ?: $entry->chest_number ?: ('#' . $entry->id) }}</span>
                                </div>

                                <div class="min-w-0 space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-slate-900 text-base truncate">
                                            {{ $entry->student?->name ?? 'Candidate' }}
                                        </h3>
                                        <span class="text-[11px] font-mono font-bold text-slate-500 bg-slate-200/80 px-2 py-0.5 rounded">
                                            Chest #{{ $entry->chest_number ?: '---' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs font-mono text-slate-500">
                                        <span>{{ $entry->student?->group?->name ?? $entry->group?->name ?? 'Independent' }}</span>
                                        <span>•</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                            {{ $entry->attendance_status === 'present' ? 'bg-emerald-100 text-emerald-800' : ($entry->attendance_status === 'absent' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ $entry->attendance_status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Middle: Active Judge Input (Score, Grade, Remarks) -->
                            <div class="flex flex-wrap sm:flex-nowrap items-center gap-3 lg:w-5/12">
                                <!-- Score Input (0-100) -->
                                <div class="w-28 sm:w-32 shrink-0">
                                    <label class="block text-[10px] font-mono uppercase tracking-wider text-slate-500 font-bold mb-1">
                                        J{{ $judgeNum }} Marks:
                                    </label>
                                    <div class="relative">
                                        <input type="number" step="0.1" min="0" max="100"
                                               id="score_input_{{ $entry->id }}"
                                               value="{{ $initialScore }}"
                                               placeholder="0 - 100"
                                               class="w-full pl-3 pr-8 py-2.5 rounded-xl bg-white border-2 border-slate-300 focus:border-[#be1e2d] text-slate-900 font-mono font-black text-lg text-right focus:outline-none transition">
                                        <span class="absolute right-2.5 top-3 text-[10px] font-mono font-bold text-slate-400">
                                            PTS
                                        </span>
                                    </div>
                                </div>

                                <!-- Grade Input -->
                                <div class="w-24 shrink-0">
                                    <label class="block text-[10px] font-mono uppercase tracking-wider text-slate-500 font-bold mb-1">
                                        Grade:
                                    </label>
                                    <select id="grade_input_{{ $entry->id }}"
                                            class="w-full py-2.5 px-2 rounded-xl bg-white border-2 border-slate-300 focus:border-[#be1e2d] text-slate-900 font-mono font-bold text-sm focus:outline-none transition cursor-pointer">
                                        <option value="">Auto</option>
                                        <option value="A+" {{ $initialGrade === 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A" {{ $initialGrade === 'A' ? 'selected' : '' }}>A</option>
                                        <option value="B" {{ $initialGrade === 'B' ? 'selected' : '' }}>B</option>
                                        <option value="C" {{ $initialGrade === 'C' ? 'selected' : '' }}>C</option>
                                    </select>
                                </div>

                                <!-- Remarks -->
                                <div class="flex-1 min-w-[130px]">
                                    <label class="block text-[10px] font-mono uppercase tracking-wider text-slate-500 font-bold mb-1">
                                        Remarks:
                                    </label>
                                    <input type="text"
                                           id="remarks_input_{{ $entry->id }}"
                                           value="{{ $initialRemarks }}"
                                           placeholder="Notes (optional)"
                                           class="w-full px-3 py-2.5 rounded-xl bg-white border border-slate-300 focus:border-[#be1e2d] text-slate-900 font-mono text-xs focus:outline-none transition">
                                </div>

                                <!-- Save Button -->
                                <div class="self-end pb-0.5">
                                    <button type="button"
                                            id="save_btn_{{ $entry->id }}"
                                            @click="saveSingleScore({{ $entry->id }})"
                                            :disabled="savingEntryId === {{ $entry->id }}"
                                            class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-mono font-bold transition flex items-center gap-1 shadow-2xs cursor-pointer">
                                        <template x-if="savingEntryId === {{ $entry->id }}">
                                            <span>Saving...</span>
                                        </template>
                                        <template x-if="savingEntryId !== {{ $entry->id }}">
                                            <span>Save</span>
                                        </template>
                                    </button>
                                </div>
                            </div>

                            <!-- Right: Live Comparison Matrix Across 3 Judges -->
                            <div class="lg:w-1/4 p-3 bg-white rounded-xl border border-slate-200/90 shadow-2xs space-y-1.5 shrink-0">
                                <div class="text-[10px] font-mono text-slate-400 font-bold uppercase tracking-wider flex items-center justify-between">
                                    <span>All Judges Score:</span>
                                    <span class="text-emerald-700 font-black" id="matrix_grade_{{ $entry->id }}">
                                        Grade {{ $combGrade }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-1 text-xs font-mono">
                                    <!-- J1 -->
                                    <div class="text-center flex-1">
                                        <span class="text-[9px] text-slate-400 block">J1</span>
                                        <span id="matrix_j1_{{ $entry->id }}"
                                              class="px-1.5 py-0.5 rounded font-bold text-xs {{ $j1Sheet ? 'bg-red-100 text-[#be1e2d] border border-red-200' : 'bg-slate-100 text-slate-400' }}">
                                            {{ $j1Sheet ? $j1Sheet->total_score : '-' }}
                                        </span>
                                    </div>

                                    <!-- J2 -->
                                    <div class="text-center flex-1">
                                        <span class="text-[9px] text-slate-400 block">J2</span>
                                        <span id="matrix_j2_{{ $entry->id }}"
                                              class="px-1.5 py-0.5 rounded font-bold text-xs {{ $j2Sheet ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-400' }}">
                                            {{ $j2Sheet ? $j2Sheet->total_score : '-' }}
                                        </span>
                                    </div>

                                    <!-- J3 -->
                                    <div class="text-center flex-1">
                                        <span class="text-[9px] text-slate-400 block">J3</span>
                                        <span id="matrix_j3_{{ $entry->id }}"
                                              class="px-1.5 py-0.5 rounded font-bold text-xs {{ $j3Sheet ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-400' }}">
                                            {{ $j3Sheet ? $j3Sheet->total_score : '-' }}
                                        </span>
                                    </div>

                                    <!-- Final Average -->
                                    <div class="text-center flex-1 pl-1 border-l border-slate-200">
                                        <span class="text-[9px] text-slate-500 font-bold block">AVG</span>
                                        <span id="matrix_avg_{{ $entry->id }}" class="font-black text-slate-900 text-xs">
                                            {{ $avgScore > 0 ? $avgScore : '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-500 font-mono text-xs bg-slate-50 rounded-2xl border border-slate-200">
                        ഈ മത്സരത്തിൽ നിലവിൽ മത്സരാർത്ഥികളില്ല (No verified participants found).
                    </div>
                @endforelse
            </div>

            <!-- Bottom Action Footer -->
            @if($entries->isNotEmpty())
                <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="text-xs font-mono text-slate-500">
                        <span>Active Judge: <strong class="text-slate-900 font-bold">Judge {{ $judgeNum }}</strong></span>
                        <span class="mx-2">•</span>
                        <span>മുകളിൽ നൽകിയ മാർക്കുകൾ ഓരോന്നായി സേവ് ചെയ്യുക.</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="switchJudge(activeJudgeNum === 3 ? 1 : activeJudgeNum + 1)"
                                class="px-5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-xs uppercase transition shadow-2xs cursor-pointer flex items-center gap-1.5">
                            <span>Next Judge (J<span x-text="activeJudgeNum === 3 ? 1 : activeJudgeNum + 1"></span>) &rarr;</span>
                        </button>
                    </div>
                </div>
            @endif

        </div>

        <!-- Live Podium Preview (1st, 2nd, 3rd) -->
        @if($podiumPreview && ($podiumPreview['first'] || $podiumPreview['second'] || $podiumPreview['third']))
            <div class="bg-white rounded-3xl p-5 sm:p-7 border border-slate-200/90 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                        <h3 class="font-bold text-slate-900 text-base">
                            തത്സമയ വിജയികളുടെ നില (Live Podium Standings)
                        </h3>
                    </div>
                    <span class="text-xs font-mono text-slate-400">Calculated from 3 Judges' Average</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 font-mono">
                    <!-- 1st -->
                    <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 text-amber-950 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded bg-amber-400 text-slate-950 font-black text-xs">1ST PLACE</span>
                            <span class="font-bold text-sm">{{ $podiumPreview['first']?->computed_avg_score ?? 0 }} pts</span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900 truncate">
                            {{ $podiumPreview['first']?->student?->name ?? '---' }}
                        </h4>
                        <div class="text-[11px] text-slate-600 flex items-center justify-between">
                            <span>Chest #{{ $podiumPreview['first']?->chest_number ?? '-' }}</span>
                            <span class="font-bold">Grade {{ $podiumPreview['first']?->computed_grade ?? '-' }}</span>
                        </div>
                    </div>

                    <!-- 2nd -->
                    <div class="p-4 rounded-2xl bg-slate-100 border border-slate-200 text-slate-900 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded bg-slate-300 text-slate-900 font-black text-xs">2ND PLACE</span>
                            <span class="font-bold text-sm">{{ $podiumPreview['second']?->computed_avg_score ?? 0 }} pts</span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900 truncate">
                            {{ $podiumPreview['second']?->student?->name ?? '---' }}
                        </h4>
                        <div class="text-[11px] text-slate-600 flex items-center justify-between">
                            <span>Chest #{{ $podiumPreview['second']?->chest_number ?? '-' }}</span>
                            <span class="font-bold">Grade {{ $podiumPreview['second']?->computed_grade ?? '-' }}</span>
                        </div>
                    </div>

                    <!-- 3rd -->
                    <div class="p-4 rounded-2xl bg-orange-50/70 border border-orange-200 text-orange-950 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded bg-orange-400 text-white font-black text-xs">3RD PLACE</span>
                            <span class="font-bold text-sm">{{ $podiumPreview['third']?->computed_avg_score ?? 0 }} pts</span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900 truncate">
                            {{ $podiumPreview['third']?->student?->name ?? '---' }}
                        </h4>
                        <div class="text-[11px] text-slate-600 flex items-center justify-between">
                            <span>Chest #{{ $podiumPreview['third']?->chest_number ?? '-' }}</span>
                            <span class="font-bold">Grade {{ $podiumPreview['third']?->computed_grade ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    @endif

</div>
@endsection
