@extends('layouts.media')

@section('title', 'Medal Library | QUAF Media')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#be1e2d] text-white">
                    Medals Library
                </span>
                <span class="text-xs text-slate-500 font-mono">1st, 2nd, 3rd Position Medals</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-2 font-sora">
                Result Medals Management (മെഡലുകൾ)
            </h1>
            <p class="text-xs text-slate-600 mt-1 max-w-2xl">
                ടെംപ്ലേറ്റുകൾ പോലെ തന്നെ കസ്റ്റം മെഡൽ സെറ്റുകൾ ഇവിടെ അപ്‌ലോഡ് ചെയ്യാം. ഒന്നാം സ്ഥാനം, രണ്ടാം സ്ഥാനം, മൂന്നാം സ്ഥാനം എന്നിവയ്ക്ക് നിങ്ങളുടെ സ്വന്തം ഡിസൈൻ മെഡൽ ഇമേജുകൾ നൽകി റിസൾട്ട് പോസ്റ്ററുകളിൽ നേരിട്ട് ഉൾപ്പെടുത്താം.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('media.results.templates') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors">
                Templates
            </a>
            <a href="{{ route('media.results.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors">
                Back to Results
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Upload New Medal Set Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs h-fit space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900">Upload New Medal Set (പുതിയ മെഡൽ സെറ്റ്)</h2>
                <p class="text-[11px] text-slate-500">ട്രാൻസ്പാരന്റ് ബാക്ക്ഗ്രൗണ്ടിലുള്ള PNG ഇമേജുകൾ ഉപയോഗിക്കുന്നത് പോസ്റ്ററുകൾക്ക് കൂടുതൽ ഭംഗി നൽകും.</p>
            </div>

            <form action="{{ route('media.results.medals.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Medal Set Name (മെഡൽ പേര്)</label>
                    <input type="text" name="name" required placeholder="e.g. QUAF Golden Emblem Medals"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-amber-700 mb-1">1st Place Medal (ഒന്നാം സ്ഥാനം PNG)</label>
                    <input type="file" name="first_image" accept="image/png,image/webp,image/svg+xml,image/jpeg" required
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-500 file:text-slate-950 hover:file:bg-amber-400 cursor-pointer border border-slate-300 rounded-xl p-1">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">2nd Place Medal (രണ്ടാം സ്ഥാനം PNG)</label>
                    <input type="file" name="second_image" accept="image/png,image/webp,image/svg+xml,image/jpeg" required
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-300 file:text-slate-900 hover:file:bg-slate-200 cursor-pointer border border-slate-300 rounded-xl p-1">
                </div>

                <div>
                    <label class="block text-xs font-bold text-amber-900 mb-1">3rd Place Medal (മൂന്നാം സ്ഥാനം PNG)</label>
                    <input type="file" name="third_image" accept="image/png,image/webp,image/svg+xml,image/jpeg" required
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-800 file:text-white hover:file:bg-amber-700 cursor-pointer border border-slate-300 rounded-xl p-1">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                           class="rounded border-slate-300 text-[#be1e2d] focus:ring-[#be1e2d]">
                    <label for="is_active" class="text-xs font-medium text-slate-700">Set as Active (ഉടൻ ലഭ്യമാക്കുക)</label>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs shadow-xs transition-colors">
                    Upload Medal Set
                </button>
            </form>
        </div>

        <!-- Existing Medals Grid -->
        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-sm font-bold text-slate-900">Available Medal Sets (ലഭ്യമായ മെഡലുകൾ)</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($medals as $med)
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs flex flex-col justify-between">
                        <div>
                            <div class="p-4 bg-slate-950 flex items-center justify-around gap-2">
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-white/5 rounded-xl flex items-center justify-center p-2 border border-white/10">
                                        <img src="{{ asset($med->first_image) }}" alt="1st" class="max-w-full max-h-full object-contain">
                                    </div>
                                    <span class="text-[9px] font-bold text-amber-400 mt-1 block">1st Place</span>
                                </div>
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-white/5 rounded-xl flex items-center justify-center p-2 border border-white/10">
                                        <img src="{{ asset($med->second_image) }}" alt="2nd" class="max-w-full max-h-full object-contain">
                                    </div>
                                    <span class="text-[9px] font-bold text-slate-300 mt-1 block">2nd Place</span>
                                </div>
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-white/5 rounded-xl flex items-center justify-center p-2 border border-white/10">
                                        <img src="{{ asset($med->third_image) }}" alt="3rd" class="max-w-full max-h-full object-contain">
                                    </div>
                                    <span class="text-[9px] font-bold text-amber-600 mt-1 block">3rd Place</span>
                                </div>
                            </div>

                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xs font-bold text-slate-900 truncate" title="{{ $med->name }}">{{ $med->name }}</h3>
                                    @if($med->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500 text-white">Active</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-400 text-white">Inactive</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">ID: #{{ $med->id }} &bull; Added {{ $med->created_at->format('d M Y') }}</div>
                            </div>
                        </div>

                        <div class="p-4 pt-0 border-t border-slate-100 flex items-center justify-between mt-2">
                            <form action="{{ route('media.results.medals.toggle', $med) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs font-bold text-slate-600 hover:text-slate-900">
                                    {{ $med->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>

                            <form action="{{ route('media.results.medals.destroy', $med) }}" method="POST" onsubmit="return confirm('ഈ മെഡൽ സെറ്റ് ഒഴിവാക്കണമെന്നുറപ്പാണോ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-700">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-8 text-center bg-white rounded-2xl border border-slate-200">
                        <p class="text-xs text-slate-500">ഇതുവരെ കസ്റ്റം മെഡലുകൾ അപ്‌ലോഡ് ചെയ്തിട്ടില്ല. ഇടത് വശത്തെ ഫോം ഉപയോഗിച്ച് പുതിയ മെഡൽ സെറ്റുകൾ ചേർക്കാം.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
