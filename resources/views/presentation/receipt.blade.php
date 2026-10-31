<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Receipt #{{ $transaction->transaction_reference }} | QUAF 09</title>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700;800&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
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
            background: #fef3c7;
            color: #92400e;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 9999px;
            border: 1px solid #fde68a;
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
        .item-box {
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 0;
            margin-bottom: 18px;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            font-weight: 700;
        }
        .item-sub {
            font-size: 11px;
            color: #64748b;
            font-family: 'JetBrains Mono', monospace;
            margin-top: 4px;
        }
        .coins-total {
            font-family: 'JetBrains Mono', monospace;
            font-size: 16px;
            font-weight: 900;
            color: #be1e2d;
        }
        .balance-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 12px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'JetBrains Mono', monospace;
            margin-bottom: 24px;
        }
        .balance-label { font-size: 11px; color: #166534; font-weight: 700; }
        .balance-val { font-size: 16px; color: #15803d; font-weight: 900; }
        .signature-area {
            border-top: 2px dashed #cbd5e1;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #94a3b8;
            font-family: 'JetBrains Mono', monospace;
        }
        .actions {
            margin-top: 24px;
            display: flex;
            gap: 10px;
        }
        .btn {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            border: none;
            font-family: 'Sora', sans-serif;
            text-align: center;
            text-decoration: none;
        }
        .btn-print { background: #0f172a; color: white; }
        .btn-back { background: #e2e8f0; color: #334155; }
        @media print {
            body { background: white; padding: 0; }
            .slip-card { box-shadow: none; border: none; max-width: 100%; width: 100%; padding: 15px; }
            .actions { display: none; }
        }
    </style>
</head>
<body>

<div class="slip-card">
    <div class="header">
        <div class="logo-text">QUAF SEASON 09</div>
        <div class="sub-title">Presentation Desk • Official Voucher</div>
        <div class="badge">COIN REDEMPTION SLIP</div>
    </div>

    <div class="meta-grid">
        <div class="meta-item">
            <span>RECEIPT NO</span>
            <strong>{{ $transaction->transaction_reference }}</strong>
        </div>
        <div class="meta-item">
            <span>DATE & TIME</span>
            <strong>{{ $transaction->created_at?->format('d/m/Y h:i A') }}</strong>
        </div>
    </div>

    <div class="student-box">
        <div class="student-name">{{ $transaction->student?->name }}</div>
        <div class="student-meta">
            Chest #: <strong>{{ $transaction->student?->chest_number ?? $transaction->student?->student_id }}</strong>
            • Team: <strong>{{ $transaction->student?->group?->name ?? '---' }}</strong>
        </div>
    </div>

    <div class="item-box">
        @if(!empty($transaction->metadata['cart_items']))
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 8px;">
                <thead>
                    <tr style="border-bottom: 1px solid #cbd5e1; text-align: left; color: #64748b; font-size: 10px; font-family: 'JetBrains Mono', monospace;">
                        <th style="padding-bottom: 6px;">ITEM</th>
                        <th style="text-align: center; padding-bottom: 6px;">QTY</th>
                        <th style="text-align: right; padding-bottom: 6px;">COINS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->metadata['cart_items'] as $cItem)
                        <tr style="border-bottom: 1px dashed #e2e8f0;">
                            <td style="padding: 6px 0; font-weight: 700;">{{ $cItem['item_name'] }}</td>
                            <td style="text-align: center; font-family: 'JetBrains Mono', monospace;">{{ $cItem['quantity'] }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 800; color: #be1e2d;">{{ $cItem['subtotal_coins'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="item-row" style="padding-top: 6px;">
                <div style="font-size: 12px; font-weight: 800;">Total Cart Coins:</div>
                <div class="coins-total">{{ abs($transaction->coins) }} Coins</div>
            </div>
        @else
            <div class="item-row">
                <div>{{ $transaction->metadata['item_name'] ?? $transaction->description }}</div>
                <div class="coins-total">{{ abs($transaction->coins) }} Coins</div>
            </div>
            <div class="item-sub">
                Qty: {{ $transaction->metadata['quantity'] ?? 1 }}
                • Rate: {{ $transaction->metadata['coins_per_unit'] ?? abs($transaction->coins) }} Coins/unit
                @if(!empty($transaction->metadata['notes']))
                    • Ref: {{ $transaction->metadata['notes'] }}
                @endif
            </div>
        @endif
    </div>

    <div class="balance-box">
        <div class="balance-label">WALLET REMAINING COINS:</div>
        <div class="balance-val">{{ $transaction->student?->coins_cache ?? 0 }} Coins</div>
    </div>

    <div class="signature-area">
        <div>Operator: {{ $transaction->metadata['operator_name'] ?? 'Presentation Desk' }}</div>
        <div>Authorized Seal & Signature</div>
    </div>

    <div class="actions">
        <button onclick="window.print()" class="btn btn-print">Print Receipt</button>
        <button onclick="window.close()" class="btn btn-back">Close</button>
    </div>
</div>

</body>
</html>
