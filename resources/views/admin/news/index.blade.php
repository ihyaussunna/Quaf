@extends('layouts.admin', ['title' => 'Festival Journal & News'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif font-black text-slate-900">Festival Journal</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">Publish dispatches, editorial reviews, and official festival announcements.</p>
        </div>
        <a href="{{ route('admin.news.create') }}" class="px-4 py-2.5 rounded-xl bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase tracking-wider hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
            + New Article
        </a>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Article Title</th>
                        <th class="px-6 py-4 font-semibold">Category</th>
                        <th class="px-6 py-4 font-semibold">Featured</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Published Date</th>
                        <th class="px-6 py-4 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($articles as $a)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.news.edit', $a) }}" class="font-medium text-slate-900 hover:text-[#f3bd2e] transition-colors block">
                                    {{ $a->title }}
                                </a>
                                <span class="text-[10px] text-slate-500">/news/{{ $a->slug }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $a->category }}</td>
                            <td class="px-6 py-4">
                                @if($a->is_featured)
                                    <span class="px-2 py-0.5 rounded bg-amber-50 text-[#f3bd2e] font-bold border border-[#f3bd2e]/30">FEATURED</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($a->status === 'published')
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold">PUBLISHED</span>
                                @elseif($a->status === 'draft')
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200 font-bold">DRAFT</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-bold">SCHEDULED</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $a->published_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.news.edit', $a) }}" class="text-[#f3bd2e] hover:underline font-semibold">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">No news articles published yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $articles->links() }}
    </div>
</div>
@endsection
