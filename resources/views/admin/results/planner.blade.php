@extends('layouts.admin')

@section('title', 'Results Strategy & Queue Planner - QUAF Fest')

@section('content')
<div class="space-y-6" x-data="announcementPlanner()">
    <!-- Header Banner -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-950 text-purple-200 border border-purple-800">
                    Central Committee Strategy Room
                </span>
                <span class="text-xs text-slate-500 font-mono">Standings Impact & Announcement Queue</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1.5 font-sora">
                ഫലപ്രഖ്യാപന സ്ട്രാറ്റജി പ്ലാനർ & ടീം ലീഡ് സിമുലേറ്റർ
            </h1>
            <p class="text-xs text-slate-600 mt-1 max-w-3xl leading-relaxed">
                ഓരോ അനൗൺസ്‌മെന്റിലും ടീമുകളുടെ പോയിന്റ് നിലയിലെ മാറ്റങ്ങൾ നേരത്തെ കണ്ട്, ഏത് ടീമിനെ ഒന്നാം സ്ഥാനത്ത് എത്തിക്കണമെന്ന് മുൻകൂട്ടി പ്ലാൻ ചെയ്ത് ഫലങ്ങൾ സ്റ്റേജിലേക്കും മീഡിയയിലേക്കും ക്രമീകരിക്കാനുള്ള അഡ്മിൻ കൺട്രോൾ ടൂൾ.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <a href="{{ route('admin.results.declared') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Declared Results</span>
            </a>
            <button type="button" @click="selectAllReady()" class="px-4 py-2.5 rounded-xl bg-purple-50 text-purple-900 border border-purple-200 hover:bg-purple-100 font-bold text-xs transition">
                Select All Ready Results
            </button>
            <button type="button" @click="clearSelection()" x-show="selectedResultIds.length > 0" class="px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 font-bold text-xs transition">
                Clear Selection (<span x-text="selectedResultIds.length"></span>)
            </button>
        </div>
    </div>

    <!-- Festival Progress Metrics Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- 1. Total Programs -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">ആകെ മത്സരങ്ങൾ</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 font-mono mt-1">{{ $stats['total_programs'] }}</div>
            <div class="text-[10px] text-slate-400 mt-1 font-mono">100% Festival Slate</div>
        </div>

        <!-- 2. Published Results -->
        <div class="bg-emerald-50/60 p-4 sm:p-5 rounded-2xl border border-emerald-200/80 shadow-2xs">
            <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider flex items-center justify-between">
                <span>പ്രസിദ്ധീകരിച്ച ഫലങ്ങൾ</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-950 font-mono mt-1">{{ $stats['published_count'] }}</div>
            <div class="text-[10px] text-emerald-700 mt-1 font-mono">പോസ്റ്ററുകൾ പബ്ലിഷായവ</div>
        </div>

        <!-- 3. Ready Awaiting Announcement / Stage Queue -->
        <div class="bg-amber-50/80 p-4 sm:p-5 rounded-2xl border border-amber-300 shadow-2xs ring-2 ring-amber-400/30">
            <div class="text-[11px] font-bold text-amber-900 uppercase tracking-wider flex items-center justify-between">
                <span>ക്യൂവിലുള്ള ഫലങ്ങൾ</span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-amber-950 font-mono mt-1">{{ $stats['ready_count'] }}</div>
            <div class="text-[10px] text-amber-800 font-medium mt-1">അനൗൺസ് ചെയ്യാൻ തയ്യാറുള്ളവ</div>
        </div>

        <!-- 4. Remaining Competitions -->
        <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">ബാക്കിയുള്ള മത്സരങ്ങൾ</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-700 font-mono mt-1">{{ $stats['remaining_count'] }}</div>
            <div class="text-[10px] text-slate-400 mt-1 font-mono">ഫലം വരാനിരിക്കുന്നവ</div>
        </div>
    </div>

    <!-- Team Strategy Cards: See points in queue for each team -->
    <div class="space-y-2">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-800">
                ടീം പോയിന്റ് നില & ക്യൂവിലുള്ള ആധിപത്യം (Team Potential & Queue Analysis)
            </h2>
            <span class="text-xs text-slate-500">ടീമിൽ ക്ലിക്ക് ചെയ്താൽ ആ ടീം മുന്നിലെത്തിയ ഫലങ്ങൾ മാത്രം ഫിൽട്ടർ ചെയ്യാം</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach($groups as $grp)
                @php
                    $strat = $groupStrategyStats[$grp->id] ?? ['ready_wins' => 0, 'pending_points' => 0];
                @endphp
                <div @click="filterTeamId = (filterTeamId === {{ $grp->id }} ? null : {{ $grp->id }})"
                     class="p-4 rounded-2xl border transition-all cursor-pointer relative overflow-hidden"
                     :class="filterTeamId === {{ $grp->id }} ? 'bg-purple-900 text-white ring-2 ring-purple-500 border-purple-500 shadow-md' : 'bg-white hover:border-slate-400 border-slate-200 shadow-2xs text-slate-800'">
                    
                    <div class="flex items-center justify-between mb-2">
                        <span class="w-3.5 h-3.5 rounded-full" style="background-color: {{ $grp->color_hex ?? '#be1e2d' }}"></span>
                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider opacity-80">{{ $grp->code }}</span>
                    </div>

                    <div class="text-xs font-bold truncate leading-tight">{{ $grp->name }}</div>
                    
                    <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-baseline justify-between">
                        <div>
                            <div class="text-[9px] uppercase font-bold opacity-70">Current</div>
                            <div class="text-base font-black font-mono">{{ number_format($grp->points_cache) }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-[9px] uppercase font-bold text-amber-500">In Queue</div>
                            <div class="text-base font-black font-mono text-amber-500">+{{ $strat['pending_points'] }}</div>
                        </div>
                    </div>

                    @if($strat['ready_wins'] > 0)
                        <div class="mt-2 text-[10px] font-bold px-2 py-0.5 rounded bg-amber-400/20 text-amber-600 text-center font-mono">
                            {{ $strat['ready_wins'] }} Wins in Queue
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Main Workspace: Simulator (Left) & Results Queue (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Live Standings Simulator Column (5 cols) -->
        <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-4">
            
            <div class="bg-slate-900 text-white p-5 rounded-2xl border border-slate-800 shadow-lg space-y-4">
                <div class="flex items-center justify-between border-b border-white/10 pb-3">
                    <div>
                        <div class="text-xs font-mono font-bold text-amber-400 uppercase tracking-wider">Live Standings Simulator</div>
                        <h3 class="text-sm font-black text-white mt-0.5">അടുത്ത അനൗൺസ്‌മെന്റിലെ പ്രൊജക്ഷൻ</h3>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-white/10 text-slate-300">
                        <span x-text="selectedResultIds.length"></span> Selected
                    </span>
                </div>

                <!-- Simulation Status Banner -->
                <div x-show="selectedResultIds.length === 0" class="p-3.5 rounded-xl bg-white/5 border border-white/10 text-xs text-slate-300">
                    ലിസ്റ്റിൽ നിന്ന് റിസൾട്ടുകൾ സെലക്ട് ചെയ്യുക. അവ അനൗൺസ് ചെയ്യുകയോ പബ്ലിഷ് ചെയ്യുകയോ ചെയ്താൽ ഏത് ടീം ഒന്നാമതെത്തും എന്ന് താഴെ തത്സമയം കാണാം.
                </div>

                <div x-show="selectedResultIds.length > 0" class="p-3.5 rounded-xl bg-purple-950/80 border border-purple-500/50 text-xs text-purple-200">
                    <div class="font-bold flex items-center gap-1.5 text-white">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <span x-text="projectedWinnerBanner"></span>
                    </div>
                </div>

                <!-- Projected Leaderboard Table -->
                <div class="space-y-2">
                    <template x-for="(team, idx) in projectedLeaderboard" :key="team.id">
                        <div class="p-3 rounded-xl border transition-all flex items-center justify-between"
                             :class="idx === 0 ? 'bg-amber-400/15 border-amber-400 text-white' : 'bg-white/5 border-white/5 text-slate-200'">
                            
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-lg flex items-center justify-center font-black font-mono text-xs"
                                      :class="idx === 0 ? 'bg-amber-400 text-slate-950 shadow-sm' : 'bg-white/10 text-white'">
                                    #<span x-text="idx + 1"></span>
                                </span>
                                <div>
                                    <div class="text-xs font-bold flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: team.color }"></span>
                                        <span x-text="team.name"></span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        Current: <span x-text="team.current_points"></span> pts
                                        <span x-show="team.added_points > 0" class="text-emerald-400 font-bold ml-1">
                                            (+<span x-text="team.added_points"></span> pts from selected)
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="text-base font-black font-mono" :class="idx === 0 ? 'text-amber-300' : 'text-white'" x-text="team.projected_points"></div>
                                <div class="text-[9px] uppercase font-mono text-slate-400">Total Pts</div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Batch Action Buttons -->
                <div class="pt-3 border-t border-white/10 space-y-2.5">
                    <!-- Action 1: Send to Announcer Desk -->
                    <form method="POST" action="{{ route('admin.results.batch-send-announcer') }}">
                        @csrf
                        <template x-for="rId in selectedResultIds" :key="'snd_' + rId">
                            <input type="hidden" name="result_ids[]" :value="rId">
                        </template>

                        <button type="submit" :disabled="selectedResultIds.length === 0"
                                class="w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 disabled:opacity-40 text-white font-black text-xs uppercase tracking-wider transition-all shadow-md font-mono flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                            <span>സ്റ്റേജ് അനൗൺസർക്ക് അയക്കുക (<span x-text="selectedResultIds.length"></span>)</span>
                        </button>
                    </form>

                    <!-- Action 2: Batch Publish to Website -->
                    <form method="POST" action="{{ route('admin.results.batch-publish') }}" class="space-y-2">
                        @csrf
                        <template x-for="rId in selectedResultIds" :key="'pub_' + rId">
                            <input type="hidden" name="result_ids[]" :value="rId">
                        </template>

                        <div class="flex items-center gap-2 pt-1">
                            <input type="checkbox" id="update_standings_check" name="update_standings" value="1" checked class="rounded border-slate-700 text-emerald-500 focus:ring-0">
                            <label for="update_standings_check" class="text-xs text-slate-300 font-medium">ഫലങ്ങൾക്കൊപ്പം പുതിയ ടീം സ്റ്റാൻഡിങ്സും പബ്ലിക് ആക്കുക</label>
                        </div>

                        <input type="text" name="standings_label" :value="suggestedMilestoneLabel"
                               class="w-full bg-white/10 border border-white/20 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-400 font-mono focus:outline-none focus:border-emerald-400">

                        <button type="submit" :disabled="selectedResultIds.length === 0"
                                class="w-full py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 disabled:opacity-40 text-slate-950 font-black text-xs uppercase tracking-wider transition-all shadow-md font-mono flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>പബ്ലിഷ് ചെയ്യുക & പോയിന്റ് നില അപ്‌ഡേറ്റ് ചെയ്യുക (<span x-text="selectedResultIds.length"></span>)</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Results Queue List (7 cols) -->
        <div class="lg:col-span-7 space-y-4">
            
            <!-- Filters Toolbar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 flex flex-wrap items-center justify-between gap-3 shadow-2xs">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="filterStatus = 'all'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
                            :class="filterStatus === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        All (<span x-text="results.length"></span>)
                    </button>
                    <button type="button" @click="filterStatus = 'ready'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
                            :class="filterStatus === 'ready' ? 'bg-amber-500 text-slate-950' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        Unpublished Queue (<span x-text="results.filter(r => r.status !== 'published').length"></span>)
                    </button>
                    <button type="button" @click="filterStatus = 'published'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
                            :class="filterStatus === 'published' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        Published (<span x-text="results.filter(r => r.status === 'published').length"></span>)
                    </button>
                </div>

                <div x-show="filterTeamId !== null" class="flex items-center gap-1.5 text-xs text-purple-700 bg-purple-50 px-2.5 py-1 rounded-xl border border-purple-200">
                    <span>Filtering by team</span>
                    <button type="button" @click="filterTeamId = null" class="font-bold hover:text-purple-950">&times;</button>
                </div>
            </div>

            <!-- Result Cards -->
            <div class="space-y-3">
                <template x-for="res in filteredResults" :key="res.id">
                    <div class="bg-white rounded-2xl border transition-all p-5 shadow-2xs"
                         :class="selectedResultIds.includes(res.id) ? 'border-purple-400 ring-2 ring-purple-400/30 bg-purple-50/20' : 'border-slate-200 hover:border-slate-300'">
                        
                        <div class="flex items-start justify-between gap-4">
                            <!-- Checkbox & Program Code -->
                            <div class="flex items-start gap-3">
                                <input type="checkbox" :id="'res_check_' + res.id" :value="res.id" x-model="selectedResultIds"
                                       class="mt-1 rounded border-slate-300 text-purple-600 focus:ring-purple-400 w-4 h-4 cursor-pointer">
                                
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-slate-900 text-white" x-text="res.program_code"></span>
                                        <span class="text-[10px] font-bold text-slate-500 uppercase font-mono" x-text="res.category_name"></span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                                              :class="{
                                                  'bg-emerald-100 text-emerald-800 border border-emerald-200': res.status === 'published',
                                                  'bg-purple-100 text-purple-800 border border-purple-200': res.status === 'announced',
                                                  'bg-blue-100 text-blue-800 border border-blue-200': res.status === 'send',
                                                  'bg-amber-100 text-amber-900 border border-amber-300': res.status !== 'published' && res.status !== 'send' && res.status !== 'announced'
                                              }">
                                            <span x-text="res.status_label"></span>
                                        </span>
                                    </div>
                                    <h4 class="text-base font-bold text-slate-900 mt-1 leading-snug" x-text="res.program_name"></h4>
                                </div>
                            </div>

                            <!-- Top Beneficiary Badge -->
                            <div x-show="res.top_group" class="text-right shrink-0">
                                <div class="text-[9px] uppercase font-bold text-slate-400">Favors Team</div>
                                <div class="px-2.5 py-1 rounded-xl text-xs font-bold text-white flex items-center gap-1.5 shadow-2xs mt-0.5"
                                     :style="{ backgroundColor: res.top_group_color || '#be1e2d' }">
                                    <span x-text="res.top_group_name"></span>
                                    <span class="font-mono font-black text-amber-300" x-text="'+' + res.max_points + ' pts'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Podium Placements Strip -->
                        <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                            <!-- 1st Place -->
                            <div class="p-2.5 rounded-xl bg-amber-50/60 border border-amber-200">
                                <div class="text-[10px] font-bold text-amber-900 uppercase font-mono">1st Place</div>
                                <div class="font-bold text-slate-900 truncate mt-0.5" x-text="res.first_name || '—'"></div>
                                <div class="text-[11px] text-amber-800 truncate" x-text="res.first_unit || '—'"></div>
                            </div>

                            <!-- 2nd Place -->
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                                <div class="text-[10px] font-bold text-slate-600 uppercase font-mono">2nd Place</div>
                                <div class="font-bold text-slate-900 truncate mt-0.5" x-text="res.second_name || '—'"></div>
                                <div class="text-[11px] text-slate-600 truncate" x-text="res.second_unit || '—'"></div>
                            </div>

                            <!-- 3rd Place -->
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                                <div class="text-[10px] font-bold text-slate-600 uppercase font-mono">3rd Place</div>
                                <div class="font-bold text-slate-900 truncate mt-0.5" x-text="res.third_name || '—'"></div>
                                <div class="text-[11px] text-slate-600 truncate" x-text="res.third_unit || '—'"></div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                            <span class="text-[11px] text-slate-500 font-mono">
                                Points split: <span x-text="formatPointsSplit(res.points_by_group)"></span>
                            </span>

                            <div class="flex items-center gap-2">
                                <form method="POST" :action="'/admin/results/' + res.id + '/send-to-announcer'" x-show="res.status !== 'send' && res.status !== 'announced' && res.status !== 'published'">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition">
                                        Send to Announcer
                                    </button>
                                </form>

                                <form method="POST" :action="'/admin/results/' + res.id + '/publish'" x-show="res.status !== 'published'">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition">
                                        1-Click Publish
                                    </button>
                                </form>

                                <a :href="'/media-portal/results/' + res.id + '/studio'" target="_blank"
                                   class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-1.5 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    <span>Poster Studio</span>
                                </a>
                            </div>
                        </div>

                    </div>
                </template>
            </div>
        </div>

    </div>
</div>

<script>
function announcementPlanner() {
    const rawGroups = @json($groups);
    const rawResults = @json($resultsData);
    const publishedCount = {{ $stats['published_count'] }};

    return {
        groups: rawGroups.map(g => ({
            id: g.id,
            name: g.name,
            code: g.code,
            color: g.color_hex || '#be1e2d',
            current_points: parseInt(g.points_cache || 0, 10)
        })),

        results: rawResults.map(r => ({
            id: r.id,
            program_code: r.program ? r.program.code : 'PROG',
            program_name: r.program ? r.program.name : 'Unknown',
            category_name: (r.program && r.program.category) ? r.program.category.name : 'General',
            status: r.status,
            status_label: r.status === 'published' ? 'Published' : (r.status === 'announced' ? 'Announced on Stage' : (r.status === 'send' ? 'At Announcer' : 'Declared')),
            first_name: r.first_entry ? (r.first_entry.student ? r.first_entry.student.name : 'Participant') : '',
            first_unit: r.first_entry ? (r.first_entry.group ? r.first_entry.group.name : '') : '',
            second_name: r.second_entry ? (r.second_entry.student ? r.second_entry.student.name : 'Participant') : '',
            second_unit: r.second_entry ? (r.second_entry.group ? r.second_entry.group.name : '') : '',
            third_name: r.third_entry ? (r.third_entry.student ? r.third_entry.student.name : 'Participant') : '',
            third_unit: r.third_entry ? (r.third_entry.group ? r.third_entry.group.name : '') : '',
            top_group_id: r.top_group ? r.top_group.id : null,
            top_group_name: r.top_group ? r.top_group.name : '',
            top_group_color: r.top_group ? r.top_group.color_hex : '',
            max_points: r.max_points || 0,
            points_by_group: r.points_by_group || {}
        })),

        filterStatus: 'ready',
        filterTeamId: null,
        selectedResultIds: [],

        get filteredResults() {
            return this.results.filter(r => {
                if (this.filterStatus === 'ready' && r.status === 'published') return false;
                if (this.filterStatus === 'published' && r.status !== 'published') return false;
                if (this.filterTeamId !== null && r.top_group_id !== this.filterTeamId) return false;
                return true;
            });
        },

        get projectedLeaderboard() {
            const addedPoints = {};
            this.groups.forEach(g => { addedPoints[g.id] = 0; });

            this.selectedResultIds.forEach(id => {
                const res = this.results.find(r => r.id == id);
                if (res && res.points_by_group) {
                    for (let grpId in res.points_by_group) {
                        addedPoints[grpId] = (addedPoints[grpId] || 0) + (res.points_by_group[grpId] || 0);
                    }
                }
            });

            const simulated = this.groups.map(g => ({
                id: g.id,
                name: g.name,
                code: g.code,
                color: g.color,
                current_points: g.current_points,
                added_points: addedPoints[g.id] || 0,
                projected_points: g.current_points + (addedPoints[g.id] || 0)
            }));

            simulated.sort((a, b) => b.projected_points - a.projected_points);
            return simulated;
        },

        get projectedWinnerBanner() {
            const board = this.projectedLeaderboard;
            if (board.length === 0) return '';
            const leader = board[0];
            const runnerUp = board[1];
            const lead = runnerUp ? (leader.projected_points - runnerUp.projected_points) : 0;
            return `ഈ ഫലങ്ങൾ പ്രഖ്യാപിച്ചാൽ '${leader.name}' ${leader.projected_points} പോയിന്റോടെ ഒന്നാം സ്ഥാനത്തെത്തും (+${lead} pts ലീഡ്)!`;
        },

        get suggestedMilestoneLabel() {
            const count = publishedCount + this.selectedResultIds.length;
            return `Standings after ${count} Results`;
        },

        selectAllReady() {
            this.selectedResultIds = this.results.filter(r => r.status !== 'published').map(r => r.id);
        },

        clearSelection() {
            this.selectedResultIds = [];
        },

        formatPointsSplit(pointsMap) {
            if (!pointsMap || Object.keys(pointsMap).length === 0) return 'No points recorded';
            const parts = [];
            for (let gId in pointsMap) {
                const grp = this.groups.find(g => g.id == gId);
                const gName = grp ? grp.code : 'Group';
                parts.push(`${gName}: +${pointsMap[gId]}pts`);
            }
            return parts.join(' • ');
        }
    };
}
</script>
@endsection
