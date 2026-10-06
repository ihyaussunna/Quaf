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

    <title>{{ $title ?? 'QUAF 9.0 — Markaz Cultural Festival 2026' }} | Ihyaussunna Students Union</title>
    <meta name="description" content="Official Public Platform for QUAF 9.0 Markaz Cultural Festival 2026. Confluence of eloquence, arts, and intellectual heritage organized by Ihyaussunna Students Union, Jamia Markaz.">
    <meta name="keywords" content="QUAF 9.0, Markaz Cultural Festival, Ihyaussunna Students Union, QUAF 2026, Jamia Markaz, Arts Festival, Live Results, Adabic Inheritance">

    <!-- Open Graph & Social Cards -->
    <meta property="og:title" content="{{ $title ?? 'QUAF 9.0 — Markaz Cultural Festival 2026' }}">
    <meta property="og:description" content="Official Festival Platform of QUAF 9.0 organized by Ihyaussunna Students Union, Markazu Saquafathi Sunniyya.">
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
      x-data="{ scrolled: false, mobileOpen: false }"
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

    <!-- Global Header (Apple-inspired Sticky Navigation with Glassmorphism) -->
    <header class="sticky top-0 z-40 transition-all duration-300"
            :class="scrolled ? 'bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm' : 'bg-white/80 backdrop-blur-sm border-b border-slate-200/60'">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <!-- Brand Logo & Identity -->
                <a href="{{ route('home.view') }}" class="flex items-center gap-3 group py-2 shrink-0">
                    <img src="{{ asset('images/dashboard-logo-dark.svg') }}" 
                         alt="QUAF 9.0" 
                         height="40"
                         style="height: 40px; max-height: 40px; width: auto; object-fit: contain;"
                         class="h-9 sm:h-10 w-auto object-contain transition-transform duration-300 group-hover:scale-[1.02]">
                    <div class="hidden sm:block border-l border-slate-200 pl-3 leading-tight">
                        <span class="font-mono text-[9px] uppercase tracking-widest text-[#be1e2d] font-bold block">SEASON 09</span>
                        <span class="font-sora text-[11px] font-semibold text-slate-500 block">2026</span>
                    </div>
                </a>

                <!-- Desktop Center Navigation Links -->
                <nav class="hidden lg:flex items-center gap-6 xl:gap-8 text-xs font-semibold uppercase tracking-wider">
                    <a href="{{ route('home.view') }}" class="transition-colors hover:text-[#be1e2d] {{ request()->routeIs('home.view') || request()->routeIs('home') ? 'text-[#be1e2d] font-bold border-b-2 border-[#be1e2d] pb-1' : 'text-slate-600' }}">Home</a>
                    <a href="{{ route('results.index') }}" class="transition-colors hover:text-[#be1e2d] {{ request()->routeIs('results.*') ? 'text-[#be1e2d] font-bold border-b-2 border-[#be1e2d] pb-1' : 'text-slate-600' }}">Results</a>
                    <a href="{{ route('schedule.index') }}" class="transition-colors hover:text-[#be1e2d] {{ request()->routeIs('schedule.*') ? 'text-[#be1e2d] font-bold border-b-2 border-[#be1e2d] pb-1' : 'text-slate-600' }}">Schedule</a>
                    <a href="{{ route('gallery.index') }}" class="transition-colors hover:text-[#be1e2d] {{ request()->routeIs('gallery.*') ? 'text-[#be1e2d] font-bold border-b-2 border-[#be1e2d] pb-1' : 'text-slate-600' }}">Gallery</a>
                    <a href="{{ route('news.index') }}" class="transition-colors hover:text-[#be1e2d] {{ request()->routeIs('news.*') ? 'text-[#be1e2d] font-bold border-b-2 border-[#be1e2d] pb-1' : 'text-slate-600' }}">News</a>
                    <a href="{{ route('media.index') }}" class="transition-colors hover:text-[#be1e2d] {{ request()->routeIs('media.*') || request()->routeIs('videos.*') ? 'text-[#be1e2d] font-bold border-b-2 border-[#be1e2d] pb-1' : 'text-slate-600' }}">Media</a>
                    <a href="{{ route('brochure.index') }}" class="transition-colors hover:text-[#be1e2d] {{ request()->routeIs('brochure.*') ? 'text-[#be1e2d] font-bold border-b-2 border-[#be1e2d] pb-1' : 'text-slate-600' }}">Brochure</a>
                </nav>

                <!-- Right Action CTAs -->
                <div class="hidden sm:flex items-center gap-3">
                    <!-- Live Results Pill -->
                    <a href="{{ route('results.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider bg-red-50 text-[#be1e2d] border border-red-200/80 hover:bg-red-100/80 transition-all shadow-2xs">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-600"></span>
                        </span>
                        <span>Live Results</span>
                    </a>

                    <!-- Portal Login / Profile -->
                    @auth
                        @php
                            $user = Auth::user();
                            $targetRoute = $user->isAdmin() ? route('admin.dashboard') : ($user->isJudge() ? route('judge.dashboard') : ($user->role === 'green_room_coordinator' ? route('greenroom.index') : ($user->isLeader() ? route('leader.dashboard') : route('student.dashboard'))));
                        @endphp
                        <a href="{{ $targetRoute }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-800 transition-all flex items-center gap-2 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="truncate max-w-[120px]">{{ $user->name }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider bg-gradient-to-r from-[#be1e2d] to-[#991522] text-white hover:brightness-110 shadow-sm transition-all">
                            Portal Login
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button (Comfortable 44px+ touch target) -->
                <div class="flex lg:hidden items-center gap-2">
                    <a href="{{ route('results.index') }}" class="p-2.5 rounded-xl bg-red-50 text-[#be1e2d] border border-red-200 text-xs font-bold" aria-label="Results">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-600"></span>
                        </span>
                    </a>
                    <button @click="mobileOpen = !mobileOpen" type="button" class="w-11 h-11 flex items-center justify-center text-slate-700 hover:text-slate-900 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-colors" aria-label="Toggle Navigation">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div x-show="mobileOpen" @click="mobileOpen = false" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 lg:hidden" style="display: none;" x-transition.opacity></div>

        <div x-show="mobileOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="-translate-y-4 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="-translate-y-4 opacity-0"
             class="lg:hidden bg-white border-b border-slate-200 px-4 pt-3 pb-6 space-y-1 shadow-lg relative z-50"
             style="display: none;">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-2">
                <span class="font-mono text-xs text-slate-500 uppercase font-bold tracking-wider">Festival Navigation</span>
                <button @click="mobileOpen = false" class="text-xs font-semibold text-slate-500 hover:text-slate-800">Close ✕</button>
            </div>
            <a href="{{ route('home.view') }}" class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm font-semibold {{ request()->routeIs('home.view') || request()->routeIs('home') ? 'bg-red-50 text-[#be1e2d]' : 'text-slate-700 hover:bg-slate-50' }}">
                <span>Home Page</span>
            </a>
            <a href="{{ route('results.index') }}" class="flex items-center justify-between py-2.5 px-3 rounded-xl text-sm font-semibold {{ request()->routeIs('results.*') ? 'bg-red-50 text-[#be1e2d]' : 'text-slate-700 hover:bg-slate-50' }}">
                <span>Live Results</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-red-100 text-red-700 font-bold uppercase">Live</span>
            </a>
            <a href="{{ route('schedule.index') }}" class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm font-semibold {{ request()->routeIs('schedule.*') ? 'bg-red-50 text-[#be1e2d]' : 'text-slate-700 hover:bg-slate-50' }}">
                <span>Festival Schedule</span>
            </a>
            <a href="{{ route('gallery.index') }}" class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm font-semibold {{ request()->routeIs('gallery.*') ? 'bg-red-50 text-[#be1e2d]' : 'text-slate-700 hover:bg-slate-50' }}">
                <span>Photo Gallery</span>
            </a>
            <a href="{{ route('news.index') }}" class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm font-semibold {{ request()->routeIs('news.*') ? 'bg-red-50 text-[#be1e2d]' : 'text-slate-700 hover:bg-slate-50' }}">
                <span>Festival News & Dispatches</span>
            </a>
            <a href="{{ route('media.index') }}" class="flex items-center gap-3 py-2.5 px-3 rounded-xl text-sm font-semibold {{ request()->routeIs('media.*') || request()->routeIs('videos.*') ? 'bg-red-50 text-[#be1e2d]' : 'text-slate-700 hover:bg-slate-50' }}">
                <span>Media & Videos</span>
            </a>
            <a href="{{ route('brochure.index') }}" class="flex items-center justify-between py-2.5 px-3 rounded-xl text-sm font-semibold {{ request()->routeIs('brochure.*') ? 'bg-red-50 text-[#be1e2d]' : 'text-slate-700 hover:bg-slate-50' }}">
                <span>Official Brochure</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-amber-100 text-amber-800 font-bold">14 Pages</span>
            </a>

            <div class="pt-3 border-t border-slate-100 mt-2">
                @auth
                    @php
                        $user = Auth::user();
                        $targetRoute = $user->isAdmin() ? route('admin.dashboard') : ($user->isJudge() ? route('judge.dashboard') : ($user->role === 'green_room_coordinator' ? route('greenroom.index') : ($user->isLeader() ? route('leader.dashboard') : route('student.dashboard'))));
                    @endphp
                    <a href="{{ $targetRoute }}" class="w-full py-3 rounded-xl text-xs font-bold uppercase tracking-wider bg-slate-100 text-slate-900 border border-slate-300 flex items-center justify-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Dashboard: {{ $user->name }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full py-3 rounded-xl text-xs font-bold uppercase tracking-wider bg-gradient-to-r from-[#be1e2d] to-[#991522] text-white flex items-center justify-center">
                        Portal Login
                    </a>
                @endauth
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-10 pb-12 border-b border-slate-800">
                
                <!-- Col 1: Festival Identity (2 cols on large) -->
                <div class="lg:col-span-2 space-y-4">
                    <a href="{{ route('home.view') }}" class="inline-block">
                        <img src="{{ asset('images/dashboard-logo.svg') }}" 
                             alt="QUAF 9.0" 
                             height="44" 
                             style="height: 44px; max-height: 44px; width: auto; object-fit: contain;" 
                             class="h-11 sm:h-12 w-auto object-contain drop-shadow-xs">
                    </a>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed max-w-sm">
                        QUAF 9.0 — Markaz Cultural Festival 2026. The grand confluence of eloquence, arts, and intellectual heritage uniting premier collegiate groups across 120+ cultural and literary disciplines.
                    </p>
                    <div class="flex flex-wrap items-center gap-2 text-[11px] font-mono text-slate-400 pt-1">
                        <span class="px-2.5 py-1 rounded bg-slate-900 border border-slate-800 text-amber-400 font-semibold">06 OCT — 01 NOV 2026</span>
                        <span class="px-2.5 py-1 rounded bg-slate-900 border border-slate-800 text-slate-300">CENTRAL FESTIVAL ARENA</span>
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

                <!-- Col 3: Resources & Verifications -->
                <div>
                    <h4 class="font-sora text-white font-bold text-xs uppercase tracking-wider mb-4">Verification & Pubs</h4>
                    <ul class="space-y-2.5 text-xs sm:text-sm">
                        <li><a href="{{ route('brochure.index') }}" class="hover:text-white transition-colors flex items-center gap-1.5">
                            <span>Official Brochure</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-slate-800 text-amber-400">PDF</span>
                        </a></li>
                        <li><a href="{{ route('verify.index') }}" class="hover:text-white transition-colors">Verification Hub</a></li>
                        <li><a href="{{ route('verify.certificate', 'SAMPLE') }}" class="hover:text-white transition-colors">Certificate Verify</a></li>
                        <li><a href="{{ route('verify.student', 'SAMPLE') }}" class="hover:text-white transition-colors">Student Delegate Pass</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">Festival Concept & Theme</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition-colors">Campus Venue & Contact</a></li>
                    </ul>
                </div>

                <!-- Col 4: Organization -->
                <div>
                    <h4 class="font-sora text-white font-bold text-xs uppercase tracking-wider mb-4">Organization</h4>
                    <p class="text-xs text-slate-400 leading-relaxed mb-3">
                        Organized by the <strong class="text-slate-200">Ihyaussunna Students Union (ISU)</strong> under Markazu Saquafathi Sunniyya.
                    </p>
                    <div class="space-y-1.5 text-xs text-slate-400 font-mono">
                        <div>Jamia Markaz Campus</div>
                        <div>Karanthur, Kozhikode, Kerala</div>
                        <div class="text-[#f3bd2e] pt-1">Theme: Ādabīc Inheritance</div>
                    </div>
                    <div class="pt-4">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-300 hover:text-white px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            <span>Staff & Judge Login</span>
                        </a>
                    </div>
                </div>

            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-3 text-center sm:text-left">
                <p>© 2026 QUAF 9.0 — Ihyaussunna Students Union, Markazu Saquafathi Sunniyya. All Rights Reserved.</p>
                <div class="flex items-center gap-3 font-mono text-[11px]">
                    <span class="text-slate-400">Powered by QUAF Management Platform</span>
                    <span>•</span>
                    <a href="{{ route('home.view') }}" class="text-[#f3bd2e] hover:underline">Official Portal</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Fixed Bottom Navigation Bar (Apple / App Native Style) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/90 backdrop-blur-xl border-t border-slate-200/90 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] px-2 py-1.5 transition-transform duration-300">
        <div class="grid grid-cols-5 items-center justify-around text-center max-w-lg mx-auto">
            
            <!-- Home -->
            <a href="{{ route('home.view') }}" 
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ request()->routeIs('home.view') || request()->routeIs('home') ? 'text-[#be1e2d] font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="text-[10px] tracking-tight font-sans">Home</span>
            </a>

            <!-- Results -->
            <a href="{{ route('results.index') }}" 
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors relative {{ request()->routeIs('results.*') ? 'text-[#be1e2d] font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <span class="absolute top-1 right-3 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                </span>
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="text-[10px] tracking-tight font-sans">Results</span>
            </a>

            <!-- Schedule -->
            <a href="{{ route('schedule.index') }}" 
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ request()->routeIs('schedule.*') ? 'text-[#be1e2d] font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="text-[10px] tracking-tight font-sans">Schedule</span>
            </a>

            <!-- Groups -->
            <a href="{{ route('groups.index') }}" 
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ request()->routeIs('groups.*') ? 'text-[#be1e2d] font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="text-[10px] tracking-tight font-sans">Groups</span>
            </a>

            <!-- Menu Drawer Button -->
            <button @click="mobileOpen = !mobileOpen" 
                    type="button"
                    class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors"
                    :class="mobileOpen ? 'text-[#be1e2d] font-bold' : 'text-slate-500 hover:text-slate-800'">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <span class="text-[10px] tracking-tight font-sans">Menu</span>
            </button>

        </div>
    </nav>

</body>
</html>
