@extends('layouts.admin', ['title' => 'Student Wise Programs | QUAF 09'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Student Wise Programs</h1>
            <p class="text-xs text-slate-500 mt-0.5">Filter and view enrolled competitions by student participant.</p>
        </div>
        <div class="flex items-center gap-2.5">
            @if($selectedStudent)
                <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-[#be1e2d] text-white font-bold text-xs uppercase tracking-wider hover:bg-[#a01624] transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Print Sheet</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Filter Form (Hide on print) -->
    <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs print:hidden">
        <form method="GET" action="{{ route('admin.students.student-wise') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Select Group</label>
                <select name="group" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Groups</option>
                    @foreach($groups as $grp)
                        <option value="{{ $grp->id }}" {{ $selectedGroupId == $grp->id ? 'selected' : '' }}>{{ $grp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Select Zone</label>
                <select name="category" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($zones as $zKey => $zVal)
                        @php
                            $zoneName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                            $zoneValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                        @endphp
                        <option value="{{ $zoneValue }}" {{ ($selectedCategory ?? '') === $zoneValue ? 'selected' : '' }}>{{ $zoneName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="relative" x-data="{
                open: false,
                search: '{{ $selectedStudent ? addslashes($selectedStudent->name.' (#'.$selectedStudent->student_id.')') : '' }}',
                selectedStudentId: '{{ $selectedStudentId ?? '' }}',
                studentsList: [
                    @foreach($students as $st)
                        { id: '{{ $st->id }}', name: '{{ addslashes($st->name) }}', chest: '{{ $st->student_id }}', label: '{{ addslashes($st->name) }} (#{{ $st->student_id }})' },
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
                },
                clear() {
                    this.selectedStudentId = '';
                    this.search = '';
                    this.open = true;
                }
            }" @click.outside="open = false">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Select Student</label>
                <input type="hidden" name="student" :value="selectedStudentId">
                <div class="relative">
                    <input type="text"
                           x-model="search"
                           @focus="open = true"
                           @input="open = true"
                           placeholder="Type Name or Chest #..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d] pr-8">
                    <button type="button" x-show="selectedStudentId" @click="clear()" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <!-- Dropdown results -->
                <div x-show="open"
                     x-transition
                     class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-lg divide-y divide-slate-100 text-xs"
                     style="display: none;">
                    <div class="p-2 text-slate-400 text-[10px] uppercase font-mono tracking-wider bg-slate-50">
                        Type to search by name or chest no
                    </div>
                    <template x-for="s in filtered" :key="s.id">
                        <button type="button"
                                @click="select(s)"
                                class="w-full text-left px-3 py-2 hover:bg-slate-50 flex items-center justify-between transition-colors">
                            <span class="font-medium text-slate-800" x-text="s.name"></span>
                            <span class="font-mono text-[11px] text-[#be1e2d] font-semibold" x-text="'#' + s.chest"></span>
                        </button>
                    </template>
                    <div x-show="filtered.length === 0" class="p-3 text-center text-slate-400 text-xs">
                        No students match your search
                    </div>
                </div>
            </div>
            <div>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs uppercase tracking-wider transition-colors shadow-sm">
                    View Programs
                </button>
            </div>
        </form>
    </div>

    <!-- Selected Student & Programs Table -->
    @if($selectedStudent)
        <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-2xs">
            <!-- Student Header (Matches Screenshot) -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4 text-xs font-sora">
                <div>
                    <span class="text-slate-500">Student Id:</span>
                    <span class="font-bold text-slate-900 ml-1">{{ $selectedStudent->student_id }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Name:</span>
                    <span class="font-bold text-slate-900 ml-1">{{ $selectedStudent->name }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Team:</span>
                    <span class="font-bold text-slate-900 ml-1">{{ $selectedStudent->group?->name }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Class:</span>
                    <span class="font-bold text-slate-900 ml-1">{{ $selectedStudent->class_level ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Zone:</span>
                    <span class="font-bold text-slate-900 ml-1">{{ $selectedStudent->category }}</span>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-sora">
                    <thead class="bg-slate-50/50 text-slate-500 uppercase border-b border-slate-200 text-[11px] font-semibold">
                        <tr>
                            <th class="px-6 py-3.5">No</th>
                            <th class="px-6 py-3.5">Program Id</th>
                            <th class="px-6 py-3.5">Program Name</th>
                            <th class="px-6 py-3.5">Zone</th>
                            <th class="px-6 py-3.5">Type</th>
                            <th class="px-6 py-3.5">Stage</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($selectedStudent->entries as $idx => $entry)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-3.5 font-bold text-slate-900">{{ $idx + 1 }}</td>
                                <td class="px-6 py-3.5 font-mono font-medium text-slate-600">{{ $entry->program?->code }}</td>
                                <td class="px-6 py-3.5 font-semibold text-slate-900">{{ $entry->program?->name }}</td>
                                <td class="px-6 py-3.5 text-slate-500">{{ $entry->program?->eligibility ?? 'A Zone' }}</td>
                                <td class="px-6 py-3.5 capitalize">{{ $entry->program?->type }}</td>
                                <td class="px-6 py-3.5 text-slate-500">{{ $entry->program?->stage ? 'Stage' : 'Non-stage' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-400">No programs registered for this student yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($selectedStudentId)
        <div class="p-8 text-center bg-white rounded-2xl border border-slate-200 text-slate-400 text-xs">
            Student not found.
        </div>
    @else
        <div class="p-12 text-center bg-white rounded-2xl border border-slate-200 text-slate-400 text-xs">
            Please select a student above to view registered competition programs.
        </div>
    @endif
</div>
@endsection
