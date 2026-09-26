<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Judges Portal' }} | QUAF 09</title>

    <!-- Google Fonts (Multilingual: Sora, Manjari, Gayathri, Amiri, Noto Nastaliq Urdu) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Gayathri:wght@400;700&family=JetBrains+Mono:wght@400;500;600;700&family=Manjari:wght@400;700&family=Noto+Nastaliq+Urdu:wght@400;700&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 font-sans antialiased min-h-screen flex flex-col">

    <!-- Top Evaluation Header (Light Theme) -->
    <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-6 sticky top-0 z-40 shadow-xs">
        <div class="flex items-center gap-4">
            <a href="{{ route('judge.dashboard') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF Logo" class="h-10 sm:h-12 w-auto object-contain">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold tracking-wider text-sm sm:text-base text-slate-900 group-hover:text-[#be1e2d] transition-colors">JUDGES JURY</span>
                        <span class="px-2 py-0.5 rounded-full bg-red-50 text-[#be1e2d] border border-red-200 text-[10px] font-mono font-bold">CONFIDENTIAL</span>
                    </div>
                    <span class="text-[10px] font-mono tracking-widest text-slate-500 block uppercase">QUAF SEASON 09</span>
                </div>
            </a>
        </div>

        <!-- Center: Quick Judge Info -->
        <div class="hidden md:flex items-center gap-2 px-4 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono">
            <span class="text-slate-500">Juror:</span>
            <span class="text-slate-900 font-bold">{{ Auth::user()->name }}</span>
            <span class="text-slate-300">•</span>
            <span class="text-[#f3bd2e] font-semibold">{{ Auth::user()->judgeProfile->designation ?? 'Official Adjudicator' }}</span>
        </div>

        <!-- Right: Sign Out -->
        <div class="flex items-center gap-3">
            <a href="{{ route('judge.dashboard') }}" class="px-3.5 py-2 rounded-xl text-xs font-mono font-bold bg-slate-100 text-slate-700 hover:text-slate-900 hover:bg-slate-200 border border-slate-200 transition-all">
                My Programs
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-mono font-bold bg-slate-100 text-slate-700 hover:text-red-700 hover:bg-red-50 border border-slate-200 transition-all">
                    Sign Out
                </button>
            </form>
        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="max-w-6xl w-full mx-auto px-6 mt-6">
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-mono flex items-center justify-between shadow-sm">
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-6xl w-full mx-auto px-6 mt-6">
            <div class="p-4 rounded-xl bg-red-50 border border-red-300 text-red-800 text-xs font-mono flex items-center justify-between shadow-sm">
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-1 max-w-6xl w-full mx-auto p-4 sm:p-6 md:p-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 py-4 text-center text-slate-500 text-[11px] font-mono bg-white">
        QUAF '09 Adjudication Engine • Ihyaussunna Students Union • Strictly Confidential Scoring System
    </footer>

</body>
</html>
