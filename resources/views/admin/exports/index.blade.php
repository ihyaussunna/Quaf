@extends('layouts.admin', ['title' => 'Data Exports & Reports | Quaf'])

@section('content')
<div class="space-y-8" x-data="{
    generating: false,
    selectedType: 'participants',
    queueExport(type) {
        this.generating = true;
        this.selectedType = type;
        setTimeout(() => {
            this.generating = false;
            window.location.href = '{{ url('admin/exports') }}/' + type;
        }, 1200);
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 font-sora tracking-tight">Data Exports & Reports</h1>
            <p class="text-xs text-slate-500 mt-1 font-sora">Generate, customize, and print/export official rosters, jury score sheets, and verdict reports in PDF and CSV format.</p>
        </div>
    </div>

    <!-- Selective Print & PDF Export Hub (Featured Section) -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-6 sm:p-7 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-white/10">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#be1e2d] text-white">SELECTIVE EXPORT</span>
                        <h2 class="text-lg font-bold">Customizable Print & PDF Reports Hub</h2>
                    </div>
                    <p class="text-xs text-slate-300 mt-0.5">Filter by group, zone, or competition and choose exact columns/sections to print or save as PDF.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-1">
                <!-- 1. Print Results -->
                <a href="{{ route('admin.print.results') }}" target="_blank" class="p-4 rounded-2xl bg-white/10 hover:bg-white/15 border border-white/10 hover:border-[#f3bd2e] transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-mono font-bold text-[#f3bd2e]">RESULTS & MERIT</span>
                            <svg class="w-4 h-4 text-white/50 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-white mb-1">Official Results & Leaderboard</h3>
                        <p class="text-[11px] text-slate-300 leading-relaxed">Toggle group championship points, 1st/2nd/3rd rank verdicts, grade tables, and jury signatures.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-xs font-mono font-bold text-[#f3bd2e]">
                        <span>Print / Save PDF →</span>
                    </div>
                </a>

                <!-- 2. Print Students -->
                <a href="{{ route('admin.print.students') }}" target="_blank" class="p-4 rounded-2xl bg-white/10 hover:bg-white/15 border border-white/10 hover:border-[#f3bd2e] transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-mono font-bold text-[#f3bd2e]">PARTICIPANTS</span>
                            <svg class="w-4 h-4 text-white/50 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-white mb-1">Participant Delegate Roster</h3>
                        <p class="text-[11px] text-slate-300 leading-relaxed">Filter by 5 groups or 4 zones, toggle chest numbers, enrolled events, points, and sign-off columns.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-xs font-mono font-bold text-[#f3bd2e]">
                        <span>Print / Save PDF →</span>
                    </div>
                </a>

                <!-- 3. Print Programs -->
                <a href="{{ route('admin.print.programs') }}" target="_blank" class="p-4 rounded-2xl bg-white/10 hover:bg-white/15 border border-white/10 hover:border-[#f3bd2e] transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-mono font-bold text-[#f3bd2e]">COMPETITIONS</span>
                            <svg class="w-4 h-4 text-white/50 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-white mb-1">Master Schedule & Event Manual</h3>
                        <p class="text-[11px] text-slate-300 leading-relaxed">Filter by stage venue or zone, toggle Malayalam titles, durations, weights, and evaluation guidelines.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-xs font-mono font-bold text-[#f3bd2e]">
                        <span>Print / Save PDF →</span>
                    </div>
                </a>

                <!-- 4. Print Entries -->
                <a href="{{ route('admin.print.entries') }}" target="_blank" class="p-4 rounded-2xl bg-white/10 hover:bg-white/15 border border-white/10 hover:border-[#f3bd2e] transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-mono font-bold text-[#f3bd2e]">ENTRIES & ROSTER</span>
                            <svg class="w-4 h-4 text-white/50 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-white mb-1">Entries Roster (Group & Program-Wise)</h3>
                        <p class="text-[11px] text-slate-300 leading-relaxed">Official competition registrations, chest numbers, and team member lists organized group-wise or program-wise.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-xs font-mono font-bold text-[#f3bd2e]">
                        <span>Print / Save PDF →</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="pt-2">
        <h2 class="text-base font-bold text-slate-900 mb-1">Raw CSV & Spreadsheet Data Exports</h2>
        <p class="text-xs text-slate-500 mb-4">Export entire raw database tables directly into Excel compatible UTF-8 CSV files.</p>
    </div>

    <!-- Quick Export Cards (Section to queue and auto-download report files) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- 1. Participants -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs hover:border-[#be1e2d] transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-[#be1e2d] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">{{ number_format($counts['participants']) }} Records</span>
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">Participants Roster</h3>
                <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                    Student profiles, chest numbers, registered categories, unit teams, and points.
                </p>
            </div>
            <button @click="queueExport('participants')"
                    class="w-full py-2 px-3 rounded-xl bg-[#be1e2d] text-white font-semibold text-xs hover:bg-[#a01624] transition-colors flex items-center justify-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Export Participants</span>
            </button>
        </div>

        <!-- 2. Judges -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs hover:border-[#be1e2d] transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">{{ number_format($counts['judges']) }} Records</span>
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">Judges & Jury Panel</h3>
                <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                    Adjudicator profiles, contact numbers, specializations, and competition allocations.
                </p>
            </div>
            <button @click="queueExport('judges')"
                    class="w-full py-2 px-3 rounded-xl bg-slate-900 text-white font-semibold text-xs hover:bg-slate-800 transition-colors flex items-center justify-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Export Judges</span>
            </button>
        </div>

        <!-- 3. Competitions -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs hover:border-[#be1e2d] transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#005c94] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">{{ number_format($counts['competitions']) }} Records</span>
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">Competitions & Schedule</h3>
                <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                    Full events catalog, duration, stage assignment, gender criteria, and weights.
                </p>
            </div>
            <button @click="queueExport('competitions')"
                    class="w-full py-2 px-3 rounded-xl bg-slate-900 text-white font-semibold text-xs hover:bg-slate-800 transition-colors flex items-center justify-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Export Events</span>
            </button>
        </div>

        <!-- 4. Results -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs hover:border-[#be1e2d] transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">{{ number_format($counts['results']) }} Published</span>
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">Official Results</h3>
                <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                    1st, 2nd, and 3rd place winners, affiliated teams, categories, and timestamps.
                </p>
            </div>
            <button @click="queueExport('results')"
                    class="w-full py-2 px-3 rounded-xl bg-slate-900 text-white font-semibold text-xs hover:bg-slate-800 transition-colors flex items-center justify-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Export Results</span>
            </button>
        </div>

        <!-- 5. Entries -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs hover:border-[#be1e2d] transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">{{ number_format($counts['entries'] ?? 0) }} Entries</span>
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">Competition Entries</h3>
                <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                    Group registrations, student allocations, chest numbers, and verification status.
                </p>
            </div>
            <button @click="queueExport('entries')"
                    class="w-full py-2 px-3 rounded-xl bg-slate-900 text-white font-semibold text-xs hover:bg-slate-800 transition-colors flex items-center justify-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Export Entries</span>
            </button>
        </div>
    </div>

    <!-- Active Generation Loading Banner (Alpine) -->
    <div x-show="generating" class="p-4 rounded-xl bg-red-50 border border-red-200 text-slate-800 text-xs flex items-center justify-between" style="display: none;">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-[#be1e2d] animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            <div>
                <span class="font-bold text-slate-900 block">Preparing Report Download...</span>
                <span class="text-slate-500">Compiling data stream into UTF-8 CSV. Download will begin automatically.</span>
            </div>
        </div>
        <span class="text-[10px] font-mono text-[#be1e2d] font-bold">QUEUED</span>
    </div>

    <!-- Summary Table (Export Type, Detailed Summary, Status, Generation Duration, Actions) -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-2xs space-y-0">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-red-50 text-[#be1e2d] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h2 class="font-bold text-slate-900 text-sm font-sora">Recent Generated Reports</h2>
            </div>
            <span class="text-xs text-slate-400 font-sora">Auto-refreshed</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sora">
                <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200 text-[11px] font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Export Type</th>
                        <th class="px-6 py-3.5">Detailed Summary</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 font-mono">Generation Duration</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($recentExports as $export)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-slate-900 block">{{ $export['type'] }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $export['created_at'] }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $export['summary'] }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ $export['status'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-slate-500 text-[11px]">
                                {{ $export['duration'] }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ $export['download_url'] }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#be1e2d] text-white font-semibold text-[11px] hover:bg-[#a01624] transition-colors shadow-2xs">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    <span>Download</span>
                                </a>
                                <button onclick="this.closest('tr').remove()" class="inline-flex items-center p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                No report exports generated yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
