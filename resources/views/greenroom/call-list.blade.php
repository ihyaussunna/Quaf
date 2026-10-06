<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Digital Call List & Attendance Desk | QUAF 09</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Malayalam:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 font-sora antialiased min-h-screen flex flex-col"
      x-data="callListManager()">

    <!-- Topbar -->
    <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-40 shadow-xs gap-4 flex-nowrap">
        <div class="flex items-center gap-3 sm:gap-4 shrink-0">
            <img src="{{ asset('images/dashboard-logo-dark.svg') }}" alt="QUAF 09" class="h-9 sm:h-10 w-auto shrink-0 object-contain">
            <div class="hidden xs:block shrink-0">
                <div class="flex items-center gap-2">
                    <span class="font-rockwell font-bold tracking-wider text-sm sm:text-base text-slate-900 whitespace-nowrap">DIGITAL CALL LIST</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping shrink-0"></span>
                </div>
                <span class="text-[10px] font-mono tracking-widest text-[#005c94] block uppercase font-bold whitespace-nowrap">Real-Time Attendance & Jury Sync</span>
            </div>
        </div>

        <!-- Fest Navigation Triad -->
        <nav class="hidden md:flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-semibold">
            <a href="{{ route('greenroom.index') }}" class="px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-white/60 transition-all font-sora">
                Green Room Desk
            </a>
            <a href="{{ route('greenroom.call-list') }}" class="px-3.5 py-1.5 rounded-lg bg-white text-slate-900 shadow-2xs font-bold font-sora">
                Digital Call List
            </a>
        </nav>

        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <!-- IST Live Clock Badge (Auto-updates every second without refresh) -->
            <div class="flex items-center gap-2 px-3 sm:px-3.5 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono font-bold text-slate-700 shadow-2xs"
                 x-data="{
                     currentTime: '',
                     updateClock() {
                         const now = new Date();
                         this.currentTime = now.toLocaleTimeString('en-US', {
                             timeZone: 'Asia/Kolkata',
                             hour: '2-digit',
                             minute: '2-digit',
                             second: '2-digit',
                             hour12: true
                         }) + ' IST';
                     }
                 }"
                 x-init="updateClock(); setInterval(() => updateClock(), 1000)">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                <span x-text="currentTime">{{ \Carbon\Carbon::now(config('app.timezone', 'Asia/Kolkata'))->format('h:i:s A') }} IST</span>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-3 sm:px-3.5 py-2 rounded-xl text-xs font-mono font-bold bg-slate-100 text-slate-700 hover:text-red-700 hover:bg-red-50 border border-slate-200 transition-all whitespace-nowrap">
                    Sign Out
                </button>
            </form>
        </div>
    </header>

    <!-- Floating Live Toast Notification -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 max-w-sm rounded-2xl px-4 py-3 shadow-xl font-mono text-xs flex items-center gap-3 border"
         :class="toast.isError ? 'bg-red-950 text-red-200 border-red-800' : 'bg-slate-900 text-emerald-300 border-slate-700'"
         style="display: none;">
        <span class="w-2.5 h-2.5 rounded-full" :class="toast.isError ? 'bg-red-500' : 'bg-emerald-400 animate-pulse'"></span>
        <span x-text="toast.message"></span>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 mt-4">
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-mono flex items-center justify-between shadow-xs">
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 mt-4">
            <div class="p-4 rounded-xl bg-red-50 border border-red-300 text-red-800 text-xs font-mono flex items-center justify-between shadow-xs">
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 md:p-8 space-y-6">

        <!-- Search & Filter Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
            <form method="GET" action="{{ route('greenroom.call-list') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 items-end">
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Zone / Category</label>
                    <select name="zone" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-[#005c94]">
                        <option value="">-- All Zones --</option>
                        @foreach($zones as $zKey => $zVal)
                            @php
                                $zoneName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                                $zoneValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                            @endphp
                            <option value="{{ $zoneValue }}" {{ ($selectedZone ?? '') == $zoneValue ? 'selected' : '' }}>{{ $zoneName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Program</label>
                    <select name="program" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-[#005c94]">
                        <option value="">-- Choose Program --</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" {{ (string)$selectedProgramId === (string)$prog->id ? 'selected' : '' }}>
                                {{ $prog->name }} (ID: {{ $prog->code ?: $prog->id }}){{ $prog->is_call_list_locked ? ' [LOCKED BY ADMIN - ACCESS REMOVED]' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Search Participant</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Name, Chest #, Code..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-[#005c94]">
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="w-full bg-[#005c94] text-white py-2 px-4 rounded-xl text-xs font-bold hover:bg-[#004b78] transition flex items-center justify-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Filter</span>
                    </button>
                </div>

                @if($selectedProgram)
                    <!-- Secondary Filters -->
                    <div class="sm:col-span-2 md:col-span-12 pt-3 border-t border-slate-100 flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-slate-500 font-bold uppercase text-[10px] tracking-wider">Attendance Filter:</span>
                        <a href="{{ request()->fullUrlWithQuery(['attendance' => null, 'eval_status' => null]) }}"
                           class="px-2.5 py-1 rounded-lg font-mono font-semibold {{ empty($attendanceFilter) && empty($evalFilter) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            All
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['attendance' => 'present', 'eval_status' => null]) }}"
                           class="px-2.5 py-1 rounded-lg font-mono font-semibold {{ ($attendanceFilter ?? '') === 'present' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                            Present Only
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['attendance' => 'absent', 'eval_status' => null]) }}"
                           class="px-2.5 py-1 rounded-lg font-mono font-semibold {{ ($attendanceFilter ?? '') === 'absent' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-800 hover:bg-red-100' }}">
                            Absent Only
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['attendance' => 'waiting', 'eval_status' => null]) }}"
                           class="px-2.5 py-1 rounded-lg font-mono font-semibold {{ ($attendanceFilter ?? '') === 'waiting' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                            Waiting Only
                        </a>
                        <span class="text-slate-300">|</span>
                        <a href="{{ request()->fullUrlWithQuery(['eval_status' => 'evaluated', 'attendance' => null]) }}"
                           class="px-2.5 py-1 rounded-lg font-mono font-semibold {{ ($evalFilter ?? '') === 'evaluated' ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-800 hover:bg-purple-100' }}">
                            Evaluated
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['eval_status' => 'pending', 'attendance' => null]) }}"
                           class="px-2.5 py-1 rounded-lg font-mono font-semibold {{ ($evalFilter ?? '') === 'pending' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-800 hover:bg-blue-100' }}">
                            Pending Evaluation
                        </a>
                    </div>
                @endif
            </form>
        </div>

        @if($selectedProgram)
            @php
                $windowState = $windowState ?? $selectedProgram->getCallListWindowState();
                $isAdmin = in_array(Auth::user()->role, ['admin', 'super_admin']);
                $isEditable = $windowState['is_open'] || $isAdmin;
            @endphp
            <!-- Program Header & Live Summary Cards -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-2xs space-y-5">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-slate-900 text-white">
                                ID: {{ $selectedProgram->code ?: '#'.$selectedProgram->id }}
                            </span>
                            <span class="text-xs text-slate-500 font-mono">
                                {{ $selectedProgram->category->name ?? $selectedProgram->eligibility ?? 'General' }} &bull; Stage: {{ $selectedProgram->stage->name ?? 'TBA' }}
                            </span>
                            @if($windowState['state'] === 'locked_by_admin')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-red-100 text-red-800 border border-red-200 flex items-center gap-1 font-mono">
                                    <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    {{ $windowState['badge_ml'] }} (LOCKED BY ADMIN)
                                </span>
                            @elseif($windowState['state'] === 'auto_locked_ended')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-red-100 text-red-800 border border-red-200 flex items-center gap-1 font-mono">
                                    <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $windowState['badge_ml'] }} (SCHEDULE ENDED)
                                </span>
                            @elseif($windowState['state'] === 'upcoming_window')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1 font-mono">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    {{ $windowState['badge_ml'] }} (OPENS AT {{ $windowState['opens_at']?->format('h:i A') }})
                                </span>
                            @elseif($windowState['state'] === 'not_scheduled')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700 border border-slate-200 flex items-center gap-1 font-mono">
                                    {{ $windowState['badge_ml'] }} (NOT SCHEDULED)
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1 font-mono">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    {{ $windowState['badge_ml'] }} (ATTENDANCE ACTIVE)
                                </span>
                            @endif
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight font-sora">
                            {{ $selectedProgram->name }}
                        </h2>
                        @if($selectedProgram->malayalam_name)
                            <div class="text-xs text-slate-600 font-ml mt-0.5">{{ $selectedProgram->malayalam_name }}</div>
                        @endif
                    </div>

                    <!-- Actions Bar -->
                        @php
                            $shuffleCount = (int) ($selectedProgram->shuffle_count ?? 0);
                            $canShuffle = ($shuffleCount < 2) || $isAdmin;
                        @endphp
                        <!-- Auto Shuffle Code Letters -->
                        <form method="POST" action="{{ route('greenroom.generate-codes', $selectedProgram->id) }}" onsubmit="return confirm('ഹാജരായവർക്ക് മാത്രം റാൻഡം ആയി കോഡ് ലെറ്ററുകൾ (A, B, C...) നൽകണോ? (അവസരം: {{ min(2, $shuffleCount + 1) }}/2)');">
                            @csrf
                            <button type="submit" @if(! $isEditable || ! $canShuffle) disabled @endif
                                    class="px-4 py-2.5 rounded-xl {{ $canShuffle ? 'bg-purple-600 hover:bg-purple-700 cursor-pointer' : 'bg-slate-400 cursor-not-allowed' }} disabled:opacity-50 text-white font-bold text-xs flex items-center gap-1.5 shadow-2xs transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                @if($canShuffle)
                                    <span>നറുക്കെടുപ്പ് (Shuffle Chance {{ $shuffleCount + 1 }}/2)</span>
                                @else
                                    <span>Shuffle Limit Reached (2/2 Used)</span>
                                @endif
                            </button>
                        </form>

                        @if($isEditable)
                            <button type="button"
                                    @click="saveAllCodeLetters('{{ route('greenroom.batch-update-code-letters', $selectedProgram->id) }}')"
                                    :disabled="savingAllCodes"
                                    class="px-4 py-2.5 rounded-xl bg-[#005c94] hover:bg-[#004875] text-white font-bold text-xs flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer active:scale-95 disabled:opacity-50">
                                <template x-if="savingAllCodes">
                                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                </template>
                                <template x-if="!savingAllCodes">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                </template>
                                <span x-text="savingAllCodes ? 'Saving...' : 'Save All Codes (കോഡുകൾ സേവ് ചെയ്യുക)'"></span>
                            </button>
                        @endif

                    </div>
                </div>

                <!-- 6 Quick Summary Counters -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 font-mono block text-[11px]">Total Call List</span>
                        <span class="text-xl font-black text-slate-900 mt-1 block" x-text="stats.total">{{ $stats['total'] }}</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900">
                        <span class="font-mono block text-emerald-700 text-[11px]">Present (ഹാജർ)</span>
                        <span class="text-xl font-black mt-1 block" x-text="stats.present">{{ $stats['present'] }}</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-red-50 border border-red-200 text-red-900">
                        <span class="font-mono block text-red-700 text-[11px]">Absent (ഹാജരില്ല)</span>
                        <span class="text-xl font-black mt-1 block" x-text="stats.absent">{{ $stats['absent'] }}</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900">
                        <span class="font-mono block text-amber-700 text-[11px]">Waiting (കാത്തിരിപ്പ്)</span>
                        <span class="text-xl font-black mt-1 block" x-text="stats.waiting">{{ $stats['waiting'] }}</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-purple-50 border border-purple-200 text-purple-900">
                        <span class="font-mono block text-purple-700 text-[11px]">Evaluated</span>
                        <span class="text-xl font-black mt-1 block" x-text="stats.evaluated">{{ $stats['evaluated'] }}</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900">
                        <span class="font-mono block text-blue-700 text-[11px]">Pending Eval</span>
                        <span class="text-xl font-black mt-1 block" x-text="stats.pending_evaluation">{{ $stats['pending_evaluation'] }}</span>
                    </div>
                </div>

                @if(! $isEditable)
                    <div class="p-4 rounded-2xl border-2 text-xs font-mono flex items-start gap-3 {{ $windowState['badge_color'] === 'amber' ? 'bg-amber-50 border-amber-300 text-amber-900' : ($windowState['badge_color'] === 'slate' ? 'bg-slate-50 border-slate-200 text-slate-700' : 'bg-red-50 border-red-300 text-red-900') }}">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <span class="font-bold uppercase tracking-wider block">
                                {{ $windowState['badge_ml'] }} &bull; {{ $windowState['badge'] }}
                            </span>
                            <p class="mt-1 leading-relaxed">{{ $windowState['message'] }}</p>
                            @if($windowState['opens_at'])
                                <div class="mt-2 pt-2 border-t border-slate-200/60 flex flex-wrap items-center gap-4 text-[11px] opacity-90">
                                    <span>ഷെഡ്യൂൾ ചെയ്ത തുടക്ക സമയം: <strong>{{ $windowState['scheduled_start']?->format('h:i A') }}</strong></span>
                                    <span>ഹാജർ തുറക്കുന്ന സമയം: <strong>{{ $windowState['opens_at']->format('h:i A') }}</strong> (10 മിനിറ്റ് മുമ്പ്)</span>
                                    <span>അവസാനിക്കുന്ന സമയം: <strong>{{ $windowState['closes_at']?->format('h:i A') }}</strong></span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Digital Interactive Table -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 font-sora">
                            മത്സരാർത്ഥികളുടെ തത്സമയ ഹാജർ പട്ടിക (Live Call Sheet)
                        </h3>
                        <p class="text-[11px] text-slate-500 font-mono mt-0.5">
                            PRESENT അടയാളപ്പെടുത്തുമ്പോൾ മത്സരാർത്ഥി തത്സമയം വിധികർത്താവിന്റെ മൂല്യനിർണ്ണയ പട്ടികയിൽ ലഭ്യമാകും.
                        </p>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-600 bg-white px-3 py-1 rounded-xl border border-slate-200">
                        {{ $entries->count() }} Records Loaded
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs font-sora">
                        <thead>
                            <tr class="bg-slate-100/75 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                                <th class="px-4 py-3 text-center w-12">No</th>
                                <th class="px-4 py-3 text-center w-36 sm:w-44">Code Letter</th>
                                <th class="px-4 py-3 w-24">Chest #</th>
                                <th class="px-4 py-3">Student Name</th>
                                <th class="px-4 py-3">Group / Team</th>
                                <th class="px-4 py-3 text-center w-72">Attendance Action</th>
                                <th class="px-4 py-3 text-center w-36">Evaluation Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($entries as $index => $entry)
                                <tr id="entry-row-{{ $entry->id }}"
                                    class="hover:bg-slate-50/75 transition-colors {{ $entry->attendance_status === 'absent' ? 'opacity-50 bg-red-50/20' : '' }}">
                                    <td class="px-4 py-3 text-center font-mono text-slate-400">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        @if($isEditable)
                                            <div class="inline-flex items-center justify-center gap-1.5" x-data="{
                                                codeVal: '{{ $entry->code_letter }}',
                                                isSaving: false,
                                                isSaved: false,
                                                async saveCode() {
                                                    if (this.isSaving) return;
                                                    this.isSaving = true;
                                                    try {
                                                        const ok = await updateCodeLetter('{{ route('greenroom.update-code-letter', $entry) }}', this.codeVal, {{ $entry->id }});
                                                        if (ok) {
                                                            this.isSaved = true;
                                                            setTimeout(() => { this.isSaved = false; }, 2500);
                                                        }
                                                    } finally {
                                                        this.isSaving = false;
                                                    }
                                                }
                                            }">
                                                <input type="text"
                                                       maxlength="4"
                                                       placeholder="-"
                                                       x-model="codeVal"
                                                       data-entry-id="{{ $entry->id }}"
                                                       @keydown.enter.prevent="saveCode()"
                                                       title="Enter Code Letter (A, B, C...) and click Save"
                                                       class="code-letter-input w-12 sm:w-14 h-9 text-center uppercase font-mono font-black text-xs sm:text-sm rounded-xl border border-slate-300 bg-white hover:border-[#005c94] focus:border-[#005c94] focus:ring-2 focus:ring-[#005c94]/20 shadow-2xs transition-all">

                                                <button type="button"
                                                        @click="saveCode()"
                                                        :disabled="isSaving"
                                                        :class="isSaved ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-100 hover:bg-[#005c94] hover:text-white text-slate-700 border-slate-300'"
                                                        title="Save this code letter"
                                                        class="h-9 px-2 sm:px-2.5 rounded-xl border text-[11px] font-mono font-bold flex items-center gap-1 shadow-2xs transition-all cursor-pointer active:scale-95">
                                                    <template x-if="isSaving">
                                                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                                    </template>
                                                    <template x-if="!isSaving && isSaved">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    </template>
                                                    <template x-if="!isSaving && !isSaved">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                                    </template>
                                                    <span class="text-[10px] uppercase font-bold" x-text="isSaving ? '...' : (isSaved ? 'Saved' : 'Save')"></span>
                                                </button>
                                            </div>
                                        @else
                                            <span id="code-badge-{{ $entry->id }}"
                                                  class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-black font-mono {{ $entry->code_letter ? 'bg-purple-100 text-purple-900 border border-purple-300' : 'bg-slate-100 text-slate-400 italic' }}">
                                                {{ $entry->code_letter ? 'Code ' . $entry->code_letter : '- None -' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-mono font-bold text-slate-900">
                                        #{{ $entry->chest_number }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-slate-900">{{ $entry->student?->name ?? 'Participant' }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $entry->student?->student_id ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-700">
                                        {{ $entry->student?->group?->name ?? $entry->group?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if(! $isEditable)
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold font-mono {{ $entry->attendance_status === 'present' ? 'bg-emerald-100 text-emerald-800' : ($entry->attendance_status === 'absent' ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-700') }}">
                                                {{ strtoupper($entry->attendance_status ?? 'waiting') }}
                                            </span>
                                        @else
                                            <!-- Interactive Large Buttons for Fast Attendance Marking -->
                                            <div class="inline-flex items-center gap-1.5" id="attendance-buttons-{{ $entry->id }}">
                                                <button type="button"
                                                        @click="markAttendance({{ $entry->id }}, 'present', '{{ route('greenroom.mark-attendance', $entry->id) }}')"
                                                        id="btn-present-{{ $entry->id }}"
                                                        class="px-3.5 py-2 min-h-[38px] rounded-xl text-xs font-bold transition-all active:scale-95 shadow-2xs cursor-pointer {{ $entry->attendance_status === 'present' ? 'bg-emerald-600 text-white font-black' : 'bg-slate-100 hover:bg-emerald-50 text-slate-700 border border-slate-200' }}">
                                                    PRESENT
                                                </button>
                                                <button type="button"
                                                        @click="markAttendance({{ $entry->id }}, 'absent', '{{ route('greenroom.mark-attendance', $entry->id) }}')"
                                                        id="btn-absent-{{ $entry->id }}"
                                                        class="px-3.5 py-2 min-h-[38px] rounded-xl text-xs font-bold transition-all active:scale-95 shadow-2xs cursor-pointer {{ $entry->attendance_status === 'absent' ? 'bg-red-600 text-white font-black' : 'bg-slate-100 hover:bg-red-50 text-slate-700 border border-slate-200' }}">
                                                    ABSENT
                                                </button>
                                                <button type="button"
                                                        @click="markAttendance({{ $entry->id }}, 'waiting', '{{ route('greenroom.mark-attendance', $entry->id) }}')"
                                                        id="btn-waiting-{{ $entry->id }}"
                                                        class="px-3.5 py-2 min-h-[38px] rounded-xl text-xs font-bold transition-all active:scale-95 shadow-2xs cursor-pointer {{ $entry->attendance_status === 'waiting' || empty($entry->attendance_status) ? 'bg-amber-500 text-white font-black' : 'bg-slate-100 hover:bg-amber-50 text-slate-700 border border-slate-200' }}">
                                                    WAITING
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span id="eval-status-{{ $entry->id }}"
                                              class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-mono font-bold uppercase
                                              @if($entry->evaluation_status === 'EVALUATED') bg-purple-100 text-purple-900 border border-purple-200
                                              @elseif($entry->evaluation_status === 'EVALUATION_PENDING') bg-blue-100 text-blue-900 border border-blue-200
                                              @elseif($entry->evaluation_status === 'NOT_ELIGIBLE') bg-red-100 text-red-800 border border-red-200
                                              @else bg-slate-100 text-slate-600 border border-slate-200 @endif">
                                            {{ $entry->evaluation_status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-400 text-xs">
                                        തിരഞ്ഞെടുത്ത ഫിൽട്ടറുകളിൽ മത്സരാർത്ഥികളൊന്നും ലഭ്യമല്ല.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="p-12 bg-white rounded-3xl border border-slate-200 text-center text-xs text-slate-500 space-y-2">
                <svg class="w-8 h-8 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <div class="font-bold text-slate-700">ഡിജിറ്റൽ കോൾ ലിസ്റ്റ് തുറക്കാൻ മുകളിലെ ഡ്രോപ്പ് ഡൗണിൽ നിന്ന് പ്രോഗ്രാം തിരഞ്ഞെടുക്കുക.</div>
            </div>
        @endif

    </main>

    <!-- Alpine.js Call List Manager Script -->
    <script>
        function callListManager() {
            return {
                stats: {
                    total: {{ (int) ($stats['total'] ?? 0) }},
                    present: {{ (int) ($stats['present'] ?? 0) }},
                    absent: {{ (int) ($stats['absent'] ?? 0) }},
                    waiting: {{ (int) ($stats['waiting'] ?? 0) }},
                    evaluated: {{ (int) ($stats['evaluated'] ?? 0) }},
                    pending_evaluation: {{ (int) ($stats['pending_evaluation'] ?? 0) }},
                },
                savingAllCodes: false,
                toast: {
                    show: false,
                    message: '',
                    isError: false,
                    timeout: null
                },
                showToast(msg, isErr = false) {
                    this.toast.message = msg;
                    this.toast.isError = isErr;
                    this.toast.show = true;
                    if (this.toast.timeout) clearTimeout(this.toast.timeout);
                    this.toast.timeout = setTimeout(() => {
                        this.toast.show = false;
                    }, 3500);
                },
                async markAttendance(entryId, status, actionUrl) {
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch(actionUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ status: status })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Failed to update attendance');
                        }

                        // Update local button states
                        const btnPresent = document.getElementById('btn-present-' + entryId);
                        const btnAbsent = document.getElementById('btn-absent-' + entryId);
                        const btnWaiting = document.getElementById('btn-waiting-' + entryId);
                        const row = document.getElementById('entry-row-' + entryId);
                        const codeBadge = document.getElementById('code-badge-' + entryId);
                        const evalBadge = document.getElementById('eval-status-' + entryId);

                        if (btnPresent && btnAbsent && btnWaiting) {
                            btnPresent.className = status === 'present' 
                                ? 'px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-2xs bg-emerald-600 text-white font-black'
                                : 'px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-2xs bg-slate-100 hover:bg-emerald-50 text-slate-700 border border-slate-200';
                            btnAbsent.className = status === 'absent'
                                ? 'px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-2xs bg-red-600 text-white font-black'
                                : 'px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-2xs bg-slate-100 hover:bg-red-50 text-slate-700 border border-slate-200';
                            btnWaiting.className = status === 'waiting'
                                ? 'px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-2xs bg-amber-500 text-white font-black'
                                : 'px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-2xs bg-slate-100 hover:bg-amber-50 text-slate-700 border border-slate-200';
                        }

                        if (row) {
                            if (status === 'absent') {
                                row.classList.add('opacity-50', 'bg-red-50/20');
                            } else {
                                row.classList.remove('opacity-50', 'bg-red-50/20');
                            }
                        }

                        if (codeBadge) {
                            if (data.code_letter) {
                                codeBadge.textContent = 'Code ' + data.code_letter;
                                codeBadge.className = 'inline-flex items-center px-3 py-1 rounded-xl text-xs font-black font-mono bg-purple-100 text-purple-900 border border-purple-300';
                            } else {
                                codeBadge.textContent = '- None -';
                                codeBadge.className = 'inline-flex items-center px-3 py-1 rounded-xl text-xs font-black font-mono bg-slate-100 text-slate-400 italic';
                            }
                        }

                        if (evalBadge && data.evaluation_status) {
                            evalBadge.textContent = data.evaluation_status;
                            if (data.evaluation_status === 'EVALUATED') {
                                evalBadge.className = 'inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-mono font-bold uppercase bg-purple-100 text-purple-900 border border-purple-200';
                            } else if (data.evaluation_status === 'EVALUATION_PENDING') {
                                evalBadge.className = 'inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-mono font-bold uppercase bg-blue-100 text-blue-900 border border-blue-200';
                            } else if (data.evaluation_status === 'NOT_ELIGIBLE') {
                                evalBadge.className = 'inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-mono font-bold uppercase bg-red-100 text-red-800 border border-red-200';
                            } else {
                                evalBadge.className = 'inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-mono font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200';
                            }
                        }

                        // Update counters from live server stats
                        if (data.stats) {
                            this.stats = data.stats;
                        }

                        this.showToast(data.message || 'Attendance saved.');
                    } catch (err) {
                        this.showToast(err.message || 'Error saving attendance.', true);
                    }
                },

                async updateCodeLetter(url, newCode, entryId = null) {
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ code_letter: newCode })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.showToast(data.message || 'കോഡ് ലെറ്റർ സേവ് ചെയ്തു.');
                            if (entryId) {
                                const badge = document.getElementById('code-badge-' + entryId);
                                if (badge) {
                                    badge.textContent = newCode ? ('Code ' + newCode) : '- None -';
                                }
                            }
                            return true;
                        } else {
                            this.showToast(data.message || 'Error updating code letter', true);
                            return false;
                        }
                    } catch (e) {
                        this.showToast('Error: ' + e.message, true);
                        return false;
                    }
                },

                async saveAllCodeLetters(url) {
                    if (this.savingAllCodes) return;
                    this.savingAllCodes = true;
                    try {
                        const inputs = document.querySelectorAll('.code-letter-input');
                        const codes = {};
                        inputs.forEach(input => {
                            const id = input.getAttribute('data-entry-id');
                            if (id) {
                                codes[id] = input.value;
                            }
                        });

                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ codes: codes })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.showToast(data.message || 'എല്ലാ കോഡ് ലെറ്ററുകളും സേവ് ചെയ്തു.');
                        } else {
                            this.showToast(data.message || 'Error saving codes', true);
                        }
                    } catch (e) {
                        this.showToast('Error: ' + e.message, true);
                    } finally {
                        this.savingAllCodes = false;
                    }
                }
            };
        }
    </script>
</body>
</html>
