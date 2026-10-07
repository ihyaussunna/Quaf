@extends('layouts.public', ['title' => 'Venue & Contact — QUAF'])

@section('content')

<!-- Header -->
<section class="py-14 sm:py-20 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">FESTIVAL INFORMATION & LOCATION</span>
            <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 mt-2">
                Venue & Contact
            </h1>
            <p class="text-base text-slate-600 mt-2 leading-relaxed">
                Reach the festival management committee and explore stage locations across the Jamia Markaz campus.
            </p>
        </div>
    </div>
</section>

<!-- Content Grid -->
<section class="py-14 sm:py-20 bg-slate-50 min-h-[60vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            
            <!-- Campus Venue Info -->
            <div class="rounded-3xl bg-white border border-slate-200 p-8 shadow-xs space-y-6">
                <span class="text-xs font-mono font-bold uppercase tracking-widest text-[#be1e2d]">CENTRAL FESTIVAL ARENA</span>
                <h2 class="text-2xl font-sora font-black text-slate-900">Jamia Markaz Campus</h2>
                <div class="space-y-3 font-mono text-xs text-slate-600 leading-relaxed">
                    <p><strong>Campus:</strong> Markazu Saquafathi Sunniyya</p>
                    <p><strong>Location:</strong> Karanthur, Kozhikode District, Kerala 673573, India</p>
                    <p><strong>Event Dates:</strong> 06 Oct — 01 Nov, 2026 (Offstage: Oct 06 | Main Stage: Oct 31 – Nov 01)</p>
                    <p><strong>Organized by:</strong> Ihyaussunna-Markaz Students' Union</p>
                </div>

                <div class="pt-4 border-t border-slate-100 space-y-3">
                    <h3 class="font-sora font-bold text-slate-900 text-sm">Active Festival Stages</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 font-mono text-xs">
                        @foreach($stages as $stg)
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <strong class="text-slate-900 font-bold block">{{ $stg->name }}</strong>
                                <span class="text-slate-500">{{ $stg->location }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Inquiries & Contact -->
            <div class="rounded-3xl bg-white border border-slate-200 p-8 shadow-xs space-y-6 flex flex-col justify-between">
                <div>
                    <span class="text-xs font-mono font-bold uppercase tracking-widest text-[#be1e2d]">FESTIVAL SECRETARIAT</span>
                    <h2 class="text-2xl font-sora font-black text-slate-900 mb-4">Official Helpdesk</h2>
                    
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-6">
                        For delegate accreditation, stage coordinators, jury verification, and press queries, contact the festival committee.
                    </p>

                    <div class="space-y-4 font-mono text-xs">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <span class="text-slate-500">General Secretariat:</span>
                            <strong class="text-slate-900">ISU Markaz Central Office</strong>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <span class="text-slate-500">Verification Engine:</span>
                            <strong class="text-emerald-700">Online 24/7 Verified</strong>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <span class="text-slate-500">Website:</span>
                            <strong class="text-slate-900">quaf.ihyaussunna.in</strong>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('home.view') }}" class="text-xs font-bold text-[#be1e2d] hover:underline">
                        ← Back to Homepage
                    </a>
                    <a href="{{ route('brochure.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition-colors shadow-2xs">
                        Read Brochure
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection
