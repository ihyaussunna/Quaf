@extends('layouts.admin', ['title' => 'Points Progression Matrix | QUAF 2026'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.results.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-700 transition">
                    &larr; Results Leaderboard
                </a>
                <span class="text-slate-300">/</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-900 font-mono">
                    Points Matrix
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-2 font-sora">
                Sequential Points Progression Matrix
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-3xl font-anek">
                അനൗൺസ് ചെയ്ത ഓരോ മത്സരത്തിലും ഓരോ ടീമിനും ലഭിച്ച പോയിന്റുകളും, ഓരോ മത്സരം കഴിയുമ്പോഴുമുള്ള ക്യുമുലേറ്റീവ് റണ്ണിംഗ് ആകെ പോയിന്റും (1 Total, 1+2 Total, 1+2+3 Total...).
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Print Sheet Button -->
            <a href="{{ route('admin.print.progression-matrix', ['zone' => $selectedZone, 'category' => $selectedCategory]) }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print A4 Matrix Sheet</span>
            </a>

            <!-- Back to Results -->
            <a href="{{ route('admin.results.index') }}" 
               class="px-4 py-2.5 rounded-xl border border-slate-300 hover:border-slate-400 bg-white text-slate-700 font-bold text-xs transition-colors flex items-center gap-1.5">
                <span>Leaderboard</span>
            </a>
        </div>
    </div>

    <!-- Live Team Score Summary Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @foreach($totals as $gid => $tItem)
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs relative overflow-hidden flex flex-col justify-between">
                <div class="absolute top-0 left-0 right-0 h-1.5" style="background-color: {{ $tItem['color_hex'] ?? '#be1e2d' }};"></div>
                <div class="flex items-center justify-between text-xs font-mono mb-2 pt-1">
                    <span class="px-2 py-0.5 rounded font-black {{ $tItem['rank'] === 1 ? 'bg-amber-400 text-slate-950 shadow-xs' : ($tItem['rank'] === 2 ? 'bg-slate-200 text-slate-900' : ($tItem['rank'] === 3 ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-700')) }}">
                        #{{ $tItem['rank'] }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $tItem['code'] }}</span>
                </div>
                <div>
                    <div class="font-bold text-slate-900 text-sm truncate font-sora">{{ $tItem['name'] }}</div>
                    <div class="mt-1 flex items-baseline gap-1.5 font-mono">
                        <span class="text-2xl font-black text-amber-600">{{ number_format($tItem['total_points']) }}</span>
                        <span class="text-[10px] text-slate-400 uppercase font-bold">pts</span>
                    </div>
                    <div class="mt-2 text-[10px] font-mono text-slate-500 flex items-center justify-between border-t border-slate-100 pt-1.5">
                        <span>Pos: <strong>{{ $tItem['position_points'] }}</strong></span>
                        <span>Grade: <strong>{{ $tItem['grade_points'] }}</strong></span>
                        <span>1st: <strong>{{ $tItem['first_count'] }}</strong></span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <form method="GET" action="{{ route('admin.results.progression-matrix') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 font-mono mb-0.5">Zone</label>
                <select name="zone" onchange="this.form.submit()" class="text-xs py-1.5 px-3 rounded-xl border border-slate-300 font-bold bg-white focus:outline-none focus:border-amber-500">
                    <option value="">All Zones</option>
                    @foreach($zones as $z)
                        <option value="{{ $z }}" {{ $selectedZone === $z ? 'selected' : '' }}>{{ $z }} Zone</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 font-mono mb-0.5">Category</label>
                <select name="category" onchange="this.form.submit()" class="text-xs py-1.5 px-3 rounded-xl border border-slate-300 font-bold bg-white focus:outline-none focus:border-amber-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $selectedCategory == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($selectedZone || $selectedCategory)
                <div class="self-end pb-0.5">
                    <a href="{{ route('admin.results.progression-matrix') }}" class="text-xs text-rose-600 hover:underline font-bold">
                        Clear Filter
                    </a>
                </div>
            @endif
        </form>

        <div class="text-xs font-mono text-slate-500 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
            Total Results: <strong>{{ $totalResults }}</strong>
        </div>
    </div>

    <!-- The Progression Matrix Table (Exact Match to User's Format) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-900 text-white font-mono uppercase text-center border-b border-slate-800">
                        <th class="py-3 px-3 w-14 text-center text-sm font-black border-r border-slate-800">
                            R No
                        </th>
                        <th class="py-3 px-4 text-left min-w-[220px] text-sm font-black border-r border-slate-800">
                            Program Name
                        </th>
                        @foreach($groups as $grp)
                            <th class="py-3 px-3 min-w-[140px] text-center border-r border-slate-800 last:border-r-0" style="border-top: 4px solid {{ $grp->color_hex ?? '#be1e2d' }};">
                                <div class="text-base font-black tracking-tight font-sora">{{ $grp->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">({{ $grp->code }})</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200 font-mono">
                    @forelse($rows as $r)
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $loop->iteration % 2 === 0 ? 'bg-slate-50/40' : 'bg-white' }}">
                            <!-- R No -->
                            <td class="py-3 px-3 text-center font-black text-slate-900 text-sm border-r border-slate-200 bg-slate-100/60">
                                {{ $r['formatted_result_number'] }}
                            </td>

                            <!-- Program Name & Details -->
                            <td class="py-3 px-4 font-sora border-r border-slate-200">
                                <div class="font-bold text-slate-900 text-sm">
                                    {{ $r['program_name'] }}
                                </div>
                                <div class="text-[11px] text-slate-500 font-mono flex items-center gap-1.5 mt-0.5">
                                    <span class="font-semibold text-slate-700">{{ $r['program_code'] }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $r['category_name'] }}</span>
                                    @if($r['zone'])
                                        <span>&bull;</span>
                                        <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 text-[10px]">{{ $r['zone'] }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Group Columns -->
                            @foreach($groups as $grp)
                                @php
                                    $tData = $r['teams'][$grp->id] ?? null;
                                    $thisPts = $tData['this_points'] ?? 0;
                                    $cumul = $tData['cumulative_total'] ?? 0;
                                    $placements = $tData['placements'] ?? [];
                                    $grades = $tData['grades'] ?? [];
                                    $hasPoints = $thisPts > 0;
                                @endphp
                                <td class="py-2.5 px-3 text-center border-r border-slate-200 last:border-r-0 {{ $hasPoints ? 'bg-amber-50/40' : '' }}">
                                    <!-- Points in this result -->
                                    <div class="flex items-center justify-center gap-1">
                                        @if($hasPoints)
                                            <span class="inline-block px-2 py-0.5 rounded text-xs font-black bg-amber-200 text-amber-950 font-mono shadow-2xs">
                                                +{{ $thisPts }} pts
                                            </span>
                                        @else
                                            <span class="text-slate-300 text-xs font-bold">-</span>
                                        @endif
                                    </div>

                                    <!-- Cumulative Progressive Total (1, 1+2, 1+2+3...) -->
                                    <div class="mt-1 text-xs font-black text-slate-900 font-mono tracking-tight">
                                        <span class="text-[10px] text-slate-400 font-normal">Tot:</span> {{ $cumul }}
                                    </div>

                                    <!-- Breakdown Notes (Position, Grade) -->
                                    @if(!empty($placements) || !empty($grades))
                                        <div class="mt-0.5 text-[10px] font-sans truncate" title="{{ implode(', ', $placements) }} {{ implode(', ', $grades) }}">
                                            @if(!empty($placements))
                                                <span class="font-bold text-amber-800">{{ implode(', ', $placements) }}</span>
                                            @endif
                                            @if(!empty($grades))
                                                <span class="text-emerald-700 font-medium">({{ implode(', ', $grades) }})</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($groups) + 2 }}" class="py-16 text-center text-slate-400 font-mono">
                                No announced competition results found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <!-- Grand Totals Footer -->
                <tfoot>
                    <tr class="bg-slate-900 text-white font-mono text-center font-black border-t-2 border-slate-900">
                        <td colspan="2" class="py-3 px-4 text-right uppercase tracking-wider text-xs border-r border-slate-800">
                            GRAND TOTAL STANDING POINTS:
                        </td>
                        @foreach($groups as $grp)
                            @php
                                $st = $totals[$grp->id] ?? null;
                            @endphp
                            <td class="py-3 px-3 border-r border-slate-800 last:border-r-0 bg-slate-950">
                                <div class="text-base font-black text-amber-400 font-mono">
                                    {{ $st['total_points'] ?? 0 }} pts
                                </div>
                                <div class="text-[10px] text-slate-400 font-normal mt-0.5">
                                    Rank: <strong>#{{ $st['rank'] ?? '-' }}</strong>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                    <tr class="bg-slate-100 text-slate-700 font-mono text-center text-xs border-b border-slate-200">
                        <td colspan="2" class="py-2 px-4 text-right uppercase tracking-wider border-r border-slate-200 font-bold text-[11px]">
                            Position Points / Grade Points:
                        </td>
                        @foreach($groups as $grp)
                            @php
                                $st = $totals[$grp->id] ?? null;
                            @endphp
                            <td class="py-2 px-3 border-r border-slate-200 last:border-r-0 text-[11px]">
                                <div>Pos: <strong class="text-slate-900">{{ $st['position_points'] ?? 0 }}</strong></div>
                                <div>Grade: <strong class="text-emerald-700">{{ $st['grade_points'] ?? 0 }}</strong></div>
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
