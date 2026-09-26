@extends('layouts.admin', ['title' => 'Competitions'])

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 font-sans">Competitions</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage events and competitions</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.print.programs') }}" target="_blank" class="px-3.5 py-2 rounded-lg bg-white border border-slate-200 text-slate-800 font-semibold text-xs hover:bg-slate-50 hover:border-[#be1e2d] transition-all flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print / PDF Report</span>
            </a>
            <a href="{{ route('admin.programs.create') }}" class="px-4 py-2 rounded-lg bg-[#be1e2d] hover:bg-[#a01624] text-white font-medium text-xs flex items-center gap-1.5 shadow-xs transition-colors">
                <span>+</span> <span>New Competition</span>
            </a>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-xs">
        <form method="GET" action="{{ route('admin.programs.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <!-- Search -->
            <div class="relative w-full sm:w-96">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search competitions..."
                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-900 focus:outline-none focus:border-[#be1e2d] transition-all">
            </div>

            <!-- Dropdowns -->
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Statuses</option>
                    <option value="upcoming" {{ $status === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                <select name="zone" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($zones as $zKey => $zVal)
                        @php
                            $zoneName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                            $zoneValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                        @endphp
                        <option value="{{ $zoneValue }}" {{ ($zone ?? '') === $zoneValue ? 'selected' : '' }}>{{ $zoneName }}</option>
                    @endforeach
                </select>

                @if($search || $status || $zone)
                    <a href="{{ route('admin.programs.index') }}" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 bg-slate-100 rounded-lg">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Competitions Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sans">
                <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 uppercase text-[11px] font-mono">
                    <tr>
                        <th class="px-5 py-3.5">Prog ID</th>
                        <th class="px-5 py-3.5">Programme Name</th>
                        <th class="px-5 py-3.5">Stage / Off Stage</th>
                        <th class="px-5 py-3.5">Zone</th>
                        <th class="px-5 py-3.5">Limit</th>
                        <th class="px-5 py-3.5">Entries</th>
                        <th class="px-5 py-3.5 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($programs as $p)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-1 rounded font-mono font-bold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $p->code }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-slate-900 block text-sm">{{ $p->name }}</span>
                                @if($p->malayalam_name)
                                    <span class="text-[11px] text-[#005c94] font-medium block mt-0.5 font-ml">{{ $p->malayalam_name }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($p->is_stage)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Stage
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Non Stage
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="font-mono text-xs font-semibold px-2 py-0.5 bg-blue-50 text-blue-800 rounded border border-blue-200">
                                    {{ $p->eligibility ?? 'A Zone' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="font-mono font-bold text-xs px-2 py-0.5 rounded bg-amber-50 text-amber-900 border border-amber-200">
                                    {{ $p->participant_count ?? 2 }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 font-mono font-medium text-slate-700">
                                {{ $p->entries_count }}
                            </td>
                            <td class="px-5 py-3.5 text-right relative" x-data="{ menuOpen: false }">
                                <button @click="menuOpen = !menuOpen" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                                    <span class="font-bold text-sm leading-none">···</span>
                                </button>
                                <div x-show="menuOpen" @click.away="menuOpen = false" class="absolute right-5 mt-1 w-36 bg-white border border-slate-200 rounded-lg shadow-lg py-1 z-20 text-left text-xs" style="display: none;">
                                    <a href="{{ route('admin.programs.show', $p) }}" class="block px-3 py-1.5 text-slate-700 hover:bg-slate-50">View Details</a>
                                    <a href="{{ route('admin.programs.edit', $p) }}" class="block px-3 py-1.5 text-slate-700 hover:bg-slate-50">Edit</a>
                                    <form method="POST" action="{{ route('admin.programs.destroy', $p) }}" onsubmit="return confirm('Delete this competition?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-left px-3 py-1.5 text-red-600 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                No competitions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($programs->hasPages())
            <div class="p-3 border-t border-slate-100">
                {{ $programs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
