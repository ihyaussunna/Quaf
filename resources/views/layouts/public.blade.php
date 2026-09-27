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

    <title>{{ $title ?? 'QUAF' }} | Ihyaussunna Students Union</title>
    <meta name="description" content="Official Festival Platform organized by Ihyaussunna Students Union, Markazu Saquafathi Sunniyya.">

    <!-- Google Fonts (Multilingual: Anek Malayalam, JetBrains Mono, Sora, Manjari, Gayathri, Amiri, Noto Nastaliq Urdu) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Anek+Malayalam:wght@300;400;500;600;700;800&family=Gayathri:wght@400;700&family=JetBrains+Mono:wght@400;500;600;700;800&family=Manjari:wght@400;700&family=Noto+Nastaliq+Urdu:wght@400;700&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Rockwell Web Font Declarations */
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
            src: local('Rockwell'), local('Rockwell Regular'), local('Rockwell-Regular'), url('/fonts/Rockwell-Regular.woff2') format('woff2');
            font-weight: 500;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rockwell';
            src: local('Rockwell Bold'), local('Rockwell-Bold'), local('Rockwell'), url('/fonts/Rockwell-Bold.woff2') format('woff2');
            font-weight: 600;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rockwell';
            src: local('Rockwell Bold'), local('Rockwell-Bold'), local('Rockwell'), url('/fonts/Rockwell-Bold.woff2') format('woff2');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rockwell';
            src: local('Rockwell Extra Bold'), local('Rockwell-ExtraBold'), local('Rockwell-Bold'), url('/fonts/Rockwell-Extra-Bold.woff2') format('woff2');
            font-weight: 800;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rockwell';
            src: local('Rockwell Extra Bold'), local('Rockwell-ExtraBold'), local('Rockwell-Bold'), url('/fonts/Rockwell-Extra-Bold.woff2') format('woff2');
            font-weight: 900;
            font-style: normal;
            font-display: swap;
        }

        /* Public Portal Typography: Titles & UI -> Sora, Headings/Display -> Rockwell, Numbers & Chest Numbers -> JetBrains Mono, Malayalam -> Anek Malayalam */
        body,
        h1, h2, h3, h4, h5, h6,
        .font-sora {
            font-family: 'Sora', 'Malayalam Sangam MN', 'Malayalam MN', 'Manjari', sans-serif !important;
        }

        .font-rockwell {
            font-family: 'Rockwell', 'Rockwell Std', serif !important;
        }

        .font-mono,
        [data-mono],
        .font-jetbrains {
            font-family: 'JetBrains Mono', monospace !important;
        }

        .font-anek,
        .font-malayalam,
        .malayalam-text,
        :lang(ml),
        [data-script="malayalam"] {
            font-family: 'Anek Malayalam', 'Malayalam Sangam MN', 'Manjari', sans-serif !important;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 font-sora antialiased selection:bg-[#be1e2d] selection:text-white min-h-[100dvh] flex flex-col relative w-full overflow-x-clip">

    <!-- Top Announcement Bar / Live Alert -->
    @php
        $urgentAlert = \App\Models\Announcement::where('is_active', true)->where('priority', 'urgent')->latest()->first();
    @endphp
    @if($urgentAlert)
        <div class="bg-amber-100 border-b border-amber-300 text-amber-950 text-xs sm:text-sm px-4 py-2 flex items-center justify-between sticky top-0 z-50 shadow-sm">
            <div class="max-w-7xl mx-auto flex items-center gap-2 overflow-hidden">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono uppercase tracking-wider bg-amber-500 text-white font-bold animate-pulse">URGENT</span>
                <span class="font-medium truncate">{{ $urgentAlert->title }}: {{ $urgentAlert->message }}</span>
            </div>
        </div>
    @endif

    <!-- Main Navigation Bar (Light Theme) -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs transition-all" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <!-- Brand Logo & Identity -->
                <a href="{{ route('home') }}" class="flex items-center group py-2">
                    <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF Logo" class="h-12 sm:h-14 w-auto object-contain">
                </a>

                <!-- Desktop Nav Links -->
                <nav class="hidden md:flex items-center gap-6 lg:gap-8 text-sm font-medium">
                    <a href="{{ route('home') }}" class="transition-colors hover:text-[#f3bd2e] {{ request()->routeIs('home') ? 'text-[#f3bd2e] font-bold border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-600' }}">Home</a>
                    <a href="{{ route('results.index') }}" class="transition-colors hover:text-[#f3bd2e] {{ request()->routeIs('results.*') ? 'text-[#f3bd2e] font-bold border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-600' }}">Results</a>
                    <a href="{{ route('news.index') }}" class="transition-colors hover:text-[#f3bd2e] {{ request()->routeIs('news.*') ? 'text-[#f3bd2e] font-bold border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-600' }}">News</a>
                    <a href="{{ route('gallery.index') }}" class="transition-colors hover:text-[#f3bd2e] {{ request()->routeIs('gallery.*') ? 'text-[#f3bd2e] font-bold border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-600' }}">Gallery</a>
                    <a href="{{ route('videos.index') }}" class="transition-colors hover:text-[#f3bd2e] {{ request()->routeIs('videos.*') ? 'text-[#f3bd2e] font-bold border-b-2 border-[#f3bd2e] pb-1' : 'text-slate-600' }}">Videos</a>
                </nav>

                <!-- Action CTA -->
                <div class="hidden md:flex items-center gap-4">
                    @auth
                        @php
                            $user = Auth::user();
                            $targetRoute = $user->isAdmin() ? route('admin.dashboard') : ($user->isJudge() ? route('judge.dashboard') : ($user->role === 'green_room_coordinator' ? route('greenroom.index') : ($user->isLeader() ? route('leader.dashboard') : route('student.dashboard'))));
                        @endphp
                        <a href="{{ $targetRoute }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-amber-50 border border-slate-300 hover:border-[#f3bd2e]/40 text-slate-800 transition-all flex items-center gap-2 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ $user->name }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-gradient-to-r from-[#f3bd2e] to-[#be1e2d] text-white hover:brightness-105 shadow-md transition-all">
                            Portal Login
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button (Comfortable 44px Touch Target) -->
                <div class="flex md:hidden items-center gap-2">
                    @auth
                        @php
                            $user = Auth::user();
                            $targetRoute = $user->isAdmin() ? route('admin.dashboard') : ($user->isJudge() ? route('judge.dashboard') : ($user->role === 'green_room_coordinator' ? route('greenroom.index') : ($user->isLeader() ? route('leader.dashboard') : route('student.dashboard'))));
                        @endphp
                        <a href="{{ $targetRoute }}" class="p-2 rounded-xl bg-amber-50 text-[#f3bd2e] border border-amber-200 text-xs font-bold" title="Dashboard">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </a>
                    @endauth
                    <button @click="mobileOpen = !mobileOpen" type="button" class="w-10 h-10 flex items-center justify-center text-slate-700 hover:text-slate-900 focus:outline-none rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-colors" aria-label="Toggle Navigation">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Drawer Overlay & Content -->
        <div x-show="mobileOpen" @click="mobileOpen = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 md:hidden" style="display: none;" x-transition.opacity></div>

        <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="md:hidden absolute top-full left-0 right-0 bg-white border-b border-slate-200 px-4 pt-3 pb-6 space-y-2 shadow-xl z-50">
            
            <div class="grid grid-cols-2 gap-2 pb-2">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('home') ? 'bg-amber-50 text-[#f3bd2e] border border-amber-200' : 'text-slate-700 hover:bg-slate-50 bg-slate-50/60 border border-slate-200/60' }}">
                    <span>Home</span>
                </a>
                <a href="{{ route('results.index') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('results.*') ? 'bg-amber-50 text-[#f3bd2e] border border-amber-200' : 'text-slate-700 hover:bg-slate-50 bg-slate-50/60 border border-slate-200/60' }}">
                    <span>Results</span>
                </a>
                <a href="{{ route('news.index') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('news.*') ? 'bg-amber-50 text-[#f3bd2e] border border-amber-200' : 'text-slate-700 hover:bg-slate-50 bg-slate-50/60 border border-slate-200/60' }}">
                    <span>News</span>
                </a>
                <a href="{{ route('gallery.index') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('gallery.*') ? 'bg-amber-50 text-[#f3bd2e] border border-amber-200' : 'text-slate-700 hover:bg-slate-50 bg-slate-50/60 border border-slate-200/60' }}">
                    <span>Gallery</span>
                </a>
                <a href="{{ route('videos.index') }}" class="col-span-2 flex items-center justify-center gap-2.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('videos.*') ? 'bg-amber-50 text-[#f3bd2e] border border-amber-200' : 'text-slate-700 hover:bg-slate-50 bg-slate-50/60 border border-slate-200/60' }}">
                    <span>Stage Videos & Streams</span>
                </a>
            </div>

            <!-- Role Access Quick Links -->
            <div class="pt-3 border-t border-slate-200">
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400 block mb-2 px-1">Management Access</span>
                <div class="grid grid-cols-2 gap-1.5 text-xs font-mono">
                    <a href="{{ route('login') }}" class="p-2 rounded-lg bg-slate-50 hover:bg-amber-50 text-slate-700 border border-slate-200 text-center font-semibold">
                        Admin
                    </a>
                    <a href="{{ route('login') }}" class="p-2 rounded-lg bg-slate-50 hover:bg-amber-50 text-slate-700 border border-slate-200 text-center font-semibold">
                        Judges
                    </a>
                    <a href="{{ route('login') }}" class="p-2 rounded-lg bg-slate-50 hover:bg-amber-50 text-slate-700 border border-slate-200 text-center font-semibold">
                        Green Room
                    </a>
                    <a href="{{ route('login') }}" class="p-2 rounded-lg bg-slate-50 hover:bg-amber-50 text-slate-700 border border-slate-200 text-center font-semibold">
                        Group Leader
                    </a>
                </div>
            </div>

            <div class="pt-3">
                @auth
                    <a href="{{ $targetRoute }}" class="block text-center py-3 rounded-xl bg-gradient-to-r from-[#f3bd2e] to-[#be1e2d] text-white font-bold text-sm shadow-md">
                        Open My Dashboard ({{ $user->name }})
                    </a>
                @else
                    <a href="{{ route('login') }}" class="block text-center py-3 rounded-xl bg-gradient-to-r from-[#f3bd2e] to-[#be1e2d] text-white font-bold text-sm uppercase tracking-wider shadow-md">
                        Sign In to Portal
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between shadow-xs">
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-xl flex items-center justify-between shadow-xs">
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Content (With safe padding for mobile bottom bar and notch) -->
    <main class="flex-1 pb-[max(5.5rem,calc(4.5rem+env(safe-area-inset-bottom,0px)))] md:pb-0 min-w-0 w-full overflow-x-clip">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Mobile Bottom App Bar (Sticky Native-Style Thumb Navigation) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200/90 shadow-[0_-4px_25px_rgba(0,0,0,0.06)] px-2 pt-1.5 pb-[max(0.6rem,env(safe-area-inset-bottom,0.6rem))] flex items-center justify-around select-none">
        <a href="{{ route('home') }}" class="app-tap flex flex-col items-center py-1 px-3 rounded-2xl text-center transition-all {{ request()->routeIs('home') ? 'text-[#be1e2d] font-bold bg-red-50/80' : 'text-slate-500 hover:text-slate-800' }}">
            <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('home') ? 'stroke-current stroke-[2.5]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-[10px] font-mono tracking-tight">Home</span>
        </a>

        <a href="{{ route('results.index') }}" class="app-tap flex flex-col items-center py-1 px-3 rounded-2xl text-center transition-all {{ request()->routeIs('results.*') ? 'text-[#be1e2d] font-bold bg-red-50/80' : 'text-slate-500 hover:text-slate-800' }}">
            <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('results.*') ? 'stroke-current stroke-[2.5]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
            <span class="text-[10px] font-mono tracking-tight">Results</span>
        </a>

        <a href="{{ route('news.index') }}" class="app-tap flex flex-col items-center py-1 px-3 rounded-2xl text-center transition-all {{ request()->routeIs('news.*') ? 'text-[#be1e2d] font-bold bg-red-50/80' : 'text-slate-500 hover:text-slate-800' }}">
            <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('news.*') ? 'stroke-current stroke-[2.5]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
            <span class="text-[10px] font-mono tracking-tight">News</span>
        </a>

        <a href="{{ route('gallery.index') }}" class="app-tap flex flex-col items-center py-1 px-3 rounded-2xl text-center transition-all {{ request()->routeIs('gallery.*') ? 'text-[#be1e2d] font-bold bg-red-50/80' : 'text-slate-500 hover:text-slate-800' }}">
            <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('gallery.*') ? 'stroke-current stroke-[2.5]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span class="text-[10px] font-mono tracking-tight">Gallery</span>
        </a>

        @auth
            <a href="{{ $targetRoute }}" class="app-tap flex flex-col items-center py-1 px-3 rounded-2xl text-center text-[#be1e2d] font-bold bg-red-50/80">
                <div class="w-5 h-5 mb-0.5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] shadow-xs">✓</div>
                <span class="text-[10px] font-mono tracking-tight">Portal</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="app-tap flex flex-col items-center py-1 px-3 rounded-2xl text-center transition-all {{ request()->routeIs('login') ? 'text-[#be1e2d] font-bold bg-red-50/80' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                <span class="text-[10px] font-mono tracking-tight">Login</span>
            </a>
        @endauth
    </nav>

    <!-- Public Institutional Footer (100% Light Theme) -->
    <footer class="bg-white text-slate-600 border-t border-slate-200 pt-12 pb-16 sm:pb-12 mt-12 sm:mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 lg:gap-12 mb-10">
                <!-- Col 1: Festival Identity (Logo on side, Description on the side) -->
                <div class="sm:col-span-2">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6">
                        <!-- Larger Title Logo -->
                        <a href="{{ route('home') }}" class="shrink-0 block">
                            <img src="{{ asset('images/quaf-title-logo.png') }}" 
                                 alt="QUAF" 
                                 class="h-14 sm:h-20 md:h-24 w-auto object-contain max-w-[200px] sm:max-w-[240px]">
                        </a>

                        <!-- Description to the side of the logo (not underneath) -->
                        <div class="space-y-2">
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed max-w-md">
                                The premier collegiate arts and cultural confluence of Jamia Markaz. Organized by the Ihyaussunna Students Union, Markazu Saquafathi Sunniyya, fostering intellectual oratory, fine arts, and spiritual aesthetics.
                            </p>
                            <div class="flex flex-wrap items-center gap-2 text-[11px] font-mono text-slate-500 pt-0.5">
                                <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200">OCTOBER 24 - 28, 2026</span>
                                <span>•</span>
                                <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200">MARKAZ CAMPUS</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div>
                    <h4 class="font-sora text-slate-900 font-bold text-xs sm:text-sm tracking-wider uppercase mb-3 sm:mb-4">Festival Portal</h4>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ route('home') }}" class="text-slate-600 hover:text-[#f3bd2e] transition-colors">Home Page</a></li>
                        <li><a href="{{ route('results.index') }}" class="text-slate-600 hover:text-[#f3bd2e] transition-colors">Official Results</a></li>
                        <li><a href="{{ route('news.index') }}" class="text-slate-600 hover:text-[#f3bd2e] transition-colors">Festival Journal</a></li>
                        <li><a href="{{ route('gallery.index') }}" class="text-slate-600 hover:text-[#f3bd2e] transition-colors">Visual Gallery</a></li>
                        <li><a href="{{ route('videos.index') }}" class="text-slate-600 hover:text-[#f3bd2e] transition-colors">Stage Highlights</a></li>
                    </ul>
                </div>

                <!-- Col 3: Verifications & Management -->
                <div>
                    <h4 class="font-sora text-slate-900 font-bold text-xs sm:text-sm tracking-wider uppercase mb-3 sm:mb-4">Verifications</h4>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li>
                            <a href="{{ route('verify.certificate', 'SAMPLE') }}" class="text-slate-600 hover:text-[#f3bd2e] transition-colors flex items-center gap-1.5">
                                <span>Verify Certificate</span>
                                <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('login') }}" class="text-slate-600 hover:text-[#f3bd2e] transition-colors flex items-center gap-1.5">
                                <span>Judges & Staff Login</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('login') }}" class="text-slate-600 hover:text-[#f3bd2e] transition-colors flex items-center gap-1.5">
                                <span>Student / Leader Portal</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-200 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-3 text-center sm:text-left">
                <p>© 2026 Ihyaussunna Students Union, Markazu Saquafathi Sunniyya. All Rights Reserved.</p>
                <div class="flex items-center gap-2 font-mono text-[11px]">
                    <span class="text-[#f3bd2e] font-bold">Arts & Cultural Platform</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
