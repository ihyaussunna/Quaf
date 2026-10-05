<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#ffffff">
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Judges Portal' }} | QUAF 09</title>

    <!-- Google Fonts (Multilingual: Anek Malayalam, JetBrains Mono, Sora, Amiri, Noto Nastaliq Urdu) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Anek+Malayalam:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&family=Noto+Nastaliq+Urdu:wght@400;700&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 font-sora antialiased min-h-[100dvh] flex flex-col w-full overflow-x-clip">

    <!-- Top Evaluation Header (Light Theme, Mobile App Friendly) -->
    <header class="h-16 sm:h-20 bg-white border-b border-slate-200 flex items-center justify-between px-3 sm:px-6 sticky top-0 z-40 shadow-xs">
        <div class="flex items-center gap-2 sm:gap-4 min-w-0">
            <a href="{{ route('judge.dashboard') }}" class="flex items-center gap-2 sm:gap-3 group shrink-0">
                <img src="{{ asset('images/dashboard-logo-dark.svg') }}" alt="QUAF Logo" class="h-9 sm:h-10 w-auto object-contain max-h-10" style="height: 38px; width: auto; max-width: 125px; object-fit: contain;">
                <div>
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <span class="font-bold tracking-wider text-xs sm:text-base text-slate-900 group-hover:text-[#be1e2d] transition-colors">JUDGES JURY</span>
                        <span class="px-1.5 py-0.5 rounded-full bg-red-50 text-[#be1e2d] border border-red-200 text-[9px] sm:text-[10px] font-mono font-bold">JURY</span>
                    </div>
                    <span class="text-[9px] sm:text-[10px] font-mono tracking-widest text-slate-500 block uppercase">EVALUATION CONSOLE</span>
                </div>
            </a>
        </div>

        <!-- Center: Quick Judge Info (hidden on small mobile) -->
        <div class="hidden lg:flex items-center gap-2 px-4 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono">
            <span class="text-slate-500">Juror:</span>
            <span class="text-slate-900 font-bold">{{ Auth::user()->name }}</span>
            <span class="text-slate-300">•</span>
            <span class="text-[#f3bd2e] font-semibold">{{ Auth::user()->judgeProfile->designation ?? 'Official Adjudicator' }}</span>
        </div>

        <!-- Right: Actions with app-tap -->
        <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
            <a href="{{ route('judge.dashboard') }}" class="app-tap px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-mono font-bold bg-slate-100 text-slate-700 hover:text-slate-900 hover:bg-slate-200 border border-slate-200 transition-all">
                Programs
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="app-tap px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-mono font-bold bg-slate-100 text-slate-700 hover:text-red-700 hover:bg-red-50 border border-slate-200 transition-all">
                    Sign Out
                </button>
            </form>
        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="max-w-6xl w-full mx-auto px-4 sm:px-6 mt-4">
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-mono flex items-center justify-between shadow-xs">
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-6xl w-full mx-auto px-4 sm:px-6 mt-4">
            <div class="p-3.5 rounded-xl bg-red-50 border border-red-300 text-red-800 text-xs font-mono flex items-center justify-between shadow-xs">
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-1 max-w-6xl w-full mx-auto p-3 sm:p-6 md:p-8 min-w-0 max-w-full overflow-x-clip">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 py-4 px-4 text-center text-slate-500 text-[11px] font-mono bg-white safe-bottom-padding">
        QUAF '09 Adjudication Engine • Ihyaussunna Students Union • Strictly Confidential Scoring System
    </footer>

</body>
</html>
