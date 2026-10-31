<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Points Progression Matrix Sheet | QUAF 2026</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
                padding: 4mm 6mm !important;
                margin: 0 !important;
            }
            .page-break {
                page-break-before: always;
            }
            tr, .avoid-break {
                page-break-inside: avoid !important;
            }
            thead {
                display: table-header-group;
            }
            .matrix-table {
                border-collapse: collapse !important;
                width: 100% !important;
            }
            .matrix-table th, .matrix-table td {
                border: 1px solid #1e293b !important;
            }
        }
        @page {
            size: A4 landscape;
            margin: 6mm;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sora antialiased min-h-screen py-6 px-3 sm:px-6">

    <!-- Top Toolbar (Hidden on Print) -->
    <div class="max-w-7xl mx-auto mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4 no-print">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-amber-100 text-amber-900">
                    Official Ledger
                </span>
                <span class="text-xs text-slate-500 font-mono">Sequential Result Points Matrix</span>
            </div>
            <h1 class="text-lg font-black text-slate-900 mt-1 font-sora">
                Points Progression Matrix Sheet (1, 1+2, 1+2+3...)
            </h1>
            <p class="text-xs text-slate-500 font-anek">
                ഓരോ മത്സരത്തിലെയും ഗ്രൂപ്പ് പോയിന്റും, തുടർന്നുള്ള ക്യുമുലേറ്റീവ് ആകെ പോയിന്റും (Cumulative Running Total).
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Zone Filter -->
            <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                @if($selectedCategory)
                    <input type="hidden" name="category" value="{{ $selectedCategory }}">
                @endif
                <select name="zone" onchange="this.form.submit()" class="text-xs py-1.5 px-2.5 rounded-xl border border-slate-300 font-bold bg-white focus:outline-none">
                    <option value="">All Zones</option>
                    @foreach(['D1', 'D2', 'General'] as $z)
                        <option value="{{ $z }}" {{ $selectedZone === $z ? 'selected' : '' }}>{{ $z }} Zone</option>
                    @endforeach
                </select>
            </form>

            <!-- Print Button -->
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-sm flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Sheet (A4)</span>
            </button>

            <!-- Back Button -->
            @php
                $backRoute = route('admin.results.index');
                if (request()->routeIs('announcer.*')) {
                    $backRoute = route('announcer.index');
                } elseif (request()->routeIs('media.*')) {
                    $backRoute = route('media.results.index');
                }
            @endphp
            <a href="{{ $backRoute }}" class="px-3 py-2 rounded-xl border border-slate-300 hover:border-slate-400 bg-white text-slate-700 font-bold text-xs transition">
                &larr; Back
            </a>
        </div>
    </div>

    <!-- Printable Paper Sheet -->
    <div class="print-container max-w-7xl mx-auto bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm text-slate-900">
        
        <!-- Sheet Header -->
        <div class="border-b-2 border-slate-900 pb-4 mb-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/quaf-logo-dark.svg') }}" alt="QUAF Logo" class="h-10 w-auto" onerror="this.style.display='none'">
                <div>
                    <div class="text-[10px] font-mono font-bold tracking-widest text-slate-500 uppercase">
                        QUAF 2026 • MARKAZ CULTURAL FESTIVAL
                    </div>
                    <h2 class="text-xl font-black text-slate-950 font-sora uppercase tracking-tight">
                        Official Points Progression Matrix Sheet
                    </h2>
                    <div class="text-[11px] font-mono text-slate-600 mt-0.5">
                        <span>Total Announced Competitions: <strong>{{ $totalResults }}</strong></span>
                        @if($selectedZone) &bull; <span>Zone: <strong>{{ $selectedZone }}</strong></span> @endif
                        &bull; <span>Status: <strong>Declared & Verified</strong></span>
                    </div>
                </div>
            </div>

            <div class="text-right font-mono text-[11px] text-slate-500">
                <div>Generated: <strong>{{ now()->format('d M Y, h:i A') }}</strong></div>
                <div class="text-[10px] text-slate-400">quaf.ihyaussunna.in</div>
            </div>
        </div>

        <!-- Matrix Table (Exact Match to Requested Structure) -->
        <div class="overflow-x-auto">
            <table class="matrix-table w-full text-left border-collapse border border-slate-900 text-xs">
                <!-- Table Header -->
                <thead>
                    <tr class="bg-slate-900 text-white font-mono uppercase text-center border-b-2 border-slate-900">
                        <th class="py-3 px-2 border border-slate-700 w-12 text-center text-sm font-black">
                            R No
                        </th>
                        <th class="py-3 px-3 border border-slate-700 text-left min-w-[200px] text-sm font-black">
                            Program Name
                        </th>
                        @foreach($groups as $grp)
                            <th class="py-3 px-2 border border-slate-700 min-w-[130px] text-center" style="border-top: 4px solid {{ $grp->color_hex ?? '#be1e2d' }};">
                                <div class="text-base font-black tracking-tight font-sora">{{ $grp->name }}</div>
                                <div class="text-[10px] opacity-80 font-mono tracking-wider">({{ $grp->code }})</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody class="divide-y divide-slate-300 font-mono">
                    @forelse($rows as $r)
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $loop->iteration % 2 === 0 ? 'bg-slate-50/40' : 'bg-white' }}">
                            <!-- R No -->
                            <td class="py-2.5 px-2 border border-slate-300 text-center font-black text-slate-900 text-sm bg-slate-100/50">
                                {{ $r['formatted_result_number'] }}
                            </td>

                            <!-- Program Name & Details -->
                            <td class="py-2.5 px-3 border border-slate-300 font-sora">
                                <div class="font-bold text-slate-950 text-xs">
                                    {{ $r['program_name'] }}
                                </div>
                                <div class="text-[10px] text-slate-500 font-mono flex items-center gap-1.5 mt-0.5">
                                    <span>{{ $r['program_code'] }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $r['category_name'] }}</span>
                                    @if($r['zone'])
                                        <span>&bull;</span>
                                        <span>{{ $r['zone'] }}</span>
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
                                <td class="py-2 px-2 border border-slate-300 text-center {{ $hasPoints ? 'bg-amber-50/40' : '' }}">
                                    <!-- Points in this result -->
                                    <div class="flex items-center justify-center gap-1">
                                        @if($hasPoints)
                                            <span class="inline-block px-1.5 py-0.5 rounded text-xs font-black bg-amber-200 text-amber-950 font-mono">
                                                +{{ $thisPts }}
                                            </span>
                                        @else
                                            <span class="text-slate-300 text-xs font-bold">-</span>
                                        @endif
                                    </div>

                                    <!-- Cumulative Total (1, 1+2, 1+2+3...) -->
                                    <div class="mt-1 text-xs font-black text-slate-900 font-mono tracking-tight">
                                        <span class="text-[9px] text-slate-400 font-normal">Tot:</span> {{ $cumul }}
                                    </div>

                                    <!-- Breakdown Notes (Position, Grade) -->
                                    @if(!empty($placements) || !empty($grades))
                                        <div class="mt-0.5 text-[9px] text-slate-600 font-sans truncate" title="{{ implode(', ', $placements) }} {{ implode(', ', $grades) }}">
                                            @if(!empty($placements))
                                                <span class="font-bold text-amber-800">{{ implode(', ', $placements) }}</span>
                                            @endif
                                            @if(!empty($grades))
                                                <span class="text-emerald-700">({{ implode(', ', $grades) }})</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($groups) + 2 }}" class="py-12 text-center text-slate-400 font-mono">
                                No announced competition results found yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <!-- Table Footer (Grand Totals) -->
                <tfoot>
                    <tr class="bg-slate-900 text-white font-mono text-center font-black border-t-2 border-slate-900">
                        <td colspan="2" class="py-3 px-3 text-right uppercase tracking-wider text-xs border border-slate-700">
                            GRAND TOTAL STANDING POINTS:
                        </td>
                        @foreach($groups as $grp)
                            @php
                                $st = $totals[$grp->id] ?? null;
                            @endphp
                            <td class="py-3 px-2 border border-slate-700 bg-slate-950">
                                <div class="text-base font-black text-amber-400 font-mono">
                                    {{ $st['total_points'] ?? 0 }} pts
                                </div>
                                <div class="text-[10px] text-slate-400 font-normal flex items-center justify-center gap-1 mt-0.5">
                                    <span>Rank: <strong>#{{ $st['rank'] ?? '-' }}</strong></span>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                    <tr class="bg-slate-100 text-slate-700 font-mono text-center text-[10px] border-b border-slate-300">
                        <td colspan="2" class="py-2 px-3 text-right uppercase tracking-wider border border-slate-300 font-bold">
                            Score Breakdown (Position / Grade):
                        </td>
                        @foreach($groups as $grp)
                            @php
                                $st = $totals[$grp->id] ?? null;
                            @endphp
                            <td class="py-2 px-2 border border-slate-300">
                                <div>Pos: <strong class="text-slate-900">{{ $st['position_points'] ?? 0 }}</strong></div>
                                <div>Grade: <strong class="text-emerald-700">{{ $st['grade_points'] ?? 0 }}</strong></div>
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer Signatures Strip for Official Records -->
        <div class="mt-8 pt-6 border-t border-slate-300 grid grid-cols-3 text-center text-xs font-sora text-slate-600">
            <div>
                <div class="h-8"></div>
                <div class="border-t border-slate-400 pt-1 font-bold text-slate-800">Scrutiny / Tabulation Officer</div>
                <div class="text-[10px] text-slate-500">QUAF Results Scrutiny Wing</div>
            </div>
            <div>
                <div class="h-8"></div>
                <div class="border-t border-slate-400 pt-1 font-bold text-slate-800">Chief Announcer</div>
                <div class="text-[10px] text-slate-500">Official Announcement Desk</div>
            </div>
            <div>
                <div class="h-8"></div>
                <div class="border-t border-slate-400 pt-1 font-bold text-slate-800">Festival General Convener</div>
                <div class="text-[10px] text-slate-500">QUAF 2026 Directorate</div>
            </div>
        </div>

    </div>

</body>
</html>
