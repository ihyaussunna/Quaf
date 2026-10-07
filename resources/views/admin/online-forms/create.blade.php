@extends('layouts.admin', ['title' => 'Create Online Submission Form | QUAF 9.0'])

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
                Create Online Submission Form (പുതിയ സബ്മിഷൻ ഫോം)
            </h1>
            <p class="text-xs text-slate-600 mt-1">
                മത്സരം തിരഞ്ഞെടുത്ത് വിദ്യാർത്ഥികൾക്ക് അവരുടെ രചനകൾ/ഫോട്ടോ/വീഡിയോ കോഡ് ലെറ്റർ നൽകി സമർപ്പിക്കാനുള്ള ഫോം ക്രമീകരിക്കുക.
            </p>
        </div>
    </div>

    <!-- Create Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-2xs"
         x-data="{
             selectedProg: '{{ $selectedProgram?->id ?? '' }}',
             programs: {{ json_encode($programs->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'code' => $p->code, 'category' => $p->category?->name ?? 'General', 'zone' => $p->zone?->name ?? $p->eligibility ?? 'All'])) }},
             formTitle: '{{ $selectedProgram?->name ?? '' }}',
             allowText: true,
             allowImage: false,
             allowVideo: false,
             
             onProgramChange() {
                 const p = this.programs.find(item => item.id == this.selectedProg);
                 if (p && !this.formTitle) {
                     this.formTitle = p.name + ' — Submission Form';
                 }
             }
         }">

        <form action="{{ route('admin.online-forms.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Program Selector -->
            <div>
                <label for="program_id" class="block text-xs font-mono uppercase tracking-wider font-bold text-slate-700 mb-1.5">
                    Select Program (മത്സരം തിരഞ്ഞെടുക്കുക) <span class="text-red-500">*</span>
                </label>
                <select name="program_id" 
                        id="program_id" 
                        required 
                        x-model="selectedProg"
                        @change="onProgramChange()"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs sm:text-sm font-sans focus:outline-none focus:ring-2 focus:ring-[#be1e2d]">
                    <option value="">-- Choose a Competition / Program --</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}" {{ ($selectedProgram?->id == $p->id) ? 'selected' : '' }}>
                            [{{ $p->code }}] {{ $p->name }} ({{ $p->category?->name ?? 'General' }} • {{ $p->zone?->name ?? $p->eligibility ?? 'All' }})
                        </option>
                    @endforeach
                </select>
                @error('program_id')
                    <p class="text-xs text-red-500 mt-1 font-mono">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Title -->
            <div>
                <label for="title" class="block text-xs font-mono uppercase tracking-wider font-bold text-slate-700 mb-1.5">
                    Form Display Title (ഫോമിന്റെ പേര്) <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="title" 
                       id="title" 
                       required 
                       x-model="formTitle"
                       placeholder="e.g. Malayalam Essay Writing Submission Desk" 
                       class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs sm:text-sm font-sans focus:outline-none focus:ring-2 focus:ring-[#be1e2d]">
                @error('title')
                    <p class="text-xs text-red-500 mt-1 font-mono">{{ $message }}</p>
                @enderror
            </div>

            <!-- Instructions / Guidelines -->
            <div>
                <label for="instructions" class="block text-xs font-mono uppercase tracking-wider font-bold text-slate-700 mb-1.5">
                    Rules & Instructions for Students (വിദ്യാർത്ഥികൾക്കുള്ള നിർദ്ദേശങ്ങൾ / വിഷയം)
                </label>
                <textarea name="instructions" 
                          id="instructions" 
                          rows="4" 
                          placeholder="വിദ്യാർത്ഥികൾ ശ്രദ്ധിക്കേണ്ട നിർദ്ദേശങ്ങൾ, വിഷയം, പേജ് പരിധി, ഫോർമാറ്റ് എന്നിവ ഇവിടെ രേഖപ്പെടുത്താം..." 
                          class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs sm:text-sm font-sans focus:outline-none focus:ring-2 focus:ring-[#be1e2d]">{{ old('instructions') }}</textarea>
                <p class="text-[11px] text-slate-500 mt-1 font-mono">
                    This will be displayed on the student submission form above the entry fields.
                </p>
            </div>

            <div class="border-t border-slate-100 pt-6">
                <h3 class="font-sora text-sm font-bold text-slate-900 mb-1">
                    Allowed Submission Types (ഏതൊക്കെ രൂപത്തിലുള്ള രചനകളാണ് സ്വീകരിക്കേണ്ടത്?)
                </h3>
                <p class="text-xs text-slate-500 mb-5">
                    മത്സരത്തിന് അനുയോജ്യമായ ഓപ്ഷനുകൾ താഴെ നിന്നും തിരഞ്ഞെടുക്കുക:
                </p>

                <div class="space-y-4">
                    
                    <!-- 1. Text Submission Option -->
                    <div class="p-4 sm:p-5 rounded-2xl border transition-colors"
                         :class="allowText ? 'bg-blue-50/40 border-blue-200' : 'bg-slate-50 border-slate-200'">
                        <div class="flex items-center justify-between mb-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" 
                                       name="allow_text" 
                                       value="1" 
                                       x-model="allowText" 
                                       class="w-5 h-5 rounded text-[#be1e2d] focus:ring-[#be1e2d]">
                                <span class="font-bold text-slate-900 text-sm">
                                    Text Content Submission (രചന / ഉപന്യാസം / കവിത ഉള്ളടക്കം ടൈപ്പ് ചെയ്യാൻ)
                                </span>
                            </label>

                            <label class="flex items-center gap-2 text-xs font-mono text-slate-600" x-show="allowText">
                                <input type="checkbox" name="is_text_required" value="1" class="rounded text-[#be1e2d]">
                                <span>Required (നിർബന്ധം)</span>
                            </label>
                        </div>

                        <div x-show="allowText" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <div>
                                <label class="block text-[11px] font-mono text-slate-600 mb-1">Field Label</label>
                                <input type="text" name="text_label" value="രചനയുടെ ഉള്ളടക്കം (Text Content)" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-mono text-slate-600 mb-1">Placeholder Hint</label>
                                <input type="text" name="text_placeholder" value="നിങ്ങളുടെ രചന ഇവിടെ ടൈപ്പ് ചെയ്യുക അല്ലെങ്കിൽ പേസ്റ്റ് ചെയ്യുക..." class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs">
                            </div>
                        </div>
                    </div>

                    <!-- 2. Photo / Image / Document Upload Option -->
                    <div class="p-4 sm:p-5 rounded-2xl border transition-colors"
                         :class="allowImage ? 'bg-emerald-50/40 border-emerald-200' : 'bg-slate-50 border-slate-200'">
                        <div class="flex items-center justify-between mb-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" 
                                       name="allow_image" 
                                       value="1" 
                                       x-model="allowImage" 
                                       class="w-5 h-5 rounded text-[#be1e2d] focus:ring-[#be1e2d]">
                                <span class="font-bold text-slate-900 text-sm">
                                    Photo / Drawing / Document Upload (ഫോട്ടോ / ഡ്രോയിംഗ് / PDF ഫയൽ അപ്‌ലോഡ്)
                                </span>
                            </label>

                            <label class="flex items-center gap-2 text-xs font-mono text-slate-600" x-show="allowImage">
                                <input type="checkbox" name="is_image_required" value="1" class="rounded text-[#be1e2d]">
                                <span>Required (നിർബന്ധം)</span>
                            </label>
                        </div>

                        <div x-show="allowImage" class="pt-2">
                            <label class="block text-[11px] font-mono text-slate-600 mb-1">Upload Field Label</label>
                            <input type="text" name="image_label" value="ഫോട്ടോ അല്ലെങ്കിൽ PDF അപ്‌ലോഡ് ചെയ്യുക (Upload Image or PDF)" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs">
                        </div>
                    </div>

                    <!-- 3. Video Option -->
                    <div class="p-4 sm:p-5 rounded-2xl border transition-colors"
                         :class="allowVideo ? 'bg-purple-50/40 border-purple-200' : 'bg-slate-50 border-slate-200'">
                        <div class="flex items-center justify-between mb-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" 
                                       name="allow_video" 
                                       value="1" 
                                       x-model="allowVideo" 
                                       class="w-5 h-5 rounded text-[#be1e2d] focus:ring-[#be1e2d]">
                                <span class="font-bold text-slate-900 text-sm">
                                    Video Submission (വീഡിയോ ഫയൽ അല്ലെങ്കിൽ ഡ്രൈവ്/യൂട്യൂബ് ലിങ്ക്)
                                </span>
                            </label>

                            <label class="flex items-center gap-2 text-xs font-mono text-slate-600" x-show="allowVideo">
                                <input type="checkbox" name="is_video_required" value="1" class="rounded text-[#be1e2d]">
                                <span>Required (നിർബന്ധം)</span>
                            </label>
                        </div>

                        <div x-show="allowVideo" class="pt-2">
                            <label class="block text-[11px] font-mono text-slate-600 mb-1">Video Field Label</label>
                            <input type="text" name="video_label" value="വീഡിയോ ഫയൽ അല്ലെങ്കിൽ ഡ്രൈവ് ലിങ്ക് (Video File or Link)" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs">
                        </div>
                    </div>

                </div>
            </div>

            <!-- Form Status & Closing Time -->
            <div class="border-t border-slate-100 pt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-900 text-xs block">Form Status (സബ്മിഷൻ സ്റ്റാറ്റസ്)</span>
                        <span class="text-[11px] text-slate-500">Open now to receive candidate entries</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_open" value="1" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-mono uppercase tracking-wider font-bold text-slate-700 mb-1">
                        Deadline (അവസാന തീയതി & സമയം - ഓപ്ഷണൽ)
                    </label>
                    <input type="datetime-local" 
                           name="closes_at" 
                           class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-[#be1e2d]">
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.online-forms.index') }}" 
                   class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold uppercase transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 rounded-2xl bg-[#be1e2d] hover:bg-[#a01824] text-white text-xs font-bold uppercase tracking-wider shadow-md shadow-red-600/20 transition cursor-pointer">
                    Save & Create Form (ഫോം തയ്യാറാക്കുക)
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
