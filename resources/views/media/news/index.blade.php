@extends('layouts.media')

@section('title', 'Manage News Articles | QUAF 09')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                News & Festival Bulletins
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Publish official festival news, updates, announcements, and bulletins.
            </p>
        </div>
        <a href="{{ route('media.news.create') }}" class="px-4 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs transition-colors shadow-md shadow-[#be1e2d]/20 shrink-0">
            + Publish New Article
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            @php $currentStatus = request('status', ''); @endphp
            <a href="{{ route('media.news.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ empty($currentStatus) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All
            </a>
            <a href="{{ route('media.news.index', ['status' => 'published']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $currentStatus === 'published' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Published
            </a>
            <a href="{{ route('media.news.index', ['status' => 'draft']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $currentStatus === 'draft' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Draft
            </a>
        </div>

        <form method="GET" action="{{ route('media.news.index') }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles..."
                   class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:outline-none focus:border-[#be1e2d] w-full sm:w-64">
            <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white text-xs font-bold rounded-lg hover:bg-slate-700">
                Filter
            </button>
        </form>
    </div>

    <!-- News Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Article</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Featured</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($news as $article)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    @if($article->cover_image)
                                        <img src="{{ $article->cover_image }}" alt="Cover" class="w-12 h-10 object-cover rounded-lg shrink-0 border border-slate-200">
                                    @else
                                        <div class="w-12 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs">{{ $article->title }}</div>
                                        <div class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $article->excerpt ?? Str::limit(strip_tags($article->content), 80) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-600">
                                {{ $article->category }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($article->status === 'published')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Published</span>
                                @elseif($article->status === 'scheduled')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Scheduled</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Draft</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <form method="POST" action="{{ route('media.news.toggle-featured', $article) }}">
                                    @csrf
                                    <button type="submit" class="text-[10px] font-bold px-2 py-0.5 rounded border transition-colors {{ $article->is_featured ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-slate-50 text-slate-400 border-slate-200' }}">
                                        {{ $article->is_featured ? 'Featured' : 'Standard' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 font-mono text-[11px]">
                                {{ $article->created_at->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('media.news.edit', $article) }}" class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition-colors">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('media.news.destroy', $article) }}" onsubmit="return confirm('Are you sure you want to delete this article?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 rounded-lg hover:bg-rose-50 text-rose-600 font-semibold text-xs transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                No news articles found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($news->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $news->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
