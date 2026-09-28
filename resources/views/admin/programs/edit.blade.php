@extends('layouts.admin', ['title' => 'Edit Program: ' . $program->name])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.programs.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-1.5 block font-semibold">← Back to Programs</a>
            <h1 class="text-2xl sm:text-3xl font-sora font-black text-slate-900">Edit Program: {{ $program->name }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ $program->code }}</span>
            <span class="px-2.5 py-1 rounded-md text-xs font-bold {{ $program->type === 'group' ? 'bg-[#005c94]/10 text-[#005c94]' : 'bg-[#009444]/10 text-[#009444]' }} uppercase">
                {{ $program->type }}
            </span>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs space-y-1">
            <p class="font-bold">Please correct the following errors:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.programs.update', $program) }}" class="rounded-2xl bg-white border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm">
        @csrf
        @method('PUT')

        <!-- Names & Code -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="sm:col-span-2">
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Program Name (English) <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $program->name) }}" required
                       placeholder="e.g. Arabic Elocution"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Program Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $program->code) }}" required
                       placeholder="e.g. Q9-ARB-01"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 uppercase font-mono focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>
        </div>

        <!-- Malayalam Name -->
        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Alternative / Secondary Name</label>
            <input type="text" name="malayalam_name" value="{{ old('malayalam_name', $program->malayalam_name) }}"
                   placeholder="e.g. Regional title if applicable"
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
        </div>

        <input type="hidden" name="category_id" value="{{ old('category_id', $program->category_id ?? '') }}">

        <!-- Type, Zone & Limit -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Competition Type <span class="text-red-500">*</span></label>
                <select name="type" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
                    <option value="individual" {{ old('type', $program->type) == 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="group" {{ old('type', $program->type) == 'group' ? 'selected' : '' }}>Group</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Zone <span class="text-red-500">*</span></label>
                <select name="zone_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
                    @foreach($zones as $z)
                        @php
                            $zId = is_object($z) ? $z->id : $loop->index;
                            $zName = is_object($z) ? $z->name : $z;
                        @endphp
                        <option value="{{ $zId }}" {{ old('zone_id', $program->zone_id) == $zId ? 'selected' : '' }}>{{ $zName }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Participant Limit</label>
                <input type="number" name="participant_count" value="{{ old('participant_count', $program->participant_count ?? 2) }}" min="1" max="50"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>
        </div>

        <!-- Venue, Duration & Points Multiplier -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Stage Venue</label>
                <select name="stage_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
                    <option value="">-- Off-Stage / Unassigned --</option>
                    @foreach($stages as $stg)
                        <option value="{{ $stg->id }}" {{ old('stage_id', $program->stage_id) == $stg->id ? 'selected' : '' }}>
                            {{ $stg->name }} ({{ $stg->location ?? 'Campus' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-mono uppercase text-slate-600 font-bold">Duration (Min)</label>
                    <label class="flex items-center gap-1.5 cursor-pointer text-[11px] font-mono text-slate-500">
                        <input type="checkbox" name="has_time_limit" value="1" id="admin_edit_has_time_limit" {{ old('has_time_limit', $program->has_time_limit) ? 'checked' : '' }}
                               onchange="document.getElementById('admin_edit_duration').disabled = !this.checked;"
                               class="rounded border-slate-300 text-[#be1e2d] focus:ring-[#be1e2d]">
                        <span>Time Limit</span>
                    </label>
                </div>
                <input type="number" id="admin_edit_duration" name="duration_minutes" value="{{ old('duration_minutes', $program->duration_minutes) }}" min="1"
                       {{ !old('has_time_limit', $program->has_time_limit) ? 'disabled' : '' }}
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors disabled:opacity-40">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Points Weight <span class="text-red-500">*</span></label>
                <input type="number" step="0.5" min="0.5" max="10" name="points_weight" value="{{ old('points_weight', $program->points_weight) }}" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
            </div>
        </div>

        <!-- Evaluation Mode -->
        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
            <div>
                <span class="text-xs font-mono font-bold text-slate-900 block">Scoring Criteria</span>
                <span class="text-[11px] font-mono text-slate-500">Enable criteria breakdown or use direct total mark only (100 Marks).</span>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="has_criteria" value="1" {{ old('has_criteria', $program->has_criteria) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-slate-300 text-[#be1e2d] focus:ring-[#be1e2d]">
                <span class="text-xs font-mono font-bold text-slate-700">Has Criteria Breakdown</span>
            </label>
        </div>

        <!-- Stage Event -->
        <div class="pt-2 border-t border-slate-100">
            <div class="flex items-center gap-3 pt-2">
                <input type="checkbox" id="is_stage" name="is_stage" value="1" {{ old('is_stage', $program->is_stage) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-slate-300 text-[#be1e2d] focus:ring-[#be1e2d]">
                <label for="is_stage" class="text-xs font-mono font-bold text-slate-700 cursor-pointer">
                    Is Stage Competition
                </label>
            </div>
        </div>

        <!-- Rules & Guidelines -->
        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Rules & Evaluation Guidelines</label>
            <textarea name="rules" rows="4" 
                      placeholder="Specify judging criteria, time rules, materials permitted, etc."
                      class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">{{ old('rules', $program->rules) }}</textarea>
        </div>

        <!-- Status -->
        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Competition Status</label>
            <select name="status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
                <option value="upcoming" {{ old('status', $program->status) == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="in_progress" {{ old('status', $program->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ old('status', $program->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ old('status', $program->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 flex items-center justify-between border-t border-slate-100">
            <button type="submit" form="delete-form" class="text-xs font-mono text-red-600 hover:text-red-700 hover:underline">
                Delete Program
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.programs.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800 transition-colors">Cancel</a>
                <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#be1e2d] text-white hover:bg-[#a01624] shadow-md shadow-[#be1e2d]/20 transition-all">
                    Update Program
                </button>
            </div>
        </div>
    </form>

    <form id="delete-form" method="POST" action="{{ route('admin.programs.destroy', $program) }}" onsubmit="return confirm('Are you sure you want to delete this program?');" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
