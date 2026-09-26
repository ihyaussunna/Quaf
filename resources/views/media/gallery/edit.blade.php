@extends('layouts.media')

@section('title', 'Edit Gallery Photo | QUAF 09')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                Edit Gallery Photo
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Update photo caption, category, stage assignment, and order.
            </p>
        </div>
        <a href="{{ route('media.gallery.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs">
            Back to Gallery
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

    <form method="POST" action="{{ route('media.gallery.update', $gallery) }}" enctype="multipart/form-data" class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-2xs space-y-6">
        @csrf
        @method('PUT')

        <!-- Title -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Photo Caption / Title *</label>
            <input type="text" name="title" value="{{ old('title', $gallery->title) }}" required
                   class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:outline-none focus:border-[#be1e2d]">
        </div>

        <!-- Current Image Preview & Change -->
        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Photo File</label>
                <span class="text-[11px] text-slate-500">Current photo displayed below</span>
            </div>

            <div class="flex items-center gap-4">
                <img src="{{ $gallery->image_path }}" alt="Current image" class="w-24 h-24 rounded-xl object-cover border border-slate-200 shrink-0">
                <div class="space-y-2 flex-1">
                    <div>
                        <span class="text-[11px] text-slate-500 block mb-1">Upload Replacement Image</span>
                        <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800">
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-500 block mb-1">Or Replace with URL</span>
                        <input type="url" name="image_path" value="{{ old('image_path', $gallery->image_path) }}"
                               class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-mono focus:outline-none focus:border-[#be1e2d]">
                    </div>
                </div>
            </div>
        </div>

        <!-- Category & Group & Stage -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Category *</label>
                <input type="text" name="category" value="{{ old('category', $gallery->category) }}" required list="galleryCategories"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-[#be1e2d]">
                <datalist id="galleryCategories">
                    <option value="Stage Event">
                    <option value="Crowd & Mood">
                    <option value="Backstage">
                    <option value="Inauguration & Valedictory">
                    <option value="Cultural Performances">
                    <option value="Festival Highlights">
                </datalist>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Stage (Optional)</label>
                <select name="stage_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-[#be1e2d]">
                    <option value="">None (General)</option>
                    @foreach($stages as $stage)
                        <option value="{{ $stage->id }}" {{ old('stage_id', $gallery->stage_id) == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Team / House (Optional)</label>
                <select name="group_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-[#be1e2d]">
                    <option value="">None (General)</option>
                    @foreach($groups as $grp)
                        <option value="{{ $grp->id }}" {{ old('group_id', $gallery->group_id) == $grp->id ? 'selected' : '' }}>{{ $grp->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Display Order & Featured -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center pt-2">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Display Order</label>
                <input type="number" name="display_order" value="{{ old('display_order', $gallery->display_order) }}"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-[#be1e2d]">
            </div>

            <div class="pt-5">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $gallery->is_featured) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-slate-300 text-[#be1e2d] focus:ring-[#be1e2d]">
                    <span class="text-xs font-bold text-slate-700">Mark as Featured Photo</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('media.gallery.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs shadow-md shadow-[#be1e2d]/20">
                Update Photo
            </button>
        </div>
    </form>
</div>
@endsection
