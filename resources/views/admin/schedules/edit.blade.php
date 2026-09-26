@extends('layouts.admin', ['title' => 'Edit Schedule: ' . $schedule->program->name])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.schedules.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to Schedule</a>
        <h1 class="text-3xl font-serif font-black text-slate-900">Edit Schedule: {{ $schedule->program->name }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}" class="rounded-2xl bg-white border border-slate-200 p-8 space-y-6 shadow-sm">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Stage Venue</label>
            <select name="stage_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                @foreach($stages as $stg)
                    <option value="{{ $stg->id }}" {{ old('stage_id', $schedule->stage_id) == $stg->id ? 'selected' : '' }}>
                        {{ $stg->name }} ({{ $stg->code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Start Time</label>
                <input type="datetime-local" name="start_time" value="{{ old('start_time', $schedule->start_time?->format('Y-m-d\TH:i')) }}" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">End Time</label>
                <input type="datetime-local" name="end_time" value="{{ old('end_time', $schedule->end_time?->format('Y-m-d\TH:i')) }}" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Status</label>
            <select name="status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="scheduled" {{ old('status', $schedule->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="ongoing" {{ old('status', $schedule->status) == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ old('status', $schedule->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="delayed" {{ old('status', $schedule->status) == 'delayed' ? 'selected' : '' }}>Delayed</option>
            </select>
        </div>

        <div class="pt-4 flex items-center justify-between border-t border-slate-100">
            <button type="submit" form="delete-form" class="text-xs font-mono text-red-600 hover:underline">
                Remove from Schedule
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.schedules.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
                <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#f3bd2e] text-white hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                    Update Schedule
                </button>
            </div>
        </div>
    </form>

    <form id="delete-form" method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" onsubmit="return confirm('Remove schedule?');" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
