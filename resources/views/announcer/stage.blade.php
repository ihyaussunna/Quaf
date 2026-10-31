@extends('layouts.app')

@section('title', 'Stage Calling Console | QUAF Announcer')

@section('content')
<div class="min-h-screen bg-[#edf3f8] py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto space-y-6">
        
        <!-- Header Console Banner -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-900 text-white">
                        Live Announcer Console
                    </span>
                    <span class="text-xs text-slate-500 font-mono">Stage Calling & Performer Sync</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight font-sora">
                    Stage Announcer Desk
                </h1>
                <p class="text-xs text-slate-600 mt-1 max-w-2xl font-sora">
                    Use this console to call scheduled performers to the stage by code letter, track who is currently performing, and coordinate the stage queue in real time.
                </p>
            </div>

            <!-- Top Links -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('announcer.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs font-sora">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    <span>Results Announcement Desk</span>
                </a>
                <button type="button" onclick="window.location.reload()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        <!-- Flash messages -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm font-semibold flex items-center justify-between shadow-2xs">
                <span class="font-ml">{{ session('success') }}</span>
                <span class="text-xs font-mono text-emerald-700">UPDATED</span>
            </div>
        @endif

        <!-- Stage Switcher Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
            @foreach($stages as $st)
                <a href="{{ route('announcer.stage', ['stage_id' => $st->id]) }}"
                   class="px-5 py-3 rounded-2xl font-mono text-xs font-bold transition-all shrink-0 flex items-center gap-2.5 {{ $selectedStageId == $st->id ? 'bg-[#005c94] text-white shadow-md scale-105' : 'bg-white text-slate-700 hover:text-slate-900 border border-slate-200 shadow-xs' }}">
                    <span class="w-2 h-2 rounded-full {{ $selectedStageId == $st->id ? 'bg-white' : 'bg-slate-400' }}"></span>
                    <span>{{ $st->name }}</span>
                    @if($st->status === 'live')
                        <span class="px-1.5 py-0.5 rounded text-[9px] bg-red-600 text-white font-bold animate-pulse">LIVE</span>
                    @endif
                </a>
            @endforeach
        </div>

        @if($activeProgram)
            <!-- Active Program Banner -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-slate-900 text-white">
                            Active Program: {{ $activeProgram->code ?: '#'.$activeProgram->id }}
                        </span>
                        <span class="text-xs text-slate-500 font-mono">
                            {{ $activeProgram->zone?->name ?? $activeProgram->eligibility ?? 'All Zones' }} &bull; Stage: {{ $stage?->name }} &bull; Duration: {{ $activeProgram->duration_minutes }} Mins
                        </span>
                        @if($activeProgram->is_call_list_locked)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-red-100 text-red-800 border border-red-200">
                                Call List Locked
                            </span>
                        @endif
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight font-sora">
                        {{ $activeProgram->name }}
                    </h2>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-mono font-bold bg-slate-100 px-3.5 py-2 rounded-xl border border-slate-200">
                        {{ $calls->count() }} Performers in Line
                    </span>
                </div>
            </div>

            <!-- Spotlight Cards: Now on Stage vs Up Next -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Now On Stage Card -->
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-amber-300 rounded-3xl p-6 shadow-md relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-600 animate-ping"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-900 font-sora">
                                Now On Stage
                            </span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-600 text-white uppercase tracking-wider">
                            ON STAGE
                        </span>
                    </div>

                    @if($currentPerformer)
                        <div class="space-y-4">
                            <div class="flex items-baseline gap-3">
                                <span class="text-4xl sm:text-5xl font-black text-slate-900 font-mono">
                                    {{ $currentPerformer->entry?->code_letter ? 'CODE ' . $currentPerformer->entry->code_letter : 'CHEST #' . $currentPerformer->entry?->chest_number }}
                                </span>
                                <span class="text-sm font-mono text-slate-500">
                                    Chest #{{ $currentPerformer->entry?->chest_number }}
                                </span>
                            </div>

                            <div class="bg-white/80 backdrop-blur-xs rounded-2xl p-4 border border-amber-200/80">
                                <div class="text-base font-bold text-slate-900">
                                    {{ $currentPerformer->entry?->student?->name ?? 'Candidate' }}
                                </div>
                                <div class="text-xs text-slate-600 font-medium mt-0.5">
                                    {{ $currentPerformer->entry?->student?->group?->name ?? $currentPerformer->entry?->group?->name ?? 'Team / Unit' }}
                                </div>
                            </div>

                            <form method="POST" action="{{ route('announcer.complete-stage', $currentPerformer->id) }}">
                                @csrf
                                <button type="submit" class="w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm">
                                    Finish & Mark Complete
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="py-8 text-center text-slate-500 text-xs font-mono">
                            No performer currently on stage. Call the next participant from the queue below.
                        </div>
                    @endif
                </div>

                <!-- Up Next in Line Card -->
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600 font-sora">
                                Up Next in Line
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wider">
                                NEXT IN LINE
                            </span>
                        </div>

                        @if($nextPerformer)
                            <div class="space-y-4">
                                <div class="flex items-baseline gap-3">
                                    <span class="text-3xl sm:text-4xl font-black text-slate-900 font-mono">
                                        {{ $nextPerformer->entry?->code_letter ? 'CODE ' . $nextPerformer->entry->code_letter : 'CHEST #' . $nextPerformer->entry?->chest_number }}
                                    </span>
                                    <span class="text-sm font-mono text-slate-500">
                                        Chest #{{ $nextPerformer->entry?->chest_number }}
                                    </span>
                                </div>

                                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
                                    <div class="text-base font-bold text-slate-900">
                                        {{ $nextPerformer->entry?->student?->name ?? 'Candidate' }}
                                    </div>
                                    <div class="text-xs text-slate-600 font-medium mt-0.5">
                                        {{ $nextPerformer->entry?->student?->group?->name ?? $nextPerformer->entry?->group?->name ?? 'Team / Unit' }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="py-8 text-center text-slate-400 text-xs font-mono">
                                No upcoming performers in queue.
                            </div>
                        @endif
                    </div>

                    @if($nextPerformer)
                        <div class="grid grid-cols-2 gap-3 mt-4">
                            <form method="POST" action="{{ route('announcer.call-stage', $nextPerformer->id) }}">
                                @csrf
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-[#005c94] hover:bg-[#004b78] text-white font-bold text-xs uppercase tracking-wider transition shadow-2xs">
                                    Call to Stage
                                </button>
                            </form>
                            <form method="POST" action="{{ route('announcer.enter-stage', $nextPerformer->id) }}">
                                @csrf
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider transition shadow-2xs">
                                    Enter Stage
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Full Stage Queue by Code Letter -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 font-sora">
                            Performer Calling Queue
                        </h3>
                        <p class="text-[11px] text-slate-500 font-mono mt-0.5">
                            Call performers in code letter order sequence.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs font-sora">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                                <th class="px-4 py-3 text-center w-28">Code Letter</th>
                                <th class="px-4 py-3 w-28">Chest #</th>
                                <th class="px-4 py-3">Student Name</th>
                                <th class="px-4 py-3">Team / Group</th>
                                <th class="px-4 py-3 text-center w-36">Status</th>
                                <th class="px-4 py-3 text-right w-64">Stage Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($calls as $call)
                                @php
                                    $entry = $call->entry;
                                    $isOnStage = $call->status === 'on_stage';
                                    $isCalled = $call->status === 'called';
                                    $isCompleted = $call->status === 'completed';
                                    $isAbsent = $entry?->attendance_status === 'absent';
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors {{ $isOnStage ? 'bg-amber-50/60 font-bold' : ($isAbsent ? 'opacity-40 bg-red-50/20' : '') }}">
                                    <td class="px-4 py-3 text-center">
                                        @if($entry?->code_letter)
                                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-black bg-purple-100 text-purple-900 border border-purple-300 font-mono">
                                                Code {{ $entry->code_letter }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 font-mono italic">- None -</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-mono font-bold text-slate-900">
                                        #{{ $entry?->chest_number }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-slate-900">{{ $entry?->student?->name ?? 'Candidate' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $entry?->student?->group?->name ?? $entry?->group?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($isOnStage)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-red-100 text-red-800 border border-red-200 animate-pulse">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                                On Stage
                                            </span>
                                        @elseif($isCalled)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800 border border-amber-200">
                                                Called
                                            </span>
                                        @elseif($isCompleted)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Completed
                                            </span>
                                        @elseif($isAbsent)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-red-100 text-red-800 border border-red-200">
                                                Absent
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                                {{ ucfirst($call->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if(!$isCompleted && !$isAbsent)
                                                @if(!$isOnStage)
                                                    <form method="POST" action="{{ route('announcer.call-stage', $call->id) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-2.5 py-1 rounded-lg border text-[11px] font-bold bg-white text-slate-700 hover:bg-slate-50 transition-colors">
                                                            Call
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('announcer.enter-stage', $call->id) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white text-[11px] font-bold hover:bg-emerald-700 transition-colors shadow-2xs">
                                                            Enter Stage
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('announcer.complete-stage', $call->id) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1 rounded-lg bg-slate-900 text-white text-[11px] font-bold hover:bg-slate-800 transition-colors shadow-2xs">
                                                            Done
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400 text-xs">
                                        No participants in queue for this program.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="p-12 bg-white rounded-3xl border border-slate-200 text-center text-xs text-slate-500">
                No active programs currently scheduled on this stage.
            </div>
        @endif

    </div>
</div>
@endsection
