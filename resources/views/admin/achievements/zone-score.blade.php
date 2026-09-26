@extends('layouts.admin')

@section('title', 'Zone Score - QUAF Fest')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Score by Zone</h1>
            <p class="text-xs text-gray-500 mt-1">Student points and rankings filtered by zone category</p>
        </div>
        <form method="GET" action="{{ route('admin.achievements.zone-score') }}" class="w-full sm:w-80">
            <select name="zone" onchange="this.form.submit()" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange shadow-xs">
                @foreach($zones as $zKey => $zVal)
                    @php
                        $zoneName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                        $zoneValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                    @endphp
                    <option value="{{ $zoneValue }}" {{ $selectedZone === $zoneValue ? 'selected' : '' }}>{{ $zoneName }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Table matching screenshot -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/75 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-5 py-3.5 text-center w-16">Rank</th>
                        <th class="px-5 py-3.5">Student Id</th>
                        <th class="px-6 py-3.5">Name</th>
                        <th class="px-5 py-3.5">Team</th>
                        <th class="px-5 py-3.5 text-center">Total Score</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($students as $index => $student)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-5 py-3.5 text-center font-bold text-gray-800 text-xs">{{ $index + 1 }}</td>
                            <td class="px-5 py-3.5 text-gray-600 font-mono text-xs">{{ $student->student_id ?: $student->id }}</td>
                            <td class="px-6 py-3.5 font-medium text-gray-900 capitalize">{{ $student->name }}</td>
                            <td class="px-5 py-3.5 text-gray-600 text-xs">{{ $student->group?->name ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-center font-bold text-gray-900">{{ number_format($student->points_cache ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                No students found in this zone.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
