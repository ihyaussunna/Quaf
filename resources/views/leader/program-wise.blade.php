@extends('layouts.leader')

@section('title', 'Program Wise Students - Leader Panel')

@section('content')
@php
    $programsCollection = $selectedProgram ? collect([$selectedProgram]) : $displayedPrograms;
    $allProgramIdsToDisplay = $programsCollection->pluck('id');
@endphp
<div class="space-y-6" x-data="{
    allProgIds: @json($allProgramIdsToDisplay->values()),
    excludedProgIds: [],
    isProgExcluded(id) {
        return this.excludedProgIds.includes(Number(id));
    },
    toggleProg(id) {
        const numId = Number(id);
        if (this.excludedProgIds.includes(numId)) {
            this.excludedProgIds = this.excludedProgIds.filter(x => x !== numId);
        } else {
            this.excludedProgIds.push(numId);
        }
    },
    includeAllProgs() {
        this.excludedProgIds = [];
    },
    excludeAllProgs() {
        this.excludedProgIds = [...this.allProgIds];
    },
    get includedCount() {
        return this.allProgIds.length - this.excludedProgIds.length;
    },
    printPdf() {
        if (this.includedCount === 0) {
            alert('Please include at least one program section to generate the PDF.');
            return;
        }
        window.print();
    }
}">
    <style>
        @media print {
            #sidebar, aside, header, nav, .filter-card, .no-print, button, form, .mobile-nav {
                display: none !important;
            }
            .print-hidden-section {
                display: none !important;
            }
            body, html {
                background: #ffffff !important;
                color: #000000 !important;
                overflow: visible !important;
                height: auto !important;
                font-size: 11px !important;
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
            .avoid-break, tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            thead {
                display: table-header-group !important;
            }
            .program-wise-table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            .program-wise-table th,
            .program-wise-table td {
                border: 1px solid #64748b !important;
                padding: 5px 6px !important;
                color: #0f172a !important;
            }
            .program-wise-table th {
                background-color: #f1f5f9 !important;
                font-weight: 700 !important;
            }
        }
        @media screen {
            .print-only {
                display: none !important;
            }
        }
        @page {
            size: A4 portrait;
            margin: 8mm;
        }
    </style>

    <!-- Official Print Header (Visible only when printing or saving to PDF) -->
    <div class="print-only mb-4">
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
                    {{ $programsCollection->count() }} Competitions
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
            <button @click="printPdf()" class="px-5 py-2.5 bg-[#be1e2d] hover:bg-[#a01624] text-white rounded-xl text-xs font-bold font-sora transition shadow-sm flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print / Save PDF</span>
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs filter-card no-print">
        <form method="GET" action="{{ route('leader.programs-wise') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <!-- Zone Filter -->
            <div class="md:col-span-3">
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

            <!-- Status Filter -->
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-gray-600 mb-1 font-sora">Registration Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange font-sora">
                    <option value="all" {{ ($statusFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Programs</option>
                    <option value="registered" {{ ($statusFilter ?? '') === 'registered' ? 'selected' : '' }}>Registered Only</option>
                    <option value="pending" {{ ($statusFilter ?? '') === 'pending' ? 'selected' : '' }}>Pending / Incomplete Only</option>
                    <option value="completed" {{ ($statusFilter ?? '') === 'completed' ? 'selected' : '' }}>Completed Only</option>
                </select>
            </div>

            <!-- Program Filter -->
            <div class="md:col-span-4">
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
            <div class="md:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 bg-brand-orange text-white py-2.5 px-4 rounded-xl text-sm font-semibold hover:bg-orange-600 transition flex items-center justify-center gap-2 font-sora">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Filter
                </button>
                @if($selectedZone || $selectedProgramId || ($statusFilter ?? 'all') !== 'all')
                    <a href="{{ route('leader.programs-wise') }}" class="px-3 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-sora font-semibold transition" title="Clear Filters">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- PDF Section Exclusion Toolbar (Visible on screen, hidden on print) -->
    <div class="no-print bg-slate-900 text-white rounded-2xl p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-0.5">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#be1e2d] text-white">PDF SECTIONS</span>
                <span class="text-xs font-sora font-semibold text-slate-200">
                    <strong class="text-amber-400 font-mono" x-text="includedCount"></strong> of <span class="font-mono text-slate-300" x-text="allProgIds.length"></span> programs selected for PDF
                </span>
            </div>
            <p class="text-[11px] font-sora text-slate-400">
                Omit / exclude unwanted program sections from the PDF by unchecking them before downloading or printing.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs font-sora shrink-0">
            <button type="button" @click="includeAllProgs()" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-medium transition cursor-pointer">
                Select All
            </button>
            <button type="button" @click="excludeAllProgs()" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white font-medium transition cursor-pointer">
                Deselect All
            </button>
            <button type="button" @click="printPdf()" class="px-4 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold transition flex items-center gap-2 shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Download / Print PDF</span>
            </button>
        </div>
    </div>

    <!-- Empty State for Single Program Selection -->
    @if($selectedProgram && $selectedProgram->entries->isEmpty())
        <div class="p-6 text-center text-gray-500 bg-amber-50/60 rounded-2xl border border-amber-200 no-print">
            <p class="text-sm font-medium text-gray-700 font-sora">No students registered from your team for this program.</p>
            <a href="{{ route('leader.registrations', ['zone' => $selectedProgram->zone?->name ?? $selectedProgram->eligibility, 'program_id' => $selectedProgram->id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-orange text-white rounded-xl text-xs font-semibold hover:bg-orange-600 transition shadow-xs mt-3 font-sora">
                + Enroll Students in this Program
            </a>
        </div>
    @endif

    <!-- Program-Wise Comprehensive Table (Print & Screen View) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="program-wise-table w-full text-left text-xs font-sora">
                <thead>
                    <tr class="bg-slate-50 border-b-2 border-slate-200 text-[11px] font-bold text-slate-700 uppercase tracking-tight">
                        <th class="no-print px-3 py-3 text-center w-12">In PDF</th>
                        <th class="px-3 py-3 text-left w-20">Program id</th>
                        <th class="px-4 py-3 text-left min-w-[140px]">Program</th>
                        <th class="px-3 py-3 text-center w-24">Type</th>
                        <th class="px-3 py-3 text-center w-20">zone</th>
                        <th class="px-3 py-3 text-center w-20">Stage/off</th>
                        <th class="px-3 py-3 text-center w-14">limit</th>
                        <th class="px-3 py-3 text-center w-36">registerd status</th>
                        <th class="px-4 py-3 text-left min-w-[180px]">Students Name</th>
                        <th class="no-print px-3 py-3 text-center w-20">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($programsCollection as $prog)
                        @php
                            $isGroupType = ($prog->type ?? 'individual') === 'group';
                            $isStage = (bool)($prog->is_stage || ($prog->stage_id && strtolower($prog->stage?->name ?? '') !== 'off stage'));
                            $regCount = $prog->entries->count();
                            $limit = (int)($prog->limit ?: 1);
                            $pending = max(0, $limit - $regCount);
                            $zoneName = $prog->zone?->name ?? ($prog->eligibility ?? 'A Zone');
                            $progId = $prog->code ?: (string)$prog->id;
                        @endphp
                        <tr :class="isProgExcluded({{ $prog->id }}) ? 'opacity-40 bg-slate-50/60 border-dashed print-hidden-section' : ''"
                            class="hover:bg-slate-50/60 transition-colors avoid-break">

                            <!-- In PDF Checkbox (Screen Only) -->
                            <td class="no-print px-3 py-3 text-center align-top">
                                <label class="inline-flex items-center cursor-pointer" title="Include/Exclude from PDF">
                                    <input type="checkbox"
                                           :checked="!isProgExcluded({{ $prog->id }})"
                                           @change="toggleProg({{ $prog->id }})"
                                           class="w-4 h-4 text-brand-orange rounded border-slate-300 focus:ring-brand-orange/30 cursor-pointer">
                                </label>
                            </td>

                            <!-- Program id -->
                            <td class="px-3 py-3 font-mono font-bold text-xs text-slate-900 whitespace-nowrap align-top">
                                {{ $progId }}
                            </td>

                            <!-- Program -->
                            <td class="px-4 py-3 align-top">
                                <div class="font-bold text-xs sm:text-sm text-slate-900 leading-snug">
                                    {{ $prog->name }}
                                </div>
                                @if($prog->malayalam_name)
                                    <div class="text-[11px] text-slate-500 font-malayalam mt-0.5 leading-tight">
                                        {{ $prog->malayalam_name }}
                                    </div>
                                @endif
                            </td>

                            <!-- Type -->
                            <td class="px-3 py-3 text-center whitespace-nowrap align-top">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $isGroupType ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                    {{ ucfirst($prog->type ?? 'Individual') }}
                                </span>
                            </td>

                            <!-- zone -->
                            <td class="px-3 py-3 text-center font-semibold text-xs text-slate-700 whitespace-nowrap align-top">
                                {{ $zoneName }}
                            </td>

                            <!-- Stage/off -->
                            <td class="px-3 py-3 text-center whitespace-nowrap align-top">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $isStage ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                    {{ $isStage ? 'Stage' : 'Off Stage' }}
                                </span>
                            </td>

                            <!-- limit -->
                            <td class="px-3 py-3 text-center font-mono font-bold text-xs text-slate-900 whitespace-nowrap align-top">
                                {{ $limit }}
                            </td>

                            <!-- registerd status -->
                            <td class="px-3 py-3 text-center whitespace-nowrap align-top">
                                <div class="space-y-1">
                                    <div class="font-mono text-xs font-bold text-slate-900">
                                        registerd {{ $regCount }}/pending{{ $pending }}
                                    </div>
                                    @if($regCount >= $limit)
                                        <span class="inline-block px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Completed
                                        </span>
                                    @elseif($regCount > 0)
                                        <span class="inline-block px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            Partially Filled
                                        </span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            Not Registered
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Students Name -->
                            <td class="px-4 py-3 align-top">
                                @if($prog->entries->isNotEmpty())
                                    <div class="space-y-1.5 text-xs font-sora">
                                        @foreach($prog->entries as $entryIdx => $entry)
                                            @php
                                                $isGroup = $entry->isGroupEntry();
                                                $leaderStudent = $isGroup
                                                    ? ($entry->participants()->wherePivot('role', 'captain')->first() ?? $entry->student ?? $entry->participants()->first())
                                                    : $entry->student;
                                                $displayChest = ltrim((string)($entry->chest_number ?: $leaderStudent?->student_id), '#');
                                                $displayName = $leaderStudent?->name ?: ('Chest ' . ltrim((string)$entry->chest_number, '#'));
                                                $isVerified = in_array($entry->status, ['verified', 'confirmed'], true);
                                            @endphp
                                            <div class="border-b border-slate-100 last:border-0 pb-1 last:pb-0">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="font-bold text-slate-900">{{ $displayName }}</span>
                                                    @if($displayChest)
                                                        <span class="font-mono text-[10px] text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded font-bold">
                                                            #{{ $displayChest }}
                                                        </span>
                                                    @endif
                                                    @if($isVerified)
                                                        <span class="text-[9px] px-1.5 py-0.2 rounded font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                            Verified
                                                        </span>
                                                    @else
                                                        <span class="text-[9px] px-1.5 py-0.2 rounded font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                            Pending
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($isGroup && $entry->participants->isNotEmpty())
                                                    <div class="text-[10px] text-slate-500 font-mono mt-0.5 leading-tight">
                                                        Members ({{ $entry->participants->count() }}): {{ $entry->participants->pluck('name')->implode(', ') }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">No students registered</span>
                                @endif
                            </td>

                            <!-- Action (Screen Only) -->
                            <td class="no-print px-3 py-3 text-center whitespace-nowrap align-top">
                                <a href="{{ route('leader.registrations', ['zone' => $prog->zone?->name ?? $prog->eligibility, 'program_id' => $prog->id]) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold transition shadow-2xs {{ $regCount > 0 ? 'bg-slate-100 hover:bg-slate-200 text-slate-800' : 'bg-brand-orange hover:bg-orange-600 text-white' }}">
                                    {{ $regCount > 0 ? 'Edit' : '+ Enroll' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                                <div class="max-w-md mx-auto space-y-3">
                                    <p class="text-sm font-medium font-sora">No programs found matching the selected filters.</p>
                                    <a href="{{ route('leader.programs-wise') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-black transition shadow-xs font-sora">
                                        Reset Filters
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Official Signatures Block (Print Only) -->
    <div class="print-only pt-8 mt-8 border-t border-slate-400 avoid-break">
        <div class="grid grid-cols-3 gap-6 text-center text-xs font-mono">
            <div>
                <div class="border-b border-slate-400 mb-1.5 h-8"></div>
                <div class="font-bold text-slate-900">Desk Officer / Registrar</div>
                <div class="text-[10px] text-slate-500">Registration Committee</div>
            </div>
            <div>
                <div class="border-b border-slate-400 mb-1.5 h-8"></div>
                <div class="font-bold text-slate-900">{{ $group->name }} Leader / Captain</div>
                <div class="text-[10px] text-slate-500">Official Verification</div>
            </div>
            <div>
                <div class="border-b border-slate-400 mb-1.5 h-8"></div>
                <div class="font-bold text-slate-900">General Convener</div>
                <div class="text-[10px] text-slate-500">Ashabul Quaf Committee</div>
            </div>
        </div>
    </div>
</div>
@endsection
