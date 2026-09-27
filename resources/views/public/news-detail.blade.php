@extends('layouts.public', ['title' => $article->title . ' — QUAF 09 Journal'])

@section('content')
<article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="mb-8">
        <a href="{{ route('news.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline flex items-center gap-1 mb-6 font-semibold">
            ← Back to Journal
        </a>
        <div class="flex items-center gap-3 text-xs font-mono text-slate-500 mb-4">
            <span class="px-2.5 py-1 rounded bg-amber-50 border border-amber-200 text-[#f3bd2e] uppercase font-bold">{{ $article->category }}</span>
            <span>•</span>
            <span>{{ $article->published_at?->format('F d, Y — h:i A') }}</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-black text-slate-900 leading-tight mb-6 {{ preg_match('/[\x{0D00}-\x{0D7F}]/u', $article->title) ? 'font-anek' : 'font-sora' }}">
            {{ $article->title }}
        </h1>
        @if($article->excerpt)
            <p class="text-lg text-slate-600 font-normal leading-relaxed border-l-4 border-[#f3bd2e] pl-4 italic bg-amber-50/50 py-2 rounded-r-xl {{ preg_match('/[\x{0D00}-\x{0D7F}]/u', $article->excerpt) ? 'font-anek' : 'font-sora' }}">
                {{ $article->excerpt }}
            </p>
        @endif
    </div>

    @if($article->cover_image)
        <div class="rounded-3xl overflow-hidden aspect-[16/9] w-full bg-slate-100 mb-12 shadow-md border border-slate-200">
            <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
        </div>
    @endif

    <!-- Content Body (Light Theme) -->
    <div class="prose prose-slate prose-lg max-w-none text-slate-700 leading-relaxed space-y-6 font-normal {{ preg_match('/[\x{0D00}-\x{0D7F}]/u', $article->content) ? 'font-anek' : 'font-sora' }}">
        {!! nl2br(e($article->content)) !!}
    </div>

    <!-- Related Articles (Light Theme) -->
    @if($related->isNotEmpty())
        <div class="mt-20 pt-12 border-t border-slate-200">
            <h3 class="font-sora font-bold text-2xl text-slate-900 mb-6">Related Chronicles</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($related as $rel)
                    <a href="{{ route('news.show', $rel->slug) }}" class="group block rounded-2xl bg-white border border-slate-200 p-5 hover:border-[#f3bd2e]/40 transition-all shadow-sm hover:shadow-md">
                        <span class="text-[10px] font-mono text-[#f3bd2e] uppercase font-bold block mb-1">{{ $rel->category }}</span>
                        <h4 class="font-bold text-slate-900 text-base group-hover:text-[#f3bd2e] transition-colors leading-snug {{ preg_match('/[\x{0D00}-\x{0D7F}]/u', $rel->title) ? 'font-anek' : 'font-sora' }}">
                            {{ $rel->title }}
                        </h4>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</article>
@endsection
