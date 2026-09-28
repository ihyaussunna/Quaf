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

    <title>{{ $title ?? 'Student Portal' }} | QUAF 09</title>

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

    <!-- Top Navigation Bar (Light Theme, Mobile Optimized) -->
    <header class="h-16 sm:h-20 bg-white border-b border-slate-200 flex items-center justify-between px-3 sm:px-6 sticky top-0 z-40 shadow-xs">
        <div class="flex items-center gap-2 sm:gap-4 min-w-0">
            <a href="{{ route('student.dashboard') }}" class="flex items-center gap-2 sm:gap-3 shrink-0">
                <img src="{{ asset('images/dashboard-logo-dark.svg') }}" alt="QUAF Logo" class="h-9 sm:h-11 w-auto object-contain">
                <div>
                    <span class="font-bold tracking-wider text-xs sm:text-base text-slate-900">STUDENT PORTAL</span>
                    <span class="text-[9px] sm:text-[10px] font-mono tracking-widest text-[#be1e2d] block uppercase font-bold">DELEGATE CONSOLE</span>
                </div>
            </a>
        </div>

        <!-- Navigation Links (Desktop) -->
        <nav class="hidden md:flex items-center gap-1 text-xs font-mono">
            <a href="{{ route('student.dashboard') }}" class="px-4 py-2 rounded-xl transition-all {{ request()->routeIs('student.dashboard') ? 'bg-[#f3bd2e] text-white font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                Dashboard
            </a>
            <a href="{{ route('student.idcard') }}" class="px-4 py-2 rounded-xl transition-all {{ request()->routeIs('student.idcard') ? 'bg-[#f3bd2e] text-white font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                Digital QR Badge
            </a>
            <a href="{{ route('student.certificates') }}" class="px-4 py-2 rounded-xl transition-all {{ request()->routeIs('student.certificates') ? 'bg-[#f3bd2e] text-white font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                My Certificates
            </a>
        </nav>

        <!-- Right: Profile & Logout -->
        <div class="flex items-center gap-2 sm:gap-4 shrink-0">
            <div class="text-right hidden sm:block">
                <span class="text-xs font-bold text-slate-900 block truncate max-w-[120px]">{{ Auth::user()->name }}</span>
                <span class="text-[10px] font-mono text-[#f3bd2e] font-bold">Participant</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="app-tap px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-mono font-bold bg-slate-100 text-slate-700 hover:text-red-700 hover:bg-red-50 border border-slate-200 transition-all">
                    Sign Out
                </button>
            </form>
        </div>
    </header>

    <!-- Main Content (With safe padding for mobile bottom bar) -->
    <main class="flex-1 max-w-6xl w-full mx-auto p-3 sm:p-6 md:p-8 pb-24 md:pb-8 min-w-0 overflow-x-clip">
        @yield('content')
    </main>

    <!-- Mobile Bottom App Bar for Student Portal -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] px-2 pt-1 pb-[max(0.6rem,env(safe-area-inset-bottom,0.6rem))] flex items-center justify-around select-none">
        <a href="{{ route('student.dashboard') }}" class="app-tap flex flex-col items-center py-1 px-3 rounded-2xl text-center {{ request()->routeIs('student.dashboard') ? 'text-[#be1e2d] font-bold bg-red-50/80' : 'text-slate-500' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="text-[10px] font-mono">Overview</span>
        </a>
        <a href="{{ route('student.idcard') }}" class="app-tap flex flex-col items-center py-1 px-3 rounded-2xl text-center {{ request()->routeIs('student.idcard') ? 'text-[#be1e2d] font-bold bg-red-50/80' : 'text-slate-500' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            <span class="text-[10px] font-mono">QR Badge</span>
        </a>
        <a href="{{ route('student.certificates') }}" class="app-tap flex flex-col items-center py-1 px-3 rounded-2xl text-center {{ request()->routeIs('student.certificates') ? 'text-[#be1e2d] font-bold bg-red-50/80' : 'text-slate-500' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            <span class="text-[10px] font-mono">Certificates</span>
        </a>
    </nav>

    <!-- Footer -->
    <footer class="border-t border-slate-200 py-6 text-center text-slate-500 text-xs font-mono bg-white hidden md:block">
        QUAF '09 Student Portal • Ihyaussunna Students Union, Markaz
    </footer>

</body>
</html>
