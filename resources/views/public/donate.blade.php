@extends('layouts.public', ['title' => 'QUAF 2026 | ₹100 Challenge — Ādabic Inheritance', 'hideHeader' => true, 'hideBottomNav' => true])

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

                <!-- 2 Key Impact Stats (Clean, Borderless Solid Tiles) -->
                <div class="grid grid-cols-2 gap-3 sm:gap-4 pt-2">
                    <div class="rounded-2xl bg-slate-50 p-3.5 sm:p-4 text-center">
                        <span class="block font-rockwell text-xl sm:text-2xl font-bold text-[#94121b]">1500+</span>
                        <span class="font-anek text-xs sm:text-sm text-slate-600 font-medium">മുതഅല്ലിമുകൾ</span>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3.5 sm:p-4 text-center">
                        <span class="block font-rockwell text-xl sm:text-2xl font-bold text-[#c99935]">&infin;</span>
                        <span class="font-anek text-xs sm:text-sm text-slate-600 font-medium">നന്മയുടെ പ്രതീക്ഷ</span>
                    </div>
                </div>
            </section>

            <!-- CARD 2: STEP 01 - OFFICIAL UPI QR & PAYMENT -->
            <section id="donate" class="rounded-3xl bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/60 relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-3 py-1 rounded-full bg-red-50 text-[#94121b] text-[11px] font-mono font-bold tracking-widest uppercase">
                        STEP 01
                    </span>
                    <span class="text-xs font-mono text-emerald-700 font-semibold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Official Bank Verified QR
                    </span>
                </div>

                <div class="text-center max-w-md mx-auto mb-6">
                    <h2 class="font-anek text-xl sm:text-2xl font-bold text-slate-900 mb-2">
                        ഔദ്യോഗിക UPI QR കോഡ്
                    </h2>
                    <p class="font-anek text-xs sm:text-sm text-slate-600 leading-relaxed">
                        താഴെയുള്ള ക്യുആർ കോഡ് നിങ്ങളുടെ ഗാലറിയിലേക്ക് ഡൗൺലോഡ് ചെയ്ത് Google Pay, PhonePe, Paytm അല്ലെങ്കിൽ ഏത് UPI ആപ്പിലും 'Scan from Gallery' വഴി പണമടയ്ക്കാം.
                    </p>
                </div>

                <!-- Centered QR Box -->
                <div class="max-w-sm mx-auto flex flex-col items-center">
                    <div class="w-full rounded-3xl bg-slate-50 p-5 text-center flex flex-col items-center shadow-inner">
                        <div class="text-[11px] font-mono uppercase tracking-wider text-slate-600 font-bold mb-2">
                            South Indian Bank • Jamia Markaz
                        </div>
                        
                        <!-- High Resolution QR Image with Pure White Padding -->
                        <div class="relative w-64 max-w-full aspect-square rounded-2xl bg-white p-3 shadow-md flex items-center justify-center">
                            <img src="{{ asset('images/quaf-sib-qr.jpg') }}" 
                                 alt="QUAF 2026 South Indian Bank UPI QR Code" 
                                 class="w-full h-full object-contain rounded-xl">
                        </div>

                        <div class="mt-3.5 text-xs font-mono text-slate-900 font-bold uppercase tracking-tight">
                            JAMIA MARKAZU SSAQUAFATHISSUNNIYYA
                        </div>
                        <div class="text-[11px] font-mono text-slate-500 mt-0.5">
                            UPI ID: <span class="font-bold text-[#94121b] select-all">qr.9074680630@sib</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="w-full space-y-3 mt-5">
                        <!-- 1. Pay Now Button (Top - Red) -->
                        <button type="button" 
                                @click="payNow()" 
                                style="background-color: #be1e2d !important; color: #ffffff !important;"
                                class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-[#be1e2d] via-red-600 to-[#991522] hover:brightness-105 text-white font-sora font-bold text-base sm:text-lg shadow-xl shadow-red-900/25 border-0 transition-all cursor-pointer flex items-center justify-center gap-2.5">
                            <svg class="w-5 h-5 shrink-0 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span>Pay Now</span>
                        </button>

                        <!-- 2. Download QR to Gallery Button (Below - Black) -->
                        <a href="{{ asset('images/quaf-sib-qr.jpg') }}" download="quaf-sib-qr.jpg" 
                           style="background-color: #0f172a !important; color: #ffffff !important;"
                           class="w-full py-3.5 px-6 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-sora font-bold text-sm sm:text-base shadow-md border-0 transition-all cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Download QR to Gallery</span>
                        </a>

                        <!-- 3. Copy UPI ID Button -->
                        <button type="button" 
                                @click="copyUPI()" 
                                style="background-color: #f1f5f9 !important; color: #0f172a !important;"
                                class="w-full py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-sora text-xs sm:text-sm font-semibold shadow-2xs border-0 transition-all cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                            </svg>
                            <span x-text="copiedUPI ? 'UPI ID Copied Successfully' : 'Copy UPI ID (qr.9074680630@sib)'"></span>
                        </button>
                    </div>

                    <!-- Helpful Step-by-Step Payment Instructions -->
                    <div class="w-full mt-6 p-4 rounded-2xl bg-slate-50 text-slate-700 text-left space-y-2">
                        <div class="font-anek text-xs font-bold text-slate-900 mb-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#94121b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>എങ്ങനെ പണമടയ്ക്കാം?</span>
                        </div>
                        <p class="font-anek text-[11px] sm:text-xs text-slate-600 leading-relaxed">
                            1. മുകളിലെ <strong>'Pay Now'</strong> ബട്ടൺ ക്ലിക്ക് ചെയ്തോ, അല്ലെങ്കിൽ <strong>'Download QR to Gallery'</strong> വഴി QR കോഡ് ഫോണിലേക്ക് സേവ് ചെയ്തോ പണമടയ്ക്കാം.
                        </p>
                        <p class="font-anek text-[11px] sm:text-xs text-slate-600 leading-relaxed">
                            2. <strong>Google Pay / PhonePe / Paytm</strong> തുറന്ന് QR Scanner എടുത്ത് <strong>'Upload from Gallery'</strong> വഴി ഈ QR തിരഞ്ഞെടുക്കുക.
                        </p>
                        <p class="font-anek text-[11px] sm:text-xs text-slate-600 leading-relaxed">
                            3. നിങ്ങളുടെ വിഹിതം നൽകി പേയ്‌മെന്റ് പൂർത്തിയാക്കുക. തുടർന്ന് ലഭിച്ച സ്ക്രീൻഷോട്ട് താഴെ നൽകി രസീത് പോസ്റ്റർ സ്വന്തമാക്കാം.
                        </p>
                    </div>
                </div>
            </section>

            <!-- CARD 3: STEP 02 - DOWNLOAD RECEIPT POSTER -->
            <section id="receipt-download" class="rounded-3xl bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/60 relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-3 py-1 rounded-full bg-red-50 text-[#94121b] text-[11px] font-mono font-bold tracking-widest uppercase">
                        STEP 02
                    </span>
                    <span class="text-xs font-mono text-slate-400">Instant Poster Download</span>
                </div>

                <h2 class="font-anek text-xl sm:text-2xl font-bold text-slate-900 mb-2">
                    നിങ്ങളുടെ രസീത് പോസ്റ്റർ ഡൗൺലോഡ് ചെയ്യാം
                </h2>
                <p class="font-anek text-xs sm:text-sm text-slate-600 leading-relaxed mb-6">
                    നിങ്ങൾ പണം അയച്ച ശേഷം ലഭിച്ച Transaction ID, സ്ക്രീൻഷോട്ട്, WhatsApp നമ്പർ എന്നിവ നൽകി ഔദ്യോഗിക രസീത് പോസ്റ്റർ ഇപ്പോൾ തന്നെ ഡൗൺലോഡ് ചെയ്യാം.
                </p>

                <!-- UPLOAD SCREENSHOT & GENERATE/DOWNLOAD POSTER (Direct View, No Tabs) -->
                <div x-cloak x-show="!foundReceipt" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Transaction ID / UTR -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-anek text-xs font-semibold text-slate-700">UPI Transaction ID / UTR *</label>
                                <button type="button" 
                                        @click="pasteUploadTxnId()" 
                                        class="text-[11px] font-sora font-bold text-[#be1e2d] hover:underline cursor-pointer border-0 bg-transparent flex items-center gap-1">
                                    <svg class="w-3 h-3 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span>Paste</span>
                                </button>
                            </div>
                            <input type="text" 
                                   x-model="uploadForm.transaction_id" 
                                   placeholder="ഉദാ: 428765432109" 
                                   class="w-full bg-slate-100 rounded-xl px-3.5 py-3 text-sm font-anek font-semibold text-slate-900 placeholder-slate-400 border-0 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]">
                        </div>

                        <!-- WhatsApp Number -->
                        <div>
                            <label class="block font-anek text-xs font-semibold text-slate-700 mb-1">WhatsApp നമ്പർ *</label>
                            <input type="tel" 
                                   x-model="uploadForm.whatsapp_number" 
                                   placeholder="ഉദാ: 9876543210" 
                                   class="w-full bg-slate-100 rounded-xl px-3.5 py-3 text-sm font-mono font-semibold text-slate-900 placeholder-slate-400 border-0 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]">
                        </div>

                        <!-- Donor Name -->
                        <div>
                            <label class="block font-anek text-xs font-semibold text-slate-700 mb-1">നിങ്ങളുടെ പേര് (Donor Name) *</label>
                            <input type="text" 
                                   x-model="uploadForm.donor_name" 
                                   placeholder="ഉദാ: Muhammed Shafi" 
                                   class="w-full bg-slate-100 rounded-xl px-3.5 py-3 text-sm font-anek font-semibold text-slate-900 placeholder-slate-400 border-0 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]">
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block font-anek text-xs font-semibold text-slate-700 mb-1">നൽകിയ തുക (₹) *</label>
                            <input type="number" 
                                   x-model="uploadForm.amount" 
                                   min="1" 
                                   step="1"
                                   placeholder="100" 
                                   class="w-full bg-slate-100 rounded-xl px-3.5 py-3 text-sm font-rockwell font-bold text-slate-900 placeholder-slate-400 border-0 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]">
                        </div>

                        <!-- Place (Optional) -->
                        <div class="sm:col-span-2">
                            <label class="block font-anek text-xs font-semibold text-slate-700 mb-1">സ്ഥലം (ഓപ്ഷണൽ)</label>
                            <input type="text" 
                                   x-model="uploadForm.donor_place" 
                                   placeholder="ഉദാ: Calicut" 
                                   class="w-full bg-slate-100 rounded-xl px-3.5 py-3 text-sm font-anek font-semibold text-slate-900 placeholder-slate-400 border-0 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]">
                        </div>
                    </div>

                    <!-- Payment Screenshot Upload Box -->
                    <div class="pt-1">
                        <label class="block font-anek text-xs font-semibold text-slate-700 mb-1.5">
                            പേയ്‌മെന്റ് സ്ക്രീൻഷോട്ട് (Payment Screenshot) *
                        </label>

                        <div class="relative rounded-2xl bg-slate-100 hover:bg-slate-200/70 transition-all p-4 text-center cursor-pointer border-0">
                            <input type="file" 
                                   id="receipt-screenshot-input"
                                   @change="handleFileSelect($event)" 
                                   accept="image/*" 
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                            <!-- If NO file selected -->
                            <template x-if="!uploadForm.screenshotPreview">
                                <div class="flex flex-col items-center justify-center py-2">
                                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-600 mb-2 shadow-2xs">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <span class="font-anek text-xs sm:text-sm font-bold text-slate-800">
                                        GPay / UPI Payment സ്ക്രീൻഷോട്ട് തിരഞ്ഞെടുക്കുക
                                    </span>
                                    <span class="font-anek text-[11px] text-slate-500 mt-0.5">
                                        JPG, PNG അല്ലെങ്കിൽ WEBP (Max 10MB)
                                    </span>
                                </div>
                            </template>

                            <!-- If file selected with preview -->
                            <template x-if="uploadForm.screenshotPreview">
                                <div class="flex items-center justify-between gap-3 bg-white p-2.5 rounded-xl text-left">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img :src="uploadForm.screenshotPreview" alt="Screenshot Preview" class="w-12 h-12 rounded-lg object-cover shrink-0 border border-slate-200">
                                        <div class="min-w-0">
                                            <div class="font-anek text-xs font-bold text-slate-900 truncate" x-text="uploadForm.screenshot ? uploadForm.screenshot.name : 'Screenshot selected'"></div>
                                            <div class="text-[10px] font-mono text-emerald-600 font-semibold flex items-center gap-1 mt-0.5">
                                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                ചിത്രം റെഡിയാണ്
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" 
                                            @click.stop="removeScreenshot()" 
                                            class="relative z-20 px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 font-anek text-xs font-bold transition-colors cursor-pointer border-0 shrink-0">
                                        മാറ്റുക
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Error Alert -->
                    <div x-cloak x-show="uploadError" class="p-3.5 rounded-xl bg-red-100 text-red-900 font-anek text-xs">
                        <span x-text="uploadError"></span>
                    </div>

                    <!-- Submit & Download Button -->
                    <div class="pt-2">
                        <button type="button" 
                                @click="submitUpload()" 
                                :disabled="isUploading"
                                style="background-color: #be1e2d !important; color: #ffffff !important;"
                                class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-[#be1e2d] via-red-600 to-[#991522] hover:brightness-105 disabled:opacity-50 text-white font-anek font-bold text-base sm:text-lg shadow-xl shadow-red-900/25 border-0 transition-all cursor-pointer flex items-center justify-center gap-2.5">
                            <template x-if="isUploading">
                                <svg class="w-5 h-5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                            <template x-if="!isUploading">
                                <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </template>
                            <span x-text="isUploading ? 'സ്ക്രീൻഷോട്ട് അപ്‌ലോഡ് ചെയ്യുന്നു...' : 'വിവരങ്ങൾ നൽകി രസീത് പോസ്റ്റർ ഡൗൺലോഡ് ചെയ്യുക'"></span>
                        </button>
                    </div>
                
                </div>

                <!-- FOUND / GENERATED RECEIPT STATE (ONE CLICK DOWNLOAD) -->
                <div x-cloak x-show="foundReceipt" class="mt-6 rounded-2xl bg-slate-50 p-5 sm:p-6 space-y-4 border-0">
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-200/60">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold px-3 py-1 rounded-full bg-slate-900 text-white" x-text="foundReceipt?.receipt_number"></span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-mono font-semibold">
                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Verified Receipt
                            </span>
                        </div>
                        <span class="text-xs font-mono text-slate-500" x-text="foundReceipt?.date"></span>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <span class="block text-[11px] font-mono text-slate-500 uppercase">Donor Name</span>
                            <span class="font-anek text-lg sm:text-xl font-bold text-slate-900" x-text="foundReceipt?.donor_name"></span>
                        </div>
                        <div class="text-right">
                            <span class="block text-[11px] font-mono text-slate-500 uppercase">Amount</span>
                            <span class="font-rockwell text-2xl sm:text-3xl font-bold text-[#94121b]" x-text="'₹' + foundReceipt?.formatted_amount"></span>
                        </div>
                    </div>

                    <!-- Direct Action Buttons -->
                    <div class="space-y-3 pt-2">
                        <!-- Primary Download Poster Button -->
                        <a :href="foundReceipt?.download_url" 
                           target="_blank" 
                           download
                           style="background-color: #be1e2d !important; color: #ffffff !important;"
                           class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-[#be1e2d] via-red-600 to-[#991522] hover:brightness-105 text-white font-sora font-bold text-sm sm:text-base shadow-xl shadow-red-900/25 border-0 transition-all cursor-pointer flex items-center justify-center gap-2.5">
                            <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>രസീത് പോസ്റ്റർ ഡൗൺലോഡ് ചെയ്യുക (Download Poster)</span>
                        </a>

                        <!-- View Poster & View Voucher Dual Buttons -->
                        <div class="grid grid-cols-2 gap-3">
                            <a :href="foundReceipt?.image_url" 
                               target="_blank" 
                               style="background-color: #0f172a !important; color: #ffffff !important;"
                               class="py-3.5 px-4 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-sora text-xs sm:text-sm font-bold shadow-md border-0 transition-all cursor-pointer flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span>പോസ്റ്റർ കാണുക</span>
                            </a>
                            <a :href="foundReceipt?.view_url" 
                               target="_blank" 
                               style="background-color: #f1f5f9 !important; color: #0f172a !important;"
                               class="py-3.5 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-900 font-sora text-xs sm:text-sm font-bold shadow-2xs border-0 transition-all cursor-pointer flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>രസീത് വൗച്ചർ</span>
                            </a>
                        </div>

                        <!-- Reset / Submit Another -->
                        <div class="text-center pt-1">
                            <button type="button" 
                                    @click="resetReceiptSearch()" 
                                    class="text-xs font-anek text-slate-500 hover:text-slate-800 underline transition-colors cursor-pointer border-0 bg-transparent">
                                മറ്റൊരു രസീത് സമർപ്പിക്കുക / പരിശോധിക്കുക
                            </button>
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
                            <div class="font-anek font-bold text-slate-900 text-sm sm:text-base">ഔദ്യോഗിക QR കോഡ് ഡൗൺലോഡ് ചെയ്യുക</div>
                            <div class="font-anek text-xs sm:text-sm text-slate-600 leading-relaxed">
                                'Download QR to Gallery' വഴി QR സേവ് ചെയ്ത് Google Pay, PhonePe അല്ലെങ്കിൽ Paytm വഴി പണമടയ്ക്കുക.
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-3.5 rounded-2xl bg-slate-50">
                        <span class="w-9 h-9 rounded-full bg-[#be1e2d] text-white font-rockwell font-bold text-sm flex items-center justify-center shrink-0 shadow-sm border-0">
                            2
                        </span>
                        <div>
                            <div class="font-anek font-bold text-slate-900 text-sm sm:text-base">രസീത് പോസ്റ്റർ സ്വന്തമാക്കുക</div>
                            <div class="font-anek text-xs sm:text-sm text-slate-600 leading-relaxed">
                                പേയ്‌മെന്റ് സ്ക്രീൻഷോട്ടും വിവരങ്ങളും നൽകി ഔദ്യോഗിക രസീത് പോസ്റ്റർ ഡൗൺലോഡ് ചെയ്യുക.
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
        payeeName: "JAMIA MARKAZU SSAQUAFATHISSUNNIYYA",
        merchantCode: "8661",
        orgId: "159059",
        mode: "02",
        selectedAmount: 100,
        customInput: '',
        copiedUPI: false,
        modalOpen: false,

        receiptTab: 'upload',
        uploadForm: {
            transaction_id: '',
            whatsapp_number: '',
            donor_name: '',
            amount: 100,
            donor_place: '',
            screenshot: null,
            screenshotPreview: null
        },
        isUploading: false,
        uploadError: '',

        txnQuery: '',
        isSearching: false,
        searchError: '',
        foundReceipt: null,
        notFound: false,

        selectAmount(amt) {
            this.selectedAmount = Number(amt);
            this.uploadForm.amount = Number(amt);
            const step2 = document.getElementById('step-02');
            if (step2) {
                step2.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        },

        handleFileSelect(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                this.uploadError = 'ദയവായി ഒരു ചിത്രം (JPG, PNG, WEBP) തിരഞ്ഞെടുക്കുക.';
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                this.uploadError = 'ഫയൽ സൈസ് 10MB-യിൽ കുറവായിരിക്കണം.';
                return;
            }
            this.uploadForm.screenshot = file;
            this.uploadError = '';
            const reader = new FileReader();
            reader.onload = (e) => {
                this.uploadForm.screenshotPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        removeScreenshot() {
            this.uploadForm.screenshot = null;
            this.uploadForm.screenshotPreview = null;
            const fileInput = document.getElementById('receipt-screenshot-input');
            if (fileInput) fileInput.value = '';
        },

        async pasteUploadTxnId() {
            try {
                if (navigator.clipboard && navigator.clipboard.readText) {
                    const text = await navigator.clipboard.readText();
                    if (text && text.trim()) {
                        this.uploadForm.transaction_id = text.trim();
                        return;
                    }
                }
            } catch (err) {}
            const manual = prompt("UPI Transaction ID / UTR ഇവിടെ നൽകുക:");
            if (manual && manual.trim()) {
                this.uploadForm.transaction_id = manual.trim();
            }
        },

        async submitUpload() {
            if (!this.uploadForm.transaction_id.trim()) {
                this.uploadError = 'ദയവായി Transaction ID / UTR നൽകുക.';
                return;
            }
            if (!this.uploadForm.whatsapp_number.trim()) {
                this.uploadError = 'ദയവായി WhatsApp നമ്പർ നൽകുക.';
                return;
            }
            if (!this.uploadForm.donor_name.trim()) {
                this.uploadError = 'ദയവായി നിങ്ങളുടെ പേര് നൽകുക.';
                return;
            }
            if (!this.uploadForm.amount || Number(this.uploadForm.amount) < 1) {
                this.uploadError = 'ദയവായി ശരിയായ തുക നൽകുക.';
                return;
            }
            if (!this.uploadForm.screenshot) {
                this.uploadError = 'ദയവായി പേയ്‌മെന്റ് സ്ക്രീൻഷോട്ട് അപ്‌ലോഡ് ചെയ്യുക.';
                return;
            }

            this.isUploading = true;
            this.uploadError = '';

            try {
                const formData = new FormData();
                formData.append('transaction_id', this.uploadForm.transaction_id.trim());
                formData.append('whatsapp_number', this.uploadForm.whatsapp_number.trim());
                formData.append('donor_name', this.uploadForm.donor_name.trim());
                formData.append('amount', Number(this.uploadForm.amount));
                formData.append('screenshot', this.uploadForm.screenshot);
                if (this.uploadForm.donor_place) {
                    formData.append('donor_place', this.uploadForm.donor_place.trim());
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch("{{ route('donate.upload-receipt') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();
                if (data.success && data.receipt) {
                    this.foundReceipt = data.receipt;
                    this.uploadError = '';
                    this.removeScreenshot();
                    this.$nextTick(() => {
                        const el = document.getElementById('receipt-download');
                        if (el) el.scrollIntoView({ behavior: 'smooth' });
                    });
                } else {
                    this.uploadError = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'അപ്‌ലോഡ് ചെയ്യുന്നതിൽ തടസ്സം നേരിട്ടു.');
                }
            } catch (e) {
                this.uploadError = 'നെറ്റ്‌വർക്ക് തകരാർ കാരണം അപ്‌ലോഡ് പൂർത്തിയായില്ല. വീണ്ടും ശ്രമിക്കുക.';
            } finally {
                this.isUploading = false;
            }
        },

        async pasteTxnId() {
            try {
                if (navigator.clipboard && navigator.clipboard.readText) {
                    const text = await navigator.clipboard.readText();
                    if (text && text.trim()) {
                        this.txnQuery = text.trim();
                        this.searchReceipt();
                        return;
                    }
                }
            } catch (err) {
                // Clipboard permission blocked or unsupported
            }
            const manual = prompt("UPI Transaction ID / UTR ഇവിടെ നൽകുക:");
            if (manual && manual.trim()) {
                this.txnQuery = manual.trim();
                this.searchReceipt();
            }
        },

        async searchReceipt() {
            const q = (this.txnQuery || '').trim();
            if (!q) return;
            this.isSearching = true;
            this.searchError = '';
            this.foundReceipt = null;
            this.notFound = false;
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch("{{ route('donate.check-receipt') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ transaction_id: q })
                });
                const data = await res.json();
                if (data.found && data.receipt) {
                    this.foundReceipt = data.receipt;
                    this.notFound = false;
                } else {
                    this.foundReceipt = null;
                    this.notFound = true;
                }
            } catch (e) {
                this.searchError = 'പരിശോധിക്കുന്നതിനിടയിൽ തടസ്സം നേരിട്ടു. വീണ്ടും ശ്രമിക്കുക.';
            } finally {
                this.isSearching = false;
            }
        },

        sendWhatsAppVerification() {
            const q = (this.txnQuery || '').trim();
            const msg = `QUAF 2026 — രസീത് വെരിഫിക്കേഷൻ അഭ്യർത്ഥന\n\n` +
                        `Transaction ID / UTR: ${q}\n` +
                        `തുക: ₹${Number(this.selectedAmount).toLocaleString('en-IN')}\n\n` +
                        `ഞാൻ QUAF 2026 ഫണ്ടിലേക്ക് ഓൺലൈനായി പണം അയച്ചിട്ടുണ്ട്. ബാങ്ക് ക്രെഡിറ്റ് പരിശോധിച്ച് ഔദ്യോഗിക രസീത് ലഭ്യമാക്കാൻ അപേക്ഷിക്കുന്നു.`;
            const text = encodeURIComponent(msg);
            window.open("https://wa.me/919074680630?text=" + text, "_blank");
        },

        resetReceiptSearch() {
            this.txnQuery = '';
            this.foundReceipt = null;
            this.notFound = false;
            this.searchError = '';
            this.uploadError = '';
            this.removeScreenshot();
        },

        getOfficialQrLink() {
            return "upi://pay?pa=qr.9074680630@sib&pn=JAMIA%MARKAZU%SSAQUAFATHISSUNNIYYA&mc=8661&cu=INR&orgid=159059&mode=02&sign=MEYCIQDPR9QWDEqm6Zq8M8DKyE/193h6NmYq9ieoAufr8CoevgIhANpl1HCh5O9y0zlyEeStHXQSlUfPeEnzMfCer3wgAF5v";
        },

        getUpiLink() {
            return this.getOfficialQrLink();
        },

        async autoCopyUpi() {
            try {
                await navigator.clipboard.writeText(this.upiId);
                this.copiedUPI = true;
                setTimeout(() => { this.copiedUPI = false; }, 3500);
            } catch (e) {
                // Ignore if clipboard denied
            }
        },

        payNow() {
            this.autoCopyUpi();
            window.location.href = this.getOfficialQrLink();
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
