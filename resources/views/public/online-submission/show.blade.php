@extends('layouts.public', ['title' => 'Submit Entry: ' . ($form->program?->name ?? $form->title) . ' | QUAF 9.0'])

@section('content')
<section class="min-h-[85vh] py-10 sm:py-16 bg-slate-50 flex items-center justify-center relative overflow-hidden">
    <!-- Ambient Glows -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[340px] sm:w-[600px] h-[340px] sm:h-[400px] bg-red-500/10 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute bottom-10 right-10 w-72 h-72 bg-amber-400/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 w-full relative z-10">

        <!-- Header Card -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white border border-slate-200/90 shadow-2xs text-[11px] font-mono uppercase tracking-widest text-slate-600 mb-3">
                <span class="w-2 h-2 rounded-full bg-[#be1e2d]"></span>
                <span>QUAF 9.0 Digital Submission Desk</span>
            </div>

            <h1 class="font-sora text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                {{ $form->program?->name ?? $form->title }}
            </h1>

            <div class="flex items-center justify-center gap-2 mt-3 text-xs font-mono text-slate-500 flex-wrap">
                <span class="px-2.5 py-0.5 rounded-md bg-[#be1e2d]/10 text-[#be1e2d] font-bold">
                    {{ $form->program?->code ?? 'ONLINE' }}
                </span>
                <span>•</span>
                <span>Category: {{ $form->program?->category?->name ?? 'General' }}</span>
                <span>•</span>
                <span style="color: {{ $form->program?->zone?->color_hex ?? '#005c94' }};" class="font-bold">
                    {{ $form->program?->zone?->name ?? $form->program?->eligibility ?? 'All' }}
                </span>
            </div>
        </div>

        @if($isOpen)
            <!-- Submission Form Card (Apple Glassy Style) -->
            <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-6 sm:p-10 border border-slate-200 shadow-xl relative overflow-hidden"
                 x-data="{
                     submitting: false,
                     codeLetter: '{{ old('code_letter') }}',
                     textContent: '{{ old('text_content') }}',
                     fileName: null,
                     handleFileSelect(event) {
                         const file = event.target.files[0];
                         this.fileName = file ? file.name : null;
                     }
                 }">

                <!-- Rules / Instructions Accordion or Box -->
                @if($form->instructions)
                    <div class="p-4 sm:p-5 rounded-2xl bg-amber-50/60 border border-amber-200/80 mb-6 text-xs text-amber-950 font-sans leading-relaxed">
                        <div class="flex items-center gap-2 font-mono font-bold text-amber-900 text-xs mb-1.5 uppercase tracking-wide">
                            <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>നിർദ്ദേശങ്ങൾ (Instructions & Guidelines)</span>
                        </div>
                        <div class="whitespace-pre-wrap">{{ $form->instructions }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 mb-6">
                        <div class="flex items-center gap-2 text-xs font-bold text-red-800 font-mono mb-1">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>സബ്മിഷനിൽ ചില തടസ്സങ്ങൾ കണ്ടെത്തി:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs text-red-700 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('online-submission.submit', $form->slug) }}" 
                      method="POST" 
                      enctype="multipart/form-data" 
                      @submit="submitting = true" 
                      class="space-y-6">
                    @csrf

                    <!-- 1. Code Letter Input (Prominent & Crucial) -->
                    <div>
                        <label for="code_letter" class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Your Assigned Code Letter (നിങ്ങളുടെ കോഡ് ലെറ്റർ) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   name="code_letter" 
                                   id="code_letter" 
                                   required 
                                   x-model="codeLetter" 
                                   placeholder="e.g. A, B, C, D..." 
                                   maxlength="10" 
                                   class="w-full px-4 py-3.5 rounded-2xl bg-slate-50 border-2 border-slate-200 focus:border-[#be1e2d] text-slate-900 font-mono text-lg font-black uppercase tracking-widest placeholder-slate-400 focus:outline-none transition">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1.5">
                            ഗ്രീൻ റൂമിൽ നിന്നും അല്ലെങ്കിൽ കോർഡിനേറ്ററിൽ നിന്നും നിങ്ങൾക്ക് നൽകിയ കോഡ് ലെറ്റർ ഇവിടെ രേഖപ്പെടുത്തുക.
                        </p>
                    </div>

                    <!-- 2. Text Content Submission -->
                    @if($form->allow_text)
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="text_content" class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700">
                                    {{ $form->text_label ?: 'Content / Text Submission' }}
                                    @if($form->is_text_required) <span class="text-red-500">*</span> @endif
                                </label>
                                <span class="text-[11px] font-mono text-slate-400" x-text="(textContent ? textContent.length : 0) + ' characters'"></span>
                            </div>
                            <textarea name="text_content" 
                                      id="text_content" 
                                      rows="8" 
                                      x-model="textContent" 
                                      {{ $form->is_text_required ? 'required' : '' }}
                                      placeholder="{{ $form->text_placeholder ?: 'നിങ്ങളുടെ രചന ഇവിടെ ടൈപ്പ് ചെയ്യുക അല്ലെങ്കിൽ പേസ്റ്റ് ചെയ്യുക...' }}" 
                                      class="w-full px-4 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 focus:border-[#be1e2d] text-slate-900 text-sm font-sans leading-relaxed placeholder-slate-400 focus:outline-none transition"></textarea>
                        </div>
                    @endif

                    <!-- 3. Photo / Image / PDF File Upload -->
                    @if($form->allow_image)
                        <div>
                            <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                {{ $form->image_label ?: 'Upload Photo / Document' }}
                                @if($form->is_image_required) <span class="text-red-500">*</span> @endif
                            </label>
                            
                            <label class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-slate-300 hover:border-[#be1e2d] rounded-2xl bg-slate-50 hover:bg-slate-100/60 transition cursor-pointer text-center">
                                <svg class="w-8 h-8 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-xs font-bold text-slate-800" x-text="fileName ? fileName : 'Choose photo, drawing scan or PDF file'"></span>
                                <span class="text-[10px] text-slate-500 font-mono mt-1">Supported: JPG, PNG, WEBP, PDF (Max 30MB)</span>
                                <input type="file" 
                                       name="submission_file" 
                                       @change="handleFileSelect($event)" 
                                       accept="image/*,application/pdf" 
                                       {{ $form->is_image_required ? 'required' : '' }}
                                       class="sr-only">
                            </label>
                        </div>
                    @endif

                    <!-- 4. Video File Upload or URL -->
                    @if($form->allow_video)
                        <div class="space-y-3 pt-2 border-t border-slate-100">
                            <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700">
                                {{ $form->video_label ?: 'Video File or Link' }}
                                @if($form->is_video_required) <span class="text-red-500">*</span> @endif
                            </label>

                            <div>
                                <label for="video_url" class="block text-[11px] font-mono text-slate-500 mb-1">
                                    Option A: Google Drive / YouTube Video Link
                                </label>
                                <input type="url" 
                                       name="video_url" 
                                       id="video_url" 
                                       value="{{ old('video_url') }}" 
                                       placeholder="https://drive.google.com/... or https://youtu.be/..." 
                                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-[#be1e2d]">
                            </div>

                            <div class="text-center font-mono text-xs text-slate-400">OR</div>

                            <div>
                                <label class="block text-[11px] font-mono text-slate-500 mb-1">
                                    Option B: Direct Video File Upload (MP4 / MOV)
                                </label>
                                <input type="file" 
                                       name="video_file" 
                                       accept="video/mp4,video/quicktime,video/webm" 
                                       class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono">
                            </div>
                        </div>
                    @endif

                    <!-- Submit Button -->
                    <div class="pt-4 border-t border-slate-100">
                        <button type="submit" 
                                :disabled="submitting" 
                                class="w-full py-4 px-6 rounded-2xl bg-[#be1e2d] hover:bg-[#a01824] text-white font-bold text-sm uppercase tracking-wider shadow-lg shadow-red-600/30 transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                            <template x-if="submitting">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span>Submitting Entry...</span>
                                </span>
                            </template>
                            <template x-if="!submitting">
                                <span class="flex items-center gap-2">
                                    <span>Submit Entry (രചന സമർപ്പിക്കുക)</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </span>
                            </template>
                        </button>
                    </div>

                </form>
            </div>
        @else
            <!-- Closed State Notice -->
            <div class="bg-white rounded-3xl p-8 sm:p-12 border border-slate-200 text-center shadow-lg max-w-lg mx-auto">
                <div class="w-16 h-16 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3 class="font-sora text-xl font-bold text-slate-900 mb-2">
                    Submissions Closed (സബ്മിഷൻ അവസാനിച്ചു)
                </h3>
                <p class="text-xs text-slate-600 leading-relaxed max-w-sm mx-auto mb-6">
                    ഈ പ്രോഗ്രാമിന്റെ ഓൺലൈൻ സബ്മിഷൻ സമയം അവസാനിച്ചിരിക്കുന്നു. കൂടുതൽ വിവരങ്ങൾക്ക് ഗ്രീൻ റൂം കോർഡിനേറ്ററുമായി ബന്ധപ്പെടുക.
                </p>
                <a href="{{ route('home.view') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 text-white font-mono text-xs font-bold uppercase">
                    Return to Festival Home
                </a>
            </div>
        @endif

    </div>
</section>
@endsection
