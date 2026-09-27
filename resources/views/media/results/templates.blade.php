@extends('layouts.media')

@section('title', 'Poster Templates | QUAF 09 Media')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-900 text-white">
                    Templates
                </span>
                <span class="text-xs text-slate-500 font-mono">Result Poster Frames</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-2 font-sora">
                Result Poster Templates
            </h1>
            <p class="text-xs text-slate-600 mt-1 max-w-2xl">
                Manage background frames used for generating winner posters. Upload new template images or toggle active templates.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('media.results.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors">
                Back to Results
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Upload New Template Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs h-fit space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900">Upload New Template</h2>
                <p class="text-[11px] text-slate-500">Choose a 1080x1080 px or 1080x1350 px PNG/JPG image.</p>
            </div>

            <form action="{{ route('media.results.templates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Template Name</label>
                    <input type="text" name="name" required placeholder="e.g. QUAF 09 Golden Glory Frame"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Template Image</label>
                    <input type="file" name="template_file" accept="image/png,image/jpeg,image/webp" required
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer border border-slate-300 rounded-xl p-1">
                    <p class="text-[10px] text-slate-400 mt-1">Recommended ratio: 1:1 (Square) or 4:5 (Portrait).</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                           class="rounded border-slate-300 text-[#be1e2d] focus:ring-[#be1e2d]">
                    <label for="is_active" class="text-xs font-medium text-slate-700">Set as Active Template</label>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs shadow-xs transition-colors">
                    Upload Template
                </button>
            </form>
        </div>

        <!-- Existing Templates Grid -->
        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-sm font-bold text-slate-900">Available Templates</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($templates as $tpl)
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs flex flex-col justify-between">
                        <div>
                            <div class="relative aspect-square bg-slate-950 flex items-center justify-center overflow-hidden group">
                                <img src="{{ $tpl->image_path }}" alt="{{ $tpl->name }}" class="w-full h-full object-contain">
                                <div class="absolute top-2 right-2">
                                    @if($tpl->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500 text-white shadow-xs">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-500 text-white shadow-xs">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-4">
                                <h3 class="text-xs font-bold text-slate-900 truncate" title="{{ $tpl->name }}">{{ $tpl->name }}</h3>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">ID: #{{ $tpl->id }} &bull; Added {{ $tpl->created_at->format('d M Y') }}</div>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2">
                            <form action="{{ route('media.results.templates.toggle', $tpl) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg border text-[11px] font-bold transition-colors {{ $tpl->is_active ? 'border-amber-300 text-amber-800 hover:bg-amber-50' : 'border-emerald-300 text-emerald-800 hover:bg-emerald-50' }}">
                                    {{ $tpl->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>

                            <form action="{{ route('media.results.templates.destroy', $tpl) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this template?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-red-200 text-red-700 hover:bg-red-50 text-[11px] font-bold transition-colors">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 p-8 bg-white rounded-2xl border border-slate-200 text-center text-xs text-slate-500">
                        No templates available. Please upload a template.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
