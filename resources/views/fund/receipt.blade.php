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
            size: portrait;
            margin: 6mm;
        }

        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                background: transparent !important;
            }
            .receipt-graphic-poster {
                max-height: 98vh !important;
                max-width: 100% !important;
                width: auto !important;
                margin: 0 auto !important;
                display: block !important;
                box-shadow: none !important;
                border-radius: 0 !important;
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
@php
    $downloadUrl = Auth::user()?->isAdmin() 
        ? route('admin.fund.receipts.download', $receipt) 
        : route('fund.receipts.download', $receipt);

    $imageUrl = Auth::user()?->isAdmin() 
        ? route('admin.fund.receipts.image', $receipt) 
        : route('fund.receipts.image', $receipt);

    $canEdit = (! $receipt->is_cancelled) && (
        (session('fund_student_id') && (int) session('fund_student_id') === (int) $receipt->student_id) ||
        (Auth::check() && (
            (method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin()) ||
            in_array(Auth::user()->role, ['admin', 'super_admin', 'fund_manager', 'fund_collector'])
        ))
    );
@endphp
<body class="bg-slate-900 min-h-screen py-6 px-3 sm:px-6 font-sora antialiased text-slate-100"
      x-data="{ 
          copied: false,
          activeTab: 'poster',
          sharing: false,
          showEditModal: false,
          editForm: {
              donor_name: @js($receipt->donor_name),
              payment_method: @js($receipt->payment_method),
              transaction_reference: @js($receipt->transaction_reference ?? ''),
              donor_phone: @js($receipt->donor_phone ?? ''),
              donor_place: @js($receipt->donor_place ?? '')
          },
          copyLink() {
              navigator.clipboard.writeText('{{ route('fund.receipts.show', $receipt) }}');
              this.copied = true;
              setTimeout(() => this.copied = false, 2500);
          },
          async shareWhatsAppImage() {
              this.sharing = true;
              const donorName = @js($receipt->donor_name);
              const donorPlace = @js($receipt->donor_place ?? '');
              const donorDisplayName = donorPlace ? `${donorName}, ${donorPlace}` : donorName;
              const shareMessage = `ജസാഖല്ലാഹു ഖൈർ...
*${donorDisplayName}*
നിങ്ങളുടെ ഈ ഹദിയ അല്ലാഹു സ്വീകരിക്കട്ടെ... 

ഖാഫ് അദബിക് എഡിഷനിൽ ഞങ്ങളോടൊപ്പം ചേരാൻ മർകസിലേക്ക് നിങ്ങൾക്കും ഹൃദ്യമായ സ്വാഗതം`;

              try {
                  if (navigator.share) {
                      const response = await fetch('{{ $imageUrl }}');
                      const blob = await response.blob();
                      const file = new File([blob], '{{ $receipt->receipt_number }}.jpg', { type: 'image/jpeg' });
                      if (navigator.canShare && navigator.canShare({ files: [file] })) {
                          await navigator.share({
                              files: [file],
                              title: 'QUAF Receipt #{{ $receipt->receipt_number }}',
                              text: shareMessage
                          });
                          this.sharing = false;
                          return;
                      }
                  }
              } catch (err) {
                  console.warn('Share file error or canceled:', err);
              }

              // Fallback: download file and open WhatsApp Web / App
              const a = document.createElement('a');
              a.href = '{{ $downloadUrl }}';
              a.download = '{{ $receipt->receipt_number }}.jpg';
              document.body.appendChild(a);
              a.click();
              document.body.removeChild(a);
              const fallbackUrl = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(shareMessage + '\n\n' + window.location.href);
              window.open(fallbackUrl, '_blank');
              this.sharing = false;
          }
      }">

    <!-- Top Action Ribbon (Hidden when printing) -->
    <div class="max-w-2xl mx-auto mb-6 no-print space-y-4">
        <!-- Navigation Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 bg-slate-800/80 backdrop-blur-md p-3.5 rounded-2xl border border-slate-700/80 shadow-lg">
            <div class="flex items-center gap-2">
                @if(session('fund_student_id'))
                    <a href="{{ route('fund.student.desk') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs transition-all shadow-xs">
                        <span>← My Desk</span>
                    </a>
                    <a href="{{ route('fund.student.desk') }}#new-receipt-card"
                       class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all">
                        <span>+ New</span>
                    </a>
                @else
                    <a href="{{ route('fund.home') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs transition-all shadow-xs">
                        <span>← Fund Home</span>
                    </a>
                @endif
            </div>

            <!-- Action Controls (Edit & Mode Switcher) -->
            <div class="flex items-center gap-2">
                @if($canEdit)
                    <button type="button" @click="showEditModal = true"
                            class="px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition-all shadow-xs flex items-center gap-1.5 cursor-pointer active:scale-98">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>Edit Details</span>
                    </button>
                @endif

                <div class="flex items-center gap-1 bg-slate-900/80 p-1 rounded-xl border border-slate-700/60 text-xs font-bold">
                    <button type="button" @click="activeTab = 'poster'"
                            :class="activeTab === 'poster' ? 'bg-amber-500 text-slate-950 shadow-xs' : 'text-slate-400 hover:text-white'"
                            class="px-3 py-1.5 rounded-lg transition-all cursor-pointer">
                        Receipt Card
                    </button>
                    <button type="button" @click="activeTab = 'ledger'"
                            :class="activeTab === 'ledger' ? 'bg-amber-500 text-slate-950 shadow-xs' : 'text-slate-400 hover:text-white'"
                            class="px-3 py-1.5 rounded-lg transition-all cursor-pointer">
                        Details
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Action Buttons Row -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
            <!-- Download JPG -->
            <a href="{{ $downloadUrl }}" 
               class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-amber-500/20 active:scale-98">
                <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Download JPG</span>
            </a>

            <!-- Print Receipt Voucher -->
            <button type="button" onclick="window.print()" 
                    class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-md shadow-emerald-600/20 cursor-pointer active:scale-98">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print</span>
            </button>

            <!-- Share WhatsApp Image -->
            <button type="button" @click="shareWhatsAppImage()" :disabled="sharing"
               class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-[#25D366] hover:bg-[#1ebd5b] text-slate-950 font-bold text-xs uppercase tracking-wider transition-all shadow-md shadow-[#25D366]/20 active:scale-98 cursor-pointer">
                <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                <span x-text="sharing ? 'Preparing...' : 'WhatsApp Image'">WhatsApp Image</span>
            </button>

            <!-- Copy Link -->
            <button type="button" @click="copyLink()" 
                    class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs uppercase tracking-wider transition-all border border-slate-700 cursor-pointer active:scale-98">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                <span x-text="copied ? 'Copied!' : 'Copy Link'">Copy Link</span>
            </button>
        </div>

        @if(session('success'))
            <div class="p-3.5 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-200 text-xs font-bold flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <span class="text-[11px] font-mono text-emerald-300">#{{ $receipt->receipt_number }}</span>
            </div>
        @endif
    </div>

    <!-- MAIN RECEIPT DISPLAY CONTAINER -->
    <div class="max-w-2xl mx-auto print-wrapper">

        <!-- TAB 1: THE GRAPHIC POSTER VOUCHER (DEFAULT) -->
        <div x-show="activeTab === 'poster'" class="transition-all duration-300">
            <div class="relative bg-slate-950 p-2 sm:p-4 rounded-3xl border border-slate-800 shadow-2xl overflow-hidden">
                <!-- Glowing Accent Glow -->
                <div class="absolute -top-24 -left-24 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <!-- The Exact Generated Image Poster -->
                <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-amber-500/20 bg-black">
                    <img src="{{ $imageUrl }}" 
                         alt="QUAF Official Receipt {{ $receipt->receipt_number }}" 
                         class="receipt-graphic-poster w-full h-auto block select-none">
                </div>

                <!-- Footer Hint in Dark Mode (hidden in print) -->
                <div class="mt-3.5 px-3 py-2 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400 font-mono no-print">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-slate-300 font-bold">1126 x 1407 HD Voucher</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ $downloadUrl }}" class="text-amber-400 hover:underline font-bold">Download JPG</a>
                        <span>•</span>
                        <button type="button" @click="activeTab = 'ledger'" class="text-slate-300 hover:text-white underline">View Data</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: DETAILED LEDGER BREAKDOWN & AUDIT METADATA -->
        <div x-show="activeTab === 'ledger'" x-cloak class="transition-all duration-300 space-y-4">
            <div class="bg-white text-slate-900 rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                    <div>
                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                            Official Digital Record
                        </span>
                        <h2 class="text-xl font-black text-slate-900 mt-2">Fund Contribution Voucher</h2>
                        <p class="text-xs text-slate-500 font-mono">QUAF '09 • Ihya-Us-Sunna Arts Fest</p>
                    </div>
                    <div class="text-right font-mono">
                        <div class="text-[10px] uppercase text-slate-400 font-bold">Receipt No</div>
                        <div class="text-base font-black text-slate-900">{{ $receipt->receipt_number }}</div>
                    </div>
                </div>

                <!-- Donor Card -->
                @php
                    $isMalayalam = (bool) preg_match('/[\x{0D00}-\x{0D7F}]/u', (string) $receipt->donor_name);
                @endphp
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-2 relative">
                    <div class="flex items-center justify-between">
                        <div class="text-[10px] uppercase font-mono font-bold tracking-wider text-slate-400">Contributor Details</div>
                        @if($canEdit)
                            <button type="button" @click="showEditModal = true"
                                    class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[11px] border border-blue-200 flex items-center gap-1 cursor-pointer transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                <span>Edit Name</span>
                            </button>
                        @endif
                    </div>
                    <div class="text-lg font-black text-slate-900 {{ $isMalayalam ? 'font-anek' : 'uppercase font-sora' }}">
                        {{ $receipt->donor_name }}
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-xs font-mono text-slate-600 pt-1">
                        <div>
                            <span class="text-slate-400">Phone:</span>
                            <span class="font-bold text-slate-800">{{ $receipt->donor_phone ?: 'Not provided' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Place:</span>
                            <span class="font-bold text-slate-800">{{ $receipt->donor_place ?: 'Not provided' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Amount Breakdown -->
                <div class="p-5 rounded-2xl bg-emerald-50/70 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="text-[10px] uppercase font-mono font-bold tracking-wider text-emerald-800">Contribution Amount</div>
                        <div class="text-3xl font-black font-mono text-emerald-900 mt-1">
                            ₹{{ number_format($receipt->amount, 2) }}
                        </div>
                        <div class="text-xs font-mono text-emerald-700 mt-1">
                            {{ $receipt->amount_in_words }}
                        </div>
                    </div>

                    <div class="sm:text-right font-mono text-xs">
                        <div class="flex items-center sm:justify-end gap-2">
                            <span class="inline-block px-3 py-1 rounded-full font-bold uppercase text-[11px] bg-emerald-700 text-white shadow-2xs">
                                {{ strtoupper($receipt->payment_method) }}
                            </span>
                            @if($canEdit)
                                <button type="button" @click="showEditModal = true"
                                        class="px-2 py-0.5 rounded text-[10px] font-bold text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 cursor-pointer transition-colors">
                                    Change Mode
                                </button>
                            @endif
                        </div>
                        @if($receipt->transaction_reference)
                            <div class="text-[10px] text-slate-600 mt-1.5">
                                Ref: {{ $receipt->transaction_reference }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Key Attributes -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs font-mono">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] uppercase text-slate-400 font-bold">Category</div>
                        <div class="font-bold text-slate-800 mt-0.5">{{ $receipt->category ?? 'Festival Fund' }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] uppercase text-slate-400 font-bold">Issued By</div>
                        <div class="font-bold text-slate-800 mt-0.5">{{ $receipt->collector_name ?? 'QUAF Fund Desk' }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 col-span-2 sm:col-span-1">
                        <div class="text-[10px] uppercase text-slate-400 font-bold">Date & Time</div>
                        <div class="font-bold text-slate-800 mt-0.5">{{ $receipt->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                </div>

                @if($receipt->notes)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                        <span class="text-[10px] font-mono font-bold uppercase text-slate-400">Notes / Memo:</span>
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

                <!-- QR Code Verification Block -->
                <div class="pt-4 border-t border-slate-200 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-16 h-16 p-1 bg-white rounded-xl border border-slate-300 shrink-0">
                            {!! \App\Services\QrCodeService::svg(route('fund.receipts.show', $receipt), 100) !!}
                        </div>
                        <div class="text-[10px] font-mono text-slate-500 leading-tight">
                            <div class="font-bold text-slate-800">Scan to Verify</div>
                            <div>QUAF '09 Secured Record</div>
                            <div class="text-emerald-700 font-bold">Valid & Authenticated</div>
                        </div>
                    </div>

                    <button type="button" @click="activeTab = 'poster'" 
                            class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors">
                        View Card Poster ↗
                    </button>
                </div>
            </div>
        </div>

    </div>

    @if($canEdit)
        <!-- =====================================================================
             RECEIPT EDIT MODAL (DONOR NAME & PAYMENT METHOD)
             ===================================================================== -->
        <div x-show="showEditModal" 
             x-cloak 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/80 backdrop-blur-md"
             style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; height: 100dvh !important; z-index: 99999 !important; display: flex !important; align-items: center !important; justify-content: center !important; background-color: rgba(10, 15, 30, 0.8) !important; -webkit-backdrop-filter: blur(8px) !important; backdrop-filter: blur(8px) !important;"
             @keydown.escape.window="showEditModal = false">
            
            <!-- Backdrop Click to Close -->
            <div class="fixed inset-0" style="position: absolute; inset: 0; width: 100%; height: 100%;" @click="showEditModal = false"></div>

            <!-- Pure White Apple-Style Dialog Card -->
            <div x-show="showEditModal"
                 x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
                 x-transition:enter-start="opacity-0 translate-y-6 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-6 scale-95"
                 class="relative w-full max-w-lg bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100 z-10 text-slate-900 space-y-5"
                 style="position: relative !important; z-index: 100000 !important; background-color: #ffffff !important; color: #0f172a !important; border-radius: 24px !important; box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.45) !important; max-height: calc(100dvh - 32px) !important; overflow-y: auto !important;"
                 @click.stop>
                
                <!-- Apple-Style Circular Close Button -->
                <button type="button" 
                        @click="showEditModal = false"
                        class="absolute top-5 right-5 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-800 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- Modal Header -->
                <div class="flex items-center gap-3.5 pr-8">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-200 shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-blue-50 text-blue-800 border border-blue-200">
                                Edit Receipt
                            </span>
                            <span class="text-xs font-mono font-bold text-slate-500">#{{ $receipt->receipt_number }}</span>
                        </div>
                        <h3 class="font-sora text-lg sm:text-xl font-bold text-slate-900 tracking-tight mt-0.5">
                            രസീത് വിവരങ്ങൾ തിരുത്തുക
                        </h3>
                    </div>
                </div>

                <!-- Edit Form -->
                <form method="POST" action="{{ route('fund.receipts.update', $receipt) }}" class="space-y-4 pt-1">
                    @csrf
                    @method('PUT')

                    <!-- 1. Donor Name (Malayalam / English) -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 font-mono">
                            Donor Name / ദാതാവിന്റെ പേര് <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="donor_name" 
                               x-model="editForm.donor_name" 
                               required
                               placeholder="Enter name (e.g. മുഹമ്മദ് ഷാഫി / Mohammed Shafi)"
                               style="font-family: 'Sora', 'Anek Malayalam', sans-serif;"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 focus:bg-white border border-slate-200 text-sm font-semibold text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>

                    <!-- 2. Payment Method (Cash, UPI, etc.) -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 font-mono">
                            Payment Mode / പേയ്മെന്റ് രീതി <span class="text-rose-500">*</span>
                        </label>
                        <select name="payment_method" 
                                x-model="editForm.payment_method" 
                                required
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 focus:bg-white border border-slate-200 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                            <option value="upi">UPI / GPay / PhonePe (യു.പി.ഐ)</option>
                            <option value="cash">Cash (പണം)</option>
                            <option value="bank_transfer">Bank Transfer / NEFT</option>
                            <option value="card">Debit / Credit Card</option>
                            <option value="other">Other Mode (മറ്റുള്ളവ)</option>
                        </select>
                    </div>

                    <!-- 3. Transaction Reference (Shown prominently if UPI or other non-cash) -->
                    <div class="space-y-1" x-show="editForm.payment_method !== 'cash'">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 font-mono">
                            UPI Reference / Transaction ID
                        </label>
                        <input type="text" 
                               name="transaction_reference" 
                               x-model="editForm.transaction_reference" 
                               placeholder="e.g. UPI Ref, GPay Txn ID, UTR"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 focus:bg-white border border-slate-200 text-sm font-mono text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>

                    <!-- 4. Phone & Place (Optional) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 font-mono">
                                Mobile Number
                            </label>
                            <input type="tel" 
                                   name="donor_phone" 
                                   x-model="editForm.donor_phone" 
                                   placeholder="Phone number"
                                   class="w-full px-3.5 py-2 rounded-xl bg-slate-50 focus:bg-white border border-slate-200 text-xs font-mono text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 font-mono">
                                Place / സ്ഥലം
                            </label>
                            <input type="text" 
                                   name="donor_place" 
                                   x-model="editForm.donor_place" 
                                   placeholder="e.g. Kozhikode"
                                   class="w-full px-3.5 py-2 rounded-xl bg-slate-50 focus:bg-white border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" 
                                @click="showEditModal = false"
                                class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs uppercase tracking-wider transition-all cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-blue-600/20 cursor-pointer active:scale-98">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</body>
</html>
