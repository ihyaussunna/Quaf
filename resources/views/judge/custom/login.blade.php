<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Custom Judge Panel — 4-Digit Access Code | QUAF</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Anek+Malayalam:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&family=Noto+Nastaliq+Urdu:wght@400;700&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 text-slate-100 font-sora antialiased min-h-screen flex flex-col justify-between selection:bg-[#be1e2d] selection:text-white">

    <!-- Top Minimal Navigation -->
    <header class="h-16 sm:h-20 bg-slate-950/80 backdrop-blur-md border-b border-white/10 px-4 sm:px-8 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('images/quaf-logo-favicon.svg') }}" alt="QUAF" class="h-7 w-auto object-contain">
                <div>
                    <span class="text-xs font-mono tracking-widest text-[#f3bd2e] font-bold uppercase block">QUAF 2026</span>
                    <span class="text-[10px] text-slate-400 font-mono block -mt-0.5">Stage Evaluation Desk</span>
                </div>
            </a>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('judge.login') }}" class="text-xs font-mono text-slate-400 hover:text-white px-3 py-1.5 rounded-xl border border-white/10 hover:bg-white/5 transition">
                Individual Judge PIN &rarr;
            </a>
        </div>
    </header>

    <!-- Main Card Section -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md bg-slate-950/90 border border-white/15 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden text-center"
             x-data="{
                 pin: '',
                 addDigit(d) {
                     if (this.pin.length < 4) {
                         this.pin += d;
                         if (this.pin.length === 4) {
                             this.$nextTick(() => {
                                 this.$refs.pinForm.submit();
                             });
                         }
                     }
                 },
                 backspace() {
                     this.pin = this.pin.slice(0, -1);
                 },
                 clear() {
                     this.pin = '';
                 }
             }">

            <!-- Ambient Glow -->
            <div class="absolute -top-24 -right-24 w-60 h-60 bg-[#be1e2d]/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-60 h-60 bg-[#f3bd2e]/15 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Header Badge -->
            <div class="relative z-10 mb-6 space-y-2">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-[#be1e2d] to-[#991522] text-white flex items-center justify-center shadow-lg shadow-red-900/40">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>

                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-500/10 border border-red-500/20 text-[#f3bd2e] text-[11px] font-mono font-bold uppercase tracking-wider">
                    <span>Universal Stage Desk</span>
                </div>

                <h1 class="text-2xl font-sora font-black text-white tracking-tight">
                    Custom Judge Panel
                </h1>

                <p class="text-xs font-mono text-slate-400 max-w-xs mx-auto">
                    Enter the 4-digit secret passcode to open stage evaluation panel for Judge 1, 2, and 3.
                </p>
            </div>

            <!-- Error Notification -->
            @if($errors->has('pin'))
                <div class="relative z-10 mb-5 p-3.5 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs font-mono text-left flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $errors->first('pin') }}</span>
                </div>
            @endif

            <!-- Form -->
            <form x-ref="pinForm" method="POST" action="{{ route('custom-judge.authenticate') }}" autocomplete="off" class="relative z-10 space-y-6">
                @csrf

                <!-- 4 Digit Indicator Dots & Input -->
                <div class="space-y-3">
                    <div class="relative">
                        <input type="password"
                               name="pin"
                               x-model="pin"
                               maxlength="4"
                               required
                               autofocus
                               autocomplete="new-password"
                               placeholder="••••"
                               class="w-full text-center text-4xl font-mono tracking-[0.6em] font-black py-4 px-3 bg-white/5 border-2 border-white/20 focus:border-[#be1e2d] focus:bg-white/10 rounded-2xl outline-none transition text-white placeholder:text-white/20">
                    </div>

                    <!-- 4 Visual Dots Indicator -->
                    <div class="flex items-center justify-center gap-3 py-1">
                        <template x-for="i in [0, 1, 2, 3]">
                            <div class="w-3.5 h-3.5 rounded-full border transition-all duration-200"
                                 :class="pin.length > i ? 'bg-[#be1e2d] border-[#be1e2d] scale-110 shadow-[0_0_8px_#be1e2d]' : 'bg-transparent border-white/20'">
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Touch Keypad (Optimized for Tablets/iPad & Mobile on Stage) -->
                <div class="grid grid-cols-3 gap-2.5 pt-2 font-mono">
                    @for($i = 1; $i <= 9; $i++)
                        <button type="button" @click="addDigit('{{ $i }}')"
                                class="py-3.5 rounded-2xl bg-white/5 hover:bg-white/10 active:bg-white/20 border border-white/10 text-xl font-bold text-white transition active:scale-95 cursor-pointer shadow-xs">
                            {{ $i }}
                        </button>
                    @endfor
                    <button type="button" @click="clear()"
                            class="py-3.5 rounded-2xl bg-white/5 hover:bg-white/10 active:bg-white/20 border border-white/10 text-xs font-bold text-slate-400 hover:text-white transition active:scale-95 cursor-pointer uppercase">
                        Clear
                    </button>
                    <button type="button" @click="addDigit('0')"
                            class="py-3.5 rounded-2xl bg-white/5 hover:bg-white/10 active:bg-white/20 border border-white/10 text-xl font-bold text-white transition active:scale-95 cursor-pointer shadow-xs">
                        0
                    </button>
                    <button type="button" @click="backspace()"
                            class="py-3.5 rounded-2xl bg-white/5 hover:bg-white/10 active:bg-white/20 border border-white/10 text-slate-400 hover:text-white transition active:scale-95 cursor-pointer flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"/></svg>
                    </button>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full py-4 rounded-2xl bg-gradient-to-r from-[#be1e2d] to-[#991522] hover:brightness-110 active:scale-98 text-white font-bold text-sm tracking-wider uppercase transition shadow-lg shadow-red-950/40 cursor-pointer">
                    Unlock Judge Panel
                </button>

                <!-- Passcode Hint -->
                <div class="pt-2 text-center">
                    <span class="text-[11px] font-mono text-slate-500">
                        Default Stage PIN: <strong class="text-amber-400 font-bold">9999</strong> (or 2026)
                    </span>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-4 text-center text-xs font-mono text-slate-500 border-t border-white/5">
        QUAF 2026 &bull; Secure Adjudication System &bull; Unified Jury Desk
    </footer>

</body>
</html>
