<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch ID Badges Print (60mm × 100mm) | QUAF</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        @font-face {
            font-family: 'Rockwell Std';
            src: url('{{ asset('fonts/ROCK.TTF') }}') format('truetype'),
                 local('Rockwell Std'), local('Rockwell');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'Rockwell Std';
            src: url('{{ asset('fonts/ROCK.TTF') }}') format('truetype'),
                 local('Rockwell Std Bold'), local('Rockwell Bold');
            font-weight: 700;
            font-style: normal;
        }
        @font-face {
            font-family: 'Rockwell Std';
            src: url('{{ asset('fonts/ROCK.TTF') }}') format('truetype'),
                 local('Rockwell Std Extra Bold'), local('Rockwell Extra Bold');
            font-weight: 800;
            font-style: normal;
        }
        @font-face {
            font-family: 'Rockwell';
            src: url('{{ asset('fonts/ROCK.TTF') }}') format('truetype'),
                 local('Rockwell');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'Rockwell';
            src: url('{{ asset('fonts/ROCK.TTF') }}') format('truetype'),
                 local('Rockwell Bold');
            font-weight: 700;
            font-style: normal;
        }
        @font-face {
            font-family: 'Rockwell';
            src: url('{{ asset('fonts/ROCK.TTF') }}') format('truetype'),
                 local('Rockwell Extra Bold');
            font-weight: 800;
            font-style: normal;
        }
        .font-rockwell {
            font-family: 'Rockwell Std', 'Rockwell', serif !important;
        }

        @page {
            size: A3 portrait;
            margin: 10mm;
        }

        @media print {
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-grid {
                display: grid !important;
                grid-template-columns: repeat(4, 60mm) !important;
                gap: 0 !important;
                justify-content: center !important;
            }
            .badge-card {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
                border: 0.25pt solid #d0d0d0 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sora p-6 min-h-screen">

    <!-- Action Bar -->
    <div class="no-print max-w-7xl mx-auto mb-6 p-4 bg-white border border-slate-200 rounded-2xl flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.templates.index') }}" class="text-xs font-mono text-slate-500 hover:text-slate-800">
                    ← Templates Customizer
                </a>
                <span class="text-xs text-slate-300">•</span>
                <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-mono text-[10px] font-bold">
                    Official 60mm × 100mm Badge Master
                </span>
            </div>
            <h1 class="font-sora font-bold text-lg text-slate-900 mt-1">
                Batch ID Badges Print ({{ $students->count() }} Cards Loaded)
            </h1>
            <p class="text-xs font-mono text-slate-500">
                Vector-rendered with official team SVG templates, dynamic QR codes, and Rockwell Std typography.
            </p>
        </div>

        <!-- Filter & Print Controls -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Team Filter -->
            <form method="GET" action="{{ route('admin.idcards.print') }}" class="flex items-center gap-1.5">
                <select name="group" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold focus:outline-none focus:border-slate-400">
                    <option value="">All Teams ({{ \App\Models\Student::count() }})</option>
                    @foreach($groups as $grp)
                        <option value="{{ $grp->id }}" {{ $groupId == $grp->id ? 'selected' : '' }}>
                            {{ $grp->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="{{ asset('downloads/badges/QUAF_Badges_ALL_TEAMS_A3.pdf') }}" download
               class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-xs rounded-xl flex items-center gap-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>A3 PDF Pack</span>
            </a>

            <button onclick="window.print()" class="px-5 py-2 bg-[#be1e2d] text-white font-mono font-bold text-xs uppercase rounded-xl hover:bg-[#a01624] flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Badges Now</span>
            </button>
        </div>
    </div>

    <!-- Badges Grid Container (4 Columns for A3, centered) -->
    <div class="print-grid max-w-7xl mx-auto flex flex-wrap justify-center gap-4">
        @foreach($students as $student)
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
                $zoneText = strtoupper($student->zone_name ?? 'A Zone');
                $classText = strtoupper($student->class_level ?? '');
                $zoneClassDisplay = $classText ? ($zoneText . ' • ' . $classText) : $zoneText;
            @endphp

            <div class="badge-card relative overflow-hidden bg-white shadow-md rounded-lg"
                 style="width: 60mm; height: 100mm; flex-shrink: 0;">
                <!-- Vector SVG Template Background -->
                <img src="{{ asset('images/badges/' . $badgeSvg) }}"
                     alt="{{ $displayName }}"
                     style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: fill; z-index: 0; pointer-events: none;">

                <!-- 1. QR Code inside White Square Box (Centered directly below "Scan It, Know It" at Y ~ 45.6%) -->
                <div style="position: absolute; top: 45.6%; left: 50%; transform: translateX(-50%); width: 25.2mm; height: 25.2mm; background: #ffffff; border-radius: 6px; box-shadow: none; display: flex; align-items: center; justify-content: center; z-index: 10;">
                    <img src="{{ $qrUrl }}"
                         alt="QR Code"
                         style="width: 23mm; height: 23mm; object-fit: contain; display: block;">
                </div>

                <!-- 2. QF Number in RED BUTTON (Clean no white stroke, no shadow, centered at Y ~ 72.4%) -->
                <div style="position: absolute; top: 72.4%; left: 50%; transform: translateX(-50%); z-index: 10; width: max-content;">
                    <div class="font-rockwell" style="background: #be1e2d; color: #ffffff; font-weight: 800; font-size: 11pt; letter-spacing: 0.8px; text-transform: uppercase; padding: 1.2mm 5mm; border-radius: 9999px; box-shadow: none; border: none; line-height: 1; display: inline-flex; align-items: center; justify-content: center;">
                        {{ $chest }}
                    </div>
                </div>

                <!-- 3. Student Full Name (Rockwell Std bold, compact line distance, centered at Y ~ 79.2%) -->
                <div style="position: absolute; top: 79.2%; left: 2.5mm; right: 2.5mm; text-align: center; z-index: 10;">
                    <div class="font-rockwell" style="color: {{ $textColor }}; font-size: {{ $nameLen > 24 ? '8.5pt' : ($nameLen > 18 ? '9.5pt' : '10.5pt') }}; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.1; padding: 0 0.5mm;">
                        {{ $student->name }}
                    </div>
                </div>

                <!-- 4. Group inside Pill Button (Clean no stroke, no shadow, centered at Y ~ 82.6%) -->
                <div style="position: absolute; top: 82.6%; left: 50%; transform: translateX(-50%); z-index: 10; width: max-content;">
                    <div class="font-rockwell" style="background: {{ $isConco ? 'rgba(0,0,0,0.12)' : 'rgba(255,255,255,0.22)' }}; border: none; box-shadow: none; color: {{ $textColor }}; font-size: 8.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; padding: 0.8mm 3.5mm; border-radius: 9999px; line-height: 1; white-space: nowrap; display: inline-flex; align-items: center; justify-content: center;">
                        {{ $displayName }}
                    </div>
                </div>

                <!-- 5. Zone & Class / Category (Prominent Zone display, centered at Y ~ 88.2%) -->
                <div style="position: absolute; top: 88.2%; left: 2.5mm; right: 2.5mm; text-align: center; z-index: 10;">
                    <div class="font-rockwell" style="color: {{ $isConco ? '#1e293b' : 'rgba(255,255,255,0.92)' }}; font-size: 8.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; line-height: 1;">
                        {{ $zoneClassDisplay }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
