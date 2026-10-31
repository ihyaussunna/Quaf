@extends('layouts.media')

@section('title', (!empty($isTemplateCustomizer) ? 'Customize Template: ' . ($template->name ?? '') : 'Poster Studio: ' . ($result->program->name ?? '')) . ' | QUAF Media')

@section('content')
<div class="space-y-6" x-data="posterStudio()">
    <!-- Font Preloaders to force instant Canvas font availability -->
    <div class="sr-only" aria-hidden="true" style="position: absolute; left: -9999px; top: -9999px; visibility: hidden;">
        <span style="font-family: 'Mongolian Baiti';">0123456789</span>
        <span style="font-family: 'Rockwell Std'; font-weight: 300;">Rockwell Std 300</span>
        <span style="font-family: 'Rockwell Std'; font-weight: 400;">Rockwell Std 400</span>
        <span style="font-family: 'Rockwell Std'; font-weight: 700;">Rockwell Std 700</span>
        <span style="font-family: 'Rockwell Std'; font-weight: 800;">Rockwell Std 800</span>
        <span style="font-family: 'Montserrat'; font-weight: 300;">Montserrat 300</span>
        <span style="font-family: 'Montserrat'; font-weight: 400;">Montserrat 400</span>
        <span style="font-family: 'Montserrat'; font-weight: 500;">Montserrat 500</span>
        <span style="font-family: 'Montserrat'; font-weight: 600;">Montserrat 600</span>
        <span style="font-family: 'Montserrat'; font-weight: 700;">Montserrat 700</span>
        <span style="font-family: 'Montserrat'; font-weight: 800;">Montserrat 800</span>
        <span style="font-family: 'Montserrat'; font-weight: 900;">Montserrat 900</span>
        <span style="font-family: 'Sora'; font-weight: 300;">Sora 300</span>
        <span style="font-family: 'Sora'; font-weight: 400;">Sora 400</span>
        <span style="font-family: 'Sora'; font-weight: 600;">Sora 600</span>
        <span style="font-family: 'Sora'; font-weight: 700;">Sora 700</span>
        <span style="font-family: 'Sora'; font-weight: 800;">Sora 800</span>
        <span style="font-family: 'JetBrains Mono'; font-weight: 400;">JetBrains Mono 400</span>
        <span style="font-family: 'JetBrains Mono'; font-weight: 700;">JetBrains Mono 700</span>
        <span style="font-family: 'Georgia'; font-weight: 400;">Georgia 400</span>
        <span style="font-family: 'Playfair Display'; font-weight: 700;">Playfair Display 700</span>
        <span style="font-family: 'Cinzel'; font-weight: 700;">Cinzel 700</span>
        <span style="font-family: 'Anek Malayalam'; font-weight: 700;">Anek Malayalam 700</span>
    </div>

    <!-- Top Action & Navigation Header -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    @if(!empty($isTemplateCustomizer))
                        <a href="{{ route('media.results.templates') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            <span>Back to Poster Templates</span>
                        </a>
                        <span class="text-slate-300">&bull;</span>
                        <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-[#be1e2d] text-white">
                            Template #{{ $template->id }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-purple-100 text-purple-800 border border-purple-200">
                            Master Template Customizer
                        </span>
                    @else
                        <a href="{{ route('media.results.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            <span>Back to Results</span>
                        </a>
                        <span class="text-slate-300">&bull;</span>
                        <span class="px-2.5 py-0.5 rounded font-mono text-[10px] font-black bg-[#be1e2d] text-white shadow-2xs">
                            Result #{{ sprintf('%02d', $result->result_number) }}
                        </span>
                        <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-slate-900 text-white">
                            {{ $result->program->code ?? 'RESULT' }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                            {{ ucfirst($result->status ?? 'announced') }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-purple-100 text-purple-800 border border-purple-200">
                            Result Poster Studio
                        </span>
                    @endif
                </div>

                <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight mt-1.5 font-sora">
                    @if(!empty($isTemplateCustomizer))
                        Customize Template: <span class="text-[#be1e2d]">{{ $template->name }}</span>
                    @else
                        {{ $result->program->name }}
                    @endif
                </h1>
                <p class="text-xs text-slate-500">
                    @if(!empty($isTemplateCustomizer))
                        Mode: <strong class="text-purple-700">Master Template Designer</strong> &bull;
                        Result No: <strong class="text-[#be1e2d] font-mono">#{{ sprintf('%02d', $resultNo) }}</strong> &bull;
                        Selected: <strong class="text-purple-700 font-mono" x-text="selectedLabel">None</strong>
                    @else
                        Zone: <strong class="text-slate-700">{{ $result->program->zone->name ?? $result->program->eligibility ?? 'All Zones' }}</strong> &bull;
                        Result No: <strong class="text-[#be1e2d] font-mono">#{{ sprintf('%02d', $resultNo) }}</strong> &bull;
                        Selected: <strong class="text-purple-700 font-mono" x-text="selectedLabel">None</strong>
                    @endif
                </p>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Add Text Button -->
                <button type="button" @click="addCustomText()"
                        class="px-3.5 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-900 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ Text</span>
                </button>

                <!-- Add Shape Button -->
                <button type="button" @click="addCustomShape('badge')"
                        class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-900 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                    <span>+ Badge / Shape</span>
                </button>

                <!-- Guidelines Toggle -->
                <button type="button" @click="guidelines.show = !guidelines.show; render();"
                        class="px-3 py-2 rounded-xl border text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer"
                        :class="guidelines.show ? 'bg-cyan-50 border-cyan-400 text-cyan-800' : 'bg-white border-slate-300 text-slate-600 hover:bg-slate-50'">
                    <svg class="w-3.5 h-3.5 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16M10 4v16M14 4v16"></path></svg>
                    <span x-text="guidelines.show ? 'Guides ON' : 'Guides OFF'"></span>
                </button>

                <!-- Undo & Redo Buttons (Header) -->
                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200">
                    <button type="button" @click="undo()" :disabled="historyIndex <= 0"
                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-slate-700 hover:bg-white hover:shadow-2xs transition disabled:opacity-30 disabled:pointer-events-none cursor-pointer flex items-center gap-1"
                            title="Undo (Ctrl+Z) - മുൻപത്തെ മാറ്റം റദ്ദാക്കുക">
                        <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2m0 0l-4-4m4 4l4-4M3 10l4-4m-4 4l4 4"></path></svg>
                        <span>Undo</span>
                    </button>
                    <button type="button" @click="redo()" :disabled="historyIndex >= history.length - 1"
                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-slate-700 hover:bg-white hover:shadow-2xs transition disabled:opacity-30 disabled:pointer-events-none cursor-pointer flex items-center gap-1"
                            title="Redo (Ctrl+Y / Ctrl+Shift+Z) - മാറ്റം വീണ്ടെടുക്കുക">
                        <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2m0 0l4-4m-4 4l-4-4m15-6l-4-4m4 4l-4 4"></path></svg>
                        <span>Redo</span>
                    </button>
                </div>

                @if(!empty($isTemplateCustomizer))
                    <!-- Save Template Defaults in Customizer mode -->
                    <button type="button" @click="saveAsDefault()" :disabled="savingDefault"
                            class="px-5 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs flex items-center gap-1.5 transition-all shadow-md shadow-[#be1e2d]/20 disabled:opacity-50 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span x-text="savingDefault ? 'സേവ് ചെയ്യുന്നു...' : 'Save Template Defaults (ടെംപ്ലേറ്റ് സേവ് ചെയ്യുക)'"></span>
                    </button>
                    <!-- Download PNG Preview for Template Customizer -->
                    <button type="button" @click="downloadPoster(2)"
                            class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer"
                            title="Download high-resolution Ultra HD preview">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>Preview Ultra HD</span>
                    </button>
                @else
                    <!-- Save as Default in Result mode -->
                    <button type="button" @click="saveAsDefault()" :disabled="savingDefault"
                            class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors disabled:opacity-50 cursor-pointer"
                            title="ഈ റിസൾട്ടിലെ മാറ്റങ്ങൾ ടെംപ്ലേറ്റിന്റെ സ്ഥിരം ഡിഫോൾട്ടാക്കി മാറ്റുക">
                        <span x-text="savingDefault ? 'Saving...' : 'Save Default'"></span>
                    </button>

                    <!-- Download PNG (Ultra HD) -->
                    <button type="button" @click="downloadPoster(2)"
                            class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer"
                            title="Download in Ultra HD (2160x2700 High Quality Clear Poster)">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>Download Ultra HD</span>
                    </button>

                    <!-- Direct Poster Upload Button -->
                    <button type="button" @click="showStudioUploadModal = true"
                            class="px-3.5 py-2 rounded-xl border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center gap-1.5 transition-colors shadow-2xs cursor-pointer"
                            title="Upload already finished poster file directly (ഡിസൈൻ ചെയ്ത പോസ്റ്റർ നേരിട്ട് അപ്‌ലോഡ് ചെയ്യുക)">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Upload Poster</span>
                    </button>

                    <!-- Save & Publish -->
                    <button type="button" @click="saveAndPublish()" :disabled="publishing"
                            class="px-5 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-black text-xs flex items-center gap-1.5 transition-all shadow-md shadow-[#be1e2d]/20 disabled:opacity-50 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span x-text="publishing ? 'Publishing...' : 'Save & Publish'"></span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Quick Template Selector Bar (Always visible for instant 1-click switching) -->
        <div class="pt-3 border-t border-slate-100 flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5 shrink-0">
                <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>ഫ്രെയിം തിരഞ്ഞെടുക്കുക (Template):</span>
            </span>
            <div class="flex items-center gap-1.5 flex-wrap">
                @foreach($templates as $tmpl)
                    <button type="button" @click="selectTemplate({{ $tmpl->id }}, '{{ asset($tmpl->image_path) }}', '{{ $tmpl->posterSetting?->layout_mode ?? "default" }}')"
                            class="px-3 py-1.5 text-xs font-bold rounded-xl border transition-all cursor-pointer flex items-center gap-2"
                            :class="settings.template_id == {{ $tmpl->id }} ? 'bg-[#be1e2d] text-white border-[#be1e2d] shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:border-slate-300 hover:bg-slate-100'">
                        <span class="w-2 h-2 rounded-full" :class="settings.template_id == {{ $tmpl->id }} ? 'bg-amber-300' : 'bg-slate-300'"></span>
                        <span>{{ $tmpl->name }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Alert Status Messages -->
    <div x-show="statusMsg" x-cloak
         :class="statusType === 'success' ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-red-50 border-red-300 text-red-900'"
         class="p-4 rounded-xl border text-xs font-bold transition-all flex items-center justify-between">
        <span x-text="statusMsg"></span>
        <button type="button" @click="statusMsg = ''" class="text-xs opacity-60 hover:opacity-100 font-bold">&times;</button>
    </div>

    <!-- Studio Layout: Left Inspector Panel (5 cols) & Right Canvas Studio (7 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Controls Column (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            
            <!-- Pro Navigation Tabs -->
            <div class="flex items-center gap-1.5 p-1.5 bg-slate-200/80 rounded-2xl border border-slate-300/80 overflow-x-auto">
                <button type="button" @click="activeTab = 'templates'"
                        class="px-3 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer"
                        :class="activeTab === 'templates' ? 'bg-[#be1e2d] text-white shadow-xs' : 'text-slate-700 hover:text-slate-900 bg-white/50'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Templates</span>
                </button>

                <button type="button" @click="activeTab = 'design'"
                        class="px-3 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer"
                        :class="activeTab === 'design' ? 'bg-white text-purple-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    <span>Design Tool</span>
                </button>

                <button type="button" @click="activeTab = 'layers'"
                        class="px-3 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer"
                        :class="activeTab === 'layers' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>Layers</span>
                </button>

                <button type="button" @click="activeTab = 'winners'"
                        class="px-3 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer"
                        :class="activeTab === 'winners' ? 'bg-white text-amber-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>Winners & Scale</span>
                </button>

                <button type="button" @click="activeTab = 'core'"
                        class="px-3 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer"
                        :class="activeTab === 'core' ? 'bg-white text-blue-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"></path></svg>
                    <span>Core Elements</span>
                </button>

                <button type="button" @click="activeTab = 'guides'"
                        class="px-3 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer"
                        :class="activeTab === 'guides' ? 'bg-white text-cyan-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    <span>Guides</span>
                </button>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 1: MINI POSTER DESIGN TOOL (ACTIVE ELEMENT INSPECTOR)       -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'design'" class="space-y-4">
                
                <!-- Quick Insertion Bar -->
                <div class="bg-gradient-to-r from-purple-950 via-slate-900 to-slate-900 text-white p-4 rounded-2xl border border-purple-800 shadow-md">
                    <div class="text-[10px] font-mono font-bold text-purple-300 uppercase tracking-wider mb-2">Insert Elements (പുതിയ എലമെന്റുകൾ ചേർക്കുക)</div>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="addCustomText()"
                                class="p-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-xs font-bold flex flex-col items-center justify-center gap-1 transition">
                            <span class="text-base font-black">T</span>
                            <span>+ Add Text</span>
                        </button>
                        <button type="button" @click="addCustomShape('badge')"
                                class="p-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-xs font-bold flex flex-col items-center justify-center gap-1 transition">
                            <span class="text-base font-black text-amber-400">★</span>
                            <span>+ Shape / Badge</span>
                        </button>
                        <label class="p-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-xs font-bold flex flex-col items-center justify-center gap-1 transition cursor-pointer">
                            <span class="text-base font-black text-cyan-400">🖼</span>
                            <span>+ Upload Image</span>
                            <input type="file" @change="uploadCustomImage($event)" accept="image/png,image/jpeg,image/svg+xml" class="hidden">
                        </label>
                    </div>
                </div>

                <!-- Case A: Selected Element is Custom Text -->
                <div x-show="selectedElement && selectedElement.type === 'text'" class="bg-white p-5 rounded-2xl border border-purple-300 ring-2 ring-purple-400/20 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-purple-100 text-purple-900 font-black text-xs flex items-center justify-center">T</span>
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800" x-text="selectedElement ? selectedElement.label : 'Custom Text'"></h3>
                                <p class="text-[10px] text-slate-500">Edit text content, typography, rotation, and position.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="duplicateSelected()" title="Duplicate" class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </button>
                            <button type="button" @click="deleteSelected()" title="Delete" class="p-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Text Content (Multiline Supported) -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Text Content (എഴുത്ത്)</label>
                        <textarea x-model="selectedElement.text" @input="render()" rows="2"
                                  class="w-full text-xs font-medium p-2.5 rounded-xl border border-slate-300 focus:outline-none focus:border-purple-500"></textarea>
                    </div>

                    <!-- Font Family & Font Weight -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Font</label>
                            <select x-model="selectedElement.font" @change="render()" class="w-full text-xs p-2 rounded-xl border border-slate-300 bg-white">
                                <option value="Rockwell Std">Rockwell Std</option>
                                <option value="Mongolian Baiti">Mongolian Baiti</option>
                                <option value="Sora">Sora</option>
                                <option value="Anek Malayalam">Anek Malayalam</option>
                                <option value="Amiri">Amiri (Arabic/Urdu)</option>
                                <option value="JetBrains Mono">JetBrains Mono</option>
                                <option value="Montserrat">Montserrat</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Weight</label>
                            <select x-model="selectedElement.weight" @change="render()" class="w-full text-xs p-2 rounded-xl border border-slate-300 bg-white">
                                <option value="300">Light (300)</option>
                                <option value="400">Regular (400)</option>
                                <option value="700">Bold (700)</option>
                                <option value="800">Extra Bold (800)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Font Size & Text Color -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                <span>Font Size</span>
                                <span class="font-mono text-purple-700" x-text="selectedElement.size + 'px'"></span>
                            </div>
                            <input type="range" min="12" max="250" step="1" x-model.number="selectedElement.size" @input="render()" class="w-full accent-purple-600">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Text Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="selectedElement.color" @input="render()" class="w-8 h-8 rounded border border-slate-200 cursor-pointer">
                                <input type="text" x-model="selectedElement.color" @input="render()" class="flex-1 text-xs font-mono p-1.5 rounded-lg border border-slate-300 uppercase">
                            </div>
                        </div>
                    </div>

                    <!-- Alignment & Letter Spacing -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Align</label>
                            <div class="flex rounded-xl border border-slate-300 p-0.5 bg-slate-100">
                                <button type="button" @click="selectedElement.align = 'left'; render();" class="flex-1 py-1 text-xs font-bold rounded-lg" :class="selectedElement.align === 'left' ? 'bg-white shadow-2xs text-purple-900' : 'text-slate-600'">Left</button>
                                <button type="button" @click="selectedElement.align = 'center'; render();" class="flex-1 py-1 text-xs font-bold rounded-lg" :class="selectedElement.align === 'center' ? 'bg-white shadow-2xs text-purple-900' : 'text-slate-600'">Center</button>
                                <button type="button" @click="selectedElement.align = 'right'; render();" class="flex-1 py-1 text-xs font-bold rounded-lg" :class="selectedElement.align === 'right' ? 'bg-white shadow-2xs text-purple-900' : 'text-slate-600'">Right</button>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                <span>Tracking</span>
                                <span class="font-mono text-purple-700" x-text="selectedElement.tracking + 'px'"></span>
                            </div>
                            <input type="range" min="-5" max="25" step="0.5" x-model.number="selectedElement.tracking" @input="render()" class="w-full accent-purple-600">
                        </div>
                    </div>

                    <!-- Line Distance / Spacing for Multi-line Text -->
                    <div class="space-y-1.5 p-2.5 rounded-xl bg-purple-50/60 border border-purple-200">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                            <span>Line Distance (വരികൾ തമ്മിലുള്ള അകലം)</span>
                            <div class="flex items-center gap-1 font-mono">
                                <button type="button" @click="selectedElement.lineHeight = Math.max(0.6, +(selectedElement.lineHeight - 0.1).toFixed(1)); render();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold hover:bg-slate-100 flex items-center justify-center">-</button>
                                <span class="w-12 text-center text-purple-700 font-bold" x-text="selectedElement.lineHeight"></span>
                                <button type="button" @click="selectedElement.lineHeight = Math.min(2.8, +(selectedElement.lineHeight + 0.1).toFixed(1)); render();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold hover:bg-slate-100 flex items-center justify-center">+</button>
                            </div>
                        </div>
                        <input type="range" min="0.6" max="2.8" step="0.05" x-model.number="selectedElement.lineHeight" @input="render()" class="w-full accent-purple-600">
                    </div>

                    <!-- Rotation & Opacity -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                <span>Rotation</span>
                                <span class="font-mono text-purple-700" x-text="selectedElement.rotation + '°'"></span>
                            </div>
                            <input type="range" min="-180" max="180" step="1" x-model.number="selectedElement.rotation" @input="render()" class="w-full accent-purple-600">
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                <span>Opacity</span>
                                <span class="font-mono text-purple-700" x-text="selectedElement.opacity + '%'"></span>
                            </div>
                            <input type="range" min="0" max="100" step="1" x-model.number="selectedElement.opacity" @input="render()" class="w-full accent-purple-600">
                        </div>
                    </div>

                    <!-- Position X & Y + Precision Nudge -->
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-700">Position Coordinates</span>
                            <div class="text-[10px] font-mono text-slate-500">
                                X: <span class="font-bold text-slate-800" x-text="selectedElement.x"></span>,
                                Y: <span class="font-bold text-slate-800" x-text="selectedElement.y"></span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex items-center gap-1.5 text-xs">
                                <span class="font-bold text-slate-500">X:</span>
                                <input type="number" x-model.number="selectedElement.x" @input="render()" class="w-full text-xs p-1.5 rounded-lg border border-slate-300 font-mono">
                            </div>
                            <div class="flex items-center gap-1.5 text-xs">
                                <span class="font-bold text-slate-500">Y:</span>
                                <input type="number" x-model.number="selectedElement.y" @input="render()" class="w-full text-xs p-1.5 rounded-lg border border-slate-300 font-mono">
                            </div>
                        </div>

                        <!-- D-Pad Nudge Buttons -->
                        <div class="flex items-center justify-center gap-1 pt-1">
                            <button type="button" @click="nudgeSelected(-10, 0)" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700">&larr; 10</button>
                            <button type="button" @click="nudgeSelected(-1, 0)" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700">&larr;</button>
                            <button type="button" @click="nudgeSelected(0, -1)" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700">&uarr;</button>
                            <button type="button" @click="nudgeSelected(0, 1)" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700">&darr;</button>
                            <button type="button" @click="nudgeSelected(1, 0)" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700">&rarr;</button>
                            <button type="button" @click="nudgeSelected(10, 0)" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700">10 &rarr;</button>
                        </div>
                    </div>

                    <!-- Layer Stacking Order: Top / Up / Down / Bottom -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-700">Layer Stack (ലെയർ ക്രമം)</span>
                        <div class="flex items-center gap-1 font-mono">
                            <button type="button" @click="bringToFront(selectedId)" :disabled="layerStack.indexOf(selectedId) <= 0" class="px-2 py-1 bg-slate-100 hover:bg-purple-100 rounded text-[10px] font-bold text-slate-700 disabled:opacity-30 cursor-pointer" title="Bring to Front (ഏറ്റവും മുകളിൽ)">⇈ Top</button>
                            <button type="button" @click="moveLayerUp(selectedId)" :disabled="layerStack.indexOf(selectedId) <= 0" class="px-2 py-1 bg-purple-600 hover:bg-purple-700 rounded text-[10px] font-bold text-white disabled:opacity-30 cursor-pointer" title="Move Up (മുകളിലേക്ക്)">▲ Up</button>
                            <button type="button" @click="moveLayerDown(selectedId)" :disabled="layerStack.indexOf(selectedId) >= layerStack.length - 1" class="px-2 py-1 bg-purple-600 hover:bg-purple-700 rounded text-[10px] font-bold text-white disabled:opacity-30 cursor-pointer" title="Move Down (താഴേക്ക്)">▼ Down</button>
                            <button type="button" @click="sendToBack(selectedId)" :disabled="layerStack.indexOf(selectedId) >= layerStack.length - 1" class="px-2 py-1 bg-slate-100 hover:bg-purple-100 rounded text-[10px] font-bold text-slate-700 disabled:opacity-30 cursor-pointer" title="Send to Back (ഏറ്റവും താഴെ)">⇊ Bottom</button>
                        </div>
                    </div>
                </div>

                <!-- Case B: Selected Element is Custom Shape / Badge -->
                <div x-show="selectedElement && selectedElement.type === 'shape'" class="bg-white p-5 rounded-2xl border border-amber-300 ring-2 ring-amber-400/20 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-amber-100 text-amber-900 font-black text-xs flex items-center justify-center">★</span>
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800" x-text="selectedElement ? selectedElement.label : 'Shape / Badge'"></h3>
                                <p class="text-[10px] text-slate-500">Shape geometry, fill color, and transformation.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="duplicateSelected()" class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </button>
                            <button type="button" @click="deleteSelected()" class="p-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Shape Type Preset -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Shape Preset</label>
                        <select x-model="selectedElement.shapeType" @change="render()" class="w-full text-xs p-2 rounded-xl border border-slate-300 bg-white">
                            <option value="badge">Badge with Ribbon</option>
                            <option value="seal">Golden Award Seal</option>
                            <option value="pill">Rounded Capsule</option>
                            <option value="rectangle">Solid Box</option>
                            <option value="line">Divider Line</option>
                            <option value="star">Star Icon</option>
                            <option value="stamp">Result Stamp</option>
                        </select>
                    </div>

                    <!-- Dimensions Width & Height -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                <span>Width</span>
                                <span class="font-mono text-amber-700" x-text="selectedElement.width + 'px'"></span>
                            </div>
                            <input type="range" min="10" max="800" step="2" x-model.number="selectedElement.width" @input="render()" class="w-full accent-amber-600">
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                <span>Height</span>
                                <span class="font-mono text-amber-700" x-text="selectedElement.height + 'px'"></span>
                            </div>
                            <input type="range" min="4" max="400" step="2" x-model.number="selectedElement.height" @input="render()" class="w-full accent-amber-600">
                        </div>
                    </div>

                    <!-- Colors: Fill & Stroke -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Fill Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="selectedElement.fillColor" @input="render()" class="w-8 h-8 rounded border border-slate-200 cursor-pointer">
                                <input type="text" x-model="selectedElement.fillColor" @input="render()" class="flex-1 text-xs font-mono p-1.5 rounded-lg border border-slate-300 uppercase">
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Stroke Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="selectedElement.strokeColor" @input="render()" class="w-8 h-8 rounded border border-slate-200 cursor-pointer">
                                <input type="text" x-model="selectedElement.strokeColor" @input="render()" class="flex-1 text-xs font-mono p-1.5 rounded-lg border border-slate-300 uppercase">
                            </div>
                        </div>
                    </div>

                    <!-- Rotation & Opacity -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                <span>Rotation</span>
                                <span class="font-mono text-amber-700" x-text="selectedElement.rotation + '°'"></span>
                            </div>
                            <input type="range" min="-180" max="180" step="1" x-model.number="selectedElement.rotation" @input="render()" class="w-full accent-amber-600">
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                <span>Opacity</span>
                                <span class="font-mono text-amber-700" x-text="selectedElement.opacity + '%'"></span>
                            </div>
                            <input type="range" min="0" max="100" step="1" x-model.number="selectedElement.opacity" @input="render()" class="w-full accent-amber-600">
                        </div>
                    </div>

                    <!-- Position X & Y -->
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex items-center gap-1.5 text-xs">
                                <span class="font-bold text-slate-500">X:</span>
                                <input type="number" x-model.number="selectedElement.x" @input="render()" class="w-full text-xs p-1.5 rounded-lg border border-slate-300 font-mono">
                            </div>
                            <div class="flex items-center gap-1.5 text-xs">
                                <span class="font-bold text-slate-500">Y:</span>
                                <input type="number" x-model.number="selectedElement.y" @input="render()" class="w-full text-xs p-1.5 rounded-lg border border-slate-300 font-mono">
                            </div>
                        </div>
                        <!-- Nudge Buttons -->
                        <div class="flex items-center justify-center gap-1 pt-1">
                            <button type="button" @click="nudgeSelected(-10, 0)" class="px-2.5 py-1 rounded bg-slate-100 text-xs font-bold">&larr; 10</button>
                            <button type="button" @click="nudgeSelected(0, -1)" class="px-2.5 py-1 rounded bg-slate-100 text-xs font-bold">&uarr;</button>
                            <button type="button" @click="nudgeSelected(0, 1)" class="px-2.5 py-1 rounded bg-slate-100 text-xs font-bold">&darr;</button>
                            <button type="button" @click="nudgeSelected(10, 0)" class="px-2.5 py-1 rounded bg-slate-100 text-xs font-bold">10 &rarr;</button>
                        </div>
                    </div>

                    <!-- Layer Stacking Order: Top / Up / Down / Bottom -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-700">Layer Stack (ലെയർ ക്രമം)</span>
                        <div class="flex items-center gap-1 font-mono">
                            <button type="button" @click="bringToFront(selectedId)" :disabled="layerStack.indexOf(selectedId) <= 0" class="px-2 py-1 bg-slate-100 hover:bg-purple-100 rounded text-[10px] font-bold text-slate-700 disabled:opacity-30 cursor-pointer" title="Bring to Front (ഏറ്റവും മുകളിൽ)">⇈ Top</button>
                            <button type="button" @click="moveLayerUp(selectedId)" :disabled="layerStack.indexOf(selectedId) <= 0" class="px-2 py-1 bg-purple-600 hover:bg-purple-700 rounded text-[10px] font-bold text-white disabled:opacity-30 cursor-pointer" title="Move Up (മുകളിലേക്ക്)">▲ Up</button>
                            <button type="button" @click="moveLayerDown(selectedId)" :disabled="layerStack.indexOf(selectedId) >= layerStack.length - 1" class="px-2 py-1 bg-purple-600 hover:bg-purple-700 rounded text-[10px] font-bold text-white disabled:opacity-30 cursor-pointer" title="Move Down (താഴേക്ക്)">▼ Down</button>
                            <button type="button" @click="sendToBack(selectedId)" :disabled="layerStack.indexOf(selectedId) >= layerStack.length - 1" class="px-2 py-1 bg-slate-100 hover:bg-purple-100 rounded text-[10px] font-bold text-slate-700 disabled:opacity-30 cursor-pointer" title="Send to Back (ഏറ്റവും താഴെ)">⇊ Bottom</button>
                        </div>
                    </div>
                </div>

                <!-- Case C: Selected Element is Custom Image -->
                <div x-show="selectedElement && selectedElement.type === 'image'" class="bg-white p-5 rounded-2xl border border-cyan-300 ring-2 ring-cyan-400/20 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-cyan-100 text-cyan-900 font-black text-xs flex items-center justify-center">🖼</span>
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800" x-text="selectedElement ? selectedElement.label : 'Custom Image'"></h3>
                                <p class="text-[10px] text-slate-500">Scale, rotate, and position graphic.</p>
                            </div>
                        </div>
                        <button type="button" @click="deleteSelected()" class="p-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>

                    <!-- Dimensions -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                <span>Width</span>
                                <span class="font-mono text-cyan-700" x-text="selectedElement.width + 'px'"></span>
                            </div>
                            <input type="range" min="20" max="900" step="5" x-model.number="selectedElement.width" @input="render()" class="w-full accent-cyan-600">
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                <span>Height</span>
                                <span class="font-mono text-cyan-700" x-text="selectedElement.height + 'px'"></span>
                            </div>
                            <input type="range" min="20" max="900" step="5" x-model.number="selectedElement.height" @input="render()" class="w-full accent-cyan-600">
                        </div>
                    </div>

                    <!-- Rotation & Opacity -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                <span>Rotation</span>
                                <span class="font-mono text-cyan-700" x-text="selectedElement.rotation + '°'"></span>
                            </div>
                            <input type="range" min="-180" max="180" step="1" x-model.number="selectedElement.rotation" @input="render()" class="w-full accent-cyan-600">
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                <span>Opacity</span>
                                <span class="font-mono text-cyan-700" x-text="selectedElement.opacity + '%'"></span>
                            </div>
                            <input type="range" min="0" max="100" step="1" x-model.number="selectedElement.opacity" @input="render()" class="w-full accent-cyan-600">
                        </div>
                    </div>

                    <!-- Position -->
                    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                        <div class="flex items-center gap-1.5 text-xs">
                            <span class="font-bold text-slate-500">X:</span>
                            <input type="number" x-model.number="selectedElement.x" @input="render()" class="w-full text-xs p-1.5 rounded-lg border border-slate-300 font-mono">
                        </div>
                        <div class="flex items-center gap-1.5 text-xs">
                            <span class="font-bold text-slate-500">Y:</span>
                            <input type="number" x-model.number="selectedElement.y" @input="render()" class="w-full text-xs p-1.5 rounded-lg border border-slate-300 font-mono">
                        </div>
                    </div>

                    <!-- Layer Stacking Order: Top / Up / Down / Bottom -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-700">Layer Stack (ലെയർ ക്രമം)</span>
                        <div class="flex items-center gap-1 font-mono">
                            <button type="button" @click="bringToFront(selectedId)" :disabled="layerStack.indexOf(selectedId) <= 0" class="px-2 py-1 bg-slate-100 hover:bg-purple-100 rounded text-[10px] font-bold text-slate-700 disabled:opacity-30 cursor-pointer" title="Bring to Front (ഏറ്റവും മുകളിൽ)">⇈ Top</button>
                            <button type="button" @click="moveLayerUp(selectedId)" :disabled="layerStack.indexOf(selectedId) <= 0" class="px-2 py-1 bg-purple-600 hover:bg-purple-700 rounded text-[10px] font-bold text-white disabled:opacity-30 cursor-pointer" title="Move Up (മുകളിലേക്ക്)">▲ Up</button>
                            <button type="button" @click="moveLayerDown(selectedId)" :disabled="layerStack.indexOf(selectedId) >= layerStack.length - 1" class="px-2 py-1 bg-purple-600 hover:bg-purple-700 rounded text-[10px] font-bold text-white disabled:opacity-30 cursor-pointer" title="Move Down (താഴേക്ക്)">▼ Down</button>
                            <button type="button" @click="sendToBack(selectedId)" :disabled="layerStack.indexOf(selectedId) >= layerStack.length - 1" class="px-2 py-1 bg-slate-100 hover:bg-purple-100 rounded text-[10px] font-bold text-slate-700 disabled:opacity-30 cursor-pointer" title="Send to Back (ഏറ്റവും താഴെ)">⇊ Bottom</button>
                        </div>
                    </div>
                </div>

                <!-- Case D: Nothing Selected or Core Element Selected -->
                <div x-show="!selectedElement || selectedType === 'core'" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
                    <div class="text-xs font-bold text-slate-700">എലമെന്റുകൾ സെലക്ട് ചെയ്യുക</div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        വലതുവശത്തുള്ള പോസ്റ്റർ ക്യാൻവാസിൽ ക്ലിക്ക് ചെയ്ത് ഏതൊരു എലമെന്റും സെലക്ട് ചെയ്യാം, അല്ലെങ്കിൽ മൗസ് ഉപയോഗിച്ച് ഡ്രാഗ് ചെയ്ത് സ്ഥാനം മാറ്റാം.
                    </p>
                    <div class="flex flex-wrap gap-2 pt-2">
                        <button type="button" @click="selectCoreLayer('result_no')" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold hover:bg-slate-50">
                            # Result Number
                        </button>
                        <button type="button" @click="selectCoreLayer('competition')" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold hover:bg-slate-50">
                            Program Name
                        </button>
                        <button type="button" @click="selectCoreLayer('category')" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold hover:bg-slate-50">
                            Zone Capsule
                        </button>
                        <button type="button" @click="selectCoreLayer('winners')" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold hover:bg-slate-50">
                            Podium Winners
                        </button>
                    </div>
                </div>

            </div>

            <!-- ============================================================== -->
            <!-- TAB 2: LAYERS LIST (ALL CORE & CUSTOM LAYERS)                  -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'layers'" class="space-y-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-xs font-black uppercase tracking-wider text-slate-800">Poster Layers (ലെയറുകൾ)</h2>
                            <p class="text-[11px] text-slate-500">മുകളിലേക്ക് (Up) / താഴേക്ക് (Down) നീക്കി ലെയറുകളുടെ ഓർഡർ മാറ്റാം. Top layer sits in front.</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-mono font-bold bg-purple-50 text-purple-800 border border-purple-200">
                            <span x-text="layerStack.length"></span> Layers
                        </span>
                    </div>

                    <!-- Selected Layer Reorder Controls Banner (Shows when any layer is selected) -->
                    <template x-if="selectedId">
                        <div class="p-3 bg-gradient-to-r from-purple-950 via-slate-900 to-slate-900 rounded-xl text-white space-y-2 shadow-xs border border-purple-800">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold flex items-center gap-1.5 truncate max-w-[200px]">
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                    <span x-text="selectedLabel"></span>
                                </span>
                                <span class="text-[10px] text-purple-300 font-mono">Move Order</span>
                            </div>
                            <div class="grid grid-cols-4 gap-1.5 pt-1">
                                <button type="button" @click="bringToFront(selectedId)"
                                        :disabled="layerStack.indexOf(selectedId) <= 0"
                                        class="py-1.5 px-2 bg-white/10 hover:bg-white/20 border border-white/15 rounded-lg text-[11px] font-bold text-center transition disabled:opacity-30 disabled:pointer-events-none cursor-pointer"
                                        title="Bring to Front (ഏറ്റവും മുകളിൽ)">
                                    ⇈ Top
                                </button>
                                <button type="button" @click="moveLayerUp(selectedId)"
                                        :disabled="layerStack.indexOf(selectedId) <= 0"
                                        class="py-1.5 px-2 bg-purple-600 hover:bg-purple-500 border border-purple-400 rounded-lg text-[11px] font-bold text-center transition disabled:opacity-30 disabled:pointer-events-none cursor-pointer flex items-center justify-center gap-1"
                                        title="Move Layer Up (മുകളിലേക്ക്)">
                                    <span>▲</span> <span>Up</span>
                                </button>
                                <button type="button" @click="moveLayerDown(selectedId)"
                                        :disabled="layerStack.indexOf(selectedId) >= layerStack.length - 1"
                                        class="py-1.5 px-2 bg-purple-600 hover:bg-purple-500 border border-purple-400 rounded-lg text-[11px] font-bold text-center transition disabled:opacity-30 disabled:pointer-events-none cursor-pointer flex items-center justify-center gap-1"
                                        title="Move Layer Down (താഴേക്ക്)">
                                    <span>▼</span> <span>Down</span>
                                </button>
                                <button type="button" @click="sendToBack(selectedId)"
                                        :disabled="layerStack.indexOf(selectedId) >= layerStack.length - 1"
                                        class="py-1.5 px-2 bg-white/10 hover:bg-white/20 border border-white/15 rounded-lg text-[11px] font-bold text-center transition disabled:opacity-30 disabled:pointer-events-none cursor-pointer"
                                        title="Send to Back (ഏറ്റവും താഴെ)">
                                    ⇊ Bottom
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Unified Drag & Reorder Layer Stack -->
                    <div class="space-y-2">
                        <template x-for="(layerId, idx) in layerStack" :key="'stk_' + layerId">
                            <div class="flex items-center justify-between p-2.5 rounded-xl border transition-all cursor-pointer group select-none"
                                 :class="selectedId === layerId ? 'bg-purple-50 border-purple-400 ring-2 ring-purple-400/30 shadow-xs' : 'bg-slate-50 border-slate-200 hover:border-slate-300 hover:bg-slate-100/70'"
                                 draggable="true"
                                 @dragstart="onLayerDragStart($event, idx)"
                                 @dragover.prevent="onLayerDragOver($event, idx)"
                                 @drop="onLayerDrop($event, idx)"
                                 @click="selectLayer(layerId)">
                                
                                <!-- Left side: Index, Visibility, Label & Type -->
                                <div class="flex items-center gap-2.5 min-w-0 flex-1 mr-2">
                                    <!-- Order Index -->
                                    <span class="w-5 h-5 rounded flex items-center justify-center text-[10px] font-mono font-bold shrink-0"
                                          :class="selectedId === layerId ? 'bg-purple-600 text-white' : 'bg-slate-200 text-slate-600'"
                                          x-text="idx + 1"></span>

                                    <!-- Visibility Button -->
                                    <button type="button" @click.stop="toggleLayerVisibility(layerId)"
                                            class="p-1 rounded text-slate-400 hover:text-slate-800 transition cursor-pointer shrink-0"
                                            :class="getLayerInfo(layerId).visible ? 'text-emerald-600' : 'text-slate-300 opacity-40'"
                                            title="Toggle Visibility">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>

                                    <!-- Name and Type Badge -->
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-xs font-bold text-slate-900 truncate" x-text="getLayerInfo(layerId).name"></span>
                                            <span class="text-[9px] font-mono font-bold uppercase px-1.5 py-0.5 rounded border"
                                                  :class="getLayerInfo(layerId).badgeColor"
                                                  x-text="getLayerInfo(layerId).badge"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right side: Up / Down / Reorder Controls -->
                                <div class="flex items-center gap-1 shrink-0">
                                    <!-- Move Layer Up (മുകളിലേക്ക്) -->
                                    <button type="button" @click.stop="moveLayerUp(layerId)"
                                            :disabled="idx === 0"
                                            class="w-7 h-7 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 hover:border-slate-300 text-slate-700 font-bold flex items-center justify-center text-xs shadow-2xs disabled:opacity-25 disabled:pointer-events-none transition cursor-pointer"
                                            title="Move Up (മുകളിലേക്ക്)">
                                        ▲
                                    </button>

                                    <!-- Move Layer Down (താഴേക്ക്) -->
                                    <button type="button" @click.stop="moveLayerDown(layerId)"
                                            :disabled="idx === layerStack.length - 1"
                                            class="w-7 h-7 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 hover:border-slate-300 text-slate-700 font-bold flex items-center justify-center text-xs shadow-2xs disabled:opacity-25 disabled:pointer-events-none transition cursor-pointer"
                                            title="Move Down (താഴേക്ക്)">
                                        ▼
                                    </button>

                                    <!-- Delete (custom elements only) -->
                                    <template x-if="getLayerInfo(layerId).canDelete">
                                        <button type="button" @click.stop="deleteElement(layerId)"
                                                class="w-7 h-7 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 flex items-center justify-center text-sm font-bold transition ml-0.5 cursor-pointer"
                                                title="Delete Layer">
                                            &times;
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Bottom Quick Insertion -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>New elements default to the top.</span>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="addCustomText()" class="text-purple-700 font-bold hover:underline cursor-pointer">+ Text</button>
                            <span>&bull;</span>
                            <button type="button" @click="addCustomShape('badge')" class="text-purple-700 font-bold hover:underline cursor-pointer">+ Badge</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 3: WINNERS & SCALE                                         -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'winners'" class="space-y-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-xs font-black uppercase tracking-wider text-slate-800">Podium Master Scale & Spacing</h2>
                        <p class="text-[11px] text-slate-500">വിന്നേഴ്സ് ലിസ്റ്റും ഗ്രൂപ്പും ഒരുമിച്ച് സൈസ് മാറ്റാനും ലൈൻ സ്പേസിംഗ് ക്രമീകരിക്കാനും.</p>
                    </div>

                    <!-- Master Scale Slider -->
                    <div class="space-y-2 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                            <span>Winners Master Scale (ഒരുമിച്ച് സൈസ് മാറ്റുക)</span>
                            <span class="font-mono text-purple-700" x-text="Math.round(settings.winners_scale * 100) + '%'"></span>
                        </div>
                        <input type="range" min="0.5" max="1.8" step="0.02" x-model.number="settings.winners_scale" @input="render()" class="w-full accent-purple-600">
                    </div>

                    <!-- Medal Style Selector -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700">Medal Style (മെഡൽ തരം)</label>
                            <a href="{{ route('media.results.medals') }}" target="_blank" class="text-[10px] text-[#be1e2d] font-bold hover:underline flex items-center gap-1">
                                <span>Medals Library</span> &rarr;
                            </a>
                        </div>
                        <div class="grid grid-cols-5 gap-1.5">
                            <button type="button" @click="settings.medal_type = 'medals'; loadMedalImages(); render();" class="p-2 text-xs font-bold rounded-xl border text-center transition flex flex-col items-center justify-center gap-0.5" :class="settings.medal_type === 'medals' ? 'bg-[#be1e2d] text-white border-[#be1e2d] shadow-xs' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300'">
                                <span class="text-amber-300 text-sm leading-none">★</span>
                                <span class="text-[10px]">Medals</span>
                            </button>
                            <button type="button" @click="settings.medal_type = 'numbers'; render();" class="p-2 text-xs font-bold rounded-xl border text-center transition" :class="settings.medal_type === 'numbers' ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-white border-slate-200 text-slate-700'">01 02 03</button>
                            <button type="button" @click="settings.medal_type = 'badges'; render();" class="p-2 text-xs font-bold rounded-xl border text-center transition" :class="settings.medal_type === 'badges' ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-white border-slate-200 text-slate-700'">Badges</button>
                            <button type="button" @click="settings.medal_type = 'text'; render();" class="p-2 text-xs font-bold rounded-xl border text-center transition" :class="settings.medal_type === 'text' ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-white border-slate-200 text-slate-700'">1st 2nd</button>
                            <button type="button" @click="settings.medal_type = 'none'; render();" class="p-2 text-xs font-bold rounded-xl border text-center transition" :class="settings.medal_type === 'none' ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-white border-slate-200 text-slate-700'">None</button>
                        </div>

                        <!-- Custom Medals Options Panel -->
                        <div x-show="settings.medal_type === 'medals'" class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-2xl space-y-3">
                            <div class="flex items-center justify-between text-xs font-bold text-amber-950">
                                <span>Uploaded Medal Sets (ലഭ്യമായ മെഡലുകൾ)</span>
                                <span class="text-[10px] text-amber-800">ക്ലിക്ക് ചെയ്ത് സെലക്ട് ചെയ്യാം</span>
                            </div>

                            <!-- Medal Sets Gallery -->
                            <div class="grid grid-cols-2 gap-2">
                                <template x-for="pack in medalPacks" :key="pack.id">
                                    <div @click="selectMedalPack(pack)"
                                         class="p-2 rounded-xl border-2 cursor-pointer transition flex items-center gap-2 bg-white"
                                         :class="settings.selected_medal_pack_id === pack.id ? 'border-[#be1e2d] ring-1 ring-[#be1e2d]' : 'border-slate-200 hover:border-slate-300'">
                                        <div class="flex -space-x-1.5 overflow-hidden">
                                            <img :src="pack.first_image" alt="1st" class="w-6 h-6 object-contain">
                                            <img :src="pack.second_image" alt="2nd" class="w-6 h-6 object-contain">
                                            <img :src="pack.third_image" alt="3rd" class="w-6 h-6 object-contain">
                                        </div>
                                        <div class="truncate text-[10px] font-bold text-slate-800" x-text="pack.name"></div>
                                    </div>
                                </template>
                            </div>

                            <!-- Direct Upload Custom Medals (1st, 2nd, 3rd) -->
                            <div class="pt-2 border-t border-amber-200/80 space-y-2">
                                <div class="text-[11px] font-bold text-amber-900">Upload Your Own Medals (സ്വന്തം മെഡൽ അപ്‌ലോഡ് ചെയ്യുക)</div>
                                <div class="grid grid-cols-3 gap-2 text-center text-[10px]">
                                    <!-- 1st Medal Pick -->
                                    <label class="p-2 rounded-xl border border-dashed border-amber-300 bg-white hover:bg-amber-50 cursor-pointer flex flex-col items-center gap-1">
                                        <img :src="settings.custom_medal_1" alt="1st" class="w-8 h-8 object-contain">
                                        <span class="font-bold text-amber-800">1st Medal</span>
                                        <span class="text-[9px] text-slate-400">Change</span>
                                        <input type="file" accept="image/png,image/webp,image/svg+xml" class="hidden" @change="onLocalMedalPicked(1, $event)">
                                    </label>
                                    <!-- 2nd Medal Pick -->
                                    <label class="p-2 rounded-xl border border-dashed border-slate-300 bg-white hover:bg-slate-50 cursor-pointer flex flex-col items-center gap-1">
                                        <img :src="settings.custom_medal_2" alt="2nd" class="w-8 h-8 object-contain">
                                        <span class="font-bold text-slate-700">2nd Medal</span>
                                        <span class="text-[9px] text-slate-400">Change</span>
                                        <input type="file" accept="image/png,image/webp,image/svg+xml" class="hidden" @change="onLocalMedalPicked(2, $event)">
                                    </label>
                                    <!-- 3rd Medal Pick -->
                                    <label class="p-2 rounded-xl border border-dashed border-amber-400 bg-white hover:bg-amber-50 cursor-pointer flex flex-col items-center gap-1">
                                        <img :src="settings.custom_medal_3" alt="3rd" class="w-8 h-8 object-contain">
                                        <span class="font-bold text-amber-900">3rd Medal</span>
                                        <span class="text-[9px] text-slate-400">Change</span>
                                        <input type="file" accept="image/png,image/webp,image/svg+xml" class="hidden" @change="onLocalMedalPicked(3, $event)">
                                    </label>
                                </div>
                            </div>

                            <!-- Medal Size & Spacing Steppers -->
                            <div class="space-y-2 pt-2 border-t border-amber-200/80">
                                <!-- Medal Size -->
                                <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                                    <span>Medal Size (മെഡൽ സൈസ്)</span>
                                    <div class="flex items-center gap-1 font-mono">
                                        <button type="button" @click="settings.medal_size = Math.max(16, settings.medal_size - 4); render();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold hover:bg-slate-100 flex items-center justify-center">-</button>
                                        <span class="w-12 text-center" x-text="settings.medal_size + 'px'"></span>
                                        <button type="button" @click="settings.medal_size = Math.min(220, settings.medal_size + 4); render();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold hover:bg-slate-100 flex items-center justify-center">+</button>
                                    </div>
                                </div>
                                <input type="range" min="16" max="220" step="2" x-model.number="settings.medal_size" @input="render()" class="w-full accent-[#be1e2d]">

                                <!-- Medal Alignment & Offsets -->
                                <div class="space-y-1.5 pt-1">
                                    <div class="text-[11px] font-bold text-slate-700">Medal Alignment (മെഡൽ സ്ഥാനം)</div>
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <button type="button" @click="settings.medal_position = 'left'; render();"
                                                class="py-1 px-2 text-[11px] font-bold rounded-lg border text-center transition"
                                                :class="(settings.medal_position || 'left') === 'left' ? 'bg-[#be1e2d] text-white border-[#be1e2d] shadow-xs' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300'">
                                            Left (ഇടത് വശത്ത്)
                                        </button>
                                        <button type="button" @click="settings.medal_position = 'top'; render();"
                                                class="py-1 px-2 text-[11px] font-bold rounded-lg border text-center transition"
                                                :class="settings.medal_position === 'top' ? 'bg-[#be1e2d] text-white border-[#be1e2d] shadow-xs' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300'">
                                            Top (മുകളിൽ)
                                        </button>
                                    </div>
                                </div>

                                <!-- Medal Gap & X/Y Offsets -->
                                <div class="grid grid-cols-3 gap-2 pt-1 text-[11px] font-bold text-slate-700">
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span>Text Gap</span>
                                            <span class="font-mono text-[10px]" x-text="settings.medal_gap + 'px'"></span>
                                        </div>
                                        <input type="range" min="2" max="60" step="1" x-model.number="settings.medal_gap" @input="render()" class="w-full accent-[#be1e2d]">
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span>X-Offset</span>
                                            <span class="font-mono text-[10px]" x-text="settings.medal_x_offset + 'px'"></span>
                                        </div>
                                        <input type="range" min="-60" max="80" step="1" x-model.number="settings.medal_x_offset" @input="render()" class="w-full accent-[#be1e2d]">
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span>Y-Offset</span>
                                            <span class="font-mono text-[10px]" x-text="settings.medal_y_offset + 'px'"></span>
                                        </div>
                                        <input type="range" min="-40" max="60" step="1" x-model.number="settings.medal_y_offset" @input="render()" class="w-full accent-[#be1e2d]">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2-Line Student Name Toggle -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <div>
                            <span class="text-xs font-bold text-slate-800">2-Line Student Names</span>
                            <p class="text-[10px] text-slate-500">നീളമുള്ള പേരുകൾ രണ്ട് വരിയായി മാറ്റുക (അല്ലെങ്കിൽ പേരിനിടയിൽ / ഇടുക)</p>
                        </div>
                        <input type="checkbox" x-model="settings.winner_name_2lines" @change="render()" class="w-4 h-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                    </div>

                    <!-- Winner Text Styling & Colors (വിദ്യാർത്ഥികളുടെയും ഗ്രൂപ്പിന്റെയും നിറവും വലുപ്പവും) -->
                    <div class="space-y-4 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                            <span class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#be1e2d]"></span>
                                <span>Winner Text Colors & Styling (ടെക്സ്റ്റ് കളറുകൾ)</span>
                            </span>
                            <span class="text-[10px] font-bold text-purple-700">Student & Team Color Controls</span>
                        </div>

                        <!-- 1. Student Name Styling (വിദ്യാർത്ഥിയുടെ പേരിന്റെ നിറം) -->
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    <span>Student Name Color (വിദ്യാർത്ഥിയുടെ പേര്)</span>
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" x-model="settings.winner_name_color" @input="render()" class="w-7 h-7 rounded border border-slate-300 cursor-pointer bg-white p-0.5">
                                    <input type="text" x-model="settings.winner_name_color" @input="render()" class="w-20 font-mono text-xs text-slate-700 font-bold px-1.5 py-0.5 rounded border border-slate-300 bg-white uppercase text-center" maxlength="7" title="Custom Hex Code">
                                </div>
                            </div>

                            <!-- Swatches for Student Name -->
                            <div class="flex flex-wrap items-center gap-1.5">
                                <template x-for="c in ['#D02627', '#000000', '#FFFFFF', '#1e293b', '#be1e2d', '#eab308', '#2563eb', '#16a34a', '#7c3aed', '#ea580c']" :key="'name_c_' + c">
                                    <button type="button" @click="settings.winner_name_color = c; render();"
                                            :style="'background-color: ' + c"
                                            class="w-6 h-6 rounded-md border border-slate-300 shadow-2xs hover:scale-110 transition cursor-pointer"
                                            :class="(settings.winner_name_color || '').toLowerCase() === c.toLowerCase() ? 'ring-2 ring-[#be1e2d] ring-offset-1 scale-105' : ''"
                                            :title="c">
                                    </button>
                                </template>
                            </div>

                            <!-- Student Name Size & Weight -->
                            <div class="grid grid-cols-2 gap-2 pt-1 text-[11px]">
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between font-bold text-slate-700">
                                        <span>Size</span>
                                        <div class="flex items-center gap-0.5 font-mono">
                                            <button type="button" @click="settings.winner_name_size = Math.max(16, settings.winner_name_size - 2); render();" class="w-5 h-5 rounded bg-white border border-slate-300 font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center">-</button>
                                            <span class="w-9 text-center text-[#be1e2d] font-bold" x-text="settings.winner_name_size + 'px'"></span>
                                            <button type="button" @click="settings.winner_name_size = Math.min(80, settings.winner_name_size + 2); render();" class="w-5 h-5 rounded bg-white border border-slate-300 font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center">+</button>
                                        </div>
                                    </div>
                                    <input type="range" min="16" max="80" step="1" x-model.number="settings.winner_name_size" @input="render()" class="w-full accent-[#be1e2d]">
                                </div>
                                <div class="space-y-1">
                                    <span class="font-bold text-slate-700 block">Weight</span>
                                    <div class="grid grid-cols-3 gap-0.5">
                                        <button type="button" @click="settings.winner_name_weight = '400'; render();" class="py-1 text-[10px] font-bold rounded border text-center transition" :class="(settings.winner_name_weight || '400') === '400' ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-white border-slate-200 text-slate-700'">Normal</button>
                                        <button type="button" @click="settings.winner_name_weight = '600'; render();" class="py-1 text-[10px] font-bold rounded border text-center transition" :class="settings.winner_name_weight === '600' ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-white border-slate-200 text-slate-700'">Semi</button>
                                        <button type="button" @click="settings.winner_name_weight = '700'; render();" class="py-1 text-[10px] font-bold rounded border text-center transition" :class="settings.winner_name_weight === '700' ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-white border-slate-200 text-slate-700'">Bold</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Team / Group Name Styling (ടീം / ഗ്രൂപ്പ് പേരിന്റെ നിറം) -->
                        <div class="space-y-2.5 pt-3 border-t border-slate-200">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-700"></span>
                                    <span>Team / Group Color (ടീം / ഗ്രൂപ്പ് പേര്)</span>
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" x-model="settings.winner_unit_color" @input="render()" class="w-7 h-7 rounded border border-slate-300 cursor-pointer bg-white p-0.5">
                                    <input type="text" x-model="settings.winner_unit_color" @input="render()" class="w-20 font-mono text-xs text-slate-700 font-bold px-1.5 py-0.5 rounded border border-slate-300 bg-white uppercase text-center" maxlength="7" title="Custom Hex Code">
                                </div>
                            </div>

                            <!-- Swatches for Team / Group Name -->
                            <div class="flex flex-wrap items-center gap-1.5">
                                <template x-for="c in ['#000000', '#475569', '#D02627', '#FFFFFF', '#1e293b', '#be1e2d', '#eab308', '#2563eb', '#16a34a', '#059669']" :key="'unit_c_' + c">
                                    <button type="button" @click="settings.winner_unit_color = c; render();"
                                            :style="'background-color: ' + c"
                                            class="w-6 h-6 rounded-md border border-slate-300 shadow-2xs hover:scale-110 transition cursor-pointer"
                                            :class="(settings.winner_unit_color || '').toLowerCase() === c.toLowerCase() ? 'ring-2 ring-purple-600 ring-offset-1 scale-105' : ''"
                                            :title="c">
                                    </button>
                                </template>
                            </div>

                            <!-- Team / Group Size & Weight -->
                            <div class="grid grid-cols-2 gap-2 pt-1 text-[11px]">
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between font-bold text-slate-700">
                                        <span>Size</span>
                                        <div class="flex items-center gap-0.5 font-mono">
                                            <button type="button" @click="settings.winner_unit_size = Math.max(10, settings.winner_unit_size - 2); render();" class="w-5 h-5 rounded bg-white border border-slate-300 font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center">-</button>
                                            <span class="w-9 text-center text-purple-700 font-bold" x-text="settings.winner_unit_size + 'px'"></span>
                                            <button type="button" @click="settings.winner_unit_size = Math.min(60, settings.winner_unit_size + 2); render();" class="w-5 h-5 rounded bg-white border border-slate-300 font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center">+</button>
                                        </div>
                                    </div>
                                    <input type="range" min="10" max="60" step="1" x-model.number="settings.winner_unit_size" @input="render()" class="w-full accent-purple-600">
                                </div>
                                <div class="space-y-1">
                                    <span class="font-bold text-slate-700 block">Weight</span>
                                    <div class="grid grid-cols-3 gap-0.5">
                                        <button type="button" @click="settings.winner_unit_weight = '300'; render();" class="py-1 text-[10px] font-bold rounded border text-center transition" :class="(settings.winner_unit_weight || '300') === '300' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white border-slate-200 text-slate-700'">Light</button>
                                        <button type="button" @click="settings.winner_unit_weight = '400'; render();" class="py-1 text-[10px] font-bold rounded border text-center transition" :class="settings.winner_unit_weight === '400' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white border-slate-200 text-slate-700'">Normal</button>
                                        <button type="button" @click="settings.winner_unit_weight = '700'; render();" class="py-1 text-[10px] font-bold rounded border text-center transition" :class="settings.winner_unit_weight === '700' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white border-slate-200 text-slate-700'">Bold</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Divider Line Under Group Toggle -->
                            <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                                <div>
                                    <span class="font-bold text-slate-700 block">Divider Line Below Group</span>
                                    <span class="text-[10px] text-slate-400">ഗ്രൂപ്പിന് താഴെ വര കാണിക്കുക</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="settings.show_winner_divider" @change="render()" class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Line Spacing & Distance Controls (വരികൾ തമ്മിലുള്ള അകലം) -->
                    <div class="space-y-3 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                        <div class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center justify-between">
                            <span>Line Spacing & Distance (വരികൾ തമ്മിലുള്ള അകലം)</span>
                            <span class="text-[10px] text-purple-700 font-bold">അകലം കൂട്ടാനും കുറയ്ക്കാനും</span>
                        </div>

                        <!-- 1. Winner Name 2-line Distance -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                <span>Name 2-Line Distance (പേരുകളിലെ വരികൾ)</span>
                                <div class="flex items-center gap-1 font-mono">
                                    <button type="button" @click="settings.winner_name_line_height = Math.max(12, settings.winner_name_line_height - 3); render();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center">-</button>
                                    <span class="w-12 text-center" x-text="settings.winner_name_line_height + 'px'"></span>
                                    <button type="button" @click="settings.winner_name_line_height = Math.min(90, settings.winner_name_line_height + 3); render();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center">+</button>
                                </div>
                            </div>
                            <input type="range" min="12" max="90" step="1" x-model.number="settings.winner_name_line_height" @input="render()" class="w-full accent-[#be1e2d]">
                        </div>

                        <!-- 2. Distance from Name to Group/Team -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                <span>Name to Team Gap (പേരും ടീമും തമ്മിലുള്ള അകലം)</span>
                                <div class="flex items-center gap-1 font-mono">
                                    <button type="button" @click="settings.winner_unit_gap = Math.max(-25, (Number.isFinite(settings.winner_unit_gap) ? settings.winner_unit_gap : 2) - 2); render();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center cursor-pointer" title="അടുപ്പിക്കുക (Reduce Gap)">-</button>
                                    <span class="w-12 text-center" x-text="settings.winner_unit_gap + 'px'"></span>
                                    <button type="button" @click="settings.winner_unit_gap = Math.min(50, (Number.isFinite(settings.winner_unit_gap) ? settings.winner_unit_gap : 2) + 2); render();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center cursor-pointer" title="അകലം കൂട്ടുക (Increase Gap)">+</button>
                                </div>
                            </div>
                            <input type="range" min="-25" max="50" step="1" x-model.number="settings.winner_unit_gap" @input="render()" class="w-full accent-[#be1e2d]">
                            <p class="text-[10px] text-slate-500">ടീം നെയിം പാർട്ടിസിപ്പന്റിന്റെ പേരിനോട് കൂടുതൽ അടുപ്പിക്കാൻ കുറഞ്ഞ / മൈനസ് വാല്യൂ നൽകാം.</p>
                        </div>

                        <!-- 3. Row Gap Between 1st, 2nd, 3rd -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                <span>Winner Row Gap (സ്ഥാനങ്ങൾ തമ്മിലുള്ള അകലം)</span>
                                <div class="flex items-center gap-1 font-mono">
                                    <button type="button" @click="settings.row_gap = Math.max(60, settings.row_gap - 8); render();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center">-</button>
                                    <span class="w-12 text-center" x-text="settings.row_gap + 'px'"></span>
                                    <button type="button" @click="settings.row_gap = Math.min(350, settings.row_gap + 8); render();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center">+</button>
                                </div>
                            </div>
                            <input type="range" min="60" max="350" step="2" x-model.number="settings.row_gap" @input="render()" class="w-full accent-[#be1e2d]">
                        </div>
                    </div>

                    <!-- Editable Winner Names and Groups -->
                    <div class="space-y-3 pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800">വിജയികളുടെ പേരുകൾ (Edit Winner Names)</span>
                            <span class="text-[10px] text-slate-500">പേരും ഗ്രൂപ്പും ഇവിടെ എഡിറ്റ് ചെയ്യാം</span>
                        </div>

                        <!-- 1st Place Winners -->
                        <div class="space-y-1.5 p-3 rounded-xl bg-amber-50/70 border border-amber-200">
                            <div class="flex items-center justify-between text-xs font-bold text-amber-900">
                                <span class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded bg-amber-400 text-slate-950 font-mono text-[10px] font-black">01</span>
                                    <span>ഒന്നാം സ്ഥാനം (1st Place)</span>
                                </span>
                                <button type="button" @click="addWinner('first')" class="text-[10px] text-amber-800 font-bold hover:underline">+ Add Tie</button>
                            </div>
                            <template x-for="(w, idx) in data.winners.first" :key="'w1_' + idx">
                                <div class="space-y-1 pt-1">
                                    <div class="flex items-start gap-2">
                                        <textarea rows="2" 
                                                  x-model="w.name" 
                                                  @input="render()" 
                                                  @keydown.enter.stop="setTimeout(() => { render(); pushHistory(); }, 10)"
                                                  placeholder="വിദ്യാർത്ഥിയുടെ പേര് (Enter = 2 വരി)" 
                                                  class="flex-1 text-xs p-1.5 rounded-lg border border-slate-300 bg-white font-medium resize-y leading-snug focus:border-purple-600 focus:ring-1 focus:ring-purple-600"></textarea>
                                        <button type="button" @click="removeWinner('first', idx)" x-show="data.winners.first.length > 1" class="text-red-500 p-1 font-bold text-sm hover:bg-red-50 rounded">&times;</button>
                                    </div>
                                    <input type="text" x-model="w.unit" @input="render()" placeholder="ഗ്രൂപ്പ് / യൂണിറ്റ് (Group Name)" class="w-full text-xs p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600">
                                </div>
                            </template>
                        </div>

                        <!-- 2nd Place Winners -->
                        <div class="space-y-1.5 p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                <span class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded bg-slate-200 text-slate-800 font-mono text-[10px] font-black">02</span>
                                    <span>രണ്ടാം സ്ഥാനം (2nd Place)</span>
                                </span>
                                <button type="button" @click="addWinner('second')" class="text-[10px] text-slate-600 font-bold hover:underline">+ Add Tie</button>
                            </div>
                            <template x-for="(w, idx) in data.winners.second" :key="'w2_' + idx">
                                <div class="space-y-1 pt-1">
                                    <div class="flex items-start gap-2">
                                        <textarea rows="2" 
                                                  x-model="w.name" 
                                                  @input="render()" 
                                                  @keydown.enter.stop="setTimeout(() => { render(); pushHistory(); }, 10)"
                                                  placeholder="വിദ്യാർത്ഥിയുടെ പേര് (Enter = 2 വരി)" 
                                                  class="flex-1 text-xs p-1.5 rounded-lg border border-slate-300 bg-white font-medium resize-y leading-snug focus:border-purple-600 focus:ring-1 focus:ring-purple-600"></textarea>
                                        <button type="button" @click="removeWinner('second', idx)" x-show="data.winners.second.length > 1" class="text-red-500 p-1 font-bold text-sm hover:bg-red-50 rounded">&times;</button>
                                    </div>
                                    <input type="text" x-model="w.unit" @input="render()" placeholder="ഗ്രൂപ്പ് / യൂണിറ്റ് (Group Name)" class="w-full text-xs p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600">
                                </div>
                            </template>
                        </div>

                        <!-- 3rd Place Winners -->
                        <div class="space-y-1.5 p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                <span class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded bg-slate-200 text-slate-800 font-mono text-[10px] font-black">03</span>
                                    <span>മൂന്നാം സ്ഥാനം (3rd Place)</span>
                                </span>
                                <button type="button" @click="addWinner('third')" class="text-[10px] text-slate-600 font-bold hover:underline">+ Add Tie</button>
                            </div>
                            <template x-for="(w, idx) in data.winners.third" :key="'w3_' + idx">
                                <div class="space-y-1 pt-1">
                                    <div class="flex items-start gap-2">
                                        <textarea rows="2" 
                                                  x-model="w.name" 
                                                  @input="render()" 
                                                  @keydown.enter.stop="setTimeout(() => { render(); pushHistory(); }, 10)"
                                                  placeholder="വിദ്യാർത്ഥിയുടെ പേര് (Enter = 2 വരി)" 
                                                  class="flex-1 text-xs p-1.5 rounded-lg border border-slate-300 bg-white font-medium resize-y leading-snug focus:border-purple-600 focus:ring-1 focus:ring-purple-600"></textarea>
                                        <button type="button" @click="removeWinner('third', idx)" x-show="data.winners.third.length > 1" class="text-red-500 p-1 font-bold text-sm hover:bg-red-50 rounded">&times;</button>
                                    </div>
                                    <input type="text" x-model="w.unit" @input="render()" placeholder="ഗ്രൂപ്പ് / യൂണിറ്റ് (Group Name)" class="w-full text-xs p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 4: CORE ELEMENTS (PROGRAM, RESULT NO, ZONE)               -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'core'" class="space-y-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-xs font-black uppercase tracking-wider text-slate-800">Core Program Elements (പ്രധാന ഘടകങ്ങൾ)</h2>
                        <span class="text-[10px] text-slate-500 font-mono">Realtime Controls</span>
                    </div>

                    <!-- 1. PROGRAM NAME (മത്സരത്തിന്റെ പേര്) -->
                    <div class="space-y-3.5 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wide text-slate-800 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                                <span>Program Name (മത്സരത്തിന്റെ പേര്)</span>
                            </span>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] font-bold text-slate-600 select-none">
                                <input type="checkbox" x-model="settings.show_competition" @change="render()" class="w-4 h-4 rounded border-slate-300 text-purple-600 focus:ring-0">
                                <span>Show</span>
                            </label>
                        </div>

                        <!-- Editable Program Name Input -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-bold text-slate-600">Program Title Text (പേര് എഡിറ്റ് ചെയ്യാം)</label>
                                <span class="text-[10px] text-purple-700 font-bold bg-purple-50 px-2 py-0.5 rounded border border-purple-200">Enter = 2 വരി (Double Line)</span>
                            </div>
                            <textarea rows="2" 
                                      x-model="data.competition" 
                                      @input="render()" 
                                      @keydown.enter.stop="setTimeout(() => { render(); pushHistory(); }, 10)"
                                      placeholder="മത്സരത്തിന്റെ പേര് (Enter അടിച്ചു 2 വരി ആക്കാം)"
                                      class="w-full text-xs p-2 rounded-lg border border-slate-300 bg-white font-bold text-slate-900 focus:border-purple-600 focus:ring-1 focus:ring-purple-600 resize-y leading-snug"></textarea>
                        </div>

                        <!-- Program Name Color Picker & Quick Swatches -->
                        <div class="pt-2 border-t border-slate-200 space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                                <span>Program Name Color (നിറം മാറ്റുക)</span>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" x-model="settings.competition_color" @input="render()" class="w-7 h-7 rounded border border-slate-300 cursor-pointer bg-white p-0.5">
                                    <span class="font-mono text-xs text-slate-700 font-bold" x-text="settings.competition_color"></span>
                                </div>
                            </div>
                            <!-- Swatches -->
                            <div class="flex flex-wrap items-center gap-1.5">
                                <template x-for="c in ['#000000', '#D02627', '#be1e2d', '#FFFFFF', '#1e1b4b', '#0f172a', '#d97706', '#059669', '#2563eb', '#4b5563']" :key="'comp_c_' + c">
                                    <button type="button" @click="settings.competition_color = c; render();"
                                            :style="'background-color: ' + c"
                                            class="w-6 h-6 rounded-md border border-slate-300 shadow-2xs hover:scale-110 transition cursor-pointer"
                                            :class="settings.competition_color.toLowerCase() === c.toLowerCase() ? 'ring-2 ring-purple-600 ring-offset-1' : ''">
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Program Name Size -->
                        <div class="pt-2 border-t border-slate-200 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                                <span>Font Size (വലുപ്പം)</span>
                                <div class="flex items-center gap-1 font-mono">
                                    <button type="button" @click="settings.competition_size = Math.max(20, settings.competition_size - 4); render(); recordHistory();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold hover:bg-slate-100 flex items-center justify-center">-</button>
                                    <span class="w-12 text-center text-purple-700" x-text="settings.competition_size + 'px'"></span>
                                    <button type="button" @click="settings.competition_size = Math.min(220, settings.competition_size + 4); render(); recordHistory();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold hover:bg-slate-100 flex items-center justify-center">+</button>
                                </div>
                            </div>
                            <input type="range" min="20" max="220" step="2" x-model.number="settings.competition_size" @input="render(); recordHistory();" class="w-full accent-purple-600">
                        </div>

                        <!-- Program Font Weight (Light, Regular, Semi, Bold, Extra) -->
                        <div class="pt-2 border-t border-slate-200 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                                <span>Font Weight (ഫോണ്ട് കട്ടി / ഭാരം)</span>
                                <span class="text-[11px] font-mono text-purple-700 font-bold" x-text="settings.competition_weight === '300' ? 'Light (300)' : (settings.competition_weight === '400' ? 'Regular (400)' : (settings.competition_weight === '600' ? 'Semi-Bold (600)' : (settings.competition_weight === '700' ? 'Bold (700)' : 'Extra-Bold (800)')))"></span>
                            </div>
                            <div class="grid grid-cols-5 gap-1">
                                <button type="button" @click="settings.competition_weight = '300'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="(settings.competition_weight || '400') === '300' ? 'bg-[#be1e2d] text-white border-[#be1e2d] shadow-xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'">
                                    Light
                                </button>
                                <button type="button" @click="settings.competition_weight = '400'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="(settings.competition_weight || '400') === '400' ? 'bg-[#be1e2d] text-white border-[#be1e2d] shadow-xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'">
                                    Regular
                                </button>
                                <button type="button" @click="settings.competition_weight = '600'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="settings.competition_weight === '600' ? 'bg-[#be1e2d] text-white border-[#be1e2d] shadow-xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'">
                                    Semi
                                </button>
                                <button type="button" @click="settings.competition_weight = '700'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="settings.competition_weight === '700' ? 'bg-[#be1e2d] text-white border-[#be1e2d] shadow-xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'">
                                    Bold
                                </button>
                                <button type="button" @click="settings.competition_weight = '800'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="settings.competition_weight === '800' ? 'bg-[#be1e2d] text-white border-[#be1e2d] shadow-xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'">
                                    Extra
                                </button>
                            </div>
                        </div>

                        <!-- Program Line Distance / Line Spacing -->
                        <div class="pt-2 border-t border-slate-200 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                <span>Line Distance (വരികൾ തമ്മിലുള്ള അകലം)</span>
                                <div class="flex items-center gap-1 font-mono">
                                    <button type="button" @click="settings.competition_line_height = Math.max(15, settings.competition_line_height - 4); render();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center">-</button>
                                    <span class="w-12 text-center text-purple-700" x-text="settings.competition_line_height + 'px'"></span>
                                    <button type="button" @click="settings.competition_line_height = Math.min(180, settings.competition_line_height + 4); render();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center">+</button>
                                </div>
                            </div>
                            <input type="range" min="15" max="180" step="2" x-model.number="settings.competition_line_height" @input="render()" class="w-full accent-purple-600">
                        </div>

                        <!-- Max Width & Alignment -->
                        <div class="pt-2 border-t border-slate-200 grid grid-cols-2 gap-2 text-xs">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between font-bold text-slate-700">
                                    <span>Max Width</span>
                                    <span class="font-mono text-purple-700 text-[11px]" x-text="settings.competition_max_width + 'px'"></span>
                                </div>
                                <input type="range" min="180" max="950" step="10" x-model.number="settings.competition_max_width" @input="render()" class="w-full accent-purple-600">
                            </div>
                            <div class="space-y-1">
                                <span class="font-bold text-slate-700 block">Alignment</span>
                                <div class="grid grid-cols-3 gap-1">
                                    <button type="button" @click="settings.competition_align = 'left'; render();" class="py-1 px-1.5 text-[10px] font-bold rounded border text-center" :class="(settings.competition_align || 'left') === 'left' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white border-slate-200 text-slate-700'">Left</button>
                                    <button type="button" @click="settings.competition_align = 'center'; render();" class="py-1 px-1.5 text-[10px] font-bold rounded border text-center" :class="settings.competition_align === 'center' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white border-slate-200 text-slate-700'">Center</button>
                                    <button type="button" @click="settings.competition_align = 'right'; render();" class="py-1 px-1.5 text-[10px] font-bold rounded border text-center" :class="settings.competition_align === 'right' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white border-slate-200 text-slate-700'">Right</button>
                                </div>
                            </div>
                        </div>

                        <!-- Rotation -->
                        <div class="pt-2 border-t border-slate-200 space-y-1">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                <span>Rotation</span>
                                <span class="font-mono text-purple-700" x-text="settings.competition_rotation + '°'"></span>
                            </div>
                            <input type="range" min="-180" max="180" step="1" x-model.number="settings.competition_rotation" @input="render()" class="w-full accent-purple-600">
                        </div>
                    </div>

                    <!-- 2. ZONE CAPSULE (MIX ZONE / സോൺ കാപ്സ്യൂൾ) -->
                    <div class="space-y-3.5 bg-amber-50/60 p-4 rounded-xl border border-amber-200">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wide text-amber-950 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span>Zone Capsule (MIX ZONE / സോൺ)</span>
                            </span>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] font-bold text-amber-900 select-none">
                                <input type="checkbox" x-model="settings.show_category" @change="render()" class="w-4 h-4 rounded border-amber-300 text-amber-600 focus:ring-0">
                                <span>Show</span>
                            </label>
                        </div>

                        <!-- Editable Zone Text Input -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-amber-900">Zone Text (സോൺ ടെക്സ്റ്റ്)</label>
                            <input type="text" x-model="data.category" @input="render()" placeholder="ഉദാ: MIX ZONE, ZONE A"
                                   class="w-full text-xs p-2 rounded-lg border border-amber-300 bg-white font-bold text-slate-900 uppercase focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                        </div>

                        <!-- Quick Zone Presets -->
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-amber-800">Quick Zone Presets (ക്ലിക്ക് ചെയ്ത് മാറ്റാം):</span>
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" @click="data.category = 'MIX ZONE'; render();" class="px-2.5 py-1 rounded-md text-[10px] font-black border transition" :class="data.category.toUpperCase() === 'MIX ZONE' ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-white border-amber-300 text-amber-950 hover:bg-amber-100'">
                                    MIX ZONE
                                </button>
                                <button type="button" @click="data.category = 'ZONE A'; render();" class="px-2 py-1 rounded-md text-[10px] font-black border transition" :class="data.category.toUpperCase() === 'ZONE A' ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-white border-amber-300 text-amber-950 hover:bg-amber-100'">
                                    ZONE A
                                </button>
                                <button type="button" @click="data.category = 'ZONE B'; render();" class="px-2 py-1 rounded-md text-[10px] font-black border transition" :class="data.category.toUpperCase() === 'ZONE B' ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-white border-amber-300 text-amber-950 hover:bg-amber-100'">
                                    ZONE B
                                </button>
                                <button type="button" @click="data.category = 'ZONE C'; render();" class="px-2 py-1 rounded-md text-[10px] font-black border transition" :class="data.category.toUpperCase() === 'ZONE C' ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-white border-amber-300 text-amber-950 hover:bg-amber-100'">
                                    ZONE C
                                </button>
                                <button type="button" @click="data.category = 'GENERAL'; render();" class="px-2 py-1 rounded-md text-[10px] font-black border transition" :class="data.category.toUpperCase() === 'GENERAL' ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-white border-amber-300 text-amber-950 hover:bg-amber-100'">
                                    GENERAL
                                </button>
                                <button type="button" @click="data.category = 'ALL ZONES'; render();" class="px-2 py-1 rounded-md text-[10px] font-black border transition" :class="data.category.toUpperCase() === 'ALL ZONES' ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-white border-amber-300 text-amber-950 hover:bg-amber-100'">
                                    ALL ZONES
                                </button>
                            </div>
                        </div>

                        <!-- Zone Color Picker & Quick Swatches -->
                        <div class="pt-2 border-t border-amber-200 space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold text-amber-950">
                                <span>Zone Text Color (സോൺ നിറം)</span>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" x-model="settings.category_color" @input="render()" class="w-7 h-7 rounded border border-amber-300 cursor-pointer bg-white p-0.5">
                                    <span class="font-mono text-xs text-amber-900 font-bold" x-text="settings.category_color"></span>
                                </div>
                            </div>
                            <!-- Swatches -->
                            <div class="flex flex-wrap items-center gap-1.5">
                                <template x-for="c in ['#FFFFFF', '#000000', '#D02627', '#be1e2d', '#F59E0B', '#0EA5E9', '#10B981', '#1e1b4b', '#fde047', '#4b5563']" :key="'cat_c_' + c">
                                    <button type="button" @click="settings.category_color = c; render();"
                                            :style="'background-color: ' + c"
                                            class="w-6 h-6 rounded-md border border-slate-300 shadow-2xs hover:scale-110 transition cursor-pointer"
                                            :class="settings.category_color.toLowerCase() === c.toLowerCase() ? 'ring-2 ring-amber-600 ring-offset-1' : ''">
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Zone Font Size -->
                        <div class="pt-2 border-t border-amber-200 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold text-amber-950">
                                <span>Zone Size (വലുപ്പം)</span>
                                <div class="flex items-center gap-1 font-mono">
                                    <button type="button" @click="settings.category_size = Math.max(16, settings.category_size - 2); render(); recordHistory();" class="w-6 h-6 rounded bg-white border border-amber-300 font-bold hover:bg-amber-50 flex items-center justify-center">-</button>
                                    <span class="w-12 text-center text-amber-900" x-text="settings.category_size + 'px'"></span>
                                    <button type="button" @click="settings.category_size = Math.min(120, settings.category_size + 2); render(); recordHistory();" class="w-6 h-6 rounded bg-white border border-amber-300 font-bold hover:bg-amber-50 flex items-center justify-center">+</button>
                                </div>
                            </div>
                            <input type="range" min="16" max="120" step="1" x-model.number="settings.category_size" @input="render(); recordHistory();" class="w-full accent-amber-600">
                        </div>

                        <!-- Zone Font Weight (Light, Regular, Semi, Bold, Extra) -->
                        <div class="pt-2 border-t border-amber-200 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold text-amber-950">
                                <span>Zone Font Weight (ഫോണ്ട് കട്ടി)</span>
                                <span class="text-[11px] font-mono text-amber-900 font-bold" x-text="settings.category_weight === '300' ? 'Light (300)' : (settings.category_weight === '400' ? 'Regular (400)' : (settings.category_weight === '600' ? 'Semi (600)' : (settings.category_weight === '700' ? 'Bold (700)' : 'Extra (800)')))"></span>
                            </div>
                            <div class="grid grid-cols-5 gap-1">
                                <button type="button" @click="settings.category_weight = '300'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="(settings.category_weight || '400') === '300' ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-white border-amber-300 text-amber-950 hover:bg-amber-100'">
                                    Light
                                </button>
                                <button type="button" @click="settings.category_weight = '400'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="(settings.category_weight || '400') === '400' ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-white border-amber-300 text-amber-950 hover:bg-amber-100'">
                                    Regular
                                </button>
                                <button type="button" @click="settings.category_weight = '600'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="settings.category_weight === '600' ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-white border-amber-300 text-amber-950 hover:bg-amber-100'">
                                    Semi
                                </button>
                                <button type="button" @click="settings.category_weight = '700'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="settings.category_weight === '700' ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-white border-amber-300 text-amber-950 hover:bg-amber-100'">
                                    Bold
                                </button>
                                <button type="button" @click="settings.category_weight = '800'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="settings.category_weight === '800' ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-white border-amber-300 text-amber-950 hover:bg-amber-100'">
                                    Extra
                                </button>
                            </div>
                        </div>

                        <!-- Rotation -->
                        <div class="pt-2 border-t border-amber-200 space-y-1">
                            <div class="flex items-center justify-between text-xs font-bold text-amber-950">
                                <span>Rotation</span>
                                <span class="font-mono text-amber-900" x-text="settings.category_rotation + '°'"></span>
                            </div>
                            <input type="range" min="-180" max="180" step="1" x-model.number="settings.category_rotation" @input="render()" class="w-full accent-amber-600">
                        </div>
                    </div>

                    <!-- 3. RESULT NUMBER (#01) -->
                    <div class="space-y-3.5 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wide text-slate-800 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-red-600"></span>
                                <span>Result Number (#{{ sprintf('%02d', $resultNo) }})</span>
                            </span>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] font-bold text-slate-600 select-none">
                                <input type="checkbox" x-model="settings.show_result_no" @change="render()" class="w-4 h-4 rounded border-slate-300 text-red-600 focus:ring-0">
                                <span>Show</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-3 items-center">
                            <!-- Number Input -->
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Result Number</label>
                                <input type="text" x-model="data.result_no" @input="render()" placeholder="01"
                                       class="w-full text-xs p-2 rounded-lg border border-slate-300 bg-white font-mono font-bold text-slate-900">
                            </div>
                            <!-- Color Picker -->
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                    <span>Color</span>
                                    <span class="font-mono text-[10px]" x-text="settings.result_color"></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" x-model="settings.result_color" @input="render()" class="w-full h-8 rounded border border-slate-300 cursor-pointer bg-white p-0.5">
                                </div>
                            </div>
                        </div>

                        <!-- Quick Color Swatches for Result Number -->
                        <div class="flex flex-wrap items-center gap-1.5 pt-1">
                            <template x-for="c in ['#D02627', '#be1e2d', '#000000', '#FFFFFF', '#1e1b4b', '#d97706', '#059669', '#7c3aed']" :key="'res_c_' + c">
                                <button type="button" @click="settings.result_color = c; render();"
                                        :style="'background-color: ' + c"
                                        class="w-6 h-6 rounded-md border border-slate-300 shadow-2xs hover:scale-110 transition cursor-pointer"
                                        :class="settings.result_color.toLowerCase() === c.toLowerCase() ? 'ring-2 ring-red-600 ring-offset-1' : ''">
                                </button>
                            </template>
                        </div>

                        <!-- Font Family Selection -->
                        <div class="pt-2 border-t border-slate-200 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                                <span>Font Family (ഫോണ്ട്)</span>
                                <span class="font-mono text-[10px] text-red-600 truncate max-w-[140px]" x-text="settings.result_font || 'Rockwell Std'"></span>
                            </div>

                            <select x-model="settings.result_font" @change="onResultFontChange()" class="w-full text-xs p-2 rounded-lg border border-slate-300 bg-white font-semibold text-slate-800">
                                <optgroup label="Official Festival Fonts">
                                    <option value="Rockwell Std">Rockwell Std</option>
                                    <option value="Montserrat">Montserrat</option>
                                    <option value="Sora">Sora</option>
                                    <option value="Mongolian Baiti">Mongolian Baiti</option>
                                </optgroup>
                                <optgroup label="Popular Display Fonts">
                                    <option value="JetBrains Mono">JetBrains Mono</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Playfair Display">Playfair Display</option>
                                    <option value="Cinzel">Cinzel</option>
                                    <option value="Impact">Impact</option>
                                    <option value="Arial">Arial</option>
                                </optgroup>
                                <template x-if="availableSystemFonts && availableSystemFonts.length > 0">
                                    <optgroup label="Installed PC / System Fonts">
                                        <template x-for="sf in availableSystemFonts" :key="'sf_' + sf">
                                            <option :value="sf" x-text="sf"></option>
                                        </template>
                                    </optgroup>
                                </template>
                                <option value="__custom__">+ Custom / System Font (മറ്റൊരു ഫോണ്ട് ടൈപ്പ് ചെയ്യുക)...</option>
                            </select>

                            <!-- Custom / System Font input when selected or custom font in use -->
                            <div x-show="settings.result_font === '__custom__' || isCustomFontName" class="space-y-1 pt-1">
                                <div class="text-[10px] text-slate-500 font-medium">Type any font installed on your computer (e.g. Century Gothic, Segoe UI, Tahoma, Trebuchet MS):</div>
                                <div class="flex items-center gap-1.5">
                                    <input type="text" x-model="customFontInput" @input="applyCustomFont()" placeholder="Type font name..." class="w-full text-xs p-1.5 rounded-lg border border-slate-300 bg-white font-medium">
                                    <button type="button" @click="applyCustomFont()" class="px-2.5 py-1.5 bg-slate-800 text-white text-[11px] font-bold rounded-lg hover:bg-slate-700">Apply</button>
                                </div>
                            </div>

                            <!-- System Font Detector Button -->
                            <div class="flex items-center justify-between pt-1">
                                <button type="button" @click="loadSystemFonts()" :disabled="systemFontsLoading" class="text-[10px] font-bold text-indigo-700 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 px-2 py-1 rounded-md flex items-center gap-1 transition cursor-pointer">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                    <span x-text="systemFontsLoading ? 'Scanning PC...' : (availableSystemFonts.length > 0 ? '✓ ' + availableSystemFonts.length + ' PC Fonts Loaded' : 'Detect PC / System Fonts')"></span>
                                </button>
                                <span class="text-[10px] text-slate-400 font-mono" x-show="availableSystemFonts.length > 0" x-text="availableSystemFonts.length + ' fonts'"></span>
                            </div>
                        </div>

                        <!-- Font Weight Selection -->
                        <div class="pt-2 border-t border-slate-200 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                                <span>Font Weight (ഫോണ്ട് കട്ടി)</span>
                                <span class="text-[11px] font-mono text-red-600 font-bold" x-text="getWeightLabel(settings.result_weight)"></span>
                            </div>
                            <div class="grid grid-cols-4 sm:grid-cols-7 gap-1">
                                <button type="button" @click="settings.result_weight = '300'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="(settings.result_weight || '400') === '300' ? 'bg-red-600 text-white border-red-600 shadow-2xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-100'">
                                    Light
                                </button>
                                <button type="button" @click="settings.result_weight = '400'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="(settings.result_weight || '400') === '400' ? 'bg-red-600 text-white border-red-600 shadow-2xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-100'">
                                    Regular
                                </button>
                                <button type="button" @click="settings.result_weight = '500'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="(settings.result_weight || '400') === '500' ? 'bg-red-600 text-white border-red-600 shadow-2xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-100'">
                                    Med
                                </button>
                                <button type="button" @click="settings.result_weight = '600'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="(settings.result_weight || '400') === '600' ? 'bg-red-600 text-white border-red-600 shadow-2xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-100'">
                                    Semi
                                </button>
                                <button type="button" @click="settings.result_weight = '700'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="(settings.result_weight || '400') === '700' ? 'bg-red-600 text-white border-red-600 shadow-2xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-100'">
                                    Bold
                                </button>
                                <button type="button" @click="settings.result_weight = '800'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="(settings.result_weight || '400') === '800' ? 'bg-red-600 text-white border-red-600 shadow-2xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-100'">
                                    Extra
                                </button>
                                <button type="button" @click="settings.result_weight = '900'; render(); recordHistory();"
                                        class="py-1 px-1 text-[10px] font-bold rounded-lg border text-center transition cursor-pointer"
                                        :class="(settings.result_weight || '400') === '900' ? 'bg-red-600 text-white border-red-600 shadow-2xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-100'">
                                    Black
                                </button>
                            </div>
                        </div>

                        <!-- Result Number Size -->
                        <div class="pt-2 border-t border-slate-200 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                                <span>Number Size</span>
                                <div class="flex items-center gap-1 font-mono">
                                    <button type="button" @click="settings.result_size = Math.max(80, settings.result_size - 10); render();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold hover:bg-slate-100 flex items-center justify-center">-</button>
                                    <span class="w-14 text-center text-red-600" x-text="settings.result_size + 'px'"></span>
                                    <button type="button" @click="settings.result_size = Math.min(800, settings.result_size + 10); render();" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold hover:bg-slate-100 flex items-center justify-center">+</button>
                                </div>
                            </div>
                            <input type="range" min="80" max="800" step="5" x-model.number="settings.result_size" @input="render()" class="w-full accent-red-600">
                        </div>

                        <!-- Rotation -->
                        <div class="pt-2 border-t border-slate-200 space-y-1">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                <span>Rotation</span>
                                <span class="font-mono text-red-600" x-text="settings.result_rotation + '°'"></span>
                            </div>
                            <input type="range" min="-180" max="180" step="1" x-model.number="settings.result_rotation" @input="render()" class="w-full accent-red-600">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 5: GUIDELINES (CYAN ILLUSTRATOR GUIDES)                    -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'guides'" class="space-y-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-xs font-black uppercase tracking-wider text-slate-800">Illustrator Cyan Guidelines</h2>
                        <button type="button" @click="guidelines.show = !guidelines.show; render();" class="px-2.5 py-1 rounded text-xs font-bold bg-cyan-100 text-cyan-800">
                            <span x-text="guidelines.show ? 'Enabled' : 'Disabled'"></span>
                        </button>
                    </div>

                    <!-- Vertical Guides (X) -->
                    <div class="space-y-2">
                        <span class="text-xs font-bold text-slate-700">Vertical Guides (X)</span>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="(gx, idx) in guidelines.vertical" :key="'vx_' + idx">
                                <span class="px-2.5 py-1 rounded-lg bg-cyan-50 border border-cyan-300 text-cyan-900 font-mono text-xs flex items-center gap-1.5">
                                    <span x-text="'X: ' + gx + 'px'"></span>
                                    <button type="button" @click="removeGuide('v', idx)" class="hover:text-red-600">&times;</button>
                                </span>
                            </template>
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <input type="number" x-model.number="guidelines.newX" class="w-24 text-xs p-1.5 rounded-lg border border-slate-300 font-mono" placeholder="X px">
                            <button type="button" @click="addGuide('v')" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white font-bold text-xs">+ Add X Guide</button>
                        </div>
                    </div>

                    <!-- Horizontal Guides (Y) -->
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <span class="text-xs font-bold text-slate-700">Horizontal Guides (Y)</span>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="(gy, idx) in guidelines.horizontal" :key="'hy_' + idx">
                                <span class="px-2.5 py-1 rounded-lg bg-cyan-50 border border-cyan-300 text-cyan-900 font-mono text-xs flex items-center gap-1.5">
                                    <span x-text="'Y: ' + gy + 'px'"></span>
                                    <button type="button" @click="removeGuide('h', idx)" class="hover:text-red-600">&times;</button>
                                </span>
                            </template>
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <input type="number" x-model.number="guidelines.newY" class="w-24 text-xs p-1.5 rounded-lg border border-slate-300 font-mono" placeholder="Y px">
                            <button type="button" @click="addGuide('h')" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white font-bold text-xs">+ Add Y Guide</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 6: TEMPLATES SELECTOR                                      -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'templates'" class="space-y-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-800">Select Template</h2>
                    <div class="grid grid-cols-2 gap-3 max-h-96 overflow-y-auto">
                        @foreach($templates as $tmpl)
                            <div @click="selectTemplate({{ $tmpl->id }}, '{{ asset($tmpl->image_path) }}', '{{ $tmpl->posterSetting?->layout_mode ?? "default" }}')"
                                 class="p-2 rounded-xl border-2 cursor-pointer transition-all flex flex-col justify-between"
                                 :class="settings.template_id === {{ $tmpl->id }} ? 'border-[#be1e2d] bg-red-50/20' : 'border-slate-200 hover:border-slate-300'">
                                <img src="{{ asset($tmpl->image_path) }}" alt="{{ $tmpl->name }}" class="w-full aspect-[4/5] object-cover rounded-lg shadow-2xs">
                                <span class="text-[11px] font-bold text-slate-800 truncate mt-1.5">{{ $tmpl->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        <!-- ============================================================== -->
        <!-- CANVAS STUDIO PREVIEW (RIGHT COLUMN - 7 COLS)                 -->
        <!-- ============================================================== -->
        <div class="lg:col-span-7 space-y-3">
            
            <!-- Quick Floating Canvas Action Ribbon -->
            <div class="bg-slate-900 text-white px-4 py-2.5 rounded-2xl border border-slate-800 flex flex-wrap items-center justify-between gap-3 shadow-md">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span class="text-xs font-mono font-bold">Interactive Studio</span>
                    <span class="text-slate-400">&bull;</span>
                    <span class="text-xs text-amber-300 font-bold" x-text="selectedLabel"></span>
                </div>

                <div class="flex items-center gap-1.5 flex-wrap">
                    <!-- Undo & Redo Canvas Buttons -->
                    <div class="flex items-center gap-1 bg-white/10 p-0.5 rounded-lg border border-white/15 mr-1 font-mono text-xs">
                        <button type="button" @click="undo()" :disabled="historyIndex <= 0"
                                title="Undo (Ctrl+Z) - മുൻപത്തെ അവസ്ഥ"
                                class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-amber-300 font-bold disabled:opacity-30 disabled:pointer-events-none transition cursor-pointer flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2m0 0l-4-4m4 4l4-4M3 10l4-4m-4 4l4 4"></path></svg>
                            <span>Undo</span>
                            <span class="text-[9px] text-slate-400 hidden sm:inline">Ctrl+Z</span>
                        </button>
                        <button type="button" @click="redo()" :disabled="historyIndex >= history.length - 1"
                                title="Redo (Ctrl+Y / Ctrl+Shift+Z) - തിരിച്ചെടുക്കുക"
                                class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-amber-300 font-bold disabled:opacity-30 disabled:pointer-events-none transition cursor-pointer flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2m0 0l4-4m-4 4l-4-4m15-6l-4-4m4 4l-4 4"></path></svg>
                            <span>Redo</span>
                            <span class="text-[9px] text-slate-400 hidden sm:inline">Ctrl+Y</span>
                        </button>
                    </div>

                    <!-- Layer Stacking Order Controls for Selected Layer -->
                    <template x-if="selectedId">
                        <div class="flex items-center gap-1 bg-white/10 p-0.5 rounded-lg border border-white/15 mr-1 font-mono text-xs">
                            <button type="button" @click="moveLayerUp(selectedId)"
                                    :disabled="layerStack.indexOf(selectedId) <= 0"
                                    title="Move Layer Up (മുകളിലേക്ക്)"
                                    class="px-2 py-1 rounded bg-purple-600 hover:bg-purple-500 text-white font-bold disabled:opacity-30 disabled:pointer-events-none transition cursor-pointer">
                                ▲ Up
                            </button>
                            <button type="button" @click="moveLayerDown(selectedId)"
                                    :disabled="layerStack.indexOf(selectedId) >= layerStack.length - 1"
                                    title="Move Layer Down (താഴേക്ക്)"
                                    class="px-2 py-1 rounded bg-purple-600 hover:bg-purple-500 text-white font-bold disabled:opacity-30 disabled:pointer-events-none transition cursor-pointer">
                                ▼ Down
                            </button>
                            <button type="button" @click="bringToFront(selectedId)"
                                    :disabled="layerStack.indexOf(selectedId) <= 0"
                                    title="Bring to Front (ഏറ്റവും മുകളിൽ)"
                                    class="px-1.5 py-1 rounded hover:bg-white/20 font-bold disabled:opacity-30 disabled:pointer-events-none transition cursor-pointer">
                                ⇈ Top
                            </button>
                            <button type="button" @click="sendToBack(selectedId)"
                                    :disabled="layerStack.indexOf(selectedId) >= layerStack.length - 1"
                                    title="Send to Back (ഏറ്റവും താഴെ)"
                                    class="px-1.5 py-1 rounded hover:bg-white/20 font-bold disabled:opacity-30 disabled:pointer-events-none transition cursor-pointer">
                                ⇊ Bottom
                            </button>
                        </div>
                    </template>

                    <button type="button" @click="addCustomText()" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold flex items-center gap-1 cursor-pointer">+ Text</button>
                    <button type="button" @click="addCustomShape('badge')" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold flex items-center gap-1 cursor-pointer">+ Badge</button>
                    <button type="button" @click="duplicateSelected()" :disabled="!selectedElement" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold disabled:opacity-40 cursor-pointer">Duplicate</button>
                    <button type="button" @click="deleteSelected()" :disabled="!selectedElement" class="px-2 py-1 rounded-lg bg-red-500/20 hover:bg-red-500/40 text-red-300 text-xs font-bold disabled:opacity-40 cursor-pointer">&times; Delete</button>
                </div>
            </div>

            <!-- Canvas Container with Interactive Mouse / Touch Dragging -->
            <div class="bg-slate-950 p-3 sm:p-5 rounded-2xl border border-slate-800 shadow-xl flex items-center justify-center relative overflow-hidden select-none">
                <canvas id="posterCanvas" width="1080" height="1350"
                        @mousedown="onMouseDown($event)"
                        @mousemove="onMouseMove($event)"
                        @mouseup="onMouseUp($event)"
                        @mouseleave="onMouseUp($event)"
                        @touchstart="onTouchStart($event)"
                        @touchmove="onTouchMove($event)"
                        @touchend="onTouchEnd($event)"
                        class="w-full max-w-[540px] aspect-[4/5] h-auto object-contain rounded-xl shadow-2xl cursor-crosshair bg-slate-900 border border-slate-800"></canvas>
            </div>

            <!-- Bottom Control Bar -->
            <div class="flex items-center justify-between text-xs text-slate-500 px-2 font-mono">
                <span>Canvas Size: 1080 x 1350 px (High-Res 300 DPI)</span>
                <span>Click & drag any element on the canvas to move</span>
            </div>

        </div>

    </div>

    <!-- Studio Upload Ready Poster Modal -->
    <div x-show="showStudioUploadModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;"
         @keydown.escape.window="showStudioUploadModal = false">

        <div @click.away="showStudioUploadModal = false" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-3xl max-w-lg w-full border border-slate-200 shadow-2xl overflow-hidden font-sora my-8">

            <form action="{{ !empty($result->id) ? route('media.results.save-poster', $result->id) : '#' }}" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  @submit="isStudioUploading = true">
                @csrf

                <!-- Header -->
                <div class="p-5 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-white">Upload Finished Poster</h3>
                            <p class="text-[11px] text-slate-300">ഫോട്ടോഷോപ്പ് / കാൻവ ഡിസൈൻ ചെയ്ത പോസ്റ്റർ നേരിട്ട് അപ്‌ലോഡ് ചെയ്യുക</p>
                        </div>
                    </div>
                    <button type="button" @click="showStudioUploadModal = false" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-white/10 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-5 space-y-4">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono">Target Result</div>
                        <div class="text-xs font-bold text-slate-900 mt-0.5">
                            #{{ !empty($resultNo) ? sprintf("%02d", $resultNo) : ($result->id ?? "01") }} • {{ $result->program->name ?? 'Program' }} ({{ $result->program->zone?->name ?? $result->program->eligibility ?? 'All Zones' }})
                        </div>
                    </div>

                    <!-- Drop Box -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Poster Image File
                        </label>
                        <div @dragover.prevent="isStudioDragging = true"
                             @dragleave.prevent="isStudioDragging = false"
                             @drop.prevent="isStudioDragging = false; const f = $event.dataTransfer.files[0]; if (f) { const el = document.getElementById('studioFileInput'); if (el) { const dt = new DataTransfer(); dt.items.add(f); el.files = dt.files; } handleStudioFile(f); }"
                             :class="isStudioDragging ? 'border-indigo-500 bg-indigo-50/50' : 'border-slate-300 hover:border-slate-400 bg-slate-50/50'"
                             class="relative border-2 border-dashed rounded-2xl p-5 text-center cursor-pointer transition-all">

                            <input type="file" 
                                   id="studioFileInput"
                                   name="poster_file" 
                                   accept="image/png,image/jpeg,image/webp" 
                                   required
                                   @change="handleStudioFile($event.target.files[0])"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                            <div x-show="!studioUploadPreview" class="space-y-1.5">
                                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 mx-auto flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                </div>
                                <div class="text-xs font-bold text-slate-800">
                                    Drag & drop finished poster image here
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    or <span class="text-indigo-600 font-bold underline">browse file</span>
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">PNG, JPG, WEBP (Max 10MB)</div>
                            </div>

                            <div x-show="studioUploadPreview" class="space-y-2" style="display: none;">
                                <div class="relative max-w-[200px] mx-auto rounded-lg overflow-hidden shadow border border-slate-200 aspect-[4/5] bg-slate-900">
                                    <img :src="studioUploadPreview" alt="Preview" class="w-full h-full object-contain">
                                </div>
                                <div class="text-xs font-bold text-slate-800 truncate" x-text="studioUploadFileName"></div>
                                <div class="text-[10px] text-slate-500 font-mono" x-text="studioUploadFileSize"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Publish checkbox -->
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-2.5">
                        <input type="hidden" name="publish_now" value="0">
                        <input type="checkbox" name="publish_now" id="studioPublishCheck" value="1" checked class="w-4 h-4 mt-0.5 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="studioPublishCheck" class="text-xs text-emerald-900 cursor-pointer">
                            <span class="font-bold">Publish immediately to public website</span>
                            <span class="block text-[11px] text-emerald-700 mt-0.5">തത്സമയം പബ്ലിക് വെബ്സൈറ്റിൽ പബ്ലിഷ് ചെയ്യുക</span>
                        </label>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showStudioUploadModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="isStudioUploading || !studioUploadPreview"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs transition flex items-center gap-2 shadow-md disabled:opacity-50 cursor-pointer">
                        <svg x-show="!isStudioUploading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span x-text="isStudioUploading ? 'Uploading...' : 'Upload & Publish Poster'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function posterStudio() {
    return {
        activeTab: 'design',
        selectedId: null,
        selectedType: null, // 'custom', 'core'
        isDragging: false,
        dragStartX: 0,
        dragStartY: 0,
        elementStartX: 0,
        elementStartY: 0,

        // Transform Gizmo state
        isResizing: false,
        activeHandle: null,
        elementStartW: 0,
        elementStartH: 0,

        showStudioUploadModal: false,
        studioUploadPreview: null,
        studioUploadFileName: '',
        studioUploadFileSize: '',
        isStudioUploading: false,
        isStudioDragging: false,

        handleStudioFile(file) {
            if (!file) return;
            if (!file.type.match('image.*')) {
                alert('Please select an image file (PNG, JPG, WEBP).');
                return;
            }
            this.studioUploadFileName = file.name;
            this.studioUploadFileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            const reader = new FileReader();
            reader.onload = (e) => {
                this.studioUploadPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },
        elementStartSize: 0,
        elementStartScale: 1.0,
        elementStartRotation: 0,

        // History for Undo (Ctrl+Z) & Redo (Ctrl+Y)
        history: [],
        historyIndex: -1,
        isApplyingHistory: false,
        hasMutated: false,
        historyDebounceTimer: null,

        savingDefault: false,
        publishing: false,
        statusMsg: '',
        statusType: 'success',

        templatesMap: @json($templatesMap ?? []),
        templateImageObj: null,

        // System & Custom Fonts
        availableSystemFonts: [],
        systemFontsLoading: false,
        customFontInput: '',
        isCustomFontName: false,

        // Medal Library
        medalPacks: @json($medalPacks ?? []),
        medalImageObjs: { 1: null, 2: null, 3: null },
        uploadingMedal: false,

        // Cyan Guidelines
        guidelines: {
            show: {{ isset($currentSettings['guidelines']['show']) ? ($currentSettings['guidelines']['show'] ? 'true' : 'false') : 'true' }},
            vertical: @json($currentSettings['guidelines']['vertical'] ?? [205, 540, 740]),
            horizontal: @json($currentSettings['guidelines']['horizontal'] ?? [145, 165, 779]),
            newX: {{ (int) ($currentSettings['guidelines']['newX'] ?? 205) }},
            newY: {{ (int) ($currentSettings['guidelines']['newY'] ?? 200) }}
        },

        // Dynamic Custom Elements Array
        customElements: [],

        // Unified Layer Stacking Order (Index 0 = Frontmost/Top, Index N-1 = Backmost/Bottom)
        layerStack: [],
        draggedLayerIdx: null,

        // Core Settings
        settings: {
            template_id: {{ $activeTemplate ? $activeTemplate->id : 1 }},
            template_image: '{{ $activeTemplate ? asset($activeTemplate->image_path) : asset("/images/result-templates/1778705272_srt1-01.png") }}',
            layout_mode: '{{ $currentSettings["layout_mode"] ?? "default" }}',

            // Visibility
            show_result_no: {{ isset($currentSettings['show_result_no']) ? ($currentSettings['show_result_no'] ? 'true' : 'false') : 'true' }},
            show_competition: {{ isset($currentSettings['show_competition']) ? ($currentSettings['show_competition'] ? 'true' : 'false') : 'true' }},
            show_category: {{ isset($currentSettings['show_category']) ? ($currentSettings['show_category'] ? 'true' : 'false') : 'true' }},
            show_winners: {{ isset($currentSettings['show_winners']) ? ($currentSettings['show_winners'] ? 'true' : 'false') : 'true' }},
            show_winner_divider: {{ !empty($currentSettings['show_winner_divider']) ? 'true' : 'false' }},

            // Rotations
            result_rotation: {{ (float) ($currentSettings['result_rotation'] ?? 0) }},
            competition_rotation: {{ (float) ($currentSettings['competition_rotation'] ?? 0) }},
            category_rotation: {{ (float) ($currentSettings['category_rotation'] ?? 0) }},

            // Master Winner Scaling & Spacing
            winners_scale: {{ (float) ($currentSettings['winners_scale'] ?? 1.0) }},
            winner_name_2lines: {{ isset($currentSettings['winner_name_2lines']) ? ($currentSettings['winner_name_2lines'] ? 'true' : 'false') : 'true' }},
            winner_name_line_height: {{ (float) ($currentSettings['winner_name_line_height'] ?? 38) }},
            winner_unit_gap: {{ (float) ($currentSettings['winner_unit_gap'] ?? 2) }},
            row_gap: {{ (float) ($currentSettings['row_gap'] ?? 178) }},

            // Medal System
            medal_type: '{{ $currentSettings["medal_type"] ?? "medals" }}', // 'medals', 'numbers', 'badges', 'text', 'none'
            medal_position: '{{ $currentSettings["medal_position"] ?? "left" }}', // 'left', 'top'
            selected_medal_pack_id: {{ $currentSettings['selected_medal_pack_id'] ?? (optional($medalPacks->first())->id ?? 1) }},
            medal_size: {{ (float) ($currentSettings['medal_size'] ?? 52) }},
            medal_x_offset: {{ (float) ($currentSettings['medal_x_offset'] ?? 0) }},
            medal_y_offset: {{ (float) ($currentSettings['medal_y_offset'] ?? 0) }},
            medal_gap: {{ (float) ($currentSettings['medal_gap'] ?? 16) }},
            custom_medal_1: '{{ $currentSettings["custom_medal_1"] ?? (optional($medalPacks->first())->first_image ? asset($medalPacks->first()->first_image) : asset("/images/medals/first.png")) }}',
            custom_medal_2: '{{ $currentSettings["custom_medal_2"] ?? (optional($medalPacks->first())->second_image ? asset($medalPacks->first()->second_image) : asset("/images/medals/second.png")) }}',
            custom_medal_3: '{{ $currentSettings["custom_medal_3"] ?? (optional($medalPacks->first())->third_image ? asset($medalPacks->first()->third_image) : asset("/images/medals/third.png")) }}',

            result_x: {{ (float) ($currentSettings['result_x'] ?? 740) }},
            result_y: {{ (float) ($currentSettings['result_y'] ?? 779) }},
            result_size: {{ (float) ($currentSettings['result_size'] ?? 480) }},
            result_weight: '{{ $currentSettings["result_weight"] ?? "400" }}',
            result_font: '{{ $currentSettings["result_font"] ?? "Rockwell Std" }}',
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

            @php
                $formatHex = function ($val, $fallback) {
                    $val = trim((string) $val);
                    if ($val === '') return $fallback;
                    if (strtolower($val) === 'white') return '#ffffff';
                    if (strtolower($val) === 'black') return '#000000';
                    if (!str_starts_with($val, '#') && strlen($val) === 6) $val = '#' . $val;
                    return (strlen($val) === 7 && preg_match('/^#[0-9a-fA-F]{6}$/', $val)) ? $val : $fallback;
                };
                $cleanWinnerNameColor = $formatHex($currentSettings['winner_name_color'] ?? '', '#D02627');
                $cleanWinnerUnitColor = $formatHex($currentSettings['winner_unit_color'] ?? '', '#000000');
            @endphp
            winner_name_size: {{ (float) ($currentSettings['winner_name_size'] ?? 34) }},
            winner_name_weight: '{{ $currentSettings["winner_name_weight"] ?? "400" }}',
            winner_name_color: '{{ $cleanWinnerNameColor }}',

            winner_unit_size: {{ (float) ($currentSettings['winner_unit_size'] ?? 26) }},
            winner_unit_weight: '{{ $currentSettings["winner_unit_weight"] ?? "300" }}',
            winner_unit_color: '{{ $cleanWinnerUnitColor }}',
            winner_font: '{{ $currentSettings["winner_font"] ?? "Rockwell Std" }}',
        },

        // Content Data
        data: {
            result_no: '{{ !empty($resultNo) ? sprintf("%02d", $resultNo) : ($result->id ?? "01") }}',
            category: '{{ \App\Services\PointCalculationService::toTitleCase($result->program->zone?->name ?? $result->program->eligibility ?? "All Zones") }}',
            competition: '{{ \App\Services\PointCalculationService::toTitleCase($result->program->name) }}',
            winners: {
                first: @json(!empty($winners['first']) ? array_map(fn($w) => ['name' => \App\Services\PointCalculationService::toTitleCase($w['name'] ?? ''), 'unit' => \App\Services\PointCalculationService::toTitleCase($w['unit'] ?? '')], array_values($winners['first'])) : [['name' => 'Muhammed Yaseen P.', 'unit' => 'Red Fox Team']]),
                second: @json(!empty($winners['second']) ? array_map(fn($w) => ['name' => \App\Services\PointCalculationService::toTitleCase($w['name'] ?? ''), 'unit' => \App\Services\PointCalculationService::toTitleCase($w['unit'] ?? '')], array_values($winners['second'])) : [['name' => 'Ahmad Rayyan K.', 'unit' => 'Green Warriors']]),
                third: @json(!empty($winners['third']) ? array_map(fn($w) => ['name' => \App\Services\PointCalculationService::toTitleCase($w['name'] ?? ''), 'unit' => \App\Services\PointCalculationService::toTitleCase($w['unit'] ?? '')], array_values($winners['third'])) : [['name' => 'Bilal Muhammed', 'unit' => 'Blue Riders']])
            }
        },

        addWinner(rank) {
            if (!this.data.winners[rank]) {
                this.data.winners[rank] = [];
            }
            this.data.winners[rank].push({ name: '', unit: '' });
            this.$nextTick(() => { this.render(); this.pushHistory(); });
        },

        removeWinner(rank, index) {
            if (this.data.winners[rank] && this.data.winners[rank].length > 1) {
                this.data.winners[rank].splice(index, 1);
                this.$nextTick(() => { this.render(); this.pushHistory(); });
            }
        },

        get selectedElement() {
            if (this.selectedType === 'custom') {
                return this.customElements.find(el => el.id === this.selectedId) || null;
            }
            return null;
        },

        get selectedLabel() {
            if (this.selectedType === 'custom') {
                const el = this.selectedElement;
                return el ? el.label : 'None';
            }
            if (this.selectedType === 'core') {
                const coreLabels = {
                    'result_no': 'Result Number',
                    'competition': 'Program Name',
                    'category': 'Zone Capsule',
                    'winners': 'Podium Winners'
                };
                return coreLabels[this.selectedId] || this.selectedId;
            }
            return 'None (Click canvas to select)';
        },

        syncLayerStack() {
            const coreIds = ['category', 'competition', 'result_no', 'winners'];
            const customIds = this.customElements.map(e => e.id);
            const validIds = new Set([...coreIds, ...customIds]);

            // Filter out invalid/removed IDs
            this.layerStack = (this.layerStack || []).filter(id => validIds.has(id));

            // Ensure all customIds are in stack (add new ones at top)
            customIds.forEach(id => {
                if (!this.layerStack.includes(id)) {
                    this.layerStack.unshift(id);
                }
            });

            // Ensure all coreIds are in stack
            coreIds.forEach(id => {
                if (!this.layerStack.includes(id)) {
                    this.layerStack.push(id);
                }
            });
        },

        getLayerInfo(layerId) {
            if (layerId === 'result_no') {
                return {
                    name: 'Result Number (' + (this.data.result_no || '') + ')',
                    badge: 'CORE',
                    badgeColor: 'bg-rose-50 text-rose-700 border-rose-200',
                    visible: !!this.settings.show_result_no,
                    canDelete: false
                };
            }
            if (layerId === 'competition') {
                return {
                    name: 'Program Name (' + (this.data.competition || '') + ')',
                    badge: 'CORE',
                    badgeColor: 'bg-blue-50 text-blue-700 border-blue-200',
                    visible: !!this.settings.show_competition,
                    canDelete: false
                };
            }
            if (layerId === 'category') {
                return {
                    name: 'Zone Capsule (' + (this.data.category || '') + ')',
                    badge: 'CORE',
                    badgeColor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    visible: !!this.settings.show_category,
                    canDelete: false
                };
            }
            if (layerId === 'winners') {
                return {
                    name: 'Podium Winners (1st, 2nd, 3rd)',
                    badge: 'PODIUM',
                    badgeColor: 'bg-amber-50 text-amber-700 border-amber-200',
                    visible: !!this.settings.show_winners,
                    canDelete: false
                };
            }

            const el = this.customElements.find(e => e.id === layerId);
            if (!el) {
                return {
                    name: layerId,
                    badge: 'CUSTOM',
                    badgeColor: 'bg-slate-50 text-slate-700 border-slate-200',
                    visible: true,
                    canDelete: true
                };
            }

            let badge = (el.type || 'TEXT').toUpperCase();
            let badgeColor = 'bg-purple-50 text-purple-700 border-purple-200';
            if (el.type === 'shape') {
                badge = (el.shapeType || 'SHAPE').toUpperCase();
                badgeColor = 'bg-amber-50 text-amber-700 border-amber-200';
            } else if (el.type === 'image') {
                badge = 'IMAGE';
                badgeColor = 'bg-cyan-50 text-cyan-700 border-cyan-200';
            }

            return {
                name: el.label || el.text || el.id,
                badge: badge,
                badgeColor: badgeColor,
                visible: el.visible !== false,
                canDelete: true
            };
        },

        moveLayerUp(id) {
            const idx = this.layerStack.indexOf(id);
            if (idx > 0) {
                const temp = this.layerStack[idx];
                this.layerStack[idx] = this.layerStack[idx - 1];
                this.layerStack[idx - 1] = temp;
                this.render();
                this.pushHistory();
            }
        },

        moveLayerDown(id) {
            const idx = this.layerStack.indexOf(id);
            if (idx >= 0 && idx < this.layerStack.length - 1) {
                const temp = this.layerStack[idx];
                this.layerStack[idx] = this.layerStack[idx + 1];
                this.layerStack[idx + 1] = temp;
                this.render();
                this.pushHistory();
            }
        },

        bringToFront(id) {
            const idx = this.layerStack.indexOf(id);
            if (idx > 0) {
                const [item] = this.layerStack.splice(idx, 1);
                this.layerStack.unshift(item);
                this.render();
                this.pushHistory();
            }
        },

        sendToBack(id) {
            const idx = this.layerStack.indexOf(id);
            if (idx >= 0 && idx < this.layerStack.length - 1) {
                const [item] = this.layerStack.splice(idx, 1);
                this.layerStack.push(item);
                this.render();
                this.pushHistory();
            }
        },

        selectLayer(id) {
            if (['result_no', 'competition', 'category', 'winners'].includes(id)) {
                this.selectCoreLayer(id);
            } else {
                this.selectElement(id);
            }
        },

        toggleLayerVisibility(id) {
            if (id === 'result_no') {
                this.settings.show_result_no = !this.settings.show_result_no;
            } else if (id === 'competition') {
                this.settings.show_competition = !this.settings.show_competition;
            } else if (id === 'category') {
                this.settings.show_category = !this.settings.show_category;
            } else if (id === 'winners') {
                this.settings.show_winners = !this.settings.show_winners;
            } else {
                const el = this.customElements.find(e => e.id === id);
                if (el) {
                    el.visible = (el.visible === false) ? true : false;
                }
            }
            this.render();
            this.pushHistory();
        },

        onLayerDragStart(e, idx) {
            this.draggedLayerIdx = idx;
            if (e.dataTransfer) {
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', idx);
            }
        },

        onLayerDragOver(e, idx) {
            e.preventDefault();
            if (e.dataTransfer) {
                e.dataTransfer.dropEffect = 'move';
            }
        },

        onLayerDrop(e, targetIdx) {
            if (this.draggedLayerIdx === null || this.draggedLayerIdx === targetIdx) return;
            const item = this.layerStack.splice(this.draggedLayerIdx, 1)[0];
            this.layerStack.splice(targetIdx, 0, item);
            this.draggedLayerIdx = null;
            this.render();
            this.pushHistory();
        },

        // History Engine for Undo (Ctrl+Z) & Redo (Ctrl+Y)
        pushHistory() {
            if (this.isApplyingHistory) return;
            const snapshot = {
                settings: JSON.parse(JSON.stringify(this.settings)),
                data: JSON.parse(JSON.stringify(this.data)),
                customElements: this.customElements.map(el => {
                    const copy = Object.assign({}, el);
                    delete copy.imgObj;
                    return copy;
                }),
                layerStack: [...(this.layerStack || [])],
                guidelines: JSON.parse(JSON.stringify(this.guidelines))
            };

            // Check if identical to last snapshot
            if (this.history.length > 0 && this.historyIndex >= 0 && this.historyIndex < this.history.length) {
                const last = this.history[this.historyIndex];
                if (JSON.stringify(last) === JSON.stringify(snapshot)) {
                    return;
                }
            }

            if (this.historyIndex < this.history.length - 1) {
                this.history = this.history.slice(0, this.historyIndex + 1);
            }
            this.history.push(snapshot);
            if (this.history.length > 50) {
                this.history.shift();
            }
            this.historyIndex = this.history.length - 1;
        },

        recordHistory() {
            if (this.isApplyingHistory) return;
            if (this.historyDebounceTimer) clearTimeout(this.historyDebounceTimer);
            this.historyDebounceTimer = setTimeout(() => {
                this.pushHistory();
            }, 300);
        },

        undo() {
            if (this.historyIndex > 0) {
                this.historyIndex--;
                this.applyHistory(this.history[this.historyIndex]);
            }
        },

        redo() {
            if (this.historyIndex < this.history.length - 1) {
                this.historyIndex++;
                this.applyHistory(this.history[this.historyIndex]);
            }
        },

        applyHistory(snapshot) {
            if (!snapshot) return;
            this.isApplyingHistory = true;
            try {
                this.settings = JSON.parse(JSON.stringify(snapshot.settings));
                this.data = JSON.parse(JSON.stringify(snapshot.data));
                this.guidelines = JSON.parse(JSON.stringify(snapshot.guidelines || this.guidelines));
                this.customElements = (snapshot.customElements || []).map(el => {
                    if (el.type === 'image' && el.imgSrc) {
                        const img = new Image();
                        img.src = el.imgSrc;
                        el.imgObj = img;
                    }
                    return el;
                });
                if (Array.isArray(snapshot.layerStack)) {
                    this.layerStack = [...snapshot.layerStack];
                    this.syncLayerStack();
                }
                if (this.settings.template_image && (!this.templateImageObj || this.templateImageObj.src !== this.settings.template_image)) {
                    this.loadTemplateImage(this.settings.template_image);
                }
                this.render();
            } finally {
                this.$nextTick(() => {
                    this.isApplyingHistory = false;
                });
            }
        },

        init() {
            // Restore saved settings and presets
            const savedSettings = @json($currentSettings);
            if (savedSettings && typeof savedSettings === 'object') {
                for (const [key, val] of Object.entries(savedSettings)) {
                    if (['custom_elements', 'layer_stack', 'guidelines', 'custom_winners'].includes(key)) continue;
                    if (val !== undefined && val !== null) {
                        this.settings[key] = val;
                    }
                }

                if (savedSettings.guidelines) {
                    this.guidelines = Object.assign(this.guidelines, savedSettings.guidelines);
                }
            }

            const presetResultFonts = ['Rockwell Std', 'Montserrat', 'Sora', 'Mongolian Baiti', 'JetBrains Mono', 'Georgia', 'Playfair Display', 'Cinzel', 'Impact', 'Arial'];
            if (this.settings.result_font && !presetResultFonts.includes(this.settings.result_font)) {
                this.customFontInput = this.settings.result_font;
                this.isCustomFontName = true;
            }

            // Restore saved custom elements if any
            if (savedSettings && savedSettings.custom_elements && Array.isArray(savedSettings.custom_elements)) {
                this.customElements = savedSettings.custom_elements
                    .filter(el => el.id !== 'text_result_default' && !(el.type === 'text' && el.text === 'Result' && (el.color === '#000000' || el.color === '#000') && el.y > 700))
                    .map(el => {
                        if (el.type === 'image' && el.imgSrc) {
                            const img = new Image();
                            img.src = el.imgSrc;
                            el.imgObj = img;
                        }
                        return el;
                    });
            } else {
                this.customElements = [];
            }

            // Restore saved layer stack or establish default hierarchy (Top to Bottom)
            if (savedSettings && Array.isArray(savedSettings.layer_stack) && savedSettings.layer_stack.length > 0) {
                this.layerStack = savedSettings.layer_stack.filter(id => id !== 'text_result_default');
            } else {
                const belowCustom = this.customElements.filter(e => e.layerOrder === 'below').map(e => e.id);
                const aboveCustom = this.customElements.filter(e => e.layerOrder !== 'below').map(e => e.id);
                this.layerStack = [
                    ...aboveCustom,
                    'winners',
                    'result_no',
                    'competition',
                    'category',
                    ...belowCustom
                ];
            }
            this.syncLayerStack();

            this.loadTemplateImage(this.settings.template_image);
            this.loadMedalImages();

            // Keyboard Shortcuts for Undo/Redo, Nudging, Sizing, and Line Spacing
            window.addEventListener('keydown', (e) => {
                // Undo: Ctrl+Z / Cmd+Z (without Shift)
                if ((e.ctrlKey || e.metaKey) && (e.key === 'z' || e.key === 'Z') && !e.shiftKey) {
                    if (!['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
                        e.preventDefault();
                        this.undo();
                        return;
                    }
                }
                // Redo: Ctrl+Y / Cmd+Y or Ctrl+Shift+Z / Cmd+Shift+Z
                if (((e.ctrlKey || e.metaKey) && (e.key === 'y' || e.key === 'Y')) ||
                    ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'z' || e.key === 'Z'))) {
                    if (!['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
                        e.preventDefault();
                        this.redo();
                        return;
                    }
                }

                if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
                if (!this.selectedId) return;

                const step = e.shiftKey ? 10 : 1;
                let altered = false;
                if (e.key === 'ArrowLeft') { e.preventDefault(); this.nudgeSelected(-step, 0); altered = true; }
                else if (e.key === 'ArrowRight') { e.preventDefault(); this.nudgeSelected(step, 0); altered = true; }
                else if (e.key === 'ArrowUp') { e.preventDefault(); this.nudgeSelected(0, -step); altered = true; }
                else if (e.key === 'ArrowDown') { e.preventDefault(); this.nudgeSelected(0, step); altered = true; }
                else if (e.key === '+' || e.key === '=') {
                    e.preventDefault();
                    if (this.selectedId === 'winners') this.settings.winners_scale = Math.min(3.0, +(this.settings.winners_scale + 0.05).toFixed(2));
                    else if (this.selectedId === 'competition') this.settings.competition_size += 2;
                    else if (this.selectedId === 'category') this.settings.category_size += 2;
                    else if (this.selectedId === 'result_no') this.settings.result_size += 10;
                    else if (this.selectedType === 'custom' && this.selectedElement) {
                        if (this.selectedElement.type === 'text') this.selectedElement.size += 2;
                        else { this.selectedElement.width += 5; this.selectedElement.height += 5; }
                    }
                    this.render();
                    altered = true;
                } else if (e.key === '-' || e.key === '_') {
                    e.preventDefault();
                    if (this.selectedId === 'winners') this.settings.winners_scale = Math.max(0.3, +(this.settings.winners_scale - 0.05).toFixed(2));
                    else if (this.selectedId === 'competition') this.settings.competition_size = Math.max(12, this.settings.competition_size - 2);
                    else if (this.selectedId === 'category') this.settings.category_size = Math.max(12, this.settings.category_size - 2);
                    else if (this.selectedId === 'result_no') this.settings.result_size = Math.max(40, this.settings.result_size - 10);
                    else if (this.selectedType === 'custom' && this.selectedElement) {
                        if (this.selectedElement.type === 'text') this.selectedElement.size = Math.max(8, this.selectedElement.size - 2);
                        else { this.selectedElement.width = Math.max(10, this.selectedElement.width - 5); this.selectedElement.height = Math.max(10, this.selectedElement.height - 5); }
                    }
                    this.render();
                    altered = true;
                } else if (e.key === '[' || e.key === '{') {
                    e.preventDefault();
                    if (this.selectedId === 'winners') this.settings.winner_name_line_height = Math.max(12, this.settings.winner_name_line_height - 2);
                    else if (this.selectedId === 'competition') this.settings.competition_line_height = Math.max(15, this.settings.competition_line_height - 4);
                    else if (this.selectedType === 'custom' && this.selectedElement) this.selectedElement.lineHeight = Math.max(0.6, +(this.selectedElement.lineHeight - 0.1).toFixed(1));
                    this.render();
                    altered = true;
                } else if (e.key === ']' || e.key === '}') {
                    e.preventDefault();
                    if (this.selectedId === 'winners') this.settings.winner_name_line_height = Math.min(90, this.settings.winner_name_line_height + 2);
                    else if (this.selectedId === 'competition') this.settings.competition_line_height = Math.min(160, this.settings.competition_line_height + 4);
                    else if (this.selectedType === 'custom' && this.selectedElement) this.selectedElement.lineHeight = Math.min(2.8, +(this.selectedElement.lineHeight + 0.1).toFixed(1));
                    this.render();
                    altered = true;
                } else if (e.key === 'Delete' || e.key === 'Backspace') {
                    if (this.selectedType === 'custom') {
                        e.preventDefault();
                        this.deleteSelected();
                    }
                }
                if (altered) {
                    this.recordHistory();
                }
            });

            // Initial history snapshot
            this.$nextTick(() => {
                this.pushHistory();
            });

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

        loadMedalImages() {
            const m1 = this.settings.custom_medal_1 || '/images/medals/first.png';
            const m2 = this.settings.custom_medal_2 || '/images/medals/second.png';
            const m3 = this.settings.custom_medal_3 || '/images/medals/third.png';

            const img1 = new Image();
            img1.crossOrigin = 'anonymous';
            img1.onload = () => { this.medalImageObjs[1] = img1; this.render(); };
            img1.src = m1;

            const img2 = new Image();
            img2.crossOrigin = 'anonymous';
            img2.onload = () => { this.medalImageObjs[2] = img2; this.render(); };
            img2.src = m2;

            const img3 = new Image();
            img3.crossOrigin = 'anonymous';
            img3.onload = () => { this.medalImageObjs[3] = img3; this.render(); };
            img3.src = m3;
        },

        selectMedalPack(pack) {
            this.settings.selected_medal_pack_id = pack.id;
            this.settings.custom_medal_1 = pack.first_image;
            this.settings.custom_medal_2 = pack.second_image;
            this.settings.custom_medal_3 = pack.third_image;
            this.settings.medal_type = 'medals';
            this.loadMedalImages();
            this.pushHistory();
        },

        onResultFontChange() {
            if (this.settings.result_font === '__custom__') {
                this.isCustomFontName = true;
            } else {
                this.isCustomFontName = false;
                this.render();
                this.recordHistory();
            }
        },

        applyCustomFont() {
            if (this.customFontInput && this.customFontInput.trim().length > 0) {
                this.settings.result_font = this.customFontInput.trim();
                this.render();
                this.recordHistory();
            }
        },

        async loadSystemFonts() {
            if ('queryLocalFonts' in window) {
                try {
                    this.systemFontsLoading = true;
                    const fonts = await window.queryLocalFonts();
                    const fontFamilies = Array.from(new Set(fonts.map(f => f.family))).filter(Boolean).sort();
                    this.availableSystemFonts = fontFamilies;
                } catch (err) {
                    console.warn('System font permission dismissed or denied:', err);
                    alert('System font permission was not granted. You can type any system font name manually in the box!');
                } finally {
                    this.systemFontsLoading = false;
                }
            } else {
                alert('Your browser does not support queryLocalFonts API. You can type any font installed on your computer into the box below (e.g. Century Gothic, Segoe UI, Tahoma) and it will render directly on the poster!');
                this.isCustomFontName = true;
                this.settings.result_font = '__custom__';
            }
        },

        getWeightLabel(w) {
            const weights = {
                '300': 'Light (300)',
                '400': 'Regular (400)',
                '500': 'Medium (500)',
                '600': 'SemiBold (600)',
                '700': 'Bold (700)',
                '800': 'ExtraBold (800)',
                '900': 'Black (900)'
            };
            return weights[w] || (w ? w + ' Weight' : 'Regular (400)');
        },

        onLocalMedalPicked(rank, e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                const dataUrl = loadEvent.target.result;
                if (rank === 1) this.settings.custom_medal_1 = dataUrl;
                if (rank === 2) this.settings.custom_medal_2 = dataUrl;
                if (rank === 3) this.settings.custom_medal_3 = dataUrl;
                this.settings.medal_type = 'medals';
                this.loadMedalImages();
                this.pushHistory();
            };
            reader.readAsDataURL(file);
        },

        selectTemplate(id, src, layoutMode) {
            this.settings.template_id = id;
            this.settings.template_image = src;
            this.settings.layout_mode = layoutMode || 'default';

            const tmplData = this.templatesMap[id];
            if (tmplData && tmplData.preset && typeof tmplData.preset === 'object') {
                for (const [key, val] of Object.entries(tmplData.preset)) {
                    if (['custom_elements', 'layer_stack', 'guidelines', 'custom_winners', 'template_id', 'template_image'].includes(key)) continue;
                    if (val !== undefined && val !== null) {
                        this.settings[key] = val;
                    }
                }
                if (Array.isArray(tmplData.preset.custom_elements) && tmplData.preset.custom_elements.length > 0) {
                    this.customElements = tmplData.preset.custom_elements
                        .filter(el => el.id !== 'text_result_default' && !(el.type === 'text' && el.text === 'Result' && (el.color === '#000000' || el.color === '#000') && el.y > 700))
                        .map(el => {
                            if (el.type === 'image' && el.imgSrc) {
                                const img = new Image();
                                img.src = el.imgSrc;
                                el.imgObj = img;
                            }
                            return el;
                        });
                } else {
                    this.customElements = [];
                }
                if (Array.isArray(tmplData.preset.layer_stack) && tmplData.preset.layer_stack.length > 0) {
                    this.layerStack = tmplData.preset.layer_stack.filter(id => id !== 'text_result_default');
                    this.syncLayerStack();
                }
                if (tmplData.preset.guidelines) {
                    this.guidelines = Object.assign(this.guidelines, tmplData.preset.guidelines);
                }
            }

            this.loadTemplateImage(src);
            this.pushHistory();
        },

        addCustomText() {
            const newId = 'text_' + Date.now();
            const count = this.customElements.filter(e => e.type === 'text').length + 1;
            const newEl = {
                id: newId,
                type: 'text',
                label: 'Custom Text ' + count,
                text: 'NEW TEXT',
                font: 'Rockwell Std',
                size: 52,
                weight: '700',
                color: '#D02627',
                align: 'left',
                tracking: 0,
                lineHeight: 1.2,
                rotation: 0,
                opacity: 100,
                x: 205,
                y: 650 + (count * 40),
                visible: true,
                layerOrder: 'above'
            };
            this.customElements.push(newEl);
            this.layerStack.unshift(newId);
            this.selectElement(newId);
            this.activeTab = 'design';
            this.render();
            this.pushHistory();
        },

        addCustomShape(shapeType) {
            const newId = 'shape_' + Date.now();
            const count = this.customElements.filter(e => e.type === 'shape').length + 1;
            const newEl = {
                id: newId,
                type: 'shape',
                shapeType: shapeType || 'badge',
                label: (shapeType === 'badge' ? 'Badge ' : 'Shape ') + count,
                width: 160,
                height: 50,
                fillColor: '#D02627',
                strokeColor: '#000000',
                lineWidth: 0,
                rotation: 0,
                opacity: 100,
                x: 205,
                y: 750,
                visible: true,
                layerOrder: 'above'
            };
            this.customElements.push(newEl);
            this.layerStack.unshift(newId);
            this.selectElement(newId);
            this.activeTab = 'design';
            this.render();
            this.pushHistory();
        },

        uploadCustomImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                const img = new Image();
                img.onload = () => {
                    const newId = 'img_' + Date.now();
                    const count = this.customElements.filter(e => e.type === 'image').length + 1;
                    const newEl = {
                        id: newId,
                        type: 'image',
                        label: 'Graphic ' + count,
                        imgSrc: loadEvent.target.result,
                        imgObj: img,
                        width: Math.min(250, img.width || 200),
                        height: Math.min(250, img.height || 200),
                        rotation: 0,
                        opacity: 100,
                        x: 480,
                        y: 700,
                        visible: true,
                        layerOrder: 'above'
                    };
                    this.customElements.push(newEl);
                    this.layerStack.unshift(newId);
                    this.selectElement(newId);
                    this.activeTab = 'design';
                    this.render();
                    this.pushHistory();
                };
                img.src = loadEvent.target.result;
            };
            reader.readAsDataURL(file);
        },

        selectElement(id) {
            this.selectedId = id;
            this.selectedType = 'custom';
            this.render();
        },

        selectCoreLayer(layerName) {
            this.selectedId = layerName;
            this.selectedType = 'core';
            if (layerName === 'winners') this.activeTab = 'winners';
            else this.activeTab = 'core';
            this.render();
        },

        duplicateSelected() {
            if (this.selectedType === 'custom' && this.selectedElement) {
                const clone = JSON.parse(JSON.stringify(this.selectedElement));
                clone.id = clone.type + '_' + Date.now();
                clone.label = clone.label + ' (Copy)';
                clone.x += 25;
                clone.y += 25;
                if (clone.type === 'image' && this.selectedElement.imgObj) {
                    clone.imgObj = this.selectedElement.imgObj;
                }
                this.customElements.push(clone);
                const origIdx = this.layerStack.indexOf(this.selectedElement.id);
                if (origIdx >= 0) {
                    this.layerStack.splice(origIdx, 0, clone.id);
                } else {
                    this.layerStack.unshift(clone.id);
                }
                this.selectElement(clone.id);
                this.render();
                this.pushHistory();
            }
        },

        deleteSelected() {
            if (this.selectedType === 'custom' && this.selectedId) {
                this.deleteElement(this.selectedId);
            }
        },

        deleteElement(id) {
            this.customElements = this.customElements.filter(el => el.id !== id);
            this.layerStack = this.layerStack.filter(lid => lid !== id);
            this.selectedId = null;
            this.selectedType = null;
            this.render();
            this.pushHistory();
        },

        nudgeSelected(dx, dy) {
            if (this.selectedType === 'custom' && this.selectedElement) {
                this.selectedElement.x += dx;
                this.selectedElement.y += dy;
                this.render();
            } else if (this.selectedType === 'core') {
                if (this.selectedId === 'result_no') {
                    this.settings.result_x += dx;
                    this.settings.result_y += dy;
                } else if (this.selectedId === 'competition') {
                    this.settings.competition_x += dx;
                    this.settings.competition_y += dy;
                } else if (this.selectedId === 'category') {
                    this.settings.category_x += dx;
                    this.settings.category_y += dy;
                } else if (this.selectedId === 'winners') {
                    this.settings.block_left += dx;
                    this.settings.first_top += dy;
                }
                this.render();
            }
        },

        // Canvas Coordinates Conversion
        getCanvasCoords(e) {
            const canvas = document.getElementById('posterCanvas');
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
            };
        },

        // Compute Bounding Box of Selected Element for Transform Handles
        getSelectionBox() {
            if (!this.selectedId) return null;

            if (this.selectedType === 'custom' && this.selectedElement) {
                const el = this.selectedElement;
                let w = el.width;
                let h = el.height;
                if (!w || !h) {
                    if (el.type === 'text') {
                        const lines = (el.text || '').split('\n');
                        const maxLineLen = Math.max(...lines.map(l => l.length), 1);
                        const lineH = Math.round((el.size || 48) * (el.lineHeight || 1.2));
                        w = Math.max(60, Math.round(maxLineLen * (el.size || 48) * 0.58));
                        h = Math.max(30, lines.length * lineH);
                    } else {
                        w = 120;
                        h = 50;
                    }
                }
                return {
                    x: el.x,
                    y: el.y,
                    w: Math.max(20, w),
                    h: Math.max(20, h),
                    rotation: el.rotation || 0,
                    type: 'custom',
                    el: el
                };
            } else if (this.selectedType === 'core') {
                if (this.selectedId === 'result_no') {
                    const size = this.settings.result_size || 480;
                    return {
                        x: this.settings.result_x,
                        y: this.settings.result_y,
                        w: Math.round(size * 0.52),
                        h: Math.round(size * 0.65),
                        rotation: this.settings.result_rotation || 0,
                        type: 'core',
                        id: 'result_no'
                    };
                } else if (this.selectedId === 'competition') {
                    const maxW = this.settings.competition_max_width || 440;
                    const lineH = this.settings.competition_line_height || 94;
                    const lineCount = Math.max(1, (this.data.competition || '').split(/\r?\n/).length);
                    return {
                        x: this.settings.competition_x,
                        y: this.settings.competition_y,
                        w: maxW,
                        h: Math.round(lineH * Math.max(1.8, lineCount * 1.15)),
                        rotation: this.settings.competition_rotation || 0,
                        type: 'core',
                        id: 'competition'
                    };
                } else if (this.selectedId === 'category') {
                    return {
                        x: this.settings.category_x - 120,
                        y: this.settings.category_y - 35,
                        w: 240,
                        h: 70,
                        rotation: this.settings.category_rotation || 0,
                        type: 'core',
                        id: 'category'
                    };
                } else if (this.selectedId === 'winners') {
                    const scale = this.settings.winners_scale || 1.0;
                    return {
                        x: this.settings.block_left,
                        y: this.settings.first_top,
                        w: Math.round(440 * scale),
                        h: Math.round(520 * scale),
                        rotation: 0,
                        type: 'core',
                        id: 'winners'
                    };
                }
            }
            return null;
        },

        // Transform Handles (8 Resize Bounding Handles + 1 Rotation Knob)
        getHandles(box) {
            if (!box) return {};
            const { x, y, w, h } = box;
            return {
                nw: { x: x, y: y, cursor: 'nwse-resize' },
                n:  { x: x + w / 2, y: y, cursor: 'ns-resize' },
                ne: { x: x + w, y: y, cursor: 'nesw-resize' },
                e:  { x: x + w, y: y + h / 2, cursor: 'ew-resize' },
                se: { x: x + w, y: y + h, cursor: 'nwse-resize' },
                s:  { x: x + w / 2, y: y + h, cursor: 'ns-resize' },
                sw: { x: x, y: y + h, cursor: 'nesw-resize' },
                w:  { x: x, y: y + h / 2, cursor: 'ew-resize' },
                rot: { x: x + w / 2, y: y - 28, cursor: 'grab' }
            };
        },

        // Check if cursor hits any handle of the selected element
        getHandleAt(mouseX, mouseY) {
            const box = this.getSelectionBox();
            if (!box) return null;
            const handles = this.getHandles(box);
            const hitRadius = 16;
            for (const [name, pos] of Object.entries(handles)) {
                if (Math.hypot(mouseX - pos.x, mouseY - pos.y) <= hitRadius) {
                    return { name, pos, cursor: pos.cursor };
                }
            }
            return null;
        },

        // Hit Detection for Interactive Canvas Dragging & Selection (Topmost layer checked first)
        hitTest(x, y) {
            for (let i = 0; i < this.layerStack.length; i++) {
                const layerId = this.layerStack[i];
                if (layerId === 'result_no') {
                    if (this.settings.show_result_no) {
                        const rx = this.settings.result_x;
                        const ry = this.settings.result_y;
                        if (x >= rx - 40 && x <= rx + 320 && y >= ry - 40 && y <= ry + 300) {
                            return { type: 'core', id: 'result_no' };
                        }
                    }
                } else if (layerId === 'competition') {
                    if (this.settings.show_competition) {
                        const cx = this.settings.competition_x;
                        const cy = this.settings.competition_y;
                        if (x >= cx - 20 && x <= cx + (this.settings.competition_max_width || 440) + 20 && y >= cy - 20 && y <= cy + 220) {
                            return { type: 'core', id: 'competition' };
                        }
                    }
                } else if (layerId === 'category') {
                    if (this.settings.show_category) {
                        const zx = this.settings.category_x;
                        const zy = this.settings.category_y;
                        if (x >= zx - 140 && x <= zx + 140 && y >= zy - 40 && y <= zy + 40) {
                            return { type: 'core', id: 'category' };
                        }
                    }
                } else if (layerId === 'winners') {
                    if (this.settings.show_winners) {
                        const wx = this.settings.block_left;
                        const wy = this.settings.first_top;
                        const scale = this.settings.winners_scale || 1.0;
                        if (x >= wx - 20 && x <= wx + Math.round(440 * scale) + 20 && y >= wy - 20 && y <= wy + Math.round(540 * scale) + 20) {
                            return { type: 'core', id: 'winners' };
                        }
                    }
                } else {
                    const el = this.customElements.find(e => e.id === layerId);
                    if (el && el.visible) {
                        const w = el.width || (el.size ? (el.text ? el.text.length * el.size * 0.55 : el.size) : 100);
                        const h = el.height || (el.size || 50);
                        if (x >= el.x - 20 && x <= el.x + w + 20 && y >= el.y - 20 && y <= el.y + h + 20) {
                            return { type: 'custom', id: el.id, el: el };
                        }
                    }
                }
            }

            return null;
        },

        onMouseDown(e) {
            this.hasMutated = false;
            const coords = this.getCanvasCoords(e);

            // 1. Check if clicking on a transform handle first (resizing / rotating)
            const handleHit = this.getHandleAt(coords.x, coords.y);
            if (handleHit) {
                this.isResizing = true;
                this.activeHandle = handleHit.name;
                this.dragStartX = coords.x;
                this.dragStartY = coords.y;

                const box = this.getSelectionBox();
                this.elementStartX = box.x;
                this.elementStartY = box.y;
                this.elementStartW = box.w;
                this.elementStartH = box.h;

                if (this.selectedType === 'custom' && this.selectedElement) {
                    this.elementStartSize = this.selectedElement.size || 48;
                    this.elementStartRotation = this.selectedElement.rotation || 0;
                } else if (this.selectedType === 'core') {
                    if (this.selectedId === 'winners') {
                        this.elementStartScale = this.settings.winners_scale || 1.0;
                    } else if (this.selectedId === 'competition') {
                        this.elementStartSize = this.settings.competition_size || 100;
                        this.elementStartW = this.settings.competition_max_width || 440;
                        this.elementStartRotation = this.settings.competition_rotation || 0;
                    } else if (this.selectedId === 'result_no') {
                        this.elementStartSize = this.settings.result_size || 480;
                        this.elementStartRotation = this.settings.result_rotation || 0;
                    } else if (this.selectedId === 'category') {
                        this.elementStartSize = this.settings.category_size || 45;
                        this.elementStartRotation = this.settings.category_rotation || 0;
                    }
                }
                return;
            }

            // 2. Normal hit testing for element dragging or selection
            const hit = this.hitTest(coords.x, coords.y);
            if (hit) {
                this.isDragging = true;
                this.dragStartX = coords.x;
                this.dragStartY = coords.y;

                if (hit.type === 'custom') {
                    this.selectElement(hit.id);
                    this.activeTab = 'design';
                    this.elementStartX = hit.el.x;
                    this.elementStartY = hit.el.y;
                } else {
                    this.selectCoreLayer(hit.id);
                    if (hit.id === 'result_no') {
                        this.elementStartX = this.settings.result_x;
                        this.elementStartY = this.settings.result_y;
                    } else if (hit.id === 'competition') {
                        this.elementStartX = this.settings.competition_x;
                        this.elementStartY = this.settings.competition_y;
                    } else if (hit.id === 'category') {
                        this.elementStartX = this.settings.category_x;
                        this.elementStartY = this.settings.category_y;
                    } else if (hit.id === 'winners') {
                        this.elementStartX = this.settings.block_left;
                        this.elementStartY = this.settings.first_top;
                    }
                }
                this.render();
            } else {
                this.selectedId = null;
                this.selectedType = null;
                this.render();
            }
        },

        onMouseMove(e) {
            const canvas = document.getElementById('posterCanvas');
            const coords = this.getCanvasCoords(e);

            // Handle cursor styling when hovering
            if (!this.isDragging && !this.isResizing) {
                const handleHit = this.getHandleAt(coords.x, coords.y);
                if (handleHit) {
                    if (canvas) canvas.style.cursor = handleHit.cursor;
                } else if (this.hitTest(coords.x, coords.y)) {
                    if (canvas) canvas.style.cursor = 'move';
                } else {
                    if (canvas) canvas.style.cursor = 'default';
                }
                return;
            }

            const dx = coords.x - this.dragStartX;
            const dy = coords.y - this.dragStartY;

            // Handle Resizing / Scaling / Rotating via Handles
            if (this.isResizing) {
                this.hasMutated = true;
                if (this.activeHandle === 'rot') {
                    const cx = this.elementStartX + this.elementStartW / 2;
                    const cy = this.elementStartY + this.elementStartH / 2;
                    let angle = Math.round(Math.atan2(coords.y - cy, coords.x - cx) * (180 / Math.PI) + 90);
                    if (angle < 0) angle += 360;
                    [0, 90, 180, 270, 360].forEach(snap => {
                        if (Math.abs(angle - snap) < 5) angle = (snap === 360 ? 0 : snap);
                    });
                    if (this.selectedType === 'custom' && this.selectedElement) {
                        this.selectedElement.rotation = angle;
                    } else if (this.selectedType === 'core') {
                        if (this.selectedId === 'competition') this.settings.competition_rotation = angle;
                        else if (this.selectedId === 'result_no') this.settings.result_rotation = angle;
                        else if (this.selectedId === 'category') this.settings.category_rotation = angle;
                    }
                    this.render();
                    return;
                }

                // Resizing logic depending on active handle
                if (this.selectedType === 'custom' && this.selectedElement) {
                    const el = this.selectedElement;
                    if (el.type === 'text') {
                        const distDelta = (this.activeHandle.includes('e') || this.activeHandle.includes('s')) ? (dx + dy) : (-dx - dy);
                        const scaleFactor = 1 + (distDelta / 240);
                        el.size = Math.max(10, Math.min(320, Math.round(this.elementStartSize * scaleFactor)));
                    } else {
                        if (this.activeHandle.includes('e')) {
                            el.width = Math.max(20, Math.round(this.elementStartW + dx));
                        }
                        if (this.activeHandle.includes('s')) {
                            el.height = Math.max(15, Math.round(this.elementStartH + dy));
                        }
                        if (this.activeHandle.includes('w')) {
                            const newW = Math.max(20, Math.round(this.elementStartW - dx));
                            el.x = Math.round(this.elementStartX + (this.elementStartW - newW));
                            el.width = newW;
                        }
                        if (this.activeHandle.includes('n')) {
                            const newH = Math.max(15, Math.round(this.elementStartH - dy));
                            el.y = Math.round(this.elementStartY + (this.elementStartH - newH));
                            el.height = newH;
                        }
                    }
                    this.render();
                } else if (this.selectedType === 'core') {
                    if (this.selectedId === 'winners') {
                        const distDelta = (this.activeHandle.includes('e') || this.activeHandle.includes('s')) ? (dx + dy) : (-dx - dy);
                        const scaleFactor = 1 + (distDelta / 300);
                        this.settings.winners_scale = Math.max(0.35, Math.min(2.5, +(this.elementStartScale * scaleFactor).toFixed(2)));
                        this.render();
                    } else if (this.selectedId === 'competition') {
                        if (this.activeHandle.includes('e') || this.activeHandle.includes('w')) {
                            this.settings.competition_max_width = Math.max(180, Math.min(920, Math.round(this.elementStartW + dx)));
                        }
                        if (this.activeHandle.includes('s') || this.activeHandle.includes('se') || this.activeHandle.includes('sw')) {
                            const scaleFactor = 1 + (dy / 200);
                            this.settings.competition_size = Math.max(18, Math.min(220, Math.round(this.elementStartSize * scaleFactor)));
                        }
                        this.render();
                    } else if (this.selectedId === 'result_no') {
                        const distDelta = (this.activeHandle.includes('e') || this.activeHandle.includes('s')) ? (dx + dy) : (-dx - dy);
                        const scaleFactor = 1 + (distDelta / 300);
                        this.settings.result_size = Math.max(60, Math.min(800, Math.round(this.elementStartSize * scaleFactor)));
                        this.render();
                    } else if (this.selectedId === 'category') {
                        const distDelta = (this.activeHandle.includes('e') || this.activeHandle.includes('s')) ? (dx + dy) : (-dx - dy);
                        const scaleFactor = 1 + (distDelta / 200);
                        this.settings.category_size = Math.max(16, Math.min(120, Math.round(this.elementStartSize * scaleFactor)));
                        this.render();
                    }
                }
                return;
            }

            // Normal Position Dragging
            if (this.isDragging) {
                this.hasMutated = true;
                if (this.selectedType === 'custom' && this.selectedElement) {
                    this.selectedElement.x = Math.round(this.elementStartX + dx);
                    this.selectedElement.y = Math.round(this.elementStartY + dy);
                    this.render();
                } else if (this.selectedType === 'core') {
                    if (this.selectedId === 'result_no') {
                        this.settings.result_x = Math.round(this.elementStartX + dx);
                        this.settings.result_y = Math.round(this.elementStartY + dy);
                    } else if (this.selectedId === 'competition') {
                        this.settings.competition_x = Math.round(this.elementStartX + dx);
                        this.settings.competition_y = Math.round(this.elementStartY + dy);
                    } else if (this.selectedId === 'category') {
                        this.settings.category_x = Math.round(this.elementStartX + dx);
                        this.settings.category_y = Math.round(this.elementStartY + dy);
                    } else if (this.selectedId === 'winners') {
                        this.settings.block_left = Math.round(this.elementStartX + dx);
                        this.settings.first_top = Math.round(this.elementStartY + dy);
                    }
                    this.render();
                }
            }
        },

        onMouseUp() {
            if (this.hasMutated) {
                this.pushHistory();
                this.hasMutated = false;
            }
            this.isDragging = false;
            this.isResizing = false;
            this.activeHandle = null;
            const canvas = document.getElementById('posterCanvas');
            if (canvas) canvas.style.cursor = 'default';
        },

        onTouchStart(e) {
            this.onMouseDown(e);
        },

        onTouchMove(e) {
            this.onMouseMove(e);
        },

        onTouchEnd() {
            this.onMouseUp();
        },

        addGuide(type) {
            if (type === 'v') {
                this.guidelines.vertical.push(this.guidelines.newX);
                this.guidelines.vertical.sort((a, b) => a - b);
            } else {
                this.guidelines.horizontal.push(this.guidelines.newY);
                this.guidelines.horizontal.sort((a, b) => a - b);
            }
            this.render();
            this.pushHistory();
        },

        removeGuide(type, index) {
            if (type === 'v') this.guidelines.vertical.splice(index, 1);
            else this.guidelines.horizontal.splice(index, 1);
            this.render();
            this.pushHistory();
        },

        // Render Canvas Pipeline
        render(isExport = false) {
            const canvas = document.getElementById('posterCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const width = canvas.width;
            const height = canvas.height;

            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';

            if (!this.isDragging && !this.isResizing && !isExport && !this.isApplyingHistory) {
                this.recordHistory();
            }

            ctx.clearRect(0, 0, width, height);

            // 1. Draw Template Background
            if (this.templateImageObj && this.templateImageObj.complete) {
                ctx.drawImage(this.templateImageObj, 0, 0, width, height);
            } else {
                ctx.fillStyle = '#1e1b4b';
                ctx.fillRect(0, 0, width, height);
            }

            // 2. Draw Layers in Painter's order (reverse of layerStack: bottom to top)
            if (!this.layerStack || this.layerStack.length === 0) {
                this.syncLayerStack();
            }
            const drawOrder = [...this.layerStack].reverse();
            drawOrder.forEach(layerId => {
                this.drawLayer(ctx, layerId);
            });

            // 3. If Preview (!isExport), draw Selection Box & Guidelines
            if (!isExport) {
                this.drawSelectionOutline(ctx);
                if (this.guidelines.show) {
                    this.drawGuidelines(ctx, width, height);
                }
            }
        },

        drawLayer(ctx, layerId) {
            if (layerId === 'category') {
                this.drawZoneCapsule(ctx);
            } else if (layerId === 'competition') {
                this.drawProgramName(ctx);
            } else if (layerId === 'result_no') {
                this.drawResultNo(ctx);
            } else if (layerId === 'winners') {
                if (this.settings.show_winners) {
                    this.drawPodiumWinners(ctx);
                }
            } else {
                const el = this.customElements.find(e => e.id === layerId);
                if (el && el.visible) {
                    this.drawSingleCustomElement(ctx, el);
                }
            }
        },

        drawZoneCapsule(ctx) {
            if (!this.settings.show_category || !this.data.category) return;
            ctx.save();
            ctx.translate(this.settings.category_x, this.settings.category_y);
            if (this.settings.category_rotation) {
                ctx.rotate((this.settings.category_rotation * Math.PI) / 180);
            }
            ctx.fillStyle = this.settings.category_color || '#FFFFFF';
            ctx.font = `${this.settings.category_weight || '400'} ${this.settings.category_size || 45}px "Rockwell Std", serif`;
            ctx.textAlign = this.settings.category_align || 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(this.toTitleCase(this.data.category), 0, 0);
            ctx.restore();
        },

        drawProgramName(ctx) {
            if (!this.settings.show_competition || !this.data.competition) return;
            ctx.save();
            ctx.translate(this.settings.competition_x, this.settings.competition_y);
            if (this.settings.competition_rotation) {
                ctx.rotate((this.settings.competition_rotation * Math.PI) / 180);
            }
            const compFont = this.settings.competition_font || 'Rockwell Std';
            ctx.fillStyle = this.settings.competition_color || '#000000';
            ctx.font = `${this.settings.competition_weight || '400'} ${this.settings.competition_size || 100}px "${compFont}", serif`;
            ctx.textAlign = this.settings.competition_align || 'left';
            ctx.textBaseline = 'top';

            const lines = this.wrapText(ctx, this.toTitleCase(this.data.competition), this.settings.competition_max_width || 440);
            let lineY = 0;
            for (let l of lines) {
                ctx.fillText(l, 0, lineY);
                lineY += (this.settings.competition_line_height || 94);
            }
            ctx.restore();
        },

        drawResultNo(ctx) {
            if (!this.settings.show_result_no || !this.data.result_no) return;
            ctx.save();
            ctx.translate(this.settings.result_x, this.settings.result_y);
            if (this.settings.result_rotation) {
                ctx.rotate((this.settings.result_rotation * Math.PI) / 180);
            }
            ctx.fillStyle = this.settings.result_color || '#D02627';
            const fontFam = this.settings.result_font || 'Rockwell Std';
            const weight = this.settings.result_weight || '400';
            const size = this.settings.result_size || 480;
            ctx.font = `${weight} ${size}px "${fontFam}", "Rockwell Std", "Montserrat", "Sora", serif, sans-serif`;
            ctx.textBaseline = 'top';
            ctx.textAlign = 'left';
            if ('letterSpacing' in ctx) {
                const lowerFont = fontFam.toLowerCase();
                if (lowerFont.includes('mongolian') || lowerFont.includes('monbaiti')) {
                    ctx.letterSpacing = Math.round(-12 * (size / 480)) + 'px';
                } else if (lowerFont.includes('rockwell')) {
                    ctx.letterSpacing = Math.round(-3 * (size / 480)) + 'px';
                } else {
                    ctx.letterSpacing = '0px';
                }
            }
            ctx.fillText(this.data.result_no, 0, 0);
            ctx.restore();
        },

        drawSingleCustomElement(ctx, el) {
            if (!el || !el.visible) return;
            ctx.save();
            ctx.translate(el.x, el.y);
            if (el.rotation) {
                ctx.rotate((el.rotation * Math.PI) / 180);
            }
            ctx.globalAlpha = (el.opacity ?? 100) / 100;

            if (el.type === 'text') {
                ctx.fillStyle = el.color || '#000000';
                ctx.font = `${el.weight || '400'} ${el.size || 48}px "${el.font || 'Rockwell Std'}", sans-serif`;
                ctx.textAlign = el.align || 'left';
                ctx.textBaseline = 'top';
                if ('letterSpacing' in ctx && el.tracking) {
                    ctx.letterSpacing = el.tracking + 'px';
                }
                const lines = (el.text || '').split('\n');
                const lineH = Math.round((el.size || 48) * (el.lineHeight || 1.2));
                lines.forEach((l, idx) => {
                    ctx.fillText(l, 0, idx * lineH);
                });

            } else if (el.type === 'shape') {
                if (el.shapeType === 'badge' || el.shapeType === 'stamp') {
                    // Styled Festival Badge
                    ctx.fillStyle = el.fillColor || '#D02627';
                    this.drawRoundedRect(ctx, 0, 0, el.width, el.height, 8);
                    ctx.fill();
                    if (el.strokeColor && el.lineWidth > 0) {
                        ctx.strokeStyle = el.strokeColor;
                        ctx.lineWidth = el.lineWidth;
                        ctx.stroke();
                    }
                } else if (el.shapeType === 'pill') {
                    ctx.fillStyle = el.fillColor || '#be1e2d';
                    this.drawRoundedRect(ctx, 0, 0, el.width, el.height, el.height / 2);
                    ctx.fill();
                } else if (el.shapeType === 'line') {
                    ctx.strokeStyle = el.fillColor || '#000000';
                    ctx.lineWidth = el.height || 2;
                    ctx.beginPath();
                    ctx.moveTo(0, 0);
                    ctx.lineTo(el.width, 0);
                    ctx.stroke();
                } else {
                    ctx.fillStyle = el.fillColor || '#D02627';
                    ctx.fillRect(0, 0, el.width, el.height);
                }

            } else if (el.type === 'image') {
                if (el.imgObj && el.imgObj.complete) {
                    ctx.drawImage(el.imgObj, 0, 0, el.width, el.height);
                }
            }

            ctx.restore();
        },

        drawPodiumWinners(ctx) {
            const flatWinners = [];
            const addPlace = (arr, rank, label, defaultName) => {
                if (Array.isArray(arr) && arr.length > 0) {
                    arr.forEach(w => {
                        const name = (w && w.name && w.name.trim()) ? this.toTitleCase(w.name) : defaultName;
                        const unit = (w && w.unit && w.unit.trim()) ? this.toTitleCase(w.unit) : 'Team / Unit';
                        flatWinners.push({ rank, rankLabel: label, name, unit });
                    });
                } else {
                    flatWinners.push({ rank, rankLabel: label, name: defaultName, unit: 'Team / Unit' });
                }
            };
            addPlace(this.data.winners.first, '01', '1st Place', 'Muhammed Yaseen P.');
            addPlace(this.data.winners.second, '02', '2nd Place', 'Ahmad Rayyan K.');
            addPlace(this.data.winners.third, '03', '3rd Place', 'Bilal Muhammed');

            const count = flatWinners.length;
            if (count === 0) return;

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
            const nameLineH = Math.max(14, Math.round((this.settings.winner_name_line_height || 38) * finalScale));
            const rawUnitGap = (this.settings.winner_unit_gap !== undefined && this.settings.winner_unit_gap !== null && !isNaN(this.settings.winner_unit_gap)) ? Number(this.settings.winner_unit_gap) : 2;
            const unitGap = Math.round(rawUnitGap * finalScale);
            const blockLeft = this.settings.block_left || 205;
            const dividerWidth = Math.round(280 * Math.min(1.0, finalScale * 1.1));

            const medalType = this.settings.medal_type || 'medals';
            const medalPos = this.settings.medal_position || 'left';
            const medSize = Math.max(20, Math.round((this.settings.medal_size || 52) * finalScale));
            const medXOffset = Math.round((this.settings.medal_x_offset || 0) * finalScale);
            const medYOffset = Math.round((this.settings.medal_y_offset || 0) * finalScale);
            const medGap = Math.max(6, Math.round((this.settings.medal_gap || 16) * finalScale));

            let currY = startY;
            flatWinners.forEach(w => {
                ctx.save();
                ctx.textAlign = 'left';
                ctx.textBaseline = 'top';

                const rankIndex = (w.rank === '01') ? 1 : ((w.rank === '02') ? 2 : 3);
                let textX = blockLeft;
                let nameY = currY;

                // 1. Draw Medals / Numbers / Badges
                if (medalType === 'medals') {
                    const medImg = this.medalImageObjs ? this.medalImageObjs[rankIndex] : null;
                    const medX = blockLeft + medXOffset;
                    const medY = currY + medYOffset;

                    if (medImg && medImg.complete && medImg.naturalWidth > 0) {
                        ctx.drawImage(medImg, medX, medY, medSize, medSize);
                    } else {
                        // High-contrast clean vector badge fallback while loading
                        const grad = ctx.createLinearGradient(medX, medY, medX + medSize, medY + medSize);
                        if (rankIndex === 1) { grad.addColorStop(0, '#fef08a'); grad.addColorStop(1, '#eab308'); }
                        else if (rankIndex === 2) { grad.addColorStop(0, '#f8fafc'); grad.addColorStop(1, '#94a3b8'); }
                        else { grad.addColorStop(0, '#fed7aa'); grad.addColorStop(1, '#c2410c'); }
                        ctx.fillStyle = grad;
                        ctx.beginPath();
                        ctx.arc(medX + medSize / 2, medY + medSize / 2, medSize / 2, 0, Math.PI * 2);
                        ctx.fill();
                        ctx.strokeStyle = 'rgba(0,0,0,0.15)';
                        ctx.lineWidth = 1.5;
                        ctx.stroke();
                        ctx.fillStyle = '#0f172a';
                        ctx.font = `bold ${Math.round(medSize * 0.42)}px "Rockwell Std", sans-serif`;
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(w.rank, medX + medSize / 2, medY + medSize / 2);
                        ctx.textAlign = 'left';
                        ctx.textBaseline = 'top';
                    }

                    if (medalPos === 'left') {
                        textX = blockLeft + medSize + medGap;
                        nameY = currY;
                    } else {
                        textX = blockLeft;
                        nameY = currY + medSize + medGap;
                    }

                } else if (medalType === 'numbers') {
                    ctx.fillStyle = this.settings.winner_name_color || '#D02627';
                    ctx.font = `400 ${rankSize}px "Rockwell Std", serif`;
                    if ('letterSpacing' in ctx) ctx.letterSpacing = '-0.8px';
                    ctx.fillText(w.rank, blockLeft, currY);
                    textX = blockLeft;
                    nameY = currY + Math.round(rankSize * 1.3);

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
                    textX = blockLeft;
                    nameY = currY + badgeH + 6;

                } else if (medalType === 'text') {
                    ctx.fillStyle = this.settings.winner_name_color || '#D02627';
                    ctx.font = `700 ${Math.round(rankSize * 0.9)}px "Rockwell Std", serif`;
                    ctx.fillText(w.rankLabel, blockLeft, currY);
                    textX = blockLeft;
                    nameY = currY + Math.round(rankSize * 1.3);

                } else if (medalType === 'none') {
                    textX = blockLeft;
                    nameY = currY;
                }

                // 2. Student Name (with multi-line & 2-line auto-split support)
                ctx.fillStyle = this.settings.winner_name_color || '#D02627';
                ctx.font = `${this.settings.winner_name_weight || '400'} ${winSize}px "${winFont}", serif`;
                if ('letterSpacing' in ctx) ctx.letterSpacing = '-1.2px';

                let nameLines = [];
                if (w.name.includes('\n')) {
                    nameLines = w.name.split(/\r?\n/).map(s => s.trim()).filter(s => s.length > 0);
                } else if (w.name.includes('/')) {
                    nameLines = w.name.split('/').map(s => s.trim()).filter(s => s.length > 0);
                } else if (this.settings.winner_name_2lines && w.name.length > 16 && w.name.includes(' ')) {
                    const parts = w.name.split(' ');
                    const splitIdx = Math.ceil(parts.length / 2);
                    nameLines = [parts.slice(0, splitIdx).join(' '), parts.slice(splitIdx).join(' ')];
                } else {
                    nameLines = [w.name];
                }
                if (nameLines.length === 0) nameLines = [w.name || ''];

                let currentNameY = nameY;
                nameLines.forEach((nLine, lIdx) => {
                    ctx.fillText(nLine.trim(), textX, currentNameY);
                    if (lIdx < nameLines.length - 1) {
                        currentNameY += nameLineH;
                    }
                });

                // 3. Group / Team Name (closer to student name)
                let unitY = currentNameY + Math.round(winSize * 0.76) + unitGap;
                ctx.fillStyle = this.settings.winner_unit_color || '#000000';
                ctx.font = `${this.settings.winner_unit_weight || '300'} ${unitSize}px "Rockwell Std", serif`;
                if ('letterSpacing' in ctx) ctx.letterSpacing = '-0.5px';
                ctx.fillText(w.unit, textX, unitY);

                // 4. Divider Line (optional)
                if (this.settings.show_winner_divider) {
                    let lineY = unitY + Math.round(unitSize * 0.95) + 6;
                    ctx.strokeStyle = this.settings.winner_divider_color || 'rgba(0, 0, 0, 0.22)';
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(textX, lineY);
                    ctx.lineTo(textX + dividerWidth, lineY);
                    ctx.stroke();
                }

                ctx.restore();
                const extraNameH = Math.max(0, (nameLines.length - 1) * nameLineH);
                currY += (rowGap + extraNameH);
            });
        },

        drawSelectionOutline(ctx) {
            const box = this.getSelectionBox();
            if (!box) return;

            const { x, y, w, h } = box;
            ctx.save();

            // 1. Sleek cyan bounding box
            ctx.strokeStyle = '#00f0ff';
            ctx.lineWidth = 1.8;
            ctx.setLineDash([5, 4]);
            ctx.strokeRect(x - 4, y - 4, w + 8, h + 8);
            ctx.setLineDash([]); // clear dash

            // 2. Rotation Handle (connecting line + circular knob)
            const rotX = x + w / 2;
            const rotY = y - 28;
            ctx.strokeStyle = '#00f0ff';
            ctx.lineWidth = 1.5;
            ctx.beginPath();
            ctx.moveTo(rotX, y - 4);
            ctx.lineTo(rotX, rotY);
            ctx.stroke();

            // Rotation Knob
            ctx.fillStyle = '#ffffff';
            ctx.strokeStyle = '#00f0ff';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.arc(rotX, rotY, 6, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();

            // 3. 8 Transform Resize Handles (square knobs)
            const handles = this.getHandles(box);
            const handleSize = 9;
            for (const [name, pos] of Object.entries(handles)) {
                if (name === 'rot') continue;
                ctx.fillStyle = '#ffffff';
                ctx.strokeStyle = '#00f0ff';
                ctx.lineWidth = 1.8;
                ctx.fillRect(pos.x - handleSize / 2, pos.y - handleSize / 2, handleSize, handleSize);
                ctx.strokeRect(pos.x - handleSize / 2, pos.y - handleSize / 2, handleSize, handleSize);
            }

            // 4. Floating Dimension & Position Tooltip Badge
            let badgeText = `W:${Math.round(w)}px H:${Math.round(h)}px`;
            if (this.selectedId === 'winners') {
                badgeText = `Winners Scale: ${this.settings.winners_scale}x`;
            } else if (this.selectedId === 'competition') {
                badgeText = `Size:${this.settings.competition_size}px  Wrap:${Math.round(w)}px`;
            }
            if (box.rotation) {
                badgeText += `  ${box.rotation}°`;
            }

            ctx.font = 'bold 11px monospace';
            const textMetrics = ctx.measureText(badgeText);
            const badgeW = textMetrics.width + 16;
            const badgeH = 22;
            const badgeX = x - 4;
            const badgeY = y - 36;

            ctx.fillStyle = 'rgba(15, 23, 42, 0.94)';
            this.drawRoundedRect(ctx, badgeX, badgeY, badgeW, badgeH, 6);
            ctx.fill();
            ctx.strokeStyle = 'rgba(0, 240, 255, 0.5)';
            ctx.lineWidth = 1;
            ctx.stroke();

            ctx.fillStyle = '#00f0ff';
            ctx.textAlign = 'left';
            ctx.textBaseline = 'middle';
            ctx.fillText(badgeText, badgeX + 8, badgeY + (badgeH / 2));

            ctx.restore();
        },

        drawGuidelines(ctx, width, height) {
            ctx.save();
            ctx.strokeStyle = 'rgba(0, 240, 255, 0.85)';
            ctx.lineWidth = 1;
            ctx.setLineDash([4, 4]);

            // Vertical Guides (X)
            (this.guidelines.vertical || []).forEach(gx => {
                ctx.beginPath();
                ctx.moveTo(gx, 0);
                ctx.lineTo(gx, height);
                ctx.stroke();

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

        toTitleCase(str) {
            if (!str) return '';
            return str.toString().trim().replace(/\w\S*/g, function(txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            });
        },

        wrapText(ctx, text, maxWidth) {
            if (!text) return [];
            // Split by explicit newline (Enter key) first
            const paragraphs = text.toString().split(/\r?\n/);
            const allLines = [];

            for (const para of paragraphs) {
                const trimmed = para.trim();
                if (!trimmed) {
                    continue;
                }
                const words = trimmed.split(/\s+/);
                let currentLine = words[0];

                for (let i = 1; i < words.length; i++) {
                    const word = words[i];
                    const width = ctx.measureText(currentLine + ' ' + word).width;
                    if (maxWidth && width <= maxWidth) {
                        currentLine += ' ' + word;
                    } else {
                        allLines.push(currentLine);
                        currentLine = word;
                    }
                }
                if (currentLine) allLines.push(currentLine);
            }
            return allLines.length > 0 ? allLines : [text];
        },

        generateHighQualityCanvas(scale = 2) {
            const baseCanvas = document.getElementById('posterCanvas');
            const exportCanvas = document.createElement('canvas');
            const baseW = baseCanvas ? baseCanvas.width : 1080;
            const baseH = baseCanvas ? baseCanvas.height : 1350;
            
            exportCanvas.width = baseW * scale;
            exportCanvas.height = baseH * scale;
            
            const ctx = exportCanvas.getContext('2d');
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.scale(scale, scale);

            // 1. Draw Template Background at High Res
            if (this.templateImageObj && this.templateImageObj.complete) {
                ctx.drawImage(this.templateImageObj, 0, 0, baseW, baseH);
            } else {
                ctx.fillStyle = '#1e1b4b';
                ctx.fillRect(0, 0, baseW, baseH);
            }

            // 2. Draw Layers in Painter's order (reverse of layerStack: bottom to top)
            if (!this.layerStack || this.layerStack.length === 0) {
                this.syncLayerStack();
            }
            const drawOrder = [...this.layerStack].reverse();
            drawOrder.forEach(layerId => {
                this.drawLayer(ctx, layerId);
            });

            return exportCanvas;
        },

        downloadPoster(scale = 2) {
            this.render(true);
            const exportCanvas = this.generateHighQualityCanvas(scale);
            const link = document.createElement('a');
            const resNo = (this.data.result_no || '01').toString().trim().padStart(2, '0');
            const cleanSlug = (s) => (s || '').toString().trim().replace(/[/\\?%*:|"<>#]/g, '').replace(/\s+/g, '_').replace(/_+/g, '_').replace(/^_+|_+$/g, '');
            const zone = cleanSlug(this.data.category || 'Zone');
            const prog = cleanSlug(this.data.competition || 'Program');
            link.download = `${resNo}_${zone}_${prog}.png`;
            link.href = exportCanvas.toDataURL('image/png', 1.0);
            link.click();
            this.render(false);
        },

        saveAsDefault() {
            this.savingDefault = true;
            this.statusMsg = '';

            const saveUrl = @if(!empty($isTemplateCustomizer))
                '{{ route("media.results.templates.save-customization", $template ?? 1) }}'
            @else
                '{{ route("media.results.save-default-settings") }}'
            @endif;

            fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    template_id: this.settings.template_id,
                    result_id: {{ $result->id ?? 'null' }},
                    settings: Object.assign({}, this.settings, {
                        layer_stack: this.layerStack,
                        custom_elements: this.customElements.map(el => {
                            const clone = Object.assign({}, el);
                            delete clone.imgObj;
                            return clone;
                        }),
                        guidelines: this.guidelines
                    })
                })
            })
            .then(async res => {
                this.savingDefault = false;
                const data = await res.json().catch(() => null);
                if (res.ok && data && data.success) {
                    this.statusType = 'success';
                    this.statusMsg = data.message || 'ഡിഫോൾട്ട് ക്രമീകരണങ്ങൾ വിജയകരമായി സേവ് ചെയ്തു.';
                    if (this.templatesMap && this.templatesMap[this.settings.template_id]) {
                        this.templatesMap[this.settings.template_id].preset = Object.assign({}, this.settings, {
                            layer_stack: [...this.layerStack],
                            custom_elements: this.customElements.map(el => {
                                const clone = Object.assign({}, el);
                                delete clone.imgObj;
                                return clone;
                            }),
                            guidelines: Object.assign({}, this.guidelines)
                        });
                    }
                } else {
                    this.statusType = 'error';
                    this.statusMsg = (data && data.message) ? data.message : 'സേവ് ചെയ്യാൻ സാധിച്ചില്ല (Status ' + res.status + ')';
                }
            })
            .catch(err => {
                this.savingDefault = false;
                this.statusType = 'error';
                this.statusMsg = 'Error saving defaults: ' + err.message;
            });
        },

        saveAndPublish() {
            @if(!empty($isTemplateCustomizer))
                this.saveAsDefault();
                return;
            @endif

            this.publishing = true;
            this.statusMsg = '';
            this.render(true);

            // Export Ultra-High Quality 2X resolution (2160x2700) for crystal clear clarity
            const exportCanvas = this.generateHighQualityCanvas(2);
            const dataUrl = exportCanvas.toDataURL('image/png', 1.0);

            fetch('{{ !empty($result->id) ? route("media.results.save-poster", $result->id) : "#" }}', {
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
                        layer_stack: this.layerStack,
                        custom_winners: this.data.winners,
                        custom_elements: this.customElements.map(el => {
                            const clone = Object.assign({}, el);
                            delete clone.imgObj;
                            return clone;
                        }),
                        guidelines: this.guidelines
                    }),
                    publish_now: true
                })
            })
            .then(async res => {
                this.publishing = false;
                this.render(false);
                const data = await res.json().catch(() => null);
                if (res.ok && data && data.success) {
                    this.statusType = 'success';
                    this.statusMsg = 'Result poster generated and published successfully!';
                    setTimeout(() => {
                        window.location.href = '{{ route("media.results.index", ["tab" => "published"]) }}';
                    }, 1200);
                } else {
                    this.statusType = 'error';
                    this.statusMsg = (data && data.message) ? data.message : 'Failed to publish poster (Status ' + res.status + ')';
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
