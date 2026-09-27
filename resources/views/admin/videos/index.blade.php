@extends('layouts.admin', ['title' => 'Festival Videos'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-sora font-black text-slate-900">Festival Videos</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">Manage live broadcast feeds, promotional teasers, and performance recordings.</p>
        </div>
        <a href="{{ route('admin.videos.create') }}" class="px-4 py-2.5 rounded-xl bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase tracking-wider hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
            + Add Video
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($videos as $vid)
            <div class="rounded-2xl bg-white border border-slate-200 p-4 flex flex-col justify-between group shadow-sm hover:shadow-md transition-shadow">
                <div>
                    <div class="aspect-video w-full rounded-xl overflow-hidden bg-slate-100 relative mb-3">
                        <img src="{{ $vid->thumbnail_path ?? 'https://img.youtube.com/vi/' . $vid->youtube_id . '/hqdefault.jpg' }}"
                             alt="{{ $vid->title }}" class="w-full h-full object-cover">
                        @if($vid->is_live)
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded text-[9px] font-mono font-bold uppercase bg-red-600 text-white shadow-sm">LIVE</span>
                        @endif
                    </div>
                    <span class="text-[10px] font-mono text-[#f3bd2e] uppercase block mb-1 font-bold">{{ $vid->category }}</span>
                    <h4 class="font-sora font-bold text-slate-900 text-sm line-clamp-2 leading-snug">{{ $vid->title }}</h4>
                    <span class="text-[10px] font-mono text-slate-500 block mt-1">YouTube ID: {{ $vid->youtube_id }}</span>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-end">
                    <form method="POST" action="{{ route('admin.videos.destroy', $vid) }}" onsubmit="return confirm('Delete video?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-mono text-red-600 hover:underline">Remove</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-16 text-slate-400 font-mono text-sm bg-white rounded-2xl border border-slate-200 shadow-sm">
                No videos added yet.
            </div>
        @endforelse
    </div>

    <div>
        {{ $videos->links() }}
    </div>
</div>
@endsection
