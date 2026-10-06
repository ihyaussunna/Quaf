<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Welcome to Green Room Desk | QUAF 9.0</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts (Multilingual: Anek Malayalam, JetBrains Mono, Sora, Amiri, Noto Nastaliq Urdu) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Anek+Malayalam:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&family=Noto+Nastaliq+Urdu:wght@400;700&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 font-sora antialiased min-h-screen flex flex-col"
      x-data="{
          showRulesModal: false,
          showCriteriaModal: false,
          modalProgramName: '',
          modalProgramCode: '',
          modalRules: '',
          modalCriteria: [],
          searchQuery: '',
          attendanceFilter: 'all',
          loadingEntryId: null,

          openRules(name, code, rules) {
              this.modalProgramName = name;
              this.modalProgramCode = code;
              this.modalRules = rules || 'ഈ പ്രോഗ്രാമിന് പ്രത്യേകം നിയമാവലികൾ രേഖപ്പെടുത്തിയിട്ടില്ല (No specific guidelines recorded).';
              this.showRulesModal = true;
          },

          openCriteria(name, code, criteria) {
              this.modalProgramName = name;
              this.modalProgramCode = code;
              this.modalCriteria = criteria || [];
              this.showCriteriaModal = true;
          },

          async submitAttendance(url, entryId, newStatus) {
              this.loadingEntryId = entryId;
              try {
                  const res = await fetch(url, {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'Accept': 'application/json',
                          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                      },
                      body: JSON.stringify({ status: newStatus })
                  });
                  const data = await res.json();
                  if (res.ok && data.success) {
                      window.location.reload();
                  } else {
                      alert(data.message || 'ഹാജർ രേഖപ്പെടുത്താൻ സാധിച്ചില്ല. ദയവായി വീണ്ടും ശ്രമിക്കുക.');
                  }
              } catch (err) {
                  alert('Error updating attendance: ' + err.message);
              } finally {
                  this.loadingEntryId = null;
              }
          },

          toastMessage: '',
          showToast: false,
          toastTimeout: null,
          savingAllCodes: false,

          triggerToast(msg) {
              this.toastMessage = msg;
              this.showToast = true;
              if (this.toastTimeout) clearTimeout(this.toastTimeout);
              this.toastTimeout = setTimeout(() => { this.showToast = false; }, 3500);
          },

          async updateCodeLetter(url, newCode, entryId) {
              try {
                  const res = await fetch(url, {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'Accept': 'application/json',
                          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                      },
                      body: JSON.stringify({ code_letter: newCode })
                  });
                  const data = await res.json();
                  if (!res.ok || !data.success) {
                      alert(data.message || 'കോഡ് ലെറ്റർ സേവ് ചെയ്യാൻ സാധിച്ചില്ല.');
                      return false;
                  }
                  this.triggerToast(data.message || 'കോഡ് ലെറ്റർ സേവ് ചെയ്തു.');
                  return true;
              } catch (err) {
                  alert('Error updating code letter: ' + err.message);
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

                  const res = await fetch(url, {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'Accept': 'application/json',
                          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                      },
                      body: JSON.stringify({ codes: codes })
                  });
                  const data = await res.json();
                  if (!res.ok || !data.success) {
                      alert(data.message || 'കോഡുകൾ സേവ് ചെയ്യാൻ സാധിച്ചില്ല.');
                  } else {
                      this.triggerToast(data.message || 'എല്ലാ കോഡ് ലെറ്ററുകളും സേവ് ചെയ്തു.');
                  }
              } catch (err) {
                  alert('Error saving codes: ' + err.message);
              } finally {
                  this.savingAllCodes = false;
              }
          }
      }">

    <!-- Floating Live Toast Notification -->
    <div x-show="showToast"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 max-w-sm rounded-2xl px-4 py-3 shadow-xl font-mono text-xs flex items-center gap-3 border bg-slate-900 text-emerald-300 border-slate-700"
         style="display: none;">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
        <span x-text="toastMessage"></span>
    </div>

    <!-- Unified Green Room Topbar -->
    <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 md:px-8 sticky top-0 z-40 shadow-xs gap-4">
        <div class="flex items-center gap-3 sm:gap-4 shrink-0">
            <a href="{{ route('greenroom.index') }}" class="flex items-center shrink-0">
                <img src="{{ asset('images/dashboard-logo-dark.svg') }}" alt="QUAF 9.0" class="h-9 sm:h-10 w-auto object-contain max-h-10" style="height: 38px; width: auto; max-width: 125px; object-fit: contain;">
            </a>
            <div class="border-l border-slate-200 pl-3 sm:pl-4">
                <div class="flex items-center gap-2">
                    <h1 class="font-sora font-black tracking-tight text-base sm:text-lg text-slate-900 leading-none">
                        Welcome to Green Room Desk
                    </h1>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping shrink-0"></span>
                </div>
            </div>
        </div>

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
                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-mono font-bold bg-slate-100 text-slate-700 hover:text-red-700 hover:bg-red-50 border border-slate-200 transition-all whitespace-nowrap cursor-pointer">
                    Sign Out
                </button>
            </form>
        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 mt-4">
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-mono flex items-center justify-between shadow-xs">
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 mt-4">
            <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-900 text-xs font-mono flex items-center justify-between shadow-xs">
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 md:p-8 space-y-6">

        <!-- Stage Switcher Tabs: Stages 1 to 8 -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-mono uppercase font-bold text-slate-500 tracking-wider">Festival Stage Venues (Stages 01 - 08)</span>
                <span class="text-xs font-mono text-slate-500">Selected: <strong>{{ $stage?->name }}</strong></span>
            </div>
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                @foreach($stages as $st)
                    @php
                        $isActive = ((int) $selectedStageId === (int) $st->id);
                        $hasActiveSlot = $st->schedules()->where('start_time', '<=', now()->copy()->addMinutes(10))->where('end_time', '>=', now())->exists();
                    @endphp
                    <a href="{{ route('greenroom.index', ['stage_id' => $st->id]) }}"
                       class="px-4 sm:px-5 py-3 rounded-2xl font-mono text-xs font-bold transition-all flex-shrink-0 flex items-center gap-2.5 border {{ $isActive ? 'bg-[#be1e2d] text-white border-[#be1e2d] shadow-md scale-102' : 'bg-white text-slate-700 hover:text-slate-900 hover:bg-slate-50 border-slate-200 shadow-2xs' }}">
                        <span class="w-2.5 h-2.5 rounded-full {{ $isActive ? 'bg-white' : ($hasActiveSlot ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400') }}"></span>
                        <span>{{ $st->name }}</span>
                        @if($hasActiveSlot && ! $isActive)
                            <span class="px-1.5 py-0.5 rounded text-[9px] bg-emerald-100 text-emerald-800 font-bold uppercase">LIVE</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        @if($stage)
            <!-- Stage Schedule Lineup Ribbon -->
            @if($stageSchedules->isNotEmpty())
                <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-2.5 overflow-x-auto scrollbar-none text-xs font-mono">
                    <span class="text-slate-500 font-bold uppercase text-[10px] shrink-0 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-[#005c94]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Stage Schedule:</span>
                    </span>
                    @foreach($stageSchedules as $sch)
                        @php
                            $isCurrentSlot = ($currentProgram && $sch->program_id === $currentProgram->id);
                            $isSelectedSlot = ($activeProgram && $sch->program_id === $activeProgram->id);
                            $schStart = \Carbon\Carbon::parse($sch->getRawOriginal('start_time') ?? $sch->start_time, 'Asia/Kolkata');
                            $schZoneName = $sch->program?->zone?->name ?? $sch->program?->eligibility;
                            $schZoneColor = $sch->program?->zone?->color_hex ?? '#005c94';
                        @endphp
                        <a href="{{ route('greenroom.index', ['stage_id' => $stage->id, 'program_id' => $sch->program_id]) }}"
                           class="px-3 py-1.5 rounded-xl shrink-0 transition flex items-center gap-2 border {{ $isSelectedSlot ? 'bg-slate-900 text-white font-bold border-slate-900 shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                            <span class="text-[10px] font-bold {{ $isSelectedSlot ? 'text-amber-300' : 'text-slate-500' }}">{{ $schStart->format('h:i A') }}</span>
                            <span>{{ Str::limit($sch->program?->name, 22) }}</span>
                            @if($schZoneName)
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase" style="background-color: {{ $schZoneColor }}20; color: {{ $isSelectedSlot ? '#f8fafc' : $schZoneColor }};">
                                    {{ $schZoneName }}
                                </span>
                            @endif
                            @if($isCurrentSlot)
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- Selected Stage Overview: NOW ON STAGE & UP NEXT IN LINE -->
            <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-7 grid grid-cols-1 lg:grid-cols-3 gap-6 shadow-xs">
                
                <!-- 1. Now On Stage Card -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-slate-500 uppercase tracking-widest block font-bold">Now On Stage</span>
                        @if($currentProgram)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>LIVE NOW</span>
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200">
                                INTERMISSION
                            </span>
                        @endif
                    </div>

                    @if($currentProgram)
                        <div>
                            <div class="flex items-center gap-2 flex-wrap mb-1.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold text-white shadow-2xs"
                                      style="background-color: {{ $currentProgram->zone?->color_hex ?? '#be1e2d' }};">
                                    {{ $currentProgram->zone?->name ?? $currentProgram->eligibility ?? 'General' }}
                                </span>
                                @if($currentProgram->zone?->sub_text)
                                    <span class="text-[10px] font-mono text-slate-500">({{ $currentProgram->zone->sub_text }})</span>
                                @endif
                            </div>
                            <h3 class="text-xl sm:text-2xl font-sora font-black text-slate-900 tracking-tight">
                                {{ $currentProgram->name }}
                            </h3>
                            @if($currentProgram->malayalam_name)
                                <p class="text-xs font-ml text-slate-600 mt-0.5">{{ $currentProgram->malayalam_name }}</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2 text-xs font-mono text-slate-600">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 font-bold text-slate-800">ID: {{ $currentProgram->code }}</span>
                            <span>&bull;</span>
                            <span class="font-bold text-slate-700">Zone: {{ $currentProgram->zone?->name ?? $currentProgram->eligibility ?? 'All' }}</span>
                            <span>&bull;</span>
                            <span>{{ $currentProgram->category->name ?? 'Category' }}</span>
                            <span>&bull;</span>
                            <span class="text-emerald-700 font-bold">
                                {{ $activeSchedule?->start_time?->format('h:i A') ?? $currentProgram->scheduled_time?->format('h:i A') }}
                                @if($activeSchedule?->end_time) – {{ $activeSchedule->end_time->format('h:i A') }} @endif
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200 font-bold text-[11px]">
                                {{ $currentProgram->duration_minutes ?: 30 }} Mins
                            </span>
                        </div>

                        <!-- Niyamavali & Criteria Modals Buttons -->
                        <div class="flex items-center gap-2 pt-1">
                            <button type="button"
                                    @click="openRules('{{ addslashes($currentProgram->name) }}', '{{ $currentProgram->code }}', '{{ addslashes($currentProgram->rules ?? '') }}')"
                                    class="px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-900 border border-purple-200 text-xs font-mono font-bold flex items-center gap-1.5 transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>നിയമാവലി (Rules)</span>
                            </button>

                            <button type="button"
                                    @click="openCriteria('{{ addslashes($currentProgram->name) }}', '{{ $currentProgram->code }}', {{ json_encode($currentProgram->scoringCriteria) }})"
                                    class="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-900 border border-blue-200 text-xs font-mono font-bold flex items-center gap-1.5 transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <span>മാനദണ്ഡങ്ങൾ (Criteria)</span>
                            </button>
                        </div>
                    @else
                        <div class="py-4">
                            <h3 class="text-lg font-sora font-semibold text-slate-500">Stage on Intermission</h3>
                            <p class="text-xs font-mono text-slate-400 mt-1">ഈ സ്റ്റേജിൽ ഇപ്പോൾ ലൈവ് പ്രോഗ്രാം നടന്നു കൊണ്ടിരിക്കുന്നില്ല. അടുത്ത ഷെഡ്യൂൾ ഉടൻ ആരംഭിക്കും.</p>
                        </div>
                    @endif
                </div>

                <!-- 2. Up Next In Line Card -->
                <div class="space-y-3 lg:border-l lg:border-slate-200 lg:pl-6">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-slate-500 uppercase tracking-widest block font-bold">Up Next in Line</span>
                        @if($nextProgram)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-blue-100 text-blue-800 border border-blue-200">
                                SCHEDULED SLOT
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-slate-100 text-slate-500 border border-slate-200">
                                NONE QUEUED
                            </span>
                        @endif
                    </div>

                    @if($nextProgram)
                        <div>
                            <div class="flex items-center gap-2 flex-wrap mb-1.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold text-white shadow-2xs"
                                      style="background-color: {{ $nextProgram->zone?->color_hex ?? '#005c94' }};">
                                    {{ $nextProgram->zone?->name ?? $nextProgram->eligibility ?? 'General' }}
                                </span>
                                @if($nextProgram->zone?->sub_text)
                                    <span class="text-[10px] font-mono text-slate-500">({{ $nextProgram->zone->sub_text }})</span>
                                @endif
                            </div>
                            <h3 class="text-xl sm:text-2xl font-sora font-bold text-slate-900 tracking-tight">
                                {{ $nextProgram->name }}
                            </h3>
                            @if($nextProgram->malayalam_name)
                                <p class="text-xs font-ml text-slate-600 mt-0.5">{{ $nextProgram->malayalam_name }}</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2 text-xs font-mono text-slate-600">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 font-bold text-slate-800">ID: {{ $nextProgram->code }}</span>
                            <span>&bull;</span>
                            <span class="font-bold text-slate-700">Zone: {{ $nextProgram->zone?->name ?? $nextProgram->eligibility ?? 'All' }}</span>
                            <span>&bull;</span>
                            <span>{{ $nextProgram->category->name ?? 'Category' }}</span>
                            <span>&bull;</span>
                            <span class="text-blue-800 font-bold">
                                {{ $nextSchedule?->start_time?->format('h:i A') ?? $nextProgram->scheduled_time?->format('h:i A') ?? 'TBA' }}
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200 font-bold text-[11px]">
                                {{ $nextProgram->duration_minutes ?: 30 }} Mins
                            </span>
                        </div>

                        <!-- Niyamavali & Criteria Modals Buttons -->
                        <div class="flex items-center gap-2 pt-1">
                            <button type="button"
                                    @click="openRules('{{ addslashes($nextProgram->name) }}', '{{ $nextProgram->code }}', '{{ addslashes($nextProgram->rules ?? '') }}')"
                                    class="px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-900 border border-purple-200 text-xs font-mono font-bold flex items-center gap-1.5 transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>നിയമാവലി (Rules)</span>
                            </button>

                            <button type="button"
                                    @click="openCriteria('{{ addslashes($nextProgram->name) }}', '{{ $nextProgram->code }}', {{ json_encode($nextProgram->scoringCriteria) }})"
                                    class="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-900 border border-blue-200 text-xs font-mono font-bold flex items-center gap-1.5 transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <span>മാനദണ്ഡങ്ങൾ (Criteria)</span>
                            </button>
                        </div>
                    @else
                        <div class="py-4">
                            <h3 class="text-lg font-sora font-semibold text-slate-500">None Queued</h3>
                            <p class="text-xs font-mono text-slate-400 mt-1">ഈ സ്റ്റേജിൽ അടുത്ത പ്രോഗ്രാമുകൾ ഷെഡ്യൂൾ ചെയ്തിട്ടില്ല.</p>
                        </div>
                    @endif
                </div>

                <!-- 3. Stage Controls -->
                <div class="space-y-3 lg:border-l lg:border-slate-200 lg:pl-6 flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] font-mono text-slate-500 uppercase tracking-widest block font-bold">Stage Controls</span>
                        <div class="text-xs font-mono text-slate-600 mt-1">
                            Current Stage: <strong class="text-slate-900">{{ $stage->name }}</strong>
                        </div>
                    </div>

                    @if($activeProgram)
                        @php
                            $shuffleCount = (int) ($activeProgram->shuffle_count ?? 0);
                            $canShuffle = ($shuffleCount < 2) || $isAdmin;
                        @endphp
                        <div class="flex flex-col gap-2.5">
                            <!-- Shuffle Code Letters -->
                            <form method="POST" action="{{ route('greenroom.generate-codes', $activeProgram->id) }}"
                                  onsubmit="return confirm('ഹാജരായവർക്ക് മാത്രം റാൻഡം ആയി രഹസ്യ കോഡ് ലെറ്ററുകൾ (A, B, C...) നൽകണോ? (അവസരം: {{ min(2, $shuffleCount + 1) }}/2)');">
                                @csrf
                                <button type="submit" @if(! $isEditable || ! $canShuffle) disabled @endif
                                        class="w-full px-4 py-3 {{ $canShuffle ? 'bg-[#be1e2d] hover:bg-[#a01825] cursor-pointer' : 'bg-slate-400 cursor-not-allowed' }} disabled:opacity-50 text-white font-mono font-bold text-xs uppercase rounded-2xl shadow-sm flex items-center justify-center gap-2 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    @if($canShuffle)
                                        <span>Shuffle Code Letters (Chance {{ $shuffleCount + 1 }}/2)</span>
                                    @else
                                        <span>Shuffle Limit Reached (2/2 Used)</span>
                                    @endif
                                </button>
                            </form>

                            @if($isEditable)
                                <!-- Batch Save All Codes Button -->
                                <button type="button"
                                        @click="saveAllCodeLetters('{{ route('greenroom.batch-update-code-letters', $activeProgram->id) }}')"
                                        :disabled="savingAllCodes"
                                        class="w-full px-4 py-2.5 bg-[#005c94] hover:bg-[#004875] text-white font-mono font-bold text-xs uppercase rounded-2xl shadow-sm flex items-center justify-center gap-2 transition-all cursor-pointer active:scale-95 disabled:opacity-50">
                                    <template x-if="savingAllCodes">
                                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    </template>
                                    <template x-if="!savingAllCodes">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    </template>
                                    <span x-text="savingAllCodes ? 'Saving Codes...' : 'Save All Codes (കോഡുകൾ സേവ് ചെയ്യുക)'"></span>
                                </button>
                            @endif

                            <p class="text-[11px] font-mono text-slate-500">
                                @if($canShuffle)
                                    പരമാവധി 2 തവണ മാത്രമേ നറുക്കെടുപ്പ് അനുവദിക്കൂ (ഇതുവരെ ഉപയോഗിച്ചത്: {{ $shuffleCount }}/2). ആവശ്യമെങ്കിൽ താഴെ ടേബിളിൽ മാനുവലായും കോഡ് നൽകാം.
                                @else
                                    പരമാവധി 2 നറുക്കെടുപ്പ് അവസരങ്ങളും പൂർത്തിയായി. ആവശ്യമെങ്കിൽ താഴെ ടേബിളിൽ മാനുവലായി കോഡ് നൽകാവുന്നതാണ്.
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-mono text-slate-500 text-center">
                            Awaiting schedule slot to activate green room stage controls.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Call List & Real-Time Attendance Center -->
            <div class="space-y-4">
                
                @if($activeProgram)
                    <!-- Timing Window & Lock Alert Banner -->
                    @if($windowState['state'] === 'open')
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs font-mono flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                                <div>
                                    <strong class="font-bold">ഹാജർ പട്ടിക തുറന്നിരിക്കുന്നു (ATTENDANCE OPEN):</strong>
                                    <span>പ്രോഗ്രാം കഴിയുന്നത് വരെ ഹാജർ രേഖപ്പെടുത്താം.</span>
                                </div>
                            </div>
                            @if($windowState['scheduled_start'])
                                <div class="shrink-0 flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 font-bold border border-emerald-300">
                                        ഷെഡ്യൂൾ: {{ $windowState['scheduled_start']?->format('h:i A') }} @if($windowState['closes_at']) – {{ $windowState['closes_at']->format('h:i A') }} @endif
                                    </span>
                                </div>
                            @endif
                        </div>
                    @elseif($windowState['state'] === 'auto_locked_ended')
                        <div class="p-4 rounded-2xl bg-red-50 border border-red-300 text-red-900 text-xs font-mono flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <div>
                                    <strong class="font-bold">പ്രോഗ്രാം പൂർത്തിയായി (PROGRAM COMPLETED):</strong>
                                    <span>പ്രോഗ്രാം പൂർത്തിയായതിനാൽ കോൾ ലിസ്റ്റ് പൂർണ്ണമായി ലോക്ക് ചെയ്യപ്പെട്ടു.</span>
                                </div>
                            </div>
                        </div>
                    @elseif($windowState['state'] === 'locked_by_admin')
                        <div class="p-4 rounded-2xl bg-red-50 border border-red-300 text-red-900 text-xs font-mono flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <div>
                                    <strong class="font-bold">അഡ്മിൻ ലോക്ക് ചെയ്തു (LOCKED BY ADMIN):</strong>
                                    <span>{{ $windowState['message'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Header & Summary Counters -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                            <div>
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <h2 class="text-xl font-sora font-black text-slate-900">
                                        Digital Call List & Attendance: {{ $activeProgram->name }}
                                    </h2>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold text-white shadow-2xs"
                                          style="background-color: {{ $activeProgram->zone?->color_hex ?? '#005c94' }};">
                                        {{ $activeProgram->zone?->name ?? $activeProgram->eligibility ?? 'General' }}
                                    </span>
                                </div>
                                <p class="text-xs font-mono text-slate-500 mt-1">
                                    Code: <strong>{{ $activeProgram->code }}</strong> &bull; Zone: <strong>{{ $activeProgram->zone?->name ?? $activeProgram->eligibility ?? 'All' }}</strong> &bull; Stage: <strong>{{ $stage->name }}</strong> &bull; Total Registered: <strong>{{ $stats['total'] }} Students</strong>
                                </p>
                            </div>
                        </div>

                        <!-- 4 Summary Counters (On Stage & Called Removed) -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                                <span class="text-slate-500 font-mono block text-[11px]">Total Call List</span>
                                <span class="text-xl font-black text-slate-900 mt-1 block font-mono">{{ $stats['total'] }}</span>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900">
                                <span class="font-mono block text-emerald-700 text-[11px]">Present (ഹാജർ)</span>
                                <span class="text-xl font-black mt-1 block font-mono">{{ $stats['present'] }}</span>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-red-50 border border-red-200 text-red-900">
                                <span class="font-mono block text-red-700 text-[11px]">Absent (ഹാജരില്ല)</span>
                                <span class="text-xl font-black mt-1 block font-mono">{{ $stats['absent'] }}</span>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900">
                                <span class="font-mono block text-amber-700 text-[11px]">Waiting (കാത്തിരിപ്പ്)</span>
                                <span class="text-xl font-black mt-1 block font-mono">{{ $stats['waiting'] }}</span>
                            </div>
                        </div>

                        <!-- Live Search & Filter Bar -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                            <div class="relative flex-1 max-w-md">
                                <input type="text"
                                       x-model="searchQuery"
                                       placeholder="Search chest #, student name, group, code..."
                                       class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono focus:bg-white focus:border-[#be1e2d] focus:outline-none transition">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>

                            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-mono">
                                <button type="button" @click="attendanceFilter = 'all'"
                                        :class="attendanceFilter === 'all' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                                    All ({{ $stats['total'] }})
                                </button>
                                <button type="button" @click="attendanceFilter = 'present'"
                                        :class="attendanceFilter === 'present' ? 'bg-emerald-600 text-white font-bold' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100'"
                                        class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                                    Present ({{ $stats['present'] }})
                                </button>
                                <button type="button" @click="attendanceFilter = 'absent'"
                                        :class="attendanceFilter === 'absent' ? 'bg-red-600 text-white font-bold' : 'bg-red-50 text-red-800 hover:bg-red-100'"
                                        class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                                    Absent ({{ $stats['absent'] }})
                                </button>
                                <button type="button" @click="attendanceFilter = 'waiting'"
                                        :class="attendanceFilter === 'waiting' ? 'bg-amber-600 text-white font-bold' : 'bg-amber-50 text-amber-800 hover:bg-amber-100'"
                                        class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                                    Waiting ({{ $stats['waiting'] }})
                                </button>
                            </div>
                        </div>

                        <!-- Student Call Board & Attendance Table -->
                        <div class="overflow-x-auto border border-slate-200 rounded-2xl shadow-2xs">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-mono text-[11px] uppercase tracking-wider">
                                        <th class="py-3 px-4 w-12 text-center">#</th>
                                        <th class="py-3 px-4 w-36 sm:w-44 text-center">Code Letter</th>
                                        <th class="py-3 px-4 w-24">Chest #</th>
                                        <th class="py-3 px-4">Student Name</th>
                                        <th class="py-3 px-4">Group / Team</th>
                                        <th class="py-3 px-4 text-center">Attendance Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse($entries as $index => $entry)
                                        <tr class="hover:bg-slate-50/70 transition-colors"
                                            x-show="(attendanceFilter === 'all' || attendanceFilter === '{{ $entry->attendance_status ?? 'waiting' }}') && (!searchQuery || '{{ strtolower($entry->chest_number . ' ' . ($entry->student?->name ?? '') . ' ' . ($entry->group?->name ?? '') . ' ' . ($entry->code_letter ?? '')) }}'.includes(searchQuery.toLowerCase()))">
                                            
                                            <!-- Index -->
                                            <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">
                                                {{ $index + 1 }}
                                            </td>

                                            <!-- Code Letter (Manual Input / View) -->
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
                                                    @if($entry->code_letter)
                                                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-xl bg-purple-100 text-purple-900 border border-purple-200 font-mono font-black text-xs shadow-2xs">
                                                            {{ $entry->code_letter }}
                                                        </span>
                                                    @else
                                                        <span class="text-slate-400 font-mono text-xs">&mdash;</span>
                                                    @endif
                                                @endif
                                            </td>

                                            <!-- Chest # -->
                                            <td class="py-3.5 px-4">
                                                <span class="font-mono font-bold text-slate-900 bg-slate-100 px-2.5 py-1 rounded-lg">
                                                    #{{ $entry->chest_number }}
                                                </span>
                                            </td>

                                            <!-- Student Name -->
                                            <td class="py-3.5 px-4">
                                                <div class="font-sora font-bold text-slate-900 text-sm">
                                                    {{ $entry->student?->name ?? 'Enrolled Participant' }}
                                                </div>
                                                <div class="text-[10px] font-mono text-slate-400 mt-0.5">
                                                    {{ $entry->student?->student_id }}
                                                </div>
                                            </td>

                                            <!-- Group / Team -->
                                            <td class="py-3.5 px-4">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-mono font-semibold"
                                                      style="background-color: {{ $entry->group?->color_hex ?? '#64748b' }}15; color: {{ $entry->group?->color_hex ?? '#64748b' }}; border: 1px solid {{ $entry->group?->color_hex ?? '#64748b' }}30;">
                                                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $entry->group?->color_hex ?? '#64748b' }};"></span>
                                                    <span>{{ $entry->group?->name ?? 'Team' }}</span>
                                                </span>
                                            </td>

                                            <!-- Attendance Action -->
                                            <td class="py-3.5 px-4 text-center">
                                                @if($isEditable)
                                                    <div class="inline-flex items-center gap-1.5">
                                                        <button type="button"
                                                                @click="submitAttendance('{{ route('greenroom.mark-attendance', $entry) }}', {{ $entry->id }}, 'present')"
                                                                :disabled="loadingEntryId === {{ $entry->id }}"
                                                                class="px-3.5 py-2 min-h-[40px] rounded-xl font-mono text-xs font-bold transition-all cursor-pointer flex items-center gap-1 active:scale-95 {{ $entry->attendance_status === 'present' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200' }}">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                            <span>PRESENT</span>
                                                        </button>

                                                        <button type="button"
                                                                @click="submitAttendance('{{ route('greenroom.mark-attendance', $entry) }}', {{ $entry->id }}, 'absent')"
                                                                :disabled="loadingEntryId === {{ $entry->id }}"
                                                                class="px-3 py-2 min-h-[40px] rounded-xl font-mono text-xs font-bold transition-all cursor-pointer flex items-center gap-1 active:scale-95 {{ $entry->attendance_status === 'absent' ? 'bg-red-600 text-white shadow-xs' : 'bg-red-50 hover:bg-red-100 text-red-800 border border-red-200' }}">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                            <span>ABSENT</span>
                                                        </button>
                                                    </div>
                                                @else
                                                    <div class="inline-flex items-center gap-1.5 px-3 py-2 min-h-[40px] rounded-xl bg-slate-100 text-slate-500 font-mono text-xs font-bold">
                                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                        <span>{{ strtoupper($entry->attendance_status ?: 'WAITING') }}</span>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-12 text-center text-slate-500 font-mono text-xs">
                                                ഈ പ്രോഗ്രാമിൽ ഇതുവരെ എൻട്രികൾ ലഭ്യമായിട്ടില്ല. (No registered entries found).
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <!-- No Program Scheduled Alert for this stage -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center space-y-3 shadow-xs">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-500 mx-auto flex items-center justify-center font-bold">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-sora font-bold text-slate-800">
                            ഈ സ്റ്റേജിൽ ഇപ്പോൾ ലൈവ് പ്രോഗ്രാമുകൾ ഒന്നും ഷെഡ്യൂൾ ചെയ്തിട്ടില്ല
                        </h3>
                        <p class="text-xs font-mono text-slate-500 max-w-md mx-auto">
                            ഷെഡ്യൂൾ ചെയ്ത പ്രോഗ്രാമുകൾ മാത്രമേ ലൈവ് വിൻഡോയിൽ (തുടങ്ങുന്നതിന് 10 മിനിറ്റ് മുമ്പ്) കോൾ ലിസ്റ്റിൽ ലഭ്യമാകൂ. മറ്റ് സ്റ്റേജുകൾ പരിശോധിക്കുകയോ ഷെഡ്യൂൾ പരിശോധിക്കുകയോ ചെയ്യുക.
                        </p>
                    </div>
                @endif
            </div>
        @endif

    </main>

    <!-- Niyamavali (Rules) Modal Popup -->
    <div x-show="showRulesModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;"
         @click.self="showRulesModal = false">
        
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 space-y-5 shadow-2xl border border-slate-200 relative transform transition-all"
             @click.stop>
            
            <div class="flex items-start justify-between border-b border-slate-100 pb-4">
                <div>
                    <span class="text-[10px] font-mono uppercase tracking-widest text-[#be1e2d] font-bold block">Official Guidelines</span>
                    <h3 class="text-xl font-sora font-bold text-slate-900 mt-0.5" x-text="modalProgramName"></h3>
                    <p class="text-xs font-mono text-slate-500">Program ID: <span x-text="modalProgramCode"></span></p>
                </div>
                <button type="button" @click="showRulesModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center cursor-pointer transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="max-h-[60vh] overflow-y-auto pr-2 space-y-3 font-mono text-xs text-slate-700 leading-relaxed whitespace-pre-wrap"
                 x-text="modalRules">
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[11px] font-mono text-slate-400">QUAF 9.0 Official Niyamavali</span>
                <button type="button" @click="showRulesModal = false" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-mono text-xs font-bold hover:bg-slate-800 transition cursor-pointer">
                    Close (അടയ്ക്കുക)
                </button>
            </div>
        </div>
    </div>

    <!-- Scoring Criteria Modal Popup -->
    <div x-show="showCriteriaModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;"
         @click.self="showCriteriaModal = false">
        
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 space-y-5 shadow-2xl border border-slate-200 relative transform transition-all"
             @click.stop>
            
            <div class="flex items-start justify-between border-b border-slate-100 pb-4">
                <div>
                    <span class="text-[10px] font-mono uppercase tracking-widest text-[#005c94] font-bold block">Evaluation Blueprint</span>
                    <h3 class="text-xl font-sora font-bold text-slate-900 mt-0.5" x-text="modalProgramName"></h3>
                    <p class="text-xs font-mono text-slate-500">Program ID: <span x-text="modalProgramCode"></span></p>
                </div>
                <button type="button" @click="showCriteriaModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center cursor-pointer transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div>
                <template x-if="modalCriteria && modalCriteria.length > 0">
                    <div class="border border-slate-200 rounded-2xl overflow-hidden">
                        <table class="w-full text-xs font-mono">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase">
                                <tr>
                                    <th class="py-2.5 px-4 text-left">Criterion</th>
                                    <th class="py-2.5 px-4 text-right">Max Marks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="crit in modalCriteria" :key="crit.id || crit.criterion_name">
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2.5 px-4 text-slate-800 font-bold" x-text="crit.criterion_name"></td>
                                        <td class="py-2.5 px-4 text-right font-black text-[#be1e2d]" x-text="crit.max_marks + ' Marks'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
                <template x-if="!modalCriteria || modalCriteria.length === 0">
                    <div class="p-6 text-center text-slate-500 font-mono text-xs bg-slate-50 rounded-2xl border border-slate-200">
                        ഈ പ്രോഗ്രാമിന് മാനദണ്ഡങ്ങൾ പ്രത്യേകം രേഖപ്പെടുത്തിയിട്ടില്ല. ജനറൽ മൂല്യനിർണ്ണയ രീതി ബാധകമാണ്.
                    </div>
                </template>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[11px] font-mono text-slate-400">Jury Evaluation System</span>
                <button type="button" @click="showCriteriaModal = false" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-mono text-xs font-bold hover:bg-slate-800 transition cursor-pointer">
                    Close (അടയ്ക്കുക)
                </button>
            </div>
        </div>
    </div>

</body>
</html>
