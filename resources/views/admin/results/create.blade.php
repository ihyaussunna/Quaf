@extends('layouts.admin', ['title' => 'Enter Result: ' . $program->name])

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="mb-6">
        <a href="{{ route('admin.results.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to Results</a>
        <div class="flex items-center gap-2 mb-1">
            <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-amber-50 text-[#f3bd2e] border border-[#f3bd2e]/30">{{ $program->code }}</span>
            <span class="text-xs font-mono text-slate-500">{{ $program->eligibility ?? 'A Zone' }} • {{ ucfirst($program->type) }}</span>
        </div>
        <h1 class="text-3xl font-sora font-black text-slate-900">Record Verdict: {{ $program->name }}</h1>
    </div>

    <!-- Judge Score Sheets Reference -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="font-sora font-bold text-base text-slate-900">Contestants & Judge Evaluations</h3>
            <p class="text-xs font-mono text-slate-500">Review total judge scores before establishing official placements</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Chest #</th>
                        <th class="px-6 py-3 font-semibold">Participant</th>
                        <th class="px-6 py-3 font-semibold">Group</th>
                        <th class="px-6 py-3 font-semibold">Judge Score</th>
                        <th class="px-6 py-3 font-semibold">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($program->entries as $entry)
                        @php $score = $entry->scoreSheets->first(); @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-3 font-bold text-[#f3bd2e]">{{ $entry->chest_number }}</td>
                            <td class="px-6 py-3 font-medium text-slate-900">{{ $entry->student?->name ?? 'Group Ensemble' }}</td>
                            <td class="px-6 py-3">{{ $entry->group->name }}</td>
                            <td class="px-6 py-3 font-bold text-emerald-700">
                                {{ $score ? $score->total_score . ' pts' : 'Pending' }}
                            </td>
                            <td class="px-6 py-3 text-slate-500 italic">
                                {{ $score?->remarks ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Placements Form -->
    <form method="POST" action="{{ route('admin.results.store') }}" class="rounded-2xl bg-white border border-slate-200 p-8 space-y-6 shadow-sm">
        @csrf
        <input type="hidden" name="program_id" value="{{ $program->id }}">

        <div>
            <label class="block text-xs font-mono uppercase text-[#f3bd2e] mb-1.5 font-bold">1st Place (Champion)</label>
            <select name="first_entry_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="">-- Choose 1st Place Entry --</option>
                @foreach($program->entries as $entry)
                    <option value="{{ $entry->id }}" {{ old('first_entry_id') == $entry->id ? 'selected' : '' }}>
                        Chest #{{ $entry->chest_number }} — {{ $entry->student?->name ?? 'Group Team' }} ({{ $entry->group->name }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-700 mb-1.5 font-bold">2nd Place</label>
            <select name="second_entry_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="">-- Choose 2nd Place Entry --</option>
                @foreach($program->entries as $entry)
                    <option value="{{ $entry->id }}" {{ old('second_entry_id') == $entry->id ? 'selected' : '' }}>
                        Chest #{{ $entry->chest_number }} — {{ $entry->student?->name ?? 'Group Team' }} ({{ $entry->group->name }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-amber-700 mb-1.5 font-bold">3rd Place</label>
            <select name="third_entry_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="">-- Choose 3rd Place Entry --</option>
                @foreach($program->entries as $entry)
                    <option value="{{ $entry->id }}" {{ old('third_entry_id') == $entry->id ? 'selected' : '' }}>
                        Chest #{{ $entry->chest_number }} — {{ $entry->student?->name ?? 'Group Team' }} ({{ $entry->group->name }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Workflow Status</label>
            <select name="status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="published" {{ old('status', 'published') == 'published' ? 'selected' : '' }}>Published (Live on Public Portal + Auto Certificates + Calculate Points)</option>
                <option value="verified" {{ old('status') == 'verified' ? 'selected' : '' }}>Verified (Awaiting Publishing)</option>
                <option value="under_review" {{ old('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Jury Remarks / Official Notes</label>
            <textarea name="remarks" rows="2" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">{{ old('remarks') }}</textarea>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
            <a href="{{ route('admin.results.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
            <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#f3bd2e] text-white hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                Confirm & Save Verdict
            </button>
        </div>
    </form>
</div>
@endsection
