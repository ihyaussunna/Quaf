@extends('layouts.admin', ['title' => 'Enter Result: ' . $program->name])

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="mb-6">
        <a href="{{ route('admin.results.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">&larr; Back to Results</a>
        <div class="flex items-center gap-2 mb-1">
            <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-amber-50 text-[#f3bd2e] border border-[#f3bd2e]/30">{{ $program->code }}</span>
            <span class="text-xs font-mono text-slate-500">{{ $program->eligibility ?? 'A Zone' }} &bull; {{ ucfirst($program->type) }}</span>
        </div>
        <h1 class="text-3xl font-sora font-black text-slate-900">Record Verdict: {{ $program->name }}</h1>
    </div>

    @if(!empty($podium['first']))
        <!-- Auto-Determined Podium Banner -->
        <div class="rounded-2xl bg-linear-to-r from-amber-50 via-white to-slate-50 border border-amber-200 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-amber-100 text-amber-800 uppercase tracking-wider">
                        Auto-Determined from Judge Scores
                    </span>
                    <h3 class="font-sora font-bold text-base text-slate-900 mt-1">Official Podium Ranking</h3>
                </div>
                <span class="text-xs font-mono text-slate-500">1st, 2nd, and 3rd automatically computed</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- 1st Place Card -->
                <div class="p-4 rounded-xl bg-white border-2 border-amber-300 shadow-xs relative overflow-hidden">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#f3bd2e] text-slate-950 uppercase">1st Place</span>
                        <span class="text-xs font-mono font-bold text-emerald-700">{{ $podium['first']->computed_avg_score }} pts</span>
                    </div>
                    <div class="font-sora font-bold text-sm text-slate-900 truncate">
                        {{ $podium['first']->student?->name ?? $podium['first']->group?->name }}
                    </div>
                    <div class="text-xs font-mono text-slate-500 truncate mt-0.5">
                        Chest #{{ $podium['first']->chest_number }} &bull; {{ $podium['first']->group?->name }}
                    </div>
                    <div class="mt-2 text-[11px] font-mono font-bold text-emerald-800">
                        Grade: {{ $podium['first']->computed_grade ?? 'None' }}
                    </div>
                </div>

                <!-- 2nd Place Card -->
                @if(!empty($podium['second']))
                    <div class="p-4 rounded-xl bg-white border border-slate-300 shadow-xs">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-200 text-slate-800 uppercase">2nd Place</span>
                            <span class="text-xs font-mono font-bold text-emerald-700">{{ $podium['second']->computed_avg_score }} pts</span>
                        </div>
                        <div class="font-sora font-bold text-sm text-slate-900 truncate">
                            {{ $podium['second']->student?->name ?? $podium['second']->group?->name }}
                        </div>
                        <div class="text-xs font-mono text-slate-500 truncate mt-0.5">
                            Chest #{{ $podium['second']->chest_number }} &bull; {{ $podium['second']->group?->name }}
                        </div>
                        <div class="mt-2 text-[11px] font-mono font-bold text-emerald-800">
                            Grade: {{ $podium['second']->computed_grade ?? 'None' }}
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center flex flex-col justify-center text-xs font-mono text-slate-400">
                        No 2nd place candidate
                    </div>
                @endif

                <!-- 3rd Place Card -->
                @if(!empty($podium['third']))
                    <div class="p-4 rounded-xl bg-white border border-amber-200/80 shadow-xs">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-900 uppercase">3rd Place</span>
                            <span class="text-xs font-mono font-bold text-emerald-700">{{ $podium['third']->computed_avg_score }} pts</span>
                        </div>
                        <div class="font-sora font-bold text-sm text-slate-900 truncate">
                            {{ $podium['third']->student?->name ?? $podium['third']->group?->name }}
                        </div>
                        <div class="text-xs font-mono text-slate-500 truncate mt-0.5">
                            Chest #{{ $podium['third']->chest_number }} &bull; {{ $podium['third']->group?->name }}
                        </div>
                        <div class="mt-2 text-[11px] font-mono font-bold text-emerald-800">
                            Grade: {{ $podium['third']->computed_grade ?? 'None' }}
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center flex flex-col justify-center text-xs font-mono text-slate-400">
                        No 3rd place candidate
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Judge Score Sheets Reference -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-xs">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="font-sora font-bold text-base text-slate-900">Contestants & Judge Evaluations</h3>
                <p class="text-xs font-mono text-slate-500">Evaluated marks and auto-computed grades (A+, A, B, C)</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Rank</th>
                        <th class="px-6 py-3 font-semibold">Chest #</th>
                        <th class="px-6 py-3 font-semibold">Participant</th>
                        <th class="px-6 py-3 font-semibold">Group</th>
                        <th class="px-6 py-3 font-semibold">Average Score</th>
                        <th class="px-6 py-3 font-semibold">Grade</th>
                        <th class="px-6 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($podium['ranked'] ?? [] as $idx => $entry)
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $idx === 0 ? 'bg-amber-50/40' : '' }}">
                            <td class="px-6 py-3 font-bold">
                                @if($idx === 0)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#f3bd2e] text-slate-950">1st</span>
                                @elseif($idx === 1)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-300 text-slate-900">2nd</span>
                                @elseif($idx === 2)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-900">3rd</span>
                                @else
                                    <span class="text-slate-400 font-normal">{{ $idx + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 font-bold text-[#f3bd2e]">{{ $entry->chest_number }}</td>
                            <td class="px-6 py-3 font-medium text-slate-900">{{ $entry->student?->name ?? $entry->group?->name }}</td>
                            <td class="px-6 py-3">{{ $entry->group?->name }}</td>
                            <td class="px-6 py-3 font-bold text-emerald-700">
                                {{ $entry->computed_avg_score > 0 ? $entry->computed_avg_score . ' pts' : 'Pending' }}
                            </td>
                            <td class="px-6 py-3">
                                @if($entry->computed_grade)
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-bold {{ in_array($entry->computed_grade, ['A+', 'A']) ? 'bg-emerald-100 text-emerald-800' : ($entry->computed_grade === 'B' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $entry->computed_grade }}
                                    </span>
                                @else
                                    <span class="text-slate-300">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-slate-500">
                                {{ ucfirst($entry->attendance_status) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400 font-mono">
                                No evaluated participants recorded yet.
                            </td>
                        </tr>
                    @endforelse
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
                    <option value="{{ $entry->id }}" {{ (string) old('first_entry_id', $podium['first']?->id ?? '') === (string) $entry->id ? 'selected' : '' }}>
                        Chest #{{ $entry->chest_number }} &mdash; {{ $entry->student?->name ?? 'Group Team' }} ({{ $entry->group?->name }})
                        @if(!empty($podium['first']) && $podium['first']->id === $entry->id)
                            [AUTO 1ST - {{ $podium['first']->computed_avg_score }} pts]
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-700 mb-1.5 font-bold">2nd Place</label>
            <select name="second_entry_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="">-- Choose 2nd Place Entry --</option>
                @foreach($program->entries as $entry)
                    <option value="{{ $entry->id }}" {{ (string) old('second_entry_id', $podium['second']?->id ?? '') === (string) $entry->id ? 'selected' : '' }}>
                        Chest #{{ $entry->chest_number }} &mdash; {{ $entry->student?->name ?? 'Group Team' }} ({{ $entry->group?->name }})
                        @if(!empty($podium['second']) && $podium['second']->id === $entry->id)
                            [AUTO 2ND - {{ $podium['second']->computed_avg_score }} pts]
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-amber-700 mb-1.5 font-bold">3rd Place</label>
            <select name="third_entry_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="">-- Choose 3rd Place Entry --</option>
                @foreach($program->entries as $entry)
                    <option value="{{ $entry->id }}" {{ (string) old('third_entry_id', $podium['third']?->id ?? '') === (string) $entry->id ? 'selected' : '' }}>
                        Chest #{{ $entry->chest_number }} &mdash; {{ $entry->student?->name ?? 'Group Team' }} ({{ $entry->group?->name }})
                        @if(!empty($podium['third']) && $podium['third']->id === $entry->id)
                            [AUTO 3RD - {{ $podium['third']->computed_avg_score }} pts]
                        @endif
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
