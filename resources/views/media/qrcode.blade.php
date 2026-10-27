@extends('layouts.media')

@section('title', 'QR Code Generator | QUAF Media')

@section('content')
<div x-data="qrGeneratorApp()" x-init="init()" class="space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Top Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-[#1e1014] text-white p-6 sm:p-8 rounded-2xl shadow-xl relative overflow-hidden print:hidden">
        <div class="relative z-10 max-w-3xl">
            <div class="flex items-center gap-2 mb-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#be1e2d] text-white">
                    Publicity & Media Tool
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-mono tracking-wider bg-white/10 text-purple-300">
                    Vector SVG & HD PNG
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight flex items-center gap-3">
                <span>QUAF QR Code Studio</span>
                <span class="text-xs sm:text-sm font-normal text-slate-400 font-anek">ക്യുആർ കോഡ് ജനറേറ്റർ</span>
            </h1>
            <p class="text-slate-300 text-xs sm:text-sm mt-2 leading-relaxed font-anek">
                വെബ്സൈറ്റ് ലിങ്കുകൾ, റിസൾട്ടുകൾ, ഷെഡ്യൂൾ, ഡി.പി മേക്കർ, അല്ലെങ്കിൽ ഏത് കസ്റ്റം ലിങ്കും നൽകി ഹൈ-റെസലൂഷൻ ക്യുആർ കോഡുകളും ഒഫീഷ്യൽ ടേബിൾ സ്റ്റാൻഡ് കാർഡുകളും നിമിഷനേരം കൊണ്ട് തയ്യാറാക്കാം.
            </p>
        </div>
    </div>

    <!-- Main Studio Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Column: Controls & Configuration (7 cols) -->
        <div class="lg:col-span-7 space-y-6 print:hidden">
            
            <!-- 1. URL / Link Input Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <label for="qr-target-url" class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        <span>Target Link / URL</span>
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="pasteUrl()" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <span>Paste</span>
                        </button>
                        <button type="button" @click="url = ''" x-show="url.length > 0" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors cursor-pointer">
                            <span>Clear</span>
                        </button>
                    </div>
                </div>

                <div class="relative">
                    <input id="qr-target-url" 
                           type="text" 
                           x-model="url" 
                           @input="scheduleRender()"
                           placeholder="https://quaf.ihyaussunna.in/..." 
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-[#be1e2d] focus:ring-2 focus:ring-[#be1e2d]/20 text-sm font-mono text-slate-800 transition-all outline-hidden">
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-400">
                    <span>Enter any full URL or custom link</span>
                    <span x-text="url.length + ' characters'"></span>
                </div>

                <!-- Quick Presets -->
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block">
                        Quick QUAF Presets (1-Click Fill)
                    </span>
                    <div class="flex flex-wrap gap-2">
                        @foreach($presets as $preset)
                        <button type="button" 
                                @click="applyPreset('{{ $preset['url'] }}', '{{ $preset['label'] }}', '{{ $preset['color'] }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-semibold transition-all cursor-pointer"
                                :class="url === '{{ $preset['url'] }}' ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200'">
                            <span class="w-2 h-2 rounded-full" style="background-color: {{ $preset['color'] }};"></span>
                            <span>{{ $preset['badge'] }}</span>
                            <span class="text-[10px] text-slate-400 font-normal">({{ $preset['label'] }})</span>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- 2. Display Mode Selector -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-2xs space-y-4">
                <div class="text-xs font-black uppercase tracking-wider text-slate-700">
                    Display Template Mode
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" 
                            @click="displayMode = 'standalone'; scheduleRender();"
                            class="p-4 rounded-xl border-2 text-left transition-all cursor-pointer"
                            :class="displayMode === 'standalone' ? 'border-[#be1e2d] bg-red-50/30 ring-2 ring-[#be1e2d]/10' : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-slate-900">Standalone QR Code</span>
                            <span class="w-3.5 h-3.5 rounded-full border-2 flex items-center justify-center"
                                  :class="displayMode === 'standalone' ? 'border-[#be1e2d] bg-[#be1e2d]' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="displayMode === 'standalone'"></span>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-normal">
                            Pure clean QR code with optional center festival emblem. Perfect for graphics, posters & web.
                        </p>
                    </button>

                    <button type="button" 
                            @click="displayMode = 'card'; scheduleRender();"
                            class="p-4 rounded-xl border-2 text-left transition-all cursor-pointer"
                            :class="displayMode === 'card' ? 'border-[#be1e2d] bg-red-50/30 ring-2 ring-[#be1e2d]/10' : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-slate-900">Table Stand / Poster Card</span>
                            <span class="w-3.5 h-3.5 rounded-full border-2 flex items-center justify-center"
                                  :class="displayMode === 'card' ? 'border-[#be1e2d] bg-[#be1e2d]' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="displayMode === 'card'"></span>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-normal">
                            Framed QUAF branded card with header, customizable title, scan instructions & URL.
                        </p>
                    </button>
                </div>

                <!-- Extra fields for Card Mode -->
                <div x-show="displayMode === 'card'" x-transition class="pt-4 border-t border-slate-100 space-y-3">
                    <div>
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600 block mb-1">
                            Card Headline / Title
                        </label>
                        <input type="text" 
                               x-model="cardTitle" 
                               @input="scheduleRender()"
                               placeholder="e.g. SCAN TO VIEW LIVE RESULTS" 
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#be1e2d] focus:ring-1 focus:ring-[#be1e2d] text-slate-800">
                    </div>
                    <div>
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600 block mb-1">
                            Card Subtitle / Category
                        </label>
                        <input type="text" 
                               x-model="cardSubtitle" 
                               @input="scheduleRender()"
                               placeholder="e.g. Official Markaz Cultural Festival '26" 
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#be1e2d] focus:ring-1 focus:ring-[#be1e2d] text-slate-800">
                    </div>
                </div>
            </div>

            <!-- 3. Styling & Options Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-2xs space-y-5">
                <div class="text-xs font-black uppercase tracking-wider text-slate-700">
                    Customization & Colors
                </div>

                <!-- Foreground Color -->
                <div class="space-y-2">
                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600 flex items-center justify-between">
                        <span>QR Code Color (Foreground)</span>
                        <span class="font-mono text-slate-500 font-normal" x-text="fgColor"></span>
                    </label>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <template x-for="c in colorPalette" :key="c.hex">
                            <button type="button" 
                                    @click="fgColor = c.hex; scheduleRender();"
                                    class="w-8 h-8 rounded-full border-2 transition-transform cursor-pointer relative"
                                    :class="fgColor.toLowerCase() === c.hex.toLowerCase() ? 'scale-110 border-slate-900 shadow-md' : 'border-transparent hover:scale-105'"
                                    :style="`background-color: ${c.hex}`"
                                    :title="c.name">
                                <span x-show="fgColor.toLowerCase() === c.hex.toLowerCase()" class="absolute inset-0 flex items-center justify-center text-white text-xs font-black drop-shadow-sm">✓</span>
                            </button>
                        </template>
                        <!-- Custom color picker -->
                        <label class="w-8 h-8 rounded-full border-2 border-slate-300 hover:border-slate-500 flex items-center justify-center cursor-pointer overflow-hidden bg-gradient-to-tr from-pink-500 via-amber-400 to-indigo-500 text-white text-[10px] font-bold" title="Custom Color">
                            <input type="color" x-model="fgColor" @input="scheduleRender()" class="opacity-0 absolute w-0 h-0">
                            <span>+</span>
                        </label>
                    </div>
                </div>

                <!-- Background Color & Margin -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-slate-100">
                    <!-- Background -->
                    <div>
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600 block mb-1.5">
                            Background Color
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="bgColor = '#ffffff'; scheduleRender();"
                                    class="px-3 py-1.5 rounded-lg border text-xs font-semibold cursor-pointer"
                                    :class="bgColor === '#ffffff' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-200'">
                                White
                            </button>
                            <button type="button" 
                                    @click="bgColor = '#f8fafc'; scheduleRender();"
                                    class="px-3 py-1.5 rounded-lg border text-xs font-semibold cursor-pointer"
                                    :class="bgColor === '#f8fafc' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-700 border-slate-200'">
                                Soft Cream
                            </button>
                            <label class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-600 hover:bg-slate-50 flex items-center gap-1.5 cursor-pointer">
                                <input type="color" x-model="bgColor" @input="scheduleRender()" class="w-4 h-4 rounded cursor-pointer border-0">
                                <span class="font-mono text-[10px]" x-text="bgColor"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Margin / Quiet Zone -->
                    <div>
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600 block mb-1.5">
                            Quiet Zone (Margin)
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="margin = 0; scheduleRender();"
                                    class="px-3 py-1.5 rounded-lg border text-xs font-semibold cursor-pointer"
                                    :class="margin === 0 ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-200'">
                                None (0)
                            </button>
                            <button type="button" 
                                    @click="margin = 1; scheduleRender();"
                                    class="px-3 py-1.5 rounded-lg border text-xs font-semibold cursor-pointer"
                                    :class="margin === 1 ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-200'">
                                Compact (1)
                            </button>
                            <button type="button" 
                                    @click="margin = 2; scheduleRender();"
                                    class="px-3 py-1.5 rounded-lg border text-xs font-semibold cursor-pointer"
                                    :class="margin === 2 ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-200'">
                                Wide (2)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Center Logo Toggle & Resolution -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-slate-100 items-center">
                    <!-- Center Logo -->
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" 
                                   x-model="includeLogo" 
                                   @change="scheduleRender()"
                                   class="w-4 h-4 rounded text-[#be1e2d] focus:ring-[#be1e2d] border-slate-300">
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">QUAF Center Emblem</span>
                                <span class="text-[11px] text-slate-500">Overlay official logo in center</span>
                            </div>
                        </label>
                    </div>

                    <!-- Output Resolution -->
                    <div>
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600 block mb-1.5">
                            Export Resolution: <span class="text-slate-900 font-mono" x-text="exportSize + ' × ' + exportSize + 'px'"></span>
                        </label>
                        <select x-model="exportSize" @change="scheduleRender()" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-800 bg-white">
                            <option value="500">500 × 500 px (Standard Web / Social)</option>
                            <option value="1000">1000 × 1000 px (High-Res Document)</option>
                            <option value="1500">1500 × 1500 px (Poster / Print HD)</option>
                            <option value="2000">2000 × 2000 px (Ultra HD Flex Print)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Live Interactive Preview & Action Dock (5 cols) -->
        <div class="lg:col-span-5 space-y-5 lg:sticky lg:top-20">
            
            <!-- Live Preview Container -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-md flex flex-col items-center justify-center relative overflow-hidden"
                 :class="{'print:p-0 print:border-none print:shadow-none': true}">
                
                <div class="w-full flex items-center justify-between pb-4 border-b border-slate-100 mb-4 print:hidden">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Live Preview</span>
                    </span>
                    <span class="text-[11px] font-mono text-slate-500" x-text="displayMode === 'card' ? 'Card Layout' : 'Standalone QR'"></span>
                </div>

                <!-- Canvas Wrapper -->
                <div class="flex items-center justify-center w-full min-h-[320px] bg-slate-50/50 rounded-xl p-4 border border-dashed border-slate-200 relative">
                    <!-- Loading Spinner -->
                    <div x-show="isRendering" class="absolute inset-0 bg-white/70 backdrop-blur-xs flex items-center justify-center z-20" style="display: none;">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-700 bg-white px-4 py-2 rounded-xl shadow-lg border border-slate-200">
                            <svg class="w-4 h-4 animate-spin text-[#be1e2d]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Rendering QR...</span>
                        </div>
                    </div>

                    <!-- The Working Canvas (Used for live display & PNG export) -->
                    <canvas id="qr-canvas" class="max-w-full h-auto rounded-lg shadow-sm transition-all" style="max-height: 440px; object-fit: contain;"></canvas>
                </div>

                <!-- URL text preview -->
                <div class="mt-4 text-center max-w-full px-2 print:hidden">
                    <div class="text-[11px] font-mono text-slate-500 truncate" :title="url" x-text="url"></div>
                </div>
            </div>

            <!-- Action Buttons Panel -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs space-y-3 print:hidden">
                <div class="text-xs font-black uppercase tracking-wider text-slate-700">
                    Export & Share Actions
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <!-- Download High-Res PNG -->
                    <button type="button" 
                            @click="downloadPng()" 
                            class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white text-xs font-bold shadow-md shadow-[#be1e2d]/20 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>Download PNG</span>
                    </button>

                    <!-- Download Vector SVG -->
                    <button type="button" 
                            @click="downloadSvg()" 
                            class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-md transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <span>Download SVG</span>
                    </button>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <!-- Copy Image to Clipboard -->
                    <button type="button" 
                            @click="copyImage()" 
                            class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition-all cursor-pointer"
                            :title="'Copy image directly to clipboard for pasting into WhatsApp, Photoshop or Word'">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        <span x-text="copiedImageNotice ? 'Copied!' : 'Copy Img'"></span>
                    </button>

                    <!-- Copy Target URL -->
                    <button type="button" 
                            @click="copyUrl()" 
                            class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition-all cursor-pointer"
                            :title="'Copy the link to clipboard'">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        <span x-text="copiedUrlNotice ? 'Copied!' : 'Copy URL'"></span>
                    </button>

                    <!-- Print Stand / Card -->
                    <button type="button" 
                            @click="triggerPrint()" 
                            class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition-all cursor-pointer"
                            :title="'Print directly to paper or table stand'">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        <span>Print</span>
                    </button>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-[11px] text-slate-500 leading-relaxed font-anek">
                    സൂചന: ഡിസൈൻ സോഫ്റ്റ്‌വെയറുകളായ ഇല്ലസ്ട്രേറ്റർ, കൊറേൽഡ്രോ എന്നിവയിൽ ഉപയോഗിക്കാൻ <strong>Download SVG</strong> സെലക്ട് ചെയ്യുക. ഫ്ലെക്സ് പ്രിന്റിംഗിന് റെസലൂഷൻ 2000px സെലക്ട് ചെയ്യാവുന്നതാണ്.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print-Only Dedicated Container -->
<div id="print-container" class="hidden print:flex print:items-center print:justify-center print:min-h-screen">
    <img id="print-image" src="" alt="QUAF QR Code" class="max-w-[85vw] max-h-[85vh] object-contain">
</div>

<style>
@media print {
    body {
        background: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    aside, header, nav, .print\:hidden {
        display: none !important;
    }
    #print-container {
        display: flex !important;
    }
}
</style>

<script>
function qrGeneratorApp() {
    return {
        url: @json($defaultUrl),
        fgColor: '#be1e2d',
        bgColor: '#ffffff',
        exportSize: 1000,
        margin: 1,
        includeLogo: true,
        displayMode: 'standalone', // 'standalone' | 'card'
        cardTitle: 'QUAF FESTIVAL 2026',
        cardSubtitle: 'Scan to View / Explore Live',
        isRendering: false,
        copiedImageNotice: false,
        copiedUrlNotice: false,
        renderTimeout: null,
        logoImage: null,

        colorPalette: [
            { name: 'Festival Red', hex: '#be1e2d' },
            { name: 'Deep Black', hex: '#0f172a' },
            { name: 'Pure Black', hex: '#000000' },
            { name: 'Emerald Green', hex: '#059669' },
            { name: 'Royal Blue', hex: '#2563eb' },
            { name: 'Amber Gold', hex: '#d97706' },
            { name: 'Purple', hex: '#7c3aed' },
            { name: 'Teal', hex: '#0d9488' },
        ],

        init() {
            // Preload QUAF logo
            this.logoImage = new Image();
            this.logoImage.crossOrigin = 'Anonymous';
            this.logoImage.src = '{{ asset("images/festival-round-logo.png") }}';
            this.logoImage.onload = () => {
                this.render();
            };
            this.logoImage.onerror = () => {
                // Fallback to favicon
                this.logoImage = new Image();
                this.logoImage.crossOrigin = 'Anonymous';
                this.logoImage.src = '{{ asset("favicon.png") }}';
                this.logoImage.onload = () => this.render();
            };

            this.$nextTick(() => {
                this.render();
            });
        },

        scheduleRender() {
            clearTimeout(this.renderTimeout);
            this.renderTimeout = setTimeout(() => {
                this.render();
            }, 150);
        },

        applyPreset(presetUrl, label, color) {
            this.url = presetUrl;
            if (color) {
                this.fgColor = color;
            }
            if (label) {
                this.cardTitle = label.toUpperCase();
            }
            this.scheduleRender();
        },

        hexToRgbString(hex) {
            hex = (hex || '#000000').replace('#', '').trim();
            if (hex.length === 3) {
                hex = hex.split('').map(c => c + c).join('');
            }
            const r = parseInt(hex.substring(0, 2), 16) || 0;
            const g = parseInt(hex.substring(2, 4), 16) || 0;
            const b = parseInt(hex.substring(4, 6), 16) || 0;
            return `${r}-${g}-${b}`;
        },

        getQrApiUrl(format = 'png') {
            const data = encodeURIComponent(this.url.trim() || 'https://quaf.ihyaussunna.in');
            const fg = this.hexToRgbString(this.fgColor);
            const bg = this.hexToRgbString(this.bgColor);
            const size = Math.min(Math.max(parseInt(this.exportSize) || 1000, 300), 2000);
            const formatParam = format === 'svg' ? '&format=svg' : '';
            return `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&color=${fg}&bgcolor=${bg}&margin=${this.margin}${formatParam}&data=${data}`;
        },

        render() {
            const canvas = document.getElementById('qr-canvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            this.isRendering = true;

            const qrImg = new Image();
            qrImg.crossOrigin = 'Anonymous';
            qrImg.src = this.getQrApiUrl('png');

            qrImg.onload = () => {
                const targetSize = parseInt(this.exportSize) || 1000;

                if (this.displayMode === 'standalone') {
                    // STANDALONE MODE
                    canvas.width = targetSize;
                    canvas.height = targetSize;

                    // Draw QR code
                    ctx.fillStyle = this.bgColor;
                    ctx.fillRect(0, 0, targetSize, targetSize);
                    ctx.drawImage(qrImg, 0, 0, targetSize, targetSize);

                    // Overlay center logo if enabled
                    if (this.includeLogo && this.logoImage && this.logoImage.complete && this.logoImage.naturalWidth > 0) {
                        const logoSize = Math.round(targetSize * 0.22);
                        const logoX = Math.round((targetSize - logoSize) / 2);
                        const logoY = Math.round((targetSize - logoSize) / 2);

                        // White rounded background medallion
                        const pad = Math.round(logoSize * 0.12);
                        const medX = logoX - pad;
                        const medY = logoY - pad;
                        const medSize = logoSize + (pad * 2);
                        const radius = Math.round(medSize * 0.25);

                        ctx.save();
                        ctx.shadowColor = 'rgba(0, 0, 0, 0.18)';
                        ctx.shadowBlur = Math.round(targetSize * 0.02);
                        ctx.shadowOffsetY = Math.round(targetSize * 0.005);
                        ctx.fillStyle = '#ffffff';

                        this.drawRoundedRect(ctx, medX, medY, medSize, medSize, radius);
                        ctx.fill();
                        ctx.restore();

                        // Border around medallion
                        ctx.save();
                        ctx.lineWidth = Math.max(2, Math.round(targetSize * 0.004));
                        ctx.strokeStyle = this.fgColor;
                        this.drawRoundedRect(ctx, medX, medY, medSize, medSize, radius);
                        ctx.stroke();
                        ctx.restore();

                        // Draw Logo image inside
                        ctx.drawImage(this.logoImage, logoX, logoY, logoSize, logoSize);
                    }
                } else {
                    // CARD / TABLE STAND MODE (Aspect Ratio 3:4 e.g. 1200 x 1600)
                    const cardW = targetSize;
                    const cardH = Math.round(targetSize * 1.35);
                    canvas.width = cardW;
                    canvas.height = cardH;

                    // 1. Background
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, cardW, cardH);

                    // Top Decorative Accent Band
                    const headerH = Math.round(cardH * 0.15);
                    const grad = ctx.createLinearGradient(0, 0, cardW, headerH);
                    grad.addColorStop(0, '#121417');
                    grad.addColorStop(1, '#1e1014');
                    ctx.fillStyle = grad;
                    ctx.fillRect(0, 0, cardW, headerH);

                    // Red festival bottom bar on header
                    ctx.fillStyle = '#be1e2d';
                    ctx.fillRect(0, headerH - Math.round(cardH * 0.008), cardW, Math.round(cardH * 0.008));

                    // 2. Header Content
                    const logoH = Math.round(headerH * 0.55);
                    const logoW = logoH;
                    const headerLogoX = Math.round(cardW * 0.08);
                    const headerLogoY = Math.round((headerH - logoH) / 2);

                    if (this.logoImage && this.logoImage.complete && this.logoImage.naturalWidth > 0) {
                        ctx.drawImage(this.logoImage, headerLogoX, headerLogoY, logoW, logoH);
                    }

                    // Header Text
                    ctx.fillStyle = '#ffffff';
                    ctx.font = `bold ${Math.round(cardW * 0.045)}px Sora, sans-serif`;
                    ctx.textAlign = 'left';
                    ctx.textBaseline = 'middle';
                    ctx.fillText('QUAF FESTIVAL 2026', headerLogoX + logoW + Math.round(cardW * 0.03), headerLogoY + (logoH * 0.35));

                    ctx.fillStyle = '#cbd5e1';
                    ctx.font = `500 ${Math.round(cardW * 0.024)}px Sora, sans-serif`;
                    ctx.fillText('Markaz Cultural Festival • Official Desk', headerLogoX + logoW + Math.round(cardW * 0.03), headerLogoY + (logoH * 0.72));

                    // 3. Card Title & Subtitle
                    const titleY = headerH + Math.round(cardH * 0.07);
                    ctx.fillStyle = '#0f172a';
                    ctx.font = `800 ${Math.round(cardW * 0.046)}px Sora, sans-serif`;
                    ctx.textAlign = 'center';
                    ctx.fillText(this.cardTitle || 'SCAN TO EXPLORE', cardW / 2, titleY);

                    ctx.fillStyle = '#64748b';
                    ctx.font = `500 ${Math.round(cardW * 0.026)}px Sora, sans-serif`;
                    ctx.fillText(this.cardSubtitle || 'Use your phone camera or QR scanner', cardW / 2, titleY + Math.round(cardH * 0.035));

                    // 4. QR Code Box with Card Shadow
                    const qrBoxSize = Math.round(cardW * 0.68);
                    const qrX = Math.round((cardW - qrBoxSize) / 2);
                    const qrY = titleY + Math.round(cardH * 0.075);

                    ctx.save();
                    ctx.shadowColor = 'rgba(0, 0, 0, 0.08)';
                    ctx.shadowBlur = Math.round(cardW * 0.04);
                    ctx.shadowOffsetY = Math.round(cardW * 0.015);
                    ctx.fillStyle = '#ffffff';
                    this.drawRoundedRect(ctx, qrX, qrY, qrBoxSize, qrBoxSize, Math.round(qrBoxSize * 0.06));
                    ctx.fill();
                    ctx.restore();

                    // Border around QR Box
                    ctx.save();
                    ctx.lineWidth = Math.round(cardW * 0.003);
                    ctx.strokeStyle = '#e2e8f0';
                    this.drawRoundedRect(ctx, qrX, qrY, qrBoxSize, qrBoxSize, Math.round(qrBoxSize * 0.06));
                    ctx.stroke();
                    ctx.restore();

                    // Draw QR inside box
                    const innerPad = Math.round(qrBoxSize * 0.06);
                    const innerQrSize = qrBoxSize - (innerPad * 2);
                    ctx.drawImage(qrImg, qrX + innerPad, qrY + innerPad, innerQrSize, innerQrSize);

                    // Center logo inside QR
                    if (this.includeLogo && this.logoImage && this.logoImage.complete && this.logoImage.naturalWidth > 0) {
                        const logoSize = Math.round(innerQrSize * 0.22);
                        const logoX = Math.round(qrX + (qrBoxSize - logoSize) / 2);
                        const logoY = Math.round(qrY + (qrBoxSize - logoSize) / 2);

                        const pad = Math.round(logoSize * 0.12);
                        const medX = logoX - pad;
                        const medY = logoY - pad;
                        const medSize = logoSize + (pad * 2);
                        const radius = Math.round(medSize * 0.25);

                        ctx.save();
                        ctx.shadowColor = 'rgba(0, 0, 0, 0.18)';
                        ctx.shadowBlur = Math.round(cardW * 0.02);
                        ctx.fillStyle = '#ffffff';
                        this.drawRoundedRect(ctx, medX, medY, medSize, medSize, radius);
                        ctx.fill();
                        ctx.restore();

                        ctx.save();
                        ctx.lineWidth = Math.max(2, Math.round(cardW * 0.004));
                        ctx.strokeStyle = this.fgColor;
                        this.drawRoundedRect(ctx, medX, medY, medSize, medSize, radius);
                        ctx.stroke();
                        ctx.restore();

                        ctx.drawImage(this.logoImage, logoX, logoY, logoSize, logoSize);
                    }

                    // 5. Instruction Pill below QR
                    const pillY = qrY + qrBoxSize + Math.round(cardH * 0.04);
                    const pillW = Math.round(cardW * 0.75);
                    const pillH = Math.round(cardH * 0.05);
                    const pillX = Math.round((cardW - pillW) / 2);

                    ctx.fillStyle = '#f8fafc';
                    this.drawRoundedRect(ctx, pillX, pillY, pillW, pillH, Math.round(pillH * 0.5));
                    ctx.fill();

                    ctx.strokeStyle = '#e2e8f0';
                    ctx.lineWidth = 1;
                    this.drawRoundedRect(ctx, pillX, pillY, pillW, pillH, Math.round(pillH * 0.5));
                    ctx.stroke();

                    ctx.fillStyle = '#0f172a';
                    ctx.font = `600 ${Math.round(cardW * 0.025)}px monospace`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    const displayUrl = this.url.replace(/^https?:\/\//i, '').replace(/\/$/, '');
                    ctx.fillText(displayUrl.length > 40 ? displayUrl.substring(0, 37) + '...' : displayUrl, cardW / 2, pillY + (pillH / 2));

                    // 6. Card Footer
                    const footerY = cardH - Math.round(cardH * 0.04);
                    ctx.fillStyle = '#94a3b8';
                    ctx.font = `500 ${Math.round(cardW * 0.022)}px Sora, sans-serif`;
                    ctx.textAlign = 'center';
                    ctx.fillText('quaf.ihyaussunna.in • Ihya-us-sunna Arts Festival', cardW / 2, footerY);
                }

                this.isRendering = false;
            };

            qrImg.onerror = () => {
                this.isRendering = false;
            };
        },

        drawRoundedRect(ctx, x, y, width, height, radius) {
            ctx.beginPath();
            ctx.moveTo(x + radius, y);
            ctx.lineTo(x + width - radius, y);
            ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
            ctx.lineTo(x + width, y + height - radius);
            ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
            ctx.lineTo(x + radius, y + height);
            ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
            ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y);
            ctx.closePath();
        },

        downloadPng() {
            const canvas = document.getElementById('qr-canvas');
            if (!canvas) return;

            const filename = (this.displayMode === 'card' ? 'quaf-qr-stand-card' : 'quaf-qr-code') + '-' + Date.now() + '.png';
            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL('image/png');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        async downloadSvg() {
            try {
                const svgUrl = this.getQrApiUrl('svg');
                const res = await fetch(svgUrl);
                const blob = await res.blob();
                const blobUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.download = 'quaf-qr-vector-' + Date.now() + '.svg';
                link.href = blobUrl;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                setTimeout(() => URL.revokeObjectURL(blobUrl), 1000);
            } catch (err) {
                // Fallback to direct window download
                window.open(this.getQrApiUrl('svg'), '_blank');
            }
        },

        async copyImage() {
            const canvas = document.getElementById('qr-canvas');
            if (!canvas) return;

            try {
                canvas.toBlob(async (blob) => {
                    if (!blob) return;
                    await navigator.clipboard.write([
                        new ClipboardItem({ 'image/png': blob })
                    ]);
                    this.copiedImageNotice = true;
                    setTimeout(() => {
                        this.copiedImageNotice = false;
                    }, 2500);
                }, 'image/png');
            } catch (err) {
                alert('Copying image to clipboard is not supported in this browser. Please use Download PNG.');
            }
        },

        async copyUrl() {
            try {
                await navigator.clipboard.writeText(this.url.trim());
                this.copiedUrlNotice = true;
                setTimeout(() => {
                    this.copiedUrlNotice = false;
                }, 2000);
            } catch (err) {
                // fallback
            }
        },

        async pasteUrl() {
            try {
                const text = await navigator.clipboard.readText();
                if (text && text.trim()) {
                    this.url = text.trim();
                    this.scheduleRender();
                }
            } catch (err) {
                const promptVal = prompt('Paste your URL / link here:', this.url);
                if (promptVal !== null) {
                    this.url = promptVal.trim();
                    this.scheduleRender();
                }
            }
        },

        triggerPrint() {
            const canvas = document.getElementById('qr-canvas');
            const printImg = document.getElementById('print-image');
            if (canvas && printImg) {
                printImg.src = canvas.toDataURL('image/png');
                setTimeout(() => {
                    window.print();
                }, 200);
            }
        }
    };
}
</script>
@endsection
