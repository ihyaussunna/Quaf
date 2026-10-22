@extends('layouts.admin', ['title' => 'Competitions'])

@section('content')
<div class="space-y-4" x-data="{
    selectedIds: [],
    allIds: @json($programs->pluck('id')->values()),
    toggleAll() {
        if (this.selectedIds.length === this.allIds.length) {
            this.selectedIds = [];
        } else {
            this.selectedIds = [...this.allIds];
        }
    },
    isSelected(id) {
        return this.selectedIds.includes(Number(id));
    },
    toggleId(id) {
        const num = Number(id);
        if (this.selectedIds.includes(num)) {
            this.selectedIds = this.selectedIds.filter(x => x !== num);
        } else {
            this.selectedIds.push(num);
        }
    },
    clearSelection() {
        this.selectedIds = [];
    },
    regOverrides: {},
    regLoading: {},
    isProgRegOpen(id, defaultVal) {
        if (this.regOverrides[id] !== undefined) {
            return this.regOverrides[id];
        }
        return defaultVal;
    },
    async toggleProgramRegistration(progId, url) {
        if (this.regLoading[progId]) return;
        this.regLoading[progId] = true;
        try {
            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '{{ csrf_token() }}';
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json();
            if (data.success) {
                this.regOverrides[progId] = data.is_registration_open;
            } else {
                alert(data.message || 'Error updating registration status.');
            }
        } catch (e) {
            console.error(e);
            alert('Error updating registration status.');
        } finally {
            this.regLoading[progId] = false;
        }
    },
    bulkLoading: false,
    async bulkToggle(target, action) {
        if (this.bulkLoading) return;
        if (target === 'selected' && this.selectedIds.length === 0) {
            alert('Please select competitions first.');
            return;
        }
        this.bulkLoading = true;
        try {
            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '{{ csrf_token() }}';
            const payload = {
                target: target,
                action: action,
                program_ids: target === 'selected' ? this.selectedIds : []
            };
            const res = await fetch('{{ route('admin.programs.bulk-toggle-registration') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                if (target === 'selected') {
                    this.selectedIds.forEach(id => {
                        this.regOverrides[id] = (action === 'open');
                    });
                }
            } else {
                alert(data.message || 'Error updating registration status.');
            }
        } catch (e) {
            console.error(e);
            alert('Error updating registration status.');
        } finally {
            this.bulkLoading = false;
        }
    },
    showBulkModeModal: false,
    bulkModeChoice: 'online',
    bulkDeadline: '',
    bulkModeLoading: false,
    async submitBulkMode() {
        if (this.bulkModeLoading) return;
        if (this.selectedIds.length === 0) {
            alert('Please select at least one competition.');
            return;
        }
        this.bulkModeLoading = true;
        try {
            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '{{ csrf_token() }}';
            const payload = {
                mode: this.bulkModeChoice,
                program_ids: this.selectedIds,
                deadline: this.bulkDeadline || null
            };
            const res = await fetch('{{ route('admin.programs.bulk-set-submission-mode') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error updating competition mode.');
            }
        } catch (e) {
            console.error(e);
            alert('Error updating competition mode.');
        } finally {
            this.bulkModeLoading = false;
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 font-sora">Competitions</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage events, schedule categories, and registration status</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.print.programs') }}" target="_blank" class="px-3.5 py-2 rounded-lg bg-white border border-slate-200 text-slate-800 font-semibold text-xs hover:bg-slate-50 hover:border-[#be1e2d] transition-all flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print / PDF Report</span>
            </a>
            <a href="{{ route('admin.programs.create') }}" class="px-4 py-2 rounded-lg bg-[#be1e2d] hover:bg-[#a01624] text-white font-medium text-xs flex items-center gap-1.5 shadow-xs transition-colors">
                <span>+</span> <span>New Competition</span>
            </a>
        </div>
    </div>

    <!-- Program Summary Counts & Quick Filter Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2.5">
        <!-- Total Competitions -->
        <a href="{{ route('admin.programs.index') }}"
           class="p-2.5 rounded-xl border transition-all shadow-xs flex flex-col justify-between {{ empty($status) && ($isStage === null || $isStage === '') && empty($submissionType) ? 'bg-slate-900 border-slate-900 text-white ring-2 ring-slate-900/10' : 'bg-white border-slate-200 text-slate-800 hover:border-slate-300 hover:bg-slate-50/50' }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ empty($status) && ($isStage === null || $isStage === '') && empty($submissionType) ? 'text-slate-300' : 'text-slate-500' }}">Total</span>
                <span class="text-[10px] font-mono font-semibold {{ empty($status) && ($isStage === null || $isStage === '') && empty($submissionType) ? 'text-slate-300' : 'text-slate-400' }}">All</span>
            </div>
            <div class="mt-2">
                <span class="text-xl font-black font-sora {{ empty($status) && ($isStage === null || $isStage === '') && empty($submissionType) ? 'text-white' : 'text-slate-900' }}">{{ $counts['total'] }}</span>
                <span class="block text-[9px] {{ empty($status) && ($isStage === null || $isStage === '') && empty($submissionType) ? 'text-slate-300' : 'text-slate-400' }} font-sora mt-0.5">Competitions</span>
            </div>
        </a>

        <!-- Upcoming -->
        <a href="{{ route('admin.programs.index', array_merge(request()->except(['page']), ['status' => 'upcoming'])) }}"
           class="p-2.5 rounded-xl border transition-all shadow-xs flex flex-col justify-between {{ $status === 'upcoming' ? 'bg-amber-500 border-amber-600 text-white ring-2 ring-amber-500/20' : 'bg-white border-slate-200 text-slate-800 hover:border-amber-300 hover:bg-amber-50/20' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full {{ $status === 'upcoming' ? 'bg-white' : 'bg-amber-500' }}"></span>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ $status === 'upcoming' ? 'text-amber-100' : 'text-amber-800' }}">Upcoming</span>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-xl font-black font-sora {{ $status === 'upcoming' ? 'text-white' : 'text-amber-700' }}">{{ $counts['upcoming'] }}</span>
                <span class="block text-[9px] {{ $status === 'upcoming' ? 'text-amber-100' : 'text-slate-400' }} font-sora mt-0.5">To be held</span>
            </div>
        </a>

        <!-- In Progress -->
        <a href="{{ route('admin.programs.index', array_merge(request()->except(['page']), ['status' => 'in_progress'])) }}"
           class="p-2.5 rounded-xl border transition-all shadow-xs flex flex-col justify-between {{ $status === 'in_progress' ? 'bg-blue-600 border-blue-700 text-white ring-2 ring-blue-600/20' : 'bg-white border-slate-200 text-slate-800 hover:border-blue-300 hover:bg-blue-50/20' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full {{ $status === 'in_progress' ? 'bg-white animate-pulse' : 'bg-blue-500 animate-pulse' }}"></span>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ $status === 'in_progress' ? 'text-blue-100' : 'text-blue-800' }}">Ongoing</span>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-xl font-black font-sora {{ $status === 'in_progress' ? 'text-white' : 'text-blue-700' }}">{{ $counts['in_progress'] }}</span>
                <span class="block text-[9px] {{ $status === 'in_progress' ? 'text-blue-100' : 'text-slate-400' }} font-sora mt-0.5">In Progress</span>
            </div>
        </a>

        <!-- Completed -->
        <a href="{{ route('admin.programs.index', array_merge(request()->except(['page']), ['status' => 'completed'])) }}"
           class="p-2.5 rounded-xl border transition-all shadow-xs flex flex-col justify-between {{ $status === 'completed' ? 'bg-emerald-600 border-emerald-700 text-white ring-2 ring-emerald-600/20' : 'bg-white border-slate-200 text-slate-800 hover:border-emerald-300 hover:bg-emerald-50/20' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full {{ $status === 'completed' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ $status === 'completed' ? 'text-emerald-100' : 'text-emerald-800' }}">Done</span>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-xl font-black font-sora {{ $status === 'completed' ? 'text-white' : 'text-emerald-700' }}">{{ $counts['completed'] }}</span>
                <span class="block text-[9px] {{ $status === 'completed' ? 'text-emerald-100' : 'text-slate-400' }} font-sora mt-0.5">Evaluated</span>
            </div>
        </a>

        <!-- Stage Events -->
        <a href="{{ route('admin.programs.index', array_merge(request()->except(['page', 'submission_type']), ['is_stage' => '1'])) }}"
           class="p-2.5 rounded-xl border transition-all shadow-xs flex flex-col justify-between {{ ($isStage ?? '') === '1' ? 'bg-[#be1e2d] border-[#be1e2d] text-white ring-2 ring-[#be1e2d]/20' : 'bg-white border-slate-200 text-slate-800 hover:border-rose-300 hover:bg-rose-50/20' }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ ($isStage ?? '') === '1' ? 'text-rose-100' : 'text-slate-500' }}">Stage</span>
                <span class="text-[10px] font-mono font-semibold {{ ($isStage ?? '') === '1' ? 'text-rose-100' : 'text-slate-400' }}">Live</span>
            </div>
            <div class="mt-2">
                <span class="text-xl font-black font-sora {{ ($isStage ?? '') === '1' ? 'text-white' : 'text-slate-900' }}">{{ $counts['stage'] }}</span>
                <span class="block text-[9px] {{ ($isStage ?? '') === '1' ? 'text-rose-100' : 'text-slate-400' }} font-sora mt-0.5">Venues</span>
            </div>
        </a>

        <!-- Off-Stage Events -->
        <a href="{{ route('admin.programs.index', array_merge(request()->except(['page', 'submission_type']), ['is_stage' => '0'])) }}"
           class="p-2.5 rounded-xl border transition-all shadow-xs flex flex-col justify-between {{ ($isStage ?? '') === '0' && empty($submissionType) ? 'bg-indigo-600 border-indigo-700 text-white ring-2 ring-indigo-600/20' : 'bg-white border-slate-200 text-slate-800 hover:border-indigo-300 hover:bg-indigo-50/20' }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ ($isStage ?? '') === '0' && empty($submissionType) ? 'text-indigo-100' : 'text-slate-500' }}">Off-Stage</span>
                <span class="text-[10px] font-mono font-semibold {{ ($isStage ?? '') === '0' && empty($submissionType) ? 'text-indigo-100' : 'text-slate-400' }}">Exams</span>
            </div>
            <div class="mt-2">
                <span class="text-xl font-black font-sora {{ ($isStage ?? '') === '0' && empty($submissionType) ? 'text-white' : 'text-slate-900' }}">{{ $counts['off_stage'] }}</span>
                <span class="block text-[9px] {{ ($isStage ?? '') === '0' && empty($submissionType) ? 'text-indigo-100' : 'text-slate-400' }} font-sora mt-0.5">Non-Stage</span>
            </div>
        </a>

        <!-- Online Submissions -->
        <a href="{{ route('admin.programs.index', array_merge(request()->except(['page', 'is_stage']), ['submission_type' => 'online'])) }}"
           class="p-2.5 rounded-xl border transition-all shadow-xs flex flex-col justify-between {{ ($submissionType ?? '') === 'online' ? 'bg-blue-600 border-blue-700 text-white ring-2 ring-blue-600/20' : 'bg-white border-slate-200 text-slate-800 hover:border-blue-300 hover:bg-blue-50/20' }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ ($submissionType ?? '') === 'online' ? 'text-blue-100' : 'text-blue-700' }}">Online</span>
                <span class="text-[10px] font-mono font-semibold {{ ($submissionType ?? '') === 'online' ? 'text-blue-100' : 'text-blue-500' }}">Portal</span>
            </div>
            <div class="mt-2">
                <span class="text-xl font-black font-sora {{ ($submissionType ?? '') === 'online' ? 'text-white' : 'text-blue-900' }}">{{ $counts['online_submission'] }}</span>
                <span class="block text-[9px] {{ ($submissionType ?? '') === 'online' ? 'text-blue-100' : 'text-slate-400' }} font-sora mt-0.5">Uploads</span>
            </div>
        </a>

        <!-- Office Submissions -->
        <a href="{{ route('admin.programs.index', array_merge(request()->except(['page', 'is_stage']), ['submission_type' => 'office'])) }}"
           class="p-2.5 rounded-xl border transition-all shadow-xs flex flex-col justify-between {{ ($submissionType ?? '') === 'office' ? 'bg-amber-600 border-amber-700 text-white ring-2 ring-amber-600/20' : 'bg-white border-slate-200 text-slate-800 hover:border-amber-300 hover:bg-amber-50/20' }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ ($submissionType ?? '') === 'office' ? 'text-amber-100' : 'text-amber-700' }}">Office</span>
                <span class="text-[10px] font-mono font-semibold {{ ($submissionType ?? '') === 'office' ? 'text-amber-100' : 'text-amber-500' }}">Direct</span>
            </div>
            <div class="mt-2">
                <span class="text-xl font-black font-sora {{ ($submissionType ?? '') === 'office' ? 'text-white' : 'text-amber-900' }}">{{ $counts['office_submission'] }}</span>
                <span class="block text-[9px] {{ ($submissionType ?? '') === 'office' ? 'text-amber-100' : 'text-slate-400' }} font-sora mt-0.5">Physical</span>
            </div>
        </a>
    </div>

    <!-- Category Registration Control Widgets (Stage & Off-Stage) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
        <!-- Stage Programs Registration Toggle Widget -->
        <div class="bg-white border-2 {{ ($isStageRegOpen ?? true) ? 'border-emerald-500/30' : 'border-rose-500/30' }} rounded-xl p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ ($isStageRegOpen ?? true) ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                    <h3 class="text-xs font-bold font-sora text-slate-900 uppercase tracking-wide">Stage Programs Registration</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase {{ ($isStageRegOpen ?? true) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ ($isStageRegOpen ?? true) ? 'OPEN' : 'CLOSED' }}
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 font-sora">
                    {{ ($isStageRegOpen ?? true) ? 'Group leaders can submit entries for stage competitions.' : 'Registration for all stage competitions is currently locked.' }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <form method="POST" action="{{ route('admin.programs.bulk-toggle-registration') }}">
                    @csrf
                    <input type="hidden" name="target" value="stage">
                    <input type="hidden" name="action" value="{{ ($isStageRegOpen ?? true) ? 'close' : 'open' }}">
                    <button type="submit" class="w-full sm:w-auto px-3.5 py-1.5 rounded-lg text-xs font-bold font-mono uppercase tracking-wider transition-all shadow-2xs {{ ($isStageRegOpen ?? true) ? 'bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                        {{ ($isStageRegOpen ?? true) ? 'Close Stage Registration' : 'Open Stage Registration' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Off-Stage Programs Registration Toggle Widget -->
        <div class="bg-white border-2 {{ ($isOffStageRegOpen ?? true) ? 'border-emerald-500/30' : 'border-rose-500/30' }} rounded-xl p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ ($isOffStageRegOpen ?? true) ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                    <h3 class="text-xs font-bold font-sora text-slate-900 uppercase tracking-wide">Off-Stage Programs Registration</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase {{ ($isOffStageRegOpen ?? true) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ ($isOffStageRegOpen ?? true) ? 'OPEN' : 'CLOSED' }}
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 font-sora">
                    {{ ($isOffStageRegOpen ?? true) ? 'Group leaders can submit entries for off-stage competitions.' : 'Registration for all off-stage competitions is currently locked.' }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <form method="POST" action="{{ route('admin.programs.bulk-toggle-registration') }}">
                    @csrf
                    <input type="hidden" name="target" value="off_stage">
                    <input type="hidden" name="action" value="{{ ($isOffStageRegOpen ?? true) ? 'close' : 'open' }}">
                    <button type="submit" class="w-full sm:w-auto px-3.5 py-1.5 rounded-lg text-xs font-bold font-mono uppercase tracking-wider transition-all shadow-2xs {{ ($isOffStageRegOpen ?? true) ? 'bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                        {{ ($isOffStageRegOpen ?? true) ? 'Close Off-Stage Registration' : 'Open Off-Stage Registration' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-xs">
        <form method="GET" action="{{ route('admin.programs.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <!-- Search -->
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search competitions..."
                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-900 focus:outline-none focus:border-[#be1e2d] transition-all">
            </div>

            <!-- Dropdowns -->
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Statuses</option>
                    <option value="upcoming" {{ $status === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                <select name="reg_status" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Registrations</option>
                    <option value="open" {{ ($regStatus ?? '') === 'open' ? 'selected' : '' }}>Reg: Open</option>
                    <option value="closed" {{ ($regStatus ?? '') === 'closed' ? 'selected' : '' }}>Reg: Closed</option>
                </select>

                <select name="is_stage" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Stages</option>
                    <option value="1" {{ ($isStage ?? '') === '1' ? 'selected' : '' }}>Stage Programs</option>
                    <option value="0" {{ ($isStage ?? '') === '0' ? 'selected' : '' }}>Non-Stage (Off-Stage)</option>
                </select>

                <select name="submission_type" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Submission Modes</option>
                    <option value="online" {{ ($submissionType ?? '') === 'online' ? 'selected' : '' }}>Online Portal Submissions</option>
                    <option value="office" {{ ($submissionType ?? '') === 'office' ? 'selected' : '' }}>Festival Office Submissions</option>
                    <option value="none" {{ ($submissionType ?? '') === 'none' ? 'selected' : '' }}>Standard Off-Stage (No Submission)</option>
                </select>

                <select name="type" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Types</option>
                    <option value="individual" {{ ($type ?? '') === 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="group" {{ ($type ?? '') === 'group' ? 'selected' : '' }}>Group</option>
                </select>

                <select name="zone" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($zones as $zKey => $zVal)
                        @php
                            $zoneName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                            $zoneValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                        @endphp
                        <option value="{{ $zoneValue }}" {{ ($zone ?? '') === $zoneValue ? 'selected' : '' }}>{{ $zoneName }}</option>
                    @endforeach
                </select>

                @if($search || $status || $zone || ($type ?? '') || ($regStatus ?? '') || ($isStage !== null && $isStage !== '') || ($submissionType ?? ''))
                    <a href="{{ route('admin.programs.index') }}" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 bg-slate-100 rounded-lg">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Floating Bulk Selection Action Bar for Selected Programs -->
    <div x-show="selectedIds.length > 0" 
         x-transition 
         class="bg-slate-900 text-white rounded-xl p-3 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-3 border border-slate-800"
         style="display: none;">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
            <span class="font-sora text-xs font-bold">
                <span class="font-mono text-amber-300" x-text="selectedIds.length"></span> Competitions Selected
            </span>
            <button type="button" @click="clearSelection()" class="text-xs text-slate-400 hover:text-white underline ml-2 font-mono">
                Clear
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Set Submission Mode Button -->
            <button type="button"
                    @click="showBulkModeModal = true"
                    :disabled="bulkLoading || bulkModeLoading"
                    class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold font-mono uppercase tracking-wider transition-all flex items-center gap-1 shadow-sm cursor-pointer">
                <span>Set Mode / Deadline (രീതി മാറ്റുക)</span>
            </button>

            <!-- Open Registration Button -->
            <button type="button"
                    @click.prevent="bulkToggle('selected', 'open')"
                    :disabled="bulkLoading"
                    class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold font-mono uppercase tracking-wider transition-all flex items-center gap-1 shadow-sm cursor-pointer">
                <span>Open Registration</span>
            </button>

            <!-- Close Registration Button -->
            <button type="button"
                    @click.prevent="bulkToggle('selected', 'close')"
                    :disabled="bulkLoading"
                    class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold font-mono uppercase tracking-wider transition-all flex items-center gap-1 shadow-sm cursor-pointer">
                <span>Close Registration</span>
            </button>
        </div>
    </div>

    <!-- Bulk Submission Mode Modal -->
    <div x-show="showBulkModeModal"
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
         style="display: none;">
        <div @click.away="showBulkModeModal = false"
             class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold font-sora text-slate-900">Set Competition & Submission Mode</h3>
                    <p class="text-xs text-slate-500 font-sora mt-0.5">
                        Update mode for <span class="font-bold text-amber-600 font-mono" x-text="selectedIds.length"></span> selected competitions
                    </p>
                </div>
                <button type="button" @click="showBulkModeModal = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold">×</button>
            </div>

            <div class="space-y-3">
                <label class="block text-xs font-mono uppercase font-bold text-slate-700">Select Mode (രീതി തിരഞ്ഞെടുക്കുക)</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50"
                           :class="bulkModeChoice === 'online' ? 'border-blue-500 bg-blue-50/40 ring-1 ring-blue-500' : ''">
                        <input type="radio" name="bulk_mode" value="online" x-model="bulkModeChoice" class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-xs font-bold font-sora text-slate-900 block">Online Portal Submission</span>
                            <span class="text-[11px] text-blue-700 font-ml block">വെബ്സൈറ്റ് ഓൺലൈൻ പോർട്ടൽ വഴി ഫയലുകൾ സബ്മിറ്റ് ചെയ്യുന്നത്</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50"
                           :class="bulkModeChoice === 'office' ? 'border-amber-500 bg-amber-50/40 ring-1 ring-amber-500' : ''">
                        <input type="radio" name="bulk_mode" value="office" x-model="bulkModeChoice" class="text-amber-600 focus:ring-amber-500">
                        <div>
                            <span class="text-xs font-bold font-sora text-slate-900 block">Festival Office Submission</span>
                            <span class="text-[11px] text-amber-800 font-ml block">ഫെസ്റ്റിവൽ ഓഫീസിൽ നേരിട്ട് സമർപ്പിക്കുന്നത്</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50"
                           :class="bulkModeChoice === 'stage' ? 'border-[#be1e2d] bg-rose-50/40 ring-1 ring-[#be1e2d]' : ''">
                        <input type="radio" name="bulk_mode" value="stage" x-model="bulkModeChoice" class="text-[#be1e2d] focus:ring-[#be1e2d]">
                        <div>
                            <span class="text-xs font-bold font-sora text-slate-900 block">Stage Competition</span>
                            <span class="text-[11px] text-[#be1e2d] font-ml block">വേദിയിൽ നടക്കുന്ന സ്റ്റേജ് മത്സരം</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50"
                           :class="bulkModeChoice === 'offstage' ? 'border-slate-600 bg-slate-50 ring-1 ring-slate-600' : ''">
                        <input type="radio" name="bulk_mode" value="offstage" x-model="bulkModeChoice" class="text-slate-700 focus:ring-slate-600">
                        <div>
                            <span class="text-xs font-bold font-sora text-slate-900 block">Standard Off-Stage / Exam</span>
                            <span class="text-[11px] text-slate-700 font-ml block">ക്ലാസ്സ് റൂം / ഹാൾ പരീക്ഷകൾ / ജനറൽ ഓഫ്-സ്റ്റേജ്</span>
                        </div>
                    </label>
                </div>

                <div class="pt-2">
                    <label class="block text-xs font-mono uppercase font-bold text-slate-700 mb-1">
                        Deadline / Scheduled Time (ഓപ്ഷണൽ)
                    </label>
                    <input type="datetime-local" x-model="bulkDeadline"
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <span class="text-[10px] text-slate-500 font-sora mt-0.5 block">Leave empty to keep existing scheduled time intact.</span>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" @click="showBulkModeModal = false"
                        class="px-4 py-2 rounded-xl text-xs font-mono text-slate-600 hover:text-slate-900 bg-slate-100">
                    Cancel
                </button>
                <button type="button" @click="submitBulkMode()"
                        :disabled="bulkModeLoading"
                        class="px-4 py-2 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50 flex items-center gap-1.5">
                    <span x-text="bulkModeLoading ? 'Updating...' : 'Apply Mode to Selected'">Apply Mode to Selected</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Competitions Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sora">
                <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 uppercase text-[11px] font-mono">
                    <tr>
                        <th class="px-4 py-3.5 w-10 text-center">
                            <input type="checkbox" 
                                   @change="toggleAll()" 
                                   :checked="selectedIds.length === allIds.length && allIds.length > 0" 
                                   class="rounded border-slate-300 text-[#be1e2d] focus:ring-[#be1e2d] cursor-pointer">
                        </th>
                        <th class="px-4 py-3.5">Prog ID</th>
                        <th class="px-5 py-3.5">Programme Name</th>
                        <th class="px-4 py-3.5">Type</th>
                        <th class="px-4 py-3.5">Mode & Schedule</th>
                        <th class="px-4 py-3.5">Zone</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5">Limit</th>
                        <th class="px-4 py-3.5">Entries</th>
                        <th class="px-4 py-3.5 text-center">Registration</th>
                        <th class="px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($programs as $p)
                        @php
                            $isDirectOpen = (bool) ($p->is_registration_open ?? true);
                            $isEffectiveOpen = $p->isRegistrationOpen();
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors" :class="isSelected({{ $p->id }}) ? 'bg-amber-50/40' : ''">
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <input type="checkbox" 
                                       :value="{{ $p->id }}" 
                                       :checked="isSelected({{ $p->id }})" 
                                       @change="toggleId({{ $p->id }})" 
                                       class="rounded border-slate-300 text-[#be1e2d] focus:ring-[#be1e2d] cursor-pointer">
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-1 rounded font-mono font-bold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $p->code }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-slate-900 block text-sm">{{ $p->name }}</span>
                                @if($p->malayalam_name)
                                    <span class="text-[11px] text-[#005c94] font-medium block mt-0.5 font-ml">{{ $p->malayalam_name }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if(($p->type ?? 'individual') === 'group')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        Group
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        Individual
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($p->is_stage)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-block">
                                        Stage (വേദി)
                                    </span>
                                @elseif($p->submission_type === 'online' || $p->onlineSubmissionForm)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 inline-block">
                                        Online Portal
                                    </span>
                                @elseif($p->submission_type === 'office')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 inline-block">
                                        Office Submit
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 inline-block">
                                        Off-Stage
                                    </span>
                                @endif
                                @if($p->scheduled_time)
                                    <span class="block text-[10px] font-mono text-slate-500 mt-1">
                                        {{ $p->scheduled_time->format('d M, h:i A') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-mono text-xs font-semibold px-2 py-0.5 bg-blue-50 text-blue-800 rounded border border-blue-200">
                                    {{ $p->eligibility ?? 'A Zone' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($p->status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Completed
                                    </span>
                                @elseif(in_array($p->status, ['in_progress', 'ongoing']))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase bg-blue-100 text-blue-800 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                        In Progress
                                    </span>
                                @elseif($p->status === 'cancelled')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase bg-rose-100 text-rose-800 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        Cancelled
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase bg-amber-100 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                        Upcoming
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-mono font-bold text-xs px-2 py-0.5 rounded bg-amber-50 text-amber-900 border border-amber-200">
                                    {{ $p->participant_count ?? 2 }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-mono font-medium text-slate-700">
                                {{ $p->entries_count }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <button type="button" 
                                        @click.prevent="toggleProgramRegistration('{{ $p->id }}', '{{ route('admin.programs.toggle-registration', $p) }}')"
                                        :disabled="regLoading['{{ $p->id }}']"
                                        :title="isProgRegOpen('{{ $p->id }}', {{ $isDirectOpen ? 'true' : 'false' }}) ? 'Click to Close registration for this competition' : 'Click to Open registration for this competition'"
                                        class="px-2.5 py-1 rounded-full text-[10px] font-bold font-mono inline-flex items-center gap-1 transition-all shadow-2xs cursor-pointer"
                                        :class="isProgRegOpen('{{ $p->id }}', {{ $isDirectOpen ? 'true' : 'false' }}) ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200 border border-emerald-300' : 'bg-rose-100 text-rose-800 hover:bg-rose-200 border border-rose-300'">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                          :class="isProgRegOpen('{{ $p->id }}', {{ $isDirectOpen ? 'true' : 'false' }}) ? 'bg-emerald-600' : 'bg-rose-600'"></span>
                                    <span x-text="isProgRegOpen('{{ $p->id }}', {{ $isDirectOpen ? 'true' : 'false' }}) ? 'OPEN' : 'CLOSED'">{{ $isDirectOpen ? 'OPEN' : 'CLOSED' }}</span>
                                </button>
                                @if(! $isEffectiveOpen)
                                    <span class="block text-[9px] text-amber-700 font-sans font-medium mt-0.5" title="{{ $p->getRegistrationClosureReason() }}">
                                        @if($p->is_stage && !($isStageRegOpen ?? true))
                                            (Stage Closed • Admin Active)
                                        @elseif(! $p->is_stage && !($isOffStageRegOpen ?? true))
                                            (Off-Stage Closed • Admin Active)
                                        @else
                                            (Closed • Admin Active)
                                        @endif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right relative" x-data="{ menuOpen: false }">
                                <button @click="menuOpen = !menuOpen" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                                    <span class="font-bold text-sm leading-none">···</span>
                                </button>
                                <div x-show="menuOpen" @click.away="menuOpen = false" class="absolute right-5 mt-1 w-48 bg-white border border-slate-200 rounded-lg shadow-lg py-1 z-20 text-left text-xs" style="display: none;">
                                    <a href="{{ route('admin.registrations.create', ['program_id' => $p->id]) }}" class="block px-3 py-1.5 text-[#be1e2d] hover:bg-amber-50 font-bold flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>+ Add Participants</span>
                                    </a>
                                    <a href="{{ route('admin.programs.show', $p) }}" class="block px-3 py-1.5 text-slate-700 hover:bg-slate-50">View Details</a>
                                    <a href="{{ route('admin.programs.edit', $p) }}" class="block px-3 py-1.5 text-slate-700 hover:bg-slate-50">Edit</a>
                                    <button type="button" 
                                            @click.prevent="toggleProgramRegistration('{{ $p->id }}', '{{ route('admin.programs.toggle-registration', $p) }}'); menuOpen = false"
                                            class="w-full text-left px-3 py-1.5 font-medium cursor-pointer"
                                            :class="isProgRegOpen('{{ $p->id }}', {{ $isDirectOpen ? 'true' : 'false' }}) ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50'">
                                        <span x-text="isProgRegOpen('{{ $p->id }}', {{ $isDirectOpen ? 'true' : 'false' }}) ? 'Close Registration' : 'Open Registration'">{{ $isDirectOpen ? 'Close Registration' : 'Open Registration' }}</span>
                                    </button>
                                    <form method="POST" action="{{ route('admin.programs.destroy', $p) }}" onsubmit="return confirm('Delete this competition?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-left px-3 py-1.5 text-red-600 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-5 py-12 text-center text-slate-400">
                                No competitions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($programs->hasPages())
            <div class="p-3 border-t border-slate-100">
                {{ $programs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
