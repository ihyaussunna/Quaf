@extends('layouts.public', ['title' => 'QUAF 9.0 Official Theme Note & Brochure — Digital Book Experience'])

@section('content')

<!-- Digital Book Reader Environment -->
<section id="brochure-container" class="min-h-[calc(100vh-80px)] bg-slate-950 text-white relative flex flex-col justify-between overflow-hidden select-none"
         x-data="digitalBookViewer({
             pdfUrl: '{{ $brochure['pdf_url'] }}',
             totalPages: {{ $brochure['total_pages'] }}
         })"
         x-init="initViewer()"
         @mousemove="handleActivity()"
         @touchstart.passive="handleActivity()"
         @keydown.window.left="prevPage()"
         @keydown.window.right="nextPage()">

    <!-- Top Header Bar -->
    <div class="px-4 sm:px-8 py-3 sm:py-4 flex items-center justify-between z-30 transition-opacity duration-300 bg-gradient-to-b from-slate-950/90 to-transparent"
         :class="controlsVisible ? 'opacity-100' : 'opacity-20 hover:opacity-100'">
        <div class="flex items-center gap-3">
            <a href="{{ route('home.view') }}" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition-colors" title="Back to Home">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <span class="text-[10px] font-mono uppercase tracking-widest text-amber-400 font-bold block">QUAF 9.0 OFFICIAL PUBLICATION</span>
                <h1 class="text-sm sm:text-base font-sora font-black tracking-tight text-white flex items-center gap-2">
                    <span>Digital Brochure Experience</span>
                    <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 font-bold border border-amber-500/30">Theme Note</span>
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-2 font-mono text-xs">
            <span class="hidden sm:inline-block px-2.5 py-1 rounded-full bg-white/10 text-slate-300 text-[11px]">
                20 Pages • Theme Note
            </span>
            <a href="{{ $brochure['handbook_url'] }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-slate-200 text-xs transition-colors hidden sm:flex items-center gap-1.5">
                <span>Handbook (51p)</span>
                <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </a>
            <a href="{{ $brochure['pdf_url'] }}" download="QUAF_9.0_Official_Theme_Note.pdf" class="px-3.5 py-1.5 rounded-xl bg-[#be1e2d] hover:bg-[#991522] text-white font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Download PDF</span>
            </a>
        </div>
    </div>

    <!-- Center Stage: Digital Two-Page Book Viewer with 3D Page Turn -->
    <div class="flex-1 flex items-center justify-center relative px-2 sm:px-12 py-2 sm:py-4 overflow-hidden" id="book-stage">
        
        <!-- Loading Spinner & Progress -->
        <div x-show="loading" class="flex flex-col items-center justify-center gap-4 z-20 text-center py-20">
            <div class="w-12 h-12 border-4 border-amber-400 border-t-transparent rounded-full animate-spin"></div>
            <div>
                <p class="font-sora font-bold text-sm text-white" x-text="loadingText">Preparing Theme Note Booklet...</p>
                <p class="font-mono text-xs text-slate-400 mt-1" x-text="loadingSubtext">Rendering 20 high-definition pages</p>
            </div>
            <!-- Progress Bar -->
            <div class="w-48 sm:w-64 h-1.5 bg-white/10 rounded-full overflow-hidden mt-1">
                <div class="h-full bg-gradient-to-r from-amber-400 to-[#be1e2d] transition-all duration-200 rounded-full"
                     :style="'width: ' + progressPercent + '%'"></div>
            </div>
        </div>

        <!-- Floating Left Navigation Arrow -->
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
            <div id="flipbook" class="shadow-2xl shadow-black/90 rounded-md">
                <!-- Pages dynamically rendered into DOM here -->
            </div>
        </div>

        <!-- Floating Right Navigation Arrow -->
        <button @click="nextPage()"
                type="button"
                x-show="!loading && hasNext"
                class="hidden md:flex absolute right-4 lg:right-8 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-slate-900/80 hover:bg-slate-800 text-white border border-white/20 items-center justify-center z-30 transition-all shadow-xl hover:scale-110 focus:outline-none cursor-pointer"
                :class="controlsVisible ? 'opacity-90' : 'opacity-20 hover:opacity-100'"
                aria-label="Next Page">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
    </div>

    <!-- Floating Bottom Glass Controls Toolbar -->
    <div class="px-4 py-3 sm:py-5 flex flex-col items-center gap-2 z-30 transition-opacity duration-300 pointer-events-none"
         :class="controlsVisible ? 'opacity-100' : 'opacity-0'">
        
        <div class="bg-slate-900/90 backdrop-blur-md pointer-events-auto rounded-2xl px-4 sm:px-6 py-2.5 sm:py-3 flex flex-wrap items-center justify-center gap-3 sm:gap-5 shadow-2xl border border-white/10 text-xs font-mono">
            
            <!-- Previous Button -->
            <button @click="prevPage()"
                    :disabled="!hasPrev"
                    class="flex items-center gap-1.5 text-slate-300 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-colors font-semibold cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                <span class="hidden sm:inline">Previous</span>
            </button>

            <!-- Page Counter Display -->
            <div class="px-3.5 py-1 rounded-xl bg-white/10 text-white font-bold tracking-wider text-[11px] sm:text-xs">
                <span x-text="pageIndicator">Loading...</span>
            </div>

            <!-- Next Button -->
            <button @click="nextPage()"
                    :disabled="!hasNext"
                    class="flex items-center gap-1.5 text-slate-300 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-colors font-semibold cursor-pointer">
                <span class="hidden sm:inline">Next</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>

            <span class="w-px h-4 bg-white/20 hidden sm:inline-block"></span>

            <!-- Sound Toggle -->
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
    </div>
