@extends('layouts.program-committee', ['title' => 'Niyamavali Editor - ' . $program->name])

@section('content')
<div class="max-w-4xl mx-auto space-y-6"
     x-data="{
         rulesText: `{{ old('rules', $program->rules) }}`,
         durationMinutes: {{ old('duration_minutes', $program->duration_minutes ?? 5) }},
         criteria: {{ json_encode($program->scoringCriteria->count() > 0 ? $program->scoringCriteria->map(fn($c) => ['name' => $c->criterion_name, 'max_marks' => $c->max_marks])->toArray() : [
             ['name' => 'Presentation & Delivery', 'max_marks' => 30],
             ['name' => 'Content & Knowledge', 'max_marks' => 30],
             ['name' => 'Style & Expression', 'max_marks' => 25],
             ['name' => 'Time Adherence', 'max_marks' => 15]
         ]) }},
         addCriterion() {
             this.criteria.push({ name: '', max_marks: 20 });
         },
         removeCriterion(index) {
             if (this.criteria.length > 1) {
                 this.criteria.splice(index, 1);
             }
         },
         get totalMarks() {
             return this.criteria.reduce((sum, c) => sum + (parseInt(c.max_marks) || 0), 0);
         },
         applyTemplate(type) {
             if (type === 'speech') {
                 this.rulesText = `1. Topic: Provided 30 minutes prior to competition.\n2. Time limit: 5 minutes (First warning bell at 4th minute, final bell at 5th minute).\n3. Reading from notes or papers is strictly prohibited.\n4. Evaluation: Presentation, delivery, content, language purity, and time adherence.`;
                 this.criteria = [
                     { name: 'Content & Knowledge', max_marks: 35 },
                     { name: 'Language & Voice Modulation', max_marks: 25 },
                     { name: 'Delivery & Style', max_marks: 25 },
                     { name: 'Time Adherence', max_marks: 15 }
                 ];
                 this.durationMinutes = 5;
             } else if (type === 'quran') {
                 this.rulesText = `1. Recite the designated portion from the selected Surahs.\n2. Strictly follow Tajweed rules (Idgham, Iqlab, Madd, Waqf, etc.).\n3. Time limit: 5 minutes.\n4. Melody, articulation, and authentic recitation style will be evaluated.`;
                 this.criteria = [
                     { name: 'Tajweed Rules', max_marks: 40 },
                     { name: 'Makhraj & Sifaat', max_marks: 30 },
                     { name: 'Melody & Style', max_marks: 20 },
                     { name: 'Time Adherence', max_marks: 10 }
                 ];
                 this.durationMinutes = 5;
             } else if (type === 'song') {
                 this.rulesText = `1. Lyrics must uphold ethical and moral values.\n2. Musical instruments are not permitted.\n3. Time limit: 6 minutes.\n4. Melody, pitch, and expressive delivery will be evaluated.`;
                 this.criteria = [
                     { name: 'Lyric Value & Expression', max_marks: 30 },
                     { name: 'Rhythm & Melody', max_marks: 30 },
                     { name: 'Vocal Clarity & Pitch', max_marks: 25 },
                     { name: 'Overall Presentation', max_marks: 15 }
                 ];
                 this.durationMinutes = 6;
             } else if (type === 'essay') {
                 this.rulesText = `1. Topic will be announced in the competition hall.\n2. Write only on designated answer sheets.\n3. Time limit: 60 minutes.\n4. Clarity of ideas, grammar, factual accuracy, and conclusion will be evaluated.`;
                 this.criteria = [
                     { name: 'Content & Factual Accuracy', max_marks: 40 },
                     { name: 'Language & Grammar', max_marks: 30 },
                     { name: 'Clarity of Thought & Structure', max_marks: 20 },
                     { name: 'Handwriting & Presentation', max_marks: 10 }
                 ];
                 this.durationMinutes = 60;
             }
         }
     }">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('program-committee.programs.show', $program) }}" 
           class="inline-flex items-center gap-1.5 text-xs font-mono font-bold text-slate-500 hover:text-slate-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to {{ $program->name }}</span>
        </a>

        <div class="flex items-center gap-2">
            @if(!empty($program->rules))
                <a href="{{ route('program-committee.programs.rules.print', $program) }}" target="_blank"
                   class="px-3.5 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-mono font-bold hover:bg-slate-50 transition">
                    Print Rules Sheet
                </a>
            @endif
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-sm space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-slate-100 pb-5">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-2.5 py-0.5 rounded-lg bg-amber-400 text-slate-950 font-mono font-bold text-xs">
                        {{ $program->code }}
                    </span>
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-mono text-xs">
                        {{ $program->zone?->name ?? $program->eligibility }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-xs font-mono capitalize {{ $program->type === 'group' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $program->type }}
                    </span>
                </div>
                <h1 class="text-2xl font-sora font-black text-slate-900">{{ $program->name }}</h1>
                @if($program->malayalam_name)
                    <h2 class="text-lg font-malayalam font-bold text-slate-600">{{ $program->malayalam_name }}</h2>
                @endif
            </div>

            <!-- Template Shortcuts -->
            <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 text-right">
                <span class="text-[10px] font-mono text-slate-400 uppercase font-bold block mb-1.5">Auto-fill Templates</span>
                <div class="flex items-center gap-1.5 flex-wrap justify-end">
                    <button type="button" @click="applyTemplate('speech')" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:border-amber-400 hover:bg-amber-50 text-[11px] font-malayalam font-bold text-slate-700 transition">Speech</button>
                    <button type="button" @click="applyTemplate('quran')" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:border-amber-400 hover:bg-amber-50 text-[11px] font-malayalam font-bold text-slate-700 transition">Qur'an</button>
                    <button type="button" @click="applyTemplate('song')" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:border-amber-400 hover:bg-amber-50 text-[11px] font-malayalam font-bold text-slate-700 transition">Song</button>
                    <button type="button" @click="applyTemplate('essay')" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:border-amber-400 hover:bg-amber-50 text-[11px] font-malayalam font-bold text-slate-700 transition">Essay</button>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('program-committee.programs.rules.update', $program) }}" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- SECTION 1: DURATION & TIME RULES -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-mono uppercase text-slate-700 font-bold mb-1">
                        Time Duration (Minutes) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="duration_minutes" x-model="durationMinutes" min="1" max="180" required 
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-mono text-slate-900 focus:outline-none focus:border-brand-burgundy">
                    <span class="text-[10px] text-slate-400 font-mono">Total allocated performance/exam time</span>
                </div>

                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs font-mono text-amber-900 flex items-center">
                    <span>
                        <strong>Bell System Guidelines:</strong> Warning bell sounds 1 minute before completion, followed by final bell.
                    </span>
                </div>
            </div>

            <!-- SECTION 2: NIYAMAVALI RULES TEXTAREA -->
            <div class="space-y-2">
                <label class="block text-xs font-sora uppercase text-slate-700 font-bold">
                    Official Competition Rules (Niyamavali) <span class="text-red-500">*</span>
                </label>
                <textarea name="rules" x-model="rulesText" rows="9" required
                          placeholder="Enter official competition rules, timing instructions, guidelines, etc..."
                          class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-4 text-slate-900 focus:outline-none focus:border-brand-burgundy leading-relaxed"
                          :class="/[\u0D00-\u0D7F]/.test(rulesText) ? 'font-anek text-sm' : 'font-sora text-xs'"></textarea>
                <p class="text-[11px] font-sora text-slate-400">
                    * Use numbered points (1., 2., 3.) for better readability.
                </p>
            </div>

            <!-- SECTION 3: SCORING & EVALUATION CRITERIA -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div>
                        <h3 class="text-xs font-sora uppercase tracking-wider text-brand-burgundy font-bold">
                            Evaluation Criteria & Marks Breakdown
                        </h3>
                        <p class="text-[11px] font-sora text-slate-500">Criteria and score distribution for the judging panel.</p>
                    </div>
                    <div class="text-xs font-sora font-bold px-3 py-1 rounded-xl bg-amber-50 border border-amber-200 text-amber-900">
                        Total Marks: <span class="font-rockwell font-bold" x-text="totalMarks"></span>
                    </div>
                </div>

                <div class="space-y-2.5">
                    <template x-for="(crit, index) in criteria" :key="index">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="w-6 h-6 rounded-lg bg-slate-200 text-slate-700 text-xs font-rockwell font-bold flex items-center justify-center flex-shrink-0" x-text="index + 1"></span>
                            
                            <input type="text" :name="'criteria[' + index + '][name]'" x-model="crit.name" required
                                   placeholder="Criterion name (e.g. Presentation & Delivery)"
                                   class="flex-1 bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-900 focus:outline-none focus:border-brand-burgundy transition-all"
                                   :class="/[\u0D00-\u0D7F]/.test(crit.name) ? 'font-anek text-sm font-semibold' : 'font-sora text-xs'">
                            
                            <div class="flex items-center gap-1">
                                <input type="number" :name="'criteria[' + index + '][max_marks]'" x-model.number="crit.max_marks" min="1" max="100" required
                                       class="w-20 bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-rockwell font-bold text-right text-slate-900 focus:outline-none focus:border-brand-burgundy">
                                <span class="text-xs font-sora text-slate-400">Marks</span>
                            </div>

                            <button type="button" @click="removeCriterion(index)" 
                                    class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Remove">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <button type="button" @click="addCriterion()" 
                        class="px-4 py-2 rounded-xl border border-dashed border-slate-300 text-slate-600 hover:text-brand-burgundy hover:border-brand-burgundy text-xs font-mono font-bold flex items-center gap-1.5 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Add Another Criterion</span>
                </button>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('program-committee.programs.show', $program) }}" 
                   class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-mono font-semibold transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-8 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-mono font-bold uppercase tracking-wider shadow-md transition-all">
                    Save Rules & Criteria
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
