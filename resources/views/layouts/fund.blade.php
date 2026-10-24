<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overflow-x-clip">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#121417">
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Fund Collection Desk' }} | QUAF Season 09</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Malayalam:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f8fafc] text-slate-900 font-sora antialiased min-h-screen flex overflow-x-hidden max-w-full"
      x-data="{ sidebarOpen: false }">

    <!-- Mobile Backdrop -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs lg:hidden" style="display: none;"></div>

    <!-- Sidebar Navigation -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed lg:static top-0 bottom-0 left-0 z-50 w-64 bg-[#121417] text-slate-300 border-r border-slate-800 flex flex-col transition-transform duration-300 overflow-y-auto shadow-xl">
        
        <!-- Brand -->
        <div class="h-20 flex items-center justify-between px-4 border-b border-slate-800/80 flex-shrink-0">
            <a href="{{ route('fund.dashboard') }}" class="flex items-center gap-3 group py-2">
                <img src="{{ asset('images/quaf-logo-favicon-white.svg') }}" alt="QUAF Logo" class="h-10 w-auto object-contain">
                <div class="leading-none">
                    <span class="font-sora font-black tracking-wider text-base text-white block">QUAF</span>
                    <span class="text-[9px] font-mono tracking-widest text-emerald-400 block uppercase font-bold mt-0.5">Fund Desk</span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Portal Badge -->
        <div class="px-4 py-3 bg-[#181a1e] border-b border-slate-800/60">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Fund Collection Desk</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            </div>
            <div class="text-xs font-semibold text-slate-200 mt-0.5">Festival Fund Receipts</div>
            <div class="text-[11px] text-slate-400 font-anek">ഫണ്ട് കളക്ഷൻ & രസീത് വിതരണം</div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-4 space-y-1.5 text-xs font-sora text-slate-400">
            <a href="{{ route('fund.dashboard') }}" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('fund.dashboard') ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/20' : 'hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Receipts Desk</span>
            </a>

            <a href="{{ route('fund.ledger') }}" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('fund.ledger') ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/20' : 'hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Collection Ledger</span>
            </a>

            <a href="{{ route('home') }}" target="_blank"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all hover:text-white hover:bg-slate-800/60 text-slate-400">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                <span>Festival Website ↗</span>
            </a>
        </nav>

        <!-- Current User & Sign Out -->
        <div class="p-4 border-t border-slate-800/80 bg-[#15181c]">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/5 border border-slate-700/60 p-1.5 flex items-center justify-center flex-shrink-0">
                    <img src="{{ asset('images/quaf-logo-favicon-white.svg') }}" alt="Fund Desk" class="w-full h-full object-contain">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-slate-200 truncate">{{ Auth::user()->name ?? 'Fund Desk' }}</div>
                    <div class="text-[10px] text-emerald-400 font-mono truncate">Desk Operator</div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-slate-800 hover:bg-rose-950/40 text-slate-300 hover:text-rose-300 border border-slate-700/60 hover:border-rose-800/60 text-xs font-medium transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 max-w-full overflow-x-hidden overflow-y-auto bg-[#fafafa]">
        <!-- Top Mobile Header -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sticky top-0 z-30 shadow-2xs lg:hidden">
            <button @click="sidebarOpen = true" class="p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-bold text-slate-800 font-mono">QUAF FUND DESK</span>
            </div>
            <div class="w-8"></div>
        </header>

        <!-- Main Workspace -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-medium flex items-center justify-between shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-medium flex items-center justify-between shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
