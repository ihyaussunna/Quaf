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

        <div class="flex flex-col sm:flex-row items-center gap-2 shrink-0">
            <a :href="'{{ route('media.print.milestone-standings') }}?count=' + resCount" target="_blank" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-800 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs font-mono">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Standings PDF (Print)</span>
            </a>
            <a href="{{ route('home') }}" target="_blank" class="px-4 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-2xs font-mono">
                <span>View Public Site &rarr;</span>
            </a>
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
    <form method="POST" action="{{ route('media.standings.update') }}" enctype="multipart/form-data" x-data="{
        resCount: {{ (int) $resultsCount }},
        posterPreview: null,
        removePoster: false,
        handlePosterChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.posterPreview = URL.createObjectURL(file);
                this.removePoster = false;
            }
        },
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
        </div>

        <!-- 3. Standings Poster Upload Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900 font-sora flex items-center gap-2">
                        <span>3. ടീം സ്റ്റാൻഡിങ്സ് പോസ്റ്റർ അപ്‌ലോഡ് ചെയ്യുക (Upload Standings Poster)</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-purple-100 text-purple-800">PUBLIC WEBSITE</span>
                    </h2>
                    <p class="text-xs text-slate-500 font-ml mt-0.5">
                        പോയിന്റ് നില അപ്‌ഡേറ്റ് ചെയ്യുമ്പോൾ പുതിയ സ്റ്റാൻഡിങ്സ് പോസ്റ്റർ ഇമേജ് ഇവിടെ അപ്‌ലോഡ് ചെയ്യാം. ഇത് വെബ്സൈറ്റിലെ ഗ്രൂപ്പ്സ് പേജിൽ താഴെ കാണാനും സന്ദർശകർക്ക് ഡൗൺലോഡ് ചെയ്യാനും ലഭ്യമാകും.
                    </p>
                </div>
                @if(!empty($standingsPosterUrl))
                    <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-emerald-50 text-emerald-800 border border-emerald-300 shadow-2xs shrink-0">
                        Live Poster Active
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start pt-2">
                <!-- Upload Area -->
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider font-mono">
                        Select Poster File (PNG, JPG, WebP - Max 15MB):
                    </label>
                    <div class="border-2 border-dashed border-slate-300 hover:border-[#be1e2d] rounded-2xl p-6 text-center bg-slate-50/50 hover:bg-white transition cursor-pointer relative">
                        <input type="file" name="standings_poster" accept="image/png,image/jpeg,image/webp"
                               @change="handlePosterChange($event)"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="space-y-2 pointer-events-none">
                            <div class="w-12 h-12 rounded-2xl bg-slate-200 text-slate-600 mx-auto flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-800">ക്ലിക്ക് ചെയ്ത് പോസ്റ്റർ തിരഞ്ഞെടുക്കുക</div>
                            <div class="text-[11px] text-slate-500 font-mono">Drag and drop or browse file</div>
                        </div>
                    </div>

                    @if(!empty($standingsPosterUrl))
                        <div class="pt-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-red-600 select-none">
                                <input type="checkbox" name="remove_poster" value="1" x-model="removePoster" class="w-4 h-4 rounded border-slate-300 text-red-600 focus:ring-0">
                                <span>നിലവിലെ പോസ്റ്റർ നീക്കം ചെയ്യുക (Remove active poster)</span>
                            </label>
                        </div>
                    @endif
                </div>

                <!-- Preview Area -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider font-mono">
                        Poster Preview:
                    </label>
                    
                    <!-- New File Preview -->
                    <template x-if="posterPreview">
                        <div class="rounded-2xl border-2 border-emerald-400 p-2 bg-emerald-50/40 space-y-2">
                            <span class="text-[11px] font-mono font-bold text-emerald-800 uppercase block">New Poster to Upload:</span>
                            <img :src="posterPreview" class="max-h-72 w-auto mx-auto rounded-xl object-contain shadow-md">
                        </div>
                    </template>

                    <!-- Active Saved Poster -->
                    @if(!empty($standingsPosterUrl))
                        <div x-show="!posterPreview && !removePoster" class="rounded-2xl border border-slate-200 p-3 bg-slate-50 space-y-2">
                            <div class="flex items-center justify-between text-xs font-mono">
                                <span class="font-bold text-slate-700">Currently Active on Website:</span>
                                <a href="{{ $standingsPosterUrl }}" target="_blank" class="text-purple-700 font-bold hover:underline">Full Size &rarr;</a>
                            </div>
                            <img src="{{ $standingsPosterUrl }}" alt="Active Standings Poster" class="max-h-64 w-auto mx-auto rounded-xl object-contain shadow-xs">
                        </div>
                    @endif

                    <div x-show="!posterPreview && (!{{ !empty($standingsPosterUrl) ? 'true' : 'false' }} || removePoster)" class="rounded-2xl border border-slate-200 p-8 text-center text-slate-400 text-xs font-mono">
                        No poster selected. Standings leaderboard table will be shown without poster view.
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Publish Button Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-slate-500 font-ml">
                ഈ ബട്ടൺ ക്ലിക്ക് ചെയ്യുമ്പോൾ നൽകിയ പോയിന്റുകളും പുതിയ സ്റ്റാൻഡിങ്സ് പോസ്റ്ററും തത്സമയം പബ്ലിക് വെബ്സൈറ്റിൽ ലഭ്യമാകും.
            </div>
            
            <button type="submit" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-sora font-extrabold text-sm uppercase tracking-wider transition shadow-lg hover:shadow-emerald-600/20 flex items-center justify-center gap-2 font-mono shrink-0 cursor-pointer">
                <svg class="w-5 h-5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                <span>പബ്ലിക് വെബ്സൈറ്റിലേക്ക് പബ്ലിഷ് ചെയ്യുക</span>
            </button>
        </div>

    </form>

</div>
@endsection
