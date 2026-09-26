@extends('layouts.admin', ['title' => 'Create Program'])

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.programs.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to Programs</a>
        <h1 class="text-3xl font-serif font-black text-slate-900">Create Program</h1>
    </div>

    <form method="POST" action="{{ route('admin.programs.store') }}" class="rounded-2xl bg-white border border-slate-200 p-8 space-y-6 shadow-sm">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Program Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Arabic Speech"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Program Code</label>
                <input type="text" name="code" value="{{ old('code') }}" required placeholder="e.g. Q9-ARB-01"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
        </div>

        <!-- Alternative Name -->
        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Alternative / Secondary Name</label>
            <input type="text" name="malayalam_name" value="{{ old('malayalam_name') }}"
                   placeholder="e.g. Regional title if applicable"
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <input type="hidden" name="category_id" value="{{ old('category_id', $categories->first()?->id ?? '') }}">

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Type</label>
                <select name="type" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="individual" {{ old('type') == 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="group" {{ old('type') == 'group' ? 'selected' : '' }}>Group</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Zone</label>
                <select name="zone_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    @foreach($zones as $z)
                        @php
                            $zId = is_object($z) ? $z->id : $loop->index;
                            $zName = is_object($z) ? $z->name : $z;
                        @endphp
                        <option value="{{ $zId }}" {{ old('zone_id', 1) == $zId ? 'selected' : '' }}>{{ $zName }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Participant Limit</label>
                <input type="number" name="participant_count" value="{{ old('participant_count', 2) }}" min="1" max="50"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Stage Venue</label>
                <select name="stage_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="">-- Unassigned --</option>
                    @foreach($stages as $stg)
                        <option value="{{ $stg->id }}" {{ old('stage_id') == $stg->id ? 'selected' : '' }}>{{ $stg->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Duration (Minutes)</label>
                <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 30) }}" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Points Multiplier</label>
                <input type="number" step="0.5" name="points_weight" value="{{ old('points_weight', 1.0) }}" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Rules & Evaluation Guidelines</label>
            <textarea name="rules" rows="3" placeholder="Specify performance timing, allowed props, and rules..."
                      class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">{{ old('rules') }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Initial Status</label>
            <select name="status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="upcoming" {{ old('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
            <a href="{{ route('admin.programs.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
            <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#f3bd2e] text-white hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                Create Program
            </button>
        </div>
    </form>
</div>
@endsection
