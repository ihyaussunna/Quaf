<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Green Room Operations | QUAF 09</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts (Multilingual: Anek Malayalam, JetBrains Mono, Sora, Manjari, Gayathri, Amiri, Noto Nastaliq Urdu) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Anek+Malayalam:wght@300;400;500;600;700;800&family=Gayathri:wght@400;700&family=JetBrains+Mono:wght@400;500;600;700;800&family=Manjari:wght@400;700&family=Noto+Nastaliq+Urdu:wght@400;700&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 font-sora antialiased min-h-screen flex flex-col">

    <!-- Backstage High-Speed Topbar (Light Theme) -->
    <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-6 sticky top-0 z-40 shadow-xs">
        <div class="flex items-center gap-4">
            <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF 09" class="h-10 w-auto object-contain">
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-rockwell font-bold tracking-wider text-base text-slate-900">GREEN ROOM DESK</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                </div>
                <span class="text-[10px] font-mono tracking-widest text-[#f3bd2e] block uppercase font-bold">Backstage Dispatch Center • QUAF 09</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if(Auth::user()->role === 'admin' || Auth::user()->role === 'super_admin')
                <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-mono font-semibold">
                    &larr; Admin Panel
                </a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-mono font-bold bg-slate-100 text-slate-700 hover:text-red-700 hover:bg-red-50 border border-slate-200 transition-all">
                    Sign Out
                </button>
            </form>
        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="max-w-7xl w-full mx-auto px-6 mt-4">
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-mono flex items-center justify-between shadow-sm">
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('info'))
        <div class="max-w-7xl w-full mx-auto px-6 mt-4">
            <div class="p-4 rounded-xl bg-blue-50 border border-blue-300 text-blue-800 text-xs font-mono flex items-center justify-between shadow-sm">
                <span>{{ session('info') }}</span>
            </div>
        </div>
    @endif

    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 md:p-8 space-y-6">

        <!-- Stage Switcher Tabs (Light Theme) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
            @foreach($stages as $st)
                <a href="{{ route('greenroom.index', ['stage_id' => $st->id]) }}"
                   class="px-5 py-3 rounded-2xl font-mono text-xs font-bold transition-all flex-shrink-0 flex items-center gap-2.5 {{ $selectedStageId == $st->id ? 'bg-[#f3bd2e] text-white shadow-md scale-105' : 'bg-white text-slate-700 hover:text-slate-900 border border-slate-200 shadow-xs' }}">
                    <span class="w-2 h-2 rounded-full {{ $selectedStageId == $st->id ? 'bg-white' : 'bg-slate-400' }}"></span>
                    <span>{{ $st->name }}</span>
                    @if($st->status === 'live')
                        <span class="px-1.5 py-0.5 rounded text-[9px] bg-red-600 text-white font-bold animate-pulse">LIVE</span>
                    @endif
                </a>
            @endforeach
        </div>

        @if($stage)
            <!-- Active Stage Overview Bar (Light Theme) -->
            <div class="rounded-3xl bg-white border border-slate-200 p-6 grid grid-cols-1 md:grid-cols-3 gap-6 relative overflow-hidden shadow-sm">
                <!-- Current Program -->
                <div class="space-y-1">
                    <span class="text-[10px] font-mono text-slate-500 uppercase tracking-wider block font-semibold">Now On Stage</span>
                    @if($currentProgram)
                        <h3 class="text-xl font-sora font-bold text-slate-900">{{ $currentProgram->name }}</h3>
                        <p class="text-xs font-mono text-[#f3bd2e] font-semibold">Code: {{ $currentProgram->code }} • {{ $currentProgram->category->name ?? 'General' }}</p>
                    @else
                        <h3 class="text-lg font-sora italic text-slate-400">No Program Active</h3>
                        <p class="text-xs font-mono text-slate-500">Stage is currently on intermission</p>
                    @endif
                </div>

                <!-- Next Program -->
                <div class="space-y-1 md:border-l md:border-slate-200 md:pl-6">
                    <span class="text-[10px] font-mono text-slate-500 uppercase tracking-wider block font-semibold">Up Next in Line</span>
                    @if($nextProgram)
                        <h3 class="text-xl font-sora font-bold text-slate-800">{{ $nextProgram->name }}</h3>
                        <p class="text-xs font-mono text-slate-500">Scheduled: {{ $nextProgram->scheduled_time?->format('h:i A') ?? 'TBA' }}</p>
                    @else
                        <h3 class="text-lg font-sora italic text-slate-400">None Queued</h3>
                        <p class="text-xs font-mono text-slate-500">Awaiting next schedule slot</p>
                    @endif
                </div>

                <!-- Action Buttons: Shuffle Code Letters & Call Next -->
                <div class="flex flex-wrap items-center justify-start md:justify-end md:border-l md:border-slate-200 md:pl-6 gap-3">
                    @if($activeProgram)
                        <form method="POST" action="{{ route('greenroom.generate-codes', $activeProgram) }}"
                              onsubmit="return confirm('Generate random code letters (A, B, C...) for PRESENT participants? This will be confidential for judges.')">
                            @csrf
                            <button type="submit" class="px-5 py-3.5 bg-amber-600 hover:bg-amber-500 text-white font-mono font-bold text-xs uppercase rounded-2xl shadow-lg shadow-amber-600/20 flex items-center justify-center gap-2 transition-all transform active:scale-95">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>🎲 Shuffle Code Letters</span>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('greenroom.call-next', $activeProgram) }}">
                            @csrf
                            <button type="submit" class="px-5 py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-mono font-bold text-xs uppercase rounded-2xl shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-2 transition-all transform active:scale-95">
                                <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                <span>CALL NEXT</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Participant Dispatch Queue (Light Theme) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-sora font-bold text-slate-900">Backstage Lineup & Call Board</h2>
                        <p class="text-xs font-mono text-slate-500 mt-0.5">Program: {{ $activeProgram?->name ?? 'None' }} ({{ $calls->count() }} Participants)</p>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-mono">
                        <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold">
                            {{ $calls->where('entry.attendance_status', 'present')->count() }} Present
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-[#005c94] border border-blue-200 font-bold">
                            {{ $calls->where('status', 'on_stage')->count() }} On Stage
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 font-bold">
                            {{ $calls->where('status', 'called')->count() }} Called
                        </span>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($calls as $call)
                        @php
                            $attendance = $call->entry->attendance_status ?? 'waiting';
                            $statusBg = match($call->status) {
                                'on_stage' => 'border-blue-300 bg-blue-50/50 shadow-xs',
                                'called' => 'border-amber-300 bg-amber-50/60 shadow-xs',
                                'ready' => 'border-emerald-300 bg-emerald-50/50 shadow-xs',
                                'checked_in' => 'border-blue-300 bg-blue-50/50 shadow-xs',
                                'completed' => 'border-slate-200 bg-slate-50 opacity-60',
                                'absent' => 'border-red-300 bg-red-50/50 opacity-60',
                                default => 'border-slate-200 bg-white shadow-xs',
                            };
                        @endphp

                        <div class="border rounded-2xl p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 transition-all {{ $statusBg }}">
                            <!-- Participant Info & Code Letter -->
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center font-mono font-bold text-base text-[#f3bd2e] shadow-xs">
                                    #{{ $call->entry->chest_number }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-base font-sora font-bold text-slate-900">{{ $call->entry->student?->name ?? 'Participant' }}</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold" style="background-color: {{ $call->entry->student?->group->color_hex ?? '#f3bd2e' }}20; color: {{ $call->entry->student?->group->color_hex ?? '#f3bd2e' }}">
                                            {{ $call->entry->student?->group->name ?? $call->entry->group?->name }}
                                        </span>
                                        @if($call->entry->code_letter)
                                            <span class="px-2.5 py-0.5 rounded-full bg-[#f3bd2e] text-white font-mono font-black text-xs shadow-xs">
                                                Code {{ $call->entry->code_letter }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-400 font-mono text-[10px]">
                                                No Code Yet
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs font-mono text-slate-500 mt-0.5">
                                        Turn: Order #{{ $call->order_num }}
                                        @if($call->called_at)
                                            • Called at: {{ $call->called_at->format('h:i:s A') }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Attendance Status & Marking -->
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-mono uppercase text-slate-400 font-bold">Attendance:</span>
                                @if($attendance === 'present')
                                    <span class="px-3 py-1 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300 font-mono text-xs font-bold flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        PRESENT
                                    </span>
                                @elseif($attendance === 'absent')
                                    <span class="px-3 py-1 rounded-xl bg-red-100 text-red-800 border border-red-300 font-mono text-xs font-bold">
                                        ABSENT
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-xl bg-slate-100 text-slate-600 border border-slate-200 font-mono text-xs">
                                        WAITING
                                    </span>
                                @endif

                                <!-- Quick Attendance Buttons -->
                                @if($attendance !== 'present')
                                    <form method="POST" action="{{ route('greenroom.mark-attendance', $call->entry) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="present">
                                        <button type="submit" title="Mark Present" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-mono font-bold shadow-2xs">
                                            ✓ Present
                                        </button>
                                    </form>
                                @endif
                                @if($attendance !== 'absent')
                                    <form method="POST" action="{{ route('greenroom.mark-attendance', $call->entry) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="absent">
                                        <button type="submit" title="Mark Absent" class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-lg text-xs font-mono font-bold shadow-2xs">
                                            ✗ Absent
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- Current Dispatch Status Badge -->
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-xl text-xs font-mono font-bold uppercase tracking-wider
                                    {{ $call->status === 'on_stage' ? 'bg-blue-50 text-[#005c94] border border-blue-300 animate-pulse' : '' }}
                                    {{ $call->status === 'called' ? 'bg-amber-100 text-amber-800 border border-amber-300' : '' }}
                                    {{ $call->status === 'ready' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : '' }}
                                    {{ $call->status === 'checked_in' ? 'bg-blue-100 text-blue-800 border border-blue-300' : '' }}
                                    {{ $call->status === 'waiting' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}
                                    {{ $call->status === 'completed' ? 'bg-slate-200 text-slate-500 line-through' : '' }}
                                    {{ $call->status === 'absent' ? 'bg-red-100 text-red-800 border border-red-300' : '' }}
                                ">
                                    {{ str_replace('_', ' ', $call->status) }}
                                </span>
                            </div>

                            <!-- Fast Action Control Buttons -->
                            <div class="flex flex-wrap items-center gap-2">
                                @if($call->status !== 'checked_in' && $call->status !== 'ready' && $call->status !== 'called' && $call->status !== 'on_stage' && $call->status !== 'completed')
                                    <form method="POST" action="{{ route('greenroom.update-status', $call) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="checked_in">
                                        <button type="submit" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-mono font-semibold shadow-2xs">
                                            Check In
                                        </button>
                                    </form>
                                @endif

                                @if($call->status !== 'ready' && $call->status !== 'on_stage' && $call->status !== 'completed')
                                    <form method="POST" action="{{ route('greenroom.update-status', $call) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="ready">
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-mono font-semibold shadow-2xs">
                                            Mark Ready
                                        </button>
                                    </form>
                                @endif

                                @if($call->status !== 'called' && $call->status !== 'on_stage' && $call->status !== 'completed')
                                    <form method="POST" action="{{ route('greenroom.update-status', $call) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="called">
                                        <button type="submit" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-mono font-semibold shadow-2xs">
                                            Call
                                        </button>
                                    </form>
                                @endif

                                @if($call->status !== 'on_stage' && $call->status !== 'completed')
                                    <form method="POST" action="{{ route('greenroom.update-status', $call) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="on_stage">
                                        <button type="submit" class="px-4 py-1.5 bg-[#005c94] text-white hover:bg-blue-500 rounded-xl text-xs font-mono font-bold uppercase shadow-sm">
                                            Stage Now
                                        </button>
                                    </form>
                                @endif

                                @if($call->status === 'on_stage')
                                    <form method="POST" action="{{ route('greenroom.update-status', $call) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="px-3 py-1.5 bg-slate-600 hover:bg-slate-700 text-white rounded-xl text-xs font-mono shadow-2xs">
                                            Finish
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-16 text-center text-slate-500 font-mono text-xs bg-white rounded-3xl border border-slate-200 shadow-sm">
                            No participants in the green room queue for this program yet.
                        </div>
                    @endforelse
                </div>
            </div>
        @else
            <div class="py-16 text-center text-slate-500 font-mono text-xs bg-white rounded-3xl border border-slate-200 shadow-sm">
                Please select a stage to view green room operations.
            </div>
        @endif

    </main>

</body>
</html>
