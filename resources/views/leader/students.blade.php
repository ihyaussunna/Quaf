@extends('layouts.leader', ['title' => 'Group Roster: ' . $group->name])

@section('content')
<div class="space-y-6" x-data="studentRosterManager()">
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

    <!-- Status Notice Banner -->
    @if($isEditingOpen)
        <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200/80 text-amber-900 text-xs font-sora flex items-start sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>
                    <strong>Spelling Correction Allowed:</strong> വിദ്യാർത്ഥികളുടെ പേരിലുള്ള അക്ഷരത്തെറ്റുകൾ തിരുത്താൻ 'Edit Name' ബട്ടൺ ഉപയോഗിക്കുക. മാറ്റങ്ങൾ ഡിജിറ്റൽ പാസിലും സർട്ടിഫിക്കറ്റിലും തത്സമയം അപ്‌ഡേറ്റ് ചെയ്യപ്പെടും.
                </span>
            </div>
        </div>
    @else
        <div class="p-4 rounded-2xl bg-slate-100 border border-slate-300 text-slate-700 text-xs font-sora flex items-start sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>
                    <strong>Student Name Editing Closed:</strong> വിദ്യാർത്ഥികളുടെ വിവരങ്ങൾ തിരുത്താനുള്ള സൗകര്യം അഡ്മിൻ ബ്ലോക്ക് ചെയ്തിരിക്കുന്നു. എന്തെങ്കിലും മാറ്റങ്ങൾ വരുത്തേണ്ടതുണ്ടെങ്കിൽ അഡ്മിനുമായി ബന്ധപ്പെടുക.
                </span>
            </div>
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
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

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-sora font-semibold shadow-2xs flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->has('student_editing'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-sora font-semibold shadow-2xs">
            {{ $errors->first('student_editing') }}
        </div>
    @endif

    <!-- Students Table (Light Theme) -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
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
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse($students as $student)
                        @php
                            $chestNo = ltrim((string)($student->chest_number ?: $student->student_id), '#');
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900 font-mono text-sm">{{ $chestNo ?: '---' }}</td>
                            <td class="px-6 py-4 font-bold text-slate-900">
                                <span :id="'student-name-' + {{ $student->id }}">{{ $student->name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-semibold">
                                    {{ $student->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5 max-w-md">
                                    @forelse($student->entries as $entry)
                                        <span class="px-2.5 py-1 rounded-lg text-[11px] bg-slate-100 border border-slate-200 text-slate-700 font-medium">
                                            {{ $entry->program->name }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 text-xs">No entries yet</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($isEditingOpen)
                                        <button type="button"
                                                @click="openEditModal({{ $student->id }}, '{{ addslashes($student->name) }}', '{{ $chestNo }}', '{{ addslashes($student->category) }}')"
                                                class="px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 font-sora text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            Edit Name
                                        </button>
                                    @else
                                        <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-400 text-[10px] font-mono font-semibold flex items-center gap-1 cursor-not-allowed" title="Editing closed by admin">
                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            Locked
                                        </span>
                                    @endif
                                    <a href="{{ route('verify.student', $student->qr_token) }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-[#005c94] border border-slate-200 font-sora text-xs font-semibold transition">
                                        Pass &nearr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">No students found matching your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $students->links() }}
    </div>

    <!-- Edit Student Name Modal -->
    <div x-show="modalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.outside="if(!isSaving) modalOpen = false"
             class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-slate-200 space-y-5 text-left transform transition-all">
            
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-lg font-sora font-black text-slate-900">Edit Student Name</h3>
                    <p class="text-xs text-slate-500 font-sora mt-0.5">തിരുത്തലുകൾ (Spelling Correction) വരുത്തുക</p>
                </div>
                <button type="button" @click="modalOpen = false" :disabled="isSaving" class="text-slate-400 hover:text-slate-600 text-lg font-bold font-mono">
                    ✕
                </button>
            </div>

            <!-- Student Badges -->
            <div class="flex items-center gap-2 text-xs font-mono">
                <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-800 font-bold">
                    Chest: <span x-text="studentChest"></span>
                </span>
                <span class="px-2.5 py-1 rounded-xl bg-amber-50 text-amber-900 border border-amber-200 font-bold" x-text="studentZone"></span>
            </div>

            <form @submit.prevent="submitEditName()">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-sora uppercase tracking-wider text-slate-700 font-bold mb-1.5">
                            Correct Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               x-model="formName"
                               x-ref="nameInput"
                               required
                               minlength="2"
                               maxlength="255"
                               placeholder="Enter correct name..."
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-brand-orange focus:bg-white font-sora transition">
                        <span class="text-[11px] font-sora text-slate-500 block mt-1.5 leading-relaxed">
                            ശ്രദ്ധിക്കുക: അക്ഷരത്തെറ്റുകൾ തിരുത്താൻ മാത്രം ഇത് ഉപയോഗിക്കുക. ഈ പേരാണ് ഡിജിറ്റൽ പാസിലും സർട്ടിഫിക്കറ്റിലും പ്രിന്റ് ചെയ്യപ്പെടുക.
                        </span>
                    </div>

                    <template x-if="errorMessage">
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-sora font-semibold" x-text="errorMessage"></div>
                    </template>

                    <template x-if="successMessage">
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-sora font-semibold" x-text="successMessage"></div>
                    </template>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button"
                                @click="modalOpen = false"
                                :disabled="isSaving"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-sora font-bold hover:bg-slate-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                :disabled="isSaving || !formName.trim()"
                                class="px-5 py-2.5 rounded-xl bg-brand-orange text-white text-xs font-sora font-bold hover:bg-orange-600 transition flex items-center gap-2 shadow-xs disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSaving">Save Changes</span>
                            <span x-show="isSaving">Saving...</span>
                        </button>
                    </div>
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
