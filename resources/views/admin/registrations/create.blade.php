@extends('layouts.admin', ['title' => 'Create Program Entry'])

@section('content')
@php
    $programsData = $programs->map(function ($p) {
        return [
            'id' => $p->id,
            'code' => $p->code,
            'name' => $p->name,
            'type' => $p->type,
            'zone_id' => $p->zone_id,
            'zone_name' => $p->zone?->name ?? ($p->eligibility ?? 'Mix Zone'),
            'limit' => $p->limit,
            'is_stage' => (bool)$p->is_stage,
        ];
    });

    $studentsData = $students->map(function ($s) {
        return [
            'id' => $s->id,
            'chest' => (string)($s->student_id ?: $s->id),
            'name' => $s->name,
            'class' => $s->class_level ?? '',
            'group_id' => $s->group_id,
            'group_name' => $s->group?->name ?? 'Unassigned',
            'group_code' => $s->group?->code ?? '',
            'group_color' => $s->group?->color_hex ?? '#005c94',
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

<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.registrations.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-1.5 block font-semibold">← Back to Program Entries</a>
            <h1 class="text-2xl sm:text-3xl font-sora font-black text-slate-900">Create Program Entry</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">Direct festival desk registration with automatic zone validation and conflict checking.</p>
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

    <form method="POST" action="{{ route('admin.registrations.store') }}" 
          x-data="{
              selectedZone: '{{ old('zone', '') }}',
              selectedProgramId: '{{ old('program_id', '') }}',
              selectedGroupId: '{{ old('group_id', '') }}',
              
              // Individual participant state
              selectedStudent: null,
              studentSearch: '',
              showStudentDropdown: false,
              
              // Group participants state
              groupStudents: [],
              leaderStudentId: null,
              groupStudentSearch: '',
              showGroupStudentDropdown: false,
              
              allPrograms: {{ json_encode($programsData) }},
              allStudents: {{ json_encode($studentsData) }},
              allZones: {{ json_encode($zonesData) }},
              
              get currentProgram() {
                  return this.allPrograms.find(p => p.id == this.selectedProgramId) || null;
              },
              
              get isGroup() {
                  return this.currentProgram ? this.currentProgram.type === 'group' : false;
              },
              
              get participantLimit() {
                  return this.currentProgram ? (this.currentProgram.limit || 2) : 2;
              },
              
              get filteredPrograms() {
                  if (!this.selectedZone) return [];
                  return this.allPrograms.filter(p => p.zone_name.toLowerCase().trim() === this.selectedZone.toLowerCase().trim());
              },
              
              get eligibleStudents() {
                  if (!this.selectedZone) return [];
                  return this.allStudents.filter(s => {
                      const isMix = this.selectedZone.toLowerCase().trim() === 'mix zone';
                      const zoneMatch = isMix || (s.zone_name.toLowerCase().trim() === this.selectedZone.toLowerCase().trim());
                      if (!zoneMatch) return false;
                      if (this.selectedGroupId && s.group_id != this.selectedGroupId) return false;
                      return true;
                  });
              },
              
              get searchFilteredStudents() {
                  const q = this.studentSearch.toLowerCase().trim();
                  const base = this.eligibleStudents;
                  if (!q) return base.slice(0, 30);
                  return base.filter(s => s.name.toLowerCase().includes(q) || s.chest.toLowerCase().includes(q)).slice(0, 30);
              },
              
              get groupSearchFilteredStudents() {
                  const q = this.groupStudentSearch.toLowerCase().trim();
                  const alreadyAddedIds = this.groupStudents.map(s => s.id);
                  const base = this.eligibleStudents.filter(s => !alreadyAddedIds.includes(s.id));
                  if (!q) return base.slice(0, 30);
                  return base.filter(s => s.name.toLowerCase().includes(q) || s.chest.toLowerCase().includes(q)).slice(0, 30);
              },
              
              onZoneChange() {
                  this.selectedProgramId = '';
                  this.selectedStudent = null;
                  this.studentSearch = '';
                  this.groupStudents = [];
                  this.leaderStudentId = null;
              },
              
              onProgramChange() {
                  this.selectedStudent = null;
                  this.studentSearch = '';
                  this.groupStudents = [];
                  this.leaderStudentId = null;
              },
              
              selectStudent(st) {
                  this.selectedStudent = st;
                  this.selectedGroupId = st.group_id;
                  this.showStudentDropdown = false;
                  this.studentSearch = '';
              },
              
              clearStudent() {
                  this.selectedStudent = null;
                  this.studentSearch = '';
              },
              
              addGroupStudent(st) {
                  if (this.groupStudents.length >= this.participantLimit) return;
                  this.groupStudents.push(st);
                  if (!this.leaderStudentId) {
                      this.leaderStudentId = st.id;
                  }
                  if (!this.selectedGroupId) {
                      this.selectedGroupId = st.group_id;
                  }
                  this.groupStudentSearch = '';
                  this.showGroupStudentDropdown = false;
              },
              
              removeGroupStudent(stId) {
                  this.groupStudents = this.groupStudents.filter(s => s.id !== stId);
                  if (this.leaderStudentId == stId) {
                      this.leaderStudentId = this.groupStudents.length > 0 ? this.groupStudents[0].id : null;
                  }
              }
          }"
          class="rounded-2xl bg-white border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm">
        @csrf

        <!-- STEP 1: ZONE SELECTION -->
        <div>
            <label class="block text-xs font-mono uppercase text-slate-700 mb-1.5 font-bold">
                1. Select Zone <span class="text-red-500">*</span>
            </label>
            <select name="zone" 
                    x-model="selectedZone" 
                    @change="onZoneChange()" 
                    required 
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
                <option value="">-- Choose Zone --</option>
                <template x-for="z in allZones" :key="z.id">
                    <option :value="z.name" x-text="z.name"></option>
                </template>
            </select>
            <p class="text-[11px] font-mono text-slate-500 mt-1">Select the festival zone to filter competitions and eligible students.</p>
        </div>

        <!-- STEP 2: PROGRAM SELECTION (FILTERED BY ZONE) -->
        <div>
            <label class="block text-xs font-mono uppercase text-slate-700 mb-1.5 font-bold">
                2. Select Program <span class="text-red-500">*</span>
            </label>
            <select name="program_id" 
                    x-model="selectedProgramId" 
                    @change="onProgramChange()"
                    :disabled="!selectedZone"
                    required 
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <option value="" x-text="selectedZone ? '-- Choose Competition Program (' + filteredPrograms.length + ' available) --' : '-- Please select a Zone first --'"></option>
                <template x-for="p in filteredPrograms" :key="p.id">
                    <option :value="p.id" x-text="'[' + p.code + '] ' + p.name + ' (' + (p.type === 'group' ? 'Group - Limit: ' + p.limit : 'Individual - Limit: ' + p.limit) + ')'"></option>
                </template>
            </select>
            <template x-if="currentProgram">
                <div class="mt-2 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono flex items-center justify-between">
                    <span>Program Type: <strong class="text-slate-800" x-text="currentProgram.type === 'group' ? 'Group Competition' : 'Individual Competition'"></strong></span>
                    <span>Participant Limit: <strong class="text-[#be1e2d]" x-text="participantLimit + ' per group'"></strong></span>
                </div>
            </template>
        </div>

        <!-- STEP 3: PARTICIPANT SELECTION -->
        <template x-if="currentProgram && !isGroup">
            <!-- INDIVIDUAL PARTICIPANT SELECTION -->
            <div class="space-y-3 pt-2 border-t border-slate-100">
                <label class="block text-xs font-mono uppercase text-slate-700 font-bold">
                    3. Select Student Participant <span class="text-red-500">*</span>
                </label>

                <!-- Hidden Form Inputs for Individual -->
                <input type="hidden" name="student_id" :value="selectedStudent ? selectedStudent.id : ''">
                <input type="hidden" name="group_id" :value="selectedStudent ? selectedStudent.group_id : ''">
                <input type="hidden" name="chest_number" :value="selectedStudent ? selectedStudent.chest : ''">

                <!-- Selected Student Card -->
                <div x-show="selectedStudent" class="p-4 rounded-xl border-2 border-emerald-300 bg-emerald-50/60 flex items-center justify-between gap-4 transition-all">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-[#005c94] text-white flex flex-col items-center justify-center font-mono font-black text-sm shadow-sm flex-shrink-0">
                            <span class="text-[9px] uppercase font-bold text-sky-200 leading-tight">CHEST</span>
                            <span x-text="selectedStudent ? selectedStudent.chest : ''"></span>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm font-sora" x-text="selectedStudent ? selectedStudent.name : ''"></h4>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold text-white shadow-2xs font-mono"
                                      :style="'background-color: ' + (selectedStudent ? selectedStudent.group_color : '#005c94')"
                                      x-text="selectedStudent ? selectedStudent.group_name : ''">
                                </span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-white border border-slate-200 text-slate-700"
                                      x-text="'Zone: ' + (selectedStudent ? selectedStudent.zone_name : '')">
                                </span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-white border border-slate-200 text-slate-700"
                                      x-text="'Class: ' + (selectedStudent ? (selectedStudent.class || '-') : '-')">
                                </span>
                            </div>
                        </div>
                    </div>

                    <button type="button" @click="clearStudent()" 
                            class="px-3 py-1.5 rounded-lg border border-red-300 text-red-700 bg-white hover:bg-red-50 text-xs font-mono font-bold flex items-center gap-1 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Change
                    </button>
                </div>

                <!-- Searchable Combobox for Student -->
                <div x-show="!selectedStudent" class="relative" @click.outside="showStudentDropdown = false">
                    <div class="relative">
                        <input type="text" 
                               x-model="studentSearch" 
                               @focus="showStudentDropdown = true"
                               @input="showStudentDropdown = true"
                               placeholder="Type name or chest number to search..."
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors pr-10">
                        <span class="absolute right-3.5 top-3.5 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                    </div>

                    <!-- Autocomplete Dropdown List -->
                    <div x-show="showStudentDropdown" 
                         class="absolute z-50 left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-xl divide-y divide-slate-100"
                         style="display: none;">
                        <div class="p-2 text-slate-400 text-[10px] uppercase font-mono tracking-wider bg-slate-50">
                            Eligible Students in <span x-text="selectedZone"></span>
                        </div>
                        <template x-for="st in searchFilteredStudents" :key="st.id">
                            <button type="button" 
                                    @click="selectStudent(st)"
                                    class="w-full text-left px-4 py-2.5 hover:bg-slate-50 flex items-center justify-between gap-3 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-[#005c94]/10 text-[#005c94] group-hover:bg-[#005c94] group-hover:text-white transition-colors">
                                        #<span x-text="st.chest"></span>
                                    </span>
                                    <div>
                                        <span class="font-bold text-sm text-slate-800 block" x-text="st.name"></span>
                                        <span class="text-[11px] text-slate-500 font-mono" x-text="'Class: ' + (st.class || '-')"></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold text-white font-mono"
                                          :style="'background-color: ' + st.group_color"
                                          x-text="st.group_name">
                                    </span>
                                    <span class="text-[11px] font-mono text-slate-500" x-text="st.zone_name"></span>
                                </div>
                            </button>
                        </template>
                        <div x-show="searchFilteredStudents.length === 0" class="px-4 py-4 text-center text-xs font-mono text-slate-500">
                            No eligible students found in <span x-text="selectedZone"></span>.
                        </div>
                    </div>
                    <p class="text-[11px] font-mono text-slate-500 mt-1.5">
                        * Note: Only students belonging to <strong x-text="selectedZone"></strong> are listed<span x-show="selectedZone === 'Mix Zone'"> (Mix Zone allows students from all zones)</span>.
                    </p>
                </div>
            </div>
        </template>

        <!-- STEP 3 (GROUP): GROUP COMPETITION PARTICIPANTS & LEADER -->
        <template x-if="currentProgram && isGroup">
            <div class="space-y-4 pt-2 border-t border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <label class="block text-xs font-mono uppercase text-slate-700 font-bold">
                            3. Group Competition Team & Leader <span class="text-red-500">*</span>
                        </label>
                        <p class="text-[11px] font-mono text-slate-500">
                            Add participating students (Maximum <span x-text="participantLimit"></span> students) and designate one as Leader.
                        </p>
                    </div>
                    <div class="text-xs font-mono px-3 py-1.5 rounded-lg bg-orange-50 border border-orange-200 text-orange-800 font-bold">
                        Enrolled: <span x-text="groupStudents.length"></span> / <span x-text="participantLimit"></span>
                    </div>
                </div>

                <!-- Participating Group Selection -->
                <div>
                    <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Select Competing Group</label>
                    <select name="group_id" 
                            x-model="selectedGroupId" 
                            required 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
                        <option value="">-- Choose Group --</option>
                        @foreach($groups as $grp)
                            <option value="{{ $grp->id }}" {{ old('group_id') == $grp->id ? 'selected' : '' }}>
                                {{ $grp->name }} ({{ $grp->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Hidden inputs for Leader and Participant IDs -->
                <input type="hidden" name="leader_id" :value="leaderStudentId">
                <input type="hidden" name="student_id" :value="leaderStudentId">
                <template x-for="st in groupStudents" :key="st.id">
                    <input type="hidden" name="student_ids[]" :value="st.id">
                </template>

                <!-- List of Added Group Participants -->
                <div class="rounded-xl border border-slate-200 divide-y divide-slate-100 overflow-hidden bg-slate-50/50">
                    <div class="p-3 bg-slate-100/80 font-mono text-xs font-bold text-slate-700 flex items-center justify-between">
                        <span>Team Participants</span>
                        <span class="text-[11px] text-slate-500">Select Leader (Name appears on result poster)</span>
                    </div>
                    
                    <template x-for="(st, idx) in groupStudents" :key="st.id">
                        <div class="p-3 bg-white flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-1.5 cursor-pointer" title="Click to designate this student as Team Leader">
                                    <input type="radio" 
                                           name="_leader_choice" 
                                           :value="st.id" 
                                           :checked="leaderStudentId == st.id" 
                                           @change="leaderStudentId = st.id" 
                                           class="text-[#be1e2d] focus:ring-[#be1e2d]">
                                    <span class="text-[11px] font-mono font-bold" 
                                          :class="leaderStudentId == st.id ? 'text-[#be1e2d]' : 'text-slate-400'" 
                                          x-text="leaderStudentId == st.id ? 'LEADER' : 'Member'"></span>
                                </label>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-sm text-slate-900" x-text="st.name"></span>
                                        <span class="font-mono text-xs text-[#005c94] font-semibold" x-text="'#' + st.chest"></span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-mono">
                                        <span x-text="'Zone: ' + st.zone_name"></span>
                                        <span class="ml-2" x-text="'Class: ' + (st.class || '-')"></span>
                                    </div>
                                </div>
                            </div>

                            <button type="button" 
                                    @click="removeGroupStudent(st.id)" 
                                    class="text-red-500 hover:text-red-700 p-1 rounded-lg hover:bg-red-50 text-xs font-mono flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Remove
                            </button>
                        </div>
                    </template>

                    <div x-show="groupStudents.length === 0" class="p-6 text-center text-xs font-mono text-slate-400">
                        No team members added yet. Search and add students below.
                    </div>
                </div>

                <!-- Add Student to Team Section -->
                <div x-show="groupStudents.length < participantLimit" class="relative" @click.outside="showGroupStudentDropdown = false">
                    <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">
                        Add Student to Team (Search by Name or Chest #)
                    </label>
                    <div class="relative">
                        <input type="text" 
                               x-model="groupStudentSearch" 
                               @focus="showGroupStudentDropdown = true" 
                               @input="showGroupStudentDropdown = true" 
                               placeholder="Type name or chest number to add student..."
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors pr-10">
                        <span class="absolute right-3.5 top-3 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </span>
                    </div>

                    <!-- Autocomplete dropdown for group student add -->
                    <div x-show="showGroupStudentDropdown" 
                         class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-xl divide-y divide-slate-100"
                         style="display: none;">
                        <template x-for="st in groupSearchFilteredStudents" :key="st.id">
                            <button type="button" 
                                    @click="addGroupStudent(st)" 
                                    class="w-full text-left px-4 py-2.5 hover:bg-orange-50 flex items-center justify-between gap-3 transition-colors">
                                <div>
                                    <span class="font-bold text-sm text-slate-800 block" x-text="st.name"></span>
                                    <span class="text-[11px] text-slate-500 font-mono" x-text="'Chest #' + st.chest + ' • Zone: ' + st.zone_name + ' • Class: ' + (st.class || '-')"></span>
                                </div>
                                <span class="px-2 py-1 rounded bg-[#be1e2d] text-white text-[10px] font-mono font-bold">
                                    + Add
                                </span>
                            </button>
                        </template>
                        <div x-show="groupSearchFilteredStudents.length === 0" class="p-3 text-center text-xs font-mono text-slate-400">
                            No matching students available to add.
                        </div>
                    </div>
                </div>

                <div x-show="groupStudents.length >= participantLimit" class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-mono font-semibold">
                    Maximum limit of <span x-text="participantLimit"></span> participants reached for this competition.
                </div>
            </div>
        </template>

        <!-- Status & Notes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-700 mb-1.5 font-bold">Status</label>
                <select name="status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#005c94] focus:bg-white transition-colors">
                    <option value="verified" {{ old('status', 'verified') == 'verified' ? 'selected' : '' }}>Verified (Direct to Green Room)</option>
                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending Verification</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-700 mb-1.5 font-bold">Notes (Optional)</label>
                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Special requests, accompanists..."
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#005c94] focus:bg-white transition-colors">
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
            <a href="{{ route('admin.registrations.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
            <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#be1e2d] text-white hover:brightness-110 shadow-lg shadow-[#be1e2d]/20 transition-all">
                Confirm Entry
            </button>
        </div>
    </form>
</div>
@endsection
