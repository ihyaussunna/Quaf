@extends('layouts.admin', ['title' => 'Participants'])

@section('content')
<div class="space-y-4" x-data="{ newParticipantOpen: false, manualEntry: true }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 font-sans">Participants</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage registrations and chest numbers</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.print.students') }}" target="_blank" class="px-3.5 py-2 rounded-lg bg-white border border-slate-200 text-slate-800 font-semibold text-xs hover:bg-slate-50 hover:border-[#be1e2d] transition-all flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print / PDF Report</span>
            </a>
            <button @click="newParticipantOpen = true" class="px-4 py-2 rounded-lg bg-[#be1e2d] hover:bg-[#a01624] text-white font-medium text-xs flex items-center gap-1.5 shadow-xs transition-colors">
                <span>+</span> <span>New Participant</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-xs">
        <form method="GET" action="{{ route('admin.students.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="relative w-full sm:w-96">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search participants..."
                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-900 focus:outline-none focus:border-[#be1e2d] transition-all">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="category" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($categories as $val => $label)
                        <option value="{{ $val }}" {{ $category == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="group" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Teams</option>
                    @foreach($groups as $grp)
                        <option value="{{ $grp->id }}" {{ $groupId == $grp->id ? 'selected' : '' }}>{{ $grp->name }}</option>
                    @endforeach
                </select>

                @if($search || $category || $groupId)
                    <a href="{{ route('admin.students.index') }}" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 bg-slate-100 rounded-lg">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Participants Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sans">
                <thead class="bg-white text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Name</th>
                        <th class="px-5 py-3.5">Chest No.</th>
                        <th class="px-5 py-3.5">Class</th>
                        <th class="px-5 py-3.5">Zone</th>
                        <th class="px-5 py-3.5">Team</th>
                        <th class="px-5 py-3.5 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($students as $s)
                        @php
                            // Extract initials
                            $words = explode(' ', trim($s->name));
                            $initials = '';
                            foreach (array_slice($words, 0, 2) as $w) {
                                $initials .= strtoupper(substr($w, 0, 1));
                            }
                            $initials = $initials ?: 'ST';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-full bg-red-100 text-[#be1e2d] font-bold text-xs flex items-center justify-center flex-shrink-0">
                                        {{ $initials }}
                                    </span>
                                    <div>
                                        <a href="{{ route('admin.students.show', $s) }}" class="font-medium text-slate-900 hover:text-[#be1e2d] transition-colors">
                                            {{ $s->name }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-medium font-mono text-slate-800">
                                {{ str_replace('QUAF-ST-', '', $s->student_id) }}
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-slate-700 font-mono">
                                {{ $s->class_level ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-mono bg-slate-100 text-slate-700 font-medium">
                                    {{ $s->category }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">
                                {{ $s->group?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right relative" x-data="{ menuOpen: false }">
                                <button @click="menuOpen = !menuOpen" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                                    <span class="font-bold text-sm leading-none">···</span>
                                </button>
                                <div x-show="menuOpen" @click.away="menuOpen = false" class="absolute right-5 mt-1 w-32 bg-white border border-slate-200 rounded-lg shadow-lg py-1 z-20 text-left text-xs" style="display: none;">
                                    <a href="{{ route('admin.students.show', $s) }}" class="block px-3 py-1.5 text-slate-700 hover:bg-slate-50">View 360°</a>
                                    <a href="{{ route('admin.students.edit', $s) }}" class="block px-3 py-1.5 text-slate-700 hover:bg-slate-50">Edit</a>
                                    <form method="POST" action="{{ route('admin.students.destroy', $s) }}" onsubmit="return confirm('Delete this participant?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-left px-3 py-1.5 text-red-600 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                No participants found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="p-3 border-t border-slate-100">
                {{ $students->links() }}
            </div>
        @endif
    </div>

    <!-- Slide-Over Drawer Modal: New Participant (From Screenshot 4) -->
    <div x-show="newParticipantOpen" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
        <!-- Backdrop -->
        <div x-show="newParticipantOpen" @click="newParticipantOpen = false" class="absolute inset-0 bg-slate-900/40 backdrop-blur-2xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div x-show="newParticipantOpen" class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                
                <!-- Drawer Header -->
                <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900 font-sans">New Participant</h2>
                    <button @click="newParticipantOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Drawer Body / Form -->
                <form method="POST" action="{{ route('admin.students.store') }}" class="p-6 space-y-4 overflow-y-auto flex-1 text-xs font-sans">
                    @csrf

                    <!-- Category / Zone -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Zone</label>
                        <select name="category" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900">
                            <option value="">Select Zone</option>
                            @foreach($categories as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Group -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Group</label>
                        <select name="group_id" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900">
                            <option value="">Select Group</option>
                            @foreach($groups as $grp)
                                <option value="{{ $grp->id }}">{{ $grp->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Participant Name / Manual Entry -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Participant Name *</label>
                        <input type="text" name="name" required placeholder="Enter participant full name"
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900">
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="manual_check" x-model="manualEntry" class="rounded text-[#be1e2d] focus:ring-[#be1e2d]">
                        <label for="manual_check" class="text-xs text-slate-600">Participant not listed? Enter manually</label>
                    </div>

                    <!-- Participant Photo Upload Area -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Participant Photo</label>
                        <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center hover:border-slate-300 transition-colors cursor-pointer bg-slate-50/50">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-1.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            </div>
                            <p class="text-xs font-medium text-slate-700">Drop file here, or click to select</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Upload a photo (max 5MB)</p>
                        </div>
                    </div>

                    <!-- Mobile -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Mobile *</label>
                        <input type="text" name="contact" required placeholder="Enter mobile number"
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900">
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Date of Birth *</label>
                        <input type="date" name="dob"
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900">
                    </div>


                    <!-- Drawer Footer Buttons -->
                    <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
                        <button type="button" @click="newParticipantOpen = false" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-medium">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-lg bg-[#be1e2d] hover:bg-[#a01624] text-white text-xs font-medium shadow-xs">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
