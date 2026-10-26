@extends('layouts.public', [
    'title' => $article->title . ' — QUAF Journal',
    'ogTitle' => $article->title,
    'ogDescription' => $article->excerpt ?? Str::limit(strip_tags($article->content), 160),
    'ogImage' => $article->cover_image ? (str_starts_with($article->cover_image, 'http') ? $article->cover_image : asset($article->cover_image)) : asset('images/quaf-title-logo.png'),
    'ogUrl' => route('news.show', $article->slug),
    'ogType' => 'article'
])

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

        <h1 class="text-3xl sm:text-5xl font-bold text-slate-900 mb-6 font-revathi-bold" style="line-height: 1.15;">
            {{ $article->title }}
        </h1>

        @if($article->excerpt)
            <p class="text-base sm:text-lg text-slate-600 font-normal border-l-4 border-[#be1e2d] pl-4 italic bg-slate-50 py-3 rounded-r-xl font-revathi" style="line-height: 1.25;">
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
    <div class="prose prose-slate prose-lg max-w-none text-slate-700 space-y-6 font-normal font-revathi" style="line-height: 1.28;">
        {!! nl2br(e($article->content)) !!}
    </div>

    <!-- Social Share Bar (Section 36) -->
    <div class="mt-12 p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-4 font-mono text-xs">
        <span class="text-slate-500">Share this festival dispatch:</span>
        <div class="flex items-center gap-2">
            <button type="button"
                    onclick="shareNewsItem('{{ addslashes($article->title) }}', '{{ addslashes($article->excerpt) }}', '{{ route('news.show', $article->slug) }}', '{{ $article->cover_image ? (str_starts_with($article->cover_image, 'http') ? $article->cover_image : asset($article->cover_image)) : '' }}')"
                    class="px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold hover:bg-emerald-100 transition-colors cursor-pointer flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                <span>WhatsApp</span>
            </button>
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

    <!-- Shared JavaScript for News WhatsApp / Native Sharing matching Image 3 -->
    <script>
    async function shareNewsItem(title, excerpt, url, imageUrl) {
        const formattedTitle = title ? title.trim().toUpperCase() : 'QUAF FESTIVAL NEWS';
        const formattedExcerpt = excerpt ? excerpt.trim() : '';
        
        const shareText = `*${formattedTitle}*\n\n${formattedExcerpt}\n\n🔗 Read Full Story: ${url}\n\nĀDABĪC\nINHERITANCE\n\nQUAF — SEASON 09\n2026 Oct 31, Nov 01\nMarkazu Saquafathi Sunniyya, Calicut\n\n© ISU MARKAZ`;

        let fileToShare = null;

        if (imageUrl) {
            try {
                const absoluteUrl = imageUrl.startsWith('http') ? imageUrl : (window.location.origin + imageUrl);
                const res = await fetch(absoluteUrl, { mode: 'cors' });
                if (res.ok) {
                    const blob = await res.blob();
                    const mime = blob.type || 'image/jpeg';
                    const ext = mime.includes('png') ? 'png' : (mime.includes('webp') ? 'webp' : 'jpg');
                    fileToShare = new File([blob], `quaf-news.${ext}`, { type: mime });
                }
            } catch (e) {
                console.warn('Image fetch failed for native sharing:', e);
            }
        }

        if (navigator.share) {
            if (fileToShare && navigator.canShare && navigator.canShare({ files: [fileToShare] })) {
                try {
                    await navigator.share({
                        files: [fileToShare],
                        title: formattedTitle,
                        text: shareText
                    });
                    return;
                } catch (err) {
                    if (err.name === 'AbortError') return;
                }
            }

            try {
                await navigator.share({
                    title: formattedTitle,
                    text: shareText
                });
                return;
            } catch (err) {
                if (err.name === 'AbortError') return;
            }
        }

        const waUrl = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(shareText);
        window.open(waUrl, '_blank');
    }
    </script>

    <!-- Related Articles -->
    @if(isset($related) && $related->isNotEmpty())
        <div class="mt-16 pt-10 border-t border-slate-200">
            <h3 class="font-sora font-bold text-xl sm:text-2xl text-slate-900 mb-6">Related Chronicles</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($related as $rel)
                    <a href="{{ route('news.show', $rel->slug) }}" class="group block rounded-2xl bg-white border border-slate-200 p-5 hover:border-slate-300 transition-all shadow-2xs hover:shadow-md">
                        <span class="text-[10px] font-mono text-[#be1e2d] uppercase font-bold block mb-1">{{ $rel->category }}</span>
                        <h4 class="font-bold text-slate-900 text-base group-hover:text-[#be1e2d] transition-colors font-revathi-bold" style="line-height: 1.18;">
                            {{ $rel->title }}
                        </h4>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</article>
@endsection
