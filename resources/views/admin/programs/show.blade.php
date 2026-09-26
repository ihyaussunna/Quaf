@extends('layouts.admin', ['title' => 'Program: ' . $program->name])

@section('content')
<div class="space-y-8 max-w-6xl mx-auto">
    <div>
        <a href="{{ route('admin.programs.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to Programs</a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-amber-50 text-[#f3bd2e] border border-[#f3bd2e]/30">{{ $program->code }}</span>
                    <span class="text-xs font-mono text-slate-500">{{ $program->eligibility ?? 'A Zone' }} • {{ ucfirst($program->type) }}</span>
                </div>
                <h1 class="text-3xl font-serif font-black text-slate-900">{{ $program->name }}</h1>
            </div>
            <div class="flex items-center gap-3">
                @if(!$program->result)
                    <a href="{{ route('admin.results.create', ['program_id' => $program->id]) }}" class="px-4 py-2.5 rounded-xl bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase tracking-wider hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                        + Enter Verdict / Result
                    </a>
                @else
                    <a href="{{ route('admin.results.edit', $program->result) }}" class="px-4 py-2.5 rounded-xl bg-[#005c94] text-white font-mono font-bold text-xs uppercase tracking-wider hover:brightness-110 shadow-lg shadow-purple-600/20">
                        Edit Official Verdict ({{ $program->result->status }})
                    </a>
                @endif
                <a href="{{ route('admin.programs.edit', $program) }}" class="px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-700 font-mono text-xs font-bold uppercase hover:bg-slate-50 shadow-sm">
                    Edit Program
                </a>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs font-mono">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <span class="text-[10px] text-slate-500 uppercase block mb-1">Assigned Stage</span>
            <span class="text-slate-900 font-bold">{{ $program->stage?->name ?? 'Unassigned' }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <span class="text-[10px] text-slate-500 uppercase block mb-1">Scheduled Time</span>
            <span class="text-slate-900 font-bold">{{ $program->scheduled_time?->format('h:i A, M d') ?? 'Not Scheduled' }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <span class="text-[10px] text-slate-500 uppercase block mb-1">Duration & Weight</span>
            <span class="text-slate-900 font-bold">{{ $program->duration_minutes }}m • {{ $program->points_weight }}x Multiplier</span>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <span class="text-[10px] text-slate-500 uppercase block mb-1">Current Status</span>
            <span class="text-[#f3bd2e] font-bold uppercase">{{ $program->status }}</span>
        </div>
    </div>

    <!-- Scoring Criteria Rubric Section -->
    <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm" x-data="{
        criteria: [
            @foreach($program->scoringCriteria as $c)
                { name: '{{ addslashes($c->criterion_name) }}', max_marks: {{ $c->max_marks }} },
            @endforeach
        ],
        addCriterion() {
            this.criteria.push({ name: '', max_marks: 25 });
        },
        removeCriterion(index) {
            this.criteria.splice(index, 1);
        },
        totalMarks() {
            return this.criteria.reduce((sum, item) => sum + (parseInt(item.max_marks) || 0), 0);
        }
    }">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-serif font-bold text-lg text-slate-900">Scoring Rubric Criteria</h3>
                <p class="text-xs font-mono text-slate-500">Custom rubric parameters used by assigned judges on score sheets</p>
            </div>
            <div class="text-xs font-mono text-slate-600">
                Total Marks: <span class="font-bold text-[#f3bd2e]" x-text="totalMarks()"></span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.programs.criteria.update', $program) }}" class="space-y-4">
            @csrf

            <template x-for="(item, index) in criteria" :key="index">
                <div class="flex items-center gap-3">
                    <input type="text" :name="'criteria[' + index + '][name]'" x-model="item.name" required placeholder="Criterion Name (e.g. Performance)"
                           class="flex-1 bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <div class="flex items-center gap-1 w-32">
                        <input type="number" :name="'criteria[' + index + '][max_marks]'" x-model="item.max_marks" required min="1" max="100"
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                        <span class="text-xs font-mono text-slate-500">pts</span>
                    </div>
                    <button type="button" @click="removeCriterion(index)" class="p-2.5 text-red-600 hover:text-red-700 rounded-lg hover:bg-slate-100">
                        ✕
                    </button>
                </div>
            </template>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="addCriterion()" class="text-xs font-mono text-[#f3bd2e] hover:underline flex items-center gap-1 font-semibold">
                    + Add Criterion
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#f3bd2e] text-white hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                    Save Rubric
                </button>
            </div>
        </form>
    </div>

    <!-- Registered Entries Roster -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-serif font-bold text-lg text-slate-900">Contestants Roster</h3>
            <span class="text-xs font-mono text-slate-500">{{ $program->entries->count() }} Entries</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Chest #</th>
                        <th class="px-6 py-3 font-semibold">Participant</th>
                        <th class="px-6 py-3 font-semibold">Group</th>
                        <th class="px-6 py-3 font-semibold">Verification</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($program->entries as $entry)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-3 font-bold text-[#f3bd2e]">{{ $entry->chest_number }}</td>
                            <td class="px-6 py-3 font-medium text-slate-900">{{ $entry->student?->name ?? 'Group Ensemble' }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold" style="background-color: {{ $entry->group->color_hex }}15; color: {{ $entry->group->color_hex }}">
                                    {{ $entry->group->name }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                @if($entry->status === 'verified')
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold">VERIFIED</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-bold">PENDING</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-400">No participants registered for this event yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
