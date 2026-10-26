@extends('layouts.public')

@section('content')
<div class="py-8 sm:py-12 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Hero Title Section -->
        <div class="text-center max-w-3xl mx-auto mb-8 sm:mb-10">
            <!-- Festive Badge -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-red-50 border border-red-200/80 shadow-xs mb-3.5">
                <span class="w-2 h-2 rounded-full bg-[#be1e2d] animate-ping"></span>
                <span class="text-[11px] sm:text-xs font-semibold tracking-wider uppercase text-[#be1e2d] font-mono">QUAF 2026 • ĀDABĪC INHERITANCE</span>
            </div>

            <!-- Page Title -->
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight font-sora text-slate-900 leading-none mb-3">
                QF CHEST DIRECTORY
            </h1>

            <!-- Descriptive Text -->
            <p class="text-base sm:text-lg text-slate-700 font-medium max-w-2xl mx-auto font-revathi" style="line-height: 1.22;">
                ക്വാഫ് ഒമ്പതാം എഡിഷനിലെ മത്സരാർത്ഥികൾക്ക് അവരുടെ പേര്, QF ചെസ്റ്റ് നമ്പർ, ഗ്രൂപ്പ്, ടീം ലീഡർ എന്നിവ എളുപ്പത്തിൽ കണ്ടെത്താം.
            </p>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-light">
                Search participant name, QF chest number, academic group, class, or zone across all {{ number_format($totalCount) }} records.
            </p>
        </div>

        <!-- 5 Teams & Leaders Cards (Clickable Filter) -->
        <div class="mb-10">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#be1e2d]"></span>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 uppercase tracking-wider font-sora">Team Flags & Leadership</h2>
                </div>
                <span class="text-xs text-slate-500 font-mono">5 Academic Groups</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 sm:gap-4">
                @foreach($teams as $tName => $t)
                <div onclick="selectTeamFilter('{{ addslashes($tName) }}')" 
                     id="team-card-{{ Str::slug($tName) }}"
                     class="team-card group bg-white rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-md transition-all cursor-pointer overflow-hidden transform hover:-translate-y-1">
                    
                    <!-- Flag Header (Clean authentic flag without overlays) -->
                    <div class="h-24 sm:h-28 w-full relative bg-slate-900 overflow-hidden">
                        <img src="{{ $t['flag'] }}" 
                             alt="{{ $tName }} Flag" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>

                    <!-- Leadership Info -->
                    <div class="p-3 text-xs space-y-1.5 bg-white">
                        <div class="text-slate-700 leading-snug">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Leader:</span>
                            <strong class="text-slate-900 text-xs font-bold">{{ $t['leader'] }}</strong>
                        </div>
                        <div class="text-slate-600 leading-snug pt-1 border-t border-slate-100">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Assistants:</span>
                            <span class="text-[11px] text-slate-600">{{ implode(' • ', $t['assistants']) }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Unified Search & Filter Control Bar -->
        <div class="sticky top-16 sm:top-20 z-30 bg-slate-50/95 backdrop-blur-md py-4 -mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 border-y border-slate-200/70 mb-8 shadow-xs">
            <div class="space-y-3 max-w-5xl mx-auto">
                
                <!-- Live Search Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" 
                           id="searchInput" 
                           placeholder="പേര് അല്ലെങ്കിൽ QF നമ്പർ നൽകി തിരയുക (Search participant name or QF number...)"
                           autocomplete="off"
                           class="w-full pl-11 pr-24 py-3 bg-white border border-slate-200 rounded-2xl text-slate-800 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#be1e2d] focus:border-[#be1e2d] shadow-sm transition-all">
                    
                    <!-- Quick Clear Button -->
                    <button type="button" 
                            id="clearSearchBtn" 
                            class="hidden absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs font-semibold text-slate-400 hover:text-slate-700 transition cursor-pointer">
                        <span class="bg-slate-100 hover:bg-slate-200 px-2 py-1 rounded-lg border border-slate-200">Clear</span>
                    </button>
                </div>

                <!-- Filter Chips Row (Team & Zone) -->
                <div class="flex flex-wrap items-center justify-between gap-2.5 pt-1">
                    
                    <!-- Team Chips -->
                    <div class="flex flex-wrap items-center gap-1.5 text-xs font-semibold" id="teamFilterContainer">
                        <span class="text-slate-400 text-[11px] uppercase tracking-wider mr-1">Team:</span>
                        <button type="button" onclick="setTeamFilter('')" data-team="" class="team-chip px-3 py-1.5 rounded-xl transition border bg-[#be1e2d] text-white border-[#be1e2d] shadow-xs cursor-pointer">
                            All Teams
                        </button>
                        @foreach($teams as $tName => $t)
                        <button type="button" onclick="setTeamFilter('{{ addslashes($tName) }}')" data-team="{{ $tName }}" class="team-chip px-3 py-1.5 rounded-xl transition border bg-white hover:bg-slate-100 text-slate-700 border-slate-200 shadow-xs cursor-pointer">
                            {{ $tName }}
                        </button>
                        @endforeach
                    </div>

                    <!-- Zone Chips -->
                    <div class="flex items-center gap-1.5 text-xs font-semibold" id="zoneFilterContainer">
                        <span class="text-slate-400 text-[11px] uppercase tracking-wider mr-1">Zone:</span>
                        <button type="button" onclick="setZoneFilter('')" data-zone="" class="zone-chip px-2.5 py-1.5 rounded-xl transition border bg-[#005c94] text-white border-[#005c94] shadow-xs cursor-pointer">
                            All
                        </button>
                        <button type="button" onclick="setZoneFilter('A ZONE')" data-zone="A ZONE" class="zone-chip px-2.5 py-1.5 rounded-xl transition border bg-white hover:bg-slate-100 text-slate-700 border-slate-200 shadow-xs cursor-pointer">
                            A Zone
                        </button>
                        <button type="button" onclick="setZoneFilter('B ZONE')" data-zone="B ZONE" class="zone-chip px-2.5 py-1.5 rounded-xl transition border bg-white hover:bg-slate-100 text-slate-700 border-slate-200 shadow-xs cursor-pointer">
                            B Zone
                        </button>
                        <button type="button" onclick="setZoneFilter('C ZONE')" data-zone="C ZONE" class="zone-chip px-2.5 py-1.5 rounded-xl transition border bg-white hover:bg-slate-100 text-slate-700 border-slate-200 shadow-xs cursor-pointer">
                            C Zone
                        </button>
                    </div>

                </div>

            </div>
        </div>

        <!-- Results Header & Live Count -->
        <div class="flex items-center justify-between mb-4">
            <h3 id="resultsHeading" class="text-sm sm:text-base font-bold text-slate-800 uppercase tracking-wider font-sora">
                All Participants
            </h3>
            <span id="resultsCount" class="text-xs sm:text-sm font-semibold text-slate-500 font-mono">
                Loading...
            </span>
        </div>

        <!-- Participants Cards Grid -->
        <div id="participantsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">
            <!-- Rendered dynamically by JavaScript -->
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-16 px-4 bg-white rounded-3xl border border-dashed border-slate-300 max-w-xl mx-auto my-8">
            <div class="w-16 h-16 rounded-full bg-red-50 text-[#be1e2d] flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h4 class="text-base font-bold text-slate-800 mb-1">മത്സരാർത്ഥികളെ കണ്ടെത്തിയില്ല</h4>
            <p class="text-xs sm:text-sm text-slate-500 max-w-sm mx-auto">
                നിങ്ങൾ നൽകിയ വിവരങ്ങളുള്ള ആരും ലിസ്റ്റിൽ ഇല്ല. പേര് അല്ലെങ്കിൽ QF നമ്പർ വീണ്ടും പരിശോധിച്ച് തിരയുക.
            </p>
            <button type="button" onclick="resetAllFilters()" class="mt-4 px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer">
                Reset All Filters
            </button>
        </div>

        <!-- Load More Action -->
        <div id="loadMoreContainer" class="text-center py-4">
            <button type="button" 
                    id="loadMoreBtn" 
                    class="py-3 px-8 rounded-2xl bg-white border border-slate-300 hover:bg-slate-100 text-slate-800 text-xs sm:text-sm font-bold shadow-xs hover:shadow-sm transition cursor-pointer">
                Load More Records
            </button>
        </div>

    </div>
</div>

<script>
// Complete Directory Dataset
const allMembers = @json($members);
const teamColors = {
    'Pacto Hikmic': '#2e3192',
    'Yugo Rushdic': '#ac1553',
    'Conco Majdic': '#d97706',
    'Lumo Fikric': '#4d2469',
    'Unio Hilmic': '#451a03'
};

const PAGE_SIZE = 48;
let currentVisible = PAGE_SIZE;
let activeTeam = '';
let activeZone = '';
let activeQuery = '';

const searchInput = document.getElementById('searchInput');
const clearSearchBtn = document.getElementById('clearSearchBtn');
const participantsGrid = document.getElementById('participantsGrid');
const emptyState = document.getElementById('emptyState');
const resultsHeading = document.getElementById('resultsHeading');
const resultsCount = document.getElementById('resultsCount');
const loadMoreContainer = document.getElementById('loadMoreContainer');
const loadMoreBtn = document.getElementById('loadMoreBtn');

function getFilteredData() {
    const q = activeQuery.trim().toLowerCase();
    return allMembers.filter(m => {
        const matchTeam = !activeTeam || m.team === activeTeam;
        const matchZone = !activeZone || m.zone.toLowerCase() === activeZone.toLowerCase();
        const matchQuery = !q || 
            m.name.toLowerCase().includes(q) || 
            m.qf.toLowerCase().includes(q) || 
            m.class.toLowerCase().includes(q) || 
            m.team.toLowerCase().includes(q) ||
            m.leader.toLowerCase().includes(q);
        return matchTeam && matchZone && matchQuery;
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderCard(m) {
    const color = teamColors[m.team] || '#be1e2d';
    return `
    <article class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:shadow-md transition-all relative overflow-hidden flex flex-col justify-between" style="border-left: 5px solid ${color};">
        <div>
            <!-- Top Badges Row -->
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-xl sm:text-2xl font-black font-mono tracking-tight" style="color: ${color};">
                    ${escapeHtml(m.qf)}
                </span>
                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full text-white" style="background-color: ${color};">
                    ${escapeHtml(m.team)}
                </span>
            </div>

            <!-- Participant Name -->
            <h4 class="text-sm sm:text-base font-bold text-slate-900 tracking-wide leading-snug mb-3">
                ${escapeHtml(m.name)}
            </h4>

            <!-- Category & Zone Tags -->
            <div class="flex flex-wrap items-center gap-1.5 mb-3">
                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    Class: <strong class="text-slate-900">${escapeHtml(m.class)}</strong>
                </span>
                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    ${escapeHtml(m.zone)}
                </span>
            </div>
        </div>

        <!-- Leadership Information -->
        <div class="pt-2.5 border-t border-slate-100 text-[11px] text-slate-500 leading-relaxed space-y-0.5">
            <div>
                <span class="font-semibold text-slate-700">Leader:</span> ${escapeHtml(m.leader)}
            </div>
            ${m.assistants && m.assistants.length ? `
            <div class="text-[10px] text-slate-400">
                <span class="font-semibold text-slate-500">Assistants:</span> ${escapeHtml(m.assistants.join(' • '))}
            </div>` : ''}
        </div>
    </article>
    `;
}

function updateUI() {
    const filtered = getFilteredData();
    const total = filtered.length;
    const toShow = filtered.slice(0, currentVisible);

    // Update Headings & Count
    if (activeTeam || activeZone || activeQuery) {
        resultsHeading.textContent = 'Filtered Results';
    } else {
        resultsHeading.textContent = 'All Participants';
    }
    resultsCount.textContent = `${total} records found`;

    // Render Cards or Empty State
    if (total === 0) {
        participantsGrid.innerHTML = '';
        emptyState.classList.remove('hidden');
        loadMoreContainer.classList.add('hidden');
    } else {
        emptyState.classList.add('hidden');
        participantsGrid.innerHTML = toShow.map(renderCard).join('');

        if (total > currentVisible) {
            loadMoreContainer.classList.remove('hidden');
            const remaining = total - currentVisible;
            loadMoreBtn.textContent = `Load More (${Math.min(PAGE_SIZE, remaining)} more of ${remaining})`;
        } else {
            loadMoreContainer.classList.add('hidden');
        }
    }

    // Toggle clear button
    if (searchInput.value.trim().length > 0) {
        clearSearchBtn.classList.remove('hidden');
    } else {
        clearSearchBtn.classList.add('hidden');
    }
}

// Live Search Listener
searchInput.addEventListener('input', function() {
    activeQuery = this.value;
    currentVisible = PAGE_SIZE;
    updateUI();
});

clearSearchBtn.addEventListener('click', function() {
    searchInput.value = '';
    activeQuery = '';
    currentVisible = PAGE_SIZE;
    updateUI();
    searchInput.focus();
});

// Team Filter Controls
function setTeamFilter(team) {
    activeTeam = team;
    currentVisible = PAGE_SIZE;

    // Update Team Chips
    document.querySelectorAll('.team-chip').forEach(btn => {
        const btnTeam = btn.getAttribute('data-team');
        if (btnTeam === team) {
            btn.className = 'team-chip px-3 py-1.5 rounded-xl transition border bg-[#be1e2d] text-white border-[#be1e2d] shadow-xs cursor-pointer';
        } else {
            btn.className = 'team-chip px-3 py-1.5 rounded-xl transition border bg-white hover:bg-slate-100 text-slate-700 border-slate-200 shadow-xs cursor-pointer';
        }
    });

    updateUI();
}

function selectTeamFilter(team) {
    setTeamFilter(team);
    // Smooth scroll down to search/results
    const topOffset = document.getElementById('searchInput').getBoundingClientRect().top + window.pageYOffset - 110;
    window.scrollTo({ top: topOffset, behavior: 'smooth' });
}

// Zone Filter Controls
function setZoneFilter(zone) {
    activeZone = zone;
    currentVisible = PAGE_SIZE;

    // Update Zone Chips
    document.querySelectorAll('.zone-chip').forEach(btn => {
        const btnZone = btn.getAttribute('data-zone');
        if (btnZone === zone) {
            btn.className = 'zone-chip px-2.5 py-1.5 rounded-xl transition border bg-[#005c94] text-white border-[#005c94] shadow-xs cursor-pointer';
        } else {
            btn.className = 'zone-chip px-2.5 py-1.5 rounded-xl transition border bg-white hover:bg-slate-100 text-slate-700 border-slate-200 shadow-xs cursor-pointer';
        }
    });

    updateUI();
}

// Reset Filters
function resetAllFilters() {
    searchInput.value = '';
    activeQuery = '';
    setTeamFilter('');
    setZoneFilter('');
}

// Load More Listener
loadMoreBtn.addEventListener('click', function() {
    currentVisible += PAGE_SIZE;
    updateUI();
});

// Initialize on Load
document.addEventListener('DOMContentLoaded', function() {
    updateUI();
});
</script>
@endsection
