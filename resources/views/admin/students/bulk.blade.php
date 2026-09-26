@extends('layouts.admin', ['title' => 'Bulk Register Participants'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Top Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.students.index') }}" class="text-xs font-mono text-[#be1e2d] hover:underline mb-1.5 block font-semibold">
                ← Back to Participants
            </a>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight font-sans">
                Bulk Register Participants
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                ഒന്നിലധികം വിദ്യാർത്ഥികളെ ഒരേസമയം വേഗത്തിൽ ചേർക്കുക (Paste text list or upload CSV)
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.students.bulk-template') }}" class="px-4 py-2 rounded-xl border border-slate-300 hover:border-[#be1e2d] bg-white text-slate-700 hover:text-slate-900 text-xs font-mono font-bold flex items-center gap-2 shadow-2xs transition-colors">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Download Sample CSV</span>
            </a>
            <a href="{{ route('admin.students.create') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-mono font-medium transition-colors">
                Single Add
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <p class="font-bold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span>Registration Issue</span>
            </p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.students.bulk-store') }}" enctype="multipart/form-data" 
          x-data="{
              activeTab: 'paste',
              pasteContent: '{{ old('paste_text', '') }}',
              defaultGroup: '{{ old('default_group_id', '') }}',
              defaultCategory: '{{ old('default_category', '') }}',
              get lineCount() {
                  if (!this.pasteContent.trim()) return 0;
                  return this.pasteContent.trim().split(/\r\n|\r|\n/).filter(l => l.trim() !== '').length;
              },
              insertSample() {
                  const sample = `QF3001\tIMRAN MAVINAKATT\tTQS\tA ZONE\nQF3002\tSYD SWABAH\tTQS\tA ZONE\nQF3003\tMIDLAJ MANGAD\tTQS\tA ZONE\nQF3004\tKABEER KUTTOTH\tTQS\tA ZONE\nQF3005\tMUBASHIR POONOOR\tTQS\tA ZONE`;
                  this.pasteContent = sample;
              },
              insertAltSample() {
                  const sample = `Muhammed Bilal, LUMO, Class 4, 9847111111\nAhmed Shafeeq, PACTO, Class 3, 9847222222\nZaid Rayan, CONCO, Class 2, 9847333333\nUmar Swalih, UNIO, Class 1, 9847444444\nHassan Ali, YUGO, TQS, 9847555555`;
                  this.pasteContent = sample;
              }
          }"
          class="space-y-6">
        @csrf

        <!-- Top Defaults Card -->
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <span class="text-xs font-mono font-bold text-slate-700 uppercase tracking-wider">
                    Default Selection (ഓപ്ഷണൽ ഡിഫോൾട്ട് ക്രമീകരണങ്ങൾ)
                </span>
                <span class="text-[11px] font-mono text-slate-400">
                    If not specified in rows, these will be applied automatically
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">
                        Default Group (ഡിഫോൾട്ട് ഗ്രൂപ്പ്)
                    </label>
                    <select name="default_group_id" x-model="defaultGroup" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
                        <option value="">-- None (Specify in each row / ഓരോ വരിയിലും നൽകുക) --</option>
                        @foreach($groups as $grp)
                            <option value="{{ $grp->id }}" {{ old('default_group_id') == $grp->id ? 'selected' : '' }}>
                                {{ $grp->name }} ({{ $grp->code }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1 font-mono">
                        ഒരു ഗ്രൂപ്പിലെ കുട്ടികളെ മാത്രം ആഡ് ചെയ്യുമ്പോൾ ഇവിടെ സെലക്ട് ചെയ്യുക.
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">
                        Default Zone (ഡിഫോൾട്ട് സോൺ)
                    </label>
                    <select name="default_category" x-model="defaultCategory" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors">
                        <option value="">-- Auto-detect from Class (ക്ലാസിൽ നിന്ന് കണ്ടെത്തുക) --</option>
                        @foreach($categories as $val => $label)
                            <option value="{{ $val }}" {{ old('default_category') == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1 font-mono">
                        ക്ലാസ് നൽകുമ്പോൾ സോൺ ഓട്ടോമാറ്റിക് ആയി ഡിറ്റക്ട് ചെയ്യും.
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Input Container with Tabs -->
        <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-xs">
            <!-- Tabs Bar -->
            <div class="flex items-center border-b border-slate-200 bg-slate-50 px-4 pt-3 gap-2">
                <button type="button" @click="activeTab = 'paste'" 
                        :class="activeTab === 'paste' ? 'bg-white border-t-2 border-t-[#be1e2d] text-slate-900 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800 font-medium'"
                        class="px-5 py-2.5 rounded-t-xl text-xs font-mono uppercase tracking-wider transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>1. Quick Paste Text (ദ്രുത പേസ്റ്റ്)</span>
                </button>

                <button type="button" @click="activeTab = 'file'" 
                        :class="activeTab === 'file' ? 'bg-white border-t-2 border-t-[#be1e2d] text-slate-900 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800 font-medium'"
                        class="px-5 py-2.5 rounded-t-xl text-xs font-mono uppercase tracking-wider transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <span>2. Upload CSV File (CSV അപ്‌ലോഡ്)</span>
                </button>
            </div>

            <!-- Tab 1: Quick Paste -->
            <div x-show="activeTab === 'paste'" class="p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2">
                    <div>
                        <label class="block text-xs font-mono uppercase font-bold text-slate-700">
                            Paste Student List (വിദ്യാർത്ഥികളുടെ പട്ടിക പേസ്റ്റ് ചെയ്യുക)
                        </label>
                        <p class="text-[11px] text-slate-400 mt-0.5 font-mono">
                            Recommended Format: <span class="text-slate-900 font-bold">Chest No, Name, Class, Zone</span> (One student per line)
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="insertSample()" class="text-[11px] font-mono text-[#be1e2d] hover:underline font-semibold">
                            + Insert 4-Col Sample (Chest, Name, Class, Zone)
                        </button>
                        <button type="button" @click="insertAltSample()" class="text-[11px] font-mono text-slate-500 hover:text-slate-800 underline">
                            5-Col Format
                        </button>
                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-mono font-bold">
                            Lines: <span x-text="lineCount" class="text-[#be1e2d]">0</span>
                        </span>
                    </div>
                </div>

                <textarea name="paste_text" x-model="pasteContent" rows="12"
                          placeholder="QF3001	IMRAN MAVINAKATT	TQS	A ZONE
QF3002	SYD SWABAH	TQS	A ZONE
QF3003	MIDLAJ MANGAD	TQS	A ZONE
QF3004	KABEER KUTTOTH	TQS	A ZONE
QF3005	MUBASHIR POONOOR	TQS	A ZONE

(Note: Direct copy-paste from Excel or Google Sheets supported! You can also paste: Name, Group, Class/Zone, Contact)"
                          class="w-full bg-slate-50 border border-slate-300 rounded-xl p-4 text-xs font-mono text-slate-900 focus:outline-none focus:border-[#be1e2d] focus:bg-white transition-colors leading-relaxed"></textarea>

                <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200 text-amber-900 text-xs space-y-1">
                    <p class="font-bold flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        <span>ഉപയോഗ ക്രമം (Instructions):</span>
                    </p>
                    <ul class="list-disc list-inside text-[11px] text-amber-800 space-y-0.5 font-mono">
                        <li>പ്രധാന ഫോർമാറ്റ്: <strong class="text-amber-950 font-bold">Chest No, Name, Class, Zone</strong> (Excel/Spreadsheet-ൽ നിന്ന് കോപ്പി ചെയ്ത് നേരിട്ട് ഇവിടെ പേസ്റ്റ് ചെയ്യാം).</li>
                        <li>ടാബ് (Tab), കോമ (,), അല്ലെങ്കിൽ പൈപ്പ് (|) വഴി വേർതിരിക്കാം.</li>
                        <li>മുകളിൽ ഡിഫോൾട്ട് ഗ്രൂപ്പ് സെലക്ട് ചെയ്യുകയോ, പട്ടികക്ക് മുകളിൽ ഗ്രൂപ്പ് പേര് നൽകുകയോ (ഉദാ: CONCO MAJDIC) ചെയ്യാം.</li>
                        <li>ചെസ്റ്റ് നമ്പർ നൽകിയാൽ ആ ചെസ്റ്റ് നമ്പർ നേരിട്ട് അസൈൻ ചെയ്യപ്പെടും; നൽകിയില്ലെങ്കിൽ സിസ്റ്റം സ്വയം നമ്പർ അലോക്കേറ്റ് ചെയ്യും.</li>
                    </ul>
                </div>
            </div>

            <!-- Tab 2: Upload CSV -->
            <div x-show="activeTab === 'file'" class="p-6 space-y-5" style="display: none;">
                <div>
                    <label class="block text-xs font-mono uppercase font-bold text-slate-700 mb-2">
                        Select CSV Document (.csv)
                    </label>
                    <div class="border-2 border-dashed border-slate-300 hover:border-[#be1e2d] rounded-2xl p-8 text-center bg-slate-50 transition-colors">
                        <input type="file" name="csv_file" accept=".csv,.txt" id="csv_file_input" class="hidden" onchange="document.getElementById('fileNameDisplay').textContent = this.files[0]?.name || ''">
                        <label for="csv_file_input" class="cursor-pointer space-y-3 block">
                            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-500 flex items-center justify-center mx-auto shadow-2xs">
                                <svg class="w-6 h-6 text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-800">Click to choose CSV file or drag and drop here</p>
                                <p class="text-[11px] font-mono text-slate-400 mt-1">Accepts UTF-8 formatted CSV files with header</p>
                            </div>
                        </label>
                        <p id="fileNameDisplay" class="text-xs font-mono font-bold text-[#be1e2d] mt-3"></p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-xs">
                        <p class="font-bold text-slate-800">Need a template format?</p>
                        <p class="text-slate-500 text-[11px] font-mono">Download sample spreadsheet, fill data, and upload</p>
                    </div>
                    <a href="{{ route('admin.students.bulk-template') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-300 hover:border-slate-400 text-xs font-mono font-bold text-slate-700 shadow-2xs">
                        Download Template CSV
                    </a>
                </div>
            </div>

            <!-- Footer Action Bar -->
            <div class="p-5 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
                <a href="{{ route('admin.students.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-white text-xs font-mono font-medium transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-7 py-3 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white text-xs font-mono font-bold uppercase tracking-wider shadow-md shadow-[#be1e2d]/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Import & Register All Students</span>
                </button>
            </div>
        </div>
    </form>

    <!-- Reference Cheat Sheet -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs space-y-3">
            <h3 class="text-xs font-mono font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#be1e2d]"></span>
                <span>Official Groups & Chest Series</span>
            </h3>
            <div class="space-y-2 text-xs font-mono">
                @foreach($groups as $grp)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full" style="background-color: {{ $grp->color_hex }}"></span>
                            <span class="font-bold text-slate-800">{{ $grp->name }}</span>
                            <span class="text-slate-400">({{ $grp->code }})</span>
                        </div>
                        <span class="text-[11px] font-bold text-slate-600">
                            {{ $grp->code === 'LUMO' ? '1001+' : ($grp->code === 'PACTO' ? '2001+' : ($grp->code === 'CONCO' ? '3001+' : ($grp->code === 'UNIO' ? '4001+' : '5001+'))) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs space-y-3">
            <h3 class="text-xs font-mono font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <span>Zone Auto-Detection from Class</span>
            </h3>
            <div class="space-y-2 text-xs font-mono">
                <div class="p-2 rounded-lg bg-red-50/50 border border-red-200 text-red-900">
                    <span class="font-bold block">A Zone (Class 4 / TQS)</span>
                    <span class="text-[11px] text-red-700">NF4, UH4, S4, ID4, UT4, L4, TQS, Rabia</span>
                </div>
                <div class="p-2 rounded-lg bg-amber-50/50 border border-amber-200 text-amber-900">
                    <span class="font-bold block">B Zone (Class 3)</span>
                    <span class="text-[11px] text-amber-700">NF3, ID3, UH3, UT3, S3, L3, Salis</span>
                </div>
                <div class="p-2 rounded-lg bg-sky-50/50 border border-sky-200 text-sky-900">
                    <span class="font-bold block">C Zone (Class 1 & 2)</span>
                    <span class="text-[11px] text-sky-700">U1, U2, L2, S1, S2, Ula, Sani</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
