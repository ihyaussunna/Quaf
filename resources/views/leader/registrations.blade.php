@extends('layouts.leader', ['title' => 'Program Entries & Registrations'])

@section('content')
@php
    $programsData = $eligiblePrograms->map(function ($p) use ($registeredProgramIds, $entriesByProgram) {
        $limit = $p->limit;
            
        $pEntries = isset($entriesByProgram) ? $entriesByProgram->get($p->id, collect()) : collect();
        $enrolledStudents = [];
        if ($p->type === 'group') {
            $firstEntry = $pEntries->first();
            if ($firstEntry) {
                $leaderStudent = $firstEntry->leaderStudent() ?? $firstEntry->student;
                if ($firstEntry->participants && $firstEntry->participants->isNotEmpty()) {
                    $enrolledStudents = $firstEntry->participants->map(function ($st) use ($firstEntry, $leaderStudent) {
                        return [
                            'id' => $st->id,
                            'chest' => ltrim((string)($st->chest_number ?: ($st->student_id ?: $st->id)), '#'),
                            'name' => $st->name,
                            'class' => $st->class_level ?? '',
                            'zone_id' => $st->zone_id,
                            'zone_name' => $st->zone?->name ?? 'N/A',
                            'is_leader' => ($leaderStudent && $leaderStudent->id === $st->id),
                            'entry_id' => $firstEntry->id,
                        ];
                    })->values()->all();
                } elseif ($firstEntry->student) {
                    $st = $firstEntry->student;
                    $enrolledStudents = [[
                        'id' => $st->id,
                        'chest' => ltrim((string)($st->chest_number ?: ($st->student_id ?: $st->id)), '#'),
                        'name' => $st->name,
                        'class' => $st->class_level ?? '',
                        'zone_id' => $st->zone_id,
                        'zone_name' => $st->zone?->name ?? 'N/A',
                        'is_leader' => true,
                        'entry_id' => $firstEntry->id,
                    ]];
                }
            }
        } else {
            $enrolledStudents = $pEntries->map(function ($entry) {
                $st = $entry->student;
                if (!$st) return null;
                return [
                    'id' => $st->id,
                    'chest' => ltrim((string)($st->chest_number ?: ($st->student_id ?: $st->id)), '#'),
                    'name' => $st->name,
                    'class' => $st->class_level ?? '',
                    'zone_id' => $st->zone_id,
                    'zone_name' => $st->zone?->name ?? 'N/A',
                    'is_leader' => false,
                    'entry_id' => $entry->id,
                ];
            })->filter()->values()->all();
        }
        $enrolled = count($enrolledStudents);
        
        $remaining = max(0, $limit - $enrolled);
        $isFull = ($enrolled >= $limit);
        $isRegistered = ($enrolled > 0);
        
        if ($enrolled === 0) {
            $tag = "[0/{$limit} • {$limit} left]";
            $statusLabel = "0/{$limit} • {$limit} left";
            $statusType = "unregistered";
        } elseif ($enrolled < $limit) {
            $tag = "[{$enrolled}/{$limit} • {$remaining} left]";
            $statusLabel = "{$enrolled}/{$limit} • {$remaining} left";
            $statusType = "partial";
        } else {
            $tag = "[Complete {$enrolled}/{$limit}]";
            $statusLabel = "Complete {$enrolled}/{$limit}";
            $statusType = "full";
        }

        $statusKey = $statusType === 'full' ? 'completed' : ($statusType === 'partial' ? 'partial' : 'pending');

        return [
            'id' => $p->id,
            'code' => $p->code,
            'name' => $p->name,
            'malayalam_name' => $p->malayalam_name ?? '',
            'type' => $p->type,
            'zone_id' => $p->zone_id,
            'zone_name' => $p->zone?->name ?? ($p->eligibility ?? 'Mix Zone'),
            'limit' => $limit,
            'enrolled' => $enrolled,
            'remaining' => $remaining,
            'needed' => $remaining,
            'percent' => $limit > 0 ? min(100, round(($enrolled / $limit) * 100)) : 0,
            'is_full' => $isFull,
            'is_registered' => $isRegistered,
            'enrolled_students' => $enrolledStudents,
            'tag' => $tag,
            'status_label' => $statusLabel,
            'status_type' => $statusType,
            'status_key' => $statusKey,
            'is_stage' => (bool) $p->is_stage,
        ];
    });

    $studentsData = $students->map(function ($s) {
        $indCount = $s->getIndividualParticipationCount();
        return [
            'id' => $s->id,
            'chest' => ltrim((string)($s->chest_number ?: ($s->student_id ?: $s->id)), '#'),
            'name' => $s->name,
            'class' => $s->class_level ?? '',
            'zone_name' => $s->zone?->name ?? ($s->category ?? 'A Zone'),
            'individual_count' => $indCount,
            'has_reached_individual_limit' => ($indCount >= 5),
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
    programs: {!! json_encode($programsData, JSON_UNESCAPED_UNICODE) !!},
    students: {!! json_encode($studentsData, JSON_UNESCAPED_UNICODE) !!},
    zones: {!! json_encode($zonesData, JSON_UNESCAPED_UNICODE) !!},
    programsStatusList: {!! json_encode($programsStatusList, JSON_UNESCAPED_UNICODE) !!},
    statusSummary: {!! json_encode($statusSummary, JSON_UNESCAPED_UNICODE) !!}
};

window.quafSetTrackerTab = function(tab) {
    if (window.quafQuotaTrackerInstance) {
        window.quafQuotaTrackerInstance.setTab(tab);
    }
    const sec = document.getElementById('programs-tracker-section');
    if (sec) {
        sec.scrollIntoView({ behavior: 'smooth' });
    }
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
            window.quafRegistrationManagerInstance = this;
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

        get currentProgram() {
            return this.allPrograms.find(p => String(p.id) === String(this.selectedProgramId)) || null;
        },
        
        get isGroup() {
            return this.currentProgram ? this.currentProgram.type === 'group' : false;
        },
        
        get participantLimit() {
            return this.currentProgram ? (this.currentProgram.limit || 1) : 1;
        },
        
        get filteredPrograms() {
            if (!this.selectedZone) return [];
            const z = this.normalizeZone(this.selectedZone);
            return this.allPrograms.filter(p => {
                const pZone = this.normalizeZone(p.zone_name);
                return pZone === z || (z === 'mix' && (p.is_mix_zone || pZone === 'mix'));
            });
        },
        
        get eligibleStudents() {
            if (!this.selectedZone) return this.allStudents;
            const z = this.normalizeZone(this.selectedZone);
            const isMix = (z === 'mix');
            return this.allStudents.filter(s => {
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
            // If the student was already registered in the database for the current program,
            // they already occupy 1 slot for this program, so their effective count is (individual_count - 1)
            const isEnrolledInCurrent = this.currentProgram && Array.isArray(this.currentProgram.enrolled_students) &&
                this.currentProgram.enrolled_students.some(es => Number(es.id) === Number(st.id));
            
            const count = isEnrolledInCurrent ? Math.max(0, (st.individual_count || 0) - 1) : (st.individual_count || 0);
            return count >= 5;
        },
        
        updateProgramDropdown() {
            const select = this.$refs.programSelect || document.getElementById('program_select');
            if (!select) return;
            
            const zone = this.selectedZone ? this.selectedZone.trim() : '';
            const currentProgId = String(this.selectedProgramId || '');
            
            // Clear options safely using standard HTML OptionsCollection API
            select.options.length = 0;
            
            const defaultPrompt = zone 
                ? `-- Choose Competition Program (${this.filteredPrograms.length} available) --` 
                : '-- Please select a Zone first --';
            
            select.options[0] = new Option(defaultPrompt, '', false, !currentProgId);
            
            let matched = false;
            this.filteredPrograms.forEach((p, idx) => {
                const isSelected = String(p.id) === currentProgId;
                if (isSelected) matched = true;
                select.options[idx + 1] = new Option(`${p.name} ${p.tag}`, String(p.id), false, isSelected);
            });
            
            if (matched && currentProgId) {
                select.value = currentProgId;
            } else if (!matched && this.filteredPrograms.length > 0 && currentProgId) {
                select.value = '';
                this.selectedProgramId = '';
            }
        },
        
        selectProgram(zoneName, progId) {
            if (zoneName) {
                this.selectedZone = zoneName;
            }
            this.selectedProgramId = String(progId || '');
            this.updateProgramDropdown();
            this.onProgramChange(this.selectedProgramId);
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
            
            const prog = this.currentProgram;
            if (prog && Array.isArray(prog.enrolled_students) && prog.enrolled_students.length > 0) {
                this.selectedStudents = prog.enrolled_students.map(s => ({ ...s }));
                if (prog.type === 'group') {
                    const ldr = this.selectedStudents.find(s => s.is_leader);
                    this.leaderStudentId = ldr ? ldr.id : (this.selectedStudents[0] ? this.selectedStudents[0].id : null);
                } else {
                    this.leaderStudentId = this.selectedStudents[0] ? this.selectedStudents[0].id : null;
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
            if (!this.currentProgram || !this.currentProgram.is_registered) return;
            if (!confirm(`Are you sure you want to remove all registered participants for "${this.currentProgram.name}"?`)) return;
            
            this.isSubmitting = true;
            this.feedbackSuccess = '';
            this.feedbackError = '';
            
            try {
                const res = await fetch(`{{ url('leader/registrations/by-program') }}/${this.currentProgram.id}`, {
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
                
                const prog = this.currentProgram;
                prog.enrolled = 0;
                prog.enrolled_students = [];
                prog.remaining = prog.limit;
                prog.is_full = false;
                prog.is_registered = false;
                prog.tag = `[0/${prog.limit} • ${prog.limit} left]`;
                prog.status_label = `0/${prog.limit} • ${prog.limit} left`;
                prog.status_type = 'unregistered';
                
                this.updateStudentCounts(data.updated_students);
                
                this.selectedStudents = [];
                this.leaderStudentId = null;
                this.updateProgramDropdown();
                this.feedbackSuccess = data.message || `All registrations for "${prog.name}" removed successfully.`;

                window.dispatchEvent(new CustomEvent('quaf-program-quota-updated', {
                    detail: {
                        program_id: prog.id,
                        enrolled: 0,
                        enrolled_students: [],
                        limit: prog.limit
                    }
                }));
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
            if (!this.currentProgram) {
                this.feedbackError = 'Please select a competition program first.';
                return;
            }
            if (this.selectedStudents.length > this.participantLimit) {
                this.feedbackError = `Maximum limit is ${this.participantLimit} participant(s). You have selected ${this.selectedStudents.length}.`;
                return;
            }
            if (this.selectedStudents.length === 0 && !this.currentProgram.is_registered) {
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
                
                // Registration / Update Success!
                const enrolledProg = this.currentProgram;
                const enrolledProgName = enrolledProg ? enrolledProg.name : 'Program';
                const freshEnrolled = data.enrolled_students || this.selectedStudents;
                const enrolledCount = data.enrolled_count !== undefined ? data.enrolled_count : freshEnrolled.length;
                
                if (enrolledProg) {
                    enrolledProg.enrolled = enrolledCount;
                    enrolledProg.enrolled_students = freshEnrolled;
                    enrolledProg.remaining = Math.max(0, enrolledProg.limit - enrolledProg.enrolled);
                    enrolledProg.is_full = (enrolledProg.enrolled >= enrolledProg.limit);
                    enrolledProg.is_registered = (enrolledProg.enrolled > 0);
                    if (enrolledProg.enrolled === 0) {
                        enrolledProg.tag = `[0/${enrolledProg.limit} • ${enrolledProg.limit} left]`;
                        enrolledProg.status_label = `0/${enrolledProg.limit} • ${enrolledProg.limit} left`;
                        enrolledProg.status_type = 'unregistered';
                    } else if (enrolledProg.enrolled < enrolledProg.limit) {
                        enrolledProg.tag = `[${enrolledProg.enrolled}/${enrolledProg.limit} • ${enrolledProg.remaining} left]`;
                        enrolledProg.status_label = `${enrolledProg.enrolled}/${enrolledProg.limit} • ${enrolledProg.remaining} left`;
                        enrolledProg.status_type = 'partial';
                    } else {
                        enrolledProg.tag = `[Full ${enrolledProg.enrolled}/${enrolledProg.limit}]`;
                        enrolledProg.status_label = `Full ${enrolledProg.enrolled}/${enrolledProg.limit}`;
                        enrolledProg.status_type = 'full';
                    }
                }
                
                // Update live student individual counts across memory
                this.updateStudentCounts(data.updated_students);

                // Keep leader on this program and reflect fresh enrolled roster
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

                window.dispatchEvent(new CustomEvent('quaf-program-quota-updated', {
                    detail: {
                        program_id: enrolledProg.id,
                        enrolled: enrolledProg.enrolled,
                        enrolled_students: enrolledProg.enrolled_students,
                        limit: enrolledProg.limit
                    }
                }));
            } catch (err) {
                this.feedbackError = 'Connection error: Could not complete registration. Please check your network.';
            } finally {
                this.isSubmitting = false;
            }
        }
    };
}

function quotaStatusTracker() {
    const progsList = window.quafRegistrationData ? (window.quafRegistrationData.programsStatusList || []) : [];
    const map = {};
    progsList.forEach(p => {
        map[p.id] = { ...p };
    });

    return {
        trackerTab: 'action_required', // 'action_required', 'partial', 'pending', 'completed', 'all'
        searchQuery: '',
        filterZone: '',
        filterType: '',
        programsMap: map,
        
        init() {
            window.quafQuotaTrackerInstance = this;
            window.addEventListener('quaf-program-quota-updated', (e) => {
                this.handleProgramQuotaUpdate(e.detail);
            });
        },

        getProg(id) {
            return this.programsMap[id] || {};
        },

        handleProgramQuotaUpdate(detail) {
            const prog = this.programsMap[detail.program_id];
            if (!prog) return;

            prog.enrolled = Number(detail.enrolled || 0);
            prog.enrolled_students = detail.enrolled_students || [];
            prog.remaining = Math.max(0, prog.limit - prog.enrolled);
            prog.needed = prog.remaining;
            prog.percent = prog.limit > 0 ? Math.min(100, Math.round((prog.enrolled / prog.limit) * 100)) : 0;

            if (prog.enrolled >= prog.limit) {
                prog.status_key = 'completed';
                prog.status_label = 'Complete';
            } else if (prog.enrolled > 0) {
                prog.status_key = 'partial';
                prog.status_label = `Partial (${prog.remaining} More Needed)`;
            } else {
                prog.status_key = 'pending';
                prog.status_label = `Pending (${prog.limit} To Fill)`;
            }
        },

        setTab(tab) {
            this.trackerTab = tab;
        },

        get allProgramsArray() {
            return Object.values(this.programsMap);
        },

        get completedCount() {
            return this.allProgramsArray.filter(p => p.enrolled >= p.limit).length;
        },

        get partialCount() {
            return this.allProgramsArray.filter(p => p.enrolled > 0 && p.enrolled < p.limit).length;
        },

        get pendingCount() {
            return this.allProgramsArray.filter(p => p.enrolled === 0).length;
        },

        get actionRequiredCount() {
            return this.allProgramsArray.filter(p => p.enrolled < p.limit).length;
        },

        get totalSlotsNeeded() {
            return this.allProgramsArray.reduce((sum, p) => sum + Math.max(0, p.limit - p.enrolled), 0);
        },

        isRowVisible(id) {
            const p = this.programsMap[id];
            if (!p) return false;

            // Tab filter
            if (this.trackerTab === 'action_required' && p.status_key === 'completed') return false;
            if (this.trackerTab === 'partial' && p.status_key !== 'partial') return false;
            if (this.trackerTab === 'pending' && p.status_key !== 'pending') return false;
            if (this.trackerTab === 'completed' && p.status_key !== 'completed') return false;

            // Zone filter
            if (this.filterZone) {
                const pZone = (p.zone_name || '').toLowerCase();
                const targetZ = this.filterZone.toLowerCase();
                if (!pZone.includes(targetZ) && !(targetZ.includes('mix') && p.is_mix_zone)) {
                    return false;
                }
            }

            // Type filter
            if (this.filterType && p.type !== this.filterType) return false;

            // Search query
            if (this.searchQuery) {
                const q = this.searchQuery.toLowerCase().trim();
                const code = (p.code || '').toLowerCase();
                const name = (p.name || '').toLowerCase();
                const mal = (p.malayalam_name || '').toLowerCase();
                if (!code.includes(q) && !name.includes(q) && !mal.includes(q)) {
                    return false;
                }
            }

            return true;
        },

        get hasVisibleRows() {
            return this.allProgramsArray.some(p => this.isRowVisible(p.id));
        },

        enrollProgram(p) {
            if (window.quafRegistrationManagerInstance) {
                window.quafRegistrationManagerInstance.selectProgram(p.zone_name, p.id);
            }
            const el = document.getElementById('enrollment_card');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth' });
            }
        }
    };
}
</script>

<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-sora font-black text-slate-900">Program Entries & Registrations</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">Enroll participants from {{ $group->name }} into cultural programs with automated zone and conflict checking.</p>
        </div>
    </div>

    <!-- Summary Counters (Total, Fully Registered, Partially Registered, Unregistered) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <button type="button" 
                onclick="window.quafSetTrackerTab('all')" 
                class="text-left p-5 rounded-3xl bg-white border border-slate-200 shadow-sm flex items-center justify-between hover:border-slate-400 hover:shadow-md transition-all cursor-pointer">
            <div>
                <span class="text-[11px] font-sora uppercase text-slate-400 font-bold block">Total Programs</span>
                <span class="text-2xl font-sora font-black text-slate-900">{{ $totalProgramsCount ?? $eligiblePrograms->count() }}</span>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </button>

        <button type="button" 
                onclick="window.quafSetTrackerTab('completed')" 
                class="text-left p-5 rounded-3xl bg-white border-2 border-emerald-500/40 shadow-sm flex items-center justify-between hover:border-emerald-500 hover:shadow-md transition-all cursor-pointer">
            <div>
                <span class="text-[11px] font-sora uppercase text-emerald-600 font-bold block">Fully Registered</span>
                <span class="text-2xl font-sora font-black text-emerald-700">{{ $fullyRegisteredCount ?? 0 }}</span>
            </div>
            <span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-sora font-bold">
                Full Quota
            </span>
        </button>

        <button type="button" 
                onclick="window.quafSetTrackerTab('partial')" 
                class="text-left p-5 rounded-3xl bg-white border-2 border-amber-500/40 shadow-sm flex items-center justify-between hover:border-amber-500 hover:shadow-md transition-all cursor-pointer">
            <div>
                <span class="text-[11px] font-sora uppercase text-amber-600 font-bold block">Partially Registered</span>
                <span class="text-2xl font-sora font-black text-amber-700">{{ $partiallyRegisteredCount ?? 0 }}</span>
            </div>
            <span class="px-2.5 py-1 rounded-xl bg-amber-100 text-amber-800 text-xs font-sora font-bold">
                {{ $statusSummary['partial_slots_needed'] ?? $partialSlotsNeeded ?? 0 }} Slots Needed
            </span>
        </button>

        <button type="button" 
                onclick="window.quafSetTrackerTab('pending')" 
                class="text-left p-5 rounded-3xl bg-white border border-slate-200 shadow-sm flex items-center justify-between hover:border-slate-400 hover:shadow-md transition-all cursor-pointer">
            <div>
                <span class="text-[11px] font-sora uppercase text-slate-400 font-bold block">Unregistered Programs</span>
                <span class="text-2xl font-sora font-black text-slate-700">{{ $unregisteredProgramsCount ?? 0 }}</span>
            </div>
            <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-600 text-xs font-sora font-bold">
                0 Enrolled
            </span>
        </button>
    </div>

    <!-- Registration Window Notice -->
    @if($isRegistrationOpen)
        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-sora flex items-center justify-between shadow-2xs">
            <span class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <strong>Registration Window Open:</strong> You can submit individual and group program registrations for your group.
            </span>
        </div>
    @else
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs font-sora flex items-center justify-between shadow-2xs">
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

    <!-- Registration Submission Card -->
    <div id="enrollment_card" class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm {{ !$isRegistrationOpen ? 'opacity-60 pointer-events-none' : '' }}">
        <div class="flex items-center gap-2 border-b border-slate-100 pb-4">
            <svg class="w-5 h-5 text-[#f3bd2e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            <h2 class="text-lg font-sora font-bold text-slate-900">Enroll Participant for Program</h2>
        </div>

        <form method="POST" action="{{ route('leader.registrations.store') }}" 
              @submit.prevent="submitRegistration($event)"
              x-data="registrationManager()"
              class="space-y-6">
            @csrf

            <!-- Reactive Feedback Banners -->
            <div x-show="feedbackSuccess" 
                 x-transition 
                 class="p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-400 text-emerald-900 text-xs font-sora font-bold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="feedbackSuccess"></span>
                </div>
                <button type="button" @click="feedbackSuccess = ''" class="text-emerald-700 hover:text-emerald-950 p-1 text-base leading-none">&times;</button>
            </div>

            <div x-show="feedbackError" 
                 x-transition 
                 class="p-4 rounded-2xl bg-red-50 border-2 border-red-300 text-red-900 text-xs font-sora font-bold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#be1e2d] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span x-text="feedbackError"></span>
                </div>
                <button type="button" @click="feedbackError = ''" class="text-red-700 hover:text-red-950 p-1 text-base leading-none">&times;</button>
            </div>

            <!-- STEP 1: ZONE SELECTION -->
            <div>
                <label class="block text-xs font-sora uppercase text-slate-700 mb-1.5 font-bold">
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
                <p class="text-[11px] font-sora text-slate-500 mt-1">Select the festival zone to filter competitions and eligible students from {{ $group->name }}.</p>
            </div>

            <!-- STEP 2: PROGRAM SELECTION (FILTERED BY ZONE) -->
            <div>
                <label class="block text-xs font-sora uppercase text-slate-700 mb-1.5 font-bold">
                    2. Select Program <span class="text-red-500">*</span>
                </label>
                <select name="program_id" 
                        id="program_select"
                        x-ref="programSelect"
                        x-model="selectedProgramId" 
                        @change="onProgramChange($event.target.value)" 
                        :disabled="!selectedZone"
                        required 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="">-- Please select a Zone first --</option>
                    @foreach($programsData as $pData)
                        <option value="{{ $pData['id'] }}">{{ $pData['name'] }} {{ $pData['tag'] }}</option>
                    @endforeach
                </select>
                <template x-if="currentProgram">
                    <div class="mt-2.5 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-sora flex flex-wrap items-center justify-between gap-2.5 shadow-2xs">
                        <div class="flex items-center gap-2">
                            <span>Type: <strong class="text-slate-800" x-text="currentProgram.type === 'group' ? 'Group Competition' : 'Individual Competition'"></strong></span>
                            <span class="text-slate-300">•</span>
                            <span>Limit: <strong class="text-slate-800 font-mono" x-text="currentProgram.limit"></strong></span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <template x-if="currentProgram.enrolled === 0">
                                <span class="px-2.5 py-1 rounded-xl bg-slate-200 text-slate-700 font-bold">
                                    Not Registered (0 / <span class="font-mono" x-text="currentProgram.limit"></span> slots)
                                </span>
                            </template>
                            <template x-if="currentProgram.enrolled > 0 && !currentProgram.is_full">
                                <span class="px-2.5 py-1 rounded-xl bg-amber-100 text-amber-900 border border-amber-300 font-bold flex items-center gap-1.5">
                                    <span>Partially Registered (<span class="font-mono" x-text="currentProgram.enrolled"></span>/<span class="font-mono" x-text="currentProgram.limit"></span>)</span>
                                    <span class="text-amber-700 font-black">— <span class="font-mono" x-text="currentProgram.remaining"></span> Slot(s) Available</span>
                                </span>
                            </template>
                            <template x-if="currentProgram.is_full">
                                <span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold">
                                    Quota Full (<span class="font-mono" x-text="currentProgram.enrolled"></span>/<span class="font-mono" x-text="currentProgram.limit"></span> slots filled)
                                </span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- STEP 3: PARTICIPANTS ENROLLMENT & ROSTER MANAGEMENT -->
            <template x-if="currentProgram">
                <div class="space-y-4 pt-2 border-t border-slate-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <label class="block text-xs font-sora uppercase text-slate-700 font-bold">
                                3. Selected Participants & Quota Roster <span class="text-red-500">*</span>
                            </label>
                            <p class="text-[11px] font-sora text-slate-500">
                                <template x-if="currentProgram.is_registered">
                                    <span>
                                        Showing registered students for this competition. You can <strong>Remove</strong> any student to open a slot and search to add a replacement.
                                    </span>
                                </template>
                                <template x-if="!currentProgram.is_registered">
                                    <span>
                                        Add up to <strong class="text-slate-800 font-mono" x-text="participantLimit"></strong> participant(s) from {{ $group->name }} for this competition.
                                    </span>
                                </template>
                                <span x-show="isGroup" class="text-amber-700 font-semibold ml-1">(Designate one student as Team Leader)</span>
                            </p>
                        </div>
                        <div class="text-xs font-sora px-3 py-1.5 rounded-xl border font-bold"
                             :class="selectedStudents.length >= participantLimit ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-orange-50 border-orange-200 text-orange-800'">
                            Enrolled: <span class="font-mono" x-text="selectedStudents.length"></span> / <span class="font-mono" x-text="participantLimit"></span> Slot(s)
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
                        <div class="p-3 bg-slate-100/80 font-sora text-xs font-bold text-slate-700 flex items-center justify-between">
                            <span>Selected Students (<span class="font-mono" x-text="selectedStudents.length"></span> of <span class="font-mono" x-text="participantLimit"></span>)</span>
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
                                            <span class="text-[11px] font-sora font-bold" 
                                                   :class="leaderStudentId == st.id ? 'text-brand-orange' : 'text-slate-400'" 
                                                   x-text="leaderStudentId == st.id ? 'LEADER' : 'Member'"></span>
                                        </label>
                                    </template>
                                    <div class="h-10 w-10 rounded-xl bg-brand-orange text-white flex flex-col items-center justify-center text-xs shadow-xs flex-shrink-0">
                                        <span class="text-[8px] uppercase font-bold text-orange-100 leading-tight font-sora">CHEST</span>
                                        <span class="font-mono font-bold" x-text="st.chest"></span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-sm text-slate-900" x-text="st.name"></span>
                                            <span class="font-mono text-xs text-brand-orange font-semibold" x-text="st.chest"></span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-sora">
                                            <span x-text="'Zone: ' + st.zone_name"></span>
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
                            No students currently selected. Search and add candidates below.
                        </div>
                    </div>

                    <!-- Search Input to Add Student -->
                    <div x-show="selectedStudents.length < participantLimit" class="relative" @click.outside="showStudentDropdown = false">
                        <label class="block text-xs font-sora uppercase text-slate-600 mb-1.5 font-bold">
                            Search & Add Student (Name or Chest Number)
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
                            <div class="p-2 text-slate-400 text-[10px] uppercase font-sora tracking-wider bg-slate-50 font-bold">
                                Eligible Candidates in <span x-text="selectedZone"></span> ({{ $group->name }})
                            </div>
                            <template x-for="st in availableStudents" :key="st.id">
                                <button type="button" 
                                        @click="addStudent(st)" 
                                        :disabled="!isGroup && isStudentIndividualLimitReached(st)"
                                        :class="!isGroup && isStudentIndividualLimitReached(st) ? 'opacity-60 cursor-not-allowed bg-slate-50' : 'hover:bg-orange-50'"
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
                                        <span class="text-[11px] text-slate-500 font-sora"><span class="font-mono font-bold text-slate-800" x-text="'Chest: ' + st.chest"></span> • Zone: <span x-text="st.zone_name"></span> • Class: <span x-text="st.class || '-'"></span></span>
                                    </div>
                                    <div>
                                        <template x-if="!isGroup && isStudentIndividualLimitReached(st)">
                                            <span class="px-2.5 py-1 rounded-lg bg-slate-200 text-slate-500 text-[11px] font-sora font-semibold">
                                                Max 5
                                            </span>
                                        </template>
                                        <template x-if="isGroup || !isStudentIndividualLimitReached(st)">
                                            <span class="px-2.5 py-1 rounded-lg bg-brand-orange text-white text-[11px] font-sora font-bold">
                                                + Add
                                            </span>
                                        </template>
                                    </div>
                                </button>
                            </template>
                            <div x-show="availableStudents.length === 0" class="p-3 text-center text-xs font-sora text-slate-400">
                                No matching eligible students found in {{ $group->name }} for <span x-text="selectedZone"></span>.
                            </div>
                        </div>
                    </div>

                    <div x-show="selectedStudents.length >= participantLimit" class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-sora font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Available quota limit reached (<span class="font-mono" x-text="selectedStudents.length"></span> of <span class="font-mono" x-text="participantLimit"></span>). To replace a student, click Remove above. Click Save & Update Participants below to confirm.</span>
                    </div>
                </div>
            </template>

            <div class="pt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100">
                <div>
                    <template x-if="currentProgram && currentProgram.is_registered">
                        <button type="button" 
                                @click="deleteCurrentProgramRegistration()" 
                                :disabled="isSubmitting"
                                class="px-4 py-2.5 rounded-xl text-xs font-sora font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Remove All Registrations for this Program</span>
                        </button>
                    </template>
                </div>

                <div class="flex items-center gap-3 ml-auto">
                    <button type="submit" 
                            :disabled="isSubmitting || !selectedProgramId || (selectedStudents.length === 0 && (!currentProgram || !currentProgram.is_registered)) || selectedStudents.length > participantLimit"
                            :class="isSubmitting || !selectedProgramId || (selectedStudents.length === 0 && (!currentProgram || !currentProgram.is_registered)) || selectedStudents.length > participantLimit ? 'opacity-60 cursor-not-allowed' : 'hover:bg-orange-600 active:scale-95 shadow-md shadow-orange-500/20'"
                            class="px-6 py-3 rounded-xl text-xs font-sora font-bold uppercase tracking-wider bg-brand-orange text-white transition-all flex items-center gap-2">
                        <template x-if="isSubmitting">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </template>
                        <span x-text="isSubmitting ? 'Saving Changes...' : (currentProgram && currentProgram.is_registered ? 'Save & Update Participants (' + selectedStudents.length + '/' + participantLimit + ')' : (selectedStudents.length > 1 ? 'Submit All ' + selectedStudents.length + ' Participants' : 'Submit Registration'))"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Active / Registered Entries Table -->
    <div id="entries-table-section" class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-sora font-bold text-slate-900">Registered Program Entries ({{ $entries->total() }})</h3>
                <p class="text-[11px] font-sora text-slate-500">All programs enrolled by {{ $group->name }}. You can edit participants or remove an entry while registration is open.</p>
            </div>
            <span class="px-3 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-sora font-bold">
                {{ $registeredProgramsCount ?? 0 }} Unique Programs Registered ({{ $fullyRegisteredCount ?? 0 }} Full • {{ $partiallyRegisteredCount ?? 0 }} Partial)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sora">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 uppercase">
                        <th class="py-3 px-3">Chest No</th>
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
                            $displayChest = ltrim((string)($displayLeader?->student_id ?: ($e->chest_number ?: '—')), '#');
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-3 font-bold text-brand-orange font-mono">{{ $displayChest }}</td>
                            <td class="py-3 px-3 font-sora font-semibold text-slate-900">
                                <span class="text-[10px] font-mono text-slate-400 mr-1">[{{ $e->program->code }}]</span>
                                {{ $e->program->name }}
                            </td>
                            <td class="py-3 px-3">{{ $e->program->zone?->name ?? $e->program->eligibility }}</td>
                            <td class="py-3 px-3 font-sora">
                                @if($displayLeader)
                                    <span class="font-bold text-slate-900">{{ $displayLeader->name }}</span>
                                    @if($isGroupComp)
                                        <span class="text-[10px] text-amber-700 font-sora font-bold block">
                                            (Group Leader • {{ max(1, $e->participants->count()) }} members)
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                @if(($e->program->type ?? 'individual') === 'group')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        Group
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        Individual
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $e->status === 'verified' || $e->status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : ($e->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $e->status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                @if($isRegistrationOpen)
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('leader.registrations.edit', $e) }}" 
                                           class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-brand-orange hover:text-white text-slate-700 font-sora text-[11px] font-bold transition-colors">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('leader.registrations.destroy', $e) }}" onsubmit="return confirm('Are you sure you want to remove this registration entry?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-50 hover:bg-red-600 hover:text-white text-red-700 font-sora text-[11px] font-bold transition-colors">
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

    <!-- Program Quota & Entry Tracker -->
    <div id="programs-tracker-section" class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm" x-data="quotaStatusTracker()">
        <!-- Header & Quick KPIs -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-brand-orange"></span>
                    <h3 class="text-xl font-sora font-black text-slate-900">Program Quota & Entry Tracker</h3>
                </div>
                <p class="text-xs font-sora text-slate-500 mt-1">
                    Live overview of all competitions for {{ $group->name }}. Track complete entries, pending programs, and exactly how many more students need to be enrolled.
                </p>
            </div>
            
            <!-- Quick Slot Summary Badges -->
            <div class="flex flex-wrap items-center gap-2">
                <div class="px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-xs font-sora font-bold flex items-center gap-1.5 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Slots Needed: <strong class="font-mono text-amber-950" x-text="totalSlotsNeeded"></strong></span>
                </div>
                <div class="px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs font-sora font-bold flex items-center gap-1.5 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Completed: <strong class="font-mono text-emerald-950" x-text="completedCount"></strong> / <span class="font-mono" x-text="programs.length"></span></span>
                </div>
            </div>
        </div>

        <!-- Filter Tabs (Action Required, Partial, Pending, Completed, All) -->
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-3">
            <button type="button" 
                    @click="setTab('action_required')" 
                    :class="trackerTab === 'action_required' ? 'bg-orange-600 text-white shadow-sm' : 'bg-orange-50 text-orange-900 hover:bg-orange-100 border border-orange-200'" 
                    class="px-3.5 py-2 rounded-xl text-xs font-sora font-bold transition-all flex items-center gap-2 cursor-pointer">
                <span>Action Required</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold" 
                      :class="trackerTab === 'action_required' ? 'bg-white text-orange-700' : 'bg-orange-200 text-orange-900'"
                      x-text="actionRequiredCount"></span>
            </button>

            <button type="button" 
                    @click="setTab('partial')" 
                    :class="trackerTab === 'partial' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-900 hover:bg-amber-100 border border-amber-200'" 
                    class="px-3.5 py-2 rounded-xl text-xs font-sora font-bold transition-all flex items-center gap-2 cursor-pointer">
                <span>Partial (Need More)</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold" 
                      :class="trackerTab === 'partial' ? 'bg-white text-amber-700' : 'bg-amber-200 text-amber-900'"
                      x-text="partialCount"></span>
            </button>

            <button type="button" 
                    @click="setTab('pending')" 
                    :class="trackerTab === 'pending' ? 'bg-slate-700 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200'" 
                    class="px-3.5 py-2 rounded-xl text-xs font-sora font-bold transition-all flex items-center gap-2 cursor-pointer">
                <span>Pending (0 Enrolled)</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold" 
                      :class="trackerTab === 'pending' ? 'bg-white text-slate-800' : 'bg-slate-200 text-slate-700'"
                      x-text="pendingCount"></span>
            </button>

            <button type="button" 
                    @click="setTab('completed')" 
                    :class="trackerTab === 'completed' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-900 hover:bg-emerald-100 border border-emerald-200'" 
                    class="px-3.5 py-2 rounded-xl text-xs font-sora font-bold transition-all flex items-center gap-2 cursor-pointer">
                <span>Completed</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold" 
                      :class="trackerTab === 'completed' ? 'bg-white text-emerald-700' : 'bg-emerald-200 text-emerald-900'"
                      x-text="completedCount"></span>
            </button>

            <button type="button" 
                    @click="setTab('all')" 
                    :class="trackerTab === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200'" 
                    class="px-3.5 py-2 rounded-xl text-xs font-sora font-bold transition-all flex items-center gap-2 cursor-pointer">
                <span>All Programs</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold" 
                      :class="trackerTab === 'all' ? 'bg-white text-slate-900' : 'bg-slate-200 text-slate-700'"
                      x-text="programs.length"></span>
            </button>
        </div>

        <!-- Filter and Search Toolbar -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="relative">
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Search by code, English or Malayalam name..." 
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs font-sora text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors pr-10">
                <span class="absolute right-3.5 top-3 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
            </div>

            <div>
                <select x-model="filterZone" 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-xs font-sora text-slate-700 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="">All Zones</option>
                    @foreach($zones as $z)
                        <option value="{{ $z->name }}">{{ $z->name }}</option>
                    @endforeach
                    <option value="Mix Zone">Mix Zone</option>
                </select>
            </div>

            <div>
                <select x-model="filterType" 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-xs font-sora text-slate-700 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="">All Competition Types</option>
                    <option value="individual">Individual Programs</option>
                    <option value="group">Group Programs</option>
                </select>
            </div>
        </div>

        <!-- Dynamic Quota Status Table -->
        <div class="overflow-x-auto max-h-[550px] overflow-y-auto border border-slate-100 rounded-2xl">
            <table class="w-full text-left text-xs font-sora">
                <thead class="sticky top-0 bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[11px] z-10">
                    <tr>
                        <th class="py-3 px-3.5">Code & Program</th>
                        <th class="py-3 px-3">Zone & Type</th>
                        <th class="py-3 px-3">Quota Status</th>
                        <th class="py-3 px-3">Action Needed</th>
                        <th class="py-3 px-3">Enrolled Candidates</th>
                        <th class="py-3 px-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                    @foreach($programsStatusList as $p)
                        @php
                            $pId = $p['id'];
                            $pEnrolled = $p['enrolled'];
                            $pLimit = $p['limit'];
                            $pRemaining = $p['remaining'];
                            $pPercent = $p['percent'];
                            $pKey = $p['status_key'];
                        @endphp
                        <tr x-show="isRowVisible({{ $pId }})" 
                            class="hover:bg-amber-50/30 transition-colors"
                            :class="getProg({{ $pId }}).status_key === 'partial' ? 'bg-amber-50/20' : (getProg({{ $pId }}).status_key === 'completed' ? 'bg-emerald-50/10' : '')">
                            <!-- Code & Program Name -->
                            <td class="py-3 px-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded text-[11px]">{{ $p['code'] }}</span>
                                    <span class="font-bold text-slate-900 text-sm">{{ $p['name'] }}</span>
                                </div>
                                @if(!empty($p['malayalam_name']))
                                    <span class="text-[11px] text-slate-500 block mt-0.5">{{ $p['malayalam_name'] }}</span>
                                @endif
                            </td>

                            <!-- Zone & Type -->
                            <td class="py-3 px-3 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <span class="font-semibold text-slate-700 text-xs">{{ $p['zone_name'] }}</span>
                                    @if($p['type'] === 'group')
                                        <span class="inline-block w-fit px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                            Group (<span class="font-mono">{{ $pLimit }}</span>)
                                        </span>
                                    @else
                                        <span class="inline-block w-fit px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            Individual (<span class="font-mono">{{ $pLimit }}</span>)
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Quota Progress & Slot Counts (SSR + Reactive) -->
                            <td class="py-3 px-3">
                                <div class="w-36">
                                    <div class="flex items-center justify-between text-[11px] mb-1">
                                        <span class="font-mono font-bold" 
                                              :class="getProg({{ $pId }}).status_key === 'completed' ? 'text-emerald-700' : (getProg({{ $pId }}).status_key === 'partial' ? 'text-amber-700' : 'text-slate-500')"
                                              x-text="getProg({{ $pId }}).enrolled + ' / ' + getProg({{ $pId }}).limit + ' Filled'">{{ $pEnrolled }} / {{ $pLimit }} Filled</span>
                                        <span class="font-mono text-[10px] text-slate-400" x-text="getProg({{ $pId }}).percent + '%'">{{ $pPercent }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                                        <div class="h-2 rounded-full transition-all duration-300"
                                             :class="getProg({{ $pId }}).status_key === 'completed' ? 'bg-emerald-500' : (getProg({{ $pId }}).status_key === 'partial' ? 'bg-amber-500' : 'bg-slate-300')"
                                             :style="'width: ' + getProg({{ $pId }}).percent + '%'"
                                             style="width: {{ $pPercent }}%"></div>
                                    </div>
                                    <span class="text-[10px] text-slate-400 block font-normal font-mono mt-0.5"
                                          x-text="'(' + getProg({{ $pId }}).remaining + ' slot' + (getProg({{ $pId }}).remaining === 1 ? '' : 's') + ' remaining)'">({{ $pRemaining }} slot{{ $pRemaining === 1 ? '' : 's' }} remaining)</span>
                                </div>
                            </td>

                            <!-- Action Needed (CRITICAL) -->
                            <td class="py-3 px-3 whitespace-nowrap">
                                <template x-if="getProg({{ $pId }}).status_key === 'completed'">
                                    <span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-bold inline-flex items-center gap-1">
                                        Quota Complete (0 needed)
                                    </span>
                                </template>

                                <template x-if="getProg({{ $pId }}).status_key === 'partial'">
                                    <span class="px-2.5 py-1 rounded-xl bg-amber-100 text-amber-950 border border-amber-400 text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span><strong class="font-mono font-black text-amber-900" x-text="getProg({{ $pId }}).remaining">{{ $pRemaining }}</strong> more needed</span>
                                    </span>
                                </template>

                                <template x-if="getProg({{ $pId }}).status_key === 'pending'">
                                    <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 border border-slate-300 text-xs font-bold inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                        <span><strong class="font-mono font-black" x-text="getProg({{ $pId }}).limit">{{ $pLimit }}</strong> needed (Not registered)</span>
                                    </span>
                                </template>
                            </td>

                            <!-- Enrolled Candidates Preview -->
                            <td class="py-3 px-3">
                                <template x-if="getProg({{ $pId }}).enrolled_students && getProg({{ $pId }}).enrolled_students.length > 0">
                                    <div class="flex flex-wrap items-center gap-1.5 max-w-xs">
                                        <template x-for="st in getProg({{ $pId }}).enrolled_students" :key="st.id">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-100 text-slate-800 border border-slate-200 text-[11px] font-sora">
                                                <span class="font-mono font-bold text-brand-orange" x-text="'#' + st.chest"></span>
                                                <span class="font-semibold truncate max-w-[100px]" x-text="st.name"></span>
                                                <template x-if="st.is_leader">
                                                    <span class="text-[9px] uppercase font-bold text-amber-700 bg-amber-100 px-1 rounded">Ldr</span>
                                                </template>
                                            </span>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!getProg({{ $pId }}).enrolled_students || getProg({{ $pId }}).enrolled_students.length === 0">
                                    <span class="text-slate-400 text-[11px] italic">None enrolled yet</span>
                                </template>
                            </td>

                            <!-- Action Button -->
                            <td class="py-3 px-3 text-right whitespace-nowrap">
                                @if($isRegistrationOpen)
                                    <button type="button" 
                                            @click="enrollProgram(getProg({{ $pId }}))" 
                                            :class="getProg({{ $pId }}).status_key === 'completed' ? 'bg-slate-100 hover:bg-slate-200 text-slate-700' : 'bg-brand-orange hover:bg-orange-600 text-white shadow-2xs'"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-sora font-bold transition-all cursor-pointer">
                                        <template x-if="getProg({{ $pId }}).status_key === 'completed'">
                                            <span>Edit Roster</span>
                                        </template>
                                        <template x-if="getProg({{ $pId }}).status_key === 'partial'">
                                            <span>+ Add Students</span>
                                        </template>
                                        <template x-if="getProg({{ $pId }}).status_key === 'pending'">
                                            <span>+ Enroll</span>
                                        </template>
                                    </button>
                                @else
                                    <span class="text-[11px] text-slate-400">Locked</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    <tr x-show="!hasVisibleRows">
                        <td colspan="6" class="py-12 text-center text-slate-400 font-sora text-xs">
                            <span class="block text-slate-500 font-semibold mb-1">No matching programs found.</span>
                            <span>Try adjusting your tab, search query, or zone filter.</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
