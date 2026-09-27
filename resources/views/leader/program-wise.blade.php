@extends('layouts.leader')

@section('title', 'Program Wise Students - Leader Panel')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-serif text-gray-900">Program Wise Students</h1>
            <p class="text-xs text-gray-500 mt-1 font-sans">View all participants registered in {{ $group->name }} by program</p>
        </div>
        <div class="flex items-center gap-2">
            @if($selectedProgram)
                <button onclick="window.print()" class="px-5 py-2 bg-brand-orange text-white rounded-xl text-xs font-bold hover:bg-orange-600 transition shadow-xs flex items-center gap-1.5 font-sans">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </button>
            @endif
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs">
        <form method="GET" action="{{ route('leader.programs-wise') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-5">
                <label class="block text-xs font-semibold text-gray-600 mb-1 font-sans">Zone</label>
                <select name="zone" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange font-sans">
                    <option value="">-- All Zones --</option>
                    @foreach($zones as $zKey => $zVal)
                        @php
                            $zoneName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                            $zoneValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                        @endphp
                        <option value="{{ $zoneValue }}" {{ ($selectedZone ?? '') === $zoneValue ? 'selected' : '' }}>{{ $zoneName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-5">
                <label class="block text-xs font-semibold text-gray-600 mb-1 font-sans">Program</label>
                <select name="program" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange font-sans">
                    <option value="">-- Select Program --</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ (string)$selectedProgramId === (string)$prog->id ? 'selected' : '' }}>{{ $prog->name }} (Code: {{ $prog->code ?: $prog->id }})</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="w-full bg-brand-orange text-white py-2.5 px-4 rounded-xl text-sm font-semibold hover:bg-orange-600 transition flex items-center justify-center gap-2 font-sans">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    View
                </button>
            </div>
        </form>
    </div>

    @if($selectedProgram)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <!-- Header matching screenshot -->
            <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs font-sans">
                <div>
                    <span class="text-gray-400 font-medium">Name:</span>
                    <span class="font-bold text-gray-800 ml-1">{{ $selectedProgram->name }}</span>
                </div>
                <div>
                    <span class="text-gray-400 font-medium">Program Code:</span>
                    <span class="font-bold text-gray-800 ml-1 font-mono">{{ $selectedProgram->code ?: $selectedProgram->id }}</span>
                </div>
                <div>
                    <span class="text-gray-400 font-medium">Zone:</span>
                    <span class="font-bold text-gray-800 ml-1">{{ $selectedProgram->eligibility ?? 'A Zone' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 font-medium">Type:</span>
                    <span class="font-bold text-gray-800 ml-1">{{ ucfirst($selectedProgram->type ?? 'Individual') }}</span>
                </div>
            </div>

            <!-- Table matching screenshot -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm font-sans">
                    <thead>
                        <tr class="bg-gray-50/75 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider font-sans">
                            <th class="px-4 py-3.5 text-center w-12">No</th>
                            <th class="px-4 py-3.5">Chest No</th>
                            <th class="px-6 py-3.5">Name</th>
                            <th class="px-4 py-3.5">Team</th>
                            <th class="px-4 py-3.5">Zone</th>
                            <th class="px-4 py-3.5">Class</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($selectedProgram->entries as $index => $entry)
                            @php
                                $isGroup = $entry->isGroupEntry();
                                $leaderStudent = $isGroup
                                    ? ($entry->participants()->wherePivot('role', 'captain')->first() ?? $entry->student ?? $entry->participants()->first())
                                    : $entry->student;
                                $displayChest = ltrim((string)($leaderStudent?->student_id ?: $entry->chest_number), '#');
                                $displayName = $leaderStudent?->name ?: ('Chest ' . ltrim((string)$entry->chest_number, '#'));
                                $displayZone = $leaderStudent?->category ?? ($selectedProgram->eligibility ?? 'A Zone');
                                $displayClass = $leaderStudent?->class_level ?? '-';
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-4 py-3.5 text-center text-gray-500 text-xs font-medium font-mono">{{ $index + 1 }}</td>
                                <td class="px-4 py-3.5 text-gray-700 font-mono text-xs">{{ $displayChest }}</td>
                                <td class="px-6 py-3.5 text-gray-900 font-medium">
                                    <span>{{ $displayName }}</span>
                                    @if($isGroup)
                                        <span class="text-[10px] text-gray-400 block font-sans font-normal">
                                            (Group Leader • {{ $entry->participants->count() }} members)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $group->name }}</td>
                                <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $displayZone }}</td>
                                <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $displayClass }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    No students registered from your team for this program.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-red-50 text-brand-orange mx-auto flex items-center justify-center mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <h3 class="text-base font-serif font-bold text-gray-900">Select a program to view students</h3>
            <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto font-sans">Select a program from the box above to view registered students.</p>
        </div>
    @endif
</div>
@endsection
