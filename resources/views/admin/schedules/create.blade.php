@php
    $isProgramCommittee = (isset($isProgramCommittee) && $isProgramCommittee)
        || request()->routeIs('program-committee.*')
        || request()->is('program-committee/*')
        || (auth()->check() && in_array(auth()->user()->role, ['program_committee', 'program_coordinator']) && !auth()->user()->isAdmin());
    $layout = $layout ?? ($isProgramCommittee ? 'layouts.program-committee' : 'layouts.admin');
    $routePrefix = $routePrefix ?? ($isProgramCommittee ? 'program-committee.schedules.' : 'admin.schedules.');
@endphp
@extends($layout, ['title' => 'Schedule Event'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route($routePrefix . 'index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to Schedule</a>
        <h1 class="text-3xl font-sora font-black text-slate-900">Schedule Event</h1>
        <p class="text-xs font-mono text-slate-500 mt-1">Assign program to a stage and time window. The conflict engine automatically verifies participant and judge availability.</p>
    </div>

    <form method="POST" action="{{ route($routePrefix . 'store') }}" class="rounded-2xl bg-white border border-slate-200 p-8 space-y-6 shadow-sm">
        @csrf

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Select Program</label>
            <select name="program_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="">-- Choose Unscheduled Event --</option>
                @foreach($programs as $p)
                    <option value="{{ $p->id }}" data-duration="{{ $p->duration_minutes ?: 30 }}" {{ old('program_id') == $p->id ? 'selected' : '' }}>
                        [{{ $p->code }}] {{ $p->name }} ({{ $p->duration_minutes ?: 30 }}m)
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Stage Venue</label>
            <select name="stage_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="">-- Choose Stage --</option>
                @foreach($stages as $stg)
                    <option value="{{ $stg->id }}" {{ old('stage_id') == $stg->id ? 'selected' : '' }}>
                        {{ $stg->name }} ({{ $stg->code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Start Time</label>
                <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">End Time (Auto-calculated from duration)</label>
                <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Schedule Status</label>
            <select name="status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="ongoing" {{ old('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="delayed" {{ old('status') == 'delayed' ? 'selected' : '' }}>Delayed</option>
            </select>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
            <a href="{{ route($routePrefix . 'index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
            <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#f3bd2e] text-white hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                Confirm Schedule & Check Clashes
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const progSelect = document.querySelector('select[name="program_id"]');
    const startInput = document.querySelector('input[name="start_time"]');
    const endInput = document.querySelector('input[name="end_time"]');

    function updateEndTime() {
        if (!progSelect || !startInput || !endInput) return;
        const selectedOpt = progSelect.options[progSelect.selectedIndex];
        if (!selectedOpt) return;
        const duration = parseInt(selectedOpt.dataset.duration || 30, 10);
        if (startInput.value) {
            const startDate = new Date(startInput.value);
            if (!isNaN(startDate.getTime())) {
                const endDate = new Date(startDate.getTime() + duration * 60000);
                const pad = n => String(n).padStart(2, '0');
                const formatted = `${endDate.getFullYear()}-${pad(endDate.getMonth() + 1)}-${pad(endDate.getDate())}T${pad(endDate.getHours())}:${pad(endDate.getMinutes())}`;
                endInput.value = formatted;
            }
        }
    }

    if (progSelect) progSelect.addEventListener('change', updateEndTime);
    if (startInput) startInput.addEventListener('change', updateEndTime);
});
</script>
@endsection
