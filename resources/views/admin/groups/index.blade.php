@extends('layouts.admin', ['title' => 'Groups'])

@section('content')
<div class="space-y-4" x-data="{ newTeamOpen: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 font-sans">Groups</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage official festival competition groups and leaders</p>
        </div>
        <div>
            <button @click="newTeamOpen = true" class="px-4 py-2 rounded-lg bg-[#be1e2d] hover:bg-[#a01624] text-white font-medium text-xs flex items-center gap-1.5 shadow-xs transition-colors">
                <span>+</span> <span>New Group</span>
            </button>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-xs">
        <form method="GET" action="{{ route('admin.groups.index') }}" class="flex items-center justify-between gap-3">
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search groups or managers..."
                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-900 focus:outline-none focus:border-[#be1e2d] transition-all">
            </div>
            @if($search)
                <a href="{{ route('admin.groups.index') }}" class="px-3 py-1.5 text-xs text-slate-500 hover:text-slate-800 bg-slate-100 rounded-lg">Reset</a>
            @endif
        </form>
    </div>

    <!-- Groups Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sans">
                <thead class="bg-white text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Group</th>
                        <th class="px-5 py-3.5">Manager / Leader</th>
                        <th class="px-5 py-3.5">Phone</th>
                        <th class="px-5 py-3.5">Assignments Modified</th>
                        <th class="px-5 py-3.5 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($groups as $grp)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-3.5 font-medium text-slate-900">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-3 h-3 rounded-full border border-slate-300 shadow-xs shrink-0" style="background-color: {{ $grp->color_hex }}"></span>
                                    <div>
                                        <span class="font-bold text-slate-900 block">{{ $grp->name }}</span>
                                        <span class="text-[10px] text-slate-500 font-mono">{{ $grp->code }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">
                                {{ $grp->manager_name ?: ($grp->admin_username ?: '—') }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">
                                {{ $grp->manager_contact ?: '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-500">
                                {{ $grp->updated_at ? $grp->updated_at->diffForHumans() : '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right relative" x-data="{ menuOpen: false }">
                                <button @click="menuOpen = !menuOpen" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                                    <span class="font-bold text-sm leading-none">···</span>
                                </button>
                                <div x-show="menuOpen" @click.away="menuOpen = false" class="absolute right-5 mt-1 w-32 bg-white border border-slate-200 rounded-lg shadow-lg py-1 z-20 text-left text-xs" style="display: none;">
                                    <a href="{{ route('admin.groups.show', $grp) }}" class="block px-3 py-1.5 text-slate-700 hover:bg-slate-50">View Details</a>
                                    <a href="{{ route('admin.groups.edit', $grp) }}" class="block px-3 py-1.5 text-slate-700 hover:bg-slate-50">Edit</a>
                                    <form method="POST" action="{{ route('admin.groups.destroy', $grp) }}" onsubmit="return confirm('Delete this group?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-left px-3 py-1.5 text-red-600 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                                No groups found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Slide-Over Drawer Modal: New Group -->
    <div x-show="newTeamOpen" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
        <!-- Backdrop -->
        <div x-show="newTeamOpen" @click="newTeamOpen = false" class="absolute inset-0 bg-slate-900/40 backdrop-blur-2xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div x-show="newTeamOpen" class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                
                <!-- Drawer Header -->
                <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900 font-sans">New Group</h2>
                    <button @click="newTeamOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Drawer Body / Form -->
                <form method="POST" action="{{ route('admin.groups.store') }}" class="p-6 space-y-4 overflow-y-auto flex-1 text-xs font-sans">
                    @csrf

                    <!-- Group Name -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Group Name</label>
                        <input type="text" name="name" required placeholder="Enter group name"
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900">
                    </div>

                    <!-- Manager Name -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Manager Name</label>
                        <input type="text" name="manager_name" placeholder="Enter manager name"
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900">
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Phone Number</label>
                        <input type="text" name="manager_contact" placeholder="Enter phone number"
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900">
                    </div>

                    <!-- Name in Results -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Name in Results</label>
                        <input type="text" name="name_in_results" placeholder="Enter name to be shown in results"
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900">
                    </div>

                    <!-- Name in Certificates -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Name in Certificates</label>
                        <input type="text" name="name_in_certificates" placeholder="Enter name to be shown in certificates"
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900">
                    </div>

                    <!-- Admin Username -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Admin Username</label>
                        <input type="text" name="admin_username" placeholder="Enter admin username"
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900">
                    </div>

                    <!-- Admin Password with Generate -->
                    <div x-data="{ pass: '', generate() { this.pass = Math.random().toString(36).slice(-8) + Math.random().toString(36).slice(-4); } }">
                        <label class="block font-medium text-slate-700 mb-1">Admin Password</label>
                        <div class="relative flex items-center">
                            <input type="text" name="admin_password" x-model="pass" placeholder="Enter password"
                                   class="w-full px-3 py-2 pr-24 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900">
                            <button type="button" @click="generate()" class="absolute right-1.5 px-2.5 py-1 text-[11px] font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded flex items-center gap-1">
                                <span>👁️</span> <span>Generate</span>
                            </button>
                        </div>
                    </div>

                    <!-- Assistant Managers -->
                    <div x-data="{ rows: [] }">
                        <label class="block font-medium text-slate-700 mb-1">Assistant Managers</label>
                        <template x-for="(row, idx) in rows" :key="idx">
                            <div class="flex items-center gap-2 mb-2">
                                <input type="text" :name="'assistant_managers['+idx+']'" placeholder="Assistant manager name"
                                       class="flex-1 px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-900">
                                <button type="button" @click="rows.splice(idx, 1)" class="text-red-500 hover:text-red-700">✕</button>
                            </div>
                        </template>
                        <button type="button" @click="rows.push('')" class="text-xs text-slate-600 hover:text-slate-900 font-medium flex items-center gap-1 mt-1">
                            <span>+</span> <span>Add Row</span>
                        </button>
                    </div>

                    <!-- Drawer Footer Buttons -->
                    <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
                        <button type="button" @click="newTeamOpen = false" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-medium">
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