</section>

<!-- Scripts: PDF.js and StPageFlip -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/page-flip/dist/js/page-flip.browser.js"></script>

<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    function digitalBookViewer(config) {
        return {
            pdfDoc: null,
            pageFlip: null,
            loading: true,
            loadingText: 'Loading Theme Note Booklet...',
            loadingSubtext: 'Fetching 20 pages...',
            progressPercent: 5,
            currentPage: 0,
            totalPages: config.totalPages || 20,
            pageIndicator: 'Loading...',
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

            async initViewer() {
                this.handleActivity();
                const container = document.getElementById('flipbook');

                try {
                    this.loadingText = 'Downloading Theme Note PDF...';
                    const loadingTask = pdfjsLib.getDocument(config.pdfUrl);
                    this.pdfDoc = await loadingTask.promise;
                    this.totalPages = this.pdfDoc.numPages;
                    this.progressPercent = 15;

                    // Inspect first page for native aspect ratio
                    const page1 = await this.pdfDoc.getPage(1);
                    const rawVp = page1.getViewport({ scale: 1 });
                    const pageRatio = rawVp.height / rawVp.width; // 1.0 for square theme note booklet

                    // Calculate viewport dimensions
                    const isSmall = window.innerWidth < 768;
                    const maxStageHeight = Math.max(340, window.innerHeight - (isSmall ? 180 : 160));
                    const maxStageWidth = isSmall
                        ? Math.min(window.innerWidth - 24, 480)
                        : Math.min((window.innerWidth - 80) / 2, 540);

                    let pageWidth = maxStageWidth;
                    let pageHeight = Math.round(pageWidth * pageRatio);

                    if (pageHeight > maxStageHeight) {
                        pageHeight = maxStageHeight;
                        pageWidth = Math.round(pageHeight / pageRatio);
                    }

                    // Temporary container to assemble page DOM elements
                    const pagesWrapper = document.createElement('div');

                    // Render pages as high-DPI image canvases
                    for (let pageNum = 1; pageNum <= this.totalPages; pageNum++) {
                        this.loadingText = `Rendering page ${pageNum} of ${this.totalPages}...`;
                        this.progressPercent = Math.round(15 + ((pageNum / this.totalPages) * 80));

                        const page = await this.pdfDoc.getPage(pageNum);
                        const pixelRatio = Math.min(window.devicePixelRatio || 1.5, 2.5);
                        const viewport = page.getViewport({ scale: (pageWidth / rawVp.width) * pixelRatio });

                        const canvas = document.createElement('canvas');
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        const ctx = canvas.getContext('2d');

                        await page.render({
                            canvasContext: ctx,
                            viewport: viewport
                        }).promise;

                        const dataUrl = canvas.toDataURL('image/jpeg', 0.92);

                        const pageDiv = document.createElement('div');
                        pageDiv.className = 'page bg-white overflow-hidden shadow-md flex items-center justify-center';
                        pageDiv.setAttribute('data-density', (pageNum === 1 || pageNum === this.totalPages) ? 'hard' : 'soft');

                        const img = document.createElement('img');
                        img.src = dataUrl;
                        img.style.width = '100%';
                        img.style.height = '100%';
                        img.style.objectFit = 'contain';
                        img.draggable = false;

                        pageDiv.appendChild(img);
                        pagesWrapper.appendChild(pageDiv);
                    }

                    // Clear container and append pages
                    container.innerHTML = '';
                    const pageNodes = Array.from(pagesWrapper.children);

                    // Initialize St.PageFlip
                    const PageFlipClass = window.St?.PageFlip || window.PageFlip;
                    this.pageFlip = new PageFlipClass(container, {
                        width: pageWidth,
                        height: pageHeight,
                        size: 'fixed',
                        minWidth: 200,
                        maxWidth: 900,
                        minHeight: 200,
                        maxHeight: 900,
                        drawShadow: true,
                        maxShadowOpacity: 0.5,
                        showCover: !isSmall,
                        usePortrait: isSmall,
                        startPage: 0,
                        flippingTime: 650,
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
                    this.progressPercent = 100;
                    this.updatePageIndicator();

                } catch (error) {
                    console.error('Error rendering Theme Note PDF:', error);
                    this.loading = false;
                    container.innerHTML = `
                        <div class="p-8 text-center text-slate-300 max-w-md bg-white/5 rounded-2xl border border-white/10">
                            <p class="text-sm font-semibold mb-3">Interactive Flipbook Reader</p>
                            <p class="text-xs text-slate-400 mb-4">You can download and read the complete 20-page Theme Note PDF directly.</p>
                            <a href="${config.pdfUrl}" download class="inline-block px-5 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs uppercase tracking-wider">Download PDF Directly</a>
                        </div>
                    `;
                }
            },

            updatePageIndicator() {
                if (this.isMobile) {
                    this.pageIndicator = `Page ${this.currentPage + 1} of ${this.totalPages}`;
                } else {
                    if (this.currentPage === 0) {
                        this.pageIndicator = `Front Cover (1 of ${this.totalPages})`;
                    } else if (this.currentPage >= this.totalPages - 1) {
                        this.pageIndicator = `Back Cover (${this.totalPages} of ${this.totalPages})`;
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
                const elem = document.getElementById('brochure-container');
                if (!document.fullscreenElement) {
                    elem.requestFullscreen?.().catch(err => console.log(err));
                } else {
                    document.exitFullscreen?.();
                }
            }
        };
    }
</script>

<style>
    /* 3D Page Curl & Spine Depth */
    .page {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
    }
    .page.--left {
        border-right: 1px solid rgba(0, 0, 0, 0.1);
        background: linear-gradient(to right, #fff 0%, #fdfdfd 95%, #ececec 100%);
    }
    .page.--right {
        border-left: 1px solid rgba(0, 0, 0, 0.1);
        background: linear-gradient(to left, #fff 0%, #fdfdfd 95%, #ececec 100%);
    }
</style>

@endsection
