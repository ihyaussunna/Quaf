@extends('layouts.admin')

@section('title', 'All Student Score - QUAF Fest')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">All Student Score</h1>
            <p class="text-xs text-gray-500 mt-1">Global student points leaderboard across all categories and teams</p>
        </div>
        <form method="GET" action="{{ route('admin.achievements.all-students') }}" class="w-full sm:w-72">
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search student name or ID..." class="w-full bg-white border border-gray-200 rounded-xl pl-10 pr-4 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange shadow-xs">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </form>
    </div>

    <!-- Table matching screenshot -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/75 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-5 py-3.5 text-center w-16">Rank</th>
                        <th class="px-5 py-3.5">Id</th>
                        <th class="px-6 py-3.5">Student Name</th>
                        <th class="px-5 py-3.5">Zone</th>
                        <th class="px-5 py-3.5">Team</th>
                        <th class="px-5 py-3.5 text-center">Total Score</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($students as $index => $student)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-5 py-3.5 text-center font-bold text-gray-800 text-xs">{{ $students->firstItem() + $index }}</td>
                            <td class="px-5 py-3.5 text-gray-600 font-mono text-xs">{{ $student->student_id ?: $student->id }}</td>
                            <td class="px-6 py-3.5 font-medium text-gray-900 capitalize">{{ $student->name }}</td>
                            <td class="px-5 py-3.5 text-gray-600 text-xs">{{ $student->category ?? 'GENERAL' }}</td>
                            <td class="px-5 py-3.5 text-gray-600 text-xs">{{ $student->group?->name ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-center font-bold text-gray-900">{{ number_format($student->points_cache ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                No students found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($students->hasPages())
        <div>
            {{ $students->links() }}
        </div>
    @endif
</div>
@endsection
