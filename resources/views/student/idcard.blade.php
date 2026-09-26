@extends('layouts.student', ['title' => 'My Digital QR Badge'])

@section('content')
<div class="space-y-6 max-w-xl mx-auto">
    <!-- Action Bar -->
    <div class="print:hidden flex items-center justify-between gap-4 bg-white border border-slate-200 p-4 rounded-2xl shadow-sm">
        <a href="{{ route('student.dashboard') }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-mono">
            &larr; Dashboard
        </a>
        <button onclick="window.print()" class="px-5 py-2 bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase rounded-xl hover:brightness-110 flex items-center gap-2 shadow-lg shadow-[#f3bd2e]/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            <span>Print / Save Badge</span>
        </button>
    </div>

    <!-- Official Student Badge Card -->
    <div class="flex justify-center">
        <div id="badge-card" class="w-[340px] bg-white text-slate-900 rounded-3xl border-2 border-[#f3bd2e]/40 shadow-xl overflow-hidden relative print:border-2 print:border-black print:text-black print:bg-white">
            <!-- Header Stripe -->
            <div class="p-4 text-center border-b border-slate-100 bg-gradient-to-b from-amber-50/60 to-white print:from-gray-100 print:to-white">
                <div class="w-8 h-8 mx-auto rounded-lg bg-[#f3bd2e] text-white flex items-center justify-center font-serif font-black text-xs mb-1.5 shadow-md">
                    Q9
                </div>
                <p class="text-[9px] font-mono tracking-[0.2em] text-[#f3bd2e] font-bold uppercase">
                    IHYAUSSUNNA STUDENTS UNION
                </p>
                <h2 class="text-base font-serif font-black tracking-widest text-slate-900 mt-0.5 print:text-black">
                    QUAF SEASON 09
                </h2>
                <p class="text-[9px] font-mono text-slate-500 uppercase">
                    Official Participant Pass
                </p>
            </div>

            <!-- Student Details -->
            <div class="p-6 text-center space-y-4">
                <div class="relative inline-block">
                    <div class="w-28 h-28 mx-auto rounded-2xl bg-slate-50 border-2 border-[#f3bd2e] flex items-center justify-center font-serif text-4xl font-bold text-[#f3bd2e] overflow-hidden shadow-md">
                        @if($student->photo_path)
                            <img src="{{ asset('storage/' . $student->photo_path) }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                        @else
                            {{ substr($student->name, 0, 1) }}
                        @endif
                    </div>
                    <div class="absolute -bottom-2.5 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-[#f3bd2e] text-white font-mono font-bold text-[10px] uppercase shadow-md whitespace-nowrap">
                        CHEST #{{ $student->chest_number ?? '---' }}
                    </div>
                </div>

                <div class="pt-2">
                    <h3 class="text-xl font-serif font-bold text-slate-900 tracking-wide print:text-black">
                        {{ $student->name }}
                    </h3>
                    <p class="text-xs font-mono text-slate-500 mt-0.5">ID: {{ $student->student_id }}</p>
                </div>

                <!-- House Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-slate-200" style="background-color: {{ $student->group->color_hex }}15;">
                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $student->group->color_hex }};"></span>
                    <span class="text-xs font-mono font-bold uppercase tracking-wider" style="color: {{ $student->group->color_hex }};">
                        {{ $student->group->name }}
                    </span>
                    <span class="text-slate-400">•</span>
                    <span class="text-xs font-mono text-slate-600 print:text-black">{{ $student->category }}</span>
                </div>

                <!-- QR Code Block -->
                <div class="pt-4 border-t border-slate-100">
                    <div class="w-40 h-40 mx-auto bg-white p-2 rounded-2xl border border-slate-200 shadow-inner flex items-center justify-center">
                        {!! $qrCodeSvg !!}
                    </div>
                    <p class="text-[9px] font-mono text-slate-500 mt-2 uppercase tracking-widest">
                        Show this pass at green room check-in
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-3 bg-slate-50 border-t border-slate-100 text-center text-[9px] font-mono text-slate-500 print:bg-white print:border-t-2">
                Markazu Saquafathi Sunniyya • Karanthur
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body {
        background: white !important;
        color: black !important;
    }
    header, nav, footer, .print\:hidden {
        display: none !important;
    }
    #badge-card {
        box-shadow: none !important;
        margin: 20px auto !important;
    }
}
</style>
@endsection
