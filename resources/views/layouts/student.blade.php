<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Student Portal' }} | QUAF 09</title>

    <!-- Google Fonts (Multilingual: Anek Malayalam, JetBrains Mono, Sora, Manjari, Gayathri, Amiri, Noto Nastaliq Urdu) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Anek+Malayalam:wght@300;400;500;600;700;800&family=Gayathri:wght@400;700&family=JetBrains+Mono:wght@400;500;600;700;800&family=Manjari:wght@400;700&family=Noto+Nastaliq+Urdu:wght@400;700&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 font-sora antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Bar (Light Theme) -->
    <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-6 sticky top-0 z-40 shadow-xs">
        <div class="flex items-center gap-4">
            <a href="{{ route('student.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF Logo" class="h-10 sm:h-12 w-auto object-contain">
                <div>
                    <span class="font-bold tracking-wider text-base text-slate-900">STUDENT PORTAL</span>
                    <span class="text-[10px] font-mono tracking-widest text-[#be1e2d] block uppercase font-bold">DELEGATE CONSOLE</span>
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="hidden md:flex items-center gap-1 text-xs font-mono">
            <a href="{{ route('student.dashboard') }}" class="px-4 py-2 rounded-xl transition-all {{ request()->routeIs('student.dashboard') ? 'bg-[#f3bd2e] text-white font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                Dashboard
            </a>
            <a href="{{ route('student.idcard') }}" class="px-4 py-2 rounded-xl transition-all {{ request()->routeIs('student.idcard') ? 'bg-[#f3bd2e] text-white font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                Digital QR Badge
            </a>
            <a href="{{ route('student.certificates') }}" class="px-4 py-2 rounded-xl transition-all {{ request()->routeIs('student.certificates') ? 'bg-[#f3bd2e] text-white font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                My Certificates
            </a>
        </nav>

        <!-- Right: Profile & Logout -->
        <div class="flex items-center gap-4">
            <div class="text-right hidden sm:block">
                <span class="text-xs font-bold text-slate-900 block">{{ Auth::user()->name }}</span>
                <span class="text-[10px] font-mono text-[#f3bd2e] font-bold">Participant</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-mono font-bold bg-slate-100 text-slate-700 hover:text-red-700 hover:bg-red-50 border border-slate-200 transition-all">
                    Sign Out
                </button>
            </form>
        </div>
    </header>

    <!-- Mobile Subnav -->
    <div class="md:hidden flex items-center justify-around bg-white border-b border-slate-200 px-4 py-2.5 text-xs font-mono shadow-xs">
        <a href="{{ route('student.dashboard') }}" class="py-1 px-3 rounded-lg {{ request()->routeIs('student.dashboard') ? 'bg-amber-50 text-[#f3bd2e] font-bold' : 'text-slate-600' }}">Dashboard</a>
        <a href="{{ route('student.idcard') }}" class="py-1 px-3 rounded-lg {{ request()->routeIs('student.idcard') ? 'bg-amber-50 text-[#f3bd2e] font-bold' : 'text-slate-600' }}">QR Badge</a>
        <a href="{{ route('student.certificates') }}" class="py-1 px-3 rounded-lg {{ request()->routeIs('student.certificates') ? 'bg-amber-50 text-[#f3bd2e] font-bold' : 'text-slate-600' }}">Certificates</a>
    </div>

    <!-- Main Content -->
    <main class="flex-1 max-w-6xl w-full mx-auto p-4 sm:p-6 md:p-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 py-6 text-center text-slate-500 text-xs font-mono bg-white">
        QUAF '09 Student Portal • Ihyaussunna Students Union, Markaz
    </footer>

</body>
</html>
