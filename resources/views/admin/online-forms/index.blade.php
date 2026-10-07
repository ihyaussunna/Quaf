@extends('layouts.admin', ['title' => 'Online Submission Forms | QUAF'])

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono uppercase tracking-widest bg-emerald-100 text-emerald-800 font-bold border border-emerald-200">
                    PROGRAM FORMS
                </span>
                <span class="text-xs font-mono text-slate-400">QUAF Digital Desk</span>
            </div>
            <h1 class="font-sora text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Online Submission Forms (ഓൺലൈൻ സബ്മിഷൻ ഫോമുകൾ)
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 mt-1">
                ഓൺലൈനായി രചനകൾ, ഫോട്ടോകൾ, വീഡിയോകൾ എന്നിവ സ്വീകരിക്കുന്ന മത്സരങ്ങൾക്ക് ഫോമുകൾ ക്രമീകരിക്കുക. ഫോം സെറ്റ് ചെയ്യുന്നതോടെ അതത് മത്സരങ്ങളുടെ ഗ്രീൻ റൂമിൽ ക്യുആർ കോഡ് സ്വയം ലഭ്യമാകും.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.online-forms.create') }}" 
               class="px-5 py-2.5 rounded-2xl bg-[#be1e2d] hover:bg-[#a01824] text-white font-bold text-xs uppercase tracking-wider flex items-center gap-2 shadow-md shadow-red-600/20 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Create New Form</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
        <form action="{{ route('admin.online-forms.index') }}" method="GET" class="w-full sm:w-80 relative">
            <input type="text" 
                   name="search" 
                   value="{{ $search }}" 
                   placeholder="Search program, code, or title..." 
                   class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-[#be1e2d]">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </form>

        <div class="text-xs font-mono text-slate-500">
            Total Forms: <strong class="text-slate-900">{{ $forms->total() }}</strong>
        </div>
    </div>

    <!-- Forms List Table -->
    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-2xs">
        @if($forms->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3.5 px-4">Program</th>
                            <th class="py-3.5 px-4">Submission Types</th>
                            <th class="py-3.5 px-4 text-center">Submissions</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-center">QR Code</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($forms as $form)
                            <tr class="hover:bg-slate-50/70 transition">
                                <!-- Program Info -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded-md bg-[#be1e2d]/10 text-[#be1e2d] font-bold text-[10px]">
                                            {{ $form->program?->code ?? 'N/A' }}
                                        </span>
                                        <span class="text-[11px] font-sans font-bold text-slate-900">
                                            {{ $form->program?->name ?? $form->title }}
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 flex items-center gap-2">
                                        <span>Category: {{ $form->program?->category?->name ?? 'General' }}</span>
                                        <span>•</span>
                                        <span>Slug: /submit/{{ $form->slug }}</span>
                                    </div>
                                </td>

                                <!-- Types Enabled -->
                                <td class="py-4 px-4">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if($form->allow_text)
                                            <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-800 border border-blue-200 text-[10px] font-bold">
                                                Text {{ $form->is_text_required ? '*' : '' }}
                                            </span>
                                        @endif
                                        @if($form->allow_image)
                                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold">
                                                Photo {{ $form->is_image_required ? '*' : '' }}
                                            </span>
                                        @endif
                                        @if($form->allow_video)
                                            <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-800 border border-purple-200 text-[10px] font-bold">
                                                Video {{ $form->is_video_required ? '*' : '' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Submissions Count -->
                                <td class="py-4 px-4 text-center">
                                    <a href="{{ route('admin.online-forms.submissions', $form->id) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold transition">
                                        <span>{{ $form->submissions_count }}</span>
                                        <span class="text-[10px] font-normal text-slate-500">entries</span>
                                    </a>
                                </td>

                                <!-- Status Toggle -->
                                <td class="py-4 px-4 text-center">
                                    <form action="{{ route('admin.online-forms.toggle', $form->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase transition cursor-pointer {{ $form->isOpenForSubmissions() ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                            {{ $form->isOpenForSubmissions() ? 'OPEN' : 'CLOSED' }}
                                        </button>
                                    </form>
                                </td>

                                <!-- QR Code Link -->
                                <td class="py-4 px-4 text-center">
                                    <a href="{{ route('admin.online-forms.qr', $form->id) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition" 
                                       title="View & Project QR Code">
                                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                        <span>QR</span>
                                    </a>
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ $form->public_url }}" 
                                           target="_blank" 
                                           class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 transition" 
                                           title="Preview Public Form">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>

                                        <a href="{{ route('admin.online-forms.edit', $form->id) }}" 
                                           class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-blue-600 hover:text-blue-900 transition" 
                                           title="Edit Form Settings">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>

                                        <form action="{{ route('admin.online-forms.destroy', $form->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('ഈ ഓൺലൈൻ ഫോം നീക്കം ചെയ്യാൻ ഉറപ്പാണോ? ഇതിലേക്ക് ലഭിച്ച സബ്മിഷനുകളും ഇല്ലാതാകുന്നതാണ്.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-2 rounded-xl bg-slate-100 hover:bg-red-50 text-red-600 hover:text-red-800 transition cursor-pointer" 
                                                    title="Delete Form">
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
                {{ $forms->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="font-sora text-base font-bold text-slate-800 mb-1">ഓൺലൈൻ സബ്മിഷൻ ഫോമുകൾ ക്രമീകരിച്ചിട്ടില്ല</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto mb-5">
                    രചനകൾ സ്വീകരിക്കേണ്ട മത്സരങ്ങൾക്കായി മുകളിലെ "Create New Form" ബട്ടൺ ഉപയോഗിച്ച് പുതിയ ഫോം തയ്യാറാക്കാവുന്നതാണ്.
                </p>
                <a href="{{ route('admin.online-forms.create') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#be1e2d] text-white text-xs font-bold uppercase tracking-wider">
                    <span>Create First Form</span>
                </a>
            </div>
        @endif
    </div>

</div>
@endsection
