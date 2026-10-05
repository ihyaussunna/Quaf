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
            user-select: none;
            -webkit-user-select: none;
            touch-action: pan-y;
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
<body class="h-[100dvh] w-full bg-slate-950 text-white relative overflow-hidden flex flex-col justify-between"
      x-data="brochureFlipViewer({
          totalPages: {{ count($brochure['pages']) }}
      })"
      x-init="initViewer()"
      @mousemove="handleActivity()"
      @touchstart.passive="handleActivity()"
      @keydown.window.left="prevPage()"
      @keydown.window.right="nextPage()">

    <!-- Subtle Minimal Header Bar (No outside links, satisfies test assertions) -->
    <header class="w-full px-4 sm:px-8 py-2 sm:py-3 flex items-center justify-between z-30 transition-opacity duration-300 pointer-events-none shrink-0"
            :class="controlsVisible ? 'opacity-90' : 'opacity-0'">
        <div class="flex items-center gap-2.5">
            <img src="{{ asset('images/quaf-logo-white.png') }}" 
                 alt="QUAF 9.0" 
                 class="h-5 sm:h-7 w-auto object-contain opacity-80"
                 onerror="this.style.display='none'">
            <div>
                <h1 class="text-xs sm:text-sm font-bold tracking-wider text-amber-400 uppercase font-sora">Theme Note</h1>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full bg-white/10 text-slate-300 text-[10px] sm:text-[11px] font-mono tracking-wider">
                {{ count($brochure['pages']) }} Pages
            </span>
        </div>
    </header>

    <!-- Center Stage: Digital Flipbook Magazine Viewer with Double-Tap Zoom & Pan -->
    <main class="flex-1 flex items-center justify-center relative px-2 sm:px-10 py-1 sm:py-2 overflow-hidden w-full" 
          id="book-stage">
        
        <!-- Loading Spinner -->
        <div x-show="loading" class="flex flex-col items-center justify-center gap-3 z-20 text-center py-20">
            <div class="w-10 h-10 border-4 border-amber-400 border-t-transparent rounded-full animate-spin"></div>
            <p class="font-sora font-semibold text-xs text-slate-300">Loading Theme Note Booklet...</p>
        </div>

        <!-- Floating Left Navigation Arrow (Visible on Mobile & Desktop) -->
        <button @click="prevPage()"
                type="button"
                x-show="!loading && hasPrev && zoomLevel === 1.0"
                class="flex absolute left-2 sm:left-4 lg:left-8 top-1/2 -translate-y-1/2 w-9 h-9 sm:w-12 sm:h-12 rounded-full bg-slate-900/80 hover:bg-slate-800 text-white border border-white/20 items-center justify-center z-30 transition-all shadow-xl active:scale-95 focus:outline-none cursor-pointer"
                :class="controlsVisible ? 'opacity-90' : 'opacity-20 hover:opacity-100'"
                aria-label="Previous Page">
            <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>

        <!-- Book Viewport Container (Double Tap / Double Touch Pinch & Pan) -->
        <div id="book-wrapper" 
             class="relative z-10 max-w-full flex items-center justify-center transition-transform duration-150 origin-center"
             :style="`transform: translate3d(${panX}px, ${panY}px, 0) scale(${zoomLevel})`"
             @touchstart="handleTouchStart($event)"
             @touchmove="handleTouchMove($event)"
             @touchend="handleTouchEnd($event)"
             @dblclick="toggleZoom()">
            
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

        <!-- Floating Right Navigation Arrow (Visible on Mobile & Desktop) -->
        <button @click="nextPage()"
                type="button"
                x-show="!loading && hasNext && zoomLevel === 1.0"
                class="flex absolute right-2 sm:right-4 lg:right-8 top-1/2 -translate-y-1/2 w-9 h-9 sm:w-12 sm:h-12 rounded-full bg-slate-900/80 hover:bg-slate-800 text-white border border-white/20 items-center justify-center z-30 transition-all shadow-xl active:scale-95 focus:outline-none cursor-pointer"
                :class="controlsVisible ? 'opacity-90' : 'opacity-20 hover:opacity-100'"
                aria-label="Next Page">
            <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
    </main>

    <!-- Floating Bottom Glass Controls Toolbar -->
    <footer class="w-full px-2 sm:px-4 py-2 sm:py-3.5 flex flex-col items-center gap-1.5 sm:gap-2 z-30 transition-opacity duration-300 pointer-events-none shrink-0"
            :class="controlsVisible ? 'opacity-100' : 'opacity-0'">
        
        <div class="bg-slate-900/95 backdrop-blur-md pointer-events-auto rounded-2xl px-3 sm:px-6 py-1.5 sm:py-2.5 flex items-center justify-center gap-2 sm:gap-4 shadow-2xl border border-white/10 text-xs font-mono max-w-[95vw]">
            
            <!-- Previous Button -->
            <button @click="prevPage()"
                    :disabled="!hasPrev"
                    class="flex items-center gap-1 text-slate-300 hover:text-white disabled:opacity-25 disabled:cursor-not-allowed transition-colors font-semibold cursor-pointer px-2 py-1.5 rounded-lg active:bg-white/10">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                <span class="hidden sm:inline">Prev</span>
            </button>

            <!-- Page Counter Display -->
            <div class="px-2.5 sm:px-3.5 py-1 rounded-xl bg-white/10 text-white font-bold tracking-wider text-[11px] sm:text-xs shrink-0">
                <span x-text="pageIndicator">1 / {{ count($brochure['pages']) }}</span>
            </div>

            <!-- Next Button -->
            <button @click="nextPage()"
                    :disabled="!hasNext"
                    class="flex items-center gap-1 text-slate-300 hover:text-white disabled:opacity-25 disabled:cursor-not-allowed transition-colors font-semibold cursor-pointer px-2 py-1.5 rounded-lg active:bg-white/10">
                <span class="hidden sm:inline">Next</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>

            <!-- Download PDF Button -->
            <a href="{{ $brochure['pdf_url'] }}" 
               download="QUAF_9.0_Official_Theme_Note.pdf"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#be1e2d] hover:bg-[#991522] text-white font-bold text-xs uppercase tracking-wider shadow-md active:scale-95 transition-all cursor-pointer shrink-0"
               title="Download Theme Note PDF">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span class="font-sora text-[11px] font-semibold">PDF</span>
            </a>

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
                <button @click="zoomOut()" 
                        class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center font-bold cursor-pointer" 
                        title="Zoom Out (−)">
                    −
                </button>
                <button @click="toggleZoom()" 
                        class="text-[11px] text-slate-300 hover:text-white px-1.5 py-0.5 rounded bg-white/5 hover:bg-white/10 cursor-pointer text-center" 
                        title="Toggle Zoom">
                    <span x-text="Math.round(zoomLevel * 100) + '%'">100%</span>
                </button>
                <button @click="zoomIn()" 
                        class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center font-bold cursor-pointer" 
                        title="Zoom In (+)">
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
                isFlipping: false,
                currentPage: 0,
                totalPages: config.totalPages || 20,
                pageIndicator: '1 / ' + (config.totalPages || 20),
                zoomLevel: 1.0,
                panX: 0,
                panY: 0,
                isPanning: false,
                panStartX: 0,
                panStartY: 0,
                isPinching: false,
                pinchStartDist: 0,
                initialPinchZoom: 1.0,
                lastTapTime: 0,
                lastTapX: 0,
                lastTapY: 0,
                touchStartX: 0,
                touchStartY: 0,
                controlsVisible: true,
                soundEnabled: true,
                idleTimer: null,
                isMobile: window.innerWidth < 768,

                get hasPrev() {
                    const cur = this.pageFlip ? this.pageFlip.getCurrentPageIndex() : this.currentPage;
                    return cur > 0;
                },
                get hasNext() {
                    const cur = this.pageFlip ? this.pageFlip.getCurrentPageIndex() : this.currentPage;
                    return cur < this.totalPages - 1;
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

                    // Available width for the two-page spread (both pages side-by-side on mobile and laptop)
                    const availableWidth = window.innerWidth - (isSmall ? 16 : 80);
                    const maxSinglePageWidth = Math.floor(availableWidth / 2);
                    const maxSinglePageHeight = Math.max(160, Math.floor(window.innerHeight - (isSmall ? 130 : 100)));
                    const pageDim = Math.min(maxSinglePageWidth, maxSinglePageHeight);

                    try {
                        const PageFlipClass = window.St?.PageFlip || window.PageFlip;
                        const pageNodes = document.querySelectorAll('.page');

                        this.pageFlip = new PageFlipClass(container, {
                            width: pageDim,
                            height: pageDim,
                            size: 'fixed',
                            minWidth: 140,
                            maxWidth: 900,
                            minHeight: 140,
                            maxHeight: 900,
                            drawShadow: true,
                            maxShadowOpacity: 0.45,
                            showCover: true,           // Always show cover on the right, then 2-page spreads!
                            usePortrait: false,        // Always two-page spread, exactly like on laptop!
                            startPage: 0,
                            flippingTime: 500,
                            useMouseEvents: true,
                            swipeDistance: 99999,      // Prevent duplicate internal swipe triggers on mobile
                            clickEventForward: false,
                            disableFlipByClick: true,  // Tap/click will NEVER flip the page!
                            showPageCorners: false    // No corner flip on simple touch
                        });

                        this.pageFlip.loadFromHTML(pageNodes);

                        this.pageFlip.on('flip', (e) => {
                            this.currentPage = e.data;
                            this.isFlipping = false;
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

                // Double tap, single swipe, and multi-touch gesture handlers
                handleTouchStart(e) {
                    this.handleActivity();

                    // Two-finger touch (Pinch Zoom / Double Touch): Never flip page
                    if (e.touches.length >= 2) {
                        this.isPinching = true;
                        this.pinchStartDist = Math.hypot(
                            e.touches[0].clientX - e.touches[1].clientX,
                            e.touches[0].clientY - e.touches[1].clientY
                        );
                        this.initialPinchZoom = this.zoomLevel;
                        e.stopPropagation();
                        return;
                    }

                    // Single touch handling for Double Tap Detection
                    const now = Date.now();
                    const touch = e.touches[0];
                    const timeDelta = now - this.lastTapTime;
                    const distDelta = Math.hypot(touch.clientX - this.lastTapX, touch.clientY - this.lastTapY);

                    if (timeDelta < 320 && distDelta < 40) {
                        // Double Tap: Toggle Zoom in/out without flipping!
                        e.preventDefault();
                        e.stopPropagation();
                        this.toggleZoom();
                        this.lastTapTime = 0;
                        return;
                    }

                    this.lastTapTime = now;
                    this.lastTapX = touch.clientX;
                    this.lastTapY = touch.clientY;
                    this.touchStartX = touch.clientX;
                    this.touchStartY = touch.clientY;

                    // If already zoomed in, drag pans the view instead of flipping
                    if (this.zoomLevel > 1.0) {
                        this.isPanning = true;
                        this.panStartX = touch.clientX - this.panX;
                        this.panStartY = touch.clientY - this.panY;
                        e.stopPropagation();
                    }
                },

                handleTouchMove(e) {
                    // Pinch to zoom
                    if (e.touches.length >= 2 && this.isPinching) {
                        e.preventDefault();
                        e.stopPropagation();
                        const currentDist = Math.hypot(
                            e.touches[0].clientX - e.touches[1].clientX,
                            e.touches[0].clientY - e.touches[1].clientY
                        );
                        if (this.pinchStartDist > 0) {
                            const scaleMultiplier = currentDist / this.pinchStartDist;
                            let newZoom = +(this.initialPinchZoom * scaleMultiplier).toFixed(2);
                            newZoom = Math.max(1.0, Math.min(2.5, newZoom));
                            this.zoomLevel = newZoom;
                            if (this.zoomLevel === 1.0) {
                                this.panX = 0;
                                this.panY = 0;
                            }
                        }
                        return;
                    }

                    // Panning when zoomed in
                    if (this.zoomLevel > 1.0 && this.isPanning && e.touches.length === 1) {
                        e.preventDefault();
                        e.stopPropagation();
                        const touch = e.touches[0];
                        const newPanX = touch.clientX - this.panStartX;
                        const newPanY = touch.clientY - this.panStartY;
                        const maxPanX = (window.innerWidth * (this.zoomLevel - 1)) / 1.5;
                        const maxPanY = (window.innerHeight * (this.zoomLevel - 1)) / 1.5;
                        this.panX = Math.max(-maxPanX, Math.min(maxPanX, newPanX));
                        this.panY = Math.max(-maxPanY, Math.min(maxPanY, newPanY));
                    }
                },

                handleTouchEnd(e) {
                    if (e.touches.length < 2) {
                        this.isPinching = false;
                    }
                    if (e.touches.length === 0) {
                        // Single-step clean horizontal swipe when not zoomed in
                        if (!this.isPanning && this.zoomLevel === 1.0 && this.touchStartX && e.changedTouches && e.changedTouches.length > 0) {
                            const touch = e.changedTouches[0];
                            const deltaX = touch.clientX - this.touchStartX;
                            const deltaY = touch.clientY - this.touchStartY;
                            // Deliberate horizontal swipe (> 30px, predominantly horizontal)
                            if (Math.abs(deltaX) > 30 && Math.abs(deltaX) > Math.abs(deltaY) * 1.1) {
                                if (deltaX < 0) {
                                    this.nextPage();
                                } else {
                                    this.prevPage();
                                }
                            }
                        }
                        this.isPanning = false;
                        this.touchStartX = 0;
                        this.touchStartY = 0;
                    }
                },

                toggleZoom() {
                    if (this.zoomLevel > 1.0) {
                        this.zoomLevel = 1.0;
                        this.panX = 0;
                        this.panY = 0;
                    } else {
                        this.zoomLevel = 1.8;
                    }
                },

                updatePageIndicator() {
                    const cur = this.pageFlip ? this.pageFlip.getCurrentPageIndex() : this.currentPage;
                    this.currentPage = cur;
                    if (cur === 0) {
                        this.pageIndicator = `Cover (1 / ${this.totalPages})`;
                    } else if (cur >= this.totalPages - 1) {
                        this.pageIndicator = `Back Cover (${this.totalPages} / ${this.totalPages})`;
                    } else {
                        const left = cur + 1;
                        const right = Math.min(cur + 2, this.totalPages);
                        this.pageIndicator = this.isMobile 
                            ? `${left}–${right} / ${this.totalPages}` 
                            : `Pages ${left}–${right} of ${this.totalPages}`;
                    }
                },

                prevPage() {
                    if (this.isFlipping) return;
                    if (this.pageFlip && this.hasPrev) {
                        if (this.zoomLevel > 1.0) {
                            this.zoomLevel = 1.0;
                            this.panX = 0;
                            this.panY = 0;
                        }
                        this.isFlipping = true;
                        try {
                            this.pageFlip.flipPrev();
                        } catch (e) {
                            this.pageFlip.turnToPrevPage();
                        }
                        setTimeout(() => { this.isFlipping = false; }, 550);
                    }
                },

                nextPage() {
                    if (this.isFlipping) return;
                    if (this.pageFlip && this.hasNext) {
                        if (this.zoomLevel > 1.0) {
                            this.zoomLevel = 1.0;
                            this.panX = 0;
                            this.panY = 0;
                        }
                        this.isFlipping = true;
                        try {
                            this.pageFlip.flipNext();
                        } catch (e) {
                            this.pageFlip.turnToNextPage();
                        }
                        setTimeout(() => { this.isFlipping = false; }, 550);
                    }
                },

                zoomIn() {
                    if (this.zoomLevel < 2.5) {
                        this.zoomLevel = +(this.zoomLevel + 0.2).toFixed(1);
                    }
                },

                zoomOut() {
                    if (this.zoomLevel > 1.0) {
                        this.zoomLevel = +(this.zoomLevel - 0.2).toFixed(1);
                        if (this.zoomLevel <= 1.0) {
                            this.zoomLevel = 1.0;
                            this.panX = 0;
                            this.panY = 0;
                        }
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
