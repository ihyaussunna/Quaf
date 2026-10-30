<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#121417">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>7012450418# — Paste Text & Notes</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Malayalam:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&family=Sora:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body, button, input, select, textarea {
            font-family: 'Sora', sans-serif;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace !important;
        }
        .font-ml {
            font-family: 'Anek Malayalam', sans-serif !important;
        }
    </style>
</head>
<body class="bg-[#0f1115] text-slate-100 min-h-screen antialiased selection:bg-[#be1e2d] selection:text-white p-3 sm:p-6" x-data="standaloneNotesState()">

    <div class="max-w-7xl mx-auto space-y-5">

        <!-- Standalone Header (No external links) -->
        <header class="bg-[#181b20] border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-mono font-black bg-[#be1e2d] text-white tracking-widest uppercase shadow-sm">
                        7012450418#
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-300 font-mono border border-slate-700">
                        Isolated Scratchpad
                    </span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-2">
                    <span>7012450418#</span>
                    <span class="text-slate-500 font-light text-xl sm:text-2xl">—</span>
                    <span class="text-slate-200 text-lg sm:text-2xl font-bold">Paste Text & Notes Board</span>
                </h1>
                <p class="text-xs text-slate-400 mt-1 font-mono leading-relaxed">
                    ഏത് ടെക്സ്റ്റും നേരിട്ട് പേസ്റ്റ് ചെയ്യാനും നോട്സ് ടൈപ്പ് ചെയ്ത് സേവ് ചെയ്യാനുമുള്ള പ്രത്യേക ബോർഡ്.
                </p>
            </div>

            <!-- Quick Paste Action Button -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="pasteFromClipboard()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-mono font-bold text-xs uppercase tracking-wider transition-all shadow-md flex items-center gap-2 cursor-pointer active:scale-95">
                    <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Paste Clipboard</span>
                </button>
            </div>
        </header>

        <!-- Flash Success Notification -->
        @if(session('success'))
            <div class="p-3.5 rounded-xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 text-xs font-mono flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Quick Metrics Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono">
            <div class="bg-[#181b20] p-4 rounded-2xl border border-slate-800">
                <div class="text-[10px] uppercase text-slate-500 font-bold">Stored Notes</div>
                <div class="text-2xl font-black text-white mt-1">{{ $totalCount }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Total saved entries</div>
            </div>

            <div class="bg-[#181b20] p-4 rounded-2xl border border-amber-900/40 bg-amber-950/10">
                <div class="text-[10px] uppercase text-amber-400 font-bold">Pinned Notes</div>
                <div class="text-2xl font-black text-amber-300 mt-1">{{ $pinnedCount }}</div>
                <div class="text-[10px] text-amber-500/80 mt-0.5">Top prioritized</div>
            </div>

            <div class="bg-[#181b20] p-4 rounded-2xl border border-slate-800">
                <div class="text-[10px] uppercase text-slate-500 font-bold">Page Mode</div>
                <div class="text-xs font-bold text-emerald-400 mt-2">Standalone Hub</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Enclosed scratchpad</div>
            </div>

            <div class="bg-[#181b20] p-4 rounded-2xl border border-slate-800">
                <div class="text-[10px] uppercase text-slate-500 font-bold">Auth Requirement</div>
                <div class="text-xs font-bold text-slate-300 mt-2">No Login Required</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Direct open access</div>
            </div>
        </div>

        <!-- Main Workspace (Left: Paste & Editor, Right: Notes List) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- LEFT COLUMN: Live Editor & Paste Pad -->
            <div class="lg:col-span-6 space-y-4">
                <div class="bg-[#181b20] rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                    <div class="bg-[#121418] p-4 border-b border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <h2 class="text-xs font-mono font-bold tracking-wider uppercase text-slate-200">
                                Paste Text / Type Note
                            </h2>
                        </div>
                        <span class="text-[10px] font-mono text-slate-500">7012450418#</span>
                    </div>

                    <form method="POST" action="/7012450418" class="p-5 space-y-4">
                        @csrf

                        <!-- Title & Category -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-mono uppercase text-slate-400 font-bold mb-1">
                                    Title <span class="text-slate-600 font-normal">(Optional)</span>
                                </label>
                                <input type="text" name="title" x-model="draftTitle" placeholder="e.g. Stage Notice, Result List, Phone Number..."
                                       class="w-full bg-[#121418] border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder:text-slate-600 focus:outline-none focus:border-[#be1e2d] font-sans">
                            </div>

                            <div>
                                <label class="block text-[10px] font-mono uppercase text-slate-400 font-bold mb-1">Category</label>
                                <select name="category" x-model="draftCategory" class="w-full bg-[#121418] border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#be1e2d] font-mono">
                                    <option value="general">General</option>
                                    <option value="announcement">Announcement</option>
                                    <option value="result">Result / Standings</option>
                                    <option value="program">Program / Duty</option>
                                    <option value="schedule">Schedule / Time</option>
                                    <option value="urgent">Urgent / Priority</option>
                                </select>
                            </div>
                        </div>

                        <!-- Content Textarea -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[10px] font-mono uppercase text-slate-300 font-bold">
                                    Content / Pasted Text <span class="text-red-400">*</span>
                                </label>

                                <!-- Text Formatting Helpers -->
                                <div class="flex items-center gap-1.5 text-[10px] font-mono">
                                    <button type="button" @click="transformText('upper')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 transition" title="ALL UPPERCASE">AA</button>
                                    <button type="button" @click="transformText('lower')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 transition" title="all lowercase">aa</button>
                                    <button type="button" @click="transformText('trim_lines')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 transition" title="Trim blank lines">Trim</button>
                                    <button type="button" @click="clearEditor()" class="px-2 py-0.5 rounded bg-rose-950/60 hover:bg-rose-900/80 text-rose-300 transition" title="Clear">Clear</button>
                                </div>
                            </div>

                            <textarea name="content" x-ref="contentInput" x-model="draftContent" rows="12" required
                                      placeholder="Paste any text here (WhatsApp messages, schedule changes, judge contacts, participant lists, announcements...) or type notes..."
                                      class="w-full bg-[#121418] border border-slate-700 rounded-xl p-4 text-xs font-mono text-slate-100 placeholder:text-slate-600 focus:outline-none focus:border-[#be1e2d] transition-all leading-relaxed"></textarea>

                            <!-- Live Character Metrics -->
                            <div class="flex items-center justify-between mt-1 text-[11px] font-mono text-slate-500">
                                <div class="flex items-center gap-3">
                                    <span>Chars: <strong class="text-slate-300" x-text="charCount"></strong></span>
                                    <span>Words: <strong class="text-slate-300" x-text="wordCount"></strong></span>
                                    <span>Lines: <strong class="text-slate-300" x-text="lineCount"></strong></span>
                                </div>
                                <span x-show="draftContent.length > 0" class="text-emerald-400 font-bold text-[10px]">
                                    Ready to Save
                                </span>
                            </div>
                        </div>

                        <!-- Card Style & Submit Bar -->
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-800">
                            <div class="flex items-center gap-4">
                                <!-- Color Picker -->
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] font-mono uppercase text-slate-500 font-bold mr-1">Color:</span>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="color" value="slate" x-model="draftColor" class="sr-only">
                                        <span class="w-5 h-5 rounded-full bg-slate-700 block border-2" :class="draftColor === 'slate' ? 'border-amber-400 scale-110' : 'border-transparent'"></span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="color" value="red" x-model="draftColor" class="sr-only">
                                        <span class="w-5 h-5 rounded-full bg-rose-600 block border-2" :class="draftColor === 'red' ? 'border-white scale-110' : 'border-transparent'"></span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="color" value="emerald" x-model="draftColor" class="sr-only">
                                        <span class="w-5 h-5 rounded-full bg-emerald-600 block border-2" :class="draftColor === 'emerald' ? 'border-white scale-110' : 'border-transparent'"></span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="color" value="amber" x-model="draftColor" class="sr-only">
                                        <span class="w-5 h-5 rounded-full bg-amber-500 block border-2" :class="draftColor === 'amber' ? 'border-white scale-110' : 'border-transparent'"></span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="color" value="blue" x-model="draftColor" class="sr-only">
                                        <span class="w-5 h-5 rounded-full bg-blue-600 block border-2" :class="draftColor === 'blue' ? 'border-white scale-110' : 'border-transparent'"></span>
                                    </label>
                                </div>

                                <!-- Pin Checkbox -->
                                <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-mono select-none">
                                    <input type="checkbox" name="is_pinned" value="1" x-model="draftPinned" class="rounded border-slate-700 bg-slate-800 text-[#be1e2d] focus:ring-[#be1e2d]">
                                    <span class="font-bold text-slate-300">Pin to Top</span>
                                </label>
                            </div>

                            <button type="submit" :disabled="!draftContent.trim()"
                                    class="px-6 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] disabled:opacity-40 disabled:cursor-not-allowed text-white font-mono font-bold text-xs uppercase tracking-wider transition-all shadow-md flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                <span>Save Note to 7012450418#</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- RIGHT COLUMN: Stored Notes Grid -->
            <div class="lg:col-span-6 space-y-4">

                <!-- Search & Filter Bar (Keeps inside /7012450418) -->
                <div class="bg-[#181b20] p-4 rounded-2xl border border-slate-800 shadow-xl space-y-3">
                    <form method="GET" action="/7012450418" class="space-y-3">
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500 text-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </span>
                                <input type="text" name="search" value="{{ $search }}" placeholder="Search stored notes, text, numbers..."
                                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-700 bg-[#121418] text-white placeholder:text-slate-600 focus:outline-none focus:border-[#be1e2d] font-mono">
                            </div>

                            <button type="submit" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-mono font-bold shrink-0 cursor-pointer">
                                Search
                            </button>

                            @if($search || $category || $onlyPinned)
                                <a href="/7012450418" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white rounded-xl text-xs font-mono shrink-0">
                                    Reset
                                </a>
                            @endif
                        </div>

                        <!-- Category Filter Pills -->
                        <div class="flex flex-wrap items-center gap-1.5 text-xs font-mono">
                            <a href="/7012450418{{ $search ? '?search='.urlencode($search) : '' }}"
                               class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition {{ empty($category) && !$onlyPinned ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-[#121418] text-slate-400 border-slate-800 hover:text-white' }}">
                                All ({{ $totalCount }})
                            </a>
                            <a href="/7012450418?pinned=1{{ $search ? '&search='.urlencode($search) : '' }}"
                               class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition {{ $onlyPinned ? 'bg-amber-500 text-slate-950 border-amber-500' : 'bg-[#121418] text-amber-400 border-amber-900/40 hover:bg-amber-950/20' }}">
                                Pinned ({{ $pinnedCount }})
                            </a>
                            @foreach($categories as $cat)
                                <a href="/7012450418?category={{ $cat }}{{ $search ? '&search='.urlencode($search) : '' }}"
                                   class="px-2.5 py-1 rounded-lg border text-[11px] font-bold capitalize transition {{ $category === $cat ? 'bg-[#be1e2d] text-white border-[#be1e2d]' : 'bg-[#121418] text-slate-400 border-slate-800 hover:text-white' }}">
                                    {{ $cat }}
                                </a>
                            @endforeach
                        </div>
                    </form>
                </div>

                <!-- Notes List -->
                @if($notes->isEmpty())
                    <div class="bg-[#181b20] rounded-2xl border border-slate-800 p-12 text-center shadow-xl">
                        <div class="w-12 h-12 rounded-full bg-slate-800 text-slate-500 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-300">No Notes Found</h3>
                        <p class="text-xs text-slate-500 font-mono mt-1">
                            @if($search || $category || $onlyPinned)
                                No notes matched your filter.
                            @else
                                Type or paste text on the left to create your first note.
                            @endif
                        </p>
                    </div>
                @else
                    <div class="space-y-3.5">
                        @foreach($notes as $note)
                            @php
                                $cardBorder = match($note->color) {
                                    'red' => 'border-rose-900/60 bg-rose-950/10',
                                    'emerald' => 'border-emerald-900/60 bg-emerald-950/10',
                                    'amber' => 'border-amber-900/60 bg-amber-950/10',
                                    'blue' => 'border-blue-900/60 bg-blue-950/10',
                                    default => 'border-slate-800 bg-[#181b20]',
                                };
                                $badgeColor = match($note->category) {
                                    'urgent' => 'bg-rose-950 text-rose-300 border-rose-800',
                                    'announcement' => 'bg-amber-950 text-amber-300 border-amber-800',
                                    'result' => 'bg-emerald-950 text-emerald-300 border-emerald-800',
                                    'program' => 'bg-blue-950 text-blue-300 border-blue-800',
                                    'schedule' => 'bg-purple-950 text-purple-300 border-purple-800',
                                    default => 'bg-slate-800 text-slate-300 border-slate-700',
                                };
                            @endphp

                            <div class="rounded-2xl border {{ $cardBorder }} p-4 sm:p-5 shadow-lg relative group transition"
                                 x-data="{ expanded: false, editModalOpen: false }">

                                <!-- Note Header -->
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            @if($note->is_pinned)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-500 text-slate-950">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v2.586l1.707 1.707a1 1 0 01.293.707V10a1 1 0 01-.293.707L11 12.414V17a1 1 0 01-2 0v-4.586l-1.707-1.707A1 1 0 017 10V8a1 1 0 01.293-.707L9 5.586V3a1 1 0 011-1z"/></svg>
                                                    <span>PINNED</span>
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase border {{ $badgeColor }}">
                                                {{ $note->category }}
                                            </span>
                                            <span class="text-[10px] font-mono text-slate-500">
                                                {{ $note->created_at->diffForHumans() }}
                                            </span>
                                        </div>

                                        <h3 class="text-sm font-bold text-white font-sans break-words">
                                            {{ $note->title ?: 'Untitled Note' }}
                                        </h3>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-1 shrink-0">
                                        <!-- Copy Text Button -->
                                        <button type="button" @click="copyNoteContent({{ json_encode($note->content) }}, $el)"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition cursor-pointer"
                                                title="Copy to clipboard">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>

                                        <!-- Pin Toggle Form -->
                                        <form method="POST" action="/7012450418/{{ $note->id }}/pin" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg {{ $note->is_pinned ? 'text-amber-400 bg-amber-950/40' : 'text-slate-500 hover:text-slate-300 hover:bg-slate-800' }} transition cursor-pointer" title="{{ $note->is_pinned ? 'Unpin' : 'Pin to top' }}">
                                                <svg class="w-4 h-4" fill="{{ $note->is_pinned ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                            </button>
                                        </form>

                                        <!-- Edit Modal Trigger -->
                                        <button type="button" @click="editModalOpen = true"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-blue-400 hover:bg-slate-800 transition cursor-pointer" title="Edit note">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <!-- Delete Form -->
                                        <form method="POST" action="/7012450418/{{ $note->id }}" class="inline" onsubmit="return confirm('Delete this note permanently from 7012450418#?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-950/30 transition cursor-pointer" title="Delete note">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Note Content Body -->
                                <div class="text-xs font-mono text-slate-200 bg-[#121418] rounded-xl p-3 border border-slate-800 leading-relaxed whitespace-pre-wrap break-words overflow-hidden"
                                     :class="expanded ? 'max-h-none' : 'max-h-36'">
                                    {{ $note->content }}
                                </div>

                                <!-- Expand / Collapse -->
                                @if(strlen($note->content) > 220 || substr_count($note->content, "\n") > 5)
                                    <div class="mt-2 text-right">
                                        <button type="button" @click="expanded = !expanded" class="text-[11px] font-mono text-[#be1e2d] hover:underline cursor-pointer">
                                            <span x-text="expanded ? 'Show Less' : 'Show Full Text &rarr;'"></span>
                                        </button>
                                    </div>
                                @endif

                                <!-- Meta Footer -->
                                <div class="mt-3 pt-2 border-t border-slate-800/80 flex items-center justify-between text-[10px] font-mono text-slate-500">
                                    <span>{{ strlen($note->content) }} chars · {{ str_word_count($note->content) }} words</span>
                                    <span>7012450418#</span>
                                </div>

                                <!-- Inline Edit Modal -->
                                <div x-show="editModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
                                    <div @click.away="editModalOpen = false" class="bg-[#181b20] rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-800 text-left">
                                        <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                                            <h3 class="font-bold text-white text-sm font-mono">Edit Note in 7012450418#</h3>
                                            <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-white text-lg">&times;</button>
                                        </div>

                                        <form method="POST" action="/7012450418/{{ $note->id }}" class="space-y-4">
                                            @csrf
                                            @method('PUT')

                                            <div>
                                                <label class="block text-[10px] font-mono uppercase text-slate-400 font-bold mb-1">Title</label>
                                                <input type="text" name="title" value="{{ $note->title }}" class="w-full bg-[#121418] border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-[#be1e2d]">
                                            </div>

                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-[10px] font-mono uppercase text-slate-400 font-bold mb-1">Category</label>
                                                    <select name="category" class="w-full bg-[#121418] border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-mono">
                                                        @foreach($categories as $cat)
                                                            <option value="{{ $cat }}" {{ $note->category === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-[10px] font-mono uppercase text-slate-400 font-bold mb-1">Color Theme</label>
                                                    <select name="color" class="w-full bg-[#121418] border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-mono">
                                                        <option value="slate" {{ $note->color === 'slate' ? 'selected' : '' }}>Slate (Default)</option>
                                                        <option value="red" {{ $note->color === 'red' ? 'selected' : '' }}>Red</option>
                                                        <option value="emerald" {{ $note->color === 'emerald' ? 'selected' : '' }}>Emerald</option>
                                                        <option value="amber" {{ $note->color === 'amber' ? 'selected' : '' }}>Amber</option>
                                                        <option value="blue" {{ $note->color === 'blue' ? 'selected' : '' }}>Blue</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-[10px] font-mono uppercase text-slate-400 font-bold mb-1">Content</label>
                                                <textarea name="content" rows="8" required class="w-full bg-[#121418] border border-slate-700 rounded-xl p-3 text-xs font-mono text-white focus:outline-none focus:border-[#be1e2d] leading-relaxed">{{ $note->content }}</textarea>
                                            </div>

                                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-mono">
                                                <input type="checkbox" name="is_pinned" value="1" {{ $note->is_pinned ? 'checked' : '' }} class="rounded border-slate-700 bg-slate-800 text-[#be1e2d] focus:ring-[#be1e2d]">
                                                <span class="font-bold text-slate-300">Pin to Top</span>
                                            </label>

                                            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                                                <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-mono font-bold cursor-pointer">Cancel</button>
                                                <button type="submit" class="px-5 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white text-xs font-mono font-bold cursor-pointer">Save Changes</button>
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

    <!-- Standalone Client Logic -->
    <script>
    function standaloneNotesState() {
        return {
            draftTitle: '',
            draftCategory: 'general',
            draftColor: 'slate',
            draftPinned: false,
            draftContent: '',

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

            async pasteFromClipboard() {
                try {
                    if (navigator.clipboard && navigator.clipboard.readText) {
                        const text = await navigator.clipboard.readText();
                        if (text) {
                            this.draftContent = (this.draftContent ? this.draftContent + "\n\n" : "") + text;
                            this.$refs.contentInput?.focus();
                        }
                    } else {
                        alert('Please press Ctrl+V to paste into the content box.');
                        this.$refs.contentInput?.focus();
                    }
                } catch (e) {
                    alert('Please press Ctrl+V directly into the content box.');
                    this.$refs.contentInput?.focus();
                }
            },

            clearEditor() {
                if (this.draftContent.length > 0 && confirm('Clear typed content?')) {
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
                    const orig = btnElement.innerHTML;
                    btnElement.innerHTML = '<span class="text-[10px] font-mono text-emerald-400 font-bold">Copied!</span>';
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
</body>
</html>
