@extends('layouts.fund', ['title' => 'Fund Collection Ledger'])

@section('content')
<div class="space-y-8">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-600 text-white">
                    Fund Desk
                </span>
                <span class="text-xs text-slate-500 font-mono">Central Accounting Ledger</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-2 font-sora">
                Fund Collection & Receipts Ledger
            </h1>
            <p class="text-sm text-slate-600 mt-1 max-w-2xl font-anek">
                ഫെസ്റ്റിവൽ ഫണ്ട് സംഭാവനകളുടെ ആകെ വിവരങ്ങൾ, ഗ്രൂപ്പ് അടിസ്ഥാനത്തിലുള്ള കളക്ഷൻ സ്റ്റാറ്റസ്, രസീത് ഹിസ്റ്ററി എന്നിവ പരിശോധിക്കുക.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('fund.dashboard') }}" 
               class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Issue New Receipt</span>
            </a>
            <a href="{{ route('home') }}" target="_blank"
               class="px-4 py-2.5 rounded-xl border border-slate-300 hover:border-slate-400 bg-white text-slate-700 font-bold text-xs transition-colors flex items-center gap-1.5">
                <span>Festival Site ↗</span>
            </a>
        </div>
    </div>

    <!-- Metric Badges Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Grand Total Amount -->
        <div class="bg-white p-5 rounded-2xl border border-emerald-200 bg-emerald-50/20 shadow-2xs">
            <div class="text-xs font-semibold text-emerald-800 uppercase tracking-wider font-mono">Total Collected</div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-700 font-mono mt-1">₹{{ number_format($totalAmount, 2) }}</div>
            <div class="text-xs text-emerald-600 mt-1 font-mono font-medium">{{ number_format($totalCount) }} active receipts</div>
        </div>

        <!-- Today's Collection -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider font-mono">Today's Collection</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 font-mono mt-1">₹{{ number_format($todayAmount, 2) }}</div>
            <div class="text-xs text-slate-400 mt-1 font-mono">Recorded today</div>
        </div>

        <!-- Active Receipts Count -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider font-mono">Total Receipts</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 font-mono mt-1">{{ number_format($totalCount) }}</div>
            <div class="text-xs text-slate-400 mt-1">Issued across all desks</div>
        </div>

        <!-- Cancelled / Deleted Receipts -->
        <div class="bg-white p-5 rounded-2xl border border-rose-200 bg-rose-50/20 shadow-2xs">
            <div class="text-xs font-semibold text-rose-700 uppercase tracking-wider font-mono">Cancelled / Void</div>
            <div class="text-2xl sm:text-3xl font-black text-rose-600 font-mono mt-1">{{ number_format($cancelledCount) }}</div>
            <div class="text-xs text-rose-500 mt-1 font-mono">₹{{ number_format($cancelledAmount, 2) }} voided</div>
        </div>
    </div>

    <!-- Top Collected Groups Standings -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                        Group Standings
                    </span>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight font-sora">
                        Top Collected Groups
                    </h2>
                </div>
                <p class="text-xs text-slate-500 font-anek mt-0.5">
                    ഗ്രൂപ്പുകളുടെ അടിസ്ഥാനത്തിലുള്ള ആകെ ഫണ്ട് കളക്ഷൻ റാങ്കിംഗ് ലിസ്റ്റ്.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('fund.standings.poster') }}" target="_blank"
                   class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <span>Preview Poster</span>
                </a>
                <a href="{{ route('fund.standings.download') }}"
                   class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black text-xs transition-all shadow-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Download Standing Poster</span>
                </a>
                <div class="text-xs font-mono text-slate-500 bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-200">
                    {{ $topGroups->count() }} Groups
                </div>
            </div>
        </div>

        <div class="space-y-3">
            @forelse($topGroups as $index => $grp)
                @php
                    $rank = $index + 1;
                    $collected = (float) ($grp->total_collected ?? 0);
                    $count = (int) ($grp->receipts_count ?? 0);

                    $isGold = ($rank === 1);
                    $isSilver = ($rank === 2);
                    $isBronze = ($rank === 3);

                    $cardBg = match($rank) {
                        1 => 'bg-gradient-to-r from-amber-500/10 via-amber-400/5 to-white border-2 border-amber-400/90 shadow-md shadow-amber-400/10',
                        2 => 'bg-gradient-to-r from-slate-200/30 via-slate-100/15 to-white border-2 border-slate-300 shadow-xs',
                        3 => 'bg-gradient-to-r from-amber-700/10 via-amber-600/5 to-white border-2 border-amber-700/35 shadow-2xs',
                        default => 'bg-slate-50 border border-slate-200/80 shadow-2xs'
                    };

                    $rankBadge = match($rank) {
                        1 => 'bg-gradient-to-br from-amber-400 via-amber-500 to-amber-600 text-slate-950 font-black shadow-md shadow-amber-500/30',
                        2 => 'bg-gradient-to-br from-slate-200 via-slate-300 to-slate-400 text-slate-900 font-black shadow-xs',
                        3 => 'bg-gradient-to-br from-amber-700 via-amber-600 to-amber-800 text-white font-black shadow-xs',
                        default => 'bg-slate-200/80 text-slate-700 border border-slate-300 font-bold'
                    };

                    $medalTag = match($rank) {
                        1 => 'GOLD',
                        2 => 'SILVER',
                        3 => 'BRONZE',
                        default => null
                    };
                @endphp

                <div class="p-4 sm:p-5 rounded-2xl transition-all hover:shadow-md flex items-center justify-between gap-3 sm:gap-4 relative overflow-hidden {{ $cardBg }}">
                    <!-- Left Vertical Color Accent Bar -->
                    <div class="absolute top-0 bottom-0 left-0 w-2" style="background-color: {{ $grp->color_hex ?? '#be1e2d' }};"></div>

                    <!-- Left: Rank & Group Info -->
                    <div class="flex items-center gap-3 sm:gap-4 min-w-0 pl-1.5">
                        
                        <!-- Rank Badge -->
                        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl flex flex-col items-center justify-center shrink-0 font-mono {{ $rankBadge }}">
                            <span class="text-xs sm:text-sm leading-none">
                                @if($isGold) 1st @elseif($isSilver) 2nd @elseif($isBronze) 3rd @else #{{ $rank }} @endif
                            </span>
                            @if($medalTag)
                                <span class="text-[8px] font-black uppercase tracking-tighter opacity-90 leading-none mt-0.5">{{ $medalTag }}</span>
                            @endif
                        </div>

                        <!-- Group Details -->
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] sm:text-xs font-mono font-black"
                                      style="background-color: {{ $grp->color_hex ?? '#be1e2d' }}20; color: {{ $grp->color_hex ?? '#be1e2d' }};">
                                    {{ $grp->code }}
                                </span>
                                <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight break-words">
                                    {{ $grp->name }}
                                </h3>
                            </div>
                            <div class="text-[10px] sm:text-xs font-mono text-slate-500 mt-0.5">
                                <span><strong class="text-slate-700">{{ $count }}</strong> {{ \Illuminate\Support\Str::plural('receipt', $count) }}</span>
                            </div>
                        </div>

                    </div>

                    <!-- Right: Total Collected Metric -->
                    <div class="text-right shrink-0 font-mono">
                        <div class="text-base sm:text-2xl font-black {{ $isGold ? 'text-amber-800' : ($isSilver ? 'text-slate-900' : ($isBronze ? 'text-amber-900' : 'text-slate-900')) }}">
                            ₹{{ number_format($collected, 0) }}
                        </div>
                        <div class="mt-1 flex items-center justify-end">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] sm:text-xs font-bold tracking-wider {{ $isGold ? 'bg-amber-200/80 text-amber-900 border border-amber-300' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                {{ \App\Services\StandingPosterGenerator::calculateFinancePoints($collected) }} PTS
                            </span>
                        </div>
                    </div>

                </div>
            @empty
                <div class="py-8 text-center text-slate-400 text-xs font-mono">
                    No group standings available.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Manual Team Amount & Point Standing Poster Generator Section -->
    <div x-data="manualStandingsManager()" class="bg-white p-6 rounded-2xl border border-indigo-200/90 shadow-2xs space-y-6">
        <!-- Section Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-indigo-100 text-indigo-800">
                        Manual Point Standings
                    </span>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight font-sora">
                        Team Point Standing Poster Generator
                    </h2>
                </div>
                <p class="text-xs text-slate-500 font-anek mt-1">
                    ടീമുകൾക്ക് മാനുവലായി തുക നൽകി പോയിന്റും സ്റ്റാൻഡിങ്ങും കണക്കാക്കുക. നൽകിയ തുകയ്ക്കനുസരിച്ചുള്ള പോയിന്റും റാങ്കും മാത്രം ഉൾപ്പെടുത്തി പോസ്റ്റർ തയ്യാറാക്കാം (പോസ്റ്ററിൽ തുക പ്രദർശിപ്പിക്കില്ല).
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="openPreview()"
                   class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors flex items-center gap-1.5 shadow-2xs cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <span>Preview Poster</span>
                </button>
                <button type="button" @click="downloadPoster()"
                   class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black text-xs transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Download Standing Poster</span>
                </button>
                <button type="button" @click="saveAmounts()"
                   :disabled="saving"
                   class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold text-xs transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    <span x-text="saving ? 'Saving...' : 'Save Amounts'">Save Amounts</span>
                </button>
            </div>
        </div>

        <!-- Success notification toast -->
        <div x-show="toastMessage" x-cloak class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center justify-between transition">
            <span x-text="toastMessage"></span>
            <button type="button" @click="toastMessage = ''" class="text-emerald-600 hover:text-emerald-950">&times;</button>
        </div>

        <!-- Manual Input Toolbar -->
        <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/80 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <span class="text-xs font-bold text-slate-700 font-mono uppercase tracking-wider">
                    Enter Manual Amount For Each Team (തുക നൽകുക):
                </span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="copyRealAmounts()"
                            class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-white px-2.5 py-1 rounded-lg border border-slate-200 hover:border-indigo-300 transition shadow-2xs">
                        യഥാർത്ഥ ലെഡ്ജർ തുകകൾ നൽകുക
                    </button>
                    <button type="button" @click="resetAmounts()"
                            class="text-xs font-semibold text-slate-500 hover:text-slate-800 bg-white px-2.5 py-1 rounded-lg border border-slate-200 hover:border-slate-300 transition shadow-2xs">
                        ക്ലിയർ (0)
                    </button>
                </div>
            </div>

            <!-- 5 Team Amount Input Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-1">
                <template x-for="grp in groups" :key="grp.id">
                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-2xs space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold"
                                  :style="{ backgroundColor: grp.color_hex + '20', color: grp.color_hex }"
                                  x-text="grp.code"></span>
                            <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-200"
                                  x-text="grp.points + ' PTS'"></span>
                        </div>
                        <div class="text-xs font-bold text-slate-900 truncate" x-text="grp.name"></div>
                        <div class="relative">
                            <span class="absolute left-2.5 top-1.5 text-xs text-slate-400 font-bold">₹</span>
                            <input type="number" step="any" min="0" x-model.number="grp.amount" @input="recalc()"
                                   placeholder="0"
                                   class="w-full pl-6 pr-2 py-1.5 text-xs rounded-lg border border-slate-300 font-mono font-bold text-slate-900 focus:outline-none focus:border-indigo-600">
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Live Team Point Standing Cards (NO AMOUNT DISPLAYED - BEAUTIFUL DESIGN) -->
        <div class="space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-500 pb-1">
                <span class="font-bold uppercase tracking-wider font-mono">
                    Official Team Point Standings (Preview)
                </span>
                <span class="text-[11px] font-anek text-slate-400">
                    നൽകിയ തുകയ്ക്കനുസരിച്ചുള്ള പോയിന്റും ഔദ്യോഗിക റാങ്കിംഗും:
                </span>
            </div>

            <!-- Dynamic Alpine.js Sorted Cards -->
            <template x-for="(grp, idx) in sortedGroups" :key="grp.id">
                <div :class="cardBg(idx + 1)"
                     class="p-4 sm:p-5 rounded-2xl transition-all hover:shadow-md flex items-center justify-between gap-3 sm:gap-4 relative overflow-hidden">
                    <!-- Left Vertical Color Accent Bar -->
                    <div class="absolute top-0 bottom-0 left-0 w-2" :style="{ backgroundColor: grp.color_hex }"></div>

                    <!-- Left: Rank & Group Info -->
                    <div class="flex items-center gap-3 sm:gap-4 min-w-0 pl-1.5">
                        <!-- Rank Badge -->
                        <div :class="rankBadgeClass(idx + 1)"
                             class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl flex flex-col items-center justify-center shrink-0 font-mono">
                            <span class="text-xs sm:text-sm leading-none" x-text="rankLabel(idx + 1)"></span>
                            <span x-show="idx < 3" class="text-[8px] font-black uppercase tracking-tighter opacity-90 leading-none mt-0.5" x-text="medalTag(idx + 1)"></span>
                        </div>

                        <!-- Group Details -->
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] sm:text-xs font-mono font-black"
                                      :style="{ backgroundColor: grp.color_hex + '20', color: grp.color_hex }"
                                      x-text="grp.code"></span>
                                <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight break-words" x-text="grp.name"></h3>
                            </div>
                            <div class="text-[10px] sm:text-xs font-mono text-slate-500 mt-0.5">
                                <span>Finance Support Points</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Large Prominent Point Metric Badge (NO AMOUNT DISPLAYED) -->
                    <div class="text-right shrink-0 font-mono">
                        <span :class="pointsBadgeClass(idx + 1)"
                              class="inline-flex items-center px-4 py-2 rounded-xl text-sm sm:text-base font-black tracking-wider shadow-xs">
                            <span x-text="grp.points + ' PTS'"></span>
                        </span>
                    </div>
                </div>
            </template>

            <!-- Server-Side Fallback for non-JS / search engine / tests -->
            <noscript>
                <div class="space-y-3">
                    @foreach($manualStandings as $index => $grp)
                        @php
                            $rank = $index + 1;
                            $points = (int) ($grp->points ?? 0);
                            $isGold = ($rank === 1);
                            $isSilver = ($rank === 2);
                            $isBronze = ($rank === 3);
                            $cardBg = match($rank) {
                                1 => 'bg-gradient-to-r from-amber-500/10 via-amber-400/5 to-white border-2 border-amber-400/90 shadow-md shadow-amber-400/10',
                                2 => 'bg-gradient-to-r from-slate-200/30 via-slate-100/15 to-white border-2 border-slate-300 shadow-xs',
                                3 => 'bg-gradient-to-r from-amber-700/10 via-amber-600/5 to-white border-2 border-amber-700/35 shadow-2xs',
                                default => 'bg-slate-50 border border-slate-200/80 shadow-2xs'
                            };
                            $rankBadge = match($rank) {
                                1 => 'bg-gradient-to-br from-amber-400 via-amber-500 to-amber-600 text-slate-950 font-black shadow-md shadow-amber-500/30',
                                2 => 'bg-gradient-to-br from-slate-200 via-slate-300 to-slate-400 text-slate-900 font-black shadow-xs',
                                3 => 'bg-gradient-to-br from-amber-700 via-amber-600 to-amber-800 text-white font-black shadow-xs',
                                default => 'bg-slate-200/80 text-slate-700 border border-slate-300 font-bold'
                            };
                            $medalTag = match($rank) {
                                1 => 'GOLD',
                                2 => 'SILVER',
                                3 => 'BRONZE',
                                default => null
                            };
                        @endphp
                        <div class="p-4 sm:p-5 rounded-2xl flex items-center justify-between gap-3 relative overflow-hidden {{ $cardBg }}">
                            <div class="absolute top-0 bottom-0 left-0 w-2" style="background-color: {{ $grp->color_hex ?? '#be1e2d' }};"></div>
                            <div class="flex items-center gap-3 pl-1.5">
                                <div class="w-11 h-11 rounded-xl flex flex-col items-center justify-center shrink-0 font-mono {{ $rankBadge }}">
                                    <span class="text-xs sm:text-sm">@if($isGold) 1st @elseif($isSilver) 2nd @elseif($isBronze) 3rd @else #{{ $rank }} @endif</span>
                                    @if($medalTag)<span class="text-[8px] font-black">{{ $medalTag }}</span>@endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold" style="background-color: {{ $grp->color_hex ?? '#be1e2d' }}20; color: {{ $grp->color_hex ?? '#be1e2d' }};">
                                            {{ $grp->code }}
                                        </span>
                                        <h3 class="text-sm font-black text-slate-900">{{ $grp->name }}</h3>
                                    </div>
                                    <div class="text-[10px] font-mono text-slate-500">Finance Support Points</div>
                                </div>
                            </div>
                            <div class="text-right shrink-0 font-mono">
                                <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-black {{ $isGold ? 'bg-amber-200 text-amber-950 border border-amber-300' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                    {{ $points }} PTS
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </noscript>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
        <form method="GET" action="{{ route('fund.ledger') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <!-- Search Text -->
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold uppercase text-slate-400 font-mono mb-1">Search</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search donor name, phone, receipt #..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-emerald-600">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 font-mono mb-1">Mode</label>
                <select name="payment_method" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-emerald-600">
                    <option value="">All Modes</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="upi" {{ request('payment_method') === 'upi' ? 'selected' : '' }}>UPI / GPay</option>
                    <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 font-mono mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-emerald-600">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Receipts</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled / Void</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 font-mono mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-emerald-600">
            </div>

            <!-- Date To & Submit -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 font-mono mb-1">To Date</label>
                <div class="flex items-center gap-2">
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-emerald-600">
                    <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-colors shrink-0">
                        Filter
                    </button>
                </div>
            </div>
        </form>

        @if(request('search') || request('payment_method') || request('status') || request('date_from') || request('date_to'))
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-mono">Filters active</span>
                <a href="{{ route('fund.ledger') }}" class="text-emerald-700 font-bold hover:underline">
                    Reset All Filters
                </a>
            </div>
        @endif
    </div>

    <!-- Receipts Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4">Receipt #</th>
                        <th class="py-3.5 px-4">Donor Name & Place</th>
                        <th class="py-3.5 px-4">Phone</th>
                        <th class="py-3.5 px-4">Amount</th>
                        <th class="py-3.5 px-4">Payment Mode</th>
                        <th class="py-3.5 px-4">Collector</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($receipts as $rcp)
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $rcp->is_cancelled ? 'bg-rose-50/40 text-rose-900' : '' }}">
                            <!-- Receipt Number -->
                            <td class="py-3 px-4 font-mono font-bold">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-[11px] {{ $rcp->is_cancelled ? 'line-through text-slate-400' : 'text-slate-900' }}">
                                    {{ $rcp->receipt_number }}
                                </span>
                            </td>

                            <!-- Donor Name & Place -->
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $rcp->donor_name }}</div>
                                @if($rcp->donor_place)
                                    <div class="text-[11px] text-slate-500">{{ $rcp->donor_place }}</div>
                                @endif
                                @if($rcp->category)
                                    <span class="inline-block px-1.5 py-0.2 rounded text-[9px] bg-slate-100 text-slate-600 font-mono mt-0.5">
                                        {{ $rcp->category }}
                                    </span>
                                @endif
                            </td>

                            <!-- Phone -->
                            <td class="py-3 px-4 font-mono text-slate-600 text-[11px]">
                                <div>{{ $rcp->donor_phone ?: '—' }}</div>
                                @if($rcp->whatsapp_number)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $rcp->whatsapp_number) }}" target="_blank" class="inline-flex items-center gap-0.5 text-[10px] text-emerald-700 font-bold hover:underline mt-0.5">
                                        WA: {{ $rcp->whatsapp_number }} ↗
                                    </a>
                                @endif
                            </td>

                            <!-- Amount -->
                            <td class="py-3 px-4">
                                <span class="text-sm font-black font-mono {{ $rcp->is_cancelled ? 'text-rose-500 line-through' : 'text-emerald-700' }}">
                                    ₹{{ number_format($rcp->amount, 2) }}
                                </span>
                            </td>

                            <!-- Payment Mode -->
                            <td class="py-3 px-4 font-mono">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ strtoupper($rcp->payment_method) }}
                                </span>
                                @if($rcp->transaction_reference)
                                    <div class="text-[9px] text-slate-600 font-bold mt-0.5 truncate max-w-[130px]" title="{{ $rcp->transaction_reference }}">
                                        Ref: {{ $rcp->transaction_reference }}
                                    </div>
                                @endif
                                @if($rcp->screenshot_path)
                                    <div class="mt-1">
                                        <a href="{{ asset('storage/' . $rcp->screenshot_path) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded border border-sky-200 hover:bg-sky-100 transition-colors">
                                            Proof ↗
                                        </a>
                                    </div>
                                @endif
                            </td>

                            <!-- Collector -->
                            <td class="py-3 px-4 text-slate-700">
                                <div class="text-[11px] font-semibold">{{ $rcp->collector_name ?? 'Fund Desk' }}</div>
                            </td>

                            <!-- Date -->
                            <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                                <div>{{ $rcp->created_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $rcp->created_at->format('h:i A') }}</div>
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4 font-mono">
                                @if($rcp->is_cancelled)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        VOID
                                    </span>
                                    @if($rcp->cancelled_reason)
                                        <div class="text-[9px] text-rose-600 mt-0.5 truncate max-w-[100px]" title="{{ $rcp->cancelled_reason }}">
                                            {{ $rcp->cancelled_reason }}
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        Active
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('fund.receipts.show', $rcp) }}" 
                                       target="_blank"
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors"
                                       title="View Voucher">
                                        Voucher ↗
                                    </a>

                                    <a href="{{ route('fund.receipts.download', $rcp) }}" 
                                       class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-xs transition-colors"
                                       title="Download JPG Voucher">
                                        JPG ↓
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400 font-mono">
                                <p class="text-sm font-semibold text-slate-600">No receipt records found</p>
                                <p class="text-xs text-slate-400 mt-1">Receipts generated from the Fund Desk will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipts->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $receipts->links() }}
            </div>
        @endif
    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('manualStandingsManager', () => ({
        saving: false,
        toastMessage: '',
        groups: [
            @foreach($allGroups as $g)
                @php
                    $initAmt = (float) ($manualAmounts[$g->id] ?? ($topGroups->firstWhere('id', $g->id)?->total_collected ?? 0));
                    $realAmt = (float) ($topGroups->firstWhere('id', $g->id)?->total_collected ?? 0);
                    $initPts = \App\Services\StandingPosterGenerator::calculateFinancePoints($initAmt);
                @endphp
                {
                    id: {{ $g->id }},
                    name: @json($g->name),
                    code: @json($g->code),
                    color_hex: @json($g->color_hex ?? '#be1e2d'),
                    real_amount: {{ $realAmt }},
                    amount: {{ $initAmt }},
                    points: {{ $initPts }}
                },
            @endforeach
        ],
        calcPoints(amount) {
            const a = parseFloat(amount) || 0;
            if (a >= 100000) return 40;
            if (a >= 90000) return 39;
            if (a >= 80000) return 37;
            if (a >= 70000) return 35;
            if (a >= 60000) return 32;
            if (a >= 50000) return 28;
            if (a >= 40000) return 22;
            if (a >= 30000) return 16;
            if (a >= 20000) return 10;
            if (a >= 10000) return 5;
            if (a >= 5000) return 2;
            return 0;
        },
        recalc() {
            this.groups.forEach(g => {
                g.points = this.calcPoints(g.amount);
            });
        },
        get sortedGroups() {
            return [...this.groups].sort((a, b) => {
                const amtA = parseFloat(a.amount) || 0;
                const amtB = parseFloat(b.amount) || 0;
                if (amtB !== amtA) return amtB - amtA;
                if (b.points !== a.points) return b.points - a.points;
                return a.name.localeCompare(b.name);
            });
        },
        cardBg(rank) {
            if (rank === 1) return 'bg-gradient-to-r from-amber-500/10 via-amber-400/5 to-white border-2 border-amber-400/90 shadow-md shadow-amber-400/10';
            if (rank === 2) return 'bg-gradient-to-r from-slate-200/30 via-slate-100/15 to-white border-2 border-slate-300 shadow-xs';
            if (rank === 3) return 'bg-gradient-to-r from-amber-700/10 via-amber-600/5 to-white border-2 border-amber-700/35 shadow-2xs';
            return 'bg-slate-50 border border-slate-200/80 shadow-2xs';
        },
        rankBadgeClass(rank) {
            if (rank === 1) return 'bg-gradient-to-br from-amber-400 via-amber-500 to-amber-600 text-slate-950 font-black shadow-md shadow-amber-500/30';
            if (rank === 2) return 'bg-gradient-to-br from-slate-200 via-slate-300 to-slate-400 text-slate-900 font-black shadow-xs';
            if (rank === 3) return 'bg-gradient-to-br from-amber-700 via-amber-600 to-amber-800 text-white font-black shadow-xs';
            return 'bg-slate-200/80 text-slate-700 border border-slate-300 font-bold';
        },
        rankLabel(rank) {
            if (rank === 1) return '1st';
            if (rank === 2) return '2nd';
            if (rank === 3) return '3rd';
            return '#' + rank;
        },
        medalTag(rank) {
            if (rank === 1) return 'GOLD';
            if (rank === 2) return 'SILVER';
            if (rank === 3) return 'BRONZE';
            return '';
        },
        pointsBadgeClass(rank) {
            if (rank === 1) return 'bg-amber-400 text-slate-950 border border-amber-500 font-black shadow-md shadow-amber-400/20';
            if (rank === 2) return 'bg-slate-200 text-slate-900 border border-slate-300 font-black shadow-xs';
            if (rank === 3) return 'bg-amber-600 text-white border border-amber-700 font-black shadow-xs';
            return 'bg-slate-100 text-slate-700 border border-slate-200 font-bold';
        },
        copyRealAmounts() {
            this.groups.forEach(g => {
                g.amount = g.real_amount;
            });
            this.recalc();
            this.toastMessage = 'യഥാർത്ഥ ലെഡ്ജർ തുകകൾ ഇൻപുട്ടിലേക്ക് പകർന്നു.';
            setTimeout(() => this.toastMessage = '', 4000);
        },
        resetAmounts() {
            this.groups.forEach(g => {
                g.amount = 0;
            });
            this.recalc();
            this.toastMessage = 'എല്ലാ തുകകളും പൂജ്യമാക്കി (0).';
            setTimeout(() => this.toastMessage = '', 4000);
        },
        openPreview() {
            const params = new URLSearchParams();
            this.groups.forEach(g => {
                params.append('amounts[' + g.id + ']', g.amount || 0);
            });
            window.open('{{ route('fund.standings.manual.poster') }}?' + params.toString(), '_blank');
        },
        downloadPoster() {
            const params = new URLSearchParams();
            this.groups.forEach(g => {
                params.append('amounts[' + g.id + ']', g.amount || 0);
            });
            window.location.href = '{{ route('fund.standings.manual.download') }}?' + params.toString();
        },
        async saveAmounts() {
            this.saving = true;
            this.toastMessage = '';
            try {
                const payload = {};
                this.groups.forEach(g => {
                    payload[g.id] = g.amount || 0;
                });
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch('{{ route('fund.standings.manual.save') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ amounts: payload })
                });
                const data = await response.json();
                this.toastMessage = data.message || 'മാനുവൽ ടീം തുകകൾ വിജയകരമായി സേവ് ചെയ്തു!';
            } catch (e) {
                this.toastMessage = 'സേവ് ചെയ്യുന്നതിൽ പിഴവുണ്ടായി.';
            } finally {
                this.saving = false;
                setTimeout(() => this.toastMessage = '', 5000);
            }
        }
    }));
});
</script>
@endsection
