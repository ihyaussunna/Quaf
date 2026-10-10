@extends('layouts.admin', ['title' => 'Evaluation Details: ' . $program->name . ' | FestFloww'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.evaluation-monitor.index') }}" class="text-xs font-mono font-semibold text-slate-500 hover:text-slate-900">
                    &larr; Back to Evaluation Monitor
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-sora">
                {{ $program->name }}
            </h1>
            <p class="text-xs text-slate-500 mt-0.5 font-mono">
                ID: {{ $program->code }} &bull; {{ $program->zone?->name ?? $program->eligibility ?? 'All Zones' }} &bull; Stage: {{ $program->stage->name ?? 'TBA' }} &bull; Duration: {{ $program->duration_minutes }} Mins
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.mark-entry.show', $program->id) }}"
               class="px-4 py-2 rounded-xl bg-[#be1e2d] text-white font-bold text-xs uppercase hover:bg-[#a01624] transition-colors shadow-2xs flex items-center gap-1.5 font-sora">
                <span>Mark Entry / Declare Result &rarr;</span>
            </a>
        </div>
    </div>

    <!-- Assigned Judges & Evaluation Rubric Summary Card -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Assigned Jury Panel -->
        <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-2xs space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 font-sora flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    <span>Assigned Jury Panel ({{ $judges->count() }})</span>
                </h3>
                <span class="text-[10px] font-mono text-slate-400">Multiple Judge Enabled</span>
            </div>
            <div class="space-y-2">
                @forelse($judges as $judge)
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs font-sora">
                        <div>
                            <span class="font-bold text-slate-900 block">{{ $judge->name }}</span>
                            <span class="text-[10px] font-mono text-slate-500">{{ $judge->designation ?? 'Adjudicator' }} &bull; {{ $judge->specialization ?? 'Specialist' }}</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl bg-purple-50 text-purple-700 font-mono text-[10px] font-bold border border-purple-200">
                            Jury ID #{{ $judge->id }}
                        </span>
                    </div>
                @empty
                    <div class="p-4 text-center text-slate-400 font-mono text-xs">
                        No judges assigned to this program yet.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Rubric & Criteria -->
        <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-2xs space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 font-sora">
                    Evaluation Rubric ({{ $program->scoringCriteria->count() }} Criteria)
                </h3>
                <span class="text-[10px] font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                    Max: {{ $program->scoringCriteria->sum('max_marks') ?: 100 }} Pts
                </span>
            </div>
            <div class="space-y-2 max-h-48 overflow-y-auto">
                @forelse($program->scoringCriteria as $crit)
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs font-mono">
                        <span class="text-slate-800 font-medium">{{ $crit->criterion_name }}</span>
                        <span class="font-bold text-slate-900">{{ $crit->max_marks }} Marks</span>
                    </div>
                @empty
                    <div class="p-4 text-center text-slate-400 font-mono text-xs">
                        Single 100-point total scale (no individual criteria breakdown).
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Candidate Evaluation Matrix -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 font-sora">
                    Candidate Score Breakdown & Multi-Judge Audit
                </h3>
                <p class="text-[11px] text-slate-500 font-mono mt-0.5">
                    Evaluated against actual judge score sheets. Absent candidates are strictly excluded.
                </p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-600 bg-white px-3 py-1 rounded-xl border border-slate-200">
                {{ $entries->count() }} Total Candidates
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sora">
                <thead>
                    <tr class="bg-slate-100/75 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <th class="px-4 py-3 text-center w-12">Rank</th>
                        <th class="px-4 py-3 text-center w-28">Code Letter</th>
                        <th class="px-4 py-3 w-24">Chest #</th>
                        <th class="px-4 py-3">Student Name</th>
                        <th class="px-4 py-3">Team / Group</th>
                        <th class="px-4 py-3 text-center w-28">Attendance</th>
                        <!-- Dynamic Columns for each Judge -->
                        @foreach($judges as $judge)
                            <th class="px-4 py-3 text-center min-w-32 bg-purple-50/50">
                                {{ $judge->name }}
                            </th>
                        @endforeach
                        <th class="px-4 py-3 text-center w-28 bg-emerald-50/60 text-emerald-900 font-black">Average</th>
                        <th class="px-4 py-3 text-center w-20">Grade</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($entries as $index => $entry)
                        @php
                            $isPresent = $entry->attendance_status === 'present';
                        @endphp
                        <tr class="hover:bg-slate-50/75 transition-colors {{ ! $isPresent ? 'opacity-50 bg-red-50/10' : '' }}">
                            <td class="px-4 py-3 text-center font-mono font-bold text-slate-400">
                                @if($isPresent && $entry->average_score > 0)
                                    @if($index === 0)
                                        <span class="w-6 h-6 rounded-full bg-amber-400 text-white font-black inline-flex items-center justify-center text-xs shadow-xs">1</span>
                                    @elseif($index === 1)
                                        <span class="w-6 h-6 rounded-full bg-slate-400 text-white font-black inline-flex items-center justify-center text-xs shadow-xs">2</span>
                                    @elseif($index === 2)
                                        <span class="w-6 h-6 rounded-full bg-amber-700 text-white font-black inline-flex items-center justify-center text-xs shadow-xs">3</span>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-black text-purple-900">
                                <span class="px-2.5 py-1 rounded-xl bg-purple-100 border border-purple-200">
                                    {{ $entry->code_letter ? 'Code ' . $entry->code_letter : 'None' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono font-bold text-slate-900">
                                #{{ $entry->chest_number }}
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-900">
                                {{ $entry->student?->name ?? 'Participant' }}
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-600">
                                {{ $entry->student?->group?->name ?? $entry->group?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-mono font-bold uppercase
                                      {{ $entry->attendance_status === 'present' ? 'bg-emerald-100 text-emerald-800' : ($entry->attendance_status === 'absent' ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-600') }}">
                                    {{ strtoupper($entry->attendance_status ?? 'waiting') }}
                                </span>
                            </td>

                            <!-- Per-Judge Score Columns -->
                            @foreach($judges as $judge)
                                @php
                                    $sheet = $entry->scoreSheets->where('judge_id', $judge->id)->first();
                                    $isSub = $sheet && $sheet->is_submitted;
                                @endphp
                                <td class="px-4 py-3 text-center font-mono text-xs">
                                    @if(! $isPresent)
                                        <span class="text-slate-300 italic text-[10px]">Excluded</span>
                                    @elseif($isSub)
                                        <span class="font-bold text-slate-900">{{ number_format($sheet->total_score, 1) }}</span>
                                        <span class="text-[9px] text-emerald-600 block">&check; Saved</span>
                                    @else
                                        <span class="text-amber-500 font-bold text-[10px]">Pending</span>
                                    @endif
                                </td>
                            @endforeach

                            <!-- Average Score -->
                            <td class="px-4 py-3 text-center font-mono font-black text-sm bg-emerald-50/40 text-emerald-950">
                                @if($isPresent && $entry->submitted_score_count > 0)
                                    {{ number_format($entry->average_score, 2) }}
                                @else
                                    <span class="text-slate-400 font-normal">-</span>
                                @endif
                            </td>

                            <!-- Grade -->
                            <td class="px-4 py-3 text-center font-mono font-bold text-xs">
                                @if($isPresent && $entry->submitted_score_count > 0 && $entry->grade !== '-')
                                    <span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $entry->grade }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 8 + $judges->count() }}" class="p-12 text-center text-slate-400 font-mono text-xs">
                                No participants registered in this program.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
