@extends('layouts.media')

@section('title', 'Poster Studio: ' . $result->program->name . ' | QUAF 09 Media')

@section('content')
<div class="space-y-6" x-data="posterStudio()">
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
            </div>

            <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight mt-1.5 font-sora">
                {{ $result->program->name }}
            </h1>
            <p class="text-xs text-slate-500">
                Category: <strong class="text-slate-700">{{ $result->program->category->name ?? 'General' }}</strong> &bull;
                Stage: <strong class="text-slate-700">{{ $result->program->stage->name ?? 'Stage' }}</strong> &bull;
                Podium Winners: <strong class="text-slate-700">1st, 2nd, and 3rd Places Only</strong>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" @click="saveAsDefault()" :disabled="savingDefault"
                    class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors disabled:opacity-50">
                <span x-text="savingDefault ? 'Saving Defaults...' : 'Save as Template Default'"></span>
            </button>

            <button type="button" @click="downloadPoster()"
                    class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-2 transition-colors shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Download High-Res PNG</span>
            </button>

            <button type="button" @click="saveAndPublish()" :disabled="publishing"
                    class="px-5 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-black text-xs flex items-center gap-2 transition-all shadow-md shadow-[#be1e2d]/20 disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span x-text="publishing ? 'Publishing...' : 'Save & Publish Poster'"></span>
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
            
            <!-- 1. Template Selector -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">1. Poster Frame Template</h2>
                    <a href="{{ route('media.results.templates') }}" target="_blank" class="text-[11px] text-[#be1e2d] hover:underline font-bold">+ Upload New</a>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($templates as $tpl)
                        <label class="cursor-pointer border-2 rounded-xl p-2 flex flex-col items-center text-center transition-all"
                               :class="settings.template_id == {{ $tpl->id }} ? 'border-[#be1e2d] bg-red-50/30 ring-1 ring-[#be1e2d]' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="template_id" value="{{ $tpl->id }}" class="sr-only"
                                   @change="selectTemplate({{ $tpl->id }}, '{{ asset($tpl->image_path) }}')"
                                   :checked="settings.template_id == {{ $tpl->id }}">
                            <div class="w-full aspect-square bg-slate-900 rounded-lg overflow-hidden mb-2">
                                <img src="{{ asset($tpl->image_path) }}" alt="{{ $tpl->name }}" class="w-full h-full object-cover">
                            </div>
                            <span class="text-[11px] font-bold text-slate-800 line-clamp-1">{{ $tpl->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Accordion Settings -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs divide-y divide-slate-100 overflow-hidden text-xs">
                
                <!-- 2. Result Number / Code -->
                <div class="p-4" x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between font-bold text-slate-800">
                        <span>Result Code / Number Settings</span>
                        <svg class="w-4 h-4 transition-transform text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" class="mt-4 space-y-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Code / Text</label>
                            <input type="text" x-model="data.result_no" @input="render()" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 font-mono">
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
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Size (<span x-text="settings.result_size"></span>px)</label>
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

                <!-- 3. Category Settings -->
                <div class="p-4" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between font-bold text-slate-800">
                        <span>Category Settings</span>
                        <svg class="w-4 h-4 transition-transform text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" class="mt-4 space-y-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Category Title</label>
                            <input type="text" x-model="data.category" @input="render()" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300">
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
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Size (<span x-text="settings.category_size"></span>px)</label>
                                <input type="number" min="10" max="150" x-model.number="settings.category_size" @input="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Align</label>
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

                <!-- 4. Competition Name Settings -->
                <div class="p-4" x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between font-bold text-slate-800">
                        <span>Competition Name</span>
                        <svg class="w-4 h-4 transition-transform text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" class="mt-4 space-y-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Competition Name Text</label>
                            <input type="text" x-model="data.competition" @input="render()" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300">
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
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Font Size (<span x-text="settings.competition_size"></span>px)</label>
                                <input type="number" min="10" max="150" x-model.number="settings.competition_size" @input="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Max Width (<span x-text="settings.competition_max_width"></span>px)</label>
                                <input type="number" min="100" max="1080" step="10" x-model.number="settings.competition_max_width" @input="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Font Family</label>
                                <select x-model="settings.competition_font" @change="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                                    <option value="Anek Malayalam">Anek Malayalam</option>
                                    <option value="Sora">Sora</option>
                                    <option value="Rockwell Std">Rockwell Std</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Color</label>
                                <input type="color" x-model="settings.competition_color" @input="render()" class="w-full h-7 rounded border border-slate-300 cursor-pointer p-0.5">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Winner Block & Line Spacing -->
                <div class="p-4" x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between font-bold text-slate-800">
                        <span class="flex items-center gap-2">
                            <span>Winner Block & Line Spacing</span>
                            <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-900 text-[10px] font-bold">Key</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" class="mt-4 space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Block Left (X) (<span x-text="settings.block_left"></span>px)</label>
                                <input type="range" min="0" max="1080" step="1" x-model.number="settings.block_left" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">First Winner Top (Y) (<span x-text="settings.first_top"></span>px)</label>
                                <input type="range" min="0" max="1350" step="1" x-model.number="settings.first_top" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                        </div>

                        <!-- Row Gap (Line Distance) Highlighted -->
                        <div class="p-3 bg-amber-50/60 rounded-xl border border-amber-200/70">
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-bold text-amber-900">Line Distance / Row Gap</label>
                                <span class="font-mono font-bold text-amber-900 text-xs" x-text="settings.row_gap + ' px'"></span>
                            </div>
                            <input type="range" min="20" max="250" step="1" x-model.number="settings.row_gap" @input="render()" class="w-full accent-[#be1e2d]">
                            <p class="text-[10px] text-amber-800 mt-1">Adjust vertical distance between 1st, 2nd, and 3rd winner lines.</p>
                        </div>

                        <!-- Item Gap (Medal to text) -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Item Gap (Medal & Name) (<span x-text="settings.item_gap"></span>px)</label>
                                <input type="range" min="0" max="80" step="1" x-model.number="settings.item_gap" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Medal Size (<span x-text="settings.medal_size"></span>px)</label>
                                <input type="range" min="20" max="150" step="1" x-model.number="settings.medal_size" @input="render()" class="w-full accent-[#be1e2d]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. Winner Names & Units Styling -->
                <div class="p-4" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between font-bold text-slate-800">
                        <span>Winner Typography & Colors</span>
                        <svg class="w-4 h-4 transition-transform text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" class="mt-4 space-y-3">
                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Name Font Family</label>
                                <select x-model="settings.winner_font" @change="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                                    <option value="Anek Malayalam">Anek Malayalam</option>
                                    <option value="Sora">Sora</option>
                                    <option value="Rockwell Std">Rockwell Std</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Name Color</label>
                                <input type="color" x-model="settings.winner_name_color" @input="render()" class="w-full h-7 rounded border border-slate-300 cursor-pointer p-0.5">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Name Size (<span x-text="settings.winner_name_size"></span>px)</label>
                                <input type="number" min="12" max="80" x-model.number="settings.winner_name_size" @input="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Name Weight</label>
                                <select x-model="settings.winner_name_weight" @change="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                                    <option value="400">400 Normal</option>
                                    <option value="600">600 SemiBold</option>
                                    <option value="700">700 Bold</option>
                                    <option value="800">800 ExtraBold</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Team/Unit Size (<span x-text="settings.winner_unit_size"></span>px)</label>
                                <input type="number" min="10" max="60" x-model.number="settings.winner_unit_size" @input="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Team Weight</label>
                                <select x-model="settings.winner_unit_weight" @change="render()" class="w-full px-2 py-1 text-xs rounded border border-slate-300">
                                    <option value="300">300 Light</option>
                                    <option value="400">400 Normal</option>
                                    <option value="600">600 SemiBold</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Team Color</label>
                                <input type="color" x-model="settings.winner_unit_color" @input="render()" class="w-full h-7 rounded border border-slate-300 cursor-pointer p-0.5">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 7. Edit Podium Winners (1st, 2nd, 3rd) -->
                <div class="p-4" x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between font-bold text-slate-800">
                        <span>Podium Winners Data (1st, 2nd, 3rd)</span>
                        <svg class="w-4 h-4 transition-transform text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" class="mt-4 space-y-3">
                        <!-- First Place -->
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center gap-2 mb-1.5">
                                <img src="{{ asset('images/medals/first.png') }}" alt="1st" class="w-4 h-4 object-contain">
                                <span class="font-bold text-[11px] text-slate-800">1st Prize Winner</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" x-model="data.winners.first[0].name" @input="render()" placeholder="Student Name" class="px-2 py-1 text-xs rounded border border-slate-300 bg-white">
                                <input type="text" x-model="data.winners.first[0].unit" @input="render()" placeholder="Team / Unit" class="px-2 py-1 text-xs rounded border border-slate-300 bg-white">
                            </div>
                        </div>

                        <!-- Second Place -->
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center gap-2 mb-1.5">
                                <img src="{{ asset('images/medals/second.png') }}" alt="2nd" class="w-4 h-4 object-contain">
                                <span class="font-bold text-[11px] text-slate-800">2nd Prize Winner</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" x-model="data.winners.second[0].name" @input="render()" placeholder="Student Name" class="px-2 py-1 text-xs rounded border border-slate-300 bg-white">
                                <input type="text" x-model="data.winners.second[0].unit" @input="render()" placeholder="Team / Unit" class="px-2 py-1 text-xs rounded border border-slate-300 bg-white">
                            </div>
                        </div>

                        <!-- Third Place -->
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center gap-2 mb-1.5">
                                <img src="{{ asset('images/medals/third.png') }}" alt="3rd" class="w-4 h-4 object-contain">
                                <span class="font-bold text-[11px] text-slate-800">3rd Prize Winner</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" x-model="data.winners.third[0].name" @input="render()" placeholder="Student Name" class="px-2 py-1 text-xs rounded border border-slate-300 bg-white">
                                <input type="text" x-model="data.winners.third[0].unit" @input="render()" placeholder="Team / Unit" class="px-2 py-1 text-xs rounded border border-slate-300 bg-white">
                            </div>
                        </div>
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
                        <p class="text-[11px] text-slate-500 font-mono">1080 &times; 1080 px High Definition</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" x-model="showGuides" @change="render()" class="rounded text-[#be1e2d] focus:ring-[#be1e2d]">
                            <span class="text-[11px] font-medium">Center Guides</span>
                        </label>
                    </div>
                </div>

                <!-- Live Canvas Display Area -->
                <div class="bg-slate-950 rounded-2xl overflow-hidden p-2 sm:p-4 flex items-center justify-center relative min-h-[420px]">
                    
                    <!-- Guide Crosshair -->
                    <div x-show="showGuides" class="absolute inset-0 pointer-events-none z-20 flex items-center justify-center">
                        <div class="w-px h-full bg-cyan-400/40 border-l border-dashed border-cyan-400"></div>
                        <div class="h-px w-full bg-cyan-400/40 border-t border-dashed border-cyan-400 absolute"></div>
                    </div>

                    <!-- The Real HTML5 Canvas (1080x1080 HD, scaled responsively via CSS) -->
                    <canvas id="posterCanvas" width="1080" height="1080"
                            class="w-full max-w-[540px] aspect-square object-contain shadow-2xl rounded-xl border border-slate-800 bg-black"></canvas>
                </div>

                <!-- Footer Quick Share & Download -->
                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-[11px] text-slate-500">
                        <span class="font-bold text-slate-700">Auto-Render:</span> Poster preview updates in real-time as you adjust settings.
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
        showGuides: false,
        savingDefault: false,
        publishing: false,
        statusMsg: '',
        statusType: 'success',

        templatesMap: @json($templatesMap),

        templateImageObj: null,
        medals: {
            first: null,
            second: null,
            third: null
        },

        // Settings initialized from Controller
        settings: {
            template_id: {{ $activeTemplate ? $activeTemplate->id : 1 }},
            template_image: '{{ $activeTemplate ? asset($activeTemplate->image_path) : asset("/images/result-templates/1778705272_srt1-01.png") }}',
            layout_mode: '{{ $currentSettings["layout_mode"] ?? "default" }}',

            result_x: {{ (float) ($currentSettings['result_x'] ?? 733) }},
            result_y: {{ (float) ($currentSettings['result_y'] ?? 238) }},
            result_size: {{ (float) ($currentSettings['result_size'] ?? 78) }},
            result_weight: '{{ $currentSettings["result_weight"] ?? "700" }}',
            result_color: '{{ (strlen($currentSettings["result_color"] ?? "") === 7) ? $currentSettings["result_color"] : "#be1e2d" }}',

            category_x: {{ (float) ($currentSettings['category_x'] ?? 540) }},
            category_y: {{ (float) ($currentSettings['category_y'] ?? 286) }},
            category_size: {{ (float) ($currentSettings['category_size'] ?? 31) }},
            category_weight: '{{ $currentSettings["category_weight"] ?? "400" }}',
            category_color: '{{ (strlen($currentSettings["category_color"] ?? "") === 7) ? $currentSettings["category_color"] : "#ffffff" }}',
            category_align: '{{ $currentSettings["category_align"] ?? "center" }}',

            competition_x: {{ (float) ($currentSettings['competition_x'] ?? 540) }},
            competition_y: {{ (float) ($currentSettings['competition_y'] ?? 338) }},
            competition_size: {{ (float) ($currentSettings['competition_size'] ?? 46) }},
            competition_weight: '{{ $currentSettings["competition_weight"] ?? "700" }}',
            competition_color: '{{ (strlen($currentSettings["competition_color"] ?? "") === 7) ? $currentSettings["competition_color"] : "#ffffff" }}',
            competition_align: '{{ $currentSettings["competition_align"] ?? "center" }}',
            competition_max_width: {{ (float) ($currentSettings['competition_max_width'] ?? 650) }},
            competition_line_height: {{ (float) ($currentSettings['competition_line_height'] ?? 46) }},

            block_left: {{ (float) ($currentSettings['block_left'] ?? 380) }},
            first_top: {{ (float) ($currentSettings['first_top'] ?? 460) }},
            row_gap: {{ (float) ($currentSettings['row_gap'] ?? 98) }},
            item_gap: {{ (float) ($currentSettings['item_gap'] ?? 14) }},
            medal_size: {{ (float) ($currentSettings['medal_size'] ?? 60) }},

            winner_name_size: {{ (float) ($currentSettings['winner_name_size'] ?? 32) }},
            winner_name_weight: '{{ $currentSettings["winner_name_weight"] ?? "700" }}',
            winner_name_color: '{{ (strlen($currentSettings["winner_name_color"] ?? "") === 7) ? $currentSettings["winner_name_color"] : "#ffffff" }}',

            winner_unit_size: {{ (float) ($currentSettings['winner_unit_size'] ?? 22) }},
            winner_unit_weight: '{{ $currentSettings["winner_unit_weight"] ?? "400" }}',
            winner_unit_color: '{{ (strlen($currentSettings["winner_unit_color"] ?? "") === 7) ? $currentSettings["winner_unit_color"] : "#e2e8f0" }}',

            competition_font: '{{ $currentSettings["competition_font"] ?? "Anek Malayalam" }}',
            winner_font: '{{ $currentSettings["winner_font"] ?? "Anek Malayalam" }}',
        },

        // Content Data
        data: {
            result_no: '{{ $result->program->code ?? "01" }}',
            category: '{{ $result->program->category->name ?? $result->program->eligibility ?? "General" }}',
            competition: '{{ $result->program->name }}',
            winners: {
                first: [
                    {
                        name: '{{ !empty($winners["first"]) ? addslashes($winners["first"][0]["name"]) : "1st Winner Name" }}',
                        unit: '{{ !empty($winners["first"]) ? addslashes($winners["first"][0]["unit"]) : "Team / Group" }}'
                    }
                ],
                second: [
                    {
                        name: '{{ !empty($winners["second"]) ? addslashes($winners["second"][0]["name"]) : "2nd Winner Name" }}',
                        unit: '{{ !empty($winners["second"]) ? addslashes($winners["second"][0]["unit"]) : "Team / Group" }}'
                    }
                ],
                third: [
                    {
                        name: '{{ !empty($winners["third"]) ? addslashes($winners["third"][0]["name"]) : "3rd Winner Name" }}',
                        unit: '{{ !empty($winners["third"]) ? addslashes($winners["third"][0]["unit"]) : "Team / Group" }}'
                    }
                ]
            }
        },

        init() {
            // Preload medals
            const loadImg = (src) => {
                const img = new Image();
                img.crossOrigin = "anonymous";
                img.src = src;
                return img;
            };

            this.medals.first = loadImg('{{ asset("images/medals/first.png") }}');
            this.medals.second = loadImg('{{ asset("images/medals/second.png") }}');
            this.medals.third = loadImg('{{ asset("images/medals/third.png") }}');

            // Preload template
            this.loadTemplateImage(this.settings.template_image);

            // Preload fonts for Canvas
            if (document.fonts) {
                document.fonts.ready.then(() => {
                    this.render();
                });
            }
        },

        loadTemplateImage(src) {
            this.templateImageObj = new Image();
            this.templateImageObj.crossOrigin = "anonymous";
            this.templateImageObj.src = src;
            this.templateImageObj.onload = () => {
                this.render();
            };
        },

        selectTemplate(id, src) {
            this.settings.template_id = id;
            this.settings.template_image = src;

            // Dynamically apply template-specific coordinates and colors
            if (this.templatesMap && this.templatesMap[id]) {
                const tplSettings = this.templatesMap[id];
                const numericKeys = [
                    'result_x', 'result_y', 'result_size',
                    'category_x', 'category_y', 'category_size',
                    'competition_x', 'competition_y', 'competition_size', 'competition_max_width', 'competition_line_height',
                    'block_left', 'first_top', 'row_gap', 'item_gap', 'medal_size',
                    'winner_name_size', 'winner_unit_size'
                ];
                const stringKeys = [
                    'result_weight', 'result_color',
                    'category_weight', 'category_color', 'category_align',
                    'competition_weight', 'competition_color', 'competition_align',
                    'winner_name_weight', 'winner_name_color',
                    'winner_unit_weight', 'winner_unit_color'
                ];

                numericKeys.forEach(k => {
                    if (tplSettings[k] !== undefined && tplSettings[k] !== null) {
                        this.settings[k] = parseFloat(tplSettings[k]);
                    }
                });

                stringKeys.forEach(k => {
                    if (tplSettings[k] !== undefined && tplSettings[k] !== null && tplSettings[k] !== '') {
                        this.settings[k] = tplSettings[k];
                    }
                });
            }

            this.loadTemplateImage(src);
        },

        render() {
            const canvas = document.getElementById('posterCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            // Clear
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

            // 3. Draw Category
            ctx.save();
            ctx.fillStyle = this.settings.category_color;
            ctx.font = `${this.settings.category_weight} ${this.settings.category_size}px "Anek Malayalam", Sora, sans-serif`;
            ctx.textAlign = this.settings.category_align;
            ctx.textBaseline = 'top';
            ctx.fillText(this.data.category, this.settings.category_x, this.settings.category_y);
            ctx.restore();

            // 4. Draw Competition Name (Multi-line text wrapping support)
            ctx.save();
            ctx.fillStyle = this.settings.competition_color;
            const compFont = this.settings.competition_font || 'Anek Malayalam';
            ctx.font = `${this.settings.competition_weight} ${this.settings.competition_size}px "${compFont}", "Anek Malayalam", Sora, sans-serif`;
            ctx.textAlign = this.settings.competition_align;
            ctx.textBaseline = 'top';

            const compLines = this.wrapText(ctx, this.data.competition, this.settings.competition_max_width);
            let compY = this.settings.competition_y;
            for (let line of compLines) {
                ctx.fillText(line, this.settings.competition_x, compY);
                compY += this.settings.competition_line_height;
            }
            ctx.restore();

            // 5. Draw Winners (Podium only: 1st, 2nd, 3rd)
            const winnerPositions = [
                { key: 'first', medal: this.medals.first },
                { key: 'second', medal: this.medals.second },
                { key: 'third', medal: this.medals.third }
            ];

            let currentY = this.settings.first_top;
            const blockLeft = this.settings.block_left;
            const medalSize = this.settings.medal_size;
            const itemGap = this.settings.item_gap;
            const rowGap = this.settings.row_gap;

            winnerPositions.forEach(pos => {
                const item = this.data.winners[pos.key][0];
                if (!item || !item.name) return;

                // Draw Medal
                if (pos.medal && pos.medal.complete && pos.medal.naturalWidth > 0) {
                    ctx.drawImage(pos.medal, blockLeft, currentY, medalSize, medalSize);
                }

                // Draw Name
                const textX = blockLeft + medalSize + itemGap;
                ctx.save();
                ctx.textAlign = 'left';
                ctx.textBaseline = 'top';

                ctx.fillStyle = this.settings.winner_name_color;
                const winFont = this.settings.winner_font || 'Anek Malayalam';
                ctx.font = `${this.settings.winner_name_weight} ${this.settings.winner_name_size}px "${winFont}", "Anek Malayalam", Sora, sans-serif`;
                ctx.fillText(item.name, textX, currentY);

                // Draw Team / Unit below name
                ctx.fillStyle = this.settings.winner_unit_color;
                ctx.font = `${this.settings.winner_unit_weight} ${this.settings.winner_unit_size}px "${winFont}", "Anek Malayalam", Sora, sans-serif`;
                const nameOffset = this.settings.winner_name_size * 1.15;
                ctx.fillText(item.unit, textX, currentY + nameOffset);
                ctx.restore();

                // Increment Y by Row Gap (Line Distance)
                currentY += rowGap;
            });
        },

        wrapText(ctx, text, maxWidth) {
            const words = text.split(' ');
            const lines = [];
            let currentLine = words[0] || '';

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
            if (currentLine) {
                lines.push(currentLine);
            }
            return lines;
        },

        downloadPoster() {
            this.render();
            const canvas = document.getElementById('posterCanvas');
            const link = document.createElement('a');
            link.download = `QUAF09_Result_${this.data.result_no}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
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
                    settings: this.settings
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
            this.render();

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
                    settings: this.settings,
                    publish_now: true
                })
            })
            .then(res => res.json())
            .then(data => {
                this.publishing = false;
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
                this.statusType = 'error';
                this.statusMsg = 'Error uploading poster: ' + err.message;
            });
        }
    };
}
</script>
@endsection
