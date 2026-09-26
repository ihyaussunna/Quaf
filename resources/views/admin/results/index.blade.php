@extends('layouts.admin', ['title' => 'Results & Team Leaderboard | Quaf'])

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 font-sans tracking-tight">Results & Leaderboard</h1>
            <p class="text-xs text-slate-500 mt-1 font-sans">Manage program verdicts, review jury scorecards, and monitor real-time team championship standings.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Publish Team Points -->
            <form method="POST" action="{{ route('admin.points.recalculate') }}" class="inline">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#be1e2d] text-white font-semibold text-xs hover:bg-[#a01624] transition-all flex items-center gap-2 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Publish Team Points</span>
                </button>
            </form>

            <!-- Print / Export PDF -->
            <a href="{{ route('admin.print.results') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 font-semibold text-xs hover:bg-slate-50 hover:border-[#be1e2d] transition-all flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print / PDF Report</span>
            </a>

            <!-- Export CSV -->
            <a href="{{ route('admin.exports.download', 'results') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-all flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>CSV</span>
            </a>

            @if($pendingPrograms->isNotEmpty())
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white font-semibold text-xs hover:bg-slate-800 transition-all flex items-center gap-1.5 shadow-2xs">
                        <span>+ Enter Result</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-72 bg-white border border-slate-200 rounded-xl p-1.5 shadow-xl z-20 space-y-0.5 text-xs max-h-64 overflow-y-auto" style="display: none;">
                        @foreach($pendingPrograms as $prog)
                            <a href="{{ route('admin.results.create', ['program_id' => $prog->id]) }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:text-[#be1e2d] hover:bg-slate-50 truncate transition-colors">
                                <span class="font-mono text-[11px] text-slate-400">[{{ $prog->code }}]</span> {{ $prog->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Grid: Left = Results, Right = Real-time Team Leaderboard -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Panel: Results List & Status Tabs (8 cols on lg) -->
        <div class="lg:col-span-8 space-y-4">
            <!-- Status Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 bg-white p-1.5 rounded-xl border border-slate-200 shadow-2xs">
                <a href="{{ route('admin.results.index') }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all {{ empty($status) ? 'bg-[#be1e2d] text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    All ({{ $counts['all'] }})
                </a>
                <a href="{{ route('admin.results.index', ['status' => 'pending']) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all {{ $status === 'pending' ? 'bg-[#be1e2d] text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    Pending ({{ $counts['pending'] }})
                </a>
                <a href="{{ route('admin.results.index', ['status' => 'in_progress']) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all {{ $status === 'in_progress' ? 'bg-[#be1e2d] text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    In Progress ({{ $counts['in_progress'] }})
                </a>
                <a href="{{ route('admin.results.index', ['status' => 'submitted']) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all {{ $status === 'submitted' ? 'bg-[#be1e2d] text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    Submitted ({{ $counts['submitted'] }})
                </a>
                <a href="{{ route('admin.results.index', ['status' => 'published']) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all {{ $status === 'published' ? 'bg-[#be1e2d] text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    Published ({{ $counts['published'] }})
                </a>
            </div>

            <!-- Results Table -->
            <div class="rounded-xl bg-white border border-slate-200 overflow-hidden shadow-2xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200 font-semibold text-[11px]">
                            <tr>
                                <th class="px-5 py-3.5">Competition</th>
                                <th class="px-5 py-3.5">1st Place</th>
                                <th class="px-5 py-3.5">2nd Place</th>
                                <th class="px-5 py-3.5">3rd Place</th>
                                <th class="px-5 py-3.5">Status</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($results as $res)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-[11px] text-[#be1e2d] bg-red-50 px-2 py-0.5 rounded border border-orange-100">{{ $res->program->code }}</span>
                                            <div>
                                                <span class="font-semibold text-slate-900 block">{{ $res->program->name }}</span>
                                                @if($res->program->malayalam_name)
                                                    <span class="text-[11px] text-slate-400 block font-ml">{{ $res->program->malayalam_name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($res->firstEntry)
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-800 font-black text-[10px] flex items-center justify-center">1</span>
                                                <div>
                                                    <span class="text-slate-900 font-semibold block">{{ $res->firstEntry->student?->name ?? 'Team ' . $res->firstEntry->group?->name }}</span>
                                                    <span class="text-[10px] text-slate-400 font-mono">#{{ $res->firstEntry->chest_number }} ({{ $res->firstEntry->group?->code }})</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($res->secondEntry)
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 font-black text-[10px] flex items-center justify-center">2</span>
                                                <div>
                                                    <span class="text-slate-800 font-medium block">{{ $res->secondEntry->student?->name ?? 'Team ' . $res->secondEntry->group?->name }}</span>
                                                    <span class="text-[10px] text-slate-400 font-mono">#{{ $res->secondEntry->chest_number }} ({{ $res->secondEntry->group?->code }})</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($res->thirdEntry)
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-5 h-5 rounded-full bg-amber-50 text-amber-700 font-black text-[10px] flex items-center justify-center">3</span>
                                                <div>
                                                    <span class="text-slate-800 font-medium block">{{ $res->thirdEntry->student?->name ?? 'Team ' . $res->thirdEntry->group?->name }}</span>
                                                    <span class="text-[10px] text-slate-400 font-mono">#{{ $res->thirdEntry->chest_number }} ({{ $res->thirdEntry->group?->code }})</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($res->status === 'published')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Published
                                            </span>
                                        @elseif($res->status === 'announced')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Announced (Stage)
                                            </span>
                                        @elseif($res->status === 'send' || $res->status === 'delivered')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-300 animate-pulse">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Sent to Announcer
                                            </span>
                                        @elseif($res->status === 'verified')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Verified
                                            </span>
                                        @elseif($res->status === 'submitted')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Submitted
                                            </span>
                                        @elseif($res->status === 'under_review')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-[#005c94] border border-blue-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Under Review
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> {{ $res->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                        @if(!in_array($res->status, ['send', 'delivered', 'announced', 'published']))
                                            <form method="POST" action="{{ route('admin.results.send-to-announcer', $res) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-amber-500 text-slate-900 font-bold text-[11px] hover:bg-amber-400 transition-colors shadow-2xs" title="Send to Stage Announcer Desk">
                                                    Send to Announcer
                                                </button>
                                            </form>
                                        @endif
                                        @if($res->status === 'announced' || $res->status === 'published')
                                            <a href="{{ route('media.results.studio', $res) }}" class="px-2.5 py-1 rounded-lg bg-purple-600 text-white font-semibold text-[11px] hover:bg-purple-700 transition-colors shadow-2xs">
                                                Media Poster
                                            </a>
                                        @endif
                                        @if($res->status !== 'published')
                                            <form method="POST" action="{{ route('admin.results.publish', $res) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white font-semibold text-[11px] hover:bg-emerald-700 transition-colors shadow-2xs">
                                                    Publish
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.results.edit', $res) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold text-[11px] hover:bg-slate-200 transition-colors">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                        <div class="max-w-xs mx-auto text-center space-y-2">
                                            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </div>
                                            <p class="font-medium text-slate-600">No results found</p>
                                            <p class="text-[11px] text-slate-400">Results will appear once mark sheets are evaluated and verified.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($results->hasPages())
                    <div class="p-3 border-t border-slate-100 bg-slate-50/50">
                        {{ $results->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Panel: Real-time Team Leaderboard (4 cols on lg) -->
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-red-50 text-[#be1e2d] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <h2 class="font-bold text-slate-900 text-sm font-sans">Team Leaderboard</h2>
                    </div>
                    <span class="text-[10px] font-mono text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full font-bold border border-emerald-100 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> LIVE
                    </span>
                </div>

                <!-- Teams List -->
                <div class="divide-y divide-slate-100 mt-2">
                    @forelse($teams as $index => $team)
                        <div class="py-3 flex items-center justify-between gap-3 hover:bg-slate-50/50 px-2 rounded-lg transition-colors">
                            <div class="flex items-center gap-3">
                                <!-- Rank Badge -->
                                @if($index === 0)
                                    <span class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-300 to-amber-500 text-slate-900 font-black text-xs flex items-center justify-center shadow-xs">
                                        1
                                    </span>
                                @elseif($index === 1)
                                    <span class="w-7 h-7 rounded-full bg-gradient-to-br from-slate-200 to-slate-400 text-slate-900 font-black text-xs flex items-center justify-center shadow-xs">
                                        2
                                    </span>
                                @elseif($index === 2)
                                    <span class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-600 to-amber-800 text-white font-black text-xs flex items-center justify-center shadow-xs">
                                        3
                                    </span>
                                @else
                                    <span class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center">
                                        {{ $index + 1 }}
                                    </span>
                                @endif

                                <div>
                                    <span class="font-semibold text-slate-900 text-xs block">{{ $team->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $team->code }} • {{ $team->manager_name ?? 'Manager' }}</span>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="font-mono font-black text-base text-slate-900 block leading-tight">{{ number_format($team->points_cache ?? 0) }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">PTS</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">
                            No team points recorded yet.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400 font-sans">
                    <span>Updates instantly on results</span>
                    <a href="{{ route('admin.points.index') }}" class="text-[#be1e2d] hover:underline font-medium">Points Rules →</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
