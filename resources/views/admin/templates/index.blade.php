@extends('layouts.admin', ['title' => 'Official Badge Templates & A3 Print Packs | Quaf'])

@section('content')
<style>
    @font-face {
        font-family: 'Rockwell Std';
        src: local('Rockwell Std Bold'), local('Rockwell Bold'), local('Rockwell-Bold'),
             url('{{ asset('fonts/Rockwell-Bold.otf') }}') format('opentype'),
             url('{{ asset('fonts/Rockwell-Bold.woff2') }}') format('woff2');
        font-weight: 700;
        font-style: normal;
        font-display: swap;
    }
    @font-face {
        font-family: 'Rockwell Std';
        src: local('Rockwell Std Extra Bold'), local('Rockwell Extra Bold'), local('Rockwell-ExtraBold'),
             url('{{ asset('fonts/Rockwell-Extra-Bold.otf') }}') format('opentype'),
             url('{{ asset('fonts/Rockwell-Extra-Bold.woff2') }}') format('woff2');
        font-weight: 800;
        font-style: normal;
        font-display: swap;
    }
    @font-face {
        font-family: 'Rockwell Std';
        src: local('Rockwell Std'), local('Rockwell'), local('Rockwell-Regular'),
             url('{{ asset('fonts/Rockwell-Regular.otf') }}') format('opentype'),
             url('{{ asset('fonts/Rockwell-Regular.woff2') }}') format('woff2');
        font-weight: normal;
        font-style: normal;
        font-display: swap;
    }
    .font-rockwell {
        font-family: 'Rockwell Std', 'Rockwell', 'Rockwell Nova', serif !important;
    }
</style>

