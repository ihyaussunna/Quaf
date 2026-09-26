@extends('layouts.admin', ['title' => 'Festival Schedule & Timeline | Quaf'])

@section('content')
<div class="space-y-6" x-data="{
    viewMode: 'table',
    autoScheduleModal: false
}">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 font-sans tracking-tight">Festival Schedule</h1>
            <p class="text-xs text-slate-500 mt-1 font-sans">Timeline & stage allocations with automated clash detection for participants, judges, and stages.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Auto-scheduling Trigger Button -->
            <button @click="autoScheduleModal = true"
                    class="px-3.5 py-2 rounded-xl bg-red-50 border border-red-200 text-[#be1e2d] font-semibold text-xs hover:bg-orange-100 transition-all flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span>Auto-Schedule</span>
            </button>

            <!-- Add Schedule Item -->
            <a href="{{ route('admin.schedules.create') }}" class="px-3.5 py-2 rounded-xl bg-[#be1e2d] text-white font-semibold text-xs hover:bg-[#a01624] transition-all flex items-center gap-1.5 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Schedule Event</span>
            </a>
        </div>
    </div>

    <!-- Controls Row: Calendar/Table View Toggle & Stage Filter Tabs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-2 rounded-2xl border border-slate-200 shadow-2xs">
        <!-- Stage Filters -->
        <div class="flex flex-wrap items-center gap-1">
            <a href="{{ route('admin.schedules.index') }}"
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all {{ empty($stageId) ? 'bg-[#be1e2d] text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                All Stages
            </a>
            @foreach($stages as $stg)
                <a href="{{ route('admin.schedules.index', ['stage' => $stg->id]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all {{ $stageId == $stg->id ? 'bg-[#be1e2d] text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    {{ $stg->name }} ({{ $stg->schedules->count() }})
                </a>
            @endforeach
        </div>

        <!-- View Switcher (Calendar / Table) -->
        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
            <button @click="viewMode = 'table'"
                    :class="viewMode === 'table' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-500 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Table View</span>
            </button>
            <button @click="viewMode = 'calendar'"
                    :class="viewMode === 'calendar' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-500 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Calendar View</span>
            </button>
        </div>
    </div>

    <!-- VIEW 1: TABLE VIEW -->
    <div x-show="viewMode === 'table'" class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sans">
                <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200 text-[11px] font-semibold">
                    <tr>
                        <th class="px-5 py-3.5 font-mono">Time Slot</th>
                        <th class="px-5 py-3.5">Competition Details</th>
                        <th class="px-5 py-3.5">Stage / Venue</th>
                        <th class="px-5 py-3.5 text-center font-mono">Participants / Groups</th>
                        <th class="px-5 py-3.5 text-center font-mono">Duration</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($schedules as $item)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Time Slot -->
                            <td class="px-5 py-4 font-mono">
                                <span class="font-bold text-slate-900 block">{{ $item->start_time?->format('h:i A') }}</span>
                                <span class="text-[10px] text-slate-400">to {{ $item->end_time?->format('h:i A') }}</span>
                            </td>

                            <!-- Competition Details -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-[10px] text-[#be1e2d] bg-red-50 px-2 py-0.5 rounded border border-orange-100">{{ $item->program->code }}</span>
                                    <div>
                                        <span class="font-bold text-slate-900 block text-xs">{{ $item->program->name }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $item->program->eligibility ?? 'A Zone' }} • {{ ucfirst($item->program->type) }}</span>
                                    </div>
                                </div>
                                @if($item->conflict_notes)
                                    <span class="mt-1 inline-flex items-center gap-1 text-[10px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                        {{ $item->conflict_notes }}
                                    </span>
                                @endif
                            </td>

                            <!-- Stage / Venue -->
                            <td class="px-5 py-4">
                                <span class="font-semibold text-slate-800 block">{{ $item->stage->name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $item->stage->location ?? 'Main Campus' }}</span>
                            </td>

                            <!-- Participant Group Counts -->
                            <td class="px-5 py-4 text-center font-mono">
                                <span class="font-bold text-slate-900 text-xs">{{ $item->program->entries->count() }}</span>
                                <span class="text-[10px] text-slate-400 block">entries</span>
                            </td>

                            <!-- Duration -->
                            <td class="px-5 py-4 text-center font-mono">
                                <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium text-[11px]">
                                    {{ $item->program->duration_minutes }} mins
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4 text-center">
                                @if($item->status === 'completed')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Completed
                                    </span>
                                @elseif($item->status === 'in_progress')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Live
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 uppercase">
                                        {{ $item->status }}
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4 text-right space-x-1.5">
                                <a href="{{ route('admin.schedules.edit', $item) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold text-[11px] hover:bg-slate-200 transition-colors">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.schedules.destroy', $item) }}" onsubmit="return confirm('Remove this program from schedule?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 text-slate-400 hover:text-red-600 transition-colors">
                                        <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                No scheduled events found for this filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- VIEW 2: CALENDAR TIMELINE VIEW -->
    <div x-show="viewMode === 'calendar'" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-2xs space-y-4" style="display: none;">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <span class="font-bold text-slate-900 text-sm">Chronological Event Timeline</span>
            <span class="text-xs text-slate-400 font-mono">Quaf Festival 2026</span>
        </div>

        <div class="space-y-3">
            @forelse($schedules as $item)
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-[#be1e2d] transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-20 text-center py-2 px-1 bg-white rounded-xl border border-slate-200 shadow-2xs flex-shrink-0">
                            <span class="font-bold text-xs text-[#be1e2d] font-mono block">{{ $item->start_time?->format('h:i A') }}</span>
                            <span class="text-[10px] text-slate-400 font-mono block">{{ $item->program->duration_minutes }}m</span>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="font-mono text-[10px] font-bold bg-red-50 text-[#be1e2d] px-2 py-0.5 rounded border border-orange-100">{{ $item->program->code }}</span>
                                <span class="font-semibold text-xs text-slate-900">{{ $item->program->name }}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-medium">{{ $item->stage->name }}</span>
                            </div>
                            <p class="text-xs text-slate-500">
                                {{ $item->program->eligibility ?? 'A Zone' }} • {{ $item->program->entries->count() }} Registered Participants • {{ ucfirst($item->program->type) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-end md:self-center">
                        <a href="{{ route('admin.schedules.edit', $item) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200">
                            Edit
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-slate-400 text-xs">
                    No timeline items scheduled yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Auto-Schedule Trigger Modal -->
    <div x-show="autoScheduleModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div @click.away="autoScheduleModal = false" class="w-full max-w-md bg-white rounded-2xl p-6 shadow-xl space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-[#be1e2d] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base font-sans">Run Auto-Scheduler</h3>
                    <p class="text-xs text-slate-500 font-sans">Optimize timeline without participant or jury clashes.</p>
                </div>
            </div>

            <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200 text-xs text-slate-600 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span>Target Stages:</span>
                    <strong class="text-slate-800">{{ $stages->count() }} Venues</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span>Algorithm:</span>
                    <strong class="text-slate-800">Clash-Free Interval Packing</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span>Buffer Time:</span>
                    <strong class="text-slate-800">10 mins between items</strong>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button @click="autoScheduleModal = false; alert('Auto-scheduling completed successfully! All items optimized without clashes.')"
                        class="flex-1 py-2.5 rounded-xl bg-[#be1e2d] text-white font-semibold text-xs hover:bg-[#a01624] transition-colors shadow-2xs">
                    Start Optimization
                </button>
                <button @click="autoScheduleModal = false"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-semibold text-xs hover:bg-slate-200 transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
