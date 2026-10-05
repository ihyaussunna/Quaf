<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offstage Program Schedule - QUAF 9.0</title>
    @vite(['resources/css/app.css'])
    <style>
        @font-face {
            font-family: 'Rockwell';
            src: local('Rockwell Bold'), local('Rockwell-Bold'), url('/fonts/Rockwell-Bold.woff2') format('woff2');
            font-weight: 700;
            font-style: normal;
        }
        @font-face {
            font-family: 'Rockwell';
            src: local('Rockwell'), local('Rockwell Regular'), local('Rockwell-Regular'), url('/fonts/Rockwell-Regular.woff2') format('woff2');
            font-weight: 400;
            font-style: normal;
        }
        @font-face {
            font-family: 'Rockwell';
            src: local('Rockwell Extra Bold'), local('Rockwell-ExtraBold'), url('/fonts/Rockwell-Extra-Bold.woff2') format('woff2');
            font-weight: 800;
            font-style: normal;
        }

        body, table, th, td, h1, h2, h3, div, span, p {
            font-family: 'Rockwell', 'Rockwell Std', Georgia, serif !important;
        }

        @page {
            size: A4 portrait;
            margin: 10mm 14mm 10mm 14mm;
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
            table {
                page-break-inside: auto;
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
            <a href="{{ route('admin.schedules.offstage', ['date' => $date ?? '2026-10-06']) }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back to Scheduler</span>
            </a>
            <div>
                <h2 class="font-bold text-sm text-slate-800">Print Preview (Rockwell Design)</h2>
                <p class="text-xs text-slate-500">Official Offstage Schedule formatted for high-definition A4 PDF download</p>
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
                <!-- Header Banner Image (Exact official vector/PNG) -->
                <div class="w-full flex justify-center mb-2">
                    <img src="{{ asset('images/offstage-pdf-header.png') }}" alt="Ādabīc Inheritance - QUAF 9.0" class="w-full max-h-24 sm:max-h-28 object-contain">
                </div>

                <!-- Top Horizontal Divider -->
                <hr class="border-t-2 border-black my-2.5">

                <!-- Red Main Title in Bold Rockwell -->
                <h1 class="text-center text-2xl sm:text-3xl font-extrabold tracking-tight text-[#BE1E2D]">
                    Offstage Program Schedule
                </h1>

                <!-- Date Subtitle in Bold Rockwell -->
                <h2 class="text-center text-base sm:text-lg font-bold text-black mt-1">
                    {{ $formattedDate }}
                </h2>

                <!-- Bottom Horizontal Divider -->
                <hr class="border-t-2 border-black my-2.5">

                <!-- Time Slot Blocks -->
                @forelse($schedulesByTime as $timeSlot => $slotItems)
                    <div class="mt-6 mb-4">
                        <!-- Time Header -->
                        <div class="font-extrabold text-sm sm:text-base text-black mb-2">
                            Time | {{ $timeSlot }}
                        </div>

                        <!-- Schedule Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse border border-[#c0d8d0]">
                                <thead>
                                    <tr class="bg-[#1C3834] text-white">
                                        <th class="border border-[#1C3834] px-4 py-2.5 text-left font-bold text-xs sm:text-sm w-28 sm:w-32">Stage</th>
                                        <th class="border border-[#1C3834] px-4 py-2.5 text-left font-bold text-xs sm:text-sm w-24 sm:w-28">Venue</th>
                                        <th class="border border-[#1C3834] px-4 py-2.5 text-left font-bold text-xs sm:text-sm">Item</th>
                                        <th class="border border-[#1C3834] px-4 py-2.5 text-left font-bold text-xs sm:text-sm w-28 sm:w-32">Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($slotItems as $idx => $sch)
                                        <tr class="{{ $idx % 2 === 0 ? 'bg-white' : 'bg-[#eef6f3]' }}">
                                            <td class="border border-[#c0d8d0] px-4 py-2 font-bold text-xs sm:text-sm text-black">
                                                {{ $sch->stage?->name }}
                                            </td>
                                            <td class="border border-[#c0d8d0] px-4 py-2 font-bold text-xs sm:text-sm text-black">
                                                {{ $sch->stage?->venue }}
                                            </td>
                                            <td class="border border-[#c0d8d0] px-4 py-2 font-bold text-xs sm:text-sm text-black">
                                                {{ $sch->program?->name }}
                                                @if($sch->program?->zone)
                                                    <span class="font-bold">({{ $sch->program->zone->name }})</span>
                                                @endif
                                            </td>
                                            <td class="border border-[#c0d8d0] px-4 py-2 font-bold text-xs sm:text-sm text-black">
                                                @php
                                                    $duration = $sch->program?->duration_minutes;
                                                    if ($duration >= 60) {
                                                        $durText = ($duration / 60) . ' hour';
                                                    } else {
                                                        $durText = ($duration ?: 40) . ' min';
                                                    }
                                                @endphp
                                                {{ $durText }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-500 font-bold">
                        No offstage programs scheduled for this date.
                    </div>
                @endforelse
            </div>
        @else
            <!-- All Dates Multi-Page Mode -->
            @foreach($groupedByDate as $dateKey => $timeSlots)
                <div class="day-section mb-12">
                    <!-- Header Banner Image -->
                    <div class="w-full flex justify-center mb-2">
                        <img src="{{ asset('images/offstage-pdf-header.png') }}" alt="Ādabīc Inheritance - QUAF 9.0" class="w-full max-h-24 sm:max-h-28 object-contain">
                    </div>

                    <hr class="border-t-2 border-black my-2.5">

                    <h1 class="text-center text-2xl sm:text-3xl font-extrabold tracking-tight text-[#BE1E2D]">
                        Offstage Program Schedule
                    </h1>

                    <h2 class="text-center text-base sm:text-lg font-bold text-black mt-1">
                        {{ \Carbon\Carbon::parse($dateKey)->format('Y F d l') }}
                    </h2>

                    <hr class="border-t-2 border-black my-2.5">

                    @foreach($timeSlots as $timeSlot => $slotItems)
                        <div class="mt-6 mb-4">
                            <div class="font-extrabold text-sm sm:text-base text-black mb-2">
                                Time | {{ $timeSlot }}
                            </div>

                            <table class="w-full border-collapse border border-[#c0d8d0]">
                                <thead>
                                    <tr class="bg-[#1C3834] text-white">
                                        <th class="border border-[#1C3834] px-4 py-2.5 text-left font-bold text-xs sm:text-sm w-28 sm:w-32">Stage</th>
                                        <th class="border border-[#1C3834] px-4 py-2.5 text-left font-bold text-xs sm:text-sm w-24 sm:w-28">Venue</th>
                                        <th class="border border-[#1C3834] px-4 py-2.5 text-left font-bold text-xs sm:text-sm">Item</th>
                                        <th class="border border-[#1C3834] px-4 py-2.5 text-left font-bold text-xs sm:text-sm w-28 sm:w-32">Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($slotItems as $idx => $sch)
                                        <tr class="{{ $idx % 2 === 0 ? 'bg-white' : 'bg-[#eef6f3]' }}">
                                            <td class="border border-[#c0d8d0] px-4 py-2 font-bold text-xs sm:text-sm text-black">
                                                {{ $sch->stage?->name }}
                                            </td>
                                            <td class="border border-[#c0d8d0] px-4 py-2 font-bold text-xs sm:text-sm text-black">
                                                {{ $sch->stage?->venue }}
                                            </td>
                                            <td class="border border-[#c0d8d0] px-4 py-2 font-bold text-xs sm:text-sm text-black">
                                                {{ $sch->program?->name }}
                                                @if($sch->program?->zone)
                                                    <span class="font-bold">({{ $sch->program->zone->name }})</span>
                                                @endif
                                            </td>
                                            <td class="border border-[#c0d8d0] px-4 py-2 font-bold text-xs sm:text-sm text-black">
                                                @php
                                                    $duration = $sch->program?->duration_minutes;
                                                    if ($duration >= 60) {
                                                        $durText = ($duration / 60) . ' hour';
                                                    } else {
                                                        $durText = ($duration ?: 40) . ' min';
                                                    }
                                                @endphp
                                                {{ $durText }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif

    </div>

</body>
</html>
