@extends('layouts.leader', ['title' => 'Program Entries & Registrations'])

@section('content')
@php
    $programsData = $eligiblePrograms->map(function ($p) use ($registeredProgramIds) {
        $isReg = in_array($p->id, $registeredProgramIds ?? []);
        $limit = $p->type === 'group' 
            ? max(1, (int) ($p->participant_count ?? $p->max_participants ?? 2))
            : max(1, (int) ($p->max_participants_per_group ?? $p->participant_count ?? 1));
        return [
            'id' => $p->id,
            'code' => $p->code,
            'name' => $p->name,
            'type' => $p->type,
            'zone_id' => $p->zone_id,
            'zone_name' => $p->zone?->name ?? ($p->eligibility ?? 'Mix Zone'),
            'limit' => $limit,
            'is_stage' => (bool)$p->is_stage,
            'is_registered' => $isReg,
        ];
    });

    $studentsData = $students->map(function ($s) {
        return [
            'id' => $s->id,
            'chest' => (string)($s->student_id ?: $s->id),
            'name' => $s->name,
            'class' => $s->class_level ?? '',
            'zone_name' => $s->zone?->name ?? ($s->category ?? 'A Zone'),
        ];
    });

    $zonesData = $zones->map(function ($z) {
        return [
            'id' => $z->id,
            'name' => $z->name,
        ];
    });
@endphp

<script>
window.quafRegistrationData = {
    programs: @json($programsData),
    students: @json($studentsData),
    zones: @json($zonesData)
};

