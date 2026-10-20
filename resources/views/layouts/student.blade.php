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

    <!-- Top Festival Brand Header -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 shadow-2xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between">
            <a href="{{ route('home.view') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/dashboard-logo.svg') }}" alt="QUAF" class="h-8 sm:h-9 w-auto object-contain">
                <span class="hidden sm:inline-block h-4 w-[1px] bg-slate-300"></span>
                <span class="hidden sm:inline-block text-[11px] font-mono font-bold tracking-wider uppercase text-[#be1e2d]">
                    Student Portal
                </span>
            </a>
            <div class="flex items-center gap-2 sm:gap-3 text-xs font-mono">
                <a href="{{ route('home.view') }}" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition font-semibold">
                    Home
                </a>
                <a href="{{ route('schedule.index') }}" class="hidden sm:inline-block px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition font-semibold">
                    Schedule
                </a>
                <a href="{{ route('results.index') }}" class="hidden sm:inline-block px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition font-semibold">
                    Results
                </a>
                <a href="{{ route('student.idcard') }}" class="hidden sm:inline-block px-3 py-1.5 rounded-lg text-[#be1e2d] bg-red-50 hover:bg-red-100 transition font-bold border border-red-200/80">
                    QR Badge
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-6xl w-full mx-auto p-3 sm:p-6 md:p-8 pb-10 min-w-0 overflow-x-clip">
        @yield('content')
    </main>

    <!-- Global Premium Dark Footer -->
    <footer class="bg-slate-950 text-slate-400 border-t border-slate-800/80 pt-14 pb-12 lg:pb-14 mt-12 sm:mt-16 relative overflow-hidden">
        <!-- Subtle Glow in Footer -->
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-red-600/5 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-[#be1e2d]/5 rounded-full blur-[140px] pointer-events-none"></div>

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
                        <span class="px-2.5 py-1 rounded bg-slate-900 border border-slate-800 text-red-400 font-semibold">31 OCT — 01 NOV 2026</span>
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

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-4 text-center sm:text-left">
                <p>© {{ date('Y') }} QUAF — Ihyaussunna-Markaz Students' Union, Markazu Saquafathi Sunniyya. All Rights Reserved.</p>
                <div class="flex items-center justify-center sm:justify-end gap-2 text-[11px] text-slate-400">
                    <span>Designed and Developed by</span>
                    <a href="https://ahrazdesign.com" target="_blank" rel="noopener noreferrer" class="inline-flex items-center transition-opacity hover:opacity-80 group" title="Ahraz Design">
                        <img src="{{ asset('images/ahraz-design-footer.svg') }}" 
                             alt="Ahraz Design" 
                             class="h-4 sm:h-4.5 w-auto object-contain inline-block"
                             style="height: 16px; max-height: 18px; width: auto;">
                    </a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
