@extends('layouts.admin', ['title' => 'Enter Marks — ' . $program->name])

@section('content')
<div class="space-y-8">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-slate-500 mb-1">
                <a href="{{ route('admin.mark-entry.index') }}" class="hover:text-[#f3bd2e]">← Back to Mark Entry</a>
                <span>/</span>
                <span>{{ $program->code }}</span>
            </div>
            <h1 class="text-3xl font-sora font-black text-slate-900">{{ $program->name }}</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">
                Zone: <span class="text-slate-800 font-bold">{{ $program->eligibility ?? 'A Zone' }}</span> • 
                Venue: <span class="text-slate-800 font-bold">{{ $program->stage?->name ?? 'Off-Stage' }}</span> • 
                Points Weight: <span class="text-[#f3bd2e] font-bold">{{ $program->points_weight }}x</span>
            </p>
        </div>

        <!-- Publication Status Badge -->
        <div>
            @if($program->result && $program->result->is_published)
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Official Result Published
                </span>
            @else
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-mono font-bold bg-amber-50 text-amber-700 border border-amber-300">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Pending Publication
                </span>
            @endif
        </div>
    </div>

    <!-- Section 1: Enter Marks Form -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-sora font-bold text-lg text-slate-900">1. Judge Score Entry</h2>
                <p class="text-xs font-mono text-slate-500">Input evaluated marks for each participant or unit entry.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.mark-entry.save', $program) }}" class="p-6 space-y-6">
            @csrf

            <!-- Judge Selection -->
            <div class="max-w-xs">
                <label class="block text-xs font-mono font-bold uppercase text-slate-600 mb-1">Evaluating Judge / Jury</label>
                <select name="judge_id" class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-all text-slate-900">
                    @foreach($judges as $judge)
                        <option value="{{ $judge->id }}">{{ $judge->name }} ({{ $judge->contact_number ?: 'No phone' }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Entries Table -->
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Chest No</th>
                            <th class="px-4 py-3 font-semibold">Participant / Entry</th>
                            <th class="px-4 py-3 font-semibold">Team / Unit</th>
                            <th class="px-4 py-3 font-semibold w-36">Total Score (0 - 100)</th>
                            <th class="px-4 py-3 font-semibold">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($program->entries as $entry)
                            @php
                                $score = $entry->scores->first()?->total_score ?? '';
                                $remarks = $entry->scores->first()?->remarks ?? '';
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 font-bold text-[#f3bd2e]">
                                    {{ $entry->student?->student_id ?? 'GROUP-'.$entry->id }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-sora font-bold text-sm text-slate-900 block">
                                        {{ $entry->student?->name ?? $entry->group?->name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-slate-800">
                                        {{ $entry->group?->name ?? $entry->student?->group?->name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.1" min="0" max="100"
                                           name="scores[{{ $entry->id }}][total_score]"
                                           value="{{ old('scores.'.$entry->id.'.total_score', $score) }}"
                                           placeholder="0.0"
                                           class="w-full px-3 py-1.5 text-xs font-mono font-bold text-slate-900 rounded-lg bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text"
                                           name="scores[{{ $entry->id }}][remarks]"
                                           value="{{ old('scores.'.$entry->id.'.remarks', $remarks) }}"
                                           placeholder="Optional remarks"
                                           class="w-full px-3 py-1.5 text-xs font-mono text-slate-700 rounded-lg bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                    No participants registered for this competition yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($program->entries->count() > 0)
                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-800 text-white font-mono font-bold text-xs uppercase hover:bg-slate-900 shadow-xs">
                        Save Marks
                    </button>
                </div>
            @endif
        </form>
    </div>

    <!-- Section 2: Publish Official Results & Update Leaderboard -->
    <div class="bg-white border-2 border-[#f3bd2e]/30 rounded-2xl overflow-hidden shadow-xs">
        <div class="px-6 py-4 border-b border-slate-200 bg-amber-50/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-sora font-bold text-lg text-slate-900 flex items-center gap-2">
                    <span>2. Publish Official Verdict & Results</span>
                </h2>
                <p class="text-xs font-mono text-slate-600">
                    1st, 2nd, and 3rd rank winners are auto-calculated from judge marks. <strong class="text-[#f3bd2e]">Leaderboard points update automatically in real-time!</strong>
                </p>
            </div>
        </div>

        @if(!empty($podium['first']))
            <div class="p-6 bg-slate-50/60 border-b border-slate-200">
                <span class="text-[11px] font-mono font-bold text-slate-500 uppercase tracking-wider block mb-2">Auto-Calculated Standings from Judge Marks:</span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="p-3 bg-white rounded-xl border border-amber-300">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#f3bd2e] text-slate-950 uppercase">1st Place</span>
                        <div class="font-bold text-sm text-slate-900 mt-1 truncate">{{ $podium['first']->student?->name ?? $podium['first']->group?->name }}</div>
                        <div class="text-xs font-mono text-slate-500">{{ $podium['first']->computed_avg_score }} pts &bull; Grade {{ $podium['first']->computed_grade ?? 'None' }}</div>
                    </div>
                    @if(!empty($podium['second']))
                        <div class="p-3 bg-white rounded-xl border border-slate-200">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-200 text-slate-800 uppercase">2nd Place</span>
                            <div class="font-bold text-sm text-slate-900 mt-1 truncate">{{ $podium['second']->student?->name ?? $podium['second']->group?->name }}</div>
                            <div class="text-xs font-mono text-slate-500">{{ $podium['second']->computed_avg_score }} pts &bull; Grade {{ $podium['second']->computed_grade ?? 'None' }}</div>
                        </div>
                    @endif
                    @if(!empty($podium['third']))
                        <div class="p-3 bg-white rounded-xl border border-amber-200">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-900 uppercase">3rd Place</span>
                            <div class="font-bold text-sm text-slate-900 mt-1 truncate">{{ $podium['third']->student?->name ?? $podium['third']->group?->name }}</div>
                            <div class="text-xs font-mono text-slate-500">{{ $podium['third']->computed_avg_score }} pts &bull; Grade {{ $podium['third']->computed_grade ?? 'None' }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.mark-entry.publish', $program) }}" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- 1st Place -->
                <div class="p-4 rounded-xl bg-amber-50/50 border border-amber-200 space-y-2">
                    <label class="block text-xs font-mono font-bold text-amber-900 uppercase">
                        1st Place Winner *
                    </label>
                    <select name="first_entry_id" required class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-white border border-amber-300 focus:outline-none focus:border-[#f3bd2e] text-slate-900">
                        <option value="">-- Select Winner --</option>
                        @foreach($program->entries as $entry)
                            @php $isFirst = (string)old('first_entry_id', $program->result?->first_entry_id ?? $podium['first']?->id ?? '') === (string)$entry->id; @endphp
                            <option value="{{ $entry->id }}" {{ $isFirst ? 'selected' : '' }}>
                                {{ $entry->student?->student_id ? '['.$entry->student->student_id.'] ' : '' }}
                                {{ $entry->student?->name ?? $entry->group?->name }} 
                                ({{ $entry->group?->name ?? $entry->student?->group?->name }})
                                @if(!empty($podium['first']) && $podium['first']->id === $entry->id)
                                    [AUTO 1ST - {{ $podium['first']->computed_avg_score }} pts]
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 2nd Place -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <label class="block text-xs font-mono font-bold text-slate-800 uppercase">
                        2nd Place Winner
                    </label>
                    <select name="second_entry_id" class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-white border border-slate-300 focus:outline-none focus:border-[#f3bd2e] text-slate-900">
                        <option value="">-- Select 2nd Place --</option>
                        @foreach($program->entries as $entry)
                            @php $isSecond = (string)old('second_entry_id', $program->result?->second_entry_id ?? $podium['second']?->id ?? '') === (string)$entry->id; @endphp
                            <option value="{{ $entry->id }}" {{ $isSecond ? 'selected' : '' }}>
                                {{ $entry->student?->student_id ? '['.$entry->student->student_id.'] ' : '' }}
                                {{ $entry->student?->name ?? $entry->group?->name }}
                                ({{ $entry->group?->name ?? $entry->student?->group?->name }})
                                @if(!empty($podium['second']) && $podium['second']->id === $entry->id)
                                    [AUTO 2ND - {{ $podium['second']->computed_avg_score }} pts]
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 3rd Place -->
                <div class="p-4 rounded-xl bg-amber-50/30 border border-amber-100 space-y-2">
                    <label class="block text-xs font-mono font-bold text-amber-900 uppercase">
                        3rd Place Winner
                    </label>
                    <select name="third_entry_id" class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-white border border-amber-200 focus:outline-none focus:border-[#f3bd2e] text-slate-900">
                        <option value="">-- Select 3rd Place --</option>
                        @foreach($program->entries as $entry)
                            @php $isThird = (string)old('third_entry_id', $program->result?->third_entry_id ?? $podium['third']?->id ?? '') === (string)$entry->id; @endphp
                            <option value="{{ $entry->id }}" {{ $isThird ? 'selected' : '' }}>
                                {{ $entry->student?->student_id ? '['.$entry->student->student_id.'] ' : '' }}
                                {{ $entry->student?->name ?? $entry->group?->name }}
                                ({{ $entry->group?->name ?? $entry->student?->group?->name }})
                                @if(!empty($podium['third']) && $podium['third']->id === $entry->id)
                                    [AUTO 3RD - {{ $podium['third']->computed_avg_score }} pts]
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Remarks -->
            <div>
                <label class="block text-xs font-mono font-bold uppercase text-slate-600 mb-1">Jury Remarks / Citation</label>
                <textarea name="remarks" rows="2" placeholder="Optional jury citation or notes regarding this verdict..."
                          class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white text-slate-900">{{ old('remarks', $program->result?->remarks) }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-slate-200">
                <div class="text-[11px] font-mono text-slate-500">
                    Publishing will instantly mark this competition as completed and broadcast points across the team leaderboard.
                </div>
                <button type="submit" class="px-6 py-3 rounded-xl bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase tracking-wider hover:brightness-110 shadow-sm">
                    Publish Result & Update Leaderboard
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