function registrationManager() {
    return {
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
        allZones: window.quafRegistrationData ? (window.quafRegistrationData.zones || []) : [],
        
        init() {
            if (this.selectedZone) {
                this.updateProgramDropdown();
                if (this.selectedProgramId) {
                    const sel = this.$refs.programSelect || document.getElementById('program_select');
                    if (sel) sel.value = this.selectedProgramId;
                }
            }
        },
        
        get currentProgram() {
            return this.allPrograms.find(p => p.id == this.selectedProgramId) || null;
        },
        
        get isGroup() {
            return this.currentProgram ? this.currentProgram.type === 'group' : false;
        },
        
        get participantLimit() {
            return this.currentProgram ? (this.currentProgram.limit || 1) : 1;
        },
        
        get filteredPrograms() {
            if (!this.selectedZone) return [];
            const z = this.selectedZone.toLowerCase().trim();
            return this.allPrograms.filter(p => {
                const pZone = (p.zone_name || '').toLowerCase().trim();
                return pZone === z || (z === 'mix zone' && pZone.includes('mix'));
            });
        },
        
        get eligibleStudents() {
            if (!this.selectedZone) return this.allStudents;
            const z = this.selectedZone.toLowerCase().trim();
            return this.allStudents.filter(s => {
                const isMix = z === 'mix zone';
                const sZone = (s.zone_name || '').toLowerCase().trim();
                return isMix || sZone === z;
            });
        },
        
        get availableStudents() {
            const addedIds = this.selectedStudents.map(s => s.id);
            const q = (this.studentSearch || '').toLowerCase().trim();
            const base = this.eligibleStudents.filter(s => !addedIds.includes(s.id));
            if (!q) return base.slice(0, 30);
            return base.filter(s => (s.name || '').toLowerCase().includes(q) || (s.chest || '').toLowerCase().includes(q)).slice(0, 30);
        },
        
        updateProgramDropdown() {
            const select = this.$refs.programSelect || document.getElementById('program_select');
            if (!select) return;
            
            const zone = (this.selectedZone || '').toLowerCase().trim();
            const currentProgId = this.selectedProgramId;
            select.innerHTML = '';
            
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            
            if (!zone) {
                defaultOpt.textContent = '-- Please select a Zone first --';
                select.appendChild(defaultOpt);
                return;
            }
            
            const progs = this.filteredPrograms;
            defaultOpt.textContent = `-- Choose Competition Program (${progs.length} available) --`;
            select.appendChild(defaultOpt);
            
            progs.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                const regStatus = p.is_registered ? ' [REGISTERED ✓]' : ' [UNREGISTERED]';
                const typeInfo = p.type === 'group' ? `Group - Limit: ${p.limit}` : `Individual - Limit: ${p.limit}`;
                opt.textContent = `[${p.code}] ${p.name}${regStatus} (${typeInfo})`;
                if (p.id == currentProgId) {
                    opt.selected = true;
                }
                select.appendChild(opt);
            });
        },
        
        onZoneChange() {
            this.selectedProgramId = '';
            this.selectedStudents = [];
            this.leaderStudentId = null;
            this.studentSearch = '';
            this.feedbackSuccess = '';
            this.feedbackError = '';
            
            this.updateProgramDropdown();
            
            // Auto-select first unregistered program in this zone
            const firstUnreg = this.filteredPrograms.find(p => !p.is_registered);
            if (firstUnreg) {
                this.selectedProgramId = firstUnreg.id;
                const select = this.$refs.programSelect || document.getElementById('program_select');
                if (select) select.value = firstUnreg.id;
            }
            this.onProgramChange();
        },
        
        onProgramChange() {
            this.selectedStudents = [];
            this.leaderStudentId = null;
            this.studentSearch = '';
            this.feedbackSuccess = '';
            this.feedbackError = '';
        },
        
        addStudent(st) {
            if (this.selectedStudents.length >= this.participantLimit) return;
            this.selectedStudents.push(st);
            if (!this.leaderStudentId) {
                this.leaderStudentId = st.id;
            }
            this.studentSearch = '';
            this.showStudentDropdown = false;
        },
        
        removeStudent(stId) {
            this.selectedStudents = this.selectedStudents.filter(s => s.id !== stId);
            if (this.leaderStudentId == stId) {
                this.leaderStudentId = this.selectedStudents.length > 0 ? this.selectedStudents[0].id : null;
            }
        },
        
        async submitRegistration(e) {
            if (this.isSubmitting) return;
            if (!this.currentProgram) {
                this.feedbackError = 'Please select a competition program first.';
                return;
            }
            if (this.selectedStudents.length === 0) {
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
                
                // Registration Success!
                const enrolledProg = this.currentProgram;
                const enrolledProgName = enrolledProg ? enrolledProg.name : 'Program';
                const enrolledCount = this.selectedStudents.length;
                
                // 1. Mark current program registered in state
                if (enrolledProg) {
                    enrolledProg.is_registered = true;
                }
                
                // 2. Clear current enrolled students
                this.selectedStudents = [];
                this.leaderStudentId = null;
                this.studentSearch = '';
                
                // 3. Update program dropdown to show updated [REGISTERED ✓] tag
                this.updateProgramDropdown();
                
                // 4. Find next unregistered program in the same zone
                const nextUnreg = this.filteredPrograms.find(p => !p.is_registered);
                if (nextUnreg) {
                    this.selectedProgramId = nextUnreg.id;
                    const select = this.$refs.programSelect || document.getElementById('program_select');
                    if (select) select.value = nextUnreg.id;
                    this.feedbackSuccess = `✓ Successfully enrolled ${enrolledCount} participant(s) for "${enrolledProgName}"! Switched to next program: "${nextUnreg.name}".`;
                } else {
                    this.selectedProgramId = '';
                    this.feedbackSuccess = `✓ Successfully enrolled ${enrolledCount} participant(s) for "${enrolledProgName}"! All programs in ${this.selectedZone} have been completed.`;
                }
            } catch (err) {
                this.feedbackError = 'Connection error: Could not complete registration. Please check your network.';
            } finally {
                this.isSubmitting = false;
            }
        }
    };
}
</script>

