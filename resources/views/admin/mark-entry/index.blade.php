@extends('layouts.admin', ['title' => 'Mark Entry & Evaluations | Quaf'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 font-sora tracking-tight">Mark Entry & Evaluations</h1>
            <p class="text-xs text-slate-500 mt-1 font-sora">Enter judges' marks for participants and publish official results with real-time points update.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.results.index') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-all flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span>View Results</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs">
        <form method="GET" action="{{ route('admin.mark-entry.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <!-- Search -->
            <div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search competition name or code..."
                       class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all text-slate-900">
            </div>

            <!-- Zone Filter -->
            <div>
                <select name="zone" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all text-slate-900">
                    <option value="">All Zones</option>
                    @foreach($zones as $z)
                        <option value="{{ $z }}" {{ ($zone ?? '') === $z ? 'selected' : '' }}>{{ $z }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Publication Status Filter -->
            <div>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all text-slate-900">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending Evaluation</option>
                    <option value="published" {{ $status === 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-[#be1e2d] text-white font-semibold text-xs hover:bg-[#a01624] transition-colors shadow-2xs">
                    Filter
                </button>
                <a href="{{ route('admin.mark-entry.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Competitions Table with Status Tags & Options Dropdown (···) -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sora">
                <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200 text-[11px] font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">Code</th>
                        <th class="px-5 py-3.5">Competition</th>
                        <th class="px-5 py-3.5">Zone</th>
                        <th class="px-5 py-3.5">Stage / Venue</th>
                        <th class="px-5 py-3.5 text-center font-mono">Participants</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($programs as $program)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Code -->
                            <td class="px-5 py-4 font-mono font-bold text-[#be1e2d]">
                                {{ $program->code }}
                            </td>

                            <!-- Competition -->
                            <td class="px-5 py-4">
                                <span class="font-bold text-slate-900 block text-xs">{{ $program->name }}</span>
                                @if($program->malayalam_name)
                                    <span class="text-[11px] text-slate-400 block font-ml">{{ $program->malayalam_name }}</span>
                                @endif
                                <span class="text-[10px] text-slate-400 capitalize">{{ $program->type }} • Weight: {{ $program->points_weight }}x</span>
                            </td>

                            <!-- Zone -->
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $program->eligibility ?? 'A Zone' }}
                                </span>
                            </td>

                            <!-- Stage / Venue -->
                            <td class="px-5 py-4 text-slate-600">
                                {{ $program->stage?->name ?? 'Off-Stage / Non-Stage' }}
                            </td>

                            <!-- Participants -->
                            <td class="px-5 py-4 text-center">
                                <span class="font-bold text-slate-900 font-mono">{{ $program->entries_count }}</span>
                                @if($program->evaluated_entries_count > 0)
                                    <span class="text-[10px] text-emerald-600 block font-semibold font-mono">({{ $program->evaluated_entries_count }} scored)</span>
                                @else
                                    <span class="text-[10px] text-slate-400 block font-mono">(0 scored)</span>
                                @endif
                            </td>

                            <!-- Status Tags (*Published*, *Pending*, *In Progress*, *Submitted*, *Cancelled*) -->
                            <td class="px-5 py-4 text-center">
                                @if($program->status === 'cancelled')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Cancelled
                                    </span>
                                @elseif($program->result && $program->result->status === 'published')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Published
                                    </span>
                                @elseif($program->result && $program->result->status === 'submitted')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Submitted
                                    </span>
                                @elseif($program->evaluated_entries_count > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-[#005c94] border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> In Progress
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                    </span>
                                @endif
                            </td>

                            <!-- Options Dropdown (···) -->
                            <td class="px-5 py-4 text-right">
                                <div class="relative inline-block text-left" x-data="{ open: false }">
                                    <button @click="open = !open" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                    </button>

                                    <div x-show="open" @click.away="open = false"
                                         class="absolute right-0 mt-1 w-44 bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-30 text-xs font-sora text-left"
                                         style="display: none;">
                                        <a href="{{ route('admin.mark-entry.show', $program) }}" class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-slate-50 transition-colors">
                                            <svg class="w-3.5 h-3.5 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            <span>Enter / Edit Marks</span>
                                        </a>
                                        @if($program->result)
                                            <a href="{{ route('admin.results.edit', $program->result) }}" class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-slate-50 transition-colors">
                                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span>View Result</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                No competitions found matching your search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($programs->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $programs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
