@extends('layouts.admin')

@section('title', 'Specified Results - QUAF Fest')

@section('content')
<div class="space-y-6">
    <div class="text-center max-w-xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900">Specified Results</h1>
        <p class="text-xs text-gray-500 mt-1">Look up complete result breakdown by entering Program ID or Code</p>
    </div>

    <!-- Centered Search Box matching screenshot -->
    <div class="max-w-xl mx-auto">
        <form method="GET" action="{{ route('admin.results.specified') }}">
            <div class="relative">
                <input type="text" name="program_id" value="{{ $programQuery }}" placeholder="Enter Program Code or Number (e.g. 204, Q9-204)" autofocus class="w-full text-center bg-white border-2 border-blue-400 rounded-2xl px-6 py-3.5 text-base font-bold text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-blue-100 shadow-sm transition">
            </div>
        </form>
    </div>

    @if($selectedProgram)
        <div class="max-w-5xl mx-auto bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden mt-6">
            <!-- Header matching screenshot -->
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-center text-lg font-bold text-gray-900 mb-4">
                    Result {{ $selectedProgram->code ?: $selectedProgram->id }}
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="text-gray-400 font-medium">Id:</span>
                        <span class="font-bold text-gray-800 ml-1">{{ $selectedProgram->code ?: $selectedProgram->id }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Name:</span>
                        <span class="font-bold text-gray-800 ml-1 capitalize">{{ $selectedProgram->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Type:</span>
                        <span class="font-bold text-gray-800 ml-1">{{ ucfirst($selectedProgram->type ?? 'Individual') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Zone:</span>
                        <span class="font-bold text-gray-800 ml-1">{{ $selectedProgram->eligibility ?? 'A Zone' }}</span>
                    </div>
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
                            <th class="px-4 py-3.5">Class</th>
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
                                <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $item->student?->class_level ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $item->group?->name ?? $item->student?->group?->name ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-bold {{ $item->computed_grade === 'A+' || $item->computed_grade === 'A' ? 'bg-emerald-100 text-emerald-700' : ($item->computed_grade === 'B' ? 'bg-blue-100 text-blue-700' : ($item->computed_grade === 'C' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700')) }}">
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
                                <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                    No records found for this program.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($programQuery)
        <div class="max-w-xl mx-auto bg-white rounded-2xl border border-gray-100 p-8 text-center text-gray-400">
            Program with ID '{{ $programQuery }}' not found. Please enter a valid ID.
        </div>
    @endif
</div>
@endsection
