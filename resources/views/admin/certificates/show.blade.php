@extends('layouts.admin', ['title' => 'Certificate: ' . $certificate->certificate_number])

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar (hidden on print) -->
    <div class="print:hidden flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-slate-200 p-4 rounded-2xl shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.certificates.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-mono font-semibold">
                &larr; Back to Certificates
            </a>
            <div>
                <h1 class="text-sm font-mono font-bold text-slate-900">{{ $certificate->certificate_number }}</h1>
                <p class="text-[11px] font-mono text-slate-500">Recipient: {{ $certificate->student->name }} ({{ $certificate->student->group->name }})</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('verify.certificate', $certificate->certificate_number) }}" target="_blank" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-mono font-semibold">
                Verify Link &nearr;
            </a>
            <button onclick="window.print()" class="px-5 py-2 bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase rounded-xl hover:brightness-110 flex items-center gap-2 shadow-lg shadow-[#f3bd2e]/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Official Certificate</span>
            </button>
        </div>
    </div>

    <!-- Certificate Render Area -->
    <div class="flex justify-center">
        <div id="certificate-print-area" class="w-full max-w-4xl bg-white text-slate-900 p-8 sm:p-12 rounded-3xl border-2 border-[#f3bd2e]/40 shadow-xl relative overflow-hidden print:p-8 print:m-0 print:border-none print:shadow-none print:max-w-none print:w-[297mm] print:h-[210mm] print:rounded-none">
            
            <!-- Geometric Watermark -->
            <div class="absolute inset-0 pointer-events-none opacity-[0.03] flex items-center justify-center">
                <svg class="w-[550px] h-[550px]" viewBox="0 0 100 100" fill="currentColor">
                    <polygon points="50,0 63,37 100,50 63,63 50,100 37,63 0,50 37,37" />
                    <circle cx="50" cy="50" r="45" stroke="currentColor" stroke-width="2" fill="none" />
                </svg>
            </div>

            <!-- Luxury Border Frame -->
            <div class="border border-[#f3bd2e]/40 p-8 sm:p-10 rounded-2xl relative">
                <!-- Corner Ornaments -->
                <div class="absolute top-2 left-2 w-8 h-8 border-t-2 border-l-2 border-[#f3bd2e]"></div>
                <div class="absolute top-2 right-2 w-8 h-8 border-t-2 border-r-2 border-[#f3bd2e]"></div>
                <div class="absolute bottom-2 left-2 w-8 h-8 border-b-2 border-l-2 border-[#f3bd2e]"></div>
                <div class="absolute bottom-2 right-2 w-8 h-8 border-b-2 border-r-2 border-[#f3bd2e]"></div>

                <!-- Header -->
                <div class="text-center space-y-2">
                    <p class="text-[11px] font-mono tracking-[0.3em] text-[#f3bd2e] uppercase font-bold">
                        IHYAUSSUNNA STUDENTS UNION • MARKAZU SAQUAFATHI SUNNIYYA
                    </p>
                    <h2 class="text-4xl sm:text-5xl font-sora font-black tracking-widest text-[#f3bd2e] uppercase">
                        QUAF '09
                    </h2>
                    <p class="text-xs font-mono tracking-widest text-slate-500 uppercase">
                        The Grand Cultural Conclave of Talents
                    </p>
                    
                    <div class="pt-6">
                        <span class="inline-block px-6 py-1.5 border-y border-[#f3bd2e]/50 font-sora text-lg tracking-widest uppercase text-slate-900 font-bold">
                            Certificate of Excellence
                        </span>
                    </div>
                </div>

                <!-- Certificate Body Text -->
                <div class="mt-8 text-center space-y-4 max-w-2xl mx-auto">
                    <p class="text-xs sm:text-sm font-sora italic text-slate-600">
                        This is proudly presented to
                    </p>
                    
                    <div class="py-2 border-b border-[#f3bd2e]/40 inline-block min-w-[280px]">
                        <h3 class="text-2xl sm:text-3xl font-sora font-bold text-slate-900 tracking-wide">
                            {{ $certificate->student->name }}
                        </h3>
                    </div>

                    <p class="text-xs sm:text-sm font-sora text-slate-700 leading-relaxed">
                        representing <span class="font-bold text-[#f3bd2e] font-sora">{{ $certificate->student->group->name }}</span>
                        for securing <span class="font-bold text-slate-900 font-sora uppercase tracking-wider px-2 py-0.5 bg-amber-50 border border-[#f3bd2e]/40 rounded">{{ $certificate->position }}</span> in the event
                    </p>

                    <div class="py-1">
                        <span class="text-xl sm:text-2xl font-sora font-bold text-[#f3bd2e] tracking-wider block">
                            {{ $certificate->program->name }}
                        </span>
                        <span class="text-[11px] font-mono text-slate-500">
                            Zone: {{ $certificate->program->eligibility ?? 'A Zone' }}
                        </span>
                    </div>

                    <p class="text-xs font-mono text-slate-500 pt-2">
                        held as part of the Grand Conclave on {{ $certificate->issued_at?->format('F d, Y') ?? date('F d, Y') }}.
                    </p>
                </div>

                <!-- Footer: Signatures & QR Authentication -->
                <div class="mt-12 pt-8 border-t border-slate-200 grid grid-cols-3 items-end text-center">
                    <!-- General Convener -->
                    <div class="space-y-2">
                        <div class="h-10 flex items-end justify-center">
                            <span class="font-sora italic text-base text-[#f3bd2e]">Anas Al-Azhari</span>
                        </div>
                        <div class="w-32 mx-auto border-t border-slate-300 pt-1">
                            <p class="text-[10px] font-mono text-slate-900 font-semibold uppercase">General Convener</p>
                            <p class="text-[9px] font-mono text-slate-500">QUAF Directorate</p>
                        </div>
                    </div>

                    <!-- Seal & QR Code -->
                    <div class="flex flex-col items-center justify-center space-y-2">
                        <div class="w-20 h-20 bg-white p-1 rounded-xl border border-slate-200 shadow-md flex items-center justify-center">
                            {!! $qrCodeSvg !!}
                        </div>
                        <div class="text-[9px] font-mono text-[#f3bd2e] tracking-wider font-semibold">
                            SECURE VERIFIED ID<br>
                            <span class="text-slate-500 font-normal">{{ $certificate->certificate_number }}</span>
                        </div>
                    </div>

                    <!-- General Secretary -->
                    <div class="space-y-2">
                        <div class="h-10 flex items-end justify-center">
                            <span class="font-sora italic text-base text-[#f3bd2e]">Sayyid Munawwar</span>
                        </div>
                        <div class="w-32 mx-auto border-t border-slate-300 pt-1">
                            <p class="text-[10px] font-mono text-slate-900 font-semibold uppercase">General Secretary</p>
                            <p class="text-[9px] font-mono text-slate-500">Ihyaussunna Students Union</p>
                        </div>
                    </div>
                </div>

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
    aside, header, nav, .print\:hidden {
        display: none !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
    }
    #certificate-print-area {
        background: white !important;
        color: black !important;
        border: 4px solid #8c6d1d !important;
        box-shadow: none !important;
        width: 100% !important;
        max-width: 100% !important;
        height: 100vh !important;
    }
    #certificate-print-area * {
        color: black !important;
    }
    #certificate-print-area h2, #certificate-print-area h3, #certificate-print-area .text-\[\#f3bd2e\] {
        color: #8c6d1d !important;
    }
}
</style>
@endsection
