@extends('layouts.admin', ['title' => 'Create Program Entry & Participant Roster'])

@section('content')
<script>
window.quafRegistrationData = {
    programs: {!! json_encode($programsData, JSON_UNESCAPED_UNICODE) !!},
    students: {!! json_encode($studentsData, JSON_UNESCAPED_UNICODE) !!},
    groups: {!! json_encode($groupsData, JSON_UNESCAPED_UNICODE) !!},
    zones: {!! json_encode($zonesData, JSON_UNESCAPED_UNICODE) !!},
    entriesByGroupAndProgram: {!! json_encode($entriesByGroupAndProgram, JSON_UNESCAPED_UNICODE) !!}
};

function adminRegistrationManager() {
    return {
        selectedGroupId: '{{ old('group_id', $selectedGroupId ?? '') }}',
        selectedZone: '{{ old('zone', '') }}',
        selectedProgramId: '{{ old('program_id', '') }}',
        selectedStudents: [],
        leaderStudentId: null,
        studentSearch: '',
        showStudentDropdown: false,
        isSubmitting: false,
        feedbackSuccess: '',
        feedbackError: '',

        allPrograms: window.quafRegistrationData ? (window.quafRegistrationData.programs || []) : [],
        allStudents: window.quafRegistrationData ? (window.quafRegistrationData.students || []) : [],
        allGroups: window.quafRegistrationData ? (window.quafRegistrationData.groups || []) : [],
        allZones: window.quafRegistrationData ? (window.quafRegistrationData.zones || []) : [],
        entriesMap: window.quafRegistrationData ? (window.quafRegistrationData.entriesByGroupAndProgram || {}) : {},

        init() {
            window.quafAdminRegManagerInstance = this;
            if (!this.selectedGroupId && this.allGroups.length > 0) {
                this.selectedGroupId = String(this.allGroups[0].id);
            }
            this.updateProgramDropdown();
            if (this.selectedZone && this.selectedProgramId) {
                this.onProgramChange(this.selectedProgramId);
            }
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

        get currentGroup() {
            return this.allGroups.find(g => String(g.id) === String(this.selectedGroupId)) || null;
        },

        get currentProgram() {
            return this.allPrograms.find(p => String(p.id) === String(this.selectedProgramId)) || null;
        },

        get isGroup() {
            return this.currentProgram ? this.currentProgram.type === 'group' : false;
        },

        get participantLimit() {
            return this.currentProgram ? (this.currentProgram.limit || 1) : 1;
        },

        getProgramEnrolledStudents(progId) {
            if (!this.selectedGroupId || !progId) return [];
            const grpEntries = this.entriesMap[this.selectedGroupId];
            if (!grpEntries) return [];
            return grpEntries[progId] || [];
        },

        getProgramQuotaInfo(p) {
            const enrolledList = this.getProgramEnrolledStudents(p.id);
            const enrolled = enrolledList.length;
            const remaining = Math.max(0, p.limit - enrolled);
            const isFull = (enrolled >= p.limit);
            const isRegistered = (enrolled > 0);

            let tag = '';
            let statusLabel = '';
            let statusType = 'unregistered';

            if (enrolled === 0) {
                tag = `[0/${p.limit} • ${p.limit} left]`;
                statusLabel = `0/${p.limit} • ${p.limit} left`;
                statusType = 'unregistered';
            } else if (enrolled < p.limit) {
                tag = `[${enrolled}/${p.limit} • ${remaining} left]`;
                statusLabel = `${enrolled}/${p.limit} • ${remaining} left`;
                statusType = 'partial';
            } else {
                tag = `[Complete ${enrolled}/${p.limit}]`;
                statusLabel = `Complete ${enrolled}/${p.limit}`;
                statusType = 'full';
            }

            return {
                enrolled,
                remaining,
                isFull,
                isRegistered,
                enrolledStudents: enrolledList,
                tag,
                statusLabel,
                statusType
            };
        },

        get filteredPrograms() {
            if (!this.selectedZone) return [];
            const z = this.normalizeZone(this.selectedZone);
            return this.allPrograms.filter(p => {
                const pZone = this.normalizeZone(p.zone_name);
                return pZone === z || (z === 'mix' && (p.is_mix_zone || pZone === 'mix'));
            }).map(p => {
                const qInfo = this.getProgramQuotaInfo(p);
                return {
                    ...p,
                    ...qInfo
                };
            });
        },

        get eligibleStudents() {
            if (!this.selectedGroupId) return [];
            const gId = String(this.selectedGroupId);
            const z = this.normalizeZone(this.selectedZone);
            const isMix = (z === 'mix');

            return this.allStudents.filter(s => {
                if (String(s.group_id) !== gId) return false;
                if (!this.selectedZone) return true;
                const sZone = this.normalizeZone(s.zone_name);
                return isMix || sZone === z;
            });
        },

        get availableStudents() {
            const addedIds = this.selectedStudents.map(s => Number(s.id));
            const q = (this.studentSearch || '').toLowerCase().trim();
            const base = this.eligibleStudents.filter(s => !addedIds.includes(Number(s.id)));
            if (!q) return base.slice(0, 40);
            return base.filter(s => 
                (s.name || '').toLowerCase().includes(q) || 
                String(s.chest || '').toLowerCase().includes(q) ||
                String(s.id || '').includes(q)
            ).slice(0, 40);
        },

        isStudentIndividualLimitReached(st) {
            if (this.isGroup) return false;
            const enrolledInCurrent = this.getProgramEnrolledStudents(this.selectedProgramId);
            const isEnrolled = enrolledInCurrent.some(es => Number(es.id) === Number(st.id));
            const count = isEnrolled ? Math.max(0, (st.individual_count || 0) - 1) : (st.individual_count || 0);
            return count >= 5;
        },

        updateProgramDropdown() {
            const select = this.$refs.programSelect || document.getElementById('program_select');
            if (!select) return;

            const zone = this.selectedZone ? this.selectedZone.trim() : '';
            const currentProgId = String(this.selectedProgramId || '');

            select.options.length = 0;

            const defaultPrompt = zone 
                ? `-- Choose Competition Program (${this.filteredPrograms.length} available) --` 
                : '-- Please select a Zone first --';

            select.options[0] = new Option(defaultPrompt, '', false, !currentProgId);

            let matched = false;
            this.filteredPrograms.forEach((p, idx) => {
                const isSelected = String(p.id) === currentProgId;
                if (isSelected) matched = true;
                const closedLabel = !p.is_registration_open ? ' [Closed / ക്ലോസ് ചെയ്തു]' : '';
                select.options[idx + 1] = new Option(`${p.name} ${p.tag}${closedLabel}`, String(p.id), false, isSelected);
            });

            if (matched && currentProgId) {
                select.value = currentProgId;
            } else if (!matched && this.filteredPrograms.length > 0 && currentProgId) {
                select.value = '';
                this.selectedProgramId = '';
            }
        },

        onGroupChange() {
            this.feedbackSuccess = '';
            this.feedbackError = '';
            this.updateProgramDropdown();
            if (this.selectedProgramId) {
                this.onProgramChange(this.selectedProgramId);
            } else {
                this.selectedStudents = [];
                this.leaderStudentId = null;
            }
        },

        onZoneChange() {
            this.selectedProgramId = '';
            this.selectedStudents = [];
            this.leaderStudentId = null;
            this.studentSearch = '';
            this.feedbackSuccess = '';
            this.feedbackError = '';

            const progs = this.filteredPrograms;
            const firstOpen = progs.find(p => !p.is_full) || (progs.length > 0 ? progs[0] : null);
            if (firstOpen) {
                this.selectedProgramId = String(firstOpen.id);
            }

            this.updateProgramDropdown();
            this.onProgramChange(this.selectedProgramId);
        },

        onProgramChange(newId) {
            if (newId !== undefined && newId !== null) {
                this.selectedProgramId = String(newId);
            }
            const select = this.$refs.programSelect || document.getElementById('program_select');
            if (select && select.value !== this.selectedProgramId) {
                select.value = this.selectedProgramId;
            }

            this.selectedStudents = [];
            this.leaderStudentId = null;
            this.studentSearch = '';
            this.feedbackSuccess = '';
            this.feedbackError = '';

            if (this.selectedProgramId && this.selectedGroupId) {
                const enrolled = this.getProgramEnrolledStudents(this.selectedProgramId);
                if (enrolled && enrolled.length > 0) {
                    this.selectedStudents = enrolled.map(s => ({ ...s }));
                    if (this.isGroup) {
                        const ldr = this.selectedStudents.find(s => s.is_leader);
                        this.leaderStudentId = ldr ? ldr.id : (this.selectedStudents[0] ? this.selectedStudents[0].id : null);
                    } else {
                        this.leaderStudentId = this.selectedStudents[0] ? this.selectedStudents[0].id : null;
                    }
                }
            }
        },

        addStudent(st) {
            if (this.selectedStudents.length >= this.participantLimit) return;
            if (this.selectedStudents.some(s => Number(s.id) === Number(st.id))) return;
            if (!this.isGroup && this.isStudentIndividualLimitReached(st)) {
                this.feedbackError = `"${st.name}" ഇതിനകം പരമാവധി 5 വ്യക്തിഗത (Individual) മത്സരങ്ങളിൽ പങ്കെടുത്തിട്ടുണ്ട്. ഗ്രൂപ്പ് ഇനങ്ങളിൽ മാത്രമേ ഇനി ചേർക്കാനാവൂ.`;
                return;
            }
            this.feedbackError = '';
            this.selectedStudents.push({
                id: st.id,
                chest: st.chest,
                name: st.name,
                class: st.class,
                zone_name: st.zone_name,
                is_leader: false
            });
            if (!this.leaderStudentId) {
                this.leaderStudentId = st.id;
            }
            this.studentSearch = '';
            this.showStudentDropdown = false;
        },

        removeStudent(stId) {
            const removedId = Number(stId);
            this.selectedStudents = this.selectedStudents.filter(s => Number(s.id) !== removedId);
            if (this.leaderStudentId == stId) {
                this.leaderStudentId = this.selectedStudents.length > 0 ? this.selectedStudents[0].id : null;
            }
        },

        async deleteCurrentProgramRegistration() {
            if (!this.currentProgram || !this.selectedGroupId) return;
            const groupName = this.currentGroup ? this.currentGroup.name : 'Selected Group';
            if (!confirm(`Are you sure you want to remove all registered participants for "${this.currentProgram.name}" (${groupName})?`)) return;

            this.isSubmitting = true;
            this.feedbackSuccess = '';
            this.feedbackError = '';

            try {
                const res = await fetch(`{{ url('admin/registrations/by-program') }}/${this.currentProgram.id}?group_id=${this.selectedGroupId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.success) {
                    this.feedbackError = data.message || 'Failed to remove program registration.';
                    return;
                }

                if (!this.entriesMap[this.selectedGroupId]) {
                    this.entriesMap[this.selectedGroupId] = {};
                }
                this.entriesMap[this.selectedGroupId][this.currentProgram.id] = [];

                this.updateStudentCounts(data.updated_students);

                this.selectedStudents = [];
                this.leaderStudentId = null;
                this.updateProgramDropdown();
                this.feedbackSuccess = data.message || `All registrations for "${this.currentProgram.name}" (${groupName}) removed successfully.`;
            } catch (err) {
                this.feedbackError = 'Connection error while removing registration.';
            } finally {
                this.isSubmitting = false;
            }
        },

        updateStudentCounts(updatedStudents) {
            if (!Array.isArray(updatedStudents)) return;
            updatedStudents.forEach(us => {
                const found = this.allStudents.find(s => s.id == us.id);
                if (found) {
                    found.individual_count = us.individual_count;
                    found.has_reached_individual_limit = !!us.has_reached_individual_limit;
                }
            });
        },

        async submitRegistration(e) {
            if (this.isSubmitting) return;
            if (!this.selectedGroupId) {
                this.feedbackError = 'Please select a competing Group first.';
                return;
            }
            if (!this.currentProgram) {
                this.feedbackError = 'Please select a competition program first.';
                return;
            }
            if (this.selectedStudents.length > this.participantLimit) {
                this.feedbackError = `Maximum limit is ${this.participantLimit} participant(s). You have selected ${this.selectedStudents.length}.`;
                return;
            }

            const currentEnrolled = this.getProgramEnrolledStudents(this.selectedProgramId);
            if (this.selectedStudents.length === 0 && currentEnrolled.length === 0) {
                this.feedbackError = 'Please select at least one student participant.';
                return;
            }

            this.isSubmitting = true;
            this.feedbackSuccess = '';
            this.feedbackError = '';

            const form = e.target;
            const formData = new FormData(form);

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok || !data.success) {
                    this.feedbackError = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Registration failed. Please check candidate eligibility.');
                    return;
                }

                const enrolledProg = this.currentProgram;
                const enrolledProgName = enrolledProg ? enrolledProg.name : 'Program';
                const freshEnrolled = data.enrolled_students || this.selectedStudents;

                if (!this.entriesMap[this.selectedGroupId]) {
                    this.entriesMap[this.selectedGroupId] = {};
                }
                this.entriesMap[this.selectedGroupId][this.currentProgram.id] = freshEnrolled;

                this.updateStudentCounts(data.updated_students);

                this.selectedStudents = freshEnrolled.map(s => ({ ...s }));
                if (this.isGroup) {
                    const ldr = this.selectedStudents.find(s => s.is_leader);
                    this.leaderStudentId = ldr ? ldr.id : (this.selectedStudents[0] ? this.selectedStudents[0].id : null);
                } else {
                    this.leaderStudentId = this.selectedStudents[0] ? this.selectedStudents[0].id : null;
                }

                this.studentSearch = '';
                this.updateProgramDropdown();

                this.feedbackSuccess = data.message || `Saved and updated participants for "${enrolledProgName}".`;
            } catch (err) {
                this.feedbackError = 'Connection error: Could not complete registration. Please check your network.';
            } finally {
                this.isSubmitting = false;
            }
        }
    };
}
</script>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.registrations.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-1.5 block font-semibold">&larr; Back to Program Entries</a>
            <h1 class="text-2xl sm:text-3xl font-sora font-black text-slate-900">Create Program Entry & Participant Roster</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">Direct festival desk registration with group filter, zone validation, candidate quota meter, and conflict checking.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.registrations.stats') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-mono font-bold text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-2xs">
                Registration Matrix
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-300 text-red-900 text-xs space-y-1 shadow-2xs">
            <p class="font-bold flex items-center gap-2 text-sm text-[#be1e2d]">
                <svg class="w-4 h-4 text-[#be1e2d] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Registration Error:
            </p>
            <ul class="list-disc list-inside space-y-0.5 pl-2 font-sora font-medium text-red-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs font-sora font-medium shadow-2xs">
            {{ session('success') }}
        </div>
    @endif

    <!-- Main Registration Form Container with Alpine.js -->
    <div id="enrollment_card" 
         x-data="adminRegistrationManager()" 
         class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm">
        
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-[#f3bd2e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <h2 class="text-lg font-sora font-bold text-slate-900">Enroll Candidates to Program</h2>
            </div>
            
            <template x-if="currentGroup">
                <span class="px-3 py-1 rounded-xl text-xs font-mono font-bold text-white shadow-2xs"
                      :style="'background-color: ' + (currentGroup.color_hex || '#005c94')"
                      x-text="currentGroup.name + ' (' + currentGroup.code + ')'">
                </span>
            </template>
        </div>

        <form method="POST" action="{{ route('admin.registrations.store') }}" 
              @submit.prevent="submitRegistration($event)"
              class="space-y-6">
            @csrf

            <!-- Reactive Feedback Banners -->
            <div x-show="feedbackSuccess" 
                 x-transition 
                 class="p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-400 text-emerald-900 text-xs font-sora font-bold flex items-center justify-between shadow-xs"
                 style="display: none;">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="feedbackSuccess"></span>
                </div>
                <button type="button" @click="feedbackSuccess = ''" class="text-emerald-700 hover:text-emerald-950 p-1 text-base leading-none">&times;</button>
            </div>

            <div x-show="feedbackError" 
                 x-transition 
                 class="p-4 rounded-2xl bg-red-50 border-2 border-red-300 text-red-900 text-xs font-sora font-bold flex items-center justify-between shadow-xs"
                 style="display: none;">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#be1e2d] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span x-text="feedbackError"></span>
                </div>
                <button type="button" @click="feedbackError = ''" class="text-red-700 hover:text-red-950 p-1 text-base leading-none">&times;</button>
            </div>

            <!-- STEP 1: GROUP FILTER DROPDOWN (ADMIN SPECIAL EXTRA DROPDOWN) -->
            <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200 space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                    <label class="block text-xs font-sora uppercase text-slate-800 font-bold">
                        1. Select Competing Group (ഗ്രൂപ്പ് തിരഞ്ഞെടുക്കുക) <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[11px] font-mono text-slate-500">Admin Group Selector</span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-5 gap-2">
                    <template x-for="grp in allGroups" :key="grp.id">
                        <button type="button"
                                @click="selectedGroupId = String(grp.id); onGroupChange()"
                                :class="String(selectedGroupId) === String(grp.id) 
                                    ? 'border-2 text-white font-black shadow-sm ring-2 ring-slate-900/10' 
                                    : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 font-bold'"
                                :style="String(selectedGroupId) === String(grp.id) ? 'background-color: ' + grp.color_hex + '; border-color: ' + grp.color_hex : ''"
                                class="px-3 py-2.5 rounded-xl text-xs font-sora text-center transition-all flex flex-col items-center justify-center gap-0.5">
                            <span class="text-[10px] uppercase font-mono tracking-wider opacity-80" x-text="grp.code"></span>
                            <span class="truncate w-full text-center" x-text="grp.name"></span>
                        </button>
                    </template>
                </div>

                <div class="mt-2">
                    <select name="group_id" 
                            id="group_select"
                            x-model="selectedGroupId" 
                            @change="onGroupChange()" 
                            required 
                            class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] transition-colors font-sora font-semibold">
                        <option value="">-- Choose Competing Group --</option>
                        <template x-for="grp in allGroups" :key="grp.id">
                            <option :value="String(grp.id)" x-text="grp.name + ' (' + grp.code + ')'"></option>
                        </template>
                    </select>
                </div>
                <p class="text-[11px] font-sora text-slate-500">Select the group whose participants you want to register. Quota counts and eligible students will adjust automatically.</p>
            </div>

            <!-- STEP 2: ZONE SELECTION -->
            <div>
                <label class="block text-xs font-sora uppercase text-slate-700 mb-1.5 font-bold">
                    2. Select Zone (സോൺ തിരഞ്ഞെടുക്കുക) <span class="text-red-500">*</span>
                </label>
                <select name="zone" 
                        id="zone_select"
                        x-model="selectedZone" 
                        @change="onZoneChange()" 
                        required 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors font-sora">
                    <option value="">-- Choose Zone --</option>
                    <template x-for="z in allZones" :key="z.id">
                        <option :value="z.name" x-text="z.name"></option>
                    </template>
                </select>
                <p class="text-[11px] font-sora text-slate-500 mt-1">Filter programs and students strictly by zone eligibility.</p>
            </div>

            <!-- STEP 3: PROGRAM SELECTION (FILTERED BY ZONE) -->
            <div>
                <label class="block text-xs font-sora uppercase text-slate-700 mb-1.5 font-bold">
                    3. Select Program (മത്സരം തിരഞ്ഞെടുക്കുക) <span class="text-red-500">*</span>
                </label>
                <select name="program_id" 
                        id="program_select"
                        x-ref="programSelect"
                        x-model="selectedProgramId" 
                        @change="onProgramChange($event.target.value)" 
                        :disabled="!selectedZone"
                        required 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-sora">
                    <option value="">-- Please select a Zone first --</option>
                </select>

                <!-- Program Details & Quota Meter -->
                <template x-if="currentProgram">
                    <div class="mt-2.5 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-sora space-y-2 shadow-2xs">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/60 pb-2">
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 font-sora">
                                    <span class="text-xs font-mono text-slate-400 font-semibold" x-text="'[' + currentProgram.code + '] '"></span>
                                    <span x-text="currentProgram.name"></span>
                                    <span x-show="currentProgram.malayalam_name" class="ml-1 text-slate-600 font-normal" x-text="'(' + currentProgram.malayalam_name + ')'"></span>
                                </h4>
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-200 text-slate-700" x-text="currentProgram.zone_name"></span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold"
                                          :class="currentProgram.is_stage ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'"
                                          x-text="currentProgram.is_stage ? 'Stage Item' : 'Off-Stage Item'"></span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-900"
                                          x-text="currentProgram.type === 'group' ? 'Group Item (Limit: ' + currentProgram.limit + ')' : 'Individual Item (Limit: ' + currentProgram.limit + ')'"></span>
                                </div>
                            </div>

                            <!-- Live Quota Status Tag for Selected Group -->
                            <div>
                                <template x-if="getProgramQuotaInfo(currentProgram).enrolled === 0">
                                    <span class="px-3 py-1 rounded-xl bg-slate-200 text-slate-700 font-bold">
                                        Not Registered (0 / <span class="font-mono" x-text="currentProgram.limit"></span> slots)
                                    </span>
                                </template>
                                <template x-if="getProgramQuotaInfo(currentProgram).enrolled > 0 && !getProgramQuotaInfo(currentProgram).isFull">
                                    <span class="px-3 py-1 rounded-xl bg-amber-100 text-amber-900 border border-amber-300 font-bold flex items-center gap-1.5">
                                        <span>Partially Registered (<span class="font-mono" x-text="getProgramQuotaInfo(currentProgram).enrolled"></span>/<span class="font-mono" x-text="currentProgram.limit"></span>)</span>
                                        <span class="text-amber-700 font-black">&mdash; <span class="font-mono" x-text="getProgramQuotaInfo(currentProgram).remaining"></span> Slot(s) Left</span>
                                    </span>
                                </template>
                                <template x-if="getProgramQuotaInfo(currentProgram).isFull">
                                    <span class="px-3 py-1 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold">
                                        Quota Full (<span class="font-mono" x-text="getProgramQuotaInfo(currentProgram).enrolled"></span>/<span class="font-mono" x-text="currentProgram.limit"></span> slots filled)
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Registration Closure Notice -->
                        <template x-if="!currentProgram.is_registration_open">
                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-sora space-y-0.5">
                                <div class="flex items-center gap-2 font-bold text-rose-800">
                                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span x-text="currentProgram.closure_reason_ml || 'മത്സരത്തിന്റെ രജിസ്ട്രേഷൻ ക്ലോസ് ചെയ്തിരിക്കുന്നു (Closed).'"></span>
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-rose-200 text-rose-900 font-mono font-bold">Admin Override Allowed</span>
                                </div>
                                <p class="text-[11px] text-rose-600 font-mono pl-6" x-text="currentProgram.closure_reason || 'Registration closed for group leaders. Desk admins may still register participants.'"></p>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- STEP 4: PARTICIPANTS ROSTER & SELECTION -->
            <template x-if="currentProgram">
                <div class="space-y-4 pt-2 border-t border-slate-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <label class="block text-xs font-sora uppercase text-slate-800 font-bold">
                                4. Selected Participants & Quota Roster <span class="text-red-500">*</span>
                            </label>
                            <p class="text-[11px] font-sora text-slate-500">
                                <span x-show="selectedStudents.length > 0">
                                    Showing current candidates for <strong x-text="currentGroup ? currentGroup.name : 'Group'"></strong>. You can <strong>Remove</strong> any student to open a slot and search to add a replacement.
                                </span>
                                <span x-show="selectedStudents.length === 0">
                                    Add up to <strong class="text-slate-800 font-mono" x-text="participantLimit"></strong> participant(s) for <strong x-text="currentGroup ? currentGroup.name : 'Group'"></strong>.
                                </span>
                                <span x-show="isGroup" class="text-amber-700 font-semibold ml-1">(Designate one student as Team Leader)</span>
                            </p>
                        </div>
                        <div class="text-xs font-sora px-3 py-1.5 rounded-xl border font-bold"
                             :class="selectedStudents.length >= participantLimit ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-orange-50 border-orange-200 text-orange-800'">
                            Enrolled: <span class="font-mono" x-text="selectedStudents.length"></span> / <span class="font-mono" x-text="participantLimit"></span> Slot(s)
                        </div>
                    </div>

                    <!-- Hidden Form Inputs for Backend Submission -->
                    <input type="hidden" name="leader_id" :value="leaderStudentId">
                    <input type="hidden" name="student_id" :value="leaderStudentId || (selectedStudents[0] ? selectedStudents[0].id : '')">
                    <template x-for="st in selectedStudents" :key="st.id">
                        <input type="hidden" name="student_ids[]" :value="st.id">
                    </template>

                    <!-- Selected Students List -->
                    <div class="rounded-2xl border border-slate-200 divide-y divide-slate-100 overflow-hidden bg-slate-50/50">
                        <div class="p-3 bg-slate-100/80 font-sora text-xs font-bold text-slate-700 flex items-center justify-between">
                            <span>Selected Candidates (<span class="font-mono" x-text="selectedStudents.length"></span> of <span class="font-mono" x-text="participantLimit"></span>)</span>
                            <span x-show="isGroup" class="text-[11px] text-slate-500">Select Leader (Name appears on result poster)</span>
                        </div>

                        <template x-for="(st, idx) in selectedStudents" :key="st.id">
                            <div class="p-3 bg-white flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <template x-if="isGroup">
                                        <label class="flex items-center gap-1.5 cursor-pointer" title="Click to designate this student as Team Leader">
                                            <input type="radio" 
                                                   name="_ui_leader_choice" 
                                                   :value="st.id" 
                                                   :checked="leaderStudentId == st.id" 
                                                   @change="leaderStudentId = st.id" 
                                                   class="text-[#be1e2d] focus:ring-[#be1e2d]">
                                            <span class="text-[11px] font-sora font-bold" 
                                                  :class="leaderStudentId == st.id ? 'text-[#be1e2d]' : 'text-slate-400'" 
                                                  x-text="leaderStudentId == st.id ? 'LEADER' : 'Member'"></span>
                                        </label>
                                    </template>
                                    
                                    <div class="h-10 w-10 rounded-xl text-white flex flex-col items-center justify-center text-xs shadow-xs flex-shrink-0"
                                         :style="'background-color: ' + (currentGroup ? currentGroup.color_hex : '#005c94')">
                                        <span class="text-[8px] uppercase font-bold opacity-80 leading-tight font-sora">CHEST</span>
                                        <span class="font-mono font-bold" x-text="st.chest"></span>
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-sm text-slate-900 font-sora" x-text="st.name"></span>
                                            <span class="font-mono text-xs font-semibold text-slate-500" x-text="'#' + st.chest"></span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-sora">
                                            <span x-text="'Zone: ' + (st.zone_name || 'N/A')"></span>
                                            <span class="ml-2" x-text="'Class: ' + (st.class || '-')"></span>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" 
                                        @click="removeStudent(st.id)" 
                                        class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 text-xs font-sora font-bold flex items-center gap-1 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Remove
                                </button>
                            </div>
                        </template>

                        <div x-show="selectedStudents.length === 0" class="p-6 text-center text-xs font-sora text-slate-400">
                            No students currently selected for this program. Search and add candidates below.
                        </div>
                    </div>

                    <!-- Search Input to Add Student -->
                    <div x-show="selectedStudents.length < participantLimit" class="relative" @click.outside="showStudentDropdown = false">
                        <label class="block text-xs font-sora uppercase text-slate-700 mb-1.5 font-bold">
                            Search & Add Student (Name or Chest Number)
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   x-model="studentSearch" 
                                   @focus="showStudentDropdown = true" 
                                   @input="showStudentDropdown = true" 
                                   :placeholder="'Search student by name or chest number to add (Slot ' + (selectedStudents.length + 1) + ' of ' + participantLimit + ')...'"
                                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors pr-10 font-sora">
                            <span class="absolute right-3.5 top-3 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                        </div>

                        <!-- Dropdown Search Results -->
                        <div x-show="showStudentDropdown" 
                             class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-xl divide-y divide-slate-100"
                             style="display: none;">
                            <div class="p-2 text-slate-400 text-[10px] uppercase font-sora tracking-wider bg-slate-50 font-bold">
                                Eligible Candidates in <span x-text="selectedZone"></span> (<span x-text="currentGroup ? currentGroup.name : 'Group'"></span>)
                            </div>
                            <template x-for="st in availableStudents" :key="st.id">
                                <button type="button" 
                                        @click="addStudent(st)" 
                                        :disabled="!isGroup && isStudentIndividualLimitReached(st)"
                                        :class="!isGroup && isStudentIndividualLimitReached(st) ? 'opacity-60 cursor-not-allowed bg-slate-50' : 'hover:bg-amber-50'"
                                        class="w-full text-left px-4 py-2.5 flex items-center justify-between gap-3 transition-colors font-sora">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-sm text-slate-800" x-text="st.name"></span>
                                            <template x-if="!isGroup && isStudentIndividualLimitReached(st)">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">5/5 Limit Reached</span>
                                            </template>
                                            <template x-if="!isGroup && !isStudentIndividualLimitReached(st)">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700" x-text="(st.individual_count || 0) + '/5 Ind'"></span>
                                            </template>
                                            <template x-if="isGroup">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700">Group Eligible (No Limit)</span>
                                            </template>
                                        </div>
                                        <span class="text-[11px] text-slate-500 font-sora">
                                            <span class="font-mono font-bold text-slate-800" x-text="'Chest: #' + st.chest"></span> &bull; 
                                            Zone: <span x-text="st.zone_name"></span> &bull; 
                                            Class: <span x-text="st.class || '-'"></span>
                                        </span>
                                    </div>
                                    <div>
                                        <template x-if="!isGroup && isStudentIndividualLimitReached(st)">
                                            <span class="px-2.5 py-1 rounded-lg bg-slate-200 text-slate-500 text-[11px] font-sora font-semibold">
                                                Max 5
                                            </span>
                                        </template>
                                        <template x-if="isGroup || !isStudentIndividualLimitReached(st)">
                                            <span class="px-2.5 py-1 rounded-lg bg-[#be1e2d] text-white text-[11px] font-sora font-bold">
                                                + Add
                                            </span>
                                        </template>
                                    </div>
                                </button>
                            </template>
                            <div x-show="availableStudents.length === 0" class="p-3 text-center text-xs font-sora text-slate-400">
                                No matching eligible students found in <span x-text="currentGroup ? currentGroup.name : 'Group'"></span> for <span x-text="selectedZone"></span>.
                            </div>
                        </div>
                    </div>

                    <div x-show="selectedStudents.length >= participantLimit" class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-sora font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Available quota limit reached (<span class="font-mono" x-text="selectedStudents.length"></span> of <span class="font-mono" x-text="participantLimit"></span>). To replace a student, click Remove above. Click Confirm Entry below to save.</span>
                    </div>
                </div>
            </template>

            <!-- STEP 5: STATUS & NOTES (ADMIN DESK CONTROLS) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-sora uppercase text-slate-700 mb-1.5 font-bold">Registration Status</label>
                    <select name="status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#005c94] focus:bg-white transition-colors font-sora">
                        <option value="verified" {{ old('status', 'verified') == 'verified' ? 'selected' : '' }}>Verified (Direct to Green Room)</option>
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending Verification</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-sora uppercase text-slate-700 mb-1.5 font-bold">Notes (Optional)</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Accompanists, special requests..."
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#005c94] focus:bg-white transition-colors font-sora">
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="pt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100">
                <div>
                    <template x-if="currentProgram && getProgramQuotaInfo(currentProgram).isRegistered">
                        <button type="button" 
                                @click="deleteCurrentProgramRegistration()" 
                                :disabled="isSubmitting"
                                class="px-4 py-2.5 rounded-xl text-xs font-sora font-bold border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 transition-colors flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Remove All Registrations for this Program &amp; Group</span>
                        </button>
                    </template>
                </div>

                <div class="flex items-center gap-3 ml-auto">
                    <a href="{{ route('admin.registrations.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
                    <button type="submit" 
                            :disabled="isSubmitting || !selectedProgramId || !selectedGroupId || (selectedStudents.length === 0 && (!currentProgram || !getProgramQuotaInfo(currentProgram).isRegistered)) || selectedStudents.length > participantLimit"
                            :class="isSubmitting || !selectedProgramId || !selectedGroupId || (selectedStudents.length === 0 && (!currentProgram || !getProgramQuotaInfo(currentProgram).isRegistered)) || selectedStudents.length > participantLimit ? 'opacity-60 cursor-not-allowed' : 'hover:brightness-110 active:scale-95 shadow-md shadow-[#be1e2d]/20 cursor-pointer'"
                            class="px-6 py-3 rounded-xl text-xs font-sora font-bold uppercase tracking-wider bg-[#be1e2d] text-white transition-all flex items-center gap-2">
                        <template x-if="isSubmitting">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </template>
                        <span x-text="isSubmitting ? 'Saving Changes...' : (currentProgram && getProgramQuotaInfo(currentProgram).isRegistered ? 'Save & Update Participants (' + selectedStudents.length + '/' + participantLimit + ')' : (selectedStudents.length > 1 ? 'Confirm Entry (' + selectedStudents.length + ' Students)' : 'Confirm Entry'))"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
