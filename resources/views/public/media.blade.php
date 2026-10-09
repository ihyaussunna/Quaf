@extends('layouts.public', ['title' => 'Festival Media & Video Hub — QUAF'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16" x-data="{
    activeVideo: '{{ $featured?->youtube_id ?? '' }}',
    activeTitle: '{{ addslashes($featured?->title ?? '') }}'
}">
    <!-- Header -->
    <div class="mb-6 sm:mb-10">
        <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase animate-subheading">Official Media Broadcasts</span>
        <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 mt-1 animate-heading">Festival Media Hub</h1>
    </div>

    <!-- Category Filter Bar & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 sm:mb-12">
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-2 sm:pb-0 select-none">
            <a href="{{ route('media.index') }}" 
               class="px-4 py-2 rounded-xl text-xs font-mono font-bold uppercase transition-all whitespace-nowrap {{ empty($category) ? 'bg-[#be1e2d] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                All Videos
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('media.index', ['category' => $cat]) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-mono font-bold uppercase transition-all whitespace-nowrap {{ $category === $cat ? 'bg-[#be1e2d] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('media.index') }}" class="sm:w-72">
            @if($category)
                <input type="hidden" name="category" value="{{ $category }}">
            @endif
            <input type="text" name="search" value="{{ $search }}" placeholder="Search videos..."
                   class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#be1e2d]/20 focus:border-[#be1e2d]">
        </form>
    </div>

    <!-- Main Player Spotlight -->
    @if($featured && $featured->youtube_id)
        <div class="rounded-3xl bg-white border border-slate-200 p-4 sm:p-8 mb-10 sm:mb-16 shadow-md overflow-hidden">
            <div class="aspect-video w-full rounded-2xl overflow-hidden bg-black mb-4 relative shadow-xs">
                <iframe :src="'https://www.youtube.com/embed/' + activeVideo + '?autoplay=0'"
                        class="w-full h-full"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span class="text-[11px] font-mono text-[#be1e2d] uppercase tracking-wider block mb-0.5 font-bold">SPOTLIGHT BROADCAST</span>
                    <h2 class="text-xl sm:text-2xl font-sora font-black text-slate-900 leading-snug" x-text="activeTitle">
                        {{ $featured->title }}
                    </h2>
                </div>
                @if($featured->is_live)
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-50 border border-red-200 text-red-700 text-xs font-mono font-bold shrink-0 self-start sm:self-auto">
                        <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                        <span>LIVE STREAM</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Video Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($videos as $video)
            <div @click="activeVideo = '{{ $video->youtube_id }}'; activeTitle = '{{ addslashes($video->title) }}'; window.scrollTo({ top: 180, behavior: 'smooth' })"
                 class="group rounded-2xl bg-white border border-slate-200 hover:border-slate-300 p-4 cursor-pointer transition-all duration-300 shadow-2xs hover:shadow-md">
                <div class="aspect-video w-full rounded-xl overflow-hidden bg-slate-950 relative mb-4">
                    <img src="{{ $video->thumbnail_path ?? ($video->youtube_id ? 'https://img.youtube.com/vi/' . $video->youtube_id . '/mqdefault.jpg' : '') }}"
                         alt="{{ $video->title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-black/25 flex items-center justify-center group-hover:bg-black/10 transition-colors">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-white/25 hover:bg-white/40 text-white flex items-center justify-center backdrop-blur-md border border-white/40 shadow-xl group-hover:scale-110 transition-all">
                            <svg class="w-5 h-5 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    </div>
                    @if($video->is_live)
                        <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-red-600 text-white shadow-xs">LIVE</span>
                    @endif
                </div>
                <span class="text-[10px] font-mono text-[#be1e2d] uppercase tracking-wider block mb-1 font-bold">{{ $video->category ?? 'Highlight' }}</span>
                <h3 class="font-sora font-bold text-base text-slate-900 group-hover:text-[#be1e2d] transition-colors line-clamp-2 leading-snug">
                    {{ $video->title }}
                </h3>
            </div>
        @empty
            <div class="col-span-3 text-center py-20 bg-white rounded-3xl border border-slate-200 text-slate-500 font-mono text-sm shadow-2xs">
                No video highlights found matching your search.
            </div>
        @endforelse
    </div>

    <div class="mt-12">
        {{ $videos->links() }}
    </div>
</div>
@endsection
