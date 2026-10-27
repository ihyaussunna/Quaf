@extends('layouts.admin', ['title' => 'Badge: ' . $student->name])

@section('content')
<style>
    @font-face {
        font-family: 'Rockwell Std';
        src: url('{{ asset('fonts/Rockwell-Bold.otf') }}') format('opentype');
        font-weight: bold;
        font-style: normal;
    }
    @font-face {
        font-family: 'Rockwell Std';
        src: url('{{ asset('fonts/Rockwell-Regular.otf') }}') format('opentype');
        font-weight: normal;
        font-style: normal;
    }
    .font-rockwell {
        font-family: 'Rockwell Std', 'Rockwell', 'Rockwell Nova', 'DejaVu Serif', Georgia, serif;
    }

    @media print {
        @page {
            size: 60mm 100mm;
            margin: 0;
        }
        body {
            background: white !important;
            color: black !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        aside, header, nav, .no-print, .print\:hidden {
            display: none !important;
        }
        #badge-single-print-wrapper {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 60mm !important;
            height: 100mm !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            background: white !important;
        }
        #badge-single-card {
            width: 60mm !important;
            height: 100mm !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }
        #badge-single-card .qf-pill {
            font-size: 10pt !important;
            padding: 1.5mm 5.2mm !important;
        }
        #badge-single-card .student-name {
            font-size: {{ strlen($student->name) > 24 ? '8.5pt' : (strlen($student->name) > 18 ? '9pt' : '10pt') }} !important;
        }
        #badge-single-card .group-pill {
            font-size: 7pt !important;
            padding: 1.2mm 4.2mm !important;
        }
        #badge-single-card .category-text {
            font-size: 7pt !important;
        }
    }
</style>

@php
    $groupCode = strtoupper($student->group?->code ?? '');
    $groupName = strtoupper($student->group?->name ?? '');

    if ($groupCode === 'CONCO' || str_contains($groupName, 'CONCO')) {
        $badgeSvg = 'conco-badge.svg';
        $isConco = true;
        $teamColor = '#be1e2d';
        $qfBg = '#be1e2d';
        $qfColor = '#ffffff';
    } elseif ($groupCode === 'PACTO' || str_contains($groupName, 'PACTO')) {
        $badgeSvg = 'pacto-badge.svg';
        $isConco = false;
        $teamColor = '#2e3192';
        $qfBg = '#ffffff';
        $qfColor = '#2e3192';
    } elseif ($groupCode === 'UNIO' || str_contains($groupName, 'UNIO')) {
        $badgeSvg = 'unio-badge.svg';
        $isConco = false;
        $teamColor = '#7f1518';
        $qfBg = '#ffffff';
        $qfColor = '#7f1518';
    } elseif ($groupCode === 'YUGO' || str_contains($groupName, 'YUGO')) {
        $badgeSvg = 'yugo-badge.svg';
        $isConco = false;
        $teamColor = '#ad1e56';
        $qfBg = '#ffffff';
        $qfColor = '#ad1e56';
    } else {
        $badgeSvg = 'lumo-badge.svg';
        $isConco = false;
        $teamColor = '#56286b';
        $qfBg = '#ffffff';
        $qfColor = '#56286b';
    }

    $chest = $student->chest_number ?? $student->student_id;
    $textColor = $isConco ? '#0f172a' : '#ffffff';
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&margin=0&data=" . urlencode(route('verify.student', $student->qr_token ?? $chest));
    $displayName = $student->group?->name ?? 'General';
    $nameLen = strlen($student->name);
    $classDisplay = trim(($student->category ?? '') . ($student->class_level ? ' • ' . $student->class_level : ''));
@endphp

