<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Access Restricted | QUAF</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Malayalam:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0f1115] text-slate-100 font-sora antialiased min-h-screen flex items-center justify-center p-4 selection:bg-[#be1e2d] selection:text-white">

    <div class="w-full max-w-xl mx-auto text-center space-y-6">
        <!-- Logo -->
        <div class="flex justify-center">
            <a href="{{ route('home') }}" class="inline-block transition-transform hover:scale-105">
                <img src="{{ asset('images/festival-round-logo.png') }}" alt="QUAF Emblem" class="w-24 h-24 object-contain mx-auto drop-shadow-2xl">
            </a>
        </div>

        <!-- 403 Badge & Title -->
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-bold uppercase tracking-widest bg-red-950/80 text-red-400 border border-red-800/80 mb-3">
                Error 403 &bull; Unauthorized Area
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                Access Restricted
            </h1>
            <p class="text-sm text-slate-400 mt-2 max-w-md mx-auto">
                You do not have sufficient permissions to access the requested path (<code class="font-mono text-amber-400 font-bold">/{{ request()->path() }}</code>).
            </p>
        </div>

        <!-- Current Session Info Card -->
        @auth
            <div class="bg-[#17191f] border border-slate-800 rounded-2xl p-5 text-left space-y-3 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2.5">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Account</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#be1e2d] text-white uppercase">
                        {{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}
                    </span>
                </div>
                <div class="space-y-1">
                    <div class="text-sm font-bold text-white">{{ Auth::user()->name }}</div>
                    <div class="text-xs font-mono text-slate-400">{{ Auth::user()->email }}</div>
                </div>
                <p class="text-xs text-slate-400 pt-1">
                    You are currently signed in with this account. To access administrative panels (<code class="font-mono text-slate-300">/admin</code>), please sign in with an authorized administrator account.
                </p>
            </div>
        @endauth

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
            <!-- 1. Logout & Sign In as Admin -->
            <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-[#be1e2d]/25 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span>Log Out / Switch Account</span>
                </button>
            </form>

            <!-- 2. Go to My Dashboard -->
            @auth
                @php
                    $role = Auth::user()->role;
                    $dashUrl = match($role) {
                        'media_team', 'media_manager' => route('media.dashboard'),
                        'group_leader' => route('leader.dashboard'),
                        'program_committee', 'program_coordinator' => route('program-committee.dashboard'),
                        'judge' => route('judge.dashboard'),
                        'green_room_coordinator' => route('greenroom.index'),
                        'announcer' => route('announcer.index'),
                        'student' => route('student.dashboard'),
                        'admin', 'super_admin' => route('admin.dashboard'),
                        default => route('home'),
                    };
                    $dashName = match($role) {
                        'media_team', 'media_manager' => 'Media Dashboard',
                        'group_leader' => 'Leader Dashboard',
                        'program_committee', 'program_coordinator' => 'Program Samithi',
                        'judge' => 'Judge Panel',
                        'green_room_coordinator' => 'Green Room',
                        'announcer' => 'Announcer Desk',
                        'student' => 'Student Dashboard',
                        default => 'My Dashboard',
                    };
                @endphp
                <a href="{{ $dashUrl }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>Go to {{ $dashName }}</span>
                </a>
            @endauth

            <!-- 3. Return Home -->
            <a href="{{ route('home') }}" class="w-full sm:w-auto px-5 py-3 rounded-xl border border-slate-700 hover:bg-slate-800 text-slate-300 font-bold text-xs uppercase tracking-wider transition-colors">
                Public Website
            </a>
        </div>

        <!-- Admin Quick Hint Card -->
        <div class="bg-[#121418] border border-slate-800/80 rounded-2xl p-4 text-xs text-slate-400 space-y-1">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 font-mono">Central Admin Credentials Reminder</div>
            <div class="font-mono text-slate-300">
                Username: <strong class="text-white">admin</strong> &bull; Password: <strong class="text-white">CentralAdmin#2026@Quaf!</strong>
            </div>
        </div>

    </div>

</body>
</html>
