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

    <title>{{ $title ?? 'Admin Control' }} | QUAF 09</title>

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

        /* Typography Hierarchy: Titles & UI -> Sora, Headings/Display -> Rockwell, Numbers & Chest Numbers -> JetBrains Mono, Malayalam -> Anek Malayalam */
        h1, h2, h3, h4, h5, h6,
        body,
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

        @media print {
            aside, header, #sidebar, .print\:hidden, [x-show="settingsDrawerOpen"] {
                display: none !important;
            }
            body, html {
                background: #ffffff !important;
                color: #000000 !important;
                overflow: visible !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .flex-1, main {
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
                max-width: 100% !important;
                width: 100% !important;
                background: #ffffff !important;
            }
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900 font-sora antialiased min-h-[100dvh] flex overflow-x-clip max-w-full w-full" 
      x-data="{ 
          sidebarOpen: false, 
          settingsDrawerOpen: false, 
          activeDrawerTab: 'add_mark',
          adminMenuOpen: false
      }">

    <!-- Mobile Backdrop for Sidebar -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs lg:hidden" style="display: none;"></div>

    <!-- Sidebar Navigation (Light / Obsidian-Accented Theme) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed lg:static top-0 bottom-0 left-0 z-50 w-64 bg-[#121417] text-slate-300 border-r border-slate-800 flex flex-col transition-transform duration-300 overflow-y-auto shadow-xl print:hidden">
        
        <!-- Sidebar Brand -->
        <div class="h-20 flex items-center justify-between px-4 border-b border-slate-800/80 flex-shrink-0 overflow-hidden">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center group py-2 min-w-0">
                <img src="{{ asset('images/dashboard-logo.svg') }}" alt="QUAF Logo" class="h-11 w-auto object-contain max-w-[180px] drop-shadow-xs">
            </a>
            <!-- Close button on mobile -->
            <button @click="sidebarOpen = false" class="lg:hidden p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-4 space-y-1 text-xs font-sora text-slate-400">
            <!-- 1. Dashboard -->
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span>Dashboard</span>
            </a>

            <!-- 2. Groups -->
            <a href="{{ route('admin.groups.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.groups.*') ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Groups</span>
            </a>

            <!-- 3. Zone -->
            <a href="{{ route('admin.zones.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.zones.*') ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                <span>Zone</span>
            </a>

            <!-- 4. Students (Dropdown) -->
            <div x-data="{ open: {{ request()->routeIs('admin.students.*') ? 'true' : 'false' }} }" class="space-y-0.5">
                <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all hover:text-white hover:bg-slate-800/60 {{ request()->routeIs('admin.students.*') ? 'text-white font-semibold' : '' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span>Students</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" class="pl-9 pr-2 py-1 space-y-1 text-[11px]" style="display: none;">
                    <a href="{{ route('admin.students.create') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.students.create') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Add Student</a>
                    <a href="{{ route('admin.students.index') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.students.index') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">View Student</a>
                    <a href="{{ route('admin.students.student-wise') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.students.student-wise') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Student Wise Programs</a>
                </div>
            </div>

            <!-- 5. Programs (Dropdown) -->
            <div x-data="{ open: {{ request()->routeIs('admin.programs.*') || request()->routeIs('admin.registrations.*') ? 'true' : 'false' }} }" class="space-y-0.5">
                <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all hover:text-white hover:bg-slate-800/60 {{ request()->routeIs('admin.programs.*') || request()->routeIs('admin.registrations.*') ? 'text-white font-semibold' : '' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <span>Programs</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" class="pl-9 pr-2 py-1 space-y-1 text-[11px]" style="display: none;">
                    <a href="{{ route('admin.registrations.index') }}" class="flex items-center justify-between px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.registrations.index') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">
                        <span>Program Entries</span>
                    </a>
                    <a href="{{ route('admin.programs.create') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.programs.create') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Create Programs</a>
                    <a href="{{ route('admin.programs.index') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.programs.index') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Program List</a>
                    <a href="{{ route('admin.registrations.create') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.registrations.create') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Add Student To Program</a>
                    <a href="{{ route('admin.programs.program-wise') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.programs.program-wise') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Program Wise Students</a>
                    <a href="{{ route('program-committee.dashboard') }}" class="flex items-center justify-between px-3 py-1.5 rounded-lg text-amber-400 hover:text-white hover:bg-slate-800/40 font-semibold border-t border-slate-800/60 mt-1 pt-1">
                        <span>Program Samithi Portal</span>
                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-400/20 text-amber-300 font-mono">PORTAL</span>
                    </a>
                </div>
            </div>

            <!-- 6. Code letter -->
            <a href="{{ route('admin.code-letters.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.code-letters.*') ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
                <span>Code letter</span>
            </a>

            <!-- 7. Forms (Dropdown) -->
            <div x-data="{ open: {{ request()->routeIs('admin.forms.*') || request()->routeIs('admin.idcards.*') ? 'true' : 'false' }} }" class="space-y-0.5">
                <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all hover:text-white hover:bg-slate-800/60 {{ request()->routeIs('admin.forms.*') ? 'text-white font-semibold' : '' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Forms</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" class="pl-9 pr-2 py-1 space-y-1 text-[11px]" style="display: none;">
                    <a href="{{ route('admin.forms.call-list') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.forms.call-list') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Call list</a>
                    <a href="{{ route('admin.forms.evaluation') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.forms.evaluation') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Evaluation Form</a>
                    <a href="{{ route('admin.idcards.chest-slips') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.idcards.chest-slips') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Chest Slips</a>
                </div>
            </div>

            <!-- 8. Marks (Dropdown) -->
            <div x-data="{ open: {{ request()->routeIs('admin.mark-entry.*') ? 'true' : 'false' }} }" class="space-y-0.5">
                <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all hover:text-white hover:bg-slate-800/60 {{ request()->routeIs('admin.mark-entry.*') ? 'text-white font-semibold' : '' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        <span>Marks</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" class="pl-9 pr-2 py-1 space-y-1 text-[11px]" style="display: none;">
                    <a href="{{ route('admin.mark-entry.view-marks') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.mark-entry.view-marks') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">View Marks</a>
                    <a href="{{ route('admin.mark-entry.handler') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.mark-entry.handler') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Marks Handler</a>
                    <a href="{{ route('admin.mark-entry.check') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.mark-entry.check') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Mark Check</a>
                </div>
            </div>

            <!-- 9. Results (Dropdown) -->
            <div x-data="{ open: {{ request()->routeIs('admin.results.*') ? 'true' : 'false' }} }" class="space-y-0.5">
                <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all hover:text-white hover:bg-slate-800/60 {{ request()->routeIs('admin.results.*') ? 'text-white font-semibold' : '' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                        <span>Results</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" class="pl-9 pr-2 py-1 space-y-1 text-[11px]" style="display: none;">
                    <a href="{{ route('admin.results.declare') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.results.declare') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Declare Results</a>
                    <a href="{{ route('admin.results.declared') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.results.declared') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Declared Results</a>
                    <a href="{{ route('admin.results.specified') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.results.specified') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Show Specified</a>
                    <a href="{{ route('admin.results.all') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.results.all') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Show All</a>
                </div>
            </div>

            <!-- 10. Achievements (Dropdown) -->
            <div x-data="{ open: {{ request()->routeIs('admin.achievements.*') || request()->routeIs('admin.top-scorers.*') ? 'true' : 'false' }} }" class="space-y-0.5">
                <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all hover:text-white hover:bg-slate-800/60 {{ request()->routeIs('admin.achievements.*') || request()->routeIs('admin.top-scorers.*') ? 'text-white font-semibold' : '' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                        <span>Achievements</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" class="pl-9 pr-2 py-1 space-y-1 text-[11px]" style="display: none;">
                    <a href="{{ route('admin.achievements.team-score') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.achievements.team-score') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Group Score</a>
                    <a href="{{ route('admin.achievements.zone-score') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.achievements.zone-score') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Zone Score</a>
                    <a href="{{ route('admin.achievements.stage-score') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.achievements.stage-score') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Stage Score</a>
                    <a href="{{ route('admin.achievements.all-students') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.achievements.all-students') ? 'bg-[#be1e2d]/20 text-[#be1e2d] font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">All Student Score</a>
                </div>
            </div>

            <!-- 11. Venues & Stages -->
            <a href="{{ route('admin.stages.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.stages.*') ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>Venues & Stages</span>
            </a>

            <!-- 12. Judges -->
            <a href="{{ route('admin.judges.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.judges.*') ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                <span>Judges</span>
            </a>

            <!-- 13. Templates -->
            <a href="{{ route('admin.templates.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.templates.*') ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                <span>Templates</span>
            </a>

            <!-- 14. Exports -->
            <a href="{{ route('admin.exports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.exports.*') ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Exports</span>
            </a>

            <!-- 15. Panel Access & Passwords Hub -->
            <a href="{{ route('admin.panel-access.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.panel-access.*') ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                <div class="flex items-center justify-between w-full">
                    <span>Panel Access & Passwords</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                </div>
            </a>

            <!-- 16. Announcer Desk -->
            <a href="{{ route('announcer.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('announcer.*') ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                <div class="flex items-center justify-between w-full">
                    <span>Announcer Desk</span>
                    @php
                        $pendingAnnounceCount = \App\Models\Result::whereIn('status', ['send', 'delivered'])->count();
                    @endphp
                    @if($pendingAnnounceCount > 0)
                        <span class="px-2 py-0.5 rounded-full bg-amber-500 text-slate-900 font-bold text-[10px] animate-pulse">
                            {{ $pendingAnnounceCount }}
                        </span>
                    @endif
                </div>
            </a>

            <!-- 17. Media Portal -->
            <a href="{{ route('media.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('media.*') ? 'bg-[#be1e2d] text-white font-bold shadow-md shadow-[#be1e2d]/20' : 'hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                <span>Media Wing Portal</span>
            </a>
        </nav>

        <!-- Sidebar Bottom: Admin Button & Popover -->
        <div class="p-3 border-t border-slate-800/80 bg-[#0d0f11] relative">
            <button @click="adminMenuOpen = !adminMenuOpen" 
                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs transition-all shadow-md">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>{{ Auth::user()->name ?? 'Admin' }}</span>
                </div>
                <svg class="w-4 h-4 transition-transform" :class="adminMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </button>

            <!-- Admin Popover Menu -->
            <div x-show="adminMenuOpen" @click.away="adminMenuOpen = false" 
                 class="absolute bottom-16 left-3 right-3 bg-[#1e2329] border border-slate-700/80 rounded-2xl shadow-2xl py-2 z-50 text-xs font-sora space-y-0.5" 
                 style="display: none;">
                <a href="{{ route('admin.panel-access.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-slate-300 hover:text-white hover:bg-slate-700/50">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    <span>Panel Access & Passwords</span>
                </a>
                <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-slate-300 hover:text-white hover:bg-slate-700/50">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>Profile</span>
                </a>
                <button @click="settingsDrawerOpen = true; adminMenuOpen = false" class="w-full flex items-center gap-2 px-4 py-2.5 text-slate-300 hover:text-white hover:bg-slate-700/50 text-left">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>Settings</span>
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-red-400 hover:text-red-300 hover:bg-red-500/10 text-left font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span>Signout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 max-w-full overflow-x-hidden overflow-y-auto bg-[#fafafa] print:overflow-visible print:bg-white print:p-0">
        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-3 sm:px-6 sticky top-0 z-30 shadow-2xs w-full max-w-full overflow-hidden print:hidden">
            <div class="flex items-center gap-2 sm:gap-4 min-w-0 flex-1">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-1.5 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100 shrink-0" aria-label="Toggle Navigation">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>

                <!-- Breadcrumb or Search -->
                <div class="relative w-full max-w-[140px] xs:max-w-[190px] sm:max-w-xs">
                    <span class="absolute inset-y-0 left-0 pl-2.5 sm:pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" placeholder="Search..."
                           class="w-full pl-7 sm:pl-8 pr-2 sm:pr-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white text-slate-900 focus:outline-none focus:border-[#be1e2d] transition-all">
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                <!-- Settings Drawer Trigger -->
                <button @click="settingsDrawerOpen = true" 
                        class="flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
                    <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="hidden sm:inline">Settings</span>
                </button>

                <!-- Public Portal Link -->
                <a href="{{ route('home') }}" target="_blank" 
                   class="flex items-center gap-1 px-2 sm:px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-[#be1e2d] hover:bg-slate-50 transition-colors shadow-2xs whitespace-nowrap">
                    <span class="hidden xs:inline">Live Site</span>
                    <span class="xs:hidden">Live</span> ↗
                </a>
            </div>
        </header>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mx-3 sm:mx-6 mt-4 sm:mt-6 p-3 sm:p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-mono flex items-center justify-between shadow-xs">
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mx-3 sm:mx-6 mt-4 sm:mt-6 p-3 sm:p-4 rounded-xl bg-red-50 border border-red-300 text-red-800 text-xs font-mono flex items-center justify-between shadow-xs">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Main Body -->
        <main class="p-3 sm:p-6 md:p-8 flex-1 max-w-full overflow-x-hidden print:p-0 print:m-0 print:overflow-visible print:max-w-none">
            @yield('content')
        </main>
    </div>

    <!-- ========================================================================= -->
    <!-- SETTINGS SLIDE-OUT DRAWER (Off-canvas from Right)                         -->
    <!-- ========================================================================= -->
    <div x-show="settingsDrawerOpen" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
        <!-- Backdrop -->
        <div x-show="settingsDrawerOpen" 
             @click="settingsDrawerOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex">
            <div x-show="settingsDrawerOpen"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="w-screen max-w-2xl bg-[#181a1e] text-white flex flex-col sm:flex-row shadow-2xl border-l border-slate-800 h-full overflow-hidden">
                
                <!-- Mobile Header & Tab Pills for Settings Drawer -->
                <div class="sm:hidden flex items-center justify-between p-3 border-b border-slate-800 bg-[#1f2228] shrink-0">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-200 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>Festival Settings</span>
                    </span>
                    <button @click="settingsDrawerOpen = false" class="p-1.5 text-slate-400 hover:text-white rounded-lg bg-slate-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="sm:hidden flex items-center gap-1.5 p-2 bg-[#1a1d22] border-b border-slate-800 overflow-x-auto no-scrollbar shrink-0 text-xs">
                    <button @click="activeDrawerTab = 'add_mark'" :class="activeDrawerTab === 'add_mark' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-800/80 text-slate-300'" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap shrink-0">Marks</button>
                    <button @click="activeDrawerTab = 'update_limit'" :class="activeDrawerTab === 'update_limit' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-800/80 text-slate-300'" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap shrink-0">Limits</button>
                    <button @click="activeDrawerTab = 'message'" :class="activeDrawerTab === 'message' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-800/80 text-slate-300'" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap shrink-0">Broadcast</button>
                    <button @click="activeDrawerTab = 'deadline'" :class="activeDrawerTab === 'deadline' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-800/80 text-slate-300'" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap shrink-0">Deadline</button>
                    <button @click="activeDrawerTab = 'score_handle'" :class="activeDrawerTab === 'score_handle' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-800/80 text-slate-300'" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap shrink-0">Score Mode</button>
                    <button @click="activeDrawerTab = 'registration'" :class="activeDrawerTab === 'registration' ? 'bg-[#be1e2d] text-white font-bold' : 'bg-slate-800/80 text-slate-300'" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap shrink-0">Reg Portal</button>
                </div>

                <!-- Left Content Area of Drawer -->
                <div class="flex-1 flex flex-col p-4 sm:p-8 overflow-y-auto min-w-0">
                    <!-- Tab 1: Mark's Settings -->
                    <div x-show="activeDrawerTab === 'add_mark'" class="space-y-6">
                        <div>
                            <h2 class="text-2xl font-bold text-white tracking-tight">Mark's Settings</h2>
                            <p class="text-xs text-slate-400 mt-1">Default individual program mark is 5 and group program mark is 10</p>
                        </div>
                        <form method="POST" action="{{ route('admin.settings.mark-settings') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Zone</label>
                                <select name="zone" class="w-full bg-[#23272e] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#be1e2d]">
                                    <option value="GENERAL">GENERAL</option>
                                    <option value="ZONE A">ZONE A</option>
                                    <option value="ZONE B">ZONE B</option>
                                    <option value="ZONE C">ZONE C</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Name</label>
                                <select name="program_id" class="w-full bg-[#23272e] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#be1e2d]">
                                    @foreach(\App\Models\Program::orderBy('name')->get() as $prog)
                                        <option value="{{ $prog->id }}">{{ $prog->name }} — ID: {{ $prog->code ?? $prog->id }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Mark</label>
                                <select name="points_weight" class="w-full bg-[#23272e] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#be1e2d]">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                    <option value="20">20</option>
                                    <option value="25">25</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full py-3 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-sm transition-colors shadow-lg shadow-[#be1e2d]/20">
                                Add Mark
                            </button>
                        </form>
                    </div>

                    <!-- Tab 2: Limit's Settings -->
                    <div x-show="activeDrawerTab === 'update_limit'" class="space-y-6" style="display: none;">
                        <div>
                            <h2 class="text-2xl font-bold text-white tracking-tight">Limit's Settings</h2>
                            <p class="text-xs text-slate-400 mt-1">Limit settings are for group programs only. Default limit is 2.</p>
                        </div>
                        <form method="POST" action="{{ route('admin.settings.limit-settings') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Program</label>
                                <select name="program_id" class="w-full bg-[#23272e] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#be1e2d]">
                                    @foreach(\App\Models\Program::where('type', 'group')->orderBy('name')->get() as $prog)
                                        <option value="{{ $prog->id }}">[{{ $prog->code }}] {{ $prog->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Group Program Limit from Team</label>
                                <input type="number" name="group_limit" value="2" min="1" max="10" required 
                                       class="w-full bg-[#23272e] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#be1e2d]">
                            </div>
                            <button type="submit" class="w-full py-3 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-sm transition-colors shadow-lg shadow-[#be1e2d]/20">
                                Save Limits
                            </button>
                        </form>
                    </div>

                    <!-- Tab 3: Message Settings -->
                    <div x-show="activeDrawerTab === 'message'" class="space-y-6" style="display: none;">
                        <div>
                            <h2 class="text-2xl font-bold text-white tracking-tight">Message Settings</h2>
                            <p class="text-xs text-slate-400 mt-1">Send Updates and details to the Teams or Students</p>
                        </div>
                        <form method="POST" action="{{ route('admin.settings.broadcast') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Message Title</label>
                                <input type="text" name="title" required placeholder="Enter message title"
                                       class="w-full bg-[#23272e] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#be1e2d]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Message Content</label>
                                <textarea name="message" rows="5" required placeholder="Enter message here..."
                                          class="w-full bg-[#23272e] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#be1e2d]"></textarea>
                            </div>
                            <button type="submit" class="w-full py-3 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-sm transition-colors shadow-lg shadow-[#be1e2d]/20">
                                Send Message
                            </button>
                        </form>
                    </div>

                    <!-- Tab 4: Deadline Settings -->
                    <div x-show="activeDrawerTab === 'deadline'" class="space-y-6" style="display: none;">
                        <div>
                            <h2 class="text-2xl font-bold text-white tracking-tight">Deadline Settings</h2>
                            <p class="text-xs text-slate-400 mt-1">Set deadlines for students or programs and notify teams</p>
                        </div>
                        <form method="POST" action="{{ route('admin.settings.deadline') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Title</label>
                                <input type="text" name="title" required placeholder="Enter deadline notice title"
                                       class="w-full bg-[#23272e] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#be1e2d]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Message</label>
                                <textarea name="message" rows="3" placeholder="Enter message here..."
                                          class="w-full bg-[#23272e] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#be1e2d]"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Deadline Date & Time</label>
                                <input type="datetime-local" name="deadline" required
                                       class="w-full bg-[#23272e] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#be1e2d]">
                            </div>
                            <button type="submit" class="w-full py-3 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-sm transition-colors shadow-lg shadow-[#be1e2d]/20">
                                Set Deadline & Notify
                            </button>
                        </form>
                    </div>

                    <!-- Tab 5: Score Display Settings -->
                    <div x-show="activeDrawerTab === 'score_handle'" class="space-y-6" style="display: none;">
                        <div>
                            <h2 class="text-2xl font-bold text-white tracking-tight">Score display Settings</h2>
                            <p class="text-xs text-slate-400 mt-1">Showing score and student score in dashboard. Controls public visibility.</p>
                        </div>
                        <form method="POST" action="{{ route('admin.settings.score-display') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Select Declare count</label>
                                <select name="declare_count" class="w-full bg-[#23272e] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#be1e2d]">
                                    <option value="Off">Off (Hide all scores)</option>
                                    @for($i = 1; $i <= 50; $i++)
                                        <option value="{{ $i }}">{{ $i }} Results Shown</option>
                                    @endfor
                                    <option value="All" selected>All Declared Results</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full py-3 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-sm transition-colors shadow-lg shadow-[#be1e2d]/20">
                                Update Display Settings
                            </button>
                        </form>
                    </div>

                    <!-- Tab 6: Registration Portal Control -->
                    <div x-show="activeDrawerTab === 'registration'" class="space-y-6" style="display: none;">
                        <div>
                            <h2 class="text-2xl font-bold text-white tracking-tight">Registration Portal</h2>
                            <p class="text-xs text-slate-400 mt-1">Open or Close student entry submissions for group leaders</p>
                        </div>
                        @php
                            $drawerRegOpen = (\App\Models\FestivalSetting::get('registration_open', '1') == '1');
                        @endphp
                        <div class="p-5 rounded-2xl {{ $drawerRegOpen ? 'bg-emerald-950/60 border border-emerald-500/40 text-emerald-200' : 'bg-rose-950/60 border border-rose-500/40 text-rose-200' }} text-xs space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="font-bold flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $drawerRegOpen ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }}"></span>
                                    Status: {{ $drawerRegOpen ? 'REGISTRATION OPEN' : 'REGISTRATION CLOSED' }}
                                </span>
                            </div>
                            <p class="text-slate-300 text-[11px] leading-relaxed">
                                {{ $drawerRegOpen ? 'Group leaders can currently submit participant registrations. Click the button below to close registration.' : 'Registration is currently closed. Leaders cannot submit new entries until reopened.' }}
                            </p>
                            <form method="POST" action="{{ route('admin.settings.toggle-registration') }}">
                                @csrf
                                <button type="submit" class="w-full py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition-all shadow-md {{ $drawerRegOpen ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                    {{ $drawerRegOpen ? 'Close Registration Now' : 'Open Registration Now' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Sub-Sidebar of Drawer (Settings Tabs - Desktop only) -->
                <div class="hidden sm:flex sm:w-48 bg-[#1f2228] border-l border-slate-800 flex-col justify-between p-4 flex-shrink-0">
                    <div>
                        <!-- Header with Close Button -->
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-800">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span>Settings</span>
                            </span>
                            <button @click="settingsDrawerOpen = false" class="p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <!-- Drawer Tab Buttons -->
                        <div class="space-y-1.5 text-xs">
                            <button @click="activeDrawerTab = 'add_mark'" 
                                    :class="activeDrawerTab === 'add_mark' ? 'bg-[#be1e2d] text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
                                    class="w-full flex items-start gap-2.5 p-2.5 rounded-xl text-left transition-all">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                <div>
                                    <div class="font-semibold">Add Mark</div>
                                    <div class="text-[10px] opacity-70">Add mark to programs</div>
                                </div>
                            </button>

                            <button @click="activeDrawerTab = 'update_limit'" 
                                    :class="activeDrawerTab === 'update_limit' ? 'bg-[#be1e2d] text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
                                    class="w-full flex items-start gap-2.5 p-2.5 rounded-xl text-left transition-all">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <div class="font-semibold">Update Limit</div>
                                    <div class="text-[10px] opacity-70">Modify existing limits</div>
                                </div>
                            </button>

                            <button @click="activeDrawerTab = 'message'" 
                                    :class="activeDrawerTab === 'message' ? 'bg-[#be1e2d] text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
                                    class="w-full flex items-start gap-2.5 p-2.5 rounded-xl text-left transition-all">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                <div>
                                    <div class="font-semibold">Message</div>
                                    <div class="text-[10px] opacity-70">Send Information</div>
                                </div>
                            </button>

                            <button @click="activeDrawerTab = 'deadline'" 
                                    :class="activeDrawerTab === 'deadline' ? 'bg-[#be1e2d] text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
                                    class="w-full flex items-start gap-2.5 p-2.5 rounded-xl text-left transition-all">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <div class="font-semibold">DeadLine</div>
                                    <div class="text-[10px] opacity-70">Set deadline</div>
                                </div>
                            </button>

                            <button @click="activeDrawerTab = 'score_handle'" 
                                    :class="activeDrawerTab === 'score_handle' ? 'bg-[#be1e2d] text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
                                    class="w-full flex items-start gap-2.5 p-2.5 rounded-xl text-left transition-all">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                <div>
                                    <div class="font-semibold">Score handle</div>
                                    <div class="text-[10px] opacity-70">Display the score</div>
                                </div>
                            </button>

                            <button @click="activeDrawerTab = 'registration'" 
                                    :class="activeDrawerTab === 'registration' ? 'bg-[#be1e2d] text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
                                    class="w-full flex items-start gap-2.5 p-2.5 rounded-xl text-left transition-all">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <div class="font-semibold">Registration</div>
                                    <div class="text-[10px] opacity-70">Open / Close Portal</div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div class="text-[10px] text-slate-500 text-center font-mono">
                        QUAF Fest 09 Settings
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
