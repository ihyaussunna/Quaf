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
                Unified live festival call desk, attendance tracking, and anonymous participant code management.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('greenroom.call-list') }}" target="_blank"
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

    <!-- Section 10: 6 Live Summary Counter Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
        <!-- 1. Total Call List -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-2xs">
            <span class="text-slate-500 font-mono block text-[11px] uppercase font-bold">Total Call List</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block font-mono">{{ number_format($stats['total']) }}</span>
            <span class="text-[10px] text-slate-400 font-mono">Registered Entries</span>
        </div>

        <!-- 2. Present -->
        <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 text-emerald-900 shadow-2xs">
            <span class="font-mono block text-emerald-700 text-[11px] uppercase font-bold">Total Present</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($stats['present']) }}</span>
            <span class="text-[10px] text-emerald-600 font-mono">Eligible for Jury</span>
        </div>

        <!-- 3. Absent -->
        <div class="p-4 rounded-2xl bg-red-50/70 border border-red-200 text-red-900 shadow-2xs">
            <span class="font-mono block text-red-700 text-[11px] uppercase font-bold">Total Absent</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($stats['absent']) }}</span>
            <span class="text-[10px] text-red-600 font-mono">Excluded from Jury</span>
        </div>

        <!-- 4. Pending Calls / Waiting -->
        <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 text-amber-900 shadow-2xs">
            <span class="font-mono block text-amber-700 text-[11px] uppercase font-bold">Pending Calls</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($stats['waiting']) }}</span>
            <span class="text-[10px] text-amber-600 font-mono">Waiting Attendance</span>
        </div>

        <!-- 5. Pending Evaluation -->
        <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200 text-blue-900 shadow-2xs">
            <span class="font-mono block text-blue-700 text-[11px] uppercase font-bold">Pending Eval</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($stats['pending_evaluation']) }}</span>
            <span class="text-[10px] text-blue-600 font-mono">Present without Marks</span>
        </div>

        <!-- 6. Completed Evaluation -->
        <div class="p-4 rounded-2xl bg-purple-50/70 border border-purple-200 text-purple-900 shadow-2xs">
            <span class="font-mono block text-purple-700 text-[11px] uppercase font-bold">Completed Eval</span>
            <span class="text-2xl font-black mt-1 block font-mono">{{ number_format($stats['evaluated']) }}</span>
            <span class="text-[10px] text-purple-600 font-mono">Score Submitted</span>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs">
        <form method="GET" action="{{ route('admin.call-list.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 items-end">
            <!-- Search Keyword -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1">Search Participant / Code / Chest</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Student name, Code AA, Chest #..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
            </div>

            <!-- Program Filter -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Program</label>
                <select name="program_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Programs</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}" {{ (string)$programId === (string)$p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Zone Filter -->
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

            <!-- Group Filter -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Team / Group</label>
                <select name="group_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Teams</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" {{ (string)$groupId === (string)$g->id ? 'selected' : '' }}>
                            {{ $g->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Stage Filter -->
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

            <!-- Attendance Filter -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Attendance</label>
                <select name="attendance" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Statuses</option>
                    <option value="present" {{ ($attendance ?? '') === 'present' ? 'selected' : '' }}>Present</option>
                    <option value="absent" {{ ($attendance ?? '') === 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="waiting" {{ ($attendance ?? '') === 'waiting' ? 'selected' : '' }}>Waiting</option>
                </select>
            </div>

            <!-- Evaluation Status Filter -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Evaluation Status</label>
                <select name="eval_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Evaluations</option>
                    <option value="evaluated" {{ ($evalStatus ?? '') === 'evaluated' ? 'selected' : '' }}>Evaluated</option>
                    <option value="pending" {{ ($evalStatus ?? '') === 'pending' ? 'selected' : '' }}>Pending Evaluation</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5 font-sora">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Apply Filter</span>
                </button>
                <a href="{{ route('admin.call-list.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Section 10: Detailed Call List & Attendance Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 font-sora">
                    Live Call List & Attendance Records
                </h3>
                <p class="text-[11px] text-slate-500 font-mono mt-0.5">
                    Authorized staff view displaying student identity, team affiliation, and instant attendance controls.
                </p>
            </div>
            <div class="text-xs font-mono text-slate-500">
                Showing {{ $entries->firstItem() ?? 0 }}-{{ $entries->lastItem() ?? 0 }} of {{ $entries->total() }} entries
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sora">
                <thead>
                    <tr class="bg-slate-100/75 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <th class="px-4 py-3">Program</th>
                        <th class="px-4 py-3 text-center w-28">Code</th>
                        <th class="px-4 py-3">Student (Admin View)</th>
                        <th class="px-4 py-3">Group</th>
                        <th class="px-4 py-3 text-center w-36">Attendance</th>
                        <th class="px-4 py-3 text-center w-64">Attendance Action</th>
                        <th class="px-4 py-3 text-center w-36">Evaluation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($entries as $entry)
                        <tr id="entry-row-{{ $entry->id }}"
                            class="hover:bg-slate-50/75 transition-colors {{ $entry->attendance_status === 'absent' ? 'opacity-60 bg-red-50/20' : '' }}">
                            <!-- Program -->
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.programs.show', $entry->program_id) }}" class="font-bold text-slate-900 hover:text-[#be1e2d] transition-colors">
                                    {{ $entry->program?->name }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                    ID: {{ $entry->program?->code }} &bull; {{ $entry->program?->category?->name ?? 'General' }} &bull; {{ $entry->program?->stage?->name ?? 'Stage TBA' }}
                                </div>
                            </td>

                            <!-- Participant Code -->
                            <td class="px-4 py-3 text-center">
                                <span id="code-badge-{{ $entry->id }}"
                                      class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-black font-mono {{ $entry->code_letter ? 'bg-purple-100 text-purple-900 border border-purple-300' : 'bg-slate-100 text-slate-400 italic' }}">
                                    {{ $entry->code_letter ? 'Code ' . $entry->code_letter : '- None -' }}
                                </span>
                            </td>

                            <!-- Student Identity (Admin Only) -->
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $entry->student?->name ?? 'Participant' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">
                                    ID: {{ $entry->student?->student_id ?? '-' }} &bull; Chest: #{{ $entry->chest_number }}
                                </div>
                            </td>

                            <!-- Group / Team -->
                            <td class="px-4 py-3 font-semibold text-slate-700">
                                {{ $entry->student?->group?->name ?? $entry->group?->name ?? '-' }}
                            </td>

                            <!-- Attendance Status Badge -->
                            <td class="px-4 py-3 text-center">
                                <span id="status-badge-{{ $entry->id }}"
                                      class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-mono font-bold uppercase
                                      {{ $entry->attendance_status === 'present' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($entry->attendance_status === 'absent' ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-amber-100 text-amber-800 border border-amber-300') }}">
                                    {{ strtoupper($entry->attendance_status ?? 'waiting') }}
                                </span>
                            </td>

                            <!-- Attendance Action (Instant Toggle) -->
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-1.5" id="btn-group-{{ $entry->id }}">
                                    <button type="button"
                                            @click="toggleAttendance({{ $entry->id }}, 'present', '{{ route('admin.call-list.attendance', $entry->id) }}')"
                                            id="btn-present-{{ $entry->id }}"
                                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all shadow-2xs {{ $entry->attendance_status === 'present' ? 'bg-emerald-600 text-white font-black' : 'bg-slate-100 hover:bg-emerald-50 text-slate-700 border border-slate-200' }}">
                                        PRESENT
                                    </button>
                                    <button type="button"
                                            @click="toggleAttendance({{ $entry->id }}, 'absent', '{{ route('admin.call-list.attendance', $entry->id) }}')"
                                            id="btn-absent-{{ $entry->id }}"
                                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all shadow-2xs {{ $entry->attendance_status === 'absent' ? 'bg-red-600 text-white font-black' : 'bg-slate-100 hover:bg-red-50 text-slate-700 border border-slate-200' }}">
                                        ABSENT
                                    </button>
                                    <button type="button"
                                            @click="toggleAttendance({{ $entry->id }}, 'waiting', '{{ route('admin.call-list.attendance', $entry->id) }}')"
                                            id="btn-waiting-{{ $entry->id }}"
                                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all shadow-2xs {{ $entry->attendance_status === 'waiting' || empty($entry->attendance_status) ? 'bg-amber-500 text-white font-black' : 'bg-slate-100 hover:bg-amber-50 text-slate-700 border border-slate-200' }}">
                                        WAITING
                                    </button>
                                </div>
                            </td>

                            <!-- Evaluation Status -->
                            <td class="px-4 py-3 text-center">
                                <span id="eval-badge-{{ $entry->id }}"
                                      class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase
                                      @if($entry->evaluation_status === 'EVALUATED') bg-purple-100 text-purple-900 border border-purple-200
                                      @elseif($entry->evaluation_status === 'EVALUATION_PENDING') bg-blue-100 text-blue-900 border border-blue-200
                                      @elseif($entry->evaluation_status === 'NOT_ELIGIBLE') bg-red-100 text-red-800 border border-red-200
                                      @else bg-slate-100 text-slate-600 border border-slate-200 @endif">
                                    {{ $entry->evaluation_status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-slate-400 font-mono text-xs">
                                No call list records match the specified filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($entries->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function adminCallListManager() {
        return {
            toast: {
                show: false,
                message: '',
                isError: false,
                timeout: null
            },
            showToast(msg, isErr = false) {
                this.toast.message = msg;
                this.toast.isError = isErr;
                this.toast.show = true;
                if (this.toast.timeout) clearTimeout(this.toast.timeout);
                this.toast.timeout = setTimeout(() => {
                    this.toast.show = false;
                }, 3500);
            },
            async toggleAttendance(entryId, status, actionUrl) {
                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const res = await fetch(actionUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
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
