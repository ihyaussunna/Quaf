<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contestant Chest Number Slips | FestPro | QUAF 09</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }
        @media print {
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .slip-card {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans p-6 min-h-screen">

    <!-- Action & Filter Bar (Hidden on Print) -->
    <div class="no-print max-w-5xl mx-auto mb-6 p-4 bg-white border border-slate-200 rounded-2xl shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-amber-100 text-[#f3bd2e] border border-amber-200">
                        FestPro Operational Feature
                    </span>
                    <span class="text-xs font-mono text-slate-500">• {{ $students->count() }} Contestant Slips</span>
                </div>
                <h1 class="font-serif font-black text-xl text-slate-900 mt-1">Printable Chest Number Slips</h1>
                <p class="text-xs font-mono text-slate-500">Standard A4 grid with high-visibility chest badges for contestant chest pinning & green room check-in.</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="px-5 py-2.5 bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase rounded-xl hover:brightness-110 flex items-center gap-2 shadow-lg shadow-[#f3bd2e]/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Print All Slips</span>
                </button>
                <button onclick="window.close()" class="px-4 py-2.5 bg-slate-100 text-slate-700 font-mono text-xs rounded-xl hover:bg-slate-200 font-semibold">
                    Close
                </button>
            </div>
        </div>

        <!-- Filter form -->
        <form method="GET" action="{{ route('admin.idcards.chest-slips') }}" class="flex flex-wrap items-center gap-3 pt-3 border-t border-slate-100 text-xs font-mono">
            <span class="text-slate-500 font-semibold">Filter:</span>
            <select name="group" class="bg-slate-50 border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-800">
                <option value="">All Houses</option>
                @foreach($groups as $g)
                    <option value="{{ $g->id }}" {{ $groupId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                @endforeach
            </select>
            <select name="category" class="bg-slate-50 border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-800">
                <option value="">All Zones</option>
                @foreach($zones as $val => $label)
                    <option value="{{ $val }}" {{ $category == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-lg hover:bg-slate-900 font-semibold">Apply</button>
            <a href="{{ route('admin.idcards.chest-slips') }}" class="text-slate-500 hover:text-slate-800 underline">Reset</a>
        </form>
    </div>

    <!-- Slips Container (Grid: 2 Columns on A4, 4-6 slips per page) -->
    <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-4 print:grid-cols-2 print:gap-3">
        @forelse($students as $student)
            <div class="slip-card bg-white text-slate-900 rounded-2xl border-2 border-dashed border-slate-300 print:border-black p-4 flex flex-col justify-between shadow-sm relative overflow-hidden h-[240px]">
                
                <!-- Cut-line Guideline indicator -->
                <div class="no-print absolute top-1 right-2 text-[8px] font-mono text-slate-400">✂ Cut Along Border</div>

                <!-- Top Header -->
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-[#f3bd2e] text-white flex items-center justify-center font-serif font-black text-xs">
                            Q9
                        </div>
                        <div>
                            <h3 class="font-serif font-black text-xs uppercase tracking-wider text-slate-900">QUAF SEASON 09</h3>
                            <p class="text-[8px] font-mono text-slate-500 uppercase">Ihyaussunna • Markaz</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold text-white shadow-sm" style="background-color: {{ $student->group->color_hex ?? '#f3bd2e' }};">
                            {{ $student->group->name }}
                        </span>
                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[9px] font-mono font-bold">
                            {{ $student->category }}
                        </span>
                    </div>
                </div>

                <!-- Middle: Huge Chest Number & Details -->
                <div class="flex items-center justify-between py-2">
                    <!-- Left: Large Chest Number Badge -->
                    <div class="flex flex-col items-center justify-center bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 min-w-[120px] text-center">
                        <span class="text-[9px] font-mono font-bold text-slate-500 uppercase tracking-widest">CHEST NO.</span>
                        <span class="font-mono font-black text-4xl text-slate-900 tracking-tight">
                            {{ $student->chest_number ?? '---' }}
                        </span>
                    </div>

                    <!-- Center: Student Name & Reg Info -->
                    <div class="flex-1 px-4 min-w-0">
                        <h4 class="font-serif font-bold text-base text-slate-900 truncate">
                            {{ $student->name }}
                        </h4>
                        <p class="text-[10px] font-mono text-slate-500">ID: {{ $student->student_id }}</p>
                        @if($student->entries->isNotEmpty())
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach($student->entries->take(2) as $entry)
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[8px] font-mono text-slate-600 truncate max-w-[140px]">
                                        {{ $entry->program->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Right: QR Code for Verification -->
                    <div class="w-20 h-20 bg-white p-1 border border-slate-200 rounded-xl flex items-center justify-center flex-shrink-0 shadow-inner">
                        {!! \App\Services\QrCodeService::svg(route('verify.student', $student->qr_token), 72) !!}
                    </div>
                </div>

                <!-- Bottom Footer -->
                <div class="border-t border-slate-100 pt-2 flex items-center justify-between text-[8px] font-mono text-slate-500">
                    <span>PIN ON CHEST • PRESENT AT GREEN ROOM</span>
                    <span>OCT 2026 • KARANTHUR</span>
                </div>
            </div>
        @empty
            <div class="col-span-2 py-16 text-center text-slate-500 font-mono text-xs bg-white rounded-2xl border border-slate-200">
                No students found for chest number slips.
            </div>
        @endforelse
    </div>

</body>
</html>
