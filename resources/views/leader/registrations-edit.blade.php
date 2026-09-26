@extends('layouts.leader', ['title' => 'Edit Registration — ' . $program->name])

@section('content')
@php
    $studentsData = $students->map(function ($s) {
        return [
            'id' => $s->id,
            'chest' => (string)($s->student_id ?: $s->id),
            'name' => $s->name,
            'class' => $s->class_level ?? '',
            'zone_name' => $s->zone?->name ?? ($s->category ?? 'A Zone'),
        ];
    });

    $isGroup = $program->isGroup();
    $currentParticipantIds = $entry->participants->pluck('id')->toArray();
    if (empty($currentParticipantIds) && $entry->student_id) {
        $currentParticipantIds = [$entry->student_id];
    }
    $currentLeaderId = $entry->participants()->wherePivot('role', 'captain')->first()?->id ?? $entry->student_id;
@endphp

<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('leader.registrations') }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h1 class="text-2xl font-serif font-black text-slate-900">Edit Registration</h1>
            </div>
            <p class="text-xs font-mono text-slate-500 mt-1 pl-9">
                Update participant assignments for <span class="font-bold text-slate-800">{{ $program->name }}</span> (#{{ $entry->chest_number }}).
            </p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-mono font-bold uppercase {{ $entry->status === 'verified' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
            Status: {{ $entry->status }}
        </span>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-300 text-red-900 text-xs space-y-1 shadow-2xs">
            <p class="font-bold text-sm text-[#be1e2d]">Cannot Save Changes:</p>
            <ul class="list-disc list-inside space-y-0.5 font-sans font-medium text-red-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Program Metadata Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs font-mono">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Program ID</span>
                <span class="font-bold text-slate-900">{{ $program->code ?? $program->id }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Program Type</span>
                <span class="font-bold text-slate-900 uppercase">{{ $program->type }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Eligible Zone</span>
                <span class="font-bold text-slate-900">{{ $program->zone?->name ?? $program->eligibility }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Participant Limit</span>
                <span class="font-bold text-slate-900">{{ $isGroup ? ($program->participant_count ?? 4) . ' Members' : '1 Student' }}</span>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
        <form method="POST" action="{{ route('leader.registrations.update', $entry) }}" 
              x-data="{
                  allStudents: {{ json_encode($studentsData) }},
                  isGroup: {{ $isGroup ? 'true' : 'false' }},
                  participantLimit: {{ (int)($program->participant_count ?? 4) }},
                  
                  // Individual
                  selectedStudentId: '{{ old('student_id', $entry->student_id) }}',
                  studentSearch: '',
                  showStudentDropdown: false,

                  // Group
                  groupStudents: {{ json_encode($entry->participants->map(fn($p) => ['id' => $p->id, 'chest' => (string)($p->student_id ?: $p->id), 'name' => $p->name, 'class' => $p->class_level ?? '', 'zone_name' => $p->category ?? 'A Zone'])) }},
                  leaderStudentId: {{ (int)$currentLeaderId }},
                  groupStudentSearch: '',
                  showGroupStudentDropdown: false,

                  get individualFilteredStudents() {
                      if (!this.studentSearch.trim()) return this.allStudents;
                      const q = this.studentSearch.toLowerCase();
                      return this.allStudents.filter(s => s.name.toLowerCase().includes(q) || s.chest.toLowerCase().includes(q));
                  },

                  get groupSearchFilteredStudents() {
                      const existingIds = this.groupStudents.map(s => s.id);
                      let list = this.allStudents.filter(s => !existingIds.includes(s.id));
                      if (this.groupStudentSearch.trim()) {
                          const q = this.groupStudentSearch.toLowerCase();
                          list = list.filter(s => s.name.toLowerCase().includes(q) || s.chest.toLowerCase().includes(q));
                      }
                      return list;
                  },

                  addGroupStudent(st) {
                      if (this.groupStudents.length >= this.participantLimit) return;
                      this.groupStudents.push(st);
                      if (!this.leaderStudentId) {
                          this.leaderStudentId = st.id;
                      }
                      this.groupStudentSearch = '';
                      this.showGroupStudentDropdown = false;
                  },

                  removeGroupStudent(idx) {
                      const removed = this.groupStudents.splice(idx, 1)[0];
                      if (removed && removed.id === this.leaderStudentId) {
                          this.leaderStudentId = this.groupStudents[0] ? this.groupStudents[0].id : null;
                      }
                  }
              }"
              class="space-y-6">
            @csrf
            @method('PUT')

            <!-- INDIVIDUAL MODE -->
            <template x-if="!isGroup">
                <div class="space-y-3">
                    <label class="block text-xs font-mono uppercase text-slate-700 font-bold">
                        Select Participant Student *
                    </label>

                    <div class="relative" @click.outside="showStudentDropdown = false">
                        <input type="text" 
                               x-model="studentSearch" 
                               @focus="showStudentDropdown = true" 
                               @input="showStudentDropdown = true" 
                               placeholder="Search participant by name or chest #..."
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">

                        <input type="hidden" name="student_id" :value="selectedStudentId">

                        <div x-show="showStudentDropdown" 
                             class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-xl divide-y divide-slate-100"
                             style="display: none;">
                            <template x-for="st in individualFilteredStudents" :key="st.id">
                                <button type="button" 
                                        @click="selectedStudentId = st.id; studentSearch = st.name + ' (Chest #' + st.chest + ')'; showStudentDropdown = false" 
                                        class="w-full text-left px-4 py-2.5 hover:bg-slate-50 flex items-center justify-between text-xs transition-colors">
                                    <div>
                                        <span class="font-bold text-slate-900 block" x-text="st.name"></span>
                                        <span class="text-slate-400 font-mono text-[11px]" x-text="'Chest #' + st.chest + ' • Zone: ' + st.zone_name + ' • Class: ' + (st.class || '—')"></span>
                                    </div>
                                    <span x-show="selectedStudentId == st.id" class="text-emerald-600 font-bold font-mono">Selected ✓</span>
                                </button>
                            </template>
                        </div>
                    </div>

                    @if($entry->student)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono flex items-center justify-between">
                            <div>
                                <span class="text-slate-400 block text-[10px]">CURRENT PARTICIPANT</span>
                                <span class="font-bold text-slate-800">{{ $entry->student->name }}</span>
                                <span class="text-slate-500 font-mono">({{ $entry->student->student_id }})</span>
                            </div>
                        </div>
                    @endif
                </div>
            </template>

            <!-- GROUP MODE -->
            <template x-if="isGroup">
                <div class="space-y-5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-mono uppercase text-slate-700 font-bold">
                            Team Participants & Designated Leader *
                        </label>
                        <span class="px-3 py-1 rounded-full text-xs font-mono font-bold"
                              :class="groupStudents.length === participantLimit ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                              x-text="'Enrolled: ' + groupStudents.length + ' / ' + participantLimit"></span>
                    </div>

                    <!-- Hidden inputs for form submission -->
                    <template x-for="st in groupStudents" :key="st.id">
                        <input type="hidden" name="student_ids[]" :value="st.id">
                    </template>
                    <input type="hidden" name="leader_id" :value="leaderStudentId">

                    <!-- List of chosen participants with Leader selection radio -->
                    <div class="border border-slate-200 rounded-2xl divide-y divide-slate-100 overflow-hidden bg-slate-50/50">
                        <template x-for="(st, idx) in groupStudents" :key="st.id">
                            <div class="p-3.5 sm:px-5 flex items-center justify-between gap-3 bg-white">
                                <div class="flex items-center gap-3">
                                    <input type="radio" 
                                           name="designated_leader_radio" 
                                           :checked="leaderStudentId == st.id" 
                                           @change="leaderStudentId = st.id" 
                                           class="w-4 h-4 text-[#be1e2d] focus:ring-[#be1e2d]">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-sm text-slate-900" x-text="st.name"></span>
                                            <span x-show="leaderStudentId == st.id" class="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[9px] uppercase font-mono">
                                                Team Leader
                                            </span>
                                        </div>
                                        <span class="text-xs text-slate-400 font-mono" x-text="'Chest #' + st.chest + ' • Zone: ' + st.zone_name + ' • Class: ' + (st.class || '—')"></span>
                                    </div>
                                </div>
                                <button type="button" @click="removeGroupStudent(idx)" class="text-xs font-mono text-rose-600 hover:text-rose-800 font-bold px-2 py-1 rounded hover:bg-rose-50">
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
                        <label class="block text-xs font-mono uppercase text-slate-600 mb-1 font-bold">
                            Add Participant (Search by Name or Chest #)
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   x-model="groupStudentSearch" 
                                   @focus="showGroupStudentDropdown = true" 
                                   @input="showGroupStudentDropdown = true" 
                                   placeholder="Type name or chest number to add student..."
                                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
                        </div>

                        <!-- Autocomplete dropdown for group student add -->
                        <div x-show="showGroupStudentDropdown" 
                             class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-xl divide-y divide-slate-100"
                             style="display: none;">
                            <template x-for="st in groupSearchFilteredStudents" :key="st.id">
                                <button type="button" 
                                        @click="addGroupStudent(st)" 
                                        class="w-full text-left px-4 py-2.5 hover:bg-slate-50 flex items-center justify-between gap-3 text-xs transition-colors">
                                    <div>
                                        <span class="font-bold text-slate-800 block" x-text="st.name"></span>
                                        <span class="text-slate-400 font-mono text-[11px]" x-text="'Chest #' + st.chest + ' • Zone: ' + st.zone_name + ' • Class: ' + (st.class || '—')"></span>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-lg bg-[#be1e2d] text-white font-mono font-bold text-[10px]">
                                        + Add
                                    </span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Action Buttons -->
            <div class="pt-5 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('leader.registrations') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-mono font-semibold transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white text-xs font-mono font-bold uppercase tracking-wider shadow-md shadow-[#be1e2d]/25 transition-all">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
