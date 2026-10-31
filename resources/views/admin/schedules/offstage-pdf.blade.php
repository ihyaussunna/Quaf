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

        /* Clean Matrix Timetable Styling - Matching user screenshot with exact alternating row shades and crisp borders */
        .schedule-table-wrap {
            border: 1px solid #C6C7C7 !important;
            border-radius: 10px;
            overflow: hidden;
            background-color: #FFFFFF;
        }
        .schedule-matrix-table {
            width: 100% !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
        }
        .schedule-matrix-table th,
        .schedule-matrix-table td {
            border-right: 1px solid #C6C7C7 !important;
            border-bottom: 1px solid #C6C7C7 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .schedule-matrix-table th:last-child,
        .schedule-matrix-table td:last-child {
            border-right: none !important;
        }
        .schedule-matrix-table tbody tr:last-child td {
            border-bottom: none !important;
        }
        .schedule-header-th {
            background-color: #FFFFFF !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .schedule-header-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 12px;
            border-radius: 8px;
            background-color: rgba(15, 23, 42, 0.05) !important; /* 5% opacity grey shape */
            border: 1px solid rgba(15, 23, 42, 0.08);
            color: #0f172a;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .schedule-row-blue,
        .schedule-row-blue td {
            background-color: #F2F6FE !important; /* 5% opacity blue */
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .schedule-row-white,
        .schedule-row-white td {
            background-color: #FFFFFF !important; /* white */
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
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
                <div class="w-full flex justify-center mb-4">
                    <img src="{{ asset('images/offstage-pdf-header.png') }}" alt="Ādabīc Inheritance - QUAF" class="w-full max-h-24 sm:max-h-28 object-contain">
                </div>

                <!-- Creative Title in Rockwell Std -->
                <h1 class="text-center text-2xl sm:text-3xl font-bold tracking-tight text-[#BE1E2D] uppercase mt-1 mb-1">
                    Program Schedule
                </h1>

                <!-- Creative Date & Milestone Badge (Clean Light Theme) -->
                <div class="flex justify-center mt-2 mb-5">
                    <div class="inline-flex items-center gap-2 px-5 py-1.5 rounded-full bg-gradient-to-r from-[#FFF5F5] via-[#FFFBF2] to-[#FFF5F5] border border-[#FECACA] text-[#991B1B] text-xs sm:text-sm font-medium shadow-2xs">
                        <span class="w-1.5 h-1.5 rotate-45 bg-[#F2BC2E]"></span>
                        <span>{{ $formattedDate }}</span>
                        <span class="w-1.5 h-1.5 rotate-45 bg-[#F2BC2E]"></span>
                    </div>
                </div>

                <div class="border-b border-slate-200 mb-6"></div>

                @if($stages->isNotEmpty() && $timeSlots->isNotEmpty())
                    <!-- Timetable Matrix Table (Exact Match to User Graphic Template) -->
                    <div class="schedule-table-wrap shadow-xs">
                        <table class="schedule-matrix-table text-left">
                            <thead class="schedule-header-row">
                                <tr>
                                    <th class="schedule-header-th w-[15%] sm:w-[14%] px-3 py-3 text-center align-middle">
                                        <div class="schedule-header-pill font-medium text-xs sm:text-sm text-slate-800">
                                            Time
                                        </div>
                                    </th>
                                    @php
                                        $stageColWidth = round(85 / max(1, $stages->count()), 2);
                                    @endphp
                                    @foreach($stages as $stage)
                                        <th class="schedule-header-th px-3 py-3 text-center align-middle" style="width: {{ $stageColWidth }}%;">
                                            <div class="schedule-header-pill flex-col font-medium text-xs sm:text-sm text-slate-800">
                                                <div>{{ $stage->name }}</div>
                                                @if($stage->location && !str_contains(strtolower($stage->name), strtolower($stage->location)))
                                                    <div class="text-[10px] text-slate-500 font-normal mt-0.5 normal-case tracking-normal">
                                                        Venue: {{ $stage->location }}
                                                    </div>
                                                @endif
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timeSlots as $idx => $timeSlot)
                                    @php
                                        // Alternating row graphics: 5% opacity blue & white
                                        $rowClass = $idx % 2 === 0 ? 'schedule-row-blue' : 'schedule-row-white';
                                        $rowHex = $idx % 2 === 0 ? '#F2F6FE' : '#FFFFFF';
                                    @endphp
                                    <tr class="{{ $rowClass }}" style="background-color: {{ $rowHex }};">
                                        <!-- Time Column -->
                                        <td class="px-3 py-3.5 text-center font-normal text-xs sm:text-sm text-black align-middle whitespace-nowrap">
                                            {{ $timeSlot }}
                                        </td>

                                        <!-- Stage Columns -->
                                        @foreach($stages as $stage)
                                            <td class="px-3 py-3.5 text-center align-middle">
                                                @if(!empty($matrix[$timeSlot][$stage->id]))
                                                    <div class="flex flex-col items-center justify-center gap-1">
                                                        @foreach($matrix[$timeSlot][$stage->id] as $sch)
                                                            <div class="w-full text-center py-0.5">
                                                                <div class="font-normal text-xs sm:text-sm text-slate-900 leading-snug">
                                                                    {{ $sch->program?->name }}
                                                                </div>
                                                                @if($sch->program?->zone)
                                                                    @php
                                                                        $zName = $sch->program->zone->name;
                                                                        $zStyle = match (true) {
                                                                            str_contains($zName, 'A') => 'bg-white text-slate-700 border-slate-200/90 shadow-2xs',
                                                                            str_contains($zName, 'B') => 'bg-blue-50/80 text-blue-900 border-blue-200',
                                                                            str_contains($zName, 'C') => 'bg-purple-50/80 text-purple-900 border-purple-200',
                                                                            str_contains($zName, 'Mix') => 'bg-amber-50/80 text-amber-900 border-amber-200',
                                                                            default => 'bg-blue-50/80 text-blue-900 border-blue-200',
                                                                        };
                                                                    @endphp
                                                                    <div class="mt-1">
                                                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider border shadow-2xs {{ $zStyle }}">
                                                                            {{ $zName }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-slate-400 font-normal text-base select-none">—</span>
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

                <!-- Official Result PDF Style Clean Footer -->
                <div class="mt-8 pt-3 border-t border-slate-300 text-[10px] text-slate-500 flex justify-between items-center gap-4 font-mono">
                    <div class="inline-flex items-center gap-1.5 whitespace-nowrap leading-none shrink-0">
                        <img src="{{ asset('images/quaf-black-title.svg') }}" alt="QUAF" class="w-auto object-contain inline-block align-middle" style="height: 11px; max-height: 12px;">
                        <span class="font-normal text-slate-600 whitespace-nowrap leading-none font-sans">Markaz Cultural Festival</span>
                    </div>
                    <span class="text-[10px] text-slate-400 whitespace-nowrap shrink-0 leading-none">Printed: {{ now()->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        @else
            <!-- All Dates Multi-Page Mode -->
            @foreach($datesData as $dateKey => $data)
                <div class="day-section {{ !$loop->last ? 'mb-12' : '' }}">
                    <!-- Header Banner Image -->
                    <div class="w-full flex justify-center mb-4">
                        <img src="{{ asset('images/offstage-pdf-header.png') }}" alt="Ādabīc Inheritance - QUAF" class="w-full max-h-24 sm:max-h-28 object-contain">
                    </div>

                    <!-- Creative Title in Rockwell Std -->
                    <h1 class="text-center text-2xl sm:text-3xl font-bold tracking-tight text-[#BE1E2D] uppercase mt-1 mb-1">
                        Program Schedule
                    </h1>

                    <!-- Creative Date & Milestone Badge -->
                    <div class="flex justify-center mt-2 mb-5">
                        <div class="inline-flex items-center gap-2 px-5 py-1.5 rounded-full bg-gradient-to-r from-[#FFF5F5] via-[#FFFBF2] to-[#FFF5F5] border border-[#FECACA] text-[#991B1B] text-xs sm:text-sm font-medium shadow-2xs">
                            <span class="w-1.5 h-1.5 rotate-45 bg-[#F2BC2E]"></span>
                            <span>{{ $data['formattedDate'] }}</span>
                            <span class="w-1.5 h-1.5 rotate-45 bg-[#F2BC2E]"></span>
                        </div>
                    </div>

                    <div class="border-b border-slate-200 mb-6"></div>

                    @if($data['stages']->isNotEmpty() && $data['timeSlots']->isNotEmpty())
                        <!-- Timetable Matrix Table (Exact Match to User Graphic Template) -->
                        <div class="schedule-table-wrap shadow-xs">
                            <table class="schedule-matrix-table text-left">
                                <thead class="schedule-header-row">
                                    <tr>
                                        <th class="schedule-header-th w-[15%] sm:w-[14%] px-3 py-3 text-center align-middle">
                                            <div class="schedule-header-pill font-medium text-xs sm:text-sm text-slate-800">
                                                Time
                                            </div>
                                        </th>
                                        @php
                                            $stageColWidth = round(85 / max(1, $data['stages']->count()), 2);
                                        @endphp
                                        @foreach($data['stages'] as $stage)
                                            <th class="schedule-header-th px-3 py-3 text-center align-middle" style="width: {{ $stageColWidth }}%;">
                                                <div class="schedule-header-pill flex-col font-medium text-xs sm:text-sm text-slate-800">
                                                    <div>{{ $stage->name }}</div>
                                                    @if($stage->location && !str_contains(strtolower($stage->name), strtolower($stage->location)))
                                                        <div class="text-[10px] text-slate-500 font-normal mt-0.5 normal-case tracking-normal">
                                                            Venue: {{ $stage->location }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['timeSlots'] as $idx => $timeSlot)
                                        @php
                                            // Alternating row graphics: 5% opacity blue & white
                                            $rowClass = $idx % 2 === 0 ? 'schedule-row-blue' : 'schedule-row-white';
                                            $rowHex = $idx % 2 === 0 ? '#F2F6FE' : '#FFFFFF';
                                        @endphp
                                        <tr class="{{ $rowClass }}" style="background-color: {{ $rowHex }};">
                                            <!-- Time Column -->
                                            <td class="px-3 py-3.5 text-center font-normal text-xs sm:text-sm text-black align-middle whitespace-nowrap">
                                                {{ $timeSlot }}
                                            </td>

                                            <!-- Stage Columns -->
                                            @foreach($data['stages'] as $stage)
                                                <td class="px-3 py-3.5 text-center align-middle">
                                                    @if(!empty($data['matrix'][$timeSlot][$stage->id]))
                                                        <div class="flex flex-col items-center justify-center gap-1">
                                                            @foreach($data['matrix'][$timeSlot][$stage->id] as $sch)
                                                                <div class="w-full text-center py-0.5">
                                                                    <div class="font-normal text-xs sm:text-sm text-slate-900 leading-snug">
                                                                        {{ $sch->program?->name }}
                                                                    </div>
                                                                    @if($sch->program?->zone)
                                                                        @php
                                                                            $zName = $sch->program->zone->name;
                                                                            $zStyle = match (true) {
                                                                                str_contains($zName, 'A') => 'bg-white text-slate-700 border-slate-200/90 shadow-2xs',
                                                                                str_contains($zName, 'B') => 'bg-blue-50/80 text-blue-900 border-blue-200',
                                                                                str_contains($zName, 'C') => 'bg-purple-50/80 text-purple-900 border-purple-200',
                                                                                str_contains($zName, 'Mix') => 'bg-amber-50/80 text-amber-900 border-amber-200',
                                                                                default => 'bg-blue-50/80 text-blue-900 border-blue-200',
                                                                            };
                                                                        @endphp
                                                                        <div class="mt-1">
                                                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider border shadow-2xs {{ $zStyle }}">
                                                                                {{ $zName }}
                                                                            </span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-slate-400 font-normal text-base select-none">—</span>
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

                    <!-- Official Result PDF Style Clean Footer -->
                    <div class="mt-8 pt-3 border-t border-slate-300 text-[10px] text-slate-500 flex justify-between items-center gap-4 font-mono">
                        <div class="inline-flex items-center gap-1.5 whitespace-nowrap leading-none shrink-0">
                            <img src="{{ asset('images/quaf-black-title.svg') }}" alt="QUAF" class="w-auto object-contain inline-block align-middle" style="height: 11px; max-height: 12px;">
                            <span class="font-normal text-slate-600 whitespace-nowrap leading-none font-sans">Markaz Cultural Festival</span>
                        </div>
                        <span class="text-[10px] text-slate-400 whitespace-nowrap shrink-0 leading-none">Printed: {{ now()->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
            @endforeach
        @endif

    </div>

</body>
</html>
