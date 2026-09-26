@extends('layouts.admin', ['title' => 'Zones'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 font-sans">Zones Overview</h1>
            <p class="text-xs text-slate-500 mt-0.5">Official festival zones, registered competitions, and student distributions</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.achievements.zone-score') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 hover:border-[#be1e2d] transition-all flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Zone Scoreboard</span>
            </a>
            <a href="{{ route('admin.programs.create') }}" class="px-3.5 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-medium text-xs flex items-center gap-1.5 shadow-xs transition-colors">
                <span>+</span> <span>New Program</span>
            </a>
            <a href="{{ route('admin.students.create') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs flex items-center gap-1.5 shadow-xs transition-colors">
                <span>+</span> <span>Add Student</span>
            </a>
        </div>
    </div>

    <!-- Official Zone & Class Distribution Guide -->
    <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 border-b border-slate-100 pb-3 mb-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-900"></span>
                <h2 class="text-sm font-bold text-slate-900 font-sans uppercase tracking-wider">Official Zone & Class Mapping</h2>
            </div>
            <span class="text-[11px] font-mono text-slate-500 font-medium">All students & competitions are categorized by academic class</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="p-3.5 rounded-xl bg-red-50/70 border border-red-200/80">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="font-bold text-red-900 font-sans text-sm">A Zone</span>
                    <span class="text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded-md bg-red-100 text-red-800 border border-red-200">Class 4</span>
                </div>
                <div class="text-xs font-bold text-red-800 mb-1">All Class 4 Levels</div>
                <div class="text-[11px] font-mono text-red-700 font-medium">NF4, UH4, S4, ID4, UT4, L4, TQS</div>
            </div>

            <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/80">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="font-bold text-amber-900 font-sans text-sm">B Zone</span>
                    <span class="text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 border border-amber-200">Class 3</span>
                </div>
                <div class="text-xs font-bold text-amber-800 mb-1">All Class 3 Levels</div>
                <div class="text-[11px] font-mono text-amber-700 font-medium">NF3, ID3, UH3, UT3, S3, L3</div>
            </div>

            <div class="p-3.5 rounded-xl bg-sky-50/70 border border-sky-200/80">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="font-bold text-sky-900 font-sans text-sm">C Zone</span>
                    <span class="text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded-md bg-sky-100 text-sky-800 border border-sky-200">Class 1 & 2</span>
                </div>
                <div class="text-xs font-bold text-sky-800 mb-1">All Class 1 & 2 Levels</div>
                <div class="text-[11px] font-mono text-sky-700 font-medium">U1, U2, L2, S1, S2</div>
            </div>

            <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-200/80">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="font-bold text-emerald-900 font-sans text-sm">Mix Zone</span>
                    <span class="text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200">Open</span>
                </div>
                <div class="text-xs font-bold text-emerald-800 mb-1">All Classes (General / Open Zone)</div>
                <div class="text-[11px] font-mono text-emerald-700 font-medium">All Classes Included</div>
            </div>
        </div>
    </div>

    <!-- 4 Official Zone Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($zoneCards as $key => $card)
            @php
                $isSelected = ($selectedZoneKey === $key);
            @endphp
            <a href="{{ route('admin.zones.index', ['zone' => $key, 'tab' => $activeTab]) }}" 
               class="rounded-2xl bg-white border-2 p-5 transition-all duration-200 relative overflow-hidden flex flex-col justify-between shadow-2xs hover:shadow-md {{ $isSelected ? 'border-[#be1e2d] ring-2 ring-[#be1e2d]/20 bg-gradient-to-b from-white to-red-50/20' : 'border-slate-200 hover:border-slate-300' }}"
               style="border-top-color: {{ $card['color'] }}; border-top-width: 4px;">
                
                <div>
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-mono text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg {{ $card['bg_light'] }}">
                            {{ $card['title'] }}
                        </span>
                        @if($isSelected)
                            <span class="text-[10px] font-mono font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-[#be1e2d] text-white shadow-2xs">
                                SELECTED
                            </span>
                        @else
                            <span class="text-xs text-slate-400 group-hover:text-slate-600 transition-colors">
                                View Details →
                            </span>
                        @endif
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 mb-0.5">{{ $card['title'] }}</h3>
                    <p class="text-xs font-semibold text-slate-600 mb-1 font-serif">{{ $card['sub'] }}</p>
                    <p class="text-[11px] text-slate-500 font-medium leading-relaxed mb-4">{{ $card['classes'] }}</p>
                </div>

                <!-- Metrics Strip -->
                <div class="pt-3 border-t border-slate-100 grid grid-cols-3 gap-2 text-center">
                    <div>
                        <div class="text-base font-black text-slate-900 font-sans">{{ $card['programs_count'] }}</div>
                        <div class="text-[10px] font-mono uppercase text-slate-400 font-semibold">Programs</div>
                    </div>
                    <div>
                        <div class="text-base font-black text-slate-900 font-sans">{{ $card['students_count'] }}</div>
                        <div class="text-[10px] font-mono uppercase text-slate-400 font-semibold">Students</div>
                    </div>
                    <div>
                        <div class="text-base font-black text-slate-900 font-sans" style="color: {{ $card['color'] }};">{{ number_format($card['points_total']) }}</div>
                        <div class="text-[10px] font-mono uppercase text-slate-400 font-semibold">Points</div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Active Zone Details & Listings Panel -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <!-- Panel Header with Tab Navigation -->
        <div class="p-4 sm:p-5 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-2xs" style="background-color: {{ $selectedZone['color'] }};"></span>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-900">{{ $selectedZone['title'] }}</h2>
                        <span class="text-xs text-slate-500 font-medium">({{ $selectedZone['sub'] }})</span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium mt-0.5"><span class="font-bold text-slate-700">Eligible Classes:</span> {{ $selectedZone['classes'] }}</p>
                </div>
            </div>

            <!-- Tab Switcher & Filter Form -->
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <!-- Tabs -->
                <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200 text-xs">
                    <a href="{{ route('admin.zones.index', ['zone' => $selectedZoneKey, 'tab' => 'programs', 'search' => $search]) }}" 
                       class="px-3.5 py-1.5 rounded-lg font-semibold transition-all {{ $activeTab === 'programs' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                        Programs ({{ $selectedZone['programs_count'] }})
                    </a>
                    <a href="{{ route('admin.zones.index', ['zone' => $selectedZoneKey, 'tab' => 'students', 'search' => $search]) }}" 
                       class="px-3.5 py-1.5 rounded-lg font-semibold transition-all {{ $activeTab === 'students' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                        Students ({{ $selectedZone['students_count'] }})
                    </a>
                </div>

                <!-- Search -->
                <form method="GET" action="{{ route('admin.zones.index') }}" class="flex items-center gap-1.5">
                    <input type="hidden" name="zone" value="{{ $selectedZoneKey }}">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="{{ $activeTab === 'programs' ? 'Search programs...' : 'Search students...' }}"
                               class="pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white text-slate-900 focus:outline-none focus:border-[#be1e2d] w-48 sm:w-60">
                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400 text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                    </div>
                    @if($search)
                        <a href="{{ route('admin.zones.index', ['zone' => $selectedZoneKey, 'tab' => $activeTab]) }}" class="px-2 py-1.5 text-xs text-slate-500 hover:text-slate-800 bg-slate-100 rounded-lg">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Tab 1: Programs Listing -->
        @if($activeTab === 'programs')
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-sans">
                    <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 uppercase text-[11px] font-mono">
                        <tr>
                            <th class="px-5 py-3.5">Prog ID</th>
                            <th class="px-5 py-3.5">Programme Name</th>
                            <th class="px-5 py-3.5">Stage / Off Stage</th>
                            <th class="px-5 py-3.5">Zone</th>
                            <th class="px-5 py-3.5 text-center">Limit</th>
                            <th class="px-5 py-3.5 text-center">Entries</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($programs as $prog)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded font-mono font-bold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $prog->code }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-slate-900 text-sm">{{ $prog->name }}</div>
                                    @if($prog->malayalam_name)
                                        <div class="text-[11px] text-slate-500 font-serif">{{ $prog->malayalam_name }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if($prog->is_stage)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-mono uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200 font-bold">
                                            Stage
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-mono uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200 font-bold">
                                            Off Stage
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap font-medium text-slate-800">
                                    {{ $prog->eligibility ?? $selectedZoneKey }}
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap font-mono font-bold text-slate-700">
                                    {{ $prog->participant_count ?? 2 }}
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap font-mono font-bold text-slate-900">
                                    {{ $prog->entries_count ?? 0 }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if($prog->status === 'completed')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold">Completed</span>
                                    @elseif($prog->status === 'in_progress')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-amber-50 text-amber-700 border border-amber-200 font-bold animate-pulse">In Progress</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-slate-100 text-slate-600 font-bold">Upcoming</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.programs.edit', $prog->id) }}" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                                            Edit
                                        </a>
                                        <a href="{{ route('admin.programs.show', $prog->id) }}" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-50 hover:bg-red-100 text-[#be1e2d] transition-colors">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-slate-400 font-mono text-xs">
                                    No programs found for {{ $selectedZoneKey }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($programs->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $programs->links() }}
                </div>
            @endif

        <!-- Tab 2: Students Listing -->
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-sans">
                    <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 uppercase text-[11px] font-mono">
                        <tr>
                            <th class="px-5 py-3.5">Chest No</th>
                            <th class="px-5 py-3.5">Student Name</th>
                            <th class="px-5 py-3.5">Class</th>
                            <th class="px-5 py-3.5">Group</th>
                            <th class="px-5 py-3.5">Zone</th>
                            <th class="px-5 py-3.5 text-center">Points</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($students as $student)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded font-mono font-bold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $student->student_id }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 font-bold text-slate-900 text-sm capitalize">
                                    {{ $student->name }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap font-mono text-slate-600">
                                    {{ $student->class_level ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800">{{ $student->group?->name ?? 'Unassigned' }}</span>
                                    @if($student->group)
                                        <span class="font-mono text-[10px] text-slate-400">({{ $student->group->code }})</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap font-medium text-slate-700">
                                    {{ $student->category ?? $selectedZoneKey }}
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap font-black font-sans text-sm" style="color: {{ $selectedZone['color'] }};">
                                    {{ number_format($student->points_cache ?? 0) }}
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.students.edit', $student->id) }}" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                                            Edit
                                        </a>
                                        <a href="{{ route('admin.students.show', $student->id) }}" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-50 hover:bg-red-100 text-[#be1e2d] transition-colors">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-400 font-mono text-xs">
                                    No students found for {{ $selectedZoneKey }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $students->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
