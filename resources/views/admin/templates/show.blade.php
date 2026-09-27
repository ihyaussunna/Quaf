@extends('layouts.admin', ['title' => 'Template Preview — ' . ucfirst(str_replace('-', ' ', $type))])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-slate-500 mb-1">
                <a href="{{ route('admin.templates.index') }}" class="hover:text-[#f3bd2e]">← Back to Templates</a>
                <span>/</span>
                <span class="capitalize">{{ str_replace('-', ' ', $type) }}</span>
            </div>
            <h1 class="text-3xl font-sora font-black text-slate-900 capitalize">{{ str_replace('-', ' ', $type) }}</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">Live design preview with sample data.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase hover:brightness-110 shadow-xs">
                🖨️ Print Sample
            </button>
        </div>
    </div>

    <!-- Live Preview Container -->
    <div class="bg-slate-200/70 p-6 sm:p-12 rounded-2xl flex items-center justify-center overflow-x-auto">
        @if($type === 'merit-certificate')
            <!-- Merit Certificate Preview -->
            <div class="w-[800px] h-[560px] bg-white border-8 border-double border-[#f3bd2e] p-10 flex flex-col justify-between shadow-2xl relative">
                <div class="absolute top-4 left-4 text-[10px] font-mono text-slate-400">CERT NO: QUAF09-MC-9082</div>
                <div class="absolute top-4 right-4 text-[10px] font-mono text-[#f3bd2e] font-bold">★ MERIT CERTIFICATE ★</div>

                <!-- Header -->
                <div class="text-center space-y-1">
                    <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF Logo" class="h-14 mx-auto object-contain">
                    <p class="text-xs font-mono text-slate-500 italic">The Grand Cultural Conclave of Talents</p>
                </div>

                <!-- Body -->
                <div class="text-center my-6 space-y-4">
                    <p class="text-xs font-sora italic text-slate-600">This is to certify that</p>
                    <h3 class="text-2xl font-sora font-black text-slate-900 border-b-2 border-slate-300 pb-1 inline-block min-w-[320px]">
                        {{ $sampleStudent?->name ?? 'Muhammed Nihal' }}
                    </h3>
                    <p class="text-xs font-mono text-slate-600">
                        representing <strong class="text-slate-900 font-sora">{{ $sampleStudent?->group?->name ?? 'Group of Cordoba' }}</strong>
                        has secured <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-900 font-bold">FIRST PLACE (A GRADE)</span> in
                    </p>
                    <h4 class="text-lg font-sora font-bold text-[#f3bd2e]">
                        Elocution (English) — A Zone
                    </h4>
                </div>

                <!-- Footer Signatures -->
                <div class="flex items-end justify-between border-t border-slate-200 pt-6 text-center text-xs font-mono">
                    <div class="w-40 border-t border-slate-400 pt-1">
                        <span class="text-slate-800 font-bold block">General Convener</span>
                        <span class="text-[9px] text-slate-400">QUAF 09 Committee</span>
                    </div>
                    <div class="w-20 h-20 rounded-full border-2 border-dashed border-[#f3bd2e] flex flex-col items-center justify-center p-1 text-[8px] font-mono text-[#f3bd2e]">
                        <span class="font-bold text-xs">OFFICIAL</span>
                        <span>SEAL</span>
                    </div>
                    <div class="w-40 border-t border-slate-400 pt-1">
                        <span class="text-slate-800 font-bold block">President / Director</span>
                        <span class="text-[9px] text-slate-400">Markaz Sunniyya</span>
                    </div>
                </div>
            </div>

        @elseif($type === 'participation-certificate')
            <!-- Participation Certificate Preview -->
            <div class="w-[800px] h-[560px] bg-white border-8 border-double border-slate-400 p-10 flex flex-col justify-between shadow-2xl relative">
                <div class="absolute top-4 left-4 text-[10px] font-mono text-slate-400">CERT NO: QUAF09-PC-4120</div>
                <div class="absolute top-4 right-4 text-[10px] font-mono text-blue-700 font-bold">CERTIFICATE OF PARTICIPATION</div>

                <div class="text-center space-y-1">
                    <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF Logo" class="h-14 mx-auto object-contain">
                    <p class="text-xs font-mono text-slate-500 italic">Celebrating Art, Literature & Culture</p>
                </div>

                <div class="text-center my-6 space-y-4">
                    <p class="text-xs font-sora italic text-slate-600">Proudly presented to</p>
                    <h3 class="text-2xl font-sora font-black text-slate-900 border-b-2 border-slate-300 pb-1 inline-block min-w-[320px]">
                        {{ $sampleStudent?->name ?? 'Ahmad Shafi' }}
                    </h3>
                    <p class="text-xs font-mono text-slate-600">
                        for active participation and creative performance in
                    </p>
                    <h4 class="text-lg font-sora font-bold text-slate-800">
                        Calligraphy & Design Exhibition
                    </h4>
                </div>

                <div class="flex items-end justify-between border-t border-slate-200 pt-6 text-center text-xs font-mono">
                    <div class="w-40 border-t border-slate-400 pt-1">
                        <span class="text-slate-800 font-bold block">General Convener</span>
                    </div>
                    <div class="w-40 border-t border-slate-400 pt-1">
                        <span class="text-slate-800 font-bold block">Staff Advisor</span>
                    </div>
                </div>
            </div>

        @elseif($type === 'student-id-card')
            <!-- Student ID Card Preview -->
            <div class="w-[320px] bg-white border-2 border-slate-300 rounded-2xl overflow-hidden shadow-xl text-center">
                <div class="bg-gradient-to-r from-[#f3bd2e] to-[#be1e2d] p-4 text-white">
                    <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF Logo" class="h-10 mx-auto object-contain brightness-0 invert">
                    <p class="text-[9px] font-mono opacity-80 uppercase tracking-widest mt-1">Official Participant Badge</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="w-24 h-24 mx-auto rounded-full bg-slate-100 border-4 border-[#f3bd2e]/30 flex items-center justify-center text-4xl shadow-inner">
                        👤
                    </div>
                    <div>
                        <h4 class="font-sora font-black text-lg text-slate-900">{{ $sampleStudent?->name ?? 'Muhammed Nihal' }}</h4>
                        <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-100 text-amber-900">
                            {{ $sampleStudent?->student_id ?? 'QUAF-ST-1001' }}
                        </span>
                    </div>
                    <div class="border-t border-b border-slate-100 py-2 text-xs font-mono space-y-1 text-slate-600">
                        <div>Team: <strong class="text-slate-900">{{ $sampleStudent?->group?->name ?? 'Cordoba' }}</strong></div>
                        <div>Zone: <strong class="text-slate-900">{{ $sampleStudent?->category ?? 'A Zone' }}</strong></div>
                    </div>
                    <div class="w-24 h-24 mx-auto bg-slate-100 rounded-xl border border-slate-200 flex items-center justify-center text-[10px] font-mono text-slate-400">
                        [QR CODE]
                    </div>
                </div>
            </div>

        @else
            <!-- General Pass Preview -->
            <div class="w-[320px] bg-white border-2 border-slate-300 rounded-2xl overflow-hidden shadow-xl text-center">
                <div class="bg-slate-900 p-4 text-white">
                    <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF Logo" class="h-10 mx-auto object-contain brightness-0 invert">
                    <p class="text-[9px] font-mono opacity-80 uppercase tracking-widest mt-1">Official Accreditation Pass</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="w-20 h-20 mx-auto rounded-2xl bg-amber-50 border-2 border-amber-300 flex items-center justify-center text-3xl">
                        ⚖️
                    </div>
                    <div>
                        <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-blue-50 text-purple-900 uppercase">
                            {{ str_replace('-', ' ', $type) }}
                        </span>
                        <h4 class="font-sora font-black text-lg text-slate-900 mt-2">Dr. Abdul Kareem</h4>
                        <span class="text-xs font-mono text-slate-500">All Stages & Green Rooms</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl text-xs font-mono text-slate-600 border border-slate-200">
                        Holder is authorized for official jury evaluation and back-stage entry.
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
