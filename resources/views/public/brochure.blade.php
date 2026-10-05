@extends('layouts.public', ['title' => 'QUAF 9.0 Official Brochure — Digital Book Experience'])

@section('content')

<!-- Digital Book Reader Environment (Section 27 & 28) -->
<section id="brochure-container" class="min-h-[calc(100vh-80px)] bg-slate-950 text-white relative flex flex-col justify-between overflow-hidden select-none"
         x-data="digitalBookViewer({
             pdfUrl: '{{ $brochure['pdf_url'] }}',
             totalPages: {{ $brochure['total_pages'] }}
         })"
         x-init="initViewer()"
         @mousemove="handleActivity()"
         @keydown.window.left="prevPage()"
         @keydown.window.right="nextPage()">

    <!-- Top Header Bar -->
    <div class="px-4 sm:px-8 py-3 sm:py-4 flex items-center justify-between z-30 transition-opacity duration-300"
         :class="controlsVisible ? 'opacity-100' : 'opacity-20 hover:opacity-100'">
        <div class="flex items-center gap-3">
            <a href="{{ route('home.view') }}" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition-colors" title="Back to Home">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <span class="text-[10px] font-mono uppercase tracking-widest text-amber-400 font-bold block">QUAF 9.0 OFFICIAL PUBLICATION</span>
                <h1 class="text-sm sm:text-base font-sora font-black tracking-tight text-white">Digital Brochure Experience</h1>
            </div>
        </div>

        <div class="flex items-center gap-2 font-mono text-xs">
            <span class="hidden sm:inline-block px-2.5 py-1 rounded-full bg-white/10 text-slate-300 text-[11px]">
                Edition: Season 09
            </span>
            <a href="{{ $brochure['handbook_url'] }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-slate-200 text-xs transition-colors hidden sm:flex items-center gap-1.5">
                <span>Handbook (51p)</span>
                <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </a>
            <a href="{{ $brochure['pdf_url'] }}" download="QUAF_9.0_Official_Brochure.pdf" class="px-3.5 py-1.5 rounded-xl bg-[#be1e2d] hover:bg-[#991522] text-white font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Download PDF</span>
            </a>
        </div>
    </div>

    <!-- Center Stage: Digital Two-Page Book Viewer with 3D Page Turn -->
    <div class="flex-1 flex items-center justify-center relative px-2 sm:px-12 py-4 overflow-hidden" id="book-stage">
        
        <!-- Loading Spinner -->
        <div x-show="loading" class="flex flex-col items-center justify-center gap-3 z-20 text-center py-20">
            <div class="w-10 h-10 border-3 border-amber-400 border-t-transparent rounded-full animate-spin"></div>
            <p class="font-mono text-xs text-slate-400">Rendering high-resolution book pages...</p>
        </div>

        <!-- Floating Left Navigation Arrow (Desktop) -->
        <button @click="prevPage()"
                type="button"
                x-show="!loading && hasPrev"
                class="hidden md:flex absolute left-4 lg:left-8 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full glass-panel-dark text-white hover:bg-white/20 items-center justify-center z-30 transition-all shadow-xl hover:scale-110 focus:outline-none"
                :class="controlsVisible ? 'opacity-90' : 'opacity-20 hover:opacity-100'"
                aria-label="Previous Page">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>

        <!-- Book Viewport Container -->
        <div id="book-wrapper" class="relative z-10 max-w-full flex items-center justify-center transition-transform duration-200"
             :style="'transform: scale(' + zoomLevel + ')'">
            <div id="flipbook" class="shadow-2xl shadow-black/80 rounded-md">
                <!-- Pages dynamically rendered into DOM here -->
            </div>
        </div>

        <!-- Floating Right Navigation Arrow (Desktop) -->
        <button @click="nextPage()"
                type="button"
                x-show="!loading && hasNext"
                class="hidden md:flex absolute right-4 lg:right-8 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full glass-panel-dark text-white hover:bg-white/20 items-center justify-center z-30 transition-all shadow-xl hover:scale-110 focus:outline-none"
                :class="controlsVisible ? 'opacity-90' : 'opacity-20 hover:opacity-100'"
                aria-label="Next Page">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
    </div>

    <!-- Floating Bottom Glass Controls Toolbar (Section 28) -->
    <div class="px-4 py-4 sm:py-6 flex justify-center z-30 transition-opacity duration-300 pointer-events-none"
         :class="controlsVisible ? 'opacity-100' : 'opacity-0'">
        
        <div class="glass-panel-dark pointer-events-auto rounded-2xl px-4 sm:px-6 py-2.5 sm:py-3 flex items-center gap-3 sm:gap-6 shadow-2xl border border-white/10 text-xs font-mono">
            
            <!-- Previous Button -->
            <button @click="prevPage()"
                    :disabled="!hasPrev"
                    class="flex items-center gap-1 text-slate-300 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-colors font-semibold">
                <span>‹ Previous</span>
            </button>

            <!-- Page Counter Display -->
            <div class="px-3 py-1 rounded-lg bg-white/10 text-white font-bold tracking-wider text-[11px] sm:text-xs">
                <span x-text="pageIndicator">Pages 1–2 of 14</span>
            </div>

            <!-- Next Button -->
            <button @click="nextPage()"
                    :disabled="!hasNext"
                    class="flex items-center gap-1 text-slate-300 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-colors font-semibold">
                <span>Next ›</span>
            </button>

            <span class="w-px h-4 bg-white/20 hidden sm:inline-block"></span>

            <!-- Zoom Controls -->
            <div class="hidden sm:flex items-center gap-1.5">
                <button @click="zoomOut()" class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center font-bold" title="Zoom Out (−)">
                    −
                </button>
                <span class="text-[11px] text-slate-400 w-10 text-center" x-text="Math.round(zoomLevel * 100) + '%'">100%</span>
                <button @click="zoomIn()" class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center font-bold" title="Zoom In (+)">
                    +
                </button>
            </div>

            <span class="w-px h-4 bg-white/20 hidden sm:inline-block"></span>

            <!-- Fullscreen Button -->
            <button @click="toggleFullscreen()" class="p-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white hidden sm:flex items-center justify-center" title="Toggle Fullscreen">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
            </button>
        </div>
    </div>
