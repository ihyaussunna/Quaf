@extends('layouts.public', ['title' => 'Visual Gallery — QUAF'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16" 
     x-data="{
         lightboxOpen: false,
         currentIndex: 0,
         toastMessage: '',
         images: [
             @foreach($items as $idx => $item)
                 {
                     src: '{{ $item->image_path }}',
                     title: '{{ addslashes($item->title) }}',
                     category: '{{ $item->category }}',
                     group: '{{ $item->group?->name }}'
                 }{{ !$loop->last ? ',' : '' }}
             @endforeach
         ],
         openLightbox(idx) {
             this.currentIndex = idx;
             this.lightboxOpen = true;
         },
         sharePhoto(url) {
             const fullUrl = url.startsWith('http') ? url : window.location.origin + url;
             if (navigator.share) {
                 navigator.share({
                     title: 'QUAF Festival Photo',
                     url: fullUrl
                 }).catch(() => {});
             } else {
                 navigator.clipboard.writeText(fullUrl).then(() => {
                     this.toastMessage = 'Link copied to clipboard!';
                     setTimeout(() => { this.toastMessage = ''; }, 2500);
                 }).catch(() => {});
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
            <div @click="openLightbox({{ $index }})"
                 class="break-inside-avoid group relative rounded-2xl sm:rounded-3xl overflow-hidden bg-slate-950 cursor-pointer border border-slate-200/80 hover:border-slate-300 transition-all duration-500 shadow-2xs hover:shadow-2xl hover:-translate-y-1">
                <!-- Clean Photo (No overlaid text) -->
                <img src="{{ $item->image_path }}" 
                     alt="QUAF Festival" 
                     class="w-full h-auto block rounded-2xl sm:rounded-3xl object-cover transition-transform duration-700 ease-out group-hover:scale-105" 
                     loading="lazy">
                
                <!-- Hover Overlay with Glassy Action Buttons (Matching Sahityotsav model screenshot) -->
                <div class="absolute inset-0 bg-black/45 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center gap-3 p-4 z-10">
                    <!-- 1. Download Button -->
                    <a href="{{ $item->image_path }}" 
                       download="QUAF-Photo-{{ $item->id }}" 
                       target="_blank"
                       @click.stop
                       class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/25 hover:bg-white/40 text-white backdrop-blur-md border border-white/40 flex items-center justify-center transition-all transform hover:scale-110 shadow-lg cursor-pointer"
                       title="Download Photo">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </a>

                    <!-- 2. Share Button -->
                    <button type="button" 
                            @click.stop="sharePhoto('{{ $item->image_path }}')"
                            class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/25 hover:bg-white/40 text-white backdrop-blur-md border border-white/40 flex items-center justify-center transition-all transform hover:scale-110 shadow-lg cursor-pointer"
                            title="Share Photo">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                    </button>

                    <!-- 3. Full View / Expand Button -->
                    <button type="button" 
                            @click.stop="openLightbox({{ $index }})"
                            class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/25 hover:bg-white/40 text-white backdrop-blur-md border border-white/40 flex items-center justify-center transition-all transform hover:scale-110 shadow-lg cursor-pointer"
                            title="Full View">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        <!-- Top Right Actions: Download & Close -->
        <div class="absolute top-6 right-6 flex items-center gap-3 z-50">
            <a :href="images[currentIndex]?.src" 
               download 
               target="_blank"
               class="text-slate-300 hover:text-white p-2.5 rounded-full bg-white/10 hover:bg-white/20 transition-colors"
               title="Download Full Resolution">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
            <button @click="lightboxOpen = false" class="text-slate-300 hover:text-white p-2.5 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
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
