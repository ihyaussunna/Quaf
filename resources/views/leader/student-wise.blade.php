@extends('layouts.leader')

@section('title', 'Student Wise Programs - Leader Panel')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-sora text-gray-900">Student Wise Programs</h1>
            <p class="text-xs text-gray-500 mt-1 font-sora">Check individual program participation and schedules for students</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-5 py-2 bg-brand-orange text-white rounded-xl text-xs font-bold hover:bg-orange-600 transition shadow-xs flex items-center gap-1.5 font-sora">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print
            </button>
        </div>
    </div>

    <!-- Search / Select Student -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs">
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

    @php
        $studentsToDisplay = $selectedStudent ? collect([$selectedStudent]) : $students;
    @endphp

    <!-- Student Cards matching screenshot -->
    <div class="space-y-6">
        @forelse($studentsToDisplay as $studentItem)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                <!-- Header matching screenshot -->
                <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs font-sora">
                    <div>
                        <span class="text-gray-400 font-medium">Name:</span>
                        <span class="font-bold text-gray-800 ml-1 capitalize">{{ $studentItem->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Chest No:</span>
                        <span class="font-bold text-gray-800 ml-1 font-mono">{{ ltrim((string)($studentItem->chest_number ?: ($studentItem->student_id ?: $studentItem->id)), '#') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Zone:</span>
                        <span class="font-bold text-gray-800 ml-1">{{ $studentItem->category ?? 'ZONE A' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Team:</span>
                        <span class="font-bold text-gray-800 ml-1">{{ $group->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Class:</span>
                        <span class="font-bold text-gray-800 ml-1">{{ $studentItem->class_level ?? '-' }}</span>
                    </div>
                </div>

                <!-- Table matching screenshot -->
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
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($studentItem->entries as $index => $entry)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-4 py-3 text-center text-gray-500 text-xs font-medium font-mono">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-gray-700 font-mono text-xs">{{ $entry->program?->code ?: $entry->program?->id }}</td>
                                    <td class="px-6 py-3 text-gray-900 font-medium capitalize">{{ $entry->program?->name }}</td>
                                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $entry->program?->eligibility ?? 'A Zone' }}</td>
                                    <td class="px-4 py-3 text-gray-600 text-xs">{{ ucfirst($entry->program?->type ?? 'Individual') }}</td>
                                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $entry->program?->is_stage ? 'Stage' : 'Non-stage' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-400 text-xs">
                                        No registered programs found for this student.
                                    </td>
                                </tr>
                            @endforelse
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
</div>
@endsection
