@extends('layouts.public', ['title' => 'QUAF 09 — Festival Fund & Contribution Portal'])

@section('content')

<script>
function fundHomePage() {
    return {
        showLoginModal: {{ request()->has('login') || request()->has('open_login') ? 'true' : 'false' }},
        selectedClass: '',
        selectedGroup: '',
        searchQuery: '',
        selectedStudent: null,
        students: [],
        loading: false,
        init() {
            this.searchStudents();
        },
        async searchStudents() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.selectedClass) params.append('class_level', this.selectedClass);
                if (this.selectedGroup) params.append('group_id', this.selectedGroup);
                if (this.searchQuery) params.append('query', this.searchQuery);
                
                const res = await fetch('{{ route('fund.api.search-students') }}?' + params.toString());
                this.students = await res.json();
            } catch (e) {
                console.error('Search error:', e);
            } finally {
                this.loading = false;
            }
        },
        selectStudent(s) {
            this.selectedStudent = s;
        }
    };
}
</script>

<div x-data="fundHomePage()" class="relative w-full">

    <!-- =====================================================================
         SECTION 1: HERO HEADER (BLACK THEME / OBSIDIAN APPLE GLASS UP TO GIF)
         ===================================================================== -->
    <section class="relative bg-[#07070a] text-white pt-20 sm:pt-28 lg:pt-36 pb-0 z-10 overflow-hidden">

        <!-- Multi-Color Festival Mesh Glows -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[340px] sm:w-[680px] h-[340px] sm:h-[480px] bg-[#be1e2d]/20 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute top-10 left-10 w-72 sm:w-96 h-72 sm:h-96 bg-[#f3bd2e]/15 rounded-full blur-[130px] pointer-events-none"></div>
        <div class="absolute bottom-10 right-10 w-72 sm:w-96 h-72 sm:h-96 bg-[#005c94]/15 rounded-full blur-[130px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-1/3 w-64 h-64 bg-[#009444]/10 rounded-full blur-[110px] pointer-events-none"></div>

        <!-- Rotating Yellow Pattern Burst (Hero Background Accent) -->
        <style>
            @keyframes heroPatternSlideIn {
                0% { transform: translate3d(100%, 0, 0); opacity: 0; }
                100% { transform: translate3d(36%, 0, 0); opacity: 0.95; }
            }
            @keyframes heroPatternSpin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
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
                .hero-pattern-wrapper { bottom: -35px; width: 230px; height: 230px; }
                .hero-pattern-svg { animation: heroPatternSpin 72s linear infinite; }
            }
            @media (min-width: 640px) {
                .hero-pattern-wrapper { bottom: -75px; width: 360px; height: 360px; }
            }
            @media (min-width: 1024px) {
                .hero-pattern-wrapper { bottom: -95px; width: 440px; height: 440px; }
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
            
            <!-- Hero Center: Alternating Official Theme Logo & QUAF Festival Logo -->
            <div class="relative w-full max-w-xl mb-6 sm:mb-8 flex flex-col items-center text-center"
                 x-data="{ activeLogo: 0 }"
                 x-init="setInterval(() => { activeLogo = 1 - activeLogo; }, 4200)">
                
                <div class="relative w-full h-[112px] xs:h-[135px] sm:h-[165px] flex items-center justify-center">
                    <!-- 1. Adabic Inheritance Logo -->
                    <img src="{{ asset('images/adabic-inheritance-web.svg') }}" 
                         alt="Adabic Inheritance — QUAF" 
                         class="max-w-[210px] xs:max-w-[255px] sm:max-w-[336px] lg:max-w-[384px] max-h-[105px] xs:max-h-[128px] sm:max-h-[158px] w-auto h-auto object-contain transition-all duration-700 transform drop-shadow-[0_12px_40px_rgba(255,255,255,0.08)]"
                         :class="activeLogo === 0 ? 'opacity-100 scale-100 relative z-10' : 'opacity-0 scale-95 absolute pointer-events-none z-0'">

                    <!-- 2. QUAF Festival Logo -->
                    <img src="{{ asset('images/quaf-logo-hero.svg') }}" 
                         alt="QUAF — Markaz Cultural Festival" 
                         class="max-w-[195px] xs:max-w-[240px] sm:max-w-[336px] lg:max-w-[384px] max-h-[105px] xs:max-h-[128px] sm:max-h-[158px] w-auto h-auto object-contain transition-all duration-700 transform drop-shadow-[0_12px_40px_rgba(255,255,255,0.12)]"
                         :class="activeLogo === 1 ? 'opacity-100 scale-100 relative z-10' : 'opacity-0 scale-95 absolute pointer-events-none z-0'">
                </div>

                <!-- Festival Info Pills -->
                <div class="mt-5 sm:mt-6 flex flex-wrap items-center justify-center gap-2.5 sm:gap-3">
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-[11px] sm:text-xs font-mono tracking-widest uppercase bg-white/10 text-white/90 border border-white/15 backdrop-blur-md">
                        Markazu Saquafathi Sunniyya
                    </span>
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-[11px] sm:text-xs font-mono tracking-widest uppercase bg-[#f3bd2e]/20 text-[#f3bd2e] border border-[#f3bd2e]/30 font-bold backdrop-blur-md">
                        Festival Fund Portal
                    </span>
                </div>
            </div>

            <!-- Title & Subtitle -->
            <div class="max-w-2xl mx-auto mb-6">
                <h1 class="text-2xl sm:text-4xl font-black font-sora tracking-tight text-white">
                    QUAF '09 Festival Fund Portal
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 font-anek mt-2">
                    മഹത്തായ ക്വാഫ് ഫെസ്റ്റിവലിന്റെ വിജയത്തിനായി വിദ്യാർത്ഥികൾക്ക് നേരിട്ട് രസീത് വിതരണം ചെയ്യാനും സംഭാവനകൾ രേഖപ്പെടുത്താനുമുള്ള ഔദ്യോഗിക പ്ലാറ്റ്‌ഫോം.
                </p>
            </div>

            <!-- Primary Action CTA (Only Student Desk / Login Button - No Admin Ledger Button) -->
            <div class="flex items-center justify-center max-w-md mb-10 sm:mb-12">
                @if($currentStudent)
                    <a href="{{ route('fund.student.desk') }}" 
                       class="px-8 py-3.5 rounded-2xl font-black text-xs sm:text-sm tracking-wider uppercase bg-gradient-to-r from-amber-500 via-amber-400 to-amber-500 text-slate-950 hover:brightness-110 shadow-lg shadow-amber-500/30 border border-amber-300/40 transition-all transform hover:-translate-y-0.5 text-center flex items-center gap-2">
                        <span>Enter My Fund Desk ({{ $currentStudent->name }})</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                @else
                    <button type="button" 
                            @click="showLoginModal = true" 
                            class="px-8 py-3.5 rounded-2xl font-black text-xs sm:text-sm tracking-wider uppercase bg-gradient-to-r from-[#be1e2d] via-red-600 to-[#be1e2d] text-white hover:brightness-110 shadow-lg shadow-red-600/30 border border-red-500/30 transition-all transform hover:-translate-y-0.5 text-center cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        <span>Student Login to Issue Receipt</span>
                    </button>
                @endif
            </div>

        </div>

        <!-- JUNCTION: INFINITE ANIMATED GIF RIBBON (TRANSPARENT BACKGROUND) -->
        <div class="relative w-full overflow-hidden bg-transparent select-none p-0 m-0 leading-none" style="z-index: 10;">
            <style>
                @keyframes ribbonScrollLeft {
                    0% { transform: translate3d(0, 0, 0); }
                    100% { transform: translate3d(-50%, 0, 0); }
                }
                .animate-ribbon-loop {
                    display: flex;
                    width: max-content;
                    animation: ribbonScrollLeft 44s linear infinite;
                    will-change: transform;
                }
                .quaf-theme-ribbon-img {
                    height: 16px !important;
                    max-height: 16px !important;
                    width: auto !important;
                    object-fit: contain;
                    display: block !important;
                    flex-shrink: 0;
                }
                @media (min-width: 640px) {
                    .animate-ribbon-loop { animation: ribbonScrollLeft 32s linear infinite; }
                    .quaf-theme-ribbon-img { height: 22px !important; max-height: 22px !important; }
                }
                @media (min-width: 1024px) {
                    .quaf-theme-ribbon-img { height: 27px !important; max-height: 27px !important; }
                }
            </style>

            <div class="flex w-max animate-ribbon-loop pointer-events-none p-0 m-0 leading-none">
                <div class="flex shrink-0 items-center p-0 m-0 leading-none">
                    <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="quaf-theme-ribbon-img m-0 p-0">
                    <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="quaf-theme-ribbon-img m-0 p-0">
                    <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="quaf-theme-ribbon-img m-0 p-0">
                </div>
                <div class="flex shrink-0 items-center p-0 m-0 leading-none">
                    <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="quaf-theme-ribbon-img m-0 p-0">
                    <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="quaf-theme-ribbon-img m-0 p-0">
                    <img src="{{ asset('images/footer_web_1.gif') }}" alt="QUAF Theme Ribbon" class="quaf-theme-ribbon-img m-0 p-0">
                </div>
            </div>
        </div>

    </section>

    <!-- =====================================================================
         SECTION 2: TOP COLLECTED STUDENTS LEADERBOARD (LIST TYPE)
         1st: Gold, 2nd: Silver, 3rd: Bronze
         ===================================================================== -->
    <section class="py-12 sm:py-16 bg-slate-50 text-slate-900 border-b border-slate-200/90 relative overflow-hidden">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            
            <!-- Header (Clean without extra buttons) -->
            <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-10">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-[11px] font-mono font-bold uppercase tracking-wider">
                    Star Fund Collectors
                </span>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 font-sora mt-2">
                    Top Collected Students
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 font-anek mt-1">
                    ഫെസ്റ്റിവൽ ഫണ്ട് ശേഖരണത്തിൽ മുന്നിലെത്തിയ വിദ്യാർത്ഥികളുടെ റാങ്കിംഗ് ലിസ്റ്റ്.
                </p>
            </div>

            <!-- Top Students Ranking List (List Type) -->
            @if($topStudents->isNotEmpty())
                <div class="space-y-3">
                    @foreach($topStudents as $index => $st)
                        @php
                            $rank = $index + 1;
                            $isGold = ($rank === 1);
                            $isSilver = ($rank === 2);
                            $isBronze = ($rank === 3);

                            $cardBg = match($rank) {
                                1 => 'bg-gradient-to-r from-amber-500/10 via-amber-400/5 to-white border-2 border-amber-400/90 shadow-md shadow-amber-400/10',
                                2 => 'bg-gradient-to-r from-slate-200/30 via-slate-100/15 to-white border-2 border-slate-300 shadow-xs',
                                3 => 'bg-gradient-to-r from-amber-700/10 via-amber-600/5 to-white border-2 border-amber-700/35 shadow-2xs',
                                default => 'bg-white border border-slate-200/80 shadow-2xs'
                            };

                            $rankBadge = match($rank) {
                                1 => 'bg-gradient-to-br from-amber-400 via-amber-500 to-amber-600 text-slate-950 font-black shadow-md shadow-amber-500/30',
                                2 => 'bg-gradient-to-br from-slate-200 via-slate-300 to-slate-400 text-slate-900 font-black shadow-xs',
                                3 => 'bg-gradient-to-br from-amber-700 via-amber-600 to-amber-800 text-white font-black shadow-xs',
                                default => 'bg-slate-100 text-slate-600 border border-slate-200 font-bold'
                            };

                            $medalTag = match($rank) {
                                1 => 'GOLD',
                                2 => 'SILVER',
                                3 => 'BRONZE',
                                default => null
                            };
                        @endphp
                        
                        <div class="p-3.5 sm:p-4 rounded-2xl transition-all hover:shadow-md flex items-center justify-between gap-3 sm:gap-4 {{ $cardBg }}">
                            
                            <!-- Left: Rank & Student Identity -->
                            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                                
                                <!-- Rank Badge with Gold/Silver/Bronze -->
                                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex flex-col items-center justify-center shrink-0 font-mono {{ $rankBadge }}">
                                    <span class="text-xs sm:text-sm leading-none">
                                        @if($isGold) 1st @elseif($isSilver) 2nd @elseif($isBronze) 3rd @else #{{ $rank }} @endif
                                    </span>
                                    @if($medalTag)
                                        <span class="text-[8px] font-black uppercase tracking-tighter opacity-90 leading-none mt-0.5">{{ $medalTag }}</span>
                                    @endif
                                </div>

                                <!-- Student Details -->
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-xs sm:text-sm font-black text-slate-900 uppercase truncate">
                                            {{ $st->name }}
                                        </h3>
                                        @if($st->group)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold shrink-0 hidden xs:inline-block"
                                                  style="background-color: {{ $st->group->color_hex }}15; color: {{ $st->group->color_hex }}; border: 1px solid {{ $st->group->color_hex }}30;">
                                                {{ $st->group->code }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] sm:text-xs font-mono text-slate-500 mt-0.5">
                                        <span>Chest: <strong class="text-slate-700">{{ $st->chest_number ?: $st->student_id }}</strong></span>
                                        <span>•</span>
                                        <span>Class: {{ $st->class_level ?? '-' }}</span>
                                        @if($st->group)
                                            <span class="xs:hidden">• {{ $st->group->name }}</span>
                                        @endif
                                    </div>
                                </div>

                            </div>

                            <!-- Right: Amount Metric -->
                            <div class="text-right shrink-0 font-mono">
                                <div class="text-base sm:text-xl font-black {{ $isGold ? 'text-amber-800' : ($isSilver ? 'text-slate-900' : ($isBronze ? 'text-amber-900' : 'text-emerald-700')) }}">
                                    ₹{{ number_format($st->total_collected ?? 0, 0) }}
                                </div>
                                <div class="text-[10px] sm:text-[11px] text-slate-400">
                                    {{ $st->receipts_count ?? 0 }} receipts
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-3xl p-8 sm:p-10 text-center border border-slate-200/80 shadow-xs max-w-lg mx-auto">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-base font-black text-slate-900">രസീത് വിതരണം ആരംഭിച്ചു!</h3>
                    <p class="text-xs text-slate-500 font-anek mt-1">
                        വിദ്യാർത്ഥികൾ നൽകുന്ന സംഭാവന രസീതുകൾ അനുസരിച്ച് തത്സമയം ടോപ്പ് കളക്റ്റഡ് സ്റ്റുഡന്റ്സ് ലിസ്റ്റ് ഇവിടെ അപ്‌ഡേറ്റ് ചെയ്യപ്പെടും.
                    </p>
                </div>
            @endif

        </div>
    </section>

    <!-- =====================================================================
         SECTION 3: TOP COLLECTED GROUPS LEADERBOARD (LIST TYPE)
         1st: Gold, 2nd: Silver, 3rd: Bronze
         ===================================================================== -->
    <section class="py-12 sm:py-16 bg-white text-slate-900 border-b border-slate-200/90 relative overflow-hidden">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            
            <!-- Header -->
            <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-10">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-900 text-[11px] font-mono font-bold uppercase tracking-wider">
                    Group Standings
                </span>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 font-sora mt-2">
                    Top Collected Groups
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 font-anek mt-1">
                    ഗ്രൂപ്പുകളുടെ അടിസ്ഥാനത്തിലുള്ള ആകെ ഫണ്ട് കളക്ഷൻ റാങ്കിംഗ് ലിസ്റ്റ്.
                </p>
            </div>

            <!-- Groups Ranking List (List Type) -->
            <div class="space-y-3">
                @foreach($topGroups as $index => $grp)
                    @php
                        $rank = $index + 1;
                        $collected = (float) ($grp->total_collected ?? 0);
                        $count = (int) ($grp->receipts_count ?? 0);

                        $isGold = ($rank === 1);
                        $isSilver = ($rank === 2);
                        $isBronze = ($rank === 3);

                        $cardBg = match($rank) {
                            1 => 'bg-gradient-to-r from-amber-500/10 via-amber-400/5 to-white border-2 border-amber-400/90 shadow-md shadow-amber-400/10',
                            2 => 'bg-gradient-to-r from-slate-200/30 via-slate-100/15 to-white border-2 border-slate-300 shadow-xs',
                            3 => 'bg-gradient-to-r from-amber-700/10 via-amber-600/5 to-white border-2 border-amber-700/35 shadow-2xs',
                            default => 'bg-slate-50 border border-slate-200/80 shadow-2xs'
                        };

                        $rankBadge = match($rank) {
                            1 => 'bg-gradient-to-br from-amber-400 via-amber-500 to-amber-600 text-slate-950 font-black shadow-md shadow-amber-500/30',
                            2 => 'bg-gradient-to-br from-slate-200 via-slate-300 to-slate-400 text-slate-900 font-black shadow-xs',
                            3 => 'bg-gradient-to-br from-amber-700 via-amber-600 to-amber-800 text-white font-black shadow-xs',
                            default => 'bg-slate-200/80 text-slate-700 border border-slate-300 font-bold'
                        };

                        $medalTag = match($rank) {
                            1 => 'GOLD',
                            2 => 'SILVER',
                            3 => 'BRONZE',
                            default => null
                        };
                    @endphp

                    <div class="p-4 sm:p-5 rounded-2xl transition-all hover:shadow-md flex items-center justify-between gap-3 sm:gap-4 relative overflow-hidden {{ $cardBg }}">
                        <!-- Left Vertical Color Accent Bar -->
                        <div class="absolute top-0 bottom-0 left-0 w-2" style="background-color: {{ $grp->color_hex ?? '#be1e2d' }};"></div>

                        <!-- Left: Rank & Group Info -->
                        <div class="flex items-center gap-3 sm:gap-4 min-w-0 pl-1.5">
                            
                            <!-- Rank Badge -->
                            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl flex flex-col items-center justify-center shrink-0 font-mono {{ $rankBadge }}">
                                <span class="text-xs sm:text-sm leading-none">
                                    @if($isGold) 1st @elseif($isSilver) 2nd @elseif($isBronze) 3rd @else #{{ $rank }} @endif
                                </span>
                                @if($medalTag)
                                    <span class="text-[8px] font-black uppercase tracking-tighter opacity-90 leading-none mt-0.5">{{ $medalTag }}</span>
                                @endif
                            </div>

                            <!-- Group Details -->
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] sm:text-xs font-mono font-black"
                                          style="background-color: {{ $grp->color_hex ?? '#be1e2d' }}20; color: {{ $grp->color_hex ?? '#be1e2d' }};">
                                        {{ $grp->code }}
                                    </span>
                                    <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight truncate">
                                        {{ $grp->name }}
                                    </h3>
                                </div>
                                <div class="text-[10px] sm:text-xs font-mono text-slate-500 mt-0.5">
                                    <span>Total Issued: <strong class="text-slate-700">{{ $count }}</strong> receipts</span>
                                </div>
                            </div>

                        </div>

                        <!-- Right: Total Collected Metric -->
                        <div class="text-right shrink-0 font-mono">
                            <div class="text-base sm:text-2xl font-black {{ $isGold ? 'text-amber-800' : ($isSilver ? 'text-slate-900' : ($isBronze ? 'text-amber-900' : 'text-slate-900')) }}">
                                ₹{{ number_format($collected, 0) }}
                            </div>
                            <div class="text-[10px] sm:text-[11px] text-slate-400">
                                Total Collection
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

        </div>
    </section>

    <!-- =====================================================================
         STUDENT SELECTION & LOGIN MODAL (OUTSIDE OVERFLOW CONTAINERS AT ROOT LEVEL)
         ===================================================================== -->
    <div x-show="showLoginModal" 
         x-cloak 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
         @keydown.escape.window="showLoginModal = false">
        
        <div class="relative w-full max-w-lg bg-slate-900 border border-slate-700/80 rounded-3xl p-6 sm:p-8 shadow-2xl text-white space-y-5"
             @click.away="showLoginModal = false">
            
            <!-- Close Button -->
            <button type="button" @click="showLoginModal = false"
                    class="absolute top-5 right-5 p-2 rounded-xl text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <!-- Modal Header -->
            <div>
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-amber-400 bg-amber-400/10 px-3 py-1 rounded-full border border-amber-400/20">
                    Student Login
                </span>
                <h3 class="text-xl sm:text-2xl font-black mt-2 text-white">
                    നിങ്ങളുടെ പേര് തിരഞ്ഞെടുക്കുക
                </h3>
                <p class="text-xs text-slate-400 font-anek mt-1">
                    ക്ലാസ്സ് സെലക്ട് ചെയ്ത ശേഷം നിങ്ങളുടെ പേര് സെർച്ച് ചെയ്ത് ലോഗിൻ ചെയ്യുക.
                </p>
            </div>

            <!-- Filter Controls -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <!-- 1. Class Dropdown -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono">
                        Select Class / ക്ലാസ്സ്
                    </label>
                    <select x-model="selectedClass" @change="searchStudents()"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs font-semibold text-white focus:outline-none focus:border-amber-400">
                        <option value="">All Classes / എല്ലാ ക്ലാസ്സും</option>
                        @foreach($classes as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Group Dropdown (Optional! Not required) -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono flex items-center justify-between">
                        <span>Group / ഗ്രൂപ്പ്</span>
                        <span class="text-[9px] text-slate-500 normal-case">(Optional)</span>
                    </label>
                    <select x-model="selectedGroup" @change="searchStudents()"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs font-semibold text-white focus:outline-none focus:border-amber-400">
                        <option value="">All Groups / എല്ലാ ഗ്രൂപ്പും</option>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 3. Name Search Input -->
            <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono">
                    Type Your Name / Chest Number
                </label>
                <div class="relative">
                    <input type="text" x-model="searchQuery" @input.debounce.300ms="searchStudents()"
                           placeholder="Type to filter name or chest no..."
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-400">
                    <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Filtered Students Result List -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                    <span>Matching Students:</span>
                    <span x-text="students.length + ' found'"></span>
                </div>

                <div class="max-h-52 overflow-y-auto space-y-1.5 pr-1 border border-slate-800 rounded-2xl p-2 bg-slate-950/60">
                    <template x-if="loading">
                        <div class="py-6 text-center text-xs text-slate-400 font-mono">Loading students...</div>
                    </template>

                    <template x-if="!loading && students.length === 0">
                        <div class="py-6 text-center text-xs text-slate-500">
                            No students found. Try selecting your class or clearing search.
                        </div>
                    </template>

                    <template x-for="s in students" :key="s.id">
                        <div @click="selectStudent(s)" 
                             class="p-2.5 sm:p-3 rounded-xl border transition-all cursor-pointer flex items-center justify-between gap-3"
                             :class="selectedStudent && selectedStudent.id === s.id ? 'bg-amber-500/20 border-amber-400 text-white shadow-sm' : 'bg-slate-800/80 border-slate-700/60 hover:bg-slate-800 text-slate-200'">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-black shrink-0"
                                      :style="'background-color: ' + (s.group_color || '#be1e2d') + '33; color: ' + (s.group_color || '#f3bd2e') + ';'">
                                    <span x-text="s.chest_number ? s.chest_number.slice(-2) : 'Q'"></span>
                                </span>
                                <div class="truncate">
                                    <div class="text-xs font-bold truncate" x-text="s.name"></div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        Chest: <span class="font-bold text-slate-300" x-text="s.chest_number"></span> • Class: <span x-text="s.class_level"></span>
                                    </div>
                                </div>
                            </div>

                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold shrink-0"
                                  style="background-color: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);"
                                  :style="'color: ' + (s.group_color || '#f3bd2e') + ';'"
                                  x-text="s.group_name">
                            </span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Login Form / Action -->
            <form method="POST" action="{{ route('fund.student.login') }}">
                @csrf
                <input type="hidden" name="student_id" :value="selectedStudent ? selectedStudent.id : ''">

                <button type="submit" 
                        :disabled="!selectedStudent"
                        class="w-full py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider transition-all shadow-lg flex items-center justify-center gap-2"
                        :class="selectedStudent ? 'bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-amber-500/20 cursor-pointer active:scale-98' : 'bg-slate-800 text-slate-500 cursor-not-allowed'">
                    <span x-text="selectedStudent ? 'Enter Desk as ' + selectedStudent.name : 'Select Your Name to Proceed'"></span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>

        </div>
    </div>

</div>

@endsection

