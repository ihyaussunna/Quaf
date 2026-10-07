@extends('layouts.media')

@section('title', 'Create News Article | QUAF')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                Publish News Article
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Provide article details, category, and cover image.
            </p>
        </div>
        <a href="{{ route('media.news.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs">
            Back to List
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <div class="font-bold">Please resolve the following errors:</div>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('media.news.store') }}" enctype="multipart/form-data" class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-2xs space-y-6">
        @csrf

        <!-- Title -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Headline / Title *</label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="Enter news headline..."
                   class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:outline-none focus:border-[#be1e2d]">
        </div>

        <!-- Category & Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Category *</label>
                <input type="text" name="category" value="{{ old('category', 'Festival Updates') }}" required list="categoryList" placeholder="e.g. Festival Updates, Results, Stage News"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-[#be1e2d]">
                <datalist id="categoryList">
                    <option value="Festival Updates">
                    <option value="Stage Highlights">
                    <option value="Results & Honors">
                    <option value="General News">
                    <option value="Press Release">
                    <option value="Special Report">
                </datalist>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Publish Status *</label>
                <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-[#be1e2d]">
                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published (Live immediately)</option>
                    <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Save as draft)</option>
                    <option value="scheduled" {{ old('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                </select>
            </div>
        </div>

        <!-- Cover Image (Upload or URL) -->
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Cover Image</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <span class="text-[11px] text-slate-500 block mb-1">Option 1: Upload from Device (JPG, PNG, WebP)</span>
                    <input type="file" name="cover_file" accept="image/*" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800">
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 block mb-1">Option 2: Direct Image URL</span>
                    <input type="url" name="cover_image" value="{{ old('cover_image') }}" placeholder="https://example.com/photo.jpg"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono focus:outline-none focus:border-[#be1e2d]">
                </div>
            </div>
        </div>

        <!-- Excerpt -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Short Excerpt (Optional)</label>
            <textarea name="excerpt" rows="2" placeholder="Brief summary of the article..."
                      class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-[#be1e2d]">{{ old('excerpt') }}</textarea>
        </div>

        <!-- Full Content -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Full Content *</label>
            <textarea name="content" rows="8" required placeholder="Type full news story..."
                      class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:outline-none focus:border-[#be1e2d] leading-relaxed">{{ old('content') }}</textarea>
        </div>

        <!-- Featured Checkbox -->
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_featured" value="1" id="is_featured" {{ old('is_featured') ? 'checked' : '' }}
                   class="w-4 h-4 rounded border-slate-300 text-[#be1e2d] focus:ring-[#be1e2d]">
            <label for="is_featured" class="text-xs font-bold text-slate-700 cursor-pointer">
                Mark as Featured Article (Pin to Homepage)
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('media.news.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs shadow-md shadow-[#be1e2d]/20">
                Save & Publish
            </button>
        </div>
    </form>
</div>
@endsection
