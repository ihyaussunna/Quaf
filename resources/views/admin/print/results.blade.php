<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Official Results & Merit Report — QUAF</title>
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
                padding: 10mm !important;
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
            size: A4;
            margin: 8mm;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sora antialiased min-h-screen py-6 px-3 sm:px-6"
      x-data="{
          showLeaderboard: true,
          showResults: true,
          showGradePoints: true,
          showSignatures: true,
          showZoneMeta: true,
          printReport() {
              window.print();
          }
      }">

    <!-- Top Customization Control Bar (Hidden on Print) -->
    <div class="no-print max-w-5xl mx-auto mb-6 bg-white border border-slate-300 rounded-2xl p-4 sm:p-5 shadow-lg space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-[#be1e2d] text-white">PRINT & PDF EXPORT</span>
                    <h1 class="text-lg font-bold text-slate-900">Results Report Customizer</h1>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Choose sections and filters before printing or saving as PDF</p>
            </div>
            
            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.results.index') }}" class="px-4 py-2 rounded-xl text-xs font-mono font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition-colors">
                    ← Back to Results
                </a>
                <button @click="printReport()" class="px-5 py-2.5 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#be1e2d] hover:bg-[#a01624] text-white flex items-center gap-2 shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print / Save PDF</span>
                </button>
            </div>
        </div>

        <!-- Filter Selectors -->
        <form method="GET" action="{{ route('admin.print.results') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Filter by Zone / Category</label>
                <select name="zone" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($zones as $val => $label)
                        <option value="{{ $val }}" {{ $selectedZone == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Filter by Program</label>
                <select name="program" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Programs</option>
                    @foreach($allPrograms as $prog)
                        <option value="{{ $prog->id }}" {{ $selectedProgramId == $prog->id ? 'selected' : '' }}>
                            [{{ $prog->code }}] {{ $prog->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($selectedZone || $selectedProgramId)
                <div class="flex items-end">
                    <a href="{{ route('admin.print.results') }}" class="px-3 py-2 text-xs font-mono text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl">
                        Reset Filters
                    </a>
                </div>
            @endif
        </form>

        <!-- Section Checkboxes (Interactive Toggle) -->
        <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-4 text-xs font-medium text-slate-700">
            <span class="text-xs font-mono font-bold text-slate-500 uppercase">Include Sections:</span>
            
            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="showLeaderboard" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Group Championship Leaderboard</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="showResults" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Program Results & Winners</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="showGradePoints" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Official Grade/Points Breakdown</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="showSignatures" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Official Signature Lines</span>
            </label>
        </div>
    </div>

    <!-- Printable Official Document Container -->
    <div class="print-container max-w-5xl mx-auto bg-white border border-slate-200 rounded-2xl p-8 sm:p-10 shadow-xl text-slate-900">
        
        <!-- Official Festival Top Masthead Banner -->
        <div class="w-full pb-3 border-b border-slate-300 mb-4 text-center">
            <img src="{{ asset('images/print-pdf-header.svg') }}" alt="Festival Header" class="w-full h-auto max-h-24 sm:max-h-28 object-contain block mx-auto">
        </div>

        <!-- Official Document Header -->
        <div class="flex items-center justify-between pb-6 border-b-2 border-slate-900 gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF Logo" class="h-16 w-auto object-contain">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black font-sora text-slate-900 uppercase tracking-tight">Official Festival Results & Standings</h1>
                    <p class="text-xs font-semibold text-slate-600">Adabic Inheritance • Samastha Centenary Edition</p>
                    <p class="text-[11px] text-slate-500">Ihyaussunna Students Union, Markazu Saquafathi Sunniyya</p>
                </div>
            </div>

            <div class="text-right font-mono text-[11px] text-slate-500 space-y-0.5">
                <div class="font-bold text-slate-900 text-xs">OFFICIAL REPORT</div>
                <div>Date: {{ now()->format('d M Y, h:i A') }}</div>
                <div>Generated: Fest Central Desk</div>
                @if($selectedZone)
                    <div class="text-[#be1e2d] font-bold">Zone: {{ $selectedZone }}</div>
                @endif
            </div>
        </div>

        <div class="text-center my-6">
            <h2 class="text-lg sm:text-xl font-bold uppercase tracking-wider font-sora text-slate-900">
                Official Festival Results & Standings
            </h2>
            <p class="text-xs font-mono text-slate-500 mt-1">Authorized Scorecard and Merit Verdicts</p>
        </div>

        <!-- Section 1: Group Championship Leaderboard -->
        <div x-show="showLeaderboard" class="mb-8 avoid-break">
            <div class="flex items-center justify-between mb-3 border-b border-slate-200 pb-1.5">
                <h3 class="text-sm font-mono uppercase font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#be1e2d]"></span>
                    <span>1. Group Championship Tally</span>
                </h3>
                <span class="text-xs font-mono text-slate-500">5 Official Groups</span>
            </div>

            <table class="w-full text-left text-xs border border-slate-200">
                <thead class="bg-slate-100 font-mono font-bold text-slate-700 uppercase border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3 w-16 text-center">Rank</th>
                        <th class="py-2.5 px-3">Group / Team</th>
                        <th class="py-2.5 px-3">Code</th>
                        <th class="py-2.5 px-3">Group Captain / Leader</th>
                        <th class="py-2.5 px-3 text-right">Total Points</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-sora">
                    @foreach($groups as $idx => $grp)
                        <tr class="{{ $idx === 0 ? 'bg-amber-50/50 font-bold' : ($idx % 2 === 1 ? 'bg-slate-50/50' : 'bg-white') }}">
                            <td class="py-2 px-3 text-center font-mono font-bold">
                                #{{ $idx + 1 }}
                            </td>
                            <td class="py-2 px-3 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full border border-slate-300 shrink-0" style="background-color: {{ $grp->color_hex ?? '#be1e2d' }}"></span>
                                <span class="font-bold text-slate-900">{{ $grp->name }}</span>
                            </td>
                            <td class="py-2 px-3 font-mono text-slate-600">{{ $grp->code }}</td>
                            <td class="py-2 px-3 text-slate-700">{{ $grp->leader?->name ?? ($grp->manager_name ?? '—') }}</td>
                            <td class="py-2 px-3 text-right font-mono font-black text-slate-900 text-sm">
                                {{ $grp->points_cache }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Section 2: Program-Wise Results & Winners -->
        <div x-show="showResults" class="mb-8">
            <div class="flex items-center justify-between mb-3 border-b border-slate-200 pb-1.5">
                <h3 class="text-sm font-mono uppercase font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#005c94]"></span>
                    <span>2. Declared Competition Results</span>
                </h3>
                <span class="text-xs font-mono text-slate-500">{{ $results->count() }} Competitions</span>
            </div>

            @if($results->isEmpty())
                <div class="p-6 text-center border border-dashed border-slate-300 rounded-xl text-xs text-slate-500 font-mono">
                    No published results match the selected filter criteria.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($results as $res)
                        <div class="border border-slate-300 rounded-xl p-3.5 bg-white avoid-break shadow-2xs">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 pb-2 mb-2 border-b border-slate-200 text-xs">
                                <div>
                                    <span class="font-mono font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                        {{ $res->program?->code }}
                                    </span>
                                    <span class="font-bold text-slate-900 text-sm ml-1.5">{{ $res->program?->name }}</span>
                                    @if($res->program?->malayalam_name)
                                        <span class="text-slate-600 ml-1">({{ $res->program->malayalam_name }})</span>
                                    @endif
                                </div>
                                <div class="font-mono text-[11px] text-slate-500 flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-slate-100">{{ $res->program?->eligibility ?? 'General' }}</span>
                                    <span class="uppercase">{{ $res->program?->type }}</span>
                                    <span>• {{ $res->published_at ? $res->published_at->format('d/m/Y') : '' }}</span>
                                </div>
                            </div>

                            <!-- 1st, 2nd, 3rd Winner Podium Row -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                                <!-- 1st -->
                                <div class="p-2.5 rounded-lg bg-amber-50/70 border border-amber-200 flex items-start gap-2">
                                    <span class="px-1.5 py-0.5 rounded bg-[#f3bd2e] text-slate-950 font-bold font-mono text-[10px] shrink-0">1st</span>
                                    <div class="overflow-hidden">
                                        <div class="font-bold text-slate-900 truncate">
                                            {{ $res->firstEntry?->student?->name ?? ($res->firstEntry?->group?->name ?? '—') }}
                                        </div>
                                        <div class="text-[11px] text-slate-600 font-mono truncate">
                                            @if($res->firstEntry?->student)
                                                #{{ $res->firstEntry->chest_number }} • {{ $res->firstEntry->group?->name }}
                                            @else
                                                Team {{ $res->firstEntry?->group?->name }}
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- 2nd -->
                                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 flex items-start gap-2">
                                    <span class="px-1.5 py-0.5 rounded bg-slate-300 text-slate-900 font-bold font-mono text-[10px] shrink-0">2nd</span>
                                    <div class="overflow-hidden">
                                        <div class="font-bold text-slate-900 truncate">
                                            {{ $res->secondEntry?->student?->name ?? ($res->secondEntry?->group?->name ?? '—') }}
                                        </div>
                                        <div class="text-[11px] text-slate-600 font-mono truncate">
                                            @if($res->secondEntry?->student)
                                                #{{ $res->secondEntry->chest_number }} • {{ $res->secondEntry->group?->name }}
                                            @else
                                                Team {{ $res->secondEntry?->group?->name }}
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- 3rd -->
                                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 flex items-start gap-2">
                                    <span class="px-1.5 py-0.5 rounded bg-amber-700/20 text-amber-900 font-bold font-mono text-[10px] shrink-0">3rd</span>
                                    <div class="overflow-hidden">
                                        <div class="font-bold text-slate-900 truncate">
                                            {{ $res->thirdEntry?->student?->name ?? ($res->thirdEntry?->group?->name ?? '—') }}
                                        </div>
                                        <div class="text-[11px] text-slate-600 font-mono truncate">
                                            @if($res->thirdEntry?->student)
                                                #{{ $res->thirdEntry->chest_number }} • {{ $res->thirdEntry->group?->name }}
                                            @else
                                                Team {{ $res->thirdEntry?->group?->name }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Section 3: Official Grade Points Structure -->
        <div x-show="showGradePoints" class="mb-8 avoid-break">
            <div class="flex items-center justify-between mb-3 border-b border-slate-200 pb-1.5">
                <h3 class="text-sm font-mono uppercase font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#009444]"></span>
                    <span>3. Official Point Matrix</span>
                </h3>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <table class="w-full text-left border border-slate-200">
                    <thead class="bg-slate-100 font-mono font-bold text-slate-700 uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-1.5">Position</th>
                            <th class="p-1.5 text-right">Indiv</th>
                            <th class="p-1.5 text-right">2 Mem</th>
                            <th class="p-1.5 text-right">3 Mem</th>
                            <th class="p-1.5 text-right">4-5 Mem</th>
                            <th class="p-1.5 text-right">General</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-mono text-[11px]">
                        <tr>
                            <td class="p-1.5 font-bold text-[#be1e2d]">1st</td>
                            <td class="p-1.5 text-right font-bold">5 pts</td>
                            <td class="p-1.5 text-right font-bold">7 pts</td>
                            <td class="p-1.5 text-right font-bold">10 pts</td>
                            <td class="p-1.5 text-right font-bold">15 pts</td>
                            <td class="p-1.5 text-right font-bold">20 pts</td>
                        </tr>
                        <tr>
                            <td class="p-1.5 font-bold text-slate-800">2nd</td>
                            <td class="p-1.5 text-right">3 pts</td>
                            <td class="p-1.5 text-right">5 pts</td>
                            <td class="p-1.5 text-right">7 pts</td>
                            <td class="p-1.5 text-right">10 pts</td>
                            <td class="p-1.5 text-right">15 pts</td>
                        </tr>
                        <tr>
                            <td class="p-1.5 font-bold text-amber-800">3rd</td>
                            <td class="p-1.5 text-right">1 pt</td>
                            <td class="p-1.5 text-right">3 pts</td>
                            <td class="p-1.5 text-right">4 pts</td>
                            <td class="p-1.5 text-right">5 pts</td>
                            <td class="p-1.5 text-right">10 pts</td>
                        </tr>
                    </tbody>
                </table>

                <table class="w-full text-left border border-slate-200">
                    <thead class="bg-slate-100 font-mono font-bold text-slate-700 uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-2">Grade</th>
                            <th class="p-2">Marks Range</th>
                            <th class="p-2 text-right">Points</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-mono">
                        <tr>
                            <td class="p-2 font-bold text-[#be1e2d]">A+</td>
                            <td class="p-2">90% — 100%</td>
                            <td class="p-2 text-right font-bold">6 pts</td>
                        </tr>
                        <tr>
                            <td class="p-2 font-bold text-[#f3bd2e]">A</td>
                            <td class="p-2">70% — 89%</td>
                            <td class="p-2 text-right font-bold">5 pts</td>
                        </tr>
                        <tr>
                            <td class="p-2 font-bold text-[#005c94]">B</td>
                            <td class="p-2">60% — 69%</td>
                            <td class="p-2 text-right font-bold">3 pts</td>
                        </tr>
                        <tr>
                            <td class="p-2 font-bold text-slate-600">C</td>
                            <td class="p-2">50% — 59%</td>
                            <td class="p-2 text-right font-bold">1 pt</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 4: Official Signatures -->
        <div x-show="showSignatures" class="pt-10 mt-10 border-t border-slate-300 avoid-break">
            <div class="grid grid-cols-3 gap-6 text-center text-xs font-mono">
                <div>
                    <div class="border-b border-slate-400 mb-2 h-8"></div>
                    <div class="font-bold text-slate-900">Chief Adjudicator</div>
                    <div class="text-[10px] text-slate-500">QUAF Jury Board</div>
                </div>
                <div>
                    <div class="border-b border-slate-400 mb-2 h-8"></div>
                    <div class="font-bold text-slate-900">General Convener</div>
                    <div class="text-[10px] text-slate-500">Ashabul Quaf Committee</div>
                </div>
                <div>
                    <div class="border-b border-slate-400 mb-2 h-8"></div>
                    <div class="font-bold text-slate-900">Festival Chairman</div>
                    <div class="text-[10px] text-slate-500">Ihyaussunna Students Union</div>
                </div>
            </div>
            <div class="text-center text-[10px] text-slate-400 font-mono mt-8">
                Generated via QUAF 2026 Central Management Suite • Authenticated with Official Mark System
            </div>
        </div>

    </div>

</body>
</html>
