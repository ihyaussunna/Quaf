@extends('layouts.public', ['title' => $student->name . ' — Participant Verification | QUAF'])

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-8">
        <span class="text-xs font-mono font-bold tracking-widest text-[#f3bd2e] uppercase">PARTICIPANT IDENTIFICATION</span>
        <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 mt-2">Official Delegate Profile</h1>
    </div>

    <!-- Student 360 Card (Light Theme) -->
    <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-10 shadow-lg mb-12">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-8 pb-8 border-b border-slate-200">
            <div class="w-32 h-32 rounded-2xl overflow-hidden bg-slate-100 border-2 border-[#f3bd2e]/50 shadow-md flex-shrink-0 flex items-center justify-center">
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
                    <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-amber-50 text-[#be1e2d] border border-amber-200">
                        {{ $student->student_id }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold" style="background-color: {{ $student->group->color_hex }}20; color: {{ $student->group->color_hex }}">
                        Group: {{ $student->group->name }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded text-xs font-mono bg-slate-100 text-slate-700">
                        {{ $student->category }}
                    </span>
                </div>
                <h2 class="text-3xl font-sora font-black text-slate-900 mb-2">{{ $student->name }}</h2>
                <div class="text-xs font-mono text-slate-500 space-y-1">
                    <p>Class: {{ $student->class_level ?? 'Registered Student' }}</p>
                    <p>Conclave Standing: <span class="text-[#f3bd2e] font-bold">{{ $student->points_cache }} Points Contributed</span></p>
                </div>
            </div>
        </div>

        <!-- Registered Programs & Stage Status -->
        <div class="mt-8">
            <h3 class="font-sora font-bold text-lg text-slate-900 mb-4">Program Entries</h3>
            <div class="space-y-3">
                @forelse($student->entries as $entry)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 text-xs font-mono text-[#f3bd2e] mb-1 font-semibold">
                                <span class="font-bold">{{ $entry->program->code }}</span>
                                <span>•</span>
                                <span>Chest #{{ $entry->chest_number }}</span>
                            </div>
                            <h4 class="font-sora font-bold text-slate-900 text-base">{{ $entry->program->name }}</h4>
                            <span class="text-xs text-slate-500">{{ $entry->program->stage?->name ?? 'Stage TBD' }}</span>
                        </div>
                        <div class="text-right">
                            @if($entry->status === 'verified')
                                <span class="px-2.5 py-1 rounded text-xs font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">VERIFIED</span>
                            @else
                                <span class="px-2.5 py-1 rounded text-xs font-mono font-bold bg-amber-50 text-amber-700 border border-amber-200">PENDING</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 font-mono text-xs">No programs currently assigned.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
