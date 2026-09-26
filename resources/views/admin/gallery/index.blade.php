@extends('layouts.admin', ['title' => 'Festival Gallery'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif font-black text-slate-900">Visual Gallery</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">Manage festival photography, categorize by house, stage, or ceremonial event.</p>
        </div>
        <a href="{{ route('admin.gallery.create') }}" class="px-4 py-2.5 rounded-xl bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase tracking-wider hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
            + Add Photo
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @forelse($items as $item)
            <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden flex flex-col justify-between group shadow-sm hover:shadow-md transition-shadow">
                <div class="aspect-square w-full bg-slate-100 relative">
                    <img src="{{ $item->image_path }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                    <div class="absolute top-2 left-2">
                        <span class="px-2 py-0.5 rounded text-[9px] font-mono uppercase bg-black/75 text-white backdrop-blur-sm">
                            {{ $item->category }}
                        </span>
                    </div>
                </div>
                <div class="p-3">
                    <h4 class="text-xs font-medium text-slate-900 truncate mb-2">{{ $item->title }}</h4>
                    <form method="POST" action="{{ route('admin.gallery.destroy', $item) }}" onsubmit="return confirm('Delete photo?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[10px] font-mono text-red-600 hover:underline">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-6 text-center py-16 text-slate-400 font-mono text-sm bg-white rounded-2xl border border-slate-200 shadow-sm">
                No gallery photos added yet.
            </div>
        @endforelse
    </div>

    <div>
        {{ $items->links() }}
    </div>
</div>
@endsection
