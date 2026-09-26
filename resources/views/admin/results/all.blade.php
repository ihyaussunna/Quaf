@extends('layouts.admin')

@section('title', 'Result List - QUAF Fest')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Result List</h1>
            <p class="text-xs text-gray-500 mt-1">Complete overview of all announced and declared program results</p>
        </div>
        <div>
            <form method="GET" action="{{ route('admin.results.all') }}" class="w-full sm:w-72">
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search program..." class="w-full bg-white border border-gray-200 rounded-xl pl-4 pr-10 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange shadow-xs">
                    <button type="submit" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table matching screenshot -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/75 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3.5 text-center w-14">No</th>
                        <th class="px-4 py-3.5">ID</th>
                        <th class="px-6 py-3.5">Name</th>
                        <th class="px-4 py-3.5">Type</th>
                        <th class="px-4 py-3.5">Zone</th>
                        <th class="px-4 py-3.5">Stage</th>
                        <th class="px-4 py-3.5 text-center">View</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($programs as $index => $prog)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3.5 text-center text-gray-500 text-xs font-medium">{{ $programs->firstItem() + $index }}</td>
                            <td class="px-4 py-3.5 text-gray-900 font-mono text-xs font-semibold">{{ $prog->code ?: $prog->id }}</td>
                            <td class="px-6 py-3.5 font-medium text-gray-900 capitalize">{{ $prog->name }}</td>
                            <td class="px-4 py-3.5 text-gray-600 text-xs">{{ ucfirst($prog->type ?? 'Individual') }}</td>
                            <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $prog->eligibility ?? 'A Zone' }}</td>
                            <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $prog->is_stage ? 'Stage' : 'Non-stage' }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <a href="{{ route('admin.results.specified', ['program_id' => $prog->id]) }}" class="w-8 h-8 rounded-xl bg-brand-orange text-white inline-flex items-center justify-center hover:bg-orange-600 transition shadow-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                No results found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($programs->hasPages())
        <div>
            {{ $programs->links() }}
        </div>
    @endif
</div>
@endsection
