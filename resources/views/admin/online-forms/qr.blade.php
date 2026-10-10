@extends('layouts.admin', ['title' => 'QR Code: ' . ($form->program?->name ?? $form->title) . ' | QUAF'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6 text-center">

    <!-- Header Actions -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200 print:hidden">
        <a href="{{ route('admin.online-forms.index') }}" class="text-xs font-mono text-slate-500 hover:text-[#be1e2d] transition">
            &larr; Back to Forms
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" 
                    class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-mono font-bold flex items-center gap-1.5 transition cursor-pointer">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print QR Sheet</span>
            </button>
            <a href="{{ $publicUrl }}" 
               target="_blank" 
               class="px-4 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01824] text-white text-xs font-mono font-bold flex items-center gap-1.5 transition">
                <span>Open Form &rarr;</span>
            </a>
        </div>
    </div>

    <!-- Presentation QR Card (Printable) -->
    <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-12 shadow-xl relative overflow-hidden flex flex-col items-center">
        
        <div class="flex items-center gap-2 mb-3">
            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-[#be1e2d] text-white uppercase tracking-wider">
                QUAF DIGITAL SUBMISSION
            </span>
        </div>

        <h2 class="font-sora text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-1">
            {{ $form->program?->name ?? $form->title }}
        </h2>
        
        <p class="text-xs font-mono text-slate-500 mb-6">
            Program Code: <strong class="text-slate-800">{{ $form->program?->code }}</strong> • Category: <strong class="text-slate-800">{{ $form->program?->category?->name ?? 'General' }}</strong>
        </p>

        <!-- QR Code Container -->
        <div class="p-5 rounded-3xl bg-slate-50 border-2 border-slate-200 shadow-md mb-6 max-w-xs w-full flex items-center justify-center">
            @if(str_starts_with(trim($qrCodeSvg), '<img'))
                <div class="w-64 h-64 sm:w-72 sm:h-72 flex items-center justify-center">
                    {!! $qrCodeSvg !!}
                </div>
            @else
                <img src="{{ $qrCodeSvg }}" 
                     alt="Submission QR Code" 
                     class="w-64 h-64 sm:w-72 sm:h-72 object-contain rounded-xl">
            @endif
        </div>

        <div class="space-y-2 max-w-md">
            <h3 class="font-sora text-base font-bold text-slate-900">
                Scan to Submit Your Entry
            </h3>
            <p class="text-xs text-slate-600 leading-relaxed">
                മത്സരാർത്ഥികൾ മൊബൈൽ ക്യാമറ ഉപയോഗിച്ച് മുകളിലെ ക്യുആർ കോഡ് സ്കാൻ ചെയ്യുക. തുടർന്ന് നിങ്ങളുടെ <span class="font-bold text-[#be1e2d]">കോഡ് ലെറ്റർ (Code Letter)</span> നൽകി രചന സമർപ്പിക്കുക.
            </p>
        </div>

        <!-- URL Copy Box -->
        <div class="mt-6 pt-6 border-t border-slate-100 w-full max-w-md flex items-center gap-2"
             x-data="{ copied: false }">
            <input type="text" 
                   readonly 
                   value="{{ $publicUrl }}" 
                   class="flex-1 px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 font-mono text-xs text-slate-700">
            <button type="button" 
                    @click="navigator.clipboard.writeText('{{ $publicUrl }}'); copied = true; setTimeout(() => copied = false, 2500)"
                    class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-xs transition cursor-pointer">
                <span x-text="copied ? 'Copied!' : 'Copy Link'"></span>
            </button>
        </div>

    </div>

</div>
@endsection
