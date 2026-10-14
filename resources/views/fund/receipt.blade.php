<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Receipt #{{ $receipt->receipt_number }} | {{ $settings->header_title }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Malayalam:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @page {
            size: auto;
            margin: 8mm;
        }

        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-card {
                box-shadow: none !important;
                border: 2px solid {{ $settings->primary_color }} !important;
                margin: 0 auto !important;
                page-break-inside: avoid !important;
            }
        }

        .font-anek {
            font-family: 'Anek Malayalam', sans-serif;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-6 px-3 sm:px-6 font-sora antialiased text-slate-900">

    @php
        // Resolve dynamic alignment classes
        $alignClass = match($settings->text_alignment) {
            'left' => 'text-left',
            'right' => 'text-right',
            default => 'text-center',
        };

        $headerFlex = match($settings->text_alignment) {
            'left' => 'justify-start items-start',
            'right' => 'justify-end items-end',
            default => 'justify-center items-center',
        };

        // Resolve dynamic font size scale
        $fontSizeScale = match($settings->font_size) {
            'small' => 'text-xs',
            'large' => 'text-base',
            default => 'text-sm',
        };

        $titleScale = match($settings->font_size) {
            'small' => 'text-lg sm:text-xl',
            'large' => 'text-2xl sm:text-3xl',
            default => 'text-xl sm:text-2xl',
        };

        // Resolve dynamic padding scale
        $paddingScale = match($settings->padding_size) {
            'compact' => 'p-4 sm:p-6',
            'relaxed' => 'p-8 sm:p-12',
            default => 'p-6 sm:p-8',
        };
    @endphp

    <!-- Top Action Ribbon (Hidden when printing) -->
    <div class="max-w-2xl mx-auto mb-6 no-print flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            @if(Auth::user()?->isAdmin())
                <a href="{{ route('admin.fund.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition-all shadow-xs">
                    <span>← All Receipts Ledger</span>
                </a>
                <a href="{{ route('admin.fund.settings') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                    <span>Customize Design</span>
                </a>
            @else
                <a href="{{ route('fund.dashboard') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition-all shadow-xs">
                    <span>← Back to Fund Desk</span>
                </a>
            @endif
        </div>

        <div class="flex items-center gap-2">
            @if(!Auth::user()?->isAdmin())
                <a href="{{ route('fund.dashboard') }}#new-receipt-card"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs transition-all">
                    <span>+ New Receipt</span>
                </a>
            @endif

            <button type="button" onclick="window.print()" 
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-md shadow-emerald-600/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Receipt Voucher</span>
            </button>
        </div>
    </div>

    <!-- The Printable Receipt Voucher Document -->
    <div class="receipt-card max-w-2xl mx-auto rounded-3xl shadow-xl transition-all relative overflow-hidden {{ $paddingScale }} {{ $fontSizeScale }}"
         style="background-color: {{ $settings->bg_color }}; color: {{ $settings->text_color }}; border: 2px solid {{ $settings->primary_color }}33;">

        <!-- Top Accent Color Bar -->
        <div class="absolute top-0 left-0 right-0 h-2.5" style="background: linear-gradient(90deg, {{ $settings->primary_color }}, {{ $settings->accent_color }});"></div>

        <!-- Watermark / Background Texture -->
        <div class="absolute inset-0 pointer-events-none opacity-[0.03] flex items-center justify-center font-black text-7xl select-none" style="color: {{ $settings->primary_color }};">
            {{ $settings->header_title }}
        </div>

        <!-- Receipt Header Section -->
        <div class="relative z-10 flex flex-col {{ $headerFlex }} {{ $alignClass }} pb-6 border-b border-dashed border-slate-300/80">
            @if($settings->show_logo)
                <div class="mb-3">
                    <img src="{{ asset('images/dashboard-logo.svg') }}" alt="Logo" class="h-12 w-auto object-contain max-w-[200px]" onerror="this.style.display='none'">
                </div>
            @endif

            <span class="text-[11px] font-mono font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-1" 
                  style="background-color: {{ $settings->primary_color }}15; color: {{ $settings->primary_color }};">
                {{ $settings->organization_name }}
            </span>

            <h1 class="{{ $titleScale }} font-black tracking-tight" style="color: {{ $settings->primary_color }};">
                {{ $settings->header_title }}
            </h1>

            <p class="text-xs text-slate-500 font-medium mt-0.5">
                {{ $settings->subtitle }}
            </p>
        </div>

        <!-- Receipt Metadata Ribbon -->
        <div class="relative z-10 py-4 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 font-mono text-xs">
            <div class="flex items-center gap-2">
                <span class="text-slate-400 font-bold uppercase text-[10px]">Receipt No:</span>
                <span class="px-2.5 py-1 rounded-lg font-black tracking-wider text-white shadow-2xs" style="background-color: {{ $settings->primary_color }};">
                    {{ $receipt->receipt_number }}
                </span>
            </div>

            <div class="flex items-center gap-2 text-slate-600">
                <span class="text-slate-400 font-bold uppercase text-[10px]">Date & Time:</span>
                <span class="font-bold text-slate-800">{{ $receipt->created_at->format('d M Y, h:i A') }}</span>
            </div>
        </div>

        <!-- Donor Information & Contribution Details -->
        <div class="relative z-10 py-6 space-y-4">
            <!-- Donor Name & Place -->
            <div class="bg-slate-50/70 p-4 rounded-2xl border border-slate-200/70">
                <div class="text-[10px] uppercase font-mono font-bold tracking-wider text-slate-400">Received With Thanks From:</div>
                <div class="text-lg sm:text-xl font-black text-slate-900 mt-1 uppercase tracking-tight">
                    {{ $receipt->donor_name }}
                </div>
                @if($receipt->donor_place || $receipt->donor_phone)
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600 font-medium mt-1 font-mono">
                        @if($receipt->donor_place)
                            <span>Location: <strong class="text-slate-800">{{ $receipt->donor_place }}</strong></span>
                        @endif
                        @if($receipt->donor_phone)
                            @if($receipt->donor_place) <span>•</span> @endif
                            <span>Phone: <strong class="text-slate-800">{{ $receipt->donor_phone }}</strong></span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Amount Highlight Box -->
            <div class="p-5 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 border"
                 style="background-color: {{ $settings->primary_color }}08; border-color: {{ $settings->primary_color }}25;">
                <div>
                    <div class="text-[10px] uppercase font-mono font-bold tracking-wider" style="color: {{ $settings->primary_color }};">
                        Amount Received / ലഭിച്ച തുക
                    </div>
                    <div class="text-2xl sm:text-3xl font-black font-mono mt-1" style="color: {{ $settings->primary_color }};">
                        ₹{{ number_format($receipt->amount, 2) }}
                    </div>
                </div>

                <div class="sm:text-right font-mono text-xs">
                    <span class="inline-flex items-center px-3 py-1 rounded-full font-bold uppercase text-[11px] shadow-2xs text-white" 
                          style="background-color: {{ $settings->accent_color }};">
                        Mode: {{ strtoupper($receipt->payment_method) }}
                    </span>
                    @if($receipt->transaction_reference)
                        <div class="text-[10px] text-slate-500 mt-1">
                            Txn Ref: {{ $receipt->transaction_reference }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Amount in Words -->
            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200/80 font-mono text-xs">
                <span class="text-[10px] uppercase font-bold text-slate-400 mr-2">In Words:</span>
                <span class="font-bold text-slate-900">{{ $receipt->amount_in_words }}</span>
            </div>

            <!-- Purpose & Category -->
            <div class="grid grid-cols-2 gap-3 text-xs font-mono pt-1">
                <div class="p-3 rounded-xl bg-slate-50/50 border border-slate-200/60">
                    <div class="text-[10px] uppercase text-slate-400 font-bold">Purpose / Category</div>
                    <div class="font-bold text-slate-800 mt-0.5">{{ $receipt->category ?? 'Festival Fund' }}</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-50/50 border border-slate-200/60">
                    <div class="text-[10px] uppercase text-slate-400 font-bold">Issued By</div>
                    <div class="font-bold text-slate-800 mt-0.5">{{ $receipt->collector_name ?? 'QUAF Fund Desk' }}</div>
                </div>
            </div>

            @if($receipt->notes)
                <div class="p-3 rounded-xl bg-slate-50/50 border border-slate-200/60 text-xs">
                    <span class="text-[10px] font-mono font-bold uppercase text-slate-400">Memo / Notes:</span>
                    <span class="text-slate-700 ml-1">{{ $receipt->notes }}</span>
                </div>
            @endif

            @if($receipt->is_cancelled)
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>VOID / CANCELLED VOUCHER: {{ $receipt->cancelled_reason ?? 'Cancelled by Admin' }}</span>
                    </div>
                    <span class="font-mono text-[10px]">{{ $receipt->cancelled_at?->format('d M Y') }}</span>
                </div>
            @endif
        </div>

        <!-- Receipt Footer & Verification / Signatures -->
        <div class="relative z-10 pt-6 mt-4 border-t border-dashed border-slate-300/80 flex flex-col sm:flex-row items-center justify-between gap-6">
            <!-- QR Code Matrix for Verification -->
            <div class="flex items-center gap-3">
                <div class="w-16 h-16 p-1 bg-white rounded-xl border border-slate-300 shrink-0">
                    {!! \App\Services\QrCodeService::svg(route('fund.receipts.show', $receipt), 100) !!}
                </div>
                <div class="text-[10px] font-mono text-slate-500 leading-tight">
                    <div class="font-bold text-slate-800">Scan to Verify</div>
                    <div>Official Digital Voucher</div>
                    <div class="text-emerald-700 font-bold">Valid & Authenticated</div>
                </div>
            </div>

            <!-- Signature & Stamp Placeholder -->
            <div class="text-center sm:text-right">
                <div class="h-10 flex items-end justify-center sm:justify-end mb-1">
                    <span class="font-mono text-xs italic text-slate-400 font-semibold tracking-wider">Signed Digitally</span>
                </div>
                <div class="w-44 border-t border-slate-400/60 mx-auto sm:ml-auto"></div>
                <div class="text-[11px] font-bold text-slate-800 mt-1 uppercase tracking-wider font-mono">
                    {{ $settings->authorized_by }}
                </div>
            </div>
        </div>

        <!-- Bottom Footer Note -->
        @if($settings->footer_text)
            <div class="relative z-10 mt-6 pt-4 border-t border-slate-200/60 text-center text-[11px] text-slate-500 {{ $alignClass }}">
                {{ $settings->footer_text }}
            </div>
        @endif
    </div>

</body>
</html>
