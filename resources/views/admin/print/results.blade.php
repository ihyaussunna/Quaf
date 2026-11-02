<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Official Results & Merit Report — QUAF</title>
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
                padding: 6mm !important;
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
            margin: 6mm;
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
          viewMode: '{{ $selectedView ?? 'cards' }}',
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
                <p class="text-xs text-slate-500 mt-0.5">Filter by Stage, Zone, or Status and toggle between Cards View and Winners Table PDF format</p>
            </div>
            
            <div class="flex items-center gap-2.5 flex-wrap justify-end">
                <!-- View Mode Switcher -->
                <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200">
                    <button type="button" 
                            @click="viewMode = 'cards'"
                            :class="viewMode === 'cards' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                            class="px-3 py-1.5 rounded-lg text-xs font-mono transition-all flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Cards View</span>
                    </button>
                    <button type="button" 
                            @click="viewMode = 'table'"
                            :class="viewMode === 'table' ? 'bg-slate-900 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                            class="px-3 py-1.5 rounded-lg text-xs font-mono transition-all flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span>Winners Table (PDF List)</span>
                    </button>
                </div>

                @if(request()->user() && request()->user()->role === 'announcer')
                    <a href="{{ route('announcer.index') }}" class="px-4 py-2 rounded-xl text-xs font-mono font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition-colors">
                        ← Announcer
                    </a>
                @else
                    <a href="{{ route('admin.results.index') }}" class="px-4 py-2 rounded-xl text-xs font-mono font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition-colors">
                        ← Results
                    </a>
                @endif
                <button @click="printReport()" class="px-5 py-2.5 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#be1e2d] hover:bg-[#a01624] text-white flex items-center gap-2 shadow-md transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print / Save PDF</span>
                </button>
            </div>
        </div>

        <!-- Filter Selectors -->
        <form method="GET" action="{{ request()->routeIs('announcer.*') ? route('announcer.print.results') : route('admin.print.results') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3">
            <input type="hidden" name="view" :value="viewMode">

            <!-- Stage / Program Type -->
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Stage / Program Type</label>
                <select name="is_stage" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Types (എല്ലാ ഇനങ്ങളും)</option>
                    <option value="1" {{ (string) $selectedIsStage === '1' || $selectedIsStage === 'stage' ? 'selected' : '' }}>Stage Programs Only (സ്റ്റേജ് ഇനങ്ങൾ മാത്രം)</option>
                    <option value="0" {{ (string) $selectedIsStage === '0' || $selectedIsStage === 'off_stage' ? 'selected' : '' }}>Off-Stage Only (നോൺ-സ്റ്റേജ് ഇനങ്ങൾ)</option>
                </select>
            </div>

            <!-- Stage Venue -->
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Stage Venue</label>
                <select name="stage" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Stages (എല്ലാ വേദികളും)</option>
                    @foreach($stages as $stg)
                        <option value="{{ $stg->id }}" {{ (string) $selectedStageId === (string) $stg->id ? 'selected' : '' }}>{{ $stg->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Zone -->
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Filter by Zone / Category</label>
                <select name="zone" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($zones as $val => $label)
                        <option value="{{ $val }}" {{ $selectedZone == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Program -->
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Filter by Program</label>
                <select name="program" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Programs ({{ $allPrograms->count() }})</option>
                    @foreach($allPrograms as $prog)
                        <option value="{{ $prog->id }}" {{ $selectedProgramId == $prog->id ? 'selected' : '' }}>
                            [{{ $prog->code }}] {{ $prog->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Result Status -->
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Result Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Declared ({{ $results->count() }})</option>
                    <option value="evaluated" {{ request('status') === 'evaluated' ? 'selected' : '' }}>Valuation Completed / Evaluated (മൂല്യനിർണ്ണയം കഴിഞ്ഞവ)</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Verdict Ready / Submitted</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published Only</option>
                    <option value="announced" {{ request('status') === 'announced' ? 'selected' : '' }}>Announced Only</option>
                    <option value="send" {{ request('status') === 'send' ? 'selected' : '' }}>Sent to Announcer</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                @if($selectedZone || $selectedProgramId || request('status') || $selectedIsStage !== null || $selectedStageId)
                    <a href="{{ request()->routeIs('announcer.*') ? route('announcer.print.results') : route('admin.print.results') }}" class="w-full text-center px-3 py-2 text-xs font-mono text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition font-bold">
                        Reset Filters
                    </a>
                @endif
            </div>
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
            <img src="{{ asset('images/print-pdf-header.svg') }}" alt="Festival Header" class="w-1/2 max-w-[50%] h-auto max-h-12 sm:max-h-14 object-contain block mx-auto" style="max-height: 55px; width: 50%; max-width: 50%;">
        </div>

        <!-- Official Document Header -->
        <div class="flex items-center justify-between pb-6 border-b-2 border-slate-900 gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-black font-sora text-slate-900 uppercase tracking-tight">
                    @if((string) $selectedIsStage === '1' || $selectedIsStage === 'stage')
                        Official Stage Competition Results &amp; Merit List
                    @elseif((string) $selectedIsStage === '0' || $selectedIsStage === 'off_stage')
                        Official Off-Stage Results &amp; Merit List
                    @else
                        Official Festival Results &amp; Standings
                    @endif
                </h1>
                <p class="text-xs font-semibold text-slate-600">Adabic Inheritance • Samastha Centenary Edition</p>
                <p class="text-[11px] text-slate-500">Ihyaussunna Students Union, Markazu Saquafathi Sunniyya</p>
            </div>

            <div class="text-right font-mono text-[11px] text-slate-500 space-y-0.5">
                <div class="font-bold text-slate-900 text-xs">OFFICIAL REPORT</div>
                <div>Date: {{ now()->format('d M Y, h:i A') }}</div>
                <div>Generated: Fest Central Desk</div>
                @if((string) $selectedIsStage === '1' || $selectedIsStage === 'stage')
                    <div class="text-[#be1e2d] font-bold">SCOPE: STAGE PROGRAMS ONLY</div>
                @elseif((string) $selectedIsStage === '0' || $selectedIsStage === 'off_stage')
                    <div class="text-[#005c94] font-bold">SCOPE: OFF-STAGE PROGRAMS ONLY</div>
                @endif
                @if($selectedStageId)
                    @php $stgObj = $stages->firstWhere('id', $selectedStageId); @endphp
                    @if($stgObj)
                        <div class="text-emerald-700 font-bold">VENUE: {{ $stgObj->name }}</div>
                    @endif
                @endif
                @if($selectedZone)
                    <div class="text-[#be1e2d] font-bold">Zone: {{ $selectedZone }}</div>
                @endif
            </div>
        </div>

        <div class="text-center my-6">
            <h2 class="text-lg sm:text-xl font-bold uppercase tracking-wider font-sora text-slate-900">
                @if((string) $selectedIsStage === '1' || $selectedIsStage === 'stage')
                    Stage Competitions Merit &amp; Position List
                @elseif((string) $selectedIsStage === '0' || $selectedIsStage === 'off_stage')
                    Off-Stage Competitions Merit &amp; Position List
                @else
                    Official Festival Results &amp; Standings
                @endif
            </h2>
            <p class="text-xs font-mono text-slate-500 mt-1">Authorized Scorecard and Merit Verdicts (Code Letter, QF Number &amp; Winners)</p>
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

        @php
            $formatWinners = function($res) {
                $podium = $res->getPodiumWinnersWithTies();
                $formatted = [1 => [], 2 => [], 3 => []];

                foreach ([1, 2, 3] as $pos) {
                    $list = !empty($podium[$pos]) ? $podium[$pos] : [];
                    if (empty($list)) {
                        $entry = match($pos) {
                            1 => $res->firstEntry,
                            2 => $res->secondEntry,
                            3 => $res->thirdEntry,
                            default => null,
                        };
                        if ($entry) {
                            $grade = match($pos) {
                                1 => $res->first_grade,
                                2 => $res->second_grade,
                                3 => $res->third_grade,
                                default => null,
                            };
                            $list[] = [
                                'id' => $entry->id,
                                'entry' => $entry,
                                'name' => $entry->student?->name ?: ($entry->group?->name ?? '—'),
                                'chest' => $entry->chest_number ?: ($entry->student?->student_id ?? '—'),
                                'class' => $entry->student?->class_level ?? null,
                                'code_letter' => $entry->code_letter ? strtoupper(trim($entry->code_letter)) : null,
                                'team' => $entry->group?->name ?: ($entry->student?->group?->name ?? '—'),
                                'team_color' => $entry->group?->color_hex ?? ($entry->student?->group?->color_hex ?? '#be1e2d'),
                                'grade' => $grade,
                                'is_tied' => false,
                            ];
                        }
                    } else {
                        foreach ($list as &$w) {
                            if (empty($w['class']) && !empty($w['entry'])) {
                                $w['class'] = $w['entry']->student?->class_level ?? null;
                            }
                            if (empty($w['code_letter'])) {
                                $w['code_letter'] = !empty($w['entry']?->code_letter)
                                    ? strtoupper(trim($w['entry']->code_letter))
                                    : null;
                            }
                            if (empty($w['chest']) && !empty($w['entry'])) {
                                $w['chest'] = $w['entry']->chest_number ?: ($w['entry']->student?->student_id ?? '—');
                            }
                            if (empty($w['team']) && !empty($w['entry'])) {
                                $w['team'] = $w['entry']->group?->name ?: ($w['entry']->student?->group?->name ?? '—');
                            }
                        }
                        unset($w);
                    }
                    $formatted[$pos] = $list;
                }
                return $formatted;
            };
        @endphp

        <!-- Section 2: Program-Wise Results & Winners -->
        <div x-show="showResults" class="mb-8">
            <div class="flex items-center justify-between mb-3 border-b border-slate-200 pb-1.5">
                <h3 class="text-sm font-mono uppercase font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#005c94]"></span>
                    <span>2. Declared Competition Results</span>
                    <span class="text-xs font-mono font-normal text-slate-500">
                        (Showing in <strong x-text="viewMode === 'table' ? 'Winners Table View' : 'Cards View'"></strong>)
                    </span>
                </h3>
                <span class="text-xs font-mono text-slate-500">{{ $results->count() }} Competitions</span>
            </div>

            @if($results->isEmpty())
                <div class="p-6 text-center border border-dashed border-slate-300 rounded-xl text-xs text-slate-500 font-mono">
                    No published results match the selected filter criteria.
                </div>
            @else
                <!-- View A: Detailed Podium Cards -->
                <div x-show="viewMode === 'cards'" class="space-y-4">
                    @foreach($results as $res)
                        @php
                            $winnersByPos = $formatWinners($res);
                            $prog = $res->program;
                        @endphp
                        <div class="border border-slate-300 rounded-xl p-3.5 bg-white avoid-break shadow-2xs">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 pb-2 mb-2 border-b border-slate-200 text-xs">
                                <div>
                                    <span class="font-mono font-black text-white bg-slate-900 px-2 py-0.5 rounded text-[11px] mr-1 shadow-2xs">
                                        Result #{{ sprintf('%02d', $res->result_number) }}
                                    </span>
                                    <span class="font-mono font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                        {{ $prog?->code }}
                                    </span>
                                    <span class="font-bold text-slate-900 text-sm ml-1.5">{{ $prog?->name }}</span>
                                    @if($prog?->malayalam_name)
                                        <span class="text-slate-600 ml-1">({{ $prog->malayalam_name }})</span>
                                    @endif
                                </div>
                                <div class="font-mono text-[11px] text-slate-500 flex items-center gap-2 flex-wrap">
                                    <a href="{{ request()->routeIs('announcer.*') ? route('announcer.print.evaluation', $res->program_id) : route('admin.print.evaluation-sheet', $res->program_id) }}" target="_blank" class="no-print px-2 py-0.5 rounded bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 font-bold transition flex items-center gap-1" title="Print Multi-Judge Valuation Scorecard">
                                        <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        <span>Valuation Sheet ↗</span>
                                    </a>
                                    <span class="px-2 py-0.5 rounded bg-slate-100 font-bold text-slate-800">{{ $prog?->zone?->name ?? $prog?->eligibility ?? 'General' }}</span>
                                    <span class="px-2 py-0.5 rounded {{ $prog?->is_stage ? 'bg-amber-100 text-amber-900 font-bold' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $prog?->is_stage ? ($prog->stage?->name ?? 'Stage') : 'Off-Stage' }}
                                    </span>
                                    <span class="uppercase">{{ $prog?->type }}</span>
                                    <span>• {{ $res->published_at ? $res->published_at->format('d/m/Y') : ($res->created_at ? $res->created_at->format('d/m/Y') : '') }}</span>
                                </div>
                            </div>

                            <!-- 1st, 2nd, 3rd Winner Podium Row -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                                @foreach([
                                    1 => ['bg' => 'bg-amber-50/70', 'border' => 'border-amber-200', 'badgeBg' => 'bg-[#f3bd2e]', 'badgeText' => 'text-slate-950', 'label' => '1st'],
                                    2 => ['bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'badgeBg' => 'bg-slate-300', 'badgeText' => 'text-slate-900', 'label' => '2nd'],
                                    3 => ['bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'badgeBg' => 'bg-amber-700/20', 'badgeText' => 'text-amber-900', 'label' => '3rd']
                                ] as $pos => $cfg)
                                    @php $wList = $winnersByPos[$pos]; @endphp
                                    <div class="p-2.5 rounded-lg {{ $cfg['bg'] }} border {{ $cfg['border'] }} flex flex-col gap-1.5">
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="px-1.5 py-0.5 rounded {{ $cfg['badgeBg'] }} {{ $cfg['badgeText'] }} font-bold font-mono text-[10px] shrink-0">
                                                {{ $cfg['label'] }}
                                            </span>
                                            @if(count($wList) > 1)
                                                <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-900 border border-amber-200 font-mono font-bold text-[9px] uppercase">
                                                    Shared (Tied)
                                                </span>
                                            @endif
                                        </div>

                                        @forelse($wList as $w)
                                            <div class="overflow-hidden min-w-0 border-t border-slate-200/60 pt-1.5 first:border-t-0 first:pt-0">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="font-bold text-slate-900 text-xs truncate">
                                                        {{ $w['name'] }}
                                                    </span>
                                                    @if(!empty($w['code_letter']))
                                                        <span class="px-1.5 py-0.2 rounded bg-slate-900 text-white font-mono font-black text-[10px] tracking-wider shadow-2xs" title="Official Evaluation Code Letter">
                                                            Code {{ $w['code_letter'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-600 font-mono truncate mt-0.5 flex items-center gap-1.5 flex-wrap">
                                                    <span class="font-bold text-slate-800">#{{ $w['chest'] }}</span>
                                                    @if(!empty($w['class']))
                                                        <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-800 border border-slate-200 font-bold text-[10px]">
                                                            Class {{ $w['class'] }}
                                                        </span>
                                                    @endif
                                                    <span>• {{ $w['team'] }}</span>
                                                    @if(!empty($w['grade']) && $w['grade'] !== '-')
                                                        <span class="px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold text-[10px]">
                                                            Grade {{ $w['grade'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-[11px] text-slate-400 font-mono italic">No winner declared</div>
                                        @endforelse
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- View B: Winners Table (PDF / Printout List) -->
                <div x-show="viewMode === 'table'" class="overflow-x-auto rounded-xl border border-slate-300 bg-white shadow-xs">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-900 text-white font-mono uppercase text-[11px] border-b border-slate-800">
                                <th class="py-2.5 px-3 w-10 text-center">#</th>
                                <th class="py-2.5 px-3 min-w-[200px]">Program Name</th>
                                <th class="py-2.5 px-2.5 w-24">Zone</th>
                                <th class="py-2.5 px-2.5 w-28">Stage / Venue</th>
                                <th class="py-2.5 px-2 w-14 text-center">Rank</th>
                                <th class="py-2.5 px-2 w-16 text-center bg-slate-800 text-amber-300 font-black">Code</th>
                                <th class="py-2.5 px-3 w-24">QF Number</th>
                                <th class="py-2.5 px-3 min-w-[170px]">Student / Contestant Name</th>
                                <th class="py-2.5 px-2 w-16 text-center">Class</th>
                                <th class="py-2.5 px-3 min-w-[120px]">Group / Team</th>
                                <th class="py-2.5 px-2 w-16 text-center">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 font-sora">
                            @foreach($results as $resIdx => $res)
                                @php
                                    $winnersByPos = $formatWinners($res);
                                    $prog = $res->program;
                                    $allWinnersForProg = [];
                                    foreach ([1, 2, 3] as $pos) {
                                        foreach ($winnersByPos[$pos] as $w) {
                                            $allWinnersForProg[] = array_merge($w, ['pos' => $pos]);
                                        }
                                    }
                                    $totalProgWinners = count($allWinnersForProg);
                                @endphp

                                @if($totalProgWinners === 0)
                                    <tr class="avoid-break hover:bg-slate-50/70 border-b border-slate-200">
                                        <td class="py-2 px-3 text-center font-mono font-bold text-slate-700 bg-slate-50/60">
                                            {{ sprintf('%02d', $res->result_number) }}
                                        </td>
                                        <td class="py-2 px-3">
                                            <div class="font-bold text-slate-900">
                                                <span class="font-mono text-slate-500 mr-1">[{{ $prog?->code }}]</span>
                                                {{ $prog?->name }}
                                            </div>
                                            @if($prog?->malayalam_name)
                                                <div class="text-[11px] text-slate-500">{{ $prog->malayalam_name }}</div>
                                            @endif
                                        </td>
                                        <td class="py-2 px-2.5 font-mono text-slate-700">
                                            {{ $prog?->zone?->name ?? $prog?->eligibility ?? 'General' }}
                                        </td>
                                        <td class="py-2 px-2.5 font-mono text-[11px] text-slate-600">
                                            {{ $prog?->is_stage ? ($prog->stage?->name ?? 'Stage') : 'Off-Stage' }}
                                        </td>
                                        <td colspan="7" class="py-2 px-3 text-center text-slate-400 font-mono italic">
                                            No podium winners declared
                                        </td>
                                    </tr>
                                @else
                                    @foreach($allWinnersForProg as $wIdx => $w)
                                        @php
                                            $isFirstRow = ($wIdx === 0);
                                            $posBadge = match($w['pos']) {
                                                1 => 'bg-[#f3bd2e] text-slate-950',
                                                2 => 'bg-slate-300 text-slate-900',
                                                3 => 'bg-amber-700/20 text-amber-900',
                                                default => 'bg-slate-200 text-slate-800',
                                            };
                                            $posLabel = match($w['pos']) {
                                                1 => '1st',
                                                2 => '2nd',
                                                3 => '3rd',
                                                default => $w['pos'],
                                            };
                                        @endphp
                                        <tr class="avoid-break hover:bg-slate-50/70 {{ $wIdx === $totalProgWinners - 1 ? 'border-b-2 border-slate-300' : 'border-b border-slate-100' }}">
                                            @if($isFirstRow)
                                                <td rowspan="{{ $totalProgWinners }}" class="py-2 px-3 text-center font-mono font-black text-slate-900 bg-slate-50/60 align-top border-r border-slate-200">
                                                    {{ sprintf('%02d', $res->result_number) }}
                                                </td>
                                                <td rowspan="{{ $totalProgWinners }}" class="py-2 px-3 align-top border-r border-slate-200">
                                                    <div class="font-bold text-slate-900">
                                                        <span class="font-mono text-slate-600 mr-1 text-[11px] font-bold">[{{ $prog?->code }}]</span>
                                                        {{ $prog?->name }}
                                                    </div>
                                                    @if($prog?->malayalam_name)
                                                        <div class="text-[11px] text-slate-500 font-sans mt-0.5">{{ $prog->malayalam_name }}</div>
                                                    @endif
                                                    <div class="text-[10px] font-mono text-slate-400 uppercase mt-0.5">
                                                        {{ $prog?->type ?? 'Individual' }}
                                                    </div>
                                                </td>
                                                <td rowspan="{{ $totalProgWinners }}" class="py-2 px-2.5 font-mono text-slate-800 align-top border-r border-slate-200 font-bold">
                                                    {{ $prog?->zone?->name ?? $prog?->eligibility ?? 'General' }}
                                                </td>
                                                <td rowspan="{{ $totalProgWinners }}" class="py-2 px-2.5 font-mono text-[11px] text-slate-700 align-top border-r border-slate-200">
                                                    <span class="px-1.5 py-0.5 rounded {{ $prog?->is_stage ? 'bg-amber-100 text-amber-900 font-bold' : 'bg-slate-100 text-slate-600' }}">
                                                        {{ $prog?->is_stage ? ($prog->stage?->name ?? 'Stage') : 'Off-Stage' }}
                                                    </span>
                                                </td>
                                            @endif

                                            <!-- Winner Rank -->
                                            <td class="py-2 px-2 text-center align-middle font-mono font-bold">
                                                <span class="px-1.5 py-0.5 rounded {{ $posBadge }} text-[10px] inline-block font-black">
                                                    {{ $posLabel }}
                                                </span>
                                            </td>

                                            <!-- Code Letter -->
                                            <td class="py-2 px-2 text-center align-middle font-mono bg-slate-50/80 border-x border-slate-200 font-black text-xs text-slate-950">
                                                @if(!empty($w['code_letter']))
                                                    <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-black tracking-wider text-[11px] shadow-2xs">
                                                        {{ $w['code_letter'] }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-300 font-normal">—</span>
                                                @endif
                                            </td>

                                            <!-- QF Number -->
                                            <td class="py-2 px-3 align-middle font-mono font-bold text-slate-900 border-r border-slate-200">
                                                #{{ $w['chest'] }}
                                            </td>

                                            <!-- Contestant Name -->
                                            <td class="py-2 px-3 align-middle">
                                                <div class="font-bold text-slate-900 text-xs">
                                                    {{ $w['name'] }}
                                                </div>
                                                @if(!empty($w['is_tied']))
                                                    <span class="text-[9px] font-mono uppercase text-amber-700 font-bold">Shared / Tied</span>
                                                @endif
                                            </td>

                                            <!-- Class -->
                                            <td class="py-2 px-2 text-center align-middle font-mono font-bold text-slate-800 text-[11px]">
                                                @if(!empty($w['class']))
                                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-800 border border-slate-200 shadow-2xs">
                                                        {{ $w['class'] }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-300 font-normal">—</span>
                                                @endif
                                            </td>

                                            <!-- Group / Team -->
                                            <td class="py-2 px-3 align-middle font-mono text-slate-700 text-xs">
                                                <span class="inline-flex items-center gap-1.5">
                                                    <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $w['team_color'] ?? '#be1e2d' }}"></span>
                                                    <span>{{ $w['team'] }}</span>
                                                </span>
                                            </td>

                                            <!-- Grade -->
                                            <td class="py-2 px-2 text-center align-middle font-mono font-bold text-xs">
                                                @if(!empty($w['grade']) && $w['grade'] !== '-')
                                                    <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 font-black">
                                                        {{ $w['grade'] }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-300 font-normal">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
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
            <!-- Official Print Footer -->
            <div class="mt-8 pt-3 border-t border-slate-200 flex items-center justify-between text-[10px] font-mono text-slate-500">
                <div class="inline-flex items-center gap-1.5 whitespace-nowrap leading-none shrink-0">
                    <img src="{{ asset('images/quaf-black-title.svg') }}" alt="QUAF" class="w-auto object-contain inline-block align-middle" style="height: 11px; max-height: 12px;">
                    <span class="font-normal text-slate-600 whitespace-nowrap leading-none font-sans">Markaz Cultural Festival</span>
                </div>
                <span class="text-[10px] text-slate-400 whitespace-nowrap shrink-0 leading-none">Printed: {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>

    </div>

</body>
</html>
