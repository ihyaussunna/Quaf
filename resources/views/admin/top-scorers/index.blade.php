@extends('layouts.admin', ['title' => 'Top Scorers & Festival Awards | Quaf Fest Management'])

@section('content')
<div class="space-y-6">

    <!-- Header Section matching Sahityotsav / festfloww model -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-sora tracking-tight">Top Scorers</h1>
            <p class="text-xs text-slate-500 mt-1 font-mono">View event top performers and their total points (only includes published results)</p>
        </div>
        <div class="flex items-center gap-2.5">
            <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer print:hidden">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Table</span>
            </button>
            <a href="{{ route('admin.points.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white font-semibold text-xs hover:bg-slate-800 transition-all flex items-center gap-1.5 shadow-sm print:hidden">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span>Points Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Official QUAF Special Awards Spotlight (Rule 10) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 print:gap-3">

        <!-- 1. Vocal of the Fest: ചെറിയ എ പി ഉസ്താദ് കലാപ്രതിഭാ പുരസ്കാരം -->
        <div class="rounded-2xl bg-gradient-to-br from-red-50/80 via-white to-amber-50/60 border border-red-200/90 p-5 shadow-2xs relative overflow-hidden flex flex-col justify-between">
            <div class="absolute -right-4 -bottom-4 w-28 h-28 bg-[#be1e2d]/5 rounded-full pointer-events-none"></div>
            <div>
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#be1e2d] text-white uppercase tracking-wider flex items-center gap-1 shadow-2xs">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"></path></svg>
                        <span>VOCAL OF THE FEST</span>
                    </span>
                    <span class="text-[10px] font-mono font-bold text-slate-500 uppercase">Stage Top Scorer</span>
                </div>

                <div class="mt-1">
                    <h2 class="text-sm font-extrabold text-slate-900 font-sora">ചെറിയ എ പി ഉസ്താദ് കലാപ്രതിഭാ പുരസ്കാരം</h2>
                    <p class="text-[11px] text-slate-500 font-mono mt-0.5">സ്റ്റേജ് മത്സരങ്ങളിൽ കൂടുതൽ പോയിന്റ് നേടിയ പ്രതിഭ</p>
                </div>

                @if($vocalWinners->isNotEmpty())
                    <div class="mt-3.5 space-y-2">
                        @foreach($vocalWinners as $winner)
                            <div class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-white/90 border border-red-100 shadow-2xs">
                                <div class="min-w-0">
                                    <div class="font-sora font-black text-slate-900 text-sm truncate">{{ $winner->name }}</div>
                                    <div class="flex items-center gap-1.5 text-[10px] text-slate-500 font-mono">
                                        <span class="font-bold text-slate-700">#{{ $winner->student_id }}</span>
                                        <span>•</span>
                                        <span class="font-bold text-[#be1e2d]">{{ $winner->group?->name }}</span>
                                        <span>•</span>
                                        <span>{{ $winner->category }}</span>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <div class="font-sora font-black text-lg text-[#be1e2d] leading-none">{{ $winner->calc_stage_points }}</div>
                                    <div class="text-[9px] font-mono uppercase tracking-wider text-slate-400 font-bold">Stage Pts</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-100 text-center">
                        <p class="text-xs text-slate-400 font-mono">No stage points declared yet</p>
                    </div>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-red-100/80 flex items-center justify-between text-[10px] font-mono text-slate-500">
                <span>Rule 10 • Stage Top Performer</span>
                <span class="font-bold text-[#be1e2d]">{{ $maxStage }} Pts</span>
            </div>
        </div>

        <!-- 2. Pen of the Fest: നെല്ലിക്കുത്ത് ഉസ്താദ് സർഗ്ഗപ്രതിഭാ പുരസ്കാരം -->
        <div class="rounded-2xl bg-gradient-to-br from-slate-900/[0.04] via-white to-blue-50/50 border border-slate-200/90 p-5 shadow-2xs relative overflow-hidden flex flex-col justify-between">
            <div class="absolute -right-4 -bottom-4 w-28 h-28 bg-slate-900/5 rounded-full pointer-events-none"></div>
            <div>
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-900 text-white uppercase tracking-wider flex items-center gap-1 shadow-2xs">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        <span>PEN OF THE FEST</span>
                    </span>
                    <span class="text-[10px] font-mono font-bold text-slate-500 uppercase">Offstage Top Scorer</span>
                </div>

                <div class="mt-1">
                    <h2 class="text-sm font-extrabold text-slate-900 font-sora">നെല്ലിക്കുത്ത് ഉസ്താദ് സർഗ്ഗപ്രതിഭാ പുരസ്കാരം</h2>
                    <p class="text-[11px] text-slate-500 font-mono mt-0.5">സ്റ്റേജിതര മത്സരങ്ങളിൽ കൂടുതൽ പോയിന്റ് നേടിയ പ്രതിഭ</p>
                </div>

                @if($penWinners->isNotEmpty())
                    <div class="mt-3.5 space-y-2">
                        @foreach($penWinners as $winner)
                            <div class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-white/90 border border-slate-200 shadow-2xs">
                                <div class="min-w-0">
                                    <div class="font-sora font-black text-slate-900 text-sm truncate">{{ $winner->name }}</div>
                                    <div class="flex items-center gap-1.5 text-[10px] text-slate-500 font-mono">
                                        <span class="font-bold text-slate-700">#{{ $winner->student_id }}</span>
                                        <span>•</span>
                                        <span class="font-bold text-slate-900">{{ $winner->group?->name }}</span>
                                        <span>•</span>
                                        <span>{{ $winner->category }}</span>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <div class="font-sora font-black text-lg text-slate-900 leading-none">{{ $winner->calc_offstage_points }}</div>
                                    <div class="text-[9px] font-mono uppercase tracking-wider text-slate-400 font-bold">Offstage Pts</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-100 text-center">
                        <p class="text-xs text-slate-400 font-mono">No offstage points declared yet</p>
                    </div>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] font-mono text-slate-500">
                <span>Rule 10 • Offstage Top Performer</span>
                <span class="font-bold text-slate-900">{{ $maxOffstage }} Pts</span>
            </div>
        </div>

        <!-- 3. Zone-wise Top Scorers: കട്ടിപ്പാറ ഉസ്താദ് അവാർഡ് -->
        <div class="rounded-2xl bg-gradient-to-br from-amber-500/[0.06] via-white to-amber-50/40 border border-amber-300/80 p-5 shadow-2xs relative overflow-hidden flex flex-col justify-between">
            <div class="absolute -right-4 -bottom-4 w-28 h-28 bg-amber-400/10 rounded-full pointer-events-none"></div>
            <div>
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-500 text-slate-950 uppercase tracking-wider flex items-center gap-1 shadow-2xs">
                        <svg class="w-3 h-3 text-slate-950" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <span>ZONE CHAMPIONS</span>
                    </span>
                    <span class="text-[10px] font-mono font-bold text-amber-800 uppercase">Zone Top Scorers</span>
                </div>

                <div class="mt-1">
                    <h2 class="text-sm font-extrabold text-slate-900 font-sora">കട്ടിപ്പാറ ഉസ്താദ് അവാർഡ്</h2>
                    <p class="text-[11px] text-slate-500 font-mono mt-0.5">ഓരോ സോണുകളിലും കൂടുതൽ പോയിന്റ് നേടിയവർ</p>
                </div>

                @if(!empty($zoneToppers))
                    <div class="mt-3 space-y-2">
                        @foreach($zoneToppers as $zoneName => $info)
                            <div class="p-2.5 rounded-xl bg-white border border-amber-200/80 shadow-2xs">
                                <div class="flex items-center justify-between text-xs font-bold font-sora text-slate-800 pb-1 border-b border-slate-100">
                                    <span class="text-amber-800">{{ $zoneName }}</span>
                                    <span class="font-mono text-amber-600">{{ $info['max_points'] }} PTS</span>
                                </div>
                                <div class="mt-1.5 space-y-1">
                                    @foreach($info['winners'] as $w)
                                        <div class="flex items-center justify-between text-[11px] text-slate-700">
                                            <span class="font-semibold truncate">{{ $w->name }} (#{{ $w->student_id }})</span>
                                            <span class="text-[10px] font-mono text-slate-500 shrink-0 ml-2 font-bold">{{ $w->group?->name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-100 text-center">
                        <p class="text-xs text-slate-400 font-mono">No zone points declared yet</p>
                    </div>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-amber-100 flex items-center justify-between text-[10px] font-mono text-slate-500">
                <span>Rule 10 • Zone Top Scorers</span>
                <span class="font-bold text-amber-800">A, B, C Zones</span>
            </div>
        </div>

    </div>

    <!-- Filter Bar Matching Sahityotsav Model (Image 1) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-2xs print:hidden">
        <form method="GET" action="{{ route('admin.top-scorers.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            <!-- Search participants input -->
            <div class="sm:col-span-5 relative">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search participants..."
                       class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#be1e2d] focus:bg-white text-slate-900 font-medium transition-all">
            </div>

            <!-- Categories / Zones Filter -->
            <div class="sm:col-span-3">
                <select name="category" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#be1e2d] focus:bg-white text-slate-900 font-medium transition-all">
                    <option value="all" {{ $selectedCategory === 'all' ? 'selected' : '' }}>All Categories</option>
                    @foreach($allCategories as $cat)
                        <option value="{{ $cat->name }}" {{ $selectedCategory === $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Teams Filter -->
            <div class="sm:col-span-2">
                <select name="team" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#be1e2d] focus:bg-white text-slate-900 font-medium transition-all">
                    <option value="all" {{ $selectedTeam === 'all' ? 'selected' : '' }}>All Teams</option>
                    @foreach($allTeams as $t)
                        <option value="{{ $t->id }}" {{ (string)$selectedTeam === (string)$t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Awards / Special Filter -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <select name="award" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#be1e2d] focus:bg-white text-slate-900 font-medium transition-all">
                    <option value="all" {{ $awardFilter === 'all' ? 'selected' : '' }}>All Performers</option>
                    <option value="any_award" {{ $awardFilter === 'any_award' ? 'selected' : '' }}>Award Winners Only</option>
                    <option value="vocal" {{ $awardFilter === 'vocal' ? 'selected' : '' }}>Vocal of the Fest</option>
                    <option value="pen" {{ $awardFilter === 'pen' ? 'selected' : '' }}>Pen of the Fest</option>
                    <option value="zone" {{ $awardFilter === 'zone' ? 'selected' : '' }}>Zone Toppers</option>
                </select>
                @if($search !== '' || $selectedCategory !== 'all' || $selectedTeam !== 'all' || $awardFilter !== 'all')
                    <a href="{{ route('admin.top-scorers.index') }}" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Clear Filters">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Main Top Scorers Table Matching Sahityotsav Layout (Image 1) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-mono uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4 font-bold">#</th>
                        <th class="py-3.5 px-4 font-bold">Participant</th>
                        <th class="py-3.5 px-4 font-bold">Gender</th>
                        <th class="py-3.5 px-4 font-bold">Team</th>
                        <th class="py-3.5 px-4 font-bold">Category</th>
                        <th class="py-3.5 px-4 font-bold text-center">Offstage</th>
                        <th class="py-3.5 px-4 font-bold text-center">Stage</th>
                        <th class="py-3.5 px-4 font-bold text-right">Total Points</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($topScorers as $student)
                        @php
                            $rank = ($topScorers->currentPage() - 1) * $topScorers->perPage() + $loop->iteration;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Rank Number -->
                            <td class="py-3 px-4 font-mono font-bold text-slate-400">
                                {{ sprintf('%02d', $rank) }}
                            </td>

                            <!-- Participant Details with Award Badges (Matching Image 1) -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-sora font-extrabold text-slate-900 text-sm">
                                        {{ $student->name }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        #{{ $student->student_id }}
                                    </span>

                                    <!-- Award Badges (Vocal of the Fest / Pen of the Fest / Zone Topper) -->
                                    @if(!empty($student->award_badges))
                                        @foreach($student->award_badges as $b)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold {{ $b['bg_class'] }} shadow-2xs"
                                                  title="{{ $b['award_name'] }}">
                                                @if($b['type'] === 'vocal')
                                                    <svg class="w-2.5 h-2.5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"></path></svg>
                                                @elseif($b['type'] === 'pen')
                                                    <svg class="w-2.5 h-2.5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                @else
                                                    <svg class="w-2.5 h-2.5 text-current" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                @endif
                                                <span>{{ $b['label'] }}</span>
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                            </td>

                            <!-- Gender -->
                            <td class="py-3 px-4 font-mono text-slate-500">
                                {{ $student->gender ?? 'Male' }}
                            </td>

                            <!-- Team -->
                            <td class="py-3 px-4 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $student->group?->color ?? '#be1e2d' }}"></span>
                                    <span class="font-bold text-slate-800">{{ $student->group?->name ?? 'Independent' }}</span>
                                </div>
                            </td>

                            <!-- Category / Zone -->
                            <td class="py-3 px-4 font-mono font-medium text-slate-600">
                                {{ $student->category ?? ($student->zone?->name ?? 'Open') }}
                            </td>

                            <!-- Offstage Points -->
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">
                                {{ $student->calc_offstage_points }}
                            </td>

                            <!-- Stage Points -->
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">
                                {{ $student->calc_stage_points }}
                            </td>

                            <!-- Total Points -->
                            <td class="py-3 px-4 text-right">
                                <span class="font-sora font-black text-base text-[#be1e2d] leading-none">
                                    {{ $student->calc_total_points }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <h4 class="font-sora font-bold text-slate-700 text-sm">No Top Scorers Found</h4>
                                    <p class="text-xs text-slate-400 max-w-sm">No individual student points match your current search and filter criteria.</p>
                                    <a href="{{ route('admin.top-scorers.index') }}" class="mt-2 text-xs font-bold text-[#be1e2d] hover:underline">Reset All Filters</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($topScorers->hasPages())
            <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-mono print:hidden">
                <div>
                    Showing <span class="font-bold text-slate-700">{{ $topScorers->firstItem() ?? 0 }}</span> to <span class="font-bold text-slate-700">{{ $topScorers->lastItem() ?? 0 }}</span> of <span class="font-bold text-slate-700">{{ $topScorers->total() }}</span> performers
                </div>
                <div>
                    {{ $topScorers->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Official Rule 10 Reference Card -->
    <div class="rounded-2xl p-4 bg-slate-50 border border-slate-200/80 text-xs text-slate-600 font-mono flex items-start gap-3 print:hidden">
        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div>
            <span class="font-bold text-slate-800">QUAF ചട്ടം 10 (അവാർഡുകൾ):</span>
            ക്വാഫിൽ സ്റ്റേജ് മത്സരങ്ങളിൽ കൂടുതൽ പോയിന്റ് നേടിയ മത്സരാർത്ഥി <span class="font-bold text-[#be1e2d]">ചെറിയ എ പി ഉസ്താദ് കലാപ്രതിഭാ പുരസ്കാരത്തിനും</span> (Vocal of the Fest), സ്റ്റേജിതര മത്സരങ്ങളിൽ കൂടുതൽ പോയിന്റ് നേടിയ മത്സരാർത്ഥി <span class="font-bold text-slate-900">നെല്ലിക്കുത്ത് ഉസ്താദ് സർഗ്ഗപ്രതിഭാ പുരസ്കാരത്തിനും</span> (Pen of the Fest), ഓരോ സോണുകളിലും കൂടുതൽ പോയിന്റ് നേടുന്നവർക്ക് <span class="font-bold text-amber-700">കട്ടിപ്പാറ ഉസ്താദ് അവാർഡിനും</span> (Zone Top Scorer) അർഹത നേടുന്നു. ഗ്രൂപ്പ് ഇനങ്ങളിലെ പ്രകടനം വ്യക്തിഗത പോയിന്റ് ടേബിളിൽ പരിഗണിക്കുന്നതല്ല.
        </div>
    </div>

</div>

<style>
@media print {
    body {
        background: white !important;
        color: black !important;
    }
    aside, nav, header, .print\:hidden {
        display: none !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
    }
}
</style>
@endsection
