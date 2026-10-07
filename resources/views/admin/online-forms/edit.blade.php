@extends('layouts.admin', ['title' => 'Edit Online Submission Form | QUAF'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.online-forms.index') }}" class="text-xs font-mono text-slate-500 hover:text-[#be1e2d] transition">
                    &larr; Back to Forms
                </a>
            </div>
            <h1 class="font-sora text-2xl font-black text-slate-900 tracking-tight">
                Edit Submission Form: {{ $form->program?->name ?? $form->title }}
            </h1>
            <p class="text-xs text-slate-600 mt-1">
                Program Code: <strong class="font-mono text-[#be1e2d]">{{ $form->program?->code }}</strong> • Public URL: <a href="{{ $form->public_url }}" target="_blank" class="underline text-blue-600 font-mono">{{ $form->public_url }}</a>
            </p>
        </div>
    </div>

    <!-- Edit Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-2xs"
         x-data="{
             allowText: {{ $form->allow_text ? 'true' : 'false' }},
             allowImage: {{ $form->allow_image ? 'true' : 'false' }},
             allowVideo: {{ $form->allow_video ? 'true' : 'false' }}
         }">

        <form action="{{ route('admin.online-forms.update', $form->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Form Title -->
            <div>
                <label for="title" class="block text-xs font-mono uppercase tracking-wider font-bold text-slate-700 mb-1.5">
                    Form Display Title <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="title" 
                       id="title" 
                       required 
                       value="{{ old('title', $form->title) }}" 
                       class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs sm:text-sm font-sans focus:outline-none focus:ring-2 focus:ring-[#be1e2d]">
                @error('title')
                    <p class="text-xs text-red-500 mt-1 font-mono">{{ $message }}</p>
                @enderror
            </div>

            <!-- Instructions -->
            <div>
                <label for="instructions" class="block text-xs font-mono uppercase tracking-wider font-bold text-slate-700 mb-1.5">
                    Rules & Instructions for Students
                </label>
                <textarea name="instructions" 
                          id="instructions" 
                          rows="4" 
                          class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs sm:text-sm font-sans focus:outline-none focus:ring-2 focus:ring-[#be1e2d]">{{ old('instructions', $form->instructions) }}</textarea>
            </div>

            <div class="border-t border-slate-100 pt-6">
                <h3 class="font-sora text-sm font-bold text-slate-900 mb-1">
                    Allowed Submission Types
                </h3>

                <div class="space-y-4">
                    
                    <!-- 1. Text -->
                    <div class="p-4 sm:p-5 rounded-2xl border transition-colors"
                         :class="allowText ? 'bg-blue-50/40 border-blue-200' : 'bg-slate-50 border-slate-200'">
                        <div class="flex items-center justify-between mb-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="allow_text" value="1" x-model="allowText" class="w-5 h-5 rounded text-[#be1e2d]">
                                <span class="font-bold text-slate-900 text-sm">Text Content Submission</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-mono text-slate-600" x-show="allowText">
                                <input type="checkbox" name="is_text_required" value="1" {{ $form->is_text_required ? 'checked' : '' }} class="rounded text-[#be1e2d]">
                                <span>Required</span>
                            </label>
                        </div>
                        <div x-show="allowText" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <div>
                                <label class="block text-[11px] font-mono text-slate-600 mb-1">Field Label</label>
                                <input type="text" name="text_label" value="{{ old('text_label', $form->text_label) }}" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-mono text-slate-600 mb-1">Placeholder Hint</label>
                                <input type="text" name="text_placeholder" value="{{ old('text_placeholder', $form->text_placeholder) }}" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs">
                            </div>
                        </div>
                    </div>

                    <!-- 2. Image -->
                    <div class="p-4 sm:p-5 rounded-2xl border transition-colors"
                         :class="allowImage ? 'bg-emerald-50/40 border-emerald-200' : 'bg-slate-50 border-slate-200'">
                        <div class="flex items-center justify-between mb-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="allow_image" value="1" x-model="allowImage" class="w-5 h-5 rounded text-[#be1e2d]">
                                <span class="font-bold text-slate-900 text-sm">Photo / Document Upload</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-mono text-slate-600" x-show="allowImage">
                                <input type="checkbox" name="is_image_required" value="1" {{ $form->is_image_required ? 'checked' : '' }} class="rounded text-[#be1e2d]">
                                <span>Required</span>
                            </label>
                        </div>
                        <div x-show="allowImage" class="pt-2">
                            <label class="block text-[11px] font-mono text-slate-600 mb-1">Upload Field Label</label>
                            <input type="text" name="image_label" value="{{ old('image_label', $form->image_label) }}" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs">
                        </div>
                    </div>

                    <!-- 3. Video -->
                    <div class="p-4 sm:p-5 rounded-2xl border transition-colors"
                         :class="allowVideo ? 'bg-purple-50/40 border-purple-200' : 'bg-slate-50 border-slate-200'">
                        <div class="flex items-center justify-between mb-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="allow_video" value="1" x-model="allowVideo" class="w-5 h-5 rounded text-[#be1e2d]">
                                <span class="font-bold text-slate-900 text-sm">Video Submission</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-mono text-slate-600" x-show="allowVideo">
                                <input type="checkbox" name="is_video_required" value="1" {{ $form->is_video_required ? 'checked' : '' }} class="rounded text-[#be1e2d]">
                                <span>Required</span>
                            </label>
                        </div>
                        <div x-show="allowVideo" class="pt-2">
                            <label class="block text-[11px] font-mono text-slate-600 mb-1">Video Field Label</label>
                            <input type="text" name="video_label" value="{{ old('video_label', $form->video_label) }}" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs">
                        </div>
                    </div>

                </div>
            </div>

            <!-- Status & Deadline -->
            <div class="border-t border-slate-100 pt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-900 text-xs block">Form Status</span>
                        <span class="text-[11px] text-slate-500">Accept candidate submissions</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_open" value="1" {{ $form->is_open ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-mono uppercase tracking-wider font-bold text-slate-700 mb-1">
                        Deadline
                    </label>
                    <input type="datetime-local" 
                           name="closes_at" 
                           value="{{ $form->closes_at ? $form->closes_at->format('Y-m-d\TH:i') : '' }}" 
                           class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-[#be1e2d]">
                </div>
            </div>

            <!-- Submit -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.online-forms.index') }}" 
                   class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold uppercase transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 rounded-2xl bg-[#be1e2d] hover:bg-[#a01824] text-white text-xs font-bold uppercase tracking-wider shadow-md shadow-red-600/20 transition cursor-pointer">
                    Update Form (അപ്‌ഡേറ്റ് ചെയ്യുക)
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
