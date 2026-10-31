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
        <span style="font-family: 'Sora'; font-weight: 700;">Sora 700</span>
        <span style="font-family: 'Anek Malayalam'; font-weight: 700;">Anek Malayalam 700</span>
    </div>

    <!-- Top Action & Navigation Header -->
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
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-purple-100 text-purple-800 border border-purple-200">
                    Mini Poster Design Studio
                </span>
                <a href="{{ route('media.results.planner') }}" class="text-[10px] font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 px-2 py-0.5 rounded border border-purple-200 flex items-center gap-1 transition">
                    <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span>Strategy Planner</span>
                </a>
            </div>

            <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight mt-1.5 font-sora">
                {{ $result->program->name }}
            </h1>
            <p class="text-xs text-slate-500">
                Zone: <strong class="text-slate-700">{{ $result->program->zone->name ?? $result->program->eligibility ?? 'All Zones' }}</strong> &bull;
                Result No: <strong class="text-[#be1e2d] font-mono">#{{ sprintf('%02d', $resultNo) }}</strong> &bull;
                Selected: <strong class="text-purple-700 font-mono" x-text="selectedLabel">None</strong>
            </p>
        </div>

        <!-- Header Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Add Text Button -->
            <button type="button" @click="addCustomText()"
                    class="px-3.5 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-900 font-bold text-xs flex items-center gap-1.5 transition">
                <svg class="w-3.5 h-3.5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Text</span>
            </button>

            <!-- Add Shape Button -->
            <button type="button" @click="addCustomShape('badge')"
                    class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-900 font-bold text-xs flex items-center gap-1.5 transition">
                <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                <span>+ Badge / Shape</span>
            </button>

            <!-- Guidelines Toggle -->
            <button type="button" @click="guidelines.show = !guidelines.show; render();"
                    class="px-3 py-2 rounded-xl border text-xs font-bold flex items-center gap-1.5 transition-colors"
                    :class="guidelines.show ? 'bg-cyan-50 border-cyan-400 text-cyan-800' : 'bg-white border-slate-300 text-slate-600 hover:bg-slate-50'">
                <svg class="w-3.5 h-3.5 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16M10 4v16M14 4v16"></path></svg>
                <span x-text="guidelines.show ? 'Guides ON' : 'Guides OFF'"></span>
            </button>

            <!-- Save as Default -->
            <button type="button" @click="saveAsDefault()" :disabled="savingDefault"
                    class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors disabled:opacity-50">
                <span x-text="savingDefault ? 'Saving...' : 'Save Default'"></span>
            </button>

            <!-- Download PNG -->
            <button type="button" @click="downloadPoster()"
                    class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-1.5 transition-colors shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Download PNG</span>
            </button>

            <!-- Save & Publish -->
            <button type="button" @click="saveAndPublish()" :disabled="publishing"
                    class="px-5 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-black text-xs flex items-center gap-1.5 transition-all shadow-md shadow-[#be1e2d]/20 disabled:opacity-50">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span x-text="publishing ? 'Publishing...' : 'Save & Publish'"></span>
            </button>
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
                <button type="button" @click="activeTab = 'design'"
                        class="px-3 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5"
                        :class="activeTab === 'design' ? 'bg-white text-purple-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    <span>Design Tool</span>
                </button>

                <button type="button" @click="activeTab = 'layers'"
                        class="px-3 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5"
                        :class="activeTab === 'layers' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>Layers</span>
                </button>

                <button type="button" @click="activeTab = 'winners'"
                        class="px-3 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5"
                        :class="activeTab === 'winners' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>Winners & Scale</span>
                </button>

                <button type="button" @click="activeTab = 'core'"
                        class="px-3 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5"
                        :class="activeTab === 'core' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"></path></svg>
                    <span>Core Elements</span>
                </button>

                <button type="button" @click="activeTab = 'guides'"
                        class="px-3 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5"
                        :class="activeTab === 'guides' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    <span>Guides</span>
                </button>

                <button type="button" @click="activeTab = 'templates'"
                        class="px-3 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5"
                        :class="activeTab === 'templates' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Templates</span>
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

                    <!-- Layer Depth: Top Layer vs Behind Text -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-700">Z-Order (ലെയർ ക്രമം)</span>
                        <select x-model="selectedElement.layerOrder" @change="render()" class="text-xs p-1.5 rounded-lg border border-slate-300 bg-white">
                            <option value="above">Above All Content (Top)</option>
                            <option value="below">Behind Text (Background)</option>
                        </select>
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

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-700">Z-Order (ലെയർ ക്രമം)</span>
                        <select x-model="selectedElement.layerOrder" @change="render()" class="text-xs p-1.5 rounded-lg border border-slate-300 bg-white">
                            <option value="above">Above All Content (Top)</option>
                            <option value="below">Behind Text (Background)</option>
                        </select>
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

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-700">Z-Order (ലെയർ ക്രമം)</span>
                        <select x-model="selectedElement.layerOrder" @change="render()" class="text-xs p-1.5 rounded-lg border border-slate-300 bg-white">
                            <option value="above">Above All Content (Top)</option>
                            <option value="below">Behind Text (Background)</option>
                        </select>
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
                            <p class="text-[11px] text-slate-500">Click any layer to select, toggle visibility, or delete.</p>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-700">
                            <span x-text="customElements.length + 4"></span> Layers
                        </span>
                    </div>

                    <!-- Custom Elements Stack -->
                    <div class="space-y-2">
                        <template x-for="(el, idx) in customElements" :key="el.id">
                            <div class="flex items-center justify-between p-2.5 rounded-xl border transition-all cursor-pointer"
                                 :class="selectedElement && selectedElement.id === el.id ? 'bg-purple-50 border-purple-400 ring-1 ring-purple-400' : 'bg-slate-50 border-slate-200 hover:border-slate-300'"
                                 @click="selectElement(el.id)">
                                
                                <div class="flex items-center gap-2.5">
                                    <button type="button" @click.stop="el.visible = !el.visible; render();"
                                            class="p-1 rounded text-slate-400 hover:text-slate-800 transition" :class="el.visible ? 'text-emerald-600' : 'text-slate-300 opacity-50'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                    <span class="text-xs font-bold text-slate-800" x-text="el.label"></span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-mono uppercase px-2 py-0.5 rounded bg-white text-slate-600 border border-slate-200" x-text="el.type"></span>
                                    <button type="button" @click.stop="deleteElement(el.id)" class="text-red-500 hover:text-red-700 p-1">
                                        &times;
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Core Layers -->
                        <div class="p-2.5 rounded-xl border bg-slate-50 border-slate-200 flex items-center justify-between cursor-pointer"
                             :class="selectedId === 'result_no' ? 'bg-amber-50 border-amber-400 ring-1 ring-amber-400' : ''"
                             @click="selectCoreLayer('result_no')">
                            <span class="text-xs font-bold text-slate-800">Result Number (#{{ sprintf('%02d', $resultNo) }})</span>
                            <span class="text-[10px] font-mono text-slate-500">Core</span>
                        </div>

                        <div class="p-2.5 rounded-xl border bg-slate-50 border-slate-200 flex items-center justify-between cursor-pointer"
                             :class="selectedId === 'competition' ? 'bg-amber-50 border-amber-400 ring-1 ring-amber-400' : ''"
                             @click="selectCoreLayer('competition')">
                            <span class="text-xs font-bold text-slate-800">Program Name</span>
                            <span class="text-[10px] font-mono text-slate-500">Core</span>
                        </div>

                        <div class="p-2.5 rounded-xl border bg-slate-50 border-slate-200 flex items-center justify-between cursor-pointer"
                             :class="selectedId === 'category' ? 'bg-amber-50 border-amber-400 ring-1 ring-amber-400' : ''"
                             @click="selectCoreLayer('category')">
                            <span class="text-xs font-bold text-slate-800">Zone Capsule</span>
                            <span class="text-[10px] font-mono text-slate-500">Core</span>
                        </div>

                        <div class="p-2.5 rounded-xl border bg-slate-50 border-slate-200 flex items-center justify-between cursor-pointer"
                             :class="selectedId === 'winners' ? 'bg-amber-50 border-amber-400 ring-1 ring-amber-400' : ''"
                             @click="selectCoreLayer('winners')">
                            <span class="text-xs font-bold text-slate-800">Podium Winners (1st, 2nd, 3rd)</span>
                            <span class="text-[10px] font-mono text-slate-500">Core</span>
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
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-700">Medal Style (മെഡൽ തരം)</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button type="button" @click="settings.medal_type = 'numbers'; render();" class="p-2 text-xs font-bold rounded-xl border text-center transition" :class="settings.medal_type === 'numbers' ? 'bg-purple-900 text-white border-purple-900' : 'bg-white border-slate-200 text-slate-700'">01 02 03</button>
                            <button type="button" @click="settings.medal_type = 'badges'; render();" class="p-2 text-xs font-bold rounded-xl border text-center transition" :class="settings.medal_type === 'badges' ? 'bg-purple-900 text-white border-purple-900' : 'bg-white border-slate-200 text-slate-700'">Badges</button>
                            <button type="button" @click="settings.medal_type = 'text'; render();" class="p-2 text-xs font-bold rounded-xl border text-center transition" :class="settings.medal_type === 'text' ? 'bg-purple-900 text-white border-purple-900' : 'bg-white border-slate-200 text-slate-700'">1st 2nd</button>
                            <button type="button" @click="settings.medal_type = 'none'; render();" class="p-2 text-xs font-bold rounded-xl border text-center transition" :class="settings.medal_type === 'none' ? 'bg-purple-900 text-white border-purple-900' : 'bg-white border-slate-200 text-slate-700'">None</button>
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

                    <!-- Line Spacing Sliders -->
                    <div class="grid grid-cols-3 gap-2">
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-slate-600">Leading</span>
                            <input type="range" min="18" max="70" step="1" x-model.number="settings.winner_name_line_height" @input="render()" class="w-full accent-purple-600">
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-slate-600">Group Gap</span>
                            <input type="range" min="2" max="30" step="1" x-model.number="settings.winner_unit_gap" @input="render()" class="w-full accent-purple-600">
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-slate-600">Row Gap</span>
                            <input type="range" min="90" max="250" step="2" x-model.number="settings.row_gap" @input="render()" class="w-full accent-purple-600">
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
                                    <div class="flex items-center gap-2">
                                        <input type="text" x-model="w.name" @input="render()" placeholder="വിദ്യാർത്ഥിയുടെ പേര് (Student Name)" class="flex-1 text-xs p-2 rounded-lg border border-slate-300 bg-white font-medium">
                                        <button type="button" @click="removeWinner('first', idx)" x-show="data.winners.first.length > 1" class="text-red-500 p-1">&times;</button>
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
                                    <div class="flex items-center gap-2">
                                        <input type="text" x-model="w.name" @input="render()" placeholder="വിദ്യാർത്ഥിയുടെ പേര് (Student Name)" class="flex-1 text-xs p-2 rounded-lg border border-slate-300 bg-white font-medium">
                                        <button type="button" @click="removeWinner('second', idx)" x-show="data.winners.second.length > 1" class="text-red-500 p-1">&times;</button>
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
                                    <div class="flex items-center gap-2">
                                        <input type="text" x-model="w.name" @input="render()" placeholder="വിദ്യാർത്ഥിയുടെ പേര് (Student Name)" class="flex-1 text-xs p-2 rounded-lg border border-slate-300 bg-white font-medium">
                                        <button type="button" @click="removeWinner('third', idx)" x-show="data.winners.third.length > 1" class="text-red-500 p-1">&times;</button>
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
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-3">Core Program Elements</h2>
                    
                    <!-- Result Number -->
                    <div class="space-y-2 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                            <span>Result Number (#{{ sprintf('%02d', $resultNo) }})</span>
                            <span class="font-mono text-purple-700" x-text="settings.result_size + 'px'"></span>
                        </div>
                        <input type="range" min="150" max="650" step="5" x-model.number="settings.result_size" @input="render()" class="w-full accent-purple-600">
                        <div class="flex items-center justify-between text-xs pt-1">
                            <span>Rotation</span>
                            <span class="font-mono" x-text="settings.result_rotation + '°'"></span>
                        </div>
                        <input type="range" min="-180" max="180" step="1" x-model.number="settings.result_rotation" @input="render()" class="w-full accent-purple-600">
                    </div>

                    <!-- Program Name -->
                    <div class="space-y-2 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                            <span>Program Name Size</span>
                            <span class="font-mono text-purple-700" x-text="settings.competition_size + 'px'"></span>
                        </div>
                        <input type="range" min="40" max="160" step="2" x-model.number="settings.competition_size" @input="render()" class="w-full accent-purple-600">
                        <div class="flex items-center justify-between text-xs pt-1">
                            <span>Rotation</span>
                            <span class="font-mono" x-text="settings.competition_rotation + '°'"></span>
                        </div>
                        <input type="range" min="-180" max="180" step="1" x-model.number="settings.competition_rotation" @input="render()" class="w-full accent-purple-600">
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

                <div class="flex items-center gap-1.5">
                    <button type="button" @click="addCustomText()" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold flex items-center gap-1">+ Text</button>
                    <button type="button" @click="addCustomShape('badge')" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold flex items-center gap-1">+ Badge</button>
                    <button type="button" @click="duplicateSelected()" :disabled="!selectedElement" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold disabled:opacity-40">Duplicate</button>
                    <button type="button" @click="deleteSelected()" :disabled="!selectedElement" class="px-2 py-1 rounded-lg bg-red-500/20 hover:bg-red-500/40 text-red-300 text-xs font-bold disabled:opacity-40">&times; Delete</button>
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

        savingDefault: false,
        publishing: false,
        statusMsg: '',
        statusType: 'success',

        templatesMap: @json($templatesMap),
        templateImageObj: null,

        // Cyan Guidelines
        guidelines: {
            show: true,
            vertical: [205, 540, 740],
            horizontal: [145, 165, 779],
            newX: 205,
            newY: 200
        },

        // Dynamic Custom Elements Array
        customElements: [],

        // Core Settings
        settings: {
            template_id: {{ $activeTemplate ? $activeTemplate->id : 1 }},
            template_image: '{{ $activeTemplate ? asset($activeTemplate->image_path) : asset("/images/result-templates/1778705272_srt1-01.png") }}',
            layout_mode: '{{ $currentSettings["layout_mode"] ?? "default" }}',

            // Visibility
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
            medal_type: 'numbers', // 'numbers', 'badges', 'text', 'none'
            medal_size: 26,

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
                first: @json(!empty($winners['first']) ? array_values($winners['first']) : [['name' => 'വിദ്യാർത്ഥിയുടെ പേര് (Winner 1)', 'unit' => 'ഗ്രൂപ്പ് പേര്']]),
                second: @json(!empty($winners['second']) ? array_values($winners['second']) : [['name' => 'രണ്ടാം സ്ഥാനം (Winner 2)', 'unit' => 'ഗ്രൂപ്പ് പേര്']]),
                third: @json(!empty($winners['third']) ? array_values($winners['third']) : [['name' => 'മൂന്നാം സ്ഥാനം (Winner 3)', 'unit' => 'ഗ്രൂപ്പ് പേര്']])
            }
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

        init() {
            // Restore saved custom elements if any
            const savedSettings = @json($currentSettings);
            if (savedSettings && savedSettings.custom_elements && Array.isArray(savedSettings.custom_elements)) {
                this.customElements = savedSettings.custom_elements.map(el => {
                    if (el.type === 'image' && el.imgSrc) {
                        const img = new Image();
                        img.src = el.imgSrc;
                        el.imgObj = img;
                    }
                    return el;
                });
            } else {
                // Default stylized "Result" text preset like Illustrator template
                this.customElements.push({
                    id: 'text_result_default',
                    type: 'text',
                    label: 'Result Text Art',
                    text: 'Result',
                    font: 'Rockwell Std',
                    size: 140,
                    weight: '400',
                    color: '#000000',
                    align: 'left',
                    tracking: 0,
                    lineHeight: 1.2,
                    rotation: 0,
                    opacity: 100,
                    x: 480,
                    y: 830,
                    visible: true,
                    layerOrder: 'below'
                });
            }

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
            this.settings.layout_mode = layoutMode || 'default';
            this.loadTemplateImage(src);
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
            this.selectElement(newId);
            this.activeTab = 'design';
            this.render();
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
            this.selectElement(newId);
            this.activeTab = 'design';
            this.render();
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
                    this.selectElement(newId);
                    this.activeTab = 'design';
                    this.render();
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
                this.selectElement(clone.id);
                this.render();
            }
        },

        deleteSelected() {
            if (this.selectedType === 'custom' && this.selectedId) {
                this.deleteElement(this.selectedId);
            }
        },

        deleteElement(id) {
            this.customElements = this.customElements.filter(el => el.id !== id);
            this.selectedId = null;
            this.selectedType = null;
            this.render();
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

        // Hit Detection for Interactive Canvas Dragging
        hitTest(x, y) {
            // Check custom elements in reverse order (topmost first)
            for (let i = this.customElements.length - 1; i >= 0; i--) {
                const el = this.customElements[i];
                if (!el.visible) continue;
                const w = el.width || (el.size ? (el.text ? el.text.length * el.size * 0.55 : el.size) : 100);
                const h = el.height || (el.size || 50);
                if (x >= el.x - 25 && x <= el.x + w + 25 && y >= el.y - 25 && y <= el.y + h + 25) {
                    return { type: 'custom', id: el.id, el: el };
                }
            }

            // Check Result Number
            if (this.settings.show_result_no) {
                const rx = this.settings.result_x;
                const ry = this.settings.result_y;
                if (x >= rx - 100 && x <= rx + 300 && y >= ry - 50 && y <= ry + 250) {
                    return { type: 'core', id: 'result_no' };
                }
            }

            // Check Program Name
            if (this.settings.show_competition) {
                const cx = this.settings.competition_x;
                const cy = this.settings.competition_y;
                if (x >= cx - 20 && x <= cx + (this.settings.competition_max_width || 440) && y >= cy - 20 && y <= cy + 200) {
                    return { type: 'core', id: 'competition' };
                }
            }

            // Check Zone Capsule
            if (this.settings.show_category) {
                const zx = this.settings.category_x;
                const zy = this.settings.category_y;
                if (x >= zx - 120 && x <= zx + 120 && y >= zy - 35 && y <= zy + 35) {
                    return { type: 'core', id: 'category' };
                }
            }

            // Check Winners
            if (this.settings.show_winners) {
                const wx = this.settings.block_left;
                const wy = this.settings.first_top;
                if (x >= wx - 20 && x <= wx + 380 && y >= wy - 20 && y <= wy + 550) {
                    return { type: 'core', id: 'winners' };
                }
            }

            return null;
        },

        onMouseDown(e) {
            const coords = this.getCanvasCoords(e);
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
            } else {
                this.selectedId = null;
                this.selectedType = null;
                this.render();
            }
        },

        onMouseMove(e) {
            if (!this.isDragging) return;
            const coords = this.getCanvasCoords(e);
            const dx = coords.x - this.dragStartX;
            const dy = coords.y - this.dragStartY;

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
        },

        onMouseUp() {
            this.isDragging = false;
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
        },

        removeGuide(type, index) {
            if (type === 'v') this.guidelines.vertical.splice(index, 1);
            else this.guidelines.horizontal.splice(index, 1);
            this.render();
        },

        // Render Canvas Pipeline
        render(isExport = false) {
            const canvas = document.getElementById('posterCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const width = canvas.width;
            const height = canvas.height;

            ctx.clearRect(0, 0, width, height);

            // 1. Draw Template Background
            if (this.templateImageObj && this.templateImageObj.complete) {
                ctx.drawImage(this.templateImageObj, 0, 0, width, height);
            } else {
                ctx.fillStyle = '#1e1b4b';
                ctx.fillRect(0, 0, width, height);
            }

            // 2. Draw Custom Elements with layerOrder === 'below'
            this.drawCustomElements(ctx, 'below');

            // 3. Draw Zone Capsule
            if (this.settings.show_category && this.data.category) {
                ctx.save();
                ctx.translate(this.settings.category_x, this.settings.category_y);
                if (this.settings.category_rotation) {
                    ctx.rotate((this.settings.category_rotation * Math.PI) / 180);
                }
                ctx.fillStyle = this.settings.category_color || '#FFFFFF';
                ctx.font = `${this.settings.category_weight || '400'} ${this.settings.category_size || 45}px "Rockwell Std", serif`;
                ctx.textAlign = this.settings.category_align || 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(this.data.category.toUpperCase(), 0, 0);
                ctx.restore();
            }

            // 4. Draw Program Name
            if (this.settings.show_competition && this.data.competition) {
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

                const lines = this.wrapText(ctx, this.data.competition, this.settings.competition_max_width || 440);
                let lineY = 0;
                for (let l of lines) {
                    ctx.fillText(l, 0, lineY);
                    lineY += (this.settings.competition_line_height || 94);
                }
                ctx.restore();
            }

            // 5. Draw Result Number (Mongolian Baiti)
            if (this.settings.show_result_no && this.data.result_no) {
                ctx.save();
                ctx.translate(this.settings.result_x, this.settings.result_y);
                if (this.settings.result_rotation) {
                    ctx.rotate((this.settings.result_rotation * Math.PI) / 180);
                }
                ctx.fillStyle = this.settings.result_color || '#D02627';
                ctx.font = `${this.settings.result_weight || '400'} ${this.settings.result_size || 480}px "Mongolian Baiti", serif`;
                ctx.textBaseline = 'top';
                ctx.textAlign = 'left';
                if ('letterSpacing' in ctx) ctx.letterSpacing = '-12px';
                ctx.fillText(this.data.result_no, 0, 0);
                ctx.restore();
            }

            // 6. Draw Podium Winners
            if (this.settings.show_winners) {
                this.drawPodiumWinners(ctx);
            }

            // 7. Draw Custom Elements with layerOrder === 'above'
            this.drawCustomElements(ctx, 'above');

            // 8. If Preview (!isExport), draw Selection Box & Guidelines
            if (!isExport) {
                this.drawSelectionOutline(ctx);
                if (this.guidelines.show) {
                    this.drawGuidelines(ctx, width, height);
                }
            }
        },

        drawCustomElements(ctx, orderFilter) {
            this.customElements.forEach(el => {
                if (!el.visible) return;
                if ((el.layerOrder || 'above') !== orderFilter) return;

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
            });
        },

        drawPodiumWinners(ctx) {
            const flatWinners = [];
            const addPlace = (arr, rank, label, defaultName) => {
                if (Array.isArray(arr) && arr.length > 0) {
                    arr.forEach(w => {
                        const name = (w && w.name && w.name.trim()) ? w.name : defaultName;
                        const unit = (w && w.unit && w.unit.trim()) ? w.unit : 'ഗ്രൂപ്പ് / ടീം';
                        flatWinners.push({ rank, rankLabel: label, name, unit });
                    });
                } else {
                    flatWinners.push({ rank, rankLabel: label, name: defaultName, unit: 'ഗ്രൂപ്പ് / ടീം' });
                }
            };
            addPlace(this.data.winners.first, '01', '1st Place', 'വിദ്യാർത്ഥിയുടെ പേര് (Winner 1)');
            addPlace(this.data.winners.second, '02', '2nd Place', 'രണ്ടാം സ്ഥാനം (Winner 2)');
            addPlace(this.data.winners.third, '03', '3rd Place', 'മൂന്നാം സ്ഥാനം (Winner 3)');

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
            const nameLineH = Math.max(16, Math.round((this.settings.winner_name_line_height || 38) * finalScale));
            const unitGap = Math.max(4, Math.round((this.settings.winner_unit_gap || 10) * finalScale));
            const blockLeft = this.settings.block_left || 205;
            const dividerWidth = Math.round(280 * Math.min(1.0, finalScale * 1.1));

            let currY = startY;
            flatWinners.forEach(w => {
                ctx.save();
                ctx.textAlign = 'left';
                ctx.textBaseline = 'top';

                // Medal / Rank
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

                // Student Name (2-line support)
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

                // Group Name
                let unitY = nameY + winSize + unitGap;
                ctx.fillStyle = this.settings.winner_unit_color || '#000000';
                ctx.font = `${this.settings.winner_unit_weight || '300'} ${unitSize}px "Rockwell Std", serif`;
                if ('letterSpacing' in ctx) ctx.letterSpacing = '-0.5px';
                ctx.fillText(w.unit, blockLeft, unitY);

                // Divider Line
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
        },

        drawSelectionOutline(ctx) {
            if (!this.selectedId) return;

            let bx = 0, by = 0, bw = 0, bh = 0;

            if (this.selectedType === 'custom' && this.selectedElement) {
                const el = this.selectedElement;
                bx = el.x;
                by = el.y;
                bw = el.width || (el.size ? (el.text ? el.text.length * el.size * 0.55 : el.size) : 100);
                bh = el.height || (el.size || 50);
            } else if (this.selectedType === 'core') {
                if (this.selectedId === 'result_no') {
                    bx = this.settings.result_x;
                    by = this.settings.result_y;
                    bw = 250;
                    bh = 220;
                } else if (this.selectedId === 'competition') {
                    bx = this.settings.competition_x;
                    by = this.settings.competition_y;
                    bw = this.settings.competition_max_width || 440;
                    bh = 160;
                } else if (this.selectedId === 'category') {
                    bx = this.settings.category_x - 100;
                    by = this.settings.category_y - 30;
                    bw = 200;
                    bh = 60;
                } else if (this.selectedId === 'winners') {
                    bx = this.settings.block_left;
                    by = this.settings.first_top;
                    bw = 360;
                    bh = 480;
                }
            }

            if (bw > 0 && bh > 0) {
                ctx.save();
                ctx.strokeStyle = '#00f0ff';
                ctx.lineWidth = 1.5;
                ctx.setLineDash([4, 4]);
                ctx.strokeRect(bx - 6, by - 6, bw + 12, bh + 12);

                // Small Corner Handles
                ctx.fillStyle = '#00f0ff';
                ctx.fillRect(bx - 10, by - 10, 8, 8);
                ctx.fillRect(bx + bw + 2, by - 10, 8, 8);
                ctx.fillRect(bx - 10, by + bh + 2, 8, 8);
                ctx.fillRect(bx + bw + 2, by + bh + 2, 8, 8);

                // Small badge
                ctx.fillStyle = 'rgba(15, 23, 42, 0.9)';
                ctx.fillRect(bx - 6, by - 26, 120, 18);
                ctx.fillStyle = '#00f0ff';
                ctx.font = 'bold 10px monospace';
                ctx.fillText(`X:${bx} Y:${by}`, bx, by - 13);

                ctx.restore();
            }
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
            this.render(true);
            const canvas = document.getElementById('posterCanvas');
            const link = document.createElement('a');
            link.download = `QUAF_Result_${this.data.result_no}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
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
                        custom_elements: this.customElements.map(el => {
                            const clone = Object.assign({}, el);
                            delete clone.imgObj;
                            return clone;
                        }),
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
            .then(res => res.json())
            .then(data => {
                this.publishing = false;
                this.render(false);
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
