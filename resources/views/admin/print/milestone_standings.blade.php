<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Team Standings — {{ $milestoneLabel }} | QUAF</title>
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
                padding: 6mm 10mm !important;
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
        }
        @page {
            size: A4 portrait;
            margin: 8mm;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sora antialiased min-h-screen py-6 px-3 sm:px-6"
      x-data="{
          customCount: {{ (int) $count }},
          printReport() {
              window.print();
          },
          goToCount(n) {
              window.location.href = '{{ route('admin.print.milestone-standings') }}?count=' + n;
          }
      }">

    <!-- Top Customization Control Bar (Hidden on Print) -->
    <div class="no-print max-w-5xl mx-auto mb-6 bg-white border border-slate-300 rounded-3xl p-5 shadow-lg space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-[#be1e2d] text-white uppercase">
                        Milestone Standings &amp; PDF Print
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        {{ $milestoneLabel }}
                    </span>
                </div>
                <h1 class="text-xl font-black text-slate-900 mt-1 font-sora">
                    ടീം പോയിന്റ് നില &amp; പ്രിന്റ് റിപ്പോർട്ട് (Team Standings Customizer)
                </h1>
                <p class="text-xs text-slate-500 font-mono mt-0.5">
                    ആകെ പ്രഖ്യാപിച്ച ഫലങ്ങൾ: <strong>{{ $totalDeclared }}</strong> • ഈ റിപ്പോർട്ടിൽ ഉൾപ്പെടുത്തിയത്: <strong>{{ $effectiveCount }}</strong> ഫലങ്ങൾ
                </p>
            </div>
            
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.results.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-mono font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition">
                    &larr; Back to Results
                </a>
                <button @click="printReport()" class="px-5 py-2.5 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#be1e2d] hover:bg-[#a01624] text-white flex items-center gap-2 shadow-md transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print / Save PDF</span>
                </button>
            </div>
        </div>

        <!-- Quick Milestone Buttons & Custom Number Input -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 font-mono text-xs">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-slate-500 font-bold uppercase text-[11px]">Quick Milestones:</span>
                <button type="button" @click="goToCount(5)" class="px-3 py-1.5 rounded-xl font-bold transition {{ (int)$count === 5 ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    After 5 Results
                </button>
                <button type="button" @click="goToCount(10)" class="px-3 py-1.5 rounded-xl font-bold transition {{ (int)$count === 10 ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    After 10 Results
                </button>
                <button type="button" @click="goToCount(15)" class="px-3 py-1.5 rounded-xl font-bold transition {{ (int)$count === 15 ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    After 15 Results
                </button>
                <button type="button" @click="goToCount(20)" class="px-3 py-1.5 rounded-xl font-bold transition {{ (int)$count === 20 ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    After 20 Results
                </button>
                @if($totalDeclared > 0)
                    <button type="button" @click="goToCount({{ $totalDeclared }})" class="px-3 py-1.5 rounded-xl font-bold transition {{ (int)$count === $totalDeclared ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' }}">
                        All Declared ({{ $totalDeclared }})
                    </button>
                @endif
            </div>

            <!-- Custom Count Input -->
            <form method="GET" action="{{ route('admin.print.milestone-standings') }}" class="flex items-center gap-2">
                <span class="text-slate-500 font-bold uppercase text-[11px]">Custom After:</span>
                <div class="relative w-24">
                    <input type="number" name="count" min="1" max="999" value="{{ $count }}"
                           class="w-full pl-3 pr-2 py-1.5 rounded-xl border border-slate-300 font-black text-center text-sm focus:outline-none focus:border-[#be1e2d]">
                </div>
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white font-bold text-xs uppercase hover:bg-slate-800 transition">
                    View
                </button>
            </form>
        </div>
    </div>

    <!-- Official Printable Document -->
    <div class="print-container max-w-5xl mx-auto bg-white border border-slate-300 rounded-3xl p-8 sm:p-10 shadow-xl space-y-6">
        
        <!-- Document Letterhead & Festival Header -->
        <div class="border-b-2 border-slate-900 pb-5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/quaf-logo-favicon.svg') }}" alt="QUAF" class="h-14 w-auto object-contain shrink-0">
                <div>
                    <span class="text-xs font-mono font-black tracking-widest text-[#be1e2d] uppercase block">
                        QUAF SEASON 09 &bull; INTER-HOUSE FESTIVAL 2026
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-sora">
                        OFFICIAL TEAM STANDINGS
                    </h1>
                    <span class="text-xs text-slate-600 font-mono">
                        Office of the Controller of Examinations &amp; Festival Directorate
                    </span>
                </div>
            </div>

            <div class="text-right shrink-0">
                <div class="inline-block px-4 py-1.5 rounded-xl bg-slate-900 text-white font-mono font-black text-sm uppercase tracking-wider">
                    {{ $milestoneLabel }}
                </div>
                <span class="text-[11px] font-mono text-slate-400 block mt-1">
                    Date: {{ now()->format('d M Y, h:i A') }}
                </span>
            </div>
        </div>

        <!-- Milestone Highlight Banner -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs font-mono">
            <div>
                <span class="text-slate-500 uppercase tracking-wider font-bold">Championship Standing:</span>
                <strong class="text-slate-900 ml-1 font-sora text-sm font-black">{{ $milestoneLabel }}</strong>
            </div>
            <div class="text-slate-500">
                <span>Calculated on strictly the first <strong class="text-slate-900">{{ $effectiveCount }}</strong> declared competition results.</span>
            </div>
        </div>

        <!-- Official Team Leaderboard Table -->
        <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-900 text-white uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-16">Rank</th>
                        <th class="px-4 py-3.5">House / Team</th>
                        <th class="px-4 py-3.5 text-center">1st</th>
                        <th class="px-4 py-3.5 text-center">2nd</th>
                        <th class="px-4 py-3.5 text-center">3rd</th>
                        <th class="px-4 py-3.5 text-right">Position Pts</th>
                        <th class="px-4 py-3.5 text-right">Grade Pts</th>
                        <th class="px-5 py-3.5 text-right font-black text-amber-300">Total Points</th>
                        <th class="px-4 py-3.5 text-right">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($standings as $item)
                        @php
                            $g = $item['group'];
                            $isTop = $item['rank'] === 1;
                        @endphp
                        <tr class="{{ $isTop ? 'bg-amber-50/60 font-bold' : ($item['rank'] === 2 ? 'bg-slate-50/50' : 'bg-white') }}">
                            <!-- Rank -->
                            <td class="px-4 py-4 text-center">
                                @if($item['rank'] === 1)
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-400 text-slate-950 font-black text-xs shadow-xs">
                                        1
                                    </span>
                                @elseif($item['rank'] === 2)
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-300 text-slate-900 font-black text-xs shadow-xs">
                                        2
                                    </span>
                                @elseif($item['rank'] === 3)
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-600 text-white font-black text-xs shadow-xs">
                                        3
                                    </span>
                                @else
                                    <span class="font-bold text-slate-500 text-sm">
                                        {{ $item['rank'] }}
                                    </span>
                                @endif
                            </td>

                            <!-- Team -->
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-white text-xs shrink-0 font-mono shadow-2xs"
                                         style="background-color: {{ $g->color_hex ?: '#be1e2d' }}">
                                        {{ substr($g->code ?: $g->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="font-sora font-black text-slate-900 text-sm block">
                                            {{ $g->name }}
                                        </span>
                                        <span class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">
                                            {{ $g->code }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- 1st -->
                            <td class="px-4 py-4 text-center">
                                <span class="px-2 py-0.5 rounded {{ $item['first_count'] > 0 ? 'bg-amber-100 text-amber-900 font-black' : 'text-slate-400' }}">
                                    {{ $item['first_count'] }}
                                </span>
                            </td>

                            <!-- 2nd -->
                            <td class="px-4 py-4 text-center">
                                <span class="px-2 py-0.5 rounded {{ $item['second_count'] > 0 ? 'bg-slate-200 text-slate-900 font-black' : 'text-slate-400' }}">
                                    {{ $item['second_count'] }}
                                </span>
                            </td>

                            <!-- 3rd -->
                            <td class="px-4 py-4 text-center">
                                <span class="px-2 py-0.5 rounded {{ $item['third_count'] > 0 ? 'bg-amber-50 text-amber-800 font-black' : 'text-slate-400' }}">
                                    {{ $item['third_count'] }}
                                </span>
                            </td>

                            <!-- Position Pts -->
                            <td class="px-4 py-4 text-right text-slate-700">
                                {{ $item['position_points'] }}
                            </td>

                            <!-- Grade Pts -->
                            <td class="px-4 py-4 text-right text-slate-700">
                                {{ $item['grade_points'] }}
                            </td>

                            <!-- Total Points -->
                            <td class="px-5 py-4 text-right font-black font-sora text-base sm:text-lg text-slate-900">
                                <span class="{{ $isTop ? 'text-[#be1e2d]' : 'text-slate-900' }}">
                                    {{ $item['points'] }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono font-bold uppercase ml-0.5">PTS</span>
                            </td>

                            <!-- Margin -->
                            <td class="px-4 py-4 text-right text-slate-500 font-bold">
                                @if($item['diff_from_leader'] === 0)
                                    <span class="text-emerald-700 font-black">LEADER</span>
                                @else
                                    <span class="text-rose-700">-{{ $item['diff_from_leader'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-slate-400">
                                ടീം വിവരങ്ങൾ ലഭ്യമല്ല.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Competitions Included in this Milestone -->
        <div class="space-y-3 pt-2">
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                <h3 class="text-xs font-mono uppercase font-black tracking-wider text-slate-800 flex items-center gap-2">
                    <span>Competitions Included (ആകെ {{ $results->count() }} മത്സരങ്ങൾ):</span>
                </h3>
                <span class="text-[11px] font-mono text-slate-400">In order of declaration</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[11px] font-mono">
                    <thead class="bg-slate-100 text-slate-600 uppercase text-[10px] border-y border-slate-200">
                        <tr>
                            <th class="px-3 py-2 w-10">#</th>
                            <th class="px-3 py-2">Competition / Program</th>
                            <th class="px-3 py-2">Zone</th>
                            <th class="px-3 py-2">1st Place Winner</th>
                            <th class="px-3 py-2">2nd Place</th>
                            <th class="px-3 py-2">3rd Place</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($results as $idx => $res)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-3 py-2 font-bold text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-3 py-2 font-bold text-slate-900 font-sora">
                                    {{ $res->program?->name }}
                                </td>
                                <td class="px-3 py-2 text-slate-500">
                                    {{ $res->program?->zone?->name ?? $res->program?->eligibility ?? 'All' }}
                                </td>
                                <td class="px-3 py-2 font-semibold text-slate-800">
                                    {{ $res->firstEntry?->student?->name ?? $res->firstEntry?->group?->name ?? '—' }}
                                    @if($res->firstEntry?->group)
                                        <span class="text-[10px] font-bold text-slate-500">({{ $res->firstEntry->group->code }})</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-slate-700">
                                    {{ $res->secondEntry?->student?->name ?? $res->secondEntry?->group?->name ?? '—' }}
                                    @if($res->secondEntry?->group)
                                        <span class="text-[10px] text-slate-500">({{ $res->secondEntry->group->code }})</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-slate-700">
                                    {{ $res->thirdEntry?->student?->name ?? $res->thirdEntry?->group?->name ?? '—' }}
                                    @if($res->thirdEntry?->group)
                                        <span class="text-[10px] text-slate-500">({{ $res->thirdEntry->group->code }})</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-4 text-center text-slate-400">
                                    പ്രഖ്യാപിച്ച മത്സര ഫലങ്ങൾ നിലവിലില്ല.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Official Signatures Block -->
        <div class="avoid-break pt-8 border-t-2 border-slate-900 grid grid-cols-3 gap-8 text-center font-mono">
            <div class="space-y-6">
                <div class="h-10"></div>
                <div class="border-t border-slate-400 pt-1.5">
                    <span class="font-bold text-xs text-slate-900 block">General Convener</span>
                    <span class="text-[10px] text-slate-500 block uppercase">QUAF 2026 Committee</span>
                </div>
            </div>

            <div class="space-y-6">
                <div class="h-10"></div>
                <div class="border-t border-slate-400 pt-1.5">
                    <span class="font-bold text-xs text-slate-900 block">Controller of Examinations</span>
                    <span class="text-[10px] text-slate-500 block uppercase">Results Tabulation Desk</span>
                </div>
            </div>

            <div class="space-y-6">
                <div class="h-10"></div>
                <div class="border-t border-slate-400 pt-1.5">
                    <span class="font-bold text-xs text-slate-900 block">Chairman / Jury Chief</span>
                    <span class="text-[10px] text-slate-500 block uppercase">Central Festival Directorate</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer Note -->
    <div class="no-print max-w-5xl mx-auto mt-6 text-center text-xs font-mono text-slate-400">
        QUAF 2026 Season 09 &bull; Inter-House Festival Management System
    </div>

</body>
</html>
