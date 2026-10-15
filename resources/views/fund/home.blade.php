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

        <!-- Symmetrical Festival Ambient Mesh Lighting (Standard & Polished) -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[340px] sm:w-[640px] h-[340px] sm:h-[460px] bg-[#be1e2d]/18 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 sm:w-96 h-64 sm:h-96 bg-[#f3bd2e]/12 rounded-full blur-[130px] pointer-events-none"></div>
        <div class="absolute -bottom-10 left-1/2 -translate-x-1/2 w-72 sm:w-[480px] h-48 bg-[#005c94]/10 rounded-full blur-[120px] pointer-events-none"></div>

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
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-xs sm:text-sm font-black text-slate-900 uppercase break-words leading-tight">
                                            {{ $st->name }}
                                        </h3>
                                        @if($st->group)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold shrink-0"
                                                  style="background-color: {{ $st->group->color_hex }}15; color: {{ $st->group->color_hex }}; border: 1px solid {{ $st->group->color_hex }}30;">
                                                {{ $st->group->code ?: $st->group->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center flex-wrap gap-x-2 gap-y-0.5 text-[10px] sm:text-xs font-mono text-slate-500 mt-1">
                                        <span>Chest: <strong class="text-slate-700">{{ $st->chest_number ?: $st->student_id }}</strong></span>
                                        <span>•</span>
                                        <span>Class: {{ $st->class_level ?? '-' }}</span>
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
                                    <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight break-words">
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
         STUDENT SELECTION & LOGIN MODAL (APPLE-GRADE WHITE DESIGN WITH SMOOTH TRANSITIONS)
         ===================================================================== -->
    <div x-show="showLoginModal" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/80 backdrop-blur-md"
         style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; height: 100dvh !important; z-index: 99999 !important; display: flex !important; align-items: center !important; justify-content: center !important; background-color: rgba(10, 15, 30, 0.8) !important; -webkit-backdrop-filter: blur(8px) !important; backdrop-filter: blur(8px) !important;"
         @keydown.escape.window="showLoginModal = false">
        
        <!-- Backdrop Click to Close -->
        <div class="fixed inset-0" style="position: absolute; inset: 0; width: 100%; height: 100%;" @click="showLoginModal = false"></div>

        <!-- Pure White Apple-Style Dialog Card -->
        <div x-show="showLoginModal"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
             x-transition:enter-start="opacity-0 translate-y-6 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-6 scale-95"
             class="relative w-full max-w-lg bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100 z-10 text-slate-900 space-y-5"
             style="position: relative !important; z-index: 100000 !important; background-color: #ffffff !important; color: #0f172a !important; border-radius: 24px !important; box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.45) !important; max-height: calc(100dvh - 32px) !important; overflow-y: auto !important;"
             @click.stop>
            
            <!-- Apple-Style Circular Close Button -->
            <button type="button" 
                    @click="showLoginModal = false"
                    class="absolute top-5 right-5 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-800 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- Modal Header -->
            <div class="flex items-center gap-3.5 pr-8">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#be1e2d] to-amber-500 flex items-center justify-center text-white shadow-md shadow-red-500/20 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200/80">
                        Student Fund Desk
                    </span>
                    <h3 class="font-sora text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mt-0.5">
                        Student Login
                    </h3>
                    <p class="text-xs text-slate-500 font-anek">
                        രസീത് നൽകാൻ നിങ്ങളുടെ പേര് സെലക്ട് ചെയ്യുക
                    </p>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <!-- 1. Class Dropdown -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 font-mono">
                        Select Class / ക്ലാസ്സ്
                    </label>
                    <select x-model="selectedClass" @change="searchStudents()"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 focus:bg-white border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/25 focus:border-[#be1e2d] transition-all">
                        <option value="">All Classes / എല്ലാ ക്ലാസ്സും</option>
                        @foreach($classes as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Group Dropdown (Optional) -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 font-mono flex items-center justify-between">
                        <span>Group / ഗ്രൂപ്പ്</span>
                        <span class="text-[9px] text-slate-400 font-normal lowercase">(optional)</span>
                    </label>
                    <select x-model="selectedGroup" @change="searchStudents()"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 focus:bg-white border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/25 focus:border-[#be1e2d] transition-all">
                        <option value="">All Groups / എല്ലാ ഗ്രൂപ്പും</option>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 3. Name Search Input -->
            <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 font-mono">
                    Type Name or Chest Number
                </label>
                <div class="relative">
                    <input type="text" x-model="searchQuery" @input.debounce.250ms="searchStudents()"
                           placeholder="Type to filter name or chest no..."
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 focus:bg-white border border-slate-200 text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/25 focus:border-[#be1e2d] transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Filtered Students Result List -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-[11px] text-slate-500 font-mono">
                    <span class="font-bold uppercase tracking-wider">Matching Students</span>
                    <span x-text="students.length + ' found'"></span>
                </div>

                <div class="max-h-56 overflow-y-auto space-y-1.5 p-2 bg-slate-50/70 border border-slate-200/90 rounded-2xl">
                    <template x-if="loading">
                        <div class="py-8 text-center text-xs text-slate-400 font-mono flex items-center justify-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-[#be1e2d]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Loading students...</span>
                        </div>
                    </template>

                    <template x-if="!loading && students.length === 0">
                        <div class="py-8 text-center text-xs text-slate-400 font-anek">
                            വിദ്യാർത്ഥികളെ കണ്ടെത്താനായില്ല. ക്ലാസ്സ് മാറ്റുകയോ പേര് ശരിയായി ടൈപ്പ് ചെയ്യുകയോ ചെയ്യുക.
                        </div>
                    </template>

                    <template x-for="s in students" :key="s.id">
                        <div @click="selectStudent(s)" 
                             class="p-2.5 sm:p-3 rounded-xl border transition-all duration-150 cursor-pointer flex items-center justify-between gap-3 select-none"
                             :class="selectedStudent && selectedStudent.id === s.id 
                                 ? 'bg-red-50/90 border-[#be1e2d] text-slate-950 shadow-xs ring-1 ring-[#be1e2d]/30' 
                                 : 'bg-white border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/90 text-slate-800 shadow-2xs'">
                            <div class="min-w-0 flex-1">
                                <div class="text-xs sm:text-sm font-bold text-slate-900 break-words leading-snug" x-text="s.name"></div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                    <span>Chest: <strong class="text-slate-800" x-text="s.chest_number"></strong></span>
                                    <span>•</span>
                                    <span>Class: <span class="text-slate-700" x-text="s.class_level || '-'"></span></span>
                                </div>
                            </div>

                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold shrink-0 uppercase tracking-wide"
                                  :style="'background-color: ' + (s.group_color || '#be1e2d') + '15; color: ' + (s.group_color || '#be1e2d') + '; border: 1px solid ' + (s.group_color || '#be1e2d') + '30;'"
                                  x-text="s.group_code || s.group_name">
                            </span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Login Form / Action -->
            <form method="POST" action="{{ route('fund.student.login') }}" class="pt-1">
                @csrf
                <input type="hidden" name="student_id" :value="selectedStudent ? selectedStudent.id : ''">

                <button type="submit" 
                        :disabled="!selectedStudent"
                        class="w-full py-3.5 px-6 rounded-2xl font-black text-xs uppercase tracking-wider transition-all duration-200 shadow-lg flex items-center justify-center gap-2 select-none"
                        :class="selectedStudent 
                            ? 'bg-gradient-to-r from-[#be1e2d] via-red-600 to-[#be1e2d] hover:brightness-110 text-white shadow-red-600/25 cursor-pointer active:scale-[0.99]' 
                            : 'bg-slate-100 text-slate-400 cursor-not-allowed border border-slate-200/60'">
                    <span x-text="selectedStudent ? 'Enter Desk as ' + selectedStudent.name : 'Select Your Name to Proceed'"></span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

        </div>
    </div>

</div>

@endsection

