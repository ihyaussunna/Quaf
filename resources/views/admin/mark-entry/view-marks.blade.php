@extends('layouts.admin')

@section('title', 'View Program Marks - QUAF Fest')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">View Program Marks</h1>
        <p class="text-sm text-gray-500 mt-1">Check submitted marks and scores for each program</p>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs">
        <form method="GET" action="{{ route('admin.mark-entry.view-marks') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-5">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Zone</label>
                <select name="zone" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/20 focus:border-[#be1e2d]">
                    <option value="">-- All Zones --</option>
                    @foreach($zones as $val => $label)
                        <option value="{{ $val }}" {{ ($selectedZone ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-5">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Program</label>
                <select name="program" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/20 focus:border-[#be1e2d]">
                    <option value="">-- Select Program --</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ (string)$selectedProgramId === (string)$prog->id ? 'selected' : '' }}>{{ $prog->name }} — ID: {{ $prog->code ?: $prog->id }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="w-full bg-[#be1e2d] text-white py-2.5 px-4 rounded-xl text-xs sm:text-sm font-semibold hover:bg-[#a01624] transition flex items-center justify-center gap-2 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>View</span>
                </button>
            </div>
        </form>
    </div>

    @if($selectedProgram)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-4 sm:px-6 py-4 bg-slate-50/75 border-b border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm sm:text-base">{{ $selectedProgram->name }} (ID: {{ $selectedProgram->code ?: $selectedProgram->id }})</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $selectedProgram->eligibility ?? 'A Zone' }} • {{ $selectedProgram->is_stage ? 'Stage' : 'Non-stage' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.mark-entry.show', $selectedProgram->id) }}" class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-black transition">
                        Edit Marks
                    </a>
                    <a href="{{ route('admin.mark-entry.check', ['program' => $selectedProgram->id]) }}" class="px-3 py-1.5 bg-[#be1e2d] text-white rounded-lg text-xs font-semibold hover:bg-[#a01624] transition">
                        Mark Check
                    </a>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($entries as $entry)
                    <div class="px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 hover:bg-slate-50/50 transition">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#be1e2d] text-white flex items-center justify-center font-bold text-xs sm:text-sm shadow-xs shrink-0">
                                {{ $entry->code_letter ?: '?' }}
                            </span>
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-900 text-sm capitalize truncate">{{ $entry->student?->name ?: 'Chest #'.$entry->chest_number }}</h4>
                                <p class="text-xs text-slate-500">Letter: {{ $entry->code_letter ?: 'None' }} • Team: {{ $entry->group?->name ?? $entry->student?->group?->name ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-4 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                            <div class="text-left sm:text-right">
                                <span class="text-[10px] sm:text-xs text-slate-400 block font-medium">Total</span>
                                <span class="text-sm sm:text-base font-bold text-emerald-600">{{ number_format($entry->total_score, 1) }}</span>
                            </div>
                            <a href="{{ route('admin.mark-entry.show', $selectedProgram->id) }}" class="px-3.5 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-black transition">
                                View
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 sm:p-12 text-center text-slate-400 text-xs sm:text-sm">
                        No marks entered yet for this program.
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-12 text-center">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-red-50 text-[#be1e2d] mx-auto flex items-center justify-center mb-3">
                <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900">Select a zone and program to view marks</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Please select a zone and program from the dropdown above to view marks.</p>
        </div>
    @endif
</div>
@endsection
