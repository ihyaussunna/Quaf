<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Student Portal Access | QUAF 09</title>

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
                <span class="text-[10px] font-mono tracking-widest text-[#f3bd2e] block uppercase font-bold">Student Portal</span>
            </div>
        </a>

        <a href="{{ route('login') }}" class="text-xs font-mono text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 transition-all">
            Official Login &rarr;
        </a>
    </header>

    <!-- Main Card -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">

            <!-- Top Emblem -->
            <div class="text-center space-y-2 mb-6">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-[#f3bd2e] shadow-xs">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-sora font-black text-slate-900">Student Portal Login</h1>
                <p class="text-xs font-mono text-slate-500">
                    Enter your Chest Number or Student ID to access your dashboard (no password required).
                </p>
            </div>

            <!-- Error Notification -->
            @if($errors->has('identifier'))
                <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-mono flex items-start gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $errors->first('identifier') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('student.login.submit') }}" autocomplete="off" class="space-y-6">
                @csrf

                <div class="space-y-2">
                    <label class="text-xs font-mono font-bold uppercase tracking-wider text-slate-700 block">
                        Chest Number / Student ID
                    </label>
                    <div class="relative">
                        <input type="text"
                               name="identifier"
                               value="{{ old('identifier') }}"
                               required
                               autofocus
                               autocomplete="off"
                               readonly
                               onfocus="this.removeAttribute('readonly');"
                               placeholder="e.g. QUAF-ST-1001 or 101"
                               class="w-full text-base font-mono font-bold py-3.5 px-4 bg-slate-50 border-2 border-slate-300 focus:border-[#f3bd2e] focus:bg-white rounded-2xl outline-none transition-all text-slate-900 placeholder:text-slate-400">
                    </div>
                    <p class="text-[11px] font-mono text-slate-400">
                        Use the ID from your official badge or registration slip.
                    </p>
                </div>

                <button type="submit"
                        class="w-full py-4 bg-[#f3bd2e] text-white font-mono font-bold text-sm uppercase rounded-2xl shadow-lg shadow-[#f3bd2e]/20 hover:brightness-105 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
                    <span>Access Student Portal</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-mono">
                <a href="{{ route('results.index') }}" class="text-[#f3bd2e] hover:underline">
                    &larr; Live Festival Results
                </a>
                <a href="{{ route('judge.login') }}" class="text-slate-500 hover:text-slate-800">
                    Judge PIN &rarr;
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-4 text-center text-slate-400 text-xs font-mono">
        Students Union Ihyaussunna • Markazu Saquafathi Sunniyya
    </footer>

</body>
</html>
