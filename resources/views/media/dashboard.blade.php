@extends('layouts.media')

@section('title', 'Media Dashboard | QUAF')

@section('content')
<div class="space-y-8">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-[#1e1014] text-white p-6 sm:p-8 rounded-2xl shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#be1e2d] text-white mb-3">
                Live Media Desk
            </span>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                QUAF Fest — Media & Publicity Wing
            </h1>
            <p class="text-slate-300 text-xs sm:text-sm mt-2 leading-relaxed">
                Publish festival news bulletins, high-resolution media gallery photos, and manage YouTube live streams in real time.
            </p>
        </div>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('media.news.create') }}" class="px-4 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs transition-colors shadow-md">
                + Publish News Article
            </a>
            <a href="{{ route('media.gallery.create') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-colors">
                + Upload Gallery Photo
            </a>
            <a href="{{ route('media.videos.create') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-colors">
                + Add YouTube Video / Live
            </a>
        </div>
    </div>

    <!-- Stat Badges -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">News Articles</span>
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
            </div>
            <div class="text-3xl font-black text-slate-900">{{ $stats['news_total'] }}</div>
            <div class="text-[11px] text-emerald-600 font-medium mt-1">{{ $stats['news_published'] }} published</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Gallery Photos</span>
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div class="text-3xl font-black text-slate-900">{{ $stats['gallery_total'] }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-1">Festival captures</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Videos Uploaded</span>
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
            </div>
            <div class="text-3xl font-black text-slate-900">{{ $stats['videos_total'] }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-1">YouTube streams & trailers</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Live Streams</span>
                <span class="w-2.5 h-2.5 rounded-full {{ $stats['videos_live'] > 0 ? 'bg-red-500 animate-ping' : 'bg-slate-300' }}"></span>
            </div>
            <div class="text-3xl font-black {{ $stats['videos_live'] > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $stats['videos_live'] }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-1">Currently on air</div>
        </div>
    </div>

    <!-- Recent Content Feeds -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent News -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Recent News Articles</h3>
                <a href="{{ route('media.news.index') }}" class="text-xs text-[#be1e2d] hover:underline font-bold">View All</a>
            </div>

            @if($recentNews->count() > 0)
                <div class="space-y-3">
                    @foreach($recentNews as $article)
                        <div class="flex items-start justify-between gap-3 p-3 rounded-xl hover:bg-slate-50 border border-slate-100 transition-colors">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                                        {{ $article->category }}
                                    </span>
                                    @if($article->status === 'published')
                                        <span class="text-[10px] font-bold text-emerald-600">Published</span>
                                    @else
                                        <span class="text-[10px] font-bold text-amber-600">Draft</span>
                                    @endif
                                </div>
                                <h4 class="font-bold text-slate-900 text-xs line-clamp-1">{{ $article->title }}</h4>
                                <div class="text-[11px] text-slate-400">{{ $article->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ route('media.news.edit', $article) }}" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-600 text-xs">
                                    Edit
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-slate-400 text-xs">
                    No news articles published yet.
                </div>
            @endif
        </div>

        <!-- Recent Gallery Uploads -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Recent Gallery Photos</h3>
                <a href="{{ route('media.gallery.index') }}" class="text-xs text-[#be1e2d] hover:underline font-bold">View All</a>
            </div>

            @if($recentGallery->count() > 0)
                <div class="grid grid-cols-3 gap-2">
                    @foreach($recentGallery as $photo)
                        <div class="group relative rounded-xl overflow-hidden aspect-square bg-slate-100 border border-slate-200">
                            <img src="{{ $photo->image_path }}" alt="{{ $photo->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-2 flex flex-col justify-end text-white">
                                <span class="text-[10px] font-bold line-clamp-1">{{ $photo->title }}</span>
                                <span class="text-[9px] text-slate-300">{{ $photo->category }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-slate-400 text-xs">
                    No gallery photos uploaded yet.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
