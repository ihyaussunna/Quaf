@extends('layouts.media')

@section('title', 'Upload Gallery Photo | QUAF')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                Upload Gallery Photo
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Upload single photo files or provide direct image links.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('media.gallery.bulk') }}" class="px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm shadow-purple-600/30 flex items-center gap-1.5 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Bulk Upload</span>
            </a>
            <a href="{{ route('media.gallery.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors">
                Back to Gallery
            </a>
        </div>
    </div>

    <!-- Mode Switcher Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('media.gallery.create') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-[#be1e2d] text-white shadow-sm shadow-[#be1e2d]/30">
            Single Photo Upload
        </a>
        <a href="{{ route('media.gallery.bulk') }}" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Bulk Photos Upload (Multi)</span>
        </a>
    </div>

    <!-- Bulk Upload Suggestion Banner -->
    <div class="p-3.5 rounded-xl bg-purple-50/80 border border-purple-200/80 flex items-center justify-between gap-3 text-xs text-purple-900">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>Need to upload multiple stage/event photos in one go?</span>
        </div>
        <a href="{{ route('media.gallery.bulk') }}" class="shrink-0 font-bold text-purple-700 hover:text-purple-900 underline flex items-center gap-1">
            <span>Use Bulk Uploader &rarr;</span>
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

    <form method="POST" action="{{ route('media.gallery.store') }}" enctype="multipart/form-data" class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-2xs space-y-6">
        @csrf

        <!-- Title -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Photo Caption / Title (Optional)</label>
            <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Stage 1 Mappilappattu Finals, Crowd moments... (Optional)"
                   class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:outline-none focus:border-[#be1e2d]">
        </div>

        <!-- Image Upload or URL -->
        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Photo File or Direct Image URL *</label>
            <div class="space-y-3">
                <div>
                    <span class="text-[11px] text-slate-500 block mb-1">Option 1: Upload from Phone / PC (JPG, PNG, WebP up to 6MB)</span>
                    <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800">
                </div>
                <div class="pt-2 border-t border-slate-200">
                    <span class="text-[11px] text-slate-500 block mb-1">Option 2: Direct Image URL</span>
                    <input type="url" name="image_path" value="{{ old('image_path') }}" placeholder="https://example.com/uploads/photo.jpg"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-mono focus:outline-none focus:border-[#be1e2d]">
                </div>
            </div>
        </div>

        <!-- Category & Group & Stage -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Category (Optional)</label>
                <input type="text" name="category" value="{{ old('category', 'Stage Event') }}" list="galleryCategories"
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
                        <option value="{{ $stage->id }}" {{ old('stage_id') == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Team / Group (Optional)</label>
                <select name="group_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-[#be1e2d]">
                    <option value="">None (General)</option>
                    @foreach($groups as $grp)
                        <option value="{{ $grp->id }}" {{ old('group_id') == $grp->id ? 'selected' : '' }}>{{ $grp->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Display Order & Featured -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center pt-2">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Display Order</label>
                <input type="number" name="display_order" value="{{ old('display_order', 0) }}"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-[#be1e2d]">
            </div>

            <div class="pt-5">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
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
                Upload Photo
            </button>
        </div>
    </form>
</div>
@endsection
