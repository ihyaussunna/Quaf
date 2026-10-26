@extends('layouts.media')

@section('title', 'Manage Gallery Photos | QUAF')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                Gallery Photos
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Upload, organize, and manage media gallery albums and venue photo moments.
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('media.gallery.bulk') }}" class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition-colors shadow-md shadow-purple-600/20 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Bulk Upload Photos</span>
            </a>
            <a href="{{ route('media.gallery.create') }}" class="px-4 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs transition-colors shadow-md shadow-[#be1e2d]/20">
                + Single Photo
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            @php $currentCat = request('category', ''); @endphp
            <a href="{{ route('media.gallery.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ empty($currentCat) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All Categories
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('media.gallery.index', ['category' => $cat]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $currentCat === $cat ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('media.gallery.index') }}" class="flex items-center gap-2">
            <select name="group_id" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:outline-none focus:border-[#be1e2d]" onchange="this.form.submit()">
                <option value="">Filter by Team</option>
                @foreach($groups as $grp)
                    <option value="{{ $grp->id }}" {{ request('group_id') == $grp->id ? 'selected' : '' }}>{{ $grp->name }}</option>
                @endforeach
            </select>
            <select name="stage_id" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:outline-none focus:border-[#be1e2d]" onchange="this.form.submit()">
                <option value="">Filter by Stage</option>
                @foreach($stages as $stg)
                    <option value="{{ $stg->id }}" {{ request('stage_id') == $stg->id ? 'selected' : '' }}>{{ $stg->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Gallery Grid -->
    @if($items->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($items as $item)
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-2xs group flex flex-col justify-between">
                    <div class="relative aspect-square bg-slate-100 overflow-hidden">
                        <img src="{{ $item->image_path }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @if($item->is_featured)
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded bg-amber-500 text-white font-bold text-[9px] uppercase tracking-wider shadow">
                                Featured
                            </span>
                        @endif
                    </div>
                    <div class="p-3 space-y-1">
                        <div class="font-bold text-slate-900 text-xs line-clamp-1">{{ $item->title }}</div>
                        <div class="flex items-center justify-between text-[10px] text-slate-400">
                            <span>{{ $item->category }}</span>
                            @if($item->stage)
                                <span class="text-indigo-600 font-semibold">{{ $item->stage->code ?? $item->stage->name }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="px-3 pb-3 pt-1 flex items-center justify-between border-t border-slate-100 text-xs">
                        <a href="{{ route('media.gallery.edit', $item) }}" class="text-slate-600 hover:text-slate-900 font-semibold text-[11px]">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('media.gallery.destroy', $item) }}" onsubmit="return confirm('Are you sure you want to delete this photo?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold text-[11px]">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @if($items->hasPages())
            <div class="p-4 bg-white rounded-xl border border-slate-200">
                {{ $items->links() }}
            </div>
        @endif
    @else
        <div class="bg-white p-12 rounded-2xl border border-slate-200 text-center text-slate-400 text-sm">
            No gallery photos uploaded yet. Use the button above to upload new photos.
        </div>
    @endif
</div>
@endsection
