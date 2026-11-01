<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Judges Passwords & Schedule Access | QUAF 2026</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    @vite(['resources/css/app.css'])

    <style>
        @font-face {
            font-family: 'Rockwell Std';
            src: url('/fonts/Rockwell-Light.woff2') format('woff2'), url('/fonts/Rockwell-Light.otf') format('opentype'), local('Rockwell Light');
            font-weight: 300;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rockwell Std';
            src: url('/fonts/Rockwell-Regular.woff2') format('woff2'), url('/fonts/ROCK.TTF') format('truetype'), url('/fonts/Rockwell-Regular.otf') format('opentype'), local('Rockwell Std'), local('Rockwell');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rockwell Std';
            src: url('/fonts/Rockwell-Bold.woff2') format('woff2'), url('/fonts/Rockwell-Bold.otf') format('opentype'), local('Rockwell Bold');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Rockwell Std';
            src: url('/fonts/Rockwell-Extra-Bold.woff2') format('woff2'), url('/fonts/Rockwell-Extra-Bold.otf') format('opentype'), local('Rockwell Extra Bold');
            font-weight: 800;
            font-style: normal;
            font-display: swap;
        }

        body, table, th, td, h1, h2, h3, h4, div, span, p, a, button {
            font-family: 'Rockwell Std', 'Rockwell', Georgia, serif;
        }

        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .page-container {
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            table {
                page-break-inside: auto;
                width: 100% !important;
            }
            thead {
                display: table-header-group;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }

        .credential-table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        .credential-table th,
        .credential-table td {
            border: 1px solid #cbd5e1 !important;
            padding: 7px 8px !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .credential-table thead th {
            background-color: #0f172a !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 11px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen py-6 print:py-0">

    <!-- Floating Top Toolbar (Hidden during print) -->
    <div class="no-print fixed top-4 inset-x-0 z-50 flex justify-center px-4">
        <div class="bg-slate-900/95 backdrop-blur-md text-white rounded-2xl px-5 py-3 shadow-2xl border border-white/10 flex flex-wrap items-center gap-3 text-xs font-mono">
            <a href="{{ route('admin.judges.index') }}" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white font-bold transition">
                &larr; Judges Management
            </a>
            <a href="{{ route('admin.judges.duty') }}" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white font-bold transition">
                Duty Schedules
            </a>
            <a href="{{ route('admin.judges.duty.print') }}" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white font-bold transition">
                Judge Duty Slips
            </a>

            <div class="h-4 w-px bg-white/20"></div>

            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black flex items-center gap-2 transition shadow-md cursor-pointer uppercase tracking-wider font-sora">
                <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print / Save as PDF</span>
            </button>
        </div>
    </div>

    <!-- Main Container -->
    <div class="max-w-5xl mx-auto px-4 print:px-0 print:max-w-none pt-14 print:pt-0">
        <div class="bg-white rounded-2xl print:rounded-none border print:border-none border-slate-200 p-6 sm:p-8 print:p-0 shadow-sm print:shadow-none">
            
            <!-- Official Header -->
            @include('partials.print-pdf-header', [
                'title' => 'JUDGES OFFICIAL PASSWORDS & SCHEDULE ACCESS ROSTER',
                'centerTitle' => true,
                'hideDate' => false,
            ])

            <!-- Quick Access Portals Info Box -->
            <div class="my-4 p-3.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-800 text-xs font-mono">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="border-b sm:border-b-0 sm:border-r border-slate-300 pr-2 pb-2 sm:pb-0">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block">Option 1: Quick 4-Digit PIN</span>
                        <div class="font-bold text-slate-900 mt-0.5">quaf.ihyaussunna.in/judge/login</div>
                        <span class="text-[10px] text-slate-500">Enter Judge's 4-Digit Access PIN</span>
                    </div>
                    <div class="border-b sm:border-b-0 sm:border-r border-slate-300 pr-2 pb-2 sm:pb-0">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block">Option 2: Universal Custom PIN</span>
                        <div class="font-bold text-slate-900 mt-0.5">quaf.ihyaussunna.in/custom-judge/login</div>
                        <span class="text-[10px] text-slate-500">Universal Master PIN: <strong class="text-[#be1e2d]">{{ $customPin }}</strong></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-500 block">Option 3: Username &amp; Password</span>
                        <div class="font-bold text-slate-900 mt-0.5">quaf.ihyaussunna.in/login</div>
                        <span class="text-[10px] text-slate-500">Standard Email &amp; Password Login</span>
                    </div>
                </div>
            </div>

            <!-- Total Judges Count Badge -->
            <div class="flex items-center justify-between py-2 border-b border-slate-200 mb-3 text-xs font-mono">
                <span class="text-slate-600 font-bold uppercase tracking-wider">
                    Total Registered Adjudicators: <strong class="text-slate-950">{{ $judges->count() }}</strong>
                </span>
                <span class="text-slate-500 text-[11px]">
                    Strictly Confidential • For Festival Academic Directorate Use Only
                </span>
            </div>

            <!-- Credentials Master Table -->
            <table class="credential-table">
                <thead>
                    <tr>
                        <th style="width: 35px; text-align: center;">#</th>
                        <th style="width: 170px;">Judge Name</th>
                        <th style="width: 110px;">Contact</th>
                        <th style="width: 85px; text-align: center;">4-Digit PIN</th>
                        <th style="width: 200px;">Username / Email</th>
                        <th style="width: 125px;">Password</th>
                        <th>Assigned Events / Duty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($judges as $idx => $j)
                        @php
                            $dutyList = $j->sorted_programs ?? $j->programs;
                            $plainPass = $j->user?->plain_password ?? $j->access_code;
                        @endphp
                        <tr style="background-color: {{ $idx % 2 === 0 ? '#ffffff' : '#f8fafc' }};">
                            <td style="text-align: center; font-weight: bold; color: #64748b; font-size: 11px;">
                                {{ $idx + 1 }}
                            </td>
                            <td>
                                <div style="font-weight: 700; font-size: 12px; color: #0f172a;">{{ $j->name }}</div>
                                @if($j->designation)
                                    <div style="font-size: 10px; color: #64748b;">{{ $j->designation }}</div>
                                @endif
                            </td>
                            <td style="font-family: monospace; font-size: 10px; color: #334155;">
                                {{ $j->contact ?: '—' }}
                            </td>
                            <td style="text-align: center;">
                                <span style="display: inline-block; padding: 2px 6px; background-color: #fee2e2; color: #991b1b; font-weight: 800; font-family: monospace; font-size: 13px; border-radius: 4px; border: 1px solid #fecaca;">
                                    {{ $j->access_code ?: '—' }}
                                </span>
                            </td>
                            <td style="font-family: monospace; font-size: 10px; color: #1e293b; word-break: break-all;">
                                {{ $j->user?->email ?: '—' }}
                            </td>
                            <td style="font-family: monospace; font-weight: 700; font-size: 11px; color: #0f172a;">
                                {{ $plainPass ?: '—' }}
                            </td>
                            <td style="font-size: 10px; color: #334155;">
                                @if($dutyList->isEmpty())
                                    <span style="color: #94a3b8; font-style: italic;">No Duty Assigned</span>
                                @else
                                    <div style="font-weight: 700; color: #0f172a; margin-bottom: 2px;">
                                        {{ $dutyList->count() }} Competitions:
                                    </div>
                                    <div style="line-height: 1.3;">
                                        @foreach($dutyList as $prog)
                                            @php
                                                $stg = $prog->stage?->name ?? $prog->schedule?->stage?->name;
                                            @endphp
                                            <div>
                                                • <strong>{{ $prog->code }}</strong>: {{ \Illuminate\Support\Str::limit($prog->name, 26) }}
                                                @if($stg)
                                                    <span style="color: #64748b;">({{ $stg }})</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Signatures Section at Bottom -->
            <div class="mt-8 pt-6 border-t-2 border-slate-900 grid grid-cols-3 gap-4 text-center text-xs font-mono">
                <div>
                    <div class="h-10"></div>
                    <div class="border-t border-slate-400 pt-1 font-bold">Convenor, Adjudication Desk</div>
                    <div class="text-[10px] text-slate-500">QUAF Central Festival Committee</div>
                </div>
                <div>
                    <div class="h-10"></div>
                    <div class="border-t border-slate-400 pt-1 font-bold">Technical Director / IT Cell</div>
                    <div class="text-[10px] text-slate-500">QUAF 2026 Portal Management</div>
                </div>
                <div>
                    <div class="h-10"></div>
                    <div class="border-t border-slate-400 pt-1 font-bold">General Controller / Principal</div>
                    <div class="text-[10px] text-slate-500">Central Directorate</div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
