@extends('layouts.leader', ['title' => 'Group Roster: ' . $group->name])

@section('content')
<style>
@media print {
    #sidebar, aside, header, nav, .filter-card, .no-print, button, form, .mobile-nav, .search-card {
        display: none !important;
    }
    .print-hidden-section {
        display: none !important;
    }
    body, html {
        background: #ffffff !important;
        color: #000000 !important;
        overflow: visible !important;
        height: auto !important;
        font-size: 11px !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
        overflow: visible !important;
        max-width: 100% !important;
        height: auto !important;
    }
    .print-only {
        display: block !important;
    }
    .avoid-break, tr {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    thead {
        display: table-header-group !important;
    }
    .official-print-table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    .official-print-table th,
    .official-print-table td {
        border: 1px solid #64748b !important;
        padding: 5px 6px !important;
        color: #0f172a !important;
    }
    .official-print-table th {
        background-color: #f1f5f9 !important;
        font-weight: 700 !important;
        text-align: center !important;
    }
}
@media screen {
    .print-only {
        display: none !important;
    }
}
@page {
    size: A4 portrait;
    margin: 8mm;
}
</style>

<div class="space-y-6" x-data="studentRosterManager()">
    <!-- Header (Screen Only) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <div>
            <h1 class="text-3xl font-sora font-black text-slate-900">Group Students Roster</h1>
            <p class="text-xs font-sora text-slate-500 mt-1">All registered participants belonging to {{ $group->name }}.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if($isEditingOpen)
                <span class="px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-sora text-emerald-800 font-bold flex items-center gap-1.5 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Name Editing Open
                </span>
            @else
                <span class="px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-200 text-xs font-sora text-rose-800 font-bold flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Name Editing Locked by Admin
                </span>
            @endif
            <span class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs font-sora text-slate-700 font-bold shadow-xs">
                Total: <span class="font-mono text-slate-900 font-bold">{{ $students->total() }}</span> Students
            </span>
        </div>
    </div>

    <!-- PDF Selection & Download Action Bar (Screen Only) -->
    <div class="no-print bg-slate-900 text-white rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-lg">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-400/20 text-amber-400 flex items-center justify-center font-bold text-sm font-mono">
                PDF
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-sora font-bold text-white">Official Delegate Registry PDF Export</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-400 text-slate-950"
                          x-text="selectedCount + ' of ' + allStudentIds.length + ' students included'"></span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">
                    Uncheck any student you wish to omit. Generates a clean, official A4 document ready for print.
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <button type="button" @click="includeAllStudents()" 
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-sora text-slate-200 border border-slate-700 transition cursor-pointer">
                Select All
            </button>
            <button type="button" @click="excludeAllStudents()" 
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-sora text-slate-200 border border-slate-700 transition cursor-pointer">
                Deselect All
            </button>
            <button type="button" @click="printPdf()" 
                    class="px-5 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-sora font-bold text-xs uppercase tracking-wider transition-all shadow-md flex items-center gap-2 cursor-pointer ml-auto md:ml-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Download / Print PDF</span>
            </button>
        </div>
    </div>

    <!-- Status Notice Banner (Screen Only) -->
    @if($isEditingOpen)
        <div class="no-print p-4 rounded-2xl bg-amber-50/80 border border-amber-200/80 text-amber-900 text-xs font-sora flex items-start sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>
                    <strong>Spelling Correction Allowed:</strong> വിദ്യാർത്ഥികളുടെ പേരിലുള്ള അക്ഷരത്തെറ്റുകൾ തിരുത്താൻ 'Edit Name' ബട്ടൺ ഉപയോഗിക്കുക. മാറ്റങ്ങൾ ഡിജിറ്റൽ പാസിലും സർട്ടിഫിക്കറ്റിലും തത്സമയം അപ്‌ഡേറ്റ് ചെയ്യപ്പെടും.
                </span>
            </div>
        </div>
    @else
        <div class="no-print p-4 rounded-2xl bg-slate-100 border border-slate-300 text-slate-700 text-xs font-sora flex items-start sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>
                    <strong>Student Name Editing Closed:</strong> വിദ്യാർത്ഥികളുടെ വിവരങ്ങൾ തിരുത്താനുള്ള സൗകര്യം അഡ്മിൻ ബ്ലോക്ക് ചെയ്തിരിക്കുന്നു. എന്തെങ്കിലും മാറ്റങ്ങൾ വരുത്തേണ്ടതുണ്ടെങ്കിൽ അഡ്മിനുമായി ബന്ധപ്പെടുക.
                </span>
            </div>
        </div>
    @endif

    <!-- Search & Filter Bar (Screen Only) -->
    <div class="no-print bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
        <form method="GET" action="{{ route('leader.students') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="text"
                       name="search"
                       value="{{ $search ?? '' }}"
                       placeholder="Search by student name or chest number..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-brand-orange focus:bg-white font-sora transition pr-10">
                @if(!empty($search))
                    <a href="{{ route('leader.students') }}" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold font-mono">
                        ✕
                    </a>
                @endif
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-brand-orange text-white rounded-xl text-xs font-bold hover:bg-orange-600 transition shadow-xs flex items-center justify-center gap-1.5 font-sora">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Search
            </button>
        </form>
    </div>

    <!-- Screen Paginated Table (Hidden on Print) -->
    <div class="no-print rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sora">
                <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-200 font-semibold text-[11px] tracking-wider">
                    <tr>
                        <th class="px-4 py-4 text-center w-14">In PDF</th>
                        <th class="px-6 py-4">Chest Number</th>
                        <th class="px-6 py-4">Full Name</th>
                        <th class="px-6 py-4">Zone</th>
                        <th class="px-6 py-4">Enrolled Programs</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                        <tr :class="isStudentExcluded({{ $student->id }}) ? 'opacity-40 bg-slate-50/60 border-dashed' : ''" class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-4 text-center">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           :checked="!isStudentExcluded({{ $student->id }})"
                                           @change="toggleStudent({{ $student->id }})"
                                           class="w-4 h-4 text-brand-orange rounded border-slate-300 focus:ring-brand-orange/30 cursor-pointer">
                                </label>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                {{ $student->student_id }}
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-900">
                                <div class="flex items-center gap-2">
                                    <span id="student-name-{{ $student->id }}" class="font-bold text-slate-900 text-sm">
                                        {{ $student->name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold">
                                    {{ $student->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($student->entries->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 max-w-md">
                                        @foreach($student->entries as $entry)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-900 border border-amber-200">
                                                {{ $entry->program?->code }}: {{ $entry->program?->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">No entries yet</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    @if($isEditingOpen)
                                        <button type="button"
                                                @click="openEditModal({{ $student->id }}, '{{ addslashes($student->name) }}', '{{ $student->student_id }}', '{{ $student->category }}')"
                                                class="px-3 py-1.5 rounded-xl bg-orange-50 hover:bg-orange-100 text-brand-orange border border-orange-200 text-xs font-bold font-sora transition shadow-2xs flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Edit Name
                                        </button>
                                    @else
                                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-400 text-xs font-semibold cursor-not-allowed" title="Name editing closed by Admin">
                                            Locked
                                        </span>
                                    @endif

                                    <a href="{{ route('admin.idcards.show', $student->id) }}"
                                       target="_blank"
                                       class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold font-sora transition shadow-2xs flex items-center gap-1">
                                        <span>Pass</span>
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                No students found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $students->links() }}
            </div>
        @endif
    </div>

    <!-- ================================================================= -->
    <!-- REALISTIC, BEAUTIFUL, OFFICIAL PRINTABLE DOCUMENT (Print Only)     -->
    <!-- ================================================================= -->
    <div class="print-only w-full bg-white text-slate-900">
        <!-- Official Festival Top Masthead Banner -->
        <div class="w-full pb-2 mb-3 text-center">
            <img src="{{ asset('images/print-pdf-header.svg') }}"
                 alt="Markaz Cultural Festival"
                 class="w-1/2 max-w-[50%] h-auto max-h-12 sm:max-h-14 object-contain block mx-auto"
                 style="max-height: 55px; width: 50%; max-width: 50%;">
        </div>

        <!-- Official Document Header Block -->
        <div class="flex items-center justify-between pb-3 border-b-2 border-slate-900 gap-4 mb-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF Logo" class="h-12 w-auto object-contain">
                <div>
                    <h1 class="text-lg font-black font-sora text-slate-900 uppercase tracking-tight">Participant Delegate Registry & Roll Sheet</h1>
                    <p class="text-[11px] font-semibold text-slate-600">Ihyaussunna Students Union, Markazu Saquafathi Sunniyya</p>
                </div>
            </div>

            <div class="text-right font-mono text-[11px] text-slate-600 space-y-0.5 shrink-0">
                <div class="font-bold text-slate-900 text-xs font-sora">GROUP: {{ $group->name }} ({{ $group->code }})</div>
                <div>Date: {{ now()->format('d M Y, h:i A') }}</div>
                <div>Total Delegates: <span class="font-bold text-slate-900 font-mono">{{ $allStudentsForPrint->count() }}</span></div>
            </div>
        </div>

        <!-- Official Participants Registry Table -->
        <table class="official-print-table w-full text-left text-xs font-sora">
            <thead>
                <tr class="bg-slate-100 text-slate-900 border-b-2 border-slate-300 text-[11px] font-bold uppercase">
                    <th class="py-2 px-1 text-center w-8">#</th>
                    <th class="py-2 px-2 text-center w-24">Chest / ID</th>
                    <th class="py-2 px-3 text-left">Participant Full Name</th>
                    <th class="py-2 px-2 text-center w-20">Zone</th>
                    <th class="py-2 px-2 text-center w-16">Class</th>
                    <th class="py-2 px-3 text-left">Enrolled Competitions</th>
                    <th class="py-2 px-2 text-center w-28">Signature / Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allStudentsForPrint as $idx => $st)
                    <tr :class="isStudentExcluded({{ $st->id }}) ? 'print-hidden-section' : ''"
                        class="{{ $idx % 2 === 1 ? 'bg-slate-50/50' : 'bg-white' }} avoid-break">
                        <td class="text-center font-mono font-medium text-slate-600 text-[10px]">
                            {{ $idx + 1 }}
                        </td>
                        <td class="text-center font-mono font-bold text-slate-900 text-[11px] whitespace-nowrap">
                            {{ $st->student_id }}
                        </td>
                        <td class="font-bold text-slate-900">
                            {{ $st->name }}
                        </td>
                        <td class="text-center font-semibold text-slate-700 whitespace-nowrap">
                            {{ $st->category }}
                        </td>
                        <td class="text-center text-slate-600 whitespace-nowrap">
                            {{ $st->class_level ?? '—' }}
                        </td>
                        <td>
                            @if($st->entries && $st->entries->isNotEmpty())
                                <div class="space-y-0.5 leading-tight">
                                    @foreach($st->entries as $entry)
                                        <div class="text-[10px] text-slate-800">
                                            <span class="font-mono font-bold text-slate-900">{{ $entry->program?->code ?: $entry->program?->id }}:</span>
                                            {{ $entry->program?->name }}
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-400 italic text-[10px]">No entries registered</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="border-b border-dashed border-slate-400 h-5 mt-2"></div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Official Signatures Block -->
        <div class="pt-8 mt-8 border-t border-slate-400 avoid-break">
            <div class="grid grid-cols-3 gap-6 text-center text-xs font-mono">
                <div>
                    <div class="border-b border-slate-400 mb-1.5 h-8"></div>
                    <div class="font-bold text-slate-900">Desk Officer / Registrar</div>
                    <div class="text-[10px] text-slate-500">Registration Committee</div>
                </div>
                <div>
                    <div class="border-b border-slate-400 mb-1.5 h-8"></div>
                    <div class="font-bold text-slate-900">{{ $group->name }} Leader / Captain</div>
                    <div class="text-[10px] text-slate-500">Official Verification</div>
                </div>
                <div>
                    <div class="border-b border-slate-400 mb-1.5 h-8"></div>
                    <div class="font-bold text-slate-900">General Convener</div>
                    <div class="text-[10px] text-slate-500">Ashabul Quaf Committee</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Name Modal (Screen Only) -->
    <div x-show="modalOpen"
         x-cloak
         class="no-print fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
         @keydown.escape.window="if(!isSaving) modalOpen = false"
         style="display: none;">
        <div class="bg-white rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl border border-slate-100 transform transition-all"
             @click.outside="if(!isSaving) modalOpen = false">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-orange-100 text-brand-orange flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 font-sora">Edit Participant Name</h3>
                        <p class="text-[11px] text-slate-400 font-mono">Chest: <span class="text-slate-700 font-bold" x-text="studentChest"></span> • Zone: <span class="text-slate-700 font-bold" x-text="studentZone"></span></p>
                    </div>
                </div>
                <button type="button" @click="if(!isSaving) modalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form @submit.prevent="submitEditName()" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 font-sora">Student Full Name (English)</label>
                    <input type="text"
                           x-model="formName"
                           x-ref="nameInput"
                           required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 font-semibold focus:outline-none focus:border-brand-orange focus:bg-white font-sora transition"
                           placeholder="Enter student full name">
                    <p class="text-[11px] text-slate-500 mt-1 font-sora">
                        സർട്ടിഫിക്കറ്റിലും ഐഡി കാർഡിലും പ്രിന്റ് ചെയ്യേണ്ട പേര് ശരിയായി ടൈപ്പ് ചെയ്യുക.
                    </p>
                </div>

                <div x-show="errorMessage" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-sora" x-text="errorMessage"></div>
                <div x-show="successMessage" class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-sora" x-text="successMessage"></div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button"
                            @click="modalOpen = false"
                            :disabled="isSaving"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold font-sora transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="isSaving || !formName.trim()"
                            class="px-5 py-2.5 bg-brand-orange text-white rounded-xl text-xs font-bold font-sora hover:bg-orange-600 transition shadow-sm flex items-center gap-2 cursor-pointer disabled:opacity-50">
                        <span x-show="!isSaving">Save Changes</span>
                        <span x-show="isSaving">Updating...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function studentRosterManager() {
    return {
        allStudentIds: @json($allStudentsForPrint->pluck('id')),
        excludedStudentIds: [],
        toggleStudent(id) {
            const numId = Number(id);
            if (this.excludedStudentIds.includes(numId)) {
                this.excludedStudentIds = this.excludedStudentIds.filter(x => x !== numId);
            } else {
                this.excludedStudentIds.push(numId);
            }
        },
        isStudentExcluded(id) {
            return this.excludedStudentIds.includes(Number(id));
        },
        includeAllStudents() {
            this.excludedStudentIds = [];
        },
        excludeAllStudents() {
            this.excludedStudentIds = [...this.allStudentIds];
        },
        get selectedCount() {
            return Math.max(0, this.allStudentIds.length - this.excludedStudentIds.length);
        },
        printPdf() {
            window.print();
        },
        modalOpen: false,
        studentId: null,
        studentChest: '',
        studentZone: '',
        formName: '',
        isSaving: false,
        errorMessage: '',
        successMessage: '',
        openEditModal(id, currentName, chest, zone) {
            this.studentId = id;
            this.formName = currentName;
            this.studentChest = chest;
            this.studentZone = zone;
            this.errorMessage = '';
            this.successMessage = '';
            this.isSaving = false;
            this.modalOpen = true;
            this.$nextTick(() => {
                if (this.$refs.nameInput) {
                    this.$refs.nameInput.focus();
                    this.$refs.nameInput.select();
                }
            });
        },
        async submitEditName() {
            if (this.isSaving || !this.formName.trim() || !this.studentId) return;
            this.isSaving = true;
            this.errorMessage = '';
            this.successMessage = '';

            try {
                const res = await fetch(`{{ url('leader/students') }}/${this.studentId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ name: this.formName.trim() })
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok || !data.success) {
                    this.errorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Failed to update student name.');
                    this.isSaving = false;
                    return;
                }

                this.successMessage = data.message || 'Student name updated successfully.';
                const el = document.getElementById('student-name-' + this.studentId);
                if (el) {
                    el.textContent = data.student ? data.student.name : this.formName.trim();
                }

                setTimeout(() => {
                    this.modalOpen = false;
                }, 1200);
            } catch (err) {
                this.errorMessage = 'Network error while updating student name.';
            } finally {
                this.isSaving = false;
            }
        }
    };
}
</script>
@endsection
