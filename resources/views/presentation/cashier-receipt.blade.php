<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Receipt #{{ $transaction->transaction_reference }} | QUAF 09</title>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Malayalam:wght@400;600;700&family=JetBrains+Mono:wght@400;500;700;800&family=Sora:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Sora', sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            padding: 24px;
            display: flex;
            justify-content: center;
        }
        .slip-card {
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #cbd5e1;
            padding-bottom: 18px;
            margin-bottom: 18px;
        }
        .logo-text {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 2px;
            color: #be1e2d;
        }
        .sub-title {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 3px;
        }
        .badge {
            display: inline-block;
            margin-top: 8px;
            background: #ecfdf5;
            color: #065f46;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: 9999px;
            border: 1px solid #a7f3d0;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 11px;
            margin-bottom: 16px;
            font-family: 'JetBrains Mono', monospace;
        }
        .meta-item span { color: #64748b; display: block; font-size: 10px; }
        .meta-item strong { color: #0f172a; font-weight: 700; }
        .student-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }
        .student-name {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
        }
        .student-meta {
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            color: #64748b;
            margin-top: 2px;
        }
        .deposit-box {
            background: #f0fdf4;
            border: 2px solid #bbf7d0;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 18px;
            text-align: center;
        }
        .deposit-label {
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            text-transform: uppercase;
            color: #166534;
        }
        .deposit-amount {
            font-size: 26px;
            font-weight: 900;
            color: #15803d;
            margin-top: 4px;
        }
        .coins-added-row {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #86efac;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 800;
        }
        .added-tag {
            background: #15803d;
            color: #ffffff;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 10px;
        }
        .balance-summary {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            space-y: 6px;
        }
        .balance-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .balance-row.final {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #cbd5e1;
            font-weight: 800;
            font-size: 13px;
            color: #0f172a;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            font-family: 'Anek Malayalam', sans-serif;
            border-top: 2px dashed #cbd5e1;
            padding-top: 14px;
        }
        .print-btn {
            display: block;
            width: 100%;
            background: #0f172a;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            cursor: pointer;
            margin-top: 18px;
            text-transform: uppercase;
        }
        .print-btn:hover { background: #1e293b; }
        @media print {
            body { background: white; padding: 0; }
            .slip-card { box-shadow: none; border: none; padding: 10px; width: 100%; max-width: 100%; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

    <div class="slip-card">
        <div class="header">
            <div class="logo-text">QUAF FESTIVAL 09</div>
            <div class="sub-title">Presentation Cashier Counter • Cash Deposit Receipt</div>
            <div class="badge">ADDED COINS RECEIPT</div>
        </div>

        <div class="meta-grid">
            <div class="meta-item">
                <span>RECEIPT REF</span>
                <strong>{{ $transaction->transaction_reference }}</strong>
            </div>
            <div class="meta-item" style="text-align: right;">
                <span>DATE & TIME</span>
                <strong>{{ $transaction->created_at?->format('d M Y, h:i A') }}</strong>
            </div>
            <div class="meta-item">
                <span>PAYMENT MODE</span>
                <strong>{{ strtoupper($transaction->payment_mode ?? 'CASH IN HAND') }}</strong>
            </div>
            <div class="meta-item" style="text-align: right;">
                <span>CASHIER</span>
                <strong>{{ $transaction->metadata['operator_name'] ?? 'Counter Desk' }}</strong>
            </div>
        </div>

        <div class="student-box">
            <div class="student-name">{{ $transaction->student?->name ?? 'Student' }}</div>
            <div class="student-meta">
                Chest #: <strong>{{ $transaction->student?->chest_number ?? $transaction->student?->student_id }}</strong> • 
                {{ $transaction->student?->group?->name ?? 'Group' }} • {{ $transaction->student?->zone_name ?? 'Zone' }}
            </div>
        </div>

        <!-- Cash & Coins Box -->
        <div class="deposit-box">
            <div class="deposit-label">Cash Received in Hand (കൈയിൽ പണം)</div>
            <div class="deposit-amount">₹ {{ number_format($transaction->cash_amount ?: ($transaction->metadata['cash_amount'] ?? 0), 2) }}</div>
            <div class="coins-added-row">
                <span class="added-tag">ADDED COINS</span>
                <span style="color: #15803d; font-size: 15px;">+{{ $transaction->coins }} Coins</span>
            </div>
        </div>

        <!-- Balance Info -->
        <div class="balance-summary">
            @if(isset($transaction->metadata['balance_before']))
                <div class="balance-row">
                    <span style="color: #64748b;">Previous Balance:</span>
                    <span>{{ $transaction->metadata['balance_before'] }} Coins</span>
                </div>
            @endif
            <div class="balance-row">
                <span style="color: #15803d; font-weight: 700;">Added Coins Now:</span>
                <span style="color: #15803d; font-weight: 700;">+{{ $transaction->coins }} Coins</span>
            </div>
            @if(isset($transaction->metadata['balance_after']))
                <div class="balance-row final">
                    <span>New Total Balance:</span>
                    <span>{{ $transaction->metadata['balance_after'] }} Coins</span>
                </div>
            @endif
        </div>

        @if(!empty($transaction->metadata['notes']))
            <div style="font-size: 11px; color: #64748b; margin-bottom: 14px; font-style: italic; text-align: center;">
                Notes: {{ $transaction->metadata['notes'] }}
            </div>
        @endif

        <div class="footer">
            പണം നൽകി വിജയകരമായി ചേർത്ത കോയിനുകൾ (Added Coins). ഈ കോയിനുകൾ ഉപയോഗിച്ച് പ്രസന്റേഷൻ കൗണ്ടറിൽ നിന്നും സമ്മാനങ്ങളും പുസ്തകങ്ങളും വാങ്ങാവുന്നതാണ്.
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; margin-top: 6px; color: #94a3b8;">
                QUAF Presentation Counter System • QUAF Season 09
            </div>
        </div>

        <button onclick="window.print()" class="print-btn">Print Slip / പ്രിന്റ് ചെയ്യുക</button>
    </div>

</body>
</html>
