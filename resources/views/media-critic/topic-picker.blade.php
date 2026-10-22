<!DOCTYPE html>
<html lang="ml" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>മീഡിയ ക്രിറ്റിക് വിഷയം തിരഞ്ഞെടുക്കൽ | QUAF 9.0 Sahityotsav</title>

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
        .gold-gradient {
            background: linear-gradient(135deg, #fce072 0%, #dfa528 50%, #b8860b 100%);
        }
        .gold-border {
            border-image: linear-gradient(to bottom right, #fce072, #dfa528, #996515) 1;
        }
        .card-perspective {
            perspective: 1000px;
        }
        .card-inner {
            transition: transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform-style: preserve-3d;
        }
        .card-flipped .card-inner {
            transform: rotateY(180deg);
        }
        .card-front, .card-back {
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }
        .card-back {
            transform: rotateY(180deg);
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 25px rgba(223, 165, 40, 0.3), inset 0 0 15px rgba(223, 165, 40, 0.2); }
            50% { box-shadow: 0 0 45px rgba(223, 165, 40, 0.6), inset 0 0 25px rgba(223, 165, 40, 0.4); }
        }
        .animate-glow {
            animation: pulseGlow 2.5s infinite;
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(200%); }
        }
        .shimmer-effect {
            position: relative;
            overflow: hidden;
        }
        .shimmer-effect::after {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.15), transparent);
            transform: translateX(-100%);
            animation: shimmer 3s infinite;
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
<body class="min-h-screen bg-[#07090e] text-slate-100 antialiased flex flex-col selection:bg-amber-500 selection:text-slate-950">

    <!-- Confetti Canvas -->
    <canvas id="confettiCanvas" class="fixed inset-0 pointer-events-none z-50 w-full h-full"></canvas>

    <!-- Top Festival Bar -->
    <header class="no-print border-b border-white/10 bg-black/40 backdrop-blur-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <img src="{{ asset('images/dashboard-logo-dark.svg') }}" alt="QUAF" class="h-8 w-auto">
                </a>
                <span class="text-white/20">|</span>
                <span class="text-xs font-mono font-bold tracking-wider text-amber-400">MEDIA CRITIC &bull; Q9-183</span>
            </div>

            <div class="flex items-center gap-3">
                @if(Auth::check())
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/5 border border-white/10 text-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="font-bold text-slate-200">{{ Auth::user()->name }}</span>
                        @if($group)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold text-slate-950" style="background-color: {{ $group->color_hex ?? '#dfa528' }};">
                                {{ $group->name }}
                            </span>
                        @endif
                    </div>
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.media-critic-topics.index') }}" class="text-xs px-3 py-1.5 rounded-xl bg-amber-500/20 text-amber-300 hover:bg-amber-500 hover:text-slate-950 border border-amber-500/30 font-bold transition">
                            Admin Desk
                        </a>
                    @elseif(Auth::user()->role === 'group_leader')
                        <a href="{{ route('leader.dashboard') }}" class="text-xs px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-slate-200 font-bold transition">
                            Leader Portal
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs px-3 py-1.5 rounded-xl bg-white/5 hover:bg-rose-500/20 hover:text-rose-400 text-slate-400 font-bold transition border border-white/5">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-xs px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold font-mono transition shadow-sm">
                        Standard Login &rarr;
                    </a>
                @endif
            </div>
        </div>
    </header>

    @if(Auth::check() && Auth::user()->isAdmin())
        <!-- Admin Simulation Bar -->
        <div class="no-print bg-amber-500/10 border-b border-amber-500/20 py-2 px-4">
            <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3 text-xs font-mono">
                <div class="flex items-center gap-2 text-amber-300">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <strong>ADMIN TESTING MODE:</strong> You can simulate or pick on behalf of any group.
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-slate-400">Simulate Group:</span>
                    <select onchange="location.href = '{{ route('topic-picker.index') }}?group_id=' + this.value" class="bg-slate-900 border border-amber-500/40 text-amber-300 rounded px-2 py-1 text-xs">
                        @foreach($allGroups as $g)
                            <option value="{{ $g->id }}" {{ ($group && $group->id === $g->id) ? 'selected' : '' }}>
                                {{ $g->name }} ({{ $g->code }})
                            </option>
                        @endforeach
                    </select>
                    <a href="{{ route('admin.media-critic-topics.index') }}" class="underline text-amber-400 hover:text-amber-300 ml-2">
                        Open Admin Manager &rarr;
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 flex flex-col justify-center">

        @if(!Auth::check())
            <!-- ========================================== -->
            <!-- 1. NOT LOGGED IN: LEADER LOGIN SCREEN      -->
            <!-- ========================================== -->
            <div class="max-w-md mx-auto w-full">
                <!-- Branding Card -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-mono font-bold uppercase tracking-wider mb-4">
                        QUAF 9.0 Official Topic Lottery
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-2 font-sora">
                        Media Critic
                    </h1>
                    <p class="text-lg text-amber-400 font-bold font-malayalam">
                        മീഡിയ ക്രിറ്റിക് വിഷയം തിരഞ്ഞെടുക്കൽ
                    </p>
                    <p class="text-xs text-slate-400 mt-2 font-mono">
                        Program Code: Q9-183 &bull; 5 Official Stage Topics
                    </p>
                </div>

                <!-- Login Form Box -->
                <div class="rounded-3xl bg-slate-900/90 border border-white/10 p-8 shadow-2xl backdrop-blur-xl relative overflow-hidden">
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="mb-6 pb-4 border-b border-white/10">
                        <h2 class="text-base font-bold text-white flex items-center gap-2 font-sora">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>ഗ്രൂപ്പ് ലീഡർ ലോഗിൻ</span>
                        </h2>
                        <p class="text-xs text-slate-400 font-malayalam mt-1">
                            വിഷയം തിരഞ്ഞെടുക്കാൻ നിങ്ങളുടെ ടീം ക്രഡൻഷ്യലുകൾ നൽകുക.
                        </p>
                    </div>

                    @if($errors->any())
                        <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-mono">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('topic-picker.login') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-mono uppercase tracking-wider text-slate-300 font-bold mb-1.5">
                                Username / Group Code
                            </label>
                            <input type="text" name="username" value="{{ old('username') }}" required autofocus
                                placeholder="e.g. LUMO, PACTO, YUGO, UNIO, CONCO"
                                class="w-full bg-slate-950 border border-white/15 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-mono uppercase tracking-wider text-slate-300 font-bold mb-1.5">
                                Password
                            </label>
                            <input type="password" name="password" required
                                placeholder="Enter your password"
                                class="w-full bg-slate-950 border border-white/15 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition font-mono">
                        </div>

                        <button type="submit"
                            class="w-full mt-2 py-3.5 px-6 rounded-xl font-sora font-black text-sm bg-linear-to-r from-amber-400 via-amber-500 to-amber-600 hover:from-amber-300 hover:to-amber-500 text-slate-950 transition duration-200 shadow-lg shadow-amber-500/20 active:scale-[0.98] flex items-center justify-center gap-2">
                            <span>ലോഗിൻ ചെയ്ത് പ്രവേശിക്കുക</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>

                    <div class="mt-6 pt-4 border-t border-white/5 text-center">
                        <p class="text-[11px] text-slate-500 font-mono">
                            QUAF 9.0 Sahityotsav Official Topic Allocation System
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
                <div class="slip-card rounded-3xl bg-linear-to-b from-slate-900 via-slate-900/95 to-slate-950 border-2 border-amber-400/50 p-6 sm:p-10 shadow-2xl relative overflow-hidden text-center">
                    <!-- Subtle Glow -->
                    <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-96 h-96 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>

                    <!-- Seal / Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-mono font-bold mb-4 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>TOPIC CONFIRMED & LOCKED &bull; വിഷയം സ്ഥിരീകരിച്ചു</span>
                    </div>

                    <div class="mb-4">
                        <div class="text-xs font-mono text-slate-400 tracking-widest uppercase">QUAF 9.0 Sahityotsav &bull; Media Critic (Q9-183)</div>
                        <h2 class="text-2xl sm:text-3xl font-black text-white mt-1">
                            {{ $group->name ?? 'Team Topic' }}
                        </h2>
                    </div>

                    <!-- Card Code Pill -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-amber-500 text-slate-950 font-mono font-black text-sm uppercase tracking-wider mb-6 shadow-md">
                        <span>Card {{ $myTopic->code_letter }} (വിഷയം #{{ $myTopic->topic_number }})</span>
                    </div>

                    <!-- The Assigned Topic Box -->
                    <div class="p-6 sm:p-8 rounded-2xl bg-black/60 border-2 border-amber-400/60 shadow-inner my-2 relative">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-amber-400 font-bold block mb-2">
                            ഔദ്യോഗിക വിഷയം (Official Topic)
                        </span>
                        <div class="text-xl sm:text-2xl md:text-3xl font-bold font-malayalam text-white leading-relaxed text-balance">
                            {{ $myTopic->title }}
                        </div>
                    </div>

                    <!-- Metadata Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 my-6 text-xs font-mono">
                        <div class="p-3 rounded-xl bg-white/5 border border-white/5">
                            <span class="text-slate-400 block text-[10px]">Team Name</span>
                            <span class="font-bold text-white text-sm">{{ $group->name }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-white/5 border border-white/5">
                            <span class="text-slate-400 block text-[10px]">Selection Mode</span>
                            <span class="font-bold text-amber-300 uppercase">{{ ucfirst($myTopic->selection_method) }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-white/5 border border-white/5 col-span-2 sm:col-span-1">
                            <span class="text-slate-400 block text-[10px]">Locked At</span>
                            <span class="font-bold text-white">{{ $myTopic->picked_at?->format('d M, h:i A') ?? now()->format('d M, h:i A') }}</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-400 font-malayalam leading-relaxed">
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
                        <button onclick="window.print()" class="px-5 py-2.5 rounded-xl font-bold text-xs bg-amber-500 hover:bg-amber-400 text-slate-950 transition flex items-center gap-2 shadow-sm font-mono">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Print Official Slip</span>
                        </button>
                        @if(Auth::user()->role === 'group_leader')
                            <a href="{{ route('leader.dashboard') }}" class="px-5 py-2.5 rounded-xl font-bold text-xs bg-white/10 hover:bg-white/20 text-white transition font-mono">
                                &larr; Return to Leader Dashboard
                            </a>
                        @endif
                    </div>
                </div>
            </div>

        @else
            <!-- ========================================== -->
            <!-- 3. TOPIC PICKER SELECTION SCREEN           -->
            <!-- ========================================== -->
            <div class="max-w-4xl mx-auto w-full">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-mono font-bold uppercase tracking-wider mb-3">
                        Team: {{ $group->name }} &bull; Q9-183 Media Critic
                    </div>
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight font-sora">
                        Pick Your Competition Topic
                    </h1>
                    <p class="text-base sm:text-lg text-amber-400 font-bold font-malayalam mt-2">
                        നിങ്ങളുടെ ടീമിനുള്ള മീഡിയ ക്രിറ്റിക് വിഷയം തിരഞ്ഞെടുക്കുക
                    </p>
                    <p class="text-xs text-slate-400 mt-1 max-w-xl mx-auto font-malayalam">
                        താഴെ നൽകിയിട്ടുള്ള കാർഡുകളിൽ നിന്നോ സ്പിൻ വീലിൽ നിന്നോ ഒരെണ്ണം തിരഞ്ഞെടുക്കുക. തിരഞ്ഞെടുത്ത ഉടൻ ആ വിഷയം നിങ്ങളുടെ ടീമിനായി ലോക്ക് ചെയ്യപ്പെടും.
                    </p>

                    <!-- Interactive Mode Tabs -->
                    <div class="inline-flex p-1.5 rounded-2xl bg-slate-900 border border-white/10 mt-6 gap-1 shadow-md">
                        <button id="tabCardsBtn" onclick="switchMode('cards')" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-amber-500 text-slate-950 shadow-xs font-mono">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>1. മിസ്റ്ററി കാർഡുകൾ (Cards A-E)</span>
                        </button>
                        <button id="tabWheelBtn" onclick="switchMode('wheel')" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white font-mono">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>2. ലക്കി സ്പിൻ വീൽ (Spin Wheel)</span>
                        </button>
                    </div>
                </div>

                <!-- MODE 1: MYSTERY CARDS -->
                <div id="modeCards" class="block">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                        @foreach($topics as $topic)
                            @php
                                $isClaimed = $topic->isClaimed();
                            @endphp

                            <div class="card-perspective h-64 sm:h-72">
                                <div id="cardItem-{{ $topic->id }}" class="card-inner w-full h-full relative rounded-2xl {{ $isClaimed ? 'card-flipped' : '' }}">
                                    <!-- FRONT (Available Mystery Card) -->
                                    <div onclick="{{ ! $isClaimed ? "openConfirmModal({$topic->id}, '{$topic->code_letter}', {$topic->topic_number})" : '' }}"
                                        class="card-front absolute inset-0 w-full h-full rounded-2xl bg-linear-to-b from-slate-900 via-slate-900 to-black border-2 {{ $isClaimed ? 'border-slate-800 opacity-60' : 'border-amber-400/50 hover:border-amber-300 cursor-pointer shadow-lg hover:shadow-amber-500/20 hover:-translate-y-1.5 transition-all duration-300' }} p-4 flex flex-col items-center justify-between text-center select-none">

                                        <!-- Top Pill -->
                                        <div class="w-full flex items-center justify-between">
                                            <span class="text-[10px] font-mono font-bold text-amber-400">QUAF 9.0</span>
                                            <span class="w-6 h-6 rounded-full bg-white/5 border border-white/10 text-amber-300 font-mono font-black text-xs flex items-center justify-center">
                                                #{{ $topic->topic_number }}
                                            </span>
                                        </div>

                                        <!-- Center Big Letter -->
                                        <div class="my-auto text-center">
                                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-linear-to-br from-amber-400 to-amber-600 text-slate-950 font-black text-3xl sm:text-4xl flex items-center justify-center shadow-md font-sora mx-auto">
                                                {{ $topic->code_letter }}
                                            </div>
                                            <span class="text-[11px] font-mono text-slate-400 font-bold tracking-wider uppercase block mt-2">
                                                Card {{ $topic->code_letter }}
                                            </span>
                                        </div>

                                        <!-- Bottom Action -->
                                        <div class="w-full pt-2 border-t border-white/5">
                                            @if($isClaimed)
                                                <span class="text-[10px] font-mono font-bold text-rose-400 flex items-center justify-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                    <span>ലോക്ക് ചെയ്തു</span>
                                                </span>
                                            @else
                                                <span class="text-[10px] font-mono font-bold text-amber-300 flex items-center justify-center gap-1 group-hover:underline">
                                                    <span>തുറക്കുക / Pick</span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- BACK (Revealed / Locked by Team) -->
                                    <div class="card-back absolute inset-0 w-full h-full rounded-2xl bg-linear-to-b from-slate-900 via-black to-slate-950 border-2 border-rose-500/50 p-4 flex flex-col items-center justify-between text-center select-none shadow-xl">
                                        <div class="w-full flex items-center justify-between">
                                            <span class="text-[10px] font-mono font-bold text-rose-400 uppercase">CLAIMED</span>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-500/20 text-rose-300">
                                                Card {{ $topic->code_letter }}
                                            </span>
                                        </div>

                                        <div class="my-auto w-full px-1">
                                            <div class="w-10 h-10 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center mx-auto mb-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            </div>
                                            <div class="text-xs font-bold text-white truncate">
                                                {{ $topic->group?->name ?? 'Another Team' }}
                                            </div>
                                            <div class="text-[10px] font-mono text-slate-400 mt-1">
                                                വേറെ ടീം തിരഞ്ഞെടുത്തു
                                            </div>
                                        </div>

                                        <div class="w-full text-[10px] font-mono text-slate-500 pt-2 border-t border-white/5">
                                            Not Available
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- MODE 2: SPIN WHEEL -->
                <div id="modeWheel" class="hidden text-center">
                    <div class="max-w-md mx-auto p-6 rounded-3xl bg-slate-900/80 border border-white/10 shadow-2xl relative">
                        <!-- Canvas Wheel Container -->
                        <div class="relative w-72 h-72 sm:w-80 sm:h-80 mx-auto my-4">
                            <!-- Pointer Arrow -->
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2 z-20 w-0 h-0 border-l-[12px] border-l-transparent border-r-[12px] border-r-transparent border-t-[20px] border-t-amber-400 drop-shadow-md"></div>

                            <canvas id="wheelCanvas" width="320" height="320" class="w-full h-full rounded-full shadow-2xl"></canvas>

                            <!-- Center Spin Button -->
                            <button id="spinBtn" onclick="spinWheel()"
                                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-slate-950 border-4 border-amber-400 text-amber-400 font-black text-xs sm:text-sm font-mono flex flex-col items-center justify-center shadow-2xl active:scale-95 transition cursor-pointer z-10 hover:bg-amber-400 hover:text-slate-950">
                                <span>SPIN</span>
                                <span class="text-[8px] font-normal">കറക്കുക</span>
                            </button>
                        </div>

                        <p class="text-xs text-slate-400 font-malayalam mt-2">
                            'SPIN' ബട്ടൺ ക്ലിക്ക് ചെയ്താൽ ചക്രം കറങ്ങി അവശേഷിക്കുന്ന ഏതെങ്കിലും ഒരു വിഷയത്തിൽ നിൽക്കുകയും അത് നിങ്ങളുടെ ടീമിനായി ലോക്ക് ചെയ്യപ്പെടുകയും ചെയ്യും.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- MODAL: CONFIRM / SCRATCH REVEAL DIALOG     -->
            <!-- ========================================== -->
            <div id="confirmModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
                <div class="max-w-md w-full rounded-3xl bg-slate-900 border border-amber-500/40 p-6 sm:p-8 shadow-2xl text-center relative animate-glow">
                    <!-- Close Button -->
                    <button onclick="closeConfirmModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <div class="w-16 h-16 rounded-2xl bg-amber-500/20 text-amber-400 font-black text-3xl flex items-center justify-center mx-auto mb-3 font-sora">
                        <span id="modalCodeLetter">A</span>
                    </div>

                    <h3 class="text-xl font-black text-white font-sora">
                        Confirm Topic Selection
                    </h3>
                    <p class="text-sm font-bold text-amber-400 font-malayalam mt-1">
                        കാർഡ് <span id="modalCardLabel">A</span> തിരഞ്ഞെടുത്ത് വിഷയം ലോക്ക് ചെയ്യണോ?
                    </p>

                    <div class="p-3.5 rounded-xl bg-black/40 border border-white/10 text-xs text-slate-300 font-malayalam my-5 text-left leading-relaxed">
                        <ul class="list-disc list-inside space-y-1">
                            <li>ഈ കാർഡ് തിരഞ്ഞെടുത്താൽ നിങ്ങളുടെ ടീമിനുള്ള വിഷയം ഇതിൽ ലോക്കാകും.</li>
                            <li>മറ്റ് ടീമുകൾക്ക് ഈ വിഷയം ഇനി ലഭിക്കില്ല.</li>
                            <li>ഒരിക്കൽ ലോക്ക് ചെയ്താൽ ടീമിന് സ്വന്തമായി മാറ്റാൻ സാധിക്കില്ല.</li>
                        </ul>
                    </div>

                    <!-- Scratch Card Foil Option -->
                    <div id="scratchContainer" class="hidden my-4">
                        <p class="text-xs text-amber-300 font-mono mb-2">Scratch below to reveal:</p>
                        <canvas id="scratchCanvas" width="280" height="80" class="mx-auto rounded-xl border-2 border-amber-400 cursor-crosshair shadow-inner"></canvas>
                    </div>

                    <div class="flex items-center gap-3">
                        <button onclick="closeConfirmModal()" class="flex-1 py-3 px-4 rounded-xl border border-white/15 text-slate-300 hover:bg-white/5 font-mono text-xs font-bold transition">
                            Cancel / വേണ്ട
                        </button>
                        <button id="modalConfirmBtn" onclick="confirmCardSelection()" class="flex-1 py-3 px-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-sora text-xs font-black transition shadow-lg shadow-amber-500/20 flex items-center justify-center gap-1.5">
                            <span id="modalBtnText">ലോക്ക് ചെയ്യുക &rarr;</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </main>

    <!-- Footer -->
    <footer class="no-print border-t border-white/10 py-4 text-center text-xs font-mono text-slate-500 bg-black/40">
        QUAF 9.0 Sahityotsav &bull; Media Critic Stage Competition Topic Allocator
    </footer>

    <!-- Client-side Logic (Vanilla JS) -->
    <script>
        let selectedTopicId = null;
        let selectedCodeLetter = null;
        let selectedTopicNumber = null;
        let isSpinning = false;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Topics data injected from server
        const allTopics = @json($topics ?? []);
        const groupId = {{ $group ? $group->id : 'null' }};

        function switchMode(mode) {
            const cardsTab = document.getElementById('modeCards');
            const wheelTab = document.getElementById('modeWheel');
            const tabCardsBtn = document.getElementById('tabCardsBtn');
            const tabWheelBtn = document.getElementById('tabWheelBtn');

            if (mode === 'cards') {
                cardsTab.classList.remove('hidden');
                wheelTab.classList.add('hidden');
                tabCardsBtn.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-amber-500 text-slate-950 shadow-xs font-mono';
                tabWheelBtn.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white font-mono';
            } else {
                cardsTab.classList.add('hidden');
                wheelTab.classList.remove('hidden');
                tabCardsBtn.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white font-mono';
                tabWheelBtn.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-amber-500 text-slate-950 shadow-xs font-mono';
                drawWheel();
            }
        }

        function openConfirmModal(id, letter, num) {
            selectedTopicId = id;
            selectedCodeLetter = letter;
            selectedTopicNumber = num;

            document.getElementById('modalCodeLetter').innerText = letter;
            document.getElementById('modalCardLabel').innerText = `${letter} (വിഷയം #${num})`;
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.add('hidden');
            selectedTopicId = null;
        }

        async function confirmCardSelection() {
            if (!selectedTopicId) return;

            const btn = document.getElementById('modalConfirmBtn');
            const btnText = document.getElementById('modalBtnText');
            btn.disabled = true;
            btnText.innerText = 'Locking... ദയവായി കാത്തിരിക്കുക';

            try {
                const response = await fetch('{{ route('topic-picker.select') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        topic_id: selectedTopicId,
                        group_id: groupId,
                        method: 'card'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    playSuccessChime();
                    triggerConfetti();
                    // Flip the selected card visually
                    const cardInner = document.getElementById('cardItem-' + selectedTopicId);
                    if (cardInner) {
                        cardInner.classList.add('card-flipped');
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                } else {
                    alert(data.message || 'Error picking topic');
                    btn.disabled = false;
                    btnText.innerText = 'ലോക്ക് ചെയ്യുക →';
                    closeConfirmModal();
                    window.location.reload();
                }
            } catch (err) {
                alert('Connection error: ' + err.message);
                btn.disabled = false;
                btnText.innerText = 'ലോക്ക് ചെയ്യുക →';
            }
        }

        // ==========================================
        // SPIN WHEEL CANVAS IMPLEMENTATION
        // ==========================================
        let wheelAngle = 0;
        const wheelColors = ['#f59e0b', '#3b82f6', '#10b981', '#ec4899', '#8b5cf6'];

        function getAvailableTopics() {
            return allTopics.filter(t => !t.is_locked && !t.group_id);
        }

        function drawWheel() {
            const canvas = document.getElementById('wheelCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const center = canvas.width / 2;
            const radius = center - 8;

            const available = getAvailableTopics();
            const segments = available.length > 0 ? available : allTopics;
            const numSegments = segments.length;
            const arc = (2 * Math.PI) / numSegments;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Draw segments
            for (let i = 0; i < numSegments; i++) {
                const angle = wheelAngle + i * arc;
                ctx.beginPath();
                ctx.fillStyle = wheelColors[i % wheelColors.length];
                ctx.moveTo(center, center);
                ctx.arc(center, center, radius, angle, angle + arc);
                ctx.lineTo(center, center);
                ctx.fill();
                ctx.strokeStyle = '#07090e';
                ctx.lineWidth = 3;
                ctx.stroke();

                // Draw Text
                ctx.save();
                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 16px Sora, sans-serif';
                ctx.translate(center, center);
                ctx.rotate(angle + arc / 2);
                ctx.textAlign = 'right';
                ctx.fillText(`Card ${segments[i].code_letter}`, radius - 20, 6);
                ctx.restore();
            }

            // Outer ring
            ctx.beginPath();
            ctx.arc(center, center, radius, 0, 2 * Math.PI);
            ctx.strokeStyle = '#dfa528';
            ctx.lineWidth = 5;
            ctx.stroke();
        }

        async function spinWheel() {
            if (isSpinning) return;
            const available = getAvailableTopics();
            if (available.length === 0) {
                alert('No remaining topics available to spin!');
                return;
            }

            isSpinning = true;
            const spinBtn = document.getElementById('spinBtn');
            spinBtn.disabled = true;

            // Random winner among available
            const randomIndex = Math.floor(Math.random() * available.length);
            const winner = available[randomIndex];

            const numSegments = available.length;
            const arc = (2 * Math.PI) / numSegments;

            // Target pointer is at top (-PI/2)
            const targetSegmentAngle = randomIndex * arc + arc / 2;
            const fullSpins = 5 + Math.floor(Math.random() * 3);
            const targetAngle = fullSpins * 2 * Math.PI + (1.5 * Math.PI - targetSegmentAngle);

            const duration = 4000;
            const start = performance.now();
            const initialAngle = wheelAngle;

            function animate(now) {
                const elapsed = now - start;
                const progress = Math.min(elapsed / duration, 1);
                // Ease out cubic
                const ease = 1 - Math.pow(1 - progress, 3);

                wheelAngle = initialAngle + ease * targetAngle;
                drawWheel();

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    isSpinning = false;
                    playSuccessChime();
                    triggerConfetti();

                    // Lock topic on server
                    setTimeout(() => {
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
                                window.location.reload();
                            } else {
                                alert(data.message || 'Error locking topic');
                                window.location.reload();
                            }
                        }).catch(e => {
                            alert('Network error');
                            window.location.reload();
                        });
                    }, 800);
                }
            }

            requestAnimationFrame(animate);
        }

        // ==========================================
        // AUDIO & CONFETTI CELEBRATION EFFECTS
        // ==========================================
        function playSuccessChime() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const ctx = new AudioContext();
                const notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6
                notes.forEach((freq, idx) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'triangle';
                    osc.frequency.value = freq;
                    gain.gain.setValueAtTime(0.2, ctx.currentTime + idx * 0.1);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + idx * 0.1 + 0.5);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(ctx.currentTime + idx * 0.1);
                    osc.stop(ctx.currentTime + idx * 0.1 + 0.5);
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
            const colors = ['#f59e0b', '#10b981', '#3b82f6', '#ec4899', '#ffffff', '#eab308'];

            for (let i = 0; i < 120; i++) {
                particles.push({
                    x: canvas.width / 2,
                    y: canvas.height / 2,
                    vx: (Math.random() - 0.5) * 16,
                    vy: (Math.random() - 0.8) * 18,
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
                    data.topics.forEach(t => {
                        const card = document.getElementById('cardItem-' + t.id);
                        if (card && t.is_claimed && !card.classList.contains('card-flipped')) {
                            card.classList.add('card-flipped');
                        }
                    });
                }
            } catch (e) {}
        }, 6000);
    </script>
</body>
</html>
