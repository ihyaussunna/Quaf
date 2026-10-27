<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contestant Chest Number Slips | FestPro | QUAF</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
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

        @page {
            size: auto;
            margin: 8mm;
        }

        @media print {
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .slips-grid {
                display: flex !important;
                flex-wrap: wrap !important;
                justify-content: center !important;
                gap: 4mm !important;
            }
            .slip-card {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
                border: 0.25pt dashed #999999 !important;
                box-shadow: none !important;
                margin: 2mm !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sora p-6 min-h-screen">

    <!-- Action & Filter Bar (Hidden on Print) -->
    <div class="no-print max-w-7xl mx-auto mb-6 p-4 bg-white border border-slate-200 rounded-2xl shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-amber-100 text-[#be1e2d] border border-amber-200">
                        Official Production Template
                    </span>
                    <span class="text-xs font-mono text-slate-500">• {{ $students->count() }} Contestant Slips</span>
                </div>
                <h1 class="font-sora font-black text-xl text-slate-900 mt-1">Contestant Chest Number Slips</h1>
                <p class="text-xs font-mono text-slate-500">Official 60mm × 100mm badge templates with dynamic QR verification, chest pill, and Rockwell Std typography.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ asset('downloads/badges/QUAF_Badges_ALL_TEAMS_A3.pdf') }}" download
                   class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-xs rounded-xl flex items-center gap-1.5 shadow-xs transition">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Master A3 PDF</span>
                </a>
                <button onclick="window.print()" class="px-5 py-2.5 bg-[#be1e2d] text-white font-mono font-bold text-xs uppercase rounded-xl hover:bg-[#a01624] flex items-center gap-2 shadow-lg shadow-[#be1e2d]/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Print All Slips</span>
                </button>
                <button onclick="window.close()" class="px-4 py-2.5 bg-slate-100 text-slate-700 font-mono text-xs rounded-xl hover:bg-slate-200 font-semibold transition">
                    Close
                </button>
            </div>
        </div>

        <!-- Filter form -->
        <form method="GET" action="{{ route('admin.idcards.chest-slips') }}" class="flex flex-wrap items-center gap-3 pt-3 border-t border-slate-100 text-xs font-mono">
            <span class="text-slate-500 font-semibold">Filter:</span>
            <select name="group" class="bg-slate-50 border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-[#be1e2d]">
                <option value="">All Groups</option>
                @foreach($groups as $g)
                    <option value="{{ $g->id }}" {{ $groupId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                @endforeach
            </select>
            <select name="category" class="bg-slate-50 border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-[#be1e2d]">
                <option value="">All Zones</option>
                @foreach($zones as $val => $label)
                    <option value="{{ $val }}" {{ $category == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-3.5 py-1.5 bg-slate-800 text-white rounded-lg hover:bg-slate-900 font-semibold transition">Apply</button>
            <a href="{{ route('admin.idcards.chest-slips') }}" class="text-slate-500 hover:text-slate-800 underline">Reset</a>
        </form>
    </div>

    <!-- Slips Container (Rendered with the Official 5-Team Badge Templates) -->
    <div class="slips-grid max-w-7xl mx-auto flex flex-wrap justify-center gap-4">
        @forelse($students as $student)
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

            <div class="slip-card relative overflow-hidden bg-white shadow-md rounded-lg border border-dashed border-slate-300"
                 style="width: 60mm; height: 100mm; flex-shrink: 0;">
                
                <!-- Cut-line Guideline indicator (Screen Only) -->
                <div class="no-print absolute top-1 right-1.5 text-[7px] font-mono text-slate-400 z-20">✂ Cut</div>

                <!-- Hidden span for group name verification and automated testing -->
                <span class="sr-only">{{ $displayName }}</span>

                <!-- Vector SVG Template Background -->
                <img src="{{ asset('images/badges/' . $badgeSvg) }}"
                     alt="{{ $displayName }}"
                     style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: fill; z-index: 0; pointer-events: none;">

                <!-- 1. QR Code inside White Square Box (Centered directly below "Scan It, Know It" at Y ~ 46.2%) -->
                <div style="position: absolute; top: 46.2%; left: 50%; transform: translateX(-50%); width: 25mm; height: 25mm; background: #ffffff; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.22); display: flex; align-items: center; justify-content: center; z-index: 10;">
                    <img src="{{ $qrUrl }}"
                         alt="QR Code"
                         style="width: 22.8mm; height: 22.8mm; object-fit: contain; display: block;">
                </div>

                <!-- 2. QF Number inside Pill Button (Centered at Y ~ 73.0%) -->
                <div style="position: absolute; top: 73.0%; left: 50%; transform: translateX(-50%); z-index: 10; width: max-content;">
                    <div class="font-rockwell" style="background: {{ $qfBg }}; color: {{ $qfColor }}; font-weight: 800; font-size: 10pt; letter-spacing: 0.6px; text-transform: uppercase; padding: 1.5mm 5.2mm; border-radius: 9999px; box-shadow: 0 2px 6px rgba(0,0,0,0.28); line-height: 1; display: inline-flex; align-items: center; justify-content: center;">
                        {{ $chest }}
                    </div>
                </div>

                <!-- 3. Student Full Name (Rockwell bold, centered at Y ~ 80.8%) -->
                <div style="position: absolute; top: 80.8%; left: 2.5mm; right: 2.5mm; text-align: center; z-index: 10;">
                    <div class="font-rockwell" style="color: {{ $textColor }}; font-size: {{ $nameLen > 24 ? '8.5pt' : ($nameLen > 18 ? '9pt' : '10pt') }}; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.15; padding: 0 0.5mm;">
                        {{ $student->name }}
                    </div>
                </div>

                <!-- 4. Group inside Pill Button (Centered at Y ~ 87.0%) -->
                <div style="position: absolute; top: 87.0%; left: 50%; transform: translateX(-50%); z-index: 10; width: max-content;">
                    <div class="font-rockwell" style="background: {{ $isConco ? 'rgba(0,0,0,0.10)' : 'rgba(255,255,255,0.20)' }}; border: 0.8px solid {{ $isConco ? 'rgba(0,0,0,0.25)' : 'rgba(255,255,255,0.45)' }}; color: {{ $textColor }}; font-size: 7pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; padding: 1.2mm 4.2mm; border-radius: 9999px; line-height: 1; white-space: nowrap; display: inline-flex; align-items: center; justify-content: center;">
                        {{ $displayName }}
                    </div>
                </div>

                <!-- 5. Class / Category (Centered at Y ~ 93.0%) -->
                <div style="position: absolute; top: 93.0%; left: 2.5mm; right: 2.5mm; text-align: center; z-index: 10;">
                    <div class="font-rockwell" style="color: {{ $isConco ? '#334155' : 'rgba(255,255,255,0.9)' }}; font-size: 7pt; font-weight: {{ $isConco ? '700' : '600' }}; text-transform: uppercase; letter-spacing: 0.8px; line-height: 1;">
                        {{ $classDisplay }}
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-slate-500 font-mono text-xs bg-white rounded-2xl border border-slate-200">
                No students found for chest number slips.
            </div>
        @endforelse
    </div>

</body>
</html>
