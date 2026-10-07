<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch ID Cards Print | QUAF</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
            .badge-card {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sora p-6 min-h-screen">

    <!-- Action Bar -->
    <div class="no-print max-w-5xl mx-auto mb-6 p-4 bg-white border border-slate-200 rounded-2xl flex items-center justify-between shadow-sm">
        <div>
            <h1 class="font-sora font-bold text-lg text-slate-900">Batch ID Badges Print ({{ $students->count() }} Cards)</h1>
            <p class="text-xs font-mono text-slate-500">Formatted for standard A4 sheets (6 cards per page).</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase rounded-xl hover:brightness-110 flex items-center gap-2 shadow-lg shadow-[#f3bd2e]/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Badges Now</span>
            </button>
            <button onclick="window.close()" class="px-4 py-2.5 bg-slate-100 text-slate-700 font-mono text-xs rounded-xl hover:bg-slate-200 font-semibold">
                Close
            </button>
        </div>
    </div>

    <!-- Cards Grid Container (2 Columns on A4) -->
    <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 print:grid-cols-2 print:gap-4">
        @foreach($students as $student)
            <div class="badge-card bg-white text-slate-900 rounded-2xl border-2 border-[#f3bd2e]/50 p-4 flex flex-col justify-between shadow-sm overflow-hidden h-[330px]">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded bg-[#f3bd2e] text-white flex items-center justify-center font-sora font-black text-[10px]">
                            Q9
                        </div>
                        <div>
                            <h4 class="font-sora font-black text-xs uppercase tracking-wider text-slate-900">QUAF</h4>
                            <p class="text-[7px] font-mono text-slate-500 uppercase">Ihyaussunna • Markaz</p>
                        </div>
                    </div>
                    <div class="px-2 py-0.5 rounded font-mono font-bold text-[10px] text-white" style="background-color: {{ $student->group->color_hex ?? '#f3bd2e' }}">
                        {{ $student->group->name }}
                    </div>
                </div>

                <!-- Body -->
                <div class="flex items-center gap-4 py-2">
                    <!-- Photo / Initial -->
                    <div class="w-20 h-20 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center font-sora text-2xl font-bold text-[#f3bd2e] overflow-hidden flex-shrink-0">
                        @if($student->photo_path)
                            <img src="{{ asset('storage/' . $student->photo_path) }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                        @else
                            {{ substr($student->name, 0, 1) }}
                        @endif
                    </div>

                    <!-- Details -->
                    <div class="flex-1 min-w-0">
                        <div class="inline-block px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-[9px] font-mono font-bold text-slate-800">
                            CHEST #{{ $student->chest_number ?? '---' }}
                        </div>
                        <h3 class="text-base font-sora font-bold text-slate-900 truncate mt-1">
                            {{ $student->name }}
                        </h3>
                        <p class="text-[10px] font-mono text-slate-500">ID: {{ $student->student_id }}</p>
                        <p class="text-[10px] font-mono text-slate-700 font-semibold">{{ $student->category }}</p>
                    </div>

                    <!-- QR Code -->
                    <div class="w-20 h-20 bg-white p-1 border border-slate-200 rounded-lg flex items-center justify-center flex-shrink-0 shadow-inner">
                        {!! \App\Services\QrCodeService::svg(route('verify.student', $student->qr_token), 72) !!}
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-slate-100 pt-2 flex items-center justify-between text-[8px] font-mono text-slate-500">
                    <span>AUTHENTICATED FESTIVAL PASS</span>
                    <span>OCT 2026 • KARANTHUR</span>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