<div class="space-y-8" x-data="{
    teams: [
        {
            name: 'Lumo Fikric',
            code: 'LUMO',
            color: '#56286B',
            bgClass: 'bg-[#56286B]',
            borderClass: 'border-[#56286B]',
            svg: 'lumo-badge.svg',
            pdf: 'QUAF_Badges_Lumo_Fikric_A3.pdf',
            count: 236,
            defaultChest: 'QF1001',
            defaultName: 'JAMALUDHEEN ABDUL HAMEED',
            defaultClass: 'TQS',
            isYellow: false
        },
        {
            name: 'Pacto Hikmic',
            code: 'PACTO',
            color: '#2E3192',
            bgClass: 'bg-[#2E3192]',
            borderClass: 'border-[#2E3192]',
            svg: 'pacto-badge.svg',
            pdf: 'QUAF_Badges_Pacto_Hikmic_A3.pdf',
            count: 229,
            defaultChest: 'QF2001',
            defaultName: 'ASHIQ ULLAL',
            defaultClass: 'TQS',
            isYellow: false
        },
        {
            name: 'Conco Majdic',
            code: 'CONCO',
            color: '#F8E709',
            bgClass: 'bg-[#F8E709]',
            borderClass: 'border-[#e0ce05]',
            svg: 'conco-badge.svg',
            pdf: 'QUAF_Badges_Conco_Majdic_A3.pdf',
            count: 236,
            defaultChest: 'QF3001',
            defaultName: 'IMRAN MAVINAKATT',
            defaultClass: 'TQS',
            isYellow: true
        },
        {
            name: 'Unio Hilmic',
            code: 'UNIO',
            color: '#7F1518',
            bgClass: 'bg-[#7F1518]',
            borderClass: 'border-[#7F1518]',
            svg: 'unio-badge.svg',
            pdf: 'QUAF_Badges_Unio_Hilmic_A3.pdf',
            count: 232,
            defaultChest: 'QF4001',
            defaultName: 'SYD JAZOOL BAHASAN',
            defaultClass: 'TQS',
            isYellow: false
        },
        {
            name: 'Yugo Rushdic',
            code: 'YUGO',
            color: '#AD1E56',
            bgClass: 'bg-[#AD1E56]',
            borderClass: 'border-[#AD1E56]',
            svg: 'yugo-badge.svg',
            pdf: 'QUAF_Badges_Yugo_Rushdic_A3.pdf',
            count: 235,
            defaultChest: 'QF5001',
            defaultName: 'YASEEN MANGAD',
            defaultClass: 'TQS',
            isYellow: false
        }
    ],
    selectedTeamIdx: 0,
    participantName: 'JAMALUDHEEN ABDUL HAMEED',
    chestNo: 'QF1001',
    category: 'TQS',
    zone: 'A Zone',
    get activeTeam() {
        return this.teams[this.selectedTeamIdx];
    },
    selectTeam(idx) {
        this.selectedTeamIdx = idx;
        const t = this.teams[idx];
        this.participantName = t.defaultName;
        this.chestNo = t.defaultChest;
        this.category = t.defaultClass;
    },
    get qrUrl() {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&margin=0&data=' + encodeURIComponent('https://quaf.ihyaussunna.in/verify/student/' + this.chestNo.trim());
    },
    pos: {
        qrY: 45.6,
        qrX: 0,
        qrSize: 126,
        qfY: 72.4,
        qfX: 0,
        qfSize: 15,
        nameY: 80.4,
        nameX: 0,
        nameSize: 13,
        groupY: 86.4,
        groupX: 0,
        groupSize: 10,
        zoneY: 92.0,
        zoneX: 0,
        zoneSize: 10
    },
    resetPos() {
        this.pos = {
            qrY: 45.6,
            qrX: 0,
            qrSize: 126,
            qfY: 72.4,
            qfX: 0,
            qfSize: 15,
            nameY: 80.4,
            nameX: 0,
            nameSize: 13,
            groupY: 86.4,
            groupX: 0,
            groupSize: 10,
            zoneY: 92.0,
            zoneX: 0,
            zoneSize: 10
        };
    }
}">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono text-[11px] font-bold">
                    Official Production System
                </span>
                <span class="text-xs text-slate-400 font-mono">300 DPI Print Matrix</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 font-sora tracking-tight mt-1">
                Participant Badge Templates
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-sora">
                Official 60mm × 100mm templates for all 5 teams with Rockwell Std typography & dynamic student QR verification.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ asset('downloads/badges/QUAF_Badges_ALL_TEAMS_A3.pdf') }}" target="_blank" download class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition-all flex items-center gap-2 shadow-xs cursor-pointer">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Master A3 PDF (1,168 Badges)</span>
            </a>
            <a href="{{ route('admin.idcards.print') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-[#be1e2d] text-white font-semibold text-xs hover:bg-[#a01624] transition-all flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Batch Print Badges</span>
            </a>
            <a href="{{ route('admin.idcards.chest-slips') }}" target="_blank" class="px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-all flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Chest Slips</span>
            </a>
        </div>
    </div>

    <!-- Team Selection Switcher -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs">
        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-3 px-1">
            Select Official Festival Team Template
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <template x-for="(team, idx) in teams" :key="team.code">
                <button type="button"
                        @click="selectTeam(idx)"
                        :class="selectedTeamIdx === idx ? 'ring-2 ring-offset-2 ring-slate-900 border-transparent shadow-md' : 'opacity-70 hover:opacity-100 border-slate-200'"
                        class="p-3.5 rounded-xl border flex flex-col justify-between text-left transition-all cursor-pointer relative overflow-hidden"
                        :style="'background: ' + (selectedTeamIdx === idx ? team.color + '10' : '#ffffff')">
                    <div class="flex items-center justify-between">
                        <span class="w-3.5 h-3.5 rounded-full" :style="'background-color: ' + team.color"></span>
                        <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded"
                              :class="selectedTeamIdx === idx ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600'"
                              x-text="team.count + ' Badges'"></span>
                    </div>
                    <div class="mt-3">
                        <div class="font-bold text-xs text-slate-900 font-sora" x-text="team.name"></div>
                        <div class="text-[10px] font-mono text-slate-500" x-text="'Team Code: ' + team.code"></div>
                    </div>
                    <div x-show="team.isYellow" class="mt-1 text-[9px] font-bold text-amber-700 bg-amber-50 px-1 py-0.5 rounded border border-amber-200 inline-block">
                        Black Text Visibility
                    </div>
                </button>
            </template>
        </div>
    </div>

    <!-- Interactive Customization & Dynamic Preview Canvas -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Left Column: Customization Controls (5 cols on lg) -->
        <div class="lg:col-span-5 bg-white border border-slate-200 rounded-2xl p-6 shadow-2xs space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white" :style="'background-color: ' + activeTeam.color">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm font-sora">Live Badge Customizer</h3>
                        <p class="text-[11px] text-slate-500 font-mono" x-text="'Active Template: ' + activeTeam.name"></p>
                    </div>
                </div>
                <button @click="selectTeam(selectedTeamIdx)"
                        class="text-[11px] text-[#be1e2d] hover:underline font-medium">
                    Reset Team Sample
                </button>
            </div>

            <!-- Inputs -->
            <div class="space-y-3.5 text-xs">
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Participant Full Name (Rockwell Std)</label>
                    <input type="text" x-model="participantName"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 font-rockwell font-medium text-sm focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all">
                    <p class="text-[10px] text-slate-400 mt-1">Automatically auto-fits font size if name is long.</p>
                </div>

                <div class="grid grid-cols-3 gap-2.5">
                    <div>
                        <label class="block text-slate-600 font-semibold mb-1">Chest No</label>
                        <input type="text" x-model="chestNo"
                               class="w-full px-2.5 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 font-rockwell font-bold text-xs focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all uppercase">
                    </div>
                    <div>
                        <label class="block text-slate-600 font-semibold mb-1">Zone</label>
                        <input type="text" x-model="zone"
                               class="w-full px-2.5 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 font-rockwell font-bold text-xs focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all uppercase">
                    </div>
                    <div>
                        <label class="block text-slate-600 font-semibold mb-1">Class</label>
                        <input type="text" x-model="category"
                               class="w-full px-2.5 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 font-rockwell text-xs focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all uppercase">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Official Team / Group</label>
                    <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="w-3.5 h-3.5 rounded-full flex-shrink-0" :style="'background-color: ' + activeTeam.color"></span>
                        <span class="font-bold text-slate-900 font-sora text-xs" x-text="activeTeam.name"></span>
                        <span class="text-[10px] text-slate-500 font-mono ml-auto" x-text="activeTeam.count + ' Registered Members'"></span>
                    </div>
                </div>

                <!-- Live QR Target URL preview -->
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Dynamic Verification QR Destination</div>
                    <div class="font-mono text-[11px] text-slate-700 break-all select-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-text="'https://quaf.ihyaussunna.in/verify/student/' + chestNo.trim()"></span>
                    </div>
                </div>

                <!-- Position & Size Fine-Tuning Panel (X, Y, Size) -->
                <div class="pt-3 border-t border-slate-200" x-data="{ showAdjuster: true }">
                    <div class="flex items-center justify-between mb-2">
                        <button type="button" @click="showAdjuster = !showAdjuster" class="flex items-center gap-1.5 text-xs font-bold text-slate-800 hover:text-[#be1e2d] transition-colors">
                            <svg class="w-3.5 h-3.5 transition-transform" :class="showAdjuster ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            <span>Live Alignment & Size Controls (X, Y, Size)</span>
                        </button>
                        <button type="button" @click="resetPos()" class="text-[10px] text-slate-500 hover:text-[#be1e2d] font-semibold underline">
                            Reset Defaults
                        </button>
                    </div>

                    <div x-show="showAdjuster" class="space-y-3 bg-slate-50 p-3 rounded-xl border border-slate-200 text-[11px]">
                        <!-- 1. QR Code Controls -->
                        <div>
                            <div class="flex justify-between items-center font-semibold text-slate-700 mb-1">
                                <span class="font-bold text-slate-900">1. QR Code</span>
                                <span class="font-mono text-[10px] text-slate-500" x-text="`Y: ${pos.qrY}% | X: ${pos.qrX}px | Size: ${pos.qrSize}px`"></span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <span class="text-[10px] text-slate-500">Y Position</span>
                                    <input type="range" min="35" max="60" step="0.2" x-model.number="pos.qrY" class="w-full h-1.5 bg-slate-200 rounded accent-[#be1e2d] cursor-pointer">
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-500">X Shift</span>
                                    <input type="range" min="-30" max="30" step="1" x-model.number="pos.qrX" class="w-full h-1.5 bg-slate-200 rounded accent-[#be1e2d] cursor-pointer">
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-500">Size</span>
                                    <input type="range" min="80" max="150" step="1" x-model.number="pos.qrSize" class="w-full h-1.5 bg-slate-200 rounded accent-[#be1e2d] cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <!-- 2. QF Red Button Controls -->
                        <div class="pt-2 border-t border-slate-200">
                            <div class="flex justify-between items-center font-semibold text-slate-700 mb-1">
                                <span class="font-bold text-slate-900">2. QF Red Button</span>
                                <span class="font-mono text-[10px] text-slate-500" x-text="`Y: ${pos.qfY}% | X: ${pos.qfX}px | Font: ${pos.qfSize}px`"></span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <span class="text-[10px] text-slate-500">Y Position</span>
                                    <input type="range" min="65" max="80" step="0.2" x-model.number="pos.qfY" class="w-full h-1.5 bg-slate-200 rounded accent-[#be1e2d] cursor-pointer">
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-500">X Shift</span>
                                    <input type="range" min="-30" max="30" step="1" x-model.number="pos.qfX" class="w-full h-1.5 bg-slate-200 rounded accent-[#be1e2d] cursor-pointer">
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-500">Font Size</span>
                                    <input type="range" min="11" max="22" step="0.5" x-model.number="pos.qfSize" class="w-full h-1.5 bg-slate-200 rounded accent-[#be1e2d] cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <!-- 3. Student Name Controls -->
                        <div class="pt-2 border-t border-slate-200">
                            <div class="flex justify-between items-center font-semibold text-slate-700 mb-1">
                                <span class="font-bold text-slate-900">3. Student Name</span>
                                <span class="font-mono text-[10px] text-slate-500" x-text="`Y: ${pos.nameY}% | X: ${pos.nameX}px | Font: ${pos.nameSize}px`"></span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <span class="text-[10px] text-slate-500">Y Position</span>
                                    <input type="range" min="74" max="88" step="0.2" x-model.number="pos.nameY" class="w-full h-1.5 bg-slate-200 rounded accent-[#be1e2d] cursor-pointer">
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-500">X Shift</span>
                                    <input type="range" min="-30" max="30" step="1" x-model.number="pos.nameX" class="w-full h-1.5 bg-slate-200 rounded accent-[#be1e2d] cursor-pointer">
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-500">Font Size</span>
                                    <input type="range" min="9" max="18" step="0.5" x-model.number="pos.nameSize" class="w-full h-1.5 bg-slate-200 rounded accent-[#be1e2d] cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <!-- 4. Group Pill Controls -->
                        <div class="pt-2 border-t border-slate-200">
                            <div class="flex justify-between items-center font-semibold text-slate-700 mb-1">
                                <span class="font-bold text-slate-900">4. Group Pill</span>
                                <span class="font-mono text-[10px] text-slate-500" x-text="`Y: ${pos.groupY}% | X: ${pos.groupX}px | Font: ${pos.groupSize}px`"></span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <span class="text-[10px] text-slate-500">Y Position</span>
                                    <input type="range" min="80" max="92" step="0.2" x-model.number="pos.groupY" class="w-full h-1.5 bg-slate-200 rounded accent-[#be1e2d] cursor-pointer">
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-500">X Shift</span>
                                    <input type="range" min="-30" max="30" step="1" x-model.number="pos.groupX" class="w-full h-1.5 bg-slate-200 rounded accent-[#be1e2d] cursor-pointer">
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-500">Font Size</span>
                                    <input type="range" min="8" max="14" step="0.5" x-model.number="pos.groupSize" class="w-full h-1.5 bg-slate-200 rounded accent-[#be1e2d] cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <!-- 5. Zone & Class Controls -->
                        <div class="pt-2 border-t border-slate-200">
                            <div class="flex justify-between items-center font-semibold text-slate-700 mb-1">
                                <span class="font-bold text-slate-900">5. Zone & Class</span>
                                <span class="font-mono text-[10px] text-slate-500" x-text="`Y: ${pos.zoneY}% | X: ${pos.zoneX}px | Font: ${pos.zoneSize}px`"></span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <span class="text-[10px] text-slate-500">Y Position</span>
                                    <input type="range" min="85" max="98" step="0.2" x-model.number="pos.zoneY" class="w-full h-1.5 bg-slate-200 rounded accent-[#be1e2d] cursor-pointer">
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-500">X Shift</span>
                                    <input type="range" min="-30" max="30" step="1" x-model.number="pos.zoneX" class="w-full h-1.5 bg-slate-200 rounded accent-[#be1e2d] cursor-pointer">
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-500">Font Size</span>
                                    <input type="range" min="8" max="14" step="0.5" x-model.number="pos.zoneSize" class="w-full h-1.5 bg-slate-200 rounded accent-[#be1e2d] cursor-pointer">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions & Team A3 Download -->
            <div class="pt-4 border-t border-slate-100 space-y-2.5">
                <a :href="'{{ asset('downloads/badges') }}/' + activeTeam.pdf" download
                   class="w-full py-2.5 px-4 rounded-xl text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-sm transition-all cursor-pointer"
                   :style="'background-color: ' + activeTeam.color">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span x-text="'Download ' + activeTeam.name + ' A3 PDF (15 Sheets)'"></span>
                </a>
                <button onclick="window.print()" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Print This Preview Badge</span>
                </button>
            </div>
        </div>

        <!-- Right Column: Dynamic Authentic Preview Canvas (7 cols on lg) -->
        <div class="lg:col-span-7 bg-slate-100 border border-slate-200 rounded-2xl p-6 shadow-2xs flex flex-col items-center justify-center min-h-[580px]">
            <div class="w-full flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Live Authentic Badge Preview</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-mono text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200">
                        Exact 6.0 cm × 10.0 cm
                    </span>
                    <span class="text-[11px] font-mono font-bold text-white px-2 py-0.5 rounded shadow-2xs"
                          :style="'background-color: ' + activeTeam.color"
                          x-text="activeTeam.code"></span>
                </div>
            </div>

            <!-- Real Badge Vector Mockup (Ratio 170.08 / 283.46 -> Width 300px, Height 500px) -->
            <div class="relative w-[300px] h-[500px] rounded-xl overflow-hidden shadow-2xl border border-slate-300 transition-all select-none">
                <!-- SVG Vector Template Background -->
                <img :src="'{{ asset('images/badges') }}/' + activeTeam.svg"
                     :alt="activeTeam.name"
                     class="absolute inset-0 w-full h-full object-fill pointer-events-none z-0">

                <!-- 1. White Square QR Box (Position & Size driven by reactive inline style) -->
                <div :style="`position: absolute; top: ${pos.qrY}%; left: 50%; transform: translateX(calc(-50% + ${pos.qrX}px)); width: ${pos.qrSize}px; height: ${pos.qrSize}px; background: #ffffff; border-radius: 8px; box-shadow: none; display: flex; align-items: center; justify-content: center; z-index: 10; pointer-events: none;`">
                    <img :src="qrUrl"
                         alt="QR Code"
                         :style="`width: ${Math.max(pos.qrSize - 14, 40)}px; height: ${Math.max(pos.qrSize - 14, 40)}px; object-fit: contain; display: block;`">
                </div>

                <!-- 2. QF Number in RED BUTTON (Position & Size driven by reactive inline style - Clean no white stroke, no black shadow) -->
                <div :style="`position: absolute; top: ${pos.qfY}%; left: 50%; transform: translateX(calc(-50% + ${pos.qfX}px)); z-index: 10; width: max-content; pointer-events: none;`">
                    <div class="font-rockwell"
                         :style="`background: #be1e2d; color: #ffffff; font-weight: 800; font-size: ${pos.qfSize}px; letter-spacing: 0.8px; text-transform: uppercase; padding: 5px 20px; border-radius: 9999px; box-shadow: none; border: none; line-height: 1; display: inline-flex; align-items: center; justify-content: center; font-family: 'Rockwell Std', 'Rockwell', serif;`"
                         x-text="chestNo">
                    </div>
                </div>

                <!-- 3. Student Full Name (Rockwell Std typography, compact line height) -->
                <div :style="`position: absolute; top: ${pos.nameY}%; left: 10px; right: 10px; transform: translateX(${pos.nameX}px); text-align: center; z-index: 10; pointer-events: none;`">
                    <div class="font-rockwell"
                         :style="`color: ${activeTeam.isYellow ? '#0f172a' : '#ffffff'}; font-size: ${pos.nameSize}px; font-weight: 700; line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding: 0 2px; font-family: 'Rockwell Std', 'Rockwell', serif; text-shadow: none;`"
                         x-text="participantName">
                    </div>
                </div>

                <!-- 4. Official Team / Group Pill Button (Clean no stroke, no shadow) -->
                <div :style="`position: absolute; top: ${pos.groupY}%; left: 50%; transform: translateX(calc(-50% + ${pos.groupX}px)); z-index: 10; width: max-content; pointer-events: none;`">
                    <div class="font-rockwell"
                         :style="`background: ${activeTeam.isYellow ? 'rgba(0,0,0,0.12)' : 'rgba(255,255,255,0.22)'}; border: none; box-shadow: none; color: ${activeTeam.isYellow ? '#0f172a' : '#ffffff'}; font-size: ${pos.groupSize}px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; padding: 3px 14px; border-radius: 9999px; line-height: 1; white-space: nowrap; display: inline-flex; align-items: center; justify-content: center; font-family: 'Rockwell Std', 'Rockwell', serif;`"
                         x-text="activeTeam.name">
                    </div>
                </div>

                <!-- 5. Zone & Class / Category (Rockwell Std typography) -->
                <div :style="`position: absolute; top: ${pos.zoneY}%; left: 10px; right: 10px; transform: translateX(${pos.zoneX}px); text-align: center; z-index: 10; pointer-events: none;`">
                    <div class="font-rockwell"
                         :style="`color: ${activeTeam.isYellow ? '#1e293b' : 'rgba(255,255,255,0.92)'}; font-size: ${pos.zoneSize}px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; line-height: 1; font-family: 'Rockwell Std', 'Rockwell', serif; text-shadow: none;`"
                         x-text="(zone ? zone.toUpperCase() + ' • ' : '') + category.toUpperCase()">
                    </div>
                </div>
            </div>

            <!-- Notice badge -->
            <div class="mt-4 text-[11px] font-mono text-slate-500 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>300 DPI Vector-Aligned Master • 1:1 Scale with Physical Print Files</span>
            </div>
        </div>
    </div>

    <!-- Official A3 Print Production Downloads Section -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
                <h3 class="font-bold text-slate-900 text-base font-sora">Official A3 Print Sheets & Bulk Downloads</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pre-imposed 4×4 grid (16 badges per sheet) with standard 5mm crop marks and border cutting guides.
                </p>
            </div>
            <a href="{{ asset('downloads/badges/QUAF_Badges_ALL_TEAMS_A3.pdf') }}" download
               class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition-all flex items-center gap-1.5 shadow-xs w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Download All 5 Teams (75 Sheets)</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
            <!-- 1. Lumo Fikric -->
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-[#56286B]"></span>
                        <h4 class="font-bold text-xs text-slate-900 font-sora">Lumo Fikric</h4>
                    </div>
                    <span class="text-[10px] font-mono font-bold text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200">236 Badges</span>
                </div>
                <div class="text-[11px] text-slate-500 space-y-1 font-mono">
                    <div>Sheets: 15 Pages (16 per sheet)</div>
                    <div>Chest Series: QF1001 – QF1236</div>
                </div>
                <a href="{{ asset('downloads/badges/QUAF_Badges_Lumo_Fikric_A3.pdf') }}" download
                   class="w-full py-2 px-3 rounded-lg bg-white border border-slate-200 hover:bg-[#56286B] hover:text-white hover:border-transparent text-slate-700 font-semibold text-xs flex items-center justify-center gap-1.5 transition-all shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Download Lumo A3 PDF (48.5 MB)</span>
                </a>
            </div>

            <!-- 2. Pacto Hikmic -->
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-[#2E3192]"></span>
                        <h4 class="font-bold text-xs text-slate-900 font-sora">Pacto Hikmic</h4>
                    </div>
                    <span class="text-[10px] font-mono font-bold text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200">229 Badges</span>
                </div>
                <div class="text-[11px] text-slate-500 space-y-1 font-mono">
                    <div>Sheets: 15 Pages (16 per sheet)</div>
                    <div>Chest Series: QF2001 – QF2229</div>
                </div>
                <a href="{{ asset('downloads/badges/QUAF_Badges_Pacto_Hikmic_A3.pdf') }}" download
                   class="w-full py-2 px-3 rounded-lg bg-white border border-slate-200 hover:bg-[#2E3192] hover:text-white hover:border-transparent text-slate-700 font-semibold text-xs flex items-center justify-center gap-1.5 transition-all shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Download Pacto A3 PDF (47.3 MB)</span>
                </a>
            </div>

            <!-- 3. Conco Majdic -->
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-[#F8E709] border border-amber-400"></span>
                        <h4 class="font-bold text-xs text-slate-900 font-sora">Conco Majdic</h4>
                    </div>
                    <span class="text-[10px] font-mono font-bold text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200">236 Badges</span>
                </div>
                <div class="text-[11px] text-slate-500 space-y-1 font-mono">
                    <div>Sheets: 15 Pages (Black Text on Yellow)</div>
                    <div>Chest Series: QF3001 – QF3236</div>
                </div>
                <a href="{{ asset('downloads/badges/QUAF_Badges_Conco_Majdic_A3.pdf') }}" download
                   class="w-full py-2 px-3 rounded-lg bg-white border border-slate-200 hover:bg-[#F8E709] hover:text-black hover:border-amber-400 text-slate-700 font-semibold text-xs flex items-center justify-center gap-1.5 transition-all shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Download Conco A3 PDF (48.9 MB)</span>
                </a>
            </div>

            <!-- 4. Unio Hilmic -->
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-[#7F1518]"></span>
                        <h4 class="font-bold text-xs text-slate-900 font-sora">Unio Hilmic</h4>
                    </div>
                    <span class="text-[10px] font-mono font-bold text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200">232 Badges</span>
                </div>
                <div class="text-[11px] text-slate-500 space-y-1 font-mono">
                    <div>Sheets: 15 Pages (16 per sheet)</div>
                    <div>Chest Series: QF4001 – QF4232</div>
                </div>
                <a href="{{ asset('downloads/badges/QUAF_Badges_Unio_Hilmic_A3.pdf') }}" download
                   class="w-full py-2 px-3 rounded-lg bg-white border border-slate-200 hover:bg-[#7F1518] hover:text-white hover:border-transparent text-slate-700 font-semibold text-xs flex items-center justify-center gap-1.5 transition-all shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Download Unio A3 PDF (48.3 MB)</span>
                </a>
            </div>

            <!-- 5. Yugo Rushdic -->
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-[#AD1E56]"></span>
                        <h4 class="font-bold text-xs text-slate-900 font-sora">Yugo Rushdic</h4>
                    </div>
                    <span class="text-[10px] font-mono font-bold text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200">235 Badges</span>
                </div>
                <div class="text-[11px] text-slate-500 space-y-1 font-mono">
                    <div>Sheets: 15 Pages (16 per sheet)</div>
                    <div>Chest Series: QF5001 – QF5235</div>
                </div>
                <a href="{{ asset('downloads/badges/QUAF_Badges_Yugo_Rushdic_A3.pdf') }}" download
                   class="w-full py-2 px-3 rounded-lg bg-white border border-slate-200 hover:bg-[#AD1E56] hover:text-white hover:border-transparent text-slate-700 font-semibold text-xs flex items-center justify-center gap-1.5 transition-all shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Download Yugo A3 PDF (48.2 MB)</span>
                </a>
            </div>

            <!-- 6. Master All Teams -->
            <div class="p-4 rounded-xl border-2 border-emerald-500/40 bg-emerald-50/30 flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-emerald-600"></span>
                        <h4 class="font-bold text-xs text-slate-900 font-sora">All 5 Teams (Master)</h4>
                    </div>
                    <span class="text-[10px] font-mono font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded border border-emerald-300">1,168 Badges</span>
                </div>
                <div class="text-[11px] text-slate-500 space-y-1 font-mono">
                    <div>Sheets: 75 Pages Total (Complete)</div>
                    <div>Format: Standard A3 Print Sheet</div>
                </div>
                <a href="{{ asset('downloads/badges/QUAF_Badges_ALL_TEAMS_A3.pdf') }}" download
                   class="w-full py-2 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs flex items-center justify-center gap-1.5 transition-all shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Download Master A3 PDF (241 MB)</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
