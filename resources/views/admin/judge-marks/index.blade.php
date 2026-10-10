@extends('layouts.admin', ['title' => 'Judge Marks Dashboard | FestFloww'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></span>
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-blue-700">Audit & Adjudication Trail</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-sora">
                Judge Marks Dashboard
            </h1>
            <p class="text-xs text-slate-500 mt-0.5 font-sora">
                Comprehensive log of all submitted score sheets, breakdown by criteria, and individual judge marks.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.call-list.index') }}"
               class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 font-bold text-xs uppercase hover:bg-slate-50 transition-colors shadow-2xs flex items-center gap-1.5 font-sora">
                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Call List Center</span>
            </a>
            <a href="{{ route('admin.evaluation-monitor.index') }}"
               class="px-4 py-2 rounded-xl bg-purple-600 text-white font-bold text-xs uppercase hover:bg-purple-700 transition-colors shadow-2xs flex items-center gap-1.5 font-sora">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Evaluation Monitor</span>
            </a>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-2xs">
            <span class="text-slate-500 font-mono block text-[11px] uppercase font-bold">Total Score Sheets</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block font-mono">{{ number_format($scoreSheets->total()) }}</span>
            <span class="text-[10px] text-slate-400 font-mono">Submitted Evaluations</span>
        </div>
        <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 text-emerald-900 shadow-2xs">
            <span class="font-mono block text-emerald-700 text-[11px] uppercase font-bold">Active Programs</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($programs->count()) }}</span>
            <span class="text-[10px] text-emerald-600 font-mono">Assigned in Festival</span>
        </div>
        <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200 text-blue-900 shadow-2xs">
            <span class="font-mono block text-blue-700 text-[11px] uppercase font-bold">Empaneled Judges</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($judges->count()) }}</span>
            <span class="text-[10px] text-blue-600 font-mono">Active Adjudicators</span>
        </div>
        <div class="p-4 rounded-2xl bg-purple-50/70 border border-purple-200 text-purple-900 shadow-2xs">
            <span class="font-mono block text-purple-700 text-[11px] uppercase font-bold">Current Page Items</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($scoreSheets->count()) }}</span>
            <span class="text-[10px] text-purple-600 font-mono">Showing on this view</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs">
        <form method="GET" action="{{ route('admin.judge-marks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1">Search Keywords</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Student name, chest, code, judge..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Program</label>
                <select name="program_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Programs</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}" {{ (string)$programId === (string)$p->id ? 'selected' : '' }}>
                            {{ $p->code }} - {{ \Illuminate\Support\Str::limit($p->name, 22) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Judge</label>
                <select name="judge_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Judges</option>
                    @foreach($judges as $j)
                        <option value="{{ $j->id }}" {{ (string)$judgeId === (string)$j->id ? 'selected' : '' }}>
                            {{ $j->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Stage</label>
                <select name="stage_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Stages</option>
                    @foreach($stages as $st)
                        <option value="{{ $st->id }}" {{ (string)$stageId === (string)$st->id ? 'selected' : '' }}>
                            {{ $st->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5 font-sora">
                    <span>Apply</span>
                </button>
                <a href="{{ route('admin.judge-marks.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition text-center">
                    <span>Reset</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Judge Marks Table -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm font-sora">
                Submitted Evaluation Sheets
            </h2>
            <span class="text-xs text-slate-400 font-mono">
                Showing {{ $scoreSheets->firstItem() ?? 0 }} to {{ $scoreSheets->lastItem() ?? 0 }} of {{ $scoreSheets->total() }} records
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px] font-mono">
                        <th class="py-3 px-4">Time & ID</th>
                        <th class="py-3 px-4">Program</th>
                        <th class="py-3 px-4">Participant</th>
                        <th class="py-3 px-4">Judge</th>
                        <th class="py-3 px-4">Criteria Breakdown</th>
                        <th class="py-3 px-4 text-center">Total Score</th>
                        <th class="py-3 px-4">Remarks</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($scoreSheets as $sheet)
                        @php
                            $entry = $sheet->entry;
                            $program = $sheet->program;
                            $judge = $sheet->judge;
                            $criteriaMap = $program && $program->scoringCriteria ? $program->scoringCriteria->keyBy('id') : collect();
                            $gradeData = \App\Services\PointCalculationService::getGradeFromScore($sheet->total_score);
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- Time & ID -->
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                <span class="font-bold text-slate-800">#{{ $sheet->id }}</span>
                                <div class="text-[10px] text-slate-400">
                                    {{ $sheet->submitted_at ? $sheet->submitted_at->format('d M Y, h:i A') : $sheet->created_at->format('d M Y, h:i A') }}
                                </div>
                            </td>

                            <!-- Program -->
                            <td class="py-3 px-4 min-w-[200px]">
                                @if($program)
                                    <div class="font-bold text-slate-900 font-sora">
                                        {{ $program->name }}
                                    </div>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-0.5 font-mono text-[10px]">
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-bold">{{ $program->code }}</span>
                                        @if($program->zone || $program->eligibility)
                                            <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700">{{ $program->zone?->name ?? $program->eligibility }}</span>
                                        @endif
                                        @if($program->stage)
                                            <span class="px-1.5 py-0.5 rounded bg-purple-50 text-purple-700">{{ $program->stage->name }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Program N/A</span>
                                @endif
                            </td>

                            <!-- Participant -->
                            <td class="py-3 px-4 min-w-[190px]">
                                @if($entry)
                                    <div class="flex items-center gap-2">
                                        @if($entry->code_letter)
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-600 text-white font-mono font-black text-xs shadow-2xs">
                                                {{ $entry->code_letter }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-200 text-slate-600 font-mono text-xs">
                                                -
                                            </span>
                                        @endif
                                        <div>
                                            <div class="font-bold text-slate-900 font-sora">
                                                {{ $entry->student->name ?? 'Participant' }}
                                            </div>
                                            <div class="flex items-center gap-1.5 font-mono text-[10px] text-slate-500">
                                                <span>Chest #{{ $entry->chest_number }}</span>
                                                @if($entry->group)
                                                    <span>&bull;</span>
                                                    <span class="text-indigo-600 font-semibold">{{ $entry->group->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Entry removed</span>
                                @endif
                            </td>

                            <!-- Judge -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($judge)
                                    <div class="font-bold text-slate-800">
                                        {{ $judge->name }}
                                    </div>
                                    <div class="text-[10px] font-mono text-slate-400">
                                        Judge ID: {{ $judge->id }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Anonymous / System</span>
                                @endif
                            </td>

                            <!-- Criteria Breakdown -->
                            <td class="py-3 px-4 min-w-[240px]">
                                @if(!empty($sheet->criteria_scores) && is_array($sheet->criteria_scores))
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($sheet->criteria_scores as $cId => $score)
                                            @php
                                                $criterion = $criteriaMap->get($cId);
                                                $cName = $criterion ? $criterion->name : "Criterion {$cId}";
                                                $maxScore = $criterion ? $criterion->max_marks : null;
                                            @endphp
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-100 border border-slate-200 text-[10px] font-mono text-slate-700">
                                                <span class="text-slate-500 font-medium">{{ \Illuminate\Support\Str::limit($cName, 14) }}:</span>
                                                <span class="font-bold text-slate-900">{{ $score }}{{ $maxScore ? "/{$maxScore}" : '' }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px] italic font-mono">Consolidated Score</span>
                                @endif
                            </td>

                            <!-- Total Score -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="font-mono text-base font-black text-slate-900">
                                    {{ number_format($sheet->total_score, 2) }}
                                </div>
                                @if(!empty($gradeData['grade']) && $gradeData['grade'] !== '-')
                                    <span class="inline-block mt-0.5 px-2 py-0.2 rounded-md bg-purple-100 text-purple-800 text-[10px] font-mono font-bold">
                                        Grade {{ $gradeData['grade'] }}
                                    </span>
                                @endif
                            </td>

                            <!-- Remarks -->
                            <td class="py-3 px-4 max-w-[180px] truncate text-[11px] text-slate-500">
                                {{ $sheet->remarks ?: '-' }}
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                @if($program)
                                    <a href="{{ route('admin.evaluation-monitor.show', $program) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] font-sora transition">
                                        <span>Program Matrix</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 font-mono">
                                No submitted judge score sheets match the specified criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($scoreSheets->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $scoreSheets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
