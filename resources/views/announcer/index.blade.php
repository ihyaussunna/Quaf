@extends('layouts.app')

@section('title', 'QUAF - Announcer Console')

@section('content')
<div class="min-h-screen bg-[#edf3f8] py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto space-y-6">
        
        <!-- Header Console Banner -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-900 text-white">
                        Live Announcer Desk
                    </span>
                    <span class="text-xs text-slate-500 font-mono">Stage Microphone Sync</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight font-sora">
                    Stage Announcement Console
                </h1>
                <p class="text-xs text-slate-600 mt-1 max-w-2xl font-ml">
                    അഡ്മിൻ പാനലിൽ നിന്ന് വെരിഫൈ ചെയ്ത് അയച്ച മത്സര ഫലങ്ങൾ ഇവിടെ ലഭിക്കുന്നു. മൈക്രോഫോണിൽ അനൗൺസ് ചെയ്ത ശേഷം "അനൗൺസ് ചെയ്തു" ബട്ടൺ ക്ലിക്ക് ചെയ്യുക. ഫലം ഉടൻ തന്നെ മീഡിയ ടീമിന്റെ ഡെസ്കിലേക്ക് കൈമാറുന്നതാണ്.
                </p>
            </div>

            <!-- Live Status & Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('announcer.stage') }}" class="px-4 py-2.5 rounded-xl bg-[#005c94] hover:bg-[#004b78] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-2xs font-sora">
                    <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg>
                    <span>സ്റ്റേജ് കോളിംഗ് (Stage Calling Console) &rarr;</span>
                </a>
                <a href="{{ route('media.results.index') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-2xs font-sora">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                    <span>മീഡിയ ഡെസ്ക് (Media Desk)</span>
                </a>
                <button type="button" onclick="window.location.reload()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Refresh Now</span>
                </button>
                <div class="flex items-center gap-2 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="autoReloadLabel">Auto-refresh: 20s</span>
                </div>
            </div>
        </div>

        <!-- Flash messages -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm font-semibold flex items-center justify-between shadow-2xs">
                <span class="font-ml">{{ session('success') }}</span>
                <span class="text-xs font-mono text-emerald-700">SUCCESS</span>
            </div>
        @endif

        <!-- Filter Stat Tabs -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Ready for announcement -->
            <a href="{{ route('announcer.index', ['tab' => 'ready']) }}" 
               class="p-5 rounded-2xl border transition-all {{ $tab === 'ready' ? 'bg-amber-50 border-amber-300 ring-2 ring-amber-400' : 'bg-white border-slate-200 hover:border-slate-300' }}">
                <div class="flex items-center justify-between text-amber-700 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider font-sora">Awaiting Announcement</span>
                    @if($stats['ready_count'] > 0)
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                    @endif
                </div>
                <div class="text-3xl font-black text-slate-900 font-sora">{{ $stats['ready_count'] }}</div>
                <div class="text-[11px] text-amber-800 font-medium mt-1 font-ml">അനൗൺസ് ചെയ്യാൻ കാത്തിരിക്കുന്നവ</div>
            </a>

            <!-- Announced -->
            <a href="{{ route('announcer.index', ['tab' => 'announced']) }}" 
               class="p-5 rounded-2xl border transition-all {{ $tab === 'announced' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-400' : 'bg-white border-slate-200 hover:border-slate-300' }}">
                <div class="flex items-center justify-between text-emerald-700 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider font-sora">Already Announced</span>
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div class="text-3xl font-black text-slate-900 font-sora">{{ $stats['announced_count'] }}</div>
                <div class="text-[11px] text-emerald-800 font-medium mt-1 font-ml">സ്റ്റേജിൽ അനൗൺസ് ചെയ്തവ</div>
            </a>

            <!-- Total Results -->
            <a href="{{ route('announcer.index', ['tab' => 'all']) }}" 
               class="p-5 rounded-2xl border transition-all {{ $tab === 'all' ? 'bg-slate-100 border-slate-300 ring-2 ring-slate-400' : 'bg-white border-slate-200 hover:border-slate-300' }}">
                <div class="flex items-center justify-between text-slate-500 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider font-sora">Total Transferred</span>
                    <span class="text-xs font-mono font-bold">{{ $stats['total_count'] }}</span>
                </div>
                <div class="text-3xl font-black text-slate-900 font-sora">{{ $stats['total_count'] }}</div>
                <div class="text-[11px] text-slate-500 font-medium mt-1 font-ml">ആകെ ലഭ്യമായ ഫലങ്ങൾ</div>
            </a>
        </div>

        <!-- Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('announcer.index', ['tab' => 'ready']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === 'ready' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Awaiting Announcement ({{ $stats['ready_count'] }})
                </a>
                <a href="{{ route('announcer.index', ['tab' => 'announced']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === 'announced' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Announced ({{ $stats['announced_count'] }})
                </a>
                <a href="{{ route('announcer.index', ['tab' => 'all']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    All Results
                </a>
            </div>

            <form method="GET" action="{{ route('announcer.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="relative w-full md:w-64">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search program, code..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-3.5 py-2 bg-slate-900 text-white font-bold text-xs rounded-xl hover:bg-slate-800 transition">
                    Search
                </button>
                @if($search)
                    <a href="{{ route('announcer.index', ['tab' => $tab]) }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 text-xs font-medium rounded-xl hover:bg-slate-100 transition">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        @if($results->isEmpty())
            <!-- Empty state -->
            <div class="bg-white rounded-3xl p-16 text-center border border-slate-200 shadow-xs max-w-3xl mx-auto space-y-3">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"></path></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800 font-sora">
                    @if($tab === 'ready')
                        No programs currently waiting for announcement
                    @else
                        No results found for this view
                    @endif
                </h3>
                <p class="text-xs text-slate-500 font-ml max-w-md mx-auto">
                    അഡ്മിൻ പാനലിൽ നിന്ന് മൂല്യനിർണ്ണയം കഴിഞ്ഞ ഫലങ്ങൾ അയക്കുമ്പോൾ ഇവിടെ തത്സമയം ദൃശ്യമാകും.
                </p>
            </div>
        @else
            <!-- Results Cards List -->
            <div class="space-y-6">
                @foreach($results as $res)
                    @php
                        $isDelivered = in_array($res->status, ['send', 'delivered']);
                        $prog = $res->program;
                        
                        $firstWinnerName = $res->firstEntry?->student?->name ?: ($res->firstEntry ? 'Chest #'.$res->firstEntry->chest_number : 'None');
                        $firstWinnerChest = $res->firstEntry?->chest_number ?? '-';
                        $firstWinnerTeam = $res->firstEntry?->group?->name ?? $res->firstEntry?->student?->group?->name ?? '-';

                        $secondWinnerName = $res->secondEntry?->student?->name ?: ($res->secondEntry ? 'Chest #'.$res->secondEntry->chest_number : 'None');
                        $secondWinnerChest = $res->secondEntry?->chest_number ?? '-';
                        $secondWinnerTeam = $res->secondEntry?->group?->name ?? $res->secondEntry?->student?->group?->name ?? '-';

                        $thirdWinnerName = $res->thirdEntry?->student?->name ?: ($res->thirdEntry ? 'Chest #'.$res->thirdEntry->chest_number : 'None');
                        $thirdWinnerChest = $res->thirdEntry?->chest_number ?? '-';
                        $thirdWinnerTeam = $res->thirdEntry?->group?->name ?? $res->thirdEntry?->student?->group?->name ?? '-';

                        $scriptText = "ക്വാഫ് ഫെസ്റ്റ് {$prog?->name} മത്സര ഫലം പ്രഖ്യാപിക്കുന്നു: ഒന്നാം സ്ഥാനം: {$firstWinnerName} (ചെസ്റ്റ് #{$firstWinnerChest}, ടീം {$firstWinnerTeam}). രണ്ടാം സ്ഥാനം: {$secondWinnerName} (ചെസ്റ്റ് #{$secondWinnerChest}, ടീം {$secondWinnerTeam}). മൂന്നാം സ്ഥാനം: {$thirdWinnerName} (ചെസ്റ്റ് #{$thirdWinnerChest}, ടീം {$thirdWinnerTeam}).";
                    @endphp

                    <div class="bg-white rounded-3xl p-6 sm:p-8 border {{ $isDelivered ? 'border-amber-300 ring-4 ring-amber-100/70 shadow-md' : 'border-slate-200 shadow-xs' }} transition">
                        
                        <!-- Top Header Section of Card -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                    <span class="px-2.5 py-0.5 rounded font-mono text-xs font-bold bg-slate-900 text-white">
                                        {{ $prog?->code ?: 'PROG' }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $isDelivered ? 'bg-amber-100 text-amber-900 border border-amber-300 animate-pulse' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' }}">
                                        {{ $isDelivered ? 'READY FOR MIC ANNOUNCEMENT' : 'ANNOUNCED ON STAGE' }}
                                    </span>
                                    <span class="text-xs text-slate-500 font-semibold">
                                        {{ $prog?->eligibility ?? 'General' }} • {{ $prog?->is_stage ? 'Stage Event' : 'Non-stage Event' }} • {{ $prog?->stage?->name ?? 'Main Stage' }}
                                    </span>
                                </div>
                                <h3 class="text-2xl font-bold text-slate-900 font-sora">{{ $prog?->name }}</h3>
                                @if($prog?->malayalam_name)
                                    <p class="text-sm text-slate-500 font-ml mt-0.5">{{ $prog->malayalam_name }}</p>
                                @endif
                            </div>

                            @if($isDelivered)
                                <form method="POST" action="{{ route('announcer.announced', $res->id) }}">
                                    @csrf
                                    <button type="submit" class="w-full sm:w-auto px-6 py-3.5 bg-[#be1e2d] hover:bg-[#a01624] text-white rounded-2xl text-sm font-bold transition shadow-sm flex items-center justify-center gap-2 font-ml">
                                        <svg class="w-5 h-5 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                        <span>അനൗൺസ് ചെയ്തു (Mark as Announced)</span>
                                    </button>
                                </form>
                            @else
                                <div class="flex flex-col sm:items-end gap-1">
                                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Announced on Stage
                                    </span>
                                    <span class="text-[11px] font-semibold text-slate-500 font-ml">
                                        {{ $res->is_media_published ? 'മീഡിയ പോസ്റ്റർ പബ്ലിഷ് ചെയ്തു' : 'മീഡിയ ഡെസ്കിലേക്ക് കൈമാറി' }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Stage Microphone Speech Script Box -->
                        <div class="mt-5 p-4 sm:p-5 rounded-2xl bg-amber-50/70 border border-amber-200">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-900 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"></path></svg>
                                    മൈക്രോഫോൺ അനൗൺസ്മെന്റ് സ്ക്രിപ്റ്റ് (Stage Announcement Script)
                                </span>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ addslashes($scriptText) }}'); alert('അനൗൺസ്മെന്റ് സ്ക്രിപ്റ്റ് കോപ്പി ചെയ്തു.');" 
                                        class="px-2.5 py-1 rounded-lg bg-amber-200/80 hover:bg-amber-300 text-amber-900 text-[11px] font-bold transition font-ml">
                                    സ്ക്രിപ്റ്റ് കോപ്പി ചെയ്യുക
                                </button>
                            </div>
                            <div class="text-sm font-ml text-slate-800 leading-relaxed bg-white/80 p-3.5 rounded-xl border border-amber-200/60">
                                <div>ശ്രദ്ധിക്കുക... ക്വാഫ് ഫെസ്റ്റ് <strong>{{ $prog?->name }}</strong> ({{ $prog?->malayalam_name ?: $prog?->code }}) മത്സര ഫലം പ്രഖ്യാപിക്കുന്നു:</div>
                                <div class="mt-2 space-y-1 font-medium">
                                    <div>• <strong>ഒന്നാം സ്ഥാനം:</strong> {{ $firstWinnerName }} (ചെസ്റ്റ് നമ്പർ #{{ $firstWinnerChest }}, {{ $firstWinnerTeam }})</div>
                                    <div>• <strong>രണ്ടാം സ്ഥാനം:</strong> {{ $secondWinnerName }} (ചെസ്റ്റ് നമ്പർ #{{ $secondWinnerChest }}, {{ $secondWinnerTeam }})</div>
                                    <div>• <strong>മൂന്നാം സ്ഥാനം:</strong> {{ $thirdWinnerName }} (ചെസ്റ്റ് നമ്പർ #{{ $thirdWinnerChest }}, {{ $thirdWinnerTeam }})</div>
                                </div>
                            </div>
                        </div>

                        <!-- Winners Podium Details -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-5">
                            <!-- 1st Place -->
                            <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-amber-800 font-sora">1st Place</span>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded bg-amber-200 text-amber-900">Rank 1</span>
                                </div>
                                <div class="font-bold text-slate-900 text-base font-sora">
                                    {{ $firstWinnerName }}
                                </div>
                                <div class="text-xs text-slate-600 mt-1">
                                    Chest: <strong class="font-mono text-slate-900">#{{ $firstWinnerChest }}</strong> • Team: <strong class="text-slate-800">{{ $firstWinnerTeam }}</strong>
                                </div>
                            </div>

                            <!-- 2nd Place -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700 font-sora">2nd Place</span>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded bg-slate-200 text-slate-800">Rank 2</span>
                                </div>
                                <div class="font-bold text-slate-900 text-base font-sora">
                                    {{ $secondWinnerName }}
                                </div>
                                <div class="text-xs text-slate-600 mt-1">
                                    Chest: <strong class="font-mono text-slate-900">#{{ $secondWinnerChest }}</strong> • Team: <strong class="text-slate-800">{{ $secondWinnerTeam }}</strong>
                                </div>
                            </div>

                            <!-- 3rd Place -->
                            <div class="p-4 rounded-2xl bg-red-50/60 border border-red-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-[#be1e2d] font-sora">3rd Place</span>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded bg-red-100 text-[#be1e2d]">Rank 3</span>
                                </div>
                                <div class="font-bold text-slate-900 text-base font-sora">
                                    {{ $thirdWinnerName }}
                                </div>
                                <div class="text-xs text-slate-600 mt-1">
                                    Chest: <strong class="font-mono text-slate-900">#{{ $thirdWinnerChest }}</strong> • Team: <strong class="text-slate-800">{{ $thirdWinnerTeam }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Note regarding Media Sync -->
                        @if($isDelivered)
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-ml">
                                <span>മൈക്കിൽ അനൗൺസ് ചെയ്ത ശേഷം മുകളിലുള്ള ബട്ടൺ ക്ലിക്ക് ചെയ്താൽ ഈ റിസൾട്ട് തത്സമയം മീഡിയ ഡെസ്കിലേക്ക് കൈമാറും.</span>
                                <span class="font-mono text-slate-400">Step 2 of 3: Stage Announcement</span>
                            </div>
                        @else
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-ml">
                                <span>ഈ ഫലം മീഡിയ ടീം പരിശോധിക്കുകയും സോഷ്യൽ മീഡിയ പോസ്റ്റർ തയ്യാറാക്കുകയും ചെയ്യും.</span>
                                @if($res->poster_image)
                                    <a href="{{ route('media.results.public-poster', $res) }}" target="_blank" class="text-[#be1e2d] font-bold hover:underline">
                                        പബ്ലിക് പോസ്റ്റർ കാണുക
                                    </a>
                                @endif
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>

            @if($results->hasPages())
                <div class="p-4 bg-white rounded-2xl border border-slate-200">
                    {{ $results->links() }}
                </div>
            @endif
        @endif

    </div>
</div>

<script>
    // Auto-reload feature for stage announcer
    let countdown = 20;
    const label = document.getElementById('autoReloadLabel');
    if (label) {
        setInterval(() => {
            countdown--;
            if (countdown <= 0) {
                window.location.reload();
            } else {
                label.innerText = 'Auto-refresh: ' + countdown + 's';
            }
        }, 1000);
    }
</script>
@endsection
