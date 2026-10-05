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
                Designation: <span class="text-slate-800 font-semibold">{{ $judge->designation ?? 'Adjudicator' }}</span> • Specialization: <span class="text-[#f3bd2e] font-semibold">{{ $judge->specialization ?? 'General Arts' }}</span>
            </p>
        </div>

        <div class="flex items-center gap-4 bg-slate-50 border border-slate-200 p-4 rounded-2xl">
            <div class="text-center px-4 border-r border-slate-200">
                <div class="text-2xl font-sora font-bold text-slate-900">{{ $assignedPrograms->count() }}</div>
                <div class="text-[10px] font-mono text-slate-500 uppercase font-semibold">Assigned</div>
            </div>
            <div class="text-center px-4 border-r border-slate-200">
                <div class="text-2xl font-sora font-bold text-amber-600">{{ $inProgress->count() }}</div>
                <div class="text-[10px] font-mono text-slate-500 uppercase font-semibold">In Progress</div>
            </div>
            <div class="text-center px-4">
                <div class="text-2xl font-sora font-bold text-emerald-600">{{ $completed->count() }}</div>
                <div class="text-[10px] font-mono text-slate-500 uppercase font-semibold">Completed</div>
            </div>
        </div>
    </div>

    <!-- Assigned Programs Grid (Light Theme) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-sora font-bold text-slate-900">Your Evaluation Schedule</h2>
            <span class="text-xs font-mono text-slate-500 font-semibold">Total: {{ $assignedPrograms->count() }} Events</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($assignedPrograms as $prog)
                @php
                    $eval = $evaluationStatus[$prog->id] ?? ['total' => 0, 'submitted' => 0, 'is_complete' => false];
                    $percent = $eval['total'] > 0 ? round(($eval['submitted'] / $eval['total']) * 100) : 0;
                @endphp
                <div class="bg-white border border-slate-200 rounded-3xl p-6 hover:border-[#f3bd2e]/40 transition-all flex flex-col justify-between space-y-6 shadow-sm hover:shadow-md">
                    <div>
                        <!-- Program Header -->
                        <div class="flex items-start justify-between gap-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold bg-amber-50 text-[#f3bd2e] border border-amber-200 uppercase">
                                {{ $prog->category->name ?? 'General' }}
                            </span>
                            @if($prog->status === 'in_progress')
                                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-mono font-bold animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Live On Stage
                                </span>
                            @elseif($prog->status === 'completed')
                                <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-mono font-bold">
                                    Completed
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[10px] font-mono font-semibold">
                                    Upcoming
                                </span>
                            @endif
                        </div>

                        <h3 class="text-xl font-sora font-bold text-slate-900 mt-3">{{ $prog->name }}</h3>
                        <p class="text-xs font-mono text-slate-500 mt-1">Code: {{ $prog->code }} • Type: {{ ucfirst($prog->type) }}</p>

                        <!-- Stage & Timing Info -->
                        <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-slate-100 text-xs font-mono">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Stage Venue</span>
                                <span class="text-slate-800 font-semibold">{{ $prog->stage->name ?? 'Stage Unassigned' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Scheduled Time</span>
                                <span class="text-slate-800 font-semibold">{{ $prog->scheduled_time?->format('M d, h:i A') ?? 'TBA' }}</span>
                            </div>
                        </div>

                        <!-- Evaluation Progress Bar -->
                        <div class="mt-4 pt-4 border-t border-slate-100 space-y-2">
                            <div class="flex items-center justify-between text-xs font-mono">
                                <span class="text-slate-500">
                                    Present: <strong class="text-slate-900">{{ $eval['present'] }}</strong> &bull; 
                                    Evaluated: <strong class="text-emerald-700">{{ $eval['submitted'] }}</strong> &bull; 
                                    Pending: <strong class="text-amber-700">{{ $eval['pending'] }}</strong>
                                </span>
                                <span class="text-[#f3bd2e] font-bold">{{ $percent }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                                <div class="h-full bg-gradient-to-r from-[#f3bd2e] to-amber-500 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Evaluation Action -->
                    <div>
                        <a href="{{ route('judge.evaluate', $prog) }}" class="w-full py-3.5 px-4 min-h-[48px] bg-[#005c94] hover:bg-[#004875] text-white font-mono font-bold text-xs sm:text-sm uppercase rounded-xl flex items-center justify-center gap-2 shadow-md shadow-[#005c94]/20 transition-all cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            <span>{{ $eval['is_complete'] ? 'Review / Edit Scores' : 'Evaluate Participants Now' }}</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center text-slate-500 font-mono text-xs bg-white rounded-3xl border border-slate-200 shadow-sm">
                    No programs have been assigned to your jury panel yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
