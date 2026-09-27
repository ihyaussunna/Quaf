@extends('layouts.program-committee', ['title' => 'Edit Program - ' . $program->name])

@section('content')
<div class="max-w-4xl mx-auto space-y-6" 
     x-data="{
         progType: '{{ old('type', $program->type) }}',
         isStage: {{ old('is_stage', $program->is_stage) ? 'true' : 'false' }},
         rulesText: `{{ old('rules', $program->rules) }}`,
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
         }
     }">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('program-committee.programs.show', $program) }}" 
           class="inline-flex items-center gap-1.5 text-xs font-mono font-bold text-slate-500 hover:text-slate-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to {{ $program->name }}</span>
        </a>

        <a href="{{ route('program-committee.programs.rules', $program) }}" 
           class="px-3.5 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-mono font-bold hover:bg-amber-100 transition">
            Dedicated Niyamavali Editor →
        </a>
    </div>

    <!-- Form Container -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-sm space-y-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
            <div>
                <span class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-slate-100 text-slate-700">
                    {{ $program->code }}
                </span>
                <h2 class="text-2xl font-sora font-black text-slate-900 mt-1">Edit Program: {{ $program->name }}</h2>
                <p class="text-xs font-mono text-slate-500 mt-1">
                    Edit competition details and official rules.
                </p>
            </div>
            <div class="text-xs font-mono px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">
                Zone: <strong>{{ $program->zone?->name ?? $program->eligibility }}</strong>
            </div>
        </div>

        <form method="POST" action="{{ route('program-committee.programs.update', $program) }}" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- SECTION 1: BASIC DETAILS -->
            <div class="space-y-4">
                <h3 class="text-xs font-mono uppercase tracking-wider text-brand-burgundy font-bold border-b border-slate-100 pb-2">
                    1. Basic Competition Details
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Code -->
                    <div>
                        <label class="block text-xs font-mono uppercase text-slate-700 font-bold mb-1">
                            Program Code <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="code" value="{{ old('code', $program->code) }}" required 
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-mono text-slate-900 focus:outline-none focus:border-brand-burgundy uppercase">
                    </div>

                    <!-- English Name -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-mono uppercase text-slate-700 font-bold mb-1">
                            Program Name (English) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $program->name) }}" required 
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-brand-burgundy">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Malayalam Name -->
                    <div>
                        <label class="block text-xs font-mono uppercase text-slate-700 font-bold mb-1">
                            Malayalam Name
                        </label>
                        <input type="text" name="malayalam_name" value="{{ old('malayalam_name', $program->malayalam_name) }}" 
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-malayalam text-slate-900 focus:outline-none focus:border-brand-burgundy">
                    </div>

                    <!-- Zone -->
                    <div>
                        <label class="block text-xs font-mono uppercase text-slate-700 font-bold mb-1">
                            Festival Zone <span class="text-red-500">*</span>
                        </label>
                        <select name="zone_id" required 
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-brand-burgundy">
                            @foreach($zones as $z)
                                <option value="{{ $z->id }}" {{ old('zone_id', $program->zone_id) == $z->id ? 'selected' : '' }}>
                                    {{ $z->name }} ({{ $z->description ?: 'Zone' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Hidden Category ID (Zone is used instead of category) -->
                <input type="hidden" name="category_id" value="{{ old('category_id', $program->category_id ?? $categories->first()?->id ?? '') }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Duration (Minutes) -->
                    <div>
                        <label class="block text-xs font-mono uppercase text-slate-700 font-bold mb-1">
                            Duration (Minutes) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $program->duration_minutes) }}" min="1" max="180" required 
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-mono text-slate-900 focus:outline-none focus:border-brand-burgundy">
                    </div>

                    <!-- Points Weight -->
                    <div>
                        <label class="block text-xs font-mono uppercase text-slate-700 font-bold mb-1">
                            Points Weight <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.5" name="points_weight" value="{{ old('points_weight', $program->points_weight) }}" min="0.5" max="20" required 
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-mono text-slate-900 focus:outline-none focus:border-brand-burgundy">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: PARTICIPATION FORMAT & LIMITS -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <h3 class="text-xs font-mono uppercase tracking-wider text-brand-burgundy font-bold border-b border-slate-100 pb-2">
                    2. Participation Format & Limits
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Type Selection -->
                    <div>
                        <label class="block text-xs font-mono uppercase text-slate-700 font-bold mb-1.5">
                            Competition Type <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-300 flex-1 cursor-pointer transition hover:bg-slate-50"
                                   :class="progType === 'individual' ? 'border-brand-burgundy bg-amber-50/30' : ''">
                                <input type="radio" name="type" value="individual" x-model="progType" class="text-brand-burgundy focus:ring-brand-burgundy">
                                <span class="text-xs font-bold text-slate-900">Individual</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-300 flex-1 cursor-pointer transition hover:bg-slate-50"
                                   :class="progType === 'group' ? 'border-brand-burgundy bg-amber-50/30' : ''">
                                <input type="radio" name="type" value="group" x-model="progType" class="text-brand-burgundy focus:ring-brand-burgundy">
                                <span class="text-xs font-bold text-slate-900">Group</span>
                            </label>
                        </div>
                    </div>

                    <!-- Participant Limit (Only for Group) -->
                    <div x-show="progType === 'group'">
                        <label class="block text-xs font-mono uppercase text-slate-700 font-bold mb-1">
                            Team Participant Limit <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="participant_count" value="{{ old('participant_count', $program->participant_count ?? 4) }}" min="2" max="50" 
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-mono text-slate-900 focus:outline-none focus:border-brand-burgundy">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Stage or Off-Stage -->
                    <div class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 bg-slate-50/60">
                        <input type="checkbox" name="is_stage" id="is_stage" value="1" x-model="isStage" 
                               class="w-4 h-4 text-brand-burgundy rounded focus:ring-brand-burgundy">
                        <label for="is_stage" class="text-xs text-slate-900 font-bold cursor-pointer">
                            Stage Competition
                        </label>
                    </div>

                    <!-- Stage Assignment -->
                    <div x-show="isStage">
                        <label class="block text-xs font-mono uppercase text-slate-700 font-bold mb-1">
                            Assigned Stage
                        </label>
                        <select name="stage_id" 
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-brand-burgundy">
                            <option value="">-- Unassigned --</option>
                            @foreach($stages as $stg)
                                <option value="{{ $stg->id }}" {{ old('stage_id', $program->stage_id) == $stg->id ? 'selected' : '' }}>
                                    {{ $stg->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-mono uppercase text-slate-700 font-bold mb-1">
                            Program Status
                        </label>
                        <select name="status" 
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-brand-burgundy">
                            <option value="upcoming" {{ old('status', $program->status) === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="in_progress" {{ old('status', $program->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ old('status', $program->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $program->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: NIYAMAVALI (RULES & GUIDELINES) -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div>
                        <h3 class="text-xs font-mono uppercase tracking-wider text-brand-burgundy font-bold">
                            3. Competition Rules (Niyamavali)
                        </h3>
                        <p class="text-[11px] font-mono text-slate-500">Official instructions for participants and judges.</p>
                    </div>
                </div>

                <div>
                    <textarea name="rules" x-model="rulesText" rows="6" 
                              placeholder="Enter official competition rules, timing instructions, guidelines, etc..."
                              class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-4 text-xs font-mono text-slate-900 focus:outline-none focus:border-brand-burgundy leading-relaxed"></textarea>
                </div>
            </div>

            <!-- SECTION 4: SCORING CRITERIA -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div>
                        <h3 class="text-xs font-mono uppercase tracking-wider text-brand-burgundy font-bold">
                            4. Scoring Criteria & Marks
                        </h3>
                    </div>
                    <div class="text-xs font-mono font-bold px-3 py-1 rounded-xl bg-amber-50 border border-amber-200 text-amber-900">
                        Total Marks: <span x-text="totalMarks"></span>
                    </div>
                </div>

                <div class="space-y-2">
                    <template x-for="(crit, index) in criteria" :key="index">
                        <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="w-6 h-6 rounded-lg bg-slate-200 text-slate-700 text-xs font-mono font-bold flex items-center justify-center flex-shrink-0" x-text="index + 1"></span>
                            
                            <input type="text" :name="'criteria[' + index + '][name]'" x-model="crit.name" required
                                   placeholder="Criterion name"
                                   class="flex-1 bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-900 focus:outline-none focus:border-brand-burgundy">
                            
                            <div class="flex items-center gap-1">
                                <input type="number" :name="'criteria[' + index + '][max_marks]'" x-model.number="crit.max_marks" min="1" max="100" required
                                       class="w-20 bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-mono font-bold text-right text-slate-900 focus:outline-none focus:border-brand-burgundy">
                                <span class="text-xs font-mono text-slate-400">Marks</span>
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
                        class="px-8 py-3 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white text-xs font-mono font-bold uppercase tracking-wider shadow-md shadow-[#be1e2d]/20 transition-all">
                    Update Program Details
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
