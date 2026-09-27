@extends('layouts.public', ['title' => 'Festival Journal | QUAF'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16">
    <div class="mb-6 sm:mb-10">
        <span class="text-xs font-mono font-bold tracking-widest text-[#f3bd2e] uppercase">THE OFFICIAL CONCLAVE CHRONICLE</span>
        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1 sm:mt-2">Festival Journal</h1>
        <p class="text-xs sm:text-sm text-slate-600 mt-2 sm:mt-3 max-w-2xl">
            In-depth reporting, official communiques, and artistic reviews from the halls and stages of QUAF 09.
        </p>
    </div>

    <!-- Category Filter Tabs (Light Theme & Mobile Horizontal Scrollable) -->
    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mb-6 sm:mb-10">
        <a href="{{ route('news.index') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl text-xs font-mono font-bold uppercase transition-all {{ empty($category) ? 'bg-[#f3bd2e] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
            All Dispatches
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('news.index', ['category' => $cat]) }}" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl text-xs font-mono font-bold uppercase transition-all {{ $category === $cat ? 'bg-[#f3bd2e] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                {{ $cat }}
            </a>
        @endforeach
    </div>

    <!-- Featured Headline Article (Light Theme) -->
    @if($featured && empty($search) && empty($category))
        <a href="{{ route('news.show', $featured->slug) }}" class="group block mb-8 sm:mb-16 rounded-2xl sm:rounded-3xl bg-white border border-slate-200 hover:border-[#f3bd2e]/40 overflow-hidden transition-all duration-300 shadow-xs hover:shadow-md">
            <div class="grid grid-cols-1 lg:grid-cols-2">
                <div class="aspect-[16/10] lg:aspect-auto w-full overflow-hidden bg-slate-100 relative">
                    @if($featured->cover_image)
                        <img src="{{ $featured->cover_image }}" alt="{{ $featured->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    @endif
                    <div class="absolute top-3 sm:top-4 left-3 sm:left-4">
                        <span class="px-2.5 sm:px-3 py-1 rounded-full text-[10px] sm:text-xs font-mono uppercase tracking-wider bg-white/90 backdrop-blur-md text-[#f3bd2e] border border-amber-200 font-bold shadow-xs">
                            FEATURED STORY
                        </span>
                    </div>
                </div>
                <div class="p-5 sm:p-8 md:p-12 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 sm:gap-3 text-xs font-mono text-slate-400 mb-2 sm:mb-4">
                            <span class="text-[#f3bd2e] font-semibold">{{ $featured->category }}</span>
                            <span>•</span>
                            <span>{{ $featured->published_at?->format('F d, Y') }}</span>
                        </div>
                        <h2 class="text-xl sm:text-3xl md:text-4xl font-sora font-black text-slate-900 group-hover:text-[#f3bd2e] transition-colors leading-tight mb-2 sm:mb-4">
                            {{ $featured->title }}
                        </h2>
                        <p class="text-xs sm:text-sm md:text-base text-slate-600 font-normal leading-relaxed">
                            {{ $featured->excerpt }}
                        </p>
                    </div>
                    <div class="mt-5 sm:mt-8 flex items-center gap-2 text-xs font-mono font-bold text-[#f3bd2e] uppercase tracking-wider">
                        <span>Read Full Chronicle</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </div>
                </div>
            </div>
        </a>
    @endif

    <!-- Articles Grid (Light Theme) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-8">
        @forelse($news as $article)
            <a href="{{ route('news.show', $article->slug) }}" class="group flex flex-col rounded-2xl bg-white border border-slate-200 hover:border-[#f3bd2e]/40 overflow-hidden transition-all duration-300 shadow-xs hover:shadow-md">
                <div class="aspect-video w-full overflow-hidden bg-slate-100 relative">
                    @if($article->cover_image)
                        <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    @endif
                    <div class="absolute top-3 left-3">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-mono uppercase tracking-wider bg-white/90 backdrop-blur-md text-[#f3bd2e] border border-amber-200 font-bold shadow-xs">
                            {{ $article->category }}
                        </span>
                    </div>
                </div>
                <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-mono text-slate-400 block mb-1.5 sm:mb-2">{{ $article->published_at?->format('M d, Y') }}</span>
                        <h3 class="text-lg sm:text-xl font-sora font-bold text-slate-900 group-hover:text-[#f3bd2e] transition-colors leading-snug mb-2 sm:mb-3">
                            {{ $article->title }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 font-normal line-clamp-2 leading-relaxed">
                            {{ $article->excerpt }}
                        </p>
                    </div>
                    <span class="text-xs font-semibold text-[#f3bd2e] mt-4 sm:mt-6 flex items-center gap-1">
                        Read Story →
                    </span>
                </div>
            </a>
        @empty
            <div class="col-span-3 text-center py-16 bg-white rounded-2xl border border-slate-200 font-mono text-sm text-slate-500 shadow-xs">
                No articles published in this category yet.
            </div>
        @endforelse
    </div>

    <div class="mt-8 sm:mt-12">
        {{ $news->links() }}
    </div>
</div>
@endsection
