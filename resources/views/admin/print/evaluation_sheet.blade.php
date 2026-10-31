<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Valuation Scorecard — {{ $program->name }} ({{ $program->code }}) | QUAF 09</title>
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
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
                padding: 6mm 8mm !important;
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
<body class="bg-slate-100 text-slate-900 font-sora antialiased min-h-screen py-6 px-3 sm:px-6">

    <!-- Top Action Control Bar (Hidden on Print) -->
    <div class="no-print max-w-5xl mx-auto mb-6 bg-white border border-slate-300 rounded-3xl p-5 shadow-lg space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-purple-700 text-white uppercase">
                        Jury Valuation Scorecard
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        {{ $program->code }}
                    </span>
                </div>
                <h1 class="text-xl font-black text-slate-900 mt-1 font-sora">
                    {{ $program->name }}
                    @if($program->malayalam_name)
                        <span class="text-slate-600 font-anek text-base font-semibold">({{ $program->malayalam_name }})</span>
                    @endif
                </h1>
                <p class="text-xs text-slate-500 font-mono mt-0.5">
                    മൂല്യനിർണ്ണയം കഴിഞ്ഞ മത്സരത്തിന്റെ ഔദ്യോഗിക ജൂറി സ്കോർകാർഡും മാർക്ക് ലിസ്റ്റും
                </p>
            </div>
            
            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.evaluation-monitor.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-mono font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition">
                    &larr; Evaluation Monitor
                </a>
                <a href="{{ route('admin.results.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-mono font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition">
                    Results Center
                </a>
                <button onclick="window.print()" class="px-5 py-2.5 rounded-xl text-xs font-mono font-black uppercase tracking-wider bg-[#be1e2d] hover:bg-[#a01624] text-white flex items-center gap-2 shadow-md transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print Scorecard / PDF</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Official Printable Document -->
    <div class="print-container max-w-5xl mx-auto bg-white border border-slate-300 rounded-3xl p-8 sm:p-10 shadow-xl space-y-6">
        
        <!-- Header Banner -->
        <div class="w-full pb-3 border-b border-slate-300 text-center">
            <img src="{{ asset('images/print-pdf-header.svg') }}" alt="QUAF Header" class="w-1/2 max-w-[50%] h-auto max-h-12 sm:max-h-14 object-contain block mx-auto" style="max-height: 55px; width: 50%; max-width: 50%;">
        </div>

        <!-- Document Letterhead -->
        <div class="border-b-2 border-slate-900 pb-4 flex items-center justify-between gap-4">
            <div>
                <span class="text-xs font-mono font-black tracking-widest text-purple-700 uppercase block">
                    QUAF SEASON 09 &bull; JURY EVALUATION &amp; SCORECARD
                </span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight font-sora">
                    OFFICIAL VALUATION REPORT
                </h1>
                <span class="text-xs text-slate-600 font-mono">
                    Board of Adjudicators &bull; Tabulation &amp; Scrutiny Directorate
                </span>
            </div>

            <div class="text-right shrink-0">
                <div class="inline-block px-3.5 py-1.5 rounded-xl bg-purple-900 text-white font-mono font-black text-xs uppercase tracking-wider">
                    VALUATION COMPLETED
                </div>
                <span class="text-[11px] font-mono text-slate-400 block mt-1">
                    Date: {{ now()->format('d M Y, h:i A') }}
                </span>
            </div>
        </div>

        <!-- Program Meta Box -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs font-mono">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Competition</span>
                <span class="font-black text-slate-900 text-sm font-sora">{{ $program->name }}</span>
                @if($program->malayalam_name)
                    <span class="text-slate-600 font-anek text-xs block">{{ $program->malayalam_name }}</span>
                @endif
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Program Code &amp; Zone</span>
                <span class="font-bold text-[#be1e2d]">{{ $program->code }}</span> &bull; 
                <span class="text-slate-800">{{ $program->zone?->name ?? $program->eligibility ?? 'All Zones' }}</span>
                <span class="text-slate-500 block text-[11px] capitalize">{{ $program->type }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Stage / Venue</span>
                <span class="font-bold text-slate-900">{{ $program->stage?->name ?? 'Unassigned' }}</span>
                <span class="text-slate-500 block text-[11px]">{{ $program->duration_minutes ? $program->duration_minutes . ' Mins' : '—' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Jury Panel</span>
                <span class="font-bold text-purple-900">{{ $judges->count() }} Judge(s)</span>
                <span class="text-slate-500 block text-[11px]">{{ $entries->where('attendance_status', 'present')->count() }} Evaluated Candidates</span>
            </div>
        </div>

        <!-- Assigned Judges Summary -->
        @if($judges->isNotEmpty())
            <div class="border border-slate-200 rounded-2xl p-3.5 bg-purple-50/30 text-xs">
                <div class="text-[11px] font-mono font-bold uppercase text-purple-900 mb-2 flex items-center justify-between">
                    <span>Assigned Jury Members (മൂല്യനിർണ്ണയം നടത്തിയ വിധികർത്താക്കൾ)</span>
                    <span class="text-slate-500 font-normal">Score sheets verified &amp; submitted</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    @foreach($judges as $idx => $j)
                        <div class="bg-white border border-purple-200 rounded-xl p-2.5 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-slate-900 block text-xs">Judge {{ $idx + 1 }}: {{ $j->name }}</span>
                                <span class="text-[10px] text-slate-500 font-mono">{{ $j->designation ?? 'Adjudicator' }} &bull; {{ $j->specialization ?? 'Jury' }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-800 font-mono font-bold text-[10px]">
                                #{{ $j->id }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Podium Verdict Podium Box -->
        <div class="avoid-break p-4 rounded-2xl bg-amber-50/50 border border-amber-200 space-y-3">
            <div class="flex items-center justify-between border-b border-amber-200/80 pb-2">
                <h3 class="font-black text-xs font-mono uppercase text-slate-900 tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span>Valuation Verdict &amp; Winners Podium (വിജയികൾ)</span>
                </h3>
                <span class="text-[10px] font-mono text-amber-900 font-bold">Rule 11 Matrix Compliant</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- 1st Place -->
                @php $first = $podium['first']; @endphp
                <div class="p-3 rounded-xl bg-white border border-amber-300 shadow-2xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="px-2 py-0.5 rounded font-bold font-mono text-[10px] bg-amber-400 text-slate-950 uppercase">1st Place</span>
                        <span class="font-mono font-bold text-amber-700 text-xs">{{ $posPoints[1] ?? 5 }} Pts</span>
                    </div>
                    @if($first)
                        <div class="font-bold text-slate-900 text-xs truncate">
                            {{ $first->student?->name ?? ($first->group?->name ?? 'Group') }}
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono">
                            #{{ $first->chest_number }} &bull; {{ $first->student?->group?->name ?? $first->group?->name }}
                        </div>
                        <div class="text-[10px] text-emerald-700 font-mono font-bold mt-1">
                            Score: {{ $first->computed_avg_score ?? '-' }} &bull; Grade: {{ $first->computed_grade ?? '-' }}
                        </div>
                    @else
                        <div class="text-xs text-slate-400 italic">No verdict declared</div>
                    @endif
                </div>

                <!-- 2nd Place -->
                @php $second = $podium['second']; @endphp
                <div class="p-3 rounded-xl bg-white border border-slate-300 shadow-2xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="px-2 py-0.5 rounded font-bold font-mono text-[10px] bg-slate-300 text-slate-900 uppercase">2nd Place</span>
                        <span class="font-mono font-bold text-slate-700 text-xs">{{ $posPoints[2] ?? 3 }} Pts</span>
                    </div>
                    @if($second)
                        <div class="font-bold text-slate-900 text-xs truncate">
                            {{ $second->student?->name ?? ($second->group?->name ?? 'Group') }}
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono">
                            #{{ $second->chest_number }} &bull; {{ $second->student?->group?->name ?? $second->group?->name }}
                        </div>
                        <div class="text-[10px] text-emerald-700 font-mono font-bold mt-1">
                            Score: {{ $second->computed_avg_score ?? '-' }} &bull; Grade: {{ $second->computed_grade ?? '-' }}
                        </div>
                    @else
                        <div class="text-xs text-slate-400 italic">No verdict declared</div>
                    @endif
                </div>

                <!-- 3rd Place -->
                @php $third = $podium['third']; @endphp
                <div class="p-3 rounded-xl bg-white border border-amber-300/80 shadow-2xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="px-2 py-0.5 rounded font-bold font-mono text-[10px] bg-amber-700 text-white uppercase">3rd Place</span>
                        <span class="font-mono font-bold text-amber-800 text-xs">{{ $posPoints[3] ?? 1 }} Pt</span>
                    </div>
                    @if($third)
                        <div class="font-bold text-slate-900 text-xs truncate">
                            {{ $third->student?->name ?? ($third->group?->name ?? 'Group') }}
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono">
                            #{{ $third->chest_number }} &bull; {{ $third->student?->group?->name ?? $third->group?->name }}
                        </div>
                        <div class="text-[10px] text-emerald-700 font-mono font-bold mt-1">
                            Score: {{ $third->computed_avg_score ?? '-' }} &bull; Grade: {{ $third->computed_grade ?? '-' }}
                        </div>
                    @else
                        <div class="text-xs text-slate-400 italic">No verdict declared</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Full Candidate Valuation Matrix Table -->
        <div class="rounded-2xl border border-slate-300 overflow-hidden shadow-2xs">
            <div class="bg-slate-900 text-white px-4 py-2.5 text-xs font-mono font-bold uppercase tracking-wider flex items-center justify-between">
                <span>Candidate Marks Breakdown &amp; Multi-Judge Audit (മാർക്ക് പട്ടിക)</span>
                <span class="text-slate-300 text-[11px]">Total Candidates: {{ $entries->count() }}</span>
            </div>

            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-100 text-slate-700 uppercase text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="px-3 py-2.5 text-center w-12">Rank</th>
                        <th class="px-3 py-2.5 text-center w-20">Code</th>
                        <th class="px-3 py-2.5 w-16">Chest</th>
                        <th class="px-3 py-2.5">Participant / Team</th>
                        <th class="px-3 py-2.5">House</th>
                        <th class="px-3 py-2.5 text-center w-20">Status</th>
                        @foreach($judges as $idx => $j)
                            <th class="px-3 py-2.5 text-center bg-purple-50 text-purple-900 font-bold" title="{{ $j->name }}">
                                J{{ $idx + 1 }}
                            </th>
                        @endforeach
                        <th class="px-3 py-2.5 text-center font-black bg-emerald-50 text-emerald-900">Average</th>
                        <th class="px-3 py-2.5 text-center w-16">Grade</th>
                        <th class="px-3 py-2.5 text-right font-black bg-amber-50 text-amber-950">Team Pts</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($entries as $entry)
                        @php
                            $isPresent = $entry->attendance_status === 'present';
                            $isTop1 = $entry->podium_rank === 1;
                            $isTop2 = $entry->podium_rank === 2;
                            $isTop3 = $entry->podium_rank === 3;
                        @endphp
                        <tr class="{{ $isTop1 ? 'bg-amber-50/70 font-bold' : ($isTop2 ? 'bg-slate-50 font-semibold' : ($isTop3 ? 'bg-amber-50/30' : (! $isPresent ? 'opacity-40 bg-red-50/20' : 'bg-white'))) }}">
                            <!-- Rank -->
                            <td class="px-3 py-2.5 text-center font-bold">
                                @if($isTop1)
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-400 text-slate-950 text-[11px] font-black">1</span>
                                @elseif($isTop2)
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-slate-300 text-slate-900 text-[11px] font-black">2</span>
                                @elseif($isTop3)
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-700 text-white text-[11px] font-black">3</span>
                                @elseif($isPresent && $entry->avg_score > 0)
                                    <span class="text-slate-400">{{ $loop->iteration }}</span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>

                            <!-- Code Letter -->
                            <td class="px-3 py-2.5 text-center font-bold text-purple-900">
                                {{ $entry->code_letter ? 'Code ' . $entry->code_letter : '—' }}
                            </td>

                            <!-- Chest Number -->
                            <td class="px-3 py-2.5 font-bold text-slate-900">
                                #{{ $entry->chest_number }}
                            </td>

                            <!-- Participant Name -->
                            <td class="px-3 py-2.5 font-bold text-slate-900 font-sora">
                                {{ $entry->student?->name ?? ($entry->group?->name ?? 'Candidate') }}
                            </td>

                            <!-- House -->
                            <td class="px-3 py-2.5 font-semibold text-slate-700">
                                {{ $entry->student?->group?->name ?? ($entry->group?->name ?? '—') }}
                            </td>

                            <!-- Attendance -->
                            <td class="px-3 py-2.5 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $isPresent ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $entry->attendance_status ?? 'waiting' }}
                                </span>
                            </td>

                            <!-- Judge Marks -->
                            @foreach($judges as $j)
                                @php
                                    $sheet = $entry->scores->where('judge_id', $j->id)->first();
                                    $submitted = $sheet && $sheet->is_submitted;
                                @endphp
                                <td class="px-3 py-2.5 text-center">
                                    @if(! $isPresent)
                                        <span class="text-slate-300 italic text-[10px]">Abs</span>
                                    @elseif($submitted)
                                        <span class="font-bold text-slate-900">{{ number_format($sheet->total_score, 1) }}</span>
                                    @else
                                        <span class="text-amber-500 font-bold text-[10px]">Pend</span>
                                    @endif
                                </td>
                            @endforeach

                            <!-- Average Score -->
                            <td class="px-3 py-2.5 text-center font-black bg-emerald-50/50 text-emerald-950">
                                {{ $isPresent && $entry->avg_score > 0 ? number_format($entry->avg_score, 2) : '—' }}
                            </td>

                            <!-- Grade -->
                            <td class="px-3 py-2.5 text-center font-bold">
                                @if($entry->computed_grade)
                                    <span class="px-1.5 py-0.5 rounded {{ in_array($entry->computed_grade, ['A+', 'A']) ? 'bg-emerald-100 text-emerald-900 font-black' : 'bg-slate-100 text-slate-800' }}">
                                        {{ $entry->computed_grade }}
                                    </span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>

                            <!-- Team Points -->
                            <td class="px-3 py-2.5 text-right font-black bg-amber-50/50 text-amber-950">
                                @if($isPresent && $entry->total_team_points > 0)
                                    <span>{{ $entry->total_team_points }} pts</span>
                                    <span class="text-[10px] text-slate-400 font-normal block font-mono">({{ $entry->position_points }} pos + {{ $entry->grade_points }} grd)</span>
                                @else
                                    <span class="text-slate-400">0</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 9 + $judges->count() }}" class="p-8 text-center text-slate-400 font-mono text-xs">
                                No participants registered for this program.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Official Signatures Block -->
        <div class="avoid-break pt-6 border-t-2 border-slate-900 grid grid-cols-3 gap-8 text-center font-mono">
            <div class="space-y-6">
                <div class="h-10"></div>
                <div class="border-t border-slate-400 pt-1.5">
                    <span class="font-bold text-xs text-slate-900 block">Jury Panel Adjudicators</span>
                    <span class="text-[10px] text-slate-500 block uppercase">Official Valuation Signatures</span>
                </div>
            </div>

            <div class="space-y-6">
                <div class="h-10"></div>
                <div class="border-t border-slate-400 pt-1.5">
                    <span class="font-bold text-xs text-slate-900 block">Chief Tabulator / Scrutiny</span>
                    <span class="text-[10px] text-slate-500 block uppercase">Results Tabulation Directorate</span>
                </div>
            </div>

            <div class="space-y-6">
                <div class="h-10"></div>
                <div class="border-t border-slate-400 pt-1.5">
                    <span class="font-bold text-xs text-slate-900 block">Controller of Examinations</span>
                    <span class="text-[10px] text-slate-500 block uppercase">Central Festival Directorate</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer Note -->
    <div class="no-print max-w-5xl mx-auto mt-6 text-center text-xs font-mono text-slate-400">
        QUAF 2026 Season 09 &bull; Inter-House Festival Valuation &amp; Scorecard Audit System
    </div>

</body>
</html>
