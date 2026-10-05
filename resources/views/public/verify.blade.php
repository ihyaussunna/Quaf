@extends('layouts.public', ['title' => 'Official QR Verification Hub — QUAF 9.0'])

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">
    
    <!-- Header -->
    <div class="text-center mb-10 sm:mb-14">
        <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">SECURITY & ACCREDITATION</span>
        <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 mt-2">QR Verification Hub</h1>
        <p class="text-xs sm:text-sm text-slate-600 mt-2 max-w-xl mx-auto leading-relaxed">
            Verify official credentials issued by Ihyaussunna Students Union, Markazu Saquafathi Sunniyya for QUAF 9.0.
        </p>
    </div>

    <!-- Verification Choice Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8 mb-12">
        
        <!-- Card 1: Certificate Verification -->
        <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 shadow-2xs hover:shadow-md transition-shadow flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center mb-4 border border-amber-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                </div>
                <h2 class="text-xl font-sora font-black text-slate-900 mb-2">Verify Certificate</h2>
                <p class="text-xs text-slate-600 leading-relaxed mb-6">
                    Validate awards, participant diplomas, and merit certificates issued to festival delegates.
                </p>

                <form onsubmit="event.preventDefault(); const id = document.getElementById('certInput').value.trim(); if(id) window.location.href = '{{ url('/verify/certificate') }}/' + encodeURIComponent(id);" class="space-y-3">
                    <input type="text" id="certInput" placeholder="Certificate Number (e.g. Q9-CERT-101)" required
                           class="w-full text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/20 focus:border-[#be1e2d]">
                    <button type="submit" class="w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition-colors shadow-2xs">
                        Validate Certificate
                    </button>
                </form>
            </div>
        </div>

        <!-- Card 2: Student Delegate ID Verification -->
        <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 shadow-2xs hover:shadow-md transition-shadow flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#005c94] flex items-center justify-center mb-4 border border-blue-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                </div>
                <h2 class="text-xl font-sora font-black text-slate-900 mb-2">Student Accreditation</h2>
                <p class="text-xs text-slate-600 leading-relaxed mb-6">
                    Verify registered student credentials, house affiliation, chest number, and allocated program roster.
                </p>

                <form onsubmit="event.preventDefault(); const token = document.getElementById('studentInput').value.trim(); if(token) window.location.href = '{{ url('/verify/student') }}/' + encodeURIComponent(token);" class="space-y-3">
                    <input type="text" id="studentInput" placeholder="Student ID or QR Token" required
                           class="w-full text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/20 focus:border-[#be1e2d]">
                    <button type="submit" class="w-full py-3 rounded-xl bg-[#be1e2d] hover:bg-[#991522] text-white font-bold text-xs uppercase tracking-wider transition-colors shadow-2xs">
                        Verify Student Pass
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- Security Notice -->
    <div class="p-6 rounded-2xl bg-slate-100 border border-slate-200 text-center font-mono text-xs text-slate-500">
        All QR credentials generated by the QUAF system contain tamper-resistant cryptographic hashes linked to the central festival database.
    </div>

</div>
@endsection
