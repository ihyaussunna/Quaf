@extends('layouts.admin')

@section('title', 'View Program Marks - QUAF Fest')

@section('content')
<div class="space-y-6">
    <!-- Page Header & Stats -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">View Program Marks</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    A to Z Directory
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Check submitted scores, standing podiums (1st, 2nd, 3rd) and candidate letter marks
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs">
            <div class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                <span class="text-slate-500 font-medium">Total Programs:</span>
                <span class="font-bold text-slate-800">{{ $programsList->count() }}</span>
            </div>
            <div class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="text-slate-500 font-medium">Evaluated:</span>
                <span class="font-bold text-emerald-700">{{ $programsList->where('has_marks', true)->count() }}</span>
            </div>
            <div class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span class="text-slate-500 font-medium">Pending:</span>
                <span class="font-bold text-amber-700">{{ $programsList->where('has_marks', false)->count() }}</span>
            </div>
        </div>
    </div>

    <!-- Dropdown Jump / Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-2xs">
        <form method="GET" action="{{ route('admin.mark-entry.view-marks') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Zone Filter</label>
                <select name="zone" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/20 focus:border-[#be1e2d] transition">
                    <option value="">-- All Zones --</option>
                    @foreach($zones as $val => $label)
                        <option value="{{ $val }}" {{ ($selectedZone ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-5">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jump to Single Program</label>
                <select name="program" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/20 focus:border-[#be1e2d] transition">
                    <option value="">-- View All Programs (A to Z) --</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ (string)$selectedProgramId === (string)$prog->id ? 'selected' : '' }}>
                            {{ $prog->name }} (ID: {{ $prog->code ?: $prog->id }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3 flex items-center gap-2">
                <button type="submit" class="flex-1 bg-[#be1e2d] text-white py-2.5 px-4 rounded-xl text-xs sm:text-sm font-semibold hover:bg-[#a01624] transition flex items-center justify-center gap-2 shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Filter</span>
                </button>
                @if($selectedProgramId || $selectedZone)
                    <a href="{{ route('admin.mark-entry.view-marks') }}" class="px-3 py-2.5 bg-slate-100 text-slate-600 rounded-xl text-xs sm:text-sm font-semibold hover:bg-slate-200 transition" title="Clear Filter">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if($selectedProgram)
        <!-- Detailed Single Program Marks View -->
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-900 text-white font-mono text-xs font-bold">
                                #{{ $selectedProgram->code ?: $selectedProgram->id }}
                            </span>
                            <h2 class="text-lg sm:text-xl font-bold text-slate-900">
                                {{ $selectedProgram->name }}
                            </h2>
                            @if($selectedProgram->malayalam_name)
                                <span class="text-sm font-anek font-semibold text-slate-600">
                                    {{ $selectedProgram->malayalam_name }}
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 pt-1">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 font-medium text-slate-700">
                                {{ $selectedProgram->eligibility ?? 'All Zones' }}
                            </span>
                            <span>•</span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 font-medium text-slate-700">
                                {{ $selectedProgram->is_stage ? 'Stage Event' : 'Non-Stage Event' }}
                            </span>
                            <span>•</span>
                            <span>{{ $entries->count() }} Verified Candidates</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                        <a href="{{ route('admin.mark-entry.view-marks', ['zone' => $selectedZone]) }}" class="px-3 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Back to A-Z Directory</span>
                        </a>
                        <a href="{{ route('admin.mark-entry.show', $selectedProgram->id) }}" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-black transition">
                            Edit Marks
                        </a>
                        <a href="{{ route('admin.mark-entry.check', ['program' => $selectedProgram->id]) }}" class="px-3.5 py-2 rounded-xl bg-[#be1e2d] text-white text-xs font-semibold hover:bg-[#a01624] transition">
                            Mark Check
                        </a>
                    </div>
                </div>
            </div>

            <!-- Program Candidates List Sorted by Code Letter (A to Z) with Live In-Program Filter -->
            <div x-data="{
                entryQuery: '',
                matchesEntry(el) {
                    if (!this.entryQuery.trim()) return true;
                    const q = this.entryQuery.toLowerCase().trim();
                    const text = (el.getAttribute('data-entry-search') || '').toLowerCase();
                    return text.includes(q);
                }
            }" class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="px-4 sm:px-6 py-3.5 bg-slate-50/75 border-b border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#be1e2d]"></span>
                        <h3 class="text-xs sm:text-sm font-bold text-slate-800">
                            Participants by Code Letter (A to Z) & Standings
                        </h3>
                    </div>
                    <div class="relative w-full sm:w-64">
                        <input
                            type="text"
                            x-model="entryQuery"
                            placeholder="Filter by letter, chest, name..."
                            class="w-full bg-white border border-slate-200 rounded-xl pl-8 pr-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/20 focus:border-[#be1e2d]"
                        >
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($entries as $entry)
                        <div
                            x-show="matchesEntry($el)"
                            data-entry-search="{{ strtolower(($entry->code_letter ?? '') . ' ' . $entry->chest_number . ' ' . ($entry->student?->name ?? '') . ' ' . ($entry->group?->name ?? '') . ' ' . ($entry->student?->group?->name ?? '')) }}"
                            class="px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 hover:bg-slate-50/60 transition {{ $entry->rank === 1 ? 'bg-amber-50/30' : ($entry->rank === 2 ? 'bg-slate-50/50' : ($entry->rank === 3 ? 'bg-orange-50/30' : '')) }}"
                        >
                            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                                <!-- Code Letter Badge -->
                                <div class="relative shrink-0">
                                    <span class="w-10 h-10 rounded-xl {{ $entry->rank === 1 ? 'bg-amber-500 text-white ring-2 ring-amber-300' : ($entry->rank === 2 ? 'bg-slate-600 text-white ring-2 ring-slate-300' : ($entry->rank === 3 ? 'bg-amber-800 text-white ring-2 ring-amber-400' : 'bg-[#be1e2d] text-white')) }} flex items-center justify-center font-bold text-sm shadow-2xs">
                                        {{ $entry->code_letter ?: '?' }}
                                    </span>
                                    @if($entry->rank)
                                        <span class="absolute -top-1.5 -right-1.5 px-1 py-0.2 rounded-full text-[9px] font-black {{ $entry->rank === 1 ? 'bg-amber-400 text-amber-950' : ($entry->rank === 2 ? 'bg-slate-200 text-slate-900' : 'bg-orange-300 text-orange-950') }} shadow-2xs">
                                            #{{ $entry->rank }}
                                        </span>
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-slate-900 text-xs sm:text-sm capitalize truncate">
                                            {{ $entry->student?->name ?: ($entry->chest_number ? 'Chest #'.$entry->chest_number : 'Candidate') }}
                                        </h4>
                                        @if($entry->rank === 1)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                                1st Place (Gold)
                                            </span>
                                        @elseif($entry->rank === 2)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                                2nd Place (Silver)
                                            </span>
                                        @elseif($entry->rank === 3)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-900 border border-orange-200">
                                                3rd Place (Bronze)
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-slate-500 mt-0.5">
                                        <span>Code Letter: <strong class="text-slate-800 font-mono">{{ $entry->code_letter ?: 'Pending' }}</strong></span>
                                        <span>•</span>
                                        <span>Chest #{{ $entry->chest_number ?: 'N/A' }}</span>
                                        <span>•</span>
                                        <span>House: <strong class="text-slate-700">{{ $entry->group?->name ?? $entry->student?->group?->name ?? '-' }}</strong></span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-4 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                <div class="text-left sm:text-right">
                                    <span class="text-[10px] text-slate-400 block font-semibold uppercase tracking-wider">Average Mark</span>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-sm sm:text-base font-bold {{ $entry->total_score > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                                            {{ $entry->total_score > 0 ? number_format($entry->total_score, 1) : '0.0' }}
                                        </span>
                                        @if($entry->computed_grade && $entry->computed_grade !== '-')
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Grade {{ $entry->computed_grade }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('admin.mark-entry.show', $selectedProgram->id) }}" class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-black transition shadow-2xs">
                                    Edit
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 sm:p-12 text-center text-slate-400 text-xs sm:text-sm">
                            No candidates found for this program.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        <!-- All Programs Alphabetical A to Z Directory with Real-time Search & 1st, 2nd, 3rd Standings -->
        <div x-data="{
            searchQuery: '{{ addslashes($search ?? '') }}',
            statusFilter: 'all',
            visibleCount: {{ $programsList->count() }},
            totalCount: {{ $programsList->count() }},
            filterCards() {
                this.$nextTick(() => {
                    const cards = this.$el.querySelectorAll('[data-program-card]');
                    let count = 0;
                    cards.forEach(card => {
                        if (card.style.display !== 'none') {
                            count++;
                        }
                    });
                    this.visibleCount = count;
                });
            },
            matches(card) {
                const search = this.searchQuery.toLowerCase().trim();
                const text = (card.getAttribute('data-search') || '').toLowerCase();
                const hasMarks = card.getAttribute('data-has-marks') === '1';

                // Status filter check
                if (this.statusFilter === 'evaluated' && !hasMarks) return false;
                if (this.statusFilter === 'pending' && hasMarks) return false;

                // Search query check
                if (!search) return true;
                return text.includes(search);
            },
            clearSearch() {
                this.searchQuery = '';
                this.statusFilter = 'all';
                this.filterCards();
            }
        }" x-init="filterCards()" class="space-y-4">
            
            <!-- Real-time Live Search & Quick Filter Pills -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-2xs space-y-3">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="relative flex-1">
                        <input
                            type="text"
                            x-model="searchQuery"
                            @input="filterCards()"
                            placeholder="Type to search any program, code, zone or winner (e.g. Arabana, 101, A Zone, Bilal)..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-10 py-2.5 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/20 focus:border-[#be1e2d] transition"
                        >
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <button
                            type="button"
                            x-show="searchQuery.length > 0"
                            x-cloak
                            @click="clearSearch()"
                            class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 transition"
                            title="Clear search"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Live Count Indicator -->
                    <div class="flex items-center justify-between sm:justify-end gap-2 text-xs shrink-0">
                        <span class="px-3 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold" x-text="'Showing ' + visibleCount + ' of ' + totalCount + ' Programs'"></span>
                    </div>
                </div>

                <!-- Status Filter Pills -->
                <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-slate-100 text-xs">
                    <span class="text-slate-500 font-medium mr-1">Status Filter:</span>
                    <button
                        type="button"
                        @click="statusFilter = 'all'; filterCards()"
                        :class="statusFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1 rounded-lg font-semibold transition"
                    >
                        All ({{ $programsList->count() }})
                    </button>
                    <button
                        type="button"
                        @click="statusFilter = 'evaluated'; filterCards()"
                        :class="statusFilter === 'evaluated' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1 rounded-lg font-semibold transition"
                    >
                        Evaluated / Marks Entered ({{ $programsList->where('has_marks', true)->count() }})
                    </button>
                    <button
                        type="button"
                        @click="statusFilter = 'pending'; filterCards()"
                        :class="statusFilter === 'pending' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1 rounded-lg font-semibold transition"
                    >
                        Evaluation Pending ({{ $programsList->where('has_marks', false)->count() }})
                    </button>
                </div>
            </div>

            <!-- Programs List Sorted A to Z -->
            <div class="space-y-4">
                @forelse($programsList as $prog)
                    <div
                        data-program-card
                        x-show="matches($el)"
                        data-has-marks="{{ $prog->has_marks ? '1' : '0' }}"
                        data-search="{{ strtolower($prog->name . ' ' . $prog->code . ' ' . ($prog->malayalam_name ?? '') . ' ' . ($prog->eligibility ?? '') . ' ' . ($prog->podium['first']?->code_letter ?? '') . ' ' . ($prog->podium['first']?->student?->name ?? '') . ' ' . ($prog->podium['first']?->group?->name ?? '') . ' ' . ($prog->podium['second']?->code_letter ?? '') . ' ' . ($prog->podium['second']?->student?->name ?? '') . ' ' . ($prog->podium['second']?->group?->name ?? '') . ' ' . ($prog->podium['third']?->code_letter ?? '') . ' ' . ($prog->podium['third']?->student?->name ?? '') . ' ' . ($prog->podium['third']?->group?->name ?? '')) }}"
                        class="bg-white rounded-2xl border border-slate-200 shadow-2xs hover:border-slate-300 transition overflow-hidden"
                    >
                        <!-- Program Card Header -->
                        <div class="px-4 sm:px-6 py-4 bg-slate-50/70 border-b border-slate-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                            <div class="space-y-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-lg bg-slate-900 text-white font-mono text-xs font-bold shrink-0">
                                        #{{ $prog->code ?: $prog->id }}
                                    </span>
                                    <h3 class="font-bold text-slate-900 text-sm sm:text-base truncate">
                                        {{ $prog->name }}
                                    </h3>
                                    @if($prog->malayalam_name)
                                        <span class="text-xs sm:text-sm font-anek font-semibold text-slate-600">
                                            {{ $prog->malayalam_name }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 pt-0.5">
                                    <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 font-medium text-slate-700">
                                        {{ $prog->eligibility ?? 'All Zones' }}
                                    </span>
                                    <span>•</span>
                                    <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 font-medium text-slate-700">
                                        {{ $prog->is_stage ? 'Stage' : 'Non-Stage' }}
                                    </span>
                                    <span>•</span>
                                    <span>{{ $prog->entries_count }} Participants</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto justify-between md:justify-end">
                                @if($prog->result && in_array($prog->result->status, ['announced', 'verified']))
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        <span>Result Declared</span>
                                    </span>
                                @elseif($prog->has_marks)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-600"></span>
                                        <span>Marks Recorded</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>Evaluation Pending</span>
                                    </span>
                                @endif

                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('admin.mark-entry.view-marks', ['program' => $prog->id, 'zone' => $selectedZone]) }}" class="px-3 py-1.5 bg-[#be1e2d] text-white rounded-lg text-xs font-semibold hover:bg-[#a01624] transition shadow-2xs">
                                        View Marks
                                    </a>
                                    <a href="{{ route('admin.mark-entry.show', $prog->id) }}" class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-black transition shadow-2xs">
                                        Edit
                                    </a>
                                    <a href="{{ route('admin.mark-entry.check', ['program' => $prog->id]) }}" class="px-2.5 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-200 transition" title="Mark Check">
                                        Check
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- 1st, 2nd, 3rd Standings Podium Section -->
                        <div class="p-4 sm:p-5">
                            @if($prog->has_marks)
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <!-- 1st Position (Gold) -->
                                    <div class="rounded-xl border border-amber-200 bg-gradient-to-br from-amber-50/80 via-white to-amber-50/40 p-3.5 shadow-2xs space-y-2">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500 text-white flex items-center gap-1 shadow-2xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                                <span>1st Place (Gold)</span>
                                            </span>
                                            @if($prog->podium['first']?->total_score > 0)
                                                <div class="text-right">
                                                    <span class="text-xs font-bold text-amber-900 block">
                                                        {{ number_format($prog->podium['first']->total_score, 1) }} pts
                                                    </span>
                                                    @if($prog->podium['first']?->computed_grade && $prog->podium['first']?->computed_grade !== '-')
                                                        <span class="text-[10px] font-bold text-amber-700">
                                                            Grade {{ $prog->podium['first']->computed_grade }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        @if($prog->podium['first'])
                                            <div class="flex items-center gap-2.5 pt-1">
                                                <span class="w-8 h-8 rounded-full bg-amber-500 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                                    {{ $prog->podium['first']->code_letter ?: '?' }}
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="text-xs font-bold text-slate-900 truncate">
                                                        {{ $prog->podium['first']->student?->name ?: ($prog->podium['first']->group?->name ?: 'Chest #'.$prog->podium['first']->chest_number) }}
                                                    </div>
                                                    <div class="text-[11px] text-slate-500 truncate">
                                                        Letter: <strong class="text-slate-700">{{ $prog->podium['first']->code_letter ?: '-' }}</strong> • {{ $prog->podium['first']->group?->name ?? $prog->podium['first']->student?->group?->name ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-xs text-slate-400 italic pt-1">Not determined yet</p>
                                        @endif
                                    </div>

                                    <!-- 2nd Position (Silver) -->
                                    <div class="rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50/80 via-white to-slate-50/40 p-3.5 shadow-2xs space-y-2">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-500 text-white flex items-center gap-1 shadow-2xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                                <span>2nd Place (Silver)</span>
                                            </span>
                                            @if($prog->podium['second']?->total_score > 0)
                                                <div class="text-right">
                                                    <span class="text-xs font-bold text-slate-800 block">
                                                        {{ number_format($prog->podium['second']->total_score, 1) }} pts
                                                    </span>
                                                    @if($prog->podium['second']?->computed_grade && $prog->podium['second']?->computed_grade !== '-')
                                                        <span class="text-[10px] font-bold text-slate-600">
                                                            Grade {{ $prog->podium['second']->computed_grade }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        @if($prog->podium['second'])
                                            <div class="flex items-center gap-2.5 pt-1">
                                                <span class="w-8 h-8 rounded-full bg-slate-500 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                                    {{ $prog->podium['second']->code_letter ?: '?' }}
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="text-xs font-bold text-slate-900 truncate">
                                                        {{ $prog->podium['second']->student?->name ?: ($prog->podium['second']->group?->name ?: 'Chest #'.$prog->podium['second']->chest_number) }}
                                                    </div>
                                                    <div class="text-[11px] text-slate-500 truncate">
                                                        Letter: <strong class="text-slate-700">{{ $prog->podium['second']->code_letter ?: '-' }}</strong> • {{ $prog->podium['second']->group?->name ?? $prog->podium['second']->student?->group?->name ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-xs text-slate-400 italic pt-1">Not determined yet</p>
                                        @endif
                                    </div>

                                    <!-- 3rd Position (Bronze) -->
                                    <div class="rounded-xl border border-orange-200 bg-gradient-to-br from-orange-50/70 via-white to-amber-50/30 p-3.5 shadow-2xs space-y-2">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#b45309] text-white flex items-center gap-1 shadow-2xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                                <span>3rd Place (Bronze)</span>
                                            </span>
                                            @if($prog->podium['third']?->total_score > 0)
                                                <div class="text-right">
                                                    <span class="text-xs font-bold text-orange-900 block">
                                                        {{ number_format($prog->podium['third']->total_score, 1) }} pts
                                                    </span>
                                                    @if($prog->podium['third']?->computed_grade && $prog->podium['third']?->computed_grade !== '-')
                                                        <span class="text-[10px] font-bold text-orange-700">
                                                            Grade {{ $prog->podium['third']->computed_grade }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        @if($prog->podium['third'])
                                            <div class="flex items-center gap-2.5 pt-1">
                                                <span class="w-8 h-8 rounded-full bg-[#b45309] text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                                    {{ $prog->podium['third']->code_letter ?: '?' }}
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="text-xs font-bold text-slate-900 truncate">
                                                        {{ $prog->podium['third']->student?->name ?: ($prog->podium['third']->group?->name ?: 'Chest #'.$prog->podium['third']->chest_number) }}
                                                    </div>
                                                    <div class="text-[11px] text-slate-500 truncate">
                                                        Letter: <strong class="text-slate-700">{{ $prog->podium['third']->code_letter ?: '-' }}</strong> • {{ $prog->podium['third']->group?->name ?? $prog->podium['third']->student?->group?->name ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-xs text-slate-400 italic pt-1">Not determined yet</p>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="p-4 rounded-xl bg-slate-50 border border-dashed border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs text-slate-500">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>No evaluation marks entered yet for this program.</span>
                                    </div>
                                    <a href="{{ route('admin.mark-entry.show', $prog->id) }}" class="text-[#be1e2d] hover:text-[#a01624] font-semibold underline underline-offset-2">
                                        Enter Marks Now &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-12 text-center">
                        <p class="text-sm text-slate-500">No programs found.</p>
                    </div>
                @endforelse
            </div>

            <!-- Empty Search Results State -->
            <div x-show="visibleCount === 0" x-cloak class="bg-white rounded-2xl border border-dashed border-slate-300 p-8 sm:p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-500 mx-auto flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <h4 class="text-sm font-bold text-slate-800">No matching programs found</h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    No programs or winners match your query '<span class="font-semibold text-slate-800" x-text="searchQuery"></span>'.
                </p>
                <button
                    type="button"
                    @click="clearSearch()"
                    class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-black transition shadow-2xs"
                >
                    Clear Filter
                </button>
            </div>
        </div>
    @endif
</div>
@endsection
