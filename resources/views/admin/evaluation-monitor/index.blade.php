@extends('layouts.admin', ['title' => 'Evaluation Monitor | FestFloww'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500 animate-pulse"></span>
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-purple-700">Jury Adjudication Monitor</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-sora">
                Evaluation Monitor
            </h1>
            <p class="text-xs text-slate-500 mt-0.5 font-sora">
                Real-time tracking of judge evaluation progress across all festival stages and programs.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.call-list.index') }}"
               class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 font-bold text-xs uppercase hover:bg-slate-50 transition-colors shadow-2xs flex items-center gap-1.5 font-sora">
                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Call List Center</span>
            </a>
            <a href="{{ route('admin.judge-marks.index') }}"
               class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs uppercase hover:bg-slate-800 transition-colors shadow-2xs flex items-center gap-1.5 font-sora">
                <span>View All Judge Marks &rarr;</span>
            </a>
        </div>
    </div>

    <!-- Section 11: 6 Macro Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-2xs">
            <span class="text-slate-500 font-mono block text-[11px] uppercase font-bold">Total Programs</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block font-mono">{{ number_format($stats['total_programs']) }}</span>
            <span class="text-[10px] text-slate-400 font-mono">Festival Competitions</span>
        </div>
        <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 text-emerald-900 shadow-2xs">
            <span class="font-mono block text-emerald-700 text-[11px] uppercase font-bold">Completed Events</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($stats['completed_programs']) }}</span>
            <span class="text-[10px] text-emerald-600 font-mono">Published / Declared</span>
        </div>
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 shadow-2xs">
            <span class="text-slate-500 font-mono block text-[11px] uppercase font-bold">Total Participants</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block font-mono">{{ number_format($stats['total_participants']) }}</span>
            <span class="text-[10px] text-slate-400 font-mono">Across All Events</span>
        </div>
        <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 text-emerald-900 shadow-2xs">
            <span class="font-mono block text-emerald-700 text-[11px] uppercase font-bold">Present Participants</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($stats['present_participants']) }}</span>
            <span class="text-[10px] text-emerald-600 font-mono">Eligible for Scoring</span>
        </div>
        <div class="p-4 rounded-2xl bg-purple-50/70 border border-purple-200 text-purple-900 shadow-2xs">
            <span class="font-mono block text-purple-700 text-[11px] uppercase font-bold">Evaluated</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($stats['evaluated_participants']) }}</span>
            <span class="text-[10px] text-purple-600 font-mono">Marks Submitted</span>
        </div>
        <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 text-amber-900 shadow-2xs">
            <span class="font-mono block text-amber-700 text-[11px] uppercase font-bold">Pending Evaluations</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($stats['pending_evaluations']) }}</span>
            <span class="text-[10px] text-amber-600 font-mono">Awaiting Jury Scores</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs">
        <form method="GET" action="{{ route('admin.evaluation-monitor.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1">Search Program</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Program name or code..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Zone</label>
                <select name="zone" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($zones as $zKey => $zVal)
                        @php
                            $zName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                            $zValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                        @endphp
                        <option value="{{ $zValue }}" {{ ($zone ?? '') == $zValue ? 'selected' : '' }}>{{ $zName }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Stage</label>
                <select name="stage_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Stages</option>
                    @foreach($stages as $s)
                        <option value="{{ $s->id }}" {{ (string)$stageId === (string)$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5 font-sora">
                    <span>Filter</span>
                </button>
                <a href="{{ route('admin.evaluation-monitor.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Section 11: Program-wise Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 font-sora">
                    Program-Wise Evaluation Progress
                </h3>
                <p class="text-[11px] text-slate-500 font-mono mt-0.5">
                    Click any program to inspect detailed candidate marks, individual judge sheets, and ranking.
                </p>
            </div>
            <div class="text-xs font-mono text-slate-500">
                {{ $programs->total() }} Programs Found
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sora">
                <thead>
                    <tr class="bg-slate-100/75 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <th class="px-4 py-3">Program</th>
                        <th class="px-4 py-3">Stage / Venue</th>
                        <th class="px-4 py-3 text-center w-20">Total</th>
                        <th class="px-4 py-3 text-center w-20">Present</th>
                        <th class="px-4 py-3 text-center w-20">Absent</th>
                        <th class="px-4 py-3 text-center w-24">Evaluated</th>
                        <th class="px-4 py-3 text-center w-24">Pending</th>
                        <th class="px-4 py-3 text-center w-32">Status</th>
                        <th class="px-4 py-3 text-center w-36">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($programs as $prog)
                        @php
                            $total = $prog->total_entries_count;
                            $present = $prog->present_entries_count;
                            $absent = $prog->absent_entries_count;
                            $evaluated = $prog->evaluated_entries_count;
                            $pending = max(0, $present - $evaluated);
                            $isComplete = ($present > 0 && $evaluated >= $present);
                        @endphp
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.evaluation-monitor.show', $prog->id) }}" class="font-bold text-slate-900 hover:text-[#be1e2d] transition-colors block">
                                    {{ $prog->name }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                    ID: {{ $prog->code }} &bull; {{ $prog->category->name ?? $prog->eligibility ?? 'General' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-700">
                                {{ $prog->stage?->name ?? 'Unassigned' }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-bold text-slate-900">
                                {{ $total }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-bold text-emerald-700 bg-emerald-50/30">
                                {{ $present }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-bold text-red-600 bg-red-50/30">
                                {{ $absent }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-bold text-purple-700 bg-purple-50/30">
                                {{ $evaluated }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-bold text-amber-700 bg-amber-50/30">
                                {{ $pending }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($prog->result?->status === 'published')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-emerald-100 text-emerald-800">
                                        PUBLISHED
                                    </span>
                                @elseif($isComplete)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-purple-100 text-purple-800">
                                        READY FOR RESULT
                                    </span>
                                @elseif($present > 0 && $evaluated > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-blue-100 text-blue-800">
                                        SCORING LIVE
                                    </span>
                                @elseif($present > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-amber-100 text-amber-800">
                                        WAITING JURY
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-slate-100 text-slate-600">
                                        WAITING CALL
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.evaluation-monitor.show', $prog->id) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-[#be1e2d] hover:text-white text-slate-700 text-xs font-bold transition-all shadow-2xs font-sora">
                                    <span>Inspect Details &rarr;</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-12 text-center text-slate-400 font-mono text-xs">
                                No programs found matching your filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($programs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $programs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
