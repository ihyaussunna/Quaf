@extends('layouts.public', ['title' => 'QUAF — Markaz Cultural Festival 2026'])

@section('content')

<!-- =====================================================================
     SECTION 1: HERO HEADER (BLACK THEME / OBSIDIAN APPLE GLASS)
     ===================================================================== -->
<section class="relative overflow-hidden bg-[#07070a] text-white pt-8 sm:pt-14 pb-8 sm:pb-10">
    <!-- Atmospheric Multi-Color Mesh Glows (Official Festival Palette) -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[340px] sm:w-[680px] h-[340px] sm:h-[480px] bg-[#be1e2d]/20 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute top-10 left-10 w-72 sm:w-96 h-72 sm:h-96 bg-[#f3bd2e]/15 rounded-full blur-[130px] pointer-events-none"></div>
    <div class="absolute bottom-10 right-10 w-72 sm:w-96 h-72 sm:h-96 bg-[#005c94]/15 rounded-full blur-[130px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-1/3 w-64 h-64 bg-[#009444]/10 rounded-full blur-[110px] pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full flex flex-col items-center text-center">
        
        <!-- Hero Center: Alternating Official Theme Logo & QUAF Festival Logo with Apple-style blur crossfade -->
        <div class="relative w-full max-w-xl mb-6 sm:mb-8 flex flex-col items-center text-center"
             x-data="{ activeLogo: 0 }"
             x-init="setInterval(() => { activeLogo = 1 - activeLogo; }, 4200)">
            
            <div class="relative w-full h-[150px] xs:h-[180px] sm:h-[220px] flex items-center justify-center">
                <!-- 1. Adabic Inheritance Logo -->
                <img src="{{ asset('images/adabic-inheritance-web.svg') }}" 
                     alt="Ādabīc Inheritance — QUAF" 
                     class="max-w-[280px] xs:max-w-[340px] sm:max-w-md lg:max-w-lg max-h-[140px] xs:max-h-[170px] sm:max-h-[210px] w-auto h-auto object-contain transition-all duration-1000 transform drop-shadow-[0_12px_40px_rgba(255,255,255,0.08)]"
                     :class="activeLogo === 0 ? 'opacity-100 scale-100 blur-none relative z-10' : 'opacity-0 scale-95 blur-md absolute pointer-events-none z-0'">

                <!-- 2. QUAF Festival Logo -->
                <img src="{{ asset('images/quaf-logo-hero.svg') }}" 
                     alt="QUAF — Markaz Cultural Festival" 
                     class="max-w-[260px] xs:max-w-[320px] sm:max-w-md lg:max-w-lg max-h-[140px] xs:max-h-[170px] sm:max-h-[210px] w-auto h-auto object-contain transition-all duration-1000 transform drop-shadow-[0_12px_40px_rgba(255,255,255,0.12)]"
                     :class="activeLogo === 1 ? 'opacity-100 scale-100 blur-none relative z-10' : 'opacity-0 scale-95 blur-md absolute pointer-events-none z-0'">
            </div>

            <!-- Festival Info Pills -->
            <div class="mt-5 sm:mt-6 flex flex-wrap items-center justify-center gap-2.5 sm:gap-3">
                <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-[11px] sm:text-xs font-mono tracking-widest uppercase bg-white/10 text-white/90 border border-white/15 backdrop-blur-md">
                    Markazu Saquafathi Sunniyya
                </span>
                <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-[11px] sm:text-xs font-mono tracking-widest uppercase bg-[#f3bd2e]/20 text-[#f3bd2e] border border-[#f3bd2e]/30 font-bold backdrop-blur-md">
                    31 OCT — 01 NOV 2026
                </span>
            </div>
        </div>

        <!-- Primary Action CTAs (Apple Glassy Buttons) -->
        <div class="flex flex-wrap items-center justify-center gap-3.5 sm:gap-4 max-w-lg">
            <a href="{{ route('results.index') }}" 
               class="px-7 py-3 rounded-2xl font-bold text-xs sm:text-sm tracking-wider uppercase bg-gradient-to-r from-[#be1e2d] via-red-600 to-[#be1e2d] text-white hover:brightness-110 shadow-lg shadow-red-600/30 border border-red-500/30 transition-all transform hover:-translate-y-0.5 text-center">
                Festival Results
            </a>
            <a href="{{ route('schedule.index') }}" 
               class="px-7 py-3 rounded-2xl font-semibold text-xs sm:text-sm tracking-wider bg-white/10 hover:bg-white/15 text-white border border-white/20 backdrop-blur-xl transition-all transform hover:-translate-y-0.5 text-center">
                Festival Schedule
            </a>
            <button type="button" 
                    @click="showStudentModal = true" 
                    class="px-6 py-3 rounded-2xl font-semibold text-xs sm:text-sm tracking-wider bg-white/5 hover:bg-white/10 text-[#f3bd2e] border border-[#f3bd2e]/30 backdrop-blur-xl transition-all transform hover:-translate-y-0.5 text-center cursor-pointer">
                Student Portal
            </button>
        </div>

    </div>
</section>

<!-- =====================================================================
     JUNCTION: INFINITE ANIMATED GIF RIBBON (BLACK TO WHITE TRANSITION)
     Loops continuously to the left with active GIF animation, attached to shape
     ===================================================================== -->
<div class="relative w-full overflow-hidden bg-[#07070a] border-t border-white/10 select-none py-2 sm:py-2.5 z-20">
    <style>
        @keyframes ribbonScrollLeft {
            0% {
                transform: translate3d(0, 0, 0);
            }
            100% {
                transform: translate3d(-50%, 0, 0);
            }
        }
        .animate-ribbon-loop {
            display: flex;
            width: max-content;
            animation: ribbonScrollLeft 32s linear infinite;
            will-change: transform;
        }
    </style>

    <div class="flex w-max animate-ribbon-loop pointer-events-none">
        <!-- Sequence 1 -->
        <div class="flex shrink-0 items-center">
            <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="h-9 sm:h-11 md:h-12 w-auto object-contain block shrink-0">
            <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="h-9 sm:h-11 md:h-12 w-auto object-contain block shrink-0">
            <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="h-9 sm:h-11 md:h-12 w-auto object-contain block shrink-0">
        </div>
        <!-- Sequence 2 (Identical Clone for seamless infinite loop) -->
        <div class="flex shrink-0 items-center">
            <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="h-9 sm:h-11 md:h-12 w-auto object-contain block shrink-0">
            <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="h-9 sm:h-11 md:h-12 w-auto object-contain block shrink-0">
            <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="h-9 sm:h-11 md:h-12 w-auto object-contain block shrink-0">
        </div>
    </div>
</div>

<!-- =====================================================================
     SECTION 2: FESTIVAL THEME PHILOSOPHY (WHITE THEME)
     ===================================================================== -->
<section class="py-16 sm:py-24 bg-white text-slate-900 border-b border-slate-200/90 relative overflow-hidden">
    <!-- Subtle Ambient Glow -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-red-50/60 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-amber-50/60 rounded-full blur-[140px] pointer-events-none"></div>

    <!-- Side Shape touching right screen edge and touching bottom / next section -->
    <img src="{{ asset('images/side-shape-theme.svg') }}" 
         alt="" 
         aria-hidden="true"
         class="absolute right-0 bottom-0 max-h-[75%] sm:max-h-[85%] lg:max-h-[92%] w-auto object-contain object-right-bottom pointer-events-none select-none z-0 opacity-75 lg:opacity-100">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl flex flex-col items-center lg:items-start text-center lg:text-left">
            <!-- Elegant Category Pill -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-slate-600 text-[11px] font-mono uppercase tracking-widest font-semibold mb-6 animate-subheading">
                <span class="w-2 h-2 rounded-full bg-[#be1e2d]"></span>
                <span>Festival Theme & Philosophy</span>
            </div>

            <!-- Main Heading Requested by User -->
            <h2 class="font-sora text-2xl sm:text-4xl lg:text-5xl font-black text-slate-950 tracking-tight uppercase leading-tight mb-8 sm:mb-10 text-center lg:text-left animate-heading">
                KNOWLEDGE IS INHERITED, NOT MERELY TRANSMITTED.
            </h2>

            <!-- Exact Philosophical Text Body -->
            <div class="space-y-6 text-slate-700 text-base sm:text-lg leading-relaxed sm:leading-loose text-center lg:text-left">
                <p class="font-medium text-slate-900 text-lg sm:text-xl">
                    What makes knowledge worthy of being inherited? And what ensures that, as it passes from one generation to another, it remains true to its source?
                </p>

                <div class="w-16 h-0.5 bg-gradient-to-r from-[#be1e2d] to-[#f3bd2e] mx-auto lg:mx-0 my-4 opacity-70"></div>

                <p>
                    In the Islamic tradition, the answer begins with <span class="font-bold text-slate-950">Adab</span>.
                </p>

                <p>
                    Adab is far more than a code of conduct. It is an intellectual, ethical, and spiritual foundation that gives knowledge its rightful place — honouring its sources, safeguarding its transmission, and shaping the relationship between teacher and student.
                </p>

                <p>
                    The Islamic scholarly tradition developed a remarkably rigorous system of principles and disciplines to ensure that knowledge was transmitted with authenticity, integrity, and trust. This intricate architecture of transmission stands among the defining strengths of the Islamic intellectual tradition.
                </p>

                <p class="font-medium text-slate-900">
                    QUAF seeks to explore this architecture — the structures, disciplines, and ethos that preserved knowledge across generations, while preserving its meaning, authority, and spirit.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================================
     SECTION 3: FESTIVAL METRICS STRIP (#BE1E2D CRIMSON RED THEME)
     Count-up Animation (0 to Target)
     ===================================================================== -->
<section class="py-14 sm:py-20 bg-[#be1e2d] text-white relative overflow-hidden shadow-inner"
         x-data="{
             animated: false,
             animateValue(el, target, duration = 1400) {
                 let start = 0;
                 let startTime = null;
                 function step(timestamp) {
                     if (!startTime) startTime = timestamp;
                     const progress = Math.min((timestamp - startTime) / duration, 1);
                     const ease = 1 - Math.pow(1 - progress, 3);
                     el.innerText = Math.floor(ease * (target - start) + start).toLocaleString();
                     if (progress < 1) {
                         window.requestAnimationFrame(step);
                     } else {
                         el.innerText = target.toLocaleString();
                     }
                 }
                 window.requestAnimationFrame(step);
             },
             init() {
                 let observer = new IntersectionObserver((entries) => {
                     entries.forEach(entry => {
                         if (entry.isIntersecting && !this.animated) {
                             this.animated = true;
                             this.$el.querySelectorAll('[data-target]').forEach(item => {
                                 const target = parseInt(item.getAttribute('data-target'), 10) || 0;
                                 this.animateValue(item, target);
                             });
                         }
                     });
                 }, { threshold: 0.2 });
                 observer.observe(this.$el);
             }
         }">
    
    <!-- Background Gradient Accents -->
    <div class="absolute inset-0 bg-gradient-to-r from-black/20 via-transparent to-black/20 pointer-events-none"></div>
    <div class="absolute -top-32 -right-32 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -left-32 w-80 h-80 bg-[#f3bd2e]/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-2 lg:grid-cols-4 items-center">
            
            <!-- Metric 1: Students -->
            <div class="relative py-6 sm:py-8 px-4 sm:px-6 text-center">
                <span class="text-xs font-mono uppercase tracking-widest text-white/80 block mb-2 font-semibold">
                    Students
                </span>
                <span class="font-sora text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight block text-white" 
                      data-target="{{ $stats['students'] ?? 640 }}">
                    0
                </span>
                <span class="text-[11px] font-mono text-white/70 block mt-2">
                    Verified Competitors
                </span>
                <!-- Vertical Divider (between Students and Groups) -->
                <div class="absolute right-0 top-1/2 -translate-y-1/2 h-14 sm:h-16 w-px bg-white/30 pointer-events-none"></div>
            </div>

            <!-- Metric 2: Groups -->
            <div class="relative py-6 sm:py-8 px-4 sm:px-6 text-center">
                <span class="text-xs font-mono uppercase tracking-widest text-white/80 block mb-2 font-semibold">
                    Groups
                </span>
                <span class="font-sora text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight block text-[#f3bd2e]" 
                      data-target="{{ $stats['groups'] ?? 5 }}">
                    0
                </span>
                <span class="text-[11px] font-mono text-white/70 block mt-2">
                    Academic Houses
                </span>
                <!-- Vertical Divider (between Groups and Zones on desktop) -->
                <div class="hidden lg:block absolute right-0 top-1/2 -translate-y-1/2 h-14 sm:h-16 w-px bg-white/30 pointer-events-none"></div>
            </div>

            <!-- Metric 3: Zones -->
            <div class="relative py-6 sm:py-8 px-4 sm:px-6 text-center">
                <span class="text-xs font-mono uppercase tracking-widest text-white/80 block mb-2 font-semibold">
                    Zones
                </span>
                <span class="font-sora text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight block text-white" 
                      data-target="{{ $stats['zones'] ?? 4 }}">
                    0
                </span>
                <span class="text-[11px] font-mono text-white/70 block mt-2">
                    Academic Divisions
                </span>
                <!-- Vertical Divider (between Zones and Programs) -->
                <div class="absolute right-0 top-1/2 -translate-y-1/2 h-14 sm:h-16 w-px bg-white/30 pointer-events-none"></div>
            </div>

            <!-- Metric 4: Programs (Last item - no right divider) -->
            <div class="relative py-6 sm:py-8 px-4 sm:px-6 text-center">
                <span class="text-xs font-mono uppercase tracking-widest text-white/80 block mb-2 font-semibold">
                    Programs
                </span>
                <span class="font-sora text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight block text-white" 
                      data-target="{{ $stats['programs'] ?? 144 }}">
                    0
                </span>
                <span class="text-[11px] font-mono text-white/70 block mt-2">
                    Stage & Offstage Events
                </span>
            </div>

        </div>
    </div>
</section>

<!-- =====================================================================
     SECTION 4: STANDINGS & RESULTS (WHITE THEME)
     Matching user screenshot: Left Team Standings, Right Latest Results
     Title: Festival Standings (no "Academic Groups", no descriptions)
     ===================================================================== -->
<section id="standings" class="py-16 sm:py-24 bg-white border-b border-slate-200/90 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Clean Section Header -->
        <div class="mb-12">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 tracking-tight animate-heading">
                Festival Standings
            </h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 sm:gap-10">
            
            <!-- Left Column: Team Standings (5 Cols) -->
            <div class="lg:col-span-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <span class="text-xs font-mono uppercase tracking-widest text-slate-500 font-bold">
                            House Standings
                        </span>
                        <span class="text-xs font-mono text-slate-400">
                            Rank & Points
                        </span>
                    </div>

                    <div class="space-y-3.5">
                        @foreach($groups->take(4) as $group)
                            @php
                                $isLeader = $loop->iteration === 1;
                                $maxPoints = $groups->first()->points_cache > 0 ? $groups->first()->points_cache : 100;
                                $percentage = min(100, round(($group->points_cache / $maxPoints) * 100));
                            @endphp
                            <a href="{{ route('groups.show', $group->id) }}" 
                               class="group block p-4 sm:p-5 rounded-2xl bg-slate-50 hover:bg-slate-100/80 border border-slate-200 hover:border-slate-300 transition-all shadow-2xs hover:shadow-md relative overflow-hidden">
                                
                                <div class="flex items-center justify-between relative z-10">
                                    <div class="flex items-center gap-3.5">
                                        <!-- Rank Badge -->
                                        @if($isLeader)
                                            <span class="w-9 h-9 rounded-xl bg-amber-400 text-slate-950 font-sora font-black text-sm flex items-center justify-center shadow-sm">
                                                #1
                                            </span>
                                        @else
                                            <span class="w-9 h-9 rounded-xl bg-slate-200 text-slate-700 font-sora font-bold text-sm flex items-center justify-center">
                                                #{{ $loop->iteration }}
                                            </span>
                                        @endif

                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $group->color_hex ?: '#be1e2d' }}"></span>
                                                <h3 class="font-sora font-bold text-slate-900 group-hover:text-[#be1e2d] transition-colors text-base">
                                                    {{ $group->name }}
                                                </h3>
                                            </div>
                                            <span class="text-[11px] font-mono text-slate-400 block mt-0.5 uppercase tracking-wide">
                                                {{ $group->code }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Points -->
                                    <div class="text-right">
                                        <span class="font-sora font-black text-2xl text-slate-900 block leading-none">
                                            {{ number_format($group->points_cache) }}
                                        </span>
                                        <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">
                                            PTS
                                        </span>
                                    </div>
                                </div>

                                <!-- Subtle Progress Indicator -->
                                <div class="mt-3.5 w-full bg-slate-200/70 h-1.5 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-700" 
                                         style="width: {{ $percentage }}%; background-color: {{ $group->color_hex ?: '#be1e2d' }};"></div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Full Standings Link Button -->
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <a href="{{ route('groups.index') }}" 
                       class="w-full py-3 px-5 rounded-2xl bg-white hover:bg-slate-50 border border-slate-300 text-slate-800 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition hover:border-[#be1e2d]">
                        <span>Full Standings</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

            <!-- Right Column: Latest Results (7 Cols) -->
            <div class="lg:col-span-7 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <span class="text-xs font-mono uppercase tracking-widest text-slate-500 font-bold">
                            Latest Results
                        </span>
                        <a href="{{ route('results.index') }}" class="text-xs font-bold text-[#be1e2d] hover:underline">
                            View All &rarr;
                        </a>
                    </div>

                    @if($latestResults->isNotEmpty())
                        <div class="space-y-3.5">
                            @foreach($latestResults->take(4) as $result)
                                <a href="{{ route('results.show', $result->program_id) }}" 
                                   class="group block p-4 sm:p-5 rounded-2xl bg-slate-50 hover:bg-slate-100/80 border border-slate-200 hover:border-slate-300 transition-all shadow-2xs hover:shadow-md">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-[#be1e2d]/10 text-[#be1e2d] border border-[#be1e2d]/20 uppercase">
                                                    {{ $result->program?->code ?? 'PRG' }}
                                                </span>
                                                <span class="text-xs font-mono text-slate-500 uppercase">
                                                    {{ $result->program?->category?->name ?? 'General' }}
                                                </span>
                                                @if($result->program?->stage)
                                                    <span class="text-[11px] font-mono text-slate-400">
                                                        • {{ $result->program->stage->name }}
                                                    </span>
                                                @endif
                                            </div>

                                            <h3 class="font-sora font-bold text-slate-900 group-hover:text-[#be1e2d] transition-colors text-base leading-snug">
                                                {{ $result->program?->name ?? 'Festival Event' }}
                                            </h3>

                                            @if($result->firstEntry?->student)
                                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                                                    <span class="font-bold text-amber-600">1st:</span>
                                                    <span>{{ $result->firstEntry->student->name }}</span>
                                                    <span class="text-slate-400">({{ $result->firstEntry->student->group?->name ?? 'House' }})</span>
                                                </p>
                                            @endif
                                        </div>

                                        <div class="flex items-center text-xs font-bold text-[#be1e2d] group-hover:translate-x-1 transition-transform shrink-0 pt-2">
                                            <span>View</span>
                                            <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <!-- Elegant Glassy Empty State -->
                        <div class="rounded-2xl p-8 bg-slate-50 border border-dashed border-slate-300 text-center">
                            <svg class="w-10 h-10 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h4 class="font-sora font-bold text-slate-800 text-base mb-1">Verdicts in Tabulation</h4>
                            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                                The festival committee will publish jury results here as soon as verification completes.
                            </p>
                        </div>
                    @endif
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100">
                    <a href="{{ route('results.index') }}" 
                       class="w-full py-3.5 px-5 rounded-2xl bg-gradient-to-r from-[#be1e2d] via-red-600 to-[#f3bd2e] text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-red-600/25 hover:brightness-110 transition">
                        <span>All Results Archive</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- =====================================================================
     SECTION 5: HIGHLIGHTS (MEDIA & VIDEO HUB)
     Giving priority to featured videos
     ===================================================================== -->
<section id="highlights" class="py-16 sm:py-24 bg-slate-950 text-white border-b border-white/10 relative overflow-hidden">
    <!-- Atmospheric Ambient Glow -->
    <div class="absolute top-1/3 left-10 w-96 h-96 bg-[#be1e2d]/15 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute bottom-10 right-10 w-96 h-96 bg-[#005c94]/15 rounded-full blur-[140px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#f3bd2e] uppercase block mb-1 animate-subheading">
                    AUDIOVISUAL BROADCASTS
                </span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-sora font-black text-white tracking-tight animate-heading">
                    Festival Highlights
                </h2>
            </div>
            <a href="{{ route('media.index') }}" 
               class="text-xs sm:text-sm font-bold text-[#f3bd2e] hover:underline flex items-center gap-1.5 self-start sm:self-auto">
                <span>Open Media Hub</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        @if($featuredVideo)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center mb-10">
                <!-- Main Spotlight Video -->
                <div class="lg:col-span-8 rounded-3xl overflow-hidden bg-white/5 border border-white/15 backdrop-blur-xl shadow-2xl relative aspect-video flex items-center justify-center">
                    @if($featuredVideo->youtube_id)
                        <iframe class="w-full h-full" 
                                src="https://www.youtube.com/embed/{{ $featuredVideo->youtube_id }}" 
                                title="{{ $featuredVideo->title }}" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen></iframe>
                    @else
                        <div class="p-8 text-center">
                            <div class="w-16 h-16 rounded-full bg-[#be1e2d] text-white flex items-center justify-center mx-auto mb-4 shadow-lg shadow-red-600/40">
                                <svg class="w-7 h-7 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                            <h3 class="font-sora text-xl font-bold text-white mb-2">{{ $featuredVideo->title }}</h3>
                            <a href="{{ route('media.index') }}" class="inline-block mt-2 text-xs font-mono uppercase text-[#f3bd2e] underline">Watch on Media Desk &rarr;</a>
                        </div>
                    @endif
                </div>

                <!-- Spotlight Video Description Card -->
                <div class="lg:col-span-4 rounded-3xl p-6 sm:p-8 bg-white/5 border border-white/10 backdrop-blur-xl flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                            <span class="text-xs font-mono uppercase tracking-widest text-red-400 font-bold">Featured Spotlight</span>
                        </div>
                        <h3 class="font-sora text-xl sm:text-2xl font-bold text-white mb-3 leading-snug">
                            {{ $featuredVideo->title }}
                        </h3>
                        <p class="text-sm text-slate-300 leading-relaxed line-clamp-4">
                            {{ $featuredVideo->description ?: 'Watch exclusive highlights, performances, and speeches from QUAF on the official media channel.' }}
                        </p>
                    </div>

                    <div class="mt-6 pt-5 border-t border-white/10">
                        <a href="{{ route('media.index') }}" 
                           class="w-full py-3.5 px-6 rounded-2xl bg-[#be1e2d] hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition shadow-md shadow-red-600/25">
                            <span>Browse All Videos</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Elegant Broadcast Hub Placeholder Card -->
            <div class="rounded-3xl p-8 sm:p-12 bg-white/5 border border-white/10 backdrop-blur-2xl text-center max-w-2xl mx-auto shadow-2xl">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#be1e2d] to-[#f3bd2e] flex items-center justify-center text-white mx-auto mb-4 shadow-lg shadow-red-600/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="font-sora text-xl sm:text-2xl font-bold text-white mb-2">QUAF Official Broadcast Hub</h3>
                <p class="text-sm text-slate-300 max-w-md mx-auto mb-6 leading-relaxed">
                    Livestreams, grand opening coverage, and stage highlight packages will be published directly from the media desk.
                </p>
                <a href="{{ route('media.index') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/20 text-[#f3bd2e] font-bold text-xs uppercase tracking-wider backdrop-blur-xl transition">
                    <span>Visit Media Hub</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        @endif

    </div>
</section>

<!-- =====================================================================
     SECTION 6: FESTIVAL GALLERY PREVIEW (APPLE GLASSY PHOTO GRID)
     ===================================================================== -->
@if($galleryPreview->isNotEmpty())
<section id="gallery" class="py-16 sm:py-24 bg-white border-b border-slate-200/90 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase block mb-1 animate-subheading">
                    CAPTURED MOMENTS
                </span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 tracking-tight animate-heading">
                    Festival Gallery
                </h2>
            </div>
            <a href="{{ route('gallery.index') }}" 
               class="text-xs sm:text-sm font-bold text-[#be1e2d] hover:underline flex items-center gap-1.5 self-start sm:self-auto">
                <span>View Full Photo Archive</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
            @foreach($galleryPreview as $photo)
                <a href="{{ route('gallery.index') }}" 
                   class="group relative rounded-3xl overflow-hidden aspect-[4/3] bg-slate-100 shadow-2xs hover:shadow-xl transition-all duration-500">
                    <img src="{{ $photo->image_path }}" 
                         alt="{{ $photo->title }}" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" 
                         loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-5">
                        <span class="text-xs sm:text-sm font-semibold text-white truncate drop-shadow-sm">
                            {{ $photo->title }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

    </div>
</section>
@endif

<!-- =====================================================================
     SECTION 7: FESTIVAL NEWS & DISPATCHES
     ===================================================================== -->
@if($latestNews->isNotEmpty())
<section id="news" class="py-16 sm:py-24 bg-slate-50 border-b border-slate-200/90 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase block mb-1 animate-subheading">
                    JOURNAL & BULLETINS
                </span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 tracking-tight animate-heading">
                    Festival News
                </h2>
            </div>
            <a href="{{ route('news.index') }}" 
               class="text-xs sm:text-sm font-bold text-[#be1e2d] hover:underline flex items-center gap-1.5 self-start sm:self-auto">
                <span>All Articles & Bulletins</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 sm:gap-8">
            @foreach($latestNews as $article)
                <a href="{{ route('news.show', $article->slug) }}" 
                   class="group flex flex-col rounded-3xl bg-white border border-slate-200/90 hover:border-slate-300 overflow-hidden transition-all duration-300 shadow-2xs hover:shadow-xl hover:-translate-y-1">
                    <div class="aspect-video w-full overflow-hidden bg-slate-100 relative">
                        @if($article->cover_image)
                            <img src="{{ $article->cover_image }}" 
                                 alt="{{ $article->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                 loading="lazy">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-400 font-mono text-xs">
                                QUAF Press Desk
                            </div>
                        @endif
                        <div class="absolute top-3 left-3">
                            <span class="px-3 py-1 rounded-full text-[10px] font-mono uppercase tracking-wider bg-white/95 backdrop-blur-md text-[#be1e2d] border border-red-200 font-bold shadow-2xs">
                                {{ $article->category }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <span class="text-xs font-mono text-slate-400 block mb-2 font-medium">
                                {{ $article->published_at?->format('M d, Y') ?? 'Recent' }}
                            </span>
                            <h3 class="text-lg font-sora font-bold text-slate-900 group-hover:text-[#be1e2d] transition-colors leading-snug mb-2">
                                {{ $article->title }}
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 line-clamp-2 leading-relaxed">
                                {{ $article->excerpt }}
                            </p>
                        </div>
                        <span class="text-xs font-bold text-[#be1e2d] mt-5 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                            Read Dispatch &rarr;
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

    </div>
</section>
@endif

<!-- =====================================================================
     SECTION 8: FESTIVAL RESOURCE CENTER & PORTAL ACCESS (APPLE GLASSY)
     Brochure, Student Portal, and Official Access Cards
     ===================================================================== -->
<section class="py-16 sm:py-24 bg-white text-slate-900 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase block mb-1 animate-subheading">
                PUBLIC RESOURCES
            </span>
            <h2 class="text-3xl sm:text-4xl font-sora font-black text-slate-900 tracking-tight animate-heading">
                Festival Access & Documents
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
            
            <!-- Card 1: Official Brochure -->
            <div class="rounded-3xl p-6 sm:p-8 bg-slate-50 border border-slate-200/90 hover:border-slate-300 flex flex-col justify-between transition-all duration-300 shadow-2xs hover:shadow-xl">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center mb-5 font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <h3 class="font-sora text-xl font-bold text-slate-900 mb-2">Digital Brochure</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-6">
                        Explore the complete 14-page festival guidebook featuring rules, categories, timelines, and messages.
                    </p>
                </div>
                <div class="space-y-2.5">
                    <a href="{{ route('brochure.index') }}" 
                       class="w-full py-3 px-5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition">
                        <span>Read Brochure Online</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

            <!-- Card 2: Student Portal Lookup -->
            <div class="rounded-3xl p-6 sm:p-8 bg-slate-50 border border-slate-200/90 hover:border-slate-300 flex flex-col justify-between transition-all duration-300 shadow-2xs hover:shadow-xl">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-red-100 text-[#be1e2d] flex items-center justify-center mb-5 font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <h3 class="font-sora text-xl font-bold text-slate-900 mb-2">Student Portal</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-6">
                        Check your registered programs, call list queues, digital pass, and stage timings with your chest number.
                    </p>
                </div>
                <div>
                    <button type="button" 
                            @click="showStudentModal = true" 
                            class="w-full py-3 px-5 rounded-2xl bg-[#be1e2d] hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition shadow-md shadow-red-600/20 cursor-pointer">
                        <span>Enter Chest Number</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>

            <!-- Card 3: Festival Schedule & Verifications -->
            <div class="rounded-3xl p-6 sm:p-8 bg-slate-50 border border-slate-200/90 hover:border-slate-300 flex flex-col justify-between transition-all duration-300 shadow-2xs hover:shadow-xl">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 text-[#005c94] flex items-center justify-center mb-5 font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="font-sora text-xl font-bold text-slate-900 mb-2">Festival Schedule</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-6">
                        Track running stage events, offstage competitions, and venue allocations updated live.
                    </p>
                </div>
                <div>
                    <a href="{{ route('schedule.index') }}" 
                       class="w-full py-3 px-5 rounded-2xl bg-white hover:bg-slate-100 border border-slate-300 text-slate-800 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition hover:border-[#005c94]">
                        <span>View Schedule</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>

@endsection