<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif font-black text-slate-900">Program Entries & Registrations</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">Enroll participants from {{ $group->name }} into cultural programs with automated zone and conflict checking.</p>
        </div>
    </div>

    <!-- Summary Counters (Total, Registered, Unregistered) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-mono uppercase text-slate-400 font-bold block">Total Programs</span>
                <span class="text-2xl font-serif font-black text-slate-900">{{ $totalProgramsCount ?? $eligiblePrograms->count() }}</span>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-600 font-mono font-bold text-xs">
                #
            </div>
        </div>

        <a href="#entries-table-section" class="p-5 rounded-3xl bg-white border-2 border-emerald-500/40 shadow-sm flex items-center justify-between hover:border-emerald-500 transition-colors">
            <div>
                <span class="text-[11px] font-mono uppercase text-emerald-600 font-bold block">Registered Programs</span>
                <span class="text-2xl font-serif font-black text-emerald-700">{{ $registeredProgramsCount ?? 0 }}</span>
            </div>
            <span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-mono font-bold">
                Registered ✓
            </span>
        </a>

        <a href="#unregistered-section" class="p-5 rounded-3xl bg-white border-2 border-amber-500/40 shadow-sm flex items-center justify-between hover:border-amber-500 transition-colors">
            <div>
                <span class="text-[11px] font-mono uppercase text-amber-600 font-bold block">Unregistered Programs</span>
                <span class="text-2xl font-serif font-black text-amber-700">{{ $unregisteredProgramsCount ?? 0 }}</span>
            </div>
            <span class="px-2.5 py-1 rounded-xl bg-amber-100 text-amber-800 text-xs font-mono font-bold">
                Pending !
            </span>
        </a>
    </div>

    <!-- Registration Window Notice -->
    @if($isRegistrationOpen)
        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-mono flex items-center justify-between shadow-2xs">
            <span class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <strong>Registration Window Open:</strong> You can submit individual and group program registrations for your group.
            </span>
        </div>
    @else
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs font-mono flex items-center justify-between shadow-2xs">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <strong>Registration Window Closed:</strong> Program registration is currently closed by the festival administration.
            </span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-300 text-red-900 text-xs space-y-1 shadow-2xs">
            <p class="font-bold flex items-center gap-2 text-sm text-[#be1e2d]">
                <svg class="w-4 h-4 text-[#be1e2d] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Registration Error:
            </p>
            <ul class="list-disc list-inside space-y-0.5 pl-2 font-sans font-medium text-red-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs font-mono font-medium shadow-2xs">
            {{ session('success') }}
        </div>
    @endif

    <!-- Registration Submission Card -->
    <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm {{ !$isRegistrationOpen ? 'opacity-60 pointer-events-none' : '' }}">
        <div class="flex items-center gap-2 border-b border-slate-100 pb-4">
            <svg class="w-5 h-5 text-[#f3bd2e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            <h2 class="text-lg font-serif font-bold text-slate-900">Enroll Participant for Program</h2>
        </div>

        <form method="POST" action="{{ route('leader.registrations.store') }}" 
              @submit.prevent="submitRegistration($event)"
              x-data="registrationManager()"
              class="space-y-6">
            @csrf

            <!-- Reactive Feedback Banners -->
            <div x-show="feedbackSuccess" 
                 x-transition 
                 class="p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-400 text-emerald-900 text-xs font-mono font-bold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="feedbackSuccess"></span>
                </div>
                <button type="button" @click="feedbackSuccess = ''" class="text-emerald-700 hover:text-emerald-950 p-1 text-base leading-none">&times;</button>
            </div>

            <div x-show="feedbackError" 
                 x-transition 
                 class="p-4 rounded-2xl bg-red-50 border-2 border-red-300 text-red-900 text-xs font-mono font-bold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#be1e2d] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span x-text="feedbackError"></span>
                </div>
                <button type="button" @click="feedbackError = ''" class="text-red-700 hover:text-red-950 p-1 text-base leading-none">&times;</button>
            </div>

            <!-- STEP 1: ZONE SELECTION -->
            <div>
                <label class="block text-xs font-mono uppercase text-slate-700 mb-1.5 font-bold">
                    1. Select Zone <span class="text-red-500">*</span>
                </label>
                <select name="zone" 
                        id="zone_select"
                        x-model="selectedZone" 
                        @change="onZoneChange()" 
                        required 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="">-- Choose Zone --</option>
                    @php
                        $zoneList = ($zones && $zones->isNotEmpty()) 
                            ? $zones->pluck('name')->toArray() 
                            : ['A Zone', 'B Zone', 'C Zone', 'Mix Zone'];
                    @endphp
                    @foreach($zoneList as $zName)
                        <option value="{{ $zName }}" {{ old('zone') == $zName ? 'selected' : '' }}>{{ $zName }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] font-mono text-slate-500 mt-1">Select the festival zone to filter competitions and eligible students from {{ $group->name }}.</p>
            </div>

            <!-- STEP 2: PROGRAM SELECTION (FILTERED BY ZONE) -->
            <div>
                <label class="block text-xs font-mono uppercase text-slate-700 mb-1.5 font-bold">
                    2. Select Program <span class="text-red-500">*</span>
                </label>
                <select name="program_id" 
                        id="program_select"
                        x-ref="programSelect"
                        x-model="selectedProgramId" 
                        @change="onProgramChange(); selectedProgramId = $event.target.value;" 
                        :disabled="!selectedZone"
                        required 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="" x-text="selectedZone ? '-- Choose Competition Program (' + filteredPrograms.length + ' available) --' : '-- Please select a Zone first --'"></option>
                    @foreach($eligiblePrograms as $p)
                        @php
                            $pZone = $p->zone?->name ?? ($p->eligibility ?? 'Mix Zone');
                            $isReg = in_array($p->id, $registeredProgramIds ?? []);
                            $limit = $p->type === 'group' 
                                ? max(1, (int) ($p->participant_count ?? $p->max_participants ?? 2))
                                : max(1, (int) ($p->max_participants_per_group ?? $p->participant_count ?? 1));
                        @endphp
                        <option value="{{ $p->id }}" data-zone="{{ strtolower(trim($pZone)) }}">
                            [{{ $p->code }}] {{ $p->name }} {{ $isReg ? ' [REGISTERED ✓]' : ' [UNREGISTERED]' }} ({{ $p->type === 'group' ? 'Group - Limit: '.$limit : 'Individual - Limit: '.$limit }})
                        </option>
                    @endforeach
                </select>
                <template x-if="currentProgram">
                    <div class="mt-2 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono flex flex-wrap items-center justify-between gap-2">
                        <span>Competition Type: <strong class="text-slate-800" x-text="currentProgram.type === 'group' ? 'Group Competition' : 'Individual Competition'"></strong></span>
                        <span x-show="currentProgram.is_registered" class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">Already Registered by {{ $group->name }}</span>
                        <span x-show="!currentProgram.is_registered" class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold">Not Yet Registered</span>
                        <span>Group Participant Limit: <strong class="text-brand-orange font-bold" x-text="participantLimit"></strong></span>
                    </div>
                </template>
            </div>

            <!-- STEP 3: PARTICIPANTS ENROLLMENT -->
            <template x-if="currentProgram">
                <div class="space-y-4 pt-2 border-t border-slate-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <label class="block text-xs font-mono uppercase text-slate-700 font-bold">
                                3. Select Participant(s) <span class="text-red-500">*</span>
                            </label>
                            <p class="text-[11px] font-mono text-slate-500">
                                Add up to <strong class="text-slate-800" x-text="participantLimit"></strong> participant(s) from {{ $group->name }} for this competition.
                                <span x-show="isGroup" class="text-amber-700 font-semibold ml-1">(Designate one student as Team Leader)</span>
                            </p>
                        </div>
                        <div class="text-xs font-mono px-3 py-1.5 rounded-xl border font-bold"
                             :class="selectedStudents.length >= participantLimit ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-orange-50 border-orange-200 text-orange-800'">
                            Enrolled: <span x-text="selectedStudents.length"></span> / <span x-text="participantLimit"></span>
                        </div>
                    </div>

                    <!-- Hidden Form Inputs for Backend -->
                    <input type="hidden" name="leader_id" :value="leaderStudentId">
                    <input type="hidden" name="student_id" :value="leaderStudentId || (selectedStudents[0] ? selectedStudents[0].id : '')">
                    <template x-for="st in selectedStudents" :key="st.id">
                        <input type="hidden" name="student_ids[]" :value="st.id">
                    </template>

                    <!-- Selected Students List -->
                    <div class="rounded-2xl border border-slate-200 divide-y divide-slate-100 overflow-hidden bg-slate-50/50">
                        <div class="p-3 bg-slate-100/80 font-mono text-xs font-bold text-slate-700 flex items-center justify-between">
                            <span>Selected Students (<span x-text="selectedStudents.length"></span>)</span>
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
                                                   class="text-brand-orange focus:ring-brand-orange">
                                            <span class="text-[11px] font-mono font-bold" 
                                                  :class="leaderStudentId == st.id ? 'text-brand-orange' : 'text-slate-400'" 
                                                  x-text="leaderStudentId == st.id ? 'LEADER' : 'Member'"></span>
                                        </label>
                                    </template>
                                    <div class="h-10 w-10 rounded-xl bg-brand-orange text-white flex flex-col items-center justify-center font-mono font-black text-xs shadow-xs flex-shrink-0">
                                        <span class="text-[8px] uppercase font-bold text-orange-100 leading-tight">CHEST</span>
                                        <span x-text="st.chest"></span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-sm text-slate-900" x-text="st.name"></span>
                                            <span class="font-mono text-xs text-brand-orange font-semibold" x-text="'#' + st.chest"></span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-mono">
                                            <span x-text="'Zone: ' + st.zone_name"></span>
                                            <span class="ml-2" x-text="'Class: ' + (st.class || '-')"></span>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" 
                                        @click="removeStudent(st.id)" 
                                        class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 text-xs font-mono font-bold flex items-center gap-1 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Remove
                                </button>
                            </div>
                        </template>

                        <div x-show="selectedStudents.length === 0" class="p-6 text-center text-xs font-mono text-slate-400">
                            No students added yet. Search and select students below.
                        </div>
                    </div>

                    <!-- Search Input to Add Student -->
                    <div x-show="selectedStudents.length < participantLimit" class="relative" @click.outside="showStudentDropdown = false">
                        <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">
                            Search & Add Student (Name or Chest #)
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   x-model="studentSearch" 
                                   @focus="showStudentDropdown = true" 
                                   @input="showStudentDropdown = true" 
                                   :placeholder="'Search student by name or chest number to add (Slot ' + (selectedStudents.length + 1) + ' of ' + participantLimit + ')...'"
                                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors pr-10">
                            <span class="absolute right-3.5 top-3 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                        </div>

                        <!-- Dropdown Results -->
                        <div x-show="showStudentDropdown" 
                             class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-xl divide-y divide-slate-100"
                             style="display: none;">
                            <div class="p-2 text-slate-400 text-[10px] uppercase font-mono tracking-wider bg-slate-50">
                                Eligible Candidates in <span x-text="selectedZone"></span> ({{ $group->name }})
                            </div>
                            <template x-for="st in availableStudents" :key="st.id">
                                <button type="button" 
                                        @click="addStudent(st)" 
                                        class="w-full text-left px-4 py-2.5 hover:bg-orange-50 flex items-center justify-between gap-3 transition-colors">
                                    <div>
                                        <span class="font-bold text-sm text-slate-800 block" x-text="st.name"></span>
                                        <span class="text-[11px] text-slate-500 font-mono" x-text="'Chest #' + st.chest + ' • Zone: ' + st.zone_name + ' • Class: ' + (st.class || '-')"></span>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-lg bg-brand-orange text-white text-[11px] font-mono font-bold">
                                        + Add
                                    </span>
                                </button>
                            </template>
                            <div x-show="availableStudents.length === 0" class="p-3 text-center text-xs font-mono text-slate-400">
                                No matching eligible students found in {{ $group->name }} for <span x-text="selectedZone"></span>.
                            </div>
                        </div>
                    </div>

                    <div x-show="selectedStudents.length >= participantLimit" class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-mono font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Participant limit reached (<span x-text="selectedStudents.length"></span> of <span x-text="participantLimit"></span>). Click Submit Registration below to complete.</span>
                    </div>
                </div>
            </template>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="submit" 
                        :disabled="isSubmitting || selectedStudents.length === 0 || !selectedProgramId"
                        :class="isSubmitting || selectedStudents.length === 0 || !selectedProgramId ? 'opacity-60 cursor-not-allowed' : 'hover:bg-orange-600 active:scale-95 shadow-md shadow-orange-500/20'"
                        class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-brand-orange text-white transition-all flex items-center gap-2">
                    <template x-if="isSubmitting">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Submitting Registration...' : (selectedStudents.length > 1 ? 'Submit All ' + selectedStudents.length + ' Participants' : 'Submit Registration')"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Active / Registered Entries Table -->
    <div id="entries-table-section" class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-serif font-bold text-slate-900">Registered Program Entries ({{ $entries->total() }})</h3>
                <p class="text-[11px] font-mono text-slate-500">All programs enrolled by {{ $group->name }}. You can edit participants or remove an entry while registration is open.</p>
            </div>
            <span class="px-3 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-mono font-bold">
                {{ $registeredProgramsCount ?? 0 }} Unique Programs Registered
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 uppercase">
                        <th class="py-3 px-3">Chest / ID</th>
                        <th class="py-3 px-3">Program</th>
                        <th class="py-3 px-3">Zone</th>
                        <th class="py-3 px-3">Participant / Competition Leader</th>
                        <th class="py-3 px-3">Type</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($entries as $e)
                        @php
                            $isGroupComp = $e->isGroupEntry();
                            $displayLeader = $isGroupComp ? ($e->leaderStudent() ?? $e->student) : $e->student;
                            $displayChest = $displayLeader?->student_id ?: ($e->chest_number ?: '—');
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-3 font-bold text-brand-orange font-mono">#{{ $displayChest }}</td>
                            <td class="py-3 px-3 font-sans font-semibold text-slate-900">
                                <span class="text-[10px] font-mono text-slate-400 mr-1">[{{ $e->program->code }}]</span>
                                {{ $e->program->name }}
                            </td>
                            <td class="py-3 px-3">{{ $e->program->zone?->name ?? $e->program->eligibility }}</td>
                            <td class="py-3 px-3 font-sans">
                                @if($displayLeader)
                                    <span class="font-bold text-slate-900">{{ $displayLeader->name }}</span>
                                    @if($isGroupComp)
                                        <span class="text-[10px] text-amber-700 font-mono font-bold block">
                                            (Group Leader • {{ max(1, $e->participants->count()) }} members)
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 capitalize">{{ $e->program->type }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $e->status === 'verified' || $e->status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : ($e->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $e->status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                @if($isRegistrationOpen)
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('leader.registrations.edit', $e) }}" 
                                           class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-brand-orange hover:text-white text-slate-700 font-mono text-[11px] font-bold transition-colors">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('leader.registrations.destroy', $e) }}" onsubmit="return confirm('Are you sure you want to remove this registration entry?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-50 hover:bg-red-600 hover:text-white text-red-700 font-mono text-[11px] font-bold transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-[10px] text-slate-400">Locked</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No program entries submitted yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $entries->links() }}
        </div>
    </div>

    <!-- Unregistered Programs List -->
    <div id="unregistered-section" class="bg-white rounded-3xl border border-amber-200 p-6 sm:p-8 space-y-4 shadow-sm" x-data="{ filterZone: '' }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-serif font-bold text-slate-900">Unregistered Programs ({{ ($unregisteredPrograms ?? collect())->count() }})</h3>
                <p class="text-[11px] font-mono text-slate-500">Programs in which {{ $group->name }} has not registered any participant yet.</p>
            </div>
            <div class="flex items-center gap-2">
                <select x-model="filterZone" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-mono text-slate-700 focus:outline-none">
                    <option value="">All Zones</option>
                    @foreach($zones as $z)
                        <option value="{{ $z->name }}">{{ $z->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="sticky top-0 bg-white border-b border-slate-100 text-slate-400 uppercase">
                    <tr>
                        <th class="py-2.5 px-3">Code</th>
                        <th class="py-2.5 px-3">Program Name</th>
                        <th class="py-2.5 px-3">Zone</th>
                        <th class="py-2.5 px-3">Type</th>
                        <th class="py-2.5 px-3">Participant Limit</th>
                        <th class="py-2.5 px-3 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($unregisteredPrograms ?? [] as $up)
                        @php
                            $upZone = $up->zone?->name ?? ($up->eligibility ?? 'Mix Zone');
                        @endphp
                        <tr x-show="!filterZone || filterZone === '{{ addslashes($upZone) }}'" class="hover:bg-amber-50/40 transition">
                            <td class="py-2.5 px-3 font-bold text-slate-600">{{ $up->code }}</td>
                            <td class="py-2.5 px-3 font-sans font-semibold text-slate-900">{{ $up->name }}</td>
                            <td class="py-2.5 px-3">{{ $upZone }}</td>
                            <td class="py-2.5 px-3 capitalize">{{ $up->type }}</td>
                            <td class="py-2.5 px-3">{{ $up->participant_count ?? 2 }}</td>
                            <td class="py-2.5 px-3 text-right">
                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-bold uppercase">
                                    Unregistered
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-emerald-600 font-bold">All eligible programs have been registered by {{ $group->name }}!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
