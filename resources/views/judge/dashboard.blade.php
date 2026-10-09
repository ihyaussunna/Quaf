@extends('layouts.judge', ['title' => 'Assigned Programs'])

@section('content')
<div class="space-y-8">
    <!-- Judge Hero Profile (Light Theme) -->
    <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden shadow-sm">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-[#005c94] text-xs font-mono font-bold">
                <svg class="w-3.5 h-3.5 text-[#005c94]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                <span>OFFICIAL JURY DESK</span>
            </div>
            <h1 class="text-3xl font-sora font-bold text-slate-900">Welcome, {{ $judge->name }}</h1>
            <p class="text-xs font-mono text-slate-500">
                Designation: <span class="text-slate-800 font-semibold">{{ $judge->designation ?? 'Official Judge' }}</span>
            </p>
        </div>

        <div class="flex items-center gap-4 bg-slate-50 border border-slate-200 p-4 rounded-2xl">
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
                    $isComplete = $eval['is_complete'] || $prog->status === 'completed' || $prog->result !== null;
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
                        <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-xs font-mono text-slate-600 pt-1">
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400">Venue:</span>
                                <span class="text-slate-900 font-bold">{{ $prog->stage->name ?? 'Stage Unassigned' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400">Time:</span>
                                <span class="text-slate-900 font-bold">{{ $prog->scheduled_time?->format('d M, h:i A') ?? 'TBA' }}</span>
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
                            <span>{{ $eval['is_complete'] ? 'Review / Edit Scores' : 'Evaluate Participants Now' }}</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center text-slate-500 font-mono text-xs bg-white rounded-3xl border border-slate-200 shadow-sm">
                    No programs have been assigned to your jury panel yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
