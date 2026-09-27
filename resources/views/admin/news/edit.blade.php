@extends('layouts.admin', ['title' => 'Edit Article: ' . $news->title])

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.news.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to News</a>
        <h1 class="text-3xl font-sora font-black text-slate-900">Edit Article</h1>
    </div>

    <form method="POST" action="{{ route('admin.news.update', $news) }}" class="rounded-2xl bg-white border border-slate-200 p-8 space-y-6 shadow-sm">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Article Title</label>
            <input type="text" name="title" value="{{ old('title', $news->title) }}" required
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Category</label>
                <input type="text" name="category" value="{{ old('category', $news->category) }}" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Status</label>
                <select name="status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="published" {{ old('status', $news->status) == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ old('status', $news->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="scheduled" {{ old('status', $news->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Cover Image URL</label>
            <input type="url" name="cover_image" value="{{ old('cover_image', $news->cover_image) }}"
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Summary / Excerpt</label>
            <textarea name="excerpt" rows="2" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">{{ old('excerpt', $news->excerpt) }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Full Article Content</label>
            <textarea name="content" rows="8" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">{{ old('content', $news->content) }}</textarea>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_featured" value="1" id="is_featured" {{ old('is_featured', $news->is_featured) ? 'checked' : '' }}
                   class="rounded border-slate-300 text-[#f3bd2e] focus:ring-0">
            <label for="is_featured" class="text-xs font-mono text-slate-700 cursor-pointer">Feature this story on homepage headline</label>
        </div>

        <div class="pt-4 flex items-center justify-between border-t border-slate-100">
            <button type="submit" form="delete-form" class="text-xs font-mono text-red-600 hover:underline">
                Delete Article
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.news.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
                <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#f3bd2e] text-white hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                    Update Article
                </button>
            </div>
        </div>
    </form>

    <form id="delete-form" method="POST" action="{{ route('admin.news.destroy', $news) }}" onsubmit="return confirm('Delete article?');" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
