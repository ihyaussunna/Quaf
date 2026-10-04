@extends('layouts.leader')

@section('title', 'Student Wise Programs - Leader Panel')

@section('content')
@php
    $studentsToDisplay = $selectedStudent ? collect([$selectedStudent]) : $students;
@endphp
<script>
window.quafStudentWiseData = {
    csrf: '{{ csrf_token() }}',
    swapUrl: '{{ route('leader.registrations.swap') }}',
    registerUrl: '{{ route('leader.registrations.store') }}',
    destroyUrlBase: '{{ url('leader/registrations') }}',
    isRegistrationOpen: {{ $isRegistrationOpen ? 'true' : 'false' }},
    availablePrograms: @json($individualPrograms ?? []),
    allStudentIds: @json($studentsToDisplay->pluck('id')->values())
};

function studentWisePageManager() {
    return {
        activeModal: null, // 'swap' or 'add'
        modalStudent: null,
        modalFromEntry: null,
        selectedToProgramId: '',
        programSearch: '',
        isSubmitting: false,
        feedbackSuccess: '',
        feedbackError: '',
        isRegistrationOpen: window.quafStudentWiseData ? window.quafStudentWiseData.isRegistrationOpen : true,
        allPrograms: window.quafStudentWiseData ? (window.quafStudentWiseData.availablePrograms || []) : [],
        allStudentIds: (window.quafStudentWiseData && window.quafStudentWiseData.allStudentIds) ? window.quafStudentWiseData.allStudentIds : [],
        excludedStudentIds: [],
        isStudentExcluded(id) {
            return this.excludedStudentIds.includes(Number(id));
        },
        toggleStudent(id) {
            const numId = Number(id);
            if (this.excludedStudentIds.includes(numId)) {
                this.excludedStudentIds = this.excludedStudentIds.filter(x => x !== numId);
            } else {
                this.excludedStudentIds.push(numId);
            }
        },
        includeAllStudents() {
            this.excludedStudentIds = [];
        },
        excludeAllStudents() {
            this.excludedStudentIds = [...this.allStudentIds];
        },
        get includedStudentsCount() {
            return this.allStudentIds.length - this.excludedStudentIds.length;
        },
        printPdf() {
            if (this.includedStudentsCount === 0) {
                alert('Please include at least one student section to generate the PDF.');
                return;
            }
            window.print();
        },
        
        openSwapModal(student, entry) {
            this.modalStudent = student;
            this.modalFromEntry = entry;
            this.selectedToProgramId = '';
            this.programSearch = '';
            this.feedbackError = '';
            this.feedbackSuccess = '';
            this.activeModal = 'swap';
        },
        
        openAddModal(student) {
            if (student.indCount >= 5) {
                alert('This student has already reached the maximum 5 individual programs limit. To add another program, please remove or swap an existing program.');
                return;
            }
            this.modalStudent = student;
            this.modalFromEntry = null;
            this.selectedToProgramId = '';
            this.programSearch = '';
            this.feedbackError = '';
            this.feedbackSuccess = '';
            this.activeModal = 'add';
        },
        
        closeModal() {
            this.activeModal = null;
            this.modalStudent = null;
            this.modalFromEntry = null;
            this.selectedToProgramId = '';
            this.programSearch = '';
            this.feedbackError = '';
            this.feedbackSuccess = '';
        },
        
        normalizeZone(str) {
            if (!str) return '';
            const s = String(str).toLowerCase().trim();
            if (s.includes('mix')) return 'mix';
            if (s.includes('a') && !s.includes('b') && !s.includes('c')) return 'a';
            if (s.includes('b')) return 'b';
            if (s.includes('c')) return 'c';
            return s.replace(/[^a-z0-9]/g, '');
        },

        get eligibleProgramsForModal() {
            if (!this.modalStudent) return [];
            const st = this.modalStudent;
            const enrolledProgIds = st.entries.map(e => Number(e.program_id));
            const stZone = this.normalizeZone(st.zone);
            const q = (this.programSearch || '').toLowerCase().trim();
            
            return this.allPrograms.filter(p => {
                // Skip if student is already enrolled in this program
                if (enrolledProgIds.includes(Number(p.id))) return false;
                
                // Zone check:
                if (!p.is_mix_zone) {
                    const pZone = this.normalizeZone(p.zone_name);
                    if (pZone !== stZone) return false;
                } else {
                    if (!p.mix_zone_open_to_all && Array.isArray(p.allowed_zones) && p.allowed_zones.length > 0) {
                        const allowed = p.allowed_zones.map(z => this.normalizeZone(z));
                        if (!allowed.includes(stZone)) return false;
                    }
                }
                
                // Group quota check: must have remaining slot
                if (p.remaining <= 0) return false;
                
                // Text search
                if (q) {
                    const matchName = (p.name || '').toLowerCase().includes(q);
                    const matchCode = (p.code || '').toLowerCase().includes(q);
                    if (!matchName && !matchCode) return false;
                }
                
                return true;
            });
        },
        
        async submitSwap() {
            if (!this.selectedToProgramId) {
                this.feedbackError = 'Please select a replacement program.';
                return;
            }
            this.isSubmitting = true;
            this.feedbackError = '';
            this.feedbackSuccess = '';
            
            try {
                const res = await fetch(window.quafStudentWiseData.swapUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.quafStudentWiseData.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        student_id: this.modalStudent.id,
                        from_entry_id: this.modalFromEntry.id,
                        to_program_id: this.selectedToProgramId
                    })
                });
                
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.success) {
                    this.feedbackError = data.message || 'Swap failed. Please check candidate eligibility.';
                    return;
                }
                
                // Update student entries in UI
                const st = this.modalStudent;
                const idx = st.entries.findIndex(e => e.id == this.modalFromEntry.id);
                if (idx !== -1) {
                    st.entries.splice(idx, 1, data.new_entry);
                }
                
                // Update quota in allPrograms
                const oldP = this.allPrograms.find(p => p.id == this.modalFromEntry.program_id);
                if (oldP) {
                    oldP.enrolled = Math.max(0, oldP.enrolled - 1);
                    oldP.remaining = Math.min(oldP.limit, oldP.remaining + 1);
                    oldP.is_full = false;
                }
                const newP = this.allPrograms.find(p => p.id == this.selectedToProgramId);
                if (newP) {
                    newP.enrolled = newP.enrolled + 1;
                    newP.remaining = Math.max(0, newP.limit - newP.enrolled);
                    newP.is_full = (newP.enrolled >= newP.limit);
                }
                
                this.feedbackSuccess = data.message || 'Program swapped successfully!';
                setTimeout(() => {
                    this.closeModal();
                }, 800);
            } catch (err) {
                this.feedbackError = 'Connection error while swapping program.';
            } finally {
                this.isSubmitting = false;
            }
        },
        
        async submitAdd() {
            if (!this.selectedToProgramId) {
                this.feedbackError = 'Please select a competition program to add.';
                return;
            }
            this.isSubmitting = true;
            this.feedbackError = '';
            this.feedbackSuccess = '';
            
            try {
                const formData = new FormData();
                formData.append('program_id', this.selectedToProgramId);
                formData.append('student_id', this.modalStudent.id);
                
                const res = await fetch(window.quafStudentWiseData.registerUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.quafStudentWiseData.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.success) {
                    this.feedbackError = data.message || 'Failed to add student to program.';
                    return;
                }
                
                const st = this.modalStudent;
                const newProg = this.allPrograms.find(p => p.id == this.selectedToProgramId);
                const assignedEntryId = (data.enrolled_students && data.enrolled_students.length > 0)
                    ? (data.enrolled_students.find(es => es.id == st.id)?.entry_id || data.enrolled_students[0].entry_id)
                    : Date.now();

                st.entries.push({
                    id: assignedEntryId,
                    program_id: newProg ? newProg.id : this.selectedToProgramId,
                    program_code: newProg ? newProg.code : '',
                    program_name: newProg ? newProg.name : (data.program_name || 'Program'),
                    zone_name: newProg ? newProg.zone_name : (st.zone || 'Mix Zone'),
                    type: 'individual',
                    is_stage: (newProg && newProg.is_stage) ? 'Stage' : 'Non-stage'
                });
                st.indCount++;
                
                if (newProg) {
                    newProg.enrolled = newProg.enrolled + 1;
                    newProg.remaining = Math.max(0, newProg.limit - newProg.enrolled);
                    newProg.is_full = (newProg.enrolled >= newProg.limit);
                }
                
                this.feedbackSuccess = data.message || 'Student added to program successfully!';
                setTimeout(() => {
                    this.closeModal();
                }, 800);
            } catch (err) {
                this.feedbackError = 'Connection error while adding student to program.';
            } finally {
                this.isSubmitting = false;
            }
        },
        
        async removeStudentFromProgram(student, entry) {
            if (!confirm(`Are you sure you want to remove "${student.name}" from "${entry.program_name}"?`)) {
                return;
            }
            
            try {
                const res = await fetch(`${window.quafStudentWiseData.destroyUrlBase}/${entry.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': window.quafStudentWiseData.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.success) {
                    alert(data.message || 'Failed to remove program registration.');
                    return;
                }
                
                // Remove from student.entries
                student.entries = student.entries.filter(e => e.id !== entry.id);
                if (entry.type === 'individual') {
                    student.indCount = Math.max(0, student.indCount - 1);
                } else {
                    student.groupCount = Math.max(0, student.groupCount - 1);
                }
                
                // Update quota in allPrograms
                const p = this.allPrograms.find(prog => prog.id == entry.program_id);
                if (p) {
                    p.enrolled = Math.max(0, p.enrolled - 1);
                    p.remaining = Math.min(p.limit, p.remaining + 1);
                    p.is_full = false;
                }
            } catch (err) {
                alert('Connection error while removing registration.');
            }
        }
    };
}
</script>

<div class="space-y-6" x-data="studentWisePageManager()">
    <style>
        @media print {
            #sidebar, aside, header, nav, .filter-card, .no-print, button, form, .mobile-nav, [x-cloak] {
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
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
                max-width: 100% !important;
            }
            .print-only {
                display: block !important;
            }
            .avoid-break {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            tr {
                page-break-inside: avoid !important;
            }
            thead {
                display: table-header-group;
            }
        }
        @media screen {
            .print-only {
                display: none !important;
            }
        }
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
    </style>

    <!-- Official Print Header (Visible only when printing or saving to PDF) -->
    <div class="print-only mb-6">
        @include('partials.print-pdf-header', [
            'title' => 'Student Wise Programs Roster',
            'subtitle' => 'Ihyaussunna Students Union, Markazu Saquafathi Sunniyya',
            'group' => $group,
            'filterText' => $selectedStudent ? 'Student: ' . $selectedStudent->name . ' (' . ltrim((string)($selectedStudent->chest_number ?: $selectedStudent->student_id), '#') . ')' : 'All Students (' . $students->count() . ')'
        ])
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <div>
            <h1 class="text-2xl font-bold font-sora text-gray-900">Student Wise Programs</h1>
            <p class="text-xs text-gray-500 mt-1 font-sora">Check individual program participation, fast swap, and manage student rosters</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="printPdf()" class="px-5 py-2.5 bg-[#be1e2d] hover:bg-[#a01624] text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-2 font-sora cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print / Save PDF</span>
            </button>
        </div>
    </div>

    <!-- Search / Select Student -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs filter-card no-print">
        <form method="GET" action="{{ route('leader.students-wise') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-8 relative" x-data="{
                open: false,
                search: '{{ $selectedStudent ? addslashes($selectedStudent->name.' ('.ltrim((string)($selectedStudent->chest_number ?: $selectedStudent->student_id), '#').')') : '' }}',
                selectedStudentId: '{{ $selectedStudentId ?? '' }}',
                studentsList: [
                    @foreach($students as $st)
                        { id: '{{ $st->id }}', name: '{{ addslashes($st->name) }}', chest: '{{ ltrim((string)($st->chest_number ?: ($st->student_id ?: $st->id)), '#') }}', class: '{{ $st->class_level }}', label: '{{ addslashes($st->name) }} ({{ ltrim((string)($st->chest_number ?: ($st->student_id ?: $st->id)), '#') }})' },
                    @endforeach
                ],
                get filtered() {
                    if (!this.search.trim()) {
                        return this.studentsList.slice(0, 30);
                    }
                    const q = this.search.toLowerCase().trim();
                    return this.studentsList.filter(s => s.name.toLowerCase().includes(q) || s.chest.toLowerCase().includes(q)).slice(0, 30);
                },
                select(s) {
                    this.selectedStudentId = s.id;
                    this.search = s.label;
                    this.open = false;
                    $nextTick(() => { this.$el.closest('form').submit(); });
                },
                clear() {
                    this.selectedStudentId = '';
                    this.search = '';
                    this.open = true;
                }
            }" @click.outside="open = false">
                <label class="block text-xs font-semibold text-gray-600 mb-1 font-sora">Select Student</label>
                <input type="hidden" name="student" :value="selectedStudentId">
                <div class="relative">
                    <input type="text"
                           x-model="search"
                           @focus="open = true"
                           @input="open = true"
                           placeholder="Type student name or chest number to filter..."
                           class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange pr-8 font-sora">
                    <button type="button" x-show="selectedStudentId" @click="clear()" class="absolute right-2.5 top-3 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <!-- Dropdown results -->
                <div x-show="open"
                     x-transition
                     class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg divide-y divide-gray-100 text-xs font-sora"
                     style="display: none;">
                    <div class="p-2 text-gray-400 text-[10px] uppercase font-sora tracking-wider bg-gray-50">
                        Select student from {{ $group->name }}
                    </div>
                    <template x-for="s in filtered" :key="s.id">
                        <button type="button"
                                @click="select(s)"
                                class="w-full text-left px-3 py-2.5 hover:bg-orange-50 flex items-center justify-between transition-colors">
                            <div>
                                <span class="font-medium text-gray-900 block" x-text="s.name"></span>
                                <span class="text-[11px] text-gray-500 font-sora" x-text="'Class: ' + (s.class || '-')"></span>
                            </div>
                            <span class="font-mono text-xs text-brand-orange font-bold" x-text="s.chest"></span>
                        </button>
                    </template>
                    <div x-show="filtered.length === 0" class="p-3 text-center text-gray-400 text-xs font-sora">
                        No students match your search
                    </div>
                </div>
            </div>
            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1 font-sora">Or Search Name / Chest No</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search..." class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-9 pr-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange font-sora">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
        </form>
    </div>

    <!-- PDF Section Exclusion Toolbar (Visible on screen, hidden on print) -->
    <div class="no-print bg-slate-900 text-white rounded-2xl p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-0.5">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#be1e2d] text-white">PDF SECTIONS</span>
                <span class="text-xs font-sora font-semibold text-slate-200">
                    <strong class="text-amber-400 font-mono" x-text="includedStudentsCount"></strong> of <span class="font-mono text-slate-300" x-text="allStudentIds.length"></span> students selected for PDF
                </span>
            </div>
            <p class="text-[11px] font-sora text-slate-400">
                Omit / exclude individual students from the generated PDF by unchecking them before downloading or printing.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs font-sora shrink-0">
            <button type="button" @click="includeAllStudents()" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-medium transition cursor-pointer">
                Select All
            </button>
            <button type="button" @click="excludeAllStudents()" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white font-medium transition cursor-pointer">
                Deselect All
            </button>
            <button type="button" @click="printPdf()" class="px-4 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold transition flex items-center gap-2 shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Download / Print PDF</span>
            </button>
        </div>
    </div>

    <!-- Student Cards -->
    <div class="space-y-6">
        @forelse($studentsToDisplay as $studentItem)
            @php
                $indCount = $studentItem->getIndividualParticipationCount();
                $allEntries = $studentItem->entries
                    ->merge($studentItem->participations ?? collect())
                    ->unique('id');
                $groupCount = $allEntries->where('program.type', 'group')->count();
            @endphp
            <div x-data="{
                student: {
                    id: {{ $studentItem->id }},
                    name: '{{ addslashes($studentItem->name) }}',
                    chest: '{{ ltrim((string)($studentItem->chest_number ?: ($studentItem->student_id ?: $studentItem->id)), '#') }}',
                    zone: '{{ addslashes($studentItem->category ?? 'ZONE A') }}',
                    class: '{{ addslashes($studentItem->class_level ?? '-') }}',
                    indCount: {{ $indCount }},
                    groupCount: {{ $groupCount }},
                    entries: [
                        @foreach($allEntries as $entry)
                            {
                                id: {{ $entry->id }},
                                program_id: {{ $entry->program_id }},
                                program_code: '{{ addslashes($entry->program?->code ?: (string)$entry->program?->id) }}',
                                program_name: '{{ addslashes($entry->program?->name ?? '') }}',
                                zone_name: '{{ addslashes($entry->program?->eligibility ?? ($entry->program?->zone?->name ?? 'A Zone')) }}',
                                type: '{{ $entry->program?->type }}',
                                is_stage: '{{ $entry->program?->is_stage ? 'Stage' : 'Non-stage' }}'
                            },
                        @endforeach
                    ]
                }
            }" :class="isStudentExcluded(student.id) ? 'opacity-40 border-dashed bg-gray-50/50 print-hidden-section' : ''"
               class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden avoid-break transition-all">
                <!-- Header -->
                <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 grid grid-cols-2 sm:grid-cols-6 gap-3 text-xs font-sora items-center">
                    <div>
                        <span class="text-gray-400 font-medium">Name:</span>
                        <span class="font-bold text-gray-800 ml-1 capitalize" x-text="student.name"></span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Chest No:</span>
                        <span class="font-bold text-gray-800 ml-1 font-mono" x-text="student.chest"></span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Zone:</span>
                        <span class="font-bold text-gray-800 ml-1" x-text="student.zone"></span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Team:</span>
                        <span class="font-bold text-gray-800 ml-1">{{ $group->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Class:</span>
                        <span class="font-bold text-gray-800 ml-1" x-text="student.class"></span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <span class="text-gray-400 font-medium">Participation:</span>
                            <span class="font-bold ml-1" :class="student.indCount >= 5 ? 'text-brand-orange' : 'text-emerald-700'" x-text="student.indCount + '/5 Ind'"></span>
                            <template x-if="student.groupCount > 0">
                                <span class="text-gray-500 font-normal" x-text="'(' + student.groupCount + ' Group)'"></span>
                            </template>
                        </div>
                        <label class="no-print inline-flex items-center gap-1.5 cursor-pointer bg-white px-2 py-1 rounded-lg border border-gray-200 text-xs font-semibold hover:border-brand-orange transition select-none">
                            <input type="checkbox"
                                   :checked="!isStudentExcluded(student.id)"
                                   @change="toggleStudent(student.id)"
                                   class="rounded text-brand-orange focus:ring-0">
                            <span x-text="isStudentExcluded(student.id) ? 'Excluded' : 'In PDF'"
                                  :class="isStudentExcluded(student.id) ? 'text-gray-400 font-normal italic' : 'text-gray-800 font-bold'"></span>
                        </label>
                    </div>

                    <!-- Add Program Action Button -->
                    <template x-if="isRegistrationOpen">
                        <div class="col-span-2 sm:col-span-6 flex items-center justify-between pt-2 border-t border-gray-100 sm:border-0 sm:pt-1 no-print">
                            <template x-if="student.indCount < 5">
                                <button type="button"
                                        @click="openAddModal(student)"
                                        class="px-3.5 py-1.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 text-xs font-bold font-sora shadow-xs flex items-center gap-1.5 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    + Add Program
                                </button>
                            </template>
                            <template x-if="student.indCount >= 5">
                                <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold font-sora flex items-center gap-1">
                                    5/5 Full • Maximum individual limit reached. Use Swap or Remove to change programs.
                                </span>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm font-sora">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider font-sora">
                                <th class="px-4 py-3 text-center w-12">No</th>
                                <th class="px-4 py-3">Program Code</th>
                                <th class="px-6 py-3">Program Name</th>
                                <th class="px-4 py-3">Zone</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Stage</th>
                                <template x-if="isRegistrationOpen">
                                    <th class="px-4 py-3 text-right no-print">Actions</th>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(entry, index) in student.entries" :key="entry.id">
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-4 py-3 text-center text-gray-500 text-xs font-medium font-mono" x-text="index + 1"></td>
                                    <td class="px-4 py-3 text-gray-700 font-mono text-xs" x-text="entry.program_code"></td>
                                    <td class="px-6 py-3 text-gray-900 font-medium capitalize" x-text="entry.program_name"></td>
                                    <td class="px-4 py-3 text-gray-600 text-xs" x-text="entry.zone_name"></td>
                                    <td class="px-4 py-3 text-gray-600 text-xs">
                                        <template x-if="entry.type === 'group'">
                                            <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 font-semibold text-[11px]">Group</span>
                                        </template>
                                        <template x-if="entry.type !== 'group'">
                                            <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-semibold text-[11px]">Individual</span>
                                        </template>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 text-xs" x-text="entry.is_stage"></td>
                                    <template x-if="isRegistrationOpen">
                                        <td class="px-4 py-3 text-right text-xs no-print">
                                            <template x-if="entry.type !== 'group'">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <button type="button"
                                                            @click="openSwapModal(student, entry)"
                                                            title="Swap this competition with another program"
                                                            class="px-2.5 py-1 rounded-lg bg-orange-50 text-brand-orange hover:bg-orange-100 font-semibold text-xs transition flex items-center gap-1 font-sora">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                                        Swap
                                                    </button>
                                                    <button type="button"
                                                            @click="removeStudentFromProgram(student, entry)"
                                                            title="Remove student from this program"
                                                            class="px-2.5 py-1 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-semibold text-xs transition flex items-center gap-1 font-sora">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        Remove
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="entry.type === 'group'">
                                                <div class="flex items-center justify-end">
                                                    <a :href="'{{ url('leader/registrations') }}/' + entry.id + '/edit'"
                                                       class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-semibold text-xs transition font-sora">
                                                        Edit Team
                                                    </a>
                                                </div>
                                            </template>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                            <tr x-show="student.entries.length === 0">
                                <td :colspan="isRegistrationOpen ? 7 : 6" class="px-6 py-8 text-center text-gray-400 text-xs">
                                    No registered programs found for this student.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-gray-400">
                No students found.
            </div>
        @endforelse
    </div>

    <!-- Live Swap & Add Modal -->
    <div x-show="activeModal"
         x-transition.opacity
         class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 font-sora relative"
             @click.outside="closeModal()">
            
            <!-- Header -->
            <div class="flex items-start justify-between pb-4 border-b border-gray-100">
                <div>
                    <h3 class="text-lg font-bold text-gray-900" x-text="activeModal === 'swap' ? 'Swap Competition Program' : 'Register for New Program'"></h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Student: <strong class="text-gray-800" x-text="modalStudent?.name"></strong> (<span class="font-mono font-bold text-brand-orange" x-text="modalStudent?.chest"></span>)
                    </p>
                </div>
                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Alert messages -->
            <div x-show="feedbackError" class="mt-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-medium" x-text="feedbackError"></div>
            <div x-show="feedbackSuccess" class="mt-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium" x-text="feedbackSuccess"></div>

            <div class="space-y-4 mt-4 text-xs">
                <!-- Current Program Info (for swap) -->
                <template x-if="activeModal === 'swap' && modalFromEntry">
                    <div class="p-3 bg-orange-50/70 border border-orange-200 rounded-xl">
                        <span class="text-[10px] uppercase font-bold text-orange-600 block">Current Program Being Replaced:</span>
                        <span class="text-sm font-bold text-gray-900 block mt-0.5" x-text="modalFromEntry.program_name"></span>
                        <span class="text-[11px] text-gray-500 font-mono" x-text="'Code: ' + modalFromEntry.program_code + ' • Zone: ' + modalFromEntry.zone_name"></span>
                    </div>
                </template>

                <!-- Search destination program -->
                <div>
                    <label class="block font-semibold text-gray-700 mb-1" x-text="activeModal === 'swap' ? 'Select Replacement Program' : 'Select Program to Register'"></label>
                    <div class="space-y-2">
                        <select x-model="selectedToProgramId"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                            <option value="">-- Choose Program from Dropdown --</option>
                            <template x-for="p in eligibleProgramsForModal" :key="'opt-' + p.id">
                                <option :value="p.id" x-text="p.name + ' [' + p.code + '] (' + p.remaining + ' slot(s) left)'"></option>
                            </template>
                        </select>
                        <div class="relative">
                            <input type="text"
                                   x-model="programSearch"
                                   placeholder="Or type to search program list below..."
                                   class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                        </div>
                    </div>
                </div>

                <!-- Programs List Selection -->
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Available Programs (<span x-text="eligibleProgramsForModal.length"></span> available):</label>
                    <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-xl divide-y divide-gray-100 bg-white">
                        <template x-for="p in eligibleProgramsForModal" :key="p.id">
                            <label class="p-2.5 hover:bg-orange-50/60 flex items-center justify-between cursor-pointer transition">
                                <div class="flex items-center gap-2">
                                    <input type="radio"
                                           name="modal_selected_program"
                                           :value="p.id"
                                           :checked="selectedToProgramId == p.id"
                                           @change="selectedToProgramId = p.id"
                                           class="text-brand-orange focus:ring-brand-orange">
                                    <div>
                                        <span class="font-bold text-gray-900 block" x-text="p.name"></span>
                                        <span class="text-[10px] text-gray-500 font-mono" x-text="'Code: ' + p.code + ' • ' + p.zone_name + ' • ' + (p.is_stage ? 'Stage' : 'Non-stage')"></span>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 font-mono" x-text="p.remaining + ' slot(s) left'"></span>
                            </label>
                        </template>
                        <div x-show="eligibleProgramsForModal.length === 0" class="p-4 text-center text-gray-400 text-xs">
                            No eligible programs found with open quota for this zone.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-end gap-2 text-xs">
                <button type="button"
                        @click="closeModal()"
                        class="px-4 py-2 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 font-semibold transition">
                    Cancel
                </button>
                <template x-if="activeModal === 'swap'">
                    <button type="button"
                            @click="submitSwap()"
                            :disabled="!selectedToProgramId || isSubmitting"
                            :class="!selectedToProgramId || isSubmitting ? 'opacity-50 cursor-not-allowed' : 'hover:bg-orange-600'"
                            class="px-5 py-2 rounded-xl bg-brand-orange text-white font-bold transition flex items-center gap-1.5 shadow-xs">
                        <span x-text="isSubmitting ? 'Swapping...' : 'Confirm Program Swap'"></span>
                    </button>
                </template>
                <template x-if="activeModal === 'add'">
                    <button type="button"
                            @click="submitAdd()"
                            :disabled="!selectedToProgramId || isSubmitting"
                            :class="!selectedToProgramId || isSubmitting ? 'opacity-50 cursor-not-allowed' : 'hover:bg-emerald-700'"
                            class="px-5 py-2 rounded-xl bg-emerald-600 text-white font-bold transition flex items-center gap-1.5 shadow-xs">
                        <span x-text="isSubmitting ? 'Registering...' : 'Register Student'"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
