<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Digital Call List | QUAF 09</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Malayalam:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 font-sora antialiased min-h-screen flex flex-col">

    <!-- Topbar -->
    <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-40 shadow-xs gap-4 flex-nowrap">
        <div class="flex items-center gap-3 sm:gap-4 shrink-0">
            <img src="{{ asset('images/dashboard-logo-dark.svg') }}" alt="QUAF 09" class="h-9 sm:h-10 w-auto shrink-0 object-contain">
            <div class="hidden xs:block shrink-0">
                <div class="flex items-center gap-2">
                    <span class="font-rockwell font-bold tracking-wider text-sm sm:text-base text-slate-900 whitespace-nowrap">DIGITAL CALL LIST</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping shrink-0"></span>
                </div>
                <span class="text-[10px] font-mono tracking-widest text-[#005c94] block uppercase font-bold whitespace-nowrap">Interactive Attendance & Code Locking</span>
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
            <a href="{{ route('announcer.stage') }}" class="px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-white/60 transition-all font-sora">
                Announcer Tab
            </a>
        </nav>

        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            @if(Auth::user()->role === 'admin' || Auth::user()->role === 'super_admin')
                <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-mono font-semibold whitespace-nowrap">
                    &larr; Admin Panel
                </a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-3 sm:px-3.5 py-2 rounded-xl text-xs font-mono font-bold bg-slate-100 text-slate-700 hover:text-red-700 hover:bg-red-50 border border-slate-200 transition-all whitespace-nowrap">
                    Sign Out
                </button>
            </form>
        </div>
    </header>

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

        <!-- Filter Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
            <form method="GET" action="{{ route('greenroom.call-list') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Zone / Category</label>
                    <select name="zone" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-slate-800 focus:outline-none focus:border-[#005c94]">
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
                <div class="md:col-span-5">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Program</label>
                    <select name="program" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-slate-800 focus:outline-none focus:border-[#005c94]">
                        <option value="">-- Choose Program --</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" {{ (string)$selectedProgramId === (string)$prog->id ? 'selected' : '' }}>
                                {{ $prog->name }} (ID: {{ $prog->code ?: $prog->id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <button type="submit" class="w-full bg-[#005c94] text-white py-2.5 px-4 rounded-xl text-xs font-bold hover:bg-[#004b78] transition flex items-center justify-center gap-2 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Open Call List</span>
                    </button>
                </div>
            </form>
        </div>

        @if($selectedProgram)
            <!-- Program Details & Lock Status Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-2xs space-y-5">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-slate-900 text-white">
                                ID: {{ $selectedProgram->code ?: '#'.$selectedProgram->id }}
                            </span>
                            <span class="text-xs text-slate-500 font-mono">
                                {{ $selectedProgram->category->name ?? $selectedProgram->eligibility ?? 'General' }} &bull; Stage: {{ $selectedProgram->stage->name ?? 'TBA' }}
                            </span>
                            @if($selectedProgram->is_call_list_locked)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-red-100 text-red-800 border border-red-200 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    LOCKED (ലോക്ക് ചെയ്തു)
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    OPEN / EDITABLE (തുറന്നിരിക്കുന്നു)
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

                    <!-- Lock / Unlock & Shuffle Actions -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Auto Shuffle Code Letters -->
                        <form method="POST" action="{{ route('greenroom.generate-codes', $selectedProgram->id) }}" onsubmit="return confirm('ഹാജരായവർക്ക് മാത്രം റാൻഡം ആയി കോഡ് ലെറ്ററുകൾ (A, B, C...) നൽകണോ?');">
                            @csrf
                            <button type="submit" @if($selectedProgram->is_call_list_locked) disabled @endif
                                    class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs flex items-center gap-1.5 shadow-2xs transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>നറുക്കെടുപ്പ് (Shuffle Codes A-Z)</span>
                            </button>
                        </form>

                        <!-- Lock Call List Toggle Button -->
                        <form method="POST" action="{{ route('greenroom.toggle-lock', $selectedProgram->id) }}" onsubmit="return confirm('{{ $selectedProgram->is_call_list_locked ? 'കോൾ ലിസ്റ്റ് അൺലോക്ക് ചെയ്യണോ?' : 'കോൾ ലിസ്റ്റ് ലോക്ക് ചെയ്യണോ? ലോക്ക് ചെയ്താൽ ഹാജർ നിലയും കോഡ് ലെറ്ററുകളും മാറ്റാൻ കഴിയില്ല. ജഡ്ജ് പാനലിൽ ഹാജരായവർ മാത്രമേ ലഭ്യമാകൂ.' }}');">
                            @csrf
                            @if($selectedProgram->is_call_list_locked)
                                <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs flex items-center gap-1.5 shadow-2xs transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                    <span>കോൾ ലിസ്റ്റ് അൺലോക്ക് ചെയ്യുക</span>
                                </button>
                            @else
                                <button type="submit" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-2xs transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span>കോൾ ലിസ്റ്റ് ലോക്ക് ചെയ്യുക (Lock)</span>
                                </button>
                            @endif
                        </form>

                        <!-- Print Call List Button -->
                        <a href="{{ route('admin.forms.call-list', ['program' => $selectedProgram->id, 'print' => 1]) }}" target="_blank"
                           class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs flex items-center gap-1.5 transition-colors">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>പ്രിന്റ് ചെയ്യുക (Print)</span>
                        </a>

                        <!-- Go to Announcer Tab -->
                        <a href="{{ route('announcer.stage', ['stage_id' => $selectedProgram->stage_id]) }}" target="_blank"
                           class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-1.5 transition-colors">
                            <span>അനൗൺസർ ടാബ് &rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Call List Quick Stat Cards -->
                @php
                    $totalCount = $entries->count();
                    $presentCount = $entries->where('attendance_status', 'present')->count();
                    $absentCount = $entries->where('attendance_status', 'absent')->count();
                    $waitingCount = $entries->where('attendance_status', '!=', 'present')->where('attendance_status', '!=', 'absent')->count();
                    $codedCount = $entries->whereNotNull('code_letter')->count();
                @endphp
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 font-mono block">ആകെ രജിസ്ട്രേഷൻ</span>
                        <span class="text-xl font-bold text-slate-900 mt-1 block">{{ $totalCount }} പേർ</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900">
                        <span class="font-mono block text-emerald-700">ഹാജരുണ്ട് (Present)</span>
                        <span class="text-xl font-bold mt-1 block">{{ $presentCount }} പേർ</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-red-50 border border-red-200 text-red-900">
                        <span class="font-mono block text-red-700">ഹാജരില്ല (Absent)</span>
                        <span class="text-xl font-bold mt-1 block">{{ $absentCount }} പേർ</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900">
                        <span class="font-mono block text-amber-700">കാത്തിരിക്കുന്നു (Waiting)</span>
                        <span class="text-xl font-bold mt-1 block">{{ $waitingCount }} പേർ</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-purple-50 border border-purple-200 text-purple-900">
                        <span class="font-mono block text-purple-700">കോഡ് നൽകി (Coded)</span>
                        <span class="text-xl font-bold mt-1 block">{{ $codedCount }} പേർ</span>
                    </div>
                </div>

                @if($selectedProgram->is_call_list_locked)
                    <div class="p-3.5 rounded-2xl bg-red-50 border border-red-200 text-red-900 text-xs font-mono flex items-center justify-between">
                        <span>ശ്രദ്ധിക്കുക: ഈ കോൾ ലിസ്റ്റ് ലോക്ക് ചെയ്തിരിക്കുന്നു. ഹാജരായ {{ $presentCount }} പേർ മാത്രമേ മൂല്യനിർണ്ണയത്തിനായി ജഡ്ജ് പാനലിൽ ദൃശ്യമാകൂ.</span>
                        <span class="font-bold uppercase tracking-wider text-[10px] bg-red-200 text-red-900 px-2 py-0.5 rounded">FROZEN</span>
                    </div>
                @endif
            </div>

            <!-- Interactive Digital Table -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 font-sora">
                        മത്സരാർത്ഥികളുടെ ഡിജിറ്റൽ കോൾ ലിസ്റ്റ്
                    </h3>
                    <span class="text-[11px] text-slate-500 font-mono">
                        ഹാജർ രേഖപ്പെടുത്തിയ ശേഷം "നറുക്കെടുപ്പ്" നടത്തി "ലോക്ക്" ചെയ്യുക.
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs font-sora">
                        <thead>
                            <tr class="bg-slate-100/75 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                                <th class="px-4 py-3 text-center w-12">No</th>
                                <th class="px-4 py-3 w-28">Chest #</th>
                                <th class="px-4 py-3">Student Name</th>
                                <th class="px-4 py-3">Team / Group</th>
                                <th class="px-4 py-3 text-center w-36">Code Letter</th>
                                <th class="px-4 py-3 text-center w-64">Attendance Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($entries as $index => $entry)
                                <tr class="hover:bg-slate-50/75 transition-colors {{ $entry->attendance_status === 'absent' ? 'opacity-50 bg-red-50/20' : '' }}">
                                    <td class="px-4 py-3 text-center font-mono text-slate-400">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-4 py-3 font-mono font-bold text-slate-900">
                                        #{{ $entry->chest_number }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-slate-900">{{ $entry->student?->name ?? 'Participant' }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $entry->student?->class ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-700">
                                        {{ $entry->student?->group?->name ?? $entry->group?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($entry->code_letter)
                                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-black bg-purple-100 text-purple-900 border border-purple-300 font-mono">
                                                Code {{ $entry->code_letter }}
                                            </span>
                                        @else
                                            <span class="text-[11px] text-slate-400 font-mono italic">- None -</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($selectedProgram->is_call_list_locked)
                                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold font-mono {{ $entry->attendance_status === 'present' ? 'bg-emerald-100 text-emerald-800' : ($entry->attendance_status === 'absent' ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-700') }}">
                                                {{ strtoupper($entry->attendance_status ?? 'waiting') }}
                                            </span>
                                        @else
                                            <!-- Attendance Toggle Form -->
                                            <form method="POST" action="{{ route('greenroom.mark-attendance', $entry->id) }}" class="inline-flex items-center gap-1.5">
                                                @csrf
                                                <button type="submit" name="status" value="present"
                                                        class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition-all {{ $entry->attendance_status === 'present' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-emerald-50 text-slate-700 border border-slate-200' }}">
                                                    Present
                                                </button>
                                                <button type="submit" name="status" value="absent"
                                                        class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition-all {{ $entry->attendance_status === 'absent' ? 'bg-red-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-red-50 text-slate-700 border border-slate-200' }}">
                                                    Absent
                                                </button>
                                                <button type="submit" name="status" value="waiting"
                                                        class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition-all {{ $entry->attendance_status === 'waiting' || empty($entry->attendance_status) ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 hover:bg-amber-50 text-slate-700 border border-slate-200' }}">
                                                    Waiting
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400 text-xs">
                                        ഈ പ്രോഗ്രാമിൽ വെരിഫൈ ചെയ്ത മത്സരാർത്ഥികളില്ല.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="p-12 bg-white rounded-3xl border border-slate-200 text-center text-xs text-slate-500">
                മുകളിലെ ഡ്രോപ്പ് ഡൗണിൽ നിന്ന് പ്രോഗ്രാം സെലക്ട് ചെയ്യുക.
            </div>
        @endif

    </main>

</body>
</html>
