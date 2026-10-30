@extends('layouts.media')

@section('title', 'Poster Studio: ' . $result->program->name . ' | QUAF Media')

@section('content')
<div class="space-y-6" x-data="posterStudio()">
    <!-- Font Preloaders to force instant Canvas font availability -->
    <div class="sr-only" aria-hidden="true" style="position: absolute; left: -9999px; top: -9999px; visibility: hidden;">
        <span style="font-family: 'Mongolian Baiti';">0123456789</span>
        <span style="font-family: 'Rockwell Std'; font-weight: 300;">Rockwell Std 300</span>
        <span style="font-family: 'Rockwell Std'; font-weight: 400;">Rockwell Std 400</span>
        <span style="font-family: 'Rockwell Std'; font-weight: 700;">Rockwell Std 700</span>
        <span style="font-family: 'Rockwell Std'; font-weight: 800;">Rockwell Std 800</span>
    </div>

    <!-- Header Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('media.results.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    <span>Back to Results</span>
                </a>
                <span class="text-slate-300">&bull;</span>
                <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-slate-900 text-white">
                    {{ $result->program->code }}
                </span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                    {{ ucfirst($result->status) }}
                </span>
                @if($result->is_media_published)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Published
                    </span>
                @endif
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-purple-100 text-purple-800 border border-purple-200">
                    Pro Studio Engine
                </span>
            </div>

            <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight mt-1.5 font-sora">
                {{ $result->program->name }}
            </h1>
            <p class="text-xs text-slate-500">
                Zone: <strong class="text-slate-700">{{ $result->program->zone->name ?? $result->program->eligibility ?? 'All Zones' }}</strong> &bull;
                Stage: <strong class="text-slate-700">{{ $result->program->stage->name ?? 'Stage' }}</strong> &bull;
                Podium: <strong class="text-slate-700">1st, 2nd, and 3rd Places</strong>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Toggle Guidelines Button -->
            <button type="button" @click="guidelines.show = !guidelines.show; render();"
                    class="px-3.5 py-2.5 rounded-xl border text-xs font-bold flex items-center gap-1.5 transition-colors"
                    :class="guidelines.show ? 'bg-cyan-50 border-cyan-400 text-cyan-800' : 'bg-white border-slate-300 text-slate-600 hover:bg-slate-50'">
                <svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16M10 4v16M14 4v16"></path></svg>
                <span x-text="guidelines.show ? 'Hide Guidelines' : 'Show Guidelines'"></span>
            </button>

            <button type="button" @click="saveAsDefault()" :disabled="savingDefault"
                    class="px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors disabled:opacity-50">
                <span x-text="savingDefault ? 'Saving Defaults...' : 'Save as Default'"></span>
            </button>

            <button type="button" @click="downloadPoster()"
                    class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-2 transition-colors shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Download PNG</span>
            </button>

            <button type="button" @click="saveAndPublish()" :disabled="publishing"
                    class="px-5 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-black text-xs flex items-center gap-2 transition-all shadow-md shadow-[#be1e2d]/20 disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span x-text="publishing ? 'Publishing...' : 'Save & Publish'"></span>
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <div x-show="statusMsg" x-cloak
         :class="statusType === 'success' ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-red-50 border-red-300 text-red-900'"
         class="p-4 rounded-xl border text-xs font-bold transition-all flex items-center justify-between">
        <span x-text="statusMsg"></span>
        <button type="button" @click="statusMsg = ''" class="text-xs opacity-60 hover:opacity-100">&times;</button>
    </div>

    <!-- Studio Layout: Controls (Left) & Canvas Preview (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Controls Column (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            
            <!-- Pro Navigation Tabs -->
            <div class="flex items-center gap-1.5 p-1.5 bg-slate-200/80 rounded-2xl border border-slate-300/80 overflow-x-auto">
                <button type="button" @click="activeTab = 'layers'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5"
                        :class="activeTab === 'layers' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>Layers</span>
                </button>

                <button type="button" @click="activeTab = 'winners'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5"
                        :class="activeTab === 'winners' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>Winners & Scale</span>
                </button>

                <button type="button" @click="activeTab = 'elements'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5"
                        :class="activeTab === 'elements' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span>Elements & Rotate</span>
                </button>

                <button type="button" @click="activeTab = 'overlay'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5"
                        :class="activeTab === 'overlay' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Overlays</span>
                </button>

                <button type="button" @click="activeTab = 'guides'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5"
                        :class="activeTab === 'guides' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    <span>Guides</span>
                </button>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 1: RESULT LAYERS & PRECISION NUDGE -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'layers'" class="space-y-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-xs font-black uppercase tracking-wider text-slate-800">Result Layers (ലെയറുകൾ)</h2>
                            <p class="text-[11px] text-slate-500">Select any layer to inspect, reposition, or toggle visibility.</p>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-700">6 Layers</span>
                    </div>

                    <!-- Layers List (Top to Bottom Stack) -->
                    <div class="space-y-2">
                        <!-- Layer 1: Top Extra Overlay -->
                        <div class="flex items-center justify-between p-2.5 rounded-xl border transition-all cursor-pointer"
                             :class="activeLayer === 'overlay' ? 'bg-amber-50/70 border-amber-400 ring-1 ring-amber-400' : 'bg-slate-50 border-slate-200 hover:border-slate-300'"
                             @click="activeLayer = 'overlay'">
                            <div class="flex items-center gap-2.5">
                                <button type="button" @click.stop="overlay.enabled = !overlay.enabled; render();"
                                        class="p-1 rounded text-slate-400 hover:text-slate-800 transition" :class="overlay.enabled ? 'text-emerald-600' : 'text-slate-300 opacity-50'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                                <span class="text-xs font-bold text-slate-800">1. Top Extra Overlay (മുതൽക്കൂട്ട്)</span>
                            </div>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-white text-slate-600 border border-slate-200" x-text="overlay.layerOrder === 'above' ? 'Top Layer' : 'Behind Text'"></span>
                        </div>

                        <!-- Layer 2: Result Number -->
                        <div class="flex items-center justify-between p-2.5 rounded-xl border transition-all cursor-pointer"
                             :class="activeLayer === 'result_no' ? 'bg-red-50/70 border-red-400 ring-1 ring-red-400' : 'bg-slate-50 border-slate-200 hover:border-slate-300'"
                             @click="activeLayer = 'result_no'">
                            <div class="flex items-center gap-2.5">
                                <button type="button" @click.stop="settings.show_result_no = !settings.show_result_no; render();"
                                        class="p-1 rounded text-slate-400 hover:text-slate-800 transition" :class="settings.show_result_no ? 'text-emerald-600' : 'text-slate-300 opacity-50'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                                <span class="text-xs font-bold text-slate-800">2. Result Sequence Number (റിസൾട്ട് നമ്പർ)</span>
                            </div>
                            <span class="text-[11px] font-mono font-bold text-red-600" x-text="data.result_no"></span>
                        </div>

                        <!-- Layer 3: Program Name -->
                        <div class="flex items-center justify-between p-2.5 rounded-xl border transition-all cursor-pointer"
                             :class="activeLayer === 'competition' ? 'bg-blue-50/70 border-blue-400 ring-1 ring-blue-400' : 'bg-slate-50 border-slate-200 hover:border-slate-300'"
                             @click="activeLayer = 'competition'">
                            <div class="flex items-center gap-2.5">
                                <button type="button" @click.stop="settings.show_competition = !settings.show_competition; render();"
                                        class="p-1 rounded text-slate-400 hover:text-slate-800 transition" :class="settings.show_competition ? 'text-emerald-600' : 'text-slate-300 opacity-50'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                                <span class="text-xs font-bold text-slate-800">3. Program Name (മത്സര പേര്)</span>
                            </div>
                            <span class="text-[10px] text-slate-500 max-w-[120px] truncate" x-text="data.competition"></span>
                        </div>

                        <!-- Layer 4: Zone Capsule -->
                        <div class="flex items-center justify-between p-2.5 rounded-xl border transition-all cursor-pointer"
                             :class="activeLayer === 'category' ? 'bg-purple-50/70 border-purple-400 ring-1 ring-purple-400' : 'bg-slate-50 border-slate-200 hover:border-slate-300'"
                             @click="activeLayer = 'category'">
                            <div class="flex items-center gap-2.5">
                                <button type="button" @click.stop="settings.show_category = !settings.show_category; render();"
                                        class="p-1 rounded text-slate-400 hover:text-slate-800 transition" :class="settings.show_category ? 'text-emerald-600' : 'text-slate-300 opacity-50'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                                <span class="text-xs font-bold text-slate-800">4. Zone / Category Capsule (സോൺ)</span>
                            </div>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-white text-slate-700 border border-slate-200" x-text="data.category"></span>
                        </div>

                        <!-- Layer 5: Podium Winners & Groups -->
                        <div class="flex items-center justify-between p-2.5 rounded-xl border transition-all cursor-pointer"
                             :class="activeLayer === 'winners' ? 'bg-emerald-50/70 border-emerald-400 ring-1 ring-emerald-400' : 'bg-slate-50 border-slate-200 hover:border-slate-300'"
                             @click="activeLayer = 'winners'">
                            <div class="flex items-center gap-2.5">
                                <button type="button" @click.stop="settings.show_winners = !settings.show_winners; render();"
                                        class="p-1 rounded text-slate-400 hover:text-slate-800 transition" :class="settings.show_winners ? 'text-emerald-600' : 'text-slate-300 opacity-50'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                                <span class="text-xs font-bold text-slate-800">5. Podium Winners & Groups (വിജയികൾ)</span>
                            </div>
                            <span class="text-[10px] font-bold text-emerald-700">1st, 2nd, 3rd</span>
                        </div>

                        <!-- Layer 6: Background Frame Template -->
                        <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer"
                             @click="activeTab = 'templates'">
                            <div class="flex items-center gap-2.5">
                                <span class="p-1 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </span>
                                <span class="text-xs font-bold text-slate-700">6. Background Template (ടെംപ്ലേറ്റ്)</span>
                            </div>
                            <span class="text-[10px] text-blue-600 font-bold hover:underline">Change &rarr;</span>
                        </div>
                    </div>
                </div>

                <!-- Precision Alignment & Nudge D-Pad -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">
                            Precision Position & Align (<span class="text-[#be1e2d]" x-text="activeLayerLabel"></span>)
                        </h3>
                        <span class="text-[10px] text-slate-400 font-mono">1px step (Shift: 10px)</span>
                    </div>

                    <!-- Quick Align Row -->
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="alignLayer('left')" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center gap-1">
                            <span>⇤ Left</span>
                        </button>
                        <button type="button" @click="alignLayer('center')" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center gap-1">
                            <span>↔ Center</span>
                        </button>
                        <button type="button" @click="alignLayer('right')" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center gap-1">
                            <span>⇥ Right</span>
                        </button>
                    </div>

                    <!-- Nudge Arrows D-Pad -->
                    <div class="flex items-center justify-center gap-2 pt-1">
                        <button type="button" @click="nudgeLayer(-5, 0)" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold" title="Move Left">&larr;</button>
                        <div class="flex flex-col gap-1">
                            <button type="button" @click="nudgeLayer(0, -5)" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold" title="Move Up">&uarr;</button>
                            <button type="button" @click="nudgeLayer(0, 5)" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold" title="Move Down">&darr;</button>
                        </div>
                        <button type="button" @click="nudgeLayer(5, 0)" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold" title="Move Right">&rarr;</button>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 2: WINNERS LIST, MEDALS & UNIFIED SCALING -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'winners'" class="space-y-4">
                
                <!-- Unified Master Scale (Illustrator Style Bounding Box Scaling) -->
                <div class="bg-gradient-to-r from-amber-50 to-orange-50 p-5 rounded-2xl border border-amber-200/80 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-mono uppercase tracking-widest text-amber-900 font-black block">Master Scaling</span>
                            <h3 class="text-xs font-black text-amber-950 font-sora">Winners & Groups Unified Scale</h3>
                        </div>
                        <span class="font-mono font-black text-sm text-amber-900 bg-white/80 px-2.5 py-0.5 rounded-lg border border-amber-300"
                              x-text="Math.round(settings.winners_scale * 100) + '%'"></span>
                    </div>
                    <input type="range" min="0.5" max="1.8" step="0.02" x-model.number="settings.winners_scale" @input="render()" class="w-full accent-amber-600">
                    <p class="text-[11px] text-amber-900 leading-snug">
                        ഇല്ലസ്ട്രേറ്ററിലെ പോലെ വിജയികളുടെ പേര്, ഗ്രൂപ്പ്, മെഡൽ, ലൈൻ സ്പേസിംഗ് എന്നിവയെല്ലാം ഒരുമിച്ച് ആനുപാതികമായി വലുതാക്കാനും ചെറുതാക്കാനും ഈ സ്ലൈഡർ ഉപയോഗിക്കാം.
                    </p>
                </div>

                <!-- Medal System Selector -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">Medal Style (മെഡൽ മാറ്റങ്ങൾ)</h3>
                        <span class="text-[10px] text-slate-400">Per Template</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <label class="p-2.5 rounded-xl border cursor-pointer flex items-center gap-2"
                               :class="settings.medal_type === 'numbers' ? 'border-[#be1e2d] bg-red-50/50 font-bold text-red-900' : 'border-slate-200 text-slate-700'">
                            <input type="radio" name="medal_type" value="numbers" x-model="settings.medal_type" @change="render()" class="sr-only">
                            <span class="w-5 h-5 rounded bg-[#be1e2d] text-white font-mono text-[10px] flex items-center justify-center font-bold">01</span>
                            <span>Modern Numbers (01, 02)</span>
                        </label>

                        <label class="p-2.5 rounded-xl border cursor-pointer flex items-center gap-2"
                               :class="settings.medal_type === 'badges' ? 'border-[#be1e2d] bg-red-50/50 font-bold text-red-900' : 'border-slate-200 text-slate-700'">
                            <input type="radio" name="medal_type" value="badges" x-model="settings.medal_type" @change="render()" class="sr-only">
                            <span class="w-5 h-5 rounded-full bg-[#be1e2d] text-white text-[9px] flex items-center justify-center font-bold">01</span>
                            <span>Circle Badges</span>
                        </label>

                        <label class="p-2.5 rounded-xl border cursor-pointer flex items-center gap-2"
                               :class="settings.medal_type === 'medals' ? 'border-[#be1e2d] bg-red-50/50 font-bold text-red-900' : 'border-slate-200 text-slate-700'">
                            <input type="radio" name="medal_type" value="medals" x-model="settings.medal_type" @change="render()" class="sr-only">
                            <span class="text-amber-500 font-bold text-sm">🏅</span>
                            <span>Gold/Silver/Bronze Icons</span>
                        </label>

                        <label class="p-2.5 rounded-xl border cursor-pointer flex items-center gap-2"
                               :class="settings.medal_type === 'none' ? 'border-[#be1e2d] bg-red-50/50 font-bold text-red-900' : 'border-slate-200 text-slate-700'">
                            <input type="radio" name="medal_type" value="none" x-model="settings.medal_type" @change="render()" class="sr-only">
                            <span class="text-slate-400 text-xs">&times;</span>
                            <span>No Medals (Hide)</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Medal / Rank Size (<span x-text="settings.medal_size"></span>px)</label>
                            <input type="range" min="12" max="80" step="1" x-model.number="settings.medal_size" @input="render()" class="w-full accent-[#be1e2d]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Medal-to-Text Gap (<span x-text="settings.item_gap"></span>px)</label>
                            <input type="range" min="0" max="60" step="1" x-model.number="settings.item_gap" @input="render()" class="w-full accent-[#be1e2d]">
                        </div>
                    </div>
                </div>

                <!-- Line Distance & Spacing Controls -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">Line Distances & Spacing (വരി അകലം)</h3>
                        <span class="text-[10px] text-slate-400">Leading & Gaps</span>
                    </div>

                    <div class="space-y-3">
                        <!-- Student Name 2-line line-height -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-bold text-slate-700">Student Name 2-Line Leading (വിദ്യാർത്ഥി പേര് 2 വരി അകലം)</label>
                                <span class="font-mono text-xs font-bold text-slate-800" x-text="settings.winner_name_line_height + ' px'"></span>
                            </div>
                            <input type="range" min="16" max="70" step="1" x-model.number="settings.winner_name_line_height" @input="render()" class="w-full accent-[#be1e2d]">
                        </div>

                        <!-- Distance to Group Name -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-bold text-slate-700">Distance to Group Name (ഗ്രൂപ്പ് അകലം)</label>
                                <span class="font-mono text-xs font-bold text-slate-800" x-text="settings.winner_unit_gap + ' px'"></span>
                            </div>
                            <input type="range" min="2" max="40" step="1" x-model.number="settings.winner_unit_gap" @input="render()" class="w-full accent-[#be1e2d]">
                        </div>

                        <!-- Row Gap (Between Winners) -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-bold text-slate-700">Row Gap Between Winners (വിജയികൾ തമ്മിലുള്ള അകലം)</label>
                                <span class="font-mono text-xs font-bold text-slate-800" x-text="settings.row_gap + ' px'"></span>
                            </div>
                            <input type="range" min="50" max="300" step="1" x-model.number="settings.row_gap" @input="render()" class="w-full accent-[#be1e2d]">
                        </div>

                        <!-- Block Position (X, Y) -->
                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Block Left (X: <span x-text="settings.block_left"></span>px)</label>
                                <input type="range" min="50" max="800" step="1" x-model.number="settings.block_left" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">First Winner Top (Y: <span x-text="settings.first_top"></span>px)</label>
                                <input type="range" min="50" max="800" step="1" x-model.number="settings.first_top" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Winners List Data Inputs (with 2-Line Option) -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <div>
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">Podium Winners Editor</h3>
                            <p class="text-[10px] text-slate-400">പേരുകൾ 2 വരിയാക്കാൻ Enter കീ അമർത്തുക അല്ലെങ്കിൽ '/' നൽകുക.</p>
                        </div>
                        <label class="flex items-center gap-1.5 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" x-model="settings.winner_name_2lines" @change="render()" class="rounded text-[#be1e2d]">
                            <span class="text-[11px] font-bold">Auto 2-Line</span>
                        </label>
                    </div>

                    <!-- First Place (01) -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-md bg-[#be1e2d] text-white font-mono font-bold text-xs flex items-center justify-center">01</span>
                                <span class="font-bold text-xs text-slate-800">1st Place / ഒന്നാം സ്ഥാനം</span>
                            </div>
                            <button type="button" @click="addWinner('first')" class="px-2 py-0.5 text-[10px] font-bold text-[#be1e2d] hover:bg-red-50 rounded border border-red-200 transition">
                                + Add Tie
                            </button>
                        </div>
                        <template x-for="(w, idx) in data.winners.first" :key="'first-' + idx">
                            <div class="grid grid-cols-12 gap-2 items-start bg-white p-2 rounded-lg border border-slate-200">
                                <div class="col-span-6">
                                    <textarea rows="2" x-model="w.name" @input="render()" placeholder="Student Name (Line 1 &#10; Line 2)" class="w-full px-2 py-1 text-xs rounded border border-slate-300 font-bold resize-none leading-snug"></textarea>
                                </div>
                                <div class="col-span-5">
                                    <input type="text" x-model="w.unit" @input="render()" placeholder="Group / Unit" class="w-full px-2 py-1.5 text-xs rounded border border-slate-300">
                                </div>
                                <div class="col-span-1 flex items-center justify-center pt-1.5">
                                    <button type="button" x-show="data.winners.first.length > 1" @click="removeWinner('first', idx)" class="text-red-500 hover:text-red-700 font-bold text-sm">&times;</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Second Place (02) -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-md bg-slate-800 text-white font-mono font-bold text-xs flex items-center justify-center">02</span>
                                <span class="font-bold text-xs text-slate-800">2nd Place / രണ്ടാം സ്ഥാനം</span>
                            </div>
                            <button type="button" @click="addWinner('second')" class="px-2 py-0.5 text-[10px] font-bold text-[#be1e2d] hover:bg-red-50 rounded border border-red-200 transition">
                                + Add Tie
                            </button>
                        </div>
                        <template x-for="(w, idx) in data.winners.second" :key="'second-' + idx">
                            <div class="grid grid-cols-12 gap-2 items-start bg-white p-2 rounded-lg border border-slate-200">
                                <div class="col-span-6">
                                    <textarea rows="2" x-model="w.name" @input="render()" placeholder="Student Name (Line 1 &#10; Line 2)" class="w-full px-2 py-1 text-xs rounded border border-slate-300 font-bold resize-none leading-snug"></textarea>
                                </div>
                                <div class="col-span-5">
                                    <input type="text" x-model="w.unit" @input="render()" placeholder="Group / Unit" class="w-full px-2 py-1.5 text-xs rounded border border-slate-300">
                                </div>
                                <div class="col-span-1 flex items-center justify-center pt-1.5">
                                    <button type="button" x-show="data.winners.second.length > 1" @click="removeWinner('second', idx)" class="text-red-500 hover:text-red-700 font-bold text-sm">&times;</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Third Place (03) -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-md bg-amber-700 text-white font-mono font-bold text-xs flex items-center justify-center">03</span>
                                <span class="font-bold text-xs text-slate-800">3rd Place / മൂന്നാം സ്ഥാനം</span>
                            </div>
                            <button type="button" @click="addWinner('third')" class="px-2 py-0.5 text-[10px] font-bold text-[#be1e2d] hover:bg-red-50 rounded border border-red-200 transition">
                                + Add Tie
                            </button>
                        </div>
                        <template x-for="(w, idx) in data.winners.third" :key="'third-' + idx">
                            <div class="grid grid-cols-12 gap-2 items-start bg-white p-2 rounded-lg border border-slate-200">
                                <div class="col-span-6">
                                    <textarea rows="2" x-model="w.name" @input="render()" placeholder="Student Name (Line 1 &#10; Line 2)" class="w-full px-2 py-1 text-xs rounded border border-slate-300 font-bold resize-none leading-snug"></textarea>
                                </div>
                                <div class="col-span-5">
                                    <input type="text" x-model="w.unit" @input="render()" placeholder="Group / Unit" class="w-full px-2 py-1.5 text-xs rounded border border-slate-300">
                                </div>
                                <div class="col-span-1 flex items-center justify-center pt-1.5">
                                    <button type="button" x-show="data.winners.third.length > 1" @click="removeWinner('third', idx)" class="text-red-500 hover:text-red-700 font-bold text-sm">&times;</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 3: ELEMENTS & ROTATION (PROGRAM, ZONE, RESULT NUMBER) -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'elements'" class="space-y-4">
                
                <!-- Result Number Inspector -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">1. Result Sequence Number (01, 37)</h3>
                        <input type="color" x-model="settings.result_color" @input="render()" class="w-6 h-6 rounded cursor-pointer border border-slate-300 p-0.5">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sequence Number</label>
                            <input type="text" x-model="data.result_no" @input="render()" placeholder="37" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 font-mono font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Size (<span x-text="settings.result_size"></span>px)</label>
                            <input type="number" min="20" max="600" x-model.number="settings.result_size" @input="render()" class="w-full px-2 py-1.5 text-xs rounded border border-slate-300">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">X Position (<span x-text="settings.result_x"></span>px)</label>
                            <input type="range" min="0" max="1080" step="1" x-model.number="settings.result_x" @input="render()" class="w-full accent-[#be1e2d]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Y Position (<span x-text="settings.result_y"></span>px)</label>
                            <input type="range" min="0" max="1350" step="1" x-model.number="settings.result_y" @input="render()" class="w-full accent-[#be1e2d]">
                        </div>
                    </div>

                    <!-- Rotation Slider -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[11px] font-bold text-slate-700">Rotation Angle (റൊട്ടേഷൻ)</label>
                            <span class="font-mono text-xs font-bold text-slate-800" x-text="settings.result_rotation + '&deg;'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="range" min="-180" max="180" step="1" x-model.number="settings.result_rotation" @input="render()" class="w-full accent-[#be1e2d]">
                            <button type="button" @click="settings.result_rotation = 0; render();" class="text-[10px] text-slate-400 hover:text-slate-800 px-1 py-0.5 rounded border border-slate-200">0&deg;</button>
                        </div>
                    </div>
                </div>

                <!-- Program Name Inspector -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">2. Program Name (മത്സര പേര്)</h3>
                        <input type="color" x-model="settings.competition_color" @input="render()" class="w-6 h-6 rounded cursor-pointer border border-slate-300 p-0.5">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Competition / Program Name</label>
                        <textarea rows="2" x-model="data.competition" @input="render()" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 font-bold resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Font Size (<span x-text="settings.competition_size"></span>px)</label>
                            <input type="number" min="16" max="160" x-model.number="settings.competition_size" @input="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Line Height (<span x-text="settings.competition_line_height"></span>px)</label>
                            <input type="number" min="16" max="180" x-model.number="settings.competition_line_height" @input="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">X Position (<span x-text="settings.competition_x"></span>px)</label>
                            <input type="range" min="0" max="1080" step="1" x-model.number="settings.competition_x" @input="render()" class="w-full accent-[#be1e2d]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Y Position (<span x-text="settings.competition_y"></span>px)</label>
                            <input type="range" min="0" max="1350" step="1" x-model.number="settings.competition_y" @input="render()" class="w-full accent-[#be1e2d]">
                        </div>
                    </div>

                    <!-- Program Name Rotation -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[11px] font-bold text-slate-700">Program Rotation Angle</label>
                            <span class="font-mono text-xs font-bold text-slate-800" x-text="settings.competition_rotation + '&deg;'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="range" min="-180" max="180" step="1" x-model.number="settings.competition_rotation" @input="render()" class="w-full accent-[#be1e2d]">
                            <button type="button" @click="settings.competition_rotation = 0; render();" class="text-[10px] text-slate-400 hover:text-slate-800 px-1 py-0.5 rounded border border-slate-200">0&deg;</button>
                        </div>
                    </div>
                </div>

                <!-- Zone Capsule Inspector -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">3. Zone Capsule (സോൺ ക്യാപ്സ്യൂൾ)</h3>
                        <input type="color" x-model="settings.category_color" @input="render()" class="w-6 h-6 rounded cursor-pointer border border-slate-300 p-0.5">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Zone / Category Text</label>
                            <input type="text" x-model="data.category" @input="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Size (<span x-text="settings.category_size"></span>px)</label>
                            <input type="number" min="12" max="80" x-model.number="settings.category_size" @input="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">X Position (<span x-text="settings.category_x"></span>px)</label>
                            <input type="range" min="0" max="1080" step="1" x-model.number="settings.category_x" @input="render()" class="w-full accent-[#be1e2d]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Y Position (<span x-text="settings.category_y"></span>px)</label>
                            <input type="range" min="0" max="1350" step="1" x-model.number="settings.category_y" @input="render()" class="w-full accent-[#be1e2d]">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[11px] font-bold text-slate-700">Zone Rotation Angle</label>
                            <span class="font-mono text-xs font-bold text-slate-800" x-text="settings.category_rotation + '&deg;'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="range" min="-180" max="180" step="1" x-model.number="settings.category_rotation" @input="render()" class="w-full accent-[#be1e2d]">
                            <button type="button" @click="settings.category_rotation = 0; render();" class="text-[10px] text-slate-400 hover:text-slate-800 px-1 py-0.5 rounded border border-slate-200">0&deg;</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 4: EXTRA ELEMENTS & OVERLAYS (STAMPS, RIBBONS, WATERMARKS) -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'overlay'" class="space-y-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">Top Extra Overlay Element</h3>
                            <p class="text-[11px] text-slate-500">Add stylish text art (e.g. 'Result'), stamps, or custom PNG badges above all content.</p>
                        </div>
                        <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                            <input type="checkbox" x-model="overlay.enabled" @change="render()" class="rounded text-[#be1e2d]">
                            <span>Enable</span>
                        </label>
                    </div>

                    <!-- Stacking Order: Above all content or behind -->
                    <div class="p-3 bg-purple-50/60 rounded-xl border border-purple-200/80 space-y-2">
                        <label class="text-[11px] font-bold text-purple-900 block">Layer Stacking Order (ഓർഡർ)</label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <label class="p-2 rounded-lg border cursor-pointer flex items-center gap-2"
                                   :class="overlay.layerOrder === 'above' ? 'bg-purple-600 text-white font-bold border-purple-600' : 'bg-white text-slate-700 border-slate-300'">
                                <input type="radio" value="above" x-model="overlay.layerOrder" @change="render()" class="sr-only">
                                <span>&uarr; Above All Content (Top)</span>
                            </label>
                            <label class="p-2 rounded-lg border cursor-pointer flex items-center gap-2"
                                   :class="overlay.layerOrder === 'below' ? 'bg-purple-600 text-white font-bold border-purple-600' : 'bg-white text-slate-700 border-slate-300'">
                                <input type="radio" value="below" x-model="overlay.layerOrder" @change="render()" class="sr-only">
                                <span>&darr; Behind Text (Middle)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Overlay Type: Preset Typography vs Custom Upload -->
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2 text-xs font-bold">
                            <button type="button" @click="overlay.type = 'text'; render();"
                                    class="py-2 rounded-xl border text-center transition"
                                    :class="overlay.type === 'text' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-700 border-slate-200'">
                                Typography Art ('Result')
                            </button>
                            <button type="button" @click="overlay.type = 'image'; render();"
                                    class="py-2 rounded-xl border text-center transition"
                                    :class="overlay.type === 'image' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-700 border-slate-200'">
                                Custom Image / Badge
                            </button>
                        </div>

                        <!-- If Text Art -->
                        <div x-show="overlay.type === 'text'" class="space-y-3 pt-2">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Text Content</label>
                                    <input type="text" x-model="overlay.text" @input="render()" placeholder="Result" class="w-full px-2 py-1.5 text-xs rounded border border-slate-300 font-bold">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Font Family</label>
                                    <select x-model="overlay.font" @change="render()" class="w-full px-2 py-1.5 text-xs rounded border border-slate-300">
                                        <option value="Rockwell Std">Rockwell Std</option>
                                        <option value="Mongolian Baiti">Mongolian Baiti</option>
                                        <option value="Anek Malayalam">Anek Malayalam</option>
                                        <option value="Sora">Sora</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Font Size (<span x-text="overlay.size"></span>px)</label>
                                    <input type="range" min="20" max="350" step="1" x-model.number="overlay.size" @input="render()" class="w-full accent-[#be1e2d]">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Text Color</label>
                                    <input type="color" x-model="overlay.color" @input="render()" class="w-full h-8 rounded border border-slate-300 cursor-pointer p-0.5">
                                </div>
                            </div>
                        </div>

                        <!-- If Custom Image Upload -->
                        <div x-show="overlay.type === 'image'" class="space-y-3 pt-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Upload PNG / SVG Overlay</label>
                                <input type="file" accept="image/*" @change="handleOverlayUpload($event)" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800">
                            </div>
                        </div>

                        <!-- Position (X, Y) -->
                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">X Position (<span x-text="overlay.x"></span>px)</label>
                                <input type="range" min="0" max="1080" step="1" x-model.number="overlay.x" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Y Position (<span x-text="overlay.y"></span>px)</label>
                                <input type="range" min="0" max="1350" step="1" x-model.number="overlay.y" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                        </div>

                        <!-- Rotation & Opacity -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="text-[11px] font-bold text-slate-700">Rotation Angle</label>
                                    <span class="font-mono text-xs font-bold text-slate-800" x-text="overlay.rotation + '&deg;'"></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <input type="range" min="-180" max="180" step="1" x-model.number="overlay.rotation" @input="render()" class="w-full accent-[#be1e2d]">
                                    <button type="button" @click="overlay.rotation = 0; render();" class="text-[9px] px-1 py-0.5 rounded border border-slate-200">0&deg;</button>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="text-[11px] font-bold text-slate-700">Opacity (<span x-text="overlay.opacity"></span>%)</label>
                                    <span class="font-mono text-xs font-bold text-slate-800" x-text="overlay.opacity + '%'"></span>
                                </div>
                                <input type="range" min="5" max="100" step="1" x-model.number="overlay.opacity" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 5: ILLUSTRATOR GUIDELINES -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'guides'" class="space-y-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">Illustrator Guidelines (ഗൈഡുകൾ)</h3>
                            <p class="text-[11px] text-slate-500">Cyan magnetic lines for alignment. Never exported into the downloaded image.</p>
                        </div>
                        <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                            <input type="checkbox" x-model="guidelines.show" @change="render()" class="rounded text-cyan-600">
                            <span>Show</span>
                        </label>
                    </div>

                    <!-- Add Vertical Guide (X) -->
                    <div class="p-3 bg-cyan-50/60 rounded-xl border border-cyan-200 space-y-2">
                        <label class="text-[11px] font-bold text-cyan-950 block">Vertical Guide Lines (ലംബ വരകൾ - X)</label>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="(gx, idx) in guidelines.vertical" :key="'vg-' + idx">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-cyan-300 font-mono text-xs font-bold text-cyan-900 shadow-2xs">
                                    <span x-text="'X: ' + gx + 'px'"></span>
                                    <button type="button" @click="guidelines.vertical.splice(idx, 1); render();" class="text-red-500 hover:text-red-700 font-bold text-xs">&times;</button>
                                </span>
                            </template>
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <input type="number" min="0" max="1080" x-model.number="guidelines.newX" class="px-2 py-1 text-xs rounded border border-cyan-300 w-24 font-mono">
                            <button type="button" @click="addVGuide()" class="px-3 py-1 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs">
                                + Add Guide
                            </button>
                        </div>
                    </div>

                    <!-- Add Horizontal Guide (Y) -->
                    <div class="p-3 bg-cyan-50/60 rounded-xl border border-cyan-200 space-y-2">
                        <label class="text-[11px] font-bold text-cyan-950 block">Horizontal Guide Lines (തിരശ്ചീന വരകൾ - Y)</label>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="(gy, idx) in guidelines.horizontal" :key="'hg-' + idx">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-cyan-300 font-mono text-xs font-bold text-cyan-900 shadow-2xs">
                                    <span x-text="'Y: ' + gy + 'px'"></span>
                                    <button type="button" @click="guidelines.horizontal.splice(idx, 1); render();" class="text-red-500 hover:text-red-700 font-bold text-xs">&times;</button>
                                </span>
                            </template>
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <input type="number" min="0" max="1440" x-model.number="guidelines.newY" class="px-2 py-1 text-xs rounded border border-cyan-300 w-24 font-mono">
                            <button type="button" @click="addHGuide()" class="px-3 py-1 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs">
                                + Add Guide
                            </button>
                        </div>
                    </div>

                    <!-- Presets -->
                    <div class="flex items-center justify-between pt-1">
                        <button type="button" @click="resetStandardGuides()" class="text-xs text-cyan-700 hover:underline font-bold">
                            Reset to Default Illustrator Guides
                        </button>
                        <button type="button" @click="guidelines.vertical = []; guidelines.horizontal = []; render();" class="text-xs text-red-600 hover:underline">
                            Clear All
                        </button>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 6: TEMPLATES -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'templates'" class="space-y-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Poster Frame Template</h2>
                        <a href="{{ route('media.results.templates') }}" target="_blank" class="text-[11px] text-[#be1e2d] hover:underline font-bold">+ Upload New</a>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($templates as $tpl)
                            <label class="cursor-pointer border-2 rounded-xl p-2 flex flex-col items-center text-center transition-all"
                                   :class="settings.template_id == {{ $tpl->id }} ? 'border-[#be1e2d] bg-red-50/30 ring-1 ring-[#be1e2d]' : 'border-slate-200 hover:border-slate-300'">
                                <input type="radio" name="template_id" value="{{ $tpl->id }}" class="sr-only"
                                       @change="selectTemplate({{ $tpl->id }}, '{{ asset($tpl->image_path) }}', '{{ $tpl->posterSetting->layout_mode ?? (str_contains(strtolower($tpl->name), 'yellow') ? 'yellow_modern' : 'default') }}')"
                                       :checked="settings.template_id == {{ $tpl->id }}">
                                <div class="w-full aspect-square bg-slate-900 rounded-lg overflow-hidden mb-2">
                                    <img src="{{ asset($tpl->image_path) }}" alt="{{ $tpl->name }}" class="w-full h-full object-cover">
                                </div>
                                <span class="text-[11px] font-bold text-slate-800 line-clamp-1">{{ $tpl->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        <!-- Preview Column (7 cols) -->
        <div class="lg:col-span-7 space-y-4 sticky top-6">
            <div class="bg-white p-4 sm:p-6 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                
                <!-- Preview Header & Tools -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900">Live Poster Preview</h2>
                        <p class="text-[11px] text-slate-500 font-mono">1080 &times; <span x-text="settings.layout_mode === 'yellow_modern' ? '1215' : '1080'"></span> px High Definition</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 text-xs text-cyan-700 cursor-pointer font-bold">
                            <input type="checkbox" x-model="guidelines.show" @change="render()" class="rounded text-cyan-600 focus:ring-cyan-500">
                            <span class="text-[11px]">Illustrator Guides</span>
                        </label>
                    </div>
                </div>

                <!-- Live Canvas Display Area -->
                <div class="bg-slate-950 rounded-2xl overflow-hidden p-2 sm:p-4 flex items-center justify-center relative min-h-[420px]">
                    <!-- The Real HTML5 Canvas (HD, scaled responsively via CSS) -->
                    <canvas id="posterCanvas" width="1080" :height="settings.layout_mode === 'yellow_modern' ? 1215 : 1080"
                            class="w-full max-w-[540px] object-contain shadow-2xl rounded-xl border border-slate-800 bg-black"></canvas>
                </div>

                <!-- Footer Quick Share & Download -->
                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-[11px] text-slate-500">
                        <span class="font-bold text-slate-700">Real-Time Studio:</span> All changes, rotations, and scalings apply instantly.
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="downloadPoster()"
                                class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs flex items-center gap-1.5 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span>Download PNG</span>
                        </button>

                        <button type="button" @click="saveAndPublish()" :disabled="publishing"
                                class="px-4 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs flex items-center gap-1.5 transition-colors shadow-xs disabled:opacity-50">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Save & Publish</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@php
    $templatesMap = [];
    foreach ($templates as $t) {
        $templatesMap[$t->id] = $t->posterSetting ? $t->posterSetting->toArray() : \App\Models\PosterSetting::defaultSettings();
    }
@endphp

<script>
function posterStudio() {
    return {
        activeTab: 'layers',
        activeLayer: 'winners',
        savingDefault: false,
        publishing: false,
        statusMsg: '',
        statusType: 'success',

        templatesMap: @json($templatesMap),
        templateImageObj: null,
        overlayImageObj: null,

        // Guidelines (Cyan lines)
        guidelines: {
            show: true,
            vertical: [205, 540, 740],
            horizontal: [145, 165, 779],
            newX: 205,
            newY: 200
        },

        // Overlay Extra Element
        overlay: {
            enabled: true,
            type: 'text',
            preset: 'result_text',
            text: 'Result',
            font: 'Rockwell Std',
            size: 140,
            color: '#000000',
            weight: '400',
            x: 480,
            y: 830,
            rotation: 0,
            opacity: 100,
            layerOrder: 'below' // 'above' or 'below'
        },

        // Settings initialized from Controller
        settings: {
            template_id: {{ $activeTemplate ? $activeTemplate->id : 1 }},
            template_image: '{{ $activeTemplate ? asset($activeTemplate->image_path) : asset("/images/result-templates/1778705272_srt1-01.png") }}',
            layout_mode: '{{ $currentSettings["layout_mode"] ?? "default" }}',

            // Visibility Toggles
            show_result_no: true,
            show_competition: true,
            show_category: true,
            show_winners: true,

            // Rotations
            result_rotation: 0,
            competition_rotation: 0,
            category_rotation: 0,

            // Master Winner Scaling & Spacing
            winners_scale: 1.0,
            winner_name_2lines: true,
            winner_name_line_height: 38,
            winner_unit_gap: 10,
            row_gap: {{ (float) ($currentSettings['row_gap'] ?? 178) }},

            // Medal System
            medal_type: 'numbers', // 'numbers', 'badges', 'medals', 'none'
            medal_size: 26,
            item_gap: 0,

            result_x: {{ (float) ($currentSettings['result_x'] ?? 740) }},
            result_y: {{ (float) ($currentSettings['result_y'] ?? 779) }},
            result_size: {{ (float) ($currentSettings['result_size'] ?? 480) }},
            result_weight: '{{ $currentSettings["result_weight"] ?? "400" }}',
            result_color: '{{ (strlen($currentSettings["result_color"] ?? "") === 7) ? $currentSettings["result_color"] : "#D02627" }}',

            category_x: {{ (float) ($currentSettings['category_x'] ?? 686) }},
            category_y: {{ (float) ($currentSettings['category_y'] ?? 165) }},
            category_size: {{ (float) ($currentSettings['category_size'] ?? 45) }},
            category_weight: '{{ $currentSettings["category_weight"] ?? "400" }}',
            category_color: '{{ (strlen($currentSettings["category_color"] ?? "") === 7) ? $currentSettings["category_color"] : "#FFFFFF" }}',
            category_align: '{{ $currentSettings["category_align"] ?? "center" }}',

            competition_x: {{ (float) ($currentSettings['competition_x'] ?? 584) }},
            competition_y: {{ (float) ($currentSettings['competition_y'] ?? 220) }},
            competition_size: {{ (float) ($currentSettings['competition_size'] ?? 100) }},
            competition_weight: '{{ $currentSettings["competition_weight"] ?? "400" }}',
            competition_color: '{{ (strlen($currentSettings["competition_color"] ?? "") === 7) ? $currentSettings["competition_color"] : "#000000" }}',
            competition_align: '{{ $currentSettings["competition_align"] ?? "left" }}',
            competition_max_width: {{ (float) ($currentSettings['competition_max_width'] ?? 440) }},
            competition_line_height: {{ (float) ($currentSettings['competition_line_height'] ?? 94) }},
            competition_font: '{{ $currentSettings["competition_font"] ?? "Rockwell Std" }}',

            block_left: {{ (float) ($currentSettings['block_left'] ?? 205) }},
            first_top: {{ (float) ($currentSettings['first_top'] ?? 145) }},

            winner_name_size: {{ (float) ($currentSettings['winner_name_size'] ?? 34) }},
            winner_name_weight: '{{ $currentSettings["winner_name_weight"] ?? "400" }}',
            winner_name_color: '{{ (strlen($currentSettings["winner_name_color"] ?? "") === 7) ? $currentSettings["winner_name_color"] : "#D02627" }}',

            winner_unit_size: {{ (float) ($currentSettings['winner_unit_size'] ?? 26) }},
            winner_unit_weight: '{{ $currentSettings["winner_unit_weight"] ?? "300" }}',
            winner_unit_color: '{{ (strlen($currentSettings["winner_unit_color"] ?? "") === 7) ? $currentSettings["winner_unit_color"] : "#000000" }}',
            winner_font: '{{ $currentSettings["winner_font"] ?? "Rockwell Std" }}',
        },

        // Content Data
        data: {
            result_no: '{{ !empty($resultNo) ? sprintf("%02d", $resultNo) : ($result->id ?? "01") }}',
            category: '{{ $result->program->zone?->name ?? $result->program->eligibility ?? "All Zones" }}',
            competition: '{{ $result->program->name }}',
            winners: {
                first: @json(!empty($winners['first']) ? array_values($winners['first']) : [['name' => '', 'unit' => '']]),
                second: @json(!empty($winners['second']) ? array_values($winners['second']) : [['name' => '', 'unit' => '']]),
                third: @json(!empty($winners['third']) ? array_values($winners['third']) : [['name' => '', 'unit' => '']])
            }
        },

        get activeLayerLabel() {
            const labels = {
                'winners': 'Winners & Groups',
                'result_no': 'Result Number',
                'competition': 'Program Name',
                'category': 'Zone Capsule',
                'overlay': 'Extra Overlay'
            };
            return labels[this.activeLayer] || this.activeLayer;
        },

        addWinner(rank) {
            if (!this.data.winners[rank]) {
                this.data.winners[rank] = [];
            }
            this.data.winners[rank].push({ name: '', unit: '' });
            this.$nextTick(() => { this.render(); });
        },

        removeWinner(rank, index) {
            if (this.data.winners[rank] && this.data.winners[rank].length > 1) {
                this.data.winners[rank].splice(index, 1);
                this.$nextTick(() => { this.render(); });
            }
        },

        addVGuide() {
            if (this.guidelines.newX !== null && !isNaN(this.guidelines.newX)) {
                this.guidelines.vertical.push(parseInt(this.guidelines.newX, 10));
                this.guidelines.vertical.sort((a, b) => a - b);
                this.render();
            }
        },

        addHGuide() {
            if (this.guidelines.newY !== null && !isNaN(this.guidelines.newY)) {
                this.guidelines.horizontal.push(parseInt(this.guidelines.newY, 10));
                this.guidelines.horizontal.sort((a, b) => a - b);
                this.render();
            }
        },

        resetStandardGuides() {
            this.guidelines.vertical = [205, 540, 740];
            this.guidelines.horizontal = [145, 165, 779];
            this.guidelines.show = true;
            this.render();
        },

        nudgeLayer(dx, dy) {
            if (this.activeLayer === 'winners') {
                this.settings.block_left += dx;
                this.settings.first_top += dy;
            } else if (this.activeLayer === 'result_no') {
                this.settings.result_x += dx;
                this.settings.result_y += dy;
            } else if (this.activeLayer === 'competition') {
                this.settings.competition_x += dx;
                this.settings.competition_y += dy;
            } else if (this.activeLayer === 'category') {
                this.settings.category_x += dx;
                this.settings.category_y += dy;
            } else if (this.activeLayer === 'overlay') {
                this.overlay.x += dx;
                this.overlay.y += dy;
            }
            this.render();
        },

        alignLayer(direction) {
            const canvasW = (this.settings.layout_mode === 'yellow_modern') ? 1080 : 1080;
            if (direction === 'center') {
                if (this.activeLayer === 'competition') this.settings.competition_x = Math.round(canvasW / 2);
                if (this.activeLayer === 'category') this.settings.category_x = Math.round(canvasW / 2);
                if (this.activeLayer === 'result_no') this.settings.result_x = Math.round(canvasW / 2);
                if (this.activeLayer === 'overlay') this.overlay.x = Math.round(canvasW / 2);
            } else if (direction === 'left') {
                if (this.activeLayer === 'winners') this.settings.block_left = 205;
                if (this.activeLayer === 'competition') this.settings.competition_x = 205;
                if (this.activeLayer === 'overlay') this.overlay.x = 205;
            } else if (direction === 'right') {
                if (this.activeLayer === 'competition') this.settings.competition_x = 875;
                if (this.activeLayer === 'overlay') this.overlay.x = 875;
            }
            this.render();
        },

        handleOverlayUpload(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                const img = new Image();
                img.onload = () => {
                    this.overlayImageObj = img;
                    this.overlay.type = 'image';
                    this.overlay.enabled = true;
                    this.render();
                };
                img.src = loadEvent.target.result;
            };
            reader.readAsDataURL(file);
        },

        init() {
            this.loadTemplateImage(this.settings.template_image);
            if (document.fonts) {
                Promise.all([
                    document.fonts.load('480px "Mongolian Baiti"'),
                    document.fonts.load('400 40px "Rockwell Std"'),
                    document.fonts.load('300 30px "Rockwell Std"'),
                    document.fonts.load('700 40px "Rockwell Std"')
                ]).then(() => {
                    this.render();
                }).catch(() => {
                    this.render();
                });
                document.fonts.ready.then(() => { this.render(); });
            }
        },

        loadTemplateImage(src) {
            this.templateImageObj = new Image();
            this.templateImageObj.crossOrigin = 'anonymous';
            this.templateImageObj.src = src;
            this.templateImageObj.onload = () => {
                this.render();
            };
        },

        selectTemplate(id, src, layoutMode) {
            this.settings.template_id = id;
            this.settings.template_image = src;
            this.settings.layout_mode = layoutMode || (src.includes('yellow') ? 'yellow_modern' : 'default');

            if (this.settings.layout_mode === 'yellow_modern') {
                this.settings.result_x = 740;
                this.settings.result_y = 779;
                this.settings.result_size = 480;
                this.settings.result_weight = '400';
                this.settings.result_color = '#D02627';

                this.settings.category_x = 686;
                this.settings.category_y = 165;
                this.settings.category_size = 45;
                this.settings.category_weight = '400';
                this.settings.category_color = '#FFFFFF';
                this.settings.category_align = 'center';

                this.settings.competition_x = 584;
                this.settings.competition_y = 220;
                this.settings.competition_size = 100;
                this.settings.competition_weight = '400';
                this.settings.competition_color = '#000000';
                this.settings.competition_align = 'left';
                this.settings.competition_font = 'Rockwell Std';
                this.settings.competition_max_width = 440;
                this.settings.competition_line_height = 94;

                this.settings.block_left = 205;
                this.settings.first_top = 145;
                this.settings.row_gap = 178;
                this.settings.item_gap = 0;
                this.settings.medal_size = 26;

                this.settings.winner_name_size = 34;
                this.settings.winner_name_weight = '400';
                this.settings.winner_name_color = '#D02627';
                this.settings.winner_unit_size = 26;
                this.settings.winner_unit_weight = '300';
                this.settings.winner_unit_color = '#000000';
                this.settings.winner_font = 'Rockwell Std';
            } else if (this.templatesMap && this.templatesMap[id]) {
                const tplSettings = this.templatesMap[id];
                const numericKeys = [
                    'result_x', 'result_y', 'result_size',
                    'category_x', 'category_y', 'category_size',
                    'competition_x', 'competition_y', 'competition_size', 'competition_max_width', 'competition_line_height',
                    'block_left', 'first_top', 'row_gap', 'item_gap', 'medal_size',
                    'winner_name_size', 'winner_unit_size'
                ];
                numericKeys.forEach(k => {
                    if (tplSettings[k] !== undefined && tplSettings[k] !== null) {
                        this.settings[k] = parseFloat(tplSettings[k]);
                    }
                });
            }

            this.loadTemplateImage(src);
        },

        render(isExport = false) {
            const canvas = document.getElementById('posterCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            const isYellow = this.settings.layout_mode === 'yellow_modern' || (this.settings.template_image && this.settings.template_image.includes('yellow'));

            if (isYellow) {
                canvas.width = 1080;
                canvas.height = 1215;
            } else {
                canvas.width = 1080;
                canvas.height = 1080;
            }

            // Clear
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // 1. Draw Template Background
            if (this.templateImageObj && this.templateImageObj.complete && this.templateImageObj.naturalWidth > 0) {
                ctx.drawImage(this.templateImageObj, 0, 0, canvas.width, canvas.height);
            } else {
                ctx.fillStyle = isYellow ? '#F2BC2E' : '#0f172a';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
            }

            // 2. Draw Overlay Element if layer order is 'below' (Behind Text)
            if (this.overlay.enabled && this.overlay.layerOrder === 'below') {
                this.drawOverlayElement(ctx);
            }

            if (isYellow) {
                // ============================================
                // DEDICATED YELLOW MODERN POSTER RENDER ENGINE
                // ============================================

                // Zone inside Red Capsule (Top Right)
                if (this.settings.show_category) {
                    ctx.save();
                    ctx.translate(this.settings.category_x || 686, this.settings.category_y || 165);
                    if (this.settings.category_rotation) {
                        ctx.rotate((this.settings.category_rotation * Math.PI) / 180);
                    }
                    ctx.fillStyle = this.settings.category_color || '#FFFFFF';
                    ctx.font = `${this.settings.category_weight || '400'} ${this.settings.category_size || 45}px "Rockwell Std", serif`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    if ('letterSpacing' in ctx) ctx.letterSpacing = '-1.8px';
                    ctx.fillText(this.data.category || '', 0, 0);
                    ctx.restore();
                }

                // Program Name (Below Capsule, Left Aligned)
                if (this.settings.show_competition) {
                    ctx.save();
                    ctx.translate(this.settings.competition_x || 584, this.settings.competition_y || 220);
                    if (this.settings.competition_rotation) {
                        ctx.rotate((this.settings.competition_rotation * Math.PI) / 180);
                    }
                    ctx.fillStyle = this.settings.competition_color || '#000000';
                    const compFont = this.settings.competition_font || 'Rockwell Std';
                    const compSize = this.settings.competition_size || 100;
                    ctx.font = `${this.settings.competition_weight || '400'} ${compSize}px "${compFont}", serif`;
                    ctx.textAlign = this.settings.competition_align || 'left';
                    ctx.textBaseline = 'top';
                    if ('letterSpacing' in ctx) ctx.letterSpacing = '-4.0px';

                    const compLines = this.wrapText(ctx, this.data.competition || '', this.settings.competition_max_width || 440);
                    let compY = 0;
                    const compLineH = this.settings.competition_line_height || 94;
                    for (let line of compLines) {
                        ctx.fillText(line, 0, compY);
                        compY += compLineH;
                    }
                    ctx.restore();
                }

                // Result Sequence Number (Mongolian Baiti 145pt -> 480px, Color #D02627, Tracking -40)
                if (this.settings.show_result_no && this.data.result_no) {
                    ctx.save();
                    ctx.translate(this.settings.result_x || 740, this.settings.result_y || 779);
                    if (this.settings.result_rotation) {
                        ctx.rotate((this.settings.result_rotation * Math.PI) / 180);
                    }
                    ctx.fillStyle = this.settings.result_color || '#D02627';
                    const numFont = 'Mongolian Baiti';
                    ctx.font = `${this.settings.result_weight || '400'} ${this.settings.result_size || 480}px "${numFont}", serif`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'alphabetic';
                    if ('letterSpacing' in ctx) ctx.letterSpacing = '-20.8px';
                    ctx.fillText(this.data.result_no, 0, 0);
                    ctx.restore();
                }

                // Winners with 01, 02, 03 rank medals, multi-line support, and unified scale
                if (this.settings.show_winners) {
                    const flatWinners = [];
                    ['first', 'second', 'third'].forEach(rankKey => {
                        const rankNum = rankKey === 'first' ? '01' : (rankKey === 'second' ? '02' : '03');
                        const rankLabel = rankKey === 'first' ? '1st' : (rankKey === 'second' ? '2nd' : '3rd');
                        const items = this.data.winners[rankKey] || [];
                        items.forEach(item => {
                            if (item && item.name && item.name.trim() !== '') {
                                flatWinners.push({
                                    rank: rankNum,
                                    rankLabel: rankLabel,
                                    rankKey: rankKey,
                                    name: item.name.trim(),
                                    unit: (item.unit || '').trim()
                                });
                            }
                        });
                    });

                    const count = flatWinners.length;
                    if (count > 0) {
                        const masterScale = (this.settings.winners_scale || 1.0);
                        const autoScale = count <= 3 ? 1.0 : Math.min(1.0, Math.max(0.48, 3.15 / count));
                        const finalScale = masterScale * autoScale;

                        const availHeight = 520;
                        const rowGap = Math.round((count <= 3 ? (this.settings.row_gap || 178) : Math.floor(availHeight / count)) * masterScale);
                        const startY = count <= 3 ? (this.settings.first_top || 145) : Math.max(90, Math.floor(130 - (count - 3) * 10));

                        const rankSize = Math.max(14, Math.round((this.settings.medal_size || 26) * finalScale));
                        const winFont = this.settings.winner_font || 'Rockwell Std';
                        const winSize = Math.max(16, Math.round((this.settings.winner_name_size || 34) * finalScale));
                        const unitSize = Math.max(12, Math.round((this.settings.winner_unit_size || 26) * finalScale));
                        const nameLineH = Math.max(16, Math.round((this.settings.winner_name_line_height || 38) * finalScale));
                        const unitGap = Math.max(4, Math.round((this.settings.winner_unit_gap || 10) * finalScale));
                        const blockLeft = this.settings.block_left || 205;
                        const dividerWidth = Math.round(280 * Math.min(1.0, finalScale * 1.1));

                        let currY = startY;
                        flatWinners.forEach(w => {
                            ctx.save();
                            ctx.textAlign = 'left';
                            ctx.textBaseline = 'top';

                            // 1. Draw Medal / Rank
                            const medalType = this.settings.medal_type || 'numbers';
                            if (medalType === 'numbers') {
                                ctx.fillStyle = this.settings.winner_name_color || '#D02627';
                                ctx.font = `400 ${rankSize}px "Rockwell Std", serif`;
                                if ('letterSpacing' in ctx) ctx.letterSpacing = '-0.8px';
                                ctx.fillText(w.rank, blockLeft, currY);
                            } else if (medalType === 'badges') {
                                const badgeW = Math.round(rankSize * 1.8);
                                const badgeH = Math.round(rankSize * 1.1);
                                ctx.fillStyle = '#D02627';
                                this.drawRoundedRect(ctx, blockLeft, currY, badgeW, badgeH, 6);
                                ctx.fill();
                                ctx.fillStyle = '#FFFFFF';
                                ctx.font = `700 ${Math.round(rankSize * 0.75)}px monospace`;
                                ctx.textAlign = 'center';
                                ctx.fillText(w.rank, blockLeft + (badgeW / 2), currY + 2);
                                ctx.textAlign = 'left';
                            } else if (medalType === 'text') {
                                ctx.fillStyle = this.settings.winner_name_color || '#D02627';
                                ctx.font = `700 ${Math.round(rankSize * 0.9)}px "Rockwell Std", serif`;
                                ctx.fillText(w.rankLabel, blockLeft, currY);
                            }

                            // 2. Draw Student Name (Supports 2-Line Option)
                            let nameY = currY + Math.round(rankSize * 1.3);
                            ctx.fillStyle = this.settings.winner_name_color || '#D02627';
                            ctx.font = `${this.settings.winner_name_weight || '400'} ${winSize}px "${winFont}", serif`;
                            if ('letterSpacing' in ctx) ctx.letterSpacing = '-1.2px';

                            let nameLines = [];
                            if (w.name.includes('\n')) {
                                nameLines = w.name.split('\n');
                            } else if (w.name.includes('/')) {
                                nameLines = w.name.split('/');
                            } else if (this.settings.winner_name_2lines && w.name.length > 16 && w.name.includes(' ')) {
                                const parts = w.name.split(' ');
                                const splitIdx = Math.ceil(parts.length / 2);
                                nameLines = [parts.slice(0, splitIdx).join(' '), parts.slice(splitIdx).join(' ')];
                            } else {
                                nameLines = [w.name];
                            }

                            nameLines.forEach((nLine, lIdx) => {
                                ctx.fillText(nLine.trim(), blockLeft, nameY);
                                if (lIdx < nameLines.length - 1) {
                                    nameY += nameLineH;
                                }
                            });

                            // 3. Draw Group Name
                            let unitY = nameY + winSize + unitGap;
                            ctx.fillStyle = this.settings.winner_unit_color || '#000000';
                            ctx.font = `${this.settings.winner_unit_weight || '300'} ${unitSize}px "Rockwell Std", serif`;
                            if ('letterSpacing' in ctx) ctx.letterSpacing = '-0.5px';
                            ctx.fillText(w.unit, blockLeft, unitY);

                            // 4. Subtle Divider Line
                            let lineY = unitY + unitSize + 8;
                            ctx.strokeStyle = 'rgba(0, 0, 0, 0.22)';
                            ctx.lineWidth = 1;
                            ctx.beginPath();
                            ctx.moveTo(blockLeft, lineY);
                            ctx.lineTo(blockLeft + dividerWidth, lineY);
                            ctx.stroke();

                            ctx.restore();
                            currY += rowGap;
                        });
                    }
                }

            } else {
                // Classic Default Engine
                if (this.settings.show_result_no && this.data.result_no) {
                    ctx.save();
                    ctx.fillStyle = this.settings.result_color;
                    ctx.font = `${this.settings.result_weight} ${this.settings.result_size}px "JetBrains Mono", Sora, sans-serif`;
                    ctx.textBaseline = 'top';
                    ctx.fillText(this.data.result_no, this.settings.result_x, this.settings.result_y);
                    ctx.restore();
                }

                if (this.settings.show_category && this.data.category) {
                    ctx.save();
                    ctx.fillStyle = this.settings.category_color;
                    ctx.font = `${this.settings.category_weight} ${this.settings.category_size}px "Anek Malayalam", Sora, sans-serif`;
                    ctx.textAlign = this.settings.category_align;
                    ctx.textBaseline = 'top';
                    ctx.fillText(this.data.category, this.settings.category_x, this.settings.category_y);
                    ctx.restore();
                }

                if (this.settings.show_competition && this.data.competition) {
                    ctx.save();
                    ctx.fillStyle = this.settings.competition_color;
                    ctx.font = `${this.settings.competition_weight} ${this.settings.competition_size}px "${this.settings.competition_font || 'Sora'}", sans-serif`;
                    ctx.textAlign = this.settings.competition_align;
                    ctx.textBaseline = 'top';

                    const lines = this.wrapText(ctx, this.data.competition, this.settings.competition_max_width);
                    let lineY = this.settings.competition_y;
                    for (let l of lines) {
                        ctx.fillText(l, this.settings.competition_x, lineY);
                        lineY += this.settings.competition_line_height;
                    }
                    ctx.restore();
                }
            }

            // 3. Draw Overlay Element if layer order is 'above' (Top Layer - Above All Content)
            if (this.overlay.enabled && this.overlay.layerOrder === 'above') {
                this.drawOverlayElement(ctx);
            }

            // 4. Draw Illustrator Cyan Guidelines (Preview only, NOT in export)
            if (this.guidelines.show && !isExport) {
                this.drawGuidelines(ctx, canvas.width, canvas.height);
            }
        },

        drawOverlayElement(ctx) {
            ctx.save();
            ctx.translate(this.overlay.x || 480, this.overlay.y || 830);
            if (this.overlay.rotation) {
                ctx.rotate((this.overlay.rotation * Math.PI) / 180);
            }
            ctx.globalAlpha = Math.max(0, Math.min(1, (this.overlay.opacity || 100) / 100));

            if (this.overlay.type === 'image' && this.overlayImageObj && this.overlayImageObj.complete) {
                const w = this.overlayImageObj.naturalWidth * (this.overlay.scale || 1.0);
                const h = this.overlayImageObj.naturalHeight * (this.overlay.scale || 1.0);
                ctx.drawImage(this.overlayImageObj, -w / 2, -h / 2, w, h);
            } else if (this.overlay.type === 'text') {
                ctx.fillStyle = this.overlay.color || '#000000';
                ctx.font = `${this.overlay.weight || '400'} ${this.overlay.size || 140}px "${this.overlay.font || 'Rockwell Std'}", serif`;
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                ctx.fillText(this.overlay.text || 'Result', 0, 0);
            }
            ctx.restore();
        },

        drawGuidelines(ctx, width, height) {
            ctx.save();
            ctx.strokeStyle = 'rgba(0, 240, 255, 0.85)'; // Signature Cyan
            ctx.lineWidth = 1;
            ctx.setLineDash([4, 4]);

            // Vertical Guides (X)
            (this.guidelines.vertical || []).forEach(gx => {
                ctx.beginPath();
                ctx.moveTo(gx, 0);
                ctx.lineTo(gx, height);
                ctx.stroke();

                // Small badge
                ctx.fillStyle = 'rgba(0, 240, 255, 0.9)';
                ctx.fillRect(gx + 2, 8, 48, 14);
                ctx.fillStyle = '#0f172a';
                ctx.font = 'bold 9px monospace';
                ctx.fillText(`X:${gx}`, gx + 5, 18);
            });

            // Horizontal Guides (Y)
            (this.guidelines.horizontal || []).forEach(gy => {
                ctx.beginPath();
                ctx.moveTo(0, gy);
                ctx.lineTo(width, gy);
                ctx.stroke();

                // Small badge
                ctx.fillStyle = 'rgba(0, 240, 255, 0.9)';
                ctx.fillRect(8, gy + 2, 48, 14);
                ctx.fillStyle = '#0f172a';
                ctx.font = 'bold 9px monospace';
                ctx.fillText(`Y:${gy}`, 11, gy + 12);
            });

            ctx.restore();
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

        wrapText(ctx, text, maxWidth) {
            if (!text) return [];
            const words = text.split(' ');
            const lines = [];
            let currentLine = words[0];

            for (let i = 1; i < words.length; i++) {
                const word = words[i];
                const width = ctx.measureText(currentLine + ' ' + word).width;
                if (width < maxWidth) {
                    currentLine += ' ' + word;
                } else {
                    lines.push(currentLine);
                    currentLine = word;
                }
            }
            if (currentLine) lines.push(currentLine);
            return lines;
        },

        downloadPoster() {
            // Render cleanly without guidelines
            this.render(true);
            const canvas = document.getElementById('posterCanvas');
            const link = document.createElement('a');
            link.download = `QUAF_Result_${this.data.result_no}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
            // Restore preview guidelines
            this.render(false);
        },

        saveAsDefault() {
            this.savingDefault = true;
            this.statusMsg = '';

            fetch('{{ route("media.results.save-default-settings") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    template_id: this.settings.template_id,
                    settings: Object.assign({}, this.settings, {
                        overlay: this.overlay,
                        guidelines: this.guidelines
                    })
                })
            })
            .then(res => res.json())
            .then(data => {
                this.savingDefault = false;
                if (data.success) {
                    this.statusType = 'success';
                    this.statusMsg = data.message || 'Default settings saved successfully.';
                } else {
                    this.statusType = 'error';
                    this.statusMsg = 'Failed to save default settings.';
                }
            })
            .catch(err => {
                this.savingDefault = false;
                this.statusType = 'error';
                this.statusMsg = 'Error saving defaults: ' + err.message;
            });
        },

        saveAndPublish() {
            this.publishing = true;
            this.statusMsg = '';
            // Render without guidelines for high-res clean export
            this.render(true);

            const canvas = document.getElementById('posterCanvas');
            const dataUrl = canvas.toDataURL('image/png');

            fetch('{{ route("media.results.save-poster", $result) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    poster_data: dataUrl,
                    template_id: this.settings.template_id,
                    settings: Object.assign({}, this.settings, {
                        custom_winners: this.data.winners,
                        overlay: this.overlay,
                        guidelines: this.guidelines
                    }),
                    publish_now: true
                })
            })
            .then(res => res.json())
            .then(data => {
                this.publishing = false;
                this.render(false); // restore preview
                if (data.success) {
                    this.statusType = 'success';
                    this.statusMsg = 'Result poster generated and published successfully!';
                    setTimeout(() => {
                        window.location.href = '{{ route("media.results.index", ["tab" => "published"]) }}';
                    }, 1200);
                } else {
                    this.statusType = 'error';
                    this.statusMsg = data.message || 'Failed to publish poster.';
                }
            })
            .catch(err => {
                this.publishing = false;
                this.render(false);
                this.statusType = 'error';
                this.statusMsg = 'Error uploading poster: ' + err.message;
            });
        }
    };
}
</script>
@endsection
