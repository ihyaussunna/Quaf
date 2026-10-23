@extends('layouts.admin', ['title' => 'Badge Template | Quaf'])

@section('content')
<div class="space-y-8" x-data="{
    participantName: 'Muhammed Nihal',
    chestNo: '101',
    category: 'A Zone',
    group: 'Al Falah'
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 font-sora tracking-tight">Participant Badge Template</h1>
            <p class="text-xs text-slate-500 mt-1 font-sora">Official template for participant ID badges & festival entry passes.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.idcards.print') }}" target="_blank" class="px-4 py-2 rounded-xl bg-[#be1e2d] text-white font-semibold text-xs hover:bg-[#a01624] transition-all flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Batch Print Badges</span>
            </a>
            <a href="{{ route('admin.idcards.chest-slips') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-all flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Chest Slips</span>
            </a>
        </div>
    </div>

    <!-- Interactive Customization & Dynamic Preview Canvas -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Left Column: Customization Controls (5 cols on lg) -->
        <div class="lg:col-span-5 bg-white border border-slate-200 rounded-2xl p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-red-50 text-[#be1e2d] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm font-sora">Customize Badge</h3>
                </div>
                <button @click="participantName = 'Muhammed Nihal'; chestNo = '101'; category = 'A Zone'; group = 'Al Falah'"
                        class="text-[11px] text-[#be1e2d] hover:underline font-medium">
                    Reset Defaults
                </button>
            </div>

            <!-- Inputs -->
            <div class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-600 font-medium mb-1">Participant Full Name</label>
                    <input type="text" x-model="participantName"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-600 font-medium mb-1">Chest Number</label>
                        <input type="text" x-model="chestNo"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 font-mono focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-slate-600 font-medium mb-1">Zone</label>
                        <input type="text" x-model="category"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-600 font-medium mb-1">Group</label>
                    <input type="text" x-model="group"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all">
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-3 border-t border-slate-100 flex items-center gap-2">
                <button onclick="window.print()" class="flex-1 py-2.5 rounded-xl bg-slate-900 text-white font-semibold text-xs hover:bg-slate-800 transition-all flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Print Badge</span>
                </button>
            </div>
        </div>

        <!-- Right Column: Dynamic Preview Canvas (7 cols on lg) -->
        <div class="lg:col-span-7 bg-slate-50 border border-slate-200 rounded-2xl p-6 shadow-2xs flex flex-col items-center justify-center min-h-[460px]">
            <div class="w-full flex items-center justify-between mb-4">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Live Badge Preview</span>
                <span class="text-[11px] font-mono text-[#be1e2d] bg-red-50 px-2 py-0.5 rounded border border-orange-100">Badge Mode</span>
            </div>

            <!-- Badges Preview -->
            <div class="w-72 bg-white rounded-2xl border-2 border-slate-200 overflow-hidden shadow-md flex flex-col text-center transition-all">
                <div class="bg-gradient-to-r from-[#be1e2d] to-[#a01624] text-white py-4 px-3">
                    <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF" class="h-7 w-auto object-contain brightness-0 invert mx-auto mb-1.5">
                    <h4 class="font-black text-sm tracking-wide">QUAF FESTIVAL</h4>
                    <p class="text-[10px] text-white/80 uppercase">Official Participant Pass</p>
                </div>
                <div class="p-5 space-y-3">
                    <!-- Photo placeholder -->
                    <div class="w-20 h-20 rounded-xl bg-slate-100 border-2 border-slate-200 flex items-center justify-center mx-auto text-slate-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 text-base" x-text="participantName"></div>
                        <div class="text-xs font-mono font-bold text-[#be1e2d]" x-text="'CHEST NO: #' + chestNo"></div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-100 text-[11px] space-y-1">
                        <div class="text-slate-600"><span class="text-slate-400">Zone:</span> <span class="font-semibold text-slate-800" x-text="category"></span></div>
                        <div class="text-slate-600"><span class="text-slate-400">Group:</span> <span class="font-semibold text-slate-800" x-text="group"></span></div>
                    </div>
                    <!-- QR placeholder -->
                    <div class="w-14 h-14 bg-slate-900 text-white rounded-lg flex items-center justify-center mx-auto text-[9px] font-mono">
                        QR PASS
                    </div>
                </div>
                <div class="bg-slate-50 py-2 border-t border-slate-100 text-[10px] text-slate-400 font-mono">
                    VALID FOR QUAF 2026
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
