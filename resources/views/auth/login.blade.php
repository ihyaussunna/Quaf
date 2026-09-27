<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login to Festival | QUAF</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#18181b] text-white font-sora antialiased min-h-screen flex items-center justify-center p-4 selection:bg-[#be1e2d] selection:text-white">

    <div class="w-full max-w-[340px] mx-auto text-center">
        <!-- Circular Festival Emblem -->
        <div class="mb-6 flex justify-center">
            <a href="{{ route('home') }}" class="inline-block transition-transform hover:scale-105" title="QUAF 2026 Home">
                <img src="{{ asset('images/festival-round-logo.png') }}" 
                     alt="QUAF Festival Emblem" 
                     class="w-28 h-28 object-contain mx-auto drop-shadow-xl">
            </a>
        </div>

        <!-- Heading & Subtitle -->
        <h1 class="text-2xl sm:text-[28px] font-bold text-white tracking-tight mb-1.5 font-sora">Login to Festival</h1>
        <p class="text-sm text-slate-400 mb-7 font-normal">Enter your login details below</p>

        <!-- Success / Info Notification -->
        @if(session('success'))
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-950/80 border border-emerald-500 text-emerald-200 text-xs font-medium text-left">
                {{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div class="mb-5 p-3.5 rounded-xl bg-blue-950/80 border border-blue-500 text-blue-200 text-xs font-medium text-left">
                {{ session('info') }}
            </div>
        @endif

        <!-- Error Notification -->
        @if($errors->any())
            <div class="mb-5 p-3.5 rounded-xl bg-red-950/80 border border-[#be1e2d] text-red-200 text-xs font-medium text-left">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Simplified Form: Username & Password Only -->
        <form method="POST" action="{{ route('login') }}" autocomplete="off" class="space-y-4 text-left">
            @csrf

            <!-- Username Input -->
            <div>
                <input type="text" 
                       id="username" 
                       name="username" 
                       value="{{ old('username', old('email')) }}" 
                       required 
                       autofocus 
                       autocomplete="off"
                       readonly
                       onfocus="this.removeAttribute('readonly');"
                       placeholder="Username" 
                       class="w-full bg-[#f1f5f9] border-2 border-transparent focus:border-[#be1e2d] rounded-xl px-4 py-3.5 text-slate-900 text-sm font-medium focus:outline-none focus:bg-white transition-all placeholder:text-slate-400">
            </div>

            <!-- Password Input -->
            <div>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       autocomplete="new-password"
                       readonly
                       onfocus="this.removeAttribute('readonly');"
                       placeholder="Password" 
                       class="w-full bg-[#f1f5f9] border-2 border-transparent focus:border-[#be1e2d] rounded-xl px-4 py-3.5 text-slate-900 text-sm font-medium focus:outline-none focus:bg-white transition-all placeholder:text-slate-400">
            </div>

            <!-- Login Action Button -->
            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-3.5 rounded-xl font-bold text-sm text-white bg-[#be1e2d] hover:bg-[#a01624] active:scale-[0.99] transition-all shadow-lg shadow-[#be1e2d]/25">
                    Login
                </button>
            </div>
        </form>
    </div>

</body>
</html>
