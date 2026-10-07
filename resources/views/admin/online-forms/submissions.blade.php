@extends('layouts.admin', ['title' => 'Submissions: ' . ($form->program?->name ?? $form->title) . ' | QUAF 9.0'])

@section('content')
<div class="max-w-7xl mx-auto space-y-6"
     x-data="{
         viewingModal: false,
         modalCodeLetter: '',
         modalStudentInfo: '',
         modalText: '',
         modalFileUrl: '',
         modalFileName: '',
         modalVideoUrl: '',
         modalTimestamp: '',

         openModal(code, info, text, fileUrl, fileName, videoUrl, time) {
             this.modalCodeLetter = code;
             this.modalStudentInfo = info;
             this.modalText = text;
             this.modalFileUrl = fileUrl;
             this.modalFileName = fileName;
             this.modalVideoUrl = videoUrl;
             this.modalTimestamp = time;
             this.viewingModal = true;
         }
     }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.online-forms.index') }}" class="text-xs font-mono text-slate-500 hover:text-[#be1e2d] transition">
                    &larr; Back to Forms
                </a>
            </div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="px-2.5 py-0.5 rounded-md bg-[#be1e2d] text-white font-mono font-bold text-xs uppercase">
                    {{ $form->program?->code ?? 'PRG' }}
                </span>
                <h1 class="font-sora text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    {{ $form->program?->name ?? $form->title }}
                </h1>
            </div>
            <p class="text-xs text-slate-600 mt-1">
                Category: <strong>{{ $form->program?->category?->name ?? 'General' }}</strong> • Zone: <strong>{{ $form->program?->zone?->name ?? $form->program?->eligibility ?? 'All' }}</strong> • Total Submissions: <strong class="text-emerald-700 font-mono">{{ $submissions->total() }}</strong>
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.online-forms.pdf', $form->id) }}" 
               target="_blank" 
               class="px-4 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white text-xs font-mono font-bold flex items-center gap-1.5 transition shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print / Download PDF</span>
            </a>

            <a href="{{ route('admin.online-forms.qr', $form->id) }}" 
               class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-mono font-bold flex items-center gap-1.5 transition">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span>View QR Code</span>
            </a>

            <a href="{{ $form->public_url }}" 
               target="_blank" 
               class="px-4 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-mono font-bold flex items-center gap-1.5 transition">
                <span>Public Form</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>

    <!-- Submissions Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-2xs">
        @if($submissions->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3.5 px-4 text-center">Code Letter</th>
                            <th class="py-3.5 px-4">Student Details</th>
                            <th class="py-3.5 px-4">Submitted Content</th>
                            <th class="py-3.5 px-4">File / Video</th>
                            <th class="py-3.5 px-4">Submitted At</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($submissions as $s)
                            @php
                                $studentInfo = ($s->student_name ?: 'Candidate') . ' (' . ($s->chest_number ? 'Chest: ' . $s->chest_number : 'No Chest') . ' • ' . ($s->group?->name ?? 'House') . ')';
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <!-- Code Letter -->
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-slate-900 text-amber-300 font-sora font-black text-base shadow-sm">
                                        {{ $s->code_letter }}
                                    </span>
                                </td>

                                <!-- Candidate Info -->
                                <td class="py-4 px-4">
                                    <div class="font-sans font-bold text-slate-900 text-sm">
                                        {{ $s->student_name ?: 'Candidate' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                        @if($s->chest_number)
                                            <span class="font-mono font-bold text-slate-700">Chest: {{ $s->chest_number }}</span>
                                            <span>•</span>
                                        @endif
                                        <span class="text-[#be1e2d] font-bold">{{ $s->group?->name ?? 'House' }}</span>
                                    </div>
                                </td>

                                <!-- Content Excerpt -->
                                <td class="py-4 px-4 max-w-xs">
                                    @if($s->text_content)
                                        <p class="text-xs text-slate-700 line-clamp-2 leading-relaxed">
                                            {{ $s->text_content }}
                                        </p>
                                        <button type="button" 
                                                @click="openModal('{{ $s->code_letter }}', '{{ addslashes($studentInfo) }}', '{{ addslashes($s->text_content) }}', '{{ $s->file_url }}', '{{ addslashes($s->file_name ?? '') }}', '{{ addslashes($s->video_url ?? '') }}', '{{ $s->submitted_at?->format('d M Y, h:i A') }}')"
                                                class="text-[11px] text-[#be1e2d] hover:underline font-bold mt-1 inline-block cursor-pointer">
                                            View Full Text &rarr;
                                        </button>
                                    @else
                                        <span class="text-slate-400 italic">No text content</span>
                                    @endif
                                </td>

                                <!-- File / Video -->
                                <td class="py-4 px-4">
                                    <div class="flex flex-col gap-1.5">
                                        @if($s->file_path)
                                            <a href="{{ $s->file_url }}" 
                                               target="_blank" 
                                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-bold hover:bg-emerald-100 transition w-fit">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                <span>{{ Str::limit($s->file_name ?: 'View Attachment', 18) }}</span>
                                                @if($s->formatted_file_size)
                                                    <span class="text-[9px] text-slate-500">({{ $s->formatted_file_size }})</span>
                                                @endif
                                            </a>
                                        @endif

                                        @if($s->video_url)
                                            <a href="{{ $s->video_url }}" 
                                               target="_blank" 
                                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-purple-50 text-purple-800 border border-purple-200 text-[11px] font-bold hover:bg-purple-100 transition w-fit">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Watch Video Link</span>
                                            </a>
                                        @endif

                                        @if(!$s->file_path && !$s->video_url)
                                            <span class="text-slate-400 italic">None</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Timestamp -->
                                <td class="py-4 px-4 text-slate-500 text-[11px]">
                                    {{ $s->submitted_at?->format('d M, h:i A') ?? $s->created_at->format('d M, h:i A') }}
                                </td>

                                <!-- Delete Action -->
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" 
                                                @click="openModal('{{ $s->code_letter }}', '{{ addslashes($studentInfo) }}', '{{ addslashes($s->text_content ?? '') }}', '{{ $s->file_url }}', '{{ addslashes($s->file_name ?? '') }}', '{{ addslashes($s->video_url ?? '') }}', '{{ $s->submitted_at?->format('d M Y, h:i A') }}')"
                                                class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition" 
                                                title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>

                                        <a href="{{ route('admin.online-forms.submissions.pdf', $s->id) }}" 
                                           target="_blank" 
                                           class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition" 
                                           title="Print Candidate PDF">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>

                                        <form action="{{ route('admin.online-forms.submissions.destroy', $s->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('കോഡ് {{ $s->code_letter }}-ന്റെ ഈ സബ്മിഷൻ നീക്കം ചെയ്യാൻ ഉറപ്പാണോ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-2 rounded-xl bg-slate-100 hover:bg-red-50 text-red-600 transition cursor-pointer" 
                                                    title="Delete Submission">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $submissions->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="font-sora text-base font-bold text-slate-800 mb-1">സബ്മിഷനുകൾ ലഭിച്ചിട്ടില്ല</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    വിദ്യാർത്ഥികൾ ക്യുആർ കോഡ് സ്കാൻ ചെയ്ത് രചനകൾ സമർപ്പിക്കുമ്പോൾ ഇവിടെ തത്സമയം ദൃശ്യമാകും.
                </p>
            </div>
        @endif
    </div>

    <!-- Details Modal -->
    <div x-show="viewingModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;"
         @keydown.escape.window="viewingModal = false">
        
        <div class="fixed inset-0" @click="viewingModal = false"></div>

        <div class="relative w-full max-w-2xl bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-200 z-10 max-h-[90vh] overflow-y-auto">
            <button @click="viewingModal = false" class="absolute top-5 right-5 p-2 rounded-full hover:bg-slate-100 text-slate-500 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-slate-900 text-amber-300 font-sora font-black text-xl flex items-center justify-center shadow-sm">
                    <span x-text="modalCodeLetter"></span>
                </div>
                <div>
                    <h3 class="font-sora text-lg font-bold text-slate-900" x-text="modalStudentInfo"></h3>
                    <p class="text-xs font-mono text-slate-400" x-text="'Submitted: ' + modalTimestamp"></p>
                </div>
            </div>

            <!-- Text Content -->
            <div x-show="modalText" class="mt-4 pt-4 border-t border-slate-100">
                <h4 class="text-xs font-mono uppercase font-bold text-slate-500 mb-2">Written Content:</h4>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 text-sm whitespace-pre-wrap font-sans leading-relaxed max-h-64 overflow-y-auto" 
                     x-text="modalText"></div>
            </div>

            <!-- File Attachment -->
            <div x-show="modalFileUrl" class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-mono uppercase font-bold text-slate-500 mb-1">Attached File:</h4>
                    <span class="text-xs font-sans text-slate-800" x-text="modalFileName"></span>
                </div>
                <a :href="modalFileUrl" 
                   target="_blank" 
                   class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-mono font-bold text-xs uppercase flex items-center gap-1.5 transition">
                    <span>Download / Open</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>

            <!-- Video -->
            <div x-show="modalVideoUrl" class="mt-4 pt-4 border-t border-slate-100">
                <h4 class="text-xs font-mono uppercase font-bold text-slate-500 mb-2">Video Link:</h4>
                <a :href="modalVideoUrl" 
                   target="_blank" 
                   class="text-xs font-mono text-blue-600 underline break-all" 
                   x-text="modalVideoUrl"></a>
            </div>

        </div>
    </div>

</div>
@endsection
