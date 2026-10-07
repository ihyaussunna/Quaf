<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#ffffff">
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'QUAF — Markaz Cultural Festival 2026' }} | Ihyaussunna Students Union</title>
    <meta name="description" content="Official Public Platform for QUAF Markaz Cultural Festival 2026. Confluence of eloquence, arts, and intellectual heritage organized by Ihyaussunna Students Union, Jamia Markaz.">
    <meta name="keywords" content="QUAF, Markaz Cultural Festival, Ihyaussunna Students Union, QUAF 2026, Jamia Markaz, Arts Festival, Live Results, Adabic Inheritance">

    <!-- Open Graph & Social Cards -->
    <meta property="og:title" content="{{ $title ?? 'QUAF — Markaz Cultural Festival 2026' }}">
    <meta property="og:description" content="Official Festival Platform of QUAF organized by Ihyaussunna Students Union, Markazu Saquafathi Sunniyya.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/quaf-title-logo.png') }}">
    <meta name="twitter:card" content="summary_large_image">

    <!-- Google Fonts (Multilingual: Sora, Anek Malayalam, Amiri, JetBrains Mono) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Anek+Malayalam:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Rockwell Web Font Declarations for Scores and Large Metrics */
        @font-face {
            font-family: 'Rockwell';
            src: local('Rockwell Light'), local('Rockwell-Light'), url('/fonts/Rockwell-Light.woff2') format('woff2');
            font-weight: 300;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rockwell';
            src: local('Rockwell'), local('Rockwell Regular'), local('Rockwell-Regular'), url('/fonts/Rockwell-Regular.woff2') format('woff2');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rockwell';
            src: local('Rockwell Bold'), local('Rockwell-Bold'), url('/fonts/Rockwell-Bold.woff2') format('woff2');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rockwell';
            src: local('Rockwell Extra Bold'), local('Rockwell-ExtraBold'), url('/fonts/Rockwell-Extra-Bold.woff2') format('woff2');
            font-weight: 800;
            font-style: normal;
            font-display: swap;
        }

        :root {
            --fest-red: #be1e2d;
            --fest-gold: #f3bd2e;
            --fest-orange: #f58220;
            --fest-blue: #005c94;
            --fest-green: #009444;
            --slate-50: #f8fafc;
            --slate-900: #0f172a;
            --glass-bg: rgba(255, 255, 255, 0.72);
            --glass-border: rgba(226, 232, 240, 0.85);
        }

        body, .font-sora {
            font-family: 'Sora', sans-serif !important;
        }
        .font-rockwell {
            font-family: 'Rockwell', 'Rockwell Std', serif !important;
        }
        .font-mono, .font-jetbrains {
            font-family: 'JetBrains Mono', monospace !important;
        }
        .font-anek, .font-malayalam, :lang(ml) {
            font-family: 'Anek Malayalam', sans-serif !important;
        }
        .font-amiri, :lang(ar) {
            font-family: 'Amiri', serif !important;
        }

        /* Glassmorphism utility */
        .glass-panel {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .glass-panel-dark {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(51, 65, 85, 0.6);
        }

        /* Smooth marquee */
        @keyframes marquee {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-50%); }
        }
        .animate-marquee {
            display: inline-flex;
            width: max-content;
            animation: marquee 35s linear infinite;
        }
        .animate-marquee:hover {
            animation-play-state: paused;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-900 font-sora antialiased selection:bg-[#be1e2d] selection:text-white min-h-[100dvh] flex flex-col relative w-full overflow-x-clip"
      x-data="{ scrolled: false, mobileOpen: false, showStudentModal: false }"
      @scroll.window="scrolled = (window.pageYOffset > 20)">

    <!-- Top Announcement Bar / Live Alert -->
    @php
        $urgentAlert = \App\Models\Announcement::where('is_active', true)->where('priority', 'urgent')->latest()->first();
    @endphp
    @if($urgentAlert)
        <div class="bg-amber-100 border-b border-amber-300 text-amber-950 text-xs sm:text-sm px-4 py-2 sticky top-0 z-50 shadow-xs">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 overflow-hidden">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono uppercase tracking-wider bg-amber-500 text-white font-bold animate-pulse">URGENT</span>
                    <span class="font-medium truncate">{{ $urgentAlert->title }}: {{ $urgentAlert->message }}</span>
                </div>
                <span class="text-[11px] font-mono opacity-60 shrink-0">Official Dispatch</span>
            </div>
        </div>
    @endif

    <!-- Global Header (Apple-inspired Sticky Navigation with Black Glassmorphism) -->
    <header class="sticky top-0 z-40 transition-all duration-300 bg-slate-950/95 backdrop-blur-md border-b border-white/10 text-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <!-- Brand Logo & Identity -->
                <a href="{{ route('home.view') }}" class="flex items-center gap-3 group py-2 shrink-0">
                    <img src="{{ asset('images/dashboard-logo.svg') }}" 
                         alt="QUAF" 
                         height="40"
                         style="height: 40px; max-height: 40px; width: auto; object-fit: contain;"
                         class="h-9 sm:h-10 w-auto object-contain transition-transform duration-300 group-hover:scale-[1.02]">
                </a>

                <!-- Desktop Center Navigation Links -->
                <nav class="hidden lg:flex items-center gap-6 xl:gap-8 text-xs font-semibold uppercase tracking-wider">
                    <a href="{{ route('home.view') }}" class="transition-colors hover:text-white {{ request()->routeIs('home.view') || request()->routeIs('home') ? 'text-[#f3bd2e] font-bold border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-300' }}">Home</a>
                    <a href="{{ route('results.index') }}" class="transition-colors hover:text-white {{ request()->routeIs('results.*') ? 'text-[#f3bd2e] font-bold border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-300' }}">Results</a>
                    <a href="{{ route('schedule.index') }}" class="transition-colors hover:text-white {{ request()->routeIs('schedule.*') ? 'text-[#f3bd2e] font-bold border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-300' }}">Schedule</a>
                    <a href="{{ route('gallery.index') }}" class="transition-colors hover:text-white {{ request()->routeIs('gallery.*') ? 'text-[#f3bd2e] font-bold border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-300' }}">Gallery</a>
                    <a href="{{ route('news.index') }}" class="transition-colors hover:text-white {{ request()->routeIs('news.*') ? 'text-[#f3bd2e] font-bold border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-300' }}">News</a>
                    <a href="{{ route('media.index') }}" class="transition-colors hover:text-white {{ request()->routeIs('media.*') || request()->routeIs('videos.*') ? 'text-[#f3bd2e] font-bold border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-300' }}">Media</a>
                    <a href="{{ route('brochure.index') }}" class="transition-colors hover:text-white {{ request()->routeIs('brochure.*') ? 'text-[#f3bd2e] font-bold border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-300' }}">Brochure</a>
                </nav>

                <!-- Right Action CTAs: Student Portal Button -->
                <div class="hidden sm:flex items-center gap-3">
                    <button @click="showStudentModal = true" type="button" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider bg-gradient-to-r from-[#be1e2d] to-[#991522] text-white hover:brightness-110 shadow-sm transition-all cursor-pointer flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Student Portal</span>
                    </button>
                </div>

                <!-- Mobile Menu Button: Profile / Student Portal Icon -->
                <div class="flex lg:hidden items-center gap-2">
                    <button @click="showStudentModal = true" type="button" class="w-11 h-11 flex items-center justify-center text-slate-200 hover:text-[#f3bd2e] rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 transition-colors cursor-pointer" aria-label="Student Portal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Apple Glassy Fullscreen Navigation Overlay -->
        <div x-show="mobileOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/95 backdrop-blur-2xl z-50 flex flex-col justify-between p-6 sm:p-10 text-white lg:hidden overflow-y-auto"
             style="display: none;"
             @click.self="mobileOpen = false">
            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/quaf-title-logo.png') }}" alt="QUAF" class="h-8 w-auto object-contain brightness-0 invert">
                    <span class="font-mono text-xs uppercase tracking-widest text-[#f3bd2e] font-bold">Navigation</span>
                </div>
                <button type="button" @click="mobileOpen = false" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white cursor-pointer transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="space-y-4 my-auto py-6">
            <a href="{{ route('home.view') }}" @click="mobileOpen = false" class="block font-sora text-2xl font-black text-white hover:text-[#f3bd2e] transition-colors py-2 {{ request()->routeIs('home.view') || request()->routeIs('home') ? 'text-[#f3bd2e]' : '' }}">
                Home Page
            </a>
            <a href="{{ route('results.index') }}" @click="mobileOpen = false" class="block font-sora text-2xl font-black text-white hover:text-[#f3bd2e] transition-colors py-2 {{ request()->routeIs('results.*') ? 'text-[#f3bd2e]' : '' }}">
                Festival Results
            </a>
            <a href="{{ route('schedule.index') }}" @click="mobileOpen = false" class="block font-sora text-2xl font-black text-white hover:text-[#f3bd2e] transition-colors py-2 {{ request()->routeIs('schedule.*') ? 'text-[#f3bd2e]' : '' }}">
                Festival Schedule
            </a>
            <a href="{{ route('gallery.index') }}" @click="mobileOpen = false" class="block font-sora text-2xl font-black text-white hover:text-[#f3bd2e] transition-colors py-2 {{ request()->routeIs('gallery.*') ? 'text-[#f3bd2e]' : '' }}">
                Photo Gallery
            </a>
            <a href="{{ route('news.index') }}" @click="mobileOpen = false" class="block font-sora text-2xl font-black text-white hover:text-[#f3bd2e] transition-colors py-2 {{ request()->routeIs('news.*') ? 'text-[#f3bd2e]' : '' }}">
                Festival News & Dispatches
            </a>
            <a href="{{ route('media.index') }}" @click="mobileOpen = false" class="block font-sora text-2xl font-black text-white hover:text-[#f3bd2e] transition-colors py-2 {{ request()->routeIs('media.*') || request()->routeIs('videos.*') ? 'text-[#f3bd2e]' : '' }}">
                Media & Videos
            </a>
            <a href="{{ route('brochure.index') }}" @click="mobileOpen = false" class="block font-sora text-2xl font-black text-white hover:text-[#f3bd2e] transition-colors py-2 {{ request()->routeIs('brochure.*') ? 'text-[#f3bd2e]' : '' }}">
                Official Brochure
            </a>
            </nav>

            <div class="pt-4 border-t border-white/10 flex items-center justify-between text-xs font-mono text-slate-400">
                <button type="button" @click="mobileOpen = false; showStudentModal = true" class="flex items-center gap-2 text-white hover:text-[#f3bd2e] font-bold cursor-pointer">
                    <svg class="w-4 h-4 text-[#f3bd2e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Student Portal (Chest Number)</span>
                </button>
                <span>QUAF</span>
            </div>
        </div>
    </header>

    <!-- Main Content Slot -->
    <main class="flex-1 w-full pb-16 lg:pb-0">
        @yield('content')
    </main>

    <!-- Global Premium Dark Footer (Section 30) -->
    <footer class="bg-slate-950 text-slate-400 border-t border-slate-800/80 pt-14 pb-12 mt-16 sm:mt-24 relative overflow-hidden">
        <!-- Subtle Glow in Footer -->
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-red-600/5 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-amber-500/5 rounded-full blur-[140px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10 pb-12 border-b border-slate-800">
                
                <!-- Col 1: Festival Identity (2 cols on large) -->
                <div class="lg:col-span-2 space-y-4">
                    <a href="{{ route('home.view') }}" class="inline-block">
                        <img src="{{ asset('images/dashboard-logo.svg') }}" 
                             alt="QUAF" 
                             height="44" 
                             style="height: 44px; max-height: 44px; width: auto; object-fit: contain;" 
                             class="h-11 sm:h-12 w-auto object-contain drop-shadow-xs">
                    </a>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed max-w-sm">
                        QUAF Markaz Cultural Festival 2026. The grand confluence of eloquence, arts, and intellectual heritage uniting premier collegiate groups across 140+ cultural and literary disciplines.
                    </p>
                    <div class="flex flex-wrap items-center gap-2 text-[11px] font-mono text-slate-400 pt-1">
                        <span class="px-2.5 py-1 rounded bg-slate-900 border border-slate-800 text-amber-400 font-semibold">31 OCT — 01 NOV 2026</span>
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

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-3 text-center sm:text-left">
                <p>© 2026 QUAF — Ihyaussunna-Markaz Students' Union, Markazu Saquafathi Sunniyya. All Rights Reserved.</p>
                <div class="flex items-center gap-3 font-mono text-[11px]">
                    <span class="text-slate-400">Powered by QUAF Management Platform</span>
                    <span>•</span>
                    <a href="{{ route('home.view') }}" class="text-[#f3bd2e] hover:underline">Official Portal</a>
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
            <button @click="showStudentModal = true" 
                    type="button"
                    class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors text-slate-500 hover:text-[#be1e2d] cursor-pointer">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-[10px] tracking-tight font-sans">Portal</span>
            </button>

        </div>
    </nav>

    <!-- Student Portal Chest Number Lookup Modal (Apple Glassy Dialog) -->
    <div x-show="showStudentModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-md"
         style="display: none;"
         @keydown.escape.window="showStudentModal = false">
        
        <!-- Backdrop click to close -->
        <div class="fixed inset-0" @click="showStudentModal = false"></div>

        <!-- Glassy Modal Box -->
        <div x-show="showStudentModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             class="relative w-full max-w-md bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100 z-10">
            
            <button @click="showStudentModal = false" 
                    type="button" 
                    class="absolute top-5 right-5 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-800 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="flex items-center gap-3 mb-5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#be1e2d] to-red-500 flex items-center justify-center text-white shadow-md shadow-red-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-sora text-xl font-bold text-slate-900">Student Portal</h3>
                    <p class="text-xs text-slate-500">Access your registered programs, schedule and pass</p>
                </div>
            </div>

            <form action="{{ route('student.login.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="student_identifier_modal" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                        Chest Number / Student ID
                    </label>
                    <div class="relative">
                        <input type="text" 
                               name="identifier" 
                               id="student_identifier_modal" 
                               required 
                               placeholder="e.g. 101, A204 or STU-102" 
                               class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-base placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#be1e2d] focus:border-transparent transition">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1.5">
                        Enter your assigned festival chest number or registered student ID to view your profile directly.
                    </p>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-3.5 px-6 rounded-2xl bg-[#be1e2d] hover:bg-[#a01824] text-white font-bold text-sm tracking-wide shadow-lg shadow-red-600/25 transition duration-150 flex items-center justify-center gap-2 cursor-pointer">
                        <span>Open My Portal</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>

            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Looking for staff login?</span>
                <a href="{{ route('login') }}" class="font-bold text-[#be1e2d] hover:underline">Official Login &rarr;</a>
            </div>
        </div>
    </div>

</body>
</html>
