@extends('layouts.public', ['title' => 'Certificate Verification | QUAF'])

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-8">
        <span class="text-xs font-mono font-bold tracking-widest text-[#f3bd2e] uppercase">OFFICIAL AUTHENTICATION</span>
        <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 mt-2">Digital Certificate</h1>
    </div>

    <!-- Official Certificate Card with Luxury Border & Seal (Light Theme) -->
    <div class="rounded-3xl bg-white border-2 border-[#f3bd2e]/50 p-8 sm:p-12 relative overflow-hidden shadow-xl">
        <!-- Watermark Q9 -->
        <div class="absolute -right-16 -bottom-16 font-sora font-black text-[180px] text-slate-900/[0.03] pointer-events-none select-none">
            Q9
        </div>

        <!-- Verification Banner -->
        <div class="flex items-center justify-between pb-8 border-b border-slate-200 mb-8">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-300 flex items-center justify-center text-emerald-600 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <span class="text-xs font-mono font-bold tracking-wider text-emerald-700 uppercase block">AUTHENTIC & VERIFIED</span>
                    <span class="text-xs font-mono text-slate-500">ID: {{ $certificate->certificate_number }}</span>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-mono text-slate-400 block font-semibold">ISSUED ON</span>
                <span class="text-xs font-mono text-slate-700 font-bold">{{ $certificate->issued_at?->format('M d, Y') }}</span>
            </div>
        </div>

        <!-- Certificate Body -->
        <div class="text-center py-6 space-y-6">
            <span class="text-xs font-mono text-slate-400 uppercase tracking-widest block font-semibold">THIS IS TO CERTIFY THAT</span>
            <h2 class="text-3xl sm:text-4xl font-sora font-black text-slate-900 tracking-wide">
                {{ $certificate->student->name }}
            </h2>
            <div class="text-sm font-mono text-[#f3bd2e] font-semibold">
                Student ID: {{ $certificate->student->student_id }} • Group: {{ $certificate->student->group->name }}
            </div>

            <p class="text-sm sm:text-base text-slate-700 font-normal max-w-lg mx-auto leading-relaxed">
                has secured <span class="font-bold text-[#f3bd2e]">{{ $certificate->position }}</span> in the discipline of
                <br>
                <span class="font-sora font-bold text-lg text-slate-900 mt-1 inline-block">{{ $certificate->program->name }}</span>
                <br>
                <span class="text-xs text-slate-500">({{ $certificate->program->eligibility ?? 'A Zone' }})</span>
            </p>

            <p class="text-xs text-slate-500 italic">
                conducted under the auspices of Ihyaussunna Students Union, Markazu Saquafathi Sunniyya.
            </p>
        </div>

        <!-- Signatures & Official Footer -->
        <div class="mt-12 pt-8 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between text-center sm:text-left gap-6">
            <div>
                <span class="font-sora text-sm font-bold text-slate-900 block">Dr. Anas Al-Azhari</span>
                <span class="text-[11px] text-slate-500 font-mono">Central Jury Chairman</span>
            </div>
            <div class="w-16 h-16 rounded-full border-2 border-[#f3bd2e]/60 flex items-center justify-center p-1 bg-amber-50/50 shadow-xs">
                <div class="w-full h-full rounded-full border border-dashed border-[#f3bd2e] flex items-center justify-center text-[8px] font-mono font-bold text-[#f3bd2e] text-center uppercase tracking-tighter">
                    QUAF<br>SEAL
                </div>
            </div>
            <div class="text-center sm:text-right">
                <span class="font-sora text-sm font-bold text-slate-900 block">Festival Convenor</span>
                <span class="text-[11px] text-slate-500 font-mono">Ihyaussunna Students Union</span>
            </div>
        </div>
    </div>
</div>
@endsection
