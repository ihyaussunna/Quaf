@extends('layouts.media')

@section('title', 'Photopea Illustrator & PSD Editor | QUAF Media Wing')

@section('content')
<div class="space-y-4" x-data="photopeaStandalone()">
    <!-- Top Action Toolbar -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full font-mono text-[10px] font-bold bg-[#be1e2d] text-white">
                    ADOBE ILLUSTRATOR & PHOTOSHOP ENGINE
                </span>
                <span class="px-2 py-0.5 rounded-full font-mono text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                    Supports .AI &bull; .PSD &bull; .SVG &bull; .PDF
                </span>
            </div>
            <h1 class="text-lg font-black text-slate-900 tracking-tight font-sora mt-1">
                Photopea Professional Graphic Suite — QUAF 2026
            </h1>
            <p class="text-xs text-slate-500">
                Direct vector & raster editing in your browser with full layer control, typography, and export capabilities.
            </p>
        </div>

        <!-- Quick Template Launchers -->
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="loadTemplate('{{ asset('images/result-templates/quaf_template_yellow.png') }}')"
                    class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Load Yellow Modern</span>
            </button>

            <button type="button" @click="loadTemplate('{{ asset('images/result-templates/1778705272_srt1-01.png') }}')"
                    class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-xs">
                <span>Load Classic Red</span>
            </button>

            <button type="button" @click="openLocalAi()"
                    class="px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                <span>Open .AI / .PSD File</span>
            </button>

            <button type="button" @click="toggleFullscreen()"
                    class="p-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 transition" title="Toggle Fullscreen">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
            </button>
        </div>
    </div>

    <!-- Hidden file input for opening local .AI, .PSD, .SVG -->
    <input type="file" id="localFileInput" accept=".ai,.psd,.svg,.pdf,.png,.jpg" class="hidden" @change="handleLocalFile($event)">

    <!-- Embedded Photopea Full-Window Iframe -->
    <div id="photopeaWrapper" class="relative w-full rounded-2xl overflow-hidden border border-slate-800 bg-[#1e1e1e] shadow-xl" style="height: calc(100vh - 210px); min-height: 650px;">
        <iframe id="photopeaFrame" 
                class="w-full h-full border-0 block" 
                allow="clipboard-read; clipboard-write; fullscreen"
                loading="eager"></iframe>
    </div>
</div>

<script>
function photopeaStandalone() {
    return {
        iframe: null,
        fonts: [
            "{{ asset('fonts/ROCK.TTF') }}",
            "{{ asset('fonts/monbaiti.ttf') }}",
            "{{ asset('fonts/Rockwell-Light.woff2') }}",
            "{{ asset('fonts/MLU-Revathi.ttf') }}",
            "{{ asset('fonts/MLU-Revathi-Bold.ttf') }}",
            "{{ asset('fonts/AnekMalayalam-Bold.ttf') }}",
            "{{ asset('fonts/Sora-Bold.ttf') }}"
        ],

        init() {
            this.iframe = document.getElementById('photopeaFrame');
            this.buildPhotopeaUrl();
        },

        buildPhotopeaUrl(initialFileUrl = null) {
            const config = {
                environment: {
                    theme: 2, // Dark Theme
                    lang: "en",
                    vmode: 0
                },
                resources: this.fonts
            };

            if (initialFileUrl) {
                config.files = [initialFileUrl];
            } else {
                config.files = ["{{ asset('images/result-templates/quaf_template_yellow.png') }}"];
            }

            const hash = encodeURIComponent(JSON.stringify(config));
            this.iframe.src = 'https://www.photopea.com#' + hash;
        },

        loadTemplate(templateUrl) {
            // PostMessage to Photopea to open the new file URL
            const script = `app.open("${templateUrl}", null, true);`;
            this.iframe.contentWindow.postMessage(script, "https://www.photopea.com");
        },

        openLocalAi() {
            document.getElementById('localFileInput').click();
        },

        handleLocalFile(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                const arrayBuffer = loadEvent.target.result;
                // Photopea accepts ArrayBuffer directly via postMessage to open!
                this.iframe.contentWindow.postMessage(arrayBuffer, "https://www.photopea.com");
            };
            reader.readAsArrayBuffer(file);
        },

        toggleFullscreen() {
            const wrapper = document.getElementById('photopeaWrapper');
            if (!document.fullscreenElement) {
                wrapper.requestFullscreen().catch(err => {
                    alert(`Fullscreen error: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        }
    }
}
</script>
@endsection
