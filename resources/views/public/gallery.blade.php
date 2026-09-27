@extends('layouts.public', ['title' => 'Visual Gallery | QUAF'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16" x-data="{
    lightboxOpen: false,
    activeImg: '',
    activeTitle: '',
    activeCategory: '',
    openLightbox(src, title, cat) {
        this.activeImg = src;
        this.activeTitle = title;
        this.activeCategory = cat;
        this.lightboxOpen = true;
    }
}">
    <!-- Header -->
    <div class="mb-6 sm:mb-10">
        <span class="text-xs font-mono font-bold tracking-widest text-[#f3bd2e] uppercase">MOMENTS OF SPLENDOR</span>
        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1 sm:mt-2">Visual Gallery</h1>
        <p class="text-xs sm:text-sm text-slate-600 mt-2 sm:mt-3 max-w-2xl">
            A photographic tapestry capturing the spirit, devotion, and artistic triumph across all stages.
        </p>
    </div>

    <!-- Filter Tabs (Light Theme) -->
    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mb-6 sm:mb-10">
        <a href="{{ route('gallery.index') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl text-xs font-mono font-bold uppercase transition-all {{ empty($category) && empty($groupId) && empty($stageId) ? 'bg-[#f3bd2e] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
            All Photos
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('gallery.index', ['category' => $cat]) }}" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl text-xs font-mono font-bold uppercase transition-all {{ $category === $cat ? 'bg-[#f3bd2e] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                {{ $cat }}
            </a>
        @endforeach
    </div>

    <!-- Photos Grid (Light Theme - 2 cols on mobile) -->
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
        @forelse($items as $item)
            <div @click="openLightbox('{{ $item->image_path }}', '{{ addslashes($item->title) }}', '{{ $item->category }}')"
                 class="group relative rounded-xl sm:rounded-2xl overflow-hidden aspect-[4/3] bg-slate-100 cursor-pointer border border-slate-200 hover:border-[#f3bd2e]/60 transition-all duration-300 shadow-xs hover:shadow-md">
                <img src="{{ $item->image_path }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-2.5 sm:p-4">
                    <span class="self-start px-2 py-0.5 rounded text-[9px] sm:text-[10px] font-mono uppercase tracking-wider bg-white/90 text-[#f3bd2e] font-bold">
                        {{ $item->category }}
                    </span>
                    <div>
                        <h4 class="text-xs sm:text-sm font-sora font-bold text-white leading-tight truncate">{{ $item->title }}</h4>
                        @if($item->group)
                            <span class="text-[10px] sm:text-[11px] text-slate-300 block mt-0.5 truncate">Group: {{ $item->group->name }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 sm:col-span-4 text-center py-16 bg-white rounded-2xl border border-slate-200 font-mono text-sm text-slate-500 shadow-xs">
                No photographs found in this collection.
            </div>
        @endforelse
    </div>

    <div class="mt-8 sm:mt-12">
        {{ $items->links() }}
    </div>

    <!-- Lightbox Modal -->
    <div x-show="lightboxOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-4 sm:p-8"
         @keydown.escape.window="lightboxOpen = false"
         style="display: none;">

        <button @click="lightboxOpen = false" class="absolute top-6 right-6 text-slate-300 hover:text-white p-2 rounded-full bg-white/10 hover:bg-white/20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <div class="max-w-5xl max-h-[85vh] flex flex-col items-center">
            <img :src="activeImg" :alt="activeTitle" class="max-w-full max-h-[75vh] object-contain rounded-xl shadow-2xl border border-white/10 mb-4">
            <div class="text-center">
                <span class="text-xs font-mono uppercase text-[#f3bd2e] block mb-1 font-bold" x-text="activeCategory"></span>
                <h3 class="text-xl font-sora font-bold text-white" x-text="activeTitle"></h3>
            </div>
        </div>
    </div>
</div>
@endsection
