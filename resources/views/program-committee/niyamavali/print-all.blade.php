<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QUAF 09 - Official Competition Rules Booklet</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gayathri:wght@400;700&family=JetBrains+Mono:wght@400;600;700&family=Manjari:wght@400;700&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body {
            font-family: 'Sora', 'Manjari', sans-serif;
            background: #ffffff;
            color: #0f172a;
        }
        .font-malayalam {
            font-family: 'Manjari', 'Gayathri', sans-serif;
        }
        .page-break {
            page-break-after: always;
            break-after: page;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-10 max-w-4xl mx-auto text-slate-900">

    <!-- Top Action Bar (Screen Only) -->
    <div class="no-print mb-8 p-4 rounded-2xl bg-slate-900 text-white flex items-center justify-between shadow-lg">
        <div class="flex items-center gap-3">
            <span class="px-2.5 py-1 rounded bg-amber-400 text-slate-950 font-mono font-bold text-xs">
                Official Booklet
            </span>
            <span class="text-xs font-mono text-slate-300">
                QUAF 09 Festival Rules Booklet • Total {{ $programs->count() }} Competitions
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-amber-400 hover:bg-amber-500 text-slate-950 font-mono font-bold text-xs shadow-sm transition">
                Print Complete Booklet
            </button>
            <button onclick="window.close()" class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-mono text-xs transition">
                Close
            </button>
        </div>
    </div>

    <!-- COVER PAGE -->
    <div class="border-4 border-slate-900 p-12 rounded-3xl min-h-[90vh] flex flex-col justify-between text-center page-break">
        <div class="pt-8">
            <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF Logo" class="h-24 w-auto object-contain mx-auto mb-6">
            <div class="text-xs font-mono uppercase tracking-widest text-slate-500 font-bold mb-2">
                Ihyaussunna Students Union • Jamia Markaz
            </div>
            <h1 class="text-4xl font-sora font-black tracking-tight text-slate-900 uppercase">
                COMPETITION RULES & REGULATIONS
            </h1>
            <h2 class="text-2xl font-malayalam font-bold text-slate-700 mt-2">
                Official Rules Booklet
            </h2>
            @if($zone)
                <div class="mt-4 inline-block px-4 py-1.5 rounded-full bg-slate-100 font-mono text-sm font-bold border border-slate-300">
                    {{ $zone->name }}
                </div>
            @endif
        </div>

        <div class="py-12 space-y-3 font-mono text-xs text-slate-600">
            <p class="font-bold text-sm text-slate-900">Program Committee</p>
            <p>IHYAUSSUNNA STUDENTS UNION (ISU)</p>
            <p>MARKAZU SAQUAFATHI SUNNIYYA, KARANTHUR</p>
        </div>

        <div class="border-t border-slate-300 pt-6 flex items-center justify-between text-xs font-mono text-slate-400">
            <span>QUAF Fest 09</span>
            <span>Published: {{ now()->format('F Y') }}</span>
        </div>
    </div>

    <!-- PROGRAM PAGES -->
    @foreach($programs as $index => $program)
        <div class="border-2 border-slate-900 p-8 rounded-2xl space-y-6 {{ !$loop->last ? 'page-break' : '' }} mt-8">
            <!-- Header -->
            <div class="border-b-2 border-slate-900 pb-4 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-mono text-slate-400 uppercase">Program #{{ $index + 1 }}</span>
                    <h2 class="text-2xl font-sora font-black text-slate-900">{{ $program->name }}</h2>
                    @if($program->malayalam_name)
                        <h3 class="text-lg font-malayalam font-bold text-slate-700">{{ $program->malayalam_name }}</h3>
                    @endif
                </div>
                <div class="text-right font-mono">
                    <span class="px-3 py-1 rounded bg-slate-900 text-white font-bold text-sm block mb-1">
                        {{ $program->code }}
                    </span>
                    <span class="text-xs text-slate-600 font-semibold">{{ $program->zone?->name ?? $program->eligibility }}</span>
                </div>
            </div>

            <!-- Details Strip -->
            <div class="grid grid-cols-3 gap-3 bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-mono">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase block">Zone & Type</span>
                    <strong>{{ $program->zone?->name ?? $program->eligibility ?? 'General' }} • {{ ucfirst($program->type) }}</strong>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase block">Duration</span>
                    <strong>{{ $program->duration_minutes }} Minutes</strong>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase block">Weightage</span>
                    <strong>{{ $program->points_weight }} Points</strong>
                </div>
            </div>

            <!-- Rules -->
            <div class="space-y-2">
                <h4 class="text-xs font-mono uppercase tracking-wider text-slate-900 font-bold border-b border-slate-200 pb-1">
                    Official Competition Rules
                </h4>
                <div class="text-xs leading-relaxed text-slate-800 font-sora whitespace-pre-line p-4 rounded-xl bg-slate-50/70 border border-slate-200">
{{ $program->rules ?: 'Adhere to general competition rules and schedule.' }}
                </div>
            </div>

            <!-- Scoring Criteria -->
            @if($program->scoringCriteria->count() > 0)
                <div class="space-y-2">
                    <h4 class="text-xs font-mono uppercase tracking-wider text-slate-900 font-bold border-b border-slate-200 pb-1 flex justify-between">
                        <span>Scoring Rubric & Evaluation Criteria</span>
                        <span>Total: {{ $program->scoringCriteria->sum('max_marks') }} Marks</span>
                    </h4>
                    <table class="w-full text-left text-xs font-mono border border-slate-300">
                        <thead>
                            <tr class="bg-slate-100 border-b border-slate-300 text-slate-600">
                                <th class="p-2 border-r border-slate-300 w-12 text-center">No</th>
                                <th class="p-2 border-r border-slate-300 font-sora">Criterion</th>
                                <th class="p-2 text-right w-24">Max Marks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($program->scoringCriteria as $i => $crit)
                                <tr>
                                    <td class="p-2 border-r border-slate-200 text-center font-bold text-slate-500">{{ $i + 1 }}</td>
                                    <td class="p-2 border-r border-slate-200 font-sora font-bold text-slate-900">{{ $crit->criterion_name }}</td>
                                    <td class="p-2 text-right font-bold text-slate-900">{{ $crit->max_marks }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endforeach

</body>
</html>
