@extends('layouts.admin', ['title' => 'Program Wise Students | QUAF 09'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Program Wise Students</h1>
            <p class="text-xs text-slate-500 mt-0.5">Filter and view enrolled participants by competition program.</p>
        </div>
        <div class="flex items-center gap-2.5">
            @if($selectedProgram)
                <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-[#be1e2d] text-white font-bold text-xs uppercase tracking-wider hover:bg-[#a01624] transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Print Sheet</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Filter Form (Hide on print) -->
    <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs print:hidden">
        <form method="GET" action="{{ route('admin.programs.program-wise') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Select Zone</label>
                <select name="zone" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($zones as $zKey => $zVal)
                        @php
                            $zoneName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                            $zoneValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                        @endphp
                        <option value="{{ $zoneValue }}" {{ ($selectedZone ?? '') === $zoneValue ? 'selected' : '' }}>{{ $zoneName }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Select Program</label>
                <select name="program" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">-- Choose Competition Program --</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ $selectedProgramId == $prog->id ? 'selected' : '' }}>
                            {{ $prog->name }} — ID: {{ $prog->code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs uppercase tracking-wider transition-colors shadow-sm">
                    View Enrolled Students
                </button>
            </div>
        </form>
    </div>

    <!-- Selected Program & Students Table -->
    @if($selectedProgram)
        <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-2xs">
            <!-- Program Header (Matches Screenshot) -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4 text-xs font-sora">
                <div>
                    <span class="text-slate-500">Id:</span>
                    <span class="font-bold text-slate-900 ml-1">{{ $selectedProgram->code }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Name:</span>
                    <span class="font-bold text-slate-900 ml-1">{{ $selectedProgram->name }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Type:</span>
                    <span class="font-bold text-slate-900 ml-1 capitalize">{{ $selectedProgram->type }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Stage:</span>
                    <span class="font-bold text-slate-900 ml-1">{{ $selectedProgram->stage ? 'Stage' : 'Non-stage' }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Zone:</span>
                    <span class="font-bold text-slate-900 ml-1">{{ $selectedProgram->eligibility ?? 'A Zone' }}</span>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-sora">
                    <thead class="bg-slate-50/50 text-slate-500 uppercase border-b border-slate-200 text-[11px] font-semibold">
                        <tr>
                            <th class="px-6 py-3.5">No</th>
                            <th class="px-6 py-3.5">Student Id</th>
                            <th class="px-6 py-3.5">Name</th>
                            <th class="px-6 py-3.5">Team</th>
                            <th class="px-6 py-3.5">Zone</th>
                            <th class="px-6 py-3.5">Class</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($selectedProgram->entries as $idx => $entry)
                            @php
                                $isGroup = $entry->isGroupEntry();
                                $leaderStudent = $isGroup
                                    ? ($entry->participants()->wherePivot('role', 'captain')->first() ?? $entry->student ?? $entry->participants()->first())
                                    : $entry->student;
                                $displayStudentId = $leaderStudent?->student_id ?? ($entry->chest_number ? '#' . $entry->chest_number : '—');
                                $displayName = $leaderStudent?->name ?? ($isGroup ? ($entry->group?->name . ' Team') : '—');
                                $displayZone = $leaderStudent?->category ?? ($selectedProgram->zone?->name ?? ($selectedProgram->eligibility ?? 'Mix Zone'));
                                $displayClass = $leaderStudent?->class_level ?? '—';
                                $groupName = $entry->group?->name ?? $leaderStudent?->group?->name ?? '—';
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-3.5 font-bold text-slate-900">{{ $idx + 1 }}</td>
                                <td class="px-6 py-3.5 font-mono font-medium text-slate-600">
                                    {{ $displayStudentId }}
                                </td>
                                <td class="px-6 py-3.5 font-semibold text-slate-900">
                                    <div>
                                        <span>{{ $displayName }}</span>
                                        @if($isGroup)
                                            <span class="text-[10px] text-slate-500 font-mono font-normal block">
                                                (Group Leader • {{ $entry->participants->count() }} members)
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-3.5 font-medium">{{ $groupName }}</td>
                                <td class="px-6 py-3.5 text-slate-500">{{ $displayZone }}</td>
                                <td class="px-6 py-3.5 text-slate-500">{{ $displayClass }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-400">No participants registered for this competition yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($selectedProgramId)
        <div class="p-8 text-center bg-white rounded-2xl border border-slate-200 text-slate-400 text-xs">
            Program not found.
        </div>
    @else
        <div class="p-12 text-center bg-white rounded-2xl border border-slate-200 text-slate-400 text-xs">
            Please select a competition program above to view registered participants.
        </div>
    @endif
</div>
@endsection
