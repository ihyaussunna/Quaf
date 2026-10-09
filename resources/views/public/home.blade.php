@extends('layouts.public', ['title' => 'QUAF — Markaz Cultural Festival 2026'])

@section('content')

<!-- =====================================================================
     SECTION 1: HERO HEADER (BLACK THEME / OBSIDIAN APPLE GLASS)
     ===================================================================== -->
<section class="relative bg-[#07070a] text-white pt-16 sm:pt-24 lg:pt-32 pb-0 z-10">
    <!-- Atmospheric Multi-Color Mesh Glows (Official Festival Palette) -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[340px] sm:w-[680px] h-[340px] sm:h-[480px] bg-[#be1e2d]/20 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute top-10 left-10 w-72 sm:w-96 h-72 sm:h-96 bg-[#f3bd2e]/15 rounded-full blur-[130px] pointer-events-none"></div>
    <div class="absolute bottom-10 right-10 w-72 sm:w-96 h-72 sm:h-96 bg-[#005c94]/15 rounded-full blur-[130px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-1/3 w-64 h-64 bg-[#009444]/10 rounded-full blur-[110px] pointer-events-none"></div>

    <!-- Rotating Yellow Pattern Burst (Hero Background Accent with Entrance Animation) -->
    <style>
        @keyframes heroPatternSlideIn {
            0% {
                transform: translate3d(100%, 0, 0);
                opacity: 0;
            }
            100% {
                transform: translate3d(36%, 0, 0);
                opacity: 0.95;
            }
        }
        @keyframes heroPatternSpin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        .hero-pattern-wrapper {
            position: absolute;
            right: 0;
            bottom: -60px;
            width: 260px;
            height: 260px;
            pointer-events: none;
            user-select: none;
            z-index: 5;
            animation: heroPatternSlideIn 2.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            will-change: transform, opacity;
        }
        @media (max-width: 639px) {
            .hero-pattern-wrapper {
                bottom: -35px;
                width: 230px;
                height: 230px;
            }
            .hero-pattern-svg {
                animation: heroPatternSpin 72s linear infinite;
            }
        }
        @media (min-width: 640px) {
            .hero-pattern-wrapper {
                bottom: -75px;
                width: 360px;
                height: 360px;
            }
        }
        @media (min-width: 1024px) {
            .hero-pattern-wrapper {
                bottom: -95px;
                width: 440px;
                height: 440px;
            }
        }
        .hero-pattern-svg {
            width: 100%;
            height: 100%;
            display: block;
            transform-origin: center center;
            animation: heroPatternSpin 52s linear infinite;
            will-change: transform;
        }
    </style>

    <div class="hero-pattern-wrapper" style="z-index: 5;" aria-hidden="true">
        <!-- Inline SVG burst pattern with zero-dependency rendering -->
        <svg class="hero-pattern-svg" viewBox="0 0 27.7 27.7" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <style>.pat-burst-line { fill: #f3bd2e; }</style>
            </defs>
            <g>
                <polygon class="pat-burst-line" points="13.8 11.66 13.55 0 14.15 0 13.89 11.66 13.8 11.66 13.8 11.66"/>
                <polygon class="pat-burst-line" points="13.13 11.78 9.28 .77 9.86 .58 13.21 11.75 13.13 11.78 13.13 11.78"/>
                <polygon class="pat-burst-line" points="12.53 12.1 5.46 2.82 5.95 2.47 12.6 12.05 12.53 12.1 12.53 12.1"/>
                <polygon class="pat-burst-line" points="12.05 12.6 2.47 5.95 2.82 5.46 12.1 12.53 12.05 12.6 12.05 12.6"/>
                <polygon class="pat-burst-line" points="11.75 13.21 .58 9.86 .77 9.28 11.78 13.13 11.75 13.21 11.75 13.21"/>
                <polygon class="pat-burst-line" points="11.66 13.89 0 14.15 0 13.55 11.66 13.8 11.66 13.89 11.66 13.89"/>
                <polygon class="pat-burst-line" points="11.78 14.57 .77 18.41 .58 17.84 11.75 14.48 11.78 14.57 11.78 14.57"/>
                <polygon class="pat-burst-line" points="12.1 15.17 2.82 22.23 2.47 21.74 12.05 15.1 12.1 15.17 12.1 15.17"/>
                <polygon class="pat-burst-line" points="12.6 15.64 5.95 25.23 5.46 24.87 12.53 15.59 12.6 15.64 12.6 15.64"/>
                <polygon class="pat-burst-line" points="13.21 15.94 9.86 27.11 9.28 26.92 13.13 15.91 13.21 15.94 13.21 15.94"/>
                <polygon class="pat-burst-line" points="13.89 16.04 14.15 27.7 13.55 27.7 13.8 16.04 13.89 16.04 13.89 16.04"/>
                <polygon class="pat-burst-line" points="14.57 15.91 18.41 26.92 17.84 27.11 14.48 15.94 14.57 15.91 14.57 15.91"/>
                <polygon class="pat-burst-line" points="15.17 15.59 22.23 24.87 21.74 25.23 15.1 15.64 15.17 15.59 15.17 15.59"/>
                <polygon class="pat-burst-line" points="15.64 15.1 25.23 21.74 24.87 22.23 15.59 15.17 15.64 15.1 15.64 15.1"/>
                <polygon class="pat-burst-line" points="15.94 14.48 27.11 17.84 26.92 18.41 15.91 14.57 15.94 14.48 15.94 14.48"/>
                <polygon class="pat-burst-line" points="16.04 13.8 27.7 13.55 27.7 14.15 16.04 13.89 16.04 13.8 16.04 13.8"/>
                <polygon class="pat-burst-line" points="15.91 13.13 26.92 9.28 27.11 9.86 15.94 13.21 15.91 13.13 15.91 13.13"/>
                <polygon class="pat-burst-line" points="15.59 12.53 24.87 5.46 25.23 5.95 15.64 12.6 15.59 12.53 15.59 12.53"/>
                <polygon class="pat-burst-line" points="15.1 12.05 21.74 2.47 22.23 2.82 15.17 12.1 15.1 12.05 15.1 12.05"/>
                <polygon class="pat-burst-line" points="14.48 11.75 17.84 .58 18.41 .77 14.57 11.78 14.48 11.75 14.48 11.75"/>
            </g>
            <circle class="pat-burst-line" cx="13.82" cy="13.91" r="2.28"/>
        </svg>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20 w-full flex flex-col items-center text-center">
        
        <!-- Hero Center: Alternating Official Theme Logo & QUAF Festival Logo with Apple-style blur crossfade -->
        <div class="relative w-full max-w-xl mb-6 sm:mb-8 flex flex-col items-center text-center"
             x-data="{ activeLogo: 0 }"
             x-init="setInterval(() => { activeLogo = 1 - activeLogo; }, 4200)">
            
            <div class="relative w-full h-[112px] xs:h-[135px] sm:h-[165px] flex items-center justify-center">
                <!-- 1. Adabic Inheritance Logo -->
                <img src="{{ asset('images/adabic-inheritance-web.svg') }}" 
                     alt="Ādabīc Inheritance — QUAF" 
                     class="max-w-[210px] xs:max-w-[255px] sm:max-w-[336px] lg:max-w-[384px] max-h-[105px] xs:max-h-[128px] sm:max-h-[158px] w-auto h-auto object-contain transition-all duration-1000 transform drop-shadow-[0_12px_40px_rgba(255,255,255,0.08)]"
                     :class="activeLogo === 0 ? 'opacity-100 scale-100 blur-none relative z-10' : 'opacity-0 scale-95 blur-md absolute pointer-events-none z-0'">

                <!-- 2. QUAF Festival Logo -->
                <img src="{{ asset('images/quaf-logo-hero.svg') }}" 
                     alt="QUAF — Markaz Cultural Festival" 
                     class="max-w-[195px] xs:max-w-[240px] sm:max-w-[336px] lg:max-w-[384px] max-h-[105px] xs:max-h-[128px] sm:max-h-[158px] w-auto h-auto object-contain transition-all duration-1000 transform drop-shadow-[0_12px_40px_rgba(255,255,255,0.12)]"
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
        <div class="flex flex-wrap items-center justify-center gap-3.5 sm:gap-4 max-w-lg mb-6 sm:mb-8">
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

    <!-- JUNCTION: INFINITE ANIMATED GIF RIBBON (TRANSPARENT BACKGROUND, SITS ABOVE SPINNING PATTERN) -->
    <div class="relative w-full overflow-hidden bg-transparent select-none p-0 m-0 leading-none" style="z-index: 10;">
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
                animation: ribbonScrollLeft 44s linear infinite;
                will-change: transform;
            }
            @media (min-width: 640px) {
                .animate-ribbon-loop {
                    animation: ribbonScrollLeft 32s linear infinite;
                }
            }
        </style>

        <div class="flex w-max animate-ribbon-loop pointer-events-none p-0 m-0 leading-none">
            <!-- Sequence 1 -->
            <div class="flex shrink-0 items-center p-0 m-0 leading-none">
                <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="h-[27px] sm:h-[33px] md:h-[37px] w-auto object-contain block shrink-0 m-0 p-0">
                <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="h-[27px] sm:h-[33px] md:h-[37px] w-auto object-contain block shrink-0 m-0 p-0">
                <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="h-[27px] sm:h-[33px] md:h-[37px] w-auto object-contain block shrink-0 m-0 p-0">
            </div>
            <!-- Sequence 2 (Identical Clone for seamless infinite loop) -->
            <div class="flex shrink-0 items-center p-0 m-0 leading-none">
                <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="h-[27px] sm:h-[33px] md:h-[37px] w-auto object-contain block shrink-0 m-0 p-0">
                <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="h-[27px] sm:h-[33px] md:h-[37px] w-auto object-contain block shrink-0 m-0 p-0">
                <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="h-[27px] sm:h-[33px] md:h-[37px] w-auto object-contain block shrink-0 m-0 p-0">
            </div>
        </div>
    </div>
</section>

<!-- =====================================================================
     SECTION 2: FESTIVAL THEME PHILOSOPHY (WHITE THEME)
     ===================================================================== -->
<section class="py-16 sm:py-24 bg-white text-slate-900 border-b border-slate-200/90 relative overflow-hidden z-20">
    <!-- Subtle Ambient Glow -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-red-50/60 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-amber-50/60 rounded-full blur-[140px] pointer-events-none"></div>

    <!-- Side Shape touching right screen edge and touching bottom / next section (Hidden on mobile) -->
    <img src="{{ asset('images/side-shape-theme.svg') }}?v=3" 
         alt="" 
         aria-hidden="true"
         class="hidden md:block absolute right-0 bottom-0 max-h-[55%] sm:max-h-[68%] lg:max-h-[78%] max-w-[180px] sm:max-w-[240px] lg:max-w-[300px] w-auto object-contain object-right-bottom pointer-events-none select-none z-0">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl flex flex-col items-center lg:items-start text-center lg:text-left">
            <!-- Elegant Category Pill -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-slate-600 text-[11px] font-mono uppercase tracking-widest font-semibold mb-4 sm:mb-5 animate-subheading">
                <span class="w-2 h-2 rounded-full bg-[#be1e2d]"></span>
                <span>Festival Theme & Philosophy</span>
            </div>

            <!-- Main Heading Requested by User -->
            <h2 class="font-sora text-2xl sm:text-4xl lg:text-5xl font-black text-slate-950 tracking-tight uppercase leading-tight mb-4 sm:mb-6 text-center lg:text-left animate-heading">
                KNOWLEDGE IS INHERITED, NOT MERELY TRANSMITTED.
            </h2>

            <!-- Exact Philosophical Text Body (Tightened line-height & paragraph spacing) -->
            <div class="space-y-2.5 sm:space-y-3 text-slate-700 text-sm sm:text-base leading-snug sm:leading-normal text-center lg:text-left">
                <p class="font-medium text-slate-900 text-base sm:text-lg">
                    What makes knowledge worthy of being inherited? And what ensures that, as it passes from one generation to another, it remains true to its source?
                </p>

                <div class="w-16 h-0.5 bg-gradient-to-r from-[#be1e2d] to-[#f3bd2e] mx-auto lg:mx-0 my-2.5 opacity-70"></div>

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
     Seamless transition to Section 4 with 3 Moving Tape Bars at the bottom
     ===================================================================== -->
<section class="pt-14 sm:pt-20 pb-0 bg-[#be1e2d] text-white relative z-20 overflow-hidden"
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
                <!-- Vertical Divider (between Students and Groups) -->
                <div class="absolute right-0 top-1/2 -translate-y-1/2 h-14 sm:h-16 w-px bg-white/30 pointer-events-none"></div>
            </div>

            <!-- Metric 2: Groups -->
            <div class="relative py-6 sm:py-8 px-4 sm:px-6 text-center">
                <span class="text-xs font-mono uppercase tracking-widest text-white/80 block mb-2 font-semibold">
                    Groups
                </span>
                <span class="font-sora text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight block text-white" 
                      data-target="{{ $stats['groups'] ?? 5 }}">
                    0
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
            </div>

        </div>
    </div>

    <!-- JUNCTION: 3 INTERLOCKING MOVING BARS (SEAMLESS IN SECTION 3) -->
    <div class="relative w-full select-none pointer-events-none mt-4 sm:mt-6">
        <style>
            @keyframes quafBarScrollLeft {
                0% { transform: translate3d(0, 0, 0); }
                100% { transform: translate3d(-50%, 0, 0); }
            }
            @keyframes quafBarScrollRight {
                0% { transform: translate3d(-50%, 0, 0); }
                100% { transform: translate3d(0, 0, 0); }
            }
            .quaf-tape-row {
                width: 120%;
                margin-left: -10%;
                display: flex;
                overflow: hidden;
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.32);
                will-change: transform;
            }
            .quaf-tape-row-no-shadow {
                box-shadow: none !important;
            }
            .quaf-tape-track {
                display: flex;
                width: max-content;
                align-items: center;
                will-change: transform;
            }
            .quaf-tape-img {
                height: 32px;
                width: auto;
                flex-shrink: 0;
                display: block;
            }
            .quaf-tape-overlap {
                margin-top: -10px;
            }
            @media (min-width: 640px) {
                .quaf-tape-img {
                    height: 38px;
                }
                .quaf-tape-overlap {
                    margin-top: -13px;
                }
            }
            @media (min-width: 1024px) {
                .quaf-tape-img {
                    height: 44px;
                }
                .quaf-tape-overlap {
                    margin-top: -16px;
                }
            }
        </style>

        <div class="relative z-10 pt-2 pb-3 sm:pb-4">
            <!-- Bar 1: Green (Top bar, moves LEFT, tilted -1.3deg) -->
            <div class="quaf-tape-row" style="position: relative; z-index: 30; transform: rotate(-1.3deg);">
                <div class="quaf-tape-track" style="animation: quafBarScrollLeft 34s linear infinite;">
                    <!-- Sequence 1 -->
                    <div style="display: flex; flex-shrink: 0; align-items: center;">
                        @for ($i = 0; $i < 20; $i++)
                            <img src="{{ asset('images/moving-quaf-bar-green.svg') }}" alt="QUAF" class="quaf-tape-img" loading="eager">
                        @endfor
                    </div>
                    <!-- Sequence 2 (Identical Clone for infinite loop) -->
                    <div style="display: flex; flex-shrink: 0; align-items: center;" aria-hidden="true">
                        @for ($i = 0; $i < 20; $i++)
                            <img src="{{ asset('images/moving-quaf-bar-green.svg') }}" alt="" class="quaf-tape-img" loading="eager">
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Bar 2: Yellow (Middle bar, moves RIGHT, tilted +0.8deg, overlaps Green) -->
            <div class="quaf-tape-row quaf-tape-overlap" style="position: relative; z-index: 20; transform: rotate(0.8deg);">
                <div class="quaf-tape-track" style="animation: quafBarScrollRight 28s linear infinite;">
                    <!-- Sequence 1 -->
                    <div style="display: flex; flex-shrink: 0; align-items: center;">
                        @for ($i = 0; $i < 20; $i++)
                            <img src="{{ asset('images/moving-quaf-bar-yellow.svg') }}" alt="QUAF" class="quaf-tape-img" loading="eager">
                        @endfor
                    </div>
                    <!-- Sequence 2 (Identical Clone for infinite loop) -->
                    <div style="display: flex; flex-shrink: 0; align-items: center;" aria-hidden="true">
                        @for ($i = 0; $i < 20; $i++)
                            <img src="{{ asset('images/moving-quaf-bar-yellow.svg') }}" alt="" class="quaf-tape-img" loading="eager">
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Bar 3: Blue (Bottom bar, moves LEFT, tilted -0.7deg, overlaps Yellow, shadow removed) -->
            <div class="quaf-tape-row quaf-tape-overlap quaf-tape-row-no-shadow" style="position: relative; z-index: 10; transform: rotate(-0.7deg); box-shadow: none;">
                <div class="quaf-tape-track" style="animation: quafBarScrollLeft 38s linear infinite;">
                    <!-- Sequence 1 -->
                    <div style="display: flex; flex-shrink: 0; align-items: center;">
                        @for ($i = 0; $i < 20; $i++)
                            <img src="{{ asset('images/moving-quaf-bar-blue.svg') }}" alt="QUAF" class="quaf-tape-img" loading="eager">
                        @endfor
                    </div>
                    <!-- Sequence 2 (Identical Clone for infinite loop) -->
                    <div style="display: flex; flex-shrink: 0; align-items: center;" aria-hidden="true">
                        @for ($i = 0; $i < 20; $i++)
                            <img src="{{ asset('images/moving-quaf-bar-blue.svg') }}" alt="" class="quaf-tape-img" loading="eager">
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <!-- White section start: seamlessly at the bottom under the blue bar -->
        <div class="absolute inset-x-0 bottom-0 h-6 sm:h-7 lg:h-8 bg-white pointer-events-none" style="z-index: 5;"></div>
    </div>
