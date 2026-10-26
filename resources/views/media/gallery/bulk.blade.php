@extends('layouts.media')

@section('title', 'Bulk Upload Gallery Photos | QUAF')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>Bulk Upload Gallery Photos</span>
                <span class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 text-[10px] font-bold uppercase tracking-wider">Multi-Photo</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Select and upload multiple photos in one go with shared category, stage, and team tags.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('media.gallery.create') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors">
                Single Upload
            </a>
            <a href="{{ route('media.gallery.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors">
                Back to Gallery
            </a>
        </div>
    </div>

    <!-- Mode Switcher Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('media.gallery.create') }}" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors">
            Single Photo Upload
        </a>
        <a href="{{ route('media.gallery.bulk') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-purple-600 text-white shadow-sm shadow-purple-600/30 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Bulk Photos Upload (Multi)</span>
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

    <!-- Main Upload Form -->
    <form id="bulkUploadForm" method="POST" action="{{ route('media.gallery.bulk.store') }}" enctype="multipart/form-data" class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-2xs space-y-6">
        @csrf

        <!-- 1. Drag & Drop Multi-file Upload Box -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                Select Photo Files * (Multiple files supported)
            </label>
            <div id="dropZone" class="relative border-2 border-dashed border-slate-300 hover:border-purple-500 rounded-2xl p-6 sm:p-8 text-center bg-slate-50/60 hover:bg-purple-50/30 transition-all cursor-pointer group">
                <input type="file" id="bulkFileInput" name="image_files[]" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                
                <div class="space-y-2 pointer-events-none">
                    <div class="w-12 h-12 mx-auto rounded-full bg-purple-100 group-hover:bg-purple-200 text-purple-600 flex items-center justify-center transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    </div>
                    <div class="text-sm font-bold text-slate-800">
                        Drop photos here or <span class="text-purple-600 underline">Browse files</span>
                    </div>
                    <p class="text-[11px] text-slate-500">
                        Supports JPG, PNG, WebP, AVIF, HEIC • Select multiple photos at once
                    </p>
                </div>
            </div>
        </div>

        <!-- Selected Files Preview Tray -->
        <div id="previewContainer" class="hidden space-y-3 p-4 rounded-xl bg-slate-50 border border-slate-200">
            <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                <span id="selectedCountBadge">0 Photos Selected</span>
                <button type="button" id="clearAllBtn" class="text-rose-600 hover:text-rose-700 font-semibold cursor-pointer text-[11px]">
                    Clear All
                </button>
            </div>
            <div id="thumbnailGrid" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3 max-h-72 overflow-y-auto p-1">
                <!-- Dynamically populated previews -->
            </div>
        </div>

        <!-- 2. Shared Metadata Applied to all Selected Photos -->
        <div class="pt-4 border-t border-slate-100 space-y-4">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 font-mono">
                Shared Photo Details (Applied to all selected images)
            </h3>

            <!-- Shared Title / Caption Prefix -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Common Caption / Title Prefix (Optional)
                </label>
                <input type="text" id="commonTitle" name="title" value="{{ old('title') }}" placeholder="e.g. Stage 1 Mappilappattu Finals (will auto-number: #1, #2... or leave empty to use file names)"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold focus:outline-none focus:border-purple-600">
                <span class="text-[11px] text-slate-400 mt-1 block">
                    If left blank, each photo's original filename will be used as its title.
                </span>
            </div>

            <!-- Category & Group & Stage -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Category</label>
                    <input type="text" id="commonCategory" name="category" value="{{ old('category', 'Stage Event') }}" list="bulkGalleryCategories"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-purple-600">
                    <datalist id="bulkGalleryCategories">
                        <option value="Stage Event">
                        <option value="Cultural Performances">
                        <option value="Crowd & Mood">
                        <option value="Backstage">
                        <option value="Inauguration & Valedictory">
                        <option value="Festival Highlights">
                        <option value="Award Ceremony">
                    </datalist>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Stage (Optional)</label>
                    <select id="commonStage" name="stage_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-purple-600">
                        <option value="">None (General)</option>
                        @foreach($stages as $stage)
                            <option value="{{ $stage->id }}" {{ old('stage_id') == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Team / Group (Optional)</label>
                    <select id="commonGroup" name="group_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-purple-600">
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
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Starting Display Order</label>
                    <input type="number" id="commonDisplayOrder" name="display_order" value="{{ old('display_order', 0) }}"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-purple-600">
                </div>

                <div class="pt-5">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="commonFeatured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-purple-600 focus:ring-purple-600">
                        <span class="text-xs font-bold text-slate-700">Mark all as Featured Photos</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Real-time Progress Bar (shown during upload) -->
        <div id="progressSection" class="hidden space-y-2 p-4 rounded-xl bg-purple-50 border border-purple-200">
            <div class="flex items-center justify-between text-xs font-bold text-purple-900">
                <span id="progressStatusText">Uploading photos...</span>
                <span id="progressPercentText" class="font-mono">0%</span>
            </div>
            <div class="w-full h-3 rounded-full bg-purple-200 overflow-hidden">
                <div id="progressBarFill" class="h-full bg-purple-600 transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <a href="{{ route('media.gallery.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50 transition-colors">
                Cancel
            </a>
            <button type="submit" id="submitBtn" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/30 transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                <span id="submitBtnText">Upload All Selected Photos</span>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('bulkFileInput');
    const previewContainer = document.getElementById('previewContainer');
    const thumbnailGrid = document.getElementById('thumbnailGrid');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const clearAllBtn = document.getElementById('clearAllBtn');
    const form = document.getElementById('bulkUploadForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitBtnText = document.getElementById('submitBtnText');
    const progressSection = document.getElementById('progressSection');
    const progressStatusText = document.getElementById('progressStatusText');
    const progressPercentText = document.getElementById('progressPercentText');
    const progressBarFill = document.getElementById('progressBarFill');

    // In-memory array of selected File objects
    let selectedFiles = [];

    fileInput.addEventListener('change', function (e) {
        const newFiles = Array.from(e.target.files);
        if (newFiles.length === 0) return;

        // Append to existing selected files (dedup by name+size)
        newFiles.forEach(file => {
            const exists = selectedFiles.some(f => f.name === file.name && f.size === file.size);
            if (!exists) {
                selectedFiles.push(file);
            }
        });

        renderPreviews();
    });

    clearAllBtn.addEventListener('click', function () {
        selectedFiles = [];
        fileInput.value = '';
        renderPreviews();
    });

    function renderPreviews() {
        thumbnailGrid.innerHTML = '';

        if (selectedFiles.length === 0) {
            previewContainer.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtnText.textContent = 'Upload All Selected Photos';
            return;
        }

        previewContainer.classList.remove('hidden');
        const totalBytes = selectedFiles.reduce((acc, f) => acc + f.size, 0);
        const totalMb = (totalBytes / (1024 * 1024)).toFixed(1);
        selectedCountBadge.textContent = `${selectedFiles.length} Photos Selected — Total: ${totalMb} MB`;
        submitBtnText.textContent = `Upload ${selectedFiles.length} Photos`;

        selectedFiles.forEach((file, index) => {
            const card = document.createElement('div');
            card.className = 'relative aspect-square rounded-xl overflow-hidden border border-slate-200 bg-white group shadow-2xs';
            card.id = `thumb-${index}`;

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = 'w-full h-full object-cover';
            card.appendChild(img);

            // Filename tag
            const label = document.createElement('div');
            label.className = 'absolute bottom-0 inset-x-0 bg-slate-950/80 text-white text-[9px] px-1.5 py-0.5 truncate font-mono';
            label.textContent = file.name;
            card.appendChild(label);

            // Remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'absolute top-1 right-1 w-5 h-5 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center text-[10px] cursor-pointer shadow';
            removeBtn.innerHTML = '&times;';
            removeBtn.onclick = (ev) => {
                ev.stopPropagation();
                selectedFiles.splice(index, 1);
                renderPreviews();
            };
            card.appendChild(removeBtn);

            thumbnailGrid.appendChild(card);
        });
    }

    // Interactive Sequential Upload via AJAX
    form.addEventListener('submit', async function (e) {
        if (selectedFiles.length === 0) {
            alert('ദയവായി കുറഞ്ഞത് ഒരു ഫോട്ടോയെങ്കിലും തിരഞ്ഞെടുക്കുക.');
            e.preventDefault();
            return;
        }

        // Use sequential progressive AJAX uploader
        e.preventDefault();

        submitBtn.disabled = true;
        progressSection.classList.remove('hidden');
        progressStatusText.textContent = `Uploading 0 of ${selectedFiles.length}...`;
        progressBarFill.style.width = '0%';
        progressPercentText.textContent = '0%';

        const csrfToken = document.querySelector('input[name="_token"]').value;
        const commonTitle = document.getElementById('commonTitle').value;
        const commonCategory = document.getElementById('commonCategory').value;
        const commonStage = document.getElementById('commonStage').value;
        const commonGroup = document.getElementById('commonGroup').value;
        const commonDisplayOrder = parseInt(document.getElementById('commonDisplayOrder').value) || 0;
        const commonFeatured = document.getElementById('commonFeatured').checked ? '1' : '0';

        let completed = 0;
        let failed = 0;
        const total = selectedFiles.length;

        for (let i = 0; i < total; i++) {
            const file = selectedFiles[i];
            const card = document.getElementById(`thumb-${i}`);

            if (card) {
                // Highlight uploading card
                card.classList.add('ring-2', 'ring-purple-500');
            }

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('image_file', file);
            formData.append('title', commonTitle);
            formData.append('category', commonCategory);
            if (commonStage) formData.append('stage_id', commonStage);
            if (commonGroup) formData.append('group_id', commonGroup);
            formData.append('display_order', commonDisplayOrder + i);
            formData.append('is_featured', commonFeatured);
            formData.append('index', (i + 1).toString());
            formData.append('total', total.toString());

            try {
                const res = await fetch("{{ route('media.gallery.bulk.item') }}", {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                if (res.ok) {
                    completed++;
                    if (card) {
                        card.classList.remove('ring-purple-500');
                        card.classList.add('ring-2', 'ring-emerald-500');
                        const check = document.createElement('div');
                        check.className = 'absolute top-1 left-1 px-1.5 py-0.5 rounded bg-emerald-500 text-white font-bold text-[8px] uppercase';
                        check.textContent = 'Uploaded';
                        card.appendChild(check);
                    }
                } else {
                    failed++;
                    if (card) {
                        card.classList.remove('ring-purple-500');
                        card.classList.add('ring-2', 'ring-rose-500');
                    }
                }
            } catch (err) {
                failed++;
                if (card) {
                    card.classList.remove('ring-purple-500');
                    card.classList.add('ring-2', 'ring-rose-500');
                }
            }

            // Update Progress
            const percent = Math.round(((i + 1) / total) * 100);
            progressBarFill.style.width = `${percent}%`;
            progressPercentText.textContent = `${percent}%`;
            progressStatusText.textContent = `Uploading photo ${i + 1} of ${total}...`;
        }

        // Finished all
        progressStatusText.textContent = `Completed! ${completed} of ${total} photos uploaded.`;
        submitBtnText.textContent = 'Redirecting to Gallery...';

        setTimeout(() => {
            window.location.href = "{{ route('media.gallery.index') }}";
        }, 800);
    });
});
</script>
@endsection
