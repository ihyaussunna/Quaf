@extends('layouts.public')

@section('content')
<div class="py-8 sm:py-14 bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Title & Hero Section with Animation -->
        <div class="text-center max-w-3xl mx-auto mb-8 sm:mb-12">
            <!-- Festive Badge -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-red-50 border border-red-200/80 shadow-xs mb-4">
                <span class="w-2 h-2 rounded-full bg-[#be1e2d] animate-ping"></span>
                <span class="text-[11px] sm:text-xs font-semibold tracking-wider uppercase text-[#be1e2d] font-mono">ĀDABĪC INHERITANCE • OFFICIAL DP MAKER</span>
            </div>

            <!-- Animated Main Title -->
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold tracking-tight font-sora leading-none mb-3">
                <span class="block text-slate-900 tracking-tight">BE QUAF,</span>
                <span class="block bg-gradient-to-r from-[#be1e2d] via-[#f58220] to-[#005c94] bg-clip-text text-transparent dp-title-animated font-black tracking-tight mt-1">YOUR DP</span>
            </h1>

            <!-- Descriptive English Text -->
            <p class="text-sm sm:text-base text-slate-600 max-w-2xl mx-auto leading-relaxed mt-4 font-normal">
                Be part of the grand celebration of QUAF 9.0! Upload your photo, adjust it inside the official Ādabīc Inheritance frame, download your high-resolution DP, and share directly to WhatsApp.
            </p>
        </div>

        <!-- Studio Container -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left Column: Canvas Preview Area (lg:col-span-7) -->
            <div class="lg:col-span-7 flex flex-col items-center">
                <div class="w-full max-w-[480px] bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/90 shadow-xl shadow-slate-200/50">
                    
                    <!-- Canvas Box Container -->
                    <div class="relative w-full aspect-square rounded-2xl overflow-hidden bg-slate-100 ring-1 ring-slate-900/5 shadow-inner select-none" id="canvasWrapper">
                        <canvas id="dpCanvas" width="1024" height="1024" class="w-full h-full object-contain cursor-grab active:cursor-grabbing touch-none block"></canvas>

                        <!-- Empty State Overlay Cue -->
                        <div id="emptyCue" class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center pointer-events-none transition-opacity duration-300">
                            <div class="w-16 h-16 rounded-full bg-red-100/80 text-[#be1e2d] flex items-center justify-center mb-3 shadow-inner">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="text-base font-semibold text-slate-800 font-revathi">ക്ലിക്ക് ചെയ്ത് ഫോട്ടോ തിരഞ്ഞെടുക്കൂ</span>
                            <span class="text-xs text-slate-500 mt-0.5">Click or drag image to upload</span>
                        </div>

                        <!-- Top Right Badge -->
                        <div class="absolute top-3 right-3 pointer-events-none">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold bg-white/90 backdrop-blur-md text-slate-700 border border-slate-200/80 shadow-xs">1024 × 1024 HD</span>
                        </div>

                        <!-- Gesture Hint (Visible only when photo is uploaded) -->
                        <div id="gestureHint" class="hidden absolute bottom-3 inset-x-3 pointer-events-none text-center">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-slate-900/75 backdrop-blur-md text-white shadow-xs font-revathi">
                                ഡ്രാഗ് ചെയ്ത് ഫോട്ടോ നീക്കുക • Pinch / Scroll ചെയ്യുക
                            </span>
                        </div>
                    </div>

                    <!-- Hidden File Input -->
                    <input type="file" id="photoInput" accept="image/*" class="sr-only">

                    <!-- Mobile / Quick Upload Action Under Canvas -->
                    <div class="mt-4 flex items-center justify-between gap-3">
                        <button type="button" id="btnUploadTrigger" class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 flex items-center justify-center gap-2 transition cursor-pointer">
                            <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <span id="uploadBtnText"><span class="font-revathi text-sm">ഫോട്ടോ തിരഞ്ഞെടുക്കുക</span> <span class="font-sans text-xs text-slate-500">(Choose Photo)</span></span>
                        </button>
                        <button type="button" id="btnResetPhoto" title="റീസെറ്റ് ചെയ്യുക" class="p-2.5 rounded-xl text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                    </div>

                </div>
            </div>

            <!-- Right Column: Adjustments & Actions (lg:col-span-5) -->
            <div class="lg:col-span-5 space-y-5">

                <!-- Control Panel Card -->
                <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/90 shadow-lg shadow-slate-200/40">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2 mb-4 pb-2 border-b border-slate-100">
                        <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        <span><span class="font-revathi text-base">ഫോട്ടോ ക്രമീകരിക്കുക</span> <span class="font-sans text-xs text-slate-500 font-normal">(Adjust Photo)</span></span>
                    </h3>

                    <!-- Zoom Slider -->
                    <div class="mb-5">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-1.5">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                </svg>
                                <span><span class="font-revathi text-sm">സൂം / വലുപ്പം</span> <span class="font-sans text-xs text-slate-400 font-normal">(Zoom)</span></span>
                            </span>
                            <span id="zoomValText" class="font-mono text-slate-500">100%</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" id="btnZoomOut" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-base transition cursor-pointer select-none">−</button>
                            <input type="range" id="zoomSlider" min="0.2" max="3.0" step="0.01" value="1.0" class="flex-1 h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#be1e2d]">
                            <button type="button" id="btnZoomIn" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-base transition cursor-pointer select-none">+</button>
                        </div>
                    </div>

                    <!-- Quick Tools: Rotate & Align -->
                    <div class="grid grid-cols-2 gap-2.5 mb-5">
                        <button type="button" id="btnRotate" class="py-2.5 px-3 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200/80 flex items-center justify-center gap-1.5 transition cursor-pointer">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Rotate 90°</span>
                        </button>
                        <button type="button" id="btnCenterAlign" class="py-2.5 px-3 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200/80 flex items-center justify-center gap-1.5 transition cursor-pointer">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span>Center Photo</span>
                        </button>
                    </div>

                    <!-- Fine-tune Nudge Arrows -->
                    <div class="pt-2 border-t border-slate-100">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-2">
                            <span class="font-revathi text-xs">സ്ഥാനം മാറ്റാൻ</span> <span class="font-sans text-[10px] text-slate-400 font-normal">(Nudge Position):</span>
                        </div>
                        <div class="flex items-center justify-center gap-1.5">
                            <button type="button" id="btnNudgeLeft" class="w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition cursor-pointer" title="Left">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <div class="flex flex-col gap-1.5">
                                <button type="button" id="btnNudgeUp" class="w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition cursor-pointer" title="Up">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <button type="button" id="btnNudgeDown" class="w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition cursor-pointer" title="Down">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>
                            <button type="button" id="btnNudgeRight" class="w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition cursor-pointer" title="Right">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Primary Action Cards: Download & Share -->
                <div class="space-y-3">
                    
                    <!-- Download High-Res DP Button -->
                    <button type="button" id="btnDownloadDP" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-[#be1e2d] via-red-600 to-[#991522] text-white shadow-xl shadow-red-900/25 hover:brightness-110 active:scale-[0.99] transition-all cursor-pointer flex items-center justify-between group">
                        <div class="flex items-center gap-3 text-left">
                            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white shrink-0">
                                <svg class="w-5 h-5 group-hover:translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold uppercase tracking-wider">Download High-Res DP</div>
                                <div class="text-[11px] text-white/80 font-normal"><span class="font-revathi text-xs">ഡി.പി ഡൗൺലോഡ് ചെയ്യുക</span> <span class="font-sans">(1024×1024)</span></div>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-white/80 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <!-- Share to WhatsApp Button -->
                    <button type="button" id="btnShareWhatsApp" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-[#25D366] to-[#128C7E] text-white shadow-xl shadow-emerald-900/25 hover:brightness-110 active:scale-[0.99] transition-all cursor-pointer flex items-center justify-between group">
                        <div class="flex items-center gap-3 text-left">
                            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white shrink-0">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.983.565 1.77.87 2.796.87 3.183 0 5.769-2.587 5.77-5.767.001-3.181-2.585-5.756-5.77-5.756zm0-1.872c4.214 0 7.636 3.421 7.636 7.637 0 4.215-3.422 7.637-7.636 7.637-1.328 0-2.576-.345-3.665-.951l-4.366 1.144 1.164-4.254c-.689-1.142-1.07-2.477-1.07-3.876 0-4.216 3.422-7.637 7.637-7.637zm3.873 9.404c-.161-.08-.953-.47-1.101-.524-.148-.053-.255-.08-.363.08-.107.161-.418.524-.512.632-.094.107-.188.12-.349.04-.161-.08-.68-.25-1.295-.799-.479-.427-.802-.955-.896-1.116-.094-.161-.01-.248.07-.328.073-.072.161-.188.242-.282.08-.094.107-.161.161-.269.054-.107.027-.201-.013-.282-.04-.08-.363-.873-.497-1.196-.131-.314-.264-.271-.363-.276l-.309-.005c-.107 0-.282.04-.43.201-.148.161-.564.551-.564 1.344s.578 1.56 1.383 2.628c.805 1.069 1.838 1.487 2.923 1.802.946.275 1.543.226 2.003.157.514-.077 1.579-.645 1.801-1.269.222-.624.222-1.159.155-1.269-.067-.11-.242-.175-.403-.255z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold uppercase tracking-wider">Share to WhatsApp</div>
                                <div class="text-[11px] text-white/80 font-normal font-revathi text-xs">വാട്സാപ്പിൽ പങ്കുവെക്കുക</div>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-white/80 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                </div>

                <!-- Simple Step Instructions (with MLU-Revathi font) -->
                <div class="bg-white/80 rounded-2xl p-4 border border-slate-200/70 text-xs text-slate-600 space-y-2.5">
                    <div class="font-bold text-slate-800 text-[11px] uppercase tracking-wider flex items-center gap-1">
                        <span class="font-revathi-bold text-xs">ഉപയോഗിക്കേണ്ട വിധം</span>
                        <span class="font-sans text-[10px] text-slate-500 font-medium">(How to Use):</span>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-4 h-4 rounded-full bg-red-100 text-[#be1e2d] font-bold text-[10px] flex items-center justify-center shrink-0 font-sans mt-0.5">1</span>
                        <div class="text-xs text-slate-700 leading-snug">
                            <span class="font-revathi text-sm">നിങ്ങളുടെ ഫോട്ടോ തിരഞ്ഞെടുക്കുക</span>
                            <span class="font-sans text-[11px] text-slate-500"> (Choose Photo).</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-4 h-4 rounded-full bg-red-100 text-[#be1e2d] font-bold text-[10px] flex items-center justify-center shrink-0 font-sans mt-0.5">2</span>
                        <div class="text-xs text-slate-700 leading-snug">
                            <span class="font-revathi text-sm">ഫ്രെയിമിൽ പാകമാകുന്ന വിധം ഡ്രാഗ് ചെയ്തോ സൂം ചെയ്തോ ക്രമീകരിക്കുക</span>
                            <span class="font-sans text-[11px] text-slate-500"> (Drag & Zoom).</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-4 h-4 rounded-full bg-red-100 text-[#be1e2d] font-bold text-[10px] flex items-center justify-center shrink-0 font-sans mt-0.5">3</span>
                        <div class="text-xs text-slate-700 leading-snug">
                            <span class="font-sans text-[11px] font-semibold">Download</span>
                            <span class="font-revathi text-sm">ചെയ്ത്</span>
                            <span class="font-sans text-[11px] font-semibold">WhatsApp</span><span class="font-revathi text-sm">-ൽ സ്റ്റാറ്റസ് അല്ലെങ്കിൽ പ്രൊഫൈൽ ഫോട്ടോയാക്കുക!</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

<style>
@keyframes dpTitleFlow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
.dp-title-animated {
    background-size: 200% auto;
    animation: dpTitleFlow 4s ease infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('dpCanvas');
    const ctx = canvas.getContext('2d');
    const photoInput = document.getElementById('photoInput');
    const btnUploadTrigger = document.getElementById('btnUploadTrigger');
    const uploadBtnText = document.getElementById('uploadBtnText');
    const canvasWrapper = document.getElementById('canvasWrapper');
    const emptyCue = document.getElementById('emptyCue');
    const gestureHint = document.getElementById('gestureHint');
    const zoomSlider = document.getElementById('zoomSlider');
    const zoomValText = document.getElementById('zoomValText');
    const btnZoomIn = document.getElementById('btnZoomIn');
    const btnZoomOut = document.getElementById('btnZoomOut');
    const btnRotate = document.getElementById('btnRotate');
    const btnCenterAlign = document.getElementById('btnCenterAlign');
    const btnResetPhoto = document.getElementById('btnResetPhoto');
    const btnDownloadDP = document.getElementById('btnDownloadDP');
    const btnShareWhatsApp = document.getElementById('btnShareWhatsApp');
    const btnNudgeLeft = document.getElementById('btnNudgeLeft');
    const btnNudgeRight = document.getElementById('btnNudgeRight');
    const btnNudgeUp = document.getElementById('btnNudgeUp');
    const btnNudgeDown = document.getElementById('btnNudgeDown');

    // Official Frame Specs
    const FRAME_SRC = '{{ $frameConfig['frame_image'] }}';
    const CW = 1024;
    const CH = 1024;
    const CIRCLE_X = {{ $frameConfig['circle_center_x'] }}; // 512
    const CIRCLE_Y = {{ $frameConfig['circle_center_y'] }}; // 450
    const CIRCLE_RADIUS = {{ $frameConfig['circle_radius'] }}; // 310

    let frameImage = new Image();
    frameImage.crossOrigin = 'anonymous';
    frameImage.src = FRAME_SRC;

    let userImage = null;
    let hasPhoto = false;

    // Transform State
    let state = {
        baseScale: 1,
        scale: 1,
        offsetX: 0,
        offsetY: 0,
        rotation: 0 // In degrees: 0, 90, 180, 270
    };

    // Interaction State
    let isDragging = false;
    let dragStartX = 0;
    let dragStartY = 0;
    let dragStartOffsetX = 0;
    let dragStartOffsetY = 0;

    // Pinch Zoom State
    let initialPinchDistance = 0;
    let initialPinchScale = 1;

    frameImage.onload = function () {
        render();
    };

    function render() {
        ctx.clearRect(0, 0, CW, CH);

        if (userImage && hasPhoto) {
            ctx.save();
            // 1. Clip strictly to the circular frame window
            ctx.beginPath();
            ctx.arc(CIRCLE_X, CIRCLE_Y, CIRCLE_RADIUS + 1.5, 0, Math.PI * 2);
            ctx.closePath();
            ctx.clip();

            // 2. Transform coordinates to circle center
            ctx.translate(CIRCLE_X + state.offsetX, CIRCLE_Y + state.offsetY);
            ctx.rotate((state.rotation * Math.PI) / 180);
            ctx.scale(state.scale, state.scale);

            // 3. Draw user image high-quality centered
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.drawImage(
                userImage,
                -userImage.width / 2,
                -userImage.height / 2,
                userImage.width,
                userImage.height
            );
            ctx.restore();
        } else {
            // Draw clean placeholder inside circle
            ctx.save();
            ctx.beginPath();
            ctx.arc(CIRCLE_X, CIRCLE_Y, CIRCLE_RADIUS, 0, Math.PI * 2);
            ctx.fillStyle = '#f8fafc';
            ctx.fill();

            // Subtle silhouette
            ctx.fillStyle = '#e2e8f0';
            ctx.beginPath();
            ctx.arc(CIRCLE_X, CIRCLE_Y - 30, 85, 0, Math.PI * 2);
            ctx.fill();
            ctx.beginPath();
            ctx.arc(CIRCLE_X, CIRCLE_Y + 140, 140, Math.PI, 0, false);
            ctx.fill();
            ctx.restore();
        }

        // 4. Draw Official Frame PNG over top
        if (frameImage.complete && frameImage.naturalWidth > 0) {
            ctx.drawImage(frameImage, 0, 0, CW, CH);
        }
    }

    // Photo Loading Handler
    function handleFile(file) {
        if (!file || !file.type.startsWith('image/')) {
            alert('ദയവായി ഒരു ഇമേജ് ഫയൽ തിരഞ്ഞെടുക്കുക.');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const img = new Image();
            img.onload = function () {
                userImage = img;
                hasPhoto = true;

                // Calculate base scale to fill circular cutout
                const circleDiameter = CIRCLE_RADIUS * 2;
                const minSide = Math.min(img.width, img.height);
                state.baseScale = circleDiameter / minSide;
                state.scale = state.baseScale;
                state.offsetX = 0;
                state.offsetY = 0;
                state.rotation = 0;

                // Normalize slider
                zoomSlider.min = (state.baseScale * 0.4).toFixed(4);
                zoomSlider.max = (state.baseScale * 3.5).toFixed(4);
                zoomSlider.value = state.scale.toFixed(4);
                updateZoomText();

                emptyCue.classList.add('opacity-0', 'pointer-events-none');
                gestureHint.classList.remove('hidden');
                uploadBtnText.innerHTML = '<span class="font-revathi text-sm">മറ്റൊരു ഫോട്ടോ മാറ്റുക</span> <span class="font-sans text-xs text-slate-500">(Change Photo)</span>';

                render();
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    function updateZoomText() {
        const percent = Math.round((state.scale / state.baseScale) * 100);
        zoomValText.textContent = percent + '%';
    }

    // File Input Triggers
    btnUploadTrigger.addEventListener('click', () => photoInput.click());
    canvasWrapper.addEventListener('click', (e) => {
        if (!hasPhoto) {
            photoInput.click();
        }
    });

    photoInput.addEventListener('change', function (e) {
        if (e.target.files && e.target.files[0]) {
            handleFile(e.target.files[0]);
        }
    });

    // Drag and Drop files onto canvas
    canvasWrapper.addEventListener('dragover', (e) => {
        e.preventDefault();
        canvasWrapper.classList.add('ring-2', 'ring-[#be1e2d]');
    });
    canvasWrapper.addEventListener('dragleave', () => {
        canvasWrapper.classList.remove('ring-2', 'ring-[#be1e2d]');
    });
    canvasWrapper.addEventListener('drop', (e) => {
        e.preventDefault();
        canvasWrapper.classList.remove('ring-2', 'ring-[#be1e2d]');
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            handleFile(e.dataTransfer.files[0]);
        }
    });

    // Zoom Controls
    zoomSlider.addEventListener('input', function () {
        state.scale = parseFloat(this.value);
        updateZoomText();
        render();
    });

    btnZoomIn.addEventListener('click', function () {
        if (!hasPhoto) return;
        const maxVal = parseFloat(zoomSlider.max);
        state.scale = Math.min(maxVal, state.scale * 1.15);
        zoomSlider.value = state.scale;
        updateZoomText();
        render();
    });

    btnZoomOut.addEventListener('click', function () {
        if (!hasPhoto) return;
        const minVal = parseFloat(zoomSlider.min);
        state.scale = Math.max(minVal, state.scale / 1.15);
        zoomSlider.value = state.scale;
        updateZoomText();
        render();
    });

    // Rotate 90°
    btnRotate.addEventListener('click', function () {
        if (!hasPhoto) return;
        state.rotation = (state.rotation + 90) % 360;
        render();
    });

    // Center Align
    btnCenterAlign.addEventListener('click', function () {
        if (!hasPhoto) return;
        state.offsetX = 0;
        state.offsetY = 0;
        render();
    });

    // Reset All
    btnResetPhoto.addEventListener('click', function () {
        if (!hasPhoto) return;
        state.scale = state.baseScale;
        state.offsetX = 0;
        state.offsetY = 0;
        state.rotation = 0;
        zoomSlider.value = state.scale;
        updateZoomText();
        render();
    });

    // Nudge Controls (15px each step)
    const NUDGE_STEP = 15;
    btnNudgeLeft.addEventListener('click', () => { if (hasPhoto) { state.offsetX -= NUDGE_STEP; render(); } });
    btnNudgeRight.addEventListener('click', () => { if (hasPhoto) { state.offsetX += NUDGE_STEP; render(); } });
    btnNudgeUp.addEventListener('click', () => { if (hasPhoto) { state.offsetY -= NUDGE_STEP; render(); } });
    btnNudgeDown.addEventListener('click', () => { if (hasPhoto) { state.offsetY += NUDGE_STEP; render(); } });

    // Touch & Mouse Drag Handling
    function getCanvasCoords(clientX, clientY) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: ((clientX - rect.left) / rect.width) * CW,
            y: ((clientY - rect.top) / rect.height) * CH
        };
    }

    // Mouse Events
    canvas.addEventListener('mousedown', function (e) {
        if (!hasPhoto) return;
        isDragging = true;
        dragStartX = e.clientX;
        dragStartY = e.clientY;
        dragStartOffsetX = state.offsetX;
        dragStartOffsetY = state.offsetY;
    });

    window.addEventListener('mousemove', function (e) {
        if (!isDragging || !hasPhoto) return;
        const rect = canvas.getBoundingClientRect();
        const factor = CW / rect.width;
        state.offsetX = dragStartOffsetX + (e.clientX - dragStartX) * factor;
        state.offsetY = dragStartOffsetY + (e.clientY - dragStartY) * factor;
        render();
    });

    window.addEventListener('mouseup', function () {
        isDragging = false;
    });

    // Mouse Wheel Zoom
    canvas.addEventListener('wheel', function (e) {
        if (!hasPhoto) return;
        e.preventDefault();
        const delta = e.deltaY < 0 ? 1.08 : 0.92;
        const minVal = parseFloat(zoomSlider.min);
        const maxVal = parseFloat(zoomSlider.max);
        state.scale = Math.min(maxVal, Math.max(minVal, state.scale * delta));
        zoomSlider.value = state.scale;
        updateZoomText();
        render();
    }, { passive: false });

    // Touch Events (Single finger Pan & Two finger Pinch)
    function getDistance(t1, t2) {
        const dx = t1.clientX - t2.clientX;
        const dy = t1.clientY - t2.clientY;
        return Math.sqrt(dx * dx + dy * dy);
    }

    canvas.addEventListener('touchstart', function (e) {
        if (!hasPhoto) return;
        if (e.touches.length === 1) {
            isDragging = true;
            dragStartX = e.touches[0].clientX;
            dragStartY = e.touches[0].clientY;
            dragStartOffsetX = state.offsetX;
            dragStartOffsetY = state.offsetY;
        } else if (e.touches.length === 2) {
            isDragging = false;
            initialPinchDistance = getDistance(e.touches[0], e.touches[1]);
            initialPinchScale = state.scale;
        }
    }, { passive: true });

    canvas.addEventListener('touchmove', function (e) {
        if (!hasPhoto) return;
        if (e.touches.length === 1 && isDragging) {
            const rect = canvas.getBoundingClientRect();
            const factor = CW / rect.width;
            state.offsetX = dragStartOffsetX + (e.touches[0].clientX - dragStartX) * factor;
            state.offsetY = dragStartOffsetY + (e.touches[0].clientY - dragStartY) * factor;
            render();
        } else if (e.touches.length === 2 && initialPinchDistance > 0) {
            const currentDist = getDistance(e.touches[0], e.touches[1]);
            const ratio = currentDist / initialPinchDistance;
            const minVal = parseFloat(zoomSlider.min);
            const maxVal = parseFloat(zoomSlider.max);
            state.scale = Math.min(maxVal, Math.max(minVal, initialPinchScale * ratio));
            zoomSlider.value = state.scale;
            updateZoomText();
            render();
        }
    }, { passive: true });

    canvas.addEventListener('touchend', function (e) {
        if (e.touches.length === 0) {
            isDragging = false;
            initialPinchDistance = 0;
        } else if (e.touches.length === 1) {
            // Switched from pinch to single finger drag
            isDragging = true;
            dragStartX = e.touches[0].clientX;
            dragStartY = e.touches[0].clientY;
            dragStartOffsetX = state.offsetX;
            dragStartOffsetY = state.offsetY;
            initialPinchDistance = 0;
        }
    });

    // Download High-Res DP
    function triggerDownload() {
        if (!hasPhoto) {
            alert('ദയവായി ആദ്യം നിങ്ങളുടെ ഫോട്ടോ അപ്‌ലോഡ് ചെയ്യുക.');
            photoInput.click();
            return;
        }
        render();
        canvas.toBlob(function (blob) {
            if (!blob) return;
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'QUAF-Adabic-DP-2026.png';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            setTimeout(() => URL.revokeObjectURL(url), 1000);
        }, 'image/png', 1.0);
    }

    btnDownloadDP.addEventListener('click', triggerDownload);

    // WhatsApp Share Implementation
    async function shareToWhatsApp() {
        if (!hasPhoto) {
            alert('ദയവായി ആദ്യം നിങ്ങളുടെ ഫോട്ടോ അപ്‌ലോഡ് ചെയ്യുക.');
            photoInput.click();
            return;
        }
        render();

        const shareText = 'Be QUAF! എന്റെ ഒഫീഷ്യൽ ക്വാഫ് 2026 പ്രൊഫൈൽ ഫ്രെയിം റെഡിയായി. നിങ്ങളും സ്വന്തം ഡി.പി ഉണ്ടാക്കൂ: ' + window.location.href;

        canvas.toBlob(async function (blob) {
            if (!blob) return;
            const file = new File([blob], 'QUAF-Adabic-DP.png', { type: 'image/png' });

            // Try Web Share API with file support first (Mobile browsers)
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                try {
                    await navigator.share({
                        title: 'Be QUAF, Your DP',
                        text: shareText,
                        files: [file]
                    });
                    return;
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        fallbackShare(blob, shareText);
                    }
                }
            } else {
                fallbackShare(blob, shareText);
            }
        }, 'image/png', 1.0);
    }

    function fallbackShare(blob, text) {
        // Automatically save image to phone/PC so user can attach it
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'QUAF-Adabic-DP-2026.png';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 1000);

        // Open WhatsApp web or app
        const waUrl = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(text);
        window.open(waUrl, '_blank');
    }

    btnShareWhatsApp.addEventListener('click', shareToWhatsApp);
});
</script>
@endsection
