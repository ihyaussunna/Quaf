<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'QUAF Fest')</title>

    <!-- Google Fonts (Anek Malayalam, JetBrains Mono, Sora, Amiri) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Malayalam:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&family=Sora:wght@300;400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f4f7fc] text-gray-800 font-sora antialiased min-h-screen flex flex-col selection:bg-brand-orange selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-6 sticky top-0 z-30 shadow-xs">
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF Logo" class="h-9 w-auto object-contain">
            </a>
            <span class="text-gray-300">|</span>
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">@yield('title', 'Portal')</span>
        </div>

        <div class="flex items-center gap-3">
            @auth
                <span class="text-xs font-semibold text-gray-600 hidden sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-gray-100 hover:bg-rose-50 hover:text-rose-600 rounded-xl text-xs font-bold text-gray-700 transition">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="px-3 py-1.5 bg-brand-orange text-white rounded-xl text-xs font-bold hover:bg-orange-600 transition shadow-xs">
                    Login
                </a>
            @endauth
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-200/60 py-4 text-center text-gray-400 text-xs bg-white">
        Ihyaussunna Students Union • Jamia Markaz
    </footer>

</body>
</html>
