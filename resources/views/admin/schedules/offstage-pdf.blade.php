<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Schedule - QUAF</title>
    @vite(['resources/css/app.css'])
    <style>
        @font-face {
            font-family: 'Rockwell Std';
            src: url('/fonts/Rockwell-Light.woff2') format('woff2'), url('/fonts/Rockwell-Light.otf') format('opentype'), local('Rockwell Light');
            font-weight: 300;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rockwell Std';
            src: url('/fonts/Rockwell-Regular.woff2') format('woff2'), url('/fonts/ROCK.TTF') format('truetype'), url('/fonts/Rockwell-Regular.otf') format('opentype'), local('Rockwell Std'), local('Rockwell');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rockwell Std';
            src: url('/fonts/Rockwell-Bold.woff2') format('woff2'), url('/fonts/Rockwell-Bold.otf') format('opentype'), local('Rockwell Bold');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rockwell Std';
            src: url('/fonts/Rockwell-Extra-Bold.woff2') format('woff2'), url('/fonts/Rockwell-Extra-Bold.otf') format('opentype'), local('Rockwell Extra Bold');
            font-weight: 800;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: 'Rockwell';
            src: url('/fonts/Rockwell-Regular.woff2') format('woff2'), url('/fonts/ROCK.TTF') format('truetype'), local('Rockwell Std'), local('Rockwell');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rockwell';
            src: url('/fonts/Rockwell-Bold.woff2') format('woff2'), url('/fonts/Rockwell-Bold.otf') format('opentype'), local('Rockwell Bold');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rockwell';
            src: url('/fonts/Rockwell-Extra-Bold.woff2') format('woff2'), url('/fonts/Rockwell-Extra-Bold.otf') format('opentype'), local('Rockwell Extra Bold');
            font-weight: 800;
            font-style: normal;
            font-display: swap;
        }

        body, table, th, td, h1, h2, h3, h4, div, span, p, a, button {
            font-family: 'Rockwell Std', 'Rockwell', Georgia, serif !important;
        }

        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
        }

        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .page-container {
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .day-section {
                page-break-after: always;
            }
            .day-section:last-child {
                page-break-after: auto;
            }
            table {
                page-break-inside: auto;
                width: 100% !important;
            }
            thead {
                display: table-header-group;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen py-4 sm:py-8 px-2 sm:px-4">

    <!-- Action Toolbar (Hidden during print) -->
    <div class="no-print max-w-5xl mx-auto mb-6 p-4 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route(($routePrefix ?? 'admin.schedules.') . 'offstage', ['date' => $date ?? '2026-10-06']) }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back to Scheduler</span>
            </a>
            <div>
                <h2 class="font-bold text-sm text-slate-800">Program Schedule Print Preview</h2>
                <p class="text-xs text-slate-500 font-normal">Official Timetable Matrix formatted in Rockwell Std design for clean, ink-friendly A4 PDF export</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-[#BE1E2D] hover:bg-[#a01624] text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Download / Print PDF</span>
            </button>
        </div>
    </div>

    <!-- Main Printable Sheet -->
    <div class="page-container max-w-5xl mx-auto bg-white p-6 sm:p-8 shadow-lg rounded-2xl border border-slate-200 print:border-none print:shadow-none print:rounded-none">

        @if($mode === 'single')
            <div class="day-section">
                <!-- Header Banner Image (Official Ādabīc Inheritance & QUAF Header) -->
                <div class="w-full flex justify-center mb-3">
                    <img src="{{ asset('images/offstage-pdf-header.png') }}" alt="Ādabīc Inheritance - QUAF" class="w-full max-h-24 sm:max-h-28 object-contain">
                </div>

                <!-- Creative Decorative Motif Accent Bar (Light Festival Colors) -->
                <div class="relative flex items-center justify-center my-3">
                    <div class="w-full border-t border-[#BE1E2D]/40"></div>
                    <div class="absolute bg-white px-5 flex items-center gap-2">
                        <span class="w-2 h-2 rotate-45 bg-[#F2BC2E]"></span>
                        <span class="text-[11px] font-black uppercase tracking-widest text-[#BE1E2D]">QUAF '26 FESTIVAL</span>
                        <span class="w-2 h-2 rotate-45 bg-[#F2BC2E]"></span>
                    </div>
                </div>

                <!-- Creative Title in Bold Rockwell Std -->
                <h1 class="text-center text-3xl sm:text-4xl font-black tracking-tight text-[#BE1E2D] uppercase mt-2 mb-1">
                    Program Schedule
                </h1>

                <!-- Creative Date & Milestone Badge (Clean Light Theme) -->
                <div class="flex justify-center mt-2 mb-5">
                    <div class="inline-flex items-center gap-2 px-6 py-1.5 rounded-full bg-gradient-to-r from-[#FFF5F5] via-[#FFFBF2] to-[#FFF5F5] border border-[#FECACA] text-[#991B1B] text-xs sm:text-sm font-extrabold shadow-2xs">
                        <span class="w-2 h-2 rotate-45 bg-[#F2BC2E]"></span>
                        <span>{{ $formattedDate }}</span>
                        <span class="w-2 h-2 rotate-45 bg-[#F2BC2E]"></span>
                    </div>
                </div>

                <div class="border-b border-slate-200 mb-6"></div>

                @if($stages->isNotEmpty() && $timeSlots->isNotEmpty())
                    <!-- Timetable Matrix Table (Standard & Best Quality with Rich Colors) -->
                    <div class="rounded-2xl overflow-hidden border border-rose-200/90 shadow-sm bg-white">
                        <table class="w-full border-collapse text-left table-fixed">
                            <thead>
                                <tr class="bg-gradient-to-r from-[#FFF5F5] via-[#FFF9F2] to-[#FFF5F5] border-b-2 border-[#BE1E2D]">
                                    <th class="w-[15%] sm:w-[14%] px-3 py-3 text-center border-r border-slate-200/90">
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-slate-900 text-white text-[10px] font-black uppercase tracking-wider shadow-2xs">
                                            <svg class="w-3 h-3 text-[#F2BC2E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Time</span>
                                        </div>
                                    </th>
                                    @php
                                        $stageColWidth = round(85 / max(1, $stages->count()), 2);
                                    @endphp
                                    @foreach($stages as $stage)
                                        <th class="px-3 py-3 text-center border-r border-slate-200/90 last:border-r-0" style="width: {{ $stageColWidth }}%;">
                                            <div class="inline-flex items-center gap-1 px-3 py-1 rounded-md bg-[#BE1E2D] text-white text-[10px] font-black uppercase tracking-wider shadow-2xs mb-0.5">
                                                <span>{{ $stage->name }}</span>
                                            </div>
                                            @if($stage->location && !str_contains(strtolower($stage->name), strtolower($stage->location)))
                                                <div class="text-[10px] text-slate-600 font-bold mt-0.5 normal-case tracking-normal">
                                                    <span class="text-slate-400 font-semibold">Venue:</span> {{ $stage->location }}
                                                </div>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timeSlots as $idx => $timeSlot)
                                    @php
                                        $rowBg = $idx % 2 === 0 ? 'bg-white' : 'bg-[#FFFDF9]';
                                    @endphp
                                    <tr class="{{ $rowBg }} border-b border-rose-200/60 last:border-b-0 hover:bg-rose-50/40 transition-colors">
                                        <!-- Time Column with Colored Graphic Tag -->
                                        <td class="px-3 py-3.5 text-center font-bold text-xs sm:text-sm text-black border-r border-slate-200/80 align-middle whitespace-nowrap">
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gradient-to-r from-rose-50 via-white to-amber-50/50 border border-rose-200/80 shadow-2xs">
                                                <span class="w-2 h-2 rounded-full bg-[#BE1E2D] shadow-xs"></span>
                                                <span class="font-black text-black tracking-tight">{{ $timeSlot }}</span>
                                            </div>
                                        </td>

                                        <!-- Stage Columns (Direct clean text with color-coded zone badge) -->
                                        @foreach($stages as $stage)
                                            <td class="px-3 py-3.5 text-center border-r border-slate-200/80 last:border-r-0 align-middle">
                                                @if(!empty($matrix[$timeSlot][$stage->id]))
                                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                                        @foreach($matrix[$timeSlot][$stage->id] as $sch)
                                                            <div class="w-full text-center py-0.5">
                                                                <div class="font-extrabold text-xs sm:text-sm text-slate-900 leading-snug tracking-tight">
                                                                    {{ $sch->program?->name }}
                                                                </div>
                                                                @if($sch->program?->zone)
                                                                    @php
                                                                        $zName = $sch->program->zone->name;
                                                                        $zStyle = match (true) {
                                                                            str_contains($zName, 'A') => 'bg-emerald-50 text-emerald-800 border-emerald-300',
                                                                            str_contains($zName, 'B') => 'bg-blue-50 text-blue-800 border-blue-300',
                                                                            str_contains($zName, 'C') => 'bg-purple-50 text-purple-800 border-purple-300',
                                                                            str_contains($zName, 'Mix') => 'bg-amber-50 text-amber-900 border-amber-300',
                                                                            default => 'bg-rose-50 text-rose-900 border-rose-300',
                                                                        };
                                                                        $dotColor = match (true) {
                                                                            str_contains($zName, 'A') => 'bg-emerald-500',
                                                                            str_contains($zName, 'B') => 'bg-blue-500',
                                                                            str_contains($zName, 'C') => 'bg-purple-500',
                                                                            str_contains($zName, 'Mix') => 'bg-amber-500',
                                                                            default => 'bg-rose-500',
                                                                        };
                                                                    @endphp
                                                                    <div class="mt-1">
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-2xs {{ $zStyle }}">
                                                                            <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                                                                            <span>{{ $zName }}</span>
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-slate-300 font-bold text-base select-none">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-12 text-center text-slate-500 font-bold bg-[#FAF8F5] rounded-xl border border-slate-200">
                        No programs scheduled for this date.
                    </div>
                @endif
            </div>
        @else
            <!-- All Dates Multi-Page Mode -->
            @foreach($datesData as $dateKey => $data)
                <div class="day-section {{ !$loop->last ? 'mb-12' : '' }}">
                    <!-- Header Banner Image -->
                    <div class="w-full flex justify-center mb-3">
                        <img src="{{ asset('images/offstage-pdf-header.png') }}" alt="Ādabīc Inheritance - QUAF" class="w-full max-h-24 sm:max-h-28 object-contain">
                    </div>

                    <!-- Creative Decorative Motif Accent Bar (Light Festival Colors) -->
                    <div class="relative flex items-center justify-center my-3">
                        <div class="w-full border-t border-[#BE1E2D]/40"></div>
                        <div class="absolute bg-white px-5 flex items-center gap-2">
                            <span class="w-2 h-2 rotate-45 bg-[#F2BC2E]"></span>
                            <span class="text-[11px] font-black uppercase tracking-widest text-[#BE1E2D]">QUAF '26 FESTIVAL</span>
                            <span class="w-2 h-2 rotate-45 bg-[#F2BC2E]"></span>
                        </div>
                    </div>

                    <!-- Creative Title in Bold Rockwell Std -->
                    <h1 class="text-center text-3xl sm:text-4xl font-black tracking-tight text-[#BE1E2D] uppercase mt-2 mb-1">
                        Program Schedule
                    </h1>

                    <!-- Creative Date & Milestone Badge -->
                    <div class="flex justify-center mt-2 mb-5">
                        <div class="inline-flex items-center gap-2 px-6 py-1.5 rounded-full bg-gradient-to-r from-[#FFF5F5] via-[#FFFBF2] to-[#FFF5F5] border border-[#FECACA] text-[#991B1B] text-xs sm:text-sm font-extrabold shadow-2xs">
                            <span class="w-2 h-2 rotate-45 bg-[#F2BC2E]"></span>
                            <span>{{ $data['formattedDate'] }}</span>
                            <span class="w-2 h-2 rotate-45 bg-[#F2BC2E]"></span>
                        </div>
                    </div>

                    <div class="border-b border-slate-200 mb-6"></div>

                    @if($data['stages']->isNotEmpty() && $data['timeSlots']->isNotEmpty())
                        <!-- Timetable Matrix Table (Standard & Best Quality with Rich Colors) -->
                        <div class="rounded-2xl overflow-hidden border border-rose-200/90 shadow-sm bg-white">
                            <table class="w-full border-collapse text-left table-fixed">
                                <thead>
                                    <tr class="bg-gradient-to-r from-[#FFF5F5] via-[#FFF9F2] to-[#FFF5F5] border-b-2 border-[#BE1E2D]">
                                        <th class="w-[15%] sm:w-[14%] px-3 py-3 text-center border-r border-slate-200/90">
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-slate-900 text-white text-[10px] font-black uppercase tracking-wider shadow-2xs">
                                                <svg class="w-3 h-3 text-[#F2BC2E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Time</span>
                                            </div>
                                        </th>
                                        @php
                                            $stageColWidth = round(85 / max(1, $data['stages']->count()), 2);
                                        @endphp
                                        @foreach($data['stages'] as $stage)
                                            <th class="px-3 py-3 text-center border-r border-slate-200/90 last:border-r-0" style="width: {{ $stageColWidth }}%;">
                                                <div class="inline-flex items-center gap-1 px-3 py-1 rounded-md bg-[#BE1E2D] text-white text-[10px] font-black uppercase tracking-wider shadow-2xs mb-0.5">
                                                    <span>{{ $stage->name }}</span>
                                                </div>
                                                @if($stage->location && !str_contains(strtolower($stage->name), strtolower($stage->location)))
                                                    <div class="text-[10px] text-slate-600 font-bold mt-0.5 normal-case tracking-normal">
                                                        <span class="text-slate-400 font-semibold">Venue:</span> {{ $stage->location }}
                                                    </div>
                                                @endif
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['timeSlots'] as $idx => $timeSlot)
                                        @php
                                            $rowBg = $idx % 2 === 0 ? 'bg-white' : 'bg-[#FFFDF9]';
                                        @endphp
                                        <tr class="{{ $rowBg }} border-b border-rose-200/60 last:border-b-0 hover:bg-rose-50/40 transition-colors">
                                            <!-- Time Column with Colored Graphic Tag -->
                                            <td class="px-3 py-3.5 text-center font-bold text-xs sm:text-sm text-black border-r border-slate-200/80 align-middle whitespace-nowrap">
                                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gradient-to-r from-rose-50 via-white to-amber-50/50 border border-rose-200/80 shadow-2xs">
                                                    <span class="w-2 h-2 rounded-full bg-[#BE1E2D] shadow-xs"></span>
                                                    <span class="font-black text-black tracking-tight">{{ $timeSlot }}</span>
                                                </div>
                                            </td>

                                            <!-- Stage Columns (Direct clean text with color-coded zone badge) -->
                                            @foreach($data['stages'] as $stage)
                                                <td class="px-3 py-3.5 text-center border-r border-slate-200/80 last:border-r-0 align-middle">
                                                    @if(!empty($data['matrix'][$timeSlot][$stage->id]))
                                                        <div class="flex flex-col items-center justify-center gap-1.5">
                                                            @foreach($data['matrix'][$timeSlot][$stage->id] as $sch)
                                                                <div class="w-full text-center py-0.5">
                                                                    <div class="font-extrabold text-xs sm:text-sm text-slate-900 leading-snug tracking-tight">
                                                                        {{ $sch->program?->name }}
                                                                    </div>
                                                                    @if($sch->program?->zone)
                                                                        @php
                                                                            $zName = $sch->program->zone->name;
                                                                            $zStyle = match (true) {
                                                                                str_contains($zName, 'A') => 'bg-emerald-50 text-emerald-800 border-emerald-300',
                                                                                str_contains($zName, 'B') => 'bg-blue-50 text-blue-800 border-blue-300',
                                                                                str_contains($zName, 'C') => 'bg-purple-50 text-purple-800 border-purple-300',
                                                                                str_contains($zName, 'Mix') => 'bg-amber-50 text-amber-900 border-amber-300',
                                                                                default => 'bg-rose-50 text-rose-900 border-rose-300',
                                                                            };
                                                                            $dotColor = match (true) {
                                                                                str_contains($zName, 'A') => 'bg-emerald-500',
                                                                                str_contains($zName, 'B') => 'bg-blue-500',
                                                                                str_contains($zName, 'C') => 'bg-purple-500',
                                                                                str_contains($zName, 'Mix') => 'bg-amber-500',
                                                                                default => 'bg-rose-500',
                                                                            };
                                                                        @endphp
                                                                        <div class="mt-1">
                                                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-2xs {{ $zStyle }}">
                                                                                <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                                                                                <span>{{ $zName }}</span>
                                                                            </span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-slate-300 font-bold text-base select-none">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-12 text-center text-slate-500 font-bold bg-[#FAF8F5] rounded-xl border border-slate-200">
                            No programs scheduled for this date.
                        </div>
                    @endif
                </div>
            @endforeach
        @endif

    </div>

</body>
</html>
