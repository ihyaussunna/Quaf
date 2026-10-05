@extends('layouts.public', ['title' => $article->title . ' — QUAF 9.0 Journal'])

@section('content')
<article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">
    <div class="mb-8">
        <a href="{{ route('news.index') }}" class="text-xs font-mono text-[#be1e2d] hover:underline flex items-center gap-1.5 mb-6 font-semibold">
            <span>← Back to Festival Journal</span>
        </a>
        
        <div class="flex items-center gap-3 text-xs font-mono text-slate-500 mb-4">
            <span class="px-2.5 py-1 rounded-lg bg-red-50 border border-red-200 text-[#be1e2d] uppercase font-bold">{{ $article->category }}</span>
            <span>•</span>
            <span>{{ $article->published_at?->format('F d, Y — h:i A') ?? 'Official Dispatch' }}</span>
        </div>

        <h1 class="text-3xl sm:text-5xl font-black text-slate-900 leading-tight mb-6 font-sora">
            {{ $article->title }}
        </h1>

        @if($article->excerpt)
            <p class="text-base sm:text-lg text-slate-600 font-normal leading-relaxed border-l-4 border-[#be1e2d] pl-4 italic bg-slate-50 py-3 rounded-r-xl">
                {{ $article->excerpt }}
            </p>
        @endif
    </div>

    @if($article->cover_image)
        <div class="rounded-3xl overflow-hidden aspect-[16/9] w-full bg-slate-100 mb-10 shadow-md border border-slate-200">
            <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
        </div>
    @endif

    <!-- Content Body -->
    <div class="prose prose-slate prose-lg max-w-none text-slate-700 leading-relaxed space-y-6 font-normal">
        {!! nl2br(e($article->content)) !!}
    </div>

    <!-- Social Share Bar (Section 36) -->
    <div class="mt-12 p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-4 font-mono text-xs">
        <span class="text-slate-500">Share this festival dispatch:</span>
        <div class="flex items-center gap-2">
            <a href="https://api.whatsapp.com/send?text={{ urlencode($article->title . ' - ' . url()->current()) }}"
               target="_blank" class="px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold hover:bg-emerald-100 transition-colors">
                WhatsApp
            </a>
            <a href="https://twitter.com/intent/tweet?text={{ urlencode($article->title) }}&url={{ urlencode(url()->current()) }}"
               target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                X / Twitter
            </a>
            <button onclick="navigator.clipboard.writeText(window.location.href); alert('Article link copied to clipboard!');"
                    class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                Copy Link
            </button>
        </div>
    </div>

    <!-- Related Articles -->
    @if(isset($related) && $related->isNotEmpty())
        <div class="mt-16 pt-10 border-t border-slate-200">
            <h3 class="font-sora font-bold text-xl sm:text-2xl text-slate-900 mb-6">Related Chronicles</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($related as $rel)
                    <a href="{{ route('news.show', $rel->slug) }}" class="group block rounded-2xl bg-white border border-slate-200 p-5 hover:border-slate-300 transition-all shadow-2xs hover:shadow-md">
                        <span class="text-[10px] font-mono text-[#be1e2d] uppercase font-bold block mb-1">{{ $rel->category }}</span>
                        <h4 class="font-bold text-slate-900 text-base group-hover:text-[#be1e2d] transition-colors leading-snug">
                            {{ $rel->title }}
                        </h4>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</article>
@endsection
