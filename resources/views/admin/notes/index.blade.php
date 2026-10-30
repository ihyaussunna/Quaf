@extends('layouts.admin', ['title' => '7012450418# — Notes & Pastepad'])

@section('content')
<div class="space-y-6 max-w-7xl mx-auto font-sora" x-data="notesHubState()">

    <!-- Header Banner -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-mono font-bold bg-[#be1e2d] text-white tracking-widest uppercase shadow-xs">
                    7012450418#
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-900 text-white font-mono">
                    Admin Clipboard & Scratchpad
                </span>
                <span class="text-xs text-slate-500 font-mono">Live Sync</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>7012450418#</span>
                <span class="text-slate-400 font-normal text-xl sm:text-2xl">—</span>
                <span class="text-slate-800 text-lg sm:text-2xl font-bold">Admin Paste Text & Notes</span>
            </h1>
            <p class="text-xs text-slate-600 mt-1 max-w-2xl font-mono leading-relaxed">
                ഏത് ടെക്സ്റ്റും നേരിട്ട് പേസ്റ്റ് ചെയ്യാനും, പ്രധാന അറിയിപ്പുകളും റിസൾട്ടുകളും പ്രോഗ്രാം വിവരങ്ങളും നോട്സ് ആയി രേഖപ്പെടുത്താനുമുള്ള അഡ്മിൻ സ്ക്രാച്ച്പാഡ്.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <button type="button" @click="focusEditor()" class="px-4 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-mono font-bold text-xs uppercase tracking-wider transition-colors shadow-sm flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ New Note / Paste</span>
            </button>

            <button type="button" @click="pasteFromClipboard()" class="px-3.5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-xs uppercase tracking-wider transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Paste from Clipboard</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-[10px] uppercase text-slate-500 font-bold">Total Stored Notes</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalCount }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">In 7012450418# Board</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-amber-200 bg-amber-50/20 shadow-2xs">
            <div class="text-[10px] uppercase text-amber-800 font-bold">Pinned Notes</div>
            <div class="text-2xl font-black text-amber-900 mt-1">{{ $pinnedCount }}</div>
            <div class="text-[10px] text-amber-700 mt-0.5">Prioritized at top</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-emerald-200 bg-emerald-50/20 shadow-2xs">
            <div class="text-[10px] uppercase text-emerald-800 font-bold">Direct URL Access</div>
            <div class="text-xs font-bold text-emerald-900 mt-2 truncate">/7012450418#</div>
            <div class="text-[10px] text-emerald-700 mt-0.5">Instant browser link</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-[10px] uppercase text-slate-500 font-bold">Access Security</div>
            <div class="text-xs font-bold text-slate-800 mt-2">Admin / Super Admin</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Protected login session</div>
        </div>
    </div>

    <!-- Main Workspace: Left Editor, Right Saved Notes List -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT COLUMN: Live Paste Text & Note Creator -->
        <div class="lg:col-span-6 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" id="note-editor-card">
                <div class="bg-slate-900 text-white p-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <h2 class="text-sm font-bold tracking-wider uppercase font-mono">Type Notes / Paste Text Here</h2>
                    </div>
                    <span class="text-[11px] font-mono text-slate-400">7012450418#</span>
                </div>

                <form method="POST" action="{{ route('admin.notes.store') }}" class="p-5 space-y-4" @submit="isSaving = true">
                    @csrf

                    <!-- Title & Category Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-[10px] font-mono uppercase text-slate-500 font-bold mb-1">
                                Note Title <span class="text-slate-400 font-normal">(Optional)</span>
                            </label>
                            <input type="text" name="title" x-model="draftTitle" placeholder="e.g. Stage 1 Delay, WhatsApp Announcement, Result Summary..."
                                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d] font-sans">
                        </div>

                        <div>
                            <label class="block text-[10px] font-mono uppercase text-slate-500 font-bold mb-1">Category</label>
                            <select name="category" x-model="draftCategory" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d] font-mono">
                                <option value="general">General</option>
                                <option value="announcement">Announcement</option>
                                <option value="result">Result / Standings</option>
                                <option value="program">Program / Duty</option>
                                <option value="schedule">Schedule / Time</option>
                                <option value="urgent">Urgent / Priority</option>
                            </select>
                        </div>
                    </div>

                    <!-- Note Content Area -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[10px] font-mono uppercase text-slate-700 font-bold">
                                Note Content / Pasted Text <span class="text-red-500">*</span>
                            </label>

                            <!-- Live Text Formatting Quick Actions -->
                            <div class="flex items-center gap-1.5 text-[11px] font-mono">
                                <button type="button" @click="transformText('upper')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="ALL UPPERCASE">AA</button>
                                <button type="button" @click="transformText('lower')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="all lowercase">aa</button>
                                <button type="button" @click="transformText('trim_lines')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="Trim blank lines">Trim</button>
                                <button type="button" @click="clearEditor()" class="px-2 py-0.5 rounded bg-rose-50 hover:bg-rose-100 text-rose-700 transition" title="Clear Text">Clear</button>
                            </div>
                        </div>

                        <div class="relative">
                            <textarea name="content" x-ref="contentInput" x-model="draftContent" rows="12" required
                                      placeholder="Paste any text here (WhatsApp messages, schedule changes, judge notes, participant details, points table, announcements...) or type quick notes..."
                                      class="w-full bg-slate-50/70 border border-slate-300 rounded-xl p-4 text-xs font-mono text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-all leading-relaxed placeholder:font-sans placeholder:text-slate-400"></textarea>
                        </div>

                        <!-- Live Text Metrics -->
                        <div class="flex items-center justify-between mt-1 text-[11px] font-mono text-slate-500">
                            <div class="flex items-center gap-3">
                                <span>Chars: <strong class="text-slate-800" x-text="charCount"></strong></span>
                                <span>Words: <strong class="text-slate-800" x-text="wordCount"></strong></span>
                                <span>Lines: <strong class="text-slate-800" x-text="lineCount"></strong></span>
                            </div>
                            <span x-show="draftContent.length > 0" class="text-emerald-600 font-bold text-[10px]">
                                Ready to Save
                            </span>
                        </div>
                    </div>

                    <!-- Card Style & Pin Options -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100">
                        <div class="flex items-center gap-4">
                            <!-- Color Badge Picker -->
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] font-mono uppercase text-slate-500 font-bold mr-1">Color:</span>
                                <label class="cursor-pointer">
                                    <input type="radio" name="color" value="slate" x-model="draftColor" class="sr-only">
                                    <span class="w-5 h-5 rounded-full bg-slate-700 block border-2" :class="draftColor === 'slate' ? 'border-amber-400 scale-110' : 'border-transparent'"></span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="color" value="red" x-model="draftColor" class="sr-only">
                                    <span class="w-5 h-5 rounded-full bg-rose-600 block border-2" :class="draftColor === 'red' ? 'border-slate-900 scale-110' : 'border-transparent'"></span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="color" value="emerald" x-model="draftColor" class="sr-only">
                                    <span class="w-5 h-5 rounded-full bg-emerald-600 block border-2" :class="draftColor === 'emerald' ? 'border-slate-900 scale-110' : 'border-transparent'"></span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="color" value="amber" x-model="draftColor" class="sr-only">
                                    <span class="w-5 h-5 rounded-full bg-amber-500 block border-2" :class="draftColor === 'amber' ? 'border-slate-900 scale-110' : 'border-transparent'"></span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="color" value="blue" x-model="draftColor" class="sr-only">
                                    <span class="w-5 h-5 rounded-full bg-blue-600 block border-2" :class="draftColor === 'blue' ? 'border-slate-900 scale-110' : 'border-transparent'"></span>
                                </label>
                            </div>

                            <!-- Pin to top toggle -->
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-mono select-none">
                                <input type="checkbox" name="is_pinned" value="1" x-model="draftPinned" class="rounded border-slate-300 text-[#be1e2d] focus:ring-[#be1e2d]">
                                <span class="font-bold text-slate-700">Pin to Top</span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" :disabled="!draftContent.trim()"
                                class="px-6 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] disabled:opacity-50 disabled:cursor-not-allowed text-white font-mono font-bold text-xs uppercase tracking-wider transition-colors shadow-sm flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            <span>Save Note to 7012450418#</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Tips Box -->
            <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-4 text-xs font-mono text-amber-900 space-y-1">
                <div class="font-bold flex items-center gap-1.5 text-amber-950">
                    <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>7012450418# Tips</span>
                </div>
                <p class="text-[11px] text-amber-800 leading-relaxed">
                    നിങ്ങൾക്ക് ഏത് കമ്പ്യൂട്ടറിലോ ഫോണിലോ നിന്നും നേരിട്ട് <strong>/7012450418#</strong> എന്ന് ബ്രൗസറിൽ ടൈപ്പ് ചെയ്ത് ഈ ബോർഡിലേക്ക് പ്രവേശിക്കാം. പ്രധാനപ്പെട്ട വിവരങ്ങൾ "Pin to Top" നൽകി സൂക്ഷിക്കാം.
                </p>
            </div>
        </div>

        <!-- RIGHT COLUMN: Saved Notes Grid & Search -->
        <div class="lg:col-span-6 space-y-4">
            
            <!-- Filter & Search Toolbar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                <form method="GET" action="{{ route('admin.notes.index') }}" class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search stored notes, keywords, numbers..."
                                   class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:outline-none focus:border-[#be1e2d] font-mono">
                        </div>

                        <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-mono font-bold shrink-0">
                            Search
                        </button>

                        @if($search || $category || $onlyPinned)
                            <a href="{{ route('admin.notes.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-mono shrink-0">
                                Reset
                            </a>
                        @endif
                    </div>

                    <!-- Category Pills -->
                    <div class="flex flex-wrap items-center gap-1.5 text-xs font-mono">
                        <a href="{{ route('admin.notes.index', ['search' => $search]) }}"
                           class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition {{ empty($category) && !$onlyPinned ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                            All ({{ $totalCount }})
                        </a>
                        <a href="{{ route('admin.notes.index', ['pinned' => 1, 'search' => $search]) }}"
                           class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition {{ $onlyPinned ? 'bg-amber-500 text-white border-amber-500' : 'bg-slate-50 text-amber-800 border-amber-200 hover:bg-amber-50' }}">
                            Pinned ({{ $pinnedCount }})
                        </a>
                        @foreach($categories as $cat)
                            <a href="{{ route('admin.notes.index', ['category' => $cat, 'search' => $search]) }}"
                               class="px-2.5 py-1 rounded-lg border text-[11px] font-bold capitalize transition {{ $category === $cat ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                                {{ $cat }}
                            </a>
                        @endforeach
                    </div>
                </form>
            </div>

            <!-- Notes List -->
            @if($notes->isEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-xs">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">No Notes Found</h3>
                    <p class="text-xs text-slate-500 font-mono mt-1">
                        @if($search || $category || $onlyPinned)
                            No notes matched your filter criteria.
                        @else
                            Start typing on the left or paste text to save your first note.
                        @endif
                    </p>
                </div>
            @else
                <div class="space-y-3.5">
                    @foreach($notes as $note)
                        @php
                            $cardBorder = match($note->color) {
                                'red' => 'border-rose-300 bg-rose-50/10',
                                'emerald' => 'border-emerald-300 bg-emerald-50/10',
                                'amber' => 'border-amber-300 bg-amber-50/10',
                                'blue' => 'border-blue-300 bg-blue-50/10',
                                default => 'border-slate-200 bg-white',
                            };
                            $badgeColor = match($note->category) {
                                'urgent' => 'bg-rose-100 text-rose-800 border-rose-300',
                                'announcement' => 'bg-amber-100 text-amber-800 border-amber-300',
                                'result' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                'program' => 'bg-blue-100 text-blue-800 border-blue-300',
                                'schedule' => 'bg-purple-100 text-purple-800 border-purple-300',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                        @endphp

                        <div class="rounded-2xl border {{ $cardBorder }} p-4 sm:p-5 shadow-xs transition hover:shadow-md relative group"
                             x-data="{ expanded: false, copying: false, editModalOpen: false }">
                            
                            <!-- Note Header -->
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        @if($note->is_pinned)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                                <svg class="w-3 h-3 text-amber-700" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v2.586l1.707 1.707a1 1 0 01.293.707V10a1 1 0 01-.293.707L11 12.414V17a1 1 0 01-2 0v-4.586l-1.707-1.707A1 1 0 017 10V8a1 1 0 01.293-.707L9 5.586V3a1 1 0 011-1z"/></svg>
                                                <span>PINNED</span>
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase border {{ $badgeColor }}">
                                            {{ $note->category }}
                                        </span>
                                        <span class="text-[10px] font-mono text-slate-400">
                                            {{ $note->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    <h3 class="text-sm font-bold text-slate-900 font-sans break-words">
                                        {{ $note->title ?: 'Untitled Note' }}
                                    </h3>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-1 shrink-0">
                                    <!-- One-Click Copy -->
                                    <button type="button" @click="copyNoteContent({{ json_encode($note->content) }}, $el)"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition cursor-pointer"
                                            title="Copy note text to clipboard">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>

                                    <!-- Pin Toggle -->
                                    <form method="POST" action="{{ route('admin.notes.pin', $note) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 rounded-lg {{ $note->is_pinned ? 'text-amber-600 bg-amber-50' : 'text-slate-400 hover:text-slate-800 hover:bg-slate-100' }} transition cursor-pointer" title="{{ $note->is_pinned ? 'Unpin' : 'Pin to top' }}">
                                            <svg class="w-4 h-4" fill="{{ $note->is_pinned ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                        </button>
                                    </form>

                                    <!-- Edit Trigger -->
                                    <button type="button" @click="editModalOpen = true"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-blue-700 hover:bg-blue-50 transition cursor-pointer" title="Edit note">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    <!-- Delete -->
                                    <form method="POST" action="{{ route('admin.notes.destroy', $note) }}" class="inline" onsubmit="return confirm('Delete this note permanently from 7012450418#?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Delete note">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Note Content Body -->
                            <div class="text-xs font-mono text-slate-800 bg-white/80 rounded-xl p-3 border border-slate-200/80 leading-relaxed whitespace-pre-wrap break-words overflow-hidden"
                                 :class="expanded ? 'max-h-none' : 'max-h-36'">
                                {{ $note->content }}
                            </div>

                            <!-- Expand / Collapse if long -->
                            @if(strlen($note->content) > 220 || substr_count($note->content, "\n") > 5)
                                <div class="mt-2 text-right">
                                    <button type="button" @click="expanded = !expanded" class="text-[11px] font-mono text-[#be1e2d] hover:underline cursor-pointer">
                                        <span x-text="expanded ? 'Show Less' : 'Show Full Text &rarr;'"></span>
                                    </button>
                                </div>
                            @endif

                            <!-- Meta Footer -->
                            <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] font-mono text-slate-400">
                                <div>
                                    <span>By: <strong>{{ $note->creator?->name ?? 'Admin' }}</strong></span>
                                    @if($note->updated_by_user_id && $note->updated_by_user_id !== $note->created_by_user_id)
                                        <span> · Edited by: {{ $note->updater?->name }}</span>
                                    @endif
                                </div>
                                <div>
                                    <span>{{ strlen($note->content) }} chars · {{ str_word_count($note->content) }} words</span>
                                </div>
                            </div>

                            <!-- Edit Note Modal -->
                            <div x-show="editModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
                                <div @click.away="editModalOpen = false" class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 text-left font-sora">
                                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
                                        <h3 class="font-bold text-slate-900 text-sm">Edit Note in 7012450418#</h3>
                                        <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
                                    </div>

                                    <form method="POST" action="{{ route('admin.notes.update', $note) }}" class="space-y-4">
                                        @csrf
                                        @method('PUT')

                                        <div>
                                            <label class="block text-[10px] font-mono uppercase text-slate-500 font-bold mb-1">Title</label>
                                            <input type="text" name="title" value="{{ $note->title }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                                        </div>

                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[10px] font-mono uppercase text-slate-500 font-bold mb-1">Category</label>
                                                <select name="category" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 font-mono">
                                                    @foreach($categories as $cat)
                                                        <option value="{{ $cat }}" {{ $note->category === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-[10px] font-mono uppercase text-slate-500 font-bold mb-1">Color Theme</label>
                                                <select name="color" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 font-mono">
                                                    <option value="slate" {{ $note->color === 'slate' ? 'selected' : '' }}>Slate (Default)</option>
                                                    <option value="red" {{ $note->color === 'red' ? 'selected' : '' }}>Red</option>
                                                    <option value="emerald" {{ $note->color === 'emerald' ? 'selected' : '' }}>Emerald</option>
                                                    <option value="amber" {{ $note->color === 'amber' ? 'selected' : '' }}>Amber</option>
                                                    <option value="blue" {{ $note->color === 'blue' ? 'selected' : '' }}>Blue</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-[10px] font-mono uppercase text-slate-500 font-bold mb-1">Content</label>
                                            <textarea name="content" rows="8" required class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs font-mono text-slate-900 focus:outline-none focus:border-[#be1e2d]">{{ $note->content }}</textarea>
                                        </div>

                                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-mono">
                                            <input type="checkbox" name="is_pinned" value="1" {{ $note->is_pinned ? 'checked' : '' }} class="rounded border-slate-300 text-[#be1e2d] focus:ring-[#be1e2d]">
                                            <span class="font-bold text-slate-700">Pin to Top</span>
                                        </label>

                                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-mono font-bold">Cancel</button>
                                            <button type="submit" class="px-5 py-2 rounded-xl bg-[#be1e2d] text-white text-xs font-mono font-bold">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Pagination -->
                    @if($notes->hasPages())
                        <div class="pt-4">
                            {{ $notes->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function notesHubState() {
    return {
        draftTitle: '',
        draftCategory: 'general',
        draftColor: 'slate',
        draftPinned: false,
        draftContent: '',
        isSaving: false,

        get charCount() {
            return this.draftContent.length;
        },
        get wordCount() {
            const trimmed = this.draftContent.trim();
            return trimmed ? trimmed.split(/\s+/).length : 0;
        },
        get lineCount() {
            return this.draftContent ? this.draftContent.split(/\r\n|\r|\n/).length : 0;
        },

        focusEditor() {
            const input = this.$refs.contentInput;
            if (input) {
                input.focus();
                window.scrollTo({
                    top: input.getBoundingClientRect().top + window.scrollY - 100,
                    behavior: 'smooth'
                });
            }
        },

        async pasteFromClipboard() {
            try {
                if (navigator.clipboard && navigator.clipboard.readText) {
                    const text = await navigator.clipboard.readText();
                    if (text) {
                        this.draftContent = (this.draftContent ? this.draftContent + "\n\n" : "") + text;
                        this.focusEditor();
                    }
                } else {
                    alert('Clipboard access not supported by browser. Please press Ctrl+V to paste directly into the box.');
                    this.focusEditor();
                }
            } catch (err) {
                alert('Could not access clipboard automatically. Please press Ctrl+V into the content area.');
                this.focusEditor();
            }
        },

        clearEditor() {
            if (this.draftContent.length > 0 && confirm('Clear current typed content?')) {
                this.draftContent = '';
                this.draftTitle = '';
            }
        },

        transformText(type) {
            if (!this.draftContent) return;
            if (type === 'upper') {
                this.draftContent = this.draftContent.toUpperCase();
            } else if (type === 'lower') {
                this.draftContent = this.draftContent.toLowerCase();
            } else if (type === 'trim_lines') {
                this.draftContent = this.draftContent
                    .split(/\r?\n/)
                    .map(l => l.trim())
                    .filter(l => l.length > 0)
                    .join("\n");
            }
        },

        async copyNoteContent(text, btnElement) {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(text);
                } else {
                    const ta = document.createElement('textarea');
                    ta.value = text;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                }

                // Show temporary visual feedback
                const orig = btnElement.innerHTML;
                btnElement.innerHTML = '<span class="text-[10px] font-mono text-emerald-600 font-bold">Copied!</span>';
                setTimeout(() => {
                    btnElement.innerHTML = orig;
                }, 1500);
            } catch (e) {
                alert('Copy failed: ' + e);
            }
        }
    };
}
</script>
@endsection
