@extends('layouts.public', ['title' => 'Visual Gallery — QUAF'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16" 
     x-data="{
         lightboxOpen: false,
         currentIndex: 0,
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
        <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">MOMENTS OF SPLENDOR</span>
        <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 mt-1">Festival Photo Gallery</h1>
        <p class="text-xs sm:text-sm text-slate-600 mt-2 max-w-2xl leading-relaxed">
            Capturing the spirit, eloquence, and artistic triumph across all 4 stages of QUAF.
        </p>
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

    <!-- Photos Grid (4:3 aspect ratio) -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        @forelse($items as $index => $item)
            <div @click="openLightbox({{ $index }})"
                 class="group relative rounded-2xl overflow-hidden aspect-[4/3] bg-slate-100 cursor-pointer border border-slate-200 hover:border-slate-300 transition-all duration-300 shadow-2xs hover:shadow-md">
                <img src="{{ $item->image_path }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-3.5 sm:p-4">
                    <span class="self-start px-2 py-0.5 rounded text-[10px] font-mono uppercase tracking-wider bg-white/90 text-[#be1e2d] font-bold">
                        {{ $item->category }}
                    </span>
                    <div>
                        <h4 class="text-xs sm:text-sm font-sora font-bold text-white leading-tight truncate">{{ $item->title }}</h4>
                        @if($item->group)
                            <span class="text-[10px] sm:text-[11px] text-slate-300 block mt-0.5 truncate font-mono">House: {{ $item->group->name }}</span>
                        @endif
                    </div>
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

    <!-- Lightbox Modal with Next/Prev and Full Controls -->
    <div x-show="lightboxOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-slate-950/95 backdrop-blur-md flex items-center justify-between p-4 sm:p-8"
         style="display: none;">

        <!-- Close Button -->
        <button @click="lightboxOpen = false" class="absolute top-6 right-6 text-slate-300 hover:text-white p-2.5 rounded-full bg-white/10 hover:bg-white/20 transition-colors z-50">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <!-- Previous Button -->
        <button @click="prev()" class="hidden sm:flex text-white p-3 rounded-full bg-white/10 hover:bg-white/20 transition-colors z-40">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>

        <!-- Center Image Stage -->
        <div class="max-w-5xl w-full max-h-[85vh] flex flex-col items-center mx-auto" x-show="images.length > 0">
            <img :src="images[currentIndex]?.src" :alt="images[currentIndex]?.title" class="max-w-full max-h-[75vh] object-contain rounded-2xl shadow-2xl border border-white/10 mb-4">
            <div class="text-center">
                <span class="text-xs font-mono uppercase text-amber-400 block mb-1 font-bold" x-text="images[currentIndex]?.category"></span>
                <h3 class="text-lg sm:text-xl font-sora font-bold text-white" x-text="images[currentIndex]?.title"></h3>
                <span class="text-xs font-mono text-slate-400 mt-1 block" x-show="images[currentIndex]?.group" x-text="'House: ' + images[currentIndex]?.group"></span>
            </div>
        </div>

        <!-- Next Button -->
        <button @click="next()" class="hidden sm:flex text-white p-3 rounded-full bg-white/10 hover:bg-white/20 transition-colors z-40">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
    </div>
</div>
@endsection
