<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <title>QUAF 2026 — Official Website Launch</title>
    <meta name="description" content="Official Website Launch for QUAF Markaz Cultural Festival 2026. Confluence of eloquence, arts, and intellectual heritage.">

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
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
            background-color: #ffffff;
            color: #0f172a;
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

        /* Subtle Ambient Glow */
        @keyframes ambientPulse {
            0%, 100% {
                transform: scale(1) translate3d(0, 0, 0);
                opacity: 0.55;
            }
            50% {
                transform: scale(1.12) translate3d(10px, -10px, 0);
                opacity: 0.75;
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
                transform: translateY(-6px);
            }
        }
        .animate-float {
            animation: floatGentle 5s ease-in-out infinite;
        }

        /* Flash on transition */
        @keyframes warpFlash {
            0% {
                opacity: 0;
                transform: scale(0.98);
            }
            50% {
                opacity: 1;
                transform: scale(1.02);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
        .warp-active {
            animation: warpFlash 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="flex flex-col justify-between items-center min-h-[100dvh] relative overflow-y-auto overflow-x-hidden px-4 py-2 sm:py-4 md:py-5 bg-white">

    <!-- Ambient Subtle Multi-Color Atmosphere (Official QUAF Festival Palette) -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[340px] sm:w-[650px] h-[340px] sm:h-[650px] rounded-full animate-ambient-1 pointer-events-none" style="background: rgba(190, 30, 45, 0.05); filter: blur(120px); -webkit-filter: blur(120px);"></div>
        <div class="absolute top-12 left-10 w-72 sm:w-[450px] h-72 sm:h-[450px] rounded-full animate-ambient-2 pointer-events-none" style="background: rgba(243, 189, 46, 0.06); filter: blur(120px); -webkit-filter: blur(120px);"></div>
        <div class="absolute bottom-12 right-10 w-72 sm:w-[500px] h-72 sm:h-[500px] rounded-full animate-ambient-3 pointer-events-none" style="background: rgba(0, 92, 148, 0.05); filter: blur(120px); -webkit-filter: blur(120px);"></div>
        <div class="absolute bottom-1/4 left-1/4 w-60 sm:w-[380px] h-60 sm:h-[380px] rounded-full pointer-events-none" style="background: rgba(0, 148, 68, 0.04); filter: blur(100px); -webkit-filter: blur(100px);"></div>
    </div>

    <!-- Clean Subtle Pattern Grid Overlay -->
    <div class="fixed inset-0 pointer-events-none z-0 opacity-40 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:24px_24px]"></div>

    <!-- Top Action Bar (Fullscreen Toggle) -->
    <div class="w-full max-w-6xl mx-auto flex items-center justify-end z-20 relative">
        <button id="fullscreenToggleBtn" 
                type="button" 
                title="Full Screen (F11)" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-900 border border-slate-200/80 text-xs font-mono transition shadow-2xs cursor-pointer focus:outline-none">
            <svg id="fsEnterIcon" class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
            </svg>
            <svg id="fsExitIcon" class="w-3.5 h-3.5 text-slate-700 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9L4 4m0 5h5m-5 0V4m11 5l5-5m-5 5h5m-5 0V4M9 15l-5 5m0-5h5m-5 0v5m11-5l5 5m-5-5h5m-5 0v5"/>
            </svg>
            <span id="fsBtnLabel">Full Screen (F11)</span>
        </button>
    </div>

    <!-- Main Center Stage -->
    <main class="w-full max-w-3xl mx-auto flex flex-col items-center text-center my-auto py-2 sm:py-4 md:py-6 z-10 relative">
        
        <!-- Official Logo -->
        <div class="relative mb-3 sm:mb-4 animate-float w-full flex items-center justify-center">
            <div class="p-1 sm:p-2 max-w-full">
                <img src="{{ asset('images/quaf-launch-logo.svg') }}" 
                     alt="QUAF 2026 — Markaz Cultural Festival" 
                     class="w-full max-w-[260px] xs:max-w-[320px] sm:max-w-[380px] md:max-w-[420px] max-h-[90px] sm:max-h-[115px] h-auto object-contain mx-auto drop-shadow-sm transition-transform duration-300">
            </div>
        </div>

        <!-- Launch Badge -->
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50/90 border border-amber-200 text-amber-900 mb-2 sm:mb-3 shadow-2xs">
            <span class="w-2 h-2 rounded-full bg-[#f3bd2e] shadow-[0_0_6px_#f3bd2e]"></span>
            <span class="text-[10px] sm:text-xs font-mono uppercase tracking-widest font-bold">
                OFFICIAL DIGITAL LAUNCH
            </span>
        </div>

        <!-- Website Launching Titles -->
        <h1 class="text-3xl xs:text-4xl sm:text-5xl md:text-6xl font-sora font-black tracking-tight text-slate-950 uppercase leading-none mb-1 sm:mb-2">
            QUAF 2026
        </h1>

        <h2 class="text-lg xs:text-xl sm:text-3xl md:text-4xl font-sora font-extrabold tracking-tight uppercase bg-gradient-to-r from-[#be1e2d] via-amber-600 to-[#be1e2d] bg-clip-text text-transparent mb-2 sm:mb-3">
            WEBSITE LAUNCHING
        </h2>

        <!-- Theme Note & Philosophy -->
        <p id="themeNote" class="text-xs sm:text-sm text-slate-600 max-w-xl font-normal leading-relaxed px-4 mb-3 sm:mb-4 font-sora transition-all duration-300">
            The grand digital confluence of eloquence, arts, and intellectual heritage uniting collegiate fraternities across 140+ cultural disciplines.
        </p>

        <!-- Edition Info Pill -->
        <div id="editionPill" class="flex flex-wrap items-center justify-center gap-2 sm:gap-2.5 text-[10px] sm:text-xs font-mono mb-4 sm:mb-6 text-slate-700 transition-all duration-300">
            <span class="px-3.5 py-1 rounded-full bg-slate-50 border border-slate-200/90 shadow-2xs font-semibold">
                31 OCT — 01 NOV 2026
            </span>
            <span class="px-3.5 py-1 rounded-full bg-slate-50 border border-slate-200/90 text-[#be1e2d] font-bold shadow-2xs">
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
                <span class="absolute -inset-1.5 rounded-full bg-gradient-to-r from-[#be1e2d] via-[#f3bd2e] to-[#be1e2d] opacity-50 group-hover:opacity-85 blur-lg transition duration-500 group-hover:duration-200 animate-pulse"></span>
                
                <!-- Button Body -->
                <span class="relative flex items-center gap-3 px-8 sm:px-12 py-3.5 sm:py-4 rounded-full bg-gradient-to-r from-[#be1e2d] via-red-600 to-[#991522] text-white font-sora font-extrabold text-sm sm:text-base tracking-wider uppercase shadow-[0_12px_28px_rgba(190,30,45,0.3)] border border-red-400/40">
                    <svg class="w-5 h-5 text-[#f3bd2e] transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>LAUNCH WEBSITE</span>
                </span>
            </button>
        </div>

        <!-- Loading State (Reveals smoothly upon click or Enter) -->
        <div id="loadingOverlay" class="hidden w-full max-w-md mx-auto flex flex-col items-center text-center mt-1 sm:mt-2 transition-all duration-500">
            
            <!-- Circular High-Tech Pulse Orb -->
            <div class="relative w-20 h-20 sm:w-24 sm:h-24 flex items-center justify-center mb-3 sm:mb-4">
                <!-- Rotating Glowing Ring -->
                <svg class="w-full h-full animate-spin text-[#be1e2d]" viewBox="0 0 100 100" style="animation-duration: 2.2s;">
                    <circle class="text-slate-200" stroke="currentColor" stroke-width="6" fill="none" cx="50" cy="50" r="42"/>
                    <circle class="text-[#f3bd2e]" stroke="currentColor" stroke-width="6" stroke-linecap="round" fill="none" cx="50" cy="50" r="42" stroke-dasharray="264" stroke-dashoffset="90"/>
                </svg>
                
                <!-- Digital Percentage Counter -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <span id="progressPercent" class="font-mono text-lg sm:text-2xl font-bold text-slate-950 tracking-tight">0%</span>
                </div>
            </div>

            <!-- Dynamic Loading Status Text -->
            <div class="space-y-1 mb-3 sm:mb-4 w-full px-2">
                <p id="loadingStatusText" class="font-mono text-xs sm:text-base font-bold uppercase tracking-wider text-[#be1e2d] transition-all duration-200">
                    Initializing Conclave Core...
                </p>
                <p class="text-[10px] sm:text-xs text-slate-600 font-mono font-medium">
                    Preparing official festival platform
                </p>
            </div>

            <!-- Smooth Linear Progress Bar -->
            <div class="w-full max-w-xs sm:max-w-sm mx-auto bg-slate-100 rounded-full h-2 sm:h-2.5 p-0.5 border border-slate-200 overflow-hidden shadow-inner">
                <div id="progressBarFill" 
                     class="h-full bg-gradient-to-r from-[#be1e2d] via-[#f3bd2e] to-emerald-500 rounded-full transition-all duration-100 ease-out shadow-[0_0_10px_#f3bd2e]"
                     style="width: 0%;"></div>
            </div>

        </div>

    </main>

    <!-- Bottom Footer -->
    <footer class="w-full max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between text-slate-500 text-[10px] sm:text-[11px] font-mono gap-1.5 text-center sm:text-left z-10 relative pt-2 sm:pt-3 border-t border-slate-100">
        <span>&copy; 2026 QUAF &mdash; Markazu Saquafathi Sunniyya</span>
        <span class="text-slate-500">Jamia Markaz Campus, Karanthur</span>
    </footer>

    <!-- White Flash Overlay for Transition -->
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

            // Web Audio API Synthesizer (Harmonious Ethereal Major 9th Chime)
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

                    // Smooth lowpass filter
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

            const themeNote = document.getElementById('themeNote');
            const fsBtn = document.getElementById('fullscreenToggleBtn');
            const fsEnterIcon = document.getElementById('fsEnterIcon');
            const fsExitIcon = document.getElementById('fsExitIcon');
            const fsBtnLabel = document.getElementById('fsBtnLabel');

            // Fullscreen Helper Functions
            function updateFullscreenUI() {
                const isFs = !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement);
                if (isFs) {
                    if (fsEnterIcon) fsEnterIcon.classList.add('hidden');
                    if (fsExitIcon) fsExitIcon.classList.remove('hidden');
                    if (fsBtnLabel) fsBtnLabel.textContent = 'Exit Full Screen (F11)';
                } else {
                    if (fsEnterIcon) fsEnterIcon.classList.remove('hidden');
                    if (fsExitIcon) fsExitIcon.classList.add('hidden');
                    if (fsBtnLabel) fsBtnLabel.textContent = 'Full Screen (F11)';
                }
            }

            function toggleFullScreen() {
                if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.mozFullScreenElement && !document.msFullscreenElement) {
                    const elem = document.documentElement;
                    if (elem.requestFullscreen) {
                        elem.requestFullscreen().catch(() => {});
                    } else if (elem.webkitRequestFullscreen) {
                        elem.webkitRequestFullscreen();
                    } else if (elem.msRequestFullscreen) {
                        elem.msRequestFullscreen();
                    }
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen().catch(() => {});
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    } else if (document.msExitFullscreen) {
                        document.msExitFullscreen();
                    }
                }
            }

            if (fsBtn) {
                fsBtn.addEventListener('click', toggleFullScreen);
            }

            document.addEventListener('fullscreenchange', updateFullscreenUI);
            document.addEventListener('webkitfullscreenchange', updateFullscreenUI);
            document.addEventListener('mozfullscreenchange', updateFullscreenUI);
            document.addEventListener('MSFullscreenChange', updateFullscreenUI);

            // Trigger Launch Sequence
            function startLaunch() {
                if (isLaunching) return;
                isLaunching = true;

                // 1. Play Sound Effect
                playLaunchSound();

                // 2. Hide Button Area and optimize vertical space
                launchArea.classList.add('opacity-0', 'pointer-events-none');
                if (themeNote) {
                    themeNote.classList.add('opacity-0');
                }

                setTimeout(() => {
                    launchArea.classList.add('hidden');
                    if (themeNote) {
                        themeNote.classList.add('hidden');
                    }
                    loadingOverlay.classList.remove('hidden');

                    // Ensure loading area is fully centered in view
                    if (loadingOverlay) {
                        loadingOverlay.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                }, 250);

                // 3. Cinematic Progress Animation (0% to 100% over ~2.4 seconds)
                let currentProgress = 0;
                const statusMilestones = [
                    { at: 0, text: 'Initializing Conclave Core...' },
                    { at: 22, text: 'Loading 140+ Disciplines & Stages...' },
                    { at: 48, text: 'Syncing Academic Houses & Rosters...' },
                    { at: 75, text: 'Calibrating Adabic Inheritance Experience...' },
                    { at: 94, text: 'Welcome to QUAF 2026!' },
                ];

                const interval = setInterval(() => {
                    const increment = Math.max(1, Math.floor((100 - currentProgress) * 0.09));
                    currentProgress = Math.min(100, currentProgress + increment);

                    progressBarFill.style.width = currentProgress + '%';
                    progressPercent.textContent = currentProgress + '%';

                    for (let i = statusMilestones.length - 1; i >= 0; i--) {
                        if (currentProgress >= statusMilestones[i].at) {
                            statusText.textContent = statusMilestones[i].text;
                            break;
                        }
                    }

                    if (currentProgress >= 100) {
                        clearInterval(interval);
                        playArrivalSound();

                        setTimeout(() => {
                            flashScreen.classList.remove('opacity-0');
                            flashScreen.classList.add('opacity-100');
                        }, 250);

                        setTimeout(() => {
                            window.location.href = targetUrl;
                        }, 650);
                    }
                }, 35);
            }

            if (launchBtn) {
                launchBtn.addEventListener('click', startLaunch);
            }

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
