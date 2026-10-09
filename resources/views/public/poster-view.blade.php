@extends('layouts.public')

@section('title', $result->program->name . ' - Result Poster')

@push('styles')
<style>
@media print {
    body {
        background: #0f172a !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    header, footer, nav, .no-print, #shareBar, .breadcrumb-bar {
        display: none !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
    }
    #posterCardWrapper {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }
    #posterPrintArea {
        box-shadow: none !important;
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 auto !important;
    }
}
</style>
@endpush

@section('content')
<section class="py-10 sm:py-16 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        
        <!-- Breadcrumb & Back -->
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('results.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white px-3.5 py-2 rounded-xl border border-slate-200 transition-colors shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back to Results</span>
            </a>

            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-900 text-white">
                    {{ $result->program->code }}
                </span>
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Official Result
                </span>
            </div>
        </div>

        <!-- Poster Showcase Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
            <!-- Program Header Banner -->
            <div class="p-6 sm:p-8 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white border-b border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="text-xs font-mono text-amber-400 font-bold uppercase tracking-widest">
                        QUAF &bull; {{ $result->program->zone?->name ?? $result->program->eligibility ?? 'Official Event' }}
                    </div>
                    <h1 class="text-xl sm:text-3xl font-black mt-1 font-sora tracking-tight">
                        {{ $result->program->name }}
                    </h1>
                    @if($result->program->malayalam_name)
                        <div class="text-sm text-slate-300 font-ml mt-0.5">{{ $result->program->malayalam_name }}</div>
                    @endif
                </div>

                @if($result->program->stage)
                    <div class="text-left sm:text-right shrink-0">
                        <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Venue</div>
                        <div class="text-xs font-bold text-slate-200">{{ $result->program->stage->name }}</div>
                    </div>
                @endif
            </div>

            <!-- Poster Image Container -->
            <div class="p-4 sm:p-8 bg-slate-950 flex flex-col items-center justify-center">
                @if($result->poster_image)
                    <div id="posterPrintArea" class="max-w-[700px] w-full rounded-2xl overflow-hidden shadow-2xl border border-slate-800 bg-black">
                        <img src="{{ $result->poster_image }}" alt="{{ $result->program->name }} Poster" class="w-full h-auto object-contain">
                    </div>
                @else
                    <!-- High-Fidelity Dynamic Festival Poster (Rendered when static graphic is pending) -->
                    <div id="posterPrintArea" class="w-full max-w-[640px] aspect-[4/5] bg-gradient-to-br from-[#120204] via-[#1f0508] to-[#0a0203] rounded-3xl p-6 sm:p-10 border-2 border-amber-500/30 shadow-2xl relative overflow-hidden flex flex-col justify-between text-white font-sora">
                        
                        <!-- Background Decorative Glow & Watermark -->
                        <div class="absolute -top-24 -right-24 w-72 h-72 bg-red-600/15 rounded-full blur-3xl pointer-events-none"></div>
                        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
                        <div class="absolute inset-0 bg-[radial-gradient(#be1e2d_1px,transparent_1px)] [background-size:24px_24px] opacity-10 pointer-events-none"></div>

                        <!-- Top Header: QUAF Identity -->
                        <div class="relative z-10 flex flex-col items-center text-center border-b border-amber-500/20 pb-5">
                            <img src="{{ asset('images/quaf-title-logo.png') }}" alt="QUAF" class="h-14 sm:h-16 w-auto object-contain drop-shadow-md brightness-0 invert">
                            <div class="mt-2 flex items-center gap-2 text-[10px] sm:text-xs font-mono uppercase tracking-widest text-amber-400 font-bold">
                                <span>Ādabīc Inheritance</span>
                                <span>&bull;</span>
                                <span>Markaz Cultural Fest 2026</span>
                            </div>
                        </div>

                        <!-- Center: Program Details & Category -->
                        <div class="relative z-10 text-center my-auto py-4">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-950/80 border border-red-700/50 text-[#f3bd2e] text-[11px] font-mono font-bold uppercase tracking-wider mb-3">
                                <span>{{ $result->program->code }}</span>
                                <span>&bull;</span>
                                <span>{{ $result->program->zone?->name ?? $result->program->eligibility ?? 'Official Event' }}</span>
                            </div>

                            <h2 class="text-xl sm:text-3xl font-black font-rockwell tracking-tight text-white leading-tight">
                                {{ $result->program->name }}
                            </h2>

                            @if($result->program->malayalam_name)
                                <div class="text-sm sm:text-base font-ml text-amber-200/90 font-medium mt-1">
                                    {{ $result->program->malayalam_name }}
                                </div>
                            @endif

                            <div class="mt-3 text-xs font-mono text-slate-400 uppercase tracking-widest flex items-center justify-center gap-3">
                                <span>Stage: {{ $result->program->stage->name ?? 'Central Arena' }}</span>
                                @if($result->program->zone)
                                    <span>&bull;</span>
                                    <span>Zone: {{ $result->program->zone->name }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Podium Winners Grid -->
                        <div class="relative z-10 space-y-2.5 my-2">
                            <!-- 1st Prize Winner -->
                            <div class="p-3 sm:p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/40 backdrop-blur-sm flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img src="{{ asset('images/medals/first.png') }}" alt="1st" class="w-8 h-8 sm:w-10 sm:h-10 object-contain shrink-0">
                                    <div class="min-w-0 text-left">
                                        <div class="text-[9px] font-mono font-bold uppercase tracking-wider text-amber-400">First Prize</div>
                                        <div class="text-xs sm:text-sm font-black text-white truncate">
                                            {{ !empty($winners['first']) ? $winners['first'][0]['name'] : 'Declared' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-mono font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30 uppercase">
                                        {{ !empty($winners['first']) ? $winners['first'][0]['unit'] : 'House' }}
                                    </span>
                                </div>
                            </div>

                            <!-- 2nd Prize Winner -->
                            <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-800/40 border border-slate-700/60 backdrop-blur-sm flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img src="{{ asset('images/medals/second.png') }}" alt="2nd" class="w-7 h-7 sm:w-8 sm:h-8 object-contain shrink-0">
                                    <div class="min-w-0 text-left">
                                        <div class="text-[9px] font-mono font-bold uppercase tracking-wider text-slate-300">Second Prize</div>
                                        <div class="text-xs sm:text-sm font-black text-white truncate">
                                            {{ !empty($winners['second']) ? $winners['second'][0]['name'] : 'Declared' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-mono font-bold bg-slate-700/50 text-slate-300 border border-slate-600/50 uppercase">
                                        {{ !empty($winners['second']) ? $winners['second'][0]['unit'] : 'House' }}
                                    </span>
                                </div>
                            </div>

                            <!-- 3rd Prize Winner -->
                            <div class="p-3 sm:p-3.5 rounded-2xl bg-amber-950/20 border border-amber-900/40 backdrop-blur-sm flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img src="{{ asset('images/medals/third.png') }}" alt="3rd" class="w-7 h-7 sm:w-8 sm:h-8 object-contain shrink-0">
                                    <div class="min-w-0 text-left">
                                        <div class="text-[9px] font-mono font-bold uppercase tracking-wider text-amber-600">Third Prize</div>
                                        <div class="text-xs sm:text-sm font-black text-white truncate">
                                            {{ !empty($winners['third']) ? $winners['third'][0]['name'] : 'Declared' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-mono font-bold bg-amber-900/40 text-amber-400 border border-amber-800/40 uppercase">
                                        {{ !empty($winners['third']) ? $winners['third'][0]['unit'] : 'House' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer: Seal & Verification Tag -->
                        <div class="relative z-10 pt-4 border-t border-amber-500/20 flex items-center justify-between text-[9px] sm:text-[10px] font-mono text-slate-400">
                            <div>
                                <span class="font-bold text-amber-400 block uppercase">Ihyaussunna Students Union</span>
                                <span>Markazu Saquafathi Sunniyya</span>
                            </div>
                            <div class="text-right">
                                <span class="text-emerald-400 font-bold block uppercase flex items-center justify-end gap-1">
                                    <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span>Verified Verdict</span>
                                </span>
                                <span>Ref: Q9-RES-{{ str_pad($result->id, 5, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        </div>

                    </div>
                @endif
            </div>

            <!-- Winners Breakdown (Podium Only: 1st, 2nd, 3rd) -->
            <div class="p-6 sm:p-8 bg-white border-t border-slate-100">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Official Podium Winners</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- 1st Place -->
                    <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200/80 flex items-center gap-3.5">
                        <img src="{{ asset('images/medals/first.png') }}" alt="1st" class="w-10 h-10 object-contain shrink-0">
                        <div class="min-w-0">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-amber-800 font-mono">First Prize</div>
                            @if(!empty($winners['first']))
                                <div class="text-sm font-black text-slate-900 truncate">{{ $winners['first'][0]['name'] }}</div>
                                <div class="text-xs font-medium text-amber-900 truncate">{{ $winners['first'][0]['unit'] }}</div>
                            @else
                                <div class="text-xs text-slate-400 italic">Not Declared</div>
                            @endif
                        </div>
                    </div>

                    <!-- 2nd Place -->
                    <div class="p-4 rounded-2xl bg-slate-100/70 border border-slate-200 flex items-center gap-3.5">
                        <img src="{{ asset('images/medals/second.png') }}" alt="2nd" class="w-10 h-10 object-contain shrink-0">
                        <div class="min-w-0">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-600 font-mono">Second Prize</div>
                            @if(!empty($winners['second']))
                                <div class="text-sm font-black text-slate-900 truncate">{{ $winners['second'][0]['name'] }}</div>
                                <div class="text-xs font-medium text-slate-600 truncate">{{ $winners['second'][0]['unit'] }}</div>
                            @else
                                <div class="text-xs text-slate-400 italic">Not Declared</div>
                            @endif
                        </div>
                    </div>

                    <!-- 3rd Place -->
                    <div class="p-4 rounded-2xl bg-amber-900/5 border border-amber-900/10 flex items-center gap-3.5">
                        <img src="{{ asset('images/medals/third.png') }}" alt="3rd" class="w-10 h-10 object-contain shrink-0">
                        <div class="min-w-0">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-amber-950 font-mono">Third Prize</div>
                            @if(!empty($winners['third']))
                                <div class="text-sm font-black text-slate-900 truncate">{{ $winners['third'][0]['name'] }}</div>
                                <div class="text-xs font-medium text-amber-950 truncate">{{ $winners['third'][0]['unit'] }}</div>
                            @else
                                <div class="text-xs text-slate-400 italic">Not Declared</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Share & Action Bar -->
            <div class="p-6 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-2 flex-1 min-w-[280px]">
                    <input type="text" id="shareUrlInput" value="{{ url()->current() }}" readonly
                           class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-600 font-mono select-all">
                    <button onclick="navigator.clipboard.writeText(document.getElementById('shareUrlInput').value); alert('Link copied to clipboard!');" 
                            class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs shrink-0 transition-colors">
                        Copy Link
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    @php
                        $shareText = rawurlencode("QUAF - " . $result->program->name . " Official Result Announced! View poster: " . url()->current());
                    @endphp
                    <a href="https://api.whatsapp.com/send?text={{ $shareText }}" target="_blank"
                       class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        <span>WhatsApp Share</span>
                    </a>

                    <button onclick="window.print()" 
                            class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-2 transition-colors shadow-xs">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        <span>Print / Save Poster</span>
                    </button>

                    @if($result->poster_image)
                        <a href="{{ $result->poster_image }}" download="QUAF_Result_{{ $result->program->code }}.png"
                           class="px-5 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs flex items-center gap-2 transition-colors shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span>Download Poster</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
