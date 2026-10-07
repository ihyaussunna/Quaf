@extends('layouts.public', ['title' => 'Submission Recorded Successfully | QUAF'])

@section('content')
<section class="min-h-[80vh] py-14 sm:py-20 bg-slate-50 flex items-center justify-center relative overflow-hidden">
    <!-- Ambient Glows -->
    <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[340px] sm:w-[500px] h-[340px] sm:h-[400px] bg-emerald-500/15 rounded-full blur-[140px] pointer-events-none"></div>

    <div class="max-w-lg mx-auto px-4 sm:px-6 w-full relative z-10 text-center">

        <!-- Success Receipt Card (Apple Glassy Style) -->
        <div class="bg-white/95 backdrop-blur-2xl rounded-3xl p-8 sm:p-12 border border-slate-200 shadow-2xl relative overflow-hidden flex flex-col items-center">
            
            <!-- Success Icon -->
            <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-5 shadow-lg shadow-emerald-500/20 ring-8 ring-emerald-50">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>

            <span class="px-3 py-1 rounded-full text-[11px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase tracking-wider mb-3">
                SUBMISSION RECORDED (രചന സമർപ്പിച്ചു)
            </span>

            <h1 class="font-sora text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-2">
                Successfully Submitted!
            </h1>

            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-6">
                നിങ്ങളുടെ രചന മത്സര മൂല്യനിർണ്ണയത്തിനായി വിജയകരമായി സിസ്റ്റത്തിൽ രേഖപ്പെടുത്തിയിരിക്കുന്നു.
            </p>

            <!-- Receipt Box -->
            <div class="w-full p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3 text-left font-mono text-xs mb-6">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
                    <span class="text-slate-500">Program:</span>
                    <strong class="text-slate-900 font-sans text-right">{{ $form->program?->name ?? $form->title }}</strong>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
                    <span class="text-slate-500">Program Code:</span>
                    <strong class="text-[#be1e2d]">{{ $form->program?->code }}</strong>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
                    <span class="text-slate-500">Your Code Letter:</span>
                    <span class="px-3 py-1 rounded-xl bg-slate-900 text-amber-300 font-sora font-black text-sm">
                        {{ $submission->code_letter }}
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Submission Time:</span>
                    <span class="text-slate-700 font-bold">{{ $submission->submitted_at?->format('d M Y, h:i:s A') }}</span>
                </div>
            </div>

            <div class="space-y-3 w-full">
                <a href="{{ route('home.view') }}" 
                   class="w-full py-3.5 px-6 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition">
                    <span>Back to Festival Home</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

        </div>

    </div>
</section>
@endsection
