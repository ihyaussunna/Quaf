@extends('layouts.leader')

@section('title', 'Program List - Leader Panel')

@section('content')
<div class="space-y-6" x-data="{ activeModalProg: null }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-sora text-gray-900">Program List</h1>
            <p class="text-xs text-gray-500 mt-1 font-sora">Festival competitions and events for {{ $group->name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('leader.programs-wise') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-xs font-semibold hover:bg-gray-50 transition font-sora">
                View Program Wise
            </a>
            <a href="{{ route('leader.registrations') }}" class="px-4 py-2 bg-brand-orange text-white rounded-xl text-xs font-semibold hover:bg-orange-600 transition shadow-xs font-sora">
                + Add Program Entry
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
        <form method="GET" action="{{ route('leader.programs') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search program by name or code..." class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-10 pr-4 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange font-sora">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <select name="zone" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange font-sora">
                <option value="">All Zones</option>
                @foreach($zones as $zKey => $zVal)
                    @php
                        $zoneName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                        $zoneValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                    @endphp
                    <option value="{{ $zoneValue }}" {{ ($selectedZone ?? '') === $zoneValue ? 'selected' : '' }}>{{ $zoneName }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-5 py-2 bg-brand-orange text-white rounded-xl text-sm font-semibold hover:bg-orange-600 transition shadow-xs font-sora">
                Filter
            </button>
        </form>
    </div>

    <!-- Program Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm font-sora">
                <thead>
                    <tr class="bg-gray-50/75 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider font-sora">
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <th class="px-4 py-3.5">Code</th>
                        <th class="px-6 py-3.5">Program Name</th>
                        <th class="px-4 py-3.5">Zone</th>
                        <th class="px-4 py-3.5">Type</th>
                        <th class="px-4 py-3.5">Stage</th>
                        <th class="px-4 py-3.5 text-center">Niyamavali</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($programs as $index => $prog)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3.5 text-center text-gray-500 text-xs font-medium font-mono">{{ $programs->firstItem() + $index }}</td>
                            <td class="px-4 py-3.5 text-gray-900 font-mono text-xs font-semibold">{{ $prog->code ?: $prog->id }}</td>
                            <td class="px-6 py-3.5 font-bold text-gray-900 capitalize">{{ $prog->name }}</td>
                            <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $prog->eligibility ?? 'A Zone' }}</td>
                            <td class="px-4 py-3.5 text-gray-600 text-xs">{{ ucfirst($prog->type ?? 'Individual') }}</td>
                            <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $prog->is_stage ? 'Stage' : 'Non-stage' }}</td>
                            <td class="px-4 py-3.5 text-center">
                                @if(!empty($prog->rules))
                                    <button type="button" 
                                            @click="activeModalProg = { name: '{{ addslashes($prog->name) }}', code: '{{ $prog->code }}', rules: `{{ addslashes($prog->rules) }}`, duration: '{{ $prog->duration_minutes }}' }" 
                                            class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 text-xs font-sora font-bold transition">
                                        Rules ✓
                                    </button>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $prog->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($prog->status === 'in_progress' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ ucfirst(str_replace('_', ' ', $prog->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                No programs found.
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

    <!-- Niyamavali Modal for Group Leaders -->
    <div x-show="activeModalProg" 
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50"
         style="display: none;"
         @click.self="activeModalProg = null">
        <div class="bg-white rounded-3xl border border-slate-200 max-w-lg w-full p-6 sm:p-8 space-y-4 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                <div>
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-mono text-xs font-bold" x-text="activeModalProg ? activeModalProg.code : ''"></span>
                    <h3 class="text-lg font-sora font-bold text-slate-900 mt-1" x-text="activeModalProg ? activeModalProg.name : ''"></h3>
                    <p class="text-xs font-sora text-slate-500">Duration: <span class="font-mono font-bold" x-text="activeModalProg ? activeModalProg.duration : ''"></span> Minutes</p>
                </div>
                <button type="button" @click="activeModalProg = null" class="text-slate-400 hover:text-slate-700 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-2">
                <span class="text-xs font-sora uppercase font-bold text-slate-500 block">Official Competition Rules:</span>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-800 leading-relaxed font-sora whitespace-pre-line max-h-72 overflow-y-auto"
                     x-text="activeModalProg ? activeModalProg.rules : ''">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end">
                <button type="button" @click="activeModalProg = null" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-sora font-bold rounded-xl transition">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
