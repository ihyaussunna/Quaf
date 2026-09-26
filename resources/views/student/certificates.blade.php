@extends('layouts.student', ['title' => 'My Certificates'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif font-black text-slate-900">My Digital Certificates</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">Official merit and participation credentials awarded during QUAF Season 09.</p>
        </div>
        <div>
            <span class="px-4 py-2 rounded-xl bg-white border border-slate-200 shadow-sm text-xs font-mono text-[#f3bd2e] font-bold">
                Total: {{ $certificates->count() }} Issued
            </span>
        </div>
    </div>

    <!-- Certificates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($certificates as $cert)
            <div class="bg-white border border-slate-200 rounded-3xl p-6 hover:border-[#f3bd2e]/40 transition-all flex flex-col justify-between space-y-6 shadow-sm hover:shadow-md">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold bg-amber-50 text-[#f3bd2e] border border-[#f3bd2e]/30 uppercase">
                            {{ $cert->program->category->name ?? 'General' }}
                        </span>
                        <span class="px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-mono text-xs font-bold">
                            {{ $cert->position }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-xl font-serif font-bold text-slate-900">{{ $cert->program->name }}</h3>
                        <p class="text-xs font-mono text-slate-500 mt-0.5">Certificate Serial: <strong class="text-[#f3bd2e]">{{ $cert->certificate_number }}</strong></p>
                    </div>

                    <div class="text-xs font-mono text-slate-500 pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span>Issued On: {{ $cert->issued_at?->format('M d, Y') ?? 'TBA' }}</span>
                        <span class="text-emerald-600 flex items-center gap-1 font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Verified
                        </span>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center gap-3">
                    <a href="{{ route('verify.certificate', $cert->certificate_number) }}" target="_blank"
                       class="flex-1 py-2.5 bg-[#f3bd2e] hover:brightness-110 text-white font-mono font-bold text-xs uppercase rounded-xl text-center shadow-lg shadow-[#f3bd2e]/20 transition-all">
                        View & Download Certificate &nearr;
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-slate-500 font-mono text-xs bg-white rounded-3xl border border-slate-200 shadow-sm">
                No certificates have been issued yet. Certificates are generated automatically when program results are approved and published by the festival desk.
            </div>
        @endforelse
    </div>
</div>
@endsection
