@extends('layouts.student', ['title' => 'My Digital QR Code'])

@section('content')
<div class="space-y-6 max-w-xl mx-auto">
    <!-- Action Bar (Without Print Button) -->
    <div class="flex items-center justify-between gap-4 bg-white border border-slate-200/90 p-4 rounded-2xl shadow-sm">
        <a href="{{ route('student.dashboard') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-mono font-bold transition">
            <span>&larr;</span>
            <span>Dashboard</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="px-3.5 py-2 bg-slate-100 hover:bg-red-50 hover:text-[#be1e2d] text-slate-700 rounded-xl text-xs font-mono font-bold border border-slate-200 cursor-pointer transition">
                Sign Out
            </button>
        </form>
    </div>

    <!-- Official Student Details & QR Code View -->
    <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
        <!-- Student Info Header -->
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left border-b border-slate-100 pb-6">
            <!-- Avatar / Photo -->
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-red-50 to-slate-100 border-2 border-red-200/80 flex items-center justify-center font-sora text-3xl font-black text-[#be1e2d] overflow-hidden shadow-sm shrink-0">
                @if($student->photo_path)
                    <img src="{{ asset('storage/' . $student->photo_path) }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                @else
                    {{ substr($student->name, 0, 1) }}
                @endif
            </div>

            <!-- Name & Identifiers -->
            <div class="space-y-1.5 flex-1 min-w-0">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                    <span class="px-2.5 py-0.5 rounded-full bg-red-50 border border-red-200 text-[#be1e2d] font-mono font-bold text-xs uppercase">
                        Chest #{{ $student->chest_number ?? $student->student_id ?? '---' }}
                    </span>
                    @if($student->student_id && $student->student_id !== $student->chest_number)
                        <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 font-mono text-xs">
                            ID: {{ $student->student_id }}
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl font-sora font-black text-slate-900 tracking-tight">
                    {{ $student->name }}
                </h1>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs font-mono pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border border-slate-200 bg-slate-50 font-bold" style="color: {{ $student->group->color_hex }};">
                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $student->group->color_hex }};"></span>
                        <span>{{ $student->group->name }}</span>
                    </span>
                    <span class="text-slate-300">•</span>
                    <span class="text-slate-600 font-semibold">{{ $student->zone_name ?? $student->category }}</span>
                </div>
            </div>
        </div>

        <!-- High-Resolution QR Code Section -->
        <div class="flex flex-col items-center justify-center py-4 space-y-4 text-center">
            <div class="p-4 bg-white rounded-3xl border-2 border-slate-200/90 shadow-md inline-block">
                <div class="w-52 h-52 sm:w-60 sm:h-60 flex items-center justify-center">
                    {!! $qrCodeSvg !!}
                </div>
            </div>
            <div class="space-y-1 max-w-sm">
                <p class="text-xs font-mono font-bold text-slate-800 uppercase tracking-wider">
                    Official Student QR Code
                </p>
                <p class="text-xs text-slate-500 font-sora">
                    Present this QR code for verification at stages, green rooms, and presentation desk.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