</section>

<!-- =====================================================================
     SECTION 4: STANDINGS & RESULTS (WHITE THEME)
     Matching user screenshot: Left Team Standings, Right Latest Results
     Title: Festival Standings (no "Academic Groups", no descriptions)
     ===================================================================== -->
<section id="standings" class="pt-3 sm:pt-4 pb-16 sm:pb-24 bg-white border-b border-slate-200/90 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Clean Section Header -->
        <div class="mb-10 sm:mb-12">
            <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase block mb-1 animate-subheading">
                STANDINGS & TALLIES
            </span>
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
                                                @if($result->program?->eligibility)
                                                    <span class="text-xs font-mono text-slate-500 uppercase">
                                                        {{ $result->program->eligibility }}
                                                    </span>
                                                @endif
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
                    Official Media Broadcasts
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

        <div class="columns-2 sm:columns-3 lg:columns-4 gap-4 sm:gap-6 space-y-4 sm:space-y-6">
            @foreach($galleryPreview as $photo)
                <a href="{{ route('gallery.index') }}" 
                   class="break-inside-avoid group relative rounded-2xl sm:rounded-3xl overflow-hidden bg-slate-950 shadow-md hover:shadow-2xl hover:-translate-y-1 transition-all duration-500 ease-out border border-slate-200/80 hover:border-slate-300 cursor-pointer block">
                    <!-- Photo in original aspect ratio (16:9, 9:16 vertical, square) -->
                    <img src="{{ $photo->image_path }}" 
                         alt="QUAF Gallery" 
                         class="w-full h-auto block rounded-2xl sm:rounded-3xl object-cover transition-transform duration-700 ease-out group-hover:scale-105" 
                         loading="lazy">
                    
                    <!-- Clean Hover Overlay with Glassy Action Icon -->
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center p-4 z-10">
                        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/25 hover:bg-white/40 text-white backdrop-blur-md border border-white/40 flex items-center justify-center shadow-lg transform group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                            </svg>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

    </div>
