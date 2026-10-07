<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full overflow-x-clip">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#14171d">
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Program Samithi Portal' }} | QUAF Fest</title>

    <!-- Google Fonts (Anek Malayalam, JetBrains Mono, Sora, Amiri) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Anek+Malayalam:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-malayalam, .font-anek {
            font-family: 'Anek Malayalam' !important;
        }
        body, h1, h2, h3, h4, h5, h6, .font-sora {
            font-family: 'Sora' !important;
        }
        .font-rockwell {
            font-family: 'Rockwell', 'Rockwell Std' !important;
        }
        @media print {
            aside, header, nav, .no-print, [x-cloak] {
                display: none !important;
            }
            body, main {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
            }
        }
    </style>
</head>
<body class="h-full bg-[#f8fafc] text-slate-800 font-sora antialiased flex overflow-hidden" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 lg:hidden" 
         style="display: none;">
    </div>

    <!-- Sidebar (Dark Slate matching QUAF Style with Burgundy Accent) -->
    <aside id="sidebar" 
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed lg:static inset-y-0 left-0 w-64 bg-[#14171d] text-white flex-shrink-0 flex flex-col justify-between transition-transform duration-300 ease-in-out z-50 select-none shadow-2xl lg:shadow-none">
        <div class="flex-1 flex flex-col min-h-0">
            <!-- Sidebar Header / Brand -->
            <div class="h-20 flex items-center justify-between px-4 border-b border-white/10 flex-shrink-0 overflow-hidden">
                <a href="{{ route('program-committee.dashboard') }}" class="flex items-center gap-2.5 min-w-0 group py-1">
                    <img src="{{ asset('images/dashboard-logo.svg') }}" alt="QUAF Logo" class="h-9 w-auto object-contain shrink-0 max-w-[125px]">
                    <div class="min-w-0 border-l border-white/15 pl-2">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-amber-400 font-mono leading-none">SAMITHI</div>
                        <div class="text-[11px] font-bold text-slate-200 truncate mt-0.5">Program Hub</div>
                    </div>
                </a>
                <button type="button" @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto text-sm font-medium">
                <!-- Dashboard -->
                <a href="{{ route('program-committee.dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('program-committee.dashboard') ? 'bg-[#be1e2d] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <div>
                        <span>Dashboard</span>
                        <span class="block text-[10px] opacity-75">Overview & Controls</span>
                    </div>
                </a>

                <!-- Program List -->
                <a href="{{ route('program-committee.programs.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('program-committee.programs.index') || request()->routeIs('program-committee.programs.show') ? 'bg-[#be1e2d] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <div>
                        <span>Program List</span>
                        <span class="block text-[10px] opacity-75">Competition Registry</span>
                    </div>
                </a>

                <!-- Team Entries Data -->
                <a href="{{ route('program-committee.team-entries.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('program-committee.team-entries.*') ? 'bg-[#be1e2d] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <div>
                        <span>Team Entries Data</span>
                        <span class="block text-[10px] opacity-75">Group Quotas & Matrix</span>
                    </div>
                </a>

                <!-- Festival Schedule Section -->
                <div x-data="{ open: {{ request()->routeIs('program-committee.schedules.*') || request()->routeIs('program-committee.schedule') || request()->routeIs('admin.schedules.*') ? 'true' : 'false' }} }" class="space-y-0.5">
                    <button @click="open = !open" 
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('program-committee.schedules.*') || request()->routeIs('program-committee.schedule') ? 'bg-[#be1e2d] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <div class="text-left">
                                <span>Festival Schedule</span>
                                <span class="block text-[10px] opacity-75">Stages & Timing Slots</span>
                            </div>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" class="pl-9 pr-2 py-1 space-y-1 text-xs" style="display: none;">
                        <a href="{{ route('program-committee.schedules.index') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('program-committee.schedules.index') ? 'bg-[#be1e2d]/20 text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">All Stages Schedule</a>
                        <a href="{{ route('program-committee.schedules.offstage') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('program-committee.schedules.offstage') ? 'bg-[#be1e2d]/20 text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">Offstage & Clashes</a>
                        <a href="{{ route('program-committee.schedules.offstage.pdf') }}" target="_blank" class="block px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5">Print PDF (Rockwell)</a>
                    </div>
                </div>

                <!-- Add New Program -->
                <a href="{{ route('program-committee.programs.create') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('program-committee.programs.create') ? 'bg-[#be1e2d] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <span>Add New Program</span>
                        <span class="block text-[10px] opacity-75">Create New Event</span>
                    </div>
                </a>

                <!-- Niyamavali Hub -->
                <a href="{{ route('program-committee.niyamavali.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('program-committee.niyamavali.*') || request()->routeIs('program-committee.programs.rules') ? 'bg-[#be1e2d] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <div>
                        <span>Niyamavali Hub</span>
                        <span class="block text-[10px] opacity-75">Rules & Rubrics</span>
                    </div>
                </a>

                <!-- Print Booklet -->
                <a href="{{ route('program-committee.niyamavali.print-book') }}" target="_blank"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-slate-300 hover:text-white hover:bg-white/5">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <div>
                        <span>Print Rules Booklet</span>
                        <span class="block text-[10px] opacity-75">Official Print Manual</span>
                    </div>
                </a>

                @if(auth()->user()?->isAdmin() || auth()->user()?->isSuperAdmin())
                    <div class="pt-3 border-t border-white/10 mt-3">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-xs text-slate-400 hover:text-white rounded-lg hover:bg-white/5 transition">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Back to Central Admin</span>
                        </a>
                    </div>
                @endif
            </nav>
        </div>

        <!-- Bottom User Profile Pill with Logout -->
        <div class="p-4 border-t border-white/10 bg-black/20">
            <div class="flex items-center justify-between mb-2">
                <div class="truncate">
                    <div class="text-xs font-bold text-white truncate">{{ auth()->user()?->name ?? 'Program Committee' }}</div>
                    <div class="text-[10px] text-amber-400 font-mono">Program Samithi Portal</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full py-2 px-3 bg-white/10 hover:bg-brand-burgundy text-white rounded-xl text-xs font-mono font-bold transition flex items-center justify-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Topbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-8 z-10">
            <div class="flex items-center gap-3">
                <button type="button" @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs font-mono font-bold uppercase">
                        Program Samithi
                    </span>
                    <span class="hidden sm:inline text-xs text-slate-400 font-mono">|</span>
                    <span class="hidden sm:inline text-xs font-semibold text-slate-600">
                        Event & Niyamavali Management Hub
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('program-committee.team-entries.index') }}" 
                   class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-mono font-bold flex items-center gap-1.5 shadow-sm transition">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="hidden sm:inline">Team Entries</span>
                </a>
                <a href="{{ route('program-committee.programs.create') }}" 
                   class="px-3.5 py-1.5 rounded-xl bg-brand-burgundy hover:bg-[#850d18] text-white text-xs font-mono font-bold flex items-center gap-1.5 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span class="hidden sm:inline">Add Program</span>
                </a>
                <a href="{{ route('program-committee.niyamavali.index') }}" 
                   class="px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-mono font-bold flex items-center gap-1.5 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Niyamavali</span>
                </a>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-8 bg-[#f8fafc]">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs font-mono font-medium flex items-center gap-2 shadow-2xs">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 text-xs font-mono font-medium flex items-center gap-2 shadow-2xs">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 text-xs space-y-1 shadow-2xs">
                    <p class="font-bold flex items-center gap-2 text-sm text-[#be1e2d]">
                        <svg class="w-4 h-4 text-[#be1e2d] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Please correct the following errors:
                    </p>
                    <ul class="list-disc list-inside space-y-0.5 pl-2 font-mono text-rose-700">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</body>
</html>
