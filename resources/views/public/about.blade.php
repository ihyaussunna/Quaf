@extends('layouts.public', ['title' => 'About QUAF 9.0 — Markaz Cultural Festival 2026'])

@section('content')

<!-- Hero Header -->
<section class="py-14 sm:py-20 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">FESTIVAL HERITAGE & PHILOSOPHY</span>
            <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 mt-2 tracking-tight">
                About QUAF 9.0
            </h1>
            <p class="text-base sm:text-lg text-slate-600 mt-3 font-normal leading-relaxed">
                The premier collegiate arts and cultural confluence organized by the Ihyaussunna-Markaz Students' Union, Markazu Saquafathi Sunniyya.
            </p>
        </div>
    </div>
</section>

<!-- Theme Note Section -->
<section class="py-14 sm:py-20 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            
            <div class="space-y-6">
                <span class="px-3 py-1 rounded-full text-xs font-mono uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300 font-bold inline-block">
                    CENTRAL MOTIF
                </span>
                <h2 class="text-2xl sm:text-4xl font-sora font-black text-slate-900 leading-tight">
                    Ādabīc Inheritance: Confluence of Eloquence & Intellectual Heritage
                </h2>
                <div class="prose prose-slate text-slate-700 space-y-4 text-sm sm:text-base leading-relaxed">
                    <p>
                        QUAF Season 09 embodies the grand tradition of artistic refinement, Islamic aesthetics, and classical literary eloquence. Under the overarching motif of <em>Ādabīc Inheritance</em>, the festival serves as a fertile ground for cultivating oratory mastery, calligraphic finesse, choral harmonies, and analytical thought.
                    </p>
                    <p>
                        Beginning with intensive Offstage disciplines from October 06, 2026, leading to the grand Main Stage confluence from October 31 to November 01, 2026, the festival arena at Jamia Markaz hosts verified delegates representing five academic houses, competing harmoniously across 120+ codified disciplines.
                    </p>
                </div>
            </div>

            <div class="rounded-3xl bg-white border border-slate-200 p-8 shadow-md space-y-6">
                <h3 class="font-sora font-bold text-xl text-slate-900">Festival Key Dimensions</h3>
                <div class="space-y-4 font-mono text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                        <span class="text-slate-500 uppercase">Organizing Body</span>
                        <strong class="text-slate-900 font-sora">Ihyaussunna Students Union</strong>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                        <span class="text-slate-500 uppercase">Campus Venue</span>
                        <strong class="text-slate-900 font-sora">Jamia Markaz, Karanthur</strong>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                        <span class="text-slate-500 uppercase">Official Dates</span>
                        <strong class="text-[#be1e2d] font-bold">06 Oct – 01 Nov, 2026</strong>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                        <span class="text-slate-500 uppercase">Program Spectrum</span>
                        <strong class="text-slate-900">144 Codified Disciplines</strong>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Academic Houses & Zones Structure -->
<section class="py-14 sm:py-20 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">COLLEGIATE ARCHITECTURE</span>
            <h2 class="text-2xl sm:text-4xl font-sora font-black text-slate-900 mt-1">Five Houses & Four Zones</h2>
            <p class="text-xs sm:text-sm text-slate-600 mt-2">
                Participants represent one of five historic academic houses, categorized strictly according to collegiate class levels.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @foreach($groups as $grp)
                <div class="p-5 rounded-2xl border-2 border-slate-200/90 text-center relative overflow-hidden"
                     style="border-top-color: {{ $grp->color_hex }}; border-top-width: 4px;">
                    <div class="w-8 h-8 rounded-full mx-auto mb-3" style="background-color: {{ $grp->color_hex }}"></div>
                    <h4 class="font-sora font-black text-slate-900 text-lg mb-1">{{ $grp->name }}</h4>
                    <span class="font-mono text-xs text-slate-500 uppercase">{{ $grp->code }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
