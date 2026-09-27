@extends('layouts.admin', ['title' => 'Judges'])

@section('content')
<div class="space-y-4" x-data="{ newJudgeOpen: false, compSearch: '' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 font-sora">Judges</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage event judges</p>
        </div>
        <div>
            <button @click="newJudgeOpen = true" class="px-4 py-2 rounded-lg bg-[#be1e2d] hover:bg-[#a01624] text-white font-medium text-xs flex items-center gap-1.5 shadow-xs transition-colors">
                <span>+</span> <span>New Judge</span>
            </button>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-xs">
        <form method="GET" action="{{ route('admin.judges.index') }}" class="flex items-center justify-between gap-3">
            <div class="relative w-full sm:w-96">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search judges..."
                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-900 focus:outline-none focus:border-[#be1e2d] transition-all">
            </div>
            @if($search)
                <a href="{{ route('admin.judges.index') }}" class="px-3 py-1.5 text-xs text-slate-500 hover:text-slate-800 bg-slate-100 rounded-lg">Reset</a>
            @endif
        </form>
    </div>

    <!-- Judges Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sora">
                <thead class="bg-white text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Name</th>
                        <th class="px-5 py-3.5">Access PIN</th>
                        <th class="px-5 py-3.5">Mobile</th>
                        <th class="px-5 py-3.5">Notes</th>
                        <th class="px-5 py-3.5">Competitions</th>
                        <th class="px-5 py-3.5 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($judges as $j)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-3.5 font-medium text-slate-900">
                                {{ $j->name }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-mono font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-lg text-xs tracking-widest">
                                    {{ $j->access_code ?: 'N/A' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">
                                {{ $j->contact ?: '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-500">
                                {{ $j->notes ?: '—' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @forelse($j->programs->take(2) as $p)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-normal bg-slate-100 border border-slate-200 text-slate-700">
                                            {{ $p->eligibility ? $p->eligibility . ' - ' : '' }}{{ Str::limit($p->name, 22) }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 font-normal">—</span>
                                    @endforelse

                                    @if($j->programs->count() > 2)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600">
                                            +{{ $j->programs->count() - 2 }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-right relative" x-data="{ menuOpen: false }">
                                <button @click="menuOpen = !menuOpen" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                                    <span class="font-bold text-sm leading-none">···</span>
                                </button>
                                <div x-show="menuOpen" @click.away="menuOpen = false" class="absolute right-5 mt-1 w-44 bg-white border border-slate-200 rounded-lg shadow-lg py-1 z-20 text-left text-xs" style="display: none;">
                                    <a href="{{ route('admin.judges.edit', $j) }}" class="block px-3 py-1.5 text-slate-700 hover:bg-slate-50">Edit Judge</a>
                                    <form method="POST" action="{{ route('admin.judges.regenerate-pin', $j) }}" onsubmit="return confirm('Generate a new tough PIN for {{ $j->name }}?');">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-3 py-1.5 text-amber-800 hover:bg-amber-50 font-medium">
                                            Regenerate Tough PIN
                                        </button>
                                    </form>
                                    <div class="border-t border-slate-100 my-1"></div>
                                    <form method="POST" action="{{ route('admin.judges.destroy', $j) }}" onsubmit="return confirm('Delete this judge?');">
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
                                No judges found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Slide-Over Drawer Modal: New Judge (From Screenshot 5) -->
    <div x-show="newJudgeOpen" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
        <!-- Backdrop -->
        <div x-show="newJudgeOpen" @click="newJudgeOpen = false" class="absolute inset-0 bg-slate-900/40 backdrop-blur-2xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div x-show="newJudgeOpen" class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                
                <!-- Drawer Header -->
                <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900 font-sora">New Judge</h2>
                    <button @click="newJudgeOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Drawer Body / Form -->
                <form method="POST" action="{{ route('admin.judges.store') }}" class="p-6 space-y-4 overflow-y-auto flex-1 text-xs font-sora">
                    @csrf

                    <!-- Name -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Name</label>
                        <input type="text" name="name" required placeholder="Enter judge name"
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900">
                    </div>

                    <!-- Mobile -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Mobile</label>
                        <input type="text" name="contact" placeholder="Enter mobile number"
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900">
                    </div>

                    <!-- Access PIN -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-medium text-slate-700">Access PIN</label>
                            <button type="button" onclick="document.getElementById('newJudgeAccessCode').value = Math.floor(1000 + Math.random() * 9000);" class="text-[11px] text-[#be1e2d] hover:underline font-bold">
                                Generate Tough PIN
                            </button>
                        </div>
                        <input type="text" id="newJudgeAccessCode" name="access_code" maxlength="6" placeholder="Leave blank to auto-generate tough PIN"
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900 font-mono tracking-widest font-bold">
                        <p class="text-[10px] text-slate-400 mt-1">Leave empty to automatically generate a secure tough PIN.</p>
                    </div>

                    <!-- Notes (optional) -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Notes <span class="text-slate-400 font-normal">(optional)</span></label>
                        <textarea name="notes" rows="3" placeholder="Optional notes about judge qualifications or jury assignment"
                                  class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-[#be1e2d] text-slate-900"></textarea>
                    </div>

                    <!-- Competitions (Multi-select searchable dropdown) -->
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Competitions</label>
                        
                        <div class="border border-slate-200 rounded-lg p-2.5 space-y-2 bg-slate-50/50">
                            <!-- Search inside dropdown -->
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </span>
                                <input type="text" x-model="compSearch" placeholder="Search competitions..."
                                       class="w-full pl-7 pr-2.5 py-1 text-xs rounded border border-slate-200 bg-white text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                            </div>

                            <!-- Checklist -->
                            <div class="max-h-48 overflow-y-auto space-y-1 pr-1">
                                @foreach($programs as $prog)
                                    <label class="flex items-center gap-2 p-1.5 rounded hover:bg-slate-100 cursor-pointer text-xs"
                                           x-show="!compSearch || '{{ strtolower(($prog->eligibility ?? 'A Zone') . ' ' . $prog->name) }}'.includes(compSearch.toLowerCase())">
                                        <input type="checkbox" name="program_ids[]" value="{{ $prog->id }}" class="rounded text-[#be1e2d] focus:ring-[#be1e2d]">
                                        <span class="text-slate-800">
                                            <span class="text-slate-400">{{ $prog->eligibility ?? 'A Zone' }} -</span> {{ $prog->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Drawer Footer Buttons -->
                    <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
                        <button type="button" @click="newJudgeOpen = false" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-medium">
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
