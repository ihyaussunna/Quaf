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
            margin: 10mm 12mm 10mm 12mm;
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
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
            }
            .day-section {
                page-break-after: always;
            }
            .day-section:last-child {
                page-break-after: auto;
            }
            .time-slot-block {
                page-break-inside: avoid;
            }
            table {
                page-break-inside: auto;
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
    <div class="no-print max-w-4xl mx-auto mb-6 p-4 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route(($routePrefix ?? 'admin.schedules.') . 'offstage', ['date' => $date ?? '2026-10-06']) }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back to Scheduler</span>
            </a>
            <div>
                <h2 class="font-bold text-sm text-slate-800">Program Schedule Print Preview</h2>
                <p class="text-xs text-slate-500 font-normal">Official Program Schedule formatted in creative Rockwell Std design for high-definition A4 PDF download</p>
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
    <div class="page-container max-w-4xl mx-auto bg-white p-6 sm:p-10 shadow-lg rounded-2xl border border-slate-200 print:border-none print:shadow-none print:rounded-none">

        @if($mode === 'single')
            <div class="day-section">
                <!-- Header Banner Image (Official Ādabīc Inheritance & QUAF Header) -->
                <div class="w-full flex justify-center mb-3">
                    <img src="{{ asset('images/offstage-pdf-header.png') }}" alt="Ādabīc Inheritance - QUAF" class="w-full max-h-24 sm:max-h-28 object-contain">
                </div>

                <!-- Creative Decorative Motif Accent Bar -->
                <div class="relative flex items-center justify-center my-3">
                    <div class="w-full border-t-2 border-[#BE1E2D]"></div>
                    <div class="absolute bg-white px-4 flex items-center gap-2">
                        <span class="w-2 h-2 rotate-45 bg-[#F2BC2E]"></span>
                        <span class="text-[11px] font-extrabold uppercase tracking-widest text-[#BE1E2D]">QUAF '26</span>
                        <span class="w-2 h-2 rotate-45 bg-[#F2BC2E]"></span>
                    </div>
                </div>

                <!-- Creative Title in Bold Rockwell Std -->
                <h1 class="text-center text-3xl sm:text-4xl font-extrabold tracking-tight text-[#BE1E2D] uppercase mt-2 mb-1">
                    Program Schedule
                </h1>

                <!-- Creative Date & Milestone Badge -->
                <div class="flex justify-center mt-2 mb-4">
                    <div class="inline-flex items-center gap-2 px-5 py-1.5 rounded-full bg-[#1C3834] text-white text-xs sm:text-sm font-bold shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-[#F2BC2E]"></span>
                        <span>{{ $formattedDate }}</span>
                        <span class="w-2 h-2 rounded-full bg-[#F2BC2E]"></span>
                    </div>
                </div>

                <div class="border-b border-slate-200 mb-6"></div>

                <!-- Time Slot Blocks -->
                @forelse($schedulesByTime as $timeSlot => $slotItems)
                    <div class="time-slot-block mb-6">
                        <!-- Creative Time Slot Ribbon -->
                        <div class="flex items-center gap-3 mb-2.5">
                            <div class="px-3.5 py-1 rounded-lg bg-[#BE1E2D] text-white text-xs font-extrabold uppercase tracking-wider flex items-center gap-1.5 shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ $timeSlot }}</span>
                            </div>
                            <div class="h-0.5 flex-1 bg-gradient-to-r from-[#BE1E2D]/40 via-slate-200 to-transparent"></div>
                        </div>

                        <!-- Schedule Table -->
                        <div class="rounded-xl overflow-hidden border border-[#c0d8d0] shadow-2xs">
                            <table class="w-full border-collapse text-left">
                                <thead>
                                    <tr class="bg-[#1C3834] text-white">
                                        <th class="px-4 py-2.5 text-left font-bold text-xs uppercase tracking-wider w-32 border-r border-[#274c47]">Stage</th>
                                        <th class="px-4 py-2.5 text-left font-bold text-xs uppercase tracking-wider w-28 border-r border-[#274c47]">Venue</th>
                                        <th class="px-4 py-2.5 text-left font-bold text-xs uppercase tracking-wider">Program / Item</th>
                                        <th class="px-4 py-2.5 text-left font-bold text-xs uppercase tracking-wider w-28 border-l border-[#274c47]">Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($slotItems as $idx => $sch)
                                        <tr class="{{ $idx % 2 === 0 ? 'bg-white' : 'bg-[#f4f9f7]' }} border-b border-[#dbe8e3] last:border-b-0">
                                            <td class="px-4 py-2.5 font-bold text-xs sm:text-sm text-slate-900 border-r border-[#e3ede9]">
                                                {{ $sch->stage?->name ?? 'TBA' }}
                                            </td>
                                            <td class="px-4 py-2.5 font-medium text-xs text-slate-600 border-r border-[#e3ede9]">
                                                {{ $sch->stage?->venue ?: '—' }}
                                            </td>
                                            <td class="px-4 py-2.5 text-xs sm:text-sm text-slate-900">
                                                <span class="font-bold text-slate-900">{{ $sch->program?->name }}</span>
                                                @if($sch->program?->code)
                                                    <span class="text-[10px] text-slate-400 font-normal ml-1">[{{ $sch->program->code }}]</span>
                                                @endif
                                                @if($sch->program?->zone)
                                                    <span class="inline-block ml-2 px-2 py-0.5 rounded-full bg-rose-50 text-[#BE1E2D] border border-rose-200 text-[10px] font-bold">
                                                        {{ $sch->program->zone->name }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2.5 font-bold text-xs text-slate-700 border-l border-[#e3ede9]">
                                                @php
                                                    $duration = $sch->program?->duration_minutes;
                                                    if ($duration >= 60) {
                                                        $durText = ($duration / 60) . ' hour';
                                                    } else {
                                                        $durText = ($duration ?: 40) . ' min';
                                                    }
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-slate-800 text-[11px] font-bold">
                                                    {{ $durText }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-500 font-bold">
                        No programs scheduled for this date.
                    </div>
                @endforelse

                <!-- Sheet Footer -->
                <div class="pt-4 border-t border-slate-200 mt-8 flex flex-wrap items-center justify-between text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                    <span>QUAF '26 • Markaz Cultural Festival</span>
                    <span>Jamia Markaz Karanthur • Festival Office</span>
                </div>
            </div>
        @else
            <!-- All Dates Multi-Page Mode -->
            @foreach($groupedByDate as $dateKey => $timeSlots)
                <div class="day-section mb-12">
                    <!-- Header Banner Image -->
                    <div class="w-full flex justify-center mb-3">
                        <img src="{{ asset('images/offstage-pdf-header.png') }}" alt="Ādabīc Inheritance - QUAF" class="w-full max-h-24 sm:max-h-28 object-contain">
                    </div>

                    <!-- Creative Decorative Motif Accent Bar -->
                    <div class="relative flex items-center justify-center my-3">
                        <div class="w-full border-t-2 border-[#BE1E2D]"></div>
                        <div class="absolute bg-white px-4 flex items-center gap-2">
                            <span class="w-2 h-2 rotate-45 bg-[#F2BC2E]"></span>
                            <span class="text-[11px] font-extrabold uppercase tracking-widest text-[#BE1E2D]">QUAF '26</span>
                            <span class="w-2 h-2 rotate-45 bg-[#F2BC2E]"></span>
                        </div>
                    </div>

                    <!-- Creative Title in Bold Rockwell Std -->
                    <h1 class="text-center text-3xl sm:text-4xl font-extrabold tracking-tight text-[#BE1E2D] uppercase mt-2 mb-1">
                        Program Schedule
                    </h1>

                    <!-- Creative Date & Milestone Badge -->
                    <div class="flex justify-center mt-2 mb-4">
                        <div class="inline-flex items-center gap-2 px-5 py-1.5 rounded-full bg-[#1C3834] text-white text-xs sm:text-sm font-bold shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-[#F2BC2E]"></span>
                            <span>{{ \Carbon\Carbon::parse($dateKey)->format('Y F d l') }}</span>
                            <span class="w-2 h-2 rounded-full bg-[#F2BC2E]"></span>
                        </div>
                    </div>

                    <div class="border-b border-slate-200 mb-6"></div>

                    @foreach($timeSlots as $timeSlot => $slotItems)
                        <div class="time-slot-block mb-6">
                            <!-- Creative Time Slot Ribbon -->
                            <div class="flex items-center gap-3 mb-2.5">
                                <div class="px-3.5 py-1 rounded-lg bg-[#BE1E2D] text-white text-xs font-extrabold uppercase tracking-wider flex items-center gap-1.5 shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ $timeSlot }}</span>
                                </div>
                                <div class="h-0.5 flex-1 bg-gradient-to-r from-[#BE1E2D]/40 via-slate-200 to-transparent"></div>
                            </div>

                            <div class="rounded-xl overflow-hidden border border-[#c0d8d0] shadow-2xs">
                                <table class="w-full border-collapse text-left">
                                    <thead>
                                        <tr class="bg-[#1C3834] text-white">
                                            <th class="px-4 py-2.5 text-left font-bold text-xs uppercase tracking-wider w-32 border-r border-[#274c47]">Stage</th>
                                            <th class="px-4 py-2.5 text-left font-bold text-xs uppercase tracking-wider w-28 border-r border-[#274c47]">Venue</th>
                                            <th class="px-4 py-2.5 text-left font-bold text-xs uppercase tracking-wider">Program / Item</th>
                                            <th class="px-4 py-2.5 text-left font-bold text-xs uppercase tracking-wider w-28 border-l border-[#274c47]">Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($slotItems as $idx => $sch)
                                            <tr class="{{ $idx % 2 === 0 ? 'bg-white' : 'bg-[#f4f9f7]' }} border-b border-[#dbe8e3] last:border-b-0">
                                                <td class="px-4 py-2.5 font-bold text-xs sm:text-sm text-slate-900 border-r border-[#e3ede9]">
                                                    {{ $sch->stage?->name ?? 'TBA' }}
                                                </td>
                                                <td class="px-4 py-2.5 font-medium text-xs text-slate-600 border-r border-[#e3ede9]">
                                                    {{ $sch->stage?->venue ?: '—' }}
                                                </td>
                                                <td class="px-4 py-2.5 text-xs sm:text-sm text-slate-900">
                                                    <span class="font-bold text-slate-900">{{ $sch->program?->name }}</span>
                                                    @if($sch->program?->code)
                                                        <span class="text-[10px] text-slate-400 font-normal ml-1">[{{ $sch->program->code }}]</span>
                                                    @endif
                                                    @if($sch->program?->zone)
                                                        <span class="inline-block ml-2 px-2 py-0.5 rounded-full bg-rose-50 text-[#BE1E2D] border border-rose-200 text-[10px] font-bold">
                                                            {{ $sch->program->zone->name }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2.5 font-bold text-xs text-slate-700 border-l border-[#e3ede9]">
                                                    @php
                                                        $duration = $sch->program?->duration_minutes;
                                                        if ($duration >= 60) {
                                                            $durText = ($duration / 60) . ' hour';
                                                        } else {
                                                            $durText = ($duration ?: 40) . ' min';
                                                        }
                                                    @endphp
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-slate-800 text-[11px] font-bold">
                                                        {{ $durText }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach

                    <!-- Sheet Footer -->
                    <div class="pt-4 border-t border-slate-200 mt-8 flex flex-wrap items-center justify-between text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                        <span>QUAF '26 • Markaz Cultural Festival</span>
                        <span>Jamia Markaz Karanthur • Festival Office</span>
                    </div>
                </div>
            @endforeach
        @endif

    </div>

</body>
</html>
