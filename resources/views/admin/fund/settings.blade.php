@extends('layouts.admin')

@section('title', 'Receipt Voucher Customizer | QUAF 09')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" 
     x-data="{
        activeTab: 'poster',
        activePreviewTab: 'poster',

        // Poster Customizer (Receipt No, Date, Donor Name, Amount)
        rcp_x: {{ $settings->rcp_x }},
        rcp_y: {{ $settings->rcp_y }},
        rcp_font_size: {{ $settings->rcp_font_size }},
        rcp_color: '{{ $settings->rcp_color }}',
        rcp_align: '{{ $settings->rcp_align }}',

        date_x: {{ $settings->date_x }},
        date_y: {{ $settings->date_y }},
        date_font_size: {{ $settings->date_font_size }},
        date_color: '{{ $settings->date_color }}',
        date_align: '{{ $settings->date_align }}',

        donor_x: {{ $settings->donor_x }},
        donor_y: {{ $settings->donor_y }},
        donor_font_size: {{ $settings->donor_font_size }},
        donor_color: '{{ $settings->donor_color }}',
        donor_align: '{{ $settings->donor_align }}',

        amount_x: {{ $settings->amount_x }},
        amount_y: {{ $settings->amount_y }},
        amount_font_size: {{ $settings->amount_font_size }},
        amount_color: '{{ $settings->amount_color }}',
        amount_align: '{{ $settings->amount_align }}',

        // General Voucher Settings
        header_title: '{{ addslashes($settings->header_title) }}',
        organization_name: '{{ addslashes($settings->organization_name) }}',
        subtitle: '{{ addslashes($settings->subtitle) }}',
        text_alignment: '{{ $settings->text_alignment }}',
        font_size: '{{ $settings->font_size }}',
        padding_size: '{{ $settings->padding_size }}',
        primary_color: '{{ $settings->primary_color }}',
        accent_color: '{{ $settings->accent_color }}',
        text_color: '{{ $settings->text_color }}',
        bg_color: '{{ $settings->bg_color }}',
        show_logo: {{ $settings->show_logo ? 'true' : 'false' }},
        footer_text: '{{ addslashes($settings->footer_text) }}',
        authorized_by: '{{ addslashes($settings->authorized_by) }}',

        resetPosterDefaults() {
            this.rcp_x = 890;
            this.rcp_y = 175;
            this.rcp_font_size = 20;
            this.rcp_color = '#5c5a5b';
            this.rcp_align = 'left';

            this.date_x = 830;
            this.date_y = 230;
            this.date_font_size = 20;
            this.date_color = '#5c5a5b';
            this.date_align = 'left';

            this.donor_x = 563;
            this.donor_y = 610;
            this.donor_font_size = 40;
            this.donor_color = '#ffffff';
            this.donor_align = 'center';

            this.amount_x = 563;
            this.amount_y = 755;
            this.amount_font_size = 40;
            this.amount_color = '#000000';
            this.amount_align = 'center';
        },

        applyPreset(preset) {
            if (preset === 'red') {
                this.primary_color = '#be1e2d';
                this.accent_color = '#d97706';
                this.bg_color = '#ffffff';
                this.text_color = '#0f172a';
            } else if (preset === 'emerald') {
                this.primary_color = '#059669';
                this.accent_color = '#0284c7';
                this.bg_color = '#ffffff';
                this.text_color = '#0f172a';
            } else if (preset === 'indigo') {
                this.primary_color = '#4f46e5';
                this.accent_color = '#e11d48';
                this.bg_color = '#ffffff';
                this.text_color = '#0f172a';
            } else if (preset === 'gold') {
                this.primary_color = '#b45309';
                this.accent_color = '#be1e2d';
                this.bg_color = '#fffdfa';
                this.text_color = '#1c1917';
            }
        }
     }">

    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-900 text-white font-mono">
                    Design & Layout
                </span>
                <span class="text-xs text-slate-500 font-mono">Receipt Poster Customizer</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-2 font-sora">
                Receipt Poster Customizer
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-2xl font-anek">
                രസീത് പോസ്റ്ററിലെ Receipt No, Date, Donor Name, Amount എന്നിവയുടെ പ്ലേസ്‌മെന്റ് (X, Y), സൈസ്, നിറം, അലൈൻമെന്റ് എന്നിവ ക്രമീകരിക്കുക.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.fund.index') }}" 
               class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors">
                ← Back to Ledger
            </a>
            <a href="{{ route('fund.home') }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors shadow-sm">
                Fund Home ↗
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold space-y-1">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Two-Column Grid: Form Controls (Left) & Real-time Live Preview (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Controls Column (5 Cols) -->
        <div class="lg:col-span-6 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs space-y-6">
            
            <!-- Mode Switcher Tabs -->
            <div class="flex items-center gap-2 border-b border-slate-100 pb-4">
                <button type="button" @click="activeTab = 'poster'; activePreviewTab = 'poster'"
                        :class="activeTab === 'poster' ? 'bg-[#be1e2d] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all cursor-pointer">
                    Poster Elements (പോസ്റ്റർ)
                </button>
                <button type="button" @click="activeTab = 'voucher'; activePreviewTab = 'voucher'"
                        :class="activeTab === 'voucher' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all cursor-pointer">
                    Invoice Settings (ഇൻവോയ്സ്)
                </button>
            </div>

            <form method="POST" action="{{ route('admin.fund.settings.update') }}" class="space-y-6">
                @csrf

                <!-- TAB 1: RECEIPT POSTER ELEMENTS (RECEIPT NO, DATE, DONOR NAME, AMOUNT) -->
                <div x-show="activeTab === 'poster'" class="space-y-6">
                    <div class="flex items-center justify-between bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div>
                            <span class="text-xs font-black text-slate-900">Official Receipt Poster Controls</span>
                            <p class="text-[11px] text-slate-500 font-anek">1126 × 1407 റെസല്യൂഷനിലുള്ള എച്ച്.ഡി ടെംപ്ലേറ്റിന്റെ സ്ഥാനങ്ങൾ</p>
                        </div>
                        <button type="button" @click="resetPosterDefaults()" 
                                class="px-2.5 py-1.5 rounded-lg bg-white border border-slate-300 hover:bg-slate-100 text-[11px] font-bold text-slate-700 transition-colors shadow-2xs cursor-pointer">
                            Reset Defaults
                        </button>
                    </div>

                    <!-- 1. RECEIPT NUMBER CONTROLS -->
                    <div class="p-4 rounded-xl border border-slate-200/90 bg-white shadow-xs space-y-3.5">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 font-mono">1. Receipt Number (രസീത് നമ്പർ)</h3>
                            </div>
                            <span class="text-[11px] font-mono text-slate-500">X: <span x-text="rcp_x"></span> | Y: <span x-text="rcp_y"></span></span>
                        </div>

                        <!-- Coordinates X & Y -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">X Position (ഇടത് - വലത്)</label>
                                <input type="number" name="rcp_x" x-model.number="rcp_x" min="0" max="1126" 
                                       class="w-full px-2.5 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300">
                                <input type="range" x-model.number="rcp_x" min="0" max="1126" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#be1e2d]">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Y Position (മുകൾ - താഴെ)</label>
                                <input type="number" name="rcp_y" x-model.number="rcp_y" min="0" max="1407" 
                                       class="w-full px-2.5 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300">
                                <input type="range" x-model.number="rcp_y" min="0" max="1407" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#be1e2d]">
                            </div>
                        </div>

                        <!-- Font Size & Color -->
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Font Size</label>
                                    <span class="text-[10px] font-mono text-slate-600 font-bold" x-text="rcp_font_size + 'px'"></span>
                                </div>
                                <input type="number" name="rcp_font_size" x-model.number="rcp_font_size" min="8" max="100" 
                                       class="w-full px-2.5 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300">
                                <input type="range" x-model.number="rcp_font_size" min="8" max="70" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#be1e2d]">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Text Color</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" x-model="rcp_color" class="w-7 h-7 rounded border border-slate-300 cursor-pointer">
                                    <input type="text" name="rcp_color" x-model="rcp_color" 
                                           class="w-full px-2 py-1.5 text-xs font-mono rounded-lg border border-slate-300 uppercase">
                                </div>
                            </div>
                        </div>

                        <!-- Alignment -->
                        <div class="space-y-1 pt-1">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Alignment</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="rcp_align = 'left'"
                                        :class="rcp_align === 'left' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                        class="py-1 px-2 rounded-lg text-xs font-mono transition-colors text-center cursor-pointer">
                                    Left
                                </button>
                                <button type="button" @click="rcp_align = 'center'"
                                        :class="rcp_align === 'center' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                        class="py-1 px-2 rounded-lg text-xs font-mono transition-colors text-center cursor-pointer">
                                    Center
                                </button>
                                <button type="button" @click="rcp_align = 'right'"
                                        :class="rcp_align === 'right' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                        class="py-1 px-2 rounded-lg text-xs font-mono transition-colors text-center cursor-pointer">
                                    Right
                                </button>
                            </div>
                            <input type="hidden" name="rcp_align" :value="rcp_align">
                        </div>
                    </div>

                    <!-- 2. DATE CONTROLS -->
                    <div class="p-4 rounded-xl border border-slate-200/90 bg-white shadow-xs space-y-3.5">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 font-mono">2. Date (തീയതി)</h3>
                            </div>
                            <span class="text-[11px] font-mono text-slate-500">X: <span x-text="date_x"></span> | Y: <span x-text="date_y"></span></span>
                        </div>

                        <!-- Coordinates X & Y -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">X Position (ഇടത് - വലത്)</label>
                                <input type="number" name="date_x" x-model.number="date_x" min="0" max="1126" 
                                       class="w-full px-2.5 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300">
                                <input type="range" x-model.number="date_x" min="0" max="1126" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#be1e2d]">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Y Position (മുകൾ - താഴെ)</label>
                                <input type="number" name="date_y" x-model.number="date_y" min="0" max="1407" 
                                       class="w-full px-2.5 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300">
                                <input type="range" x-model.number="date_y" min="0" max="1407" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#be1e2d]">
                            </div>
                        </div>

                        <!-- Font Size & Color -->
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Font Size</label>
                                    <span class="text-[10px] font-mono text-slate-600 font-bold" x-text="date_font_size + 'px'"></span>
                                </div>
                                <input type="number" name="date_font_size" x-model.number="date_font_size" min="8" max="100" 
                                       class="w-full px-2.5 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300">
                                <input type="range" x-model.number="date_font_size" min="8" max="70" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#be1e2d]">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Text Color</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" x-model="date_color" class="w-7 h-7 rounded border border-slate-300 cursor-pointer">
                                    <input type="text" name="date_color" x-model="date_color" 
                                           class="w-full px-2 py-1.5 text-xs font-mono rounded-lg border border-slate-300 uppercase">
                                </div>
                            </div>
                        </div>

                        <!-- Alignment -->
                        <div class="space-y-1 pt-1">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Alignment</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="date_align = 'left'"
                                        :class="date_align === 'left' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                        class="py-1 px-2 rounded-lg text-xs font-mono transition-colors text-center cursor-pointer">
                                    Left
                                </button>
                                <button type="button" @click="date_align = 'center'"
                                        :class="date_align === 'center' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                        class="py-1 px-2 rounded-lg text-xs font-mono transition-colors text-center cursor-pointer">
                                    Center
                                </button>
                                <button type="button" @click="date_align = 'right'"
                                        :class="date_align === 'right' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                        class="py-1 px-2 rounded-lg text-xs font-mono transition-colors text-center cursor-pointer">
                                    Right
                                </button>
                            </div>
                            <input type="hidden" name="date_align" :value="date_align">
                        </div>
                    </div>

                    <!-- 3. DONOR NAME CONTROLS -->
                    <div class="p-4 rounded-xl border border-slate-200/90 bg-white shadow-xs space-y-3.5">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 font-mono">3. Donor Name (ദാതാവിന്റെ പേര്)</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-50 text-amber-700 border border-amber-200">Sora (English) / Anek (മലയാളം)</span>
                            </div>
                            <span class="text-[11px] font-mono text-slate-500">X: <span x-text="donor_x"></span> | Y: <span x-text="donor_y"></span></span>
                        </div>

                        <!-- Coordinates X & Y -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">X Position (Horizontal Center: 563)</label>
                                <input type="number" name="donor_x" x-model.number="donor_x" min="0" max="1126" 
                                       class="w-full px-2.5 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300">
                                <input type="range" x-model.number="donor_x" min="0" max="1126" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#be1e2d]">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Y Position (മുകൾ - താഴെ)</label>
                                <input type="number" name="donor_y" x-model.number="donor_y" min="0" max="1407" 
                                       class="w-full px-2.5 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300">
                                <input type="range" x-model.number="donor_y" min="0" max="1407" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#be1e2d]">
                            </div>
                        </div>

                        <!-- Font Size & Color -->
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Font Size</label>
                                    <span class="text-[10px] font-mono text-slate-600 font-bold" x-text="donor_font_size + 'px'"></span>
                                </div>
                                <input type="number" name="donor_font_size" x-model.number="donor_font_size" min="14" max="100" 
                                       class="w-full px-2.5 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300">
                                <input type="range" x-model.number="donor_font_size" min="14" max="80" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#be1e2d]">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Text Color</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" x-model="donor_color" class="w-7 h-7 rounded border border-slate-300 cursor-pointer">
                                    <input type="text" name="donor_color" x-model="donor_color" 
                                           class="w-full px-2 py-1.5 text-xs font-mono rounded-lg border border-slate-300 uppercase">
                                </div>
                            </div>
                        </div>

                        <!-- Alignment -->
                        <div class="space-y-1 pt-1">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Alignment</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="donor_align = 'left'"
                                        :class="donor_align === 'left' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                        class="py-1 px-2 rounded-lg text-xs font-mono transition-colors text-center cursor-pointer">
                                    Left
                                </button>
                                <button type="button" @click="donor_align = 'center'"
                                        :class="donor_align === 'center' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                        class="py-1 px-2 rounded-lg text-xs font-mono transition-colors text-center cursor-pointer">
                                    Center
                                </button>
                                <button type="button" @click="donor_align = 'right'"
                                        :class="donor_align === 'right' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                        class="py-1 px-2 rounded-lg text-xs font-mono transition-colors text-center cursor-pointer">
                                    Right
                                </button>
                            </div>
                            <input type="hidden" name="donor_align" :value="donor_align">
                        </div>
                    </div>

                    <!-- 4. AMOUNT CONTROLS -->
                    <div class="p-4 rounded-xl border border-slate-200/90 bg-white shadow-xs space-y-3.5">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 font-mono">4. Amount (സംഭാവന തുക)</h3>
                            </div>
                            <span class="text-[11px] font-mono text-slate-500">X: <span x-text="amount_x"></span> | Y: <span x-text="amount_y"></span></span>
                        </div>

                        <!-- Coordinates X & Y -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">X Position (Horizontal Center: 563)</label>
                                <input type="number" name="amount_x" x-model.number="amount_x" min="0" max="1126" 
                                       class="w-full px-2.5 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300">
                                <input type="range" x-model.number="amount_x" min="0" max="1126" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#be1e2d]">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Y Position (മുകൾ - താഴെ)</label>
                                <input type="number" name="amount_y" x-model.number="amount_y" min="0" max="1407" 
                                       class="w-full px-2.5 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300">
                                <input type="range" x-model.number="amount_y" min="0" max="1407" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#be1e2d]">
                            </div>
                        </div>

                        <!-- Font Size & Color -->
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Font Size</label>
                                    <span class="text-[10px] font-mono text-slate-600 font-bold" x-text="amount_font_size + 'px'"></span>
                                </div>
                                <input type="number" name="amount_font_size" x-model.number="amount_font_size" min="16" max="100" 
                                       class="w-full px-2.5 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300">
                                <input type="range" x-model.number="amount_font_size" min="16" max="80" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#be1e2d]">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Text Color</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" x-model="amount_color" class="w-7 h-7 rounded border border-slate-300 cursor-pointer">
                                    <input type="text" name="amount_color" x-model="amount_color" 
                                           class="w-full px-2 py-1.5 text-xs font-mono rounded-lg border border-slate-300 uppercase">
                                </div>
                            </div>
                        </div>

                        <!-- Alignment -->
                        <div class="space-y-1 pt-1">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Alignment</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="amount_align = 'left'"
                                        :class="amount_align === 'left' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                        class="py-1 px-2 rounded-lg text-xs font-mono transition-colors text-center cursor-pointer">
                                    Left
                                </button>
                                <button type="button" @click="amount_align = 'center'"
                                        :class="amount_align === 'center' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                        class="py-1 px-2 rounded-lg text-xs font-mono transition-colors text-center cursor-pointer">
                                    Center
                                </button>
                                <button type="button" @click="amount_align = 'right'"
                                        :class="amount_align === 'right' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                                        class="py-1 px-2 rounded-lg text-xs font-mono transition-colors text-center cursor-pointer">
                                    Right
                                </button>
                            </div>
                            <input type="hidden" name="amount_align" :value="amount_align">
                        </div>
                    </div>
                </div>

                <!-- TAB 2: GENERAL VOUCHER SETTINGS (INVOICE SLIP) -->
                <div x-show="activeTab === 'voucher'" class="space-y-5" x-cloak>
                    <!-- Color Presets -->
                    <div class="space-y-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 font-mono">Theme Presets</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button type="button" @click="applyPreset('red')" 
                                    class="px-2 py-1.5 rounded-lg border border-red-300 bg-red-50 text-red-800 text-[11px] font-bold hover:bg-red-100 transition-colors">
                                QUAF Red
                            </button>
                            <button type="button" @click="applyPreset('emerald')" 
                                    class="px-2 py-1.5 rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-800 text-[11px] font-bold hover:bg-emerald-100 transition-colors">
                                Emerald
                            </button>
                            <button type="button" @click="applyPreset('indigo')" 
                                    class="px-2 py-1.5 rounded-lg border border-indigo-300 bg-indigo-50 text-indigo-800 text-[11px] font-bold hover:bg-indigo-100 transition-colors">
                                Indigo
                            </button>
                            <button type="button" @click="applyPreset('gold')" 
                                    class="px-2 py-1.5 rounded-lg border border-amber-300 bg-amber-50 text-amber-800 text-[11px] font-bold hover:bg-amber-100 transition-colors">
                                Amber Gold
                            </button>
                        </div>
                    </div>

                    <!-- Header Title -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Header Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="header_title" x-model="header_title" required
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
                    </div>

                    <!-- Organization Name -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Organization Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="organization_name" x-model="organization_name" required
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
                    </div>

                    <!-- Subtitle -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Subtitle <span class="text-rose-500">*</span></label>
                        <input type="text" name="subtitle" x-model="subtitle" required
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
                    </div>

                    <!-- Alignment Controls -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Text Alignment / അലൈൻമെന്റ്</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label :class="text_alignment === 'left' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'"
                                   class="flex items-center justify-center py-2 px-3 rounded-xl text-xs cursor-pointer transition-colors border border-slate-200">
                                <input type="radio" name="text_alignment" value="left" x-model="text_alignment" class="sr-only">
                                <span>Left</span>
                            </label>
                            <label :class="text_alignment === 'center' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'"
                                   class="flex items-center justify-center py-2 px-3 rounded-xl text-xs cursor-pointer transition-colors border border-slate-200">
                                <input type="radio" name="text_alignment" value="center" x-model="text_alignment" class="sr-only">
                                <span>Center</span>
                            </label>
                            <label :class="text_alignment === 'right' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'"
                                   class="flex items-center justify-center py-2 px-3 rounded-xl text-xs cursor-pointer transition-colors border border-slate-200">
                                <input type="radio" name="text_alignment" value="right" x-model="text_alignment" class="sr-only">
                                <span>Right</span>
                            </label>
                        </div>
                    </div>

                    <!-- Colors -->
                    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold uppercase text-slate-600 font-mono">Primary Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="primary_color" class="w-8 h-8 rounded-lg cursor-pointer border border-slate-300">
                                <input type="text" name="primary_color" x-model="primary_color" required
                                       class="w-full px-2.5 py-1.5 text-xs font-mono rounded-lg border border-slate-300">
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold uppercase text-slate-600 font-mono">Accent Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="accent_color" class="w-8 h-8 rounded-lg cursor-pointer border border-slate-300">
                                <input type="text" name="accent_color" x-model="accent_color" required
                                       class="w-full px-2.5 py-1.5 text-xs font-mono rounded-lg border border-slate-300">
                            </div>
                        </div>
                    </div>

                    <!-- Hidden defaults if editing from poster tab -->
                    <input type="hidden" name="font_size" :value="font_size">
                    <input type="hidden" name="padding_size" :value="padding_size">
                    <input type="hidden" name="text_color" :value="text_color">
                    <input type="hidden" name="bg_color" :value="bg_color">
                    <input type="hidden" name="authorized_by" :value="authorized_by">
                    <input type="hidden" name="footer_text" :value="footer_text">
                </div>

                <!-- Always included hidden fields to ensure both tabs persist seamlessly -->
                <template x-if="activeTab === 'poster'">
                    <div>
                        <input type="hidden" name="header_title" :value="header_title">
                        <input type="hidden" name="organization_name" :value="organization_name">
                        <input type="hidden" name="subtitle" :value="subtitle">
                        <input type="hidden" name="text_alignment" :value="text_alignment">
                        <input type="hidden" name="font_size" :value="font_size">
                        <input type="hidden" name="padding_size" :value="padding_size">
                        <input type="hidden" name="primary_color" :value="primary_color">
                        <input type="hidden" name="accent_color" :value="accent_color">
                        <input type="hidden" name="text_color" :value="text_color">
                        <input type="hidden" name="bg_color" :value="bg_color">
                        <input type="hidden" name="authorized_by" :value="authorized_by">
                        <input type="hidden" name="footer_text" :value="footer_text">
                    </div>
                </template>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                    <span class="text-xs text-slate-400 font-mono">Changes apply to all generated vouchers</span>
                    <button type="submit" 
                            class="px-6 py-3 rounded-xl bg-[#be1e2d] hover:bg-red-700 text-white font-black text-xs uppercase tracking-wider transition-all shadow-md cursor-pointer">
                        Save Customizer Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Preview Column (6 Cols) -->
        <div class="lg:col-span-6 sticky top-20 space-y-4">
            
            <!-- Preview Controls Bar -->
            <div class="flex items-center justify-between bg-white p-3 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-black uppercase tracking-wider text-slate-800 font-mono">Live Interactive Preview</span>
                </div>

                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-bold">
                    <button type="button" @click="activePreviewTab = 'poster'"
                            :class="activePreviewTab === 'poster' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-950'"
                            class="px-3 py-1 rounded-lg transition-all cursor-pointer">
                        Poster Preview (HD)
                    </button>
                    <button type="button" @click="activePreviewTab = 'voucher'"
                            :class="activePreviewTab === 'voucher' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-950'"
                            class="px-3 py-1 rounded-lg transition-all cursor-pointer">
                        Invoice Voucher
                    </button>
                </div>
            </div>

            <!-- PREVIEW 1: REAL HD RECEIPT POSTER WITH LIVE SVG TEXT OVERLAY -->
            <div x-show="activePreviewTab === 'poster'" class="transition-all duration-200">
                <div class="p-4 sm:p-5 rounded-3xl bg-slate-950 border border-slate-800 shadow-2xl relative overflow-hidden">
                    
                    <div class="relative w-full rounded-2xl overflow-hidden shadow-2xl border border-amber-500/20 bg-black">
                        <!-- SVG with 1126 x 1407 ViewBox for 100% Pixel-Accurate Coordinate Match -->
                        <svg viewBox="0 0 1126 1407" class="w-full h-auto block select-none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Background Template Image -->
                            <image href="{{ asset('images/receipt-template.jpg') }}" width="1126" height="1407" preserveAspectRatio="none" />

                            <!-- 1. Live Receipt Number -->
                            <text :x="rcp_x" 
                                  :y="rcp_y" 
                                  :font-size="rcp_font_size" 
                                  :fill="rcp_color" 
                                  :text-anchor="rcp_align === 'center' ? 'middle' : (rcp_align === 'right' ? 'end' : 'start')"
                                  font-family="'Rockwell', Rockwell, serif" 
                                  font-weight="bold">QFR-26-042</text>

                            <!-- 2. Live Date -->
                            <text :x="date_x" 
                                  :y="date_y" 
                                  :font-size="date_font_size" 
                                  :fill="date_color" 
                                  :text-anchor="date_align === 'center' ? 'middle' : (date_align === 'right' ? 'end' : 'start')"
                                  font-family="'Rockwell', Rockwell, serif" 
                                  font-weight="bold">{{ ($sampleReceipt->created_at ?? now())->format('d/m/Y') }}</text>

                            <!-- 3. Live Donor Name -->
                            <text :x="donor_x" 
                                  :y="donor_y" 
                                  :font-size="donor_font_size" 
                                  :fill="donor_color" 
                                  :text-anchor="donor_align === 'center' ? 'middle' : (donor_align === 'right' ? 'end' : 'start')"
                                  font-family="'Sora', 'Anek Malayalam', sans-serif" 
                                  font-weight="800">{{ strtoupper($sampleReceipt->donor_name ?? 'MUHAMMED SHAFI K') }}</text>

                            <!-- 4. Live Amount -->
                            <text :x="amount_x" 
                                  :y="amount_y" 
                                  :font-size="amount_font_size" 
                                  :fill="amount_color" 
                                  :text-anchor="amount_align === 'center' ? 'middle' : (amount_align === 'right' ? 'end' : 'start')"
                                  font-family="'Rockwell', Rockwell, serif" 
                                  font-weight="bold">Rs.{{ number_format($sampleReceipt->amount ?? 5000, 0, '.', ',') }}/-</text>
                        </svg>
                    </div>

                    <!-- Real-time Position & Size Badges -->
                    <div class="mt-3.5 px-3 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-[11px] font-mono text-slate-400 grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-blue-400 font-bold">Rcp No:</span>
                            <span class="text-slate-200">(<span x-text="rcp_x"></span>, <span x-text="rcp_y"></span>) • <span x-text="rcp_font_size"></span>px</span>
                        </div>
                        <div>
                            <span class="text-emerald-400 font-bold">Date:</span>
                            <span class="text-slate-200">(<span x-text="date_x"></span>, <span x-text="date_y"></span>) • <span x-text="date_font_size"></span>px</span>
                        </div>
                        <div>
                            <span class="text-amber-400 font-bold">Donor:</span>
                            <span class="text-slate-200">(<span x-text="donor_x"></span>, <span x-text="donor_y"></span>) • <span x-text="donor_font_size"></span>px</span>
                        </div>
                        <div>
                            <span class="text-rose-400 font-bold">Amount:</span>
                            <span class="text-slate-200">(<span x-text="amount_x"></span>, <span x-text="amount_y"></span>) • <span x-text="amount_font_size"></span>px</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PREVIEW 2: INVOICE VOUCHER SLIP PREVIEW -->
            <div x-show="activePreviewTab === 'voucher'" x-cloak class="transition-all duration-200">
                <div class="p-6 rounded-3xl bg-slate-200/80 border border-slate-300/80 shadow-inner">
                    <div class="rounded-2xl shadow-xl transition-all relative overflow-hidden"
                         :class="{
                            'text-left': text_alignment === 'left',
                            'text-center': text_alignment === 'center',
                            'text-right': text_alignment === 'right',
                            'p-6 sm:p-8': padding_size === 'normal',
                         }"
                         :style="'background-color: ' + bg_color + '; color: ' + text_color + '; border: 2px solid ' + primary_color + '33;'">

                        <!-- Accent Strip -->
                        <div class="absolute top-0 left-0 right-0 h-2" :style="'background: linear-gradient(90deg, ' + primary_color + ', ' + accent_color + ');'"></div>

                        <!-- Header -->
                        <div class="pb-4 border-b border-dashed border-slate-300/80">
                            <span class="text-[9px] font-mono font-bold uppercase tracking-widest px-2.5 py-0.5 rounded-full inline-block mb-1" 
                                  :style="'background-color: ' + primary_color + '15; color: ' + primary_color + ';'" 
                                  x-text="organization_name">
                            </span>

                            <h2 class="font-black tracking-tight text-xl" 
                                :style="'color: ' + primary_color + ';'" 
                                x-text="header_title">
                            </h2>

                            <p class="text-[11px] text-slate-500 font-medium mt-0.5" x-text="subtitle"></p>
                        </div>

                        <!-- Meta Ribbon -->
                        <div class="py-3 flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/80 font-mono text-[11px]">
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400 font-bold uppercase text-[9px]">Receipt:</span>
                                <span class="px-2 py-0.5 rounded font-black tracking-wider text-white" :style="'background-color: ' + primary_color + ';'">
                                    {{ $sampleReceipt->receipt_number }}
                                </span>
                            </div>
                            <div class="text-slate-600">
                                {{ ($sampleReceipt->created_at ?? now())->format('d M Y, h:i A') }}
                            </div>
                        </div>

                        <!-- Donor Details -->
                        <div class="py-4 space-y-3">
                            <div class="bg-slate-50/70 p-3 rounded-xl border border-slate-200/70 text-left">
                                <div class="text-[9px] uppercase font-mono font-bold tracking-wider text-slate-400">Received From:</div>
                                <div class="text-sm font-black text-slate-900 mt-0.5 uppercase">{{ $sampleReceipt->donor_name }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">{{ $sampleReceipt->donor_place }} • {{ $sampleReceipt->donor_phone }}</div>
                            </div>

                            <!-- Amount Box -->
                            <div class="p-3.5 rounded-xl flex items-center justify-between gap-3 border text-left"
                                 :style="'background-color: ' + primary_color + '08; border-color: ' + primary_color + '25;'">
                                <div>
                                    <div class="text-[9px] uppercase font-mono font-bold" :style="'color: ' + primary_color + ';'">Amount Received</div>
                                    <div class="text-xl font-black font-mono mt-0.5" :style="'color: ' + primary_color + ';'">
                                        ₹{{ number_format($sampleReceipt->amount, 2) }}
                                    </div>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full font-bold uppercase text-[10px] text-white font-mono"
                                      :style="'background-color: ' + accent_color + ';'">
                                    {{ strtoupper($sampleReceipt->payment_method) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
