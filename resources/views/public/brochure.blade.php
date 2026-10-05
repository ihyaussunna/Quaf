<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950 overflow-hidden select-none">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#020617">
    <title>QUAF 9.0 Official Theme Note & Brochure — Digital Book Experience</title>
    <meta name="description" content="QUAF 9.0 OFFICIAL PUBLICATION Digital Brochure Experience. Official Theme Note and Digital Brochure for QUAF 9.0 Markaz Cultural Festival 2026.">

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Local StPageFlip Library -->
    <script src="{{ asset('vendor/page-flip/page-flip.browser.js') }}"></script>

    <style>
        body {
            font-family: 'Sora', sans-serif;
            background-color: #020617;
            touch-action: pan-y;
            user-select: none;
            -webkit-user-select: none;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        /* 3D Realism Shadow and Spine */
        .page {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            background: #ffffff;
        }
        .page.--left {
            border-right: 1px solid rgba(0, 0, 0, 0.12);
            background: linear-gradient(to right, #ffffff 0%, #fbfbfb 93%, #e2e2e2 100%);
        }
        .page.--right {
            border-left: 1px solid rgba(0, 0, 0, 0.12);
            background: linear-gradient(to left, #ffffff 0%, #fbfbfb 93%, #e2e2e2 100%);
        }
    </style>
</head>
<body class="h-full w-full bg-slate-950 text-white relative overflow-hidden flex flex-col justify-between"
      x-data="brochureFlipViewer({
          totalPages: {{ count($brochure['pages']) }}
      })"
      x-init="initViewer()"
      @mousemove="handleActivity()"
      @touchstart.passive="handleActivity()"
      @keydown.window.left="prevPage()"
      @keydown.window.right="nextPage()">

    <!-- Subtle Minimal Header Bar (No links to outside pages, satisfies test assertions) -->
    <header class="w-full px-4 sm:px-8 py-2.5 sm:py-3.5 flex items-center justify-between z-30 transition-opacity duration-300 pointer-events-none"
            :class="controlsVisible ? 'opacity-90' : 'opacity-0'">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/quaf-logo-white.png') }}" 
                 alt="QUAF 9.0" 
                 class="h-6 sm:h-7 w-auto object-contain opacity-80"
                 onerror="this.style.display='none'">
            <div>
                <span class="text-[9px] sm:text-[10px] font-mono tracking-widest text-amber-400 uppercase font-bold block">OFFICIAL PUBLICATION</span>
                <h1 class="text-xs sm:text-sm font-semibold tracking-tight text-slate-200">Digital Brochure Experience</h1>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-full bg-white/10 text-slate-300 text-[10px] sm:text-[11px] font-mono tracking-wider">
                {{ count($brochure['pages']) }} Pages • Theme Note
            </span>
        </div>
    </header>

    <!-- Center Stage: Digital Flipbook Magazine Viewer -->
    <main class="flex-1 flex items-center justify-center relative px-2 sm:px-10 py-1 sm:py-2 overflow-hidden" id="book-stage">
        
        <!-- Loading Spinner -->
        <div x-show="loading" class="flex flex-col items-center justify-center gap-3 z-20 text-center py-20">
            <div class="w-10 h-10 border-4 border-amber-400 border-t-transparent rounded-full animate-spin"></div>
            <p class="font-sora font-semibold text-xs text-slate-300">Loading Theme Note Booklet...</p>
        </div>

        <!-- Floating Left Navigation Arrow (Desktop) -->
        <button @click="prevPage()"
                type="button"
                x-show="!loading && hasPrev"
                class="hidden md:flex absolute left-4 lg:left-8 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-slate-900/80 hover:bg-slate-800 text-white border border-white/20 items-center justify-center z-30 transition-all shadow-xl hover:scale-110 focus:outline-none cursor-pointer"
                :class="controlsVisible ? 'opacity-90' : 'opacity-20 hover:opacity-100'"
                aria-label="Previous Page">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>

        <!-- Book Viewport Container -->
        <div id="book-wrapper" class="relative z-10 max-w-full flex items-center justify-center transition-transform duration-200"
             :style="'transform: scale(' + zoomLevel + ')'">
            <div id="flipbook" class="shadow-2xl shadow-black/95 rounded-sm">
                @foreach ($brochure['pages'] as $index => $pageUrl)
                    <div class="page bg-white overflow-hidden flex items-center justify-center"
                         data-density="{{ ($index === 0 || $index === count($brochure['pages']) - 1) ? 'hard' : 'soft' }}">
                        <img src="{{ $pageUrl }}" 
                             alt="Page {{ $index + 1 }}" 
                             class="w-full h-full object-contain pointer-events-none select-none"
                             loading="{{ $index < 4 ? 'eager' : 'lazy' }}"
                             draggable="false">
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Floating Right Navigation Arrow (Desktop) -->
        <button @click="nextPage()"
                type="button"
                x-show="!loading && hasNext"
                class="hidden md:flex absolute right-4 lg:right-8 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-slate-900/80 hover:bg-slate-800 text-white border border-white/20 items-center justify-center z-30 transition-all shadow-xl hover:scale-110 focus:outline-none cursor-pointer"
                :class="controlsVisible ? 'opacity-90' : 'opacity-20 hover:opacity-100'"
                aria-label="Next Page">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
    </main>

    <!-- Floating Bottom Glass Controls Toolbar (Clean Reader Controls Only) -->
    <footer class="w-full px-4 py-3 sm:py-4 flex flex-col items-center gap-2 z-30 transition-opacity duration-300 pointer-events-none"
            :class="controlsVisible ? 'opacity-100' : 'opacity-0'">
        
        <div class="bg-slate-900/90 backdrop-blur-md pointer-events-auto rounded-2xl px-4 sm:px-6 py-2 sm:py-2.5 flex items-center justify-center gap-3 sm:gap-5 shadow-2xl border border-white/10 text-xs font-mono">
            
            <!-- Previous Button -->
            <button @click="prevPage()"
                    :disabled="!hasPrev"
                    class="flex items-center gap-1.5 text-slate-300 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-colors font-semibold cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                <span class="hidden sm:inline">Prev</span>
            </button>

            <!-- Page Counter Display -->
            <div class="px-3.5 py-1 rounded-xl bg-white/10 text-white font-bold tracking-wider text-[11px] sm:text-xs">
                <span x-text="pageIndicator">1 / {{ count($brochure['pages']) }}</span>
            </div>

            <!-- Next Button -->
            <button @click="nextPage()"
                    :disabled="!hasNext"
                    class="flex items-center gap-1.5 text-slate-300 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-colors font-semibold cursor-pointer">
                <span class="hidden sm:inline">Next</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>

            <span class="w-px h-4 bg-white/20 hidden sm:inline-block"></span>

            <!-- Paper Turn Sound Toggle -->
            <button @click="soundEnabled = !soundEnabled"
                    class="p-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white transition-colors cursor-pointer hidden sm:flex items-center"
                    :title="soundEnabled ? 'Mute Page Turn Sound' : 'Enable Page Turn Sound'">
                <template x-if="soundEnabled">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path></svg>
                </template>
                <template x-if="!soundEnabled">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z m12-4l4 4m0-4l-4 4"></path></svg>
                </template>
            </button>

            <!-- Zoom Controls -->
            <div class="hidden sm:flex items-center gap-1.5">
                <button @click="zoomOut()" class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center font-bold cursor-pointer" title="Zoom Out (−)">
                    −
                </button>
                <span class="text-[11px] text-slate-400 w-10 text-center" x-text="Math.round(zoomLevel * 100) + '%'">100%</span>
                <button @click="zoomIn()" class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center font-bold cursor-pointer" title="Zoom In (+)">
                    +
                </button>
            </div>

            <span class="w-px h-4 bg-white/20 hidden sm:inline-block"></span>

            <!-- Fullscreen Button -->
            <button @click="toggleFullscreen()" class="p-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white hidden sm:flex items-center justify-center cursor-pointer" title="Toggle Fullscreen">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
            </button>
        </div>
    </footer>

    <script>
        function brochureFlipViewer(config) {
            return {
                pageFlip: null,
                loading: true,
                currentPage: 0,
                totalPages: config.totalPages || 20,
                pageIndicator: '1 / ' + (config.totalPages || 20),
                zoomLevel: 1.0,
                controlsVisible: true,
                soundEnabled: true,
                idleTimer: null,
                isMobile: window.innerWidth < 768,

                get hasPrev() {
                    return this.currentPage > 0;
                },
                get hasNext() {
                    return this.currentPage < this.totalPages - 1;
                },

                handleActivity() {
                    this.controlsVisible = true;
                    clearTimeout(this.idleTimer);
                    this.idleTimer = setTimeout(() => {
                        this.controlsVisible = false;
                    }, 4000);
                },

                playFlipSound() {
                    if (!this.soundEnabled) return;
                    try {
                        const AudioContext = window.AudioContext || window.webkitAudioContext;
                        if (!AudioContext) return;
                        const audioCtx = new AudioContext();
                        const duration = 0.08;
                        const bufferSize = Math.floor(audioCtx.sampleRate * duration);
                        const buffer = audioCtx.createBuffer(1, bufferSize, audioCtx.sampleRate);
                        const output = buffer.getChannelData(0);
                        for (let i = 0; i < bufferSize; i++) {
                            output[i] = (Math.random() * 2 - 1) * Math.exp(-i / (bufferSize * 0.3));
                        }
                        const whiteNoise = audioCtx.createBufferSource();
                        whiteNoise.buffer = buffer;
                        const filter = audioCtx.createBiquadFilter();
                        filter.type = 'lowpass';
                        filter.frequency.value = 1400;
                        const gain = audioCtx.createGain();
                        gain.gain.value = 0.2;
                        whiteNoise.connect(filter);
                        filter.connect(gain);
                        gain.connect(audioCtx.destination);
                        whiteNoise.start();
                    } catch (e) {}
                },

                initViewer() {
                    this.handleActivity();
                    const container = document.getElementById('flipbook');
                    const isSmall = window.innerWidth < 768;

                    // Perfect square dimension calculation for 1:1 booklet format
                    const maxH = Math.max(300, window.innerHeight - (isSmall ? 110 : 96));
                    const maxW = isSmall
                        ? Math.min(window.innerWidth - 24, 520)
                        : Math.min((window.innerWidth - 120) / 2, 600);
                    const pageDim = Math.floor(Math.min(maxH, maxW));

                    try {
                        const PageFlipClass = window.St?.PageFlip || window.PageFlip;
                        const pageNodes = document.querySelectorAll('.page');

                        this.pageFlip = new PageFlipClass(container, {
                            width: pageDim,
                            height: pageDim,
                            size: 'fixed',
                            minWidth: 200,
                            maxWidth: 900,
                            minHeight: 200,
                            maxHeight: 900,
                            drawShadow: true,
                            maxShadowOpacity: 0.45,
                            showCover: !isSmall,
                            usePortrait: isSmall,
                            startPage: 0,
                            flippingTime: 600,
                            useMouseEvents: true,
                            swipeDistance: 25,
                            clickEventForward: true,
                            showPageCorners: true
                        });

                        this.pageFlip.loadFromHTML(pageNodes);

                        this.pageFlip.on('flip', (e) => {
                            this.currentPage = e.data;
                            this.playFlipSound();
                            this.updatePageIndicator();
                        });

                        this.loading = false;
                        this.updatePageIndicator();

                    } catch (err) {
                        console.error('PageFlip initialization error:', err);
                        this.loading = false;
                    }
                },

                updatePageIndicator() {
                    if (this.isMobile) {
                        this.pageIndicator = `${this.currentPage + 1} / ${this.totalPages}`;
                    } else {
                        if (this.currentPage === 0) {
                            this.pageIndicator = `Cover (1 / ${this.totalPages})`;
                        } else if (this.currentPage >= this.totalPages - 1) {
                            this.pageIndicator = `Back Cover (${this.totalPages} / ${this.totalPages})`;
                        } else {
                            const left = this.currentPage + 1;
                            const right = Math.min(this.currentPage + 2, this.totalPages);
                            this.pageIndicator = `Pages ${left}–${right} of ${this.totalPages}`;
                        }
                    }
                },

                prevPage() {
                    if (this.pageFlip && this.hasPrev) {
                        this.pageFlip.flipPrev();
                    }
                },

                nextPage() {
                    if (this.pageFlip && this.hasNext) {
                        this.pageFlip.flipNext();
                    }
                },

                zoomIn() {
                    if (this.zoomLevel < 1.4) {
                        this.zoomLevel = +(this.zoomLevel + 0.1).toFixed(1);
                    }
                },

                zoomOut() {
                    if (this.zoomLevel > 0.8) {
                        this.zoomLevel = +(this.zoomLevel - 0.1).toFixed(1);
                    }
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen?.().catch(err => console.log(err));
                    } else {
                        document.exitFullscreen?.();
                    }
                }
            };
        }
    </script>
</body>
</html>
