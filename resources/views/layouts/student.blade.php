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

    <title>{{ $title ?? 'Student Portal' }} | QUAF</title>

    <!-- Google Fonts (Multilingual: Anek Malayalam, JetBrains Mono, Sora, Amiri, Noto Nastaliq Urdu) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Anek+Malayalam:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&family=Noto+Nastaliq+Urdu:wght@400;700&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 font-sora antialiased min-h-[100dvh] flex flex-col w-full overflow-x-clip"
      x-data="{ mobileOpen: false }">

    <!-- Global Header (Apple-inspired Sticky Navigation matching Public Site) -->
    <header class="hidden lg:block sticky top-0 z-40 transition-all duration-300 text-white bg-slate-950/95 backdrop-blur-md border-b border-white/10 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <!-- Desktop Navigation Links (Light font weight) -->
                <nav class="flex items-center gap-6 xl:gap-8 text-xs font-light uppercase tracking-wider">
                    <a href="{{ route('home.view') }}" class="transition-colors hover:text-white {{ request()->routeIs('home.view') || request()->routeIs('home') ? 'text-[#f3bd2e] font-normal border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-300 font-light' }}">Home</a>
                    <a href="{{ route('results.index') }}" class="transition-colors hover:text-white {{ request()->routeIs('results.*') ? 'text-[#f3bd2e] font-normal border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-300 font-light' }}">Results</a>
                    <a href="{{ route('schedule.index') }}" class="transition-colors hover:text-white {{ request()->routeIs('schedule.*') ? 'text-[#f3bd2e] font-normal border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-300 font-light' }}">Schedule</a>
                    <a href="{{ route('gallery.index') }}" class="transition-colors hover:text-white {{ request()->routeIs('gallery.*') ? 'text-[#f3bd2e] font-normal border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-300 font-light' }}">Gallery</a>
                    <a href="{{ route('news.index') }}" class="transition-colors hover:text-white {{ request()->routeIs('news.*') ? 'text-[#f3bd2e] font-normal border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-300 font-light' }}">News</a>
                    <a href="{{ route('media.index') }}" class="transition-colors hover:text-white {{ request()->routeIs('media.*') || request()->routeIs('videos.*') ? 'text-[#f3bd2e] font-normal border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-300 font-light' }}">Media</a>
                    <a href="{{ route('brochure.index') }}" class="transition-colors hover:text-white {{ request()->routeIs('brochure.*') ? 'text-[#f3bd2e] font-normal border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-300 font-light' }}">Brochure</a>
                </nav>

                <!-- Right Action CTAs: Student Portal Button & Sign Out -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('student.dashboard') }}" class="px-4 py-2 rounded-xl text-xs font-medium uppercase tracking-wider bg-gradient-to-r from-[#be1e2d] to-[#991522] text-white hover:brightness-110 shadow-sm transition-all cursor-pointer flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Student Portal</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 rounded-xl text-xs font-medium uppercase tracking-wider text-slate-300 hover:text-white hover:bg-white/10 transition cursor-pointer">
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-6xl w-full mx-auto p-3 sm:p-6 md:p-8 pb-24 lg:pb-10 min-w-0 overflow-x-clip">
        @yield('content')
    </main>

    <!-- Global Premium Dark Footer -->
    <footer class="bg-slate-950 text-slate-400 border-t border-slate-800/80 pt-14 pb-28 lg:pb-14 mt-12 sm:mt-16 relative overflow-hidden">
        <!-- Subtle Glow in Footer -->
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-red-600/5 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-[#be1e2d]/5 rounded-full blur-[140px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10 pb-12 border-b border-slate-800">
                
                <!-- Col 1: Festival Identity -->
                <div class="lg:col-span-2 space-y-4">
                    <a href="{{ route('home.view') }}" class="inline-block">
                        <img src="{{ asset('images/dashboard-logo.svg') }}" 
                             alt="QUAF" 
                             style="height: 38px; max-height: 38px; width: auto; object-fit: contain;" 
                             class="h-9 sm:h-10 w-auto object-contain drop-shadow-xs">
                    </a>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed max-w-sm">
                        QUAF Markaz Cultural Festival 2026. The grand confluence of eloquence, arts, and intellectual heritage uniting premier collegiate groups across 140+ cultural and literary disciplines.
                    </p>
                    <div class="flex flex-wrap items-center gap-2 text-[11px] font-mono text-slate-400 pt-1">
                        <span class="px-2.5 py-1 rounded bg-slate-900 border border-slate-800 text-red-400 font-semibold">31 OCT — 01 NOV 2026</span>
                        <span class="px-2.5 py-1 rounded bg-slate-900 border border-slate-800 text-slate-300">JAMIA MARKAZ KARANTHUR</span>
                    </div>
                </div>

                <!-- Col 2: Navigation Links -->
                <div>
                    <h4 class="font-sora text-white font-bold text-xs uppercase tracking-wider mb-4">Festival Portal</h4>
                    <ul class="space-y-2.5 text-xs sm:text-sm">
                        <li><a href="{{ route('home.view') }}" class="hover:text-white transition-colors">Home Page</a></li>
                        <li><a href="{{ route('results.index') }}" class="hover:text-white transition-colors">Live Results</a></li>
                        <li><a href="{{ route('schedule.index') }}" class="hover:text-white transition-colors">Program Schedule</a></li>
                        <li><a href="{{ route('groups.index') }}" class="hover:text-white transition-colors">Academic Groups</a></li>
                        <li><a href="{{ route('gallery.index') }}" class="hover:text-white transition-colors">Photo Gallery</a></li>
                        <li><a href="{{ route('news.index') }}" class="hover:text-white transition-colors">Festival Journal</a></li>
                        <li><a href="{{ route('media.index') }}" class="hover:text-white transition-colors">Media Highlights</a></li>
                    </ul>
                </div>

                <!-- Col 3: Organization -->
                <div>
                    <h4 class="font-sora text-white font-bold text-xs uppercase tracking-wider mb-4">Organization</h4>
                    <p class="text-xs text-slate-400 leading-relaxed mb-3">
                        Organized by the <strong class="text-slate-200">Ihyaussunna-Markaz Students' Union</strong> under Markazu Saquafathi Sunniyya.
                    </p>
                    <div class="space-y-1.5 text-xs text-slate-400 font-mono">
                        <div>Jamia Markaz Campus</div>
                        <div>Karanthur, Kozhikode, Kerala</div>
                    </div>
                </div>

            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-4 text-center sm:text-left">
                <p>© {{ date('Y') }} QUAF — Ihyaussunna-Markaz Students' Union, Markazu Saquafathi Sunniyya. All Rights Reserved.</p>
                <div class="flex items-center justify-center sm:justify-end gap-2 text-[11px] text-slate-400">
                    <span>Designed and Developed by</span>
                    <a href="https://ahrazdesign.com" target="_blank" rel="noopener noreferrer" class="inline-flex items-center transition-opacity hover:opacity-80 group" title="Ahraz Design">
                        <img src="{{ asset('images/ahraz-design-footer.svg') }}" 
                             alt="Ahraz Design" 
                             class="h-4 sm:h-4.5 w-auto object-contain inline-block"
                             style="height: 16px; max-height: 18px; width: auto;">
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Fixed Bottom Navigation Bar (Apple / App Native Style) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/90 backdrop-blur-xl border-t border-slate-200/90 shadow-[0_-4px_24px_rgba(0,0,0,0.08)] px-2 py-1.5 transition-all">
        <div class="grid grid-cols-5 items-center justify-around text-center max-w-lg mx-auto">
            
            <!-- 1. Home -->
            <a href="{{ route('home.view') }}" 
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ request()->routeIs('home.view') || request()->routeIs('home') ? 'text-[#be1e2d] font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="text-[10px] tracking-tight font-sans">Home</span>
            </a>

            <!-- 2. Results -->
            <a href="{{ route('results.index') }}" 
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors relative {{ request()->routeIs('results.*') ? 'text-[#be1e2d] font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="text-[10px] tracking-tight font-sans">Results</span>
            </a>

            <!-- 3. Center Navigation Menu (Elevated Apple Glassy 3-Lines Hamburger) -->
            <button @click="mobileOpen = !mobileOpen" 
                    type="button"
                    class="flex flex-col items-center justify-center -mt-4 group cursor-pointer"
                    aria-label="Navigation Menu">
                <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-[#be1e2d] via-red-600 to-[#f3bd2e] text-white flex items-center justify-center shadow-lg shadow-red-600/30 ring-4 ring-white transition-all transform active:scale-95 group-hover:scale-105">
                    <svg class="w-6 h-6 transition-transform duration-300" :class="mobileOpen ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </div>
                <span class="text-[10px] font-bold tracking-tight text-slate-700 mt-1">Menu</span>
            </button>

            <!-- 4. Schedule -->
            <a href="{{ route('schedule.index') }}" 
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ request()->routeIs('schedule.*') ? 'text-[#be1e2d] font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="text-[10px] tracking-tight font-sans">Schedule</span>
            </a>

            <!-- 5. Student Portal Profile -->
            <a href="{{ route('student.dashboard') }}" 
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ request()->routeIs('student.*') ? 'text-[#be1e2d] font-bold' : 'text-slate-500 hover:text-[#be1e2d]' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-[10px] tracking-tight font-sans">Portal</span>
            </a>

        </div>
    </nav>

    <!-- iPhone-Style Glassy Modal Popup for Mobile Navigation -->
    <div x-show="mobileOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/30 backdrop-blur-md lg:hidden"
         style="display: none;"
         @keydown.escape.window="mobileOpen = false"
         @click.self="mobileOpen = false">

        <!-- iPhone Glass Card Shape with Spring Pop-up Animation -->
        <div x-show="mobileOpen"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-350 transform"
             x-transition:enter-start="opacity-0 scale-90 translate-y-8"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-8"
             class="relative w-full max-w-sm bg-slate-900/40 backdrop-blur-2xl backdrop-saturate-150 border border-white/25 rounded-[2.5rem] p-6 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5),inset_0_1px_1px_rgba(255,255,255,0.35),inset_0_-1px_1px_rgba(255,255,255,0.1)] text-center text-white overflow-hidden ring-1 ring-white/10">

            <!-- Dynamic Indicator -->
            <div class="w-12 h-1 bg-white/40 rounded-full mx-auto mb-3 shadow-xs"></div>

            <!-- Close Button -->
            <button type="button" 
                    @click="mobileOpen = false" 
                    class="absolute top-5 right-5 w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 border border-white/25 flex items-center justify-center text-white/90 hover:text-white transition cursor-pointer backdrop-blur-md"
                    aria-label="Close menu">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- Brand Badge -->
            <div class="flex items-center justify-center gap-2 mb-3 relative z-10">
                <span class="text-[11px] font-mono uppercase tracking-widest text-[#f3bd2e] font-bold">QUAF Navigation</span>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1 py-1 relative z-10">
                <a href="{{ route('home.view') }}" @click="mobileOpen = false" class="block py-2 px-4 rounded-xl text-sm font-semibold text-white/90 hover:text-white hover:bg-white/15 transition">Home</a>
                <a href="{{ route('results.index') }}" @click="mobileOpen = false" class="block py-2 px-4 rounded-xl text-sm font-semibold text-white/90 hover:text-white hover:bg-white/15 transition">Festival Results</a>
                <a href="{{ route('schedule.index') }}" @click="mobileOpen = false" class="block py-2 px-4 rounded-xl text-sm font-semibold text-white/90 hover:text-white hover:bg-white/15 transition">Festival Schedule</a>
                <a href="{{ route('gallery.index') }}" @click="mobileOpen = false" class="block py-2 px-4 rounded-xl text-sm font-semibold text-white/90 hover:text-white hover:bg-white/15 transition">Photo Gallery</a>
                <a href="{{ route('news.index') }}" @click="mobileOpen = false" class="block py-2 px-4 rounded-xl text-sm font-semibold text-white/90 hover:text-white hover:bg-white/15 transition">Festival News</a>
                <a href="{{ route('media.index') }}" @click="mobileOpen = false" class="block py-2 px-4 rounded-xl text-sm font-semibold text-white/90 hover:text-white hover:bg-white/15 transition">Media & Videos</a>
                <a href="{{ route('brochure.index') }}" @click="mobileOpen = false" class="block py-2 px-4 rounded-xl text-sm font-semibold text-white/90 hover:text-white hover:bg-white/15 transition">Official Brochure</a>
            </nav>

            <div class="mt-4 pt-3 border-t border-white/15 relative z-10 flex gap-2">
                <a href="{{ route('student.dashboard') }}" @click="mobileOpen = false" class="flex-1 py-2 px-3 rounded-xl bg-[#be1e2d] text-white text-xs font-semibold uppercase tracking-wider text-center">
                    Dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2 px-3 rounded-xl bg-white/15 text-white text-xs font-semibold uppercase tracking-wider">
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
