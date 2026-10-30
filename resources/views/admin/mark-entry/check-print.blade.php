<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($programsData->count() === 1)
            {{ $programsData->first()['program']->name }} ({{ $programsData->first()['program']->code ?: $programsData->first()['program']->id }}) — Program Result | QUAF
        @else
            Program Results ({{ $selectedZone ?? 'Festival' }}) — QUAF
        @endif
    </title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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

        body, table, th, td, h1, h2, h3, h4, div, span, p {
            font-family: 'Rockwell Std', 'Rockwell', Georgia, serif;
        }

        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .page-sheet {
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .program-sheet {
                page-break-after: always;
                page-break-inside: avoid;
            }
            .program-sheet:last-child {
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

        .result-print-table {
            width: 100% !important;
            border-collapse: collapse !important;
            border: 1.5px solid #0f172a !important;
        }
        .result-print-table th {
            border: 1px solid #334155 !important;
            background-color: #F1F2F2 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .result-print-table td {
            border: 1px solid #cbd5e1 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .result-print-table tr:nth-child(even) td {
            background-color: #f8fafc !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .result-print-table tr.rank-first td {
            background-color: #fefce8 !important; /* soft yellow/gold */
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body.hide-scores .col-score {
            display: none !important;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen py-4 sm:py-8 px-2 sm:px-4 hide-scores">

    <!-- Action Toolbar (Hidden during print) -->
    <div class="no-print max-w-5xl mx-auto mb-6 p-4 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.mark-entry.check', ['zone' => $selectedZone, 'program' => $selectedProgramId]) }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back to Mark Check</span>
            </a>
            <div>
                <h2 class="font-bold text-sm text-slate-800">Program Result Print Preview</h2>
                <p class="text-xs text-slate-500 font-normal">Official Result Sheet with QUAF Header & Tabulation Report</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($programs->count() > 1 && !$printAll)
                <form method="GET" action="{{ route('admin.mark-entry.check.print') }}" class="inline-flex items-center gap-2">
                    <input type="hidden" name="zone" value="{{ $selectedZone }}">
                    <select name="program" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-900">
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" {{ (string)$selectedProgramId === (string)$prog->id ? 'selected' : '' }}>
                                {{ $prog->name }} ({{ $prog->code ?: $prog->id }})
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif

            <!-- Optional Score Column Toggle -->
            <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-50 border border-slate-300 hover:bg-slate-100 transition cursor-pointer select-none text-xs font-semibold text-slate-700">
                <input type="checkbox" id="toggleScoreCheckbox" onchange="toggleScoreColumn(this.checked)" class="w-4 h-4 rounded border-slate-300 text-[#BE1E2D] focus:ring-0 cursor-pointer">
                <span>Show Score</span>
            </label>

            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-[#BE1E2D] hover:bg-[#a01624] text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Result</span>
            </button>
        </div>
    </div>

    <!-- Printable Content Sheet(s) -->
    <div class="page-sheet max-w-5xl mx-auto space-y-8 print:space-y-0">
        @forelse($programsData as $data)
            @php
                $prog = $data['program'];
                $entries = $data['entries'];
            @endphp

            <div class="program-sheet bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-md print:border-none print:shadow-none print:p-0">
                
                <!-- Official QUAF Header Banner -->
                <div class="w-full flex justify-center mb-2">
                    <img src="{{ asset('images/offstage-pdf-header.png') }}" alt="Ādabīc Inheritance - QUAF" class="w-full max-h-22 sm:max-h-26 object-contain block mx-auto">
                </div>

                <!-- Title Section -->
                <div class="text-center mt-1 mb-3">
                    <h1 class="text-xl sm:text-2xl font-bold uppercase tracking-tight text-[#BE1E2D]">
                        Program Result Sheet
                    </h1>
                    <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 mt-0.5">
                        Official Tabulation & Verification Report
                    </div>
                </div>

                <!-- Program Metadata Bar -->
                <div class="mb-4 border border-slate-300 rounded-xl overflow-hidden bg-slate-50/70 text-xs">
                    <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-y sm:divide-y-0 divide-slate-300">
                        <div class="p-2.5">
                            <span class="text-slate-500 font-normal">Program ID:</span>
                            <span class="font-bold text-slate-900 ml-1">{{ $prog->code ?: $prog->id }}</span>
                        </div>
                        <div class="p-2.5">
                            <span class="text-slate-500 font-normal">Program Name:</span>
                            <span class="font-bold text-slate-900 ml-1">{{ $prog->name }}</span>
                        </div>
                        <div class="p-2.5">
                            <span class="text-slate-500 font-normal">Zone:</span>
                            <span class="font-bold text-slate-900 ml-1">{{ $prog->eligibility ?? 'All Zones' }}</span>
                        </div>
                        <div class="p-2.5">
                            <span class="text-slate-500 font-normal">Type & Stage:</span>
                            <span class="font-bold text-slate-900 ml-1">{{ ucfirst($prog->type ?? 'Individual') }} • {{ $prog->is_stage ? 'Stage' : 'Non-stage' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Results Table -->
                <table class="result-print-table text-left text-xs sm:text-sm">
                    <thead>
                        <tr>
                            <th class="px-2.5 py-2.5 text-center w-12 font-bold text-slate-900">NO</th>
                            <th class="px-3 py-2.5 text-center font-bold text-slate-900">LETTER</th>
                            <th class="px-3 py-2.5 text-center font-bold text-slate-900">ID</th>
                            <th class="px-4 py-2.5 font-bold text-slate-900">NAME</th>
                            <th class="px-3 py-2.5 font-bold text-slate-900">GROUP</th>
                            <th class="px-2.5 py-2.5 text-center font-bold text-slate-900">GRADE</th>
                            <th class="col-score px-3 py-2.5 text-center font-bold text-slate-900">SCORE</th>
                            <th class="px-3 py-2.5 text-center font-bold text-slate-900">RANK</th>
                            <th class="px-2.5 py-2.5 text-center font-bold text-slate-900">PTS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $index => $item)
                            <tr class="{{ $item->computed_rank === 'first' ? 'rank-first' : '' }}">
                                <td class="px-2.5 py-2.5 text-center text-slate-600 font-medium">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-3 py-2.5 text-center font-bold text-slate-900">
                                    {{ $item->code_letter ?: '-' }}
                                </td>
                                <td class="px-3 py-2.5 text-center text-slate-700 font-mono text-xs">
                                    {{ $item->student?->student_id ?: $item->chest_number }}
                                </td>
                                <td class="px-4 py-2.5 text-slate-900 font-medium">
                                    {{ $item->student?->name ?: 'Chest #'.$item->chest_number }}
                                </td>
                                <td class="px-3 py-2.5 text-slate-700 font-normal">
                                    {{ $item->group?->name ?? $item->student?->group?->name ?? '-' }}
                                </td>
                                <td class="px-2.5 py-2.5 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-bold {{ $item->computed_grade === 'A+' || $item->computed_grade === 'A' ? 'text-emerald-800' : ($item->computed_grade === 'B' ? 'text-blue-800' : ($item->computed_grade === 'C' ? 'text-amber-800' : 'text-slate-600')) }}">
                                        {{ $item->computed_grade }}
                                    </span>
                                </td>
                                <td class="col-score px-3 py-2.5 text-center font-bold text-slate-900">
                                    {{ number_format($item->computed_score, 0) }}
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    @if($item->computed_rank === 'first')
                                        <span class="inline-block px-2 py-0.5 font-bold text-amber-700 uppercase tracking-wider text-xs">
                                             FIRST
                                        </span>
                                    @elseif($item->computed_rank === 'second')
                                        <span class="inline-block px-2 py-0.5 font-bold text-slate-700 uppercase tracking-wider text-xs">
                                            SECOND
                                        </span>
                                    @elseif($item->computed_rank === 'third')
                                        <span class="inline-block px-2 py-0.5 font-bold text-amber-900 uppercase tracking-wider text-xs">
                                            THIRD
                                        </span>
                                    @else
                                        <span class="text-slate-300 font-bold">—</span>
                                    @endif
                                </td>
                                <td class="px-2.5 py-2.5 text-center font-bold">
                                    @if(($item->team_points ?? 0) > 0)
                                        <span class="text-slate-900 font-bold">{{ $item->team_points }}</span>
                                    @else
                                        <span class="text-slate-300 font-normal">0</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-message-td px-4 py-8 text-center text-slate-400 font-normal">
                                    No marks recorded for this program yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Footer Info -->
                <div class="mt-8 pt-3 border-t border-slate-200 text-[10px] text-slate-500 flex justify-between items-center gap-4">
                    <div class="inline-flex items-center gap-1.5 whitespace-nowrap leading-none shrink-0">
                        <img src="{{ asset('images/quaf-black-title.svg') }}" alt="QUAF" class="w-auto object-contain inline-block align-middle" style="height: 10px; max-height: 11px;">
                        <span class="font-normal text-slate-600 whitespace-nowrap leading-none">Markaz Cultural Festival</span>
                    </div>
                    <span class="text-[10px] text-slate-400 whitespace-nowrap shrink-0 leading-none">Printed: {{ now()->format('d M Y, h:i A') }}</span>
                </div>

            </div>
        @empty
            <div class="bg-white p-12 rounded-2xl border border-slate-200 text-center">
                <p class="text-slate-500 text-sm">No program results found for the selected criteria.</p>
                <a href="{{ route('admin.mark-entry.check') }}" class="inline-block mt-3 px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold">
                    Return to Mark Check
                </a>
            </div>
        @endforelse
    </div>

    <script>
        function toggleScoreColumn(show) {
            document.body.classList.toggle('hide-scores', !show);
            document.querySelectorAll('.empty-message-td').forEach(td => {
                td.colSpan = show ? 9 : 8;
            });
        }
    </script>
</body>
</html>
