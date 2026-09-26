<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Sheet — {{ $selectedProgram->name }} ({{ $selectedProgram->code }})</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <style>
        @page {
            size: landscape;
            margin: 8mm 12mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #ffffff;
            color: #000000;
            padding: 16px;
        }
        .container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 8px;
        }
        .logo {
            height: 65px;
            width: auto;
            display: inline-block;
            object-fit: contain;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin: 6px 0 10px 0;
        }
        .meta-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
            padding: 0 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            font-size: 12px;
        }
        thead tr {
            height: 36px;
            background-color: #ffffff;
        }
        th, td {
            border: 1.5px solid #000000;
            padding: 6px 8px;
        }
        th {
            text-align: center;
            font-weight: 700;
        }
        tbody tr {
            height: 40px;
        }
        .text-center { text-align: center; }
        
        .no-print-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f1f5f9;
            padding: 10px 16px;
            margin-bottom: 16px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
        }
        .btn {
            background: #be1e2d;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            cursor: pointer;
        }
        .btn-close {
            background: #475569;
            color: #ffffff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }

        @media print {
            .no-print-toolbar {
                display: none !important;
            }
            body {
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print-toolbar">
        <div>
            <strong>Evaluation Sheet Printable</strong> — {{ $selectedProgram->name }} ({{ $selectedProgram->code }})
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn">Print Sheet</button>
            <button onclick="window.close()" class="btn-close">Close</button>
        </div>
    </div>

    <div class="container">
        <!-- Logo -->
        <div class="header">
            <img src="{{ asset('images/forms-header-logo.png') }}" class="logo" alt="QUAF Logo">
        </div>

        <!-- Title -->
        <div class="title">Evaluation Sheet</div>

        <!-- Meta Bar -->
        <div class="meta-bar">
            <div>Id: {{ $selectedProgram->code ?? $selectedProgram->id }}</div>
            <div>Program: {{ $selectedProgram->name }}</div>
            <div>Type: {{ ucfirst($selectedProgram->type) }}</div>
            <div>Zone: {{ strtoupper($selectedProgram->eligibility ?? 'ZONE A') }}</div>
        </div>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 14%;">Code Letter</th>
                    @php
                        $criteria = $selectedProgram->scoringCriteria ?? collect();
                    @endphp
                    @if($criteria->count() > 0)
                        @foreach($criteria as $crit)
                            <th>
                                {{ $crit->criterion_name }}
                                @if($crit->max_marks)
                                    <span style="display: block; font-size: 10px; font-weight: normal;">({{ $crit->max_marks }})</span>
                                @endif
                            </th>
                        @endforeach
                        @for($c = $criteria->count(); $c < 4; $c++)
                            <th style="width: 14%;"></th>
                        @endfor
                    @else
                        <th style="width: 14%;"></th>
                        <th style="width: 14%;"></th>
                        <th style="width: 14%;"></th>
                        <th style="width: 14%;"></th>
                    @endif
                    <th style="width: 16%;">Out of 100</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $codeLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
                @endphp
                @foreach($codeLetters as $letter)
                    <tr>
                        <td class="text-center" style="font-weight: 700; font-size: 13px;">{{ $letter }}</td>
                        @if($criteria->count() > 0)
                            @foreach($criteria as $crit)
                                <td></td>
                            @endforeach
                            @for($c = $criteria->count(); $c < 4; $c++)
                                <td></td>
                            @endfor
                        @else
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        @endif
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
