@extends('layouts.public', ['title' => 'Participant Verification — QUAF 9.0'])

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
    <div class="text-center mb-8">
        <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">PARTICIPANT IDENTIFICATION</span>
        <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 mt-2">Official Delegate Profile</h1>
    </div>

    @if($isValid && $student)
        <!-- Valid Student Profile -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-10 shadow-lg mb-12">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-8 pb-8 border-b border-slate-200">
                <div class="w-32 h-32 rounded-2xl overflow-hidden bg-slate-100 border-2 border-slate-200 shadow-md shrink-0 flex items-center justify-center">
                    @if($student->photo_url)
                        <img src="{{ $student->photo_url }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center bg-slate-50 text-slate-400">
                            <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 mt-1 font-semibold">{{ $student->student_id }}</span>
                        </div>
                    @endif
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-red-50 text-[#be1e2d] border border-red-200">
                            {{ $student->student_id }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold" style="background-color: {{ $student->group?->color_hex ?? '#2e3192' }}20; color: {{ $student->group?->color_hex ?? '#2e3192' }}">
                            House: {{ $student->group?->name }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded text-xs font-mono bg-slate-100 text-slate-700">
                            {{ $student->category }}
                        </span>
                    </div>
                    <h2 class="text-3xl font-sora font-black text-slate-900 mb-2">{{ $student->name }}</h2>
                    <div class="text-xs font-mono text-slate-500 space-y-1">
                        <p>Class: {{ $student->class_level ?? 'Registered Student' }}</p>
                        <p>Festival Contribution: <span class="text-[#be1e2d] font-bold">{{ $student->points_cache }} Points</span></p>
                    </div>
                </div>
            </div>

            <!-- Registered Programs & Stage Status -->
            <div class="mt-8">
                <h3 class="font-sora font-bold text-lg text-slate-900 mb-4">Allocated Program Registrations</h3>
                <div class="space-y-3">
                    @forelse($student->entries as $entry)
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 text-xs font-mono text-[#be1e2d] mb-1 font-semibold">
                                    <span class="font-bold">{{ $entry->program?->code }}</span>
                                    <span>•</span>
                                    <span>Chest #{{ $entry->chest_number }}</span>
                                </div>
                                <h4 class="font-sora font-bold text-slate-900 text-base">{{ $entry->program?->name }}</h4>
                                <span class="text-xs text-slate-500 font-mono">{{ $entry->program?->stage?->name ?? 'Stage Schedule Pending' }}</span>
                            </div>
                            <div class="text-right">
                                @if($entry->status === 'verified')
                                    <span class="px-2.5 py-1 rounded text-xs font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">VERIFIED ENTRY</span>
                                @else
                                    <span class="px-2.5 py-1 rounded text-xs font-mono font-bold bg-amber-50 text-amber-700 border border-amber-200">CONFIRMED</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 font-mono text-xs">No programs currently registered for this candidate.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        <!-- Invalid Student Delegate State -->
        <div class="rounded-3xl bg-white border border-red-200 p-8 sm:p-12 text-center shadow-lg max-w-lg mx-auto space-y-4">
            <div class="w-14 h-14 rounded-full bg-red-50 text-red-600 flex items-center justify-center mx-auto mb-2 border border-red-200">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-red-100 text-red-800 uppercase inline-block">
                INVALID DELEGATE PASS
            </span>
            <h2 class="text-2xl font-sora font-black text-slate-900">Student Not Found</h2>
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                The QR identifier <strong class="font-mono text-slate-900">{{ $qrToken }}</strong> could not be matched with any accredited delegate profile.
            </p>
            <div class="pt-4">
                <a href="{{ route('verify.index') }}" class="inline-block px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition-colors">
                    Back to Verification Hub
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
