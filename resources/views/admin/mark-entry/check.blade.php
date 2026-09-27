@extends('layouts.admin')

@section('title', 'Program Results (Mark Check) - QUAF Fest')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Program Results</h1>
            <p class="text-xs text-gray-500 mt-1">Cross-verify calculated grades, scores and ranks before declaring results</p>
        </div>
        @if($selectedProgram)
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.mark-entry.view-marks', ['zone' => $selectedZone, 'program' => $selectedProgram->id]) }}" class="px-3.5 py-2 bg-gray-100 text-gray-700 rounded-xl text-xs font-semibold hover:bg-gray-200 transition">
                    View Marks
                </a>
                <a href="{{ route('admin.results.create', ['program_id' => $selectedProgram->id]) }}" class="px-4 py-2 bg-brand-orange text-white rounded-xl text-xs font-semibold hover:bg-orange-600 transition shadow-xs">
                    Declare Result
                </a>
            </div>
        @endif
    </div>

    <!-- Selection Filter -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs">
        <form method="GET" action="{{ route('admin.mark-entry.check') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-5">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Zone</label>
                <select name="zone" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                    <option value="">-- All Zones --</option>
                    @foreach($zones as $val => $label)
                        <option value="{{ $val }}" {{ ($selectedZone ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-5">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Program</label>
                <select name="program" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                    <option value="">-- Select Program --</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ (string)$selectedProgramId === (string)$prog->id ? 'selected' : '' }}>{{ $prog->name }} — ID: {{ $prog->code ?: $prog->id }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="w-full bg-brand-orange text-white py-2.5 px-4 rounded-xl text-sm font-semibold hover:bg-orange-600 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Check
                </button>
            </div>
        </form>
    </div>

    @if($selectedProgram)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <!-- Header matching screenshot -->
            <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
                <div>
                    <span class="text-gray-400 font-medium">Id:</span>
                    <span class="font-bold text-gray-800 ml-1">{{ $selectedProgram->code ?: $selectedProgram->id }}</span>
                </div>
                <div>
                    <span class="text-gray-400 font-medium">Name:</span>
                    <span class="font-bold text-gray-800 ml-1">{{ $selectedProgram->name }}</span>
                </div>
                <div>
                    <span class="text-gray-400 font-medium">Type:</span>
                    <span class="font-bold text-gray-800 ml-1">{{ ucfirst($selectedProgram->type ?? 'Individual') }}</span>
                </div>
                <div>
                    <span class="text-gray-400 font-medium">Stage:</span>
                    <span class="font-bold text-gray-800 ml-1">{{ $selectedProgram->is_stage ? 'Stage' : 'Non-stage' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 font-medium">Zone:</span>
                    <span class="font-bold text-gray-800 ml-1">{{ $selectedProgram->eligibility ?? 'A Zone' }}</span>
                </div>
            </div>

            <!-- Table matching screenshot -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50/75 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-4 py-3.5 text-center w-12">No</th>
                            <th class="px-4 py-3.5 text-center">Letter</th>
                            <th class="px-4 py-3.5">Id</th>
                            <th class="px-6 py-3.5">Name</th>
                            <th class="px-4 py-3.5">Team</th>
                            <th class="px-4 py-3.5 text-center">Grade</th>
                            <th class="px-4 py-3.5 text-center">Score</th>
                            <th class="px-4 py-3.5 text-center">Rank</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($rankedEntries as $index => $item)
                            <tr class="hover:bg-gray-50/50 transition {{ $item->computed_rank === 'first' ? 'bg-amber-50/30 font-semibold' : '' }}">
                                <td class="px-4 py-3.5 text-center text-gray-500 text-xs font-medium">{{ $index + 1 }}</td>
                                <td class="px-4 py-3.5 text-center font-bold text-gray-800">{{ $item->code_letter ?: '-' }}</td>
                                <td class="px-4 py-3.5 text-gray-700 font-mono text-xs">{{ $item->student?->student_id ?: $item->chest_number }}</td>
                                <td class="px-6 py-3.5 text-gray-900 capitalize font-medium">{{ $item->student?->name ?: 'Chest #'.$item->chest_number }}</td>
                                <td class="px-4 py-3.5 text-gray-600">{{ $item->group?->name ?? $item->student?->group?->name ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-bold {{ $item->computed_grade === 'A' || $item->computed_grade === 'A+' ? 'bg-emerald-100 text-emerald-700' : ($item->computed_grade === 'B+' || $item->computed_grade === 'B' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                                        {{ $item->computed_grade }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold text-gray-900">{{ number_format($item->computed_score, 0) }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($item->computed_rank === 'first')
                                        <span class="text-xs font-bold text-amber-600 uppercase tracking-wide">first</span>
                                    @elseif($item->computed_rank === 'second')
                                        <span class="text-xs font-bold text-gray-600 uppercase tracking-wide">second</span>
                                    @elseif($item->computed_rank === 'third')
                                        <span class="text-xs font-bold text-amber-800 uppercase tracking-wide">third</span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                    No marks recorded for this program yet.
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
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-base font-bold text-gray-900">Select a zone and program to check results</h3>
            <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Please select a zone and program from the dropdown above to inspect results.</p>
        </div>
    @endif
</div>
@endsection
