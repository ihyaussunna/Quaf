@extends('layouts.admin', ['title' => 'Add Video'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.videos.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to Videos</a>
        <h1 class="text-3xl font-serif font-black text-slate-900">Add Video</h1>
    </div>

    <form method="POST" action="{{ route('admin.videos.store') }}" class="rounded-2xl bg-white border border-slate-200 p-8 space-y-6 shadow-sm">
        @csrf

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Video Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Stage 01 Live Stream"
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">YouTube Video ID</label>
                <input type="text" name="youtube_id" value="{{ old('youtube_id') }}" required placeholder="e.g. dQw4w9WgXcQ"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Category</label>
                <input type="text" name="category" value="{{ old('category', 'Highlights') }}" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Custom Thumbnail URL (Optional)</label>
            <input type="url" name="thumbnail_path" value="{{ old('thumbnail_path') }}" placeholder="Leave blank to auto-load from YouTube..."
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_live" value="1" id="is_live" {{ old('is_live') ? 'checked' : '' }}
                   class="rounded border-slate-300 text-[#f3bd2e] focus:ring-0">
            <label for="is_live" class="text-xs font-mono text-slate-700 cursor-pointer">Mark as Live Stream Broadcast</label>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
            <a href="{{ route('admin.videos.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
            <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#f3bd2e] text-white hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                Save Video
            </button>
        </div>
    </form>
</div>
@endsection
