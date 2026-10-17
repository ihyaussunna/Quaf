@extends('layouts.public', ['title' => 'QUAF 2026 | ₹100 Challenge — Ādabic Inheritance', 'hideHeader' => true])

@section('content')
<div x-data="quafDonationPage()" class="relative w-full font-sora">

    <!-- =====================================================================
         SECTION 1: HERO HEADER (MATCHING HOME PAGE HERO - STRICTLY NO BUTTONS)
         ===================================================================== -->
    <section class="relative bg-[#07070a] text-white pt-12 sm:pt-16 lg:pt-20 pb-0 z-10 overflow-hidden">

        <!-- Symmetrical Festival Ambient Mesh Lighting -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[340px] sm:w-[680px] h-[340px] sm:h-[480px] bg-[#be1e2d]/20 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 sm:w-96 h-64 sm:h-96 bg-[#f3bd2e]/14 rounded-full blur-[130px] pointer-events-none"></div>
        <div class="absolute -bottom-10 left-1/2 -translate-x-1/2 w-72 sm:w-[500px] h-48 bg-[#005c94]/12 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20 w-full flex flex-col items-center text-center">
            
            <!-- Hero Center: Alternating Official Theme Logo & QUAF Festival Logo -->
            <div class="relative w-full max-w-xl flex flex-col items-center text-center mb-4 sm:mb-6"
                 x-data="{ activeLogo: 0 }"
                 x-init="setInterval(() => { activeLogo = 1 - activeLogo; }, 4200)">
                
                <div class="relative w-full h-[115px] xs:h-[138px] sm:h-[170px] flex items-center justify-center">
                    <!-- 1. Adabic Inheritance Logo -->
                    <img src="{{ asset('images/adabic-inheritance-web.svg') }}" 
                         alt="Adabic Inheritance — QUAF" 
                         class="max-w-[220px] xs:max-w-[270px] sm:max-w-[350px] lg:max-w-[400px] max-h-[110px] xs:max-h-[132px] sm:max-h-[162px] w-auto h-auto object-contain transition-all duration-700 transform drop-shadow-[0_12px_40px_rgba(255,255,255,0.10)]"
                         :class="activeLogo === 0 ? 'opacity-100 scale-100 relative z-10' : 'opacity-0 scale-95 absolute pointer-events-none z-0'">

                    <!-- 2. QUAF Festival Logo -->
                    <img src="{{ asset('images/quaf-logo-hero.svg') }}" 
                         alt="QUAF — Markaz Cultural Festival" 
                         class="max-w-[205px] xs:max-w-[255px] sm:max-w-[350px] lg:max-w-[400px] max-h-[110px] xs:max-h-[132px] sm:max-h-[162px] w-auto h-auto object-contain transition-all duration-700 transform drop-shadow-[0_12px_40px_rgba(255,255,255,0.14)]"
                         :class="activeLogo === 1 ? 'opacity-100 scale-100 relative z-10' : 'opacity-0 scale-95 absolute pointer-events-none z-0'">
                </div>

                <!-- Festival Info Badges (No action buttons) -->
                <div class="mt-5 flex flex-wrap items-center justify-center gap-2 sm:gap-3">
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[11px] sm:text-xs font-mono tracking-widest uppercase bg-white/10 text-white/90 border border-white/15 backdrop-blur-md">
                        Markazu Saquafathi Sunniyya
                    </span>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[11px] sm:text-xs font-mono tracking-widest uppercase bg-[#be1e2d]/25 text-red-200 border border-[#be1e2d]/40 font-semibold backdrop-blur-md">
                        QUAF 2026
                    </span>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[11px] sm:text-xs font-mono tracking-widest uppercase bg-[#f3bd2e]/20 text-[#f3bd2e] border border-[#f3bd2e]/40 font-bold backdrop-blur-md">
                        ₹100 Challenge
                    </span>
                </div>
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
         SECTION 2: LIGHT THEME MAIN CONTENT (CLEAN, SOLID, ZERO STROKES)
         ===================================================================== -->
    <div class="w-full bg-[#f8f9fa] sm:bg-[#f4f5f7] text-slate-900 py-10 sm:py-16 pb-28 sm:pb-32 relative z-20">
        
        <main class="max-w-2xl mx-auto px-4 sm:px-6 space-y-6 sm:space-y-8">

            <!-- CARD 1: MISSION & STATS -->
            <section class="rounded-3xl bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/60 relative overflow-hidden">
                <div class="text-[11px] font-mono uppercase tracking-widest text-[#94121b] font-bold mb-2">
                    ഒരു ചെറിയ വിഹിതം • ഒരു വലിയ ദൗത്യം
                </div>

                <h1 class="font-anek text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight mb-4">
                    നാളെയുടെ പ്രബോധകരെ<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#94121b] via-[#be1e2d] to-[#c99935]">
                        നമുക്ക് ഒരുമിച്ച് വളർത്താം
                    </span>
                </h1>

                <p class="font-anek text-base sm:text-lg text-slate-700 leading-relaxed font-normal mb-5">
                    <strong class="text-slate-900 font-semibold">ആയിരത്തി അഞ്ഞൂറോളം വരുന്ന ജാമിഅ മർകസിലെ മുതഅല്ലിമുകൾക്ക് വേണ്ടി</strong> ഈ വർഷത്തെ QUAF മർകസ് കൾച്ചറൽ ഫെസ്റ്റിവലിന് നിങ്ങളാൽ കഴിയുന്ന ഒരു ചെറിയ വിഹിതം നൽകുമോ?
                </p>

                <!-- Inspirational Quote Banner (No ugly outer borders) -->
                <div class="my-5 p-4 sm:p-5 rounded-r-2xl bg-amber-50/80 border-l-4 border-amber-400 font-anek text-sm sm:text-base text-amber-950 font-medium leading-relaxed">
                    "നാളെയുടെ പ്രബോധകരെ ഏറ്റവും മികച്ച ദാഇകളാക്കി വളർത്തിയെടുക്കാൻ നമുക്കൊരുമിച്ച് നിൽക്കാം."
                </div>

                <p class="font-anek text-sm sm:text-base text-slate-600 leading-relaxed mb-6">
                    നിങ്ങളുടെ ₹100 ഒരു തുക മാത്രമല്ല — അറിവിനും കഴിവിനും ഭാവിയിലെ പ്രബോധന പ്രവർത്തനങ്ങൾക്കും നൽകുന്ന ഒരു പിന്തുണയാണ്.
                </p>

                <!-- 3 Key Impact Stats (Clean, Borderless Solid Tiles) -->
                <div class="grid grid-cols-3 gap-2.5 sm:gap-4 pt-2">
                    <div class="rounded-2xl bg-slate-50 p-3.5 sm:p-4 text-center">
                        <span class="block font-rockwell text-xl sm:text-2xl font-bold text-[#94121b]">1500+</span>
                        <span class="font-anek text-xs sm:text-sm text-slate-600 font-medium">മുതഅല്ലിമുകൾ</span>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3.5 sm:p-4 text-center">
                        <span class="block font-rockwell text-xl sm:text-2xl font-bold text-[#94121b]">₹100</span>
                        <span class="font-anek text-xs sm:text-sm text-slate-600 font-medium">ചെറിയൊരു വിഹിതം</span>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3.5 sm:p-4 text-center">
                        <span class="block font-rockwell text-xl sm:text-2xl font-bold text-[#c99935]">&infin;</span>
                        <span class="font-anek text-xs sm:text-sm text-slate-600 font-medium">നന്മയുടെ പ്രതീക്ഷ</span>
                    </div>
                </div>
            </section>

            <!-- CARD 2: STEP 01 - SELECT AMOUNT -->
            <section id="donate" class="rounded-3xl bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/60 relative">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-3 py-1 rounded-full bg-red-50 text-[#94121b] text-[11px] font-mono font-bold tracking-widest uppercase">
                        STEP 01
                    </span>
                    <span class="text-xs font-mono text-slate-400 font-medium">Fast Amount Selection</span>
                </div>

                <h2 class="font-anek text-xl sm:text-2xl font-bold text-slate-900 mb-2">
                    നിങ്ങളുടെ വിഹിതം തിരഞ്ഞെടുക്കൂ
                </h2>
                <p class="font-anek text-xs sm:text-sm text-slate-600 mb-5">
                    തുക touch ചെയ്താൽ നേരിട്ട് UPI Payment സ്ക്രീനിലേക്ക് പ്രവേശിക്കാം.
                </p>

                <!-- Quick Amounts Grid (Completely Stroke-Free, High Contrast) -->
                <div class="grid grid-cols-3 gap-2.5 sm:gap-3.5 mb-5">
                    <template x-for="amt in [50, 100, 313, 500, 1000, 2000]" :key="amt">
                        <button type="button"
                                @click="selectAmount(amt)"
                                class="py-3.5 sm:py-4 px-3 rounded-2xl font-rockwell text-base sm:text-lg font-bold transition-all duration-200 cursor-pointer flex flex-col items-center justify-center gap-0.5 border-0"
                                :class="selectedAmount === amt 
                                    ? 'bg-gradient-to-r from-[#be1e2d] to-[#991522] text-white shadow-lg shadow-red-900/30 scale-[1.02]' 
                                    : 'bg-slate-100 hover:bg-slate-200 text-slate-900 shadow-2xs'">
                            <span x-text="'₹' + Number(amt).toLocaleString('en-IN')"></span>
                            <span class="text-[10px] font-anek opacity-80 font-normal" x-show="amt === 100">Standard</span>
                            <span class="text-[10px] font-anek opacity-80 font-normal" x-show="amt === 313">Badr Special</span>
                        </button>
                    </template>
                </div>

                <!-- Custom Amount Input (Stroke-Free, Modern Inset) -->
                <div class="flex gap-2.5">
                    <div class="relative flex-1">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-rockwell text-slate-400 text-sm font-bold">₹</span>
                        <input type="number" 
                               x-model="customInput" 
                               @keydown.enter.prevent="applyCustomAmount()"
                               min="1" 
                               step="1"
                               inputmode="numeric"
                               placeholder="മറ്റൊരു തുക നൽകുക (ഉദാ: 5000)" 
                               class="w-full bg-slate-100 rounded-2xl pl-8 pr-4 py-3.5 text-sm sm:text-base font-anek font-semibold text-slate-900 placeholder-slate-400 border-0 focus:outline-none focus:ring-2 focus:ring-[#be1e2d] transition-all">
                    </div>
                    <button type="button" 
                            @click="applyCustomAmount()" 
                            style="background-color: #be1e2d; color: #ffffff;"
                            class="px-6 rounded-2xl font-anek text-sm font-bold bg-[#be1e2d] hover:bg-red-700 text-white transition-all cursor-pointer shadow-md border-0">
                        Apply
                    </button>
                </div>
            </section>

            <!-- CARD 3: STEP 02 - DIRECT UPI PAYMENT & OFFICIAL SIB QR -->
            <section class="rounded-3xl bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/60 relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-3 py-1 rounded-full bg-red-50 text-[#94121b] text-[11px] font-mono font-bold tracking-widest uppercase">
                        STEP 02
                    </span>
                    <span class="text-xs font-mono text-emerald-700 font-semibold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Instant Payment Ready
                    </span>
                </div>

                <h2 class="font-anek text-xl sm:text-2xl font-bold text-slate-900 mb-1">
                    GPay / UPI വഴി നൽകാം
                </h2>

                <div class="my-4 p-4 rounded-2xl bg-amber-50/80 flex items-center justify-between">
                    <span class="font-anek text-sm text-slate-700 font-medium">തിരഞ്ഞെടുത്ത തുക:</span>
                    <span class="font-rockwell text-xl sm:text-2xl font-bold text-[#94121b]" x-text="'₹' + Number(selectedAmount).toLocaleString('en-IN')"></span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                    <!-- Left Action Buttons (Solid, High Visibility, Stroke-Free) -->
                    <div class="md:col-span-7 space-y-3.5">
                        
                        <!-- 1. Primary Direct UPI Payment Button -->
                        <button type="button" 
                                @click="payNow()" 
                                style="background-color: #be1e2d; color: #ffffff;"
                                class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-[#be1e2d] via-red-600 to-[#991522] hover:brightness-105 text-white font-sora font-bold text-base sm:text-lg shadow-xl shadow-red-900/25 border-0 transition-all cursor-pointer flex items-center justify-center gap-2.5">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="2" y="5" width="20" height="14" rx="2" stroke-width="2"/>
                                <line x1="2" y1="10" x2="22" y2="10" stroke-width="2"/>
                            </svg>
                            <span x-text="'Direct UPI Pay ₹' + Number(selectedAmount).toLocaleString('en-IN')"></span>
                        </button>

                        <!-- 2. Dual App Buttons: GPay & Generic UPI (Solid & Stroke-Free) -->
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" 
                                    @click="payWithGPay()" 
                                    style="background-color: #0f172a !important; color: #ffffff !important;"
                                    class="py-3.5 px-4 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-sora text-xs sm:text-sm font-bold shadow-md border-0 transition-all cursor-pointer flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span>Google Pay</span>
                            </button>
                            <button type="button" 
                                    @click="payGenericUPI()" 
                                    style="background-color: #f3bd2e !important; color: #020617 !important;"
                                    class="py-3.5 px-4 rounded-2xl bg-[#f3bd2e] hover:bg-amber-400 text-slate-950 font-sora text-xs sm:text-sm font-bold shadow-md border-0 transition-all cursor-pointer flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span>Other UPI Apps</span>
                            </button>
                        </div>

                        <!-- 3. Copy UPI ID Button (Solid, Clear Text, Stroke-Free) -->
                        <button type="button" 
                                @click="copyUPI()" 
                                style="background-color: #f1f5f9 !important; color: #0f172a !important;"
                                class="w-full py-3.5 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-900 font-sora text-xs sm:text-sm font-bold shadow-2xs border-0 transition-all cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                            </svg>
                            <span x-text="copiedUPI ? 'UPI ID Copied Successfully' : 'Copy UPI ID (qr.9074680630@sib)'"></span>
                        </button>

                        <!-- Informational Note -->
                        <p class="font-anek text-[11px] sm:text-xs text-slate-600 leading-relaxed pt-1">
                            തുക touch ചെയ്താൽ <strong class="text-slate-900">Direct UPI Payment</strong> നേരിട്ട് തുറക്കും; അതേ തുക <strong class="text-[#94121b]">qr.9074680630@sib</strong> എന്ന UPI ID-യിലേക്ക് payment screen-ൽ automatically fill ചെയ്യും.
                        </p>

                        <div class="p-3.5 rounded-2xl bg-slate-50 text-[11px] font-mono text-slate-700">
                            Configured UPI ID: <strong class="text-[#94121b] select-all">qr.9074680630@sib</strong>
                            <div class="text-[10px] text-slate-500 mt-0.5">South Indian Bank • Jamia Markaz</div>
                        </div>
                    </div>

                    <!-- Right Official Scan & Pay QR Code Card (Clean, Stroke-Free) -->
                    <div class="md:col-span-5 flex flex-col items-center">
                        <div class="w-full max-w-[240px] rounded-3xl bg-white p-4 shadow-lg border-0 text-center flex flex-col items-center">
                            <div class="text-[10px] font-mono uppercase tracking-wider text-slate-700 font-bold mb-1.5">
                                South Indian Bank
                            </div>
                            <div class="relative w-full aspect-square rounded-2xl overflow-hidden bg-slate-50 flex items-center justify-center p-1">
                                <img src="{{ asset('images/quaf-sib-qr.jpg') }}" 
                                     alt="QUAF 2026 South Indian Bank UPI QR Code" 
                                     class="w-full h-full object-contain">
                            </div>
                            <div class="mt-2 text-[10px] font-mono text-slate-900 font-bold uppercase tracking-tight">
                                JAMIA MARKAZU SSAQUAFATHISSUNN
                            </div>
                            <div class="text-[10px] font-mono text-slate-500">
                                qr.9074680630@sib
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CARD 4: STEP 03 - SHARE WITH OTHERS -->
            <section class="rounded-3xl bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/60 relative">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-3 py-1 rounded-full bg-red-50 text-[#94121b] text-[11px] font-mono font-bold tracking-widest uppercase">
                        STEP 03
                    </span>
                    <span class="text-xs font-mono text-slate-400">Spread The Goodness</span>
                </div>

                <h2 class="font-anek text-xl sm:text-2xl font-bold text-slate-900 mb-2">
                    ഈ നന്മ മറ്റുള്ളവരിലേക്കും എത്തിക്കാം
                </h2>
                <p class="font-anek text-xs sm:text-sm text-slate-600 leading-relaxed mb-5">
                    നിങ്ങൾക്ക് കഴിയുന്ന തുക നൽകുന്നതിനൊപ്പം, ഈ സന്ദേശം കുറഞ്ഞത് ഒരാളിലേക്കെങ്കിലും share ചെയ്യൂ.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                    <!-- WhatsApp Share (Official Vibrant Solid Green, 100% White Text, Stroke-Free) -->
                    <button type="button" 
                            @click="shareWhatsApp()" 
                            style="background-color: #25D366 !important; color: #ffffff !important;"
                            class="w-full py-4 px-5 rounded-2xl bg-[#25D366] hover:bg-[#20ba5a] text-white font-sora text-sm font-bold shadow-lg shadow-emerald-950/20 border-0 transition-all cursor-pointer flex items-center justify-center gap-2.5">
                        <svg class="w-5 h-5 fill-white shrink-0" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.983z"/>
                        </svg>
                        <span>WhatsApp Share</span>
                    </button>

                    <!-- Native Share (Solid Dark, High Contrast, Stroke-Free) -->
                    <button type="button" 
                            @click="nativeShare()" 
                            style="background-color: #0f172a !important; color: #ffffff !important;"
                            class="w-full py-4 px-5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-sora text-sm font-bold shadow-lg shadow-slate-900/15 border-0 transition-all cursor-pointer flex items-center justify-center gap-2.5">
                        <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        <span>General Share</span>
                    </button>
                </div>

                <!-- Copy Appeal Message Button (Solid Gold, Black Text, Stroke-Free) -->
                <button type="button" 
                        @click="copyMessage()" 
                        style="background-color: #f3bd2e !important; color: #020617 !important;"
                        class="w-full py-3.5 px-5 rounded-2xl bg-[#f3bd2e] hover:bg-amber-400 text-slate-950 font-anek text-sm sm:text-base font-bold shadow-md border-0 transition-all cursor-pointer flex items-center justify-center gap-2.5">
                    <svg class="w-4 h-4 text-slate-950 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span>Donation Message Copy ചെയ്യുക</span>
                </button>
            </section>

            <!-- CARD 5: 3 SIMPLE STEPS GUIDE -->
            <section class="rounded-3xl bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/60 relative">
                <div class="text-[11px] font-mono uppercase tracking-widest text-[#94121b] font-bold mb-1">
                    എങ്ങനെ സഹായിക്കാം?
                </div>
                <h2 class="font-anek text-xl sm:text-2xl font-bold text-slate-900 mb-5">
                    3 ലളിതമായ വഴികൾ
                </h2>

                <div class="space-y-3.5">
                    <div class="flex items-start gap-4 p-3.5 rounded-2xl bg-slate-50">
                        <span class="w-9 h-9 rounded-full bg-[#be1e2d] text-white font-rockwell font-bold text-sm flex items-center justify-center shrink-0 shadow-sm border-0">
                            1
                        </span>
                        <div>
                            <div class="font-anek font-bold text-slate-900 text-sm sm:text-base">ഒരു തുക തിരഞ്ഞെടുക്കുക</div>
                            <div class="font-anek text-xs sm:text-sm text-slate-600 leading-relaxed">
                                ₹50, ₹100, ₹313, ₹500 അല്ലെങ്കിൽ നിങ്ങൾക്ക് കഴിയുന്ന മറ്റൊരു തുക.
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-3.5 rounded-2xl bg-slate-50">
                        <span class="w-9 h-9 rounded-full bg-[#be1e2d] text-white font-rockwell font-bold text-sm flex items-center justify-center shrink-0 shadow-sm border-0">
                            2
                        </span>
                        <div>
                            <div class="font-anek font-bold text-slate-900 text-sm sm:text-base">GPay / UPI വഴി നൽകുക</div>
                            <div class="font-anek text-xs sm:text-sm text-slate-600 leading-relaxed">
                                Payment button അല്ലെങ്കിൽ QR code ഉപയോഗിക്കുക.
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-3.5 rounded-2xl bg-slate-50">
                        <span class="w-9 h-9 rounded-full bg-[#be1e2d] text-white font-rockwell font-bold text-sm flex items-center justify-center shrink-0 shadow-sm border-0">
                            3
                        </span>
                        <div>
                            <div class="font-anek font-bold text-slate-900 text-sm sm:text-base">ഒരു സുഹൃത്തിലേക്കെങ്കിലും share ചെയ്യുക</div>
                            <div class="font-anek text-xs sm:text-sm text-slate-600 leading-relaxed">
                                നന്മയുടെ സന്ദേശം കൂടുതൽ ആളുകളിലേക്ക് എത്തിക്കാം.
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CARD 6: FESTIVAL IDENTITY & DUA -->
            <section class="rounded-3xl bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/60 relative">
                <div class="text-[11px] font-mono uppercase tracking-widest text-[#94121b] font-bold mb-1">
                    Ādabic Inheritance
                </div>
                <h2 class="font-anek text-xl sm:text-2xl font-bold text-slate-900 mb-2">
                    മർകസ് കൾച്ചറൽ ഫെസ്റ്റിവൽ 2026
                </h2>
                <p class="font-anek text-xs sm:text-sm text-slate-700 leading-relaxed mb-4">
                    <strong>Jamia Markaz Karanthur</strong><br>
                    2026 Oct 31 – Nov 01
                </p>

                <div class="p-4 rounded-2xl bg-amber-50 text-amber-950 font-anek text-xs sm:text-sm leading-relaxed text-center font-medium">
                    നാഥൻ ഇരു ലോകത്തും പതിന്മടങ്ങ് പ്രതിഫലം പകരം നൽകട്ടെ — ആമീൻ.
                </div>
            </section>

        </main>

    </div>

    <!-- =====================================================================
         STICKY BOTTOM MOBILE ACTION BAR (SMART PHONE OPTIMIZED - ZERO STROKES)
         ===================================================================== -->
    <div class="fixed bottom-0 left-0 right-0 z-40 lg:hidden bg-white/95 px-4 py-3 backdrop-blur-xl shadow-[0_-8px_30px_rgba(0,0,0,0.12)] border-0">
        <div class="max-w-md mx-auto flex items-center gap-3">
            <button type="button" 
                    @click="payNow()" 
                    style="background-color: #be1e2d !important; color: #ffffff !important;"
                    class="flex-1 py-3.5 px-4 rounded-xl text-white font-sora font-bold text-xs uppercase tracking-wider shadow-lg shadow-red-950/20 border-0 text-center flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <span x-text="'ഇപ്പോൾ സഹായിക്കാം (₹' + Number(selectedAmount).toLocaleString('en-IN') + ')'"></span>
            </button>
            <button type="button" 
                    @click="shareWhatsApp()" 
                    style="background-color: #25D366 !important; color: #ffffff !important;"
                    class="py-3.5 px-5 rounded-xl text-white font-sora font-bold text-xs uppercase tracking-wider shadow-md shadow-emerald-950/20 border-0 text-center flex items-center justify-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.983z"/>
                </svg>
                <span>Share</span>
            </button>
        </div>
    </div>

    <!-- =====================================================================
         JAZAKALLAHU KHAIRAN CONFIRMATION MODAL
         ===================================================================== -->
    <div x-cloak 
         x-show="modalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md"
         @click.self="modalOpen = false">
        <div class="w-full max-w-sm rounded-3xl bg-white p-6 text-center shadow-2xl relative text-slate-900 border-0">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="font-anek text-xl font-bold text-slate-900 mb-2">
                ജസാകല്ലാഹു ഖൈറൻ
            </h3>
            <p class="font-anek text-sm text-slate-600 leading-relaxed mb-6">
                നിങ്ങളുടെ പിന്തുണയ്ക്ക് ഹൃദയം നിറഞ്ഞ നന്ദി. ഈ നന്മ മറ്റൊരാളിലേക്കും എത്തിക്കൂ.
            </p>
            <button type="button" 
                    @click="modalOpen = false" 
                    style="background-color: #be1e2d !important; color: #ffffff !important;"
                    class="w-full py-3.5 rounded-2xl bg-[#be1e2d] text-white font-anek font-bold text-sm shadow-md cursor-pointer hover:brightness-110 transition-all border-0">
                ശരി
            </button>
        </div>
    </div>

</div>

<script>
function quafDonationPage() {
    return {
        upiId: "qr.9074680630@sib",
        payeeName: "QUAF 2026",
        selectedAmount: 100,
        customInput: '',
        copiedUPI: false,
        modalOpen: false,

        selectAmount(amt) {
            this.selectedAmount = Number(amt);
            this.payNow();
        },

        applyCustomAmount() {
            const val = Number(this.customInput);
            if (!Number.isFinite(val) || val < 1) {
                alert("ദയവായി ശരിയായ തുക നൽകുക.");
                return;
            }
            this.selectedAmount = Math.round(val);
            this.customInput = '';
            this.payNow();
        },

        getUpiLink() {
            const params = new URLSearchParams({
                pa: this.upiId,
                pn: this.payeeName,
                am: Number(this.selectedAmount).toFixed(2),
                cu: "INR"
            });
            return "upi://pay?" + params.toString();
        },

        getGPayLink() {
            const params = new URLSearchParams({
                pa: this.upiId,
                pn: this.payeeName,
                am: Number(this.selectedAmount).toFixed(2),
                cu: "INR"
            });
            return "tez://upi/pay?" + params.toString();
        },

        getAndroidIntentLink() {
            const query = this.getUpiLink().replace("upi://pay?", "");
            return "intent://pay?" + query +
                "#Intent;scheme=upi;" +
                "package=com.google.android.apps.nbu.paisa.user;" +
                "end";
        },

        payNow() {
            window.location.href = this.getUpiLink();
        },

        payWithGPay() {
            const gpay = this.getGPayLink();
            const intent = this.getAndroidIntentLink();
            window.location.href = gpay;
            setTimeout(() => {
                window.location.href = intent;
            }, 700);
        },

        payGenericUPI() {
            window.location.href = this.getUpiLink();
        },

        async copyUPI() {
            try {
                await navigator.clipboard.writeText(this.upiId);
                this.copiedUPI = true;
                setTimeout(() => { this.copiedUPI = false; }, 3000);
            } catch (err) {
                prompt("UPI ID:", this.upiId);
            }
        },

        getMessage() {
            return `QUAF 2026 — ₹100 Challenge\n\n` +
                   `ആയിരത്തി അഞ്ഞൂറോളം വരുന്ന ജാമിഅ മർകസിലെ മുതഅല്ലിമുകൾക്ക് വേണ്ടി ഒരു ചെറിയ വിഹിതം നൽകുമോ?\n\n` +
                   `നാളെയുടെ പ്രബോധകരെ ഏറ്റവും മികച്ച ദാഇകളാക്കി വളർത്തിയെടുക്കാൻ നമുക്കൊരുമിച്ച് നിൽക്കാം.\n\n` +
                   `തുക: ₹50 / ₹100 / ₹313 / ₹500\n` +
                   `GPay / UPI: ${this.upiId}\n\n` +
                   `നിങ്ങളാൽ കഴിയുന്നൊരു വിഹിതം നൽകൂ.\n` +
                   `ഈ നന്മയുടെ സന്ദേശം മറ്റൊരാളിലേക്കും share ചെയ്യൂ.\n\n` +
                   `QUAF 2026 — Ādabic Inheritance\n` +
                   `Markaz Cultural Festival\n\n` +
                   `നാഥൻ ഇരു ലോകത്തും പതിന്മടങ്ങ് പ്രതിഫലം പകരം നൽകട്ടെ — ആമീൻ.`;
        },

        shareWhatsApp() {
            const text = encodeURIComponent(this.getMessage() + "\n\n" + window.location.href);
            window.open("https://wa.me/?text=" + text, "_blank");
        },

        async nativeShare() {
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: "QUAF 2026 — ₹100 Challenge",
                        text: this.getMessage(),
                        url: window.location.href
                    });
                } catch (e) {
                    // Canceled or failed
                }
            } else {
                this.copyMessage();
            }
        },

        async copyMessage() {
            try {
                await navigator.clipboard.writeText(this.getMessage());
                this.modalOpen = true;
            } catch (err) {
                prompt("Message:", this.getMessage());
            }
        }
    };
}
</script>
@endsection
