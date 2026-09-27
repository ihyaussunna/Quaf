@extends('layouts.admin', ['title' => 'Document & Badge Templates | Quaf'])

@section('content')
<div class="space-y-8" x-data="{
    activeTab: 'badges',
    participantName: 'Muhammed Nihal',
    chestNo: '101',
    category: 'A Zone',
    unit: 'Al Falah Unit',
    competition: 'Arabana (10 Mem)',
    grade: 'A Grade',
    position: '1st Place'
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 font-sora tracking-tight">Certificate & Document Templates</h1>
            <p class="text-xs text-slate-500 mt-1 font-sora">Official high-resolution templates for participant badges, certificates, prize certificates, and publication posters.</p>
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

    <!-- Template Library Selection Tabs -->
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
        <button @click="activeTab = 'badges'"
                :class="activeTab === 'badges' ? 'bg-[#be1e2d] text-white shadow-2xs font-semibold' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
            <span>Badges</span>
        </button>

        <button @click="activeTab = 'participation'"
                :class="activeTab === 'participation' ? 'bg-[#be1e2d] text-white shadow-2xs font-semibold' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"></path></svg>
            <span>Participation Certificate</span>
        </button>

        <button @click="activeTab = 'places'"
                :class="activeTab === 'places' ? 'bg-[#be1e2d] text-white shadow-2xs font-semibold' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
            <span>1st/2nd/3rd Place Certificates</span>
        </button>

        <button @click="activeTab = 'common_prize'"
                :class="activeTab === 'common_prize' ? 'bg-[#be1e2d] text-white shadow-2xs font-semibold' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
            <span>Common Prize Certificate</span>
        </button>

        <button @click="activeTab = 'grade'"
                :class="activeTab === 'grade' ? 'bg-[#be1e2d] text-white shadow-2xs font-semibold' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>Grade Certificate</span>
        </button>

        <button @click="activeTab = 'poster'"
                :class="activeTab === 'poster' ? 'bg-[#be1e2d] text-white shadow-2xs font-semibold' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span>Result Poster</span>
        </button>
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
                    <h3 class="font-bold text-slate-900 text-sm font-sora">Customize Template Canvas</h3>
                </div>
                <button @click="participantName = 'Muhammed Nihal'; chestNo = '101'; category = 'A Zone'; unit = 'Al Falah Unit'; competition = 'Arabana (10 Mem)'; grade = 'A Grade'; position = '1st Place'"
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
                    <label class="block text-slate-600 font-medium mb-1">Unit / Team Name</label>
                    <input type="text" x-model="unit"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block text-slate-600 font-medium mb-1">Competition / Event</label>
                    <input type="text" x-model="competition"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-600 font-medium mb-1">Position / Rank</label>
                        <select x-model="position"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all">
                            <option value="1st Place">1st Place</option>
                            <option value="2nd Place">2nd Place</option>
                            <option value="3rd Place">3rd Place</option>
                            <option value="Special Mention">Special Mention</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-600 font-medium mb-1">Grade</label>
                        <select x-model="grade"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all">
                            <option value="A Grade">A Grade</option>
                            <option value="B Grade">B Grade</option>
                            <option value="C Grade">C Grade</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-3 border-t border-slate-100 flex items-center gap-2">
                <button onclick="window.print()" class="flex-1 py-2.5 rounded-xl bg-slate-900 text-white font-semibold text-xs hover:bg-slate-800 transition-all flex items-center justify-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Print Current Canvas</span>
                </button>
            </div>
        </div>

        <!-- Right Column: Dynamic Preview Canvas (7 cols on lg) -->
        <div class="lg:col-span-7 bg-slate-50 border border-slate-200 rounded-2xl p-6 shadow-2xs flex flex-col items-center justify-center min-h-[460px]">
            <div class="w-full flex items-center justify-between mb-4">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Live Canvas Preview</span>
                <span class="text-[11px] font-mono text-[#be1e2d] bg-red-50 px-2 py-0.5 rounded border border-orange-100" x-text="'Mode: ' + activeTab"></span>
            </div>

            <!-- Canvas 1: Badges Preview -->
            <div x-show="activeTab === 'badges'" class="w-72 bg-white rounded-2xl border-2 border-slate-200 overflow-hidden shadow-md flex flex-col text-center transition-all">
                <div class="bg-gradient-to-r from-[#be1e2d] to-[#a01624] text-white py-4 px-3">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center mx-auto text-white font-black text-sm mb-1">Q</div>
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
                        <div class="text-slate-600"><span class="text-slate-400">Unit:</span> <span class="font-semibold text-slate-800" x-text="unit"></span></div>
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

            <!-- Canvas 2: Participation Certificate Preview -->
            <div x-show="activeTab === 'participation'" class="w-full max-w-lg bg-white rounded-2xl border-4 border-double border-slate-300 p-6 text-center shadow-md space-y-3" style="display: none;">
                <div class="text-xs font-mono tracking-widest text-slate-400 uppercase">Certificate of Participation</div>
                <h3 class="text-2xl font-bold text-slate-900">QUAF FESTIVAL 2026</h3>
                <p class="text-xs text-slate-500 italic max-w-sm mx-auto">This is proudly presented to</p>
                <div class="text-xl font-bold text-[#be1e2d] border-b-2 border-slate-200 pb-1 inline-block px-4" x-text="participantName"></div>
                <p class="text-xs text-slate-600 max-w-sm mx-auto">
                    representing <strong x-text="unit"></strong> for active participation in the event <strong x-text="competition"></strong> under zone <strong x-text="category"></strong>.
                </p>
                <div class="flex items-center justify-between pt-6 text-[10px] text-slate-400 font-mono">
                    <div>Chairman</div>
                    <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center mx-auto text-[9px]">SEAL</div>
                    <div>General Convener</div>
                </div>
            </div>

            <!-- Canvas 3: 1st/2nd/3rd Place Certificate Preview -->
            <div x-show="activeTab === 'places'" class="w-full max-w-lg bg-white rounded-2xl border-4 border-double border-amber-300 p-6 text-center shadow-md space-y-3" style="display: none;">
                <div class="inline-block px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-bold uppercase tracking-wider" x-text="position + ' WINNER'"></div>
                <h3 class="text-2xl font-bold text-slate-900">CERTIFICATE OF MERIT</h3>
                <p class="text-xs text-slate-500 italic">Awarded to</p>
                <div class="text-2xl font-bold text-slate-900 border-b-2 border-amber-200 pb-1 inline-block px-4" x-text="participantName"></div>
                <p class="text-xs text-slate-600 max-w-sm mx-auto">
                    of <strong x-text="unit"></strong> (Chest #<span x-text="chestNo"></span>) has secured <strong class="text-amber-800" x-text="position"></strong> with <strong class="text-[#be1e2d]" x-text="grade"></strong> in the competition <strong x-text="competition"></strong> at Quaf Festival 2026.
                </p>
                <div class="flex items-center justify-between pt-6 text-[10px] text-slate-400 font-mono">
                    <div>Jury Chairman</div>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-300 to-amber-500 text-slate-900 font-black flex items-center justify-center mx-auto text-[9px] shadow-xs">MERIT</div>
                    <div>Event Director</div>
                </div>
            </div>

            <!-- Canvas 4: Common Prize Certificate -->
            <div x-show="activeTab === 'common_prize'" class="w-full max-w-lg bg-white rounded-2xl border-4 border-double border-blue-200 p-6 text-center shadow-md space-y-3" style="display: none;">
                <div class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-900 text-xs font-bold uppercase tracking-wider">SPECIAL RECOGNITION</div>
                <h3 class="text-2xl font-bold text-slate-900">COMMON PRIZE CERTIFICATE</h3>
                <div class="text-xl font-bold text-blue-900 border-b-2 border-blue-100 pb-1 inline-block px-4" x-text="participantName"></div>
                <p class="text-xs text-slate-600 max-w-sm mx-auto">
                    Honored with the prize for outstanding group & team contribution in <strong x-text="competition"></strong> representing <strong x-text="unit"></strong>.
                </p>
                <div class="flex items-center justify-between pt-6 text-[10px] text-slate-400 font-mono">
                    <div>Coordinator</div>
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-bold flex items-center justify-center mx-auto text-[9px]">PRIZE</div>
                    <div>Convener</div>
                </div>
            </div>

            <!-- Canvas 5: Grade Certificate -->
            <div x-show="activeTab === 'grade'" class="w-full max-w-lg bg-white rounded-2xl border-4 border-double border-emerald-300 p-6 text-center shadow-md space-y-3" style="display: none;">
                <div class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-900 text-xs font-bold uppercase tracking-wider" x-text="grade + ' ACCOMPLISHMENT'"></div>
                <h3 class="text-2xl font-bold text-slate-900">GRADE CERTIFICATE</h3>
                <div class="text-xl font-bold text-emerald-900 border-b-2 border-emerald-100 pb-1 inline-block px-4" x-text="participantName"></div>
                <p class="text-xs text-slate-600 max-w-sm mx-auto">
                    Having achieved the exemplary benchmark of <strong class="text-emerald-700 text-sm" x-text="grade"></strong> in the event <strong x-text="competition"></strong> under category <strong x-text="category"></strong>.
                </p>
                <div class="flex items-center justify-between pt-6 text-[10px] text-slate-400 font-mono">
                    <div>Evaluator</div>
                    <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center mx-auto text-[9px]">GRADE</div>
                    <div>General Secretary</div>
                </div>
            </div>

            <!-- Canvas 6: Result Poster -->
            <div x-show="activeTab === 'poster'" class="w-full max-w-md bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-6 text-center shadow-xl space-y-4" style="display: none;">
                <div class="text-[10px] font-mono tracking-widest text-orange-400 uppercase">OFFICIAL RESULT ANNOUNCEMENT</div>
                <h3 class="text-xl font-black text-white" x-text="competition"></h3>
                <div class="text-xs text-slate-300" x-text="'Category: ' + category"></div>
                <div class="bg-white/10 rounded-xl p-4 space-y-2 text-left text-xs border border-white/10">
                    <div class="flex items-center justify-between pb-1 border-b border-white/10">
                        <span class="text-amber-400 font-bold">1st Place</span>
                        <span class="font-bold text-white" x-text="participantName"></span>
                    </div>
                    <div class="flex items-center justify-between pb-1 border-b border-white/10 text-slate-300">
                        <span>Team / Unit</span>
                        <span x-text="unit"></span>
                    </div>
                    <div class="flex items-center justify-between text-slate-300">
                        <span>Awarded Grade</span>
                        <span class="text-emerald-400 font-bold" x-text="grade"></span>
                    </div>
                </div>
                <div class="text-[10px] text-slate-400 font-mono">QUAF FESTIVAL 2026 • OFFICIAL VERDICT</div>
            </div>
        </div>
    </div>
</div>
@endsection
