@extends('layouts.admin')

@section('title', 'Declare Results - QUAF Fest')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Declare Results</h1>
            <p class="text-xs text-gray-500 mt-1">Official declaration of verified and approved festival results</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.results.declared') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-xs font-semibold hover:bg-gray-50 transition">
                View Declared Results
            </a>
            <a href="{{ route('admin.results.all') }}" class="px-4 py-2 bg-brand-orange text-white rounded-xl text-xs font-semibold hover:bg-orange-600 transition shadow-xs">
                All Results List
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.results.declare') }}" class="flex gap-2">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search program..." class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-10 pr-4 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-5 py-2 bg-brand-orange text-white rounded-xl text-sm font-semibold hover:bg-orange-600 transition shadow-xs">
                Search
            </button>
        </form>
    </div>

    <!-- Results to Declare Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/75 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <th class="px-4 py-3.5">ID</th>
                        <th class="px-6 py-3.5">Program Name</th>
                        <th class="px-4 py-3.5">Zone</th>
                        <th class="px-4 py-3.5">Type</th>
                        <th class="px-4 py-3.5">Stage</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($programs as $index => $prog)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3.5 text-center text-gray-500 text-xs font-medium">{{ $programs->firstItem() + $index }}</td>
                            <td class="px-4 py-3.5 text-gray-900 font-mono text-xs font-semibold">{{ $prog->code ?: $prog->id }}</td>
                            <td class="px-6 py-3.5 font-bold text-gray-900 capitalize">{{ $prog->name }}</td>
                            <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $prog->eligibility ?? 'A Zone' }}</td>
                            <td class="px-4 py-3.5 text-gray-600 text-xs">{{ ucfirst($prog->type ?? 'Individual') }}</td>
                            <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $prog->is_stage ? 'Stage' : 'Non-stage' }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 uppercase">
                                    {{ $prog->result?->status ?? 'Verified' }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <a href="{{ route('admin.results.create', ['program_id' => $prog->id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-orange text-white rounded-xl text-xs font-bold hover:bg-orange-600 transition shadow-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Declare Now
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="max-w-md mx-auto space-y-2">
                                    <p class="text-sm font-semibold text-slate-700">No pending verified programs to declare</p>
                                    <p class="text-xs text-slate-400 leading-relaxed">
                                        Programs appear here when jury evaluation is completed and marked as verified, awaiting official declaration. If this program has already been published, please check Declared Results.
                                    </p>
                                    <div class="pt-2 flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.results.declared') }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                                            View Declared Results
                                        </a>
                                        <a href="{{ route('admin.results.specified') }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                                            Check Program Ranking
                                        </a>
                                    </div>
                                </div>
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
