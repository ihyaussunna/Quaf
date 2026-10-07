@extends('layouts.media')

@section('title', 'Customize Template: ' . $template->name . ' | QUAF Media')

@section('content')
<div class="space-y-6" x-data="templateCustomizer()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('media.results.templates') }}" class="text-xs text-slate-500 hover:text-slate-800 font-mono font-bold">&larr; Templates</a>
                <span class="text-slate-300">•</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-100 text-purple-800">
                    Visual Layout Customizer
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1 font-sora">
                Customize Template Layout: <span class="text-[#be1e2d]">{{ $template->name }}</span>
            </h1>
            <p class="text-xs text-slate-600 mt-1 max-w-2xl font-ml">
                ഓരോ റിസൾട്ട് ടെംപ്ലേറ്റിലെയും റിസൾട്ട് നമ്പർ, സോൺ / കാറ്റഗറി, പ്രോഗ്രാം നാമം, 1st, 2nd, 3rd സ്ഥാനങ്ങൾ, മത്സരാർത്ഥികളുടെ പേര്, ടീം നാമം എന്നിവയുടെ സ്ഥാനം (X, Y), വലിപ്പം, കളർ എന്നിവ ക്രമീകരിച്ച് സേവ് ചെയ്യുക.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" @click="resetToDefault()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors">
                റീസെറ്റ് (Reset)
            </button>
            <button type="button" @click="saveSettings()" :disabled="saving" class="px-5 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs shadow-xs transition-colors flex items-center gap-2 disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span x-text="saving ? 'സേവ് ചെയ്യുന്നു...' : 'സേവ് ചെയ്യുക (Save Layout)'"></span>
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

    <!-- Main Layout: Controls Panel (Left) & Live Canvas Preview (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Controls Column (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs divide-y divide-slate-100 overflow-hidden text-xs">
                
                <!-- 1. Result Number / Code -->
                <div class="p-4" x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between font-bold text-slate-800">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span>1. Result Number / Code (റിസൾട്ട് നമ്പർ)</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" class="mt-4 space-y-3">
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
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Font Size (<span x-text="settings.result_size"></span>px)</label>
                                <input type="number" min="10" max="200" x-model.number="settings.result_size" @input="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Weight</label>
                                <select x-model="settings.result_weight" @change="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                                    <option value="400">400 Normal</option>
                                    <option value="600">600 SemiBold</option>
                                    <option value="700">700 Bold</option>
                                    <option value="800">800 ExtraBold</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Color</label>
                                <input type="color" x-model="settings.result_color" @input="render()" class="w-full h-7 rounded border border-slate-300 cursor-pointer p-0.5">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Program Zone / Category -->
                <div class="p-4" x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between font-bold text-slate-800">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>2. Program Zone / Category (സോൺ / കാറ്റഗറി)</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" class="mt-4 space-y-3">
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
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Font Size (<span x-text="settings.category_size"></span>px)</label>
                                <input type="number" min="10" max="120" x-model.number="settings.category_size" @input="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Alignment</label>
                                <select x-model="settings.category_align" @change="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                                    <option value="center">Center</option>
                                    <option value="left">Left</option>
                                    <option value="right">Right</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Color</label>
                                <input type="color" x-model="settings.category_color" @input="render()" class="w-full h-7 rounded border border-slate-300 cursor-pointer p-0.5">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Program / Competition Name -->
                <div class="p-4" x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between font-bold text-slate-800">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            <span>3. Program / Competition Name (പ്രോഗ്രാം നാമം)</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" class="mt-4 space-y-3">
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
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Font Size (<span x-text="settings.competition_size"></span>px)</label>
                                <input type="number" min="15" max="140" x-model.number="settings.competition_size" @input="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Alignment</label>
                                <select x-model="settings.competition_align" @change="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                                    <option value="center">Center</option>
                                    <option value="left">Left</option>
                                    <option value="right">Right</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Color</label>
                                <input type="color" x-model="settings.competition_color" @input="render()" class="w-full h-7 rounded border border-slate-300 cursor-pointer p-0.5">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Max Width (<span x-text="settings.competition_max_width"></span>px)</label>
                                <input type="range" min="200" max="1000" step="10" x-model.number="settings.competition_max_width" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Line Height (<span x-text="settings.competition_line_height"></span>px)</label>
                                <input type="range" min="20" max="100" step="2" x-model.number="settings.competition_line_height" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Winners Podium (1st, 2nd, 3rd) Placement -->
                <div class="p-4" x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between font-bold text-slate-800">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span>4. Winners Podium 1st, 2nd, 3rd (വിജയികളുടെ നിര)</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" class="mt-4 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Left Margin X (<span x-text="settings.block_left"></span>px)</label>
                                <input type="range" min="50" max="800" step="2" x-model.number="settings.block_left" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">1st Place Top Y (<span x-text="settings.first_top"></span>px)</label>
                                <input type="range" min="200" max="1000" step="2" x-model.number="settings.first_top" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Row Gap (<span x-text="settings.row_gap"></span>px)</label>
                                <input type="range" min="40" max="200" step="2" x-model.number="settings.row_gap" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Item Gap (<span x-text="settings.item_gap"></span>px)</label>
                                <input type="range" min="4" max="60" step="1" x-model.number="settings.item_gap" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Medal Size (<span x-text="settings.medal_size"></span>px)</label>
                                <input type="range" min="20" max="100" step="2" x-model.number="settings.medal_size" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Winner Participant Name -->
                <div class="p-4" x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between font-bold text-slate-800">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>5. Participant Name (മത്സരാർത്ഥിയുടെ പേര്)</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" class="mt-4 space-y-3">
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Font Size (<span x-text="settings.winner_name_size"></span>px)</label>
                                <input type="number" min="10" max="80" x-model.number="settings.winner_name_size" @input="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Weight</label>
                                <select x-model="settings.winner_name_weight" @change="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                                    <option value="400">400 Normal</option>
                                    <option value="600">600 SemiBold</option>
                                    <option value="700">700 Bold</option>
                                    <option value="800">800 ExtraBold</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Color</label>
                                <input type="color" x-model="settings.winner_name_color" @input="render()" class="w-full h-7 rounded border border-slate-300 cursor-pointer p-0.5">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. Team / Group Name -->
                <div class="p-4" x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between font-bold text-slate-800">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                            <span>6. Team / Group Name (പേരിന് താഴെ ടീം നാമം)</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" class="mt-4 space-y-3">
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Font Size (<span x-text="settings.winner_unit_size"></span>px)</label>
                                <input type="number" min="8" max="60" x-model.number="settings.winner_unit_size" @input="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Weight</label>
                                <select x-model="settings.winner_unit_weight" @change="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                                    <option value="300">300 Light</option>
                                    <option value="400">400 Normal</option>
                                    <option value="600">600 SemiBold</option>
                                    <option value="700">700 Bold</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Color</label>
                                <input type="color" x-model="settings.winner_unit_color" @input="render()" class="w-full h-7 rounded border border-slate-300 cursor-pointer p-0.5">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Canvas Preview Column (7 cols) -->
        <div class="lg:col-span-7 sticky top-20">
            <div class="bg-white p-4 sm:p-6 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Live Template Preview</h2>
                        <p class="text-[11px] text-slate-400 font-mono">1080 &times; 1080 Canvas &bull; Real-time Alignment</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[11px] font-mono text-slate-500 font-semibold">Live Rendering</span>
                    </div>
                </div>

                <!-- Canvas Wrapper -->
                <div class="relative w-full aspect-square bg-slate-950 rounded-2xl overflow-hidden shadow-inner flex items-center justify-center border border-slate-800">
                    <canvas id="customizerCanvas" width="1080" height="1080" class="w-full h-full object-contain"></canvas>
                </div>

                <div class="text-[11px] text-slate-500 font-mono flex items-center justify-between px-1">
                    <span>Template: {{ $template->name }}</span>
                    <span>Saved in Database: PosterSetting #{{ $template->posterSetting?->id ?? 'New' }}</span>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function templateCustomizer() {
    return {
        saving: false,
        statusMsg: '',
        statusType: 'success',

        templateImageObj: null,
        medals: {
            first: null,
            second: null,
            third: null
        },

        defaultSettings: @json(\App\Models\PosterSetting::defaultSettings()),

        settings: {
            result_x: {{ (float) ($settings['result_x'] ?? 733) }},
            result_y: {{ (float) ($settings['result_y'] ?? 238) }},
            result_size: {{ (float) ($settings['result_size'] ?? 78) }},
            result_weight: '{{ $settings["result_weight"] ?? "700" }}',
            result_color: '{{ (strlen($settings["result_color"] ?? "") === 7) ? $settings["result_color"] : "#be1e2d" }}',

            category_x: {{ (float) ($settings['category_x'] ?? 540) }},
            category_y: {{ (float) ($settings['category_y'] ?? 286) }},
            category_size: {{ (float) ($settings['category_size'] ?? 31) }},
            category_weight: '{{ $settings["category_weight"] ?? "400" }}',
            category_color: '{{ (strlen($settings["category_color"] ?? "") === 7) ? $settings["category_color"] : "#ffffff" }}',
            category_align: '{{ $settings["category_align"] ?? "center" }}',

            competition_x: {{ (float) ($settings['competition_x'] ?? 540) }},
            competition_y: {{ (float) ($settings['competition_y'] ?? 338) }},
            competition_size: {{ (float) ($settings['competition_size'] ?? 46) }},
            competition_weight: '{{ $settings["competition_weight"] ?? "700" }}',
            competition_color: '{{ (strlen($settings["competition_color"] ?? "") === 7) ? $settings["competition_color"] : "#ffffff" }}',
            competition_align: '{{ $settings["competition_align"] ?? "center" }}',
            competition_max_width: {{ (float) ($settings['competition_max_width'] ?? 650) }},
            competition_line_height: {{ (float) ($settings['competition_line_height'] ?? 46) }},

            block_left: {{ (float) ($settings['block_left'] ?? 380) }},
            first_top: {{ (float) ($settings['first_top'] ?? 460) }},
            row_gap: {{ (float) ($settings['row_gap'] ?? 98) }},
            item_gap: {{ (float) ($settings['item_gap'] ?? 14) }},
            medal_size: {{ (float) ($settings['medal_size'] ?? 60) }},

            winner_name_size: {{ (float) ($settings['winner_name_size'] ?? 32) }},
            winner_name_weight: '{{ $settings["winner_name_weight"] ?? "700" }}',
            winner_name_color: '{{ (strlen($settings["winner_name_color"] ?? "") === 7) ? $settings["winner_name_color"] : "#ffffff" }}',

            winner_unit_size: {{ (float) ($settings['winner_unit_size'] ?? 22) }},
            winner_unit_weight: '{{ $settings["winner_unit_weight"] ?? "400" }}',
            winner_unit_color: '{{ (strlen($settings["winner_unit_color"] ?? "") === 7) ? $settings["winner_unit_color"] : "#e2e8f0" }}',
        },

        data: @json($sampleData),

        init() {
            const loadImg = (src) => {
                const img = new Image();
                img.crossOrigin = "anonymous";
                img.src = src;
                return img;
            };

            this.medals.first = loadImg('{{ asset("images/medals/first.png") }}');
            this.medals.second = loadImg('{{ asset("images/medals/second.png") }}');
            this.medals.third = loadImg('{{ asset("images/medals/third.png") }}');

            this.templateImageObj = loadImg('{{ asset($template->image_path) }}');
            this.templateImageObj.onload = () => {
                this.render();
            };

            if (document.fonts) {
                document.fonts.ready.then(() => {
                    this.render();
                });
            }

            setTimeout(() => this.render(), 100);
        },

        resetToDefault() {
            if (!confirm('എല്ലാ കോർഡിനേറ്റുകളും കളറുകളും ഡിഫോൾട്ട് രീതിയിലേക്ക് റീസെറ്റ് ചെയ്യണോ?')) return;
            Object.assign(this.settings, this.defaultSettings);
            this.render();
        },

        render() {
            const canvas = document.getElementById('customizerCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            ctx.clearRect(0, 0, 1080, 1080);

            // 1. Draw Template Background
            if (this.templateImageObj && this.templateImageObj.complete && this.templateImageObj.naturalWidth > 0) {
                ctx.drawImage(this.templateImageObj, 0, 0, 1080, 1080);
            } else {
                ctx.fillStyle = '#0f172a';
                ctx.fillRect(0, 0, 1080, 1080);
            }

            // 2. Draw Result Number / Code
            ctx.save();
            ctx.fillStyle = this.settings.result_color;
            ctx.font = `${this.settings.result_weight} ${this.settings.result_size}px "JetBrains Mono", Sora, sans-serif`;
            ctx.textBaseline = 'top';
            ctx.fillText(this.data.result_no, this.settings.result_x, this.settings.result_y);
            ctx.restore();

            // 3. Draw Category / Zone
            ctx.save();
            ctx.fillStyle = this.settings.category_color;
            ctx.font = `${this.settings.category_weight} ${this.settings.category_size}px "Anek Malayalam", Sora, sans-serif`;
            ctx.textAlign = this.settings.category_align;
            ctx.textBaseline = 'top';
            ctx.fillText(this.data.category, this.settings.category_x, this.settings.category_y);
            ctx.restore();

            // 4. Draw Competition / Program Name
            ctx.save();
            ctx.fillStyle = this.settings.competition_color;
            ctx.font = `${this.settings.competition_weight} ${this.settings.competition_size}px "Anek Malayalam", Sora, sans-serif`;
            ctx.textAlign = this.settings.competition_align;
            ctx.textBaseline = 'top';

            const compWords = this.data.competition.split(' ');
            let line = '';
            let currentY = this.settings.competition_y;
            const maxW = this.settings.competition_max_width;
            const lineH = this.settings.competition_line_height;

            for (let n = 0; n < compWords.length; n++) {
                const testLine = line + compWords[n] + ' ';
                const metrics = ctx.measureText(testLine);
                if (metrics.width > maxW && n > 0) {
                    ctx.fillText(line, this.settings.competition_x, currentY);
                    line = compWords[n] + ' ';
                    currentY += lineH;
                } else {
                    line = testLine;
                }
            }
            ctx.fillText(line, this.settings.competition_x, currentY);
            ctx.restore();

            // 5. Draw Winners: 1st, 2nd, and 3rd Podium
            const winnerKeys = ['first', 'second', 'third'];
            const blockLeft = this.settings.block_left;
            const firstTop = this.settings.first_top;
            const rowGap = this.settings.row_gap;
            const itemGap = this.settings.item_gap;
            const medalSize = this.settings.medal_size;

            winnerKeys.forEach((key, index) => {
                const winnerList = this.data.winners[key] || [];
                const winner = winnerList[0];
                if (!winner) return;

                const rowTop = firstTop + (index * rowGap);

                // Draw Medal Image
                const medalImg = this.medals[key];
                if (medalImg && medalImg.complete && medalImg.naturalWidth > 0) {
                    ctx.drawImage(medalImg, blockLeft, rowTop, medalSize, medalSize);
                } else {
                    ctx.save();
                    ctx.fillStyle = index === 0 ? '#fbbf24' : (index === 1 ? '#94a3b8' : '#b45309');
                    ctx.beginPath();
                    ctx.arc(blockLeft + (medalSize / 2), rowTop + (medalSize / 2), medalSize / 2, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.restore();
                }

                const textLeft = blockLeft + medalSize + itemGap;

                // Draw Winner Participant Name
                ctx.save();
                ctx.fillStyle = this.settings.winner_name_color;
                ctx.font = `${this.settings.winner_name_weight} ${this.settings.winner_name_size}px "Anek Malayalam", Sora, sans-serif`;
                ctx.textBaseline = 'top';
                ctx.textAlign = 'left';
                ctx.fillText(winner.name, textLeft, rowTop + 2);
                ctx.restore();

                // Draw Winner Team / Unit Name
                if (winner.unit) {
                    ctx.save();
                    ctx.fillStyle = this.settings.winner_unit_color;
                    ctx.font = `${this.settings.winner_unit_weight} ${this.settings.winner_unit_size}px "Anek Malayalam", Sora, sans-serif`;
                    ctx.textBaseline = 'top';
                    ctx.textAlign = 'left';
                    const unitTop = rowTop + this.settings.winner_name_size + 6;
                    ctx.fillText(winner.unit, textLeft, unitTop);
                    ctx.restore();
                }
            });
        },

        async saveSettings() {
            this.saving = true;
            this.statusMsg = '';

            try {
                const response = await fetch('{{ route("media.results.templates.save-customization", $template) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        settings: this.settings
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.statusType = 'success';
                    this.statusMsg = data.message || 'ലേഔട്ട് സെറ്റിംഗ്സുകൾ സേവ് ചെയ്തു!';
                } else {
                    this.statusType = 'error';
                    this.statusMsg = data.message || 'സേവ് ചെയ്യാൻ സാധിച്ചില്ല.';
                }
            } catch (err) {
                this.statusType = 'error';
                this.statusMsg = 'Error saving settings: ' + err.message;
            } finally {
                this.saving = false;
            }
        }
    };
}
</script>
@endsection
