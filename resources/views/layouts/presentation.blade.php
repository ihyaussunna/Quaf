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

    <title>{{ $title ?? 'Presentation Desk' }} | QUAF Season 09</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Malayalam:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- HTML5 QR Code Scanner Library (Bundled locally with CDN fallback) -->
    <script src="{{ asset('vendor/html5-qrcode.min.js') }}"></script>
    <script>
        if (typeof Html5Qrcode === 'undefined') {
            var fallbackScript = document.createElement('script');
            fallbackScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js';
            document.head.appendChild(fallbackScript);
        }
    </script>
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
            <a href="{{ route('presentation.dashboard') }}" class="flex items-center gap-3 group py-2">
                <img src="{{ asset('images/quaf-logo-favicon-white.svg') }}" alt="QUAF Logo" class="h-10 w-auto object-contain">
                <div class="leading-none">
                    <span class="font-sora font-black tracking-wider text-base text-white block">QUAF</span>
                    <span class="text-[9px] font-mono tracking-widest text-amber-400 block uppercase font-bold mt-0.5">Presentation Desk</span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Portal Badge -->
        <div class="px-4 py-3 bg-[#181a1e] border-b border-slate-800/60">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Presentation Desk</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            </div>
            <div class="text-xs font-semibold text-slate-200 mt-0.5">Coins & Gift Distribution</div>
            <div class="text-[11px] text-slate-400 font-anek">കോയിൻ & സമ്മാന വിതരണം</div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-4 space-y-1.5 text-xs font-sora text-slate-400">
            <!-- 1. Presentation Counter -->
            <a href="{{ route('presentation.dashboard') }}" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('presentation.dashboard') ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Presentation Counter</span>
            </a>

            <!-- 2. Summary Report -->
            <a href="{{ route('presentation.report') }}" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('presentation.report') ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Distribution Report</span>
            </a>

            <!-- 3. Return to Admin (if admin) -->
            @if(auth()->user()?->isAdmin())
                <div class="pt-4 border-t border-slate-800/80">
                    <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Navigation</span>
                </div>
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all hover:text-white hover:bg-slate-800/60 text-slate-400">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path></svg>
                    <span>Back to Admin Panel</span>
                </a>
            @endif
        </nav>

        <!-- User Profile & Logout Footer -->
        <div class="p-3 border-t border-slate-800 bg-[#16181c]">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-white/5 border border-slate-700/60 p-1 flex items-center justify-center flex-shrink-0">
                        <img src="{{ asset('images/quaf-logo-favicon-white.svg') }}" alt="User" class="w-full h-full object-contain">
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-semibold text-slate-200 truncate">{{ auth()->user()?->name ?? 'Presentation Desk' }}</div>
                        <div class="text-[10px] font-mono text-slate-400 truncate">{{ auth()->user()?->email }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-slate-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">
        <!-- Top Mobile Header -->
        <header class="lg:hidden h-16 bg-[#121417] text-white flex items-center justify-between px-4 border-b border-slate-800 sticky top-0 z-30">
            <button @click="sidebarOpen = true" class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Presentation Desk</span>
            </div>
            <a href="{{ route('presentation.dashboard') }}" class="p-2 text-amber-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </a>
        </header>

        <!-- Flash Messages -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="text-xs sm:text-sm font-semibold flex-1">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="text-xs sm:text-sm font-semibold flex-1">
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 flex items-start gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div class="text-xs space-y-1">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
