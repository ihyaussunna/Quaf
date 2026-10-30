<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($singleJudge)
            Judge Duty Slip — {{ $singleJudge->name }} | QUAF 2026
        @else
            Judges Official Duty Allocation Schedule | QUAF 2026
        @endif
    </title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

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

        body, table, th, td, h1, h2, h3, h4, div, span, p, a, button {
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
            .page-container {
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .judge-sheet-break {
                page-break-after: always !important;
                break-after: page !important;
            }
            .judge-sheet-break:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
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

        /* Clean Matrix Timetable Table Styles */
        .duty-table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        .duty-table th,
        .duty-table td {
            border: 1px solid #cbd5e1 !important;
            padding: 6px 8px !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .duty-table thead th {
            background-color: #0f172a !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 11px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen py-6 print:py-0">

    <!-- Floating Top Toolbar (Hidden during print) -->
    <div class="no-print fixed top-4 inset-x-0 z-50 flex justify-center px-4">
        <div class="bg-slate-900/95 backdrop-blur-md text-white rounded-2xl px-5 py-3 shadow-2xl border border-white/10 flex flex-wrap items-center gap-3 text-xs font-mono">
            <a href="{{ route('admin.judges.duty', ['judge_id' => $judgeId]) }}" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white font-bold transition">
                &larr; Admin Duty View
            </a>

            <div class="h-4 w-px bg-white/20"></div>

            <!-- Mode Selector: Per Judge Page vs Continuous -->
            <div class="flex items-center gap-1 bg-white/10 p-1 rounded-xl">
                <a href="{{ route('admin.judges.duty.print', ['mode' => 'per_page', 'judge_id' => $judgeId, 'include_unassigned' => $includeUnassigned ? 1 : 0]) }}"
                   class="px-2.5 py-1 rounded-lg font-bold transition {{ $mode === 'per_page' ? 'bg-[#be1e2d] text-white shadow-xs' : 'text-slate-300 hover:text-white' }}">
                    1 Judge Per Page
                </a>
                <a href="{{ route('admin.judges.duty.print', ['mode' => 'continuous', 'judge_id' => $judgeId, 'include_unassigned' => $includeUnassigned ? 1 : 0]) }}"
                   class="px-2.5 py-1 rounded-lg font-bold transition {{ $mode === 'continuous' ? 'bg-[#be1e2d] text-white shadow-xs' : 'text-slate-300 hover:text-white' }}">
                    Continuous Sheet
                </a>
            </div>

            <!-- Judge Selector -->
            <select onchange="window.location.href = this.value" class="bg-slate-800 border border-white/20 rounded-lg px-2.5 py-1.5 text-xs text-white focus:outline-none">
                <option value="{{ route('admin.judges.duty.print', ['mode' => $mode, 'include_unassigned' => $includeUnassigned ? 1 : 0]) }}">
                    All Judges ({{ $allJudges->count() }})
                </option>
                @foreach($allJudges as $j)
                    <option value="{{ route('admin.judges.duty.print', ['mode' => $mode, 'judge_id' => $j->id]) }}" {{ (string)$judgeId === (string)$j->id ? 'selected' : '' }}>
                        {{ $j->name }}
                    </option>
                @endforeach
            </select>

            <button onclick="window.print()" class="px-4 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black flex items-center gap-1.5 transition shadow-sm cursor-pointer uppercase tracking-wider">
                <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print / Save as PDF</span>
            </button>
        </div>
    </div>

    <!-- Main Container -->
    <div class="max-w-4xl mx-auto px-4 print:px-0 print:max-w-none pt-14 print:pt-0">
        @if($judges->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-300 p-12 text-center my-8">
                <h3 class="text-lg font-bold text-slate-800">No Judges Found to Print</h3>
                <p class="text-xs text-slate-500 mt-1 font-mono">No judge records matched your criteria.</p>
            </div>
        @else
            @foreach($judges as $judgeIndex => $judge)
                @php
                    $dutyPrograms = $judge->sorted_programs ?? $judge->programs;
                    $isLast = $loop->last;
                    $shouldBreak = ($mode === 'per_page') && ! $isLast;
                @endphp

                <div class="judge-sheet {{ $shouldBreak ? 'judge-sheet-break' : '' }} bg-white print:bg-white rounded-2xl print:rounded-none border print:border-none border-slate-200 p-6 sm:p-8 print:p-0 mb-8 print:mb-0 shadow-sm print:shadow-none">
                    
                    <!-- Official QUAF Header on Every Judge Page -->
                    @include('partials.print-pdf-header', [
                        'title' => 'OFFICIAL JUDGE DUTY ALLOCATION',
                        'subtitle' => 'QUAF 2026 Markaz Arts Festival — Stage & Offstage Evaluation Duty Roster',
                        'extraMeta' => [
                            'Judge' => $judge->name,
                            'Designation' => $judge->designation ?: 'Official Evaluator',
                            'PIN' => $judge->access_code ?: '—',
                            'Duties' => $dutyPrograms->count().' Competitions',
                        ],
                    ])

                    <!-- Judge Information Card Box -->
                    <div class="bg-slate-50 border border-slate-300 rounded-xl p-3 sm:p-4 mb-4 text-xs">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono">
                            <div>
                                <span class="text-[10px] text-slate-500 uppercase font-bold block">Judge Name</span>
                                <span class="font-bold text-slate-900 text-sm font-sans">{{ $judge->name }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 uppercase font-bold block">Access PIN</span>
                                <span class="font-bold text-[#be1e2d] text-sm">{{ $judge->access_code ?: '—' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 uppercase font-bold block">Mobile / Contact</span>
                                <span class="font-bold text-slate-800">{{ $judge->contact ?: '—' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 uppercase font-bold block">Specialization / Domain</span>
                                <span class="font-bold text-slate-800 truncate block">{{ $judge->specialization ?: 'General' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Programs Table for this Judge -->
                    @if($dutyPrograms->isEmpty())
                        <div class="border border-dashed border-slate-300 rounded-xl p-8 text-center text-xs text-slate-500 my-4">
                            ഈ ജഡ്ജിന് മത്സരങ്ങൾ ഒന്നും അസൈൻ ചെയ്തിട്ടില്ല (No competitions currently assigned).
                        </div>
                    @else
                        <table class="duty-table mb-4">
                            <thead>
                                <tr>
                                    <th style="width: 28px; text-align: center;">#</th>
                                    <th style="width: 70px;">Code</th>
                                    <th>Competition / Event Name</th>
                                    <th style="width: 80px;">Category</th>
                                    <th style="width: 110px;">Stage / Venue</th>
                                    <th style="width: 105px;">Scheduled Time</th>
                                    <th style="width: 45px; text-align: center;">Entries</th>
                                    <th style="width: 80px; text-align: center;">Sign-Off</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dutyPrograms as $pIdx => $prog)
                                    @php
                                        $progTime = $prog->scheduled_time ?? $prog->schedule?->start_time;
                                        if ($progTime && is_string($progTime)) {
                                            try {
                                                $progTime = \Carbon\Carbon::parse($progTime);
                                            } catch (\Throwable $e) {
                                                $progTime = null;
                                            }
                                        }
                                        $stageName = $prog->stage?->name ?? $prog->schedule?->stage?->name ?? 'Stage —';
                                    @endphp
                                    <tr style="background-color: {{ $pIdx % 2 === 0 ? '#ffffff' : '#f8fafc' }};">
                                        <td style="text-align: center; font-weight: bold; color: #64748b; font-size: 11px;">
                                            {{ $pIdx + 1 }}
                                        </td>
                                        <td style="font-family: monospace; font-weight: 700; font-size: 11px;">
                                            {{ $prog->code ?: 'PROG-'.$prog->id }}
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; font-size: 12px; color: #0f172a;">{{ $prog->name }}</div>
                                            @if($prog->malayalam_name)
                                                <div style="font-size: 11px; color: #475569; font-family: sans-serif;">{{ $prog->malayalam_name }}</div>
                                            @endif
                                        </td>
                                        <td style="font-size: 11px; color: #334155;">
                                            {{ $prog->category?->name ?? 'General' }}
                                        </td>
                                        <td style="font-weight: 700; font-size: 11px; color: #1e293b;">
                                            {{ $stageName }}
                                        </td>
                                        <td style="font-size: 11px;">
                                            @if($progTime)
                                                <div style="font-weight: 700; color: #0f172a;">{{ $progTime->format('d M (D)') }}</div>
                                                <div style="color: #be1e2d; font-weight: 700; font-size: 10px;">{{ $progTime->format('h:i A') }}</div>
                                            @else
                                                <span style="color: #94a3b8; font-style: italic;">TBD</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center; font-weight: 700; font-size: 11px;">
                                            {{ $prog->entries_count ?? $prog->entries()->count() }}
                                        </td>
                                        <td style="text-align: center; color: #cbd5e1; font-size: 10px;">
                                            [ &nbsp; &nbsp; &nbsp; &nbsp; ]
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <!-- Official Sign-off Box at the bottom of each page -->
                    <div class="mt-8 pt-4 border-t-2 border-slate-900 grid grid-cols-3 gap-4 text-center font-mono text-[10px] uppercase tracking-wider text-slate-700">
                        <div>
                            <div class="h-10"></div>
                            <div class="border-t border-slate-400 pt-1 font-bold">Program Committee Convener</div>
                        </div>
                        <div>
                            <div class="h-10"></div>
                            <div class="border-t border-slate-400 pt-1 font-bold">Festival General Secretary</div>
                        </div>
                        <div>
                            <div class="h-10"></div>
                            <div class="border-t border-slate-400 pt-1 font-bold">Judge Acceptance Signature</div>
                        </div>
                    </div>

                    <!-- Footer Note -->
                    <div class="mt-3 text-center text-[9px] font-mono text-slate-400">
                        QUAF 2026 Markaz Arts Festival — Official Evaluation Document — Generated on {{ now()->format('d M Y, h:i A') }}
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</body>
</html>
