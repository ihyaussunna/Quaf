@extends('layouts.media')

@section('title', 'Add YouTube Video | QUAF 09')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                Add Video / Live Stream
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Paste a YouTube link and the video ID will be extracted automatically.
            </p>
        </div>
        <a href="{{ route('media.videos.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs">
            Back to Videos
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <div class="font-bold">Please correct the following errors:</div>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('media.videos.store') }}" enctype="multipart/form-data" class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-2xs space-y-6">
        @csrf

        <!-- Title -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Video Title *</label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Grand Inaugural Ceremony Live — Stage 1"
                   class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:outline-none focus:border-[#be1e2d]">
        </div>

        <!-- YouTube Link or ID -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">YouTube URL or Video ID *</label>
            <input type="text" name="youtube_url" value="{{ old('youtube_url') }}" required placeholder="https://www.youtube.com/watch?v=... or https://youtu.be/... or 11-digit ID"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-mono focus:outline-none focus:border-[#be1e2d]">
            <p class="text-[11px] text-slate-500 mt-1">
                Paste any YouTube link here (Watch URL, Share URL, Live Stream URL).
            </p>
        </div>

        <!-- Category & Live Option -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Category *</label>
                <input type="text" name="category" value="{{ old('category', 'Festival Live') }}" required list="videoCategories"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-[#be1e2d]">
                <datalist id="videoCategories">
                    <option value="Festival Live">
                    <option value="Stage Highlights">
                    <option value="Official Trailer">
                    <option value="Theme Song">
                    <option value="Speeches & Sessions">
                    <option value="Cultural Performances">
                    <option value="Daily Recap">
                </datalist>
            </div>

            <div class="flex items-center pt-6">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_live" value="1" {{ old('is_live') ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-slate-300 text-red-600 focus:ring-red-600">
                    <span class="text-xs font-bold text-slate-700">This is an active Live Stream</span>
                </label>
            </div>
        </div>

        <!-- Custom Thumbnail (Optional) -->
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Custom Thumbnail (Optional - YouTube thumbnail used if empty)</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <span class="text-[11px] text-slate-500 block mb-1">Upload Image (JPG, PNG, WebP)</span>
                    <input type="file" name="thumbnail_file" accept="image/*" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800">
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 block mb-1">Or Thumbnail URL</span>
                    <input type="url" name="thumbnail_path" value="{{ old('thumbnail_path') }}" placeholder="https://example.com/thumb.jpg"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono focus:outline-none focus:border-[#be1e2d]">
                </div>
            </div>
        </div>

        <!-- Display Order -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Display Order</label>
            <input type="number" name="display_order" value="{{ old('display_order', 0) }}"
                   class="w-32 px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-[#be1e2d]">
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('media.videos.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs shadow-md shadow-[#be1e2d]/20">
                Save Video
            </button>
        </div>
    </form>
</div>
@endsection
