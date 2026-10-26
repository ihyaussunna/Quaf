@extends('layouts.public', [
    'title' => 'Photo Gallery & Moments — QUAF 2026 | Markaz Art Fest',
    'metaDescription' => 'High resolution photos, stage moments, and visual memories from QUAF 2026 Markaz Cultural Festival at Jamia Markaz.',
])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16" 
     x-data="{
         lightboxOpen: false,
         currentIndex: 0,
         toastMessage: '',
         images: [
             @foreach($items as $idx => $item)
                 {
                     id: {{ $item->id }},
                     src: '{{ $item->image_path }}',
                     downloadUrl: '{{ route('gallery.download', $item->id) }}',
                     title: '{{ addslashes($item->title ?: 'QUAF Festival Photo') }}',
                     category: '{{ $item->category }}',
                     group: '{{ $item->group?->name }}'
                 }{{ !$loop->last ? ',' : '' }}
             @endforeach
         ],
         openLightbox(idx) {
             this.currentIndex = idx;
             this.lightboxOpen = true;
         },
         async sharePhoto(url, title = 'QUAF Festival Photo') {
             const fullUrl = url.startsWith('http') ? url : window.location.origin + url;

             if (navigator.share) {
                 try {
                     this.toastMessage = 'Opening photo sharing...';
                     const response = await fetch(fullUrl, { cache: 'no-cache' });
                     if (response.ok) {
                         const blob = await response.blob();
                         const mimeType = blob.type || 'image/jpeg';
                         const ext = mimeType.includes('/') ? mimeType.split('/')[1].replace('jpeg', 'jpg') : 'jpg';
                         const fileName = `quaf-festival-photo-${Date.now()}.${ext}`;
                         const file = new File([blob], fileName, { type: mimeType });

                         if (navigator.canShare && navigator.canShare({ files: [file] })) {
                             this.toastMessage = '';
                             await navigator.share({
                                 files: [file],
                                 title: title,
                             });
                             return;
                         }
                     }
                 } catch (err) {
                     this.toastMessage = '';
                     if (err.name === 'AbortError') {
                         return;
                     }
                     console.warn('Direct image file share failed, trying URL share:', err);
                 }

                 // Fallback to URL sharing if file sharing not supported
                 try {
                     this.toastMessage = '';
                     await navigator.share({
                         title: title,
                         url: fullUrl
                     });
                     return;
                 } catch (err) {
                     this.toastMessage = '';
                     if (err.name === 'AbortError') {
                         return;
                     }
                 }
             }

             // Fallback for browsers without Web Share API
             try {
                 await navigator.clipboard.writeText(fullUrl);
                 this.toastMessage = 'Photo link copied to clipboard!';
                 setTimeout(() => { this.toastMessage = ''; }, 2500);
             } catch (e) {
                 this.toastMessage = '';
             }
         },
         next() {
             if (this.currentIndex < this.images.length - 1) {
                 this.currentIndex++;
             } else {
                 this.currentIndex = 0;
             }
         },
         prev() {
             if (this.currentIndex > 0) {
                 this.currentIndex--;
             } else {
                 this.currentIndex = this.images.length - 1;
             }
         }
     }"
     @keydown.left.window="if(lightboxOpen) prev()"
     @keydown.right.window="if(lightboxOpen) next()"
     @keydown.escape.window="lightboxOpen = false">

    <!-- Header -->
    <div class="mb-6 sm:mb-10">
        <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase animate-subheading">MOMENTS OF SPLENDOR</span>
        <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 mt-1 animate-heading">Festival Photo Gallery</h1>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 mb-8 overflow-x-auto no-scrollbar pb-2 select-none">
        <a href="{{ route('gallery.index') }}" 
           class="px-4 py-2 rounded-xl text-xs font-mono font-bold uppercase transition-all whitespace-nowrap {{ empty($category) ? 'bg-[#be1e2d] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
            All Photos
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('gallery.index', ['category' => $cat]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-mono font-bold uppercase transition-all whitespace-nowrap {{ $category === $cat ? 'bg-[#be1e2d] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                {{ $cat }}
            </a>
        @endforeach
    </div>

    <!-- Toast Notification for Share -->
    <div x-show="toastMessage" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 bg-slate-900/90 text-white text-xs font-mono px-4 py-2.5 rounded-2xl backdrop-blur-md border border-white/20 shadow-xl flex items-center gap-2"
         style="display: none;">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <span x-text="toastMessage"></span>
    </div>

    <!-- Photos Masonry Grid (Supports 16:9 horizontal, 9:16 vertical, and all dimensions) -->
    <div class="columns-1 sm:columns-2 md:columns-3 lg:columns-4 gap-4 sm:gap-6 space-y-4 sm:space-y-6">
        @forelse($items as $index => $item)
            <div class="break-inside-avoid group relative rounded-2xl sm:rounded-3xl overflow-hidden bg-slate-950 border border-slate-200/80 hover:border-slate-300 transition-all duration-500 shadow-2xs hover:shadow-2xl hover:-translate-y-1">
                <!-- Clean Photo (No overlaid text, clicking opens lightbox) -->
                <img src="{{ $item->image_path }}" 
                     alt="{{ $item->title ?: 'QUAF Festival Photo' }}" 
                     @click="openLightbox({{ $index }})"
                     class="w-full h-auto block rounded-2xl sm:rounded-3xl object-cover cursor-pointer transition-transform duration-700 ease-out group-hover:scale-105" 
                     loading="lazy">
                
                <!-- Action Bar with Beautiful Download & Share buttons -->
                <!-- Mobile: Always visible at bottom; Desktop: smooth hover fade up -->
                <div class="absolute bottom-0 inset-x-0 p-3 sm:p-4 bg-gradient-to-t from-black/85 via-black/45 to-transparent flex items-center justify-between gap-2.5 transition-all duration-300 sm:opacity-0 sm:group-hover:opacity-100 pointer-events-auto z-10">
                    <!-- Beautiful Download Button -->
                    <a href="{{ route('gallery.download', $item->id) }}" 
                       download
                       @click.stop
                       class="app-tap flex-1 py-2 sm:py-2.5 px-3.5 rounded-xl bg-white hover:bg-slate-100 text-slate-900 font-sora font-bold text-xs flex items-center justify-center gap-1.5 shadow-lg active:scale-95 transition-all cursor-pointer"
                       title="Download Photo">
                        <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>Download</span>
                    </a>

                    <!-- Direct Photo Share Button -->
                    <button type="button" 
                            @click.stop="sharePhoto('{{ $item->image_path }}', '{{ addslashes($item->title ?: 'QUAF Festival Photo') }}')"
                            class="app-tap py-2 sm:py-2.5 px-3.5 rounded-xl bg-gradient-to-r from-[#be1e2d] via-[#f58220] to-[#f3bd2e] hover:brightness-110 text-white font-sora font-bold text-xs flex items-center justify-center gap-1.5 shadow-lg active:scale-95 transition-all cursor-pointer shrink-0"
                            title="Share Photo directly">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        <span>Share</span>
                    </button>

                    <!-- Full View / Expand Button -->
                    <button type="button" 
                            @click.stop="openLightbox({{ $index }})"
                            class="app-tap w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white/20 hover:bg-white/35 text-white backdrop-blur-md border border-white/30 flex items-center justify-center shadow-lg active:scale-95 transition-all cursor-pointer shrink-0"
                            title="Full Screen View">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-slate-200 font-mono text-sm text-slate-500 shadow-xs">
                No photographs currently found in this gallery collection.
            </div>
        @endforelse
    </div>

    <div class="mt-8 sm:mt-12">
        {{ $items->links() }}
    </div>

    <!-- Lightbox Modal with Full View Controls -->
    <div x-show="lightboxOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-slate-950/95 backdrop-blur-md flex items-center justify-between p-4 sm:p-8"
         style="display: none;">

        <!-- Top Right Actions: Download, Share & Close -->
        <div class="absolute top-4 sm:top-6 right-4 sm:right-6 flex items-center gap-2 sm:gap-3 z-50">
            <!-- Download Button in Lightbox -->
            <a :href="images[currentIndex]?.downloadUrl" 
               download 
               class="app-tap inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-900 font-sora font-bold text-xs shadow-xl active:scale-95 transition-all cursor-pointer"
               title="Download Full Resolution">
                <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Download</span>
            </a>

            <!-- Direct Share Button in Lightbox -->
            <button type="button"
                    @click="sharePhoto(images[currentIndex]?.src, images[currentIndex]?.title)"
                    class="app-tap inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gradient-to-r from-[#be1e2d] via-[#f58220] to-[#f3bd2e] hover:brightness-110 text-white font-sora font-bold text-xs shadow-xl active:scale-95 transition-all cursor-pointer"
                    title="Share Photo directly">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
                <span>Share</span>
            </button>

            <!-- Close Button -->
            <button @click="lightboxOpen = false" class="text-slate-300 hover:text-white p-2 sm:p-2.5 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Previous Button -->
        <button @click="prev()" class="hidden sm:flex text-white p-3 rounded-full bg-white/10 hover:bg-white/20 transition-colors z-40">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>

        <!-- Center Image Stage -->
        <div class="max-w-6xl w-full max-h-[90vh] flex flex-col items-center justify-center mx-auto p-2" x-show="images.length > 0">
            <img :src="images[currentIndex]?.src" 
                 alt="QUAF Gallery Photo" 
                 class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl border border-white/10">
        </div>

        <!-- Next Button -->
        <button @click="next()" class="hidden sm:flex text-white p-3 rounded-full bg-white/10 hover:bg-white/20 transition-colors z-40">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
    </div>
</div>
@endsection
