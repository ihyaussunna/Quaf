@extends('layouts.admin', ['title' => 'Badge: ' . $student->name])

@section('content')
<style>
    @font-face {
        font-family: 'Rockwell Std';
        src: local('Rockwell Std Extra Bold'), local('Rockwell Extra Bold'),
             url('{{ asset('fonts/Rockwell-Extra-Bold.otf') }}') format('opentype'),
             url('{{ asset('fonts/Rockwell-Extra-Bold.woff2') }}') format('woff2');
        font-weight: 800;
        font-style: normal;
    }
    @font-face {
        font-family: 'Rockwell Std';
        src: local('Rockwell Std Bold'), local('Rockwell Bold'),
             url('{{ asset('fonts/Rockwell-Bold.otf') }}') format('opentype'),
             url('{{ asset('fonts/Rockwell-Bold.woff2') }}') format('woff2');
        font-weight: bold;
        font-style: normal;
    }
    @font-face {
        font-family: 'Rockwell Std';
        src: local('Rockwell Std'), local('Rockwell'),
             url('{{ asset('fonts/Rockwell-Regular.otf') }}') format('opentype'),
             url('{{ asset('fonts/Rockwell-Regular.woff2') }}') format('woff2');
        font-weight: normal;
        font-style: normal;
    }
    .font-rockwell {
        font-family: 'Rockwell Std', 'Rockwell', 'Rockwell Nova', serif !important;
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
            font-size: 11.5pt !important;
            padding: 1.6mm 6mm !important;
        }
        #badge-single-card .student-name {
            font-size: {{ strlen($student->name) > 24 ? '8.5pt' : (strlen($student->name) > 18 ? '9pt' : '10pt') }} !important;
        }
        #badge-single-card .group-pill {
            font-size: 7.2pt !important;
            padding: 1mm 4mm !important;
        }
        #badge-single-card .zone-text {
            font-size: 7.2pt !important;
        }
    }
</style>

@php
    $groupCode = strtoupper($student->group?->code ?? '');
    $groupName = strtoupper($student->group?->name ?? '');

    if ($groupCode === 'CONCO' || str_contains($groupName, 'CONCO')) {
        $badgeSvg = 'conco-badge.svg';
        $isConco = true;
    } elseif ($groupCode === 'PACTO' || str_contains($groupName, 'PACTO')) {
        $badgeSvg = 'pacto-badge.svg';
        $isConco = false;
    } elseif ($groupCode === 'UNIO' || str_contains($groupName, 'UNIO')) {
        $badgeSvg = 'unio-badge.svg';
        $isConco = false;
    } elseif ($groupCode === 'YUGO' || str_contains($groupName, 'YUGO')) {
        $badgeSvg = 'yugo-badge.svg';
        $isConco = false;
    } else {
        $badgeSvg = 'lumo-badge.svg';
        $isConco = false;
    }

    $chest = $student->chest_number ?? $student->student_id;
    $textColor = $isConco ? '#0f172a' : '#ffffff';
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&margin=0&data=" . urlencode(route('verify.student', $student->qr_token ?? $chest));
    $displayName = $student->group?->name ?? 'General';
    $nameLen = strlen($student->name);
    $zoneText = strtoupper($student->zone_name ?? 'A Zone');
    $classText = strtoupper($student->class_level ?? '');
    $zoneClassDisplay = $classText ? ($zoneText . ' • ' . $classText) : $zoneText;
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

                    <!-- 1. QR Code inside White Square Box (Centered directly below "Scan It, Know It" at Y ~ 45.6%) -->
                    <div style="position: absolute; top: 45.6%; left: 50%; transform: translateX(-50%); width: 41.67%; aspect-ratio: 1 / 1; background: #ffffff; border-radius: 6px; box-shadow: none; display: flex; align-items: center; justify-content: center; z-index: 10;">
                        <img src="{{ $qrUrl }}"
                             alt="QR Code"
                             style="width: 90%; height: 90%; object-fit: contain; display: block;">
                    </div>

                    <!-- 2. QF Number in RED BUTTON (Clean no white stroke, no shadow, centered at Y ~ 72.4%) -->
                    <div style="position: absolute; top: 72.4%; left: 50%; transform: translateX(-50%); z-index: 10; width: max-content;">
                        <div class="font-rockwell qf-pill" style="background: #be1e2d; color: #ffffff; font-weight: 800; font-size: 15px; letter-spacing: 0.8px; text-transform: uppercase; padding: 6px 20px; border-radius: 9999px; box-shadow: none; border: none; line-height: 1; display: inline-flex; align-items: center; justify-content: center;">
                            {{ $chest }}
                        </div>
                    </div>

                    <!-- 3. Student Full Name (Rockwell bold, compact line distance, centered at Y ~ 80.4%) -->
                    <div style="position: absolute; top: 80.4%; left: 12px; right: 12px; text-align: center; z-index: 10;">
                        <div class="font-rockwell student-name" style="color: {{ $textColor }}; font-size: {{ $nameLen > 24 ? '11.5px' : ($nameLen > 18 ? '13px' : '14px') }}; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.1; padding: 0 2px;">
                            {{ $student->name }}
                        </div>
                    </div>

                    <!-- 4. Group inside Pill Button (Clean no stroke, no shadow, centered at Y ~ 86.4%) -->
                    <div style="position: absolute; top: 86.4%; left: 50%; transform: translateX(-50%); z-index: 10; width: max-content;">
                        <div class="font-rockwell group-pill" style="background: {{ $isConco ? 'rgba(0,0,0,0.12)' : 'rgba(255,255,255,0.22)' }}; border: none; box-shadow: none; color: {{ $textColor }}; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; padding: 4px 14px; border-radius: 9999px; line-height: 1; white-space: nowrap; display: inline-flex; align-items: center; justify-content: center;">
                            {{ $displayName }}
                        </div>
                    </div>

                    <!-- 5. Zone & Class / Category (Prominent Zone display, centered at Y ~ 92.0%) -->
                    <div style="position: absolute; top: 92.0%; left: 12px; right: 12px; text-align: center; z-index: 10;">
                        <div class="font-rockwell zone-text" style="color: {{ $isConco ? '#1e293b' : 'rgba(255,255,255,0.92)' }}; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; line-height: 1;">
                            {{ $zoneClassDisplay }}
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
