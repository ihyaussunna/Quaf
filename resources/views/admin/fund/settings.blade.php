@extends('layouts.admin')

@section('title', 'Receipt Voucher Customizer | QUAF 09')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" 
     x-data="{
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

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-900 text-white">
                    Design & Layout
                </span>
                <span class="text-xs text-slate-500 font-mono">Receipt Voucher Engine</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-2 font-sora">
                Receipt Voucher Customizer
            </h1>
            <p class="text-sm text-slate-600 mt-1 max-w-2xl font-sora">
                Customize text alignment, size, placement, colors, and branding with live interactive preview.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.fund.index') }}" 
               class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors">
                ← Back to Ledger
            </a>
            <a href="{{ route('fund.dashboard') }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors shadow-sm">
                Open Fund Desk ↗
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

    <!-- Two-Column Grid: Form on Left, Live Preview on Right -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Form Column (5 Cols) -->
        <div class="lg:col-span-5 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-base font-black text-slate-900">Customizer Controls</h2>
                <p class="text-xs text-slate-500 font-anek">രസീതിലെ ടെക്സ്റ്റ് അലൈൻമെന്റ്, സൈസ്, നിറങ്ങൾ എന്നിവ മാറ്റുക.</p>
            </div>

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

            <form method="POST" action="{{ route('admin.fund.settings.update') }}" class="space-y-5">
                @csrf

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

                <!-- Alignment Controls (Left, Center, Right) -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase">Text Alignment / അലൈൻമെന്റ്</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label :class="text_alignment === 'left' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'"
                               class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl text-xs cursor-pointer transition-colors border border-slate-200">
                            <input type="radio" name="text_alignment" value="left" x-model="text_alignment" class="sr-only">
                            <span>Left (ഇടത്)</span>
                        </label>
                        <label :class="text_alignment === 'center' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'"
                               class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl text-xs cursor-pointer transition-colors border border-slate-200">
                            <input type="radio" name="text_alignment" value="center" x-model="text_alignment" class="sr-only">
                            <span>Center (മധ്യത്തിൽ)</span>
                        </label>
                        <label :class="text_alignment === 'right' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'"
                               class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl text-xs cursor-pointer transition-colors border border-slate-200">
                            <input type="radio" name="text_alignment" value="right" x-model="text_alignment" class="sr-only">
                            <span>Right (വലത്)</span>
                        </label>
                    </div>
                </div>

                <!-- Font Size Controls (Small, Normal, Large) -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase">Font Size / ഫോണ്ട് സൈസ്</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label :class="font_size === 'small' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'"
                               class="flex items-center justify-center py-2 px-3 rounded-xl text-xs cursor-pointer transition-colors border border-slate-200">
                            <input type="radio" name="font_size" value="small" x-model="font_size" class="sr-only">
                            <span>Small (ചെറുത്)</span>
                        </label>
                        <label :class="font_size === 'normal' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'"
                               class="flex items-center justify-center py-2 px-3 rounded-xl text-xs cursor-pointer transition-colors border border-slate-200">
                            <input type="radio" name="font_size" value="normal" x-model="font_size" class="sr-only">
                            <span>Normal (സാധാരണ)</span>
                        </label>
                        <label :class="font_size === 'large' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'"
                               class="flex items-center justify-center py-2 px-3 rounded-xl text-xs cursor-pointer transition-colors border border-slate-200">
                            <input type="radio" name="font_size" value="large" x-model="font_size" class="sr-only">
                            <span>Large (വലുത്)</span>
                        </label>
                    </div>
                </div>

                <!-- Placement / Padding Controls -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase">Placement & Padding / പാഡിങ്</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label :class="padding_size === 'compact' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'"
                               class="flex items-center justify-center py-2 px-3 rounded-xl text-xs cursor-pointer transition-colors border border-slate-200">
                            <input type="radio" name="padding_size" value="compact" x-model="padding_size" class="sr-only">
                            <span>Compact</span>
                        </label>
                        <label :class="padding_size === 'normal' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'"
                               class="flex items-center justify-center py-2 px-3 rounded-xl text-xs cursor-pointer transition-colors border border-slate-200">
                            <input type="radio" name="padding_size" value="normal" x-model="padding_size" class="sr-only">
                            <span>Standard</span>
                        </label>
                        <label :class="padding_size === 'relaxed' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'"
                               class="flex items-center justify-center py-2 px-3 rounded-xl text-xs cursor-pointer transition-colors border border-slate-200">
                            <input type="radio" name="padding_size" value="relaxed" x-model="padding_size" class="sr-only">
                            <span>Relaxed</span>
                        </label>
                    </div>
                </div>

                <!-- Color Controls -->
                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                    <!-- Primary Color -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-bold uppercase text-slate-600 font-mono">Primary Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="primary_color" class="w-8 h-8 rounded-lg cursor-pointer border border-slate-300">
                            <input type="text" name="primary_color" x-model="primary_color" required
                                   class="w-full px-2.5 py-1.5 text-xs font-mono rounded-lg border border-slate-300">
                        </div>
                    </div>

                    <!-- Accent Color -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-bold uppercase text-slate-600 font-mono">Accent Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="accent_color" class="w-8 h-8 rounded-lg cursor-pointer border border-slate-300">
                            <input type="text" name="accent_color" x-model="accent_color" required
                                   class="w-full px-2.5 py-1.5 text-xs font-mono rounded-lg border border-slate-300">
                        </div>
                    </div>

                    <!-- Text Color -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-bold uppercase text-slate-600 font-mono">Text Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="text_color" class="w-8 h-8 rounded-lg cursor-pointer border border-slate-300">
                            <input type="text" name="text_color" x-model="text_color" required
                                   class="w-full px-2.5 py-1.5 text-xs font-mono rounded-lg border border-slate-300">
                        </div>
                    </div>

                    <!-- Background Color -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-bold uppercase text-slate-600 font-mono">Background Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="bg_color" class="w-8 h-8 rounded-lg cursor-pointer border border-slate-300">
                            <input type="text" name="bg_color" x-model="bg_color" required
                                   class="w-full px-2.5 py-1.5 text-xs font-mono rounded-lg border border-slate-300">
                        </div>
                    </div>
                </div>

                <!-- Show Logo Toggle -->
                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="show_logo" value="1" x-model="show_logo" class="rounded text-[#be1e2d] focus:ring-[#be1e2d]">
                        <span class="text-xs font-bold text-slate-800">Display Festival Logo on Voucher</span>
                    </label>
                </div>

                <!-- Authorized Signatory -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase">Authorized Signatory Text</label>
                    <input type="text" name="authorized_by" x-model="authorized_by" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
                </div>

                <!-- Footer Text -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase">Footer Note</label>
                    <textarea name="footer_text" x-model="footer_text" rows="2"
                              class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                    <button type="submit" 
                            class="w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-black text-xs uppercase tracking-wider transition-all shadow-md">
                        Save Receipt Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Preview Column (7 Cols) -->
        <div class="lg:col-span-7 sticky top-24 space-y-3">
            <div class="flex items-center justify-between px-2">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-black uppercase tracking-wider text-slate-700 font-mono">Live Voucher Preview</span>
                </div>
                <span class="text-[10px] text-slate-400 font-mono">Real-time updates</span>
            </div>

            <!-- Preview Frame -->
            <div class="p-6 rounded-3xl bg-slate-200/80 border border-slate-300/80 shadow-inner">
                <div class="rounded-2xl shadow-xl transition-all relative overflow-hidden"
                     :class="{
                        'text-left': text_alignment === 'left',
                        'text-center': text_alignment === 'center',
                        'text-right': text_alignment === 'right',
                        'text-xs': font_size === 'small',
                        'text-sm': font_size === 'normal',
                        'text-base': font_size === 'large',
                        'p-4 sm:p-5': padding_size === 'compact',
                        'p-6 sm:p-8': padding_size === 'normal',
                        'p-8 sm:p-10': padding_size === 'relaxed'
                     }"
                     :style="'background-color: ' + bg_color + '; color: ' + text_color + '; border: 2px solid ' + primary_color + '33;'">

                    <!-- Accent Strip -->
                    <div class="absolute top-0 left-0 right-0 h-2" :style="'background: linear-gradient(90deg, ' + primary_color + ', ' + accent_color + ');'"></div>

                    <!-- Header -->
                    <div class="pb-4 border-b border-dashed border-slate-300/80">
                        <div x-show="show_logo" class="mb-2">
                            <img src="{{ asset('images/dashboard-logo.svg') }}" alt="Logo" class="h-9 w-auto inline-block object-contain max-w-[150px]">
                        </div>

                        <span class="text-[9px] font-mono font-bold uppercase tracking-widest px-2.5 py-0.5 rounded-full inline-block mb-1" 
                              :style="'background-color: ' + primary_color + '15; color: ' + primary_color + ';'" 
                              x-text="organization_name">
                        </span>

                        <h2 class="font-black tracking-tight" 
                            :class="font_size === 'small' ? 'text-lg' : (font_size === 'large' ? 'text-2xl' : 'text-xl')"
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
                            {{ $sampleReceipt->created_at->format('d M Y, h:i A') }}
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

                        <!-- Amount in Words -->
                        <div class="px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-left font-mono text-[10px]">
                            <span class="text-slate-400 uppercase font-bold mr-1">In Words:</span>
                            <span class="font-bold text-slate-900">{{ $sampleReceipt->amount_in_words }}</span>
                        </div>
                    </div>

                    <!-- Signatures & Verification -->
                    <div class="pt-4 border-t border-dashed border-slate-300/80 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <div class="w-12 h-12 p-0.5 bg-white rounded-lg border border-slate-300 shrink-0">
                                {!! \App\Services\QrCodeService::svg(route('admin.fund.index'), 80) !!}
                            </div>
                            <div class="text-[9px] font-mono text-slate-500 leading-tight text-left">
                                <div class="font-bold text-slate-800">Scan to Verify</div>
                                <div class="text-emerald-700 font-bold">Official Voucher</div>
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="w-32 border-t border-slate-400/60 ml-auto"></div>
                            <div class="text-[10px] font-bold text-slate-800 mt-1 uppercase tracking-wider font-mono" x-text="authorized_by"></div>
                        </div>
                    </div>

                    <!-- Footer Note -->
                    <div x-show="footer_text" class="mt-4 pt-3 border-t border-slate-200/60 text-[10px] text-slate-500" x-text="footer_text"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
