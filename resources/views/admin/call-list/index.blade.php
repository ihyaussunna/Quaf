@extends('layouts.admin', ['title' => 'Call List & Attendance Center | FestFloww'])

@section('content')
<div class="space-y-6" x-data="adminCallListManager()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-emerald-700">Central Live Hub</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-sora">
                Call List & Attendance Center
            </h1>
            <p class="text-xs text-slate-500 mt-0.5 font-sora">
                @if($selectedProgram)
                    Digital Call List for <strong>[{{ $selectedProgram->code }}] {{ $selectedProgram->name }}</strong>
                @else
                    Programs Call List Directory &bull; Each program operates as its own verified call list. Historical attendance data is permanently preserved across all completed programs.
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($selectedProgram)
                <a href="{{ route('admin.call-list.index') }}"
                   class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs font-sora transition-colors shadow-2xs flex items-center gap-1.5">
                    <span>&larr; All Programs (Call Lists)</span>
                </a>
            @endif
            <a href="{{ route('greenroom.call-list', $selectedProgram ? ['program' => $selectedProgram->id] : []) }}" target="_blank"
               class="px-4 py-2 rounded-xl bg-[#005c94] text-white font-bold text-xs uppercase hover:bg-[#004b78] transition-colors shadow-2xs flex items-center gap-1.5 font-sora">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Green Room View</span>
            </a>
            <a href="{{ route('admin.evaluation-monitor.index') }}"
               class="px-4 py-2 rounded-xl bg-purple-600 text-white font-bold text-xs uppercase hover:bg-purple-700 transition-colors shadow-2xs flex items-center gap-1.5 font-sora">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Evaluation Monitor</span>
            </a>
            <a href="{{ route('admin.judge-marks.index') }}"
               class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs uppercase hover:bg-slate-800 transition-colors shadow-2xs flex items-center gap-1.5 font-sora">
                <span>Judge Marks &rarr;</span>
            </a>
        </div>
    </div>

    <!-- Live Toast Alert -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 max-w-sm rounded-2xl px-4 py-3 shadow-xl font-mono text-xs flex items-center gap-3 border"
         :class="toast.isError ? 'bg-red-950 text-red-200 border-red-800' : 'bg-slate-900 text-emerald-300 border-slate-700'"
         style="display: none;">
        <span class="w-2.5 h-2.5 rounded-full" :class="toast.isError ? 'bg-red-500' : 'bg-emerald-400 animate-pulse'"></span>
        <span x-text="toast.message"></span>
    </div>

    <!-- Live Summary Counter Cards -->
    @php
        $displayStats = $selectedProgram ? $programStats : $stats;
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
        <!-- 1. Total Call List -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-2xs">
            <span class="text-slate-500 font-mono block text-[11px] uppercase font-bold">
                {{ $selectedProgram ? 'Program Entries' : 'Total Programs' }}
            </span>
            <span class="text-2xl font-black text-slate-900 mt-1 block font-mono">
                {{ $selectedProgram ? number_format($displayStats['total']) : number_format($stats['total_programs']) }}
            </span>
            <span class="text-[10px] text-slate-400 font-mono">
                {{ $selectedProgram ? 'Registered Students' : number_format($stats['completed_programs'] ?? 0) . ' Completed & Preserved' }}
            </span>
        </div>

        <!-- 2. Present -->
        <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 text-emerald-900 shadow-2xs">
            <span class="font-mono block text-emerald-700 text-[11px] uppercase font-bold">Total Present</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($displayStats['present']) }}</span>
            <span class="text-[10px] text-emerald-600 font-mono">Eligible for Jury</span>
        </div>

        <!-- 3. Absent -->
        <div class="p-4 rounded-2xl bg-red-50/70 border border-red-200 text-red-900 shadow-2xs">
            <span class="font-mono block text-red-700 text-[11px] uppercase font-bold">Total Absent</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($displayStats['absent']) }}</span>
            <span class="text-[10px] text-red-600 font-mono">Excluded from Jury</span>
        </div>

        <!-- 4. Pending Calls / Waiting -->
        <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 text-amber-900 shadow-2xs">
            <span class="font-mono block text-amber-700 text-[11px] uppercase font-bold">Waiting Calls</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($displayStats['waiting']) }}</span>
            <span class="text-[10px] text-amber-600 font-mono">Pending Check-in</span>
        </div>

        <!-- 5. Pending Evaluation -->
        <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200 text-blue-900 shadow-2xs">
            <span class="font-mono block text-blue-700 text-[11px] uppercase font-bold">Pending Eval</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($displayStats['pending_evaluation']) }}</span>
            <span class="text-[10px] text-blue-600 font-mono">Present without Score</span>
        </div>

        <!-- 6. Completed Evaluation -->
        <div class="p-4 rounded-2xl bg-purple-50/70 border border-purple-200 text-purple-900 shadow-2xs">
            <span class="font-mono block text-purple-700 text-[11px] uppercase font-bold">Completed Eval</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($displayStats['evaluated']) }}</span>
            <span class="text-[10px] text-purple-600 font-mono">Score Submitted</span>
        </div>
    </div>

    @if(!$selectedProgram)
        <!-- ========================================== -->
        <!-- VIEW 1: ONE PROGRAM = ONE CALL LIST VIEW   -->
        <!-- ========================================== -->

        <!-- Search & Filter Bar for Programs -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs">
            <form method="GET" action="{{ route('admin.call-list.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 items-end">
                <!-- Search Program -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Search Program</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Program name, code..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                </div>

                <!-- Zone / Category Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Zone / Category</label>
                    <select name="zone" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                        <option value="">All Zones</option>
                        @foreach($zones as $zKey => $zVal)
                            @php
                                $zoneName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                                $zoneValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                            @endphp
                            <option value="{{ $zoneValue }}" {{ ($zone ?? '') == $zoneValue ? 'selected' : '' }}>{{ $zoneName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Stage / Venue Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Stage / Venue</label>
                    <select name="stage_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                        <option value="">All Stages</option>
                        @foreach($stages as $s)
                            <option value="{{ $s->id }}" {{ (string)$stageId === (string)$s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Lock Status Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Call List Lock</label>
                    <select name="lock_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                        <option value="">All Call Lists</option>
                        <option value="locked" {{ ($lockStatus ?? '') === 'locked' ? 'selected' : '' }}>Locked Only</option>
                        <option value="open" {{ ($lockStatus ?? '') === 'open' ? 'selected' : '' }}>Open Only</option>
                    </select>
                </div>

                <!-- Program Status Filter (Completed, In Progress, Upcoming) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Program Status</label>
                    <select name="program_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                        <option value="">All Programs (All Data)</option>
                        <option value="completed" {{ ($programStatus ?? '') === 'completed' ? 'selected' : '' }}>Completed (കഴിഞ്ഞവ)</option>
                        <option value="in_progress" {{ ($programStatus ?? '') === 'in_progress' ? 'selected' : '' }}>In Progress (നടക്കുന്നവ)</option>
                        <option value="upcoming" {{ ($programStatus ?? '') === 'upcoming' ? 'selected' : '' }}>Upcoming (വരാനിരിക്കുന്നവ)</option>
                    </select>
                </div>

                <!-- Filter Actions -->
                <div class="flex items-center gap-2 sm:col-span-2 md:col-span-4 lg:col-span-1">
                    <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5 font-sora">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('admin.call-list.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Programs Call List Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 font-sora">
                        Programs Call List Directory
                    </h3>
                    <p class="text-[11px] text-slate-500 font-mono mt-0.5">
                        Each program maintains its own discrete Call List with real-time jury synchronization.
                    </p>
                </div>
                <div class="text-xs font-mono text-slate-500">
                    Showing {{ $programCallLists->firstItem() ?? 0 }}-{{ $programCallLists->lastItem() ?? 0 }} of {{ $programCallLists->total() }} programs
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-sora">
                    <thead>
                        <tr class="bg-slate-100/75 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                            <th class="px-4 py-3.5 text-center w-12">#</th>
                            <th class="px-4 py-3.5">Program Details</th>
                            <th class="px-4 py-3.5">Venue & Schedule</th>
                            <th class="px-4 py-3.5 text-center">Call List Count</th>
                            <th class="px-4 py-3.5 text-center">Attendance Summary</th>
                            <th class="px-4 py-3.5 text-center">Program Status</th>
                            <th class="px-4 py-3.5 text-center">Call List Lock</th>
                            <th class="px-4 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($programCallLists as $index => $prog)
                            <tr class="hover:bg-slate-50/75 transition-colors">
                                <!-- Index -->
                                <td class="px-4 py-3.5 text-center font-mono text-slate-400">
                                    {{ $programCallLists->firstItem() + $index }}
                                </td>

                                <!-- Program Info -->
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-slate-900 text-sm hover:text-[#be1e2d] transition-colors">
                                        <a href="{{ route('admin.call-list.index', ['program_id' => $prog->id]) }}">
                                            {{ $prog->name }}
                                        </a>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1 font-mono text-[10px]">
                                        <span class="px-1.5 py-0.5 rounded bg-slate-900 text-white font-bold">
                                            {{ $prog->code ?: 'ID: ' . $prog->id }}
                                        </span>
                                        <span class="text-slate-500">
                                            {{ $prog->category?->name ?? $prog->eligibility ?? 'General' }}
                                        </span>
                                        @if($prog->malayalam_name)
                                            <span class="text-slate-400">&bull; {{ $prog->malayalam_name }}</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Venue & Time -->
                                <td class="px-4 py-3.5 font-mono text-slate-600">
                                    <div class="font-semibold text-slate-800">
                                        {{ $prog->stage?->name ?? 'Venue TBA' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $prog->scheduled_time ? \Carbon\Carbon::parse($prog->scheduled_time)->format('M d, h:i A') : 'Time TBA' }}
                                    </div>
                                </td>

                                <!-- Total Participants Count -->
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-black font-mono bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $prog->total_count }} Registered
                                    </span>
                                </td>

                                <!-- Attendance Summary Badges -->
                                <td class="px-4 py-3.5 text-center">
                                    <div class="inline-flex items-center gap-1.5 font-mono text-[11px]">
                                        <span class="px-2 py-0.5 rounded-lg font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            {{ $prog->present_count }} Present
                                        </span>
                                        @if($prog->absent_count > 0)
                                            <span class="px-2 py-0.5 rounded-lg font-bold bg-red-100 text-red-800 border border-red-200">
                                                {{ $prog->absent_count }} Absent
                                            </span>
                                        @endif
                                        @if($prog->waiting_count > 0)
                                            <span class="px-2 py-0.5 rounded-lg font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                {{ $prog->waiting_count }} Waiting
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Program Status (Upcoming, In Progress, Completed) -->
                                <td class="px-4 py-3.5 text-center">
                                    @if($prog->status === 'completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Completed
                                        </span>
                                    @elseif($prog->status === 'in_progress')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase bg-amber-100 text-amber-800 border border-amber-200">
                                            In Progress
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase bg-slate-100 text-slate-700 border border-slate-200">
                                            Upcoming
                                        </span>
                                    @endif
                                </td>

                                <!-- Call List Lock Status -->
                                <td class="px-4 py-3.5 text-center">
                                    @if($prog->is_call_list_locked)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold font-mono uppercase bg-red-100 text-red-800 border border-red-200">
                                            <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            Locked
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold font-mono uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            Open
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Open Call List Button -->
                                        <a href="{{ route('admin.call-list.index', ['program_id' => $prog->id]) }}"
                                           class="px-3 py-1.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs transition-all shadow-2xs flex items-center gap-1 font-sora">
                                            <span>Open Call List</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>

                                        <!-- Print Button -->
                                        <a href="{{ route('admin.forms.call-list', ['program_id' => $prog->id]) }}" target="_blank"
                                           class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
                                           title="Print Call List">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>

                                        <!-- Quick Toggle Lock Form -->
                                        <form method="POST" action="{{ route('admin.call-list.toggle-lock', $prog) }}" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="p-1.5 rounded-xl transition {{ $prog->is_call_list_locked ? 'bg-red-50 hover:bg-red-100 text-red-700' : 'bg-slate-100 hover:bg-slate-200 text-slate-600' }}"
                                                    title="{{ $prog->is_call_list_locked ? 'Unlock Call List' : 'Lock Call List (Remove Green Room Access)' }}">
                                                @if($prog->is_call_list_locked)
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400 font-mono">
                                    No programs match your search or filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($programCallLists->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50">
                    {{ $programCallLists->links() }}
                </div>
            @endif
        </div>

    @else
        <!-- ========================================== -->
        <!-- VIEW 2: SPECIFIC PROGRAM DIGITAL CALL LIST -->
        <!-- ========================================== -->

        <!-- Program Control Banner -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-2xs space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-slate-900 text-white">
                            {{ $selectedProgram->code ?: '#'.$selectedProgram->id }}
                        </span>
                        <span class="text-xs text-slate-500 font-mono">
                            {{ $selectedProgram->category->name ?? $selectedProgram->eligibility ?? 'General' }} &bull; Stage: {{ $selectedProgram->stage->name ?? 'TBA' }}
                        </span>
                        @if($selectedProgram->is_call_list_locked)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-red-100 text-red-800 border border-red-200 flex items-center gap-1 font-mono">
                                <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                LOCKED
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1 font-mono">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                OPEN / EDITABLE
                            </span>
                        @endif
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight font-sora">
                        {{ $selectedProgram->name }}
                    </h2>
                    @if($selectedProgram->malayalam_name)
                        <div class="text-xs text-slate-600 mt-0.5">{{ $selectedProgram->malayalam_name }}</div>
                    @endif
                </div>

                <!-- Program Actions: Lock Toggle, Shuffle Codes, Print -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Toggle Lock -->
                    <form method="POST" action="{{ route('admin.call-list.toggle-lock', $selectedProgram) }}">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs font-sora {{ $selectedProgram->is_call_list_locked ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-red-700 hover:bg-red-800 text-white' }}"
                                onclick="return confirm('{{ $selectedProgram->is_call_list_locked ? 'Unlock this call list?' : 'Lock this call list? When locked, only PRESENT students will be accessible in judge evaluation.' }}');">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>{{ $selectedProgram->is_call_list_locked ? 'Unlock Call List' : 'Lock Call List' }}</span>
                        </button>
                    </form>

                    <!-- Shuffle Code Letters -->
                    @if(!$selectedProgram->is_call_list_locked)
                        <form method="POST" action="{{ route('admin.call-list.shuffle', $selectedProgram) }}">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs font-sora"
                                    onclick="return confirm('Shuffle and assign anonymous random code letters (A, B, C...) to all PRESENT participants?');">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Shuffle Code Letters</span>
                            </button>
                        </form>
                    @endif

                    <!-- Print Call List -->
                    <a href="{{ route('admin.forms.call-list', ['program_id' => $selectedProgram->id]) }}" target="_blank"
                       class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs font-sora">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Print Call List</span>
                    </a>
                </div>
            </div>

            <!-- Participant Filters within Program -->
            <form method="GET" action="{{ route('admin.call-list.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end pt-1">
                <input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Search Participant</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Name, Chest #, Code..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-[#be1e2d]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Attendance Filter</label>
                    <select name="attendance" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-[#be1e2d]">
                        <option value="">All Statuses</option>
                        <option value="present" {{ ($attendance ?? '') === 'present' ? 'selected' : '' }}>Present Only</option>
                        <option value="absent" {{ ($attendance ?? '') === 'absent' ? 'selected' : '' }}>Absent Only</option>
                        <option value="waiting" {{ ($attendance ?? '') === 'waiting' ? 'selected' : '' }}>Waiting Only</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 font-sora">
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('admin.call-list.index', ['program_id' => $selectedProgram->id]) }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Participants Call List Table for Selected Program -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 font-sora">
                        Participant Call List &bull; {{ $selectedProgram->name }}
                    </h3>
                    <p class="text-[11px] text-slate-500 font-mono mt-0.5">
                        Mark attendance and assign anonymous jury code letters.
                    </p>
                </div>
                <div class="text-xs font-mono text-slate-500">
                    Showing {{ $entries->count() }} participants
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-sora">
                    <thead>
                        <tr class="bg-slate-100/75 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                            <th class="px-4 py-3 text-center w-28">Jury Code</th>
                            <th class="px-4 py-3">Participant Student</th>
                            <th class="px-4 py-3">Group / Team</th>
                            <th class="px-4 py-3 text-center w-36">Attendance</th>
                            <th class="px-4 py-3 text-center w-64">Attendance Actions</th>
                            <th class="px-4 py-3 text-center w-36">Evaluation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($entries as $entry)
                            <tr id="entry-row-{{ $entry->id }}"
                                class="hover:bg-slate-50/75 transition-colors {{ $entry->attendance_status === 'absent' ? 'opacity-60 bg-red-50/20' : '' }}">
                                <!-- Participant Jury Code -->
                                <td class="px-4 py-3 text-center">
                                    <span id="code-badge-{{ $entry->id }}"
                                          class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-black font-mono {{ $entry->code_letter ? 'bg-purple-100 text-purple-900 border border-purple-300' : 'bg-slate-100 text-slate-400 italic' }}">
                                        {{ $entry->code_letter ? 'Code ' . $entry->code_letter : '- None -' }}
                                    </span>
                                </td>

                                <!-- Student Identity -->
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900 text-sm">{{ $entry->student?->name ?? 'Participant' }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        Chest: #{{ $entry->chest_number }} &bull; ID: {{ $entry->student?->student_id ?? '-' }}
                                    </div>
                                </td>

                                <!-- Group -->
                                <td class="px-4 py-3 font-mono text-slate-700">
                                    {{ $entry->group?->name ?? $entry->student?->group?->name ?? '-' }}
                                </td>

                                <!-- Attendance Status Badge -->
                                <td class="px-4 py-3 text-center">
                                    <span id="status-badge-{{ $entry->id }}"
                                          class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-mono font-bold uppercase {{ $entry->attendance_status === 'present' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($entry->attendance_status === 'absent' ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-amber-100 text-amber-800 border border-amber-300') }}">
                                        {{ strtoupper($entry->attendance_status ?: 'waiting') }}
                                    </span>
                                </td>

                                <!-- Instant Attendance Toggle Buttons -->
                                <td class="px-4 py-3 text-center">
                                    @if($selectedProgram->is_call_list_locked)
                                        <span class="text-slate-400 text-[11px] font-mono italic">Locked (Call List finalized)</span>
                                    @else
                                        <div class="inline-flex items-center gap-1 font-mono">
                                            <!-- Present -->
                                            <button type="button"
                                                    id="btn-present-{{ $entry->id }}"
                                                    @click="updateAttendance({{ $entry->id }}, 'present')"
                                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all shadow-2xs {{ $entry->attendance_status === 'present' ? 'bg-emerald-600 text-white font-black' : 'bg-slate-100 hover:bg-emerald-50 text-slate-700 border border-slate-200' }}">
                                                Present
                                            </button>

                                            <!-- Absent -->
                                            <button type="button"
                                                    id="btn-absent-{{ $entry->id }}"
                                                    @click="updateAttendance({{ $entry->id }}, 'absent')"
                                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all shadow-2xs {{ $entry->attendance_status === 'absent' ? 'bg-red-600 text-white font-black' : 'bg-slate-100 hover:bg-red-50 text-slate-700 border border-slate-200' }}">
                                                Absent
                                            </button>

                                            <!-- Waiting -->
                                            <button type="button"
                                                    id="btn-waiting-{{ $entry->id }}"
                                                    @click="updateAttendance({{ $entry->id }}, 'waiting')"
                                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all shadow-2xs {{ ($entry->attendance_status === 'waiting' || empty($entry->attendance_status)) ? 'bg-amber-500 text-white font-black' : 'bg-slate-100 hover:bg-amber-50 text-slate-700 border border-slate-200' }}">
                                                Waiting
                                            </button>
                                        </div>
                                    @endif
                                </td>

                                <!-- Evaluation Status Badge -->
                                <td class="px-4 py-3 text-center">
                                    <span id="eval-badge-{{ $entry->id }}"
                                          class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase {{ $entry->evaluation_status === 'EVALUATED' ? 'bg-purple-100 text-purple-900 border border-purple-200' : ($entry->evaluation_status === 'EVALUATION_PENDING' ? 'bg-blue-100 text-blue-900 border border-blue-200' : 'bg-slate-100 text-slate-600 border border-slate-200') }}">
                                        {{ $entry->evaluation_status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-mono">
                                    No participant entries found for this program.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<!-- Alpine Manager Script for Instant Attendance Controls -->
<script>
    function adminCallListManager() {
        return {
            toast: {
                show: false,
                message: '',
                isError: false,
                timeout: null
            },
            showToast(msg, isError = false) {
                this.toast.message = msg;
                this.toast.isError = isError;
                this.toast.show = true;
                if (this.toast.timeout) clearTimeout(this.toast.timeout);
                this.toast.timeout = setTimeout(() => {
                    this.toast.show = false;
                }, 3500);
            },
            async updateAttendance(entryId, status) {
                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const url = '/admin/call-list/' + entryId + '/attendance';

                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ status: status })
                    });

                    const data = await res.json();
                    if (!res.ok) {
                        throw new Error(data.message || 'Server error updating attendance.');
                    }

                    // Update UI state
                    const btnPresent = document.getElementById('btn-present-' + entryId);
                    const btnAbsent = document.getElementById('btn-absent-' + entryId);
                    const btnWaiting = document.getElementById('btn-waiting-' + entryId);
                    const row = document.getElementById('entry-row-' + entryId);
                    const codeBadge = document.getElementById('code-badge-' + entryId);
                    const statusBadge = document.getElementById('status-badge-' + entryId);
                    const evalBadge = document.getElementById('eval-badge-' + entryId);

                    if (btnPresent && btnAbsent && btnWaiting) {
                        btnPresent.className = status === 'present' 
                            ? 'px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all shadow-2xs bg-emerald-600 text-white font-black'
                            : 'px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all shadow-2xs bg-slate-100 hover:bg-emerald-50 text-slate-700 border border-slate-200';
                        btnAbsent.className = status === 'absent'
                            ? 'px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all shadow-2xs bg-red-600 text-white font-black'
                            : 'px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all shadow-2xs bg-slate-100 hover:bg-red-50 text-slate-700 border border-slate-200';
                        btnWaiting.className = status === 'waiting'
                            ? 'px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all shadow-2xs bg-amber-500 text-white font-black'
                            : 'px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all shadow-2xs bg-slate-100 hover:bg-amber-50 text-slate-700 border border-slate-200';
                    }

                    if (row) {
                        if (status === 'absent') {
                            row.classList.add('opacity-60', 'bg-red-50/20');
                        } else {
                            row.classList.remove('opacity-60', 'bg-red-50/20');
                        }
                    }

                    if (codeBadge) {
                        if (data.code_letter) {
                            codeBadge.textContent = 'Code ' + data.code_letter;
                            codeBadge.className = 'inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-black font-mono bg-purple-100 text-purple-900 border border-purple-300';
                        } else {
                            codeBadge.textContent = '- None -';
                            codeBadge.className = 'inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-black font-mono bg-slate-100 text-slate-400 italic';
                        }
                    }

                    if (statusBadge) {
                        statusBadge.textContent = status.toUpperCase();
                        statusBadge.className = 'inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-mono font-bold uppercase ' +
                            (status === 'present' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : (status === 'absent' ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-amber-100 text-amber-800 border border-amber-300'));
                    }

                    if (evalBadge && data.evaluation_status) {
                        evalBadge.textContent = data.evaluation_status;
                        if (data.evaluation_status === 'EVALUATED') {
                            evalBadge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-purple-100 text-purple-900 border border-purple-200';
                        } else if (data.evaluation_status === 'EVALUATION_PENDING') {
                            evalBadge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-blue-100 text-blue-900 border border-blue-200';
                        } else if (data.evaluation_status === 'NOT_ELIGIBLE') {
                            evalBadge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-red-100 text-red-800 border border-red-200';
                        } else {
                            evalBadge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200';
                        }
                    }

                    this.showToast(data.message || 'Attendance status updated.');
                } catch (err) {
                    this.showToast(err.message || 'Error updating attendance.', true);
                }
            }
        };
    }
</script>
@endsection
