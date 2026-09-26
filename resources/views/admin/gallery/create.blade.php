@extends('layouts.admin', ['title' => 'Add Photo to Gallery'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.gallery.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to Gallery</a>
        <h1 class="text-3xl font-serif font-black text-slate-900">Add Photo</h1>
    </div>

    <form method="POST" action="{{ route('admin.gallery.store') }}" class="rounded-2xl bg-white border border-slate-200 p-8 space-y-6 shadow-sm">
        @csrf

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Photo Title / Caption</label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Sufi Choral Ensemble at Grand Amphitheatre"
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Image URL</label>
            <input type="url" name="image_path" value="{{ old('image_path') }}" required placeholder="https://images.unsplash.com/..."
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Category</label>
                <input type="text" name="category" value="{{ old('category', 'Festival') }}" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Group (Optional)</label>
                <select name="group_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="">-- None --</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" {{ old('group_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Stage (Optional)</label>
                <select name="stage_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="">-- None --</option>
                    @foreach($stages as $stg)
                        <option value="{{ $stg->id }}" {{ old('stage_id') == $stg->id ? 'selected' : '' }}>{{ $stg->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
            <a href="{{ route('admin.gallery.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
            <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#f3bd2e] text-white hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                Upload Photo
            </button>
        </div>
    </form>
</div>
@endsection
