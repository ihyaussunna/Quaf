<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Niyamavali - {{ $program->code }} - {{ $program->name }} | QUAF Fest 09</title>

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
                Print Preview
            </span>
            <span class="text-xs font-mono text-slate-300">
                Official Niyamavali Sheet • {{ $program->code }}
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-amber-400 hover:bg-amber-500 text-slate-950 font-mono font-bold text-xs shadow-sm transition">
                Print Sheet
            </button>
            <button onclick="window.close()" class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-mono text-xs transition">
                Close
            </button>
        </div>
    </div>

    <!-- Official Document Container (Paper) -->
    <div class="border-2 border-slate-900 p-8 sm:p-10 rounded-2xl space-y-6">
        
        <!-- Header with Official Logo -->
        <div class="border-b-2 border-slate-900 pb-6 flex items-center justify-between gap-6">
            <div class="space-y-1">
                <div class="text-[10px] font-mono uppercase tracking-widest text-slate-500 font-bold">
                    Markaz Cultural Festival • Season 09
                </div>
                <h1 class="text-2xl font-serif font-black tracking-tight text-slate-900 uppercase">
                    QUAF FESTIVAL 2026
                </h1>
                <p class="text-xs font-malayalam font-bold text-slate-700">
                    Program Committee • Official Competition Rules
                </p>
            </div>

            <div class="text-right flex-shrink-0">
                <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF Logo" class="h-16 w-auto object-contain ml-auto">
            </div>
        </div>

        <!-- Program Overview Badge Strip -->
        <div class="bg-slate-50 border border-slate-300 rounded-xl p-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs font-mono">
            <div>
                <span class="text-[10px] uppercase text-slate-400 font-bold block">Program Code</span>
                <strong class="text-base font-bold text-slate-900">{{ $program->code }}</strong>
            </div>
            <div>
                <span class="text-[10px] uppercase text-slate-400 font-bold block">Zone</span>
                <strong class="text-sm font-bold text-slate-900">{{ $program->zone?->name ?? $program->eligibility }}</strong>
            </div>
            <div>
                <span class="text-[10px] uppercase text-slate-400 font-bold block">Type & Limit</span>
                <strong class="text-sm font-bold text-slate-900 capitalize">
                    {{ $program->type }} {{ $program->type === 'group' ? '(' . ($program->participant_count ?? $program->max_participants) . ')' : '' }}
                </strong>
            </div>
            <div>
                <span class="text-[10px] uppercase text-slate-400 font-bold block">Time Duration</span>
                <strong class="text-sm font-bold text-slate-900">{{ $program->duration_minutes }} Minutes</strong>
            </div>
        </div>

        <!-- Program Title -->
        <div class="text-center py-2 border-b border-slate-200">
            <h2 class="text-2xl font-serif font-black text-slate-900">{{ $program->name }}</h2>
            @if($program->malayalam_name)
                <h3 class="text-xl font-malayalam font-bold text-slate-700 mt-1">{{ $program->malayalam_name }}</h3>
            @endif
        </div>

        <!-- Section: Rules & Guidelines -->
        <div class="space-y-2">
            <h4 class="text-xs font-mono uppercase tracking-wider text-slate-900 font-bold border-b border-slate-200 pb-1">
                Rules & Regulations
            </h4>
            <div class="text-xs leading-relaxed text-slate-800 font-sans whitespace-pre-line p-4 rounded-xl bg-slate-50/70 border border-slate-200">
{{ $program->rules ?: 'Please strictly adhere to official competition guidelines and allocated timings.' }}
            </div>
        </div>

        <!-- Section: Scoring Criteria -->
        @if($program->scoringCriteria->count() > 0)
            <div class="space-y-2">
                <h4 class="text-xs font-mono uppercase tracking-wider text-slate-900 font-bold border-b border-slate-200 pb-1 flex justify-between">
                    <span>Evaluation Criteria</span>
                    <span>Total: {{ $program->scoringCriteria->sum('max_marks') }} Marks</span>
                </h4>
                <table class="w-full text-left text-xs font-mono border border-slate-300">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-300 text-slate-600 uppercase">
                            <th class="p-2 border-r border-slate-300 w-12 text-center">No</th>
                            <th class="p-2 border-r border-slate-300 font-sans">Criterion</th>
                            <th class="p-2 text-right w-24">Max Marks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($program->scoringCriteria as $i => $crit)
                            <tr>
                                <td class="p-2 border-r border-slate-200 text-center font-bold text-slate-500">{{ $i + 1 }}</td>
                                <td class="p-2 border-r border-slate-200 font-sans font-bold text-slate-900">{{ $crit->criterion_name }}</td>
                                <td class="p-2 text-right font-bold text-slate-900">{{ $crit->max_marks }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Footer / Signature Blocks -->
        <div class="pt-8 border-t-2 border-slate-900 flex items-center justify-between text-xs font-mono">
            <div>
                <span class="text-[10px] text-slate-400 uppercase block">Generated on</span>
                <span>{{ now()->format('d M Y, h:i A') }}</span>
            </div>

            <div class="text-center">
                <div class="h-10"></div>
                <div class="border-t border-slate-400 pt-1 font-bold">
                    Program Committee Convener
                </div>
            </div>

            <div class="text-center">
                <div class="h-10"></div>
                <div class="border-t border-slate-400 pt-1 font-bold">
                    General Convener / Chairman
                </div>
            </div>
        </div>

    </div>

</body>
</html>
