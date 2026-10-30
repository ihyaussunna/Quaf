@extends('layouts.judge', ['title' => 'Assigned Programs'])

@section('content')
<div x-data="{ scheduleModalOpen: false }" class="space-y-8">
    <!-- Judge Hero Profile (Light Theme) -->
    <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden shadow-sm">
        <div class="space-y-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-[#005c94] text-xs font-mono font-bold">
                <svg class="w-3.5 h-3.5 text-[#005c94]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                <span>OFFICIAL JURY DESK</span>
            </div>
            <h1 class="text-3xl font-sora font-bold text-slate-900">Welcome, {{ $judge->name }}</h1>
            <p class="text-xs font-mono text-slate-500">
                Designation: <span class="text-slate-800 font-semibold">{{ $judge->designation ?? 'Official Judge' }}</span>
                @if($judge->specialization)
                    <span class="mx-2 text-slate-300">•</span>
                    <span class="text-slate-500">{{ $judge->specialization }}</span>
                @endif
            </p>

            <!-- Prominent Schedule Action Buttons -->
            <div class="flex items-center gap-2.5 pt-1 flex-wrap">
                <a href="{{ route('judge.schedule') }}" 
                   class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-sora font-bold text-xs flex items-center gap-2 shadow-xs transition-all active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>View My Schedule (ഷെഡ്യൂൾ ടൈംടേബിൾ)</span>
                </a>
                <button type="button" @click="scheduleModalOpen = true" 
                        class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-mono font-bold text-xs flex items-center gap-1.5 border border-slate-200 transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Quick Timetable</span>
                </button>
                <a href="{{ route('schedule.stage-pdf') }}" target="_blank" 
                   class="px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-900 font-mono text-xs flex items-center gap-1.5 border border-slate-200 transition-all">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Stage Schedule PDF</span>
                </a>
            </div>
        </div>

        <div class="flex items-center gap-4 bg-slate-50 border border-slate-200 p-4 rounded-2xl shrink-0">
            <div class="text-center px-4 border-r border-slate-200">
                <div class="text-2xl font-sora font-bold text-slate-900">{{ $assignedPrograms->count() }}</div>
                <div class="text-[10px] font-mono text-slate-500 uppercase font-semibold">Assigned</div>
            </div>
            <div class="text-center px-4 border-r border-slate-200">
                <div class="text-2xl font-sora font-bold text-amber-600">{{ $pendingEvaluationCount + $inProgressCount }}</div>
                <div class="text-[10px] font-mono text-slate-500 uppercase font-semibold">Pending Evaluation</div>
            </div>
            <div class="text-center px-4">
                <div class="text-2xl font-sora font-bold text-emerald-600">{{ $completedCount }}</div>
                <div class="text-[10px] font-mono text-slate-500 uppercase font-semibold">Completed</div>
            </div>
        </div>
    </div>

    @if($liveProgram || $nextProgram)
        <!-- Live & Next Program Focus Cards -->
        <div class="grid grid-cols-1 {{ $liveProgram && $nextProgram ? 'lg:grid-cols-2' : '' }} gap-5">
            @if($liveProgram)
                @php
                    $liveEval = $evaluationStatus[$liveProgram->id] ?? ['total' => 0, 'submitted' => 0, 'present' => 0, 'pending' => 0];
                    $livePercent = $liveEval['total'] > 0 ? round(($liveEval['submitted'] / $liveEval['total']) * 100) : 0;
                    $liveTime = $liveProgram->scheduled_time ?? $liveProgram->schedule?->start_time;
                @endphp
                <div class="rounded-3xl bg-gradient-to-br from-rose-50/80 via-white to-amber-50/30 border-2 border-[#be1e2d] p-6 shadow-md relative overflow-hidden flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#be1e2d] text-white text-[11px] font-mono font-bold uppercase tracking-wider shadow-xs animate-pulse">
                                <span class="w-2 h-2 rounded-full bg-white"></span>
                                <span>Live Program On Stage</span>
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-white text-slate-700 border border-slate-200">
                                {{ $liveProgram->code }}
                            </span>
                        </div>

                        <div>
                            <h3 class="text-xl sm:text-2xl font-sora font-black text-slate-900 leading-tight">
                                {{ $liveProgram->name }}
                            </h3>
                            @if($liveProgram->malayalam_name)
                                <p class="text-xs font-ml text-slate-600 mt-0.5">{{ $liveProgram->malayalam_name }}</p>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs font-mono pt-1 text-slate-700">
                            <div class="p-2.5 rounded-xl bg-white/90 border border-rose-100 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#be1e2d] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="truncate font-bold">{{ $liveProgram->stage->name ?? 'Stage Unassigned' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-white/90 border border-rose-100 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#be1e2d] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="truncate font-bold">{{ $liveTime?->format('h:i A') ?? 'Live Now' }}</span>
                            </div>
                        </div>

                        <div class="p-2.5 rounded-xl bg-white/90 border border-rose-100 text-xs font-mono flex items-center justify-between text-slate-600">
                            <span>Evaluation: <strong class="text-slate-900">{{ $liveEval['submitted'] }}/{{ $liveEval['present'] }} Evaluated</strong></span>
                            <span class="text-rose-700 font-bold">{{ $liveEval['pending'] }} Pending</span>
                        </div>
                    </div>

                    <div class="pt-4 mt-2 border-t border-rose-100">
                        <a href="{{ route('judge.evaluate', $liveProgram) }}" 
                           class="w-full py-3.5 bg-[#be1e2d] hover:bg-[#a01624] text-white font-mono font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Evaluate Live Program Now</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            @endif

            @if($nextProgram)
                @php
                    $nextTime = $nextProgram->scheduled_time ?? $nextProgram->schedule?->start_time;
                @endphp
                <div class="rounded-3xl bg-gradient-to-br from-blue-50/70 via-white to-slate-50 border-2 border-[#005c94]/40 p-6 shadow-md relative overflow-hidden flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 text-[#005c94] text-[11px] font-mono font-bold uppercase tracking-wider border border-blue-200">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                                <span>Next Program Schedule</span>
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-white text-slate-700 border border-slate-200">
                                {{ $nextProgram->code }}
                            </span>
                        </div>

                        <div>
                            <h3 class="text-xl sm:text-2xl font-sora font-black text-slate-900 leading-tight">
                                {{ $nextProgram->name }}
                            </h3>
                            @if($nextProgram->malayalam_name)
                                <p class="text-xs font-ml text-slate-600 mt-0.5">{{ $nextProgram->malayalam_name }}</p>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs font-mono pt-1 text-slate-700">
                            <div class="p-2.5 rounded-xl bg-white/90 border border-blue-100 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#005c94] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="truncate font-bold">{{ $nextProgram->stage->name ?? 'Stage Unassigned' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-white/90 border border-blue-100 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#005c94] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="truncate font-bold">{{ $nextTime?->format('h:i A') ?? 'Upcoming' }}</span>
                            </div>
                        </div>

                        <div class="p-2.5 rounded-xl bg-white/90 border border-blue-100 text-xs font-mono flex items-center justify-between text-slate-600">
                            <span>Zone: <strong class="text-slate-900">{{ $nextProgram->zone?->name ?: $nextProgram->eligibility }}</strong></span>
                            <span>Duration: <strong class="text-slate-900">{{ $nextProgram->duration_minutes }} Mins</strong></span>
                        </div>
                    </div>

                    <div class="pt-4 mt-2 border-t border-blue-100">
                        <a href="{{ route('judge.evaluate', $nextProgram) }}" 
                           class="w-full py-3.5 bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Open Next Evaluation Sheet</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Assigned Programs List (Simple Aligned Downward Boxes) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-sora font-bold text-slate-900">Your Evaluation Schedule</h2>
            <span class="text-xs font-mono text-slate-500 font-semibold">Total: {{ $assignedPrograms->count() }} Events</span>
        </div>

        <div class="space-y-4">
            @forelse($assignedPrograms as $prog)
                @php
                    $eval = $evaluationStatus[$prog->id] ?? ['total' => 0, 'submitted' => 0, 'is_complete' => false, 'present' => 0, 'pending' => 0];
                    $percent = $eval['total'] > 0 ? round(($eval['submitted'] / $eval['total']) * 100) : 0;
                    $isComplete = (bool) ($eval['is_complete'] ?? false);
                    $isLive = ($prog->status === 'in_progress');
                    $isPartial = (!$isComplete && $eval['submitted'] > 0);
                    $scheduledTime = $prog->scheduled_time ?? $prog->schedule?->start_time;
                    $isPast = $scheduledTime ? $scheduledTime->isPast() : false;
                    $isFuture = $scheduledTime ? $scheduledTime->isFuture() : false;
                @endphp
                <div class="bg-white border border-slate-200 hover:border-slate-300 rounded-2xl p-5 sm:p-6 shadow-xs hover:shadow-md transition-all flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                    
                    <!-- Left: Program Info & Metadata -->
                    <div class="flex-1 min-w-0 space-y-3">
                        <!-- Badges line -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase">
                                {{ $prog->code }}
                            </span>
                            @if($prog->zone?->name || $prog->eligibility)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase"
                                      style="background-color: {{ $prog->zone?->color_hex ?? '#005c94' }}15; color: {{ $prog->zone?->color_hex ?? '#005c94' }}; border: 1px solid {{ $prog->zone?->color_hex ?? '#005c94' }}30;">
                                    {{ $prog->zone?->name ?? $prog->eligibility }}
                                </span>
                            @endif
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono text-slate-500 bg-slate-50 border border-slate-200">
                                {{ ucfirst($prog->type) }}
                            </span>
                            @if($isComplete)
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-mono font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Completed</span>
                                </span>
                            @elseif($isLive)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-red-50 text-[#be1e2d] border border-red-200 text-[10px] font-mono font-bold animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#be1e2d]"></span> Live On Stage
                                </span>
                            @elseif($isPartial)
                                <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-mono font-bold">
                                    Scoring in Progress ({{ $eval['submitted'] }}/{{ $eval['present'] }})
                                </span>
                            @elseif($eval['present'] > 0 || $isPast)
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-300 text-[10px] font-mono font-bold flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    <span>Evaluation Pending</span>
                                </span>
                            @elseif($isFuture)
                                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-mono font-semibold">
                                    Upcoming
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-mono font-semibold">
                                    Scheduled
                                </span>
                            @endif
                        </div>

                        <!-- Title & Malayalam Title -->
                        <div>
                            <h3 class="text-lg sm:text-xl font-sora font-bold text-slate-900 tracking-tight">
                                {{ $prog->name }}
                            </h3>
                            @if($prog->malayalam_name)
                                <p class="text-xs font-ml text-slate-500 mt-0.5">{{ $prog->malayalam_name }}</p>
                            @endif
                        </div>

                        <!-- Simple Aligned Info Line -->
                        @php
                            $cardStage = $prog->stage?->name ?: ($prog->schedule?->stage?->name ?: 'Stage Unassigned');
                        @endphp
                        <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-xs font-mono text-slate-600 pt-1">
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400">Venue:</span>
                                <span class="text-slate-900 font-bold">{{ $cardStage }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400">Time:</span>
                                <span class="text-slate-900 font-bold">{{ $scheduledTime ? $scheduledTime->format('d M, h:i A') : 'TBA' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400">Evaluation:</span>
                                <span>Present: <strong class="text-slate-900">{{ $eval['present'] }}</strong></span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-emerald-700 font-bold">Evaluated: {{ $eval['submitted'] }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-amber-700 font-bold">Pending: {{ $eval['pending'] }}</span>
                            </div>
                        </div>

                        <!-- Progress Bar with Percentage -->
                        <div class="w-full max-w-xl flex items-center gap-3 pt-0.5">
                            <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                                <div class="h-full bg-gradient-to-r from-[#005c94] to-emerald-500 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="text-[11px] font-mono font-bold text-slate-600 shrink-0">{{ $percent }}%</span>
                        </div>
                    </div>

                    <!-- Right: Action Button -->
                    <div class="lg:shrink-0 flex items-center">
                        <a href="{{ route('judge.evaluate', $prog) }}" 
                           class="w-full lg:w-auto px-6 py-3.5 min-h-[46px] bg-[#005c94] hover:bg-[#004875] text-white font-mono font-bold text-xs uppercase rounded-xl flex items-center justify-center gap-2 shadow-sm shadow-[#005c94]/20 transition-all cursor-pointer active:scale-98">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            <span>{{ $isComplete ? 'Review / Edit Scores' : ($isPartial ? 'Continue Scoring' : 'Evaluate Participants Now') }}</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center text-slate-500 font-mono text-xs bg-white rounded-3xl border border-slate-200 shadow-sm">
                    No programs have been assigned to your jury panel yet.
                </div>
            @endforelse
        </div>
    <!-- Interactive Quick Timetable Modal -->
    <div x-cloak x-show="scheduleModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" aria-modal="true"
         @keydown.escape.window="scheduleModalOpen = false">
        
        <!-- Backdrop -->
        <div x-show="scheduleModalOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="scheduleModalOpen = false"></div>

        <!-- Modal Panel -->
        <div class="min-h-full flex items-center justify-center p-3 sm:p-6 text-center">
            <div x-show="scheduleModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-4xl bg-white rounded-3xl text-left shadow-2xl border border-slate-200 overflow-hidden relative z-10 transition-all flex flex-col max-h-[90vh]">
                
                <!-- Modal Header -->
                <div class="p-5 sm:p-6 border-b border-slate-200 flex items-center justify-between gap-4 bg-slate-50 shrink-0">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[10px] font-mono font-bold uppercase">
                            <span>Jury Schedule</span>
                        </div>
                        <h3 class="text-xl font-sora font-black text-slate-900 mt-1">
                            {{ $judge->name }} — Schedule Timetable
                        </h3>
                        <p class="text-xs font-mono text-slate-500">
                            Total Assigned: <strong class="text-slate-800">{{ $assignedPrograms->count() }} Competitions</strong>
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('judge.schedule') }}" 
                           class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 text-xs font-mono font-bold border border-slate-200 shadow-xs transition-all">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>Full Page</span>
                        </a>
                        <button type="button" @click="scheduleModalOpen = false" 
                                class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200 transition-all cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body: Scrollable Table -->
                <div class="p-4 sm:p-6 overflow-y-auto flex-1 divide-y divide-slate-100">
                    @if($assignedPrograms->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs font-mono border-collapse">
                                <thead>
                                    <tr class="text-[10px] uppercase text-slate-400 border-b border-slate-200 pb-2">
                                        <th class="pb-3 pr-3 font-bold">Time & Date</th>
                                        <th class="pb-3 px-3 font-bold">Venue</th>
                                        <th class="pb-3 px-3 font-bold">Program</th>
                                        <th class="pb-3 px-3 font-bold">Category</th>
                                        <th class="pb-3 pl-3 text-right font-bold">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($assignedPrograms as $p)
                                        @php
                                            $mTime = $p->scheduled_time ?? $p->schedule?->start_time;
                                            $mStage = $p->stage?->name ?: ($p->schedule?->stage?->name ?: 'Stage Unassigned');
                                            $mIsLive = ($p->status === 'in_progress');
                                        @endphp
                                        <tr class="hover:bg-slate-50 transition-colors {{ $mIsLive ? 'bg-rose-50/50' : '' }}">
                                            <td class="py-3 pr-3 min-w-[140px]">
                                                @if($mTime)
                                                    <div class="font-bold text-slate-900 flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 text-[#005c94] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        <span>{{ $mTime->format('h:i A') }}</span>
                                                    </div>
                                                    <div class="text-[10px] text-slate-500 font-sans">
                                                        {{ $mTime->format('D, d M') }}
                                                    </div>
                                                @else
                                                    <span class="text-slate-400 font-sans text-[11px]">TBA</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 min-w-[140px] text-slate-800 font-sans font-semibold">
                                                {{ $mStage }}
                                            </td>
                                            <td class="py-3 px-3 min-w-[180px]">
                                                <div class="font-sora font-bold text-slate-900 text-xs">
                                                    {{ $p->name }}
                                                </div>
                                                <div class="text-[10px] text-slate-400">
                                                    {{ $p->code }}
                                                </div>
                                            </td>
                                            <td class="py-3 px-3 min-w-[100px]">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                                    {{ $p->zone?->name ?? $p->eligibility }}
                                                </span>
                                            </td>
                                            <td class="py-3 pl-3 text-right">
                                                <a href="{{ route('judge.evaluate', $p) }}" 
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#005c94] hover:bg-[#004875] text-white text-[11px] font-bold font-mono transition-all shadow-xs">
                                                    <span>Evaluate</span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-12 text-center text-slate-500 font-mono text-xs">
                            No programs assigned to this judge panel yet.
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="p-4 sm:p-5 border-t border-slate-200 bg-slate-50 flex items-center justify-between gap-3 shrink-0">
                    <a href="{{ route('schedule.stage-pdf') }}" target="_blank" 
                       class="text-xs font-mono text-slate-600 hover:text-slate-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Download Stage Schedule PDF</span>
                    </a>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="scheduleModalOpen = false" 
                                class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-mono font-bold text-xs transition-all cursor-pointer">
                            Close
                        </button>
                        <a href="{{ route('judge.schedule') }}" 
                           class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-xs transition-all">
                            Full Schedule View
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
