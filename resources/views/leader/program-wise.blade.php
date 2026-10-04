@extends('layouts.leader')

@section('title', 'Program Wise Students - Leader Panel')

@section('content')
<div class="space-y-6">
    <style>
        @media print {
            #sidebar, aside, header, nav, .filter-card, .no-print, button, form, .mobile-nav {
                display: none !important;
            }
            body, html {
                background: #ffffff !important;
                color: #000000 !important;
                overflow: visible !important;
                height: auto !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
                max-width: 100% !important;
            }
            .print-only {
                display: block !important;
            }
            .avoid-break {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            tr {
                page-break-inside: avoid !important;
            }
            thead {
                display: table-header-group;
            }
        }
        @media screen {
            .print-only {
                display: none !important;
            }
        }
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
    </style>

    <!-- Official Print Header (Visible only when printing or saving to PDF) -->
    <div class="print-only mb-6">
        @include('partials.print-pdf-header', [
            'title' => 'Program Wise Students Roster',
            'subtitle' => 'Ihyaussunna Students Union, Markazu Saquafathi Sunniyya',
            'group' => $group,
            'filterText' => $selectedZone ? 'Zone: ' . $selectedZone : 'All Zones'
        ])
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold font-sora text-gray-900">Program Wise Students</h1>
                <span class="px-3 py-1 rounded-full bg-slate-900 text-amber-400 font-bold text-xs font-mono">
                    {{ $displayedPrograms->count() }} Competitions
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1 font-sora">
                View all participants registered in {{ $group->name }} by program
                @if(isset($totalDisplayedEntries) && $totalDisplayedEntries > 0)
                    • <span class="font-bold text-gray-700">{{ $totalDisplayedEntries }} Total Registrations</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-5 py-2.5 bg-[#be1e2d] hover:bg-[#a01624] text-white rounded-xl text-xs font-bold font-sora transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print / Save PDF</span>
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs filter-card no-print">
        <form method="GET" action="{{ route('leader.programs-wise') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <!-- Zone Filter -->
            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1 font-sora">Zone</label>
                <select name="zone" onchange="if(this.form.program) { this.form.program.value = ''; } this.form.submit()" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange font-sora">
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

            <!-- Program Filter -->
            <div class="md:col-span-5">
                <label class="block text-xs font-semibold text-gray-600 mb-1 font-sora">Program</label>
                <select name="program" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange font-sora">
                    <option value="">-- All Programs (View All) --</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ (string)$selectedProgramId === (string)$prog->id ? 'selected' : '' }}>
                            {{ $prog->name }}
                            @if(isset($prog->my_entries_count) && $prog->my_entries_count > 0)
                                (✓ {{ $prog->my_entries_count }} Registered)
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="md:col-span-3 flex items-center gap-2">
                <button type="submit" class="flex-1 bg-brand-orange text-white py-2.5 px-4 rounded-xl text-sm font-semibold hover:bg-orange-600 transition flex items-center justify-center gap-2 font-sora">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Filter
                </button>
                @if($selectedZone || $selectedProgramId)
                    <a href="{{ route('leader.programs-wise') }}" class="px-3 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-sora font-semibold transition" title="Clear Filters">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Programs List -->
    @if($selectedProgram)
        <!-- Single Program Focused View -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden avoid-break">
            <!-- Program Header -->
            <div class="px-6 py-4 bg-gray-50/75 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4 text-xs font-sora">
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 rounded-lg bg-gray-900 text-amber-400 font-mono font-bold text-xs">
                        {{ $selectedProgram->code ?: $selectedProgram->id }}
                    </span>
                    <div>
                        <h2 class="font-bold text-gray-900 text-sm inline">{{ $selectedProgram->name }}</h2>
                        @if($selectedProgram->malayalam_name)
                            <span class="text-xs text-gray-500 font-malayalam ml-2">{{ $selectedProgram->malayalam_name }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ ($selectedProgram->type ?? 'individual') === 'group' ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                        {{ ucfirst($selectedProgram->type ?? 'Individual') }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-mono bg-gray-100 text-gray-700 border border-gray-200">
                        {{ $selectedProgram->zone?->name ?? $selectedProgram->eligibility ?? 'A Zone' }}
                    </span>
                    @if($selectedProgram->stage)
                        <span class="px-2.5 py-0.5 rounded text-[10px] bg-emerald-50 text-emerald-800 border border-emerald-200">
                            {{ $selectedProgram->stage->name }}
                        </span>
                    @endif
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-50 text-amber-900 border border-amber-200">
                        {{ $selectedProgram->entries->count() }} Registered
                    </span>
                </div>
            </div>

            <!-- Table of Students -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm font-sora">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider font-sora">
                            <th class="px-4 py-3.5 text-center w-12">No</th>
                            <th class="px-4 py-3.5">Chest No</th>
                            <th class="px-6 py-3.5">Participant Name</th>
                            <th class="px-4 py-3.5">Team</th>
                            <th class="px-4 py-3.5">Zone</th>
                            <th class="px-4 py-3.5">Class</th>
                            <th class="px-4 py-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($selectedProgram->entries as $index => $entry)
                            @php
                                $isGroup = $entry->isGroupEntry();
                                $leaderStudent = $isGroup
                                    ? ($entry->participants()->wherePivot('role', 'captain')->first() ?? $entry->student ?? $entry->participants()->first())
                                    : $entry->student;
                                $displayChest = ltrim((string)($entry->chest_number ?: $leaderStudent?->student_id), '#');
                                $displayName = $leaderStudent?->name ?: ('Chest ' . ltrim((string)$entry->chest_number, '#'));
                                $displayZone = $leaderStudent?->category ?? ($selectedProgram->zone?->name ?? ($selectedProgram->eligibility ?? 'A Zone'));
                                $displayClass = $leaderStudent?->class_level ?? '-';
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-4 py-3.5 text-center text-gray-500 text-xs font-medium font-mono">{{ $index + 1 }}</td>
                                <td class="px-4 py-3.5 text-gray-700 font-mono text-xs font-bold">{{ $displayChest ?: '—' }}</td>
                                <td class="px-6 py-3.5 text-gray-900 font-medium">
                                    <span class="font-bold block text-gray-900">{{ $displayName }}</span>
                                    @if($isGroup)
                                        @if($entry->participants->isNotEmpty())
                                            <div class="text-[11px] text-gray-500 font-mono mt-0.5">
                                                Members ({{ $entry->participants->count() }}): {{ $entry->participants->pluck('name')->implode(', ') }}
                                            </div>
                                        @else
                                            <span class="text-[10px] text-gray-400 block font-sora">Group Team</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $group->name }}</td>
                                <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $displayZone }}</td>
                                <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $displayClass }}</td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    @if($entry->status === 'verified' || $entry->status === 'confirmed')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Verified</span>
                                    @elseif($entry->status === 'pending')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 capitalize">{{ $entry->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <div class="max-w-md mx-auto space-y-3">
                                        <p class="text-sm font-medium">No students registered from your team for this program.</p>
                                        <a href="{{ route('leader.registrations', ['zone' => $selectedProgram->zone?->name ?? $selectedProgram->eligibility, 'program_id' => $selectedProgram->id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-orange text-white rounded-xl text-xs font-semibold hover:bg-orange-600 transition shadow-xs">
                                            + Enroll Students in this Program
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($displayedPrograms->isNotEmpty())
        <!-- All Registered Programs List (Default Multi-View) -->
        <div class="space-y-6">
            @foreach($displayedPrograms as $prog)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden avoid-break">
                    <!-- Program Header -->
                    <div class="px-6 py-4 bg-gray-50/75 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4 text-xs font-sora">
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 rounded-lg bg-gray-900 text-amber-400 font-mono font-bold text-xs">
                                {{ $prog->code ?: $prog->id }}
                            </span>
                            <div>
                                <h2 class="font-bold text-gray-900 text-sm inline">{{ $prog->name }}</h2>
                                @if($prog->malayalam_name)
                                    <span class="text-xs text-gray-500 font-malayalam ml-2">{{ $prog->malayalam_name }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ ($prog->type ?? 'individual') === 'group' ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                {{ ucfirst($prog->type ?? 'Individual') }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-mono bg-gray-100 text-gray-700 border border-gray-200">
                                {{ $prog->zone?->name ?? $prog->eligibility ?? 'A Zone' }}
                            </span>
                            @if($prog->stage)
                                <span class="px-2.5 py-0.5 rounded text-[10px] bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    {{ $prog->stage->name }}
                                </span>
                            @endif
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-50 text-amber-900 border border-amber-200">
                                {{ $prog->entries->count() }} Registered
                            </span>
                        </div>
                    </div>

                    <!-- Students Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm font-sora">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider font-sora">
                                    <th class="px-4 py-3 text-center w-12">No</th>
                                    <th class="px-4 py-3">Chest No</th>
                                    <th class="px-6 py-3">Participant Name</th>
                                    <th class="px-4 py-3">Team</th>
                                    <th class="px-4 py-3">Zone</th>
                                    <th class="px-4 py-3">Class</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($prog->entries as $index => $entry)
                                    @php
                                        $isGroup = $entry->isGroupEntry();
                                        $leaderStudent = $isGroup
                                            ? ($entry->participants()->wherePivot('role', 'captain')->first() ?? $entry->student ?? $entry->participants()->first())
                                            : $entry->student;
                                        $displayChest = ltrim((string)($entry->chest_number ?: $leaderStudent?->student_id), '#');
                                        $displayName = $leaderStudent?->name ?: ('Chest ' . ltrim((string)$entry->chest_number, '#'));
                                        $displayZone = $leaderStudent?->category ?? ($prog->zone?->name ?? ($prog->eligibility ?? 'A Zone'));
                                        $displayClass = $leaderStudent?->class_level ?? '-';
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-4 py-3 text-center text-gray-500 text-xs font-medium font-mono">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 text-gray-700 font-mono text-xs font-bold">{{ $displayChest ?: '—' }}</td>
                                        <td class="px-6 py-3 text-gray-900 font-medium">
                                            <span class="font-bold block text-gray-900">{{ $displayName }}</span>
                                            @if($isGroup)
                                                @if($entry->participants->isNotEmpty())
                                                    <div class="text-[11px] text-gray-500 font-mono mt-0.5">
                                                        Members ({{ $entry->participants->count() }}): {{ $entry->participants->pluck('name')->implode(', ') }}
                                                    </div>
                                                @else
                                                    <span class="text-[10px] text-gray-400 block font-sora">Group Team</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $group->name }}</td>
                                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $displayZone }}</td>
                                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $displayClass }}</td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            @if($entry->status === 'verified' || $entry->status === 'confirmed')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Verified</span>
                                            @elseif($entry->status === 'pending')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 capitalize">{{ $entry->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-6 text-center text-gray-400 text-xs">
                                            No students registered for this program yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-orange-50 text-brand-orange mx-auto flex items-center justify-center mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <h3 class="text-base font-sora font-bold text-gray-900">No registered students found</h3>
            <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto font-sora">
                {{ $selectedZone ? 'No students from your team are registered for ' . $selectedZone . ' competitions yet.' : 'Your team has not registered students in any competitions yet.' }}
            </p>
            <div class="mt-4">
                <a href="{{ route('leader.registrations') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-orange text-white rounded-xl text-xs font-semibold hover:bg-orange-600 transition shadow-xs font-sora">
                    + Enroll Students in Competitions
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
