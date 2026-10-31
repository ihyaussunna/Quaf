@extends('layouts.media')

@section('title', 'Team Standings Management — Media Team')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto pb-12 font-sora">
    
    <!-- Top Header Banner -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-900 text-white">
                    Public Website Leaderboard
                </span>
                <span class="text-xs font-mono text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200 font-bold">
                    {{ $standingsBadge }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                ടീം പോയിന്റ് നില &amp; പബ്ലിക് സ്റ്റാൻഡിങ്സ്
            </h1>
            <p class="text-xs text-slate-600 mt-1 max-w-2xl font-ml leading-relaxed">
                പബ്ലിക് വെബ്സൈറ്റിലെ ഹോം പേജിലും ഗ്രൂപ്പ്സ് പേജിലും കാണിക്കേണ്ട ഔദ്യോഗിക ടീം പോയിന്റുകൾ ഇവിടെ ടൈപ്പ് ചെയ്ത് നൽകി പബ്ലിഷ് ചെയ്യാം. കൂടുതൽ പോയിന്റുള്ള ടീം സ്വമേധയാ ഏറ്റവും മുകളിൽ (Rank 1) വരുന്നതാണ്.
            </p>
        </div>

        <div class="flex flex-col sm:items-end gap-1.5 shrink-0">
            <a href="{{ route('home') }}" target="_blank" class="px-4 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
                <span>View Public Site &rarr;</span>
            </a>
            <span class="text-[11px] font-mono text-slate-400">
                ആകെ പബ്ലിഷ് ചെയ്ത ഫലങ്ങൾ: <strong class="text-slate-700">{{ $totalPublishedResults }}</strong>
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm font-semibold flex items-center justify-between shadow-2xs">
            <span class="font-ml">{{ session('success') }}</span>
            <span class="text-xs font-mono text-emerald-700 font-bold">UPDATED &amp; PUBLISHED</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-900 text-xs font-semibold space-y-1">
            @foreach($errors->all() as $err)
                <div>• {{ $err }}</div>
            @endforeach
        </div>
    @endif

    <!-- Standings Editor Form -->
    <form method="POST" action="{{ route('media.standings.update') }}" x-data="{
        resCount: {{ (int) $resultsCount }},
        get formattedLabel() {
            let n = parseInt(this.resCount) || 0;
            return 'After ' + String(n).padStart(2, '0') + ' Results';
        }
    }" class="space-y-6">
        @csrf

        <!-- Milestone Number Setup Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900 font-sora">
                        1. മത്സര ഫലങ്ങളുടെ എണ്ണം (Results Count)
                    </h2>
                    <p class="text-xs text-slate-500 font-ml mt-0.5">
                        പബ്ലിക് വെബ്സൈറ്റിൽ സ്റ്റാൻഡിങ്സ് ടേബിളിന്റെ മുകളിൽ കാണിക്കേണ്ട ഫലങ്ങളുടെ എണ്ണം ഇവിടെ നൽകുക.
                    </p>
                </div>
                
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs font-mono text-slate-400 font-semibold uppercase">Live Badge:</span>
                    <span class="px-3.5 py-1 rounded-full text-xs font-mono font-bold bg-emerald-500 text-slate-950 shadow-2xs" x-text="formattedLabel">
                        {{ $standingsBadge }}
                    </span>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 font-mono">
                        മത്സര ഫലങ്ങളുടെ എണ്ണം (Number of Results):
                    </label>
                    <div class="relative w-full max-w-xs">
                        <input type="number" name="results_count" min="0" max="9999" x-model="resCount"
                               class="w-full pl-4 pr-16 py-3 rounded-2xl bg-slate-50 border-2 border-slate-300 focus:bg-white focus:border-[#be1e2d] text-slate-900 font-mono font-black text-xl text-center focus:outline-none transition">
                        <span class="absolute right-4 top-3.5 text-xs font-mono font-bold text-slate-400">
                            Results
                        </span>
                    </div>
                    <span class="text-[11px] text-slate-500 font-ml mt-1.5 block">
                        ഉദാഹരണത്തിന്: 5 എന്ന് നൽകിയാൽ വെബ്സൈറ്റിൽ <strong>"After 05 Results"</strong> എന്ന് കാണിക്കും.
                    </span>
                </div>

                <!-- Quick Presets -->
                <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                    <span class="text-[11px] font-mono text-slate-400 w-full sm:text-right font-semibold">Quick Set:</span>
                    <button type="button" @click="resCount = 0" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-bold text-xs transition">
                        00
                    </button>
                    <button type="button" @click="resCount = 5" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-bold text-xs transition">
                        05
                    </button>
                    <button type="button" @click="resCount = 10" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-bold text-xs transition">
                        10
                    </button>
                    <button type="button" @click="resCount = 15" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-bold text-xs transition">
                        15
                    </button>
                    <button type="button" @click="resCount = 20" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-bold text-xs transition">
                        20
                    </button>
                    @if($totalPublishedResults > 0)
                        <button type="button" @click="resCount = {{ $totalPublishedResults }}" class="px-3 py-1 rounded-xl bg-amber-100 hover:bg-amber-200 text-amber-900 font-mono font-bold text-xs transition" title="Set to current published results total">
                            Latest ({{ $totalPublishedResults }})
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Team Points Inputs Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-5 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900 font-sora">
                        2. ടീമുകളുടെ പോയിന്റുകൾ നൽകുക (Enter Team Points)
                    </h2>
                    <p class="text-xs text-slate-500 font-ml mt-0.5">
                        ഓരോ ടീമിനും ലഭിച്ച ഔദ്യോഗിക പോയിന്റ് ടൈപ്പ് ചെയ്യുക. സേവ് ചെയ്യുമ്പോൾ പോയിന്റ് കൂടുതൽ ഉള്ള ടീം സ്വമേധയാ ഒന്നാമതായി അറേഞ്ച് ചെയ്യപ്പെടും.
                    </p>
                </div>
                <span class="text-xs font-mono text-slate-400">5 Official Groups</span>
            </div>

            <div class="mt-6 space-y-3.5">
                @foreach($groups as $grp)
                    <div class="p-4 sm:p-5 rounded-2xl border border-slate-200 bg-slate-50/70 hover:bg-white hover:border-slate-300 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <!-- Team Color & Initial Box -->
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center font-black text-white text-base shrink-0 shadow-2xs font-mono"
                                 style="background-color: {{ $grp->color_hex ?: '#be1e2d' }}">
                                {{ substr($grp->code ?: $grp->name, 0, 1) }}
                            </div>

                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-900 text-base sm:text-lg truncate">
                                        {{ $grp->name }}
                                    </h3>
                                    <span class="text-xs font-mono font-bold text-slate-500 bg-slate-200/80 px-2 py-0.5 rounded uppercase">
                                        {{ $grp->code }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Points Input -->
                        <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                            <label class="text-xs font-mono uppercase tracking-wider text-slate-500 font-bold hidden sm:inline">
                                Points:
                            </label>
                            <div class="relative w-36 sm:w-40">
                                <input type="number" min="0" max="100000" name="points[{{ $grp->id }}]"
                                       value="{{ old('points.'.$grp->id, $grp->public_points_cache ?? 0) }}"
                                       class="w-full pl-3 pr-10 py-2.5 rounded-xl bg-white border-2 border-slate-300 focus:border-[#be1e2d] text-slate-900 font-mono font-black text-lg text-right focus:outline-none transition">
                                <span class="absolute right-3 top-3 text-[11px] font-mono font-bold text-slate-400">
                                    PTS
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Submit Publish Button -->
            <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500 font-ml">
                    ഈ ബട്ടൺ ക്ലിക്ക് ചെയ്യുമ്പോൾ നൽകിയ പോയിന്റുകളും 'After XX Results' ലേബലും ഉടൻ തന്നെ പബ്ലിക് വെബ്സൈറ്റിൽ തത്സമയം ദൃശ്യമാകും.
                </div>
                
                <button type="submit" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-sora font-extrabold text-sm uppercase tracking-wider transition shadow-lg hover:shadow-emerald-600/20 flex items-center justify-center gap-2 font-mono shrink-0">
                    <svg class="w-5 h-5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    <span>പബ്ലിക് വെബ്സൈറ്റിലേക്ക് പബ്ലിഷ് ചെയ്യുക</span>
                </button>
            </div>
        </div>

    </form>

</div>
@endsection
