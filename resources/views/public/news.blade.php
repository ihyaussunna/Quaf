@extends('layouts.public', ['title' => 'News & Updates — QUAF'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16">
    <!-- Header Section matching Sahityotsav / QUAF Light Theme -->
    <div class="mb-8 sm:mb-12">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-mono font-bold uppercase tracking-wider mb-3">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
            <span>FESTIVAL DISPATCHES &amp; PRESS</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 tracking-tight">
            News &amp; Updates
        </h1>
        <p class="text-xs sm:text-sm text-slate-600 mt-2 max-w-2xl leading-relaxed">
            Real-time reports, declarations, and administrative briefs from QUAF.
        </p>
    </div>

    <!-- Category Filter Tabs (Excluding 'Festival Updates' per user request) -->
    @php
        $cleanCategories = $categories->filter(fn($c) => !empty($c) && !in_array(strtolower(trim($c)), ['festival updates', 'updates']));
    @endphp
    @if($cleanCategories->isNotEmpty())
        <div class="flex items-center gap-2 mb-8 sm:mb-10 overflow-x-auto no-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0 select-none">
            <a href="{{ route('news.index') }}" class="px-4 py-2 rounded-xl text-xs font-mono font-bold uppercase transition-all whitespace-nowrap {{ empty($category) ? 'bg-[#be1e2d] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                All Dispatches
            </a>
            @foreach($cleanCategories as $cat)
                <a href="{{ route('news.index', ['category' => $cat]) }}" class="px-4 py-2 rounded-xl text-xs font-mono font-bold uppercase transition-all whitespace-nowrap {{ $category === $cat ? 'bg-[#be1e2d] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>
    @endif

    <!-- Articles 3-Column Grid (No Featured Story Section) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
        @forelse($news as $article)
            <div class="group flex flex-col rounded-3xl bg-white border border-slate-200 hover:border-[#be1e2d]/40 overflow-hidden transition-all duration-300 shadow-xs hover:shadow-md">
                <!-- Cover Image -->
                <a href="{{ route('news.show', $article->slug) }}" class="aspect-[16/10] w-full overflow-hidden bg-slate-100 block relative">
                    @if($article->cover_image)
                        <img src="{{ str_starts_with($article->cover_image, 'http') ? $article->cover_image : asset($article->cover_image) }}" 
                             alt="{{ $article->title }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                             loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-300">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        </div>
                    @endif
                </a>

                <!-- Card Body -->
                <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <!-- Date with Calendar Icon -->
                        <div class="flex items-center gap-1.5 text-xs font-mono text-amber-600 font-semibold mb-2.5">
                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ $article->published_at?->format('M d, Y') ?? 'October 2026' }}</span>
                        </div>

                        <!-- Title in MLU-Revathi Bold -->
                        <a href="{{ route('news.show', $article->slug) }}" class="block">
                            <h3 class="text-base sm:text-lg font-revathi-bold font-bold text-slate-900 group-hover:text-[#be1e2d] transition-colors leading-snug line-clamp-2 mb-2.5">
                                {{ $article->title }}
                            </h3>
                        </a>

                        <!-- Excerpt in MLU-Revathi Regular -->
                        <p class="text-xs sm:text-sm font-revathi text-slate-600 font-normal line-clamp-3 leading-relaxed">
                            {{ $article->excerpt }}
                        </p>
                    </div>

                    <!-- Card Footer: Read Full Dispatch + Share Button -->
                    <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                        <a href="{{ route('news.show', $article->slug) }}" class="text-xs font-mono font-bold text-amber-600 hover:text-amber-700 flex items-center gap-1.5 transition-colors">
                            <span>Read Full Dispatch</span>
                            <span aria-hidden="true">&rarr;</span>
                        </a>

                        <!-- Circular Share Button -->
                        <button type="button" 
                                onclick="shareNewsItem('{{ addslashes($article->title) }}', '{{ addslashes($article->excerpt) }}', '{{ route('news.show', $article->slug) }}', '{{ $article->cover_image ? (str_starts_with($article->cover_image, 'http') ? $article->cover_image : asset($article->cover_image)) : '' }}')"
                                class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors cursor-pointer shadow-2xs hover:text-[#be1e2d]"
                                title="Share news to WhatsApp">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-1 sm:col-span-2 lg:col-span-3 text-center py-16 bg-white rounded-3xl border border-slate-200 font-mono text-sm text-slate-500 shadow-xs">
                No articles published in this category yet.
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8 sm:mt-12">
        {{ $news->links() }}
    </div>
</div>

<!-- Shared JavaScript for News WhatsApp / Native Sharing matching Image 3 -->
<script>
async function shareNewsItem(title, excerpt, url, imageUrl) {
    const formattedTitle = title ? title.trim().toUpperCase() : 'QUAF FESTIVAL NEWS';
    const formattedExcerpt = excerpt ? excerpt.trim() : '';
    
    // Construct exact share format matching screenshot 3
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

    // 1. Native Web Share API (Mobile WhatsApp sheet with file if supported)
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

        // Native share text fallback
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

    // 2. Direct WhatsApp Web / App intent
    const waUrl = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(shareText);
    window.open(waUrl, '_blank');
}
</script>
@endsection
