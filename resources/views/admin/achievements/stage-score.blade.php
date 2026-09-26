@extends('layouts.admin')

@section('title', 'Score by Stage - QUAF Fest')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Score by Stage</h1>
            <p class="text-xs text-gray-500 mt-1">Student points and rankings segregated by Stage and Non-Stage events</p>
        </div>
        <form method="GET" action="{{ route('admin.achievements.stage-score') }}" class="w-full sm:w-64">
            <select name="stage" onchange="this.form.submit()" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange shadow-xs">
                <option value="Stage" {{ $stageFilter === 'Stage' ? 'selected' : '' }}>Stage</option>
                <option value="Non stage" {{ $stageFilter === 'Non stage' ? 'selected' : '' }}>Non stage</option>
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
                        <th class="px-5 py-3.5">Zone</th>
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
                            <td class="px-5 py-3.5 text-gray-600 text-xs">{{ $student->category ?? 'GENERAL' }}</td>
                            <td class="px-5 py-3.5 text-center font-bold text-gray-900">{{ number_format($student->points_cache ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                No records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