</section>

<!-- Scripts: PDF.js and PageFlip -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/page-flip/dist/js/page-flip.browser.js"></script>

<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    function digitalBookViewer(config) {
        return {
            pdfDoc: null,
            pageFlip: null,
            loading: true,
            currentPage: 0,
            totalPages: config.totalPages || 14,
            pageIndicator: 'Loading...',
            zoomLevel: 1.0,
            controlsVisible: true,
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
                }, 3500);
            },

            async initViewer() {
                this.handleActivity();
                const container = document.getElementById('flipbook');

                try {
                    // Load the PDF Document
                    const loadingTask = pdfjsLib.getDocument(config.pdfUrl);
                    this.pdfDoc = await loadingTask.promise;
                    this.totalPages = this.pdfDoc.numPages;

                    // Calculate viewport dimensions
                    const isSmall = window.innerWidth < 768;
                    const stageHeight = Math.min(window.innerHeight - 170, 820);
                    const pageWidth = isSmall ? Math.min(window.innerWidth - 32, 450) : Math.min((window.innerWidth - 120) / 2, 540);
                    const pageHeight = Math.round(pageWidth * 1.414); // Standard A4 / Book proportion

                    // Render pages onto canvases
                    for (let pageNum = 1; pageNum <= this.totalPages; pageNum++) {
                        const page = await this.pdfDoc.getPage(pageNum);
                        const pageDiv = document.createElement('div');
                        pageDiv.className = 'page relative bg-white overflow-hidden shadow-md flex items-center justify-center';
                        pageDiv.setAttribute('data-density', pageNum === 1 || pageNum === this.totalPages ? 'hard' : 'soft');

                        const canvas = document.createElement('canvas');
                        const context = canvas.getContext('2d');

                        // High DPI rendering for sharp text
                        const pixelRatio = window.devicePixelRatio || 2;
                        const viewport = page.getViewport({ scale: (pageWidth / page.getViewport({ scale: 1 }).width) * pixelRatio });

                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        canvas.style.width = '100%';
                        canvas.style.height = '100%';

                        await page.render({
                            canvasContext: context,
                            viewport: viewport
                        }).promise;

                        pageDiv.appendChild(canvas);
                        container.appendChild(pageDiv);
                    }

                    // Initialize StPageFlip
                    const PageFlip = window.St?.PageFlip || window.PageFlip;
                    this.pageFlip = new PageFlip(container, {
                        width: pageWidth,
                        height: pageHeight,
                        size: 'fixed',
                        minWidth: 280,
                        maxWidth: 600,
                        minHeight: 400,
                        maxHeight: 880,
                        maxSpread: isSmall ? 1 : 2,
                        showCover: !isSmall,
                        autoSize: true,
                        drawShadow: true,
                        flippingTime: 700,
                        usePortrait: isSmall,
                        startZIndex: 10
                    });

                    this.pageFlip.loadFromHTML(document.querySelectorAll('.page'));

                    this.pageFlip.on('flip', (e) => {
                        this.currentPage = e.data;
                        this.updatePageIndicator();
                    });

                    this.loading = false;
                    this.updatePageIndicator();

                } catch (error) {
                    console.error('Error rendering brochure PDF:', error);
                    this.loading = false;
                    container.innerHTML = `
                        <div class="p-8 text-center text-slate-300 max-w-md">
                            <p class="text-sm font-semibold mb-3">Unable to render interactive flipbook.</p>
                            <a href="${config.pdfUrl}" download class="inline-block px-5 py-2.5 rounded-xl bg-red-600 text-white font-bold text-xs uppercase">Download PDF Directly</a>
                        </div>
                    `;
                }
            },

            updatePageIndicator() {
                if (this.isMobile) {
                    this.pageIndicator = `Page ${this.currentPage + 1} of ${this.totalPages}`;
                } else {
                    if (this.currentPage === 0) {
                        this.pageIndicator = `Cover Page (1 of ${this.totalPages})`;
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
