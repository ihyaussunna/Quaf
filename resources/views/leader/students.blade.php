@extends('layouts.leader', ['title' => 'Group Roster: ' . $group->name])

@section('content')
<div class="space-y-6" x-data="studentRosterManager()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
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
                        <th class="px-6 py-4">Chest Number</th>
                        <th class="px-6 py-4">Full Name</th>
                        <th class="px-6 py-4">Zone</th>
                        <th class="px-6 py-4">Enrolled Programs</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                        <tr class="hover:bg-slate-50/80 transition">
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

                                    <a href="{{ route('verify.student', $student->qr_token ?? $student->student_id) }}"
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
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
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
