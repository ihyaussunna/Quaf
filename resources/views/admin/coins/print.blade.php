<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival Coin Ledger & Audit Report — QUAF 2026</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @font-face {
            font-family: 'Rockwell Std';
            src: url('/fonts/Rockwell-Regular.woff2') format('woff2'), url('/fonts/ROCK.TTF') format('truetype');
            font-weight: 400;
            font-style: normal;
        }
        @font-face {
            font-family: 'Rockwell Std';
            src: url('/fonts/Rockwell-Bold.woff2') format('woff2'), url('/fonts/Rockwell-Bold.otf') format('opentype');
            font-weight: 700;
            font-style: normal;
        }

        body {
            font-family: 'Rockwell Std', 'Rockwell', Georgia, serif;
            color: #0f172a;
            background-color: #f8fafc;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @page {
            size: A4 landscape;
            margin: 8mm 8mm 8mm 8mm;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .page-container {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            thead {
                display: table-header-group;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-6 text-slate-800">

    <!-- Top Floating Print & Navigation Toolbar (Hidden during Print) -->
    <div class="no-print max-w-7xl mx-auto mb-6 bg-slate-900 text-white p-4 rounded-2xl shadow-xl flex flex-wrap items-center justify-between gap-3 sticky top-4 z-50">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.coins.index', request()->query()) }}" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-mono text-xs font-bold transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back to Coin Ledger</span>
            </a>
            <div class="text-xs font-mono text-slate-300">
                Found <span class="font-bold text-amber-400">{{ number_format($transactions->count()) }}</span> total transactions
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-sora font-extrabold text-xs uppercase tracking-wider transition-all shadow-md flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Audit Report (അച്ചടിക്കുക)</span>
            </button>
        </div>
    </div>

    <div class="page-container max-w-7xl mx-auto bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200 print:border-none print:shadow-none print:p-0">

        <!-- Report Official Header -->
        <div class="border-b-2 border-slate-900 pb-4 mb-4 flex items-start justify-between gap-4">
            <div>
                <div class="text-[10px] font-mono font-bold tracking-widest text-slate-500 uppercase">
                    QUAF 09 • MARKAZ CULTURAL FESTIVAL 2026
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-0.5">
                    FESTIVAL COIN LEDGER & AUDIT REPORT
                </h1>
                <p class="text-xs text-slate-600 mt-1 font-mono">
                    മത്സര അവാർഡുകൾ, ജഡ്ജ്മെന്റ് അപ്ഡേറ്റ് ക്ലോബാക്കുകൾ, കാഷ്യർ റിഡംപ്ഷൻ എന്നിവയുടെ സമഗ്ര ഓഡിറ്റ് രേഖ
                </p>
            </div>
            <div class="text-right shrink-0 font-mono text-xs">
                <div class="font-bold text-slate-900">Generated: {{ now()->format('d M Y, h:i A') }}</div>
                <div class="text-[11px] text-slate-500">Official Central Audit Copy</div>
                <div class="mt-1 inline-block px-2 py-0.5 rounded bg-slate-100 font-bold text-slate-700 text-[10px]">
                    Total Records: {{ number_format($transactions->count()) }}
                </div>
            </div>
        </div>

        <!-- Filter Context Banner (If any filters applied) -->
        <div class="mb-4 p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs font-mono flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-bold text-slate-700 uppercase">Filter Scope:</span>
                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-semibold text-slate-800">
                    Event: {{ match($selectedType) {
                        'cash' => 'Cash Received / Added Coins (പണം നൽകി ചേർത്തവ)',
                        'competition' => 'Competition Awards (മത്സര അവാർഡുകൾ)',
                        'credit' => 'All Credits (എല്ലാ വരവുകളും)',
                        'clawback' => 'Judgment Clawbacks & Updates',
                        'debit' => 'Cashier Gift Redemptions (-Coins)',
                        'reversal' => 'Reversals & Unplaced',
                        default => 'All Events (എല്ലാ ഇവന്റുകളും)'
                    } }}
                </span>
                @if($selectedProgramModel)
                    <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-semibold text-slate-800">
                        Program: {{ $selectedProgramModel->code }} - {{ $selectedProgramModel->name }}
                    </span>
                @endif
                @if($selectedGroupModel)
                    <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-semibold text-slate-800">
                        Team: {{ $selectedGroupModel->name }}
                    </span>
                @endif
                @if($selectedStudent)
                    <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-semibold text-slate-800">
                        Student: {{ $selectedStudent->name }} (#{{ $selectedStudent->student_id }})
                    </span>
                @endif
                @if($search !== '')
                    <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-semibold text-slate-800">
                        Search: "{{ $search }}"
                    </span>
                @endif
            </div>
            <div class="text-[11px] text-slate-500">
                Printed from quaf.ihyaussunna.in
            </div>
        </div>

        <!-- 5 Metric Cards (Summary of Entire Festival) -->
        <div class="grid grid-cols-5 gap-3 mb-5 text-xs font-mono">
            <div class="p-3 rounded-xl border border-slate-200 bg-white">
                <div class="text-[10px] text-slate-500 font-bold uppercase">Competition Awards</div>
                <div class="text-lg font-black text-emerald-600 mt-0.5">+{{ number_format($totalCompetitionAwarded) }}</div>
                <div class="text-[9px] text-slate-400">മത്സരങ്ങളിൽ നൽകിയവ</div>
            </div>
            <div class="p-3 rounded-xl border border-emerald-300 bg-emerald-50/50">
                <div class="text-[10px] text-emerald-900 font-bold uppercase">Cash Received (പണം)</div>
                <div class="text-lg font-black text-emerald-700 mt-0.5">₹{{ number_format($totalCashAmount, 2) }}</div>
                <div class="text-[9px] text-emerald-700 font-semibold">+{{ number_format($totalCoinsFromCash) }} Coins ({{ $cashDepositsCount }} തവണ)</div>
            </div>
            <div class="p-3 rounded-xl border border-slate-200 bg-white">
                <div class="text-[10px] text-slate-500 font-bold uppercase">Clawed Back</div>
                <div class="text-lg font-black text-rose-600 mt-0.5">-{{ number_format($totalClawedBack) }}</div>
                <div class="text-[9px] text-slate-400">ജഡ്ജ്മെന്റ് റിവിഷൻ തിരിച്ചുപിടിക്കൽ</div>
            </div>
            <div class="p-3 rounded-xl border border-slate-200 bg-white">
                <div class="text-[10px] text-slate-500 font-bold uppercase">Total Redeemed</div>
                <div class="text-lg font-black text-blue-600 mt-0.5">-{{ number_format($totalRedeemed) }}</div>
                <div class="text-[9px] text-slate-400">ഗിഫ്റ്റുകൾക്കായി ചിലവഴിച്ചത്</div>
            </div>
            <div class="p-3 rounded-xl border border-slate-200 bg-white">
                <div class="text-[10px] text-slate-500 font-bold uppercase">Active Wallet Balance</div>
                <div class="text-lg font-black text-slate-900 mt-0.5">{{ number_format($totalActiveBalance) }}</div>
                <div class="text-[9px] text-slate-400">{{ $studentsWithCoinsCount }} വിദ്യാർത്ഥികളുടെ വാലറ്റിൽ</div>
            </div>
        </div>

        <!-- Master All Transactions Table -->
        <div class="border border-slate-300 rounded-xl overflow-hidden mb-6">
            <table class="w-full text-left border-collapse text-[11px]">
                <thead>
                    <tr class="border-b border-slate-300 bg-slate-100 text-[10px] font-mono uppercase tracking-wider text-slate-700">
                        <th class="py-2.5 px-3 font-bold w-10 text-center">#</th>
                        <th class="py-2.5 px-3 font-bold w-24">Date & Time</th>
                        <th class="py-2.5 px-3 font-bold w-48">Participant</th>
                        <th class="py-2.5 px-3 font-bold w-48">Competition</th>
                        <th class="py-2.5 px-3 font-bold w-40">Event & Prize</th>
                        <th class="py-2.5 px-3 font-bold text-right w-20">Coins</th>
                        <th class="py-2.5 px-3 font-bold">Audit Description & Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-800">
                    @forelse($transactions as $index => $tx)
                        @php
                            $isCredit = $tx->transaction_type === 'credit' && $tx->coins > 0;
                            $isDebit = $tx->transaction_type === 'debit';
                            $isReversal = $tx->transaction_type === 'reversal';
                            $isJudgmentUpdated = !empty($tx->metadata['judgment_updated']);
                        @endphp
                        <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-50/60' : 'bg-white' }} {{ $isJudgmentUpdated ? 'bg-amber-50/40' : ($isReversal ? 'bg-rose-50/30' : '') }}">
                            <!-- Index -->
                            <td class="py-2 px-3 font-mono font-bold text-slate-500 text-center">
                                {{ sprintf('%02d', $loop->iteration) }}
                            </td>

                            <!-- Date & Time -->
                            <td class="py-2 px-3 font-mono text-slate-600 whitespace-nowrap">
                                <div class="font-bold">{{ $tx->created_at?->format('d M Y') }}</div>
                                <div class="text-[9px] text-slate-500">{{ $tx->created_at?->format('h:i A') }}</div>
                            </td>

                            <!-- Participant Details -->
                            <td class="py-2 px-3">
                                @if($tx->student)
                                    <div>
                                        <div class="font-bold text-slate-900 leading-tight">
                                            {{ $tx->student->name }}
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[9px] text-slate-600 font-mono mt-0.5">
                                            <span class="font-bold text-slate-800">#{{ $tx->student->student_id }}</span>
                                            <span>•</span>
                                            <span class="font-bold">{{ $tx->student->group?->name }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-500 font-mono">General Participant</span>
                                @endif
                            </td>

                            <!-- Competition / Channel -->
                            <td class="py-2 px-3">
                                @php
                                    $isCash = $tx->isCashDeposit();
                                    $cashAmt = (float) ($tx->cash_amount ?: ($tx->metadata['cash_amount'] ?? 0));
                                @endphp
                                @if($tx->program)
                                    <div>
                                        <div class="font-bold text-slate-900 leading-tight">{{ $tx->program->name }}</div>
                                        <div class="flex items-center gap-1.5 text-[9px] font-mono text-slate-500 mt-0.5">
                                            <span>{{ $tx->program->code }}</span>
                                            <span>•</span>
                                            <span>{{ $tx->program->isGroup() ? 'Group' : 'Individual' }}</span>
                                        </div>
                                    </div>
                                @elseif($isCash)
                                    <span class="text-emerald-800 font-mono font-bold text-[10px]">Cashier Desk (പണം നൽകിയത്)</span>
                                @else
                                    <span class="text-slate-600 font-mono font-medium">Store Redemption / Cashier</span>
                                @endif
                            </td>

                            <!-- Event & Prize -->
                            <td class="py-2 px-3 whitespace-nowrap">
                                <div class="flex flex-col gap-0.5 items-start">
                                    @if($isJudgmentUpdated)
                                        @if(($tx->metadata['action'] ?? '') === 'demoted')
                                            <span class="font-mono font-bold text-rose-700 text-[10px]">
                                                Judgment Demoted (Clawback)
                                            </span>
                                        @else
                                            <span class="font-mono font-bold text-amber-700 text-[10px]">
                                                Judgment Promoted (Upgraded)
                                            </span>
                                        @endif
                                    @elseif($isCash)
                                        <span class="font-mono font-bold text-emerald-800 text-[10px]">
                                            Cash Received (പണം സ്വീകരിച്ചത്)
                                        </span>
                                        @if($cashAmt > 0)
                                            <span class="font-mono font-black text-emerald-700 text-[10px]">
                                                ₹{{ number_format($cashAmt, 2) }}
                                            </span>
                                        @endif
                                    @elseif($isCredit)
                                        <span class="font-mono font-bold text-emerald-700 text-[10px]">
                                            Competition Award
                                        </span>
                                    @elseif($isDebit)
                                        <span class="font-mono font-bold text-blue-700 text-[10px]">
                                            Gift Redemption
                                        </span>
                                    @elseif($isReversal)
                                        <span class="font-mono font-bold text-rose-700 text-[10px]">
                                            Reversal / Cancelled
                                        </span>
                                    @endif

                                    <div class="text-[10px] font-mono text-slate-600">
                                        @if($tx->prize_position)
                                            <span class="font-bold text-slate-900">
                                                {{ match($tx->prize_position) { 1 => '1st Prize', 2 => '2nd Prize', 3 => '3rd Prize', default => "Pos {$tx->prize_position}" } }}
                                            </span>
                                            @if($tx->grade && $tx->grade !== 'NO_GRADE')
                                                <span class="font-bold text-slate-700 ml-1">[{{ $tx->grade }}]</span>
                                            @endif
                                        @elseif($isCash)
                                            <span class="text-emerald-700 font-semibold text-[9px]">Cash Deposit Counter</span>
                                        @elseif($isDebit)
                                            <span class="text-slate-500">Presentation Counter</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Coins Change -->
                            <td class="py-2 px-3 text-right whitespace-nowrap">
                                @if($isCredit)
                                    <div class="font-mono font-bold text-xs text-emerald-700">
                                        +{{ number_format($tx->coins) }}
                                    </div>
                                    @if($isCash && $cashAmt > 0)
                                        <div class="text-[9px] font-mono text-emerald-700 font-bold">
                                            (₹{{ number_format($cashAmt) }} Cash)
                                        </div>
                                    @elseif($isJudgmentUpdated && !empty($tx->metadata['clawback_amount']))
                                        <div class="text-[9px] font-mono text-rose-600 font-bold">
                                            -{{ $tx->metadata['clawback_amount'] }} clawback
                                        </div>
                                    @elseif($isJudgmentUpdated && !empty($tx->metadata['coins_added']))
                                        <div class="text-[9px] font-mono text-emerald-700 font-bold">
                                            +{{ $tx->metadata['coins_added'] }} upgrade
                                        </div>
                                    @endif
                                @elseif($isDebit)
                                    <div class="font-mono font-bold text-xs text-blue-700">
                                        -{{ number_format(abs($tx->coins)) }}
                                    </div>
                                @elseif($isReversal)
                                    <div class="font-mono font-bold text-xs text-rose-700">
                                        -{{ number_format((int)($tx->metadata['clawback_amount'] ?? abs($tx->coins))) }}
                                    </div>
                                    <div class="text-[9px] font-mono text-rose-600 font-bold">Unplaced</div>
                                @endif
                            </td>

                            <!-- Audit Description & Notes -->
                            <td class="py-2 px-3">
                                <div class="text-[10px] text-slate-800 leading-snug">
                                    {{ $tx->description }}
                                </div>
                                <div class="text-[9px] font-mono text-slate-500 mt-0.5">
                                    Ref: {{ $tx->transaction_reference }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500 font-mono text-xs">
                                No coin transactions found for the selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-900 bg-slate-100 font-mono font-bold text-xs text-slate-900">
                        <td colspan="5" class="py-2.5 px-3 text-right uppercase">
                            Filtered Report Totals ({{ number_format($transactions->count()) }} Transactions):
                        </td>
                        <td class="py-2.5 px-3 text-right text-xs">
                            <span class="text-emerald-700">+{{ number_format($totalFilteredCredit) }}</span>
                            <span class="text-slate-400 mx-1">/</span>
                            <span class="text-blue-700">-{{ number_format($totalFilteredDebit) }}</span>
                            <div class="text-[10px] text-slate-600 font-black mt-0.5">
                                Net: {{ $netFilteredCoins >= 0 ? '+' : '' }}{{ number_format($netFilteredCoins) }}
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-[10px] text-slate-500">
                            Total Clawback on Revised Marks: -{{ number_format($totalFilteredClawback) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Verification Signatures Footer -->
        <div class="pt-6 border-t border-slate-300 grid grid-cols-4 gap-6 text-center text-xs font-mono">
            <div>
                <div class="h-10 border-b border-dashed border-slate-400"></div>
                <div class="font-bold text-slate-800 mt-1">Prepared by</div>
                <div class="text-[10px] text-slate-500">Data & Ledger Operator</div>
            </div>
            <div>
                <div class="h-10 border-b border-dashed border-slate-400"></div>
                <div class="font-bold text-slate-800 mt-1">Presentation Counter</div>
                <div class="text-[10px] text-slate-500">Store / Gift Cashier</div>
            </div>
            <div>
                <div class="h-10 border-b border-dashed border-slate-400"></div>
                <div class="font-bold text-slate-800 mt-1">Accounts Auditor</div>
                <div class="text-[10px] text-slate-500">Central Audit Board</div>
            </div>
            <div>
                <div class="h-10 border-b border-dashed border-slate-400"></div>
                <div class="font-bold text-slate-800 mt-1">Festival Convener</div>
                <div class="text-[10px] text-slate-500">QUAF 2026 Directorate</div>
            </div>
        </div>

    </div>

</body>
</html>
