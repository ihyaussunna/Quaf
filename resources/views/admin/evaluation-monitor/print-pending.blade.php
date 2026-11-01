<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Evaluation Incomplete Competitions List — QUAF 2026</title>
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
                padding: 8mm !important;
                margin: 0 !important;
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
<body class="bg-slate-100 text-slate-900 font-sora antialiased min-h-screen py-6 px-3 sm:px-6">

    <!-- Top Action Control Bar (Hidden on Print) -->
    <div class="no-print max-w-5xl mx-auto mb-6 bg-white border border-slate-300 rounded-2xl p-4 sm:p-5 shadow-lg space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-amber-600 text-white uppercase">EVALUATION MONITOR</span>
                    <h1 class="text-lg font-bold text-slate-900">Pending / Incomplete Evaluations Report</h1>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-sora">
                    മൂല്യനിർണ്ണയം പൂർത്തിയാകാത്ത മത്സരങ്ങളുടെ ഒഫീഷ്യൽ ലിസ്റ്റ് (Print &amp; PDF Export)
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.evaluation-monitor.index') }}" class="px-4 py-2 rounded-xl text-xs font-mono font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition-colors">
                    &larr; Back to Monitor
                </a>
                <button onclick="window.print()" class="px-5 py-2.5 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#be1e2d] hover:bg-[#a01624] text-white flex items-center gap-2 shadow-md transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print List / PDF</span>
                </button>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.evaluation-monitor.print-pending') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-3 border-t border-slate-200">
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Zone Filter</label>
                <select name="zone" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($zones as $zKey => $zVal)
                        @php
                            $zValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                            $zName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                        @endphp
                        <option value="{{ $zValue }}" {{ ($zone ?? '') == $zValue ? 'selected' : '' }}>{{ $zName }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Stage Filter</label>
                <select name="stage_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Stages</option>
                    @foreach($stages as $s)
                        <option value="{{ $s->id }}" {{ (string)($stageId ?? '') === (string)$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex items-end gap-2">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search program code or name..."
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold font-sora">
                    Filter
                </button>
                @if($zone || $stageId || $search)
                    <a href="{{ route('admin.evaluation-monitor.print-pending') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-mono font-bold">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Official Printable Document Container -->
    <div class="print-container max-w-5xl mx-auto bg-white border border-slate-300 rounded-3xl p-8 sm:p-10 shadow-xl text-slate-900 space-y-6">
        
        <!-- Official Header Banner (From Program Mark Sheet/PDF) -->
        <div class="w-full flex justify-center mb-2">
            <img src="{{ asset('images/offstage-pdf-header.png') }}" alt="Ādabīc Inheritance - QUAF" class="w-full max-h-22 sm:max-h-26 object-contain block mx-auto">
        </div>

        <!-- Document Header Strip -->
        <div class="border-b-2 border-slate-900 pb-4 flex items-center justify-between gap-4">
            <div>
                <span class="text-xs font-mono font-black tracking-widest text-[#be1e2d] uppercase block">
                    QUAF SEASON 09 &bull; CENTRAL FESTIVAL COMMITTEE
                </span>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight font-sora uppercase">
                    EVALUATION PENDING COMPETITIONS LIST
                </h1>
                <span class="text-xs text-slate-600 font-mono">
                    മൂല്യനിർണ്ണയം പൂർത്തിയാകാത്ത മത്സരങ്ങളുടെ ഒഫീഷ്യൽ റിപ്പോർട്ട്
                </span>
            </div>

            <div class="text-right shrink-0 font-mono text-xs">
                <div class="inline-block px-3.5 py-1.5 rounded-xl bg-amber-600 text-white font-black text-xs uppercase tracking-wider">
                    PENDING: {{ $programs->count() }}
                </div>
                <div class="text-[11px] text-slate-500 mt-1">
                    Date: {{ now()->format('d M Y, h:i A') }}
                </div>
                <div class="text-[10px] text-slate-400">
                    Desk: Central Evaluation Monitor
                </div>
            </div>
        </div>

        <!-- Filter & Scope Meta Banner -->
        <div class="p-3.5 rounded-2xl bg-amber-50/60 border border-amber-200/90 flex flex-wrap items-center justify-between gap-3 text-xs font-mono">
            <div class="flex items-center gap-3 flex-wrap">
                <span class="font-bold text-amber-950">Scope / Filters:</span>
                <span class="px-2 py-0.5 rounded bg-white text-slate-800 border border-amber-200">
                    Zone: <strong>{{ $zone ?: 'All Zones' }}</strong>
                </span>
                @if($stageId)
                    @php $stgObj = $stages->firstWhere('id', $stageId); @endphp
                    <span class="px-2 py-0.5 rounded bg-white text-slate-800 border border-amber-200">
                        Stage: <strong>{{ $stgObj?->name ?? 'Stage #' . $stageId }}</strong>
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded bg-white text-slate-800 border border-amber-200">
                        Stage: <strong>All Stages</strong>
                    </span>
                @endif
                @if($search)
                    <span class="px-2 py-0.5 rounded bg-white text-slate-800 border border-amber-200">
                        Search: <strong>{{ $search }}</strong>
                    </span>
                @endif
            </div>
            <div class="text-amber-900 font-bold">
                Total Unfinished: {{ $programs->count() }} Competitions
            </div>
        </div>

        <!-- Program Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sora border border-slate-300">
                <thead class="bg-slate-100 font-mono font-bold text-slate-700 uppercase border-b-2 border-slate-300 text-[11px]">
                    <tr>
                        <th class="py-2.5 px-3 w-10 text-center">#</th>
                        <th class="py-2.5 px-3 w-20">Code</th>
                        <th class="py-2.5 px-3">Competition / Program Name</th>
                        <th class="py-2.5 px-3">Stage / Venue</th>
                        <th class="py-2.5 px-3 text-center w-16">Total</th>
                        <th class="py-2.5 px-3 text-center w-16">Present</th>
                        <th class="py-2.5 px-3 text-center w-20 text-purple-800 bg-purple-50">Evaluated</th>
                        <th class="py-2.5 px-3 text-center w-20 text-amber-800 bg-amber-50">Pending</th>
                        <th class="py-2.5 px-3 text-center w-28">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs">
                    @forelse($programs as $idx => $prog)
                        @php
                            $total = $prog->total_entries_count;
                            $present = $prog->present_entries_count;
                            $evaluated = $prog->evaluated_entries_count;
                            $pending = max(0, $present - $evaluated);
                        @endphp
                        <tr class="{{ $idx % 2 === 1 ? 'bg-slate-50/60' : 'bg-white' }} hover:bg-amber-50/30 transition-colors">
                            <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-500">
                                {{ $idx + 1 }}
                            </td>
                            <td class="py-2.5 px-3 font-mono font-bold text-[#be1e2d]">
                                {{ $prog->code }}
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-slate-900">{{ $prog->name }}</div>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                    {{ $prog->category?->name ?? 'General' }} &bull; {{ $prog->zone?->name ?? $prog->eligibility ?? 'All Zones' }} &bull; {{ ucfirst($prog->type) }}
                                </div>
                            </td>
                            <td class="py-2.5 px-3 font-medium text-slate-700 font-mono text-[11px]">
                                {{ $prog->stage?->name ?? 'Unassigned' }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-800">
                                {{ $total }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono font-bold text-emerald-700">
                                {{ $present }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-700 bg-purple-50/40">
                                {{ $evaluated }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono font-black text-amber-700 bg-amber-50/40">
                                {{ $pending }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono text-[10px]">
                                @if($present > 0 && $evaluated > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold uppercase bg-blue-100 text-blue-800">
                                        SCORING LIVE
                                    </span>
                                @elseif($present > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold uppercase bg-amber-100 text-amber-900">
                                        WAITING JURY
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold uppercase bg-slate-100 text-slate-700">
                                        WAITING CALL
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-400 font-mono text-xs">
                                All competitions matching your criteria have completed evaluation! (കഴിയാത്ത മത്സരങ്ങൾ ഒന്നുമില്ല)
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Official Signatures -->
        <div class="avoid-break pt-8 grid grid-cols-3 gap-6 text-center font-mono text-xs text-slate-700">
            <div class="space-y-6">
                <div class="h-10"></div>
                <div class="border-t border-slate-400 pt-1.5">
                    <span class="font-bold text-xs text-slate-900 block font-sora">Stage Adjudication Desk</span>
                    <span class="text-[10px] text-slate-500 block uppercase">Jury Coordinator</span>
                </div>
            </div>

            <div class="space-y-6">
                <div class="h-10"></div>
                <div class="border-t border-slate-400 pt-1.5">
                    <span class="font-bold text-xs text-slate-900 block font-sora">Evaluation Monitor</span>
                    <span class="text-[10px] text-slate-500 block uppercase">Controller of Examinations</span>
                </div>
            </div>

            <div class="space-y-6">
                <div class="h-10"></div>
                <div class="border-t border-slate-400 pt-1.5">
                    <span class="font-bold text-xs text-slate-900 block font-sora">General Convener</span>
                    <span class="text-[10px] text-slate-500 block uppercase">Central Festival Directorate</span>
                </div>
            </div>
        </div>

        <!-- Official Print Footer (From Program Mark Sheet/PDF) -->
        <div class="avoid-break mt-6 pt-3 border-t border-slate-200 text-[10px] text-slate-500 flex justify-between items-center gap-4">
            <div class="inline-flex items-center gap-1.5 whitespace-nowrap leading-none shrink-0">
                <img src="{{ asset('images/quaf-black-title.svg') }}" alt="QUAF" class="w-auto object-contain inline-block align-middle" style="height: 10px; max-height: 11px;">
                <span class="font-normal text-slate-600 whitespace-nowrap leading-none font-sans">Markaz Cultural Festival</span>
            </div>
            <span class="text-[10px] text-slate-400 whitespace-nowrap shrink-0 leading-none">Printed: {{ now()->format('d M Y, h:i A') }}</span>
        </div>

    </div>

    <!-- Footer Note -->
    <div class="no-print max-w-5xl mx-auto mt-6 text-center text-xs font-mono text-slate-400">
        QUAF 2026 Season 09 &bull; Inter-House Festival Management System
    </div>

</body>
</html>