</section>
@endif

<!-- =====================================================================
     JUNCTION: SCROLL-DRIVEN INTERACTIVE GREEN BAR (GALLERY TO NEWS)
     Sunburst elements rotate clockwise on downward scroll, and reverse
     counter-clockwise on upward scroll in real-time sync with screen scroll.
     ===================================================================== -->
<div class="relative w-full overflow-hidden bg-[#009444] py-3 sm:py-3.5 select-none z-10 border-y border-[#007a37] shadow-inner"
     x-data="{
         rotDeg: 0,
         trackX: 0,
         ticking: false,
         update() {
             const sy = window.pageYOffset || document.documentElement.scrollTop;
             this.rotDeg = (sy * 0.45) % 360;
             this.trackX = -((sy * 0.3) % 480);
             this.ticking = false;
         },
         onScroll() {
             if (!this.ticking) {
                 window.requestAnimationFrame(() => this.update());
                 this.ticking = true;
             }
         }
     }"
     x-init="window.addEventListener('scroll', () => onScroll(), { passive: true }); update();">

    <div class="flex w-max items-center will-change-transform"
         :style="'transform: translate3d(' + trackX + 'px, 0, 0)'">
        @for ($i = 0; $i < 16; $i++)
            <div class="flex items-center gap-4 sm:gap-6 px-4 shrink-0">
                <!-- Spinning Yellow Sunburst Emblem (Scroll-driven: rotates forward down, reverse up) -->
                <div class="w-6 h-6 sm:w-7 sm:h-7 shrink-0 flex items-center justify-center will-change-transform"
                     :style="'transform: rotate(' + rotDeg + 'deg)'">
                    <svg class="w-full h-full text-[#f3bd2e]" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="14" cy="14" r="2.4" fill="currentColor"/>
                        <g fill="currentColor">
                            <polygon points="14 11.8 13.7 0.2 14.3 0.2 14.1 11.8"/>
                            <polygon points="13.3 11.9 9.5 0.9 10.1 0.7 13.4 11.9"/>
                            <polygon points="12.7 12.2 5.6 2.9 6.1 2.6 12.8 12.2"/>
                            <polygon points="12.2 12.7 2.6 6.1 2.9 5.6 12.3 12.6"/>
                            <polygon points="11.9 13.3 0.7 10.1 0.9 9.5 11.9 13.2"/>
                            <polygon points="11.8 14 0.2 14.3 0.2 13.7 11.8 13.9"/>
                            <polygon points="11.9 14.7 0.9 18.5 0.7 17.9 11.9 14.6"/>
                            <polygon points="12.2 15.3 2.9 22.4 2.6 21.9 12.2 15.2"/>
                            <polygon points="12.7 15.8 6.1 25.4 5.6 25.1 12.7 15.7"/>
                            <polygon points="13.3 16.1 10.1 27.3 9.5 27.1 13.3 16.1"/>
                            <polygon points="14 16.2 14.3 27.8 13.7 27.8 13.9 16.2"/>
                            <polygon points="14.7 16.1 18.5 27.1 17.9 27.3 14.6 16.1"/>
                            <polygon points="15.3 15.8 22.4 25.1 21.9 25.4 15.2 15.7"/>
                            <polygon points="15.8 15.3 25.4 21.9 25.1 22.4 15.7 15.2"/>
                            <polygon points="16.1 14.7 27.3 17.9 27.1 18.5 16.1 14.6"/>
                            <polygon points="16.2 14 27.8 13.7 27.8 14.3 16.2 14.1"/>
                            <polygon points="16.1 13.3 27.1 9.5 27.3 10.1 16.1 13.4"/>
                            <polygon points="15.8 12.7 25.1 5.6 25.4 6.1 15.7 12.8"/>
                            <polygon points="15.3 12.2 21.9 2.6 22.4 2.9 15.2 12.3"/>
                            <polygon points="14.7 11.9 17.9 0.7 18.5 0.9 14.6 11.9"/>
                        </g>
                    </svg>
                </div>

                <!-- Branding Typography -->
                <span class="font-rockwell font-black text-white text-xs sm:text-sm tracking-wider uppercase">QUAF 2026</span>
                <span class="text-emerald-200/50 text-xs font-mono">•</span>
                <span class="font-rockwell font-bold text-amber-300 text-[11px] sm:text-xs tracking-widest uppercase">ĀDABĪC INHERITANCE</span>
                <span class="text-emerald-200/50 text-xs font-mono">•</span>
                <span class="font-rockwell font-light text-white text-[11px] sm:text-xs tracking-wider uppercase">MARKAZ CULTURAL FESTIVAL</span>
                <span class="text-emerald-200/50 text-xs font-mono">•</span>
            </div>
        @endfor
    </div>
</div>

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
                            <h3 class="text-lg font-anek font-bold text-slate-900 group-hover:text-[#be1e2d] transition-colors leading-snug mb-2">
                                {{ $article->title }}
                            </h3>
                            <p class="text-xs sm:text-sm font-anek text-slate-600 line-clamp-2 leading-relaxed">
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
                    <h3 class="font-sora text-xl font-bold text-slate-900 mb-2">Official Theme Brochure</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-6">
                        Explore the complete 20-page festival theme brochure exploring Ādabīc Inheritance, philosophy, and creative ethos.
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
