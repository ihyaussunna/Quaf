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

    <!-- Top Navigation Bar (Smooth Header, Smaller Logo, QR Badge Action) -->
    <header class="h-16 bg-white/95 backdrop-blur-md border-b border-slate-200/90 flex items-center justify-between px-3 sm:px-6 sticky top-0 z-40 shadow-xs">
        <!-- Left: Logo & Portal Identity -->
        <div class="flex items-center gap-2.5 sm:gap-4 min-w-0">
            <a href="{{ route('student.dashboard') }}" class="flex items-center gap-2 sm:gap-3 shrink-0 group">
                <img src="{{ asset('images/dashboard-logo-dark.svg') }}" 
                     alt="QUAF Logo" 
                     class="h-7 sm:h-8 max-h-8 w-auto object-contain transition-transform group-hover:scale-105">
                <div class="leading-tight">
                    <span class="font-bold tracking-wider text-xs sm:text-sm text-slate-900 block font-sora">STUDENT PORTAL</span>
                    <span class="text-[9px] sm:text-[10px] font-mono tracking-widest text-[#be1e2d] block uppercase font-bold">DELEGATE CONSOLE</span>
                </div>
            </a>
        </div>

        <!-- Center: Prominent Digital QR Badge Button (Replaced Nav Bar) -->
        <div class="flex items-center justify-center">
            <a href="{{ route('student.idcard') }}" 
               class="app-tap inline-flex items-center gap-2 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl text-xs font-mono font-bold bg-[#f3bd2e] hover:bg-amber-500 text-white shadow-xs shadow-amber-500/20 transition-all">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                <span class="hidden sm:inline">Digital QR Badge</span>
                <span class="sm:hidden text-[11px]">QR Badge</span>
            </a>
        </div>

        <!-- Right: Profile & Logout -->
        <div class="flex items-center gap-2 sm:gap-4 shrink-0">
            <div class="text-right hidden sm:block leading-tight">
                <span class="text-xs font-bold text-slate-900 block truncate max-w-[140px]">{{ Auth::user()->name }}</span>
                <span class="text-[10px] font-mono text-[#f3bd2e] font-bold">Participant</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="app-tap px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-mono font-bold bg-slate-100 text-slate-700 hover:text-red-700 hover:bg-red-50 border border-slate-200 transition-all cursor-pointer">
                    Sign Out
                </button>
            </form>
        </div>
    </header>

    <!-- Main Content (With safe padding for mobile bottom bar) -->
    <main class="flex-1 max-w-6xl w-full mx-auto p-3 sm:p-6 md:p-8 pb-24 md:pb-12 min-w-0 overflow-x-clip">
        @yield('content')
    </main>

    <!-- Global Premium Dark Footer -->
    <footer class="bg-slate-950 text-slate-400 border-t border-slate-800/80 pt-14 pb-28 sm:pb-32 lg:pb-14 mt-12 sm:mt-16 relative overflow-hidden">
        <!-- Subtle Glow in Footer -->
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-red-600/5 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-amber-500/5 rounded-full blur-[140px] pointer-events-none"></div>

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

    <!-- Mobile Fixed Bottom Navigation Bar (Exact Home Page / App Native Style) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/90 backdrop-blur-xl border-t border-slate-200/90 shadow-[0_-4px_24px_rgba(0,0,0,0.08)] px-2 py-1.5 transition-all">
        <div class="grid grid-cols-5 items-center justify-around text-center max-w-lg mx-auto">
            
            <!-- 1. Home -->
            <a href="{{ route('home.view') }}" 
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors text-slate-500 hover:text-slate-800">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="text-[10px] tracking-tight font-sans">Home</span>
            </a>

            <!-- 2. Results -->
            <a href="{{ route('results.index') }}" 
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors relative text-slate-500 hover:text-slate-800">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="text-[10px] tracking-tight font-sans">Results</span>
            </a>

            <!-- 3. Center Navigation Menu Button -->
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
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors text-slate-500 hover:text-slate-800">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="text-[10px] tracking-tight font-sans">Schedule</span>
            </a>

            <!-- 5. Student Portal Profile (Active Highlight) -->
            <a href="{{ route('student.dashboard') }}" 
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors text-[#be1e2d] font-bold">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-[10px] tracking-tight font-sans">Portal</span>
            </a>
        </div>
    </nav>

    <!-- Glassy Modal Popup for Mobile Navigation (From Home Page) -->
    <div x-show="mobileOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/65 backdrop-blur-md lg:hidden"
         style="display: none;"
         @keydown.escape.window="mobileOpen = false"
         @click.self="mobileOpen = false">

        <div x-show="mobileOpen"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-350 transform"
             x-transition:enter-start="opacity-0 scale-90 translate-y-8"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-8"
             class="relative w-full max-w-sm bg-slate-950/90 backdrop-blur-3xl border border-white/20 rounded-[2.5rem] p-6 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.8)] text-center text-white overflow-hidden ring-1 ring-white/15">

            <!-- Dynamic Indicator / Pill Grabber -->
            <div class="w-12 h-1.5 bg-white/30 rounded-full mx-auto mb-3"></div>

            <!-- Close Button -->
            <button type="button" 
                    @click="mobileOpen = false" 
                    class="absolute top-5 right-5 w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white/80 hover:text-white transition cursor-pointer"
                    aria-label="Close menu">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- Brand Badge -->
            <div class="flex items-center justify-center gap-2 mb-3">
                <span class="text-[11px] font-mono uppercase tracking-widest text-[#f3bd2e] font-bold">QUAF Navigation</span>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1 py-1">
                <a href="{{ route('home.view') }}" 
                   @click="mobileOpen = false" 
                   class="block py-2.5 px-4 rounded-2xl text-base font-semibold tracking-wide transition-all transform active:scale-95 text-slate-200 hover:text-white hover:bg-white/10">
                    Home
                </a>
                <a href="{{ route('results.index') }}" 
                   @click="mobileOpen = false" 
                   class="block py-2.5 px-4 rounded-2xl text-base font-semibold tracking-wide transition-all transform active:scale-95 text-slate-200 hover:text-white hover:bg-white/10">
                    Results
                </a>
                <a href="{{ route('schedule.index') }}" 
                   @click="mobileOpen = false" 
                   class="block py-2.5 px-4 rounded-2xl text-base font-semibold tracking-wide transition-all transform active:scale-95 text-slate-200 hover:text-white hover:bg-white/10">
                    Schedule
                </a>
                <a href="{{ route('groups.index') }}" 
                   @click="mobileOpen = false" 
                   class="block py-2.5 px-4 rounded-2xl text-base font-semibold tracking-wide transition-all transform active:scale-95 text-slate-200 hover:text-white hover:bg-white/10">
                    Academic Groups
                </a>
                <a href="{{ route('gallery.index') }}" 
                   @click="mobileOpen = false" 
                   class="block py-2.5 px-4 rounded-2xl text-base font-semibold tracking-wide transition-all transform active:scale-95 text-slate-200 hover:text-white hover:bg-white/10">
                    Photo Gallery
                </a>
                <a href="{{ route('news.index') }}" 
                   @click="mobileOpen = false" 
                   class="block py-2.5 px-4 rounded-2xl text-base font-semibold tracking-wide transition-all transform active:scale-95 text-slate-200 hover:text-white hover:bg-white/10">
                    Festival Journal
                </a>
                <a href="{{ route('media.index') }}" 
                   @click="mobileOpen = false" 
                   class="block py-2.5 px-4 rounded-2xl text-base font-semibold tracking-wide transition-all transform active:scale-95 text-slate-200 hover:text-white hover:bg-white/10">
                    Media Highlights
                </a>
                <a href="{{ route('brochure.index') }}" 
                   @click="mobileOpen = false" 
                   class="block py-2.5 px-4 rounded-2xl text-base font-semibold tracking-wide transition-all transform active:scale-95 text-slate-200 hover:text-white hover:bg-white/10">
                    Festival Brochure
                </a>

                <div class="h-[1px] bg-white/10 my-2"></div>

                <a href="{{ route('student.dashboard') }}" 
                   @click="mobileOpen = false" 
                   class="block py-2.5 px-4 rounded-2xl text-base font-bold tracking-wide transition-all transform active:scale-95 bg-white/15 text-[#f3bd2e] shadow-sm">
                    Student Dashboard
                </a>
                <a href="{{ route('student.idcard') }}" 
                   @click="mobileOpen = false" 
                   class="block py-2.5 px-4 rounded-2xl text-base font-semibold tracking-wide transition-all transform active:scale-95 text-slate-200 hover:text-white hover:bg-white/10">
                    Digital QR Badge
                </a>
            </nav>
        </div>
    </div>

</body>
</html>
