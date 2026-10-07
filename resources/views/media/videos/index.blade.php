@extends('layouts.media')

@section('title', 'Manage Videos & Live Streams | QUAF')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                Videos & Live Streams
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Manage live streams and highlight videos using YouTube links.
            </p>
        </div>
        <a href="{{ route('media.videos.create') }}" class="px-4 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs transition-colors shadow-md shadow-[#be1e2d]/20 shrink-0">
            + Add YouTube Video
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            @php $currentLive = request('is_live', ''); @endphp
            <a href="{{ route('media.videos.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $currentLive === '' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All Videos
            </a>
            <a href="{{ route('media.videos.index', ['is_live' => '1']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $currentLive === '1' ? 'bg-red-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Live Now
            </a>
            <a href="{{ route('media.videos.index', ['is_live' => '0']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $currentLive === '0' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Recorded Videos
            </a>
        </div>

        <div class="text-xs text-slate-400 font-mono">
            Total {{ $videos->total() }} Videos
        </div>
    </div>

    <!-- Video Cards Grid -->
    @if($videos->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($videos as $video)
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs group flex flex-col justify-between">
                    <div>
                        <!-- Thumbnail with Live Indicator -->
                        <div class="relative aspect-video bg-slate-900 overflow-hidden">
                            <img src="{{ $video->thumbnail_path ?? 'https://img.youtube.com/vi/' . $video->youtube_id . '/hqdefault.jpg' }}" 
                                 alt="{{ $video->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            
                            @if($video->is_live)
                                <div class="absolute top-2 left-2 flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-red-600 text-white font-bold text-[9px] uppercase tracking-wider shadow">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                                    <span>LIVE</span>
                                </div>
                            @endif

                            <a href="https://www.youtube.com/watch?v={{ $video->youtube_id }}" target="_blank" class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="w-10 h-10 rounded-full bg-red-600 text-white flex items-center justify-center shadow-lg">
                                    <svg class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            </a>
                        </div>

                        <!-- Details -->
                        <div class="p-3.5 space-y-1.5">
                            <div class="flex items-center justify-between text-[10px] text-slate-400">
                                <span class="font-semibold uppercase text-slate-600">{{ $video->category }}</span>
                                <span class="font-mono">{{ $video->youtube_id }}</span>
                            </div>
                            <h4 class="font-bold text-slate-900 text-xs line-clamp-2 leading-snug">{{ $video->title }}</h4>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="px-3.5 py-2.5 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between text-xs">
                        <form method="POST" action="{{ route('media.videos.toggle-live', $video) }}">
                            @csrf
                            <button type="submit" class="text-[10px] font-bold px-2 py-0.5 rounded border transition-colors {{ $video->is_live ? 'bg-red-50 text-red-700 border-red-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                {{ $video->is_live ? 'Live Stream' : 'Offline' }}
                            </button>
                        </form>

                        <div class="inline-flex items-center gap-2">
                            <a href="{{ route('media.videos.edit', $video) }}" class="text-slate-600 hover:text-slate-900 font-semibold text-[11px]">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('media.videos.destroy', $video) }}" onsubmit="return confirm('Are you sure you want to delete this video?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold text-[11px]">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($videos->hasPages())
            <div class="p-4 bg-white rounded-xl border border-slate-200">
                {{ $videos->links() }}
            </div>
        @endif
    @else
        <div class="bg-white p-12 rounded-2xl border border-slate-200 text-center text-slate-400 text-sm">
            No videos added yet. Click above to add a YouTube link.
        </div>
    @endif
</div>
@endsection
