<!DOCTYPE html>
<html lang="ml" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>മീഡിയ ക്രിറ്റിക് വിഷയം തിരഞ്ഞെടുക്കൽ | QUAF Fest</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Malayalam:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700;800&family=Sora:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Sora', sans-serif;
        }
        .font-malayalam, .malayalam-title {
            font-family: 'Anek Malayalam', sans-serif !important;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace !important;
        }
        @keyframes pulseGlowLight {
            0%, 100% { box-shadow: 0 10px 30px -10px rgba(217, 119, 6, 0.3); }
            50% { box-shadow: 0 20px 40px -10px rgba(217, 119, 6, 0.5); }
        }
        .animate-glow-light {
            animation: pulseGlowLight 2.5s infinite;
        }
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            .slip-card {
                border: 2px solid #000 !important;
                box-shadow: none !important;
                background: white !important;
                color: black !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-[#f8fafc] text-slate-800 antialiased flex flex-col selection:bg-amber-500 selection:text-white">

    <!-- Confetti Canvas -->
    <canvas id="confettiCanvas" class="fixed inset-0 pointer-events-none z-50 w-full h-full"></canvas>

    <!-- Top Navigation Bar -->
    <header class="no-print border-b border-slate-200/80 bg-white/95 backdrop-blur-md sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <img src="{{ asset('images/dashboard-logo.svg') }}" alt="QUAF" class="h-8 sm:h-9 w-auto">
                </a>
                <span class="text-slate-300">|</span>
                <span class="text-xs font-mono font-bold tracking-wider text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">
                    MEDIA CRITIC &bull; Q9-183
                </span>
            </div>

            <div class="flex items-center gap-2.5">
                @if(Auth::check())
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="font-bold text-slate-700">{{ Auth::user()->name }}</span>
                        @if($group)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold text-white shadow-2xs" style="background-color: {{ $group->color_hex ?? '#d97706' }};">
                                {{ $group->name }}
                            </span>
                        @endif
                    </div>
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.media-critic-topics.index') }}" class="text-xs px-3 py-1.5 rounded-xl bg-amber-100 hover:bg-amber-200 text-amber-900 border border-amber-300 font-bold transition">
                            Admin Desk
                        </a>
                    @elseif(Auth::user()->role === 'group_leader')
                        <a href="{{ route('leader.dashboard') }}" class="text-xs px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition border border-slate-200">
                            Leader Portal
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 font-bold transition border border-slate-200">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-xs px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold font-mono transition shadow-xs">
                        Standard Login &rarr;
                    </a>
                @endif
            </div>
        </div>
    </header>

    @if(Auth::check() && Auth::user()->isAdmin())
        <!-- Admin Simulation Bar -->
        <div class="no-print bg-amber-50 border-b border-amber-200 py-2 px-4">
            <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3 text-xs font-mono">
                <div class="flex items-center gap-2 text-amber-900">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <strong>ADMIN TESTING MODE:</strong> You can simulate or test for any group.
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-slate-600">Simulate Group:</span>
                    <select onchange="location.href = '{{ route('topic-picker.index') }}?group_id=' + this.value" class="bg-white border border-amber-300 text-slate-800 rounded px-2 py-1 text-xs font-mono">
                        @foreach($allGroups as $g)
                            <option value="{{ $g->id }}" {{ ($group && $group->id === $g->id) ? 'selected' : '' }}>
                                {{ $g->name }} ({{ $g->code }})
                            </option>
                        @endforeach
                    </select>
                    <a href="{{ route('admin.media-critic-topics.index') }}" class="underline text-amber-700 hover:text-amber-900 font-bold ml-2">
                        Admin Manager &rarr;
                    </a>
                    <a href="{{ route('topic-picker.index') }}?reset=1" onclick="return confirm('Reset all picks now?');" class="px-2 py-0.5 rounded bg-rose-100 text-rose-700 hover:bg-rose-200 font-bold ml-2 border border-rose-300">
                        Reset All
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 flex flex-col justify-center">

        @if(!Auth::check())
            <!-- ========================================== -->
            <!-- 1. NOT LOGGED IN: LEADER LOGIN SCREEN      -->
            <!-- ========================================== -->
            <div class="max-w-md mx-auto w-full">
                <!-- Branding Card -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 border border-amber-200 text-amber-900 text-xs font-mono font-bold uppercase tracking-wider mb-3">
                        Official Topic Lottery
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight font-sora">
                        Media Critic
                    </h1>
                    <p class="text-lg text-amber-700 font-bold font-malayalam mt-1">
                        മീഡിയ ക്രിറ്റിക് വിഷയം തിരഞ്ഞെടുക്കൽ
                    </p>
                    <p class="text-xs text-slate-500 mt-1 font-mono">
                        Program Code: Q9-183 &bull; Lucky Spin Wheel
                    </p>
                </div>

                <!-- Login Form Box -->
                <div class="rounded-3xl bg-white border border-slate-200 p-8 shadow-xl relative overflow-hidden">
                    <div class="mb-6 pb-4 border-b border-slate-100">
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2 font-sora">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>ഗ്രൂപ്പ് ലീഡർ ലോഗിൻ</span>
                        </h2>
                        <p class="text-xs text-slate-500 font-malayalam mt-1">
                            വിഷയം തിരഞ്ഞെടുക്കാൻ നിങ്ങളുടെ യൂസർനെയിമും പാസ്‌വേഡും നൽകുക.
                        </p>
                    </div>

                    @if($errors->any())
                        <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-mono">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('topic-picker.login') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-mono uppercase tracking-wider text-slate-700 font-bold mb-1.5">
                                Username / Group Code
                            </label>
                            <input type="text" name="username" value="{{ old('username') }}" required autofocus
                                placeholder="e.g. LUMO, PACTO, YUGO, UNIO, CONCO"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:bg-white focus:ring-1 focus:ring-amber-500 transition font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-mono uppercase tracking-wider text-slate-700 font-bold mb-1.5">
                                Password
                            </label>
                            <input type="password" name="password" required
                                placeholder="Enter your password"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:bg-white focus:ring-1 focus:ring-amber-500 transition font-mono">
                        </div>

                        <button type="submit"
                            class="w-full mt-2 py-3.5 px-6 rounded-xl font-sora font-black text-sm bg-linear-to-r from-amber-500 via-amber-600 to-amber-700 hover:from-amber-600 hover:to-amber-800 text-white transition duration-200 shadow-md shadow-amber-500/20 active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
                            <span>ലോഗിൻ ചെയ്ത് പ്രവേശിക്കുക</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>

                    <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                        <p class="text-[11px] text-slate-400 font-mono">
                            QUAF Official Topic Allocation System
                        </p>
                    </div>
                </div>
            </div>

        @elseif($myTopic)
            <!-- ========================================== -->
            <!-- 2. ALREADY PICKED & LOCKED: SUCCESS SCREEN -->
            <!-- ========================================== -->
            <div class="max-w-2xl mx-auto w-full">
                <!-- Printable Certificate/Slip -->
                <div class="slip-card rounded-3xl bg-white border-2 border-amber-300 p-6 sm:p-10 shadow-xl relative overflow-hidden text-center">

                    <!-- Seal / Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-mono font-bold mb-4 shadow-2xs">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>TOPIC CONFIRMED &bull; വിഷയം സ്ഥിരീകരിച്ചു</span>
                    </div>

                    <div class="mb-4">
                        <div class="text-xs font-mono text-slate-500 tracking-widest uppercase">QUAF Fest &bull; Media Critic (Q9-183)</div>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">
                            {{ $group->name ?? 'Team Topic' }}
                        </h2>
                    </div>

                    <!-- Card Code Pill -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-amber-500 text-white font-mono font-black text-sm uppercase tracking-wider mb-6 shadow-sm">
                        <span>Card {{ $myTopic->code_letter }} (വിഷയം #{{ $myTopic->topic_number }})</span>
                    </div>

                    <!-- The Assigned Topic Box -->
                    <div class="p-6 sm:p-8 rounded-2xl bg-linear-to-b from-amber-50/80 via-white to-amber-50/50 border-2 border-amber-300 shadow-inner my-2 relative">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-amber-800 font-bold block mb-2">
                            ഔദ്യോഗിക വിഷയം (Official Topic)
                        </span>
                        <div class="text-xl sm:text-2xl md:text-3xl font-bold font-malayalam text-slate-900 leading-relaxed text-balance">
                            {{ $myTopic->title }}
                        </div>
                    </div>

                    <!-- Metadata Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 my-6 text-xs font-mono">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-slate-500 block text-[10px]">Team Name</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $group->name }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-slate-500 block text-[10px]">Selection Mode</span>
                            <span class="font-bold text-amber-700 uppercase">Lucky Spin Wheel</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 col-span-2 sm:col-span-1">
                            <span class="text-slate-500 block text-[10px]">Locked At</span>
                            <span class="font-bold text-slate-900">{{ $myTopic->picked_at?->format('d M, h:i A') ?? now()->format('d M, h:i A') }}</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 font-malayalam leading-relaxed">
                        ഈ വിഷയം നിങ്ങളുടെ ടീമിനായി സ്ഥിരപ്പെടുത്തിയിരിക്കുന്നു. ഒരു ടീമിനും ഈ വിഷയം ഇനി വീണ്ടും ലഭിക്കില്ല.
                    </p>

                    <!-- Signatures (Visible on Print) -->
                    <div class="hidden print:grid grid-cols-3 gap-4 pt-12 mt-12 border-t border-black text-center text-xs font-mono text-black">
                        <div>
                            <div class="border-b border-black mb-1 h-12"></div>
                            <strong>Group Leader</strong>
                        </div>
                        <div>
                            <div class="border-b border-black mb-1 h-12"></div>
                            <strong>Stage Coordinator</strong>
                        </div>
                        <div>
                            <div class="border-b border-black mb-1 h-12"></div>
                            <strong>Chief Judge</strong>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="no-print flex flex-wrap items-center justify-center gap-3 mt-8">
                        <button onclick="window.print()" class="px-5 py-2.5 rounded-xl font-bold text-xs bg-amber-600 hover:bg-amber-700 text-white transition flex items-center gap-2 shadow-sm font-mono cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Print Official Slip</span>
                        </button>
                        @if(Auth::user()->role === 'group_leader')
                            <a href="{{ route('leader.dashboard') }}" class="px-5 py-2.5 rounded-xl font-bold text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 transition font-mono border border-slate-200">
                                &larr; Return to Leader Dashboard
                            </a>
                        @endif
                    </div>
                </div>
            </div>

        @else
            <!-- ========================================== -->
            <!-- 3. TOPIC PICKER: DEDICATED SPIN WHEEL      -->
            <!-- ========================================== -->
            <div class="max-w-4xl mx-auto w-full">
                <!-- Header -->
                <div class="text-center mb-6">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-100 border border-amber-200 text-amber-900 text-xs font-mono font-bold uppercase tracking-wider mb-2 shadow-2xs">
                        Team: {{ $group->name }} &bull; Q9-183 Media Critic
                    </div>
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 tracking-tight font-sora">
                        Spin to Pick Your Topic
                    </h1>
                    <p class="text-base sm:text-lg text-amber-700 font-bold font-malayalam mt-1">
                        ചക്രം കറക്കി നിങ്ങളുടെ ടീമിനുള്ള വിഷയം തിരഞ്ഞെടുക്കുക
                    </p>
                    <p class="text-xs text-slate-500 mt-1 max-w-xl mx-auto font-malayalam">
                        'SPIN' ബട്ടൺ ക്ലിക്ക് ചെയ്യുമ്പോൾ ചക്രം കറങ്ങി അവശേഷിക്കുന്ന ഏതെങ്കിലും ഒരു വിഷയത്തിൽ നിൽക്കുകയും അത് നിങ്ങളുടെ ടീമിനായി ലോക്ക് ചെയ്യപ്പെടുകയും ചെയ്യും.
                    </p>
                </div>

                <!-- Main Grid: Wheel + Live Status Board -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                    <!-- Left: Spin Wheel Stage -->
                    <div class="lg:col-span-7 flex flex-col items-center">
                        <div class="p-6 sm:p-8 rounded-3xl bg-white border border-slate-200/90 shadow-xl relative w-full flex flex-col items-center overflow-hidden">

                            <!-- Wheel Canvas Wrapper -->
                            <div class="relative w-[280px] h-[280px] sm:w-[340px] sm:h-[340px] mx-auto">
                                <!-- Top Pointer Arrow -->
                                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 z-30 w-0 h-0 border-l-[14px] border-l-transparent border-r-[14px] border-r-transparent border-t-[26px] border-t-amber-500 drop-shadow-[0_4px_8px_rgba(217,119,6,0.5)]"></div>

                                <!-- HTML5 Canvas -->
                                <canvas id="wheelCanvas" width="340" height="340" class="w-full h-full rounded-full shadow-lg"></canvas>

                                <!-- Center Circular Spin Button -->
                                <button id="spinCenterBtn" onclick="spinWheel()"
                                    class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-20 h-20 sm:w-22 sm:h-22 rounded-full bg-white border-4 border-amber-500 text-amber-700 font-black text-sm font-mono flex flex-col items-center justify-center shadow-xl active:scale-95 transition cursor-pointer z-20 hover:bg-amber-500 hover:text-white group">
                                    <span class="tracking-wider">SPIN</span>
                                    <span class="text-[9px] font-normal font-malayalam group-hover:text-white">കറക്കുക</span>
                                </button>
                            </div>

                            <!-- Big Bottom CTA Button -->
                            <button id="spinBottomBtn" onclick="spinWheel()"
                                class="mt-6 w-full max-w-xs py-3.5 px-6 rounded-2xl font-sora font-black text-sm bg-linear-to-r from-amber-500 via-amber-600 to-amber-700 hover:from-amber-600 hover:to-amber-800 text-white transition duration-200 shadow-lg shadow-amber-500/25 active:scale-95 flex items-center justify-center gap-2 cursor-pointer z-10 font-mono">
                                <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span id="spinBtnLabel">SPIN WHEEL NOW &bull; കറക്കുക</span>
                            </button>
                        </div>
                    </div>

                    <!-- Right: Live Topics Status Board -->
                    <div class="lg:col-span-5 space-y-3">
                        <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-md">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                                <div>
                                    <h3 class="font-sora font-bold text-sm text-slate-900">Live Topics Status</h3>
                                    <span class="text-[11px] font-mono text-slate-400">5 Official Competition Cards</span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    {{ $topics->where('is_locked', false)->count() }} Remaining
                                </span>
                            </div>

                            <!-- Topics List -->
                            <div class="space-y-2">
                                @foreach($topics as $t)
                                    @php
                                        $claimed = $t->isClaimed();
                                    @endphp
                                    <div class="p-3 rounded-2xl border transition {{ $claimed ? 'bg-slate-50 border-slate-200 opacity-60' : 'bg-amber-50/30 border-amber-200 hover:border-amber-400' }}">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span class="w-7 h-7 rounded-lg {{ $claimed ? 'bg-slate-200 text-slate-600' : 'bg-amber-500 text-white font-black' }} font-bold text-xs flex items-center justify-center shrink-0 font-sora shadow-2xs">
                                                    {{ $t->code_letter }}
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="text-xs font-bold text-slate-900 truncate">
                                                        Card {{ $t->code_letter }} (വിഷയം #{{ $t->topic_number }})
                                                    </div>
                                                    <div class="text-[10px] font-mono text-slate-500 truncate">
                                                        @if($claimed)
                                                            Taken by <strong class="text-slate-800">{{ $t->group?->name }}</strong>
                                                        @else
                                                            <span class="text-emerald-700 font-bold">Available on Wheel &bull; ലഭ്യമാണ്</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                @if($claimed)
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-200 text-slate-700 border border-slate-300">
                                                        LOCKED
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        OPEN
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="pt-3 mt-3 border-t border-slate-100 text-center">
                                <span class="text-[10px] font-mono text-slate-400">
                                    A topic once claimed is locked and excluded from other teams.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reveal Animation Modal -->
            <div id="revealModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
                <div class="max-w-md w-full rounded-3xl bg-white border-2 border-amber-400 p-8 shadow-2xl text-center relative animate-glow-light">
                    <div class="w-20 h-20 rounded-2xl bg-amber-500 text-white font-black text-4xl flex items-center justify-center mx-auto mb-4 font-sora shadow-md">
                        <span id="revealCodeLetter">?</span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-mono font-bold mb-3">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>TOPIC ALLOTTED &bull; വിഷയം ലഭിച്ചു</span>
                    </div>

                    <h3 class="text-xl font-black text-slate-900 font-sora">
                        Congratulations {{ $group->name }}!
                    </h3>

                    <div class="p-6 rounded-2xl bg-amber-50/60 border border-amber-300 shadow-inner my-5">
                        <span class="text-[10px] font-mono uppercase tracking-wider text-amber-800 font-bold block mb-1">
                            Your Official Topic
                        </span>
                        <div id="revealTopicTitle" class="text-lg sm:text-xl font-bold font-malayalam text-slate-900 leading-relaxed">
                            Loading topic...
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 font-malayalam mb-5">
                        വിഷയം നിങ്ങളുടെ ടീമിനായി സിസ്റ്റത്തിൽ സ്ഥിരീകരിച്ച് ലോക്ക് ചെയ്തിരിക്കുന്നു.
                    </p>

                    <button onclick="window.location.reload()"
                        class="w-full py-3.5 px-6 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-sora text-xs font-black transition shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 cursor-pointer font-mono">
                        <span>VIEW OFFICIAL SLIP &bull; സ്ലിപ്പ് കാണുക &rarr;</span>
                    </button>
                </div>
            </div>
        @endif

    </main>

    <!-- Footer -->
    <footer class="no-print border-t border-slate-200 py-4 text-center text-xs font-mono text-slate-400 bg-white">
        QUAF Fest &bull; Media Critic Stage Competition Topic Allocator
    </footer>

    <!-- Client-side Logic (Vanilla JS) -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const allTopics = @json($topics ?? []);
        const groupId = {{ $group ? $group->id : 'null' }};

        let isSpinning = false;
        let wheelAngle = 0;

        // Vivid palette for 5 segments in Light Theme
        const segmentColors = ['#f59e0b', '#0284c7', '#059669', '#e11d48', '#7c3aed'];

        function getAvailableTopics() {
            return allTopics.filter(t => !t.is_locked && !t.group_id);
        }

        function drawWheel() {
            const canvas = document.getElementById('wheelCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const center = canvas.width / 2;
            const radius = center - 12;

            const available = getAvailableTopics();
            const numSegments = allTopics.length;
            const arc = (2 * Math.PI) / numSegments;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Draw Segments
            for (let i = 0; i < numSegments; i++) {
                const topic = allTopics[i];
                const angle = wheelAngle + i * arc;
                const isClaimed = topic.is_locked || topic.group_id;

                ctx.beginPath();
                ctx.fillStyle = isClaimed ? '#cbd5e1' : segmentColors[i % segmentColors.length];
                ctx.moveTo(center, center);
                ctx.arc(center, center, radius, angle, angle + arc);
                ctx.lineTo(center, center);
                ctx.fill();

                // Segment Border
                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 3;
                ctx.stroke();

                // Draw Text
                ctx.save();
                ctx.fillStyle = isClaimed ? '#475569' : '#ffffff';
                ctx.font = 'bold 15px Sora, sans-serif';
                ctx.translate(center, center);
                ctx.rotate(angle + arc / 2);
                ctx.textAlign = 'right';
                const label = isClaimed ? `Card ${topic.code_letter} (X)` : `Card ${topic.code_letter}`;
                ctx.fillText(label, radius - 24, 5);
                ctx.restore();
            }

            // Outer Golden Rim with 24 Glowing Pegs
            ctx.beginPath();
            ctx.arc(center, center, radius, 0, 2 * Math.PI);
            ctx.strokeStyle = '#d97706';
            ctx.lineWidth = 8;
            ctx.stroke();

            // Pegs
            const totalPegs = 20;
            for (let p = 0; p < totalPegs; p++) {
                const pAngle = wheelAngle + (p * 2 * Math.PI) / totalPegs;
                const px = center + (radius + 2) * Math.cos(pAngle);
                const py = center + (radius + 2) * Math.sin(pAngle);

                ctx.beginPath();
                ctx.arc(px, py, 3.5, 0, 2 * Math.PI);
                ctx.fillStyle = '#ffffff';
                ctx.fill();
                ctx.strokeStyle = '#b45309';
                ctx.lineWidth = 1;
                ctx.stroke();
            }
        }

        // Initialize wheel draw on load
        window.addEventListener('DOMContentLoaded', () => {
            drawWheel();
        });

        async function spinWheel() {
            if (isSpinning) return;
            const available = getAvailableTopics();
            if (available.length === 0) {
                alert('എല്ലാ വിഷയങ്ങളും മറ്റ് ടീമുകൾ തിരഞ്ഞെടുത്തു കഴിഞ്ഞു (No remaining topics available)!');
                return;
            }

            isSpinning = true;
            const centerBtn = document.getElementById('spinCenterBtn');
            const bottomBtn = document.getElementById('spinBottomBtn');
            const btnLabel = document.getElementById('spinBtnLabel');

            if (centerBtn) centerBtn.disabled = true;
            if (bottomBtn) bottomBtn.disabled = true;
            if (btnLabel) btnLabel.innerText = 'SPINNING... കറങ്ങുന്നു';

            // Pick a random available topic as winner
            const randomIndex = Math.floor(Math.random() * available.length);
            const winner = available[randomIndex];

            // Find winner's index in the full allTopics array
            const winnerGlobalIndex = allTopics.findIndex(t => t.id === winner.id);

            const numSegments = allTopics.length;
            const arc = (2 * Math.PI) / numSegments;

            // Target pointer is at top (-PI/2)
            const targetSegmentAngle = winnerGlobalIndex * arc + arc / 2;
            const fullSpins = 6 + Math.floor(Math.random() * 3);
            const targetAngle = fullSpins * 2 * Math.PI + (1.5 * Math.PI - targetSegmentAngle);

            const duration = 4800; // 4.8 seconds
            const start = performance.now();
            const initialAngle = wheelAngle;

            let lastTickSegment = -1;

            function animate(now) {
                const elapsed = now - start;
                const progress = Math.min(elapsed / duration, 1);
                // Ease out cubic
                const ease = 1 - Math.pow(1 - progress, 3);

                wheelAngle = initialAngle + ease * targetAngle;
                drawWheel();

                // Calculate current segment for tick sound
                const currentPointerAngle = (1.5 * Math.PI - wheelAngle) % (2 * Math.PI);
                const normalizedAngle = (currentPointerAngle + 2 * Math.PI) % (2 * Math.PI);
                const currentSegment = Math.floor(normalizedAngle / arc);

                if (currentSegment !== lastTickSegment) {
                    playTick();
                    lastTickSegment = currentSegment;
                }

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    isSpinning = false;
                    playSuccessChime();
                    triggerConfetti();

                    // Lock topic on server
                    fetch('{{ route('topic-picker.select') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            topic_id: winner.id,
                            group_id: groupId,
                            method: 'wheel'
                        })
                    }).then(r => r.json()).then(data => {
                        if (data.success) {
                            // Show reveal modal with the allotted topic
                            const modal = document.getElementById('revealModal');
                            const letterEl = document.getElementById('revealCodeLetter');
                            const titleEl = document.getElementById('revealTopicTitle');

                            if (letterEl) letterEl.innerText = winner.code_letter;
                            if (titleEl) titleEl.innerText = data.topic ? data.topic.title : winner.title;
                            if (modal) modal.classList.remove('hidden');
                        } else {
                            alert(data.message || 'Error locking topic');
                            window.location.reload();
                        }
                    }).catch(e => {
                        alert('Network error');
                        window.location.reload();
                    });
                }
            }

            requestAnimationFrame(animate);
        }

        // ==========================================
        // AUDIO & CONFETTI CELEBRATION EFFECTS
        // ==========================================
        let audioCtx = null;
        function getAudioContext() {
            if (!audioCtx) {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (AudioContext) audioCtx = new AudioContext();
            }
            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            return audioCtx;
        }

        function playTick() {
            try {
                const ctx = getAudioContext();
                if (!ctx) return;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'triangle';
                osc.frequency.value = 880;
                gain.gain.setValueAtTime(0.08, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.04);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.04);
            } catch (e) {}
        }

        function playSuccessChime() {
            try {
                const ctx = getAudioContext();
                if (!ctx) return;
                const notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6
                notes.forEach((freq, idx) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'triangle';
                    osc.frequency.value = freq;
                    gain.gain.setValueAtTime(0.2, ctx.currentTime + idx * 0.1);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + idx * 0.1 + 0.6);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(ctx.currentTime + idx * 0.1);
                    osc.stop(ctx.currentTime + idx * 0.1 + 0.6);
                });
            } catch (e) {}
        }

        function triggerConfetti() {
            const canvas = document.getElementById('confettiCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;

            const particles = [];
            const colors = ['#f59e0b', '#10b981', '#0284c7', '#e11d48', '#7c3aed', '#eab308'];

            for (let i = 0; i < 150; i++) {
                particles.push({
                    x: canvas.width / 2,
                    y: canvas.height / 2,
                    vx: (Math.random() - 0.5) * 18,
                    vy: (Math.random() - 0.8) * 20,
                    size: Math.random() * 8 + 4,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    rotation: Math.random() * 360,
                    rotationSpeed: (Math.random() - 0.5) * 10,
                    gravity: 0.35,
                    opacity: 1
                });
            }

            function updateConfetti() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                let alive = false;

                particles.forEach(p => {
                    p.x += p.vx;
                    p.y += p.vy;
                    p.vy += p.gravity;
                    p.rotation += p.rotationSpeed;
                    p.opacity -= 0.008;

                    if (p.opacity > 0) {
                        alive = true;
                        ctx.save();
                        ctx.globalAlpha = Math.max(0, p.opacity);
                        ctx.translate(p.x, p.y);
                        ctx.rotate((p.rotation * Math.PI) / 180);
                        ctx.fillStyle = p.color;
                        ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
                        ctx.restore();
                    }
                });

                if (alive) {
                    requestAnimationFrame(updateConfetti);
                } else {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                }
            }

            requestAnimationFrame(updateConfetti);
        }

        // Live Status Polling (Every 6 seconds) to keep board synced
        setInterval(async () => {
            if (isSpinning) return;
            try {
                const res = await fetch('{{ route('topic-picker.status') }}');
                const data = await res.json();
                if (data.success && data.topics) {
                    data.topics.forEach(updated => {
                        const local = allTopics.find(t => t.id === updated.id);
                        if (local && updated.is_claimed && !local.is_locked) {
                            local.is_locked = true;
                            drawWheel();
                        }
                    });
                }
            } catch (e) {}
        }, 6000);
    </script>
</body>
</html>
