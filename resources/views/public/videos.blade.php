@extends('layouts.public', ['title' => 'Festival Theater & Highlights | QUAF'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16" x-data="{
    activeVideo: '{{ $featured?->youtube_id ?? '' }}',
    activeTitle: '{{ addslashes($featured?->title ?? '') }}'
}">
    <div class="mb-6 sm:mb-10">
        <span class="text-xs font-mono font-bold tracking-widest text-[#f3bd2e] uppercase">CINEMATIC ARCHIVES</span>
        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1 sm:mt-2">Festival Theater</h1>
        <p class="text-xs sm:text-sm text-slate-600 mt-2 sm:mt-3 max-w-2xl">
            Watch live broadcasts, event highlights, and grand choral performances.
        </p>
    </div>

    <!-- Main Player Spotlight (Light Theme) -->
    @if($featured)
        <div class="rounded-2xl sm:rounded-3xl bg-white border border-slate-200/90 p-3 sm:p-6 mb-8 sm:mb-16 shadow-md overflow-hidden">
            <div class="aspect-video w-full rounded-xl sm:rounded-2xl overflow-hidden bg-black mb-3 sm:mb-4 relative shadow-xs">
                <iframe :src="'https://www.youtube.com/embed/' + activeVideo + '?autoplay=0'"
                        class="w-full h-full"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
            </div>
            <div class="px-1 sm:px-2 flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4">
                <div>
                    <span class="text-[10px] sm:text-xs font-mono text-[#f3bd2e] uppercase tracking-wider block mb-0.5 font-bold">NOW PLAYING</span>
                    <h2 class="text-lg sm:text-2xl font-sora font-bold text-slate-900 leading-snug" x-text="activeTitle">
                        {{ $featured->title }}
                    </h2>
                </div>
                @if($featured->is_live)
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-50 border border-red-200 text-red-700 text-xs font-mono font-bold self-start sm:self-auto">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        <span>LIVE STREAM</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Video Grid (Light Theme) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @forelse($videos as $video)
            <div @click="activeVideo = '{{ $video->youtube_id }}'; activeTitle = '{{ addslashes($video->title) }}'; window.scrollTo({ top: 100, behavior: 'smooth' })"
                 class="group rounded-2xl bg-white border border-slate-200 hover:border-[#f3bd2e]/60 p-4 cursor-pointer transition-all duration-300 shadow-sm hover:shadow-md">
                <div class="aspect-video w-full rounded-xl overflow-hidden bg-slate-100 relative mb-4">
                    <img src="{{ $video->thumbnail_path ?? 'https://img.youtube.com/vi/' . $video->youtube_id . '/hqdefault.jpg' }}"
                         alt="{{ $video->title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-black/30 flex items-center justify-center group-hover:bg-black/10 transition-colors">
                        <div class="w-12 h-12 rounded-full bg-[#f3bd2e] text-white flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    </div>
                    @if($video->is_live)
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-red-600 text-white shadow-xs">LIVE</span>
                    @endif
                </div>
                <span class="text-[10px] font-mono text-[#f3bd2e] uppercase tracking-wider block mb-1 font-bold">{{ $video->category }}</span>
                <h3 class="font-sora font-bold text-base text-slate-900 group-hover:text-[#f3bd2e] transition-colors line-clamp-2 leading-snug">
                    {{ $video->title }}
                </h3>
            </div>
        @empty
            <div class="col-span-3 text-center py-16 bg-white rounded-2xl text-slate-500 font-mono text-sm border border-slate-200 shadow-sm">
                No videos recorded yet.
            </div>
        @endforelse
    </div>

    <div class="mt-12">
        {{ $videos->links() }}
    </div>
</div>
@endsection
