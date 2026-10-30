@extends('layouts.judge', ['title' => 'My Evaluation Schedule'])

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Top Action & Navigation Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('judge.dashboard') }}" 
               class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border border-slate-200 text-xs font-mono font-bold flex items-center gap-1.5 shadow-xs transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Dashboard</span>
            </a>
            <div class="h-4 w-px bg-slate-300"></div>
            <span class="text-xs font-mono uppercase tracking-wider text-slate-500 font-bold">Jury Timetable & Schedule</span>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button onclick="window.print()" 
                    class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border border-slate-200 text-xs font-mono font-bold flex items-center gap-1.5 shadow-xs transition-all cursor-pointer">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Schedule</span>
            </button>
            <a href="{{ route('schedule.stage-pdf') }}" target="_blank"
               class="px-3.5 py-2 rounded-xl bg-[#005c94] hover:bg-[#004875] text-white text-xs font-mono font-bold flex items-center gap-1.5 shadow-xs transition-all">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Full Stage PDF</span>
            </a>
        </div>
    </div>

    <!-- Judge Schedule Header Profile -->
    <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-sm">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-[#005c94] text-xs font-mono font-bold">
                <svg class="w-3.5 h-3.5 text-[#005c94]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>OFFICIAL EVALUATION SCHEDULE</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-sora font-black text-slate-900 tracking-tight">
                {{ $judge->name }} — Program Schedule
            </h1>
            <p class="text-xs font-mono text-slate-600">
                Designation: <span class="text-slate-900 font-bold">{{ $judge->designation ?? 'Official Judge' }}</span>
                @if($judge->specialization)
                    <span class="mx-2 text-slate-300">•</span>
                    <span class="text-slate-500">Focus: {{ $judge->specialization }}</span>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3 bg-slate-50 border border-slate-200 px-5 py-4 rounded-2xl shrink-0">
            <div class="text-center">
                <div class="text-3xl font-sora font-black text-slate-900">{{ $assignedPrograms->count() }}</div>
                <div class="text-[10px] font-mono text-slate-500 uppercase font-semibold">Total Programs</div>
            </div>
        </div>
    </div>

    <!-- Schedule List & Timeline -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-sora font-bold text-slate-900">Assigned Programs Timetable (തീയതി & സമയം)</h2>
            <span class="text-xs font-mono text-slate-500 font-semibold">{{ $assignedPrograms->count() }} Competitions</span>
        </div>

        @if($assignedPrograms->isNotEmpty())
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-mono text-slate-500 uppercase tracking-wider">
                                <th class="py-4 px-5 font-bold">Sl</th>
                                <th class="py-4 px-5 font-bold">Time & Date</th>
                                <th class="py-4 px-5 font-bold">Stage / Venue</th>
                                <th class="py-4 px-5 font-bold">Program Name</th>
                                <th class="py-4 px-5 font-bold">Category / Zone</th>
                                <th class="py-4 px-5 font-bold">Duration</th>
                                <th class="py-4 px-5 font-bold">Status</th>
                                <th class="py-4 px-5 font-bold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-mono">
                            @foreach($assignedPrograms as $index => $prog)
                                @php
                                    $schedTime = $prog->scheduled_time ?? $prog->schedule?->start_time;
                                    $stageName = $prog->stage?->name ?: ($prog->schedule?->stage?->name ?: 'Stage Unassigned');
                                    $isLive = ($prog->status === 'in_progress');
                                    $isCompleted = ($prog->status === 'completed' || ($prog->result && in_array($prog->result->status, ['published', 'announced'])));
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors {{ $isLive ? 'bg-rose-50/40' : '' }}">
                                    <!-- Index -->
                                    <td class="py-4 px-5 font-bold text-slate-400">
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </td>

                                    <!-- Time & Date -->
                                    <td class="py-4 px-5 min-w-[170px]">
                                        @if($schedTime)
                                            <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-[#005c94] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>{{ $schedTime->format('h:i A') }}</span>
                                            </div>
                                            <div class="text-[11px] text-slate-500 font-sans mt-0.5">
                                                {{ $schedTime->format('D, d M Y') }}
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-bold">
                                                To Be Announced
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Stage / Venue -->
                                    <td class="py-4 px-5 min-w-[160px]">
                                        <div class="flex items-center gap-1.5 font-bold text-slate-800">
                                            <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span class="font-sans">{{ $stageName }}</span>
                                        </div>
                                    </td>

                                    <!-- Program Name & Code -->
                                    <td class="py-4 px-5 min-w-[220px]">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-700 text-[10px] font-bold">
                                                {{ $prog->code }}
                                            </span>
                                            <span class="font-sora font-bold text-slate-900 text-sm">
                                                {{ $prog->name }}
                                            </span>
                                        </div>
                                        @if($prog->malayalam_name)
                                            <div class="text-[11px] text-slate-500 font-ml mt-0.5">
                                                {{ $prog->malayalam_name }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Category / Zone -->
                                    <td class="py-4 px-5 min-w-[130px]">
                                        @if($prog->zone?->name || $prog->eligibility)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase inline-block"
                                                  style="background-color: {{ $prog->zone?->color_hex ?? '#005c94' }}15; color: {{ $prog->zone?->color_hex ?? '#005c94' }}; border: 1px solid {{ $prog->zone?->color_hex ?? '#005c94' }}30;">
                                                {{ $prog->zone?->name ?? $prog->eligibility }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 font-sans text-xs">-</span>
                                        @endif
                                        <div class="text-[10px] text-slate-400 mt-0.5 uppercase">
                                            {{ ucfirst($prog->type) }}
                                        </div>
                                    </td>

                                    <!-- Duration -->
                                    <td class="py-4 px-5 text-slate-700 font-sans">
                                        {{ $prog->duration_minutes ?: '30' }} Mins
                                    </td>

                                    <!-- Status -->
                                    <td class="py-4 px-5 min-w-[130px]">
                                        @if($isCompleted)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Completed
                                            </span>
                                        @elseif($isLive)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-50 text-[#be1e2d] border border-red-200 text-[10px] font-bold animate-pulse">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#be1e2d]"></span>
                                                Live On Stage
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-bold">
                                                Scheduled
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Action -->
                                    <td class="py-4 px-5 text-right">
                                        <a href="{{ route('judge.evaluate', $prog) }}" 
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold font-mono transition-all {{ $isCompleted ? 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200' : 'bg-[#005c94] hover:bg-[#004875] text-white shadow-xs' }}">
                                            <span>{{ $isCompleted ? 'Review Scores' : 'Evaluate' }}</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="rounded-3xl bg-white border border-slate-200 p-12 text-center shadow-sm space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-sora font-bold text-slate-900">No Programs Assigned Yet</h3>
                    <p class="text-xs font-mono text-slate-500 max-w-md mx-auto">
                        നിങ്ങൾക്ക് ഇതുവരെ പ്രോഗ്രാമുകൾ അസൈൻ ചെയ്തിട്ടില്ല. പ്രോഗ്രാം സമിതി പ്രോഗ്രാമുകൾ അസൈൻ ചെയ്യുമ്പോൾ അവയുടെ തീയതി, സമയം, വേദി വിവരങ്ങൾ ഇവിടെ കൃത്യമായി കാണാവുന്നതാണ്.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('schedule.stage-pdf') }}" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 text-white font-mono font-bold text-xs hover:bg-slate-800 transition-all shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>View Official Stage Schedule PDF</span>
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
