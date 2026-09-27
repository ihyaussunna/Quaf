@extends('layouts.admin')

@section('title', 'Panel Access & Credentials Hub | QUAF 09')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="{ 
    editModalOpen: false, 
    currentUser: { id: null, name: '', email: '', role: '' },
    copiedId: null,
    copyText(text, id) {
        navigator.clipboard.writeText(text).then(() => {
            this.copiedId = id;
            setTimeout(() => { this.copiedId = null; }, 2000);
        });
    }
}">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-900 text-white">
                    Access Control & Security
                </span>
                <span class="text-xs text-slate-500 font-mono">Security & Access Hub</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-2 font-sans">
                Panel Access & Credentials Hub
            </h1>
            <p class="text-sm text-slate-600 mt-1 max-w-2xl font-sans">
                View, copy credentials, and manage access security for all system panels in one place.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors">
                Back to Dashboard
            </a>
            <a href="{{ route('media.dashboard') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs transition-colors shadow-md shadow-[#be1e2d]/20">
                Go to Media Panel
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Metric Stat Badges -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Accounts</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-emerald-200 bg-emerald-50/20 shadow-2xs">
            <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Active</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['active'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-rose-200 bg-rose-50/20 shadow-2xs">
            <div class="text-xs font-semibold text-rose-700 uppercase tracking-wider">Locked</div>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ $stats['locked'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Group Leaders</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['leaders'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Judges</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['judges'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Media Wing</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['media'] }}</div>
        </div>
    </div>

    <!-- Dedicated Group Leaders (5 Groups) Access Hub -->
    @if(isset($groups) && $groups->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">Group Leaders Access Credentials (5 Groups)</h2>
                    <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-mono font-bold">Official Portals</span>
                </div>
                <div class="text-xs font-mono text-slate-500">
                    Dedicated credentials to access respective Group Leader portals.
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach($groups as $grp)
                    @php
                        $leaderUser = $grp->leader;
                        $username = $grp->admin_username ?: ($leaderUser?->email ?? 'leader.' . strtolower($grp->code ?? 'group') . '@quaf.fest');
                        $password = $grp->admin_password ?: ($leaderUser?->plain_password ?? 'Not Configured');
                        $colorHex = $grp->color_hex ?: '#be1e2d';
                    @endphp
                    <div class="rounded-xl border border-slate-200/90 bg-slate-50/40 p-4 flex flex-col justify-between hover:border-slate-300 transition-all shadow-2xs hover:shadow-xs"
                         x-data="{ showCardPass: false }">
                        <div>
                            <!-- Group Header -->
                            <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-200/80">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $colorHex }};"></span>
                                    <span class="font-black text-xs text-slate-900 uppercase tracking-tight">{{ $grp->name }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase text-white shadow-2xs" style="background-color: {{ $colorHex }};">
                                    {{ $grp->code }}
                                </span>
                            </div>

                            <!-- Leader Name -->
                            <div class="mt-3">
                                <div class="text-[10px] uppercase font-mono font-bold text-slate-400">Team Leader</div>
                                <div class="text-xs font-bold text-slate-800 line-clamp-1 mt-0.5" title="{{ $leaderUser?->name ?? 'Team Leader' }}">
                                    {{ $leaderUser?->name ?? 'Team Leader' }}
                                </div>
                            </div>

                            <!-- Username -->
                            <div class="mt-3">
                                <div class="text-[10px] uppercase font-mono font-bold text-slate-400">Username</div>
                                <div class="flex items-center justify-between gap-1.5 mt-1 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5">
                                    <span class="font-mono text-[11px] text-slate-700 truncate select-all">{{ $username }}</span>
                                    <button type="button" 
                                            @click="copyText('{{ $username }}', 'g_u_{{ $grp->id }}')" 
                                            class="p-1 rounded hover:bg-slate-100 text-slate-400 hover:text-slate-800 shrink-0" 
                                            title="Copy Username">
                                        <template x-if="copiedId === 'g_u_{{ $grp->id }}'">
                                            <span class="text-[10px] font-bold text-emerald-600">✓</span>
                                        </template>
                                        <template x-if="copiedId !== 'g_u_{{ $grp->id }}'">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        </template>
                                    </button>
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="mt-2.5">
                                <div class="text-[10px] uppercase font-mono font-bold text-slate-400">Password</div>
                                <div class="flex items-center justify-between gap-1 mt-1 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5">
                                    <span class="font-mono text-[11px] text-slate-900 font-semibold truncate select-all">
                                        <span x-show="showCardPass">{{ $password }}</span>
                                        <span x-show="!showCardPass" class="tracking-widest">••••••••</span>
                                    </span>
                                    <div class="flex items-center shrink-0">
                                        <button type="button" @click="showCardPass = !showCardPass" class="p-1 text-slate-400 hover:text-slate-700" title="Show/Hide">
                                            <svg x-show="!showCardPass" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            <svg x-show="showCardPass" class="w-3.5 h-3.5 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                        </button>
                                        <button type="button" 
                                                @click="copyText('{{ $password }}', 'g_p_{{ $grp->id }}')" 
                                                class="p-1 rounded hover:bg-slate-100 text-slate-400 hover:text-slate-800" 
                                                title="Copy Password">
                                            <template x-if="copiedId === 'g_p_{{ $grp->id }}'">
                                                <span class="text-[10px] font-bold text-emerald-600">✓</span>
                                            </template>
                                            <template x-if="copiedId !== 'g_p_{{ $grp->id }}'">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            </template>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Action Link -->
                        <div class="mt-4 pt-3 border-t border-slate-200/70 flex items-center justify-between text-[11px]">
                            <a href="{{ route('admin.groups.show', $grp) }}" class="font-bold text-slate-600 hover:text-[#be1e2d] transition-colors">
                                View Group →
                            </a>
                            <a href="{{ route('login') }}" target="_blank" class="font-mono text-slate-400 hover:text-slate-700">
                                Portal Login ↗
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            @php $currentPanel = request('panel', ''); @endphp
            <a href="{{ route('admin.panel-access.index') }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ empty($currentPanel) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                All Panels
            </a>
            <a href="{{ route('admin.panel-access.index', ['panel' => 'admin']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $currentPanel === 'admin' ? 'bg-[#be1e2d] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Admin
            </a>
            <a href="{{ route('admin.panel-access.index', ['panel' => 'samithi']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $currentPanel === 'samithi' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Program Samithi
            </a>
            <a href="{{ route('admin.panel-access.index', ['panel' => 'leader']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $currentPanel === 'leader' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Team Leaders (5 Groups)
            </a>
            <a href="{{ route('admin.panel-access.index', ['panel' => 'announcer']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $currentPanel === 'announcer' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Announcer Desk
            </a>
            <a href="{{ route('admin.panel-access.index', ['panel' => 'media']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $currentPanel === 'media' ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Media Wing
            </a>
            <a href="{{ route('admin.panel-access.index', ['panel' => 'judge']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $currentPanel === 'judge' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Judges
            </a>
            <a href="{{ route('admin.panel-access.index', ['panel' => 'greenroom']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $currentPanel === 'greenroom' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Green Room
            </a>
        </div>

        <form method="GET" action="{{ route('admin.panel-access.index') }}" class="flex items-center gap-2">
            @if(request('panel'))
                <input type="hidden" name="panel" value="{{ request('panel') }}">
            @endif
            <div class="relative w-full md:w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search panel, name, email..."
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <button type="submit" class="px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-colors shrink-0">
                Filter
            </button>
            @if(request('search') || request('panel') || request('status'))
                <a href="{{ route('admin.panel-access.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 text-xs font-medium rounded-xl hover:bg-slate-100 transition-colors">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Credentials Table / Cards -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4">Panel / Role</th>
                        <th class="py-3.5 px-4">Account Holder / Name</th>
                        <th class="py-3.5 px-4">Username / Login</th>
                        <th class="py-3.5 px-4">Password</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        @php
                            $roleBadgeColor = match($user->role) {
                                'super_admin', 'admin' => 'bg-red-50 text-red-700 border-red-200',
                                'program_committee', 'program_coordinator' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'announcer' => 'bg-amber-50 text-amber-900 border-amber-300',
                                'media_team', 'media_manager' => 'bg-purple-50 text-purple-700 border-purple-200',
                                'group_leader' => 'bg-amber-50 text-amber-800 border-amber-200',
                                'judge' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'green_room_coordinator' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                default => 'bg-slate-50 text-slate-700 border-slate-200'
                            };

                            $roleLabel = match($user->role) {
                                'super_admin' => 'Central Admin',
                                'admin' => 'Admin Panel',
                                'program_committee', 'program_coordinator' => 'Program Samithi',
                                'announcer' => 'Announcer Desk',
                                'media_team', 'media_manager' => 'Media Wing',
                                'group_leader' => 'Team Leader (' . ($user->ledGroup->name ?? 'Group') . ')',
                                'judge' => 'Judges Panel',
                                'green_room_coordinator' => 'Green Room Coordinator',
                                'student' => 'Student Portal',
                                default => ucfirst($user->role)
                            };

                            $quickLoginAlias = match($user->role) {
                                'program_committee', 'program_coordinator' => 'samithi',
                                'announcer' => 'announcer',
                                'media_team', 'media_manager' => 'media',
                                'group_leader' => strtolower($user->ledGroup->code ?? 'leader'),
                                default => null
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors {{ ! $user->is_active ? 'bg-rose-50/30' : '' }}" 
                            x-data="{ showPass: false }">
                            
                            <!-- Panel & Role -->
                            <td class="py-4 px-4 align-middle">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-bold {{ $roleBadgeColor }}">
                                    <span>{{ $roleLabel }}</span>
                                </div>
                                @if($quickLoginAlias)
                                    <div class="text-[10px] text-slate-400 font-mono mt-1">
                                        Alias: <span class="text-slate-600 font-semibold">{{ $quickLoginAlias }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Name & Details -->
                            <td class="py-4 px-4 align-middle">
                                <div class="font-bold text-slate-900 text-xs">{{ $user->name }}</div>
                                <div class="text-slate-500 text-[11px] font-mono mt-0.5">{{ $user->phone ?? 'No phone' }}</div>
                            </td>

                            <!-- Username / Email -->
                            <td class="py-4 px-4 align-middle">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs text-slate-800 font-semibold">{{ $user->email }}</span>
                                    <button type="button" 
                                            @click="copyText('{{ $user->email }}', 'u_{{ $user->id }}')" 
                                            class="p-1 rounded hover:bg-slate-200 text-slate-500 hover:text-slate-900 transition-colors shrink-0" 
                                            title="Copy Username">
                                        <template x-if="copiedId === 'u_{{ $user->id }}'">
                                            <span class="text-[10px] font-bold text-emerald-600 uppercase">Copied!</span>
                                        </template>
                                        <template x-if="copiedId !== 'u_{{ $user->id }}'">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        </template>
                                    </button>
                                </div>
                            </td>

                            <!-- Password with Eye Toggle & 1-Click Copy -->
                            <td class="py-4 px-4 align-middle">
                                @php $pass = $user->plain_password ?? $user->ledGroup?->admin_password ?? 'Not Available'; @endphp
                                <div class="flex items-center gap-2">
                                    <div class="font-mono text-xs px-2.5 py-1 rounded bg-slate-100 border border-slate-200 select-all">
                                        <span x-show="showPass">{{ $pass }}</span>
                                        <span x-show="!showPass" class="tracking-widest font-bold">••••••••••••</span>
                                    </div>
                                    
                                    <!-- Toggle Eye -->
                                    <button type="button" @click="showPass = !showPass" class="p-1 text-slate-400 hover:text-slate-700 transition-colors" title="Show/Hide Password">
                                        <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <svg x-show="showPass" class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                    </button>

                                    <!-- Copy Password -->
                                    <button type="button" 
                                            @click="copyText('{{ $pass }}', 'p_{{ $user->id }}')" 
                                            class="p-1 rounded hover:bg-slate-200 text-slate-500 hover:text-slate-900 transition-colors shrink-0" 
                                            title="Copy Password">
                                        <template x-if="copiedId === 'p_{{ $user->id }}'">
                                            <span class="text-[10px] font-bold text-emerald-600 uppercase">Copied!</span>
                                        </template>
                                        <template x-if="copiedId !== 'p_{{ $user->id }}'">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        </template>
                                    </button>
                                </div>

                                @if($user->role === 'judge' && $user->judge)
                                    <div class="mt-1.5 flex items-center gap-1.5 text-[11px]">
                                        <span class="text-amber-800 font-bold bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md font-mono tracking-wider">
                                            PIN: {{ $user->judge->access_code }}
                                        </span>
                                        <button type="button" 
                                                @click="copyText('{{ $user->judge->access_code }}', 'pin_{{ $user->id }}')" 
                                                class="p-1 rounded hover:bg-slate-200 text-slate-500 hover:text-slate-900 transition-colors shrink-0" 
                                                title="Copy Judge Access PIN">
                                            <template x-if="copiedId === 'pin_{{ $user->id }}'">
                                                <span class="text-[10px] font-bold text-emerald-600 uppercase">Copied!</span>
                                            </template>
                                            <template x-if="copiedId !== 'pin_{{ $user->id }}'">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            </template>
                                        </button>
                                    </div>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-4 align-middle">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Locked
                                    </span>
                                @endif
                            </td>

                            <!-- Actions (Lock/Unlock & Password Change) -->
                            <td class="py-4 px-4 align-middle text-right">
                                <div class="inline-flex items-center gap-2">
                                    <!-- Change Password Button -->
                                    <button type="button" 
                                            @click="currentUser = { id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', email: '{{ $user->email }}', role: '{{ $user->role }}' }; editModalOpen = true;"
                                            class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold text-[11px] transition-colors">
                                        Edit Pass
                                    </button>

                                    <!-- Block / Unlock Form -->
                                    @if($user->id !== Auth::id())
                                        <form method="POST" action="{{ route('admin.panel-access.toggle-status', $user) }}" class="inline" onsubmit="return confirm('{{ $user->is_active ? 'Are you sure you want to lock this account? They will not be able to log in.' : 'Are you sure you want to reactivate this account?' }}')">
                                            @csrf
                                            @if($user->is_active)
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 font-bold text-[11px] transition-colors">
                                                    Lock Account
                                                </button>
                                            @else
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition-colors">
                                                    Unlock
                                                </button>
                                            @endif
                                        </form>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Current User</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                No records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div x-show="editModalOpen" 
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" 
         style="display: none;">
        
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.away="editModalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-base">Change Panel Password</h3>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <p class="text-xs text-slate-600">
                Update login password for <span class="font-bold text-slate-900" x-text="currentUser.name"></span> (<span class="font-mono text-slate-500" x-text="currentUser.email"></span>).
            </p>

            <form method="POST" :action="'/admin/panel-access/' + currentUser.id + '/update-password'" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">New Password</label>
                    <input type="text" name="password" required minlength="6" placeholder="Enter new strong password"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-sm focus:outline-none focus:border-[#be1e2d]">
                    <p class="text-[11px] text-slate-400 mt-1">Minimum 6 characters. Will update both login credentials and leader sync.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs shadow-md">
                        Save Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
