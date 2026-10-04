<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#141414">
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Leader Panel - QUAF Fest')</title>

    <!-- Google Fonts (Anek Malayalam, JetBrains Mono, Sora, Amiri) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Malayalam:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&family=Sora:wght@300;400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            #sidebar, aside, header, nav, .mobile-nav, .no-print, button, form.no-print, .backdrop-blur-xs {
                display: none !important;
            }
            body, html {
                background: #ffffff !important;
                color: #000000 !important;
                overflow: visible !important;
                height: auto !important;
                width: 100% !important;
            }
            .min-h-screen, .h-full, .overflow-hidden {
                overflow: visible !important;
                height: auto !important;
                min-height: auto !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
                max-width: 100% !important;
                height: auto !important;
            }
            .print-only {
                display: block !important;
            }
        }
        @media screen {
            .print-only {
                display: none !important;
            }
        }
    </style>
</head>
<body class="h-full bg-[#f8fafc] text-gray-800 font-sora antialiased flex overflow-hidden max-w-full print:bg-white print:overflow-visible print:h-auto"
      x-data="{ sidebarOpen: false }">

    <!-- Mobile Backdrop for Sidebar -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs lg:hidden print:hidden" 
         style="display: none;"></div>

    <!-- Sidebar (Dark Slate / Charcoal matching QUAF Fest) -->
    <aside id="sidebar" 
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed lg:static top-0 bottom-0 left-0 z-50 w-64 bg-[#141414] text-white flex-shrink-0 flex flex-col justify-between transition-transform duration-300 select-none shadow-2xl lg:shadow-none print:hidden">
        <div>
            <!-- Sidebar Header / Brand -->
            <div class="h-16 sm:h-20 flex items-center justify-between px-4 border-b border-white/10">
                <a href="{{ route('leader.dashboard') }}" class="flex items-center py-2">
                    <img src="{{ asset('images/dashboard-logo.svg') }}" alt="QUAF Logo" class="h-10 sm:h-11 w-auto object-contain max-w-[180px]">
                </a>
                <button type="button" @click="sidebarOpen = false" class="lg:hidden text-white/60 hover:text-white p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="p-3 space-y-1 text-sm font-medium overflow-y-auto">
                <!-- Dashboard -->
                <a href="{{ route('leader.dashboard') }}" class="app-tap flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('leader.dashboard') ? 'bg-[#be1e2d] text-white font-semibold shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Dashboard</span>
                </a>

                <!-- View Student -->
                <a href="{{ route('leader.students') }}" class="app-tap flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('leader.students') ? 'bg-[#be1e2d] text-white font-semibold shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>View Student</span>
                </a>

                <!-- Program List -->
                <a href="{{ route('leader.programs') }}" class="app-tap flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('leader.programs') ? 'bg-[#be1e2d] text-white font-semibold shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Program List</span>
                </a>

                <!-- Add Program (Registrations) -->
                <a href="{{ route('leader.registrations') }}" class="app-tap flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('leader.registrations') ? 'bg-[#be1e2d] text-white font-semibold shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Add Program</span>
                </a>

                <!-- View Program Wise -->
                <a href="{{ route('leader.programs-wise') }}" class="app-tap flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('leader.programs-wise') ? 'bg-[#be1e2d] text-white font-semibold shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>View Program Wise</span>
                </a>

                <!-- View Students Wise -->
                <a href="{{ route('leader.students-wise') }}" class="app-tap flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('leader.students-wise') ? 'bg-[#be1e2d] text-white font-semibold shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>View Students Wise</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Pill: Team Name with Logout -->
        <div class="p-4 border-t border-white/10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="app-tap w-full py-2.5 px-4 bg-[#be1e2d] text-white rounded-xl font-bold text-sm hover:bg-[#a01624] transition flex items-center justify-between shadow-sm">
                    <span class="flex items-center gap-2 truncate">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="truncate">{{ Auth::user()->group?->name ?? Auth::user()->name ?? 'Leader' }}</span>
                    </span>
                    <svg class="w-4 h-4 text-white/80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main View Container -->
    <div class="flex-1 flex flex-col h-full min-w-0 max-w-full overflow-hidden print:overflow-visible print:h-auto print:block">
        <!-- Topbar -->
        <header class="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-4 sm:px-6 flex-shrink-0 z-10 shadow-xs print:hidden">
            <div class="flex items-center gap-3 min-w-0">
                <!-- Hamburger Button for Mobile -->
                <button @click="sidebarOpen = !sidebarOpen" 
                        class="lg:hidden p-2 rounded-xl bg-gray-100 text-gray-700 hover:text-gray-900 hover:bg-gray-200 transition shrink-0" 
                        aria-label="Toggle Navigation">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <h2 class="text-sm sm:text-lg font-bold text-[#be1e2d] truncate">
                    Welcome to Quaf Manager — <span class="text-slate-900">{{ Auth::user()->group?->name ?? 'Leader' }}</span>
                </h2>
            </div>

            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-800 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full" style="background-color: {{ Auth::user()->group?->color_hex ?? '#10b981' }};"></span>
                    {{ Auth::user()->group?->name ?? 'Leader' }}
                </span>

                <a href="{{ route('home') }}" target="_blank" class="px-2.5 sm:px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-[#be1e2d] hover:bg-slate-50 transition">
                    Live ↗
                </a>
            </div>
        </header>

        <!-- Flash alerts -->
        @if(session('success'))
            <div class="px-4 sm:px-6 pt-4 print:hidden">
                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="px-4 sm:px-6 pt-4 print:hidden">
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between shadow-xs">
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Dynamic Content (with safe padding for mobile bottom bar) -->
        <main class="flex-1 overflow-y-auto p-3 sm:p-6 pb-24 lg:pb-6 max-w-full overflow-x-hidden print:overflow-visible print:h-auto print:p-0 print:m-0 print:block">
            @yield('content')
        </main>

        <!-- Mobile Bottom App Bar for Leader Portal -->
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] px-2 pt-1 pb-[max(0.6rem,env(safe-area-inset-bottom,0.6rem))] flex items-center justify-around select-none print:hidden">
            <a href="{{ route('leader.dashboard') }}" class="app-tap flex flex-col items-center py-1 px-3 rounded-2xl text-center {{ request()->routeIs('leader.dashboard') ? 'text-[#be1e2d] font-bold bg-red-50/80' : 'text-slate-500' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span class="text-[10px] font-mono">Home</span>
            </a>
            <a href="{{ route('leader.students') }}" class="app-tap flex flex-col items-center py-1 px-3 rounded-2xl text-center {{ request()->routeIs('leader.students') ? 'text-[#be1e2d] font-bold bg-red-50/80' : 'text-slate-500' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span class="text-[10px] font-mono">Students</span>
            </a>
            <a href="{{ route('leader.programs') }}" class="app-tap flex flex-col items-center py-1 px-3 rounded-2xl text-center {{ request()->routeIs('leader.programs') ? 'text-[#be1e2d] font-bold bg-red-50/80' : 'text-slate-500' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span class="text-[10px] font-mono">Programs</span>
            </a>
            <a href="{{ route('leader.registrations') }}" class="app-tap flex flex-col items-center py-1 px-3 rounded-2xl text-center {{ request()->routeIs('leader.registrations') ? 'text-[#be1e2d] font-bold bg-red-50/80' : 'text-slate-500' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-[10px] font-mono">Register</span>
            </a>
            <button @click="sidebarOpen = true" type="button" class="app-tap flex flex-col items-center py-1 px-3 rounded-2xl text-center text-slate-500">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <span class="text-[10px] font-mono">More</span>
            </button>
        </nav>
    </div>

</body>
</html>