<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Top Action Bar -->
    <div class="print:hidden no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-slate-200 p-4 rounded-2xl shadow-2xs">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.idcards.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-mono font-bold transition">
                &larr; Back to ID Badges
            </a>
            <span class="text-slate-300">•</span>
            <span class="text-xs font-mono text-slate-500">
                Official 60mm × 100mm Badge Template
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.idcards.chest-slips', ['group' => $student->group_id]) }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-mono font-bold transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Chest Slips</span>
            </a>
            <a href="{{ route('admin.idcards.print', ['group' => $student->group_id]) }}" target="_blank" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-mono font-bold transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Batch Print Team</span>
            </a>
            <button onclick="window.print()" class="px-5 py-2 bg-[#be1e2d] text-white font-mono font-bold text-xs uppercase rounded-xl hover:bg-[#a01624] flex items-center gap-2 shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Single Badge</span>
            </button>
        </div>
    </div>

    <!-- Badge Display Grid (Badge on left, Metadata details on right) -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
        
        <!-- Left: The Official 60mm × 100mm Badge Template (Scale up slightly for screen visibility) -->
        <div class="md:col-span-5 flex justify-center">
            <div id="badge-single-print-wrapper" class="p-3 bg-slate-200/60 rounded-3xl shadow-inner inline-block">
                <div id="badge-single-card"
                     class="badge-card relative overflow-hidden bg-white shadow-xl rounded-2xl"
                     style="width: 330px; height: 550px;">
                    
                    <!-- Official Vector SVG Template Background -->
                    <img src="{{ asset('images/badges/' . $badgeSvg) }}"
                         alt="{{ $displayName }}"
                         style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: fill; z-index: 0; pointer-events: none;">

                    <!-- 1. QR Code inside White Square Box (Centered directly below "Scan It, Know It" at Y ~ 46.2%) -->
                    <div style="position: absolute; top: 46.2%; left: 50%; transform: translateX(-50%); width: 41.67%; aspect-ratio: 1 / 1; background: #ffffff; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.22); display: flex; align-items: center; justify-content: center; z-index: 10;">
                        <img src="{{ $qrUrl }}"
                             alt="QR Code"
                             style="width: 90%; height: 90%; object-fit: contain; display: block;">
                    </div>

                    <!-- 2. QF Number inside Pill Button (Centered at Y ~ 73.0%) -->
                    <div style="position: absolute; top: 73.0%; left: 50%; transform: translateX(-50%); z-index: 10; width: max-content;">
                        <div class="font-rockwell qf-pill" style="background: {{ $qfBg }}; color: {{ $qfColor }}; font-weight: 800; font-size: 14px; letter-spacing: 0.6px; text-transform: uppercase; padding: 6px 18px; border-radius: 9999px; box-shadow: 0 2px 6px rgba(0,0,0,0.28); line-height: 1; display: inline-flex; align-items: center; justify-content: center;">
                            {{ $chest }}
                        </div>
                    </div>

                    <!-- 3. Student Full Name (Rockwell bold, centered at Y ~ 80.8%) -->
                    <div style="position: absolute; top: 80.8%; left: 12px; right: 12px; text-align: center; z-index: 10;">
                        <div class="font-rockwell student-name" style="color: {{ $textColor }}; font-size: {{ $nameLen > 24 ? '11.5px' : ($nameLen > 18 ? '13px' : '14px') }}; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.15; padding: 0 2px;">
                            {{ $student->name }}
                        </div>
                    </div>

                    <!-- 4. Group inside Pill Button (Centered at Y ~ 87.0%) -->
                    <div style="position: absolute; top: 87.0%; left: 50%; transform: translateX(-50%); z-index: 10; width: max-content;">
                        <div class="font-rockwell group-pill" style="background: {{ $isConco ? 'rgba(0,0,0,0.10)' : 'rgba(255,255,255,0.20)' }}; border: 0.8px solid {{ $isConco ? 'rgba(0,0,0,0.25)' : 'rgba(255,255,255,0.45)' }}; color: {{ $textColor }}; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; padding: 4px 14px; border-radius: 9999px; line-height: 1; white-space: nowrap; display: inline-flex; align-items: center; justify-content: center;">
                            {{ $displayName }}
                        </div>
                    </div>

                    <!-- 5. Class / Category (Centered at Y ~ 93.0%) -->
                    <div style="position: absolute; top: 93.0%; left: 12px; right: 12px; text-align: center; z-index: 10;">
                        <div class="font-rockwell category-text" style="color: {{ $isConco ? '#334155' : 'rgba(255,255,255,0.9)' }}; font-size: 10px; font-weight: {{ $isConco ? '700' : '600' }}; text-transform: uppercase; letter-spacing: 0.8px; line-height: 1;">
                            {{ $classDisplay }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Student Metadata & Program Entries -->
        <div class="md:col-span-7 space-y-5 print:hidden no-print">
            
            <!-- Participant Quick Info Card -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <span class="text-[10px] font-mono uppercase tracking-widest text-slate-400 font-bold">
                            Official Participant Credential
                        </span>
                        <h2 class="text-xl font-bold font-sora text-slate-900 mt-0.5">
                            {{ $student->name }}
                        </h2>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-mono font-bold uppercase shadow-2xs"
                          style="background-color: {{ $student->group?->color_hex ?? '#005c94' }}18; color: {{ $student->group?->color_hex ?? '#005c94' }}; border: 1px solid {{ $student->group?->color_hex ?? '#005c94' }}30;">
                        {{ $student->group?->name ?? 'General' }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4 text-xs font-mono">
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Chest Number</span>
                        <span class="text-base font-black text-slate-900 font-sora">{{ $chest }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Student ID</span>
                        <span class="text-base font-black text-slate-900 font-sora">{{ $student->student_id }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Zone / Category</span>
                        <span class="text-sm font-bold text-slate-800">{{ $student->category }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Admission No.</span>
                        <span class="text-sm font-bold text-slate-800">{{ $student->admission_number ?? '---' }}</span>
                    </div>
                </div>

                <!-- Verification URL link -->
                <div class="p-3.5 rounded-2xl bg-emerald-50/60 border border-emerald-200/80 text-xs font-mono flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <span class="text-[10px] font-bold text-emerald-800 uppercase block">Verification Link:</span>
                        <span class="text-emerald-700 truncate block text-[11px]">{{ route('verify.student', $student->qr_token ?? $chest) }}</span>
                    </div>
                    <a href="{{ route('verify.student', $student->qr_token ?? $chest) }}" target="_blank"
                       class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shrink-0 transition shadow-2xs">
                        Open &rarr;
                    </a>
                </div>
            </div>

            <!-- Program Entries Card -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-2xs space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold font-sora text-slate-900 uppercase tracking-wide">
                        Registered Program Entries ({{ $student->entries->count() }})
                    </h3>
                    <span class="text-xs font-mono text-slate-400">Green Room Check-In</span>
                </div>

                @if($student->entries->isNotEmpty())
                    <div class="divide-y divide-slate-100 text-xs">
                        @foreach($student->entries as $entry)
                            <div class="py-2.5 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 font-sora truncate">
                                        {{ $entry->program?->name ?? '---' }}
                                    </p>
                                    <p class="text-[10px] font-mono text-slate-400">
                                        Code: {{ $entry->program?->code ?? '---' }} • Category: {{ $entry->program?->category ?? '---' }}
                                    </p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold shrink-0
                                      {{ $entry->status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucfirst($entry->status ?? 'registered') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 font-mono py-2">No registered program entries for this contestant.</p>
                @endif
            </div>

        </div>

    </div>
</div>
@endsection
