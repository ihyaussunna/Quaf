@extends('layouts.admin', ['title' => 'Group Details: ' . $group->name])

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="{ activeTab: 'students', searchStudent: '', searchEntry: '' }">
    <!-- Breadcrumb & Actions Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.groups.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-1.5 block font-semibold">← Back to Groups</a>
            <div class="flex items-center gap-3">
                <span class="w-4 h-4 rounded-full border border-slate-300 shadow-xs shrink-0" style="background-color: {{ $group->color_hex ?? '#be1e2d' }}"></span>
                <h1 class="text-2xl sm:text-3xl font-sora font-black text-slate-900">{{ $group->name }}</h1>
                <span class="px-2.5 py-1 text-xs font-mono font-bold rounded-lg border bg-slate-100 text-slate-700 border-slate-200">{{ $group->code }}</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.groups.edit', $group) }}" class="px-4 py-2 rounded-xl bg-white border border-slate-300 text-xs font-mono text-slate-700 hover:bg-slate-50 shadow-xs font-semibold transition-colors">
                Edit Group
            </a>
            <a href="{{ route('admin.students.create') }}?group_id={{ $group->id }}" class="px-4 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white text-xs font-mono font-semibold shadow-xs transition-colors">
                + Add Student
            </a>
        </div>
    </div>

    <!-- Group Summary & Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block">Rank</span>
            <span class="text-2xl font-sora font-black text-slate-900 mt-1 block">#{{ $group->rank_cache ?: '—' }}</span>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block">Total Points</span>
            <span class="text-2xl font-sora font-black text-[#be1e2d] mt-1 block">{{ $group->points_cache ?? 0 }}</span>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block">Total Students</span>
            <span class="text-2xl font-sora font-black text-slate-900 mt-1 block">{{ $group->students->count() }}</span>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block">A Zone</span>
            <span class="text-2xl font-sora font-black text-emerald-600 mt-1 block">{{ $group->students->where('category', 'A Zone')->count() }}</span>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block">B Zone</span>
            <span class="text-2xl font-sora font-black text-amber-600 mt-1 block">{{ $group->students->where('category', 'B Zone')->count() }}</span>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block">C Zone</span>
            <span class="text-2xl font-sora font-black text-blue-600 mt-1 block">{{ $group->students->where('category', 'C Zone')->count() }}</span>
        </div>
    </div>

    <!-- Leadership & Credentials Info Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs">
        <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-slate-900 mb-4 pb-2 border-b border-slate-100">
            Leadership & Management Details
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 text-xs font-sora">
            <div>
                <span class="text-slate-400 font-mono uppercase tracking-wider text-[11px] block font-semibold mb-1">Official Manager</span>
                <span class="font-bold text-slate-900 text-sm block">{{ $group->manager_name ?: '—' }}</span>
                @if($group->manager_contact)
                    <span class="text-slate-500 font-mono mt-0.5 block">{{ $group->manager_contact }}</span>
                @endif
            </div>

            <div>
                <span class="text-slate-400 font-mono uppercase tracking-wider text-[11px] block font-semibold mb-1">Assistant Managers</span>
                @php
                    $assistants = is_array($group->assistant_managers) 
                        ? $group->assistant_managers 
                        : (is_string($group->assistant_managers) && !empty($group->assistant_managers) 
                            ? json_decode($group->assistant_managers, true) ?? [$group->assistant_managers] 
                            : []);
                @endphp
                @if(!empty($assistants))
                    <ul class="space-y-1">
                        @foreach($assistants as $asst)
                            <li class="font-medium text-slate-800">{{ $asst }}</li>
                        @endforeach
                    </ul>
                @else
                    <span class="text-slate-400 font-mono">—</span>
                @endif
            </div>

            <div>
                <span class="text-slate-400 font-mono uppercase tracking-wider text-[11px] block font-semibold mb-1">Assigned Leader Account</span>
                @if($group->leader)
                    <span class="font-bold text-slate-900 block">{{ $group->leader->name }}</span>
                    <span class="text-slate-500 font-mono block">{{ $group->leader->email }}</span>
                    @if($group->leader->phone)
                        <span class="text-slate-500 font-mono block">{{ $group->leader->phone }}</span>
                    @endif
                @else
                    <span class="text-slate-400 font-mono">No linked user account</span>
                @endif
            </div>

            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5">
                <span class="text-slate-500 font-mono uppercase tracking-wider text-[10px] block font-bold mb-1.5">Portal Credentials</span>
                <div class="space-y-1 font-mono text-[11px]">
                    <div class="text-slate-600 truncate">
                        <span class="text-slate-400">User:</span> {{ $group->leader?->email ?? $group->admin_username ?? '—' }}
                    </div>
                    <div class="text-slate-600">
                        <span class="text-slate-400">Pass:</span> <span class="bg-white px-1.5 py-0.5 rounded border border-slate-200 text-slate-800 font-bold select-all">{{ $group->admin_password ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs (Students / Entries) -->
    <div class="border-b border-slate-200 flex items-center gap-6">
        <button type="button" @click="activeTab = 'students'" 
                :class="activeTab === 'students' ? 'border-[#be1e2d] text-[#be1e2d] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="py-3 px-1 border-b-2 text-xs font-mono uppercase tracking-wider transition-colors flex items-center gap-2">
            <span>Students Roster</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 text-slate-700" :class="activeTab === 'students' ? 'bg-red-50 text-[#be1e2d]' : ''">
                {{ $group->students->count() }}
            </span>
        </button>

        <button type="button" @click="activeTab = 'entries'" 
                :class="activeTab === 'entries' ? 'border-[#be1e2d] text-[#be1e2d] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="py-3 px-1 border-b-2 text-xs font-mono uppercase tracking-wider transition-colors flex items-center gap-2">
            <span>Program Registrations</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 text-slate-700" :class="activeTab === 'entries' ? 'bg-red-50 text-[#be1e2d]' : ''">
                {{ $group->entries->count() }}
            </span>
        </button>
    </div>

    <!-- Tab 1: Students Roster -->
    <div x-show="activeTab === 'students'" class="space-y-4">
        <!-- Search Filter -->
        <div class="flex items-center justify-between gap-4">
            <div class="relative w-full sm:w-80">
                <input type="text" x-model="searchStudent" placeholder="Filter by chest no, name, class..."
                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-white text-slate-900 focus:outline-none focus:border-[#be1e2d] transition-all">
            </div>
            <span class="text-xs font-mono text-slate-400">Total: {{ $group->students->count() }} participants</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-sora">
                    <thead class="bg-slate-50/80 text-slate-600 font-semibold border-b border-slate-200 font-mono uppercase text-[11px]">
                        <tr>
                            <th class="px-5 py-3.5">Chest No</th>
                            <th class="px-5 py-3.5">Full Name</th>
                            <th class="px-5 py-3.5">Class / Level</th>
                            <th class="px-5 py-3.5">Zone</th>
                            <th class="px-5 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($group->students as $student)
                            <tr class="hover:bg-slate-50/70 transition-colors"
                                x-show="searchStudent === '' || '{{ strtolower($student->student_id . ' ' . $student->chest_number . ' ' . $student->name . ' ' . $student->class_level . ' ' . $student->category) }}'.includes(searchStudent.toLowerCase())">
                                <td class="px-5 py-3 font-mono font-bold text-slate-900">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 border border-slate-200 text-xs">
                                        {{ $student->student_id ?: $student->chest_number }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 font-bold text-slate-900">
                                    {{ $student->name }}
                                </td>
                                <td class="px-5 py-3 font-mono text-slate-600">
                                    {{ $student->class_level ?: ($student->class ?: '—') }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-mono font-semibold
                                        @if($student->category === 'A Zone') bg-emerald-50 text-emerald-700 border border-emerald-200
                                        @elseif($student->category === 'B Zone') bg-amber-50 text-amber-700 border border-amber-200
                                        @elseif($student->category === 'C Zone') bg-blue-50 text-blue-700 border border-blue-200
                                        @else bg-slate-100 text-slate-700 border border-slate-200
                                        @endif">
                                        {{ $student->category ?: ($student->zone?->name ?: '—') }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('admin.students.show', $student) }}" class="text-xs font-mono font-bold text-[#f3bd2e] hover:underline">
                                        View Profile &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-slate-400 font-mono">
                                    No students registered under this group yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 2: Program Registrations -->
    <div x-show="activeTab === 'entries'" class="space-y-4" style="display: none;">
        <!-- Search Filter -->
        <div class="flex items-center justify-between gap-4">
            <div class="relative w-full sm:w-80">
                <input type="text" x-model="searchEntry" placeholder="Filter by program, code, status..."
                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-white text-slate-900 focus:outline-none focus:border-[#be1e2d] transition-all">
            </div>
            <span class="text-xs font-mono text-slate-400">Total: {{ $group->entries->count() }} entries</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-sora">
                    <thead class="bg-slate-50/80 text-slate-600 font-semibold border-b border-slate-200 font-mono uppercase text-[11px]">
                        <tr>
                            <th class="px-5 py-3.5">Code</th>
                            <th class="px-5 py-3.5">Program</th>
                            <th class="px-5 py-3.5">Category</th>
                            <th class="px-5 py-3.5">Candidate / Chest</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($group->entries as $entry)
                            <tr class="hover:bg-slate-50/70 transition-colors"
                                x-show="searchEntry === '' || '{{ strtolower($entry->program?->code . ' ' . $entry->program?->name . ' ' . $entry->status . ' ' . $entry->chest_number) }}'.includes(searchEntry.toLowerCase())">
                                <td class="px-5 py-3 font-mono font-bold text-slate-900">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 border border-slate-200 text-xs">
                                        {{ $entry->program?->code ?: '—' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 font-bold text-slate-900">
                                    {{ $entry->program?->name ?: '—' }}
                                </td>
                                <td class="px-5 py-3 text-slate-500">
                                    {{ $entry->program?->category?->name ?: '—' }}
                                </td>
                                <td class="px-5 py-3 font-mono text-slate-800">
                                    @if($entry->student)
                                        <span class="font-bold">{{ $entry->student->name }}</span>
                                        <span class="text-slate-400">({{ $entry->student->student_id ?: $entry->chest_number }})</span>
                                    @else
                                        <span>{{ $entry->chest_number ?: 'Group Entry' }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold
                                        @if($entry->status === 'confirmed' || $entry->status === 'verified') bg-emerald-50 text-emerald-700 border border-emerald-200
                                        @elseif($entry->status === 'registered') bg-blue-50 text-blue-700 border border-blue-200
                                        @elseif($entry->status === 'rejected') bg-red-50 text-red-700 border border-red-200
                                        @else bg-slate-100 text-slate-700 border border-slate-200
                                        @endif">
                                        {{ $entry->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    @if($entry->program)
                                        <a href="{{ route('admin.programs.show', $entry->program) }}" class="text-xs font-mono font-bold text-[#f3bd2e] hover:underline">
                                            View Program &rarr;
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400 font-mono">
                                    No program entries registered for this group yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
