<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Judge Access PIN | QUAF 09</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Gayathri:wght@400;700&family=JetBrains+Mono:wght@400;500;600;700&family=Manjari:wght@400;700&family=Noto+Nastaliq+Urdu:wght@400;700&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 font-sora antialiased min-h-screen flex flex-col justify-between selection:bg-[#f3bd2e] selection:text-white">

    <!-- Header -->
    <header class="h-20 bg-white border-b border-slate-200 px-6 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#f3bd2e] to-[#be1e2d] flex items-center justify-center p-0.5 shadow-sm">
                <div class="w-full h-full bg-white rounded-[9px] flex items-center justify-center font-sora font-black text-sm text-[#f3bd2e]">Q9</div>
            </div>
            <div>
                <span class="font-sora font-black tracking-wider text-base text-slate-900 group-hover:text-[#f3bd2e] transition-colors">QUAF 09</span>
                <span class="text-[10px] font-mono tracking-widest text-slate-500 block uppercase">Judges Portal</span>
            </div>
        </a>

        <a href="{{ route('login') }}" class="text-xs font-mono text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 transition-all">
            Standard Login &rarr;
        </a>
    </header>

    <!-- Main Card -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden"
             x-data="{
                 pin: '',
                 addDigit(d) {
                     if (this.pin.length < 6) this.pin += d;
                 },
                 backspace() {
                     this.pin = this.pin.slice(0, -1);
                 },
                 clear() {
                     this.pin = '';
                 }
             }">

            <!-- Top Badge -->
            <div class="text-center space-y-2 mb-6">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-[#f3bd2e] shadow-xs">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-sora font-black text-slate-900">Judge Portal Login</h1>
                <p class="text-xs font-mono text-slate-500">
                    Enter the 4-digit confidential access PIN provided by the Festival Desk.
                </p>
            </div>

            <!-- Error Notification -->
            @if($errors->has('pin'))
                <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-mono flex items-start gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $errors->first('pin') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('judge.login.submit') }}" autocomplete="off" class="space-y-6">
                @csrf

                <!-- Display Box -->
                <div>
                    <input type="password"
                           name="pin"
                           x-model="pin"
                           maxlength="6"
                           required
                           autofocus
                           autocomplete="new-password"
                           readonly
                           onfocus="this.removeAttribute('readonly');"
                           placeholder="••••"
                           class="w-full text-center text-3xl font-mono tracking-[0.6em] font-black py-4 px-3 bg-slate-50 border-2 border-slate-300 focus:border-[#f3bd2e] focus:bg-white rounded-2xl outline-none transition-all text-slate-900 placeholder:text-slate-300">
                </div>

                <!-- On-Screen Numeric Keypad for Quick Mobile / Tablet Tap -->
                <div class="grid grid-cols-3 gap-2.5">
                    @for($i = 1; $i <= 9; $i++)
                        <button type="button" @click="addDigit('{{ $i }}')"
                                class="py-3.5 rounded-2xl bg-slate-100 hover:bg-amber-50 hover:text-[#f3bd2e] active:bg-amber-100 text-slate-800 font-mono font-bold text-lg border border-slate-200 shadow-2xs transition-all">
                            {{ $i }}
                        </button>
                    @endfor
                    <button type="button" @click="clear()"
                            class="py-3.5 rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-600 font-mono font-semibold text-xs border border-slate-200 transition-all uppercase">
                        Clear
                    </button>
                    <button type="button" @click="addDigit('0')"
                            class="py-3.5 rounded-2xl bg-slate-100 hover:bg-amber-50 hover:text-[#f3bd2e] active:bg-amber-100 text-slate-800 font-mono font-bold text-lg border border-slate-200 shadow-2xs transition-all">
                        0
                    </button>
                    <button type="button" @click="backspace()"
                            class="py-3.5 rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-600 font-mono font-semibold text-sm border border-slate-200 transition-all flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414-6.414a2 2 0 011.414-.586H19a2 2 0 012 2v10a2 2 0 01-2 2h-9.172a2 2 0 01-1.414-.586L3 12z"/></svg>
                    </button>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        :disabled="pin.length < 4"
                        class="w-full py-4 bg-[#f3bd2e] disabled:bg-slate-300 text-white font-mono font-bold text-sm uppercase rounded-2xl shadow-lg shadow-[#f3bd2e]/20 hover:brightness-105 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
                    <span>Enter Portal</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                <span class="text-[11px] font-mono text-slate-400 block">
                    QUAF 09 • Confidential Evaluation Engine
                </span>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-4 text-center text-slate-400 text-xs font-mono">
        Students Union Ihyaussunna • Markazu Saquafathi Sunniyya
    </footer>

</body>
</html>
