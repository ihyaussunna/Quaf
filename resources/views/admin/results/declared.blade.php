@extends('layouts.admin')

@section('title', 'Declared Results - QUAF Fest')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Declared Results</h1>
            <p class="text-xs text-blue-600 font-medium mt-1">Click the delete icon to undeclare a result.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.results.declare') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-xs font-semibold hover:bg-gray-50 transition">
                Declare New Result
            </a>
            <a href="{{ route('admin.results.specified') }}" class="px-4 py-2 bg-brand-orange text-white rounded-xl text-xs font-semibold hover:bg-orange-600 transition shadow-xs">
                Specified Results
            </a>
        </div>
    </div>

    <!-- Filter Program Search -->
    <div class="flex justify-end">
        <form method="GET" action="{{ route('admin.results.declared') }}" class="w-full sm:w-72">
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Filter Program" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange shadow-xs">
            </div>
        </form>
    </div>

    <!-- Declared Results Table matching screenshot -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/75 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3.5 text-center w-16">Results</th>
                        <th class="px-4 py-3.5">Id</th>
                        <th class="px-6 py-3.5">Program Name</th>
                        <th class="px-4 py-3.5">Zone</th>
                        <th class="px-4 py-3.5">Type</th>
                        <th class="px-4 py-3.5">Stage</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($results as $index => $res)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3.5 text-center text-gray-500 text-xs font-medium">{{ $results->firstItem() + $index }}</td>
                            <td class="px-4 py-3.5 text-gray-800 font-mono text-xs font-semibold">{{ $res->program?->code ?: $res->program?->id }}</td>
                            <td class="px-6 py-3.5 font-bold text-gray-900 capitalize">{{ $res->program?->name }}</td>
                            <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $res->program?->eligibility ?? 'A Zone' }}</td>
                            <td class="px-4 py-3.5 text-gray-600 text-xs">{{ ucfirst($res->program?->type ?? 'Individual') }}</td>
                            <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $res->program?->is_stage ? 'Stage' : 'Non-stage' }}</td>
                            <td class="px-6 py-3.5 text-right flex items-center justify-end gap-3">
                                <!-- Blue checkmark icon matching screenshot -->
                                <span class="w-7 h-7 rounded-full bg-blue-500 text-white flex items-center justify-center shadow-xs" title="Result Declared & Verified">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </span>

                                <!-- Red trash icon to undeclare matching screenshot -->
                                <form method="POST" action="{{ route('admin.results.undeclare', $res->id) }}" onsubmit="return confirm('Are you sure you want to undeclare this result? Team points will be recalculated.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition" title="Undeclare Result">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                No declared results yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($results->hasPages())
        <div>
            {{ $results->links() }}
        </div>
    @endif
</div>
@endsection
