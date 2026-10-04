@extends('layouts.leader')

@section('title', 'Program List - Leader Panel')

@section('content')
<style>
@media print {
    .no-print {
        display: none !important;
    }
    .print-hidden-section {
        display: none !important;
    }
    body {
        background-color: #ffffff !important;
        color: #000000 !important;
    }
    thead {
        display: table-header-group;
    }
    tr {
        page-break-inside: avoid !important;
    }
}
</style>

<div class="space-y-6" x-data="programListManager()">
    <div class="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-sora text-gray-900">Program List</h1>
            <p class="text-xs text-gray-500 mt-1 font-sora">Festival competitions and events for {{ $group->name }}</p>
        </div>
        <div class="no-print flex items-center gap-2">
            <a href="{{ route('leader.programs-wise') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-xs font-semibold hover:bg-gray-50 transition font-sora">
                View Program Wise
            </a>
            <a href="{{ route('leader.registrations') }}" class="px-4 py-2 bg-brand-orange text-white rounded-xl text-xs font-semibold hover:bg-orange-600 transition shadow-xs font-sora">
                + Add Program Entry
            </a>
        </div>
    </div>

    <!-- PDF Selection & Download Action Bar (Hidden on Print) -->
    <div class="no-print bg-slate-900 text-white rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-lg">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-400/20 text-amber-400 flex items-center justify-center font-bold text-sm font-mono">
                PDF
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-sora font-bold text-white">Programs Master List PDF Export</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-400 text-slate-950"
                          x-text="selectedCount + ' of ' + allProgIds.length + ' programs included'"></span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">
                    Uncheck any program you wish to omit. Only checked competitions will appear in the generated PDF.
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <button type="button" @click="includeAll()" 
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-sora text-slate-200 border border-slate-700 transition">
                Select All
            </button>
            <button type="button" @click="excludeAll()" 
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-sora text-slate-200 border border-slate-700 transition">
                Deselect All
            </button>
            <button type="button" @click="printPdf()" 
                    class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-sora font-bold text-xs uppercase tracking-wider transition-all shadow-md flex items-center gap-2 cursor-pointer ml-auto md:ml-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Download / Print PDF</span>
            </button>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="no-print bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
        <form method="GET" action="{{ route('leader.programs') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search program by name or code..." class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-10 pr-4 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange font-sora">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <select name="type" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange font-sora">
                <option value="">All Types</option>
                <option value="individual" {{ ($selectedType ?? '') === 'individual' ? 'selected' : '' }}>Individual</option>
                <option value="group" {{ ($selectedType ?? '') === 'group' ? 'selected' : '' }}>Group</option>
            </select>
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

    <!-- Official Festival Top Masthead Banner (Print Only) -->
    <div class="print-only mb-4">
        @include('partials.print-pdf-header')
        <div class="text-center mt-3 pb-2 border-b border-slate-300">
            <h2 class="text-base font-bold font-sora text-slate-900 uppercase">Competition Program Schedule</h2>
            <p class="text-xs font-sora text-slate-600">{{ $group->name }} • Generated on {{ now()->format('d M Y, h:i A') }}</p>
        </div>
    </div>

    <!-- Program Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm font-sora">
                <thead>
                    <tr class="bg-gray-50/75 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider font-sora">
                        <th class="no-print px-4 py-3.5 text-center w-14">In PDF</th>
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <th class="px-4 py-3.5">Code</th>
                        <th class="px-6 py-3.5">Program Name</th>
                        <th class="px-4 py-3.5">Zone</th>
                        <th class="px-4 py-3.5">Type</th>
                        <th class="px-4 py-3.5">Stage</th>
                        <th class="no-print px-4 py-3.5 text-center">Niyamavali</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($programs as $index => $prog)
                        <tr :class="isProgExcluded({{ $prog->id }}) ? 'opacity-40 border-dashed bg-slate-50/50 print-hidden-section' : ''" class="hover:bg-gray-50/50 transition">
                            <td class="no-print px-4 py-3.5 text-center whitespace-nowrap">
                                <label class="inline-flex items-center cursor-pointer" title="Include/Exclude from PDF">
                                    <input type="checkbox"
                                           :checked="!isProgExcluded({{ $prog->id }})"
                                           @change="toggleProg({{ $prog->id }})"
                                           class="w-4 h-4 text-amber-500 rounded border-slate-300 focus:ring-amber-400">
                                </label>
                            </td>
                            <td class="px-4 py-3.5 text-center text-gray-500 text-xs font-medium font-mono">{{ $programs->firstItem() + $index }}</td>
                            <td class="px-4 py-3.5 text-gray-900 font-mono text-xs font-semibold">{{ $prog->code ?: $prog->id }}</td>
                            <td class="px-6 py-3.5 font-bold text-gray-900 capitalize">{{ $prog->name }}</td>
                            <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $prog->eligibility ?? 'A Zone' }}</td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if(($prog->type ?? 'individual') === 'group')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        Group
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        Individual
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $prog->is_stage ? 'Stage' : 'Non-stage' }}</td>
                            <td class="no-print px-4 py-3.5 text-center">
                                @if(!empty($prog->rules))
                                    <button type="button" 
                                            @click="activeModalProg = { name: '{{ addslashes($prog->name) }}', code: '{{ $prog->code }}', rules: `{{ addslashes($prog->rules) }}`, duration: '{{ $prog->duration_minutes }}' }" 
                                            class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 text-xs font-sora font-bold transition">
                                        Rules
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
                            <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                No programs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($programs->hasPages())
        <div class="no-print">
            {{ $programs->links() }}
        </div>
    @endif

    <!-- Niyamavali Modal for Group Leaders -->
    <div x-show="activeModalProg" 
         class="no-print fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50"
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

<script>
function programListManager() {
    return {
        activeModalProg: null,
        allProgIds: @json($programs->pluck('id')),
        excludedProgIds: [],
        toggleProg(id) {
            if (this.excludedProgIds.includes(id)) {
                this.excludedProgIds = this.excludedProgIds.filter(x => x !== id);
            } else {
                this.excludedProgIds.push(id);
            }
        },
        isProgExcluded(id) {
            return this.excludedProgIds.includes(id);
        },
        includeAll() {
            this.excludedProgIds = [];
        },
        excludeAll() {
            this.excludedProgIds = [...this.allProgIds];
        },
        get selectedCount() {
            return Math.max(0, this.allProgIds.length - this.excludedProgIds.length);
        },
        printPdf() {
            window.print();
        }
    };
}
</script>
@endsection
