<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#060609]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#060609">
    <title>QUAF 2026 — Official Website Launch</title>
    <meta name="description" content="Official Website Launch for QUAF Markaz Cultural Festival 2026. Confluence of eloquence, arts, and intellectual heritage.">

    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Sora:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Sora', sans-serif;
            background-color: #060609;
            color: #ffffff;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
            user-select: none;
            -webkit-user-select: none;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Ethereal Glow Pulsing */
        @keyframes ambientPulse {
            0%, 100% {
                transform: scale(1) translate3d(0, 0, 0);
                opacity: 0.22;
            }
            50% {
                transform: scale(1.15) translate3d(10px, -10px, 0);
                opacity: 0.35;
            }
        }
        .animate-ambient-1 {
            animation: ambientPulse 9s ease-in-out infinite;
        }
        .animate-ambient-2 {
            animation: ambientPulse 12s ease-in-out infinite 3s;
        }
        .animate-ambient-3 {
            animation: ambientPulse 10s ease-in-out infinite 6s;
        }

        /* Floating Logo Gently */
        @keyframes floatGentle {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-8px);
            }
        }
        .animate-float {
            animation: floatGentle 5s ease-in-out infinite;
        }

        /* Ring Radar Pulse */
        @keyframes ringPulse {
            0% {
                transform: scale(0.9);
                opacity: 0.8;
            }
            100% {
                transform: scale(1.7);
                opacity: 0;
            }
        }
        .animate-ring {
            animation: ringPulse 2.4s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }

        /* Warp expansion flash on completion */
        @keyframes warpFlash {
            0% {
                opacity: 0;
                transform: scale(0.95);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
            100% {
                opacity: 1;
                transform: scale(1.2);
            }
        }
        .warp-active {
            animation: warpFlash 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="flex flex-col justify-between items-center min-h-[100dvh] relative overflow-hidden px-4 py-6 sm:py-10">

    <!-- Ambient Multi-Color Glow Mesh (Official QUAF Festival Palette) -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[340px] sm:w-[650px] h-[340px] sm:h-[650px] rounded-full animate-ambient-1 pointer-events-none" style="background: rgba(190, 30, 45, 0.20); filter: blur(140px); -webkit-filter: blur(140px);"></div>
        <div class="absolute top-12 left-10 w-72 sm:w-[450px] h-72 sm:h-[450px] rounded-full animate-ambient-2 pointer-events-none" style="background: rgba(243, 189, 46, 0.16); filter: blur(140px); -webkit-filter: blur(140px);"></div>
        <div class="absolute bottom-12 right-10 w-72 sm:w-[500px] h-72 sm:h-[500px] rounded-full animate-ambient-3 pointer-events-none" style="background: rgba(0, 92, 148, 0.20); filter: blur(140px); -webkit-filter: blur(140px);"></div>
        <div class="absolute bottom-1/4 left-1/4 w-60 sm:w-[380px] h-60 sm:h-[380px] rounded-full pointer-events-none" style="background: rgba(0, 148, 68, 0.10); filter: blur(120px); -webkit-filter: blur(120px);"></div>
    </div>

    <!-- Subtle Grid Overlay -->
    <div class="fixed inset-0 pointer-events-none z-0 opacity-[0.035] bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:24px_24px]"></div>

    <!-- Top Bar: Organization Identity & Staff Link -->
    <header class="w-full max-w-6xl mx-auto flex items-center justify-between z-10 relative">
        <div class="flex items-center gap-2.5 sm:gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-[#be1e2d] shadow-[0_0_12px_#be1e2d] animate-ping"></span>
            <span class="text-[11px] sm:text-xs font-mono tracking-widest text-slate-300 uppercase font-semibold">
                IHYAUSSUNNA STUDENTS UNION
            </span>
        </div>
        <a href="{{ route('login') }}" class="text-[11px] sm:text-xs font-mono text-slate-400 hover:text-white transition-colors flex items-center gap-1.5 py-1 px-3 rounded-full bg-white/5 hover:bg-white/10 border border-white/10">
            <span>Staff Portal</span>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </header>

    <!-- Main Center Stage -->
    <main class="w-full max-w-3xl mx-auto flex flex-col items-center text-center my-auto py-8 sm:py-12 z-10 relative">
        
        <!-- QUAF Official Brand Marks with Ethereal Floating Glow -->
        <div class="relative mb-6 sm:mb-8 animate-float">
            <!-- Pulsing Radar Glow Rings behind Logo -->
            <div class="absolute -inset-6 rounded-full pointer-events-none" style="background: radial-gradient(circle, rgba(190,30,45,0.3) 0%, rgba(243,189,46,0.15) 50%, transparent 70%); filter: blur(35px);"></div>

            <div class="relative z-10 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-5 px-5 sm:px-8 py-3.5 sm:py-4 rounded-3xl bg-white/[0.05] backdrop-blur-2xl border border-white/15 shadow-[0_20px_50px_rgba(0,0,0,0.6)] ring-1 ring-white/10">
                <img src="{{ asset('images/adabic-inheritance-web.svg') }}" 
                     alt="Ādabīc Inheritance — QUAF" 
                     class="h-10 xs:h-12 sm:h-14 md:h-16 w-auto object-contain drop-shadow-[0_8px_24px_rgba(255,255,255,0.12)]">
                <span class="hidden sm:block w-px h-10 bg-white/15"></span>
                <img src="{{ asset('images/quaf-logo-hero.svg') }}" 
                     alt="QUAF 2026" 
                     class="h-9 xs:h-10 sm:h-12 md:h-14 w-auto object-contain drop-shadow-[0_8px_24px_rgba(255,255,255,0.12)]">
            </div>
        </div>

        <!-- Launch Badge -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 backdrop-blur-xl mb-4 sm:mb-5 shadow-inner">
            <span class="w-2 h-2 rounded-full bg-[#f3bd2e] shadow-[0_0_8px_#f3bd2e]"></span>
            <span class="text-[10px] sm:text-xs font-mono uppercase tracking-widest text-[#f3bd2e] font-bold">
                OFFICIAL DIGITAL LAUNCH
            </span>
        </div>

        <!-- Website Launching Titles -->
        <h1 class="text-3xl xs:text-4xl sm:text-6xl md:text-7xl font-sora font-black tracking-tight text-white uppercase leading-none mb-2 sm:mb-3 drop-shadow-md">
            QUAF 2026
        </h1>

        <h2 class="text-xl xs:text-2xl sm:text-4xl md:text-5xl font-sora font-extrabold tracking-tight uppercase bg-gradient-to-r from-amber-200 via-amber-400 to-[#f3bd2e] bg-clip-text text-transparent drop-shadow-sm mb-4 sm:mb-5">
            WEBSITE LAUNCHING
        </h2>

        <!-- Theme Note & Confluence Philosophy -->
        <p class="text-xs sm:text-sm md:text-base text-slate-300 max-w-xl font-normal leading-relaxed px-4 mb-6 sm:mb-8">
            The grand digital confluence of eloquence, arts, and intellectual heritage uniting collegiate fraternities across 140+ cultural disciplines.
        </p>

        <!-- Edition Info Pill -->
        <div class="flex flex-wrap items-center justify-center gap-2.5 sm:gap-3 text-[11px] sm:text-xs font-mono mb-8 sm:mb-10 text-slate-300">
            <span class="px-3.5 py-1.5 rounded-full bg-slate-900/80 border border-slate-700/80 backdrop-blur-md">
                31 OCT — 01 NOV 2026
            </span>
            <span class="px-3.5 py-1.5 rounded-full bg-slate-900/80 border border-slate-700/80 text-[#f3bd2e] font-bold backdrop-blur-md">
                JAMIA MARKAZ KARANTHUR
            </span>
        </div>

        <!-- Interactive Launch Action Area -->
        <div id="launchActionArea" class="w-full flex flex-col items-center">
            
            <!-- Launch Button -->
            <button id="launchBtn" 
                    type="button" 
                    class="relative group cursor-pointer transform hover:scale-105 active:scale-95 transition-all duration-300 focus:outline-none"
                    aria-label="Launch QUAF Website">
                <!-- Glowing Aura -->
                <span class="absolute -inset-1.5 rounded-full bg-gradient-to-r from-[#be1e2d] via-[#f3bd2e] to-[#be1e2d] opacity-75 group-hover:opacity-100 blur-xl transition duration-500 group-hover:duration-200 animate-pulse"></span>
                
                <!-- Button Body -->
                <span class="relative flex items-center gap-3.5 px-8 sm:px-14 py-4 sm:py-5 rounded-full bg-gradient-to-r from-[#be1e2d] via-red-600 to-[#991522] text-white font-sora font-extrabold text-sm sm:text-lg tracking-wider uppercase shadow-[0_15px_40px_rgba(190,30,45,0.5)] border border-red-400/40">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#f3bd2e] transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>LAUNCH WEBSITE</span>
                    <span class="hidden sm:inline-flex items-center text-xs font-mono font-medium px-2 py-0.5 rounded bg-black/30 border border-white/20 text-slate-200">
                        ↵ Enter
                    </span>
                </span>
            </button>

            <!-- Keyboard & Tap Hint -->
            <p class="text-[11px] sm:text-xs font-mono text-slate-400 mt-4 sm:mt-5 flex items-center gap-1.5">
                <span>Click the button or press</span>
                <kbd class="px-2 py-0.5 rounded bg-white/10 text-white font-semibold border border-white/15">Enter</kbd>
                <span>to launch</span>
            </p>
        </div>

        <!-- Cinematic Loading State (Reveals smoothly upon click or Enter) -->
        <div id="loadingOverlay" class="hidden w-full max-w-md mx-auto flex flex-col items-center text-center mt-2 transition-all duration-500">
            
            <!-- Circular High-Tech Pulse Orb -->
            <div class="relative w-24 h-24 sm:w-28 sm:h-28 flex items-center justify-center mb-6">
                <!-- Rotating Glowing Ring -->
                <svg class="w-full h-full animate-spin text-[#be1e2d]" viewBox="0 0 100 100" style="animation-duration: 2.2s;">
                    <circle class="opacity-20 text-slate-700" stroke="currentColor" stroke-width="6" fill="none" cx="50" cy="50" r="42"/>
                    <circle class="text-[#f3bd2e]" stroke="currentColor" stroke-width="6" stroke-linecap="round" fill="none" cx="50" cy="50" r="42" stroke-dasharray="264" stroke-dashoffset="90"/>
                </svg>
                
                <!-- Digital Percentage Counter -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <span id="progressPercent" class="font-mono text-xl sm:text-2xl font-bold text-white tracking-tight">0%</span>
                </div>
            </div>

            <!-- Dynamic Loading Status Text -->
            <div class="space-y-1.5 mb-5 w-full">
                <p id="loadingStatusText" class="font-mono text-xs sm:text-sm font-semibold uppercase tracking-wider text-amber-400 transition-all duration-200">
                    Initializing Conclave Core...
                </p>
                <p class="text-[11px] text-slate-400 font-mono">
                    Preparing official festival platform
                </p>
            </div>

            <!-- Smooth Linear Progress Bar -->
            <div class="w-full bg-slate-900 rounded-full h-2 p-0.5 border border-white/15 overflow-hidden shadow-inner">
                <div id="progressBarFill" 
                     class="h-full bg-gradient-to-r from-[#be1e2d] via-[#f3bd2e] to-white rounded-full transition-all duration-100 ease-out shadow-[0_0_12px_#f3bd2e]"
                     style="width: 0%;"></div>
            </div>

        </div>

    </main>

    <!-- Bottom Footer -->
    <footer class="w-full max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between text-slate-400 text-[11px] font-mono gap-2 text-center sm:text-left z-10 relative pt-4">
        <span>© 2026 QUAF — Markazu Saquafathi Sunniyya</span>
        <span class="text-slate-400">Jamia Markaz Campus, Karanthur</span>
    </footer>

    <!-- White / Gold Flash Overlay for Warp Portal Transition -->
    <div id="warpFlashScreen" class="fixed inset-0 bg-white opacity-0 pointer-events-none z-50 transition-opacity duration-500"></div>

    <!-- Interactive Launch Audio & Animation Logic -->
    <script>
        (function() {
            let isLaunching = false;
            const targetUrl = "{{ route('home.view') }}";

            const launchBtn = document.getElementById('launchBtn');
            const launchArea = document.getElementById('launchActionArea');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const progressPercent = document.getElementById('progressPercent');
            const progressBarFill = document.getElementById('progressBarFill');
            const statusText = document.getElementById('loadingStatusText');
            const flashScreen = document.getElementById('warpFlashScreen');

            // Web Audio API Synthesizer (Harmonious Ethereal Major 9th Chime + Warm Bass Sweep)
            function playLaunchSound() {
                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContext) return;
                    const ctx = new AudioContext();
                    if (ctx.state === 'suspended') {
                        ctx.resume();
                    }

                    const now = ctx.currentTime;

                    // Master Volume Envelope
                    const masterGain = ctx.createGain();
                    masterGain.gain.setValueAtTime(0.001, now);
                    masterGain.gain.exponentialRampToValueAtTime(0.35, now + 0.12);
                    masterGain.gain.exponentialRampToValueAtTime(0.0001, now + 2.8);
                    masterGain.connect(ctx.destination);

                    // Smooth lowpass filter for silky analog warmth
                    const filter = ctx.createBiquadFilter();
                    filter.type = 'lowpass';
                    filter.frequency.setValueAtTime(600, now);
                    filter.frequency.exponentialRampToValueAtTime(3600, now + 0.8);
                    filter.frequency.exponentialRampToValueAtTime(1400, now + 2.8);
                    filter.connect(masterGain);

                    // Ethereal Chord Frequencies (C Major 9th: C3, G3, C4, E4, G4, D5)
                    const freqs = [130.81, 196.00, 261.63, 329.63, 392.00, 587.33];
                    freqs.forEach((freq, idx) => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();

                        osc.type = (idx % 2 === 0) ? 'sine' : 'triangle';
                        osc.frequency.setValueAtTime(freq, now);
                        osc.frequency.exponentialRampToValueAtTime(freq * 1.008, now + 1.2);

                        const targetVol = 0.14 / freqs.length;
                        gain.gain.setValueAtTime(0.001, now);
                        gain.gain.linearRampToValueAtTime(targetVol, now + 0.08 + idx * 0.03);
                        gain.gain.exponentialRampToValueAtTime(0.00001, now + 2.5 + idx * 0.08);

                        osc.connect(gain);
                        gain.connect(filter);

                        osc.start(now + idx * 0.025);
                        osc.stop(now + 2.8);
                    });

                    // Sparkling High Chime Tone (C6)
                    const bell = ctx.createOscillator();
                    const bellGain = ctx.createGain();
                    bell.type = 'sine';
                    bell.frequency.setValueAtTime(1046.50, now);
                    bellGain.gain.setValueAtTime(0.12, now);
                    bellGain.gain.exponentialRampToValueAtTime(0.0001, now + 1.6);
                    bell.connect(bellGain);
                    bellGain.connect(masterGain);
                    bell.start(now);
                    bell.stop(now + 1.7);

                } catch (err) {
                    console.warn('Audio synthesis not supported or blocked:', err);
                }
            }

            // Arrival Chime on 100% completion
            function playArrivalSound() {
                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContext) return;
                    const ctx = new AudioContext();
                    if (ctx.state === 'suspended') ctx.resume();

                    const now = ctx.currentTime;
                    const master = ctx.createGain();
                    master.gain.setValueAtTime(0.001, now);
                    master.gain.linearRampToValueAtTime(0.3, now + 0.04);
                    master.gain.exponentialRampToValueAtTime(0.0001, now + 1.2);
                    master.connect(ctx.destination);

                    [523.25, 659.25, 783.99, 1046.50].forEach((f, i) => {
                        const osc = ctx.createOscillator();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(f, now + i * 0.04);
                        osc.connect(master);
                        osc.start(now + i * 0.04);
                        osc.stop(now + 1.2);
                    });
                } catch (e) {}
            }

            // Trigger Launch Sequence
            function startLaunch() {
                if (isLaunching) return;
                isLaunching = true;

                // 1. Play Smooth Sound Effect
                playLaunchSound();

                // 2. Hide Button Area and Show Loading Area
                launchArea.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => {
                    launchArea.classList.add('hidden');
                    loadingOverlay.classList.remove('hidden');
                    loadingOverlay.classList.add('animate-fadeIn');
                }, 300);

                // 3. Cinematic Progress Animation (0% to 100% over ~2.4 seconds)
                let currentProgress = 0;
                const statusMilestones = [
                    { at: 0, text: 'Initializing Conclave Core...' },
                    { at: 22, text: 'Loading 140+ Disciplines & Stages...' },
                    { at: 48, text: 'Syncing Academic Houses & Rosters...' },
                    { at: 75, text: 'Calibrating Ādabīc Inheritance Experience...' },
                    { at: 94, text: 'Welcome to QUAF 2026!' },
                ];

                const interval = setInterval(() => {
                    // Gradual easing progress curve
                    const increment = Math.max(1, Math.floor((100 - currentProgress) * 0.09));
                    currentProgress = Math.min(100, currentProgress + increment);

                    progressBarFill.style.width = currentProgress + '%';
                    progressPercent.textContent = currentProgress + '%';

                    // Update status text dynamically
                    for (let i = statusMilestones.length - 1; i >= 0; i--) {
                        if (currentProgress >= statusMilestones[i].at) {
                            statusText.textContent = statusMilestones[i].text;
                            break;
                        }
                    }

                    // On Complete 100%
                    if (currentProgress >= 100) {
                        clearInterval(interval);
                        playArrivalSound();

                        // Gentle flash warp expansion transition
                        setTimeout(() => {
                            flashScreen.classList.remove('opacity-0');
                            flashScreen.classList.add('opacity-100');
                        }, 250);

                        // Redirect to Home Page
                        setTimeout(() => {
                            window.location.href = targetUrl;
                        }, 650);
                    }
                }, 35);
            }

            // Listen for Click on Launch Button
            if (launchBtn) {
                launchBtn.addEventListener('click', startLaunch);
            }

            // Listen for 'Enter' key press on window
            window.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    startLaunch();
                }
            });

        })();
    </script>
</body>
</html>
